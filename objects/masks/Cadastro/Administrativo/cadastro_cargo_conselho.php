<?php

class cadastro_cargo_conselho extends satecmax_mask
	{
	function __construct()
		{
		parent::__construct();
		
		$this->setTitle("Cadastro de Cargos do Conselho");
	
		$this->build("satecmax_db_source","src_cargo_conselho")
			->setTable("tbl_cargo_conselho")
			->setPk("cd_cargo_conselho")	
			->Load()
			->firstRow();
		
		$this->build("p4a_table","tbl_cargo_conselho")
			->setSource($this->src_cargo_conselho)
			->setLabel(__("Lista de Cargos cadastrados"))
			->setWidth(500);
			
		$this->tbl_cargo_conselho->cols->cd_cargo_conselho->setLabel(__("Cód."))
														->setWidth(50);
		$this->tbl_cargo_conselho->cols->ds_cargo_conselho->setLabel(__("Descrição"));		
		
		$this->setSource($this->src_cargo_conselho);
					
		$this->build("satecmax_full_toolbar","toolbar")
			->setMask($this);		

		$this->build("p4a_fieldset","fset_cargo_conselho")
			->setLabel(__("Detalhes"))
			->setWidth(500);	
			
		$this->build("p4a_frame","frm")
			->setWidth(1024);			
		
		$this->fset_cargo_conselho->anchor($this->fields->cd_cargo_conselho)
								->anchor($this->fields->ds_cargo_conselho);	

		$this->setFieldsProperties();
		
		$this->frm->anchorCenter($this->tbl_cargo_conselho);
		$this->frm->anchorCenter($this->fset_cargo_conselho);		

		$this->addObjEsconderEdicao($this->tbl_cargo_conselho);
		
		$this->display("main",$this->frm);
		
		$this->display("menu",p4a::singleton()->menu);
		
		$this->display("top",$this->toolbar);
		
		}		
	
	function setFieldsProperties()
		{
		$fields = $this->fields;	
		
		$fields->cd_cargo_conselho->setLabel(__("ID"))
							->setInvisible()
							->setWidth(50);

		$fields->ds_cargo_conselho->setLabel(__("Descrição"))
						->setWidth(370);
		}
	}