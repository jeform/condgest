<?php
class contas_receber extends satecmax_mask
	{	
	function __construct()
		{
		parent::__construct();
		
		$this->setTitle(__("Contas à Receber"));
		
		$this->build("satecmax_db_source","src_contas_receber")
			->setTable("contas_receber")
			->setFields(array('*',"concat_ws('/',nr_parcela,qtde_parcelas)"=>"parcelas"))
			->setPk("cd_conta_receber")
			->addJoin("tbl_pessoas","contas_receber.cd_pessoa = tbl_pessoas.cd_pessoa",array("nm_pessoa"))
			->Load()
			->firstRow();
		
		$this->setSource($this->src_contas_receber);		
		
		$this->build("satecmax_full_toolbar","toolbar")
			->setMask($this);
		
		$this->build("p4a_fieldset","fset_filtros")
			->setLabel(__("Filtros"))
			->setWidth(600);					
		
		$this->montarFiltros();	
			
		$this->build("p4a_table","tbl_contas_receber")
			->setSource($this->src_contas_receber)
			->setLabel(__("Contas à Receber"))
			->setWidth(600);
			
		$this->tbl_contas_receber->setVisibleCols(array("cd_conta_receber","nm_pessoa","dt_vencimento","vlr_conta_receber","parcelas","cd_st_recebimento"));
			
		$this->tbl_contas_receber->cols->cd_conta_receber->setLabel(__("Código"));
		$this->tbl_contas_receber->cols->nm_pessoa->setLabel(__("Fornecedor"));
		$this->tbl_contas_receber->cols->dt_vencimento->setLabel(__("Vencimento"));
		$this->tbl_contas_receber->cols->vlr_conta_receber->setLabel(__("Valor"));
		$this->tbl_contas_receber->cols->parcelas->setLabel(__("Parcela"));
		$this->tbl_contas_receber->cols->cd_st_recebimento->setLabel(__("Status"))
			->setSource(condgest::singleton()->srcStatusContasReceber);
			
		$this->tbl_contas_receber->addActionCol("baixarParcela");
		
		$this->tbl_contas_receber->cols->baixarParcela->setLabel(__("Baixar Parcela"));
		
		$this->intercept($this->tbl_contas_receber->cols->baixarParcela, "afterClick","baixarParcela");
		
		
		$this->build("p4a_field","fld_replica_contas")
			->setLabel(__("Replica parcelas"))
			->setType("checkbox")
			->setInvisible();

		$this->setFieldsProperties();
			
		
		$this->build("p4a_fieldset","fset_contas_receber")
			->setLabel(__("Detalhes"))
			->setWidth(600);
		
		$this->build("p4a_button","btn_visualizar_recebimento")
			->setLabel(__("Visualizar Recebimento"))
			->implement("onClick",$this,"visualizarRecebimento");	
			
		$this->fset_contas_receber->anchor($this->fields->cd_conta_receber)
								->anchorLeft($this->btn_visualizar_recebimento)
								->anchor($this->fields->cd_pessoa)
								->anchor($this->fields->tp_documento)
								->anchor($this->fields->nr_documento)
								->anchor($this->fields->dt_vencimento)
								->anchor($this->fields->nr_parcela)
								->anchor($this->fields->qtde_parcelas)
								->anchor($this->fld_replica_contas)
								->anchor($this->fields->vlr_conta_receber)
								->anchor($this->fields->cd_categoria_conta)
								->anchor($this->fields->dsc_conta_receber);		

		$this->build("p4a_frame","frm")
			->setWidth(1024);								
								
		$this->frm->anchorCenter($this->fset_filtros);
		$this->frm->anchorCenter($this->tbl_contas_receber);
		$this->frm->anchorCenter($this->fset_contas_receber);
		
		$this->addObjEsconderEdicao($this->tbl_contas_receber);
		$this->addObjEsconderEdicao($this->fset_filtros);
		
		
		$this->display("main",$this->frm);
		
		$this->display("menu",p4a::singleton()->menu);
		
		$this->display("top",$this->toolbar);	
		}
	
	function setFieldsProperties()			
		{
		$fields = $this->fields;
		
		$fields->cd_conta_receber->setLabel(__($this->tbl_contas_receber->cols->cd_conta_receber->getLabel()))->disable();
		
		$this->build("satecmax_db_source","src_pessoas")
			->setTable("tbl_pessoas")
			->setpk("cd_pessoa")
			->load();
		
		$fields->cd_pessoa->setLabel(__("Fornecedor"))
							->setSource($this->src_pessoas)
							->setSourceValueField("cd_pessoa")
							->setSourceDescriptionField("nm_pessoa")
							->setType("select")
							->setWidth(200);
			
		$fields->tp_documento->setLabel(__("Tipo Pagamento"))
							->setSource(P4A::singleton()->src_documentos_cadastrados)
							->setSourceValueField("cd_documento")
							->setSourceDescriptionField("desc_documento")
							->setType("select")
							->setWidth(200);	
							
		$fields->nr_documento->setLabel(__("Nr. Documento"))
							->setWidth(300);					
		
		$fields->dt_vencimento->setLabel(__("Dt.Vencimento"));
		
		$fields->nr_parcela->setLabel(__("Nr.Parcela"));
		
		$fields->qtde_parcelas->setLabel(__("Qtde.Parcelas"));
		
		$fields->vlr_conta_receber->setLabel(__("Valor Parcela"));
		
		$fields->cd_categoria_conta->setLabel(__("Categoria Rec."))
									->setSource(P4A::singleton()->src_entrada_categoria)
									->setSourceValueField("cd_categoria")
									->setSourceDescriptionField("ds_categoria")
									->setType("select")
									->setWidth(300);
		
		$fields->dsc_conta_receber->setLabel(__("Descrição"))->setType("textarea");
		
		}
		
	function montarFiltros()
		{
		$this->build("p4a_field","fld_status")
			->setLabel(__("Status"))
			->setType("radio")
			->setSource(P4A::singleton()->srcStatusContasReceber);
		
		$this->build("p4a_field","fld_dt_vencimento_inicio")
			->setLabel(__("Vencimento de:"))
			->setType("date");
			
		$this->build("p4a_field","fld_dt_vencimento_fim")
			->setLabel(__("Até:"))
			->setType("date");

		$this->build("p4a_field","fld_pessoa")
			->setLabel(__("Pessoas"));				

		$this->build("p4a_button","btn_filtrar")
			->setLabel(__("Filtrar"))
			->implement("onClick",$this,"filtrar");
		
		$this->fset_filtros->anchor($this->fld_status)
						->anchor($this->fld_dt_vencimento_inicio)
						->anchorLeft($this->fld_dt_vencimento_fim)
						->anchor($this->fld_pessoa)
						->anchorCenter($this->btn_filtrar);
			
		}	
		
	function filtrar()
		{
		$fld_status = $this->fld_status->getSQLNewValue();
		
		$fld_data_inicial = formatarDataBanco($this->fld_dt_vencimento_inicio->getSQLNewValue());
		
		$fld_data_fim = formatarDataBanco($this->fld_dt_vencimento_fim->getSQLNewValue());
		
		$fld_pessoa = $this->fld_pessoa->getSQLNewValue();
		
		$sqlCompl = " 1 = 1";
		
		if ($fld_status <> "")
			{
			$sqlCompl.= " and contas_receber.cd_st_recebimento = '{$fld_status}' ";
			}
			
		if ($fld_data_inicial <> "" || $fld_data_fim <> "")
			{
			$sqlCompl.= " and contas_receber.dt_vencimento between '{$fld_data_inicial}' and '{$fld_data_fim}' ";	
			}	
		
		if ($fld_pessoa <> "")
			{
			$sqlCompl.= " and tbl_pessoas.nm_pessoa like '%{$fld_pessoa}%' ";	
			}	
			
		$this->info($sqlCompl);
		$this->getSource()->setWhere($sqlCompl);			
		
		if ( $this->getSource()->getNumRows() == 0 )
			{
			$this->getSource()->setWhere("1=0");
			$this->error(__("Nenhum Registro Encontrado!"));
			}
		else
			{
			$this->firstRow();
			}
		}
		
	function saveRow()
		{
		$st_replica_parcela = false;
		if($this->getSource()->isNew())
			{
			$this->fields->cd_st_recebimento->setNewValue(1);	
		
			if($this->fld_replica_contas->getNewValue())
				{
				$st_replica_parcela = true;
				$this->fields->reset();
	
				while($field = $this->fields->nextItem())
					{
					if ($field->data_field->getTable() == $this->getSource()->getTable())
							$fields[$field->getName()] = $field->getSQLNewValue(); 
					}				
				}
			}
			
		try
			{
			p4a_db::singleton()->beginTransaction();
			parent::saveRow();

			if ($st_replica_parcela)
				{

				$qtde_parcelas = $fields["qtde_parcelas"];
				$nr_parcelas = $fields["nr_parcela"] + 1;
				
				
				for($a=$nr_parcelas;$a<= $qtde_parcelas; $a++)
					{
					$fields["nr_parcela"]++;

					list($ano,$mes,$dia) = desmontarDataYmd($fields["dt_vencimento"]);
					
					$fields["dt_vencimento"] = acrescentarMesesDatas($dia, $mes, $ano, 1);
					
					$this->getSource()->newRow();
					
					foreach($fields as $nm_field => $vlr_field)
						{
						$this->getSource()->fields->$nm_field->setNewValue($vlr_field);
						}
						
					$this->getSource()->saveRow();
		
					}
				}
			P4A_DB::singleton()->commit();
			$this->fld_replica_contas->setNewValue(0);
			}
		catch (Exception $e)
			{
			
			P4A_DB::singleton()->rollback();
			
			$this->error(__("Falha ao salvar! Tente novamente!"));
			}
		}
		
	function main()
		{
			
		$this->fld_replica_contas->setVisible($this->getSource()->isNew());
		
		$this->btn_visualizar_recebimento->setVisible($this->fields->cd_st_recebimento->getValue() == 2);
		
		parent::main();
		}

	function baixarParcela()
		{
		if ($this->fields->cd_st_recebimento->getValue() == 2)
			{
			$this->error("Parcela já baixada");
			return false;
			}
		condgest::singleton()->openPopup("conta_recebida_baixar_parcela");
		}
		
	function visualizarRecebimento()
		{
		condgest::singleton()->openPopup("conta_recebida_baixar_parcela");
		}		
	}	

class conta_recebida_baixar_parcela extends satecmax_mask
	{
	function __construct()
		{
		parent::__construct();
		
		$this->build("p4a_frame","frm")
			->setWidth(800);
			
		$this->setSource(condgest::singleton()->masks->contas_receber->getSource());

		if ( $this->fields->cd_st_recebimento->getValue() == 1)
			{
			$this->setStatusMode();
			}
			
		$this->setTitle(__("Baixar parcela ").$this->fields->nr_parcela->getValue()."/".$this->fields->qtde_parcelas->getValue().
										__(" - Valor R$ ").number_format($this->fields->vlr_conta_receber->getValue(),2,",","."));	
		
			
		$this->build("p4a_fieldset","fset_dados_pagamento")
			->setLabel(__("Dados Recebimento"))
			->setWidth(600);
			
		$this->frm->anchor($this->fset_dados_pagamento);
		
		$this->fset_dados_pagamento->anchor($this->fields->dt_recebimento)
			->anchor($this->fields->vlr_juros)
			->anchor($this->fields->vlr_outros)
			->anchor($this->fields->vlr_recebimento);

		$this->fields->dt_recebimento->setLabel(__("Data Recebimento"));	
		
		$this->fields->vlr_juros->setLabel(__("Juros"))
			->implement("onBlur",$this,"calculaValorTotal");
			
		$this->fields->vlr_outros->setLabel(__("Outros"))
			->implement("onBlur",$this,"calculaValorTotal");
			
		$this->fields->vlr_recebimento->setLabel(__("Valor Recebimento"))
									->setProperty("dir","rtl");
			
		$this->build("p4a_button","btn_baixar_parcela")
			->setLabel(__("Baixar Parcela"),true)
			->implement("onClick",$this,"saveRow")
			->setIcon("actions/document-save")
			->setVisible($this->fields->cd_st_recebimento->getValue() == 1);

		$this->frm->anchorCenter($this->btn_baixar_parcela);	
			
		$this->fields->dt_recebimento->setNewValue($this->fields->dt_recebimento->getSQLValue());
		
		$this->fields->vlr_recebimento->setNewValue($this->fields->vlr_conta_receber->getSQLValue());
		
		$this->display("main",$this->frm);	
			
		}

	function calculaValorTotal()
		{
		$vlrJuros = $this->fields->vlr_juros->getSQLNewValue();
		
		$vlrOutros = $this->fields->vlr_outros->getSQLNewValue();
		
		$vlr_conta_receber = $this->fields->vlr_conta_receber->getSQLValue();
		
		$this->fields->vlr_recebimento->setNewValue($vlr_conta_receber+$vlrJuros+$vlrOutros);
		}	
		
	function saveRow()
		{
		if ( $this->fields->vlr_recebimento->getSQLNewValue() == "" or $this->fields->dt_recebimento->getSQLNewValue() == "" )
			{	
			$this->error(__("Preencha todos os campos obrigatórios!"));		
			return false;
			}		
		
		try
			{
			P4A_DB::singleton()->beginTransaction();
				
			$this->fields->cd_st_recebimento->setNewValue(2);// marcar como recebido...
				
			$this->registraBaixaCaixa();
				
			parent::saveRow();
				
				
			P4A_DB::singleton()->commit();
			}
		catch (Exception $e)
			{
			P4A_DB::singleton()->rollback();
				
			$this->error(__("Erro na atualização do Registro! ".$e->getMessage()));
			}
		
		$this->showPrevMask();
		}
		
	function registraBaixaCaixa()
		{
	
		$objCaixaMovimento = new movimentoCaixa(2);
	
		$nrLancamento = $objCaixaMovimento->newMovimentoEntrada($this->fields->dt_recebimento->getNewValue(),
																$this->fields->vlr_recebimento->getNewValue(),
																$this->fields->cd_pessoa->getValue(),
																$this->fields->nr_documento->getValue(),
																$this->fields->tp_documento->getValue(),
																"BAIXA CONTAS A RECEBER: ".$this->fields->dsc_conta_receber->getValue(),
																$this->fields->cd_categoria_conta->getValue(),
																2,
																"BAIXA CONTAS A RECEBER");
		
		}
		
}