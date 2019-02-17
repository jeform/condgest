<?php
class baixa_boleto extends satecmax_mask
	{
	function __construct()
		{
		parent::__construct();

		$this->build("p4a_frame","frm")
			->setWidth(450);

		$mes_ano_referencia = p4a::singleton()->masks->processamento_boleto_lote->fields->mes_ano_referencia->getvalue();

		$this->setSource(condgest::singleton()->masks->processamento_boleto_mes_unidade->getSource());
		
		$this->build("p4a_db_source","srcDocumentos")
			->setTable("documentos")
			->setPK("cd_documento")
			->Load();

		$this->setTitle(__("Baixar boleto da Unidade ").($this->fields->cd_unidade->getValue()).(" - Valor histórico R$ ").(number_format($this->fields->vlr_boleto->getValue(),2,",",".")));

		$this->build("p4a_fieldset","fsetDadosPagamento")
			->setLabel(__("Dados Pagamento"))
			->setWidth(400);

		$this->build("p4a_field","fldDtVencimento")
			->setLabel(__("Data de Vencimento"))
			->setWidth(100)
			->enable(false)
			->label->setWidth(150);

		$this->build("p4a_field","fldDtPagamento")
			->setLabel(__("Data de Pagamento"))
			->implement("onChange",$this,"atualizaValorBoleto")
			->setWidth(100)
			->setType("date")
			->label->setWidth(150);

		$this->build("p4a_field","fldDtLiquidacao")
			->setLabel(__("Data de Liquidação"))
			->setWidth(100)
			->setType("date")
			->label->setWidth(150);
			
		$this->build("p4a_field","fldCorrecao")
			->setLabel(__("Correção boleto"))
			->setWidth(100)
			->setProperty("dir","rtl")
			->enable(false)
			->label->setWidth(150);
			
		$this->build("p4a_field","fldVlrBoleto")
			->setLabel(__("Valor Boleto"))
			->setWidth(100)
			->setProperty("dir","rtl")
			->enable(false)
			->label->setWidth(150);

		$this->build("p4a_field","fldVlrPagto")
			->setLabel(__("Valor Pago"))
			->setWidth(100)
			->setProperty("dir","rtl")
			->label->setWidth(150);

		$this->build("p4a_field","fldTpDocumentoReferencia")
			->setLabel(__("Tipo Documento Ref."))
			->setSource($this->srcDocumentos)
			->setSourceDescriptionField("ds_documento")
			->setSourceValueField("tp_documento")
			->setType("select")
			->label->setWidth(150);	
			
		$this->build("p4a_field","fldDocumentoReferencia")
			->setLabel(__("Nr. Documento Ref."))
			->setWidth(100)
			->label->setWidth(150);

		$this->frm->anchorCenter($this->fsetDadosPagamento);

		$this->fsetDadosPagamento->anchor($this->fldDtVencimento)
								->anchor($this->fldDtPagamento)
								->anchor($this->fldDtLiquidacao)
								->anchor($this->fldCorrecao)
								->anchor($this->fldVlrBoleto)
								->anchor($this->fldTpDocumentoReferencia)
								->anchor($this->fldDocumentoReferencia)
								->anchor($this->fldVlrPagto);

		$this->build("P4a_button","btnSalvar")
			->setLabel(__("Baixar boleto"),true)
			->setIcon("actions/document-save")
			->setVisible($this->fields->st_emitido->getValue() == '1')
			->implement("onClick",$this,"efetuarBaixaBoleto");

		$this->frm->anchor($this->btnSalvar);

		$this->display("main",$this->frm);
		}

	function saveRow()
		{
		$dtVencimento = p4a::singleton()->masks->processamento_boleto_mes_unidade->fields->dt_vencimento->getvalue();
		$vlrBoleto = number_format($this->fields->vlr_boleto->getValue(),2,",",".");
	
		if ( $this->fldDtPagamento->getSQLNewValue() == "" or $this->fldVlrBoleto->getSQLNewValue() == "" )
			{
			$this->error(__("Preencha todos os campos obrigatórios!"));
			return false;
			}
		$this->showPrevMask();
		}
			
	function atualizaValorBoleto()
		{		
		$percentualInadimplencia = condgest::singleton()->getParametro("PERC_INADIMPLENCIA");		
		
		$vlrHistorico = number_format($this->fields->vlr_boleto->getValue(),2,".",",");
		$cdBoletoMesUnidade = p4a::singleton()->masks->processamento_boleto_mes_unidade->fields->cd_boleto_mes_unidade->getvalue();
		
		$intervaloDatas = intervaloData($this->fldDtVencimento->getNewValue(),$this->fldDtPagamento->getNewValue());
		
		if($intervaloDatas > 0)
			{						
			$vlrMulta = $vlrHistorico * 0.02;
			$vlrJuros = ((($vlrHistorico * 0.01) / 30) * (intervaloData($this->fldDtVencimento->getNewValue(),$this->fldDtPagamento->getNewValue())));
			$vlrCorrigido = number_format($vlrHistorico + $vlrMulta + $vlrJuros,2);	
			$this->fldCorrecao->setNewValue(number_format($vlrMulta + $vlrJuros,2));
			$this->fldVlrBoleto->setNewValue($vlrCorrigido);
			}
		else
			{
			$vlrCorrigido = number_format($vlrHistorico - ($vlrHistorico * $percentualInadimplencia),2);
			$this->fldCorrecao->setNewValue("0,00");
			$this->fldVlrBoleto->setNewValue($vlrCorrigido);
			}	
		}
		
	function efetuarBaixaBoleto()
		{
		$cdBoletoMesUnidade = p4a::singleton()->masks->processamento_boleto_mes_unidade->fields->cd_boleto_mes_unidade->getvalue();
			
		if($this->fldDtPagamento->getNewValue() == null)
			{
			$this->error("Selecione a data de pagamento do boleto!");
			return false;
			}	
		if($this->fldDtLiquidacao->getNewValue() == null)
			{
			$this->error("Selecione a data de liquidação do boleto!");
			return false;
			}			
		try
			{
			P4A_DB::singleton()->beginTransaction();
	
			p4a_db::singleton()->query("UPDATE tbl_boleto_mes_unidade SET dt_pagamento = '".formatarDataBanco($this->fldDtPagamento->getNewValue())."' WHERE cd_boleto_mes_unidade = '{$cdBoletoMesUnidade}'");
			p4a_db::singleton()->query("UPDATE tbl_boleto_mes_unidade SET dt_liquidacao = '".formatarDataBanco($this->fldDtLiquidacao->getNewValue())."' WHERE cd_boleto_mes_unidade = '{$cdBoletoMesUnidade}'");
			p4a_db::singleton()->query("UPDATE tbl_boleto_mes_unidade SET vlr_juros = '{$this->fldCorrecao->getNewValue()}' WHERE cd_boleto_mes_unidade = '{$cdBoletoMesUnidade}'");
			p4a_db::singleton()->query("UPDATE tbl_boleto_mes_unidade SET vlr_baixa_boleto = '".str_ireplace(",",".",$this->fldVlrPagto->getNewValue())."' WHERE cd_boleto_mes_unidade = '{$cdBoletoMesUnidade}'");
			p4a_db::singleton()->query("UPDATE tbl_boleto_mes_unidade SET tp_documento_referencia = '{$this->fldTpDocumentoReferencia->getNewValue()}' WHERE cd_boleto_mes_unidade = '{$cdBoletoMesUnidade}'");
			p4a_db::singleton()->query("UPDATE tbl_boleto_mes_unidade SET cd_documento_referencia = '{$this->fldDocumentoReferencia->getNewValue()}' WHERE cd_boleto_mes_unidade = '{$cdBoletoMesUnidade}'");
			p4a_db::singleton()->query("UPDATE tbl_boleto_mes_unidade SET st_baixado = '1' WHERE cd_boleto_mes_unidade = '{$cdBoletoMesUnidade}'");
			
			$vlrDiferencaBaixaBoleto = abs($this->fldVlrBoleto->getNewValue() - str_ireplace(",",".",$this->fldVlrPagto->getNewValue()));
			
			$difAceitaBaixaBoleto = condgest::singleton()->getParametro("DIFERENCA_ACEITAVEL_BOL");
			
			if($vlrDiferencaBaixaBoleto != 0 && $vlrDiferencaBaixaBoleto > $difAceitaBaixaBoleto)
				{
				if($this->fldVlrBoleto->getNewValue() > $this->fldVlrPagto->getNewValue())
					{
					$tipoMovimento = 'D';
					}
				else
					{
					$tipoMovimento = 'C';
					}
					
					$this->adicionarDiferencaBoleto($vlrDiferencaBaixaBoleto,$tipoMovimento);
				}	

			$this->registraBaixaCaixa();	
			
			P4A_DB::singleton()->commit();
			}

		catch (Exception $e)
			{
			P4A_DB::singleton()->rollback();
			$this->error(__("Erro na atualização do Registro! ".$e->getMessage()));
			}	
			
		$this->showPrevMask();
		}	
			
	function adicionarDiferencaBoleto($vlrDiferencaBaixaBoleto, $tipoMovimento)
		{
		$cdCategoria = condgest::singleton()->getParametro("PROCESSAMENTO_BOLETO_CD_DIF_MES_ANTERIOR");
		$cdUnidade = p4a::singleton()->masks->processamento_boleto_mes_unidade->fields->cd_unidade->getvalue();
		list($mes,$ano) = desmontarMesAnoReferencia(p4a::singleton()->masks->processamento_boleto_lote->fields->mes_ano_referencia->getvalue());
		$mesAnoRefPosterior = acrescenterMesAnoRef('1', $mes, $ano, 1);
		
		$cdBoletoMesUnidade = P4A_DB::singleton()->fetchOne("SELECT 
																	b.cd_boleto_mes_unidade 
															   FROM 
																	tbl_boleto_mes a, tbl_boleto_mes_unidade b
															  WHERE 
																	a.cd_boleto_mes = b.cd_boleto_mes
																		and a.mes_ano_referencia = '".$mesAnoRefPosterior."'
																		and b.cd_unidade = '".$cdUnidade."'");			
		
		p4a_db::singleton()->query("INSERT INTO tbl_boleto_mes_unidade_itens_cobranca 
													(cd_boleto_mes_unidade,
													cd_unidade,
													cd_item_cobranca,
													ds_item_boleto,
													vlr_item_boleto,
													tp_movimento)
										 VALUES										
													('".$cdBoletoMesUnidade."',
													 '".$cdUnidade."',
													 '".$cdCategoria."',
													 '',
													 '".$vlrDiferencaBaixaBoleto."',
													 '".$tipoMovimento."'													 
													 )");
		
		$this->info("Inserida diferença conforme abaixo: <br />\n Valor: R$ ".$vlrDiferencaBaixaBoleto. "<br />\n Movimento: ".$tipoMovimento. "<br />\n  Mês/ano ref: ". $mesAnoRefPosterior. "<br />\n Unidade: ".$cdUnidade);
		}
		
	function registraBaixaCaixa()
		{
		$objCaixaMovimento = new movimentoCaixa(2);
		
		$tipoDocumento = "REC";
		$caixaBanco = condgest::singleton()->getParametro("CAIXA_BANCO");
		$cdUnidade = p4a::singleton()->masks->processamento_boleto_mes_unidade->fields->cd_unidade->getvalue();
		$mesAnoReferencia = p4a::singleton()->masks->processamento_boleto_lote->fields->mes_ano_referencia->getvalue();
		$cdCategoria = condgest::singleton()->getParametro("PROCESSAMENTO_BOLETO_CD_CATEGORIA_BAIXA_BOLETO");
		
		$cdPessoa = P4A_DB::singleton()->fetchOne("SELECT 	
														b.cd_pessoa
													 FROM	
														tbl_unidades a inner join tbl_pessoas b
                                                            on a.cd_pessoa = b.cd_pessoa
													WHERE	
														a.cd_unidade = '{$cdUnidade}'");
		
		$objCaixaMovimento->newMovimentoEntrada($this->fldDtLiquidacao->getNewValue(),
												str_ireplace(",",".",$this->fldVlrPagto->getNewValue()),
												$cdPessoa,
												$cdUnidade." - ".$mesAnoReferencia,
												$tipoDocumento,
												"BAIXA BOLETO - UNIDADE " .$cdUnidade. " - MES/ANO REF. " .$mesAnoReferencia, 
												$cdCategoria,
												$caixaBanco,
												"");
												
												
		}

	function main()
		{
		$this->fldDtVencimento->setNewValue(p4a::singleton()->masks->processamento_boleto_mes_unidade->fields->dt_vencimento->getNewValue());
		parent::main();
		}
}