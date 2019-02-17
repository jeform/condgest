<?php
class boletos_atraso extends satecmax_mask
	{
	function __construct()
		{
		parent::__construct();
			
		$this->build("satecmax_db_source","src_hist_acordo")
			->setTable("hist_acordo")
			->setPk("cd_acordo")
			->Load();
		
		$this->setSource($this->src_hist_acordo);
		
		$this->build("p4a_frame","frm")
			->setWidth(500);
		
		$cd_unidade = condgest::singleton()->masks->acordo_extrajudicial->fields->cd_unidade->getValue();
		
		$this->setTitle(__("Acordo Extrajudicial ref. a Unidade ".$cd_unidade));
		
		$this->build("p4a_fieldset","fsetParcelasAtraso")
			->setLabel(__("Parcelas em atraso"))
			->setWidth(500);
		
		$this->arrParcelas = p4a_db::singleton()->fetchAll("select 
																b.cd_boleto_mes,
															    a.mes_ano_referencia,
															    b.cd_unidade,
																b.dt_vencimento,
															    sum(c.vlr_item_boleto) 'vlr_historico',
															    (current_date - b.dt_vencimento) 'qtde_dias_atraso',
															    ROUND(SUM(c.vlr_item_boleto) * 0.02, 2) as 'vlr_multa',
															    ROUND(SUM(c.vlr_item_boleto) * (current_date - b.dt_vencimento) * 0.000333,
															            2) 'vlr_mora',
															    ROUND(SUM(c.vlr_item_boleto) * 1.02 + SUM(c.vlr_item_boleto) * (current_date - b.dt_vencimento) * 0.000333,
															            2) 'vlr_total',
																b.st_baixado
															from
															    tbl_boleto_mes a,
															    tbl_boleto_mes_unidade b,
															    tbl_boleto_mes_unidade_itens_cobranca c
															where
															    a.cd_boleto_mes = b.cd_boleto_mes
															        and b.cd_boleto_mes_unidade = c.cd_boleto_mes_unidade
															        and b.cd_unidade = c.cd_unidade
															        and b.st_emitido = '1'
															        and b.st_baixado = '0'
															        and (current_date - b.dt_vencimento) > 0
															        and b.cd_unidade = '{$cd_unidade}'
															group by b.cd_boleto_mes"); 
				
		foreach($this->arrParcelas as $dadosParcelas)
			{
			$cdBoletoMes = $dadosParcelas["cd_boleto_mes"];
			$cdUnidade = $dadosParcelas["cd_unidade"];
			$dtVencimento = $dadosParcelas["dt_vencimento"];
			$vlrTotal = $dadosParcelas["vlr_total"];
			
			$nmBoletoMes = "fldParcela_".$cdBoletoMes;
			
			if($dadosParcelas["st_baixado"] == '0')
				{
				$status = "Aberto";
				}
			else 	
				{
				$status = "Fechado";
				}	
			
			$this->build("p4a_field",$nmBoletoMes)
				->setLabel(__("<b>Vencimento:</b> ". date("d/m/Y",strtotime($dtVencimento)) . " - <b>Valor:</b> R$ " . $vlrTotal . " - <b>Status:</b> " . $status))
				->setType("checkbox")
				->implement("onClick",$this,"calculaValorTotal")
				->label->setWidth(350);

			$this->fsetParcelasAtraso->anchor($this->$nmBoletoMes);			
			}	

		$this->build("p4a_fieldset","fsetDetalhesAcordo")
			->setLabel(__("Detalhes do Acordo"))
			->setWidth(500);
			
		$this->build("p4a_fieldset","fsetNovasParcelas")
			->setLabel(__("Parcela(s)"))
			->setWidth(500)
			->setVisible(false);
		
		$this->build("p4a_field","fldPrimeiraDataVencimento")
			->setLabel(__("1º Vencimento"))
			->setWidth(70)
			->setType("date");
		
		
		$this->build("p4a_button","btnConfirmarParcelas")
			->setLabel(__("Confirmar Parcelas"))
			->implement("onclick",$this,"confirmarParcelas");
		
		$this->fsetDetalhesAcordo->anchor($this->fields->cd_acordo)
								->anchor($this->fields->cd_unidade)
								->anchor($this->fields->vl_acordo)
								//->anchor($this->fields->vl_correcao)
								->anchor($this->fields->dt_acordo)
								->anchor($this->fields->ds_acordo)
								->anchor($this->fields->qt_parcelas)
								//->anchor($this->fields->dt_ult_vct)
								//->anchor($this->fields->st_acordo)
								//->anchor($this->fields->tp_cobranca)
								->anchorRight($this->fldPrimeiraDataVencimento)
								->anchor($this->btnConfirmarParcelas);
		
		$this->setFieldsProperties();

		$this->frm->anchorCenter($this->fsetParcelasAtraso);
		$this->frm->anchorCenter($this->fsetDetalhesAcordo);
		$this->frm->anchorCenter($this->fsetNovasParcelas);
		
		$this->display("main",$this->frm);
		
		$this->display("menu",p4a::singleton()->menu);
		
		
		}
		
	function setFieldsProperties()
		{
		$fields = $this->fields;

		$fields->cd_acordo->setLabel(__("Código do Acordo"))
						->setWidth(50)
						->enable(false);
		
		$fields->cd_unidade->setLabel(__("Unidade"))
							->setWidth(50)
							->enable(false);
		
		$fields->dt_acordo->setLabel(__("Dt. do Acordo"))
						->setValue(date('d/m/Y'))	
						->setWidth(80)
						->enable(false);
		
		$fields->vl_acordo->setLabel(__("Vlr. Total Acordo"))
						->setWidth(80)
						->enable(false);
		
		$fields->ds_acordo->setLabel(__("Descrição"))
						//->setType("textarea")
						->setWidth(370);
		
		$fields->qt_parcelas->setLabel(__("Qtde. Parcelas"))
							->setWidth(30);
	
		/* $fields->vl_correcao->setLabel(__("Vlr. Juros"))
						->enable(false); */
		
		$fields->st_acordo->setLabel(__("St. Acordo"));
		}
		
	function calculaValorTotal()
		{
		$vlrSoma = 0;	
			
		foreach($this->arrParcelas as $dadosParcelas)
			{
			$cdBoletoMes = $dadosParcelas["cd_boleto_mes"];
			$vlrTotal = $dadosParcelas["vlr_total"];
			
			$nmBoletoMes = "fldParcela_".$cdBoletoMes;
			
			if($this->$nmBoletoMes->getNewValue() == '1')
				{
				$vlrSoma += $vlrTotal;
				}
			else
				{
				$vlrSoma == 0;
				}	
			}	
		$this->fields->vl_acordo->setNewValue($vlrSoma);
		
		}	
		
	function confirmarParcelas()
		{	
		if($this->fields->qt_parcelas->getNewValue() == '')
			{
			$this->error("Informe a quantidade parcelas do Acordo");
			return false;
			}
		
		if($this->fldPrimeiraDataVencimento->getNewValue() == '')
			{
			$this->info("Informe a primeira data de vencimento");
			return false;
			}
		
		$this->fsetNovasParcelas->clean();
		$this->fsetNovasParcelas->setVisible(true);
			
		$dataPrimeiraParcela = array();
			
		$dataPrimeiraParcela = explode("/", $this->fldPrimeiraDataVencimento->getNewValue());
		
		$this->build("p4a_button","btnConfirmarVlrParcelas")
			->setLabel(__("Confirmar"))
			->implement("onClick",$this,"efetuarAcordo");
		
		$qtde_parcelas = 0;
		$qtde_parcelas = $this->fields->qt_parcelas->getNewValue();	
		
		for($a = 0; $a < $qtde_parcelas; $a++)
			{				
			$nmDtVencimento = "fldQtdeParcela_".$a;
			$nmVlrParcela = "fldQtdeParcela_".$a;
			
			$this->build("p4a_field",$nmDtVencimento)
				->setLabel(__("<b>".($a + 1)."ª Parcela</b> -  Dt. Vencimento"))
				->setType("date")
				->setWidth(80)
				->label->setWidth(150);
			
			$this->fsetNovasParcelas->anchor($this->$nmDtVencimento);
			
			$dataPrimeiraParcelaacrescida = acrescentarMesesDatas($dataPrimeiraParcela[0],$dataPrimeiraParcela[1],$dataPrimeiraParcela[2],$a);
			
			$this->$nmDtVencimento->setNewValue(date("d/m/Y",strtotime($dataPrimeiraParcelaacrescida)));
			
			/*
			Necessário implementar - divisão das parcelas manualmente 
			$this->build("p4a_field",$nmVlrParcela)
				->setLabel(__("Valor"))
				->setProperty("dir","rtl")
				->setWidth(80); 
			
			$this->fsetNovasParcelas->anchor($this->$nmVlrParcela); */
			}

		$this->fsetNovasParcelas->anchor($this->btnConfirmarVlrParcelas);	
		}	

	function efetuarAcordo()
		{	
			$qtde_parcelas = $this->fields->qt_parcelas->getNewValue();
			
			for($a = 0; $a < $qtde_parcelas; $a++)
			{
				$nmDtVencimento = "fldQtdeParcela_".$a;
				
				$this->info($this->$nmDtVencimento->getNewValue());
			}
			
			
		//Insert com os dados do Acordo	
		try
			{
			P4A_DB::singleton()->beginTransaction();
			p4a_db::singleton()->query("INSERT INTO
												hist_acordo
														(cd_acordo,
														cd_unidade,
														dt_acordo,
														ds_acordo,
														vl_acordo,
														qt_parcelas,
														st_acordo)
											 VALUES
														('".$this->fields->cd_acordo->getNewValue()."',
														 '".$this->fields->cd_unidade->getNewValue()."',
														 '".date("Y-m-d")."',
														 '".$this->fields->ds_acordo->getNewValue()."',
														 '".$this->fields->vl_acordo->getNewValue()."',
														 '".$this->fields->qt_parcelas->getNewValue()."',
														 '".'1'."')
														");
			P4A_DB::singleton()->commit();
			}
		catch (Exception $e)
			{
			P4A_DB::singleton()->rollback();
		
			$this->error(__("Erro: ".$e->getCode()." - ".$e->getMessage()));
			}
			
		
		$qtde_parcelas = $this->fields->qt_parcelas->getNewValue();
		for($a = 0; $a < $qtde_parcelas; $a++)
			{
			$nmDtVencimento = "fldQtdeParcela_".$a;
			$vl_principal = $this->fields->vl_acordo->getNewValue();
			$vl_parcela = ($vl_principal/$qtde_parcelas);
			//Insert com os dados das parcelas do acordo
			try
				{
				P4A_DB::singleton()->beginTransaction();
				p4a_db::singleton()->query("INSERT INTO
													hist_parcela_acordo
															(cd_acordo,
															nr_parcela,
															st_parcela,
															dt_vcto,
															vl_parcela,
															vl_principal)
											 VALUES
															('".$this->fields->cd_acordo->getNewValue()."',
															 '".$a."',
															 '".'0'."',
															 '".$this->$nmDtVencimento->getNewValue()."',
															 '".$vl_parcela."',
															 '".$vl_principal."')
															");
					
				P4A_DB::singleton()->commit();
				}
			catch (Exception $e)
				{
				P4A_DB::singleton()->rollback();
			
				$this->error(__("Erro: ".$e->getCode()." - ".$e->getMessage()));
				}	
			}
		}	
		
	function main()
		{
		$this->fields->cd_acordo->setNewValue($this->fields->cd_unidade->getNewValue().date("m").date("Y"));
		$this->fields->dt_acordo->setValue(date("d/m/Y"));	
			
		parent::main();
		}		
	
	}