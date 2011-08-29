<?php
/**
 * 日志服务类文件
 * @author lmq
 * @package diary
 */

!defined('P_W') && exit('Forbidden');

/**
 * 日志服务对象
 *
 * @package PW_Diary
 */
class PW_Diary {
	
	var $attachShow;

	function countFriendsDiarys($userIds, $diaryTypeId = null) {
		$diaryDb = $this->_getDiaryDB();
		return $diaryDb->countDiarysByUids($userIds, $diaryTypeId);
	}

	function findFriendsDiarysInPage($userIds, $page = 1 , $perpage = 20, $diaryTypeId = null) {
		if(!$userIds) return array();
		$friendsDiarys = $this->findUserDiarysByUids($userIds, $page, $perpage, $diaryTypeId);
		$userService = $this->_serviceFactory('UserService', 'user'); /* @var $userService PW_UserService */
		$friendsInfo = $userService->getByUserIds($userIds);
		$diaryRt = array();
		$diaryRt = $this->_buildFriendsDiarys($friendsDiarys, $friendsInfo);
		return $diaryRt;
	}

	/**
	 * 根据用户uid，找出他的好友uid
	 *
	 * @param int $userId
	 * @return array()	$friendsUids	array(0=>uid1,1=>uid2,.......n=>uidn)
	 */
	function findFriendsByUid($userId) {
		$friendsService = $this->_serviceFactory('Friend', 'friend'); /* @var $friendsService PW_Friend */
		$friends = $friendsService->getFriendsByUid($userId);
		if(!$friends) return array();

		$friendsUids = array();
		foreach ($friends as $friend) {
			$friendsUids[] = $friend['friendid'];
		}
		$friendsUids && $friendsUids = array_diff($friendsUids,array($userId));
		if(!$friendsUids) return array();

		return $friendsUids;
	}

	/**
	 * 根据用户日志数据，和用户基本信息，构建好友日志数据
	 *
	 * @param	array()		$UserDiarys
	 * @param 	array()		$UserInfos
	 */
	function _buildFriendsDiarys($userDiarys,$userInfos) {
		if (!$userDiarys && !is_array($userDiarys)) return array();
		if (!$userInfos && !is_array($userInfos)) return array();
		global $winduid, $db_bbsurl, $basename;
		$diaryRt = array();
		$diaryAttachsData = array();
		require_once(R_P.'require/bbscode.php');

		$temUserInfo = array();
		foreach ($userInfos as $userInfo) {
			$temUserInfo[$userInfo['uid']] = $userInfo;
		}

		foreach ($userDiarys as $diary) {
			$diary['groupid'] = $temUserInfo[$diary['uid']]['groupid'];
			$diary['icon'] = $temUserInfo[$diary['uid']]['icon'];
			list($diary['subject'], $diary['content']) = $this->_getContentANDSubjectByDiary($diary, TRUE, TRUE);
			$diaryAttachsData = $this->_getAttachs($diary['aid'], $diary['content'], $diary['uid']);
			$diaryAttachsData && $diary = array_merge($diary, $diaryAttachsData);
			$diary['postdate'] = $this->_getDate($diary['postdate'],'Y-m-d H:i');

			$diary['link'] = "$db_bbsurl/{$basename}q=diary&u=$diary[uid]&did=$diary[did]";
			$diary['title'] = "($diary[link])";
			if ($diary['uid']!=$winduid) list($diary['icon']) = showfacedesign($diary['icon'],1);
			$diaryRt[] = $diary;
		}

		return $diaryRt;
	}

	/**
	 * 统计我的日志数量
	 *
	 * @param $userId
	 * @param $diaryTypeId	日志分类
	 * @param $privacy		日志权限 array(0,1,2) 全站可见，仅好友可见，仅自己可见

	 */
	function countUserDiarys($userId,$diaryTypeId = null, $privacy = array()) {
		global $winduid;
		$diaryDb = $this->_getDiaryDB();
		return $diaryDb->countUserDiarys($userId, $diaryTypeId, $privacy);
	}

	function findUserDiarysByUids($userIds, $page = 1, $perpage = 20, $diaryTypeId = null) {
		$diaryDb = $this->_getDiaryDB();
		return $diaryDb->findUserDiarysByUids($userIds, $page, $perpage, $diaryTypeId);
	}

	/**
	 * 我的日志列表
	 *
	 * @param	int		$userId
	 * @param	int		$page
	 * @param	int		$perpage
	 * @param	string	$pageUrl
	 * @param 	string 	$whereSql sql条件
	 * @param 	$privacy		日志权限 array(0,1,2) 全站可见，仅好友可见，仅自己可见
	 * @return array()
	 */
	function findUserDiarysInPage($userId, $page = 1, $perpage = 20, $diaryTypeId = null, $privacy = array()) {
		global $groupid, $winduid;
		$diaryData = $this->findUserDiarys($userId, $page, $perpage, $diaryTypeId, $privacy);
		$diaryRt = array();
		$diaryAttachsData = array();
		$gid = $groupid;
		if($userId != $winduid){
			$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
			$userInfo = $userService->get($userId);
			$gid = $userInfo['groupid'];
		}
		foreach ($diaryData as $diary) {
			$diary['groupid'] = $gid;
			list($diary['subject'], $diary['content']) = $this->_getContentANDSubjectByDiary($diary, TRUE, TRUE);
			$diaryAttachsData = $this->_getAttachs($diary['aid'], $diary['content'], $diary['uid']);
			$diaryAttachsData && $diary = array_merge($diary, $diaryAttachsData);
			$diary['postdate'] = $this->_getDate($diary['postdate'],'Y-m-d H:i');
			$diaryRt[] = $diary;
		}
		return $diaryRt;
	}

	/**
	 * 根据UID找用户日志
	 *
	 * @param $userId
	 * @param $page
	 * @param $perpage
	 * @param $diaryTypeId   日志分类
	 * @param $privacy		日志权限 array(0,1,2) 全站可见，仅好友可见，仅自己可见
	 */
	function findUserDiarys($userId, $page = 1, $perpage =20, $diaryTypeId = null, $privacy = array()) {
		$diaryDb = $this->_getDiaryDB();
		return $diaryDb->findUserDiarys($userId, $page, $perpage, $diaryTypeId, $privacy);
	}

	function filterDiaryContent($data, $islist = false, $isFilterFace = false) {
		return $this->_getContentANDSubjectByDiary($data, $islist, $isFilterFace);
	}

	/**
	 * 有条件处理日志内容和标题
	 *
	 * @param array() 	$data		日志数据
	 * @param bool 		$islist		是否是列表，列表页日志内容显示字节
	 */
	function _getContentANDSubjectByDiary($data, $islist = false, $isFilterFace = false) {
		global $groupid, $db_shield, $db_windpost, $o_shownum;

		$result = array();
		if ($data['groupid'] == 6 && $db_shield && $groupid != 3) {
			$data['subject'] = '';
			$data['content'] = appShield('ban_diary');
		}

		$wordsService = $this->_serviceFactory('FilterUtil', 'filter'); /* @var $wordsService PW_FilterUtil */
		if (!$wordsService->equal($data['ifwordsfb'])) {
			$data['content'] = $wordsService->convert($data['content'], array(
				'id'	=> $data['did'],
				'type'	=> 'diary',
				'code'	=> $data['ifwordsfb']
			));
		}
		
		$isFilterFace == true && $data['content'] = preg_replace("/\[s:(.+?)\]/eis",'',$data['content']);
		require_once(R_P.'require/bbscode.php');
		$data['ifconvert'] == 2 && $data['content'] = convert($data['content'], $db_windpost);
		if ($islist) {
			$data['content'] = strip_tags($data['content']);
			$data['content'] = stripWindCode($this->escapeStr($data['content']));
			$o_shownum && $data['content'] = substrs($data['content'],$o_shownum);
		}
		$data['content'] = preg_replace('/\[upload=(\d+)\]/Ui', "", $data['content']);
		$data['content'] = str_replace("\n","<br />",$data['content']);
		$result = array($data['subject'],$data['content']);
		return $result;
	}

	/**
	 * 处理日志附件、内容图片
	 *
	 * @param $aid			日志里附件存储数据，unserialize格式类型	aid
	 * @param $content  	日志内容
	 * @param $authorid		日志作者
	 */
	function _getAttachs($aid, &$content, $authorid) {
		global $winduid, $isGM;
		if (!$aid || !($attachs = unserialize($aid)) || !is_array($attachs)) {
			return array();
		}
		$GLOBALS += L::style();
		require_once(R_P.'require/bbscode.php');
		$attachShow = new attachShow($isGM,'',false,'diary');
		$attachShow->setData($attachs);
		return $attachShow->parseAttachs('tpc', $content, $winduid == $authorid);
	}

	/**
	 * 添加日志分类
	 *
	 * @param array $data
	 */
	function addTypeByDiary($data = array()) {
		if ($data['name']) $this->_postCheckDiaryType($data['uid'], $data['name']);
		$id = $this->insertDiaryType($data);
		return $id;
	}

	function editTypeByDiary($userId, $dtid, $data = array()) {
		$typeTemp = $this->getDiaryTypeBydtid($dtid);
		if (stripslashes($data['name']) == $typeTemp['name']) return True;
		if ($data['name']) $this->_postCheckDiaryType($userId, $data['name']);
		return $this->updateDiaryTypeByDtid($dtid, $data);
	}

	function _postCheckDiaryType($userId, $typeName) {
		global $winduid, $isGM;
		$userId = (int)$userId;
		if (!$userId) $this->_showMsg('undefined_action');
		if ($userId != $winduid && !$isGM) $this->_showMsg('undefined_action');
		if (strlen($typeName)<1 || strlen($typeName)>20) $this->_showMsg('mode_o_adddtype_name_leng');

		$diaryType = $this->findDiaryTypeByUid($userId);
		$i = 0;
		foreach ($diaryType as $type) {
			$i++;
			if($typeName != $type['name']) continue;
			$this->_showMsg('mode_o_adddtype_name_exist');
		}
		if ($i > 20) $this->_showMsg('mode_o_adddtype_length');

	}

	/**
	 * 根据用户id 查找日志分类
	 *
	 * @param int	$userId
	 * @return array
	 */
	function findDiaryTypeByUid($userId) {
		$diaryDb = $this->_getDiaryDB();
		return $diaryDb->findDiaryTypeByUid($userId);
	}




	function getDiaryTypeBydtid($dtid) {
		$diaryDb = $this->_getDiaryDB();
		return $diaryDb->getDiaryTypeBydtid($dtid);
	}



	/**
	 * 添加日志分类
	 *
	 * @param array $data
	 */
	function insertDiaryType($data = array()) {
		$diaryDb = $this->_getDiaryDB();
		return $diaryDb->insertDiaryType($data);
	}


	function updateDiaryTypeByDtid($dtid, $data = array()) {
		$diaryDb = $this->_getDiaryDB();
		return $diaryDb->updateDiaryTypeByDtid($dtid, $data);
	}

	function delDiaryTypeByDtid($dtid) {
		!$dtid && $this->_showMsg('undefined_action');
		$diaryDb = $this->_getDiaryDB();
		$affected_rows = $diaryDb->deleteDiaryType($dtid);

		if ($affected_rows) {
			$data = array('dtid'=>0);
			$diaryDb->updateDiaryByDtid($data, $dtid);
		}

		return $affected_rows;
	}

	function get($id) {
		$diaryDb = $this->_getDiaryDB();
		return $diaryDb->get($id);
	}

	function delDiary($id) {
		global $winduid, $isGM, $db_ifftp, $SYSTEM;
		if (!$id) return false;
		$diary = $this->get($id);
		!$diary && Showmsg('mode_o_no_diary');

		if ($winduid != $diary['uid'] && !$isGM && !$SYSTEM['deldiary']) {
			$this->_showMsg('mode_o_deldiary_permit_err');
		}
		$diaryDb = $this->_getDiaryDB();
		$affected_rows = $diaryDb->delete($id);

		$attachsService = L::loadClass('attachs','forum'); /* @var $attachsService PW_attachs */
		$attachs = array();
		$attachs = $attachsService->getDiaryAttachsBydid($id);
		foreach($attachs as $attach) {
			pwDelatt("diary/".$attach['attachurl'], $db_ifftp);
			$attachsService->delByids(array($attach['aid']));
		}
		if ($affected_rows) $diaryDb->countDiaryTypeNum($diary['dtid'], "-$affected_rows");
	}

	function getDiaryDbView($diary) {
		global $db_bbsurl, $basename, $space,$tpc_author;

		$diary['groupid'] = $space['groupid'];
		$tpc_author = $diary['username'];

		list($diary['subject'], $diary['content']) = $this->_getContentANDSubjectByDiary($diary);
		$diaryAttachsData = $this->_getAttachs($diary['aid'], $diary['content'], $diary['uid']);
		$diaryAttachsData && $diary = array_merge($diary, $diaryAttachsData);
		list($diary['copyuid'],$diary['copyer'],$diary['url']) = explode("|",$diary['copyurl']);
		$diary['link'] = "$db_bbsurl/{$basename}q=diary&u=$diary[uid]&did=$diary[did]";
		$diary['title'] = "($diary[link])";
		$diary['postdate'] = get_date($diary['postdate'],'Y-m-d H:i');
		$diary['r_num'] += 1;

		$fieldData = array('r_num'=>$diary['r_num']);
		$this->update($fieldData, $diary['did']);

		return $diary;
	}



	function update($fieldData,$id){
		$diaryDb = $this->_getDiaryDB();
		return $diaryDb->update($fieldData, $id);
	}


	/**
	 * 取得日志分类模块
	 *
	 *
	 * @param int $userId
	 * @param array $diaryPrivacy
	 * @return  $diaryNums, $diaryType, $defaultTypeNum, $privacyNum
	 * 			日志总数、日志分类、默认日志数量、隐私日志数量
	 */
	function getDiaryTypeMode($userId, $diaryPrivacy) {
		$diaryNums = $defaultTypeNum = $privacyNum = 0;
		$diaryType = $typeNum = $diaryDb = array();
		$diaryType = $this->findDiaryTypeByUid($userId);

		$diaryDb = $this->findUserDiaryByPrivacy($userId, $diaryPrivacy);

		foreach ($diaryDb as $diary) {
			$diaryNums ++;
			$diary['dtid'] == 0 && $defaultTypeNum++;
			$diary['privacy'] == 2 && $privacyNum++;
			$typeNum[$diary['dtid']] ++;	//单个分类日志数
		}

		foreach ($diaryType as $key=>$type) {
			$diaryType[$key]['num'] = (int)$typeNum[$key];
		}

		return array($diaryNums, $diaryType, $defaultTypeNum, $privacyNum);

	}

	/**
	 * 取得用户不同隐私权限日志的数量
	 *
	 * @param int $userId
	 * @param array $privacy     0  全站可见  1 仅好友可见   2仅自己可见
	 */
	function findUserDiaryByPrivacy($userId ,$privacy = array()) {
		$diaryDb = $this->_getDiaryDB();
		return $diaryDb->findUserDiaryByPrivacy($userId, $privacy);
	}

	function delByUids($uids) {
		global $db_ifftp;
		if (!$uids || !is_array($uids)) return false;
		$diaryDb = $this->_getDiaryDB();
		$diaryDb->delDiaryByUids($uids);
		$diaryDb->delDiaryTypeByUids($uids);
		$attachsService = L::loadClass('attachs','forum'); /* @var $attachsService PW_attachs */
		$attachs = array();
		$attachs = $attachsService->getByUids($uids);
		foreach($attachs as $attach) {
			pwDelatt("diary/".$attach['attachurl'], $db_ifftp);
			$attachsService->delByids(array($attach['aid']));
		}
		return true;
	}

	function escapeStr($str) {
		if (!$str = trim($str)) return '';
		return preg_replace('/(&nbsp;){1,}/', ' ', $str);
	}
	
	function updateDiaryContentByAttach($did, $uploadIds) {
		if (!$uploadIds) return false;
		$diaryContent = $this->get($did);
		if (!$diaryContent) return false;
		foreach ($uploadIds as $key => $value) {
			$diaryContent['content'] = str_replace("[upload=$key]", "[attachment=$value]", $diaryContent['content']);
		}
		$this->update(array('content' => $diaryContent['content']), $did);
	}
	/**
	 * Get PW_DiaryDB
	 *
	 * @access protected
	 * @return PW_DiaryDB
	 */
	function _getDiaryDB() {
		return L::loadDB('Diary', 'diary');
	}

	/**
	 * 格式化时间戳为日期字符串
	 *
	 * @param int 		$timestamp
	 * @param string 	$format
	 */
	function _getDate($timestamp, $format = null) {
		return  get_date($timestamp, $format);
	}

	/**
	 * 私有加载记录服务入口
	 * @param PW_$name
	 * @return PW_$name
	 */
	function _serviceFactory($name, $dir='') {
		$name = strtolower($name);
		return L::loadClass($name, $dir);
	}


	function _showMsg($msg){
		return Showmsg($msg);;
	}
}
