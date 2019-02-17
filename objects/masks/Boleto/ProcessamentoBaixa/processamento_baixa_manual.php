<?php
class processamento_baixa_manual extends satecmax_mask
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
			->setLabel(__("Dados Pagamento:"))
			->setWidth(400);

		$this->build("p4a_field","fldDtVencimento")
			->setLabel(__("Data de Vencimento:"))
			->setWidth(100)
			->enable(false)
			->label->setWidth(150);

		$this->build("p4a_field","fldDtPagamento")
			->setLabel(__("Data de Pagamento:"))
			->implement("onChange",$this,"atualizaValorBoleto")
			->setWidth(100)
			->setType("date")
			->label->setWidth(150);
			
		$this->build("p4a_field","fldCorrecao")
			->setLabel(__("Correção boleto:"))
			->setWidth(100)
			->setProperty("dir","rtl")
			->enable(false)
			->label->setWidth(150);
			
		$this->build("p4a_field","fldVlrBoleto")
			->setLabel(__("Valor Boleto:"))
			->setWidth(100)
			->setProperty("dir","rtl")
			->enable(false)
			->label->setWidth(150);

		$this->build("p4a_field","fldVlrPagto")
			->setLabel(__("Valor Pago:"))
			->setWidth(100)
			->setProperty("dir","rtl")
			->label->setWidth(150);

		$this->frm->anchorCenter($this->fsetDadosPagamento);

		$this->fsetDadosPagamento->anchor($this->fldDtVencimento)
								->anchor($this->fldDtPagamento)
								->anchor($this->fldCorrecao)
								->anchor($this->fldVlrBoleto)
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
/*			
	function atualizaValorBoleto()
		{		
		$percentualInadimplencia = condgest::singleton()->getParametro("PERC_INADIMPLENCIA");		
		
		$vlrHistorico = number_format($this->fields->vlr_boleto->getValue(),2,".",",");
		$cdBoletoMesUnidade = p4a::singleton()->masks->processamento_boleto_mes_unidade->fields->cd_boleto_mes_unidade->getvalue();
		
		//Valida se a data de vencimento é um dia útil
		//$diaUtilVencimento = alteraDiaUtilVencimento($this->fldDtVencimento->getNewValue());
		$diaUtilVencimento = $this->fldDtVencimento->getNewValue();
		
		$intervaloDatas = intervaloData($diaUtilVencimento,$this->fldDtPagamento->getNewValue());
		
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
			$vlrCorrigido = number_format($vlrHistorico,2);
			$this->fldCorrecao->setNewValue("0,00");
			$this->fldVlrBoleto->setNewValue($vlrCorrigido);
			}	
		}
*/
		
		function atualizaValorBoleto()
		{
			$percentualInadimplencia = condgest::singleton()->getParametro("PERC_INADIMPLENCIA");
			
			$vlrHistorico = number_format($this->fields->vlr_boleto->getValue(),2,".",",");
			$cdBoletoMesUnidade = p4a::singleton()->masks->processamento_boleto_mes_unidade->fields->cd_boleto_mes_unidade->getvalue();
			
			$intervaloDatas = intervaloData($this->fldDtVencimento->getNewValue(),$this->fldDtPagamento->getNewValue());
			
			if($intervaloDatas > 0)
			{
				$vlrMulta = $vlrHistorico * 0.02;
				$vlrJuros = (($vlrHistorico * 0.00033) * (intervaloData($this->fldDtVencimento->getNewValue(),$this->fldDtPagamento->getNewValue())));
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
				
		try
			{
			P4A_DB::singleton()->beginTransaction();
	
			$sqlBaixaBoletoIndividual = "update 
												tbl_boleto_mes_unidade
										set 
												dt_pagamento = ?,
												vlr_juros = ?,
												vlr_baixa_boleto = ?,
												st_baixado = 1,
												nr_movimento = ?
										where 
												cd_boleto_mes_unidade = ?";
			
			$dtPagamento = formatarDataBanco($this->fldDtPagamento->getNewValue());
			
			$vlrJuros = $this->fldCorrecao->getNewValue();
			$vlrPagamento = str_ireplace(",",".",$this->fldVlrPagto->getNewValue()); 
			$cdBoletoMesUnidade = p4a::singleton()->masks->processamento_boleto_mes_unidade->fields->cd_boleto_mes_unidade->getvalue();	
			
			// inserção da diferença do boleto baixado
			$vlrDiferencaBoleto = $this->fldVlrBoleto->getNewValue() - str_ireplace(",",".",$this->fldVlrPagto->getNewValue());
			$diferencaAceitaBaixa = condgest::singleton()->getParametro("DIFERENCA_ACEITAVEL_BOLETO");
		
			if($vlrDiferencaBoleto != 0 && abs($vlrDiferencaBoleto) > abs($diferencaAceitaBaixa))
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
							
			$nrLancamentoCaixa = $this->registraBaixaCaixa();	
			
			P4A_DB::singleton()->query($sqlBaixaBoletoIndividual,array($dtPagamento,$vlrJuros,$vlrPagamento,$nrLancamentoCaixa,$cdBoletoMesUnidade));
			
			P4A_DB::singleton()->commit();
			$this->info(__("Boleto da Unidade ".$this->fields->cd_unidade->getValue()." - baixado no valor R$ ".$vlrPagamento." em ".$dtPagamento));
			}

		catch (Exception $e)
			{
			P4A_DB::singleton()->rollback();
			$this->error(__("Erro na atualização do Registro! ".$e->getMessage()));
			}	
			
		$this->showPrevMask();
		}
		
	function registraBaixaCaixa()
		{		
		$tipoDocumento = "BOL";
		$caixaBanco = condgest::singleton()->getParametro("CAIXA_BANCO");
		$cdUnidade = p4a::singleton()->masks->processamento_boleto_mes_unidade->fields->cd_unidade->getvalue();
		$mesAnoReferencia = p4a::singleton()->masks->processamento_boleto_lote->fields->mes_ano_referencia->getvalue();
		$cdCategoria = condgest::singleton()->getParametro("PROCESSAMENTO_BOLETO_CD_CATEGORIA_BAIXA_BOLETO");
		$dtLiquidacao = retornaDataLiquidacao($this->fldDtPagamento->getNewValue());
		$cdPessoa = P4A_DB::singleton()->fetchOne("select
														cd_pessoa
												   from
														tbl_unidades
													where
														cd_unidade = '{$cdUnidade}'");
		
		
		$objCaixaMovimento = new movimentoCaixa(2);
		$objCaixaMovimento = new movimentoCaixa($caixaBanco);
		
		$nrNumeroMovimentoCaixa = $objCaixaMovimento->newMovimentoEntrada(
																		formatarDataBanco1($dtLiquidacao),
																		$this->fldVlrPagto->getNewValue(),
																		$cdPessoa,
																		$cdUnidade." - ".$mesAnoReferencia,
																		$tipoDocumento,
																		"BAIXA BOLETO - UNIDADE " .$cdUnidade. " - MES/ANO REF. " .$mesAnoReferencia,
																		$cdCategoria,
																		6,
																		"");
																		
		return $nrNumeroMovimentoCaixa;
		}
		
	function main()
		{
		$this->fldDtVencimento->setNewValue(p4a::singleton()->masks->processamento_boleto_mes_unidade->fields->dt_vencimento->getNewValue());
		parent::main();
		}	
	}			