<?php
!defined('P_W') && exit('Forbidden');

header("Content-type: text/html; charset=$db_charset");
S::gp(array('job'));

$photoEditor = ($db_phopen && $winduid);
if (!$photoEditor && $job) {
	Showmsg('undefined_action');
}
extract(pwCache::getData(D_P . 'data/bbscache/o_config.php', false));

require_once(R_P . 'u/require/core.php');
L::loadClass('photo', 'colony', false);
$photoService = new PW_Photo($winduid, 0, 1, 0);

if ($job == 'listalbum') {

	if ($albums = getAlbumList($photoService)) {
		echo "success\t" . pwJsonEncode($albums);
	} else {
		echo 'error';
	}

} elseif ($job == 'listphotos') {

	S::gp(array('aid'));
	
	if ($photos = getPhotoList($photoService, $aid)) {
		echo "success\t" . pwJsonEncode($photos);
	} else {
		echo 'error';
	}
} else {

	$albums = getAlbumList($photoService);
	if ($albums) {
		$aid = $albums[0][0];
		$photos = getPhotoList($photoService, $aid);
		echo "success\t" . pwJsonEncode($albums) . "\t" . pwJsonEncode($photos);
	} else {
		echo 'error';
	}
}
exit;

function getAlbumList($sv) {
	$sv->setPerpage($sv->getAlbumNumByUid());
	$result = $sv->getAlbumBrowseList();
	list(,$albums) = $result;
	$array = array();
	if ($albums) {
		foreach ($albums as $key => $value) {
			$array[] = array($value['aid'], $value['aname']);
		}
	}
	return $array;
}

function getPhotoList($sv, $aid) {
	$array = array();
	$sv->setPerpage($sv->getAlbumNumByUid());
	$result = $sv->getPhotoListByAid($aid, false, false);
	list(, $photos) = $result;
	if (S::isArray($photos)) {
		foreach ($photos as $photo) {
			$array[] = array(
				'pid' => $photo['pid'],
				'thumbpath' => getphotourl($photo['defaultPath'], $photo['ifthumb']),
				'path' => $photo['path']
			);
		}
	}
	return $array;
}