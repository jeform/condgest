<?php

class acordos extends satecmax_mask
	{	
	function __construct()
		{
		parent::__construct();
		
		$this->setTitle(__("Acordos"));
		
		$this->build("p4a_frame","frm")
			->setWidth(900);
											
		$this->montaToolbar();
		
		$this->montaTabelaAcordos();
		
		$this->display("main",$this->frm);
		$this->display("menu",p4a::singleton()->menu);
		$this->display("top",$this->toolbar);	
		}
	
	function montaToolbar()
		{
		$this->build("satecmax_quit_toolbar","toolbar");
			
		/*$this->toolbar->addButton('novoAcordo','actions/document-new')
					->setLabel("Novo acordo",true)
					->implement("onClick",$this,"unidadesInadimplentes");*/
		
		$this->toolbar->addButton('acordosInadimplentes','actions/document-print')
					->setLabel("Imprimir acordos descumpridos",true)
					->implement("onClick",$this,"acordosDescumpridos");
		
		}
		
	function montaTabelaAcordos()
		{
		$this->build("satecmax_db_source","srcAcordos")
			->setTable("acordos")
			->addJoin("tbl_unidades","acordos.cd_unidade = tbl_unidades.cd_unidade",array("cd_pessoa"))
			->setFields(array('*',"(SELECT
										COUNT(*) 
									  FROM
										acordos_detalhes 
								     WHERE
										cd_acordo = acordos.cd_acordo
									   AND cd_st_recebimento = 1)"=>"qtdeParcelasAbertas",
									"(SELECT
										COUNT(*)
									  FROM
										acordos_detalhes
									 WHERE
										cd_acordo = acordos.cd_acordo
									   AND cd_st_recebimento = 1
									   AND date_add(dt_vencimento, interval 1 day) < curdate())"=>"qtdeParcelasAtraso",
									"(SELECT
										SUM(vlr_parcela)
									  FROM
										acordos_detalhes
									  WHERE
										cd_acordo = acordos.cd_acordo)"=>"vlrTotal",
									"ROUND(IFNULL((SELECT
														SUM(vlr_parcela +
															vlr_parcela * 0.1 +
															vlr_parcela * 0.01 * DATEDIFF(DATE_aDD(CURRENT_DATE(), INTERVAL 0 DAY), dt_vencimento) / 30 +
															IFNULL(vlr_parcela * ((SELECT
																						SUM(y.indice_correcao)
                				   						 							 FROM
                    									 								tbl_inpc y
                				  													WHERE
                    																	DATE_FORMAT(STR_TO_DATE(y.mes_ano_referencia, '%m/%Y'),'%Y/%m')
                    																	BETWEEN DATE_FORMAT(acordos_detalhes.dt_vencimento,'%Y/%m')
                    																		AND DATE_FORMAT(CURRENT_DATE(), '%Y/%m')) / 100),0))
													FROM
														acordos_detalhes
					   							   WHERE 
														cd_acordo = acordos.cd_acordo
													 AND cd_st_recebimento = 1
													 AND DATE_ADD(dt_vencimento, INTERVAL 1 DAY) < CURDATE()),0),2)"=>"vlrPendente"))
			->setPk("cd_acordo")
			->addOrder("acordos.cd_unidade", "ASC")
			->Load()
			->firstRow();
		
		$this->setSource($this->srcAcordos);
		
		$this->build("satecmax_table","tblAcordos")
			->setSource($this->srcAcordos)
			->setWidth(900);

		$this->srcAcordos->fields->vlrTotal->setType("decimal");
		$this->srcAcordos->fields->vlrPendente->setType("decimal");
		
		$this->tblAcordos->setVisibleCols(array("cd_unidade","dt_acordo","vlrTotal","qtde_parcelas","qtdeParcelasAbertas","qtdeParcelasAtraso","vlrPendente"));
		$this->tblAcordos->cols->cd_unidade->setLabel(__("Unidade"));
		$this->tblAcordos->cols->dt_acordo->setLabel(__("Dt. Acordo"));
		$this->tblAcordos->cols->vlrTotal->setLabel(__("Valor Total (R$)"));
		$this->tblAcordos->cols->qtde_parcelas->setLabel(__("Parcelas"));
		$this->tblAcordos->cols->qtdeParcelasAbertas->setLabel(__("Parcelas em aberto"));
		$this->tblAcordos->cols->qtdeParcelasAtraso->setLabel(__("Parcelas em atraso"));
		$this->tblAcordos->cols->vlrPendente->setLabel(__("Vlr. Pendente (R$)"));
		
		$this->tblAcordos->addActionCol("detalhes");
		$this->tblAcordos->cols->detalhes->setLabel(__("Detalhes"));
		$this->intercept($this->tblAcordos->cols->detalhes, "afterClick","abrirDetalhes");
		
		$this->tblAcordos->addActionCol("composicaoAcordo");
		$this->tblAcordos->cols->composicaoAcordo->setLabel(__("Composição"));
		$this->intercept($this->tblAcordos->cols->composicaoAcordo, "afterClick","mostraComposicaoAcordo");
		
		$this->tblAcordos->addActionCol("imprimirAcordo");
		$this->tblAcordos->cols->imprimirAcordo->setLabel(__("Imprimir"));
		$this->intercept($this->tblAcordos->cols->imprimirAcordo, "afterClick","imprimirAcordo");
		
		
		$this->frm->anchorCenter($this->tblAcordos);	
		}
		
	function abrirDetalhes()
		{	
		condgest::singleton()->openMask("acordo_detalhe");
		}
		
	function mostraComposicaoAcordo()
		{
		condgest::singleton()->openMask("composicao_acordo");
		}

	function unidadesInadimplentes()
		{
		condgest::singleton()->openMask("unidades_inadimplentes");
		}

	function imprimirAcordo()
		{
		$objImpressaoAcordo = new impressaoAcordo();
		$objImpressaoAcordo->setParametros($this->fields->cd_acordo->getNewValue());	
		P4A_Output_File($objImpressaoAcordo->Output(), "confissaoDivida.pdf",true);
		}

	function acordosDescumpridos()
		{
		$objAcordosDescumpridos = new impressaoAcordosDescumpridos();
		P4A_Output_File($objAcordosDescumpridos->Output(),"acordosDescumpridos.pdf",true);
		}
		
	function filtrarPorStatus()
		{
		$this->srcAcordos->setWhere("st_acordo = ".$this->fldStatus->getNewValue());
		}

	function main()
		{		
		parent::main();
		}
	}