<?php
/**
 * Use this module if you want to add attachments to some page.
 * @author Paul Bukowski <pbukowski@telaxus.com>
 * @copyright Copyright &copy; 2008, Telaxus LLC
 * @license MIT
 * @version 1.0
 * @package epesi-utils
 * @subpackage attachment
 */


if(!isset($_REQUEST['cid']) || !isset($_REQUEST['id']))
	die('Invalid usage');
$cid = $_REQUEST['cid'];
$id = intval($_REQUEST['id']);
$disposition = (isset($_REQUEST['view']) && $_REQUEST['view'])?'inline':'attachment';

define('CID', $cid);
define('READ_ONLY_SESSION',true);
require_once('../../../include.php');
ModuleManager::load_modules();

if(!Acl::is_user())
	die('Permission denied');
$file = DB::GetRow('SELECT uff.filename, rbf.filestorage_id 
					FROM recordbrowser_files rbf
					INNER JOIN utils_filestorage_files uff ON rbf.filestorage_id=uff.id
					WHERE rbf.id=%d',array($id));

$original = $file['filename'];
$fsid = $file['filestorage_id'];

$meta = Utils_FileStorageCommon::meta($fsid);

require_once('mime.php');

if(headers_sent())
	die('Some data has already been output to browser, can\'t send file');

$password = '';

if (isset($_REQUEST['thumbnail'])) {
    $o_filename = $meta['file'];
    $f_filename = $o_filename.'_thumbnail';
    if(!file_exists($f_filename)) {
	    if(!file_exists($o_filename))
    	    die('File doesn\'t exists');
    	$image_info = getimagesize($o_filename);
    	$image_type = $image_info[2];
    	$image = false;
    	switch ($image_type) {
	        case IMAGETYPE_JPEG: $image = imagecreatefromjpeg($o_filename); break;
	        case IMAGETYPE_GIF: $image = imagecreatefromgif($o_filename); break;
	        case IMAGETYPE_PNG: $image = imagecreatefrompng($o_filename); break;
	        default: $buffer = file_get_contents($o_filename);
    	}
    	if ($image) {
    	    $img_w = imagesx($image);
    	    $img_h = imagesy($image);
    	    $max = 300;
    	    $w = ($img_w>=$img_h)?$max:floor($max*$img_w/$img_h);
    	    $h = ($img_h>=$img_w)?$max:floor($max*$img_h/$img_w);
    	    $new_image = imagecreatetruecolor($w, $h);
    	    imagecopyresampled($new_image, $image, 0, 0, 0, 0, $w, $h, $img_w, $img_h);
    	    switch ($image_type) {
	            case IMAGETYPE_JPEG: imagejpeg($new_image,$f_filename,95); break;
		        case IMAGETYPE_GIF: imagegif($new_image,$f_filename); break;
		        case IMAGETYPE_PNG: imagepng($new_image,$f_filename); break;
    	    }
	        $buffer = file_get_contents($f_filename);
    	}
    } else {
	    $buffer = file_get_contents($f_filename);
    }
} else {
    $f_filename = $meta['file'];
    if(!file_exists($f_filename))
    	die('File doesn\'t exists');
    @ini_set('memory_limit',ceil(filesize($f_filename)*2/1024/1024+64).'M');
    $buffer = file_get_contents($f_filename);
}

$mime = get_mime_type($f_filename, $original);

$expires = 24*60*60;
header('Pragma: public');
header('Cache-Control: maxage='.(24*60*60));
header('Expires: ' . gmdate('D, d M Y H:i:s', time()+(24*60*60)) . ' GMT');
header('Content-Type: '.$mime);
header('Content-Length: '.strlen($buffer));
header('Content-disposition: '.$disposition.'; filename="'.$original.'"');
echo $buffer;

?>