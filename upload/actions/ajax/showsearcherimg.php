<?php
!defined('P_W') && exit('Forbidden');
//http://localhost/phpwind/trunk/pw_ajax.php?action=showsearcherimg&tid=10980&imgid=13&see=next
S::gp(array(
	'tid','imgid','see'
));

$tid = intval($tid);
$imgid = intval($imgid);
//PostCheck();

if (!$tid || !$imgid) Showmsg('您请求的页面出错啦!');

$wheresql = "AND type = 'img' AND pid=0 AND special=0";
$i = 1;
$qurey = $db->query(" SELECT * FROM pw_attachs WHERE tid =".S::sqlEscape($tid)." $wheresql ORDER BY aid ASC");
while ($rt = $db->fetch_array($qurey)) {
	$imgIds[$rt['aid']] = $i;
	$i++;
}
$imgTtotal = count($imgIds);

if (!$imgTtotal) Showmsg('您请求的页面出错啦!');
if(!S::inArray($imgid, array_keys($imgIds))) Showmsg('您请求的页面出错啦!');

$imgDb = array();
if ($imgTtotal < 2) {
	$tempImgDb = getImgByIds($tid, $imgid);
	$imgDb = buildAtt($tempImgDb);
	$numberDb = array('number' =>1,'total'=>1);
	$imgDb = pwJsonEncode(array_merge($numberDb, $imgDb));
	echo "success\t{$imgDb}";
	ajax_footer();
}


switch ($see) {
	case 'next' : 	
	
		if ($imgIds[$imgid] == $imgTtotal) {
			foreach ($imgIds as $key=> $value) {
				if ($value != 1) continue;
				$imgid = $key - 1;
			}
		}
	
		$tempImgDb = getNextImg($tid, $imgid);
		break;
	case 'prev' : 
		
		if ($imgIds[$imgid] == 1) {
			foreach ($imgIds as $key=> $value) {
				if ($value != $imgTtotal) continue;
				$imgid = $key + 1;
			}
			
		}
		
		$tempImgDb = getPrevImg($tid, $imgid);
		break;
	default:
		$tempImgDb = getImgByIds($tid, $imgid);
		break;
}

if (!$tempImgDb) {
	Showmsg('您请求的页面出错啦!');
}

$imgDb = buildAtt($tempImgDb);
$numberDb = array('number' =>$imgIds[$imgDb['imgid']],'total'=>$imgTtotal);
$imgDb = pwJsonEncode(array_merge($numberDb, $imgDb));
echo "success\t{$imgDb}";
ajax_footer();


function getNextImg($tid, $imgid) {
	global $db;
	$tid = intval($tid);
	$imgid = intval($imgid);
	if (!$tid || !$imgid) return false;
	$_wheresql = " AND type = 'img' AND pid=0 AND special=0 ";
	return $db->get_one(" SELECT * FROM pw_attachs WHERE tid =".S::sqlEscape($tid)." AND aid > ".S::sqlEscape($imgid). " $_wheresql ORDER BY aid ASC");
}

function getPrevImg($tid, $imgid) {
	global $db;
	$tid = intval($tid);
	$imgid = intval($imgid);
	if (!$tid || !$imgid) return false;
	$_wheresql = " AND type = 'img' AND pid=0 AND special=0 ";
	return $db->get_one(" SELECT * FROM pw_attachs WHERE tid =".S::sqlEscape($tid)." AND aid < ".S::sqlEscape($imgid). " $_wheresql ORDER BY aid DESC");
}

function getImgByIds($tid, $imgid) {
	global $db;
	$tid = intval($tid);
	$imgid = intval($imgid);
	if (!$tid || !$imgid) return false;
	$_wheresql = " AND type = 'img' AND pid=0 AND special=0 ";
	return $db->get_one(" SELECT * FROM pw_attachs WHERE tid =".S::sqlEscape($tid)." AND aid = ".S::sqlEscape($imgid). " $_wheresql");
}


function buildAtt($imgDb) {
	if (!$imgDb) return array();
	$attachurl = $imgDb['attachurl'];
	$ifthumb = $imgDb['ifthumb'];
	$a_url = geturl($attachurl, 'show');
	$result = array(
			'imgid'	=> $imgDb['aid'],
			'thumb' => _getAttachMiniUrl($attachurl, $ifthumb, $a_url[1]),
			'view' => _getAttachMiniUrl($attachurl, 0, $a_url[1])
	);
	return $result;
}

function _getAttachMiniUrl($path, $ifthumb, $where) {
	$dir = '';
	($ifthumb & 1) && $dir = 'thumb/';
	($ifthumb & 2) && $dir = 'thumb/mini/';
	if ($where == 'Local') return $GLOBALS['attachpath'] . '/' . $dir . $path;
	if ($where == 'Ftp') return $GLOBALS['db_ftpweb'] . '/' . $dir . $path;
	if (!is_array($GLOBALS['attach_url'])) return $GLOBALS['attach_url'] . '/' . $dir . $path;
	return $GLOBALS['attach_url'][0] . '/' . $dir . $path;
}
