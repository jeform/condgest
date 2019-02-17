<?php
class cadastro_portaria extends satecmax_mask
	{
	function __construct()
		{
		parent::__construct();
		
		$this->setTitle(__("Cadastro de Portaria"));
		
		$this->build("satecmax_db_source","src_portaria")
			->setTable("tbl_portaria")
			->setPk("cd_portaria")
			->Load()
			->firstRow();
		
		$this->build("p4a_fieldset","fset_portaria")
			->setLabel(__("Cadastrar"))
			->setWidth(500);
			
		$this->build("p4a_table", "tbl_portaria")
			->setSource($this->src_portaria)
			->setLabel(__("Lista de Portarias"))
			->setWidth(500);
			
		$this->tbl_portaria->cols->cd_portaria->setLabel(__("ID"))
												->setWidth(30);
		$this->tbl_portaria->cols->nm_portaria->setLabel(__("Descrição"));
		$this->tbl_portaria->cols->st_portaria->setLabel(__("Status"))
												->setWidth(30);

		$this->tbl_portaria->setVisibleCols(array("cd_portaria","nm_portaria","st_portaria"));
				
		$this->setSource($this->src_portaria);			
			
		$this->build("satecmax_full_toolbar","toolbar")
			->setMask($this);

		$this->build("p4a_frame","frm")
			->setWidth(1024);
			
		$this->fset_portaria
			->anchor($this->fields->cd_portaria)
			->anchor($this->fields->nm_portaria)
			->anchor($this->fields->st_portaria);
			
		$this->setFieldsProperties();
		
		
		$this->frm->anchorCenter($this->tbl_portaria);
		$this->frm->anchorCenter($this->fset_portaria);	

		
		
		$this->display("main",$this->frm);
		
		$this->display("menu",p4a::singleton()->menu);
		
		$this->display("top",$this->toolbar);
		
		}
		
	
	function setFieldsProperties()			
		{
		$fields = $this->fields;
		
		$fields->cd_portaria->setLabel(__("ID"))
							->enable(false)
							->setWidth(50);	
							
		$fields->nm_portaria->setLabel(__("Descrição"))
							->setWidth(300);	
									
		$fields->st_portaria->setLabel(__("Status"));							
		}
		
	function main()
		{	
		parent::main();
		}
		
	}
?>