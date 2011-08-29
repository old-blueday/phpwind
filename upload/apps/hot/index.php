<?php
!defined('A_P') && exit('Forbidden');
!$winduid && Showmsg('not_login');

$USCR = 'user_hot';
require_once(R_P . 'u/lib/space.class.php');
$newSpace = new PwSpace($winduid);
$space =& $newSpace->getInfo();

$basename = 'apps.php?q=' . $q . '&';

//* include_once pwCache::getPath(D_P . 'data/bbscache/o_config.php');
pwCache::getData(D_P . 'data/bbscache/o_config.php');
include_once (R_P . 'require/showimg.php');
include_once (R_P . 'require/credit.php');
L::loadClass('datanalyse', 'datanalyse', false);
include_once (A_P . 'hot/lang/lang_o_hot.php');
include_once (A_P . 'hot/lib/index.class.php');
if (!trim($o_hot_groups, ',') || strpos($o_hot_groups, ',' . $winddb[groupid] . ',') !== false || ($winddb['groupid'] == -1 && strpos($o_hot_groups, ',' . $winddb['memberid'] . ',') !== false)) {
	$popedom = true;
} else {
	$popedom = false;
}
!$o_hot_open && Showmsg('hot_close');
!$popedom && Showmsg('hot_popedom');

$datanalyse = new Datanalyse();
$hotDB = new HotDB($datanalyse);
S::gp(array(
	'action', 
	'sub', 
	'fTime', 
	'fType'
), 'GP');
$tabs = $hotDB->getTabs();
if (empty($action) || $action == "center") {
	$datacache = L::loadClass('datanalysecache', 'datanalyse');
	$disModules = $hotDB->getAllDisplayModules();
	$result = array();
	if ($datacache->ifUpdateCache()) {
		$parents = (array) $disModules['parent'];
		$num = 1;
		foreach ($parents as $key => $value) {
			$rTop = array(
				'tag' => $value['tag'], 
				'title' => $value['type_name']
			);
			if ($disModules[$value['id']]) {
				foreach ($disModules[$value['id']] as $k => $v) {
					$fTime = $fType = '';
					if ($value['tag'] == 'memberHot') {
						$datanalyse->setSpecialLimit(5);
					} else {
						$datanalyse->setSpecialLimit(6);
					}
					$rSub = array(
						'tag' => $v['tag'], 
						'title' => $v['type_name']
					);
					$data = $hotDB->getData($value['tag'], $v, $fTime, $fType);
					$value['tag'] == 'memberHot' && $num == 1 && $data['data'] && $num++;
					$rSub['data'] = $data['data'];
					$rSub['unit'] = $data['unit'];
					$rSub['cTime'] = $data['currentTime'];
					$rSub['cType'] = $data['currentType'];
					$rSub['action'] = $fType;
					$data['data'] && $rTop['sub'][] = $rSub;
				}
			}
			$result[] = $rTop;
		}
		$datacache->writeCache($result);
	} else {
		$result = $datacache->getResult();
	}
} else {
	$subMenu = $hotDB->getActiveModules($action);
	$result = array();
	if (!empty($subMenu)) {
		$keys = array_keys($subMenu);
		!$sub && $sub = $keys[0];
		$result = $hotDB->getData($action, $subMenu[$sub], $fTime, $fType);
	}
}
require_once (R_P . 'require/showimg.php');

list($isheader, $isfooter, $tplname, $isleft) = array(
	true, 
	true, 
	"m_hot", 
	true
);
require_once PrintEot('m_hot');
pwOutPut();
?>