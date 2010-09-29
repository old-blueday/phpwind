<?php
!defined('P_W') && exit('Forbidden');
return array(
	'name'	=>'汽车',
	'banner'=>'apps/stopic/data/style/car/banner.jpg',
	'layout_set' => array(
		'bgcolor'		=> '#2c0001',
		'areabgcolor'	=> '#ffffff',
		'fontcolor'		=> '#b93936',
		'navfontcolor'	=> '#ffffff',
		'navbgcolor'	=> '#4e0000',
		'othercss'		=> '.wrap{width:960px;margin:0 auto 0;overflow:hidden;}/*专题内容框架*/
#main{padding:10px;}/*专题内边距*/
.zt_nav li{float:left;line-height:35px; font-size:14px;margin:0 10px; white-space:nowrap;}/*导航样式*/
.itemDraggable{border:1px solid #c9c9c9;margin-top:10px;overflow:hidden;}/*模型外边框*/
.itemDraggable .itemHeader{border-bottom:1px solid #c9c9c9;background:#e3e3e3 url(apps/stopic/data/style/car/h.png) right 0 repeat-x;padding:4px 10px; font-weight:700;color:#555;}/*标题栏*/
.itemDraggable .itemContent{padding:4px 10px;}/*模型内边距*/
.itemDraggable .itemContent li{line-height:24px;}/*列表行高*/
'
	),
);
?>