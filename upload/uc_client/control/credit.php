<?php

class creditcontrol {

	var $base;
	var $db;
	var $credit;

	function __construct(&$base) {
		$this->creditcontrol($base);
	}

	function creditcontrol(&$base) {
		$this->base = $base;
		$this->db = $base->db;
		$this->credit = $base->load('credit');
	}

	function add($credit, $isAdd = true) {
		$retv = array();
		if ($relate = $this->credit->getRelate($this->base->appid)) {
			$upcredit = array();
			foreach ($credit as $uid => $setv) {
				foreach ($setv as $cid => $value) {
					if (isset($relate[$cid])) {
						$upcredit[] = $relate[$cid];
						$retv[$uid][$cid] = $this->credit->add($uid, $relate[$cid], $value, $isAdd);
					}
				}
			}
			if ($retv) {
				$this->credit->syncredit(array_keys($retv), $upcredit);
			}
		}
		return $retv;
	}

	function get($uid) {
		$retv = array();
		if ($relate = $this->credit->getRelate($this->base->appid)) {
			$tmp = $this->credit->get($uid);
			$relate = array_flip($relate);
			foreach ($relate as $cid => $ctype) {
				if (isset($tmp[$cid])) {
					$retv[$ctype] = $tmp[$cid];
				}
			}
		}
		return $retv;
	}
}
?>