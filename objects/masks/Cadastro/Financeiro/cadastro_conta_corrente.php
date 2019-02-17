<?php
class cadastro_conta_corrente extends satecmax_mask
	{
	public $condicao;	
		
	function __construct()
		{
		parent::__construct();
		
		$this->setTitle(__("Cadastro de Conta Corrente"));
		
		$this->build("satecmax_db_source","src_conta_corrente")
			->setTable("tbl_conta_corrente")
			->setPk("cod_banco")
			->Load()
			->firstRow();
					
		$this->build("p4a_table", "tbl_conta_corrente")
			->setSource($this->src_conta_corrente)
			->setLabel(__("Lista de Bancos Cadastrados"))
			->setWidth(500);
				
		$this->build("satecmax_db_source","src_bancos_disponiveis")	
			->setFields(array("cd_banco","concat_ws(' - ',nr_banco,desc_banco)"=>"nm_banco"))
			->setTable("tbl_bancos")
			->setwhere("cd_banco in ( select cd_banco from tbl_bancos)")
			->Load();		
			
		$this->build("p4a_fieldset","fset_conta_corrente")
			->setLabel(__("Detallhes"))
			->setWidth(500);
			
		$this->tbl_conta_corrente->cols->cod_banco->setLabel(__("Banco"))
												->setSource($this->src_bancos_disponiveis)
												->setSourceValueField("cd_banco")
												->setSourceDescriptionField("nm_banco");	
		$this->tbl_conta_corrente->cols->ag_banco->setLabel(__("Agência"));
		$this->tbl_conta_corrente->cols->nr_conta_banco->setLabel(__("Número"));
		
		$this->tbl_conta_corrente->setVisibleCols(array("cod_banco","ag_banco","nr_conta_banco"));		

		$this->setSource($this->src_conta_corrente);			
			
		$this->build("satecmax_full_toolbar","toolbar")
			->setMask($this);

		$this->toolbar->buttons->new->implement("onClick",$this,"novo_cadastro_conta_corrente");		
			
		$this->build("p4a_frame","frm")
			->setWidth(1024);
			
		$this->fset_conta_corrente
			->anchor($this->fields->cd_conta_corrente)
			->anchor($this->fields->cod_banco)
			->anchor($this->fields->ag_banco)
			->anchor($this->fields->nr_conta_banco)
			->anchor($this->fields->natureza_conta)
			->anchor($this->fields->tipo_conta)
			->anchor($this->fields->dt_abertura)
			->anchor($this->fields->dt_encerramento)
			->anchor($this->fields->st_principal)
			->anchor($this->fields->st_ativo);
			
			
		$arr_natureza_conta[] = array("natureza_conta"=>0, "desc"=>"Física");
		$arr_natureza_conta[] = array("natureza_conta"=>1, "desc"=>"Jurídica");
		
		$this->build("p4a_array_source","src_natureza_conta")
			->Load($arr_natureza_conta)
			->setPk("natureza_conta");		

		$arr_tipo_conta[] = array("tipo_conta"=>0, "desc"=>"Conta Corrente");	
		$arr_tipo_conta[] = array("tipo_conta"=>1, "desc"=>"Poupança");
		$arr_tipo_conta[] = array("tipo_conta"=>2, "desc"=>"Aplicação");

		$this->build("p4a_array_source","src_tipo_conta")
			->Load($arr_tipo_conta)
			->setPk("tipo_conta");
			
			
		$this->setFieldsProperties();
		
		$this->addObjEsconderEdicao($this->tbl_conta_corrente);
		
		$this->frm->anchorCenter($this->tbl_conta_corrente);
		$this->frm->anchorCenter($this->fset_conta_corrente);		
		
		
		$this->display("main",$this->frm);
		
		$this->display("menu",p4a::singleton()->menu);
		
		$this->display("top",$this->toolbar);
		
		}
		
	
	function setFieldsProperties()			
		{
		$fields = $this->fields;
		
		$fields->cd_conta_corrente->setLabel(__("Código:"))
							->setWidth(50)
							->enable(false);	
							
		$fields->cod_banco->setLabel(__("Banco:"))
						->setSource($this->src_bancos_disponiveis)
						->setSourceValueField("cd_banco")
						->setSourceDescriptionField("nm_banco")
						->setType("select");
						
		$fields->ag_banco->setLabel(__("Agência:"))
						->setWidth(40);
						
		$fields->nr_conta_banco->setLabel(__("Número:"))
							->setWidth(70);
							
		$fields->natureza_conta->setLabel(__("Natureza:"))
								->setSource($this->src_natureza_conta)
								->setType("select");
								
		$fields->tipo_conta->setLabel(__("Tipo:"))
							->setSource($this->src_tipo_conta)
							->setType("select");
							
		$fields->dt_abertura->setLabel(__("Dt. Abertura:"))
							->setWidth(80);
		
		$fields->dt_encerramento->setLabel(__("Dt. Encerramento:"))
								->setWidth(80);
							
		$fields->st_principal->setLabel(__("Conta Principal (?)"));
		
		$fields->st_ativo->setLabel(__("Ativo (?)"));							
		}
		
	function novo_cadastro_conta_corrente()
		{
		$this->fset_conta_corrente->setVisible(true);
		$this->src_conta_corrente->newRow();	
		}
		
	function main()
		{	
		parent::main();
		}
		
	}