<?php
class processamento_boleto_mes_unidade_itens_cobranca extends satecmax_mask
	{
	function __construct()
		{
		parent::__construct();

		$this->build("P4a_frame","frm")
			->setWidth(500);

		$this->build("satecmax_full_toolbar","toolbar")
			->setMask($this);												
		
		$this->desabilitaBotoesToolbar();
		
		$this->setTitle(__("Lançamento dos Ítens de Cobrança - ").p4a::singleton()->masks->processamento_boleto_mes_unidade->fields->mes_ano_referencia->getvalue());

		$this->montaSources();
		
		$this->montaFrames();
		
		$this->montaCamposEdicaoSource();
			
		$this->display("main",$this->frm);
		
		$this->display("top",$this->toolbar);
		}

	function desabilitaBotoesToolbar()
		{
		$this->toolbar->buttons->new->setInvisible();
		$this->toolbar->buttons->save->setInvisible();
		$this->toolbar->buttons->cancel->setInvisible();
		$this->toolbar->buttons->edit->setInvisible();		
		}	
		
	function montaSources()
		{
		$this->setSource(condgest::singleton()->masks->processamento_boleto_mes_unidade->getSource());
		
		$this->build("p4a_db_source","src_mes_ano_ref_rateio_agua")
			->setTable("tbl_leitura_agua_mes")
			->setwhere("st_processamento = 1 and st_utilizado = '0'")
			->Load();		
		}	
		
	function montaFrames()
		{
		$this->build("p4a_fieldset","fset_edicao")
			->setLabel(__("Detalhes"))
			->setWidth(500);		
		
		$this->frm->anchor($this->fset_edicao);
		
		$this->build("p4a_fieldset","fsetUnidades")
			->setLabel(__("Unidades"))
			->setWidth(150)
			->setVisible(false);
			
		$this->frm->anchor($this->fsetUnidades);
		
		}	
		
	function montaCamposEdicaoSource()
		{			
 		$this->build("p4a_field","fldNome");
 		$this->fldNome->setLabel(__("Nome"));
		$this->fldNome->setWidth(250);
		$this->fldNome->setSource(P4A::singleton()->src_itens_cobranca);
		$this->fldNome->setSourceValueField("cd_item_cobranca");
		$this->fldNome->setSourceDescriptionField("desc_item_cobranca");
		$this->fldNome->setType("select");
		$this->fldNome->allowNull("Selecione ...");
		$this->fldNome->implement("onChange",$this,"habilitaCampoUnidade");
		$this->fldNome->label->setWidth(150);
 		
		$this->fset_edicao->anchor($this->fldNome);
		
		$this->build("p4a_field","fldRateioAgua");
		
		$this->fldRateioAgua->setLabel(__("Mês/Ano Referência"));
		$this->fldRateioAgua->setSource($this->src_mes_ano_ref_rateio_agua);
		$this->fldRateioAgua->setType("select");
		$this->fldRateioAgua->setInvisible();
		$this->fldRateioAgua->setWidth(100);
		$this->fldRateioAgua->label->setWidth(150);
					
		$this->fset_edicao->anchor($this->fldRateioAgua);	
		
		$this->build("p4a_field","fldValor");

		$this->fldValor->setLabel(__("Valor"));
		$this->fldValor->setProperty("dir","rtl");
		$this->fldValor->setWidth(100);
		$this->fldValor->setInvisible();
		$this->fldValor->label->setWidth(150);
		
		$this->fset_edicao->anchor($this->fldValor);
				
		$this->build("p4a_button","btnSalvar");
		
		$this->btnSalvar->setLabel(__("Adicionar Ítem de Cobrança"),true);
		$this->btnSalvar->implement("onClick",$this,"adicionarItemCobranca");
		$this->btnSalvar->setVisible(true);
		
		$this->fset_edicao->anchor($this->btnSalvar);		
		}		
		
	function habilitaCampoUnidade()
		{			
		$this->fsetUnidades->clean();	
		$this->fsetUnidades->setVisible(false);	
		
		if($this->validaItemObrigatorio() == '1')
			{
			$this->fsetUnidades->setVisible(true);
			
			$this->build("p4a_field","fldUnidades")
				->setLabel(__("Todas "))
				->setWidth(50)
				->setType("checkbox")
				->implement("onClick",$this,"desabilitarUnidades")
				->label->setWidth(60);
			
			$this->fsetUnidades->anchor($this->fldUnidades);
			
			$this->arrUnidades = p4a_db::singleton()->fetchAll("select
																		a.cd_unidade,
																		b.cd_boleto_mes_unidade
																  from
																		tbl_unidades a, tbl_boleto_mes_unidade b
															   	 where
																		a.cd_unidade = b.cd_unidade
																		and b.st_emitido = '0'
																		and b.cd_boleto_mes = ".p4a::singleton()->masks->processamento_boleto_mes_unidade->fields->cd_boleto_mes->getValue());
				
			foreach($this->arrUnidades as $dadosUnidades)
				{
				$codUnidade = $dadosUnidades["cd_unidade"];
				$codBoletoMesUnidade = $dadosUnidades["cd_boleto_mes_unidade"];
				
				$nmCampoUnidade = "fldUnidade_".$codUnidade;
			
				$this->build("p4a_field",$nmCampoUnidade)
					->setLabel(__("Unidade ".$dadosUnidades["cd_unidade"]))
					->setWidth(50)
					->setType("checkbox")
					->label->setWidth(60);
			
				$this->fsetUnidades->anchor($this->$nmCampoUnidade);
				}
			}	

		if ($this->validaTipoValor() == "3")
			{
			$this->fldRateioAgua->setVisible();
			}
		else
			{
			$this->fldRateioAgua->setInvisible();		
			}	
		}
		
	function validaItemObrigatorio()
		{
		$campoObrigatorio = p4a_db::singleton()->fetchOne("select
																count(*)
															 from
																tbl_itens_cobranca_boleto
														    where
																cd_item_cobranca = {$this->fldNome->getNewValue()}
																	and tp_valor = 0"); 		
		
		return $campoObrigatorio;
		
		}
		
	function validaTipoValor()
		{
		$tpValor = p4a_db::singleton()->fetchOne("select
														tp_valor
													from
														tbl_itens_cobranca_boleto
												   where
														cd_item_cobranca = {$this->fldNome->getNewValue()}");	
		
		return $tpValor;
		
		}	

	function desabilitarUnidades()
		{
		if($this->fldUnidades->getNewValue() == '1')
			{			
			foreach($this->arrUnidades as $dadosUnidades)
				{
				$codUnidade = $dadosUnidades["cd_unidade"];
				$nmCampoUnidade = "fldUnidade_".$codUnidade;
									
				$this->$nmCampoUnidade->enable(false);
				}
			}
		else
			{
			foreach($this->arrUnidades as $dadosUnidades)
				{
				$codUnidade = $dadosUnidades["cd_unidade"];
				$nmCampoUnidade = "fldUnidade_".$codUnidade;
					
				$this->$nmCampoUnidade->enable(true);
				}		
			}	
		}	
		
	function adicionarItemCobranca()
		{		
		$codItemCobranca = $this->fldNome->getNewValue();
		
		$this->arrItensCobranca = p4a_db::singleton()->fetchAll("select 
																		ds_item_cobranca,
																		vlr_item_cobranca 
																 from 
													  		 			tbl_itens_cobranca_boleto 
													 	  		 where 
														 				cd_item_cobranca = '{$codItemCobranca}'");
				
			foreach($this->arrItensCobranca as $dadosItensCobranca)
				{
				$dsItemCobranca = $dadosItensCobranca["ds_item_cobranca"];
				$vlrItemCobranca = $dadosItensCobranca["vlr_item_cobranca"];
				}
				
		//Acrescenta 10% no valor do ítem de cobrança
		$percentualInadimpl = condgest::singleton()->getParametro("PERC_INADIMPL_BOL");
		
		$vlrItemCobranca = $vlrItemCobranca / $percentualInadimpl;
		
		$codBoletoMes = p4a::singleton()->masks->processamento_boleto_mes_unidade->fields->cd_boleto_mes->getValue();				
			
		if($this->fldNome->getNewValue() == null)
			{	
			$this->error("Selecione um item de cobrança!");
			return false;
			}
		
		if($this->fldNome->getNewValue() == "4")
			{
			$cd_leitura_agua_mes = $this->fldRateioAgua->getNewValue();
			$mes_ano_referencia = P4A_DB::singleton()->fetchOne("select mes_ano_referencia from tbl_leitura_agua_mes where cd_leitura_agua_mes = {$cd_leitura_agua_mes}");

			//Adicionado 10% no valor total rateado de água
			$this->arrLeituraIndividual = p4a_db::singleton()->fetchAll("select
																				b.cd_boleto_mes_unidade,
																				c.cd_unidade,
																				round(c.vlr_total/{$percentualInadimpl},2) as vlr_total
																		 from
																				tbl_boleto_mes a,
																				tbl_boleto_mes_unidade b,
																				tbl_leitura_agua_mes_individual c,
																				tbl_leitura_agua_mes d
																		 where
																				a.cd_boleto_mes = b.cd_boleto_mes
																				and b.cd_unidade = c.cd_unidade
																				and c.cd_leitura_agua_mes = d.cd_leitura_agua_mes
																				and d.mes_ano_referencia = '{$mes_ano_referencia}'
																				and a.cd_boleto_mes = {$codBoletoMes}");
			
			foreach($this->arrLeituraIndividual as $dadosUnidades)
				{
				$cdBoletoMesUnidade = $dadosUnidades["cd_boleto_mes_unidade"];
				$cdUnidade = $dadosUnidades["cd_unidade"];
				$vlr_consumido = $dadosUnidades["vlr_total"];
				$this->adicionarItensCobranca($codBoletoMes, $cdBoletoMesUnidade,$cdUnidade,$codItemCobranca,$dsItemCobranca, "", $vlr_consumido,"D");	
				}
			$this->atualizaFlagLeituraAgua();	
			$this->showPrevMask();	
			} 
		else
			{
			$this->arrUnidades = p4a_db::singleton()->fetchAll("select
																		a.cd_unidade,
																		b.cd_boleto_mes_unidade
																  from
																		tbl_unidades a, tbl_boleto_mes_unidade b
															   	 where
																		a.cd_unidade = b.cd_unidade
																		and b.st_emitido = '0'
																		and b.cd_boleto_mes = '{$codBoletoMes}'");
					
			foreach($this->arrUnidades as $dadosUnidades)
				{
				$codUnidade = $dadosUnidades["cd_unidade"];
				$codBoletoMesUnidade = $dadosUnidades["cd_boleto_mes_unidade"];
	
				$this->adicionarItensCobranca($codBoletoMes, $codBoletoMesUnidade, $codUnidade, $codItemCobranca, $dsItemCobranca, "", $vlrItemCobranca);
				}
			$this->info($dsItemCobranca. " inserido(a) com sucesso para todas as unidades!");	
			$this->showPrevMask();
			}			
		}
		
	function adicionarItensCobranca($codBoletoMes, $codBoletoMesUnidade,$codUnidade,$codItemCobranca,$dsItemCobranca, $campoObservacao, $vlr_consumido)
		{			
		try
			{
			P4A_DB::singleton()->beginTransaction();
			p4a_db::singleton()->query("insert into 
													tbl_boleto_mes_unidade_itens_cobranca 
														(cd_boleto_mes_unidade,
														cd_unidade,
														cd_item_cobranca,
														ds_item_boleto,
														vlr_item_boleto)
										values										
														('".$codBoletoMesUnidade."',
														 '".$codUnidade."',
														 '".$codItemCobranca."',
														 '".$campoObservacao."',
														 '".$vlr_consumido."')
									");	

			P4A_DB::singleton()->commit();
			}
		catch (Exception $e)
			{
			P4A_DB::singleton()->rollback();
			
			$this->error(__("Erro: ".$e->getCode()." - ".$e->getMessage()));
			}
		}				
			
	function atualizaFlagLeituraAgua()
		{
		$cdLeituraAguaMes = $this->fldRateioAgua->getNewValue();

		
		$sqlAtualizarFlag = "update
									tbl_leitura_agua_mes
							set
									st_utilizado = 1
							where
									cd_leitura_agua_mes = {$cdLeituraAguaMes}";

		$this->info($sqlAtualizarFlag);
		
		P4A_DB::singleton()->query($sqlAtualizarFlag);
		
		}		
			
		
	function main()
		{	
		parent::main();
		}		
	}