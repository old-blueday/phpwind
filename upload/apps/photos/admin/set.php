<?php
!function_exists('adminmsg') && exit('Forbidden');

//* @include_once pwCache::getPath(D_P.'data/bbscache/o_config.php');
pwCache::getData(D_P.'data/bbscache/o_config.php');
if (empty($action)) {

	if (empty($_POST['step'])) {

		require_once(R_P.'require/credit.php');
		!in_array($o_mkdir,array(1,2,3)) && $o_mkdir = 1;
		${'mkdir'.$o_mkdir} = 'checked';
		ifcheck($db_phopen,'phopen');
		ifcheck($o_photos_gdcheck,'photos_gdcheck');
		ifcheck($o_photos_qcheck,'photos_qcheck');
		$maxuploadsize = @ini_get('upload_max_filesize');

		$creategroup = ''; $num = 0;
		foreach ($ltitle as $key => $value) {
			if ($key != 1 && $key != 2 && $key !='6' && $key !='7' && $key !='3') {
				$num++;
				$htm_tr = $num % 4 == 0 ? '' : '';
				$g_checked = strpos($o_photos_groups,",$key,") !== false ? 'checked' : '';
				$creategroup .= "<li><input type=\"checkbox\" name=\"groups[]\" value=\"$key\" $g_checked>$value</li>$htm_tr";
			}
		}
		$creategroup && $creategroup = "<ul class=\"list_A list_120 cc\">$creategroup</ul>";
		!is_array($creditset = unserialize($o_photos_creditset)) && $creditset = array();

		$creditlog = array();
		!is_array($photos_creditlog = unserialize($o_photos_creditlog)) && $photos_creditlog = array();
		foreach ($photos_creditlog as $key => $value) {
			foreach ($value as $k => $v) {
				$creditlog[$key][$k] = 'CHECKED';
			}
		}

		require_once PrintApp('admin');

	} else {
		S::gp(array('creditset','creditlog'),'GP');
		S::gp(array('config','phopen','groups'),'GP',2);

		require_once(R_P.'admin/cache.php');
		setConfig('db_phopen', $phopen);
		updatecache_c();

		$config['photos_groups'] = is_array($groups) ? ','.implode(',',$groups).',' : '';
		$updatecache = false;

		$config['photos_creditset'] = '';
		if (is_array($creditset) && !empty($creditset)) {
			foreach ($creditset as $key => $value) {
				foreach ($value as $k => $v) {
					$creditset[$key][$k] = round($v,($k=='rvrc' ? 1 : 0));
				}
			}
			$config['photos_creditset'] = addslashes(serialize($creditset));
		}
		is_array($creditlog) && !empty($creditlog) && $config['photos_creditlog'] = addslashes(serialize($creditlog));
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
		$updatecache && updatecache_conf('o',true);
		adminmsg('operate_success');
	}
}
?>