<?php
class unidades_inadimplentes extends satecmax_mask
	{
	function __construct()
		{
		parent::__construct();

		$this->setTitle(__(".: Unidades Inadimplentes :."));

		$this->montaTable();

		$this->build("satecmax_quit_toolbar","toolbar");

		$this->build("p4a_frame","frm")
			->setWidth(1024);

		$this->frm->anchorCenter($this->tbl_unidades_inadimplentes);

		$this->display("main",$this->frm);

		$this->display("menu",p4a::singleton()->menu);

		$this->display("top",$this->toolbar);
		}

	function montaTable()
		{
		$this->build("p4a_db_source","src_unidades_inadimplentes")
			->setQuery("SELECT  
    						a.cd_unidade as unidade, 
							ifnull(sum(vlr_item_boleto), 0) as valor
						FROM
    						tbl_boleto_mes_unidade a
								INNER JOIN
    						tbl_boleto_mes_unidade_itens_cobranca b ON a.cd_boleto_mes_unidade = b.cd_boleto_mes_unidade
						WHERE
    						a.st_baixado = 0
        					AND DATE_ADD(a.dt_vencimento, INTERVAL 1 DAY) < CURDATE()
						GROUP BY a.cd_unidade")	
			->setPk("unidade")
			->Load();
				
		$this->build("p4a_table", "tbl_unidades_inadimplentes")
			->setSource($this->src_unidades_inadimplentes)
			->setWidth(400);
		
		$this->src_unidades_inadimplentes->fields->valor->setType("decimal");
		
		$this->tbl_unidades_inadimplentes->cols->unidade->setLabel(__("Unidades"))->setWidth(100);
		$this->tbl_unidades_inadimplentes->cols->valor->setLabel(__("Total Valor Histórico em aberto (R$)"))->setWidth(200);
			
		$this->tbl_unidades_inadimplentes->addActionCol("detalhes");
		$this->tbl_unidades_inadimplentes->cols->detalhes->setLabel(__("Detalhes"))
			->setWidth(50);
			
		$this->intercept($this->tbl_unidades_inadimplentes->cols->detalhes, "afterClick","abrirDetalhes");
		
		$this->setSource($this->src_unidades_inadimplentes);
		}
	function abrirDetalhes()
		{
		condgest::singleton()->openPopup("novo_acordo");
		}
		
	function main()
		{
		parent::main();
		}
	}