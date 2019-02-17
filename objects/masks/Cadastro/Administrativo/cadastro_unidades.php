<?php

class cadastro_unidades extends satecmax_mask
	{
	function __construct()
		{
		parent::__construct();
		
		$this->setTitle(__("Cadastro de Unidades"));
		
		$this->build("satecmax_db_source","src_unidades")
			->setTable("tbl_unidades")
			->addOrder("cd_unidade")
			->setPageLimit(10)
			->Load()
			->FirstRow();	

		$this->build("satecmax_db_source","src_condominio")
			->setTable("tbl_condominio")
			->Load();
					
		$this->build("satecmax_db_source","src_condominio_unidade")
			->setTable("tbl_condominio_unidade")
			->setPk("cd_condominio_unidade")
			->Load();
				
		$this->build("p4a_db_source","src_proprietarios")
			->setTable("tbl_pessoas")
			->setWhere(" cd_pessoa in (select cd_pessoa from tbl_pessoas_tipo_pessoas where cd_tipo_pessoa = 2)")
			->Load();	
		
		$this->build("satecmax_db_source","src_hidro_unidade")
			->setTable("tbl_hidro_unidade")
			->setPk(array("cd_hidro","cd_unidade"))
			->load();
		
		$this->build("satecmax_db_source","src_hidrometros")
			->setTable("tbl_hidrometro")
			->Load();
			
		$this->build("satecmax_db_source","src_morador_unidade")
			->setTable("tbl_morador")
			->setPk(array("cd_pessoa","cd_unidade"))
			->Load();		
			
		$this->build("satecmax_db_source","src_unidade_morador")
			->setTable("tbl_pessoas")
			->setWhere(" cd_pessoa in (select cd_pessoa from tbl_pessoas_tipo_pessoas where cd_tipo_pessoa = 1)")
			->setPk("cd_pessoa")
			->Load();	
		
		$this->build("p4a_table", "tbl_unidades")
			->setSource($this->src_unidades)
			->setLabel(__("Lista de Unidades"))
			->setWidth(600);			
			
		$this->tbl_unidades->cols->cd_unidade->setLabel(__("Unidade"))
											->setWidth(80);
		$this->tbl_unidades->cols->cd_pessoa->setLabel(__("Nome Proprietário"))
											->setSource($this->src_proprietarios)
											->setSourceValueField("cd_pessoa")
											->setSourceDescriptionField("nm_pessoa");
		$this->tbl_unidades->cols->st_unidade->setLabel(__("Ativo (?)"))
											->setWidth(80);

		$this->tbl_unidades->setVisibleCols(array("cd_unidade","cd_pessoa","st_unidade"));
		
		$this->build("p4a_table","tbl_hidro_unidade")
			->setLabel(__("Hidrômetro Cadastrado"))
			->setSource($this->src_hidro_unidade)
			->setWidth(400);
		
		$this->tbl_hidro_unidade->setVisibleCols(array("cd_hidro"));
		
		$this->tbl_hidro_unidade->cols->cd_hidro->setLabel(__("Hidrômetro"))
												->setSource($this->src_hidrometros)
												->setSourceValueField("cd_hidro")
												->setSourceDescriptionField("ds_hidro");
		
		$this->tbl_hidro_unidade->addActionCol("excluirHidro");
		
		$this->tbl_hidro_unidade->cols->excluirHidro->setLabel(__("Excluir Hidrômetro"));
		
		$this->intercept($this->tbl_hidro_unidade->cols->excluirHidro,"afterClick","excluirHidroUnidade");
		
		
		$this->build("p4a_button","btn_incluir_hidro_unidade")
				->setLabel(__("Incluir hidrômetro"),true)
				->setIcon("actions/document-new")
				->implement("onClick",$this,"incluirNovoHidro");

		
		//Morador
		$this->build("p4a_table","tbl_morador")
			->setSource($this->src_morador_unidade)
			->setWidth(400)
			->setLabel(__("Lista de Moradores Cadastrados"));
			
		$this->tbl_morador->setVisibleCols(array("cd_pessoa"));
		
		$this->tbl_morador->cols->cd_pessoa->setLabel(__("Nome do morador"))
											->setSource($this->src_unidade_morador)
											->setSourceValueField("cd_pessoa")
											->setSourceDescriptionField("nm_pessoa");
												
		$this->tbl_morador->addActionCol("excluir_morador");										
								
		$this->tbl_morador->cols->excluir_morador->setLabel(__("Excluir morador"));
		
		$this->intercept($this->tbl_morador->cols->excluir_morador,"afterClick","excluirMorador");
		
		
		$this->build("p4a_button","btn_incluir_morador")
			->setLabel(__("Incluir morador"),true)
			->setIcon("actions/document-new")
			->implement("onClick",$this,"incluirMorador");	
			
		$this->setSource($this->src_unidades);			
			
		$this->build("satecmax_full_toolbar","toolbar")
			->setMask($this);			

		$this->toolbar->buttons->new->implement("onClick",$this,"novo_cadastro_unidade");	
			
		$this->build("p4a_frame","frm")
			->setWidth(1024);		
		
		$this->build("p4a_fieldset","fset_detalhes_morador")
			->setLabel(__("Detalhes morador"))
			->setInvisible();

		$this->build("p4a_fieldset","fset_detalhes_hidro")
			->setLabel(__("Detalhes Hidrômetro"))
			->setInvisible();
		
		$this->build("p4a_fieldset","fset_unidade_condominio")
			->setLabel(__("Detalhes"))
			->setWidth(500)
			->setVisible(false);
			
		$this->build("p4a_tab_pane","tab_pane")
			->addPage("fset_unidade_condominio")->setLabel(__("Detalhes"))
												->setWidth(520);

		
		$this->tab_pane->pages->fset_unidade_condominio->anchor($this->fields->cd_unidade)
														->anchor($this->fields->cd_pessoa)
														->anchor($this->fields->area_total_unidade)
														->anchor($this->fields->ds_observacao)
														->anchor($this->fields->st_unidade);

		$this->tab_pane->addPage("fset_dados_hidro_unidade")->setLabel(__("Dados Hidrômetro"))->setWidth(600);
		$this->tab_pane->pages->fset_dados_hidro_unidade->anchor($this->tbl_hidro_unidade);
		$this->tab_pane->pages->fset_dados_hidro_unidade->anchor($this->fset_detalhes_hidro);
		$this->tab_pane->pages->fset_dados_hidro_unidade->anchor($this->btn_incluir_hidro_unidade);
		
		$this->tab_pane->addPage("fset_dados_morador")->setLabel(__("Dados Morador"))->setWidth(600);
		$this->tab_pane->pages->fset_dados_morador->anchor($this->tbl_morador);
		$this->tab_pane->pages->fset_dados_morador->anchor($this->fset_detalhes_morador);	
		$this->tab_pane->pages->fset_dados_morador->anchor($this->btn_incluir_morador);												
														
		
		$this->build("p4a_fieldset","fset_busca_unidade")
				->setLabel("Procurar")
				->setWidth(350);
				
		$this->build("p4a_field","fld_busca_unidade")
				->setLabel("Buscar Unidade")
				->setTooltip("Digite a Unidade");
	
		$this->build("p4a_button","btn_busca_unidade")
			->implement("onClick",$this,"buscar")
			->setLabel("OK");
		
		$this->fset_busca_unidade->anchor($this->fld_busca_unidade)
								->anchorLeft($this->btn_busca_unidade);
	
			
		$this->setFieldsProperties();
		
		$this->frm->anchorCenter($this->fset_busca_unidade);
		$this->frm->anchorCenter($this->tbl_unidades);
		$this->frm->anchorCenter($this->tab_pane);		

		$this->addObjEsconderEdicao($this->tbl_unidades);
		
		$this->display("main",$this->frm);
		
		$this->display("menu",p4a::singleton()->menu);
		
		$this->display("top",$this->toolbar);
		
		}		
	
	function setFieldsProperties()
		{
		$fields = $this->fields;	

		$fields->cd_condominio->setLabel(__("Condomínio"))
							->setSource(P4A::singleton()->src_condominios_disponiveis)
							->setSourceDescriptionField("desc_condominio")
							->setType("select")
							->setWidth(250);
									
		$fields->cd_unidade->disable()->setLabel(__("Unidade"))
						->setSource($this->src_condominio_unidade)
						->setSourceDescriptionField("cd_unidade")
						->setWidth(30)
						->setType("select");
									
		$fields->cd_pessoa->setLabel(__("Proprietário:"))
						->setSource($this->src_proprietarios)
						->setSourceValueField("cd_pessoa")
						->setSourceDescriptionField("nm_pessoa")
						->setType("select");						
			
		$fields->area_total_unidade->setLabel(__("Área Total (m²)"))
								->setWidth(50);	
								
		$fields->ds_observacao->setLabel(__("Observação"))
			->setType("textarea");						
								
		$fields->st_unidade->setLabel(__("Ativo (?)"));												
		}
		
	function obterTotalUnidades()
		{
		$nr_unidades_condominio = p4a_db::singleton()->fetchOne("select nr_unidade_condominio from tbl_condominio");
		}
	
	
	function habilitaCampoHidrometro()
		{
		if ($this->fld_st_hidrometro->enable())
			{
			$this->fields->nr_hidrometro->setVisible(true);
			}
		}
		
	function novo_cadastro_unidade()
		{
		$this->fset_busca_unidade->setVisible(false);
		$this->fset_unidade_condominio->setVisible(true);
		$this->src_unidades->newRow();	
		}

	function incluirNovoHidro()
		{			
		$this->fset_detalhes_hidro->clean();
	
		$this->fset_detalhes_hidro->setVisible(true);
	
		$this->btn_incluir_hidro_unidade->setInvisible();
		
		$this->build("satecmax_db_source","src_hidrometros_disponiveis")
			->setTable("tbl_hidrometro")
			->setwhere("cd_hidro not in ( select cd_hidro from tbl_hidro_unidade ) and st_hidro = 1")
			->Load();
		
		$this->build("p4a_field","fld_hidro")
			->setLabel("Hidrômetros Disponiveis")
			->setSource($this->src_hidrometros_disponiveis)
			->setSourceValueField("cd_hidro")
			->setSourceDescriptionField("ds_hidro")
			->setType("select");
			
		$this->fset_detalhes_hidro->anchor($this->fld_hidro);
	
		$this->build("P4a_button","btn_salvar_hidro_unidade")
			->setLabel(__("Salvar"),true)
			->setIcon("actions/document-save")
			->implement("onClick",$this,"salvarInclusaoHidrometro");
				
		$this->fset_detalhes_hidro->anchor($this->btn_salvar_hidro_unidade);
	
		$this->build("P4a_button","btn_cancelar_hidro_unidade")
			->setLabel(__("Cancelar"),true)
			->setIcon("actions/edit-undo")
			->implement("onClick",$this,"limpaInclusaoHidrometro");
			
		$this->fset_detalhes_hidro->anchorLeft($this->btn_cancelar_hidro_unidade);	
		}
		
		
	function salvarInclusaoHidrometro()
		{

		$cd_unidade = $this->fields->cd_unidade->getNewValue();
		if ( p4a_db::singleton()->fetchOne("select count(*) from tbl_hidro_unidade where cd_unidade = '{$cd_unidade}'") > 0 )
			{
			$this->error(__("Unidade já relacionada a um hidrômetro!"));
			return false;
			}	
			
		$this->src_hidro_unidade->newRow();
		$this->src_hidro_unidade->fields->cd_hidro->setNewValue($this->fld_hidro->getNewValue());
		$this->src_hidro_unidade->fields->cd_unidade->setNewValue($this->fields->cd_unidade->getNewValue());
		$this->src_hidro_unidade->saveRow();
		
		$this->info(__("Hidrômetro relacionado com sucesso!"));
		$this->limpaInclusaoHidrometro();
		}
		
	function limpaInclusaoHidrometro()
		{
		$this->fset_detalhes_hidro->setInvisible();
		$this->btn_incluir_hidro_unidade->setVisible();
		}
		
	function excluirHidroUnidade()
		{
		$this->src_hidro_unidade->deleteRow();
		$this->info(__("Relacionamento Excluído com sucesso!"));
		}	
	
	function incluirMorador()
		{
		$this->setStatusMode();		
		
		$this->fset_detalhes_morador->clean();
		
		$this->fset_detalhes_morador->setVisible(true);
		
		$this->btn_incluir_morador->setInvisible();
	
		
		
		$this->build("p4a_field","fldMoradorUnidade")
			->setLabel(__("Morador"))
			->setType("select")
			->setSource($this->src_unidade_morador)
			->setSourceValueField("cd_pessoa")
			->setSourceDescriptionField("nm_pessoa");
		
		$this->fset_detalhes_morador->anchor($this->fldMoradorUnidade);
			
		
		$this->build("P4a_button","btn_salvar_morador")
			->setLabel(__("Salvar"),true)
			->setIcon("actions/document-save")
			->implement("onClick",$this,"salvarMorador");
			
		$this->fset_detalhes_morador->anchor($this->btn_salvar_morador);
		
		$this->build("P4a_button","btn_cancelar_morador")
			->setLabel(__("Cancelar"),true)
			->setIcon("actions/edit-undo")
			->implement("onClick",$this,"limpaMorador");
			
		$this->fset_detalhes_morador->anchorLeft($this->btn_cancelar_morador);	
		}		
		
	function excluirmorador()
		{
		$this->src_morador_unidade->deleteRow();
		$this->info(__("Morador excluído com sucesso!"));
		}
		
	function salvarMorador()
		{
		$this->src_morador_unidade->newRow();		
		$this->src_morador_unidade->fields->cd_unidade->setNewValue($this->fields->cd_unidade->getNewValue());
		$this->src_morador_unidade->fields->cd_pessoa->setNewValue($this->fldMoradorUnidade->getNewValue());		
		$this->src_morador_unidade->saveRow();
		
		$this->info(__("Morador cadastrado com sucesso!"));
		$this->limpaMorador();
		}
		
	function limpaMorador()
		{
		$this->setStatusMode();
		
		$this->fset_detalhes_morador->setInvisible();
		$this->btn_incluir_morador->setVisible();
		}
		
	function saveRow()
		{	
		if ( $this->validateFields() and $this->getSource()->isNew() )
			{
			$cd_unidade = $this->fields->cd_unidade->getNewValue();
			if ( p4a_db::singleton()->fetchOne("select count(*) from tbl_unidades  where cd_unidade = '{$cd_unidade}'") > 0 )
				{
				$this->error(__("Unidade ja cadastrada!"));
				return false;
				}
		
		$this->src_unidades->fields->cd_condominio->setNewValue("1");				
			} 
			
		return parent::saveRow();
		}
		
	function buscar()
		{
		$texto_busca = $this->fld_busca_unidade->getNewValue();
		
		$this->getSource()->setWhere("cd_unidade = '{$texto_busca}' ");

		if ( $this->getSource()->getNumRows() == 0)
			{
			$this->error(__(" Nenhuma unidade encontrada! " ));
			}
		}		
		
	function main()
		{	
		parent::main();
		}
	}