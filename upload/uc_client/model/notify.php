<?php

class notifymodel {

	var $base;
	var $db;
	var $operations;

	function __construct(&$base) {
		$this->notifymodel($base);
	}

	function notifymodel(&$base) {
		$this->base =& $base;
		$this->db =& $base->db;
		$this->operations = array(
			'updatesyncredit' => array('Cache', 'updatesyncredit'),
			'syncredit' => array('Credit', 'syncredit'),
			'altername' => array('User', 'alterName'),
			'deluser' => array('User', 'deluser')
		);
	}

	function add($type, $data, $exceptself = false, $exceptuc = false) {
		$myApp = $this->base->load('app');
		$applist = $myApp->applist();

		$pwSQL = array(
			'action' => $type,
			'param' => is_array($data) ? serialize($data) : '',
			'timestamp' => $this->base->time
		);
		$notify = array();
		foreach ($applist as $key => $app) {
			if (($exceptself && $key == $this->base->appid) || ($app['uc'] && $exceptuc)) {
				$pwSQL['app'.$key] = 1;
			} else {
				$notify[] = array('uc_notifyexists' . $key, 1);
			}
		}
		$this->db->update("INSERT INTO pw_ucnotify SET " . UC::sqlSingle($pwSQL));
		$nid = $this->db->insert_id();
		if ($notify) {
			$this->db->update("REPLACE INTO pw_config (db_name,db_value) VALUES " . UC::sqlMulti($notify));
		}
		return $nid;
	}

	function send($nid = null, $appid = null) {
		!$appid && $appid = $this->base->appid;
		register_shutdown_function(array($this, '_send'), $nid, $appid);
	}

	function _send($nid, $appid) {
		$myApp = $this->base->load('app');
		$applist = $myApp->applist();
		if (!$app = $applist[$appid]) {
			return false;
		}
		if ($nid) {
			$data = $this->get_by_id($nid);
		} else {
			$data = $this->get_by_one($appid);
			if (!$data) {
				$this->db->query("UPDATE pw_config SET db_value='0' WHERE db_name=" . UC::escape('uc_notifyexists'.$appid));
			}
			$nid = $data['nid'];
		}
		if (!$data) {
			return false;
		}
		$field = 'app' . $appid;

		if ($appid == $this->base->appid && file_exists(R_P . 'api/class_base.php')) {
			include_once(R_P . 'api/class_base.php');
			$api  = new api_client();
			$resp = $api->dataFormat($api->callback($this->operations[$data['action']][0], $this->operations[$data['action']][1], $data['param'] ? unserialize($data['param']) : array()));
			$success = isset($resp['result']);
		} else {
			$resp = $myApp->ucfopen($app['siteurl'], $app['interface'], $app['secretkey'], $this->operations[$data['action']][0], $this->operations[$data['action']][1], $data['param'] ? unserialize($data['param']) : array());
			$success = isset($resp['result']);
		}
		if ($success) {
			$data[$field] = 1;
			$pwSQL = array(
				$field => 1,
				'complete' => $this->isComplete($data, $applist) ? 1 : 0
			);
			$this->db->update("UPDATE pw_ucnotify SET " . UC::sqlSingle($pwSQL) . ' WHERE nid=' . UC::escape($nid));
		}
	}

	function send_by_id($nid) {
		$data = $this->get_by_id($nid);
		if ($data && !$data['complete']) {
			$pwSQL = array();
			$myApp = $this->base->load('app');
			$applist = $myApp->applist();
			foreach ($applist as $key => $app) {
				if ($data['app'.$key] < 1) {
					$resp = $myApp->ucfopen($app['siteurl'], $app['interface'], $app['secretkey'], $this->operations[$data['action']][0], $this->operations[$data['action']][1], $data['param'] ? unserialize($data['param']) : array());
					if (isset($resp['result'])) {
						$data['app'.$key] = 1;
						$pwSQL['app'.$key] = 1;
					}
				}
			}
			$pwSQL['complete'] = $this->isComplete($data, $applist) ? 1 : 0;
			$this->db->update("UPDATE pw_ucnotify SET " . UC::sqlSingle($pwSQL) . ' WHERE nid=' . UC::escape($nid));
		}
	}

	function isComplete($notify, $apps) {
		foreach ($apps as $key => $app) {
			if ($notify['app'.$key] < 1) {
				return false;
			}
		}
		return true;
	}

	function get_by_one($appid) {
		$field = 'app' . $appid;
		$data = $this->db->get_one("SELECT * FROM pw_ucnotify WHERE " . UC::sqlMetadata($field) . "<'1' LIMIT 1");
		return $data;
	}

	function get_by_id($nid) {
		$data = $this->db->get_one("SELECT * FROM pw_ucnotify WHERE nid=" . UC::escape($nid));
		return $data;
	}
}
?>