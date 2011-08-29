<?php
!defined('P_W') && exit('Forbidden');

class PwSpace {
	
	var $_db;

	var $uid;
	var $info  = array();
	var $models = array('friend', 'visitor', 'visit', 'tags', 'messageboard', 'article', 'weibo', 'reply');

	var $default = false;

	function PwSpace($uid) {
		global $db, $winduid, $db_phopen, $db_dopen, $db_groups_open;
		$this->_db =& $db;
		$this->uid = $uid;
		$userService = L::loadClass('UserService', 'user');
		if ($winduid && $winduid == $uid) {
			$this->info = $GLOBALS['winddb'];
			$this->info['isMe'] = 1;
		} elseif ($userdb = $userService->get($this->uid,true,true,true)) {
			//$userdb['rvrc'] /= 10;
			$this->info = $userdb;
		}
		if ($this->info) {
			if ($space = $this->_db->get_one("SELECT * FROM pw_space WHERE uid=" . S::sqlEscape($this->uid))) {
				$this->info = array_merge($this->info, $space);
				if ($this->info['banner']) {
					list($this->info['banner_s']) = geturl($this->info['banner'], 'lf');
				}
			} else {
				$this->default = true;
			}
			$spaceGroupid = $this->info['groupid'] == -1 ? $this->info['memberid'] : $this->info['groupid'];
			include pwCache::getPath(D_P . "data/groupdb/group_$spaceGroupid.php");
			$this->info['generalRight'] = $_G;
			!$this->info['name'] && $this->info['name'] = $this->info['username'] . '的个人主页';
			!$this->info['skin'] && $this->info['skin'] = 'default85';
			$GLOBALS['uskin'] =& $this->info['skin'];
		}
		if ($db_dopen) $this->models[] = 'diary';
		if ($db_phopen) $this->models[] = 'photos';
		if ($db_groups_open) $this->models[] = 'colony';
	}

	function initSet() {
		if ($this->info['modelset']) {
			$this->info['modelset'] = unserialize($this->info['modelset']);
		} else {
			$this->info['modelset'] = array(
				'friend'		=> array('ifopen' => 1, 'num' => 5),
				'visitor'		=> array('ifopen' => 1, 'num' => 5),
				'visit'			=> array('ifopen' => 0, 'num' => 5),
				'tags'			=> array('ifopen' => 1, 'num' => 10, 'expire' => 7200),
				'messageboard'	=> array('ifopen' => 1, 'num' => 5),
				'diary'			=> array('ifopen' => 0, 'num' => 10),
				'photos'		=> array('ifopen' => 0, 'num' => 8),
				'weibo'			=> array('ifopen' => 0, 'num' => 10),
				'article'		=> array('ifopen' => 1, 'num' => 5),
				'reply'			=> array('ifopen' => 1, 'num' => 5),
				//'favorites'		=> array('ifopen' => 1, 'num' => 5),
				'colony'		=> array('ifopen' => 0, 'num' => 5)
				//'share'			=> array('ifopen' => 1, 'num' => 5)
			);
		}
		if ($this->info['layout']) {
			$this->info['layout'] = unserialize($this->info['layout']);
		} else {
			$this->info['layout'] = array(
				0 => array('tags'),
				1 => array('article','reply','messageboard'),
				2 => array('friend','visitor')
			);
		}
	}

	function &getInfo() {
		return $this->info;
	}

	function getDetailInfo() {
		global $customfield,$winduid,$groupid;
		$customfield = L::config('customfield', 'customfield');
		!is_array($customfield) && $customfield = array();
		foreach ($customfield as $key => $value) {
			if($value['viewright']&& $winduid!=$this->uid && strpos(",$value[viewright],",",$groupid,")===false){
				unset($customfield[$key]);
				continue;
			}
			$customfield[$key]['id'] = $value['id'] = (int)$value['id'];
			$customfield[$key]['field'] = "field_$value[id]";
		}
		$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
		if ($detail = $userService->get($this->uid, true, true, true)) {
			
			$days = max(1, ceil(($GLOBALS['timestamp'] - $detail['regdate']) / 86400));
			$detail['lastpost'] < $GLOBALS['tdtime'] && $detail['todaypost'] = 0;
			$detail['averagepost'] = round($detail['postnum'] / $days, 2);
			$detail['onlinetime_s'] = floor($detail['onlinetime'] / 3600);
			$detail['regdate'] = get_date($detail['regdate'], 'Y-m-d');
			$detail['lastvisit_s'] = get_date($detail['lastvisit'], 'Y-m-d');
			$this->info = array_merge($this->info, $detail);
			if ($GLOBALS['db_signwindcode'] && $this->info['signature'] && getstatus($this->info['userstatus'], PW_USERSTATUS_SIGNCHANGE)) {
				require_once(R_P . 'require/bbscode.php');				
				$this->info['signature'] = convert($this->info['signature'], $GLOBALS['db_windpic'], 2);	
			}
			$this->info['signature']=str_replace("\n","<br/>",$this->info['signature']);
			$this->info['introduce']=str_replace("\n","<br/>",$this->info['introduce']);
		}
	}

	function getModels() {
		$models = array('info' => 1);
		foreach ($this->info['modelset'] as $key => $value) {
			if (in_array($key, $this->models) && $value['ifopen'] && $this->viewRight($key)) {
				$models[$key] = $value['num'];
			}
		}
		return $models;
	}

	function getSpaceData($models) {
		require_once(R_P . 'u/lib/spacemodel.class.php');
		$spacemodel = new PwSpaceModel($this);
		return $spacemodel->get($this->uid, $models);
	}

	function layout() {
		$models	= $this->getModels();
		$data	= $this->getSpaceData($models);
		$array	= array(
			0 => array(
				'info' => $data['info']
			)
		);
		$tmp = array('info');
		foreach ($this->info['layout'] as $key => $value) {
			//if ($key > 1 && $this->info['spacetype'] == 1) {
			if ($key > 1 && $this->info['spacestyle'] == 2) {
				break;
			}
			foreach ($value as $k => $v) {
				if ($v <> 'info' && isset($models[$v])) {
					$array[$key][$v] = isset($data[$v]) ? $data[$v] : array();
					$tmp[] = $v;
				}
			}
		}
		if ($diff = array_diff(array_keys($models), $tmp)) {
			foreach ($diff as $key => $value) {
				$array[1][$value] = isset($data[$value]) ? $data[$value] : array();
			}
		}
		return $array;
	}
	
	function getPrivacy() {
		$rt = $this->_db->get_one("SELECT index_privacy AS `index`,msgboard_privacy AS `messageboard`,photos_privacy AS `photos`,diary_privacy AS `diary`,owrite_privacy AS `weibo` FROM pw_ouserdata WHERE uid=" . S::sqlEscape($this->uid));
		if ($rt) {
			return $rt;
		}
		return array(
			'index'			=> 0,
			'messageboard'	=> 0,
			'photos'		=> 0,
			'diary'			=> 0,
			'weibo'			=> 0
		);
	}
	function getPrivacyByKey($key) {
		static $array = array();
		if (!isset($array[$key])) {
			$array = array(
				'friend'		=> 0,
				'visitor'		=> 0,
				'visit'			=> 0,
				'article'		=> 0,
				'colony'		=> 0,
				'share'			=> 0,
				'tags'			=> 0
			);
			$array = array_merge($array, $this->getPrivacy());
		}
		return $array[$key];
	}

	//需要重构	
	function viewRight($key) {
		global $isGM;
		//自己或者管理员 可以访问 
		if ($this->info['isMe'] || $isGM) {
			return true;
		}
		return $this->checkRight($this->getPrivacyByKey($key));
	}

	function checkRight($privacy) {
		global $winduid,$isGM;
		if (empty($privacy) || $this->info['isMe'] || $isGM) {
			return true;
		}
		//仅朋友可见
		if ($privacy == 1 && $this->isFriend($winduid)) {		
			return true;
		}
		return false;
	}

	function isFriend($uid) {
		static $array = array();
		if (!isset($array[$uid])) {
			$array[$uid] = isFriend($this->uid, $uid);
		}
		return $array[$uid];
	}

	function updateInfo($data) {
		if ($this->default) {
			$data['uid'] = $this->uid;
			$this->_db->update("INSERT INTO pw_space SET " . S::sqlSingle($data));
		} else {
			$this->_db->update("UPDATE pw_space SET " . S::sqlSingle($data) . ' WHERE uid=' . S::sqlEscape($this->uid));
		}
	}
}
?>