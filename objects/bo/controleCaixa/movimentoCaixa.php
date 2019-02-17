<?php
class movimentoCaixa
	{
	private $cdCaixa = null;
	
	/**
	 * 
	 * Enter description here ...
	 * @var P4A_DB_Source
	 */
	private $objDbSource;
	
	
	/**
	 * 
	 * Enter description here ...
	 * @var P4A_DB_Source
	 */
	private $objDbSourceCaixa;
	
	
	function __construct($cdCaixa)
		{
		$this->cdCaixa = $cdCaixa;
		
		$this->objDbSource = new P4A_DB_Source("movimentoCaixa");
		
		$this->objDbSource->setTable("caixa_movimento");
		
		$this->objDbSource->setPk("cd_caixa_movimento");
		
		$this->objDbSource->setWhere("1=0");
		
		$this->objDbSource->Load();
		

		$this->objDbSourceCaixa = new P4A_DB_Source("MovimentoCaixa2");
		
		$this->objDbSourceCaixa->setTable("caixa");
		
		$this->objDbSourceCaixa->setPk("cd_caixa");
		
		$this->objDbSourceCaixa->setWhere("cd_caixa = '{$this->cdCaixa}'");
		
		$this->objDbSourceCaixa->Load();
		
		$this->objDbSourceCaixa->firstRow();
		
		}
		
	function getSaldoInicialCaixa()
		{
		return $this->objDbSourceCaixa->fields->vlr_saldo_inicial->getValue();
		}
		
	function getDataInicialCaixa()
		{
		return $this->objDbSourceCaixa->fields->dt_saldo_inicial->getValue();
		}
	
	/**
	 * 
	 * Enter description here ...
	 * return string;
	 */
	function getSaldoCaixa($dtSaldo = "" )
		{
		// pegar o valor do saldo inicial
		
		$vlrSaldoInicial = $this->getSaldoInicialCaixa();
		
		$dtSaldoIncial = $this->objDbSourceCaixa->fields->dt_saldo_inicial->getValue();
		
		if ( $dtSaldo == "" )
			{
			$dtSaldo = date("Y-m-d");
			}
		
		$vlrSomaEntradas = P4A_DB::singleton()->fetchOne("select sum(vlr_movimento) from caixa_movimento where cd_caixa = ? and ( dt_movimento between  ? and ? ) and tp_movimento = 'E'", array($this->cdCaixa,$dtSaldoIncial, $dtSaldo));
		
		$vlrSomaSaidas = P4A_DB::singleton()->fetchOne("select sum(vlr_movimento) from caixa_movimento where cd_caixa = ? and  ( dt_movimento between ? and ? ) and tp_movimento = 'S'", array($this->cdCaixa, $dtSaldoIncial, $dtSaldo));
		
		$vlrSaldoCaixa = $vlrSaldoInicial+($vlrSomaEntradas-$vlrSomaSaidas);
		
		return $vlrSaldoCaixa;
		}
		
	function newMovimentoEntrada(
								$dtMovimento,
								$vlrMovimento,
								$cdPessoa,
								$cdDocumentoReferencia,
								$tpDocumentoReferencia,
								$dsMovimento,
								$cdPlanoConta,
								$cdHistPadrao,
								$dsComplHistPadrao,
								$nrCaixaMovimentoOriTransf = null
								)
		{
			
		if ( 
			$dtMovimento == "" or 
			$vlrMovimento == "" or 
			$cdPessoa == "" or 
			$cdDocumentoReferencia == "" or 
			$tpDocumentoReferencia == "" or 
			$dsMovimento == "" or 
			$cdPlanoConta == "" or 
			$cdHistPadrao == "" 
			
			)
			{
				throw new P4A_Exception("Campos obrigatórios não preenchidos, favor verificar", -9999);
			}
		
		$this->objDbSource->newRow();
		
		$strDataMovimento = formatarDataBanco($dtMovimento);
		
		$vlrMovimento = formataValoresBanco($vlrMovimento);
		
		$this->objDbSource->fields->cd_caixa->setNewValue($this->cdCaixa);
		$this->objDbSource->fields->dt_movimento->setNewValue($strDataMovimento);
		$this->objDbSource->fields->tp_movimento->setNewValue("E");
		$this->objDbSource->fields->vlr_movimento->setNewValue($vlrMovimento);
		$this->objDbSource->fields->cd_pessoa->setNewValue($cdPessoa);
		$this->objDbSource->fields->cd_documento_referencia->setNewValue($cdDocumentoReferencia);
		$this->objDbSource->fields->tp_documento_referencia->setNewValue($tpDocumentoReferencia);
		$this->objDbSource->fields->ds_movimento->setNewValue($dsMovimento);
		$this->objDbSource->fields->cd_plano_conta->setNewValue($cdPlanoConta);
		$this->objDbSource->fields->cd_hist_padrao->setNewValue($cdHistPadrao);
		$this->objDbSource->fields->ds_compl_hist_padrao->setNewValue($dsComplHistPadrao);
		$this->objDbSource->fields->cd_caixa_movimento_ori_transf->setNewValue($nrCaixaMovimentoOriTransf);
		$this->objDbSource->fields->cd_usuario_movimento->setNewValue(condgest::singleton()->user_login->fields->cd_usuario->getValue());
		$this->objDbSource->fields->dt_hr_movimento->setNewValue(date("Y-m-d H:i:s"));
		$this->objDbSource->fields->st_estorno->setNewValue(false);
		
		$this->objDbSource->saveRow();
		
		// retorna o numero de registro do movimento
		//p4a::singleton()->messageInfo(P4A_DB::singleton()->select()->getAdapter()->lastInsertId());
		
		return P4A_DB::singleton()->select()->getAdapter()->lastInsertId();
		}
	
	function newMovimentoSaida(
								$dtMovimento,
								$vlrMovimento,
								$cdPessoa,
								$cdDocumentoReferencia,
								$tpDocumentoReferencia,
								$dsMovimento,
								$cdPlanoConta,
								$cdHistPadrao,
								$dsComplHistPadrao,
								$nrCaixaMovimentoOriTransf = null
								)
		{

		$vlrMovimento = formataValoresBanco($vlrMovimento);
		
		if ( 
			$dtMovimento == "" or 
			$vlrMovimento == "" or 
			$cdPessoa == "" or 
			$cdDocumentoReferencia == "" or 
			$tpDocumentoReferencia == "" or 
			$dsMovimento == "" or 
			$cdPlanoConta == "" or 
			$cdHistPadrao == "" 
			
			)
			{
			throw new P4A_Exception("Campos obrigatórios não preenchidos, favor verificar", -9999);
			}

		$strDataMovimento = formatarDataBanco($dtMovimento);
			
		if ( $vlrMovimento > $this->getSaldoCaixa($strDataMovimento) )
			{
			throw new P4A_Exception("O valor de R$ ".formataValoresExibicao($vlrMovimento)." é maior que o disponivel no caixa que é de R$ ".formataValoresExibicao($this->getSaldoCaixa())."! Favor verificar", -9999);
			}
			
		$this->objDbSource->newRow();
		
		$this->objDbSource->fields->cd_caixa->setNewValue($this->cdCaixa);
		$this->objDbSource->fields->dt_movimento->setNewValue($strDataMovimento);
		$this->objDbSource->fields->tp_movimento->setNewValue("S");
		$this->objDbSource->fields->vlr_movimento->setNewValue($vlrMovimento);
		$this->objDbSource->fields->cd_pessoa->setNewValue($cdPessoa);
		$this->objDbSource->fields->cd_documento_referencia->setNewValue($cdDocumentoReferencia);
		$this->objDbSource->fields->tp_documento_referencia->setNewValue($tpDocumentoReferencia);
		$this->objDbSource->fields->ds_movimento->setNewValue($dsMovimento);
		$this->objDbSource->fields->cd_plano_conta->setNewValue($cdPlanoConta);
		$this->objDbSource->fields->cd_hist_padrao->setNewValue($cdHistPadrao);
		$this->objDbSource->fields->ds_compl_hist_padrao->setNewValue($dsComplHistPadrao);
		$this->objDbSource->fields->cd_caixa_movimento_ori_transf->setNewValue($nrCaixaMovimentoOriTransf);
		$this->objDbSource->fields->cd_usuario_movimento->setNewValue(condgest::singleton()->user_login->fields->cd_usuario->getValue());
		$this->objDbSource->fields->dt_hr_movimento->setNewValue(date("Y-m-d H:i:s"));
		$this->objDbSource->fields->st_estorno->setNewValue(false);
		
		$this->objDbSource->saveRow();

		// retorna o numero de registro do movimento
		//p4a::singleton()->messageInfo(P4A_DB::singleton()->select()->getAdapter()->lastInsertId());
		
		return P4A_DB::singleton()->select()->getAdapter()->lastInsertId();
		}
		
	function newMovimentoEstorno(
								$cdMovimentoCaixaEstornar
								)
		{
		// pegar o movimento a estonar...
		$this->objDbSource->setWhere("cd_movimento_caixa = ? and st_estorno = 0",array($cdMovimentoCaixaEstornar))
			->load()
			->firstRow();
			
		if ( ! $this->objDbSource->isNew()) // se encontrou algo, nao fica em posição de new...
			{
			// pegar os valores carregados no movimento original
			
			while ($field = $this->objDbSource->fields->nextItem())
				{
				$arrValoresMovimentoOriginal[$field->getName()] = $field->getValue();
				}
				
				
			// alterar o status do registro para estornado...
			
			$this->objDbSource->fields->st_estornado->setNewValue(true);
			
			$this->objDbSource->saveRow();

			// verificar se o movimento se trata de um movimento de transferencia
			if ( $this->objDbSource->fields->cd_caixa_movimento_ori_transf->getValue() > 0 )
				{
				// se for, estornar o registro de relacionamento...
				
				// pegar os dados para estornar...
				$cdCaixaoEstornoRegistroRelacionado = $this->getCaixaPorDocumento($this->objDbSource->fields->cd_caixa_movimento_ori_transf->getValue());
				
				$objDocumentoRelacionadoEstorno = new movimentoCaixa($cdCaixaoEstornoRegistroRelacionado);
				
				$objDocumentoRelacionadoEstorno->newMovimentoEstorno($this->objDbSource->fields->cd_caixa_movimento_ori_transf->getValue());
				}
			else
				{
				// verificar se não existe um documento relacionado a este para ver se ja foi estornado...
				if ( $cdCaixaoEstornoRegistroRelacionado2 = P4A_DB::singleton()->fetchOne("select cd_caixa_movimento from caixa_movimento where cd_caixa_movimento_ori_transf = ? ", array($cdMovimentoCaixaEstornar)) > 0 )
					{
					$objDocumentoRelacionadoEstorno2 = new movimentoCaixa($this->getCaixaPorDocumento($cdCaixaoEstornoRegistroRelacionado2));
					
					$objDocumentoRelacionadoEstorno2->newMovimentoEstorno($cdCaixaoEstornoRegistroRelacionado2);
					}
				}
			
			
			$this->objDbSource->setWhere(null);
			
			
			// criar um novo registro para fazer o registro inverso...
			$this->objDbSource->newRow();
			
			$this->objDbSource->fields->cd_caixa->setNewValue($this->cdCaixa);
			$this->objDbSource->fields->dt_movimento->setNewValue(date("Y-m-d"));
			$this->objDbSource->fields->tp_movimento->setNewValue($arrValoresMovimentoOriginal["tp_movimento"]=="S"?"E":"S");
			$this->objDbSource->fields->vlr_movimento->setNewValue($arrValoresMovimentoOriginal["vlr_movimento"]);
			$this->objDbSource->fields->cd_pessoa->setNewValue($arrValoresMovimentoOriginal["cd_pessoa"]);
			$this->objDbSource->fields->cd_documento_referencia->setNewValue($arrValoresMovimentoOriginal["cd_documento_referencia"]);
			$this->objDbSource->fields->tp_documento_referencia->setNewValue($arrValoresMovimentoOriginal["tp_documento_referencia"]);
			$this->objDbSource->fields->ds_movimento->setNewValue("ESTORNO: ".$arrValoresMovimentoOriginal["ds_movimento"]);
			$this->objDbSource->fields->cd_plano_conta->setNewValue($arrValoresMovimentoOriginal["cd_plano_conta"]);
			$this->objDbSource->fields->cd_hist_padrao->setNewValue($arrValoresMovimentoOriginal["cd_hist_padrao"]);
			$this->objDbSource->fields->ds_compl_hist_padrao->setNewValue($arrValoresMovimentoOriginal["ds_compl_hist_padrao"]);
			$this->objDbSource->fields->cd_usuario_movimento->setNewValue(condgest::singleton()->user_login->fields->cd_usuario->getValue());
			$this->objDbSource->fields->dt_hr_movimento->setNewValue(date("Y-m-d H:i:s"));
			$this->objDbSource->fields->st_estorno->setNewValue(false);
			
			$this->objDbSource->saveRow();
			
			}
			
			
		
		}
		
	function getMovimento($cdMovimento)
		{
		
		}
		
	function newMovimentoTransferencia($cdCaixaDestino, $dtMovimento, $vlrMovimento, $cdDocumentoReferencia )
		{			
		
		// criar objeto de movimento no caixa de Destino 
		$objMovimentoCaixaDestino = new movimentoCaixa($cdCaixaDestino);
		
		// carregar as configurações para a movimentação de transferencia
		$cdPessoa = condgest::singleton()->getParametro("CAIXA_MOVIMENTO_TRANSF_CD_PESSOA_DEFAULT");
		$tpDocumentoReferencia = condgest::singleton()->getParametro("CAIXA_MOVIMENTO_TRANSF_TP_MOVIMENTO_DEFAULT");
		$dsMovimento = condgest::singleton()->getParametro("CAIXA_MOVIMENTO_TRANSF_DS_MOVIMENTO_DEFAULT");
		$cdPlanoConta = condgest::singleton()->getParametro("CAIXA_MOVIMENTO_TRANSF_CD_PLANO_CONTA");
		$cdHistPadrao = condgest::singleton()->getParametro("CAIXA_MOVIMENTO_TRANSF_CD_HIST_PADRAO");
		$dsComplHistPadrao = condgest::singleton()->getParametro("CAIXA_MOVIMENTO_TRANSF_COMPL_HIST_PADRAO");
		
		// fazer a saida do caixa de origem 
		$nrMovimentoSaida = $this->newMovimentoSaida(
								$dtMovimento, 
								$vlrMovimento, 
								$cdPessoa, 
								$cdDocumentoReferencia, 
								$tpDocumentoReferencia, 
								$dsMovimento, 
								$cdPlanoConta, 
								$cdHistPadrao, 
								$dsComplHistPadrao
								);

		// fazer o movimento de entrada no caixa de destino
		$nrMovimentoEntrada = $objMovimentoCaixaDestino->newMovimentoEntrada(
													$dtMovimento, 
													$vlrMovimento, 
													$cdPessoa, 
													$cdDocumentoReferencia, 
													$tpDocumentoReferencia, 
													$dsMovimento, 
													$cdPlanoConta, 
													$cdHistPadrao, 
													$dsComplHistPadrao,
													$nrMovimentoSaida);
		}
		
	function getCaixaPorDocumento($cdCaixaMovimento)
		{
		return P4A_DB::singleton()->fetchOne("select cd_caixa from caixa_movimento where cd_caixa_movimento = ?", array($cdCaixaMovimento));
		}
		
	function montaObjSourceMovimentosCaixa($dtMovimentoIni, $dtMovimentoFim, $cdPessoa, $cdCategoria, $vlrMovimento)
		{
		// buscar o saldo anterior a data inicial...
		
		$vlrSaldoInicial = $this->getSaldoInicialCaixa();
		
		$dtSaldoInicial = $this->objDbSourceCaixa->fields->dt_saldo_inicial->getValue();
		
		$dtMovimentoInicialFormatadoBanco = formatarDataBanco($dtMovimentoIni);
		
		$dtMovimentoFinalFormatadoBanco = formatarDataBanco($dtMovimentoFim);
		
		$diaAnteriorAoInicial = date("Y-m-d",strtotime("-1 day",strtotime($dtMovimentoInicialFormatadoBanco)));
		
		$vlrSaldoCaixaDiaAnterior = $this->getSaldoCaixa($diaAnteriorAoInicial);
		
		// montar array com as linhas...
		$arrSourceListMovimentos[] = array(
											"cdMovimento"=>null,
											"dtMovimento"=>"",
											"dsMovimento"=>"Saldo Anterior",
											"vlrSaldoAnterior"=>"",
											"vlrEntrada"=>"",
											"vlrSaida"=>"",
											"vlrSaldoFinal"=>$vlrSaldoCaixaDiaAnterior
											);
		
		$sqlCompl = "a.dt_movimento between '{$dtMovimentoInicialFormatadoBanco}' and '{$dtMovimentoFinalFormatadoBanco}' and a.cd_caixa = '{$this->cdCaixa}'";
		
		if($cdPessoa <> "")
			{
			$sqlCompl.= "and a.cd_pessoa = '{$cdPessoa}'";
			}
		if($cdCategoria <> "")
			{
			$sqlCompl.= "and a.cd_plano_conta = '{$cdCategoria}'";
			}	
		if($vlrMovimento <> "")
			{
			$sqlCompl.= "and a.vlr_movimento = '{$vlrMovimento}'";
			}												
											
		$sqlDadosMovimentos = "
								SELECT
									a.cd_caixa_movimento,
									a.dt_movimento,
									a.tp_movimento,
									a.vlr_movimento,
									a.cd_pessoa,
									a.cd_documento_referencia,
									a.tp_documento_referencia,
									a.ds_movimento,
									a.cd_plano_conta,
									a.cd_hist_padrao,
									a.ds_compl_hist_padrao,
									a.cd_usuario_movimento,
									a.dt_hr_movimento,
									a.st_estorno
								FROM
									caixa_movimento a
								WHERE ".
									$sqlCompl."
							  ORDER BY
									a.dt_movimento,
									a.cd_caixa_movimento
		";
		
	
		$arrMovimentosPeriodo = P4A_DB::singleton()->fetchAll($sqlDadosMovimentos);

		$movimentosSaldoAnterior = $vlrSaldoCaixaDiaAnterior;
		
		$somaEntradas = 0;
		
		$somaSaidas = 0;
		
		foreach($arrMovimentosPeriodo as $nrLinha => $dadosLinha )
			{
			$arrMontagem["cdMovimento"]	= $dadosLinha["cd_caixa_movimento"];
			$arrMontagem["dtMovimento"]		= $dadosLinha["dt_movimento"];
			$arrMontagem["dsMovimento"]		= $dadosLinha["ds_movimento"];
			$arrMontagem["vlrSaldoAnterior"]= $movimentosSaldoAnterior;
			$arrMontagem["vlrEntrada"]		= $dadosLinha["tp_movimento"]=="E"?$dadosLinha["vlr_movimento"]:"";
			$arrMontagem["vlrSaida"]		= $dadosLinha["tp_movimento"]=="S"?$dadosLinha["vlr_movimento"]:"";;
			$arrMontagem["vlrSaldoFinal"]	= ( $movimentosSaldoAnterior+=($arrMontagem["vlrEntrada"]-$arrMontagem["vlrSaida"]) );
			
			$dadosLinha["tp_movimento"] == "E"?$somaEntradas+=$dadosLinha["vlr_movimento"]:$somaSaidas+=$dadosLinha["vlr_movimento"];
			
			$arrSourceListMovimentos[] = $arrMontagem;
			}
		
		$arrSourceListMovimentos[] = array(
											"cdMovimento"=>null,
											"dtMovimento"=>"",
											"dsMovimento"=>"Saldo Final ( Totais )",
											"vlrSaldoAnterior"=>$vlrSaldoCaixaDiaAnterior,
											"vlrEntrada"=>$somaEntradas,
											"vlrSaida"=>$somaSaidas,
											"vlrSaldoFinal"=>$movimentosSaldoAnterior
											);
			
		
		return $arrSourceListMovimentos;
		}
	}