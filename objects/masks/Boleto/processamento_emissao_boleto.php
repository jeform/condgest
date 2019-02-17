<?php
class processamento_emissao_boleto extends satecmax_mask
	{
	function __construct()
		{
		parent::__construct();

		$this->build("p4a_frame","frm")
			->setWidth(550);

		$this->build("satecmax_full_toolbar","toolbar")
			->setMask($this);

		$mes_ano_referencia = p4a::singleton()->masks->processamento_boleto_lote->fields->mes_ano_referencia->getvalue();
		$this->setSource(condgest::singleton()->masks->processamento_boleto_mes_unidade->getSource());

		$this->setTitle(__("Emissão dos boletos - ".$mes_ano_referencia));

		$this->montaCamposEdicaoSource();
			
		$this->display("main",$this->frm);

		$this->display("top",$this->toolbar);

		}
	function montaCamposEdicaoSource()
		{
		$this->build("p4a_fieldset","fsetDetalhesEmissaoBoleto")
			->setLabel(__("Detalhes"))
			->setWidth(400);
	
		$this->frm->anchorCenter($this->fsetDetalhesEmissaoBoleto);
		
		$this->build("satecmax_db_source","srcDiferencaBaixa")
			->setTable("tbl_boleto_mes_unidade")
			->setWhere("st_diferenca = 1")
			->setPk("cd_unidade")
			->setFields(array("*","(select a.mes_ano_referencia from tbl_boleto_mes a where a.cd_boleto_mes = tbl_boleto_mes_unidade.cd_boleto_mes)"=>"mesAnoReferencia"))
			->Load();
		
		$this->build("p4a_table","tblDiferencaBoleto")
			->setLabel(_("Diferença dos meses anteriores"))
			->setSource($this->srcDiferencaBaixa)
			->setWidth(350);
				
		$this->tblDiferencaBoleto->cols->cd_unidade->setLabel(_(("Unidade")));
		$this->tblDiferencaBoleto->cols->vlr_diferenca->setLabel(_(("Valor")));
		$this->tblDiferencaBoleto->cols->mesAnoReferencia->setLabel(_(("Mês/Ano Referência")));
		
		$this->tblDiferencaBoleto->setVisibleCols(array("cd_unidade","mesAnoReferencia","vlr_diferenca"));
						
		$this->build("p4a_button","btnProcessarDiferencas")
			->setLabel(__("Processar Diferenças"))
			->implement("onClick",$this,"processarDiferencas");
		
		$this->build("p4a_field","fldDtVencimento")
			->setLabel(__("Data de Vencimento"))
			->setType("date");
		
		$this->build("p4a_button","btnEmitirBoletos")
			->setLabel(__("Emitir boletos"))
			->implement("onClick",$this,"emitirBoletos");
		
		if($this->srcDiferencaBaixa->getNumRows() > 0)
			{
			
			$this->fsetDetalhesEmissaoBoleto->clean();
			$this->fsetDetalhesEmissaoBoleto->anchor($this->tblDiferencaBoleto);
			$this->fsetDetalhesEmissaoBoleto->anchor($this->btnProcessarDiferencas);
			}
		}
		
	function processarDiferencas()
		{
		// pegar as diferencas para efetuar a inserção no boleto corrente
		$arrDadosDiferencas = $this->srcDiferencaBaixa->getAll();
		$cdBoletoMes = p4a::singleton()->masks->processamento_boleto_mes_unidade->fields->cd_boleto_mes->getValue();
		try
			{
			P4A_DB::singleton()->beginTransaction();
				
			foreach($arrDadosDiferencas as $nrLinha => $dadosLinhaBaixa)
				{
				$cdUnidade = $dadosLinhaBaixa["cd_unidade"];
				$vlrDiferenca = $dadosLinhaBaixa["vlr_diferenca"];
				$mesAnoReferencia = $dadosLinhaBaixa["mesAnoReferencia"];	
				
				$buscaBoletoMesUnidade = "select
												cd_boleto_mes_unidade
										  from
												tbl_boleto_mes_unidade
										  where 
												cd_boleto_mes = {$cdBoletoMes}
												and cd_unidade = {$cdUnidade}";
				
				$arrBuscaDadosUnidade = P4A_DB::singleton()->fetchRow($buscaBoletoMesUnidade,array($cdBoletoMes,$cdUnidade));
				
				$cdBoletoMesUnidade = $arrBuscaDadosUnidade["cd_boleto_mes_unidade"];
				
				$obs = "Diferença do mês/Ano referência ".$mesAnoReferencia;
				$cdItemCobranca = '6';
				
				P4A_DB::singleton()->query("insert into
														 tbl_boleto_mes_unidade_itens_cobranca 
				 												(cd_boleto_mes_unidade,
																 cd_unidade,
																 cd_item_cobranca,
																 ds_item_boleto,
																 vlr_item_boleto)
											values
																 ('".$cdBoletoMesUnidade."',
																 '".$cdUnidade."',
																 '".$cdItemCobranca."',
																 '".$obs."',
																 '".$vlrDiferenca."')
																 ");
				
				$buscaBoletoMesUnidadeOrigem = "select
														cd_boleto_mes_unidade
											   from
														tbl_boleto_mes a, tbl_boleto_mes_unidade b
										   	   where
														a.cd_boleto_mes = b.cd_boleto_mes
															and a.mes_ano_referencia = ?
															and b.cd_unidade = ?";
				
				$arrBuscaDadosBoletoOrigem = P4A_DB::singleton()->fetchRow($buscaBoletoMesUnidadeOrigem,array($mesAnoReferencia,$cdUnidade));
				$cdBoletoMesUnidadeOrigem = $arrBuscaDadosBoletoOrigem["cd_boleto_mes_unidade"];
				
				P4A_DB::singleton()->query("update
													tbl_boleto_mes_unidade
											set 
													st_diferenca = 0
											where
													cd_boleto_mes_unidade = {$cdBoletoMesUnidadeOrigem}");
				
				}

			P4A_DB::singleton()->commit();			
			}					
		catch (Exception $e)
			{
				P4A_DB::singleton()->rollback();
				$this->error(__("Erro na atualização do Registro! ".$e->getMessage()));
			}
			
			$this->tblDiferencaBoleto->setInvisible();
			$this->btnProcessarDiferencas->setInvisible();
		}

	function emitirBoletos()
		{
		$dtVencimento = $this->fldDtVencimento->getNewValue();
		
		if($dtVencimento == '')
			{
			$this->error("Selecione uma data de vencimento!");
			return false;
			}

		$cdBoletoMes = p4a::singleton()->masks->processamento_boleto_mes_unidade->fields->cd_boleto_mes->getValue();

		$this->arrUnidades = p4a_db::singleton()->fetchAll("select
																	cd_unidade
															from
																	tbl_boleto_mes_unidade
															where
																	st_emitido = '0'
																		and cd_boleto_mes = '{$cdBoletoMes}'");
				
		//Emissão de boleto para todas as unidades...
		foreach($this->arrUnidades as $dadosUnidades)
			{
			$cdUnidade = $dadosUnidades["cd_unidade"];
			
			try
				{
				P4A_DB::singleton()->beginTransaction();
		
				$sqlProcessaEmissaoBoleto = "update
												tbl_boleto_mes_unidade
										 set
												st_emitido = 1,
												dt_vencimento = ?,
												dt_processamento = ?
										 where
												cd_boleto_mes = ?
													and cd_unidade = ?";
		
				$dtProcessamento = formatarDataBanco(date('d/m/Y'));
		
				P4A_DB::singleton()->query($sqlProcessaEmissaoBoleto,array(formatarDataBanco($dtVencimento),$dtProcessamento,$cdBoletoMes,$cdUnidade));
		
				P4A_DB::singleton()->commit();
				}
			catch (Exception $e)
				{
				P4A_DB::singleton()->rollback();
					
				$this->error(__("Erro: ".$e->getCode()." - ".$e->getMessage()));
				}
			}
		$this->info("Boletos com data de vencimento ".$dtVencimento." emitidos com sucesso!");
		$this->showPrevMask();			
		}

	function main()
		{	
		if (count($this->srcDiferencaBaixa->getAll()) == 0)
			{
			$this->fsetDetalhesEmissaoBoleto->clean();
			$this->fsetDetalhesEmissaoBoleto->anchor($this->fldDtVencimento);
			$this->fsetDetalhesEmissaoBoleto->anchor($this->btnEmitirBoletos);
			}
			
		parent::main();
		}		
	}