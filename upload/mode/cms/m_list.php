<?php
!defined('M_P') && exit('Forbidden');
InitGP(array('page', 'action'));
$articleService = C::loadClass('articleservice');
/* @var $articleService PW_ArticleService */
$db_perpage = 10;
if (empty($action)) {
	InitGP(array('column'));

	$columnService = C::loadClass('columnservice');
	/* @var $columnService PW_columnService */
	$subColumns = $columnService->getAllOrderColumns($column);
	$subColumnIds = array_keys($subColumns);
	$articleCount = $articleService->searchArticleCount($subColumnIds);
	$page = validatePage($page, $articleCount);
	$articleList = $articleService->searchAtricles($subColumnIds, '', '', '', '',($page - 1) * $db_perpage, $db_perpage);
	$pages = numofpage($articleCount, $page, ceil($articleCount / $db_perpage), $basename . '&q=list&column=' . $column . '&');

	$pageCache = L::loadClass('pagecache', 'pagecache');
	$pageCacheConfig = C::loadClass('pagecacheconfiglist', 'pagecache');
	$pageCache->init($pageCacheConfig);

	$columns = $columnService->getColumnsAndSubColumns($column);

	/* update hits */
	/*$hitfile = D_P . "data/bbscache/cms_hits.txt";
	$hitsize = @filesize($hitfile);
	if ($hitsize && $hitsize > 1024) {
		updateArticleHits();
	}*/

	$pagePosition = getPosition($column);

	$_definedSeo = array('title'=>$subColumns[$column]['seotitle'],
						 'metaDescription'=>$subColumns[$column]['seodesc'],
						 'metaKeywords'=>$subColumns[$column]['seokeywords']);

	cmsSeoSettings('index',$_definedSeo,$subColumns[$column]['name']);
} elseif ($action == 'del') {
	define('AJAX', 1);
	InitGP(array('ids','column_id'));
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
	if (!$articleService->deleteArticlesByIds($ids)) {
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