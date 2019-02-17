<?php
class verLancamentosCaixa extends satecmax_mask
	{
		
	private $cdCaixa;
	
	/**
	 * 
	 * Objeto de movimentacao do caixa
	 * @var movimentoCaixa
	 */
	private $objMovimentoCaixa;
	
	function __construct()
		{
		parent::__construct();
		
		$this->setTitle(__("Ver Lançamentos Caixa"));
		
		$this->build("p4a_frame","frm")
			->setWidth("800");
			
		$this->build("p4a_fieldset","fsetFiltros")
			->setLabel(__("Filtros"));
			
		$this->build("p4a_field","fldDtInicial")
			->setType("date")
			->setLabel(__("Data Inicial:"));
			
		$this->build("p4a_field","fldDtFinal")
			->setType("date")
			->setLabel(__("Data Final:"));
			
		$this->build("p4a_field","fldPessoa")
			->setLabel(__("Pessoa:"))
			->setSource(P4A::singleton()->srcPessoas)
			->setSourceDescriptionField("nm_pessoa")
			->setSourceValueField("cd_pessoa")
			->setType("select")
			->allowNull("Selecione ...");

		$this->build("p4a_field","fldCategoria")
			->setLabel(__("Categoria:"))
			->setSource(P4A::singleton()->src_categorias)
			->setSourceDescriptionField("categorias")
			->setSourceValueField("cd_categoria")
			->setType("select")
			->allowNull("Selecione ...");	
			
		$this->build("p4a_field", "fldValorMovimento")
			->setLabel(__("Valor:"));
			
		$this->build("p4a_button","btnFiltrar")
			->setLabel(__("Filtrar..."))
			->implement("onClick",$this,"montarTelaFiltro");
			
		$this->fsetFiltros->anchor($this->fldDtInicial)
			->anchorLeft($this->fldDtFinal)
			->anchorLeft($this->btnFiltrar)
			->anchor($this->fldPessoa)
			->anchor($this->fldCategoria)
			->anchor($this->fldValorMovimento);
			
		$this->build("p4a_fieldset","fsetResultado")
			->setLabel(__("Movimentos"))
			->setVisible(false);
			
		$this->frm->anchor($this->fsetFiltros)
			->anchor($this->fsetResultado);
			
		$this->display("main",$this->frm);
		}
		
	function setCaixa($cd_caixa)
		{
		$this->cdCaixa = $cd_caixa;
		
		$this->objMovimentoCaixa = new movimentoCaixa($this->cdCaixa);
		
		$this->fldDtInicial->setNewValue(formatarDataAplicacao($this->objMovimentoCaixa->getDataInicialCaixa()));
		
		$this->fldDtFinal->setNewValue(date("d/m/Y"));
		}
		
	function montarTelaFiltro()
		{
		$arrDados = $this->objMovimentoCaixa->montaObjSourceMovimentosCaixa($this->fldDtInicial->getNewValue(), $this->fldDtFinal->getNewValue(),$this->fldPessoa->getNewValue(), $this->fldCategoria->getNewValue(), str_ireplace(",", ".", $this->fldValorMovimento->getNewValue()));
		
		$this->build("p4a_array_source","srcMovimentos")
			->load($arrDados)
			->setPk("cdMovimento");

		$this->srcMovimentos->fields->dtMovimento->setType("date");
		
		$this->srcMovimentos->fields->vlrEntrada->setType("decimal");
		
		$this->srcMovimentos->fields->vlrSaida->setType("decimal");
		
		$this->srcMovimentos->fields->vlrSaldoFinal->setType("decimal");
		
		$this->srcMovimentos->fields->vlrSaldoAnterior->setType("decimal");
		
			
		$this->build("satecmax_table","tblMovimentos")
			->setSource($this->srcMovimentos);
			
		$this->tblMovimentos->cols->cdMovimento->setLabel(__("Cod.Mov."));
		$this->tblMovimentos->cols->dtMovimento->setLabel(__("Dt.Mov."));
		$this->tblMovimentos->cols->dsMovimento->setLabel(__("Desc. Mov."));
		$this->tblMovimentos->cols->vlrSaldoAnterior->setLabel(__("Saldo Ant."));
		$this->tblMovimentos->cols->vlrEntrada->setLabel(__("Entrada"));
		$this->tblMovimentos->cols->vlrSaida->setLabel(__("Saída"));
		$this->tblMovimentos->cols->vlrSaldoFinal->setLabel(__("Saldo"));
		
		$this->tblMovimentos->addActionCol("editarMovimento");
		$this->tblMovimentos->cols->editarMovimento->setLabel(__("Editar"))
												->implement("afterClick",$this,"editarLancamentoCaixa");
		
		$this->fsetResultado->clean();
		
		$this->fsetResultado->setVisible(true);
		
		$this->fsetResultado->anchor($this->tblMovimentos);
		
		$this->build("p4a_button","btnRptRelatorioLancamentosCaixa")
			->setLabel(__("Imprimir Relatorio"))
			->implement("onClick",$this,"gerarRptRelatorioLancamentosCaixa");
			
		$this->fsetResultado->anchor($this->btnRptRelatorioLancamentosCaixa);
		}

	function editarLancamentoCaixa()
		{
		if($this->srcMovimentos->fields->cdMovimento->getValue() > 0)
			{		
			condgest::singleton()->openMask("editarLancamentosCaixa");
			condgest::singleton()->active_mask->setLancamento($this->srcMovimentos->fields->cdMovimento->getValue());
			}
		else
			{
			$this->info(__("Selecione um lançamento antes de continuar!"));
			}
		}	
		
	function gerarRptRelatorioLancamentosCaixa()
		{
		$objRpt = new rptRelatorioLancamentosCaixa();
		
		$objRpt->setParametros($this->srcMovimentos);
		
		$strRelatorio = $objRpt->Output();
		
		P4A_Output_File($strRelatorio, "rptRelatorioLancamentosCaixa.pdf",true);
		}
		
		
	}