<?php

class cadastro_itens_cobranca_boleto extends satecmax_mask
	{

	function __construct()
		{
		parent::__construct();
		$this->setTitle(__("Cadastro de Ítens de Cobrança"));
		
		$this->criaFrames();
		$this->montaSources();
		$this->montaToolbar();
		$this->montaTabela();
		$this->montaFields();
		$this->montaMenu();
		}
	
	function criaFrames()
		{			
		$this->build("p4a_fieldset","fset_tabela")
			->setWidth(800)
			->setInvisible();
			
		$this->build("p4a_fieldset","fset_edicao")
			->setLabel(__("Editar Dados"))
			->setWidth(500)
			->setInvisible();			
			
		$this->build("p4a_frame","frm")
			->setWidth(1024)
			->anchor($this->fset_tabela)
			->anchor($this->fset_edicao);
			
		$this->display("main",$this->frm);
						
		}
	
	function montaToolbar()
		{

		$this->build("satecmax_Full_Toolbar","toolbar")
			->setMask($this);
			
		$this->display("top",$this->toolbar);
		
		}

	function montaSources()
		{
		$this->build("satecmax_db_source","source")
			->setTable("tbl_itens_cobranca_boleto")
			->setPk("cd_item_cobranca")
			->Load();
			
		$arr_tipo_valor[] = array("cd_tipo_valor"=>0, "desc"=>"Variável");
		$arr_tipo_valor[] = array("cd_tipo_valor"=>1, "desc"=>"Fixo");
		$arr_tipo_valor[] = array("cd_tipo_valor"=>3, "desc"=>"Rateio");
		
		$this->build("p4a_array_source","src_tipo_valor")
			->setPk("cd_tipo_valor")
			->Load($arr_tipo_valor);	
		
		$this->build("satecmax_db_source","src_categoria_boleto")
			->setTable("tbl_categorias")
			->setPk("cd_categoria")
			->setWhere("tp_categoria = 1")
			->Load()
			->FirstRow();	
			
		$this->setSource($this->source);
		}
	
	function montaTabela()
		{
		$this->build("p4a_table","table")
			->setWidth(800)
			->setSource($this->source);

		$this->table->setVisibleCols(array("ds_item_cobranca","tp_valor","vlr_item_cobranca","cd_categoria","cd_hist_padrao","cd_caixa"));		

		$this->table->cols->ds_item_cobranca->setLabel(__("Descrição"));

		$this->table->cols->vlr_item_cobranca->setLabel(__("Valor (R$)"));
		
		$this->table->cols->tp_valor->setLabel(__("Tipo"))
								->setSource($this->src_tipo_valor)
								->setSourceValueField("cd_tipo_valor")
								->setSourceDescriptionField("desc");
								
		$this->table->cols->cd_categoria->setLabel(__("Categoria"))						
									->setSource($this->src_categoria_boleto)
									->setSourceValueField("cd_categoria")
									->setSourceDescriptionField("ds_categoria");
	
		$this->table->cols->cd_hist_padrao->setLabel(__("Hist. Padrão"))
										->setSource(P4A::singleton()->src_hist_padrao)
										->setSourceValueField("cd_hist_padrao")
										->setSourceDescriptionField("desc_hist_padrao");
		
		$this->table->cols->cd_caixa->setLabel(__("Caixa"))
								->setSource(P4A::singleton()->src_caixas_cadastrados)	
								->setSourceValueField("cd_caixa")
								->setSourceDescriptionField("desc_caixa");
		
		$this->fset_tabela->setVisible();
		$this->fset_tabela->clean();
		$this->fset_tabela->anchor($this->table);

		}
		
	function montaFields()
		{
		$this->fields->ds_item_cobranca->setLabel(__("Descrição"));
		$this->fields->ds_item_cobranca->setWidth(300);
		$this->fields->ds_item_cobranca->enable(1);
		$this->fields->ds_item_cobranca->setType("text");
		$this->fields->ds_item_cobranca->setProperty("maxlength","255");
		
		$this->fset_edicao->anchor($this->fields->ds_item_cobranca);
	
		$this->fields->tp_valor->setLabel(__("Tipo"));
		$this->fields->tp_valor->setSource($this->src_tipo_valor);
		$this->fields->tp_valor->setSourceValueField("cd_tipo_valor");
		$this->fields->tp_valor->setSourceDescriptionField("desc");
		$this->fields->tp_valor->setType("radio");
		$this->fields->tp_valor->setWidth(200);
		$this->fields->tp_valor->enable(1);
		$this->fields->tp_valor->setType("radio");
		
		$this->fset_edicao->anchor($this->fields->tp_valor);
		
		$this->fields->vlr_item_cobranca->setLabel(__("Valor em R$"));
		$this->fields->vlr_item_cobranca->setWidth(100);
		$this->fields->vlr_item_cobranca->enable(1);
		$this->fields->vlr_item_cobranca->setType("text");
		$this->fields->vlr_item_cobranca->setProperty("maxlength","255");
		
		$this->fset_edicao->anchor($this->fields->vlr_item_cobranca);
		
		$this->fields->cd_categoria->setLabel(__("Categoria"));			
		$this->fields->cd_categoria->setWidth(250);
		$this->fields->cd_categoria->setSource($this->src_categoria_boleto);
		$this->fields->cd_categoria->setSourceValueField("cd_categoria");
		$this->fields->cd_categoria->setSourceDescriptionField("ds_categoria");
		$this->fields->cd_categoria->setType("select");
		
		$this->fset_edicao->anchor($this->fields->cd_categoria);
		
		$this->fields->cd_hist_padrao->setLabel(__("Hist. Padrão"));
		$this->fields->cd_hist_padrao->setWidth(250);
		$this->fields->cd_hist_padrao->setSource(P4A::singleton()->src_hist_padrao);
		$this->fields->cd_hist_padrao->setSourceValueField("cd_hist_padrao");
		$this->fields->cd_hist_padrao->setSourceDescriptionField("desc_hist_padrao");
		$this->fields->cd_hist_padrao->setType("select");
		
		$this->fset_edicao->anchor($this->fields->cd_hist_padrao);
		
		$this->fields->cd_caixa->setLabel(__("Caixa"));
		$this->fields->cd_caixa->setWidth(250);
		$this->fields->cd_caixa->setSource(P4A::singleton()->src_caixas_cadastrados);
		$this->fields->cd_caixa->setSourceValueField("cd_caixa");
		$this->fields->cd_caixa->setSourceDescriptionField("desc_caixa");
		$this->fields->cd_caixa->setType("select");
		
		$this->fset_edicao->anchor($this->fields->cd_caixa);
		
		$this->fset_edicao->setVisible();
		}
		
	
	function montaMenu()
		{

		$this->display("menu",p4a::singleton()->menu);
		
		}
		
/*	function saveRow()
		{
		if ( $this->getSource()->isNew())
			{
			$this->fields->nm_param->setNewValue(mb_strtoupper($this->fields->nm_param->getNewValue()));
			}
			
		return parent::saveRow();
		}
		
	function main()
		{
		$this->fields->nm_param->enable($this->getSource()->isNew());
		
		parent::main();
		}
		
		
	function getValorParametro($nm_param)
		{
		$valor_parametro = p4a_db::singleton()->getOne("select vl_param from param_portalunicred where nm_param = '{$nm_param}'");
		
		if ( $valor_parametro == "" )
			{
			throw new P4A_Exception("Nenhum valor configurado para o parametro solicitado {$nm_param}!");
			}
		return $valor_parametro;
		}*/
	}
		