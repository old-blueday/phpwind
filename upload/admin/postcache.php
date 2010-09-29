<?php
!function_exists('adminmsg') && exit('Forbidden');
InitGP('tab');
$basename = "$admin_file?adminjob=postcache";

if ($tab != 'write') {
	if (empty($action)) {
	
		$facedb = array();
		$query  = $db->query("SELECT * FROM pw_smiles WHERE type=0 ORDER BY vieworder");
		while($postcache=$db->fetch_array($query)){
			$facedb[]=$postcache;
		}
		$shownum = count($facedb);
		@extract($db->get_one("SELECT db_value AS fc_shownum FROM  pw_config WHERE db_name='fc_shownum'"));
	
		include PrintEot('postcache');exit;
	
	} elseif ($_POST['action'] == 'addface') {
	
		InitGP(array('path','name'),'P',1);
		if (empty($path) || !is_dir("$imgdir/post/smile/$path")) {
			adminmsg('smile_path_error');
		}
		empty($name) && adminmsg('smile_name_error');
		$rs = $db->get_one("SELECT COUNT(*) AS sum FROM pw_smiles WHERE path=".pwEscape($path));
		$rs['sum']>=1 && adminmsg('smile_rename');
	
		InitGP(array('vieworder'),'P',2);
		$db->update("INSERT INTO pw_smiles"
			. " SET " . pwSqlSingle(array(
				'path'		=> $path,
				'name'		=> $name,
				'vieworder'	=> $vieworder
		)));
		updatecache_p();
		adminmsg('operate_success');
	
	} elseif ($_POST['action'] == 'editsmiles') {
	
		InitGP(array('shownum','name'),'P');
		InitGP(array('vieworder'),'P',2);
	
		foreach ($vieworder as $key => $value) {
			$smilesname = $name[$key];
			$db->update("UPDATE pw_smiles"
				. " SET " . pwSqlSingle(array(
						'name'		=> $smilesname,
						'vieworder'	=> $value
					))
				. " WHERE id=".pwEscape($key)
			);
		}
		setConfig('fc_shownum', $shownum);
	
		updatecache_p();
		adminmsg('operate_success');
	
	} elseif ($action == 'delete') {
	
		InitGP(array('id'));
		$db->update("DELETE FROM pw_smiles WHERE id=".pwEscape($id));
		$db->update("DELETE FROM pw_smiles WHERE type=".pwEscape($id));
		updatecache_p();
		adminmsg('operate_success');
	
	} elseif ($action == 'smilemanage') {
	
		if (!$_POST['step']) {
	
			InitGP(array('id'));
			@extract($db->get_one("SELECT * FROM pw_smiles WHERE id=".pwEscape($id)));
			$rs = $db->query("SELECT * FROM pw_smiles WHERE type=".pwEscape($id)."ORDER BY vieworder");
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
	
			InitGP(array('name','descipt','id'),'P');
			InitGP(array('vieworder'),'P',2);
			foreach ($vieworder as $key => $value) {
				$smilesname = $name[$key];
				$descipts	= $descipt[$key];
				$db->update("UPDATE pw_smiles"
					. " SET " . pwSqlSingle(array(
							'name'		=> $smilesname,
							'descipt'	=> $descipts,
							'vieworder'	=> $value
						))
					. " WHERE id=".pwEscape($key)
				);
			}
			updatecache_p();
			adminmsg('operate_success',"$basename&action=smilemanage&id=$id");
		}
	} elseif ($_POST['action'] == 'addsmile') {
	
		InitGP(array('add','id'),'P');
		foreach ($add as $value) {
			$db->update("INSERT INTO pw_smiles SET ".pwSqlSingle(array('path'=>$value,'type'=>$id)));
		}
		updatecache_p();
		adminmsg('operate_success',"$basename&action=smilemanage&id=$id");
	
	} elseif ($action == 'delsmile') {
	
		InitGP(array('smileid','typeid'));
		$db->update("DELETE FROM pw_smiles WHERE id=".pwEscape($smileid));
		updatecache_p();
		adminmsg('operate_success',"$basename&action=smilemanage&id=$typeid");
	}
} else {
	$smileService = L::loadClass('smile', 'smile'); /* @var $smileService PW_Smile */
	
	if ($action == 'addsmile') {
		InitGP(array('add'));
		if (!is_array($add) || empty($add)) adminmsg('没有选择要添加的表情', "$basename&tab=$tab");

		$addSmiles = array();
		$existNewSmiles = $smileService->findNewInType(0, array_keys($smileService->findByType()));
		foreach ($add as $smile) {
			if ('' == $smile['path'] || !isset($existNewSmiles[$smile['path']])) continue;
			if ('' == $smile['name']) adminmsg('表情文件 '.$smile['path'].' 的表情名称必须填写', "$basename&tab=$tab");
			$addSmiles[$smile['path']] = array('path'=>$smile['path'], 'name'=>$smile['name'], 'order'=>intval($smile['order']));
		}
		if (empty($addSmiles)) adminmsg('没有选中添加任何表情文件', "$basename&tab=$tab");
		
		if ($smileService->adds(0, $addSmiles)) {
			adminmsg('operate_success', "$basename&tab=$tab");
		} else {
			adminmsg('添加表情失败', "$basename&tab=$tab");
		}
	} elseif ($action == 'edits') {
		InitGP(array('edits'));
		
		$updateSmiles = array();
		foreach ($edits as $smileId => $smile) {
			$smileId = intval($smileId);
			if ('' == $smile['name']) adminmsg('表情文件的表情名称必须填写', "$basename&tab=$tab");
			$updateSmiles[$smileId] = array('name'=>$smile['name'], 'order'=>intval($smile['order']));
			
		}
		if (empty($updateSmiles)) adminmsg('没有更新任何表情文件', "$basename&tab=$tab");

		if ($smileService->updates($updateSmiles)) {
			adminmsg('operate_success', "$basename&tab=$tab");
		} else {
			adminmsg('表情信息没有更改', "$basename&tab=$tab");
		}
	} elseif ($action == 'delsmile') {
		InitGP(array('smileid'), 2);
		if ($smileService->delete($smileid)) {
			adminmsg('operate_success', "$basename&tab=$tab");
		} else {
			adminmsg('删除表情失败', "$basename&tab=$tab");
		}
	} else {
		$smiles = $smileService->findByType();
		$newSmiles = $smileService->findNewInType(0, array_keys($smiles));

		include PrintEot('postcache');
	}
}
?>