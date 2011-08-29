<?php
/**
 * 专题业务服务类文件
 * 
 * @package STopic
 */

!defined('P_W') && exit('Forbidden');

/**
 * 专题业务服务对象
 * 
 * 提供所有专题业务操作的服务接口，包括专题本身的业务操作、专题分类操作、专题背景图片操作等。
 *
 * @package STopic
 */
class PW_STopicService {
	/**
	 * 专题配置
	 * 
	 * @var array
	 */
	var $_stopicConfig = null;

	/**
	 * 获取专题评论数
	 * 
	 * @return int
	 */
	function getCommentNum($stopicId) {
		$stopicId = intval($stopicId);
		if (!$stopicId) return false;
		$stopicDB = $this->_getSTopicDB();
		return $stopicDB->getCommentNum($stopicId);
	}
	
	/**
	 * 更新回复数
	 * 
	 * @param array $fieldsData
	 * @param int $commentid 
	 * @return boolean 
	 */
	function updateCommentnum($num,$stopicId) {
		$stopicId = intval($stopicId);
		if($stopicId < 1 || !$num) return false;
		$stopicDB = $this->_getSTopicDB();
		return $stopicDB->updateCommentnum($num,$stopicId);
	}
	
	/**
	 * 获取专题可用布局列表
	 * 
	 * @return array 专题布局列表数组
	 */
	function getLayoutList() {
		$layoutTypes= $this->_getSTopicConfig('layoutTypes');
		$layoutList	= array ();
		foreach ( $layoutTypes as $typeName => $typeDesc ) {
			$tmp = $this->getLayoutInfo ($typeName);
			if ($tmp)
				$layoutList [$typeName] = $tmp;
		}
		return $layoutList;
	}

	/**
	 * 获取专题布局配置信息
	 * 
	 * @param string $typeName 布局名称，如type1v0,type1v1等
	 * @return array 布局配置数组
	 */
	function getLayoutInfo($typeName) {
		$stopicConfig = $this->_getSTopicConfig ();
		$checkDir = $stopicConfig ['layoutPath'] . $typeName . "/";
		if (! is_dir ( $checkDir ))
			return false;

		foreach ( $stopicConfig ['layoutConfig'] as $checkFile ) {
			if (! is_file ( $checkDir . $checkFile ))
				return false;
		}
		$checkData = array ();
		$checkData ['logo'] = $stopicConfig ['layoutBaseUrl'] . $typeName . "/" . $stopicConfig ['layoutConfig'] ['logo'];
		$checkData ['html'] = $checkFile . $stopicConfig ['layoutConfig'] ['html'];
		$checkData ['desc'] = $stopicConfig ['layoutTypes'] [$typeName];
		return $checkData;
	}
	
	/**
	 * 获取专题默认的风格样式css配置
	 * 
	 * @param string $defaultStyle 默认风格样式名，默认值为baby_org
	 * @return array 风格样式css配置数组
	 */
	function getLayoutDefaultSet($defaultStyle = 'baby_org') {
		$styleConfig = $this->getStyleConfig('baby_org');
		if (empty($styleConfig)) { 
			return $this->_getSTopicConfig('layout_set');
		} else {
			$layoutSet = $styleConfig['layout_set'];
			$layoutSet['bannerurl'] = $this->getStyleBanner($defaultStyle);
			return $layoutSet;
		}
	}

	/**
	 * 获取专题的风格样式css配置
	 * 
	 * @param string $style 风格样式名
	 * @return array 风格样式css配置数组
	 */
	function getLayoutSet($style) {
		$stylePath = $this->_getSTopicConfig('stylePath');
		if ($style && is_dir($stylePath.$style)) {
			return $this->getStyleConfig($style,'layout_set');
		}
		return $this->getLayoutDefaultSet();
	}

	/**
	 * 获取风格样式列表
	 * 
	 * @return array 风格样式列表数组
	 */
	function getStyles() {
		$stylePath = $this->_getSTopicConfig('stylePath');
		$fp	= opendir($stylePath);
		$styles	= array();
		while ($styleDir = readdir($fp)) {
			if (in_array($styleDir,array('.','..')) || strpos($styleDir,'.')!==false) continue;
			$styles[$styleDir] = array(
				'name'=>$this->getStyleConfig($styleDir,'name'),
				'minipreview'=>$this->getStyleMiniPreview($styleDir),
				'preview'=>$this->getStylePreview($styleDir),
			);
		}
		return $styles;
	}

	/**
	 * 获取风格样式预览缩略图url
	 * 
	 * @param string $style 风格样式名
	 * @return string 缩略图url
	 */
	function getStyleMiniPreview($style) {
		return $this->_getSTopicConfig('styleBaseUrl').$style.'/'.$this->_getSTopicConfig('styleMiniPreview');
	}

	/**
	 * 获取风格样式预览图url
	 * 
	 * @param string $style 风格样式名
	 * @return string 预览图url
	 */
	function getStylePreview($style) {
		return $this->_getSTopicConfig('styleBaseUrl').$style.'/'.$this->_getSTopicConfig('stylePreview');
	}
	
	/**
	 * 获取风格样式的横幅图片url
	 * 
	 * @param string $style 风格样式名
	 * @return string 横幅图片url
	 */
	function getStyleBanner($style) {
		$temp = $this->getStyleConfig($style,'banner');
		if ($temp) {
			if (strpos($temp,'http')===false) {
				$temp = $GLOBALS['db_bbsurl'].'/'.$temp;
			}
			return $temp;
		}
		if ($style && file_exists($this->_getSTopicConfig('stylePath').$style.'/'.$this->_getSTopicConfig('styleBanner'))) {
			return $this->_getSTopicConfig('styleBaseUrl').$style.'/'.$this->_getSTopicConfig('styleBanner');
		}
		return 'http://';
	}
	
	/**
	 * 获取风格样式的配置
	 * 
	 * @param string $style 风格样式名
	 * @param string $key 风格样式的配置项
	 * @return mixed 如果$key为空则返回风格样式配置数组，否则返回指定项的配置值
	 */
	function getStyleConfig($style,$key='') {
		static $styles = array();
		if (!isset($styles[$style])) {
			$stylePath = $this->_getSTopicConfig('stylePath');
			if (file_exists($stylePath.$style.'/config.php')) {
				$styles[$style] = include S::escapePath($stylePath.$style."/config.php");
			} else {
				$styles[$style] = array();
			}
		}
		if ($key) {
			return isset($styles[$style][$key]) ? $styles[$style][$key] : '';
		}
		return $styles[$style];
	}

	/**
	 * 生成专题html文件
	 * 
	 * @param int $stopic_id 专题id
	 * @return null
	 */
	function creatStopicHtml($stopic_id) {
		global $db_charset,$wind_version,$db_bbsurl,$db_htmifopen;
		$stopic	= $this->getSTopicInfoById($stopic_id);
		if (!$stopic) return false;
		$tpl_content	= $this->getStopicContent($stopic_id,0);
		@extract($stopic, EXTR_SKIP);
		if (defined('A_P')) {
			include(A_P.'template/stopic.htm');
		} else {
			include(R_P.'apps/stopic/template/stopic.htm');
		}
		$output = str_replace(array('<!--<!---->','<!---->'),array('',''),ob_get_contents());
		ob_end_clean();
		$stopicDir	= $this->getStopicDir($stopic_id, $stopic['file_name']);
		$output = parseHtmlUrlRewrite($output, $db_htmifopen);
		pwCache::writeover($stopicDir,$output);
		ObStart();
	}

	/**
	 * 新增专题
	 * 
	 * @param $fieldsData 专题数据数组，对应数据库中字段
	 * @return int 专题id，失败则返回0
	 */
	function addSTopic($fieldsData) {
		if (!is_array($fieldsData) || !count($fieldsData)) return 0;
		$fieldsData['create_date'] = time();

		$stopicDB = $this->_getSTopicDB();
		$stopicPicturesDB = $this->_getSTopicPicturesDB();
		$stopicId = $stopicDB->add($fieldsData);
		//if ($stopicId && isset($fieldsData['copy_from']) && $fieldsData['copy_from']) $stopicDB->increaseField($fieldsData['copy_from'], 'used_count');
		if ($stopicId && isset($fieldsData['bg_id']) && $fieldsData['bg_id']) $stopicPicturesDB->increaseField($fieldsData['bg_id'], 'num');
		return $stopicId;
	}

	/**
	 * 删除多个专题
	 * 
	 * @param array $stopicIds 专题id数组
	 * @return int 删除个数
	 */
	function deleteSTopics($stopicIds) {
		$success = 0;
		foreach ( $stopicIds as $stopicId ) {
			$success += $this->deleteSTopicById ( $stopicId );
		}
		return $success;
	}

	/**
	 * 删除单个专题
	 * 
	 * @param int $stopicId 专题id
	 * @return bool 是否成功
	 */
	function deleteSTopicById($stopicId) {
		$stopicDB = $this->_getSTopicDB();
		$stopicPicturesDB = $this->_getSTopicPicturesDB();
		$stopicUnitDB = $this->_getSTopicUnitDB();

		$stopicData = $stopicDB->get($stopicId);
		if (null == $stopicData) return false;
		$isSuccess = (bool) $stopicDB->delete($stopicId);
		if ($isSuccess && $stopicData['bg_id']) $stopicPicturesDB->increaseField($stopicData['bg_id'], 'num', -1);
		if ($isSuccess) {
			$stopicUnitDB->deleteAll($stopicId);
			$this->_delFile($this->getStopicDir($stopicId, $stopicData['file_name']));
		}
		return $isSuccess;
	}

	/**
	 * 删除文件
	 * 
	 * @access protected
	 * @see P_unlink
	 * @param string $fileName 文件名
	 * @return bool 是否成功
	 */
	function _delFile($fileName) {
		return P_unlink($fileName);
	}

	/**
	 * 更新专题记录
	 * 
	 * @param int $stopicId 专题id
	 * @param array $updateData 要更新的数据数组
	 * @return bool 是否有更新
	 */
	function updateSTopicById($stopicId, $updateData) {
		$stopicDB = $this->_getSTopicDB();
		$stopicPicturesDB = $this->_getSTopicPicturesDB();
		$stopicData = $stopicDB->get($stopicId);
		if (null == $stopicData) return false;

		$isSuccess = (bool) $stopicDB->update($stopicId,$updateData);
		if (isset($updateData['bg_id']) && $updateData['bg_id'] != $stopicData['bg_id']) {
			if ($stopicData['bg_id']) $stopicPicturesDB->increaseField($stopicData['bg_id'], 'num', -1);
			if ($updateData['bg_id']) $stopicPicturesDB->increaseField($updateData['bg_id'], 'num');
		}
		if (isset($updateData['file_name'])) {
			$stopicDB->updateFileName($stopicId, $updateData['file_name']);
			if ($updateData['file_name'] != $stopicData['file_name'] && '' != $stopicData['file_name']) {
				$this->_delFile($this->getStopicDir($stopicId, $stopicData['file_name']));
			}
		}
		return $isSuccess;
	}

	/**
	 * 获取专题信息
	 * 
	 * @param $stopicId
	 * @return array|null 专题数据数组
	 */
	function getSTopicInfoById($stopicId) {
		$stopicDB = $this->_getSTopicDB();

		$stopic = $stopicDB->get($stopicId);
		if ($stopic) $stopic['bg_url'] = $stopic['bg_id'] ? $this->_getBackgroundUrl($stopic['bg_id']) : "";

		return $stopic;
	}

	/**
	 * 获取空专题（专题未添加任何内容）
	 * 
	 * @return array|null 专题数据数组
	 */
	function getEmptySTopic() {
		$stopicDB = $this->_getSTopicDB();
		$stopic = $stopicDB->getEmpty();
		return $stopic;
	}

	/**
	 * 获取专题个数
	 * 
	 * @param string $keyword 查询关键字
	 * @param int $categoryId 分类id
	 * @return int
	 */
	function countSTopic($keyword = '', $categoryId = 0) {
		$stopicDB = $this->_getSTopicDB();
		return $stopicDB->countByKeyWord ($keyword, $categoryId);
	}

	/**
	 * 分页查询专题
	 * 
	 * @param int $page 第几页
	 * @param int $perPage 每页记录数
	 * @param string $keyword 关键字
	 * @param int $categoryId 分类id
	 * @return array 专题数据二维数组
	 */
	function findSTopicInPage($page, $perPage, $keyword = '', $categoryId = 0) {
		$stopicDB = $this->_getSTopicDB();
		$page = intval ( $page );
		$perPage = intval ( $perPage );
		if ($page <= 0 || $perPage <= 0) return array ();
		$result	= $stopicDB->findByKeyWordInPage($page, $perPage, $keyword, $categoryId);
		foreach ($result as $key=>$value) {
			$result[$key]['url'] = $this->getStopicUrl($value['stopic_id'], $value['file_name']);
			$result[$key]['create_date'] = get_date($value['create_date']);
		}
		return $result;
	}

	/**
	 * 根据分类分页获取有效的专题列表
	 * 
	 * @param int $page 第几页
	 * @param int $perPage 每页记录数
	 * @param int $categoryId 分类id
	 * @return array 专题数据二维数组
	 */
	function findValidCategorySTopicInPage($page, $perPage, $categoryId = 0) {
		$stopicDB = $this->_getSTopicDB();
		$page = intval ( $page );
		$perPage = intval ( $perPage );
		if ($page <= 0 || $perPage <= 0)
			return array ();

		return $stopicDB->findValidByCategoryIdInPage ( $page, $perPage, $categoryId );
	}

	/**
	 * 根据分类分页获取使用率高的专题列表
	 * 
	 * @param int $limit 个数
	 * @param int $categoryId 分类id
	 * @return array 专题数据二维数组
	 */
	function findUsefulSTopicInCategory($limit, $categoryId = 0) {
		$stopicDB = $this->_getSTopicDB();
		$limit = intval ( $limit );
		if ($limit <= 0) return array ();

		return $this->_lardBackground($stopicDB->findByCategoryIdOrderByUsedInPage(1, $limit, $categoryId));
	}

	/**
	 * 得到和生成专题数据存放目录
	 * 
	 * @param int $stopic_id 专题id
	 * @param string $file_name 文件名
	 * @return string 文件路径
	 */
	function getStopicDir($stopic_id, $file_name='') {
		$stopic_id = (int) $stopic_id;
		if (!$stopic_id) return false;
		if ('' == $file_name) $file_name = $stopic_id;
		$stopicDir	= S::escapePath($this->_getSTopicConfig('htmlDir'));
		if (!file_exists($stopicDir)) {
			if (mkdir($stopicDir)) {
				@chmod($stopicDir,0777);
			} else {
				showmsg('stopic_htm_is_not_777');
			}
		}
		return $stopicDir.'/'.$file_name.$this->_getSTopicConfig('htmlSuffix');
	}

	/**
	 * 获取专题的url
	 * 
	 * @param int $stopic_id 专题id
	 * @param string $file_name 文件名
	 * @return string|bool 专题url
	 */
	function getStopicUrl($stopic_id, $file_name) {
		if ('' == $file_name) return false;
		$stopicDir = $this->getStopicDir($stopic_id, $file_name);
		if ($stopicDir && file_exists($stopicDir)) {
			return $this->_getSTopicConfig('htmlUrl').'/'.$file_name.$this->_getSTopicConfig('htmlSuffix');
		} else {
			return false;
		}
	}

	/**
	 * 获取专题的html内容
	 * 
	 * @param int $stopic_id 专题id
	 * @param bool $ifadmin 是否是后台管理时使用的html
	 * @return string
	 */
	function getStopicContent($stopic_id,$ifadmin) {
		$stopic	= $this->getSTopicInfoById($stopic_id);
		$units	= $this->getStopicUnitsByStopicId($stopic_id);
		$blocks	= $this->getBlocks();

		$parseStopicTpl	= L::loadClass('ParseStopicTpl','stopic');
		$tpl_content	= $parseStopicTpl->exute($this,$stopic,$units,$blocks,$ifadmin);
		return $tpl_content;
	}
	
	/**
	 * 专题保存的文件是否被使用
	 * 
	 * @param int $stopicId 专题id
	 * @param string $fileName 文件名
	 * @return bool
	 */
	function isFileUsed($stopicId, $fileName) {
		$stopicId = intval($stopicId);
		$stopicDB = $this->_getSTopicDB();
		$isFind = $stopicDB->getByFileNameAndExcept($stopicId, $fileName);
		return $isFind && file_exists($this->getStopicDir($stopicId, $fileName));
	}

	/**
	 * 新增一个专题分类
	 *
	 * @param array $fieldData 专题分类数据数组
	 * @return int 分类id，失败返回0
	 */
	function addCategory($fieldData) {
		$stopicCategoryDB = $this->_getSTopicCategoryDB();
		return $stopicCategoryDB->add($fieldData);
	}

	/**
	 * 更新一个专题分类
	 *
	 * @param array $fieldData 专题分类数据数组
	 * @param int $categoryId 分类id
	 * @return int|null 是否更新成功
	 */
	function updateCategory($fieldData, $categoryId) {
		$stopicCategoryDB = $this->_getSTopicCategoryDB();
		$categoryId = intval ( $categoryId );
		if ($categoryId<= 0) {
			return NULL;
		}
		return $stopicCategoryDB->update($fieldData,$categoryId);
	}

	/**
	 * 删除一个专题分类 同时更新背景分类
	 *
	 * @param int $categoryId 分类id
	 * @return int
	 */
	function deleteCategory($categoryId) {
		$stopicPicturesDB = $this->_getSTopicPicturesDB();
		$stopicCategoryDB = $this->_getSTopicCategoryDB();

		$categoryId = intval ( $categoryId );
		if ($categoryId <= 0 || ! $this->isAllowDeleteCategory ( $categoryId )) {
			return NULL;
		}
		return ($stopicCategoryDB->delete ( $categoryId )) ? $stopicPicturesDB->updateByCategoryId ( array("categoryid"=>0),$categoryId ) : NULL;
	}

	/**
	 * 是否允许删除分类
	 * 
	 * 默认专题不能删除/分类下如果有专题不能删除
	 *
	 * @param int $categoryId 分类id
	 * @return bool
	 */
	function isAllowDeleteCategory($categoryId) {
		$stopicDB = $this->_getSTopicDB();
		$stopicCategoryDB = $this->_getSTopicCategoryDB();
		if ($stopicDB->countByCategoryId($categoryId)) return false;
		$category = $stopicCategoryDB->get($categoryId);
		if (!$category || $category['status'] == 1) return false;
		return true;
	}

	/**
	 * 获取所有专题分类
	 *
	 * @return array 专题分类二维数组
	 */
	function getCategorys() {
		$stopicCategoryDB = $this->_getSTopicCategoryDB();
		return $stopicCategoryDB->gets ();
	}

	/**
	 * 获取某个分类信息
	 *
	 * @param int $categoryId 分类id
	 * @return array 专题分类数据数组
	 */
	function getCategory($categoryId) {
		$stopicCategoryDB = $this->_getSTopicCategoryDB();
		return $stopicCategoryDB->get ( $categoryId );
	}

	/**
	 * 分类名是否存在
	 * 
	 * @param string $categoryName 分类名称
	 * @return bool
	 */
	function isCategoryExist($categoryName) {
		$stopicCategoryDB = $this->_getSTopicCategoryDB();
		return $stopicCategoryDB->getByName($categoryName) ? true : false;
	}

	/**
	 * 上传背景图片 并增加一条图片 记录
	 *
	 * @param array $fileArray 上传文件数据数组
	 * @return string 文件名如[20090819152809.jpg]
	 */
	function uploadPicture($fileArray, $categoryId, $creator) {
		$stopicPicturesDB = $this->_getSTopicPicturesDB();
		$uploadPictureClass = $this->_setUploadPictureClass();
		if (count ( $fileArray ) < 0 || intval ( $categoryId ) < 0 || trim ( $creator ) == "") {
			return null;
		}
		$filename = $uploadPictureClass->upload ( $fileArray );
		if ($filename === FALSE) {
			return null;
		}
		$fieldData = array (
			"title" => time(),
			"categoryid" => intval($categoryId),
			"path" => trim ($filename),
			"creator" => $creator
		);
		return $stopicPicturesDB->add ( $fieldData );
	}

	/**
	 * 获取文件上传类
	 * 
	 * @access protected
	 */
	function _setUploadPictureClass() {
		$tempUpdatePicture = L::loadClass('UpdatePicture');
		$tempUpdatePicture->init($this->_getSTopicConfig ('bgUploadPath'));
		return $tempUpdatePicture;
		//return new UpdatePicture ($this->_getSTopicConfig ('bgUploadPath'));
	}

	/**
	 * 更新背景图片
	 *
	 * @param int $fieldData 背景图片数据数组
	 * @param int $pictureId 背景图片if
	 * @return int|null
	 */
	function updatePicture($fieldData, $pictureId) {
		$stopicPicturesDB = $this->_getSTopicPicturesDB();
		$pictureId = intval ( $pictureId );
		if ($pictureId <= 0) {
			return NULL;
		}
		return $stopicPicturesDB->update($fieldData,$pictureId);
	}

	/**
	 * 删除背景图片 删除数据并删除物理图片
	 *
	 * @param int $pictureId
	 * @return int|null
	 */
	function deletePicture($pictureId) {
		$stopicPicturesDB = $this->_getSTopicPicturesDB();
		$uploadPictureClass = $this->_setUploadPictureClass();
		$pictureId = intval ( $pictureId );
		if ($pictureId <= 0) return null;
		if (!$this->isAllowDeletePicture($pictureId)) return null;
		$picture = $stopicPicturesDB->get($pictureId);
		if (!$picture) return null;
		return ($stopicPicturesDB->delete ( $pictureId )) ? $uploadPictureClass->delete ( $picture ['path'] ) : "";
	}

	/**
	 * 是否允许删除背景图片
	 *
	 * @param int $pictureId
	 * @return bool
	 */
	function isAllowDeletePicture($pictureId) {
		$stopicDB = $this->_getSTopicDB();
		return $stopicDB->countByBackgroundId($pictureId) ? false : true;
	}

	/**
	 * 获取背景图片
	 *
	 * @param int $categoryId 分类id，为0则找所有
	 * @return array
	 */
	function getPictures($categoryId = 0) {
		$stopicPicturesDB = $this->_getSTopicPicturesDB();
		$categoryId = intval ( $categoryId );
		if ($categoryId < 0) return array();

		return $this->_lardBackground( $categoryId
			? $stopicPicturesDB->getsByCategoryId ($categoryId)
			: $stopicPicturesDB->gets() );
	}

	/**
	 * 分页获取专题背景图片
	 * 
	 * @param int $page
	 * @param int $perPage
	 * @param int $categoryId 分类id
	 * @return array 背景图片二维数组
	 */
	function getBackgroundsInPage($page, $perPage, $categoryId=0) {
		$stopicPicturesDB = $this->_getSTopicPicturesDB();
		return $this->_lardBackground($stopicPicturesDB->getsInPage($page, $perPage, $categoryId));
	}

	/**
	 * 获取系统默认背景图片和用户上传的背景图片列表
	 * 
	 * 系统默认背景图片的id为负数，如-1,-2
	 * 
	 * @param int $categoryId 分类id
	 * @return array 背景图片二维数组
	 */
	function getPicturesAndDefaultBGs($categoryId = 0) {
		$defaults = $this->_getDefaultBackGrounds();
		$thisTypePictures = $this->getPictures($categoryId);
		return array_merge($defaults,$thisTypePictures);
	}

	/**
	 * 获取背景图片url
	 * 
	 * @access protected
	 * @param int $bgId 背景图片id
	 * @return string
	 */
	function _getBackgroundUrl($bgId) {
		$stopicPicturesDB = $this->_getSTopicPicturesDB();
		if ($bgId<0) return $this->_getDefaultBackgroundUrl($bgId);

		$bg = $stopicPicturesDB->get($bgId);
		return $bg['path'] ? $this->_getSTopicConfig ('bgBaseUrl') . $bg ['path'] : "";
	}

	/**
	 * 获取系统默认背景图片url
	 * 
	 * @access protected
	 * @param int $bgId 背景图片id
	 * @return string
	 */
	function _getDefaultBackgroundUrl($bgId) {
		$bgId = (int) $bgId;
		$bgId = abs($bgId);
		if (file_exists($this->_getSTopicConfig('bgDefalutPath').$bgId.'.jpg')) {
			return $this->_getSTopicConfig('bgDefalutUrl').$bgId.'.jpg';
		}
		return '';
	}

	/**
	 * 获取系统默认背景图片列表
	 * 
	 * @access protected
	 * @return array
	 */
	function _getDefaultBackGrounds() {
		$backPath = $this->_getSTopicConfig('bgDefalutPath');
		$fp	= opendir($backPath);
		$backs	= array();

		while ($back = readdir($fp)) {
			if (in_array($back,array('.','..')) || !strpos($back,'.jpg')) continue;
			$id	= $this->_getDefaultBackGroudId($back);
			$backs[] = array(
				'id'	=> $id,
				'categoryid'	=> 'defalut',
				'thumb_url'	=> $this->_getDefaultBackgroundUrl($id)
			);
		}
		return $backs;
	}

	/**
	 * 获取系统默认背景图片id
	 * 
	 * 目前的转换规则为图片名转为负数即为图片id
	 * 
	 * @access protected
	 * @param string $filename 系统默认背景图片名
	 * @return int
	 */
	function _getDefaultBackGroudId($filename) {
		$temp = (int) $filename;
		if (!$temp || $temp<0) return false;
		return 0-$temp;
	}

	/**
	 * 统计背景图片个数
	 *
	 * @param int $categoryId 分类id，为0则统计所有
	 * @return int
	 */
	function countPictures($categoryId = 0) {
		$stopicPicturesDB = $this->_getSTopicPicturesDB();
		return $categoryId ? $stopicPicturesDB->countByCategoryId($categoryId) : $stopicPicturesDB->count();
	}

	/**
	 * 加工背景图片数据
	 * 
	 * 数据中新增背景图片url
	 * 
	 * @access protected
	 * @param array $bgList 背景图片数组
	 * @return array
	 */
	function _lardBackground($bgList) {
		foreach ($bgList as $key => $bg) {
			$bgList[$key]['thumb_url'] = $bg['path'] ? $this->_getSTopicConfig('bgBaseUrl') . "thumb_" . $bg ['path'] : "";
		}
		return $bgList;
	}

	/**
	 * 获取模块类型列表
	 * 
	 * @return 模块类型列表
	 */
	function getBlocks() {
		return $this->_getSTopicConfig('blockTypes');
	}

	/**
	 * 获取模块类型
	 * 
	 * @param string $typeId 模块类型名
	 * @return array
	 */
	function getBlockById($typeId) {
		$blockTypes = $this->_getSTopicConfig('blockTypes');
		return $blockTypes[$typeId];
	}

	/**
	 * 添加专题模块
	 * 
	 * @param array $fieldData 模块数据数组
	 * @return int 模块id
	 */
	function addUnit($fieldData) {
		$stopicUnitDB = $this->_getSTopicUnitDB();
		return $stopicUnitDB->add($fieldData);
	}

	/**
	 * 更新模块数据
	 * 
	 * @param int $stopic_id 专题id
	 * @param string $html_id 模板在html中的id
	 * @param array $fieldData 更新数据
	 * @return bool 是否更新成功
	 */
	function updateUnitByFild($stopic_id,$html_id,$fieldData) {
		$stopicUnitDB = $this->_getSTopicUnitDB();
		return $stopicUnitDB->updateByFild($stopic_id,$html_id,$fieldData);
	}

	/**
	 * 删除多个专题模块
	 * 
	 * @param int $stopic_id 专题id
	 * @param array $html_ids 模块id数组
	 * @return int 删除个数
	 */
	function deleteUnits($stopic_id,$html_ids) {
		$stopicUnitDB = $this->_getSTopicUnitDB();
		return $stopicUnitDB->deletes($stopic_id,$html_ids);
	}

	/**
	 * 获取专题模块列表
	 * 
	 * @param int $stopic_id 专题id
	 * @return array
	 */
	function getStopicUnitsByStopicId($stopic_id) {
		$stopicUnitDB = $this->_getSTopicUnitDB();
		return $stopicUnitDB->getStopicUnits($stopic_id);
	}

	/**
	 * 获取专题的一个模块数据
	 * 
	 * @param int $stopic_id 专题id
	 * @param string $html_id 模块id
	 * @return array
	 */
	function getStopicUnitByStopic($stopic_id,$html_id) {
		$stopicUnitDB = $this->_getSTopicUnitDB();
		return $stopicUnitDB->getByStopicAndHtml($stopic_id,$html_id);
	}

	/**
	 * 获取模块html模板内容
	 * 
	 * @param array $block_data 模块填充数据
	 * @param string $block_type 模块类型
	 * @param int $block_id 模块id
	 * @return string
	 */
	function getHtmlData($block_data, $block_type, $block_id=null) {
		$block_job = 'show';
		include S::escapePath(A_P."/template/admin/block/$block_type.htm");
		$output = ob_get_contents();
		ob_clean();
		return $output;
	}

	/**
	 * 返回专题数据库操作对象
	 * 
	 * @access protected
	 * @return PW_STopicDB
	 */
	function _getSTopicDB() {
		return L::loadDB('STopic', 'stopic');
	}
	
	/**
	 * 返回专题背景图片数据库操作对象
	 * 
	 * @access protected
	 * @return PW_STopicPicturesDB
	 */
	function _getSTopicPicturesDB() {
		return L::loadDB('STopicPictures', 'stopic');
	}
	
	/**
	 * 返回专题分类数据库操作对象
	 * 
	 * @access protected
	 * @return PW_STopicCategoryDB
	 */
	function _getSTopicCategoryDB() {
		return L::loadDB('STopicCategory', 'stopic');
	}
	
	/**
	 * 返回专题模块数据库操作对象
	 * 
	 * @access protected
	 * @return PW_STopicUnitDB
	 */
	function _getSTopicUnitDB() {
		return L::loadDB('STopicUnit', 'stopic');
	}

	/**
	 * 获取专题配置
	 * 
	 * @access protected
	 * @param string $key 获取专题配置的项，为空则获取所有
	 * @return mixed 配置值
	 */
	function _getSTopicConfig($key = '') {
		if (null == $this->_stopicConfig) {
			$this->_stopicConfig = include A_P."config.php";
		}
		if ($key) {
			return $this->_stopicConfig[$key];
		}
		return $this->_stopicConfig;
	}
}

