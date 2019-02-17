<?php
class contas_pagar extends satecmax_mask
	{	
	private $stExibeDetalhes = false;
	function __construct()
		{
		parent::__construct();
		
		$this->setTitle(__("Contas à Pagar"));

		$this->build("satecmax_db_source","srcContasPagar")
			->setFields(array("*","concat_ws('/',nr_parcela,qtde_parcelas)"=>"parcelas"))
			->setTable("contas_pagar")
			->setPk("cd_conta_pagar")
			->addJoin("tbl_pessoas","contas_pagar.cd_pessoa = tbl_pessoas.cd_pessoa",array("nm_pessoa"))
			->Load()
			->firstRow();

		$this->setSource($this->srcContasPagar);

		$this->build("satecmax_full_toolbar","toolbar")
			->setMask($this);
			
		$this->build("p4a_fieldset","fsetFiltrosContasPagar")
			->setLabel(__("Filtros"))
			->setWidth(600);
			
		$this->montaFiltros();
			
		
		$this->build("p4a_table","tblContasPagar")
			->setSource($this->srcContasPagar)
			->setLabel(__("Contas a Pagar"))
			->setWidth(600);

		$this->tblContasPagar->setVisibleCols(array("cd_conta_pagar","nm_pessoa","dt_vencimento","vlr_conta_pagar","parcelas","cd_st_pagamento"));	
		
		$this->tblContasPagar->cols->cd_conta_pagar->setLabel(__("Código"));
		$this->tblContasPagar->cols->nm_pessoa->setLabel(__("Fornecedor"));
		$this->tblContasPagar->cols->dt_vencimento->setLabel(__("Vencimento"));
		$this->tblContasPagar->cols->vlr_conta_pagar->setLabel(__("Valor"));
		$this->tblContasPagar->cols->nr_parcela->setLabel(__("Parcela"));
		$this->tblContasPagar->cols->cd_st_pagamento->setLabel(__("Status"))
			->setSource(condgest::singleton()->srcStatusContasPagar);
			

		$this->tblContasPagar->addActionCol("baixarParcela");
	
		
		$this->tblContasPagar->cols->baixarParcela->setLabel(__("Baixar Parcela"));
		
		$this->intercept($this->tblContasPagar->cols->baixarParcela, "afterClick","baixarParcela");
		
		$this->setFieldsProperties();
			
		$this->build("p4a_fieldset","fsetContasPagar")
			->setLabel(__("Detalhes"))
			->setWidth(600)
			;
			
		$this->build("p4a_field","fldReplicarContas")
			->setLabel(__("Replicar Parcelas"))
			->setType("checkbox")
			->setInvisible();
			
		$this->build("p4a_button","btnVisualizarPagamento")
			->setLabel(__("Visualizar Pagamento"))
			->implement("onClick",$this,"visualizarPagamento");
			
		
		$this->fsetContasPagar->anchor($this->fields->cd_conta_pagar)->anchorLeft($this->btnVisualizarPagamento)
								->anchor($this->fields->cd_pessoa)
								->anchor($this->fields->tp_documento)
								->anchor($this->fields->nr_documento)
								->anchor($this->fields->dt_vencimento)
								->anchor($this->fields->nr_parcela)
								->anchor($this->fields->qtde_parcelas)
								->anchor($this->fldReplicarContas)
								->anchor($this->fields->vlr_conta_pagar)
								->anchor($this->fields->cd_categoria_conta)
								->anchor($this->fields->dsc_conta_pagar);
								
		
		$this->build("p4a_frame","frm")
			->setWidth(1024);								
								
		$this->frm->anchorCenter($this->fsetFiltrosContasPagar);
		$this->frm->anchorCenter($this->tblContasPagar);
		$this->frm->anchorCenter($this->fsetContasPagar);
		
		$this->addObjEsconderEdicao($this->tblContasPagar);
		$this->addObjEsconderEdicao($this->fsetFiltrosContasPagar);
		
		$this->display("main",$this->frm);
		
		$this->display("menu",p4a::singleton()->menu);
		
		$this->display("top",$this->toolbar);	
		}
	
	function setFieldsProperties()			
		{
		$fields = $this->fields;
		
		
		$fields->cd_conta_pagar->setLabel(__($this->tblContasPagar->cols->cd_conta_pagar->getLabel()))->disable();
		
		$this->build("satecmax_db_source","srcPessoas")
			->setTable("tbl_pessoas")
			->setpk("cd_pessoa")
			->load();
		
		$fields->cd_pessoa->setLabel(__("Pessoa"))
						->setSource($this->srcPessoas)
						->setSourceValueField("cd_pessoa")
						->setSourceDescriptionField("nm_pessoa")
						->setType("select")
						->setWidth(200);
			
		$fields->tp_documento->setLabel(__("Tipo Pagamento"))
							->setSource(P4A::singleton()->src_documentos_cadastrados)
							->setSourceValueField("cd_documento")
							->setSourceDescriptionField("desc_documento")
							->setType("select")
							->setWidth(200);	
							
		$fields->nr_documento->setLabel(__("Nr. Documento"))
							->setWidth(300);					
		
		$fields->dt_vencimento->setLabel(__("Dt.Vencimento"));	
		
		$fields->dt_vencimento->setLabel(__("Dt.Vencimento"));
		
		$fields->nr_parcela->setLabel(__("Nr.Parcela"));
		
		$fields->qtde_parcelas->setLabel(__("Qtde.Parcelas"));
		
		$fields->vlr_conta_pagar->setLabel(__("Valor Parcela"));
		
		$fields->cd_categoria_conta->setLabel(__("Categoria Desp."))
									->setSource(P4A::singleton()->src_saida_categoria)
									->setSourceValueField("cd_categoria")
									->setSourceDescriptionField("ds_categoria")
									->setType("select")
									->setWidth(300);
		
		$fields->dsc_conta_pagar->setLabel(__("Descrição"))->setType("textarea");
		
		}
		
	function montaFiltros()
		{
		// filtro por status 
		
		$this->build("P4a_field","fldStatus")
			->setLabel(__("Status"))
			->setType("radio")
			->setSource(condgest::singleton()->srcStatusContasPagar);
			
		$this->fsetFiltrosContasPagar->anchor($this->fldStatus);
		
		$this->build("p4a_field","fldDataVencimento1")
			->setLabel(__("Vencimento De:"))
			->setType("date");
			
		$this->build("p4a_field","fldDataVencimento2")
			->setLabel(__("Até:"))
			->setType("date");
			
		$this->fsetFiltrosContasPagar->anchor($this->fldDataVencimento1)->anchorleft($this->fldDataVencimento2);
		
		$this->build("p4a_field","fldFornecedor")
			->setLabel(__("Fornecedor"));
			
		$this->fsetFiltrosContasPagar->anchor($this->fldFornecedor);
		
		
			
		$this->build("p4a_button","btnFiltrar")
			->setLabel(__("Filtrar"))
			->implement("onClick",$this,"filtrar");
		
		$this->fsetFiltrosContasPagar->anchorCenter($this->btnFiltrar);
		}
		
	function filtrar()
		{
		$fldStatus = $this->fldStatus->getSQLNewValue();
		
		$fldVencimento1 = formatarDataBanco($this->fldDataVencimento1->getSQLNewValue());
		
		$fldVencimento2 = formatarDataBanco($this->fldDataVencimento2->getSQLNewValue());
		
		$fldFornecedor = $this->fldFornecedor->getSQLNewValue();
		
		$sqlCompl = " 1 = 1";
		
		if ( $fldStatus <> "" )
			{
			$sqlCompl.= " and contas_pagar.cd_st_pagamento = '{$fldStatus}' ";
			}
			
		if ( $fldVencimento1 <> "" and $fldVencimento2 <> "" )
			{
			$sqlCompl.= " and contas_pagar.dt_vencimento between '{$fldVencimento1}' and '{$fldVencimento2}' ";
			}
		
		if ( $fldFornecedor <> "" )
			{
			$sqlCompl.= " and tbl_pessoas.nm_pessoa like '%{$fldFornecedor}%' ";
			}
			
		$this->info($sqlCompl);
		$this->getSource()->setWhere($sqlCompl);
		
		if ( $this->getSource()->getNumRows() == 0 )
			{
			$this->getSource()->setWhere("1=0");
			$this->error(__("Nenhum Registro Encontrado!"));
			}
		else
			{
			$this->firstRow();
			}
		}
		
	function saveRow()
		{
		$stReplicarContas = false;
		if ( $this->getSource()->isNew())
			{
			$this->fields->cd_st_pagamento->setNewValue(1);
			
			// se solicitar a replicacao de parcelas...
			
			if ($this->fldReplicarContas->getNewVAlue() )
				{
				// salvar os valores dos campos da primeira parcela...
				
				$stReplicarContas = true;
				$this->fields->reset();
				while($field = $this->fields->nextItem())
					{
					if ($field->data_field->getTable() == $this->getSource()->getTable())
						$fields[$field->getName()] = $field->getSQLNewValue(); 
					}
				}
			}

		
		try
			{
			p4a_db::singleton()->beginTransaction();
			parent::saveRow();
			
			if ( $stReplicarContas )
				{
				
				// interar na quantidade de parcelas...
				
				$qtde_parcelas = $fields["qtde_parcelas"];
				$nrParcela = $fields["nr_parcela"]+1;
				
				for($a=$nrParcela;$a<= $qtde_parcelas; $a++)
					{
					$fields["nr_parcela"]++;	
					// alterar a data de vencimento da parcela...
					
					list($ano,$mes,$dia) = desmontarDataYmd($fields["dt_vencimento"]);
					
					$fields["dt_vencimento"] = acrescentarMesesDatas($dia, $mes, $ano, 1);
					
					$this->getSource()->newRow();
					
					foreach($fields as $nmField => $vlrField)
						{
						$this->getSource()->fields->$nmField->setNewValue($vlrField);
						}
						
					$this->getSource()->saveRow();
					
					}
				}
			
			P4A_DB::singleton()->commit();
			$this->fldReplicarContas->setNewValue(0);
			}
		catch (Exception $e)
			{
			
			P4A_DB::singleton()->rollback();
			
			$this->error(__("Falha ao salvar! Tente novamente!"));
			}
		}
		

	function main()
		{
			
		$this->fldReplicarContas->setVisible($this->getSource()->isNew());
		
		$this->btnVisualizarPagamento->setVisible($this->fields->cd_st_pagamento->getValue() == 2);
		
		parent::main();
		}	
		
	function baixarParcela()
		{
		if ($this->fields->cd_st_pagamento->getValue() == 2)
			{
			$this->error(__("Parcela já baixada!"));
			return false;
			}
			
		condgest::singleton()->openPopup("contas_pagar_baixar_parcela");
		}
		
	function visualizarPagamento()
		{
		condgest::singleton()->openPopup("contas_pagar_baixar_parcela");
		}
			
	}
	
	
class contas_pagar_baixar_parcela extends satecmax_mask
	{
	function __construct()
		{
		parent::__construct();
		
		$this->build("p4a_frame","frm")
			->setWidth(800);
			
			
		$this->setSource(condgest::singleton()->masks->contas_pagar->getSource());
		
		if ( $this->fields->cd_st_pagamento->getValue() == 1)
			{
			$this->setStatusMode();
			}
		
		$this->setTitle(__("Baixar parcela ").$this->fields->nr_parcela->getValue()."/".$this->fields->qtde_parcelas->getValue().
										__(" - Valor R$ ").number_format($this->fields->vlr_conta_pagar->getValue(),2,",",".")
		);
		
		
		$this->build("p4a_fieldset","fsetDadosPagamento")
			->setLabel(__("Dados Pagamento"))
			->setWidth(600);
			
		$this->frm->anchor($this->fsetDadosPagamento);
		
		$this->fsetDadosPagamento->anchor($this->fields->dt_pagamento)
			->anchor($this->fields->vlr_juros)
			->anchor($this->fields->vlr_outros)
			->anchor($this->fields->vlr_pagamento);

		$this->fields->dt_pagamento->setLabel(__("Data Pagamento"));	
		
		$this->fields->vlr_juros->setLabel(__("Juros"))
			->implement("onBlur",$this,"calculaValorTotal");
			
		$this->fields->vlr_outros->setLabel(__("Outros"))
			->implement("onBlur",$this,"calculaValorTotal");
			
		$this->fields->vlr_pagamento->setLabel(__("Valor Pagamento"));
		
		$this->build("P4a_button","btnSalvar")
			->setLabel(__("Baixar parcela"),true)
			->implement("onClick",$this,"saveRow")
			->setIcon("actions/document-save")
			->setVisible($this->fields->cd_st_pagamento->getValue() == 1);
		
		$this->frm->anchorCenter($this->btnSalvar);
			
			
		$this->fields->dt_pagamento->setNewValue($this->fields->dt_vencimento->getSQLValue());
		
		$this->fields->vlr_pagamento->setNewValue($this->fields->vlr_conta_pagar->getSQLValue());
			
		$this->display("main",$this->frm);
		}
		
	function calculaValorTotal()
		{
		$vlrJuros = $this->fields->vlr_juros->getSQLNewValue();
		
		$vlrOutros = $this->fields->vlr_outros->getSQLNewValue();
		
		$vlr_conta_pagar = $this->fields->vlr_conta_pagar->getSQLValue();
		
		$this->fields->vlr_pagamento->setNewValue($vlr_conta_pagar+$vlrJuros+$vlrOutros);
		}
		
	function saveRow()
		{
			
		if ( $this->fields->vlr_pagamento->getSQLNewValue() == "" or $this->fields->dt_pagamento->getSQLNewVAlue() == "" )
			{	
			$this->error(__("Preencha todos os campos obrigatórios!"));		
			return false;
			}

		try
			{
			
			
			P4A_DB::singleton()->beginTransaction();
			
			$this->fields->cd_st_pagamento->setNewValue(2);// marcar como pago...
			
			$this->registraBaixaCaixa();
			
			parent::saveRow();
			
			
			P4A_DB::singleton()->commit();
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

		$objCaixaMovimento = new movimentoCaixa(1);
		
		$nrLancamento = $objCaixaMovimento->newMovimentoSaida($this->fields->dt_pagamento->getNewValue(), 
													$this->fields->vlr_pagamento->getNewValue(), 
													$this->fields->cd_pessoa->getValue(), 
													$this->fields->nr_documento->getValue(), 
													$this->fields->tp_documento->getValue(), 
													"BAIXA CONTAS A PAGAR: ".$this->fields->dsc_conta_pagar->getValue(), 
													$this->fields->cd_categoria_conta->getValue(), 
													1, 
													"BAIXA CONTAS A PAGAR");
		
		}
	}
	
