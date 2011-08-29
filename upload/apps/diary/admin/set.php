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
		$maxuploadsize = @ini_get('upload_max_filesize');

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
}
?>