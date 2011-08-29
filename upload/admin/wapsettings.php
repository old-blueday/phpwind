<?php
!defined('P_W') && exit('Forbidden');
$threads = L::loadClass('Threads', 'forum');
$basename .= '&action='.$action;
if (empty($action) || $action == 'cpush') {
	InitGP(array('step'),'GP');
	
	if($step == '4'){//删除 移动
		InitGP(array('pushid','subaction','pushtype'),'GP');
		$dels = array();
		if (!is_array($pushid)) {
			$dels = array($pushid);
		}else{
			$dels = $pushid;
		}
		if ($subaction=='move' && $pushtype) {
			!empty($dels) && $db->update("UPDATE pw_wappush SET typeid = ".pwEscape($pushtype)." WHERE id IN (".pwImplode($dels).")");
		}elseif($subaction=='del'){
			!empty($dels) && $db->update("DELETE FROM pw_wappush WHERE id IN (".pwImplode($dels).")");
		}
		adminmsg('operate_success');
	}elseif($step == '3'){ //添加修改
		InitGP(array('id','tid','link','subject','pushtype'),'GP');
		if (!$link || !$subject) {
			adminmsg('推荐标题或链接地址不能为空',"javascript:history.go(-1);");
		}
		//检查链接地址是否重复
		$link = trim($link);
		if (!$id) {
			$pushdb = $db->get_one("SELECT p.* FROM pw_wappush p WHERE p.link = ".pwEscape($link));
			if ($pushdb) {
				$addcpushlink = $basename . "&step=3&id=".$pushdb['id']."&tid=".$tid."&link=".$link."&subject=".urlencode($subject)."&pushtype=".$pushtype;
				adminmsg("此链接地址已经存在,是否覆盖？  <a href=".$addcpushlink.">确认</a>","javascript:history.go(-1);",10);
			}
		}
		//validation
		$insertdb = array('id'		=>	$id,
						  'tid'		=>  $tid,
						  'link'	=>  $link,
						  'subject'	=>  $subject,
						  'typeid'=>	$pushtype);
		$db->update("REPLACE INTO pw_wappush SET ".pwSqlSingle($insertdb));
		adminmsg('operate_success');
	}elseif($step == '2'){ //添加
		InitGP(array('tid','id'),'GP');
		if ($id) {	//编辑
			$pushdb = $db->get_one("SELECT p.* FROM pw_wappush p WHERE p.id = ".pwEscape($id));
			if ($pushdb) {
				$pushlink = $pushdb['link'];
				$subject = $pushdb['subject'];
				$tid = $pushdb['tid'];
			}
		}else{	//导入
			if ($tid) {
				if(!$db->get_one('SELECT tid FROM pw_recycle WHERE tid='.pwEscape($tid))){
					$pw_tmsgs = GetTtable($tid);
					$threadb = $db->get_one("SELECT t.tid,t.subject FROM pw_threads t INNER JOIN $pw_tmsgs tm ON tm.tid=t.tid WHERE t.tid=" . pwEscape($tid) . " AND ifcheck=1");
				}else{
					adminmsg("此帖已被删除","$basename&action=cpush&step=2");
				}
			}
			if ($threadb) {
				$pushlink = "read.php?tid=".$threadb['tid'];
				$subject = $threadb[subject];
			}
		}
		$pushTypes = array();
		$sql = "SELECT * FROM pw_wappushtype p WHERE p.id is not NULL AND p.state = '1' ORDER BY p.sort ";
		$query = $db->query($sql);
		while ($rt = $db->fetch_array($query)) {
			$pushTypes[] = $rt;
		}
	}else{
		InitGP(array('subject','pushtype','page'),'GP');
		if ($subject) {
			$where = "AND p.subject like ".pwEscape('%' . $subject . '%');
		}
		if ($pushtype) {
			$where .= "AND p.typeid = " . pwEscape($pushtype);
		}
		$count = $db->get_value("SELECT count(*) FROM pw_wappush p WHERE p.id is not null $where");
		list($pages,$limit) = pwLimitPages($count,$page,"$basename&");
		$pushdbs = array();
		$query = $db->query("SELECT p.*,pt.typename FROM pw_wappush p LEFT JOIN pw_wappushtype pt ON p.typeid = pt.id WHERE p.id is not null $where ORDER BY p.id DESC $limit");
		while ($rt = $db->fetch_array($query)) {
			$pushdbs[] = $rt;
		}
		
		$pushTypes = array();
		$sql = "SELECT * FROM pw_wappushtype p WHERE p.id is not NULL ORDER BY p.sort ";
		$query = $db->query($sql);
		while ($rt = $db->fetch_array($query)) {
			$pushTypes[] = $rt;
		}
	}
} elseif($action == 'pushtype') {
	InitGP(array('step'),'GP');
	if($step == '3'){ //删除
		InitGP(array('id'),'GP');
		if ($id) {
			$pushdb = $db->get_one("SELECT * FROM pw_wappush p WHERE p.typeid = " . pwEscape($id));
			if ($pushdb) {
				adminmsg('本分类下已存在推荐数据，请将内容移除后再删除');
			}
			$db->update("DELETE FROM pw_wappushtype WHERE id = " . pwEscape($id));
		}
		adminmsg('operate_success');
	}elseif ($step == '2') {  //添加修改
		InitGP(array('typename','order','states'),'GP');
		$replacedb = array();
		for ($i = 0; $i < count((array)$typename['new']); $i++) {
			if ($typename['new'][$i] != '') {
				$insertdb[] = array('id'		=>  '',
									'sort'		=> 	(int)$order['new'][$i],
									'typename'	=>	$typename['new'][$i],
									'state'		=>  $states['new'][$i]);
			}
		}
		$sql = "SELECT p.* FROM pw_wappushtype p WHERE p.id is not NULL ORDER BY p.sort DESC";
		$query = $db->query($sql);
		while ($rt = $db->fetch_array($query)) {
			empty($typename[$rt['id']]) && adminmsg('分类名称不能为空');
			if (isset($typename[$rt['id']])) {
				if ($rt['state'] != $states[$rt['id']] ||
					$rt['sort'] != $order[$rt['id']] || 
					$rt['typename'] != $typename[$rt['id']]) {
						$insertdb[] = array (
											 'id'			=>  $rt['id'],
											 'sort'		=> 	(int)$order[$rt['id']],
											 'typename'		=>	$typename[$rt['id']],
											 'state'		=>  $states[$rt['id']]);
				}
			}
		}
		!empty($insertdb) && $db->update("REPLACE INTO pw_wappushtype (`id`,`sort`,`typename`,`state`) VALUES" . pwSqlMulti($insertdb));
		adminmsg('operate_success');
	}else{
		InitGP(array('page'),'GP');
		$count = $db->get_value("SELECT COUNT(*) FROM pw_wappushtype p WHERE p.id is not NULL");
		list($pages,$limit) = pwLimitPages($count,$page,"$basename&");
		$pushTypes = array();
		$sql = "SELECT p.* FROM pw_wappushtype p WHERE p.id is not NULL ORDER BY p.sort ASC $limit";
		$query = $db->query($sql);
		while ($rt = $db->fetch_array($query)) {
			$pushTypes[] = $rt;
		}
	}
}

function pwLimitPages($count,$page,$pageurl) {
	global $db_perpage,$db_maxpage;
	//require_once (R_P.'require/forum.php');
	$numofpage = ceil($count/$db_perpage);
	$numofpage = $numofpage > $db_maxpage ? $db_maxpage : $numofpage;
	$page < 1 ? $page = 1 : ($page > $numofpage ? $page = $numofpage : null);
	$pages = numofpage($count,$page,$numofpage,$pageurl,$db_maxpage);
	$limit = pwLimit(($page-1) * $db_perpage,$db_perpage);
	return array($pages,$limit);
}

include PrintEot('wapsetting');exit;
?>