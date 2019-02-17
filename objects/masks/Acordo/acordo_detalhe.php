<?php
class acordo_detalhe extends satecmax_mask
	{		
	
	function __construct()
		{
		parent::__construct();

		$cdAcordo = p4a::singleton()->masks->acordos->fields->cd_acordo->getvalue();
		
		
		$this->setTitle(".: Acordo nº ".$cdAcordo. ":.");
		
		$this->build("p4a_frame","frm")
			->setWidth(1024);
		
		$this->build("satecmax_quit_toolbar","toolbar");
		
		$this->montaTabelaAcordo($cdAcordo);
		
		$this->display("top",$this->toolbar);
		$this->display("main",$this->frm);
		$this->display("menu",p4a::singleton()->menu);
		}
		
	function montaTabelaAcordo($cdAcordo)
		{
		$this->build("satecmax_db_source","src_acordos_detalhes")
			->setTable("acordos_detalhes")
			->setWhere("cd_acordo = ".$cdAcordo)
			->setFields(array('*',"CONCAT_WS('/',nr_parcela,".p4a::singleton()->masks->acordos->fields->qtde_parcelas->getvalue().")"=>"parcelas",
				"(IF(cd_st_recebimento = 1 and dt_vencimento < now(),
					ROUND(vlr_parcela +
					vlr_parcela * 0.1 +
					vlr_parcela * 0.01 * DATEDIFF(DATE_ADD(CURRENT_DATE(), INTERVAL 0 DAY), dt_vencimento) / 30 +
					IFNULL(vlr_parcela *
										((SELECT
												sum(y.indice_correcao)
											FROM
                    							tbl_inpc y
                				  		   WHERE
                    							DATE_FORMAT(STR_TO_DATE(y.mes_ano_referencia, '%m/%Y'),'%Y/%m')
                    							BETWEEN DATE_FORMAT(acordos_detalhes.dt_vencimento,'%Y/%m')
                    								AND date_format(CURRENT_DATE(), '%Y/%m')) / 100),0),2),vlr_parcela))"=>"vlrParcela",
				"(case cd_st_recebimento when 2 then "."'Paga'"." when  1 then "."'Em aberto'"." when 3 then "."'Cancelada'"."end)"=>"status"))
			->setPk("cd_acordo")
			->Load()
			->firstRow();

		$this->setSource($this->src_acordos_detalhes);

		$this->build("p4a_table","tbl_acordos_detalhes")
			->setSource($this->src_acordos_detalhes)
			->setWidth(850);
		
		$this->src_acordos_detalhes->fields->vlrParcela->setType("decimal");
		
		$this->tbl_acordos_detalhes->setVisibleCols(array("parcelas","dt_vencimento","vlr_parcela","dt_recebimento","vlr_recebimento","status","vlrParcela"));

		$this->tbl_acordos_detalhes->cols->parcelas->setLabel(__("Nr. Parcela"));
		$this->tbl_acordos_detalhes->cols->dt_vencimento->setLabel(__("Dt. Vencimento"));
		$this->tbl_acordos_detalhes->cols->vlr_parcela->setLabel(__("Vlr. Parcela"));
		$this->tbl_acordos_detalhes->cols->dt_recebimento->setLabel(__("Dt. Recebimento"));
		$this->tbl_acordos_detalhes->cols->vlr_recebimento->setLabel(__("Vlr. Recebimento"));
		$this->tbl_acordos_detalhes->cols->status->setLabel(__("Status Parcela"));
		$this->tbl_acordos_detalhes->cols->vlrParcela->setLabel(__("Vlr. Atualizado em ".date("d/m/Y")))->setWidth(120);;

		$this->tbl_acordos_detalhes->addActionCol("baixar_parcela");
		$this->tbl_acordos_detalhes->cols->baixar_parcela->setLabel(__("Baixar Parcela"));
		$this->intercept($this->tbl_acordos_detalhes->cols->baixar_parcela, "afterClick","baixarParcelaAcordo");
		
		$this->frm->anchorCenter($this->tbl_acordos_detalhes);
		}

	function main()
		{		
		parent::main();		
		}	
		
	function baixarParcelaAcordo()
		{
		if($this->fields->cd_st_recebimento->getNewValue() == 2)
			{
			$this->error(__("Parcela já baixada!"));
			return false;
			}
		if($this->fields->cd_st_recebimento->getNewValue() == 3)
			{
			$this->error(__("Parcela cancelada do acordo"));
			return false;
			}
			
						
		condgest::singleton()->openPopup("acordo_baixar_parcela");
		}
	}