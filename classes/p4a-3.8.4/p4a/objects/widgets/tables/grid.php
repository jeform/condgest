<?php
/**
 * This file is part of P4A - PHP For Applications.
 *
 * P4A is free software: you can redistribute it and/or modify it
 * under the terms of the GNU Lesser General Public License as
 * published by the Free Software Foundation, either version 3 of
 * the License, or (at your option) any later version.
 * 
 * P4A is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public License
 * along with P4A.  If not, see <http://www.gnu.org/licenses/lgpl.html>.
 * 
 * To contact the authors write to:                                     <br />
 * Fabrizio Balliano <fabrizio@fabrizioballiano.it>                     <br />
 * Andrea Giardina <andrea.giardina@crealabs.it>
 *
 * @author Fabrizio Balliano <fabrizio@fabrizioballiano.it>
 * @author Andrea Giardina <andrea.giardina@crealabs.it>
 * @copyright Copyright (c) 2003-2010 Fabrizio Balliano, Andrea Giardina
 * @link http://p4a.sourceforge.net
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @package p4a
 */

/**
 * Tabular rapresentation of a data source.
 * This is a complex widget that's used to allow users to navigate
 * data sources and than (for example) edit a record or view details etc...
 * @author Fabrizio Balliano <fabrizio@fabrizioballiano.it>
 * @author Andrea Giardina <andrea.giardina@crealabs.it>
 * @copyright Copyright (c) 2003-2010 Fabrizio Balliano, Andrea Giardina
 * @package p4a
 */
class P4A_Grid extends P4A_Table
{
	public function __construct($name)
	{
		parent::__construct($name);
		$this->addCssClass('p4a_table');
		$this->useTemplate('grid');
	}
	
	public function preChange($params = null)
	{
		$p4a = p4a::singleton();
		$params[0] = base64_decode($params[0]);

		$col_name = $params[1]; 
		$value = @$params[2];
		
		if ($this->cols->$col_name->isFormatted()) {
			if ($this->cols->$col_name->isActionTriggered('normalize')) {
				$params[3] = $this->cols->$col_name->actionHandler('normalize', $value, $this->data->fields->$col_name->getType(), $this->data->fields->$col_name->getNumOfDecimals());
			} else {
				$params[3] = $p4a->i18n->normalize($value, $this->data->fields->$col_name->getType(), $this->data->fields->$col_name->getNumOfDecimals(), false);
			}
		} 
		return $this->actionHandler('onChange', $params);
	}
	
	//TODO: Add the possibility to call autosave(FALSE)
	public function autoSave()
	{
		$this->intercept($this,'onChange','saveData');
		return $this;
	}
	
	public function saveData($obj,$params)
	{
		$row[$params[1]] = $params[3];
		$this->data->saveRow($row,$params[0]);
	}
	
	public function getRows($num_page, $rows)
	{
		$p4a = P4A::singleton();

		$aReturn = array();
		$aCols = $this->getVisibleCols();
		$enabled = $this->isEnabled();

		if ($this->isActionTriggered('beforedisplay')) {
			$rows = $this->actionHandler('beforedisplay', $rows);
		}

		$pk = $this->data->getPK();
		
		$i = 0;
		$j = 0;
		$z = 0;
		
		$obj_id = $this->getID();
		
		foreach ($rows as $row) {
			
			$pk_value = $row[$pk];
			$pk_value_64 = base64_encode($pk_value);
			
			foreach($aCols as $col_name) {
				if ($this->cols->$col_name->isEnabled() and !$this->data->fields->$col_name->isReadOnly()) {
					$col_enabled = TRUE;
					$cell_id = $obj_id . '_' . $pk_value_64 . '_' . $z;
					$z++;
				} else {
					$cell_id = "";
					$col_enabled = FALSE;
				}
				
				$aReturn[$i]['cells'][$j]['class'] = ($enabled and $col_enabled) ? 'p4a_grid_td p4a_grid_td_enabled': 'p4a_grid_td p4a_grid_td_disabled';
				$aReturn[$i]['cells'][$j]['clickable'] = ($enabled and $col_enabled) ? 'clickable' : '';
				$aReturn[$i]['cells'][$j]['id'] = $cell_id;
				$aReturn[$i]['cells'][$j]['title'] =  $col_name;				
				
				if ($this->cols->$col_name->isFormatted()) {
					if ($this->cols->$col_name->isActionTriggered('onformat')) {
						$aReturn[$i]['cells'][$j]['value'] = $this->cols->$col_name->actionHandler('onformat', $row[$col_name], $this->data->fields->$col_name->getType(), $this->data->fields->$col_name->getNumOfDecimals());
					} else {
						$aReturn[$i]['cells'][$j]['value'] = $p4a->i18n->format($row[$col_name], $this->data->fields->$col_name->getType(), $this->data->fields->$col_name->getNumOfDecimals(), false);
					}
				} else {
					$aReturn[$i]['cells'][$j]['value'] = $row[$col_name];
				}
				
				$aReturn[$i]['cells'][$j]['type'] = $this->data->fields->$col_name->getType();
				$j++;
			}
			$i++;
		}
		return $aReturn;
	}	
}