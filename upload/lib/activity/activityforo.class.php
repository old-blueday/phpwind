<?php
!defined('P_W') && exit('Forbidden');
L::loadClass('PostActivity', 'activity', false);

class PW_ActivityForO extends PW_PostActivity {
	/**
	 * 获取功能名称
	 * @param string $see
	 * @return string
	 */
	function PW_ActivityForO() {
		$this->initGlobalValue();
	}
	function getSeeTitleBySee($see) {
		if ('fromgroup' == $see) {
			$seeTitle = '来自群组';
		} elseif ('feeslog' == $see) {
			$seeTitle = '费用流通日志';
		} else {
			$seeTitle = '来自版块';
		}
		return $seeTitle;
	}
	/**
	 * 返回搜索时间的预设值
	 * @return multitype:multitype:string  
	 */
	function getTimeOptions() {
		return array (
			'+86400' => '一天内',
			'+259200' => '三天内',
			'+604800' => '一周内',
			'+2592000' => '一月内',
		);
	}
	/**
	 * 返回时间select的HTML
	 * @param string $selected 选中的option的value
	 * @param bool $withEmptySelection 是否包含'时间不限'的option
	 * @param string $selectTagName select的name的值，如无，返回的HTML不包含select这个Tag(只有option Tag)
	 * @return HTML
	 */
	function getTimeSelectHtml($selected, $withEmptySelection=1, $selectTagName = 'time') {
		$options = array();
		if ($withEmptySelection) {
			$options[''] = '时间不限';
		}
		$options += $this->getTimeOptions();
		return getSelectHtml($options, $selected, $selectTagName);
	}
	/**
	 * 返回活动状态
	 * @param int $status 状态
	 * @return string|array 若$status有值，返回string状态；否则，返回array所有状态
	 */
	function getFeesLogStatus($status = '') {
		$Array = array(0 => '所有活动', 1 => '进行中', 2 => '已结束', 3 => '已取消', 4 => '已删除');
		if ($status !== '') {
			if (array_key_exists($status, $Array)) {
				return $Array[$status];
			} else {
				return '';
			}
		} else {
			return $Array;
		}
	}
	/**
	 * 返回活动费用类型
	 * @param int $type 类型
	 * @return string|array 若$type有值，返回string类型；否则，返回array所有类型
	 */
	function getFeesLogCostType($type = '') {
		//$Array = array(1 => '支付费用', 2 => '追加', 3 => '退款', 4 => '确认支付');
		$Array = array(1 => '', 2 => '追加', 3 => '退款', 4 => '');
		if ($type !== '') {
			if (array_key_exists($type, $Array)) {
				return $Array[$type];
			} else {
				return '';
			}
		} else {
			return $Array;
		}
	}
	/**
	 * 返回收入或支出类型
	 * @param string $type 类型
	 * @return string|array 若$type有值，返回string类型；否则，返回array所有类型
	 */
	function getExpenseOrIncomeName($type = '') {
		$Array = array('expense' => '<span class="s3">支出</span>', 'income' => '<span class="s2">收入</s3>');
		if ($type !== '') {
			if (array_key_exists($type, $Array)) {
				return $Array[$type];
			} else {
				return '未知';
			}
		} else {
			return $Array;
		}
	}
	function getSignupHtml($data) {
		$tid = $data['tid'];
		$activityStatusKey = $this->getActivityStatusKey($data, $this->timestamp, $this->peopleAlreadySignup($tid));
		$replaceArray = array();
		if ('activity_is_ended' == $activityStatusKey) {
			/*支付成功费用流通日志*/
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

	function getGroupSignupHtml($data) {//群组活动的状态获取
		global $winduid;
		$replaceArray = $data;
		require_once(A_P . 'groups/lib/active.class.php');
		$newActive = new PW_Active();
		if ($this->timestamp > $data['endtime']) {
			$activityStatusKey = 'activity_is_ended';
		} elseif ($this->timestamp > $data['deadline'] && $this->timestamp > $data['begintime']) {
			$activityStatusKey = 'activity_group_running';
		} elseif ($this->timestamp > $data['deadline']) {
			$activityStatusKey = 'signup_is_ended';
		} elseif ($data['limitnum'] && $data['members'] >= $data['limitnum']) {
			$activityStatusKey = 'signup_number_limit_is_reached';
		} elseif ($newActive->isJoin($data['id'], $winduid) && $winduid != $data['uid']) {
			$activityStatusKey = 'activity_is_joined';
		} elseif ($winduid == $data['uid']) {
			$activityStatusKey = 'activity_group_edit';
		} else {
			$activityStatusKey = 'group_is_available_for_member';
		}
		$signupHtml = '<p class="t3">';
		$signupHtml .= $this->getSignupHtmlByActivityKey($activityStatusKey, $replaceArray);
		$signupHtml .= '</p>';
		return $signupHtml;
	}
	
	function getSignupHtmlByActivityKey ($key, $replaceArray = NULL) {
		switch ($key) {
			case 'signup_not_started_yet': //未开始报名
				$html = '<span class="bt"><span><button type="button" disabled>报名未开始</button></span></span>';
				break;
			case 'activity_is_cancelled': //报名结束但未达到最低人数限制，活动取消
				$html = '<span class="bt"><span><button type="button" disabled>活动已取消</button></span></span>';
				break;
			case 'activity_is_running':
			case 'signup_is_ended': //正常情况下报名结束
				$html = '<span class="bt"><span><button type="button" disabled>报名已结束</button></span></span>';
				break;
			case 'activity_is_ended': //活动结束
				$html = '<span class="bt"><span><button type="button" disabled>活动已结束</button></span></span>';
				break;
			case 'signup_number_limit_is_reached': //报名人数已满
				$html = '<span class="bt"><span><button type="button" disabled>报名人数已满</button></span></span>';
				break;
			case 'signup_is_available':
			case 'signup_is_available_for_guest': //未登录状态报名提示
				$text = '我要报名';
			case 'signup_is_available_for_member': //登录状态，先前未报名，报名提示
				$text || $text = '我要报名';
			case 'additional_signup_is_available_for_member': //登录状态，先前已报名，报名提示
				$text || $text = '我要补报';
				$html = "<span class=\"btn\"><span><button type=\"button\" onclick=\"window.location='read.php?tid=$replaceArray[0]'\">$text</button></span></span>";
				break;
			case 'group_is_available_for_member' :
				$text || $text = '我要报名';
				$html = "<span class=\"btn\"><span><button type=\"button\" onclick=\"window.location='apps.php?q=group&a=active&job=view&cyid=".$replaceArray['cid']."&id=".$replaceArray['id']."'\">$text</button></span></span>";
				break;
			case 'activity_group_running'://群组活动的特殊状态
				$html = '<span class=\"bt\"><span><button type=\"button\>活动进行中</button></span></span>';
				break;
			case 'activity_is_joined':
				$html = "<span class=\"bt\"><span><button type=\"button\" id=\"active_quit\" onclick=\"sendmsg('apps.php?q=group&a=active&job=quit&cyid=".$replaceArray['cid']."&id=".$replaceArray['id']."', '', this.id);\">退出活动</button></span></span>";
				break;
			case 'activity_group_edit':
				$html = "<span class=\"btn\"><span><button type=\"button\" onclick=\"window.location='apps.php?q=group&a=active&job=edit&cyid=".$replaceArray['cid']."&id=".$replaceArray['id']."'\">编辑活动</button></span></span>";
				break;
			default:
				$html = '';
		}
		return $html;
	}
	/**
	 * 获取用户参与的所有活动
	 * @param int $uid 用户ID
	 * @return array 活动ID
	 */
	function getAllParticipatedActivityIdsByUid ($uid) {
		$activityIds = array();
		$query = $this->db->query("SELECT DISTINCT tid FROM pw_activitymembers WHERE uid = ". S::sqlEscape($uid));
		while ($rt = $this->db->fetch_array($query)) {
			$activityIds[] = $rt['tid'];
		}
		return $activityIds;
	}
	
	function getActivityNumberAndLastestTimestampByUid ($uid) {
		$allActivityIdsIHaveParticipated = $this->getAllParticipatedActivityIdsByUid($uid);
		$fids = trim(getSpecialFid() . ",'0'",',');
		!empty($fids) && $where .= ($where ? ' AND ' : '')."dv.fid NOT IN($fids)";
		$where .= ($where ? ' AND ' : ''). "(tr.authorid = ".S::sqlEscape($uid).($allActivityIdsIHaveParticipated ? ' OR tr.tid IN ('.S::sqlImplode($allActivityIdsIHaveParticipated).')' : '').')';
		$where && $where = " WHERE ".$where;
		$activitynum = $this->db->get_value("SELECT COUNT(*) FROM pw_threads tr LEFT JOIN pw_activitydefaultvalue dv USING (tid) $where");
		$activity_lastpost = $this->db->get_value("SELECT postdate FROM pw_threads tr LEFT JOIN pw_activitydefaultvalue dv USING (tid) $where ORDER BY postdate DESC LIMIT 1");
		return array ($activitynum, $activity_lastpost);
	}
	
	/**
	 * 获取费用支付说明
	 * @param int $ifpay  0=>未支付 1=>已支付 2=>确认支付 3=>交易关闭 4=>退款完毕 
	 * @param int $fromuid 帮助支付的用户ID
	 * @param string $fromusername 帮助支付的用户名
	 * @return string 说明字符串
	 */
	function getPayLogDescription(&$rt){
		global $winduid;
		$u = $winduid;
		if ($rt['ifpay'] == 1){
			if ($rt['issubstitute']){
				if ($rt['fromuid'] == $u){
					$rt['otherParty'] = $rt['author'];
					$rt['expenseOrIncome'] = 'expense';	
					$rt['payDescription'] = '我帮<a href="'.USER_URL. $rt['uid'] . '" target="_blank">' .  $rt['username'] . '</a>支付';
				}elseif($rt['uid'] == $u){
					$rt['otherParty'] = $rt['author'];
					$rt['expenseOrIncome'] = 'expense';	
					$rt['payDescription'] = '<a href="'.USER_URL. $rt['fromuid'] . '" target="_blank">' .  $rt['fromusername'] . '</a>帮我支付';
				}elseif ($rt['authorid'] == $u){
					$rt['otherParty'] = $rt['username'];
					$rt['expenseOrIncome'] = 'income';	
					$rt['payDescription'] = '<a href="'.USER_URL. $rt['fromuid'] . '" target="_blank">' .  $rt['fromusername'] . '</a>帮<a href="' .USER_URL . $rt['uid'] . '" target="_blank">' .  $rt['username'] . '</a>支付';
				}
			}else{
				$rt['otherParty'] = $rt['uid'] == $u ? $rt['author'] : $rt['username'];
				$rt['expenseOrIncome'] = $rt['uid'] == $u ? 'expense' : 'income';
				$rt['payDescription'] = '支付宝支付';
			}
		} elseif ($rt['ifpay'] == 4){
				$rt['otherParty'] = $rt['uid'] == $u ? $rt['author'] : $rt['username'];
				$rt['expenseOrIncome'] = $rt['uid'] == $u ? 'expense' : 'income';
				$rt['payDescription'] = '支付宝支付';
		} else{
			$rt['otherParty'] = $rt['uid'] == $u ? $rt['author'] : $rt['username'];
			if ($rt['costtype'] == 3){
				$rt['expenseOrIncome'] = $rt['uid'] != $u ? 'expense' : 'income';
				$rt['payDescription'] = '支付宝支付';
			}else{
				$rt['expenseOrIncome'] = $rt['uid'] == $u ? 'expense' : 'income';
				$rt['payDescription'] = '其他方式支付';
			}
		}
		$rt['expenseOrIncome'] = $this->getExpenseOrIncomeName($rt['expenseOrIncome']);
	}
}