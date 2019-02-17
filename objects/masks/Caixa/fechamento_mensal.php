<?php
class fechamento_mensal extends satecmax_mask{
	
	public $campos;
	
	function __construct()
		{
		parent::__construct();
		
		$this->setTitle(".: Fechamento mensal :.");
		
		$this->build("p4a_frame","frm")
			->setWidth(1024);
		
		$this->build("p4a_quit_toolbar","toolbar");
		
		$this->montaTabela();
		$this->montaCampos();
		
		$this->display("top",$this->toolbar);
		$this->display("main",$this->frm);
		$this->display("menu",p4a::singleton()->menu);
	}
	
	function montaTabela(){
		$this->build("satecmax_db_source","srcFechamentoMensal")
			->setTable("fechamento_mensal")
			->setPk("id_fechamento_mensal")
			->addOrder("mes_ano_referencia","desc")
			->Load()
			->firstRow();
				
		$this->setSource($this->srcFechamentoMensal);
			
		$this->build("p4a_table","tblFechamentoMensal")
			->setSource($this->srcFechamentoMensal)
			->setWidth(800);
			
		$this->srcFechamentoMensal->fields->saldo_caixa_especie->setType("decimal");
		$this->srcFechamentoMensal->fields->saldo_caixa_banco->setType("decimal");
		$this->srcFechamentoMensal->fields->saldo_caixa_aplicacao->setType("decimal");
			
		$this->tblFechamentoMensal->setVisibleCols(array("mes_ano_referencia","saldo_caixa_especie", "saldo_caixa_banco", "saldo_caixa_aplicacao", "usuario","dt_fechamento"));
			
		$this->tblFechamentoMensal->cols->mes_ano_referencia->setLabel(__("Mês/ano referência"))->setWidth(100);
		$this->tblFechamentoMensal->cols->saldo_caixa_especie->setLabel(__("Saldo em espécie (R$)"))->setWidth(150);
		$this->tblFechamentoMensal->cols->saldo_caixa_banco->setLabel(__("Saldo banco(R$)"))->setWidth(150);
		$this->tblFechamentoMensal->cols->saldo_caixa_aplicacao->setLabel(__("Saldo aplicação (R$)"))->setWidth(150);
		$this->tblFechamentoMensal->cols->usuario->setLabel(__("Usuário"))
												->setSource(P4A::singleton()->srcLoginUsuario)
												->setSourceValueField("cd_usuario")
												->setSourceDescriptionField("login")
												->setWidth(100);
		$this->tblFechamentoMensal->cols->dt_fechamento->setLabel(__("Data/hora"))->setWidth(150);

		$this->frm->anchorCenter($this->tblFechamentoMensal);
		}
	
	function montaCampos(){
		$this->build("p4a_fieldset","fsetCampos")
			->setLabel(__("Detalhes"))
			->setWidth(500);
		
		$this->build("p4a_field","fldMesAnoReferencia")
					->setLabel(__("Mês/ano referência"));
		
		$this->build("p4a_field","fldSaldoCaixaEspecie")
					->setLabel(__("Saldo caixa espécie R$"));
					
		$this->build("p4a_field","fldCaixaBanco")
					->setLabel(__("Saldo banco R$"));
					
		$this->build("p4a_field","fldCaixaAplicacao")
					->setLabel(__("Saldo caixa R$"));
					
		$this->setFieldsProperties();
		
		$this->build("p4a_button","btnFechamentoMes")
			//->setIcon("actions/Ok")
			->setLabel(__("Fechar mês"),true)
			->implement("onClick",$this,"fecharMes");
		
		$this->build("p4a_button","btnReabrirMes")
			->setLabel(__("Reabrir mês"),true)
			->implement("onClick",$this,"reabrirMes");
		
		$this->fsetCampos->anchor($this->fldMesAnoReferencia)
							->anchor($this->fldSaldoCaixaEspecie)
							->anchor($this->fldCaixaBanco)
							->anchor($this->fldCaixaAplicacao)
							->anchor($this->btnFechamentoMes);
						
		$this->frm->anchorCenter($this->fsetCampos);
		}
		
	function setFieldsProperties(){
		
		$fields = $this->fields;
		
		$this->build("satecmax_db_source","srcMesAnoMovimento")
			->setTable("caixa_movimento")
			->setPk("cd_caixa_movimento")
			->setQuery("SELECT
							    YEAR(dt_movimento),
							    CONCAT_WS('/',
							            LPAD(MONTH(dt_movimento), 2, '0'),
							            YEAR(dt_movimento)) AS mes_ano
							FROM
							    caixa_movimento
							WHERE
								id_fechamento_mensal = 0
							GROUP BY 1 , 2
							ORDER BY 1 , 2")
							->load();
		
		$this->fldMesAnoReferencia->setType("select")
										->setSource($this->srcMesAnoMovimento)
										->setSourceValueField("mes_ano")
										->setSourceDescriptionField("mes_ano")
										->setLabel(__("Selecione Mês/Ano"))
										->allowNull(__("Selecione..."))
										->implement("onChange",$this,"carregarSaldoContas");
		
		$this->fldMesAnoReferencia->label->setWidth(150);
		
		$this->fldSaldoCaixaEspecie->setWidth(120)
								->enable(false);
		
		$this->fldSaldoCaixaEspecie->label->setWidth(150);
		
		$this->fldCaixaBanco->setWidth(120)
								->enable(false);
		
		$this->fldCaixaBanco->label->setWidth(150);
		
		$this->fldCaixaAplicacao->setWidth(120)
										->enable(false);
		
		$this->fldCaixaAplicacao->label->setWidth(150);
	}
	
	function carregarSaldoContas(){
		$this->arrayCaixas = P4A_DB::singleton()->fetchAll("SELECT cd_caixa FROM caixa WHERE st_caixa = 1");
		
		$data = recuperarUltimoDiaMes($this->fldMesAnoReferencia->getNewValue());
		
		foreach($this->arrayCaixas as $dadosCaixas){
			$cdCaixa = $dadosCaixas["cd_caixa"];
			$this->objMovimentoCaixa = new movimentoCaixa($cdCaixa);
			
			if($cdCaixa == "1"){
				$this->fldSaldoCaixaEspecie->setValue(formataValoresExibicao($this->objMovimentoCaixa->getSaldoCaixa($data)));
			}
			if($cdCaixa == "4"){
				$this->fldCaixaBanco->setValue(formataValoresExibicao($this->objMovimentoCaixa->getSaldoCaixa($data)));
			}
			if($cdCaixa == "5"){
				$this->fldCaixaAplicacao->setValue(formataValoresExibicao($this->objMovimentoCaixa->getSaldoCaixa($data)));
			}
		}
	}
	
	/*
	 * TODO:Criar método que validará se os meses anteriores foram encerrados
	 * 
	 */
	
	function consistirDados(){
		
	}

	function fecharMes(){
		try{
			P4A_DB::singleton()->beginTransaction();
			$objMovimento = new movimentoCaixa();
			
			$objMovimento->newFechamentoMensal(
											$this->fldMesAnoReferencia->getNewValue(),
											$this->fldSaldoCaixaEspecie->getNewValue(),
											$this->fldCaixaBanco->getNewValue(),
											$this->fldCaixaAplicacao->getNewValue()
											);
			
			list($mes,$ano) = desmontarMesAnoReferencia($this->fldMesAnoReferencia->getNewValue());
			
			P4A_DB::singleton()->query("update 
											caixa_movimento 
										set 
											id_fechamento_mensal = ? 
										where 
											MONTH(dt_movimento) = ? 
											and YEAR(dt_movimento) = ? ", 
					array(1,$mes, $ano));
			
			
			P4A_DB::singleton()->commit();
			
			$this->info(__("Registro criado com sucesso!"));
		}
		catch (Exception $e){
			P4A_DB::singleton()->rollback();
			
			$this->error(__("Erro: ".$e->getCode()." - ".$e->getMessage()));
			
		}
	}
	
	function reabrirMes(){
		$this->info("Em implementação!!!");	
	}
		
	function main(){
		parent::main();
	}
}