<?php
!defined('P_W') && exit('Forbidden');
/**
 * 门户化页面模块配置相关
 * @author xiejin
 *
 */
class PW_PageInvokeService{
	function getChannelInvokesForSelect($alias, $ifverify = 0,$ifHTML=0) {
		return $this->getPageInvokesForSelect('channel', $alias, $ifverify,$ifHTML);
	}
	
	function getPortalInvokesForSelect($alias, $ifverify = 0,$ifHTML=0) {
		return $this->getPageInvokesForSelect('other', $alias, $ifverify,$ifHTML);
	}

	function getPageInvokesForSelect($scr, $sign, $ifverify = 0,$ifHTML=0) {
		$temp = array();
		$invokes = $this->getEffectPageInvokes($scr, $sign, $ifverify);
		foreach ($invokes as $invoke) {
			if (!$invoke['pieces'] && !$ifHTML) continue; 
			$temp[$invoke['invokename']] = array('invokename'=>$invoke['invokename'],'title'=>$invoke['title'],'pieces'=>$invoke['pieces']);
		}
		return $temp;
	}

	function delPageInvoke($id) {
		$pageInvokeDB = $this->_getPageInvokeDB();
		$pageInovke = $pageInvokeDB->get($id);
		if ($pageInovke && $pageInovke['state']) {
			$invokeService = $this->_getInvokeSevice();
			$invokeService->deleteInvoke($pageInovke['invokename']);
			$pageInvokeDB->delete($id);
		}
	}

	function deletePageInvokesForChannel($alias) {
		$pageInvokeDB = $this->_getPageInvokeDB();
		$pageInvokeDB->deleteByScrAndSign('channel', $alias);
	}

	function addPageInvoke($scr, $sign, $invokeName, $pieces) {
		$pageInvokeDB = $this->_getPageInvokeDB();
		$temp = $pageInvokeDB->getByUnique($scr, $sign, $invokeName);
		if (!$temp) {
			return $pageInvokeDB->insertData(array('scr' => $scr, 'sign' => $sign, 'invokename' => $invokeName, 
				'pieces' => $pieces));
		}
		return $pageInvokeDB->update($temp['id'], array('pieces' => $pieces));
	}

	function searchPageInvokes($array, $page, $preg = 20) {
		$pageInvokeDB = $this->_getPageInvokeDB();
		return $pageInvokeDB->searchPageInvokes($array, $page, $preg);
	}

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

	function updatePageInvoke($id, $array) {
		$pageInvokeDB = $this->_getPageInvokeDB();
		$pageInvokeDB->update($id, $array);
	}

	function updatePageInvokesState($scr, $sign, $invokeNames, $state) {
		$pageInvokeDB = $this->_getPageInvokeDB();
		$pageInvokeDB->updatePageInvokesState($scr, $sign, $invokeNames, $state);
	}


	function getPageInvokeByChannelIdAndName($id, $name) {
		$id = (int) $id;
		$channelService = L::loadClass('channelService', 'area');
		$channelAlias = $channelService->getAliasByChannelid($id);
		return $this->getPageInvokeBySignAndName($channelAlias, $name,'channel');
	}
	
	function getPageInvokeBySignAndName($sign,$name,$type='other') {
		if (!in_array($type,array('other','channel'))) $type = 'other';
		$pageInvokeDB = $this->_getPageInvokeDB();
		$pageInvokeInfo = $pageInvokeDB->getByUnique($type, $sign, $name);
		
		if (!$pageInvokeInfo) return array();
		$invokeService = $this->_getInvokeSevice();
		$invokeInfo = $invokeService->getInvokeByName($name);
		
		$pageInvokeInfo['title'] = $invokeInfo['title'];
		return $pageInvokeInfo;
	}

	function getSignByInvokeName($invokeNames) {
		$pageInvokeDB = $this->_getPageInvokeDB();
		return $pageInvokeDB->getSignByInvokeName($invokeNames);
	}
	
	function getChannelPageInvokes($sign) {
		$pageInvokeDB = $this->_getPageInvokeDB();
		return $pageInvokeDB->getPageInvokes('channel',$sign);
	}

	function getEffectPageInvokes($scr, $sign, $ifverify = 0) {
		$pageInvokeDB = $this->_getPageInvokeDB();
		return $pageInvokeDB->getEffectPageInvokes($scr, $sign, $ifverify);
	}

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