<?php
!defined('P_W') && exit('Forbidden');

S::gp(array('job'));

$photoEditor = ($db_phopen && $winduid);
if (!$photoEditor && $job) {
	Showmsg('undefined_action');
}
//* include_once pwCache::getPath(D_P . 'data/bbscache/o_config.php');
pwCache::getData(D_P . 'data/bbscache/o_config.php');

require_once(R_P . 'u/require/core.php');
L::loadClass('photo', 'colony', false);
$photoService = new PW_Photo($winduid, 0, 1, 0);
$photoService->setPerpage($photoService->getAlbumNumByUid());

if ($job == 'listphotos') {
	/* ajax 请求获取相片列表 */
	define('AJAX', 1);
	S::GP(array('aid'));
	$ajaxPhotos = array();
	$result = $photoService->getPhotoListByAid($aid,false,false);
	list(,$photos) = $result;//$albumInfo,$photos
	if (S::isArray($photos)) {
		foreach ($photos as $photo) {
			$lastpos = strrpos($photo['path'],'/');
			$ajaxPhotos[] = array(
				'pid' => $photo['pid'],
				'thumbpath' => $photo['path'],
				'ifthumb' => $photo['ifthumb'],
				//'path' => dirname($photo['path']).'/'.substr($photo['path'],$lastpos+3),
				'path' => $photo['path'],
				'pintro' => $photo['pintro']
			);
		}
		$ajaxPhotos = pwJsonEncode($ajaxPhotos);
		echo "success\t{$ajaxPhotos}";
	} else {
		Showmsg('data_error');
	}
	ajax_footer();
	exit;
} elseif ($job == 'upload') {
	define('AJAX', 1);
	S::gp(array('aid'));
	$result = $photoService->uploadPhoto($aid);
	if (!is_array($result)) {
		Showmsg($result);
	}
	list($albumInfo,$pid,$photoNum,$photos) = $result;
	
	$photoNum > 0 or Showmsg('data_error');
	$photo = getphotourl($photos[0]['path'],0);
	echo "success\t{$photo}";
	ajax_footer();
}
if ($db_question && $o_photos_qcheck) {
	$qkey = array_rand($db_question);
}
list(,$showq) = explode("\t", $db_qcheck);

$networkTab = $albumTab = 'none';
if ($photoEditor) {
	$result = $photoService->getAlbumBrowseList();
	list(,$albums) = $result;//album Count,albums

	if (S::isArray($albums)) {
		$aid = intval($aid);
		$aid = $aid? $aid : $albums[0]['aid'];
	}
	$photos = array();
	$aid > 0 && list(,$photos) = $photoService->getPhotoListByAid($aid,false);
	$albumTab = '';
} else {
	$networkTab = '';
}
require_once PrintEot('wysiwyg_editor_photos');
pwOutPut();exit;