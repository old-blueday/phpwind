<?php
/**
 * 专题配置文件
 * 
 * 包含配置项：
 * <code>
 *   bgUploadPath 背景图片上传路径
 *   bgBaseUrl 背景图片基础url
 *   bgDefalutPath 背景图片默认存放目录
 *   bgDefalutUrl 背景图片默认url
 *   layoutPath 布局数据存放目录
 *   layoutBaseUrl 布局基础url
 *   stylePath 风格样式数据存放目录
 *   styleBanner 风格样式横幅图片文件名
 *   styleMiniPreview 风格样式缩略图文件名
 *   styleBaseUrl 风格样式基础url
 *   layoutConfig 布局配置
 *   layoutTypes 布局类型列表
 *   layout_set 默认风格样式的css配置
 *   htmlSuffix 专题保存文件的扩展名
 *   htmlDir 专题保存目录
 *   htmlUrl 专题url
 *   blockTypes 模块类型列表
 * </code>
 * 
 * @package STopic
 */

!defined('P_W') && exit('Forbidden');

return array(
	"bgUploadPath" => R_P . (isset($GLOBALS['db_attachname']) && '' != $GLOBALS['db_attachname'] ? $GLOBALS['db_attachname'] : 'attachment') . "/stopic/",
	"bgBaseUrl" => $GLOBALS['db_bbsurl'] . "/attachment/stopic/",

	"bgDefalutPath" => A_P . "data/uploadbg/",
	"bgDefalutUrl" => $GLOBALS['db_bbsurl'] . "/apps/stopic/data/uploadbg/",

	"layoutPath" => A_P."data/layout/",
	"layoutBaseUrl" => $GLOBALS['db_bbsurl'] . "/apps/stopic/data/layout/",

	"stylePath" => A_P."data/style/",
	"styleBanner" => "banner.jpg",
	"stylePreview" => "preview.jpg",
	"styleMiniPreview" => "mini_preview.jpg",
	"styleBaseUrl"	=> $GLOBALS['db_bbsurl'] . "/apps/stopic/data/style/",

	"layoutConfig" => array(
		"logo" => "logo.png",
		"html" => "layout.htm",
	),
	"layoutTypes" => array(
		"type1v0" => "直列",
		"type1v1" => "1:1",
		"type1v2" => "1:2",
		"type2v1" => "2:1",
		"type1v1v1" => "1:1:1",
	),
	"layout_set" => array(
		'bannerurl'		=> $GLOBALS['db_bbsurl'] . '/apps/stopic/data/style/wedding_pink/banner.jpg',
		'bgcolor'		=> '#cd6587',
		'areabgcolor'	=> '#ffffff',
		'fontcolor'		=> '#e46882',
		'navfontcolor'	=> '#ffffff',
		'navbgcolor'	=> '#ce5683',
		"othercss"		=> <<<EOT
.wrap{width:960px;margin:0 auto 0;overflow:hidden;}/*专题内容框架*/
#main{padding:10px;}/*专题内边距*/
.zt_nav li{float:left;line-height:35px; font-size:14px;margin:0 10px; white-space:nowrap;}/*导航样式*/
.itemDraggable{border:1px solid #eaebe6;margin-bottom:10px;overflow:hidden;}/*模型外边框*/
.itemDraggable .itemHeader{background:#ce5683 url(apps/stopic/data/style/wedding_pink/h-pink.png) right 0 repeat-x;padding:4px 10px; font-weight:700;color:#fff;}/*标题栏*/
.itemDraggable .itemContent{padding:4px 10px;}/*模型内边距*/
.itemDraggable .itemContent li{line-height:24px;}/*列表行高*/
EOT
	),

	'htmlSuffix'=>'.html',

	"htmlDir" => R_P.('' != $GLOBALS['db_stopicdir'] ? $GLOBALS['db_stopicdir'] : $GLOBALS['db_htmdir'].'/stopic'),
	"htmlUrl" => $GLOBALS['db_bbsurl'].'/'.('' != $GLOBALS['db_stopicdir'] ? $GLOBALS['db_stopicdir'] : $GLOBALS['db_htmdir'].'/stopic'),
	
	"blockTypes" => array(
		"banner" => "头部横幅",
		"nvgt" => "导航",
		"thrd" => "帖子列表",
		"thrdSmry" => "帖子摘要",
		"pic" => "图片",
		"picTtl" => "图片及标题",
		"picArtcl" => "图文混排",
		"picPlyr" => "图片播放器",
		"spclTpc" => "交互主题",
		"html" => "自定义代码",
		"comment" => "专题评论",
	),
);

?>