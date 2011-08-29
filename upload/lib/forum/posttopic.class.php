<?php
!defined('P_W') && exit('Forbidden');

/**
 * TopicThread
 * 
 * @package Thread
 */
class postTopic {

	var $db;
	var $post;
	var $forum;
	var $data;
	var $cateid;
	var $modelid;
	var $tablename;
	var $topiccatedb;
	var $topicmodeldb;

	function postTopic($post) {/*分类信息初始化*/
		global $db,$cateid,$modelid;
		$this->db =& $db;
		if (is_object($post)) {
			$this->post =& $post;
			$this->forum =& $post->forum;
		}
		$this->cateid =& $cateid;
		$this->modelid =& $modelid;

		$this->data = array(
			'tid'		=> '0'
		);
		postTopic::getTopicCache();
	}

	function getTopicCache(){

		//* @include pwCache::getPath(D_P.'data/bbscache/topic_config.php');
		extract(pwCache::getData(D_P.'data/bbscache/topic_config.php', false));
		$this->topiccatedb =& $topiccatedb;
		$this->topicmodeldb =& $topicmodeldb;

	}

	function getTopicHtml($modelid) {/*获取发帖分类信息*/
		global $tid,$imgpath;
		$topicfielddb = array();
		$topichtml = "
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
</style><script language=\"JavaScript\" src=\"js/pw_pccheck.js\"></script>";
		$topichtml .= "<script language=\"JavaScript\" src=\"js/date.js\"></script><script language=\"JavaScript\" src=\"js/desktop/Compatibility.js\"></script><table width=\"100%\"><tr class=\"pp f_two\"><td colspan=2>".getLangInfo('other','pc_must')."</td></tr>";

		if ($tid) {
			$tablename = GetTopcitable($modelid);
			$fieldone = $this->db->get_one("SELECT * FROM $tablename WHERE tid=".S::sqlEscape($tid));
		}
		$query = $this->db->query("SELECT fieldid,name,fieldname,type,rules,descrip,ifmust,vieworder,textsize FROM pw_topicfield WHERE modelid=".S::sqlEscape($modelid)." AND ifable=1 ORDER BY vieworder,fieldid ASC");
		while ($rt = $this->db->fetch_array($query)) {
			if ($tid) $rt['fieldvalue'] = $fieldone[$rt['fieldname']];
			list($rt['name1'],$rt['name2']) = explode('{#}',$rt['name']);
			$topicfielddb[$rt['vieworder']][$rt['fieldid']] = $rt;
		}
		$tabindex = 3;//tab键
		foreach ($topicfielddb as $key => $value) {
			if ($key == 0){
				foreach ($value as $k => $v) {
					$v['tabindex'] = $tabindex;
					$ifmust = '';
					$v['ifmust'] && $ifmust = "<span class=\"s1\">*</span>";
					$topichtml .= "<tr class=\"pp f_two\"><td width=\"100\">$v[name1]：{$ifmust}</td><td>";
					$topichtml .= postTopic::getTopicType($v)." ".$v['name2'];
					$topichtml .= " <span class='gray'>$v[descrip]</span></td></tr>";
				}
			} else {
				$topichtml .= "<tr class=\"pp\">";
				$i = 0;
				foreach ($value as $k => $v) {
					$v['tabindex'] = $tabindex;
					$ifmust = '';
					$v['ifmust'] && $ifmust = "<span class=\"s1\">*</span>";
					if ($i == 0) {
						$topichtml .= "<td style=\"width:100px;\">$v[name1]：{$ifmust}</td><td>";
					}
					$i > 0 && $topichtml .= $v['name1'].'&nbsp;';
					$topichtml .= postTopic::getTopicType($v)." ".$v['name2'];
					$i++;
				}
				$topichtml .= " <span class='gray'>$v[descrip]</span></td></tr>";
			}
			$tabindex++;
		}
		$topichtml .= "</table>";
		return $topichtml;
	}

	function getTopicType($data) {/*获取发帖分类信息：字段解析*/
		global $timestamp,$modelid;
		$topichtml = '';
		$pccheck = $error = '';
		if ($data['ifmust']) {
			$data['ifmust'] && $pccheck = 'check="/^.+$/"';
		}
		
		$textsize = $data['textsize'] ? $data['textsize'] : 20;
		$data['rules'] && $data['rules'] = unserialize($data['rules']);
		if ($data['type'] == 'number') {
			$data['rules']['minnum'] === '' && $data['rules']['minnum'] = 'n';
			$data['rules']['maxnum'] === '' && $data['rules']['maxnum'] = 'n';
			$pccheck = "check=\"{$data[rules][minnum]}-{$data[rules][maxnum]}\"";
			$error = $data['ifmust'] ? 'error="rang_error"' : 'error="rang_error2"';
			$topichtml = "<input type=\"text\" $pccheck $error class=\"input\" name=\"topic[$data[fieldid]]\" value=\"$data[fieldvalue]\" size=\"$textsize\" tabindex = \"{$data[tabindex]}\">";
			if ($data['rules']['minnum'] !== '' && $data['rules']['maxnum'] !== '') {
				$topichtml .= " <span class='gray'>(".getLanginfo('other','pc_defaultname')."{$data[rules][minnum]} ~ {$data[rules][maxnum]})</span>";
			}
		} elseif ($data['type'] == 'email') {
			$pccheck = 'check="/^[-a-zA-Z0-9_\.]+@([0-9A-Za-z][0-9A-Za-z-]+\.)+[A-Za-z]{2,5}$/"';
			if ($data['ifmust']) {
				$error = 'error="email_error"';
			} else {
				$error = 'error="email_error2"';
			}
			$topichtml = "<input type=\"text\" $pccheck $error class=\"input\" name=\"topic[$data[fieldid]]\" value=\"$data[fieldvalue]\" size=\"$textsize\"  tabindex = \"{$data[tabindex]}\" />";
		} elseif ($data['type'] == 'range') {
			$pccheck = 'check="/^\d+$/"';
			if ($data['ifmust']) {
				$error = 'error="number_error"';
			} else {
				$error = 'error="number_error2"';
			}
			$topichtml = "<input type=\"text\" $pccheck $error class=\"input\" name=\"topic[$data[fieldid]]\" value=\"$data[fieldvalue]\" size=\"$textsize\"  tabindex = \"{$data[tabindex]}\" />";
		} elseif (in_array($data['type'],array('text','img','url'))) {
			$addfieldvalue = '';
			$data['type']=='url' && !$data[fieldvalue] && $addfieldvalue='http://';
			$topichtml = "<input type=\"text\" $pccheck $error class=\"input\" name=\"topic[$data[fieldid]]\" value=\"$addfieldvalue$data[fieldvalue]\" size=\"$textsize\"  tabindex = \"{$data[tabindex]}\" />";
		} elseif ($data['type'] == 'radio') {
			$i = 0;
			foreach($data['rules'] as $rk => $rv){
				$i++;
				$chehcked = '';
				$rv_value = substr($rv,0,strpos($rv,'='));
		   		$rv_name = substr($rv,strpos($rv,'=')+1);
				if ($data['fieldvalue']) {
					$rv_value == $data['fieldvalue'] && $chehcked = 'checked';
				} elseif ($i == 1) {
					$chehcked = 'checked';
				}
				$topichtml .= "<span class=\"fl w\"><input type=\"radio\" name=\"topic[$data[fieldid]]\" value=\"$rv_value\" $chehcked  tabindex = \"{$data[tabindex]}\" /> $rv_name </span>";
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
				$topichtml .= "<span class=\"fl w\"><input $pccheck type=\"checkbox\" name=\"topic[$data[fieldid]][]\" value=\"$cv_value\" $chehcked  tabindex = \"{$data[tabindex]}\" /> $cv_name </span>";
			}
		} elseif ($data['type'] == 'textarea') {
			$topichtml = "<textarea type=\"text\" $pccheck name=\"topic[$data[fieldid]]\" rows=\"4\" class=\"input\" cols=\"$textsize\"  tabindex = \"{$data[tabindex]}\" />$data[fieldvalue]</textarea>";
		} elseif ($data['type'] == 'select') {
			$topichtml .= "<select name=\"topic[$data[fieldid]]\"  tabindex = \"{$data[tabindex]}\" >";
			foreach($data['rules'] as $sk => $sv){
				$selected = '';
				$sv_value = substr($sv,0,strpos($sv,'='));
				$sv_name = substr($sv,strpos($sv,'=')+1);
				if($sv_value == $data['fieldvalue']) $selected = 'selected';
				$topichtml .= "<option value=\"$sv_value\" $selected>$sv_name</option>";
			}
			$topichtml .= "</select>";
		} elseif ($data['type'] == 'calendar') {
			!$data['fieldvalue'] && $data['fieldvalue'] = $timestamp;
			$data['fieldvalue'] = get_date($data['fieldvalue'],'Y-n-j');
			$topichtml = "<input id=\"calendar_$data[fieldid]\" $pccheck type=\"text\" class=\"input\" name=\"topic[$data[fieldid]]\" value=\"$data[fieldvalue]\" onclick=\"ShowCalendar(this.id,0)\" size=\"$textsize\"  tabindex = \"{$data[tabindex]}\" />";
		} elseif ($data['type'] == 'upload') {
			$imgs = '';
			$data['fieldvalue'] && $data['fieldvalue'] = postTopic::getpcurl($data['fieldvalue'],1);
			$data['fieldvalue'] && $imgs = "<span id=\"img_$data[fieldid]\"><img src=\"{$data[fieldvalue]}\" width=\"240px\"/><a href=\"javascript:;\" onclick=\"pcdelimg('$modelid','$data[fieldid]','topic');return false;\">".getLangInfo('other','pc_delimg')."</a></span>";
			$topichtml .= "<input type=\"file\" class=\"bt\" name=\"topic_$data[fieldid]\" size=\"$textsize\"  tabindex = \"{$data[tabindex]}\" >$imgs";
		} else {
			$topichtml = "";
		}
		
		return $topichtml;
	}

	function getModelHtml() {/*获取发帖分类信息右侧模板选择*/
		global $fid,$modelid;
		$modeldb = explode(",",$this->forum->foruminfo['modelid']);

		$selectmodelhtml = '';
		$selectmodelhtml .= "<select name=\"modelid\" onchange=\"window.onbeforeunload = function(){};window.location.href='post.php?fid='+'$fid'+'&modelid='+this.value\"  tabindex=\"2\">";

		foreach ($modeldb as $value) {
			$selected = '';
			$value == $modelid && $selected = 'selected';
			$selectmodelhtml .= "<option value=\"$value\" $selected>{$this->topicmodeldb[$value][name]}</option>";
		}
		$selectmodelhtml .= "</select>";

		return $selectmodelhtml;
	}

	function getTopicvalue($modelid,$pcdb = array()) {/*帖子内容显示*/
		global $tid;
		$newtopicvalue = $topicvalue = $flashtopicvalue = '';

		$newtopicvalue .= "<div class=\"cates\">";
		$flashtopicvalue .= "<div class=\"cate_meg_player\" ><div id=\"pwSlidePlayer\" class=\"readFlash\">";
		$topicvalue .= "<ul class=\"cate-list\">";
		if(!isset($this->topicmodeldb[$modelid])) return;

		if (isset($pcdb) && count($pcdb) > 0) {
			$fieldone = $pcdb;
		} else {
			$tablename = GetTopcitable($modelid);
			$fieldone = $this->db->get_one("SELECT * FROM $tablename WHERE tid=".S::sqlEscape($tid));
		}

		$query = $this->db->query("SELECT fieldid,fieldname,name,rules,type,vieworder FROM pw_topicfield WHERE modelid=".S::sqlEscape($modelid)." ORDER BY vieworder,fieldid");

		$vieworder_mark = $i = $tmpCount = 0;
		$flash = false;
		while ($rt = $this->db->fetch_array($query)) {
			if (($rt['type'] == 'img' || $rt['type'] == 'upload') && $fieldone[$rt['fieldname']]) {
				$tmpCount++;
				$rt['type'] == 'upload' && $fieldone[$rt['fieldname']] = postTopic::getpcurl($fieldone[$rt['fieldname']],1);
				$flashtopicvalue .= "<div class=\"readFlash\" id=\"Switch_$rt[fieldname]\" style=\"display:none;\"><img src=\"{$fieldone[$rt[fieldname]]}\" /></div>";
				$flash = true;
			}
			if($rt['type'] == 'textarea') {
				$fieldone[$rt['fieldname']] = nl2br($fieldone[$rt['fieldname']]);
			}
			$rt['fieldvalue'] = $fieldone[$rt['fieldname']];
			if (S::isNatualValue($rt['fieldvalue']) && $rt['type'] != 'img' && $rt['type'] != 'upload'){
				$classname =  $i%2 == 0 ? 'two' : '';
				$rt['rules'] && $rt['rules'] = unserialize($rt['rules']);
				list($rt['name1'],$rt['name2']) = explode('{#}',$rt['name']);
				if ($rt['vieworder'] != $vieworder_mark && $vieworder_mark != 0) $topicvalue .= "</cite></li>";
				if ($rt['vieworder'] == 0) {
					$topicvalue .= "<li class=\"$classname\"><em>$rt[name1]：</em><cite>";
					$topicvalue .= $this->getFieldValueHTML($rt['type'],$rt['fieldvalue'],$rt['rules']);
					$topicvalue .=  $rt['name2']."</cite></li>";
					$i++;
				} else {
					if($vieworder_mark != $rt['vieworder']) {
						$topicvalue .= "<li class=\"$classname\"><em>$rt[name1]：</em><cite>";
						$topicvalue .=  $this->getFieldValueHTML($rt['type'],$rt['fieldvalue'],$rt['rules']);
						$topicvalue .= "$rt[name2]";
						$i++;
					} else {
						$topicvalue .= "$rt[name1]";
						$topicvalue .=  $this->getFieldValueHTML($rt['type'],$rt['fieldvalue'],$rt['rules']);
						$topicvalue .= "$rt[name2]";
					}
				}
				$vieworder_mark = $rt['vieworder'];
			}
		}
		$flashtopicvalue .= "<ul class=\"b\" id=\"SwitchNav\"></ul><div></div></div></div><script type=\"text/javascript\" src=\"js/sliderplayer.js\"></script><script language=\"JavaScript\">pwSliderPlayers('pwSlidePlayer');</script>";
		$vieworder_mark !=0 && $topicvalue .= "</cite></li>";
		$topicvalue .= "</ul></div>";

		$flash == false && $flashtopicvalue = '';
		$newtopicvalue .= $flashtopicvalue.$topicvalue;

		return $newtopicvalue;
	}

	function getFieldData($modelid='',$type='all') {/*获取分类模板信息*/
		$sql = '';
		$fielddb = array();
		if ($type == 'more') {
			is_array($modelid) && $sql .= " WHERE modelid IN(".S::sqlImplode($modelid).")";
		} elseif ($type == 'one') {
			$modelid && $sql .= " WHERE modelid=".S::sqlEscape($modelid);
		} else {
			$sql .= '';
		}

		$query = $this->db->query("SELECT fieldid,name,fieldname,modelid,vieworder,type,rules,ifable,ifsearch,ifasearch,ifmust,threadshow FROM pw_topicfield $sql ORDER BY vieworder");
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
		global $timestamp,$db_topicname;
		$topic = S::getGP('topic','P');

		$query = $this->db->query("SELECT fieldid,name,type,rules,ifmust,ifable FROM pw_topicfield WHERE modelid=".S::sqlEscape($this->modelid));
		while ($rt = $this->db->fetch_array($query)) {
			if ($rt['type'] != 'upload' && $rt['ifable'] && $rt['ifmust'] && !S::isNatualValue($topic[$rt['fieldid']])) {
				$db_topicname = $rt['name'];
				Showmsg('topic_field_must');
			}
			if ($topic[$rt['fieldid']]) {
				if ($rt['type'] == 'number') {
					!is_numeric($topic[$rt['fieldid']]) && Showmsg('number_error');
					$limitnum = unserialize($rt['rules']);
					if (($limitnum['minnum'] !== '' && $topic[$rt['fieldid']] < $limitnum['minnum']) || ($limitnum['maxnum'] !== '' && $topic[$rt['fieldid']] > $limitnum['maxnum'])) {
						$db_topicname = $rt['name'];
						Showmsg('topic_number_limit');
					}
				} elseif ($rt['type'] == 'range') {
					!is_numeric($topic[$rt['fieldid']]) && Showmsg('number_error');
				} elseif ($rt['type'] == 'email') {
					if (!preg_match("/^[-a-zA-Z0-9_\.]+@([0-9A-Za-z][0-9A-Za-z-]+\.)+[A-Za-z]{2,5}$/",$topic[$rt['fieldid']])) {
						Showmsg('illegal_email');
					}
				} elseif ($rt['type'] == 'checkbox') {
					$checkboxs = ',';
					foreach ($topic[$rt['fieldid']] as $value) {
						$checkboxs .= $value.',';
					}
					$topic[$rt['fieldid']] = $checkboxs;
				} elseif ($rt['type'] == 'calendar') {
					$topic[$rt['fieldid']] = PwStrtoTime($topic[$rt['fieldid']]);
				}
			}
		}

		$this->data['topic'] = serialize($topic);
	}

	function insertData($tid,$fid) {/*操作数据库*/
		$this->data['tid'] = $tid;
		$this->data['fid'] = $fid;
		$topicdb = unserialize($this->data['topic']);
		unset($this->data['topic']);

		foreach ($topicdb as $key => $value) {
			$this->data['field'.$key] = $value;
		}
		$tablename = GetTopcitable($this->modelid);

		$this->db->pw_update(
			"SELECT tid FROM $tablename WHERE tid=".S::sqlEscape($tid),
			"UPDATE $tablename SET ".S::sqlSingle($this->data) . "WHERE tid=".S::sqlEscape($tid),
			"INSERT INTO $tablename SET " . S::sqlSingle($this->data)
		);

		/*附件上传*/
		require_once(R_P.'require/functions.php');
		L::loadClass('pcupload', 'upload', false);
		$img = new PcUpload($tid,$this->modelid);
		PwUpload::upload($img);
		pwFtpClose($GLOBALS['ftp']);

	}
	function initSearchHtml($modelid) {/*获取前台分类信息搜索列表*/
		global $fid, $searchname;
		$query = $this->db->query("SELECT fieldid,name,type,rules,ifsearch,ifasearch,textsize,vieworder FROM pw_topicfield WHERE modelid = ".S::sqlEscape($modelid)." AND ifable='1' AND (ifsearch='1' OR ifasearch='1') ORDER BY vieworder ASC,fieldid ASC");
		$count=$this->db->num_rows($query);
		if($count == 0) {
			return null;
		}
		$searchhtml = "<form action=\"thread.php?fid=$fid&modelid=$modelid\" method=\"post\">";
		$searchhtml .= "<input type=\"hidden\" name=\"topicsearch\" value=\"1\"><script language=\"JavaScript\" src=\"js/date.js\"></script><table>";
		$vieworder_mark = $ifsearch = $ifasearch = 0;
		while ($rt = $this->db->fetch_array($query)) {
			if($rt['ifasearch'] == 1) {
				$ifsearch = '1';
				if ($rt['ifsearch'] == 0) continue;
			}
			$type = $rt['type'];
			$fieldid = $rt['fieldid'];
			list($name1,$name2) = explode('{#}',$rt['name']);

			$searchhtml .= "<tr><th>";
			$searchhtml .= $name1 ? $name1."</th><td>" : '';

			$op_key = $op_value = '';
			if (!$rt['textsize'] || $rt['textsize'] >10){
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
					$checked = S::isArray($searchname[$fieldid]) && in_array($op_key, $searchname[$fieldid]) ? 'checked' : '';
					$searchhtml .= "<input type=\"checkbox\" name=\"searchname[$fieldid][]\" value=\"$op_key\" $checked/> $op_value ";
				}
			} elseif ($type == 'calendar') {
				$searchhtml .= "<input id=\"calendar_start_$rt[fieldid]\" type=\"text\" class=\"input\" name=\"searchname[$fieldid][start]\" value=\"{$searchname[$fieldid]['start']}\" onclick=\"ShowCalendar(this.id,0)\" size=\"$textsize\"/> - <input id=\"calendar_end_$rt[fieldid]\" type=\"text\" class=\"input\" name=\"searchname[$fieldid][end]\" value=\"{$searchname[$fieldid]['end']}\" onclick=\"ShowCalendar(this.id,0)\" size=\"$textsize\"/>";
			} elseif ($type == 'range') {
				$searchhtml .= "<input type=\"text\" size=\"5\" class=\"input\" name=\"searchname[$fieldid][min]\" value=\"{$searchname[$fieldid]['min']}\"/> - <input type=\"text\" class=\"input\" name=\"searchname[$fieldid][max]\" value=\"{$searchname[$fieldid]['max']}\"  size=\"$textsize\"/>";
			} else {
				$searchhtml .= "<input type=\"text\" size=\"$textsize\" name=\"searchname[".$fieldid."]\" value=\"$searchname[$fieldid]\" class=\"input\">";
			}
			$searchhtml .= $name2."</td></tr>";
		}
		$searchhtml .= "<tr><th></th><td><span class=\"btn2\" style=\"margin-right:10px;\"><span><button type=\"submit\" name=\"submit\">".getLangInfo('other','pc_search')."</button></span></span>";
		$ifsearch == 0 && $searchhtml .= "</td></tr></table></form>";
		$ifsearch == '1' && $searchhtml .= "<a id=\"aserach\" href=\"javascript:;\" onclick=\"sendmsg('pw_ajax.php?action=asearch&fid=$fid&modelid=$modelid','',this.id);\">".getLangInfo('other','pc_asearch')."</a></td></tr></table></form>";

		if (strpos($searchhtml,'</td></tr><input type="submit"') !== false) {
			$searchhtml = str_replace('</td></tr><input type="submit"','</td></tr><tr><th></th><td><input type="submit"',$searchhtml);
		} elseif (strpos($searchhtml,'<input type="submit" name="submit" value="') !== false) {
			$searchhtml = str_replace('<input type="submit" name="submit" value="','</td></tr><tr><th></th><td><input type="submit" name="submit" value="',$searchhtml);
		}
		return $searchhtml;
	}


	function getSearchvalue($field,$type,$alltidtype = false,$backtype = false) {/*获取搜索结果*/
		global $db_perpage,$page,$modelid,$fid,$basename;
		$field = unserialize(StrCode($field,'DECODE'));

		$sqladd = '';
		$fid && $sqladd .= " fid=".S::sqlEscape($fid);
		$fielddb = postTopic::getFieldData($modelid,$type);

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

					$sqladd .= $sqladd ? " AND ".$fielddb[$key]['fieldname'].">=".S::sqlEscape($value['start'])." AND ".$fielddb[$key]['fieldname']."<=".S::sqlEscape($value['end']) : $fielddb[$key]['fieldname'].">=".S::sqlEscape($value['start'])." AND ".$fielddb[$key]['fieldname']."<=".S::sqlEscape($value['end']);

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
			$tablename = GetTopcitable($modelid);

			$sqladd .= $sqladd ? " AND ifrecycle=0" : " ifrecycle=0";

			$count = $this->db->get_value("SELECT COUNT(*) as count FROM $tablename WHERE $sqladd");
			$query = $this->db->query("SELECT tid FROM $tablename WHERE $sqladd $limit");
			while ($rt = $this->db->fetch_array($query)) {
				$tiddb[] = $rt['tid'];
			}
			if ($alltidtype) {
				$query = $this->db->query("SELECT tid FROM $tablename WHERE $sqladd");
				while ($rt = $this->db->fetch_array($query)) {
					$alltiddb[] = $rt['tid'];
				}
			}
			!$count && $count = -1;
		} else {
			if ($backtype) {
				adminmsg('topic_search_none',"$basename&action=topic&modelid=$modelid");
			}
			Showmsg('topic_search_none');
		}

		return array($count,$tiddb,$alltiddb);
	}

	function getFieldValueHTML($type,$fieldvalue,$rules){
		if ($type == 'radio') {
			$newradio = array();
			foreach($rules as $rk => $rv){
				$rv_value = substr($rv,0,strpos($rv,'='));
				$rv_name = substr($rv,strpos($rv,'=')+1);
				$newradio[$rv_value] = $rv_name;
			}
			$topicvalue .= "{$newradio[$fieldvalue]}";

		} elseif ($type == 'checkbox') {
			$newcheckbox = array();
			foreach($rules as $ck => $cv){
				$cv_value = substr($cv,0,strpos($cv,'='));
				$cv_name = substr($cv,strpos($cv,'=')+1);
				$newcheckbox[$cv_value] = $cv_name;
			}
			$topicvalues = '';
			foreach (explode(",",$fieldvalue) as $value) {
				if ($value) {
					$topicvalues .= $topicvalues ? ",".$newcheckbox[$value] : $newcheckbox[$value];
				}
			}
			$topicvalue .= $topicvalues;

		} elseif ($type == 'select') {
			$newselect = array();
			foreach($rules as $sk => $sv){
				$sv_value = substr($sv,0,strpos($sv,'='));
				$sv_name = substr($sv,strpos($sv,'=')+1);
				$newselect[$sv_value] = $sv_name;
			}
			$topicvalue .= "{$newselect[$fieldvalue]}";
		} elseif ($type == 'url') {
			$topicvalue .= "<a href=\"$fieldvalue\" target=\"_blank\">$fieldvalue</a>";
		} /*elseif ($type == 'img') {
			$topicvalue .= "<img src=\"$fieldvalue\">";
		}*/ elseif ($type == 'calendar') {
			$topicvalue .= get_date($fieldvalue,'Y-n-j');
		} else {
			$topicvalue .= "$fieldvalue";
		}

		return $topicvalue;

	}

	function getAsearchHTML($type,$fieldid,$size,$rules){

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
			$searchhtml .= "<input id=\"calendar_start_searchname[$fieldid]\" type=\"text\" class=\"input\" name=\"searchname[$fieldid][start]\" onclick=\"ShowCalendar(this.id,0)\" class=\"fl\" size=\"$size\"/> - <input id=\"calendar_end_searchname[$fieldid]\" type=\"text\" class=\"input\" name=\"searchname[$fieldid][end]\" onclick=\"ShowCalendar(this.id,0)\" class=\"fl\" size=\"$size\"/>";
		} elseif ($type == 'range') {
			$searchhtml .= "<input type=\"text\"  class=\"input\" name=\"searchname[$fieldid][min]\"/> - <input type=\"text\" size=\"5\" class=\"input\" name=\"field[$fieldid][max]\" size=\"$size\"/>";
		} else {
			$searchhtml .= "<input type=\"text\" name=\"searchname[".$fieldid."]\" value=\"\" class=\"input\"  size=\"$size\">";
		}
		return $searchhtml;
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
}
?>