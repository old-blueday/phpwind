<?php
define('SCR', 'searcher');
require_once ('global.php');
/* check user level */
!$_G['allowsearch'] && Showmsg('search_group_right');
if ($groupid!=3 && $groupid!=4) {
	list($db_opensch,$db_schstart,$db_schend) = explode("\t",$db_opensch);
	if ($db_opensch && (($db_schstart > $db_schend) && ($_time['hours']>$db_schend) && ($_time['hours']<$db_schstart) || ($db_schstart < $db_schend) && (($db_schstart>-1 && $_time['hours']<$db_schstart) || ($db_schend>-1 && $_time['hours']>=$db_schend)))) {
		Showmsg('search_opensch');
	}
}
initGP(array("keyword","type","fid","step","username","starttime","endtime","threadrange","diaryusername","diarystarttime","diaryendtime","diaryrange","page","fid","sch_time","digest",'authorid',"ttable","ptable"));
if($sch_time == 'newatc' || $digest  == 1 ){
	list($type,$conditon) = array( 'special',(($sch_time == 'newatc') ? 'latest' : 'digest') );
}
if($type && !in_array($type,array('thread','forum','user','diary','group','special'))){
	showMsg("抱歉,搜索类型不存在");
}
$isSphinx = ($db_sphinx['isopen'] > 0) ? true : false;/* is mysql or sphinx */
$searcherService = L::loadclass('searcher', 'search');
list($perpage,$searchURL) = array(20,'');
$threadrange  = ($threadrange > 1) ? $threadrange : 1;
$diaryrange   = ($diaryrange > 1) ? $diaryrange : 1;
$page = ($page > 1) ? $page : 1;
/* open thread type to search when no keyword */
if($type && ( $keyword || ( !$isSphinx && 'thread' == $type  ) || 'special' == $type )){
	(strtolower($GLOBALS['pwServer']['REQUEST_METHOD']) == "post") && checkVerify();
	if(!$isSphinx && 2 == $step ){
		if(!$searcherService->checkUserLevel()){
			Showmsg('search_limit');
		}
		if(!$searcherService->checkWaitSegment()){
			Showmsg('search_wait');
		}
	}
	$keyword = strip_tags($keyword);
	switch($type){
		case "thread":
			/* search range */
			$allowSearch = ( $_G['allowsearch'] > 0 && $_G['allowsearch'] == 3 ) ? array(2,3) : array(2);
			$threadrange = (in_array($threadrange,$allowSearch)) ? $threadrange : 1;
			$ptable = min(intval($ptable),count($db_plist));
			$ttable = min(intval($ttable),count($db_tlist));
			$expand = array( "ttable" => $ttable,"ptable" => $ptable);
			$threadrange = ( $keyword ) ? $threadrange : 1;
			list($total,$threads) = $searcherService->searchThreads($keyword,$threadrange,$username,$starttime,$endtime,$fid,$page,$perpage,$expand);
			$pager = ($total) ? numofpage($total,$page,ceil($total/$perpage),$searchURL."searcher.php?keyword=".urlencode($keyword)."&type=$type&threadrange=$threadrange&username=".urlencode($username)."&starttime=$starttime&endtime=$endtime&fid=$fid&",null,'',true) : '';
			break;
		case "forum":
			list($total,$forums) = $searcherService->searchForums($keyword,$page,$perpage);
			$pager = ($total) ? numofpage($total,$page,ceil($total/$perpage),$searchURL."searcher.php?keyword=".urlencode($keyword)."&type=$type&",null,'',true) : '';
			break;
		case "user":
			list($total,$users) = $searcherService->searchUsers($keyword,$page,$perpage);
			$pager = ($total) ? numofpage($total,$page,ceil($total/$perpage),$searchURL."searcher.php?keyword=".urlencode($keyword)."&type=$type&",null,'',true) : '';
			break;
		case "diary":
			list($total,$diarys) = $searcherService->searchDiarys($keyword,$diaryrange,$diaryusername,$diarystarttime,$diaryendtime,$page,$perpage);
			$pager = ($total) ? numofpage($total,$page,ceil($total/$perpage),$searchURL."searcher.php?keyword=".urlencode($keyword)."&type=$type&diaryrange=$diaryrange&diaryusername=".urlencode($diaryusername)."&diarystarttime=$diarystarttime&diaryendtime=$diaryendtime&",null,'',true) : '';
			break;
		case "group":
			list($total,$groups) = $searcherService->searchGroups($keyword,$page,$perpage);
			$pager = ($total) ? numofpage($total,$page,ceil($total/$perpage),$searchURL."searcher.php?keyword=".urlencode($keyword)."&type=$type&",null,'',true) : '';
			break;
		case "special": 
			list($total,$threads) = $searcherService->searchSpecial($conditon,$authorid,1,50);
			break;
	}
}

$types = array('thread'=>'帖子','user'=>'用户','forum'=>'版块','group'=>'群组','diary'=>'日志');
//$threadranges[$threadrange] = $diaryranges[$diaryrange] = "checked=checked";
$threadranges[1] = $diaryranges[1] = "checked=checked";
($total && $total < $perpage ) ? $perpage = $total : 0;
$type = ($type) ? $type : "thread";
if( 'thread' == $type ){
	/* thread forums */
	$forumadd = $forumcache = '';
	include D_P."data/bbscache/forumcache.php";
	$forumsService = L::loadClass('forums', 'forum');
	if($forums = $forumsService->getsNotCategory()){
		foreach($forums as $rt){
			$allowvisit = (!$rt['allowvisit'] || $rt['allowvisit'] != str_replace(",$groupid,",'',$rt['allowvisit'])) ? true : false;
			if ($rt['f_type']=='hidden' && $allowvisit) {
				$forumadd .= "<option value=\"$rt[fid]\"> &nbsp;|- $rt[name]</option>";
			} elseif ($rt['password'] || !$allowvisit) {
				$forumcache = preg_replace("/\<option value=\"$rt[fid]\"\>(.+?)\<\/option\>\\r?\\n/is",'',$forumcache);
			}
		}
	}
	if ($_G['allowsearch'] > 1 ) {
		$t_table = '';
		if ($db_plist && count($db_plist)>1) {
	
			$p_table = "<select name=\"ptable\">";
			foreach ($db_plist as $key=>$val) {
				$name = $val ? $val : ($key != 0 ? getLangInfo('other','posttable').$key : getLangInfo('other','posttable'));
				$p_table .= "<option value=\"$key\">".$name."</option>";
			}
			$p_table .= '</select>';
		}
		if ($db_tlist) {
			$t_table = '<select name="ttable">';
			foreach ($db_tlist as $key => $value) {
				$name = !empty($value['2']) ? $value['2'] : ($key == 0 ? 'tmsgs' : 'tmsgs'.$key);
				$t_table .= "<option value=\"$key\">$name</option>";
			}
			$t_table .= '</select>';
		}
	}
	if ($fid) {
		//$forumcache = preg_replace("/\<option value=\"$fid\"\>(.+?)\<\/option\>(\\r?\\n)/is","<option value=\"".$fid."\" selected>\\1</option>\\2",$forumcache);
		//$forumadd   = preg_replace("/\<option value=\"$fid\"\>(.+?)\<\/option\>(\\r?\\n)/is","<option value=\"".$fid."\" selected>\\1</option>\\2",$forumadd);
	}
	/* thread level */
	$isGM = CkInArray($windid,$manager);
	($isGM) ? $admincheck = 1 : 0;
	if(!$admincheck && $fid){
		$foruminfo = $forumsService->getForum($fid);
		$isBM = admincheck($foruminfo['forumadmin'],$foruminfo['fupadmin'],$windid);
		$pwSystem = pwRights($isBM,false,$fid);
		if ($pwSystem && ($pwSystem['tpccheck'] || $pwSystem['digestadmin'] || $pwSystem['lockadmin'] || $pwSystem['pushadmin'] || $pwSystem['coloradmin'] || $pwSystem['downadmin'] || $pwSystem['delatc'] || $pwSystem['moveatc'] || $pwSystem['copyatc'] || $pwSystem['topped'])) {
			$admincheck = 1;
		}
	}
	$superdelete = ($SYSTEM['superright'] && $SYSTEM['delatc'])  ? true : false;
	$superedit   = ($SYSTEM['superright'] && $SYSTEM['deltpcs']) ? true : false;
}
$className = ($total || $keyword) ? 'main_current' : 'main';
$hotwords = ($db_hotwords) ? explode(",",$db_hotwords) : array();
$navService = L::loadClass("navconfig", 'site'); /* @var $navService PW_Navconfig */
$navs = $navService->findValidNavListByTypeAndPostion(PW_NAV_TYPE_MAIN,'srch');
$navsLeft = $navService->findValidNavListByTypeAndPostion(PW_NAV_TYPE_HEAD_LEFT,'srch');

$navs = array_merge($navsLeft,$navs);
list($_Navbar,$_LoginInfo) = pwNavBar();
/*搜索自有底部导航*/
$_Navbar['foot'] = $navService->findValidNavListByTypeAndPostion(PW_NAV_TYPE_FOOT,'srch');
$_Navbar['head_right'] = $navService->findValidNavListByTypeAndPostion(PW_NAV_TYPE_HEAD_RIGHT,'srch');
//扩展搜索服务
$extendSearcher = L::loadClass('extendsearcher','search');
$rightSearchResult  = $extendSearcher->invokeSearcher('right_search',array('keyword'=>$keyword,'type'=>$type),false);
require_once printEOT('searcher_header');
require_once printEOT('searcher_headbar');
require_once printEOT('searcher');
//require_once printEOT('searcher_footer');
footer();
