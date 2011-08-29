<?php
!function_exists('adminmsg') && exit('Forbidden');
S::gp(array('action', 'type','adminitem'));
empty($adminitem)  && $adminitem = 'navmain';
$basename = "$admin_file?adminjob=customnav&adminitem=$adminitem";
$navConfigService = L::loadClass('navconfig', 'site'); /* @var $navConfigService PW_NavConfig */
$postions = array();
foreach ($db_modes as $key => $value) {
	if ($value['ifopen']) {
		$postions[$key] = $db_modes[$key];
	}
}
$postions['srch'] = array('m_name'=>'搜索', 'title'=>'搜索');
$postions['group'] = array('m_name'=>'群组', 'title'=>'群组');
if ('navmain' == $adminitem) {
	if ('add' == $action) {
		$navSelection = formSelect('parentid', '', array('0'=>'-顶级导航') + tmpGenerateNavSelectionArray($navConfigService->findSubNavListByType(PW_NAV_TYPE_MAIN)), 'id="parentNavId" class="select_wa"');
		require PrintEOT("customnav");
	} elseif ('save' == $action) {
		S::gp(array('parentid', 'pos', 'title', 'link', 'view', 'alt', 'color', 'b', 'i', 'u', 'target', 'isshow', 'floattype', 'listtype', 'selflisttype'), 'P');
		
		empty($title) && adminmsg("nav_empty_title");
		$pos = is_array($pos) ? $pos : array();
		foreach ($pos as $key => $value) {
			if (!isset($postions[$value])) {
				unset($pos[$key]);
			}
		}
		
		if ($parentid) {
			$parentNav = $navConfigService->get($parentid);
			if ($parentNav['upid']) $parentid = 0;
		}

		$addFields = array(
			'upid' => intval($parentid),
			'title' => $title,
			'link' => $link,
			'style' => array('color'=>$color, 'b'=>$b, 'i'=>$i, 'u'=>$u),
			'pos' => $pos,
			'alt' => $alt,
			'target' => $target,
			'view' => $view,
			'isshow' => intval($isshow),
			'floattype' => $floattype,
			'listtype' => $listtype,
			'selflisttype' => $selflisttype,
		);
		$navConfigService->add(PW_NAV_TYPE_MAIN, $addFields);
		
		adminmsg("operate_success", "$basename");
	} elseif ('del' == $action) {
		$navId = (int) S::getGP('nid');
		$navConfigService->delete($navId);
		adminmsg("operate_success", "$basename");
	} elseif ('edit' == $action) {
		S::gp(array('nid'));
		!isset($nid) && adminmsg("undefine_action");
		
		$nav = $navConfigService->get($nid);
		if (!$nav) adminmsg('找不到导航链接配置');
		
		$baseNavList = $navConfigService->findSubNavListByType(PW_NAV_TYPE_MAIN);
		$navSelection = array();
		foreach ($baseNavList as $tmpNav) {
			$navSelection[$tmpNav['nid']] = '--' . $tmpNav['title'];
		}
		unset($navSelection[$nid]);
		$navSelection = formSelect('parentid', $nav['upid'], array('0'=>'-顶级导航') + $navSelection, 'id="parentNavId" class="select_wa"');
		
		$fontstyle = tmpGetNavTitleStyle($nav['style']);
		if ($nav['style']['b'])  $bChecked = "checked";
		if ($nav['style']['i'])  $iChecked = "checked";
		if ($nav['style']['u']) $uChecked = "checked";
		
		$isshowChecked = $nav['isshow'] ? 'checked' : '';
		$nav['target'] == 1 ? $blankChecked = "checked" : $selfChecked = "checked";
		$floatCrossChecked = $nav['floattype'] == 'cross' ? 'checked' : '';
		$floatVerticalChecked = $nav['floattype'] == 'vertical' ? 'checked' : '';
		$floatNoneChecked = $nav['floattype'] == '' ? 'checked' : '';
		$listSpaceChecked = $nav['listtype'] == 'space' ? 'checked' : '';
		$listAlignChecked = $nav['listtype'] == 'align' ? 'checked' : '';
		$listOneChecked = $nav['listtype'] == 'onecol' ? 'checked' : '';
		$listTwoChecked = $nav['listtype'] == 'twocol' ? 'checked' : '';
		$selflistSpaceChecked = $nav['selflisttype'] == 'space' ? 'checked' : '';
		$selflistAlignChecked = $nav['selflisttype'] == 'align' ? 'checked' : '';

		require PrintEOT("customnav");
	} elseif ('doedit' == $action) {
		S::gp(array('nid', 'parentid', 'pos', 'title', 'link', 'view', 'alt', 'color', 'b', 'i', 'u', 'target', 'isshow', 'floattype', 'listtype', 'selflisttype'), 'P');
		
		empty($title) && adminmsg("nav_empty_title");
		$pos = is_array($pos) ? $pos : array();
		foreach ($pos as $key => $value) {
			if (!isset($postions[$value])) {
				unset($pos[$key]);
			}
		}
		
		if ($parentid) {
			$parentNav = $navConfigService->get($parentid);
			if ($parentNav['upid']) $parentid = 0;
		}
		
		$oldNavData = $navConfigService->get($nid);
		if (!$oldNavData) adminmsg('找不到导航链接配置');
		if (!$oldNavData['upid'] && $parentid && count($navConfigService->findSubNavListByType(PW_NAV_TYPE_MAIN, $nid))) {
			adminmsg("nav_hassub", "$basename");
		}

		$updateFields = array(
			'upid' => intval($parentid),
			'title' => $title,
			'link' => $link,
			'style' => array('color'=>$color, 'b'=>$b, 'i'=>$i, 'u'=>$u),
			'pos' => $pos,
			'alt' => $alt,
			'target' => $target,
			'view' => $view,
			'isshow' => intval($isshow),
			'floattype' => $floattype,
			'listtype' => $listtype,
			'selflisttype' => $selflisttype,
		);
		$navConfigService->update($nid, $updateFields);
		
		adminmsg("operate_success", "$basename");
	} elseif ('editview' == $action) {
		S::gp(array('view'), 'P');
		
		if (!is_array($view) || !count($view)) adminmsg('没有要更新的数据');
		
		$updates = 0;
		foreach ($view as $navId => $navUpdate) {
			$navId = intval($navId);
			if ($navId <= 0) adminmsg('数据非法，请重试');
			if (trim($navUpdate['title']) == '') adminmsg("nav_empty_title");
			
			$updates += $navConfigService->update($navId, array('view'=>$navUpdate['view'], 'isshow'=>(int)$navUpdate['isshow'], 'title'=>$navUpdate['title']));
		}

		adminmsg("operate_success", "$basename&type=$type");
	} elseif ('restore' == $action){
		$navConfigService->deleteByType(PW_NAV_TYPE_MAIN);
		
		$adds = 0;
		
		$view = 20;
		$vieworder = array('area' => '1', 'bbs' => '2');
		foreach ($db_modes as $key => $value) {
			if (isset($db_modedomain[$key]) && $db_modedomain[$key]) {
				$link = 'http://' . $db_modedomain[$key];
			} elseif ('o' == $key) {
				$link = 'u.php';
			} else {
				$link = 'index.php?m=' . $key;
			}
			$adds += (bool)$navConfigService->add(PW_NAV_TYPE_MAIN, array('nkey' => $key, 'pos' => '-1', 'title' => ($value['title'] ? $value['title'] : $value['m_name']), 'style' => '', 'link' => $link, 'alt' => '', 'target' => 0, 'view' => $vieworder[$key] ? $vieworder[$key] : $view++, 'upid' => 0, 'isshow' => $value['ifopen']));
		}
		
		$adds += (bool)$navConfigService->add(PW_NAV_TYPE_MAIN, array('nkey' => 'group', 'pos' => '-1', 'title' => '群组', 'style' => '', 'link' => 'group.php', 'alt' => '', 'target' => 0, 'view' => 3, 'upid' => 0, 'isshow' => 1));
		
		$view = 4;
		//* include pwCache::getPath(D_P.'data/bbscache/area_config.php');
		pwCache::getData(D_P.'data/bbscache/area_config.php');
		if (!$area_default_alias) {
			$currentAlias = is_array($area_channels) ? current($area_channels) : array();
			$area_default_alias = $currentAlias['alias'];
		}
		$channelService=L::loadClass('channelService', 'area');
		foreach ($channelService->getChannels() as $alias => $channel) {
			$link = "index.php?m=area&alias=".$alias;
			$isShow = $area_default_alias == $alias ? 0 : 1;
			$adds += (bool)$navConfigService->add(PW_NAV_TYPE_MAIN, array('nkey' => 'area_'.$alias, 'pos' => '-1', 'title' => $channel['name'], 'link' => $link, 'view' => $view++, 'upid' => 0, 'isshow' => $isShow));
		}
		
		adminmsg("operate_success", "$basename");
	} else {
		$navs = $navConfigService->relateNavList($navConfigService->findNavListByType(PW_NAV_TYPE_MAIN));

		require PrintEOT("customnav");
	}
	
} elseif ('navside' == $adminitem) {
	
	$navType = in_array($type, array(PW_NAV_TYPE_HEAD_LEFT, PW_NAV_TYPE_HEAD_RIGHT, PW_NAV_TYPE_FOOT)) ? $type : PW_NAV_TYPE_HEAD_RIGHT;
	$navTypeName = array(PW_NAV_TYPE_HEAD_RIGHT=>'顶部右侧导航', PW_NAV_TYPE_HEAD_LEFT=>'顶部左侧导航', PW_NAV_TYPE_FOOT=>'底部导航');
	$navTypeRight = PW_NAV_TYPE_HEAD_RIGHT;
	$navTypeLeft = PW_NAV_TYPE_HEAD_LEFT;
	$navTypeFoot = PW_NAV_TYPE_FOOT;
	
	if ('add' == $action) {
		$rightNavSelection = formSelect("parentid[$navTypeRight]", '', array('0'=>'-顶级导航') + tmpGenerateNavSelectionArray($navConfigService->findSubNavListByType(PW_NAV_TYPE_HEAD_RIGHT)), 'id="parentNav'.$navTypeRight.'" class="select_wa" style="display:none;"');
		$leftNavSelection = formSelect("parentid[$navTypeLeft]", '', array('0'=>'-顶级导航') + tmpGenerateNavSelectionArray($navConfigService->findSubNavListByType(PW_NAV_TYPE_HEAD_LEFT)), 'id="parentNav'.$navTypeLeft.'" class="select_wa" style="display:none;"');
		require PrintEOT("customnav");
	} elseif ('save' == $action) {
		S::gp(array('newnavtype', 'parentid', 'pos', 'title', 'link', 'view', 'alt', 'color', 'b', 'i', 'u', 'target', 'isshow', 'listtype'), 'P');

		empty($title) && adminmsg("nav_empty_title", "$basename&type=$newnavtype" . ($newnavtype == $navTypeFoot ? '&action=footlist' : ''));
		empty($newnavtype) && adminmsg('请选择导航类型');
		$pos = is_array($pos) ? $pos : array();
		foreach ($pos as $key => $value) {
			if (!isset($postions[$value])) {
				unset($pos[$key]);
			}
		}
		
		$parentid = isset($parentid[$newnavtype]) ? $parentid[$newnavtype] : 0;
		if ($parentid) {
			$parentNav = $navConfigService->get($parentid);
			if ($parentNav['upid']) $parentid = 0;
		}

		$addFields = array(
			'upid' => intval($parentid),
			'title' => $title,
			'link' => $link,
			'style' => array('color'=>$color, 'b'=>$b, 'i'=>$i, 'u'=>$u),
			'pos' => $pos,
			'alt' => $alt,
			'target' => $target,
			'view' => $view,
			'isshow' => intval($isshow),
			'listtype' => $listtype,
		);
		$navConfigService->add($newnavtype, $addFields);
		
		adminmsg("operate_success", "$basename&type=$newnavtype" . ($newnavtype == $navTypeFoot ? '&action=footlist' : ''));
	} elseif ('del' == $action) {
		$navId = (int) S::getGP('nid');
		
		$nav = $navConfigService->get($navId);
		if (!$nav) adminmsg('找不到导航链接配置');
		$navType = $nav['type'];
		
		$navConfigService->delete($navId);
		adminmsg("operate_success", "$basename&type=$navType" . ($navType == $navTypeFoot ? '&action=footlist' : ''));
	} elseif ('edit' == $action) {
		S::gp(array('nid'));
		!isset($nid) && adminmsg("undefine_action");
		
		$nav = $navConfigService->get($nid);
		if (!$nav) adminmsg('找不到导航链接配置');
		$navType = $nav['type'];
	
		tmpResetNavPostions($navType);
		
		$baseNavList = $navConfigService->findSubNavListByType($navType);
		$navSelection = array();
		foreach ($baseNavList as $tmpNav) {
			$navSelection[$tmpNav['nid']] = '--' . $tmpNav['title'];
		}
		unset($navSelection[$nid]);
		$navSelection = formSelect('parentid', $nav['upid'], array('0'=>'-顶级导航') + $navSelection, 'id="parentNavId" class="select_wa"');
		
		$fontstyle = tmpGetNavTitleStyle($nav['style']);
		if ($nav['style']['b'])  $bChecked = "checked";
		if ($nav['style']['i'])  $iChecked = "checked";
		if ($nav['style']['u']) $uChecked = "checked";
		
		$isshowChecked = $nav['isshow'] ? 'checked' : '';
		$nav['target'] == 1 ? $blankChecked = "checked" : $selfChecked = "checked";
		$listOneChecked = $nav['listtype'] == 'onecol' ? 'checked' : '';
		$listTwoChecked = $nav['listtype'] == 'twocol' ? 'checked' : '';

		require PrintEOT("customnav");
	} elseif ('doedit' == $action) {
		S::gp(array('nid', 'parentid', 'pos', 'title', 'link', 'view', 'alt', 'color', 'b', 'i', 'u', 'target', 'isshow', 'listtype'), 'P');
		
		empty($title) && adminmsg("nav_empty_title");
		$pos = is_array($pos) ? $pos : array();
		foreach ($pos as $key => $value) {
			if (!isset($postions[$value])) {
				unset($pos[$key]);
			}
		}
		
		if ($parentid) {
			$parentNav = $navConfigService->get($parentid);
			if ($parentNav['upid']) $parentid = 0;
		}
		
		$oldNavData = $navConfigService->get($nid);
		if (!$oldNavData) adminmsg('找不到导航链接配置');
		$navType = $oldNavData['type'];
		if (!$oldNavData['upid'] && $parentid && count($navConfigService->findSubNavListByType($navType, $nid))) {
			adminmsg("nav_hassub", "$basename");
		}

		$updateFields = array(
			'title' => $title,
			'link' => $link,
			'style' => array('color'=>$color, 'b'=>$b, 'i'=>$i, 'u'=>$u),
			'pos' => $pos,
			'alt' => $alt,
			'target' => $target,
			'view' => $view,
			'isshow' => intval($isshow),
			'floattype' => $floattype,
			'listtype' => $listtype,
		);
		if ($navType != $navTypeFoot) $updateFields['upid'] = intval($parentid);
		$navConfigService->update($nid, $updateFields);
		
		adminmsg("operate_success", "$basename&type=$navType" . ($navType == $navTypeFoot ? '&action=footlist' : ''));
	} elseif ('editview' == $action) {
		S::gp(array('view'), 'P');
		
		if (!is_array($view) || !count($view)) adminmsg('没有要更新的数据');
		
		$updates = 0;
		foreach ($view as $navId => $navUpdate) {
			$navId = intval($navId);
			if ($navId <= 0) adminmsg('数据非法，请重试');
			if (trim($navUpdate['title']) == '') adminmsg("nav_empty_title");
			
			$updates += $navConfigService->update($navId, array('view'=>$navUpdate['view'], 'isshow'=>(int)$navUpdate['isshow'], 'title'=>$navUpdate['title']));
		}

		adminmsg("operate_success", "$basename&type=$type" . ($type == $navTypeFoot ? '&action=footlist' : ''));
	} elseif ('restore' == $action){
		if ($navType == $navTypeFoot) {
			$navConfigService->deleteByType(PW_NAV_TYPE_FOOT);
			
			$defaults = array(
				array('pos' => '-1', 'title' => '联系我们', 'link' => '', 'view'=>1, 'target' => 0, 'isshow' => 1),
				array('pos' => '-1', 'title' => '无图版', 'link' => 'simple/', 'view'=>2, 'target' => 0, 'isshow' => 1),
				array('pos' => '-1', 'title' => '手机浏览', 'link' => 'm/index.php', 'view'=>3, 'target' => 0, 'isshow' => 1),
			);
			foreach ($defaults as $key => $value) {
				$adds += (bool)$navConfigService->add(PW_NAV_TYPE_FOOT, $value);
			}
		} elseif ($navType == $navTypeLeft) {
			$navConfigService->deleteByType(PW_NAV_TYPE_HEAD_LEFT);
			
			$defaults = array(
				array('pos' => array('bbs','area'), 'title' => '10分钟建站', 'link' => 'http://www.phpwind.com/easysite/index.php', 'view'=>1, 'target' => 1, 'isshow' => 1),
				array('pos' => array('bbs','area'), 'title' => '轻松转换', 'link' => 'http://www.phpwind.net/convert.php', 'view'=>2, 'target' => 1, 'isshow' => 1),
				array('pos' => array('bbs','area'), 'title' => '站长沙龙', 'link' => 'http://www.phpwind.com/salon/index.html', 'view'=>3, 'target' => 1, 'isshow' => 1),
			);
			foreach ($defaults as $key => $value) {
				$adds += (bool)$navConfigService->add(PW_NAV_TYPE_HEAD_LEFT, $value);
			}
		} elseif ($navType == $navTypeRight) {
			$navConfigService->deleteByType(PW_NAV_TYPE_HEAD_RIGHT);
			
			$defaults = array(
				'hack' => array(
					'data' => array('pos' => array('bbs','area'), 'nkey' => 'hack', 'title' => '社区服务', 'link' => '', 'view'=>1, 'target' => 0, 'isshow' => 1),
					'subs' => array(),
				),
				'sort' => array(
					'data' => array('pos' => array('bbs','area'), 'nkey' => 'sort', 'title' => '统计排行', 'link' => 'sort.php', 'view'=>2, 'target' => 0, 'isshow' => 1),
					'subs' => array(),
				),
				'help' => array(
					'data' => array('pos' => array('bbs','area'), 'title' => '帮助', 'link' => 'faq.php', 'view'=>3, 'target' => 0, 'isshow' => 1),
				),
			);
			
			$view = 1;
			foreach ($db_hackdb as $value) {
				list($title, $key, $valid) = $value;
				$link = 'toolcenter' == $key ? 'profile.php?action=toolcenter' : 'hack.php?H_name=' . $key;
				$defaults['hack']['subs'][$key] = array('nkey' => 'hack_' . $key, 'title' => $title, 'link' => $link, 'view' => $view++, 'isshow' => ($valid ? 1 : 0));
			}
			$view = 1;
			foreach (explode("\n", getLangInfo('all', 'mode_bbs_nav')) as $value) {
				if (!trim($value)) continue;
				list($title, $key, $link, $upkey) = explode(',', trim($value));
				if ($upkey == 'sort') {
					$defaults['sort']['subs'][$key] = array('nkey' => $key, 'title' => trim($title), 'link' => trim($link), 'view' => $view++, 'isshow' => 1);
				}
			}
			
			foreach ($defaults as $key => $value) {
				$navId = $navConfigService->add(PW_NAV_TYPE_HEAD_RIGHT, $value['data']);
				if (isset($value['subs'])) {
					foreach ($value['subs'] as $sub) {
						$sub['upid'] = $navId;
						$navConfigService->add(PW_NAV_TYPE_HEAD_RIGHT, $sub);
					}
				}
			}
		}
		adminmsg("operate_success", "$basename&type=$navType" . ($navType == $navTypeFoot ? '&action=footlist' : ''));
	} elseif ('footlist' == $action) {
		$navType = $navTypeFoot;
		$navs = $navConfigService->relateNavList($navConfigService->findNavListByType($navType));
		require PrintEOT("customnav");
	} else {
		$navs = $navConfigService->relateNavList($navConfigService->findNavListByType($navType));

		require PrintEOT("customnav");
	}
} else {
	adminmsg("undefine_action");
}

function tmpGetNavTitleStyle($styleSet) {
	$fontstyle = "color:".$styleSet['color'].";";
	if ($styleSet['b']) $fontstyle .= "font-weight='bolder';";
	if ($styleSet['i']) $fontstyle .= "font-style='italic';";
	if ($styleSet['u']) $fontstyle .= "text-decoration='underline';";
	return $fontstyle;
}

function tmpGenerateNavSelectionArray($navList) {
	$navSelection = array();
	foreach ($navList as $nav) {
		$navSelection[$nav['nid']] = '--' . $nav['title'];
	}
	return $navSelection;
}

function tmpResetNavPostions($navType) {
	global $postions;
	if (in_array($navType, array(PW_NAV_TYPE_HEAD_LEFT, PW_NAV_TYPE_HEAD_RIGHT))) unset($postions['o']);
}
