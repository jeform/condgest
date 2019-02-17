<?php
class condgest extends p4a
	{
	function __construct()
		{
		parent::__construct();
		
		$this->montarMenu();
		
		$this->set_tipo_pessoa();
		$this->set_admin_cadastradas();
		$this->set_tipo_entidade_pessoa();
		$this->set_condominios();	
		$this->tipo_imovel();		
		$this->set_tipo_condominio();
		$this->set_unidade();
		$this->set_hidrometro();
		$this->set_sigla_estado();
		$this->set_natureza_conta();
		$this->set_conta_banco();	
		$this->set_plano_conta();	
		$this->set_documentos();
		$this->set_caixas();
		$this->set_hist_padrao();
		$this->setStatusContasPagar();
		$this->setStatusContasReceber();
		$this->setTipoCategoria();
		$this->set_categorias();
		$this->set_entrada_categorias();
		$this->set_saida_categorias();	
		$this->set_itens_cobranca();
		$this->set_tipo_cobranca();
		$this->setTipoTaxaCondominial();
		$this->setTipoBaixaBoleto();
		$this->setTipoDocumentoAcordo();
		$this->set_cargo_conselho();
		$this->set_pessoas();		
		$this->abrirTelaLogin();
		$this->setStatusBoleto();
		$this->setTipoParametro();
		$this->setParamCobranca();
		$this->recuperarLoginUsuario();
		}
		

	function abrirTelaLogin()
		{
		$this->openMask("cadastro_login");
		
		$this->intercept($this->active_mask,'onLogin',"login_acesso");
		
		}
		
	function abrirTelaMenu($obj_menu)
		{
		$this->openMask($obj_menu->getName());
		}
		
	function login_acesso()
		{		
		$login = $this->active_mask->username->getNewValue();
		$password = $this->active_mask->password->getNewValue();
		
		$this->build("p4a_db_source","user_login")
			->setTable("tbl_usuarios")
			->setWhere("login = '{$login}' and password = '{$password}' and st_usuario = 1")
			->Load()
			->firstRow();
		
		if ( $this->user_login->getNumRows() == 1 )
			{
			$this->openMask("entradaSistema");
			$this->messageInfo(__("Seja bem-vindo ao Sistema de Gestão de Condomínio"));
			}
		else
			{
			$this->messageError(__("Falha no Login"));
			}
		}
		
	function montarMenu()
		{
		$this->build("p4a_menu","menu");
		
		//Módulo de Cadastro
		$this->menu->addItem("mn_cadastro")->setLabel(__("Cadastro"));
		
		//Módulo de Cadastro Administrativo
		$this->menu->items->mn_cadastro->addItem("admin")->setLabel(__("Administrativo"));
		$this->menu->items->mn_cadastro->items->admin->addItem("cadastro_condominio")->setLabel(__("Condomínio"))->implement("onClick",$this,"abrirTelaMenu");
		$this->menu->items->mn_cadastro->items->admin->addItem("cadastro_conselho")->setLabel(__("Conselho Consultivo"))->implement("onClick",$this,"abrirTelaMenu");
		$this->menu->items->mn_cadastro->items->admin->addItem("cadastro_unidades")->setLabel(__("Unidades"))->implement("onClick",$this,"abrirTelaMenu");
		$this->menu->items->mn_cadastro->items->admin->addItem("cadastro_hidrometro")->setLabel(__("Hidrômetro"))->implement("onClick",$this,"abrirTelaMenu");
		$this->menu->items->mn_cadastro->items->admin->addItem("cadastro_portaria")->setLabel(__("Portaria"))->implement("onClick",$this,"abrirTelaMenu");
		$this->menu->items->mn_cadastro->items->admin->addItem("cadastro_pessoa")->setLabel(__("Pessoas"))->implement("onClick",$this,"abrirTelaMenu");
		
		//Módulo de Cadastro Financeiro
		$this->menu->items->mn_cadastro->addItem("cadastro_financ")->setLabel(__("Financeiro"));
		$this->menu->items->mn_cadastro->items->cadastro_financ->addItem("cadastro_bancos")->setLabel(__("Bancos"))->implement("onClick",$this,"abrirTelaMenu");
		$this->menu->items->mn_cadastro->items->cadastro_financ->addItem("cadastro_conta_corrente")->setLabel(__("Conta Corrente"))->implement("onClick",$this,"abrirTelaMenu");
		$this->menu->items->mn_cadastro->items->cadastro_financ->addItem("cadastro_hist_padrao")->setLabel(__("Histórico Padrão"))->implement("onClick",$this,"abrirTelaMenu");
		$this->menu->items->mn_cadastro->items->cadastro_financ->addItem("cadastro_categorias")->setLabel(__("Categorias"))->implement("onClick",$this,"abrirTelaMenu");
		$this->menu->items->mn_cadastro->items->cadastro_financ->addItem("cobranca")->setLabel("Cobrança");
		$this->menu->items->mn_cadastro->items->cadastro_financ->items->cobranca->addItem("cadastro_itens_cobranca_boleto")->setLabel(__("Itens Cobrança Boleto"))->implement("onClick",$this,"abrirTelaMenu");
		$this->menu->items->mn_cadastro->items->cadastro_financ->items->cobranca->addItem("correcao_inpc")->setLabel("Correção Monetária")->implement("onClick",$this,"abrirTelaMenu");
		
		//Módulo Financeiro
		$this->menu->addItem("mn_financeiro")->setLabel(__("Financeiro"));
		$this->menu->items->mn_financeiro->addItem("contas")->setLabel(__("Contas"));
		$this->menu->items->mn_financeiro->items->contas->addItem("contas_pagar")->setLabel(__("Pagar"))->implement("onClick",$this,"abrirTelaMenu");
		$this->menu->items->mn_financeiro->items->contas->addItem("contas_receber")->setLabel(__("Receber"))->implement("onClick",$this,"abrirTelaMenu");

		$this->menu->items->mn_financeiro->addItem("caixa")->setLabel(__("Caixas"));
		$this->menu->items->mn_financeiro->items->caixa->addItem("controle_caixa")->setLabel(__("Controle de Caixa"))->implement("onClick",$this,"abrirTelaMenu");
		$this->menu->items->mn_financeiro->items->caixa->addItem("caixa_movimento")->setLabel(__("Movimento"))->implement("onClick",$this,"abrirTelaMenu");
		$this->menu->items->mn_financeiro->items->caixa->addItem("fechamento_mensal")->setLabel(__("Fechamento Mensal"))->implement("onClick",$this,"abrirTelaMenu");
		
		$this->menu->items->mn_financeiro->addItem("recibos")->setLabel(__("Recibos"))->implement("onClick",$this,"abrirTelaMenu");
		
		$this->menu->items->mn_financeiro->addItem("processamento_conta_agua")->setLabel(__("Rateio do Consumo de Água"))->implement("onClick",$this,"abrirTelaMenu");					
		
		$this->menu->items->mn_financeiro->addItem("cobranca")->setLabel(__("Cobrança"));
		$this->menu->items->mn_financeiro->items->cobranca->addItem("processamento_boleto_lote")->setLabel(__("Taxa Condominial"))->implement("onClick",$this,"abrirTelaMenu");	
		$this->menu->items->mn_financeiro->items->cobranca->addItem("acordos")->setLabel("Acordos")->implement("onClick",$this,"abrirTelaMenu");
		
		//Módulo Relatórios
		$this->menu->addItem("mn_relatorio")->setLabel(__("Relatórios"));
		$this->menu->items->mn_relatorio->addItem("maskRelatorioPrestacaoContasMensal")->setLabel(__("Prestação de Contas - Mensal"))->implement("onClick",$this,"abrirTelaMenu");
		$this->menu->items->mn_relatorio->addItem("maskRelatorioPrestacaoContasAnual")->setLabel(__("Prestação de Contas - Anual"))->implement("onClick",$this,"abrirTelaMenu");
		$this->menu->items->mn_relatorio->addItem("maskRelatorioDemonstrativoReceitasDespesas")->setLabel(__("Receitas x Despesas"))->implement("onClick",$this,"abrirTelaMenu");
		$this->menu->items->mn_relatorio->addItem("boleto")->setLabel(__("Boletos"));
		$this->menu->items->mn_relatorio->items->boleto->addItem("maskRelatorioBaixaBoletos")->setLabel(__("Baixa"))->implement("onClick",$this,"abrirTelaMenu");
		$this->menu->items->mn_relatorio->items->boleto->addItem("maskRelatorioImportacaoPlanilhaBradesco")->setLabel(__("Importação Planilha Bradesco"))->implement("onClick",$this,"abrirTelaMenu");
		$this->menu->items->mn_relatorio->addItem("inadimplencia")->setLabel(__("Inadimplência"));
		$this->menu->items->mn_relatorio->items->inadimplencia->addItem("maskRelatorioInadimplencia")->setLabel(__("Inadimplência Detalhada"))->implement("onClick",$this,"abrirTelaMenu");
		$this->menu->items->mn_relatorio->items->inadimplencia->addItem("maskRelatorioPerfilInadimplencia")->setLabel(__("Perfil de Inadimplência"))->implement("onClick",$this,"abrirTelaMenu");
		$this->menu->items->mn_relatorio->items->inadimplencia->addItem("maskRelatorioPainelInadimplencia")->setLabel(__("Painel de Inadimplência"))->implement("onClick",$this,"abrirTelaMenu");
		$this->menu->items->mn_relatorio->items->inadimplencia->addItem("maskRelatorioUnidadesInadimplentes")->setLabel(__("Carta de Cobrança"))->implement("onClick",$this,"abrirTelaMenu");
		$this->menu->items->mn_relatorio->addItem("maskCertidaoNegativaDebitos")->setLabel(__("Certidão Negativa de Débitos"))->implement("onClick",$this,"abrirTelaMenu");
		
		$this->menu->addItem("mn_config")->setLabel(__("Configuração"));
		$this->menu->items->mn_config->addItem("parametros")->setLabel(__("Parametros"))->implement("onClick",$this,"abrirTelaMenu");
				
		$this->menu->addItem("alterarSenha")->setLabel(__("Alterar Senha"))->implement("onClick",$this,"abrirTelaMenu");
		
		$this->menu->addItem("saida")->setLabel(__("Sair"))->implement("onClick",p4a::singleton(),"restart");
		}
		
	function set_tipo_pessoa()
		{
		$arr_tipo_pessoa[] = array("tipo_pessoa"=>"PF","desc"=>"Pessoa Física");
		$arr_tipo_pessoa[] = array("tipo_pessoa"=>"PJ","desc"=>"Pessoa Jurídica");
		
		$this->build("p4a_array_source","arr_source_tipo_pessoa")
		->Load($arr_tipo_pessoa)
		->setPk("tipo_pessoa");	
		}	
	
	function set_natureza_conta()
		{
		$arr_natureza_conta[] = array("natureza_conta"=>"D", "desc"=>"Devedora");
		$arr_natureza_conta[] = array("natureza_conta"=>"C", "desc"=>"Credora");
		
		$this->build("p4a_array_source","src_natureza_conta")
			->Load($arr_natureza_conta)
			->setPk("natureza_conta");	
		}
		
	function set_tipo_condominio()
		{
		$arr_tipo_condominio[] = array("tipo_condominio"=>0, "desc"=>"Lote");
		$arr_tipo_condominio[] = array("tipo_condominio"=>1, "desc"=>"Casa");
		$arr_tipo_condominio[] = array("tipo_condominio"=>2, "desc"=>"Apartamento");
		
		$this->build("p4a_array_source","src_tipo_condominio")
			->Load($arr_tipo_condominio)
			->setPk("tipo_condominio");	
		}
		
	function set_sigla_estado()
		{
		$this->build("p4a_db_source","src_estado_brasileiro")
			->setTable("tbl_estados_brasileiros")
			->Load();	
		}
		
	function tipo_imovel()
		{
		$this->build("p4a_db_source","src_tipo_imovel")
			->setTable("tbl_tipo_imovel")
			->Load();
		}
		
	function set_tipo_entidade_pessoa()
		{
		$this->build("p4a_db_source","src_tipo_entidade_pessoa")
			->setTable("tbl_tipo_pessoa")
			->Load();
		}
		
	function set_admin_cadastradas()
		{
		$this->build("p4a_db_source","src_admin_cadastrados")
			->setTable("tbl_pessoas")
			->setWhere("	cd_tipo_pessoa = 4")
			->Load();
		}
		
	function set_condominios()
		{
		$this->build("p4a_db_source","src_condominios_disponiveis")	
			->setFields(array("cd_condominio","concat_ws(' - ',cd_condominio,nm_condominio)"=>"desc_condominio"))
			->setTable("tbl_condominio")
			->setwhere("cd_condominio in ( select cd_condominio from tbl_condominio)")
			->Load();
		}		

	function set_conta_banco()
		{
		$this->build("p4a_db_source","src_conta_banco")	
			->setFields(array("cd_conta_corrente","concat_ws(' - ',cod_banco,ag_banco,nr_conta_banco)"=>"conta_cadastrada"))
			->setTable("tbl_conta_corrente")
			->Load();
		}
		
	function set_unidade()
		{
		$this->build("p4a_db_source","src_unidade")	
		->setFields(array("cd_unidade","concat('Unidade ',desc_unidade)"=>"unidade"))
		->setTable("tbl_unidades")
		->setwhere("cd_unidade in ( select cd_unidade from tbl_unidades)")
		->Load();
		}
		
	function set_hidrometro()
		{
		$this->build("p4a_db_source","src_hidro")
		->setTable("tbl_hidrometro")
		->setWhere("	st_hidro = 1")
		->Load();
		}	
		
	function set_plano_conta()
		{
		$this->build("p4a_db_source","src_plano_conta")
			->setFields(array("cd_plano_conta","concat_ws(' - ',cd_estrutural_conta,desc_conta)"=>"conta_contabil"))
			->setTable("tbl_plano_contas")
			->Load();	
		}
		
	function set_documentos()
		{
		$this->build("p4a_db_source","src_documentos_cadastrados")	
			->setFields(array("cd_documento","(ds_documento)"=>"desc_documento"))
			->setTable("documentos")
			->Load();
		}	

	function set_caixas()
		{
		$this->build("p4a_db_source","src_caixas_cadastrados")	
			->setFields(array("cd_caixa","ds_caixa"=>"desc_caixa"))
			->setTable("caixa")
			->setwhere("	st_caixa = 1")
			->Load();
		}
		
	function set_hist_padrao()
		{
		$this->build("p4a_db_source","src_hist_padrao")	
			->setFields(array("cd_hist_padrao","ds_hist_padrao"=>"desc_hist_padrao"))
			->setTable("hist_padrao")
			->setwhere("	st_hist_padrao = 1")
			->Load();
		}
		
	function setStatusContasPagar()
		{
		$arrStatusContasPagar[] = array("cd_st_pagamento"=>1,"dsc_st_pagamento"=>"Pendente");
		$arrStatusContasPagar[] = array("cd_st_pagamento"=>2,"dsc_st_pagamento"=>"Paga");
		
		$this->build("P4A_array_source","srcStatusContasPagar")
			->Load($arrStatusContasPagar)
			->setPk("cd_st_pagamento");
		}

	function setTipoDocumentoAcordo()
		{
		$arrTpDocumentoAcordo[] = array("cd_documento_acordo"=>1,"dsc_st_doc_acordo"=>"Depósito bancário");
		$arrTpDocumentoAcordo[] = array("cd_documento_acordo"=>2,"dsc_st_doc_acordo"=>"Boleto");
		$arrTpDocumentoAcordo[] = array("cd_documento_acordo"=>3,"dsc_st_doc_acordo"=>"Cheque");
	
		$this->build("P4A_array_source","src_documento_acordo")
			->Load($arrTpDocumentoAcordo)
			->setPk("cd_documento_acordo");
		}	
		
	function setStatusContasReceber()
		{
		$arrStatusContasReceber[] = array("cd_st_recebimento"=>1,"dsc_st_recebimento"=>"Pendente");
		$arrStatusContasReceber[] = array("cd_st_recebimento"=>2,"dsc_st_recebimento"=>"Recebida");
		
		$this->build("P4A_array_source","srcStatusContasReceber")
			->Load($arrStatusContasReceber)
			->setPk("cd_st_recebimento");
		}	
		
	function setTipoCategoria()
		{
		$arrTipoCategoria[] = array("tp_categoria"=>1,"dsc_tp_categoria"=>"Entrada");
		$arrTipoCategoria[] = array("tp_categoria"=>2,"dsc_tp_categoria"=>"Saída");
		
		$this->build("P4A_array_source","src_tipo_categoria")
			->Load($arrTipoCategoria)
			->setPk("tp_categoria");
		}	
		
	function setTipoTaxaCondominial()
		{
		$arrTipoTaxaCondominial[] = array("tp_tx_condominial"=>1,"dsc_tp_taxa_condominial"=>"Ordinária");
		$arrTipoTaxaCondominial[] = array("tp_tx_condominial"=>2,"dsc_tp_taxa_condominial"=>"Extraordinária");
	
		$this->build("P4A_array_source","src_tipo_taxa_condominial")
		->Load($arrTipoTaxaCondominial)
		->setPk("tp_tx_condominial");
		}	

	function setTipoBaixaBoleto()
		{
		$arrTipoBaixaBoleto[] = array("tp_baixa_boleto"=>0,"dsc_tp_baixa_boleto"=>"Aberto");
		$arrTipoBaixaBoleto[] = array("tp_baixa_boleto"=>1,"dsc_tp_baixa_boleto"=>"Fechado");
		$arrTipoBaixaBoleto[] = array("tp_baixa_boleto"=>3,"dsc_tp_baixa_boleto"=>"Parcialmente Fechado");

		$this->build("P4A_array_source","src_tipo_baixa_boleto")
			->Load($arrTipoBaixaBoleto)
			->setPk("tp_baixa_boleto");
		}
 	
		function set_categorias()
		{
			$this->build("p4a_db_source","src_categorias")
				->setTable("tbl_categorias")
				->setFields(array("cd_categoria","concat_ws(' - ',plano_contas,ds_categoria)"=>"categorias"))
				->addOrder("categorias")
				->Load();
		}
				
	function set_entrada_categorias()
		{
		$this->build("p4a_db_source","src_entrada_categoria")
			->setTable("tbl_categorias")
			->setWhere("	tp_categoria = 1")
			->Load();
		}	
		
	function set_saida_categorias()
		{
		$this->build("p4a_db_source","src_saida_categoria")
			->setTable("tbl_categorias")
			->setWhere("	tp_categoria = 2")
			->Load();
		}

	function set_tipo_cobranca()
		{
		$this->build("p4a_db_source","src_tipo_despesa")
			->setTable("tbl_tipo_despesa")
			->setFields(array("cd_tipo_despesa","ds_tipo_despesa"=>"desc_cadastro_cobranca"))
			->setWhere("st_tipo_despesa 	= 1")
			->setPK("cd_tipo_despesa")
			->Load();	
		}	
		
	function set_itens_cobranca()
		{
		$this->build("p4a_db_source","src_itens_cobranca")
			->setFields(array("cd_item_cobranca","ds_item_cobranca"=>"desc_item_cobranca"))
			->setTable("tbl_itens_cobranca_boleto")
			->setWhere("	st_item_cobranca = 1")
			->Load();	
		}

	function set_cargo_conselho()
		{
		$this->build("p4a_db_source","src_cargo_conselho")
			->setTable("tbl_cargo_conselho")
			->setWhere("	st_cargo_conselho = 1")
			->Load();	
		}	
		
	function set_pessoas()
		{
		$this->build("p4a_db_source","srcPessoas")
			->setTable("tbl_pessoas")
			->setWhere("	st_pessoa = 1")
			->Load();
		}
	
	/**
	 * Status do boleto
	 */
	function setStatusBoleto()
		{
		$arr_status_boleto[] = array("cd_status"=>"0", "descStatus"=>"Em aberto");
		$arr_status_boleto[] = array("cd_status"=>"1", "descStatus"=>"Fechado");
		
		$this->build("p4a_array_source","srcStatusBoleto")
			->Load($arr_status_boleto)
			->setPk("cd_status");
		}
		
	function setTipoParametro()
		{
		$arr_tp_param[] = array("cd_param"=>"0", "descParam"=>"Gerais");
		$arr_tp_param[] = array("cd_param"=>"1", "descParam"=>"Cobrança");
		
		$this->build("p4a_array_source","srcTpParametros")
			->Load($arr_tp_param)
			->setPk("cd_param");
		}

	function setParamCobranca()
		{
		
		$this->build("p4a_db_source","srcParamCobranca")
			->setTable("parametros")
			->setWhere(" tp_param = 1 ")
			
			->Load();
		}
		
	function recuperarLoginUsuario(){
		$this->build("p4a_db_source","srcLoginUsuario")
			->setTable("tbl_usuarios")
			->setWhere("st_usuario = 1")
			->Load();
	}
			
	/**
	 * 
	 * Enter description here ...
	 * @param string $nmTabelaRelac
	 * @param string $nmDominio
	 * @return array
	 */
	function getValueDominio($nmTabelaRelac,$nmDominio)
		{
		
		$arrDominios = p4a_db::singleton()->getAll("select cd_dominio, txt_valor from dominios where nm_tabela_relac = '{$nmTabelaRelac}' and nm_dominio = '{$nmDominio}'");

		$arrRetorno = $arrDominios;
		
		return $arrRetorno;
		}
		
	/**
	 * (non-PHPdoc)
	 * @see classes/p4a-3.2.2/p4a/objects/P4A::singleton()
	 * 
	 * @return condgest
	 */
    static function singleton($class_name = "p4a")
    	{
    	return parent::singleton($class_name);
    	}
    	
    function showPrevMask($destroy=true)
    	{
    	parent::showPrevMask(true);
    	}
    	
    function getParametro($nmParametro)
    	{
    	$vlParametro = P4A_DB::singleton()->fetchOne("select vl_param from parametros where nm_param = ?",array($nmParametro));
    	
    	if ( $vlParametro == "" )
    		{
    		throw new P4A_Exception("Valor do parametro {$nmParametro} não encontrado!", 99100);
    		}
    	
    	return $vlParametro;
    	}
	}