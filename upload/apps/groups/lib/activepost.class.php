<?php
!defined('P_W') && exit('Forbidden');

class PwActivePost {
	
	var $data = array();
	var $att = null;

	var $delattach = array();
	var $replacedb = array();

	function PwActivePost($cid) {
		$this->data['cid'] = $cid;
		$this->data['uid'] = $GLOBALS['winduid'];
	}

	function initData($active) {
		$active['poster'] && $this->data['poster'] = $active['poster'];
	}

	function initAttachs($attachdb, $keep, $oldatt_desc) {
		$keep = (array)$keep;
		foreach ($attachdb as $key => $value) {
			if (!in_array($key, $keep)) {
				$this->delattach[$key] = $value;
			} elseif (array_key_exists('replace_'.$key, $_FILES)) {
				$GLOBALS['db_attachnum']++;
				$value['descrip'] = $oldatt_desc[$key];
				$this->replacedb[$key] = $value;
			} elseif ($value['descrip'] <> $oldatt_desc[$key]) {
				$this->alterattach[$key] = array('descrip' => $oldatt_desc[$key]);
			}
		}
	}

	function setTitle($title) {
		$this->data['title'] = $title;
	}

	function setContent($content) {
		$this->data['content'] = $content;
	}

	function setType($type) {
		$this->data['type'] = $type;
	}

	function setActiveTime($begintime, $endtime, $deadline) {
		$begintime && $this->data['begintime'] = PwStrtoTime($begintime);
		$endtime && $this->data['endtime'] = PwStrtoTime($endtime);
		$deadline && $this->data['deadline'] = PwStrtoTime($deadline);
	}

	function setAddress($address) {
		$this->data['address'] = $address;
	}

	function setLimitnum($limitnum) {
		$this->data['limitnum'] = $limitnum;
	}

	function setObjecter($objecter) {
		$this->data['objecter'] = $objecter;
	}

	function setPrice($price) {
		$this->data['price'] = $price;
	}

	function setIntroduction($introduction) {
		$this->data['introduction'] = $introduction;
	}

	function setMembers($members) {
		$this->data['members'] = $members;
	}

	function _setPoster() {
		L::loadClass('activeupload', 'upload', false);
		$att = new activePoster($this->data['cid']);
		PwUpload::upload($att);
		if ($poster = $att->getImgUrl()) {
			if ($this->data['poster']) {
				pwDelatt($this->data['poster'], $GLOBALS['db_ifftp']);
			}
			$this->data['poster'] = $poster;
		}
	}

	function _setAtt() {
		$this->att = new activeAtt($this->data['cid']);
		if ($this->replacedb) {
			$this->att->setReplaceAtt($this->replacedb);
		}
		if ($_POST['flashatt'] && is_array($_POST['flashatt'])) {
			$this->att->transfer($_POST['flashatt']);
		}
		PwUpload::upload($this->att);
	}

	function checkData() {
		if (!$this->data['title']) {
			return '活动名称不能为空!';
		}
		if (!$this->data['type']) {
			return '请选择活动类型!';
		}
		if ($this->data['limitnum'] && !is_numeric($this->data['limitnum'])) {
			return '人数上限类型为数字!';
		}
		if ($this->data['price'] && !is_numeric($this->data['price'])) {
			return '活动费用类型为数字!';
		}
		if (!$this->data['begintime'] || !$this->data['endtime']) {
			return '请填写活动开始时间和结束时间!';
		}
		if ($this->data['begintime'] > $this->data['endtime']) {
			return '活动开始时间不能大于结束时间!';
		}
		if (!$this->data['deadline']) {
			return '请填写报名截止时间!';
		}
		if ($this->data['deadline'] > $this->data['endtime']) {
			return '报名截止时间不能大于活动结束时间!';
		}
		if (!$this->data['introduction']) {
			return '请填写活动介绍!';
		}
		if (!$this->data['content']) {
			return 'active_content_empty';
		}
		$this->_setPoster();
		$this->_setAtt();

		return true;
	}

	function insertData() {
		global $db,$timestamp;
		$this->data['members'] = 1;
		$this->data['createtime'] = $timestamp;
		$db->update("INSERT INTO pw_active SET " . pwSqlSingle($this->data));
		$id = $db->insert_id();

		$db->update("INSERT INTO pw_actmembers SET " . pwSqlSingle(array(
			'uid'		=> $GLOBALS['winduid'],
			'actid'		=> $id,
			'realname'	=> $GLOBALS['windid'],
		)));

		if (is_object($this->att) && ($aids = $this->att->getAids())) {
			$this->att->updateById($aids, array('actid' => $id));
		}
		return $id;
	}

	function updateData($id) {
		global $db;
		$db->update("UPDATE pw_active SET " . pwSqlSingle($this->data) . ' WHERE id=' . pwEscape($id));
		
		if ($this->delattach) {
			foreach ($this->delattach as $key => $value) {
				pwDelatt($value['attachurl'],$GLOBALS['db_ifftp']);
				$value['ifthumb'] && pwDelatt("thumb/$value[attachurl]",$GLOBALS['db_ifftp']);
			}
			$db->update("DELETE FROM pw_actattachs WHERE aid IN (" . pwImplode(array_keys($this->delattach)) . ')');
		}
		if ($this->alterattach) {
			foreach ($this->alterattach as $aid => $v) {
				$this->att->updateById($aid, $v);
			}
		}
		if (is_object($this->att) && ($aids = $this->att->getAids())) {
			$this->att->updateById($aids, array('actid' => $id));
		}
	}
}
?>