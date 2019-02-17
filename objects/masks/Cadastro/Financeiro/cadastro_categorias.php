<?php

class cadastro_categorias extends satecmax_mask
	{
	function __construct()
		{
		parent::__construct();

		$this->setTitle(__("Cadastro de Categorias"));
		
		$this->build("satecmax_db_source","src_categorias")
			->setTable("tbl_categorias")
			->setPK("cd_categoria")
			->addOrder("plano_contas","asc")
			->Load()
			->firstRow();
			
		$this->build("p4a_table", "tbl_categoria")
			->setSource($this->src_categorias)
			->setLabel(__("Categorias"))
			->setWidth(600);
			
		$this->tbl_categoria->cols->cd_categoria->setLabel(__("Código"));	
		$this->tbl_categoria->cols->plano_contas->setLabel(__("Plano de Contas"));
		$this->tbl_categoria->cols->ds_categoria->setLabel(__("Descrição"));
		$this->tbl_categoria->cols->tp_categoria->setLabel(__("Entrada/Saída"))
												->setSource(P4A::singleton()->src_tipo_categoria);
		$this->tbl_categoria->cols->tp_taxa_condominial->setLabel(__("Tipo"))
													->setSource(P4A::singleton()->src_tipo_taxa_condominial);
		$this->tbl_categoria->cols->st_categoria->setLabel(__("Status"));

		$this->tbl_categoria->setVisibleCols(array("cd_categoria","plano_contas","ds_categoria","tp_categoria","tp_taxa_condominial","st_categoria"));
		
		$this->setSource($this->src_categorias);		
					
		$this->build("satecmax_Full_Toolbar","toolbar")
			->setMask($this);		
			
		$this->build("p4a_frame","frm")
			->setWidth(1024);	
		
		$this->build("p4a_fieldset","fset_categorias")
			->setWidth(500);
		
		$this->fset_categorias->anchor($this->fields->cd_categoria)
							->anchor($this->fields->ds_categoria)
							->anchor($this->fields->plano_contas)
							->anchor($this->fields->tp_categoria)
							->anchor($this->fields->tp_taxa_condominial)
							->anchor($this->fields->st_categoria);		

			
		$this->setFieldsProperties();	
		
		$this->frm->anchorCenter($this->tbl_categoria);
		$this->frm->anchorCenter($this->fset_categorias);
				
		$this->addObjEsconderEdicao($this->tbl_categoria);
		
		$this->display("main",$this->frm);
		
		$this->display("menu",p4a::singleton()->menu);
		
		$this->display("top",$this->toolbar);	
			
		}	
		
		
	function setFieldsProperties()
		{
		$fields = $this->fields;
		
		$fields->cd_categoria->setLabel(__("Código"))
							->setWidth(50)
							->enable(false);
		
		$fields->plano_contas->setLabel(__("Plano Contas"))
							->setInputMask("999.999.999")
							->setWidth(100);
							
		$fields->ds_categoria->setLabel(__("Descrição"))
							->setWidth(300);
							
		$fields->tp_categoria->setLabel(__("Entrada/Saída"))
				->setSource(P4A::singleton()->src_tipo_categoria)
				->setType("select")
				->setWidth(200);
		
		$fields->tp_taxa_condominial->setLabel(__("Tipo"))
				->setSource(P4A::singleton()->src_tipo_taxa_condominial)
				->setType("select")
				->setWidth(200);

		$fields->st_categoria->setLabel(__("Status"));		
	
		}	
	
	
	function main()
		{	
		parent::main();
		}			
	}