<?php
/**
 * P4A - PHP For Applications.
 *
 * The code within this file is public domain.
 *
 * @author Fabrizio Balliano <fabrizio@fabrizioballiano.it>
 * @author Andrea Giardina <andrea.giardina@crealabs.it>
 * @package p4a_helpers
 */

function P4A_Frame_anchorTabPane($obj, $params)
{
	$tab_pane_name = $params[0];
	$tab_pane = $obj->build('p4a_tab_pane', $tab_pane_name);

	for ($i=1; $i<count($params); $i++) {
		$tab_pane->addPage($params[$i]);
	}

	$obj->anchor($tab_pane);
	return $tab_pane;
}