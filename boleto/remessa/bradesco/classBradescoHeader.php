<?php
class classBradescoHeader
	{

	/**
	 * 
	 * Enter description here ...
	 * @param unknown_type $codigoEmpresa
	 * @param unknown_type $nomeEmpresa
	 * @param unknown_type $dataTS
	 * @param unknown_type $nrRemessa
	 */
	public static function getStringHeader($codigoEmpresa, $nomeEmpresa, $dataTS, $nrRemessa)
		{
		$strRetorno = "";
		
		$strRetorno.= "0"; 														// Identificação do Registro 001
		$strRetorno.= "1"; 														// Identificação do Arquivo Remessa 001 
		$strRetorno.= "REMESSA";												// Literal "REMESSA" 007
		$strRetorno.= "02";														// Codigo de Serviço 002
		$strRetorno.= "COBRANCA       "; 										// Literal Serviço "COBRANCA" 015
		$strRetorno.= str_pad($codigoEmpresa, 20,"0",STR_PAD_LEFT);				// Código da empresa 020 
		$strRetorno.= substr($nomeEmpresa, 0,30);								// Nome da Empresa 030
		$strRetorno.= "237";													// Código do Bradesco na camara de compensacao 003
		$strRetorno.= "Bradesco       ";										// Nome do banco por extenso 015
		$strRetorno.= date("dmy",$dataTS);										// Data da gravação do arquivo 006
		$strRetorno.= "        ";												// Branco 008
		$strRetorno.= "MX";														// Identificação do Sistema 002
		$strRetorno.= str_pad($nrRemessa, 7,"0",STR_PAD_LEFT);					// Nº Sequencial de Remessa 007
		$strRetorno.= str_repeat(" ", 277);										// Branco 277
		$strRetorno.= str_pad(1,6,"0",STR_PAD_LEFT);							// Nº Sequencial do registro de Um em Um 006
		$strRetorno.= chr(13).chr(10);											// Fim de Linha 0D 0A ascii 13 + ascii 10
		
		return $strRetorno;
		}
	
	
	}