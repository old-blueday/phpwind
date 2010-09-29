<?php
/**
 * 风格样式配置
 * 
 * 包含配置项：
 * <code>
 *   name 风格样式名
 *   banner 横幅图片相对地址
 *   layout_set 风格样式css配置
 *     bgcolor 背景颜色
 *     areabgcolor 区块背景颜色
 *     fontcolor 文字颜色
 *     navfontcolor 导航文件颜色
 *     navbgcolor 导航背景颜色
 *     othercss 其他样式
 * </code>
 * 
 * @package STopic
 */

!defined('P_W') && exit('Forbidden');
return array(
	'name'	=>'母婴_橙',
	'banner'=>'apps/stopic/data/style/baby_org/banner.jpg',
	'layout_set' => array(
		'bgcolor'		=> '#ffdf98',
		'areabgcolor'	=> '#ffffff',
		'fontcolor'		=> '#c58809',
		'navfontcolor'	=> '#ffffff',
		'navbgcolor'	=> '#e1a521',
		'othercss'		=> '.wrap{width:960px;margin:0 auto 0;overflow:hidden;}/*专题内容框架*/
#main{padding:10px;}/*专题内边距*/
.zt_nav li{float:left;line-height:35px; font-size:14px;margin:0 10px; white-space:nowrap;}/*导航样式*/
.itemDraggable{margin-top:10px;overflow:hidden;}/*模型外边框*/
.itemDraggable .itemHeader{background:#f7f7f7 url(apps/stopic/data/style/baby_org/h-orange.png) right 0 repeat-x;padding:6px 10px 5px; font-weight:700;color:#fff;border:1px solid #e9be62;}/*标题栏*/
.itemDraggable .itemContent{padding:4px 10px;border:1px solid #eaebe6;}/*模型内边距*/
.itemDraggable .itemContent li{line-height:24px;}/*列表行高*/
'
	),
);
?>