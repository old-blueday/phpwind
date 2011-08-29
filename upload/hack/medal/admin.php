<?php
!function_exists('adminmsg') && exit('Forbidden');

if(!$action){
	if(!$_POST['step']){
		//* include_once pwCache::getPath(D_P.'data/bbscache/md_config.php');
		pwCache::getData(D_P.'data/bbscache/md_config.php');
		ifcheck($md_ifopen,'ifopen');
		ifcheck($md_ifmsg,'ifmsg');
		ifcheck($md_ifapply,'ifapply');
		require_once PrintHack('admin');
	} elseif($_POST['step']=='2'){
		S::gp(array('config','groups','appgroups'),'P');
		if(is_array($groups)){
			$config['md_groups'] = ','.implode(',',$groups).',';
		} else{
			$config['md_groups'] = '';
		}
		if(is_array($appgroups)){
			$config['md_appgroups'] = ','.implode(',',$appgroups).',';
		} else{
			$config['md_appgroups'] = '';
		}
		foreach($config as $key=>$value){
			$rt = $db->get_one("SELECT hk_name FROM pw_hack WHERE hk_name=".S::sqlEscape($key));
			if($rt){
				$db->update("UPDATE pw_hack SET hk_value=".S::sqlEscape($value)."WHERE hk_name=".S::sqlEscape($key));
			} else{
				$db->update("INSERT INTO pw_hack SET hk_name=".S::sqlEscape($key).",hk_value=".S::sqlEscape($value));
			}
		}
		updatecache_md();
		adminmsg('operate_success');
	}
} elseif($action=='edit'){
	if(!$_POST['step']){
		$query = $db->query("SELECT * FROM pw_medalinfo");
		while($rt = $db->fetch_array($query)){
			$medaldb[]=$rt;
		}
		require_once PrintHack('admin');
	} elseif($_POST['step']=='2'){
		S::gp(array('medal'),'P');
		foreach($medal as $key=>$value){
			$value['name']   = S::escapeChar($value['name']);
			$value['intro']  = S::escapeChar($value['intro']);
			$value['picurl'] = S::escapeChar($value['picurl']);
			$db->update("UPDATE pw_medalinfo"
				. " SET " . S::sqlSingle(array(
						'name'	=> $value['name'],
						'intro'	=> $value['intro'],
						'picurl'=> $value['picurl']
					))
				. " WHERE id=".S::sqlEscape($key));
		}
		$basename="$admin_file?adminjob=hack&hackset=medal&action=edit";
		updatecache_mddb();
		adminmsg('operate_success');
	}
} elseif($action=='add'){
	if(!$_POST['step']){
		require_once PrintHack('admin');
	} elseif($_POST['step']=='2'){
		S::gp(array('newname','newintro','newpicurl'),'P',1);
		$db->update("INSERT INTO pw_medalinfo"
			. " SET " . S::sqlSingle(array(
				'name'	=> $newname,
				'intro'	=> $newintro,
				'picurl'=> $newpicurl
		)));
		$basename="$admin_file?adminjob=hack&hackset=medal&action=edit";
		updatecache_mddb();
		adminmsg('operate_success');
	}
} elseif($action=='del'){
	S::gp(array('id'));
	$db->update("DELETE FROM pw_medalinfo WHERE id=".S::sqlEscape($id));
	$db->update("DELETE FROM `pw_medalslogs` WHERE level=".S::sqlEscape($id));//删除
	$basename="$admin_file?adminjob=hack&hackset=medal&action=edit";
	updatecache_mddb();
	adminmsg('operate_success');
}elseif($action=='selectimg'){
	//* require_once pwCache::getPath(D_P.'data/bbscache/medaldb.php');
	pwCache::getData(D_P.'data/bbscache/medaldb.php');
	S::gp(array('thisid'));
	$medalimgdir = H_P."/image/";
	$medalimgs	= $haveused = array();
	foreach($_MEDALDB as $value){
		$haveused[] = $value['picurl'];
	}
	$d = opendir($medalimgdir);
	while($filename = readdir($d)){
		if($filename=='.' || $filename=='..') continue;
		$fileext = end(explode('.',$filename));
		if(!in_array($fileext,array('gif','jpg','jpeg','png'))) continue;
		$isused = in_array($filename,$haveused) ? 1:0;
		$medalimgs[] = array('filename'=>$filename,'isused'=>$isused);
	}
	require_once PrintHack('selectimg');
}
?>