<?php
!defined('P_W') && exit('Forbidden');
InitGP(array('action','step'));

if ($step == 2) {
	S::gp(array('config', 'gdcheck'), 'P', 2);
	foreach (array('groups_gdcheck','groups_p_gdcheck','diary_gdcheck','photos_gdcheck') as $value) {
		if (!isset($config[$value]) ){
			$config[$value] = 0;
		}
		setConfig("o_$value", $config[$value], null, true);
	}
	updatecache_conf('o', true);
	unset($config['groups_gdcheck'],$config['groups_p_gdcheck'],$config['diary_gdcheck']);
	$config['gdcheck'] = !empty($gdcheck) ? intval(array_sum($gdcheck)) : 0;
	saveConfig();
	adminmsg('operate_success');
} else {
	ifcheck($db_cloudgdcode, 'cloudgdcode');
	for ($i = 0; $i < 7; $i++) {
		${'gdcheck_' . $i} = ($db_gdcheck & pow(2, $i)) ? 'CHECKED' : '';
		if($i < 3) ${'ckquestion_' . $i} = ($db_ckquestion & pow(2, $i)) ? 'CHECKED' : '';
		${'gdstyle_' . $i} = ($db_gdstyle & pow(2, $i)) ? 'CHECKED' : '';
	}
	pwCache::getData(D_P.'data/bbscache/o_config.php');
	$gdcheck_groups = $o_groups_gdcheck ? 'CHECKED' : '';
	$gdcheck_p_groups = $o_groups_p_gdcheck ? 'CHECKED' : '';
	$gdcheck_diary = $o_diary_gdcheck ? 'CHECKED' : '';
	$gdcheck_photos = $o_photos_gdcheck ? 'CHECKED' : '';
}
include PrintEot('cloudcaptcha');exit;