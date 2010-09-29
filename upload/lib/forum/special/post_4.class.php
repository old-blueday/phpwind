<?php
!defined('P_W') && exit('Forbidden');

//mode 4
//商品
class postSpecial {

	var $db;
	var $post;
	var $forum;

	var $data;
	var $special = 4;

	function postSpecial($post) {
		global $db,$db_selcount;
		$this->db =& $db;
		$this->post =& $post;
		$this->forum =& $post->forum;

		$this->data = array(
			'tid'		=> 0,				'uid'		=> $this->post->uid,
			'name'		=> '',				'icon'		=> '',
			'degree'	=> 0,				'type'		=> 0,
			'num'		=> 0,				'price'		=> 0,
			'costprice'	=> 0,				'locus'		=> '',
			'paymethod'	=> 0,				'transport'	=> 0,
			'mailfee'	=> 0,				'expressfee'=> 0,
			'emsfee'	=> 0
		);
	}

	function postCheck() {
		if (!$this->post->_G['allowgoods']) {
			Showmsg('postnew_group_goods');
		}
	}

	function setInfo() {
		$set = array(
			'num'		=> 1,
			'transport'	=> 1,
			'type'		=> 0,
			'paym_1'	=> '',
			'paym_2'	=> '',
			'paym_4'	=> '',
			'tspt_1'	=> 'checked'
		);
		$tinfo = $this->db->get_value("SELECT tradeinfo FROM pw_memberinfo WHERE uid=" . pwEscape($this->post->uid));
		if (is_array($tinfo = unserialize($tinfo))) {
			$tinfo['alipay'] && $set['paym_2'] = 'checked';
			$tinfo['tradetype'] && $set['tradetype'] = $tinfo['tradetype'];
		}
		return $set;
	}

	function resetInfo($tid, $atcdb) {
		$reset = $this->db->get_one("SELECT t.*,mb.tradeinfo FROM pw_trade t LEFT JOIN pw_memberinfo mb USING(uid) WHERE t.tid=" . pwEscape($tid));
		$reset['tspt_' . $reset['transport']] = 'checked';
		$reset['degree_' . $reset['degree']] = 'selected';
		for ($i = 0; $i < 2; $i++) {
			$reset['paym_'.pow(2,$i)] = $reset['paymethod'] & pow(2,$i) ? 'checked' : '';
		}
		if (is_array($tinfo = unserialize($reset['tradeinfo'])) && $tinfo['tradetype']) {
			$reset['tradetype'] = $tinfo['tradetype'];
		}
		return $reset;
	}

	function _setData() {
		$goodsname	= Char_cv(GetGP('goodsname'));
		$price		= Char_cv(GetGP('price'));
		$costprice	= Char_cv(GetGP('costprice'));
		$locus		= Char_cv(GetGP('locus'));
		$mailfee	= Char_cv(GetGP('mailfee'));
		$expressfee	= Char_cv(GetGP('expressfee'));
		$emsfee		= Char_cv(GetGP('emsfee'));

		$degree = intval(GetGP('degree'));
		$ptype = intval(GetGP('ptype'));
		$goodsnum = intval(GetGP('goodsnum'));
		$paymethod	= Char_cv(GetGP('paymethod'), 1);
		$transport = intval(GetGP('transport'));

		!$goodsname && $goodsname = Char_cv($_POST['atc_title']);
		if (!is_numeric($costprice) || $costprice <= 0) {
			Showmsg('goods_setprice');
		}
		$goodsnum < 1 && Showmsg('goods_num_error');
		$paymethod && $paymethod = array_sum($paymethod);
		$paymethod < 1 && Showmsg('goods_pay_error');
		!is_numeric($price) && $price = 0;

		if ($transport) {
			!is_numeric($mailfee) && $mailfee = 0;
			!is_numeric($expressfee) && $expressfee = 0;
			!is_numeric($emsfee) && $emsfee = 0;
			if (!$mailfee && !$expressfee && !$emsfee) {
				Showmsg('goods_logistics');
			}
		} else {
			$mailfee = $expressfee = $emsfee = 0;
		}
		$goodsicon = '';
		
		$this->data['name'] = $goodsname;
		$this->data['price'] = $price;
		$this->data['costprice'] = $costprice;
		$this->data['locus'] = $locus;
		$this->data['mailfee'] = $mailfee;
		$this->data['expressfee'] = $expressfee;
		$this->data['emsfee'] = $emsfee;
		$this->data['degree'] = $degree;
		$this->data['type'] = $ptype;
		$this->data['num'] = $goodsnum;
		$this->data['paymethod'] = $paymethod;
		$this->data['transport'] = $transport;
	}

	function _setIcon() {
		global $postdata;
		if ($postdata->att) {
			$ir = $postdata->att->getIdRelate();
			if ($aid = array_search(0, $ir)) {
				$att = $postdata->att->getAttachs();
				$this->data['icon'] = $att[$aid]['attachurl'];
			}
		}
	}

	function initData() {
		$this->_setData();
	}

	function insertData($tid) {
		$this->data['tid'] = $tid;
		$this->_setIcon();
		$this->db->update("INSERT INTO pw_trade SET " . pwSqlSingle($this->data));
	}

	function modifyData($tid) {
		$this->_setData();
	}

	function updateData($tid) {
		$this->_setIcon();
		$pwSQL = array(
			'tid'		=> $tid,						'name'		=> $this->data['name'],
			'degree'	=> $this->data['degree'],		'type'		=> $this->data['type'],
			'num'		=> $this->data['num'],			'price'		=> $this->data['price'],
			'costprice'	=> $this->data['costprice'],	'locus'		=> $this->data['locus'],
			'paymethod'	=> $this->data['paymethod'],	'transport'	=> $this->data['transport'],
			'mailfee'	=> $this->data['mailfee'],		'expressfee'=> $this->data['expressfee'],
			'emsfee'	=> $this->data['emsfee']
		);
		$this->data['icon'] && $pwSQL['icon'] = $this->data['icon'];

		$this->db->update("UPDATE pw_trade SET " . pwSqlSingle($pwSQL) . " WHERE tid=" . pwEscape($tid));
	}
}
?>