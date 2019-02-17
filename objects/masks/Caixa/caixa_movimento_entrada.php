<?php
class caixa_movimento_entrada extends satecmax_mask
	{

	
	/**
	 * 
	 * Enter description here ...
	 * @var P4A_Collection
	 */
	public $campos;
	
	private $cdCaixa;
	
	function __construct()
		{
		parent::__construct();
		
		$this->build("p4a_collection","campos");		
		
		$this->setTitle(__("Movimento de Entrada Caixa"));
		
		// criar os campos de input dos dados...
		/*
										$dtMovimento,
								$vlrMovimento,
								$cdPessoa,
								$cdDocumentoReferencia,
								$tpDocumentoReferencia,
								$dsMovimento,
								$cdPlanoConta,
								$cdHistPadrao,
								$dsComplHistPadrao
		*/
		
		// Data do Movimento ( tem que ser dentro do mes aberto )...
		
		$this->campos->build("p4a_field","fldDtMovimento")
			->setLabel(__("Data Movimento"));
			
		$this->campos->build("p4a_field","fldVlrMovimento")
			->setLabel(__("Valor Movimento"));
			
		$this->campos->build("p4a_field","fldCdPessoa")
			->setLabel(__("Pessoa"));
			
		$this->campos->build("p4a_field","fldDocumentoReferencia")
			->setLabel(__("Nr. Documento Ref."));
			
		$this->campos->build("p4a_field","fldTpDocumentoReferencia")
			->setLabel(__("Tipo Documento Ref."));
			
		$this->campos->build("p4a_field","fldDsMovimento")
			->setLabel(__("Descrição Entrada"));
			
		$this->campos->build("p4a_field","fldCdPlanoConta")
			->setLabel(__("Categoria"));
			
		$this->campos->build("p4a_field","fldCdHistPadrao")
			->setLabel(__("Historico Padrão"));
			
		$this->campos->build("p4a_field","fldDsComplHistPadrao")
			->setLabel(__("Compl. Histórico"));
			
		$this->setFieldsProperties();

		$this->build("p4a_frame","frm")
			->setWidth(800);
			
		$this->frm->anchor($this->campos->fldDtMovimento)
			->anchor($this->campos->fldVlrMovimento)
			->anchor($this->campos->fldCdPessoa)
			->anchor($this->campos->fldDocumentoReferencia)
			->anchor($this->campos->fldTpDocumentoReferencia)	
			->anchor($this->campos->fldDsMovimento)
			->anchor($this->campos->fldCdPlanoConta)
			->anchor($this->campos->fldCdHistPadrao)
			->anchor($this->campos->fldDsComplHistPadrao);

			
		$this->build("p4a_button","btnProcessar")
			->setLabel(__("Criar Movimento"))
			->implement("onClick",$this,"gerarMovimento");
			
		$this->frm->anchorCenter($this->btnProcessar);
			
		$this->display("main",$this->frm);
		}
		
	function setFieldsProperties()
		{
		$fields = $this->campos;
		
		$fields->fldDtMovimento->setType("date");
		
		$fields->fldVlrMovimento->setProperty("dir","rtl");
		
		$this->build("p4a_db_source","srcPessoas")
			->setTable("tbl_pessoas")
			->setPk("cd_pessoa")
			->Load();
			
		$fields->fldCdPessoa->setSource($this->srcPessoas)
			->setSourceDescriptionField("nm_pessoa")
			->setSourceValueField("cd_pessoa")
			->setType("select");
			
		$this->srcPessoas->addOrder("nm_pessoa");
		
		$this->build("p4a_db_source","srcDocumentos")
			->setTable("documentos")
			->setPK("cd_documento")
			->Load();
			
		$fields->fldTpDocumentoReferencia->setSource($this->srcDocumentos)
			->setSourceDescriptionField("ds_documento")
			->setSourceValueField("tp_documento")
			->setType("select");
			
		$fields->fldDsMovimento->setType("textarea")
			->setWidth("200")
			->setHeight("50");
			
		// categorias de contas
		$this->build("p4a_db_source","srcCategorias")
			->setTable("tbl_categorias")
			->setPk("cd_categoria")
			->setWhere("tp_categoria = 1 and st_categoria = 1")
			->Load();
			
		$fields->fldCdPlanoConta->setSource($this->srcCategorias)
			->setSourceDescriptionField("ds_categoria")
			->setSourceValueField("cd_categoria")
			->setType("select");
			
		// historico padrao
		
		$this->build("p4a_db_source","srcHistPadrao")
			->setTable("hist_padrao")
			->setPk("cd_hist_padrao")
			->Load();
			
		$fields->fldCdHistPadrao->setSource($this->srcHistPadrao)
			->setSourceDescriptionField("ds_hist_padrao")
			->setSourceValueField("cd_hist_padrao")
			->setType("select");
			
		}
		
	function setCaixa($cdCaixa)
		{
		$this->cdCaixa = $cdCaixa;
		}
		
	function gerarMovimento()
		{
		
		try
			{
			P4A_DB::singleton()->beginTransaction();
			$objMovimento = new movimentoCaixa($this->cdCaixa);
			
			$objMovimento->newMovimentoEntrada(
												$this->campos->fldDtMovimento->getNewValue(), 
												$this->campos->fldVlrMovimento->getNewValue(), 
												$this->campos->fldCdPessoa->getNewValue(), 
												$this->campos->fldDocumentoReferencia->getNewValue(), 
												$this->campos->fldTpDocumentoReferencia->getNewValue(), 
												$this->campos->fldDsMovimento->getNewValue(), 
												$this->campos->fldCdPlanoConta->getNewValue(), 
												$this->campos->fldCdHistPadrao->getNewValue(), 
												$this->campos->fldDsComplHistPadrao->getNewValue()
												);
		
			P4A_DB::singleton()->commit();
			
			$this->info(__("Registro criado com sucesso!"));
			
			$this->showPrevMask();
			}
		catch (Exception $e)
			{
			P4A_DB::singleton()->rollback();
			
			$this->error(__("Erro: ".$e->getCode()." - ".$e->getMessage()));
			
			}
												
		}
	}