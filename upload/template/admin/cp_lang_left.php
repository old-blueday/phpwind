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
		'name' => '核心设置',
		'items' => array(
			'basic',
			'regset'	=> array(
				'name'	=> '注册设置',
				'items'	=> array(
					'reg',
					'customfield',
					'invite',
					'propagateset',
				),
			),
			'credit',
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
			'messageset',
			'searcher',
			'email',
			'help',
			'wap',
			'safe',
			'seoset',
			'member',
			'pcache',
			'sethtm',
		),
	),
	'consumer' => array(
		'name'	=> '会员权限',
		'items'	=> array(
			'groups'	=> array(
				'name'	=> '会员组设置',
				'items'=> array(
					'level',
					'userstats',
					'upgrade',
					'editgroup',
					'uptime',
				),
			),
			'members'	=> array(
				'name'	=> '会员管理',
				'items'=>array(
					'setuser',
					'delmember',
					'banuser',
					'viewban',
					'unituser',
				),
			),
			'usercheck'		=> array(
				'name'			=> '会员审核',
				'items'			=> array(
					'checkreg',
					'checkemail',
				),
			),
			'customcredit',
		),
	),
	'contentforums'	=> array(
		'name'	=> '内容版块',
		'items'	=> array(
			'forums' => array(
				'name'	=> '版块管理',
				'items'=> array(
					'setforum',
					'singleright',
					'uniteforum',
					'forumsell',
					'creathtm',
				),
			),
			'contentmanage' => array(
				'name'	=> '内容管理',
				'items'=> array(
					'article',
					'app_photos',
					'app_diary',
					'app_groups',
					//'app_share',
					'app_weibo',
					'o_comments',
					'message',
					'report',
					'draftset',
					'recycle',
				),
			),
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
			'pwcode',
			'setform',
			'overprint',
			'postcache',
		),
	),
	'datacache'	=> array(
		'name'	=>'数据缓存',
		'items'	=> array(
			'aboutcache'	=> array(
				'name'	=> '缓存相关',
				'items'	=> array(
					'updatecache',
					'pwcache',
					'guestdir',
				),
			),
			'database'	=> array(
				'name'	=> '数据库',
				'items'	=> array(
					'bakup',
					'ptable',
				),
			),
			'log'	=> array(
				'name'	=> '管理日志',
				'items'	=> array(
					'adminlog',
					'forumlog',
					'creditlog',
					'adminrecord',
				),
			),
			'check'	=> array(
				'name'	=> '文件检查',
				'items'	=> array(
					'chmod',
					'safecheck',
				),
			),
			'ipban',
			'viewtoday',
			'datastate',
			'postindex',
		),
	),
	'applicationcenter'	=> array(
		'name'	=> '应用中心',
		'items'	=> array(
			'onlineapplication' => array(
				'name'	=> '在线应用',
				'items' => array(
					'appset',
					'onlineapp',
					'i9p',
					'blooming',
					'taolianjie',
					//'sinaweibo',
				),
			),
			'appslist',
			'topiccate',
			'app_stopic',
			'postcate',
			'activity',
			'hackcenter',
			'job',
			'onlinepay' => array(
				'name'	=> '网上支付设置',
				'items' => array(
					'userpay',
					'orderlist',
				),
			),
		),
	),
	'markoperation'=> array(
		'name'	=> '运营工具',
		'items'	=> array(
			'setadvert',
			'announcement',
			'sendmail',
			'sendmsg',
			'navmenu'		=> array(
				'name'	=> '导航菜单管理',
				'items'=> array(
					'navmain',
					'navside',
					//'navmode'
				),
			),
			'share',
			'plantodo',
			'present',
//			'setads',
			'ystats',
			'sitemap',
		),
	),

			'modemanage'=> array(
				'name'	=> '模式设置',
				'items'=> array(
					'modeset',
					//'modestamp',
					//'modepush',
				),
			),
			'o'		=> array(
				'name'	=> '个人中心',
				'items'=> array(
					'o_global',
					'o_skin',
				)
			),
			'bbs'	=> array(
				'name'	=> '论坛模式',
				'items'=> array(
					'detail'	=> array(
						'name' => '界面设置',
						'items' => array(
							'index',
							'thread',
							'read',
							'popinfo',
							'jsinvoke',
						)
					),
					'rebang',
					'setstyles',
				),
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

/*if (isset($db_modes['area'])) {
	$nav_left['area'] = array(
		'name'	=> '门户模式',
		'items'=> array(
			'areaCore' => array(
					'name' => '门户核心设置',
					'items' => array(
							'area_channel_manage',
							'area_module',
							//'area_selecttpl',
							'area_level_manage',
							'area_static_manage',

						)
				),
			//'areaContent' => array(
					//'name' => '内容管理',
					//'items' => array(
							'area_pushdata',
							//'area_columns_contents',
						//)
				//),
		),
	);
}*/
?>