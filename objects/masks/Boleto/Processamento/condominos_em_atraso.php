<?php
class condominos_em_atraso extends satecmax_mask
	{
	function __construct()
		{
		parent::__construct();

		$this->setTitle(__("Unidades Inadimplentes"));

		$this->build("satecmax_db_source","src_unidades_inadimplentes")
			->setTable("tbl_boleto_mes_unidade")
			->setPk("cd_boleto_mes_unidade")
			->setWhere("(st_baixado = 0 AND DATE_ADD(tbl_boleto_mes_unidade.dt_vencimento, INTERVAL 1 DAY) < CURDATE())")
			->setGroup("cd_unidade")
			->addOrder("cd_unidade")
			->Load()
			->firstRow();
			
		$this->build("p4a_table", "tbl_unidades_inadimplentes")
			->setSource($this->src_unidades_inadimplentes)
			->setLabel(__("Unidades Inadimplentes"))
			->setWidth(500);
		
		$this->build("satecmax_quit_toolbar","toolbar");
		
		$this->tbl_unidades_inadimplentes->setVisibleCols(array("cd_unidade"));
		
		$this->tbl_unidades_inadimplentes->cols->cd_unidade->setLabel(__("Unidades"))
												->setWidth(30);
		
		$this->tbl_unidades_inadimplentes->addActionCol("detalhes");
		$this->tbl_unidades_inadimplentes->cols->detalhes->setLabel(__("Detalhes"))
														->setWidth(50);
		
		$this->intercept($this->tbl_unidades_inadimplentes->cols->detalhes, "afterClick","abrirDetalhes");
		
		$this->build("satecmax_full_toolbar","toolbar")
			->setMask($this);
		
		$this->toolbar->buttons->new->setInvisible();		

		$this->build("p4a_frame","frm")
			->setWidth(1024);
		
		$this->setSource($this->src_unidades_inadimplentes);
		
		$this->frm->anchorCenter($this->tbl_unidades_inadimplentes);
				
		$this->display("main",$this->frm);
		
		$this->display("menu",p4a::singleton()->menu);
		
		$this->display("top",$this->toolbar);
		}
			
	function imprimirCartaCobranca()
		{
		$objRelatorio = new rptCartaCobranca();
	
		$objRelatorio->setParametros($this->fields->cd_unidade->getNewValue());
	
		P4A_Output_File($objRelatorio->Output(), "rpCartaCobranca.pdf",true);
		}	

	function imprimirCartaCobrancaJudicial()
		{
		$objRelatorio = new rptCartaCobrancaJudicial();
	
		$objRelatorio->setParametros($this->fields->cd_unidade->getNewValue());
	
		P4A_Output_File($objRelatorio->Output(), "rpCartaCobrancaJudicial.pdf",true);
		}

	function abrirDetalhes()
		{				
		condgest::singleton()->openPopup("parcelas_pendentes_condominos");
		}	
			
	function main()
		{
		parent::main();
		}		
	}
	
class parcelas_pendentes_condominos extends satecmax_mask
	{	
	private $arrDados;
		
	function __construct()
		{
		parent::__construct();
	
		$this->build("p4a_frame","frm")
			->setWidth(750);
							
		$this->setSource(condgest::singleton()->masks->condominos_em_atraso->getSource());
		
		
		$this->setTitle(__("Relação de pagamentos em atraso da unidade ").$this->fields->cd_unidade->getValue());
		
		$this->build("satecmax_db_source","src_parcela_atraso")
			->setTable("tbl_boleto_mes_unidade")
			->setWhere("cd_unidade = {$this->fields->cd_unidade->getValue()} and st_baixado = 0 and date_add(dt_vencimento,interval 1 day) < curdate()")
			->setPk("cd_boleto_mes_unidade")
			->setFields(array("*",
								"(SELECT
											ROUND(SUM(vlr_item_boleto),2)
								   FROM
											tbl_boleto_mes_unidade_itens_cobranca
						   		  WHERE
											cd_boleto_mes_unidade = tbl_boleto_mes_unidade.cd_boleto_mes_unidade)"=>"vlr_boleto",
							    "(SELECT
											ROUND(SUM(vlr_item_boleto) * 0.02 ,2)
								    FROM
											tbl_boleto_mes_unidade_itens_cobranca
						   		   WHERE
											cd_boleto_mes_unidade = tbl_boleto_mes_unidade.cd_boleto_mes_unidade)"=>"vlr_multa",
					 			"(SELECT
											ROUND(SUM(vlr_item_boleto) * 0.01 * DATEDIFF(DATE_ADD(CURRENT_DATE(),INTERVAL 0 DAY), tbl_boleto_mes_unidade.dt_vencimento) / 30, 2) 
								    FROM
											tbl_boleto_mes_unidade_itens_cobranca
						   		   WHERE
											cd_boleto_mes_unidade = tbl_boleto_mes_unidade.cd_boleto_mes_unidade)"=>"vlr_mora",
								"(SELECT
											IFNULL(ROUND(SUM(b.vlr_item_boleto) * 
    						  											 ((SELECT 
                    																SUM(y.indice_correcao)
                				   											 FROM
                    																tbl_correcao_monetaria y
                				  											WHERE
                    																DATE_FORMAT(STR_TO_DATE(y.mes_ano_referencia, '%m/%Y'),'%Y/%m') 
                    																	BETWEEN DATE_FORMAT(STR_TO_DATE(a.mes_ano_referencia, '%m/%Y'),'%Y/%m') 
                    																		AND DATE_FORMAT(CURRENT_DATE(), '%Y/%m')) / 100)
                    					   ,2),0)
								    FROM
											tbl_boleto_mes a,
					    					tbl_boleto_mes_unidade_itens_cobranca b
						   		   WHERE
											a.cd_boleto_mes = tbl_boleto_mes_unidade.cd_boleto_mes
												AND b.cd_boleto_mes_unidade = tbl_boleto_mes_unidade.cd_boleto_mes_unidade
												AND b.cd_unidade = tbl_boleto_mes_unidade.cd_unidade)"=>"vlr_atualizacao_monetaria",
					
								"(SELECT
											ROUND(SUM(b.vlr_item_boleto) +
												  SUM(vlr_item_boleto) * 0.02 +
												 (SUM(vlr_item_boleto) * 0.01 * DATEDIFF(DATE_ADD(CURRENT_DATE(),INTERVAL 0 DAY), tbl_boleto_mes_unidade.dt_vencimento) / 30) +					
										     	  IFNULL(SUM(b.vlr_item_boleto) *
    						  											 ((SELECT
                    																SUM(y.indice_correcao)
                				   											 FROM
                    																tbl_correcao_monetaria y
                				  											WHERE
                    																DATE_FORMAT(STR_TO_DATE(y.mes_ano_referencia, '%m/%Y'),'%Y/%m')
                    																	BETWEEN DATE_FORMAT(STR_TO_DATE(a.mes_ano_referencia, '%m/%Y'),'%Y/%m')
                    																		AND DATE_FORMAT(CURRENT_DATE(), '%Y/%m')) / 100),0)
					                    	   ,2)
								    FROM
											tbl_boleto_mes a,
					    					tbl_boleto_mes_unidade_itens_cobranca b
						   		   WHERE
											a.cd_boleto_mes = tbl_boleto_mes_unidade.cd_boleto_mes
												AND b.cd_boleto_mes_unidade = tbl_boleto_mes_unidade.cd_boleto_mes_unidade
												AND b.cd_unidade = tbl_boleto_mes_unidade.cd_unidade)"=>"vlr_total"
								))
					->Load();
		
		$this->build("p4a_table","tbl_parcela_atraso")
			->setSource($this->src_parcela_atraso)
			->setLabel(__("Pagamentos pendentes"))
			->setWidth(700);
		
		$this->tbl_parcela_atraso->setVisibleCols(array("dt_vencimento","vlr_boleto","vlr_multa","vlr_mora","vlr_atualizacao_monetaria","vlr_total"));
		
		$this->tbl_parcela_atraso->cols->dt_vencimento->setLabel(__("Data Vencimento"));
		$this->tbl_parcela_atraso->cols->vlr_boleto->setLabel(__("Valor Histórico"));
		$this->tbl_parcela_atraso->cols->vlr_multa->setLabel(__("Valor da Multa"));
		$this->tbl_parcela_atraso->cols->vlr_atualizacao_monetaria->setLabel(__("Valor Atual. Monetária"));
		$this->tbl_parcela_atraso->cols->vlr_total->setLabel(__("Valor Atualizado"));
		
		$this->build("satecmax_full_toolbar","toolbar")
			->setMask($this);
		
		$this->build("p4a_button","btn_efetuar_acordo")
			->setLabel(__("Efetuar Acordo"),true)
			->setIcon("status/folder-open")
			->implement("onClick",$this,"efetuar_acordo");
		
		$this->build("p4a_frame","frm")
			->setWidth(1024);	
		
		$this->frm->anchorCenter($this->tbl_parcela_atraso);
		$this->frm->anchorCenter($this->btn_efetuar_acordo);
		
		$this->display("main",$this->frm);
		
		$this->display("menu",p4a::singleton()->menu);	
	}

	function efetuar_acordo()
	{
		$this->btn_efetuar_acordo->setVisible(false);	
			
		$this->build("p4a_fieldset","fsetAcordo")
			->setLabel(__("Dados do Acordo"))
			->setWidth(700);
		
		$this->build("p4a_field","fldSaldoDevedor")
			->setLabel(__("Soma do Valor Atualizado"))
			->setWidth(100)
			->setProperty("dir","rtl")
			->Enable(false);
		
		$this->build("p4a_field","fldDescontoVlrTotalAcordo")
			->setLabel(__("Valor de Desconto"))
			->setWidth(100)
			->implement("onBlur",$this,"calculaValorTotal")
			->setProperty("dir","rtl");
			
		$this->build("p4a_field","fldVlrTotalAcordo")
			->setLabel(__("Valor Total"))
			->setWidth(100)
			->setProperty("dir","rtl")
			->Enable(false);
			
		$this->build("p4a_fieldset", "fsetMulta")
			->setLabel(__("Multa"));	
			
		$this->build("p4a_field","fldMulta")
			->setLabel(__("Percentual"))
			->implement("onBlur",$this,"calculaValorTotal");

		$this->build("p4a_fieldset", "fsetHonorarios")
			->setLabel(__("Honorários"));	
			
		$this->build("p4a_field","fldHonorarios")
			->setLabel(__("% sobre o total apurado"))
			->implement("onBlur",$this,"calculaValorTotal")
			->label->setWidth(150);

		$this->fsetMulta->anchor($this->fldMulta);	
		$this->fsetHonorarios->anchor($this->fldHonorarios);		
		
		$this->build("p4a_field","fldDtVencimentoParcela")
			->setLabel(__("1º Vencimento da Parcela"))
			->setWidth(80)
			->setType("date");
		
		$this->build("p4a_field","fldDsAcordo")
			->setLabel(__("Descrição do Acordo"))
			->setWidth(450);	
		
		$this->build("p4a_field","fldQtdeParcelas")
			->setLabel(__("Qtde. Parcelas"))
			->setWidth(60);	
					
		$this->build("p4a_button","btnSalvarAcordo")
			->setLabel(__("Salvar Acordo"),true)
			->setIcon("actions/document-save")
			->implement("onClick",$this,"salvarAcordo");		
			
		$sql = "(SELECT
						b.cd_boleto_mes_unidade as boleto,
						ROUND(SUM(c.vlr_item_boleto),2) as vlr_historico,
						ROUND(SUM(c.vlr_item_boleto) +
						SUM(c.vlr_item_boleto) * 0.02 +
						(SUM(c.vlr_item_boleto) * 0.01 * DATEDIFF(DATE_ADD(CURRENT_DATE(),INTERVAL 0 DAY), b.dt_vencimento) / 30) +					
						 IFNULL(SUM(c.vlr_item_boleto) *
    					  						((SELECT
                    									SUM(y.indice_correcao)
                				   					FROM
                    									tbl_correcao_monetaria y
                				  				   WHERE
                    									DATE_FORMAT(STR_TO_DATE(y.mes_ano_referencia, '%m/%Y'),'%Y/%m')
                    										BETWEEN DATE_FORMAT(STR_TO_DATE(a.mes_ano_referencia, '%m/%Y'),'%Y/%m')
                    											AND DATE_FORMAT(CURRENT_DATE(), '%Y/%m')) / 100),0)	, 2) as vlr_total
								    FROM
											tbl_boleto_mes a,
											tbl_boleto_mes_unidade b,
					    					tbl_boleto_mes_unidade_itens_cobranca c
						   		   WHERE
											a.cd_boleto_mes = b.cd_boleto_mes
												AND b.cd_boleto_mes_unidade = c.cd_boleto_mes_unidade
												AND b.cd_unidade = {$this->fields->cd_unidade->getValue()}
												AND b.st_baixado = 0 and DATE_ADD(b.dt_vencimento,interval 1 day) < curdate()
								   GROUP BY a.mes_ano_referencia)";
		
		$arrDados = P4A_DB::singleton()->fetchAll($sql);
		
		$vlr_total_historico = 0;
		$vlr_total_corrigido = 0;
		
		foreach($arrDados as $linha => $dadosLinha )
			{
			$vlr_total_historico += $dadosLinha["vlr_historico"];	
			$vlr_total_corrigido += $dadosLinha["vlr_total"];			
			}		
		
		$this->fldSaldoDevedor->setNewValue($vlr_total_corrigido);
		$this->fldVlrTotalAcordo->setNewValue($vlr_total_corrigido);
		
		$this->fsetAcordo->anchor($this->fldSaldoDevedor)
						->anchor($this->fldDescontoVlrTotalAcordo)
						->anchor($this->fsetMulta)
						->anchorLeft($this->fsetHonorarios)
						->anchor($this->fldVlrTotalAcordo)
						->anchor($this->fldDsAcordo)
						->anchor($this->fldDtVencimentoParcela)
						->anchor($this->fldQtdeParcelas)
						->anchor($this->btnSalvarAcordo);
		
		$this->frm->anchorCenter($this->fsetAcordo);
	}
		
	function calculaValorTotal()
	{
		$vlrSaldoDevedor = $this->fldSaldoDevedor->getNewValue();
		$vlrDesconto =  $this->fldDescontoVlrTotalAcordo->getNewValue();
		$vlrMulta = $this->fldMulta->getNewValue();
		$vldHonorarios = $this->fldHonorarios->getNewValue();
		
		$this->fldVlrTotalAcordo->setNewValue(round($vlrSaldoDevedor - $vlrDesconto + ($vlrSaldoDevedor * ($vlrMulta / 100) ) + ($vlrSaldoDevedor * ($vldHonorarios / 100)),2));		 
	}	
		
	function salvarAcordo()
	{
		if($this->fldDsAcordo->getNewValue() == "")
		{
			$this->error(__("Preencha a descrição do Acordo!!!"));
			return false;
		}
		if($this->fldDtVencimentoParcela->getNewValue() == "")
		{
			$this->error(__("Preencha a 1ª data de vencimento das parcelas!!!"));
			return false;
		}
		if($this->fldQtdeParcelas->getNewValue() == "")
		{
			$this->error(__("Preencha a quantidade total das parcelas!!!"));
			return false;
		}
		
		try 
		{
			P4A_DB::singleton()->beginTransaction();
			P4A_DB::singleton()->query("INSERT INTO acordos 
											(cd_unidade, 
											dt_acordo, 
											vlr_acordo, 
											qtde_parcelas, 
											dsc_acordo) 
										VALUES 
											('".$this->fields->cd_unidade->getValue()."',
											'".formatarDataBanco(date("d/m/Y"))."',
											'".$this->fldVlrTotalAcordo->getNewValue()."',
											'".$this->fldQtdeParcelas->getNewValue()."',
											'".$this->fldDsAcordo->getNewValue()."'
											)");
			
			
			P4A_DB::singleton()->commit();
		} 
		catch (Exception $e) 
		{
			P4A_DB::singleton()->rollback();
			$this->error(__("Erro: ".$e->getCode()." - ".$e->getMessage()));	
		}
	$this->salvarAcordoDetalhes();	
	}

	function salvarAcordoDetalhes()
	{	
		$qtde_parcelas = $this->fldQtdeParcelas->getNewValue();
		$nr_parcelas = 0;

		$dtPrimeiraParcela = $this->fldDtVencimentoParcela->getNewValue();
		
		try 
		{
			for($a=$nr_parcelas;$a< $qtde_parcelas; $a++)
			{
				$nr_parcelas++;
	
				list($dia,$mes,$ano) = desmontarDatadmY($dtPrimeiraParcela);
				$dtParcelas = acrescentarMesesDatas($dia, $mes, $ano, $nr_parcelas);
				$vlrParcela = round($this->fldVlrTotalAcordo->getNewValue() / $this->fldQtdeParcelas->getNewValue(),2);
				$cdCategoriaConta = "39";
				$cdStatusRecebimento = "1";
				$nrAcordo = P4A_DB::singleton()->fetchOne("SELECT max(cd_acordo) FROM acordos");

				P4A_DB::singleton()->beginTransaction();
				P4A_DB::singleton()->query("INSERT INTO acordos_detalhes
												(cd_acordo,
												dt_vencimento,
												vlr_parcela,
												nr_parcela,
												qtde_parcelas,
												cd_st_recebimento,
												cd_categoria_conta)
												VALUES
												('".$nrAcordo."',
												'".$dtParcelas."',
												'".$vlrParcela."',
												'".$nr_parcelas."',
												'".$qtde_parcelas."',
												'".$cdStatusRecebimento."',
												'".$cdCategoriaConta."'
												)");
				
				P4A_DB::singleton()->commit();
			}
		$this->baixaParcelasAcordo($nrAcordo);
		}
		catch (Exception $e) 
		{
			P4A_DB::singleton()->rollback();
			$this->error(__("Erro: ".$e->getCode()." - ".$e->getMessage()));	
		}	
	}
	
	function baixaParcelasAcordo($nrAcordo)
	{
		$boletoBaixadoAcordo = '3';
		
		
		try 
		{
			P4A_DB::singleton()->beginTransaction();
			
			$sql = ("SELECT 
							a.cd_boleto_mes_unidade as cd_boleto_mes_unidade,
							ROUND(SUM(b.vlr_item_boleto),2) as vlr_historico 
					   FROM 
							tbl_boleto_mes_unidade a, 
							tbl_boleto_mes_unidade_itens_cobranca b
					  WHERE 
					  		a.cd_boleto_mes_unidade = b.cd_boleto_mes_unidade
								and a.cd_unidade = {$this->fields->cd_unidade->getValue()} 
								and a.st_baixado = 0 
								and date_add(a.dt_vencimento, interval 1 day) < curdate()
					  GROUP BY cd_boleto_mes_unidade"
					);

			$arrDados = P4A_DB::singleton()->fetchAll($sql);		
					
			foreach($arrDados as $linha => $dadosLinha )
			{
				$cdBoletoMesUnidade = $dadosLinha["cd_boleto_mes_unidade"];
				$vlrBaixaBoletoAcordo = $dadosLinha["vlr_historico"];
				$dtAtual = formatarDataBanco(date("d/m/Y"));
				
				P4A_DB::singleton()->query("UPDATE 
													tbl_boleto_mes_unidade 
											   SET 
											   		st_baixado = {$boletoBaixadoAcordo}, 
											   		dt_pagamento = '".$dtAtual."',
											   		vlr_baixa_boleto = {$vlrBaixaBoletoAcordo},
											   		nr_acordo = {$nrAcordo} 
											 WHERE 
											 		cd_boleto_mes_unidade = {$cdBoletoMesUnidade}");
				
				$vlrBaixaBoletoAcordo = 0;
				
			}			
			P4A_DB::singleton()->commit();
			$this->info("Acordo nº ".$nrAcordo." gerado com sucesso!");
			$this->showPrevMask();
	
		} 
		catch (Exception $e) 
		{
			P4A_DB::singleton()->rollback();
			$this->error(__("Erro: ".$e->getCode()." - ".$e->getMessage()));
		}
	}
	
	function main()
	{		
		parent::main();
	}	
	}