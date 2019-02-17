<?php 	
class baixa_boleto_acordo extends satecmax_mask
	{
	function __construct()
		{
		parent::__construct();

		$this->build("p4a_frame","frm")
			->setWidth(500);

		$mes_ano_referencia = p4a::singleton()->masks->processamento_boleto->fields->mes_ano_referencia->getvalue();
		
		$this->setSource(condgest::singleton()->masks->processamento_boleto_mes_unidade->getSource());
		
		$this->setTitle(__("Baixar boleto da Unidade ").($this->fields->cd_unidade->getValue()).(" - Valor histórico R$ ").(number_format($this->fields->vlr_boleto->getValue(),2,",",".")));
		
		$this->build("p4a_fieldset","fsetDadosAcordo")
			->setLabel(__("Dados do Acordo"))
			->setWidth(400);

		$this->build("p4a_field","fld_nr_acordo")
			->setLabel(__("Número do Acordo"))
			->setWidth(120)
			->label->setWidth(150);

		$this->build("p4a_field","fld_dt_acordo")
			->setLabel(__("Data do Acordo"))
			->setWidth(100)
			->setType("date")
			->label->setWidth(150);

		$this->build("p4a_field","fld_vlr_boleto_acordo")
			->setLabel(__("Valor Boleto Acordo"))
			->setWidth(120)
			->setProperty("dir","rtl")
			->label->setWidth(150);

		$this->frm->anchorCenter($this->fsetDadosAcordo);

		$this->fsetDadosAcordo->anchor($this->fld_nr_acordo)
							->anchor($this->fld_dt_acordo)
							->anchor($this->fld_vlr_boleto_acordo);

		$this->build("P4a_button","btnBaixarBoletoAcordo")
			->setLabel(__("Efetuar baixa"),true)
			->setIcon("actions/document-save")
			->setVisible($this->fields->st_emitido->getValue() == '1' || $this->fields->st_baixado->getNewValue() == '0')
			->implement("onClick",$this,"baixaBoletoAcordo");
		
		$this->frm->anchor($this->btnBaixarBoletoAcordo);
		
		$this->display("main",$this->frm);
		}
		
	function baixaBoletoAcordo()
		{
		if($this->fld_nr_acordo->getNewValue() == null)
			{
			$this->error("Insira o número do Contrato!");
			return false;
			}
				
		if($this->fld_dt_acordo->getNewValue() == null)
			{
			$this->error("Selecione a data do acordo!");
			return false;
			}
		
		if($this->fld_vlr_boleto_acordo->getNewValue() == null)
			{
			$this->error("Informe o valor do boleto de acordo!");
			return false;
			}	
			
		$nr_acordo = $this->fld_nr_acordo->getNewValue();
		$dt_acordo = trim($this->fld_dt_acordo->getNewValue());
		$vlrBoletoAcordo =  formataValoresBanco($this->fld_vlr_boleto_acordo->getNewValue());
		
		$cdBoletoMesUnidade = p4a::singleton()->masks->processamento_boleto_mes_unidade->fields->cd_boleto_mes_unidade->getvalue();
		
		
		$dtAcordoFormatado = formatarDataBanco($dt_acordo);
		
		try
			{
			P4A_DB::singleton()->beginTransaction();
	
			p4a_db::singleton()->query("update tbl_boleto_mes_unidade set nr_acordo = '{$nr_acordo}' where cd_boleto_mes_unidade = '{$cdBoletoMesUnidade}'");
			p4a_db::singleton()->query("update tbl_boleto_mes_unidade set dt_pagamento = '{$dtAcordoFormatado}' where cd_boleto_mes_unidade = '{$cdBoletoMesUnidade}'");
			p4a_db::singleton()->query("update tbl_boleto_mes_unidade set vlr_baixa_boleto = '{$vlrBoletoAcordo}' where cd_boleto_mes_unidade = '{$cdBoletoMesUnidade}'");
			p4a_db::singleton()->query("update tbl_boleto_mes_unidade set st_baixado = '3' where cd_boleto_mes_unidade = '{$cdBoletoMesUnidade}'");
	
			P4A_DB::singleton()->commit();
				
			$this->info("Boleto baixado com sucesso!");
			}
		catch (Exception $e)
			{
			P4A_DB::singleton()->rollback();
					
			$this->error(__("Erro na atualização do Registro! ".$e->getMessage()));
			}
		
		$this->showPrevMask();
		}	
	}