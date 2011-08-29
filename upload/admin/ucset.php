<?php
!function_exists('adminmsg') && exit('Forbidden');

if (empty($action)) {

	//ifcheck($uc_client, 'ifcheck');
	${'ifcheck_' . intval($uc_server)} = 'checked';
	include PrintEot('ucset');exit;

} else {

	S::gp(array('uc_server'), 'P', 2);
	
	setConfig('uc_server', $uc_server);
	
	if ($uc_server) {

		S::gp(array('uc_appid','uc_key'));
		
		if ($uc_server == '1') {
			
			$uc_appid = 1;
			!$uc_key && $uc_key = randstr(20);
			$db->update("UPDATE pw_ucapp SET uc='0'");
			$db->pw_update(
				"SELECT * FROM pw_ucapp WHERE id=" . S::sqlEscape($uc_appid),
				"UPDATE pw_ucapp SET secretkey=" . S::sqlEscape($uc_key) . ",uc='1' WHERE id=" . S::sqlEscape($uc_appid),
				"INSERT INTO pw_ucapp SET " . S::sqlSingle(array('id' => $uc_appid, 'name' => $db_bbsname, 'siteurl' => $db_bbsurl, 'secretkey' => $uc_key, 'uc' => 1))
			);
			require_once(R_P . 'uc_client/class_core.php');
			$uc = new UC();
			$myApp = $uc->load('app');
			$myApp->checkColumns();

		} elseif ($uc_server == '2') {

			S::gp(array('uc_dbhost','uc_dbuser','uc_dbpw','uc_dbname','uc_dbpre','uc_dbcharset'));
			
			$uc_appid = intval($uc_appid);
			(!$uc_appid || $uc_appid < 2) && $uc_appid = 2;
			setConfig('uc_dbhost', $uc_dbhost);
			setConfig('uc_dbuser', $uc_dbuser);
			setConfig('uc_dbpw', $uc_dbpw);
			setConfig('uc_dbname', $uc_dbname);
			setConfig('uc_dbpre', $uc_dbpre);
			setConfig('uc_dbcharset', $uc_dbcharset);
		}
	}
	setConfig('uc_appid', $uc_appid);
	setConfig('uc_key', $uc_key);

	updatecache_c();
	adminmsg('operate_success');

}
?>