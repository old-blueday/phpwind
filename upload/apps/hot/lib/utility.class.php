<?php
!defined('P_W') && exit('Forbidden');

@include_once (R_P . 'require/credit.php');
L::loadClass('rate', 'rate', false);
class HotModuleUtility {
	var $memberShare = array();
	var $filterTime = array();
	var $threadRate = array();
	var $picRate = array();
	var $diaryRate = array();
	var $credit;
	var $rate;
	function HotModuleUtility() {
		global $credit;
		$this->rate = new PW_Rate();
		$this->credit = & $credit;
		$this->filterTime = array("today"=>"今日","week"=>"最近7天","month"=>"最近30天","history"=>"历史");
		$this->memberShare = array('memberShareThread'=>"帖子",
					  		   	   'memberShareDiary'=>"日志",
					  		       'memberShareAlbum'=>"相册",
					  		   	   'memberShareUser'=>"用户",
					  		       'memberShareGroup'=>"群组",
					  		       'memberSharePic'=>"照片",
					  		       'memberShareLink'=>"网页",
					  		       'memberShareVideo'=>"视频",
					  		       'memberShareMusic'=>"音乐",
					  		       'memberShareAll'=>"全部");
	}
	/**
	 * @param $rt
	 * @return unknown_type
	 */
	function activeCurrentFilter($rt,$fTime,&$fType,$from='index') {
		$from == 'admin' && $s = "[".$rt["id"]."]";
		$fTypeData = ( array ) unserialize ( $rt ['filter_type'] );
		if ($fTypeData ['current']) {
			if($fType){
				$fTypeData ['current'] = $fType;
			}else{
				$fType = $fTypeData ['current'];
			}
			$result ['filterTypeData'] = $fTypeData;
			$tc = $this->getFilterSelect ( "fType$s", $this->getFilter ( $rt ['tag'], 'type' ), $fTypeData, '', $from );
			$result ['selectType'] = $tc['select'];
			$result ['currentType'] = $tc['ct'];
		}
		$fTimeData = ( array ) unserialize ( $rt ['filter_time'] );
		if ($fTimeData ['current']) {
			$fTime && $fTimeData ['current'] = $fTime;
			$result ['filterTimeData'] = $fTimeData;
			$tc = $this->getFilterSelect ( "fTime$s", $this->getFilter ( $rt ['tag'], 'time' ), $fTimeData, $rt ['type_name'],$from );
			$result ['selectTime'] = $tc['select'];
			$result ['currentTime'] = $tc['ct'];
		}
		!$result && $from == 'index' && $result = $rt['filter_type']; 
		return $result;
	}
	
	/**
	 * @param $hid
	 * @param $filters
	 * @param $filterData
	 * @param $typeName
	 * @return unknown_type
	 */
	function getFilterSelect($hid, $filters, $filterData, $typeName = '', $from='index') {
		$result = array();
		$onChange = "onchange=\"this.form.submit();\"";
		if ($from == 'admin') {
			 $onChange = "";
		}
		if ($filterData ['current'] && $filters) {
			$r = "<select name=\"$hid\" $onChange>";
			foreach ( $filters as $key => $value ) {
				$filterData ["filters"] [] = $filterData ["current"];
				if (in_array ( $key, $filterData ["filters"] )) {
					$checked = $filterData ["current"] == $key ? 'selected' : '';
					$checked == 'selected' && $result['ct'] = $value;
					$r .= "<option value=\"$key\" $checked > $value" . $typeName . " </option>";
				}
			}
			$r .= "</select>";
		}
		$result['select'] = $r;
		return $result;
	}
	
	/**
	 * @param $tag
	 * @param $type
	 * @return unknown_type
	 */
	function getFilter($tag, $type = 'type') {
		if ($type == 'time') {
			return $this->filterTime;
		}
		$rateTag = array("threadRate","diaryRate","picRate");
		if ($tag == "memberShare") {
			return $this->memberShare;
		} elseif (in_array($tag,$rateTag)) {
			return $this->getRate($tag);
		} else{
			$credit = $this->getCredit();
			return $credit['filterCredit'];
		}
	}
	
	/**
	 * @param $filter
	 * @param $target
	 * @return unknown_type
	 */
	function createParam($filter,$target){
		foreach ($filter as $key => $value) {
			$params[] = $target.'_'.$key;
		}
		return $params;
	}
	
	/**
	 * @param $filters
	 * @param $fFilter
	 * @param $typeName
	 * @param $title
	 * @return unknown_type
	 */
	function getFilterHtmlData($filters,$fFilter,$typeName,$title=''){
		if($fFilter['current']){
			$num = 0;
			foreach ($filters as $key => $value) {
				$checked = in_array($key,$fFilter['filters']) ? 'checked' : '';
				$result[] = array('checked'  => $checked,
								  'typeName' => $value.$title,
								  'itemName' => $typeName.$key,
								  'fAction'	 => $key,
								  'itemValue'=> $fFilter['filterItems'][$num]);
				$num++;
			}
		}
		return $result;
	}
	
	/**
	 * @return unknown_type
	 */
	function getCredit(){
		$cTypes = $this->credit->cType;
		$cUnit = $this->credit->cUnit;
		$result['filterCredit'] = $cTypes;
		$result['cUnit'] = $cUnit;
		return $result;
	}
	
	
	/**
	 * @param $tag
	 * @return unknown_type
	 */
	function getRate($tag){
		$rates = array();
		if ($tag == "threadRate" && $this->getRateSet($tag)) {
			$rates = $this->rate->getRateThreadHotTypes();
		}elseif($tag == "diaryRate" && $this->getRateSet($tag)){
			$rates = $this->rate->getRateDiaryHotTypes();
		}elseif($tag == "picRate" && $this->getRateSet($tag)){
			$rates = $this->rate->getRatePictureHotTypes();
		}
		return $rates;
	}
	/**
	 * @param $type 
	 * 		  1:thread 
	 * 		  2:diary 
	 * 		  3:pic
	 * @return unknown_type
	 */
	function getRateSet($tag){
		$result = 1;
		global $db_ratepower;
		$rateSets = unserialize($db_ratepower);
		$tag == 'threadRate' &&  $type = 1;
		$tag == 'diaryRate' && $type = 2;
		$tag == 'picRate' && $type = 3;
		$type && $result = $rateSets[$type];
		return $result;
	}
}
?>