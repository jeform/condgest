<?php

class cadastro_condominio extends satecmax_mask
	{
	function __construct()
		{
		parent::__construct();
		
		$this->setTitle(__("Cadastro de Condomínio"));
		
		$this->build("satecmax_db_source","src_condominio")
			->setTable("tbl_condominio")
			->setPk("cd_condominio")
			->Load()
			->firstRow();

		$this->setSource($this->src_condominio);		
		
		$this->build("p4a_table", "tbl_condominio")
			->setSource($this->src_condominio)
			->setLabel(__("Lista de Condomínios"))
			->setVisible(false)
			->setWidth(560);
		
		$this->tbl_condominio->cols->cd_condominio->setLabel(__("ID Condominio"));
		$this->tbl_condominio->cols->nm_condominio->setLabel(__("Nome"));
		$this->tbl_condominio->cols->nr_cnpj_condominio->setLabel(__("CNPJ"));
		$this->tbl_condominio->cols->logradouro_condominio->setLabel(__("Logradouro"));
		$this->tbl_condominio->cols->bairro_condominio->setLabel(__("Bairro"));
		$this->tbl_condominio->cols->municipio_condominio->setLabel(__("Município"));
		$this->tbl_condominio->cols->uf_condominio->setLabel(__("UF"));
		$this->tbl_condominio->cols->cep_condominio->setLabel(__("CEP"));
		$this->tbl_condominio->cols->telefone_condominio->setLabel(__("Telefone"));
		$this->tbl_condominio->cols->nr_unidade_condominio->setLabel(__("Número de Unidades"));

		$this->tbl_condominio->setVisibleCols(array("nm_condominio","nr_cnpj_condominio","telefone_condominio","nr_unidade_condominio"));
		
			
		$this->build("satecmax_full_toolbar","toolbar")
			->setMask($this);
			
		$this->build("p4a_frame","frm")
			->setWidth(1024);
			
		$this->build("p4a_fieldset","fset_condominio")
			->setLabel(__("Cadastrar"))
			->setWidth(1000);			
		
		$this->build("p4a_tab_pane","tab_pane")
			->addPage("fset_condominio")->setLabel(__("Dados Cadastrais"))
			->setWidth(910);
		
		$this->tab_pane->pages->fset_condominio
			->anchor($this->fields->cd_condominio)
			->anchor($this->fields->nm_condominio)
			->anchor($this->fields->nr_cnpj_condominio)
			->anchor($this->fields->dt_fundacao)
			->anchor($this->fields->logradouro_condominio)
			->anchorLeft($this->fields->nr_logradouro_condominio)
			->anchorLeft($this->fields->compl_logradouro_condominio)
			->anchor($this->fields->cep_condominio)
			->anchorLeft($this->fields->bairro_condominio)
			->anchorLeft($this->fields->municipio_condominio)
			->anchorLeft($this->fields->uf_condominio)
			->anchor($this->fields->telefone_condominio)
			->anchor($this->fields->telefone_secundario_condominio)
			->anchor($this->fields->email_condominio)
			->anchor($this->fields->area_total_condominio)
			->anchor($this->fields->nr_unidade_condominio);			
			
		$this->setFieldsProperties();
		
		
		$this->frm->anchorCenter($this->tbl_condominio);
		$this->frm->anchorCenter($this->tab_pane);		
		
		$this->display("main",$this->frm);
		
		$this->display("menu",p4a::singleton()->menu);
		
		$this->display("top",$this->toolbar);
		
		}	
		
	
	function setFieldsProperties()
		{
		$fields = $this->fields;
		
		$fields->cd_condominio->setLabel(__("ID"))
							->setInvisible()
							->setWidth(50);	
							
		$fields->nm_condominio->setLabel(__("Nome"))
							->setWidth(430);	

		$fields->dt_fundacao->setLabel(__("Data de Fundação"))
							->setWidth(80);
							
		$fields->nr_cnpj_condominio->setLabel(__("CNPJ"))
								->setInputMask("99.999.999/9999-99")
								->setWidth(140);
			
		$fields->logradouro_condominio->setLabel(__("Logradouro"))
								->setWidth(430);
								
		$fields->nr_logradouro_condominio->setLabel(__("Número"))
								->setWidth(50)
								->label->setWidth(60);		

		$fields->compl_logradouro_condominio->setLabel(__("Complemento"))
								->setWidth(50);									

		$fields->bairro_condominio->setLabel(__("Bairro/Distrito"))
								->setWidth(140);
						
		$fields->municipio_condominio->setLabel(__("Município"))
								->setWidth(200);
								
		$fields->uf_condominio->setLabel(__("UF"))
								->setSource(P4A::singleton()->src_estado_brasileiro)
								->setType("select")
								->label->setWidth(60);															
			
		$fields->cep_condominio->setLabel(__("CEP"))
									->setInputMask("99.999-999")
									->setWidth(80);
		
		$fields->telefone_condominio->setLabel(__("Tel. Principal"))
									->setInputMask("(99)9999-9999")
									->setWidth(100)
									->label->setWidth(100);
									
		$fields->telefone_secundario_condominio->setLabel(__("Tel. Secundário"))
									->setInputMask("(99)9999-9999")
									->setWidth(100)
									->label->setWidth(100);	

		$fields->email_condominio->setLabel(__("E-mail"))
								->setWidth("200");							
								
		$fields->area_total_condominio->setLabel(__("Área Total (m²)"))
									->setWidth(80);
		
		$fields->nr_unidade_condominio->setLabel(__("N° de Unidades"))
									->setWidth(80);	
		
		}
		
	function main()
		{		
		parent::main();
		}
		
	function saveRow()
		{ 	
		return parent::saveRow();		
		}			
	}

?>