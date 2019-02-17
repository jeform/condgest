<?php
class rptCertidaoNegativa extends satecmax_pdf
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
			$this->cell(210,5,utf8_decode($dadosCondominio["municipio_condominio"])."/".utf8_decode($dadosCondominio["desc_estado"]). " - CEP:".$dadosCondominio["cep_condominio"],0,1,"L");
			$this->cell(210,5,utf8_decode("Telefone: ".$dadosCondominio["telefone_condominio"]),0,1,"L");
			$this->ln(10); 
			}
		}
	
	function setParametros($unidade)
		{	
		$this->SetXY(10, 15);
				
		$sql = "SELECT
    				b.nm_pessoa
				FROM
    				tbl_unidades a INNER JOIN tbl_pessoas b 
    					ON a.cd_pessoa = b.cd_pessoa
				WHERE
    				a.cd_unidade = {$unidade}";
		
		$arrDados = P4A_DB::singleton()->fetchAll($sql);
		
		foreach($arrDados as $linha => $dadosLinha )
			{
			$nmPessoa = $dadosLinha["nm_pessoa"];
				
			$this->AddPage();
			$this->setFont("ArialBlack","",15);
			
			$this->cell(200,5,utf8_decode("Declaração de Quitação"),"",1,"C");
			$this->setLineWidth("0.5");
			$this->Line("10","58","200","58");
			
			$this->SetXY(6, 60);
			
			$this->imprimirLinhaDetalhes($unidade, $nmPessoa);
			}
		}
		
	function imprimirLinhaDetalhes($unidade, $nmPessoa)
		{	
		$this->setFont("tahoma","",12);
		
		$this->SetY(70);
		
		$this->Multicell(200,5,utf8_decode("Condomínio Villa Verde, por seu representante declara para fins de direito que a unidade ".$unidade).
				utf8_decode(", de propriedade do Sr(a) ".$nmPessoa).
				utf8_decode(", encontra-se quite de suas Taxas Associativas até a presente data. "),0,1 ,"J");
		}
	
	function montaRelatorio()
		{
		}
		
	function Footer()
		{
		$this->setFont("tahoma","",8);
		$this->SetY(280);
		$this->cell(210,10,utf8_decode("Sistema de Gestão Condominial - Condgest"),0,1,"C");
		}
	
	function Output()
		{
		return parent::Output("rptCertidaoNegativa.pdf","S");
		}
	}
	