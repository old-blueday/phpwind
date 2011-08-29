<?php
!function_exists('adminmsg') && exit('Forbidden');

require_once(R_P.'require/credit.php');

if (empty($job)) {

	$basename = "$admin_file?adminjob=hack&hackset=toolcenter";

	if (!$_POST['step']) {

		ifcheck($db_toolifopen,'toolifopen');
		ifcheck($db_allowtrade,'allowtrade');
		include PrintHack('admin');exit;

	} else {

		S::gp(array('toolifopen','allowtrade'),'P');
		setConfig('db_toolifopen', $toolifopen);
		setConfig('db_allowtrade', $allowtrade);
		updatecache_c();
		adminmsg('operate_success');
	}
} elseif ($job == 'toolinfo') {

	$basename = "$admin_file?adminjob=hack&hackset=toolcenter&job=toolinfo";

	if (!$action) {

		$query = $db->query("SELECT * FROM pw_tools ORDER BY vieworder ASC");
		while ($rt = $db->fetch_array($query)) {
			!$rt['creditype'] && $rt['creditype'] = 'currency';
			$tooldb[] = $rt;
		}
		include PrintHack('admin');exit;

	} elseif ($action == 'submit') {

		S::gp(array('tools', 'vieworder'),'P');
		$toolids = array(0);
		if (is_array($tools)) {
			foreach ($tools as $key => $value) {
				is_numeric($key) && $toolids[] = $key;
			}
		}

		$query = $db->query("SELECT id,vieworder FROM pw_tools ORDER BY vieworder ASC");
		while ($rt = $db->fetch_array($query)) {
			if($rt['vieworder'] == $vieworder[$rt['id']]) continue;
			is_numeric($vieworder[$rt['id']]) && $db->update("UPDATE pw_tools SET vieworder=".
															S::sqlEscape($vieworder[$rt['id']]).
															" WHERE id =($rt[id])");
		}

		$toolids = S::sqlImplode($toolids);
		if ($toolids) {
			$db->update("UPDATE pw_tools SET state='1' WHERE id IN($toolids)");
			$db->update("UPDATE pw_tools SET state='0' WHERE id NOT IN($toolids)");
		} else {
			$db->update("UPDATE pw_tools SET state='0'");
		}
		adminmsg('operate_success');

	} elseif ($action == 'edit' || $action == 'add') {

		if (!$_POST['step']) {

			if ($action == 'edit') {
				S::gp(array('id'));
				$rt = $db->get_one("SELECT * FROM pw_tools WHERE id=" . S::sqlEscape($id));
				!$rt && adminmsg('operate_fail');
			} else {
				$rt = array();
			}
			!$rt['creditype'] && $rt['creditype'] = 'currency';
			$condition = unserialize($rt['conditions']);
			$groupids  = $condition['group'];
			$fids      = $condition['forum'];
			ifcheck($rt['state'],'state');
			${'type_' . $rt['type']} = 'checked';
			foreach ($condition['credit'] as $key => $value) {
				$key == 'rvrc' && $value /= 10;
				$condition['credit'][$key] = (int)$value;
			}
			$CreditList = '';
			foreach ($credit->cType as $key => $value) {
				$CreditList	.= "<option value=\"$key\"".($rt['creditype']==$key ? ' selected' : '').">$value</option>";
			}

			$CreditLuck = '';
			foreach ($credit->cType as $key => $value) {
				$CreditLuck	.= "<option value=\"$key\"".($condition['luck']['lucktype']==$key ? ' selected' : '').">$value</option>";
			}

			$usergroup  = "<ul class='list_A list_120'>";
			foreach ($ltitle as $key => $value) {
				if ($key != 1 && $key != 2) {
					$num++;
					$htm_tr = $num%5 == 0 ?  '' : '';
					if (strpos($groupids,','.$key.',') !== false) {
						$checked = 'checked';
					} else {
						$checked = '';
					}
					$usergroup .= " <li><input type='checkbox' name='groupids[]' value='$key' $checked>$value</li>$htm_tr";
				}
			}
			$usergroup .= "</ul>";

			$num        = 0;
			$forumcheck = "<ul class='list_A list_160' style='width:auto;'>";
			$sqladd     = " AND f_type!='hidden' AND cms='0'";
			$query      = $db->query("SELECT fid,name FROM pw_forums WHERE type<>'category' $sqladd");
			while ($fm = $db->fetch_array($query)) {
				$num ++;
				$htm_tr = $num % 5 == 0 ? '' : '';
				if (strpos($fids,','.$fm['fid'].',') !== false) {
					$checked = 'checked';
				} else {
					$checked = '';
				}
				$forumcheck .= "<li><input type='checkbox' name='fids[]' value='$fm[fid]' $checked>$fm[name]</li>$htm_tr";
			}
			$forumcheck.="</ul>";
			include PrintHack('admin');exit;

		} else{
			S::gp(array('id','name','filename','vieworder','descrip','logo','state','price','stock','groupids','fids','condition','type','creditype','rmb'),'P');
			if ($groupids) {
				$condition['group'] = ','.implode(',',$groupids).',';
			}
			if ($fids) {
				$condition['forum'] = ','.implode(',',$fids).',';
			}
			foreach ($condition['credit'] as $key => $value) {
				$key == 'rvrc' && $value *= 10;
				$condition['credit'][$key] = (int)$value;
			}
			$condition = addslashes(serialize($condition));
			if ($action == 'edit') {
				$db->update("UPDATE pw_tools SET " . S::sqlSingle(array(
					'name'		=> $name,
					'filename'	=> $filename,
					'vieworder'	=> $vieworder,
					'descrip'	=> $descrip,
					'logo'		=> $logo,
					'state'		=> $state,
					'price'		=> $price,
					'creditype'	=> $creditype,
					'rmb'		=> $rmb,
					'type'		=> $type,
					'stock'		=> $stock,
					'conditions'=> $condition
				)) . " WHERE id=" . S::sqlEscape($id));
			} else{
				$db->update("INSERT INTO pw_tools SET " . S::sqlSingle(array(
					'name'		=> $name,
					'filename'	=> $filename,
					'vieworder'	=> $vieworder,
					'descrip'	=> $descrip,
					'logo'		=> $logo,
					'state'		=> $state,
					'price'		=> $price,
					'creditype'	=> $creditype,
					'rmb'		=> $rmb,
					'type'		=> $type,
					'stock'		=> $stock,
					'conditions'=> $condition
				)));
				$id = $db->insert_id();
			}
			$basename .= "&action=edit&id=$id";

			adminmsg('operate_success');
		}
	}
} elseif ($job == 'usertool') {

	$basename = "$admin_file?adminjob=hack&hackset=toolcenter&job=usertool";

	if (!$action || $action == 'search') {

		S::gp(array('username','page'));
		if ($action == 'search' && $username) {
			$rt     = $db->get_one("SELECT uid FROM pw_members WHERE username=" . S::sqlEscape($username));
			$sqladd = "WHERE u.uid=".S::sqlEscape($rt['uid'],false);
		} else {
			$sqladd = '';
		}
		if (!is_numeric($page) || $page < 1) {
			$page = 1;
		}
		$limit = S::sqlLimit(($page-1)*$db_perpage,$db_perpage);
		$rt    = $db->get_one("SELECT COUNT(*) AS sum FROM pw_usertool u $sqladd");
		$sum   = $rt['sum'];
		$total = ceil($sum/$db_perpage);
		$pages = numofpage($sum,$page,$total,"$basename&action=search&username=".rawurlencode($username)."&");

		$tooldb= array();
		$query = $db->query("SELECT u.*,t.name,t.stock,t.price,t.creditype,m.username FROM pw_usertool u LEFT JOIN pw_members m USING(uid) LEFT JOIN pw_tools t ON t.id=u.toolid $sqladd ORDER BY uid $limit");
		while ($rt = $db->fetch_array($query)) {
			!$rt['creditype'] && $rt['creditype'] = 'currency';
			$tooldb[] = $rt;
		}
		include PrintHack('admin');exit;

	} elseif ($action == 'edit') {

		S::gp(array('uid','id'));
		(!is_numeric($uid) || !is_numeric($id)) && adminmsg('numerics_checkfailed');

		if (empty($_POST['step'])) {

			$rt = $db->get_one("SELECT u.*,t.name,t.stock,t.price,m.username FROM pw_usertool u LEFT JOIN pw_members m USING(uid) LEFT JOIN pw_tools t ON t.id=u.toolid WHERE u.uid=" . S::sqlEscape($uid) . "AND u.toolid=" . S::sqlEscape($id));
			!$rt['creditype'] && $rt['creditype'] = 'currency';
			include PrintHack('admin');exit;

		} else {

			S::gp(array('nums','sellnums','sellprice'));
			$db->update("UPDATE pw_usertool SET " . S::sqlSingle(array(
				'nums'		=> $nums,
				'sellnums'	=> $sellnums,
				'sellprice'	=> $sellprice
			)) . " WHERE uid=".S::sqlEscape($uid) . " AND toolid=".S::sqlEscape($id));
			adminmsg('operate_success');
		}
	} elseif ($action == 'del') {

		S::gp(array('uid','id'));
		(!is_numeric($uid) || !is_numeric($id)) && adminmsg('numerics_checkfailed');
		$db->update("DELETE FROM pw_usertool WHERE uid=" . S::sqlEscape($uid) . "AND toolid=".S::sqlEscape($id));
		adminmsg('operate_success');
	}
} elseif ($job == 'tradelog') {

	$basename = "$admin_file?adminjob=hack&hackset=toolcenter&job=tradelog";
	S::gp(array('username','page'));
	if ($action == 'search' && $username) {
		$rt     = $db->get_one("SELECT uid FROM pw_members WHERE username=" . S::sqlEscape($username));
		$sqladd = "AND u.uid='$rt[uid]'";
	} else {
		$sqladd = '';
	}
	if (!is_numeric($page) || $page < 1) {
		$page = 1;
	}
	$limit = S::sqlLimit(($page-1)*$db_perpage,$db_perpage);
	$rt    = $db->get_one("SELECT COUNT(*) AS sum FROM pw_usertool u WHERE sellnums!=0 $sqladd");
	$sum   = $rt['sum'];
	$total = ceil($sum/$db_perpage);
	$pages = numofpage($sum,$page,$total,"$basename&action=search&username=".rawurlencode($username)."&");

	$tooldb= array();
	$query = $db->query("SELECT u.*,t.name,t.descrip,t.logo,t.creditype,m.username FROM pw_usertool u LEFT JOIN pw_members m USING(uid) LEFT JOIN pw_tools t ON t.id=u.toolid WHERE sellnums!=0 $sqladd $limit");
	while ($rt = $db->fetch_array($query)) {
		$rt['descrip']	= substrs($rt['descrip'],45);
		!$rt['creditype'] && $rt['creditype'] = 'currency';
		$tooldb[]		= $rt;
	}
	include PrintHack('admin');exit;

} elseif ($job == 'toollog') {

	$basename = "$admin_file?adminjob=hack&hackset=toolcenter&job=toollog";

	if (empty($action)) {

		require_once(R_P.'require/bbscode.php');
		S::gp(array('page','keyword'));
		if ($keyword) {
			$sqladd = "WHERE descrip LIKE " . S::sqlEscape("%$keyword%");
		} else {
			$sqladd = '';
		}
		if (!is_numeric($page) || $page < 1) {
			$page = 1;
		}
		$limit = S::sqlLimit(($page-1)*$db_perpage,$db_perpage);
		$rt    = $db->get_one("SELECT COUNT(*) AS sum FROM pw_toollog $sqladd");
		$sum   = $rt['sum'];
		$total = ceil($sum/$db_perpage);
		$pages = numofpage($sum,$page,$total,"$basename&keyword=".rawurlencode($keyword)."&");
		$logdb = array();
		$query = $db->query("SELECT * FROM pw_toollog $sqladd ORDER BY time DESC $limit");
		while ($rt = $db->fetch_array($query)) {
			$rt['time']   = get_date($rt['time']);
			$rt['descrip']= convert($rt['descrip'],array());
			$logdb[]      = $rt;
		}
	} elseif ($action == 'del') {

		S::gp(array('selid'));
		if (!$selid = checkselid($selid)) {
			$basename = "javascript:history.go(-1);";
			adminmsg('operate_error');
		}
		$db->update("DELETE FROM pw_toollog WHERE id IN($selid)");
		adminmsg('operate_success');
	}
	include PrintHack('admin');exit;
}
?>