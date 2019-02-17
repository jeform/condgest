<?php
class rptBoletosDetalhes extends satecmax_pdf
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

	function setParametros($cdBoletoMes)
		{
		$sqlHeader= "select 
						    a.mes_ano_referencia,
						    (select 
						            count(*)
						        from
						            tbl_boleto_mes_unidade b
						        where
						            b.cd_boleto_mes = a.cd_boleto_mes) as qtde_boletos_emitidos,
						    ifnull(sum(c.vlr_item_boleto), 0) as vlr_boletos_emitidos,
						    ifnull((select 
						                    sum(vlr_baixa_boleto)
						                from
						                    tbl_boleto_mes_unidade
						                where
						                    tbl_boleto_mes_unidade.cd_boleto_mes = a.cd_boleto_mes),
						            0) as vlr_boletos_baixados,
						    ifnull((sum(c.vlr_item_boleto) - (select 
						                    sum(vlr_baixa_boleto)
						                from
						                    tbl_boleto_mes_unidade
						                where
						                    tbl_boleto_mes_unidade.cd_boleto_mes = a.cd_boleto_mes)),
						            0) as vlr_pendente
					   from
						    tbl_boleto_mes a,
						    tbl_boleto_mes_unidade b,
						    tbl_boleto_mes_unidade_itens_cobranca c
					  where
						    a.cd_boleto_mes = b.cd_boleto_mes
						        AND b.cd_boleto_mes_unidade = c.cd_boleto_mes_unidade
						        AND b.cd_unidade = c.cd_unidade
						        AND b.cd_boleto_mes = {$cdBoletoMes}";
			
		$arrDadosHeader = P4A_DB::singleton()->fetchAll($sqlHeader);

		$this->AddPage();
		$this->setFont("tahoma","",10);

		foreach($arrDadosHeader as $linha => $dadosLinhaHeader )
			{
			$this->cell(190,5,utf8_decode("Relatório Detalhado dos ítens cadastrados referente à ".$dadosLinhaHeader["mes_ano_referencia"]),1,1,"C");
			$this->ln();
			$this->ln();
			}

		$boleto_aberto = condgest::singleton()->getParametro("BOLETO_ABERTO");
		$boleto_fechado = condgest::singleton()->getParametro("BOLETO_FECHADO");
		$boleto_parcialmente_fechado = condgest::singleton()->getParametro("BOLETO_PARCIALMENTE_FECHADO");
		
		
		$sql = "SELECT 
					    a.cd_unidade as unidade,
					    date_format(a.dt_vencimento,'%d/%m/%Y') as dt_vencimento,
					    (select 
					            b.vlr_item_boleto
					        from
					            tbl_boleto_mes_unidade_itens_cobranca b
					        where
					            b.cd_unidade = a.cd_unidade
					                and b.cd_boleto_mes_unidade = a.cd_boleto_mes_unidade
					                and b.cd_item_cobranca = 1) as vlr_condominio,					   
					    (select 
					            b.vlr_item_boleto
					        from
					            tbl_boleto_mes_unidade_itens_cobranca b
					        where
					            b.cd_unidade = a.cd_unidade
					                and b.cd_boleto_mes_unidade = a.cd_boleto_mes_unidade
					                and b.cd_item_cobranca = 2) as vlr_benfeitoria,
					    (select 
					            b.vlr_item_boleto
					        from
					            tbl_boleto_mes_unidade_itens_cobranca b
					        where
					            b.cd_unidade = a.cd_unidade
					                and b.cd_boleto_mes_unidade = a.cd_boleto_mes_unidade
					                and b.cd_item_cobranca = 5) as vlr_rat_agua,
					    (select 
					            b.vlr_item_boleto
					        from
					            tbl_boleto_mes_unidade_itens_cobranca b
					        where
					            b.cd_unidade = a.cd_unidade
					                and b.cd_boleto_mes_unidade = a.cd_boleto_mes_unidade
					                and b.cd_item_cobranca = 4) as vlr_rateio,
					    (select 
					            sum(b.vlr_item_boleto)
					        from
					            tbl_boleto_mes_unidade_itens_cobranca b
					        where
					            b.cd_unidade = a.cd_unidade
					                and b.cd_boleto_mes_unidade = a.cd_boleto_mes_unidade) as vlr_total
				  FROM
					    tbl_boleto_mes_unidade a
			     WHERE 
						a.cd_boleto_mes = {$cdBoletoMes}";
	
								
		$arrDados = P4A_DB::singleton()->fetchAll($sql);


		$this->setFont("tahoma","",10);

		$this->cell(15,5,utf8_decode("ÁREA"),1,0,"C");
		$this->cell(25,5,utf8_decode("VENCIMENTO"),1,0,"C");
		$this->cell(30,5,utf8_decode("CONDOMÍNIO"),1,0,"C");
		$this->cell(30,5,utf8_decode("BENFEITORIA"),1,0,"C");
		$this->cell(30,5,utf8_decode("RATEIO"),1,0,"C");
		$this->cell(30,5,utf8_decode("RAT ÁGUA"),1,0,"C");
		$this->cell(30,5,utf8_decode("TOTAL"),1,0,"C");

		foreach($arrDados as $linha => $dadosLinha )
			{
			$this->ln();
			$this->imprimirLinhaDetalhes($dadosLinha);
			}
		$this->ln();	
		$this->imprimeTotalCategoria();
	}

	function imprimirLinhaDetalhes($dadosLinha)
		{
		$this->setFont("tahoma","",7);
		$this->cell(10,5,utf8_decode($dadosLinha["unidade"]),"BT",0,"C");
		$this->cell(30,5,utf8_decode($dadosLinha["dt_vencimento"]),"BT",0,"C");		
		$this->cell(30,5,utf8_decode("R$ ".number_format($dadosLinha["vlr_condominio"],2,",",".")),"BT",0,"C");
		$this->cell(30,5,utf8_decode("R$ ".number_format($dadosLinha["vlr_benfeitoria"],2,",",".")),"BT",0,"C");
		$this->cell(30,5,utf8_decode("R$ ".number_format($dadosLinha["vlr_rateio"],2,",",".")),"BT",0,"C");
		$this->cell(30,5,utf8_decode("R$ ".number_format($dadosLinha["vlr_rat_agua"],2,",",".")),"BT",0,"C");
		$this->cell(30,5,utf8_decode("R$ ".number_format($dadosLinha["vlr_total"],2,",",".")),"BT",0,"C");
		
		$this->vlr_condominio += $dadosLinha["vlr_condominio"];
		$this->vlr_benfeitoria += $dadosLinha["vlr_benfeitoria"];
		$this->vlr_rateio += $dadosLinha["vlr_rateio"];
		$this->vlr_rat_agua += $dadosLinha["vlr_rat_agua"];
		$this->vlr_total += $dadosLinha["vlr_total"];
		}

	function imprimeTotalCategoria()
		{
		$this->setFont("tahoma","",10);
		$this->cell(65,5,"R$ ".number_format($this->vlr_condominio,2,",","."),"BT",0,"R");
		$this->cell(30,5,"R$ ".number_format($this->vlr_benfeitoria,2,",","."),"BT",0,"R");
		$this->cell(30,5,"R$ ".number_format($this->vlr_rateio,2,",","."),"BT",0,"R");
		$this->cell(25,5,"R$ ".number_format($this->vlr_rat_agua,2,",","."),"BT",0,"R");
		$this->cell(40,5,"R$ ".number_format($this->vlr_total,2,",","."),"BT",0,"R");
		
		}
	
		
	function Footer()
		{

		}

	function montaRelatorio()
		{

		}

	function Output()
		{
		return parent::Output("boleto.pdf","S");
		}

	}