<?php
!function_exists('adminmsg') && exit('Forbidden');
!$adminitem && $adminitem = 'customfield';
$basename="$admin_file?adminjob=customfield";
$ajaxurl = EncodeUrl($basename . '&adminitem=' . $adminitem);
if ($adminitem == 'customfield') {
	if (empty($action)) {
		S::gp(array('page'));
		$customfielddb=array();
		$page = max((int) $page, 1);
		$fieldService = L::loadClass('customerfield','user');
		$count = $fieldService->countAllFields();
		$count > 0 && $customfielddb = $fieldService->getAllFieldsWithPages(($page - 1) * $db_perpage, $db_perpage);
		$total = ceil($count / $db_perpage);
		$pages = numofpage($count, $page, $total, $basename . '&adminitem=customfield&');
		include PrintEot('customfield');exit;
	} elseif ($action == 'add') {
		if (!$_POST['step']) {
			$rt = array();
			$state_1		= 'checked';
			$required_0		= 'checked';
			$viewinread_0	= 'checked';
			$editable_0		= 'checked';
			$areasService = L::loadclass("areasservice", 'utility');
			$basicValue = array(array('parentid'=>0,'selectid'=>'province','defaultid'=>'','hasfirst'=>1));
			$allAreas = $areasService->buildAllAreasLists($basicValue);
			include PrintEot('customfield');exit;
		} else {
			$insertArray = handleData();
			$fieldService = L::loadClass('customerfield','user');
			$id = $fieldService->setField($insertArray);
			$colums = $db->get_one("SHOW COLUMNS FROM pw_memberinfo LIKE 'field_$id'");
			if ($colums['Field'] != 'field_' . $id) {
				$db->query("ALTER TABLE pw_memberinfo ADD field_$id VARCHAR(255) NOT NULL default ''");
			}
			updatecache_field();
			adminmsg('operate_success');
		}
	} elseif ($action == 'edit') {
		S::gp(array('id'));
		if (!$_POST['step']) {
			$id < 1 && adminmsg('fieldid_error');
			$fieldService = L::loadClass('customerfield','user');
			$rt = $fieldService->getFieldByFieldId($id);
			!$rt && adminmsg('fieldid_error');
			$rt['ifsys'] && adminmsg('不允许编辑系统默认字段');
			$rt['dateoptions'] = $rt['areaoptions'] = array();
			${'state_'.$rt['state']}			= 'checked';
			${'required_'.$rt['required']}		= 'checked';
			${'viewinread_'.$rt['viewinread']}	= 'checked';
			${'editable_'.$rt['editable']}		= 'checked';
			${'type_'.$rt['type']}				= 'selected';
			${'default_'.$rt['category']} 		= 'selected';
			${'default_'.$rt['complement']} 	= 'selected';
			$groups = explode(',',$rt['viewright']);
			foreach($groups as $val){
				${'viewright_'.$val} = 'checked';
			}
			$areasService = L::loadclass("areasservice", 'utility');
			$basicValue = array(array('parentid'=>0,'selectid'=>'province','defaultid'=>'','hasfirst'=>1));
			$initValues = array();
			if ($rt['type'] == 7) {
				$rt['areaoptions'] = unserialize($rt['options']);
				$rt['areaoptions']['province'] && $rt['areaoptions']['province'] >= 0 && $initValues[] = array('parentid'=>0,'selectid'=>'province','defaultid'=>$rt['areaoptions']['province'],'hasfirst'=>1);
				$rt['areaoptions']['city'] && $rt['areaoptions']['city'] >= 0 && $initValues[] = array('parentid'=>$rt['areaoptions']['province'],'selectid'=>'city','defaultid'=>$rt['areaoptions']['city'],'hasfirst'=>1);
				$rt['areaoptions']['area'] && $rt['areaoptions']['area'] >= 0 && $initValues[] = array('parentid'=>$rt['areaoptions']['city'],'selectid'=>'area','defaultid'=>$rt['areaoptions']['area'],'hasfirst'=>1);
			}
			$allAreas = $areasService->buildAllAreasLists(S::isArray($initValues) ? $initValues : $basicValue);
			if (S::inArray($rt['type'], array(6, 7))) {
				$rt['type'] == 6 && $rt['dateoptions'] = unserialize($rt['options']);
				$rt['options'] = '';
			}
			include PrintEot('customfield');exit;
		} else {
			$fieldService = L::loadClass('customerfield','user');
			$fieldInfo = $fieldService->getFieldByFieldId($id);
			!$fieldInfo && adminmsg('fieldid_error');
			$fieldInfo['ifsys'] && adminmsg('不允许编辑系统默认字段');
			$updateArray = handleData();
			$fieldService->setField($updateArray, $id);
			if ($fieldInfo['type'] != $updateArray['type']) {
				$dropfield = '';
				$colums = $db->get_one("SHOW COLUMNS FROM pw_memberinfo LIKE ".S::sqlEscape('field_'.$fieldInfo['id']));
				$colums['Field'] == 'field_' . $fieldInfo['id'] && $dropfield = "DROP field_$fieldInfo[id]";
				$dropfield && $db->query("ALTER TABLE pw_memberinfo $dropfield");
				$db->query("ALTER TABLE pw_memberinfo ADD field_$fieldInfo[id] VARCHAR(255) NOT NULL default ''");
				/*$colums = $db->get_one("SHOW COLUMNS FROM pw_memberinfo LIKE ".S::sqlEscape('field_'.$fieldInfo['id']));
				$colums['Field'] == 'field_' . $fieldInfo['id'] && $db->query("UPDATE pw_memberinfo SET field_$fieldInfo[id] = ''");*/
			}
			updatecache_field();
			adminmsg('operate_success');
		}
	} elseif ($action == 'del') {
		S::gp(array('selid'));
		$selid = (int) $selid;
		if (!$selid) adminmsg('operate_error');
		$fieldService = L::loadClass('customerfield','user');
		$field = $fieldService->getFieldByFieldId($selid);
		!$field && adminmsg('operate_error');
		$field['ifsys'] && adminmsg('不允许删除系统默认字段');
		$dropfield = '';
		$colums = $db->get_one("SHOW COLUMNS FROM pw_memberinfo LIKE ".S::sqlEscape('field_'.$field['id']));
		$colums['Field'] == 'field_' . $field['id'] && $dropfield = "DROP field_$field[id]";
		$dropfield && $db->query("ALTER TABLE pw_memberinfo $dropfield");
		$fieldService->deleteFieldByFieldId($selid);
		updatecache_field();
		adminmsg('operate_success');
	} elseif ($action == 'briefedit') {
		S::gp(array('field'));
		if (!$field || !S::isArray($field)) adminmsg('operate_error');
		$fieldService = L::loadClass('customerfield','user');
		foreach ($field as $key => $value) {
			!isset($value['state']) && $value['state'] = 0;
			!isset($value['required']) && $value['required'] = 0;
			!isset($value['editable']) && $value['editable'] = 0;
			$fieldService->setField($value,$key);
		}
		updatecache_field();
		adminmsg('operate_success');
	} elseif ($action == 'ajaxselect') {
		define('AJAX',1);
		S::gp(array('parentid'));
		!$parentid && adminmsg('operate_error');
		$areasService = L::loadclass("areasservice", 'utility');
		$areasData = $parentid == -1 ? '' : $areasService->getAreasSelectHtml($parentid);
		echo "success\t".$areasData;
		ajax_footer();
	}
} elseif ($adminitem == 'guide') {
	if (empty($action)) {
		//* require_once pwCache::getPath(D_P . 'data/bbscache/dbreg.php');
		extract(pwCache::getData(D_P . 'data/bbscache/dbreg.php', false));
		$rg_recommendnames && $rg_recommendnames = implode("\n", $rg_recommendnames);
		include PrintEot('customfield');exit;
	} elseif ($action == 'edit') {
		S::gp(array('reg'));
		$reg['recommendnames'] && stripos($reg['recommendnames'], "\n") !== false && $reg['recommendnames'] = explode("\n", $reg['recommendnames']);
		$reg['recommendnames'] && !S::isArray($reg['recommendnames']) && $reg['recommendnames'] = array($reg['recommendnames']);
		$reg['recommendnames'] = array_unique($reg['recommendnames']);
		count($reg['recommendnames']) > 12 && adminmsg('推荐的用户最大不能超过12个', $basename . '&adminitem=guide');
		if ($reg['recommendnames']) {
			$existsUsernames = $reg['recommendids'] = array();
			$userService = L::loadClass('userservice','user');
			$tempExistsUsernames = $userService->getByUserNames($reg['recommendnames']);
			!S::isArray($tempExistsUsernames) && adminmsg('您输入的用户名不存在', $basename . '&adminitem=guide');
			foreach ($tempExistsUsernames as $value) {
				$existsUsernames[] = $value['username'];
				$reg['recommendids'][] = $value['uid'];
			}
			$difference = array_diff($reg['recommendnames'], $existsUsernames);
			if (S::isArray($difference)) {
				$diffStr = implode(',', $difference) . '不存在';
				adminmsg($diffStr, $basename . '&adminitem=guide');
			}
		}
		$reg['recommendcontent'] = trim($reg['recommendcontent']);
		saveConfig();
		adminmsg('operate_success', $basename . '&adminitem=guide');
	} elseif ($action == 'preview') {
		S::gp(array('reg'));
		$recommendcontent = pwHtmlspecialchars_decode(stripslashes($reg['recommendcontent']));
		//可能感兴趣的内容~最新图酷帖->最新帖
		$threadsService = L::loadClass('threads', 'forum'); /* @var $threadsService PW_Threads */
		$latestImageThreads = $threadsService->getLatestImageThreads(7);
		foreach ($latestImageThreads as $k=>$v) {
			//$recommendContent['attachurl'] = 
			$a_url = geturl($v['attachurl'], 'show',1);
			$latestImageThreads[$k]['thumb'] = getMiniUrl($v['attachurl'], $v['ifthumb'], $a_url[1]);
			//url
			$latestImageThreads[$k]['url'] = "read.php?tid={$v['tid']}";
			$db_htmifopen && $latestImageThreads[$k]['url'] = urlRewrite ( $latestImageThreads[$k]['url'] );
			//thumb
			!$latestImageThreads[$k]['thumb'] && $latestImageThreads[$k]['thumb'] = 'images/defaultactive.jpg';
		}
		include PrintEot('customfield');
		$output = ob_get_contents();
		$output = str_replace(array('<!--<!--<!---->','<!--<!---->','<!---->-->','<!---->'),'',$output);
		echo ObContents($output);
		unset($output);
	}
	
} elseif ($adminitem == 'area') {
	S::gp(array('action'));
	$areasService = L::loadclass("areasservice", 'utility');
	if (empty($action)){

		S::gp(array('parentid','province','city','provinceid'));
		
		$parentid = $parentid ? $parentid : 0;
		$initValues[] = array('parentid' => 0,'selectid' => 'province_areas','defaultid' => $parentid,'hasfirst'=>1);
		if ($province) $initValues[] = array('parentid'=>0,'selectid'=>'province_areas','defaultid'=>$province,'hasfirst'=>1);
		if ($city) $initValues[] = array('parentid'=>$province,'selectid'=>'city_areas','defaultid'=>$city,'hasfirst'=>1);
		$allAreas = $areasService->buildAllAreasLists($initValues);
		include PrintEot('customfield');exit;
	} elseif ($action == 'areasList') {
		define('AJAX',1);
		S::gp(array('parentid'));
		!$parentid && adminmsg('operate_error');
		$areasList = $areasService->getAreaByAreaParent($parentid);
		echo "success\t".pwJsonEncode($areasList);
		ajax_footer();
	} elseif ($action == 'areasSelect') {
		define('AJAX',1);
		S::gp(array('parentid'));
		!$parentid && adminmsg('operate_error');
		$areasSelect = $areasService->getAreasSelectHtml($parentid);
		echo "success\t".$areasSelect;
		ajax_footer();
	} elseif ($action == 'delArea') {
		define('AJAX',1);
		S::gp(array('areaId'));
		!$areaId && adminmsg('operate_error');
		if ($areasService->getAreaByAreaParent($areaId)) adminmsg('该地区下面还有子分类，请先删除子分类再进行此操作！');
		$areasService->deleteAreaByAreaId($areaId);
		echo "success";
		ajax_footer();
	} elseif ($action == 'editAreas') {
		S::gp(array('areas','names','vieworders','parentid','province','city','provinceid'));
		(!S::isArray($areas) && !$names) && adminmsg('operate_error');
		foreach ($areas as $key => $value) {
			$area['name'] = $value['name'];
			$area['parentid'] = $parentid;
			$area['vieworder'] = $value['vieworder'];
			$areasService->updateArea($area,$key);
		}
		$names = array_filter((array)$names);
		if ($names) {
			$parentid = $parentid ? intval($parentid) : 0;
			foreach ($names as $key => $v) {
				$areadb[] = array (
						'name' => $v,
						'parentid' => $parentid,
						'vieworder'=>$vieworders[$key]
				);
			}
			$areasService->addAreas($areadb);
		}
		adminmsg('operate_success',"$basename&adminitem=area&parentid=$parentid&province=$province&city=$city&provinceid=$provinceid");
	}
} elseif ($adminitem == 'school') {
	S::gp(array('action'));
	if (empty($action)){
		$areasService = L::loadclass("areasservice", 'utility');
		$provinces = $areasService->getAreasSelectHtml();
		$basicValue = array(array('parentid'=>0,'selectid'=>'province_middle','defaultid'=>'','hasfirst'=>1),array('parentid'=>0,'selectid'=>'province_elementary','defaultid'=>'','hasfirst'=>1));
		$allAreas = $areasService->buildAllAreasLists($basicValue);
		include PrintEot('customfield');exit;
	} elseif ($action == 'universityList') {
		define('AJAX',1);
		S::gp(array('parentid','typeId'));
		!$typeId && adminmsg('operate_error');
		$schoolservice = L::loadclass("schoolservice", 'user');
		$universityList = $schoolservice->getByAreaAndType($parentid,$typeId);
		echo "success\t".pwJsonEncode($universityList);
		ajax_footer();
	} elseif ($action == 'editSchool') {
		define('AJAX',1);
		S::gp(array('schools','names','parentid','typeId'));
		(!S::isArray($schools) && !$names) && adminmsg('operate_error');
		$schoolservice = L::loadclass("schoolservice", 'user');
		if ($schools) {
			foreach ($schools as $key => $value) {
				$schoolservice->editSchool($key,$value['schoolname']);
			}
		}
		if ($names) {
			$names = array_filter((array)$names);
			$typeId = intval($typeId);
			$parentid = intval($parentid);
			foreach ($names as $v) {
				$tmpdatadb['schoolname'] = $v;
				$tmpdatadb['areaid'] = $parentid;
				$tmpdatadb['type'] = $typeId;
				$datadb[] = $tmpdatadb; 
			}
			$schoolservice->addSchools($datadb);
		}
		$schoolsList = $schoolservice->getByAreaAndType($parentid,$typeId);
		echo "success\t".pwJsonEncode($schoolsList);
		ajax_footer();
	} elseif ($action == 'delSchool') {
		define('AJAX',1);
		S::gp(array('schoolId','typeId'));
		$schoolId = intval($schoolId);
		!$schoolId && adminmsg('operate_error');
		$schoolservice = L::loadclass("schoolservice", 'user');
		$schoolservice->deleteSchool($schoolId,$typeId);
		echo "success";
		ajax_footer();
	}/* elseif ($action == 'collegesList') {
		define('AJAX',1);
		S::gp(array('parentid'));
		!$parentid && adminmsg('operate_error');
		$collegeservice = L::loadclass("collegeservice", 'user');
		$collegesList = $collegeservice->getCollegeBySchoolId($parentid);
		echo "success\t".pwJsonEncode($collegesList);
		ajax_footer();
	}elseif ($action == 'editCollege') {
		define('AJAX',1);
		S::gp(array('colleges','names','parentid','typeId'));
		(!S::isArray($colleges) && !$names) && adminmsg('operate_error');
		$collegeservice = L::loadclass("collegeservice", 'user');
		if ($colleges) {
			foreach ($colleges as $key => $value) {
				$collegeservice->editCollege($key,$value['collegename']);
			}	
		}
		if ($names) {
			$names = array_filter((array)$names);
			$parentid = intval($parentid);
			$typeId = intval($typeId);
			foreach ($names as $v) {
				$tmpdatadb['collegename'] = $v;
				$tmpdatadb['schoolid'] = $parentid;
				$tmpdatadb['type'] = $typeId;
				$datadb[] = $tmpdatadb; 
			}
			$collegeservice->addColleges($datadb);
		}
		$collegeList = $collegeservice->getCollegeBySchoolId($parentid);
		echo "success\t".pwJsonEncode($collegeList);
		ajax_footer();
	} elseif ($action == 'checkIsTopSchool') {
		define('AJAX',1);
		S::gp(array('schoolId'));
		$schoolId = intval($schoolId);
		!$schoolId && adminmsg('operate_error');
		$collegeservice = L::loadclass("collegeservice", 'user');
		echo ($collegeservice->getCollegeBySchoolId($schoolId)) ?  "true" : "false" ;
		ajax_footer();
	} elseif ($action == 'delCollege') {
		define('AJAX',1);
		S::gp(array('collegeId'));
		$collegeId = intval($collegeId);
		!$collegeId && adminmsg('operate_error');
		$collegeservice = L::loadclass("collegeservice", 'user');
		$collegeservice->deleteCollege($collegeId);
		echo "success";
		ajax_footer();
	} */
}

function handleData() {
	S::gp(array('category','title','descrip','state','vieworder','maxlen','required','viewinread','editable','complement','type','groups','options'));
	global $category,$title,$descrip,$state,$vieworder,$maxlen,$required,$viewinread,$editable,$complement,$type,$groups,$options;
	if (!$title || !$category || !$type) adminmsg('operate_fail');
	S::inArray($type, array(3, 4, 5)) && !$options['text'] && adminmsg('options_error');
	strlen($title) > 50 && adminmsg('资料名称最大50字节');
	$descrip && strlen($descrip) > 255 && adminmsg('资料名称描述最大255字节');
	$viewright = '';
	if ($groups) {
		foreach ($groups as $val) {
			if (!is_numeric($val)) continue;
			$viewright .= $viewright ? ','.$val : $val;
		}
	}
	$data = array(
		'category' => $category,
		'title' => $title,
		'descrip' => $descrip,
		'state' => $state,
		'vieworder' => $vieworder,
		'maxlen'	=> $maxlen,
		'required'	=> $required,
		'viewinread'=> $viewinread,
		'editable'	=> $editable,
		'complement'=> $complement,
		'type'		=> $type,
		'viewright'	=> $viewright,
		'options'	=> $options
	);
	return $data;
}

function getMiniUrl($path, $ifthumb, $where) {
	$dir = '';
	($ifthumb & 1) && $dir = 'thumb/';
	($ifthumb & 2) && $dir = 'thumb/mini/';
	if ($where == 'Local') return $GLOBALS['attachpath'] . '/' . $dir . $path;
	if ($where == 'Ftp') return $GLOBALS['db_ftpweb'] . '/' . $dir . $path;
	if (!is_array($GLOBALS['attach_url'])) return $GLOBALS['attach_url'] . '/' . $dir . $path;
	return $GLOBALS['attach_url'][0] . '/' . $dir . $path;
}
?>