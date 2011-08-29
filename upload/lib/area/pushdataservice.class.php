<?php
!defined('P_W') && exit('Forbidden');

class PW_PushDataService {
	var $_DAO;
	function PW_PushDataService() {
		$this->_pushdataDao = L::loadDB('pushdata', 'area');
	}
	function insertPushdata($array) {
		$temp_array = $this->_dealPushData($array);
		$temp = $this->_pushdataDao->insertData($temp_array);
		$this->_updateCacheDataPiece($array['invokepieceid']);
	}
	function editPushdata($id,$array) {
		$pushData	= $this->getPushDataById($id);
		if (!$pushData) return false;
		$temp_array = $this->_dealPushData($array);
		$this->_pushdataDao->update($id,$temp_array);
		$this->_updateCacheDataPiece($temp_array['invokepieceid']);
		if ($pushData['invokepieceid'] != $temp_array['invokepieceid']) {
			$this->_updateCacheDataPiece($pushData['invokepieceid']);
		}
	}
	function getEffectData($invokepieceid,$num=10) {
		$temp = array();
		$pushdatas = $this->_pushdataDao->getEffectInvokePushDatas($invokepieceid,$num);
		foreach ($pushdatas as $key=>$value) {
			$temp[] = $value['data'];
		}
		return $temp;
	}
	
	function verifyPushdata($id) {
		$pushdata = $this->getPushDataById($id);
		if (!$pushdata) return false;

		global $timestamp;
		$this->_updateCacheDataPiece($pushdata['id']);
		$this->_pushdataDao->update($id,array('starttime'=>$timestamp,'ifverify'=>0));
	}
	
	function _updateCacheDataPiece($invokepieceid) {
		$cacheDataService = L::loadClass('cacheDataService', 'area');
		$cacheDataService->updateCacheDataPiece($invokepieceid);
	}
	/**
	 * 
	 * @param $array invokepieceid 子模块ID
	 * @param $page
	 * @param $prePage
	 * @return unknown_type
	 */
	function searchPushdatas($array,$page,$prePage=20) {
		$temp = $this->_pushdataDao->searchPushDatas($array,$page,$prePage);
		foreach ($temp as $key=>$value) {
			$temp[$key] = $this->_initPushData($value);
		}
		return $temp;
	}
	function _initPushData($array) {
		$array['grade'] = $this->getGrade($array['vieworder']);
		$array['allpushtime'] = get_date($array['pushtime']);
		$array['pushtime'] = $this->_initPushTime($array['pushtime']);
		
		return $array;
	}
	function _initPushTime($time) {
		global $tdtime,$noformat;//当天时间
		$timeFormate = $time>$tdtime ? 'H:i' : 'm-d';
		$timeFormate=$noformat==1 ? 'Y-m-d H:i:s':$timeFormate;
		return get_date($time,$timeFormate);
	}
	function searchPushdatasCount($array,$page,$url,$prePage=20) {
		$total = $this->_pushdataDao->searchPushdatasCount($array);
		$numofpage = ceil($total/$prePage);
		foreach($array as $key=>$value) {
			$url.= "$key=$value&";
		}
		$numofpage < 1 && $numofpage = 1;
		$page > $numofpage && $page = $numofpage;
		return numofpage($total,$page,$numofpage,$url);
	}
	function deletePushdatas($ids) {
		if (!$ids || !is_array($ids)) return false;
		foreach ($ids as $value) {
			$this->deletePushdata($value);
		}
	}
	function deletePushdata($id) {
		$invokeService = L::loadClass('invokeservice', 'area');
		$pushdata = $this->getPushDataById($id);
		
		if (!$pushdata) return false;
		
		$this->_updateCacheDataPiece($pushdata['invokepieceid']);
		$this->_pushdataDao->delete($pushdata['id']);
	}
	function deletePushdataByPiece($invokePieceId) {
		$this->_pushdataDao->deleteByPiece($invokePieceId);
	}
	function getPushDataById($id) {
		return $this->_pushdataDao->get($id);
	}
	/*
	 * 获取推送数据的data数据
	 */
	function getPushData($id) {
		$temp = $this->getPushDataById($id);
		if (!$temp) return array();
		$data = $temp['data'];
		if (isset($temp['data']['title'])) {
			$temp['data']['title'] = strip_tags($temp['data']['title']);
		}
		return $temp;
	}
	
	function getTitleCss($push) {
		$temp = array();
		$temp['endtime']	= $push['titlecss']['endtime'] ? get_date($push['titlecss']['endtime'],'Y-m-d H:i') : '';
		$temp['color'] = (preg_match('/\#[0-9A-F]{6}/is',$push['titlecss']['color'])) ? $push['titlecss']['color'] : '';
		$temp['b'] = $push['titlecss']['b'] ? 'b one' : 'b';
		$temp['u'] = $push['titlecss']['u'] ? 'u one' : 'u';
		$temp['i'] = $push['titlecss']['i'] ? 'one' : '';
		return $temp;
	}
	function getGrade($num) {
		$num = (int) $num;
		switch ($num) {
			case 1 :
				return '一';
			case 2 :
				return '二';
			case 3 :
				return '三';
			case 4 :
				return '四';
			case 5 :
				return '五';
			default :
				return '普通';
		}
	}
	function _dealPushData($array) {
		global $timestamp;
		$array['starttime']	= $this->_startTimeToTime($array['starttime']);
		$array['titlecss']	= $this->pushDataTitleCss($array['titlecss']);
		$array['data']		= $this->_dealData($array['invokepieceid'],$array['data']);
		$array['vieworder']	= (int) $array['vieworder'];
		$array['ifbusiness']= (int) $array['ifbusiness'];
		$array['pushtime']	= $timestamp;
		return $array;
	}
	function _dealData($invokepieceid,$data) {
		$invokeService = L::loadClass('invokeservice', 'area');
		$invokepiece = $invokeService->getInvokePieceByInvokeId($invokepieceid);
		if (isset($invokepiece['param']['tagrelate'])) {
			require_once R_P.'mode/area/require/tagrelate.php';
			$tagrelate = S::escapeChar(S::getGP('tagrelate','P'));
			$data['tagrelate']	= getTagRelate($tagrelate);
		}
		if ($data['url']) {
			$data['url'] = str_replace('&#61;','=',$data['url']);
		}
		if(isset($data['image']) && count($_FILES) && $_FILES["uploadpic"]["name"] && $_FILES["uploadpic"]["size"]) {
			$uploadPicUrl = $this->uploadPicture($invokepiece['id']);
			$data['image'] = $uploadPicUrl ? $uploadPicUrl : $data['image'];
		}
		return $data;
	}
	function uploadPicture($invokePieceId) {
		L::loadClass('pushupload', 'upload', false);
		$img = new PushUpload($invokePieceId);
		PwUpload::upload($img);
		pwFtpClose($ftp);
		
		return $img->getImagePath();
	}

	function pushDataTitleCss($array) {
		$endtime = $this->_endTimeToTime($array['endtime']);
		return array(
			'color' => $array['color'],
			'b' => $array['b'],
			'i' => $array['i'],
			'u' => $array['u'],
			'endtime' => $endtime
		);
	}
	function _endTimeToTime($endtime) {
		if ($endtime && !is_numeric($endtime)) {
			$endtime	= PwStrtoTime($endtime);
			$endtime == -1 && $endtime = 0;
			return $endtime;
		}
		return 0;
	}

	function _startTimeToTime($starttime) {
		global $timestamp;
		if ($starttime && !is_numeric($starttime)) {
			$starttime	= PwStrtoTime($starttime);
			$starttime == -1 && $starttime = $timestamp;
			return $starttime;
		}
		return $timestamp;
	}
}