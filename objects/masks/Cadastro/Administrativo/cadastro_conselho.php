<?php

class cadastro_conselho extends satecmax_mask
{
	function __construct()
	{
		parent::__construct();
		
		$this->setTitle("Cadastro do Conselho Consultivo");
		
		$this->build("satecmax_db_source","src_conselho")
			->setTable("tbl_conselho")
			->setPk("cd_conselho")	
			->Load()
			->firstRow();

		$this->build("p4a_db_source","src_admin_cadastrados")
			->setTable("tbl_pessoas")
			->setWhere(" cd_pessoa in (select cd_pessoa from tbl_pessoas_tipo_pessoas where cd_tipo_pessoa = 4)")
			->Load();			
	
		$this->build("p4a_table","tbl_conselho")
			->setSource($this->src_conselho)
			->setLabel(__("Lista de Conselheiros Cadastrados"))
			->setWidth(600);
			
		$this->tbl_conselho->cols->cd_conselho->setLabel(__("Cód."));
		$this->tbl_conselho->cols->cd_pessoa->setLabel(__("Nome Pessoa"))
											->setSource($this->src_admin_cadastrados)
											->setSourceDescriptionField("nm_pessoa")
											->setSourceValueField("cd_pessoa");
		$this->tbl_conselho->cols->ds_funcao->setLabel(__("Cargo"))
											->setSource(P4A::singleton()->src_cargo_conselho)
											->setSourceValueField("cd_cargo_conselho")
											->setSourceDescriptionField("ds_cargo_conselho");
		$this->tbl_conselho->cols->dt_inicio_mandato->setLabel(__("Dt. Início Mandato"));
		$this->tbl_conselho->cols->dt_final_mandato->setLabel(__("Dt. Fim Mandato"));
		
		
		$this->tbl_conselho->setVisibleCols(array("cd_pessoa","ds_funcao","dt_inicio_mandato","dt_final_mandato"));
		
		$this->setSource($this->src_conselho);
					
		$this->build("satecmax_full_toolbar","toolbar")
			->setMask($this);
			
		$this->toolbar->buttons->new->implement("onClick",$this,"novo_cadastro_conselho");		

		$this->build("p4a_fieldset","fset_conselho")
			->setLabel(__("Detalhes"))
			->setWidth(500);	
			
		$this->build("p4a_frame","frm")
			->setWidth(1024);			

			
		$this->fset_conselho->anchor($this->fields->cd_conselho)
							->anchor($this->fields->cd_pessoa)
							->anchor($this->fields->ds_funcao)
							->anchor($this->fields->dt_inicio_mandato)
							->anchor($this->fields->dt_final_mandato);	

		$this->setFieldsProperties();
		
		$this->frm->anchorCenter($this->tbl_conselho);
		$this->frm->anchorCenter($this->fset_conselho);		

		$this->addObjEsconderEdicao($this->tbl_conselho);
		
		$this->display("main",$this->frm);
		
		$this->display("menu",p4a::singleton()->menu);
		
		$this->display("top",$this->toolbar);
		
	}		
	
	function setFieldsProperties()
	{
		$fields = $this->fields;	
		
		$fields->cd_conselho->setLabel(__("Cód."))
							->setInvisible()
							->setWidth(50);

		$fields->cd_pessoa->setLabel(__("Nome Pessoa"))
							->setSource($this->src_admin_cadastrados)
							->setSourceDescriptionField("nm_pessoa")
							->setSourceValueField("cd_pessoa")
							->setType("select")
							->setWidth(300);

		$fields->ds_funcao->setLabel(__("Cargo"))
						->setSource(P4A::singleton()->src_cargo_conselho)
						->setSourceValueField("cd_cargo_conselho")
						->setSourceDescriptionField("ds_cargo_conselho")
						->setType("select");

		$fields->dt_inicio_mandato->setLabel(__("Data Início Mandato"))
								->setWidth(80);
		
		$fields->dt_final_mandato->setLabel(__("Data Fim Mandato"))
								->setWidth(80);
	}
		
	function novo_cadastro_conselho()
	{
		$this->tbl_conselho->setInvisible(true);
		$this->src_conselho->newRow();	
	}		
				
	function saveRow()
	{		
		$dt_inicio = $this->fields->dt_inicio_mandato->getNewValue();		
		$dt_fim = $this->fields->dt_final_mandato->getNewValue();	
          
		$intervalo = intervaloData($dt_inicio,$dt_fim);

		$par_intervalo_mandato = condgest::singleton()->getParametro("INTERVALO_MANDATO_CONSELHO");
		
        if ($intervalo > $par_intervalo_mandato)
		{
            $this->error("O mandato do conselheiro deve ser igual a ".$par_intervalo_mandato." dias! Favor verificar os parâmetros do sistema.");
            return false;
		}       
		
		$conselheiros_cadastrados = $this->fields->cd_pessoa->getNewValue();
		
		if ( p4a_db::singleton()->fetchOne("select count(*) from tbl_conselho  where cd_pessoa = '{$conselheiros_cadastrados}'") > 0 )
		{
			$this->error(__("Conselheiro já cadastrado!"));
			return false;
		}
		
		$sindico_cadastrado = $this->fields->ds_funcao->getNewValue();
		if ( p4a_db::singleton()->fetchOne("select count(*) from tbl_conselho  where ds_funcao = 1 and ds_funcao ='{$sindico_cadastrado}'") > 0 )
		{
			$this->error(__("Já existe um síndico cadastrado!"));
			return false;
		}
		
		$subsindico_cadastrado = $this->fields->ds_funcao->getNewValue();
		if ( p4a_db::singleton()->fetchOne("select count(*) from tbl_conselho  where ds_funcao = 2 and ds_funcao ='{$subsindico_cadastrado}'") > 0 )
		{
			$this->error(__("Já existe um sub-síndico cadastrado!"));
			return false;
		}

		$conselheiro_cadastrado = $this->fields->ds_funcao->getNewValue();
		if ( p4a_db::singleton()->fetchOne("select count(*) from tbl_conselho  where ds_funcao = 3 and ds_funcao ='{$conselheiro_cadastrado}'") > 7 )
		{
			$this->error(__("Número de conselheiros excedido!"));
			return false;
		}
			
		return parent::saveRow();
	}
}