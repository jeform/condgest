<?php
class caixa_movimento_relatorios extends satecmax_mask
	{
	function __construct()
		{
		parent::__construct();
		
		$this->setTitle(__("Relatorios do Caixa: "). condgest::singleton()->masks->caixa_movimento->srcSaldoCaixa->fields->ds_caixa->getValue());
		
		
		$this->build("p4a_button","btnListagemSaidasCategoria")
			->setLabel(__("Saidas do caixa por categoria"))
			->implement("onClick",$this,"rptListagemSaidasCategoria");
			
		$this->build("p4a_fieldset","fsetFiltroRelatorio")
			->setLabel(__("Filtros Relatorio"))
			->setWidth(1000);
		
		$this->build("p4a_frame","frm")
			->anchor($this->btnListagemSaidasCategoria)
			->anchor($this->fsetFiltroRelatorio);
			
		$this->display("main",$this->frm);
		
		
		$this->build("p4a_quit_toolbar","toolbar");
		
		$this->display("top",$this->toolbar);
		//$this->display("menu", condgest::singleton()->menu);
		}
		
	function rptListagemSaidasCategoria()
		{
		$this->fsetFiltroRelatorio->clean();
		
		$this->build("p4a_field","fldDtInicio")
			->setLabel(__("Dt. Inicio"))
			->setType("date");
		
		$this->build("p4a_field","fldDtFim")
			->setLabel(__("Dt. Fim"))
			->setType("date");
			
		$this->build("satecmax_db_source","srcCategorias")
			->setTable("tbl_categorias")
			->setWhere("st_categoria = 1 and tp_categoria = 2")
			->setPk("cd_categoria")
			->addOrder("ds_categoria")
			->Load();
			
		$this->build("p4a_field","fldCategoria")
			->setLabel(__("Categoria"))
			->setType("select")
			->setSource($this->srcCategorias)
			->setSourceValueField("cd_categoria")
			->allowNull(__("Todas..."));
			
		$this->build("p4a_button","btnGerarRelatorio")
			->setLabel(__("Gerar Relatorio"))
			->implement("onClick",$this,"gerarRelatorioSaidasCategoria");
			
		$this->fsetFiltroRelatorio->anchor($this->fldDtInicio)->anchorLeft($this->fldDtFim)->anchorLeft($this->fldCategoria)->anchor($this->btnGerarRelatorio);
		}
		
	function gerarRelatorioSaidasCategoria()
		{
		
		
		if ( $this->fldDtInicio->getNewValue() == "" and $this->fldDtFim->getNewValue() == "" )
			{
			$this->error(__("Preencha todos os campos necessarios!"));
			}
			
		$objRelatorio = new rptCaixaSaidas();
		
		$objRelatorio->setParametros($this->fldDtInicio->getNewValue(), $this->fldDtFim->getNewValue(), $this->fldCategoria->getNewValue());
		
		P4A_Output_File($objRelatorio->Output(), "categoria.pdf",true);
		}
		
	}
	