<?php
class maskRelatorioUnidadesInadimplentes extends satecmax_mask
	{
	function __construct()
		{
		parent::__construct();
		
		$this->setTitle(__(".: Unidades Inadimplentes :."));
		
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
		
		$this->build("p4a_db_source","srcUnidadesInadimplentes")
			->setQuery("SELECT DISTINCT
							cd_unidade
						FROM
							tbl_boleto_mes_unidade
						WHERE
							st_baixado = 0
								AND DATE_ADD(tbl_boleto_mes_unidade.dt_vencimento,
								INTERVAL 10 DAY) < CURDATE()
						GROUP BY cd_boleto_mes_unidade , cd_boleto_mes , cd_unidade 
						UNION SELECT DISTINCT
							cd_unidade
						FROM
							acordos a
								INNER JOIN
							acordos_detalhes b ON a.cd_acordo = b.cd_acordo
						WHERE
							b.cd_st_recebimento = 1
								AND DATE_ADD(b.dt_vencimento,
								INTERVAL 10 DAY) < CURDATE()
						ORDER BY cd_unidade")
			->setPk("cd_unidade")
			->Load();
		
		$this->build("p4a_field","fldUnidades")
			->setType("select")
			->setSource($this->srcUnidadesInadimplentes)
			->setLabel(__("Unidades:"))
			->allowNull(__("Selecione ..."))
			->SetWidth(100);

		$this->fsetFiltros->anchor($this->fldUnidades);
		
		$this->build("p4a_button","btnGeraRelatorio")
			->setLabel("Gerar Carta de Cobrança",true)
			->setIcon("actions/reports-icon")
			->implement("onClick",$this,"gerarCartaCobranca");

		$this->build("p4a_button","btnGeraCartaExtraJudicial")
			->setLabel("Gerar Carta de Cobrança Extrajuducial",true)
			->setIcon("actions/reports-icon")
			->implement("onClick",$this,"gerarCartaCobrancaExtra");

		$this->fsetFiltros->anchor($this->btnGeraRelatorio);
		$this->fsetFiltros->anchor($this->btnGeraCartaExtraJudicial);
		}
		
	function gerarCartaCobranca()
		{
		$objRelatorio = new rptCartaCobranca();
		$objRelatorio->setParametros($this->fldUnidades->getNewValue());
		
		$strRelatorio = $objRelatorio->Output();
		
		P4A_Output_File($strRelatorio, "Carta cobranca da unidade ".$this->fldUnidades->getNewValue().".pdf",true);
		}
	
	function gerarCartaCobrancaExtra()
		{
		$objRelatorio = new rptCartaCobrancaJudicial();
		$objRelatorio->setParametros($this->fldUnidades->getNewValue());
	
		$strRelatorio = $objRelatorio->Output();
	
		P4A_Output_File($strRelatorio, "Carta cobranca da unidade ".$this->fldUnidades->getNewValue().".pdf",true);
		}		
	}