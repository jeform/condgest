<?php
class carta_cobranca extends satecmax_mask
	{
	function __construct()
		{
		parent::__construct();

		$this->setTitle(__("Cartas de Cobrança"));

		$this->build("satecmax_db_source","src_carta_cobranca")
			->setTable("tbl_boleto_mes_unidade")
			->setPk("cd_boleto_mes_unidade")
			->setWhere("st_baixado = 0 and DATE_ADD(dt_vencimento, INTERVAL 30 DAY) < CURDATE()")
			->setGroup("cd_unidade")
			->Load()
			->firstRow();
			
		$this->build("p4a_table", "tbl_carta_cobranca")
			->setSource($this->src_carta_cobranca)
			->setLabel(__("Relação Unidades com Pendências"))
			->setWidth(400);
		
		$this->tbl_carta_cobranca->setVisibleCols(array("cd_unidade"));
		
		$this->tbl_carta_cobranca->cols->cd_unidade->setLabel(__("Unidades"))
												->setWidth(100);
		
		$this->tbl_carta_cobranca->addActionCol("detalhes");
		$this->tbl_carta_cobranca->addActionCol("imprimir");
		
		$this->tbl_carta_cobranca->cols->detalhes->setLabel(__("Detalhes"))
											->setWidth(50);
		$this->tbl_carta_cobranca->cols->imprimir->setLabel(__("Imprimir"))
											->setWidth(70);
		
		$this->intercept($this->tbl_carta_cobranca->cols->detalhes, "afterClick","mostrarUnidades");
		$this->intercept($this->tbl_carta_cobranca->cols->imprimir, "afterClick","imprimirCartaCobranca");
		
		$this->build("satecmax_full_toolbar","toolbar")
			->setMask($this);
		
		$this->toolbar->buttons->new->setInvisible();

		$this->build("p4a_frame","frm")
			->setWidth(1024);
		
		$this->setSource($this->src_carta_cobranca);
		
		$this->frm->anchorCenter($this->tbl_carta_cobranca);
				
		$this->display("main",$this->frm);
		
		$this->display("menu",p4a::singleton()->menu);
		
		$this->display("top",$this->toolbar);
		}
			
	function imprimirCartaCobranca()
		{
		$objRelatorio = new rptCartaCobranca();
	
		$objRelatorio->setParametros($this->fields->cd_unidade->getNewValue());
	
		P4A_Output_File($objRelatorio->Output(), "rpCartaCobranca.pdf",true);
		}		
				
	function main()
		{
		parent::main();
		}		
	}