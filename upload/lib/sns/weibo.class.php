<?php
!defined('P_W') && exit('Forbidden');
/**
 * 新鲜事SERVICE
 * 
 * @package PW_Weibo
 * @author suqian && sky_hold@163.com
 */
class PW_Weibo {

	var $_map = array();
	var $_mapflip = array();
	var $_mapDescript = array();
	var $_timestamp = 0;

	function __construct(){
		global $timestamp;
		$this->_timestamp = $timestamp;
		$this->_typeMap();
	}

	function PW_Weibo(){
		$this->__construct();
	}

	/**
	 * 验证用户是否设置发表某类型操作(帖子、日志、相册、群组)同时转载到新鲜事
	 * @param int $uid
	 * @param string $type
	 * return bool
	 */
	function checkSendPrivacy($uid, $type) {
		$privacyService = L::loadClass('privacy','sns');
		return $privacyService->getIsFeed($uid, $type);
	}
	/*
	function getAtPrivacyByUserNames($usernames){
		$privacyService = L::loadClass('privacy','sns');
		return $privacyService->getAtFeed($uid);
	}
	*/
	/**
	 * 过滤隐私设置的用户（返回允许被@的用户）
	 * @return array $uids
	 */
	function filterPrivacyAtUsers($usernames,$uid = 0){
		$uid = intval($uid);
		$uid < 1 && $uid = $GLOBALS['winduid'];
		$privacyService = L::loadClass('privacy','sns');
		$attentionService = L::loadClass('attention','friend');
		$checkAttentioned = array();
		$returnUids = array();
		$filterUsers = $privacyService->getAtFeedByUserNames($usernames);
		//黑名单处理
		$blackList = $attentionService->getBlackListToMe($uid,array_keys($filterUsers));
		if (S::isArray($blackList)) {
			foreach ($blackList as $v) {
				unset($filterUsers[$v]);
			}
		}
		foreach($filterUsers as $k=>$v) {
			if ($v['at_isfeed'] == 0){
				//允许所有人@
				$returnUids[$k] = $v['username'];
			} elseif ($v['at_isfeed'] == 1) {
				//关注的人可@
				$checkAttentioned[$k] = $v['username'];
			}
		}
		//check attention
		if ($checkAttentioned) {
			foreach ($checkAttentioned as $k=>$v){
				if ($attentionService->isFollow($k,$uid)){
					$returnUids[$k] = $v;
				} 
			}
		}
		return $returnUids;
	}
	/**
	 * 查看用户空间及相关应用隐私
	 * @param int $uid
	 * @param string $type 应用类型
	 * return bool
	 */
	function checkUserSpacePrivacy($uid, $type = null) {
		$privacyService = L::loadClass('privacy','sns');
		return $privacyService->getIsPriacy($uid, $type);
	}
	
	/**
	 * 新鲜事发布验证
	 * @param str $content 验证内容
	 * @param int $groupid 验证用户组
	 * @param boolean $ifempty 判断内容是否为空
	 */
	function sendCheck($content, $groupid,$ifempty = false) {
		if ($groupid == '6') return '你已被禁言!';
		if (!$this->groupCheck($groupid)) return 'weibo_group_right';
		$content = $this->escapeStr($content);
		if (!$content && empty($ifempty)) return '新鲜事内容不为空';
		if (strlen($content) > 255) return '新鲜事内容不能多于255字节';
		$filterService = L::loadClass('FilterUtil', 'filter');
		//过滤笑脸
		$smileService = L::loadClass('smile','smile');
		$tmpSmiles = $smileTags = array();
		$tmpSmiles = $smileService->findByType();
		foreach ($tmpSmiles as $v) {
			$smileTags[] = $v['tag'];
		}
		$content = str_ireplace($smileTags, '', $content);
		if (($GLOBALS['banword'] = $filterService->comprise($content)) !== false) {
			return 'content_wordsfb';
		}
		return true;
	}

	function groupCheck($groupid) {
		global $o_weibo_groups;
		return ($groupid == 3 || empty($o_weibo_groups) || strpos($o_weibo_groups,",$groupid,") !== false);
	}
	 
	 function checkReplyRight($tid) {
	 	global $isGM,$winddb,$isBM;
	 	$threadService = L::loadClass('threads', 'forum');
	 	L::loadClass('forum', 'forum', false);
	 	$read = $threadService->getByThreadId($tid);
	 	$pwforum = new PwForum($read['fid']);	
	 	$forumset =& $pwforum->forumset;
	 	if (getstatus($read['tpcstatus'], 7)) {
			$robbuildService = L::loadClass('RobBuild', 'forum'); /* @var $robbuildService PW_RobBuild */
			$robbuild = $robbuildService->getByTid($tid);
			if ($robbuild['starttime'] > $this->_timestamp) return false;
		}
	 	$tpc_locked = $read['locked']%3<>0 ? 1 : 0;
	 	$admincheck = ($isGM || $isBM) ? 1 : 0;
	 	$isAuthStatus = $admincheck || (!$forumset['auth_allowrp'] || $pwforum->authStatus($winddb['userstatus'],$forumset['auth_logicalmethod']) === true);
	 	if ($isAuthStatus && (!$tpc_locked || $SYSTEM['replylock']) && ($admincheck || $pwforum->allowreply($winddb, $groupid))) {
	 		return true;
	 	}
	 	return false;
	 }

	function escapeStr($str) {
		if (!$str = trim($str)) return '';
		$tmp = preg_replace('/(&nbsp;){1,}/', ' ', $str);
		return preg_replace_callback('/#([^#]+)#/', array(&$this,'_callbackTrimTopicStr'), $tmp);
	}

	function _callbackTrimTopicStr($matches){
		return '#'.trim($matches[1]).'#';
	}
	/**
	 * 发送新鲜事
	 * @param int $uid 发送者
	 * @param string $content 新鲜事消息内容
	 * @param string $type 发送新鲜事类别
	 * @param string $typeid 发送新鲜事类别ID
	 * @param array  $extra 扩展字段
	 * @return boolean
	 * @access public
	 */
	function send($uid, $content, $type = 'weibo' ,$typeid = 0, $extra = array()) {
		if (!isset($this->_map[$type]) || !$this->_isLegalId($uid)) {
			return 0;
		}

		if ($this->_map[$type] > 9 && !$this->checkSendPrivacy($uid, $this->_privacyMapping($type))) {
			return 0;
		}
		$fromThread = $extra['fid'] && $extra['title'];
		if ($fromThread && $extra['atusers']) {
			$extra['atusers'] = $this->filterPrivacyAtUsers($extra['atusers']);
		}
		//无@用户,回复不产生新鲜事
		if (!$extra['atusers'] && $extra['pid']) return 0;
		$content = $this->escapeStr($content);
		$extra = $fromThread ? (array)$extra : array_merge((array)$extra, $this->_analyseContent($uid, $content));
		$message = array(
			'uid' => $uid,			'content' => $content,
			'postdate' => $this->_timestamp,
			'type' => $this->_map[$type],
			'objectid' => intval($typeid),
			'contenttype' => isset($extra['photos']) ? 1 : 0,
			'extra' => $extra ? addslashes(serialize($extra)) : ''
		);

		$contentDao = L::loadDB('weibo_content','sns');
		if (!$mid = $contentDao->insert($message)) {
			return 0;
		}
		$this->_addRelation($uid, $mid, $type);

		if ($fromThread && $extra['atusers']) {
			$extra['atusers'] && $this->addRefer(array_keys($extra['atusers']), $mid);
		} elseif ($extra['refer']) {
			$extra['refer'] = $this->filterPrivacyAtUsers($extra['refer']);
			$this->addRefer(array_keys($extra['refer']), $mid);
		}
		
		if ($extra['cyid']) {
			$this->_addCnRelation($extra['cyid'], $mid);
		}
		if ($extra['topics']) {
			$this->addTopics($extra['topics'],$mid);
		}
		$userCache = L::loadClass('Usercache', 'user');
		$userCache->delete($uid, 'weibo');

		//platform weibo app
		$siteBindService = L::loadClass('WeiboSiteBindService', 'sns/weibotoplatform/service'); /* @var $siteBindService PW_WeiboSiteBindService */
		if ($siteBindService->isOpen() && !$siteBindService->isBind($type) && !$extra['noSync']) {
			$userBindService = L::loadClass('WeiboUserBindService', 'sns/weibotoplatform/service'); /* @var $userBindService PW_WeiboUserBindService */
			if ($userBindService->isBindOne($uid)) {
				unset($message['extra']);
				$syncer = L::loadClass('WeiboSyncer', 'sns/weibotoplatform'); /* @var $syncer PW_WeiboSyncer */
				$syncer->send($mid, $type, $message, $extra);
			}
		}

		return $mid;
	}
	
	/**
	 * 分析新鲜事内容中的特殊标签
	 * @param int $uid 发送者
	 * @param string $content 新鲜事内容
	 * @return array
	 */
	function _analyseContent($uid, $content) {
		$array = array();
		if ($refer = $this->_analyseRefer($uid, $content)) {
			$array['refer'] = $refer;
		}
		if ($topics = $this->_analyseTopics($content)) {
			$array['topics'] = $topics;
		}
		return $array;
	}

	/**
	 * 分析新鲜事内容中的#话题#
	 * @param string $content 新鲜事内容
	 * @return array $topics
	 */
	function _analyseTopics($content) {
		$topics = array();
		//preg_match_all('/#([^#]+)#/U',$content,$matches) && $topics = $matches[1];
		preg_match_all('/#([^@&#!*\(\)]+)#/U',$content,$matches) && $topics = $matches[1];
		foreach ($topics as $k=>$v) {
			$v = trim($v);
			//话题内不允许含链接
			if(preg_match("/(https?|ftp|gopher|news|telnet|mms|rtsp):\/\/[a-z0-9\/\-_+=.~!%@?%&;:$\\│\|]+(#.+)?/ie", $v)) continue;
			if(!isset($v)) {
				unset($topics[$k]);
				continue;
			}
			$topics[$k] = $v;
		}
		return $topics;
	}
	
	/**
	 * 分析新鲜事内容中的@功能
	 * @param int $uid 新鲜事发送者
	 * @param string $content 新鲜事内容
	 * @return array
	 */
	function _analyseRefer($uid, $content) {
		preg_match_all('/@([^\\&\'"\/\*,<>\r\t\n\s#%?@:：]+)\s?/i', $content, $matchs);
		$array = array();
		if ($matchs[1]) {
			$userService = L::loadClass('UserService', 'user');
			$uInfo = $userService->getByUserNames($matchs[1]);
			$attentionService = L::loadClass('Attention', 'friend');
			$blackList = $attentionService->getBlackListToMe($uid, $this->_getFieldOfRecords($uInfo, 'uid'));
			foreach ($uInfo as $rt) {
				!in_array($rt['uid'], $blackList) && $array[$rt['uid']] = $rt['username'];
			}
		}
		return $array;
	}

	/**
	 * 添加新鲜事关系体
	 * @param int $uid
	 * @param int $mid
	 * @param string $type
	 * @access public
	 */
	function _addRelation($uid, $mid, $type) {
		global $db;
		$privacyService = L::loadClass('privacy','sns');
		if ($privacyService->getIsFollow($uid, 'self')) {
			$relationDao = L::loadDB('weibo_relations','sns');
			$relationDao->insert(array(
				'uid' => $uid,
				'mid' => $mid,
				'authorid' => $uid,
				'type' => $this->_map[$type],
				'postdate' => $this->_timestamp
			));
		}
		$typeKey = $this->_privacyMapping($type);
		$_sql_add = in_array($typeKey, array('article','diary','photos','group')) ? " AND o.{$typeKey}_isfollow=1" : '';

		//todo 根据以后策略再调整
		$db->update("INSERT INTO pw_weibo_relations (uid,mid,authorid,type,postdate) SELECT a.uid, ".S::sqlEscape($mid).", ".S::sqlEscape($uid).", ".S::sqlEscape($this->_map[$type]).", ".S::sqlEscape($this->_timestamp)." FROM pw_attention a LEFT JOIN pw_friends f ON a.uid=f.uid AND a.friendid=f.friendid AND f.status=0 LEFT JOIN pw_ouserdata o ON a.uid=o.uid WHERE a.friendid=" . S::sqlEscape($uid) . " AND a.uid!=a.friendid AND (o.uid IS NULL OR (o.friend_isfollow=1 AND f.uid IS NOT NULL OR o.cnlesp_isfollow=1 AND f.uid IS NULL)$_sql_add) ORDER BY a.joindate DESC LIMIT 1000");
	}

	/**
	 * 添加新鲜事关系体
	 * @param array $data 新鲜事关系体数据
	 * @return int
	 * @access public
	 */
	function addRelation($data) {
		if (!is_array($data)) {
			return 0;
		}
		$relationDao =  L::loadDB('weibo_relations','sns');
		return $relationDao->addRelation($data);
	}
	
	/**
	 * 添加提到我的新鲜事关系 
	 * @param array $data 添加的数据
	 * @return int
	 * @access public
	 */
	function addRefer($uids, $mid) {
		if (empty($uids) || !is_array($uids)) {
			return 0;
		}
		$data = array();
		foreach ($uids as $key => $uid) {
			$data[] = array($uid, $mid);
		}
		$referDao = L::loadDB('weibo_referto','sns');
		$affect = $referDao->addRefer($data);
		
		$userService = L::loadClass('UserService', 'user');
		$userService->updatesByIncrement($uids, array(), array('newreferto' => 1));
		return $affect;
	}
	
	/**
	 * 添加话题
	 * @param array $topics 新鲜事中包含的话题
	 * @param int mid 新鲜事id
	 * @return int
	 * @access public
	 */
	function addTopics($topics, $mid) {
		if (!$topics || !is_array($topics) || !$mid) {
			return false;
		}
		$topicService = L::LoadClass('topic','sns'); /* @var $topicService PW_Topic */
		$array = $topicService->addTopic($topics);
		if ($array) {
			//添加topic 与weibo 关系
			foreach ($array as $v)
				$topicService->addTopicRelations($v,$mid);
		}
	}
	function _addCnRelation($cyid,$mid){
		if(!$this->_isLegalId($cyid) || !$this->_isLegalId($mid)){
			return 0;
		}
		$cnData['cyid'] = $cyid;
		$cnData['mid'] = $mid;
		$referDao =  L::loadDB('weibo_cnrelations','sns');
		return $referDao->insert($cnData);
	}
	/**
	 * 添加关注时候推送的数据
	 * @param int $uid 添加关注者
	 * @param int $auid 被关注的人
	 * @parmm int $num 默认推送到$uid的数据
	 * @return int 
	 * @access public
	 */
	function pushData($uid, $auid, $num = 20) {
		if (!$this->_isLegalId($uid) || !$this->_isLegalId($auid) || !$this->_isLegalId($num)) {
			return 0;
		}
		$contentDao =  L::loadDB('weibo_content','sns');		
		$weibos = $contentDao->getUserWeibos($auid, 1, $num);
		if (empty($weibos)) {
			return 0;
		}
		$rData = array();
		foreach($weibos as $key => $value){
			$rData[] = array(
				'uid' => $uid,
				'mid' => $value['mid'],
				'authorid' => $auid,
				'type' => $value['type'],
				'postdate' => $value['postdate']
			);
		}
		return $this->addRelation($rData);
	}
	
	/**
	 * 取消关注时，删除我与某个人的新鲜事关系
	 * @param int $uid 操作者ID
	 * @param int $authorid 被操作者ID
	 * @return int
	 * @access public
	 */
	function removeRelation($uid,$authorid){
		if(!$this->_isLegalId($uid) || !$this->_isLegalId($authorid)){
			return 0;
		}
		$relationDao =  L::loadDB('weibo_relations','sns');
		return $relationDao->removeRelation($uid,$authorid);
	}
	
	function deleteAttentionRelation($uid, $num) {
		if ($num < 200) return 0;
		$num = min($num - 200, 1000);
		$relationDao =  L::loadDB('weibo_relations','sns');
		return $relationDao->deleteAttentionRelation($uid, $num);
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
	 * 构建新鲜事关系数据
	 * @param array $attentioner 关注我的人uid列表
	 * @param array $data 数据
	 * @return array
	 * @access private
	 */
	function _getRelationsData($attentioner,$data){
		$relationsData = array();
		foreach($attentioner as $key => $value){
			$data['uid'] = $value;
			$relationsData[] = $data;
		}
		return $relationsData;
	}

	function getWeibosByType($type, $page = 1, $perpage = 10) {
		if (!isset($this->_map[$type])) {
			return array();
		}
		$typeId = $this->_map[$type];
		$contentDao = L::loadDB('weibo_content','sns');
		$weibos = $contentDao->getWeibosByType($typeId, ($page - 1) * $perpage, $perpage);
		return $weibos;
	}
	
	function getWeibosByObjectIdsAndType($objectIds,$type){
		if (!isset($this->_map[$type]) || (!$this->_isLegalId($objectIds) && !is_array($objectIds))) {
			return array();
		}
		$type = $this->_map[$type];
		$contentDao = L::loadDB('weibo_content','sns');
		$weibos =  $contentDao->getWeibosByObjectIdsAndType($objectIds, $type);
		return is_array($objectIds) ? $weibos : current($weibos);
	}

	function getWeibosByMid($mids) {
		if (empty($mids) || (!is_numeric($mids) && !is_array($mids))) {
			return array();
		}
		if (perf::checkMemcache()){
			$_cacheService = Perf::gatherCache('pw_weibo_content');
			$array =  $_cacheService->getWeibosByMids($mids);			
		} else {
			$contentDao = L::loadDB('weibo_content','sns');
			$array = $contentDao->getWeibosByMid($mids);
		}
		return is_array($mids) ? $array : current($array);
	}
	
	/**
	 * 取得全站新鲜事新鲜事数据
	 * @param int $perpage 页记录数
	 * @param int $page 页数
	 * @return array
	 * @access public
	 */
	function getWeibos($page = 1,$perpage =20){
		$contentDao = L::loadDB('weibo_content','sns');
		$weibos = $contentDao->getWeibos($page,$perpage);
		return $this->buildData($weibos,'uid');
	}
	

	/**
	 * 取得新鲜事直播
	 */
	function getWeiboLives($num = 10){
		if (!$num) return false;
		$contentDao = L::loadDB('weibo_content','sns');
		$type = $this->_map;
		unset($type['transmit']);
		if (!$type || !is_array($type)) return false;
		$weibos = $contentDao->getWeibosByTypesAndNum($type, $num);
		return $this->buildData($weibos,'uid');
	}
	

	function getWeibosCount(){
		$contentDao = L::loadDB('weibo_content','sns');
		return $contentDao->getWeibosCount();
	}
	
	/**
	 * 取得最近发新鲜事的用户
	 * @param int $perpage 页记录数
	 * @return array
	 */
	function getWeiboAuthors($num, $exclude = array()) {
		$contentDao = L::loadDB('weibo_content','sns');
		return $contentDao->getWeiboAuthors($num, $exclude);
	}
	
	/**
	 * 取得7天内被转发次数最多的作者
	 * @param int $num 获取记录条数
	 * @return array
	 */
	function getAuthorSort($num) {
		$contentDao = L::loadDB('weibo_content','sns');
		if (!$user = $contentDao->getAuthorSort($num, $this->_timestamp - 604800)) {
			return array();
		}
		$userService = L::loadClass('UserService', 'user');
		$uinfo = $userService->getByUserIds($this->_getFieldOfRecords($user, 'uid'));
		$array = array();
		foreach ($user as $key => $value) {
			list($uinfo[$value['uid']]['icon']) = showfacedesign($uinfo[$value['uid']]['icon'], 1, 'm');
			$array[] = array(
				'uid' => $value['uid'],
				'username' => $uinfo[$value['uid']]['username'],
				'icon' => $uinfo[$value['uid']]['icon'],
				'counts' => $value['counts']
			);
		}
		return $array;
	}
	
	/**
	 * 取得n天内的新鲜事热门转发
	 * @param int $topicId
	 * @return 
	 */
	function getHotTransmit($num){
		$num = $num ? intval($num) : 20; 
		if(!$num) return array();
		extract (pwCache::getData(D_P.'data/bbscache/o_config.php',false));
		$time = $this->_timestamp - ($o_weibo_hottransmitdays ? intval($o_weibo_hottransmitdays) * 86400 : 86400);
		$contentDao = L::loadDB('weibo_content','sns');
		$objectId = $contentDao -> getHotTransmit($num,$time);
		if(!$objectId) return array();
		$contentData = $contentDao -> getWeibosByMid($objectId);
		if(!$contentData) return array();
		$data = array();
		foreach($objectId as $key => $v){
			if(!$contentData[$v]){
				unset($key);
				continue; 
			}
			$data[] = $contentData[$v];
		}
		return $this->buildData($data,'uid');
	}
	
	/**
	 * 取得n天内的新鲜事热门评论
	 * @param int $topicId
	 * @return 
	 */
	function getHotComment($num){
		$num = $num ? intval($num) : 20; 
		if(!$num) return array();
		extract (pwCache::getData(D_P.'data/bbscache/o_config.php',false));
		$time = $this->_timestamp - ($o_weibo_hotcommentdays ? intval($o_weibo_hotcommentdays) * 86400 : 86400);
		$contentDao = L::loadClass('comment','sns');
		$objectIds = $contentDao -> getHotComment($num,$time);
		if(!$objectIds) return array();
		$contentDao = L::loadDB('weibo_content','sns');
		$commentData = $contentDao -> getWeibosByMid($objectIds);
		if(!$commentData) return array();
		$data = array();
		foreach($objectIds as $key => $v){
			if(!$commentData[$v]){
				unset($key);
				continue; 
			}
			$data[] = $commentData[$v];
		}
		return $this->buildData($data,'uid');
	}
	
	/**
	 * 取得用户的新鲜事列表
	 * @param int $uid 用户ID
	 * @param int $perpage 页记录数
	 * @param int $page 页数
	 * @return array
	 * @access public
	 */
	function getUserWeibos($uid,$page = 1,$perpage = 20){
		if(!$this->_isLegalId($uid)){
			return array();
		}
		$contentDao = L::loadDB('weibo_content','sns');
		$userWeibos = $contentDao->getUserWeibos($uid,$page,$perpage);
		return $this->buildData($userWeibos, 'uid');
	}
	
	function getUserWeibosCount($uid){
		if(!$this->_isLegalId($uid)){
			return 0;
		}
		$contentDao = L::loadDB('weibo_content','sns');
		return $contentDao->getUserWeibosCount($uid);
	}
	
	function getUserAttentionWeibosCount($uid,$filter=array()) {
		if (!$this->_isLegalId($uid)) {
			return 0;
		}
		if (($sqlArr = $this->_filterSql($uid, $filter)) === false) {
			return 0;
		}
		$contentDao = L::loadDB('weibo_content','sns');/* @var $contentDao PW_Weibo_ContentDB */
		return $contentDao->getUserAttentionWeibosCount($uid, $sqlArr);
	}
	
	/**
	 * 取得用户关注的新鲜事
	 * @param int $uid 用户ID
	 * @param array $filter 用户过滤条件
	 * @param int $perpage 页记录数
	 * @param int $page 页数
	 * @return array
	 * @access public
	 */
	function getUserAttentionWeibos($uid,$filter = array(),$page = 1,$perpage = 20) {
		if (!$this->_isLegalId($uid)) {
			return array();
		}
		if (($sqlArr = $this->_filterSql($uid, $filter)) === false) {
			return array();
		}
		$contentDao = L::loadDB('weibo_content','sns');
		$attention = $contentDao->getUserAttentionWeibos($uid, $sqlArr, $page, $perpage);
		return $this->buildData($attention, 'authorid');
	}

	function _filterSql($uid, $filter) {
		if (empty($filter)) {
			return array();
		}
		if (empty($filter['relation']) || empty($filter['contenttype'])) {
			return false;
		}
		$array = array_merge($this->_relationSql($uid, $filter['relation']), $this->_sourceSql($filter['source']));
		if (count($filter['contenttype']) == 1) {
			$array['contenttype'] = isset($filter['contenttype']['string']) ? 0 : 1;
		}
		return $array;
	}

	function arrayOp($array1, $array2, $op) {
		return $op ? array_merge($array1, $array2) : array_diff($array1, $array2);
	}

	function _relationSql($uid, $relation) {
		if (!is_array($relation) || count($relation) >= 3) {
			return array();
		}
		$array = array();
		if ($relation['friend'] != $relation['attention']) {
			$friendDao = L::loadDB('friend', 'friend');
			$uArr = $this->_getFieldOfRecords($friendDao->getFriendsByUid($uid), 'friendid');
			if ($relation['friend']) {
				$array['uidsIn'] = $this->arrayOp($uArr, array($uid), $relation['self']);
			} else {
				$array['uidsNotIn'] = $this->arrayOp($uArr, array($uid), !$relation['self']);
			}
		} else {
			$array[$relation['self'] ? 'uidIn' : 'uidNotIn'] = $uid;
		}
		return $array;
	}
	
	function _sourceSql($source) {
		$source = $source ? $source : array();
		if (!is_array($source)) {
			return array();
		}
		$array = array(0, 1, 2);
		$map = $this->_compositeMap();
		if (count($source) >= (count($map) - 5)) return array();
		
		foreach ($source as $key => $value) {
			if (is_array($map[$key])) {
				$array = array_merge($array, array_values($map[$key]));
			} else {
				$array[] = $map[$key];
			}
		}
		return array('source' => $array);
	}
	
	function getUserAttentionWeibosNotMe($uid,$page = 1,$perpage = 20){
		if (!$this->_isLegalId($uid) || !$this->_isLegalId($page) || !$this->_isLegalId($perpage)) {
			return array();
		}
		$contentDao = L::loadDB('weibo_content','sns');
		$attention = $contentDao->getUserAttentionWeibosNotMe($uid,$page,$perpage);
		return $this->buildData($attention, 'authorid');
	}
	
	function getUserAttentionWeibosNotMeCount($uid){
		if(!$this->_isLegalId($uid)){
			return 0;
		}
		$contentDao = L::loadDB('weibo_content','sns');
		return $contentDao->getUserAttentionWeibosNotMeCount($uid);
	}

	function getPrevWeiboByType($uid, $type, $time = 30) {
		$contentDao = L::loadDB('weibo_content','sns');
		return $contentDao->getPrevWeiboByType($uid, $this->getTypeKey($type), ($this->_timestamp - $time));
	}

	/**
	 * 构建展示的新鲜事数据
	 * @param array $data 新鲜事数据
	 * @param string $field 用户id字段名称
	 * return array
	 */
	function buildData($data, $field = 'uid') {
		$uids = $tids = $tArr = array();
		foreach ($data as $key => $value) {
			$uids[] = $value[$field];
			$type = $this->getType($value['type']);
			if ($type == 'transmit' && $value['objectid']) {
				$tids[] = $value['objectid'];
			}
			$data[$key]['content'] = strip_tags($value['content'],'<a>');
		}
		if ($tids) {
			$tArr = $this->getWeibosByMid($tids);
			$uids = array_merge($uids, $this->_getFieldOfRecords($tArr, 'uid'));
		}
		
		$uinfo = $this->_getUserInfo($uids);
		
		/* platform weibo app */
		$siteBindService = L::loadClass('WeiboSiteBindService', 'sns/weibotoplatform/service'); /* @var $siteBindService PW_WeiboSiteBindService */
		if ($siteBindService->isOpen()) {
			$userBindService = L::loadClass('WeiboUserBindService', 'sns/weibotoplatform/service'); /* @var $userBindService PW_WeiboUserBindService */
			$usersBindInfo = $userBindService->getUsersLocalBindInfo(array_keys($uinfo));
		}
		foreach ($data as $key => $value) {
			$value = $this->formatRecord($value, $uinfo[$value[$field]]['groupid']);
			$type = $this->getType($value['type']);
			if ($type == 'transmit' && ($transmit = $tArr[$value['objectid']])) {
				$value['transmits'] = array_merge((array)$this->formatRecord($transmit, $uinfo[$transmit['uid']]['groupid']), (array)$uinfo[$transmit['uid']]);
			}
			!is_array($uinfo[$value[$field]]) && $uinfo[$value[$field]] = array();
			$data[$key] = array_merge((array)$value, $uinfo[$value[$field]]);
			
			/* platform weibo app */
			if ($siteBindService->isOpen() && $siteBindService->isBind($type)) {
				$data[$key]['bindUserInfo'] = $usersBindInfo[$type][$value[$field]]['info'];
				$data[$key]['bindSiteInfo'] = $siteBindService->getBindType($type);
				$data[$key]['bindUserInfo']['url'] = $data[$key]['bindSiteInfo']['uidUrlPrefix'] . $data[$key]['bindUserInfo']['id'];
				if (isset($data[$key]['extra']['sinaPhotos'])) $data[$key]['extra']['photos'] = $data[$key]['extra']['sinaPhotos']; //for compatible
			}
		}
		return $data;
	}

	function formatRecord($value, $gid) {
		list($value['lastdate'], $value['postdate_s']) = getLastDate($value['postdate']);
		$value['extra'] = $value['extra'] ? unserialize($value['extra']) : array();
		!$value['authorid'] && $value['authorid'] = $value['uid'];
		if ($gid == '6') {
			if (isset($value['extra']['title'])) {
				$value['extra']['title'] = "<span style=\"color:black;background-color:#ffff66\">该内容已被管理员屏蔽！</span>";
				$value['content'] = '';
			} else {
				$value['content'] = "<span style=\"color:black;background-color:#ffff66\">该内容已被管理员屏蔽！</span>";
			}
			isset($value['extra']['photos']) && $value['extra']['photos'] = array();
		} else {
			$value['content'] = $this->parseContent($value['content'], $value['extra']);
		}
		return $value;
	}
	
	/**
	 * 解析内容
	 * @param string $content 新鲜事内容
	 * @param array $extra 扩展信息
	 * return string
	 */
	function parseContent($content, &$extra) {
		global $topic;
		$this->_hasVideo = array();
		$content = $this->_parseLink($content);
		if ($this->_hasVideo) {
			$extra['_hasVideo'] = $this->_hasVideo;
		}
		if ($extra['refer']) {
			$uArray  = array_flip($extra['refer']);
			$content = preg_replace('/@([^\\&\'"\/\*,<>\r\t\n\s#%?@:：]+)(?=\s?)/ie', "\$this->_parseRefer('\\1', \$uArray)", $content);
		}
		if ($extra['topics']) {
			$content = pwHtmlspecialchars_decode($content,false);
			if(preg_match('/^#\s+#$/', $content)) return $content;
			$content = preg_replace_callback('/#([^@&#!*\(\)]+)#/U',array(&$this,'_callback_add_topic_url'),$content);
		}
		if (strpos($content,'[s:') !== false && strpos($content,']') !== false) {
			$content = $this->_parseSmile($content);
		}
		
		if ($topic && !$extra['topics']) {
			$content = strip_tags($content);
			$content = preg_replace('/' . preg_quote($topic,'/') . '/i', "<span class='s2'>$topic</span>", $content);
		}
		return $content;
	}
	
	function _callback_add_topic_url($matches){
		//global $topic;
		//if ($topic) $matches[0] = preg_replace('~' . preg_quote($topic) . '~i', "<span class='s2'>$topic</span>", $matches[0]);
		$pattern = "/(https?|ftp|gopher|news|telnet|mms|rtsp):\/\/[a-z0-9\/\-_+=.~!%@?%&;:$\\│\|]+(#.+)?/ie";
		if (preg_match($pattern, $matches[1])){
			return $matches[0];
		}
		return '<a href="apps.php?q=weibo&do=topics&topic=' . urlencode(strip_tags($matches[1],'<span>')) . '">' . strip_tags($matches[0],'<span>') . '</a>';
	}
	
	/**
	 * 解析新鲜事内容的链接地址
	 * @param string $content
	 * @param int $mid
	 * return string
	 */
	function _parseLink($content) {
		if (strpos($content,'[/URL]') !== false || strpos($content,'[/url]') !== false) {
			$content = preg_replace("/\[url=([^\[]+?)\](.*?)\[\/url\]/is","<a href=\"\\1\" target=\"_blank\">\\2</a>", $content);
		}
		//return preg_replace("/(?<!\shref=['\"])((https?|ftp|gopher|news|telnet|mms|rtsp):\/\/[a-z0-9\/\-_+=.~!%@?#%&;:$\\│\|]+)/ie", "\$this->_parseLinkContent('\\1')", $content);
		return preg_replace("/(?<!\shref=['\"])((https?|ftp|gopher|news|telnet|mms|rtsp):\/\/[a-z0-9\/\-_+=.~!%@?%&;:$\\│\|]+(#.+)?)/ie", "\$this->_parseLinkContent('\\1')", $content);
	}
	
	/**
	 * 解析网页、视频、音乐、flash等链接
	 */
	function _parseLinkContent($url) {
		if ($return = $this->_parseVideo($url)) {
			return $return;
		}
		if (preg_match("/\.(mp3|wma)\??.*$/i", $url)) {
			return $this->_parseMusic($url);
		}
		return $this->_parseWebUrl($url);
	}
	
	/**
	 * 解析新鲜事内容的flash视频
	 * @param string $url
	 * @param int $mid
	 * return string
	 */
	function _parseVideo($url) {
		static $sNum = 0;
		if (!($videoAddr = $this->_parseVideoWebSiteAddr($url)) && preg_match("/\.swf\??.*$/i", $url)) {
			$videoAddr = $url;
		}
		if ($videoAddr) {
			empty($this->_hasVideo) && $this->_hasVideo = array(++$sNum, $videoAddr);
			return "<img src=\"u/images/share_s.png\" width=\"16\" class=\"mr5\" style=\"vertical-align:middle;\" /><a class=\"cp\" onclick=\"mediaPlayer.showVideo('$videoAddr','$sNum');return false;\">$url</a>";
		}
		return false;
	}
	
	/**
	 * 解析各大视频网站的链接地址
	 * @param string $url
	 * return string
	 */
	function _parseVideoWebSiteAddr($url) {
		if (!preg_match("/(youku.com|youtube.com|sohu.com|sina.com.cn)/i", $url, $hosts)) {
			return false;
		}
		$videoRules = array(
			'youku.com'		=> '/v_show\/id_([\w=]+)\.html/',
			'youtube.com'	=> '/v\=([\w\-]+)/',
			'sina.com.cn'	=> '/\/(\d+)-(\d+)\.html/',
			'sohu.com'		=> '/\/(\d+)\/*$/'
		);
		if (isset($videoRules[$hosts[1]]) && preg_match($videoRules[$hosts[1]], $url, $matches)) {
			return $this->_getVideoWebSiteAddr($hosts[1], $matches[1]);
		}
		return false;
	}
	
	/**
	 * 获取各大视频网站的flash真实链接地址
	 * @param string $hosts
	 * @param string $hash
	 * return string
	 */
	function _getVideoWebSiteAddr($hosts, $hash) {
		switch ($hosts) {
			case 'youku.com':
				$videoAddr = 'http://player.youku.com/player.php/sid/' . $hash . '=/v.swf';break;
			case 'youtube.com':
				$videoAddr = 'http://www.youtube.com/v/' . $hash;break;
			case 'sina.com.cn':
				$videoAddr = 'http://vhead.blog.sina.com.cn/player/outer_player.swf?vid=' . $hash;break;
			case 'sohu.com':
				$videoAddr = 'http://v.blog.sohu.com/fo/v4/' . $hash;break;
			default:
				$videoAddr = false;
		}
		return $videoAddr;
	}
	
	/**
	 * 解析音乐链接
	 * @param string $url
	 * return string
	 */
	function _parseMusic($url) {
		static $sNum = 0;
		$sNum++;
		return "<span><img title=\"播放\" class=\"cp mr5\" src=\"u/images/music.png\" style=\"vertical-align:middle;\" onclick=\"mediaPlayer.showMusic('$url', '$sNum', this)\" /></span>";
	}

	/**
	 * 解析普通链接
	 * @param string $url
	 * return string
	 */
	function _parseWebUrl($url) {
		return '<a href="' . $url . '" target="_blank">' . $url . '</a>';
	}

	/**
	 * 解析表情
	 */
	function _parseSmile($content) {
		$sParse = L::loadClass('smileparser', 'smile');
		return $sParse->parse($content);
	}

	/**
	 * 解析内容中@功能
	 * @param string $username 用户名
	 * @param array @列表
	 * return string
	 */
	function _parseRefer($username, $uArray) {
		return isset($uArray[$username]) ? '<a href="'.USER_URL. $uArray[$username] . '">@' . $username . '</a>' : '@' . $username;
	}

	/**
	 * 获取用户信息
	 * @param array $uids 用户id数组
	 * return array
	 */
	function _getUserInfo($uids) {
		if (empty($uids) || !is_array($uids)) {
			return array();
		}
		require_once(R_P . 'require/showimg.php');
		$newUsersInfo = array();

		$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
		$users = $userService->getByUserIds($uids); //'m.uid','m.username','m.icon','m.groupid'
		foreach ($users as $key => $value) {
			list($value['icon']) = showfacedesign($value['icon'], 1, 's');
			$newUsersInfo[$value['uid']] = $value;
		}
		return $newUsersInfo;
	}

	/**
	 * 取得@用户的新鲜事
	 * @param int $uid 用户ID
	 * @param int $perpage 页记录数
	 * @param int $page 页数
	 * @return array
	 * @access public
	 */
	function getRefersToMe($uid,$page = 1,$perpage = 20){
		if(!$this->_isLegalId($uid)){
			return 0;
		}
		$referDao = L::loadDB('weibo_referto','sns');
		$refers = $referDao->getRefersToMe($uid,$page,$perpage);
		return $this->buildData($refers, 'uid');
	}
	
	function getRefersToMeCount($uid){
		if(!$this->_isLegalId($uid)){
			return 0;
		}
		$referDao = L::loadDB('weibo_referto','sns');
		return $referDao->getRefersToMeCount($uid);
	}
	
	/**
	 * 取得群组下面的新鲜事列表
	 * @param mixed $cyids 群组ID
	 * @param int $perpage 页记录数
	 * @param int $page 页数
	 * @return array
	 * @access public
	 */
	function getConloysWeibos($cyids,$page = 1,$perpage = 20){
		if ($cyids == 'nocyids') {
			$referDao = L::loadDB('weibo_cnrelations','sns');
			$conloyWeibos = $referDao->getConloysWeibos('nocyids',$page,$perpage);
			return $this->buildData($conloyWeibos, 'uid');
		}
		$cyids = is_array($cyids) ? $cyids : array($cyids);
		if(empty($cyids)){
			return array();
		}
		$referDao = L::loadDB('weibo_cnrelations','sns');
		$conloyWeibos = $referDao->getConloysWeibos($cyids,$page,$perpage);
		return $this->buildData($conloyWeibos, 'uid');
	}
	
	function getConloysWeibosCount($cyids){
		$cyids = is_array($cyids) ? $cyids : array($cyids);
		if(empty($cyids)){
			return 0;
		}
		$referDao = L::loadDB('weibo_cnrelations','sns');
		return $referDao->getConloysWeibosCount($cyids);
	}
	/**
	 * 删除新鲜事
	 * @param int $mid 新鲜事ID
	 * @return int
	 */
	function deleteWeibos($mids){
		if (empty($mids)) {
			return false;
		}
		$mids = is_array($mids) ? $mids : array($mids);
		$contentDao = L::loadDB('weibo_content','sns');
		$relationsDao = L::loadDB('weibo_relations','sns');
		$referstDao = L::loadDB('weibo_referto','sns');
		$cnrelationsDao = L::loadDB('weibo_cnrelations','sns');
		$contentDao->deleteWeibosByMid($mids);
		$relationsDao->delRelationsByMid($mids);
		$referstDao->deleteRefersByMid($mids);	
		$cnrelationsDao->deleteCnrelationsByMid($mids);
		$topicDao = L::loadDB('topic','sns');
		//删除微博对应的评论
		$commentService = L::loadClass("comment","sns"); /* @var $commentService PW_Comment */
		$commentService->unionDeleteCommentsByMid($mids);
		//删除与话题 的对应关系
		$topicRelationsDao = L::loadDB('weibo_topicrelations','sns');
		foreach ($mids as $mid) {
			$topicIds = $topicRelationsDao->getTopicIdsByMid($mid);
			if(!$topicIds) continue;
			$topicRelationsDao->deleteRelationByMid($mid);
			$topicDao->decreaseTopicNum($topicIds);
		}
		return true;
	}
	
	/**
	 * 更新新鲜事内容
	 * @param array $data 更新数据
	 * @param int $mid 新鲜事ID
	 * @return int
	 */
	function update($data, $mid) {
		$mid = intval($mid);
		if ($mid < 1 || !is_array($data)) {
			return false;
		}
		$contentDao = L::loadDB('weibo_content','sns');
		$contentDao->update($data, $mid);
	}

	/**
	 * 更新新鲜事统计数
	 * @param array $data 更新数据
	 * @param int $mid 新鲜事ID
	 * @return int
	 */
	function updateCountNum($data,$mid) {
		$mid = intval($mid);
		if ($mid < 1 || !is_array($data)) {
			return false;
		}
		$contentDao = L::loadDB('weibo_content','sns');
		$contentDao->updateCountNum($data, $mid);
	}
	
	function _isLegalId($id){
		return intval($id) > 0;
	}

	/**
	 * 新鲜事型map图 
	 */
	function _typeMap(){
		$this->_map = array(
			'weibo' => 0,//新鲜事
			'transmit' => 1,//转发
			'sendweibo' => 2, //发送到新鲜事
			'cms' => 3, //文章模式
			'honor' => 4,
			'article' => 10, //帖子
			'diary' => 20,//日志
			'photos' => 30,//相册
			'group_article' => 40,//群组话题
			'group_photos' => 41,//群组相册
			'group_active' => 42,//群组活动
			'group_write' => 43,//群组记录/讨论
			//NOTE please keep 50-59 for external weibo types
		);
		$this->_mapDescript = array(
			'weibo' => '新鲜事',
			'transmit' => '转发新鲜事',
			'sendweibo' => '发送到新鲜事',
			'honor' => '签名',
			'article' => '帖子',
			'diary' => '日志',
			'photos' => '相册',
			'group_article' => '群组话题',
			'group_photos' => '群组相册',
			'group_active' => '群组活动',
			'group_write' => '群组记录',
			'cms' => '文章',
		);
		
		/* platform weibo app */
		$siteBindService = L::loadClass('WeiboSiteBindService', 'sns/weibotoplatform/service'); /* @var $siteBindService PW_WeiboSiteBindService */
		if ($siteBindService->isOpen()) {
			foreach ($siteBindService->getBindTypes() as $key => $config) {
				$this->_map[$key] = $config['typeId'];
				$this->_mapDescript[$key] = $config['title'];
			}
		}
		
		$this->_mapflip = array_flip($this->_map);
	}
	
	function getTypeDescript($type){
		$type = $this->getType($type);
		return $this->_mapDescript[$type];
	}
	
	function getValueMapDescript(){
		$tmpMap = array();
		foreach($this->_map as $key => $value){
			$tmpMap[$value] = $this->_mapDescript[$key];
		}
		return $tmpMap;
	}

	function _privacyMapping($type){
		list($tmp) = explode('_', $type);
		return $tmp;
	}
	
	function _compositeMap() {
		$map = array();
		foreach ($this->_map as $key => $value) {
			$tmp = explode('_', $key);
			if (count($tmp) > 1) {
				$map[$tmp[0]][$tmp[1]] = $value;
			} else {
				$map[$key] = $value;
			}
		}
		return $map;
	}
	
	function getTypeKey($type) {
		return isset($this->_map[$type]) ? $this->_map[$type] : 0;
	}
	/**
	 * 取得新鲜事类型
	 */
	function getType($type) {
		return isset($this->_mapflip[$type]) ? $this->_mapflip[$type] : 'weibo';
	}

	/**
	 * 获取新鲜事展示类型（有些新鲜事类型可用同一展示模版）
	 */
	function getViewType($type) {
		$weiboType = $this->getType($type);
		
		/* platform weibo app */
		$siteBindService = L::loadClass('WeiboSiteBindService', 'sns/weibotoplatform/service'); /* @var $siteBindService PW_WeiboSiteBindService */
		if ($siteBindService->isBind($weiboType)) return 'bindweibo';

		return $weiboType;
	}
	
	function adminSearch($usernames,$contents,$startDate,$endDate,$type = 0 ,$orderby = 'desc',$page = 1,$perpage = 20){
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
		$contentDao = L::loadDB('weibo_content','sns');
		$result = $contentDao->adminSearch($uids,$contents,$startDate,$endDate,$type,$orderby,$page,$perpage);
		foreach($result[1] as $key => $value){
			$result[1][$key]['content'] = substr(stripWindCode($value['content']),0,30);
		}
		$weibos = $this->buildData($result[1],'uid');
		
		return array($result[0],$weibos);
	}
	
	/**
	 * 后台会员删除管理操作  ---删除微博
	 * 
	 * @param $Uids
	 */
	function deleteWeibosByUids($uids){
		if(!$uids || !is_array($uids)) return false;
		$mids = array();
		$midTems  = $this->findMidsByUids($uids);
		foreach($midTems as $mid) {
			$mids[] = $mid['mid'];
		}
		return $this->deleteWeibos($mids);
	}

	function deleteWeibosByObjectIdsAndType($objectIds, $type) {
		if (!isset($this->_map[$type]) || (!$this->_isLegalId($objectIds) && !is_array($objectIds))) {
			return array();
		}
		$type = $this->_map[$type];
		$mids = $tempMids = array();
		$contentDao = L::loadDB('weibo_content','sns');
		$tempMids = $contentDao->getMidsByObjectIdsAndType($objectIds, $type);
		foreach ($tempMids as $mid) {
			$mids[] = $mid['mid'];
		}
		if (!$mids) return false;
		return $this->deleteWeibos($mids);
	}
	
	function findMidsByUids($uids) {
		if(!$uids || !is_array($uids)) return false;
		$contentDao = L::loadDB('weibo_content','sns'); /* @var $contentDao PW_Weibo_ContentDB */
		return $contentDao->findMidsByUids($uids);
	}
}
?>