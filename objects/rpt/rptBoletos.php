<?php
class rptBoletos extends satecmax_pdf
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

	function setParametros($cdBoletoMes)
		{
		$sqlHeader= "select 
						    a.mes_ano_referencia,
						    (select 
						            count(*)
						        from
						            tbl_boleto_mes_unidade b
						        where
						            b.cd_boleto_mes = a.cd_boleto_mes) as qtde_boletos_emitidos,
						    ifnull(sum(c.vlr_item_boleto), 0) as vlr_boletos_emitidos,
						    ifnull((select 
						                    sum(vlr_baixa_boleto)
						                from
						                    tbl_boleto_mes_unidade
						                where
						                    tbl_boleto_mes_unidade.cd_boleto_mes = a.cd_boleto_mes),
						            0) as vlr_boletos_baixados,
						    ifnull((sum(c.vlr_item_boleto) - (select 
						                    sum(vlr_baixa_boleto)
						                from
						                    tbl_boleto_mes_unidade
						                where
						                    tbl_boleto_mes_unidade.cd_boleto_mes = a.cd_boleto_mes)),
						            0) as vlr_pendente
					   from
						    tbl_boleto_mes a,
						    tbl_boleto_mes_unidade b,
						    tbl_boleto_mes_unidade_itens_cobranca c
					  where
						    a.cd_boleto_mes = b.cd_boleto_mes
						        AND b.cd_boleto_mes_unidade = c.cd_boleto_mes_unidade
						        AND b.cd_unidade = c.cd_unidade
						        AND b.cd_boleto_mes = {$cdBoletoMes}";
			
		$arrDadosHeader = P4A_DB::singleton()->fetchAll($sqlHeader);

		$this->AddPage();
		$this->setFont("tahoma","",10);

		foreach($arrDadosHeader as $linha => $dadosLinhaHeader )
			{
			$this->cell(190,5,utf8_decode("Relatório dos boletos cadastrados referente à ".$dadosLinhaHeader["mes_ano_referencia"]),1,1,"C");
			$this->ln();
			$this->cell(40,5,utf8_decode("Qtde. Boletos: ".$dadosLinhaHeader["qtde_boletos_emitidos"]),1,0,"C");
			$this->cell(50,5,utf8_decode("Vlr. Total: R$ ".number_format($dadosLinhaHeader["vlr_boletos_emitidos"],2,",",".")),1,0,"C");
			$this->cell(50,5,utf8_decode("Vlr. Recebido: R$ ".number_format($dadosLinhaHeader["vlr_boletos_baixados"],2,",",".")),1,0,"C");
			$this->cell(50,5,utf8_decode("Vlr. Pendente: R$ ".number_format($dadosLinhaHeader["vlr_pendente"],2,",",".")),1,1,"C");
			$this->ln();
			}

		$boleto_aberto = condgest::singleton()->getParametro("BOLETO_ABERTO");
		$boleto_fechado = condgest::singleton()->getParametro("BOLETO_FECHADO");
		$boleto_parcialmente_fechado = condgest::singleton()->getParametro("BOLETO_PARCIALMENTE_FECHADO");
		$difBaixaBoleto = condgest::singleton()->getParametro("DIFERENCA_BAIXA_BOLETO");
		
		
		$sql = "select 
					    c.cd_unidade,
					    d.nm_pessoa,
					    date_format(b.dt_vencimento,'%d/%m/%Y') as dt_vencimento,
					    date_format(b.dt_pagamento, '%d/%m/%Y') as dt_pagamento,
					    (select 
					     	       sum(vlr_item_boleto) * 0.9
					       from
					        	   tbl_boleto_mes_unidade_itens_cobranca
					      where
					      		   cd_boleto_mes_unidade = b.cd_boleto_mes_unidade) as vlr_total_boleto,
						ifnull(b.vlr_baixa_boleto,0) as vlr_baixa_boleto,
						 if(b.st_baixado = 2,ifnull(abs((select 
                            				sum(vlr_item_boleto) * 0.9
                        			   from
                            				tbl_boleto_mes_unidade_itens_cobranca
                        			  where
                            				cd_boleto_mes_unidade = b.cd_boleto_mes_unidade) - b.vlr_baixa_boleto),
            				0),'0.00') as vl_diferenca,
					    case b.st_baixado
					        when 0 then "."'Aberto'"."
					        when 1 then "."'Fechado'"."
					        when 2 then "."'Parcialmente'"."
					    end as status_boleto
   				 from
						tbl_boleto_mes a,
					    tbl_boleto_mes_unidade b,
					    tbl_unidades c,
					    tbl_pessoas d
				where
						a.cd_boleto_mes = b.cd_boleto_mes
							and b.cd_unidade = c.cd_unidade
					        and c.cd_pessoa = d.cd_pessoa
							and b.cd_boleto_mes = {$cdBoletoMes}";
								
		$arrDados = P4A_DB::singleton()->fetchAll($sql);

		$this->setFont("tahoma","",10);

		$this->cell(15,5,utf8_decode("ÁREA"),1,0,"C");
		$this->cell(45,5,utf8_decode("CONDÔMINO"),1,0,"C");
		$this->cell(25,5,utf8_decode("DT VENC"),1,0,"C");
		$this->cell(25,5,utf8_decode("DT PAGTO"),1,0,"C");
		$this->cell(20,5,utf8_decode("VL BOLETO"),1,0,"C");
		$this->cell(20,5,utf8_decode("VL PAGO"),1,0,"C");
		$this->cell(20,5,utf8_decode("VERIFICAR"),1,0,"C");
		$this->cell(20,5,utf8_decode("STATUS"),1,0,"C");

		foreach($arrDados as $linha => $dadosLinha )
			{
			$this->ln();
			$this->imprimirLinhaDetalhes($dadosLinha);
			}
			
		//$this->imprimeTotalCategoria();
	}

	function imprimirLinhaDetalhes($dadosLinha)
		{
		$this->setFont("tahoma","",7);
		$this->cell(15,5,utf8_decode($dadosLinha["cd_unidade"]),"BT",0,"C");
		$this->cell(45,5,utf8_decode($dadosLinha["nm_pessoa"]),"BT",0,"C");
		$this->cell(25,5,utf8_decode($dadosLinha["dt_vencimento"]),"BT",0,"C");
		$this->cell(25,5,utf8_decode($dadosLinha["dt_pagamento"]),"BT",0,"C");
		$this->cell(20,5,utf8_decode(number_format($dadosLinha["vlr_total_boleto"],2,",",".")),"BT",0,"C");
		$this->cell(20,5,utf8_decode(number_format($dadosLinha["vlr_baixa_boleto"],2,",",".")),"BT",0,"C");
		$this->cell(20,5,utf8_decode(number_format($dadosLinha["vl_diferenca"],2,",",".")),"BT",0,"C");
		$this->cell(20,5,utf8_decode($dadosLinha["status_boleto"]),"BT",0,"C");

//		$this->totalConsumo += $dadosLinha["total_consumido"];
//		$this->totalCondominos += $dadosLinha["vlr_hist"];
		}

	function imprimeTotalCategoria($cdLeituraAguaMes)
		{
		$this->setFont("tahoma","",10);

		$this->cell(105,5,utf8_decode("TOTAL CONSUMO"),"BT",0,"R");
		$this->cell(20,5,utf8_decode($this->totalConsumo." m³"),"BT",0,"L");
		$this->cell(45,5,utf8_decode("TOTAL"),"BT",0,"R");
	$this->cell(20,5,"R$ ".number_format($this->totalCondominos + 2 * 73 ,2,",","."),"BT",0,"R");

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