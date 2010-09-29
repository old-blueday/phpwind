<?php

!defined('P_W') && exit('Forbidden');
/*
*api mode 11 版块相关
*/
class Forum {
    var $base;
	var $db;

    function Forum($base) {        
        $this->base = $base;
        $this->db = $base->db;
        
    }

    function get($fid = 0) {//获取版块信息
        $forumsdb = array();
        $query = $this->db->query("SELECT fid,fup,childid,type,name FROM pw_forums WHERE type IN ('category','forum','sub','sub2') ORDER BY fid");
        while ($rt = $this->db->fetch_array($query)) {
            $forumsdb[$rt['fid']] = $rt;
        }
        return new ApiResponse($forumsdb);
    }

	function insertApp($fids,$appid,$appinfo = '') {//更新版块APP信息

		if (!$fids) {
			return new ApiResponse(false);
		}
		if (is_numeric($fids)) {
			$sql = ' fid=' . pwEscape($fids);
		} else {
			$sql = ' fid IN(' . pwImplode(explode(",",$fids)) . ')';
		}
		
		$query = $this->db->query("SELECT fid,appinfo FROM pw_forumsextra WHERE appinfo!=''");
		while ($rt = $this->db->fetch_array($query)) {
			$appdb = array();
			$appdb = unserialize($rt['appinfo']);
			unset($appdb[$appid]);
			$appdb = serialize($appdb);
			
			$this->db->update("UPDATE pw_forumsextra SET appinfo=" . pwEscape($appdb) . " WHERE fid=" . pwEscape($rt['fid']));
		}

		$oldfids = array();
		$query = $this->db->query("SELECT fid,appinfo FROM pw_forumsextra WHERE $sql");
		while ($rt = $this->db->fetch_array($query)) {
			$appdb = array();
			$appdb = unserialize($rt['appinfo']);
			$appdb[$appid] = $appinfo;
			$appdb = serialize($appdb);
			$oldfids[$rt['fid']] = $rt['fid'];
			$this->db->update("UPDATE pw_forumsextra SET appinfo=" . pwEscape($appdb) . " WHERE fid=" . pwEscape($rt['fid']));
		}

		$forumset = array(
			'lock'		=> 0,		'cutnums'		=> 0,			'threadnum'		=> 0,			'readnum'		=> 0,
			'newtime'	=> 0,		'orderway'		=> 'lastpost',	'asc'			=> 'DESC',		'allowencode'	=> 0,
			'anonymous'	=> 0,		'rate'			=> 0,			'dig'			=> 0,			'inspect'		=> 0,
			'watermark'	=> 0,		'commend'		=> 0,			'autocommend'	=> 0,			'commendlist'	=> '',
			'commendnum'=> 0,		'commendlength' => 0,			'commendtime'	=> 0,			'addtpctype'	=> 0,
			'ifrelated'	=> 0,		'relatednums'	=> 0,			'relatedcon'	=> 'ownpost',	'relatedcustom'	=> array(),
			'rvrcneed'	=> 0,		'moneyneed'		=> 0,			'creditneed'	=> 0,			'postnumneed'	=> 0,
			'sellprice'	=> array(),	'uploadset'		=>'money 0',	'rewarddb'		=> '',			'allowtime'		=>''
		);
		$forumset = serialize($forumset);
		foreach (explode(",",$fids) as $key => $value) {
			if (!$oldfids[$value]) {
				$appdb = array();
				$appdb[$appid] = $appinfo;
				$appdb = serialize($appdb);
				$this->db->update("INSERT INTO pw_forumsextra SET " . pwSqlSingle(array(
					'fid'			=> $value,
					'forumset'		=> $forumset,
					'appinfo'		=> $appdb
				)));
			}
		}

		require_once(R_P.'admin/cache.php');
		updatecache_f();
		
		return new ApiResponse(true);
	}

	function alertType($fid,$name,$logo = '') {//添加版块主题分类
		$fid = $this->db->get_value("SELECT fid FROM pw_forums WHERE fid=" . pwEscape($fid));
		if (!$fid || !$name) {
			return new ApiResponse(false);
		}
		$topicid = $this->db->get_value("SELECT id FROM pw_topictype WHERE fid=" . pwEscape($fid). " AND name=" . pwEscape($name));
		if ($topicid) {
			$this->db->update("UPDATE pw_topictype SET logo=" . pwEscape($logo) . " WHERE id=" . pwEscape($topicid));
		} else {
			$this->db->update("INSERT INTO pw_topictype SET " . pwSqlSingle(array(
				'fid'		=> $fid,
				'name'		=> $name,
				'logo'		=> $logo
			),false));
			$topicid = $this->db->insert_id();
		}
		
		return new ApiResponse($topicid);
	}

	function createForum($name,$fup = 0,$descrip = '',$linkurl = '') {//创建版块
		
		if (!$name) {
			return new ApiResponse(false);
		}
		@include_once(D_P.'data/bbscache/forum_cache.php');
		$forumtype = $forum[$fup]['type'] == 'category' ? 'forum' : ($forum[$fup]['type'] == 'forum' ? 'sub' : 'sub2');

		$this->db->update("INSERT INTO pw_forums SET " . pwSqlSingle(array(
			'fup'			=> $fup,
			'type'			=> $forumtype,
			'name'			=> $name,
			'descrip'		=> $descrip,
			'cms'			=> 0,
			'ifhide'		=> 1,
			'allowtype'		=> 3
		)));

		$fid = $this->db->insert_id();
		$this->db->update("INSERT INTO pw_forumdata SET fid=".pwEscape($fid));

		$forumset = array(
			'lock'		=> 0,		'cutnums'		=> 0,			'threadnum'		=> 0,			'readnum'		=> 0,
			'newtime'	=> 0,		'orderway'		=> 'lastpost',	'asc'			=> 'DESC',		'allowencode'	=> 0,
			'anonymous'	=> 0,		'rate'			=> 0,			'dig'			=> 0,			'inspect'		=> 0,
			'watermark'	=> 0,		'commend'		=> 0,			'autocommend'	=> 0,			'commendlist'	=> '',
			'commendnum'=> 0,		'commendlength' => 0,			'commendtime'	=> 0,			'addtpctype'	=> 0,
			'ifrelated'	=> 0,		'relatednums'	=> 0,			'relatedcon'	=> 'ownpost',	'relatedcustom'	=> array(),
			'rvrcneed'	=> 0,		'moneyneed'		=> 0,			'creditneed'	=> 0,			'postnumneed'	=> 0,
			'sellprice'	=> array(),	'uploadset'		=>'money 0',	'rewarddb'		=> '',			'allowtime'		=>''
		);
		$forumset['link'] = $linkurl;
		$forumset = serialize($forumset);
		$this->db->update("INSERT INTO pw_forumsextra SET " . pwSqlSingle(array(
			'fid'			=> $fid,
			'forumset'		=> $forumset
		)));

		P_unlink(D_P.'data/bbscache/c_cache.php');
		require_once(R_P.'admin/cache.php');
		updatecache_f();
		require_once(R_P.'require/updateforum.php');
		$forumtype != 'category' && updatetop();

		return new ApiResponse($fid);
	}
}
