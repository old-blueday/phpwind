<?php
!defined('P_W') && exit('Forbidden');
S::gp(array('action'));
$columnService = C::loadClass('columnservice');
/* @var $columnService PW_ColumnService */
if (empty($action)) {
	$result = $columnService->getAllOrderColumns();
} elseif ($action == 'add') {
	define('AJAX', 1);
	$options = $columnService->getColumnOptions();
	ifcheck(0, 'allowoffer');
	$_action = "addsubmit";
} elseif ($action == 'addsubmit') {
	define('AJAX', 1);
	S::gp(array('name', 'parentId', 'allowoffer', 'order', 'seotitle', 'seodesc', 'seokeywords'));
	if (empty($name)) Showmsg('栏目名称不能为空');
	if (strlen($name) > 20) Showmsg('栏目名称长度不能超过20个字节');
	//if ($columnService->getColumnByName($name)) Showmsg('栏目名称已经存在');
	$datas = array(array($parentId, $name, (int)$order, $allowoffer, $seotitle, $seodesc, $seokeywords));
	if (!$columnService->insertColumns($datas)) Showmsg('添加栏目失败');
	Showmsg('ajaxma_success');
} elseif ($action == 'edit') {
	define('AJAX', 1);
	S::gp(array('cid'));
	if (empty($cid)) Showmsg('非法操作请返回');
	$options = $columnService->getColumnOptions($cid);
	$column = $columnService->findColumnById($cid);
	ifcheck($column['allowoffer'], 'allowoffer');
	$_action = "editsubmit";
} elseif ($action == 'editsubmit') {
	define('AJAX', 1);
	S::gp(array('cid', 'name', 'parentId', 'allowoffer', 'order', 'seotitle', 'seodesc', 'seokeywords'));
	if (empty($cid)) Showmsg('非法操作请返回');
	if (empty($name)) Showmsg('栏目名称不能为空');
	if (strlen($name) > 20) Showmsg('栏目名称长度不能超过20个字节');
	if (!is_numeric($order)) Showmsg('排序必须为数字类型');
	$data = array($parentId, $name, $order, $allowoffer, $seotitle, $seodesc, $seokeywords);
	if (!$columnService->updateColumn($cid, $data)) Showmsg('编辑栏目失败');
	Showmsg('ajaxma_success');
} elseif ($action == 'delete') {
	S::gp(array('cid'));
	if (empty($cid)) Showmsg('非法操作请返回', $basename);
	if ($articles = $columnService->getArticlesByColumeId($cid)) Showmsg('该栏目下已存在文章数据，请删除栏目下文章，并清理回收站后再删除栏目');
	if ($columns = $columnService->getSubColumnsById($cid)) Showmsg('该栏目存在子栏目，不可删除，请移除子栏目后再删除');
	if (!$columnService->deleteColumn($cid)) Showmsg('删除栏目失败');
	Showmsg('删除栏目操作成功!', $basename);
} elseif ($action == 'editOrder') {
	S::gp(array('orders'));
	if (!$columnService->updateColumnOrders($orders)) Showmsg('操作失败');
	Showmsg('操作成功!', $basename);
}

/**
 * @param unknown_type $level
 */
function getColumnLevelHtml($level,$cid) {
	global $columnService;
	if ($level == 0) {
		$subcolumns = $columnService->getSubColumnsById($cid);
		if (empty($subcolumns)) return '<i class="expand expand_d"></i>';
		return '<i id="column_'.$cid.'" class="expand expand_b" onclick="closeAllSubColumns('.$cid.')"></i>';
	} else {
		$html .= '';
		for ($i = 1; $i < $level; $i++) {
			$html .= '<i id="" class="lower lower_a"></i>';
		}
		$html .= '<i id="" class="lower"></i>';
	}
	return $html;
}

include PrintMode('column');
if (defined('AJAX')) ajax_footer();
exit();
?>