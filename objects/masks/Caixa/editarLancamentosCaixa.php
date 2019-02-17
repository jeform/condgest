<?php
class editarLancamentosCaixa extends satecmax_mask
	{
	private $cd_movimento;	
		
	function __construct()
		{
		parent::__construct();
		
		$this->setTitle(__("Editar Lançamentos Caixa"));
		
		$this->build("satecmax_db_source","srcMovimentos")
			->setTable("caixa_movimento")
			->Load()
			->firstRow();

		$this->setSource($this->srcMovimentos);				
			
		$this->build("satecmax_full_toolbar","toolbar")
			->setMask($this);				

		$this->toolbar->buttons->new->setInvisible();
		
		$this->setFieldsProperties();	
			
		$this->build("p4a_frame","frm")
			->setWidth(1024);
			
		$this->build("p4a_fieldset","fsetEditarLancamento")
			->setLabel(__("Detalhes"))
			->setWidth(600);			
 
		$this->fsetEditarLancamento->anchor($this->fields->cd_caixa_movimento)
								->anchor($this->fields->cd_documento_referencia)
								->anchor($this->fields->cd_pessoa)
								->anchor($this->fields->dt_movimento)
								->anchor($this->fields->ds_movimento)
								->anchor($this->fields->vlr_movimento)
								->anchor($this->fields->cd_plano_conta)
								->anchor($this->fields->tp_documento_referencia)
								->anchor($this->fields->cd_hist_padrao);		
 
		$this->frm->anchorCenter($this->fsetEditarLancamento);	
		
		$this->display("main",$this->frm);		
		$this->display("menu",p4a::singleton()->menu);
		$this->display("top",$this->toolbar);
	}

	function setLancamento($cd_movimento)
		{
		$this->srcMovimentos->setWhere("cd_caixa_movimento = ".$cd_movimento);
		}

 	function setFieldsProperties()
		{
		$fields = $this->fields;
		
		$fields->cd_caixa_movimento->setLabel(__("Cód."))
							->setWidth(50)
							->enable(false);

		$fields->cd_documento_referencia->setLabel(__("Nr. Documento"));
							
		$fields->cd_pessoa->setLabel(__("Pessoa"))
	 					->setSource(P4A::singleton()->srcPessoas)
						->setSourceDescriptionField("nm_pessoa")
						->setSourceValueField("cd_pessoa")
						->setType("select");															

		$fields->dt_movimento->setLabel(__("Data"))
							->setWidth(80);
							
		$fields->ds_movimento->setLabel(__("Descrição"))
								->setWidth(300);
			
		$fields->tp_documento_referencia->setLabel(__("Doc. Referência"))
									->setSource(P4A::singleton()->src_documentos_cadastrados)
									->setSourceValueField("cd_documento")
									->setSourceDescriptionField("desc_documento")
									->setType("select")
									->setWidth(300);
		
		$fields->cd_hist_padrao->setLabel(__("Hist. Padrão"))
							->setSource(P4A::singleton()->src_hist_padrao)
							->setSourceValueField("cd_hist_padrao")
							->setSourceDescriptionField("desc_hist_padrao")
							->setType("select")
							->setWidth(300);
		
		$fields->vlr_movimento->setLabel(__("Valor"))
								->setWidth(100);			

		$fields->cd_plano_conta->setLabel(__("Categoria"))
					->setSource(P4A::singleton()->src_categorias)
					->setSourceValueField("cd_categoria")
					->setSourceDescriptionField("categorias")
					->setType("select")
					->setWidth(300);
						
		}
	
	function main()
		{		
		parent::main();
		}		
	}