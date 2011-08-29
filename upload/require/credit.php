<?php
!function_exists('readover') && exit('Forbidden');

/**
 *
 * phpwind 积分操作统一入口
 * @author sky_hold@163.com
 * @package credit
 *
 */
Class PwCredit {

	var $cType = array();	//积分名称 array('money' => ??, 'rvrc' => ??, ...)
	var $cUnit = array();	//积分单位 array('money' => ??, 'rvrc' => ??, ...)
	var $Field = array();
	var $cLog  = array();
	var $setUser = array();
	var $getUser = array();

	var $_logset = array();

	function PwCredit() {
		$this->cType = array(
			'money'		=> $GLOBALS['db_moneyname'],
			'rvrc'		=> $GLOBALS['db_rvrcname'],
			'credit'	=> $GLOBALS['db_creditname'],
			'currency'	=> $GLOBALS['db_currencyname']
		);
		$this->cUnit = array(
			'money'		=> $GLOBALS['db_moneyunit'],
			'rvrc'		=> $GLOBALS['db_rvrcunit'],
			'credit'	=> $GLOBALS['db_creditunit'],
			'currency'	=> $GLOBALS['db_currencyunit']
		);
		foreach ($GLOBALS['_CREDITDB'] as $key => $value) {
			$this->cType[$key] = $value[0];
			$this->cUnit[$key] = $value[1];
		}
		$this->Field = array('postnum', 'digests');
		$this->_logset = $GLOBALS['db_creditlog'];
	}
	
	/**
	 * 获取积分设置
	 *
	 * @param array $f_set 板块积分设置
	 * @param array $d_set 论坛积分设置
	 * return array()
	 */

	function creditset($f_set,$d_set) {
		if (!is_array($f_set)) $f_set = unserialize($f_set);
		if (!is_array($d_set)) $d_set = unserialize($d_set);

		foreach ($d_set as $key => $value) {
			foreach ($value as $k => $v) {
				isset($f_set[$key][$k]) && $f_set[$key][$k] !== '' && $v = $f_set[$key][$k];
				if (!in_array($key,array('Digest','Post','Reply'))) {
					$v = -$v;
				}
				$d_set[$key][$k] = $v;
			}
		}
		return $d_set;
	}

	function check($t) {
		return (isset($this->cType[$t]) || in_array($t,$this->Field)) ? true : false;
	}

	function setMdata($uid,$field,$point) {
		if ($this->check($field)) {
			$this->setUser[$uid][$field] += $point;
		}
	}

	/**
	 * 获取用户积分
	 *
	 * @param int		$uid		用户UID
	 * @param string	$cType		获取的积分类型
	 * return mixed
	 */
	function get($uid,$cType = 'ALL') {
		global $db;
		$getv = false;
		if (isset($this->cType[$cType])) {
			if (isset($this->getUser[$uid][$cType])) return $this->getUser[$uid][$cType];
			if (is_numeric($cType)) {
				if (perf::checkMemcache()){
					$_cacheService = Perf::gatherCache('pw_members');
					$userCredit = $_cacheService->getMemberCreditByUserIds(array($uid));
					$getv = $userCredit[$uid][$cType];
				} else {
					$getv = $db->get_value('SELECT value FROM pw_membercredit WHERE uid=' . S::sqlEscape($uid) . ' AND cid=' . S::sqlEscape($cType));
				}
				empty($getv) && $getv = 0;
			} else {
				$getv = $db->get_value("SELECT $cType FROM pw_memberdata WHERE uid=" . S::sqlEscape($uid));
				$cType == 'rvrc' && $getv = intval($getv/10);
			}
			$this->getUser[$uid][$cType] = $getv;
		}
		if (in_array($cType,array('ALL','COMMON','CUSTOM'))) {
			$getv = array();
			if ($cType != 'CUSTOM') {
				$getv = $db->get_one("SELECT money,FLOOR(rvrc/10) AS rvrc,credit,currency FROM pw_memberdata WHERE uid=" . S::sqlEscape($uid));
			}
			if ($GLOBALS['_CREDITDB'] && $cType != 'COMMON') {
				if (perf::checkMemcache()){
					$_cacheService = Perf::gatherCache('pw_members');
					$userCredit = $_cacheService->getMemberCreditByUserIds(array($uid));
					$getv = $userCredit[$uid];
				} else {
					$query = $db->query("SELECT cid,value FROM pw_membercredit WHERE uid=" . S::sqlEscape($uid));
					while ($rt = $db->fetch_array($query)) {
						$getv[$rt['cid']] = $rt['value'];
					}
				}
			}
			$this->getUser[$uid] = $getv;
		}
		return $getv;
	}

	/**
	 * 设置用户积分(+-)
	 *
	 * @param int		$uid		用户UID
	 * @param string	$cType		积分类型
	 * @param int		$point		+-的值
	 * @param bool		$operate	是否实时进行数据库操作
	 * return bool
	 */
	function set($uid, $cType, $point, $operate = true) {
		if (!isset($this->cType[$cType]) || empty($point)) {
			return false;
		}
		$arr = array(
			$uid => array($cType => $point)
		);
		if ($operate) {
			$this->runsql($arr);
		} else {
			$this->array_add($arr);
		}
		return true;
	}

	/**
	 * 设置用户多个积分(+-)
	 *
	 * @param int		$uid		用户UID
	 * @param array		$setv		积分值 array('money' => ??, 'rvrc' => ??, ...)
	 * @param bool		$operate	是否实时进行数据库操作
	 * return bool
	 */
	function sets($uid, $setv, $operate = true) {
		if (empty($setv) || !is_array($setv)) {
			return false;
		}
		if ($operate) {
			$this->runsql(array($uid => $setv));
		} else {
			$this->array_add(array($uid => $setv));
		}
		return true;
	}

	/**
	 * 设置多个用户多个积分(+-)
	 *
	 * @param array		$u_array	用户UID array(1, 2, 3, ...)
	 * @param array		$setv		积分值 array('money' => ??, 'rvrc' => ??, ...)
	 * @param bool		$operate	是否实时进行数据库操作
	 * return bool
	 */
	function setus($u_array, $setv, $operate = true) {
		if (empty($u_array) || !is_array($u_array) || empty($setv) || !is_array($setv)) {
			return false;
		}
		$arr = array();
		foreach ($u_array as $uid) {
			$arr[$uid] = $setv;
		}
		if ($operate) {
			$this->runsql($arr);
		} else {
			$this->array_add($arr);
		}
		return true;
	}

	/**
	 * 对给定数据进行数据库积分增减操作
	 *
	 * @param array		$setArr		操作数据 array(1 => array('money' => ??, 'rvrc' => ??), 2 => array(), 3 => array(), ...)
	 * @param bool		$isAdd		是否实时进行数据库操作
	 */
	function runsql($setArr = null, $isAdd = true) {
		global $db,$uc_server,$uc_syncredit;
		$setUser = isset($setArr) ? $setArr : $this->setUser;
		$retv = array();
		if ($uc_server && $uc_syncredit) {
			require_once(R_P . 'uc_client/uc_client.php');
			$retv = uc_credit_add($setUser, $isAdd);
		}
		$cacheUids = $cacheCredits = array();
		
		$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
		foreach ($setUser as $uid => $setv) {
			$updateUser = $increaseUser = array();
			foreach ($setv as $cid => $v) {
				if ($this->check($cid) && ($v <> 0 || !$isAdd)) {
					if (isset($retv[$uid][$cid])) {
						if ($uc_server == 1) {
							continue;
						}
						$act = 'set';
						$v = $retv[$uid][$cid];
					} else {
						$act = $isAdd ? 'add' : 'set';
					}
					if (is_numeric($cid)) {
						$v = intval($v);
						/**
						$db->pw_update(
							"SELECT uid FROM pw_membercredit WHERE uid=" . S::sqlEscape($uid) . ' AND cid=' . S::sqlEscape($cid),
							"UPDATE pw_membercredit SET " . ($act == 'add' ? 'value=value+' : 'value=') . S::sqlEscape($v) .  ' WHERE uid=' . S::sqlEscape($uid) . ' AND cid=' . S::sqlEscape($cid),
							"INSERT INTO pw_membercredit SET " . S::sqlSingle(array('uid' => $uid, 'cid' => $cid, 'value' => $v))
						);
						**/
						$db->pw_update(
							"SELECT uid FROM pw_membercredit WHERE uid=" . S::sqlEscape($uid) . ' AND cid=' . S::sqlEscape($cid),
							pwQuery::buildClause("UPDATE :pw_table SET " . ($act == 'add' ? 'value=value+' : 'value=') . ':value' .  ' WHERE uid=:uid AND cid=:cid', array('pw_membercredit', $v, $uid, $cid)),
							pwQuery::insertClause('pw_membercredit', array('uid' => $uid, 'cid' => $cid, 'value' => $v))
						);
					} else {
						$cid == 'rvrc' && $v *= 10;
						if ($act == 'add') {
							$increaseUser[$cid] = intval($v);
						} else {
							$updateUser[$cid] = intval($v);
						}
					}
				}
			}
			if ($increaseUser) $userService->updateByIncrement($uid, array(), $increaseUser);
			if ($updateUser) $userService->update($uid, array(), $updateUser);

			unset($this->getUser[$uid]);
			$cacheUids[] = 'UID_'.$uid;
			$cacheCredits[] = 'UID_CREDIT_'.$uid;
		}
		
		
//		if ($cacheUids) {
//			$_cache = getDatastore();
//			$_cache->delete($cacheUids);
//			$_cache->delete($cacheCredits);/*积分*/
//		}
		
		$this->writeLog();
		!isset($setArr) && $this->setUser = array();
	}

	/**
	 * 追加积分日志设置
	 */
	function appendLogSet($logset, $type = null) {
		if (empty($logset) || !is_array($logset)) {
			return false;
		}
		foreach ($logset as $key => $value) {
			$type && $key = $type . '_' . $key;
			if (!isset($this->_logset[$key])) {
				$this->_logset[$key] = $value;
			}
		}
	}
	/**
	 * 验证积分日志是否开启
	 */
	function _checkLogSet($logtype, $creditName) {
		if (isset($this->_logset[$logtype][$creditName])) {
			return true;
		}
		list($lgt) = explode('_', $logtype);
		return ($lgt == 'main' || isset($this->_logset[$lgt][$creditName]));
	}
	/**
	 * 记录积分日志
	 *
	 * @param string	$logtype	日志类型
	 * @param array		$setv		积分值 array('money' => ??, 'rvrc' => ??, ...)
	 * @param array		$log		日志信息描述
	 */
	function addLog($logtype, $setv, $log) {
		global $db_ifcredit,$timestamp;
		$credit_pop = '';
		$uid = $log['uid'];
		foreach ($setv as $key => $affect) {
			if (isset($this->cType[$key]) && $affect<>0 && $this->_checkLogSet($logtype, $key)) {
				$log['username'] = S::escapeChar($log['username']);
				$log['cname']	 = $this->cType[$key];
				$log['affect']	 = $affect;
				$log['affect'] > 0 && $log['affect'] = '+'.$log['affect'];
				$log['descrip'] = S::escapeChar(strip_tags(getLangInfo('creditlog',$logtype,$log)));
				$credit_pop .= $key.":".$log['affect'].'|';
				$this->cLog[] = array($log['uid'], $log['username'], $key, $affect, $timestamp, $logtype, $log['ip'], $log['descrip']);
			}
		}
		if ($db_ifcredit && $credit_pop) {//Credit Changes Tips
			$credit_pop = $logtype.'|'.$credit_pop;
			
			$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
			$userService->update($uid, array(), array('creditpop' => $credit_pop));
		}
	}

	function writeLog() {
		if (!empty($this->cLog)) {
			$GLOBALS['db']->update("INSERT INTO pw_creditlog (uid,username,ctype,affect,adddate,logtype,ip,descrip) VALUES ".S::sqlMulti($this->cLog,false));
		}
		$this->cLog = array();
	}

	function array_add($u_a) {
		if (empty($u_a)) return false;
		foreach ($u_a as $uid => $setv) {
			foreach ($setv as $key => $value) {
				if (isset($this->cType[$key]) && is_numeric($value) && $value <> 0) {
					$this->setUser[$uid][$key] += $value;
					isset($this->getUser[$uid][$key]) && $this->getUser[$uid][$key] += $value;
				}
			}
		}
	}

	function getCreditTypeByName($cName) {
		if (isset($this->cType[$cName])) return $cName;
		return array_search($cName, $this->cType);
	}
}

$credit = new PwCredit();

?>