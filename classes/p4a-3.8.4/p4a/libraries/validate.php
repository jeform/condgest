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
 * @package p4a_validate
 */

require_once 'Zend/Validate.php';
require_once 'Zend/Validate/Alnum.php';
require_once 'Zend/Validate/Alpha.php';
require_once 'Zend/Validate/Between.php';
require_once 'Zend/Validate/Ccnum.php';
require_once 'Zend/Validate/Date.php';
require_once 'Zend/Validate/Digits.php';
require_once 'Zend/Validate/EmailAddress.php';
require_once 'Zend/Validate/Float.php';
require_once 'Zend/Validate/GreaterThan.php';
require_once 'Zend/Validate/Hex.php';
require_once 'Zend/Validate/Hostname.php';
require_once 'Zend/Validate/InArray.php';
require_once 'Zend/Validate/Int.php';
require_once 'Zend/Validate/Ip.php';
require_once 'Zend/Validate/LessThan.php';
require_once 'Zend/Validate/NotEmpty.php';
require_once 'Zend/Validate/Regex.php';
require_once 'Zend/Validate/StringLength.php';

/**
 * @author Fabrizio Balliano <fabrizio@fabrizioballiano.it>
 * @author Andrea Giardina <andrea.giardina@crealabs.it>
 * @copyright Copyright (c) 2003-2010 Fabrizio Balliano, Andrea Giardina
 */
class P4A_Validate extends Zend_Validate
{
	/**
	 * @param string $validator_class
	 */
	public function removeValidator($validator_class)
	{
		foreach ($this->_validators as $k=>$validator) {
			if ($validator['instance'] instanceof $validator_class) {
				unset($this->_validators[$k]);
				return;
			}
		}
	}
}

/**
 * @author Fabrizio Balliano <fabrizio@fabrizioballiano.it>
 * @author Andrea Giardina <andrea.giardina@crealabs.it>
 * @copyright Copyright (c) 2003-2010 Fabrizio Balliano, Andrea Giardina
 */
class P4A_Validate_Alnum extends Zend_Validate_Alnum
{
}

/**
 * @author Fabrizio Balliano <fabrizio@fabrizioballiano.it>
 * @author Andrea Giardina <andrea.giardina@crealabs.it>
 * @copyright Copyright (c) 2003-2010 Fabrizio Balliano, Andrea Giardina
 */
class P4A_Validate_Alpha extends Zend_Validate_Alpha
{
}

/**
 * @author Fabrizio Balliano <fabrizio@fabrizioballiano.it>
 * @author Andrea Giardina <andrea.giardina@crealabs.it>
 * @copyright Copyright (c) 2003-2010 Fabrizio Balliano, Andrea Giardina
 */
class P4A_Validate_Between extends Zend_Validate_Between
{
}

/**
 * @author Fabrizio Balliano <fabrizio@fabrizioballiano.it>
 * @author Andrea Giardina <andrea.giardina@crealabs.it>
 * @copyright Copyright (c) 2003-2010 Fabrizio Balliano, Andrea Giardina
 */
class P4A_Validate_Ccnum extends Zend_Validate_Ccnum
{
}

/**
 * @author Fabrizio Balliano <fabrizio@fabrizioballiano.it>
 * @author Andrea Giardina <andrea.giardina@crealabs.it>
 * @copyright Copyright (c) 2003-2010 Fabrizio Balliano, Andrea Giardina
 */
class P4A_Validate_Date extends Zend_Validate_Date
{
}

/**
 * @author Fabrizio Balliano <fabrizio@fabrizioballiano.it>
 * @author Andrea Giardina <andrea.giardina@crealabs.it>
 * @copyright Copyright (c) 2003-2010 Fabrizio Balliano, Andrea Giardina
 */
class P4A_Validate_Digits extends Zend_Validate_Digits
{
}

/**
 * @author Fabrizio Balliano <fabrizio@fabrizioballiano.it>
 * @author Andrea Giardina <andrea.giardina@crealabs.it>
 * @copyright Copyright (c) 2003-2010 Fabrizio Balliano, Andrea Giardina
 */
class P4A_Validate_EmailAddress extends Zend_Validate_EmailAddress
{
}

/**
 * @author Fabrizio Balliano <fabrizio@fabrizioballiano.it>
 * @author Andrea Giardina <andrea.giardina@crealabs.it>
 * @copyright Copyright (c) 2003-2010 Fabrizio Balliano, Andrea Giardina
 */
class P4A_Validate_Float extends Zend_Validate_Float
{
}

/**
 * @author Fabrizio Balliano <fabrizio@fabrizioballiano.it>
 * @author Andrea Giardina <andrea.giardina@crealabs.it>
 * @copyright Copyright (c) 2003-2010 Fabrizio Balliano, Andrea Giardina
 */
class P4A_Validate_GreaterThan extends Zend_Validate_GreaterThan
{
}

/**
 * @author Fabrizio Balliano <fabrizio@fabrizioballiano.it>
 * @author Andrea Giardina <andrea.giardina@crealabs.it>
 * @copyright Copyright (c) 2003-2010 Fabrizio Balliano, Andrea Giardina
 */
class P4A_Validate_Hex extends Zend_Validate_Hex
{
}

/**
 * @author Fabrizio Balliano <fabrizio@fabrizioballiano.it>
 * @author Andrea Giardina <andrea.giardina@crealabs.it>
 * @copyright Copyright (c) 2003-2010 Fabrizio Balliano, Andrea Giardina
 */
class P4A_Validate_Hostname extends Zend_Validate_Hostname
{
}

/**
 * @author Fabrizio Balliano <fabrizio@fabrizioballiano.it>
 * @author Andrea Giardina <andrea.giardina@crealabs.it>
 * @copyright Copyright (c) 2003-2010 Fabrizio Balliano, Andrea Giardina
 */
class P4A_Validate_InArray extends Zend_Validate_InArray
{
}

/**
 * @author Fabrizio Balliano <fabrizio@fabrizioballiano.it>
 * @author Andrea Giardina <andrea.giardina@crealabs.it>
 * @copyright Copyright (c) 2003-2010 Fabrizio Balliano, Andrea Giardina
 */
class P4A_Validate_Int extends Zend_Validate_Int
{
}

/**
 * @author Fabrizio Balliano <fabrizio@fabrizioballiano.it>
 * @author Andrea Giardina <andrea.giardina@crealabs.it>
 * @copyright Copyright (c) 2003-2010 Fabrizio Balliano, Andrea Giardina
 */
class P4A_Validate_Ip extends Zend_Validate_Ip
{
}

/**
 * @author Fabrizio Balliano <fabrizio@fabrizioballiano.it>
 * @author Andrea Giardina <andrea.giardina@crealabs.it>
 * @copyright Copyright (c) 2003-2010 Fabrizio Balliano, Andrea Giardina
 */
class P4A_Validate_LessThan extends Zend_Validate_LessThan
{
}

/**
 * @author Fabrizio Balliano <fabrizio@fabrizioballiano.it>
 * @author Andrea Giardina <andrea.giardina@crealabs.it>
 * @copyright Copyright (c) 2003-2010 Fabrizio Balliano, Andrea Giardina
 */
class P4A_Validate_NotEmpty extends Zend_Validate_NotEmpty
{
	public function isValid($value)
	{
		$valueString = (string) $value;
		$this->_setValue($valueString);

		if (strlen($valueString) == 0) {
			$this->_error(null);
			return false;
		}

		return true;
	}
}

/**
 * @author Fabrizio Balliano <fabrizio@fabrizioballiano.it>
 * @author Andrea Giardina <andrea.giardina@crealabs.it>
 * @copyright Copyright (c) 2003-2010 Fabrizio Balliano, Andrea Giardina
 */
class P4A_Validate_Regex extends Zend_Validate_Regex
{
}

/**
 * @author Fabrizio Balliano <fabrizio@fabrizioballiano.it>
 * @author Andrea Giardina <andrea.giardina@crealabs.it>
 * @copyright Copyright (c) 2003-2010 Fabrizio Balliano, Andrea Giardina
 */
class P4A_Validate_StringLength extends Zend_Validate_StringLength
{
}