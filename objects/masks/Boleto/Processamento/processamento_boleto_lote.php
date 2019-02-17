<?php
class processamento_boleto_lote extends satecmax_mask
	{
	function __construct()
		{
		parent::__construct();

		$this->setTitle(__("Processamento de Boletos em Lote"));

		$this->build("satecmax_db_source","src_boleto_mes")
			->setTable("tbl_boleto_mes")
			->setPk("cd_boleto_mes")
			->setFields(array("*",
							"(SELECT 
										COUNT(*) 
								FROM 
										tbl_boleto_mes_unidade 
							   WHERE 
										cd_boleto_mes = tbl_boleto_mes.cd_boleto_mes)"=>"qtde_boleto",
							"IFNULL((SELECT
												SUM(b.vlr_item_boleto) * 0.9
									   FROM
												tbl_boleto_mes_unidade a,
												tbl_boleto_mes_unidade_itens_cobranca b
							   		  WHERE
												a.cd_boleto_mes = tbl_boleto_mes.cd_boleto_mes
													AND a.cd_boleto_mes_unidade = b.cd_boleto_mes_unidade
													AND a.st_emitido = 1
									  GROUP BY tbl_boleto_mes.mes_ano_referencia),0)"=>"vlr_boleto",
							"IFNULL((SELECT 
												SUM(vlr_baixa_boleto) 
									   FROM
												tbl_boleto_mes_unidade 
									  WHERE
												cd_boleto_mes = tbl_boleto_mes.cd_boleto_mes),0)"=>"vlr_recebido",
							"IFNULL((SELECT 
												COUNT(*) 
									   FROM
												tbl_boleto_mes_unidade 
									  WHERE
												cd_boleto_mes = tbl_boleto_mes.cd_boleto_mes
													AND st_baixado = 0),0)"=>"qtde_boletos_pendentes"))
			->Load()
			->firstRow();
			
		$this->src_boleto_mes->fields->vlr_boleto->setType("decimal");
		$this->src_boleto_mes->fields->vlr_recebido->setType("decimal");
			
		$this->build("p4a_table", "tbl_boleto_mes")
			->setSource($this->src_boleto_mes)
			->setWidth(550);
		
		$this->tbl_boleto_mes->setVisibleCols(array("mes_ano_referencia","qtde_boleto","vlr_boleto","vlr_recebido","qtde_boletos_pendentes"));
		$this->tbl_boleto_mes->cols->mes_ano_referencia->setLabel(__("Mês/Ano Ref."))
													->setWidth(60);
		$this->tbl_boleto_mes->cols->qtde_boleto->setLabel(__("Total de Boletos"))
												->setWidth(70);
		$this->tbl_boleto_mes->cols->vlr_boleto->setLabel(__("Vlr. Total Esperado (R$)"))
												->setWidth(70);
		$this->tbl_boleto_mes->cols->vlr_recebido->setLabel(__("Vlr. Total Recebido (R$)"))
												->setWidth(70);
		$this->tbl_boleto_mes->cols->qtde_boletos_pendentes->setLabel(__("Qtde. Boletos Pendentes"))
												->setWidth(100);
		
		$this->tbl_boleto_mes->addActionCol("detalhes");	
		
		$this->tbl_boleto_mes->cols->detalhes->setLabel(__("Detalhes"))
											->setWidth(50);
		
		$this->intercept($this->tbl_boleto_mes->cols->detalhes, "afterClick","mostrarBoletosProcessados");
		
		$this->build("satecmax_full_toolbar","toolbar")
			->setMask($this);
		
		$this->toolbar->buttons->new->setLabel(__("Novo Mês/Ano Referência"),true);
		$this->toolbar->buttons->edit->setLabel(__("Conciliar Boletos Gerados Bradesco"),true)
									->setIcon("actions/conciliation")
									->setProperty("accesskey","C")
									->implement("onClick",$this,"conciliarBoletosGerados");						
									
		$this->toolbar->addButton('baixaBoletoLote','actions/fp_boleto');
		$this->toolbar->buttons->baixaBoletoLote->setLabel(__("Baixar Boletos em Lote"),true)
												->implement("onClick",$this,"baixarBoletosLote");								
									
		$this->build("p4a_fieldset","fset_boleto_mes")
			->setLabel(__("Detalhes"))
			->setWidth(300);

		$this->build("p4a_frame","frm")
			->setWidth(1024);
		
		$this->setSource($this->src_boleto_mes);
		
		$this->fset_boleto_mes->anchor($this->fields->cd_boleto_mes)
							->anchor($this->fields->mes_ano_referencia);
		
		$this->frm->anchorCenter($this->tbl_boleto_mes);
		$this->frm->anchorCenter($this->fset_boleto_mes);
		
		$this->setFieldsProperties();
		
		$this->addObjEsconderEdicao($this->tbl_boleto_mes);
		
		$this->display("main",$this->frm);
		
		$this->display("menu",p4a::singleton()->menu);
		
		$this->display("top",$this->toolbar);
		
		}
		
		
	function setFieldsProperties()
		{
		$fields = $this->fields;
		
		$fields->cd_boleto_mes->setInvisible();
		
		$fields->mes_ano_referencia->setLabel(__("Mês/Ano Referência"))
									->setInputMask("99/9999")
									->setWidth(50)
									->label->setWidth(150);
		}	
		
	function mostrarBoletosProcessados()
		{
		if ($this->fields->st_boleto_mes->getSQLNewValue() == "1")
			{
			$this->error("Mês/Ano já efetuados");
			return false;
			}
		p4a::singleton()->openMask("processamento_boleto_mes_unidade");
		}	
		
	function conciliarBoletosGerados()
		{
		p4a::singleton()->openMask("conciliar_planilha_boletos_gerados");
		}	
		
	function baixarBoletosLote()
		{
		p4a::singleton()->openMask("processamento_planilha_bradesco");
		}

	function saveRow()
		{
		if ( $this->validateFields() and $this->getsource()->isNew() )
			{
			$mes_ano_referencia = $this->fields->mes_ano_referencia->getNewValue();
		
			if ( (p4a_db::singleton()->fetchOne("select count(*) from tbl_boleto_mes where mes_ano_referencia = '{$mes_ano_referencia}'") > 0) )
				{
				$this->error(__("Mês/Ano referência já cadastrado!"));
				return false;
				}
			}
		
		parent::saveRow();

		if($this->fields->mes_ano_referencia->getNewValue() <> '')
			{
			$this->processarItensCobranca();
			}
		}	
		
	function processarItensCobranca()
		{
		$cd_boleto_mes = $this->fields->cd_boleto_mes->getNewValue(); 
		$this->arrDescUnidade = p4a_db::singleton()->fetchAll("SELECT cd_unidade FROM tbl_unidades WHERE st_unidade = 1 AND cd_pessoa <> 151 ORDER BY cd_unidade;");
	
		foreach($this->arrDescUnidade as $dadosUnidades)
			{
			$cdUnidade = $dadosUnidades["cd_unidade"];
	
			$nmCampoUnidade = "fldQtdeUnidadeAnterior_".$cdUnidade;

			try
				{
				P4A_DB::singleton()->beginTransaction();
	
				p4a_db::singleton()->query("INSERT INTO tbl_boleto_mes_unidade
														(cd_boleto_mes,
														 cd_unidade,
														 st_emitido,
													     st_baixado,
														 tp_boleto
														)
											     VALUES
														('".$cd_boleto_mes."',
														'".$cdUnidade."',
														'0',
														'0',
														'0'
														);
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
		parent::main();
		}		
		
	function setStatusMode()
		{
		$this->info(__("Não é possivel editar!"));
		}
	}