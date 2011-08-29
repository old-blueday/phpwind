<?php
!defined('P_W') && exit('Forbidden');

L::loadClass('PostActivity', 'activity', false);
require_once(R_P . 'require/functions.php');

class PW_ActivityForBbs extends PW_PostActivity {

	/**
	 * 获取活动发帖时的展示HTML
	 * @param int $actmid 活动子分类的ID
	 * @param int $tid 帖子的ID
	 * @return string 展示的HTML
	 * @access public
	 */
	function getActHtml($actmid,$tid = 0) {
		global $imgpath,$mergedField;
		$activityFieldDb = array();
		$activityPostFieldsHtml = '<p class="w_title_p">'.getLangInfo('other','pc_must').'</p><br />'."<link rel=\"stylesheet\" type=\"text/css\" href=\"$imgpath/activity/post.css\" />";
		$activityPostFieldsHtml .= $this->getCheckPresetErrorMessage() . '
		<script type="text/javascript" src="js/date.js"></script><script type="text/javascript">
function addElement(basename) {
	var s = getObj(basename+\'_child\').firstChild.cloneNode(true);
	getObj(basename+\'_father\').appendChild(s);
}
function delParentElement(obj) {
	var o = obj.parentNode.parentNode;
	o.parentNode.removeChild(o);
}
function toggleCalendarInputValue(obj, defaultInputValue) {
	if (!obj.value) {
		obj.value = defaultInputValue;
		if (obj.className.indexOf("gray") == -1) {
			obj.className += " gray";
		}
	} else {
		obj.className = obj.className.replace("gray", "");
	}
}
</script>
<table width="100%" cellspacing="0" cellpadding="0" border="0">';

		if ($tid) { //编辑状态
			$defaultValueTableName = getActivityValueTableNameByActmid();
			$userDefinedValueTableName = getActivityValueTableNameByActmid($actmid, 1, 1);
			$mergedField = array();
			$mergedField = $this->db->get_one("SELECT actmid,iscancel,starttime,endtime,location,contact,telephone,picture1,picture2,picture3,picture4,picture5,signupstarttime,signupendtime,minparticipant,maxparticipant,userlimit,specificuserlimit,genderlimit,fees,feesdetail,paymethod,ut.* FROM $defaultValueTableName dt LEFT JOIN $userDefinedValueTableName ut USING(tid) WHERE dt.tid=".S::sqlEscape($tid));
			$mergedField['iscancel'] && Showmsg('act_iscancel_y_modify');//活动取消，无法编辑
		}
		$query = $this->db->query("SELECT * FROM pw_activityfield WHERE actmid=".S::sqlEscape($actmid)." AND ifable=1 ORDER BY ifdel ASC, vieworder ASC, fieldid ASC");
		while ($rt = $this->db->fetch_array($query)) {
			if ($tid) { //编辑情况下保存字段的值
				$rt['fieldvalue'] = $mergedField[$rt['fieldname']];
				$rt['isedit'] = true;
			} else {
				$rt['fieldvalue'] = '';
				$rt['isedit'] = false;
			}
			//对特殊字段做处理
			if (in_array($rt['fieldname'],array('picture2','picture3','picture4','picture5'))) {
				unset($rt['name']);
			} elseif ($rt['fieldname'] == 'picture1' && $rt['fieldvalue']) {
				$rt['fieldvalue'] = array();
				for ($i=1; $i<=5; $i++) {
					$mergedField['picture'.$i] && $rt['fieldvalue']['picture'.$i] = $mergedField['picture'.$i];
				}
			}
			$rt['name'] && list($rt['name1'],$rt['name3'], $rt['name2']) = $this->getNamePartsByName($rt['name']);
			if ($rt['fieldid']) {
				$activityFieldDb[$rt['ifdel']][$rt['vieworder']][$rt['fieldid']] = $rt;
			}
		}
		
		$activityPostFieldsHtml .= $this->getAllSectionHtml($activityFieldDb);
		//活动描述的分区
		$activityPostFieldsHtml .= '<tr class="tr3">
			<td style="border-bottom:none"><table class="tgtable" width="100%" cellspacing="0" cellpadding="0" border="0">
					<tr>
						<th colspan="2">' . getLangInfo('other','act_activity_desc') . '</th>
					</tr>
			</table></td>
		</tr>';
		$activityPostFieldsHtml .= '</table>';
		return $activityPostFieldsHtml;
	}
	

	function getCheckPresetErrorMessage()
	{

		$return = "
			<script text=\"text/javascript\">

			function getErrorMessageByKey(key) {

				var errorMessage = {
		";

			
		$errorMessage = $this->fieldCheck->getErrorMessage();
		$messageJsArray = '';

		foreach ($errorMessage as $key => $message) {

			$messageJsArray .= '"'.addslashes($key).'" : "'.addslashes($message).'" , ';

		}
		$messageJsArray = rtrim($messageJsArray, ', ');

		$return .= $messageJsArray."};\n";

		$return .= "
					return errorMessage[key].replace('{value}', '');

				}

			</script>
			<script text=\"text/javascript\" src=\"js/pw_activitycheck.js\"></script>
		";

		return $return;

	}

	/**
	 * 活动多有区域字段的HTML
	 * @param array $activityFieldDb 字段的数据，形如array(bool 是否用户自定义字段  => array(int 行ID => array(int 字段ID => array 字段内容)))
	 * @param string $showtype 展示类型post:发帖页面 read:阅读页面
	 * @return string HTML
	 * @access public
	 */
	function getAllSectionHtml($activityFieldDb, $showtype = 'post') {
		$defaultSectionHtml = $userDefinedSectionHtml = '';
		foreach ($activityFieldDb as $ifdel => $defaultOrUserDefinedDb) {
			if ($showtype == 'read') {//帖子内容显示
				$sectionHtml = $this->getSectionvReadHtml($defaultOrUserDefinedDb);;
			} elseif ($showtype == 'post') {//发帖字段显示
				$sectionHtml = $this->getSectionHtml($defaultOrUserDefinedDb);
			} else {
				$sectionHtml = '';
			}
			if ($ifdel){ //用户定义的字段
				$userDefinedSectionHtml .= $sectionHtml;
			} else { //默认的字段
				$defaultSectionHtml .= $sectionHtml;
			}
		}
		return $defaultSectionHtml.$userDefinedSectionHtml;
	}
	/**
	 * 返回活动发帖时默认字段或用户定义字段区域的HTML
	 * @param array $data 字段的数据 形如array(int 行ID => array(int 字段ID => array 字段内容))
	 * @return string HTML
	 * @access public
	 */
	function getSectionHtml($data) {
		$sectionHtml = '';
		$lastSectionName = '';
		foreach ($data as $vieworder => $line) {
			$lineHtml = $this->getLineHtml($line, $vieworder);
			//获取行中第一个元素的sectionname
			$keys = array_keys($line);
			$thisSectionName = $line[$keys[0]]['sectionname'];
			$thisSectionName || $thisSectionName = getLangInfo('other','act_other');
			if ($lastSectionName != $thisSectionName) { //新建一个区块
				if ($sectionHtml) {
					$sectionHtml .= '</table></td></tr>';
				}
				$sectionHtml .= '<tr class="tr3">
					<td><table class="tgtable" width="100%" cellspacing="0" cellpadding="0" border="0">';
				if ($thisSectionName) {
					$sectionHtml .= '<tr><th colspan="2">' . $thisSectionName . '</th></tr>';
				}
				$sectionHtml .= $lineHtml;
				$lastSectionName = $thisSectionName;
			} else {
				$sectionHtml .= $lineHtml;
			}
		}
		$sectionHtml .= '</table></td></tr>';
		return $sectionHtml;
	}
	/**
	 * 返回活动发帖时每一行的字段HTML
	 * @param array $line 字段的数据，形如array(int 字段ID => array 字段内容)
	 * @param bool $severalInputsPerLine 相同排序值的字段是否显示在一行
	 * @return string HTML
	 * @access private
	 */
	function getLineHtml($line, $severalInputsPerLine) {
		if ($severalInputsPerLine) { //相同排序值的字段显示在一行
			$fieldHtml = '';
			$i = 0;
			foreach ($line as $fieldid => $fieldValue) {
				$ifmust = '';
				$fieldValue['ifmust'] && $ifmust = '<em>*</em> ';
				if ($i == 0) {
					if ($fieldValue['fieldname'] == 'picture1') {//图片特殊处理
						$fieldHtml .= '<tr><td class="tar black2" width="105" style="vertical-align:middle">' . $ifmust . $fieldValue['name1'].'：</td><td class="tal t_pic">';
					} else {
						$fieldHtml .= '<tr><td class="tar black2" width="105">' . $ifmust . $fieldValue['name1'].'：</td><td class="tal">';
					}
				} else {
					$fieldHtml .= $fieldValue['name1'].'&nbsp;';
				}

				$fieldHtml .= ($fieldValue['name3'] ? $fieldValue['name3'].' ' : '').$this->getActivityField($fieldValue).' '.$fieldValue['name2'];
				$i++;
			}
			$fieldHtml .= ' <span class="gray">' . $fieldValue['descrip'] . '</span></td></tr>';
		} else { //任何字段都显示在独立的行
			$fieldHtml = '';
			foreach ($line as $fieldid => $fieldValue) {
				$ifmust = '';
				$fieldValue['ifmust'] && $ifmust = '<em>*</em> ';
				$fieldHtml .= '<tr><td class="tar black2" width="105">' . $ifmust . $fieldValue['name1'].'：</td><td>';
				$fieldHtml .= ($fieldValue['name3'] ? $fieldValue['name3'].' ' : '').$this->getActivityField($fieldValue).' '.$fieldValue['name2'];
				$fieldHtml .= ' <span class="gray">' . $fieldValue['descrip'] . '</span></td></tr>';
			}
		}
		return $fieldHtml;
	}
	/**
	 * 获取添加元素链接的HTML
	 * @param string $basename 元素基本名
	 * @return string HTML
	 * @access private
	 */
	function getAddRowHtml ($basename) {
		$titleNameText = ($basename == 'fees') ? 'act_add_fee_type' : 'act_add_fee_detail';
		$titleName = getLangInfo('other',$titleNameText);
		return '<em title="'.$titleName.'" class="aa_add" onclick="addElement(\''.$basename.'\');">加一行</em>';
	}


	/**
	 * 获取删除元素链接的HTML
	 * @param int $additionalNoteNumber 额外添加的node数
	 * @return string HTML
	 * @access private
	 */
	function getRemoveRowHtml ($additionalNoteNumber = 0) {
		$deleteTypeText = getLangInfo('other','act_delete_type');
		$deleteLineText = getLangInfo('other', 'act_remove_line');
		$return = '<em title="' . $deleteTypeText . '" class="aa_remove" onclick="delParentElement(this);">' . $deleteLineText . '</em>';
		for ($i=0; $i<$additionalNoteNumber; $i++) {
			$return = '<span>'.$return.'</span>';
		}
		return $return;
	}
	/**
	 * 获取添加一行费用的HTML
	 * @param string $fieldname 字段名
	 * @param string $condition 添加的value
	 * @param string $money 经费的value
	 * @param bool $isDefaultValue $condition为默认值
	 * @param bool $isEditable 是否可编辑
	 * @param bool $ifMust 是否必须填写
	 * @return string HTML
	 * @access private
	 */
	function getFeesRowHtml($fieldname, $condition = '', $money = '', $isDefaultValue = 0, $isEditable = 1, $ifMust = 1) {
		$RMBText = getLangInfo('other','act_RMB');
		$perPeopleText = getLangInfo('other','act_per_people');
		if (!$isEditable) {
			return $money . $RMBText . '/'.$condition . ' <input class="input" type="hidden" name="act['.$fieldname.'][money][]" value="'. $money. '" />
			<input type="hidden" name="act[' . $fieldname . '][condition][]" value="' . $condition . '" />';
		}
		$ifMust = $ifMust ? 1 : 0;
		return '<input id="fees_default" class="input mr10" type="text" name="act[' . $fieldname . '][condition][]" value="' . $condition. '" size="5"
			 /></td><td>
			 ' . $perPeopleText . ' <input class="input" type="text" onblur="showError(this, getMoneyError(this, 1));" name="act['.$fieldname.'][money][]" value="'. $money. '" size="4" />
			 ' . $RMBText;
	}
	/**
	 * 获取添加一行费用明细的HTML
	 * @param string $fieldname
	 * @param string $item 项目
	 * @param string $money 金费，如10.50
	 * @param bool $isEditable 是否可编辑
	 * @param bool $ifMust 是否必须填写
	 * @return string HTML
	 * @access private
	 */
	function getFeesDetailRowHtml($fieldname, $item = '', $money = '', $isEditable = 1, $ifMust = 1) {
		if ($isEditable) {
			$ifMust = $ifMust ? 1 : 0;
			return '<input class="input mr10" type="text" name="act[' . $fieldname . '][item][]" value="' . $item. '" size="20" /></td>
					<td><input onblur="showError(this, getMoneyError(this, '.$ifMust.'));" class="input" type="text" name="act['.$fieldname.'][money][]" value="'. $money. '" size="4" /> ';
		} else {
			return $item . '</td><td>' . $money;
		}
	}
	/**
	 * 获取发帖时活动费用的HTML
	 * @param array $data
	 * @param $isEditable
	 * @return string HTML
	 * @access private
	 */
	function getFeesFieldHtml($data, $isEditable = true) {
		$value = $data['fieldvalue'];
		//$value此时的形式应该如$value = array(array('condition' => 'Male', 'money' => '1.00'), array('condition' => 'Female', 'money' => '2.00'));
		$value = unserialize($value);
		if (!$isEditable) {
			$return = '';
			$rulesHtml = '';
			if ($value) {
				foreach ($value as $rule) {
					$rulesHtml .= $this->getFeesRowHtml($data['fieldname'], $rule['condition'], $rule['money'], 0, $isEditable, 0).'，';
				}
				$rulesHtml = trim($rulesHtml, '，');
				$return .= $rulesHtml.'<br />';
			} else {
				$return .= getLangInfo('other','act_free');
			}
		} else {
			$return = '<table class="table_cost" cellspacing="0" cellpadding="0">';
			$return .= '<tbody id="fees_father">';
			if ($value) {
				$i = 0;
				foreach ($value as $rule) {
					$ifMust = 1 == $i && $data['ifmust'] ? 1 : 0;
					$return .= '<tr><td width="40">'.$this->getFeesRowHtml($data['fieldname'], $rule['condition'], $rule['money'], 0, $isEditable, $ifMust);
					if ($isEditable) {
						$return .= $i == 0 ? $this->getAddRowHtml('fees') : $this->getRemoveRowHtml(0);
					}
					$return .= '</td>';
					$return .= '</tr>';
					$i++;
				}
			} else {
				$ifMust = $data['ifmust'] ? 1 : 0;
				$allPeopleText = getLangInfo('other','act_all_people');
				$return .= '<tr><td width="40">' . $this->getFeesRowHtml($data['fieldname'], $allPeopleText, '' , 1, $isEditable, $ifMust) . ($isEditable ? $this->getAddRowHtml('fees') : '') . '</td></tr>';
			}
	
			$return .= '</tbody>';
			$return .= '<tbody id="fees_child" style="display: none"><tr><td>' . $this->getFeesRowHtml($data['fieldname'], '', '', 0, 1, 0) . $this->getRemoveRowHtml(0) . '</td></tr></tbody>';
			$return .= '</table>';
		}
		return $return;
	}
	/**
	 * 获取发帖时费用明细的HTML
	 * @param array $data
	 * @return string HTML
	 * @access private
	 */
	function getFeesDetailFieldHtml($data) {
		$return = '<table class="table_cost" cellspacing="0" cellpadding="0">';
		$return .= '<thead><tr><th width="160">' . getLangInfo('other','act_fee_desc') . '</th><th>' . getLangInfo('other','act_fee_unit') . '</th></tr></thead>';
		$return .= '<tbody id="feesdetail_father">';
		$value = unserialize($data['fieldvalue']);
		if ($value) {
			//$value此时的形式应该如$value = array(array('item' => 'traffic', 'money' => '1.00'), array('item' => 'food', 'money' => '2.00'));
			$i = 0;
			foreach ($value as $rule) {
				$ifMust = 1 == $i && $data['ifmust'] ? 1 : 0;
				$return .= '<tr><td>'.$this->getFeesDetailRowHtml($data['fieldname'], $rule['item'], $rule['money'], 1, $ifMust);
				$return .= $i == 0 ? $this->getAddRowHtml('feesdetail') : $this->getRemoveRowHtml();
				$return .= '</td>';
				$return .= '</tr>';
				$i++;
			}
		} else {
			$ifMust = $data['ifmust'] ? 1 : 0;
			$return .= '<tr><td>' . $this->getFeesDetailRowHtml($data['fieldname'], '', '', 1, $ifMust) . $this->getAddRowHtml('feesdetail') . '</td></tr>';
		}

		$return .= '</tbody>';
		$return .= '<tbody id="feesdetail_child" style="display: none"><tr><td>' . $this->getFeesDetailRowHtml($data['fieldname'], '', '', 1, 0) . $this->getRemoveRowHtml() . '</td></tr></tbody>';
		$return .= '</table>';
		return $return;
	}
	/**
	 * 获取发帖时calendar类型字段的HTML
	 * @param array $data
	 * @param $editable 值是否可编辑
	 * @return string HTML
	 * @global int 当前时间戳
	 */
	function getCalendarFieldHtml($data, $editable = true) {
		if (!$data['fieldvalue'] && $data['ifmust']) {
			 if ($data['fieldname'] == 'endtime' ||$data['fieldname'] == 'signupendtime') {
			 	$data['fieldvalue'] = $this->timestamp+2592000;
			 } else {	
			 	$data['fieldvalue'] = $this->timestamp;
			 }
			$className = 'gray';
			$isDefaultValue = true;
		} else {
			$className = '';
			$isDefaultValue = false;
		}
		$rules = $data['rules'];
		$data['fieldvalue'] = $data['fieldvalue'] ? $this->getTimeFromTimestamp($data['fieldvalue'], $rules['precision']) : '';
		if ('minute' == $rules['precision']) { //时间精确到分
			$showCalendarJsOption = 1;
		} else { //时间精确到日
			$showCalendarJsOption = 0;
		}
		$jsAdd = $isDefaultValue && $data['ifmust'] ? ' onblur="toggleCalendarInputValue(this, \''.$data['fieldvalue'].'\');"' : '';
		if ($editable) {
			if ($data['ifmust']) {
				$showError = "onblur=\"showError(this, getCalendarError(this, '".$data['fieldname']."'));\"";
			}
			return '<input id="calendar_'.$data['fieldname']."\" type=\"text\" class=\"input $className\" name=\"act[".$data['fieldname']."]\" 
				value=\"".$data['fieldvalue']."\" $showError onclick=\"ShowCalendar(this.id,$showCalendarJsOption);\" $jsAdd size=\"".$data['textwidth']."\" />";
		} else {
			return "<input id=\"calendar_".$data['fieldname']."\" type=\"hidden\" name=\"act[".$data['fieldname']."]\" value=\"".$data['fieldvalue']."\" />" . $data['fieldvalue'];
		}
	}
	function getMaxParticipantHtml($data,$isEditable = true) {
		if ($isEditable) {
			$L = array(
				'alreadySignup' => $this->getPeopleAlreadySignup(),
			);
			$this->getPeopleAlreadySignup() && $onfocus = 'onfocus="getObj(\'alert-alipay\').style.display=\'\';"';

			return '<span class="pr" style="padding-top:3px">
			<input id="maxparticipant" '. $onfocus . '
			 onblur="getObj(\'alert-alipay\').style.display=\'none\'; showError(this, getParticipantError(this, ' .$this->getPeopleAlreadySignup(). '));" class="input mr5" type="text" name="act[' . $data['fieldname'] . ']" value="' . $data['fieldvalue'] . '" size="' . $data['textwidth'] . '">
			<span id="alert-alipay" class="alert-alipay" style="display: none">' .($this->getPeopleAlreadySignup() ? getLangInfo('other','act_signup_info',$L) : '') . '<i></i></span>
			</span>';
		} else {
			return '<input id="maxparticipant" class="input mr5" type="hidden" name="act[' . $data['fieldname'] . ']" value="' . $data['fieldvalue'] . '" size="' . $data['textwidth'] . '">'.$data['fieldvalue'];
		}
		
	}
	function getMinParticipantHtml($data,$isEditable = true) {
		if ($isEditable) {
			return '<span class="pr"><input id="minparticipant" onblur="showError(this, getParticipantError(this, ' .$this->getPeopleAlreadyPaid(). '));" class="input mr5" type="text" name="act[' . $data['fieldname'] . ']" value="' . $data['fieldvalue'] . '" size="' . $data['textwidth'].'" /></span>';
		} else {
			return '<input id="minparticipant" type="hidden" name="act[' . $data['fieldname'] . ']" value="' . $data['fieldvalue'] . '" size="' . $data['textwidth'].'" />' . $data['fieldvalue'];
		}
	}
	function getTelephoneHtml ($data) {
		return '<input id="' . $data['fieldname'] . "\" onblur=\"showError(this, getTelephoneError(this))\" type=\"text\" class=\"input\" name=\"act[" . $data['fieldname'] . "]\" value=\"" . $data['fieldvalue'] . "\" size=\"" . $data['textwidth'] . "\"/>";
	}
	
	function getContactHtml ($data) {
		return '<input id="' . $data['fieldname'] . "\" onblur=\"showError(this, getContactError(this))\" type=\"text\" class=\"input\" name=\"act[" . $data['fieldname'] . "]\" value=\"" . $data['fieldvalue'] . "\" size=\"" . $data['textwidth'] . "\"/>";
	}

	/**
	 * 返回活动发帖时每一字段的HTML
	 * @param array $data
	 * @return string
	 */
	function getActivityField($data) {
		global $action,$tid,$imgpath,$attachpath,$mergedField;
		$actmid = $this->actmid;
		$acthtml = '';

		$textsize = $data['textwidth'] = $data['textwidth'] ? $data['textwidth'] : 20;
		$data['rules'] && $data['rules'] = unserialize($data['rules']);
		//默认检查
		if ($data['ifmust']) {
			$pccheck = 'check="/^.+$/"';
			$error = 'error=""';
		}
		if ($data['fieldname'] == 'fees') {
			$isEditable = true;
			$activityStatusKey = $this->getActivityStatusKey($mergedField, $this->timestamp, $this->peopleAlreadySignup($tid));//获取活动状态
			if ($action == 'modify' && ($this->getPeopleAlreadySignup() || $this->getPeopleAlreadySignup() && $activityStatusKey == 'signup_is_ended' || in_array($activityStatusKey,array('activity_is_ended','activity_is_running')))) {//已有人报名、活动已结束、活动进行中、报名结束
				$isEditable = false;
			}
			$acthtml .= $this->getFeesFieldHtml($data, $isEditable);
		} elseif ($data['fieldname'] == 'feesdetail') {
			$acthtml .= $this->getFeesDetailFieldHtml($data);
		} elseif ($data['fieldname'] == 'maxparticipant') {
			$isEditable = true;
			$activityStatusKey = $this->getActivityStatusKey($mergedField, $this->timestamp, $this->peopleAlreadySignup($tid));//获取活动状态
			if ($action == 'modify' && ($this->getPeopleAlreadySignup() && $activityStatusKey == 'signup_is_ended' || in_array($activityStatusKey,array('activity_is_ended','activity_is_running')))) {//活动已结束、活动进行中、报名结束
				$isEditable = false;
			}
			$acthtml .= $this->getMaxParticipantHtml($data,$isEditable);
		} elseif ($data['fieldname'] == 'minparticipant') {
			$isEditable = true;
			$activityStatusKey = $this->getActivityStatusKey($mergedField, $this->timestamp, $this->peopleAlreadySignup($tid));//获取活动状态
			if ($action == 'modify' && ($this->getpeopleAlreadySignup() && $activityStatusKey == 'signup_is_ended' || in_array($activityStatusKey,array('activity_is_ended','activity_is_running')))) {//已有人支付、活动已结束、活动进行中
				$isEditable = false;
			}
			$acthtml .= $this->getMinParticipantHtml($data,$isEditable);
		
		} elseif ($data['fieldname'] == 'contact') {
			$acthtml .= $this->getContactHtml($data);
		} elseif ($data['fieldname'] == 'telephone') {
			$acthtml .= $this->getTelephoneHtml($data);
		} elseif ($data['type'] == 'number') {
			if ($data['rules']['minnum'] && $data['rules']['maxnum']) {
				$pccheck = "check=\"{$data['rules']['minnum']}-{$data['rules']['maxnum']}\"";
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
			$acthtml .= "<input type=\"text\" $pccheck $error class=\"input\" name=\"act[" . $data['fieldname'] . "]\" value=\"" . $data['fieldvalue'] . "\" size=\"" . $data['textwidth'] . "\">";
			if ($data['rules']['minnum'] && $data['rules']['maxnum']) {
				$acthtml .= " <span class='gray'>(".getLanginfo('other','pc_defaultname')."{$data['rules']['minnum']} ~ {$data['rules']['maxnum']})</span>";
			}
		} elseif ($data['type'] == 'email') {
			$pccheck = 'check="/^[-a-zA-Z0-9_\.]+@([0-9A-Za-z][0-9A-Za-z-]+\.)+[A-Za-z]{2,5}$/"';
			if ($data['ifmust']) {
				$error = 'error="email_error"';
			} else {
				$error = 'error="email_error2"';
			}
			$acthtml .= "<input type=\"text\" $pccheck $error class=\"input\" name=\"act[" . $data['fieldname'] . "]\" value=\"" . $data['fieldvalue'] . "\" size=\"" . $data['textwidth'] . "\"/>";
		} elseif ($data['type'] == 'range') {
			$pccheck = 'check="/^\d+$/"';
			if ($data['ifmust']) {
				$error = 'error="number_error"';
			} else {
				$error = 'error="number_error2"';
			}
			$acthtml .= "<input type=\"text\" $pccheck $error class=\"input\" name=\"act[" . $data['fieldname'] . "]\" value=\"" . $data['fieldvalue'] . "\" size=\"". $data['textwidth'] . "\"/>";
		} elseif (in_array($data['type'],array('text','img','url'))) {
			$acthtml .= "<input id=\"" . $data['fieldname'] . "\" type=\"text\" $pccheck $error class=\"input\" name=\"act[" . $data['fieldname'] . "]\" value=\"" . $data['fieldvalue'] . "\" size=\"" . $data['textwidth'] . "\"/>";
		} elseif ($data['type'] == 'radio') {
			$activityStatusKey = $this->getActivityStatusKey($mergedField, $this->timestamp, $this->peopleAlreadySignup($tid));//获取活动状态

			$i = 0;
			if ($data['fieldname'] == 'genderlimit' && $action == 'new') {
				$onchange = "onclick=\"changeFeeValue(this.value);\"";
				$allPeopleText = getLangInfo('other','act_all_people');
				$maleText = getLangInfo('other','act_male');
				$femaleText = getLangInfo('other','act_female');
				$acthtml .= "
<script type=\"text/javascript\">
function changeFeeValue(value) {
	if (value == 1) {
		getObj('fees_default').value = $allPeopleText;
	} else if (value == 2) {
		getObj('fees_default').value = $maleText;
	} else if (value == 3) {
		getObj('fees_default').value = $femaleText;
	}
}
</script>";
			}
			if ($data['fieldname'] == 'paymethod') {//现金支付和支付宝换位
				krsort($data['rules']);
				reset($data['rules']);
			}
			foreach ($data['rules'] as $rk => $rv){
				$i++;
				$checked = '';
				$rv_value = substr($rv,0,strpos($rv,'='));
				$rv_name = substr($rv,strpos($rv,'=')+1);
				if ($data['fieldvalue']) {
					$rv_value == $data['fieldvalue'] && $checked = 'checked';
				} elseif (in_array($data['fieldname'],array('paymethod','userlimit','genderlimit'))) {
					if ($data['fieldname'] == 'paymethod') {//如果已经绑定并且实名认证通过
						$tradeinfo = $this->db->get_value("SELECT tradeinfo FROM pw_memberinfo WHERE uid=".S::sqlEscape($this->winduid));
						if ($tradeinfo) {
							$tradeinfo = unserialize($tradeinfo);
							$iscertified = $tradeinfo['iscertified'];
							$user_id = $tradeinfo['user_id'];
						}
						if ($user_id && $iscertified == 'T' && $rv_value == 1) {
							$checked = 'checked';
							$payDbTemp = array();
							$payDbTemp['alipay'] = 1;
						}
						if (!$payDbTemp['alipay'] && $rv_value == 2) {
							$checked = 'checked';
						}

					}
					if ($data['fieldname'] == 'userlimit' && $rv_value == 1) $checked = 'checked';
					if ($data['fieldname'] == 'genderlimit' && $rv_value == 1) $checked = 'checked';
				} elseif ($i == 1) {
					$checked = 'checked';
				}
				if ($data['fieldname'] == 'paymethod' && $rv_value == 1 && !$this->getPeopleAlreadyPaid()) {
					$onchange = "onclick=\"user_authentication(this.value);\"";
					$acthtml .= "
<script type=\"text/javascript\">
function user_authentication(paymethod) {
	if (paymethod == 1) {
		ajax.send('pw_ajax.php?action=activity&job=user_authentication','',function(){
			var rText = ajax.request.responseText.split('\t');
			if (rText[0] == 'iscertified_fail') {
				showDialog('warning','" . getLangInfo('other','act_unauth_alipay') . "');return false;
			} else if (rText[0] == 'isbinded_fail') {
				showDialog('warning','" . getLangInfo('other','act_unbind_alipay') . "');return false;
			} else {
				return true;
			}
		});
	} else {
		return false;
	}
}
</script>";
				}
				
				if ($data['fieldname'] == 'paymethod' && $action == 'modify' && ($this->getPeopleAlreadySignup() || in_array($activityStatusKey,array('activity_is_ended','activity_is_running')))) {//已有人支付、活动已结束、活动进行中
					if ($rv_value == $data['fieldvalue']) {
						$acthtml .= "<span class=\"fl w\"><input type=\"hidden\" value=\"" . $data['fieldvalue'] . "\" name=\"act[" . $data['fieldname'] . "]\" id=\"AlreadyPaid\" /> $rv_name </span>";
					} else {
						$acthtml .= '';
					}
				} else {
					$acthtml .= "<span class=\"fl w\"><input $onchange type=\"radio\" name=\"act[" . $data['fieldname'] . "]\" value=\"$rv_value\" $checked /> $rv_name </span>";
				}
			}
		} elseif ($data['type'] == 'checkbox') {
			foreach($data['rules'] as $ck => $cv){
				$checked = '';

				if ($data['ifmust']) {
					$pccheck = "check=\"1-\"";
				} else {
					$pccheck = "";
				}

				$cv_value = substr($cv,0,strpos($cv,'='));
				$cv_name = substr($cv,strpos($cv,'=')+1);
				if (strpos(",".$data['fieldvalue'].",",",".$cv_value.",") !== false) {
					$checked = 'checked';
				}
				$acthtml .= "<span class=\"fl w\"><input $pccheck type=\"checkbox\" name=\"act[" . $data['fieldname'] . "][]\" value=\"$cv_value\" $checked /> $cv_name </span>";
			}
		} elseif ($data['type'] == 'textarea') {
			$acthtml .= "<textarea type=\"text\" $pccheck name=\"act[" . $data['fieldname'] . "]\" rows=\"4\" class=\"input\" cols=\"" . $data['textwidth'] . "\"/>" . $data['fieldvalue'] . "</textarea>";
		} elseif ($data['type'] == 'select') {
			$acthtml .= "<select name=\"act[" . $data['fieldname'] . "]\">";
			foreach($data['rules'] as $sk => $sv){
				$selected = '';
				$sv_value = substr($sv,0,strpos($sv,'='));
				$sv_name = substr($sv,strpos($sv,'=')+1);
				$sv_value == $data['fieldvalue'] && $selected = 'selected';
				$acthtml .= "<option value=\"$sv_value\" $selected>$sv_name</option>";
			}
			$acthtml .= "</select>";
		} elseif ($data['type'] == 'calendar') {
			$activityStatusKey = $this->getActivityStatusKey($mergedField, $this->timestamp, $this->peopleAlreadySignup($tid));//获取活动状态
			if (!$data['ifdel'] && $action == 'modify' && ('activity_is_ended' == $activityStatusKey || 'signupstarttime' == $data['fieldname'] && $this->getPeopleAlreadySignup() || 'signup_is_ended' == $activityStatusKey && $this->getPeopleAlreadyPaid() && in_array($data['fieldname'],array('signupstarttime','signupendtime')) || 'activity_is_running' == $activityStatusKey && $data['fieldname'] != 'endtime')) {//活动已结束、已有人报名、报名结束、已有人支付、活动进行中
				$acthtml .= $this->getCalendarFieldHtml($data, 0);
			} else {
				$acthtml .= $this->getCalendarFieldHtml($data, 1);
			}
		} elseif ($data['type'] == 'upload') {
			$imgs = '';
			if ($data['fieldname'] == 'picture1') {
				$nonedisplay = '';
				if ($data['fieldvalue']) {
					$nonedisplay = 'style="display:none;"';
				}
				$imgs .= "<div class=\"t_img\" $nonedisplay id=\"img_picture_0\"><img src=\"$imgpath/activity/no_img.png\"/></div>";
				for ($i=1; $i<=5; $i++) {
					$picvalue = $data['fieldvalue']['picture'.$i];
					$valuedisplay = '';
					if (empty($picvalue)) {
						$valuedisplay = 'style="display:none;"';
					}
					$picpath = PW_PostActivity::getActivityImgUrl($picvalue,true);
					$imgs .= "<input id=\"fetch_{$i}\" type=\"hidden\" name=\"act[picture{$i}]\" value=\"$picvalue\"/><div $valuedisplay class=\"t_img\" id=\"img_picture_{$i}\"><img id=\"act_pic_" . $data['fieldid'] . "\" src=\"$picpath\"/><a href=\"javascript:;\" onclick=\"delpicture('{$i}','$actmid','$tid','" . $data['fieldid'] . "');return false;\">".getLangInfo('other','pc_delimg')."</a></div>";
				}

				$acthtml .= "$imgs <input type=\"button\" class=\"btn\" value=\"" . getLangInfo('other','act_upload_img') . "\" onclick=\"uploadpicture('$actmid')\"><p>" . $data['descrip'] . "</p>";
				$acthtml .= '
<script type="text/javascript">
function uploadpicture(actmid) {
	var total = 0;
	for (var i=1; i<=5; i++) {
		var element_name = "act[picture" + i + "]";
		var value = document.getElementsByName(element_name)[0].value;
		if (value != "") {
			continue;
		} else {
			total ++ ;
		}
	}
	if (total == 0) {
		showDialog("error","' . getLangInfo('other','act_upload_img_max') . '",2);return false;
	} else {
		try {ajax.send("pw_ajax.php?action=activity&job=upload&actmid="+actmid,"",ajax.get);} catch(e){}
	}
}
function delpicture(id,actmid,tid,fieldid){
	var total = 0;
	for (var i=1; i<=5; i++) {
		var element_name = "act[picture" + i + "]";
		var value = document.getElementsByName(element_name)[0].value;
		if (value != "") {
			continue;
		} else {
			total ++ ;
		}
	}
	if (total == 4) {
		getObj("img_picture_0").style.display = "";
	}
	fieldid = parseInt(fieldid);
	id = parseInt(id);
	fieldid = fieldid + id - 1;
	ajax.send("pw_ajax.php?action=activity&job=delimg","actmid="+actmid+"&fieldid="+fieldid+"&tid="+tid+"&attachment="+getObj("fetch_"+id).value,function(){
		var rText = ajax.request.responseText;
		if (rText == "success") {
			getObj("img_picture_" + id).style.display = "none";
			getObj("img_picture_" + id).children[0].src = "";
			document.getElementsByName("act[picture" + id + "]")[0].value = "";
		} else {
			showDialog("error","' . getLangInfo('other','act_delete_fail') . '",2);return false;
			return false;
		}
	});
}
</script>';
				
			} elseif (in_array($data['fieldname'],array('picture2','picture3','picture4','picture5'))) {
				$acthtml .= "";
			} else {
				if ($data['fieldvalue']) {
					$data['fieldvalue'] = PW_PostActivity::getActivityImgUrl($data['fieldvalue'],true);
					$imgs = "<span id=\"img_" . $data['fieldid'] . "\"><img src=\"{$data['fieldvalue']}\" width=\"195px\"/><a href=\"javascript:;\" onclick=\"delimg('$actmid','" . $data['fieldid'] . "');\">".getLangInfo('other','pc_delimg')."</a></span>";
					$imgs .= "
<script>
function delimg(actmid,fieldid) {
ajax.send('pw_ajax.php?action=activity&job=delimg','actmid='+actmid+'&fieldid='+fieldid+'&tid='+'$tid',function(){
    var rText = ajax.request.responseText;
    if (rText == 'success') {
        showDialog('success','" . getLangInfo('other','act_delete_success') . "',2); return false;
        getObj('img_'+fieldid).style.display = 'none';
    } else {
        showDialog('error','" . getLangInfo('other','act_delete_fail') . "',2);return false;
        return false;
    }
});
}
</script>";
				}
				$acthtml .= "<input type=\"file\" class=\"bt\" name=\"act_" . $data['fieldid'] . "\" size=\"" . $data['textwidth'] . "\">$imgs";
			}
		} else {
			$acthtml = "";
		}
		return $acthtml;
	}

	/** 
	 * 获取发帖活动子分类选择HTML
	 * @param int $actmid 模板（子分类）id
	 * @param int $fid 版块id
	 * @return string 活动分类选择菜单HTML
	 */
	function getActSelHtml($actmid,$fid) {
		$fid = (int)$fid;
		$actmid = (int)$actmid;
		$actmiddb = explode(",",$this->forum->foruminfo['actmids']);

		//$selectmodelhtml = '<div class="w_title_ip pr" style="margin-left: 5px;">';
		$selectmodelhtml = '<div style="margin-left: 5px;">';
		$selectmodelhtml .= "<select name=\"actmid\" onchange=\"window.onbeforeunload = function(){};window.location.href='post.php?fid='+'$fid'+'&actmid='+this.value\">";
		
		$newactcatedb = array();
		$activityModelDb = $this->getActivityModelDb();
		$activityCateDb = $this->getActivityCateDb();
		foreach ($actmiddb as $value) {
			$newactcatedb[$activityModelDb[$value]['actid']] = 1;
		}
		$options = array();

		foreach ($activityCateDb as $key => $value) {
			if ($value['ifable'] && $newactcatedb[$key]) {
				$label = $activityCateDb[$key]['name'];
				foreach ($actmiddb as $val) {
					if ($activityModelDb[$val]['actid'] == $key && $activityModelDb[$val]['ifable']) {
						$options[$label][$val] =  $activityModelDb[$val]['name'];
					}
				}
			}
		}
		$selectmodelhtml .= getSelectHtml($options, $actmid, '');
		$selectmodelhtml .= "</select></div>";
		return $selectmodelhtml;
	}

	/**
	 * 返回活动阅读页AA活动HTML/帖子内容显示
	 * @param int $actmid 活动模板id
	 * @param array $actdb 字段的值，形如array(bool [字段]  => 字段值,)
	 * @return string HTML
	 * @access private
	 */
	function getActValue($actmid ,$actdb = array()) {
		global $tid,$imgpath,$authorid,$subject,$paymethod;
		
		if (!isset($this->activitymodeldb[$actmid])) return;

		$activityReadFieldsHtml = "<link rel=\"stylesheet\" type=\"text/css\" href=\"$imgpath/activity/read.css\" />".'<div class="cc">';
		$FlashHtml = "<div id=\"pwSlidePlayer\" class=\"pwSlide fr\" style=\"margin-right:60px;\">";

		$activityValue = array();
		$defaultValueTableName = getActivityValueTableNameByActmid();
		if (isset($actdb) && count($actdb) > 0) {
			$tempdb = $this->db->get_one("SELECT iscertified,iscancel,paymethod,batch_no,fees,feesdetail FROM $defaultValueTableName WHERE tid=".S::sqlEscape($tid));
			$activityValue = $actdb;
			!$activityValue['fees'] && $activityValue['fees']		= $tempdb['fees'];
			!$activityValue['fees'] && $activityValue['feesdetail'] = $tempdb['feesdetail'];
			$activityValue['iscertified']	= $tempdb['iscertified'];
			$activityValue['batch_no']		= $tempdb['batch_no'];
			$activityValue['paymethod']		= $tempdb['paymethod'];
			$activityValue['iscancel']		= $tempdb['iscancel'];
		} else {
			$userDefinedValueTableName = getActivityValueTableNameByActmid($actmid, 1, 1);
			$activityValue = $this->db->get_one("SELECT iscertified,iscancel,actmid,out_biz_no,batch_no,recommend,starttime,endtime,location,contact,telephone,picture1,picture2,picture3,picture4,picture5,signupstarttime,signupendtime,minparticipant,maxparticipant,userlimit,specificuserlimit,genderlimit,fees,feesdetail,paymethod,pushtime,updatetime,ut.* FROM $defaultValueTableName dt LEFT JOIN $userDefinedValueTableName ut USING(tid) WHERE dt.tid=".S::sqlEscape($tid));
		}
		/*数据交互*/
		if ($this->timestamp - $activityValue['pushtime'] > 86400 && $activityValue['updatetime'] > $activityValue['pushtime']) {//每日更新一次\报名列表有所变动
			$this->pushActivityToAppCenter($tid,$actmid);
		}
		/*数据交互*/

		if (!unserialize($activityValue['feesdetail'])) unset($activityValue['feesdetail']);//释放费用明细变量
		$activityValue['authorid'] = $authorid;//作者赋值
		$paymethod = $activityValue['paymethod'];//支付方式赋值

		/*活动未认证，检查是否支付宝绑定+实名认证 否则创建AA活动号*/
		list($signupStaus,$isCertifiedHtml) = $this->isCertified($activityValue,$tid,$actmid);
		$activityReadFieldsHtml .= $isCertifiedHtml;
		/*活动未认证，检查是否支付宝绑定+实名认证 否则创建AA活动号*/

		$tmpCount = 0;
		$flash = false;
		$activityFieldDb = $signupDb = array();
		$query = $this->db->query("SELECT * FROM pw_activityfield WHERE actmid=".S::sqlEscape($actmid)." AND ifable=1 ORDER BY ifdel ASC, vieworder ASC, fieldid ASC");
		while ($rt = $this->db->fetch_array($query)) {
			$activityValue && $rt['fieldvalue'] = $activityValue[$rt['fieldname']];
			$rt['name'] && list($rt['name1'],$rt['name3'], $rt['name2']) = $this->getNamePartsByName($rt['name']);
			if (($rt['type'] == 'img' || $rt['type'] == 'upload') && $rt['fieldvalue']) {
				$tmpCount ++;
				$rt['type'] == 'upload' && $rt['fieldvalue'] = PW_PostActivity::getActivityImgUrl($rt['fieldvalue'],true);
				$FlashHtml .= "<div id=\"Switch_" . $rt['fieldname'] . "\" style=\"display:none;display: block;\"><img src=\"{$rt['fieldvalue']}\" width=\"240px\"/></div>";
				$flash = true;
			}
			if ($rt['type'] == 'textarea') {
				$rt['fieldvalue'] = nl2br($rt['fieldvalue']);
			}
			if ($rt['fieldid']) {
				$activityFieldDb[$rt['ifdel']][$rt['vieworder']][$rt['fieldid']] = $rt;
			}
		}

		//显示活动类型
		$activityReadFieldsHtml .= '<ul class="aa_infos"><li><em>' . getLangInfo('other','act_activity_type') . '</em>'.$this->activitymodeldb[$this->actmid]['name'];

		if ($signupStaus == true && $this->winduid) {
			$activityReadFieldsHtml .= " &nbsp;<a href=\"read.php?tid=$tid#memberlist_show\">(" . getLangInfo('other','act_signuper_info') . ")</a></li>";
		} else {
			$activityReadFieldsHtml .= '</li>';
		}
		
		$activityReadFieldsHtml .= $this->getAllSectionHtml($activityFieldDb,'read').'<ul/>';//字段解析
		
		$this->isCancelled($activityValue,$tid,$actmid);//判断是否活动被取消

		if ($signupStaus == true) {
			$signupHtml = $this->getSignupHtml($activityValue);//获取报名状态
		}

		$FlashHtml .= "<div class=\"pwSlide-bg\"></div><ul id=\"SwitchNav\"></ul></div><script type=\"text/javascript\" src=\"js/sliderplayer.js\"></script><script>pwSliderPlayers('pwSlidePlayer');</script>";
		$flash == false && $FlashHtml = '';
		$activityReadFieldsHtml = $FlashHtml.$activityReadFieldsHtml.$signupHtml.'</div>';

		return array($activityReadFieldsHtml,$activityValue);
	}
	
	/**
	 * 返回活动是否认证
	 * @param array $data 字段的数据 形如array(int 行ID => array(int 字段ID => array 字段内容))
	 * @param int $tid 帖子id
	 * @param int $actmid 分类id
	 * @return string HTML
	 * @access private
	 */
	function isCertified($data,$tid,$actmid) {
		global $subject,$tdtime;
		$isCertifiedHtml = '';
		$signupStaus = true;
		$defaultValueTableName = getActivityValueTableNameByActmid();
		if (!$data['iscertified'] && $data['paymethod'] == 1) {
			$tradeinfo	= $this->db->get_value("SELECT tradeinfo FROM pw_memberinfo WHERE uid=".S::sqlEscape($data['authorid']));
			$tradeinfo	= unserialize($tradeinfo);
			$alipay		= $tradeinfo['alipay'];
			$isBinded	= $tradeinfo['isbinded'];
			$isCertified= $tradeinfo['iscertified'];

			if ($isBinded != 'T') {//尚未绑定
				if ($data['authorid'] == $this->winduid) {
					$isCertifiedHtml .= '<p class="aa_err">' . getLangInfo('other','act_err_unbind_alipay') . '</p>';
				} else {
					$isCertifiedHtml .= '<p class="aa_err">' . getLangInfo('other','act_err_author_unbind') . '</p>';
				}
			} elseif ($isCertified != 'T') {//尚未实名认证
				require_once(R_P . 'lib/activity/alipay_push.php');
				$alipayPush = new AlipayPush();
				$is_success = $alipayPush->user_query($data['authorid']);//查询是否实名认证

				if ($is_success != 'T') {
					if ($data['authorid'] == $this->winduid) {
						$isCertifiedHtml .= '<p class="aa_err">' . getLangInfo('other','act_err_unauth_alipay') . '</p>';
					} else {
						$isCertifiedHtml .= '<p class="aa_err">' . getLangInfo('other','act_err_author_unpass') . '</p>';
					}
				} else {
					$isCertifiedHtml .= '<p class="aa_err">' . getLangInfo('other','act_err_activity_unNo') . '</p>';
					$this->db->update("UPDATE $defaultValueTableName SET iscertified=1 WHERE tid=".S::sqlEscape($tid));//活动通过认证
				}
			} else {
				$isCertifiedHtml .= '<p class="aa_err">' . getLangInfo('other','act_err_activity_unNo') . '</p>';
				$this->db->update("UPDATE $defaultValueTableName SET iscertified=1 WHERE tid=".S::sqlEscape($tid));//活动通过认证
			}
			$signupStaus = false;

		} elseif ($data['iscertified'] && !$data['batch_no'] && $data['paymethod'] == 1) {
			require_once(R_P . 'lib/activity/alipay_push.php');
			$alipayPush = new AlipayPush();
			$certStatus = $alipayPush->create_aa_payment($tid,$data['authorid'],$actmid,$subject);//创建AA活动号

			if ($certStatus != 'T') {
				if ($certStatus == 'AA_FAIL_TO_CREATE_AA_NOT_T') {
					if ($data['authorid'] == $this->winduid) {
						$isCertifiedHtml .= '<p class="aa_err">' . getLangInfo('other','act_err_alipayinfo_un') . '</p>';
					} else {
						$isCertifiedHtml .= '<p class="aa_err">' . getLangInfo('other','act_err_alipayAccount') . '</p>';
					}
				} elseif ($certStatus == 'AA_FAIL_TO_CREATE_AA_FREEZED') {
					if ($data['authorid'] == $this->winduid) {
						$isCertifiedHtml .= '<p class="aa_err">' . getLangInfo('other','act_err_alipay_freeze') . '</p>';
					} else {
						$isCertifiedHtml .= '<p class="aa_err">' . getLangInfo('other','act_err_author_freeze') . '</p>';
					}
				} elseif ($certStatus == 'AA_FAIL_TO_CREATE_AA_NEED_CERTIFY') {
					if ($data['authorid'] == $this->winduid) {
						$isCertifiedHtml .= '<p class="aa_err">' . getLangInfo('other','act_err_unauth_alipay') . '</p>';
					} else {
						$isCertifiedHtml .= '<p class="aa_err">' . getLangInfo('other','act_err_alipayAccount') . '</p>';
					}
				} elseif ($certStatus == 'AA_FAIL_TO_CREATE_AA_BEYOND_LIMIT' && $data['signupstarttime'] > $tdtime && $data['signupstarttime'] < $tdtime + 86400) {//报名时间不是今天
					if ($data['authorid'] == $this->winduid) {
						$isCertifiedHtml .= '<p class="aa_err">' . getLangInfo('other','act_err_max_activity') . '</p>';
					} else {
						$isCertifiedHtml .= '<p class="aa_err">' . getLangInfo('other','act_err_max_activity2') . '</p>';
					}
				} else {
					if ($data['authorid'] == $this->winduid) {
						$isCertifiedHtml .= '<p class="aa_err">' . getLangInfo('other','act_err_fail_No') . '</p>';
					} else {
						$isCertifiedHtml .= '<p class="aa_err">' . getLangInfo('other', 'act_err_system_error') . '</p>';
					}
				}
				$signupStaus = false;
			}
		}
		return array($signupStaus,$isCertifiedHtml);
	}

	/**
	 * 返回活动是否被取消
	 * @param array $data 字段的数据 形如array(int 行ID => array(int 字段ID => array 字段内容))
	 * @param int $tid 帖子id
	 * @param int $actmid 分类id
	 * @return string HTML
	 * @access private
	 */
	function isCancelled($data,$tid,$actmid) {
		global $subject;
		//延迟10分钟取消活动
		if ($data['signupendtime'] < $this->timestamp - 600 && $data['iscancel'] == 0 && $this->peopleAlreadySignup($tid) < $data['minparticipant'] && $data['minparticipant']) {
			$defaultValueTableName = getActivityValueTableNameByActmid();

			/*活动取消*/
			$this->UpdatePayLog($tid,0,3);
			/*活动取消*/

			$this->db->update("UPDATE $defaultValueTableName SET iscancel=1 WHERE tid=".S::sqlEscape($tid));
			/*短消息通知 取消活动 发起人*/		
			M::sendNotice(
				array($data['author']),
				array(
					'title' => getLangInfo('writemsg', 'activity_cancel_title', array(
							'tid' => $tid,
							'subject'  => $subject
						)
					),
					'content' => getLangInfo('writemsg', 'activity_cancel_content', array(
							'tid'      => $tid,
							'subject'  => $subject
						)
					)
				),'notice_active', 'notice_active'
			);
			
			/*短消息通知 取消活动 参与人*/
			$query = $this->db->query("SELECT uid,username FROM pw_activitymembers WHERE tid=".S::sqlEscape($tid));
			while ($rt = $this->db->fetch_array($query)) {
				M::sendNotice(
					array($rt['username']),
					array(
						'title' => getLangInfo('writemsg', 'activity_cancel_signuper_title', array(
								'tid' => $tid,
								'subject'  => $subject
							)
						),
						'content' => getLangInfo('writemsg', 'activity_cancel_signuper_content', array(
								'tid'      => $tid,
								'subject'  => $subject
							)
						)
					),'notice_active', 'notice_active'
				);
			}
		}
	}

	/**
	 * 返回活动阅读页时默认字段或用户定义字段区域的HTML
	 * @param array $data 字段的数据 形如array(int 行ID => array(int 字段ID => array 字段内容))
	 * @return string HTML
	 * @access private
	 */
	function getSectionvReadHtml($data) {
		$sectionValueHtml = '';
		foreach ($data as $vieworder => $line) {
			$lineHtml = $this->getReadLineHtml($line,$vieworder);
			$sectionValueHtml .= $lineHtml;
		}
		return $sectionValueHtml;
	}

	/**
	 * 返回帖子报名状态（9种状态）
	 * @param array $data 字段的数据 形如array(string 字段名 => string 字段值))
	 * @return string HTML
	 * @access private
	 */
	function getSignupHtml($data) {
		global $tid;
		$activityStatusKey = $this->getActivityStatusKey($data, $this->timestamp, $this->peopleAlreadySignup($tid));
		$replaceArray = array();
		if ('activity_is_ended' == $activityStatusKey) {/*活动结束*/
			$this->UpdatePayLog($tid,0,2);
		} elseif ('activity_is_cancelled' == $activityStatusKey) {
			$replaceArray = array($data['minparticipant']);
		} elseif ('signup_is_available' == $activityStatusKey) {
			$replaceArray = array($tid, $data['authorid'], $this->actmid);
			if ($this->getOrderMemberUid($tid) == $this->winduid && $this->winduid) {
				$activityStatusKey = 'additional_signup_is_available_for_member';
			} elseif ($this->winduid) {
				$activityStatusKey = 'signup_is_available_for_member';
			} else {
				$activityStatusKey = 'signup_is_available_for_guest';
			}
		}
		$signupHtml = '<p class="t3">';
		$signupHtml .= $this->getSignupHtmlByActivityKey($activityStatusKey, $replaceArray);
		$signupHtml .= '</p>';
		return $signupHtml;
	}
	
	/**
	 * 根据活动状态Key获取报名按钮的HTML
	 * @param string $key 活动状态key
	 * @param array $replaceArray 补充的变量
	 * @return string HTML
	 * @global string 图片base URL
	 */
	function getSignupHtmlByActivityKey ($key, $replaceArray = NULL) {
		global $imgpath;
		switch ($key) {
			case 'signup_not_started_yet': //未开始报名
				$html = '<span class="bt"><span><button type="button" onfocus="blur()">' . getLangInfo('other','act_signup_not_start') . '</button></span></span>';
				break;
			case 'activity_is_cancelled': //报名结束但未达到最低人数限制，活动取消
				$html = "<p class=\"aa_tip mt10\"><img src=\"$imgpath/activity/alert.png\" align=\"absmiddle\" /> <strong>" . getLangInfo('other','act_signup_not_enough',$replaceArray[0]) . "</strong></p>";
				break;
			case 'activity_is_running':
			case 'signup_is_ended': //正常情况下报名结束
				$html = '<span class="bt"><span><button type="button" onfocus="blur()">' . getLangInfo('other','act_signup_over') . '</button></span></span>';
				break;
			case 'activity_is_ended': //活动结束
				$html = '<span class="bt"><span><button type="button" onfocus="blur()">' . getLangInfo('other','act_activity_over') . '</button></span></span>';
				break;
			case 'signup_number_limit_is_reached': //报名人数已满
				$html = '<span class="bt"><span><button type="button" onfocus="blur()">' . getLangInfo('other','act_signup_overflow') . '</button></span></span>';
				break;
			case 'signup_is_available':
			case 'signup_is_available_for_guest': //未登录状态报名提示
				$html = "<span class=\"btn\"><span><button type=\"button\" id=\"signup\" onclick=\"sendmsg('job.php?action=pcjoin&tid=$replaceArray[0]','',this.id);\">" . getLangInfo('other','act_I_signup') . "</button></a></span></span>";
				break;
			case 'signup_is_available_for_member': //登录状态，先前未报名，报名提示
				$text = getLangInfo('other','act_I_signup');
			case 'additional_signup_is_available_for_member': //登录状态，先前已报名，报名提示
				$text || $text = getLangInfo('other','act_additional_signup');
				$html = "<span class=\"btn\"><span><button type=\"button\" id=\"signup\" onclick=\"sendmsg('pw_ajax.php?action=activity&job=signup&tid=$replaceArray[0]&authorid=$replaceArray[1]&actmid=$replaceArray[2]','',this.id);\">$text</button></span></span>";
				break;
			default:
				$html = '';
		}
		return $html;
	}

	
	/**
	 * 返回活动阅读页时每一行的字段值显示HTML
	 * @param array $line 字段的数据，形如array(int 字段ID => array 字段内容)
	 * @param bool $severalInputsPerLine 相同排序值的字段是否显示在一行
	 * @return string HTML
	 * @access private
	 */
	function getReadLineHtml($line, $severalInputsPerLine) {
		global $paymethod;
		if ($severalInputsPerLine) { //相同排序值的字段显示在一行
			$fieldHtml = '';
			$i = 0;
			$tempParticipant = array();//当minparticipant值为空时，临时存储的值
			foreach ($line as $fieldid => $fieldValue) {
				if ($fieldValue['fieldname'] == 'minparticipant' && !$fieldValue['fieldvalue']) {//如果不填写最少人数，则默认最少人数不限制
					$tempParticipant['min'] = '1';
					$fieldValue['fieldvalue'] = '-1';//赋值为-1
				} elseif ($fieldValue['fieldname'] == 'maxparticipant' && !$fieldValue['fieldvalue']) {
					if (isset($tempParticipant['min']) && $tempParticipant['min']) {//当最小值为空，最大值也为空时的处理
						$fieldValue['fieldvalue'] = '-1';
					}
				}
				if ($fieldValue['fieldvalue'] && $fieldValue['type'] != 'img' && $fieldValue['type'] != 'upload'){
					if ($i == 0) {
						$fieldHtml .= '<li><em>' .$fieldValue['name1'].'：</em>';
					} else {
						if ($fieldValue['fieldname'] == 'specificuserlimit') {//报名限制特殊处理
							$fieldHtml .= '，';
						} elseif ($fieldValue['fieldname'] == 'maxparticipant') {//人数限制特殊处理
							if (isset($tempParticipant['min']) && $tempParticipant['min']) {//当最小值为空时，不加，
								$addJoin = '';
							} else {
								$addJoin = '，';
							}
							if ($fieldValue['fieldvalue'] == '-1') {//最大值为空时的处理
								$fieldHtml .= '';
								$fieldValue['fieldvalue'] = '人数不限';
							} else {
								$fieldHtml .= $addJoin.$fieldValue['name1'].'&nbsp;';
							}
						} elseif ($fieldValue['fieldname'] == 'feesdetail') {//费用明细特殊处理
							$fieldHtml .= '</li><li><em>'.$fieldValue['name1'].'：</em>';
						} else {
							$fieldHtml .= $fieldValue['name1'].'&nbsp;';
						}
					}
					if ($fieldValue['fieldname'] == 'minparticipant' && $fieldValue['fieldvalue'] == '-1') {
						$fieldValue['name3'] = '';
						$fieldValue['fieldvalue'] = '';
					}
					$fieldHtml .= $fieldValue['name3'] ? $fieldValue['name3'].' ' : '';

					$tempFieldvalue = $this->getFieldValueHTML($fieldValue['type'],$fieldValue['fieldvalue'],$fieldValue['rules']);

					if ($fieldValue['fieldname'] == 'fees') {//活动费用特殊处理
						$fees = '';
						$tempFieldvalue = unserialize($tempFieldvalue);
						if ($tempFieldvalue) {
							foreach ($tempFieldvalue as $value) {
								$fees .= ($fees ? '，' : '') .$this->getFeesRowHtml('', $value['condition'], $value['money'], 1, 0);
							}
						} else {
							$fees .= getLangInfo('other','act_free');
						}
						
						$fieldHtml .= $fees;
					} elseif ($fieldValue['fieldname'] == 'feesdetail') {//费用明细特殊处理
						$feesdetail = '<table class="aa_table_cost" cellpadding="0" cellspacing="0">
											<thead>
												<tr>
													<th width="150">' . getLangInfo('other','act_fee_desc') . '</th>
													<th class="tac">' . getLangInfo('other','act_fee_unit') . '</th>
												</tr>
											</thead><tbody>';
						$tempFieldvalue = unserialize($tempFieldvalue);
						foreach ($tempFieldvalue as $value) {
							$feesdetail .= '<tr><td>'.$value['item'].'</td><td style="border-right:none;" class="tac">'.$value['money'].'</td></tr>';
						}
						$feesdetail .= '</tbody></table>';
						$fieldHtml .= $feesdetail;
					} else {
						$fieldHtml .= $tempFieldvalue;
					}

					if (in_array($fieldValue['fieldname'],array('minparticipant','maxparticipant'))) {//人数限制特殊处理
						if ($fieldValue['fieldname'] == 'minparticipant' && !$fieldValue['fieldvalue'] || $fieldValue['fieldname'] == 'maxparticipant' && !is_numeric($fieldValue['fieldvalue'])) {//如果不填写最少人数，则默认最少人数不限制 或者 最大值为空时的处理
							$fieldHtml .= ' '.$fieldValue['name2'];
						} else {
							$fieldHtml .= '人 '.$fieldValue['name2'];
						}
					} else {
						$fieldHtml .= ' '.$fieldValue['name2'];
					}
					$i++;
				}
			}
			$fieldHtml .= ' </li>';

		} else { //任何字段都显示在独立的行
			$fieldHtml = '';
			foreach ($line as $fieldid => $fieldValue) {
				
				if ($fieldValue['fieldvalue'] && $fieldValue['type'] != 'img' && $fieldValue['type'] != 'upload'){
					$fieldHtml .= '<li><em>' . $fieldValue['name1'].'：</em>';
					$fieldHtml .= ($fieldValue['name3'] ? $fieldValue['name3'].' ' : '').$this->getFieldValueHTML($fieldValue['type'],$fieldValue['fieldvalue'],$fieldValue['rules']).' '.$fieldValue['name2'].' </li>';
				}
			}
		}
		return $fieldHtml;
	}

	/**
	 * 返回活动阅读页时每个字段类型的展示
	 * @param string $type 字段类型
	 * @param string $fieldvalue 字段值
	 * @param bool $rules 字段规则
	 * @return string 值
	 * @access private
	 */
	function getFieldValueHTML($type,$fieldvalue,$rules){
		$rules && $rules = unserialize($rules);
		if ($type == 'radio') {
			$newradio = array();
			foreach ($rules as $rk => $rv){
				$rv_value = substr($rv,0,strpos($rv,'='));
				$rv_name = substr($rv,strpos($rv,'=')+1);
				$newradio[$rv_value] = $rv_name;
			}
			$actvalue .= "{$newradio[$fieldvalue]}";

		} elseif ($type == 'checkbox') {
			$newcheckbox = array();
			foreach ($rules as $ck => $cv){
				$cv_value = substr($cv,0,strpos($cv,'='));
				$cv_name = substr($cv,strpos($cv,'=')+1);
				$newcheckbox[$cv_value] = $cv_name;
			}
			$actvalues = '';
			foreach (explode(",",$fieldvalue) as $value) {
				if ($value) {
					$actvalues .= $actvalues ? ",".$newcheckbox[$value] : $newcheckbox[$value];
				}
			}
			$actvalue .= $actvalues;

		} elseif ($type == 'select') {
			$newselect = array();
			foreach($rules as $sk => $sv){
				$sv_value = substr($sv,0,strpos($sv,'='));
				$sv_name = substr($sv,strpos($sv,'=')+1);
				$newselect[$sv_value] = $sv_name;
			}
			$actvalue .= "{$newselect[$fieldvalue]}";
		} elseif ($type == 'url') {
			$actvalue .= "<a href=\"$fieldvalue\" target=\"_blank\">$fieldvalue</a>";
		} elseif ($type == 'calendar') {
			$actvalue .= $this->getTimeFromTimestamp($fieldvalue, $rules['precision']);
		} else {
			$actvalue .= "$fieldvalue";
		}
		return $actvalue;
	}

	/**
	 * 返回帖子报名信息列表
	 * @param int $actmid 二级分类id
	 * @param int $tid 帖子id
	 * @param int $fid 版块id
	 * @param int $paymethod 支付方式
	 * @param int $authorid 作者id
	 * @return string html
	 * @access private
	 */
	function getOrderMemberList($actmid,$tid,$fid,$paymethod,$authorid) {
		global $imgpath;
		$orderMemberList = "<div id=\"memberlist_show\"><div style=\"padding:13px 30px;\"><img src=\"$imgpath/loading.gif\" align=\"absbottom\" /> " . getLangInfo('other','act_loading_data') . "</div></div>
<script>
var authorid = parseInt('$authorid');
var paymethod = parseInt('$paymethod');

function ajaxview(url) {
	try {
		ajaxget(url,'memberlist_show');
		return false;
	} catch(e){}
}

function ajaxget(url,tag) {
	try {
		ajax.send(url,'',function() {
			if (ajax.request.responseText.indexOf('<') != -1) {
				getObj(tag).innerHTML = ajax.request.responseText;
			}
		});
		return false;
	} catch(e){}
}

window.onReady(function () {
	setTimeout(function(){ajaxget('pw_ajax.php?action=activity&job=memberlist&actmid=$actmid&tid=$tid&fid=$fid&authorid='+authorid+'&paymethod='+paymethod,'memberlist_show')},200);
});

function signupClose(actuid) {//关闭报名信息
	
	var rText = ajax.request.responseText;
	if (rText == 'success') {		
		ajaxget('pw_ajax.php?action=activity&job=memberlist&actmid=$actmid&tid=$tid&authorid='+authorid+'&paymethod='+paymethod,'memberlist_show');
		showDialog('success','" . getLangInfo('other','act_fee_close_success') . "',2);return false;
	} else if (rText == 'payed') {
		showDialog('error','" . getLangInfo('other','act_fee_close_fail') . "',2);return false;
	} else if (rText == 'fail') {
		showDialog('error','" . getLangInfo('other','act_fee_close_fail2') . "',2);return false;
	} else {
		showDialog('error','" . getLangInfo('other','act_fee_op_error') . "',2);return false;
	}
}

function signupModify() {//修改报名信息
	var rText = ajax.request.responseText;
	if (rText == 'success') {
		ajaxget('pw_ajax.php?action=activity&job=memberlist&actmid=$actmid&tid=$tid&authorid='+authorid+'&paymethod='+paymethod,'memberlist_show');
		showDialog('success','" . getLangInfo('other','act_op_success') . "',2);return false;
	} else if (rText == 'act_signupnums_error') {
		showDialog('error','" . getLangInfo('other','act_signup_alert') . "',2);return false;
	} else if (rText == 'act_signupnums_error_max') {
		showDialog('error','" . getLangInfo('other','act_signup_alert2') . "',2);return false;
	} else if (rText == 'act_mobile_nickname_error') {
		showDialog('error','" . getLangInfo('other','act_signup_alert3') . "',2);return false;
	} else if (rText == 'act_num_overflow') {
		showDialog('error','" . getLangInfo('other','act_signup_alert4') . "',2);return false;
	} else {
		showDialog('error','" . getLangInfo('other','act_fee_op_error') . "',2);return false;
	}
}

function loadMemberList() {//加载报名列表
	ajaxget('pw_ajax.php?action=activity&job=memberlist&actmid=$actmid&tid=$tid&authorid='+authorid+'&paymethod='+paymethod,'memberlist_show');
	closep();
}

function additional() {//追加费用
	var rText = ajax.request.responseText;
	if (rText == 'success') {
		ajaxget('pw_ajax.php?action=activity&job=memberlist&actmid=$actmid&tid=$tid&authorid='+authorid+'&paymethod='+paymethod,'memberlist_show');
		showDialog('success','" . getLangInfo('other','act_add_fee_success') . "',2);return false;
	} else if (rText == 'totalcost_error') {
		showDialog('error','追加的费用格式非法',2);return false;
	} else {
		showDialog('error','非法操作',2);return false;
	}
}
</script>";
		return $orderMemberList;
	}

	/**
	 * 返回推荐
	 * @param int $actmid 子分类id
	 * @param int $tid 帖子id
	 * @param int $isRecommend 是否推荐
	 * @return string 字段名第一部分
	 */
	function getActRecommendHtml($actmid,$tid,$isRecommend = 0) {
		global $groupid;
		$isRecommendOrNot = !$isRecommend ? getLangInfo('other','act_recommend') : getLangInfo('other','act_cancel_recommend');
		$actRecommendHtml = "<a id=\"actrecommend\" onclick=\"actRecommend('$tid', '$actmid', this);\" href=\"javascript:;\" title=\"$isRecommendOrNot\">$isRecommendOrNot</a>
<script>
function actRecommend(tid, actmid, obj) {
	ajax.send(\"pw_ajax.php?action=activity&job=recommend&tid=\"+tid+\"&actmid=\"+actmid,\"\",function(){
		var rText = ajax.request.responseText.split('\t');
		if (rText[0] == 'success') {
			var isRecommendOrNot;
			if (rText[1] != 1) {
				isRecommendOrNot = '" . getLangInfo('other','act_recommend') . "';
			} else {
				isRecommendOrNot = '" . getLangInfo('other','act_cancel_recommend') . "';
			}
			obj.title = isRecommendOrNot;
			obj.innerHTML = isRecommendOrNot;
			showDialog('success','" . getLangInfo('other','act_op_success') . "',2);return false;

		} else if (rText[0] == 'noright') {
			showDialog('error','" . getLangInfo('other','act_not_right_recommend') . "',2);return false;
			return false;
		} else {
			showDialog('error','" . getLangInfo('other','act_op_fail') . "',2);return false;
			return false;
		}
	});
}
</script>";
		if ($groupid == 3) {
			return $actRecommendHtml;
		} else {
			return '';
		}
	}

	/**
	 * 返回报名列表管理/浏览权限
	 * @param int $authorid 发起人id
	 * @return bool
	 * @access private
	 */
	function getAdminRight($authorid) {
		global $groupid,$manager,$foruminfo,$windid;
		$isGM = S::inArray($windid,$manager);//是否是创始人
		$isBM = admincheck($foruminfo['forumadmin'],$foruminfo['fupadmin'],$windid);//是否有管理权限

		if (!$isGM) {#非创始人权限获取
			$pwSystem = pwRights($isBM);
			if ($pwSystem && $pwSystem['activitylist']) {
				$isBM = 1;
			} else {
				$isBM = 0;
			}
		}
		if ($groupid == 3 || $isGM || $isBM || $authorid == $this->winduid) {
			return true;
		}
		return false;
	}

	/**
	 * 返回活动帖预览表单
	 * @param int $actmid 二级分类id
	 * @param int $tid 帖子id
	 * @return string html
	 * @access private
	 */
	function getPreviewForm($actmid,$tid) {
		global $authorid;
		$previewForm = "
		<form name=\"preview\" action=\"job.php?action=activity&job=preview\" method=\"post\" target=\"preview_page\">
		<input type=\"hidden\" name=\"actmid\" value=\"$actmid\" />
		<input type=\"hidden\" name=\"tid\" value=\"$tid\" />
		<input type=\"hidden\" name=\"authorid\" value=\"$authorid\" />";
		return $previewForm;
	}

	/**
	 * 返回预览信息解析数组
	 * @param array $actinfo 活动变量值的字符串
	 * @return array 数组
	 * @access private
	 */
	function getPreviewdata ($actinfo) {
		$fielddb = $this->getFieldData($this->actmid,false);
		$actdb = $feesdb = $feesdetaildb = array();
		foreach(explode("{|}",$actinfo) as $val) {
			if (strpos($val,'act[') !== false) {
				$name = $value = $type = $fieldid = '';
				$fieldb = array();
				$val = str_replace('&#41;',')',$val);
				list($name,$value,$type) = explode("(|)",$val);
				preg_match("/act\[(.+?)\]/",$name,$fieldata);
				if (strpos($val,'act[fees][') !== false) {//活动费用处理
					$feesdb[] = $val;
				} elseif (strpos($val,'act[feesdetail][') !== false) {//费用明细处理
					$feesdetaildb[] = $val;
				} else {$fieldname = $fieldata['1'];
					if ($fieldname) {
						if ($fielddb[$fieldname] != $newid) {
							strpos($type,'calendar_') !== false && $value = PwStrtoTime($value);
							$actdb[$fieldname] = $value;
						} elseif ($fielddb[$fieldname] == $newid) {
							$actdb[$fieldname] .= ','.$value;
						}
						$newid = $fielddb[$fieldname];
					}
				}
			}
		}

		/*活动费用处理*/
		$fees = $feesArray = array();
		if ($feesdb) {
			foreach ($feesdb as $value) {
			if (strpos($value,'act[fees][condition][](|)') !== false) {
					list(,$condition) = explode('(|)',$value);
					$condition && $fees['condition'][] = $condition;
				} elseif (strpos($value,'act[fees][money][](|)') !== false) {
					list(,$money) = explode('(|)',$value);
					$money && $fees['money'][] = $money;
				}
			}
			if ($fees['condition']) {	
				foreach ($fees['condition'] as $key => $value) {
					if ($value && $fees['money'][$key]) {
						$feesArray[$key]['condition'] = $value;
						$feesArray[$key]['money'] = $fees['money'][$key];
					}
				}
			}

			$feesArray && $actdb['fees'] = serialize($feesArray);
		}
		/*活动费用处理*/

		/*费用明细处理*/
		$feesdetail = $feesdetailArray = array();
		if ($feesdetaildb) {
			foreach ($feesdetaildb as $value) {
				if (strpos($value,'act[feesdetail][item][](|)') !== false) {
					list(,$item) = explode('(|)',$value);
					$item && $feesdetail['item'][] = $item;
				} elseif (strpos($value,'act[feesdetail][money][](|)') !== false) {
					list(,$money) = explode('(|)',$value);
					$money && $feesdetail['money'][] = $money;
				}
			}
			if ($feesdetail['item']) {	
				foreach ($feesdetail['item'] as $key => $value) {
					if ($value && $feesdetail['item'][$key]) {
						$feesdetailArray[$key]['item'] = $value;
						$feesdetailArray[$key]['money'] = $feesdetail['money'][$key];
					}
				}
			}
			$feesdetailArray && $actdb['feesdetail'] = serialize($feesdetailArray);
		}
		
		/*费用明细处理*/

		return $actdb;
	}

	/**
	 * 检查发帖权限
	 * @access public
	 */
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

	function initSearchHtml($actmid = 0) {/*获取前台活动搜索列表*/
		global $fid, $searchname;

		$fieldService = L::loadClass('ActivityField', 'activity');
		$searchhtml = "<form action=\"thread.php?fid=$fid".($actmid ? "&actmid=$actmid" : "&allactmid=1")."\" method=\"post\">";
		$searchhtml .= "<input type=\"hidden\" name=\"topicsearch\" value=\"1\"><script src=\"js/date.js\"></script><table>";
		if ($actmid) {
			$searchFieldDb = $fieldService->getEnabledAndSearchableFieldsByModelId($actmid);
		} else {
			$searchFieldDb = $fieldService->getDefaultSearchFields();
		}
		$vieworder_mark = $ifsearch = $ifasearch = 0;
		if ($searchFieldDb) {
			foreach ($searchFieldDb as $rt) {
				if($rt['ifasearch'] == 1) {
					$ifasearch = '1';
					if ($rt['ifsearch'] == 0) continue;
				}
				$ifsearch = '1';
				$type = $rt['type'];
				$fieldid = $rt['fieldid'];
				$fieldname = $rt['fieldname'];

				if ($vieworder_mark != $rt['vieworder']) {
					if ($vieworder_mark != 0) $searchhtml .= "</th></tr>";
					$searchhtml .= "<tr><th>";
					$searchhtml .= $rt['name'][0] ? $rt['name'][0]."：</th><td>".$rt['name'][1] : '';
					//$searchhtml .= "</td><tr>";
				} elseif ($vieworder_mark == $rt['vieworder']) {
					$searchhtml .= $rt['name'][0] ? $rt['name'][0] : '';
				}
				
				$op_key = $op_value = '';
				if (!$rt['textwidth'] || $rt['textwidth'] >15){
					$textwidth = 15;
				} else {
					$textwidth = $rt['textwidth'];
				}
				$rules = $rt['rules'];

				if (in_array($type,array('radio','select'))) {
					$searchhtml .= "<select name=\"searchname[$fieldname]\"><option value=\"\"></option>";
					foreach ($rules as $key => $value) {
						$op_key = substr($value,0,strpos($value,'='));
						$op_value = substr($value,strpos($value,'=')+1);
						$selected = $searchname[$fieldname] == $op_key ? 'selected' : '';
						$searchhtml .= "<option value=\"".$op_key."\" $selected>".$op_value."</option>";
					}
					$searchhtml .= '</select>';
				} elseif ($type == 'checkbox') {
					foreach($rules as $ck => $cv){
						$op_key = substr($cv,0,strpos($cv,'='));
						$op_value = substr($cv,strpos($cv,'=')+1);
						$checked = in_array($op_key, $searchname[$fieldname]) ? 'checked' : '';
						$searchhtml .= "<input type=\"checkbox\" name=\"searchname[$fieldname][]\" value=\"$op_key\" $checked/> $op_value ";
					}
				} elseif ($type == 'calendar') {
					$showCalendarJsOption = 'minute' == $rules['precision'] ? 1 : 0;
					$searchhtml .= "<input id=\"calendar_start_$fieldname\" type=\"text\" class=\"input\" name=\"searchname[$fieldname]\" value=\"$searchname[$fieldname]\" onclick=\"ShowCalendar(this.id,$showCalendarJsOption)\" size=\"$textwidth\"/>";
				} elseif ($type == 'range') {
					$searchhtml .= "<input type=\"text\" size=\"5\" class=\"input\" name=\"searchname[$fieldname][min]\" value=\"{$searchname[$fieldname]['min']}\"/> - <input type=\"text\" class=\"input\" name=\"searchname[$fieldname][max]\" value=\"{$searchname[$fieldname]['max']}\" size=\"$textwidth\"/>";
				} else {
					$searchhtml .= "<input type=\"text\" size=\"$textwidth\" name=\"searchname[$fieldname]\" value=\"$searchname[$fieldname]\" class=\"input\">";
				}

				$searchhtml .= $rt['name'][2];
				$vieworder_mark = $rt['vieworder'];

			}
		}
		$searchhtml .= "</td></tr><tr><th></th><td><span class=\"btn2\" style=\"margin-right:10px;\"><span><button type=\"submit\" name=\"submit\">".getLangInfo('other','pc_search')."</button></span></span>";
		$ifsearch == 0 && $searchhtml = '</td></tr></table></form>';

		$ifasearch == '1' && $searchhtml .= "<a id=\"aserach\" href=\"javascript:;\" onclick=\"sendmsg('pw_ajax.php?action=asearch&fid=$fid".($actmid ? "&actmid=$actmid" : "&allactmid=1")."','',this.id);\">".getLangInfo('other','pc_asearch')."</a></td></tr></table></form>";

		if (strpos($searchhtml,'</td></tr><input type="submit"') !== false) {
			$searchhtml = str_replace('</td></tr><input type="submit"','</td></tr><tr><th></th><td><input type="submit"',$searchhtml);
		} elseif (strpos($searchhtml,'<input type="submit" name="submit" value="') !== false) {
			$searchhtml = str_replace('<input type="submit" name="submit" value="','</td></tr><tr><th></th><td><input type="submit" name="submit" value="',$searchhtml);
		}
		return $searchhtml;
	}

	function getASearchHTML($type,$fieldid,$size,$rules){
		!$size && $size = 20;

		if (in_array($type,array('radio','select'))) {
			$searchhtml .= "<select name=\"searchname[$fieldid]\"><option value=\"\"></option>";
			foreach ($rules as $key => $value) {
				$op_key = substr($value,0,strpos($value,'='));
				$op_value = substr($value,strpos($value,'=')+1);
				$searchhtml .= "<option value=\"".$op_key."\">".$op_value."</option>";
			}
			$searchhtml .= '</select>';
		} elseif ($type == 'checkbox') {
			foreach($rules as $ck => $cv){
				$op_key = substr($cv,0,strpos($cv,'='));
				$op_value = substr($cv,strpos($cv,'=')+1);
				$searchhtml .= "<input type=\"checkbox\" class=\"input\" name=\"searchname[$fieldid][]\" value=\"$op_key\"/> $op_value ";
			}
		} elseif ($type == 'calendar') {
			$showCalendarJsOption = 'minute' == $rules['precision'] ? 1 : 0;
			$searchhtml .= "<input id=\"calendar_start_searchname[$fieldid]\" type=\"text\" class=\"input\" name=\"searchname[$fieldid]\" onclick=\"ShowCalendar(this.id,$showCalendarJsOption)\" class=\"fl\" size=\"$size\"/>";
		} elseif ($type == 'range') {
			$searchhtml .= "<input type=\"text\"  class=\"input\" name=\"searchname[$fieldid][min]\"/> - <input type=\"text\" size=\"5\" class=\"input\" name=\"field[$fieldid][max]\" size=\"$size\"/>";
		} else {
			$searchhtml .= "<input type=\"text\" name=\"searchname[$fieldid]\" value=\"\" class=\"input\"  size=\"$size\">";
		}
		return $searchhtml;
	}
	/**
	 * 获取字段名第一部分
	 * @param string $name 字段名
	 * @return string 字段名第一部分
	 */
	function getFieldNameOneByName($name) {
		$names = $this->getNamePartsByName($name);
		return $names[0];
	}
	
	/**
	 * 获取字段名的3部分
	 * @param string $name
	 * @return array 字段名的3部分
	 */
	function getNamePartsByName($name) {
		list($name1, $name3) = explode('{#}',$name);
		list($name1, $name2) = explode('{@}',$name1);
		return array($name1, $name2, $name3);
	}
	/**
	 * 获取帖子列表页字段展示HTML
	 * @param string $type 字段类型
	 * @param mix $fieldValue　字段值
	 * @param string $rules 规则（序列化）
	 * @param string $fieldName 字段名
	 * @return string HTML
	 */
	function getThreadFieldValueHtml($type, $fieldValue, $rules, $fieldName = '') {
		if ($fieldName == 'fees') {
			$html = '';
			$feesTempValue = unserialize($fieldValue);
			foreach ($feesTempValue as $feesItem) {
				$html .= $this->getFeesRowHtml('', $feesItem['condition'], $feesItem['money'], 0, 0).', ';
			}
			$html = trim($html, ' ,');
		} elseif ($fieldName == 'feesdetail') {
			$html = '';
			$feesTempValue = unserialize($fieldValue);
			foreach ($feesTempValue as $feesItem) {
				$html .= $feesItem['item'].'/'.$feesItem['money']. getLangInfo('other','act_RMB') . ', ';
			}
			$html = trim($html, ' ,');
		} else {
			$html = $this->getFieldValueHTML($type, $fieldValue, $rules);
		}
		return $html;
	}
	
	function getActivityStatusValue($tid){
		
		$activityValue = array();
		$defaultValueTableName = getActivityValueTableNameByActmid();
		$activityValue = $this->db->get_one("SELECT iscancel,signupstarttime,signupendtime,endtime,starttime,maxparticipant FROM $defaultValueTableName dt WHERE dt.tid=".S::sqlEscape($tid));
		$activityStatusKey = $this->getActivityStatusKey($activityValue, $this->timestamp, $this->peopleAlreadySignup($tid));
		switch ($activityStatusKey){
			case 'activity_is_cancelled':
				$return = 3;
				break;
			case 'signup_is_ended':
				$return = 2;
				break;
			case 'activity_is_ended':
				$return = 2;
				break;
			case 'signup_number_limit_is_reached':
			case 'signup_is_available':
				$return = 1;
				break;
			default:
				$return = 0;
				break;
		}
		return $return;
	}
	
}
?>
