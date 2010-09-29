<?php
!function_exists('readover') && exit('Forbidden');
return array(
	'subject'	=> array('title'=>'帖子排行'),
	'user'		=> array('title'=>'会员排行'),
	'image'		=> array('title'=>'论坛图片'),
	'forum'		=> array('title'=>'版块排行'),
	'tag'		=> array('title'=>'标签排行'),
	'group'		=> array('title'=>'群组排行'),
	'groupimage'=> array('title'=>'群组相片'),

	'grouparticle'	=> array('title'=>'群组话题'),
	'diary'		=> array('title'=>'日志排行'),
	'photo'		=> array('title'=>'相册排行'),
	//'owrite'	=> array('title'=>'记录'),
	'announce'  => array('title'=>'公告'),
	'sharelinks'=> array('title'=>'友情链接'),
);
?>