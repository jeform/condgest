<?php
class entradaSistema extends satecmax_mask
	{
	function __construct()
		{
		parent::__construct();
		
		$this->setTitle(__("Sistema de Gestão de Condomínio"));
/*		
		$this->build("p4a_image","image_logo")
			->setIcon("images/banner_cadastro.jpg");
*/				
		$this->build("p4a_frame","frm");
			//->anchor($this->image_logo);


		$this->display("menu",p4a::singleton()->menu);
		$this->display("main",$this->frm);
		}
	}
