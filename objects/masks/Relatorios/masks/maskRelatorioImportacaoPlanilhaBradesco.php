<?php
class maskRelatorioImportacaoPlanilhaBradesco extends satecmax_mask
	{
	function __construct()
		{
		parent::__construct();
		
		$this->setTitle(__("Geração Relatório de Importação Planilha Bradesco"));
		
		$this->build("p4a_frame","frm");
		
		$this->build("p4a_quit_toolbar","toolbar");
		
		$this->build("p4a_fieldset","fsetFiltros")
			->setLabel(__("Filtros"));
			
		$this->frm->anchor($this->fsetFiltros);
			
		$this->montaFiltros();
		
		$this->display("main",$this->frm);
		$this->display("menu",condgest::singleton()->menu);
		$this->display("top",$this->toolbar);
		}
		
	function montaFiltros()
		{
		$this->fsetFiltros->clean();
		
		$this->build("satecmax_db_source","srcDataImportacao")
			->setFields(array("distinct (dt_imp_planilha)"=>"data"))
			->setTable("tbl_boleto_mes_unidade")
			->setWhere("dt_imp_planilha is not null")
			->setPk("cd_caixa_movimento")
			->load();
		
		$this->build("p4a_field","fldDataImportacao")
			->setType("select")
			->setSource($this->srcDataImportacao)
			->setSourceValueField("data")
			->setSourceDescriptionField("data")
			->setLabel(__("Dt. Importação:"))
			->allowNull(__("Sem data"));		
			
		$this->fsetFiltros->anchor($this->fldDataImportacao);
		
		$this->build("p4a_button","btnGeraRelatorio")
			->setLabel(__("Gerar Relatorio"))
			->implement("onClick",$this,"gerarRelatorio");
						
		$this->fsetFiltros->anchor($this->btnGeraRelatorio);
		
		}
		
	function gerarRelatorio()
		{
		$objRelatorio = new rptRelatorioImportacaoPlanilhaBradesco();
		$objRelatorio->setParametros($this->fldDataImportacao->getNewValue());
		
		$strRelatorio = $objRelatorio->Output();
		
		P4A_Output_File($strRelatorio, "rptRelatorioImportacaoPlanilhaBradesco.pdf",true);
		}
	}