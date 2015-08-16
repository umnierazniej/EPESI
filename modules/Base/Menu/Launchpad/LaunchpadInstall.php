<?php
/**
 */
defined("_VALID_ACCESS") || die('Direct access forbidden');

class Base_Menu_LaunchpadInstall extends ModuleInstall {
	public function install() {
		return true;
	}
	
	public function uninstall() {
		return true;
	}
	
	public function version() {
		return array('1.0.0');
	}
	public function requires($v) {
		return array(
			array('name'=>'Base/Lang','version'=>0),
			array('name'=>'Base/Menu','version'=>0),
			array('name'=>'Base/Theme','version'=>0),
			array('name'=>'Base/Acl','version'=>0)
		);
	}

	public static function simple_setup() {
		return __('EPESI Core');
	}
}

?>
