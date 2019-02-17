<?php
	class acordo_baixar_parcela extends satecmax_mask
	{
		function __construct()
			{
			parent::__construct();

			$this->setSource(condgest::singleton()->masks->acordo_detalhe->getSource());
			$qtdeParcelasAcordo = p4a::singleton()->masks->acordos->fields->qtde_parcelas->getvalue();
						
			$this->setTitle("Baixar Parcela ".$this->fields->nr_parcela->getValue()."/".$qtdeParcelasAcordo." - Valor Hist. R$ ".number_format($this->fields->vlr_parcela->getValue(),2,",","."));
	
			$this->criaFrames();
			}
		
		function criaFrames()
			{	
			$this->build("p4a_frame","frm")
				->setWidth(1024);
			
			$this->build("p4a_fieldset","fsetBaixarParcela")
				->setLabel(__("Detalhes"))
				->setWidth(500);
			
			$this->build("p4a_field","fldDtVencimento")
				->setLabel(__("Data Vencimento"))
				->setWidth(100)
				->enable(false)
				->label->setWidth(150);
			
			$this->build("p4a_field","fldVlrParcelaHistorico")
				->setLabel(__("Valor Parcela"))
				->setWidth(100)
				->setProperty("dir","rtl")
				->enable(false)
				->label->setWidth(150);
			
			$this->build("p4a_field","fldDtRecebimento")
				->setLabel(__("Data Recebimento"))
				->setType("date")
				->setWidth(100)
				->implement("onChange",$this,"atualizaValorParcela")
				->label->setWidth(150);
			
			$this->build("p4a_field","fldVlrRecebimento")
				->setLabel(__("Valor Recebimento"))
				->setWidth(100)
				->setProperty("dir","rtl")
				->label->setWidth(150);
		
			$this->build("p4a_field","fldVlrJuros")
				->setLabel(__("Valor Mora / Multa"))
				->setWidth(100)
				->setProperty("dir","rtl")
				->enable(false)
				->label->setWidth(150);
			
			$this->build("p4a_field","fldTpDocumento")
				->setLabel(__("Tipo Documento"))
				->setSource(P4A::singleton()->src_documentos_cadastrados)
				->setSourceValueField("cd_documento")
				->setSourceDescriptionField("desc_documento")
				->setType("select")
				->setWidth(200)
				->label->setWidth(150);
			
			$this->build("p4a_field","fldNrDocumento")
				->setLabel(__("Nr. Documento"))
				->setWidth(100)
				->label->setWidth(150);
			
			$this->build("p4a_button", "btn_baixar_parcela")
				->setLabel(__("Baixar Parcela"),true)
				->setIcon("actions/document-save")
				->implement("onClick",$this,"baixarParcela");
			
			$this->fsetBaixarParcela->anchor($this->fldDtVencimento)
									->anchor($this->fldVlrParcelaHistorico)
									->anchor($this->fldDtRecebimento)
									->anchor($this->fldVlrRecebimento)
									->anchor($this->fldVlrJuros)
									->anchor($this->fldTpDocumento)
									->anchor($this->fldNrDocumento)
									->anchor($this->btn_baixar_parcela);
			
			$this->frm->anchorCenter($this->fsetBaixarParcela);
			
			$this->display("main",$this->frm);
			$this->display("menu",p4a::singleton()->menu);
			
			}
	
		 function atualizaValorParcela()
			{
			$dtVencimento = $this->fldDtVencimento->getNewValue();
			$dtRecebimento = $this->fldDtRecebimento->getNewValue();
			$percentualMulta = condgest::singleton()->getParametro("PERC_MULTA_APOS_VENC");
			$percentualMora =  condgest::singleton()->getParametro("PERC_MORA_APOS_VENC");
			$vlr_historico = $this->fields->vlr_parcela->getValue();
			$diasAtraso = intervaloData($dtVencimento,$dtRecebimento);
			$vlrMulta = $percentualMulta * $vlr_historico;
			$vlrMora = round($percentualMora * $diasAtraso * $vlr_historico,2);
	
			if($diasAtraso <= 0 )
				{
				$this->fldVlrJuros->setNewValue('0');
				}
			elseif($diasAtraso > 0)
				{
				$this->fldVlrJuros->setNewValue($vlrMulta + $vlrMora);
				}
			}
	
		function main()
			{
			$this->fldDtVencimento->setNewValue(formatarDataAplicacao($this->fields->dt_vencimento->getValue()));
			$this->fldVlrParcelaHistorico->setNewValue($this->fields->vlr_parcela->getValue());
			parent::main();
			}
		
		function baixarParcela()
			{
			if($this->fldDtRecebimento->getNewValue() == null)
				{
				$this->error("Selecione a data de pagamento!");
				return false;
				}
				
			$cdBoletoDetalhe = $this->fields->cd_acordo_detalhe->getValue();
			$dtRecebimento = formatarDataBanco($this->fldDtRecebimento->getNewValue());
			$vlrRecebimento = number_format($this->fldVlrRecebimento->getSQLNewValue(),2,",",".");
			$vlrJuros = number_format($this->fldVlrJuros->getNewValue(),2,",",".");
			$tpDocumento = $this->fldTpDocumento->getNewValue();
			$nrDocumento = $this->fldNrDocumento->getNewValue();
			
			try
				{
				P4A_DB::singleton()->beginTransaction();
	
				p4a_db::singleton()->query("UPDATE acordos_detalhes SET dt_recebimento = '{$dtRecebimento}' WHERE cd_acordo_detalhe = '{$cdBoletoDetalhe}'");
				p4a_db::singleton()->query("UPDATE acordos_detalhes SET vlr_recebimento = '{$vlrRecebimento}' WHERE cd_acordo_detalhe = '{$cdBoletoDetalhe}'");
				p4a_db::singleton()->query("UPDATE acordos_detalhes SET vlr_juros = '{$vlrJuros}' WHERE cd_acordo_detalhe = '{$cdBoletoDetalhe}'");
				p4a_db::singleton()->query("UPDATE acordos_detalhes SET cd_st_recebimento = 2 WHERE cd_acordo_detalhe = '{$cdBoletoDetalhe}'");
				p4a_db::singleton()->query("UPDATE acordos_detalhes SET cd_categoria_conta = 39 WHERE cd_acordo_detalhe = '{$cdBoletoDetalhe}'");
				p4a_db::singleton()->query("UPDATE acordos_detalhes SET tp_documento = '{$tpDocumento}' WHERE cd_acordo_detalhe = '{$cdBoletoDetalhe}'");
				p4a_db::singleton()->query("UPDATE acordos_detalhes SET nr_documento = '{$nrDocumento}' WHERE cd_acordo_detalhe = '{$cdBoletoDetalhe}'");
				
				$this->registraBaixaParcelaAcordo();
				P4A_DB::singleton()->commit();
				}
			catch (Exception $e)
				{
				P4A_DB::singleton()->rollback();
		
				$this->error(__("Erro na atualização do Registro! ".$e->getMessage()));
				} 
			$this->showPrevMask();
			}
	
		function registraBaixaParcelaAcordo()
			{				
			$caixaBanco = condgest::singleton()->getParametro("CAIXA_BANCO");
				
			$dtLiquidacao = retornaDataLiquidacao($this->fldDtRecebimento->getNewValue());
			
			$objCaixaMovimento = new movimentoCaixa($caixaBanco);
	
			$objCaixaMovimento->newMovimentoEntrada(formatarDataBanco1($dtLiquidacao),
													$this->fldVlrRecebimento->getNewValue(),
													p4a::singleton()->masks->acordos->fields->cd_pessoa->getvalue(),
													$this->fldNrDocumento->getNewValue(),
													$this->fldTpDocumento->getNewValue(),
													"BAIXA DA PARCELA ".$this->fields->parcelas->getValue()." DO ACORDO Nº ".p4a::singleton()->masks->acordos->fields->cd_acordo->getvalue(),
													"39",
													2,
													"");
			
			}

		}