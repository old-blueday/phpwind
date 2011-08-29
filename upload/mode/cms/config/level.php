<?php
!defined('P_W') && exit('Forbidden');
return array(
	'title' => '文章权限',
	'field' => array(
		'cms_post' => array(
			'name' => '发表文章',
			'type' => 'radio',
			'value'=> array(
				'1'=>'开启',
				'0'=>'关闭'
			),
			'descrip' => '开启后，普通会员用户组的用户可以投稿，管理权限用户组的用户可以发表文章'
		),

		'cms_replypost' => array(
			'name' => '评论文章',
			'type' => 'radio',
			'value'=> array(
				'1'=>'开启',
				'0'=>'关闭'
			),
			'descrip' => '开启后，该用户组可以对文章进行评论'
		),
	),
);
?>