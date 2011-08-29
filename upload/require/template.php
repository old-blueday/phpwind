<?php
!function_exists('readover') && exit('Forbidden');
require_once(R_P.'require/bbscode.php');
require_once(R_P.'require/showimg.php');
//* include_once pwCache::getPath(D_P."data/bbscache/forumcache.php");
//* include_once pwCache::getPath(D_P.'data/bbscache/customfield.php');
extract(pwCache::getData(D_P."data/bbscache/forumcache.php", false));
extract(pwCache::getData(D_P.'data/bbscache/customfield.php', false));

if ($tid) {
	if (!is_array($db_union)) {
		$db_union = explode("\t",stripslashes($db_union));
		$db_union[0] && $db_hackdb = array_merge((array)$db_hackdb,(array)unserialize($db_union[0]));
	}
	$readtpl = 'readtpl';
	//* include_once S::escapePath(D_P."data/style/$tskin.php");
	extract(pwCache::getData(S::escapePath(D_P."data/style/$tskin.php"), false));
	if (!file_exists(R_P."template/$tplpath/$readtpl.htm")) {
		//* include_once S::escapePath(D_P."data/style/$db_defaultstyle.php");
		extract(pwCache::getData(S::escapePath(D_P."data/style/$db_defaultstyle.php"), false));
		if (!file_exists(R_P."template/$tplpath/$readtpl.htm")) {
			$tplpath = 'wind';
		}
	}
	if (file_exists(D_P."data/style/{$tplpath}_css.htm")) {
		$css_path = D_P."data/style/{$tplpath}_css.htm";
	} else {
		$css_path = D_P.'data/style/wind_css.htm';
	}

	if ($db_md_ifopen) {
		$_MEDALDB = L::config('_MEDALDB', 'cache_read');
	}
	$fieldadd = $tablaadd = '';
	foreach ($customfield as $key => $val) {
		$val['id'] = (int) $val['id'];
		$fieldadd .= ",mb.field_$val[id]";
	}
	$fieldadd && $tablaadd = "LEFT JOIN pw_memberinfo mb ON mb.uid=t.authorid";
	$S_sql = ',tm.*,m.uid,m.username,m.oicq,m.groupid,m.memberid,m.icon AS micon ,m.hack,m.honor,m.signature,m.regdate,m.medals,m.userstatus,md.onlinetime,md.postnum,md.digests,md.rvrc,md.money,md.credit,md.currency,md.starttime,md.thisvisit,md.lastvisit,p.voteopts,p.modifiable,p.previewable,p.multiple,p.mostvotes,p.voters,p.timelimit,p.leastvotes,p.regdatelimit,p.creditlimit,p.postnumlimit';
	$pw_tmsgs = GetTtable($tid);
	$J_sql = "LEFT JOIN $pw_tmsgs tm ON t.tid=tm.tid LEFT JOIN pw_members m ON m.uid=t.authorid LEFT JOIN pw_memberdata md ON md.uid=t.authorid LEFT JOIN pw_polls p ON p.tid=t.tid";
	$usehtm = 1;
	$read = $db->get_one("SELECT t.* $S_sql $fieldadd FROM pw_threads t $J_sql $tablaadd WHERE t.tid=".S::sqlEscape($tid));
	if (!$read) {
		$usehtm = 0;
	}
	if ($foruminfo['allowvisit']) {
		$usehtm = 0;
	} elseif ($foruminfo['password']) {
		$usehtm = 0;
	} elseif ($read['special'] == 2 || $read['special'] == 3) {
		$usehtm = 0;
	} elseif ($foruminfo['allowsell'] && strpos($read['content'],"[sell") !== false && strpos($read['content'],"[/sell]") !== false) {
		$usehtm = 0;
	} elseif ($foruminfo['allowhide'] && strpos($read['content'],"[post]") !== false && strpos($read['content'],"[/post]") !== false) {
		$usehtm = 0;
	} elseif ($forumset['allowencode'] && strpos($read['content'],'[hide=') !== false && strpos($read['content'],'[/hide]') !== false) {
		$usehtm = 0;
	} elseif (!$read['ifcheck']) {
		$usehtm = 0;
	}
	$date = date('ym',$read['postdate']);
	if (!$usehtm && file_exists(R_P."$db_readdir/$fid/$date/$tid.html")) {
		P_unlink(R_P."$db_readdir/$fid/$date/$tid.html");
	}
	$page = floor($article/$db_readperpage)+1;
	$count = $read['replies']+1;

	if ($usehtm && ($page == 1 || $read['replies'] <= $db_readperpage || $read['replies'] % $db_readperpage == 0 || !file_exists(R_P."$db_readdir/$fid/$date/$tid.html"))) {
		$db_menuinit = "'td_post' : 'menu_post','td_post1' : 'menu_post','td_hack' : 'menu_hack'";
		$f_url = "read.php?tid=$tid&";

		if ($read['special'] == 1) {
			$voters			= $read['voters'];
			$modifiable		= $read['modifiable'];
			$previewable	= $read['previewable'];
			$multiple		= $read['multiple'];
			$leastvotes		= $read['leastvotes'];
			$multi			= $read['multiple'] ? $read['mostvotes'] : 0;
			$vote_close		= ($read['state'] || ($read['timelimit'] && $timestamp - $read['postdate'] > $read['timelimit'] * 86400)) ? 1 : 0;
			$tpc_date		= get_date($read['postdate']);
			$tpc_endtime	= $read['timelimit'] ? get_date($read['postdate'] + $read['timelimit'] * 86400) : 0;
			$regdatelimit 	= $read['regdatelimit'] ? get_date($read['regdatelimit'],'Y-m-d') : '';
			$creditlimit 	= !empty($read['creditlimit']) ? unserialize($read['creditlimit']) : '';
			$postnumlimit	= $read['postnumlimit'];
			$havevote		= $read['havevote'];
			if ($creditlimit) {
				require_once(R_P.'require/credit.php');
			}
			htmvote($read['voteopts'], $read['multiple'], $previewable);
		} else {
			$votedb  = array();
		}
		$read['pid']	= 'tpc';

		//管理提醒内容处理
		if ($read['remindinfo']) {
			$remind = explode("\t",$read['remindinfo']);
			$remind[0] = str_replace("\n","<br />",$remind[0]);
			$remind[2] && $remind[2] = get_date($remind[2]);
			$read['remindinfo'] = $remind;
		}

		$readdb			= $authorids = array();
		$readdb[]		= $read;
		$subject		= $read['subject'];
		$tpctitle		= '- '.$subject;
		$favortitle		= str_replace("&#39","‘",$subject);
		$titletop1		= substrs('Re:'.str_replace('&nbsp;',' ',$subject),$GLOBALS['db_titlemax']-2);
		$j_p = "$R_url/$db_readdir/$fid/$date/$tid.html";
		list($guidename,$forumtitle) = getforumtitle(forumindex($foruminfo['fup'],1),1);
		$guidename .= "<em>&gt;</em><a href=\"read.php?tid=$tid\">$subject</a>";
		$forumtitle = "|$forumtitle";
		$db_metakeyword = "$subject".str_replace(array('|',' - '),',',$forumtitle).'phpwind';
		$read['content'] && $db_metadescrip = substrs(strip_tags(str_replace('"','&quot;',$read['content'])),50);
		$msg_guide = headguide($guidename,false);

		unset($fourm,$guidename);

		if ($read['replies'] > 0) {
			$readnum = $db_readperpage - 1;
			$pw_posts = GetPtable($read['ptable']);
			$query = $db->query("SELECT t.*,m.uid,m.username,m.oicq,m.groupid,m.memberid,m.icon AS micon,m.hack,m.honor,m.signature,m.regdate,m.medals,m.userstatus,md.onlinetime,md.postnum,md.digests,md.rvrc,md.money,md.credit,md.currency,md.starttime,md.thisvisit,md.lastvisit $fieldadd FROM $pw_posts t LEFT JOIN pw_members m ON m.uid=t.authorid LEFT JOIN pw_memberdata md ON md.uid=t.authorid $tablaadd WHERE t.tid=".S::sqlEscape($tid)."ORDER BY postdate LIMIT 0,$readnum");
			while ($read = $db->fetch_array($query)) {
				if ($foruminfo['allowsell'] && strpos($read['content'],"[sell") !== false && strpos($read['content'],"[/sell]") !== false) {
					$usehtm = 0;break;
				} elseif ($foruminfo['allowhide'] && strpos($read['content'],"[post]") !== false && strpos($read['content'],"[/post]") !== false) {
					$usehtm = 0;break;
				}
				$readdb[] = $read;
			}
			$db->free_result($query);
			unset($sign);
		}
		if ($usehtm) {
			$bandb = isban($readdb,$fid);
			$start_limit = 0;
			foreach ($readdb as $key => $read) {
				isset($bandb[$read['authorid']]) && $read['groupid'] = 6;
				$authorids[]  = $read['authorid'];
				$readdb[$key] = htmread($read,$start_limit++);
				$db_menuinit .= ",'td_read_".$read['pid']."':'menu_read_".$read['pid']."'";
			}
			$authorids = S::sqlImplode($authorids);

			if ($db_showcolony) {
				$colonydb = array();
				$query = $db->query("SELECT c.uid,cy.id,cy.cname FROM pw_cmembers c LEFT JOIN pw_colonys cy ON cy.id=c.colonyid WHERE c.uid IN($authorids) AND c.ifadmin!='-1'");
				while ($rt = $db->fetch_array($query)) {
					if (!$colonydb[$rt['uid']]) {
						$colonydb[$rt['uid']] = $rt;
					}
				}
			}
			if ($db_showcustom) {
				$cids = $customdb = array();
				$add = '';
				foreach ($_CREDITDB as $key => $value) {
					if (strpos($db_showcustom,",$key,") !== false) {
						$cids[] = $key;
					}
				}
				if ($cids) {
					$cids = S::sqlImplode($cids);
					$query = $db->query("SELECT uid,cid,value FROM pw_membercredit WHERE uid IN($authorids) AND cid IN($cids)");
					while ($rt = $db->fetch_array($query)){
						$customdb[$rt['uid']][$rt['cid']] = $rt['value'];
					}
				}
			}
			list($_Navbar,$_LoginInfo) = pwNavBar();

			if ($count % $db_readperpage == 0) {//$count $db_readperpage read.php?fid=$fid&tid=$tid&
				$numofpage = $count/$db_readperpage;
			} else {
				$numofpage = floor($count/$db_readperpage)+1;
			}
			$pages = numofpage($count,1,$numofpage,$f_url);//文章数,页码,共几页,路径
			ob_end_clean();
			ObStart();
			$db_bbsname_a = addslashes($db_bbsname);//模版内用到

			require_once(PrintEot($readtpl));
			$ceversion = defined('CE') ? 1 : 0;
			$content = str_replace(array('<!--<!---->','<!---->'),array('',''),ob_get_contents());
			$content.= "<script type=\"text/javascript\">(function(d,t){
var url=\"http://init.phpwind.net/init.php?sitehash={$db_sitehash}&v=$wind_version&c=$ceversion\";
var g=d.createElement(t);g.async=1;g.src=url;d.body.appendChild(g)}(document,\"script\"));</script>";
			ob_end_clean();
			ObStart();
			if (!is_dir(R_P.$db_readdir.'/'.$fid)) {
				@mkdir(R_P.$db_readdir.'/'.$fid);
				@chmod(R_P.$db_readdir.'/'.$fid,0777);
				pwCache::writeover(R_P."$db_readdir/$fid/index.html",'');
				@chmod(R_P."$db_readdir/$fid/index.html",0777);
			}
			if (!is_dir(R_P.$db_readdir.'/'.$fid.'/'.$date)) {
				@mkdir(R_P.$db_readdir.'/'.$fid.'/'.$date);
				@chmod(R_P.$db_readdir.'/'.$fid.'/'.$date,0777);
				pwCache::writeover(R_P."$db_readdir/$fid/$date/index.html",'');
				@chmod(R_P."$db_readdir/$fid/$date/index.html",0777);
			}
			pwCache::writeover(R_P."$db_readdir/$fid/$date/$tid.html",$content, "rb+",0);
			@chmod(R_P."$db_readdir/$fid/$date/$tid.html",0777);
		} elseif (file_exists(R_P."$db_readdir/$fid/$date/$tid.html")) {
			P_unlink(R_P."$db_readdir/$fid/$date/$tid.html");
		}
	}
}
function htmread($read,$start_limit) {
	global $tpc_author,$count,$timestamp,$db_onlinetime,$db_bbsurl,$attachdir,$attachpath,$_G,$tablecolor,$readcolorone,$readcolortwo,$lpic,$ltitle,$imgpath,$db_ipfrom,$db_showonline,$stylepath,$db_windpost,$db_windpic,$fid,$tid,$attachments,$aids,$db_signwindcode,$db_md_ifopen,$_MEDALDB,$db_shield;
	//* include_once pwCache::getPath(D_P.'data/bbscache/level.php');
	extract(pwCache::getData(D_P.'data/bbscache/level.php', false));
	$read['lou'] = $start_limit;
	$start_limit == $count-1 && $read['jupend'] = '<a name=lastatc></a>';

	$read['ifsign']<2 && $read['content'] = str_replace("\n","<br>",$read['content']);
	$read['groupid'] == '-1' && $read['groupid'] = $read['memberid'];
	$anonymous = $read['anonymous'] ? 1 : 0;
	if ($read['groupid'] != '' && $anonymous == 0) {
		!$lpic[$read['groupid']] && $read['groupid'] = 8;
		$read['lpic']		= $lpic[$read['groupid']];
		$read['level']		= $ltitle[$read['groupid']];
		$read['regdate']	= get_date($read['regdate'],"Y-m-d");
		$read['lastlogin']	= get_date($read['lastvisit'],"Y-m-d");
		$read['aurvrc']		= floor($read['rvrc']/10);
		$read['author']		= $read['username'];
		$read['ontime']		= (int)($read['onlinetime']/3600);
		$tpc_author			= $read['author'];
		$read['face']		= showfacedesign($read['micon']);
		if ($db_ipfrom == 1) $read['ipfrom'] = ' From:'.$read['ipfrom'];

		if ($db_md_ifopen && $read['medals']) {
			$medals = '';
			$md_a = explode(',',$read['medals']);
			foreach ($md_a as $key=>$value) {
				if ($value) $medals .= "<img src=\"{$_MEDALDB[$value][smallimage]}\" title=\"{$_MEDALDB[$value][name]}\" /> ";
			}
			$read['medals'] = $medals.'<br />';
		} else {
			$read['medals'] = '';
		}

		if ($read['ifsign'] == 1 || $read['ifsign'] == 3) {
			global $sign;
			if (!$sign[$read['author']]) {
				global $db_signmoney,$db_signgroup,$tdtime;
				if (strpos($db_signgroup,",$read[groupid],") !== false && $db_signmoney && (!getstatus($read['userstatus'], PW_USERSTATUS_SHOWSIGN) ||  (!$read['starttime'] || $read['currency'] < ($tdtime-$read['starttime'])/86400*$db_signmoney))) {
					$read['signature'] = '';
				} else {
					if ($db_signwindcode && getstatus($read['userstatus'], PW_USERSTATUS_SIGNCHANGE)) {
						$read['signature'] = convert($read['signature'],$db_windpic,2);
					}
					$read['signature'] = str_replace("\n","<br>",$read['signature']);
				}
				$sign[$read['author']] = $read['signature'];
			} else {
				$read['signature'] = $sign[$read['author']];
			}
		} else {
			$read['signature'] = '';
		}
	} else {
		$read['face'] = "<br>";$read['lpic'] = '8';
		$read['level'] = $read['digests'] = $read['postnum'] = $read['money'] = $read['regdate'] = $read['lastlogin'] = $read['aurvrc'] = $read['credit'] = '*';
		if ($anonymous) {
			$read['signature'] = $read['honor'] = $read['medals'] = $read['ipfrom'] = '';
			$read['author'] = $GLOBALS['db_anonymousname'];
			$read['authorid'] = 0;
			foreach ($GLOBALS['customfield'] as $key => $val) {
				$field = "field_".(int)$val['id'];
				$read[$field] = '*';
			}
		}
	}
	$read['postdate'] = get_date($read['postdate']);
	$read['mark'] = '';
	if ($read['ifmark']) {
		$markdb = explode("\t",$read['ifmark']);
		foreach ($markdb as $key => $value) {
			$read['mark'] .= "<li>$value</li>";
		}
	}
	if ($read['icon']) {
		$read['icon'] = "<img src=\"$imgpath/post/emotion/$read[icon].gif\" align=left border=0>";
	} else {
		$read['icon'] = '';
	}
	/**
	* 动态判断发帖是否需要转换
	*/
	$tpc_shield = 0;
	if ($read['ifshield'] || $read['groupid'] == 6 && $db_shield) {
		$read['subject'] = $read['icon'] = '';
		$read['content'] = shield($read['ifshield'] ? ($read['ifshield'] == 1 ? 'shield_article' : 'shield_del_article') : 'ban_article');
		$tpc_shield = 1;
	}
	$creditnames = pwCreditNames();
	if (!$tpc_shield) {
		$attachs = $aids = array();
		if ($read['aid'] && !$read['ifhide']) {
			$attachs = unserialize($read['aid']);
			if (is_array($attachs)) {
				$aids = attachment($read['content']);
			}
		}
		$wordsfb = L::loadClass('FilterUtil', 'filter');
		if (!$wordsfb->equal($read['ifwordsfb'])) {
			$read['content'] = $wordsfb->convert($read['content']);
		}
		if ($read['ifconvert'] == 2) {
			$read['content'] = preg_replace("/\[sell=(.+?)\]/is","",$read['content']);
			$read['content'] = preg_replace("/\[hide=(.+?)\]/is","",$read['content']);
			$read['content'] = str_replace(array('[/hide]','[/sell]','[post]','[/post]'),'',$read['content']);
			$read['content'] = convert($read['content'],$db_windpost);
		} else {
			strpos($read['content'],'[s:') !== false && $read['content'] = showface($read['content']);
		}
		if ($attachs && is_array($attachs) && !$read['ifhide']) {
			foreach ($attachs as $at) {
				$atype = '';
				$rat = array();
				if ($at['type'] == 'img' && $at['needrvrc'] == 0) {
					$a_url = geturl($at['attachurl'],'show');
					if (is_array($a_url)) {
						$atype = 'pic';
						$dfurl = '<br>'.cvpic($a_url[0], 1, $db_windpost['picwidth'], $db_windpost['picheight'], $at['ifthumb']);
						$rat = array('aid' => $at['aid'], 'img' => $dfurl, 'dfadmin' => 0, 'desc' => $at['desc']);
					} elseif ($a_url == 'imgurl') {
						$atype = 'picurl';
						$rat = array('aid' => $at['aid'], 'name' => $at['name'], 'dfadmin' => 0, 'verify' => md5("showimg{$tid}{$read[pid]}{$fid}{$at[aid]}{$GLOBALS[db_hash]}"));
					}
				} else {
					$atype = 'downattach';
					if ($at['needrvrc'] > 0) {
						!$at['ctype'] && $at['ctype'] = $at['special'] == 2 ? 'money' : 'rvrc';
						$at['special'] == 2 && $GLOBALS['db_sellset']['price'] > 0 && $at['needrvrc'] = min($at['needrvrc'], $GLOBALS['db_sellset']['price']);
					}
					$rat = array('aid' => $at['aid'], 'name' => $at['name'], 'size' => $at['size'], 'hits' => $at['hits'], 'needrvrc' => $at['needrvrc'], 'special' => $at['special'], 'cname' => $creditnames[$at['ctype']], 'type' => $at['type'], 'dfadmin' => 0, 'desc' => $at['desc'], 'ext' => strtolower(substr(strrchr($at['name'],'.'),1)));
				}
				if (!$atype) continue;
				if (in_array($at['aid'], $aids)) {
					$read['content'] = attcontent($read['content'], $atype, $rat);
				} else {
					$read[$atype][$at['aid']] = $rat;
				}
			}
		}
	}

	$GLOBALS['foruminfo']['copyctrl'] && $read['content'] = preg_replace("/<br>/eis","copyctrl('$read[colour]')",$read['content']);

	$read['alterinfo'] && $read['content'] .= "<br><br><br><font color=gray>[ $read[alterinfo] ]</font>";
	return $read;
}
function htmvote($voteopts,$muti,$previewable) {
	global $votetype,$votedb;
	$votearray = unserialize(stripslashes($voteopts));
	if (!is_array($votearray)) return;
	$votetype = $muti ? 'checkbox' : 'radio';
	$votesum = 0;
	$votedb = array();
	foreach ($votearray as $option) {
		$votesum += $option[1];
	}
	foreach ($votearray as $key => $value) {
		$vote = array();
		if ($previewable == 0) {
			$vote['width'] = floor(500 * $value[1] / ($votesum + 1));
			$vote['num']   = $value[1];
		} else {
			$vote['width'] = 0;
			$vote['num']   = '*';
		}
		$vote['name'] = $value[0];
		$votedb[] = $vote;
	}
}
?>