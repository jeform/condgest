<?php

class cadastro_hist_padrao extends satecmax_mask
	{
	function __construct()
		{
		parent::__construct();

		$this->setTitle(__("Histórico Padrão"));
		
		$this->build("satecmax_db_source","src_hist_padrao")
			->setTable("hist_padrao")
			->setPK("cd_hist_padrao")
			->Load()
			->firstRow();
			
		$this->build("p4a_table", "tbl_hist_padrao")
			->setSource($this->src_hist_padrao)
			->setLabel(__("Históricos Padrão cadastrados"))
			->setWidth(500);
			
		$this->tbl_hist_padrao->cols->cd_hist_padrao->setLabel(__("Cód."))
											->setWidth(20);
		$this->tbl_hist_padrao->cols->ds_hist_padrao->setLabel(__("Descrição"))
												->setWidth(200);
		$this->tbl_hist_padrao->cols->st_hist_padrao->setLabel(__("Status"));


		
		$this->setSource($this->src_hist_padrao);		
					
		$this->build("satecmax_Full_Toolbar","toolbar")
			->setMask($this);		
			
		$this->build("p4a_frame","frm")
			->setWidth(1024);	
		
		$this->build("p4a_fieldset","fset_hist_padrao")
			->setWidth(500);
		
		$this->fset_hist_padrao
			->anchor($this->fields->cd_hist_padrao)
			->anchor($this->fields->ds_hist_padrao)
			->anchor($this->fields->st_hist_padrao);		

			
		$this->setFieldsProperties();	
		
		$this->frm->anchorCenter($this->tbl_hist_padrao);
		$this->frm->anchorCenter($this->fset_hist_padrao);
				
		
		$this->display("main",$this->frm);
		
		$this->display("menu",p4a::singleton()->menu);
		
		$this->display("top",$this->toolbar);	
			
		}	
		
		
	function setFieldsProperties()
		{
		$fields = $this->fields;
		
		$fields->cd_hist_padrao->setLabel(__("Código:"))
							->enable(false)
							->setWidth(30);
							
		$fields->ds_hist_padrao->setLabel(__("Descrição"))
							->setWidth(300);
							
		$fields->st_hist_padrao->setLabel(__("Ativo (?)"));					
	
		}	
	
	
	function main()
		{	
		parent::main();
		}			
	}