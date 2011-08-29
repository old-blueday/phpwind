<?php
!function_exists('adminmsg') && exit('Forbidden');

L::loadClass('menuitem', 'site', false);
L::loadClass('menu', 'site', false);
L::loadClass('menuhead', 'site', false);
L::loadClass('menustart', 'site', false);
L::loadClass('menudiy', 'site', false);

require GetLang('left');
$headdb = array();
if (If_manager) {
	$nav_left['config']['items'][] = 'all';
	$nav_left = array_merge(array('initiator' => array('name' => $nav_manager['name'],'items' => $nav_manager['items'])),$nav_left);
}
unset($nav_manager);

isset($adskin) ? Cookie('adskin',$adskin) : $adskin = GetCookie('adskin');
/*
if (empty($adskin)) {
	$modeNav = array();
	$flag = false;
	foreach ($nav_left as $key => $value) {
		if ($flag) {
			$modeNav[$key] = $value;
			$nav_left[$key] = array();
			unset($nav_left[$key]);
		}
		$key == 'modemanage' && $flag = true;
	}
	$nav_left['modemanage']['items'] += $modeNav;
}
*/

$diyoptions = $db_diy ? explode(',',$db_diy) : array('setforum','setuser','level','postcache','article');
$newopration = getHotOpration();

$menu		= new MenuStart();
$diymenu	= new MenuDiy();
$hotmenu	= new MenuDiy();
creadMenu($nav_left,$menu);
$allmenu	= $menu->myStruct();
$allmenu	= "" == $allmenu ? "{}" : $allmenu;

$diyjsstr	= $diymenu->myStruct();
$hotjsstr	= $hotmenu->myStruct();

$headjsstr	= headSerialize();
$db_guideshow = ($db_guideshow === null ) ? 1 : (($db_guideshow == 1) ? 1 : 0);/*init*/
$ajaxurl = EncodeUrl($db_adminfile."?adminjob=ajaxhandler");

if ($adskin) {
	include PrintEot('windowindex');
} else {
	$mainList = $minorList = array();
	$flag = 0;
	foreach ($nav_left as $key => $value) {
		$key == 'modelist' && $flag = 1;
		if (!isset($headdb[$key])) continue;
		if ($flag) {
			$minorList[$key] = $value;
		} else {
			$mainList[$key] = $value;
		}
	}
	include PrintEot('indexlayout83');
}
exit();

function getMenuTree($item,$array){
	if (isset($array['items'])) {
		$temp	= getMenuTree($item,$array['items']);
		if ($temp !== false) {
			return $array['name'].'-'.$temp;
		}
	} else {
		foreach ($array as $key=>$value) {
			if (is_array($value)) {
				$temp	= getMenuTree($item,$value);
				if ($temp !== false) {
					return $temp;
				}
			} elseif ($value==$item) {
				global $purview;
				return $purview[$item][0];
			}
		}
	}
	return false;
}

function creadMenu($array,&$father){
	if (!is_array($array)) return;
	foreach ($array as $cate => $left) {
		if (isset(${$cate})) break;
		if (isNavHead($cate)) {
			global ${$cate};
			${$cate} = new MenuHead($cate,$left['name']);
			creadMenu($left['items'],${$cate});
			if (${$cate}->haveItems()) {
				addToHead(array('id'=>$cate,'name'=>$left['name']));
			}
		} elseif (isMenuLeaf($left)) {
			global ${$cate};
			${$cate} = new Menu($cate,$left['name']);
			creadMenu($left['items'],${$cate});
		} elseif (isMenuItem($left)) {
			global $purview;
			$cate = $left;
			if (adminRightCheck($cate)) {
				${$cate} = new MenuItem($cate,$purview[$cate][0],$purview[$cate][1]);
				diyAddCheck($cate,${$cate});
				hotAddCheck($cate,${$cate});
			}
		} else {
			creadMenu($left,$father);
		}
		if (isset(${$cate})) {
			$father->addChild(${$cate});
		}
	}
}
function diyAddCheck($key,$diyitem){
	global $diymenu,$diyoptions;
	if (in_array($key,$diyoptions)) {
		$diymenu->addChild($diyitem);
	}
}
function hotAddCheck($key,$diyitem){
	global $hotmenu,$newopration;
	if (in_array($key,$newopration)) {
		$hotmenu->addChild($diyitem);
	}
}
function addToHead($arr){
	global $headdb;
	!is_array($headdb) && $headdb = array();
	if (is_array($arr)) {
		$headdb[$arr['id']] = $arr;
	}
}

function headSerialize(){
	global $headdb;
	if (is_array($headdb) && count($headdb)>0) {
		$temp = array();
		foreach ($headdb as $value) {
			$temp[] = "{id:"."'".$value['id']."',name:'".$value['name']."'}";
		}
		$result = "[";
		$result .= implode(',',$temp);
		$result .= "]";
		return $result;
	} else {
		return '{}';
	}
}

function isMenuLeaf($array){
	return isset($array['items']) && is_array($array['items']);
}

function isNavHead($key){
	global $nav_head,$nav_left;
	return /*array_key_exists($key,$nav_head) && */array_key_exists($key,$nav_left);
}

function isMenuItem($array){
	global $purview;
	return array_key_exists($array,$purview);
	//return array_keys($array) == array(0,1);
}

function checkPurviewUrl($url){
	global $purview;
	foreach ($purview as $key=>$value) {
		if (strpos($url,$value[1])!==false) {
			return $key;
		}
	}
	return false;
}
function sortArrayByValue($array,$ordering='ASC'){
	if (!is_array($array)) {
		return false;
	}
	asort ($array);
	reset ($array);
	if ($ordering == 'DESC') {
		$array = array_reverse($array);
	}
	return $array;
}
function getPurviewKeys($array){
	$temp = array();
	foreach ($array as $key=>$value) {
		$value = explode('|',$value);
		if ($temp_key = checkPurviewUrl($value[3])) {
			$temp[$temp_key] = isset($temp[$temp_key]) ? $temp[$temp_key]+1:1;
		}
	}
	return $temp;
}
function getHotOpration(){
	if(file_exists(D_P.'data/bbscache/admin_record.php')){
		$bbslogfiledata=readlog(D_P.'data/bbscache/admin_record.php',10000);
	}
	$newopration = array();
	if ($bbslogfiledata) {
		unset($bbslogfiledata[0]);
		$newopration = getPurviewKeys($bbslogfiledata);
		$newopration = sortArrayByValue($newopration,'DESC');
	}
	if (is_array($newopration)) {
		$newopration = array_slice($newopration,0,5);
		return array_keys($newopration);
	}
	return false;
}
?>