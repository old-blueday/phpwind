<?php
!defined('P_W') && exit('Forbidden');
S::gp(array('action', 'type'));
$baseurl = $basename;
$db_perpage = 20;
$basename = $type ? $basename . '&type=' . $type : $basename;
$articleService = C::loadClass('articleservice');
/* @var $articleService PW_ArticleService */
if (empty($action)) {
	S::gp(array('page', 'cid', 'title', 'author', 'user'));
	$conditions = array('cid', 'title', 'author');
	$ifcheck = $type == 'recycle' ? '2' : ($type == 'uncheck' ? '0' : '1');
	$count = $articleService->searchArticleCount($cid, $title, $author, $ifcheck, $user);
	$columnService = C::loadClass('columnservice');
	$columns = $columnService->findAllColumns();
	$columnSelect = "<option value=''></option>";
	foreach ($columns as $column) {
		if (!$column['parent_id']) {
			$columnSelect .= "<option value='" . $column['column_id'] . "'>" . $column['name'] . "</option>";
			getColumnSelectHtml($columns, $column['column_id'], &$columnSelect, 1);
		}
	}
	$page = validatePage($page, $count);
	$articles = $articleService->searchAtricles($cid, $title, $author, $ifcheck, $user,'', ($page - 1) * $db_perpage, $db_perpage);
	$pages = numofpage($count, $page, ceil($count / $db_perpage), $basename . '&cid=' . $cid . '&');
} elseif ($action == 'move') {
	S::gp(array('aids', 'column_id', 'cid'));
	if (empty($aids)) Showmsg('请选择要移动的文章', 'javascript:history.go(-1);');
	if (empty($column_id)) Showmsg('非法操作请返回', 'javascript:history.go(-1);');
	if (!$articleService->moveArticlesByIds($aids, $column_id)) Showmsg('移动文章操作失败', $basename);
	Showmsg('移动文章操作成功!', $basename . '&cid=' . $cid);
} elseif ($action == 'del') {
	S::gp(array('aids', 'cid'));
	if (empty($aids) || !is_array($aids)) Showmsg('请选择要彻底删除的文章', $basename . '&cid=' . $cid);
	if (!$articleService->deleteArticlesByIds($aids)) Showmsg('彻底删除操作失败', $basename . '&cid=' . $cid);
	Showmsg('删除操作成功!', $basename . '&cid=' . $cid);
} elseif ($action == 'recycle') {
	S::gp(array('aids', 'cid'));
	if (empty($aids) || !is_array($aids)) Showmsg('请选择要删除的文章', $basename . '&cid=' . $cid);
	if (!$articleService->deleteArticlesToRecycle($aids)) Showmsg('删除操作失败', $basename . '&cid=' . $cid);
	Showmsg('操作成功!', $basename . '&cid=' . $cid);
} elseif ($action == 'revert') {
	S::gp(array('aids'));
	if (empty($aids) || !is_array($aids)) Showmsg('请选择要还原的文章', $basename);
	if (!$articleService->revertArticleFromRecycle($aids)) Showmsg('还原操作失败', $basename);
	Showmsg('操作成功!', $basename);
} elseif ($action == 'cleanRecycle') {
	if (!$articleService->cleanRecycle()) Showmsg('清空回收站操作失败', $basename);
	Showmsg('清空操作成功!', $basename);
}

function getColumnSelectHtml($columns, $cid, $columnSelect, $l = 1) {
	foreach ($columns as $c) {
		if ($c['parent_id'] == $cid) {
			$_tag = '|';
			for ($i = 0; $i < $l; $i++) {
				$_tag .= '--';
			}
			$columnSelect .= "<option value='" . $c['column_id'] . "'>" . $_tag . $c['name'] . "</option>";
			getColumnSelectHtml($columns, $c['column_id'], & $columnSelect, $l + 1);
		}
	}
}

include PrintMode('article');
if (defined('AJAX')) ajax_footer();
exit();
?>