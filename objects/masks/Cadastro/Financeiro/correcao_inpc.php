<?php
class correcao_inpc extends satecmax_mask
	{
	function __construct()
		{
		parent::__construct();

		$this->setTitle(__(".: Cadastro de Índice Nacional de Preços ao Consumidor- INPC :."));
		
		$this->build("P4a_frame","frm")
			->setWidth(1024);
		
		$this->build("satecmax_full_toolbar","toolbar")
			->setMask($this);
		
		$this->build("satecmax_db_source","src_correcao_monetaria")
			->setTable("tbl_inpc")
			->setPK("cd_correcao")
			->Load()
			->addOrder("cd_correcao","desc")
			->firstRow();
			
		$this->build("p4a_table","tbl_correcao_monetaria")
			->setSource($this->src_correcao_monetaria)
			->setWidth(420);
	
		$this->tbl_correcao_monetaria->cols->mes_ano_referencia->setLabel(__("Mês/Ano Referência"));
		$this->tbl_correcao_monetaria->cols->indice_correcao->setLabel(__("Índice do mês (em %)"));
		
		$this->tbl_correcao_monetaria->setVisibleCols(array("mes_ano_referencia","indice_correcao"));
		
		$this->setSource($this->src_correcao_monetaria);
		
		$this->build("p4a_fieldset","fset_correcao_monetaria")
			->setLabel(__("Detalhes"))
			->setWidth(300);
		
		$this->fset_correcao_monetaria->anchor($this->fields->mes_ano_referencia)
										->anchor($this->fields->indice_correcao);
		
		$this->setFieldsProperties();
			
		$this->frm->anchorCenter($this->tbl_correcao_monetaria);
		$this->frm->anchorCenter($this->fset_correcao_monetaria);
		
		$this->addObjEsconderEdicao($this->tbl_correcao_monetaria);
		
		$this->display("main",$this->frm);
		
		$this->display("menu",p4a::singleton()->menu);
		
		$this->display("top",$this->toolbar);		
		}

	function setFieldsProperties()
		{
		$fields = $this->fields;

		$fields->mes_ano_referencia->setLabel(__("Mês/Ano Referência"))
							->setWidth(80)
							->setInputMask("99/9999")
							->label->setWidth(120);
						
		$fields->indice_correcao->setLabel(__("Índice do Mês"))
									->setWidth(80)
									->label->setWidth(120)
									->setProperty("dir","rtl");
	
		}
		
	function saveRow()
		{
		if ( $this->validateFields() and $this->getSource()->isNew() )
			{
			$mes_ano_referencia = $this->fields->mes_ano_referencia->getNewValue();
			if ( p4a_db::singleton()->fetchOne("select count(*) from tbl_inpc where mes_ano_referencia = '{$mes_ano_referencia}'") > 0 )
				{
				$this->error(__("Mês/Ano Referência já cadastrado!"));
				return false;
				}
			}
				
		return parent::saveRow();
		}	
}	