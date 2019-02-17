<?php
class cadastro_login extends satecmax_mask
	{
		
	/**
	 * P4A_Frame
	 */
	public $frame = null;
	/**
	 * @var P4A_Field
	 */
	public $username = null;

	/**
	 * @var P4A_Field
	 */
	public $password = null;
	
	/**
	 * @var P4A_Button
	 */
	public $go = null;
	
	public function __construct()
		{
		parent::__construct();
		
		$this->build("P4A_Field", 'username')
			->addAction('onreturnpress')
			->addAjaxAction('onreturnpress')
			->implement('onreturnpress', $this, 'login');
		
		$this->build('P4A_Field', 'password')
			->setType('password')
			->addAjaxAction('onreturnpress')
			->implement('onreturnpress', $this, 'login');
		
		$this->build('P4A_Button', 'go')
			->addAjaxAction('onclick')
			->implement('onclick', $this, 'login');

		$this->build('P4A_Frame', 'frame')
			->setStyleProperty('margin-top', '100px')
			->setStyleProperty('margin-bottom', '50px')
			;

		$this->build("p4a_fieldset","fset_login")
			->setWidth(300)
			->setLabel(__("Login Administrador"))
			->anchor($this->username)
			->anchor($this->password)
			->anchorCenter($this->go);
			
		$this->frame->anchor($this->fset_login);
		
		$this->setTitle(__("Login de Acesso ao Sistema de Gestão de Condomínio"));
		
		
		$this->username->setLabel(__("Login:"));
		$this->password->setLabel(__("Senha:"));
		$this->go->setLabel(__("Entrar >>"),true);
		
			
		$this
			->display('main', $this->frame)
			->setFocus($this->username);
		}
	
	public function login()
		{
		$this->actionHandler('onLogin');
		}
	}