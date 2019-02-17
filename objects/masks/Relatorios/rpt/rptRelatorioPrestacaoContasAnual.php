<?php

class rptRelatorioPrestacaoContasAnual extends satecmax_pdf
	{
		
	private $mesReferencia = "";
	private $anoReferencia = "";	
	
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
		$this->cell(200,5,utf8_decode("RELATORIO DE PRESTAÇÃO DE CONTAS - Anual - ".$this->anoReferencia),1,1,"C");
		}
		
	function setParametros($anoReferencia)
		{
		$strAnoReferencia = $anoReferencia;
		
		$this->anoReferencia = $strAnoReferencia;
		}
		
	function Footer()
		{				
		$this->setFont("tahoma","",6);
		$this->cell(200,10,utf8_decode("Condgest - O seu sistema de Gestão Condominial"),0,1,"R");
		}
		
	function montaRelatorio()
		{
		$this->AddPage();
		$this->ln(2);
		$this->setFont("tahoma","",8);
		$this->cell(150,7,utf8_decode("RECEITAS/HISTORICO"),1,0,"C");
		$this->cell(50,7,utf8_decode("VALORES EM R$"),1,1,"C");
		
		// pegar as receitas do mes...
		$sqlReceitasAno = "
							select
								b.plano_contas,
								b.ds_categoria,
								b.tp_taxa_condominial,
								sum(a.vlr_movimento) soma_vlr_movimento
							from
								caixa_movimento a,
								tbl_categorias b,
								caixa c
							where
									a.cd_plano_conta = b.cd_categoria
								and a.cd_caixa = c.cd_caixa
								and year(a.dt_movimento) = '{$this->anoReferencia}'
								and a.tp_movimento = 'E'
							group by 
								1,3
							order by 
								1,3
							";
		
		$arrReceitasMes = p4a_db::singleton()->fetchAll($sqlReceitasAno);
		
		$this->setFont("tahoma","",6);
		
		
		$cd_caixa = 0;
		$vlrTotalGeralReceitas = 0;
		foreach($arrReceitasMes as $nrLinha => $dadosLinha )
			{
			if ( $cd_caixa <> $dadosLinha["cd_caixa"] )
				{
				$cd_caixa = $dadosLinha["cd_caixa"];
				$this->cell(150,3,utf8_decode($dadosLinha["ds_caixa"]),"LR",0,"L");
				$this->cell(50,3,0,"LR",1,"L");
				}
			$this->cell(150,3,utf8_decode("    ".$dadosLinha["plano_contas"]." - ".$dadosLinha["ds_categoria"]),"LR",0,"L");
			$this->cell(50,3,utf8_decode(number_format($dadosLinha["soma_vlr_movimento"],2,",",".")),"LR",1,"R");
			$vlrTotalGeralReceitas += $dadosLinha["soma_vlr_movimento"];
			
			}
			
		$this->setFont("tahoma","",8);
			
		$this->cell(150,7,utf8_decode("Total Receitas"),1,0,"C");
		$this->cell(50,7,utf8_decode(number_format($vlrTotalGeralReceitas,2,",",".")),1,1,"R");

		$this->ln(2);
		$this->setFont("tahoma","",8);
		
		$this->cell(150,7,utf8_decode("DESPESAS/HISTORICO"),1,0,"C");
		$this->cell(50,7,utf8_decode("VALORES EM R$"),1,1,"C");
		
		// pegar as despesas do mes...
		$sqlDespesasMes = "
							select
								b.plano_contas,
								b.ds_categoria,
								b.tp_taxa_condominial,
								sum(a.vlr_movimento) soma_vlr_movimento
							from
								caixa_movimento a,
								tbl_categorias b,
								caixa c
							where
									a.cd_plano_conta = b.cd_categoria
								and a.cd_caixa = c.cd_caixa
								and year(a.dt_movimento) = '{$this->anoReferencia}'
								and a.tp_movimento = 'S'
							group by 
								1,2,3
							order by 
								1,2,3
							";
		
		$arrDespesasMes = p4a_db::singleton()->fetchAll($sqlDespesasMes);
		
		$this->setFont("tahoma","",6);
		
		$cd_caixa = 0;
		$vlrTotalGeralDespesas = 0;
		foreach($arrDespesasMes as $nrLinhaDespesa => $dadosLinhaDespesa )
			{
			/*	
	 		if ( $cd_caixa <> $dadosLinhaDespesa["cd_caixa"] )
				{
				$cd_caixa = $dadosLinhaDespesa["cd_caixa"];
				$this->cell(150,3,utf8_decode($dadosLinhaDespesa["ds_caixa"]),"LR",0,"L");
				$this->cell(50,3,0,"LR",1,"L");
				}
			*/
			$this->cell(150,3,utf8_decode("    ".$dadosLinhaDespesa["plano_contas"]." - ".$dadosLinhaDespesa["ds_categoria"]),"LR",0,"L");
			$this->cell(50,3,utf8_decode(number_format($dadosLinhaDespesa["soma_vlr_movimento"],2,",",".")),"LR",1,"R");
			$vlrTotalGeralDespesas += $dadosLinhaDespesa["soma_vlr_movimento"];
			
			}
		$this->setFont("tahoma","",8);
			
		$this->cell(150,7,utf8_decode("Total Despesas"),1,0,"C");
		$this->cell(50,7,utf8_decode(number_format($vlrTotalGeralDespesas,2,",",".")),1,1,"R");

		$this->ln(2);
		
		$this->setFont("tahoma","",8);
			
		$this->cell(200,7,utf8_decode("Resumo"),1,1,"C");

		$this->setFont("tahoma","",8);
		
		$this->cell(150,5,utf8_decode("Saldos Anteriores"),1,0,"C");
		$this->cell(50,5,utf8_decode("Valores em R$"),1,1,"C");
		
		$arrCaixas = P4A_DB::singleton()->fetchAll("select cd_caixa, ds_caixa from caixa");
		$mesReferencia = P4A_DB::singleton()->fetchAll("select min(month(dt_movimento)) from caixa_movimento where year(dt_movimento) = {$this->anoReferencia}");
		
		$dtSaldoAnterior = date("Y-m-d",strtotime("-1 day",strtotime($this->anoReferencia."-".$mesReferencia."-01")));
				
		$saldoAnteriorTotal = 0;
		
		$this->setFont("tahoma","",6);
				
		foreach($arrCaixas as $dadosCaixas)
			{
			$objCaixa = new movimentoCaixa($dadosCaixas["cd_caixa"]);
			$saldoAnteriorCaixa = $objCaixa->getSaldoCaixa(($this->anoReferencia - 1)."-12-31");
			$this->cell(150,3,utf8_decode($dadosCaixas["ds_caixa"]),"LR",0,"L");
			$this->cell(50,3,number_format($saldoAnteriorCaixa,2,",","."),"LR",1,"R");
			$saldoAnteriorTotal += $saldoAnteriorCaixa;
			}
		$this->setFont("tahoma","",8);
		
		
		$this->cell(150,7,utf8_decode("Saldo Anterior Total (A)"),1,0,"C");
		$this->cell(50,7,number_format($saldoAnteriorTotal,2,",","."),1,1,"R");
		
		$this->cell(150,7,utf8_decode("Total Receitas (B)"),1,0,"C");
		$this->cell(50,7,number_format($vlrTotalGeralReceitas,2,",","."),1,1,"R");
		
		$this->cell(150,7,utf8_decode("Total Despesas (C)"),1,0,"C");
		$this->cell(50,7,number_format($vlrTotalGeralDespesas,2,",","."),1,1,"R");
		
		$this->cell(150,7,utf8_decode("Saldo Final (A + B - C)"),1,0,"C");
		$this->cell(50,7,number_format(($saldoAnteriorTotal+$vlrTotalGeralReceitas)-$vlrTotalGeralDespesas,2,",","."),1,1,"R");
		
		//$this->Cell(185,1,"","LRB",0);
		//$this->Cell(100,1,"","LRB",1);
		}
		
	function Output()
		{
		$this->montaRelatorio();
		return parent::Output("rptRelatorioPrestacaoContaMensal.pdf","S");
		}
		
	}