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
 * The label is associated to an input field, do not use it otherwise
 * @author Fabrizio Balliano <fabrizio@fabrizioballiano.it>
 * @author Andrea Giardina <andrea.giardina@crealabs.it>
 * @copyright Copyright (c) 2003-2010 Fabrizio Balliano, Andrea Giardina
 * @package p4a
 */
class P4A_Label extends P4A_Widget
{
	/**
	 * @param string $name Object identifier
	 * @param string $value
	 */
	public function __construct($name, $value = null)
	{
		parent::__construct($name);
		$this->setLabel($value);
	}

	/**
	 * Returns the HTML rendered label.
	 * This is done by building a SPAN, because with a SPAN you
	 * can trigger events such as onClick ect.
	 * Label is rendered only if the widget is visible.
	 */
	public function getAsString()
	{
		$id = $this->getId();
		if (!$this->isVisible()) {
			return "<span id='$id' class='hidden'></span>";
		}
		
		$css_classes = $this->getCSSClasses();
		$actions = $this->composeStringActions();

		$tooltip_text = __($this->getTooltip());
		$tooltip_icon = '';
		if ($tooltip_text) {
			$tooltip_icon = '<img src="' . P4A_ICONS_PATH . '/16/status/dialog-information.png" class="p4a_tooltip_icon" alt="" />';
			$tooltip_text = "<div id='{$id}tooltip' class='p4a_tooltip'><div class='p4a_tooltip_inner'>{$tooltip_text}</div></div>";
			$actions .= " onmouseover='p4a_tooltip_show(this)' ";
			$css_classes[] = 'p4a_label_tooltip';
		}

		$css_classes = join(' ', $css_classes);
		return "<label id='{$id}' class='$css_classes' " . $this->composeStringProperties() . 
				"$actions>$tooltip_icon<span>" . __($this->getLabel()) . "</span>$tooltip_text</label>\n";
	}
}