<?php
/**
 * ActionBar
 * 
 * This class provides action bar component.
 * 
 * @author Paul Bukowski <pbukowski@telaxus.com>
 * @copyright Copyright &copy; 2008, Telaxus LLC
 * @license MIT
 * @version 1.0
 * @package epesi-base
 * @subpackage actionbar
 */
defined("_VALID_ACCESS") || die('Direct access forbidden');

class Base_ActionBar extends Module {

	/**
	 * Displays action bar.
	 */
	public function body() {
		//todo-pj: co tu mamy z tym helpem?
		$this->help('ActionBar basics','main');
		$this->display('default.twig', array('icons' => Base_ActionBarCommon::get()));
	}
}

?>
