<?php
!defined('P_W') && exit('Forbidden');
return array(
	'name'	=>'团购',
	'banner'=>'apps/stopic/data/style/buying/banner.jpg',
	'layout_set' => array(
		'bgcolor'		=> '#000000',
		'areabgcolor'	=> '#ffffff',
		'fontcolor'		=> '#370123',
		'navfontcolor'	=> '#ffffff',
		'navbgcolor'	=> '#000000',
		'othercss'		=> '.wrap{width:960px;margin:0 auto 0;overflow:hidden;}/*专题内容框架*/
#main{padding:10px;}/*专题内边距*/
.zt_nav li{float:left;line-height:35px; font-size:14px;margin:0 10px; white-space:nowrap;}/*导航样式*/
.itemDraggable{border:1px solid #e4e4e4;margin-top:10px;overflow:hidden;}/*模型外边框*/
.itemDraggable .itemHeader{background:#ffeae1 url(apps/stopic/data/style/buying/h.png) right 0 repeat-x;padding:10px 10px 0; font-weight:700;color:#dc4f12;}/*标题栏*/
.itemDraggable .itemContent{padding:4px 10px;}/*模型内边距*/
.itemDraggable .itemContent li{line-height:24px;}/*列表行高*/
'
	),
);
?>