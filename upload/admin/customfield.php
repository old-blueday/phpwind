<?php
!function_exists('adminmsg') && exit('Forbidden');
$basename="$admin_file?adminjob=customfield";

if(empty($action)){
	$customfielddb=array();
	$query = $db->query("SELECT * FROM pw_customfield ORDER BY vieworder");
	while($rt = $db->fetch_array($query)){
		$customfielddb[]=$rt;
	}
	include PrintEot('customfield');exit;
} elseif($action == 'add'){
	if(!$_POST['step']){
		$rt = array();
		$state_1		= 'checked';
		$required_0		= 'checked';
		$viewinread_0	= 'checked';
		$editable_0		= 'checked';
		include PrintEot('customfield');exit;
	} else{
		InitGP(array('title','descrip','state','vieworder','maxlen','required','viewinread','editable','groups','options'),'P');
		if(!$title){
			adminmsg('operate_fail');
		}
		if($type == '3' && !$options){
			adminmsg('options_error');
		}
		$viewright = '';
		if($groups){
			foreach($groups as $key=>$val){
				if(is_numeric($val)){
					$viewright .= $viewright ? ','.$val : $val;
				}
			}
		}
		$db->update("INSERT INTO pw_customfield"
			. " SET ".pwSqlSingle(array(
				'title'		=> $title,
				'maxlen'	=> $maxlen,
				'vieworder'	=> $vieworder,
				'type'		=> $type,
				'state'		=> $state,
				'required'	=> $required,
				'viewinread'=> $viewinread,
				'editable'	=> $editable,
				'descrip'	=> $descrip,
				'viewright'	=> $viewright,
				'options'	=> $options
		)));
		$id=$db->insert_id();
		$colums=$db->get_one("SHOW COLUMNS FROM pw_memberinfo LIKE 'field_$id'");
		if($colums['Field']!='field_'.$id){
			$db->query("ALTER TABLE pw_memberinfo ADD field_$id VARCHAR(255) NOT NULL");
		}
		updatecache_field();
		adminmsg('operate_success');
	}
} elseif($action=='edit'){
	InitGP(array('id'));
	if(!$_POST['step']){
		$rt = $db->get_one("SELECT * FROM pw_customfield WHERE id=".pwEscape($id));
		if(!$rt){
			adminmsg('fieldid_error');
		}
		${'state_'.$rt['state']}			= 'checked';
		${'required_'.$rt['required']}		= 'checked';
		${'viewinread_'.$rt['viewinread']}	= 'checked';
		${'editable_'.$rt['editable']}		= 'checked';
		${'type_'.$rt['type']}				= 'selected';

		$groups = explode(',',$rt['viewright']);
		foreach($groups as $key=>$val){
			${'viewright_'.$val}='checked';
		}
		include PrintEot('customfield');exit;
	} else{
		InitGP(array('title','descrip','state','vieworder','maxlen','required','viewinread','editable','groups','options'),'P');
		$viewright = '';
		if($groups){
			foreach($groups as $key=>$val){
				if(is_numeric($val)){
					$viewright .= $viewright ? ','.$val : $val;
				}
			}
		}
		$db->update("UPDATE pw_customfield"
			. " SET " . pwSqlSingle(array(
					'title'		=> $title,
					'maxlen'	=> $maxlen,
					'vieworder'	=> $vieworder,
					'type'		=> $type,
					'state'		=> $state,
					'required'	=> $required,
					'viewinread'=> $viewinread,
					'editable'	=> $editable,
					'descrip'	=> $descrip,
					'viewright'	=> $viewright,
					'options'	=> $options
				))
			. " WHERE id=".pwEscape($id));
		updatecache_field();
		adminmsg('operate_success');
	}
} elseif($_POST['action']=='del'){
	InitGP(array('selid'),'P');
	if(!$selids = checkselid($selid)){
		$basename="javascript:history.go(-1);";
		adminmsg('operate_error');
	}
	$dropfield = '';
	foreach($selid as $key=>$val){
		if(is_numeric($val)){
			$colums=$db->get_one("SHOW COLUMNS FROM pw_memberinfo LIKE ".pwEscape('field_'.$val));
			if($colums['Field']=='field_'.$val){
				$dropfield .= $dropfield ? ",DROP field_$val" : "DROP field_$val";
			}
		}
	}
	if($dropfield){
		$db->query("ALTER TABLE pw_memberinfo $dropfield");
	}
	$db->update("DELETE FROM pw_customfield WHERE id IN($selids)");
	updatecache_field();
	adminmsg('operate_success');
}
?>