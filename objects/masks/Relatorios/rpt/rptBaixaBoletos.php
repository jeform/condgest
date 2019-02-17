<?php
class rptBaixaBoletos extends satecmax_pdf
	{

	private $arrDados;

	function __construct()
		{
		parent::satecmax_pdf("P","mm","A4");
		$this->SetLeftMargin(6);
		$this->AliasNbPages();
		$this->SetDrawColor(0,0,0);
		$this->SetFillColor(0,0,0);
		$this->SetLineWidth(.1);
		$this->SetAutoPageBreak(true, 9);
		$this->setFont("tahoma","",10);
		}

	function Header()
		{
		$this->SetXY(6, 8);
		$this->setFont("tahoma","",8);
			
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
			$this->cell(210,5,utf8_decode("Condomínio ".$dadosCondominio["nm_condominio"]),0,1,"L");
			$this->cell(210,5,utf8_decode("CNPJ: ".$dadosCondominio["nr_cnpj_condominio"]),0,1,"L");
			$this->cell(210,5,utf8_decode("Endereço: ".$dadosCondominio["logradouro_condominio"]." Nº ".$dadosCondominio["nr_logradouro_condominio"]." Compl. ".$dadosCondominio["compl_logradouro_condominio"]),0,1,"L");
			$this->cell(210,5,utf8_decode("Bairro: ".$dadosCondominio["bairro_condominio"]),0,1,"L");
			$this->cell(210,5,utf8_decode($dadosCondominio["municipio_condominio"])."/".utf8_decode($dadosCondominio["desc_estado"]). " - CEP: ".$dadosCondominio["cep_condominio"],0,1,"L");
			$this->cell(210,5,utf8_decode("Telefone: ".$dadosCondominio["telefone_condominio"]),0,1,"L");
			$this->ln(10);
			}
		}	

	function setParametros($cdBoletoMes,$statusBoleto)
		{
		$this->AddPage();
		$this->setFont("tahoma","",10);

		$this->cell(190,5,utf8_decode("Relatório dos boletos cadastrados"),1,1,"C");
		$this->ln();	
		
		$sqlComplStatusBaixa = $statusBoleto <> "" ? " and b.st_baixado = '{$statusBoleto}' ":"";
		$sqlComplBoletoMes = $cdBoletoMes <> "" ? " and b.cd_boleto_mes = '{$cdBoletoMes}' ":"";
		
		$sqlBuscaDados = "SELECT 
							    c.cd_unidade,
							    DATE_FORMAT(b.dt_vencimento, '%d/%m/%Y') AS dt_vencimento,
							    DATE_FORMAT(b.dt_pagamento, '%d/%m/%Y') AS dt_pagamento,
							    (SELECT 
							                ROUND(SUM(vlr_item_boleto), 2)
							            FROM
							                tbl_boleto_mes_unidade_itens_cobranca
							            WHERE
							                cd_boleto_mes_unidade = b.cd_boleto_mes_unidade) as vlr_hist, 
							    IF(st_baixado = 1
							            AND b.dt_pagamento <= b.dt_vencimento,
							        (SELECT 
							                ROUND(SUM(vlr_item_boleto) * 0.9, 2)
							            FROM
							                tbl_boleto_mes_unidade_itens_cobranca
							            WHERE
							                cd_boleto_mes_unidade = b.cd_boleto_mes_unidade),
							        (SELECT 
							                ROUND(SUM(vlr_item_boleto) + SUM(vlr_item_boleto) * 0.02 + SUM(vlr_item_boleto) * 0.01 * DATEDIFF(b.dt_pagamento,dt_vencimento) / 30,
							                            2)
							            FROM
							                tbl_boleto_mes_unidade_itens_cobranca
							            WHERE
							                cd_boleto_mes_unidade = b.cd_boleto_mes_unidade)) AS vlr_total_boleto,
							    IFNULL(b.vlr_baixa_boleto, 0) AS vlr_baixa_boleto,
							    IFNULL(b.vlr_diferenca, 0) AS diferenca,
							    IF(b.st_baixado = 1,
							        IF(b.vlr_diferenca <> 0
							                AND b.st_diferenca = 1,
							            IF(b.vlr_diferenca > 0,
							                'PAGO A MENOR',
							                'PAGO A MAIOR'),
							            'FECHADO'),
							        'EM ABERTO') AS status_boleto,
							    IFNULL(abs(b.vlr_diferenca),0) as valor_diferenca,
							    CASE b.st_diferenca
							        WHEN 0 then 'PROCESSADA'
					        		WHEN 1 then 'À PROCESSAR'
					    		END AS status_diferenca,
					    		b.nosso_numero as nosso_numero
						  FROM
							    tbl_boleto_mes a,
							    tbl_boleto_mes_unidade b,
							    tbl_unidades c
						  WHERE
							    a.cd_boleto_mes = b.cd_boleto_mes
							        AND b.cd_unidade = c.cd_unidade
							        AND b.dt_vencimento is not null
							        {$sqlComplStatusBaixa}
							        {$sqlComplBoletoMes}
						  ORDER BY b.cd_unidade, b.dt_vencimento ASC";									
		
		$arrDados = P4A_DB::singleton()->fetchAll($sqlBuscaDados);
		$this->setFont("tahoma","",8);

		$this->cell(15,5,utf8_decode("UNIDADE"),1,0,"C");
		$this->cell(15,5,utf8_decode("DT VENC"),1,0,"C");
		$this->cell(20,5,utf8_decode("VLR. HIST"),1,0,"C");
		$this->cell(15,5,utf8_decode("DT PAGTO"),1,0,"C");
		$this->cell(20,5,utf8_decode("VLR. À PAGAR"),1,0,"C");
		$this->cell(20,5,utf8_decode("VLR. PAGO"),1,0,"C");
		$this->cell(15,5,utf8_decode("STATUS"),1,0,"C");
		$this->cell(25,5,utf8_decode("VLR. DIFERENÇA"),1,0,"C");
		$this->cell(20,5,utf8_decode("DIFERENCA"),1,0,"C");
		$this->cell(25,5,utf8_decode("NOSSO NUMERO"),1,0,"C");

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
		$this->cell(15,5,utf8_decode($dadosLinha["dt_vencimento"]),"BT",0,"C");
		$this->cell(20,5,utf8_decode(number_format($dadosLinha["vlr_hist"],2,",",".")),"BT",0,"C");
		$this->cell(15,5,utf8_decode($dadosLinha["dt_pagamento"]),"BT",0,"C");
		$this->cell(20,5,utf8_decode(number_format($dadosLinha["vlr_total_boleto"],2,",",".")),"BT",0,"C");
		$this->cell(20,5,utf8_decode(number_format($dadosLinha["vlr_baixa_boleto"],2,",",".")),"BT",0,"C");
		$this->cell(15,5,utf8_decode($dadosLinha["status_boleto"]),"BT",0,"C");
		$this->cell(25,5,utf8_decode(number_format($dadosLinha["valor_diferenca"],2,",",".")),"BT",0,"C");
		$this->cell(20,5,utf8_decode($dadosLinha["status_diferenca"]),"BT",0,"C");
		$this->cell(25,5,utf8_decode($dadosLinha["nosso_numero"]),"BT",0,"C");
		}
	
	function Footer()
		{
		$this->Ln();
		$this->Ln();	
		$this->cell(190,5,utf8_decode("Data extração: ".date("d/m/Y H:i:s")),0,0,"R");
		}

	function montaRelatorio()
		{

		}

	function Output()
		{
		return parent::Output("relProcessamentoBoleto.pdf","S");
		}

	}