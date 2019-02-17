<?php
class recibos extends satecmax_mask
	{		
	function __construct()
		{
		parent::__construct();
		
		$this->setTitle(__("Cadastro de Recibos"));
		
		$this->build("satecmax_db_source","src_recibos")
			->setTable("recibos")
			->setPk("cd_recibo")
			->Load()
			->firstRow();

		$this->build("p4a_db_source","srcPessoas")
			->setTable("tbl_pessoas")
			->setPk("cd_pessoa")
			->addOrder("nm_pessoa")
			->Load();
		
		$this->build("p4a_fieldset","fset_recibos")
			->setLabel(__("Detalhes"))
			->setWidth(600);
			
		$this->build("p4a_table", "tbl_recibos")
			->setSource($this->src_recibos)
			->setLabel(__("Lista de Recibos Cadastrados"))
			->setWidth(700);
			
		$this->tbl_recibos->cols->cd_recibo->setLabel(__("Número"))
											->setWidth(50);
		$this->tbl_recibos->cols->cd_caixa->setLabel(__("Caixa"))
										->setSource(P4a::Singleton()->src_caixas_cadastrados)
										->setSourceDescriptionField("desc_caixa");
		$this->tbl_recibos->cols->cd_pessoa->setLabel(__("Pessoa"))
										->setSource($this->srcPessoas)
										->setSourceDescriptionField("nm_pessoa")
										->setSourceValueField("cd_pessoa")
										->setWidth(300);
		$this->tbl_recibos->cols->dt_recibo->setLabel(__("Data"));
		$this->tbl_recibos->cols->vlr_recibo->setLabel(__("Valor"));		

		$this->tbl_recibos->addActionCol("imprimirRecibo");		
		$this->tbl_recibos->cols->imprimirRecibo->setLabel(__("Imprimir Recibo"));
		$this->intercept($this->tbl_recibos->cols->imprimirRecibo, "afterClick","imprimirRecibo");		
		
		$this->tbl_recibos->setVisibleCols(array("cd_recibo","cd_caixa","cd_pessoa","dt_recibo","vlr_recibo","imprimirRecibo"));
				
		$this->setSource($this->src_recibos);			
			
		$this->build("satecmax_full_toolbar","toolbar")
			->setMask($this);

			
		$this->build("p4a_frame","frm")
			->setWidth(1024);
			
		$this->fset_recibos->anchor($this->fields->cd_recibo)
						->anchor($this->fields->cd_caixa)
						->anchor($this->fields->cd_pessoa)
						->anchor($this->fields->tp_movimento_recibo)		
						->anchor($this->fields->tp_documento_referencia)
						->anchor($this->fields->cd_plano_conta)
						->anchor($this->fields->cd_hist_padrao)
						->anchor($this->fields->ds_compl_hist_padrao)
						->anchor($this->fields->ds_recibo)
						->anchor($this->fields->dt_recibo)
						->anchor($this->fields->vlr_recibo);
			
		$this->setFieldsProperties();
		
		$this->frm->anchorCenter($this->tbl_recibos);
		$this->frm->anchorCenter($this->fset_recibos);		
		
		$this->display("main",$this->frm);
		
		$this->display("menu",p4a::singleton()->menu);
		
		$this->display("top",$this->toolbar);
		}
		
	
	function setFieldsProperties()			
		{
		$fields = $this->fields;
		
		$fields->cd_recibo->setLabel(__("Número"))
							->setWidth(50)
							->enable(false);	

		$fields->cd_caixa->setLabel(__("Caixa"))
						->setSource(P4a::Singleton()->src_caixas_cadastrados)
						->setSourceDescriptionField("desc_caixa")
						->setType("select")
						->setWidth(120);
		
		$fields->cd_pessoa->setLabel(__("Pessoa"))
						->setSource($this->srcPessoas)
						->setSourceDescriptionField("nm_pessoa")
						->setSourceValueField("cd_pessoa")
						->setType("select");
						
		$arr_tp_movimento[] = array("tipo_movimento"=>"0","movimento"=>"Entrada");
		$arr_tp_movimento[] = array("tipo_movimento"=>"1","movimento"=>"Saída");
			
		$this->build("p4a_array_source","arr_source_tp_movimento")
			->Load($arr_tp_movimento)
			->setPk("tipo_movimento");
		
		
		$fields->tp_movimento_recibo->setLabel(__("Tipo Movimento"))
							->setSource($this->arr_source_tp_movimento)
							->setType("select")
							->setWidth(120);			
		
		$this->build("p4a_db_source","srcDocumentos")
			->setTable("documentos")
			->setPK("cd_documento")
			->setWhere("tp_documento = 'REC'")
			->Load();
		
		$fields->tp_documento_referencia->setLabel(__("Tipo Documento Ref."))
										->setSource($this->srcDocumentos)
										->setSourceDescriptionField("ds_documento")
										->setSourceValueField("tp_documento")
										->setType("select")
										->setWidth("100")
										->enable(false);
		
		// categorias de contas
		$this->build("p4a_db_source","srcCategorias")
			->setTable("tbl_categorias")
			->addOrder("ds_categoria")
			->setPk("cd_categoria")
			->Load();
		
		$fields->cd_plano_conta->setLabel(__("Categoria"))
							->setSource($this->srcCategorias)
							->setSourceDescriptionField("ds_categoria")
							->setSourceValueField("cd_categoria")
							->setType("select");
		
		// historico padrao
		$this->build("p4a_db_source","srcHistPadrao")
			->setTable("hist_padrao")
			->setPk("cd_hist_padrao")
			->Load();
		
		$fields->cd_hist_padrao->setLabel(__("Histórico Padrão"))
							->setSource($this->srcHistPadrao)
							->setSourceDescriptionField("ds_hist_padrao")
							->setSourceValueField("cd_hist_padrao")
							->setType("select");
		
		$fields->ds_compl_hist_padrao->setLabel(__("Compl. Histórico"));		
		
		
		
		$fields->ds_recibo->setLabel(__("Descrição"))
						->setWidth(300)
						->setType("textarea");
		
		$fields->dt_recibo->setLabel(__("Data"))
						->setWidth(100);
		
		$fields->vlr_recibo->setLabel(__("Valor"))
						->setProperty("dir","rtl")
						->setWidth(100);
		}
		
	function saveRow()
		{
		$this->fields->tp_documento_referencia->setNewValue('REC');

		parent::saveRow();
		
		}

	function imprimirRecibo()
		{		
		$objRelatorio = new rptRecibos();
		
		$objRelatorio->setParametros($this->fields->cd_recibo->getValue());
		
		P4A_Output_File($objRelatorio->Output(), "Recibo ".$this->fields->cd_recibo->getValue().".pdf",true);
		}
		
	function main()
		{	
		parent::main();
		}
	}