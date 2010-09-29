<?php
!function_exists('adminmsg') && exit('Forbidden');
$basename="$admin_file?adminjob=share";

if(empty($action)){
	InitGP(array('state'),null,2);
	$sqladd='';
	if($state=='2'){
		$sqladd="WHERE ifcheck=0";
		$state_2='selected';
	} elseif($state=='1'){
		$sqladd="WHERE ifcheck=1";
		$state_1='selected';
	} else {
		$state_0='selected';
	}
	$threaddb=array();
	$query=$db->query("SELECT * FROM pw_sharelinks $sqladd ORDER BY threadorder");
	while($share=$db->fetch_array($query)){
		strlen($share['name'])>30 && $share['name']=substrs($share['name'],30);
		strlen($share['url'])>30 && $share['url']=substrs($share['url'],30);
		strlen($share['descrip'])>30 && $share['descrip']=substrs($share['descrip'],30);
		$threaddb[]=$share;
	}
	include PrintEot('sharelink');exit;
} elseif($action=="add"){
	InitGP(array('state'),null,2);
	if(!$_POST['step']){
		include PrintEot('sharelink');exit;
	} else{
		InitGP(array('name','url','descrip','logo','threadorder','ifcheck'),'P',1);
		if(empty($name) || empty($url)){
			adminmsg('operate_fail');
		}
		$url && substr($url,0,4)!='http' && $url = "http://".$url;
		$threadorder = (int)$threadorder;
		$db->update("INSERT INTO pw_sharelinks"
			. " SET " . pwSqlSingle(array(
				'threadorder'	=> $threadorder,
				'name'			=> $name,
				'url'			=> $url,
				'descrip'		=> $descrip,
				'logo'			=> $logo,
				'ifcheck'		=> $ifcheck
		)));
		updatecache_i();
		adminmsg('operate_success');
	}
} elseif($action=="edit"){
	InitGP(array('sid','state'),null,2);
	if(!$_POST['step']){
		@extract($db->get_one("SELECT * FROM pw_sharelinks WHERE sid=".pwEscape($sid)));
		$name = str_replace(array('"',"'"),array('&quot;','&#39;'),$name);
		$descrip = str_replace(array('"',"'"),array('&quot;','&#39;'),$descrip);
		ifcheck($ifcheck,'ifcheck');
		include PrintEot('sharelink');exit;
	} else{
		InitGP(array('name','url','descrip','logo','threadorder','username','ifcheck'),'P',1);
		$descrip = str_replace(array('"',"'"),array('&quot;','&#39;'),$descrip);
		$threadorder = (int)$threadorder;
		$ifcheck = (int)$ifcheck;
		if ($ifcheck) {
			$temp = $db->get_value("SELECT ifcheck FROM pw_sharelinks WHERE sid=".pwEscape($sid));
			if (!$temp) {
				M::sendNotice(
					array($username),
					array(
						'title' => getLangInfo('writemsg','sharelink_pass_title'),
						'content' => getLangInfo('writemsg','sharelink_pass_content'),
					)
				);
			}
		}
		$url && substr($url,0,4)!='http' && $url = "http://".$url;
		$db->update("UPDATE pw_sharelinks"
			. " SET " . pwSqlSingle(array(
					'threadorder'	=> $threadorder,
					'name'			=> $name,
					'url'			=> $url,
					'descrip'		=> $descrip,
					'logo'			=> $logo,
					'username'		=> $username,
					'ifcheck'		=> $ifcheck
				))
			. " WHERE sid=".pwEscape($sid));
		updatecache_i();
		adminmsg('operate_success');
	}
} elseif($_POST['pass']){
	InitGP(array('deiaid'),'P');
	if(!$deiaid) adminmsg('operate_error');
	foreach($deiaid as $sid){
		$db->update("UPDATE pw_sharelinks SET ifcheck=1 WHERE sid=".pwEscape($sid));
	}
	$temp = array();
	$rs = $db->query("SELECT username FROM pw_sharelinks WHERE sid IN(".pwImplode($deiaid).")");
	while ($rt = $db->fetch_array($rs)) {
		$temp[] = $rt['username'];
	}
	M::sendNotice(
		$temp,
		array(
			'title' => getLangInfo('writemsg','sharelink_pass_title'),
			'content' => getLangInfo('writemsg','sharelink_pass_content'),
		)
	);
	updatecache_i();
	adminmsg('operate_success');
} elseif($_POST['unpass']){
	InitGP(array('deiaid'),'P');
	if(!$deiaid) adminmsg('operate_error');
	foreach($deiaid as $sid){
		$db->update("UPDATE pw_sharelinks SET ifcheck=0 WHERE sid=".pwEscape($sid));
	}
	updatecache_i();
	adminmsg('operate_success');
} elseif($_POST['delete']){
	InitGP(array('deiaid'),'P');
	if(!$deiaid) adminmsg('operate_error');
	foreach($deiaid as $sid){
		$db->update("DELETE FROM pw_sharelinks WHERE sid=".pwEscape($sid));
	}
	updatecache_i();
	adminmsg('operate_success');
}
?>