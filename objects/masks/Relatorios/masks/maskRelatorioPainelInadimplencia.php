<?php
class maskRelatorioPainelInadimplencia extends satecmax_mask
	{
	function __construct()
		{
		parent::__construct();
		
		$this->setTitle(__(".: Painel de inadimplencia :."));
		
		$this->build("p4a_frame","frm");
		
		$this->build("p4a_quit_toolbar","toolbar");
		
		$this->build("p4a_fieldset","fsetFiltros")
			->setLabel(__("Filtros"));
			
		$this->frm->anchor($this->fsetFiltros);
			
		$this->montaFiltros();
		
		$this->display("main",$this->frm);
		$this->display("menu",condgest::singleton()->menu);
		$this->display("top",$this->toolbar);
		}

	function montaFiltros()
		{
		$this->fsetFiltros->clean();

		// montar o array_source...
		for($a = 1; $a <= 13; $a++)
			{
			$intMesAnterior = strtotime("-{$a} month");
			$strMesAnterior = date("m_Y",$intMesAnterior);
			
			$arrFieldsMesesesAnteriores[] = array("mes"=>date("m",$intMesAnterior),"ano"=>date("Y",$intMesAnterior),"strMes"=>$strMesAnterior);
			}
		
		// montar os campos...
		
		foreach($arrFieldsMesesesAnteriores as $dadosMes)
			{
			$arrCampos[] = array("ano"=>$dadosMes["ano"],"mes"=>$dadosMes["mes"],"string"=>$dadosMes["strMes"]);
			}
			
		asort($arrCampos);

		// pegar a listagem das unidades...
		$sqlUnidades = "
							select
								a.cd_unidade,
								b.nm_pessoa
							from
								tbl_unidades a
							inner join tbl_pessoas b on a.cd_pessoa = b.cd_pessoa
							where
								a.st_unidade = 1		
						";
		
		$arrUnidadesPessoas = P4A_DB::singleton()->fetchAll($sqlUnidades);
		
		foreach($arrUnidadesPessoas as $dadosUnidadesPessoas)
			{
			//$arrDadosLinhaListagem["cd_unidade"] = $dadosUnidadesPessoas["cd_unidade"];
			
			$arrDadosLinhaListagem["nm_unidade"] = $dadosUnidadesPessoas["cd_unidade"]." - ".$dadosUnidadesPessoas["nm_pessoa"];
			
			foreach($arrCampos as $dadosCamposMeses)
				{
				//$arrDadosLinhaListagem[$dadosCamposMeses["string"]] = "Pendente";
				/*
				$sqlValidaBaixa = " 
									select
										a.st_baixado
									from
										tbl_boleto_mes_unidade a
										inner join tbl_boleto_mes b on a.cd_boleto_mes = b.cd_boleto_mes
									where
										a.cd_unidade = ?
										and b.mes_ano_referencia = ?
									";
				
				$stBaixado = P4A_DB::singleton()->fetchOne($sqlValidaBaixa, array($dadosUnidadesPessoas["cd_unidade"],"10/".$dadosCamposMeses["mes"]."/".$dadosCamposMeses["ano"]));
				*/
					
				//Solicitado em reunião no dia 13/01/2016 pelo Sr. Nelson, a alteração do processo para recuperar os dados utilizando a data de vencimento e não mais o mês/ano de competência	
				$sqlValidaBaixa = "
									select
										a.st_baixado
									from
										tbl_boleto_mes_unidade a
										inner join tbl_boleto_mes b on a.cd_boleto_mes = b.cd_boleto_mes
									where
										a.cd_unidade = ?
										and a.dt_vencimento = ?
									";
				
				$stBaixado = P4A_DB::singleton()->fetchOne($sqlValidaBaixa, array($dadosUnidadesPessoas["cd_unidade"],$dadosCamposMeses["ano"]."-".$dadosCamposMeses["mes"]."-10"));
				
				if ( $stBaixado == 1 )
					{
					$arrDadosLinhaListagem[$dadosCamposMeses["string"]] = "OK";
					}
				else
					{
					$arrDadosLinhaListagem[$dadosCamposMeses["string"]] = "Pend.";
					}
				}
				
			$arrLinhasListagem[] = $arrDadosLinhaListagem;
			}

		$this->build("p4a_array_source","arrDadosListagem")
			->load($arrLinhasListagem)
			->setPk("nm_unidade")
			->setPageLimit(count($arrLinhasListagem));
		
		
			
		$this->build("satecmax_table","tblDadosListagem")
			->setSource($this->arrDadosListagem)
			
			;
			
		$this->tblDadosListagem->cols->nm_unidade->setLabel(__("Unidade"));
		
		foreach($arrCampos as $dadosCamposMeses)
			{
			$nmCampo = $dadosCamposMeses["string"];
			$this->tblDadosListagem->cols->$nmCampo->setLabel("10/".$dadosCamposMeses["mes"]."/".$dadosCamposMeses["ano"]);
			}
			
		$this->intercept($this->tblDadosListagem->rows, "beforeDisplay", "view_table");
			
		$this->fsetFiltros->anchor($this->tblDadosListagem);
		
		}
		
	function view_table($event, $rows)
		{ 
		for( $a = 0; $a < count($rows); $a++)
			{
			foreach($rows[$a] as $nm_campo => $valorCampo)
				{
				if ( $valorCampo == "Pend.")
					{
					$rows[$a][$nm_campo] = "<strong><font style=\"color: #FF0000\">".$rows[$a][$nm_campo]."</font></strong>";
					}
				}
			}
		return $rows;
		}
		
	}