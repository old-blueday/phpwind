<?php
!function_exists('adminmsg') && exit('Forbidden');
$basename="$admin_file?adminjob=pwcode";

if(!$action){
	$codedb = array();
	$query  = $db->query("SELECT * FROM pw_windcode");
	while($rt = $db->fetch_array($query)){
		$codedb[] = $rt;
	}
	include PrintEot('pwcode');exit;
} elseif($action=='add'){
	if(!$_POST['step']){
		$s_1 = 'selected';
		$r_0_0 = $r_1_0 = $r_2_0 = 'checked';
		include PrintEot('pwcode');exit;
	} else{
		S::gp(array('name','icon','pattern','param','title','descrip'));
		S::gp(array('replace'),'P',0);
		$pattern = implode("\t",$pattern);
		$db->update("INSERT INTO pw_windcode"
			. " SET " . S::sqlSingle(array(
				'name'		=> $name,
				'icon'		=> $icon,
				'pattern'	=> $pattern,
				'replacement'=> $replace,
				'param'		=> $param,
				'title'		=> $title,
				'descrip'	=> $descrip
		)));
		//updatecache_wcode();
		updatecache_c();
		adminmsg("operate_success");
	}
} elseif($action=='edit'){
	if(!$_POST['step']){
		S::gp(array('id'),'GP',2);
		$rt = $db->get_one("SELECT * FROM pw_windcode WHERE id=".S::sqlEscape($id));
		${'s_'.$rt['param']} = 'selected';
		$p = explode("\t",$rt['pattern']);
		for($i=0;$i<3;$i++){
			$s = (int)$p[$i];
			${'r_'.$i.'_'.$s} = 'checked';
		}
		include PrintEot('pwcode');exit;
	} else{
		S::gp(array('id','name','icon','pattern','param','title','descrip'));
		S::gp(array('replace'),'P',0);
		$pattern = implode("\t",$pattern);
		$db->update("UPDATE pw_windcode"
			. " SET " . S::sqlSingle(array(
					'name'		=> $name,
					'icon'		=> $icon,
					'pattern'	=> $pattern,
					'replacement'=> $replace,
					'param'		=> $param,
					'title'		=> $title,
					'descrip'	=>$descrip
				))
			. " WHERE id=".S::sqlEscape($id));
		//updatecache_wcode();
		updatecache_c();
		adminmsg("operate_success");
	}
} elseif($_POST['action']=='submit'){
	S::gp(array('selid','icon'));
	$delids = checkselid($selid);
	if($delids){
		$db->update("DELETE FROM pw_windcode WHERE id IN($delids)");
	}
	adminmsg('operate_success');
}
?>