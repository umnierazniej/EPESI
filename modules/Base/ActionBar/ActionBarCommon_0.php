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

class Base_ActionBarCommon extends ModuleCommon {
	const FIRST = 0;
	const LAST = 1;
	private static $icons = array();
    public static $quick_access_shortcuts = false;

	/**
	 * @param string $icon Font awesome icon name (without fa- prefix)
	 * @param string $label Label for action
	 * @param string $action Action href
	 * @param string $description Description will be showed in tooltip
	 * @param int $position Position of button ({@see Base_ActionBarCommon::FIRST}, {@see Base_ActionBarCommon::LAST})
	 * @throws Exception
     */
	public static function add($icon, $label, $action, $description=null, $position = self::LAST) {
//		if(!array_key_exists($type,self::$available_icons)) trigger_error('Invalid action '.$type,E_USER_ERROR);
		foreach (self::$icons as $k=>$v) { //QUESTION: Po co to robimy?
			if ($v['icon']==$icon && $v['label']==$label) unset(self::$icons[$k]);
		}

		//todo: Throw exception if icon does not exist
		$icon_arr = array('icon'=>$icon,'label'=>$label,'action'=>$action,'description'=>$description,'position'=>$position);
		switch($position) {
			case self::FIRST:
				array_push(self::$icons, $icon_arr);
				break;
			case self::LAST:
				array_unshift(self::$icons, $icon_arr);
				break;
			default:
				throw new Exception('Invalid icon position');
		}
	}

	public static function get() {
		return self::$icons;
	}

	public static function clean() {
		self::$icons = array();
	}
    
    public static function show_quick_access_shortcuts($value = true) {
        self::$quick_access_shortcuts = $value;
    }
}
on_exit(array('Base_ActionBarCommon','clean'));
?>
