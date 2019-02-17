<?php
class caixa_movimento_transferencia extends satecmax_mask
	{
	/**
	 * 
	 * Enter description here ...
	 * @var P4A_Collection
	 */
	public $campos;
	
	private $cdCaixa;
	
	function __construct()
		{
		parent::__construct();
		
		$this->build("p4a_collection","campos");		
		
		$this->setTitle(__("Movimento de Transferencia"));
		
		$this->campos->build("p4a_field","fldDtMovimento")
			->setLabel(__("Data Movimento"));
			
		$this->campos->build("p4a_field","fldVlrMovimento")
			->setLabel(__("Valor Movimento"));
			
		$this->campos->build("p4a_field","fldCdCaixaDestino")
			->setLabel(__("Caixa destino"));
			
		$this->campos->build("p4a_field","fldCdDocumentoReferencia")
			->setLabel(__("Documento Referencia"));
						
		$this->setFieldsProperties();

		$this->build("p4a_frame","frm")
			->setWidth(800);
			
		$this->frm->anchor($this->campos->fldDtMovimento)
			->anchor($this->campos->fldVlrMovimento)
			->anchor($this->campos->fldCdDocumentoReferencia)
			->anchor($this->campos->fldCdCaixaDestino)
			;
			
			
		$this->build("p4a_button","btnProcessar")
			->setLabel(__("Criar Movimento"))
			->implement("onClick",$this,"gerarMovimento");
			
		$this->frm->anchorCenter($this->btnProcessar);
			
		$this->display("main",$this->frm);
		}
		
	function setFieldsProperties()
		{
		$fields = $this->campos;
		
		$fields->fldDtMovimento->setType("date");
		
		$fields->fldVlrMovimento->setProperty("dir","rtl");
		
		$this->build("p4a_db_source","srcCaixa")
			->setTable("caixa")
			->setPk("cd_caixa")
			->setWhere(" 1 = 0")
			->Load();
			
		$fields->fldCdCaixaDestino->setSource($this->srcCaixa)
			->setSourceDescriptionField("ds_caixa")
			->setSourceValueField("cd_caixa")
			->setType("select");
			
		}
		
	function setCaixa($cdCaixa)
		{
		$this->cdCaixa = $cdCaixa;
		
		$this->srcCaixa->setWhere("cd_caixa not in ( $this->cdCaixa ) and st_caixa = 1 ");
		}
		
	function gerarMovimento()
		{
		
		try
			{
			P4A_DB::singleton()->beginTransaction();
			$objMovimento = new movimentoCaixa($this->cdCaixa);
			
			$objMovimento->newMovimentoTransferencia($this->campos->fldCdCaixaDestino->getNewValue(), 
													$this->campos->fldDtMovimento->getNewValue(), 
													$this->campos->fldVlrMovimento->getNewValue(),
													$this->campos->fldCdDocumentoReferencia->getNewValue() 
													);
		
			P4A_DB::singleton()->commit();
			
			$this->info(__("Registro criado com sucesso!"));
			
			$this->showPrevMask();
			}
		catch (Exception $e)
			{
			P4A_DB::singleton()->rollback();
			
			$this->error(__("Erro: ".$e->getCode()." - ".$e->getMessage()));
			
			}
												
		}
	}