<?php
!function_exists('readover') && exit('Forbidden');

$lang['creditpop'] = array (

	/*
	* 论坛相关操作
	*/
	'main_buygroup'		=> '使用用户组身份购买功能，花费积分 ',
	'main_showsign'		=> '使用签名展示功能，花费积分 ',
	'main_forumsell'	=> '购买版块访问权限，花费积分 ',
	'main_changereduce'	=> '使用积分转换功能，转出积分 ',
	'main_changeadd'	=> '使用积分转换功能，转进积分 ',
	'main_virefrom'		=> '给其他用户转帐，转出积分 ',
	'main_vireto'		=> '收到用户转帐，转进积分 ',
	'main_olpay'		=> '使用在线充值功能，充值积分 ',

	/*
	* 注册
	*/
	'reg_register'		=> '注册成功，奖励积分 ',

	/*
	* 主题相关操作
	*/
	'topic_upload'		=> '上传附件，奖励积分 ',
	'topic_download'	=> '下载附件，消耗积分 ',
	'topic_Post'		=> '发表主题，奖励积分 ',
	'topic_Reply'		=> '回复帖子，奖励积分 ',
	'topic_Digest'		=> '被设置精华，奖励积分 ',
	'topic_Delete'		=> '主题被删除，扣除积分 ',
	'topic_Deleterp'	=> '回复被删除，扣除积分 ',
	'topic_Undigest'	=> '主题被取消精华，扣除积分 ',
	'topic_buy'			=> '购买帖子阅读权限，消耗积分 ',
	'topic_sell'		=> '出售帖子阅读权限成功，获得积分 ',
	'topic_attbuy'		=> '购买附件下载权限，消耗积分 ',
	'topic_attsell'		=> '出售附件下载权限，获得积分 ',
	'reply_reward'		=> '回帖奖励，奖励积分 ',


	/*
	* 评分相关操作
	*/
	'credit_showping'	=> '发表的帖子评分，奖励积分 ',
	'credit_delping'	=> '发表的帖子被取消评分，扣除积分 ',

	/*
	* 悬赏相关操作
	*/
	'reward_new'		=> '发布悬赏，消耗积分 ',
	'reward_modify'		=> '追加悬赏积分，消耗积分 ',
	'reward_answer'		=> '获得最佳答案奖励，奖励积分 ',
	'reward_active'		=> '获得热心助人奖励，奖励积分 ',
	'reward_return'		=> '悬赏帖已经结束，系统返回积分 ',

	/*
	* 插件相关操作
	*/
	'hack_banksave1'	=> '在银行活期存款，存入积分 ',
	'hack_banksave2'	=> '在银行定期存款，存入积分 ',
	'hack_bankdraw1'	=> '在银行活期取款，取出积分 ',
	'hack_bankdraw2'	=> '在银行定期取款，取出积分 ',
	'hack_cytransfer'	=> '朋友圈转让，消耗积分 ',
	'hack_cycreate'		=> '创建朋友圈，消耗积分 ',
	'hack_cyjoin'		=> '加入群组，消耗积分 ',
	'hack_cydonate'		=> '给群帐户充值积分，消耗积分 ',
	'hack_cyvire'		=> '获得来自群的积分转帐，转帐积分 ',
	'hack_cyalbum'		=> '创建了一个相册，消耗积分 ',
	'hack_dbpost'		=> '发表辩论主题，奖励积分 ',
	'hack_dbreply'		=> '发表辩论观点，奖励积分 ',
	'hack_dbdelt'		=> '发表的辩论主题被删除，扣除积分 ',
	'hack_toolubuy'		=> '向用户购买道具，消耗积分 ',
	'hack_toolbuy'		=> '购买道具，消耗积分 ',
	'hack_toolsell'		=> '向用户出售道具，获得积分 ',
	'hack_teampay'		=> '获得月份考核奖励，获得积分 ',
	'hack_invcodebuy'	=> '购买邀请码',
	/* phpwind.net */
	'hack_creditget'	=> '领取积分兑换功能赠送的积分，领取积分 ',
	'hack_creditaward'	=> '使用积分兑换礼品，兑换积分 ',

	//运气卡
	'hack_creditluckadd' =>'[b]{$L[username]}[/b] 使用运气卡获得积分：[b]{$L[cname]}[/b]，影响：{$L[affect]}。',
	'hack_creditluckdel' =>'[b]{$L[username]}[/b] 使用运气卡减少积分：[b]{$L[cname]}[/b]，影响：{$L[affect]}。',

	/*
	* 分享相关
	*/
	'share_Delete'		=> '删除分享，扣除积分',
	'share_Post'		=> '发表分享，增加积分',

	/*
	* 日志相关
	*/
	'diary_Delete'		=> '删除日志，扣除积分',
	'diary_Post'		=> '发表日志，增加积分',

	/*
	* 群组相关
	*/
	'groups_Uploadphoto'=> '上传照片，增加积分',
	'groups_Deletephoto'=> '删除照片，扣除积分',
	'groups_Createalbum'=> '创建相册，扣除积分',
	'groups_Postarticle'=> '发布文章，增加积分',
	'groups_Deletearticle'=> '删除文章，扣除积分',
	'groups_Joingroup'  => '加入群组，扣除积分',
	'groups_Creategroup'=> '创建群组，扣除积分',

	/*
	* 相册相关
	*/
	'photos_Deletephoto'=> '删除照片，扣除积分',
	'photos_Uploadphoto'=> '上传照片，增加积分',
	'photos_Createalbum'=> '创建相册，扣除积分',

	/*
	* 记录相关
	*/
	'write_Post'        => '发表记录，增加积分',
	'write_Delete'      => '删除记录，扣除积分',

	/*
	* 其他
	*/
	'other_present'		=> '获得操作节日送礼赠送的积分，赠送积分 ',
	'other_propaganda' 	=> '宣传奖励',

);
?>