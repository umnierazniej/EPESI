<?php
/**
 * Index file
 *
 * This file includes all 'include files', loads modules
 * and gets output of default module.
 * @author Paul Bukowski <pbukowski@telaxus.com>
 * @copyright Copyright &copy; 2006, Telaxus LLC
 * @license MIT
 * @version 1.0
 * @package epesi-base
 */

use Symfony\Component\HttpFoundation\Response;

define('_VALID_ACCESS', 1);


if (version_compare(phpversion(), '5.0.0') == -1)
    die("You are running an old version of PHP, php5 required.");

if (trim(ini_get("safe_mode")))
    die('You cannot use EPESI with PHP safe mode turned on - please disable it. Please notice this feature is deprecated since PHP 5.3 and will be removed in PHP 6.0.');

require_once('include/data_dir.php');
if (!is_writable(DATA_DIR))
    die('Cannot write into "' . DATA_DIR . '" directory. Please fix privileges.');

include('vendor/autoload.php');
$response = new Response();



if (!file_exists(DATA_DIR . '/config.php')) {
    $response->headers->add(array('Location' => 'setup.php'));
    $response->send();
    exit();
}



// require_once('include/include_path.php');
require_once('include/config.php');
require_once('include/maintenance_mode.php');
require_once('include/error.php');
require_once('include/database.php');
require_once('include/variables.php');

if (epesi_requires_update()) {
    $response->headers->add(array('Location' => 'update.php'));
    $response->send();
    exit();
}


$tables = DB::MetaTables();
if (!in_array('modules', $tables) || !in_array('variables', $tables) || !in_array('session', $tables))
    die('Database structure you are using is apparently out of date or damaged. If you didn\'t perform application update recently you should try to restore the database. Otherwise, please refer to EPESI documentation in order to perform database update.');

require_once('include/misc.php');


$options = array();
$options['epesi'] = EPESI;

ini_set('include_path', 'libs/minify' . PATH_SEPARATOR . '.' . PATH_SEPARATOR . 'libs' . PATH_SEPARATOR . ini_get('include_path'));


require_once('Minify/Build.php');

$jquery = DEBUG_JS ? 'libs/jquery-1.11.3.js' : 'libs/jquery-1.11.3.min.js';
$jquery_migrate = DEBUG_JS ? 'libs/jquery-migrate-1.2.1.js' : 'libs/jquery-migrate-1.2.1.min.js';
$bootstrap = DEBUG_JS ? 'libs/bootstrap/js/bootstrap.js' : 'libs/bootstrap/js/bootstrap.min.js';
$perfect_scrollbar = DEBUG_JS ? 'libs/perfect-scrollbar/js/perfect-scrollbar.jquery.js' : 'libs/perfect-scrollbar/js/min/perfect-scrollbar.jquery.min.js';
$jses = array('libs/prototype.js', $jquery, $jquery_migrate, 'libs/lodash.js', $bootstrap, 'libs/jquery-ui-1.10.1.custom.min.js', 'libs/HistoryKeeper.js', 'include/epesi.js', $perfect_scrollbar);

if (!DEBUG_JS) {
    $jsses_build = new Minify_Build($jses);
    $options['jsses_src'] = $jsses_build->uri('serve.php?' . http_build_query(array('f' => array_values($jses))));
} else {
    $options['jsses_src'] = $jses;
}

$csses = array('libs/jquery-ui-1.10.1.custom.min.css','libs/bootstrap/css/bootstrap.css','libs/font-awesome/css/font-awesome.css','libs/perfect-scrollbar/css/perfect-scrollbar.css');
$options['csses_src'] = $csses;


$options['rtl'] = DIRECTION_RTL;
$options['tracking_code'] = TRACKING_CODE;
$options['starting_message'] = STARTING_MESSAGE;


/*
 * init_js file allows only num_of_clients sessions. If there is image
 * with empty src="" browser will load index.php file, so we cannot
 * include init_js file directly because num_of_clients request will
 * reset our history and restart EPESI.
 *
 * Check here if request accepts html. If it does we can assume that
 * this is request for page and include init_js file which is faster.
 * If there is not 'html' in accept use script with src property.
 */
$options['init_js'] = array();
if (isset($_SERVER['HTTP_ACCEPT']) && stripos($_SERVER['HTTP_ACCEPT'], 'html') !== false) {
    $options['init_js']['include'] = true;
    ob_start();
    require_once 'init_js.php';
    $options['init_js']['content'] = ob_get_contents();
    ob_end_clean();
} else {
    $options['init_js']['include'] = false;
    $options['init_js']['query'] = http_build_query($_GET);
}


include('include/module_manager.php');
$twig = ModuleManager::get_container()['twig'];

/** @var Twig_Environment $twig */
$content = $twig->render('main.twig', $options);

$response->setContent($content);
$response->send();