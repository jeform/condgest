<?php
class composicao_acordo extends satecmax_mask
	{		
	
	function __construct()
		{
		parent::__construct();

		$cdAcordo = p4a::singleton()->masks->acordos->fields->cd_acordo->getvalue();
		
		$this->setTitle(".: Composição do Acordo de nº ".$cdAcordo. ":.");
		
		$this->build("p4a_frame","frm")
			->setWidth(1024);
		
		$this->build("p4a_quit_toolbar","toolbar");
		
		$this->montaTabelaAcordo($cdAcordo);
		
		$this->display("top",$this->toolbar);
		$this->display("main",$this->frm);
		$this->display("menu",p4a::singleton()->menu);
		}
		
	function montaTabelaAcordo($cdAcordo)
		{
		$this->build("satecmax_db_source","srcComposicaoAcordo")
			->setTable("tbl_boleto_mes_unidade")
			->setWhere("cd_acordo = ".$cdAcordo)
			->setFields(array("*","(select	  
										ifnull(sum(vlr_item_boleto),0)
									from
										tbl_boleto_mes_unidade_itens_cobranca 
									where 
										cd_boleto_mes_unidade = tbl_boleto_mes_unidade.cd_boleto_mes_unidade)"=>"vlr_boleto",
								  "(case st_baixado when 0 then 'EM ABERTO' when 1 then 'BAIXADO' when 2 then 'ACORDO EM ANDAMENTO' when 3 then 'ACORDO DESCUMPRIDO'  end)"=>"status"))
			->setPk("cd_boleto_mes_unidade")
			->Load()
			->firstRow();

		$this->setSource($this->srcComposicaoAcordo);

		$this->build("p4a_table","tblComposicaoAcordo")
			->setSource($this->srcComposicaoAcordo)
			->setWidth(550);
		
		$this->srcComposicaoAcordo->fields->vlr_boleto->setType("decimal");
		
		$this->tblComposicaoAcordo->setVisibleCols(array("cd_unidade","nosso_numero", "dt_processamento", "dt_vencimento", "vlr_boleto", "status"));
		
		$this->tblComposicaoAcordo->cols->cd_unidade->setLabel(__("Unidade"))->setWidth(40);
		$this->tblComposicaoAcordo->cols->nosso_numero->setLabel(__("Nosso Número"));
		$this->tblComposicaoAcordo->cols->dt_processamento->setLabel(__("Data de Emissão"))->setWidth(70);
		$this->tblComposicaoAcordo->cols->dt_vencimento->setLabel(__("Data de Vencimento"))->setWidth(70);
		$this->tblComposicaoAcordo->cols->vlr_boleto->setLabel(__("Valor Histórico (R$)"))->setWidth(100);
		$this->tblComposicaoAcordo->cols->status->setLabel(__("Status"))->setWidth(120);
				
		$this->frm->anchorCenter($this->tblComposicaoAcordo);
		}

	function main()
		{	
		parent::main();		
		}	
	}