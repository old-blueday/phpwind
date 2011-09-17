<?php
!defined('P_W') && exit('Forbidden');

/**
 * 团购帖
 *
 * @package Thread
 */
class postCate {

	var $db;
	var $post;
	var $forum;
	var $data;
	var $pcid;
	var $pcvaluetable;
	var $postcatedb;

	function postCate($post) {/*团购初始化*/
		global $db,$pcid;
		$this->db =& $db;
		if (is_object($post)) {
			$this->post =& $post;
			$this->forum =& $post->forum;
		}
		$this->pcid =& $pcid;
		$this->data = array(
			'tid'=> '0'
		);
		postCate::getPcCache();
	}

	function getPcCache(){
		//* @include_once pwCache::getPath(D_P.'data/bbscache/postcate_config.php');
		extract(pwCache::getData(D_P.'data/bbscache/postcate_config.php', false));
		$this->postcatedb =& $postcatedb;
	}

	function getCateHtml($pcid) {/*获取发帖团购*/
		global $tid,$imgpath;
		$postcatefielddb = array();
		$postcatehtml = "
<style>
.pp td{padding:5px 10px;}
.msg {
	background: #fff url($imgpath/pccheck.gif) no-repeat 0 -37px;
	border: 1px solid #fff;
	display: inline;
	margin-left: 5px;
	padding: 2px 2px 2px 20px;
	vertical-align : -2px;
	*vertical-align : 0;
}
.error {
	background-position: 2px -37px;
	background-color: #fef1f0;
	border-color: #ffb3b6;
	color:#f14a10;
	zoom:1;
	height:17px;
	overflow:hidden;
}
.pass {
	background-position: 2px -57px;
	width:22px;
	height:21px;
}
</style><script type=\"text/javascript\" src=\"js/pw_pccheck.js\"></script>";
		$postcatehtml .= "<script type=\"text/javascript\" src=\"js/date.js\"></script><table width=\"100%\"><tr class=\"pp f_two\"><td colspan=2>".getLangInfo('other','pc_must')."</td></tr>";

		if ($tid) {
			$pcid = (int)$pcid;
			$pcvaluetable = GetPcatetable($pcid);
			$fieldone = $this->db->get_one("SELECT * FROM $pcvaluetable WHERE tid=".S::sqlEscape($tid));
		}
		$query = $this->db->query("SELECT fieldid,name,fieldname,type,rules,descrip,ifmust,vieworder,textsize FROM pw_pcfield WHERE pcid=".S::sqlEscape($pcid)." AND ifable=1 ORDER BY vieworder,fieldid ASC");
		while ($rt = $this->db->fetch_array($query)) {
			if ($tid) $rt['fieldvalue'] = $fieldone[$rt['fieldname']];
			list($rt['name1'],$rt['name2']) = explode('{#}',$rt['name']);
			$pcfielddb[$rt['vieworder']][$rt['fieldid']] = $rt;
		}
		$tabindex = 3;//tab键
		foreach ($pcfielddb as $key => $value) {
			if ($key == 0){
				foreach ($value as $k => $v) {
					$v['tabindex'] = $tabindex;
					$ifmust = '';
					$v['ifmust'] && $ifmust = "<span class=\"s1\">*</span>";
					$postcatehtml .= "<tr class=\"pp f_two\"><td width=\"100\">$v[name1]{$ifmust}：</td><td>";
					$postcatehtml .= postCate::getCateType($v)." ".$v['name2'];
					$postcatehtml .= " <span class='gray'>$v[descrip]</span></td></tr>";
				}
			} else {
				$postcatehtml .= "<tr class=\"pp\">";
				$i = 0;
				foreach ($value as $k => $v) {
					$v['tabindex'] = $tabindex;
					$ifmust = '';
					$v['ifmust'] && $ifmust = "<span class=\"s1\">*</span>";
					if ($i == 0) {
						$postcatehtml .= "<td style=\"width:100px;\">$v[name1]{$ifmust}：</td><td>";
					}
					$i > 0 && $postcatehtml .= $v['name1'];
					$postcatehtml .= postCate::getCateType($v)." ".$v['name2'];
					$i++;
				}
				$postcatehtml .= " <span class='gray'>$v[descrip]</span></td></tr>";
			}
		}
		$postcatehtml .= "</table>";
		return $postcatehtml;
	}

	function getCateType($data) {/*获取发帖团购：字段解析*/
		global $timestamp,$pcid;
		$postcatehtml = '';
		$pccheck = $error = '';
		if ($data['ifmust']) {
			$data['ifmust'] && $pccheck = 'check="/^.+$/"';
			$error = 'error=""';
		}
		if (in_array($data['fieldname'],array('tel','phone','limitnum'))) {
			$pccheck = 'check="/^\d+$/"';
			if ($data['ifmust']) {
				$error = 'error="number_error"';
			} else {
				$error = 'error="number_error2"';
			}
		} elseif (in_array($data['fieldname'],array('price','deposit','mprice'))) {
			$pccheck = 'check="/^\d+(\.\d+)?$/"';
			if ($data['ifmust']) {
				$error = 'error="numberic_error"';
			} else {
				$error = 'error="numberic_error2"';
			}
		}
		$textsize = $data['textsize'] ? $data['textsize'] : 20;
		$data['rules'] && $data['rules'] = unserialize($data['rules']);
		if ($data['type'] == 'number') {
			if ($data['rules']['minnum'] && $data['rules']['maxnum']) {
				$pccheck = "check=\"{$data[rules][minnum]}-{$data[rules][maxnum]}\"";
				if ($data['ifmust']) {
					$error = 'error="rang_error"';
				} else {
					$error = 'error="rang_error2"';
				}
			} else {
				$pccheck = 'check="/^\d+$/"';
				if ($data['ifmust']) {
					$error = 'error="number_error"';
				} else {
					$error = 'error="number_error2"';
				}
			}

			$postcatehtml = "<input type=\"text\" $pccheck $error class=\"input\" name=\"postcate[$data[fieldname]]\" value=\"$data[fieldvalue]\" size=\"$textsize\" tabindex = \"{$data[tabindex]}\">";
			if ($data['rules']['minnum'] && $data['rules']['maxnum']) {
				$postcatehtml .= " <span class='gray'>(".getLangInfo('other','pc_defaultname')."{$data[rules][minnum]} ~ {$data[rules][maxnum]})</span>";
			}

		} elseif ($data['type'] == 'email') {
			$pccheck = 'check="/^[-a-zA-Z0-9_\.]+@([0-9A-Za-z][0-9A-Za-z-]+\.)+[A-Za-z]{2,5}$/"';
			if ($data['ifmust']) {
				$error = 'error="email_error"';
			} else {
				$error = 'error="email_error2"';
			}
			$postcatehtml = "<input type=\"text\" $pccheck $error class=\"input\" name=\"postcate[$data[fieldname]]\" value=\"$data[fieldvalue]\" size=\"$textsize\" tabindex = \"{$data[tabindex]}\"/>";
		} elseif ($data['type'] == 'range') {
			$pccheck = 'check="/^\d+$/"';
			if ($data['ifmust']) {
				$error = 'error="number_error"';
			} else {
				$error = 'error="number_error2"';
			}
			$postcatehtml = "<input type=\"text\" $pccheck $error class=\"input\" name=\"postcate[$data[fieldname]]\" value=\"$data[fieldvalue]\" size=\"$textsize\" tabindex = \"{$data[tabindex]}\"/>";
		} elseif (in_array($data['type'],array('text','img','url'))) {
			$postcatehtml = "<input type=\"text\" $pccheck $error class=\"input\" name=\"postcate[$data[fieldname]]\" value=\"$data[fieldvalue]\" size=\"$textsize\" tabindex = \"{$data[tabindex]}\"/>";
		} elseif ($data['type'] == 'radio') {
			$i = 0;
			foreach($data['rules'] as $rk => $rv){
				$i++;
				$chehcked = '';
				$rv_value = substr($rv,0,strpos($rv,'='));
				$rv_name = substr($rv,strpos($rv,'=')+1);
				if ($data['fieldvalue']) {
					$rv_value == $data['fieldvalue'] && $chehcked = 'checked';
				} elseif (in_array($data['fieldname'],array('pctype','payway','objecter','gender'))) {
					if ($data['fieldname'] == 'pctype' && $rv_value == 1) $chehcked = 'checked';
					if ($data['fieldname'] == 'payway' && $rv_value == 2) $chehcked = 'checked';
					if ($data['fieldname'] == 'objecter' && $rv_value == 1) $chehcked = 'checked';
					if ($data['fieldname'] == 'gender' && $rv_value == 1) $chehcked = 'checked';
				} elseif ($i == 1) {
					$chehcked = 'checked';
				}
				if ($data['fieldname'] == 'payway' && $rv_value == 1) {
					$onchange = "onclick=\"ifalipay(this.value)\"";
				}
				$postcatehtml .= "<span class=\"fl w\"><input $onchange type=\"radio\" name=\"postcate[$data[fieldname]]\" value=\"$rv_value\" $chehcked tabindex = \"{$data[tabindex]}\"/> $rv_name </span>";
			}
		} elseif ($data['type'] == 'checkbox') {

			foreach($data['rules'] as $ck => $cv){

				$chehcked = '';

				if ($data['ifmust']) {
					$pccheck = "check=\"1-\"";
				} else {
					$pccheck = "";
				}

				$cv_value = substr($cv,0,strpos($cv,'='));
				$cv_name = substr($cv,strpos($cv,'=')+1);
				if (strpos(",".$data['fieldvalue'].",",",".$cv_value.",") !== false) {
					$chehcked = 'checked';
				}

				$postcatehtml .= "<span class=\"fl w\"><input $pccheck type=\"checkbox\" class=\"input\" name=\"postcate[$data[fieldname]][]\" value=\"$cv_value\" $chehcked tabindex = \"{$data[tabindex]}\"/> $cv_name </span>";
			}
		} elseif ($data['type'] == 'textarea') {
			$pccheck = 'check="/^.+$/m"';
			$postcatehtml = "<textarea $pccheck type=\"text\" class=\"input\" name=\"postcate[$data[fieldname]]\" rows=\"4\" cols=\"$textsize\" tabindex = \"{$data[tabindex]}\"/>$data[fieldvalue]</textarea>";
		} elseif ($data['type'] == 'select') {
			$postcatehtml .= "<select name=\"postcate[$data[fieldname]]\" tabindex = \"{$data[tabindex]}\">";
			foreach($data['rules'] as $sk => $sv){
				$selected = '';
				$sv_value = substr($sv,0,strpos($sv,'='));
				$sv_name = substr($sv,strpos($sv,'=')+1);
				$sv_value == $data['fieldvalue'] && $selected = 'selected';
				$postcatehtml .= "<option value=\"$sv_value\" $selected>$sv_name</option>";
			}
			$postcatehtml .= "</select>";
		} elseif ($data['type'] == 'calendar') {
			!$data['fieldvalue'] && $data['fieldvalue'] = $timestamp;
			$data['fieldvalue'] = get_date($data['fieldvalue'],'Y-n-j H:i');
			($data['fieldname'] == "endtime") && $data['fieldvalue'] && $data['fieldvalue'] = $data[fieldname] ? $data[fieldvalue] : $this->_getLastMonthDay($timestamp);  //结束时间
			($data['fieldname'] == "endtime") && $showError = "onblur=\"getCalendarError();\"";
			$postcatehtml = "<input id=\"calendar_$data[fieldname]\" $showError $pccheck type=\"text\" class=\"input\" name=\"postcate[$data[fieldname]]\" value=\"$data[fieldvalue]\" onclick=\"ShowCalendar(this.id,1)\" size=\"$textsize\" tabindex = \"{$data[tabindex]}\"/>";
		} elseif ($data['type'] == 'upload') {
			$imgs = '';
			$data['fieldvalue'] && $data['fieldvalue'] = postCate::getpcurl($data['fieldvalue'],1);
			$data['fieldvalue'] && $imgs = "<span id=\"img_$data[fieldid]\"><img src=\"{$data[fieldvalue]}\" width=\"200px\"/><a href=\"javascript:;\" onclick=\"pcdelimg('$pcid','$data[fieldid]','postcate');return false;\">".getLangInfo('other','pc_delimg')."</a></span>";
			$postcatehtml .= "<input type=\"file\" class=\"input\" name=\"postcate_$data[fieldid]\" size=\"$textsize\" tabindex = \"{$data[tabindex]}\">$imgs";
		} else {
			$postcatehtml = "";
		}

		return $postcatehtml;
	}

	function getPcHtml() {/*获取发帖团购右侧模板选择*/
		global $fid,$pcid,$_G;
		$modeldb = explode(",",$this->forum->foruminfo['pcid']);

		$selectmodelhtml = '';
		$selectmodelhtml .= "<select name=\"pcid\" onchange=\"window.onbeforeunload = function(){};window.location.href='post.php?fid='+'$fid'+'&pcid='+this.value\" tabindex=\"2\">";

		foreach ($modeldb as $value) {
			$selected = '';
			$value == $pcid && $selected = 'selected';
			if (strpos(",".$_G['allowpcid'].",",",".$value.",") !== false) {
				$selectmodelhtml .= "<option value=\"$value\" $selected>{$this->postcatedb[$value][name]}</option>";
			}
		}
		$selectmodelhtml .= "</select>";

		return $selectmodelhtml;
	}

	function getCatevalue($pcid,$pcdb = array()) {/*帖子内容显示*/
		global $tid,$db_charset;
		$newpostcatevalue = $postcatevalue = $flashcatevalue = '';
		$newpostcatevalue .= "<div class=\"cates\">";
		$flashcatevalue .= "<div class=\"cate_meg_player\" ><div id=\"pwSlidePlayer\" class=\"readFlash\">";
		$postcatevalue .= "<ul class=\"cate-list\">";

		if(!isset($this->postcatedb[$pcid])) return;

		if (isset($pcdb) && count($pcdb) > 0) {
			$fieldone = $pcdb;
		} else {
			$pcid = (int)$pcid;
			$pcvaluetable = GetPcatetable($pcid);
			$fieldone = $this->db->get_one("SELECT pv.*,SUM(pm.nums) as nums FROM $pcvaluetable pv LEFT JOIN pw_pcmember pm ON pv.tid=pm.tid WHERE pv.tid=".S::sqlEscape($tid)." GROUP BY pv.tid");
		}


		$query = $this->db->query("SELECT fieldid,fieldname,name,rules,type,vieworder FROM pw_pcfield WHERE pcid=".S::sqlEscape($pcid)." ORDER BY vieworder,fieldid");

		$vieworder_mark = $i = $tmpCount = 0;
		$flash = false;
		while ($rt = $this->db->fetch_array($query)) {
			if (($rt['type'] == 'img' || $rt['type'] == 'upload') && $fieldone[$rt['fieldname']]) {
				$tmpCount++;
				$rt['type'] == 'upload' && $fieldone[$rt['fieldname']] = postCate::getpcurl($fieldone[$rt['fieldname']],1);
				$flashcatevalue .= "<div class=\"readFlash\" id=\"Switch_$rt[fieldname]\" style=\"display:none;\"><img src=\"{$fieldone[$rt[fieldname]]}\"/></div>";
				$flash = true;
			}
			if ($rt['type'] == 'textarea') {
				$fieldone[$rt['fieldname']] = nl2br($fieldone[$rt['fieldname']]);
			}
			$rt['fieldvalue'] = $fieldone[$rt['fieldname']];
			if ((S::isNatualValue($rt['fieldvalue']) || $rt['fieldname'] == 'limitnum') && $rt['type'] != 'img' && $rt['type'] != 'upload'){
				$classname =  $i%2 == 0 ? 'two' : '';
				$rt['rules'] && $rt['rules'] = unserialize($rt['rules']);
				list($rt['name1'],$rt['name2']) = explode('{#}',$rt['name']);
				if ($rt['fieldname'] == 'limitnum') {
					!$rt['fieldvalue'] && $rt['fieldvalue'] = getLangInfo('other','pc_limitnum');
				}
				if ($rt['fieldname'] == 'mprice') {
					$rt['fieldvalue'] = '<span style="text-decoration:line-through">'.$rt['fieldvalue'].'</span>';;
				}
				if ($rt['fieldname'] == 'wangwang') {
					$wang = '';
					$wang = rawurlencode(pwConvert($rt['fieldvalue'],'utf-8',$db_charset));
					$rt['fieldvalue'] .= ' <a target="_blank" href="http://amos1.taobao.com/msg.ww?v=2&uid='.$wang.'&s=1" ><img border="0" src="http://amos1.taobao.com/online.ww?v=2&uid='.$wang.'&s=1" alt="'.getLangInfo('other','pc_wangwang').'" /></a>';;
				}

				if ($rt['vieworder'] != $vieworder_mark && $vieworder_mark != 0) $postcatevalue .= "</cite></li>";
				if ($rt['vieworder'] == 0) {
					$postcatevalue .= "<li class=\"$classname\"><em>$rt[name1]：</em><cite>";
					$postcatevalue .= $this->getFieldValueHTML($rt['type'],$rt['fieldvalue'],$rt['rules']);
					$postcatevalue .=  $rt['name2']."</cite></li>";
					$i++;
				} else {
					if ($vieworder_mark != $rt['vieworder']) {
						$postcatevalue .= "<li class=\"$classname\"><em>$rt[name1]：</em><cite>";
						$postcatevalue .=  $this->getFieldValueHTML($rt['type'],$rt['fieldvalue'],$rt['rules']);
						$postcatevalue .= "$rt[name2]";
						$i++;
					} else {
						$postcatevalue .= "$rt[name1]";
						$postcatevalue .=  $this->getFieldValueHTML($rt['type'],$rt['fieldvalue'],$rt['rules']);
						$postcatevalue .= "$rt[name2]";
					}
				}
				$vieworder_mark = $rt['vieworder'];
			}
		}
		$flashcatevalue .= "<ul class=\"b\" id=\"SwitchNav\"></ul><div></div></div></div><script type=\"text/javascript\" src=\"js/sliderplayer.js\"></script><script type=\"text/javascript\">pwSliderPlayers('pwSlidePlayer');</script>";

		$vieworder_mark != 0 && $postcatevalue .= "</cite></li>";
		$postcatevalue .= "</ul></div>";

		$flash == false && $flashcatevalue = '';
		$newpostcatevalue .= $flashcatevalue.$postcatevalue;

		return array($fieldone,$newpostcatevalue);
	}

	function getFieldData($pcid='',$type='all') {/*获取分类模板信息*/
		$sql = '';
		$fielddb = array();
		if ($type == 'more') {
			is_array($pcid) && $sql .= " WHERE pcid IN(".S::sqlImplode($pcid).")";
		} elseif ($type == 'one') {
			$pcid && $sql .= " WHERE pcid=".S::sqlEscape($pcid);
		} else {
			$sql .= '';
		}

		$query = $this->db->query("SELECT fieldid,name,fieldname,pcid,vieworder,type,rules,ifable,ifsearch,ifasearch,ifmust,threadshow FROM pw_pcfield $sql ORDER BY vieworder");
		while ($rt = $this->db->fetch_array($query)) {
			$rt['name'] = str_replace('{#}','',$rt['name']);
			$fielddb[$rt['fieldid']] = $rt;
		}
		return $fielddb;
	}

	function postCheck() {
		global $groupid,$winddb;

		if ($winddb['groups']) {
			$groupids = explode(',',substr($winddb['groups'],1,-1));
			foreach ($groupids as $value) {
				if (strpos($this->forum->foruminfo['allowpost'],",$value,") !== false) {
					$ifgroups = true;
				}
			}
		}

		if ($this->forum->foruminfo['allowpost'] && strpos($this->forum->foruminfo['allowpost'],','.$groupid.',') === false && !$ifgroups && !$this->post->admincheck) {
			Showmsg('postnew_group_right');
		}
	}

	function initData() {/*初始化上传信息*/
		global $timestamp,$db_topicname,$tid,$limitnums;
		$postcate = S::getGP('postcate','P');

		$query = $this->db->query("SELECT fieldname,name,type,rules,ifmust,ifable FROM pw_pcfield WHERE pcid=".S::sqlEscape($this->pcid));
		while ($rt = $this->db->fetch_array($query)) {
			if ($rt['type'] != 'upload' && $rt['ifable'] && $rt['ifmust'] && !S::isNatualValue($postcate[$rt['fieldname']])) {
				$db_topicname = $rt['name'];
				Showmsg('topic_field_must');
			}
			if (in_array($rt['fieldname'],array('tel','phone','limitnum'))) {
				$postcate[$rt['fieldname']] && !is_numeric($postcate[$rt['fieldname']]) && Showmsg('telphone_error');
			} elseif (in_array($rt['fieldname'],array('price','deposit','mprice'))) {
				$postcate[$rt['fieldname']] && !is_numeric($postcate[$rt['fieldname']]) && Showmsg('numeric_error');
				$postcate[$rt['fieldname']] = number_format(floatval($postcate[$rt['fieldname']]), 2, '.', '');
			}

			if ($postcate[$rt['fieldname']]) {
				if ($rt['type'] == 'number') {
					!is_numeric($postcate[$rt['fieldname']]) && Showmsg('number_error');
					$limitnum = unserialize($rt['rules']);
					if ($limitnum['minnum'] && $limitnum['maxnum'] && ($postcate[$rt['fieldname']] < $limitnum['minnum'] || $postcate[$rt['fieldname']] > $limitnum['maxnum'])) {
						$db_topicname = $rt['name'];
						Showmsg('topic_number_limit');
					}
				} elseif ($rt['type'] == 'range') {
					!is_numeric($postcate[$rt['fieldname']]) && Showmsg('number_error');
				} elseif ($rt['type'] == 'email') {
					if (!preg_match("/^[-a-zA-Z0-9_\.]+@([0-9A-Za-z][0-9A-Za-z-]+\.)+[A-Za-z]{2,5}$/",$postcate[$rt['fieldname']])) {
						Showmsg('illegal_email');
					}
				} elseif ($rt['type'] == 'checkbox') {
					$checkboxs = ',';
					foreach ($postcate[$rt['fieldname']] as $value) {
						$checkboxs .= $value.',';
					}
					$postcate[$rt['fieldname']] = $checkboxs;
				} elseif ($rt['type'] == 'calendar') {
					//日期值检查
					$checkTime = strtotime($postcate[$rt['fieldname']]);
					if (!$checkTime || -1 == $checkTime){
						$GLOBALS['db_actname'] = $rt['name'];
						Showmsg('calendar_wrong_format');
					}
					//end
					$postcate[$rt['fieldname']] = PwStrtoTime($postcate[$rt['fieldname']]);
				}
			}
		}
		$limitnums = $this->db->get_value("SELECT SUM(nums) as num FROM pw_pcmember WHERE tid=".S::sqlEscape($tid));
		if ($postcate['limitnum'] && $limitnums > $postcate['limitnum']) {
			Showmsg('pclimitnum_error');
		}
		$postcate['begintime'] > $postcate['endtime'] && Showmsg('begin_endtime');
		$postcate['endtime'] < $timestamp && Showmsg ('截止时间必须大于当前时间');
		$this->data['postcate'] = serialize($postcate);
	}

	function insertData($tid,$fid) {/*操作数据库*/
		global $timestamp;
		$this->data['tid'] = $tid;
		$this->data['fid'] = $fid;
		$pcdb = unserialize($this->data['postcate']);
		unset($this->data['postcate']);

		foreach ($pcdb as $key => $value) {
			$this->data[$key] = $value;
		}
		$pcvaluetable = GetPcatetable($this->pcid);

		$this->db->pw_update(
			"SELECT tid FROM $pcvaluetable WHERE tid=".S::sqlEscape($tid),
			"UPDATE $pcvaluetable SET ".S::sqlSingle($this->data) . "WHERE tid=".S::sqlEscape($tid),
			"INSERT INTO $pcvaluetable SET " . S::sqlSingle($this->data)
		);

		/*附件上传-淡定*/
		require_once(R_P.'require/functions.php');
		L::loadClass('pcupload', 'upload', false);
		$img = new PcUpload($tid,$this->pcid);
		PwUpload::upload($img);
		pwFtpClose($GLOBALS['ftp']);
	}

	function initSearchHtml($pcid) {/*获取前台团购搜索列表*/
		global $fid, $searchname;

		$searchhtml = "<form action=\"thread.php?fid=$fid&pcid=$pcid\" method=\"post\">";
		$searchhtml .= "<input type=\"hidden\" name=\"topicsearch\" value=\"1\"><script type=\"text/javascript\" src=\"js/date.js\"></script><table>";

		$query = $this->db->query("SELECT fieldid,name,type,rules,ifsearch,ifasearch,textsize,vieworder FROM pw_pcfield WHERE pcid = ".S::sqlEscape($pcid)." AND ifable='1' AND (ifsearch='1' OR ifasearch='1') ORDER BY vieworder,fieldid ASC");
		$vieworder_mark = $ifsearch = $ifasearch = 0;
		while ($rt = $this->db->fetch_array($query)) {
			if ($rt['ifasearch'] == 1) {
				$ifasearch = '1';
				if ($rt['ifsearch'] == 0) continue;
			}
			$ifsearch = '1';
			$type = $rt['type'];
			$fieldid = $rt['fieldid'];
			list($name1,$name2) = explode('{#}',$rt['name']);

			$searchhtml .= "<tr><th>";
			$searchhtml .= $name1 ? $name1."</th><td>" : '';

			$op_key = $op_value = '';
			if (!$rt['textsize'] || $rt['textsize'] >10) {
				$textsize = 10;
			} else {
				$textsize = $rt['textsize'];
			}
			if (in_array($type,array('radio','select'))) {
				$searchhtml .= "<select name=\"searchname[".$fieldid."]\"><option value=\"\"></option>";
				foreach (unserialize($rt['rules']) as $key => $value) {
					$op_key = substr($value,0,strpos($value,'='));
					$op_value = substr($value,strpos($value,'=')+1);
					$selected = $searchname[$fieldid] == $op_key ? 'selected' : '';
					$searchhtml .= "<option value=\"".$op_key."\" $selected>".$op_value."</option>";
				}
				$searchhtml .= '</select>';
			} elseif ($type == 'checkbox') {
				foreach(unserialize($rt['rules']) as $ck => $cv){
					$op_key = substr($cv,0,strpos($cv,'='));
					$op_value = substr($cv,strpos($cv,'=')+1);
					$checked = in_array($op_key, $searchname[$fieldid]) ? 'checked' : '';
					$searchhtml .= "<input type=\"checkbox\" name=\"searchname[$fieldid][]\" value=\"$op_key\" $checked/> $op_value ";
				}
			} elseif ($type == 'calendar') {
				$searchhtml .= "<input id=\"calendar_start_$rt[fieldid]\" type=\"text\" class=\"input\" name=\"searchname[$fieldid][start]\" value=\"{$searchname[$fieldid]['start']}\" onclick=\"ShowCalendar(this.id,1)\" size=\"$textsize\"/> - <input id=\"calendar_end_$rt[fieldid]\" type=\"text\" class=\"input\" name=\"searchname[$fieldid][end]\" value=\"{$searchname[$fieldid]['end']}\" onclick=\"ShowCalendar(this.id,1)\" size=\"$textsize\"/>";
			} elseif ($type == 'range') {
				$searchhtml .= "<input type=\"text\" size=\"5\" class=\"input\" name=\"searchname[$fieldid][min]\" value=\"{$searchname[$fieldid]['min']}\"/> - <input type=\"text\" size=\"5\" class=\"input\" name=\"searchname[$fieldid][max]\" value=\"{$searchname[$fieldid]['max']}\" size=\"$textsize\"/>";
			} else {
				$searchhtml .= "<input type=\"text\" size=\"$textsize\" name=\"searchname[".$fieldid."]\" value=\"$searchname[$fieldid]\" class=\"input\">";
			}
			$searchhtml .= $name2."</td></tr>";
		}
		$searchhtml .= "<tr><th></th><td><span class=\"btn2\" style=\"margin-right:10px;\"><span><button type=\"submit\" name=\"submit\">".getLangInfo('other','pc_search')."</button></span></span>";
		$ifsearch == 0 && $searchhtml = '</td></tr></table></form>';

		$ifasearch == '1' && $searchhtml .= "<a id=\"aserach\" href=\"javascript:;\" onclick=\"sendmsg('pw_ajax.php?action=asearch&fid=$fid&pcid=$pcid','',this.id);\">".getLangInfo('other','pc_asearch')."</a></td></tr></table></form>";

		if (strpos($searchhtml,'</td></tr><input type="submit"') !== false) {
			$searchhtml = str_replace('</td></tr><input type="submit"','</td></tr><tr><th></th><td><input type="submit"',$searchhtml);
		} elseif (strpos($searchhtml,'<input type="submit" name="submit" value="') !== false) {
			$searchhtml = str_replace('<input type="submit" name="submit" value="','</td></tr><tr><th></th><td><input type="submit" name="submit" value="',$searchhtml);
		}
		return $searchhtml;
	}


	function getSearchvalue($field,$type,$alltidtype = false,$backtype = false) {/*获取搜索结果*/
		global $db_perpage,$page,$pcid,$fid,$basename;
		$field = unserialize(StrCode($field,'DECODE'));

		$sqladd = '';
		$fid && $sqladd .= " fid=".S::sqlEscape($fid);
		$fielddb = postCate::getFieldData($pcid,$type);

		foreach ($field as $key => $value) {
			if ($value) {
				if (in_array($fielddb[$key]['type'],array('number','radio','select'))) {
					$sqladd .= $sqladd ? " AND ".$fielddb[$key]['fieldname']."=".S::sqlEscape($value) : $fielddb[$key]['fieldname']."=".S::sqlEscape($value);
				} elseif ($fielddb[$key]['type'] == 'checkbox') {
					$checkboxs = '';
					foreach ($value as $cv) {
						$checkboxs .= $checkboxs ? ','.$cv : $cv;
					}
					$value = '%,'.$checkboxs.',%';
					$sqladd .= $sqladd ? " AND ".$fielddb[$key]['fieldname'] ." LIKE(".S::sqlEscape($value).")" : $fielddb[$key]['fieldname'] ." LIKE(".S::sqlEscape($value).")";
				} elseif ($fielddb[$key]['type'] == 'calendar' && ($value['start'] || $value['end'])) {

					$value['start'] && $value['start'] = PwStrtoTime($value['start']);
					$value['end'] && $value['end'] = PwStrtoTime($value['end']);
					if ($value['start'] > $value['end'] && $value['start'] && $value['end']) {
						Showmsg('calendar_error');
					}
					$calendarEnd = trim(S::sqlEscape($value['end']));
					$sqladd .= $sqladd ?
						 " AND ".$fielddb[$key]['fieldname'].">=".S::sqlEscape($value['start']).($calendarEnd == "''"?'':" AND ".$fielddb[$key]['fieldname'].'<='.$calendarEnd) :
						 $fielddb[$key]['fieldname'].">=".S::sqlEscape($value['start']).($calendarEnd == "''"?'':" AND ".$fielddb[$key]['fieldname'].'<='.$calendarEnd);
				} elseif (in_array($fielddb[$key]['type'],array('text','url','email','textarea'))) {
					$value = '%'.$value.'%';
					$sqladd .= $sqladd ? " AND ".$fielddb[$key]['fieldname'] ." LIKE(".S::sqlEscape($value).")" : $fielddb[$key]['fieldname'] ." LIKE(".S::sqlEscape($value).")";
				} elseif ($fielddb[$key]['type'] == 'range' && $value['min'] && $value['max']) {
					$sqladd .= $sqladd ? " AND ".$fielddb[$key]['fieldname'].">=".S::sqlEscape($value['min'])." AND ".$fielddb[$key]['fieldname']."<=".S::sqlEscape($value['max']) : $fielddb[$key]['fieldname'].">=".S::sqlEscape($value['min'])." AND ".$fielddb[$key]['fieldname']."<=".S::sqlEscape($value['max']);
				} else {
					$sqladd .= '';
				}
			}
		}

		if ($sqladd) {
			!$page && $page = 1;
			$start = ($page-1)*$db_perpage;
			$limit = S::sqlLimit($start,$db_perpage);
			$pcvaluetable = GetPcatetable($pcid);

			$sqladd .= $sqladd ? " AND ifrecycle=0" : " ifrecycle=0";

			$count = $this->db->get_value("SELECT COUNT(*) as count FROM $pcvaluetable WHERE $sqladd");
			$query = $this->db->query("SELECT tid FROM $pcvaluetable WHERE $sqladd $limit");
			while ($rt = $this->db->fetch_array($query)) {
				$tiddb[] = $rt['tid'];
			}
			if ($alltidtype) {
				$query = $this->db->query("SELECT tid FROM $pcvaluetable WHERE $sqladd");
				while ($rt = $this->db->fetch_array($query)) {
					$alltiddb[] = $rt['tid'];
				}
			}
			!$count && $count = -1;
		} else {
			if ($backtype) {
				adminmsg('topic_search_none',"$basename&action=postcate&pcid=$pcid");
			}
			Showmsg('topic_search_none');
		}

		return array($count,$tiddb,$alltiddb);
	}

	function getFieldValueHTML($type,$fieldvalue,$rules){/*帖子内容变量显示*/
		if ($type == 'radio') {
			$newradio = array();
			foreach($rules as $rk => $rv){
				$rv_value = substr($rv,0,strpos($rv,'='));
				$rv_name = substr($rv,strpos($rv,'=')+1);
				$newradio[$rv_value] = $rv_name;
			}
			$postcatevalue .= "{$newradio[$fieldvalue]}";

		} elseif ($type == 'checkbox') {
			$newcheckbox = array();
			foreach($rules as $ck => $cv){
				$cv_value = substr($cv,0,strpos($cv,'='));
				$cv_name = substr($cv,strpos($cv,'=')+1);
				$newcheckbox[$cv_value] = $cv_name;
			}
			$postcatevalues = '';
			foreach (explode(",",$fieldvalue) as $value) {
				if ($value) {
					$postcatevalues .= $postcatevalues ? ",".$newcheckbox[$value] : $newcheckbox[$value];
				}
			}
			$postcatevalue .= $postcatevalues;

		} elseif ($type == 'select') {
			$newselect = array();
			foreach($rules as $sk => $sv){
				$sv_value = substr($sv,0,strpos($sv,'='));
				$sv_name = substr($sv,strpos($sv,'=')+1);
				$newselect[$sv_value] = $sv_name;
			}
			$postcatevalue .= "{$newselect[$fieldvalue]}";
		} elseif ($type == 'url') {
			$postcatevalue .= "<a href=\"$fieldvalue\" target=\"_blank\">$fieldvalue</a>";
		} /*elseif ($type == 'img') {
			$postcatevalue .= "<img src=\"$fieldvalue\">";
		}*/ elseif ($type == 'calendar') {
			$postcatevalue .= get_date($fieldvalue,'Y-n-j H:i');
		} else {
			$postcatevalue .= "$fieldvalue";
		}

		return $postcatevalue;

	}
	function getAsearchHTML($type,$fieldid,$size,$rules){/*获取高级搜索*/

		!$size && $size = 20;
		if (in_array($type,array('radio','select'))) {
			$searchhtml .= "<select name=\"searchname[".$fieldid."]\"><option value=\"\"></option>";
			foreach (unserialize($rules) as $key => $value) {
				$op_key = substr($value,0,strpos($value,'='));
				$op_value = substr($value,strpos($value,'=')+1);
				$searchhtml .= "<option value=\"".$op_key."\">".$op_value."</option>";
			}
			$searchhtml .= '</select>';
		} elseif ($type == 'checkbox') {
			foreach(unserialize($rules) as $ck => $cv){
				$op_key = substr($cv,0,strpos($cv,'='));
				$op_value = substr($cv,strpos($cv,'=')+1);
				$searchhtml .= "<input type=\"checkbox\" class=\"input\" name=\"searchname[$fieldid][]\" value=\"$op_key\"/> $op_value ";
			}
		} elseif ($type == 'calendar') {
			$searchhtml .= "<input id=\"calendar_start_searchname[$fieldid]\" type=\"text\" class=\"input\" name=\"searchname[$fieldid][start]\" onclick=\"ShowCalendar(this.id,1)\" class=\"fl\" size=\"$size\"/> - <input id=\"calendar_end_searchname[$fieldid]\" type=\"text\" class=\"input\" name=\"searchname[$fieldid][end]\" onclick=\"ShowCalendar(this.id,1)\" class=\"fl\" size=\"$size\"/>";
		} elseif ($type == 'range') {
			$searchhtml .= "<input type=\"text\"  class=\"input\" name=\"searchname[$fieldid][min]\"/> - <input type=\"text\" size=\"5\" class=\"input\" name=\"field[$fieldid][max]\" size=\"$size\"/>";
		} else {
			$searchhtml .= "<input type=\"text\" name=\"searchname[".$fieldid."]\" value=\"\" class=\"input\" size=\"$size\">";
		}
		return $searchhtml;
	}

	function getViewright($pcid,$tid){/*是否允许查看参与人列表*/
		global $groupid,$winduid,$isadminright;

		$pcuid = $this->db->get_value("SELECT uid FROM pw_pcmember WHERE tid=".S::sqlEscape($tid)." AND uid=".S::sqlEscape($winduid));
		if ($isadminright || strpos($this->postcatedb[$pcid]['viewright'],','.$groupid.',') !== false || $pcuid) {
			return array($pcuid,1);
		} else {
			return array($pcuid,0);
		}
	}

	function getAdminright($pcid,$authorid){/*是否允许管理参与人列表*/
		global $groupid,$winduid,$isGM,$isBM;

		if($authorid == $winduid || $isGM){
			return 1;//发起人、创始人
		} elseif ($groupid != 5 && strpos($this->postcatedb[$pcid]['adminright'],','.$groupid.',') !== false || $groupid == 5 && $isBM && strpos($this->postcatedb[$pcid]['adminright'],','.$groupid.',') !== false) {
			return 2;//既是参与者，又是管理者
		} else {
			return 0;
		}

	}

	function getpcurl($path,$thumb = false) {
		global $attachdir;
		$lastpos = strrpos($path,'/') + 1;
		$s_path = substr($path, 0, $lastpos) . 's_' . substr($path, $lastpos);

		if (file_exists("$attachdir/$s_path") && $thumb) {
			$newpath = $s_path;
		} else {
			$newpath = $path;
		}

		list($newpath) = geturl($newpath, 'show');
		return $newpath;
	}
	
	/**
	 * 获取这个月最后一天
	 */
	function _getLastMonthDay($timestamp) { 
		$date = get_date($timestamp);
		$dateArr = explode(" ",$date);
		$dateArr[0] = explode("-", $dateArr[0]);
		$dateArr[0][1] = $dateArr[0][1] + 1;
		$b = mktime(0,0,0,$dateArr[0][1],1,$dateArr[0][0]);
		$b = $b - 1;
		$c = date("Y-m-d",$b);
		$c = $c." ".$dateArr[1];
		return $c;
	}
}
?>