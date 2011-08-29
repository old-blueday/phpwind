<?php
!defined('P_W') && exit('Forbidden');
/**
 * 发送到新鲜事SERVICE
 * 
 * @package PW_sendWeibo
 * @author lmq
 */

class diaryWeibo extends baseWeibo {
	var $_db;
	var $_did;
	var $_url;
	function diaryWeibo() {
		global $db;
		$this->_db =& $db;
		$this->_url = $GLOBALS['db_bbsurl']."/apps.php?q=diary";
	}
	function init($id) {
		$this->_did = $id;
		$diaryDB = $this->_db->get_one("SELECT did,uid,username,subject,content,privacy FROM pw_diary WHERE did=".S::sqlEscape($id));
		if (!$diaryDB) return false;
		$uid = $diaryDB['uid'];
		require_once(R_P.'require/bbscode.php');
		$descrip = strip_tags(convert($this->escapeStr($diaryDB['content']),''));
		$content = sprintf("[url=%s] %s [/url]", $this->_url."&uid=$uid&a=detail&did=".$this->_did, $diaryDB['subject']);
		$mailSubject =  getLangInfo('app','diary_recommend');
		$mailContent = getLangInfo('app','ajax_sendweibo_info',array(
												'db_bbsurl'=> $GLOBALS['db_bbsurl'],
												'uid'	=> $uid,
												'username'=>$diaryDB['username'],
												'title'	=>$content,
												'descrip'	=>substrs($descrip,50)
											)
								);
		$this->_content = $content;
		$this->_mailSubject = $mailSubject;
		$this->_mailContent = $mailContent;
	}
}

class groupWeibo extends baseWeibo {
	var $_db;
	var $_cyid;
	var $_url;
	function groupWeibo() {
		global $db;
		$this->_db =& $db;
		$this->_url = $GLOBALS['db_bbsurl']."/apps.php?q=group";
	}
	function init($id) {
		$this->_cyid = $id;
		$colonyDB = $this->_db->get_one("SELECT c.id,c.cname,c.admin,c.descrip,m.uid FROM pw_colonys c LEFT JOIN pw_members m ON c.admin=m.username WHERE c.id=".S::sqlEscape($id));
		if (!$colonyDB) return false;
		$content = sprintf("[url=%s] %s [/url]", $this->_url."&cyid=".$this->_cyid, $colonyDB['cname']);
		$mailSubject =  getLangInfo('app','group_recommend');
		$mailContent = getLangInfo('app','ajax_sendweibo_info',array(
												'db_bbsurl'=> $GLOBALS['db_bbsurl'],
												'uid'	=> $colonyDB['uid'],
												'username'=> $colonyDB['admin'],
												'title'	=>$content,
												'descrip'	=>substrs($colonyDB['descrip'],50)
											)
								);
		$this->_content = $content;
		$this->_mailSubject = $mailSubject;
		$this->_mailContent = $mailContent;		
	}
}

class groupActiveWeibo extends baseWeibo {
	var $_db;
	var $_activeId;
	var $_url;
	function groupActiveWeibo() {
		global $db;
		$this->_db =& $db;
		$this->_url = $GLOBALS['db_bbsurl']."/apps.php?q=group";
	}
	function init($id) {
		
		$this->_activeId = $id;
		require_once(R_P. 'apps/groups/lib/active.class.php');
		$newActive = new PW_Active();
		$activeDB = $newActive->getActiveById($this->_activeId);
	
		if (!$activeDB) return false;
		
		require_once(R_P. 'apps/groups/lib/colonys.class.php');
		$newColony = new PW_Colony();
		$colonyDB = $newColony->getColonyById($activeDB['cid']);
		
		$content = sprintf("[url=%s] %s [/url]", $this->_url."&a=active&job=view&cyid=$colonyDB[id]&id=".$this->_activeId, $activeDB['title']);
	
		
		$mailSubject =  getLangInfo('app','groupactive_recommend');
		$mailContent = getLangInfo('app','ajax_sendweibo_groupinfo',array(
												'cname'=> $colonyDB['cname'],
												'title'	=>$content,
												'descrip'	=>substrs($activeDB['introduction'],50)
											)
								);
		$this->_content = $content;
		$this->_mailSubject = $mailSubject;
		$this->_mailContent = $mailContent;		
	}
}

class albumWeibo extends baseWeibo {
	var $_db;
	var $_aid;
	var $_url;
	function albumWeibo() {
		global $db;
		$this->_db =& $db;
		$this->_url = $GLOBALS['db_bbsurl']."/apps.php?q=photos&a=album";
	}
	function init($id) {
		global $cyid;
		$this->_aid = $id;
		$albumDB = $this->_db->get_one("SELECT aid,aname,ownerid,owner,lastphoto,aintro FROM pw_cnalbum WHERE aid=" . S::sqlEscape($id));
		if (!$albumDB) return false;
		$this->_url = !$cyid ? $this->_url."&uid=".$albumDB['ownerid']."&aid=".$this->_aid : $GLOBALS['db_bbsurl']."/apps.php?q=galbum&a=album&cyid=$cyid&aid=".$this->_aid;
		$pids = array();
		$query = $this->_db->query("SELECT pid FROM pw_cnphoto WHERE aid = ".S::sqlEscape($albumDB['aid'])." LIMIT 10");
		while($rt = $this->_db->fetch_array($query)) {
			$pids[] = $rt['pid'];
		}
		if(!$pids)  return false;
		$title = sprintf("[url=%s] %s [/url]", $this->_url, $albumDB['aname']);
		$content = sprintf("我觉得这个相册“%s”不错哦~~~", $title);		
		$mailSubject =  getLangInfo('app','album_recommend');
		$mailContent = getLangInfo('app','ajax_sendweibo_info',array(
										'db_bbsurl'=> $GLOBALS['db_bbsurl'],
										'uid'	=> $albumDB['ownerid'],
										'username'=> $albumDB['owner'],
										'title'	=> $title,
										'descrip'	=>substrs($albumDB['aintro'],50)
									)
						);
		if ($cyid) {
			$mailContent = getLangInfo('app','ajax_sendweibo_groupinfo',array(
								'cname'=> $albumDB['owner'],
								'title'	=> $title,
								'descrip'	=>substrs($albumDB['aintro'],50)
							)
				);
		}
		$this->_content = $content;
		$this->_mailSubject = $mailSubject;
		$this->_mailContent = $mailContent;
		$this->_pids = $pids;
		
	}
}

class photoWeibo extends baseWeibo {
	var $_db;
	var $_pid;
	var $_url;
	function photoWeibo() {
		global $db;
		$this->_db =& $db;
		$this->_url = $GLOBALS['db_bbsurl']."/apps.php?q=photos&a=view";
	}
	function init($id) {
		global $cyid;
		$this->_pid = $id;
		$photoDB = $this->_db->get_one("SELECT p.pid,p.path as basepath,p.pintro,p.ifthumb,a.ownerid,a.owner FROM pw_cnphoto p LEFT JOIN pw_cnalbum a ON p.aid=a.aid WHERE p.pid=" . S::sqlEscape($id));
		if (!$photoDB) return false;
		$this->_url = !$cyid ? $this->_url."&uid=".$photoDB['ownerid']."&pid=".$this->_pid : $GLOBALS['db_bbsurl']."/apps.php?q=galbum&a=view&cyid=$cyid&pid=".$this->_pid;
		$content = "我觉得这图不错哦~~~";
		$pids[] = $photoDB['pid'] ? $photoDB['pid'] :  array();
		if(!$pids)  return false;
		$title = sprintf("[url=%s] %s [/url]", $this->_url, $photoDB['pintro']);
		$mailSubject =  getLangInfo('app','photo_recommend');
		$mailContent = getLangInfo('app','ajax_sendweibo_info',array(
										'db_bbsurl'=> $GLOBALS['db_bbsurl'],
										'uid'	=> $photoDB['ownerid'],
										'username'=> $photoDB['owner'],
										'title'	=> $title,
										'descrip'	=>substrs($photoDB['pintro'],50)
									)
						);
		if ($cyid) {
			$mailContent = getLangInfo('app','ajax_sendweibo_groupinfo',array(
								'cname'=> $photoDB['owner'],
								'title'	=> $title,
								'descrip'	=>substrs($photoDB['pintro'],50)
							)
				);
		}
		$this->_content = $content;
		$this->_mailSubject = $mailSubject;
		$this->_mailContent = $mailContent;
		$this->_pids = $pids;
		
	}
}

class topicWeibo extends baseWeibo {
	var $_db;
	var $_tid;
	var $_url;
	function topicWeibo() {
		global $db;
		$this->_db =& $db;
		$this->_url = $GLOBALS['db_bbsurl']."/read.php?tid=";
	}
	function init($id) {
		global $cyid;
		$this->_tid = $id;
		$pw_tmsgs = GetTtable($this->_tid);
		$topicDB = $this->_db->get_one("SELECT t.tid,t.subject,t.anonymous,t.ifshield,t.authorid,t.author,t.postdate,tm.content FROM pw_threads t LEFT JOIN $pw_tmsgs tm ON t.tid=tm.tid WHERE t.tid=".S::sqlEscape($this->_tid));
		if (!$topicDB) return false;
		$uid = $topicDB['authorid'];
		$username = ($topicDB['anonymous'] == 1) ? $db_anonymousname : $topicDB['author'];		
		$descrip = $topicDB['subject'];
		$this->_url = !$cyid ? $this->_url.$this->_tid : $GLOBALS['db_bbsurl']."/apps.php?q=group&a=read&cyid=$cyid&tid=".$this->_tid;
		$content = sprintf("[url=%s] %s [/url]", urlRewrite($this->_url), $topicDB['subject']);
		$title = $content;
		$mailSubject =  getLangInfo('app','topic_recommend');
		$mailContent = getLangInfo('app','ajax_sendweibo_info',array(
										'db_bbsurl'=> $GLOBALS['db_bbsurl'],
										'uid'	=> $uid,
										'username'=> $username,
										'title'	=> $title,
										'descrip'	=>substrs($descrip,50)
									)
						);
		if ($cyid) {
			require_once(R_P. 'apps/groups/lib/colonys.class.php');
			$newColony = new PW_Colony();
			$colonyDB = $newColony->getColonyById($cyid);
			$cname = $colonyDB['cname'];
			$mailContent = getLangInfo('app','ajax_sendweibo_groupinfo',array(
								'cname'=> $cname,
								'title'	=> $title,
								'descrip'	=>substrs($descrip,50)
							)
				);
		}
		$this->_content = $content;
		$this->_mailSubject = $mailSubject;
		$this->_mailContent = $mailContent;
		
	}
}


class replyWeibo extends baseWeibo {
	var $_db;
	var $_pid;
	var $_url;
	function replyWeibo() {
		global $db;
		$this->_db =& $db;
		$this->_url = $GLOBALS['db_bbsurl']."/job.php?action=topost&tid=";
	}
	function init($id) {
		global $cyid,$tid;
		$this->_pid = $id;
		if (!$tid) return false;
		$tid = (int)$tid;
		$pw_posts = GetPtable('N',$tid);
		$replyDB = $this->_db->get_one("SELECT p.pid,p.tid,p.anonymous,p.ifshield,p.subject as psubject,p.author,p.authorid,p.postdate,p.content,t.subject as tsubject FROM $pw_posts p LEFT JOIN pw_threads t ON p.tid=t.tid WHERE p.pid=".S::sqlEscape($this->_pid));
		$uid = $replyDB['authorid'];
		$subject = $replyDB['psubject'] ? $replyDB['psubject'] : 'Re:'.$replyDB['tsubject'];
		$username = ($replyDB['anonymous'] == 1) ? $db_anonymousname : $replyDB['author'];
		$this->_url = !$cyid ? $this->_url.$tid."&pid=".$this->_pid : $this->_url.$tid."&pid=".$this->_pid."&cyid=$cyid";
		require_once(R_P.'require/bbscode.php');
		$replyDB['content'] = strip_tags(convert($this->escapeStr(stripWindCode($replyDB['content'])),''));
		$title = sprintf("[url=%s] %s [/url]",$this->_url,$subject);
		$descrip = $content = ($replyDB['ifshield'] == 1) ? "该主题已屏蔽" : stripWindCode(substrs($replyDB['content'],100,'N'));
		$content .= sprintf("------[url=%s] %s [/url]",$this->_url,$subject);
		
		$mailSubject =  getLangInfo('app','reply_recommend');
		$mailContent = getLangInfo('app','ajax_sendweibo_info',array(
										'db_bbsurl'=> $GLOBALS['db_bbsurl'],
										'uid'	=> $uid,
										'username'=> $username,
										'title'	=> $title,
										'descrip'	=>substrs($descrip,50)
									)
						);
		if ($cyid) {
			require_once(R_P. 'apps/groups/lib/colonys.class.php');
			$newColony = new PW_Colony();
			$colonyDB = $newColony->getColonyById($cyid);
			$cname = $colonyDB['cname'];
			$mailContent = getLangInfo('app','ajax_sendweibo_groupinfo',array(
								'cname'=> $cname,
								'title'	=> $title,
								'descrip'	=>substrs($descrip,50)
							)
				);
		}
		$this->_content = $content;
		$this->_mailSubject = $mailSubject;
		$this->_mailContent = $mailContent;
		
	}
}

class baseWeibo {
	var $_content;
	var $_mailSubject;
	var $_mailContent;
	var $_pids = array();
		
	function getContent() {
		return $this->_content;
	}
	
	function getMailSubject() {
		global $windid;
		return sprintf('"%s"%s',$windid,$this->_mailSubject);
	}
	
	function getMailContent() {
		return $this->_mailContent;
	}
	
	function getPids() {
		return $this->_pids;
	}
	
	function escapeStr($str) {
		if (!$str = trim($str)) return '';
		return preg_replace('/(&nbsp;){1,}/', ' ', $str);
	}
}
?>