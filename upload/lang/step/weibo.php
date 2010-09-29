<?php
!defined('PW_UPLOAD') && exit('Forbidden');
class weiboBase {

	var $limit = 100;
	var $record;
	var $end;
	var $_db;

	function weiboBase() {
		$this->_db = $GLOBALS['db'];
	}

	function get($start) {//interface
		return array();
	}

	function run($array) {//interface

	}

	function execute($start) {
		empty($start) && $start = 0;
		if (!$array = $this->get($start)) {
			return false;
		} else {
			$this->times = $this->limit;
		}
		$this->run($array);
		return $start + $this->limit;
	}

	function insert($data) {
		$data['extra'] = $data['extra'] ? serialize($data['extra']) : '';
		$this->_db->update("INSERT INTO pw_weibo_content SET " . pwSqlSingle($data));
		$mid = $this->_db->insert_id();
		return $mid;
	}
}

class writeChange extends weiboBase {

	function get($start) {
		$array = array();
		$end = $this->end = $start+$this->limit;
		$query = $this->_db->query("SELECT * FROM pw_owritedata WHERE id>$start AND id<=$end");
		while ($rt = $this->_db->fetch_array($query)) {
			$array[$rt['id']] = $rt;
		}
		return $array;
	}

	function run($writedb) {
		foreach ($writedb as $key => $value) {
			$data = array(
				'uid' => $value['uid'],
				'content' => $value['content'],
				'postdate' => $value['postdate'],
				'type' => 0,
				'objectid' => 0,
				'replies' => $value['c_num'],
				'extra'	=> ''
			);
			$mid = $this->insert($data);
			if ($value['c_num']) {
				$this->runComment($mid, $value['id']);
			}
		}
	}

	function runComment($mid, $id) {
		$this->_db->query("INSERT INTO pw_weibo_comment (uid,mid,content,postdate) SELECT uid,$mid,title,postdate FROM pw_comment where type='write' and typeid=" . pwEscape($id));
	}
}

class shareChange extends weiboBase {

	function get($start) {
		$array = array();
		$end = $this->end = $start+$this->limit;
		$query = $this->_db->query("SELECT * FROM pw_collection WHERE ifhidden=0 AND id>$start AND id<=$end");
		while ($rt = $this->_db->fetch_array($query)) {
			$array[$rt['id']] = $rt;
		}
		return $array;
	}

	function run($sharedb) {
		foreach ($sharedb as $key => $value) {
			$arr = unserialize($value['content']);
			$content = '';
			$extra = array();
			switch ($value['type']) {
				case 'video':
				case 'web':
				case 'music':
					$content = $arr['link'] . ' ' . $arr['descrip'];break;
				case 'topic':
					$content = '[url=' . $arr['link'] . ']' . $arr['topic']['subject'] . '[/url] ' . $arr['descrip'];break;
				case 'diary':
					$content = '[url=' . str_replace('{#APPS_BASEURL#}', 'apps.php?', $arr['link']) . ']' . $arr['diary']['subject'] . '[/url] ' . $arr['descrip'];break;
				case 'photo':
					if (preg_match('/pid=(\d+)/i', $arr['link'], $matchs)) {
						$pid = $matchs[1];
						$content = '我觉得这图不错哦~~~';
						$extra['photos'] = $this->getPhoto($pid);
					}
					break;
				case 'album':
					if (preg_match('/aid=(\d+)/i', $arr['link'], $matchs)) {
						$aid = $matchs[1];
						$album = $this->_db->get_one("SELECT * FROM pw_cnalbum WHERE aid=" . pwEscape($aid));
						$link = str_replace(
							array('{#APPS_BASEURL#}', 'space=1'),
							array('apps.php?', 'uid=' . $album['ownerid']),
							$arr['link']
						);
						$content = "我觉得这个相册“[url=$link]{$album[aname]}[/url]”不错哦~~~";
						$extra['photos'] = $this->getPhotos($aid);
					}
					break;
			}
			if ($content || $extra) {
				$data = array(
					'uid' => $value['uid'],
					'content' => $content,
					'postdate' => $value['postdate'],
					'type' => 0,
					'objectid' => 0,
					'extra'	=> $extra
				);
				$this->insert($data);
			}
		}
	}

	function getPhoto($pid) {
		$array = array();
		$query = $this->_db->query("SELECT pid,path,ifthumb FROM pw_cnphoto WHERE pid=" . pwEscape($pid));
		while ($rt = $this->_db->fetch_array($query)) {
			$array[$rt['pid']] = $rt;
		}
		return $array;
	}

	function getPhotos($aid) {
		$array = array();
		$query = $this->_db->query("SELECT pid,path,ifthumb FROM pw_cnphoto WHERE aid=" . pwEscape($aid) . ' limit 8');
		while ($rt = $this->_db->fetch_array($query)) {
			$array[$rt['pid']] = $rt;
		}
		return $array;
	}
}

class feedChange extends weiboBase {

	var $cnData = array();

	function get($start) {
		$array = array();
		$end = $this->end = $start+$this->limit;
		$query = $this->_db->query("SELECT * FROM pw_feed WHERE id>$start AND id<=$end");
		while ($rt = $this->_db->fetch_array($query)) {
			$array[$rt['id']] = $rt;
		}
		return $array;
	}

	function run($feeddb) {
		foreach ($feeddb as $key => $value) {
			switch ($value['type']) {
				case 'post':
					$this->runThread($value);break;
				case 'diary':
					$this->runDiary($value);break;
				case 'photo':
					$this->runPhoto($value);break;
				case 'colony_post':
					$this->runColonyPost($value);break;
				case 'colony_photo':
					$this->runColonyPhoto($value);break;
			}
		}
		$this->_runCn();
	}

	function runColonyPost($value) {
		if (!preg_match('/tid=(\d+)/', $value['descrip'], $matchs)) {
			return false;
		}
		$tid = $matchs[1];
		if (!$data = $this->getColonyThread($tid)) {
			return false;
		}
		$data = array_merge(array('uid' => $value['uid'], 'postdate' => $value['timestamp']), $data);
		if (($mid = $this->insert($data)) && $data['extra']['cyid']) {
			$this->cnData[] = array($data['extra']['cyid'], $mid);
		}
	}

	function runColonyPhoto($value) {
		if (!preg_match('/pid=(\d+)/', $value['descrip'], $matchs)) {
			return false;
		}
		$pid = $matchs[1];
		if (!$data = $this->getColonyPhoto($pid)) {
			return false;
		}
		$data = array_merge(array('uid' => $value['uid'], 'postdate' => $value['timestamp']), $data);
		if (($mid = $this->insert($data)) && $data['extra']['cyid']) {
			$this->cnData[] = array($data['extra']['cyid'], $mid);
		}
	}

	function runThread($value) {
		if (!preg_match('/read.php\?tid=(\d+)/', $value['descrip'], $matchs)) {
			return false;
		}
		$tid = $matchs[1];
		if (!$data = $this->getThread($tid)) {
			return false;
		}
		$data = array_merge(array('uid' => $value['uid'], 'postdate' => $value['timestamp']), $data);
		$this->insert($data);
	}

	function runDiary($value) {
		if (!preg_match('/did=(\d+)/i', $value['descrip'], $matchs)) {
			return false;
		}
		$did = $matchs[1];
		if (!$data = $this->getDiary($did)) {
			return false;
		}
		$data = array_merge(array('uid' => $value['uid'], 'postdate' => $value['timestamp']), $data);
		$this->insert($data);
	}

	function runPhoto($value) {
		if (!preg_match('/pid=(\d+)/i', $value['descrip'], $matchs)) {
			return false;
		}
		$pid = $matchs[1];
		if (!$data = $this->getPhoto($pid)) {
			return false;
		}
		$data = array_merge(array('uid' => $value['uid'], 'postdate' => $value['timestamp']), $data);
		$mid = $this->insert($data);
	}

	function _runCn() {
		if ($this->cnData) {
			$this->_db->update("INSERT INTO pw_weibo_cnrelations (cyid, mid) VALUES " . pwSqlMulti($this->cnData));
		}
	}

	function getPhoto($pid) {
		$rt = $this->_db->get_one("SELECT p.*,a.aid,a.aname FROM pw_cnphoto p LEFT JOIN pw_cnalbum a ON p.aid=a.aid WHERE p.pid=" . pwEscape($pid));
		return $rt ? array(
			'content' =>  '',
			'type' => 30,
			'objectid' => $rt['pid'],
			'extra' => array(
				'aid' => $rt['aid'],
				'aname' => $rt['aname'],
				'photos' => array(
					0 => array(
						'pid' => $rt['pid'],
						'path' => $rt['path'],
						'ifthumb' => $rt['ifthumb']
					)
				)
			)
		) : array();
	}

	function getDiary($did) {
		$rt = $this->_db->get_one("SELECT did,subject,content FROM pw_diary WHERE did=" . pwEscape($did));
		return $rt ? array(
			'content' => substrs(stripWindCode($rt['content']), 125),
			'type' => 20,
			'objectid' => $rt['did'],
			'extra' => array(
				'did' => $rt['did'],
				'title' => $rt['subject']
			)
		) : array();
	}

	function getThread($tid) {
		$pw_tmsgs = GetTtable($tid);
		$rt = $this->_db->get_one("SELECT t.tid,t.subject,t.fid,t.ptable,tm.content,f.name FROM pw_threads t LEFT JOIN $pw_tmsgs tm ON t.tid=tm.tid LEFT JOIN pw_forums f ON t.fid=f.fid WHERE t.tid=" . pwEscape($tid));
		return $rt ? array(
			'content' => substrs(stripWindCode($rt['content']), 125),
			'type' => 10,
			'objectid' => $rt['tid'],
			'extra' => array(
				'title' => $rt['subject'],
				'fid' => $rt['fid'],
				'fname' => $rt['name']
			)
		) : array();
	}

	function getColonyThread($tid) {
		$pw_tmsgs = GetTtable($tid);
		$rt = $this->_db->get_one("SELECT a.cyid,t.tid,t.subject,t.fid,t.ptable,tm.content,c.cname FROM pw_argument a left join pw_threads t ON a.tid=t.tid LEFT JOIN $pw_tmsgs tm ON t.tid=tm.tid LEFT JOIN pw_colonys c ON a.cyid=c.id WHERE a.tid=" . pwEscape($tid));
		return $rt ? array(
			'content' => substrs(stripWindCode($rt['content']), 125),
			'type' => 40,
			'objectid' => $rt['tid'],
			'extra' => array(
				'title' => $rt['subject'],
				'cyid' => $rt['cyid'],
				'cname' => $rt['cname']
			)
		) : array();
	}

	function getColonyPhoto($pid) {
		$rt = $this->_db->get_one("SELECT p.*,a.aid,a.aname,c.id as cyid,c.cname FROM pw_cnphoto p LEFT JOIN pw_cnalbum a ON p.aid=a.aid left join pw_colonys c ON a.ownerid=c.id WHERE p.pid=" . pwEscape($pid) . ' and a.atype=1');
		return $rt ? array(
			'content' =>  '',
			'type' => 41,
			'objectid' => $rt['pid'],
			'extra' => array(
				'aid' => $rt['aid'],
				'aname' => $rt['aname'],
				'cyid' => $rt['cyid'],
				'cname' => $rt['cname'],
				'photos' => array(
					0 => array(
						'pid' => $rt['pid'],
						'path' => $rt['path'],
						'ifthumb' => $rt['ifthumb']
					)
				)
			)
		) : array();
	}
}
?>