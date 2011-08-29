<?php
define ( 'SCR', 'searcher' );
require_once ('global.php');
$_searchHelper = new PW_SearchHelper ();
$_searchHelper->checkLevel ();
S::gp ( array ("keyword", "type", "condition", "fid", "step", "username", "starttime", "endtime", "threadrange", "diaryusername", "diarystarttime", "diaryendtime", "diaryrange", "page", "fid", "sch_time", "digest", 'authorid', "ttable", "ptable", 'sortby' ) );
if ($sch_time == 'newatc' || $digest == 1 || $sch_time == 'today') {
	list ( $type, $condition ) = $_searchHelper->getSpecialCondition ();
}
$searchPassType = $db_search_type ? array_keys ( $db_search_type ) : array_keys($_searchHelper->getDefaultSearcherType());
if ($type && ! in_array ( $type, array_merge ( array ('special' ), ( array ) $searchPassType ) )) {
	showMsg ( "抱歉,搜索类型不存在" );
}
$searcherService = L::loadclass ( 'searcher', 'search' ); /* @var $searcherService PW_Searcher */
list ( $page, $isSphinx, $threadrange, $diaryrange ) = $_searchHelper->initCondition ( $page, $threadrange, $diaryrange );
list ( $perpage, $searchURL ) = array (20, '' );

if ($type && ! $keyword) {//默认数据
	$perpage = 50;
	switch ($type) {
		case "thread" :
			pwCache::getData ( D_P . 'data/bbscache/search_config.php');
			list ( $searchForumPart1, $searchForumPart2 ) = $_searchHelper->getSearchForum ();
			$timesFilterList =  $_searchHelper->getTimesFilterListByPostTimes ( array(1,24,168,720) );
			$expandCondition = array ( 'fid' => $fid, 'starttime' => $starttime, 'endtime' => $endtime);
			list ( $total, $threads ) = $searcherService->searchDefault ($type, $page, $perpage, $expandCondition);
			$pager = ($total) ? numofpage ( $total, $page, ceil ( $total / $perpage ), $searchURL . "searcher.php?type=$type&fid=$fid&starttime=$starttime&endtime=$endtime&sortby=$sortby&", null, '', true ) : '';
			break;
		case "forum" :
			list ( $total, $forums ) = $searcherService->searchDefault ( $type, $page, $perpage );
			$forums = $total ? $_searchHelper->buildForums ( $forums ) : array ();
			$pager = ($total) ? numofpage ( $total, $page, ceil ( $total / $perpage ), $searchURL . "searcher.php?type=$type&", null, '', true ) : '';
			break;
		case "user" :
			list ( $total, $users ) = $searcherService->searchDefault ( $type, $page, $perpage );
			$pager = ($total) ? numofpage ( $total, $page, ceil ( $total / $perpage ), $searchURL . "searcher.php?type=$type&", null, '', true ) : '';
			break;
		case "diary" :
			$timesFilterList =  $_searchHelper->getTimesFilterListByPostTimes ( array(1,24,168,720) );
			$expandCondition = array ('starttime' => $diarystarttime, 'endtime' => $diaryendtime);
			list ( $total, $diarys ) = $searcherService->searchDefault ( $type, $page, $perpage, $expandCondition);
			$pager = ($total) ? numofpage ( $total, $page, ceil ( $total / $perpage ), $searchURL . "searcher.php?type=$type&", null, '', true ) : '';
			break;
		case "group" :
			list ( $total, $groups ) = $searcherService->searchDefault ( $type, $page, $perpage );
			$pager = ($total) ? numofpage ( $total, $page, ceil ( $total / $perpage ), $searchURL . "searcher.php?type=$type&", null, '', true ) : '';
			break;
		case "special" :
			pwCache::getData ( D_P . 'data/bbscache/search_config.php');
			list ( $searchForumPart1, $searchForumPart2 ) = $_searchHelper->getSearchForum ();
			$timesFilterList =  $_searchHelper->getTimesFilterListByPostTimes ( array(1,24,168,720) );
			$expandCondition = array ( 'fid' => $fid, 'starttime' => $starttime, 'endtime' => $endtime);
			list ( $total, $threads ) = $searcherService->searchSpecial ( $condition, $authorid, $page, $perpage , $expandCondition);
			$pager = ($total) ? numofpage ( $total, $page, ceil ( $total / $perpage ), $searchURL . "searcher.php?type=$type&condition=$condition&authorid=$authorid&fid=$fid&starttime=$starttime&endtime=$endtime&", null, '', true ) : '';
			break;
		default :
			$_extendSearcher = L::loadClass ( 'extendsearcher', 'search' );
			$_searcherService = $_extendSearcher->extendSearcher ( $type );
			list ( $total, $lists ) = $_searcherService->searchDefault ( $page, $perpage );
			$pager = ($total) ? numofpage ( $total, $page, ceil ( $total / $perpage ), $searchURL . "searcher.php?type=$type&keyword=$keyword&", null, '', true ) : '';
			break;
	}
}

//*帖子搜索当没有关键字有用户情况走mysql搜索
$isUseMysqlWithThread = (S::inArray($type, array('thread', 'diary')) && (!$keyword && ($username || $diaryusername))) ? true : false;

if (($type && $keyword) || $isUseMysqlWithThread) {
	(strtolower ( $GLOBALS ['pwServer'] ['REQUEST_METHOD'] ) == "post") && checkVerify ();
	if (! $isSphinx && 2 == $step) {
		if (! $searcherService->checkUserLevel ()) {
			Showmsg ( 'search_limit' );
		}
		if (! $searcherService->checkWaitSegment ()) {
			Showmsg ( 'search_wait' );
		}
	}
	$keyword = strip_tags ( $keyword );
	//* @include_once pwCache::getPath ( D_P . 'data/bbscache/search_config.php' );
	pwCache::getData ( D_P . 'data/bbscache/search_config.php');
	switch ($type) {
		case "thread" :
			list ( $searchForumPart1, $searchForumPart2 ) = $_searchHelper->getSearchForum ();
			$adverts = $_searchHelper->getSearchAdvert ( $keyword );
			$timesFilterList =  $_searchHelper->getTimesFilterListByPostTimes ( array(1,24,168,720) );
			$allowSearch = ($_G ['allowsearch'] > 0 && $_G ['allowsearch'] == 3) ? array (2, 3 ) : array (2 ); /* search range */
			$threadrange = (in_array ( $threadrange, $allowSearch )) ? $threadrange : 1;
			$ptable = min ( intval ( $ptable ), count ( $db_plist ) );
			$ttable = min ( intval ( $ttable ), count ( $db_tlist ) );
			$q_sortby = $sortby = $_searchHelper->getSearchSortby ($sortby);
			$sortby = $sortby != 'relation' ? $sortby : '';
			$expand = array ("ttable" => $ttable, "ptable" => $ptable, 'sortby' =>$sortby);
			list ( $total, $threads ) = $searcherService->searchThreads ( $keyword, $threadrange, $username, $starttime, $endtime, $fid, $page, $perpage, $expand );
			$pager = ($total) ? numofpage ( $total, $page, ceil ( $total / $perpage ), $searchURL . "searcher.php?keyword=" . urlencode ( $keyword ) . "&type=$type&threadrange=$threadrange&username=" . urlencode ( $username ) . "&starttime=$starttime&endtime=$endtime&fid=$fid&sortby=$q_sortby&", null, '', true ) : '';			
			$forumsTotal = $_searchHelper->searchForumGroups($keyword, $threadrange, $username, $starttime, $endtime, $fid, $page, $perpage, $expand);
			break;
		case "forum" :
			list ( $total, $forums ) = $searcherService->searchForums ( $keyword, $page, $perpage );
			$forums = $total ? $_searchHelper->buildForums ( $forums ) : array ();
			$pager = ($total) ? numofpage ( $total, $page, ceil ( $total / $perpage ), $searchURL . "searcher.php?keyword=" . urlencode ( $keyword ) . "&type=$type&", null, '', true ) : '';
			break;
		case "user" :
			list ( $total, $users ) = $searcherService->searchUsers ( $keyword, $page, $perpage );
			$pager = ($total) ? numofpage ( $total, $page, ceil ( $total / $perpage ), $searchURL . "searcher.php?keyword=" . urlencode ( $keyword ) . "&type=$type&", null, '', true ) : '';
			break;
		case "diary" :
			$adverts = $_searchHelper->getSearchAdvert ( $keyword );
			$timesFilterList =  $_searchHelper->getTimesFilterListByPostTimes ( array(1,24,168,720) );
			list ( $total, $diarys ) = $searcherService->searchDiarys ( $keyword, $diaryrange, $diaryusername, $diarystarttime, $diaryendtime, $page, $perpage );
			$pager = ($total) ? numofpage ( $total, $page, ceil ( $total / $perpage ), $searchURL . "searcher.php?keyword=" . urlencode ( $keyword ) . "&type=$type&diaryrange=$diaryrange&diaryusername=" . urlencode ( $diaryusername ) . "&diarystarttime=$diarystarttime&diaryendtime=$diaryendtime&", null, '', true ) : '';
			break;
		case "group" :
			list ( $total, $groups ) = $searcherService->searchGroups ( $keyword, $page, $perpage );
			$pager = ($total) ? numofpage ( $total, $page, ceil ( $total / $perpage ), $searchURL . "searcher.php?keyword=" . urlencode ( $keyword ) . "&type=$type&", null, '', true ) : '';
			break;
		default :
			$adverts = $_searchHelper->getSearchAdvert ( $keyword );
			$_extendSearcher = L::loadClass ( 'extendsearcher', 'search' );
			$_searcherService = $_extendSearcher->extendSearcher ( $type );
			$conditions = array ('keywords' => $keyword, 'username' => $username, 'starttime' => $starttime, 'endtime' => $endtime, 'authorid' => $authorid );
			list ( $total, $lists ) = $_searcherService->search ( $conditions, $page, $perpage );
			$pager = ($total) ? numofpage ( $total, $page, ceil ( $total / $perpage ), $searchURL . "searcher.php?type=$type&keyword=$keyword&", null, '', true ) : '';
			break;
	}
}

$typeTitle = $_searchHelper->getTypeTitle ( $type, $condition );

$seoService = L::loadClass ( 'searcherseo', 'search' ); /* @var $seoService PW_SearchSEO */
$webPageTitle = $seoService->getPageTitle ( $typeTitle, $keyword );

$totaltime = number_format ( (pwMicrotime () - $P_S_T), 6 );

$threadranges [1] = $diaryranges [1] = "checked=checked";

$total = ( int ) $total;

$type = ($type) ? $type : "thread";
//版块信息
list ( $forumcache, $p_table, $t_table, $forumadd ) = $_searchHelper->getForumHtml ( $type );
//帖子管理权限
list ( $admincheck, $superdelete, $superedit ) = $_searchHelper->getThreadLevel ( $type, $fid );
//关键字统计功能
$_searchHelper->keywordStatistic ( $keyword );

//热门关键字更新
$_searchHelper->updateHotwords ();

//热门搜索
$hotwords = ($db_hotwords) ? explode ( ",", $db_hotwords ) : array ();
//导航
$_Navbar = $_searchHelper->getSearchNav ();
//扩展搜索服务
$extendSearcher = L::loadClass ( 'extendsearcher', 'search' );
$rightSearchResult = $extendSearcher->invokeSearcher ( 'right_search', array ('keyword' => $keyword, 'type' => $type ), false );
require_once printEOT ( 'searcher_header' );
require_once printEOT ( 'searcher_headbar' );
require_once printEOT ( 'searcher' );
//require_once printEOT('searcher_footer');
footer ();

/**
 * 视图
 * @author luomingqu 2010-11-16
 * @version phpwind 8.3
 */
class PW_SearchHelper {
	function PW_SearchHelper() {
	
	}
	
	function getTypeTitle($type, $condition) {
		if ($type && $condition) {
			return $this->_getSpecialTypeTitle ( $condition );
		}
		return $this->_getTypeTitle ( $type );
	}
	
	function getSpecialCondition() {
		global $digest, $sch_time;
		if ($digest == 1) {
			return array ('special', 'digest' );
		}
		switch ($sch_time) {
			case "newatc" :
				return array ('special', 'latest' );
				break;
			case 'today' :
				return array ('special', 'today' );
				break;
		}
		return true;
	}
	
	function _getSpecialTypeTitle($condition) {
		if (! $condition)
			return array ();
		$_lang = array ('digest' => '精华帖', 'latest' => '最新帖', 'today' => '今日帖' );
		return $_lang [$condition];
	}
	
	function _getTypeTitle($type) {
		global $db_search_type;
		if ($db_search_type) {
			return $db_search_type [$type];
		}
		$defaultType = $this->getDefaultSearcherType();
		return $defaultType[$type];
	}
	
	function getSearchNav() {
		$navService = L::loadClass ( "navconfig", 'site' );
		$tmpNav ['main'] = $navService->findValidNavListByTypeAndPostion ( PW_NAV_TYPE_MAIN, 'srch' );
		$tmpNav ['foot'] = $navService->findValidNavListByTypeAndPostion ( PW_NAV_TYPE_FOOT, 'srch' );
		if (! S::isArray ( $tmpNav ))
			return array (false, false );
		$tmpMainNav = $tmpNav ['main'];
		$tmpNav ['main'] ['few'] = array_slice ( $tmpMainNav, 0, 4 );
		if (count ( $tmpMainNav ) <= 4)
			return $tmpNav;
		$tmpNav ['main'] ['more'] = array_slice ( $tmpMainNav, 4 );
		return $tmpNav;
	}
	
	function keywordStatistic($keyword) {
		L::loadClass ( 'keywordstatistic', 'search/userdefine' );
		$keywordStatisticServer = new PW_KeywordStatistic ();
		$keywordStatisticServer->init ( $keyword );
		$keywordStatisticServer->execute ();
		return true;
	}
	
	function updateHotwords() {
		global $db_hotwordsconfig,$db_hotwordlasttime,$timestamp;
		if (!$db_hotwordsconfig) return false;
		$_config = unserialize($db_hotwordsconfig);
		if (!$_config['openautoinvoke']) return false; 
		$_timeNode = 90000;
		if ($timestamp - $db_hotwordlasttime < $_timeNode) return false;
		$_autoInvoke = array('isOpne'=> $_config['openautoinvoke'], 'period'=>$_config['invokeperiod']);
		L::loadClass ( 'hotwordssearcher', 'search/userdefine' );
		$hotwordsServer = new PW_HotwordsSearcher ();
		$hotwordsServer->update($_autoInvoke, $_config['shownum']);
		return true;
	}
	
	function initCondition($page, $threadrange, $diaryrange) {
		global $db_sphinx;
		$isSphinx = ($db_sphinx ['isopen'] > 0) ? true : false; /* is mysql or sphinx */
		$threadrange = ($threadrange > 1) ? $threadrange : 1;
		$diaryrange = ($diaryrange > 1) ? $diaryrange : 1;
		$page = ($page > 1) ? $page : 1;
		return array ($page, $isSphinx, $threadrange, $diaryrange );
	}
	
	function checkLevel() {
		global $_G, $groupid, $db_opensch, $db_schstart, $db_schend, $_time;
		! $_G ['allowsearch'] && Showmsg ( 'search_group_right' );
		if ($groupid != 3 && $groupid != 4) {
			list ( $db_opensch, $db_schstart, $db_schend ) = explode ( "\t", $db_opensch );
			if ($db_opensch && (($db_schstart > $db_schend) && ($_time ['hours'] > $db_schend) && ($_time ['hours'] < $db_schstart) || ($db_schstart < $db_schend) && (($db_schstart > - 1 && $_time ['hours'] < $db_schstart) || ($db_schend > - 1 && $_time ['hours'] >= $db_schend)))) {
				Showmsg ( 'search_opensch' );
			}
		}
		return true;
	}
	
	function getForumHtml($type) {
		global $_G, $db_plist, $db_tlist, $groupid, $db_filterids;
		if (! s::inArray ( $type, array ('thread', 'special' ) )) {
			return array ('', '', '' );
		}
		$forumadd = $forumcache = '';
		$notAllowedFids = $db_filterids ? explode(',',$db_filterids) : array();
		//* include pwCache::getPath ( D_P . "data/bbscache/forumcache.php" );
		extract(pwCache::getData( D_P . "data/bbscache/forumcache.php" , false));
		$_forumsService = L::loadClass ( 'forums', 'forum' ); /* @var $_forumsService PW_Forums */
		if ($forums = $_forumsService->getsNotCategory ()) {
			foreach ( $forums as $rt ) {
				$allowvisit = (! $rt ['allowvisit'] || $rt ['allowvisit'] != str_replace ( ",$groupid,", '', $rt ['allowvisit'] )) ? true : false;
				if ($rt ['f_type'] == 'hidden' && $allowvisit) {
					$forumadd .= "<option value=\"$rt[fid]\"> &nbsp;|- $rt[name]</option>";
				} elseif ($rt ['password'] || ! $allowvisit || S::inArray($rt['fid'], $notAllowedFids)) {
					$forumcache = preg_replace ( "/\<option value=\"$rt[fid]\"\>(.+?)\<\/option\>\\r?\\n/is", '', $forumcache );
				}
			}
		}
		if ($_G ['allowsearch'] > 1) {
			$t_table = '';
			if ($db_plist && count ( $db_plist ) > 1) {
				$p_table = "<select name=\"ptable\">";
				foreach ( $db_plist as $key => $val ) {
					$name = $val ? $val : ($key != 0 ? getLangInfo ( 'other', 'posttable' ) . $key : getLangInfo ( 'other', 'posttable' ));
					$p_table .= "<option value=\"$key\">" . $name . "</option>";
				}
				$p_table .= '</select>';
			}
			if ($db_tlist) {
				$t_table = '<select name="ttable">';
				foreach ( $db_tlist as $key => $value ) {
					$name = ! empty ( $value ['2'] ) ? $value ['2'] : ($key == 0 ? 'tmsgs' : 'tmsgs' . $key);
					$t_table .= "<option value=\"$key\">$name</option>";
				}
				$t_table .= '</select>';
			}
		}
		return array ($forumcache, $p_table, $t_table, $forumadd );
	}
	
	function getThreadLevel($type, $fid) {
		if (! in_array ( $type, array ('thread', 'special' ) )) {
			return array ('', '', '' );
		}
		global $windid, $manager, $SYSTEM;
		/* thread level */
		$isGM = S::inArray ( $windid, $manager );
		($isGM) ? $admincheck = 1 : 0;
		if (! $admincheck && $fid) {
			$_forumsService = L::loadClass ( 'forums', 'forum' );
			$foruminfo = $_forumsService->getForum ( $fid );
			$isBM = admincheck ( $foruminfo ['forumadmin'], $foruminfo ['fupadmin'], $windid );
			$pwSystem = pwRights ( $isBM, false, $fid );
			if ($pwSystem && ($pwSystem ['tpccheck'] || $pwSystem ['digestadmin'] || $pwSystem ['lockadmin'] || $pwSystem ['pushadmin'] || $pwSystem ['coloradmin'] || $pwSystem ['downadmin'] || $pwSystem ['delatc'] || $pwSystem ['moveatc'] || $pwSystem ['copyatc'] || $pwSystem ['topped'])) {
				$admincheck = 1;
			}
		}
		$superdelete = ($SYSTEM ['superright'] && $SYSTEM ['delatc']) ? true : false;
		$superedit = ($SYSTEM ['superright'] && $SYSTEM ['deltpcs']) ? true : false;
		return array ($admincheck, $superdelete, $superedit );
	}

	/**
	 * 时间筛选列表 array(1=>1小时内,24=>一天内,168=>一周内，720=>一个月内)
	 * @param int $postTimes
	 * @return array
	 */
	function getTimesFilterListByPostTimes($postTimes = array(1,24,168,720)) {
		if (!$postTimes || !S::isArray($postTimes)) return array('','');
		$postTimeMapList = array(1=>"1小时内",24=>"一天内",168=>"一周内",720=>"一个月内");
		$result = $temp = array();
		foreach ($postTimes as $value) {
			$value = intval($value);
			$temp['title'] = $postTimeMapList[$value];
			list($temp['starttime'],$temp['endtime']) = $this->_getStartAndEndTimeByPostTimes($value);
			$result[] = $temp;
		}
		return $result;
	}
	
	function _getStartAndEndTimeByPostTimes($posttime) {
		global $timestamp;
		if (! $posttime)
			return array ('', '');
		$posttime = ( int ) $posttime;
		$starttime = $timestamp - $posttime * 60 * 60;
		$endtime = $timestamp;
		$starttime = get_date ( $starttime, 'Y-m-d H:i' );
		$endtime = get_date ( $endtime, 'Y-m-d H:i' );
		return array ($starttime, $endtime );
	}
	
	/**
	 * 解析搜索推荐数组 
	 * @param unknown_type $array
	 */
	function getSearchForum() {		
		global $s_searchforumdb;
		if (! $s_searchforumdb) {
			return array ($this->_getBBSForum (), array () );
		}
		$tempPart1 = $tempPart2 = array();
		$forums = $this->_getBBSForum();
		$tempPart1 = $s_searchforumdb;
		$tempPart2 = array_diff($forums, $tempPart1);
		return array ($tempPart1, $tempPart2 );
	}
	
	function _getBBSForum() {
		//* include_once pwCache::getPath ( D_P . 'data/bbscache/forum_cache.php' );
		extract(pwCache::getData( D_P . 'data/bbscache/forum_cache.php' , false));
		if (! s::isArray ( $forum ))
			return array ();
		$result = array ();
		foreach ( $forum as $key => $val ) {
			if ($val ['type'] == 'category' || $val ['f_type'] == 'hidden')
				continue;
			$result [$val ['fid']] = $val ['name'];
		}
		return $result;
	}
	
	function buildForums($forums) {
		if (! $forums || ! s::isArray ( $forums ))
			return array ();
		$result = array ();
		$myFavorforum = pwGetMyShortcut ();
		$myFavorFids = $myFavorforum ? array_keys ( $myFavorforum ) : array ();
		foreach ( $forums as $t ) {
			$t ['favor'] = S::inArray ( $t ['fid'], $myFavorFids ) ? 1 : 0;
			$t ['admin'] = $this->_getForumAdmin ( $t );
			$result [] = $t;
		}
		return $result;
	}
	
	function _getForumAdmin($forum) {
		if (! $forum ['forumadmin'])
			return false;
		$forumadmins = explode ( ',', $forum ['forumadmin'] );
		$count = count ( $forumadmins );
		$forumadmin = '';
		foreach ( $forumadmins as $value ) {
			if (! $value)
				continue;
			$forumadmin .= '<a href="u.php?username=' . rawurlencode ( $value ) . "\">$value</a> , ";
		}
		$forumadmin = trim ( $forumadmin, " , " );
		return $forumadmin;
	}
	
	function getSearchSortby($sortby) {
		if (!$sortby) return 'postdate';
		if (!S::inArray($sortby, array('relation','lastpost','postdate','replies'))) return 'postdate';
		return $sortby;	
	}
	
	/**
	 * 获得搜索广告
	 * 
	 */
	function getSearchAdvert($keyword) {
		global $timestamp, $s_advertdb, $_time;
		if (! $keyword || ! $s_advertdb || ! s::isArray ( $s_advertdb ))
			return false;
		$hours = $_time ['hours'] + 1;
		$result = $advertdb = array ();
		foreach ( $s_advertdb as $key => $value ) {
			if (strpos ( $value ['keyword'], $keyword ) === false)
				continue;
			$advertdb [] = $value;
		}
		foreach ( $advertdb as $key => $value ) {
			if ($value ['starttime'] > $timestamp || $value ['endtime'] < $timestamp || ($value ['dtime'] && strpos ( ",{$value['dtime']},", ",{$hours}," ) === false))
				continue;
			if ((! $value ['ddate'] && ! $value ['dweek']) || ($value ['ddate'] && strpos ( ",{$value['ddate']},", ",{$_time['day']}," ) !== false) || ($value ['dweek'] && strpos ( ",{$value['dweek']},", ",{$_time['week']}," ) !== false)) {
				$result [] = str_replace ( $keyword, '<font color="red"><u>' . $keyword . '</u></font>', $value ['code'] );
			}
		}
		return $result;
	}
	
	function searchForumGroups($keywords,$threadrange,$userNames="",$starttime="",$endtime="", $forumIds = array(), $page = 1, $perpage = 20, $expand = array()) {
		global $searcherService, $s_searchforumdb, $isSphinx;
		if (!$isSphinx) return array();
		$forumIds = $this->_getBBSForum();
		$forumIds = $forumIds ? array_keys($forumIds) : array();
		return $searcherService->searchForumGroups($keywords, $threadrange, $userNames, $starttime, $endtime, $forumIds, $page = 1, $perpage = 20, $expand = array());			
	}
	
	function getDefaultSearcherType() {
		return array ('thread'=>'帖子','diary'=>'日志','user'=>'用户','forum'=>'版块','group'=>'群组');
	}
}
?>