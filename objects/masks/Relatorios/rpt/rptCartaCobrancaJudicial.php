<?php
class rptCartaCobrancaJudicial extends satecmax_pdf
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

		}
	
	function setParametros($cdUnidade)
		{	
		$this->SetXY(10, 15);
				
		$sql = "SELECT 
					d.cd_unidade,
					e.nm_pessoa,
					a.mes_ano_referencia as referencia,
					b.dt_vencimento as dt_vencimento,
					ROUND(SUM(c.vlr_item_boleto), 2) as vlr_historico,
					ROUND(SUM(c.vlr_item_boleto) * 0.02, 2) as vlr_multa,
					ROUND(SUM(c.vlr_item_boleto) * 0.00033 * DATEDIFF(DATE_ADD(CURRENT_DATE(),
										INTERVAL 0 DAY),
									b.dt_vencimento),
							2) as vlr_juros,
					IFNULL(ROUND(SUM(c.vlr_item_boleto) * ((SELECT 
																SUM(CASE
																		WHEN y.indice_correcao < 0 THEN 0.00
																		ELSE y.indice_correcao
																	END)
															FROM
																tbl_inpc y
															WHERE
																DATE_FORMAT(STR_TO_DATE(y.mes_ano_referencia, '%m/%Y'), '%Y/%m') 
																BETWEEN DATE_FORMAT(b.dt_vencimento, '%Y/%m') AND DATE_FORMAT(CURRENT_DATE(), '%Y/%m')) / 100), 2), 0.00) as atualizacao_monetaria,
					ROUND(
					SUM(c.vlr_item_boleto) + 
					SUM(c.vlr_item_boleto) * 0.02 + 
					SUM(c.vlr_item_boleto) * 0.00033 * DATEDIFF(DATE_ADD(CURRENT_DATE(), INTERVAL 0 DAY), b.dt_vencimento) + 	
					IFNULL(ROUND(SUM(c.vlr_item_boleto) * ((SELECT 
																SUM(CASE
																		WHEN y.indice_correcao < 0 THEN 0.00
																		ELSE y.indice_correcao
																	END)
															FROM
																tbl_inpc y
															WHERE
																DATE_FORMAT(STR_TO_DATE(y.mes_ano_referencia, '%m/%Y'), '%Y/%m') 
																BETWEEN DATE_FORMAT(b.dt_vencimento, '%Y/%m') AND DATE_FORMAT(CURRENT_DATE(), '%Y/%m')) / 100), 2), 0.00),
							2) vlr_total,
					CASE WHEN b.tp_boleto = '0' THEN
										'TX CONDOMINIAL'
									ELSE
										'MULTA'
									END AS observacao
				FROM
					tbl_boleto_mes a,
					tbl_boleto_mes_unidade b,
					tbl_boleto_mes_unidade_itens_cobranca c,
					tbl_unidades d,
					tbl_pessoas e
				WHERE
					a.cd_boleto_mes = b.cd_boleto_mes
						AND b.cd_boleto_mes_unidade = c.cd_boleto_mes_unidade
						AND b.cd_unidade = d.cd_unidade
						AND c.cd_unidade = d.cd_unidade
						AND d.cd_pessoa = e.cd_pessoa
						AND b.st_baixado = 0
						AND DATE_ADD(b.dt_vencimento,
						INTERVAL 1 DAY) < CURDATE()
						AND b.cd_unidade = {$cdUnidade}
				GROUP BY b.cd_boleto_mes_unidade
				UNION
				SELECT 
					a.cd_unidade AS 'UNIDADE',
					'' AS 'NM_PESSOA',
					'' AS 'REFERENCIA',
					b.dt_vencimento AS dt_vencimento,
					ROUND(b.vlr_parcela, 2) AS vlr_historico,
					ROUND(b.vlr_parcela * 0.02, 2) AS vlr_multa,
					ROUND(b.vlr_parcela * 0.01 * DATEDIFF(DATE_ADD(CURRENT_DATE(),
										INTERVAL 0 DAY),
									b.dt_vencimento) / 30,
							2) AS vlr_juros,
							IFNULL(ROUND(SUM(b.vlr_parcela) * ((SELECT 
								SUM(CASE
									WHEN y.indice_correcao < 0 THEN 0.00
									ELSE y.indice_correcao
								END)
						FROM
							tbl_inpc y
						WHERE
							DATE_FORMAT(STR_TO_DATE(y.mes_ano_referencia, '%m/%Y'),
									'%Y/%m') BETWEEN DATE_FORMAT(b.dt_vencimento, '%Y/%m') AND DATE_FORMAT(CURRENT_DATE(), '%Y/%m')) / 100), 2), 0.00) AS atualizacao_monetaria,
					ROUND(SUM(b.vlr_parcela) + 
					SUM(b.vlr_parcela) * 0.02 + 
					SUM(b.vlr_parcela) * 0.00033 * DATEDIFF(DATE_ADD(CURRENT_DATE(), INTERVAL 11 DAY), b.dt_vencimento) + 
					SUM(b.vlr_parcela) * ((SELECT 
												SUM(CASE
														WHEN y.indice_correcao < 0 THEN 0.00
														ELSE y.indice_correcao
													END)
											FROM
												tbl_inpc y
											WHERE
												DATE_FORMAT(STR_TO_DATE(y.mes_ano_referencia, '%m/%Y'),
														'%Y/%m') BETWEEN DATE_FORMAT(b.dt_vencimento, '%Y/%m') AND DATE_FORMAT(CURRENT_DATE(), '%Y/%m')) / 100),2) vlr_total,
					'PARCELA DE ACORDO' as observacao
				FROM
					acordos a
						INNER JOIN
					acordos_detalhes b ON a.cd_acordo = b.cd_acordo
				WHERE
					b.cd_st_recebimento = 1
					AND cd_unidade = {$cdUnidade}
					GROUP BY b.cd_acordo_detalhe
				ORDER BY dt_vencimento";
		
		$arrDados = P4A_DB::singleton()->fetchAll($sql);
		
		$this->AddPage();
		$this->setFont("ArialBlack","",20);
		$this->setY(30);
		$this->cell(190,5,utf8_decode("NOTIFICAÇÃO DE INADIMPLÊNCIA"),"",1,"C");
		$this->setLineWidth("0.5");
		$this->Line("82","38","120","38");
		
		foreach($arrDados as $linha => $dadosLinha )
			{
			$this->setY(55);
			$this->setFont("tahoma","",10);
			$this->setY(65);
			$this->Cell(190,10,utf8_decode("Morador da unidade ".$dadosLinha["cd_unidade"]." - "."Sr.(a) ".$dadosLinha["nm_pessoa"]),0,1,"L");
			$this->setY(80);
			$this->MultiCell(190,5,utf8_decode("Servimo-nos da presente, para informar que o condômino supracitado, encontra-se inadimplente das taxas de condomínio e do acordo firmado:"),0,1,"L");
			$this->Ln();
			$this->setFont("ArialBlack","",10);
			$this->cell(190,5,utf8_decode("TAXAS CONDOMINIAIS / MULTAS / ACORDOS"),"",1,"C");
			$this->Ln();
			}
		
		$this->setLineWidth("0.1");
		$this->setFont("tahoma","",7);
		$this->setY(105);
		$this->cell(25,5,utf8_decode("VENCIMENTO"),1,0,"C");
		$this->cell(25,5,utf8_decode("VLR. HIST."),1,0,"C");
		$this->cell(25,5,utf8_decode("MULTA"),1,0,"C");
		$this->cell(25,5,utf8_decode("ATUAL. MONET."),1,0,"C");
		$this->cell(25,5,utf8_decode("JUROS"),1,0,"C");
		$this->cell(25,5,utf8_decode("VLR. CORRIGIDO"),1,0,"C");
		$this->cell(30,5,utf8_decode("TIPO BOLETO"),1,0,"C");
				
		foreach($arrDados as $linha => $dadosLinha )
			{
			$this->ln();
			$this->imprimirLinhaDetalhes($dadosLinha);
			}
		$this->Ln();
		$this->imprimeTotalCategoria();
		
		$this->Ln();
		$this->Ln();
		$this->cell(190,5,utf8_decode("Araçariguama, ".formata_data_extenso(date("Y-m-d"),true)),0,1,"C");
		$this->Ln();
		$this->Ln();
		$this->cell(190,5,utf8_decode("Atenciosamente,"),0,1,"L");
		$this->ln();
		$this->setFont("tahoma","",9);
		$sqlCondominio = "SELECT
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
						    FROM
						   			tbl_condominio a, tbl_estados_brasileiros b
						   WHERE
									a.uf_condominio = b.cd_estado";
			
		$arrDadosCondominio = P4A_DB::singleton()->fetchAll($sqlCondominio);
			
		foreach($arrDadosCondominio as $condominio => $dadosCondominio )
			{
			$this->cell(190,5,utf8_decode("Administração"),0,1,"L");
			$this->cell(190,5,utf8_decode("Condomínio ".$dadosCondominio["nm_condominio"]),0,1,"L");
			$this->cell(190,5,utf8_decode("CNPJ: ".$dadosCondominio["nr_cnpj_condominio"]),0,1,"L");
			$this->cell(190,5,utf8_decode($dadosCondominio["logradouro_condominio"]." Nº ".$dadosCondominio["nr_logradouro_condominio"]." Compl. ".$dadosCondominio["compl_logradouro_condominio"]),0,1,"L");
			$this->cell(190,5,utf8_decode($dadosCondominio["bairro_condominio"]),0,1,"L");
			$this->cell(190,5,utf8_decode($dadosCondominio["municipio_condominio"])."/".utf8_decode($dadosCondominio["desc_estado"]). " - CEP:".$dadosCondominio["cep_condominio"],0,1,"L");
			$this->cell(190,5,utf8_decode("+55 ".$dadosCondominio["telefone_condominio"]),0,1,"L");
			$this->ln(10);
			}				
		}
		
	function imprimirLinhaDetalhes($dadosLinha)
		{					
		$this->setFont("tahoma","",7);
		$this->cell(25,5,utf8_decode(formatarDataAplicacao($dadosLinha["dt_vencimento"])),"BT",0,"C");
		$this->cell(25,5,utf8_decode("R$ ".number_format($dadosLinha["vlr_historico"],2,",",".")),"BT",0,"C");
		$this->cell(25,5,utf8_decode("R$ ".number_format($dadosLinha["vlr_multa"],2,",",".")),"BT",0,"C");
		$this->cell(25,5,utf8_decode("R$ ".number_format($dadosLinha["atualizacao_monetaria"],2,",",".")),"BT",0,"C");
		$this->cell(25,5,utf8_decode("R$ ".number_format($dadosLinha["vlr_juros"],2,",",".")),"BT",0,"C");
		$this->cell(25,5,utf8_decode("R$ ".number_format($dadosLinha["vlr_total"],2,",",".")),"BT",0,"C");
		$this->cell(30,5,utf8_decode($dadosLinha["observacao"]),"BT",0,"C");
		
		$this->vlr_historico += $dadosLinha["vlr_historico"];
		$this->vlr_multa += $dadosLinha["vlr_multa"];
		$this->vlr_atual_monetaria += $dadosLinha["atualizacao_monetaria"];
		$this->vlr_juros += $dadosLinha["vlr_juros"];
		$this->vlr_total += $dadosLinha["vlr_total"];
		}
	
		
	function imprimeTotalCategoria()
		{
		$this->setFont("tahoma","",8);
		$this->cell(25,5,"TOTAL","BT",0,"C");
		$this->cell(25,5,"R$ ".number_format($this->vlr_historico,2,",","."),"BT",0,"C");
		$this->cell(25,5,"R$ ".number_format($this->vlr_multa,2,",","."),"BT",0,"C");
		$this->cell(25,5,"R$ ".number_format($this->vlr_atual_monetaria,2,",","."),"BT",0,"C");
		$this->cell(25,5,"R$ ".number_format($this->vlr_juros,2,",","."),"BT",0,"C");
		$this->cell(25,5,"R$ ".number_format($this->vlr_total,2,",","."),"BT",0,"C");
		$this->cell(30,5,'',"BT",0,"C");
		}		
		
	function Footer()
		{				
		$this->setFont("tahoma","",8);
		$this->cell(210,10,utf8_decode("Condgest - O seu sistema de Gestão Condominial"),0,1,"C");
		}
	
	function montaRelatorio()
		{
		}
	
	function Output()
		{
		return parent::Output("rptCartaCobranca.pdf","S");
		}
	}