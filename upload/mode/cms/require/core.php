<?php
!defined('M_P') && exit('Forbidden');

/**
 * @param string $_page 当前页面信息(list,view)
 * @param string $_definedSeo 自定义SEO配置信息
 * @param string $_fname 板块名称
 * @param string $_types 分类信息
 * @param string $_subject 帖子名称
 * @param string $_tags 标签
 * @param string $_summary 摘要
 */
function cmsSeoSettings($_page = 'index', $_definedSeo = '', $_column = '', $_subject = '', $_tags = '', $_summary = '') {
	global $cms_sitename, $cms_seoset;
	/* 网站名称，栏目名称，文章名称，标签名称，文章概要  */
	$_targets = array('{wzmc}', '{lmmc}', '{armc}', '{tmc}', '{wzgy}');
	$_replace = array($cms_sitename, $_column, $_subject, $_tags, $_summary);
	
	/*获取SEO配置信息  自定义->后台定义->默认*/
	empty($_definedSeo['title']) &&	$_definedSeo['title'] = $cms_seoset['title'][$_page];
	empty($_definedSeo['metaDescription']) && $_definedSeo['metaDescription'] = $cms_seoset['metaDescription'][$_page];
	empty($_definedSeo['metaKeywords'])	&& $_definedSeo['metaKeywords'] = $cms_seoset['metaKeywords'][$_page];
	
	/*如果以上设置为空则采用默认配置*/
	$_default = array('title' => '{armc} | {lmmc} - {wzmc}', 'descp' => '{wzgy} | {armc}', 
		'keywords' => '{tmc} , {armc} | {lmmc} - {wzmc}');
	
	return seoSettings($_definedSeo, $_replace, $_default, $_targets);
}

/**
 * 获取发表文章权限
 * @param string $username
 * @param array $_G
 * return bool
 */
function getPostPurview($username, $_G) {
	if (isGM($username)) return true;
	if (isset($_G['cms_post']) && $_G['cms_post']) return true;
	return false;
}

/**
 * 检查用户是否有管理文章权限
 * @param unknown_type $name
 * @param unknown_type $cid
 * @return string|string|string|boolean
 */
function checkEditPurview($name, $cid) {
	if (isGM($name)) return true;
	if (!$name) return false;
	$cms_editadmin = L::config('cms_editadmin', 'cms_config');
	if (!$cms_editadmin) return false;
	if (empty($cid)) {
		$_keys = array_keys($cms_editadmin);
		foreach ($_keys as $key) {
			if (in_array($name, $cms_editadmin[$key])) return true;
		}
		return false;
	}
	return in_array($name, $cms_editadmin[$cid]);
}

function updateArticleHitsDatanalyse($aid, $cid, $num) {
	$articleService = C::loadClass('articleservice');
	/* @var $articleService PW_ArticleService */
	$articleService->updateArticleHits($aid);
	if (((int) $num % 13) == 0) {
		updateDatanalyse($aid, 'article_' . $cid, (int) $num, true);
	}
}

/**
 * 获取页面位置
 * @param $cid
 * @param $id
 * @param $columns
 */
function getPosition($cid, $id = 0, $columns = array()) {
	if (!$columns) {
		$columnService = C::loadClass('columnservice');
		$columns = $columnService->findAllColumns();
	}
	$postion = '<a href="index.php?m=cms" class="b">首页</a> ';
	if (!$cid) {return $postion . '&raquo; 文章列表';}
	$columnLists = getColumnList($columns, $cid);
	
	foreach ($columnLists as $value) {
		$postion .= '&raquo; <a href="' . getColumnUrl($value['column_id']) . '">' . $value['name'] . '</a> ';
	}
	if (!$id) {return $postion;}
	return $postion . '&raquo; 正文内容';
}

/**
 * 获取栏目链接地址
 * @param unknown_type $cid
 */
function getColumnUrl($cid) {
	return 'mode.php?m=cms&q=list&column=' . $cid;
}

/**
 * 获取文章链接地址
 * @param $id
 */
function getArticleUrl($id) {
	return 'mode.php?m=cms&q=view&id=' . $id;
}

function getColumnList($columns, $cid) {
	static $list = array();
	if (!$cid) return $list;
	$thisColumn = $columns[$cid];
	
	array_unshift($list, $thisColumn);
	
	$parentColumnId = $thisColumn['parent_id'];
	
	return getColumnList($columns, $parentColumnId);
}

/**
 * 更新文章列表
 */
function updateArticleHits() {
	global $hitsize, $hitfile, $db;
	if (file_exists($hitfile)) {
		if (!$hitsize) $hitsize = @filesize($hitfile);
		if ($hitsize < 10240) {
			$hitarray = explode("\t", readover($hitfile));
			$hits = array_count_values($hitarray);
			$count = 0;
			$hits_a = '';
			foreach ($hits as $key => $val) {
				$hits_a .= ",('$key','$val')";
				if (++$count > 300) break;
			}
			if ($hits_a) {
				$hits_a = trim($hits_a, ', ');
				$db->query("CREATE TEMPORARY TABLE heap_hitupdate (article_id INT(10) UNSIGNED NOT NULL ,hits SMALLINT(6) UNSIGNED NOT NULL) TYPE = HEAP");
				$db->update("INSERT INTO heap_hitupdate (article_id,hits) VALUES $hits_a");
				$db->update("UPDATE pw_cms_articleextend as a, heap_hitupdate as h SET a.hits = a.hits+h.hits WHERE a.article_id=h.article_id");
				$db->query("DELETE FROM heap_hitupdate");
			}
			unset($hitarray, $hits, $hits_a);
		}
		P_unlink($hitfile);
	}
}

class cmsTemplate {
	
	var $dir;

	function cmsTemplate() {
		$this->dir = M_P . 'template/';
	}

	function getpath($template, $EXT = 'htm') {
		$srcTpl = $this->dir . 'default/' . "$template.$EXT";
		$tarTpl = D_P . "data/tplcache/cms_" . $template . '.' . $EXT;
		
		if (!file_exists($srcTpl)) return false;
		
		if (pwFilemtime($tarTpl) > pwFilemtime($srcTpl)) return $tarTpl;
		
		return modeTemplate($srcTpl, $tarTpl);
	}

	function getDefaultDir() {
		return $this->dir . 'default/';
	}

	//static function
	function printEot($template, $EXT = 'htm') {
		static $uTemplate = null;
		isset($uTemplate) || $uTemplate = new template(new cmsTemplate());
		return $uTemplate->printEot($template, $EXT);
	}
}

class C extends PW_BaseLoader {

	/**
	 * 类文件的加载入口
	 * 
	 * @param string $className 类的名称
	 * @param string $dir 目录：末尾不需要'/'
	 * @param boolean $isGetInstance 是否实例化
	 * @return mixed
	 */
	function loadClass($className, $dir = '', $isGetInstance = true) {
		return parent::_loadClass($className, 'mode/cms/lib/' . parent::_formatDir($dir), $isGetInstance);
	}

	/**
	 * 加载db类
	 * @param $className
	 */
	function loadDB($dbName, $dir = '') {
		parent::_loadBaseDB();
		return C::loadClass($dbName . 'DB', parent::_formatDir($dir) . 'db');
	}
}
?>