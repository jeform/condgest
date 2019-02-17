<?php
class satecmax_db_source extends P4A_DB_Source
	{
	private $_st_log = true;
	
	public $reg_log = array();
	
	private $_tables_metadataSatecMax = array();
	
	/**
	 * Função para habilitar o registro de log de alteracoes
	 * @param boolean $status
	 * @return void
	 */
	function enableLog($status = true)
		{
		$this->_st_log = $status;
		return $this;
		}
		
	/**
	 * Funcao para desabilitar o registro de log de alteracoes
	 * @return void
	 */
	function disableLog()
		{
		$thi->_st_log = false;
		return $this;
		}
		
	/**
	 * (non-PHPdoc)
	 * @see classes/p4a-3.2.2/p4a/objects/data_sources/P4A_DB_Source#saveRow()
	 */
	function saveRow($fields_values = array(), $pk_values = array())
		{
		$valor_chave = $this->monta_valor_chave_log();
		if ( $this->_st_log )
			{
			$this->reg_log = $this->verifica_log();
			}
		$is_new = $this->isNew();	
		parent::saveRow($fields_values,$pk_values);
		$this->registraLogAtualizacao($is_new,$valor_chave);
		}
		
	/**
	 * Retorna array dos campos alterados 
	 * @return array
	 */
	function verifica_log()
		{
		$arr_fields = $this->fields;
		
		$arr_log = array();
		while($field = &$arr_fields->nextItem())
			{
			if ( $field->getValue() <> $field->getNewValue() )
				$arr_log[$field->getName()] = array("valor_antigo"=>$field->getValue(),"valor_novo"=>$field->getNewValue());
			}
		return $arr_log;
		}
		
	/**
	 * Serializa a identificacao do registro a ser registrado no log
	 * @return string
	 */
	function monta_valor_chave_log()
		{
		$campos_chave = $this->getPk();
		
		if ( is_array($campos_chave))
			{
			$arr_campos_chave = $this->getPkValues();
			}
		else
			{
			$valor_chave = $this->getPkValues();
			$arr_campos_chave = array($campos_chave=>$valor_chave);
			}
			
		return serialize($arr_campos_chave);
		}

	/**
	 * Registra log das alteracoes
	 * @return void
	 */
	function registraLogAtualizacao($is_new = true, $valor_chave)
		{
		$db_log = P4A_DB::singleton(P4A_DSN_LOG_ALTERACAO_TABELA);
		
		if ( $is_new )
			{
			$valor_chave = $this->monta_valor_chave_log();
			}
		
		$arr_alteracao = $this->reg_log;
		if ( count($arr_alteracao) )
			{
			$cd_usuario = 99;
			if ( isset(p4a::singleton()->user_login) and is_object(p4a::singleton()->user_login ) )
				{
				$cd_usuario = p4a::singleton()->user_login->fields->cd_usuario->getValue();	
				}
				
			foreach($arr_alteracao as $campo =>$valores )
				{
				if ( is_array($valores["valor_antigo"]) )
					$valores["valor_antigo"] = serialize($valores["valor_antigo"]);
					
				if ( is_array($valores["valor_novo"]) )
					$valores["valor_novo"] = serialize($valores["valor_novo"]);
					
				$arr_insert = array("nm_tabela"=>$this->getTable(),
									"valores_chave"=>$valor_chave,
									"tp_alteracao"=>$is_new?"I":"A",
									"nm_campo_alteracao"=>$campo,
									"valor_antigo"=>$is_new?"NV":$valores["valor_antigo"],
									"valor_novo"=>$valores["valor_novo"],
									"dt_hr_alteracao"=>date("Y-m-d H:i:s"),
									"cd_usuario"=>$cd_usuario
									);
				$db_log->adapter->insert("log_alteracao_tabela",$arr_insert);								
				}
			}
		}
		
	/**
	 * Registra log das exclusao a serem feitas...
	 * @return void
	 */
	function registraLogExclusao()
		{
		$db_log = P4A_DB::singleton(P4A_DSN_LOG_ALTERACAO_TABELA);
		
		while($field = $this->fields->nextItem())
			{
			$cd_usuario = 99;
			if ( is_object(p4a::singleton()->user_login ) )
				{
				$cd_usuario = p4a::singleton()->user_login->fields->cd_usuario->getValue();	
				}
				
			$arr_insert = array("nm_tabela"=>$this->getTable(),
								"valores_chave"=>$this->monta_valor_chave_log(),
								"tp_alteracao"=>"E",
								"nm_campo_alteracao"=>$field->getName(),
								"valor_antigo"=>$field->getValue(),
								"valor_novo"=>"EX",
								"dt_hr_alteracao"=>date("Y-m-d H:i:s"),
								"cd_usuario"=>$cd_usuario
								);
								
			$db_log->adapter->insert("log_alteracao_tabela",$arr_insert);								
			}
		}
		
	/**
	 * (non-PHPdoc)
	 * @see classes/p4a-3.2.2/p4a/objects/data_sources/P4A_DB_Source#deleteRow()
	 */
	function deleteRow()
		{
		if ( $this->_st_log )
			{
			$this->registraLogExclusao();
			}
			
		parent::deleteRow();
		}
		
	function getMetaDataInfo()
		{
			
		$db = P4A_DB::singleton($this->getDSN());
		
		$select = $this->_composeSelectStructureQuery();
		$main_table = $this->getTable();
		
		// retrieving tables metadata 
		foreach ($select->getPart('from') as $alias=>$table_data) 
			{
			$p4a_db_table = new P4A_Db_Table(array('name'=>$table_data['tableName'], 'schema'=>$table_data['schema'], 'db'=>$db->adapter));
			$this->_tables_metadataSatecMax[$table_data['tableName']] = $p4a_db_table->info();
			}
		
		return $this->_tables_metadataSatecMax;	
		}
	}