<?php

require '../init.php';

$identificador = $_GET["id"];

if ( $identificador == "" )
	{
	echo "sem dados!";
	
	exit;
	}
	
	
// buscar dados do boleto, baseado no codigo do boleto individual...

$sqlBoleto = "
			select
				a.cd_unidade,
				a.cd_boleto_mes,
				b.mes_ano_referencia,
				a.dt_processamento,
				a.dt_vencimento,
				d.nm_pessoa,
				d.nr_identif,
				d.txt_endereco_correspondencia,
				d.txt_bairro_correspondencia,
				d.txt_cidade_correspondencia,
				e.sigla_estado,
				d.cep_correspondencia,
				( select sum(y.vlr_item_boleto) from tbl_boleto_mes_unidade_itens_cobranca y where y.cd_boleto_mes_unidade = a.cd_boleto_mes_unidade ) as vlr_boleto
			from
				tbl_boleto_mes_unidade a,
				tbl_boleto_mes b,
				tbl_unidades c,
				tbl_pessoas d,
				tbl_estados_brasileiros e
			where
				a.cd_unidade = c.cd_unidade
			and 	a.cd_boleto_mes = b.cd_boleto_mes
			and 	c.cd_pessoa = d.cd_pessoa
			and 	d.cd_estado = e.cd_estado
			and 	a.cd_boleto_mes_unidade = '{$identificador}'

			";

$arrInfoBoleto = P4A_DB::singleton()->fetchRow("select * from tbl_info_boleto");

$arrDadosBoleto = P4A_DB::singleton()->fetchRow($sqlBoleto);

foreach($arrDadosBoleto as $chave =>$valor);
	{
	$arrDadosBoleto[$chave] = utf8_decode($valor);
	}

	

// ------------------------- DADOS DIN�MICOS DO SEU CLIENTE PARA A GERA��O DO BOLETO (FIXO OU VIA GET) -------------------- //
// Os valores abaixo podem ser colocados manualmente ou ajustados p/ formul�rio c/ POST, GET ou de BD (MySql,Postgre,etc)	//
	
// DADOS DO BOLETO PARA O SEU CLIENTE
//$dias_de_prazo_para_pagamento = 5;
$taxa_boleto = 0;
//$data_venc = date("d/m/Y", time() + ($dias_de_prazo_para_pagamento * 86400));  // Prazo de X dias OU informe data: "13/04/2006"; 

$data_venc = formatarDataAplicacao($arrDadosBoleto["dt_vencimento"]);
$data_proc = formatarDataAplicacao($arrDadosBoleto["dt_processamento"]);

$valor_cobrado = $arrDadosBoleto["vlr_boleto"]; // Valor - REGRA: Sem pontos na milhar e tanto faz com "." ou "," ou com 1 ou 2 ou sem casa decimal
$valor_cobrado = str_replace(",", ".",$valor_cobrado);
$valor_boleto=number_format($valor_cobrado+$taxa_boleto, 2, ',', '');

list($mes_referencia,$ano_referencia) = explode("/",$arrDadosBoleto["mes_ano_referencia"]);

$dadosboleto["nosso_numero"] = str_pad($arrDadosBoleto["cd_unidade"],2,"0",STR_PAD_LEFT).str_pad($mes_referencia, 2,"0",STR_PAD_LEFT).$ano_referencia;  // Nosso numero sem o DV - REGRA: M�ximo de 11 caracteres!
$dadosboleto["numero_documento"] = $dadosboleto["nosso_numero"];	// Num do pedido ou do documento = Nosso numero
$dadosboleto["data_vencimento"] = $data_venc; // Data de Vencimento do Boleto - REGRA: Formato DD/MM/AAAA
$dadosboleto["data_documento"] = $data_proc; // Data de emissao do Boleto
$dadosboleto["data_processamento"] = $data_proc; // Data de processamento do boleto (opcional)
$dadosboleto["valor_boleto"] = $valor_boleto; 	// Valor do Boleto - REGRA: Com virgula e sempre com duas casas depois da virgula

// DADOS DO SEU CLIENTE
$dadosboleto["sacado"] = $arrDadosBoleto["nm_pessoa"]." CPF: ".$arrDadosBoleto["nr_identif"];
$dadosboleto["endereco1"] = $arrDadosBoleto["txt_endereco_correspondencia"]." - ".$arrDadosBoleto["txt_bairro_correspondencia"];//Endere�o do seu Cliente";
$dadosboleto["endereco2"] = utf8_decode($arrDadosBoleto["txt_cidade_correspondencia"]." - ".$arrDadosBoleto["sigla_estado"]." - CEP: ".$arrDadosBoleto["cep_correspondencia"]); //Cidade - Estado -  CEP: 00000-000";

// INFORMACOES PARA O CLIENTE
$dadosboleto["demonstrativo1"] = utf8_decode("Boleto Ref. Mês/Ano: ".$mes_referencia."/".$ano_referencia." - Unidade ".$arrDadosBoleto["cd_unidade"]);
$dadosboleto["demonstrativo2"] = "";
$dadosboleto["demonstrativo3"] = utf8_decode("CondGest - Gestão de Condomínio");
$dadosboleto["instrucoes1"] = utf8_decode($arrInfoBoleto["instrucao_1"]); 	//Campo de Instrucao 1
$dadosboleto["instrucoes2"] = utf8_decode($arrInfoBoleto["instrucao_2"]);	//Campo de Instrucao 2
$dadosboleto["instrucoes3"] = utf8_decode($arrInfoBoleto["instrucao_3"]);	//Campo de Instrucao 3
$dadosboleto["instrucoes4"] = utf8_decode($arrInfoBoleto["instrucao_4"]);	//Campo de Instrucao 4

// DADOS OPCIONAIS DE ACORDO COM O BANCO OU CLIENTE
$dadosboleto["quantidade"] = "001";
$dadosboleto["valor_unitario"] = $valor_boleto;
$dadosboleto["aceite"] = "N";		
$dadosboleto["especie"] = "R$";
$dadosboleto["especie_doc"] = "DM";


// ---------------------- DADOS FIXOS DE CONFIGURACAO DO SEU BOLETO --------------- //

// DADOS DA SUA CONTA - Bradesco
$dadosboleto["agencia"] = $arrInfoBoleto["agencia"];			//Num da agencia, sem digito
$dadosboleto["agencia_dv"] = $arrInfoBoleto["agencia_dv"];		//Digito do Num da agencia
$dadosboleto["conta"] = $arrInfoBoleto["conta"];				//Num da conta, sem digito
$dadosboleto["conta_dv"] = $arrInfoBoleto["agencia_dv"];		//Digito do Num da conta

// DADOS PERSONALIZADOS - Bradesco
$dadosboleto["conta_cedente"] = $arrInfoBoleto["conta_cedente"];		//ContaCedente do Cliente, sem digito (Somente Numeros)
$dadosboleto["conta_cedente_dv"] = $arrInfoBoleto["conta_cedente_dv"]; 	// Digito da ContaCedente do Cliente
$dadosboleto["carteira"] = $arrInfoBoleto["carteira"];					// Codigo da Carteira: pode ser 06 ou 03


// dados do condominio...
$arrDadosCondominio = P4A_DB::singleton()->fetchRow("select 
															a.nm_condominio, 
															a.nr_cnpj_condominio, 
															a.logradouro_condominio, 
															a.municipio_condominio, 
															b.sigla_estado, 
															a.nm_condominio 
													   from 
															tbl_condominio a, tbl_estados_brasileiros b 
													  where 
															a.uf_condominio = b.cd_estado");

// SEUS DADOS
$dadosboleto["identificacao"] = utf8_decode($arrDadosCondominio["nm_condominio"]." - CondGest - Gestão de Condominios");
$dadosboleto["cpf_cnpj"] = utf8_decode($arrDadosCondominio["nr_cnpj_condominio"]);
$dadosboleto["endereco"] = utf8_decode($arrDadosCondominio["logradouro_condominio"]);
$dadosboleto["cidade_uf"] = utf8_decode($arrDadosCondominio["municipio_condominio"]."/".$arrDadosCondominio["sigla_estado"]);
$dadosboleto["cedente"] = utf8_decode($arrDadosCondominio["nm_condominio"]);

include("include/funcoes_bradesco.php"); 
include("include/layout_bradesco.php");