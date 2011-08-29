<?php
!function_exists('readover') && exit('Forbidden');

$lang['creditlog'] = array (

	/*
	* 论坛相关操作
	*/
	'main_buygroup'		=> '[b]{$L[username]}[/b] 使用用户组身份购买功能，购买用户组($L[gptitle])身份{$L[days]}天。\n花费积分：[b]{$L[cname]}[/b]，影响：{$L[affect]}。',
	'main_showsign'		=> '[b]{$L[username]}[/b] 使用签名展示功能。\n花费积分：[b]{$L[cname]}[/b]，影响：{$L[affect]}。',
	'main_forumsell'	=> '[b]{$L[username]}[/b] 购买[b]{$L[fname]}[/b]版块访问权限{$L[days]}天，\n花费积分：[b]{$L[cname]}[/b]，影响：{$L[affect]}。',
	'main_changereduce'	=> '[b]{$L[username]}[/b] 使用 [b]{$L[cname]}[/b] -> [b]{$L[tocname]}[/b] 积分转换功能。\n转出积分：[b]{$L[cname]}[/b]，影响：{$L[affect]}。',
	'main_changeadd'	=> '[b]{$L[username]}[/b] 使用 [b]{$L[fromcname]}[/b] -> [b]{$L[cname]}[/b] 积分转换功能。\n转进积分：[b]{$L[cname]}[/b]，影响：{$L[affect]}。',
	'main_virefrom'		=> '[b]{$L[username]}[/b] 给用户 [b]{$L[toname]}[/b] 转帐{$L[cname]}。\n转出积分：[b]{$L[cname]}[/b]，影响：{$L[affect]}。',
	'main_vireto'		=> '[b]{$L[username]}[/b] 收到用户 [b]{$L[fromname]}[/b] 转帐的{$L[cname]}。\n转进积分：[b]{$L[cname]}[/b]，影响：{$L[affect]}。',
	'main_olpay'		=> '[b]{$L[username]}[/b] 使用在线充值功能，充值金额：{$L[number]}。\n充值积分：[b]{$L[cname]}[/b]，影响：{$L[affect]}。',

	/*
	* 注册
	*/
	'reg_register'		=> '[b]{$L[username]}[/b] 注册成功。\n奖励积分：[b]{$L[cname]}[/b]，影响：{$L[affect]}。',

	/*
	* 主题相关操作
	*/
	'topic_upload'		=> '[b]{$L[username]}[/b] 在版块 {$L[fname]} 上传附件。\n奖励积分：[b]{$L[cname]}[/b]，影响：{$L[affect]}。',
	'topic_download'	=> '[b]{$L[username]}[/b] 在版块 {$L[fname]} 下载附件。\n消耗积分：[b]{$L[cname]}[/b]，影响：{$L[affect]}。',
	'topic_Post'		=> '[b]{$L[username]}[/b] 在版块 {$L[fname]} 发表主题。\n奖励积分：[b]{$L[cname]}[/b]，影响：{$L[affect]}。',
	'topic_Reply'		=> '[b]{$L[username]}[/b] 在版块 {$L[fname]} 回复帖子。\n奖励积分：[b]{$L[cname]}[/b]，影响：{$L[affect]}。',
	'topic_Digest'		=> '[b]{$L[username]}[/b] 在版块 {$L[fname]} 的主题被 {$L[operator]} 设置精华。\n奖励积分：[b]{$L[cname]}[/b]，影响：{$L[affect]}。',
	'topic_Delete'		=> '[b]{$L[username]}[/b] 在版块 {$L[fname]} 的主题被 {$L[operator]} 删除。\n扣除积分：[b]{$L[cname]}[/b]，影响：{$L[affect]}。',
	'topic_Deleterp'	=> '[b]{$L[username]}[/b] 在版块 {$L[fname]} 的回复被 {$L[operator]} 删除。\n扣除积分：[b]{$L[cname]}[/b]，影响：{$L[affect]}。',
	'topic_Undigest'	=> '[b]{$L[username]}[/b] 在版块 {$L[fname]} 的主题被 {$L[operator]} 取消精华。\n扣除积分：[b]{$L[cname]}[/b]，影响：{$L[affect]}。',
	'topic_buy'			=> '[b]{$L[username]}[/b] 购买 [b][url={$GLOBALS[db_bbsurl]}/read.php?tid=$L[tid]]{$L[subject]}[/url][/b] 帖子阅读权限。\n消耗积分：[b]{$L[cname]}[/b]，影响：{$L[affect]}。',
	'topic_sell'		=> '[b]{$L[username]}[/b] 向 {$L[buyer]} 出售 [b][url={$GLOBALS[db_bbsurl]}/read.php?tid=$L[tid]]{$L[subject]}[/url][/b] 帖子阅读权限成功。\n获得积分：[b]{$L[cname]}[/b]，影响：{$L[affect]}。',
	'topic_attbuy'		=> '[b]{$L[username]}[/b] 购买附件下载权限。\n消耗积分：[b]{$L[cname]}[/b]，影响：{$L[affect]}。',
	'topic_attsell'		=> '[b]{$L[username]}[/b] 向 {$L[buyer]} 出售附件下载权限。\n获得积分：[b]{$L[cname]}[/b]，影响：{$L[affect]}。',


	/*
	* 评分相关操作
	*/
	'credit_showping'	=> '[b]{$L[username]}[/b] 发表的帖子：[url={$GLOBALS[db_bbsurl]}/read.php?tid={$L[tid]}]{$L[subject]}[/url] 被 [b]{$L[operator]}[/b] 评分。\n原因：{$L[reason]}\n奖励积分：[b]{$L[cname]}[/b]，影响：{$L[affect]}。',
	'credit_delping'	=> '[b]{$L[username]}[/b] 发表的帖子：[url={$GLOBALS[db_bbsurl]}/read.php?tid={$L[tid]}]{$L[subject]}[/url] 被 [b]{$L[operator]}[/b] 取消评分。\n原因：{$L[reason]}\n扣除积分：[b]{$L[cname]}[/b]，影响：{$L[affect]}。',

	/*
	* 悬赏相关操作
	*/
	'reward_new'		=> '[b]{$L[username]}[/b] 在版块 {$L[fname]} 发布悬赏。\n最佳答案：{$L[cbval]} {$L[cbtype]}，热心助人：{$L[caval]} {$L[catype]}\n消耗积分：[b]{$L[cname]}[/b]，影响：{$L[affect]}。',
	'reward_modify'		=> '[b]{$L[username]}[/b] 在版块 {$L[fname]} 追加悬赏积分。\n最佳答案：+{$L[cbval]} {$L[cbtype]}，热心助人：+{$L[caval]} {$L[catype]}\n消耗积分：[b]{$L[cname]}[/b]，影响：{$L[affect]}。',
	'reward_answer'		=> '[b]{$L[username]}[/b] 在版块 {$L[fname]} 获得最佳答案奖励。\n奖励积分：[b]{$L[cname]}[/b]，影响：{$L[affect]}。',
	'reward_active'		=> '[b]{$L[username]}[/b] 在版块 {$L[fname]} 获得热心助人奖励。\n奖励积分：[b]{$L[cname]}[/b]，影响：{$L[affect]}。',
	'reward_return'		=> '[b]{$L[username]}[/b] 在版块 {$L[fname]} 的悬赏帖已经结束。\n系统返回积分：[b]{$L[cname]}[/b]，影响：{$L[affect]}。',

	/*
	* 插件相关操作
	*/
	'hack_banksave1'	=> '[b]{$L[username]}[/b] 在银行活期存款。\n存入积分：[b]{$L[cname]}[/b]，影响：{$L[affect]}。',
	'hack_banksave2'	=> '[b]{$L[username]}[/b] 在银行定期存款。\n存入积分：[b]{$L[cname]}[/b]，影响：{$L[affect]}。',
	'hack_bankdraw1'	=> '[b]{$L[username]}[/b] 在银行活期取款。\n取出积分：[b]{$L[cname]}[/b]，影响：{$L[affect]}。',
	'hack_bankdraw2'	=> '[b]{$L[username]}[/b] 在银行定期取款。\n取出积分：[b]{$L[cname]}[/b]，影响：{$L[affect]}。',
	'hack_cytransfer'	=> '[b]{$L[username]}[/b] 将朋友圈 [b]{$L[cnname]}[/b] 转让给 {$L[toname]}。\n消耗积分：[b]{$L[cname]}[/b]，影响：{$L[affect]}。',
	'hack_cycreate'		=> '[b]{$L[username]}[/b] 创建朋友圈 [b]{$L[cnname]}[/b]。\n消耗积分：[b]{$L[cname]}[/b]，影响：{$L[affect]}。',
	'hack_cyjoin'		=> '[b]{$L[username]}[/b] 加入群组 [b]{$L[cnname]}[/b]。\n消耗积分：[b]{$L[cname]}[/b]，影响：{$L[affect]}。',
	'hack_cydonate'		=> '[b]{$L[username]}[/b] 给群 [b]{$L[cnname]}[/b] 帐户充值积分。\n消耗积分：[b]{$L[cname]}[/b]，影响：{$L[affect]}。',
	'hack_cyvire'		=> '[b]{$L[username]}[/b] 获得来自群 [b]{$L[cnname]}[/b] 的积分转帐\n操作者：{$L[operator]}。\n转帐积分：[b]{$L[cname]}[/b]，影响：{$L[affect]}。',
	'hack_cyalbum'		=> '[b]{$L[username]}[/b] 创建了一个相册 {[b]{$L[aname]}[/b]}。\n消耗积分：[b]{$L[cname]}[/b]，影响：{$L[affect]}。',
	'hack_dbpost'		=> '[b]{$L[username]}[/b] 发表辩论主题。\n奖励积分：[b]{$L[cname]}[/b]，影响：{$L[affect]}。',
	'hack_dbreply'		=> '[b]{$L[username]}[/b] 发表辩论观点。\n奖励积分：[b]{$L[cname]}[/b]，影响：{$L[affect]}。',
	'hack_dbdelt'		=> '[b]{$L[username]}[/b] 发表的辩论主题被 {$L[operator]} 删除。\n扣除积分：[b]{$L[cname]}[/b]，影响：{$L[affect]}。',
	'hack_toolubuy'		=> '[b]{$L[username]}[/b] 向用户 [b]{$L[seller]}[/b] 购买{$L[nums]}个 [b]{$L[toolname]}[/b] 道具。\n消耗积分：[b]{$L[cname]}[/b]，影响：{$L[affect]}。',
	'hack_toolbuy'		=> '[b]{$L[username]}[/b] 购买{$L[nums]}个 [b]{$L[toolname]}[/b] 道具。\n消耗积分：[b]{$L[cname]}[/b]，影响：{$L[affect]}。',
	'hack_toolsell'		=> '[b]{$L[username]}[/b] 向用户 [b]{$L[buyer]}[/b] 出售道具 [b]{$L[toolname]}[/b]。\n获得积分：[b]{$L[cname]}[/b]，影响：{$L[affect]}。',
	'hack_teampay'		=> '[b]{$L[username]}[/b] 获得 {$L[datef]} 月份考核奖励。\n获得积分：[b]{$L[cname]}[/b]，影响：{$L[affect]}。',
	'hack_invcodebuy'	=> '[b]{$L[username]}[/b] 购买 {$L[invnum]} 个邀请码。\n消耗积分：[b]{$L[cname]}[/b]，影响：{$L[affect]}。',
	/* phpwind.net */
	'hack_creditget'	=> '[b]{$L[username]}[/b] 领取积分兑换功能赠送的积分。\n领取积分：[b]{$L[cname]}[/b]，影响：{$L[affect]}。',
	'hack_creditaward'	=> '[b]{$L[username]}[/b] 使用积分兑换礼品 [b]{$L[subject]}[/b] {$L[num]}件。\n兑换积分：[b]{$L[cname]}[/b]，影响：{$L[affect]}。',


	/*
	* 分享相关
	*/
	'share_Delete'		=> '[b]{$L[username]}[/b] 删除分享扣除积分。\n扣除积分：[b]{$L[cname]}[/b]，影响：{$L[affect]}。',
	'share_Post'		=> '[b]{$L[username]}[/b] 发表分享增加积分。\n增加积分：[b]{$L[cname]}[/b]，影响：{$L[affect]}。',

	/*
	* 日志相关
	*/
	'diary_Delete'		=> '[b]{$L[username]}[/b] 删除日志扣除积分。\n扣除积分：[b]{$L[cname]}[/b]，影响：{$L[affect]}。',
	'diary_Post'		=> '[b]{$L[username]}[/b] 发表日志增加积分。\n增加积分：[b]{$L[cname]}[/b]，影响：{$L[affect]}。',

	/*
	* 群组相关
	*/
	'groups_Uploadphoto'=> '[b]{$L[username]}[/b] 上传照片增加积分。\n增加积分：[b]{$L[cname]}[/b]，影响：{$L[affect]}。',
	'groups_Deletephoto'=> '[b]{$L[username]}[/b] 删除照片扣除积分。\n扣除积分：[b]{$L[cname]}[/b]，影响：{$L[affect]}。',
	'groups_Createalbum'=> '[b]{$L[username]}[/b] 创建相册扣除积分。\n扣除积分：[b]{$L[cname]}[/b]，影响：{$L[affect]}。',
	'groups_Postarticle'=> '[b]{$L[username]}[/b] 发布文章增加积分。\n增加积分：[b]{$L[cname]}[/b]，影响：{$L[affect]}。',
	'groups_Deletearticle'=> '[b]{$L[username]}[/b] 删除文章扣除积分。\n扣除积分：[b]{$L[cname]}[/b]，影响：{$L[affect]}。',
	'groups_Joingroup'  => '[b]{$L[username]}[/b] 加入群组扣除积分。\n扣除积分：[b]{$L[cname]}[/b]，影响：{$L[affect]}。',
	'groups_Creategroup'=> '[b]{$L[username]}[/b] 创建群组扣除积分。\n扣除积分：[b]{$L[cname]}[/b]，影响：{$L[affect]}。',

	/*
	* 相册相关
	*/
	'photos_Deletephoto'=> '[b]{$L[username]}[/b] 删除照片扣除积分。\n扣除积分：[b]{$L[cname]}[/b]，影响：{$L[affect]}。',
	'photos_Uploadphoto'=> '[b]{$L[username]}[/b] 上传照片增加积分。\n增加积分：[b]{$L[cname]}[/b]，影响：{$L[affect]}。',
	'photos_Createalbum'=> '[b]{$L[username]}[/b] 创建相册扣除积分。\n扣除积分：[b]{$L[cname]}[/b]，影响：{$L[affect]}。',

	/*
	* 新鲜事相关
	*/
	'weibo_Post'=> '[b]{$L[username]}[/b] 发表新鲜事增加积分。\n增加积分：[b]{$L[cname]}[/b]，影响：{$L[affect]}。',
	'weibo_Delete'=> '[b]{$L[username]}[/b] 删除新鲜事扣除积分。\n扣除积分：[b]{$L[cname]}[/b]，影响：{$L[affect]}。',


	/*
	* 其他
	*/
	'other_finishjob'   => '[b]{$L[username]}[/b] 完成 [b]{$GLOBALS[job]}[/b] 任务,获得系统 赠送的积分。\n奖励积分：[b]{$L[cname]}[/b]，影响：{$L[affect]}。',
	'other_finishpunch'   => '[b]{$L[username]}[/b] 完成 [b]每日打卡[/b] 任务,获得系统 赠送的积分。\n奖励积分：[b]{$L[cname]}[/b]，影响：{$L[affect]}。',
	'other_present'		=> '[b]{$L[username]}[/b] 获得由 [b]{$L[admin]}[/b] 操作节日送礼赠送的积分。\n赠送积分：[b]{$L[cname]}[/b]，影响：{$L[affect]}。',
	'other_propaganda'  =>'[b]{$L[username]}[/b] 由于发送宣传链接获得赠送积分。\n赠送积分：[b]{$L[cname]}[/b]，影响：{$L[affect]}。',


);
?>