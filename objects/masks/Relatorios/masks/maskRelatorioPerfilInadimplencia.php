<?php
class maskRelatorioPerfilInadimplencia extends satecmax_mask
	{
	function __construct()
		{
		parent::__construct();
		
		$this->setTitle(__("Geração Relatório - Perfil de Inadimplência"));
		
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
		
		$this->build("satecmax_db_source","srcAnoReferencia")
			->setFields(array("distinct(mid(mes_ano_referencia,4,8))"=>"ano"))
			->setTable("tbl_boleto_mes")
			->setPk("cd_boleto_mes")
			->addOrder("ano","desc")
			->load();
		
		$this->build("p4a_field","fldAnoReferencia")
			->setType("select")
			->setSource($this->srcAnoReferencia)
			->setSourceValueField("ano")
			->setSourceDescriptionField("ano")
			->setLabel(__("Selecione o ano:"))
			->allowNull(__("Selecione..."));
						
		$this->fsetFiltros->anchor($this->fldAnoReferencia);
		
		$this->build("p4a_button","btnGeraRelatorio")
			->setLabel(__("Gerar Relatorio"))
			->implement("onClick",$this,"gerarRelatorio");
			
		$this->fsetFiltros->anchor($this->btnGeraRelatorio);
		
		}
		
	function gerarRelatorio()
		{			
		$objRelatorio = new rptRelatorioPerfilInadimplencia();
		$objRelatorio->setParametros($this->fldAnoReferencia->getNewValue());
		
		$strRelatorio = $objRelatorio->Output();
		
		P4A_Output_File($strRelatorio, "rptRelatorioPerfilInadimplencia.pdf",true);			
		}
	}