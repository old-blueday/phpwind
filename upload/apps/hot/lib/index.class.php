<?php
!defined('P_W') && exit('Forbidden');

@include_once (A_P . 'hot/lib/utility.class.php');
class HotDB {
	var $datanalyse;
	var $actions;
	var $utility;
	var $allModules = array();
	
	function HotDB($datanalyse){
		$this->datanalyse = $datanalyse;
		$this->actions = new DatanalyseAction();
		$this->utility = new HotModuleUtility();
	}
	
	function getData($top,$rt,$fTime,&$fType){
		global $units;
		switch ($top) {
			case 'memberHot':
				$result = $this->getUserHot($rt,$fTime,$fType);
				break;
			case 'threadHot':
				$result = $this->getThreadsHot($rt,$fTime,$fType);
				break;
			case 'diaryHot':
				$result = $this->getLogHot($rt,$fTime,$fType);
				break;
			case 'picHot':
				$result = $this->getPicHot($rt,$fTime,$fType);
				break;
			case 'forumHot':
				$result = $this->getForumHot($rt,$fTime,$fType);
				break;
		}
		if (!$result['unit']) { 
			$result['unit'] = $this->actions->getUnit($rt['tag']);
		}
		return $result;
	}
	
	function getForumHot($rt,$fTime,&$fType){
		$rt['tag'] == "forumPost" && $sortType = 'tpost';
		$rt['tag'] == "forumTopic" && $sortType = 'topic';
		$rt['tag'] == "forumArticle" && $sortType = 'article';
		$limit = 0;
		$limit = $rt['filter_type'];
		$result['data'] = $this->datanalyse->getSortData ($rt['tag'], null, $limit, $sortType);
		return $result;
	}
	
	function getPicHot($rt,$fTime,&$fType){
		$filter = $this->utility->activeCurrentFilter($rt,$fTime,$fType);
		$result['fTime'] = $filter['selectTime'];
		$result['fType'] = $filter['selectType'];
		$result['fTypeData'] = $filter['filterTypeData'];
		$result['fTimeData'] = $filter['filterTimeData'];
		$result['currentTime'] = $filter['currentTime'];
		$result['currentType'] = $filter['currentType'];
		$limit = $result['fTimeData']['filterItems'][array_search($result['fTimeData']['current'],(array)$result['fTimeData']['filters'])];
		$action_time = $result['fTimeData']['current'];
		$action_type = $result['fTypeData']['current'];
		if ($rt['tag'] == "picRate") { 
			$rt['tag'] = $action_type;
			$action_type = null;
		}
		$result ['data'] = $this->datanalyse->getSortData ( $rt['tag'], $action_time, $limit, $action_type);
		return $result;
	}
	
	function getLogHot($rt,$fTime,&$fType){
		$filter = $this->utility->activeCurrentFilter($rt,$fTime,$fType);
		$result['fTime'] = $filter['selectTime'];
		$result['fType'] = $filter['selectType'];
		$result['fTypeData'] = $filter['filterTypeData'];
		$result['fTimeData'] = $filter['filterTimeData'];
		$result['currentTime'] = $filter['currentTime'];
		$result['currentType'] = $filter['currentType'];
		$limit = $result['fTimeData']['filterItems'][array_search($result['fTimeData']['current'],(array)$result['fTimeData']['filters'])];
		$action_time = $result['fTimeData']['current'];
		$action_type = $result['fTypeData']['current'];
		if ($rt['tag'] == "diaryRate") {
			$rt['tag'] = $action_type;
			$action_type = null;
		}
		$result ['data'] = $this->datanalyse->getSortData ($rt['tag'], $action_time, $limit, $action_type);
		return $result;
	}
	
	function getThreadsHot($rt,$fTime,&$fType){
		$filter = $this->utility->activeCurrentFilter($rt,$fTime,$fType);
		$result['fTime'] = $filter['selectTime'];
		$result['fType'] = $filter['selectType'];
		$result['fTypeData'] = $filter['filterTypeData'];
		$result['fTimeData'] = $filter['filterTimeData'];
		$result['currentTime'] = $filter['currentTime'];
		$result['currentType'] = $filter['currentType'];
		$limit = $result['fTimeData']['filterItems'][array_search($result['fTimeData']['current'],(array)$result['fTimeData']['filters'])];
		$action_time = $result['fTimeData']['current'];
		$action_type = $result['fTypeData']['current'];
		if ($rt['tag'] == "threadRate") { 
			$rt['tag'] = $action_type;
			$action_type = null;
		}
		$result ['data'] = $this->datanalyse->getSortData ( $rt['tag'], $action_time, $limit, $action_type);
		return $result;
	}
	
	function getUserHot($rt,$fTime,&$fType){
		$credit = $this->utility->getCredit();
		$filter = $this->utility->activeCurrentFilter($rt,$fTime,$fType);
		if (!is_array($filter)) {
			$limit = $filter;
		}else{
			$result['fTime'] = $filter['selectTime'];
			$result['fType'] = $filter['selectType'];
			$result['fTypeData'] = $filter['filterTypeData'];
			$result['fTimeData'] = $filter['filterTimeData'];
			$result['currentTime'] = $filter['currentTime'];
			$result['currentType'] = $filter['currentType'];
			$limit = $result['fTimeData']['filterItems'][array_search($result['fTimeData']['current'],(array)$result['fTimeData']['filters'])];
			!$limit && $limit = $result['fTypeData']['filterItems'][array_search($result['fTimeData']['current'],(array)$result['fTimeData']['filters'])];
		}
		$action_time = $result['fTimeData']['current'];
		$action_type = $result['fTypeData']['current'];
		if($rt['tag'] == "memberCredit"){
			$cUnit = $credit['cUnit']; 
			$result['unit'] = $cUnit[$action_type];
		}elseif($rt['tag'] == "memberFriend"){
			$action_type = "f_num";
		}elseif($rt['tag'] == "memberShare"){
			$rt['tag'] = $action_type;
			$action_type = null;
		}
		$result['data'] = $this->datanalyse->getSortData($rt['tag'],$action_time,$limit,$action_type);
		return $result;
	}
	
	function getTabs() {
		$hotTabs = array();
		$modules = $this->_getAllModules();
		foreach($modules as $m){
			if (!empty($m['parent_id']) || !$this->utility->getRateSet($m['tag'])) {
				continue;
			}
			$hotTabs [] = array ('name' => $m ['type_name'], 'tag' => $m ['tag'], 'id' => $m ['id'] );
		}
		return $hotTabs;
	}
	
	/**
	 * @param $tag   当前所在的模块 $tag=0 返回所有模块
	 * @return unknown_type
	 */
	function getActiveModules($tag='0') {
		$result = array();
		$parentId = '';
		$modules = $this->_getAllModules();
		foreach($modules as $m){
			if($tag == '0'){break;}
			if($m['tag'] == $tag ){
				$parentId = $m['id'];break;
			}
		}
		foreach($modules as $m){
			if (!$this->utility->getRateSet($m['tag'])) {
				continue;
			}
			if ($tag == '0') {
				$result [$m ['tag']] = $m;
			}elseif ($m['parent_id']==$parentId) {
				$result [$m ['tag']] = $m;
			}
		}
		return $result;
	}
	
	function _getAllModules(){
		if($this->allModules){
			return $this->allModules;
		}
		global $db;
		$query = $db->query ( "SELECT * FROM pw_modehot ORDER BY sort ASC" );
		while ( $rt = $db->fetch_array ( $query ) ) {
			if(!empty($rt['active'])){
				$this->allModules[] = $rt;
			}
		}
		return $this->allModules;
	}
	
	function getAllDisplayModules(){
		$result = array();
		$modules = $this->_getAllModules();
		foreach($modules as $m){
			if(empty($m['display']) || !$this->utility->getRateSet($m['tag'])){
				continue;
			}
			if ($m['parent_id']) { 
				$result[$m['parent_id']][] = $m;
			}else{
				$result['parent'][] = $m;
			}
		}
		return $result;
	}
}
?>