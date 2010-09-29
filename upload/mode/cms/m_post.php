<?php
!defined('M_P') && exit('Forbidden');
InitGP(array('action', 'step', 'cid'));

if (!getPostPurview($windid, $_G) && !checkEditPurview($windid, $cid)) Showmsg('您没有发表文章的权限');

$articleService = C::loadClass('articleservice'); /* @var $articleService PW_ArticleService */
cmsSeoSettings();

if (!$action) {
	if (!$step) {
		InitGP(array('sourcetype', 'sourceid'));
		$columnService = C::loadClass('columnservice');
		/* @var $columnService PW_columnService */
		$columns = $columnService->getAllOrderColumns();
		$purviews = $columnService->getAllPurviewColumns($windid);

		if (!isGM($windid)) {
			foreach ($columns as $key => $value) {
				if ((!$value['allowoffer'] || !getPostPurview($windid, $_G)) && !in_array($value['column_id'], $purviews)) {
					unset($columns[$key]);
				}
			}
		}
		$hasSource = isGM($windid) || S::inArray($cid, $purviews) ? true : false;//栏目编辑或创始人才有权限使用自动调用
		if (!$hasSource) {
			$sourcetype = $sourceid = null;
		}
		$articleModule = $articleService->getArticleModuleFromSource($sourcetype, $sourceid);
		$content = $articleModule->content;
		$articleModule->setColumnId($cid);

		list($filetype, $filetypeinfo) = initFileTypeInfo($db_uploadfiletype);
		require_once (M_P . 'require/header.php');
	} else {
		InitGP(array('cms_subject', 'atc_content', 'cms_descrip'), 'P', 0);
		InitGP(array('cms_sourcetype', 'cms_sourceid', 'cid', 'cms_jumpurl', 'cms_author', 'cms_frominfo',
			'cms_fromurl', 'cms_relate', 'addnewpage'));
		PostCheck();
		//		if (!checkEditPurview($windid,$cid)) Showmsg('你没有权限向本栏目添加文章');

		$columnService = C::loadClass('columnservice');
		$column = $columnService->findColumnById((int)$cid);
		$purviews = $columnService->getAllPurviewColumns($windid);
		if (!isGM($windid) && !in_array($column['column_id'], $purviews) && (!$column['allowoffer'] || !getPostPurview($windid, $_G))) {
			Showmsg('你没有权限向本栏目添加文章');
		}

		$articleModule = C::loadClass('articleModule'); /* @var $articleModule PW_ArticleModule */

		$articleModule->setSubject($cms_subject);
		$articleModule->setContent($atc_content);
		$articleModule->setDescrip($cms_descrip);
		$articleModule->setColumnId($cid);
		$articleModule->setJumpUrl($cms_jumpurl);
		$articleModule->setPostDate($timestamp);
		$articleModule->setModifyDate($timestamp);
		$articleModule->setFromInfo($cms_frominfo);
		$articleModule->setFromUrl($cms_fromurl);
		$articleModule->setAuthor($cms_author);
		$articleModule->setUser($windid);
		$articleModule->setUserId($winduid);
		$articleModule->setIfCheck(1);
		$articleModule->setSourceType($cms_sourcetype);
		$articleModule->setSourceId($cms_sourceid);
		$articleModule->setRelate($cms_relate);
		$articleModule->setAttach();
		$articleModule->showError();

		$result = $articleService->addArticle($articleModule);

		if ($result) {
			$jumpUrl = $addnewpage ? $basename . "q=post&action=edit&id=" . $result . "&page=add" : $basename . "q=view&id=" . $result;

			$columnService = C::loadClass('columnservice'); /* @var $columnService PW_ColumnService */
			$cname = $columnService->getColumnNameByCIds($cid);
			$weiboService = L::loadClass('weibo','sns');/* @var $weiboService PW_Weibo */
			$weiboContent = substrs(stripWindCode($weiboService->escapeStr(strip_tags($atc_content))), 125);
			$weiboExtra = array(
							'title' => stripslashes($cms_subject),
							'cid' => $cid,
							'cname' => $cname,
							);
			$weiboService->send($winduid,$weiboContent,'cms',$result,$weiboExtra);

			refreshto($jumpUrl, '添加文章成功!');
		} else {
			Showmsg('添加文章失败');
		}
	}
} elseif ($action == 'edit') {
	InitGP(array('id', 'page'));
	$articleModule = $articleService->getArticleModule($id);
	if (!checkEditPurview($windid, $articleModule->columnId)) Showmsg('你没有权限编辑本栏目的文章');
	if (!$step) {
		if (!$page) $page = 1;
		if (!is_object($articleModule)) Showmsg('文章不存在');

		$columnService = C::loadClass('columnservice'); /* @var $columnService PW_columnService */
		$columns = $columnService->getAllOrderColumns();

		$attach = initAttach($articleModule->attach);

		$content = $articleModule->getPageContent($page);
		$articleModule->showError();

		$pages = $articleModule->getPages($page, 'mode.php?m=cms&q=post&action=edit&id=' . $id . '&');

		list($filetype, $filetypeinfo) = initFileTypeInfo($db_uploadfiletype);
		require_once (M_P . 'require/header.php');
	} else {
		InitGP(array('cms_subject', 'atc_content', 'cms_descrip'), 'P', 0);
		InitGP(array('cms_sourcetype', 'cms_sourceid', 'cid', 'cms_jumpurl', 'cms_author', 'cms_frominfo',
			'cms_fromurl', 'cms_relate', 'keep', 'oldatt_desc', 'addnewpage','cms_sourcetype','cms_sourceid'));
		PostCheck();
		$articleModule->setSubject($cms_subject);
		$articleModule->setContent($atc_content, $page);
		$articleModule->setDescrip($cms_descrip);
		$articleModule->setColumnId($cid);
		$articleModule->setJumpUrl($cms_jumpurl);
		$articleModule->setModifyDate($timestamp);
		$articleModule->setFromInfo($cms_frominfo);
		$articleModule->setFromUrl($cms_fromurl);
		$articleModule->setAuthor($cms_author);
		$articleModule->setUser($windid);
		$articleModule->setUserId($winduid);
		$articleModule->setRelate($cms_relate);
		$articleModule->setSourceType($cms_sourcetype);
		$articleModule->setSourceId($cms_sourceid);
		$articleModule->setAttach($oldatt_desc, $keep);
		$articleModule->showError();

		$result = $articleService->updateArticle($articleModule);
		if ($result) {
			$jumpUrl = $addnewpage ? $basename . "q=post&action=edit&id=" . $id . "&page=add" : $basename . "q=view&id=" . $id;
			refreshto($jumpUrl, '修改文章成功!');
		} else {
			Showmsg('修改文章失败');
		}
	}
} elseif ($action == 'deletepage') {
	InitGP(array('id', 'page'));
	$articleModule = $articleService->getArticleModule($id);

	if (!checkEditPurview($windid, $articleModule->columnId)) Showmsg('你没有权限编辑本栏目的文章');

	$articleModule->deletePage($page);
	$articleModule->showError();
	$result = $articleService->updateArticle($articleModule);
	if ($result) {
		refreshto("{$basename}q=post&action=edit&id=$id&page=1", 'operate_success', 2);
	} else {
		Showmsg('删除分页失败');
	}
}

require cmsTemplate::printEot('post');
footer();

function initFileTypeInfo($db_uploadfiletype) {
	$uploadfiletype = ($db_uploadfiletype) ? unserialize($db_uploadfiletype) : '';
	$filetypeinfo = $filetype = '';
	if ($uploadfiletype) {
		foreach ($uploadfiletype as $type => $size) {
			$filetype .= ' ' . $type . ' ';
			$filetypeinfo .= $type . ":" . $size . "KB; ";
		}
	}
	return array($filetype, $filetypeinfo);
}

function initAttach($attachs) {
	$attach = '';
	if ($attachs) {
		foreach ($attachs as $key => $value) {
			$attach .= "'$key' : ['$value[name]', '$value[size]', '$value[attachurl]', '$value[type]', '', '', '', '$value[descrip]'],";
		}
		$attach = rtrim($attach, ',');
	}
	return $attach;
}
?>