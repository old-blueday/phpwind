<?php
!defined('P_W') && exit('Forbidden');

/**
 * 实名认证
 * fix by sky_hold@163.com
 *
 */
class PW_Authentication {
	
	var $expiresTime = 180;	//验证码有效期
	var $waitTime = 90;		//重新获取验证码间隔时
	var $certificateTypes = array(); //证件认证类型
	var $_time;

	function PW_Authentication() {
		$this->_time = $GLOBALS['timestamp'];
		$this->_initCertificateMap();
	}
	
	function getStatus($bev) {
		list($sendTime, $sendMobile) = $this->getPreInfo($bev);
		$step = 1;
		$remainTime = $this->waitTime;
		if ($sendTime > 0 && $sendTime + $this->expiresTime > $this->_time) {
			$step = 2;
			$remainTime -= ($this->_time - $sendTime);
			$remainTime < 0 && $remainTime = 0;
		} else {
			$sendMobile = '';
		}
		return array($step, $remainTime, $this->waitTime, $sendMobile);
	}

	function getverify($bev, $mobile, $markid, $issendtocredit = false, $messagetype = '') {
		$params = array(
			'mobile' => $mobile,
			'markid' => $markid
		);
		$issendtocredit && $params['issendtocredit'] = true;
		$messagetype && $params['messagetype'] = $messagetype;

		$returnData = $this->sendData('credit.mobile.getverify', $params);
		if (!$returnData->isOpen || $returnData->isError) {
			return 1;
		} elseif (!$returnData->isMobile) {
			return 2;
		} elseif ($returnData->isBlack) {
			return 8;
		} elseif ((bool)$returnData->isCredit != $issendtocredit) {
			return 3;
		} elseif ($returnData->count >= $returnData->sendNum) {
			return 4;
		}
		$this->setCurrentInfo($bev, $GLOBALS['timestamp'], $mobile);
		return 0;
	}

	function checkverify($mobile, $markid, $verify) {
		$returnData = $this->sendData(
			'credit.mobile.checkverify',
			array('mobile' => $mobile, 'markid' => $markid, 'verify' => $verify)
		);
		return $returnData->status;
	}

	function syncuser($mobile, $markid, $verify, $userid, $username, $userfrom) {
		$returnData = $this->sendData(
			'credit.mobile.syncuser',
			array(
				'mobile'	=> $mobile,
				'markid'	=> $markid,
				'verify'	=> $verify,
				'userid'	=> $userid,
				'username'	=> $username,
				'userfrom'	=> $userfrom
			)
		);
		return $returnData->status;
	}

	function sendData($method, $params) {
		if ($method == '') return false;
		L::loadClass('client', 'utility/platformapisdk', false);
		L::loadClass('json', 'utility', false);
		$PlatformApiClient = new PlatformApiClient($GLOBALS['db_sitehash'], $GLOBALS['db_siteownerid']);
		$returnData = $PlatformApiClient->get($method, $params);//return $returnData;
		$Json = new Services_JSON();
		return $Json->decode($returnData);
	}

	function getPreInfo($apply) {
		return array(GetCookie($apply . '_verifySendTime'), GetCookie($apply . '_verifyMobile'));
	}

	function setCurrentInfo($apply, $time = '', $mobile = '') {
		Cookie($apply . '_verifySendTime', $time, $time ? 'F' : 0);
		Cookie($apply . '_verifyMobile', $mobile, $mobile ? 'F' : 0);
	}
	
	//证件认证functions
	/**
	 * 
	 * 证件认证信息获取
	 */
	function getCertificateInfoByUid($uid){
		$uid = intval($uid);
		if ($uid < 1) return false;
		$dao = $this->_getAuthCertificateDB();
		return $dao->getAuthCertificateByUid($uid);
	}
	
	function getCertificateInfo($start,$limit,$state = 0){
		$start = intval($start);
		$limit = intval($limit);
		$state = intval($state);
		!$limit && $limit = $GLOBALS['db_perpage'];
		if ($state) {
			$states = $this->getCertificateStates();
			!isset($states[$state]) && $state = 0;
		}
		$dao = $this->_getAuthCertificateDB();
		return $dao->getCertificateInfo($start,$limit,$state);
	}
	
	function countCertificateInfo($state = 0){
		$state = intval($state);
		if ($state) {
			$states = $this->getCertificateStates();
			!isset($states[$state]) && $state = 0;
		}
		$dao = $this->_getAuthCertificateDB();
		return $dao->countCertificateInfo($state);
	}
	
	function addCertificateInfo($data){
		$data = $this->filterCertificateInfo($data);
		$dao = $this->_getAuthCertificateDB();
		return $dao->insert($data);
	}
	function updateCertificateInfo($data,$id){
		$id = intval($id);
		if ($id < 1) return false;
		$data = $this->filterCertificateInfo($data);
		$dao = $this->_getAuthCertificateDB();
		$dao->update($data,$id);
	}
	
	function updateCertificateStateByIds($ids,$state){
		global $db_md_ifopen;
		$states = $this->getCertificateStates();
		if (!isset($states[$state]) || !S::isArray($ids)) return false;
		$dao = $this->_getAuthCertificateDB();
		if ($state == 2 || $state == 3) {
			$status = $state == 2 ? 1 : 0;
			if ($state == 2) {
				$status = 1;
				$msgType = 1;
			} else {
				$status = 0;
				$msgType = 2;
			}
			$userService = L::loadClass('UserService','user');
			$uids = array();
			//颁发勋章
			if ($db_md_ifopen) {
				$medalService = L::loadClass('medalservice','medal');
			}
			foreach ($ids as $id) {
				if($info = $dao->get($id)){
					$userService->setUserStatus($info['uid'], PW_USERSTATUS_AUTHCERTIFICATE,$status);
					$medalService && $medalService->awardMedalByIdentify($info['uid'],'shimingrenzheng');
					$uids[] = $info['uid'];
				}
			}
			//发消息
			$uids && $this->sendCertificateMessage($GLOBALS['admin_name'],$uids,$msgType);

		}
		$dao->updateCertificateStateByIds($ids,$state);
	}
	
	/**
	 * 
	 * 发送消息
	 * @param string $sender
	 * @param array $receivers (uids)
	 * @param int $type 1通过,2拒绝
	 */
	function sendCertificateMessage($sender,$receivers,$type){
		if (!$sender || !S::isArray($receivers)) return false;
		$userService = L::loadClass('UserService','user');
		$sendUid = $userService->getUserIdByUserName($sender);
		$receivers = $userService->getUserNamesByUserIds($receivers);
		$msgTitle = '来自实名认证的消息';
		if($type == 1){
			$msgContent = '您的实名认证申请已经通过，祝您生活愉快。本消息由系统自动生成请勿回复。';
		} else {
			$msgContent = '您的实名认证申请未获准通过，请按照站点要求重新提交所需资料。如有不明之处请与网站管理员联系。';
		}
		$messageServer = L::loadClass("message", 'message');
		return $messageServer->sendMessage(
			$sendUid,
			$receivers,
			array(
				'create_uid' => $sendUid,
				'create_username' => $sender,
				'title' => $msgTitle,
				'content' => $msgContent,
			),
			null,
			true
		);
	}
	
	function deleteCertificateByIds($ids){
		if (!S::isArray($ids)) return false;
		$dao = $this->_getAuthCertificateDB();
		foreach ($ids as $v){
			$return = $this->deleteCertificateById($v);
			if ($return !== true){
				return $return;
			}
		}
		return true;
	}
	
	function deleteCertificateById($id,$force = false){
		global $attachdir;
		$id = intval($id);
		$dao = $this->_getAuthCertificateDB();
		$info = $dao->get($id);
		if (S::isArray($info)) {
			if ($info['state'] == 2 && !$force) return '不能够删除已认证的会员信息';
			$info['attach1'] && P_unlink("$attachdir/{$info['attach1']}");
			$info['attach2'] && P_unlink("$attachdir/{$info['attach2']}");
			$info['state'] == 1 && $this->sendCertificateMessage($GLOBALS['admin_name'],array($info['uid']),2);
			$dao->delete($id);
		}
		return true;
	}
	
	/**
	 * 认证信息白名单过滤
	 * @param array $data
	 */
	function filterCertificateInfo($data){
		foreach($data as $k=>$v){
			switch ($k){
				case 'type':
				case 'uid':
				case 'state':
				case 'createtime':
				case 'admintime':
					$data[$k] = intval($v);
					break;
				case 'attach1':
				case 'attach2':
				case 'number':
					//$data[$k] = S::sqlEscape($v);
					break;
				default:
					unset($data[$k]);
					break;
			}
		}
		return $data;
	}
	
	function _initCertificateMap(){
		$this->certificateTypes = array(
			1	=> '身份证',
			2	=> '护照',
			3	=> '营业执照',
			4	=> '组织机构代码证',
			5	=> '其它'
		);		
	}
	
	function getCertificateStates(){
		return array(
			1	=> '待审核',
			2	=> '已通过',
			3	=> '已拒绝'
		);
	}
	
	function getCertificateTypeHtml($default = 1){
		$html = '';
		foreach ($this->certificateTypes as $k=>$v) {
			$html .= sprintf('<option value="%d">%s</option>',$k,$v);
		}
		return $html;
	}
	
	function _getAuthCertificateDB() {
		return L::loadDB('AuthCertificate', 'user');
	}
	//end 证件认证
}
?>