<?php
class classBradescoTransacao
	{
	
	/**
	 * 
	 * Enter description here ...
	 * @param $nrCarteira
	 * @param $nrAgenciaSemDigito
	 * @param $nrContaCorrenteSemDigito
	 * @param $nrDigitoContaCorrente
	 * @param $assinaturaSistema
	 * @param $stMulta
	 * @param $percentualMulta
	 * @param $nossoNumero
	 * @param $dvNossoNumero
	 * @param $descontoBonificacaoDia
	 * @param $stEmissaoBanco
	 * @param $stDebitoAutomatico
	 * @param $nrDocumento
	 * @param $dataVencimento
	 * @param $valorTitulo
	 * @param $vlDiaAtraso
	 * @param $dtLimiteDesconto
	 * @param $vlDesconto
	 * @param $identificacaoSacado
	 * @param $nomeSacado
	 * @param $enderecoSacado
	 * @param $mensagem1
	 * @param $nrCEP
	 * @param $mensagem2
	 * @param $nrRegistroArquivo
	 */	
	public static function getStringTransacao(
										$nrCarteira, 
										$nrAgenciaSemDigito, 
										$nrContaCorrenteSemDigito,
										$nrDigitoContaCorrente, 
										$assinaturaSistema, 
										$stMulta = 0,
										$percentualMulta=null,
										$nossoNumero=null,
										$dvNossoNumero=null,
										$descontoBonificacaoDia=null,
										$stEmissaoBanco=1,
										$stDebitoAutomatico="N",
										$nrDocumento,
										$dataVencimento,
										$valorTitulo,
										$vlDiaAtraso=0,
										$dtLimiteDesconto,
										$vlDesconto,
										$identificacaoSacado,
										$nomeSacado,
										$enderecoSacado,
										$mensagem1,
										$nrCEP,
										$mensagem2,
										$nrRegistroArquivo
										)
		{
		
			
		$strRetorno = "";
		
		$strRetorno.= "1";														// Identificação do Registro 001
		$strRetorno.= "     ";													// Codigo da Agencia do Sacado - Debito em conta 005
		$strRetorno.= " ";														// Digito Agencia Sacado - Debito em conta 001
		$strRetorno.= "     ";													// Razão da Conta do sacado - Debito em conta 005
		$strRetorno.= "       ";												// Conta Corrente Sacado - Debito em Conta 007
		$strRetorno.= " ";														// Digito conta corrente sacado - Debito em conta 001
		$strRetorno.= "0".														// 0
					  str_pad($nrCarteira, 3,"0",STR_PAD_LEFT).					// Carteira
					  str_pad($nrAgenciaSemDigito, 5,"0",STR_PAD_LEFT). 		// Agencia Sem digito
					  str_pad($nrContaCorrenteSemDigito,7,"0",STR_PAD_LEFT).	// CC sem digito
					  $nrDigitoContaCorrente;									// Digito CC | Identificação Empresa - 0+Carteira+Agencia+Conta 017 
		$strRetorno.= substr($assinaturaSistema, 0,25);							// Numero controle Participante 025 - Assinatura do sistema para validar no retorno
		$strRetorno.= "237";													// Codigo do banco a ser debitado na camara de compensacao 003
		$strRetorno.= $stMulta;													// Campo de Multa - 2 Considerar percentual, 0 Sem Multa 001
		$strRetorno.= str_pad($percentualMulta, 4,"0",STR_PAD_LEFT);			// Percentual de multa se o campo anterior for igual a 2 004
		$strRetorno.= $nossoNumero==null?str_repeat("0", 11):str_pad($nossoNumero,11,"0",STR_PAD_LEFT); // Identificação do Titulo no banco 011
		$strRetorno.= $dvNossoNumero==null?str_repeat("0", 1):$dvNossoNumero;	// Digito Verificador do Nosso numero 001
		$strRetorno.= str_pad($descontoBonificacaoDia,10,"0",STR_PAD_LEFT);		// Valor do Desconto bonificação Dia 010
		$strRetorno.= $stEmissaoBanco;											// Condição para Emissao da Papeleta de Cobrança 001 
		$strRetorno.= $stDebitoAutomatico;										// Identif. se emite Boleto para Debito Automatico 001
		$strRetorno.= str_repeat(" ", 10);										// Identificação da Operação do banco 010 - Brancos
		$strRetorno.= " ";														// Identificacão Rateio Crédito 001 - Somente se empresa contratou
		$strRetorno.= 2;														// Endereçamento do Aviso de Débito Automático em Conta Corrente - 2 não emite aviso
		$strRetorno.= "  ";														// Branco 002
		$strRetorno.= "01";														// Identificação de Ocorrência - 002 - 01 Remessa
		$strRetorno.= substr($nrDocumento,0,10);								// Numero do documento 010
		$strRetorno.= substr(date("dmy",$dataVencimento),0,6);					// Data de vencimento 006 Formato DDMMYY dmy
		$strRetorno.= str_pad(str_ireplace(array(".",","), "", $valorTitulo),13,"0",STR_PAD_LEFT); //Valor do Titulo 013 - Sem pontos e virgula
		$strRetorno.= str_repeat("0", 3);										// Banco encarregado da cobrança 003 - Preencher com zeros
		$strRetorno.= str_repeat("0", 5);										// Agencia depositaria - 005 - Preencher com zeros
		$strRetorno.= "01";														// Especie de Titulo 002 - 01 - Duplicata
		$strRetorno.= "N";														// Identificação - 001 - Sempre N
		$strRetorno.= substr(date("dmy",$dataEmissao),0,6);						// Data de emissão do Titulo 006 Formato DDMMYY dmy
		$strRetorno.= "00";														// 1º Instrução Protesto - Inativo;
		$strRetorno.= "00";														// 2º Instrução Protesto - Inativo;
		$strRetorno.= str_pad(str_ireplace(array(",","."),"",$vlDiaAtraso), 13,"0",STR_PAD_LEFT); // Valor a ser cobrado por dia de atraso - 013 - Mora por dia de atraso
		$strRetorno.= substr(date("dmy",$dataVencimento),0,6);					// Data Limite para concessão desconto - 006 - Formato DDMMYY dmy
		$strRetorno.= str_pad(str_ireplace(array(",","."),"",$vlDesconto), 13,"0",STR_PAD_LEFT); // Valor do desconto 013
		$strRetorno.= str_repeat("0", 13);										// Valor do IOF - Somente seguro
		$strRetorno.= str_repeat("0", 13);										// Valor do Abatimento a ser concedido ou cancelado - 013
		$strRetorno.= "01";														// Identificacao do Tipo de inscrição do Sacado - 01-CPF, 02-CNPJ
		$strRetorno.= str_pad(str_ireplace(array(".","-","/"), "", $identificacaoSacado),14,"0",STR_PAD_LEFT);// Identificação Sacado - Nr CPF
		$strRetorno.= substr($nomeSacado,0,40);									// Nome do sacado 040
		$strRetorno.= substr($enderecoSacado,0,40);								// Endereco Completo sacado 040
		$strRetorno.= substr($mensagem1, 0,12);									// 1ª Mensagem 012
		$strRetorno.= substr(str_replace(array(".","-"), "", $nrCEP), 0,5);		// CEP - 005
		$strRetorno.= substr(str_replace(array(".","-"), "", $nrCEP), 5,3);		// CEP Sufixo 003
		$strRetorno.= substr($mensagem2, 0,60);									// 2ª Mensagem 060
		$strRetorno.= str_pad($nrRegistroArquivo, 6,"0",STR_PAD_LEFT);			// Nº Sequencial do Registro
		$strRetorno.= chr(13).chr(10);											// Fim de Linha 0D 0A ascii 13 + ascii 10
		
		return $strRetorno;
		}
	}