<?php
!function_exists('adminmsg') && exit('Forbidden');
$basename="$admin_file?adminjob=share";
S::gp(array('action'));
if(empty($action)){
	S::gp(array('state'),null,2);
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

	$threaddb = array();
	$query=$db->query("SELECT * FROM pw_sharelinks $sqladd ORDER BY threadorder");
	while($share=$db->fetch_array($query)){
		strlen($share['name'])>30 && $share['name']=substrs($share['name'],30);
		strlen($share['url'])>30 && $share['url']=substrs($share['url'],30);
		strlen($share['descrip'])>30 && $share['descrip']=substrs($share['descrip'],30);
		$stids = $typename = array();
		$relationService = L::loadClass('SharelinksRelationService', 'site');
		$stids = $relationService->findStidBySid(intval($share['sid']));
		if ($stids) {
			foreach ($stids as $stid) {
				$typeService = L::loadClass('SharelinksTypeService', 'site');
				$types = $typeService->getTypesByStid($stid);
				$typename[] = $types['name'];
			}
		}
		$typename && ( $share['type'] = implode('、',$typename) );
		unset($typename);
		$threaddb[]=$share;
	}

	include PrintEot('sharelink');exit;
} elseif($action=="add"){
	S::gp(array('state'),null,2);
	if(!$_POST['step']){
		$typeCates = array();
		$typeService = L::loadClass('SharelinksTypeService', 'site');
		$typeCates = $typeService->getAllTypes();

		include PrintEot('sharelink');exit;
	} else{
		S::gp(array('name','url','descrip','logo','threadorder','ifcheck','stid'),'P',1);
		if(empty($name) || empty($url)){
			adminmsg('operate_fail');
		}
		$url && substr($url,0,4)!='http' && $url = "http://".$url;
		$threadorder = (int)$threadorder;
		$result = $db->update("INSERT INTO pw_sharelinks"
			. " SET " . S::sqlSingle(array(
				'threadorder'	=> $threadorder,
				'name'			=> $name,
				'url'			=> $url,
				'descrip'		=> $descrip,
				'logo'			=> $logo,
				'ifcheck'		=> $ifcheck
		)));
		if ($result) {
			$relationService = L::loadClass('SharelinksRelationService', 'site');
			$sid = $db->insert_id();
			$fieldsData['sid'] = intval($sid);
			foreach ($stid as $value) {
				$fieldsData['stid'] = intval($value);
				$relationService->insert($fieldsData);
			}
		}
		updatecache_i();
		adminmsg('operate_success');
	}
} elseif($action=="edit"){
	S::gp(array('sid','state'),null,2);
	if(!$_POST['step']){
		$typeCates = $stids = array();
		$typeService = L::loadClass('SharelinksTypeService', 'site');
		$typeCates = $typeService->getAllTypes();
		$relationService = L::loadClass('SharelinksRelationService', 'site');
		$stids = $relationService->findStidBySid($sid);

		@extract($db->get_one("SELECT * FROM pw_sharelinks WHERE sid=".S::sqlEscape($sid)));
		$name = str_replace(array('"',"'"),array('&quot;','&#39;'),$name);
		$descrip = str_replace(array('"',"'"),array('&quot;','&#39;'),$descrip);
		ifcheck($ifcheck,'ifcheck');
		include PrintEot('sharelink');exit;
	} else{
		S::gp(array('name','url','descrip','logo','threadorder','username','ifcheck','stid'),'P',1);
		$descrip = str_replace(array('"',"'"),array('&quot;','&#39;'),$descrip);
		$threadorder = (int)$threadorder;
		$ifcheck = (int)$ifcheck;
		if ($ifcheck) {
			$temp = $db->get_value("SELECT ifcheck FROM pw_sharelinks WHERE sid=".S::sqlEscape($sid));
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
		$result = $db->update("UPDATE pw_sharelinks"
			. " SET " . S::sqlSingle(array(
					'threadorder'	=> $threadorder,
					'name'			=> $name,
					'url'			=> $url,
					'descrip'		=> $descrip,
					'logo'			=> $logo,
					'username'		=> $username,
					'ifcheck'		=> $ifcheck
				))
			. " WHERE sid=".S::sqlEscape($sid));

		if ($result) {
			$relationService = L::loadClass('SharelinksRelationService', 'site');
			$sid = intval($sid);
			$fieldsData['sid'] = $sid;
			$relationService->deleteBySid($sid);
			foreach ($stid as $value) {
				$fieldsData['stid'] = intval($value);
				$relationService->insert($fieldsData);
			}
		}
		updatecache_i();
		adminmsg('operate_success');
	}
} elseif($_POST['pass']){
	S::gp(array('deiaid'),'P');
	if(!$deiaid) adminmsg('operate_error');
	foreach($deiaid as $sid){
		$db->update("UPDATE pw_sharelinks SET ifcheck=1 WHERE sid=".S::sqlEscape($sid));
	}
	$temp = array();
	$rs = $db->query("SELECT username FROM pw_sharelinks WHERE sid IN(".S::sqlImplode($deiaid).")");
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
	S::gp(array('deiaid'),'P');
	if(!$deiaid) adminmsg('operate_error');
	foreach($deiaid as $sid){
		$db->update("UPDATE pw_sharelinks SET ifcheck=0 WHERE sid=".S::sqlEscape($sid));
	}
	updatecache_i();
	adminmsg('operate_success');
} elseif($_POST['delete']){
	S::gp(array('deiaid'),'P');
	if(!$deiaid) adminmsg('operate_error');
	foreach($deiaid as $sid){
		$db->update("DELETE FROM pw_sharelinks WHERE sid=".S::sqlEscape($sid));
	}
	updatecache_i();
	adminmsg('operate_success');
} elseif($_POST['order']){
	S::gp(array('vieworder'),'P');
	foreach($vieworder as $sid=>$value){
		$db->update('UPDATE pw_sharelinks SET threadorder = '.S::sqlEscape($value).' WHERE sid= '.S::sqlEscape($sid));
	}
	updatecache_i();
	adminmsg('operate_success');
}  elseif ($action == 'types') {
	$query = L::loadClass('SharelinksTypeService', 'site');
	$typeCates = $query->getAllTypesName();
	$ajax_basename_add = EncodeUrl($basename."&action=addtype");
	include PrintEot('sharelink');exit;
} elseif ($action == 'addtype') {
	define('AJAX',1);
	S::gp(array('step'),'P');
	if (empty($step)) {
		$ajax_basename_add = EncodeUrl($basename."&action=addtype");
		$ifable_Y = 'checked';
		include PrintEot('sharelink');ajax_footer();
	} elseif ($step == 2) {
		S::gp(array('name','ifable','vieworder'),'P');
		(!$name || strlen($name) > 30) && adminmsg('type_name_long');
		$typeService = L::loadClass('SharelinksTypeService', 'site');
		$stid = $typeService->getTypeIdByName($name);
		$stid && adminmsg('type_name_exist');
		$fieldsData = array(
			'name'	    => $name,
			'ifable'	=> intval($ifable),
			'vieworder'	=> intval($vieworder)
		 );
		$typeService->insert($fieldsData);
		adminmsg('linkstype_add_success',"$basename&action=types");
	}
} elseif ($action == 'edittype') {
	S::gp(array('types'),'P');
	!is_array($types) && $types = array();
	$typeService = L::loadClass('SharelinksTypeService', 'site');
	foreach ($types as $key => $value) {
		$value['ifable'] = ($value['ifable'] > 0) ? '1' : '0';
		$typeService->update($value,$key);
	}
	adminmsg('operate_success',"$basename&action=types");
} elseif ($action == 'deltype') {
	S::gp(array('stid'),'G');
	$typeService = L::loadClass('SharelinksTypeService', 'site');
	$result = $typeService->delete($stid);
	if ($result) {
		$relationService = L::loadClass('SharelinksRelationService', 'site');
		$relationService->deleteByStid($stid);
	}
	adminmsg('operate_success',"$basename&action=types");
}

?>