<?php
class controle_caixa extends satecmax_mask
	{
		
	function __construct()
		{
		parent::__construct();
		
		$this->setTitle(__("Cadastro de Caixa"));
		
		$this->build("satecmax_db_source","src_caixa")
			->setTable("caixa")
			->setPk("cd_caixa")
			->Load()
			->firstRow();
			
		$this->build("satecmax_db_source","src_contas_correntes_cadastradas")
			->setFields(array("cd_conta_corrente","concat_ws(' - ',cod_banco,ag_banco,nr_conta_banco)"=>"desc_conta_corrente"))
			->setTable("tbl_conta_corrente")
			->Load();

		$arr_tp_caixa[] = array("tipo_caixa"=>"0","caixa"=>"Em espécie");
		$arr_tp_caixa[] = array("tipo_caixa"=>"1","caixa"=>"Banco");
			
		$this->build("p4a_array_source","arr_source_tp_caixa")
			->Load($arr_tp_caixa)
			->setPk("tipo_caixa");		
			
		$this->build("p4a_table","tbl_caixa")
			->setSource($this->src_caixa)
			->setLabel(__("Caixas cadastrados"))
			->setWidth(500);

		$this->tbl_caixa->cols->cd_caixa->setLabel(__("Cód."));
		$this->tbl_caixa->cols->tp_caixa->setLabel(__("Tipo"))
										->setSource($this->arr_source_tp_caixa)
										->setSourceValueField("tipo_caixa")
										->setSourceDescriptionField("caixa");
		$this->tbl_caixa->cols->ds_caixa->setLabel(__("Descrição"));
		$this->tbl_caixa->cols->cd_conta_corrente->setLabel(__("Conta Corrente"))
												->setSource(P4A::singleton()->src_conta_banco)
												->setSourceValueField("cd_conta_corrente")
												->setSourceDEscriptionField("conta_cadastrada");
		$this->tbl_caixa->cols->st_caixa->setLabel(__("Status"));
		
		
		$this->tbl_caixa->setVisibleCols(array("cd_caixa","tp_caixa","ds_caixa","cd_conta_corrente","st_caixa"));
		
		$this->setSource($this->src_caixa);			
		
		$this->build("satecmax_full_toolbar","toolbar")
			->setMask($this);
			
		$this->build("p4a_frame","frm")
			->setWidth(1024);
			
		$this->build("p4a_fieldset","fset_caixa")
			->setLabel(__("Cadastro de Caixa"))
			->setWidth(500);
			
		$this->fset_caixa->anchor($this->fields->cd_caixa)
						->anchor($this->fields->tp_caixa)
						->anchorLeft($this->fields->cd_conta_corrente)
						->anchor($this->fields->ds_caixa)
						->anchor($this->fields->dt_saldo_inicial)
						->anchor($this->fields->vlr_saldo_inicial)
						->anchor($this->fields->st_caixa);
		
		$this->setFieldsProperties();
		
		
		$this->frm->anchorCenter($this->tbl_caixa);
		$this->frm->anchorCenter($this->fset_caixa);	

		
		
		$this->display("main",$this->frm);
		
		$this->display("menu",p4a::singleton()->menu);
		
		$this->display("top",$this->toolbar);
		
		}
		
	
	function setFieldsProperties()			
		{
		$fields = $this->fields;
		
		$fields->cd_caixa->setLabel(__("Cód."))
						->enable(false)
						->setWidth(50);		
			
		$fields->tp_caixa->setLabel(__("Tipo"))
			->setSource($this->arr_source_tp_caixa)
			->setSourceValueField("tipo_caixa")
			->setSourceDescriptionField("caixa")
			->setType("select")
			->allowNull("Selecione")
			->addAction("onChange");			
			
		$fields->cd_conta_corrente->setLabel(__("Conta Corrente"))
				->setSource(P4A::singleton()->src_conta_banco)
				->setSourceValueField("cd_conta_corrente")
				->setSourceDEscriptionField("conta_cadastrada")
				->setType("select");	
			
		$fields->ds_caixa->setLabel(__("Descrição"))
						->setWidth(300	);
		
		$fields->dt_saldo_inicial->setLabel(__("Dt. Saldo Inicial"))
								->setWidth(100);
								
		$fields->vlr_saldo_inicial->setLabel(__("Saldo Inicial"))
								->setWidth(100);

		$fields->st_caixa->setLabel(__("Status"));						
		}
		
	function main()
		{
		$this->fields->cd_conta_corrente->setVisible($this->fields->tp_caixa->getNewValue() == "1");	
			
		parent::main();
		}	
		
	}