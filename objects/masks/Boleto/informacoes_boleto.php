<?php
class informacoes_boleto extends satecmax_mask
	{
	function __construct()
		{
		parent::__construct();

		$this->setTitle(__("Informações do Boleto"));
		
		$this->build("P4a_frame","frm")
			->setWidth(1024);
		
		$this->build("satecmax_full_toolbar","toolbar")
			->setMask($this);
		
		
		$this->build("satecmax_db_source","src_informacoes_boleto")
			->setTable("tbl_info_boleto")
			->setPK("cd_boleto")
			->Load()
			->firstRow();
			
		$this->build("p4a_table","tbl_informacoes_boleto")
			->setSource($this->src_informacoes_boleto)
			->setWidth(420);
		
		$this->setSource($this->src_informacoes_boleto);
		
		$this->build("p4a_fieldset","fset_informacoes_boleto")
			->setLabel(__("Detalhes"))
			->setWidth(600);
		
		$this->fset_informacoes_boleto->anchor($this->fields->agencia)
									->anchorLeft($this->fields->agencia_dv)
									->anchor($this->fields->conta)
									->anchorLeft($this->fields->conta_dv)
									->anchor($this->fields->conta_cedente)
									->anchorLeft($this->fields->conta_cedente_dv)
									->anchor($this->fields->carteira)
									->anchor($this->fields->instrucao_1)
									->anchor($this->fields->instrucao_2)
									->anchor($this->fields->instrucao_3)
									->anchor($this->fields->instrucao_4);		
		
		$this->setFieldsProperties();
		
		$this->frm->anchorCenter($this->fset_informacoes_boleto);
		
		$this->display("main",$this->frm);
		
		$this->display("menu",p4a::singleton()->menu);
		
		$this->display("top",$this->toolbar);		
		}

	function setFieldsProperties()
		{
		$fields = $this->fields;

		$fields->agencia->setLabel(__("Agência"))
							->setWidth(60);

		$fields->agencia_dv->setLabel(__("Agência Díg. Verificador"))
						->setWidth(30)
						->label->setWidth(180);
		
		$fields->carteira->setLabel(__("Carteira"))
									->setWidth(30);	

		$fields->conta->setLabel(__("Conta"))
					->setWidth(60);
		
		$fields->conta_dv->setLabel(__("Conta Díg. Verificador"))
						->setWidth(30)
						->label->setWidth(180);
	
		$fields->conta_cedente->setLabel(__("Conta Cedente"))
							->setWidth(60);
		
		$fields->conta_cedente_dv->setLabel(__("Conta Cedente Díg. Verificador"))
								->setWidth(30)
								->label->setWidth(180);
		
		$fields->instrucao_1->setLabel(__("Instruções 1"))
							->setWidth(450);

		$fields->instrucao_2->setLabel(__("Instruções 2"))
							->setWidth(450);
		
		$fields->instrucao_3->setLabel(__("Instruções 3"))
							->setWidth(450);
		
		$fields->instrucao_4->setLabel(__("Instruções 4"))
							->setWidth(450);
		
		}
		
		/*		
	function saveRow()
		{
		if ( $this->validateFields() and $this->getSource()->isNew() )
			{
			$mes_ano_referencia = $this->fields->mes_ano_referencia->getNewValue();
			if ( p4a_db::singleton()->fetchOne("select count(*) from tbl_correcao_monetaria where mes_ano_referencia = '{$mes_ano_referencia}'") > 0 )
				{
				$this->error(__("Mês/Ano Referência já cadastrado!"));
				return false;
				}
			}
				
		return parent::saveRow();
		}	 */
}	