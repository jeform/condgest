<?php
class classBradescoRemessa
	{
			
	private $_strRetorno = "";
	
	private $_nrRegistroAtual = 0;
	
	private $_qtdeRegistros = 0;
	
	private $_stHeader = false;
	
	private $_stTransacao = false;
	
	private $_stTrailer = false;
	
	private $_nrCarteira = "";
	
	private $_nrAgencia = "";
	
	private $_digitoAgencia = "";
	
	private $_nrCC = "";
	
	private $_digitoCC = "";
		
	public function __construct(	$codigoEmpresa, 
									$nomeEmpresa, 
									$nrRemessa,
									$nrCarteira,
									$nrAgencia,
									$nrCC,
									$dataTS = ""
									)
		{
		if ( $dataTS == "" )
			{
			$dataTS = mktime();
			}
			
		$this->_nrCarteira = $nrCarteira;
		
		$arrAgencia = explode("-", $nrAgencia);
		
		$this->_nrAgencia = $arrAgencia[0];
		
		$this->_digitoAgencia = $arrAgencia[1];
		
		$arrCC = explode("-", $nrCC);
			
		$this->_nrCC = $arrCC[0];
		
		$this->_digitoCC = $arrCC[1];
		
		$this->_nrRegistroAtual++;
			
		$this->_strRetorno.= classBradescoHeader::getStringHeader($codigoEmpresa, $nomeEmpresa, $dataTS, $nrRemessa);
		
		$this->_stHeader = true;
		}
		
	public function adicionarBoletoRemessa(	$nrDocumento, 
											$dataVencimento, 
											$valorTitulo, 
											$dtLimiteDesconto, 
											$vlDesconto, 
											$identificacaoSacado, 
											$nomeSacado, 
											$enderecoSacado, 
											$mensagem1, 
											$nrCEP, 
											$mensagem2, 
											$nrRegistroArquivo)
		{
		$this->_nrRegistroAtual++;
		
		$this->_strRetorno.= classBradescoTransacao::getStringTransacao(
																			$this->_nrCarteira, 
																			$this->_nrAgencia, 
																			$this->_nrCC, 
																			$this->_digitoCC, 
																			$assinaturaSistema, 
																			$nrDocumento, 
																			$dataVencimento, 
																			$valorTitulo, 
																			$dtLimiteDesconto, 
																			$vlDesconto, 
																			$identificacaoSacado, 
																			$nomeSacado, 
																			$enderecoSacado, 
																			$mensagem1, 
																			$nrCEP, 
																			$mensagem2, 
																			$nrRegistroArquivo);
																			
		$this->_stTransacao = true;
		}
		
	public function fechaArquivoTrailer()
		{
		
		$this->_nrRegistroAtual++;
		
		$this->_strRetorno.= classBradescoTrailer::getStringTrailer($this->_nrRegistroAtual);
		
		$this->_stTrailer = true;
		}
		
	public function __toString()
		{
		return $this->getDadosRemessa();
		}
		
	public function getDadosRemessa()
		{
		if ( !$this->_stHeader or !$this->_stTransacao or !$this->_stTrailer )
			{
			throw new Exception("Erro na montagem do arquivo de remessa. Pendente Alguma parte do arquivo!", -9999);
			}
			
		return $this->_strRetorno;
		}
	}