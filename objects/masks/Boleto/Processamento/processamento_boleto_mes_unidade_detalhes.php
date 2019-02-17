<?php
class processamento_boleto_mes_unidade_detalhes extends satecmax_mask
	{	
	function __construct()
		{
		parent::__construct();

		$this->setSource(condgest::singleton()->masks->processamento_boleto_mes_unidade->getSource());

		$this->setTitle(__("Composição da Arrecadação - Unidade ".p4a::singleton()->masks->processamento_boleto_mes_unidade->fields->cd_unidade->getValue()));
		
		$this->build("satecmax_db_source","src_boleto_mes_unidade_itens_cobranca")
			->setTable("tbl_boleto_mes_unidade_itens_cobranca")
			->addJoin("tbl_itens_cobranca_boleto","tbl_boleto_mes_unidade_itens_cobranca.cd_item_cobranca = tbl_itens_cobranca_boleto.cd_item_cobranca",array("ds_item_cobranca"))
			->setPk("cd_boleto_mes_unidade_itens_cobranca")
			->setWhere("cd_boleto_mes_unidade = '".p4a::singleton()->masks->processamento_boleto_mes_unidade->fields->cd_boleto_mes_unidade->getValue()."'")
			->Load()
			->firstRow();
		
		// carregar o db source em p4a_array
		
		
		$arrSrcBoletoMesUnidadeItensCobranca1 = $this->src_boleto_mes_unidade_itens_cobranca->getAll();
		
		$arrSrcBoletoMesUnidadeItensCobranca2 = array();

		$vlrTotalItensBoleto = 0;
		
		foreach($arrSrcBoletoMesUnidadeItensCobranca1 as $arrBoletoItem)
			{
			$vlrTotalItensBoleto += $arrBoletoItem["vlr_item_boleto"];
			
			$arrSrcBoletoMesUnidadeItensCobranca2[] = $arrBoletoItem;
			}
		
		$arrSrcBoletoMesUnidadeItensCobranca2[] = array("cd_unidade"=>"","ds_item_cobranca"=>"TOTAL", "ds_item_boleto"=>"", "vlr_item_boleto"=>$vlrTotalItensBoleto );

		$this->build("p4a_array_source","srcArr_boleto_mes_unidade_itens_cobranca")
			->Load($arrSrcBoletoMesUnidadeItensCobranca2)
			->setPk("cd_unidade");
		
		$this->srcArr_boleto_mes_unidade_itens_cobranca->fields->vlr_item_boleto->setType("decimal");
		
		$this->build("satecmax_db_source","srcBaixaBoleto")
			->setTable(__("tbl_boleto_mes_unidade"))
			->setFields(array("*","(IFNULL(vlr_diferenca,0))"=>"valor_diferenca",
									"(CASE st_diferenca WHEN 0 THEN 'PROCESSADA' WHEN 1 THEN 'À PROCESSAR' END)"=> "status_diferenca"))
			->setWhere("cd_boleto_mes_unidade = '".p4a::singleton()->masks->processamento_boleto_mes_unidade->fields->cd_boleto_mes_unidade->getValue()."'")
			->Load()
			->firstRow();
		
		$this->build("p4a_table", "tbl_boleto_mes_unidade_itens_cobranca")
			->setSource($this->srcArr_boleto_mes_unidade_itens_cobranca)
			->setWidth(500);
		
		$this->build("p4a_table", "tblBoletoBaixa")
			->setSource($this->srcBaixaBoleto)
			->setWidth(500);
		
		$this->tbl_boleto_mes_unidade_itens_cobranca->cols->cd_unidade->setLabel(__("Unidade"))
																		->setWidth(60);
		$this->tbl_boleto_mes_unidade_itens_cobranca->cols->ds_item_cobranca->setLabel(__("Desc. Item Cobrança"));
		$this->tbl_boleto_mes_unidade_itens_cobranca->cols->ds_item_boleto->setLabel(__("Info. Compl."));
		$this->tbl_boleto_mes_unidade_itens_cobranca->cols->vlr_item_boleto->setType("decimal")->setLabel(__("Vlr. Item Cobrança"))
																			->setWidth(80);

		$this->tbl_boleto_mes_unidade_itens_cobranca->addActionCol("excluir");
		$this->tbl_boleto_mes_unidade_itens_cobranca->cols->excluir->setLabel(__("Excluir"));
		$this->intercept($this->tbl_boleto_mes_unidade_itens_cobranca->cols->excluir, "afterClick","excluirItem");
		$this->tbl_boleto_mes_unidade_itens_cobranca->setVisibleCols(array("cd_unidade","ds_item_cobranca","ds_item_boleto","vlr_item_boleto","excluir"));
	
		$this->srcBaixaBoleto->fields->vlr_baixa_boleto->setType("decimal");
		$this->srcBaixaBoleto->fields->valor_diferenca->setType("decimal");
		
		$this->tblBoletoBaixa->cols->cd_unidade->setLabel(__("Unidade"));
		$this->tblBoletoBaixa->cols->dt_vencimento->setLabel(__("Dt Vencimento"));
		$this->tblBoletoBaixa->cols->dt_pagamento->setLabel(__("Dt Pagamento"));
		$this->tblBoletoBaixa->cols->vlr_baixa_boleto->setLabel(__("Valor Baixa"));
		
		$this->tblBoletoBaixa->addActionCol("desfazerBaixa");
		$this->tblBoletoBaixa->cols->desfazerBaixa->setLabel(__("Desfazer Baixa"));
		$this->intercept($this->tblBoletoBaixa->cols->desfazerBaixa, "afterClick","desfazerBaixa");
		
		$this->tblBoletoBaixa->setVisibleCols(array("cd_unidade","dt_vencimento","dt_pagamento","vlr_baixa_boleto","valor_diferenca","status_diferenca","desfazerBaixa"));		
		
		$this->build("p4a_frame","frm")
			->setWidth(600);
	
		$this->setSource($this->src_boleto_mes_unidade_itens_cobranca);

		$this->frm->anchorCenter($this->tbl_boleto_mes_unidade_itens_cobranca);
		$this->frm->anchorCenter($this->tblBoletoBaixa);
		$this->display("main",$this->frm);

	}

	function excluirItem()
		{
		$cd_item_cobranca =  str_replace(".","",$this->fields->cd_boleto_mes_unidade_itens_cobranca->getNewValue());
		
		try
			{
			P4A_DB::singleton()->beginTransaction();
			p4a_db::singleton()->query("delete from tbl_boleto_mes_unidade_itens_cobranca where cd_boleto_mes_unidade_itens_cobranca = '".$cd_item_cobranca."'");
			P4A_DB::singleton()->commit();
		
			$this->info("Ítem excluído com sucesso!");
			}
		catch (Exception $e)
			{
			P4A_DB::singleton()->rollback();
		
			$this->error(__("Erro: ".$e->getCode()." - ".$e->getMessage()));
			}			
		}
	function desfazerBaixa()
		{
		$this->info("Em implementação!!!");
		}		
		
	function main()
		{
		$stEmitido = p4a::singleton()->masks->processamento_boleto_mes_unidade->fields->st_emitido->getValue();
		$stBaixado = p4a::singleton()->masks->processamento_boleto_mes_unidade->fields->st_baixado->getValue();	
			
		$this->tbl_boleto_mes_unidade_itens_cobranca->cols->excluir->setVisible($stEmitido == '0');
		$this->tblBoletoBaixa->setVisible($stEmitido == '1' and $stBaixado == '1');
		
		parent::main();
		}	
}