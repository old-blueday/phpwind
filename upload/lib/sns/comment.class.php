<?php
!defined('P_W') && exit('Forbidden');
/**
 * 新鲜事评论SERVICE
 * @package PW_Comment
 * @author  suqian && sky_hold@163.com
 * @access  public
 */
class PW_Comment {

	var $_timestamp = 0;
	var $cid = 0;

	function __construct(){
		global $timestamp;
		$this->_timestamp = $timestamp;
	}

	function PW_Comment(){
		$this->__construct();
	}
	
	/**
	 *发表评论
	 *@param int $uid 用户ID
	 *@param int $mid 消息ID
	 *@param string $content 内容体
	 *@param array $extra 扩展字段
	 *@return boolean 是否发送成功
	 *@access public
	 */
	function comment($uid,$mid,$content,$extra = array()){
		if (!$this->_isLegalId($uid)  || !$this->_isLegalId($mid)  || empty($content)) {
			return 0;
		}
		$weiboService = L::loadClass('weibo', 'sns'); /* @var $weiboService PW_Weibo */
		$weibos = $weiboService->getWeibosByMid($mid);
		if (empty($weibos)) {
			return 0;
		}
		$content = $this->escapeStr($content);
		$extra = array_merge((array)$extra, $this->_analyseContent($uid, $content));
		/*$ruid = array($weibos['uid']);
		if ($extra['refer']) {
			$ruid = array_merge($ruid,array_keys($extra['refer']));
		}*/
		$ruid = $extra['refer'] ? array_keys($extra['refer']) : array($weibos['uid']);
		$blacklist = $this->_actionBlackList($uid,$ruid,$extra);
		if (empty($ruid) || in_array($weibos['uid'],$blacklist)) {
			return 0;
		}
		$comment = array();
		$comment['uid'] = $uid;
		$comment['mid'] = $mid;
		$comment['content'] = $content;
		$comment['postdate'] = $this->_timestamp;
		$comment['extra'] = $extra ? serialize($extra) : '';
		$commentDao = L::loadDB('weibo_comment','sns');
		$cid = $commentDao->insert($comment);
		if (empty($cid)) {
			return 0;
		}
		$this->cid = $cid;
		$data = $this->_getCmRelationsData($cid,$ruid);
		$this->_addCmRelations($data);
		$userService = L::loadClass('UserService', 'user');
		if($uid !== $weibos['uid']) $userService->updatesByIncrement($ruid, array(), array('newcomment' => 1));

		//platform weibo app
		$siteBindService = L::loadClass('WeiboSiteBindService', 'sns/weibotoplatform/service'); /* @var $siteBindService PW_WeiboSiteBindService */
		if ($siteBindService->isOpen() && !$extra['noSync']) {
			$userBindService = L::loadClass('WeiboUserBindService', 'sns/weibotoplatform/service'); /* @var $userBindService PW_WeiboUserBindService */
			if ($userBindService->isBindOne($uid)) {
				unset($comment['extra']);
				$syncer = L::loadClass('WeiboSyncer', 'sns/weibotoplatform'); /* @var $syncer PW_WeiboSyncer */
				$syncer->sendComment($cid, $comment);
			}
		}
		
		return $cid;
	}
	
	/**
	 * 黑名单处理
	 *@param int $uid 评论者ID
	 *@param array $ruid 屏蔽我的用户ID
	 *@param array $extra 对评论内容@我的过滤
	 *@return array 返回设置我为黑名单的用户
	 *@access private
	 */
	function _actionBlackList($uid,&$ruid,&$extra){
		$ruid = array_unique($ruid);
		$attentionService = L::loadClass('Attention', 'friend');/* @var $attentionService PW_Attention */
		$blackList = $attentionService->getBlackListToMe($uid, $ruid);
		foreach($ruid as $key => $value){
			if(in_array($value,$blackList)){
				unset($ruid[$key]);
				if($extra['refer'][$value]){
					unset($extra['refer'][$value]);
				}
			}
		}
		return $blackList;
	}
	
	/**
	 * 评论发布验证
	 * @param string $content 验证内容
	 */
	function commentCheck($content) {
		if ($GLOBALS['groupid'] == '6') return '你已被禁言!';
		$content = $this->escapeStr($content);
		if (!$content) return '评论内容不为空';
		if (strlen($content) > 255) return '评论内容不能多于255字节';
		$filterService = L::loadClass('FilterUtil', 'filter');
		if (($GLOBALS['banword'] = $filterService->comprise($content)) !== false) {
			return 'content_wordsfb';
		}
		return true;
	}
	
	function escapeStr($str) {
		if (!$str = trim($str)) return '';
		return preg_replace('/(&nbsp;){1,}/', ' ', $str);
	}
	
	/**
	 * 取得新鲜事对应的评论列表
	 * @param int $mid 消息ID
	 * @param int $perpage 页记录数
	 * @param int $page 页数
	 * @return array
	 * @access public
	 */
	function getCommentsByMid($mid,$page = 1,$perpage = 20){
		$commentDao = L::loadDB('weibo_comment','sns');
		$comments = $commentDao->getCommentsByMid($mid,$page,$perpage);
		return $this->_buildData($comments,false);
	}
	
	function getCommentsCountByMid($mid){
		$commentDao = L::loadDB('weibo_comment','sns');
		return $commentDao->getCommentsCountByMid($mid);
	}
	
	/**
	 * 取得用户收到的评论
	 * @param int $uid 用户有ID
	 * @param int $perpage 页记录数
	 * @param int $page 页数
	 * @return array
	 * @access public
	 */
	function getUserReceiveComments($uid,$page = 1,$perpage = 20){
		$commentDao = L::loadDB('weibo_comment','sns');
		$comments = $commentDao->getUserReceiveComments($uid,$page,$perpage);
		return $this->_buildData($comments,true);
	}
	
	function getUserReceiveCommentsCount($uid){
		$commentDao = L::loadDB('weibo_comment','sns');
		return $comments = $commentDao->getUserReceiveCommentsCount($uid);
	}
	
	/**
	 *  取得用户新鲜事评论的回复
	 *@param int $uid  用户UID
	 *@param int $mid  新鲜事ID
	 *@param int $cuid 回复者UID
	 *@param int $page
	 *@param int $perpage
	 *@return array
	 */
	function getUserCommentOfRelpays($uid,$mid,$cuid,$page = 1,$perpage = 20 ){
		$commentDao = L::loadDB('weibo_comment','sns');
		$comments = $commentDao->getUserCommentOfRelpays($uid,$mid,$cuid,$page,$perpage);
		return $this->_buildData($comments,false);
	}
	
	function getUserCommentOfRelpaysCount($uid,$mid,$cuid){
		$commentDao = L::loadDB('weibo_comment','sns');
		return $commentDao->getUserCommentOfRelpaysCount($uid,$mid,$cuid);
	}
	/**
	 * 取得用户发送的评论
	 * @param int $uid 用户有ID
	 * @param int $perpage 页记录数
	 * @param int $page 页数
	 * @return array
	 * @access public
	 */
	function getUserSendComments($uid,$page = 1,$perpage = 20){
		$commentDao = L::loadDB('weibo_comment','sns');
		$comments = $commentDao->getUserSendComments($uid,$page,$perpage);
		return $this->_buildData($comments,true);
	}
	
	/**
	 * 删除评论
	 * @param array $cid 评论ID
	 * @return boolean
	 */
	function deleteComment($cids){
		$commentDao = L::loadDB('weibo_comment','sns');
		$commentDao->deleteCommentByCids($cids);
		$cmrelationsDao = L::loadDB('weibo_cmrelations','sns');
		$cmrelationsDao->deleteCmRelationsByCids($cids);
		return true;
	}
	
	function checkCommentAuthor($cid,$uid=0){
		$cid = intval($cid);
		$uid = intval($uid);
		$uid < 1 && $uid = $GLOBALS['winduid'];
		$commentDao = L::loadDB('weibo_comment','sns');
		$comment = $commentDao->get($cid);
		if (!$comment) return false;
		return $comment['uid'] == $uid;
	}
	/**
	 * 删除某新鲜事下面所有的评论
	 * @param int $mid 新鲜事ID
	 * @return boolean
	 */
	function unionDeleteCommentsByMid($mids){
		if(empty($mids)){
			return false;
		}
		$mids = is_array($mids) ? $mids : array($mids);
		$commentDao = L::loadDB('weibo_comment','sns');
		if ($GLOBALS['db']->server_info() > '4') {
			return $commentDao->unionDeleteCommentsByMid($mids);
		}
		$cids = $commentDao->getCidsOfCommentsByMid($mids);
		return $this->deleteComment($cids);
	}
	
	/**
	 * 解除某用户对应的新鲜事的评论关系
	 * @param int $uid 用户ID
	 * @param int $cid 评论ID
	 * @return boolean
	 */
	function removeCmRelation($uid,$cid){
		$cmrelationsDao = L::loadDB('weibo_cmrelations','sns');
		return $cmrelationsDao->removeCmRelation($uid,$cid);
	}
	
	function _getCmRelationsData($cid,$ruid = array()){
		$data = $tmp = array();
		foreach($ruid as  $value){
			$tmp['uid'] = $value;
			$tmp['cid'] = $cid;
			$data[] = $tmp;
		}
		return $data;
	}
	
	function _addCmRelations($data){
		if(!is_array($data) || empty($data)){
			return array();
		}
		$cmrelationsDao = L::loadDB('weibo_cmrelations','sns');
		return $cmrelationsDao->addCmRelations($data);
	}
	
	function _buildData($data,$ifweibo){
		$uids = $mids = $uinfo = $winfo = array();
		foreach ($data as $key => $value) {
			if (!$value['uid']) continue;
			$uids[] = $value['uid'];
			$mids[] = $value['mid'];
		}
		$uinfo = $this->_getUsersInfo($uids);
		if($ifweibo){
			$winfo = $this->_getWeiBosInfo($mids);
		}
		foreach($data as $key => $value){
			if (!$value['uid']) continue;
			$value = $this->_formatRecord($value, $uinfo[$value['uid']]['groupid']);
			if($winfo){
				$value['weibo'] = $winfo[$value['mid']];
			}
			$uinfo[$value['uid']] = $uinfo[$value['uid']] ? $uinfo[$value['uid']] : array();
			$data[$key] = array_merge($value, $uinfo[$value['uid']]);
		}
		return $data;
	}
	
	/**
	 * 取得数据库记录指定的单个记录
	 * @param array $records 记录
	 * @param string $key 指定的记录
	 * @return array
	 * @access private
	 */
	function _getFieldOfRecords($records, $key) {
		$field = array();
		if (!is_array($records)) {
			return array();
		}
		foreach ($records as $rkey => $value) {
			if (isset($value[$key])) {
				$field[] = $value[$key];
			}
		}
		return $field;
	}
	/**
	 * 获取用户信息
	 * @param array  $uids  用户条件数组
	 * @param string $type 类别
	 * return array
	 */
	function _getUsersInfo($uids,$type = 'uid') {
		if (empty($uids) || !is_array($uids)) {
			return array();
		}
		$newUsersInfo = $users =  array();
		$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
		if($type == 'uid'){
			$users = $userService->getByUserIds($uids); //'m.uid','m.username','m.icon','m.groupid'
		}elseif($type == 'username'){
			$users = $userService->getByUserNames($uids);
		}
		foreach ($users as $key => $value) {
			list($value['icon']) = showfacedesign($value['icon'], 1, 'm');
			$newUsersInfo[$value['uid']] = $value;
		}
		return $newUsersInfo;
	}
	/**
	 * 获取新鲜事信息
	 * @param array $mids 用户id数组
	 * return array
	 */
	function _getWeiBosInfo($mids){
		if (empty($mids) || !is_array($mids)) {
			return array();
		}
		$weiboService = L::loadClass('weibo', 'sns'); /* @var $weiboService PW_Weibo */
		$weibos = $weiboService->getWeibosByMid($mids);
		return $weiboService->buildData($weibos,'uid');
		
	}
	
	/**
	 * 取得n天内评论次数最多的新鲜事Id
	 * @param int $num 获取记录条数
	 * @return array
	 */
	function getHotComment($num,$time){
		$time = intval($time);
		$num = intval($num);
		if($time < 0 || $num < 0) return array();
		$commentDao = L::loadDB('weibo_comment','sns');
		return $commentDao->getHotComment($num=20,$time);
	}
	
	/**
	 * 解析内容
	 * @param string $content 评论内容
	 * @param array $extra 扩展信息
	 * return string
	 */
	function _parseContent($content, &$extra) {
	
		if ($extra['refer']) {
			$uArray  = array_flip($extra['refer']);
			$content = preg_replace('/@([^\\&\'"\/\*,<>\r\t\n\s#%?@:：]+)(?=\s?)/ie', "\$this->_parseRefer('\\1', \$uArray)", $content);
		}
		/*
		if ($extra['upload']) {
			$content = preg_replace('/\[upload=(\d+)\]/ie', "\$this->_parseUpload('\\1', \$extra['upload'])", $content);
		}
		*/
		if (strpos($content,'[s:') !== false && strpos($content,']') !== false) {
			$content = $this->_parseSmile($content);
		}
		return $content;
	}
	
	/**
	 * 解析表情
	 */
	function _parseSmile($content) {
		$sParse = L::loadClass('smileparser', 'smile');
		return $sParse->parse($content);
	}
	

	/**
	 * 解析内容中@功能(评论)
	 * @param string $username 用户名
	 * @param array @列表
	 * return string
	 */
	function _parseRefer($username, $uArray) {
		return isset($uArray[$username]) ? '<a href="'.USER_URL. $uArray[$username] . '">@' . $username . '</a>' : '@' . $username;
	}
	
	/**
	 * 分析评论内容中的特殊标签
	 * @param int $uid 发送者
	 * @param string $content 新鲜事内容
	 * @return array
	 */
	function _analyseContent($uid, $content) {
		$array = array();
		if ($refer = $this->_analyseRefer($uid,$content)) {
			$array['refer'] = $refer;
		}
		return $array;
	}
	
	/**
	 * 分析评论内容中的@功能
	 * @param int $uid 评论发送者
	 * @param string $content 新鲜事内容
	 * @return array
	 */
	function _analyseRefer($uid,$content) {
		preg_match_all('/@([^\\&\'"\/\*,<>\r\t\n\s#%?@:：]+)\s?/i', $content, $matchs);
		$refer = array();
		if ($matchs[1]) {
			$uInfo = $this->_getUsersInfo($matchs[1],'username');
			foreach ($uInfo as $rt) {
				$refer[$rt['uid']] = $rt['username'];
			}
		}
		return $refer;
	}
	
	function _formatRecord($record, $gid){
		list($record['lastdate'], $record['postdate_s']) = getLastDate($record['postdate']);
		$record['extra'] = $record['extra'] ? unserialize($record['extra']) : array();
		if ($gid == '6') {
			$record['content'] = "<span style=\"color:black;background-color:#ffff66\">该内容已被管理员屏蔽！</span>";
		} else {
			$record['content'] = $this->_parseContent($record['content'], $record['extra']);
		}
		return $record;
	}

	function _isLegalId($id){
		return intval($id) > 0;
	}
	
	function adminSearch($usernames,$contents,$startDate,$endDate,$orderby = 'desc',$page = 1,$perpage = 20){
		if($usernames){
			$usernames = is_array($usernames) ? $usernames : array($usernames);
		}
		$uids = array();
		if(is_array($usernames) && count($usernames) > 0){
			$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
			$users = $userService->getByUserNames($usernames);
			$uids = $this->_getFieldOfRecords($users,'uid');
		}
		$startDate && !is_numeric($startDate) && $startDate = PwStrtoTime($startDate);
		$endDate && !is_numeric($endDate) && $endDate = PwStrtoTime($endDate);
		$type = intval($type);
		$commentDao = L::loadDB('weibo_comment','sns');
		$result = $commentDao->adminSearch($uids,$contents,$startDate,$endDate,$orderby,$page,$perpage);
		foreach($result[1] as $key => $value){
			$result[1][$key]['content'] = substr(stripWindCode($value['content']),0,30);
		}
		$weibos = $this->_buildData($result[1]);
		return array($result[0],$weibos);
	}
}
?>