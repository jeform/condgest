<?php
class satecmax_table extends P4A_Table
	{
	public function __construct($name)
		{
		parent::__construct($name);
		
		$this->addCSSClass("p4a_table");
		$this->showNavigationBar();
		$this->showRowIndicator();
		$this->showHeaders();
		$this->showElementsOnPageBar();
		
		$this->navigation_bar->addButton("export_excel","mimetypes/Excel-icon");
		
		$this->navigation_bar->buttons->export_excel->setLabel(__("Exportar"),true);
		
		$this->navigation_bar->buttons->export_excel->implement("onclick",$this,"exportarXLS");
		}
		
	function exportarXLS()
		{
		$arr_dados = $this->data->getAll();
		
		$objExcel = new PHPExcel();
		
		$objExcel->getProperties()->setCompany("Votorantim");
		
		$objExcel->getProperties()->setTitle("Exportação Data: ".date("d/m/Y"));
		
		$objExcel->getActiveSheet()->setTitle("Exportação");
		
		//$objExcel->getActiveSheet()->setCellValueByColumnAndRow(1,1,"ID");
		
		//$cabecalho = "ID;Coordenador UN;Coordenador VID;Visita;Data Inclusão;Semana Inclusão;UN;Negócio;Unidade;Área;SSTI;Cockpit;Ação PMO;Prioridade;Nome do Projeto / Demanda / Atividade;Descrição BREVE;Responsável pela Gestão do Projeto;Categoria;Recurso (Interno ou Consultoria);Início Planejado;Fim Planejado;Início Real;Fim Real;Semana;Status;Diretoria;Solicitante;Ponto Focal;Observação;Plano de Ação;Farol;Esforço Horas";
		$arrCabecalho = $this->getVisibleCols();
		
		$arrCabecalho1 = $arrCabecalho;		
		
		foreach($arrCabecalho as $nmCabecalho)
			{
			$arrCabecalho2[] = $this->cols->$nmCabecalho->getLabel();
			}
		
		$arrCabecalho = $arrCabecalho2;
		$coluna=0;
		foreach($arrCabecalho as $nmCabecalho)
			{
			$objExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna,1,$nmCabecalho);
			$objExcel->getActiveSheet()->getStyleByColumnAndRow($coluna,1)->getFont()->setBold(true);
			$objExcel->getActiveSheet()->getStyleByColumnAndRow($coluna,1)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('C0C0C0');
			$objExcel->getActiveSheet()->getColumnDimensionByColumn($coluna,1)->setAutoSize(true);
			$coluna++;
			}
		// carregar os dados...

		$linha = 2;
		foreach($arr_dados as $nrLinha=> $dadosLinha)
			{
			$coluna = 0;
			foreach($arrCabecalho1 as $nmColuna)
				{
				$objExcel->getActiveSheet()->setCellValueByColumnAndRow($coluna,$linha,$dadosLinha[$nmColuna]);
				//$objExcel->getActiveSheet()->getStyleByColumnAndRow($coluna,$linha)->getFont()->setBold(true);
				//$objExcel->getActiveSheet()->getStyleByColumnAndRow($coluna,$linha)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('C0C0C0');
				
				$objExcel->getActiveSheet()->getColumnDimensionByColumn($coluna,$linha)->setAutoSize(true);
				$coluna++;
				}
			$linha++;
			}
		
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="export_'.date("dmY-Hi").'.xlsx"');
		header('Cache-Control: max-age=0');
		
		$objXLSWriter = PHPExcel_IOFactory::createWriter($objExcel, 'Excel2007');
		$objXLSWriter->save('php://output');
		exit;
		
		
		}
	}