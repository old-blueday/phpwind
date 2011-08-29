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
	
	function update($fieldsData,$id) {
		$collectionDB = $this->_getCollectionDB();
		return $collectionDB->update($fieldsData,$id);
	}

	function updateByCtid($ctid) {
		$ctid = (int)$ctid;
		if (!$ctid) return false; 
		$collectionDB = $this->_getCollectionDB();
		return $collectionDB->updateByCtid($ctid);
	}

	function delete($ids) {
		$collectionDB = $this->_getCollectionDB();
		return $collectionDB->delete($ids);
	}

	/**
	 * 根据用户uid统计各分类收藏数
	 * 
	 * @param int $uid 用户uid
	 * @return array  分类ctid和count数
	 */
	function countTypesByUid($uid) {
		$typeNumArr = array();
		if (!$uid) return array();
		$collectionDB = $this->_getCollectionDB();
		$typeArr =  $collectionDB->countTypesByUid($uid);
		if (!is_array($typeArr)) return $typeNumArr;
		foreach ($typeArr as $value) {
			$typeNumArr[$value['ctid']] = $value['count'];
		}
		return $typeNumArr;
	}
	/**
	 * 改变收藏分类
	 * 
	 * @param array $ids 收藏ID
	 * @param int $ctid 分类ID
	 * @return int 
	 */
	function remove($ids,$ctid) {
		if (!$ctid) return null;
		$collectionDB = $this->_getCollectionDB();
		return $collectionDB->remove($ids,$ctid);
	}
	
	function deleteByUids($uids) {
		$collectionDB = $this->_getCollectionDB();
		return $collectionDB->deleteByUids($uids);
	}
	
	function countByUid($uid,$ftype = null) {
		if(!$uid) return false;
		$collectionDB = $this->_getCollectionDB();
		return  $collectionDB->countByUid($uid,$ftype);
	}
	
	function countByUidAndType($uid, $type, $ftype = null) {
		if(!$uid || !$type) return false;
		$collectionDB = $this->_getCollectionDB();
		return $collectionDB->countByUidAndType($uid,$type,$ftype);
	}
	
	function findByUidInPage($uid, $page = 1, $perpage = 20, $ftype = null) {
		if (!$uid) return false; 
		$page = (int)$page;
		$perpage = (int)$perpage;
		if ($page <= 0 || $perpage <= 0) return array();
		$offset = ($page-1) * $perpage;
		$collectionDB = $this->_getCollectionDB();
		$tmpDate = $result = array();
		$tmpDate = $collectionDB->findByUid($uid, $offset, $perpage, $ftype);
		$weiboService = L::loadClass('weibo', 'sns'); /* @var $weiboService PW_Weibo */
		foreach ($tmpDate as $key => $temp) {
			$temp['content'] = unserialize($temp['content']);
			$temp['type'] == 'postfavor' && $temp['content']['link'] = urlRewrite($temp['content']['link']);
			if (strpos($temp['content']['link'],'{#APPS_BASEURL#}') !== false) {			
				$temp['content']['link'] = str_replace('{#APPS_BASEURL#}','apps.php?',$temp['content']['link']);
			}
			//$temp['title']	= ($temp['type'] == 'postfavor') ? getLangInfo('app','collection_postfavor_name',array('type'		=> getLangInfo('app',$temp['type']),'postdate'	=> get_date($temp['content']['lastpost']),)) : getLangInfo('app','collection_type_name',array('type'		=> getLangInfo('app',$temp['type']),'postdate'	=> get_date($temp['postdate']),));
			$temp['title'] = getLangInfo('app','collection_type_name',array('type'		=> getLangInfo('app',$temp['type']),'postdate'	=> get_date($temp['postdate']),));
			$result[] = $temp;
		}
		return $result;
	}
	
	function findByUidAndTypeInPage($uid, $type, $page = 1, $perpage = 20, $ftype = null) {
		if (!$uid || !$type) return false; 
		$page = (int)$page;
		$perpage = (int)$perpage;
		if ($page <= 0 || $perpage <= 0) return array();
		$offset = ($page-1) * $perpage;
		$collectionDB = $this->_getCollectionDB();
		$tmpDate = $result = array();
		$tmpDate = $collectionDB->findByUidAndType($uid, $type, $offset, $perpage, $ftype);
		$weiboService = L::loadClass('weibo', 'sns'); /* @var $weiboService PW_Weibo */
		foreach ($tmpDate as $key => $temp) {
			$temp['content'] = unserialize($temp['content']);
			$temp['type'] == 'postfavor' && $temp['content']['link'] = urlRewrite($temp['content']['link']);
			if (strpos($temp['content']['link'],'{#APPS_BASEURL#}') !== false) {			
				$temp['content']['link'] = str_replace('{#APPS_BASEURL#}','apps.php?',$temp['content']['link']);
			}
			//$temp['title']	= ($temp['type'] == 'postfavor') ? getLangInfo('app','collection_postfavor_name',array('type'		=> getLangInfo('app',$temp['type']),'postdate'	=> get_date($temp['content']['lastpost']),)) : getLangInfo('app','collection_type_name',array('type'		=> getLangInfo('app',$temp['type']),'postdate'	=> get_date($temp['postdate']),));
			$temp['title'] = getLangInfo('app','collection_type_name',array('type'		=> getLangInfo('app',$temp['type']),'postdate'	=> get_date($temp['postdate']),));
			$result[] = $temp;
		}
		return $result;
	}
	
	function getByTypeAndTypeid($uid, $type, $typeid) {
		if (!$uid) return false; 
		$collectionDB = $this->_getCollectionDB();
		return $collectionDB->getByTypeAndTypeid($uid, $type, $typeid);
	}

	function getByType($uid, $type) {
		if (!$uid) return false; 
		$collectionDB = $this->_getCollectionDB();
		$result = $collectionDB->getByType($uid, $type);
		$tids = array();
		foreach ($result as $tid) {
			$tids[$tid['ctid']][$tid['typeid']] = $tid['typeid'];
		}
		return $tids;
	}
	
	function checkCollectionIds($ids,$uid) {
		if (!S::isArray($ids)) return false; 
		$collectionDB = $this->_getCollectionDB();
		$result = $collectionDB->getUidsByIds($ids);
		foreach ($result as $v) {
			if ($v['uid'] != $uid || !$v['id']) continue;
			$colids[] = $v['id'];
		}
		return $colids;
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

