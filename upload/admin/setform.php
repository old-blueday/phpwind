<?php
!function_exists('adminmsg') && exit('Forbidden');
$basename="$admin_file?adminjob=setform";
if(!$action){
	$setformdb=array();
	$query=$db->query("SELECT * FROM pw_setform");
	while($rt=$db->fetch_array($query)){
		${'c_'.$rt['id']} = $rt['ifopen'] ? 'checked' : '';
		$setformdb[]=$rt;
	}
	include PrintEot('setform');exit;
} elseif($action=='add'){
	if(!$_POST['step']){
		$num = 0;
		include PrintEot('setform');exit;
	} else{
		InitGP(array('name','value','descipt'),'P');
		(!$name || !$value) && adminmsg('setform_empty');
		$setform = array();
		foreach($value as $k=>$v){
			$setform[] = array($v,$descipt[$k]);
		}
		$setform = addslashes(serialize($setform));
		$db->update("INSERT INTO pw_setform"
			. " SET " . pwSqlSingle(array(
				'name'	=> $name,
				'ifopen'=> 1,
				'value'	=> $setform
		)));
		updatecache_form();
		adminmsg("operate_success");
	}
} elseif($action=='edit'){
	if(!$_POST['step']){
		InitGP(array('id'));
		@extract($db->get_one("SELECT name,value FROM pw_setform WHERE id=".pwEscape($id)));
		!$name && adminmsg('operate_error');
		$setform = unserialize($value);
		$num     = count($setform);
		include PrintEot('setform');exit;
	} else{
		InitGP(array('id','name','value','descipt'),'P');
		(!$name || !$value) && adminmsg('setform_empty');
		$setform = array();
		foreach($value as $k=>$v){
			$setform[] = array($v,$descipt[$k]);
		}
		$setform = serialize($setform);
		$db->update("UPDATE pw_setform SET".pwSqlSingle(array('name'=>$name,'value'=>$setform))."WHERE id=".pwEscape($id));
		updatecache_form();
		adminmsg("operate_success");
	}
} elseif($action == 'ifopen'){
	InitGP(array('selid'),'P');
	$db->update("UPDATE pw_setform SET ifopen='0'");
	if($selid = checkselid($selid)){
		$db->update("UPDATE pw_setform SET ifopen='1' WHERE id IN($selid)");
	}
	updatecache_form();
	adminmsg("operate_success");
} elseif($action=='delete'){
	InitGP(array('id'));
	$id = (int)GetGP('id');
	$db->update("DELETE FROM pw_setform WHERE id=".pwEscape($id,false));
	updatecache_form();
	adminmsg("operate_success");
}
?>