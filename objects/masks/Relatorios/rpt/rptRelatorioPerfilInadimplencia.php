<?php
class rptRelatorioPerfilInadimplencia extends satecmax_pdf
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
		$this->Image("imagens/logo.jpg",150, 1,27,27);
		$this->SetXY(6, 8);
		$this->setFont("tahoma","",6);
			
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
			$this->cell(100,2.5,utf8_decode("CONDOMÍNIO: ".$dadosCondominio["nm_condominio"]),0,1,"C");
			$this->cell(100,2.5,utf8_decode("CNPJ: ".$dadosCondominio["nr_cnpj_condominio"]),0,1,"C");
			$this->cell(100,2.5,utf8_decode("ENDEREÇO: ".$dadosCondominio["logradouro_condominio"]." Nº ".$dadosCondominio["nr_logradouro_condominio"]." COMPL. ".$dadosCondominio["compl_logradouro_condominio"]),0,1,"C");
			$this->cell(100,2.5,utf8_decode("BAIRRO: ".$dadosCondominio["bairro_condominio"]),0,1,"C");
			$this->cell(100,2.5,utf8_decode($dadosCondominio["municipio_condominio"])."/".utf8_decode($dadosCondominio["desc_estado"]). " - CEP: ".$dadosCondominio["cep_condominio"],0,1,"C");
			$this->cell(100,2.5,utf8_decode("TELEFONE: ".$dadosCondominio["telefone_condominio"]),0,1,"C");
			$this->ln(2);		
			}
		$this->cell(190,5,utf8_decode("RELATORIO DE PERFIL DE INADIMPLÊNCIA - Ano: ".$this->ano),1,1,"C");
		}	

	function setParametros($ano)
		{		
		$anoReferencia = $ano;
		$this->ano = $anoReferencia;	
			
		$this->AddPage();
		$this->ln();
		$this->ln();
		$this->setFont("tahoma","",10);

		$sql = "select 
				    a.mes_ano_referencia,
				    ifnull((select 
				                    sum(vlr_baixa_boleto)
				                from
				                    tbl_boleto_mes_unidade
				                where
				                    tbl_boleto_mes_unidade.cd_boleto_mes = a.cd_boleto_mes),
				            0) as vlr_boletos_faturados,
				    ifnull((sum(c.vlr_item_boleto) - (select 
				                    sum(vlr_baixa_boleto)
				                from
				                    tbl_boleto_mes_unidade
				                where
				                    tbl_boleto_mes_unidade.cd_boleto_mes = a.cd_boleto_mes)),
				            0) as vlr_inadimplencia,
				    100 - round(((select 
				                    count(*)
				                from
				                    tbl_boleto_mes_unidade b
				                where
				                    b.cd_boleto_mes = a.cd_boleto_mes
				                        and b.st_baixado = '1') * 100 / (select 
				                    count(*)
				                from
				                    tbl_boleto_mes_unidade b
				                where
				                    b.cd_boleto_mes = a.cd_boleto_mes)),
				            2) as percentual_inadimplencia,
				    (select 
				            count(*)
				        from
				            tbl_boleto_mes_unidade b
				        where
				            b.cd_boleto_mes = a.cd_boleto_mes
				                and b.st_baixado = '0') as qtde_unidades_inadimplentes
				from
				    tbl_boleto_mes a,
				    tbl_boleto_mes_unidade b,
				    tbl_boleto_mes_unidade_itens_cobranca c
				where
				    a.cd_boleto_mes = b.cd_boleto_mes
				        and b.cd_boleto_mes_unidade = c.cd_boleto_mes_unidade
				        and b.cd_unidade = c.cd_unidade
				        and mid(a.mes_ano_referencia, 4, 8) = {$ano}
				group by mes_ano_referencia
				order by a.cd_boleto_mes asc
				";
				 			
		
		$arrDados = P4A_DB::singleton()->fetchAll($sql);
		
		$this->setFont("tahoma","",7);
		
		$this->cell(30,5,utf8_decode("MES/ANO REF."),1,0,"C");
		$this->cell(40,5,utf8_decode("VLR. BOLETOS FATURADOS (R$)"),1,0,"C");
		$this->cell(40,5,utf8_decode("VLR. INADIMPLÊNCIA (R$)"),1,0,"C");
		$this->cell(35,5,utf8_decode("INADIMPLÊNCIA (%)"),1,0,"C");
		$this->cell(45,5,utf8_decode("QTDE. UNIDADES INADIMPLENTES"),1,0,"C");
		
		foreach($arrDados as $linha => $dadosLinha )
			{
			$this->ln();
			$this->imprimirLinhaDetalhes($dadosLinha);
			}
		}
		
	function imprimirLinhaDetalhes($dadosLinha)
		{
		$this->setFont("tahoma","",7);
		$this->cell(30,5,utf8_decode($dadosLinha["mes_ano_referencia"]),"BT",0,"C");
		$this->cell(40,5,utf8_decode(number_format($dadosLinha["vlr_boletos_faturados"],2,",",".")),"BT",0,"C");
		$this->cell(40,5,utf8_decode(number_format($dadosLinha["vlr_inadimplencia"],2,",",".")),"BT",0,"C");
		$this->cell(35,5,utf8_decode($dadosLinha["percentual_inadimplencia"]),"BT",0,"C");
		$this->cell(45,5,utf8_decode($dadosLinha["qtde_unidades_inadimplentes"]),"BT",0,"C");
	 	}
			
	function Footer()
		{
		$this->setFont("tahoma","",6);
		$this->cell(200,10,utf8_decode("Condgest - O seu sistema de Gestão Condominial"),0,1,"R");
		$this->cell(190,10,utf8_decode("Data extração: ".date("d/m/Y H:i:s")),0,0,"R");
		}

	function Output()
		{
		return parent::Output("rptRelatorioPerfilInadimplencia.pdf","S");
		}
	
	}
