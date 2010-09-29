<?php
!defined('P_W') && exit('Forbidden');
return array(
	'name'	=>'房产',
	'banner'=>'apps/stopic/data/style/house/banner.jpg',
	'layout_set' => array(
		'bgcolor'		=> '#2e0307',
		'areabgcolor'	=> '#ffffff',
		'fontcolor'		=> '#000000',
		'navfontcolor'	=> '#ffffff',
		'navbgcolor'	=> '#862c07',
		'othercss'		=> '.wrap{width:960px;margin:0 auto 0;overflow:hidden;}/*专题内容框架*/
#main{padding:10px;}/*专题内边距*/
.zt_nav li{float:left;line-height:35px; font-size:14px;margin:0 10px; white-space:nowrap;}/*导航样式*/
.itemDraggable{border:1px solid #c9c9c9;margin-top:10px;overflow:hidden;}/*模型外边框*/
.itemDraggable .itemHeader{border-bottom:1px solid #c9c9c9;background:#a33f1b url(apps/stopic/data/style/house/h.png) right 0 repeat-x;padding:4px 10px; font-weight:700;color:#fff;}/*标题栏*/
.itemDraggable .itemContent{padding:4px 10px;}/*模型内边距*/
.itemDraggable .itemContent li{line-height:24px;}/*列表行高*/
'
	),
);
?>