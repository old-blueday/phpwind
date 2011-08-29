<?php
!defined('P_W') && exit('Forbidden');
include_once (D_P . 'admin/cache.php');
S::gp(array('$adminitem'));
empty($adminitem) && $adminitem = 'index';
$basename = "$admin_file?adminjob=interfacesettings&adminitem=".$adminitem;
if ($_POST['step'] != 2) {
	if ($adminitem == 'index' || $settingdb['index']) {
		${'indexfmlogo_' . (int) $db_indexfmlogo} = 'CHECKED';
		$gporder = explode(',', $db_showgroup);
		$usergroup = '';
		$num = 0;
		include_once(D_P.'data/style/'.$db_defaultstyle.'.php');
		foreach ($ltitle as $key => $value) {
			if ($key!=1 && $key!=2) {
				$num++;
				$htm_tr = $num%2 == 0 ? '' : '';
				if (in_array($key, $gporder)) {
					$g_ck = 'CHECKED';
					$g_order = array_search($key, $gporder);
				} else {
					$g_order = $g_ck = '';
				}
//				$imgdisabled = !is_file($imgdir.'/'.$stylepath.'/group/'.$lpic[$key].'.gif') ? 'DISABLED' : '';
				$usergroup .= '<li><input type="checkbox" name="gpshow[' . $key . ']" value="' . $key . '" ' . $g_ck . '> <input name="gporder[' . $key . ']" value="' . $g_order . '" class="input input_wd"> ' . $value . '</li>' . $htm_tr;
			}
		}
		$usergroup && $usergroup = ' <ul class="cc list_B list_160">' . $usergroup . '</ul>';

		ifcheck($db_setindex, 'setindex');
		ifcheck($db_todaypost, 'todaypost');
		ifcheck($db_showguest, 'showguest');
		ifcheck($db_adminshow, 'adminshow');
		ifcheck($db_indexlink, 'indexlink');
		ifcheck($db_indexonline, 'indexonline');
		ifcheck($db_ifselfshare, 'ifselfshare');
		ifcheck($db_bdayautohide, 'bdayautohide');
		ifcheck($db_indexmqshare, 'indexmqshare');
		ifcheck($db_indexshowbirth, 'indexshowbirth');
		include PrintEot('interfacesettings');exit;
	}
	if ($adminitem == 'thread' || $settingdb['thread']) {
		$hithour_sel[(int) $db_hithour] = 'SELECTED';

		$db_newtime = (int) $db_newtime;
		$db_perpage = (int) $db_perpage;
		$db_maxpage = (int) $db_maxpage;
		$db_maxtypenum = (int) $db_maxtypenum;
		ifcheck($db_threadonline, 'threadonline');
		ifcheck($db_threademotion, 'threademotion');
		ifcheck($db_threadshowpost, 'threadshowpost');
		ifcheck($db_threadsidebarifopen, 'threadsidebarifopen');
		${'hits_store_' . intval($db_hits_store)} = 'CHECKED';
		include PrintEot('interfacesettings');exit;
	}
	if ($adminitem == 'read' || $settingdb['read']) {
		require_once (R_P . 'require/credit.php');

		$db_pingtime = (int) $db_pingtime;
		$db_readperpage = (int) $db_readperpage;
		foreach ($db_floorname as $key => $value) {
			if (empty($floorname)) {
				$floorname = $key . ':' . $value;
				$sFloor = $key;
			} elseif ($key - $sFloor == 1) {
				$floorname .= " , " . $value;
				$sFloor = $key;
			} else {
				$floorname .= "\r\n" . $key . ':' . $value;
			}
		}
		//$floorname = implode("\n",$db_floorname);
		ifcheck($db_shield, 'shield');
		ifcheck($db_ipfrom, 'ipfrom');
		ifcheck($db_showonline, 'showonline');
		ifcheck($db_showcolony, 'showcolony');
		ifcheck($db_ifonlinetime, 'ifonlinetime');
		ifcheck($db_threadrelated, 'threadrelated');
		ifcheck($db_sharesite, 'sharesite');
		ifcheck($db_readinfo, 'readinfo');
		include PrintEot('interfacesettings');exit;
	}
	if ($adminitem == 'popinfo' || $settingdb['popinfo']) {
		$db_sitemsg['reg'] = implode("\n", $db_sitemsg['reg']);
		$db_sitemsg['login'] = implode("\n", $db_sitemsg['login']);
		$db_sitemsg['post'] = implode("\n", $db_sitemsg['post']);
		$db_sitemsg['reply'] = implode("\n", $db_sitemsg['reply']);
		include PrintEot('interfacesettings');exit;
	}
	if ($adminitem == 'jsinvoke' || $settingdb['jsinvoke']) {
		//S::gp(array('jsinvoke'), 'P');
		$db_jsper = (int) $db_jsper;
		ifcheck($db_jsifopen, 'jsifopen');
		include PrintEot('interfacesettings');exit;
	}
} else {
	S::gp('config');
	if ($adminitem == 'index' || $settingdb['index']) {
		S::gp(array('gpshow', 'gporder'), 'P', 2);
		if (is_array($gpshow)) {
			$showgroup = array();
			foreach ($gpshow as $key => $value) {
				$showgroup[$value] = $gporder[$key];
			}
			asort($showgroup);
			$showgroup = array_keys($showgroup);
			$config['showgroup'] = ',' . implode(',', $showgroup) . ',';
		} else {
			$config['showgroup'] = '';
		}
	}
	if ($adminitem == 'thread' || $settingdb['thread']) {
		(int) $config['perpage'] < 1 && $config['perpage'] = 25;
		
		// pw_hits_threads 和 pw_threads数据互导
		if ($db_hits_store == 1){ // 已经开启数据库缓存
			$db->update('UPDATE pw_threads t INNER JOIN pw_hits_threads h ON t.tid=h.tid SET t.hits=h.hits') || adminmsg('将pw_hits_threads数据导入pw_threads时失败');
		}else if($config['hits_store'] == 1){ // 现在开启使用数据库缓存
			$db->update('REPLACE INTO pw_hits_threads (tid,hits) (SELECT tid,hits FROM pw_threads)') || adminmsg('将pw_threads数据导入pw_hits_threads时失败');
		}
	}
	if ($adminitem == 'read' || $settingdb['read']) {
		S::gp(array('showcustom'), 'P');
		S::gp(array('floorname'), 'P');
		(int) $config['readperpage'] < 1 && $config['readperpage'] = 10;
		$config['anonymousname'] = str_replace(array('<', '>'), array('&lt;', '&gt;'), $config['anonymousname']);
		$config['showcustom'] = $showcustom ? (array) $showcustom : array();
		//$config['showcustom'] = $showcustom ? ','.implode(',',$showcustom).',' : '';


		$floorname = str_replace('，', ',', $floorname);
		if ($floorname = explode("\n", $floorname)) {
			$sFloor = 0;
			foreach ($floorname as $key => $value) {
				if ($tmpArr = explode(",", trim($value))) {
					foreach ($tmpArr as $v) {
						if (preg_match('/(\d+):(.*)/i', $v, $matches)) {
							$sFloor = $matches[1];
							$v = $matches[2];
						}
						if ($v = trim($v)) {
							$floors[$sFloor] = $v;
						}
						$sFloor++;
					}
				}
			}
		} else {
			$floors = array();
		}
		ksort($floors);
		$config['floorname'] = is_array($floors) ? $floors : array();
	}
	if ($adminitem == 'popinfo' || $settingdb['popinfo']) {
		S::gp(array('sitemsg'), 'P');
		$config['bindurl'] = trim($config['bindurl'], ',');
		$sitemsg['reg'] = explode("\n", stripslashes($sitemsg['reg']));
		$sitemsg['login'] = explode("\n", stripcslashes($sitemsg['login']));
		$sitemsg['post'] = explode("\n", stripslashes($sitemsg['post']));
		$sitemsg['reply'] = explode("\n", stripslashes($sitemsg['reply']));
		$config['sitemsg'] = is_array($sitemsg) ? $sitemsg : array();
	}
	saveConfig();	
	
	if($adminFileChanged){
		/*@fix 更改admin_file后引起的的404错误 */
		echo '<script type="text/javascript">parent.location.href = "'.$config['adminfile'].'";</script>';
	}else{
		adminmsg('operate_success');
	}
}
if($adminitem == 'overprint') {
	$overPrintClass = L::loadclass("overprint", 'forum');
	if(empty($action)){
	$relatedSelect = $overPrintClass->getRelatedSelect('');
	$isOpenSelect = $overPrintClass->getStatusSelect('');
	$iconPath = $overPrintClass->getIconPath();
	$overprints = $overPrintClass->getOverPrints(false);
	$overprintlists = array();
	foreach($overprints as $overprint){
		$list = array();
		$name = "list[".$overprint['id']."][related]";
		$isopen = "list[".$overprint['id']."][isopen]";
		$list['select'] = $overPrintClass->getRelatedSelect($overprint['related'],$name,$name);
		$list['isopen'] = ($overprint['isopen'] > 0) ? "启用" : "关闭";
		$list['open'] = ($overprint['isopen'] > 0) ? "checked" : "";
		$overprintlists[] = array_merge($overprint,$list);
	}
	
	$icons = $overPrintClass->getOverPrintIcons();
	include PrintEot('overprint');exit;
}elseif($action == "add"){
	S::gp(array('title','icon','related','isopen'));
	$title = trim($title);
	$icon = trim($icon);
	$related = intval($related);
	$isopen = in_array($isopen,array(0,1)) ? $isopen : 0;
	($title == "") && adminmsg("主题印戳 关联名称不能为空");
	($icon == "" || !$overPrintClass->checkIcon($icon)) && adminmsg("请选择主题印戳图标或图标格式不正确");
	$data = array();
	$data['title']      = $title;
	$data['icon']       = $icon;
	$data['related']    = $related;
	$data['total']      = 1;
	$data['createtime'] = time();
	$data['isopen']     = $isopen;
	$result = $overPrintClass->addOverPrint($data);
	(!$result) && adminmsg("主题印戳增加失败 ");
	adminmsg('operate_success',"$basename&action=");
}elseif($action == "manage"){
	S::gp(array('list'));
	!is_array($list) && adminmsg("提示的数据有误 ");
	$overprints = array();
	$relatedFlag =false;
	$relatedArray =array();
	foreach($list as $id=>$v){
		($v['title'] == "") && adminmsg("主题印戳 关联名称不能为空");
		($v['icon']  == "" || !$overPrintClass->checkIcon($v['icon'])) && adminmsg("请选择主题印戳图标或图标格式不正确");
		$t = array();
		$t['title']   = $v['title'];
		$t['icon']    = $v['icon'];
		$t['isopen']  = $v['check'] ? 1 : 0;
		$t['related'] = ($operate == "close") ? '-20' : $v['related'];
		if($v['related'] !=0) {
			$relatedArray[$id] =$v['related'];
		}
		$overprints[$id] = $t;
	}
	if(count($relatedArray) != count(array_unique($relatedArray))){
		$relatedFlag = true;
	}
	$relatedFlag && adminmsg("多个印戳不能关联同一个操作");
	!$overprints && adminmsg('operate_success',"$basename&action=");
	$status = ($isopen == 'open') ? 1 : 0;
	foreach($overprints as $id=>$overprint){
		$overPrintClass->updateOverPrint($overprint,$id);/*更新*/
	}
	adminmsg('operate_success',"$basename&action=");
}elseif($action == "delete"){
	S::gp(array('id'));
	($id<0) && adminmsg("主题印戳ID错误");
	$overPrintClass->deleteOverPrint($id); /*删除*/
	adminmsg('operate_success',"$basename&action=");
}else{	
}
}
?>