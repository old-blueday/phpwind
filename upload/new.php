<?php
/*
**************************************************************************
*
* phpwind 首页调用
*
* $color   : 标题后增加显示信息颜色，例如作者，时间，点击数
* $prefix  : 标题前字符，可以用图片 : <img src="http://www.phpwind.net/pre.gif" border="0">
*
**************************************************************************
*/
require_once('global.php');
ob_end_clean();
$db_obstart && function_exists('ob_gzhandler') ? ob_start('ob_gzhandler') : ob_start();

$color   = '#666666';
$prefix  = array('<li>','◇','·','○','●','- ','□-');
$per = $db_jsper;

$REFERER = parse_url($pwServer['HTTP_REFERER']);

if (!$db_jsifopen) {
	$showmsg = getLangInfo('other','js_close');
	exit("document.write(\"$showmsg\");");
}
if ($db_bindurl && $pwServer['HTTP_REFERER'] && strpos(",$db_bindurl,",",$REFERER[host],") === false) {
	$showmsg = getLangInfo('other','bindurl');
	exit("document.write(\"$showmsg\");");
}
S::gp(array('action'));

switch ($action) {
	case 'forum':
		//* include_once pwCache::getPath(D_P.'data/bbscache/forum_cache.php');
		pwCache::getData(D_P.'data/bbscache/forum_cache.php');
		S::gp(array('pre','fidin'));
		$pre       = is_numeric($pre) ? $prefix[$pre] : $prefix[0];
		$fids      = explode('_',$fidin);
		$foruminfo = '';
		foreach ($fids as $key => $value) {
			if (is_numeric($value)) {
				$foruminfo .= "$pre <a href='$db_bbsurl/thread.php?fid=$value' target='_blank'>".$forum[$value]['name']."</a><br>";
			}
		}
		$foruminfo = str_replace('"','\"',$foruminfo);
		echo "document.write(\"$foruminfo\");";
		break;
	case 'notice':
		S::gp(array('pre','num','length'));
		$cachefile = D_P."data/bbscache/new_{$action}_".md5($action.(int)$pre.(int)$num.(int)$length);
		if ($timestamp - $per >= pwFilemtime($cachefile) && procLock('new_js_notice')) {
			$pre      = is_numeric($pre) ? $prefix[$pre] : $prefix[0];
			$num      = is_numeric($num) ? $num : 5;
			$length	  = is_numeric($length) ? $length : 35;

			$noticedb = '';
			$query = $db->query("SELECT aid,subject,startdate FROM pw_announce WHERE fid='-1' ORDER BY vieworder,startdate DESC LIMIT $num");
			while ($rt = $db->fetch_array($query)) {
				$startdate = $date ? '('.get_date($rt['startdate']+8*3600,'Y-m-d H:i').')' : '';
				$noticedb .= "$pre <a href='$db_bbsurl/notice.php?fid=-1#$rt[aid]' target='_blank'>".substrs(preg_replace("/\<(.+?)\>/eis",'',$rt['subject']),$length)."</a>$startdate<br>";
			}
			$noticedb = str_replace('"','\"',$noticedb);
			$noticedb = "document.write(\"$noticedb\");";
			pwCache::writeover($cachefile,$noticedb);
			procUnLock('new_js_notice');
			echo $noticedb;
		} else {
			@readfile($cachefile);
		}
		break;
	case 'info':
		S::gp(array('pre','member','article','yesterday','online'));
		$cachefile = D_P."data/bbscache/new_{$action}_".md5($action.(int)$pre.(int)$member.(int)$article.(int)$yesterday.(int)$online);
		if ($timestamp - $per >= pwFilemtime($cachefile) && procLock('new_js_info')) {
			$pre = is_numeric($pre) ? $prefix[$pre] : $prefix[0];
			//* $bbsinfo = $db->get_one("SELECT * FROM pw_bbsinfo WHERE id=1");
			$bbsInfoService = L::loadClass('BbsInfoService', 'forum'); 
			$bbsinfo = $bbsInfoService->getBbsInfoById(1);
			
 			if ($bbsinfo['tdtcontrol'] < $tdtime) {
				if ($db_hostweb == 1) {
					$rt=$db->get_one("SELECT SUM(fd.tpost) as tposts FROM pw_forums f LEFT JOIN pw_forumdata fd USING(fid) WHERE f.ifsub='0' AND f.cms!='1'");
					//* $db->update("UPDATE pw_bbsinfo SET yposts=".S::sqlEscape($rt['tposts']).",tdtcontrol=".S::sqlEscape($tdtime)." WHERE id=1");
					pwQuery::update('pw_bbsinfo', 'id=:id', array(1), array('yposts'=>$rt['tposts'], 'tdtcontrol'=>$tdtime));
					//* $db->update("UPDATE pw_forumdata SET tpost=0 WHERE tpost<>'0'");
					pwQuery::update('pw_forumdata', 'tpost<>:tpost', array(0), array('tpost'=>0));
				}
			}
			$info = '';
			if ($member) {
				$info .= "$pre ".getLangInfo('other','js_totalmember').":$bbsinfo[totalmember]<br>"
						."$pre ".getLangInfo('other','js_newmember').":$bbsinfo[newmember]<br>";
			}
			if ($article) {
				$rs = $db->get_one("SELECT SUM(fd.topic) as topic,SUM(fd.subtopic) as subtopic,SUM(fd.article) as article,SUM(fd.tpost) as tposts FROM pw_forums f LEFT JOIN pw_forumdata fd USING(fid) WHERE f.ifsub='0' AND f.cms!='1'");
				$topic   = $rs['topic'] + $rs['subtopic'];
				$article = $rs['article'];
				$info .= "$pre ".getLangInfo('other','js_topic').":$topic<br>"
						."$pre ".getLangInfo('other','js_article').":$article<br>";
			}
			if ($yesterday) {
				if (!$article) {
					$rs = $db->get_one("SELECT SUM(fd.tpost) as tposts FROM pw_forums f LEFT JOIN pw_forumdata fd USING(fid) WHERE f.ifsub='0' AND f.cms!='1'");
				}
				if (!$member) {
					//* $bbsinfo = $db->get_one("SELECT * FROM pw_bbsinfo WHERE id=1");
					$bbsInfoService = L::loadClass('BbsInfoService', 'forum'); 
					$bbsinfo = $bbsInfoService->getBbsInfoById(1);
				}
				$tposts = $rs['tposts'];
				$info .= "$pre ".getLangInfo('other','js_today').":$tposts<br>"
						."$pre ".getLangInfo('other','js_yesterday').":$bbsinfo[yposts]<br>"
						."$pre ".getLangInfo('other','js_highday').":$bbsinfo[hposts]<br>";
			}
			if ($online) {
				if (!$member && !$yesterday) {
					//* $bbsinfo = $db->get_one("SELECT * FROM pw_bbsinfo WHERE id=1");
					$bbsInfoService = L::loadClass('BbsInfoService', 'forum'); 
					$bbsinfo = $bbsInfoService->getBbsInfoById(1);					
				}
				@include_once(D_P.'data/bbscache/olcache.php');
				$usertotal  = $guestinbbs+$userinbbs;
				$higholtime = get_date($bbsinfo['higholtime']+8*3600,'Y-m-d');
				$info .= "$pre ".getLangInfo('other','js_online').":$usertotal<br>"
						."$pre ".getLangInfo('other','js_onlinemen').":$userinbbs<br>"
						."$pre ".getLangInfo('other','js_onlineguest').":$guestinbbs<br>"
						."$pre ".getLangInfo('other','js_highonline').":$bbsinfo[higholnum]<br>"
						."$pre ".getLangInfo('other','js_happen').":$higholtime";
			}
			$info = str_replace('"','\"',$info);
			$info = "document.write(\"$info\");";
			pwCache::writeover($cachefile,$info);
			procUnLock('new_js_info');
			echo $info ;
		} else {
			@readfile($cachefile);
		}
		break;
	case 'member':
		S::gp(array('num','pre','order'));
		$cachefile = D_P."data/bbscache/new_{$action}_".md5($action.(int)$num.(int)$pre.(int)$order);
		if ($timestamp - $per >= pwFilemtime($cachefile) && procLock('new_js_member')) {
			$num	  = is_numeric($num) ? $num : 10;
			$pre	  = is_numeric($pre) ? $prefix[$pre] : $prefix[0];
			$orderway = array(
				'1'   => 'uid',
				'2'   => 'postnum',
				'3'   => 'digests',
				'4'   => 'rvrc',
				'5'   => 'money',
				'6'   => 'credit'
			);
			$orderby  = is_numeric($order) ? $orderway[$order] : $orderway[1];
			!$orderby && $orderby = $orderway[1];
			//require_once(R_P.'require/element.class.php');
			//$element = new Element($num);
			$element = L::loadClass('element');
			$element->setDefaultNum($num);
			if ($orderby == 'uid') {
				$result = $element->getMembers('new');
			} else {
				$result = $element->userSort($orderby,0,false);
			}
			$newlist = '';
			foreach ($result as $value) {
				if ($orderby != 'uid') {
					$useradd = "($value[value])";
				} else {
					$useradd = '';
				}
				$userdb = "$pre <a href='$db_bbsurl/$value[url]' target='_blank'>$value[title]</a> $useradd";
				$userdb = str_replace('"','\"',$userdb);
				$newlist .= "document.write(\"$userdb<br>\");\n";
			}
			pwCache::writeover($cachefile,$newlist);
			procUnLock('new_js_member');
			echo $newlist;
		} else {
			@readfile($cachefile);
		}
		break;
	case 'article':
		S::gp(array('num','length','fidin','fidout','postdate','author','fname','hits','replies', 'pre','digest','order'));
		$cachefile = D_P."data/bbscache/new_{$action}_".md5($action.(int)$num.(int)$length.$fidin.$fidout.(int)$postdate.(int)$author.(int)$fname.(int)$hits.(int)$replies.(int)$pre.(int)$digest.(int)$order);
		if ($timestamp - $per >= pwFilemtime($cachefile) && procLock('new_js_article')) {
			$num	  = is_numeric($num) ? $num : 10;
			$length	  = is_numeric($length) ? $length : 35;
			$pre	  = is_numeric($pre) ? $prefix[$pre] : $prefix[0];
			//* $fname && include_once pwCache::getPath(D_P.'data/bbscache/forum_cache.php');
			$fname && pwCache::getData(D_P.'data/bbscache/forum_cache.php');
			$orderway = array(
				'1'   => 'lastpost',
				'2'   => 'postdate',
				'3'   => 'replies',
				'4'   => 'hits'
			);
			$orderby  = is_numeric($order) ? $orderway[$order] : $orderway[1];
			!$orderby && $orderby = $orderway[1];
			$sqladd = "ifcheck=1";
			$fidoff = $ext = '';
			$query = $db->query("SELECT fid FROM pw_forums WHERE password!='' OR allowvisit!='' OR f_type='hidden'");
			while ($rt = $db->fetch_array($query)) {
				$fidoff .= $ext.$rt['fid'];
				!$ext && $ext = ',';
			}
			$fidoff && $sqladd .= " AND fid NOT IN($fidoff)";
			if ($fidin) {
				$sqladd .= " AND fid IN ('".str_replace('_', "','", $fidin)."')";
			} elseif ($fidout) {
				$sqladd .= " AND fid NOT IN ('".str_replace('_', "','", $fidout)."')";
			}
			$digest && $sqladd .= " AND digest>'0'";
			$newlist = '';
			$query = $db->query("SELECT tid,fid,author,authorid,subject,postdate,hits,replies FROM pw_threads WHERE $sqladd ORDER BY $orderby DESC LIMIT 0, $num");
			while ($threads = $db->fetch_array($query)) {
				$threads['subject'] = substrs($threads['subject'], $length);
				$article = "$pre <a href='$db_bbsurl/read.php?tid=$threads[tid]' target='_blank'>$threads[subject]</a> ";
				if ($postdate) {
					$article .= " <font color='$color'>(".get_date($threads['postdate'],"Y-m-d H:i").')</font>';
				}
				if ($author) {
					$article .= " <a href='$db_bbsurl/".USER_URL."$threads[authorid]' target='_blank'><font color='$color'>($threads[author])</font></a>";
				}
				if ($replies) {
					$article .= " <font color='$color'>(".getLangInfo('other','js_replies')."：$threads[replies])</font></a>";
				}
				if ($hits) {
					$article .= " <font color='$color'>(".getLangInfo('other','js_hits')."：$threads[hits])</font></a>";
				}
				if ($fname) {
					$article .= " <a href='$db_bbsurl/thread.php?fid=$threads[fid]' target='_blank'><font color='$color'>(".$forum[$threads['fid']]['name'].")</font></a>";
				}
				$article = str_replace('"','\"',$article);
				$newlist .= "document.write(\"$article<br>\");\n";
			}
			pwCache::writeover($cachefile,$newlist);
			procUnLock('new_js_article');
			echo $newlist;
		} else {
			@readfile($cachefile);
		}
		break;
	default:
		$showmsg = getLangInfo('other','js_close');
		exit("document.write(\"$showmsg\");");
}
?>