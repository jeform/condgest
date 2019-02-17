<?php
class maskCertidaoNegativa extends satecmax_mask
	{
	function __construct()
		{
		parent::__construct();
		
		$this->setTitle(__(".: Certidão Negativa de Débitos :."));
		
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
			->setFields(array("distinct year(dt_movimento)"=>"ano"))
			->setTable("caixa_movimento")
			->setPk("ano")
			->addOrder("ano","desc")
			->load();
		
		$this->build("p4a_field","fldAno")
			->setType("select")
			->setSource($this->srcAnoMovimento)
			->setSourceValueField("ano")
			->setSourceDescriptionField("ano")
			->setLabel(__("Selecione o Ano:"))
			->allowNull(__("Selecione..."))
			->implement("onChange",$this,"habilitaUnidade");		
			
		$this->fsetFiltros->anchor($this->fldAno);
		}
		
	function habilitaUnidade(){
		
		$this->build("satecmax_db_source","srcUnidadesAdimplentes")
			->setQuery("SELECT
    							a.cd_unidade as unidade
						FROM
							    tbl_boleto_mes_unidade a
						WHERE
    							a.st_baixado = 1
        						and year(a.dt_pagamento) = {$this->fldAno->getNewValue()}
						GROUP BY a.cd_unidade
						HAVING COUNT(*) > 11
						ORDER BY a.cd_unidade")
								->setPk("unidade")
								->Load();

		$this->build("p4a_field","fldUnidadesAdimplentes")
			->setType("select")
			->setSource($this->srcUnidadesAdimplentes)
			->setLabel(__("Selecione a Unidade:"))
			->allowNull(__("Selecione..."));
		
		$this->build("p4a_button","btnGerarCertidao")
			->setLabel(__("Gerar Certidão"))
			->implement("onClick",$this,"gerarCertidao");
		
		$this->fsetFiltros->anchor($this->fldUnidadesAdimplentes);
		$this->fsetFiltros->anchor($this->btnGerarCertidao);
		
	}	
		
	function gerarCertidao()
		{
		$objRelatorio = new rptCertidaoNegativa();
		$objRelatorio->setParametros($this->fldUnidadesAdimplentes->getNewValue());
		
		$strRelatorio = $objRelatorio->Output();
		
		P4A_Output_File($strRelatorio, "rptCertidaoNegativa.pdf",true);
		}	
	}