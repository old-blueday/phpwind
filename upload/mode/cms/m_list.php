<?php
!defined('M_P') && exit('Forbidden');
S::gp(array('page', 'action'));
$articleService = C::loadClass('articleservice');
/* @var $articleService PW_ArticleService */
$db_perpage = 10;
if (empty($action)) {
	S::gp(array('column'));
	$column = (int) $column ? (int) $column : 0;
	$columnService = C::loadClass('columnservice');
	/* @var $columnService PW_columnService */
	$subColumns = $columnService->getAllOrderColumns($column);
	$subColumnIds = array_keys($subColumns);
	if (isGM($windid) || checkEditPurview($windid,$column)) {
		$articleCount = $articleService->searchArticleCount($subColumnIds,'','',1);
		$page = validatePage($page, $articleCount);
		$articleList = $articleService->searchAtricles($subColumnIds, '', '', 1, '', '', ($page - 1) * $db_perpage, $db_perpage);
	} else {
		$articleCount = $articleService->searchArticleCount($subColumnIds,'','',1,'',$timestamp);
		$page = validatePage($page, $articleCount);
		$articleList = $articleService->searchAtricles($subColumnIds, '', '', 1, '',$timestamp, ($page - 1) * $db_perpage, $db_perpage);
	}
	$pages = numofpage($articleCount, $page, ceil($articleCount / $db_perpage), $basename . 'q=list&column=' . $column . '&');
		
	$pageCache = L::loadClass('pagecache', 'pagecache');
	$pageCacheConfig = C::loadClass('pagecacheconfiglist', 'pagecache');
	$pageCache->init($pageCacheConfig);
	$tmpHotArticle = $pageCache->getData('hotArticle');
	$hotArticle = $articleService->filterArticles($tmpHotArticle);

	list($columns, $columnInfo) = $columnService->getCurrentAndSubColumns($column);
	if (!S::isArray($columns)) {
		list($columns, $columnInfo) = $columnService->getCurrentAndSubColumns($columnInfo['parent_id']);
	}
	/* update hits */
	/*$hitfile = D_P . "data/bbscache/cms_hits.txt";
	$hitsize = @filesize($hitfile);
	if ($hitsize && $hitsize > 1024) {
		updateArticleHits();
	}*/
	$pagePosition = getPosition($column,'','',$cms_sitename);
	$_definedSeo = array('title'=>$subColumns[$column]['seotitle'],
						 'metaDescription'=>$subColumns[$column]['seodesc'],
						 'metaKeywords'=>$subColumns[$column]['seokeywords']);

	cmsSeoSettings('index',$_definedSeo,$subColumns[$column]['name']);
} elseif ($action == 'del') {
	define('AJAX', 1);
	S::gp(array('ids','column_id'));
	/*
	if(!checkEditPurview($windid,$column_id)) {
		Showmsg('您没有权限删除帖子');
	}
	*/
	if (strpos($ids, ',')) $ids = explode(',', $ids);
	$articleDB = C::loadDB('article');
	$list = $articleDB->getArticlesByIds(is_array($ids) ? $ids : array($ids));
	if (empty($list)) {
		Showmsg('data_error');
	}
	foreach ($list as $key => $value) {
		if (!checkEditPurview($windid, $value['column_id'])) {
			Showmsg('您没有权限删除帖子');
		}
	}
	if (!$articleService->deleteArticlesToRecycle($ids)) {
		echo 'error';
		ajax_footer();
	}
	echo 'success';
	ajax_footer();
}

require_once (M_P . 'require/header.php');
require cmsTemplate::printEot('list');
footer();
?>