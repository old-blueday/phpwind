<?php
!defined('P_W') && exit('Forbidden');

class PW_CmsAttachService {
	/**
	 * 更新帖子附件
	 * @param int $articleId
	 * @param array $attachs
	 * return array('是否有附件','插入图片的id集合')
	 */
	function updateAttachs($articleId,$attachs) {
		$cmsAttachDAO = $this->_getCmsAttachDAO();
		$ifAttach = 0;
		$uploadIds = array();
		foreach ($attachs as $value) {
			if ($value['attname'] != 'delete') $ifAttach = 1;
			if ($value['attname'] == 'replace') {
				$saveData = $this->_cookUploadData($value,$articleId);
				$cmsAttachDAO->update($saveData,$value['id']);
			} elseif ($value['attname'] == 'attachment') {
				$saveData = $this->_cookUploadData($value,$articleId);
				$temp = $cmsAttachDAO->insert($saveData);
				$uploadIds[$value['id']] = $temp;
			} elseif ($value['attname'] == 'update') {
				$cmsAttachDAO->update(array('descrip' => $value['descrip']),$value['attach_id']);
			} elseif ($value['attname'] == 'delete') {
				$cmsAttachDAO->delete($value['attach_id']);
			}
		}
		return array($ifAttach,$uploadIds);
	}
	/**
	 * 获取帖子的附件
	 * @param $articleId
	 * return array
	 */
	function getArticleAttachs($articleId) {
		$cmsAttachDAO = $this->_getCmsAttachDAO();
		$attachs = $cmsAttachDAO->findArticleAttaches($articleId);
		
		$result = array();
		foreach ($attachs as $key => $value) {
			$value['attachurl'] = $this->_getImageUrl($value['attachurl']);
			$result[$value['attach_id']] = $value;
		}
		return $result;
	}
	/**
	 * 获取单个附件信息
	 * @param $id
	 */
	function getAttachById($id) {
		$cmsAttachDAO = $this->_getCmsAttachDAO();
		$temp = $cmsAttachDAO->get($id);
		if (!$temp) return array();
		$temp['attachurl'] = $this->_getImageUrl($temp['attachurl']);
		return $temp;
	}
	
	function _cookUploadData($data,$articleId) {
		global $timestamp;
		return array(
			'name' => $data['name'],
			'descrip' => $data['descrip'],
			'article_id' => $articleId,
			'type' => $data['type'],
			'size' => $data['size'],
			'uploadtime' => $timestamp,
			'attachurl' => $data['fileuploadurl'],
			'ifthumb' => $data['ifthumb'],
		);
	}
	
	function _getImageUrl($path) {
		$temp = geturl($path);
		return $temp[0];
	}
	function _getCmsAttachDAO() {
		return C::loadDB('cmsattach');
	}
}