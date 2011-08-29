<?php
!function_exists('adminmsg') && exit('Forbidden');
empty($adminitem) && $adminitem = 'basic';
$jobUrl = "$admin_file?adminjob=editer";
$basename="$admin_file?adminjob=editer&adminitem=$adminitem";
S::gp('step');
if ($adminitem == 'basic') {
	if ($step != 2){
		$db_cvtimes = (int) $db_cvtimes;
		$db_windpost['size'] = (int) $db_windpost['size'];
		$db_windpost['picwidth'] = (int) $db_windpost['picwidth'];
		$db_windpost['picheight'] = (int) $db_windpost['picheight'];
		ifcheck($db_tcheck, 'tcheck');
		ifcheck($db_pwcode, 'pwcode');
		ifcheck($db_setform, 'setform');
		ifcheck($db_autoimg, 'autoimg');
		ifcheck($db_windmagic, 'windmagic');
		ifcheck($db_replysendmail, 'replysendmail');
		ifcheck($db_replysitemail, 'replysitemail');
		ifcheck($db_windpost['pic'], 'windpost_pic');
		ifcheck($db_windpost['mpeg'], 'windpost_mpeg');
		ifcheck($db_windpost['flash'], 'windpost_flash');
		ifcheck($db_windpost['iframe'], 'windpost_iframe');
		
		include PrintEot('editer');exit;
	} else {
		S::gp('config');
		S::gp(array('windpost'), 'P', 2);
		$config['windpost'] = is_array($db_windpost) ? $db_windpost : array();
		is_array($windpost) && $config['windpost'] = array_merge($config['windpost'],$windpost);
		saveConfig();
		adminmsg('operate_success');
	}
} elseif ($adminitem == 'setform') {
	//预设帖子格式
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
			S::gp(array('name','value','descipt'),'P');
			(!$name || !$value) && adminmsg('setform_empty');
			$setform = array();
			foreach($value as $k=>$v){
				$setform[] = array($v,$descipt[$k]);
			}
			$setform = addslashes(serialize($setform));
			$db->update("INSERT INTO pw_setform"
				. " SET " . S::sqlSingle(array(
					'name'	=> $name,
					'ifopen'=> 1,
					'value'	=> $setform
			)));
			updatecache_form();
			adminmsg("operate_success");
		}
	} elseif($action=='edit'){
		if(!$_POST['step']){
			S::gp(array('id'));
			@extract($db->get_one("SELECT name,value FROM pw_setform WHERE id=".S::sqlEscape($id)));
			!$name && adminmsg('operate_error');
			$setform = unserialize($value);
			$num     = count($setform);
			include PrintEot('setform');exit;
		} else{
			S::gp(array('id','name','value','descipt'),'P');
			(!$name || !$value) && adminmsg('setform_empty');
			$setform = array();
			foreach($value as $k=>$v){
				$setform[] = array($v,$descipt[$k]);
			}
			$setform = serialize($setform);
			$db->update("UPDATE pw_setform SET".S::sqlSingle(array('name'=>$name,'value'=>$setform))."WHERE id=".S::sqlEscape($id));
			updatecache_form();
			adminmsg("operate_success");
		}
	} elseif($action == 'ifopen'){
		S::gp(array('selid'),'P');
		$db->update("UPDATE pw_setform SET ifopen='0'");
		if($selid = checkselid($selid)){
			$db->update("UPDATE pw_setform SET ifopen='1' WHERE id IN($selid)");
		}
		updatecache_form();
		adminmsg("operate_success");
	} elseif($action=='delete'){
		S::gp(array('id'));
		$id = (int)S::getGP('id');
		$db->update("DELETE FROM pw_setform WHERE id=".S::sqlEscape($id,false));
		updatecache_form();
		adminmsg("operate_success");
	}
} elseif ($adminitem == 'pwcode') {
	//自定义代码格式
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
} elseif ($adminitem == 'commonsmile') {
		if (empty($action)) {
			$facedb = array();
			//type=0:使用的分类  type=-1：未启用的分类
			$query  = $db->query("SELECT * FROM pw_smiles WHERE type=0 OR type=-1 ORDER BY vieworder");
			$maxOrder = 1;
			while($postcache=$db->fetch_array($query)){
				$facedb[]=$postcache;
				if ($postcache['vieworder']>=$maxOrder) $maxOrder = $postcache['vieworder']+1;
			}
			$shownum = count($facedb);
			@extract($db->get_one("SELECT db_value AS fc_shownum FROM  pw_config WHERE db_name='fc_shownum'"));
			include PrintEot('postcache');exit;
		} elseif ($action == 'addface') {
			S::gp(array('path','name'),'P',1);
			if (empty($path) || !is_dir("$imgdir/post/smile/$path")) {
				adminmsg('smile_path_error');
			}
			empty($name) && adminmsg('smile_name_error');
			$rs = $db->get_one("SELECT COUNT(*) AS sum FROM pw_smiles WHERE path=".S::sqlEscape($path));
			$rs['sum']>=1 && adminmsg('smile_rename');
		
			S::gp(array('vieworder'),'P',2);
			$db->update("INSERT INTO pw_smiles"
				. " SET " . S::sqlSingle(array(
					'path'		=> $path,
					'name'		=> $name,
					'vieworder'	=> $vieworder
			)));
			$id = $db->insert_id();
			$smilepath = "$imgdir/post/smile/$path";
			$fp = opendir($smilepath);
			$picext = array("gif","bmp","jpeg","jpg","png");
			while ($smilefile = readdir($fp)) {
				$smileValue = explode(".",$smilefile);
				if ($smileValue && in_array(strtolower(end($smileValue)),$picext)) {
					$db->update("INSERT INTO pw_smiles SET ".S::sqlSingle(array('path'=>$smilefile,'type'=>$id)));
				}
			}
			closedir($fp);
			updatecache_p();
			adminmsg('operate_success');
		
		} elseif ($action == 'editsmiles') {
		
			S::gp(array('shownum','name','type'),'P');
			S::gp(array('vieworder'),'P',2);
			foreach ($vieworder as $key => $value) {
				$smilesname = $name[$key];
				$db->update("UPDATE pw_smiles"
					. " SET " . S::sqlSingle(array(
							'name'		=> $smilesname,
							'vieworder'	=> $value,
							'type'	=> in_array($key,$type) ? 0:-1
						))
					. " WHERE id=".S::sqlEscape($key)
				);
			}
			setConfig('fc_shownum', $shownum);
		
			updatecache_p();
			adminmsg('operate_success');
		
		} elseif ($action == 'delete') {
		
			S::gp(array('id'));
			$db->update("DELETE FROM pw_smiles WHERE id=".S::sqlEscape($id));
			$db->update("DELETE FROM pw_smiles WHERE type=".S::sqlEscape($id));
			updatecache_p();
			adminmsg('operate_success');
		
		} elseif ($action == 'smilemanage') {
		
			if (!$_POST['step']) {
		
				S::gp(array('id'));
				@extract($db->get_one("SELECT * FROM pw_smiles WHERE id=".S::sqlEscape($id)));
				$rs = $db->query("SELECT * FROM pw_smiles WHERE type=".S::sqlEscape($id)."ORDER BY vieworder");
				$smiles_new = $smiles_old = $smiles = array();
				$picext = array("gif","bmp","jpeg","jpg","png");
				while ($smiledb = $db->fetch_array($rs)) {
					$smiledb['src'] = "$imgpath/post/smile/$path/{$smiledb[path]}";
					$smiles_old[] = $smiledb['path'];
					$smiles[] = $smiledb;
				}
				$smilepath = "$imgdir/post/smile/$path";
				$fp = opendir($smilepath);
				$i = 0;
				while ($smilefile = readdir($fp)) {
					if (in_array(strtolower(end(explode(".",$smilefile))),$picext)) {
						if (!in_array($smilefile,$smiles_old)) {
							$i++;
							$smiles_new[$i]['path']=$smilefile;
							$smiles_new[$i]['src']="$imgpath/post/smile/$path/$smilefile";
						}
					}
				}
				closedir($fp);
				include PrintEot('postcache');exit;
		
			} else {
		
				S::gp(array('name','descipt','id'),'P');
				S::gp(array('vieworder'),'P',2);
				foreach ($vieworder as $key => $value) {
					$smilesname = $name[$key];
					$descipts	= $descipt[$key];
					$db->update("UPDATE pw_smiles"
						. " SET " . S::sqlSingle(array(
								'name'		=> $smilesname,
								'descipt'	=> $descipts,
								'vieworder'	=> $value
							))
						. " WHERE id=".S::sqlEscape($key)
					);
				}
				updatecache_p();
				adminmsg('operate_success',"$basename&action=smilemanage&id=$id");
			}
		} elseif ($action == 'addsmile') {
		
			S::gp(array('add','id'),'P');
			foreach ($add as $value) {
				$db->update("INSERT INTO pw_smiles SET ".S::sqlSingle(array('path'=>$value,'type'=>$id)));
			}
			updatecache_p();
			adminmsg('operate_success',"$basename&action=smilemanage&id=$id");
		
		} elseif ($action == 'delsmile') {
		
			S::gp(array('smileid','typeid','checkSelect'));
			if($checkSelect){
				foreach($checkSelect as $key => $v){
					$v = intval($v);
					if(!$v) continue;
					$db->update("DELETE FROM pw_smiles WHERE id=".S::sqlEscape($v));
				}
			}else{
				$smileid && $db->update("DELETE FROM pw_smiles WHERE id=".S::sqlEscape($smileid));
			}
			updatecache_p();
			adminmsg('operate_success',"$basename&action=smilemanage&id=$typeid");
		}
} elseif ($adminitem == 'specialsmile') {
	$smileService = L::loadClass('smile', 'smile'); /* @var $smileService PW_Smile */
		
		if ($action == 'addsmile') {
			S::gp(array('add'));
			if (!is_array($add) || empty($add)) adminmsg('没有选择要添加的表情');
	
			$addSmiles = array();
			$existNewSmiles = $smileService->findNewInType(0, array_keys($smileService->findByType()));
			foreach ($add as $smile) {
				if ('' == $smile['path'] || !isset($existNewSmiles[$smile['path']])) continue;
				if ('' == $smile['name']) adminmsg('表情文件 '.$smile['path'].' 的表情名称必须填写', "$basename&tab=$tab");
				$addSmiles[$smile['path']] = array('path'=>$smile['path'], 'name'=>$smile['name'], 'order'=>intval($smile['order']));
			}
			if (empty($addSmiles)) adminmsg('没有选中添加任何表情文件');
			
			if ($smileService->adds(0, $addSmiles)) {
				adminmsg('operate_success');
			} else {
				adminmsg('添加表情失败');
			}
		} elseif ($action == 'edits') {
			S::gp(array('edits'));
			
			$updateSmiles = array();
			foreach ($edits as $smileId => $smile) {
				$smileId = intval($smileId);
				if ('' == $smile['name']) adminmsg('表情文件的表情名称必须填写');
				$updateSmiles[$smileId] = array('name'=>$smile['name'], 'order'=>intval($smile['order']));
				
			}
			if (empty($updateSmiles)) adminmsg('没有更新任何表情文件');
	
			if ($smileService->updates($updateSmiles)) {
				adminmsg('operate_success');
			} else {
				adminmsg('表情信息没有更改');
			}
		} elseif ($action == 'delsmile') {
			S::gp(array('smileid','checkSelect'), 2);
			if($checkSelect){
				foreach($checkSelect as $key => $v){
					$v = intval($v);
					if(!$v || $v < 0) continue;
					$smileService->delete($v);
				}
				adminmsg('operate_success');
			}else{
				if ($smileService->delete($smileid)) {
					adminmsg('operate_success');
				} else {
					adminmsg('删除表情失败');
				}
			}
		} else {
			$smiles = $smileService->findByType();
			$newSmiles = $smileService->findNewInType(0, array_keys($smiles));
			include PrintEot('postcache');
		}
}

//functions for setform


//end functions for setform