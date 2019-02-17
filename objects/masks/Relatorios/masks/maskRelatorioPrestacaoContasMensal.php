<?php
class maskRelatorioPrestacaoContasMensal extends satecmax_mask
	{
	function __construct()
		{
		parent::__construct();
		
		$this->setTitle(__("Geração Relatório Prestação Contas - Mensal"));
		
		$this->build("p4a_frame","frm");
		
		$this->build("p4a_quit_toolbar","toolbar");
		
		$this->build("p4a_fieldset","fsetFiltros")
			->setLabel(__("Filtros"))
			->setWidth(300);
			
		$this->frm->anchor($this->fsetFiltros);
			
		$this->montaFiltros();
		
		$this->display("main",$this->frm);
		$this->display("menu",condgest::singleton()->menu);
		$this->display("top",$this->toolbar);
		}
		
	function montaFiltros()
		{
		$this->fsetFiltros->clean();
		
		$this->build("satecmax_db_source","srcMesAnoMovimento")
			->setTable("caixa_movimento")
			->setPk("cd_caixa_movimento")
			->setQuery("SELECT 
						    YEAR(dt_movimento) as ano,
						    CONCAT_WS('/',
						            LPAD(MONTH(dt_movimento), 2, '0'),
						            YEAR(dt_movimento)) AS mes_ano
						FROM
						    caixa_movimento
						GROUP BY ano, mes_ano")
			->load();
		
		$this->build("p4a_field","fldMesAno")
			->setType("select")
			->setSource($this->srcMesAnoMovimento)
			->setSourceValueField("mes_ano")
			->setSourceDescriptionField("mes_ano")
			->setLabel(__("Selecione Mes/Ano"))
			->allowNull(__("Selecione..."));

		$this->build("p4a_field","fldImprimirComposicaoBoletos")
			->setLabel(__("Imprimir composição dos boletos (?)"))
			->setType("checkbox");
			
		$this->fldMesAno->label->setWidth(150);
		$this->fldImprimirComposicaoBoletos->label->setWidth(210);
		
		$this->fsetFiltros->anchor($this->fldMesAno);
		$this->fsetFiltros->anchor($this->fldImprimirComposicaoBoletos);
		
		$this->build("p4a_button","btnGeraRelatorio")
			->setLabel(__("Gerar Relatorio"))
			->implement("onClick",$this,"gerarRelatorio");
			
		$this->fsetFiltros->anchor($this->btnGeraRelatorio);
		
		}
		
	function gerarRelatorio(){
		if ( $this->fldMesAno->getNewValue() <> "" ){
			$objRelatorio = new rptRelatorioPrestacaoContasMensal();
			$objRelatorio->setParametros($this->fldMesAno->getNewValue(),$this->fldImprimirComposicaoBoletos->getNewValue());
			
			$strRelatorio = $objRelatorio->Output();
			
			P4A_Output_File($strRelatorio, "rptRelatorioPrestacaoContaMensal.pdf",true);
		}
		else{
			$this->info(__("Selecione uma competência para geração do relatório mensal!!!"));
		}
	}
}