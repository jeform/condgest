<?php
class rptCaixaSaidas extends satecmax_pdf
{
	private $arrDados;
	
	function __construct()
	{
		parent::satecmax_pdf("L","mm","A4");
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
		$this->Image("imagens/logo.jpg",250, 3,40,40);
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
			$this->cell(285,5,utf8_decode("CONDOMÍNIO: ".$dadosCondominio["nm_condominio"]),0,1,"C");
			$this->cell(285,5,utf8_decode("CNPJ: ".$dadosCondominio["nr_cnpj_condominio"]),0,1,"C");
			$this->cell(285,5,utf8_decode("ENDEREÇO: ".$dadosCondominio["logradouro_condominio"]." Nº ".$dadosCondominio["nr_logradouro_condominio"]." COMPL. ".$dadosCondominio["compl_logradouro_condominio"]),0,1,"C");
			$this->cell(285,5,utf8_decode("BAIRRO: ".$dadosCondominio["bairro_condominio"]),0,1,"C");
			$this->cell(285,5,utf8_decode($dadosCondominio["municipio_condominio"])."/".utf8_decode($dadosCondominio["desc_estado"]). " - CEP: ".$dadosCondominio["cep_condominio"],0,1,"C");
			$this->cell(285,5,utf8_decode("TELEFONE: ".$dadosCondominio["telefone_condominio"]),0,1,"C");
			$this->ln(10);
		}	
	}
		
	function setParametros($dtInicio,$dtFim,$categoria="")
	{
		$dataIni = formatarDataBanco($dtInicio);
		
		$dataFim = formatarDataBanco($dtFim);
		
		$sqlCompl = $categoria <> "" ? " and a.cd_plano_conta = '{$categoria}' ":"";
		
		$sql = "
				select 
					a.dt_movimento,
					a.tp_movimento,
					a.cd_plano_conta,
					b.plano_contas,
					b.ds_categoria,
					a.vlr_movimento,
					a.cd_documento_referencia,
					a.tp_documento_referencia,
					a.ds_movimento,
					c.nm_pessoa
				
				from 
					caixa_movimento a,
					tbl_categorias b,
					tbl_pessoas c
				where
						a.cd_plano_conta = b.cd_categoria
					and a.cd_pessoa = c.cd_pessoa
					and b.tp_categoria = 2
					and a.dt_movimento between '{$dataIni}' and '{$dataFim}'
					{$sqlCompl}
								
				order by
					b.plano_contas,
					a.dt_movimento		
		";
					
		$arrDados = P4A_DB::singleton()->fetchAll($sql);
		
		$cdCategoria = 0;
		foreach($arrDados as $linha => $dadosLinha )
			{
			if ( $cdCategoria <> $dadosLinha["cd_plano_conta"])
			{
				if ( $cdCategoria <> 0 )
				{
					// imprimir o total e zerar...
					$this->imprimeTotalCategoria($cdCategoria);
				}
				$cdCategoria = $dadosLinha["cd_plano_conta"];
				
				$this->AddPage();
				$this->setFont("tahoma","",10);
				
				$this->cell(285,10,utf8_decode("RELATÓRIO DE DESPESAS POR CATEGORIA"),1,1,"C");
					
				$this->SetY(65);
				
				$this->cell(290,5,utf8_decode("Conta: ".$dadosLinha["plano_contas"]),0,0,"L");
				$this->Ln();
				$this->cell(290,5,utf8_decode("Descrição: ".$dadosLinha["ds_categoria"]),0,0,"L");
				$this->Ln();
				$this->cell(290,5,utf8_decode("Período: ".$dtInicio." - ".$dtFim),0,0,"L");
				$this->Ln();
				$this->SetY(85);
				$this->cell(10,5,utf8_decode("Data"),1,0,"C");
				$this->cell(150,5,utf8_decode("Descrição"),1,0,"C");
				$this->cell(20,5,utf8_decode("Valor"),1,0,"C");
				$this->cell(85,5,utf8_decode("Fornecedor/Prestador"),1,0,"C");
				$this->cell(20,5,utf8_decode("Nr.Doc"),1,1,"C");
				
				$this->imprimirLinhaDetalhes($dadosLinha);
			}
			else
			{
				$this->imprimirLinhaDetalhes($dadosLinha);
			}
				
		}
		$this->imprimeTotalCategoria($cdCategoria);

	}
		
	function imprimirLinhaDetalhes($dadosLinha)
	{
		$this->setFont("tahoma","",7);
		$this->cell(10,5,utf8_decode(substr(formatarDataAplicacao($dadosLinha["dt_movimento"]),0,5)),"BT",0,"C");
		$this->cell(150,5,utf8_decode($dadosLinha["ds_movimento"]),"BT",0,"L");
		$this->cell(20,5,utf8_decode(number_format($dadosLinha["vlr_movimento"],2,",",".")),"BT",0,"R");
		$this->cell(85,5,utf8_decode($dadosLinha["nm_pessoa"]),"BT",0,"L");
		$this->cell(20,5,utf8_decode($dadosLinha["cd_documento_referencia"]),"BT",1,"C");
		
		$this->totalCategoria[$dadosLinha["cd_plano_conta"]]+= $dadosLinha["vlr_movimento"];
	}
		
	function imprimeTotalCategoria($cdCategoria)
	{
		$this->setFont("tahoma","",10);
		$this->cell(155,5,utf8_decode("TOTAL"),"BT",0,"C");
		$this->cell(35,5,"R$ ".number_format($this->totalCategoria[$cdCategoria],2,",","."),"BT",0,"C");
		$this->cell(95,5,"","BT",1,"C");
	}
			
	function Footer()
	{				
		$this->setFont("tahoma","",8);
		$this->cell(285,10,utf8_decode("Condgest - O seu sistema de Gestão Condominial"),0,1,"C");
	}
		
	function montaRelatorio()
	{
		
	}
		
	function Output()
	{
		return parent::Output("categorias.pdf","S");
	}
	}