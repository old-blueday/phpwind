<?php
!defined('P_W') && exit('Forbidden');
/**
 * 附件服务层
 * @author liuhui @2010-4-27
 * @version phpwind 8.0
 */
class PW_Attachs {
	
	function insert($fieldsData) {
		if (!S::isArray($fieldsData)) return false;
		$attachsDao = $this->getAttachsDao();
		return $attachsDao->insert($fieldsData);
	}
	
	function updateByAid($aid,$fieldsData) {
		$aid = intval($aid);
		if ($aid < 1 || !S::isArray($fieldsData)) return false;
		$attachsDao = $this->getAttachsDao();
		return $attachsDao->updateById($aid,$fieldsData);
	}
	
	function countMultiUpload($userId){
		$userId = intval($userId);
		if( $userId < 1 ){
			return false;
		}
		$attachsDao = $this->getAttachsDao();
		return $attachsDao->countMultiUpload($userId);
	}
	
	function countTopicImagesByTid($tid) {
		$attachsDao = $this->getAttachsDao();
		return (int)$attachsDao->countTopicImagesByTid($tid);
	}
	
	function countThreadImagesByTidUid($tid,$uid) {
		$tid = intval($tid);
		$uid = intval($uid);
		if($tid < 1 || $uid < 1) return false;
		$attachsDao = $this->getAttachsDao();
		return $attachsDao->countThreadImagesByTidUid($tid,$uid);
	}
	
	function getUidByTidPidType($tid,$pid = 0 ,$type = 'img') {
		$tid = intval($tid);
		$pid = intval($pid);
		if($tid < 1) return false;
		$attachsDao = $this->getAttachsDao();
		return $attachsDao->getUidByTidPidType($tid,$pid,$type);
	}
	function getDiaryAttachsBydid($id) {
		if(!$id) return false;
		$attachsDao = $this->getAttachsDao();
		return $attachsDao->getDiaryAttachsBydid($id);
	}
	
	/**
	 * 根据发布时间获取版块图酷帖
	 * @param int $fid
	 * @param int $startTime
	 * @param int $endTime
	 * return array
	 */
	function getTuCool($fid,$tucoolPic,$startTime,$endTime,$offset,$size=10){
		$startTime = intval($startTime);
		$endTime = intval($endTime);
		$offset = intval($offset);
		$fid = intval($fid);
		$tucoolPic = intval($tucoolPic);
		if(!$fid || !$startTime || !$endTime || $offset < 0 || !$tucoolPic) return array();
		$foruminfo = L::forum($fid);
		if (!S::isArray($foruminfo)) continue;
		if(!$foruminfo['forumset']['iftucool'] || $foruminfo['forumset']['tucoolpic'] < 1) continue;	
		$attachsDao = $this->getAttachsDao();
		$tmpTids = $attachsDao->getImgs($fid,$tucoolPic,$startTime,$endTime,$offset,$size);
		return array_keys($tmpTids);
	}
	
	/**
	 * 
	 * 计算图酷帖总条数
	 * @param array $fids
	 * @param int $startTime
	 * @param int $endTime 
	 * return int $count
	 */
	function countTuCoolThreadNum($tucoolForums,$startTime,$endTime){
		$startTime = intval($startTime);
		$endTime = intval($endTime);
		if(!$tucoolForums || !$startTime || !$endTime) return array();
		//$foruminfo = array();
		foreach($tucoolForums as $fid=>$forumset){
			//$foruminfo = L::forum($fid);
			//if (!S::isArray($foruminfo)) continue;
			//if(!$foruminfo['forumset']['iftucool'] || $foruminfo['forumset']['tucoolpic'] < 1) continue;
			if ($forumset['tucoolpic'] < 1) continue;
			$attachsDao = $this->getAttachsDao();
			$count += $attachsDao->countTuCoolThreadNum($fid,$startTime,$endTime,$forumset['tucoolpic']);	
		}	
		return $count;
	}
	
	/**
	 * 
	 * 重新生成历史缩略图
	 * @param int $tid 
	 * return bool 
	 */
	function reBuildAttachs($tid){
		global $attachdir,$db_ifftp,$db_athumbtype;
		if ($db_ifftp) return false;
		require_once (R_P . 'require/imgfunc.php');
		$tid = intval($tid);
		if($tid < 1) return false;
		$attachsDao = $this->getAttachsDao();
		$yuanPics = $attachsDao->getImgsByTid($tid);
		if(!$yuanPics) return false;
		foreach ($yuanPics as $v){
			$targtImg = $attachdir ."/thumb/mini/".$v['attachurl'];
			$srcfile = $attachdir . '/' . $v['attachurl'];
			$this->createFolder(dirname($targtImg));
			if(!file_exists($srcfile)) continue;
			MakeThumb($srcfile, $targtImg, 200, 150,$db_athumbtype);
		}
	}

	function createFolder($path) {
		if (!is_dir($path)) {
			PW_Attachs::createFolder(dirname($path));
			@mkdir($path);
			@chmod($path, 0777);
			@fclose(@fopen($path . '/index.html', 'w'));
			@chmod($path . '/index.html', 0777);
		}
	}
	
	function getLatestAttachByTidType($tid,$type='img') {
		$tid = intval($tid);
		if ($tid < 1) return false;
		$attachsDao = $this->getAttachsDao();
		return $attachsDao->getLatestAttachByTidType($tid,$type);
	}

	function getLatestAttachInfoByTidType($tid,$type='img') {
		$tid = intval($tid);
		if ($tid < 1) return false;
		$attachsDao = $this->getAttachsDao();
		return $attachsDao->getLatestAttachInfoByTidType($tid,$type);
	}

	function delByids($ids) {
		if(!$ids) return false;
		$attachsDao = $this->getAttachsDao();
		return $attachsDao->delete($ids);
	}
	
	function getByUids($uids) {
		if(!$uids) return false;
		$attachsDao = $this->getAttachsDao();
		return $attachsDao->getByUids($uids);
	}
	
	function getByAid($aid) {
		$aid = intval($aid);
		if($aid < 1) return array();
		$attachsDao = $this->getAttachsDao();
		return $attachsDao->get($aid);
	}
	
	/**
	 * 图酷帖附件
	 * @param $tid 帖子tid
	 * @param $uid 
	 * @return array
	 */
	function getByTidAndUid($tid,$uid) {
		$tid = intval($tid);
		$uid = intval($uid);
		if ($tid < 1 || $uid < 1) return array();
		$attachsDao = $this->getAttachsDao();
		return $attachsDao->getByTidAndUid($tid,$uid);
	}

	function getUnsatisfiedTidsByTopicImageNum($fid,$tpcImageNum){
		$fid = intval($fid);
		$tpcImageNum = intval($tpcImageNum);
		if(!$fid || !$tpcImageNum) return false;
		$attachsDao = $this->getAttachsDao();
		return $attachsDao->getUnsatisfiedTidsByTopicImageNum($fid,$tpcImageNum);
	}
	/**
	 * 幻灯模式图酷帖附件信息
	 * @param $tid 帖子tid
	 * @param $uid 
	 * @return array
	 */
	function getSlidesByTidAndUid($tid,$uid) {
		$tid = intval($tid);
		$uid = intval($uid);
		if ($tid < 1 || $uid < 1) return array();
		$i = 1;
		$attachs = array();
		$tmpAttachs = $this->getByTidAndUid($tid,$uid);
		$countNum = count((array) $tmpAttachs);
		foreach ((array)$tmpAttachs as $v) {
			if ($v['needrvrc']) continue;
			$v[position] = '['.$i . '/' . $countNum.']';
			$v[json] = pwJsonEncode($v);
			$attachs[$v[aid]] = $v;
			$i++;
		}
		return $attachs;
	}
	
	//获取附件小图片地址
	function getThreadAttachUrl($path) {
		global $attachpath, $db_ftpweb, $imgpath;
		if (!$path) return $imgpath . '/imgdel_h200.jpg';
		$picurlpath = $db_ftpweb ? $db_ftpweb : $attachpath;
		$mainPath = $picurlpath . '/thumb/mini/' . $path;
		return file_exists($mainPath) ? $mainPath : $imgpath . '/imgdel_h200.jpg';
	}
	
	function getMiniDir($path, $where) {
		if ($where != 'Local') return false;
		$localMiniUrl = $GLOBALS['attachpath'] . '/thumb/mini/' . $path;
		$localThumbUrl = $GLOBALS['attachpath'] . '/thumb/' . $path;
		$localUrl = $GLOBALS['attachpath'] . '/' . $path;
		$defaultUrl = $GLOBALS['imgpath'] . '/imgdel_h200.jpg';
		if (file_exists($localMiniUrl)) return $localMiniUrl;
		if (file_exists($localThumbUrl)) return $localThumbUrl;
		if (file_exists($localUrl)) return $localUrl;
		return $defaultUrl;
	}
	
	function getFtpMiniDir($path, $where, $ifthumb) {
		if ($where == 'Ftp') return $ifthumb ? $GLOBALS['db_ftpweb'] . '/thumb/mini/' . $path : $GLOBALS['db_ftpweb'] . '/' . $path;
		if (!is_array($GLOBALS['attach_url'])) return $GLOBALS['attach_url'] . '/thumb/mini/' . $path;
		return $GLOBALS['attach_url'][0] . '/thumb/mini/' . $path;
	}
	
	function getThreadAttachMini($path,$ifthumb = null) {
		if (!$path) return $GLOBALS['imgpath'] . '/imgdel_h200.jpg';
		list($relativePath,$where) = geturl($path, 'show', $ifthumb);
		return ($where == 'Local') ? $this->getMiniDir($path, $where) : $this->getFtpMiniDir($path, $where, $ifthumb);
	}

	function isEditAttachRight($aid,$tid) {
		global $isGM,$winduid;
		$aid = intval($aid);
		$tid = intval($tid);
		if ($aid <1 || $tid<1) return false;
		$threadsService = L::loadClass('threads','forum');
		$read = $threadsService->getByThreadId($tid);
		if (!$read ) return false;
		L::loadClass('forum', 'forum', false);
		$pwforum = new PwForum($read['fid']);
		if (!$pwforum->isForum()) return false;
		$isBM = $pwforum->isBM($windid);
		return ($isGM || $isBM || $read['authorid'] == $winduid) ? true : false;
	}
	
	function getAttachsDao(){
		static $sAttachsDao;
		if(!$sAttachsDao){
			$sAttachsDao = L::loadDB('attachs', 'forum');
		}
		return $sAttachsDao;
	}
}