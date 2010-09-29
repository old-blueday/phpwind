<?php
!function_exists('readover') && exit('Forbidden');
/**
 * 搜索引擎基类服务
 * @author liuhui 2010-4-21
 * @version phpwind 8.0
 */
class Search_Base {
	
	var $_timestamp  	= null;
	var $_username   	= null;
	var $_userId     	= null;
	var $_groupId    	= null;
	var $_userGroup  	= null;
	var $_maxResult  	= null;
	var $_waitSegment	= 0;
	var $_isLevel    	= 1; //是否开启权限检查
	var $_version       = true; //php_version
	
	function Search_Base(){
		global $timestamp,$winduid,$windid,$gorupid,$_G,$db_maxresult,$db_schwait;
		$this->_userId    	= &$winduid;
		$this->_username  	= &$windid;
		$this->_groupId   	= &$gorupid;
		$this->_userGroup   = &$_G;
		$this->_waitSegment = &$db_schwait;
		$this->_maxResult 	= ($db_maxresult) ? $db_maxresult : 500;
		$this->_timestamp 	= &$timestamp;
		$this->_version     = (function_exists('str_ireplace')) ? true : false;
	}
	/**
	 * 检查用户搜索权限
	 * @return unknown_type
	 */
	function _checkUserLevel(){
		$userService = $this->_getUserService();
		if( $this->_userGroup['searchtime'] > 0 ){
			return true;
		}
		if (!($memberInfo = $userService->get($this->_userId, false, true))) {
			return false;
		}
		if($this->_timestamp - $memberInfo['lasttime'] < $this->_userGroup['searchtime']){
			return false;
		}
		$userService->update($this->_userId, array(), array(), array('lasttime'=>$this->_timestamp));
		return true;
	}
	/**
	 * 检查用户搜索间隔时间
	 * @return unknown_type
	 */
	function _checkWaitSegment(){
		if(!$this->_waitSegment) return true;
		if (file_exists(D_P.'data/bbscache/schwait_cache.php')) {
			if ($this->_timestamp - pwFilemtime(D_P.'data/bbscache/schwait_cache.php') > $this->_waitSegment) {
				P_unlink(D_P.'data/bbscache/schwait_cache.php');
			} else {
				return false;
			}
		}
		return true;
	}
	/**
	 * 检查关键字查询条件
	 * @param $keyword
	 * @return 关键字数组
	 */
	function _checkKeywordCondition($keyword){
		if( $this->_sphinxlen && strlen($keyword) < 3 ){
			return array();
		}
		$keyword = trim(($keyword));
		$keyword = str_replace(array("&#160;","&#61;","&nbsp;","&#60;","<",">","&gt;","(",")","&#41;"),array(" "),$keyword);
		$ks = explode(" ",$keyword);
		$keywords = array();
		foreach($ks as $v){
			$v = trim($v);
			($v) && $keywords[] = $v;
		}
		if(!$keywords){
			return array();
		}
		$keywords = implode(" ",$keywords);
		return $keywords;
	}
	/**
	 * 检查用户查询条件
	 * @param array $userNames
	 * @return 返回数组 array(uid=>username)
	 */
	function _checkUserCondition($userNames){
		if(!$userNames) return false;
		$userNames = (is_array($userNames)) ? $userNames : array($userNames);

		$userService = $this->_getUserService();
		$users = $userService->getByUserNames($userNames);
		if (!$users) {
			return false;
		}
		
		$tmp = array();
		foreach($users as $user){
			$tmp[$user['uid']] = $user['username'];
		}
		return $tmp;
	}
	/**
	 * 检查时间查询条件
	 * @param int $startTime
	 * @param int $endTime
	 * @return unknown_type
	 */
	function _checkTimeNodeCondition($startTime,$endTime){
		$startTime && $startTime = PwStrtoTime($startTime);
		$endTime   && $endTime   = PwStrtoTime($endTime);
		if($startTime && !$endTime){
			$endTime = $this->_timestamp;
		}
		if($endTime && !$startTime){
			$startTime = 0;
		}
		if(!$startTime && !$endTime){
			$startTime = 0;
			$endTime   = $this->_timestamp;
		}
		return array($startTime,$endTime);
	}
	
	function _checkThreadConditions($keywords,$userNames="",$starttime="",$endtime=""){
		$keywords = $this->_checkKeywordCondition($keywords);
		if(!$keywords) return array(false);
		$users = array();
		if($userNames && !$users = $this->_checkUserCondition($userNames)){
			return array(false);
		}
		list($starttime,$endtime) = $this->_checkTimeNodeCondition($starttime,$endtime);
		return array($keywords,$users,$starttime,$endtime);
	}
	
	function _getThreads($threadIds,$keywords){
		if(!$threadIds) return array();
		$threadsDao = $this->getThreadsDao();
		if(!($result = $threadsDao->getsBythreadIds($threadIds))){
			return array();
		}
		return $this->_buildThreads($result,$keywords);
	}
	/**
	 * 获取最新帖子
	 * @param $page
	 * @param $perpage
	 * @return unknown_type
	 */
	function _getLatestThreads($page,$perpage = 50){
		$offset = ($page - 1) * $perpage;
		$threadsDao = $this->getThreadsDao();
		if(!($result = $threadsDao->getLatestThreads($offset,$perpage))){
			return array();
		}
		return array(count($result),$this->_buildThreads($result,array()));
	}
	/**
	 * 获取精华帖子
	 * @param $page
	 * @param $perpage
	 * @return unknown_type
	 */
	function _getDigestThreads($uid, $page,$perpage = 50){
		$offset = ($page - 1) * $perpage;
		$threadsDao = $this->getThreadsDao();
		if(!($result = $threadsDao->getDigestThreads($uid, array(1,2),$offset,$perpage))){
			return array();
		}
		return array(count($result),$this->_buildThreads($result,array()));
	}
	
	/**
	 * 获取特殊帖子
	 * @param $type
	 * @param $page
	 * @param $perpage
	 * @return unknown_type
	 */
	function getSpecialThreads($type ='latest', $uid, $page=1, $perpage = 50){
		if($type == 'digest'){
			return $this->_getDigestThreads($uid, $page, $perpage);
		}else{
			return $this->_getLatestThreads($page,$perpage);
		}
	}
	
	/**
	 * 组装帖子数据
	 * @param $result
	 * @return unknown_type
	 */
	function _buildThreads($threads,$keywords){
		if(!$threads) return false;
		$keywords = (is_array($keywords)) ? $keywords : explode(" ",$keywords);
		$data = array();
		require_once(R_P.'require/bbscode.php');
		foreach($threads as $t){
			$t['postdate'] = get_date($t['postdate'],"Y-m-d H:i");
			$forum = L::forum($t['fid']);
			$t['content'] = substrs(stripWindCode(strip_tags(convert($t['content'],array()))),200);
			foreach($keywords as $keyword){
				$keyword && $t['subject'] = $this->_highlighting($keyword,$t['subject']);
				$keyword && $t['content'] = $this->_highlighting($keyword,$t['content']);
			}
			$t['subject'] = ($t['subject']) ? $t['subject'] : 'RE:';
			$t['name'] = $forum['name'];
			$data[] = $t;
		}
		return $data;
	}
	
	function _getPosts($postIds,$keywords,$tableName){
		if(!$postIds || !$tableName ) return array();
		$postsDao = $this->getPostsDao();
		
		if(!($result = $postsDao->getsByPostIds($postIds,$tableName))){
			return array();
		}
		return $this->_buildThreads($result,$keywords);
	}
	
	function _buildUsers($users){
		if(!$users) return false;
		$data = array();
		require_once(R_P.'require/showimg.php');
		$genders = array(0=>"保密",1=>"男",2=>"女");
		foreach($users as $t){
			list($t['face'])    = showfacedesign($t['icon'],1);
			$t['gender']        = $genders[$t['gender']];	
			$t['constellation'] = $this->_getConstellation($t['bday']);
			$t['introduce']     = ($t['introduce']) ? $t['introduce'] : '暂无';
			$data[] = $t;
		}
		return $data;
	}
	
	function _buildDiarys($diarys,$keywords){
		if(!$diarys) return false;
		$result = $dtids = array();
		require_once(R_P.'require/bbscode.php');
		foreach($diarys as $t){
			$t['postdate'] = get_date($t['postdate'],"Y-m-d H:i");
			$t['content'] = substrs(strip_tags(convert($t['content'],array())),200);
			foreach($keywords as $keyword){
				$keyword && $t['subject'] = $this->_highlighting($keyword,$t['subject']);
				$keyword && $t['content'] = $this->_highlighting($keyword,$t['content']);
			}
			$dtids[] = $t['dtid'];
			$result[$t['did']]  = $t;
		}
		$diaryTypes = $this->_getDiaryTypes($dtids);
		$tmp = array();
		foreach($result as $diary){
			$diary['type'] = ($diary['dtid'] > 0 && isset($diaryTypes[$diary['dtid']])) ? $diaryTypes[$diary['dtid']] : '默认分类';
			$tmp[] = $diary;
		}
		return $tmp;
	}
	
	function _getDiaryTypes($dtids){
		if(!$dtids) return false;
		$diarytypeDao = $this->getDiaryTypeDao();
		$types = $diarytypeDao->getsByTdids($dtids);
		$tmp = array();
		foreach($types as $t){
			$tmp[$t['dtid']] = $t['name'];
		}
		return $tmp;
	}
	
	function _buildForums($forums,$keywords){
		if(!$forums) return array();
		$result = array();
		$keywords = ($keywords) ? explode(",",$keywords) : array();
		foreach($forums as $t){
			$t['descrip']    = strip_tags($t['descrip']);
			$t['descrip']    = substrs($t['descrip'],200);
			foreach($keywords as $keyword){
				$keyword && $t['name']     = $this->_highlighting($keyword,$t['name']);
				$keyword && $t['descrip']  = $this->_highlighting($keyword,$t['descrip'] );
			}
			$t['forumadmin'] = trim($t['forumadmin'],",");
			$t['logo']       = $this->_getForumLogo($t);
			$result[]        = $t;
		}
		return $result;
	}
	function _getForumLogo($forums){
		global $db_indexfmlogo,$imgdir,$stylepath,$attachpath,$imgpath,$attachdir;
		if ($db_indexfmlogo == 1 && file_exists("$imgdir/$stylepath/forumlogo/$forums[fid].gif")) {
			$forums['logo'] = "$imgpath/$stylepath/forumlogo/$forums[fid].gif";
		} elseif ($db_indexfmlogo == 2) {
			if(!empty($forums['logo']) && strpos($forums['logo'],'http://') === false && file_exists($attachdir.'/'.$forums['logo'])){
				$forums['logo'] = "$attachpath/$forums[logo]";
			}
		} else {
			$forums['logo'] = '';
		}
		return $forums['logo'] ? $forums['logo'] : 'images/search/forum.png';
	}
	
	function _buildGroups($groups,$keywords){
		if(!$groups) return array();
		$result = array();
		$keywords = ($keywords) ? explode(",",$keywords) : array();
		foreach($groups as $group){
			$group['id']         = $group['id'];
			$group['createtime'] = get_date($group['createtime'],"Y-m-d H:i");
			$group['descrip']    = strip_tags($group['descrip']);
			$group['descrip']    = substrs($group['descrip'],200);
			$group['credit']     = $this->_calculateCredit($group);
			$group['sname']      = ($group['sname']) ? $group['sname'] : '末分类';
			foreach($keywords as $keyword){
				$keyword && $group['cname']   = $this->_highlighting($keyword,$group['cname']);
				$keyword && $group['descrip'] = $this->_highlighting($keyword,$group['descrip']);
			}
			if ($group['cnimg']) {
				list($group['cnimg']) = geturl("cn_img/".$group['cnimg'],'lf');
			} else {
				$group['cnimg'] = "images/search/group.png";
			}
			$result[] = $group;
		}
		return $result;
	}
	/**
	 * 注意关联函数 apps/groups/lib/colony.class.php
	 * @param $info
	 */
	function _calculateCredit($info) {
		require_once R_P.'require/functions.php';
		$info['pnum'] -= $info['tnum'];
		return CalculateCredit($info, L::config('o_groups_upgrade','o_config'));
	}
	
	function _highlighting($pattern,$subject){
		//return preg_replace('/(?<=[^\w=]|^)('.preg_quote($pattern,'/').')(?=[^\w=]|$)/si','<font color="red"><u>\\1</u></font>',$subject);
		if($this->_version){
			return str_ireplace($pattern,'<font color="red"><u>'.$pattern.'</u></font>',$subject);
		}else{
			return str_replace($pattern,'<font color="red"><u>'.$pattern.'</u></font>',$subject);
		}
	}
	
	function _checkPage($page,$perpage,$total){
		$totalPages = ceil($total/$perpage);
		return ($page < 0 ) ? 1 : (($page>$totalPages) ? $totalPages : $page);
	}
	
	function _getConstellation($bday) {
		list($y,$month,$day) = explode('-',$bday);
		$signs=array(array("20"=>"水瓶座"),array("19"=>"双鱼座"),array("21"=>"白羊座"),array("20"=>"金牛座"),array("21"=>"双子座"),array("22"=>"巨蟹座"),array("23"=>"狮子座"),array("23"=>"处女座"),array("23"=>"天秤座"),array("24"=>"天蝎座"),array("22"=>"射手座"),array("22"=>"魔蝎座"));
		$k = $month<1 ? 1 : $month-1;
		list($sign_start,$sign_name)=each($signs[$k]);
		if($day<$sign_start) list($sign_start,$sign_name)=each($signs[($month-2<0)?$month=11:$month-=2]);
		return $sign_name;
	}
	
	function _searchForums($keywords,$page=1,$perpage=20){
		if(!($keywords = $this->_checkKeywordCondition($keywords))){
			return array(false,false);
		}
		$page = $page>1 ? $page : 1;
		$offset = intval(($page - 1) * $perpage);
		$forumsDao = $this->getForumsDao();
		if(!($total = $forumsDao->countSearch($keywords))){
			return array(false,false);
		}
		$result = $forumsDao->getSearch($keywords,$offset,$perpage);
		return array($total,$this->_buildForums($result,$keywords));
	}
	
	function _searchGroups($keywords,$page=1,$perpage=20){
		if(!($keywords = $this->_checkKeywordCondition($keywords))){
			return array(false,false);
		}
		$page = $page>1 ? $page : 1;
		$offset = intval(($page - 1) * $perpage);
		$colonysDao = $this->getColonysDao();
		if(!($total = $colonysDao->countSearch($keywords))){
			return array(false,false);
		}
		$result = $colonysDao->getSearch($keywords,$offset,$perpage);
		return array($total,$this->_buildGroups($result,$keywords));
	}
	/**
	 * 日志表DAO
	 * @return unknown_type
	 */
	function getDiarysDao(){
		static $sDiarysDao;
		if(!$sDiarysDao){
			$sDiarysDao = L::loadDB('diary', 'diary');
		}
		return $sDiarysDao;
	}
	/**
	 * 帖子表DAO
	 * @return unknown_type
	 */
	function getThreadsDao(){
		static $sThreadsDao;
		if(!$sThreadsDao){
			$sThreadsDao = L::loadDB('threads', 'forum');
		}
		return $sThreadsDao;
	}
	/**
	 * 回复表DAO
	 * @return unknown_type
	 */
	function getPostsDao(){
		static $sPostsDao;
		if(!$sPostsDao){
			$sPostsDao = L::loadDB('posts', 'forum');
		}
		return $sPostsDao;
	}
	/**
	 * 版块表DAO
	 * @return unknown_type
	 */
	function getForumsDao(){
		static $sForumsDao;
		if(!$sForumsDao){
			$sForumsDao = L::loadDB('forums', 'forum');
		}
		return $sForumsDao;
	}
	/**
	 * 群组表DAO
	 * @return unknown_type
	 */
	function getColonysDao(){
		static $sColonysDao;
		if(!$sColonysDao){
			$sColonysDao = L::loadDB('colonys', 'colony');
		}
		return $sColonysDao;
	}
	/**
	 * 日志分类表DAO
	 * @return unknown_type
	 */
	function getDiaryTypeDao(){
		static $sDiaryTypeDao;
		if(!$sDiaryTypeDao){
			$sDiaryTypeDao = L::loadDB('diarytype', 'diary');
		}
		return $sDiaryTypeDao;
	}
	/**
	 * 搜索缓存表DAO
	 * @return unknown_type
	 */
	function getSchcacheDao(){
		static $sSchcacheDao;
		if(!$sSchcacheDao){
			$sSchcacheDao = L::loadDB('schcache', 'search');
		}
		return $sSchcacheDao;
	}
	
	/**
	 * @return PW_UserService
	 */
	function _getUserService() {
		return L::loadClass('UserService', 'user');
	}
}