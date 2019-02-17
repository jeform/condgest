<?php
class parametros extends satecmax_mask
	{

	function __construct()
		{
		parent::__construct();
		$this->setTitle(__(".: Parâmetros do Sistema :."));
		
		$this->criaFrames();
		$this->montaSources();
		$this->montaToolbar();
		$this->montaTabela();
		$this->montaFields();
		$this->montaMenu();
		}
	
	function criaFrames()
		{
			
		$this->build("p4a_fieldset","fset_busca")
			->setLabel(__("Buscar"))
			->setWidth(1000)
			->setInvisible();
			
		$this->build("p4a_fieldset","fset_tabela")
			->setLabel(__("Tabela de Navegação de Registros"))
			->setWidth(1000)
			->setInvisible();
			
		$this->build("p4a_fieldset","fset_edicao")
			->setLabel(__("Editar Dados"))
			->setWidth(1000)
			->setInvisible();
			
		$this->build("p4a_frame","frm")
			->setWidth(1024)
			->anchor($this->fset_busca)
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
			->setTable("parametros")
			->Load();
			
		$this->setSource($this->source);
		}
	
	function montaTabela()
		{
		$this->build("p4a_table","table")
			->setSource($this->source);

		$this->table->setVisibleCols(array("nm_param","tp_param","vl_param"));

		$this->table->cols->nm_param->setLabel(__("Nome Parâmetro"));
		
		$this->table->cols->tp_param->setLabel(__("Tipo Parâmetro"))
									->setSource(p4a::singleton()->srcTpParametros)
									->setSourceDescriptionField("descParam");

		$this->table->cols->vl_param->setLabel(__("Valor Parâmetro"));

		
		$this->fset_tabela->setVisible();
		$this->fset_tabela->clean();
		$this->fset_tabela->anchor($this->table);

		}
		
	function montaFields()
		{
		

		$this->fields->nm_param->setLabel(__("Nome Parâmetro"));
		$this->fields->nm_param->setWidth(300);
		$this->fields->nm_param->enable(1);
		$this->fields->nm_param->setType("text");
		$this->fields->nm_param->setProperty("maxlength","255");
		
		$this->fset_edicao->anchor($this->fields->nm_param);
		
		$this->fields->tp_param->setLabel(__("Tipo Parâmetro"));
		$this->fields->tp_param->setWidth(300);
		$this->fields->tp_param->enable(1);
		$this->fields->tp_param->setType("select");
		$this->fields->tp_param->setSource(P4A::singleton()->srcTpParametros);
		$this->fields->tp_param->setProperty("maxlength","255");
		
		$this->fset_edicao->anchor($this->fields->tp_param);
			
		$this->fields->vl_param->setLabel(__("Valor Parâmetro"));
		$this->fields->vl_param->setWidth(300);
		$this->fields->vl_param->enable(1);
		$this->fields->vl_param->setType("text");
		$this->fields->vl_param->setProperty("maxlength","255");
		
		$this->fset_edicao->anchor($this->fields->vl_param);
		
		$this->fset_edicao->setVisible();
		}
		
	
	function montaMenu()
		{

		$this->display("menu",p4a::singleton()->menu);
		
		}
		
	function saveRow()
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
		
		
	/*function getValorParametro($nm_param)
		{
		$valor_parametro = p4a_db::singleton()->getOne("select vl_param from param_portalunicred where nm_param = '{$nm_param}'");
		
		if ( $valor_parametro == "" )
			{
			throw new P4A_Exception("Nenhum valor configurado para o parametro solicitado {$nm_param}!");
			}
		return $valor_parametro;
		}*/
	}
		