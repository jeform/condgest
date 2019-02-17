<?php
class rptRelatorioLancamentosCaixa extends satecmax_pdf
	{
		
	/**
	 * 
	 * Enter description here ...
	 * @var P4A_Array_Source
	 */
	private $objSrcMovimentos;
	
	public function __construct($orientation='P',$unit='mm',$format='A4')
		{
		parent::satecmax_pdf("P","mm","A4");
		$this->SetLeftMargin(5);
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
		$this->setFont("tahoma","",8);
		$this->cell(200,5,utf8_decode("Relatorio de Movimentos Caixa"),1,1,"C");
		}
		
	function setParametros($objSrcMoviementos)
		{
			
		$this->objSrcMovimentos = $objSrcMoviementos;
		}
		
	function Footer()
		{				
		$this->setFont("tahoma","",6);
		$this->cell(200,10,utf8_decode("Condgest - O seu sistema de Gestão Condominial"),0,1,"R");
		}
		
	function montaRelatorio()
		{
		$this->AddPage();
		$this->ln(2);
		$this->setFont("tahoma","",8);
		
		$this->cell(15,7,utf8_decode("Cod.Mov."),1,0,"C");
		$this->cell(15,7,utf8_decode("Dt.Mov."),1,0,"C");
		$this->cell(110,7,utf8_decode("Descrição"),1,0,"C");
		$this->cell(15,7,utf8_decode("Saldo Ant."),1,0,"C");
		$this->cell(15,7,utf8_decode("Entrada"),1,0,"C");
		$this->cell(15,7,utf8_decode("Saida"),1,0,"C");
		$this->cell(15,7,utf8_decode("Saldo Final"),1,1,"C");
		
		$this->setFont("tahoma","",6);

		$arrDadosMovimento = $this->objSrcMovimentos->getAll();
		
		foreach ($arrDadosMovimento as $dadosLinhaMovimento)
			{
			$this->cell(15,4,utf8_decode($dadosLinhaMovimento["cdMovimento"]),1,0,"C");
			$this->cell(15,4,utf8_decode(formatarDataAplicacao($dadosLinhaMovimento["dtMovimento"])),1,0,"C");
			$this->cell(110,4,utf8_decode($dadosLinhaMovimento["dsMovimento"]),1,0,"L");
			$this->cell(15,4,utf8_decode(number_format($dadosLinhaMovimento["vlrSaldoAnterior"],2,",",".")),1,0,"R");
			$this->cell(15,4,utf8_decode(number_format($dadosLinhaMovimento["vlrEntrada"],2,",",".")),1,0,"R");
			$this->cell(15,4,utf8_decode(number_format($dadosLinhaMovimento["vlrSaida"],2,",",".")),1,0,"R");
			$this->cell(15,4,utf8_decode(number_format($dadosLinhaMovimento["vlrSaldoFinal"],2,",",".")),1,1,"R");
				
			}
			
		$this->setFont("tahoma","",8);
			
		
		//$this->Cell(185,1,"","LRB",0);
		//$this->Cell(100,1,"","LRB",1);
		}
		
	function Output()
		{
		$this->montaRelatorio();
		return parent::Output("rptRelatorioLancamentosCaixa.pdf","S");
		}
		
	}