<?php
class emissao_boleto extends satecmax_mask
	{
	function __construct()
		{
		parent::__construct();

		$this->build("p4a_frame","frm")
			->setWidth(550);

		$this->build("satecmax_full_toolbar","toolbar")
			->setMask($this);

		$this->toolbar->buttons->new->setInvisible();
		$this->toolbar->buttons->save->setInvisible();
		$this->toolbar->buttons->cancel->setInvisible();
		$this->toolbar->buttons->edit->setInvisible();

		$mes_ano_referencia = p4a::singleton()->masks->processamento_boleto_lote->fields->mes_ano_referencia->getvalue();
		$this->setSource(condgest::singleton()->masks->processamento_boleto_mes_unidade->getSource());

		$this->setTitle(__("Emissão dos boletos - ".$mes_ano_referencia));

		$this->montaCamposEdicaoSource();
			
		$this->display("main",$this->frm);

		$this->display("top",$this->toolbar);

		}
	function montaCamposEdicaoSource()
		{
		$cd_boleto_mes = p4a::singleton()->masks->processamento_boleto_mes_unidade->fields->cd_boleto_mes->getvalue();

		$this->build("p4a_fieldset","fset_detalhes")
			->setLabel(__("Detalhes"))
			->setWidth(400);

		$this->build("p4a_field","fld_dt_vencimento")
			->setLabel(__("Data de vencimento"))
			->setType("date")
			->setWidth(80)
			->implement("onChange",$this,"exibirUnidades");

		$this->build("p4a_button","btn_emissao_boletos")
			->setLabel(__("Emitir Boletos de Cobrança"),true)
			->setIcon("actions/process-accept-icon")
			->implement("onClick",$this,"emitirBoletosUnidades");

		$this->frm->anchorCenter($this->fset_detalhes);
		$this->fset_detalhes->anchor($this->fld_dt_vencimento);
		$this->fset_detalhes->anchor($this->btn_emissao_boletos);

		$this->build("p4a_fieldset","fset_unidades")
			->setLabel(__("Unidades"))
			->setWidth(400)
			->setVisible(false);
				
		$this->frm->anchorCenter($this->fset_unidades);

		}

	function exibirUnidades()
		{
		$this->fset_unidades->clean();
		$this->fset_unidades->setVisible(true);

		$this->build("p4a_field","fld_todas_unidades")
			->setLabel(__("Todas Unidades"))
			->setWidth(50)
			->setVisible(false)
			->setType("checkbox")
			->implement("onClick",$this,"ocultarUnidades")
			->label->setWidth(85);

		$cd_boleto_mes = p4a::singleton()->masks->processamento_boleto_mes_unidade->fields->cd_boleto_mes->getValue();
		//Opção necessária que permite o usuário inserir quantas multas desejar

		$this->arrUnidades = p4a_db::singleton()->fetchAll("select
																	a.cd_unidade
															  from
																	tbl_unidades a, tbl_boleto_mes_unidade b
															 where
																	a.cd_unidade = b.cd_unidade
																		and b.st_emitido = '0'
																		and b.cd_boleto_mes = '{$cd_boleto_mes}'");
					
		$this->fset_unidades->anchor($this->fld_todas_unidades);
		$this->fld_todas_unidades->setVisible();

		foreach($this->arrUnidades as $dadosUnidades)
			{
			$cdUnidade = $dadosUnidades["cd_unidade"];
	
			$nmCampoUnidade = "fldUnidade_".$cdUnidade;
	
			$this->build("p4a_field",$nmCampoUnidade)
					->setLabel(__("Unidade ".$dadosUnidades["cd_unidade"]))
					->setWidth(50)
					->setType("checkbox")
					->label->setWidth(85);
						
			$this->fset_unidades->anchor($this->$nmCampoUnidade);
			}

		}

	function ocultarUnidades()
		{
		if($this->fld_todas_unidades->getNewValue() == '1')
			{
			foreach($this->arrUnidades as $dadosUnidades)
				{
				$cdUnidade = $dadosUnidades["cd_unidade"];
				$nmCampoUnidade = "fldUnidade_".$cdUnidade;
		
				$this->$nmCampoUnidade->enable(false);
				}
			}
		else
			{
			foreach($this->arrUnidades as $dadosUnidades)
				{
				$cdUnidade = $dadosUnidades["cd_unidade"];
				$nmCampoUnidade = "fldUnidade_".$cdUnidade;
	
				$this->$nmCampoUnidade->enable(true);
				}
			}
		}

	function emitirBoletosUnidades()
		{
		$cd_boleto_mes = p4a::singleton()->masks->processamento_boleto_mes_unidade->fields->cd_boleto_mes->getvalue();
		$dt_vencimento = formatarDataBanco($this->fld_dt_vencimento->getNewValue());
		
		if($dt_vencimento == '')
			{
			$this->error("Selecione uma data de vencimento!");
			return false;
			}
						
		//Emissão de boleto para todas as unidades...
		if($this->fld_todas_unidades->getNewValue() == '1')
			{
			foreach($this->arrUnidades as $dadosUnidades)
				{
				$cdUnidade = $dadosUnidades["cd_unidade"];
	
				try
					{
					P4A_DB::singleton()->beginTransaction();
		
					p4a_db::singleton()->query("update tbl_boleto_mes_unidade set st_emitido = 1 where cd_boleto_mes = '{$cd_boleto_mes}' and cd_unidade = '{$cdUnidade}'");
					p4a_db::singleton()->query("update tbl_boleto_mes_unidade set dt_vencimento = '{$dt_vencimento}' where cd_boleto_mes = '{$cd_boleto_mes}' and cd_unidade = '{$cdUnidade}'");
					p4a_db::singleton()->query("update tbl_boleto_mes_unidade set dt_processamento = current_date where cd_boleto_mes = '{$cd_boleto_mes}' and cd_unidade = '{$cdUnidade}'");
							
					P4A_DB::singleton()->commit();
					}
				catch (Exception $e)
					{
					P4A_DB::singleton()->rollback();
						
					$this->error(__("Erro: ".$e->getCode()." - ".$e->getMessage()));
					}
				}
			$this->info("Boletos emitidos com data de vencimento ".$dt_vencimento);
			$this->showPrevMask();
					
			}
		else
			{
			foreach($this->arrUnidades as $dadosUnidades)
				{
				$cdUnidade = $dadosUnidades["cd_unidade"];
				$nmCampoUnidade = "fldUnidade_".$cdUnidade;

				if($this->$nmCampoUnidade->getNewValue() == '1')
					{
					try
						{
						P4A_DB::singleton()->beginTransaction();
				
						p4a_db::singleton()->query("update tbl_boleto_mes_unidade set st_emitido = 1 where cd_boleto_mes = '{$cd_boleto_mes}' and cd_unidade = '{$cdUnidade}'");
						p4a_db::singleton()->query("update tbl_boleto_mes_unidade set dt_vencimento = '{$dt_vencimento}' where cd_boleto_mes = '{$cd_boleto_mes}' and cd_unidade = '{$cdUnidade}'");
						p4a_db::singleton()->query("update tbl_boleto_mes_unidade set dt_processamento = current_date where cd_boleto_mes = '{$cd_boleto_mes}' and cd_unidade = '{$cdUnidade}'");

						P4A_DB::singleton()->commit();
						$this->info("Boletos emitido para a unidade ".$cdUnidade. " com data de vencimento ".$dt_vencimento);
						}
					catch (Exception $e)
						{
						P4A_DB::singleton()->rollback();
			
						$this->error(__("Erro: ".$e->getCode()." - ".$e->getMessage()));
						}
					}
				}
				$this->showPrevMask();
			}
		}
	}