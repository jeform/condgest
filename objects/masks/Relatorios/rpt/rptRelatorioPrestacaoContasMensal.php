<?php
class rptRelatorioPrestacaoContasMensal extends satecmax_pdf
	{
		
	private $mesReferencia = "";
	private $anoReferencia = "";
	private $mesAnoReferencia = "";
	private $imprimirComposicaoBoletos = "";	
	
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
		$this->cell(200,5,utf8_decode("RELATORIO DE PRESTAÇÃO DE CONTAS MENSAL - ".$this->mesReferencia."/".$this->anoReferencia),1,1,"C");
		}
		
	function setParametros($mesAnoReferencia,$imprimirComposicaoBoletos)
		{
		$strMesAnoReferencia = $mesAnoReferencia;
		$this->imprimirComposicaoBoletos = $imprimirComposicaoBoletos;
		
		$this->mesAnoReferencia = $strMesAnoReferencia;
		$arrMesAnoReferencia = explode("/", $strMesAnoReferencia);
		
		$this->mesReferencia = $arrMesAnoReferencia[0];
		$this->anoReferencia = $arrMesAnoReferencia[1];
		}
		
	function Footer()
		{
		$this->setFont("tahoma","",8);
		$this->SetY(280);
		$this->cell(200,10,utf8_decode("Data extração: ".date("d/m/Y H:i:s")),0,0,"R");
		$this->Ln();
		$this->cell(210,10,utf8_decode("Sistema de Gestão Condominial - Condgest"),0,1,"C");
		}
		
	function montaRelatorio()
		{
		$this->AddPage();
		$this->ln(2);
		$this->setFont("tahoma","",8);
		$this->cell(150,7,utf8_decode("RECEITAS/HISTORICO"),1,0,"C");
		$this->cell(50,7,utf8_decode("VALORES EM R$"),1,1,"C");
		
		// pegar as receitas do mes...
		$sqlReceitasMes = "
							select
								c.cd_caixa,
								c.ds_caixa,
								b.plano_contas,
								b.ds_categoria,
								sum(a.vlr_movimento) soma_vlr_movimento
							from
								caixa_movimento a,
								tbl_categorias b,
								caixa c
							where
									a.cd_plano_conta = b.cd_categoria
								and a.cd_caixa = c.cd_caixa
								and month(a.dt_movimento) = '{$this->mesReferencia}'
								and year(a.dt_movimento) = '{$this->anoReferencia}'
								and a.tp_movimento = 'E'
							group by 
								1,2,3,4
							order by 
								1,2,3
							";
		
		$arrReceitasMes = p4a_db::singleton()->fetchAll($sqlReceitasMes);
		
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
								c.cd_caixa,
								c.ds_caixa,
								b.plano_contas,
								b.ds_categoria,
								sum(a.vlr_movimento) soma_vlr_movimento
							from
								caixa_movimento a,
								tbl_categorias b,
								caixa c
							where
									a.cd_plano_conta = b.cd_categoria
								and a.cd_caixa = c.cd_caixa
								and month(a.dt_movimento) = '{$this->mesReferencia}'
								and year(a.dt_movimento) = '{$this->anoReferencia}'
								and a.tp_movimento = 'S'
							group by 
								1,2,3,4
							order by 
								1,2,3
							";
		
		$arrDespesasMes = p4a_db::singleton()->fetchAll($sqlDespesasMes);
		
		$this->setFont("tahoma","",6);
		
		$cd_caixa = 0;
		$vlrTotalGeralDespesas = 0;
		foreach($arrDespesasMes as $nrLinhaDespesa => $dadosLinhaDespesa ){
			if ( $cd_caixa <> $dadosLinhaDespesa["cd_caixa"] ){
				$cd_caixa = $dadosLinhaDespesa["cd_caixa"];
				$this->cell(150,3,utf8_decode($dadosLinhaDespesa["ds_caixa"]),"LR",0,"L");
				$this->cell(50,3,0,"LR",1,"L");
			}
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
		
		$arrCaixas = P4A_DB::singleton()->fetchAll("select cd_caixa, ds_caixa from caixa where st_caixa = 1");
		
		$dtSaldoAnterior = date("Y-m-d",strtotime("-1 day",strtotime($this->anoReferencia."-".$this->mesReferencia."-01")));
		
		// recuperar saldo do mês anterior ao selecionado
		$saldoAnteriorTotal = 0;
		
		$this->setFont("tahoma","",6);
		
		foreach($arrCaixas as $dadosCaixas){
			$objCaixa = new movimentoCaixa($dadosCaixas["cd_caixa"]);
			$saldoAnteriorCaixa = $objCaixa->getSaldoCaixa($dtSaldoAnterior);
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
		
		$fundoReserva2014 = condgest::singleton()->getParametro("FUNDO_RESERVA_AGO_2014");
		$fundoReserva2015 = condgest::singleton()->getParametro("FUNDO_RESERVA_AGO_2015");
		$fundoReserva2016 = condgest::singleton()->getParametro("FUNDO_RESERVA_AGO_2016");
		
		if($this->anoReferencia <= 2016)
			{
			if ($this->mesReferencia < 02)
				{
				$this->cell(150,7,utf8_decode("Fundo de Reserva (D)"),1,0,"C");
				$this->cell(50,7,number_format($fundoReserva2014,2,",","."),1,1,"R");
					
				$this->cell(150,7,utf8_decode("Saldo Final (A + B - C - D)"),1,0,"C");
				$this->cell(50,7,number_format(($saldoAnteriorTotal+$vlrTotalGeralReceitas)-$vlrTotalGeralDespesas - $fundoReserva2014,2,",","."),1,1,"R");
				}
			else
				{
				$this->cell(150,7,utf8_decode("Fundo de Reserva (D)"),1,0,"C");
				$this->cell(50,7,number_format($fundoReserva2015,2,",","."),1,1,"R");
					
				$this->cell(150,7,utf8_decode("Saldo Final (A + B - C - D)"),1,0,"C");
				$this->cell(50,7,number_format(($saldoAnteriorTotal+$vlrTotalGeralReceitas)-$vlrTotalGeralDespesas - $fundoReserva2015,2,",","."),1,1,"R");
				}
			}
		else
			{
			if ($this->mesReferencia < 02)
				{
				$this->cell(150,7,utf8_decode("Fundo de Reserva (D)"),1,0,"C");
				$this->cell(50,7,number_format($fundoReserva2015,2,",","."),1,1,"R");
					
				$this->cell(150,7,utf8_decode("Saldo Final (A + B - C - D)"),1,0,"C");
				$this->cell(50,7,number_format(($saldoAnteriorTotal+$vlrTotalGeralReceitas)-$vlrTotalGeralDespesas - $fundoReserva2015,2,",","."),1,1,"R");
				}
			else
				{
				$this->cell(150,7,utf8_decode("Fundo de Reserva (D)"),1,0,"C");
				$this->cell(50,7,number_format($fundoReserva2016,2,",","."),1,1,"R");
					
				$this->cell(150,7,utf8_decode("Saldo Final (A + B - C - D)"),1,0,"C");
				$this->cell(50,7,number_format(($saldoAnteriorTotal+$vlrTotalGeralReceitas)-$vlrTotalGeralDespesas - $fundoReserva2016,2,",","."),1,1,"R");
				}
			}
		
		if($this->imprimirComposicaoBoletos == "0"){
			$this->cell(150,5,utf8_decode("Saldo atual"),1,0,"C");
			$this->cell(50,5,utf8_decode("Valores em R$"),1,1,"C");
			// recuperar saldo do mês selecionado
			$saldoAtualTotal = 0;
			$data = recuperarUltimoDiaMes($this->mesAnoReferencia);
			$this->setFont("tahoma","",6);
			foreach($arrCaixas as $dadosCaixas){
				$objCaixa = new movimentoCaixa($dadosCaixas["cd_caixa"]);
				$saldoAtualCaixa = $objCaixa->getSaldoCaixa($data);
				$this->cell(150,3,utf8_decode($dadosCaixas["ds_caixa"]),"LR",0,"L");
				$this->cell(50,3,number_format($saldoAtualCaixa,2,",","."),"LR",1,"R");
				$saldoAtualTotal += $saldoAtualCaixa;
			}
			$this->setFont("tahoma","",8);
			$this->cell(150,7,utf8_decode("Saldo Atual Total"),1,0,"C");
			$this->cell(50,7,number_format($saldoAtualTotal,2,",","."),1,1,"R");
		}
		
		if($this->imprimirComposicaoBoletos == "1"){
			$aviso_linha_1 = condgest::singleton()->getParametro("AVISO_DFIN_LINHA_1");
			$aviso_linha_2 = condgest::singleton()->getParametro("AVISO_DFIN_LINHA_2");
			$this->setFont("tahoma","",8);
			$this->cell(200,4,utf8_decode("AVISOS"),1,1,"C");		
			$this->Multicell(200,4,utf8_decode($aviso_linha_1),1,1 ,"J"); 
			$this->Multicell(200,4,utf8_decode($aviso_linha_2),1,1 ,"J");
		}
	}
		
	function montarComposicaoBoleto($unidade)
		{
		// recuperar a composição do boleto por unidade
		$data = $this->anoReferencia."-".$this->mesReferencia."-01";
			
		$sqlComposicao = "
							select 
							    c.cd_unidade,
							    d.ds_item_cobranca,
							    c.ds_item_boleto,
							    c.vlr_item_boleto
							from
							    tbl_boleto_mes a
							        inner join 
							    tbl_boleto_mes_unidade b on a.cd_boleto_mes = b.cd_boleto_mes
							        inner join
							    tbl_boleto_mes_unidade_itens_cobranca c on b.cd_boleto_mes_unidade = c.cd_boleto_mes_unidade
							        inner join
							    tbl_itens_cobranca_boleto d on c.cd_item_cobranca = d.cd_item_cobranca
							where
								a.mes_ano_referencia = DATE_FORMAT( ADDDATE( '{$data}' , INTERVAL + 1 MONTH ) , '%m/%Y' )
							        and b.cd_unidade = '{$unidade}'
							union 
                            select 
                                    '' cd_unidade,
                                    '' ds_item_cobranca,
                                    'TOTAL' as total,
                                    sum(c.vlr_item_boleto)
                                    
                            from
							    tbl_boleto_mes a
							        inner join 
							    tbl_boleto_mes_unidade b on a.cd_boleto_mes = b.cd_boleto_mes
							        inner join
							    tbl_boleto_mes_unidade_itens_cobranca c on b.cd_boleto_mes_unidade = c.cd_boleto_mes_unidade
							        inner join
							    tbl_itens_cobranca_boleto d on c.cd_item_cobranca = d.cd_item_cobranca
                            where
								a.mes_ano_referencia = DATE_FORMAT( ADDDATE( '{$data}' , INTERVAL + 1 MONTH ) , '%m/%Y' )
							        and b.cd_unidade = '{$unidade}'
						";
		
		$arrComposicao = p4a_db::singleton()->fetchAll($sqlComposicao);
		
		$this->ln(2);
		$this->setFont("tahoma","",8);
		
		$this->cell(180,7,utf8_decode("Composição da Taxa Associativa da unidade ".$unidade),1,0,"C");
		$this->ln();
		$this->cell(20,7,utf8_decode("Unidade"),1,0,"C");
		$this->cell(70,7,utf8_decode("Descrição"),1,0,"C");
		$this->cell(70,7,utf8_decode("Descrição Complementar"),1,0,"C");
		$this->cell(20,7,utf8_decode("Valor (R$)"),1,0,"C");
		$this->ln();
	
		foreach($arrComposicao as $nrLinhaComposicao => $dadosLinhaComposicao)
			{	
			$this->cell(20,4,utf8_decode($dadosLinhaComposicao["cd_unidade"]),1,0,"C");
			$this->cell(70,4,utf8_decode($dadosLinhaComposicao["ds_item_cobranca"]),1,0,"L");
			$this->cell(70,4,utf8_decode($dadosLinhaComposicao["ds_item_boleto"]),1,0,"L");
			$this->cell(20,4,utf8_decode(number_format($dadosLinhaComposicao["vlr_item_boleto"],2,",",".")),1,0,"R");
			$this->ln();
			}
		}
		
	function montarProcessamentoAgua($unidade)
		{	
		$arrUnidades = P4A_DB::singleton()->fetchAll("select cd_unidade from tbl_leitura_agua_mes_individual where cd_unidade = '{$unidade}'");
			
		foreach($arrUnidades as $dadosUnidades)
			{
			$teste = $dadosUnidades["cd_unidade"];
			}
		
		if(!is_null($teste))
			{
			$data = $this->anoReferencia."-".$this->mesReferencia."-01";
				
			$sql = "
					select
						b.cd_unidade,
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
						tbl_unidades c
					where
						a.cd_leitura_agua_mes = b.cd_leitura_agua_mes
						and b.cd_unidade = c.cd_unidade
						and a.mes_ano_referencia = DATE_FORMAT( ADDDATE( '{$data}' , INTERVAL - 1 MONTH ) , '%m/%Y' )
						and b.cd_unidade = '{$unidade}'
						";
				
			$arrDados = P4A_DB::singleton()->fetchAll($sql);
			
			$this->cell(180,7,utf8_decode("Dados do consumo de água da Unidade ".$unidade),1,0,"C");
			$this->ln();
			
			$this->setFont("tahoma","",8);
				
			$this->cell(20,7,utf8_decode("Unidade"),1,0,"C");
			$this->cell(15,7,utf8_decode("Inicial"),1,0,"C");
			$this->cell(15,7,utf8_decode("Final"),1,0,"C");
			$this->cell(15,7,utf8_decode("Inicial"),1,0,"C");
			$this->cell(15,7,utf8_decode("Final"),1,0,"C");
			$this->cell(15,7,utf8_decode("Consumo"),1,0,"C");
			$this->cell(25,7,utf8_decode("Percentual (%)"),1,0,"C");
			$this->cell(20,7,utf8_decode("Valor (R$)"),1,0,"C");
			$this->cell(20,7,utf8_decode("Taxa (R$)"),1,0,"C");
			$this->cell(20,7,utf8_decode("Total (R$)"),1,1,"C");
			
			foreach($arrDados as $linha => $dadosLinha )
				{
				$this->cell(20,4,utf8_decode($dadosLinha["cd_unidade"]),1,0,"C");
				$this->cell(15,4,utf8_decode($dadosLinha["leitura_inicial_1"]),1,0,"C");
				$this->cell(15,4,utf8_decode($dadosLinha["leitura_final_1"]),1,0,"C");
				$this->cell(15,4,utf8_decode($dadosLinha["leitura_inicial_2"]),1,0,"C");
				$this->cell(15,4,utf8_decode($dadosLinha["leitura_final_2"]),1,0,"C");
				$this->cell(15,4,utf8_decode($dadosLinha["total_consumido"]),1,0,"C");
				$this->cell(25,4,utf8_decode(number_format($dadosLinha["percent_rateio"],6,",",".")),1,0,"C");
				$this->cell(20,4,utf8_decode(number_format($dadosLinha["vlr_total"],2,",",".")),1,0,"C");
				$this->cell(20,4,utf8_decode(number_format($dadosLinha["taxa_consumo"],2,",",".")),1,0,"C");
				$this->cell(20,4,utf8_decode(number_format($dadosLinha["total"],2,",",".")),1,0,"C");
				}
			}
		}
			
	function Output()
		{
		if($this->imprimirComposicaoBoletos== "1"){
			$arrUnidades = P4A_DB::singleton()->fetchAll("select cd_unidade from tbl_unidades where st_unidade = 1 and cd_unidade not in (89,90)");
				
			foreach($arrUnidades as $dadosUnidades)
			{
				$this->montaRelatorio();
				$this->AddPage();
				$this->Ln(5);
				$this->montarComposicaoBoleto($dadosUnidades["cd_unidade"]);
				$this->Ln(5);
				$this->montarProcessamentoAgua($dadosUnidades["cd_unidade"]);
			}
		}
		else{
			$this->montaRelatorio();
		}
		
		return parent::Output("rptRelatorioPrestacaoContaMensal.pdf","S");
		}
		
	}