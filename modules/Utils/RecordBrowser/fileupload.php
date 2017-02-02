<?php
/**
 * Created by PhpStorm.
 * User: norbert
 * Date: 13.09.16
 * Time: 11:59
 */
if(!isset($_REQUEST['cid']) && !isset($_REQUEST['action']))
    die('Invalid usage');

$cid = $_REQUEST['cid'];

define('CID', $cid);
define('READ_ONLY_SESSION',false);

require_once('../../../include.php');
ModuleManager::load_modules();

if(!Acl::is_user())
    die('Permission denied');
switch($_REQUEST['action']) {
    case 'add' :
        $_FILES['field'] = $_REQUEST['field'];
        $_SESSION['client']['recordbrowser_file'][CID]['files'][] = $_FILES;
        foreach ($_FILES['file']['tmp_name'] as $key => $file) {
            if (is_uploaded_file($file)) {
                $file_content = file_get_contents($file);
                file_put_contents(DATA_DIR . '/Utils_RecordBrowser/' . $_FILES['file']['name'][$key], $file_content);
            }
        }
        break;
    case 'delete' :
        foreach($_SESSION['client']['premium_laycom'][CID]['files'] as $files_key=>$files) {
            $found = false;
            foreach ($files['file']['name'] as $file_key => $filename) {
                if ($_REQUEST['filename'] == $filename) {
                    unset($_SESSION['client']['recordbrowser_file'][CID]['files'][$files_key]['file']['name'][$file_key]);
                    unset($_SESSION['client']['recordbrowser_file'][CID]['files'][$files_key]['file']['type'][$file_key]);
                    unset($_SESSION['client']['recordbrowser_file'][CID]['files'][$files_key]['file']['tmp_name'][$file_key]);
                    unset($_SESSION['client']['recordbrowser_file'][CID]['files'][$files_key]['file']['error'][$file_key]);
                    unset($_SESSION['client']['recordbrowser_file'][CID]['files'][$files_key]['file']['size'][$file_key]);
                    $found = true;
                }
            }
            if($found){
                break;
            }
            if(empty($_SESSION['client']['recordbrowser_file'][CID]['files'][$files_key]['file']['name'])){
                unset($_SESSION['client']['recordbrowser_file'][CID]['files'][$files_key]);
            }
        }
        if(empty($_SESSION['client']['recordbrowser_file'][CID]['files'])){
            unset($_SESSION['client']['recordbrowser_file']);
        }
        break;
}