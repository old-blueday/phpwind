<?php
!function_exists('adminmsg') && exit('Forbidden');

$basename = "$admin_file?adminjob=postcate";

InitGP(array('pcid'),GP,2);
$pcid > 0 && $pcvaluetable = GetPcatetable($pcid);

if (empty($action)){
	$postcatedb = array();
	$query = $db->query("SELECT name,pcid,ifable,vieworder FROM pw_postcate ORDER BY vieworder,pcid");
	while ($rt = $db->fetch_array($query)) {
		$postcatedb[$rt['pcid']] = $rt;
	}
	$query = $db->query("SELECT name,pcid,fid FROM pw_forums WHERE pcid!=0 AND type<>'category'");
	while ($rt = $db->fetch_array($query)) {
		foreach (explode(',',$rt['pcid']) as $value) {
			if (!$postcatedb[$value]['forum']) {
				$postcatedb[$value]['forum'] = "<a href=\"thread.php?fid={$rt['fid']}\" target=\"blank\">".$rt['name']."</a>";
			} else {
				$postcatedb[$value]['forum'] .= ','."<a href=\"thread.php?fid={$rt['fid']}\" target=\"blank\">".$rt['name']."</a>";
			}
		}
	}
	$ajax_basename_editpostcate = EncodeUrl($basename."&action=editpostcate&");
	include PrintEot('postcate');exit;

}  elseif ($action == 'postcate') {
	InitGP(array('page','step','field','newfield'));

	@include_once(D_P.'data/bbscache/postcate_config.php');

	$sql = '';
	!$pcid && $pcid = $db->get_value("SELECT pcid FROM pw_postcate ORDER BY vieworder");

	if ($pcid) {
		$searchhtml = $asearchhtml = '';
		$i = 0;
		$query = $db->query("SELECT fieldid,name,type,rules,ifsearch,ifasearch,vieworder FROM pw_pcfield WHERE ifable=1 AND (ifsearch=1 OR ifasearch=1) AND pcid=".pwEscape($pcid). "ORDER BY vieworder,fieldid");
		while ($rt = $db->fetch_array($query)) {
			$i++;
			$rt['fieldvalue'] = $field[$rt['fieldid']];
			$rt['rules'] && $rt['rules'] = unserialize($rt['rules']);
			if ($rt['ifsearch'] == 1) {
				$searchhtml .= getSearchHtml($rt);
			} elseif ($rt['ifasearch'] == 1) {
				$asearchhtml .= getSearchHtml($rt);
			}
		}
		$searchhtml .= '</span>';
		$asearchhtml .= '</span>';
		if (strpos($searchhtml,'</span></span>') !== false) {
			$searchhtml = str_replace('</span></span>','</span>',$searchhtml);
		}
		if (strpos($asearchhtml,'</span></span>') !== false) {
			$asearchhtml = str_replace('</span></span>','</span>',$asearchhtml);
		}
		$pcid = (int)$pcid;
		$pcvaluetable = GetPcatetable($pcid);
	}

	if ($step == 'search') {
		L::loadClass('postcate', 'forum', false);
		$searchPostcate = new postCate($field);
		if (!$newfield) {
			$newfield = StrCode(serialize($field));
		}
		list($count,$tiddb,$alltiddb) = $searchPostcate->getSearchvalue($newfield,'one',true,true);
		is_array($tiddb) && $sql .= " AND pv.tid IN(".pwImplode($tiddb).")";
		is_array($alltiddb) && $alltids = implode(',',$alltiddb);
	}

	if ($step != 'search' || !$count) {

		$alltiddb = $threadb = $newtiddb = array();
		$alltiddb = array();
		$query = $db->query("SELECT tid FROM $pcvaluetable WHERE ifrecycle=0");
		while ($rt = $db->fetch_array($query)) {
			$alltiddb[] = $rt['tid'];
		}
		if ($alltiddb) {
			$query = $db->query("SELECT tid FROM pw_threads WHERE tid IN(".pwImplode($alltiddb).")");
			while ($rt = $db->fetch_array($query)) {
				$threadb[$rt['tid']] = $rt['tid'];
			}
		}
		foreach ($alltiddb as $value) {
			if (!$threadb[$value]) {
				$newtiddb[] = $value;
			}
		}

		if (count($newtiddb) > 0) {
			$db->update("DELETE FROM $pcvaluetable WHERE tid IN(".pwImplode($newtiddb).") AND ifrecycle=0");
		}

		is_array($threadb) && $alltids = implode(',',$threadb);
		$count = $db->get_value("SELECT COUNT(tid) as count FROM $pcvaluetable WHERE ifrecycle=0");
	}

	if ($count > 0) {
		$page < 1 && $page = 1;
		$numofpage = ceil($count/$db_perpage);
		if ($numofpage && $page > $numofpage) {
			$page = $numofpage;
		}
		$pages = numofpage($count,$page,$numofpage,"$admin_file?adminjob=postcate&action=postcate&pcid=$pcid&newfield=$newfield&step=$step&");
		if ($step != 'search') {
			$start = ($page-1)*$db_perpage;
			$limit = pwLimit($start,$db_perpage);
		}
		$catedb = array();
		$query = $db->query("SELECT pv.tid,t.fid,t.subject,t.author,t.authorid,t.postdate FROM $pcvaluetable pv LEFT JOIN pw_threads t ON pv.tid=t.tid WHERE 1 AND ifrecycle=0 $sql ORDER BY t.postdate DESC $limit");
		while ($rt = $db->fetch_array($query)) {
			$rt['postdate'] = get_date($rt['postdate']);
			$catedb[] = $rt;
		}
	}

	include PrintEot('postcate');exit;

} elseif ($_POST['sendmsg'] || $action == 'sendmsg') {
	InitGP(array('step','nexto'));
	if (empty($step)) {
		InitGP(array('selid','alltids'));

		if ($selid) {
			$selid = pwImplode($selid);
		} elseif ($alltids) {
			$alltids = explode(',',$alltids);
			$selid = pwImplode($alltids);
		} else {
			adminmsg('operate_error',"$basename&action=postcate");
		}

		$uids = '';
		$query = $db->query("SELECT authorid FROM pw_threads WHERE tid IN($selid) GROUP BY authorid");
		while ($rt = $db->fetch_array($query)) {
			$uids .= $uids ? ','.$rt['authorid'] : $rt['authorid'];
		}
		include PrintEot('postcate');exit;
	} elseif ($step == '2') {
		InitGP(array('subject','atc_content','uids'));
		$cache_file = D_P."data/bbscache/".substr(md5($admin_pwd),10,10).".txt";
		if (!$nexto) {
			writeover($cache_file,$atc_content);
		} else {
			$atc_content = readover($cache_file);
		}

		if (empty($subject) || empty($atc_content)) {
			adminmsg('sendmsg_empty','javascript:history.go(-1);');
		}

		$subject     = Char_cv($subject);
		$sendmessage = Char_cv($atc_content);
		$percount = 1;
		empty($nexto) && $nexto = 1;

		$uids = explode(',',$uids);
		$count = count($uids);

		if ($uids) {
			$msg_a = array();
			
			$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
			$members = $userService->getByUserIds($uids);
			foreach ($members as $member) {
				@extract($member);
				$sendmessage = str_replace("\$email",$email,$atc_content);
				$sendmessage = str_replace("\$windid",$username,$sendmessage);
				M::sendNotice(array($username),array('title' => $subject,'content' => $sendmessage));
			}
		}
		$havesend = $nexto*$percount;
		if ($count > ($nexto*$percount)) {
			$nexto++;
			$j_url = "$basename&action=$action&step=2&nexto=$nexto&subject=".rawurlencode($subject);
			adminmsg("sendmsg_step",EncodeUrl($j_url),1);
		} else {
			P_unlink($cache_file);
			adminmsg('operate_success',"$basename&action=postcate");
		}
	}

} elseif ($action == 'delthreads') {

	InitGP(array('selid'));
	
	!$selid && adminmsg('operate_error');
	is_numeric($selid) && $selid = explode(',',$selid);
	$delids = array();
	foreach ($selid as $key => $value) {
		if (is_numeric($value)) {
			$delids[] = $value;
		}
	}

	!$delids && Showmsg('operate_error');

	$delarticle = L::loadClass('DelArticle', 'forum');
	$readdb = $delarticle->getTopicDb('tid ' . $delarticle->sqlFormatByIds($delids));
	$delarticle->delTopic($readdb, $db_recycle);

	if ($db_ifpwcache ^ 1) {
		$db->update("DELETE FROM pw_elements WHERE type !='usersort' AND id IN(" . pwImplode($delids) . ')');
	}

	# $db->update("DELETE FROM pw_threads WHERE tid IN ($selids)");
	# ThreadManager
    $threadManager = L::loadClass("threadmanager", 'forum');
	$threadManager->deleteByThreadIds($fid,$selids);

	P_unlink(D_P.'data/bbscache/c_cache.php');

	adminmsg('operate_success',"$basename&action=postcate&pcid=$pcid");

} elseif ($action == 'editpostcate') {
	define('AJAX',1);
	if (empty($_POST['step'])) {
		
		$ajax_basename_editpostcate = EncodeUrl($basename."&action=editpostcate&");

		extract($db->get_one("SELECT pcid,name,ifable,vieworder FROM pw_postcate WHERE pcid=".pwEscape($pcid)));
		ifcheck($ifable,'ifable');

		include PrintEot('postcate');ajax_footer();
	} elseif ($_POST['step'] == 2) {
		InitGP(array('ifable','vieworder'),'P',2);
		$name = GetGP('name');
		$name = trim($name);
		if(!$name || strlen($name) > 14) adminmsg('topic_name');
		$name = char_cv($name);
		$db->update("UPDATE pw_postcate"
			. " SET " . pwSqlSingle(array(
					'name'		=> $name,			'ifable'	=> $ifable,
					'vieworder'	=> $vieworder
					))
			. " WHERE pcid=".pwEscape($pcid));

		updatecache_postcate();
		Showmsg('topic_edit_success');
	}
} elseif ($action == 'postcatelist') {
	InitGP(array('selid','vieworder'));

	!is_array($selid) && $selid = array();
	$updatedb = array();
	foreach ($selid as $key => $value) {
		if (is_numeric($key)) {
			$key = (int)$key;
			$updatedb[] = $key;
		}
	}
	if ($updatedb) {
		$db->update("UPDATE pw_postcate SET ifable=1 WHERE pcid IN (".pwImplode($updatedb).')');
		$db->update("UPDATE pw_postcate SET ifable=0 WHERE pcid NOT IN (".pwImplode($updatedb).')');
	} else {
		$db->update("UPDATE pw_postcate SET ifable=0");
	}

	foreach ($vieworder as $key => $value) {
		$key && $db->update("UPDATE pw_postcate SET vieworder=".pwEscape($value)."WHERE pcid=".pwEscape($key));
	}
	updatecache_postcate();
	adminmsg('operate_success',$basename);
} elseif ($action == 'editmodel') {

	InitGP(array('step'),GP,2);
	$ajax_basename_add = EncodeUrl($basename."&action=addfield");
	if (empty($step)) {
		@include_once(D_P.'data/bbscache/postcate_config.php');
		$ajax_basename = EncodeUrl($basename);
		$ajax_basename_edit = EncodeUrl($basename."&action=editfield");
		$ajax_basename_delfield = EncodeUrl($basename."&action=delfield");
		$ajax_basename_editindex = EncodeUrl($basename."&action=editindex");

		$pcid = $db->get_value("SELECT pcid FROM pw_postcate WHERE pcid=".pwEscape($pcid));
		empty($pcid) && adminmsg('postcate_not_exists');

		$query = $db->query("SELECT * FROM pw_pcfield WHERE pcid=".pwEscape($pcid)." ORDER BY vieworder,fieldid ASC");
		while ($rt = $db->fetch_array($query)){
			$rt['ifable_checked'] = $rt['ifable'] ? 'checked' : '';
			$rt['ifsearch_checked'] = $rt['ifsearch'] ? 'checked' : '';
			$rt['ifasearch_checked'] = $rt['ifasearch'] ? 'checked' : '';
			$rt['threadshow_checked'] = $rt['threadshow'] ? 'checked' : '';
			$rt['ifmust_checked'] = $rt['ifmust'] ? 'checked' : '';
			$rt['rules'] = unserialize($rt['rules']);
			if ($rt['ifsearch'] || $rt['ifasearch']) {
				$rt['ifindex'] = 1;
			}

			//获取字段的索引状态
			if (in_array($rt['type'],array('textarea','url','image','upload'))) {
				$rt['indexstate'] = '-1';
			} else {
				$fieldname = $rt['fieldname'];
				$rt['indexstate'] = 0;
				$query2 = $db->query("SHOW KEYS FROM $pcvaluetable");
				while($rt2 = $db->fetch_array($query2)) {
					$fieldname == $rt2['Column_name'] && $rt['indexstate'] = 1;
				}
			}
			list($rt['name1'],$rt['name2']) = explode('{#}',$rt['name']);
			$fielddb[$rt['fieldid']] = $rt;
		}

		include PrintEot('postcate');exit;
	} elseif ($step == '2') {

		InitGP(array('ifable','vieworder','ifsearch','ifasearch','threadshow','ifmust','textsize'));
		foreach ($vieworder as $key => $value) {
			$field = array();
			$field = array_keys($value);
			$fieldname = $field['0'];
			$viewvalue = $value[$fieldname];
			$db->update("UPDATE pw_pcfield SET ".pwSqlSingle(array('ifable'=>$ifable[$key]))." WHERE fieldid=".pwEscape($key)."AND ifdel=0");
			$db->update("UPDATE pw_pcfield SET ".pwSqlSingle(array( 'vieworder'=>$viewvalue,'ifsearch'=>$ifsearch[$key],'ifasearch'=>$ifasearch[$key],'threadshow'=>$threadshow[$key],'ifmust'=>$ifmust[$key],'textsize'=>$textsize[$key]))." WHERE fieldid=".pwEscape($key));
		}
		adminmsg("operate_success",$basename."&action=editmodel&pcid=".$pcid);
	} elseif ($step == '3') {

		L::loadClass('postcate', 'forum', false);
		$pwpost = array();
		$postCate = new postCate($pwpost);
		$topichtml = $postCate->getCateHtml($pcid);
		include PrintEot('postcate');exit;

	}

} elseif ($action == 'addfield')  {
	define('AJAX',1);

	if (!$_POST['step']) {

		$ajax_basename_add = EncodeUrl($basename."&action=addfield");
		include PrintEot('postcate');
		ajax_footer();

	} elseif ($_POST['step'] == 2) {

		InitGP(array('fieldtype','name','rule_min','rule_max','rules','descrip'));

		if (empty($fieldtype)) Showmsg('fieldtype_not_exists');
		if ($fieldtype == 'select' || $fieldtype == 'radio' || $fieldtype == 'checkbox') {
			$temp_rules = explode("\n",$rules);

			foreach ($temp_rules as $key => $value) {
				$rule_value[] = substr($value,strpos($value,'=')+1);
			}
			$s_rules = serialize(explode("\n",$rules));
		} elseif ($fieldtype == 'number') {
			if (!$rule_min && $rule_max || $rule_min && !$rule_max) Showmsg('field_number_numerror');
			$rule_min > $rule_max && Showmsg('field_number_error');
			$rule_value = array('minnum' => $rule_min,'maxnum' => $rule_max);
			$s_rules = serialize(array('minnum' => $rule_min,'maxnum' => $rule_max));
		} else {
			$rule_value = $s_rules = '';
		}
		if (strlen($descrip) > 255) {
			Showmsg('field_descrip_limit');
		}

		$db->update("INSERT INTO pw_pcfield SET ".pwSqlSingle(array('name'=>$name,'pcid' => $pcid,'type'=>$fieldtype,'rules'=>$s_rules,'descrip'=>$descrip)));
		$fieldid = $db->insert_id();
		$fieldname = 'field'.$fieldid;

		$db->update("UPDATE pw_pcfield SET fieldname=".pwEscape($fieldname)." WHERE fieldid=".pwEscape($fieldid));

		/*$ckfieldname = $db->get_one("SHOW COLUMNS FROM $pcvaluetable LIKE '$fieldname'");
		if ($ckfieldname) {
			Showmsg('field_have_exists');
		} else {
			$sql = getFieldSqlByType($fieldtype);
			$db->query("ALTER TABLE $pcvaluetable ADD $fieldname $sql");
		}*/
		$sql = getFieldSqlByType($fieldtype);
		$db->query("ALTER TABLE $pcvaluetable ADD $fieldname $sql");
		Showmsg('pcfield_add_success');
	}

} elseif ($action == 'editfield') {
	define('AJAX',1);
	if (!$_POST['step']) {
		$ajax_basename_edit = EncodeUrl($basename."&action=editfield");
		InitGP(array('fieldid'));
		if (empty($fieldid)) Showmsg('field_not_select');
		$fielddb = $db->get_one("SELECT name,fieldname,rules,type,descrip,ifdel FROM pw_pcfield WHERE fieldid=".pwEscape($fieldid));

		$count = $db->get_value("SELECT COUNT(*) FROM $pcvaluetable WHERE ".$fielddb['fieldname']." != ''");//查找是否变量已有值
		if ($count || $fielddb['ifdel']) $ifhidden = '1';
		if (in_array($fielddb['fieldname'],array('objecter','payway'))) $areaifhidden = '1';

		$rules = unserialize($fielddb['rules']);
		$type = $fielddb['type'];
		if ($type == 'number') {
			$minnum = $rules['minnum'];
			$maxnum = $rules['maxnum'];

		} elseif($type == 'select' || $type == 'radio' || $type == 'checkbox') {
			foreach($rules as $key => $value) {
				$rule_content .= ($rule_content ? "\r\n" : '').$value;
			}
		}
		include PrintEot('postcate');ajax_footer();
	} elseif ($_POST['step'] == 2) {
		InitGP(array('fieldtype','name','rule_min','rule_max','rules','fieldid','descrip'));
		if (empty($fieldid)) Showmsg('field_not_select');
		if (empty($fieldtype)) Showmsg('fieldtype_not_exists');

		if ($fieldtype == 'select' || $fieldtype == 'radio' || $fieldtype == 'checkbox') {
			$temp_rules = explode("\n",$rules);
			foreach ($temp_rules as $key => $value) {
				$rule_value[] = substr($vlaue,strpos($value,'='));
			}
			$s_rules = serialize(explode("\n",$rules));
		} elseif ($fieldtype == 'number') {
			if (!$rule_min && $rule_max || $rule_min && !$rule_max) Showmsg('field_number_numerror');
			$rule_min > $rule_max && Showmsg('field_number_error');
			$rule_value = array('minnum' => $rule_min,'maxnum' => $rule_max);
			$s_rules = serialize(array('minnum' => $rule_min,'maxnum' => $rule_max));
		} else {
			$rule_value = $s_rules = '';
		}

		//判断该字段是否有数据或者是默认字段，如有数据不可更改字段类型
		$fielddb = $db->get_one("SELECT type,ifdel,fieldname FROM pw_pcfield WHERE fieldid=".pwEscape($fieldid));

		if ($fieldtype != $fielddb['type']) {
			$count = $db->get_value("SELECT COUNT(*) FROM $pcvaluetable WHERE ".$fielddb['fieldname']." != ''");
			if ($count || $fielddb['ifdel']) Showmsg('can_not_modify_field_type');
		}

		$db->update("UPDATE pw_pcfield SET ".pwSqlSingle(array('name'=>$name,'type'=>$fieldtype,'rules'=>$s_rules,'descrip'=>$descrip))." WHERE fieldid=".pwEscape($fieldid));

		Showmsg('pcfield_edit_success');
	}
} elseif ($action == 'delfield') {
	define('AJAX',1);
	InitGP(array('fieldid'));
	$ckfield = $db->get_one("SELECT fieldid,pcid,fieldname,ifdel FROM pw_pcfield WHERE fieldid=".pwEscape($fieldid));

	if ($ckfield['fieldid'] && !$ckfield['ifdel']) {

		$fieldname = $ckfield['fieldname'];
		$db->update("DELETE FROM pw_pcfield WHERE fieldid=".pwEscape($fieldid));
		$ckfield2 = $db->get_one("SHOW COLUMNS FROM $pcvaluetable LIKE '$fieldname'");
		if ($ckfield2) {
			$db->query("ALTER TABLE $pcvaluetable DROP $fieldname");
		} else {
			echo "fail";
		}
		echo "success\t$fieldid";
	} else {
		echo "fail";
	}
	ajax_footer();

} elseif ($action == 'editindex') {
	define('AJAX',1);
	InitGP(array('type','fieldid'));

	$fielddb = $db->get_one("SELECT * FROM pw_pcfield WHERE fieldid=".pwEscape($fieldid));
	$fieldname = $fielddb['fieldname'];

	$field = $db->get_one("SHOW COLUMNS FROM $pcvaluetable LIKE ".pwEscape($fieldname));
	if (empty($fielddb) || empty($field)) {
		Showmsg('field_not_exists');
	}
	if (in_array($fielddb['type'],array('textarea','url','image','upload'))) {
		Showmsg('field_cannot_modify_index');
	}
	$fieldindex = 0;
	$query = $db->query("SHOW KEYS FROM $pcvaluetable");
	while($rt = $db->fetch_array($query)){
		$fieldname == $rt['Column_name'] && $fieldindex = 1;
	}
	if ($type == 'add') {
		if ($fieldindex) {
			Showmsg('field_key_have_exists');
		} else {
			$db->query("ALTER TABLE $pcvaluetable ADD INDEX ($fieldname)");
		}
	} else {
		if (empty($fieldindex)) {
			Showmsg('field_key_not_exists');
		} else {
			$db->query("ALTER TABLE $pcvaluetable DROP INDEX $fieldname");
		}
	}
	echo "success\t$pcid";ajax_footer();
} elseif ($action == 'rightset') {

	if (!$_POST['step']){
		@include_once(D_P.'data/bbscache/postcate_config.php');
		!$pcid && $pcid = $db->get_value("SELECT pcid FROM pw_postcate ORDER BY vieworder");
		$postcate = $db->get_one("SELECT * FROM pw_postcate WHERE pcid=".pwEscape($pcid));

		$query = $db->query("SELECT gid,gptype,grouptitle FROM pw_usergroups");
		while($rt = $db->fetch_array($query)) {
			$groupdb[$rt['gid']] = $rt;
		}

		include PrintEot('postcate');exit;
	} else {
		InitGP(array('viewright','adminright'));

		$viewrights = ','.implode(',',$viewright).',';
		$adminrights = ','.implode(',',$adminright).',';
		$db->update("UPDATE pw_postcate"
			. " SET " . pwSqlSingle(array(
					'viewright'		=> $viewrights,			'adminright'	=> $adminrights
				   
					))
			. " WHERE pcid=".pwEscape($pcid));

	
		updatecache_postcate();
		adminmsg('operate_success',$basename."&action=rightset&pcid=$pcid");
	}
}

function getFieldSqlByType($type) {
	if (in_array($type,array('number','calendar'))) {
		$sql = "INT(10) UNSIGNED NOT NULL default '0'";
	} elseif (in_array($type,array('radio','select'))){
		$sql = "TINYINT(3) UNSIGNED NOT NULL default '0'";
	} elseif ($type == 'textarea') {
		$sql = "TEXT NOT NULL";
	} else {
		$sql = "VARCHAR(255) NOT NULL";
	}
	return $sql;
}

function getSearchHtml($data) {
	global $vieworder_mark;
	list($name1,$name2) = explode('{#}',$data['name']);

	if ($data['vieworder'] == 0) {
		$searchhtml .= "<span>";
		$searchhtml .= $name1 ? $name1."：" : '';
	} elseif ($data['vieworder'] != 0) {
		if ($vieworder_mark != $data['vieworder']) {
			if ($vieworder_mark != 0) $searchhtml .= "</span>";
			$searchhtml .= "<span>";
			$searchhtml .= $name1 ? $name1."：" : '';
		} elseif ($vieworder_mark == $data['vieworder']) {
			$searchhtml .= $name1 ? $name1 : '';
		}
	}

	if (in_array($data['type'],array('radio','select'))) {
		$searchhtml .= "<select name=\"field[$data[fieldid]]\" class=\"input\"><option value=\"\"></option>";
		foreach($data['rules'] as $sk => $sv){
			$sv_value = substr($sv,0,strpos($sv,'='));
			$sv_name = substr($sv,strpos($sv,'=')+1);
			$selected = '';
			if ($sv_value == $data['fieldvalue']) $selected = 'selected';
			$searchhtml .= "<option value=\"$sv_value\" $selected>$sv_name</option>";
		}
		$searchhtml .= "</select>";
	} elseif ($data['type'] == 'checkbox') {
		foreach($data['rules'] as $ck => $cv){
			$cv_value = substr($cv,0,strpos($cv,'='));
			$cv_name = substr($cv,strpos($cv,'=')+1);
			$checked = '';
			if (strpos(",".implode(",",$data['fieldvalue']).",",",".$cv_value.",") !== false) $checked = 'checked';
			$searchhtml .= "<input type=\"checkbox\" class=\"input\" name=\"field[$data[fieldid]][]\" value=\"$cv_value\" $checked/> $cv_name ";
		}
	} elseif ($data['type'] == 'calendar') {
		$searchhtml .= "<input id=\"calendar_start_$data[fieldid]\" type=\"text\" class=\"input\" name=\"field[$data[fieldid]][start]\" value=\"{$data[fieldvalue][start]}\" onclick=\"ShowCalendar(this.id,1)\"/> - <input id=\"calendar_end_$data[fieldid]\" type=\"text\" class=\"input\" name=\"field[$data[fieldid]][end]\" value=\"{$data[fieldvalue][end]}\" onclick=\"ShowCalendar(this.id,1)\"/><script language=\"JavaScript\" src=\"js/date.js\"></script>";
	} elseif ($data['type'] == 'range') {
		$searchhtml .= "<input type=\"text\" size=\"5\" class=\"input\" name=\"field[$data[fieldid]][min]\" value=\"{$data[fieldvalue][min]}\"/> - <input type=\"text\" size=\"5\" class=\"input\" name=\"field[$data[fieldid]][max]\" value=\"{$data[fieldvalue][max]}\"/>";
	} else {
		$searchhtml .= "<input type=\"text\" name=\"field[$data[fieldid]]\" class=\"input\" value=\"$data[fieldvalue]\">";
	}

	if ($data['vieworder'] == 0) {
		$searchhtml .= $name2."</span>";
	} elseif ($data['vieworder'] != 0) {
		$searchhtml .= $name2;
		$vieworder_mark = $data['vieworder'];
	}
	return $searchhtml;
}
?>