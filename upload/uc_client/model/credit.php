<?php

class creditmodel {

	var $base;
	var $db;
	var $relate;

	function __construct(&$base) {
		$this->creditmodel($base);
	}

	function creditmodel(&$base) {
		$this->base = $base;
		$this->db = $base->db;
	}

	function isAllow($cid) {
		return in_array($cid, array('money', 'rvrc', 'credit', 'currency'));
	}

	function getRelate($appid) {
		$relate = array();
		$syncredit = $this->base->config('uc_syncredit');
		$myApp = $this->base->load('app');

		if ($myApp->isUc($appid)) {
			foreach ($syncredit as $ctype => $value) {
				$relate[$ctype] = $ctype;
			}
		} else {
			foreach ($syncredit as $ctype => $value) {
				if (isset($value[$appid])) {
					$relate[$value[$appid]] = $ctype;
				}
			}
		}
		return $relate;
	}

	function syncredit($uids, $cids) {
		$apps  = $notify = array();
		$myApp = $this->base->load('app');
		$applist = $myApp->applist();
		$fields = array('uid');
		$sp = 0;
		foreach ($applist as $key => $app) {
			if (!$app['uc']) {
				$fields[] = 'app'.$key;
				if ($key == $this->base->appid) {
					$apps[$key] = 1;
				} elseif (array_intersect($cids, $this->getRelate($key))) {
					$apps[$key] = 0;
					$notify[] = array('uc_syncreditexists' . $key, 1);
				} else {
					$sp = 1;
				}
			}
		}
		if (empty($apps)) {
			return;
		}
		$stats = array();
		if ($sp) {
			$query = $this->db->query("SELECT * FROM pw_ucsyncredit WHERE uid IN(" . UC::implode($uids) . ')');
			while ($rt = $this->db->fetch_array($query)) {
				$stats[$rt['uid']] = $rt;
			}
		}
		$pwSQL = array();
		foreach ($uids as $uid) {
			$sql = array($uid);
			foreach ($applist as $key => $app) {
				if (!$app['uc']) {
					$sql[] = isset($apps[$key]) ? $apps[$key] : (isset($stats[$uid]) ? $stats[$uid]['app'.$key] : 1);
				}
			}
			$pwSQL[] = $sql;
		}
		if ($pwSQL) {
			$this->db->update("REPLACE INTO pw_ucsyncredit (" . implode(',', $fields) . ") VALUES " . UC::sqlMulti($pwSQL));
		}
		if ($notify) {
			$this->db->update("REPLACE INTO pw_config (db_name, db_value) VALUES " . UC::sqlMulti($notify));
		}
	}

	function synupdate($appid = null, $uids = array()) {
		!$appid && $appid = $this->base->appid;
		//$this->_synupdate($appid, $uids);
		register_shutdown_function(array($this, '_synupdate'), $appid, $uids);
	}

	function _synupdate($appid, $u_arr) {
		$myApp = $this->base->load('app');
		$applist = $myApp->applist();
		if (!$app = $applist[$appid]) {
			return false;
		}
		$field = 'app' . $appid;
		if ($u_arr) {
			$query = $this->db->query("SELECT * FROM pw_ucsyncredit WHERE uid IN(" . UC::implode($u_arr) . ')');
		} else {
			$query = $this->db->query("SELECT * FROM pw_ucsyncredit WHERE $field < '1' LIMIT 10");
			if ($this->db->num_rows($query) == 0) {
				$this->db->query("UPDATE pw_config SET db_value='0' WHERE db_name=" . UC::escape('uc_syncreditexists'.$appid));
				return false;
			}
		}
		$us = array();
		while ($rt = $this->db->fetch_array($query)) {
			$us[$rt['uid']] = $rt;
		}
		if (empty($us)) {
			return false;
		}
		$uids = array_keys($us);
		$ucds = $this->get($uids);
		$ucds = $this->exchange($ucds, $appid);
		if ($appid == $this->base->appid && file_exists(R_P . 'api/class_base.php')) {
			include(R_P . 'api/class_base.php');
			$api  = new api_client();
			$resp = $api->dataFormat($api->callback('Credit', 'syncredit', array($ucds)));
			$success = isset($resp['result']);
		} else {
			$resp = $myApp->ucfopen($app['siteurl'], $app['interface'], $app['secretkey'], 'Credit', 'syncredit', array($ucds));
			$success = isset($resp['result']);
		}
		if ($success) {
			$cls = array();
			foreach ($us as $key => $data) {
				$data[$field] = 1;
				if ($this->isComplete($data, $applist)) {
					$cls[] = $key;
				}
			}
			$cls && $this->db->update("DELETE FROM pw_ucsyncredit WHERE uid IN(" . UC::implode($cls) . ')');
			$this->db->update("UPDATE pw_ucsyncredit SET $field='1' WHERE uid IN(" . UC::implode($uids) . ')');
		}
	}

	function isComplete($notify, $apps) {
		foreach ($apps as $key => $app) {
			if (!$app['uc'] && $notify['app'.$key] < 1) {
				return false;
			}
		}
		return true;
	}

	function add($uid, $cid, $value, $isAdd = true) {
		$cid == 'rvrc' && $value *= 10;
		$value = intval($value);
		if (is_numeric($cid)) {
			$rt = $this->db->get_one("SELECT uid,value FROM pw_membercredit WHERE uid=" . UC::escape($uid) . ' AND cid=' . UC::escape($cid));
			if ($rt) {
				if ($isAdd) {
					$this->db->update("UPDATE pw_membercredit SET value=value+" . UC::escape($value) .  ' WHERE uid=' . UC::escape($uid) . ' AND cid=' . UC::escape($cid));
					return $rt['value'] + $value;
				} else {
					$this->db->update("UPDATE pw_membercredit SET value=" . UC::escape($value) .  ' WHERE uid=' . UC::escape($uid) . ' AND cid=' . UC::escape($cid));
					return $value;
				}
			} else {
				$this->db->update("INSERT INTO pw_membercredit SET " . UC::sqlSingle(array('uid' => $uid, 'cid' => $cid, 'value' => $value)));
				return $value;
			}
		} elseif ($this->isAllow($cid)) {
			if ($isAdd) {
				$this->db->update("UPDATE pw_memberdata SET $cid=$cid+" . UC::escape($value) . ' WHERE uid=' . UC::escape($uid));
				$cid == 'rvrc' && $cid = "FLOOR(rvrc/10)";
				return $this->db->get_value("SELECT $cid FROM pw_memberdata WHERE uid=" . UC::escape($uid));
			} else {
				$this->db->update("UPDATE pw_memberdata SET $cid=" . UC::escape($value) . ' WHERE uid=' . UC::escape($uid));
				$cid == 'rvrc' && $value /= 10;
				return $value;
			}
		}
		return null;
	}

	function exchange($ucds, $appid) {
		$ucredit = array();
		if ($appid > 0) {
			$syncredit = $this->base->config('uc_syncredit');
			foreach ($ucds as $uid => $setv) {
				foreach ($setv as $ctype => $value) {
					if (isset($syncredit[$ctype][$appid])) {
						$ucredit[$uid][$syncredit[$ctype][$appid]] = $value;
					}
				}
			}
		}
		return $ucredit;
	}

	function get($uids) {
		$ucredit = $ucd = $mcd = array();
		$syncredit = $this->base->config('uc_syncredit');
		foreach ($syncredit as $key => $value) {
			if (is_numeric($key)) {
				$ucd[] = $key;
			} elseif ($this->isAllow($key)) {
				$key == 'rvrc' && $key = "FLOOR(rvrc/10) AS rvrc";
				$mcd[] = $key;
			}
		}
		if ($mcd) {
			$query = $this->db->query("SELECT uid," . implode(',', $mcd) . " FROM pw_memberdata WHERE uid IN(" . UC::implode($uids) . ')');
			while ($rt = $this->db->fetch_array($query)) {
				$ucredit[$rt['uid']] = $rt;
			}
		}
		if ($ucd) {
			$query = $this->db->query("SELECT * FROM pw_membercredit WHERE uid IN(" . UC::implode($uids) . ') AND cid IN(' . UC::implode($ucd) . ')');
			while ($rt = $this->db->fetch_array($query)) {
				$ucredit[$rt['uid']][$rt['cid']] = $rt['value'];
			}
		}
		return $ucredit;
	}

	function get22($uid) {
		$retv = array();
		$query = $this->db->query("SELECT cid,value FROM uc_usercredit WHERE uid=" . UC::escape($uid));
		while ($rt = $this->db->fetch_array($query)) {
			$retv[$rt['cid']] = $rt['value'];
		}
		return $retv;
	}
}
?>