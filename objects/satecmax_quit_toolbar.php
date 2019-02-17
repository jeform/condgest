<?php
class satecmax_quit_toolbar extends P4A_Toolbar
{
	/**
	 * @param string $name Mnemonic identifier for the object
	 */
	public function __construct($name)
	{
		parent::__construct($name);
		$this->addDefaultButtons();
	}
	
	private function addDefaultButtons()
	{
		$this->addButton('exit', 'actions/window-close', 'right')
			->setLabel("Go back to the previous mask")
			->setAccessKey("X")
			->implement('onclick', P4A::singleton(), 'showPrevMask');
	}
}