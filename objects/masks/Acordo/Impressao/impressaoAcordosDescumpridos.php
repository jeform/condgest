<?php
class impressaoAcordosDescumpridos extends satecmax_pdf
	{
	function __construct()
		{
		parent::satecmax_pdf("P","mm","A4");
		$this->SetLeftMargin(6);
		$this->AliasNbPages();
		$this->SetDrawColor(0,0,0);
		$this->SetFillColor(0,0,0);
		$this->SetLineWidth(.1);
		$this->SetAutoPageBreak(true, 9);
		$this->montaRelatorio();
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
		}	

	function montaRelatorio()
		{
		$this->AddPage();
		$this->ln();
		$this->ln();
		$this->setFont("tahoma","",10);
		
		$this->cell(190,5,utf8_decode(".: ACORDOS DESCUMPRIDOS :."),1,1,"C");
		$this->setXY(30,50);
		
		$this->cell(20,5,utf8_decode("UNIDADE"),1,0,"C");
		$this->cell(40,5,utf8_decode("DT ACORDO"),1,0,"C");
		$this->cell(40,5,utf8_decode("VLR. ACORDO"),1,0,"C");
		$this->cell(40,5,utf8_decode("PARCELAS EM ABERTO"),1,0,"C");
		
		$sqlBuscaDados = "select
						    cd_unidade, dt_acordo, sum(vlr_parcela) vlr_total, COUNT(*) qtde_parcelas
						from
						    acordos a
						        inner join
						    acordos_detalhes b ON a.cd_acordo = b.cd_acordo
						where
						    cd_st_recebimento = 1
						        and date_add(dt_vencimento, interval 1 day) < curdate()
						group by cd_unidade
					  	order by cd_unidade";
		
		$arrDados = P4A_DB::singleton()->fetchAll($sqlBuscaDados);
		$this->setFont("tahoma","",8);
		
		foreach($arrDados as $linha => $dadosLinha)
			{
			$this->ln();
			$this->SetX(30);
			$this->cell(20,5,utf8_decode($dadosLinha["cd_unidade"]),"BT",0,"C");
			$this->cell(40,5,utf8_decode(formatarDataAplicacao($dadosLinha["dt_acordo"])),"BT",0,"C");
			$this->cell(40,5,utf8_decode(number_format($dadosLinha["vlr_total"],2,",",".")),"BT",0,"C");
			$this->cell(40,5,utf8_decode($dadosLinha["qtde_parcelas"]),"BT",0,"C");
			}
		}
	
	function Footer()
		{
		$this->Ln();
		$this->Ln();
		$this->cell(190,5,utf8_decode("Data extração: ".date("d/m/Y H:i:s")),0,0,"R");
		}

	function Output()
		{
		return parent::Output("acordosDescumpridos.pdf","S");
		}

	}