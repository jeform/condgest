<?php
class rptProcessamentoContaAguaSimplificado extends satecmax_pdf
	{

	private $arrDados;

	function __construct()
		{
		parent::satecmax_pdf("P","mm","A4");
		$this->SetLeftMargin(15);
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

	function setParametros($cdLeituraAguaMes)
		{
		$sqlHeader= "select 
							    a.mes_ano_referencia
						  from
							    tbl_leitura_agua_mes a
						 where
							    a.cd_leitura_agua_mes = {$cdLeituraAguaMes}";	
			
		$arrDadosHeader = P4A_DB::singleton()->fetchAll($sqlHeader);
		
		$this->AddPage();
		$this->setFont("tahoma","",6);
		
		foreach($arrDadosHeader as $linha => $dadosLinhaHeader )
			{
			$this->cell(175,3,utf8_decode("Relatório de Consumo de Água referente à ".$dadosLinhaHeader["mes_ano_referencia"]),1,1,"C");
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

		$this->setFont("tahoma","",5);
		
		$this->cell(10,3,utf8_decode("ÁREA"),1,0,"C");
		$this->cell(45,3,utf8_decode("CONDÔMINO"),1,0,"C");
		$this->cell(12,3,utf8_decode("INIC"),1,0,"C");
		$this->cell(12,3,utf8_decode("FINAL"),1,0,"C");
		$this->cell(12,3,utf8_decode("INIC"),1,0,"C");
		$this->cell(12,3,utf8_decode("FINAL"),1,0,"C");
		$this->cell(12,3,utf8_decode("CONS"),1,0,"C");
		$this->cell(15,3,utf8_decode("%"),1,0,"C");
		$this->cell(15,3,utf8_decode("VALOR"),1,0,"C");
		$this->cell(15,3,utf8_decode("TAXA"),1,0,"C");
		$this->cell(15,3,utf8_decode("TOTAL"),1,1,"C");
		
		foreach($arrDados as $linha => $dadosLinha )
			{
			$this->imprimirLinhaDetalhes($dadosLinha);
			$this->ln();
			}

		$this->imprimeTotalCategoria();
			
		}
		
	function imprimirLinhaDetalhes($dadosLinha)
		{
		$this->setFont("tahoma","",5);
		$this->cell(10,3,utf8_decode($dadosLinha["cd_unidade"]),"BT",0,"C");
		$this->cell(45,3,utf8_decode($dadosLinha["nm_pessoa"]),"BT",0,"C");
		$this->cell(12,3,utf8_decode($dadosLinha["leitura_inicial_1"]),"BT",0,"C");
		$this->cell(12,3,utf8_decode($dadosLinha["leitura_final_1"]),"BT",0,"C");
		$this->cell(12,3,utf8_decode($dadosLinha["leitura_inicial_2"]),"BT",0,"C");
		$this->cell(12,3,utf8_decode($dadosLinha["leitura_final_2"]),"BT",0,"C");
		$this->cell(12,3,utf8_decode($dadosLinha["total_consumido"]),"BT",0,"C");
		$this->cell(15,3,utf8_decode(number_format($dadosLinha["percent_rateio"],6,",",".")),"BT",0,"C");
		$this->cell(15,3,utf8_decode(number_format($dadosLinha["vlr_total"],2,",",".")),"BT",0,"C");
		$this->cell(15,3,utf8_decode(number_format($dadosLinha["taxa_consumo"],2,",",".")),"BT",0,"C");
		$this->cell(15,3,utf8_decode(number_format($dadosLinha["total"],2,",",".")),"BT",0,"C");
		
		$this->totalConsumo += $dadosLinha["total_consumido"];
		$this->totalCondominos += $dadosLinha["total"];
	 	}
	 	
	 function imprimeTotalCategoria($cdLeituraAguaMes)
	 	{
 		$this->setFont("tahoma","",8);
 	
 		$this->cell(105,5,utf8_decode("TOTAL CONSUMO"),"BT",0,"R");
 		$this->cell(20,5,utf8_decode($this->totalConsumo." m³"),"BT",0,"L");
 		$this->cell(30,5,utf8_decode("TOTAL"),"BT",0,"R");
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