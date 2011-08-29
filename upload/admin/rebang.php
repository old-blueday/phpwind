<?php
!function_exists('adminmsg') && exit('Forbidden');
$basename = "$amind_file?adminjob=rebang";
	empty($action) && $action = 'rebang';
	$query = $db->query("SELECT db_name,db_value FROM pw_config WHERE db_name LIKE 'nf\_%'");
	while ($rt = $db->fetch_array($query)) {
		$rt['db_name'] = preg_replace('/[^\d\w\_]/is','',$rt['db_name']);
		${$rt['db_name']} = (array)unserialize($rt['db_value']);

	}
	if ($action == 'rebang') {
		require_once(R_P.'require/credit.php');
		if ($_POST['job'] == 'cate') {
		S::gp(array('cate'),'GP',1);
		$catedb = array();
		foreach ($cate as $key=>$val) {
			$flag = $val['flag'];
			$id = $val['id'];
			if (!$flag) {
				$flag = $id ? $id : randstr(5);
			} elseif($id && $id!=$flag) {
				$nf_order[$id] = $nf_order[$flag];
				$nf_newinfodb[$id] = $nf_newinfodb[$flag];
				unset($nf_order[$flag]);
				unset($nf_newinfodb[$flag]);
				$flag = $id;
			}
			if ($nf_order[$flag]['type'] != $val['type']) {
				$nf_newinfodb[$flag] = $tmp = array();
			} else {
				$tmp = $nf_order[$flag];
			}
			$tmp['name']  = $val['name'];
			$tmp['type']  = $val['type'];
			$tmp['order'] = (int)$val['order'];
			$catedb[$flag] = $tmp;
		}
		$nf_order = $catedb;
		uasort($nf_order,'orderCmp');
		setConfig('nf_order', $nf_order);
		setConfig('nf_newinfodb', $nf_newinfodb);
		updatecache_conf('nf', false, 'newinfo_config.php');
		adminmsg('operate_success',"$basename&action=rebang");

	} elseif($_POST['job'] == 'config') {

		S::gp(array('newinfoifopen','position','titlelen','shownum','bbsradioifopen'),'P');
		$newinfoifopen = $newinfoifopen ? '1' : '0';
		$bbsradioifopen = $bbsradioifopen ? '1' : '0';
		$nf_config['position'] = (int)$position ? (int)$position : '1';
		$nf_config['titlelen'] = (int)$titlelen ? (int)$titlelen : '25';
		$nf_config['shownum'] = (int)$shownum ? (int)$shownum : '7';

		setConfig('db_newinfoifopen', $newinfoifopen);
		setConfig('db_bbsradioifopen', $bbsradioifopen);
		setConfig('nf_config', $nf_config);

		updatecache_c();
		updatecache_conf('nf', false, 'newinfo_config.php');
		adminmsg('operate_success',"$basename&action=rebang");

	} else {

		uasort($nf_order,'orderCmp');
		ifcheck($db_newinfoifopen,'newinfoifopen');
		ifcheck($db_bbsradioifopen,'bbsradioifopen');
		$nf_config['position'] ? ${'position_'.$nf_config['position']} = 'checked' : $position_1 = 'checked';
		!$nf_config['titlelen'] && $nf_config['titlelen'] = '25';
		!$nf_config['shownum'] && $nf_config['shownum'] = '7';
		include PrintEot('rebang');exit;
	}
} elseif ($action == 'update') {

	!$nf_config['shownum'] && $nf_config['shownum'] = '7';
	!$nf_config['titlelen'] && $nf_config['titlelen'] = '24';
	$t		= array('hours'=>gmdate('G',$timestamp+$db_timedf*3600));
	$tdtime	= PwStrtoTime(get_date($timestamp,'Y-m-d'));
	$montime = PwStrtoTime(get_date($timestamp,'Y-m')."-1");
	$element = L::loadClass('element');
	$element->setDefaultNum($nf_config['shownum']);
	foreach ($nf_order as $key=>$val) {
		$updatetime = $nf_order[$key]['updatetime'] ? $nf_order[$key]['updatetime'] : 30;
		if ($val['order'] && $val['type'] != 'custom' && ($val['type'] != 'newpic' || !$val['mode'])) {
			if ($val['type'] == 'newpic' && !$val['mode']) {
				$nf_newinfodb[$key] = $element->newPic(0,5);
				foreach ($nf_newinfodb[$key] as $k => $v) {
					$nf_newinfodb[$key][$k] = array('id'=>$v['addition']['tid'],'name'=>substrs($v['title'],$nf_config['titlelen'],'N'),'value'=>$v['image']);
				}
			} elseif ($val['type'] == 'info' && $val['mode']) {
				$info = $element->getInfo();
				$bbsinfo = explode(',',$val['mode']);
				unset($nf_newinfodb[$key]);
				foreach ($bbsinfo as $val) {
					$nf_newinfodb[$key][$val] = $info[$val];
				}
			} elseif (in_array($val['type'],array('newpost','newreply','digest','hits','replies','newfavor','hotfavor'))) {
				switch ($val['type']) {
					case 'newpost' :
						$nf_newinfodb[$key] = $element->newSubject();break;
					case 'newreply' :
						$nf_newinfodb[$key] = $element->newReply();break;
					case 'digest' :
						$nf_newinfodb[$key] = $element->digestSubject();break;
					case 'hits' :
						$nf_newinfodb[$key] = $element->hitSort();break;
					case 'replies' :
						$nf_newinfodb[$key] = $element->replySort();break;
					case 'newfavor' :
						$nf_newinfodb[$key] = $element->newFavorsort();break;
					case 'hotfavor' :
						$nf_newinfodb[$key] = $element->hotFavorsort();break;
				}
				foreach ($nf_newinfodb[$key] as $k => $v) {
					$nf_newinfodb[$key][$k] = array(
						'id'		=> $v['addition']['tid'],
						'name'		=> substrs($v['title'],$nf_config['titlelen'],'N'),
						'value'		=> $v['value'],
						'addition'	=> array(
							'fid'		=> $v['addition']['fid'],
							'author'	=> $v['addition']['author'],
							'authorid'	=> $v['addition']['authorid'],
							'type'		=> $v['addition']['type'],
							'hits'		=> $v['addition']['hits'],
							'replies'	=> $v['addition']['replies'],
							'favors'	=> $v['addition']['favors']
						)
					);
				}
			} elseif ($val['type'] == 'new') {
				$nf_newinfodb[$key] = $element->getMembers('new');
				foreach ($nf_newinfodb[$key] as $k => $v) {
					$nf_newinfodb[$key][$k] = array('id'=>$v['addition']['uid'],'name'=>$v['title'],'value'=>$v['value']);
				}
			} elseif (in_array($val['type'],array('todaypost','rvrc','postnum','onlinetime','monthpost','monoltime','money','digests','currency','credit')) || array_key_exists($val['type'],$_CREDITDB)) {
				$nf_newinfodb[$key] = $element->userSort($val['type'],0,false);
				foreach ($nf_newinfodb[$key] as $k => $v) {
					$nf_newinfodb[$key][$k] = array('id'=>$v['addition']['uid'],'name'=>$v['title'],'value'=>$v['value']);
				}
			} elseif (in_array($val['type'],array('topic','article','tpost'))) {
				$nf_newinfodb[$key] = $element->forumSort($val['type']);
				foreach ($nf_newinfodb[$key] as $k => $v) {
					$nf_newinfodb[$key][$k] = array('id'=>$v['addition']['fid'],'name'=>$v['title'],'value'=>$v['value']);
				}
			}
			$nf_order[$key]['cachetime'] = $timestamp;
		}
	}
	uasort($nf_order,'orderCmp');
	setConfig('nf_order', $nf_order);
	setConfig('nf_newinfodb', $nf_newinfodb);
	updatecache_conf('nf', false, 'newinfo_config.php');
	adminmsg('operate_success',"$basename&action=rebang");

} elseif ($action == 'setting') {

	S::gp(array('orderid','type'));
	if (!$nf_order[$orderid] || $nf_order[$orderid]['type'] != $type) {
		adminmsg('operate_fail',"$basename&action=rebang");
	}
	if ($type == 'newpic') {

		if ($_POST['step'] == '2') {

			S::gp(array('title'),'P',0);
			S::gp(array('picmode','urls','links','idlinks'),'P');
			$nf_order[$orderid]['mode'] = $picmode ? '1' : '0';
			if ($nf_order[$orderid]['mode'] && is_array($urls)) {
				$pic = array();
				foreach ($urls as $key => $value) {
					is_numeric($idlinks[$key]) && empty($links[$key]) && $links[$key] = $idlinks[$key];
					if ($value) {
						$pic[] = array(
							'id'		=> stripslashes($links[$key]),
							'name'		=> stripslashes($title[$key]),
							'value'		=> stripslashes($value)
						);
					}
				}
				$nf_newinfodb[$orderid] = $pic;
				setConfig('nf_newinfodb', $nf_newinfodb);
			}
		} else {

			if (!$nf_order[$orderid]['mode']) {
				$picmode_1 = 'checked';
			} else {
				$picmode_2 = 'checked';
			}
			include PrintEot('rebang');exit;
		}
	} elseif ($type == 'custom') {

		if (!$_POST['step']) {

			foreach ($nf_newinfodb[$orderid] as $key => $value) {
				$nf_newinfodb[$orderid][$key]['name'] = Quot_cv($value['name']);
			}
			include PrintEot('rebang');exit;

		} else {

			S::gp(array('titles'),'P',0);
			S::gp(array('links'),'P');

			if (is_array($titles)) {
				$custom = array();
				foreach ($titles as $key => $value) {
					if($value){
						$custom[] = array(
							'name'=>stripslashes($value),
							'id'=>$links[$key],
						);
					}
				}
				$nf_newinfodb[$orderid] = $custom;
				setConfig('nf_newinfodb', $nf_newinfodb);
			}
		}
	} elseif ($type == 'info') {

		if ($_POST['step'] == '2') {

			S::gp(array('info'),'P');
			$bbsinfo = '';
			foreach ($info as $val) {
				$val && $bbsinfo .= ','.$val;
			}
			$bbsinfo && $bbsinfo = substr($bbsinfo,1);
			$nf_order[$orderid]['mode'] = $bbsinfo;

		} else {

			$bbsinfo = explode(',',$nf_order[$orderid]['mode']);
			foreach ($bbsinfo as $val) {
				$$val = 'checked';
			}
			include PrintEot('rebang');exit;
		}
	} elseif (!$_POST['step']) {
		include PrintEot('rebang');exit;
	}
	if ($_POST['step'] == '2') {

		S::gp(array('updatetime'),'P');
		$nf_order[$orderid]['updatetime'] = is_numeric($updatetime) ? intval($updatetime) : '0';
		setConfig('nf_order', $nf_order);
		updatecache_conf('nf', false, 'newinfo_config.php');
		adminmsg('operate_success',"$basename&action=rebang");
	}
}	


function orderCmp($row1,$row2){
   if ($row2['order'] > $row1['order']) {
       return 1;
   } elseif ($row2['order'] < $row1['order']) {
		return -1;
   } else {
		return strcmp($row1['cachetime'], $row2['cachetime']) ;
   }
}
?>