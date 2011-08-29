<?php
!defined('P_W') && exit('Forbidden');
/**
 * 个人空间相册业务处理
 * @package PW_Photo
 * @author suqian
 * @access public
 */
class PW_Photo {
	var $_winduid = 0;
	var $_windid = 0;
	var $_uid = 0;
	var $_groupid = 0;
	var $_manager = array();
	var $_dbshield = 0;
	var $_dbifftp = 0;
	var $_timestamp = 0;
	var $_page = 0;
	var $_perpage = 0;
	var $_pwModeImg = '';
	var $_ifriend = 0;
	function PW_Photo($uid,$ifriend,$page,$perpage){
		$this->__construct($uid,$ifriend,$page,$perpage);
	}
	function __construct($uid,$ifriend,$page,$perpage){
		global $winduid,$windid,$groupid,$manager,$db_shield,$db_ifftp,$timestamp,$pwModeImg;
		$this->_winduid = $winduid;
		$this->_windid = $windid;
		$this->_uid = $uid;
		$this->_groupid = $groupid;
		$this->_manager = $manager;
		$this->_dbshield = $db_shield;
		$this->_dbifftp = $db_ifftp;
		$this->_timestamp = $timestamp;
		$this->_page = $page;
		$this->_perpage = $perpage;
		$this->_pwModeImg = $pwModeImg;
		$this->_ifriend = $ifriend;
	}
	/**
	 * 设置读取个数
	 * @param unknown_type $num
	 */
	function setPerpage($num) {
		$num = (int) $num ? (int) $num : 20;
		$this->_perpage = $num;
	}
	/**
	 *是否是超级管理员
	 *@return boolean
	 */
	function isSuper (){
		return in_array($this->_windid,$this->_manager);
	}
	/**
	 *是否是属于管理组
	 *@return boolean
	 */
	function isManger (){
		return $this->_groupid == 3;
	}
	/**
	 *是否具有管理权限
	 *@return boolean
	 */
	function isPermission(){
		if($this->isSuper() || $this->isManger()){
			return true;
		}
		return false;
	}
	
	/**
	 *是否具有删除权限
	 *@return boolean
	 */
	function isDelRight() {
		global $SYSTEM;
		return ($this->isSuper() || $SYSTEM['delalbum']);
	}
	/**
	 *是否是自己
	 *@return boolean
	 */
	function isSelf(){
		return $this->_uid == $this->_winduid;
	}
	/**
	 *是否是自己的相册
	 *@param $albumOwnerId int 相册拥有者ID
	 *@return boolean
	 */
	function isMyAlbum($albumOwnerId){
		return $albumOwnerId == $this->_winduid;
	}
	/**
	 *是否是指定用户下自己的相册
	 *@param $albumOwnerId int 相册拥有者ID
	 *@param $ifriend int 是否是好友相册
	 *@return boolean
	 */
	function isUserAlbum($albumOwnerId){
		if($this->_ifriend){
			return true;
		}
		return $albumOwnerId == $this->_uid;
	}
	/**
	 * 取得DAO类工厂,返回相关DB类
	 * @param string $daoName model层的db类
	 * @param string $dir 存放db的路径
	 * @return resource 返回DAO
	 */
	function _getDaoFactory($daoName, $dir = 'colony'){
		static $dao = array();
		if(!isset($dao[$daoName])){
			$dao[$daoName] = L::loadDB($daoName, $dir);
		}
		return $dao[$daoName];
	}
	/**
	 * 取得相关服务类实例工厂
	 *@param string $servicename 服务类的名字
	 *@return resource 返回相关服务类实例
	 */
	function _getServiceFactory($servicename, $dir = ''){
		return L::loadClass($servicename, $dir);
	}
	
	/****************************相册相关操作**************************/
	/**
	 *创建相册
	 *@param $data Array 要创建相册的相关信息
	 *@return int 返回相册表ID
	 */
	function createAlbum($data){
		if((!$this->isPermission() && !$this->isSelf()) || empty($data) || !is_array($data)){
			return array();
		}
		$albumDao = $this->_getDaoFactory('CnAlbum');
		return	 $albumDao->insert($data);
	}
	/**
	 *取得相册总数
	 *@return int 返回相册总数
	 */
	function getAlbumNumByUid(){
		$albumDao = $this->_getDaoFactory('CnAlbum');
		$priacy = $this->_getAlbumBrowseListPriacy();
		$result = $albumDao->getAlbumNumByUid($this->_uid,0,$priacy);
		return $result['sum'];
	}
	/**
	 *取得相册列表(包括个人管理中心相册列表,个人空间相册列表)
	 *@return Array 返回相册列表
	 */
	function getAlbumBrowseList(){
		$albumDao = $this->_getDaoFactory('CnAlbum');
		$priacy = $this->_getAlbumBrowseListPriacy();
		$userInfo = $this->getUserInfoByUid();
		$result = $albumDao->getAlbumNumByUid($this->_uid,0,$priacy);
		$total = $result['sum'];
		$albumdb =  array();
		if($total){
			$pageCount = ceil($total / $this->_perpage);
			$this->_page = validatePage($this->_page,$pageCount);
			$albumList = $albumDao->getPageAlbumsByUid($this->_uid,$this->_page,$this->_perpage,0,$priacy);
			foreach($albumList as $key => $value){
				$value['sub_aname']  = substrs($value['aname'],18);
				$value['lasttime']	 = get_date($value['lasttime']);
				$value['lastphoto']	 = getphotourl($value['lastphoto']);
				if ($this->_dbshield && $userInfo['groupid'] == 6 && !$this->isPermission()) {
					$value['lastphoto'] = $this->_pwModeImg.'/banuser.gif';
				}
				$albumdb[] = $value;
			}
		}
		return array($total,$albumdb);	
		
	}
	
	/**
	 * 列表获取的限制条件 $priacy
	 *@return Array 返回相册列表限制条件
	 */
	function _getAlbumBrowseListPriacy() {
		$priacy = array();
		$friendService = $this->_getServiceFactory('Friend', 'friend');
		if(!$this->isSelf() && !$this->isPermission()){	
			$priacy = array_merge($priacy,array(0,3));
			$isFriend = $friendService->isFriend($this->_winduid,$this->_uid);
			if ($isFriend !== "null") $priacy = array_merge($priacy,array(1));
		}
		return $priacy;
	}
	/**
	 *取得朋友相册列表
	 *@return Array 返回朋友相册列表
	 */
	function getFriendAlbumsList(){
		$friendService = $this->_getServiceFactory('Friend', 'friend');
		$friendInfo = $friendService->getFriendInfoByUid($this->_uid);
		if(empty($friendInfo)){
			return array();
		}
		$priacy = array();
		if(!$this->isPermission()){
			$priacy = array_merge($priacy,array(0,1,3));		
		}
		$friend = $ouserData = array();
		foreach($friendInfo as $key => $value){
			$friend[$value['friendid']] = $value;
		}
		$ouserDao = $this->_getDaoFactory('Ouserdata', 'sns');
		$ouserData = $ouserDao->findUserPhotoPrivacy(array_keys($friend));
		foreach($ouserData as $key=>$value){
			if($value['photos_privacy'] == 2 && $friend[$value['uid']]){
				unset($friend[$value['uid']]);
			}
		}
		if(empty($friend)){
			return array();
		}
		$albumDao = $this->_getDaoFactory('CnAlbum');
		$result =  $albumDao->getAlbumsNumByUids(array_keys($friend),0,$priacy);
		$total = $result['total'];
		$albumdb =  array();
		if($total){
			$pageCount = ceil($total / $this->_perpage);
			$this->_page = validatePage($this->_page,$pageCount);
			$albumList = $albumDao->getAlbumsByUids(array_keys($friend),$this->_page,$this->_perpage,0,$priacy);
			foreach($albumList as $key => $value){
				$value['sub_aname']  = substrs($value['aname'],18);
				$value['lasttime']	 = get_date($value['lasttime']);
				$value['lastphoto']	 = getphotourl($value['lastphoto']);
				$ownerid = $value['ownerid'];
				if ($this->_dbshield && $friend[$ownerid]['groupid'] == 6 && !$this->isPermission()) {
					$value['lastphoto'] = $this->_pwModeImg.'/banuser.gif';
				}
				$albumdb[] = $value;
			}
		}
		
		return array($total,$albumdb);	
	}
	/**
	 *取得相册列表
	 *@param $atype int   相册类型(0表示个人相册,1表示群组相册)
	 *@param $priacy Array 相册浏览权限
	 *@return Array 返回相册列表
	 */
	function getAlbumList($atype = 0,$priacy = array()){
		$albumDao = $this->_getDaoFactory('CnAlbum');
		$albumList = $albumDao->getAlbumsByUid($this->_uid,$atype,$priacy);
		return $albumList;
	}
	function updateAlbumInfo($aid,$data){
		if((!$this->isPermission && !$this->isSelf()) || empty($data) || !is_array($data) || intval($aid) <= 0){
			return false;
		}
		$albumDao = $this->_getDaoFactory('CnAlbum');
		$albumDao->update($data,$aid);
		return true;
	}
	/**
	 *删除相册
	 *@param $aid int 相册ID
	 */
	function delAlbum($aid){
		if((!$this->isDelRight() && !$this->isSelf()) || intval($aid) <= 0){
			return array();
		}
		$photoDao = $this->_getDaoFactory('CnPhoto');
		$albumDao = $this->_getDaoFactory('CnAlbum');
		$photoList = $photoDao->getPhotosInfoByAid($aid);
		if(!empty($photoList)){
			$affected_rows = 0;
			foreach($photoList as $key => $value){
				pwDelatt($value['path'], $this->_dbifftp);
				if ($value['ifthumb']) {
					$lastpos = strrpos($value['path'],'/') + 1;
					pwDelatt(substr($value['path'], 0, $lastpos) . 's_' . substr($value['path'], $lastpos), $this->_dbifftp);
				}
				$affected_rows += delAppAction('photo',$value['pid'])+1;//TODO 效率？
			}
			pwFtpClose($ftp);
			countPosts("-$affected_rows");
		}
		$photoDao->delPhotosByAid($aid);
		$albumDao->delete($aid);		
	}
	/**
	 *取得相册相关信息
	 *@param $aid int 相册ID
	 *@param $atype int 相册类型 0表示个人相册 1表示群组相册
	 *@return Array 返回相册相关信息
	 */
	function getAlbumInfo($aid,$atype=0){
		$albumDao = $this->_getDaoFactory('CnAlbum');
		return $albumDao->getAlbumInfo($aid,$atype);
	}
	/**
	 *相册浏览权限
	 *@param $aid int 相册ID
	 *@return Array 返回相册相关信息
	 */
	function albumViewRight($aid){
		$albumDao = $this->_getDaoFactory('CnAlbum');
		$album = $albumDao->getAlbumInfo($aid,0);
		if(empty($album)){
			return 'data_error';
		}
		$ownerid = $album['ownerid'];
		/*if(!$this->isUserAlbum($ownerid)){
			return 'mode_o_photos_private_0';
		}*/
		$friendService = $this->_getServiceFactory('Friend', 'friend');
		if (!$this->isMyAlbum($ownerid) && $album['private'] == 1 && $friendService->isFriend($this->_winduid,$ownerid) !== true && !$this->isPermission()) {
			return 'mode_o_photos_private_1';
		}
		if (!$this->isMyAlbum($ownerid) && $album['private'] == 2 && !$this->isPermission()) {
			return 'mode_o_photos_private_2';
		}
		$cookiename = 'albumview_'.$aid;
		if (($album['albumpwd'] && PwdCode($album['albumpwd']) != GetCookie($cookiename)) && !$this->isMyAlbum($ownerid) && $album['private'] == 3 && !$this->isPermission()) {
			//GetCookie($cookiename) && Cookie($cookiename,'',time()-3600);
			return 'mode_o_photos_private_3';
		}
		return $album;
	}
	
	
	function getSort($sort,$field){
		if(!is_array($sort)){
			return array();
		}
		if(!isset($sort[0][$field])){
			return array();
		}
		$count = count($sort);
		for($i=$count;$i>0;$i--){
			for($j=0;$j<$i;$j++){
				if($sort[$j+1][$field] < $sort[$j][$field]){
					$tmp = $sort[$j];
					$sort[$j] = $sort[$j+1];
					$sort[$j+1] = $tmp;
					
				}
			}
		}
		return $sort;
		
	}
	
	/**************************相片相关操作****************************/
	
	/**
	 * 上传相片
	 * @param $aid int 相册表ID
	 * @return Array 上传相片相关信息
	 **/
	function uploadPhoto($aid){
		if((!$this->isPermission && !$this->isSelf())){
			return 'colony_phototype';
		}
		$albumDao = $this->_getDaoFactory('CnAlbum');
		$photoDao = $this->_getDaoFactory('CnPhoto');
		if (!$aid) {
			$albumcheck = $albumDao->getAlbumNumByUid($this->_uid,0);
			if (empty($albumcheck)) {
				return 'colony_albumclass';
			} else {
				$userInfo = $this->getUserInfoByUid();
				$data = array(
						'aname'		=> getLangInfo('app','defaultalbum'),	
						'atype'		=> 0,
						'ownerid'	=> $this->_uid,		
						'owner'		=> $this->_windid,
						'lasttime'	=> $this->_timestamp,	
						'crtime'	=> $this->_timestamp,
					);			
				$aid = $albumDao->insert($data);
			}
		}
		if(!$aid){
			return 'colony_albumclass';
		}
		$albumInfo = $albumDao->getAlbumInfo($aid,0);
		if(empty($albumInfo)){
			return 'undefined_action';
		}
		$uploadNum = 0;
		foreach($_FILES as $k=>$v){
			(isset($v['name']) && $v['name'] != "") && $uploadNum++;
		}
		//* include_once pwCache::getPath(D_P.'data/bbscache/o_config.php');
		extract(pwCache::getData(D_P.'data/bbscache/o_config.php', false));
		if($o_maxphotonum && ($albumInfo['photonum']+$uploadNum) > $o_maxphotonum ){
			return 'colony_photofull';
		}
		L::loadClass('photoupload', 'upload', false);
		$img = new PhotoUpload($aid);
		PwUpload::upload($img);
		pwFtpClose($ftp);

		if (!$photos = $img->getAttachs()) {
			return 'colony_uploadnull';
		}
		$photoNum = count($photos);
		$pid = $img->getNewID();
	
		if (empty($albumInfo['lastphoto'])) {
			$albumDao->update(array('lastphoto' => $img->getLastPhotoThumb()), $aid);
		}
		return array($albumInfo,$pid,$photoNum,$photos);		
	}
	
	/**
	 *取得下一张相片
	 *@param $pid int 相片表ID
	 *@param $aid int 相册表ID
	 *@return Array 返回下一张相片相关信息
	 */
	function getNextPhoto($pid){
		$photoDao = $this->_getDaoFactory('CnPhoto');
		$next_photo = $photoDao->getNextPhoto($pid,$aid);
		return "ok\t".$next_photo['pid'];	
	}
	/**
	 *取得上一张相片
	 *@param $pid int 相片表ID
	 *@param $aid int 相册表ID
	 *@return Array 返回上一张相片相关信息
	 */
	function getPrevPhoto($pid){
		$photoDao = $this->_getDaoFactory('CnPhoto');
		$prev_photo = $photoDao->getPrevPhoto($pid);
		return "ok\t".$prev_photo['pid'];
	}
	/**
	 * 更新相片相关信息
	 * @param $pid int 相片表ID
	 * @param $aid int 相册表ID
	 * @param $srcPhoto Array 相片原始信息
	 * @param $dstPhoto Array 相片目标信息
	 * @return boolean 更新是否成功
	 */
	function updatePhoto($pid,$aid,$srcPhoto,$dstPhoto){
		if((!$this->isPermission && !$this->isSelf()) || intval($pid) <= 0 || intval($aid) <= 0){
			return false;
		}
		if(!is_array($srcPhoto) || !is_array($dstPhoto) || empty($dstPhoto) || empty($srcPhoto)){
			return false;
		}
		$albumDao = $this->_getDaoFactory('CnAlbum');
		$photoDao = $this->_getDaoFactory('CnPhoto');
		$ischage = false;
		if ($aid != $srcPhoto['aid'] && ($this->isPermission() || $this->isSelf())) {
			$dstPhoto['aid'] = $aid;
			$ischage = true;
		}
		$photoDao->update($dstPhoto,$pid);
		if($ischage){
			$phnum = array();
			$list = $photoDao->getPhotoNumsGroupByAid(array($aid,$srcPhoto['aid']));
			foreach($list as $key => $value){
				$phnum[$value['aid']] = $value['sum'];
			}
			$srcPhoto['aid'] or	$srcPhoto = $this->getPhotoInfo($pid);
			$srcAlbum = $this->getAlbumInfo($srcPhoto['aid']);
			if (empty($srcAlbum['lastphoto']) || ($srcPhoto['lastphoto']?$srcPhoto['lastphoto']:$srcPhoto['path']) == $srcAlbum['lastphoto']) {
				$result = $photoDao->getPhotosInfoByAid($srcPhoto['aid'],1,1);
				$lastphoto = $this->getPhotoThumb($result[0]['path'],$result[0]['ifthumb']);
			}
			$srcAlbumPhotoNum = $phnum[$srcPhoto['aid']] ? $phnum[$srcPhoto['aid']] : 0;
			$dstAlbumPhotoNum = $phnum[$aid] ? $phnum[$aid] : 0;
			$albumDao->update(array('photonum'=>$dstAlbumPhotoNum),$aid);
			$srcAlbumData = array('photonum'=>$srcAlbumPhotoNum);
			$lastphoto && $srcAlbumData['lastphoto'] = $lastphoto;
			$srcAlbumPhotoNum or $srcAlbumData['lastphoto'] = '';
			$albumDao->update($srcAlbumData,$srcPhoto['aid']);		
			$dstAlbumPhotoNum == 1 && $this->setCover($pid);
		}
		return true;
	}
	/**
	 * 删除相片
	 *@param $pid int 相片ID
	 *@return Array 返回相片相关信息
	 */
	function delPhoto($pid) {
		if (intval($pid) <= 0) {
			return array();
		}
		$albumDao = $this->_getDaoFactory('CnAlbum');
		$photoDao = $this->_getDaoFactory('CnPhoto');
		$photo = $photoDao->getPhotoUnionInfoByPid($pid);

		if (empty($photo) || ($photo['ownerid'] != $GLOBALS['winduid'] && !$this->isDelRight())) {
			return array();
		}
		$photoDao->delete($pid);
		
		$thumbPath = $this->getPhotoThumb($photo['path'],$photo['ifthumb']);
		$photoPath = $this->getPhotoThumb($photo['path'],0);
		
		if (empty($photo['lastphoto']) || $thumbPath == $photo['lastphoto'] || $photoPath == $photo['lastphoto']) {	
			$result = $photoDao->getPhotosInfoByAid($photo['aid'],1,1);
			$data['lastphoto'] = $this->getPhotoThumb($result[0]['path'],$result[0]['ifthumb']);
		}
		$data['photonum'] = intval($photo['photonum'])-1;
		$albumDao->update($data,$photo['aid']);
		pwDelatt($photo['path'], $this->_dbifftp);
	//	if($photo['ifthumb']){
		pwDelatt($thumbPath, $this->_dbifftp);
	//		pwDelatt($path, $this->_dbifftp);
	//	}
		pwFtpClose($ftp);
		$photo['uid'] = $this->_uid;
		return $photo;
	}
	/**
	 *设置相册封面
	 *@param $pid int 相片表ID
	 *@return void
	 */
	function setCover($pid){
		if((!$this->isPermission && !$this->isSelf()) || intval($pid) <= 0){
			return array();
		}
		$albumDao = $this->_getDaoFactory('CnAlbum');
		$photoDao = $this->_getDaoFactory('CnPhoto');
		
		$photo = $photoDao->getPhotoUnionInfoByPid($pid);
		if(empty($photo)){
			return array();
		}
		$data['lastphoto'] = $this->getPhotoThumb($photo['path'],$photo['ifthumb']);
		$albumDao->update($data,$photo['aid']);
		$photo['uid'] = $this->_uid;
		return $photo;
	}
	/**
	 *取得相片和该相片所在相册的相关信息
	 *@param $pid int 返回相册表ID
	 *@return Array 返回照片相关信息 
	 */
	function getPhotoUnionInfo($pid){
		$photoDao = $this->_getDaoFactory('CnPhoto');
		$photo = $photoDao->getPhotoUnionInfoByPid($pid);
		return $photo;
	}
	
	function getPhotoInfo($pid) {
		$photoDao = $this->_getDaoFactory('CnPhoto');
		return $photoDao->get($pid);
	}
	
	/**
	 * 根据相册ID取回相片列表
	 *@param $aid int 相册ID
	 *@return Array 返回相片列表
	 */
	function getPhotoListByAid($aid,$paging=true,$ifthumb = true){
		$album = $this->albumViewRight($aid);
		if(!is_array($album)){
			return $album;
		}
		$ownerid = 	$album['ownerid'];	
		$userInfo = $this->getUserInfoByUid($ownerid);
		$album['lastphoto']	= getphotourl($album['lastphoto']);
		if ($this->_dbshield && $userInfo['groupid'] == 6  && !$this->isPermission()) {
			$album['lastphoto'] = $this->_pwModeImg.'/banuser.gif';
			$album['aintro'] = appShield('ban_album_aintro');
		}
		$cnpho = array();
		if ($album['photonum']) {
			$pageCount = ceil($album['photonum'] / $this->_perpage);
			$this->_page = validatePage($this->_page,$pageCount);
			$photoDao = $this->_getDaoFactory('CnPhoto');
			!$paging && $this->_perpage = $album['photonum'];
			$photoList = $photoDao->getPagePhotosInfoByAid($aid,$this->_page,$this->_perpage,1);
			foreach ($photoList as $key => $value) {
				$value['defaultPath'] = $value['path'];
				$value['path'] = getphotourl($value['path'], $value['ifthumb'] && $ifthumb);
				if ($this->_dbshield && $userInfo['groupid'] == 6 && !$this->isPermission()) {
					$value['path'] = $this->_pwModeImg.'/banuser.gif';
				}
				$value['sub_pintro'] = substrs($value['pintro'],25);
				$value['uptime']	= get_date($value['uptime']);
				$cnpho[] = $value;
			}
		}
		return array($album,$cnpho);
	}
	/**
	 *查看照片
	 *@param $pid int 照片ID
	 *@param $aid int 相册ID
	 *@return Array 照片相关信息
	 */
	function viewPhoto($pid){
		global $attachpath;
		$nearphoto = array();
        $register = array('db_shield'=>$this->_dbshield,"groupid"=>$this->_groupid,"pwModeImg"=>$this->_pwModeImg);
        L::loadClass('showpicture', 'colony', false);
        $sp = new PW_ShowPicture($register);
        list($photo,$nearphoto,$prePid,$nextPid) = $sp->getPictures($pid);
        if(empty($photo)){
        	return 'data_error';
        }
		$album = $this->albumViewRight($photo['aid']);
		if(!is_array($album)){
			return $album;
		}
		$photo['privacy'] = $album['privacy'];
		$photo['uptime'] = get_date($photo['uptime']);
		$photo['path'] = getphotourl($photo['basepath']);
		$tmpPath = substr($photo['path'], 0, strlen($attachpath) + 1) == "$attachpath/" ? R_P . $photo['path'] : $photo['path'];
		list($photo['w'],$photo['h']) = getimagesize($tmpPath);
		if ($this->_dbshield && $photo['groupid'] == 6  && !$this->isPermission()) {
			$photo['path'] = $this->_pwModeImg.'/banuser.gif';
			$photo['pintro'] = appShield('ban_photo_pintro');
		}
		$photoDao = $this->_getDaoFactory('CnPhoto');
		$data['hits'] = intval($photo['hits'])+1;
		$photoDao->update($data,$pid);
		return array($photo,$nearphoto,$prePid,$nextPid);		
	}
	/**
	 * 取得用户相关信息
	 * @param $uid int 用户ID
	 * @return Array 返回用户信息
	 */
	function getUserInfoByUid($uid = 0){
		$userInfo = array();
		$uid = $uid ? $uid : $this->_uid;
		if($this->isSelf() && !$uid){
			$userInfo['groupid'] = $this->_groupid;
			$userInfo['uid'] = $this->_winduid;
			$userInfo['username'] = $this->_windid;
		}else{
			$userService = $this->_getServiceFactory('UserService', 'user'); /* @var $userService PW_UserService */
			$userinfo = $userService->get($uid);
		}
		return $userInfo;	
	}
	
	function getPhotoThumb($path,$ifthumb){
		$thumbpath = '';
		$lastpos = strrpos($path,'/') + 1;
		if($ifthumb){
			$thumbpath = substr($path, 0, $lastpos) . 's_' . substr($path, $lastpos);
		}else{
			$thumbpath = $path;
		}
		return $thumbpath;
	}
	
	function getAlbumAidsByUids($uids) {
		if(!$uids || !is_array($uids)) return false;
		$albumList = $result = array();
		$albumDao = $this->_getDaoFactory('Cnalbum'); /* @var $albumDao PW_CnalbumDB */
		$albumList = $albumDao->getAlbumByUids($uids);
		foreach ($albumList as $album) {
			$result[] = $album['aid'];
		}
		return $result;	
	}
	
	function delAlbumByUids($uids) {
		if(!$uids || !is_array($uids)) return false;
		$aids = array();
		$aids = $this->getAlbumAidsByUids($uids);
		foreach($aids as $aid) {
			$this->delAlbum($aid);
		}
		return true;
	}
}
