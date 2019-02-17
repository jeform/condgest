<?php
class rptRelatorioInadimplencia extends satecmax_pdf
	{

	private $arrDados;

	function __construct()
		{
		parent::satecmax_pdf("P","mm","A4");
		$this->SetLeftMargin(6);
		$this->AliasNbPages();
		$this->SetDrawColor(0,0,0);
		$this->SetFillColor(0,0,0);
		$this->SetLineWidth(.1);
		$this->SetAutoPageBreak(true, 9);
		$this->setFont("tahoma","",10);
		}

	function Header()
		{
		$this->SetXY(6, 8);
		$this->setFont("tahoma","",8);
			
		$sqlCondominio = "select
							    a.nm_condominio,
							    a.nr_cnpj_condominio,
							    a.logradouro_condominio,
							    a.nr_logradouro_condominio,
							    a.compl_logradouro_condominio,
							    a.bairro_condominio,
							    a.municipio_condominio,
								b.desc_estado,
							    a.cep_condominio,
							    a.telefone_condominio
						from
						   		tbl_condominio a, tbl_estados_brasileiros b
						where
								a.uf_condominio = b.cd_estado";
		
		$arrDadosCondominio = P4A_DB::singleton()->fetchAll($sqlCondominio);
		
		foreach($arrDadosCondominio as $condominio => $dadosCondominio )
			{
			$this->cell(210,5,utf8_decode("Condomínio ".$dadosCondominio["nm_condominio"]),0,1,"L");
			$this->cell(210,5,utf8_decode("CNPJ: ".$dadosCondominio["nr_cnpj_condominio"]),0,1,"L");
			$this->cell(210,5,utf8_decode("Endereço: ".$dadosCondominio["logradouro_condominio"]." Nº ".$dadosCondominio["nr_logradouro_condominio"]." Compl. ".$dadosCondominio["compl_logradouro_condominio"]),0,1,"L");
			$this->cell(210,5,utf8_decode("Bairro: ".$dadosCondominio["bairro_condominio"]),0,1,"L");
			$this->cell(210,5,utf8_decode($dadosCondominio["municipio_condominio"])."/".utf8_decode($dadosCondominio["desc_estado"]). " - CEP: ".$dadosCondominio["cep_condominio"],0,1,"L");
			$this->cell(210,5,utf8_decode("Telefone: ".$dadosCondominio["telefone_condominio"]),0,1,"L");
			$this->ln(10);
			}	
		}

	function setParametros($mesAno)
		{	
		$mesAnoInadimplencia = $mesAno;
		
		$this->data = $mesAnoInadimplencia;
		}
		
	function montaRelatorio()
		{
			
		$this->AddPage();
		$this->setFont("tahoma","",10);
		
		$this->cell(190,5,utf8_decode("UNIDADES INADIMPLENTES"),1,1,"C");
		$this->ln();
		$this->cell(70,5,utf8_decode("Inadimplência até ".date("d-M-y")),0,0,"L");
		$this->ln();
		$this->ln();	

		$sqlCompl = $this->data <> "" ? " and a.cd_boleto_mes = '{$this->data}' group by b.cd_unidade , b.dt_vencimento order by b.cd_unidade":"group by b.cd_unidade , b.dt_vencimento order by b.cd_unidade, b.dt_vencimento";
		
		$sql = "select 
					    b.cd_unidade as unidade,
					    e.nm_pessoa as nm_pessoa,
					    a.mes_ano_referencia as mes_ano_referencia,
					    b.dt_vencimento as dt_vencimento,
					    SUM(c.vlr_item_boleto) as valor,
					 	CASE b.tp_boleto 
                            WHEN 0 THEN "."'TX. CONDOMINIAL'"." 
                            WHEN 1 THEN "."'MULTA'"."
                        END AS tipo
				  FROM
					    tbl_boleto_mes a,
					    tbl_boleto_mes_unidade b,
					    tbl_boleto_mes_unidade_itens_cobranca c,
					    tbl_unidades d,
					    tbl_pessoas e
				 WHERE
					    a.cd_boleto_mes = b.cd_boleto_mes
					        AND b.cd_boleto_mes_unidade = c.cd_boleto_mes_unidade 
					        AND b.cd_unidade = c.cd_unidade
					        AND c.cd_unidade = d.cd_unidade
					        AND d.cd_pessoa = e.cd_pessoa
					        AND b.st_baixado = 0 
					        AND DATE_ADD(b.dt_vencimento, INTERVAL 0 DAY) < CURDATE()
							AND d.st_unidade = 1
							{$sqlCompl}";
							
		$arrDados = P4A_DB::singleton()->fetchAll($sql);
		
		$this->setFont("tahoma","",10);
		
		$this->cell(20,5,utf8_decode("UNIDADE"),1,0,"C");
		$this->cell(60,5,utf8_decode("NOME"),1,0,"C");
		$this->cell(30,5,utf8_decode("MES/ANO REF."),1,0,"C");
		$this->cell(30,5,utf8_decode("VENCIMENTO"),1,0,"C");
		$this->cell(20,5,utf8_decode("VALOR"),1,0,"C");
		$this->cell(30,5,utf8_decode("TIPO"),1,0,"C");
		
		foreach($arrDados as $linha => $dadosLinha )
			{
			$this->ln();
			$this->imprimirLinhaDetalhes($dadosLinha);
			}
			
		$this->imprimeTotalCategoria();
		}
		
	function imprimirLinhaDetalhes($dadosLinha)
		{
		$this->setFont("tahoma","",7);
		$this->cell(20,5,utf8_decode($dadosLinha["unidade"]),"BT",0,"C");
		$this->cell(60,5,utf8_decode($dadosLinha["nm_pessoa"]),"BT",0,"C");
		$this->cell(30,5,utf8_decode($dadosLinha["mes_ano_referencia"]),"BT",0,"C");
		$this->cell(30,5,utf8_decode(formatarDataAplicacao($dadosLinha["dt_vencimento"])),"BT",0,"C");
		$this->cell(20,5,utf8_decode(number_format($dadosLinha["valor"],2,",",".")),"BT",0,"C");
		$this->cell(30,5,utf8_decode($dadosLinha["tipo"]),"BT",0,"C");

		$this->totalCondominos += $dadosLinha["valor"];
	 	}

	function imprimeTotalCategoria()
		{
		$this->ln();
		$this->setFont("tahoma","",10);				
		$this->cell(160,5,utf8_decode("TOTAL INADIMPLÊNCIA"),"BT",0,"R");
		$this->cell(30,5,utf8_decode("R$ ".number_format($this->totalCondominos,2,",",".")),"BT",0,"L");
		}
			
	function Footer()
		{

		}

	function Output()
		{
		$this->montaRelatorio();
		return parent::Output("rptRelatorioInadimplencia.pdf","S");
		}
	
	}
