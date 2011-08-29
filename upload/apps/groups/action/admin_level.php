<?php
!function_exists('adminmsg') && exit('Forbidden');

S::gp(array('job'));

if (empty($job)) {
	
	$cnLevel = array();
	$query = $db->query("SELECT * FROM pw_cnlevel ORDER BY ltype,lpoint,id");
	while ($rt = $db->fetch_array($query)) {
		$cnLevel[$rt['ltype']][] = $rt;
	}
	require_once PrintApp('admin');

} elseif ($_POST['job'] == 'add') {
	
	S::gp(array('ltitle_add','ltitle','ltype'));
	S::gp(array('lpoint_add','lpoint'), 'GP', 2);

	$ltype <> 'special' && $ltype = 'common';
	
	$pwSQL = array();
	foreach ($ltitle_add as $key => $value) {
		if ($value) {
			$pwSQL[] = array(
				'ltype' => $ltype,
				'ltitle' => $value,
				'lpoint' => $ltype == 'common' ? $lpoint_add[$key] : 0
			);
		}
	}
	if ($pwSQL) {
		$db->update("INSERT INTO pw_cnlevel (ltype,ltitle,lpoint) VALUES " . S::sqlMulti($pwSQL));
	}

	foreach ($ltitle as $key => $value) {
		if ($value) {
			$db->update("UPDATE pw_cnlevel SET " . S::sqlSingle(array(
				'ltitle' => $value,
				'lpoint' => $ltype == 'common' ? $lpoint[$key] : 0
			)) . ' WHERE id=' . S::sqlEscape($key));
		}
	}
	$basename .= '&action=level';

	updateGroupLevel();
	
	adminmsg('operate_success');

} elseif ($job == 'edit') {

	S::gp(array('id'));

	$rt = $db->get_one("SELECT * FROM pw_cnlevel WHERE id=" . S::sqlEscape($id));
	empty($rt) && adminmsg('operate_success');

	if (empty($_POST['step'])) {
	
		ifcheck($rt['allowmerge'], 'allowmerge');
		ifcheck($rt['allowattorn'], 'allowattorn');
		ifcheck($rt['allowdisband'], 'allowdisband');
		ifcheck($rt['pictopic'], 'pictopic');
		ifcheck($rt['allowstyle'], 'allowstyle');

		$modeset = $rt['modeset'] ? unserialize($rt['modeset']) : array();
		$layout = $rt['layout'] ? unserialize($rt['layout']) : array();
		$topicadmin = $rt['topicadmin'] ? unserialize($rt['topicadmin']) : array();
	
		$modesetThreadOpen = $modeset['thread']['ifopen'] ? ' checked' : '';
		$modesetWriteOpen = $modeset['write']['ifopen'] ? ' checked' : '';
		$modesetActiveOpen = $modeset['active']['ifopen'] ? ' checked' : '';
		$modesetGalbumOpen = $modeset['galbum']['ifopen'] ? ' checked' : '';

		$layoutThreadOpen = $layout['thread']['ifopen'] ? ' checked' : '';
		$layoutWriteOpen = $layout['write']['ifopen'] ? ' checked' : '';
		$layoutActiveOpen = $layout['active']['ifopen'] ? ' checked' : '';
		$layoutGalbumOpen = $layout['galbum']['ifopen'] ? ' checked' : '';

		${'layoutThreadNum_' . $layout['thread']['num']} = ' selected';
		${'layoutWriteNum_' . $layout['write']['num']} = ' selected';
		${'layoutActiveNum_' . $layout['active']['num']} = ' selected';
		${'layoutGalbumNum_' . $layout['galbum']['num']} = ' selected';

		ifcheck($topicadmin['del'], 'ta_del');
		ifcheck($topicadmin['highlight'], 'ta_highlight');
		ifcheck($topicadmin['lock'], 'ta_lock');
		ifcheck($topicadmin['pushtopic'], 'ta_pushtopic');
		ifcheck($topicadmin['downtopic'], 'ta_downtopic');
		ifcheck($topicadmin['toptopic'], 'ta_toptopic');
		ifcheck($topicadmin['digest'], 'ta_digest');

		$options = '';
		$query = $db->query("SELECT id,ltitle FROM pw_cnlevel ORDER BY ltype,lpoint,id");
		while ($rs = $db->fetch_array($query)) {
			$options .= "<option value=\"{$rs['id']}\"" . ($rs['id'] == $id ? ' selected' : '') . ">{$rs['ltitle']}</option>";
		}
		require_once PrintApp('admin');

	} else {

		S::gp(array('config'));

		foreach ($config as $key => $value) {
			switch ($key) {
				case 'albumnum':
				case 'maxphotonum':
				case 'maxmember':
				case 'allowmerge':
				case 'allowattorn':
				case 'allowdisband':
				case 'pictopic':
				case 'allowstyle':
					$config[$key] = intval($value);
					break;
				case 'modeset':
					foreach ($value as $k => $v) {
						foreach ($v as $k1 => $v1) {
							$value[$k][$k1] = ($k1 == 'title') ? getModelTitle($v1, $k) : intval($v1);
						}
					}
					uasort($value, "pwLevelCmp");
					$config[$key] = serialize($value);
					break;
				case 'layout':
					foreach ($value as $k => $v) {
						foreach ($v as $k1 => $v1) {
							$value[$k][$k1] = intval($v1);
						}
					}
					uasort($value, "pwLevelCmp");
					$config[$key] = serialize($value);
					break;
				case 'topicadmin':
					foreach ($value as $k => $v) {
						foreach ($v as $k1 => $v1) {
							$value[$k][$k1] = intval($v1);
						}
					}
					$config[$key] = serialize($value);
					break;
				default:
					unset($config[$key]);
			}
		}
		$db->update("UPDATE pw_cnlevel SET " . S::sqlSingle($config) . ' WHERE id=' . S::sqlEscape($id));
		
		$basename .= '&action=level&job=edit&id=' . $id;
		adminmsg('operate_success');
	}

} elseif ($job == 'del') {

	define('AJAX', 1);
	S::gp(array('id'), 'GP', 2);

	if (empty($_POST['step'])) {
		$posthash = EncodeUrl("$basename&action=level&job=del&id=$id");
		require_once PrintApp('admin_ajax');
	} else {

		$db->update("DELETE FROM pw_cnlevel WHERE id=" . S::sqlEscape($id));
		echo "ok\t$id";
	}
	updateGroupLevel();
	ajax_footer();
} elseif ($job == 'upgrade') {

	if (empty($_POST['step'])) {
		require_once PrintApp('admin');
	} else {

		S::gp(array('upgrade'), 'GP');
		if (is_array($upgrade)) {
			foreach ($upgrade as $key => $value) {
				$upgrade[$key] = round($value, 2);
			}
		}
		$upgrade = serialize($upgrade);
		$db->pw_update(
			"SELECT hk_name FROM pw_hack WHERE hk_name='o_groups_upgrade'",
			'UPDATE pw_hack SET ' . S::sqlSingle(array('hk_value' => $upgrade, 'vtype' => 'array')) . " WHERE hk_name='o_groups_upgrade'",
			'INSERT INTO pw_hack SET ' . S::sqlSingle(array('hk_name' => "o_groups_upgrade", 'vtype' => 'array', 'hk_value' => $upgrade))
		);
		updatecache_conf('o',true);

		$basename .= '&action=level&job=upgrade';

		adminmsg('operate_success');

	}
} elseif ($job == 'sort') {
	$commomGroup = $specialGroup = array();
	$query = $db->query("SELECT COUNT(*) AS sum,commonlevel FROM pw_colonys WHERE commonlevel>0 GROUP BY commonlevel");
	while ($rt = $db->fetch_array($query)) {
		$commomGroup[$rt['commonlevel']] = $rt['sum']; 
	}

	$query = $db->query("SELECT COUNT(*) AS sum,speciallevel FROM pw_colonys WHERE speciallevel>0 GROUP BY speciallevel");
	while ($rt = $db->fetch_array($query)) {
		$specialGroup[$rt['speciallevel']] = $rt['sum']; 
	}
	 
	require_once PrintApp('admin');
}

function updateGroupLevel() {
	global $db;
	$array = $upgrade = array();
	$query = $db->query("SELECT * FROM pw_cnlevel ORDER BY ltype,lpoint,id");
	while ($rt = $db->fetch_array($query)) {
		$array[$rt['id']] = $rt['ltitle'];
		if ($rt['ltype'] == 'common') {
			$upgrade[$rt['id']] = $rt['lpoint'];
		}
	}
	$array = serialize($array);
	$upgrade = serialize($upgrade);
	$db->pw_update(
		"SELECT hk_name FROM pw_hack WHERE hk_name='o_groups_level'",
		'UPDATE pw_hack SET ' . S::sqlSingle(array('hk_value' => $array, 'vtype' => 'array')) . " WHERE hk_name='o_groups_level'",
		'INSERT INTO pw_hack SET ' . S::sqlSingle(array('hk_name' => "o_groups_level", 'vtype' => 'array', 'hk_value' => $array))
	);
	$db->pw_update(
		"SELECT hk_name FROM pw_hack WHERE hk_name='o_groups_levelneed'",
		'UPDATE pw_hack SET ' . S::sqlSingle(array('hk_value' => $upgrade, 'vtype' => 'array')) . " WHERE hk_name='o_groups_levelneed'",
		'INSERT INTO pw_hack SET ' . S::sqlSingle(array('hk_name' => "o_groups_levelneed", 'vtype' => 'array', 'hk_value' => $upgrade))
	);
	updatecache_conf('o',true);
}

function pwLevelCmp ($a, $b) {
    if ($a['vieworder'] == $b['vieworder']) return 0;
    return ($a['vieworder'] < $b['vieworder']) ? -1 : 1;
}

function getModelTitle($title, $model) {
	$lang = array(
		'thread' => '话题',
		'write' => '新鲜事',
		'active' => '活动',
		'galbum' => '相册',
		'member' => '成员'
	);
	return $title ? $title : $lang[$model];
}
?>