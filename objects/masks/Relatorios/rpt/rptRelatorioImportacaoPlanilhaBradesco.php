<?php

class rptRelatorioImportacaoPlanilhaBradesco extends satecmax_pdf
	{	
	public function __construct($orientation='P',$unit='mm',$format='A4')
		{
		parent::satecmax_pdf("P","mm","A4");
		$this->SetLeftMargin(5);
		$this->AliasNbPages();
		$this->SetDrawColor(0,0,0);
		$this->SetFillColor(0,0,0);
		$this->SetLineWidth(.1);
		$this->SetAutoPageBreak(true, 9);
		$this->setFont("tahoma","",10);
		}
		
	function Header()
		{
		$this->Image("imagens/logo.jpg",150, 1,27,27);
		$this->SetXY(6, 8);
		$this->setFont("tahoma","",6);
			
		$sqlCondominio = "select
							    a.nm_condominio,
							    a.nr_cnpj_condominio,
							    a.logradouro_condominio,
							    a.nr_logradouro_condominio,
							    a.compl_logradouro_condominio,
							    a.bairro_condominio,
							    a.municipio_condominio,
								b.desc_estado,
							    a.cep_condominio,
							    a.telefone_condominio
						from
						   		tbl_condominio a, tbl_estados_brasileiros b
						where
								a.uf_condominio = b.cd_estado";
		
		$arrDadosCondominio = P4A_DB::singleton()->fetchAll($sqlCondominio);
		
		foreach($arrDadosCondominio as $condominio => $dadosCondominio )
			{
			$this->cell(100,2.5,utf8_decode("CONDOMÍNIO: ".$dadosCondominio["nm_condominio"]),0,1,"C");
			$this->cell(100,2.5,utf8_decode("CNPJ: ".$dadosCondominio["nr_cnpj_condominio"]),0,1,"C");
			$this->cell(100,2.5,utf8_decode("ENDEREÇO: ".$dadosCondominio["logradouro_condominio"]." Nº ".$dadosCondominio["nr_logradouro_condominio"]." COMPL. ".$dadosCondominio["compl_logradouro_condominio"]),0,1,"C");
			$this->cell(100,2.5,utf8_decode("BAIRRO: ".$dadosCondominio["bairro_condominio"]),0,1,"C");
			$this->cell(100,2.5,utf8_decode($dadosCondominio["municipio_condominio"])."/".utf8_decode($dadosCondominio["desc_estado"]). " - CEP: ".$dadosCondominio["cep_condominio"],0,1,"C");
			$this->cell(100,2.5,utf8_decode("TELEFONE: ".$dadosCondominio["telefone_condominio"]),0,1,"C");
			$this->ln(2);
		
			}
		$this->setFont("tahoma","",8);

		if($this->data <> '')
			{
			$this->cell(200,5,utf8_decode("RELATORIO DE IMPORTAÇÃO PLANILHA BRADESCO - ".formatarDataBanco1($this->data)),1,1,"C");
			}
		else 
			{
			$this->cell(200,5,utf8_decode("RELATORIO DE IMPORTAÇÃO PLANILHA BRADESCO"),1,1,"C");
			}
		}
		
	function setParametros($dtImp)
		{
		$dataImportacaoPlanilha = $dtImp;
		
		$this->data = $dataImportacaoPlanilha;
		}
		
	function Footer()
		{				
		$this->setFont("tahoma","",6);
		$this->cell(200,10,utf8_decode("Condgest - O seu sistema de Gestão Condominial"),0,1,"R");
		$this->cell(200,10,utf8_decode("Data extração: ".date("d/m/Y H:i:s")),0,0,"R");
		}
		
	function montaRelatorio()
		{
		$this->AddPage();
		$this->ln(2);
		$this->setFont("tahoma","",8);
			
		$sqlCompl = $this->data <> "" ? " where dt_imp_planilha = '{$this->data}' ":"";
		
		$sqlBuscaDados = "
				select 
				    cd_unidade,
				    dt_vencimento,
				    dt_pagamento,
				    dt_liquidacao,
				    vlr_baixa_boleto,
				    nr_movimento,
				    dt_imp_planilha
				from
				    tbl_boleto_mes_unidade
				{$sqlCompl}
				order by cd_boleto_mes, cd_unidade";					
		
		$arrDados = P4A_DB::singleton()->fetchAll($sqlBuscaDados);
		
		$this->setFont("tahoma","",6);
		
		$this->cell(15,5,utf8_decode("UNIDADE"),1,0,"C");
		$this->cell(20,5,utf8_decode("DT. VENC."),1,0,"C");
		$this->cell(20,5,utf8_decode("DT. PAGTO."),1,0,"C");
		$this->cell(20,5,utf8_decode("DT. LIQ."),1,0,"C");
		$this->cell(20,5,utf8_decode("VLR. BAIXA"),1,0,"C");
		$this->cell(20,5,utf8_decode("NR. MOV."),1,0,"C");
		$this->cell(20,5,utf8_decode("DT. IMPORTACAO."),1,0,"C");
		
		foreach($arrDados as $linha => $dadosLinha )
			{
			$this->ln();
			$this->imprimirLinhaDetalhes($dadosLinha);
			}
		}

	function imprimirLinhaDetalhes($dadosLinha)
		{
		$this->setFont("tahoma","",7);
		$this->cell(15,5,utf8_decode($dadosLinha["cd_unidade"]),"BT",0,"C");
		$this->cell(20,5,utf8_decode(formatarDataBanco1($dadosLinha["dt_vencimento"])),"BT",0,"C");
		$this->cell(20,5,utf8_decode(formatarDataBanco1($dadosLinha["dt_pagamento"])),"BT",0,"C");
		$this->cell(20,5,utf8_decode(formatarDataBanco1($dadosLinha["dt_liquidacao"])),"BT",0,"C");
		$this->cell(20,5,utf8_decode(number_format($dadosLinha["vlr_baixa_boleto"],2,",",".")),"BT",0,"C");
		$this->cell(20,5,utf8_decode($dadosLinha["nr_movimento"]),"BT",0,"C");
		$this->cell(20,5,utf8_decode(formatarDataBanco1($dadosLinha["dt_imp_planilha"])),"BT",0,"C");
		}		

		
	function Output()
		{
		$this->montaRelatorio();
		return parent::Output("rptRelatorioImportacaoPlanilhaBradesco.pdf","S");
		}
			
	}