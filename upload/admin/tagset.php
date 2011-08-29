<?php
!function_exists('adminmsg') && exit('Forbidden');
$basename="$admin_file?adminjob=tagset";

if(!$action){
	$db_perpage = 51;
	S::gp(array('keyword'));
	S::gp(array('page','ls','le','ns','ne','ifhot'),'GP');
	is_numeric($ls) && $ls = (int) $ls;
	is_numeric($le) && $le = (int) $le;
	is_numeric($ns) && $ns = (int) $ns;
	is_numeric($ne) && $ne = (int) $ne;
	$page < 1 && $page = 1;
	${'sel_'.$ifhot} = 'selected';
	$sql = "WHERE ifhot=".S::sqlEscape($ifhot);
	if($keyword){
		$sql .= " AND tagname LIKE ".S::sqlEscape("%$keyword%");
	}
	if($ls>0){
		$sql .= " AND char_length(tagname)>=".$ls;
	}
	if($le>0){
		$sql .= " AND char_length(tagname)<=".$le;
	}
	if($ns>0){
		$sql .= " AND num>=".$ns;
	}
	if($ne>0){
		$sql .= " AND num<=".$ne;
	}
	$limit = S::sqlLimit(($page-1)*$db_perpage,$db_perpage);
	$rt    = $db->get_one("SELECT COUNT(*) AS sum FROM pw_tags $sql");
	$pages = numofpage($rt['sum'],$page,ceil($rt['sum']/$db_perpage), "$basename&keyword=$keyword&ls=$ls&le=$le&ns=$ns&ne=$ne&ifhot=$ifhot&");

	$i = ($page-1)*$db_perpage+1;
	$j = 0;
	$tagdb = array();
	$query = $db->query("SELECT * FROM pw_tags $sql ORDER BY num DESC $limit");
	while($rt = $db->fetch_array($query)){
		$tagdb[] = $rt;
	}
	
	$ls = $ls ? $ls : (is_int($ls) ? 0 : '');
	$le = $le ? $le : (is_int($le) ? 0 : '');
	$ns = $ns ? $ns : (is_int($ns) ? 0 : '');
	$ne = $ne ? $ne : (is_int($ne) ? 0 : '');
	include PrintEot('tagset');exit;
} elseif($action=='tag'){
	S::gp(array('page','tagid','tagname'));
	(int)$page < 1 && $page = 1;
	$sql   = is_numeric($tagid) ? "tagid=".S::sqlEscape($tagid) : "tagname=".S::sqlEscape($tagname);
	$limit = "LIMIT ".($page-1)*$db_perpage.",$db_perpage";
	$rs    = $db->get_one("SELECT tagid,tagname,num FROM pw_tags WHERE $sql");
	$pages = numofpage($rs['num'],$page,ceil($rs['num']/$db_perpage),"$basename&tagid=$tagid&");

	$readb = $ttable_a = array();
	$query = $db->query("SELECT td.tagid,td.tid,t.subject FROM pw_tagdata td LEFT JOIN pw_threads t ON td.tid=t.tid WHERE tagid=".S::sqlEscape($rs['tagid'],false).$limit);
	while($rt = $db->fetch_array($query)){
		$readb[$rt['tid']] = $rt;
		$ttable_a[GetTtable($rt['tid'])][] = $rt['tid'];
	}
	foreach($ttable_a as $pw_tmsgs=>$tids){
		$tids  = S::sqlImplode($tids);
		$query = $db->query("SELECT tid,tags FROM $pw_tmsgs WHERE tid IN($tids)");
		while($rt = $db->fetch_array($query)){
			list($tags,$relatetag) = explode("\t",$rt['tags']);
			foreach(explode(' ',$tags) as $key=>$value){
				$readb[$rt['tid']]['tags'] .= "<a href=\"$basename&action=tag&tagname=".rawurlencode($value)."\">$value</a> ";
			}
			foreach(explode(' ',$relatetag) as $key=>$value){
				$readb[$rt['tid']]['relatetag'] .= "<a href=\"$basename&action=tag&tagname=".rawurlencode($value)."\">$value</a> ";
			}
		}
	}
	include PrintEot('tagset');exit;
} elseif($action=='addtag'){
	if($_POST['step']){
		S::gp(array('tags'),'GP',1);
		
		function checkTags($tags) {
			$tmpTags = explode(',',$tags);
			foreach ($tmpTags as $value) {
				$tagLength = strlen(trim($value));
				($tagLength > 15 || $tagLength < 3) && adminmsg('标签的长度请控制在 3-15 个字节之间', $basename);
			}
			return $tmpTags;
		}
		$tagdb = checkTags($tags);
		foreach($tagdb as $tag){
			if($tag = trim($tag)){
				$rt = $db->get_one("SELECT tagid FROM pw_tags WHERE tagname=".S::sqlEscape($tag));
				if(!$rt){
					$db->update("INSERT INTO pw_tags SET tagname=".S::sqlEscape($tag).",num=0");
				}
			}
		}
		updatetags();
		adminmsg('operate_success');
	}
} elseif ($action == 'setting') {
	ifcheck($db_iftag,'iftag');
	ifcheck($db_readtag,'readtag');
	include PrintEot('tagset');exit;
} elseif ($_POST['action'] == 'set') {

	S::gp(array('config'));

	foreach ($config as $key => $value) {
		setConfig("db_$key", $value);
	}

	$navConfigService = L::loadClass('navconfig', 'site'); /* @var $navConfigService PW_NavConfig */
	$navConfigService->controlShowByKey('sort_taglist',$config['iftag']);
	updatecache_c();
	adminmsg('operate_success', $basename . "&action=setting");

} elseif ($_POST['action'] == 'deltag') {

	S::gp(array('selid'));
	if (!$selid = checkselid($selid)) {
		adminmsg('operate_error');
	}
	$db->update("DELETE FROM pw_tags WHERE tagid IN($selid)");
	$db->update("DELETE FROM pw_tagdata WHERE tagid IN($selid)");
	updatetags();
	adminmsg('operate_success');

} elseif ($_POST['action'] == 'sethot') {

	S::gp(array('selid','ifhot'));
	if(!$selid = checkselid($selid)){
		adminmsg('operate_error');
	}
	$ifhot = $ifhot ? 0 : 1;
	$db->update("UPDATE pw_tags SET ifhot='$ifhot' WHERE tagid IN($selid)");
	updatetags();
	adminmsg('operate_success');
}


function updatetags() {
	global $db,$db_tagindex;
	$tagnum = max($db_tagindex,200);
	$tagdb = array();
	$query = $db->query("SELECT * FROM pw_tags WHERE ifhot='0' ORDER BY num DESC".S::sqlLimit($tagnum));
	while ($rs = $db->fetch_array($query)) {
		$tagdb[$rs['tagname']] = $rs['num'];
	}
	/** pwCache::writeover(D_P."data/bbscache/tagdb.php","<?php\r\n\$tagdb=".pw_var_export($tagdb).";\r\n?>"); **/
	pwCache::setData(D_P."data/bbscache/tagdb.php","<?php\r\n\$tagdb=".pw_var_export($tagdb).";\r\n?>");
	touch(D_P."data/bbscache/tagdb.php");
}
?>