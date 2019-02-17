<?php
class itens_cobranca_boleto extends satecmax_mask
	{	
	function __construct()
		{
		parent::__construct();
		
		$this->setTitle(__("Ítens do Boleto de Cobrança"));
		
		$this->build("satecmax_db_source","src_itens_cobranca")
			->setTable("tbl_itens_cobranca_boleto")
			->setPk("cd_item_cobranca")
			->Load()
			->firstRow();
		
		$this->build("p4a_fieldset","fset_itens_cobranca")
			->setLabel(__("Detalhes"))
			->setWidth(500);

		$arr_tipo_valor[] = array("cd_tipo_valor"=>0, "desc"=>"Calculado");
		$arr_tipo_valor[] = array("cd_tipo_valor"=>1, "desc"=>"Fixo");
		$arr_tipo_valor[] = array("cd_tipo_valor"=>2, "desc"=>"Rateio");
		
		$this->build("p4a_array_source","src_tipo_valor")
			->setPk("cd_tipo_valor")
			->Load($arr_tipo_valor);
		
		$this->build("satecmax_db_source","src_categoria_boleto")
			->setTable("tbl_categorias")
			->setPk("cd_categoria")
			->setWhere("tp_categoria 	= 1")
			->Load()
			->FirstRow();
			
		$this->build("p4a_table", "tbl_itens_cobranca")
			->setSource($this->src_itens_cobranca)
			->setLabel(__("Lista de Ítens Cobrança"))
			->setWidth(500);
		
		$this->tbl_itens_cobranca->cols->ds_item_cobranca->setLabel(__("Descrição"))
														->setWidth(350);
		$this->tbl_itens_cobranca->cols->vlr_item_cobranca->setLabel(__("Valor"));	
		
		$this->tbl_itens_cobranca->cols->tp_valor->setLabel(__("Tipo do valor"))
												->setSource($this->src_tipo_valor)
												->setSourceValueField("cd_tipo_valor")
												->setSourceDescriptionField("desc");
								
		$this->tbl_itens_cobranca->setVisibleCols(array("ds_item_cobranca","vlr_item_cobranca","tp_valor"));
		
		$this->setSource($this->src_itens_cobranca);
			
		$this->build("satecmax_full_toolbar","toolbar")
			->setMask($this);
			
		$this->toolbar->buttons->new->setLabel(__("Novo Ítem de Cobrança"),true);
		
		$this->build("p4a_frame","frm")
			->setWidth(1024);
		
		$this->setFieldsProperties();
		
		$this->fset_itens_cobranca->anchor($this->fields->cd_categoria)
								->anchor($this->fields->cd_hist_padrao)
								->anchor($this->fields->cd_caixa)
								->anchor($this->fields->ds_item_cobranca)
								->anchor($this->fields->tp_valor)
								->anchor($this->fields->vlr_item_cobranca);
		
		$this->frm->anchorCenter($this->tbl_itens_cobranca);
		$this->frm->anchorCenter($this->fset_itens_cobranca);
		
		$this->addObjEsconderEdicao($this->tbl_itens_cobranca);
		
		$this->display("main",$this->frm);
		
		$this->display("menu",p4a::singleton()->menu);
		
		$this->display("top",$this->toolbar);
		
		}

	function setFieldsProperties()
		{
		$fields = $this->fields;
		
		$fields->cd_categoria->setLabel(__("Categoria"))			
							->setWidth(250)
							->setSource($this->src_categoria_boleto)
							->setSourceValueField("cd_categoria")
							->setSourceDescriptionField("ds_categoria")
							->setType("select");
		
		$fields->cd_hist_padrao->setLabel(__("Hist. Padrão"))
							->setWidth(250)
							->setSource(P4A::singleton()->src_hist_padrao)
							->setSourceValueField("cd_hist_padrao")
							->setSourceDescriptionField("desc_hist_padrao")
							->setType("select");
		
		$fields->cd_caixa->setLabel(__("Caixa"))
						->setWidth(250)
						->setSource(P4A::singleton()->src_caixas_cadastrados)
						->setSourceValueField("cd_caixa")
						->setSourceDescriptionField("desc_caixa")
						->setType("select");
		
		$fields->ds_item_cobranca->setLabel(__("Descrição"))
								->setWidth(350);
		
		$fields->vlr_item_cobranca->setLabel(__("Valor"))
								->setProperty("dir","rtl")
								->enable(false);
				
		$fields->tp_valor->setLabel(__("Tipo Valor"))
						->setWidth(200)
						->setSource($this->src_tipo_valor)
						->setSourceValueField("cd_tipo_valor")
						->setSourceDescriptionField("desc") 
						->setType("radio")
						->implement("onChange",$this,"exibeCampoValor")
						->label->setWidth("100");
		}

	function exibeCampoValor()
		{
		if($this->fields->tp_valor->getNewValue() == '1')	
 			{
 			$this->fields->vlr_item_cobranca->enable();
 			}
 			 
 		else
 			{
 			$this->fields->vlr_item_cobranca->enable(false);
 			}
		}
	
	function saveRow()
		{			
		if($this->fields->tp_valor->getNewValue() == '0')
			{	
			$this->fields->vlr_item_cobranca->setNewValue('0.00');
			}
		$this->fields->st_item_cobranca->setNewValue('1');	
		
		return parent::saveRow();
		}		
		
		
	function main()
		{	
		parent::main();
		}		
	}