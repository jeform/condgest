<?php
class classBradescoTrailer
	{

	/**
	 * 
	 * Enter description here ...
	 * @param $nrRegistro
	 */
	public static function getStringTrailer($nrRegistro)
		{
		$strRetorno = "";

		$strRetorno.= "9";												// Identificação do registro
		$strRetorno.= str_repeat(" ", 393);								// Brancos
		$strRetorno.= str_pad($nrRegistro,6,"0",STR_PAD_LEFT);			// Nº Sequencial do registro do Ultimo registro
		$strRetorno.=  chr(13).chr(10);									// Fim de Linha 0D 0A ascii 13 + ascii 10
		
		return $strRetorno;
		}
	}