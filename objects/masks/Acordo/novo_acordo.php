<?php
class novo_acordo extends satecmax_mask
{
	private $arrDados;

	function __construct()
	{
		parent::__construct();

		$this->build("p4a_frame","frm")
		->setWidth(850);
			
		$this->setSource(condgest::singleton()->masks->unidades_inadimplentes->getSource());

		$this->setTitle(__(".: Boletos em aberto da unidade ").$this->fields->unidade->getValue() ." :.");

		$this->build("satecmax_db_source","srcPagamentosAtraso")
			->setTable("tbl_boleto_mes_unidade")
			->setWhere("cd_unidade = {$this->fields->unidade->getValue()} and st_baixado = 0 and date_add(dt_vencimento,interval 1 day) < curdate()")
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
                    																tbl_inpc y
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
                    																tbl_inpc y
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

		$arrSrcBoletosEmAberto1 = $this->srcPagamentosAtraso->getAll();
		
		$arrSrcBoletosEmAberto2 = array();
		
		$vlrTotalHistBoleto = 0;
		$vlrTotalMulta = 0;
		$vlrTotalJuros = 0;
		$vlrTotalCorrecao = 0;
		$vlrTotalItensBoleto = 0;
		
		foreach($arrSrcBoletosEmAberto1 as $arrBoletoItem)
			{
			$vlrTotalHistBoleto += $arrBoletoItem["vlr_boleto"];
			$vlrTotalMulta += $arrBoletoItem["vlr_multa"];
			$vlrTotalJuros += $arrBoletoItem["vlr_mora"];
			$vlrTotalCorrecao += $arrBoletoItem["vlr_atualizacao_monetaria"];
			$vlrTotalItensBoleto += $arrBoletoItem["vlr_total"];
			$arrSrcBoletosEmAberto2[] = $arrBoletoItem;
			}
		$arrSrcBoletosEmAberto2[] = array("nosso_numero"=>"TOTAL","dt_vencimento"=>"","vlr_boleto"=>$vlrTotalHistBoleto,"vlr_multa"=>$vlrTotalMulta,"vlr_mora"=>$vlrTotalJuros,"vlr_atualizacao_monetaria"=>$vlrTotalCorrecao,"vlr_total"=>$vlrTotalItensBoleto);
		
		$this->build("p4a_array_source","srcArr_boleto_aberto")
			->Load($arrSrcBoletosEmAberto2);

		$this->srcArr_boleto_aberto->fields->dt_vencimento->setType("date");
		$this->srcArr_boleto_aberto->fields->vlr_boleto->setType("decimal");
		$this->srcArr_boleto_aberto->fields->vlr_multa->setType("decimal");
		$this->srcArr_boleto_aberto->fields->vlr_mora->setType("decimal");
		$this->srcArr_boleto_aberto->fields->vlr_atualizacao_monetaria->setType("decimal");
		$this->srcArr_boleto_aberto->fields->vlr_total->setType("decimal");
		
		$this->build("p4a_table","tbl_parcela_atraso")
			->setSource($this->srcArr_boleto_aberto)
			->setWidth(800);

		$this->tbl_parcela_atraso->setVisibleCols(array("nosso_numero","dt_vencimento","vlr_boleto","vlr_multa","vlr_mora","vlr_atualizacao_monetaria","vlr_total"));

		$this->tbl_parcela_atraso->cols->nosso_numero->setLabel(__("Nosso Número"));
		$this->tbl_parcela_atraso->cols->dt_vencimento->setLabel(__("Data de Vencimento"));
		$this->tbl_parcela_atraso->cols->vlr_boleto->setLabel(__("Valor Histórico (R$)")); 
		$this->tbl_parcela_atraso->cols->vlr_multa->setLabel(__("Valor Multa (R$)"));
		$this->tbl_parcela_atraso->cols->vlr_mora->setLabel(__("Valor Juros (R$)"));
		$this->tbl_parcela_atraso->cols->vlr_atualizacao_monetaria->setLabel(__("Valor Correção (R$)"));
		$this->tbl_parcela_atraso->cols->vlr_total->setLabel(__("Valor Atualizado (R$)"));
		
		$this->build("satecmax_full_toolbar","toolbar")
			->setMask($this);

		$this->build("p4a_button","btnEfetuarAcordoAdmin")
			->setLabel(__("Efetuar Acordo Administrativo"),true)
			->setIcon("status/folder-open")
			->implement("onClick",$this,"efetuarAcordoAdministrativo");
		
		$this->build("p4a_button","btnEfetuarAcordoExtraJudicial")
			->setLabel(__("Efetuar Acordo Extrajudicial"),true)
			->setIcon("actions/auction-hammer-icon")
			->implement("onClick",$this,"efetuarAcordoExtrajudicial");

		$this->frm->anchorCenter($this->tbl_parcela_atraso);
		$this->frm->anchorLeft($this->btnEfetuarAcordoAdmin);
		$this->frm->anchorRight($this->btnEfetuarAcordoExtraJudicial);

		$this->display("main",$this->frm);

		$this->display("menu",p4a::singleton()->menu);
		}

	function efetuarAcordoAdministrativo()
		{
		$this->btnEfetuarAcordoAdmin->setVisible(false);
		$this->btnEfetuarAcordoExtraJudicial->setVisible(false);
				
		$this->build("p4a_fieldset","fsetAcordo")
			->setLabel(__("Dados do Acordo"))
			->setWidth(700);
		
		$this->build("p4a_field","fldDescontoVlrTotalAcordo")
			->setLabel(__("Valor de Desconto"))
			->setWidth(100)
			->setProperty("dir","rtl")
			->implement("onBlur",$this,"calculaValorTotal");
			
		$this->build("p4a_field","fldVlrTotalAcordo")
			->setLabel(__("Valor Total"))
			->setWidth(100)
			->setProperty("dir","rtl")
			->Enable(false);		
		
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
			->setLabel(__("Salvar Acordo Administrativo"),true)
			->setIcon("actions/document-save")
			->implement("onClick",$this,"salvarAcordo");
					
		$this->fsetAcordo->anchor($this->fldDescontoVlrTotalAcordo)
						->anchor($this->fldVlrTotalAcordo)
						->anchor($this->fldDsAcordo)
						->anchor($this->fldDtVencimentoParcela)
						->anchor($this->fldQtdeParcelas)
						->anchor($this->btnSalvarAcordo);
			
		$this->frm->anchorCenter($this->fsetAcordo);
		}
		
	function efetuarAcordoExtrajudicial()
		{
		$this->info(__("Em implementação!!!"));
		}

	function calculaValorTotal()
		{	
		$arrSrcBoletosEmAberto1 = $this->srcPagamentosAtraso->getAll();			
		
		$vlrTotalItensBoleto = 0;
			
		foreach($arrSrcBoletosEmAberto1 as $arrBoletoItem)
			{
			$vlrTotalItensBoleto += $arrBoletoItem["vlr_total"];
			}					
		$this->fldVlrTotalAcordo->setNewValue($vlrTotalItensBoleto - $this->fldDescontoVlrTotalAcordo->getNewValue());
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
				$sqlNovoAcordo = "INSERT INTO acordos
												(cd_unidade,
												dt_acordo,
												vlr_acordo,
												qtde_parcelas,
												dsc_acordo,
												st_acordo)
								       VALUES
												(?,
												 ?,
												 ?,
												 ?,
												 ?,
												 ?)";

				$dataAtual = formatarDataBanco(date("d/m/Y"));
				$vlrTotalAcordo = $this->fldVlrTotalAcordo->getNewValue();
				$qtdeParcelas = $this->fldQtdeParcelas->getNewValue();
				$dsAcordo = $this->fldDsAcordo->getNewValue();
				$stAcordo = 0;
				
				P4A_DB::singleton()->query($sqlNovoAcordo,array($dataAtual,$vlrTotalAcordo,$qtdeParcelas,$dsAcordo,$stAcordo));
				
				
				P4A_DB::singleton()->commit();
				}
			catch (Exception $e)
			{
				P4A_DB::singleton()->rollback();
				$this->error(__("Erro: ".$e->getCode()." - ".$e->getMessage()));
			}
			//$this->salvarAcordoDetalhes();
		}	
	
	function main()
		{
		parent::main();
		}
	}