<?php
/**
 * Copyright (c) 2003-2103 phpwind
 * Function library
 */
defined('P_W') || exit('Forbidden');

function openfile($filename){
	$filedb = explode('<:wind:>',str_replace("\n","\n<:wind:>",readover($filename)));
	$count = count($filedb)-1;
	if ($count > -1 && (!$filedb[$count] || $filedb[$count]=="\r")) {
		unset($filedb[$count]);
	}
	empty($filedb) && $filedb[0] = '';
	return $filedb;
}
function getDescripByTid($tid){
	global $db;
	$tid = (int)$tid;
	if (!$tid) return '';
	$table	= GetTtable($tid);
	$content= $db->get_value("SELECT content FROM $table WHERE tid=".S::sqlEscape($tid));
	$content= preg_replace("/<((style|script).*?)>(.*?)<(\/\\1.*?)>/si","",$content);
	$content= strip_tags(stripWindCode($content));
	$content= trim($content);
	return substrs($content,200);
}
function Sql_cv($var){
	global $db;
	$db->update('INSERT INTO pw_sqlcv SET var='.S::sqlEscape($var),0);
	$id = $db->insert_id();
	$rt = $db->get_one('SELECT var FROM pw_sqlcv WHERE id='.S::sqlEscape($id));
	$db->update('DELETE FROM pw_sqlcv WHERE id='.S::sqlEscape($id));
	return $rt['var'];
}
/* admin bench only */
function pwWritable($pathfile) {
	//Copyright (c) 2003-2103 phpwind
	//fix windows acls bug noizy
	substr($pathfile,-1)=='/' && $pathfile = substr($pathfile,0,-1);
	if (is_dir($pathfile)) {
		mt_srand((double)microtime()*1000000);
		$pathfile = $pathfile.'/pw_'.uniqid(mt_rand()).'.tmp';
	}
	$unlink = file_exists($pathfile) ? false : true;
	$fp = @fopen($pathfile,'ab');
	if ($fp===false) return false;
	fclose($fp);
	if ($unlink) P_unlink($pathfile);
	return true;
}
/*
 * 获取论坛的普通版块id
 */
function getCommonFid() {
	static $fids = null;

	if (!isset($fids)) {
		if (pwFilemtime(D_P.'data/bbscache/commonforum.php') < pwFilemtime(D_P.'data/bbscache/forum_cache.php')) {
			global $db;
			$query = $db->query("SELECT fid FROM pw_forums WHERE type<>'category' AND cms<>1 AND password='' AND forumsell='' AND f_type<>'hidden' AND allowvisit=''");
			while ($rt = $db->fetch_array($query)) {
				$fids .= ",'$rt[fid]'";
			}
			$fids && $fids = substr($fids,1);
			pwCache::setData(D_P.'data/bbscache/commonforum.php',"<?php\r\n\$fids = \"$fids\";\r\n?>");
		} else {
			//* include  pwCache::getPath(D_P.'data/bbscache/commonforum.php');
			extract(pwCache::getData(D_P.'data/bbscache/commonforum.php', false));
		}
	}
	return $fids;
}
/*
 * 获取论坛的特殊版块id
 */
function getSpecialFid() {
	static $fids = null;

	if (!isset($fids)) {
		if (pwFilemtime(D_P.'data/bbscache/specialforum.php') < pwFilemtime(D_P.'data/bbscache/forum_cache.php')) {
			global $db;
			$query = $db->query("SELECT fid FROM pw_forums WHERE type<>'category' AND (cms=1 OR password!='' OR forumsell!='' OR f_type='hidden' OR allowvisit!='')");
			while ($rt = $db->fetch_array($query)) {
				$fids .= ",'$rt[fid]'";
			}
			$fids && $fids = substr($fids,1);
			pwCache::setData(D_P.'data/bbscache/specialforum.php',"<?php\r\n\$fids = \"$fids\";\r\n?>");
		} else {
			//* include pwCache::getPath(D_P.'data/bbscache/specialforum.php');
			extract(pwCache::getData(D_P.'data/bbscache/specialforum.php', false));
		}
	}
	return $fids;
}
function getCateid($fid) {
	global $forum;
	if (in_array($forum[$fid]['type'],array('sub2','sub','forum'))) {
		return getCateid($forum[$fid]['fup']);
	} elseif ($forum[$fid]['type'] == 'category') {
		return $fid;
	} else {
		return false;
	}
}

function pwDelThreadAtt($path, $ifftp, $ifthumb = 3) {
	pwDelatt($path, $ifftp);
	($ifthumb & 1) && pwDelatt('thumb/' . $path, $ifftp);
	($ifthumb & 2) && pwDelatt('thumb/mini/' . $path, $ifftp);
}

function pwDelatt($path, $ifftp) {
	if (strpos($path,'..') !== false) {
		return false;
	}
	if (file_exists("$GLOBALS[attachdir]/$path")) {
		P_unlink("$GLOBALS[attachdir]/$path");
	}
	if (pwFtpNew($GLOBALS['ftp'], $ifftp)) {
		$GLOBALS['ftp']->delete($path);
	}
	return true;
}

function pwFtpNew(&$ftp,$ifftp) {
	if (!$ifftp) return false;
	if (!is_object($ftp)) {
		//* include pwCache::getPath(D_P . 'data/bbscache/ftp_config.php');
		extract(pwCache::getData(D_P . 'data/bbscache/ftp_config.php', false));
		L::loadClass('ftp', 'utility', false);
		$ftp = new FTP($ftp_server,$ftp_port,$ftp_user,$ftp_pass,$ftp_dir);
	}
	return true;
}

function pwFtpClose(&$ftp) {
	if (is_object($ftp) && method_exists($ftp,'close')) {
		$ftp->close();
		$ftp = null;
	}
}
/**
 * 获取好友列表
 *
 * @param int $uid		需要查找的uid;
 * @param int $start	limit条件
 * @param int $num		limit条件
 * @param int $ftype	好友分组
 * @param int $show		是否需要详细数据
 * @return array
 */
function getFriends($uid,$start=0,$num=0,$ftype=false,$show=false,$imgtype='m'){
	global $db,$db_onlinetime,$timestamp,$winduid;
	$fild	= 'm.uid,m.username,f.ftid,f.iffeed';
	$order  = $where = '';
	if ($show) {
		$fild .= ',m.icon as face,m.honor,md.f_num,md.thisvisit,md.lastvisit';
		$left = 'LEFT JOIN pw_memberdata md ON f.friendid=md.uid';
		$order = 'md.thisvisit';
	} else {
		$left = '';
		$order = 'f.joindate';
	}
	if ($ftype !== false && $ftype !== '') {
		$ftype	= (int)$ftype;
		$where = ' AND f.ftid='.S::sqlEscape($ftype);
	}
	$start	= (int) $start;
	$num	= (int) $num;
	if ($start || $num) {
		!$num && $num = 8;
		$limit = S::sqlLimit($start,$num);
	} else {
		$limit = '';
	}
	$rs = $db->query("SELECT $fild FROM pw_friends f LEFT JOIN pw_members m ON f.friendid=m.uid $left WHERE f.uid=".S::sqlEscape($uid)." AND f.status=0 $where ORDER BY $order DESC $limit");
	$result = array();
	if ($show) {
		require_once(R_P.'require/showimg.php');
		while ($one = $db->fetch_array($rs)) {
			list($one['face']) = showfacedesign($one['face'],1,$imgtype);
			$one['honor'] = substrs($one['honor'],90);
			$one['lastvisit']	= get_date($one['lastvisit']);
			$result[$one['uid']] = $one;
		}
	} else {
		while ($one = $db->fetch_array($rs)) {
			$result[$one['uid']] = $one;
		}
	}
	count($result) == 0 && $result = false;
	return $result;
}

function getForumName($fid){
	$temp_forum = getForumCache();
	if (isset($temp_forum[$fid])) {
		return strip_tags($temp_forum[$fid]['name']);
	}
	return '';
}
function getForumCache(){
	static $temp_forum = array();

	if (!$temp_forum) {
		global $forum;
		if (!$forum) {
			//* include pwCache::getPath(D_P.'data/bbscache/forum_cache.php');
			extract(pwCache::getData(D_P.'data/bbscache/forum_cache.php', false));
		}
		$temp_forum = $forum;
	}
	return $temp_forum;
}
function getForumUrl($fid){
	global $db_bbsurl;
	$fid = (int) $fid;
	if ($fid) {
		return $db_bbsurl.'/thread.php?fid='.$fid;
	}
	return '';
}
/**
 * 获取主题的主题分类名称及url
 * @param $type
 * @param $fid
 * @return array
 */
function getTopicType($type,$fid) {
	$foruminfo = L::forum($fid);
	$topic_type = isset($foruminfo['topictype'][$type]) ? $foruminfo['topictype'][$type] : array();
	return $topic_type ? array($topic_type['name'],getForumUrl($fid).'&type='.$type) : array('','');
}

function getmemberid($nums){
	global $lneed;
	$lneed || $lneed = L::config('lneed', 'level');
	arsort($lneed); reset($lneed);
	foreach ($lneed as $key => $lowneed) {
		$gid = $key;
		if ($nums >= $lowneed) {
			break;
		}
	}
	return $gid;
}
function getMembername($memberid) {
	global $ltitle;
	$ltitle || $ltitle = L::config('ltitle', 'level');
	arsort($ltitle) ;reset($ltitle);
	return  $ltitle[$memberid];
}
function getNextMemberid($memberid) {
	global $lneed;
	$lneed || $lneed = L::config('lneed', 'level');
	asort($lneed,0); reset($lneed);
	$memberneed =$lneed[$memberid];
	foreach ($lneed as $key => $lowneed) {
		if ($memberneed < $lowneed) {
			$gid = $key;
			break;
		}
	}
	return $gid;
}
function getmemberNeed($memberid) {
	global $lneed;
	$lneed || $lneed = L::config('lneed', 'level');
	arsort($lneed) ;reset($lneed);
	return  (int)$lneed[$memberid];
}
function CalculateCredit($creditdb,$upgradeset) {
	$credit = 0;
	if (!is_array($upgradeset)) return $credit;
	foreach ($upgradeset as $key => $val) {
		if ($creditdb[$key] && $val) {
			if ($key == 'rvrc') {
				$creditdb[$key] = round($creditdb[$key]/10,1);
			} elseif ($key == 'onlinetime') {
				$creditdb[$key] = (int)($creditdb[$key]/3600);
			}
			$credit += (int)$creditdb[$key]*$val;
		}
	}
	return (int)$credit;
}

/**
 * 更新数据缓存库
 *
 */
function updateDatanalyse($tag, $action, $num) {
	global $db,$tdtime;
	$tag = (int)$tag; $num = (int)$num;
	$history = mktime ( 0, 0, 0, 0, 0, 0);
	if (!empty($tag) && !empty($action)) {
		$isTdtime = $isHistory = 0;
		$timeuints = array($tdtime,$history);
		$query = $db->query("SELECT timeunit FROM pw_datanalyse WHERE tag=".S::sqlEscape($tag)."AND action=".S::sqlEscape($action));
		while($rs = $db->fetch_array($query)){
			if($rs['timeunit'] == $tdtime){
				$isTdtime = 1;
			}elseif($rs['timeunit'] == $history){
				$isHistory = 1;
			}
		}
		if($isTdtime && $isHistory){
			return $db->query("UPDATE LOW_PRIORITY pw_datanalyse SET num=num+".S::sqlEscape($num) ." WHERE tag=".S::sqlEscape($tag)."AND action=".S::sqlEscape($action)."AND timeunit IN (".S::sqlImplode($timeuints).")");
		}elseif($isTdtime == 0 && $isHistory == 0){
			return $db->query("REPLACE LOW_PRIORITY INTO pw_datanalyse (tag,action,timeunit,num) VALUES (".S::sqlEscape($tag).",".S::sqlEscape($action).",".S::sqlEscape($tdtime).",".S::sqlEscape($num)."),(".S::sqlEscape($tag).",".S::sqlEscape($action).",".S::sqlEscape($history).",".S::sqlEscape($num).")");
		}
		if($isTdtime){
			$db->query("UPDATE LOW_PRIORITY pw_datanalyse SET num=num+".S::sqlEscape($num) ." WHERE tag=".S::sqlEscape($tag)."AND action=".S::sqlEscape($action)."AND timeunit=".S::sqlEscape($tdtime));
		}else{
			$db->query("REPLACE LOW_PRIORITY INTO pw_datanalyse SET tag=".S::sqlEscape($tag).",action=".S::sqlEscape($action).",timeunit=".S::sqlEscape($tdtime).",num=".S::sqlEscape($num));
		}
		if($isHistory){
			$db->query("UPDATE LOW_PRIORITY pw_datanalyse SET num=num+".S::sqlEscape($num) ." WHERE tag=".S::sqlEscape($tag)."AND action=".S::sqlEscape($action)."AND timeunit=".S::sqlEscape($history));
		}else{
			$db->query("REPLACE LOW_PRIORITY INTO pw_datanalyse SET tag=".S::sqlEscape($tag).",action=".S::sqlEscape($action).",timeunit=".S::sqlEscape($history).",num=".S::sqlEscape($num));
		}
	}
}

function initJob($userId,$jobName,$factor=array()){
	global $db_job_isopen;
	if(!$db_job_isopen){
		return;
	}
	$jobService = L::loadclass("job", 'job');
	$jobService->jobController($userId,$jobName,$factor);
}
/*主题印戳*/
function overPrint($overprint,$tid,$operate='',$oid=''){
	if(!in_array($overprint,array(1,2))){
		return false;
	}
	$overPrintService = L::loadclass("overprint", 'forum');
	/*过滤*/
	if($overPrintService->checkThreadRelated($overprint,$operate,$tid)){
		return false;
	}
	if($overprint == 2){
		$oid = 0;$operate='';
	}
	$overPrintService->suckThread($tid,$operate,$oid);
}
/**
 * 获得在线用户
 * @return Array <multitype:, unknown>
 */
function GetOnlineUser() {
	global $db_online,$db;
	$onlineuser = array();

	if ($db_online == 1) {
		/**
		$query = $db->query("SELECT username,uid FROM pw_online WHERE uid>0");
		while ($rt = $db->fetch_array($query)) {
			$onlineuser[$rt['uid']] = $rt['username'];
		}**/
		
		$onlineService = L::loadClass('OnlineService', 'user');
		$onlineuser = $onlineService->getOnlineUserName();			
	
	} else {
		$onlinedb = openfile(D_P.'data/bbscache/online.php');
		if (count($onlinedb) == 1) {
			$onlinedb = array();
		} else {
			unset($onlinedb[0]);
		}
		foreach ($onlinedb as $key => $value) {
			if (trim($value)) {
				if (strrpos($value,'<>')) continue;
				$dt = explode("\t",$value);
				$onlineuser[$dt[8]] = $dt[0];
			}
		}
	}
	return $onlineuser;
}


/**
 * 返回select的HTML Tag
 * @param array $options option选项，可以是array('value1' => 'text1', 'value2' => 'text2')， array ('optgrouplabel1' => array ('value1' => 'text1', 'value2' => 'text2'), 'value3' => 'text3')
 * @param mix $selected 选中的option的value
 * @param select的name的值，如无，返回的HTML不包含select这个Tag(只有option Tag)
 * @return HTML
 */
function getSelectHtml($options, $selected = '', $selectTagName = '') {
	$return = '';
	if (is_array($options)) {
		foreach ($options as $value => $text) {
			if (is_array($text)) {
				$return .= "<optgroup label=\"$value\">";
				$return .= getSelectHtml($text, $selected);
				$return .= "</optgroup>";
			} else {
				$selectedHtml = (string) $value == (string) $selected ? ' selected="selected"' : '';
				$return .= "<option value=\"$value\"$selectedHtml>$text</option>";
			}
		}
	}
	if ($selectTagName) {
		$return = "<select name=\"$selectTagName\">$return</select>";
	}
	return $return;
}
