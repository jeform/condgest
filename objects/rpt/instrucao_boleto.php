<?php
class instrucao_boleto extends satecmax_pdf
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

	function setParametros($cdBoletoMes)
		{
			
		$this->AddPage();
		$this->setFont("tahoma","",10);
			
		$sql = "SELECT 
					    a.cd_unidade as unidade,
					    date_format(a.dt_vencimento,'%d/%m/%Y') as dt_vencimento,
					    (select 
					            b.vlr_item_boleto
					        from
					            tbl_boleto_mes_unidade_itens_cobranca b
					        where
					            b.cd_unidade = a.cd_unidade
					                and b.cd_boleto_mes_unidade = a.cd_boleto_mes_unidade
					                and b.cd_item_cobranca = 1) as vlr_condominio,					   
					    (select 
					            b.vlr_item_boleto
					        from
					            tbl_boleto_mes_unidade_itens_cobranca b
					        where
					            b.cd_unidade = a.cd_unidade
					                and b.cd_boleto_mes_unidade = a.cd_boleto_mes_unidade
					                and b.cd_item_cobranca = 2) as vlr_benfeitoria,
					    (select 
					            b.vlr_item_boleto
					        from
					            tbl_boleto_mes_unidade_itens_cobranca b
					        where
					            b.cd_unidade = a.cd_unidade
					                and b.cd_boleto_mes_unidade = a.cd_boleto_mes_unidade
					                and b.cd_item_cobranca = 5) as vlr_rat_agua,
					    (select 
					            b.vlr_item_boleto
					        from
					            tbl_boleto_mes_unidade_itens_cobranca b
					        where
					            b.cd_unidade = a.cd_unidade
					                and b.cd_boleto_mes_unidade = a.cd_boleto_mes_unidade
					                and b.cd_item_cobranca = 4) as vlr_rateio,
					    (select 
					            sum(b.vlr_item_boleto)
					        from
					            tbl_boleto_mes_unidade_itens_cobranca b
					        where
					            b.cd_unidade = a.cd_unidade
					                and b.cd_boleto_mes_unidade = a.cd_boleto_mes_unidade) as vlr_total
				  FROM
					    tbl_boleto_mes_unidade a
			     WHERE 
						a.cd_boleto_mes = {$cdBoletoMes}";
	
								
		$arrDados = P4A_DB::singleton()->fetchAll($sql);

		$this->setFont("tahoma","",10);

		

		foreach($arrDados as $linha => $dadosLinha )
			{
			$this->ln();
			$this->cell(15,5,utf8_decode("ÁREA"),1,0,"C");
			$this->cell(25,5,utf8_decode("VENCIMENTO"),1,0,"C");
			$this->cell(30,5,utf8_decode("CONDOMÍNIO"),1,0,"C");
			$this->cell(30,5,utf8_decode("BENFEITORIA"),1,0,"C");
			$this->cell(30,5,utf8_decode("RATEIO"),1,0,"C");
			$this->cell(30,5,utf8_decode("RAT ÁGUA"),1,0,"C");
			$this->cell(30,5,utf8_decode("TOTAL"),1,0,"C");
			$this->ln();
			$this->imprimirLinhaDetalhes($dadosLinha);
			$this->ln();
			$this->ln();
			}
		$this->ln();
		}

	function imprimirLinhaDetalhes($dadosLinha)
		{
		$this->setFont("tahoma","",7);
		$this->cell(10,5,utf8_decode($dadosLinha["unidade"]),"BT",0,"C");
		$this->cell(30,5,utf8_decode($dadosLinha["dt_vencimento"]),"BT",0,"C");		
		$this->cell(30,5,utf8_decode("R$ ".number_format($dadosLinha["vlr_condominio"],2,",",".")),"BT",0,"C");
		$this->cell(30,5,utf8_decode("R$ ".number_format($dadosLinha["vlr_benfeitoria"],2,",",".")),"BT",0,"C");
		$this->cell(30,5,utf8_decode("R$ ".number_format($dadosLinha["vlr_rateio"],2,",",".")),"BT",0,"C");
		$this->cell(30,5,utf8_decode("R$ ".number_format($dadosLinha["vlr_rat_agua"],2,",",".")),"BT",0,"C");
		$this->cell(30,5,utf8_decode("R$ ".number_format($dadosLinha["vlr_total"],2,",",".")),"BT",0,"C");
		}
		
	function Footer()
		{

		}

	function montaRelatorio()
		{

		}

	function Output()
		{
		return parent::Output("boleto.pdf","S");
		}

	}