<?php

/**
 * Retorna uma data em formato Y-m-d com a quantidade de dias adicionada
 * @param integer $dia
 * @param integer $mes
 * @param integer $ano
 * @param integer $qtdeDias
 * @return string
 */
function acrescentarDiasData($dia,$mes,$ano, $qtdeDias)
	{
	$data1 =  mktime(0,0,0,$mes,$dia,$ano);
	
	$data2 = strtotime("+{$qtdeDias} days",$data1);
	
	return date("Y-m-d",$data2);
	}
	
/**
 * Retorna uma data em formato d/m/Y com a quantidade de dias adicionada
 * @param integer $dia
 * @param integer $mes
 * @param integer $ano
 * @param integer $qtdeDias
 * @return string
 */
function acrescentarDiasData_1($dia,$mes,$ano, $qtdeDias)
	{
	$data1 =  mktime(0,0,0,$mes,$dia, $ano);
	
	$data2 = strtotime("+{$qtdeDias} days",$data1);
	
	return date("d/m/Y",$data2);
	}
	
/**
 * 
 * Acrestar mes(s) a uma data
 * @param integer $dia
 * @param integer $mes
 * @param integer $ano
 * @param integer $qtdeMes
 * @return string
 */
function acrescentarMesesDatas($dia,$mes,$ano,$qtdeMes)
	{
	$data1 = mktime(0,0,0,$mes,$dia,$ano);
	
	$data2 = strtotime("+{$qtdeMes} Month",$data1);
	
	return date("Y-m-d",$data2);
	}	
	
function subtrairMesesData($dia,$mes,$ano,$qtdeMes)
	{
	$data1 = mktime(0,0,0,$mes,$dia,$ano);
	
	$data2 = strtotime("-{$qtdeMes} Month",$data1);
	
	return date("Y-m-d",$data2);
	}	
		
/**
 * 
 * Desmontar em array uma data dada em formato Y-m-d
 * @param string $strDataYmd
 * @return array
 */
function desmontarDataYmd($strDataYmd)
	{
	$data1 = explode("-", $strDataYmd);
	
	return array($data1[0],$data1[1],$data1[2]);
	}
	
function desmontarDataYmd_1($strDataYmd)
	{
	$data1 = explode("/", $strDataYmd);
	
	return array($data1[0],$data1[1],$data1[2]);
	}	

function desmontarDatadmY($strDatadmY)
	{
	$data1 = explode("/",$strDatadmY);
	
	return array($data1[0],$data1[1],$data1[2]);
	}

function desmontarMesAnoReferencia($strMesAnoRef)
	{
	$data1 = explode("/", $strMesAnoRef);
	
	return array($data1[0],$data1[1]);
	}

function desmontarCdBoletoUnidade($strCdBoletoMesUnidade)
	{
	$data1 = explode(".",$strCdBoletoMesUnidade);

	return $data1[0].$data1[1];
	}	
	
function subtrairMesAnoRef($dia,$mes,$ano,$qtdeMes)
	{
	$data1 = mktime(0,0,0,$mes,$dia,$ano);
	
	$data2 = strtotime("-{$qtdeMes} Month",$data1);
	
	return date("m/Y",$data2);
	}	
	
function acrescenterMesAnoRef($dia,$mes,$ano,$qtdeMes)	
	{
	$data1 = mktime(0,0,0,$mes,$dia,$ano);
	
	$data2 = strtotime("+{$qtdeMes} Month",$data1);
	
	return date("m/Y",$data2);
	}	
	
function formatarDataAplicacao($strdataYMD="")
	{
	$arrData1 = explode("-", $strdataYMD);
	
	return (count($arrData1)>0 and $strdataYMD<> "")?($arrData1[2]."/".$arrData1[1]."/".$arrData1[0]):"";
		
	}
	
function formatarDataBanco($strdataDMY="")
	{
	$arrData1 = explode("/", $strdataDMY);
	
	return (count($arrData1)>0 and $strdataDMY<> "")?($arrData1[2]."-".$arrData1[1]."-".$arrData1[0]):"";
	}
	
function formatarDataBanco1($strdataDMY="")
	{
	$arrData1 = explode("-", $strdataDMY);
	
	return (count($arrData1)>0 and $strdataDMY<> "")?($arrData1[2]."/".$arrData1[1]."/".$arrData1[0]):"";
	}	
	
function formataValoresBanco($strValorFormatado)
	{
	// retirar os pontos...
	$strNovoValor = str_ireplace(".","",$strValorFormatado);
	
	// substituir a virgula por ponto
	$strNovoValor = str_ireplace(",",".",$strNovoValor);
	
	return floatval($strNovoValor);
	}
	
function formataValoresExibicao($strValorBanco)
	{
	$strNovoValor = number_format($strValorBanco,"2",",",".");
	
	return $strNovoValor;
	}	
	
function intervaloData($inicio, $fim)
	{
	list($diaInicio,$mesInicio,$anoInicio) = explode('/', $inicio);
	list($diaFim,$mesFim,$anoFim) = explode('/', $fim);

	$dataInicio = gregoriantojd($mesInicio,$diaInicio,$anoInicio);	
	$dataFim = gregoriantojd($mesFim,$diaFim,$anoFim);
	$diferenca = $dataFim -$dataInicio;
	
	return $diferenca;
	}	

function formata_data_extenso($strDate,$utilizaDiaSemana)
	{
	if($utilizaDiaSemana)
		// Array com os dia da semana em português;
		$arrDaysOfWeek = array('Domingo','Segunda-feira','Terça-feira','Quarta-feira','Quinta-feira','Sexta-feira','Sábado');
	
	// Array com os meses do ano em português;
	$arrMonthsOfYear = array(1 => 'Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro');
	// Descobre o dia da semana
	$intDayOfWeek = date('w',strtotime($strDate));
	// Descobre o dia do mês
	$intDayOfMonth = date('d',strtotime($strDate));
	// Descobre o mês
	$intMonthOfYear = date('n',strtotime($strDate));
	// Descobre o ano
	$intYear = date('Y',strtotime($strDate));
	// Formato a ser retornado
	
	if($utilizaDiaSemana)	
		return $arrDaysOfWeek[$intDayOfWeek] . ', ' . $intDayOfMonth . ' de ' . $arrMonthsOfYear[$intMonthOfYear] . ' de ' . $intYear;
	else 
		return $intDayOfMonth . ' de ' . $arrMonthsOfYear[$intMonthOfYear] . ' de ' . $intYear;
	}	
	
function retornaDataLiquidacao($strDate) // data no formato dd/mm/YY
	{
	list($dia,$mes,$ano) = desmontarDatadmY($strDate);
	$dtaux = acrescentarDiasData($dia, $mes, $ano, 2);
	$intDiaDaSemana = date('w',strtotime($dtaux));
	
	if ($intDiaDaSemana == 0) 	//domingo
		{
		list($ano,$mes,$dia) = desmontarDataYmd($dtaux);
		$dtLiquidacao = acrescentarDiasData($dia, $mes, $ano, 1);
		}
	elseif($intDiaDaSemana == 6)  //sábado
		{
		list($ano,$mes,$dia) = desmontarDataYmd($dtaux);
		$dtLiquidacao = acrescentarDiasData($dia, $mes, $ano, 2);
		}
	else 
		{
		$dtLiquidacao = $dtaux;
		}
	return $dtLiquidacao;
	}	
	
function alteraDiaUtilVencimento($strDate)
	{
	list($dia,$mes,$ano) = desmontarDataYmd_1($strDate);
	
	$dtaux = acrescentarDiasData($dia, $mes, $ano, 0);
	$intDiaDaSemana = date('w',strtotime($dtaux));
	
	if ($intDiaDaSemana == 0) 	//domingo
		{
		list($ano,$mes,$dia) = desmontarDataYmd($dtaux);
		$dtVencimento = acrescentarDiasData($dia, $mes, $ano, 1);
		}
	elseif($intDiaDaSemana == 6)  //sábado
		{
		list($ano,$mes,$dia) = desmontarDataYmd($dtaux);
		$dtVencimento = acrescentarDiasData($dia, $mes, $ano, 2);
		}
	else
		{
		$dtVencimento = $dtaux;
		}
	
	return formatarDataAplicacao($dtVencimento);
	}

function valorPorExtenso($valor = 0, $maiusculas = false) 
	{	
	$singular = array("centavo", "real", "mil", "milhão", "bilhão", "trilhão", "quatrilhão");
	$plural = array("centavos", "reais", "mil", "milhões", "bilhões", "trilhões","quatrilhões");
	$c = array("", "cem", "duzentos", "trezentos", "quatrocentos",
	"quinhentos", "seiscentos", "setecentos", "oitocentos", "novecentos");
	$d = array("", "dez", "vinte", "trinta", "quarenta", "cinquenta",
	"sessenta", "setenta", "oitenta", "noventa");
	$d10 = array("dez", "onze", "doze", "treze", "quatorze", "quinze",
	"dezesseis", "dezesete", "dezoito", "dezenove");
	$u = array("", "um", "dois", "três", "quatro", "cinco", "seis",
	"sete", "oito", "nove");
	$z = 0;
	$rt = "";
	
	$valor = number_format($valor, 2, ".", ".");
	$inteiro = explode(".", $valor);
	for($i=0;$i<count($inteiro);$i++)
	for($ii=strlen($inteiro[$i]);$ii<3;$ii++)
	$inteiro[$i] = "0".$inteiro[$i];
	
	$fim = count($inteiro) - ($inteiro[count($inteiro)-1] > 0 ? 1 : 2);
	for ($i=0;$i<count($inteiro);$i++) 
		{
		$valor = $inteiro[$i];
		$rc = (($valor > 100) && ($valor < 200)) ? "cento" : $c[$valor[0]];
		$rd = ($valor[1] < 2) ? "" : $d[$valor[1]];
		$ru = ($valor > 0) ? (($valor[1] == 1) ? $d10[$valor[2]] : $u[$valor[2]]) : "";
		
		$r = $rc.(($rc && ($rd || $ru)) ? " e " : "").$rd.(($rd &&
		$ru) ? " e " : "").$ru;
		$t = count($inteiro)-1-$i;
		$r .= $r ? " ".($valor > 1 ? $plural[$t] : $singular[$t]) : "";
		if ($valor == "000")$z++; elseif ($z > 0) $z--;
		if (($t==1) && ($z>0) && ($inteiro[0] > 0)) $r .= (($z>1) ? " de " : "").$plural[$t];
		if ($r) $rt = $rt . ((($i > 0) && ($i <= $fim) &&
		($inteiro[0] > 0) && ($z < 1)) ? ( ($i < $fim) ? ", " : " e ") : " ") . $r;
		}
	
	if(!$maiusculas)
		{
		return($rt ? $rt : "zero");
		} 
	else 
		{
		if ($rt) $rt=ereg_replace(" E "," e ",ucwords($rt));
		return (($rt) ? ($rt) : "Zero");
		}
	}
	
function recuperarUltimoDiaMes($mesAnoReferencia){
	list($mes,$ano) = desmontarMesAnoReferencia($mesAnoReferencia);
	$ultimoDia = cal_days_in_month(CAL_GREGORIAN, $mes , $ano);
	$dataAux = $ano."-".$mes."-".$ultimoDia;
	return $dataAux;
}




























