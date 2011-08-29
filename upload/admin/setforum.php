<?php
!function_exists('adminmsg') && exit('Forbidden');
S::gp(array('adminitem'));
empty($adminitem) && $adminitem = 'setforum';
$basename = "$admin_file?adminjob=setforum&adminitem=$adminitem";
if ($adminitem == 'setforum'){
	$basename .= "&c_type=$c_type";
	//* include_once pwCache::getPath(D_P.'data/bbscache/forumcache.php');
	pwCache::getData(D_P.'data/bbscache/forumcache.php');
	require_once(R_P.'require/updateforum.php');
	list($hidefid,$hideforum) = GetHiddenForum();
	$forumcache .= $hideforum;
	if (empty($action)) {
		$catedb = $forumdb = $subdb1 = $subdb2 = array();
		$space  = '<i class="lower lower_a"></i>';

		$query = $db->query("SELECT fid,fup,type,name,vieworder,forumadmin,f_type,cms FROM pw_forums WHERE cms!='1' ORDER BY vieworder");
		while ($forums = $db->fetch_array($query)) {
			$forums['name'] = Quot_cv(strip_tags($forums['name']));
			$forums['forumadmin'] = trim($forums['forumadmin'],',');
			if ($forums['type'] == 'category') {
				$catedb[$forums['fid']] = $forums;
			} elseif ($forums['type'] == 'forum') {
				$forumdb[$forums['fid']] = $forums;
			} elseif ($forums['type'] == 'sub') {
				$subdb1[$forums['fid']] = $forums;
			} else {
				$subdb2[$forums['fid']] = $forums;
			}
		}
	//$fup_forumcache = $forumcache;
		$fup_forumcache = getForumSelectHtml();
		foreach ($subdb2 as $value) {
			$fup_forumcache = str_replace("<option value=\"{$value['fid']}\">&nbsp;&nbsp; &nbsp; &nbsp;|-  {$value['name']}</option>\r\n",'',$fup_forumcache);
		}
		$threaddb = array();
		foreach ($catedb as $cate) {
			$threaddb[$cate['fid']] = array();
			foreach ($forumdb as $key2 => $forumss) {
				if ($forumss['fup'] == $cate['fid']) {
					$threaddb[$cate['fid']][] = $forumss;
					unset($forumdb[$key2]);
					foreach ($subdb1 as $key3 => $sub1) {
						if ($sub1['fup'] == $forumss['fid']) {
							$threaddb[$cate['fid']][] = $sub1;
							unset($subdb1[$key3]);
							foreach ($subdb2 as $key4 => $sub2) {
								if ($sub2['fup'] == $sub1['fid']) {
									$threaddb[$cate['fid']][] = $sub2;
									unset($subdb2[$key4]);
								}
							}
						}
					}
				}
			}
		}
		$forum_L = array();
		if ($forumdb) {
			foreach ($forumdb as $value) {
				$forum_L[] = $value;
			}
		}
		if ($subdb1) {
			foreach ($subdb1 as $value) {
				$forum_L[] = $value;
			}
		}
		if ($subdb2) {
			foreach ($subdb2 as $value) {
				$forum_L[] = $value;
			}
		}
		$ajaxurl = EncodeUrl($basename);
		include PrintEot('setforum');exit;

} elseif ($action == 'searchforum') {
	S::gp(array('keyword'));
	$keyword = trim($keyword);
	
	$result = array();
	if ($keyword) {
		$query = $db->query("SELECT fid,name FROM pw_forums WHERE cms!='1' AND name LIKE ".S::sqlEscape($keyword . '%')." ORDER BY vieworder");
		while ($rt = $db->fetch_array($query)) {
			$result[] = array(
				'forumName' => $rt['name'],
				'url' => $basename . "&action=edit&fid=" . $rt['fid'],
			);
		}
	}
	
	$result	= pwJsonEncode($result);
	echo "success\t".$result;
	ajax_footer();
	
} elseif ($action == 'addforum') {
	S::gp(array('fup','forumnum','ifsave'));
	S::gp(array('name'),'P',0);
	if (empty($_POST['step'])) {
		if (!empty($name)) {
			/*
			$db->update("INSERT INTO pw_forums SET " . S::sqlSingle(array(
				'fup'	=> 0,
				'type'	=> 'category',
				'name'	=> $name,
				'f_type'=> 'forum',
				'cms'	=> 0,
				'ifhide'=> 1,
				'allowtype'=> 3
			)));
			*/
			pwQuery::insert('pw_forums', array(
				'fup'	=> 0,
				'type'	=> 'category',
				'name'	=> $name,
				'f_type'=> 'forum',
				'cms'	=> 0,
				'ifhide'=> 1,
				'allowtype'=> 3
			));
			$fid = $db->insert_id();
			$db->update("INSERT INTO pw_forumdata SET fid=".S::sqlEscape($fid));
			//* P_unlink(D_P.'data/bbscache/c_cache.php');
			pwCache::deleteData(D_P.'data/bbscache/c_cache.php');
			updatecache_f();
			ObHeader("$basename&action=addforum&fup=$fid");
		} elseif (!empty($fup)) {
			if(empty($forum[$fup]) || $forum[$fup]['type'] == 'sub2' ) {
				adminmsg('fup_empty');
			}
			$checked = $ifsave == 1 ? 'checked' : '';
			empty($forumnum) && $forumnum = 5;
			require_once(R_P."require/forum.php");
			$setfid_style = getstyles($style);
		}
		include PrintEot('setforum');exit;
	} else {
		S::gp(array('vieworder','forumadmin','style','keywords','descrip','logo','ifsave','addtype'));
		S::gp(array('descrip'),'P',0);
		$fidArr = array(); //存放新增的版块id数组
		$forumtype = $forum[$fup]['type'] == 'category' ? 'forum' : ($forum[$fup]['type'] == 'forum' ? 'sub' : 'sub2');
		if ($forum[$fup]['type'] != 'category') {
			$fupset = $db->get_one("SELECT f.allowhide,f.allowsell,f.allowtype,f.copyctrl,f.viewsub,f.allowvisit,f.allowpost,f.allowrp,f.allowdownload,f.allowupload,f.f_type,f.f_check,f.cms,f.ifhide,fe.creditset,fe.forumset FROM pw_forums f LEFT JOIN pw_forumsextra fe USING(fid) WHERE f.fid=".S::sqlEscape($fup));
			S::slashes($fupset);
			@extract($fupset,EXTR_OVERWRITE);
		}
		foreach($name as $key => $value) {
			if(empty($value)) continue;
			$value     = str_replace('<iframe','&lt;iframe',$value);
			$descrip[$key]  = str_replace('<iframe','&lt;iframe',$descrip[$key]);
			$keywords[$key] = S::escapeChar($keywords[$key]);
//			strlen($descrip[$key])>250 && adminmsg('descrip_long');
			$newadmin= array();
			$str_admin = '';
			$admin_a  = explode(",",$forumadmin[$key]);
			foreach ($admin_a as $aid=>$avalue) {
				$avalue = trim($avalue);
				if ($avalue && !in_array($avalue,$newadmin)) {
					$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
					$mb = $userService->getUserIdByUserName($avalue);
					if ($mb) {
						$newadmin[] = $avalue;
					} else {
						$errorname .= $avalue.',';
					}
				}
			}
			if ($newadmin) {
				$newadmin = implode(',',$newadmin);
				$str_admin = ','.$newadmin.',';
			} else {
				$str_admin = '';
			}
			if($forum[$fup]['type'] != 'category' && $ifsave[$key] == 1) {
				/*
				$db->update("INSERT INTO pw_forums SET " . S::sqlSingle(array(
					'fup'			=> $fup,
					'type'			=> $forumtype,
					'logo'			=> $logo[$key],
					'name'			=> $value,
					'descrip'		=> $descrip[$key],
					'keywords'		=> $keywords[$key],
					'vieworder'		=> $vieworder[$key],
					'forumadmin'    => $str_admin,
					'style'			=> $style[$key],
					'allowhide'		=> $allowhide,
					'allowsell'		=> $allowsell,
					'allowtype'		=> $allowtype,
					'copyctrl'		=> $copyctrl,
					'viewsub'		=> $viewsub,
					'allowvisit'	=> $allowvisit,
					'allowpost'		=> $allowpost,
					'allowrp'		=> $allowrp,
					'allowdownload'	=> $allowdownload,
					'allowupload'	=> $allowupload,
					'f_type'		=> $f_type,
					'f_check'		=> $f_check,
					'cms'			=> $cms,
					'ifhide'		=> $ifhide
				)));
				*/
				pwQuery::insert('pw_forums', array(
					'fup'			=> $fup,
					'type'			=> $forumtype,
					'logo'			=> $logo[$key],
					'name'			=> $value,
					'descrip'		=> $descrip[$key],
					'keywords'		=> $keywords[$key],
					'vieworder'		=> $vieworder[$key],
					'forumadmin'    => $str_admin,
					'style'			=> $style[$key],
					'allowhide'		=> $allowhide,
					'allowsell'		=> $allowsell,
					'allowtype'		=> $allowtype,
					'copyctrl'		=> $copyctrl,
					'viewsub'		=> $viewsub,
					'allowvisit'	=> $allowvisit,
					'allowpost'		=> $allowpost,
					'allowrp'		=> $allowrp,
					'allowdownload'	=> $allowdownload,
					'allowupload'	=> $allowupload,
					'f_type'		=> $f_type,
					'f_check'		=> $f_check,
					'cms'			=> $cms,
					'ifhide'		=> $ifhide
				));
				$fid = $db->insert_id();
				
				if ($creditset || $forumset) {
					$db->update("INSERT INTO pw_forumsextra SET " . S::sqlSingle(array(
						'fid'		=> $fid,
						'creditset'	=> $creditset,
						'forumset'	=> $forumset
					)));
				}
			} else {
				$f_type = $forum[$fup]['f_type'] == 'hidden' ? 'hidden' : 'forum';
				/*
				$db->update("INSERT INTO pw_forums SET " . S::sqlSingle(array(
					'fup'			=> $fup,
					'type'			=> $forumtype,
					'logo'			=> $logo[$key],
					'name'			=> $value,
					'descrip'		=> $descrip[$key],
					'keywords'		=> $keywords[$key],
					'vieworder'		=> $vieworder[$key],
					'forumadmin'    => $str_admin,
					'style'			=> $style[$key],
					'f_type'		=> $f_type,
					'cms'			=> 0,
					'ifhide'		=> 1,
					'allowtype'		=> 3
				)));
				*/
				pwQuery::insert('pw_forums', array(
					'fup'			=> $fup,
					'type'			=> $forumtype,
					'logo'			=> $logo[$key],
					'name'			=> $value,
					'descrip'		=> $descrip[$key],
					'keywords'		=> $keywords[$key],
					'vieworder'		=> $vieworder[$key],
					'forumadmin'    => $str_admin,
					'style'			=> $style[$key],
					'f_type'		=> $f_type,
					'cms'			=> 0,
					'ifhide'		=> 1,
					'allowtype'		=> 3
				));
				$fid = $db->insert_id();

				$forumset = serialize(array(
					'orderway'		=> 'lastpost',
					'asc'			=> 'DESC',
					'replayorder'	=> '1',
					'addnotice'		=> '1',
					'relatedcon'	=> '1',
					'allowtpctype'	=> '1',
					'uploadset'		=> '1',
					'allowtpctype'	=> 'money			0',
					'thumbsize'		=> '300	300'
				));
				$db->update("INSERT INTO pw_forumsextra SET " . S::sqlSingle(array(
					'fid'		=> $fid,
					'creditset'	=> '',
					'forumset'	=> $forumset
				)));
			}
			$fidArr[]  = $fid; //获取新增的版块ID
			$db->update("INSERT INTO pw_forumdata SET fid=".S::sqlEscape($fid));
		}
		//版块置顶操作
		updateForumPostTop($fidArr);
		//* P_unlink(D_P.'data/bbscache/c_cache.php');
		pwCache::deleteData(D_P.'data/bbscache/c_cache.php');
		updatecache_f();
		$forumtype != 'category' && updatetop();
		if($addtype == 1){
			ObHeader("$basename&action=edit&fid=$fid");
		}else{
			adminmsg('operate_success');
		}
	}
} elseif ($_POST['action'] == 'editforum') {

	S::gp(array('forumadmin'), 'P', 0);
	S::gp(array('order'), 'P', 2);
	$errorname = array();
	$forumdb = $db->query("SELECT fid,forumadmin,vieworder FROM pw_forums WHERE cms!='1'");
	while ($foruminfo = $db->fetch_array($forumdb)) {
		$pwSQL = $admin_a = $admin_n = $admin_d = array();
		if ($foruminfo['forumadmin'] != $forumadmin[$foruminfo['fid']] && $foruminfo['forumadmin'] != ','.$forumadmin[$foruminfo['fid']].',') {
			$admin_a = explode(',',$forumadmin[$foruminfo['fid']]);
			if ($admin_a) {
				$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
				$members = $userService->getByUserNames($admin_a);
				foreach ($members as $member) {
					$admin_d[] = $member['username'];
				}
				foreach ($admin_a as $value) {
					if (S::inArray($value,$admin_d)) {
						$admin_n[] = $value;
					}
				}
			}
			if ($admin_n) {
				$pwSQL['forumadmin'] = ','.implode(',',$admin_n).',';
			} else {
				$pwSQL['forumadmin'] = '';
			}
			$errorname = array_merge($errorname,array_diff($admin_a,$admin_n));
		}

		if ($order[$foruminfo['fid']] != $foruminfo['vieworder']) {
			$pwSQL['vieworder'] = $order[$foruminfo['fid']];
		}
		if ($pwSQL) {
			//$db->update("UPDATE pw_forums SET".S::sqlSingle($pwSQL)."WHERE fid=".S::sqlEscape($foruminfo['fid'],false));
			pwQuery::update('pw_forums', 'fid=:fid', array($foruminfo['fid']), $pwSQL);
		}
	}
	updatecache_f();
	updateadmin();
	$errorname && $errorname = implode(',',$errorname);
	adminmsg($errorname ? 'user_not_exists' : 'operate_success');

} elseif ($action == 'delete') {

	S::gp(array('fid'));

	$count = $db->get_value("SELECT COUNT(*) AS count FROM pw_forums WHERE fup=".S::sqlEscape($fid)." AND type<>'category'");
	if ($count) {
		adminmsg('forum_havesub');
	}
	if (empty($_POST['step'])) {

		include PrintEot('setforum');exit;

	} else {
		delforum($fid);
		//* P_unlink(D_P.'data/bbscache/c_cache.php');
		pwCache::deleteData(D_P.'data/bbscache/c_cache.php');
		updatecache_f();
		adminmsg('operate_success');
	}
} elseif ($action == 'edit') {
	S::gp(array('fid'),'GP',2);
	S::gp(array('c_type'),'GP');
	if (!$fid) {
		$basename = "$admin_file?adminjob=setforum&action=edit&c_type=".$c_type;
		include PrintEot('setforum');exit;
	}
	if (empty($_POST['step'])) {
		$subdb2 = array();
		$query = $db->query("SELECT fid,name FROM pw_forums WHERE cms!='1' AND type='sub2' ORDER BY vieworder");
		while ($forums = $db->fetch_array($query)) {
			$subdb2[] = $forums;
		}
		//$fup_forumcache = $forumcache;
                $fup_forumcache = getForumSelectHtml();
		foreach ($subdb2 as $value) {
			$fup_forumcache = str_replace("<option value=\"{$value['fid']}\">&nbsp;&nbsp; &nbsp; &nbsp;|-  {$value['name']}</option>\r\n",'',$fup_forumcache);
		}
		!in_array($c_type, array('basic','property','recommended','threadtype','usergroup','usercredit')) && $c_type = 'basic';

		@extract($db->get_one("SELECT f.*,fe.forumset FROM pw_forums f LEFT JOIN pw_forumsextra fe USING(fid) WHERE f.fid=" . S::sqlEscape($fid)));
		$forumset = unserialize($forumset);
		$forumset['newtime']  /= 60;
		//$forumset['rvrcneed'] /= 10;
		
		$forumset['addtpctype'] ? $addtpctype_Y = 'checked' : $addtpctype_N = 'checked';
		$forumset['allowtpctype'] ? $allowtpctype_Y = 'checked' : $allowtpctype_N = 'checked';
		$forumset['ifrelated'] ? $ifrelated_Y = 'checked' : $ifrelated_N = 'checked';
		${'r_'.$forumset['relatedcon']} = 'selected';

		$name = str_replace("<","&lt;",$name);
		$name = str_replace(">","&gt;",$name);
		$name = str_replace('"',"&quot;",$name);
		$name = str_replace("'","&#39;",$name);

		require_once(R_P."require/forum.php");
		$setfid_style = getstyles($style);

		if ($type <> 'category') {
			require_once(R_P.'require/credit.php');
			list($uploadcredit,$uplodmoney,$downloadmoney,$downloadimg) = explode("\t",$forumset['uploadset']);
			ifcheck($downloadimg,'img');
			ifcheck($forumset['dig'],'dig');
			ifcheck($forumset['inspect'],'inspect');
			ifcheck($forumset['watermark'],'watermark');
			ifcheck($forumset['allowencode'],'allowencode');
			ifcheck($forumset['anonymous'],'anonymous');
			ifcheck($forumset['commend'],'commend');
			ifcheck($forumset['rate'],'rate');
			ifcheck($forumset['overprint'],'overprint');
			ifcheck($forumset['viewpic'],'viewpic');
			ifcheck($forumset['iftucool'],'iftucool');
			ifcheck($forumset['iftucooldefault'],'iftucooldefault');
			ifcheck($forumset['iftucoolbrowse'],'iftucoolbrowse');
			ifcheck($forumset['tucoolpic'],'tucoolpic');
			ifcheck($forumset['postedittime'],'postedittime');
			ifcheck($forumset['viewcolony'],'viewcolony');
			ifcheck($forumset['ifcolonycate'],'ifcolonycate');
			ifcheck($forumset['addnotice'],'addnotice');
			list($rw_time,$rw_b_val,$rw_a_val,$rw_credit) = explode("\t",$forumset['rewarddb']);
			
			$rw_credit = explode(',',$rw_credit);

			for ($i = 0;$i < 6;$i++) {
				${'allowtype_'.pow(2,$i)} = $allowtype & pow(2,$i) ? 'checked' : '';
			}		
			
			for ($i = 1;$i < 4;$i++) {
				${'recycle_'.pow(2,$i)} = $forumset['recycle'] & pow(2,$i) ? 'checked' : '';
			}
			${'autocommend_'.$forumset['autocommend']}='selected';
			${'sel_'.$forumset['orderway']}='selected';
			${'sel_'.$forumset['asc']}='selected';
			/* 版块默认排序方式 默认关闭*/
			$replayorder_asc = $replayorder_desc = '';
			if ($forumset['replayorder'] == '1') {
				$replayorder_asc = 'selected';
			}elseif($forumset['replayorder'] == '2'){
				$replayorder_desc = 'selected';
			}else{
				$replayorder_asc = 'selected';
			}
			$name	 = str_replace(array('<','>','"',"'"),array("&lt;","&gt;","&quot;","&#39;"),$name);
			$descrip = str_replace(array('<','>'),array("&lt;","&gt;"),$descrip);

			//ifcheck($viewsub,'viewsub');
			ifcheck($allowhide,'allowhide');
			ifcheck($allowsell,'allowsell');
			ifcheck($copyctrl,'copyctrl');
			ifcheck($showsub,'showsub');
			ifcheck($ifhide,'ifhide');
			
			($viewsub == 1 || $viewsub == 0) && $viewsub_1 = 'checked';
			($viewsub == 2 || $viewsub == 0) && $viewsub_2 = 'checked';

			$logotype = !empty($logo) && strpos($logo,'http://') === false && file_exists($attachdir.'/'.$logo) ? 'upload' : 'url';
			if ($logotype == 'upload') {
				$logotype_upload = 'checked';
				$logo && $logoimg = $attachpath.'/'.$logo;
				$logo = '';
			} else {
				$logotype_url = 'checked';
				$logo && $logoimg = $logo;
			}
			${'check_'.$f_check} = 'checked';
			${'ftype_'.$f_type} = 'checked';
			$fup_forumcache = str_replace("<option value=\"$fup\">","<option value=\"$fup\" selected>",$fup_forumcache);
			$usergroup  = "<ul class='list_A list_120 cc'>";
			foreach ($ltitle as $key => $value) {
				if ($key == 1 || $key == 2) continue;
				$htm_tr='';$num++;$num%5==0?$htm_tr='':'';
				$usergroup.="<li><input type='checkbox' name='permit[]' value='$key' _{$key}_>$value</li>$htm_tr";
			}
			$usergroup  .= "</ul>";
			$viewvisit	 = str_replace('permit','allowvisit',$usergroup);
			$viewread	 = str_replace('permit','allowread',$usergroup);
			$viewpost    = str_replace('permit','allowpost',$usergroup);
			$viewrp      = str_replace('permit','allowrp',$usergroup);
			$viewupload  = str_replace('permit','allowupload',$usergroup);
			$viewdownload= str_replace('permit','allowdownload',$usergroup);
			$visitper = explode(",",$allowvisit);
			$readper  = explode(",",$allowread);
			$postper  = explode(",",$allowpost);
			$rpper	  = explode(",",$allowrp);
			$uploadper= explode(",",$allowupload);
			$downper  = explode(",",$allowdownload);
			$t_type = (int)$t_type;
			${'t_type_'.$t_type}='checked';
			foreach ($visitper as $value)
				$viewvisit = str_replace("_{$value}_",'checked',$viewvisit);
			foreach ($readper as $value)
				$viewread  = str_replace("_{$value}_",'checked',$viewread);
			foreach ($postper as $value)
				$viewpost  = str_replace("_{$value}_",'checked',$viewpost);
			foreach ($rpper as $value)
				$viewrp = str_replace("_{$value}_",'checked',$viewrp);
			foreach ($uploadper as $value)
				$viewupload = str_replace("_{$value}_",'checked',$viewupload);
			foreach ($downper as $value)
				$viewdownload = str_replace("_{$value}_",'checked',$viewdownload);

			//主题分类
			$query = $db->query("SELECT id,name,logo,vieworder,upid,ifsys FROM pw_topictype WHERE fid=".S::sqlEscape($fid)." ORDER BY vieworder ");
			$t_typedbnum = 1;
			while ($rt = $db->fetch_array($query)) {
				$rt['name'] = str_replace(array('<','>','"',"'"),array("&lt;","&gt;","&quot;","&#39;"),$rt['name']);
				$rt['logo'] = str_replace(array('<','>','"',"'"),array("&lt;","&gt;","&quot;","&#39;"),$rt['logo']);
				if($rt['upid'] == 0) {
					$typedb[$rt['id']] = $rt;
				} else {
					$subtypedb[$rt['id']] = $rt;
				}
				$t_typedbnum++;
			}

			//分类主题类型
			$topicdb = $modeldb = array();
			//* @include_once pwCache::getPath(D_P. 'data/bbscache/topic_config.php');
			pwCache::getData(D_P. 'data/bbscache/topic_config.php');
			foreach ($topiccatedb as $key => $value) {
				if ($value['ifable'] == 1) {
					$topicdb[$key]['cateid'] = $value['cateid'];
					$topicdb[$key]['name'] = $value['name'];
				}
			}
			$jsoncateids = pwJsonEncode($topicdb);

			//分类模型
			foreach ($topicmodeldb as $key => $value) {
				if ($value['ifable'] == 1) {
					$modeldb[$value['cateid']][$key]['cateid'] = $value['cateid'];
					$modeldb[$value['cateid']][$key]['modelid'] = $value['modelid'];
					$modeldb[$value['cateid']][$key]['name'] = $value['name'];
				}
			}

			//活动主题分类
			$activitycatedb = $activitymodeldb = array();
			//* @include_once pwCache::getPath(D_P. 'data/bbscache/activity_config.php');
			pwCache::getData(D_P. 'data/bbscache/activity_config.php');
			foreach ($activity_catedb as $key => $value) {
				if ($value['ifable'] == 1) {
					$activitycatedb[$key]['actid'] = $value['actid'];
					$activitycatedb[$key]['name'] = $value['name'];
				}
			}
			$jsonactids = pwJsonEncode($topicdb);

			//活动二级分类
			foreach ($activity_modeldb as $key => $value) {
				if ($value['ifable'] == 1) {
					$activitymodeldb[$value['actid']][$key]['actid'] = $value['actid'];
					$activitymodeldb[$value['actid']][$key]['actmid'] = $value['actmid'];
					$activitymodeldb[$value['actid']][$key]['name'] = $value['name'];
				}
			}
		
			$thumbSelect = array($forumset['ifthumb']=>'selected');
			list($forumset['width'],$forumset['height']) = explode("\t",$forumset['thumbsize']);
			$style = $forumset['ifthumb'] == 1 ? 'display:' : 'display:none';
			
			if($forumset['ifthumb'] == 0){
				$thumbstyle = array(0=>'style="display:;"',1=>'style="display:none"',2=>'style="display:none"');
			}elseif($forumset['ifthumb'] == 1 ){
				$thumbstyle = array(0=>'style="display:none"',1=>'style="display:;"',2=>'style="display:none"');
			}elseif($forumset['ifthumb'] == 2){
				$thumbstyle = array(0=>'style="display:none"',1=>'style="display:none"',2=>'style="display:;"');
			}
				
		
			//团购
			//* @include_once pwCache::getPath(D_P.'data/bbscache/postcate_config.php');
			pwCache::getData(D_P.'data/bbscache/postcate_config.php');

			require_once(R_P.'require/credit.php');
			$creditset = $db->get_value("SELECT creditset FROM pw_forumsextra WHERE fid=".S::sqlEscape($fid));
			$creditset = $creditset ? unserialize($creditset) : array();

		}
		$ajaxurl = EncodeUrl($basename);
		include PrintEot('setforum');exit;

	} elseif ($_POST['step'] == 2) {
		$forum = $db->get_one("SELECT type,fup,forumadmin,logo FROM pw_forums WHERE fid=".S::sqlEscape($fid));
		S::gp(array('name','descrip','metadescrip'),'P',0);
		S::gp(array('vieworder','dirname','style','across','keywords','c_type'),'P');
		Cookie('thisPWTabs', $c_type , 'F', false);
		$name     = str_replace('<iframe','&lt;iframe',$name);
		$descrip  = str_replace('<iframe','&lt;iframe',$descrip);
		$metadescrip = str_replace('<iframe','&lt;iframe',$metadescrip);
		$keywords = S::escapeChar($keywords);
		//去掉版块简介字数限制@modify panjl@2010-11-2
		//strlen($descrip)>250 && adminmsg('descrip_long');
		strlen($metadescrip)>250 && adminmsg('descrip_long', $basename . $c_type . '&action=edit&fid=' . $fid);
		
		if ($forum['type'] == 'category') {
			/*
			$db->update("UPDATE pw_forums SET " . S::sqlSingle(array(
				'name'		=> $name,
				'vieworder'	=> $vieworder,
				'dirname'	=> $dirname,
				'style'		=> $style,
				'across'	=> $across,
				'cms'		=> $cms
			)) . " WHERE fid=".S::sqlEscape($fid));
			*/
			pwQuery::update('pw_forums', 'fid=:fid', array($fid), array(
				'name'		=> $name,
				'vieworder'	=> $vieworder,
				'dirname'	=> $dirname,
				'style'		=> $style,
				'across'	=> $across,
				'cms'		=> $cms
			));
		} else {

			S::gp(array('creditdb','forumsetdb','uploadset','rewarddb','cfup','ffup','showsub','ifhide', 'viewsub_1','viewsub_2','allowhide','allowsell','copyctrl','f_check','password','allowvisit','allowread', 'allowpost','allowrp','allowupload','allowdownload','otherfid','otherforum','allowtime','allowtype', 'recycle','forumsell','sdate','cprice','rprice','logotype','logo_upload','logo_url','ifdellogo','t_view_db','new_t_view_db','t_logo_db','new_t_logo_db','t_sys_db','new_t_sys_db','new_t_sub_logo_db','new_t_sub_view_db','new_t_sub_sys_db','t_type','modelid','pcid','actmid'),'P');
			S::gp(array('t_db','new_t_db','new_t_sub_db','f_type'),'P',0);
			S::gp(array('ifcms'));
			
			$iftucool = intval($forumsetdb['iftucool']);
			$tucoolpic = intval($forumsetdb['tucoolpic']);
			if($iftucool && $tucoolpic < 1){
				adminmsg("主楼图片数不能小于1","$admin_file?adminjob=setforum&action=edit&fid=$fid&c_type=$c_type");
				
			}
			
			//主题分类

			//更新原有的分类
			foreach ($t_db as $key => $value) {
				$db->update("UPDATE pw_topictype SET " . S::sqlSingle(array(
					'name'			=> $value,
					'vieworder'		=> $t_view_db[$key],
					'logo'			=> $t_logo_db[$key],
					'ifsys'			=> isset($t_sys_db[$key]) ? $t_sys_db[$key] : 0
				)) . " WHERE id=".S::sqlEscape($key));
			}

			//增加新分类
			foreach ($new_t_db as $key => $value) {
				if(empty($value)) continue;
				$typedb[] = array ('fid' => $fid,'name' => $value,'logo'=>$new_t_logo_db[$key],'vieworder'=>$new_t_view_db[$key], 'ifsys' => isset($new_t_sys_db[$key]) ? $new_t_sys_db[$key] : 0);
			}
			if ($typedb) {
				$db->update("REPLACE INTO pw_topictype (fid,name,logo,vieworder,ifsys) VALUES " . S::sqlMulti($typedb));
			}
			//增加二级新分类
			foreach ($new_t_sub_db as $key => $value) {
				foreach ($value as $k => $v) {
					if (empty($v)) continue;
					$subtypedb[] = array ('fid' => $fid,'name' => $v,'logo'=>$new_t_sub_logo_db[$key][$k],'vieworder'=>$new_t_sub_view_db[$key][$k],'upid'=>$key, 'ifsys' => isset($new_t_sub_sys_db[$key][$k]) ? $new_t_sub_sys_db[$key][$k] : 0);
				}
			}
			if ($subtypedb) {
				$db->update("REPLACE INTO pw_topictype (fid,name,logo,vieworder,upid,ifsys) VALUES " . S::sqlMulti($subtypedb));
			}
			$forumsetdb['newtime'] *= 60;
			foreach ($forumsetdb as $key => $value) {
				if ($key == 'link') {
					$forumsetdb['link'] = str_replace(array('"',"'",'\\'),array('','',''),$value);
				} elseif ($key == 'recycle') {
					$forumsetdb['recycle'] = array_sum($value);
				} elseif (!in_array($key,array('orderway','asc','replayorder','commendlist','chat','relatedcon','relatedcustom','iftucooldefault'))) {
					$forumsetdb[$key] = (int)$value;
				}
			}
			$forumsetdb['contentminlen'] = $forumsetdb['contentminlen'] < 0 ? 0 : $forumsetdb['contentminlen'];

			$sellprice = array();
			foreach ($sdate as $key => $value) {
				if ($value && ($cprice[$key] || $rprice[$key])) {
					$sellprice[$value] = array('cprice' => $cprice[$key], 'rprice' => $rprice[$key]);
				}
			}

			$i = 0;
			foreach ($forumsetdb['relatedcustom']['title'] as $key => $value) {
				if ($value) {
					$forumsetdb['relatedcustom'][$i]['title'] = stripslashes($value);
					$forumsetdb['relatedcustom'][$i]['url'] = $forumsetdb['relatedcustom']['url'][$key];
					$i++;
				}
			}
			unset($forumsetdb['relatedcustom']['title']);
			unset($forumsetdb['relatedcustom']['url']);
			ksort($sellprice);
			$forumsetdb['sellprice'] = $sellprice;
			$forumsetdb['uploadset'] = implode("\t",$uploadset);
			//$forumsetdb['rvrcneed'] *= 10;
			
			$rewarddb[3] = implode(',',$rewarddb[3]);
			$reward = array();
			foreach ($rewarddb as $key => $v){
				if($key !== 3 && $v !== 0){
					$v = intval($v);
					if($v < 1) $v = 0;
				}	
				$reward[] = $v;
			} 
			$forumsetdb['rewarddb']  = implode("\t",$reward);
			$forumsetdb['allowtime'] = $allowtime ? ",".implode(",",$allowtime)."," : '';
			//论坛附件缩略图控制
			if($forumsetdb['ifthumb'] == 0){
				$forumsetdb['thumbsize'] = $db_athumbsize;
			}elseif($forumsetdb['ifthumb'] == 1 ){
				$width  = intval($forumsetdb['thumbwidth']);
				$height = intval($forumsetdb['thumbheight']);
				$forumsetdb['thumbsize'] = $width."\t".$height;
			}elseif($forumsetdb['ifthumb'] == 2){
				$forumsetdb['thumbsize'] = "0\t0";
			}
			unset($forumsetdb['thumbwidth']);
			unset($forumsetdb['thumbheight']);
			$forumsextradb = serialize($forumsetdb);
			$db->pw_update(
				"SELECT fid FROM pw_forumsextra WHERE fid=".S::sqlEscape($fid),
				"UPDATE pw_forumsextra SET forumset=".S::sqlEscape($forumsextradb,false)."WHERE fid=".S::sqlEscape($fid),
				"INSERT INTO pw_forumsextra SET forumset=".S::sqlEscape($forumsextradb,false).',fid='.S::sqlEscape($fid)
			);
			foreach ($creditdb as $key => $value) {
				foreach ($value as $k => $v) {
					if (is_numeric($v)) {
						$creditdb[$key][$k] = round($v,$k == 'rvrc' ? 1 : 0);
					} else {
						$creditdb[$key][$k] = '';
					}
				}
			}
			$creditset = $creditdb ? serialize($creditdb) : '';

			$db->pw_update(
				"SELECT fid FROM pw_forumsextra WHERE fid=".S::sqlEscape($fid),
				"UPDATE pw_forumsextra SET creditset=".S::sqlEscape($creditset,false).'WHERE fid='.S::sqlEscape($fid),
				"INSERT INTO pw_forumsextra SET creditset=".S::sqlEscape($creditset,false).',fid='.S::sqlEscape($fid)
			);
			$fup = $cms == '1' ? $cfup : $ffup;
			$fup == $fid && adminmsg('setforum_fupsame',$basename . $c_type . '&action=edit&fid=' . $fid);
			if (!$fup || !is_numeric($fup)) {
				$fupfid = $db->get_one("SELECT fid FROM pw_forums WHERE type='category' ORDER BY fid LIMIT 1");
				$fup = $fupfid['fid'];
			}
			if (!empty($password) && strlen($password) != 32) {
				$password = md5($password);
			}
			$allowvisit		&& $allowvisit		= ','.implode(",",$allowvisit).',';
			$allowread		&& $allowread		= ','.implode(",",$allowread).',';
			$allowpost		&& $allowpost		= ','.implode(",",$allowpost).',';
			$allowrp		&& $allowrp			= ','.implode(",",$allowrp).',';
			$allowupload	&& $allowupload		= ','.implode(",",$allowupload).',';
			$allowdownload	&& $allowdownload	= ','.implode(",",$allowdownload).',';
			$allowtype = array_sum($allowtype);

			$rt = $db->get_one("SELECT type,cms FROM pw_forums WHERE fid=".S::sqlEscape($fup));
			if ($rt['type'] == 'category') {
				$type = 'forum';
			} elseif ($rt['type'] == 'forum') {
				if (($rt['cms'] && !$cms) || (!$rt['cms'] && $cms)) {
					adminmsg('setforum_cms',$basename . $c_type . '&action=edit&fid=' . $fid);
				}
				$type = 'sub';
			} elseif ($rt['type'] == 'sub') {
				$type = 'sub2';
			}

			if ($f_type == 'hidden' && $allowvisit == '') {
				$basename = "$admin_file?adminjob=setforum&action=edit&fid=$fid&c_type=$c_type";
				adminmsg('forum_hidden',$basename . $c_type . '&action=edit&fid=' . $fid);
			}
			//$db_uploadfiletype = !empty($db_uploadfiletype) ? (is_array($db_uploadfiletype) ? $db_uploadfiletype : unserialize($db_uploadfiletype)) : array();
			$db_uploadfiletype = array(
				'gif'  => 2048,				'jpg'  => 2048,
				'jpeg' => 2048,				'bmp'  => 2048,
				'png'  => 2048
			);
			if ($logotype == 'upload') {
				if ($ifdellogo == 1) {
					pwDelatt($forum['logo'],$db_ifftp);
					//$db->update("UPDATE pw_forums SET logo='' WHERE fid=".S::sqlEscape($fid));
					pwQuery::update('pw_forums', 'fid=:fid', array($fid), array('logo' => ''));
					$forum['logo'] = '';
				}
				require_once(R_P.'require/postfunc.php');
				$uploaddb = UploadFile($winduid,'forumlogo');
				$logo = !empty($uploaddb) ? $uploaddb[0]['attachurl'] : $forum['logo'];
			} elseif ($logotype == 'url') {
				$logo = $logo_url;
			}

			$modelids = '';
			foreach ($modelid as $value) {
				$modelids .= $modelids ? ','.$value : $value;
			}
			//团购
			$pcids = '';
			foreach ($pcid as $value) {
				$pcids .= $pcids ? ','.$value : $value;
			}

			//活动
			$actmids = '';
			foreach ($actmid as $value) {
				$actmids .= $actmids ? ','.$value : $value;
			}
			
			if ($viewsub_1 && $viewsub_2) {
				$viewsub = 0;
			} elseif ($viewsub_1) {
				$viewsub = $viewsub_1;
			} elseif ($viewsub_2) {
				$viewsub = $viewsub_2;
			} else {
				$viewsub = 3;
			}
            /*
			$db->update("UPDATE pw_forums SET " . S::sqlSingle(array(
				'fup'		=> $fup,			'type'		=> $type,
				'name'		=> $name,			'vieworder'	=> $vieworder,
				'logo'		=> $logo,			'keywords'	=> $keywords,
				'descrip'	=> $descrip,		'style'		=> $style,
				'metadescrip' => $metadescrip,	'ifcms'		=> $ifcms,
				'across'	=> $across,			'allowhide'	=> $allowhide,
				'allowsell'	=> $allowsell,		'allowtype'	=> $allowtype,
				'copyctrl'	=> $copyctrl,		'password'	=> $password,
				'viewsub'	=> $viewsub,		'allowvisit'=> $allowvisit,
				'allowread'	=> $allowread,		'allowpost'	=> $allowpost,
				'allowrp'	=> $allowrp,		'allowdownload'=> $allowdownload,
				'allowupload' => $allowupload,	'f_type'	=> $f_type,
				'f_check'	=> $f_check,		't_type'	=> $t_type,
				'forumsell'	=> $forumsell,		'cms'		=> $cms,
				'ifhide'	=> $ifhide,			'showsub'	=> $showsub,
				'modelid'	=> $modelids,		'pcid'		=> $pcids,
				'actmids'	=> $actmids

			)) . " WHERE fid=".S::sqlEscape($fid));
			*/
			pwQuery::update('pw_forums', 'fid=:fid', array($fid), array(
				'fup'		=> $fup,			'type'		=> $type,
				'name'		=> $name,			'vieworder'	=> $vieworder,
				'logo'		=> $logo,			'keywords'	=> $keywords,
				'descrip'	=> $descrip,		'style'		=> $style,
				'metadescrip' => $metadescrip,	'ifcms'		=> $ifcms,
				'across'	=> $across,			'allowhide'	=> $allowhide,
				'allowsell'	=> $allowsell,		'allowtype'	=> $allowtype,
				'copyctrl'	=> $copyctrl,		'password'	=> $password,
				'viewsub'	=> $viewsub,		'allowvisit'=> $allowvisit,
				'allowread'	=> $allowread,		'allowpost'	=> $allowpost,
				'allowrp'	=> $allowrp,		'allowdownload'=> $allowdownload,
				'allowupload' => $allowupload,	'f_type'	=> $f_type,
				'f_check'	=> $f_check,		't_type'	=> $t_type,
				'forumsell'	=> $forumsell,		'cms'		=> $cms,
				'ifhide'	=> $ifhide,			'showsub'	=> $showsub,
				'modelid'	=> $modelids,		'pcid'		=> $pcids,
				'actmids'	=> $actmids
			));
			updateforum($fup);
			updateforum($forum['fup']);
		}
		//* P_unlink(D_P.'data/bbscache/c_cache.php');
		pwCache::deleteData(D_P.'data/bbscache/c_cache.php');
		
		$othersql = $otherfids = array();
		$update_f = '';
		if (is_array($otherfid)) {
			$otherfids = S::sqlImplode($otherfid);
		}
		if (is_array($otherforum)) {
			foreach ($otherforum as $key => $value) {
				if ($key === 'forumsetdb' || $key === 'creditset' || $key=== 't_typemain') {
					$update_f = 1;
					continue;
				}
				$othersql[$key] = $$key;
				if($key === 'allowtype'){
					if(is_array($modelid)){
					$othersql['modelid']=$modelids;	
					}
					if(is_array($pcid)){
					$othersql['pcid']=$pcids;	
					}
					if(is_array($actmid)){
					$othersql['actmids']=$actmids;	
					}
				}
			}
			
		}
	/*	var_dump($allowtype);
		var_dump($otherforum);
		var_dump($otherfids);
		var_dump($othersql);exit;*/
		if ($otherforum['ffup']) {
			$fup = $cms == '1' ? $cfup : $ffup;
			doOtherFidsSetFup($fup,$otherfid);
			unset($otherforum['ffup'],$othersql['ffup']);
		}
		if ($othersql && $otherfids) {
			//$db->update("UPDATE pw_forums SET".S::sqlSingle($othersql)."WHERE fid IN($otherfids)");
			pwQuery::update('pw_forums', 'fid IN(:fid)', array($otherfid), $othersql);
			if ($otherforum['t_typemain']) {
				doOtherFidsTopicType($fid,$otherfid);
			}		
		}
		if ($otherfids && $update_f) {
			//* include pwCache::getPath(D_P.'data/bbscache/forum_cache.php');
			pwCache::getData(D_P.'data/bbscache/forum_cache.php');
			foreach ($otherfid as $key => $selfid) {
				if (!$selfid || !is_numeric($selfid) || $selfid == $fid || $forum[$selfid]['type'] == 'category') {
					continue;
				}
				$rt = $db->get_one("SELECT fid,forumset,creditset FROM pw_forumsextra WHERE fid=".S::sqlEscape($selfid));
				if ($rt['fid']) {
					$newforumset = unserialize($rt['forumset']);
					foreach ($forumsetdb as $key => $value) {
						if ($otherforum['forumsetdb'][$key]) {
							$newforumset[$key] = $value;
						} elseif (!isset($newforumset[$key])) {
							$newforumset[$key] = 0;
						}
					}
					$newforumset['ifthumb'] && $newforumset['thumbsize'] = $forumsetdb['thumbsize'];
					if ($rt['creditset']) {
						$newcreditset = (array)unserialize($rt['creditset']);
						foreach ($newcreditset as $key => $value) {
							foreach ($value as $k => $val) {
								if ($otherforum['creditset'][$key][$k]) {
									$newcreditset[$key][$k] = $creditdb[$key][$k];
								}
							}
						}
					} else {
						$newcreditset = array();
						foreach ($creditdb as $key => $value) {
							foreach ($value as $k => $val) {
								if ($otherforum['creditset'][$key][$k]) {
									$newcreditset[$key][$k] = $creditdb[$key][$k];
								} else {
									$newcreditset[$key][$k] = '';
								}
							}
						}
					}

					$newcreditset = serialize($newcreditset);
					$forumset = serialize($newforumset);
					$db->update("UPDATE pw_forumsextra SET forumset=".S::sqlEscape($forumset,false).",creditset=".S::sqlEscape($newcreditset,false)."WHERE fid=".S::sqlEscape($selfid));
				} else {
					$newforumset = array();
					foreach ($forumsetdb as $key => $value) {
						if ($otherforum['forumsetdb'][$key]) {
							$newforumset[$key] = $value;
						} else {
							$newforumset[$key] = 0;
						}
					}
					$newforumset['ifthumb'] && $newforumset['thumbsize'] = $forumsetdb['thumbsize'];
					$newcreditset = array();
					foreach ($creditdb as $key => $value) {
						foreach ($value as $k => $val) {
							if ($otherforum[$key][$k]) {
								$newcreditset[$key][$k] = $creditdb[$key][$k];
							} else {
								$newcreditset[$key][$k] = '';
							}
						}
					}
					$newcreditset = serialize($newcreditset);
					$forumset = serialize($newforumset);
					$db->update("INSERT INTO pw_forumsextra SET forumset=".S::sqlEscape($forumset,false).',creditset='.S::sqlEscape($newcreditset,false).',fid='.S::sqlEscape($selfid));
				}
			}
		}
		updatecache_f();
		$basename = "$admin_file?adminjob=setforum&action=edit&fid=$fid&c_type=$c_type";
		adminmsg('operate_success');
	}
} elseif ($action == 'changename') {

	$fid = (int)S::getGP('fid');
	S::gp(array('fname'),'P',0);
	$fname     = str_replace('<iframe','&lt;iframe',$fname);
	$fname	 = str_replace(array('<iframe','"',"'"),array("&lt;iframe","",""),$fname);
	//$db->update("UPDATE pw_forums SET name=" . S::sqlEscape($fname)." WHERE fid=".S::sqlEscape($fid));
	pwQuery::update('pw_forums', 'fid=:fid', array($fid), array('name' => $fname));
	updatecache_f();
	$msg = getLangInfo('cpmsg','operate_success');
	echo $msg;
	ajax_footer();
} elseif ($action == 'delttype') {
	S::gp(array('type','id','fid'));
	$id_array = array();
	if ($type == 'top') {
		$query = $db->query("SELECT id FROM pw_topictype WHERE upid=".S::sqlEscape($id));
		while ($rt = $db->fetch_array($query)) {
			$id_array[] = $rt['id'];
		}
	}
	$id_array = array_merge($id_array,array($id));
	if (!empty($id_array)) {
		if ($type == 'sub') {
			$upid = $db->get_value('SELECT upid FROM pw_topictype WHERE id = '.S::sqlEscape($id));
		}
		$db->update("DELETE FROM pw_topictype WHERE id IN (".S::sqlImplode($id_array).")");
		if ($upid) $db->update('UPDATE pw_threads SET type = '.S::sqlEscape($upid) . ' WHERE fid = ' . S::sqlEscape($fid) . ' AND type = '.S::sqlEscape($id));
		updatecache_f();
		$ids = implode("\t",$id_array);
		echo "success\t".$ids;
	} else {
		echo 'fail';
	}
	ajax_footer();

}} elseif ($adminitem =='uniteforum'){
$basename .= "&type=$type";
require_once(R_P.'require/updateforum.php');
if(empty($_POST['action'])){
	//* @include_once pwCache::getPath(D_P.'data/bbscache/forumcache.php');
	pwCache::getData(D_P.'data/bbscache/forumcache.php');
	list($hidefid,$hideforum) = GetHiddenForum();

	include PrintEot('uniteforum');exit;
} else{
	S::gp(array('fid','tofid'),'P',2);
	if(empty($fid)){
		adminmsg('unite_type');
	}
	if($fid==$tofid){
		adminmsg('unite_same');
	}
	$sub=$db->get_one("SELECT fid,name FROM pw_forums WHERE fup=".S::sqlEscape($fid)."LIMIT 1");
	if($sub){
		adminmsg('forum_havesub');
	}
	$forum=$db->get_one("SELECT type FROM pw_forums WHERE fid=".S::sqlEscape($tofid)."LIMIT 1");
	if($forum['type']=='category'){
		adminmsg('unite_type');
	}
	$forum=$db->get_one("SELECT fup,type FROM pw_forums WHERE fid=".S::sqlEscape($fid)."LIMIT 1");
	if($forum['type']=='category'){
		adminmsg('unite_type');
	}
	//$db->update("UPDATE pw_threads SET fid=".S::sqlEscape($tofid)."WHERE fid=".S::sqlEscape($fid));
	pwQuery::update('pw_threads', 'fid = :fid', array($fid), array('fid'=>$tofid));
	$ptable_a=array('pw_posts');

	if ($db_plist && count($db_plist)>1) {
		foreach ($db_plist as $key => $value) {
			if($key == 0) continue;
			$ptable_a[] = 'pw_posts'.$key;
		}
	}
	foreach($ptable_a as $val){
		//$db->update("UPDATE $val SET fid=".S::sqlEscape($tofid)."WHERE fid=".S::sqlEscape($fid));
		pwQuery::update($val, 'fid=:fid', array($fid), array('fid' => $tofid));
	}
	$db->update("UPDATE pw_attachs SET fid=".S::sqlEscape($tofid)."WHERE fid=".S::sqlEscape($fid));
	//$db->update("DELETE FROM pw_forums WHERE fid=".S::sqlEscape($fid));
	pwQuery::delete('pw_forums', 'fid=:fid' , array($fid));
	//* $db->update("DELETE FROM pw_forumdata WHERE fid=".S::sqlEscape($fid));
	pwQuery::delete('pw_forumdata', 'fid=:fid', array($fid));
	$db->update("DELETE FROM pw_forumsextra WHERE fid=".S::sqlEscape($fid));
	//* P_unlink(D_P."data/forums/fid_{$fid}.php");
	pwCache::deleteData(D_P."data/forums/fid_{$fid}.php");

	updatecache_f();
	updateforum($tofid);
	if($forum['type']=='sub'){
		updateforum($forum['fup']);
	}
	adminmsg('operate_success');
}} elseif ($adminitem == 'forumsell') {
	$basename .= "&adminitem=forumsell";
if (empty($action)) {

	require_once(R_P.'require/credit.php');
	//* include_once pwCache::getPath(D_P.'data/bbscache/forumcache.php');
	pwCache::getData(D_P.'data/bbscache/forumcache.php');
	S::gp(array('username'));
	S::gp(array('page','uid','fid'),'GP',2);

	$sql = "WHERE 1";
	if ($username) {
		$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
		$userdb = $userService->getByUserName($username);
		if (!$userdb) {
			$errorname = $username;
			adminmsg('user_not_exists');
		}
		$uid = $userdb['uid'];
	}
	if ($uid) {
		$sql .= " AND fs.uid=".S::sqlEscape($uid);
	}
	if ($fid) {
		$sql .= " AND fs.fid=".S::sqlEscape($fid);
	}
	$page < 1 && $page = 1;
	$limit = S::sqlLimit(($page-1)*$db_perpage,$db_perpage);
	$rt = $db->get_one("SELECT COUNT(*) AS sum FROM pw_forumsell fs $sql");
	$pages = numofpage($rt['sum'],$page,ceil($rt['sum']/$db_perpage),"$basename&uid=$uid&fid=$fid&");
	$buydb = array();
	$query = $db->query("SELECT fs.*,m.username,m.uid FROM pw_forumsell fs LEFT JOIN pw_members m USING(uid) $sql ORDER BY fs.overdate DESC $limit");
	while ($rt = $db->fetch_array($query)) {
		$rt['buydate']	= get_date($rt['buydate']);
		$rt['overtime']	= get_date($rt['overdate']);
		$buydb[] = $rt;
	}

	include PrintEot('forumsell');exit;

} elseif ($_POST['action'] == 'del') {

	S::gp(array('selid'));
	if (!$selid = checkselid($selid)) {
		adminmsg('operate_error');
	}
	$db->update("DELETE FROM pw_forumsell WHERE id IN($selid)");
	adminmsg('operate_success');
}} elseif ($adminitem == 'creathtm') {
	require_once(R_P.'require/nav.php'); //导航
	$basename .= "&type=$type";
$sqladd = "WHERE type<>'category' AND allowvisit='' AND f_type!='hidden' AND cms='0'";
if (!$action) {
	//* @include_once pwCache::getPath(D_P.'data/bbscache/forumcache.php');
	pwCache::getData(D_P.'data/bbscache/forumcache.php');
	$num = 0;
	$forumcheck = "<ul class=\"list_A list_120\">";

	$select = '';
	$query	= $db->query("SELECT fid,name,allowhtm FROM pw_forums $sqladd");
	while ($rt = $db->fetch_array($query)) {
		$num++;
		$htm_tr = $num % 5 == 0 ? '' : '';
		$checked = $rt['allowhtm'] ? 'checked' : $checked='';
		$forumcheck .= "<li><input type='checkbox' name='selid[]' value='$rt[fid]' $checked>$rt[name]</li>$htm_tr";
		$rt['allowhtm'] && $select .= "<option value=\"$rt[fid]\">$rt[name]</option>";
	}
	$forumcheck.="</ul>";
	include PrintEot('creathtm');exit;

} elseif ($_POST['action'] == 'submit') {
	S::gp(array('selid'),'P');
	$_tmpSelid = $selid;
	$selid = checkselid($selid);
	if ($selid === false) {
		$basename = "javascript:history.go(-1);";
		adminmsg('operate_error');
	} elseif ($selid == '') {
		//* $db->update("UPDATE pw_forums SET allowhtm='0' $sqladd");
		$db->update(pwQuery::buildClause("UPDATE :pw_table SET allowhtm='0' $sqladd", array('pw_forums')));
	} elseif ($selid) {
		//* $db->update("UPDATE pw_forums SET allowhtm='1' $sqladd AND fid IN($selid)");
		//* $db->update("UPDATE pw_forums SET allowhtm='0' $sqladd AND fid NOT IN($selid)");
		
		$db->update(pwQuery::buildClause("UPDATE :pw_table SET allowhtm='1' $sqladd AND fid IN(:fid)", array('pw_forums',$_tmpSelid)));
		$db->update(pwQuery::buildClause("UPDATE :pw_table SET allowhtm='0' $sqladd AND fid NOT IN(:fid)", array('pw_forums',$_tmpSelid)));		
	}
	updatecache_f();
	adminmsg('operate_success');

} elseif ($action == 'creat') {
	@set_time_limit(0);
	$pwServer['REQUEST_METHOD'] != 'POST' && PostCheck($verify);
	S::gp(array('creatfid','percount','step','tfid','forumnum'));

	$fids = $tid = $fieldadd = $tableadd = $tids = '';
	!is_array($creatfid) && $creatfid = explode(',',$creatfid);
	if (in_array('all', $creatfid)) {
		$query = $db->query("SELECT fid FROM pw_forums $sqladd AND allowhtm='1'");
		while ($rt = $db->fetch_array($query)) {
			$fids .= ($fids ? ',' : '') . $rt['fid'];
		}
		$creatfid = explode(',',$fids);
	} else {
		$fids = implode(',',$creatfid);
	}
	!$fids && adminmsg('template_noforum');

	!$tfid && $tfid = 0;
	$thisfid = (int)$creatfid[$tfid];

	$imgpath	= $db_http	!= 'N' ? $db_http : $db_picpath;
	$attachpath	= $db_attachurl	!= 'N' ? $db_attachurl : $db_attachname;
	$staticPage = L::loadClass('StaticPage');

	if (!$staticPage->initForum($thisfid)) {
		Showmsg('data_error');
	}
	(!is_numeric($forumnum) || $forumnum < 0) && $forumnum = 0;
	!$step && $step = 1;
	!$percount && $percount = 100;
	$start = ($step-1) * $percount;
	$next  = $start + $percount;
	$step++;
	$j_url = "$basename&action=$action&percount=$percount&creatfid=$fids&forumnum=$forumnum";
	$goon  = 0;

	$query = $db->query("SELECT tid FROM pw_threads WHERE fid='$thisfid' AND ifcheck=1 AND special='0' ORDER BY topped DESC,lastpost DESC" . S::sqlLimit($start, $percount));
	while ($topic = $db->fetch_array($query)) {
		$goon = 1;
		$staticPage->update($topic['tid']);
	}
	if ($forumnum && $next >= $forumnum) {
		$goon = 0;
	}
	if ($goon) {
		$j_url .= "&step=$step&tfid=$tfid";
		adminmsg('updatecache_step',EncodeUrl($j_url));
	} else {
		$tfid++;
		if (isset($creatfid[$tfid])) {
			$j_url .= "&step=1&tfid=$tfid";
			adminmsg('updatecache_step1',EncodeUrl($j_url));
		}
		adminmsg('operate_success');
	}
} elseif ($_POST['action'] == 'delete') {

	//* @include_once pwCache::getPath(D_P.'data/bbscache/forum_cache.php');
	pwCache::getData(D_P.'data/bbscache/forum_cache.php');
	S::gp(array('creatfid'),'P');
	if (in_array('all',$creatfid)) {
		$handle = opendir(R_P.$db_readdir.'/');
		while ($file = readdir($handle)) {
			if (($file != ".") && ($file != "..") && ($file != "")) {
				if (is_dir(R_P.$db_readdir.'/'.$file)){
					//cms
					if (!$forum[$file]['cms']) {
						deldir(R_P.$db_readdir.'/'.$file);
					}
					//cms
				}
			}
		}
	} elseif ($creatfid) {
		foreach ($creatfid as $key => $value) {
			if (is_numeric($value)) {
				deldir(R_P.$db_readdir.'/'.$value);
			}
		}
	} else {
		adminmsg('forumid_error');
	}
	adminmsg('operate_success');
}
/*
 * 函数名和 common.php 里面冲突了
function pwAdvert($ckey,$fid=0,$lou=-1,$scr=0) {
	global $timestamp,$db_advertdb,$_time;
	if (empty($db_advertdb[$ckey])) return false;
	$hours = $_time['hours'] + 1;
	$fid || $fid = $GLOBALS['fid'];
	$scr || $scr = 'read';
	$lou = (int)$lou;
	$tmpAdvert = $db_advertdb[$ckey];
	if ($db_advertdb['config'][$ckey] == 'rand') {
		shuffle($tmpAdvert);
	}
	$arrAdvert = array();$advert = '';
	foreach ($tmpAdvert as $key=>$value) {
            if ($value['stime'] > $timestamp ||
                $value['etime'] < $timestamp ||
                ($value['dtime'] && strpos(",{$value['dtime']},",",{$hours},")===false) ||
		($value['mode'] && strpos($value['mode'],'bbs')===false) ||
		($value['page'] && strpos($value['page'],$scr)===false) ||
		($value['fid'] && strpos(",{$value['fid']},",",$fid,")===false) ||
		($value['lou'] && strpos(",{$value['lou']},",",$lou,")===false)
            ) {
		continue;
            }
            if ((!$value['ddate'] && !$value['dweek']) ||
                ($value['ddate'] && strpos(",{$value['ddate']},",",{$_time['day']},")!==false) ||
                ($value['dweek'] && strpos(",{$value['dweek']},",",{$_time['week']},")!==false)) {
                $arrAdvert[] = $value['code'];
                $advert .= is_array($value['code']) ? $value['code']['code'] : $value['code'];
                if ($db_advertdb['config'][$ckey] != 'all') break;
            }
	}
	return array($advert,$arrAdvert);
}
*/
}

function delforum($fid) {
	global $db,$db_guestdir,$db_guestthread,$db_guestread;

	$foruminfo = $db->get_one("SELECT fid,fup,forumadmin FROM pw_forums WHERE fid=".S::sqlEscape($fid));
	//$db->update("DELETE FROM pw_forums WHERE fid=".S::sqlEscape($fid));
	pwQuery::delete('pw_forums', 'fid=:fid', array($fid));
	//* $db->update("DELETE FROM pw_forumdata WHERE fid=".S::sqlEscape($fid));
	pwQuery::delete('pw_forumdata', 'fid=:fid', array($fid));
	$db->update("DELETE FROM pw_forumsextra WHERE fid=".S::sqlEscape($fid));
	$db->update("DELETE FROM pw_permission WHERE fid>'0' AND fid=".S::sqlEscape($fid));
	if ($foruminfo['forumadmin']) {
		$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
		$forumadmin = explode(",",$foruminfo['forumadmin']);
		foreach ($forumadmin as $key => $value) {
			if ($value) {
				$gid = $userService->getByUserName($value);
				if ($gid['groupid'] == 5 && !ifadmin($value)) {
					$userService->update($gid['uid'], array('groupid'=>-1));
					admincheck($gid['uid'],$value,$gid['groupid'],'','delete');
				}
			}
		}
	}
	if ($db_guestthread || $db_guestread) {
		require_once(R_P.'require/guestfunc.php');
		$db_guestthread && deldir(D_P."$db_guestdir/T_{$fid}");
	}
	//* P_unlink(D_P."data/forums/fid_{$fid}.php");
	pwCache::deleteData(D_P."data/forums/fid_{$fid}.php");


	require_once(R_P.'require/functions.php');
	require_once(R_P.'require/updateforum.php');
	$pw_attachs = L::loadDB('attachs', 'forum');
	$ttable_a = $ptable_a = array();
	$query = $db->query("SELECT tid,replies,ptable FROM pw_threads WHERE fid=".S::sqlEscape($fid));
	while ($tpc = $db->fetch_array($query)) {
		$tid = $tpc['tid'];
		$ttable_a[GetTtable($tid)][] = $tid;
		$ptable_a[$tpc['ptable']] = 1;
		$db_guestread && clearguestcache($tid,$tpc['replies']);
		if ($attachdb = $pw_attachs->getByTid($tid)) {
			delete_att($attachdb);
		}
	}
	pwFtpClose($GLOBALS['ftp']);

	foreach ($ttable_a as $pw_tmsgs => $val) {
		//* $val = S::sqlImplode($val,false);
		//* $db->update("DELETE FROM $pw_tmsgs WHERE tid IN($val)");
		pwQuery::delete($pw_tmsgs, 'tid IN(:tid)', array($val));
	}
	# $db->update("DELETE FROM pw_threads WHERE fid=".S::sqlEscape($fid));
	# ThreadManager
	//* $threadManager = L::loadClass("threadmanager", 'forum');
	//* $threadManager->deleteByForumId($fid);
	$threadService = L::loadclass('threads', 'forum');
	$threadService->deleteByForumId($fid);
	//* Perf::gatherInfo('changeThreadWithForumIds', array('fid'=>$fid));			

	foreach ($ptable_a as $key => $val) {
		$pw_posts = GetPtable($key);
		//$db->update("DELETE FROM $pw_posts WHERE fid=".S::sqlEscape($fid));
		pwQuery::delete($pw_posts, 'fid=:fid', array($fid));
	}
	updateforum($foruminfo['fup']);
}

function getForumSelectHtml(){
        global $db;
    	$query	= $db->query("SELECT f.*,fe.creditset,fe.forumset,fe.commend FROM pw_forums f LEFT JOIN pw_forumsextra fe ON f.fid=fe.fid ORDER BY f.vieworder,f.fid");
	$fkeys = array('fid','fup','ifsub','childid','type','name','style','f_type','cms','ifhide');
	$catedb = $forumdb = $subdb1 = $subdb2 = $forum_cache = $fname= array();
	while ($forums = $db->fetch_array($query)) {
		$fname[$forums['fid']] = str_replace(array("\\","'",'<','>'),array("\\\\","\'",'&lt;','&gt;'), strip_tags($forums['name']));
		$forum = array();
		foreach ($fkeys as $k) {
			$forum[$k] = $forums[$k];
		}
		if ($forum['type'] == 'category') {
			$catedb[] = $forum;
		} elseif ($forum['type'] == 'forum') {
			$forumdb[$forum['fup']] || $forumdb[$forum['fup']] = array();
			$forumdb[$forum['fup']][] = $forum;
		} elseif ($forum['type'] == 'sub') {
			$subdb1[$forum['fup']] || $subdb1[$forum['fup']] = array();
			$subdb1[$forum['fup']][] = $forum;
		} else {
			$subdb2[$forum['fup']] || $subdb2[$forum['fup']] = array();
			$subdb2[$forum['fup']][] = $forum;
		}
	}
	$forumcache = '';
	foreach ($catedb as $cate) {
		if (!$cate) continue;
		$forum_cache[$cate['fid']] = $cate;
		$forumlist_cache[$cate['fid']]['name'] = strip_tags($cate['name']);
		$forumcache .= "<option value=\"$cate[fid]\">&gt;&gt; {$fname[$cate[fid]]}</option>\r\n";
		if (!$forumdb[$cate['fid']]) continue;

		foreach ($forumdb[$cate['fid']] as $forum) {
			$forum_cache[$forum['fid']] = $forum;
			$forumlist_cache[$cate['fid']]['child'][$forum['fid']] = strip_tags($forum['name']);
			$forumcache .= "<option value=\"$forum[fid]\"> &nbsp;|- {$fname[$forum[fid]]}</option>\r\n";
			if (!$subdb1[$forum['fid']]) continue;
			foreach ($subdb1[$forum['fid']] as $sub1) {
				$forum_cache[$sub1['fid']] = $sub1;
				$forumcache .= "<option value=\"$sub1[fid]\"> &nbsp; &nbsp;|-  {$fname[$sub1[fid]]}</option>\r\n";
				if (!$subdb2[$sub1['fid']]) continue;

				foreach ($subdb2[$sub1['fid']] as $sub2) {
					$forum_cache[$sub2['fid']] = $sub2;
					$forumcache .= "<option value=\"$sub2[fid]\">&nbsp;&nbsp; &nbsp; &nbsp;|-  {$fname[$sub2[fid]]}</option>\r\n";
				}
			}
		}
	}
        return $forumcache;
}
function doOtherFidsSetFup($fup, $otherfid = array()){
	global $db;
	if (!$fup) return false;
	$rt = $db->get_one("SELECT type,cms FROM pw_forums WHERE fid=".S::sqlEscape($fup));
	if ($rt['type'] == 'category') {
		$type = 'forum';
	} elseif ($rt['type'] == 'forum') {
		if (($rt['cms'] && !$cms) || (!$rt['cms'] && $cms)) {
			adminmsg('setforum_cms');
		}
		$type = 'sub';
	} elseif ($rt['type'] == 'sub') {
		$type = 'sub2';
	}
	foreach ($otherfid as $v){
		$pwSQL = array('fup'=>$fup,'type'=>$type);
		$db->update("UPDATE pw_forums SET".S::sqlSingle($pwSQL)."WHERE fid=".S::sqlEscape($v,false));
	}
}
function doOtherFidsTopicType($fid, $otherfid = array()) {
	global $db;
	if (!$fid) return false;
	$otherTypeDb = $subTypedb = array(); 
	$query = $db->query("SELECT * FROM pw_topictype WHERE fid =".S::sqlEscape($fid));
	while($rt = $db->fetch_array($query)) {
		$otherTypeDb[$rt['id']] = $rt;
		$rt['upid'] && $subTypedb[$rt['upid']][] = $rt;
	}
	foreach($otherTypeDb as $key=> $value) {
		if (in_array($key, array_keys($subTypedb))) {
			$otherTypeDb[$key]['subType'] = $subTypedb[$key];
			foreach ($subTypedb[$key] as $unsetValue) {
				unset($otherTypeDb[$unsetValue['id']]);
			}
		}
	}
	$otherTypeArr = array();//格式成数组
	foreach($otherfid as $value) {
		$otherTypeArr[$value] = $otherTypeDb;
	}
	foreach($otherTypeArr as $key=>$otherType) {
		$db->update("DELETE FROM pw_topictype WHERE fid=". S::sqlEscape($key));
		foreach ($otherType as $value) {
			$typeSqldb = array('fid'=>$key, 'name'=>$value['name'], 'logo'=>$value['logo'], 'vieworder'=>$value['vieworder']);
			$db->update("INSERT INTO pw_topictype SET " . S::sqlSingle($typeSqldb));
			$newId = $db->insert_id();
			if ($value['subType']) {
				foreach ($value['subType'] as $subValue) {
					$subTypeSqldb = array('fid'=>$key, 'name'=>$subValue['name'], 'logo'=>$subValue['logo'], 'vieworder'=>$subValue['vieworder'],'upid'=>$newId);
					$db->update("INSERT INTO pw_topictype SET " . S::sqlSingle($subTypeSqldb));
				}
			}
		}
	}
}

//新增版块置顶操作
function updateForumPostTop($fidArr) {	
	global $db;
	if (!is_array($fidArr)) return false;
	$postTopData = array();
	$query = $db->query("SELECT * FROM pw_poststopped WHERE floor = 3 GROUP BY tid ");
	while ($row = $db->fetch_array($query)) {
		$postTopData[] = $row;
	}
	foreach ($postTopData as $key => $value) {
		foreach ($fidArr as $fid) {
			pwQuery::insert('pw_poststopped', array(
				'fid'			=> $fid,
				'tid'			=> $value['tid'],
				'pid'			=> $value['pid'],
				'floor'			=> $value['floor'],
				'uptime'		=> $value['uptime'],
				'overtime'		=> $value['overtime']
			));
		}
	}
	updatetop();
}

function viewHiddenAtt($attach) {
	if ($attach['dfadmin']) return true;
	if ($attach['special'] == 2 && isBuyFromSellAtt($attach['aid'])) {
		return true;
	}
	if ($attach['special'] == 1 && checkCreditFromHiddenAtt($attach['ctype'], $attach['needrvrc'])) {
		return true;
	}
	return false;
}

function isBuyFromSellAtt($aid) {
	static $buyAids = null;
	if (!isset($buyAids)) {
		global $db,$sellAttachs,$winduid;
		$buyAids = array();
		if ($sellAttachs) {
			$query = $db->query("SELECT aid FROM pw_attachbuy WHERE uid= " . S::sqlEscape($winduid) . ' AND aid IN(' . S::sqlImplode($sellAttachs) . ')');
			while ($rt = $db->fetch_array($query)) {
				$buyAids[] = $rt['aid'];	
			}
		}
	}
	return in_array($aid, $buyAids);
}

function checkCreditFromHiddenAtt($ctype, $v) {
	$hav = 0;
	if (in_array($ctype, array('money', 'rvrc', 'credit', 'currency'))) {
		$hav = $ctype == 'rvrc' ? $GLOBALS['userrvrc'] : $GLOBALS['winddb'][$ctype]; 
	}
	if (is_numeric($ctype)) {
		static $creditdb = null;
		if (!isset($creditdb)) {
			global $credit;
			require_once( R_P ."require/credit.php");
			$creditdb = $credit->get($GLOBALS['winduid'],'CUSTOM');
		}
		$hav = $creditdb[$ctype];
	}
	return $hav > $v;
}
?>