<?php
class maskRelatorioInadimplencia extends satecmax_mask
	{
	function __construct()
		{
		parent::__construct();
		
		$this->setTitle(__("Geração Relatório de Inadimplência"));
		
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
		
		$this->build("satecmax_db_source","srcMesAnoMovimento")
			->setTable("tbl_boleto_mes")
			->setPk("cd_boleto_mes")
			->addOrder("cd_boleto_mes","desc")
			->load();
		
		$this->build("p4a_field","fldMesAno")
			->setType("select")
			->setSource($this->srcMesAnoMovimento)
			->setSourceValueField("cd_boleto_mes")
			->setSourceDescriptionField("mes_ano_referencia")
			->setLabel(__("Selecione Mês/Ano"))
			->allowNull(__("Todos"));
						
		$this->fsetFiltros->anchor($this->fldMesAno);
		
		$this->build("p4a_button","btnGeraRelatorio")
			->setLabel(__("Gerar Relatorio"))
			->implement("onClick",$this,"gerarRelatorio");
			
		$this->fsetFiltros->anchor($this->btnGeraRelatorio);
		
		}
		
	function gerarRelatorio()
		{			
		$objRelatorio = new rptRelatorioInadimplencia();
		$objRelatorio->setParametros($this->fldMesAno->getNewValue());
		
		$strRelatorio = $objRelatorio->Output();
		
		P4A_Output_File($strRelatorio, "rptRelatorioInadimplencia.pdf",true);			
		}
	}