<?php
class rptRelatorioDemonstrativoReceitasDespesas extends satecmax_pdf
	{
	private $anoReferencia = "";	
	
	public function __construct($orientation='P',$unit='mm',$format='A4')
		{
		parent::satecmax_pdf("L","mm","A4");
		$this->SetLeftMargin(5);
		$this->AliasNbPages();
		$this->SetDrawColor(0,0,0);
		$this->SetFillColor(0,0,0);
		$this->SetLineWidth(.1);
		$this->SetAutoPageBreak(true, 9);
		$this->setFont("tahoma","",10);
		}

	function setParametros($anoReferencia)
		{
		$strAnoReferencia = $anoReferencia;
		
		$this->anoReferencia = $strAnoReferencia;
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
		$this->setFont("tahoma","",10);
		$this->Ln(2);
		$this->cell(284,5,utf8_decode("DEMONSTRATIVO DE RECEITAS E DESPESAS - ".$this->anoReferencia),1,1,"C");
		}
		
	function Footer()
		{				
		$this->setFont("tahoma","",6);
		$this->cell(200,10,utf8_decode("Condgest - O seu sistema de Gestão Condominial"),0,1,"R");
		}
		
	function montaRelatorio()
		{
		$this->AddPage();
		$this->setFont("tahoma","",9);
		$this->ln(2);
		$this->cell(284,5,utf8_decode("DEMONSTRATIVO DE RECEITAS EM (R$)"),1,1,"C");
		$this->ln(2);
		$this->setFont("tahoma","",8);
		$this->cell(15,4,utf8_decode("CONTA"),1,0,"C");
		$this->cell(65,4,utf8_decode("DESCRIÇÃO"),1,0,"C");
		$this->cell(17,4,utf8_decode("01/".$this->anoReferencia),1,0,"C");
		$this->cell(17,4,utf8_decode("02/".$this->anoReferencia),1,0,"C");
		$this->cell(17,4,utf8_decode("03/".$this->anoReferencia),1,0,"C");
		$this->cell(17,4,utf8_decode("04/".$this->anoReferencia),1,0,"C");
		$this->cell(17,4,utf8_decode("05/".$this->anoReferencia),1,0,"C");
		$this->cell(17,4,utf8_decode("06/".$this->anoReferencia),1,0,"C");
		$this->cell(17,4,utf8_decode("07/".$this->anoReferencia),1,0,"C");
		$this->cell(17,4,utf8_decode("08/".$this->anoReferencia),1,0,"C");
		$this->cell(17,4,utf8_decode("09/".$this->anoReferencia),1,0,"C");
		$this->cell(17,4,utf8_decode("10/".$this->anoReferencia),1,0,"C");
		$this->cell(17,4,utf8_decode("11/".$this->anoReferencia),1,0,"C");
		$this->cell(17,4,utf8_decode("12/".$this->anoReferencia),1,0,"C");
		$this->Ln();				
		
		$sqlDemonstrativoReceitas = "
									select 
										    a.plano_contas,
										    a.ds_categoria,
										    ifnull(SUM(CASE
										                WHEN month(b.dt_movimento) = '01' THEN ifnull(b.vlr_movimento, 0)
										            END),
										            0) as 'janeiro',
										    ifnull(SUM(CASE
										                WHEN month(b.dt_movimento) = '02' THEN ifnull(b.vlr_movimento, 0)
										            END),
										            0) as 'fevereiro',
										    ifnull(SUM(CASE
										                WHEN month(b.dt_movimento) = '03' THEN ifnull(b.vlr_movimento, 0)
										            END),
										            0) as 'marco',
										    ifnull(SUM(CASE
										                WHEN month(b.dt_movimento) = '04' THEN ifnull(b.vlr_movimento, 0)
										            END),
										            0) as 'abril',
										    ifnull(SUM(CASE
										                WHEN month(b.dt_movimento) = '05' THEN ifnull(b.vlr_movimento, 0)
										            END),
										            0) as 'maio',
										    ifnull(SUM(CASE
										                WHEN month(b.dt_movimento) = '06' THEN ifnull(b.vlr_movimento, 0)
										            END),
										            0) as 'junho',
										    ifnull(SUM(CASE
										                WHEN month(b.dt_movimento) = '07' THEN ifnull(b.vlr_movimento, 0)
										            END),
										            0) as 'julho',
										    ifnull(SUM(CASE
										                WHEN month(b.dt_movimento) = '08' THEN ifnull(b.vlr_movimento, 0)
										            END),
										            0) as 'agosto',
										    ifnull(SUM(CASE
										                WHEN month(b.dt_movimento) = '09' THEN ifnull(b.vlr_movimento, 0)
										            END),
										            0) as 'setembro',
										    ifnull(SUM(CASE
										                WHEN month(b.dt_movimento) = '10' THEN ifnull(b.vlr_movimento, 0)
										            END),
										            0) as 'outubro',
										    ifnull(SUM(CASE
										                WHEN month(b.dt_movimento) = '11' THEN ifnull(b.vlr_movimento, 0)
										            END),
										            0) as 'novembro',
										    ifnull(SUM(CASE
										                WHEN month(b.dt_movimento) = '12' THEN ifnull(b.vlr_movimento, 0)
										            END),
										            0) as 'dezembro'
									from
										    tbl_categorias a
										        inner join
										    caixa_movimento b ON a.cd_categoria = b.cd_plano_conta 
										    	and year(dt_movimento) = '{$this->anoReferencia}'
										    	and a.tp_categoria = 1	
										    	and b.tp_movimento = 'E'								    
										group by a.cd_categoria , ds_categoria
										order by a.plano_contas
										";
		
		$arrReceitasAno = p4a_db::singleton()->fetchAll($sqlDemonstrativoReceitas);
		
		$this->setFont("tahoma","",6);
		
		$vlrTotalReceitas = 0;
		foreach($arrReceitasAno as $nrLinha => $dadosLinhaReceitas )
			{
			$this->cell(15,4,utf8_decode($dadosLinhaReceitas["plano_contas"]),1,0,"L");
			$this->cell(65,4,utf8_decode($dadosLinhaReceitas["ds_categoria"]),1,0,"L");
			$this->cell(17,4,utf8_decode(number_format($dadosLinhaReceitas["janeiro"],2,",",".")),1,0,"R");
			$this->cell(17,4,utf8_decode(number_format($dadosLinhaReceitas["fevereiro"],2,",",".")),1,0,"R");
			$this->cell(17,4,utf8_decode(number_format($dadosLinhaReceitas["marco"],2,",",".")),1,0,"R");
			$this->cell(17,4,utf8_decode(number_format($dadosLinhaReceitas["abril"],2,",",".")),1,0,"R");
			$this->cell(17,4,utf8_decode(number_format($dadosLinhaReceitas["maio"],2,",",".")),1,0,"R");
			$this->cell(17,4,utf8_decode(number_format($dadosLinhaReceitas["junho"],2,",",".")),1,0,"R");
			$this->cell(17,4,utf8_decode(number_format($dadosLinhaReceitas["julho"],2,",",".")),1,0,"R");
			$this->cell(17,4,utf8_decode(number_format($dadosLinhaReceitas["agosto"],2,",",".")),1,0,"R");
			$this->cell(17,4,utf8_decode(number_format($dadosLinhaReceitas["setembro"],2,",",".")),1,0,"R");
			$this->cell(17,4,utf8_decode(number_format($dadosLinhaReceitas["outubro"],2,",",".")),1,0,"R");
			$this->cell(17,4,utf8_decode(number_format($dadosLinhaReceitas["novembro"],2,",",".")),1,0,"R");
			$this->cell(17,4,utf8_decode(number_format($dadosLinhaReceitas["dezembro"],2,",",".")),1,0,"R");
			$this->Ln();
			
			$vlrTotalJaneiro += $dadosLinhaReceitas["janeiro"];
			$vlrTotalFevereiro += $dadosLinhaReceitas["fevereiro"];
			$vlrTotalMarco += $dadosLinhaReceitas["marco"];
			$vlrTotalAbril += $dadosLinhaReceitas["abril"];
			$vlrTotalMaio += $dadosLinhaReceitas["maio"];
			$vlrTotalJunho += $dadosLinhaReceitas["junho"];
			$vlrTotalJulho += $dadosLinhaReceitas["julho"];
			$vlrTotalAgosto += $dadosLinhaReceitas["agosto"];
			$vlrTotalSetembro+= $dadosLinhaReceitas["setembro"];
			$vlrTotalOutubro += $dadosLinhaReceitas["outubro"];
			$vlrTotalNovembro += $dadosLinhaReceitas["novembro"];
			$vlrTotalDezembro += $dadosLinhaReceitas["dezembro"];
			}
		
		$this->cell(80,5,utf8_decode("Total Receitas em (R$)"),1,0,"C");
		$this->cell(17,5,utf8_decode(number_format($vlrTotalJaneiro,2,",",".")),1,0,"R");
		$this->cell(17,5,utf8_decode(number_format($vlrTotalFevereiro,2,",",".")),1,0,"R");
		$this->cell(17,5,utf8_decode(number_format($vlrTotalMarco,2,",",".")),1,0,"R");
		$this->cell(17,5,utf8_decode(number_format($vlrTotalAbril,2,",",".")),1,0,"R");
		$this->cell(17,5,utf8_decode(number_format($vlrTotalMaio,2,",",".")),1,0,"R");
		$this->cell(17,5,utf8_decode(number_format($vlrTotalJunho,2,",",".")),1,0,"R");
		$this->cell(17,5,utf8_decode(number_format($vlrTotalJulho,2,",",".")),1,0,"R");
		$this->cell(17,5,utf8_decode(number_format($vlrTotalAgosto,2,",",".")),1,0,"R");
		$this->cell(17,5,utf8_decode(number_format($vlrTotalSetembro,2,",",".")),1,0,"R");
		$this->cell(17,5,utf8_decode(number_format($vlrTotalOutubro,2,",",".")),1,0,"R");
		$this->cell(17,5,utf8_decode(number_format($vlrTotalNovembro,2,",",".")),1,0,"R");
		$this->cell(17,5,utf8_decode(number_format($vlrTotalDezembro,2,",",".")),1,0,"R");
		
		$this->AddPage();
		$this->setFont("tahoma","",9);
		$this->Ln(2);
		$this->cell(284,5,utf8_decode("DEMONSTRATIVO DE DESPESAS EM (R$)"),1,1,"C");
		$this->ln(2);
		$this->cell(15,4,utf8_decode("CONTA"),1,0,"C");
		$this->cell(65,4,utf8_decode("DESCRIÇÃO"),1,0,"C");
		$this->cell(17,4,utf8_decode("01/".$this->anoReferencia),1,0,"C");
		$this->cell(17,4,utf8_decode("02/".$this->anoReferencia),1,0,"C");
		$this->cell(17,4,utf8_decode("03/".$this->anoReferencia),1,0,"C");
		$this->cell(17,4,utf8_decode("04/".$this->anoReferencia),1,0,"C");
		$this->cell(17,4,utf8_decode("05/".$this->anoReferencia),1,0,"C");
		$this->cell(17,4,utf8_decode("06/".$this->anoReferencia),1,0,"C");
		$this->cell(17,4,utf8_decode("07/".$this->anoReferencia),1,0,"C");
		$this->cell(17,4,utf8_decode("08/".$this->anoReferencia),1,0,"C");
		$this->cell(17,4,utf8_decode("09/".$this->anoReferencia),1,0,"C");
		$this->cell(17,4,utf8_decode("10/".$this->anoReferencia),1,0,"C");
		$this->cell(17,4,utf8_decode("11/".$this->anoReferencia),1,0,"C");
		$this->cell(17,4,utf8_decode("12/".$this->anoReferencia),1,0,"C");
		$this->Ln();				
		
		$sqlDemonstrativoDespesas = "
									select 
										    a.plano_contas,
										    a.ds_categoria,
										    ifnull(SUM(CASE
										                WHEN month(b.dt_movimento) = '01' THEN ifnull(b.vlr_movimento, 0)
										            END),
										            0) as 'janeiro',
										    ifnull(SUM(CASE
										                WHEN month(b.dt_movimento) = '02' THEN ifnull(b.vlr_movimento, 0)
										            END),
										            0) as 'fevereiro',
										    ifnull(SUM(CASE
										                WHEN month(b.dt_movimento) = '03' THEN ifnull(b.vlr_movimento, 0)
										            END),
										            0) as 'marco',
										    ifnull(SUM(CASE
										                WHEN month(b.dt_movimento) = '04' THEN ifnull(b.vlr_movimento, 0)
										            END),
										            0) as 'abril',
										    ifnull(SUM(CASE
										                WHEN month(b.dt_movimento) = '05' THEN ifnull(b.vlr_movimento, 0)
										            END),
										            0) as 'maio',
										    ifnull(SUM(CASE
										                WHEN month(b.dt_movimento) = '06' THEN ifnull(b.vlr_movimento, 0)
										            END),
										            0) as 'junho',
										    ifnull(SUM(CASE
										                WHEN month(b.dt_movimento) = '07' THEN ifnull(b.vlr_movimento, 0)
										            END),
										            0) as 'julho',
										    ifnull(SUM(CASE
										                WHEN month(b.dt_movimento) = '08' THEN ifnull(b.vlr_movimento, 0)
										            END),
										            0) as 'agosto',
										    ifnull(SUM(CASE
										                WHEN month(b.dt_movimento) = '09' THEN ifnull(b.vlr_movimento, 0)
										            END),
										            0) as 'setembro',
										    ifnull(SUM(CASE
										                WHEN month(b.dt_movimento) = '10' THEN ifnull(b.vlr_movimento, 0)
										            END),
										            0) as 'outubro',
										    ifnull(SUM(CASE
										                WHEN month(b.dt_movimento) = '11' THEN ifnull(b.vlr_movimento, 0)
										            END),
										            0) as 'novembro',
										    ifnull(SUM(CASE
										                WHEN month(b.dt_movimento) = '12' THEN ifnull(b.vlr_movimento, 0)
										            END),
										            0) as 'dezembro'
									from
										    tbl_categorias a
										        inner join
										    caixa_movimento b ON a.cd_categoria = b.cd_plano_conta 
										    	and year(dt_movimento) = '{$this->anoReferencia}'
										    	and a.tp_categoria = 2
										    	and b.tp_movimento = 'S'									    
										group by a.cd_categoria , ds_categoria
										order by a.plano_contas
										";
		
		$arrDespesasAno = p4a_db::singleton()->fetchAll($sqlDemonstrativoDespesas);
		
		$this->setFont("tahoma","",6);
		
		$vlrTotalReceitas = 0;
		foreach($arrDespesasAno as $nrLinha => $dadosLinhaDespesas )
			{
			$this->cell(15,4,utf8_decode($dadosLinhaDespesas["plano_contas"]),1,0,"L");
			$this->cell(65,4,utf8_decode($dadosLinhaDespesas["ds_categoria"]),1,0,"L");
			$this->cell(17,4,utf8_decode(number_format($dadosLinhaDespesas["janeiro"],2,",",".")),1,0,"R");
			$this->cell(17,4,utf8_decode(number_format($dadosLinhaDespesas["fevereiro"],2,",",".")),1,0,"R");
			$this->cell(17,4,utf8_decode(number_format($dadosLinhaDespesas["marco"],2,",",".")),1,0,"R");
			$this->cell(17,4,utf8_decode(number_format($dadosLinhaDespesas["abril"],2,",",".")),1,0,"R");
			$this->cell(17,4,utf8_decode(number_format($dadosLinhaDespesas["maio"],2,",",".")),1,0,"R");
			$this->cell(17,4,utf8_decode(number_format($dadosLinhaDespesas["junho"],2,",",".")),1,0,"R");
			$this->cell(17,4,utf8_decode(number_format($dadosLinhaDespesas["julho"],2,",",".")),1,0,"R");
			$this->cell(17,4,utf8_decode(number_format($dadosLinhaDespesas["agosto"],2,",",".")),1,0,"R");
			$this->cell(17,4,utf8_decode(number_format($dadosLinhaDespesas["setembro"],2,",",".")),1,0,"R");
			$this->cell(17,4,utf8_decode(number_format($dadosLinhaDespesas["outubro"],2,",",".")),1,0,"R");
			$this->cell(17,4,utf8_decode(number_format($dadosLinhaDespesas["novembro"],2,",",".")),1,0,"R");
			$this->cell(17,4,utf8_decode(number_format($dadosLinhaDespesas["dezembro"],2,",",".")),1,0,"R");
			$this->Ln();
			
			$vlrTotalDespesasJaneiro += $dadosLinhaDespesas["janeiro"];
			$vlrTotalDespesasFevereiro += $dadosLinhaDespesas["fevereiro"];
			$vlrTotalDespesasMarco += $dadosLinhaDespesas["marco"];
			$vlrTotalDespesasAbril += $dadosLinhaDespesas["abril"];
			$vlrTotalDespesasMaio += $dadosLinhaDespesas["maio"];
			$vlrTotalDespesasJunho += $dadosLinhaDespesas["junho"];
			$vlrTotalDespesasJulho += $dadosLinhaDespesas["julho"];
			$vlrTotalDespesasAgosto += $dadosLinhaDespesas["agosto"];
			$vlrTotalDespesasSetembro+= $dadosLinhaDespesas["setembro"];
			$vlrTotalDespesasOutubro += $dadosLinhaDespesas["outubro"];
			$vlrTotalDespesasNovembro += $dadosLinhaDespesas["novembro"];
			$vlrTotalDespesasDezembro += $dadosLinhaDespesas["dezembro"];
			}

		$this->cell(80,5,utf8_decode("Total Despesas em (R$)"),1,0,"C");
		$this->cell(17,5,utf8_decode(number_format($vlrTotalDespesasJaneiro,2,",",".")),1,0,"R");
		$this->cell(17,5,utf8_decode(number_format($vlrTotalDespesasFevereiro,2,",",".")),1,0,"R");
		$this->cell(17,5,utf8_decode(number_format($vlrTotalDespesasMarco,2,",",".")),1,0,"R");
		$this->cell(17,5,utf8_decode(number_format($vlrTotalDespesasAbril,2,",",".")),1,0,"R");
		$this->cell(17,5,utf8_decode(number_format($vlrTotalDespesasMaio,2,",",".")),1,0,"R");
		$this->cell(17,5,utf8_decode(number_format($vlrTotalDespesasJunho,2,",",".")),1,0,"R");
		$this->cell(17,5,utf8_decode(number_format($vlrTotalDespesasJulho,2,",",".")),1,0,"R");
		$this->cell(17,5,utf8_decode(number_format($vlrTotalDespesasAgosto,2,",",".")),1,0,"R");
		$this->cell(17,5,utf8_decode(number_format($vlrTotalDespesasSetembro,2,",",".")),1,0,"R");
		$this->cell(17,5,utf8_decode(number_format($vlrTotalDespesasOutubro,2,",",".")),1,0,"R");
		$this->cell(17,5,utf8_decode(number_format($vlrTotalDespesasNovembro,2,",",".")),1,0,"R");
		$this->cell(17,5,utf8_decode(number_format($vlrTotalDespesasDezembro,2,",",".")),1,0,"R");
		}
	
	function Output()
		{
		$this->montaRelatorio();
		return parent::Output("rptRelatorioDemonstrativoReceitasDespesas.pdf","S");
		}		
	}	