<?php
class rptProcesso extends satecmax_pdf
	{
	private $arrDados;
	private $vlr_total_acordo;
	private $vlr_total_taxa_condominial;
	
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
						a.cd_unidade,
						d.nm_pessoa,
						DATE_FORMAT(b.dt_vencimento, '%m/%Y'),
						b.dt_vencimento as dt_vencimento,
						b.vlr_parcela as vlr_parcela,
						ROUND(b.vlr_parcela * 0.1,2) as vlr_multa,
						IF (DATE_ADD(b.dt_vencimento, INTERVAL 1 DAY) < CURDATE(),
							ROUND(b.vlr_parcela * 0.01 * DATEDIFF(DATE_ADD(CURRENT_DATE(), INTERVAL 0 DAY), b.dt_vencimento) / 30,2),
							ROUND(b.vlr_parcela * 0.00,2)) as vlr_juros,
						IF (DATE_ADD(b.dt_vencimento, INTERVAL 1 DAY) < CURDATE(),
							ROUND(IFNULL( b.vlr_parcela * ((SELECT
																	SUM(y.indice_correcao)
															  FROM
																	tbl_correcao_monetaria y
															 WHERE
																	DATE_FORMAT(STR_TO_DATE(y.mes_ano_referencia, '%m/%Y'),'%Y/%m')
																	BETWEEN DATE_FORMAT(STR_TO_DATE(b.dt_vencimento, '%m/%Y'),'%Y/%m')
																		AND DATE_FORMAT(CURRENT_DATE(), '%Y/%m')) / 100),0),2),
							ROUND(b.vlr_parcela * 0.00,2)) as vlr_atu_monetaria,
						ROUND(b.vlr_parcela * 0.1,2) +
						IF(DATE_ADD(b.dt_vencimento, INTERVAL 1 DAY) < CURDATE(),
							(b.vlr_parcela +
							ROUND(b.vlr_parcela * 0.01 * DATEDIFF(DATE_ADD(CURRENT_DATE(), INTERVAL 0 DAY), b.dt_vencimento) / 30,2) +
							ROUND(IFNULL( b.vlr_parcela * ((SELECT
																	SUM(y.indice_correcao)
															  FROM
																	tbl_correcao_monetaria y
															 WHERE
																	DATE_FORMAT(STR_TO_DATE(y.mes_ano_referencia, '%m/%Y'),'%Y/%m')
																	BETWEEN DATE_FORMAT(STR_TO_DATE(b.dt_vencimento, '%m/%Y'),'%Y/%m')
																	AND DATE_FORMAT(CURRENT_DATE(), '%Y/%m')) / 100),0),2)),
							b.vlr_parcela) as vlr_total
				  FROM
						acordos a,
						acordos_detalhes b,
						tbl_unidades c,
						tbl_pessoas d
				 WHERE
						a.cd_acordo = b.cd_acordo
							AND a.cd_unidade = c.cd_unidade
							AND c.cd_pessoa = d.cd_pessoa
							AND b.cd_st_recebimento = 1
							AND a.cd_unidade = {$cdUnidade}";
		
		$arrDados = P4A_DB::singleton()->fetchAll($sql);
		
		$this->AddPage();
		$this->setFont("ArialBlack","",16);
		$this->setY(30);
		$this->cell(190,5,utf8_decode("INADIMPLÊNCIA PARA INGRESSO DE AÇÃO"),"",1,"C");
		$this->setLineWidth("0.5");
		$this->Line("20","38","180","38");
		
		foreach($arrDados as $linha => $dadosLinha )
			{
			$this->setY(55);
			$this->setFont("tahoma","",10);
			$this->setY(50);
			$this->Cell(190,10,utf8_decode("Morador da unidade ".$dadosLinha["cd_unidade"]." - "."Sr.(a) ".$dadosLinha["nm_pessoa"]."."),0,1,"L");
			$this->MultiCell(190,5,utf8_decode("Servimo-nos da presente, para informar que o condômino supracitado, encontra-se inadimplente do Acordo firmado e das Taxas Condominiais."),0,1,"L");
			$this->Ln();
			$this->Ln();
			$this->setFont("ArialBlack","",10);
			$this->cell(190,5,utf8_decode("DETALHES DAS PARCELAS EM ABERTO DO ACORDO EM ATRASO"),"",1,"C");
			$this->Ln();
			}
		
		$this->setLineWidth("0.1");
		$this->setFont("tahoma","",10);
		$this->cell(30,5,utf8_decode("VENCIMENTO"),1,0,"C");
		$this->cell(32,5,utf8_decode("VLR. HIST."),1,0,"C");
		$this->cell(32,5,utf8_decode("MULTA"),1,0,"C");
		$this->cell(32,5,utf8_decode("ATUAL. MONET."),1,0,"C");
		$this->cell(32,5,utf8_decode("JUROS"),1,0,"C");
		$this->cell(32,5,utf8_decode("VLR. CORRIGIDO"),1,0,"C");
				
		foreach($arrDados as $linha => $dadosLinha )
			{
			$this->ln();
			$this->imprimirLinhaDetalhes($dadosLinha);
			}
		$this->Ln();
		$this->imprimeTotalCategoria();
		
		$sqlTaxas = "SELECT 
							d.cd_unidade,
							e.nm_pessoa,
	    					a.mes_ano_referencia as referencia,
	    					b.dt_vencimento as dt_vencimento,
	    					ROUND(SUM(c.vlr_item_boleto), 2) as vlr_historico,
	    					ROUND(SUM(c.vlr_item_boleto) * 0.02, 2) as vlr_multa,
	    					ROUND(SUM(c.vlr_item_boleto) * 0.01 * DATEDIFF(DATE_ADD(CURRENT_DATE(),INTERVAL 0 DAY), b.dt_vencimento) / 30
	    					  , 2) as vlr_juros,
	    					IFNULL(ROUND(SUM(c.vlr_item_boleto) * 
	    						   ((SELECT 
	                    					SUM(y.indice_correcao)
	                				   FROM
	                    					tbl_correcao_monetaria y
	                				  WHERE
	                    					DATE_FORMAT(STR_TO_DATE(y.mes_ano_referencia, '%m/%Y'),'%Y/%m') 
	                    						BETWEEN DATE_FORMAT(STR_TO_DATE(a.mes_ano_referencia, '%m/%Y'),'%Y/%m') 
	                    							AND DATE_FORMAT(CURRENT_DATE(), '%Y/%m')) / 100)
	                    	   ,2),0) as atualizacao_monetaria,
	    					ROUND((SUM(c.vlr_item_boleto) + 
	    						   SUM(c.vlr_item_boleto) * 0.02 + 
	    						  (SUM(c.vlr_item_boleto) * 0.01 * DATEDIFF(DATE_ADD(CURRENT_DATE(), INTERVAL 0 DAY), b.dt_vencimento) / 30)) + 
	    						  IFNULL(SUM(c.vlr_item_boleto) * 
	    						   		((SELECT
	                    							SUM(y.indice_correcao)
	                						FROM
	                    							tbl_correcao_monetaria y
	                					   WHERE
	                    							DATE_FORMAT(STR_TO_DATE(y.mes_ano_referencia, '%m/%Y'),'%Y/%m') 
	                    								BETWEEN DATE_FORMAT(STR_TO_DATE(a.mes_ano_referencia, '%m/%Y'),'%Y/%m') 
	                    									AND DATE_FORMAT(CURRENT_DATE(), '%Y/%m')) / 100),0)
	                    	 ,2) vlr_total
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
								AND DATE_ADD(b.dt_vencimento, INTERVAL 1 DAY) < CURDATE()
								AND b.cd_unidade = {$cdUnidade}
				   GROUP BY b.cd_boleto_mes_unidade";
		
		$arrDadosTaxas = P4A_DB::singleton()->fetchAll($sqlTaxas);
		
		$this->Ln();
		$this->Ln();
		$this->setFont("ArialBlack","",10);
		$this->cell(190,5,utf8_decode("DETALHES DAS TAXAS CONDOMINIAIS EM ATRASO"),"",1,"C");
		
		$this->setLineWidth("0.1");
		$this->setFont("tahoma","",10);
		$this->Ln();
		$this->cell(30,5,utf8_decode("VENCIMENTO"),1,0,"C");
		$this->cell(32,5,utf8_decode("VLR. HIST."),1,0,"C");
		$this->cell(32,5,utf8_decode("MULTA"),1,0,"C");
		$this->cell(32,5,utf8_decode("ATUAL. MONET."),1,0,"C");
		$this->cell(32,5,utf8_decode("JUROS ACUMUL."),1,0,"C");
		$this->cell(32,5,utf8_decode("VLR. CORRIGIDO"),1,0,"C");
		
		foreach($arrDadosTaxas as $linhaTaxas => $dadosLinhaTaxas )
			{
			$this->ln();
			$this->imprimirLinhaDetalhesTaxas($dadosLinhaTaxas);
			}
		$this->Ln();
		$this->imprimeTotalCategoriaTaxas();
		
		$this->Ln();
		$this->Ln();
		$this->setFont("tahoma","",10);
		$this->MultiCell(190,5,utf8_decode("Assim, o montante total do débito é de R$ ".number_format($this->vlr_total + $this->vlr_total_taxas,2,",",".")."."),0,1,"L");
		
		$this->Ln();
		$this->Ln();
		$this->cell(190,5,utf8_decode("Araçariguama, ".formata_data_extenso(date("Y-m-d",true))),0,1,"C");
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
		$this->cell(30,5,utf8_decode(formatarDataAplicacao($dadosLinha["dt_vencimento"])),"BT",0,"C");
		$this->cell(32,5,utf8_decode("R$ ".number_format($dadosLinha["vlr_parcela"],2,",",".")),"BT",0,"C");
		$this->cell(32,5,utf8_decode("R$ ".number_format($dadosLinha["vlr_multa"],2,",",".")),"BT",0,"C");
		$this->cell(32,5,utf8_decode("R$ ".number_format($dadosLinha["atualizacao_monetaria"],2,",",".")),"BT",0,"C");
		$this->cell(32,5,utf8_decode("R$ ".number_format($dadosLinha["vlr_juros"],2,",",".")),"BT",0,"C");
		$this->cell(32,5,utf8_decode("R$ ".number_format($dadosLinha["vlr_total"],2,",",".")),"BT",0,"C");		
		
		$this->vlr_parcela += $dadosLinha["vlr_parcela"];
		$this->vlr_multa += $dadosLinha["vlr_multa"];
		$this->vlr_atual_monetaria += $dadosLinha["atualizacao_monetaria"];
		$this->vlr_juros += $dadosLinha["vlr_juros"];
		$this->vlr_total += $dadosLinha["vlr_total"];
		}

	function imprimirLinhaDetalhesTaxas($dadosLinhaTaxas)
		{
		$this->setFont("tahoma","",7);
		$this->cell(30,5,utf8_decode(formatarDataAplicacao($dadosLinhaTaxas["dt_vencimento"])),"BT",0,"C");
		$this->cell(32,5,utf8_decode("R$ ".number_format($dadosLinhaTaxas["vlr_historico"],2,",",".")),"BT",0,"C");
		$this->cell(32,5,utf8_decode("R$ ".number_format($dadosLinhaTaxas["vlr_multa"],2,",",".")),"BT",0,"C");
		$this->cell(32,5,utf8_decode("R$ ".number_format($dadosLinhaTaxas["atualizacao_monetaria"],2,",",".")),"BT",0,"C");
		$this->cell(32,5,utf8_decode("R$ ".number_format($dadosLinhaTaxas["vlr_juros"],2,",",".")),"BT",0,"C");
		$this->cell(32,5,utf8_decode("R$ ".number_format($dadosLinhaTaxas["vlr_total"],2,",",".")),"BT",0,"C");
	
		$this->vlr_historico_taxas += $dadosLinhaTaxas["vlr_historico"];
		$this->vlr_multa_taxas += $dadosLinhaTaxas["vlr_multa"];
		$this->vlr_atual_monetaria_taxas += $dadosLinha["atualizacao_monetaria"];
		$this->vlr_juros_taxas += $dadosLinhaTaxas["vlr_juros"];
		$this->vlr_total_taxas += $dadosLinhaTaxas["vlr_total"];
		}	
		
	function imprimeTotalCategoria()
		{
		$this->setFont("tahoma","",8);
		$this->cell(30,5,"TOTAL","BT",0,"C");
		$this->cell(32,5,"R$ ".number_format($this->vlr_parcela,2,",","."),"BT",0,"C");
		$this->cell(32,5,"R$ ".number_format($this->vlr_multa,2,",","."),"BT",0,"C");
		$this->cell(32,5,"R$ ".number_format($this->vlr_atual_monetaria,2,",","."),"BT",0,"C");
		$this->cell(32,5,"R$ ".number_format($this->vlr_juros,2,",","."),"BT",0,"C");
		$this->cell(32,5,"R$ ".number_format($this->vlr_total,2,",","."),"BT",0,"C");
		}		
		
	function imprimeTotalCategoriaTaxas()
		{
		$this->setFont("tahoma","",8);
		$this->cell(30,5,"TOTAL","BT",0,"C");
		$this->cell(32,5,"R$ ".number_format($this->vlr_historico_taxas,2,",","."),"BT",0,"C");
		$this->cell(32,5,"R$ ".number_format($this->vlr_multa_taxas,2,",","."),"BT",0,"C");
		$this->cell(32,5,"R$ ".number_format($this->vlr_atual_monetaria_taxas,2,",","."),"BT",0,"C");
		$this->cell(32,5,"R$ ".number_format($this->vlr_juros_taxas,2,",","."),"BT",0,"C");
		$this->cell(32,5,"R$ ".number_format($this->vlr_total_taxas,2,",","."),"BT",0,"C");
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
		return parent::Output("rptCartaProcesso.pdf","S");
		}
	}