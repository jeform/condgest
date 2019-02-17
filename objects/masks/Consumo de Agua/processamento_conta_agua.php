<?php

class processamento_conta_agua extends satecmax_mask
	{	
	function __construct()
		{
		parent::__construct();

		$this->setTitle(__("Processamento de Conta de Água"));
		
		$this->build("satecmax_db_source","src_leitura_agua_mes")
			->setFields(array("*",
					"(vlr_consumido + ifnull(vlr_consumido_1,0) + vlr_adicionais+(vlr_taxa_consumo*(select count(*) from tbl_unidades where st_unidade = 1 and st_hidrometro = 1)))"=>"vlr_total_presumido",
					"(select sum(vlr_total) + sum(ifnull(vlr_rateio_perda,0)) from tbl_leitura_agua_mes_individual where tbl_leitura_agua_mes.cd_leitura_agua_mes = tbl_leitura_agua_mes_individual.cd_leitura_agua_mes)"=>"vlr_total_individual",
					"(select sum(total_consumido) from tbl_leitura_agua_mes_individual where tbl_leitura_agua_mes.cd_leitura_agua_mes = tbl_leitura_agua_mes_individual.cd_leitura_agua_mes)"=>"total_consumido_condominos"))
			->setTable("tbl_leitura_agua_mes")
			->setPK("cd_leitura_agua_mes")
			->Load()
			->firstRow();		
			
		$this->build("p4a_table","tbl_leitura")
			->setSource($this->src_leitura_agua_mes)
			->setWidth(900);	
		
		$this->src_leitura_agua_mes->fields->vlr_total_presumido->setType("decimal");
		$this->src_leitura_agua_mes->fields->vlr_total_individual->setType("decimal");		
		$this->tbl_leitura->cols->mes_ano_referencia->setLabel("Mês/Ano Referência");
		$this->tbl_leitura->cols->total_consumido->setLabel("Consumo Condomínio");
		$this->tbl_leitura->cols->total_consumido_condominos->setLabel(__("Consumo Condôminos"));
		$this->tbl_leitura->cols->vlr_consumido->setLabel("Valor Conta de Água");
		$this->tbl_leitura->cols->vlr_adicionais->setLabel(__("Valor Conta Energia Elétrica"));
		$this->tbl_leitura->cols->vlr_taxa_consumo->setLabel(__("Valor Taxa Consumo"));
		$this->tbl_leitura->cols->vlr_total_presumido->setLabel(__("Total a Ratear"));
		
		$this->tbl_leitura->cols->vlr_total_individual->setLabel(__("Total Rateado"));
		
		$this->tbl_leitura->cols->st_leitura->setLabel("Leitura Realizada?");
		$this->tbl_leitura->cols->st_processamento->setLabel(__("Proc. realizado?"));

		$this->tbl_leitura->addActionCol("lancamentos");		
		$this->tbl_leitura->addActionCol("imprimirRelCompleto");
		$this->tbl_leitura->addActionCol("imprimirRelSimples");
		
		$this->tbl_leitura->cols->lancamentos->setLabel(__("Lançamentos"));
		$this->tbl_leitura->cols->imprimirRelCompleto->setLabel(__("Relatório Completo"))->setWidth(80);
		$this->tbl_leitura->cols->imprimirRelSimples->setLabel(__("Relatório Simples"))->setWidth(80);
		
		$this->tbl_leitura->setVisibleCols(array("mes_ano_referencia","total_consumido","total_consumido_condominos","vlr_total_presumido","vlr_total_individual","st_leitura","st_processamento","lancamentos","imprimirRelCompleto","imprimirRelSimples"));
		
		$this->build("satecmax_full_toolbar","toolbar")
			->setMask($this);	
			
		$this->toolbar->buttons->new->setLabel(__("Novo mês/ano referencia"),true);
		
		$this->toolbar->buttons->edit->setInvisible();
		
		$this->intercept($this->tbl_leitura->cols->lancamentos,"afterClick","mostraDistribuicaoInvidualAgua");
		$this->intercept($this->tbl_leitura->cols->imprimirRelCompleto,"afterClick","imprimirRelProcessamentoAgua");
		$this->intercept($this->tbl_leitura->cols->imprimirRelSimples,"afterClick","imprimirRelProcessamentoAguaCorreio");
		
			
		$this->setSource($this->src_leitura_agua_mes);		

		$this->build("p4a_frame","frm")
			->setWidth(1024);			
		
		$this->build("p4a_fieldset","fset_nova_leitura")
			->setLabel(__("Detalhes"))
			->setWidth(500);	
			
		$hidroPrincipal = P4A_DB::singleton()->fetchOne("select ds_hidro from tbl_hidrometro where st_principal = 'S' and st_hidro = 1");
		$hidroSecundario = P4A_DB::singleton()->fetchOne("SELECT ds_hidro FROM tbl_hidrometro where st_principal = 'N' and st_hidro = 1");
			
		$this->build("p4a_fieldset","fset_hidro_principal")
			->setLabel(__("Hidrômetro ".$hidroPrincipal));
					
		$this->build("p4a_fieldset","fset_hidro_secundario")
			->setLabel(__("Hidrômetro ".$hidroSecundario));	

		$this->fset_hidro_principal->anchor($this->fields->leitura_inicial)
								->anchorLeft($this->fields->leitura_final)
								->anchor($this->fields->vlr_consumido);
								
		$this->fset_hidro_secundario->anchor($this->fields->leitura_inicial_1)
								->anchorLeft($this->fields->leitura_final_1)
								->anchor($this->fields->vlr_consumido_1);
								
		$this->fset_nova_leitura
			->anchor($this->fields->mes_ano_referencia)
			->anchor($this->fset_hidro_principal)
			->anchor($this->fset_hidro_secundario)
			->anchor($this->fields->total_consumido)
			->anchor($this->fields->vlr_adicionais)
			->anchor($this->fields->vlr_taxa_consumo);
			
		$this->setFieldsProperties();		
			
		$this->frm->anchorCenter($this->tbl_leitura);	
		$this->frm->anchorCenter($this->fset_nova_leitura);	

		$this->addObjEsconderEdicao($this->tbl_leitura);
		
		$this->display("main",$this->frm);
		
		$this->display("menu",p4a::singleton()->menu);
		
		$this->display("top",$this->toolbar);	
			
		}			

	function setFieldsProperties()
		{
		$fields = $this->fields;			
				
		$fields->mes_ano_referencia->setLabel(__("Mês/Ano Referência"))
								->setInputMask("99/9999")
								->setWidth(50)
								->implement("onBlur",$this,"obterLeituraInicial")
								->label->setWidth(120);						
		
		$fields->leitura_inicial->setLabel(__("Leitura Anterior"))
								->setWidth(100)
								->enable(false)
								->label->setWidth(120);
		
		$fields->leitura_final->setLabel(__("Leitura Atual"))
							->setWidth(100)
							->implement("onBlur",$this,"calcularConsumoTotal")
							->label->setWidth(120);
							
		$fields->leitura_inicial_1->setLabel(__("Leitura Anterior"))
								->setWidth(100)
								->enable(false)
								->label->setWidth(120);
		
		$fields->leitura_final_1->setLabel(__("Leitura Atual"))
							->setWidth(100)
							->implement("onBlur",$this,"calcularConsumoTotal")
							->label->setWidth(120);					
		
		$fields->total_consumido->setLabel(__("Consumo"))
								->enable(false)
								->setWidth(100)
								->label->setWidth(120);

		$fields->vlr_consumido->setLabel(__("Vlr. Conta de Água"))
								->setWidth(100)
								->label->setWidth(120);
								
		$fields->vlr_consumido_1->setLabel(__("Vlr. Conta de Água"))
								->setWidth(100)
								->label->setWidth(120);
		
		$fields->vlr_taxa_consumo->setLabel(__("Vlr. Taxa Consumo"))
								->setWidth(100)
								->label->setWidth(120);

		$fields->vlr_adicionais->setLabel(__("Vlr. Conta de Energia Elétrica"))
							->setWidth(100)
							->label->setWidth(120);
		}
	
	function main()
		{				
		parent::main();
		}	
		
	function saveRow()
		{
		if ( $this->validateFields() and $this->getsource()->isNew() )
			{		
			$mes_ano_referencia = $this->fields->mes_ano_referencia->getNewValue();
		
			if ( (p4a_db::singleton()->fetchOne("select 
														count(*) 
												   from 
														tbl_leitura_agua_mes 
												  where 
														mes_ano_referencia = '{$mes_ano_referencia}'") > 0) )
				{
				$this->error(__("Mês/Ano referência já cadastrado!"));
				return false;
				}
			}
		$this->fields->st_leitura->setNewValue('0');
		$this->fields->st_processamento->setNewValue('0');
		$this->fields->st_utilizado->setNewValue('0');
				
		parent::saveRow();
		}	

	function obterLeituraInicial()
		{
		list($mes,$ano) = desmontarMesAnoReferencia($this->fields->mes_ano_referencia->getNewValue());
		
		$mes_ano_referencia_anterior = subtrairMesAnoRef('1', $mes, $ano, 1);
		
		$leituraInicial = p4a_db::singleton()->fetchOne("select leitura_final from tbl_leitura_agua_mes where mes_ano_referencia = '{$mes_ano_referencia_anterior}'");
		$leituraInicial1 = p4a_db::singleton()->fetchOne("select leitura_final_1 from tbl_leitura_agua_mes where mes_ano_referencia = '{$mes_ano_referencia_anterior}'");
			
		$this->fields->leitura_inicial->setNewValue($leituraInicial);
		$this->fields->leitura_inicial_1->setNewValue($leituraInicial1);
		
		if ($this->fields->leitura_inicial->getNewValue() <> "" or $this->fields->leitura_inicial->getNewValue() == 0)
			{
			$this->fields->leitura_inicial->enable();
			}
			
		if ($this->fields->leitura_inicial_1->getNewValue() <> "" or $this->fields->leitura_inicial_1->getNewValue() == 0)
			{
			$this->fields->leitura_inicial_1->enable();
			}	
		}
		
	function calcularConsumoTotal()
		{
		$leitura_anterior = str_replace(".", "",$this->fields->leitura_inicial->getNewValue());	
		$leitura_atual = str_replace(".", "",$this->fields->leitura_final->getNewValue());		
		$leitura_anterior1 = str_replace(".", "",$this->fields->leitura_inicial_1->getNewValue());	
		$leitura_atual1 = str_replace(".", "",$this->fields->leitura_final_1->getNewValue());		
		$consumo_atual = $leitura_atual - $leitura_anterior + $leitura_atual1 - $leitura_anterior1;
		
		$this->fields->total_consumido->setNewValue($consumo_atual);
		}	
  
	function mostraDistribuicaoInvidualAgua()
		{
		condgest::singleton()->openPopup("lancamento_conta_agua_individual");
		
		if ($this->fields->st_processamento->getSQLNewValue() == "1")
			{
			condgest::singleton()->active_mask->desabilitaEdicaoVisualizacao();
			}
		}			
		
	function setStatusMode()
		{
		if ( !$this->fields->st_processamento->getValue() == 1 )
			{
			parent::setStatusMode();
			}
		else
			{
			$this->info(__("Não é possivel editar após o processamento!"));
			}
		}
		
	function imprimirRelProcessamentoAgua()
		{
		$objRelatorio = new relProcessamentoAgua();
		
		$objRelatorio->setParametros($this->fields->cd_leitura_agua_mes->getNewValue());
		
		P4A_Output_File($objRelatorio->Output(), "relProcessamentoAgua.pdf", true);
		}	
		
	function imprimirRelProcessamentoAguaCorreio()
		{
		$objRelatorio = new relProcessamentoAguaCorreio();
	
		$objRelatorio->setParametros($this->fields->cd_leitura_agua_mes->getNewValue());
	
		P4A_Output_File($objRelatorio->Output(), "relProcessamentoAguaCorreio.pdf",true);
		}
	}