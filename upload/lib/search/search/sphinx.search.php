<?php
!function_exists('readover') && exit('Forbidden');
class Search_Sphinx extends Search_Base {
	
	var $_sphinx 		    = null;
	var $_sphinxPerPage     = 20;
	var $_sphinxLimit 	    = 20;
	var $_sphinxMethod      = "AND";
	var $_sphinxOrder       = "DESC";
	var $_sphinxlen         = false;
	var $_sphinxAPI         = null;   //sphinx公共API服务接口
	var $_sphinxHost        = null;   //sphinx服务器IP地址
	var $_sphinxPort        = null;   //sphinx服务器端口
	var $_sphinxMode        = null;   //sphinx搜索模式
	var $_sphinxFilter      = array();//sphinx过滤条件
	var $_sphinxFilterRange = array();//sphinx过滤范围
	var $_sphinxGroupBy     = null;   //sphinx分组条件
	var $_sphinxGroup       = null;   //sphinx分组方式
	var $_sphinxSortBy      = null;   //sphinx排序条件
	var $_sphinxSort        = null;   //sphinx排序方式
	var $_sphinxOffset      = 0;      //sphinx offset查询起点
	var $_sphinxMaxMatch    = 1000;   //sphinx最大查询返回数
	var $_sphinxRanking     = null;   //sphinx评分模式
	var $_sphinxKeywords    = null;   //sphinx查询关键字
	var $_sphinxIndex       = null;   //sphinx查询索引名称
	var $_sphinxCharset     = 1;      //sphinx分词方法
	
	var $_sphinxFilterIds   = null;
	
	var $_server_thread  = 'thread'; //帖子单服务器服置
	var $_server_user    = 'user';   //用户单服务器服置
	var $_server_post    = 'post';   //回复单服务器服置
	var $_server_diary   = 'diary';  //日志单服务器服置
	var $_server_weibo   = 'weibo';  //新鲜事单服务器服置
	var $_key_tindex     = 'tindex';  //帖子标题索引键
	var $_key_tcindex 	 = 'tcindex'; //帖子内容索引键
	var $_key_taindex	 = 'taindex'; //帖子索引键
	var $_key_pindex  	 = 'pindex';  //回复索引键
	var $_key_windex 	 = 'windex';  //新鲜事标题索引键
	var $_key_dindex 	 = 'dindex';  //日志标题索引键
	var $_key_dcindex	 = 'dcindex'; //日志内容索引键
	var $_key_daindex	 = 'daindex'; //日志索引键
	var $_key_mindex 	 = 'mindex';  //用户索引键
	
	function Search_Sphinx(){
		global $db_sphinx,$db_filterids;
		parent::Search_Base();
		$this->_sphinx          = &$db_sphinx;
		$this->_sphinxFilterIds = ($db_filterids) ? explode(",",$db_filterids) : false;
		$this->_sphinxMaxMatch  = min($this->_maxResult,$this->_sphinxMaxMatch);
		$this->_sphinxCharset   = (isset($this->_sphinx['wordsegment_mode'])) ? $this->_sphinx['wordsegment_mode'] : $this->_sphinxCharset;
		
	}
	function checkUserLevel(){
		return $this->_checkUserLevel();
	}
	function checkWaitSegment(){
		return $this->_checkWaitSegment();
	}
	function searchThreads($keywords,$range,$userNames="",$starttime="",$endtime="",$forumIds = array(),$page=1,$perpage=20,$expand=array()){
		if(!($result = $this->_searchThreads($keywords,$range,$userNames,$starttime,$endtime,$forumIds,$page,$perpage,$expand['sortby']))){
			return array(false,false);
		}
		//$threads = $this->_getThreads($result[1],$result[2]);
		if($range == 3){
			$this->_getExpand($expand);
			$searchs = $this->_getPosts($result[1],$result[2],$this->_getPostsTable());
		}else{
			$searchs = $this->_getThreads($result[1],$result[2]);
		}
		return array($result[0],$searchs);
	}
	function manageThreads($keywords,$range,$userNames="",$starttime="",$endtime="",$forumIds = array(),$page=1,$perpage=20){
		return $this->_searchThreads($keywords,$range,$userNames,$starttime,$endtime,$forumIds,$page,$perpage);
	}
	function _searchThreads($keywords,$range,$userNames="",$starttime="",$endtime="",$forumIds = array(),$page=1,$perpage=20,$sortby=''){
		list($keywords,$users,$starttime,$endtime) = $this->_checkThreadConditions($keywords,$userNames,$starttime,$endtime);
		if(!$keywords || ($userNames && !$users )) return false;
		$configs = $this->_getSphinxConfigs($this->_server_thread);
		list($host,$port) = ($configs) ? $configs : $this->_getSphinxConfig();
		$filter = $filterRange = array();
		if($users){
			$filter[] = array('attribute' => 'authorid','values' => array_keys($users),'exclude' => false);
		}
		if($forumIds){
			$forumIds = (is_array($forumIds)) ? $forumIds : array($forumIds);
			$filter[] = array('attribute' => 'fid','values' => $forumIds,'exclude' => false);
		}
		if($this->_sphinxFilterIds){
			$filter[] = array('attribute' => 'fid','values' => $this->_sphinxFilterIds,'exclude' => true);
		}
		$filter[] = array('attribute' => 'fid','values' => array(0),'exclude' => true);
		$filterRange = array( array('attribute' => 'postdate','min' => $starttime,'max' => $endtime,'exclude' => false));
		$page = $page>1 ? $page : 1;
		$offset = intval(($page - 1) * $perpage);
		$this->_setDefaultSphinx();
		$this->_sphinxHost        = $host;
		$this->_sphinxPort        = $port;
		$this->_sphinxMode        = $this->_getSphinxMode($this->_sphinxMethod);
		$this->_sphinxFilter      = $filter;
		$this->_sphinxFilterRange = $filterRange;
		$this->_sphinxOffset      = $offset;
		$this->_sphinxLimit       = $perpage;
		$this->_sphinxKeywords    = $keywords;
		$this->_sphinxSortBy      = ($sortby && in_array($sortby,array('postdate','lastpost','replies'))) ? $sortby : ''; 
		$this->_sphinxIndex       = $this->_getSphinxMap($this->_getThreadRange($range));
		$result = $this->_sphinxAssemble();
		if ( $result === false ) return false;
		return $this->_buildSphinxResult($result,'id');
	}
	function _getThreadRange($k){
		$ranges = array( 1 => $this->_key_tindex,2 => $this->_key_tcindex,3 => $this->_key_pindex,4 => $this->_key_taindex);
		return $ranges[$k] ? $ranges[$k] : $ranges[2];
	}
	
	function _getExpand($expand){
		global $db_plist;
		$this->_expand['ptable'] = min(intval($expand['ptable']),count($db_plist));
	}
	
	function _getPostsTable(){
		$num =  ( intval($this->_expand['ptable']) > 0 ) ? intval($this->_expand['ptable']) : '';
		return 'pw_posts'.$num;
	}

	/********************************************************************/
	function searchUsers($keywords,$page=1,$perpage=20){
		if(!($result = $this->_searchUsers($keywords,$page,$perpage))){
			return array(false,false);
		}
		$users = $this->_getUsers($result[1],$result[2]);
		return array($result[0],$users);
	}
	function _searchUsers($keywords,$page=1,$perpage=20){
		$keywords = $this->_checkKeywordCondition($keywords);
		if(!$keywords) return array();
		$configs = $this->_getSphinxConfigs($this->_server_user);
		list($host,$port) = ($configs) ? $configs : $this->_getSphinxConfig();
		$filter = $filterRange = array();
		$page = $page>1 ? $page : 1;
		$offset = intval(($page - 1) * $perpage);
		$this->_setDefaultSphinx();
		$this->_sphinxHost        = $host;
		$this->_sphinxPort        = $port;
		$this->_sphinxMode        = $this->_getSphinxMode($this->_sphinxMethod);
		$this->_sphinxFilter      = $filter;
		$this->_sphinxFilterRange = $filterRange;
		$this->_sphinxOffset      = $offset;
		$this->_sphinxLimit       = $perpage;
		$this->_sphinxKeywords    = $keywords;
		$this->_sphinxSortBy      = 'groupid';
		$this->_sphinxSort        = $this->_getSphinxSort('ASC');
		$this->_sphinxIndex       = $this->_getSphinxMap($this->_key_mindex);
		$result = $this->_sphinxAssemble();
		if ( $result === false ){
			return false;
		} 
		return $this->_buildSphinxResult($result,'id');
	}
	function _getUsers($userIds){
		if(!$userIds) return array();

		$userService = $this->_getUserService();
		$result = $userService->getUsersWithMemberDataByUserIds(explode(",",trim($userIds)));
		if (!$result) return array();
		
		return $this->_buildUsers($result);
	}
	/*****************************************************************************/
	function searchWeibo($keywords,$userNames="",$starttime="",$endtime="",$page=1,$perpage=20){
		if(!($result = $this->_searchWeibo($keywords,$userNames,$starttime,$endtime,$page,$perpage))){
			return array(false,false);
		}
		$weibo = $this->_getweibo($result[1],$result[2]);
		return array($result[0],$weibo);
	}
	function _searchWeibo($keywords,$userNames="",$starttime="",$endtime="",$page=1,$perpage=20){
		list($keywords,$users,$starttime,$endtime) = $this->_checkThreadConditions($keywords,$userNames,$starttime,$endtime);
		if(!$keywords || ($userNames && !$users) ) return false;
		$configs = $this->_getSphinxConfigs($this->_server_weibo);
		list($host,$port) = ($configs) ? $configs : $this->_getSphinxConfig();
		$filter = $filterRange = array();
		($users) ? $filter[] = array('attribute' => 'uid','values' => array_keys($users),'exclude' => false) : 0;
		$filterRange = array( array('attribute' => 'postdate','min' => $starttime,'max' => $endtime,'exclude' => false));
		$page = $page>1 ? $page : 1;
		$offset = intval(($page - 1) * $perpage);
		$this->_setDefaultSphinx();
		$this->_sphinxHost        = $host;
		$this->_sphinxPort        = $port;
		$this->_sphinxMode        = $this->_getSphinxMode($this->_sphinxMethod);
		$this->_sphinxFilter      = $filter;
		$this->_sphinxFilterRange = $filterRange;
		$this->_sphinxOffset      = $offset;
		$this->_sphinxLimit       = $perpage;
		$this->_sphinxKeywords    = $keywords;
		$this->_sphinxIndex       = $this->_getSphinxMap($this->_key_windex);
		$result = $this->_sphinxAssemble();
		if ( $result === false ){
			return false;
		} 
		return $this->_buildSphinxResult($result,'id');
	}
	function _getWeibo($mids,$keywords){
		if(!$mids) return array();
		$mids = explode(',',$mids);//
		$weiboDao = $this->getWeiboDao();
		if(!($result = $weiboDao->getWeibosByMid($mids))){
			return array();
		} 
		//return $this->_buildWeibo($result,$keywords);
		return $result;
	}
	
	/*****************************************************************************/
	function searchDiarys($keywords,$range,$userNames="",$starttime="",$endtime="",$page=1,$perpage=20){
		if(!($result = $this->_searchDiarys($keywords,$range,$userNames,$starttime,$endtime,$page,$perpage))){
			return array(false,false);
		}
		$diarys = $this->_getDiarys($result[1],$result[2]);
		return array($result[0],$diarys);
	}
	function _searchDiarys($keywords,$range,$userNames="",$starttime="",$endtime="",$page=1,$perpage=20){
		list($keywords,$users,$starttime,$endtime) = $this->_checkThreadConditions($keywords,$userNames,$starttime,$endtime);
		if(!$keywords || ($userNames && !$users) ) return false;
		$configs = $this->_getSphinxConfigs($this->_server_diary);
		list($host,$port) = ($configs) ? $configs : $this->_getSphinxConfig();
		$filter = $filterRange = array();
		($aids)  ? $filter[] = array('attribute' => 'aid','values' => $aids,'exclude' => false) : 0;
		($users) ? $filter[] = array('attribute' => 'uid','values' => array_keys($users),'exclude' => false) : 0;
		$privacy = $this->_getDiaryPrivacy();
		($privacy)  ? $filter[] = array('attribute' => 'privacy','values' => $privacy,'exclude' => false) : 0;
		$filterRange = array( array('attribute' => 'postdate','min' => $starttime,'max' => $endtime,'exclude' => false));
		$page = $page>1 ? $page : 1;
		$offset = intval(($page - 1) * $perpage);
		$this->_setDefaultSphinx();
		$this->_sphinxHost        = $host;
		$this->_sphinxPort        = $port;
		$this->_sphinxMode        = $this->_getSphinxMode($this->_sphinxMethod);
		$this->_sphinxFilter      = $filter;
		$this->_sphinxFilterRange = $filterRange;
		$this->_sphinxOffset      = $offset;
		$this->_sphinxLimit       = $perpage;
		$this->_sphinxKeywords    = $keywords;
		$this->_sphinxIndex       = $this->_getSphinxMap($this->_getDiaryRange($range));
		$result = $this->_sphinxAssemble();
		if ( $result === false ){
			return false;
		} 
		return $this->_buildSphinxResult($result,'id');
	}
	function _getDiaryRange($k){
		$ranges = array(1=>$this->_key_dindex,2=>$this->_key_dcindex,3=>$this->_key_daindex);
		return $ranges[$k] ? $ranges[$k] : $ranges[2];
	}
	function _getDiarys($dids,$keywords){
		if(!$dids) return array();
		$diarysDao = $this->getDiarysDao();
		if(!($result = $diarysDao->getsByDids($dids))){
			return array();
		} 
		return $this->_buildDiarys($result,$keywords);
	}
	
	/*****************************************************************************/
	function searchForums($keywords,$page=1,$perpage=20){
		return $this->_searchForums($keywords,$page,$perpage);
	}
	
	function searchGroups($keywords,$page=1,$perpage=20){
		return $this->_searchGroups($keywords,$page,$perpage);
	}
	/*
	 * 组装搜索结果页数据 总数/IDs/关键字
	 */
	function _buildSphinxResult($result,$id = 'id'){
		global $db_charset;
		L::loadClass('Chinese', 'utility/lang', false);
		$chs = new Chinese('utf8', $db_charset);
		foreach ( $result["words"] as $word => $info ){
			$words[] = $chs->Convert($word);
		}
		$totals = $result['total'];
		if ( is_array($result["matches"]) ){
			$ids ='';
			foreach ( $result["matches"] as $docinfo ){
				$ids && $ids.=',';
				$ids .= $docinfo[$id];
			}
			return array($totals,$ids,$words);
		}
		return false;
	}
	
	/**
	 * 全文索引聚合器 
	 * @version phpwind 8.0
	 * @return unknown_type
	 */
	function _sphinxAssemble(){
		$sphinxAPI = $this->_sphinxAPI;
		if(!$sphinxAPI) return false;
		$sphinxAPI->SetServer ( $this->_sphinxHost, (int)$this->_sphinxPort );
		$sphinxAPI->SetConnectTimeout ( 1 );
		$sphinxAPI->SetMatchMode ( $this->_sphinxMode );
		if($this->_sphinxFilter){
			foreach($this->_sphinxFilter as $filter){
				$sphinxAPI->SetFilter ($filter['attribute'],$filter['values'],$filter['exclude']);
			}
		}
		if($this->_sphinxFilterRange){
			foreach($this->_sphinxFilterRange as $filter){
				$sphinxAPI->SetFilterRange ($filter['attribute'],$filter['min'],$filter['max'],$filter['exclude']);
			}
		}
		$this->_sphinxGroupBy && $sphinxAPI->SetGroupBy ( $this->_sphinxGroupBy, $this->_sphinxGroup, "@group desc" );
		if ($this->_sphinxSortBy){
			$sphinxAPI->SetSortMode ( $this->_sphinxSort, $this->_sphinxSortBy );
		}else{
			$sphinxAPI->SetSortMode ( SPH_SORT_RELEVANCE );
		}
		$sphinxAPI->SetLimits ( $this->_sphinxOffset, $this->_sphinxLimit, $this->_sphinxMaxMatch );
		$sphinxAPI->SetRankingMode ( $this->_sphinxRanking );
		$sphinxAPI->SetArrayResult ( true );
		return $sphinxAPI->Query ( $this->charsetReverse($this->_sphinxKeywords), $this->_sphinxIndex );
	}
	/*
	 * 自定义通用全文索引扩展服务
	 * @version phpwind 8.3
	 */
	function sphinxSearcher($conditions,$primaryId = 'id'){
		$this->_sphinxAPI         = $this->_getSphinxAPI();
		$this->_sphinxHost        = isset($conditions['host']) ? $conditions['host'] : $this->_sphinx['host'];
		$this->_sphinxPort        = isset($conditions['port']) ? $conditions['port'] : $this->_sphinx['port'];
		$this->_sphinxFilter      = $conditions['filter'];
		$this->_sphinxFilterRange = $conditions['filterRange'];
		$this->_sphinxOffset      = $conditions['offset'];
		$this->_sphinxLimit       = $conditions['perpage'];
		$this->_sphinxKeywords    = $conditions['keywords'];
		$this->_sphinxIndex       = $conditions['index'];
		$this->_sphinxSortBy      = $conditions['sortby'];
		$this->_sphinxGroupBy     = $conditions['groupby'] ? $conditions['groupby'] : '';
		$this->_sphinxGroup       = $conditions['group'] ? $conditions['group'] : $this->_getSphinxGroup();
		$this->_sphinxSort        = $conditions['sort'] ? $conditions['sort'] : $this->_getSphinxSort($this->_sphinxOrder);
		$this->_sphinxRanking     = $conditions['ranking'] ? $conditions['ranking'] : $this->_getSphinxRanking();
		$this->_sphinxMode        = $conditions['mode'] ? $conditions['mode'] : $this->_getSphinxMode($this->_sphinxMethod);
		$result = $this->_sphinxAssemble();
		if ( $result === false ){
			return false;
		}
		return $this->_buildSphinxResult($result,$primaryId);
	}
	
	function charsetReverse($keyword){
		global $db_charset;
		if($this->_sphinxCharset == 2){
			return $keyword;
		}
		static $sCharset;
		if(!$sCharset){
			L::loadClass('Chinese', 'utility/lang', false);
			$sCharset = new Chinese($db_charset, 'utf8');
		}
		return $sCharset->Convert($keyword);
	}
	/**
	 * 设置默认Sphinx环境配置
	 * @return unknown_type
	 */
	function _setDefaultSphinx(){
		$this->_sphinxAPI         = $this->_getSphinxAPI();
		$this->_sphinxGroupBy     = '';
		$this->_sphinxSortBy      = 'postdate';
		$this->_sphinxGroup       = $this->_getSphinxGroup();
		$this->_sphinxSort        = $this->_getSphinxSort($this->_sphinxOrder);
		$this->_sphinxRanking     = $this->_getSphinxRanking();
	}
	/**
	 * 全文索引单功能服务器配置扩展  array('host'=>'','port'=>'')
	 */
	function _setSphinxConfigs(){
		return array(
			$this->_server_thread => array(),
			$this->_server_post   => array(),
			$this->_server_user   => array(),
			$this->_server_diary  => array(),
			$this->_server_weibo  => array(),
		);
	}
	/**
	 * 根椐类型获取单功能服务器配置
	 * @param $type
	 * @return unknown_type
	 */
	function _getSphinxConfigs($type){
		$configs = $this->_setSphinxConfigs();
		return isset($configs[$type]) ? $configs[$type] : '';
	}
	
	function _getSphinxSort($sort){
		return ($sort == $this->_sphinxOrder ) ? SPH_SORT_ATTR_DESC : SPH_SORT_ATTR_ASC;
	}
	/*
	 * 获取索引源
	 */
	function _getSphinxMap($index){
		$map = $this->_setSphinxMap();
		return ($this->_sphinx[$index]) ? $this->_sphinx[$index] : $map[$index];
	}
	
	/*
	 * 获取评分模式
	 */
	function _getSphinxRanking(){
		$default = $this->_getSphinxDefaults();
		return ($this->_sphinx['rank']) ? $this->_sphinx['rank'] : $default['rank'];
	}
	
	/*
	 * 获取分组模式
	 */
	function _getSphinxGroup(){
		$default = $this->_getSphinxDefaults();
		return ($this->_sphinx['group']) ? $this->_sphinx['group'] : $default['group'];
	}
	
	/*
	 * 设置对应图 主索引名称
	 * 注意，key不需要调整，只需要调整值
	 */
	function _setSphinxMap(){
		return array( 
			$this->_key_tindex   => "threadsindex",      #帖子标题索引
			$this->_key_tcindex  => "tmsgsindex",        #帖子内容索引
			$this->_key_taindex  => "threadsallindex",   #帖子索引
			$this->_key_pindex   => "postsindex",        #回复索引
			$this->_key_windex   => "weiboindex",        #新鲜事索引
			$this->_key_dindex   => "diarysindex",        #日志标题索引
			$this->_key_dcindex  => "diarycontentsindex", #日志内容索引
			$this->_key_daindex  => "diaryallsindex",     #日志索引
			$this->_key_mindex   => "membersindex",      #用户索引
		);
	}
	/*
	 * 获取默认值
	 */
	function _getSphinxDefaults(){
		return array ( 'isopen'  => 0, 
					   'host'    => 'localhost', 
					   'port'    => 3312,
					   'wordsegment_mode' => 1,
					   'rank'    => "SPH_RANK_PROXIMITY_BM25", 
					   'group'   => "SPH_GROUPBY_ATTR",
					   'tindex'  => "threadsindex",
					   'tcindex' => "tmsgsindex",
					   'pindex'  => "postsindex",
					   'windex'  => 'weiboindex',
					   'dindex'  => 'diarysindex',
					   'dcindex' => 'diarycontentsindex',
					   'cmsindex'=> 'cmsindex',					   
		);
	}
	
	/*
	 * 获取评分模式
	 */
	function _getSphinxRanks(){
		return array(1=>"SPH_RANK_PROXIMITY_BM25",2=>"SPH_RANK_BM25",3=>"SPH_RANK_NONE");;
	}
	
	/*
	 * 获取分组模式
	 */
	function _getSphinxGroups(){
		return array(1=>"SPH_GROUPBY_DAY",2=>"SPH_GROUPBY_WEEK",3=>"SPH_GROUPBY_MONTH",4=>"SPH_GROUPBY_YEAR",5=>"SPH_GROUPBY_ATTR");
	}
	
	/*
	 * 获取搜索模式
	 */
	function _getSphinxMode($method){
		return ( $method == $this->_sphinxMethod ) ? SPH_MATCH_ALL : SPH_MATCH_ANY;
	}
	
	/*
	 * 获取配置
	 */
	function _getSphinxConfig(){
		return array($this->_sphinx['host'],intval($this->_sphinx['port']));
	}
	
	/*
	 * 获取sphinx类
	 */
	function _getSphinxAPI(){
		L::loadClass('sphinx', 'utility', false);
		return new SphinxClient ();
	}
	
	/****************************分组数据服务****************************************/
	/**
	 * 搜索关键字版块分组信息
	 * @version phpwind 8.5
	 */
	function searchForumGroups($keywords,$range,$userNames="",$starttime="",$endtime="",$forumIds=array(),$page=1,$perpage=20,$expand=array()) {
		list($keywords,$users,$starttime,$endtime) = $this->_checkThreadConditions($keywords,$userNames,$starttime,$endtime);
		if(!$keywords || ($userNames && !$users )) return false;
		$configs = $this->_getSphinxConfigs($this->_server_thread);
		list($host,$port) = ($configs) ? $configs : $this->_getSphinxConfig();
		$filter = $filterRange = array();
		if($users){
			$filter[] = array('attribute' => 'authorid','values' => array_keys($users),'exclude' => false);
		}
		if($forumIds){
			$forumIds = (is_array($forumIds)) ? $forumIds : array($forumIds);
			$filter[] = array('attribute' => 'fid','values' => $forumIds,'exclude' => false);
		}
		if($this->_sphinxFilterIds){
			$filter[] = array('attribute' => 'fid','values' => $this->_sphinxFilterIds,'exclude' => true);
		}
		$filter[] = array('attribute' => 'fid','values' => array(0),'exclude' => true);
		$filterRange = array( array('attribute' => 'postdate','min' => $starttime,'max' => $endtime,'exclude' => false));
		$this->_sphinxAPI         = $this->_getSphinxAPI ();
		$this->_sphinxHost        = $host;
		$this->_sphinxPort        = $port;
		$this->_sphinxFilter      = $filter;
		$this->_sphinxFilterRange = $filterRange;
		$this->_sphinxGroupBy     = 'fid';
		$this->_sphinxKeywords    = $keywords;
		$this->_sphinxIndex = $this->_getSphinxMap($this->_getThreadRange($range));
		$result = $this->_sphinxGroupSearcher ();
		if ($result === false) {
			return false;
		}
		return $this->_buildGroupSphinxResult ( $result, 'fid' );
	}
	
	/**
	 * 全文索引分组聚合器 
	 * @version phpwind 8.5
	 * @return unknown_type
	 */
	function _sphinxGroupSearcher() {
		$sphinxAPI = $this->_sphinxAPI;
		if (! $sphinxAPI)
			return false;
		$sphinxAPI->SetServer ( $this->_sphinxHost, ( int ) $this->_sphinxPort );
		$sphinxAPI->SetConnectTimeout ( 1 );
		$sphinxAPI->SetMatchMode ( SPH_MATCH_ALL );
		if($this->_sphinxFilter){
			foreach($this->_sphinxFilter as $filter) {
				$sphinxAPI->SetFilter ($filter['attribute'],$filter['values'],$filter['exclude']);
			}
		}
		if($this->_sphinxFilterRange){
			foreach($this->_sphinxFilterRange as $filter){
				$sphinxAPI->SetFilterRange ($filter['attribute'],$filter['min'],$filter['max'],$filter['exclude']);
			}
		}
		$sphinxAPI->SetGroupBy ( $this->_sphinxGroupBy, SPH_GROUPBY_ATTR, "@count desc" );
		$sphinxAPI->SetLimits ( 0, 1000, 1000 );
		$sphinxAPI->SetArrayResult ( true );
		return $sphinxAPI->Query ( $this->charsetReverse ( $this->_sphinxKeywords ), $this->_sphinxIndex );
	}
	/**
	 * 全文索引分组聚合器 组装数组
	 * @version phpwind 8.5
	 * @return unknown_type
	 */
	function _buildGroupSphinxResult($result,$primaryId) {
		if (! is_array ( $result ["matches"] )) {
			return false;
		}
		$groups = array ();
		foreach ( $result ["matches"] as $docinfo ) {
			$attrs = $docinfo ['attrs'];
			if (! is_array ( $attrs )) {
				continue;
			}
			$groups [$attrs [$primaryId]] = $attrs ['@count'];
		}
		return $groups;
	}
	
	
}