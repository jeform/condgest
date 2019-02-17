<?php
class cadastro_bancos extends satecmax_mask
	{
	public $condicao;	
		
	function __construct()
		{
		parent::__construct();
		
		$this->setTitle(__("Cadastro de Bancos"));
		
		$this->build("satecmax_db_source","src_banco")
			->setTable("tbl_bancos")
			->setPk("cod_banco")
			->Load()
			->firstRow();
		
		$this->build("p4a_fieldset","fset_bancos")
			->setLabel(__("Detallhes"))
			->setWidth(500);
			
		$this->build("p4a_table", "tbl_bancos")
			->setSource($this->src_banco)
			->setLabel(__("Lista de Bancos Cadastrados"))
			->setWidth(500);
			
		$this->tbl_bancos->cols->nr_banco->setLabel(__("Número"));
		$this->tbl_bancos->cols->desc_banco->setLabel(__("Descrição"));	

		$this->tbl_bancos->setVisibleCols(array("nr_banco","desc_banco"));
				
		$this->setSource($this->src_banco);			
			
		$this->build("satecmax_full_toolbar","toolbar")
			->setMask($this);

			
		$this->build("p4a_frame","frm")
			->setWidth(1024);
			
		$this->fset_bancos
			->anchor($this->fields->nr_banco)
			->anchor($this->fields->desc_banco)
			;
			
		$this->setFieldsProperties();
		
		
		$this->frm->anchorCenter($this->tbl_bancos);
		$this->frm->anchorCenter($this->fset_bancos);		
		
		
		$this->display("main",$this->frm);
		
		$this->display("menu",p4a::singleton()->menu);
		
		$this->display("top",$this->toolbar);
		
		}
		
	
	function setFieldsProperties()			
		{
		$fields = $this->fields;
		
		$fields->nr_banco->setLabel(__("Código:"))
							->setWidth(50);	
							
		$fields->desc_banco->setLabel(__("Descrição"))
							->setWidth(300);			
		}
		
		
	function main()
		{	
		parent::main();
		}
		
	}
		
		
		
?>