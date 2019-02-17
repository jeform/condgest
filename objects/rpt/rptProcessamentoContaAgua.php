<?php
class rptProcessamentoContaAgua extends satecmax_pdf
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

	function setParametros($cdLeituraAguaMes)
		{
		$sqlHeader= "select 
							    a.mes_ano_referencia,
								a.total_consumido,	
							    (a.vlr_consumido + ifnull(vlr_consumido_1,0) + a.vlr_adicionais + (a.vlr_taxa_consumo * 
							    (select 
							            count(*)
							       from
							            tbl_unidades
							      where
							            st_unidade = 1 and st_hidrometro = 1))) as 'vlr_total_presumido',
							    sum(b.vlr_total) as 'vlr_total_individual'
						  from
							    tbl_leitura_agua_mes a,
							    tbl_leitura_agua_mes_individual b
						 where
							    a.cd_leitura_agua_mes = b.cd_leitura_agua_mes
							        and a.cd_leitura_agua_mes = {$cdLeituraAguaMes}";	
			
		$arrDadosHeader = P4A_DB::singleton()->fetchAll($sqlHeader);
		
		$this->AddPage();
		$this->setFont("tahoma","",10);
		
		foreach($arrDadosHeader as $linha => $dadosLinhaHeader )
			{
			$this->cell(190,5,utf8_decode("Relatório de Consumo de Água referente à ".$dadosLinhaHeader["mes_ano_referencia"]),1,1,"C");
			$this->ln();
			$this->cell(70,5,utf8_decode("Total Consumido: ".$dadosLinhaHeader["total_consumido"]." m³"),1,0,"C");
			$this->cell(60,5,utf8_decode("Valor Presumido: R$ ".number_format($dadosLinhaHeader["vlr_total_presumido"],2,",",".")),1,0,"C");
			$this->cell(60,5,utf8_decode("Total Total: R$ ".number_format($dadosLinhaHeader["vlr_total_individual"],2,",",".")),1,1,"C");
			$this->ln();
			}
		
			
		$sql = "
				select 
					    b.cd_unidade,
						d.nm_pessoa,
					    b.leitura_inicial_1,
					    b.leitura_final_1,
					    b.leitura_inicial_2,
					    b.leitura_final_2,
					    b.total_consumido,
					    b.percent_rateio,
					    round(b.vlr_taxa_consumo / 0.9, 2) as 'taxa_consumo',
					    round(b.vlr_agua + b.vlr_energia,2) as 'vlr_hist',
					    round(b.vlr_agua / 0.9, 2) + round(b.vlr_energia / 0.9,2) as 'vlr_total',
					    round((round(b.vlr_taxa_consumo / 0.9, 2)) + round(b.vlr_agua / 0.9, 2) + round(b.vlr_energia / 0.9,2),2) as 'total'
					from
					    tbl_leitura_agua_mes a,
					    tbl_leitura_agua_mes_individual b,
					    tbl_unidades c,
						tbl_pessoas d
					where
					    a.cd_leitura_agua_mes = b.cd_leitura_agua_mes
					        and b.cd_unidade = c.cd_unidade
							and c.cd_pessoa = d.cd_pessoa 
				    		and b.cd_leitura_agua_mes = {$cdLeituraAguaMes}";
			
		$arrDados = P4A_DB::singleton()->fetchAll($sql);

		
		$this->setFont("tahoma","",10);
		
		
		
		$this->cell(10,5,utf8_decode("ÁREA"),1,0,"C");
		$this->cell(45,5,utf8_decode("CONDÔMINO"),1,0,"C");
		$this->cell(12,5,utf8_decode("INIC"),1,0,"C");
		$this->cell(12,5,utf8_decode("FINAL"),1,0,"C");
		$this->cell(12,5,utf8_decode("INIC"),1,0,"C");
		$this->cell(12,5,utf8_decode("FINAL"),1,0,"C");
		$this->cell(12,5,utf8_decode("CONS"),1,0,"C");
		$this->cell(15,5,utf8_decode("%"),1,0,"C");
		$this->cell(15,5,utf8_decode("VL HIST"),1,0,"C");
		$this->cell(15,5,utf8_decode("VALOR"),1,0,"C");
		$this->cell(15,5,utf8_decode("TAXA"),1,0,"C");
		$this->cell(15,5,utf8_decode("TOTAL"),1,1,"C");
		
		foreach($arrDados as $linha => $dadosLinha )
			{


			$this->imprimirLinhaDetalhes($dadosLinha);
			$this->ln();
			}
			
		$this->imprimeTotalCategoria();
		}
		
	function imprimirLinhaDetalhes($dadosLinha)
		{
		$this->setFont("tahoma","",7);
		$this->cell(10,5,utf8_decode($dadosLinha["cd_unidade"]),"BT",0,"C");
		$this->cell(45,5,utf8_decode($dadosLinha["nm_pessoa"]),"BT",0,"C");
		$this->cell(12,5,utf8_decode($dadosLinha["leitura_inicial_1"]),"BT",0,"C");
		$this->cell(12,5,utf8_decode($dadosLinha["leitura_final_1"]),"BT",0,"C");
		$this->cell(12,5,utf8_decode($dadosLinha["leitura_inicial_2"]),"BT",0,"C");
		$this->cell(12,5,utf8_decode($dadosLinha["leitura_final_2"]),"BT",0,"C");
		$this->cell(12,5,utf8_decode($dadosLinha["total_consumido"]),"BT",0,"C");
		$this->cell(15,5,utf8_decode(number_format($dadosLinha["percent_rateio"],6,",",".")),"BT",0,"C");
		$this->cell(15,5,utf8_decode(number_format($dadosLinha["vlr_hist"],2,",",".")),"BT",0,"C");
		$this->cell(15,5,utf8_decode(number_format($dadosLinha["vlr_total"],2,",",".")),"BT",0,"C");
		$this->cell(15,5,utf8_decode(number_format($dadosLinha["taxa_consumo"],2,",",".")),"BT",0,"C");
		$this->cell(15,5,utf8_decode(number_format($dadosLinha["total"],2,",",".")),"BT",0,"C");

		
		$this->totalConsumo += $dadosLinha["total_consumido"];
		$this->totalCondominos += $dadosLinha["vlr_hist"];
	 	}

	function imprimeTotalCategoria($cdLeituraAguaMes)
		{
		$this->setFont("tahoma","",10);
		
		$this->cell(105,5,utf8_decode("TOTAL CONSUMO"),"BT",0,"R");
		$this->cell(20,5,utf8_decode($this->totalConsumo." m³"),"BT",0,"L");
		$this->cell(45,5,utf8_decode("TOTAL"),"BT",0,"R");
		$this->cell(20,5,"R$ ".number_format($this->totalCondominos + 2 * 73 ,2,",","."),"BT",0,"R");
		
		}
			
	function Footer()
		{

		}

	function montaRelatorio()
		{

		}

	function Output()
		{
		return parent::Output("rateio.pdf","S");
		}
	
	}