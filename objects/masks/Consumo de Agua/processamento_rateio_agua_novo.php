<?php
class processamento_rateio_agua_novo extends satecmax_mask
	{
	var $nmCampoQtde;

	private $arrUnidades = array();

	private $stSalvoSemPendencias = false;
	
	public $totalUnidadesRateio  = 0;

	function __construct()
		{
		parent::__construct();

		$this->build("P4a_frame","frm")
			->setWidth(1024);

		$this->setSource(condgest::singleton()->masks->processamento_conta_agua->getSource());

		if ( $this->fields->st_processamento->getValue() == 1)
			{
			$this->setStatusMode();
			}

		$mes_ano_referencia = p4a::singleton()->masks->processamento_conta_agua->fields->mes_ano_referencia->getvalue();
		
		$this->setTitle(__("Lançamento indivídual - mês/ano referência ").$mes_ano_referencia);

		$this->montaCamposEdicaoSource();
			
		$this->display("main",$this->frm);
		}

	function main()
		{		
		$this->btn_processar->enable($this->stSalvoSemPendencias and !$this->fields->st_processamento->getValue());

		$this->btn_salvar->enable(!$this->stSalvoSemPendencias and !$this->fields->st_processamento->getValue());
		
		parent::main();
		}	

	function montaCamposEdicaoSource()
		{
		$mes_ano_referencia = p4a::singleton()->masks->processamento_conta_agua->fields->mes_ano_referencia->getvalue();
		$valor_taxa_consumo = p4a::singleton()->masks->processamento_conta_agua->fields->vlr_taxa_consumo->getValue();

		list($mes,$ano) = desmontarMesAnoReferencia($mes_ano_referencia);

		$mes_ano_referencia_anterior = subtrairMesAnoRef('1', $mes, $ano, 1);

		$cod_leitura_agua_mes_anterior = p4a_db::singleton()->fetchOne("select cd_leitura_agua_mes from tbl_leitura_agua_mes where mes_ano_referencia = '{$mes_ano_referencia_anterior}'");

		$this->arrUnidades = p4a_db::singleton()->fetchAll("select cd_unidade from tbl_unidades where st_unidade = 1 and st_hidrometro = 1 order by cd_unidade");

		$cd_leitura_agua_mes = $this->fields->cd_leitura_agua_mes->getValue();

		foreach($this->arrUnidades as $dadosUnidades)
			{
			$cdUnidade = $dadosUnidades["cd_unidade"];	
			$nmCampoLeituraAnterior_1 = "fldQtdeUnidadeAnterior_".$cdUnidade;
			$nmCampoLeituraAtual_1 = "fldQtdeUnidadeAtual_".$cdUnidade;
			$nmCampoLeituraAnterior_2 = "fldQtdeUnidadeAnterior2_".$cdUnidade;
			$nmCampoLeituraAtual_2 = "fldQtdeUnidadeAtual2_".$cdUnidade;
			$vlr_individual_energia = "fldVlrEnergia_".$cdUnidade;
			$vlr_individual_agua = "fldVlrAgua_".$cdUnidade;
			$vlr_taxa = "fldVlrTaxa".$cdUnidade;
			$nmCampoQtde = "fldQtdeUnidade_".$cdUnidade;
			$nmCampoValor = "fldValorUnidade_".$cdUnidade;

			$this->build("p4a_field",$nmCampoLeituraAnterior_1)
				->setLabel(__("Unidade: ".$dadosUnidades["cd_unidade"]." - Inicial:"))
				->setWidth(50)
				->enable(false)
				->setProperty("dir","rtl")
				->label->setWidth(130);
				
			if($cod_leitura_agua_mes_anterior > 0)
				{
				//Obter a leitura inicial do hidrômetro primário
				$leituraInicial_1 = p4a_db::singleton()->fetchOne("select
																			leitura_final_1
																	 from
																			tbl_leitura_agua_mes_individual
																	where
																			cd_leitura_agua_mes = {$cod_leitura_agua_mes_anterior}
																				and cd_unidade = {$cdUnidade}");
													
				$leituraInicial_2 = p4a_db::singleton()->fetchOne("select
																			leitura_final_2
	 																 from
																			tbl_leitura_agua_mes_individual
																	where
																			cd_leitura_agua_mes = {$cod_leitura_agua_mes_anterior}
																				and cd_unidade = {$cdUnidade}");

				if ($leituraInicial_2 > 0)
					{
					$this->$nmCampoLeituraAnterior_1->setNewValue($leituraInicial_2);
					}
				else
					{
					$this->$nmCampoLeituraAnterior_1->setNewValue($leituraInicial_1);
					}
				}

			$this->$nmCampoLeituraAnterior_1->enable($this->$nmCampoLeituraAnterior_1->getNewValue()=="");

			$this->build("p4a_field",$nmCampoLeituraAtual_1)
				->setLabel(__("Final"))
				->setWidth(50)
				->label->setWidth(40);
				
			$this->build("p4a_field",$vlr_individual_agua)
				->setWidth(50)
				->setProperty("dir","rtl")
				->enable(false);
				
			$this->build("p4a_field",$vlr_taxa)
				->setLabel(__("Taxa: "))
				->enable(false)
				->setWidth(50)
				->setProperty("dir","rtl")
				->label->setWidth(40);

			$this->$vlr_taxa->setNewValue(number_format($valor_taxa_consumo,2,",","."));
		
			$this->build("p4a_field",$nmCampoLeituraAnterior_2)
				->setLabel(__("Inicial"))
				->setWidth(50)
				->setNewValue(0)
				->enable(false)
				->setProperty("dir","rtl")
				->label->setWidth(40);
				
			$this->build("p4a_field",$nmCampoLeituraAtual_2)
				->setLabel(__("Final	"))
				->setWidth(50)
				->label->setWidth(40)
				->setProperty("dir","rtl")
				->implement("onBlur",$this,"calcularConsumoTotal");
					
			$this->build("p4a_field",$nmCampoQtde)
				->setLabel("Consumo")
				->setWidth(50)
				->enable(false)
				->setProperty("dir","rtl")
				->label->setWidth(60);
			
			$this->build("p4a_field",$nmCampoValor)
				->setLabel("Valor à Pagar")
				->setWidth(70)
				->enable(false)
				->setProperty("dir","rtl");
							
			$this->build("p4a_button","btn_salvar")
				->setIcon("actions/document-save")
				->setLabel(__("Salvar Lançamentos"),true)
				->implement("onClick",$this,"calculaValorTotal");
				
			$this->build("p4a_button","btn_rateio_perdas")
				->setIcon("actions/document-save")
				->setLabel(__("Processar Rateior de Perdas"),true)
				->implement("onClick",$this,"processarRateioPerda");	

			$this->build("p4a_button","btn_processar")
				->setIcon("actions/process-accept-icon")
				->setLabel(__("Processar Lançamentos"),true)
				->implement("onClick",$this,"salvarLancamentos")
				->enable(false);

			// trazer possiveis valores ja salvos...
			$arrDadosRegistroSalvo = P4A_DB::singleton()->fetchRow("select
																			a.*
																	  from
																			tbl_leitura_agua_mes_individual a, tbl_unidades b
																	 where
																			a.cd_unidade = b.cd_unidade
																				and b.st_hidrometro = 1
																				and a.cd_leitura_agua_mes = '{$cd_leitura_agua_mes}'
																				and a.cd_unidade = '{$cdUnidade}'");
					
			if ($arrDadosRegistroSalvo["cd_unidade"] <> "" )
				{
				$this->$nmCampoLeituraAnterior_1->setNewValue($arrDadosRegistroSalvo["leitura_inicial_1"]);
				$this->$nmCampoLeituraAtual_1->setNewValue($arrDadosRegistroSalvo["leitura_final_1"]);
				$this->$nmCampoLeituraAnterior_2->setNewValue($arrDadosRegistroSalvo["leitura_inicial_2"]);
				$this->$nmCampoLeituraAtual_2->setNewValue($arrDadosRegistroSalvo["leitura_final_2"]);
				$this->$nmCampoQtde->setNewValue($arrDadosRegistroSalvo["total_consumido"]);
				$this->$vlr_taxa->setNewValue(number_format($arrDadosRegistroSalvo["vlr_taxa_consumo"],2,",","."));
				$this->$nmCampoValor->setNewValue(number_format($arrDadosRegistroSalvo["vlr_total"],2,",","."));
				}


			$this->frm->anchor($this->$nmCampoLeituraAnterior_1)
					->anchorLeft($this->$nmCampoLeituraAtual_1)
					->anchorLeft($this->$nmCampoLeituraAnterior_2)
					->anchorLeft($this->$nmCampoLeituraAtual_2)
					->anchorLeft($this->$nmCampoQtde)
					->anchorLeft($this->$vlr_taxa)
					->anchorLeft($this->$nmCampoValor);
			}
			
		$this->frm->anchor($this->btn_salvar);
		$this->frm->anchorLeft($this->btn_rateio_perdas);
		$this->frm->anchorLeft($this->btn_processar);
		}

	function calcularConsumoTotal()
		{
		foreach($this->arrUnidades as $dadosUnidades)
			{
			$cdUnidade = $dadosUnidades["cd_unidade"];
			$nmCampoLeituraAnterior_1 = "fldQtdeUnidadeAnterior_".$cdUnidade;
			$nmCampoLeituraAtual_1 = "fldQtdeUnidadeAtual_".$cdUnidade;
			$nmCampoLeituraAnterior_2 = "fldQtdeUnidadeAnterior2_".$cdUnidade;
			$nmCampoLeituraAtual_2 = "fldQtdeUnidadeAtual2_".$cdUnidade;
			$nmCampoQtde = "fldQtdeUnidade_".$cdUnidade;
			
			$leitura_anterior_1 = $this->$nmCampoLeituraAnterior_1->getNewValue();
			$leitura_atual_1 = $this->$nmCampoLeituraAtual_1->getNewValue();
			$leitura_anterior_2 = $this->$nmCampoLeituraAnterior_2->getNewValue();
			$leitura_atual_2 = $this->$nmCampoLeituraAtual_2->getNewValue();
			$this->$nmCampoQtde->setNewValue(($leitura_atual_1 - $leitura_anterior_1) + ($leitura_atual_2 - $leitura_anterior_2));
			}
		}
	
	
	function calculaValorTotal()
		{
		$this->calcularConsumoTotal();
		$valor_total_agua = p4a::singleton()->masks->processamento_conta_agua->fields->vlr_consumido->getValue();
		$valor_total_energia = p4a::singleton()->masks->processamento_conta_agua->fields->vlr_adicionais->getValue();
		$valor_taxa_consumo = p4a::singleton()->masks->processamento_conta_agua->fields->vlr_taxa_consumo->getValue();

		$qtdeTotal = 0;

		foreach($this->arrUnidades as $dadosUnidades)
			{
			$cdUnidade = $dadosUnidades["cd_unidade"];
			$nmCampoQtde = "fldQtdeUnidade_".$cdUnidade;
			$nmCampoValor = "fldValorUnidade_".$cdUnidade;
			$qtdeTotal += $this->$nmCampoQtde->getNewValue();
			}

		$valor_total = 0;
								
		foreach($this->arrUnidades as $dadosUnidades)
			{
			$cdUnidade = $dadosUnidades["cd_unidade"];
			$nmCampoQtde = "fldQtdeUnidade_".$cdUnidade;
			$nmCampoValor = "fldValorUnidade_".$cdUnidade;
			$nmCampoLeituraAtual_1 = "fldQtdeUnidadeAtual_".$cdUnidade;
			$vlr_individual_agua = "fldVlrAgua_".$cdUnidade;
			$vlr_taxa = "fldVlrTaxa".$cdUnidade;
			
			$consumoIndividual = $this->$nmCampoQtde->getNewValue();
			$vlrConsumoIndividual = 0;
			
			$vlrConsumoIndividual = $this->processarTabelaProgressiva($consumoIndividual);
			
			$valor_total_energia_acrec = $valor_total_energia;
			$valor_taxa_consumo_acres = $valor_taxa_consumo ;
			$valor_total = $vlrConsumoIndividual + $valor_taxa_consumo_acres;
				
			$this->$vlr_individual_agua->setNewValue($vlrConsumoIndividual);
			$this->$vlr_taxa->setNewValue(number_format($valor_taxa_consumo,2,",","."));
			$this->$nmCampoValor->setNewValue(number_format($valor_total,2,",","."));
			}
		$this->salvarLancamentos(false);
		$this->processarRateioEnergia();
		$this->processarRateioPerda();
		}

	function processarTabelaProgressiva($consumoIndividual)
		{			
		$faixaMinima = 10;
		$faixaMaxima = 50;
		$tarifaFaixa1 = condgest::singleton()->getParametro("TRF_TBL_PROGRESSIVA_FAIXA_1");
		$tarifaFaixa2 = condgest::singleton()->getParametro("TRF_TBL_PROGRESSIVA_FAIXA_2");
		$tarifaFaixa3 = condgest::singleton()->getParametro("TRF_TBL_PROGRESSIVA_FAIXA_3");
		$tarifaFaixa4 = condgest::singleton()->getParametro("TRF_TBL_PROGRESSIVA_FAIXA_4");
		$vlrConsumoIndividual = 0;
		
		if ($consumoIndividual <= $faixaMinima)
			{
			$vlrConsumoIndividual = $tarifaFaixa1;
			}
		if($consumoIndividual > 10 and $consumoIndividual <= 20)
			{
			$vlrConsumoIndividual = $tarifaFaixa1 + $tarifaFaixa2 * ($consumoIndividual - 10);
			}
		if($consumoIndividual > 20 and $consumoIndividual <= 50)
			{
			$vlrConsumoIndividual = $tarifaFaixa1 + $tarifaFaixa2 * 10 + $tarifaFaixa3 * ($consumoIndividual - 20);
			}
		if($consumoIndividual > 50)
			{
			$vlrConsumoIndividual = $tarifaFaixa1 + $tarifaFaixa2 * 10 + $tarifaFaixa3 * 20 + $tarifaFaixa4 * ($consumoIndividual - 50);
			}

		return $vlrConsumoIndividual;	
		}	
	
	function processarRateioEnergia()
	{
		$qtdeUnidades = P4A_DB::singleton()->fetchOne("select count(*) from tbl_unidades where st_unidade = 1 and st_hidrometro = 1");
		
		$vlrContaEnergia = p4a::singleton()->masks->processamento_conta_agua->fields->vlr_adicionais->getvalue();
		
		$vlrRateioEnergia = $vlrContaEnergia / $qtdeUnidades;
		
		$this->salvarLancamentosCompl($vlrRateioEnergia, 1);			
	}	
		
	function processarRateioPerda()
	{					
		$vlrPerdas = 0;
		$somaVlrAgua = 0;		
		
		$qtdeUnidades = P4A_DB::singleton()->fetchOne("select count(*) from tbl_unidades where st_unidade = 1 and st_hidrometro = 1");
		
		$cd_leitura_agua_mes = p4a::singleton()->masks->processamento_conta_agua->fields->cd_leitura_agua_mes->getValue();
		$vlrTotalConsumido = P4A_DB::singleton()->fetchOne("select sum(round(vlr_agua,2)) from tbl_leitura_agua_mes_individual where cd_leitura_agua_mes = '{$cd_leitura_agua_mes}'");
		
		foreach ( $this->arrUnidades as $dadosUnidades )
		{
			$cdUnidade = $dadosUnidades["cd_unidade"];
			$nmCampoValor = "fldValorUnidade_".$cdUnidade;
			
			$somaVlrAgua += $this->$nmCampoValor->getNewValue();
		}					
		
		$vlrContaAgua = $this->fields->vlr_consumido->getValue();		
		
		if($vlrTotalConsumido < $vlrContaAgua)
			{					
			$vlrPerdas = ($vlrContaAgua - $vlrTotalConsumido) / $qtdeUnidades;
			$this->info($vlrPerdas);
			}
		
		$this->salvarLancamentosCompl($vlrPerdas, 2);
	}	
		
	function salvarLancamentos($stProcessar=true)
		{
		$cd_leitura_agua_mes = p4a::singleton()->masks->processamento_conta_agua->fields->cd_leitura_agua_mes->getValue();
										
		if ( !$stProcessar )
			{
			try
				{
				p4a_db::singleton()->beginTransaction();
					
				P4A_DB::singleton()->query("delete from tbl_leitura_agua_mes_individual where cd_leitura_agua_mes = '{$cd_leitura_agua_mes}'");
					
				$qtdeUnidades = 0;
				$qtdeSalvas = 0;
				foreach($this->arrUnidades as $dadosUnidades)
					{
					// incrementa a quantidade de unidades...
					$qtdeUnidades ++;
					
					$cdUnidade = $dadosUnidades["cd_unidade"];
					$nmCampoLeituraAnterior_1 = "fldQtdeUnidadeAnterior_".$cdUnidade;
					$nmCampoLeituraAtual_1 = "fldQtdeUnidadeAtual_".$cdUnidade;
					$nmCampoLeituraAnterior_2 = "fldQtdeUnidadeAnterior2_".$cdUnidade;
					$nmCampoLeituraAtual_2 = "fldQtdeUnidadeAtual2_".$cdUnidade;
					$nmCampoQtde = "fldQtdeUnidade_".$cdUnidade;
					$nmCampoValor = "fldValorUnidade_".$cdUnidade;
					$vlr_individual_agua = "fldVlrAgua_".$cdUnidade;
					$vlr_taxa = "fldVlrTaxa".$cdUnidade;
						
					//validações...
						
					if ( $this->$nmCampoLeituraAtual_1->getNewValue() < $this->$nmCampoLeituraAnterior_1->getNewValue() )
						{
						throw new P4A_Exception(__("Quantidade da leitura atual da unidade ".$cdUnidade." tem que ser maior que a anterior. Favor verifique!"),-1);
						}

					if ( $this->$nmCampoLeituraAtual_1->getNewValue() >= $this->$nmCampoLeituraAnterior_1->getNewValue() and
					
						 $this->$nmCampoLeituraAtual_2->getNewValue() < $this->$nmCampoLeituraAtual_2->getNewValue()
						)
						{
						throw new P4A_Exception(__("Quantidade da leitura atual (2) da unidade ".$cdUnidade." tem que ser maior que a anterior. Favor verifique!"),-1);
						}
									
					if ( $this->$nmCampoLeituraAtual_1->getNewValue() <> "" )
						{
						$qtdeSalvas ++;
						
						$sql_salvar_lancamentos = ("insert into tbl_leitura_agua_mes_individual(
																	cd_unidade,
																	leitura_inicial_1,
																	leitura_final_1,
																	leitura_inicial_2,
																	leitura_final_2,
															 		total_consumido,
																	vlr_agua,
															 		vlr_taxa_consumo,
															 		vlr_total,
															 		st_leitura,
															 		cd_leitura_agua_mes)
														values
																	('".$cdUnidade."',
																	'".$this->$nmCampoLeituraAnterior_1->getNewValue()."',
																	'".$this->$nmCampoLeituraAtual_1->getNewValue()."',
																	'".$this->$nmCampoLeituraAnterior_2->getNewValue()."',
																	'".$this->$nmCampoLeituraAtual_2->getNewValue()."',
																	'".$this->$nmCampoQtde->getNewValue()."',
																	'".$this->$vlr_individual_agua->getNewValue()."',
																	'".str_replace(",",".",$this->$vlr_taxa->getNewValue())."',
																	'".str_replace(",", ".", $this->$nmCampoValor->getNewValue())."',
																	'1',
																	'".$cd_leitura_agua_mes."')
																	");
						
						//Atualiza o flag st_leitura após lançados as leituras individuais
						$sql_st_leitura = "update tbl_leitura_agua_mes set st_leitura = 1 where cd_leitura_agua_mes = ".$cd_leitura_agua_mes;
						
						p4a_db::singleton()->query($sql_salvar_lancamentos);
						p4a_db::singleton()->query($sql_st_leitura);
						}
					}
								
				if ($qtdeSalvas == $qtdeUnidades )
					{
					$this->stSalvoSemPendencias = true;								
					$this->btn_salvar->disable();
					}	
				P4A_DB::singleton()->commit();
				
				$this->info("Lançamentos inseridos com sucesso!");
				}
			catch (Exception $e)
				{
				P4A_DB::singleton()->rollback();

				$this->error(__(" Erro ao salvar! ".$e->getMessage()));
				}
			}
		else
			{
			// tratar se o valor total que será cobrado, é maior ou igual ao valor total previsto
			// somar os valores carregados...
			$valorTotalGeral = 0;
			$totalConsumido = 0;
			$diferencaConsumo = 0;
			
			foreach ( $this->arrUnidades as $dadosUnidades )
				{
				$cdUnidade = $dadosUnidades["cd_unidade"];
				$nmCampoValor = "fldValorUnidade_".$cdUnidade;
				$valorTotalGeral += str_replace(",", ".",$this->$nmCampoValor->getNewValue());
				}
				
			$totalConsumido = $this->fields->total_consumido->getValue();
			
			$valorPresumido = $this->fields->vlr_total_presumido->getValue();
					
			$diferenca = ( $valorPresumido - $valorTotalGeral );
					
			$diferencaPermitida = condgest::singleton()->getParametro("PROC_AGUA_DIFERENCA_VALOR");
					
			if ( $valorTotalGeral >=  $valorPresumido  or  ( ($diferenca) <= str_replace(",",".",$diferencaPermitida)  ) )
				{
				$sql_st_processamento = ("update tbl_leitura_agua_mes set st_processamento = '1' where cd_leitura_agua_mes= '".$cd_leitura_agua_mes."'");

				p4a_db::singleton()->query($sql_st_processamento);
			
				$this->info(__("Processamento efetuado com sucesso!"));

				$this->desabilitaEdicaoVisualizacao();
				}
			else
				{
				$this->error(__("Valor total ( R$ ".number_format($valorTotalGeral,2,",",".")." ) é menor que o valor presumido ( R$ ".number_format($valorPresumido,2,",",".")." ) para pagamento das contas! Diferença: ( R$ ".$diferenca." ) Permitido: ( R$ ".$diferencaPermitida." )"));
				}
			}
		}
		
	function salvarLancamentosCompl($vlrCompl, $tpLancamento)
		{
		$cd_leitura_agua_mes = p4a::singleton()->masks->processamento_conta_agua->fields->cd_leitura_agua_mes->getValue();
		
		try
			{
			p4a_db::singleton()->beginTransaction();
				
			if ($tpLancamento == 1)
				{					
				p4a_db::singleton()->query("update tbl_leitura_agua_mes_individual set vlr_energia = '".round($vlrCompl,2)."' where cd_leitura_agua_mes= '".$cd_leitura_agua_mes."'");
				}
			if($tpLancamento == 2) 
				{	
				p4a_db::singleton()->query("update tbl_leitura_agua_mes_individual set vlr_rateio_perda = '".round($vlrCompl,2)."' where cd_leitura_agua_mes= '".$cd_leitura_agua_mes."'");
				}
			P4A_DB::singleton()->commit();	
			
			$this->info("Lançamento complementar efetuado com sucesso!");
			}
		catch (Exception $e)
			{
			P4A_DB::singleton()->rollback();	
			$this->error(__(" Erro ao salvar! ".$e->getMessage()));
			}
		}	

	function desabilitaEdicaoVisualizacao()
		{
		$this->btn_salvar->setInvisible();
		$this->btn_processar->setInvisible();
		
		foreach ($this->arrUnidades as $dadosUnidades)
			{
			$cdUnidade = $dadosUnidades["cd_unidade"];	
			$nmCampoLeituraAtual_1 = "fldQtdeUnidadeAtual_".$cdUnidade;
			$nmCampoLeituraAnterior_1 = "fldQtdeUnidadeAnterior_".$cdUnidade;
			$nmCampoLeituraAnterior_2 = "fldQtdeUnidadeAnterior2_".$cdUnidade;
			$nmCampoLeituraAtual_2 = "fldQtdeUnidadeAtual2_".$cdUnidade;
			$vlr_individual_agua = "fldVlrAgua_".$cdUnidade;
			$vlr_taxa = "fldVlrTaxa".$cdUnidade;
			$nmCampoQtde = "fldQtdeUnidade_".$cdUnidade;
			$nmCampoValor = "fldValorUnidade_".$cdUnidade;
				
			$this->$nmCampoLeituraAtual_1->disable();
			$this->$nmCampoLeituraAnterior_1->disable();
			$this->$nmCampoLeituraAnterior_2->disable();
			$this->$nmCampoLeituraAtual_2->disable();
			$this->$vlr_individual_agua->disable();
			$this->$vlr_taxa->disable();
			$this->$nmCampoQtde->disable();
			$this->$nmCampoValor->disable();
			}
		}	
	}