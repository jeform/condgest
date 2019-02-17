<?php
class rptCertidaoNegativaDebitos extends satecmax_pdf
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
		}
	
	function setParametros($unidade)
		{	
		$this->SetXY(10, 15);
				
		$sql = "select
    				b.nm_pessoa
				from
    				tbl_unidades a 
    					inner join 
    				tbl_pessoas b 
    					on a.cd_pessoa = b.cd_pessoa
				where
    				a.cd_unidade = {$unidade}";
		
		$arrDados = P4A_DB::singleton()->fetchAll($sql);
		
		foreach($arrDados as $linha => $dadosLinha )
			{
			$nmPessoa = $dadosLinha["nm_pessoa"];
				
			$this->AddPage();
			$this->setFont("ArialBlack","",15);
			$this->SetXY(10, 20);
			$this->cell(190,5,utf8_decode("DECLARAÇÃO DE QUITAÇÃO DE DÉBITOS"),"",1,"C");
			$this->setLineWidth("0.5");
			$this->Line("10","30","190","30");
			
			$this->SetXY(6, 60);
			
			$this->imprimirLinhaDetalhes($unidade, $nmPessoa);
			}
		}
		
	 function imprimirLinhaDetalhes($unidade, $nmPessoa)
		{	
		$this->setFont("tahoma","",12);
		$this->SetXY(10,50);
		
		$sqlDadosCondominio = "select
									    a.nm_condominio,
										a.municipio_condominio,
									    d.nm_pessoa,
									    d.nr_identif
								from
									    tbl_condominio a
									        inner join
									    tbl_conselho c ON a.cd_condominio = c.cd_condominio
									        inner join
									    tbl_pessoas d ON c.cd_pessoa = d.cd_pessoa
								where
									    c.ds_funcao = 1";
		
		$arrDadosCondominio = P4A_DB::singleton()->fetchAll($sqlDadosCondominio);
		
		foreach($arrDadosCondominio as $condominio => $dadosCondominio )
			{
			$this->Multicell(185,6,utf8_decode($dadosCondominio["nm_condominio"].", por seu representante declara para fins de direito que a unidade ".$unidade).
						utf8_decode(", de propriedade do Sr(a) ".$nmPessoa .", encontra-se quite com as suas taxas de condomínio e demais encargos relativos até a presente data. "),0,1 ,"J");
				
			// Local e data do evento
			$this->Ln();
			$this->Ln();
			$this->setXy(15,150);
			$this->cell(185,10,utf8_decode($dadosCondominio["municipio_condominio"]. ", ".formata_data_extenso(date("Y-m-d"),false)."."),0,0,"R");
			// Assinaturas
			$this->Ln();
			$this->Ln();
			$this->Ln();
			$this->setFont("tahoma","",11);
			$this->setX("15");
			$this->cell(185,5,utf8_decode($dadosCondominio["nm_condominio"]),O,O,"L");
			$this->Ln();
			$this->setX("15");
			$this->cell(185,5,utf8_decode($dadosCondominio["nm_pessoa"]),O,O,"L");
			$this->Ln();
			$this->setX("15");
			$this->cell(185,5,utf8_decode($dadosCondominio["nr_identif"]),O,O,"L");
			} 
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
	