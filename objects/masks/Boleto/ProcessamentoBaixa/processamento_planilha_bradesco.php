<?php
class processamento_planilha_bradesco extends satecmax_mask
	{
	function __construct()
		{
		parent::__construct();
		
		$this->setTitle(__("Processamento Planilha de baixa Bradesco"));
		
		
		$this->build("p4a_frame","frm");
		
		$this->build("P4a_field","fldArquivoXLS")
			->setLabel(__("Arquivo Excel Baixa"))
			->setWidth(280)
			->setType("file");
		
		
		$this->frm->anchor($this->fldArquivoXLS);
		
		
		$this->build("p4a_button","btnProcessar")
			->setLabel(__("Processar"))
			->implement("onClick",$this,"processarArquivo");
		
		$this->frm->anchor($this->btnProcessar);
		
		$this->build("p4a_quit_toolbar","toolbar");
		
		$this->build("p4a_fieldset","fsetDadosPlanilha")
			->setlabel(__("Dados Planilha"));
		
		$this->frm->anchor($this->fsetDadosPlanilha);
		
		$this->display("main",$this->frm);
		$this->display("menu",condgest::singleton()->menu);
		$this->display("top",$this->toolbar);
		}
		
		
	function processarArquivo()
		{
			
		$arrDadosArquivo = $this->fldArquivoXLS->getNewValue();
			
		//$this->info(var_export($arrDadosArquivo,true));
		
		$arrDadosArquivoExcelTratado = P4A_File2array($arrDadosArquivo);
		
		if ( !$arrDadosArquivo == null and $arrDadosArquivoExcelTratado["type"] == "application/vnd.ms-excel" )
			{
			//$this->info(var_export($arrDadosArquivoExcelTratado,true));
			
			//$this->info(P4A_UPLOADS_DIR);
			
			$path_arquivo = P4A_UPLOADS_DIR.$arrDadosArquivoExcelTratado["path"];
			
			//$this->info($path_arquivo);
			
			$inputFileType = 'Excel5';
			$inputFileName = $path_arquivo;
			
			$objReader = PHPExcel_IOFactory::createReader($inputFileType);
			$objPHPExcel = $objReader->load($inputFileName);
			
			// pegar a quantidade de worksheets
			$sheetCount = $objPHPExcel->getSheetCount();

			// carregar as worksheets
			$sheetNames = $objPHPExcel->getSheetNames();
			
			
			// inicio testes samuel...
			
			$arrDadosPlanilhaCarregada = array();
			
			$arrDadosPlanilhaBaixaBoleto = array();
			
			foreach($sheetNames as $sheetIndex => $sheetName) 
				{
			
				$objPHPExcel->setActiveSheetIndex($sheetIndex);
			
				$sheetData = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);
			
				$inicio = false;
				foreach ($sheetData as $contador => $cols)
					{
					if ($inicio == true and ( $cols["A"] == "" or !intval($cols["A"]) > 0 ) )
						{
						// linha em branco depois das informações...
						break;
						}
			
					if ( $contador > 4 )
						{
						$inicio = true;
						
						$seuNumero = $cols["C"];
						
						$nrBoletoSistema = "";
						$vlrBoletoSistema = "";
						$statusBoletoSistema = "";
						$dataVencSistema = "";
						
						if ( strlen($seuNumero) == 8 )
							{
							$nrUnidade = substr($seuNumero, 0,2);
							
							$nrMes = substr($seuNumero, 2,2);
							
							$nrAno = substr($seuNumero, 4,4);
							
							$nrMesAno = $nrMes.$nrAno;
							
							//$this->info($nrUnidade."-".$nrMes."-".$nrAno);
							$sqlBuscaDadosBoleto = " 
													select 
															b.cd_boleto_mes_unidade, 
															IFNULL(SUM(vlr_item_boleto),0) as vlr_boleto,
															b.dt_vencimento,					
															(concat_ws(' / ',CASE st_emitido WHEN 0 THEN 'NÃO EMITIDO' WHEN 1 THEN 'EMITIDO' END, CASE st_baixado WHEN 0 THEN 'EM ABERTO' WHEN 1 THEN 'BAIXADO' END)) as status,
															st_emitido,
															st_baixado
													from
															tbl_boleto_mes a
														inner join tbl_boleto_mes_unidade b on a.cd_boleto_mes = b.cd_boleto_mes
														inner join tbl_boleto_mes_unidade_itens_cobranca c on b.cd_boleto_mes_unidade = c.cd_boleto_mes_unidade
													where
															replace(a.mes_ano_referencia,'/','') = '{$nrMesAno}'
														and b.cd_unidade = '{$nrUnidade}'									
													";
							$arrDadosBoletoSistema = P4A_DB::singleton()->fetchRow($sqlBuscaDadosBoleto);
							
							if (count($arrDadosBoletoSistema) > 1 )
								{
								$nrBoletoSistema = $arrDadosBoletoSistema["cd_boleto_mes_unidade"];
								$vlrBoletoSistema = $arrDadosBoletoSistema["vlr_boleto"];
								$statusBoletoSistema = $arrDadosBoletoSistema["status"];
								$dataVencSistema = $arrDadosBoletoSistema["dt_vencimento"];
								
								// verificar se o boleto ja foi baixado, se não, carregar no array $arrDadosPlanilhaBaixa
								if ( $arrDadosBoletoSistema["st_emitido"] == 1 and $arrDadosBoletoSistema["st_baixado"] == 0
									and $cols["K"] <> "" and str_replace(",", ".", $cols["P"]) > 0
									)
									{	
									$arrDadosPlanilhaBaixaBoleto[] = array(
																			"sheetname"=>$sheetName,
																			"nosso_numero"=>$cols["A"],
																			"seu_numero"=>$cols["C"],
																			"nome_pagador"=>$cols["F"],
																			"vencimento"=>$cols["I"],
																			"data_pagto"=>$cols["K"],
																			"valor_titulo"=>$cols["L"],
																			"oscilacao"=>$cols["N"],
																			"valor_cobrado"=>$cols["P"],
																			"CL"=>$cols["R"],
																			"boleto_sistema"=>$nrBoletoSistema,
																			"valor_boleto_sistema"=>$vlrBoletoSistema,
																			"status_boleto_sistema"=>$statusBoletoSistema,
																			"data_venc_sistema"=>$dataVencSistema,
																			"status_baixa"=>"Este boleto Será baixado com o valor: ".$cols["P"]." na data ".$cols["K"]."."
													
																		);
										
									}
								}
							}
						
						$arrDadosPlanilhaCarregada[] = array(
															"sheetname"=>$sheetName,
															"nosso_numero"=>$cols["A"],
															"seu_numero"=>$cols["C"],
															"nome_pagador"=>$cols["F"],
															"vencimento"=>$cols["I"],
															"data_pagto"=>$cols["K"],
															"valor_titulo"=>$cols["L"],
															"oscilacao"=>$cols["N"],
															"valor_cobrado"=>$cols["P"],
															"CL"=>$cols["R"],
															"boleto_sistema"=>$nrBoletoSistema,
															"valor_boleto_sistema"=>$vlrBoletoSistema,
															"status_boleto_sistema"=>$statusBoletoSistema,
															"data_venc_sistema"=>$dataVencSistema
															
														);	
						}
					}
				$inicio = false;
				}
				
			if ( count($arrDadosPlanilhaCarregada) > 1 )
				{
				$this->build("p4a_array_source","srcPlanilhaCarregada")
					->Load($arrDadosPlanilhaCarregada)
					->setPk("nosso_numero");
				
				$this->build("satecmax_table","tblPlanilhaCarregada")
					->setLabel(__("Boletos Carregados pela Planilha"))
					->setSource($this->srcPlanilhaCarregada);

				$this->fsetDadosPlanilha->clean();
				
				$this->fsetDadosPlanilha->anchor($this->tblPlanilhaCarregada);
				
				if ( count($arrDadosPlanilhaBaixaBoleto) > 0 ) // se tem boleto a baixar, criar um botao para processar...
					{
					$this->build("p4a_array_source","srcPlanilhaBaixaBoleto")
						->Load($arrDadosPlanilhaBaixaBoleto)
						->setPk("nosso_numero")
						;
						
					$this->build("satecmax_table","tblPlanilhaBaixaBoleto")
						->setLabel(_("Boletos a serem baixados automaticamente"))
						->setSource($this->srcPlanilhaBaixaBoleto)
						;
					
					$this->fsetDadosPlanilha->anchor($this->tblPlanilhaBaixaBoleto);
										
					$this->build("p4a_button","btnBaixarBoletos")
						->setLabel(__("Baixar Boletos Selecionados Automaticamente"))
						->implement("onClick",$this,"baixarBoletos");
					
					$this->fsetDadosPlanilha->anchorCenter($this->btnBaixarBoletos);
					}
				else
					{
					$this->info(__("Sem boletos para baixar desta planilha"));
					}
				
				}
			
			}
		else
			{
			$this->error(__("Arquivo carregado é invalido!"));
			}
		
		//{tmp_2.xls,/tmp/tmp_2.xls,85504,application/vnd.ms-excel,,,movimentacao_17012015175519_3761987017
		}
		
	function baixarBoletos()
		{
		// pegar os boletos a baixar e realizar a baixa...
		
		$arrDadosBoletosBaixar = $this->srcPlanilhaBaixaBoleto->getAll();
		
		try
			{
		
			P4A_DB::singleton()->beginTransaction();
			
			$sqlUpdateBaixaBoleto = "update 
										tbl_boleto_mes_unidade
									set 
										dt_pagamento = ?, 
										dt_liquidacao = ?, 
										vlr_juros = ?, 
										vlr_baixa_boleto = ?, 
										tp_documento_referencia = ?, 
										cd_documento_referencia = ?,
										dt_imp_planilha = ?,
										nr_movimento = ?, 
										st_baixado = 1 
									where 
										cd_boleto_mes_unidade = ? ";
			
			foreach($arrDadosBoletosBaixar as $nrLinha => $dadosLinhaBaixa)
				{
				$dtVencimento = formatarDataBanco($dadosLinhaBaixa["vencimento"]);
					
				$dtPagamento = formatarDataBanco($dadosLinhaBaixa["data_pagto"]);
				
				$dtLiquidacao = retornaDataLiquidacao($dadosLinhaBaixa["data_pagto"]);

				$vlrBaixaBoleto = formataValoresBanco($dadosLinhaBaixa["valor_cobrado"]);
				
				$vlrBoletoSistema = $dadosLinhaBaixa["valor_boleto_sistema"];
				
				$vlrJuros = ( ($vlrBaixaBoleto>$vlrBoletoSistema) ? ($vlrBaixaBoleto-$vlrBoletoSistema) : (0));
				
				$tpDocumentoReferencia = "BOL";
				
				$cdDocumentoReferencia = $dadosLinhaBaixa["nosso_numero"];
				
				$cdBoletoMesUnidade = $dadosLinhaBaixa["boleto_sistema"];
				
				// realizar a movimentação de recebimento no caixa...
				
				$caixaBanco = condgest::singleton()->getParametro("CAIXA_BANCO");
				
				$objCaixaMovimento = new movimentoCaixa($caixaBanco);
				
				$tipoDocumento = "BOL";
				
				$sqlBuscaDadosCaixa = "
											select 
												a.cd_unidade,
												b.mes_ano_referencia,
												c.cd_pessoa
											from 
												tbl_boleto_mes_unidade a,
												tbl_boleto_mes b,
											    tbl_unidades c
											where
												a.cd_boleto_mes = b.cd_boleto_mes
											    and a.cd_unidade = c.cd_unidade
												and a.cd_boleto_mes_unidade = ?
									";
				
				$arrBuscaDadosUnidade = P4A_DB::singleton()->fetchRow($sqlBuscaDadosCaixa,array($cdBoletoMesUnidade));
				
				$cdUnidade = $arrBuscaDadosUnidade["cd_unidade"];
				
				$mesAnoReferencia = $arrBuscaDadosUnidade["mes_ano_referencia"];
				
				$cdCategoria = condgest::singleton()->getParametro("PROCESSAMENTO_BOLETO_CD_CATEGORIA_BAIXA_BOLETO");
				
				$cdPessoa = $arrBuscaDadosUnidade["cd_pessoa"];
				
				$nrNumeroMovimentoCaixa = $objCaixaMovimento->newMovimentoEntrada(
																				formatarDataBanco1($dtLiquidacao),
																				$dadosLinhaBaixa["valor_cobrado"],
																				$cdPessoa,
																				$cdUnidade." - ".$mesAnoReferencia." - ".$cdDocumentoReferencia,
																				$tipoDocumento,
																				"BAIXA BOLETO - UNIDADE " .$cdUnidade. " - MES/ANO REF. " .$mesAnoReferencia." - ".$cdDocumentoReferencia,
																				$cdCategoria,
																				6,
																				"");
				
				//Rotina de verificação do valor pago pelo condômino
				$percentualInadimplencia = condgest::singleton()->getParametro("PERC_INADIMPLENCIA");
				$diaUtilVencimento = alteraDiaUtilVencimento($dadosLinhaBaixa["vencimento"]);
				
				$intervaloDatas = intervaloData($diaUtilVencimento,$dadosLinhaBaixa["data_pagto"]);
				
				if($intervaloDatas > 0)
					{
					$vlrMulta = $vlrBoletoSistema * 0.02;
					$vlrJuros = ((($vlrBoletoSistema * 0.01) / 30) * (intervaloData($dadosLinhaBaixa["vencimento"],$dadosLinhaBaixa["data_pagto"])));
					$vlrCorrigido = number_format($vlrBoletoSistema + $vlrMulta + $vlrJuros,2);
					
					
					}
				else
					{
					$vlrCorrigido = number_format($vlrBoletoSistema - ($vlrBoletoSistema * $percentualInadimplencia),2);
					}

				// inserção da diferença do boleto baixado
				$vlrDiferencaBoleto = $vlrCorrigido - $vlrBaixaBoleto;
				$diferencaAceitaBaixa = condgest::singleton()->getParametro("DIFERENCA_ACEITAVEL_BOLETO");
					
				if($vlrDiferencaBoleto != 0 && abs($vlrDiferencaBoleto) > $diferencaAceitaBaixa)
					{
					$sqlDiferencaBaixa = "update
												tbl_boleto_mes_unidade
										  set
												vlr_diferenca = ?,
												st_diferenca = ?
										  where
												cd_boleto_mes_unidade = ?";
							
					P4A_DB::singleton()->query($sqlDiferencaBaixa,array($vlrDiferencaBoleto,'1',$cdBoletoMesUnidade));
					}
					
				P4A_DB::singleton()->query($sqlUpdateBaixaBoleto,array($dtPagamento,$dtLiquidacao,$vlrJuros,$vlrBaixaBoleto,$tpDocumentoReferencia,$cdDocumentoReferencia, date('Y-m-d'), $nrNumeroMovimentoCaixa, $cdBoletoMesUnidade));
				
				}
			P4A_DB::singleton()->commit();
			
			$this->info(__("Baixa de boletos efetuado com sucesso!"));
			
			$this->__construct();
			}
		catch (Exception $e)
			{
			P4A_DB::singleton()->rollback();
			$this->error(__("Erro na atualização do Registro! ".$e->getMessage()));
			}		
		}
	}