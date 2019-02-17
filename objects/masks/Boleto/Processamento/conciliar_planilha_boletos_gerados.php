<?php
class conciliar_planilha_boletos_gerados extends satecmax_mask
	{
	function __construct()
		{
		parent::__construct();

		$this->setTitle(__("Conciliação dos boletos gerados"));

		$this->build("p4a_frame","frm");

		$this->build("p4a_db_source","srcMesAnoBoleto")
			->setTable("tbl_boleto_mes")
			->setPk("mes_ano_referencia")
			->Load();
		
		$this->build("p4a_field","fldMesAnoReferenciaBoleto")
			->setLabel(__("Mês/Ano Referência"))
			->allowNull("Selecione ...")
			->setSource($this->srcMesAnoBoleto)
			->setSourceValueField("mes_ano_referencia")
			->setType("select")
			->setWidth(100)
			->label->setWidth(150);

		$this->frm->anchor($this->fldMesAnoReferenciaBoleto);
		
		$this->build("P4a_field","fldArquivoXLS")
			->setLabel(__("Arquivo Excel Baixa"))
			->setType("file")
			->setWidth(300);

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
				
			$arrDadosPlanilhaCarregada = array();
				
			$arrDadosPlanilhaBoletosErrados = array();
				
			foreach($sheetNames as $sheetIndex => $sheetName)
				{			
				$objPHPExcel->setActiveSheetIndex($sheetIndex);
					
				$sheetData = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);
					
				$inicio = false;
				foreach ($sheetData as $contador => $cols)
					{
					if ($inicio == true and ( $cols["A"] == "" or !intval($cols["A"]) > 0 ) )
						{
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
							$mesAnoReferencia = $nrMes."/".$nrAno;
								
							//$this->info($nrUnidade."-".$nrMes."-".$nrAno);
							$sqlBuscaDadosBoleto = "
													select
															a.cd_boleto_mes,
															b.cd_boleto_mes_unidade,
															ifnull(sum(vlr_item_boleto),0) as vlr_boleto,
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
																and b.cd_unidade = '{$nrUnidade}'";
							
							$arrDadosBoletoSistema = P4A_DB::singleton()->fetchRow($sqlBuscaDadosBoleto);
							
							if (count($arrDadosBoletoSistema) > 1 )
								{
								$cdBoletoMes = $arrDadosBoletoSistema["cd_boleto_mes"];
								$nrBoletoSistema = $arrDadosBoletoSistema["cd_boleto_mes_unidade"];
								$vlrBoletoSistema = $arrDadosBoletoSistema["vlr_boleto"];
								$statusBoletoSistema = $arrDadosBoletoSistema["status"];
								$dataVencSistema = $arrDadosBoletoSistema["dt_vencimento"];
								
								if ( $mesAnoReferencia == $this->fldMesAnoReferenciaBoleto->getNewValue())
									{
									$arrDadosPlanilhaCarregada[] = array(
																			"sheetname"=>$sheetName,
																			"nosso_numero"=>$cols["A"],
																			"seu_numero"=>$cols["C"],
																			"nome_pagador"=>$cols["F"],
																			"vencimento"=>$cols["I"],
																			"valor_titulo"=>str_replace(",", ".", $cols["L"]),
																			"boleto_sistema"=>$nrBoletoSistema,
																			"data_venc_sistema"=>$dataVencSistema,
																			"valor_boleto_sistema"=>$vlrBoletoSistema);
									
									$this->importarNossoNumero($nrBoletoSistema,$cols["A"]);
									}
									
								if($mesAnoReferencia == $this->fldMesAnoReferenciaBoleto->getNewValue() and ($cols["L"] - number_format($vlrBoletoSistema,2,",",".")<>0))
									{
									$arrDadosPlanilhaBoletosErrados[] = array(
																			"sheetname"=>$sheetName,
																			"nosso_numero"=>$cols["A"],
																			"seu_numero"=>$cols["C"],
																			"nome_pagador"=>$cols["F"],
																			"vencimento"=>$cols["I"],
																			"valor_titulo"=>$cols["L"],
																			"boleto_sistema"=>$nrBoletoSistema,
																			"data_venc_sistema"=>$dataVencSistema,
																			"valor_boleto_sistema"=>number_format($vlrBoletoSistema,2,",","."),
																			"valor_diferenca"=>round(str_replace(",", ".",$cols["L"]) - $vlrBoletoSistema,2));

									}
							}
						}
					}
				$inicio = false;
				}

				if ( count($arrDadosPlanilhaCarregada) > 1 )
					{
					$this->build("p4a_array_source","srcPlanilhaCarregada")
						->Load($arrDadosPlanilhaCarregada)
						->setPk("nosso_numero");
					
					$this->build("p4a_table","tblPlanilhaCarregada")
						->setLabel(__("Boletos Carregados pela Planilha"))
						->setSource($this->srcPlanilhaCarregada);
						
					$this->fsetDadosPlanilha->clean();

					$this->fsetDadosPlanilha->anchor($this->tblPlanilhaCarregada);
					}
					
				if ( count($arrDadosPlanilhaBoletosErrados) > 0 )
					{
					$this->build("p4a_array_source","srcPlanilhaBoletosErrados")
					->Load($arrDadosPlanilhaBoletosErrados)
					->setPk("nosso_numero");
						
					$this->build("p4a_table","tblPlanilhaBoletosErrados")
					->setLabel(__("Boletos com diferença"))
					->setSource($this->srcPlanilhaBoletosErrados);
				
					$this->fsetDadosPlanilha->anchor($this->tblPlanilhaBoletosErrados);
					}
				}
			}
			else
				{
				$this->error(__("Arquivo carregado é invalido!"));
				}
		}

	function importarNossoNumero($boletoSistema, $nossoNumero)
		{
		$sqlUpdateBaixaBoleto = "update 
									tbl_boleto_mes_unidade
								set 
									nosso_numero = ?
								where 
									cd_boleto_mes_unidade = ? ";
		
		P4A_DB::singleton()->query($sqlUpdateBaixaBoleto,array($nossoNumero,$boletoSistema));
		}
	}