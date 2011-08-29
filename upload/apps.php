<?php
define('SCR', 'app');
require_once ('global.php');
require_once (R_P . 'require/functions.php');
require_once (R_P . 'u/require/core.php');
//* require_once pwCache::getPath(D_P . 'data/bbscache/o_config.php');
pwCache::getData(D_P . 'data/bbscache/o_config.php');
if (isset($_GET['ajax'])) {
	define('AJAX', '1');
}
S::gp(array('q','uid'));
$USCR = 'user_app';
//导航
$navConfigService = L::loadClass('navconfig', 'site');
$homenavigation = $navConfigService->userHomeNavigation(PW_NAV_TYPE_MAIN, 'o');

if (in_array($q, array('ajax', 'article', 'diary', 'galbum', 'group', 'groups', 'hot', 'photos', 'sharelink', 'stopic', 'topicadmin', 'activity', 'weibo', 'collection'))) {
	$pwModeImg = "$imgpath/apps";
	require_once(R_P . 'u/lib/space.class.php');
	require_once(R_P.'require/showimg.php');
	$newSpace = new PwSpace($uid?$uid:$winduid);
	//TODO DELETE: ajax, topicadmin
	$appdir = app_specialRoute($q); //USE INT global.php printEOT
	list ( $_Navbar, $_LoginInfo ) = pwNavBar ();

	if (!is_dir($appEntryBasePath = A_P . $appdir . '/')) Showmsg('undefined_action');
	$appEntry = $appEntryBasePath . 'index.php';
	if (!file_exists($appEntry)) Showmsg("包含文件不存在，请创建index.php");
	list($faceurl) = showfacedesign($winddb['icon'],1,'m');
	require_once S::escapePath($appEntry);

//TODO what these actions for?
} elseif ($q == 'blooming') {

	S::gp(array('tid'),'G',2);
	!$db_siteappkey && Showmsg('app_not_register');

	//* @include_once pwCache::getPath(D_P.'data/bbscache/info_class.php');
	pwCache::getData(D_P.'data/bbscache/info_class.php');
	!is_array($info_class) && $info_class = array();

	$openclass = array();
	if (!empty($info_class)) {
       $openclass = $info_class;
	} else {
		Showmsg('app_not_blooming_class');
	}
	require_once PrintEot ( 'apps' );
	ajax_footer();

} elseif ($q == 'updata') {

	require_once (R_P . 'require/posthost.php');
	//* include_once pwCache::getPath(D_P . 'data/bbscache/level.php');
	pwCache::getData(D_P . 'data/bbscache/level.php');
	S::gp(array('tid', 'cid'), 'P', 2);

	$pw_tmsgs = GetTtable($tid);
	$rt = $db->get_one("SELECT t.tid,subject,content FROM pw_threads t LEFT JOIN $pw_tmsgs tm ON t.tid=tm.tid WHERE t.tid=" . S::sqlEscape($tid));

	$systitle = $winddb['groupid'] == '-1' ? '' : $ltitle[$winddb['groupid']];
	$memtitle = $ltitle[$winddb['memberid']];
	$uptitle = $systitle ? $systitle : $memtitle;

	if (!$cid || !$tid) Showmsg('Please select class');

	$partner = md5($db_siteid . $db_siteownerid);
	$content = pwConvert($rt['content'], 'gbk', $db_charset);
	$subject = pwConvert($rt['subject'], 'gbk', $db_charset);
	$windid = pwConvert($windid, 'gbk', $db_charset);
	$uptitle = pwConvert($uptitle, 'gbk', $db_charset);

	$para = array('tid' => $rt['tid'], 'cid' => $cid, 'upposter' => $windid, 'uptitle' => $uptitle, 'subject' => $subject, 'rf' => $pwServer['HTTP_REFERER'], 'sitehash' => $db_sitehash, 'action' => 'updata');

	ksort($para);
	reset($para);

	$arg = '';
	foreach ($para as $key => $value) {
		$arg .= "$key=" . urlencode($value) . "&";
	}

	$verify = md5(substr($arg, 0, -1) . $partner);

	if (strpos($content, '[attachment=') !== false) {
		preg_replace("/\[attachment=([0-9]+)\]/eis", "upload('\\1')", $content, $db_cvtimes);
	}

	$data = PostHost("http://app.phpwind.net/pw_app.php?", "action=updata&tid=$rt[tid]&cid=$cid&upposter=$windid&uptitle=$uptitle&sitehash=$db_sitehash&subject=" . urlencode($subject) . "&content=" . urlencode($content) . "&verify=$verify&rf=" . urlencode($pwServer['HTTP_REFERER']), "POST");

	$backdata = substr($data, strpos($data, '$backdata=') + 10);
	$backdata = pwConvert($backdata, $db_charset, 'gbk');

	Showmsg($backdata);

} elseif ($q == 'survey') {

	//* @include_once pwCache::getPath(D_P . "data/bbscache/survey_cache.php");
	pwCache::getData(D_P . "data/bbscache/survey_cache.php");
	require_once (R_P . 'require/header.php');
	S::gp(array('itemid'), 'G', 2);
	if (!$itemid) {
		foreach ($survey_cache as $itemdb) {
			$itemid = $itemdb['itemid'] > $itemid ? $itemdb['itemid'] : $itemid;
		}
	}
	$survey = $survey_cache[$itemid];

	require_once PrintEot('apps');
	footer();
} elseif ($q == 'appthread') { #新增app帖子交换的弹出框
	S::gp(array('do'), 'G');
	S::gp(array('forumid'), 'G', 2);
	!$db_siteappkey && Showmsg('app_siteappkey_notexist');
	$appclient = L::loadClass('appclient');
	$app_url = $appclient->getThreadsUrl('index', 'main', $do, $forumid);
	require_once PrintEot('apps');
	ajax_footer();

} elseif ($q == 'music') {

	S::gp(array('page'), GP, 2);
	S::gp(array('keyword'));

	if (!$db_xiami_music_open || $db_apps_list['11']['status'] != 1) {
		echo "close\t";
		ajax_footer();
	}
	$numofpage = (int)GetCookie('numofpage');
	$page > $numofpage && $page = $numofpage;

	!$db_siteappkey && Showmsg('app_siteappkey_notexist');

	$datadb = array();
	$appclient = L::loadClass('appclient');
	$datadb = $appclient->getMusic($page, $keyword);

	if ($datadb == 'close') {
		echo "close\t";
		ajax_footer();
	}

	$musicdb = $datadb['music'];
	$numofpage = $datadb['page'];
	$total = $datadb['total'];
	Cookie('numofpage', $numofpage);

	(!is_numeric($page) || $page < 1) && $page = 1;
	$db_perpage = 8;
	$page > $numofpage && $page = $numofpage;
	$pages = numofpage_music($total, $page, $numofpage, "apps.php?q=music&ajax=1&keyword=$keyword&", null, 1);

	$music_list_html = '<div class="musicshow">';
	if (is_array($musicdb)) {
		$music_list_html .= '<ul class="music_list">';
		foreach ($musicdb as $value) {
			$value['song_name_s'] = substrs($value['song_name'], 30);
			$music_list_html .= "<li><a onclick=\"insert_xiami_music('$value[song_id]');return false;\" href=\"javascript:;\" class=\"fr\">[" . getLangInfo('other', 'music_insert') . "]</a><a title=\"$value[song_name]\" href=\"javascript:;\" onclick=\"insert_xiami_music('$value[song_id]');return false;\">$value[song_name_s] -- $value[artist_name]</a><input type=\"hidden\" id=\"$value[song_id]\" value=\"$value[song_info]\"/></li>";
		}
		$music_list_html .= "</ul>$pages";
	} else {
		$music_list_html .= "<p align=\"center\" style=\"height:70px; line-height:50px\"><span class=\"musicresult\" style=\"background:url($imgpath/post/c_editor/music_none.gif) no-repeat 0 0; display:inline-block; padding-left:60px\">" . getLangInfo('other', 'music_none1') . "<span class=\"musicred\"><a href=\"javascript:;\" onclick=\"return false\">$keyword</a></span>" . getLangInfo('other', 'music_none2') . "</span></p>";

	}
	$music_list_html .= "</div>";

	echo "success\t$music_list_html";
	ajax_footer();

} else {
	S::gp(array('id'), 'G', 2);
	$q = $id;
	$USCR = 'user_appid';
	!$winduid && Showmsg('not_login');
	if (!$db_appifopen || !$db_siteappkey) Showmsg('app_close');

	$app_array = getUserApplist();
	$appinfo = getAppById($app_array,$id);

	require_once(R_P . 'u/lib/space.class.php');
	$newSpace = new PwSpace($winduid);
	$space = $newSpace->getInfo();

	$param = array();
	$pw_query = base64_decode(urldecode(str_replace('&#61;', '=', $_GET['pw_query'])));

	if ($pw_query) {
		$param['pw_query'] = base64_encode($pw_query);
	}
	$param['pw_appId'] = $id;
	$param['pw_uid'] = $winduid;
	$param['pw_siteurl'] = $db_bbsurl;
	$param['pw_sitehash'] = $db_sitehash;
	$param['pw_t'] = $timestamp;
	$param['pw_bbsapp'] = 1;

	$url = $db_server_url . '/apps.php?';

	foreach ($param as $key => $value) {
		$url .= "$key=" . urlencode($value) . '&';
	}
	$hash = $param['pw_appId'] . '|' . $param['pw_uid'] . '|' . $param['pw_siteurl'] . '|' . $param['pw_sitehash'] . '|' . $param['pw_t'];
	$url .= 'pw_sig=' . md5($hash . $db_siteownerid);

	//require_once (R_P . 'require/header.php');
	require_once PrintEot('apps');
	pwOutPut();
	//footer();
}
function getAppById($app,$appid){
	$appInfo = array();
	foreach($app as $key=>$value){
		if($value['appid'] == $appid ){
			$appInfo = $value;
			break;
		}
	}
	return $appInfo;
}
function upload($aid) {
	global $db, $content, $db_bbsurl, $attachpath;
	$rt = $db->get_one("SELECT attachurl,type FROM pw_attachs WHERE aid=".S::sqlEscape($aid));
	if ($rt['attachurl']) {
		if ($rt['type'] == 'img') {
			$img = "[img]$db_bbsurl/$attachpath/" . $rt['attachurl'] . "[/img]";
			$content = addslashes(str_replace("[attachment=$aid]", $img, $content));
		} else {
			$content = addslashes(str_replace("[attachment=$aid]", '', $content));
		}
	}
}

//xiami_music 分页
function numofpage_music($count, $page, $numofpage, $url, $max = null, $ajaxurl = '') {
	global $tablecolor;
	$total = $numofpage;
	if (!empty($max)) {
		$max = (int) $max;
		$numofpage > $max && $numofpage = $max;
	}
	if ($numofpage <= 1 || !is_numeric($page)) {
		return '';
	} else {
		list($url, $mao) = explode('#', $url);
		$mao && $mao = '#' . $mao;
		$pages = "<ul class=\"B_face_pages B_cc\">";
		for ($i = $page - 3; $i <= $page - 1; $i++) {
			if ($i < 1) continue;
			$pages .= "<li><a style=\"cursor:pointer;\"" . ($ajaxurl ? " onclick=\"return getMusic('$i')\"" : '') . ">$i</a></li>";
		}
		$pages .= "<li><a href=\"javascript:;\" class=\"current\">$page</a></li>";
		if ($page < $numofpage) {
			$flag = 0;
			for ($i = $page + 1; $i <= $numofpage; $i++) {
				$pages .= "<li><a href=\"javascript:;\"" . ($ajaxurl ? " onclick=\"return getMusic('$i')\"" : '') . ">$i</a><li>";
				$flag++;
				if ($flag == 4) break;
			}
		}
		$pages .= "</ul>";
		return $pages;
	}
}

function app_specialRoute($route) {
	if (in_array($route, array("groups", "group", "galbum", "topicadmin"))) {
		define('APP_GROUP',1);
		return 'groups';
	}
	if (in_array($route, array("share", "sharelink"))) {
		return 'share';
	}
	return $route;
}

function getNextOrPreDiaryName($did,$fuid,$type){
	global $db,$winduid;
	$uid = $fuid ? $fuid : $winduid;
	$sqladd = "WHERE uid=".S::sqlEscape($uid);
	if ($type == 'next') {
		$sqladd = $uid != $winduid ? $sqladd . " AND privacy != 2 AND did > " . S::sqlEscape($did) : $sqladd. " AND did > " . S::sqlEscape($did);
		$minDid = $db->get_value('SELECT MIN(did) FROM pw_diary ' . $sqladd);
		if(!$minDid) return '';
		$diaryName = $db->get_value("SELECT subject as diaryName FROM pw_diary  WHERE did = $minDid");
	} elseif ($type == 'pre') {
		$sqladd = $uid != $winduid ? $sqladd . " AND privacy != 2 AND did < " . S::sqlEscape($did) : $sqladd . " AND did < " . S::sqlEscape($did);
		$maxDid = $db->get_value('SELECT MAX(did) FROM pw_diary ' . $sqladd);
		if(!$maxDid) return '';
		$diaryName = $db->get_value("SELECT subject as diaryName FROM pw_diary WHERE did = $maxDid");
	}
	return ($diaryName) ? $diaryName : '';
}
?>