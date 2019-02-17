<?php
class satecmax_mask extends P4A_Mask
	{
	/**
	 * Status de Edição se false desabilita todos os fields se nao, habilita
	 * @var st_mode
	 */
	private $st_mode = false;
	
	/**
	 * Status se é a primeira vez que exibe a mask
	 * @var boolean
	 */
	private $st_first_main = true;
	
	private $data_source;
	private $fields_disable = array();

	/**
	 * 
	 * @var p4a_collection
	 */
	private $_obj_esconder;
	/**
	 * 
	 * @var unknown_type
	 */
	private $_obj_table_invisible;
	
	
	/**
	 * 
	 * @var unknown_type
	 */
	private $_obj_fset_search;
	
	
	private $_st_destroy_mask = true;
	
	private $_st_required_auto = true;
	
	private $_required_field_vm = array();
	
	/**
	 * Definição se a mascara esta sendo renderizada
	 * @var boolean
	 */
	private $st_render_mask = false;
	
	private $_tpl_varsSatecMax = array();
	
	/**
	 * 
	 * @param $status
	 * @return void
	 */
	function __construct($name=null)
		{
		parent::__construct($name);
		
		$this->close_popup_button->implement("onclick",$this,"showPrevMask");
		}
		
	function setStatusMode()
		{
		$this->st_mode = !$this->st_mode;
		}
	
	function getStatusMode()
		{
		return $this->st_mode;
		}
		
	function main()
		{
		$this->st_render_mask = true;
		$disable_button = true;
		$data_source = $this->getSource();
		
		// pegar todos os fields desabilitados se houverem
		if ( $data_source )
			{	
			if ( $this->st_first_main )
				{
				while( $field = $this->fields->nextItem() )
					{
					if ( !$field->isEnabled())
						$this->fields_disable[] = $field->getName();
					}
				$this->st_first_main = false;
				}
				
			$this->fields->reset();
				
			if ( !$this->st_mode and !$data_source->isNew() or $data_source->getWhere() == "1=0" )
				{
				// procurar todos os fields vinculados ao source e inabilita-lo
				while($field = $this->fields->nextItem())
					{
					$field->disable();
					}
				$disable_button = false;
				}
			else
				{
				// procurar todos os fields vinculados ao source e inabilita-lo
				while($field = $this->fields->nextItem())
					{
					
					if (!in_array($field->getName(),$this->fields_disable))
						$field->enable();
					}
				}
			// desabilitar os botoes quando utilizando o satecmax_full_toolbar
			$tpl_vars = $this->getTplVars();
			if ( isset($tpl_vars["top"]) and get_class($tpl_vars["top"])=="satecmax_full_toolbar" )
				{
				$satecmax_toolbar = $tpl_vars["top"];
				$satecmax_toolbar->buttons->edit->setVisible(true);			
				$satecmax_toolbar->buttons->save->enable($disable_button);
				$satecmax_toolbar->buttons->cancel->enable($disable_button);
				$satecmax_toolbar->buttons->new->enable(true);
				$satecmax_toolbar->buttons->edit->enable(true);
				$satecmax_toolbar->buttons->delete->enable(true);
				
				if ( !$data_source->getNumRows() )
					{ 
					$satecmax_toolbar->buttons->delete->disable();
					$satecmax_toolbar->buttons->edit->disable();			
					}
						
				if ( $disable_button )
					{ 
					$satecmax_toolbar->buttons->cancel->enable(true);
					$satecmax_toolbar->buttons->new->enable(false);
					$satecmax_toolbar->buttons->edit->enable(false);
					$satecmax_toolbar->buttons->exit->enable($data_source->getNumRows()>0?false:true);
					}
				else
					{			
					$satecmax_toolbar->buttons->edit->enable(true);			
					$satecmax_toolbar->buttons->exit->enable(true);
					}						
				}
				
			// aplica perfil
			$this->aplicaPerfis();
			
			if ( $this->_obj_table_invisible )
				{
				$this->_obj_table_invisible->setVisible(!$disable_button);
				}
				
			if ( $this->_obj_fset_search )
				{
				$this->_obj_fset_search->setVisible(!$disable_button);

				}
				
			if ( count($this->_obj_esconder))
				{
				foreach($this->_obj_esconder as $obj )
					{
					$obj->setVisible(!$disable_button);
					}
				}
			}

		parent::main();
		}
		
	/**
	 * 
	 * @param $obj_table_table
	 * @return unknown_type
	 */
	function setObjTable(p4a_table $obj_table)
		{
		$this->addObjEsconderEdicao($obj_table);
		}
		
	/**
	 * 
	 * @param $obj_fset_search
	 * @return unknown_type
	 */
	function setObjFsetSearch(p4a_fieldset $obj_fset_search)
		{
		$this->addObjEsconderEdicao($obj_fset_search);
		}

	function reloadRow()
		{
		parent::reloadRow();
		$this->st_mode = false;
		}
		
	function addObjEsconderEdicao(P4A_Widget $obj)
		{
		$this->_obj_esconder[] = $obj;
		}
		
	function setDestroyMask($status = true)
		{
		$this->_st_destroy_mask = $status;
		}
		
	function showPrevMask()
		{
		parent::showPrevMask();
		if ( $this->_st_destroy_mask)
			$this->destroy();
		}
		
	function saveRow()
		{
		if ( $this->validateFields())
			{
			parent::saveRow();
			if ( $this->st_render_mask )
				$this->info(__("Registro Atualizado com sucesso!"));
			$this->st_mode = false;
			return true;
			}
		else
			{
			if ( $this->st_render_mask )
				$this->error(__("Preencha todos os campos necessarios"));
			return false;
			}
		}
		
	function deleteRow()
		{
		parent::deleteRow();
		if ( $this->getSource()->getNumRows() == 0)
			{
			$this->getSource()->setWhere(null);
			$this->getSource()->Load();
			$this->getSource()->firstRow();
			}
			
		if ( $this->st_render_mask )
			$this->info(__("Registro Excluido com Sucesso!"));
		}
	
	function enableRequiredAuto($status=true)
		{
		$this->_st_required_auto = $status;
		}
		
	function disableRequiredAuto()
		{
		$this->_st_required_auto = false;
		}
		
	function setSource($data_source)
		{
		parent::setSource($data_source);
		
		if ( $this->_st_required_auto and $data_source instanceof satecmax_db_source )
			{
			$metadata_source = $data_source->getMetaDataInfo();
			while($field = $this->fields->nextItem() )
				{
				if ( isset($metadata_source[$data_source->getTable()]["metadata"][$field->getName()]["NULLABLE"]) 
					 and !$metadata_source[$data_source->getTable()]["metadata"][$field->getName()]["NULLABLE"] 
					 and !$metadata_source[$data_source->getTable()]["metadata"][$field->getName()]["IDENTITY"]
					 )
					{
					$this->setRequiredField($field->getName());
					}
				}
			}
		}
	
	function setRequiredField($field)
		{
			/**
			 * TODO: Registrar na mascara os objetos que deverao ser validadados...
			 */
		//$this->_required_field_vm[] = is_string($field)?:"";
		parent::setRequiredField($field);
		return $this;
		}
		
	function aplicaPerfis()
		{
		// desabilitar os botoes quando utilizando o satecmax_full_toolbar
			
		}
		
	function display($variable, $object)
		{
		$this->_tpl_varsSatecMax[$variable] =& $object;
		parent::display($variable, $object);
		return $this;
		}
		
	function getTplVars()
		{
		return $this->_tpl_varsSatecMax;
		}
	}