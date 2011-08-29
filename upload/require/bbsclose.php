<?php
!function_exists('readover') && exit('Forbidden');

$bbsclose = true;
$AdminUser = GetCookie('AdminUser');
$CK = $AdminUser ? explode("\t",StrCode(GetCookie('AdminUser'),'DECODE')) : array();
if (S::inArray($CK[1],$manager)) {
	$v_key = array_search($CK[1],$manager);
	SafeCheck($CK,PwdCode($manager_pwd[$v_key])) && $bbsclose = false;
}

if (!$db_bbsifopen) {
	if ($_GET['logined'] && !$bbsclose) {
		Cookie('logined',1,$timestamp+1800);
	} elseif (!GetCookie('logined') || $bbsclose) {
		$skin	 = $skinco ? $skinco : $db_defaultstyle;
		$groupid = '';
		Showmsg($db_whybbsclose,($bbsclose ? NULL : 'bbsclose'));
	}
} elseif ($db_bbsifopen==2) {
	if ($db_visitopen) {
		$tmpAllowvisit = false;
		if ($db_visitips && $onlineip != 'Unknown') {
			$tmpIP = ip2long($onlineip);
			if ($tmpIP != -1 && $tmpIP !== FALSE) {
				$tmpVisitips = explode(',',$db_visitips);
				foreach ($tmpVisitips as $value) {
					if (!trim($value)) continue;
					$tmpSIP = ip2long(str_replace('*','1',$value));
					$tmpEIP = ip2long(str_replace('*','255',$value));
					if ($tmpIP >= $tmpSIP && $tmpIP <= $tmpEIP) {
						$tmpAllowvisit = true; break;
					}
				}
			}
		}
		if ($tmpAllowvisit === false) {
			if (!$windid) {
				Showmsg($db_visitmsg);
			} elseif (!S::inArray($windid,$manager) && strpos($db_visitgroup,','.$groupid.',')===false && strpos(strtolower($db_visituser),','.strtolower($windid).',')===false) {
				PwNewDB();
				require_once(R_P.'require/checkpass.php');
				Loginout(); Showmsg('visiter_login');
			}
		}
	} elseif (!$windid) {
		Showmsg($db_visitmsg);
	}
}

unset($AdminUser,$CK,$bbsclose);
?>