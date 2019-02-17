<?php
class maskRelatorioBaixaBoletos extends satecmax_mask
	{
	function __construct()
		{
		parent::__construct();
		
		$this->setTitle(__("Geração Relatórios de Baixa dos Boletos"));
		
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
		
		$this->build("satecmax_db_source","srcMesAnoReferencia")
			->setTable("tbl_boleto_mes")
			->setPk("cd_boleto_mes")
			->addOrder("cd_boleto_mes","desc")
			->load();
		
		$this->build("p4a_field","fldMesAnoReferencia")
			->setType("select")
			->setSource($this->srcMesAnoReferencia)
			->setSourceValueField("cd_boleto_mes")
			->setSourceDescriptionField("mes_ano_referencia")
			->setLabel(__("Mês/Ano Ref.:"))
			->allowNull(__("Selecione ..."))
			->SetWidth(100);

		$arr_baixa_boleto[] = array("status_boleto"=>"0","desc_status_boleto"=>"Aberto");
		$arr_baixa_boleto[] = array("status_boleto"=>"1","desc_status_boleto"=>"Fechado");
		
		$this->build("p4a_array_source","arr_baixa_boleto")
			->Load($arr_baixa_boleto)
			->setPk("status_boleto");
			
		$this->build("p4a_field","fldStatusBoleto")
			->setType("select")
			->setSource($this->arr_baixa_boleto)
			->setSourceValueField("status_boleto")
			->setSourceDescriptionField("desc_status_boleto")
			->allowNull(__("Todos ..."))
			->setLabel(__("Status Boleto:"))
			->SetWidth(100);				
			
		$this->fsetFiltros->anchor($this->fldMesAnoReferencia);
		$this->fsetFiltros->anchor($this->fldStatusBoleto);
		
		$this->build("p4a_button","btnGeraRelatorio")
			->setLabel(__("Gerar Relatorio"))
			->implement("onClick",$this,"gerarRelatorio");
						
		$this->fsetFiltros->anchor($this->btnGeraRelatorio);
		
		}
		
	function gerarRelatorio()
		{
		$objRelatorio = new rptBaixaBoletos();
		$objRelatorio->setParametros($this->fldMesAnoReferencia->getNewValue(),$this->fldStatusBoleto->getNewValue());
		
		$strRelatorio = $objRelatorio->Output();
		
		P4A_Output_File($strRelatorio, "rptProcessamentoBoletos.pdf",true);
		}	
	}