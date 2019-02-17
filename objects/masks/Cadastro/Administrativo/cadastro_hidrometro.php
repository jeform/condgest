<?php

class cadastro_hidrometro extends satecmax_mask
{
	function __construct()
		{
		parent::__construct();
		
		$this->setTitle("Cadastro de Hidrômetros");
		
	
		$this->build("satecmax_db_source","src_hidro")
			->setTable("tbl_hidrometro")
			->setPk("cd_hidro")	
			->Load()
			->firstRow();
		
		$this->build("satecmax_db_source","src_hidro_unidade")
			->setTable("tbl_hidro_unidade")
			->Load()
			->FirstRow();
	
		$this->build("p4a_table","tbl_hidro")
			->setSource($this->src_hidro)
			->setLabel(__("Lista de hidrômetros cadastrados"))
			->setWidth(500);
			
		$this->tbl_hidro->cols->cd_hidro->setLabel(__("ID"));
		$this->tbl_hidro->cols->ds_hidro->setLabel(__("Descrição"));
		$this->tbl_hidro->cols->st_hidro->setLabel(__("Status"));
		
		$this->setSource($this->src_hidro);
					
		$this->build("satecmax_full_toolbar","toolbar")
			->setMask($this);		

		$this->build("p4a_fieldset","fset_hidro")
			->setLabel(__("Detalhes"))
			->setWidth(500);	
			
		$this->build("p4a_frame","frm")
			->setWidth(1024);			

			
		$this->fset_hidro->anchor($this->fields->cd_hidro)
						->anchor($this->fields->ds_hidro)
						->anchor($this->fields->dt_ini_utilizacao)
						->anchorLeft($this->fields->dt_fim_utilizacao)
						->anchor($this->fields->st_hidro);

		$this->setFieldsProperties();
		
		$this->frm->anchorCenter($this->tbl_hidro);
		$this->frm->anchorCenter($this->fset_hidro);		

		$this->addObjEsconderEdicao($this->tbl_hidro);
		
		$this->display("main",$this->frm);
		
		$this->display("menu",p4a::singleton()->menu);
		
		$this->display("top",$this->toolbar);
		
		}		
	
	function setFieldsProperties()
		{
		$fields = $this->fields;	
		
		$fields->cd_hidro->setLabel(__("ID"))
							->setInvisible()
							->setWidth(50);
		
	/* 	$fields->cd_unidade->setLabel(__("Unidade"))
							->setWidth(90)
							->setSource(p4a::singleton()->src_unidade)
							->setSourceValueField("cd_unidade")
							->setSourceDescriptionField("unidade")
							->setType("select"); */

		$fields->ds_hidro->setLabel(__("Descrição"))
							->setWidth(350);

		$fields->dt_ini_utilizacao->setLabel(__("Dt. Inicio Utilização"))
								->setWidth(80);
		
		$fields->dt_fim_utilizacao->setLabel(__("Dt. Fim Utilização"))
								->setWidth(80);

		
		$fields->st_hidro->setLabel(__("Status"));
		}
}