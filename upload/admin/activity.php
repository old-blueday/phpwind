<?php
!function_exists('adminmsg') && exit('Forbidden');
$basename = "$admin_file?adminjob=activity";

S::gp(array('actid','actmid'),GP,2);
$categoryService = L::loadClass('ActivityCategory', 'activity');

if (empty($action)){ //活动分类管理
	$activity_catedb = $categoryService->getCates();
	foreach ($activity_catedb as $rt) {
		$activity_catedb[$rt['actid']]['ajax_basename_edittopic'] = EncodeUrl($basename . '&action=edittopic&actid=' . $rt['actid']);
		$activity_catedb[$rt['actid']]['ajax_basename_addmodelname'] = EncodeUrl($basename . '&action=addmodelname&actid=' . $rt['actid']);
		$activity_catedb[$rt['actid']]['ajax_basename_editmodelname'] = EncodeUrl($basename . '&action=editmodelname&actid=' . $rt['actid']);
	}
	$activity_modeldb = $categoryService->getModels();
	foreach ($activity_modeldb as $rt) {
		if ($rt['ifable']) {
			if (!$activity_catedb[$rt['actid']]['model']) {
				$activity_catedb[$rt['actid']]['model'] = "<a href=\"$basename&action=editmodel&actid=$rt[actid]&actmid=$rt[actmid]\">".$rt['name']."</a>";
			} else {
				$activity_catedb[$rt['actid']]['model'] .= ','."<a href=\"$basename&action=editmodel&actid=$rt[actid]&actmid=$rt[actmid]\">".$rt['name']."</a>";
			}
		}
		$activity_modeldb[$rt['actmid']] = $rt['actid'];
	}

	$actmidb = $modelforumdb = $oldforum = array();
	$query = $db->query("SELECT fid,actmids FROM pw_forums WHERE actmids!='' AND type<>'category'");
	while ($rt = $db->fetch_array($query)) {
		$actmidb[$rt['fid']] = $rt['actmids'];
	}

	foreach ($actmidb as $key => $value) {
		foreach (explode(",",$value) as $val) {
			$modelforumdb[$val][] = $key;
		}
	}

	foreach ($modelforumdb as $k => $value) {
		$cateforumdb[$activity_modeldb[$k]][] = $value;
	}

	foreach ($cateforumdb as $actid => $value) {
		foreach ($value as $values) {
			foreach ($values as $val) {
				if ($oldforum[$actid][$forum[$val]['name']] != $forum[$val]['name'] && $activity_catedb[$actid]) {
					if (!$activity_catedb[$actid]['forum']) {
						$activity_catedb[$actid]['forum'] = $forum[$val]['name'];
					} else {
						$activity_catedb[$actid]['forum'] .= ','.$forum[$val]['name'];
					}
					$oldforum[$actid][$forum[$val]['name']] = $forum[$val]['name'];
				}
			}
		}
	}

	include PrintEot('activity');
} elseif ($action == 'topic') { //活动内容管理
	S::gp(array('page','step','field','newfield', 'actmid'));
	
	//* @include_once pwCache::getPath(D_P. 'data/bbscache/activity_config.php');
	pwCache::getData(D_P. 'data/bbscache/activity_config.php');
	$Activity = L::loadClass('Activity', 'activity');
	$fieldService = L::loadClass('ActivityField', 'activity');
	//获取分类选项框
	$topicdb = array();
	$actmidSelectWithoutSelectTagHtml = $Activity->getActmidSelectHtml($actmid, 1, '');
	
	$defaultValueTableName = getActivityValueTableNameByActmid();

	$searchhtml = $asearchhtml = '';
	if ($actmid) { //查询子分类
		$searchFieldDb = $fieldService->getEnabledAndSearchableFieldsByModelId($actmid);
		//自定义字段的表
		$tablename = $userDefinedValueTableName = getActivityValueTableNameByActmid($actmid, 1, 1);
	} else { //查询所有子分类
		// 对于所有子分类，搜索为程序预设
		$searchFieldDb = $fieldService->getDefaultSearchFields();
		//预设字段的表
		$tablename = $defaultValueTableName;
	}
	foreach ($searchFieldDb as $rt) {
		$rt['fieldvalue'] = $field[$rt['fieldname']];
		if ($rt['ifsearch'] == 1) {
			$searchhtml .= getSearchHtml($rt);
		} elseif ($rt['ifasearch'] == 1) {
			unset($rt['fieldvalue']);
			$asearchhtml .= getSearchHtml($rt);
		}
	}
	$searchhtml && $searchhtml .= '</span>';
	$asearchhtml && $asearchhtml .= '</span>';
	if (strpos($searchhtml,'</span></span>') !== false) {
		$searchhtml = str_replace('</span></span>','</span>',$searchhtml);
	}
	if (strpos($asearchhtml,'</span></span>') !== false) {
		$asearchhtml = str_replace('</span></span>','</span>',$asearchhtml);
	}

	if ('search' == $step) {
		L::loadClass('PostActivity', 'activity', false);
		$searchTopic = new PW_PostActivity($field);
		if (!$newfield) {
			$newfield = StrCode(serialize($field));
		}
		list($count,$tiddb,$alltiddb) = $searchTopic->getSearchvalue($newfield,'one',true,true);
		is_array($tiddb) && $sql .= " AND tv.tid IN(" . S::sqlImplode($tiddb) . ")";
		is_array($alltiddb) && $alltids = implode(',',$alltiddb);
	}

	if ($step != 'search' || !$count) {
		$alltiddb = $threadb = $newtiddb = array();
		$query = $db->query("SELECT tid FROM $tablename WHERE ifrecycle=0");
		while ($rt = $db->fetch_array($query)) {
			$alltiddb[] = $rt['tid'];
		}
		if ($alltiddb) {
			$query = $db->query("SELECT tid FROM pw_threads WHERE tid IN(" . S::sqlImplode($alltiddb) . ")");
			while ($rt = $db->fetch_array($query)) {
				$threadb[$rt['tid']] = $rt['tid'];
			}
		}
		foreach ($alltiddb as $value) {
			if (!$threadb[$value]) {
				$newtiddb[] = $value;
			}
		}

		if ($tablename && count($newtiddb) > 0) {
			if ($defaultValueTableName) {
				$db->update("DELETE FROM $defaultValueTableName WHERE tid IN(" . S::sqlImplode($newtiddb) . ") AND ifrecycle=0");
			}
			if ($userDefinedValueTableName) {
				$db->update("DELETE FROM $userDefinedValueTableName WHERE tid IN(" . S::sqlImplode($newtiddb) . ") AND ifrecycle=0");
			}
		}

		is_array($threadb) && $alltids = implode(',',$threadb);
		$count = $db->get_value("SELECT COUNT(tid) as count FROM $tablename WHERE ifrecycle=0");
	}

	if ($count > 0) {
		$page < 1 && $page = 1;
		$numofpage = ceil($count/$db_perpage);
		if ($numofpage && $page > $numofpage) {
			$page = $numofpage;
		}
		$pages = numofpage($count,$page,$numofpage,"$basename&action=topic&actmid=$actmid&newfield=$newfield&step=$step&");
		if ($step != 'search') {
			$start = ($page-1)*$db_perpage;
			$limit = S::sqlLimit($start,$db_perpage);
		}
		$addActmidSql = $actmid ? "AND actmid=$actmid" : '';
		$query = $db->query("SELECT tv.tid, tv.recommend, tv.actmid, t.fid,t.subject,t.author,t.authorid,t.postdate 
							FROM $defaultValueTableName tv 
							LEFT JOIN pw_threads t ON tv.tid=t.tid 
							WHERE 1 AND ifrecycle=0 $addActmidSql $sql 
							ORDER BY t.postdate DESC $limit");
		while ($rt = $db->fetch_array($query)) {
			$rt['postdate'] = get_date($rt['postdate']);
			$rt['recommend'] = $rt['recommend'] ? getLangInfo('other','act_yes') : getLangInfo('other','act_no');
			$topicdb[] = $rt;
		}
	}

	include PrintEot('activity');
	exit;

} elseif ($_POST['setrecommend'] || $action == 'setrecommend' || $_POST['removerecommend'] || $action == 'removerecommend') { //设为或取消推荐
	S::gp(array('selid','alltids'));
	if ($selid) {
		$selid = S::sqlImplode($selid);
	} elseif ($alltids) {
		$alltids = explode(',',$alltids);
		$selid = S::sqlImplode($alltids);
	} else {
		adminmsg('operate_error',"$basename&action=topic");
	}

	$defaultValueTableName = getActivityValueTableNameByActmid();
	if ($_POST['setrecommend'] || $action == 'setrecommend') {
		$setValue = 1;
	} else {
		$setValue = 0;
	}
	$db->update("UPDATE $defaultValueTableName SET recommend = ". S::sqlEscape($setValue)." WHERE tid IN ($selid)");
	adminmsg('operate_success',"$basename&action=topic");
} elseif ($_POST['sendmsg'] || $action == 'sendmsg') {
	S::gp(array('step','nexto'));
	if (empty($step)) {
		S::gp(array('selid','alltids'));

		if ($selid) {
			$selid = S::sqlImplode($selid);
		} elseif ($alltids) {
			$alltids = explode(',',$alltids);
			$selid = S::sqlImplode($alltids);
		} else {
			adminmsg('operate_error',"$basename&action=topic");
		}

		$uids = '';
		$query = $db->query("SELECT authorid FROM pw_threads WHERE tid IN($selid) GROUP BY authorid");
		while ($rt = $db->fetch_array($query)) {
			$uids .= $uids ? ','.$rt['authorid'] : $rt['authorid'];
		}
		include PrintEot('activity');exit;
	} elseif ($step == '2') {
		S::gp(array('subject','atc_content','uids'));
		$cache_file = D_P."data/bbscache/".substr(md5($admin_pwd),10,10).".txt";
		if (!$nexto) {
			pwCache::setData($cache_file,$atc_content);
		} else {
			$atc_content = pwCache::getData($cache_file, false, true);
		}

		if (empty($subject) || empty($atc_content)) {
			adminmsg('sendmsg_empty','javascript:history.go(-1);');
		}

		$subject     = S::escapeChar($subject);
		$sendmessage = S::escapeChar($atc_content);
		$percount = 1;
		empty($nexto) && $nexto = 1;

		$uids = explode(',',$uids);
		$count = count($uids);

		if ($uids) {
			$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
			$members = $userService->getByUserIds($uids);
			foreach ($members as $member) {
				@extract($member);
				$sendmessage = str_replace("\$email",$email,$atc_content);
				$sendmessage = str_replace("\$windid",$username,$sendmessage);
				$userNames[] = $username;
			}
			M::sendNotice(
					$userNames,
					array(
						'title' 			=> $subject,
						'content' 			=> $sendmessage
					),
					'notice_active',
					'notice_active'
			);
		}
		$havesend = $step*$percount;
		if ($count > ($nexto*$percount)) {
			$nexto++;
			$j_url = "$basename&action=$action&step=2&nexto=$nexto&subject=".rawurlencode($subject);
			adminmsg("sendmsg_step",EncodeUrl($j_url),1);
		} else {
			//* P_unlink($cache_file);
			pwCache::deleteData($cache_file);
			adminmsg('operate_success',"$basename&action=topic");
		}
	}
} elseif ($action == 'delthreads') {

	S::gp(array('selid'));
	!$selid && adminmsg('operate_error');
	is_numeric($selid) && $selid = explode(',',$selid);
	$delids = array();
	foreach ($selid as $key => $value) {
		if (is_numeric($value)) {
			$delids[] = $value;
		}
	}

	!$delids && Showmsg('operate_error');

	$delarticle = L::loadClass('DelArticle', 'forum');
	$readdb = $delarticle->getTopicDb('tid ' . $delarticle->sqlFormatByIds($delids));
	$delarticle->delTopic($readdb, $db_recycle);

	if ($db_ifpwcache ^ 1) {
		$db->update("DELETE FROM pw_elements WHERE type !='usersort' AND id IN(" . S::sqlImplode($delids) . ')');
	}

	# $db->update("DELETE FROM pw_threads WHERE tid IN ($selids)");
	# ThreadManager
	//* $threadManager = L::loadClass("threadmanager", 'forum');
	//* $threadManager->deleteByThreadIds($fid,$selids);
	$threadService = L::loadclass('threads', 'forum');
	$threadService->deleteByThreadIds($delids);
	Perf::gatherInfo('changeThreadWithForumIds', array('fid'=>$fid));
	
	//P_unlink(D_P.'data/bbscache/c_cache.php');
	pwCache::deleteData(D_P.'data/bbscache/c_cache.php');

	adminmsg('operate_success',"$basename&action=topic&actmid=$actmid");

} elseif ('topiclist' == $action) { //编辑活动主分类列表
	S::gp(array('selid', 'vieworder', 'topiclist'), 'P', 2);
	S::gp(array('name'), 'P');

	foreach ($topiclist as $actid => $value) {
		$thisIfAble		= $selid[$actid] == 1 ? 1 : 0;
		$thisViewOrder	= $vieworder[$actid] ? (int)$vieworder[$actid] : 0;
		$thisName		= $name[$actid] ? $name[$actid] : '';
		$fieldData = array('ifable' => $thisIfAble, 'vieworder' => $thisViewOrder);
		if ($thisName) { //可编辑分类名称的情况
			strlen($thisName) > 14 && Showmsg('act_topic_name_error');
			$oldData = $categoryService->getCate($actid);
			if ($oldData['ifdel'] == 1) { //只有非预设分类才可编辑
				$fieldData['name'] = $thisName;
			}
		}
		$categoryService->updateCate($actid, $fieldData);
	}
	updateCacheActivity();
	adminmsg('operate_success',$basename);
} elseif ('edittopic' == $action) {
	define('AJAX',1);
	S::gp(array('step'), 'P', 2);

	if (empty($step)) {

		$ajax_basename_add = EncodeUrl($basename."&action=edittopic&");
		$selectmodel = '';
		$topic = $categoryService->getCate($actid);

		$modelsInThisCate = $categoryService->getModelsByCateId($actid);
		foreach ($modelsInThisCate as $rt) {
			$checked = $rt['ifable'] ? 'checked="checked"' : '';
			$selectmodel .= "<li><label><input style=\"vertical-align:middle;\" type=\"checkbox\" name=\"actmid[$rt[actmid]]\" value=\"1\" $checked /> $rt[name]</label></li>";
		}
		$nameHtml = $topic['ifdel'] ? '<input type="text" name="name" id="cate_name" class="input input_wa" value="' . $topic['name'] . '" >&nbsp;'.getLanginfo('other','act_topic_more14') : $topic['name'];

		ifcheck($topic['ifable'],'ifable');
		include PrintEot('activity');
		ajax_footer();

	} elseif (2 == $step) {
		S::gp(array('name'),'P');
		S::gp(array('ifable','vieworder'),'P',2);

		$fieldData = array('ifable' => $ifable, 'vieworder' => $vieworder);
		if ($name) {
			$name = trim(ieconvert($name));
			strlen($name) > 14 && Showmsg('act_topic_name_error');
			$thisCate = $categoryService->getCate($actid);
			if ($thisCate['ifdel'] == 1) {
				$fieldData['name'] = $name;
			}
		}
		$categoryService->updateCate($actid, $fieldData);

		!is_array($actmid) && $actmid = array();
		$updatedb = array();
		foreach ($actmid as $key => $value) {
			if (is_numeric($key)) {
				$value = (int)$value;
				$updatedb[] = $key;
			}
		}
		if ($updatedb) {
			$categoryService->updateModelByCateIdInIds($actid, $updatedb, array('ifable' => 1));
			$categoryService->updateModelByCateIdNotInIds($actid, $updatedb, array('ifable' => 0));
		} else {
			Showmsg('act_model_not_none');
		}
		updateCacheActivity();

		Showmsg('act_topic_edit_success');
	}
} elseif ('deltopic' == $action) {

	$thisCate = $categoryService->getCate($actid);
	!$thisCate && Showmsg('act_topic_undefined');

	$categoryService->deleteCate($actid);
	$modelsInThisCate = $categoryService->getModelsByCateId($actid);
	foreach ($modelsInThisCate as $rt) {
		deleteTopicAndModelDataByActmid($rt['actmid']);
	}
	updateCacheActivity();
	adminmsg('operate_success',$basename);

} elseif ('editmodel' == $action) {
	$fieldService = L::loadClass('ActivityField', 'activity');
	S::gp(array('step'),GP,2);

	$ajax_basename_add = EncodeUrl($basename."&action=addfield");
	$thisModel = $categoryService->getModel($actmid);
	$modelname = $thisModel['name'];

	if (empty($step)) {

		$thisCate = $categoryService->getCate($actid);
		$actid = $thisCate['actid'];
		empty($actid) && adminmsg('act_illegal_actid_or_actmid');

		$ajax_basename				= EncodeUrl($basename);
		$ajax_basename_edit			= EncodeUrl($basename."&action=editfield");
		$ajax_basename_view			= EncodeUrl($basename."&action=viewfield");
		$ajax_basename_delfield		= EncodeUrl($basename."&action=delfield");
		$ajax_basename_editindex	= EncodeUrl($basename."&action=editindex");

		if (empty($actmid)) {//获取子分类
			$firstModelInCate = $categoryService->getFirstModelByCateId($actid);
			$actmid = $firstModelInCate['actmid'];
		}
		
		if ($actmid) {
			$fielddb = $fieldService->getFieldsByModelId($actmid);
			foreach ($fielddb as $rt){
				$rt['ifable_checked']		= $rt['ifable'] ? 'checked' : '';
				$rt['ifsearch_checked']		= $rt['ifsearch'] ? 'checked' : '';
				$rt['ifasearch_checked']	= $rt['ifasearch'] ? 'checked' : '';
				$rt['threadshow_checked']	= $rt['threadshow'] ? 'checked' : '';
				$rt['ifmust_checked']		= $rt['ifmust'] ? 'checked' : '';
				//不能删除的字段（默认字段）
				$rt['ifable_disabled']		= !$rt['ifdel'] && $rt['mustenable'] ? 'disabled="disabled"' : '';
				$rt['ifmust_disabled']		= !$rt['ifdel'] && $rt['ifmust'] && $rt['mustenable'] ? 'disabled="disabled"' : '';
				$rt['search_disabled']		= $rt['issearchable'] ? '' : 'disabled="disabled"';
				$rt['threadshow_disabled']	= $rt['allowthreadshow'] ? '' : 'disabled="disabled"';
				$rt['vieworder_disabled']	= !$rt['ifdel'] ? 'disabled="disabled"' : '';
				$rt['sectionname_disabled'] = !$rt['ifdel'] ? 'disabled="disabled"' : '';
				if ($rt['ifdel']) {
					$rt['editLinkHtml']		= "<a href=\"javascript:;\" class=\"mr20\" onclick=\"sendmsg('$ajax_basename_edit','fieldid=$rt[fieldid]&actmid=$actmid&actid=$actid',this.id);\" id=\"editfield_$rt[fieldid]\">".getLanginfo('other','act_edit')."</a>";
					$rt['deleteLinkHtml']	= "<a href=\"javascript:;\" onclick=\"delfield($rt[fieldid]);return false;\" class=\"mr20\">".getLanginfo('other','act_delete')."</a>";
				} else {
					$rt['editLinkHtml']		= "<a href=\"javascript:;\" class=\"mr20\" onclick=\"sendmsg('$ajax_basename_view','fieldid=$rt[fieldid]&actmid=$actmid&actid=$actid',this.id);\" id=\"editfield_$rt[fieldid]\">".getLanginfo('other','act_view')."</a>";
					$rt['deleteLinkHtml']	= '';
				}
				if ($rt['ifsearch'] || $rt['ifasearch']) {
					$rt['ifindex'] = 1;
				}

				//获取字段的索引状态
				if (in_array($rt['type'],array('textarea','url','image','upload'))) {
					$rt['indexstate'] = '-1';
				} else {
					$tablename = getActivityValueTableNameByActmid($actmid, 1, $rt['ifdel']);
					$fieldname = $rt['fieldname'];
					$rt['indexstate'] = 0;
					$query2 = $db->query("SHOW KEYS FROM $tablename");
					while($rt2 = $db->fetch_array($query2)) {
						$fieldname == $rt2['Column_name'] && $rt['indexstate'] = 1;
					}
				}
				$fielddb[$rt['fieldid']] = $rt;
			}
		}

		include PrintEot('activity');
		exit;
	} elseif ('2' == $step) {
		S::gp(array('fieldlist', 'sectionname', 'vieworder','ifable','ifsearch','ifasearch','threadshow','ifmust','textwidth'), P);
		foreach ($fieldlist as $key => $value) {
			$updateFields = array('textwidth' => $textwidth[$key]);
			$fieldRow = $fieldService->getField($key);
			if ($fieldRow['issearchable']) { //允许搜索的字段
				$updateFields['ifsearch'] = $ifsearch[$key];
				$updateFields['ifasearch'] = $ifasearch[$key];
			}
			if ($fieldRow['allowthreadshow']) {
				$updateFields['threadshow'] = $threadshow[$key];
			}
			if ($fieldRow['ifdel']) { //用户自定义字段
				$updateFields['ifable'] = $ifable[$key];
				$updateFields['vieworder'] = $vieworder[$key];
				$updateFields['ifmust'] = $ifmust[$key];
				$updateFields['sectionname'] = $sectionname[$key];
			} else { //预设字段
				//所有子分类预设字段共用默认查询、高级查询的设置
				//$allModelUpdateFields = array('ifsearch'=>$ifsearch[$key],'ifasearch'=>$ifasearch[$key]);
				//$db->update("UPDATE pw_activityfield SET " . S::sqlSingle($allModelUpdateFields) . " WHERE fieldname=" . S::sqlEscape($fieldRow['fieldname']));
				if (!$fieldRow['mustenable']) {
					$updateFields['ifmust'] = $ifmust[$key];
					$updateFields['ifable'] = $ifable[$key];
				}
			}
			$fieldService->updateField($key, $updateFields);
		}
		adminmsg("operate_success",$basename."&action=editmodel&actid=".$actid."&actmid=".$actmid);

	} elseif ('3' == $step) {//预览
		$pwpost = array();
		L::loadClass('ActivityForBbs', 'activity', false);
		$postActForBbs = new PW_ActivityForBbs($data);

		$topichtml = $postActForBbs->getActHtml($actmid);
		include PrintEot('activity');
		exit;
	}
} elseif ('editmodelname' == $action) {

	S::gp(array('step'),GP,2);
	define('AJAX',1);
	if (empty($step)) {
		$ajax_basename_editmodelname	= EncodeUrl($basename.'&action=editmodelname&');
		$ajax_basename_delmodel			= EncodeUrl($basename."&action=delmodel");

		$modeldb = $categoryService->getModelsByCateId($actid);//获取活动子分类
		include PrintEot('activity');
		ajax_footer();
	} elseif (2 == $step) {
		S::gp(array('vieworder', 'modellist'),'P',2);
		S::gp(array('name'));
		foreach ($name as $key => $value) {
			if (strlen($value) > 30) {
				echo "model_name_too_long\t";ajax_footer();
			}
		}
		$oldModels = $categoryService->getModels();
		foreach($modellist as $key => $value) {
			$fieldData = array('vieworder' => $vieworder[$key]);
			if ($name[$key]) {//有活动名字
				if ($oldModels[$key]['ifdel'] == 1) { //如可编辑
					$fieldData['name'] = $name[$key];
				}
				$categoryService->updateModel($key, $fieldData);
			} else {
				$categoryService->updateModel($key, $fieldData);
			}
		}
		updateCacheActivity();
		echo "success\t";
		ajax_footer();
	}

} elseif ('addmodelname' == $action) {
	define('AJAX',1);
	S::gp(array('step'), 'P', 2);
	if (empty($step)) {

		$ajax_basename_addmodelname = EncodeUrl($basename.'&action=addmodelname');
		include PrintEot('activity');
		ajax_footer();
	} elseif (2 == $step) {
		S::gp(array('modename'));
		if (strlen($modename) > 30) {
			echo "mode_name_too_long\t";
			ajax_footer();
		} elseif (0 == strlen($modename)) {
			echo "mode_name_empty\t";
			ajax_footer();
		}
		$oldmodel = $categoryService->countModelByCateIdAndName($actid, $modename);
		if ($oldmodel) {
			echo "samename\t";
			ajax_footer();
		}
		$actmid = $categoryService->addModel(array('name' => $modename, 'actid' => $actid));

		$createsql = "CREATE TABLE ".getActivityValueTableNameByActmid($actmid, 0, 1)." (tid mediumint(8) unsigned NOT NULL,fid SMALLINT( 6 ) UNSIGNED NOT NULL DEFAULT 0,ifrecycle TINYINT(1) NOT NULL DEFAULT 0,PRIMARY KEY (tid))";

		if ($db->server_info() >= '4.1') {
			$createsql .= " ENGINE=MyISAM".($charset ? " DEFAULT CHARSET=$charset" : '');
		} else {
			$createsql .= " TYPE=MyISAM";
		}
		$db->query($createsql);
		updateCacheActivity();

		$presetField = array(
			array(
				'name'		=> getLangInfo('other','act_field_activity_date'),
				'fieldtype' => 'calendar',
				'descrip'	=> '',
				'option'	=> array(
					'fieldname'			=> 'starttime',
					'vieworder'			=> 1,
					'rules'				=> array('precision' => 'minute'),
					'ifmust'			=> 1,
					'ifdel'				=> 0,
					'mustenable'		=> 1,
					'textwidth'			=> 18,
					'sectionname'		=> getLangInfo('other','act_field_activity_desc'),
					'issearchable'		=> 1,
					'allowthreadshow'	=> 1,
				)
			),
			array(
				'name'		=> '-',
				'fieldtype' => 'calendar',
				'descrip'	=> '',
				'option'	=> array(
					'fieldname'			=> 'endtime',
					'vieworder'			=> 1,
					'rules'				=> array('precision' => 'minute'),
					'ifmust'			=> 1,
					'ifdel'				=> 0,
					'mustenable'		=> 1,
					'textwidth'			=> 18,
					'sectionname'		=> getLangInfo('other','act_field_activity_desc'),
					'issearchable'		=> 1,
					'allowthreadshow'	=> 1,
				)
			),
			array(
				'name'		=> getLangInfo('other','act_field_activity_addr'),
				'fieldtype' => 'text',
				'descrip'	=> getLangInfo('other','act_field_activity_addr_desc'),
				'option'	=> array(
					'fieldname'			=> 'location',
					'vieworder'			=> 2,
					'rules'				=> '',
					'ifmust'			=> 0,
					'ifdel'				=> 0,
					'mustenable'		=> 0,
					'textwidth'			=> 40,
					'sectionname'		=> getLangInfo('other','act_field_activity_desc'),
					'issearchable'		=> 1,
					'allowthreadshow'	=> 1,
				)
			),
			array(
				'name'		=> getLangInfo('other','act_field_main_image'),
				'fieldtype' => 'upload',
				'descrip'	=> getLangInfo('other','act_field_main_image_desc'),
				'option'	=> array(
					'fieldname'			=> 'picture1',
					'vieworder'			=> 3,
					'rules'				=> '',
					'ifmust'			=> 0,
					'ifdel'				=> 0,
					'mustenable'		=> 1,
					'sectionname'		=> getLangInfo('other','act_field_activity_desc'),
					'issearchable'		=> 0,
					'allowthreadshow'	=> 0,
				)
			),
			array(
				'name'		=> getLangInfo('other','act_field_main_image2'),
				'fieldtype' => 'upload',
				'descrip'	=> '',
				'option'	=> array(
					'fieldname'			=> 'picture2',
					'vieworder'			=> 3,
					'rules'				=> '',
					'ifmust'			=> 0,
					'ifdel'				=> 0,
					'mustenable'		=> 1,
					'sectionname'		=> getLangInfo('other','act_field_activity_desc'),
					'issearchable'		=> 0,
					'allowthreadshow'	=> 0,
				)
			),
			array(
				'name'		=> getLangInfo('other','act_field_main_image3'),
				'fieldtype' => 'upload',
				'descrip'	=> '',
				'option'	=> array(
					'fieldname'			=> 'picture3',
					'vieworder'			=> 3,
					'rules'				=> '',
					'ifmust'			=> 0,
					'ifdel'				=> 0,
					'mustenable'		=> 1,
					'sectionname'		=> getLangInfo('other','act_field_activity_desc'),
					'issearchable'		=> 0,
					'allowthreadshow'	=> 0,
				)
			),
			array(
				'name'		=> getLangInfo('other','act_field_main_image4'),
				'fieldtype' => 'upload',
				'descrip'	=> '',
				'option'	=> array(
					'fieldname'			=> 'picture4',
					'vieworder'			=> 3,
					'rules'				=> '',
					'ifmust'			=> 0,
					'ifdel'				=> 0,
					'mustenable'		=> 1,
					'sectionname'		=> getLangInfo('other','act_field_activity_desc'),
					'issearchable'		=> 0,
					'allowthreadshow'	=> 0,
				)
			),
			array(
				'name'		=> getLangInfo('other','act_field_main_image5'),
				'fieldtype' => 'upload',
				'descrip'	=> '',
				'option'	=> array(
					'fieldname'			=> 'picture5',
					'vieworder'			=> 3,
					'rules'				=> '',
					'ifmust'			=> 0,
					'ifdel'				=> 0,
					'mustenable'		=> 1,
					'sectionname'		=> getLangInfo('other','act_field_activity_desc'),
					'issearchable'		=> 0,
					'allowthreadshow'	=> 0,
				)
			),
			array(
				'name'		=> getLangInfo('other','act_field_contact'),
				'fieldtype' => 'text',
				'descrip'	=> '',
				'option'	=> array(
					'fieldname'			=> 'contact',
					'vieworder'			=> 4,
					'rules'				=> '',
					'ifmust'			=> 1,
					'ifdel'				=> 0,
					'mustenable'		=> 1,
					'textwidth'			=> 20,
					'sectionname'		=> getLangInfo('other','act_field_activity_desc'),
					'issearchable'		=> 1,
					'allowthreadshow'	=> 1,
				)
			),
			array(
				'name'		=> getLangInfo('other','act_field_contact_tel'),
				'fieldtype' => 'text',
				'descrip'	=> getLangInfo('other','act_field_contact_tel_desc'),
				'option'	=> array(
					'fieldname'			=> 'telephone',
					'vieworder'			=> 5,
					'rules'				=> '',
					'ifmust'			=> 1,
					'ifdel'				=> 0,
					'mustenable'		=> 1,
					'textwidth'			=> 40,
					'sectionname'		=> getLangInfo('other','act_field_activity_desc'),
					'issearchable'		=> 1,
					'allowthreadshow'	=> 1,
				)
			),
			array(
				'name'		=> getLangInfo('other','act_field_signup_date'),
				'fieldtype' => 'calendar',
				'descrip'	=> '',
				'option'	=> array(
					'fieldname'			=> 'signupstarttime',
					'vieworder'			=> 6,
					'rules'				=> array('precision' => 'minute'),
					'ifmust'			=> 1,
					'ifdel'				=> 0,
					'mustenable'		=> 1,
					'textwidth'			=> 18,
					'sectionname'		=> getLangInfo('other','act_field_signup_desc'),
					'issearchable'		=> 1,
					'allowthreadshow'	=> 1,
				)
			),
			array(
				'name'		=> '-',
				'fieldtype' => 'calendar',
				'descrip'	=> '',
				'option'	=> array(
					'fieldname'			=> 'signupendtime',
					'vieworder'			=> 6,
					'rules'				=> array('precision' => 'minute'),
					'ifmust'			=> 1,
					'ifdel'				=> 0,
					'mustenable'		=> 1,
					'textwidth'			=> 18,
					'sectionname'		=> getLangInfo('other','act_field_signup_desc'),
					'issearchable'		=> 1,
					'allowthreadshow'	=> 1,
				)
			),
			array(
				'name'		=> getLangInfo('other','act_field_min_people'),
				'fieldtype' => 'text',
				'descrip'	=> '',
				'option'	=> array(
					'fieldname'			=> 'minparticipant',
					'vieworder'			=> 7,
					'rules'				=> '',
					'ifmust'			=> 0,
					'ifdel'				=> 0,
					'mustenable'		=> 0,
					'textwidth'			=> 3,
					'sectionname'		=> getLangInfo('other','act_field_signup_desc'),
					'issearchable'		=> 0,
					'allowthreadshow'	=> 1,
				)
			),
			array(
				'name'		=> getLangInfo('other','act_field_max_people'),
				'fieldtype' => 'text',
				'descrip'	=> getLangInfo('other','act_field_max_people_desc'),
				'option'	=> array(
					'fieldname'			=> 'maxparticipant',
					'vieworder'			=> 7,
					'rules'				=> '',
					'ifmust'			=> 0,
					'ifdel'				=> 0,
					'mustenable'		=> 0,
					'textwidth'			=> 3,
					'sectionname'		=> getLangInfo('other','act_field_signup_desc'),
					'issearchable'		=> 0,
					'allowthreadshow'	=> 1,
				)
			),
			array(
				'name'		=> getLangInfo('other','act_field_signup_restrict'),
				'fieldtype' => 'radio',
				'descrip'	=> '',
				'option'	=> array(
					'fieldname'			=> 'userlimit',
					'vieworder'			=> 8,
					'rules'				=> array('selection' => getLangInfo('other','act_field_signup_restrict_select')),
					'ifmust'			=> 0,
					'ifdel'				=> 0,
					'mustenable'		=> 0,
					'textwidth'			=> 3,
					'sectionname'		=> getLangInfo('other','act_field_signup_desc'),
					'issearchable'		=> 1,
					'allowthreadshow'	=> 1,
				)
			),
			array(
				'name'		=> getLangInfo('other','act_field_other_restrict'),
				'fieldtype' => 'text',
				'descrip'	=> '',
				'option'	=> array(
					'fieldname'			=> 'specificuserlimit',
					'vieworder'			=> 8,
					'rules'				=> '',
					'ifmust'			=> 0,
					'ifdel'				=> 0,
					'mustenable'		=> 0,
					'textwidth'			=> 14,
					'sectionname'		=> getLangInfo('other','act_field_signup_desc'),
					'issearchable'		=> 1,
					'allowthreadshow'	=> 1,
				)
			),
			array(
				'name'		=> getLangInfo('other','act_field_gender_restrict'),
				'fieldtype' => 'radio',
				'descrip'	=> '',
				'option'	=> array(
					'fieldname'			=> 'genderlimit',
					'vieworder'			=> 9,
					'rules'				=> array('selection' => getLangInfo('other','act_field_gender_restrict_select')),
					'ifmust'			=> 0,
					'ifdel'				=> 0,
					'mustenable'		=> 0,
					'sectionname'		=> getLangInfo('other','act_field_signup_desc'),
					'issearchable'		=> 1,
					'allowthreadshow'	=> 1,
				)
			),
			array(
				'name'		=> getLangInfo('other','act_field_fee'),
				'fieldtype' => 'text',
				'descrip'	=> getLangInfo('other','act_field_fee_desc'),
				'option'	=> array(
					'fieldname'			=> 'fees',
					'vieworder'			=> 10,
					'rules'				=> '',
					'ifmust'			=> 1,
					'ifdel'				=> 0,
					'mustenable'		=> 1,
					'sectionname'		=> getLangInfo('other','act_field_fee_info'),
					'issearchable'		=> 0,
					'allowthreadshow'	=> 1,
				)
			),
			array(
				'name'		=> getLangInfo('other','act_field_fee_detail'),
				'fieldtype' => 'text',
				'descrip'	=> '',
				'option'	=> array(
					'fieldname'			=> 'feesdetail',
					'vieworder'			=> 11,
					'rules'				=> '',
					'ifmust'			=> 0,
					'ifdel'				=> 0,
					'mustenable'		=> 0,
					'sectionname'		=> getLangInfo('other','act_field_fee_info'),
					'issearchable'		=> 0,
					'allowthreadshow'	=> 1,
				)
			),
			array(
				'name'		=> getLangInfo('other','act_field_fee_pay_type'),
				'fieldtype' => 'radio',
				'descrip'	=> '',
				'option'	=> array(
					'fieldname'			=> 'paymethod',
					'vieworder'			=> 12,
					'rules'				=> array('selection' => getLangInfo('other','act_field_fee_pay_type_desc')),
					'ifmust'			=> 1,
					'ifdel'				=> 0,
					'mustenable'		=> 1,
					'sectionname'		=> getLangInfo('other','act_field_fee_info'),
					'issearchable'		=> 1,
					'allowthreadshow'	=> 1,
				)
			),
		);
		foreach ($presetField as $field) {
			insertActivityFieldToDb($actmid, $field['fieldtype'], $field['name'], $field['descrip'], 1, $field['option']);
		}
		echo "success\t$actid\t$actmid";
		ajax_footer();
	}
} elseif ('addfield' == $action)  {
	define('AJAX',1);
	S::gp(array('step'), 'P', 2);

	if (!$step) {
		$ajax_basename_add			= EncodeUrl($basename."&action=addfield");
		$ajax_basename_copy			= EncodeUrl($basename."&action=copyfield");
		$ajax_basename_showfield	= EncodeUrl($basename."&action=showfield");

		//获取所有分类
		$select_catedb = $categoryService->getCates();

		//获取所有模版
		$select_modeldb = $categoryService->getModels();

		include PrintEot('activity');
		ajax_footer();

	} elseif (2 == $step) {
		S::gp(array('fieldtype','name','rules','descrip'));
		insertActivityFieldToDb($actmid, $fieldtype, $name, $descrip, 0, array('rules' => $rules));
		Showmsg('act_field_add_success');
	}
} elseif ('editfield' == $action) {
	define('AJAX',1);
	S::gp(array('step'), 'P', 2);
	S::gp(array('fieldid'),GP,2);
	$fieldService = L::loadClass('ActivityField', 'activity');
	$fielddb = $fieldService->getField($fieldid);
	$fielddb['ifdel'] || Showmsg('act_field_edit_forbidden');
	if (!$step) {
		$ajax_basename_edit = EncodeUrl($basename."&action=editfield");
		$tablename = getActivityValueTableNameByActmid($fielddb['actmid'], 1, $fielddb['ifdel']);
		$count = $db->get_value("SELECT COUNT(*) FROM $tablename WHERE ".$fielddb['fieldname']." != ''");//查找是否变量已有值
		$count && $ifhidden = '1';

		$rules = $fielddb['rules'];
		$type = $fielddb['type'];
		if ('number' == $type) {
			$minnum = $rules['minnum'];
			$maxnum = $rules['maxnum'];
		} elseif('select' == $type || 'radio' == $type || 'checkbox' == $type) {
			foreach ($rules as $key => $value) {
				$rule_content .= ($rule_content ? "\r\n" : '').$value;
			}
		} elseif ('calendar' == $type) {
			$calendarPrecision = $rules['precision'];
			$calendarChecked = array();
			if ('minute' == $calendarPrecision) {
				$calendarChecked['minute'] = ' checked="checked"';
				$calendarChecked['day'] = '';
			} elseif ('day' == $calendarPrecision) {
				$calendarChecked['minute'] = '';
				$calendarChecked['day'] = ' checked="checked"';
			}
		}
		include PrintEot('activity');
		ajax_footer();
	} elseif (2 == $step) {
		S::gp(array('fieldtype','name','rules','descrip'));
		if (empty($fieldtype)) Showmsg('fieldtype_not_exists');
		$s_rules = getFieldRules($fieldtype, $rules);

		//查找字段在表，判断是否有数据，如有数据不可更改字段类型
		$tablename = getActivityValueTableNameByActmid($fielddb['actmid'], 1, $fielddb['ifdel']);
		$fieldname = $fielddb['fieldname'];

		if ($fieldtype != $fielddb['type']) {
			$count = $db->get_value("SELECT COUNT(*) FROM $tablename WHERE $fieldname != ''");
			if ($count) Showmsg('can_not_modify_field_type');
		}
		$fieldData = array('name' => $name, 'type' => $fieldtype, 'rules' => $s_rules, 'descrip' => $descrip);
		$fieldService->updateField($fieldid, $fieldData);

		Showmsg('act_field_edit_success');
	}
} elseif ('viewfield' == $action) {
	define('AJAX',1);
	S::gp(array('fieldid'),GP,2);
	$fieldService = L::loadClass('ActivityField', 'activity');
	$fielddb = $fieldService->getField($fieldid);
	empty($fielddb['fieldid']) && Showmsg('field_not_select');
	$rules = $fielddb['rules'];
	$type = $fielddb['type'];
	if ($type == 'number') {
		$minnum = $rules['minnum'];
		$maxnum = $rules['maxnum'];
	} elseif ($type == 'calendar') {
		$calendarPrecision = $rules['precision'];
		$calendarChecked = array();
		if ('minute' == $calendarPrecision) {
			$calendarChecked['minute'] = ' checked="checked"';
			$calendarChecked['day'] = '';
		} elseif ('day' == $calendarPrecision) {
			$calendarChecked['minute'] = '';
			$calendarChecked['day'] = ' checked="checked"';
		}
	} elseif('select' == $type || 'radio' == $type || 'checkbox' == $type) {
		foreach ($rules as $key => $value) {
			$rule_content .= ($rule_content ? "<br />" : '').$value;
		}
	}
	include PrintEot('activity');
	ajax_footer();
} elseif ('showfield' == $action) {
	define('AJAX',1);
	S::gp(array('currentactmid'));
	$fieldService = L::loadClass('ActivityField', 'activity');
	$fielddb = $fieldService->getDeletableFieldsByModelId($actmid);

	foreach ($fielddb as $rt) {
		$fielddb[$rt['fieldid']] = $rt['nameInDb'];
	}
	if (!$fielddb) {
		echo "fail\t";
		ajax_footer();
	}
	$fielddb = pwJsonEncode($fielddb);
	echo "success\t$fielddb";
	ajax_footer();

} elseif ('copyfield' == $action) {
	define('AJAX',1);
	S::gp(array('copyfield'));
	if (empty($copyfield) || !is_array($copyfield)) {
		Showmsg('act_copyfield_none');
	}
	$fieldService = L::loadClass('ActivityField', 'activity');
	$fields = $fieldService->getFieldsByIds($copyfield);

	foreach ($fields as $rt) {
		if (!$rt['ifdel']) { //默认字段，禁止复制
			continue;
		} else {
			$fieldData = array('name' => $rt['nameInDb'], 'actmid' => $actmid, 'type' => $rt['type'], 'rules' => serialize($rt['rules']),'descrip' => $rt['descrip']);
			$fieldid = $fieldService->insertField($fieldData);

			$userDefinedValueTableName = getActivityValueTableNameByActmid($actmid, 1, 1);
			$fieldname = 'field'.$fieldid;
			$fieldService->updateField($fieldid, array('fieldname' => $fieldname));
			$ckfieldname = $db->get_one("SHOW COLUMNS FROM $userDefinedValueTableName LIKE '$fieldname'");
			if ($ckfieldname) {
				$fieldService->deleteField($fieldid);
				Showmsg('field_have_exists');
			} else {
				$sql = getFieldSqlByType($rt['type']);
				$db->query("ALTER TABLE $userDefinedValueTableName ADD $fieldname $sql");
			}
		}
	}
	Showmsg('act_field_copy_success');

} elseif ('delfield' == $action) {
	define('AJAX',1);
	S::gp(array('fieldid'),GP,2);
	$fieldService = L::loadClass('ActivityField', 'activity');
	$ckfield = $fieldService->getField($fieldid);
	if ($ckfield) {
		$ckfield['ifdel'] || Showmsg('act_field_del_forbidden');
		$userDefinedValueTableName = getActivityValueTableNameByActmid($ckfield['actmid'], 1, 1);
		$fieldname = $ckfield['fieldname'];
		$fieldService->deleteField($fieldid);
		$ckfield2 = $db->get_one("SHOW COLUMNS FROM $userDefinedValueTableName LIKE '$fieldname'");
		if ($ckfield2) {
			$db->query("ALTER TABLE $userDefinedValueTableName DROP $fieldname");
		} else {
			echo "fail";
		}
		echo "success\t$ckfield[actmid]";
	} else {
		echo "fail";
	}
	ajax_footer();

} elseif ('editindex' == $action) {
	define('AJAX',1);

	S::gp(array('type'));
	S::gp(array('fieldid'),GP,2);
	$fieldService = L::loadClass('ActivityField', 'activity');
	$fielddb = $fieldService->getField($fieldid);

	$tablename = getActivityValueTableNameByActmid($actmid, 1, $fielddb['ifdel']);
	$fieldname = $fielddb['fieldname'];

	$field = $db->get_one("SHOW COLUMNS FROM $tablename LIKE " . S::sqlEscape($fieldname));
	if (empty($fielddb) || empty($field)) {
		Showmsg('field_not_exists');
	}
	if (in_array($fielddb['type'],array('textarea','url','image','upload'))) {
		Showmsg('field_cannot_modify_index');
	}
	$fieldindex = 0;
	$query = $db->query("SHOW KEYS FROM $tablename");
	while($rt = $db->fetch_array($query)){
		$fieldname == $rt['Column_name'] && $fieldindex = 1;
	}
	if ('add' == $type) {
		if ($fieldindex) {
			Showmsg('field_key_have_exists');
		} else {
			$db->query("ALTER TABLE $tablename ADD INDEX ($fieldname)");
		}
	} else {
		if (empty($fieldindex)) {
			Showmsg('field_key_not_exists');
		} else {
			$db->query("ALTER TABLE $tablename DROP INDEX $fieldname");
		}
	}
	$actid = $categoryService->getCateIdByModelId($actmid);

	echo "success\t$actid\t$actmid";
	ajax_footer();
} elseif ('delmodel' == $action) {//删除活动子分类
	define('AJAX',1);
	$actid = $categoryService->getCateIdByModelId($actmid);
	if (!$actid) {
		echo 'the_model_not_exist';
		ajax_footer();
	}
	$count = $categoryService->countModelByCateId($actid);
	if ($count == 1) {
		echo 'model_mustone';
		ajax_footer();
	}

	deleteTopicAndModelDataByActmid($actmid);
	updateCacheActivity();

	echo "success\t";
	ajax_footer();
}

/**
 * 获取保存serialize的字段规则
 * @param string $fieldtype 字段类型
 * @param array $rules 规则
 * @return string
 */
function getFieldRules($fieldtype, $rules)  {
	$rules || $rules = array();
	if ($fieldtype == 'select' || $fieldtype == 'radio' || $fieldtype == 'checkbox') {
		foreach(explode("\n",stripslashes($rules['selection'])) as $key => $rule) {
			if(strpos($rule,'=') == false){
				Showmsg('field_rules_error');
			}
		}
		$s_rules = addslashes(serialize(explode("\n",stripslashes($rules['selection']))));
	} elseif ($fieldtype == 'number') {
		if (!$rules['min'] && $rules['max'] || $rules['min'] && !$rules['max']) {
			Showmsg('field_number_numerror');
		}
		$rules['min'] > $rules['max'] && Showmsg('field_number_error');
		$s_rules = addslashes(serialize(array('minnum' => $rules['min'],'maxnum' => $rules['max'])));
	} elseif ($fieldtype == 'calendar') {
		$rules['precision'] || $rules['precision'] == 'day';
		$s_rules = addslashes(serialize(array('precision' => $rules['precision'])));
	} else {
		$s_rules = '';
	}
	return $s_rules;
}
/**
 * 将活动子分类的字段插入数据库
 * @param int $actmid 活动子分类ID
 * @param string $fieldtype 字段类型
 * @param string $name 字段名
 * @param string $descrip 字段描述
 * @param bool $isDefaultField 是否是默认字段（若是，不修改数据表）
 * @param array $option 其它可选参数
 * @global DB 数据库
 */
function insertActivityFieldToDb($actmid, $fieldtype, $name, $descrip, $isDefaultField = 0, $option = null){
	global $db;
	is_array($option) || $option = array();
	$rules = $option['rules'];
	empty($fieldtype) && Showmsg('fieldtype_not_exists');
	$s_rules = getFieldRules($fieldtype, $rules);
	if (strlen($descrip) > 255) {
		Showmsg('field_descrip_limit');
	}
	$insertColumns = array('name'=>$name, 'actmid' => $actmid,'type'=>$fieldtype,'rules'=>$s_rules,'descrip'=>$descrip);
	$optionalColumns = array('vieworder', 'ifable', 'ifsearch', 'ifasearch', 'threadshow', 'ifmust', 'ifdel', 'mustenable', 'textwidth', 'sectionname', 'issearchable', 'allowthreadshow');
	$isDefaultField && $optionalColumns[] = 'fieldname';
	foreach ($optionalColumns as $column) {
		if (array_key_exists($column, $option)) {
			$insertColumns[$column] = $option[$column];
		}
	}
	$db->update("INSERT INTO pw_activityfield SET " . S::sqlSingle($insertColumns));
	if (!$isDefaultField) { //不是默认字段，则修改特殊字段表名
		$fieldid = $db->insert_id();
		$fieldname = 'field'.$fieldid;
		$db->update("UPDATE pw_activityfield SET fieldname=" . S::sqlEscape($fieldname)." WHERE fieldid=" . S::sqlEscape($fieldid));
		$userDefinedValueTableName = getActivityValueTableNameByActmid($actmid, 1, 1);
		$sql = getFieldSqlByType($fieldtype);
		$db->query("ALTER TABLE $userDefinedValueTableName ADD $fieldname $sql");
	}
}

/**
 * 删除活动子分类的相关数据
 * @param int $actmid
 * @global DB
 */
function deleteTopicAndModelDataByActmid($actmid) {
	global $db;
	//删除子分类
	$db->update("DELETE FROM pw_activitymodel WHERE actmid=" . S::sqlEscape($actmid));
	//删除子分类字段
	$db->update("DELETE FROM pw_activityfield WHERE actmid=" . S::sqlEscape($actmid));

	$userDefinedValueTableName = getActivityValueTableNameByActmid($actmid, 1, 1);
	//删除子分类帖子数据
	$query = $db->query("SELECT tid FROM $userDefinedValueTableName");
	while($rt = $db->fetch_array($query)){
		$tids[] = $rt['tid'];
	}
	$delarticle = L::loadClass('DelArticle', 'forum');
	$delarticle->delTopicByTids($tids);
	//删除子分类自定义表
	$db->query("DROP TABLE IF EXISTS $userDefinedValueTableName");
	//删除子分类默认字段数据
	$defaultValueTableName = getActivityValueTableNameByActmid();
	$db->update("DELETE FROM $defaultValueTableName WHERE actmid=" . S::sqlEscape($actmid));
}

/**
 * 根据活动子分类字段类型获取SQL语句片段
 * @param string $type 字段类型名
 * @return string SQL语句部分
 */
function getFieldSqlByType($type) {
	if (in_array($type,array('number','calendar'))) {
		$sql = "INT(10) UNSIGNED NOT NULL default '0'";
	} elseif (in_array($type,array('radio','select'))){
		$sql = "TINYINT(3) UNSIGNED NOT NULL default '0'";
	} elseif ($type == 'textarea') {
		$sql = "TEXT NOT NULL";
	} else {
		$sql = "VARCHAR(255) NOT NULL";
	}
	return $sql;
}

function getSearchHtml($data) {
	global $vieworder_mark;
	list($name1,$name2,$name3) = $data['name'];

	$textWidth = $data['textwidth'] ? $data['textwidth'] : 20;
	if ($data['vieworder'] == 0) {
		$searchhtml .= "<span>";
		$searchhtml .= $name1 ? $name1."：".$name2 : '';
	} elseif ($data['vieworder'] != 0) {
		if ($vieworder_mark != $data['vieworder']) {
			if ($vieworder_mark != 0 && $vieworder_mark) $searchhtml .= "</span>";
			$searchhtml .= "<span>";

			$searchhtml .= $name1 ? $name1."：".$name2 : '';
		} elseif ($vieworder_mark == $data['vieworder']) {
			$searchhtml .= $name1 ? $name1 : '';
		}
	}

	if (in_array($data['type'],array('radio','select'))) {
		$searchhtml .= "<select name=\"field[$data[fieldname]]\" class=\"input\"><option value=\"\"></option>";
		foreach($data['rules'] as $sk => $sv){
			$sv_value = substr($sv,0,strpos($sv,'='));
			$sv_name = substr($sv,strpos($sv,'=')+1);
			$selected = '';
			if ($sv_value == $data['fieldvalue']) $selected = 'selected';
			$searchhtml .= "<option value=\"$sv_value\" $selected>$sv_name</option>";
		}
		$searchhtml .= "</select>";
	} elseif ($data['type'] == 'checkbox') {
		foreach($data['rules'] as $ck => $cv){
			$cv_value = substr($cv,0,strpos($cv,'='));
			$cv_name = substr($cv,strpos($cv,'=')+1);
			$checked = '';
			if (strpos(",".implode(",",$data['fieldvalue']).",",",".$cv_value.",") !== false) $checked = 'checked';
			$searchhtml .= "<input type=\"checkbox\" class=\"input\" name=\"field[$data[fieldname]][]\" value=\"$cv_value\" $checked/> $cv_name ";
		}
	} elseif ($data['type'] == 'calendar') {
		$showCalendarJsOption = 'minute' == $data['rules']['precision'] ? 1 : 0;
		$searchhtml .= "<input id=\"calendar_start_$data[fieldname]\" type=\"text\" class=\"input\" name=\"field[$data[fieldname]]\" value=\"{$data[fieldvalue]}\" onclick=\"ShowCalendar(this.id,$showCalendarJsOption)\" size=\"$textWidth\" /><script type=\"text/javascript\" src=\"js/date.js\"></script>";
	} elseif ($data['type'] == 'range') {
		$searchhtml .= "<input type=\"text\" size=\"$textWidth\" class=\"input\" name=\"field[$data[fieldname]][min]\" value=\"{$data[fieldvalue][min]}\"/> - <input type=\"text\" size=\"$textWidth\" class=\"input\" name=\"field[$data[fieldname]][max]\" value=\"{$data[fieldvalue][max]}\"/>";
	} else {
		$searchhtml .= "<input type=\"text\" size=\"$textWidth\" name=\"field[$data[fieldname]]\" class=\"input\" value=\"$data[fieldvalue]\">";
	}
	if ($data['vieworder'] == 0) {
		$searchhtml .= $name3."</span>";
	} elseif ($data['vieworder'] != 0) {
		$searchhtml .= $name3;
		$vieworder_mark = $data['vieworder'];
	}
	return $searchhtml;
}
?>