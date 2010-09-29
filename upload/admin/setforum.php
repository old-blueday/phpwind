<?php
!function_exists('adminmsg') && exit('Forbidden');
$basename = "$admin_file?adminjob=setforum&c_type=$c_type";

include_once(D_P.'data/bbscache/forumcache.php');
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
	InitGP(array('keyword'));
	$keyword = trim($keyword);
	
	$result = array();
	if ($keyword) {
		$query = $db->query("SELECT fid,name FROM pw_forums WHERE cms!='1' AND name LIKE ".pwEscape($keyword . '%')." ORDER BY vieworder");
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
	InitGP(array('fup','forumnum','ifsave'));
	InitGP(array('name'),'P',0);
	if (empty($_POST['step'])) {
		if (!empty($name)) {
			$db->update("INSERT INTO pw_forums SET " . pwSqlSingle(array(
				'fup'	=> 0,
				'type'	=> 'category',
				'name'	=> $name,
				'f_type'=> 'forum',
				'cms'	=> 0,
				'ifhide'=> 1,
				'allowtype'=> 3
			)));
			$fid = $db->insert_id();
			$db->update("INSERT INTO pw_forumdata SET fid=".pwEscape($fid));
			P_unlink(D_P.'data/bbscache/c_cache.php');
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
		InitGP(array('vieworder','forumadmin','style','keywords','descrip','logo','ifsave','addtype'));
		InitGP(array('descrip'),'P',0);
		$forumtype = $forum[$fup]['type'] == 'category' ? 'forum' : ($forum[$fup]['type'] == 'forum' ? 'sub' : 'sub2');
		if ($forum[$fup]['type'] != 'category') {
			$fupset = $db->get_one("SELECT f.allowhide,f.allowsell,f.allowtype,f.copyctrl,f.viewsub,f.allowvisit,f.allowpost,f.allowrp,f.allowdownload,f.allowupload,f.f_type,f.f_check,f.cms,f.ifhide,fe.creditset,fe.forumset FROM pw_forums f LEFT JOIN pw_forumsextra fe USING(fid) WHERE f.fid=".pwEscape($fup));
			Add_S($fupset);
			@extract($fupset,EXTR_OVERWRITE);
		}
		foreach($name as $key => $value) {
			if(empty($value)) continue;
			$value     = str_replace('<iframe','&lt;iframe',$value);
			$descrip[$key]  = str_replace('<iframe','&lt;iframe',$descrip[$key]);
			$keywords[$key] = Char_cv($keywords[$key]);
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
				$db->update("INSERT INTO pw_forums SET " . pwSqlSingle(array(
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
				$fid = $db->insert_id();
				if ($creditset || $forumset) {
					$db->update("INSERT INTO pw_forumsextra SET " . pwSqlSingle(array(
						'fid'		=> $fid,
						'creditset'	=> $creditset,
						'forumset'	=> $forumset
					)));
				}
			} else {
				$f_type = $forum[$fup]['f_type'] == 'hidden' ? 'hidden' : 'forum';
				$db->update("INSERT INTO pw_forums SET " . pwSqlSingle(array(
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
				$fid = $db->insert_id();
				//添加默认
				$forumset = 'a:40:{s:4:"link";s:0:"";s:4:"lock";i:0;s:7:"cutnums";i:0;s:9:"threadnum";i:0;s:7:"readnum";i:0;s:7:"newtime";i:0;s:8:"orderway";s:8:"lastpost";s:3:"asc";s:4:"DESC";s:11:"replayorder";s:1:"1";s:10:"viewcolony";i:1;s:12:"ifcolonycate";i:1;s:11:"allowencode";i:0;s:9:"anonymous";i:0;s:4:"rate";i:0;s:3:"dig";i:0;s:7:"inspect";i:0;s:9:"watermark";i:0;s:9:"overprint";i:0;s:7:"viewpic";i:0;s:7:"ifthumb";i:0;s:9:"ifrelated";i:0;s:11:"relatednums";i:0;s:10:"relatedcon";s:7:"ownpost";s:13:"relatedcustom";a:0:{}s:7:"commend";i:0;s:11:"autocommend";i:0;s:11:"commendlist";s:0:"";s:10:"commendnum";i:0;s:13:"commendlength";i:0;s:11:"commendtime";i:0;s:10:"addtpctype";i:0;s:8:"rvrcneed";i:0;s:9:"moneyneed";i:0;s:10:"creditneed";i:0;s:11:"postnumneed";i:0;s:9:"sellprice";a:0:{}s:9:"uploadset";s:9:"money			0";s:8:"rewarddb";s:3:"			";s:9:"allowtime";s:0:"";s:9:"thumbsize";s:5:"575	0";}';
				$db->update("INSERT INTO pw_forumsextra SET " . pwSqlSingle(array(
					'fid'		=> $fid,
					'creditset'	=> '',
					'forumset'	=> $forumset
				)));
			}
			$db->update("INSERT INTO pw_forumdata SET fid=".pwEscape($fid));
		}
		P_unlink(D_P.'data/bbscache/c_cache.php');
		updatecache_f();
		$forumtype != 'category' && updatetop();
		if($addtype == 1){
			ObHeader("$basename&action=edit&fid=$fid");
		}else{
			adminmsg('operate_success');
		}
	}
} elseif ($_POST['action'] == 'editforum') {

	InitGP(array('forumadmin'), 'P', 0);
	InitGP(array('order'), 'P', 2);
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
					if (CkInArray($value,$admin_d)) {
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
			$db->update("UPDATE pw_forums SET".pwSqlSingle($pwSQL)."WHERE fid=".pwEscape($foruminfo['fid'],false));
		}
	}
	updatecache_f();
	updateadmin();
	$errorname && $errorname = implode(',',$errorname);
	adminmsg($errorname ? 'user_not_exists' : 'operate_success');

} elseif ($action == 'delete') {

	InitGP(array('fid'));

	$count = $db->get_value("SELECT COUNT(*) AS count FROM pw_forums WHERE fup=".pwEscape($fid)." AND type<>'category'");
	if ($count) {
		adminmsg('forum_havesub');
	}
	if (empty($_POST['step'])) {

		include PrintEot('setforum');exit;

	} else {
		delforum($fid);
		P_unlink(D_P.'data/bbscache/c_cache.php');
		updatecache_f();
		adminmsg('operate_success');
	}
} elseif ($action == 'edit') {

	InitGP(array('fid'),'GP',2);
	InitGP(array('c_type'),'GP');
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
		@extract($db->get_one("SELECT f.*,fe.forumset FROM pw_forums f LEFT JOIN pw_forumsextra fe USING(fid) WHERE f.fid=" . pwEscape($fid)));
		$forumset = unserialize($forumset);
		$forumset['newtime']  /= 60;
		//$forumset['rvrcneed'] /= 10;

		$forumset['addtpctype'] ? $addtpctype_Y = 'checked' : $addtpctype_N = 'checked';
		$forumset['ifrelated'] ? $ifrelated_Y = 'checked' : $ifrelated_N = 'checked';
		${'r_'.$forumset['relatedcon']} = 'selected';

		$name = str_replace("<","&lt;",$name);
		$name = str_replace(">","&gt;",$name);

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
			ifcheck($forumset['viewcolony'],'viewcolony');
			ifcheck($forumset['ifcolonycate'],'ifcolonycate');
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
			$query = $db->query("SELECT id,name,logo,vieworder,upid FROM pw_topictype WHERE fid=".pwEscape($fid)." ORDER BY vieworder ");
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
			@include_once(D_P. 'data/bbscache/topic_config.php');
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
			@include_once(D_P. 'data/bbscache/activity_config.php');
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
			@include_once(D_P.'data/bbscache/postcate_config.php');

			require_once(R_P.'require/credit.php');
			$creditset = $db->get_value("SELECT creditset FROM pw_forumsextra WHERE fid=".pwEscape($fid));
			$creditset = $creditset ? unserialize($creditset) : array();

		}
		$ajaxurl = EncodeUrl($basename);
		include PrintEot('setforum');exit;

	} elseif ($_POST['step'] == 2) {
		$forum = $db->get_one("SELECT type,fup,forumadmin,logo FROM pw_forums WHERE fid=".pwEscape($fid));
		InitGP(array('name','descrip','metadescrip'),'P',0);
		InitGP(array('vieworder','dirname','style','across','keywords'),'P');
		Cookie('thisPWTabs', $c_type , 'F', false);
		$name     = str_replace('<iframe','&lt;iframe',$name);
		$descrip  = str_replace('<iframe','&lt;iframe',$descrip);
		$metadescrip = str_replace('<iframe','&lt;iframe',$metadescrip);
		$keywords = Char_cv($keywords);
		strlen($descrip)>250 && adminmsg('descrip_long');
		strlen($metadescrip)>250 && adminmsg('descrip_long');

		if ($forum['type'] == 'category') {
			$db->update("UPDATE pw_forums SET " . pwSqlSingle(array(
				'name'		=> $name,
				'vieworder'	=> $vieworder,
				'dirname'	=> $dirname,
				'style'		=> $style,
				'across'	=> $across,
				'cms'		=> $cms
			)) . " WHERE fid=".pwEscape($fid));
		} else {

			InitGP(array('creditdb','forumsetdb','uploadset','rewarddb','cfup','ffup','showsub','ifhide', 'viewsub_1','viewsub_2','allowhide','allowsell','copyctrl','f_check','password','allowvisit','allowread', 'allowpost','allowrp','allowupload','allowdownload','otherfid','otherforum','allowtime','allowtype', 'recycle','forumsell','sdate','cprice','rprice','logotype','logo_upload','logo_url','ifdellogo','t_view_db','new_t_view_db','t_logo_db','new_t_logo_db','new_t_sub_logo_db','new_t_sub_view_db','t_type','modelid','pcid','actmid'),'P');
			InitGP(array('t_db','new_t_db','new_t_sub_db','f_type'),'P',0);
			InitGP(array('ifcms'));



			//主题分类

			//更新原有的分类
			foreach ($t_db as $key => $value) {
				$db->update("UPDATE pw_topictype SET " . pwSqlSingle(array(
					'name'			=> $value,
					'vieworder'		=> $t_view_db[$key],
					'logo'			=> $t_logo_db[$key]
				)) . " WHERE id=".pwEscape($key));
			}

			//增加新分类
			foreach ($new_t_db as $key => $value) {
				if(empty($value)) continue;
				$typedb[] = array ('fid' => $fid,'name' => $value,'logo'=>$new_t_logo_db[$key],'vieworder'=>$new_t_view_db[$key]);
			}
			if ($typedb) {
				$db->update("REPLACE INTO pw_topictype (fid,name,logo,vieworder) VALUES " . pwSqlMulti($typedb));
			}
			//增加二级新分类
			foreach ($new_t_sub_db as $key => $value) {
				foreach ($value as $k => $v) {
					if (empty($v)) continue;
					$subtypedb[] = array ('fid' => $fid,'name' => $v,'logo'=>$new_t_sub_logo_db[$key][$k],'vieworder'=>$new_t_sub_view_db[$key][$k],'upid'=>$key);
				}
			}
			if ($subtypedb) {
				$db->update("REPLACE INTO pw_topictype (fid,name,logo,vieworder,upid) VALUES " . pwSqlMulti($subtypedb));
			}
			$forumsetdb['newtime'] *= 60;

			foreach ($forumsetdb as $key => $value) {
				if ($key == 'link') {
					$forumsetdb['link'] = str_replace(array('"',"'",'\\'),array('','',''),$value);
				} elseif ($key == 'recycle') {
					$forumsetdb['recycle'] = array_sum($value);
				} elseif (!in_array($key,array('orderway','asc','replayorder','commendlist','chat','relatedcon','relatedcustom'))) {
					$forumsetdb[$key] = (int)$value;
				}
			}

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
					$forumsetdb['relatedcustom'][$i]['linkurl'] = $forumsetdb['relatedcustom']['linkurl'][$key];
					$i++;
				}

			}
			unset($forumsetdb['relatedcustom']['title']);
			unset($forumsetdb['relatedcustom']['linkurl']);
			ksort($sellprice);
			$forumsetdb['sellprice'] = $sellprice;
			$forumsetdb['uploadset'] = implode("\t",$uploadset);
			//$forumsetdb['rvrcneed'] *= 10;
			$rewarddb[3] = implode(',',$rewarddb[3]);
			$forumsetdb['rewarddb']  = implode("\t",$rewarddb);
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
				"SELECT fid FROM pw_forumsextra WHERE fid=".pwEscape($fid),
				"UPDATE pw_forumsextra SET forumset=".pwEscape($forumsextradb,false)."WHERE fid=".pwEscape($fid),
				"INSERT INTO pw_forumsextra SET forumset=".pwEscape($forumsextradb,false).',fid='.pwEscape($fid)
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
				"SELECT fid FROM pw_forumsextra WHERE fid=".pwEscape($fid),
				"UPDATE pw_forumsextra SET creditset=".pwEscape($creditset,false).'WHERE fid='.pwEscape($fid),
				"INSERT INTO pw_forumsextra SET creditset=".pwEscape($creditset,false).',fid='.pwEscape($fid)
			);
			$fup = $cms == '1' ? $cfup : $ffup;
			$fup == $fid && adminmsg('setforum_fupsame');
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

			$rt = $db->get_one("SELECT type,cms FROM pw_forums WHERE fid=".pwEscape($fup));
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

			if ($f_type == 'hidden' && $allowvisit == '') {
				$basename = "$admin_file?adminjob=setforum&action=edit&fid=$fid&c_type=$c_type";
				adminmsg('forum_hidden');
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
					$db->update("UPDATE pw_forums SET logo='' WHERE fid=".pwEscape($fid));
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

			$db->update("UPDATE pw_forums SET " . pwSqlSingle(array(
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

			)) . " WHERE fid=".pwEscape($fid));
			updateforum($fup);
			updateforum($forum['fup']);
		}
		P_unlink(D_P.'data/bbscache/c_cache.php');

		$othersql = $otherfids = array();
		$update_f = '';
		if (is_array($otherfid)) {
			$otherfids = pwImplode($otherfid);
		}
		
		if (is_array($otherforum)) {
			foreach ($otherforum as $key => $value) {
				if ($key === 'forumsetdb' || $key === 'creditset' || $key=== 't_typemain') {
					$update_f = 1;
					continue;
				}
				$othersql[$key] = $$key;
			}
		}
		
		if ($othersql && $otherfids) {
			$db->update("UPDATE pw_forums SET".pwSqlSingle($othersql)."WHERE fid IN($otherfids)");
			if ($otherforum['t_typemain']) {
				doOtherFidsTopicType($fid,$otherfid);
			}
		}
		if ($otherfids && $update_f) {
			include(D_P.'data/bbscache/forum_cache.php');
			foreach ($otherfid as $key => $selfid) {
				if (!$selfid || !is_numeric($selfid) || $selfid == $fid || $forum[$selfid]['type'] == 'category') {
					continue;
				}
				$rt = $db->get_one("SELECT fid,forumset,creditset FROM pw_forumsextra WHERE fid=".pwEscape($selfid));
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
					$db->update("UPDATE pw_forumsextra SET forumset=".pwEscape($forumset,false).",creditset=".pwEscape($newcreditset,false)."WHERE fid=".pwEscape($selfid));
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
					$db->update("INSERT INTO pw_forumsextra SET forumset=".pwEscape($forumset,false).',creditset='.pwEscape($newcreditset,false).',fid='.pwEscape($selfid));
				}
			}
		}
		updatecache_f();
		$basename = "$admin_file?adminjob=setforum&action=edit&fid=$fid&c_type=$c_type";
		adminmsg('operate_success');
	}
} elseif ($action == 'changename') {

	$fid = (int)GetGP('fid');
	InitGP(array('fname'),'P',0);
	$fname     = str_replace('<iframe','&lt;iframe',$fname);
	$fname	 = str_replace(array('<iframe','"',"'"),array("&lt;iframe","",""),$fname);
	$db->update("UPDATE pw_forums SET name=" . pwEscape($fname)." WHERE fid=".pwEscape($fid));
	updatecache_f();
	$msg = getLangInfo('cpmsg','operate_success');
	echo $msg;
	ajax_footer();
} elseif ($action == 'delttype') {
	InitGP(array('type','id'));
	$id_array = array();
	if ($type == 'top') {
		$query = $db->query("SELECT id FROM pw_topictype WHERE upid=".pwEscape($id));
		while ($rt = $db->fetch_array($query)) {
			$id_array[] = $rt['id'];
		}
	}
	$id_array = array_merge($id_array,array($id));
	if (!empty($id_array)) {
		$db->update("DELETE FROM pw_topictype WHERE id IN (".pwImplode($id_array).")");
		updatecache_f();
		$ids = implode("\t",$id_array);
		echo "success\t".$ids;
	} else {
		echo 'fail';
	}
	ajax_footer();

}

function delforum($fid) {
	global $db,$db_guestdir,$db_guestthread,$db_guestread;

	$foruminfo = $db->get_one("SELECT fid,fup,forumadmin FROM pw_forums WHERE fid=".pwEscape($fid));
	$db->update("DELETE FROM pw_forums WHERE fid=".pwEscape($fid));
	$db->update("DELETE FROM pw_forumdata WHERE fid=".pwEscape($fid));
	$db->update("DELETE FROM pw_forumsextra WHERE fid=".pwEscape($fid));
	$db->update("DELETE FROM pw_permission WHERE fid>'0' AND fid=".pwEscape($fid));
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
	P_unlink(D_P."data/forums/fid_{$fid}.php");

	require_once(R_P.'require/functions.php');
	require_once(R_P.'require/updateforum.php');
	$pw_attachs = L::loadDB('attachs', 'forum');
	$ttable_a = $ptable_a = array();
	$query = $db->query("SELECT tid,replies,ptable FROM pw_threads WHERE fid=".pwEscape($fid));
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
		$val = pwImplode($val,false);
		$db->update("DELETE FROM $pw_tmsgs WHERE tid IN($val)");
	}
	# $db->update("DELETE FROM pw_threads WHERE fid=".pwEscape($fid));
	# ThreadManager
	$threadManager = L::loadClass("threadmanager", 'forum');
	$threadManager->deleteByForumId($fid);

	foreach ($ptable_a as $key => $val) {
		$pw_posts = GetPtable($key);
		$db->update("DELETE FROM $pw_posts WHERE fid=".pwEscape($fid));
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

function doOtherFidsTopicType($fid, $otherfid = array()) {
	global $db;
	if (!$fid) return false;
	$otherTypeDb = $subTypedb = array(); 
	$query = $db->query("SELECT * FROM pw_topictype WHERE fid =".pwEscape($fid));
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
		$db->update("DELETE FROM pw_topictype WHERE fid=". pwEscape($key));
		foreach ($otherType as $value) {
			$typeSqldb = array('fid'=>$key, 'name'=>$value['name'], 'logo'=>$value['logo'], 'vieworder'=>$value['vieworder']);
			$db->update("INSERT INTO pw_topictype SET " . pwSqlSingle($typeSqldb));
			$newId = $db->insert_id();
			if ($value['subType']) {
				foreach ($value['subType'] as $subValue) {
					$subTypeSqldb = array('fid'=>$key, 'name'=>$subValue['name'], 'logo'=>$subValue['logo'], 'vieworder'=>$subValue['vieworder'],'upid'=>$newId);
					$db->update("INSERT INTO pw_topictype SET " . pwSqlSingle($subTypeSqldb));
				}
			}
		}
	}
}
?>