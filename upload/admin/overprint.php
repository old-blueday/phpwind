<?php
!defined('P_W') && exit('Forbidden');

$overPrintClass = L::loadclass("overprint", 'forum');


if(empty($action)){
	$relatedSelect = $overPrintClass->getRelatedSelect('');
	$isOpenSelect = $overPrintClass->getStatusSelect('');
	$iconPath = $overPrintClass->getIconPath();
	$overprints = $overPrintClass->getOverPrints();
	$overprintlists = array();
	foreach($overprints as $overprint){
		$list = array();
		$name = "list[".$overprint['id']."][related]";
		$isopen = "list[".$overprint['id']."][isopen]";
		$list['select'] = $overPrintClass->getRelatedSelect($overprint['related'],$name,$name);
		$list['isopen'] = ($overprint['isopen'] > 0) ? "启用" : "关闭";
		$list['open'] = ($overprint['isopen'] > 0) ? "checked" : "";
		$overprintlists[] = array_merge($overprint,$list);
	}
	
	$icons = $overPrintClass->getOverPrintIcons();
	include PrintEot('overprint');exit;
}elseif($action == "add"){
	S::gp(array('title','icon','related','isopen'));
	$title = trim($title);
	$icon = trim($icon);
	$related = intval($related);
	$isopen = in_array($isopen,array(0,1)) ? $isopen : 0;
	($title == "") && adminmsg("主题印戳 关联名称不能为空");
	($icon == "" || !$overPrintClass->checkIcon($icon)) && adminmsg("请选择主题印戳图标或图标格式不正确");
	$data = array();
	$data['title']      = $title;
	$data['icon']       = $icon;
	$data['related']    = $related;
	$data['total']      = 1;
	$data['createtime'] = time();
	$data['isopen']     = $isopen;
	$result = $overPrintClass->addOverPrint($data);
	(!$result) && adminmsg("主题印戳增加失败 ");
	adminmsg('operate_success',"$basename&action=");
}elseif($action == "manage"){
	S::gp(array('list'));
	!is_array($list) && adminmsg("提示的数据有误 ");
	$overprints = array();
	foreach($list as $id=>$v){
		($v['title'] == "") && adminmsg("主题印戳 关联名称不能为空");
		($v['icon']  == "" || !$overPrintClass->checkIcon($v['icon'])) && adminmsg("请选择主题印戳图标或图标格式不正确");
		$t = array();
		$t['title']   = $v['title'];
		$t['icon']    = $v['icon'];
		$t['isopen']  = $v['check'] ? 1 : 0;
		$t['related'] = ($operate == "close") ? '-20' : $v['related'];
		$overprints[$id] = $t;
	}
	!$overprints && adminmsg('operate_success',"$basename&action=");
	$status = ($isopen == 'open') ? 1 : 0;
	foreach($overprints as $id=>$overprint){
		$overPrintClass->updateOverPrint($overprint,$id);/*更新*/
	}
	adminmsg('operate_success',"$basename&action=");
}elseif($action == "delete"){
	S::gp(array('id'));
	($id<0) && adminmsg("主题印戳ID错误");
	$overPrintClass->deleteOverPrint($id); /*删除*/
	adminmsg('operate_success',"$basename&action=");
}else{
	
	
}


























