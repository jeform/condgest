<?php
class alterarSenha extends satecmax_mask
	{
	function __construct()
		{
		parent::__construct();
		$this->setTitle(__("Alterar Senha"));
		
		$this->build("p4a_frame","frm")
			->setwidth(400);
			
		$this->build("p4a_field","fld_nova_senha")	
			->setLabel(__("Nova Senha"))
			->setType("password");
			
		$this->build("p4a_field","fld_nova_senha2")
			->setLabel(__("Reconfirme Nova Senha"))
			->setType("password");
			
		$this->build("p4a_button","btn_altera_senha")
			->setLabel(__("Alterar senha e continuar!!"))
			->implement("onClick",$this,"alteraSenha");
			
		$this->frm->anchor($this->fld_nova_senha)
			->anchor($this->fld_nova_senha2)
			->anchor($this->btn_altera_senha);
			
		$this->display("main",$this->frm);
		}
		
	function validaNovaSenha()
		{
		$nova_senha1 = $this->fld_nova_senha->getNewValue();
		$nova_senha2 = $this->fld_nova_senha2->getNewValue();
		$senha_atual = p4a::singleton()->user_login->fields->password->getValue();
		
		if ( $nova_senha1 <> $nova_senha2 )
			{
			$this->error(__("As senhas não conferem!"));
			return false;
			}
			
		if ( $nova_senha1 == $senha_atual )
			{
			$this->error(__("A senha deve ser diferente da atual!"));
			return false;
			}
		
		return true;
		}
		
	function alteraSenha()
		{
		if ( $this->validaNovaSenha() )
			{
			condgest::singleton()->user_login->fields->password->setNewValue($this->fld_nova_senha->getNewValue());
			//p4a::singleton()->user_login->fields->st_altera_senha->setNewValue(false);
			//p4a::singleton()->user_login->fields->dt_ult_alt_senha->setNewValue(date("Y-m-d H:i:s"));
			
			condgest::singleton()->user_login->saveRow();
			
			$this->info(__("Senha alterada com sucesso!"));
			
			condgest::singleton()->showPrevMask();
			
			$this->destroy();
			}
		}
	}