<?php
!defined('P_W') && exit('Forbidden');
return array(
	'name'	=>'婚庆_粉',
	'banner'=>'apps/stopic/data/style/wedding_pink/banner.jpg',
	'layout_set' => array(
		'bgcolor'		=> '#cd6587',
		'areabgcolor'	=> '#ffffff',
		'fontcolor'		=> '#e46882',
		'navfontcolor'	=> '#ffffff',
		'navbgcolor'	=> '#ce5683',
		'othercss'		=> '.wrap{width:960px;margin:0 auto 0;overflow:hidden;}/*专题内容框架*/
#main{padding:10px;}/*专题内边距*/
.zt_nav li{float:left;line-height:35px; font-size:14px;margin:0 10px; white-space:nowrap;}/*导航样式*/
.itemDraggable{border:1px solid #eaebe6;margin-top:10px;overflow:hidden;}/*模型外边框*/
.itemDraggable .itemHeader{background:#ce5683 url(apps/stopic/data/style/wedding_pink/h-pink.png) right 0 repeat-x;padding:4px 10px; font-weight:700;color:#fff;}/*标题栏*/
.itemDraggable .itemContent{padding:4px 10px;}/*模型内边距*/
.itemDraggable .itemContent li{line-height:24px;}/*列表行高*/
'
	),
);
?>