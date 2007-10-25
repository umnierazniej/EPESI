<?php
/**
 * @author Paul Bukowski <pbukowski@telaxus.com>
 * @copyright Copyright &copy; 2007, Telaxus LLC
 * @version 1.0
 * @license SPL
 * @package epesi-tests
 * @subpackage lightbox
 */
defined("_VALID_ACCESS") || die('Direct access forbidden');

class Tests_Leightbox extends Module{
	public function body(){
		print(Utils_CalendarCommon::show('alert(__DAY__+\'.\'+__MONTH__+\'.\'+__YEAR__ )'));
		
		print('<hr><a rel="leightbox1" class="lbOn">leightbox container</a>
		<div id="leightbox1" class="leightbox">
			<h1>Leightbox</h1>
			ble ble ble
			<a href="#" class="lbAction" rel="deactivate">Close</a>
			</div><hr>');
		
		$f = $this->init_module('Libs/QuickForm');
		$f->addElement('datepicker','xxx','Date picker');
		$x = $f->addElement('commondata','xxx2','Commondata test', 'Countries',2);
		$f->addElement('submit',null,'ok');
		if($f->validate()) {
			print_r($f->exportValues());
		}
		$f->display();

		//------------------------------ print out src
		print('<hr><b>Install</b><br>');
		$this->pack_module('Utils/CatFile','modules/Tests/Leightbox/LeightboxInstall.php');
		print('<hr><b>Main</b><br>');
		$this->pack_module('Utils/CatFile','modules/Tests/Leightbox/Leightbox_0.php');
		print('<hr><b>Common</b><br>');
		$this->pack_module('Utils/CatFile','modules/Tests/Leightbox/LeightboxCommon_0.php');
		
	}
}

?>
