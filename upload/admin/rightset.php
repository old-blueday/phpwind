<?php
!defined('P_W') && exit('Forbidden');

S::gp(array('gid'),'GP',2);
if (!$action) {
	$setdb = array(); $unsetdb = '';
	$query = $db->query("SELECT g.gid,g.grouptitle,p.rvalue as allowadmincp FROM pw_usergroups g LEFT JOIN pw_permission p ON p.uid='0' AND p.fid='0' AND p.gid=g.gid AND p.rkey='allowadmincp' WHERE g.gptype IN ('system','special')");
	while ($rt = $db->fetch_array($query)) {
		if ($rt['allowadmincp']) {
			$setdb[$rt['gid']] = $rt['grouptitle'];
		} else {
			$unsetdb .= '<option value="'.$rt['gid'].'">'.$rt['grouptitle'].'</option>';
		}
	}
	$db->free_result($query);
	include PrintEot('rightset');exit;
} elseif ($action=='edit') {
	if ($_POST['step']!=2) {
		!$gid && adminmsg('rightset_setgroup');
		require GetLang('left');
		//* include_once pwCache::getPath(D_P.'data/bbscache/level.php');
		pwCache::getData(D_P.'data/bbscache/level.php');

		$right = $db->get_value('SELECT value FROM pw_adminset WHERE gid='.S::sqlEscape($gid));
		if (!$right || !(is_array($right = unserialize($right)))) {
			$right = array();
		}
		
		foreach ($nav_left['mode']['items'] as $key=>$value) {
			$nav_left[$key] = $value;
		}
		unset($nav_left['mode']);
		foreach ($nav_left as $cate=>$left) {
			foreach ($left['items'] as $key=>$value) {
				if (is_array($value)) {
					foreach ($value['items'] as $k=>$v) {
						$nav_left[$cate]['items'][$v] = $purview[$v][0];
						unset($nav_left[$cate]['items'][$k]);
					}
					unset($nav_left[$cate]['items'][$key]);
				} else {
					$nav_left[$cate]['items'][$value] = $purview[$value][0];
						unset($nav_left[$cate]['items'][$key]);
				}
			}
		}
		$editset = $checkvar = '';
		foreach ($nav_left as $title => $left) {
			$checkvar .= ",'chk_$title' : true";
			$editset .= '<tr class="tr1 vt"><td class="td1"><a style="cursor:pointer" onclick="CheckForm(getObj(\''.$title.'\'))">'.$left['name'].'</a></td><td class="td2" id="'.$title.'"><ul class="list_A list_160" style="width:100%;">';
			foreach ($left['items'] as $key => $value) {
				if($value){
					$checked = (!empty($right) && (int)$right[$key]==1) ? 'CHECKED' : '';
					$editset .= ' <li><input type="checkbox" name="rightdb['.$key.']" value="1" '.$checked.'> '.$value.'</li>';
				}
			}
			$editset .= "</ul></td></tr>";
		}
		$checkvar && $checkvar = substr($checkvar,1);
		include PrintEot('rightset');exit;
	} else {
		S::gp(array('rightdb'),'P',2);
		!$gid && adminmsg('undefined_action');
		if (!empty($rightdb) && is_array($rightdb)) {
			$right = array();
			$_modes = array('o', 'area', 'app', 'house', 'cms', 'dianpu', 'wot', 'photos', 'diary', 'groups', 'weibo');
			foreach ($rightdb as $key => $value) {
				if (!in_array($key, array('photos_manage', 'diary_manage', 'groups_manage', 'weibo_manage','photos_set', 'diary_set', 'groups_set', 'weibo_set', 'kmd_set', 'medal_manage'))) list($k1,$k2) = explode('_',$key);
				if (in_array((string)$k1, $_modes)) {
					$right[$key] = $value;
				} else {
					if ($k2) {
					$right[$k1][$k2] = $value;
					} else {
						$right[$key] = $value;
					}
				}
			}
			$rightdb = addslashes(serialize($right));
		} else {
			adminmsg('rightset_empty',$basename.'&action=edit&gid='.$gid);
		}
		$ckid = $db->get_value('SELECT gid FROM pw_adminset WHERE gid='.S::sqlEscape($gid));
		if ($ckid) {
			$db->update('UPDATE pw_adminset SET value='.S::sqlEscape($rightdb).' WHERE gid='.S::sqlEscape($gid));
		} else {
			$db->update('INSERT INTO pw_adminset SET '.S::sqlSingle(array('gid' => $gid,'value' => $rightdb)));
		}
		$db->update("REPLACE INTO pw_permission SET uid='0',fid='0',gid=".S::sqlEscape($gid).",rkey='allowadmincp',type='system',rvalue='1'");
		updatecache_g($gid);
		adminmsg('operate_success',$basename.'&action=edit&gid='.$gid);
	}
} elseif ($action=='delete') {
	if ($_POST['step']!=2) {
		$inputmsg = '<input name="step" type="hidden" value="2" /><input name="action" type="hidden" value="delete" /><input name="gid" type="hidden" value="'.$gid.'" />';
		pwConfirm('rightset_delgroup',$inputmsg);
	} else {
		!$gid && adminmsg('rightset_setgroup');
		$db->update("REPLACE INTO pw_permission SET uid='0',fid='0',gid=".S::sqlEscape($gid).",rkey='allowadmincp',type='system',rvalue='0'");
		updatecache_g($gid);
		adminmsg('operate_success');
	}
} else {
	ObHeader($basename);
}
?>