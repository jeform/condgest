<?php
class satecmax_full_toolbar extends P4A_Toolbar
	{
	function __construct($name)
		{
		parent::__construct($name);
		$this->addDefaultButtons();
		$this->formatLabel();
		}
		
	function formatLabel()
		{
		$this->buttons->new->setLabel(__("Novo Registro"),true);
		$this->buttons->save->setLabel(__("Salvar"),true);
		$this->buttons->cancel->setLabel(__("Cancelar"),true);
		$this->buttons->delete->setLabel(__("Excluir"),true);
		$this->buttons->print->setLabel(__("Imprimir"),true);
		$this->buttons->exit->setLabel(__("Voltar"),true);
		}
		
	function addDefaultButtons()
		{
		$new =& $this->addButton('new', 'actions/document-new');
		$new->setProperty("accesskey", "N");
		$new->setLabel(__("Incluir"),true);
		
		$edit = &$this->addButton("edit","actions/document-edit");
		$edit->setProperty("accesskey","E");
		$edit->setLabel(__("Editar"),true);
		$edit->setVisible(false);
		
		$save =& $this->addButton('save', 'actions/document-save');
		$save->setAccessKey("S");
		$save->setLabel(__("Salvar"),true);

		$cancel =& $this->addButton('cancel', 'actions/edit-undo');
		$cancel->setAccessKey("Z");
		$cancel->setLabel(__("Cancelar"),true);
		
		$this->addSeparator();			
		
		$delete = & $this->addButton('delete', 'actions/edit-delete');
		$delete->addAction("onclick");
		$delete->requireConfirmation("onClick",__("Deseja realmente excluir?"));
		$delete->setLabel(__("Excluir"),true);		
		$delete->setVisible(false);	
		
		$this->addSeparator();

		$print =& $this->addButton('print', 'actions/document-print');
		$print->dropAction('onclick');
		$print->setProperty('onclick', 'window.print(); return false;');
		$print->setAccessKey("P");
		$print->setLabel(__("Imprimir"),true);
		$print->setInvisible();

		$exit =& $this->addButton('exit', 'actions/window-close', 'right');
		$exit->setAccessKey("X");
		$exit->setLabel(__("Voltar"),true);
		}

	public function setMask(P4A_Mask $mask)
		{
		$this->_mask_name = $mask->getName();
		$this->buttons->save->implement('onclick', $mask, 'saveRow');
		$this->buttons->cancel->implement('onclick', $mask, 'reloadRow');
		$this->buttons->new->implement('onclick', $mask, 'newRow');
		$this->buttons->delete->implement('onclick', $mask, 'deleteRow');
		$this->buttons->exit->implement('onclick', $mask, 'showPrevMask');
		$this->buttons->edit->implement('onClick',$mask,'setStatusMode');
		return $this;
		}
	
	function getAsString()
		{
		$mask =& p4a_mask::singleton($this->_mask_name);

		if ($mask->getSource()->isNew()) 
			{
			$this->buttons->delete->enable(FALSE);
			} 
		else 
			{
			$this->buttons->delete->enable(TRUE);
			}
		
		$this->buttons->s0->setVisible($this->buttons->delete->isVisible());
		$this->buttons->s1->setVisible($this->buttons->print->isVisible());
		return parent::getAsString();
		}		
	}