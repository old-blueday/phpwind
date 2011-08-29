<?php
!defined('P_W') && exit('Forbidden');
/**
 * 门户化页面模块配置相关
 * @author xiejin
 *
 */
class PW_PageInvokeService{
	/**
	 * 111
	 */
	function getChannelInvokesForSelect($alias, $ifverify = 0,$ifHTML=0) {
		return $this->getPageInvokesForSelect('channel', $alias, $ifverify,$ifHTML);
	}
	/**
	 * 111
	 */
	function getPortalInvokesForSelect($alias, $ifverify = 0,$ifHTML=0) {
		return $this->getPageInvokesForSelect('other', $alias, $ifverify,$ifHTML);
	}
	/**
	 * 111
	 */
	function getPageInvokesForSelect($scr, $sign, $ifverify = 0,$ifHTML=0) {
		$temp = array();
		$invokes = $this->getEffectPageInvokes($scr, $sign, $ifverify);
		foreach ($invokes as $invoke) {
			if (!$invoke['pieces'] && !$ifHTML) continue; 
			$temp[$invoke['invokename']] = array('invokename'=>$invoke['invokename'],'title'=>$invoke['title'],'pieces'=>$invoke['pieces']);
		}
		return $temp;
	}
	/**
	 * 111
	 */
	function delPageInvoke($id) {
		$pageInvokeDB = $this->_getPageInvokeDB();
		$pageInovke = $pageInvokeDB->get($id);
		if ($pageInovke && $pageInovke['state']) {
			$invokeService = $this->_getInvokeSevice();
			$invokeService->deleteInvoke($pageInovke['invokename']);
			$pageInvokeDB->delete($id);
		}
	}
	/**
	 * 111
	 */
	function deletePageInvokesForChannel($alias) {
		$pageInvokeDB = $this->_getPageInvokeDB();
		$pageInvokeDB->deleteByScrAndSign('channel', $alias);
	}
	/**
	 * 111
	 */
	function addPageInvoke($scr, $sign, $invokeName, $pieces) {
		$pageInvokeDB = $this->_getPageInvokeDB();
		$temp = $pageInvokeDB->getByUnique($scr, $sign, $invokeName);
		if (!$temp) {
			return $pageInvokeDB->insertData(array('scr' => $scr, 'sign' => $sign, 'invokename' => $invokeName, 
				'pieces' => $pieces));
		}
		return $pageInvokeDB->update($temp['id'], array('pieces' => $pieces));
	}
	/**
	 * 111
	 */
	function searchPageInvokes($array, $page, $preg = 20) {
		$pageInvokeDB = $this->_getPageInvokeDB();
		return $pageInvokeDB->searchPageInvokes($array, $page, $preg);
	}
	/**
	 * 111
	 */
	function sreachPageInvokesPages($array, $page, $url, $preg = 20) {
		$pageInvokeDB = $this->_getPageInvokeDB();
		$page = (int) $page;
		if ($page < 1)
			$page = 1;
		$total = $pageInvokeDB->searchCount($array);
		$numofpage = ceil($total / $preg);
		$numofpage < 1 && $numofpage = 1;
		$page > $numofpage && $page = $numofpage;
		
		return numofpage($total, $page, $numofpage, $url);
	}
	/**
	 * 111
	 */
	function updatePageInvoke($id, $array) {
		$pageInvokeDB = $this->_getPageInvokeDB();
		$pageInvokeDB->update($id, $array);
	}
	/**
	 * 111
	 */
	function updatePageInvokesState($scr, $sign, $invokeNames, $state) {
		$pageInvokeDB = $this->_getPageInvokeDB();
		$pageInvokeDB->updatePageInvokesState($scr, $sign, $invokeNames, $state);
	}

	/**
	 * 111
	 */
	function getPageInvokeByChannelIdAndName($id, $name) {
		$id = (int) $id;
		$channelService = L::loadClass('channelService', 'area');
		$channelAlias = $channelService->getAliasByChannelid($id);
		return $this->getPageInvokeBySignAndName($channelAlias, $name,'channel');
	}
	/**
	 * 111
	 */
	function getPageInvokeBySignAndName($sign,$name,$type='other') {
		if (!in_array($type,array('other','channel'))) $type = 'other';
		$pageInvokeDB = $this->_getPageInvokeDB();
		$pageInvokeInfo = $pageInvokeDB->getByUnique($type, $sign, $name);
		
		if (!$pageInvokeInfo) return array();
		$invokeService = $this->_getInvokeSevice();
		$invokeInfo = $invokeService->getInvokeByName($name);
		
		$pageInvokeInfo['invokeid'] = $invokeInfo['id'];
		$pageInvokeInfo['title'] = $invokeInfo['title'];
		return $pageInvokeInfo;
	}
	/**
	 * 111
	 */
	function getSignByInvokeName($invokeNames) {
		$pageInvokeDB = $this->_getPageInvokeDB();
		return $pageInvokeDB->getSignByInvokeName($invokeNames);
	}
	/**
	 * 111
	 */
	function getChannelPageInvokes($sign) {
		$pageInvokeDB = $this->_getPageInvokeDB();
		return $pageInvokeDB->getPageInvokes('channel',$sign);
	}
	/**
	 * 111
	 */
	function getEffectPageInvokes($scr, $sign, $ifverify = 0) {
		$pageInvokeDB = $this->_getPageInvokeDB();
		return $pageInvokeDB->getEffectPageInvokes($scr, $sign, $ifverify);
	}
	/**
	 * 111
	 */
	function getEffectPageInvokePieces($scr, $sign) {
		$pieces = array();
		$temp = $this->getEffectPageInvokes($scr, $sign);
		$i = 0;
		foreach ($temp as $value) {
			if ($value['pieces'] && is_array($value['pieces'])) {
				$pieces = array_merge($pieces, array_keys($value['pieces']));
			}
		}
		return $pieces;
	}
	
	function _getInvokeSevice() {
		return L::loadClass('invokeservice', 'area');
	}
	
	function _getPageInvokeDB() {
		return L::loadDB('PageInvoke', 'area');
	}
}