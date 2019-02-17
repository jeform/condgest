<?php
class rptRecibos extends satecmax_pdf
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
	
	function setParametros($cdRecibo)
		{	

		$this->SetXY(10, 15);
			
		$sql = "select 
					    a.cd_recibo,
					    a.tp_movimento_recibo,
					    b.ds_caixa,
					    c.nm_pessoa,
						c.txt_endereco,
						c.txt_bairro,
						c.txt_cidade,
						f.desc_estado,
					    a.dt_recibo,
					    (CASE WHEN a.tp_movimento_recibo = 1 THEN 'Entrada' ELSE 'Saída' END) AS 'tp_movimento',
					    a.vlr_recibo,
					    a.ds_recibo,
					    a.tp_documento_referencia,
					    d.ds_categoria,
					    e.ds_hist_padrao,
					    a.ds_compl_hist_padrao
				from
					    recibos a,
					    caixa b,
					    tbl_pessoas c,
					    tbl_categorias d,
					    hist_padrao e,
						tbl_estados_brasileiros f
			   where
					    a.cd_caixa = b.cd_caixa
					        and a.cd_pessoa = c.cd_pessoa
					        and a.cd_plano_conta = d.cd_categoria
					        and a.cd_hist_padrao = e.cd_hist_padrao
							and c.cd_estado = f.cd_estado
							and cd_recibo = {$cdRecibo}";
				
		$arrDados = P4A_DB::singleton()->fetchAll($sql);
		
		foreach($arrDados as $linha => $dadosLinha )
			{
			$cdRecibo = $dadosLinha["cd_recibo"];
	
			$this->AddPage();
			$this->setFont("ArialBlack","",15);

			$this->cell(200,5,utf8_decode("Recibo nº ".$cdRecibo." no valor de R$ ".number_format($dadosLinha["vlr_recibo"],2,",",".")),"",1,"C");
			$this->setLineWidth("0.5");
			$this->Line("10","58","200","58");

			$this->SetXY(6, 60);
			
			$this->imprimirLinhaDetalhes($dadosLinha);
			}
		}
		
	function imprimirLinhaDetalhes($dadosLinha)
		{
		if($dadosLinha["tp_movimento"] == 'Saída')
			{
			$txtTpMovimento = "pago";
			}
		else 
			{
			$txtTpMovimento = "recebido";
			}
			
		$this->setFont("tahoma","",13);
		$this->setXY("10","70");
		
		$this->Multicell(190,6,utf8_decode("Eu, ".$dadosLinha["nm_pessoa"]).
						utf8_decode(", residente e domiciliado à ").
						utf8_decode($dadosLinha["txt_endereco"]).", ".
						" no bairro ".utf8_decode($dadosLinha["txt_bairro"]).", ".
						"na cidade de ".utf8_decode($dadosLinha["txt_cidade"]).
						"/".utf8_decode($dadosLinha["desc_estado"]).
						utf8_decode(", declaro ter ").$txtTpMovimento.		
						utf8_decode(", a importância supra de R$ ".number_format($dadosLinha["vlr_recibo"],2,",",".")).
						utf8_decode(" do Condomínio Villa Verde, referente a ").
						utf8_decode($dadosLinha["ds_recibo"])."."
						,0,1 ,"J");
		
		$this->SetXY(10, 110);
		$this->cell(210,10,utf8_decode("Categoria: ".$dadosLinha["ds_categoria"]),0,0,"L");
		$this->SetXY(10, 116);
		$this->cell(210,10,utf8_decode("Histórico Padrão: ".$dadosLinha["ds_hist_padrao"]),0,0,"L");
		$this->SetXY(10, 122);
		$this->cell(210,10,utf8_decode("Compl. Histórico: ".$dadosLinha["ds_compl_hist_padrao"]),0,0,"L");
		$this->SetXY(10,160);
		$this->cell(210,10,utf8_decode("E, por ser verdade e para maior clareza eu emito o presente recibo."),0,0,"L");
		$this->SetXY(120, 220);
		$this->setLineWidth("0.3");
		$this->Line("10","210","70","210");
		$this->Line("75","210","90","210");
		$this->Line("101","210","160","210");
		$this->Line("172","210","195","210");
		$this->setY(204);
		$this->SetX(70);
		$this->cell(200,10,",");
		$this->SetX(92);
		$this->cell(200,10,"de");
		$this->SetX(163);
		$this->cell(200,10,"de");
		$this->Line("60","240","160","240");
		$this->SetXY(10, 245);
		$this->cell(200,5,utf8_decode($dadosLinha["nm_pessoa"]),"",0,"C");
		}
		
	function Footer()
		{
		$this->setFont("tahoma","",8);
		$this->cell(210,10,utf8_decode("Sistema de Gestão Condominial - Condgest"),0,1,"C");
		}
	
	function montaRelatorio()
		{
		}
	
	function Output()
		{
		return parent::Output("recibo.pdf","S");
		}
	}