<?php
!function_exists('adminmsg') && exit('Forbidden');
$basename = "$admin_file?adminjob=help";

S::gp(array('hid'),'GP',2);
//* @include_once pwCache::getPath(D_P.'data/bbscache/help_cache.php');
pwCache::getData(D_P.'data/bbscache/help_cache.php');
if ($action=='add' || $action=='edit') {
	S::gp(array('hup'),'GP',2);
	if ($_POST['step']!='2') {
		$helpslt = '';
		$action=='add' && !$hup && $hup = $hid;
		foreach ($_HELP as $value) {
			$add = $slted = '';
			if ($value['lv'] >= 3) continue;
			for ($i=0;$i<$value['lv'];$i++) {
				$add .= '&nbsp;&nbsp;';
			}
			$hup>0 && $value['hid']==$hup && $slted = 'SELECTED';
			$helpslt .= "<option value=\"$value[hid]\" $slted>$add$value[title]</option>";
		}
		$title = $content = $vieworder = $url = '';
		if ($action!='add') {
			@extract($_HELP[$hid]);
			if ($ifcontent) {
				@extract($db->get_one("SELECT content FROM pw_help WHERE hid=".S::sqlEscape($hid)));
				$content = htmlspecialchars($content);
			}
		}
	} else {
		S::gp(array('content'),'P',0);
		S::gp(array('vieworder','title','url'),'P');
		$title	= trim($title);
		$url	= trim($url);
		$content = str_replace(
			array("\t","\r",'  '),
			array('&nbsp; &nbsp; ','','&nbsp; '),
			trim($content)
		);
		empty($title) && ($hup == 0 ? adminmsg('top_help_empty', $basename . '&action=add') : adminmsg('help_empty', $basename . '&action=add&hup=' . $hup));
		$lv = 0;
		$fathers = '';
		$vieworder = (int)$vieworder;
		if ($action=='add') {
			foreach ($_HELP as $key => $value) {
				strtolower($title)==strtolower($value['title']) && adminmsg('help_title');
				if ($key==$hup) {
					$lv = $value['lv']+1;
					$lv > 3 && adminmsg('help_maxlv');
					$fathers = ($value['fathers'] ? "$value[fathers]," : '').$hup;
					!$value['ifchild'] && $db->update("UPDATE pw_help SET ifchild='1' WHERE hid=".S::sqlEscape($hup));
				}
			}
			$db->update("INSERT INTO pw_help"
				. " SET " . S::sqlSingle(array(
					'hup'		=> $hup,
					'lv'		=> $lv,
					'fathers'	=> $fathers,
					'title'		=> $title,
					'url'		=> $url,
					'content'	=> $content,
					'vieworder'	=> $vieworder
			)));
		} else {
			$hid==$hup && adminmsg('hup_error1');
			$_HELP[$hid]['hup']!=$hup && strpos(",{$_HELP[$hup][fathers]},",",$hid,")!==false && adminmsg('hup_error2');
			foreach ($_HELP as $key => $value) {
				$key!=$hid && strtolower($title)==strtolower($value['title']) && adminmsg('help_title');
			}
			$db->update("UPDATE pw_help"
				. " SET " . S::sqlSingle(array(
						'hup'		=> $hup,
						'title'		=> $title,
						'url'		=> $url,
						'content'	=> $content,
						'vieworder'	=> $vieworder
					))
				. " WHERE hid=".S::sqlEscape($hid)
			);
		}
		updatecache_help();
		adminmsg('operate_success');
	}
} elseif ($action=='update') {
	S::gp(array('selid'),'P',2);
	foreach ($selid as $key => $value) {
		$value!=$_HELP[$key]['vieworder'] && $db->update("UPDATE pw_help SET vieworder=".S::sqlEscape($value).'WHERE hid='.S::sqlEscape((int)$key));
	}
	updatecache_help();
	adminmsg('operate_success');
} elseif ($action=='delete' && $hid > 0) {
	if ($_POST['step']!='2') {
		$dtitle = $_HELP[$hid]['title'];
	} else {
		$db->update("DELETE FROM pw_help WHERE hid=".S::sqlEscape($hid).'OR hup='.S::sqlEscape($hid));
		updatecache_help();
		adminmsg('operate_success');
	}
} else {
	$listdb = $fathers = array();
	$lv = isset($_HELP[$hid]['lv']) ? $_HELP[$hid]['lv']+1 : 0;
	$nav = '';
	if ($_HELP[$hid]['title']) {
		$_HELP[$hid]['fathers'] && $fathers = explode(',',$_HELP[$hid]['fathers']);
		foreach ($fathers as $key) {
			$nav .= " &raquo; <a href=\"$basename&hid={$_HELP[$key][hid]}\"><b>{$_HELP[$key][title]}</b></a>";
		}
		$nav .= " &raquo; <b>{$_HELP[$hid][title]}</b>";
	}
	foreach ($_HELP as $key => $value) {
		if ($hid>0 && strpos(",$value[fathers],",",$hid,")===false) {
			continue;
		}
		if ($lv+2>$value['lv']) {
			$value['add'] = '';
			for ($i=$lv;$i<$value['lv'];$i++) {
				$value['add'] = '<i class="lower"></i>';
			}
			$listdb[$key] = array('hid' => $value['hid'],'hup' => $value['hup'],'fathers' => $value['fathers'],'ifchild' => $value['ifchild'],'order' => $value['vieworder'],'title' => $value['title'],'add' => $value['add'],'lv' => $value['lv']);
		}
	}
	unset($_HELP);
}
include PrintEot('help');exit;
?>