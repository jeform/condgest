<?php
class processamento_boleto_mes_unidade extends satecmax_mask
	{		
	function __construct()
		{
		parent::__construct();
		
		$this->build("P4a_frame","frm")
			->setWidth(1024);

		$this->build("satecmax_full_toolbar","toolbar")
			->setMask($this);
		
		$this->toolbar->buttons->new->setInvisible();
		$this->toolbar->buttons->save->setInvisible();
		$this->toolbar->buttons->cancel->setInvisible();
		$this->toolbar->buttons->edit->setLabel(__("Adicionar ítens cobrança"),true);
		$this->toolbar->buttons->edit->setIcon("actions/document-new");
		$this->toolbar->buttons->edit->setProperty("accesskey","N");
		$this->toolbar->buttons->edit->implement("onClick",$this,"adicionarItensCobranca");
		
		$this->toolbar->addButton('emitirBoleto','actions/process-accept-icon');
		$this->toolbar->buttons->emitirBoleto->setLabel(__("Emitir Boletos"),true)
											->enable(false)
											->implement("onClick",$this,"emitirBoletos");									
											
		$this->setSource(condgest::singleton()->masks->processamento_boleto_lote->getSource());

		$mes_ano_referencia = p4a::singleton()->masks->processamento_boleto_lote->fields->mes_ano_referencia->getvalue();
		$cd_boleto_mes = p4a::singleton()->masks->processamento_boleto_lote->fields->cd_boleto_mes->getValue();

		$this->setTitle(__("Boletos cadastrados em ").$mes_ano_referencia);

		$this->build("satecmax_db_source","src_boleto_mes_unidade")
			->setTable("tbl_boleto_mes_unidade")
			->setPk("cd_boleto_mes_unidade")
			->setWhere("cd_boleto_mes = '".$cd_boleto_mes."'")
			->setFields(array("*","(SELECT	  
										IFNULL(SUM(vlr_item_boleto),0)
									FROM
										tbl_boleto_mes_unidade_itens_cobranca 
									WHERE 
										cd_boleto_mes_unidade = tbl_boleto_mes_unidade.cd_boleto_mes_unidade)"=>"vlr_boleto",
								  "(SELECT
										IF(st_baixado = 1,
											ROUND(vlr_baixa_boleto,2),
											ROUND(
												SUM(vlr_item_boleto) + 
												SUM(vlr_item_boleto) * 0.02 + 
												SUM(vlr_item_boleto) * 0.00033 * DATEDIFF(DATE_ADD(CURRENT_DATE(), INTERVAL 0 DAY), dt_vencimento)  +
												IFNULL(SUM(vlr_item_boleto) * ((SELECT 
																					SUM(CASE
																							WHEN y.indice_correcao < 0 THEN 0.00
																							ELSE y.indice_correcao
																						END)
																				FROM
																					tbl_inpc y
																				WHERE
																					DATE_FORMAT(STR_TO_DATE(y.mes_ano_referencia, '%m/%Y'),
																							'%Y/%m') BETWEEN DATE_FORMAT(dt_vencimento, '%Y/%m') AND DATE_FORMAT(CURRENT_DATE(), '%Y/%m')) / 100),0)
												
											,2))	
									FROM 
										tbl_boleto_mes_unidade_itens_cobranca
								   	WHERE
					 				    cd_boleto_mes_unidade = tbl_boleto_mes_unidade.cd_boleto_mes_unidade)"=>"vlr_atual_boleto",
								  "(CONCAT_WS(' / ',CASE st_emitido WHEN 0 THEN 'NÃO EMITIDO' WHEN 1 THEN 'EMITIDO' END, CASE st_baixado WHEN 0 THEN 'EM ABERTO' WHEN 1 THEN 'BAIXADO' ELSE 'EM ACORDO' END))"=>"status"))
			->Load()
			->firstRow();

		$this->setSource($this->src_boleto_mes_unidade);
		
		$this->build("p4a_table", "tbl_boleto_mes_unidade")
			 ->setSource($this->src_boleto_mes_unidade)
			->setLabel(__("Unidades cadastradas / boletos"))
			->setWidth(900);

		$this->src_boleto_mes_unidade->fields->vlr_boleto->setType("decimal");	
		$this->src_boleto_mes_unidade->fields->vlr_atual_boleto->setType("decimal");	
			
		$this->tbl_boleto_mes_unidade->cols->cd_unidade->setLabel(__("Unidade"));
		$this->tbl_boleto_mes_unidade->cols->vlr_boleto->setLabel(__("Valor Histórico"))->setWidth(100);
		$this->tbl_boleto_mes_unidade->cols->vlr_atual_boleto->setLabel(__("Valor Atualizado em ".date("d/m/Y")))->setWidth(120);
		$this->tbl_boleto_mes_unidade->cols->dt_vencimento->setLabel(__("Data de Vencimento"))->setWidth(70);
		$this->tbl_boleto_mes_unidade->cols->status->setLabel(__("Status"))->setWidth(160);
		$this->tbl_boleto_mes_unidade->cols->dt_processamento->setLabel(__("Data de Emissão"))->setWidth(70);
		$this->tbl_boleto_mes_unidade->cols->dt_pagamento->setLabel(__("Data de Pagamento"))->setWidth(70);
		$this->tbl_boleto_mes_unidade->cols->vlr_baixa_boleto->setLabel(__("Valor Pago"))->setWidth(80);

		$this->tbl_boleto_mes_unidade->setVisibleCols(array("cd_unidade", "dt_processamento", "dt_vencimento", "vlr_boleto", "vlr_atual_boleto", "status", "dt_pagamento", "vlr_baixa_boleto"));
		
		$this->tbl_boleto_mes_unidade->addActionCol("detalhes");
		$this->tbl_boleto_mes_unidade->addActionCol("baixarBoleto");
		
		$this->tbl_boleto_mes_unidade->cols->detalhes->setLabel(__("Detalhes"));	
		$this->tbl_boleto_mes_unidade->cols->baixarBoleto->setLabel(__("Baixar Boleto"));

		$this->intercept($this->tbl_boleto_mes_unidade->cols->detalhes, "afterClick","mostrarItensCobranca");		
		$this->intercept($this->tbl_boleto_mes_unidade->cols->baixarBoleto, "afterClick","baixarBoleto");
		
		$this->build("p4a_fieldset","fset_boleto_mes_unidade")
			->setLabel(__("Detalhes"))
			->setWidth(500);
		
		$this->build("p4a_fieldset","fsetBuscaUnidade")
				->setLabel("Procurar")
				->setWidth(350);	
			
		$this->build("p4a_field","fldBuscaUnidade")
				->setLabel("Buscar Unidade");
	
		$this->build("p4a_button","btnBuscaUnidade")
			->implement("onClick",$this,"buscar")
			->setLabel("OK");
		
		$this->fsetBuscaUnidade->anchor($this->fldBuscaUnidade)
								->anchorLeft($this->btnBuscaUnidade);	
			
		$this->frm->anchor($this->tbl_boleto_mes_unidade);
		$this->frm->anchor($this->fsetBuscaUnidade);

		$this->display("main",$this->frm);
		$this->display("menu",p4a::singleton()->menu);
		$this->display("top",$this->toolbar);
		}
		
	function adicionarItensCobranca()
		{
		condgest::singleton()->openMask("processamento_boleto_mes_unidade_itens_cobranca");
		}	
		
	function mostrarItensCobranca()
		{
		condgest::singleton()->openPopup("processamento_boleto_mes_unidade_detalhes");
		}	

	function emitirBoletos()
		{				
		condgest::singleton()->openMask("processamento_emissao_boleto");
		}	
		
	function baixarBoleto()
		{
		if($this->fields->st_baixado->getNewValue() <> "0" )
			{
			$this->error(__("Boleto já baixado!!!"));
			return false;
			}	

		if($this->fields->st_emitido->getNewValue() == '0')
			{
			$this->error("Para efetuar a baixa do boleto ".$this->fields->cd_boleto_mes_unidade->getNewValue().", é necessário efetuar a emissão do mesmo!");
			return false;				
			}
		condgest::singleton()->openPopup("processamento_baixa_manual");		
		}
	
	function main()
		{
		$this->arrUnidades = p4a_db::singleton()->fetchAll("select st_emitido from tbl_boleto_mes_unidade where cd_boleto_mes = '{$this->fields->cd_boleto_mes->getNewValue()}'");
				
		foreach($this->arrUnidades as $dadosUnidades)
			{
			$st_emitido = $dadosUnidades["st_emitido"];
			
			if($st_emitido == '0')
				{
				$this->toolbar->buttons->emitirBoleto->enable();
				$this->toolbar->buttons->edit->enable();
				}	
			}	
		parent::main();
		}	
		
	function imprimirBoleto()
		{
		$idBoleto = $this->fields->cd_boleto_mes_unidade->getValue();		
		
		if(P4a_DB::Singleton()->FetchOne("select st_emitido from tbl_boleto_mes_unidade where cd_boleto_mes_unidade = {$idBoleto}") == '1')
			{
			P4A_Redirect_To_Url("/boleto/gerarBoleto.php?id={$idBoleto}",true);
			}	
		else
			{
			$this->error("Para efetuar a impressão, primeiro efetue a emissão do boleto!");
			}			
		}

	function buscar()
		{
		$busca_unidade = $this->fldBuscaUnidade->getNewValue();
		$cd_boleto_mes = p4a::singleton()->masks->processamento_boleto_lote->fields->cd_boleto_mes->getValue();
		$this->getSource()->setWhere("cd_unidade		= '{$busca_unidade}' and cd_boleto_mes = '{$cd_boleto_mes}'");		
		}	
	
	}