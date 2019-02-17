<?php
class maskRelatorioPrestacaoContasAnual extends satecmax_mask
	{
	function __construct()
		{
		parent::__construct();
		
		$this->setTitle(__("Geração Relatório Prestação Contas - Anual"));
		
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
		
		$this->build("satecmax_db_source","srcAnoMovimento")
			->setTable("caixa_movimento")
			->setPk("cd_caixa_movimento")
			->setQuery("select
							  distinct year(dt_movimento) as ano
						  from
							  caixa_movimento
						 order by
							  ano desc")
			->load();
		
		$this->build("p4a_field","fldAno")
			->setType("select")
			->setSource($this->srcAnoMovimento)
			->setSourceValueField("ano")
			->setSourceDescriptionField("ano")
			->setLabel(__("Selecione Ano"))
			->allowNull(__("Selecione..."));		
			
		$this->fsetFiltros->anchor($this->fldAno);
		
		$this->build("p4a_button","btnGeraRelatorio")
			->setLabel(__("Gerar Relatorio"))
			->implement("onClick",$this,"gerarRelatorio");
						
		$this->fsetFiltros->anchor($this->btnGeraRelatorio);
		
		}
		
	function gerarRelatorio()
		{
		if ( $this->fldAno->getNewValue() <> "" )
			{
			$objRelatorio = new rptRelatorioPrestacaoContasAnual();
			$objRelatorio->setParametros($this->fldAno->getNewValue());
			
			$strRelatorio = $objRelatorio->Output();
			
			P4A_Output_File($strRelatorio, "relPrestacaoContaAnual_".$this->fldAno->getNewValue()."spdf",true);
			}
		else
			{
			$this->info(__("Selecione um ano para geração do relatorio"));
			}
		}
	}