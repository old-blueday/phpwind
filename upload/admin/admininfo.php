<?php
!function_exists('adminmsg') && exit('Forbidden');

$basename = "$admin_file?adminjob=admin";

if (defined('CE')) {
	include_once(R_P.'admin/c_update.php');
}

$warnid = $mid = $u = 0;

if ($job == 'memo') {
	list($mmid,$mcontent) = $db->get_one("SELECT mid,content FROM pw_memo WHERE isuser='0' AND username=".S::sqlEscape($admin_name),MYSQL_NUM);
	$mmid = (int)$mmid;
	$content = S::escapeChar($_POST['content']);
	if (!$mmid) {
		$pwSQL = S::sqlSingle(array(
			'username'	=> $admin_name,
			'postdate'	=> $timestamp,
			'content'	=> $content,
			'isuser'	=> '0'
		));
		$db->update('INSERT INTO pw_memo SET '.$pwSQL);
	} elseif ($mmid==(int)$_POST['mid'] && $mcontent!=$content) {
		$pwSQL = S::sqlSingle(array(
			'postdate'	=> $timestamp,
			'content'	=> $content
		));
		$db->update("UPDATE pw_memo SET $pwSQL WHERE mid=".S::sqlEscape($mmid));
	}
	$job = '';
}
if (!$job) {
	$content = '';
	@extract($db->get_one('SELECT mid,content FROM pw_memo WHERE isuser=0 AND username='.S::sqlEscape($admin_name)));
	$content && $content = str_replace('<br />',"\n",$content);
}
if (!$job || $job == 'notice' || $job == 'desktop') {

	$cachetext = explode("\r\n",substr(readover(D_P.'data/bbscache/admin_cache.php'),12));
	list($cachetime,$pw_size,$o_size,$dbversion,$max_upload,$max_ex_time,$sys_mail,$totalmember,$threads,$posts,$hits, $yposts) = explode('|',$cachetext[0]);
	if ($timestamp>$cachetime) {
		require_once(R_P.'admin/table.php');
		list($tabledb) = N_getTabledb();
		$pw_size = $o_size = 0;
		$query = $db->query('SHOW TABLE STATUS');
		while ($rt = $db->fetch_array($query)) {
			if (in_array($rt['Name'],$tabledb)) {
				$pw_size += $rt['Data_length']+$rt['Index_length']+0;
			} else {
				$o_size  += $rt['Data_length']+$rt['Index_length']+0;
			}
		}
		$o_size		 = number_format($o_size/(1024*1024),2);
		$pw_size	 = number_format($pw_size/(1024*1024),2);
		$dbversion	 = $db->server_info();
		$max_upload  = ini_get('file_uploads') ? ini_get('upload_max_filesize') : 'Disabled';
		$max_ex_time = intval(ini_get('max_execution_time')).' seconds';
		if ($sendmail_path = ini_get('sendmail_path')) {
			$sys_mail = 'Unix Sendmail ( Path: '.$sendmail_path.')';
		} elseif ($SMTP = ini_get('SMTP')) {
			$sys_mail = 'SMTP ( Server: '.$SMTP.')';
		} else {
			$sys_mail = 'Disabled';
		}
		@extract($db->get_one('SELECT totalmember,yposts FROM pw_bbsinfo WHERE id=1'));
		@extract($db->get_one('SELECT SUM(topic) AS threads,SUM(article) AS posts FROM pw_forumdata'));
		$hits = $db->get_value('SELECT SUM(hits) FROM pw_threads');
		$cachetime = $timestamp+60*60*12;
		/** writeover(D_P.'data/bbscache/admin_cache.php',"<?php die;?>$cachetime|$pw_size|$o_size|$dbversion|$max_upload|$max_ex_time|$sys_mail|$totalmember|$threads|$posts|$hits|$yposts\r\n{$cachetext[1]}"); **/
		pwCache::setData(D_P.'data/bbscache/admin_cache.php',"<?php die;?>$cachetime|$pw_size|$o_size|$dbversion|$max_upload|$max_ex_time|$sys_mail|$totalmember|$threads|$posts|$hits|$yposts\r\n{$cachetext[1]}");
	}
	$altertime	= gmdate('Y-m-d H:i',$timestamp+$db_timedf*3600);
	$systemtime	= $db_cvtime==0 ? $altertime : gmdate('Y-m-d H:i',time()+$db_timedf*3600);
	$sysversion = PHP_VERSION;
	$sysos      = str_replace('PHP/'.$sysversion,'',S::getServer('SERVER_SOFTWARE'));
	$ifcookie   = isset($_COOKIE) ? 'SUCCESS' : 'FAIL';
}
if (!$job || $job == 'desktop') {
	if (/*$admin_gid=='3' ||*/ S::inArray($admin_name,$manager)) {
		$u = 1;
		if (pwWritable(D_P.'data/sql_config.php')) {
			$warnid += 1;
		}
		if (is_dir('data')) {
			$warnid += 2;
		}
		if (ini_get('register_globals')) {
			$warnid += 4;
		}
		if (file_exists('admin.php')) {
			$warnid += 8;
		}
		if (!$db_ifsafecv || strpos($db_safegroup,',3,')===false || strpos($db_safegroup,',4,')===false || strpos($db_safegroup,',5,')===false) {
			$warnid += 16;
		}
		if ($pw_size > 500) {
			$warnid += 32;
		} elseif ($pw_size > 300) {
			$warnid += 64;
		}
	}
	$sltlv = '';
	if ($rightset['level']) {
		foreach ($ltitle as $key => $value) {
			$sltlv .= '<option value="'.$key.'">'.$value.'</option>';
		}
	}

	//* include_once pwCache::getPath(D_P.'data/bbscache/forumcache.php');
	pwCache::getData(D_P.'data/bbscache/forumcache.php');
	$sysinfo = array();
	if ($admin_gid == '3') {
		$cachetext = explode("\r\n",substr(readover(D_P.'data/bbscache/admin_cache.php'),12));
		list($cachetime,$sysinfo) = explode('|',$cachetext[1]);
		if ($timestamp > $cachetime) {
			$sysinfo = array();
			$query = $db->query("SELECT COUNT(*) as num,type FROM pw_forums GROUP BY type");
			while ($rt = $db->fetch_array($query)) {
				$sysinfo[$rt['type']] = $rt['num'];
				$sysinfo['forumnum'] += $rt['num'];
			}
			$sysinfo['M3'] = $db->get_value("SELECT COUNT(*) FROM pw_administrators WHERE groupid in (3) OR groups LIKE ('%,3,%')");
			$sysinfo['M4'] = $db->get_value("SELECT COUNT(*) FROM pw_administrators WHERE groupid in (4) OR groups LIKE ('%,4,%')");
			$sysinfo['M5'] = $db->get_value("SELECT COUNT(*) FROM pw_administrators WHERE groupid in (5) OR groups LIKE ('%,5,%')");
			$sysinfo['M7'] = $db->get_value("SELECT COUNT(*) AS sum FROM pw_members WHERE groupid='7'");
			//$sysinfo['yz'] = $db->get_value("SELECT COUNT(*) AS sum FROM pw_members WHERE yz>1");
			$sysinfo['bwd'] = $db->get_value("SELECT COUNT(*) FROM pw_filter WHERE state = '0'");
			$sysinfo['sharelinks'] = $db->get_value("SELECT COUNT(*) FROM pw_sharelinks WHERE ifcheck=0");
			$sysinfo['report'] = $db->get_value("SELECT COUNT(*) FROM pw_report WHERE state=0");
			require_once(R_P.'admin/table.php');
			list($tabledb) = N_getTabledb();
			$sysinfo['pw_size'] = $sysinfo['o_size'] = 0;
			$query = $db->query('SHOW TABLE STATUS');
			while ($rt = $db->fetch_array($query)) {
				if (in_array($rt['Name'],$tabledb)) {
					$sysinfo['pw_size'] += $rt['Data_length']+$rt['Index_length']+0;
				} else {
					$sysinfo['o_size']  += $rt['Data_length']+$rt['Index_length']+0;
				}
			}
			$sysinfo['o_size']	 = number_format($sysinfo['o_size']/(1024*1024),2);
			$sysinfo['pw_size']	 = number_format($sysinfo['pw_size']/(1024*1024),2);
			$cachetext[1] = serialize($sysinfo);
			$cachetime = $timestamp+60*60;
			/** writeover(D_P.'data/bbscache/admin_cache.php',"<?php die;?>{$cachetext[0]}\r\n$cachetime|{$cachetext[1]}"); **/
			pwCache::setData(D_P.'data/bbscache/admin_cache.php',"<?php die;?>{$cachetext[0]}\r\n$cachetime|{$cachetext[1]}");
		} else {
			$sysinfo = unserialize($sysinfo);
		}
		$fids = array();
		foreach ($forum as $key => $value) {
			$fids[] = $key;
		}
		if ($fids) {
			$sql = "fid IN(" . S::sqlImplode($fids) . ")";
			$sysinfo['tcheck'] = $db->get_value("SELECT COUNT(*) FROM pw_threads WHERE $sql AND ifcheck='0'");
		} else {
			$sysinfo['tcheck'] = 0;
		}
		$sysinfo['pcheck'] = 0;
		if ($db_plist && count($db_plist)>1) {
			foreach ($db_plist as $key => $val) {
				if ($key == 0) continue;
				$pw_posts = GetPtable($key);
				$sysinfo['pcheck'] += $db->get_value("SELECT COUNT(*) FROM $pw_posts WHERE ifcheck='0'");
			}
		} else {
			$sysinfo['pcheck'] = $db->get_value("SELECT COUNT(*) FROM pw_posts WHERE ifcheck='0'");
		}

	} elseif ($admin_gid == '4' || $admin_gid == '5') {

		if ($rightset['setforum']) {
			if ($admin_gid=='5') {
				list($allowfid,$forumcache) = GetAllowForum($admin_name);
				$sql = $allowfid ? "fid IN($allowfid)" : 0;
			} else {
				list($hidefid,$hideforum) = GetHiddenForum();
				$sql = $hidefid ? "fid NOT IN($hidefid)" : '1';
			}
		} else {
			$forumcache = '';
			$sql = '';
		}

		if ($sql) {
			$sysinfo['tcheck'] = $db->get_value("SELECT COUNT(*) FROM pw_threads WHERE $sql AND ifcheck='0'");
			$sysinfo['pcheck'] = 0;
			if ($db_plist && count($db_plist)>1) {
				foreach ($db_plist as $key => $val) {
					if ($key == 0) continue;
					$pw_posts = GetPtable($key);
					$sysinfo['pcheck'] += $db->get_value("SELECT COUNT(*) FROM $pw_posts WHERE ifcheck='0' AND $sql");
				}
			} else {
				$sysinfo['pcheck'] = $db->get_value("SELECT COUNT(*) FROM pw_posts WHERE ifcheck='0' AND $sql");
			}
		} else {
			$sysinfo['tcheck'] = 0;
			$sysinfo['pcheck'] = 0;
		}
		$sysinfo['report'] = $db->get_value("SELECT COUNT(*) FROM pw_report WHERE state=0");
	}
	$lastinfo = $slog =array();
	$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
	$userdb = $userService->getByUserName($admin_name);
	if ($userdb) {
		$uid = $userdb['uid'];
		$slog = $db->get_value("SELECT slog FROM pw_administrators WHERE uid=".S::sqlEscape($uid,false));
		$slog = explode(";",$slog);
		$slog = array_reverse($slog);
		foreach ($slog as $key=>$value) {
			!$value && $value = $timestamp.','.$onlineip;
			list($lastinfo[$key]['time'],$lastinfo[$key]['ip']) = explode(',',$value);
			$lastinfo[$key]['time'] = get_date($lastinfo[$key]['time']);
		}
	}

	if ($db_adminrecord == '1') {
		$bbscrecordfile = D_P."data/bbscache/adminrecord.php";
		$recorddb = readlog($bbscrecordfile);
		$recorddb = array_reverse($recorddb);
		for($i=0; $i<=4; $i++){
			$detail=explode("|",$recorddb[$i]);
			if($detail[1] && $detail[3] && $detail[4] && $detail[6]){
				$winddate=get_date($detail[4]);
				$detail[6]=htmlspecialchars($detail[6]);
				$adlogfor.="
<tr class=\"tr1\">
<td class=\"td2\"><a href='$admin_file?adminjob=usermanage&adminitem=edit&action=search&schname=$detail[1]&schname_s=1' onclick=\"openNewUrl('usermanage','用户管理',this.href);return false;\">$detail[1]</a></td>
<td class=\"td2\">$detail[3]</td>
<td class=\"td2\">$winddate</td>
<td class=\"td2\">$detail[6]</td>
</tr>";
			}
		}
	}
}
if ($job == 'shortcut') {

	require GetLang('left');
	foreach ($nav_left['mode']['items'] as $key=>$value) {
		$nav_left[$key] = $value;
	}
	unset($nav_left['mode']);
	foreach ($nav_left as $cate=>$left) {
		foreach ($left['items'] as $key=>$value) {
			if (is_array($value)) {
				foreach ($value['items'] as $k=>$v) {
					if (adminRightCheck($v)) {
						$nav_left[$cate]['items'][$v] = strip_tags($purview[$v][0]);
					}
					unset($nav_left[$cate]['items'][$k]);
				}
				unset($nav_left[$cate]['items'][$key]);
			} else {
				if (adminRightCheck($value)) {
					$nav_left[$cate]['items'][$value] = $purview[$value][0];
				}
				unset($nav_left[$cate]['items'][$key]);
			}
		}
	}
	$poweredby = true;
}
$poweredby || $job == 'desktop' && $poweredby = true;
require_once PrintEot('admin');//afooter();

?>