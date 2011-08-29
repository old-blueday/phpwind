<?php
!defined('M_P') && exit('Forbidden');

require_once (R_P . 'require/bbscode.php');
require_once (R_P . 'require/functions.php');
S::gp(array('id', 'page'), '', 2);
!$page && $page = 1;
$stylepath = L::style('stylepath');
$articleService = C::loadClass('articleservice'); /* @var $articleService PW_ArticleService */
$articleModule = $articleService->getArticleModule($id);
if($articleModule->ifcheck == 2) Showmsg(data_error);

if (!is_object($articleModule) || (!isGM($windid) && !checkEditPurview($windid) && $articleModule->postDate > $timestamp)) Showmsg('文章不存在');
$content = cookContent($articleModule, $page);
$postdate = get_date($articleModule->postDate);
$pages = $articleModule->getPages($page, "{$basename}q=view&id=$id&");
$sourceUrl = $articleModule->getSourceUrl();

$columnService = C::loadClass('columnservice');
/* @var $columnService PW_columnService */
$columns = $columnService->getColumnsAndSubColumns($articleModule->columnId);

$pagePosition = getPosition($articleModule->columnId,$id,'',$cms_sitename);

$pageCache = L::loadClass('pagecache', 'pagecache');
$pageCacheConfig = C::loadClass('pagecacheconfigview', 'pagecache');
$pageCache->init($pageCacheConfig);
$tmpHotArticle = $pageCache->getData('hotArticle');
$hotArticle = $articleService->filterArticles($tmpHotArticle);
/* 记录hits */
updateArticleHitsDatanalyse($articleModule->articleId, $articleModule->columnId, $articleModule->hits);
$column = $columnService->findColumnById($articleModule->columnId);
/*
$definedSeo = array('title'=>$column['seotitle'],
					'metaDescription'=>$column['seodesc'],
					'metaKeywords'=>$column['seokeywords']);
*/
cmsSeoSettings('read',null,$column['name'],$articleModule->subject,'',$articleModule->descrip);
require_once (M_P . 'require/header.php');
require cmsTemplate::printEot('view');
footer();

function cookContent($articleModule, $page) {
	global $db_windpost;
	$content = $articleModule->getPageContent($page);
	$articleModule->showError();
	//$content = showface($content);
	$content = str_replace(array(" "),'&nbsp;',$content);
	$content = str_replace(array("\n","\r\n"),'<br />',trim($content,"\r\n \n \r"));

	if ($articleModule->ifAttach && is_array($articleModule->attach)) {
		$aids = attachment($articleModule->content);
	}
	$endAttachs = array();
	foreach ($articleModule->attach as $at) {
		if (in_array($at['attach_id'], $aids)) {
			$content = cmsAttContent($content, $at);
		} elseif ($page == $articleModule->getPageCount()) {
			$endAttachs[] = $at;
		}
	}
	$content = convert($content, $db_windpost);
	foreach ($endAttachs as $value) {
		$html = getAttachHtml($value);
		$content .= $html;
	}
	return $content;
}

function getAttachHtml($attach) {
	global $db_windpost, $basename;
	$html = '';
	switch ($attach['type']) {
		case 'img' :
			$html = '<br>' . cvpic($attach['attachurl'], 1, '', '', $attach['ifthumb']);
			break;
		default :
			$html = '<b>' . $attach['descrip'] . '</b>' . "<img src=\"$GLOBALS[imgpath]/" . L::style('stylepath') . "/file/$attach[type].gif\" align=\"absmiddle\" /><a href=\"{$basename}q=download&aid=$attach[attach_id]\" target=\"_blank\"> $attach[name]</a> ($attach[size] K)";
			$ext = strtolower(substr(strrchr($attach['name'], '.'), 1));
			if (in_array($ext, array('mp3', 'wma', 'wmv', 'rm', 'swf'))) {
				$html .= "[<a style=\"cursor:pointer\" onclick=\"playatt('$attach[attach_id]');\">&#35797;&#25773;</a>]";
			}
			break;
	}
	return "<span id=\"att_$attach[attach_id]\">" . $html . '</span>';
}

function cmsAttContent($message, $attach) {
	$html = getAttachHtml($attach);
	$message = str_replace("[attachment={$attach[attach_id]}]", "<span id=\"att_$attach[attach_id]\">" . $html . '</span>', $message);
	return $message;
}

function isURL($url){
	return preg_match('/^http:\/\/[A-Za-z0-9]*\.[A-Za-z0-9]*[\/=\?%\-&_~@\.A-Za-z0-9]*$/',$url);
}

function updateArticleHitsDatanalyse($aid, $cid, $num) {
	$articleService = C::loadClass('articleservice');
	/* @var $articleService PW_ArticleService */
	$articleService->updateArticleHits($aid);
	if (((int) $num % 13) == 0) {
		updateDatanalyse($aid, 'article_' . $cid, (int) $num, true);
	}
}
?>