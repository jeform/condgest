<?php
class impressaoAcordo extends satecmax_pdf
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
		}
	
	function Header()
		{
		}
	
	function setParametros($cdAcordo)
		{	
		$this->setY("30");
		
		$sql = "select 
						a.cd_unidade,
					    c.nm_pessoa,
					    c.nr_identif,
					    c.nr_identif_1,
					    c.txt_endereco,
					    c.txt_bairro,
					    c.cep,
					    c.txt_cidade,
					    d.sigla_estado
				  from
					    acordos a
					        inner join
					    tbl_unidades b ON a.cd_unidade = b.cd_unidade
					        inner join
					    tbl_pessoas c ON b.cd_pessoa = c.cd_pessoa
					        inner join
					    tbl_estados_brasileiros d ON c.cd_estado = d.cd_estado
				 where
					    cd_acordo = {$cdAcordo}";
				
		$arrDados = P4A_DB::singleton()->fetchAll($sql);
		
		foreach($arrDados as $linha => $dadosLinha )
			{
			$this->imprimirLinhaDetalhes($dadosLinha,$cdAcordo);
			}		
		}
		
	 function imprimirLinhaDetalhes($dadosLinha,$cdAcordo)
		{
		$this->AddPage();
		$this->setFont("ArialBlack","",15);
		$this->SetXY(10, 20);
		$this->cell(190,5,utf8_decode("INSTRUMENTO PARTICULAR DE CONFISSÃO DE DÍVIDA"),"",1,"C");

		$this->setFont("tahoma","",11);
		$this->setXY("15","40");
			
		$sqlDadosCondominio = "select 
									    a.nm_condominio,
									    a.nr_cnpj_condominio,
									    a.logradouro_condominio,
									    a.nr_logradouro_condominio,
									    a.compl_logradouro_condominio,
									    a.bairro_condominio,
									    a.municipio_condominio,
									    b.sigla_estado,
									    a.cep_condominio,
									    a.telefone_condominio,
									    d.nm_pessoa,
									    d.nr_identif
								from
									    tbl_condominio a
									        inner join
									    tbl_estados_brasileiros b ON a.uf_condominio = b.cd_estado
									        inner join
									    tbl_conselho c ON a.cd_condominio = c.cd_condominio
									        inner join
									    tbl_pessoas d ON c.cd_pessoa = d.cd_pessoa
								where
									    c.ds_funcao = 1";
		
		$arrDadosCondominio = P4A_DB::singleton()->fetchAll($sqlDadosCondominio);
		
		foreach($arrDadosCondominio as $condominio => $dadosCondominio )
			{
			// Qualificação
			$this->Multicell(185,6,utf8_decode("Pelo presente Instrumento Particular, de um lado ". $dadosCondominio["nm_condominio"]).		
			utf8_decode(", devidamente inscrito no CNPJ sob nº ".$dadosCondominio["nr_cnpj_condominio"]).
			utf8_decode(", estabelecido à ".$dadosCondominio["logradouro_condominio"].", ".$dadosCondominio["bairro_condominio"]).
			utf8_decode(", CEP ".$dadosCondominio["cep_condominio"]." em ".$dadosCondominio["municipio_condominio"]." - ".$dadosCondominio["sigla_estado"]).
			utf8_decode(", neste ato representado por seu síndico, doravante denominado simplesmente de CREDOR e de outro ".$dadosLinha["nm_pessoa"]).
			utf8_decode(", portador da Cédula de Identidade RG n.º ".$dadosLinha["nr_identif_1"]." e inscrito no CPF/MF sob n.º ".$dadosLinha["nr_identif"].", residente e ").
			utf8_decode(" domiciliado à ".$dadosLinha["txt_endereco"]." em ".$dadosLinha["txt_cidade"]." - ".$dadosLinha["sigla_estado"].", CEP ".$dadosLinha["cep"]).
			utf8_decode(", doravante denominado simplesmente de DEVEDOR, têm entre si justo e contratado o que adiante segue:"),0,1 ,"J");
			}

		$sqlDadosAcordo = "select 
    								concat(nr_parcela, '/', qtde_parcelas) as parcela, 
    								dt_vencimento,
    								vlr_parcela
							from
    								acordos_detalhes
						   where
    								cd_acordo = {$cdAcordo}";
			
		$arrDadosAcordo = P4A_DB::singleton()->fetchAll($sqlDadosAcordo);
		
		foreach($arrDadosAcordo as $acordo => $dadosAcordo)
			{
			$somaParcela += $dadosAcordo["vlr_parcela"];
			$vencimentoParcelas .= formatarDataAplicacao($dadosAcordo["dt_vencimento"]).", ";
			}

		// Parágrafo 1
		$this->Ln();
		$this->setX("15");
		$this->Multicell(185,6,utf8_decode("1. Neste ato o DEVEDOR reconhece e confessa seu débito para com o CREDOR no valor de R$ ".number_format($somaParcela,2,",",".")).
		utf8_decode(" (".valorPorExtenso($somaParcela,false)."), correspondente às taxas de condomínio e demais encargos relativos a unidade nº ").
		utf8_decode($dadosLinha["cd_unidade"]." do ".$dadosCondominio["nm_condominio"]." vencidos em ".$vencimentoParcelas ." atualizados monetariamente, acrescidos de juros moratórios,").
		utf8_decode(" e multa."),0,1 ,"J");

		// Parágrafo 2
		$this->Ln();
		$this->setX("15");
		$this->Multicell(185,6,utf8_decode("2. Para quitar referido débito o DEVEDOR compromete-se a efetuar o pagamento conforme o quadro abaixo:"),0,1 ,"J");
		
		$this->setX("50");
		$this->cell(40,5,"Parcela/Total",1,0,"C");
		$this->cell(40,5,"Data de vencimento",1,0,"C");
		$this->cell(40,5,"Valor da parcela",1,0,"C");
		$this->Ln();
		
		foreach($arrDadosAcordo as $acordo => $dadosAcordo)
		{
			$this->setX("50");
			$this->cell(40,5,utf8_decode($dadosAcordo["parcela"]),1,0,"C");
			$this->cell(40,5,utf8_decode(formatarDataAplicacao($dadosAcordo["dt_vencimento"])),1,0,"C");
			$this->cell(40,5,utf8_decode("R$ ".number_format($dadosAcordo["vlr_parcela"],2,",",".")),1,0,"C");
			$this->Ln();
		}
		
		// Parágrafo 3
		$this->Ln();
		$this->setX("15");
		$this->Multicell(185, 6, utf8_decode("3. O pagamento de todas as parcelas será realizado por meio de boleto bancário, conforme tabela acima, expedido pelo CREDOR e enviados ao DEVEDOR em seu endereço de cobrança."),0,1,"J");

		// Parágrafo 4
		$this->Ln();
		$this->setX("15");
		$this->Multicell(185,6,utf8_decode("4. O inadimplemento de quaisquer parcelas descritas no item 2., bem como das taxas condominais vincendas relativas a unidade ".$dadosLinha["cd_unidade"]).
		utf8_decode(", acarretará no vencimento antecipado do débito sem prejuízo da aplicação de multa de 10% (dez por cento) sobre o valor do débito remanescente, atualização monetária e").
		utf8_decode(" acréscimo de juros moratórios de 1% (um por cento) a.m."),0,1,"J");

		// Parágrafo 5
		$this->Ln();
		$this->setX("15");
		$this->Multicell(185,6,utf8_decode("5. Fica pactuado ainda que em caso de inadimplemento, o débito será cobrado pela via executiva judicial, sem qualquer aviso,").
		utf8_decode(" notificação ou protesto, sendo que neste caso os DEVEDOR arcará ainda com as respectivas custas judiciais e honorários advocatícios."),0,1,"J");
		
		// Parágrafo 6
		$this->Ln();
		$this->setX("15");
		$this->Multicell(185,6,utf8_decode("6. O presente instrumento é firmado em caráter irrevogável e irretratável, obrigando além das partes seus eventuais herdeiros ou sucessores a qualquer título."),0,1,"J");

		// Parágrafo 7
		$this->Ln();
		$this->setX("15");
		$this->Multicell(185,6,utf8_decode("7. Efetuados os pagamentos de todas as parcelas previstas neste instrumento, considerar-se- á quitado pelo CREDOR o débito descrito ").
		utf8_decode(" no item 1. para nada mais pleitear seja a que tempo ou a que título for com relação às taxas condominiais, rateios e demais encargos especificados no presente instrumento."),0,1,"J");

		//Parágrafo 8
		$this->Ln();
		$this->setX("15");
		// Parâmetro foro da comarca 
		$foroComarca = condgest::singleton()->getParametro("FORO_COMARCA");
		$this->Multicell(185,6,utf8_decode("8. Elegem o foro da Comarca de ".$foroComarca.", para a resolução de quaisquer questões oriundas do presente instrumento, com a renúncia").
		utf8_decode(" de qualquer outro por mais privilegiado que seja. E, por estarem assim, justos e contratados, firmam o presente instrumento em 02 (duas) vias de igual teor, ").
		utf8_decode(" na presença de 02 (duas) testemunhas."),0,1,"J");
		
		// Local e data do evento
		$this->Ln();
		$this->Ln();
		$this->setX("15");
		$this->cell(185,10,utf8_decode($dadosCondominio["municipio_condominio"]. ", ".formata_data_extenso(date("Y-m-d"),false)."."),0,0,"R");
		
		// Assinaturas
		$this->Ln();
		$this->Ln();
		$this->setX("15");
		$this->setFont("ArialBlack","",11);
		$this->cell(185,5,"CREDOR",O,O,"L");
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
		$this->Ln();
		$this->Ln();
		$this->Ln();
		$this->setX("15");
		$this->setFont("ArialBlack","",11);
		$this->cell(185,5,"DEVEDOR",O,O,"L");
		$this->Ln();
		$this->Ln();
		$this->Ln();
		$this->setFont("tahoma","",11);
		$this->setX("15");
		$this->cell(185,5,utf8_decode($dadosLinha["nm_pessoa"]),O,O,"L");
		$this->Ln();
		$this->setX("15");
		$this->cell(185,5,utf8_decode($dadosLinha["nr_identif"]),O,O,"L");
		$this->Ln();
		$this->Ln();
		$this->Ln();
		$this->setX("15");
		$this->setFont("ArialBlack","",11);
		$this->cell(185,5,"TESTEMUNHAS",O,O,"L");
		$this->Ln();
		$this->Ln();
		$this->Ln();
		$this->setFont("tahoma","",11);
		$this->setX("15");
		$this->cell(100,5,utf8_decode("NOME:"),0,O,"L");
		$this->cell(50,5,utf8_decode("RG Nº."),0,0,"L");
		$this->Ln();
		$this->Ln();
		$this->Ln();
		$this->setX("15");
		$this->cell(100,5,utf8_decode("NOME:"),0,O,"L");
		$this->cell(50,5,utf8_decode("RG Nº."),0,0,"L");
		$this->Ln();
		} 
		
	function Footer()
		{
		$this->setFont("tahoma","",8);
		$this->cell(210,10,utf8_decode("Sistema de Gestão Condominial - Condgest"),0,1,"C");
		}
	
	function Output()
		{
		return parent::Output("confissaoDivida.pdf","S");
		}
	}