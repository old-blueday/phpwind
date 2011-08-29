<?php
!function_exists('adminmsg') && exit('Forbidden');

//* include_once pwCache::getPath(D_P.'data/bbscache/o_config.php');
pwCache::getData(D_P.'data/bbscache/o_config.php');
require_once(R_P .'require/app_core.php');
if (empty($action)) {

	if (empty($_POST['step'])) {
		
		require_once(R_P.'require/credit.php');
		ifcheck($db_dopen,'dopen');
		ifcheck($o_diary_gdcheck,'diary_gdcheck');
		ifcheck($o_diary_qcheck,'diary_qcheck');

		$creategroup = ''; $num = 0;
		foreach ($ltitle as $key => $value) {
			if ($key != 1 && $key != 2 && $key !='6' && $key !='7' && $key !='3') {
				$num++;
				$htm_tr = $num % 4 == 0 ? '' : '';
				$g_checked = strpos($o_diary_groups,",$key,") !== false ? 'checked' : '';
				$creategroup .= "<li><input type=\"checkbox\" name=\"groups[]\" value=\"$key\" $g_checked>$value</li>$htm_tr";
			}
		}
		$creategroup && $creategroup = "<ul class=\"list_A list_120 cc\">$creategroup</ul>";

		$uploadsize = unserialize($o_uploadsize);
		$attachdir_ck[(int)$o_attachdir] = 'selected';
		
		!is_array($creditset = unserialize($o_diary_creditset)) && $creditset = array();
		
		$creditlog = array();
		!is_array($diary_creditlog = unserialize($o_diary_creditlog)) && $diary_creditlog = array();
		foreach ($diary_creditlog as $key => $value) {
			foreach ($value as $k => $v) {
				$creditlog[$key][$k] = 'CHECKED';
			}
		}
		require_once PrintApp('admin');

	} else {

		S::gp(array('config','dopen','groups','creditset','creditlog'),'GP',2);
		S::gp(array('uploadsize'),'P',2);
		
		require_once(R_P.'admin/cache.php');
		setConfig('db_dopen', $dopen);
		updatecache_c();

		$config['diary_groups'] = is_array($groups) ? ','.implode(',',$groups).',' : '';
		if(is_array($uploadsize)){
			foreach ($uploadsize as $k=>$v){
				$uploadsize[$k] = $v = intval($v);
				if($v == 0)unset($uploadsize[$k]);
			}			
		}
		$uploadsize = addslashes(serialize($uploadsize));
		$updatecache = false;
		$config['diary_creditset'] = '';
		if (is_array($creditset) && !empty($creditset)) {
			foreach ($creditset as $key => $value) {
				foreach ($value as $k => $v) {
					$creditset[$key][$k] = round($v,($k=='rvrc' ? 1 : 0));
				}
			}
			$config['diary_creditset'] = addslashes(serialize($creditset));
		}
		is_array($creditlog) && !empty($creditlog) && $config['diary_creditlog'] = addslashes(serialize($creditlog));
		foreach ($config as $key => $value) {
			if (${'o_'.$key} != $value) {
				$db->pw_update(
					'SELECT hk_name FROM pw_hack WHERE hk_name=' . S::sqlEscape("o_$key"),
					'UPDATE pw_hack SET ' . S::sqlSingle(array('hk_value' => $value, 'vtype' => 'string')) . ' WHERE hk_name=' . S::sqlEscape("o_$key"),
					'INSERT INTO pw_hack SET ' . S::sqlSingle(array('hk_name' => "o_$key", 'vtype' => 'string', 'hk_value' => $value))
				);
				$updatecache = true;
			}
		}
		if ($uploadsize) {
			$db->pw_update(
				'SELECT hk_name FROM pw_hack WHERE hk_name=' . S::sqlEscape("o_uploadsize"),
				'UPDATE pw_hack SET ' . S::sqlSingle(array('hk_value' => $uploadsize, 'vtype' => 'string')) . ' WHERE hk_name=' . S::sqlEscape("o_uploadsize"),
				'INSERT INTO pw_hack SET ' . S::sqlSingle(array('hk_name' => "o_uploadsize", 'vtype' => 'string', 'hk_value' => $uploadsize))
			);
			$updatecache = true;
		}
		$updatecache && updatecache_conf('o',true);
		adminmsg('operate_success');
	}
} elseif ($action == 'cp') {
	S::gp(array('step'));
	S::gp(array('groups','groupid','author','keyword','postdate_s','postdate_e','hits','replies','tcounts','counts','orderby','sc','perpage','direct','page', 'searchDisplay'));
	if ($step == 'delete') {
		require_once(R_P. "require/app_core.php");
		$selids = '';
		S::gp(array('selid'),'P',1);
		if (is_array($selid)) {
			foreach ($selid as $value) {
				if (is_numeric($value)) {
					$selids[] = $value;
				}
			}
			$selids = S::sqlImplode($selids);
		}
		!$selids && adminmsg('operate_error',"$basename&action=cp");
		$selids = strpos($selids,',')!==false ? "IN ($selids)" : "= $selids";
		$uids = $dids = array();
		$query = $db->query("SELECT uid,dtid,did FROM pw_diary WHERE did $selids");
		while ($rt = $db->fetch_array($query)){			
			//$db->update("DELETE FROM pw_diary WHERE did=".S::sqlEscape($rt['did']));
			pwQuery::delete('pw_diary', 'did=:did', array($rt['did']));
			$db->update("UPDATE pw_diarytype SET num=num-1 WHERE dtid=".S::sqlEscape($rt['dtid']));
			if ($affected_rows = delAppAction('diary',$rt['did'])) {
				countPosts("-$affected_rows");
			}
			$uids[] = $rt['uid'];
			$dids[] = $rt['did'];
		}
		$uids = array_unique($uids);		
		updateUserAppNum($uids,'diary','recount');
		
		//删除日志时，删除微博
		$weiboService = L::loadClass('weibo','sns'); /* @var $weiboService PW_Weibo */
		$weiboArr = $weiboService->getWeibosByObjectIdsAndType($dids,'diary');
		foreach ($weiboArr as $weibo) {
			$mids[] = $weibo['mid'];
		}
		$mids && $weiboService->deleteWeibos($mids);
		
		adminmsg('operate_success',"$basename&action=cp&step=list&groupid=$groupid&author=$author&keyword=$keyword&postdate1=$postdate1&postdate2=$postdate2&hits=$hits&replies=$replies&tcounts=$tcounts&counts=$counts&orderby=$orderby&sc=$sc&perpage=$perpage&&page=$page&");

	} else {
		$sc = $sc ? $sc : 'desc';
		$diarydb = array();
		!$perpage && $perpage = $db_perpage;
		null === $searchDisplay && $searchDisplay = 'none';
		
		if (empty($groupid) && empty($groups) && empty($author) && empty($keyword) && empty($postdate_s) && empty($postdate_s) && empty($postdate_e)) {
			$noticeMessage = getLangInfo('cpmsg', 'noenough_condition');
		} else {
		
		$sql = " WHERE 1";

		if ($groupid) {
			$groups = explode(",",$groupid);
		}
		if ($groups) {
			$groupid = implode(",",$groups);
			$sql .= " AND m.groupid IN(".S::sqlImplode($groups).")";
		}

		if ($author) {
			$authorarray = explode(",",$author);
			foreach ($authorarray as $value) {
				$value = str_replace('*','%',$value);
				$authorwhere .= " OR username LIKE ".S::sqlEscape($value,false);
			}
			$authorwhere = substr_replace($authorwhere,"",0,3);
			$authorids = array('-99');
			$query = $db->query("SELECT uid FROM pw_members WHERE $authorwhere");
			while ($rt = $db->fetch_array($query)) {
				$authorids[] = $rt['uid'];
			}
			$sql .= " AND d.uid IN(".S::sqlImplode($authorids).")";
		}

		if ($keyword) {
			$keyword = trim($keyword);
			$keywordarray = explode(",",$keyword);
			foreach ($keywordarray as $value) {
				$value = str_replace('*','%',$value);
				$keywhere .= 'OR';
				$keywhere .= " d.content LIKE ".S::sqlEscape("%$value%")."OR d.subject LIKE ".S::sqlEscape("%$value%");
			}
			$keywhere = substr_replace($keywhere,"",0,3);
			$sql .= " AND ($keywhere) ";
		}

		if ($postdate_s) {
			$date1 = PwStrtoTime($postdate_s);
			$sql.=" AND d.postdate>".S::sqlEscape($date1);
		}
		if ($postdate_e) {
			$date2  = PwStrtoTime($postdate_e);
			$sql.=" AND d.postdate<".S::sqlEscape($date2);
		}

		$hits    && $sql.=" AND d.r_num<".S::sqlEscape($hits);
		$replies && $sql.=" AND d.c_num<".S::sqlEscape($replies);
		if ($tcounts) {
			$sql .= " AND char_length(d.content)>".S::sqlEscape($tcounts);
		} elseif ($counts) {
			$sql .= " AND char_length(d.content)<".S::sqlEscape($counts);
		}
		
		$sc != 'asc' && $sc = 'desc';
		$order = /*$orderby ? " ORDER BY d.$orderby" : */" ORDER BY d.postdate $sc";
		
		(int)$page < 1 && $page = 1;
		$limit = S::sqlLimit(($page-1)*$perpage,$perpage);

		$query = $db->query("SELECT d.* FROM pw_diary d LEFT JOIN pw_members m ON d.uid=m.uid $sql $order $by $limit");
		while($rt = $db->fetch_array($query)){
			$rt['postdate'] = $rt['postdate'] ? get_date($rt['postdate']) : '-';
			$diarydb[] = $rt;
		}

		$db->free_result($query);
		@extract($db->get_one("SELECT COUNT(*) AS count FROM pw_diary d LEFT JOIN pw_members m ON d.uid=m.uid $sql"));
		if ($count > $perpage) {
			require_once(R_P.'require/forum.php');
			$pages = numofpage($count,$page,ceil($count/$perpage),"$basename&action=$action&groupid=$groupid&author=$author&keyword=$keyword&postdate1=$postdate1&postdate2=$postdate2&hits=$hits&replies=$replies&tcounts=$tcounts&counts=$counts&orderby=$orderby&sc=$sc&perpage=$perpage&searchDisplay=$searchDisplay&");
		}
		
		}
		
		$creategroup = '';
		$num = 0;
		$query = $db->query("SELECT gid,gptype,grouptitle,groupimg,grouppost FROM pw_usergroups WHERE gptype IN('system','special') ORDER BY grouppost,gid");
		while (@extract($db->fetch_array($query))) {
			$num++;
			$htm_tr = $num % 4 == 0 ? '' : '';
			$checked = in_array($gid, $groups) ? 'checked' : '';
			$creategroup .= "<li><input type=\"checkbox\" name=\"groups[]\" value=\"$gid\" $checked>$grouptitle</li>$htm_tr";
		}
		$num++;
		$htm_tr = $num % 4 == 0 ? '' : '';
		$checked = in_array(-1, $groups) ? 'checked' : '';
		$creategroup .= "<li><input type=\"checkbox\" name=\"groups[]\" value=\"-1\" $checked>普通会员组</li>$htm_tr";
		$creategroup && $creategroup = "<ul class=\"list_A list_120 cc\">$creategroup</ul>";
		
		$descChecked = $ascChecked = '';
		$sc == 'desc' && $descChecked = 'checked';
		($sc == 'asc' && !$descChecked) && $ascChecked = 'checked';
		
		require_once PrintApp('admin');
	}
}
?>