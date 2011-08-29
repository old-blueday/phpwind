<?php
!function_exists('readover') && exit('Forbidden');
require GetLang('purview');
$nav_manager = array(
	'name'		=> '创始人',
	'items'		=> array(
		'manager',
		'rightset',
		'optimize',
		'diyoption',
		'advanced',
		'usercenter'=> array(
			'name'		=> '用户中心',
			'items'		=> array(
				'ucset',
				'ucapp',
				'uccredit',
				'ucnotify',
			),
		),
	),
);

$nav_left = array(
	'config' => array(
		'name' => '全局',
		'items' => array(
			'basic',
			'customnav',
			'regset'	=> array(
				'name'	=> '注册设置',
				'items'	=> array(
					'reg',
					'customfield',
					'invite',
					'propagateset'
				),
			),
			'authentication',
			'credit',
			'member',
			'editer',
			/*
			'creditset'	=> array(
				'name'	=> '积分设置',
				'items'	=> array(
					'credit',
					'creditdiy',
					'creditchange',
				)
			),
			*/
			'attftp'	=> array(
				'name'	=>'附件设置',
				'items'	=>array(
					'att',
					'attachment',
					'attachrenew',
					'attachstats',
				)
			),
			'safe',
			'seoset',
			'messageset',
			'searcher',
			'email',
			'userpay',
			'help',
			'wap',
			'sethtm',
			//'pcache',
		),
	),
	'consumer' => array(
		'name'	=> '用户',
		'items'	=> array(
			'level',
			'upgrade',
			'usermanage',
			'banuser',
			'bansignature',
			'usercheck',
			'userstats',
			//'customcredit',
			'uptime',
		),
	),
	'contentforums'	=> array(
		'name'	=> '内容',
		'items'	=> array(
			/*'contentmanage' => array(
				'name'	=> '内容管理',
				'items'=> array(*/
					'article',
					'photos_manage',
					'diary_manage',
					'groups_manage',
					//'app_share',
					'weibo_manage',
					'o_comments',
					'message',
					'reportmanage' => array(
						'name' => '举报管理',
						'items' => array(
							'reportcontent',
							'reportremind'
						)
					),
					'draftset',
					'recycle',
				/*),
			),*/
			'contentcheck'	=> array(
				'name'	=> '内容审核',
				'items'	=> array(
					'tpccheck',
					'setbwd',
					'urlcheck',
				),
			),
			'rulelist'	=> array(
				'name'=>'内容过滤设置',
				'items'=>array(
				)
			),
			'tagset',
			//'pwcode',
			//'setform',
			//'overprint',
		),
	),
	'datacache'	=> array(
		'name'	=> '数据',
		'items'	=> array(
			'bakup',
			'aboutcache',
			'record',
			'filecheck',
			'ipban',
			'viewtoday',
			'postindex',
			'datastate',
			'ystats',
			'creditlog',
			'tucool',
		),
	),
	'applicationcenter'	=> array(
		'name'	=> '应用',
		'items'	=> array(
		'onlineapplication' => array(
				'name'	=> '应用中心',
				'items' => array(
					'appset',
					'onlineapp',
					'i9p',
					'blooming',
					'taolianjie',
					'sinaweibo',
					'yunstatistics'
				),
			),
			'hackcenter',
			//'appslist',
			'photos_set',
			'diary_set',
			'groups_set',
			//'app_share',
			'hot',
			'weibo_set',
			'postcate',
			'activity',
			'topiccate',
			/*
			'basicapp' => array(
				'name' => '基础应用',
				'items' => array(
					
				),
			),
			*/
		),
	),
	'markoperation'=> array(
		'name'	=> '运营',
		'items'	=> array(
			'setadvert',
			'stopic',
			'job',
			'announcement',
			'sendmail',
			'sendmsg',
			'present',
			'share',
			'plantodo',
			'team'
//			'setads',
			//'sitemap',
		),
	),
	
	'modelist' => array(
		'name'	=> '模式设置',
		'items'=> array(
			'modeset',
			//'modestamp',
			//'modepush',
		),
	),
	'bbs' => array(
		'name'	=> '论坛模式',
		'items'=> array(
			'bbssetting',
			'setforum',
			'interfacesettings',
			/*
			'forums' => array(
				'name'	=> '版块管理',
				'items'=> array(
					'setforum',
					'uniteforum',
					'forumsell',
					'creathtm',
				),
			),
			*/
			'singleright',
			'setstyles',
			'rebang',
		),
	),
	'o' => array(
		'name'	=> '个人中心',
		'items'=> array(
			'o_global',
			'o_tags',
			'o_skin',
			'o_commend',
		)
	),
);

/*动态加载模式菜单*/
if (isset($db_modes)) {
	foreach ($db_modes as $key => $value) {
		if ($value['ifopen'] && file_exists(R_P.'mode/'.$key.'/config/cp_lang_left.php')) {
			include R_P.'mode/'.$key.'/config/cp_lang_left.php';
		}
	}
}

/*动态加载扩展菜单*/
$extentPath = R_P.'require/extents/menu';
if ($fp = opendir($extentPath)) {
	while (($filename = readdir($fp))) {
		if($filename=='..' || $filename=='.') continue;
		$leftFile = $extentPath.'/'.$filename.'/cp_lang_left.php';
		if (file_exists($leftFile)) include $leftFile;
	}
}

?>