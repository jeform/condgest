<?php
class caixa_movimento extends satecmax_mask
	{
	function __construct()
		{
		parent::__construct();
		
		$this->setTitle(__("Controle de Movimentação no Caixa"));
		
		// listar as contas e mostrar os saldos...
		
		$this->build("p4a_db_source", "srcSaldoCaixa")
			->setQuery("
							select
								cd_caixa,
								ds_caixa,
								vlr_saldo_inicial,
								ifnull( (select sum(vlr_movimento) from caixa_movimento where cd_caixa = caixa.cd_caixa and tp_movimento = 'E'),0) as entradas,
								ifnull( (select sum(vlr_movimento) from caixa_movimento where cd_caixa = caixa.cd_caixa and tp_movimento = 'S'),0) as saidas,
								vlr_saldo_inicial + ifnull( (select sum(vlr_movimento) from caixa_movimento where cd_caixa = caixa.cd_caixa and tp_movimento = 'E'),0) - ifnull( (select sum(vlr_movimento) from caixa_movimento where cd_caixa = caixa.cd_caixa and tp_movimento = 'S'),0) as saldo 
							from
								caixa
							where
								st_caixa = 1
							")
			->setPk("cd_caixa")
			->Load();
			
		$this->build("p4a_table","tblSaldoCaixa")
			->setSource($this->srcSaldoCaixa)
			->setWidth(800);
			
		$this->srcSaldoCaixa->fields->vlr_saldo_inicial->setType("decimal");	
		$this->srcSaldoCaixa->fields->entradas->setType("decimal");
		$this->srcSaldoCaixa->fields->saidas->setType("decimal");
		$this->srcSaldoCaixa->fields->saldo->setType("decimal");
		
		$this->tblSaldoCaixa->cols->cd_caixa->setLabel(__("Código"));
		$this->tblSaldoCaixa->cols->ds_caixa->setLabel(__("Nome Caixa"));
		$this->tblSaldoCaixa->cols->vlr_saldo_inicial->setLabel(__("Saldo Inicial"));
		$this->tblSaldoCaixa->cols->entradas->setLabel(__("Entradas"));
		$this->tblSaldoCaixa->cols->saidas->setLabel(__("Saidas"));
		$this->tblSaldoCaixa->cols->saldo->setLabel(__("Saldo"));		
		
		$this->tblSaldoCaixa->addActionCol("Lancamentos");
		
		$this->intercept($this->tblSaldoCaixa->cols->Lancamentos, "afterClick","verLancamentos");
		
		$this->tblSaldoCaixa->addActionCol("Relatorios");
		
		$this->intercept($this->tblSaldoCaixa->cols->Relatorios,"afterClick","verRelatorios");
			
		$this->build("p4a_frame","frm")
			->anchor($this->tblSaldoCaixa);
			
		$this->display("main",$this->frm);
		
		// criar botões para movimentos...
		
		$this->build("p4a_button","btnNewMovimentoEntrada")
			->setLabel(__("Criar Entrada"),true)
			->setWidth("200")
			->setHeight("200")
			->implement("onClick",$this,"criarEntrada");
			
		$this->build("p4a_button","btnNewMovimentoSaida")
			->setLabel(__("Criar Saida"),true)
			->setWidth("200")
			->setHeight("200")
			->implement("onClick",$this,"criarSaida");

		$this->build("p4a_button","btnNewMovimentoTransferencia")
			->setLabel(__("Criar Transferencia"),true)
			->setWidth("200")
			->setHeight("200")
			->implement("onClick",$this,"criarTransferencia");
			
		$this->build("p4a_fieldset","fsetMovimentos")
			->setLabel(__("Movimentos"))
			->setWidth(800)
			->anchor($this->btnNewMovimentoEntrada)
			->anchorLeft($this->btnNewMovimentoSaida)
			->anchorLeft($this->btnNewMovimentoTransferencia);
			
		$this->frm->anchor($this->fsetMovimentos);
		
		$this->build("p4a_quit_toolbar","toolbar");
		
		$this->display("top",$this->toolbar);
		$this->display("menu", condgest::singleton()->menu);
		}
		
	function criarEntrada()
		{
		if ( $this->srcSaldoCaixa->fields->cd_caixa->getValue() > 0 )
			{
			condgest::singleton()->openPopup("caixa_movimento_entrada");
			
			condgest::singleton()->active_mask->setCaixa($this->srcSaldoCaixa->fields->cd_caixa->getValue());
			
			}
		else
			{
			$this->info(__("Selecione um caixa antes de continuar!"));
			}
		}
		
	function criarSaida()
		{
		if ( $this->srcSaldoCaixa->fields->cd_caixa->getValue() > 0 )
			{
			condgest::singleton()->openPopup("caixa_movimento_saida");
			
			condgest::singleton()->active_mask->setCaixa($this->srcSaldoCaixa->fields->cd_caixa->getValue());
			
			}
		else
			{
			$this->info(__("Selecione um caixa antes de continuar!"));
			}
		}
		
	function criarTransferencia()
		{
		if ( $this->srcSaldoCaixa->fields->cd_caixa->getValue() > 0 )
			{
			condgest::singleton()->openPopup("caixa_movimento_transferencia");
			
			condgest::singleton()->active_mask->setCaixa($this->srcSaldoCaixa->fields->cd_caixa->getValue());
			
			}
		else
			{
			$this->info(__("Selecione um caixa antes de continuar!"));
			}
		}
		
	function verLancamentos()
		{
		if ( $this->srcSaldoCaixa->fields->cd_caixa->getValue() > 0 )
			{
			condgest::singleton()->openPopup("verLancamentosCaixa");
			
			condgest::singleton()->active_mask->setCaixa($this->srcSaldoCaixa->fields->cd_caixa->getValue());
			
			}
		else
			{
			$this->info(__("Selecione um caixa antes de continuar!"));
			}
		}
		
	function verRelatorios()
		{
		condgest::singleton()->openMask("caixa_movimento_relatorios");
		}
		
	}
	