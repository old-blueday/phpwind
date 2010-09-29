<?php
!defined('P_W') && exit('Forbidden');

/**
 * 收藏服务层
 * 
 * @package PW_Collection
 * @author	lmq
 * @abstract
 */

class PW_Collection {
	
	function PW_Collection() {
		
	}

	function get($id) {
		$collectionDB = $this->_getCollectionDB();
		return $collectionDB->_get($id);
	}
	
	function insert($filedData) {
		$collectionDB = $this->_getCollectionDB();
		return $collectionDB->insert($filedData);
	}
	
	function delete($ids) {
		$collectionDB = $this->_getCollectionDB();
		return $collectionDB->delete($ids);
	}
	
	function deleteByUids($uids) {
		$collectionDB = $this->_getCollectionDB();
		return $collectionDB->deleteByUids($uids);
	}
	
	function countByUid($uid) {
		if(!$uid) return false;
		$collectionDB = $this->_getCollectionDB();
		return $collectionDB->countByUid($uid);
	}
	
	function countByUidAndType($uid, $type) {
		if(!$uid || !$type) return false;
		$collectionDB = $this->_getCollectionDB();
		return $collectionDB->countByUidAndType($uid,$type);
	}
	
	function findByUidInPage($uid, $page = 1, $perpage = 20) {
		if (!$uid) return false; 
		$page = (int)$page;
		$perpage = (int)$perpage;
		if ($page <= 0 || $perpage <= 0) return array();
		$offset = ($page-1) * $perpage;
		$collectionDB = $this->_getCollectionDB();
		$tmpDate = $result = array();
		$tmpDate = $collectionDB->findByUid($uid, $offset, $perpage);
		$weiboService = L::loadClass('weibo', 'sns'); /* @var $weiboService PW_Weibo */
		foreach ($tmpDate as $key => $temp) {
			$temp['content'] = unserialize($temp['content']);
			if (strpos($temp['content']['link'],'{#APPS_BASEURL#}') !== false) {			
				$temp['content']['link'] = str_replace('{#APPS_BASEURL#}','apps.php?',$temp['content']['link']);
			}
			$temp['title']	= getLangInfo('app','collection_type_name',array('type'		=> getLangInfo('app',$temp['type']),'postdate'	=> get_date($temp['postdate']),));
			$result[] = $temp;
		}
		return $result;
	}
	
	function findByUidAndTypeInPage($uid, $type, $page = 1, $perpage = 20) {
		if (!$uid || !$type) return false; 
		$page = (int)$page;
		$perpage = (int)$perpage;
		if ($page <= 0 || $perpage <= 0) return array();
		$offset = ($page-1) * $perpage;
		$collectionDB = $this->_getCollectionDB();
		$tmpDate = $result = array();
		$tmpDate = $collectionDB->findByUidAndType($uid, $type, $offset, $perpage);
		$weiboService = L::loadClass('weibo', 'sns'); /* @var $weiboService PW_Weibo */
		foreach ($tmpDate as $key => $temp) {
			$temp['content'] = unserialize($temp['content']);
			if (strpos($temp['content']['link'],'{#APPS_BASEURL#}') !== false) {			
				$temp['content']['link'] = str_replace('{#APPS_BASEURL#}','apps.php?',$temp['content']['link']);
			}
			$temp['title']	= getLangInfo('app','collection_type_name',array('type'		=> getLangInfo('app',$temp['type']),'postdate'	=> get_date($temp['postdate']),));
			$result[] = $temp;
		}
		return $result;
	}
	
	function getByTypeAndTypeid($uid, $type, $typeid) {
		if (!$uid) return false; 
		$collectionDB = $this->_getCollectionDB();
		return $collectionDB->getByTypeAndTypeid($uid, $type, $typeid);
	}
	/**
	 * get PW_CollectionDB
	 * 
	 * @access protected
	 * @return PW_CollectionDB
	 */
	function _getCollectionDB() {
		return L::loadDB('Collection', 'collection');
	}
}

