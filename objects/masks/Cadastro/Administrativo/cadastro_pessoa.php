<?php

class cadastro_pessoa extends satecmax_mask
	{
	function __construct()
		{
		parent::__construct();
		
		$this->setTitle(__("Cadastro de Pessoas"));
		
		$this->build("satecmax_db_source","src_pessoas")
			->setTable("tbl_pessoas")
			->addMultivalueField ("pessoas_tipo_pessoas", "tbl_pessoas_tipo_pessoas", "cd_pessoa", "cd_tipo_pessoa")
			->setPk("cd_pessoa")	
			->addOrder("nm_pessoa")
			->Load()
			->firstRow();

		$this->build("p4a_table","tbl_pessoas")
			->setSource($this->src_pessoas)
			->setLabel(__("Lista de Pessoas Cadastradas"))
			->setWidth(800);
			
		$this->tbl_pessoas->cols->nm_pessoa->setLabel(__("Nome"));
		$this->tbl_pessoas->cols->nr_tel_res->setLabel(__("Telefone"));
		$this->tbl_pessoas->cols->nr_identif->setLabel(__("CPF/CNPJ"));
		$this->tbl_pessoas->cols->st_pessoa->setLabel(__("Ativo (?)"));
		
		$this->tbl_pessoas->setVisibleCols(array("nm_pessoa","nr_tel_cel","nr_identif","st_pessoa"));
			
		$this->setSource($this->src_pessoas);		
			
		$this->build("satecmax_full_toolbar","toolbar")
			->setMask($this);			

		$this->toolbar->buttons->new->implement("onClick",$this,"novo_cadastro_pessoa");	
			
		$this->build("p4a_frame","frm")
			->setWidth(1024);					
			
		$this->build("p4a_fieldset","fset_detalhes_pessoas")
			->setLabel(__("Detalhes"))
			->setWidth(800)
			->setVisible(false);														

		$this->build("p4a_fieldset","fset_contatos")
			->setLabel(__("Telefone / Emails"))
			->setWidth(800);		
			
		$this->fset_contatos->anchor($this->fields->nr_tel_res)
							->anchorLeft($this->fields->nr_tel_cel)
							->anchorRight($this->fields->nr_tel_cel_1)
							->anchor($this->fields->nr_tel_cml)
							->anchorLeft($this->fields->nr_tel_fax)
							->anchor($this->fields->email_pessoa);	

		$this->build("p4a_fieldset","fset_end")
			->setLabel(__("Endereço"))
			->setWidth(800);	

		$arr_end_correspondencia[] = array("end_correspondencia"=>false,"desc"=>"Sim");
		$arr_end_correspondencia[] = array("end_correspondencia"=>true,"desc"=>"Não");
			
		$this->build("p4a_array_source","arr_source_end_correspondencia")
			->Load($arr_end_correspondencia)
			->setPk("end_correspondencia");	

		$this->build("p4a_field","fld_end_comercial")
				->setLabel(__("Mesmo endereço para correspondência ?"))
				->setType("radio")
				->setVisible(false)
				->setSource($this->arr_source_end_correspondencia);
			
		$this->fset_end->anchorLeft($this->fields->cep)
						->anchor($this->fields->txt_endereco)
						->anchor($this->fields->txt_bairro)
						->anchorLeft($this->fields->txt_cidade)
						->anchorLeft($this->fields->cd_estado);			
			
		$this->build("p4a_fieldset","fset_tipo_entidade")
			->setLabel(__("Tipo de Entidade"))
			->setWidth(800);	

		$this->fset_tipo_entidade->anchor($this->fields->pessoas_tipo_pessoas);
			
		$this->build("P4A_Tab_Pane","tab_pane")
			->setWidth(830)
			->addPage("fset_detalhes")->setLabel(__("Detalhes"));								
			
		$this->tab_pane->pages->fset_detalhes
			->anchor($this->fields->nm_pessoa)
			->anchor($this->fields->tipo_pessoa)
			->anchor($this->fields->nr_identif)
			->anchor($this->fset_end)
			->anchor($this->fset_contatos)
			->anchor($this->fset_tipo_entidade)
			->anchor($this->fields->st_pessoa);
			
		$this->setFieldsProperties();

		$this->build("p4a_fieldset","fset_busca_pessoa")
				->setLabel("Procurar")
				->setWidth(350);
				
		$this->build("p4a_field","fld_busca_pessoa")
				->setLabel("Buscar Pessoa");
	
		$this->build("p4a_button","btn_busca_pessoa")
			->implement("onClick",$this,"buscar")
			->setLabel("OK");
		
		$this->fset_busca_pessoa->anchor($this->fld_busca_pessoa)
								->anchorLeft($this->btn_busca_pessoa);	
		
		$this->frm->anchorCenter($this->fset_busca_pessoa);
		$this->frm->anchorCenter($this->tbl_pessoas);
		$this->frm->anchorCenter($this->tab_pane);						
								
		$this->addObjEsconderEdicao($this->tbl_pessoas);
		$this->addObjEsconderEdicao($this->fset_busca_pessoa);
		
		$this->display("main",$this->frm);
		
		$this->display("menu",p4a::singleton()->menu);
		
		$this->display("top",$this->toolbar);								
								
		}
		
	function setFieldsProperties()
		{
		$fields = $this->fields;	

		$fields->pessoas_tipo_pessoas->setLabel(__("Entidades"))
							->settype("multicheckbox")
							->setSource(P4A::singleton()->src_tipo_entidade_pessoa)
							->setSourceValueField("cd_tipo_pessoa")
							->setSourceDescriptionField("desc_tipo_pessoa");
								
		$fields->tipo_pessoa->setLabel(__("Tipo Pessoa"))
							->setType("radio")
							->setSource(P4A::singleton()->arr_source_tipo_pessoa)
							->addAction("onChange")
							->setInvisible();

		$this->intercept($this->fields->tipo_pessoa,"onChange","exibeTipoPessoa");
										
		$fields->nm_pessoa->setLabel(__("Nome"))
							->setWidth(350);
							
		$fields->nr_identif->setWidth(115);
											
							
		$fields->txt_endereco->setWidth(400)
							->setLabel(__("Endereço"));
															
		$fields->txt_bairro->setWidth(200)
							->setLabel(__("Bairro"));							

		$fields->txt_cidade->setLabel(__("Cidade"))
							->setWidth(200);

		$fields->cd_estado->setLabel(__("Estado"))
						->setSource(P4A::singleton()->src_estado_brasileiro)
						->setType("select");					

		$fields->cep->setLabel(__("CEP:"))
					->setInputMask("99.999-999")
					->setWidth(80);	
		
		$fields->txt_endereco_correspondencia->setWidth(400)
							->setLabel(__("Endereço"));
															
		$fields->txt_bairro_correspondencia->setWidth(200)
							->setLabel(__("Bairro"));							

		$fields->txt_cidade_correspondencia->setLabel(__("Cidade"))
							->setWidth(200);

		$fields->txt_estado_correspondencia->setLabel(__("Estado"))
										->setSource(P4A::singleton()->src_estado_brasileiro)
										->setType("select");					

		$fields->cep_correspondencia->setLabel(__("CEP:"))
					->setInputMask("99.999-999")
					->setWidth(80);				
					
		$fields->nr_tel_res->setLabel(__("Tel. Res."))
							->setInputMask("(99)9999-9999")
							->setWidth(150);									
								
		$fields->nr_tel_cml->setLabel(__("Tel. Com."))
							->setInputMask("(99)9999-9999")
							->setWidth(150);							
									
		$fields->nr_tel_fax->setLabel(__("Tel. Fax"))
							->setInputMask("(99)9999-9999")
							->setWidth(150);			
							
		$fields->nr_tel_cel->setLabel(__("Tel. Cel."))
							->setInputMask("(99)99999-9999")
							->setWidth(150);		

		$fields->nr_tel_cel_1->setLabel(__("Tel. Cel."))
							->setInputMask("(99)99999-9999")
							->setWidth(150);							
											
		$fields->email_pessoa->setLabel(__("E-mail"))
								->setWidth("412");
								
		$fields->st_pessoa->setLabel(__("Ativo (?)"));								
		}

	function exibeTipoPessoa()
		{
		$this->fields->nr_identif->setVisible();
		$tipo_pessoa = $this->fields->tipo_pessoa->getNewValue();
		
		if($tipo_pessoa == "PF")
			{
			$this->fields->nr_identif->setLabel(__("CPF"))
										->setInputMask("999.999.999-99");
			}
		if($tipo_pessoa == "PJ")
			{
			$this->fields->nr_identif->setLabel(__("CNPJ"))
										->setInputMask("99.999.999/9999-99");
			}	
		}	
	
	function novo_cadastro_pessoa()
		{
		$this->src_pessoas->newRow();		
		}	
	
	function buscar()
		{
		$busca_pessoa = $this->fld_busca_pessoa->getNewValue();
		$this->getSource()->setWhere("nm_pessoa		LIKE '%{$busca_pessoa}%'");		
		}

	function saveRow()
		{
		if ( $this->validateFields() and $this->getSource()->isNew() )
			{
			$nr_identif = $this->fields->nr_identif->getNewValue();
			
			if ( (p4a_db::singleton()->fetchOne("select count(*) from tbl_pessoas  where nr_identif = '{$nr_identif}'") > 0) )
				{
				$this->error(__("Pessoa já cadastrada!"));
				return false;
				}
				
			if ( !$this->fld_end_comercial->getNewValue() )
				{		
				$this->fields->txt_endereco_correspondencia->setNewValue($this->fields->txt_endereco->getNewValue());
				$this->fields->txt_bairro_correspondencia->setNewValue($this->fields->txt_bairro->getNewValue());
				$this->fields->txt_cidade_correspondencia->setNewValue($this->fields->txt_cidade->getNewValue());
				$this->fields->txt_estado_correspondencia->setNewValue($this->fields->cd_estado->getNewValue());
				$this->fields->cep_correspondencia->setNewValue($this->fields->cep->getNewValue());
				}	
			
			if ( $this->fields->pessoas_tipo_pessoas->getNewValue() == 0)
				{
				$this->error("Favor selecionar uma entidade ao menos");
				return false;
				}
			
			if( ( $this->fields->st_pessoa->getNewValue() <> "1"))
				{
				$this->warning("Pessoa cadastrada com status desativado!!!");
				}		
			}
		
		return parent::saveRow();		
		}
	
	function main()
		{
		if ( $this->getSource()->isNew())
			{
			$this->fields->tipo_pessoa->setVisible();
			$this->fld_end_comercial->setVisible();
			}
		else
			{
			$this->fields->tipo_pessoa->setVisible();
			$this->fld_end_comercial->setInVisible();
			//$this-fields
			}
		parent::main();
		}
		
	function reloadRow()
		{
		$this->fld_end_comercial->setNewValue(NULL);
		parent::reloadRow();
		}
	}