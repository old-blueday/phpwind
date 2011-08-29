<?php
!function_exists('readover') && exit('Forbidden');

$lang['_othermsg'] = '\n\n[b]帖子：[/b][url=$GLOBALS[db_bbsurl]/read.php?tid={$L[tid]}]{$L[subject]}[/url]\n'
					. '[b]发表日期：[/b]{$L[postdate]}\n'
					. '[b]所在版块：[/b][url=$GLOBALS[db_bbsurl]/thread.php?fid={$L[fid]}]{$L[forum]}[/url]\n'
					. '[b]操作时间：[/b]{$L[admindate]}\n'
					. '[b]操作理由：[/b]{$L[reason]}\n\n'
					. '论坛管理操作通知短消息，对本次管理操作有任何异议，请与我取得联系。';
$lang['_othermsg1'] = '\n\n[b]帖子：[/b][url=$GLOBALS[db_bbsurl]/job.php?action=topost&tid={$L[tid]}&pid={$L[pid]}]{$L[subject]}[/url]\n'
					. '[b]发表日期：[/b]{$L[postdate]}\n'
					. '[b]所在版块：[/b][url=$GLOBALS[db_bbsurl]/thread.php?fid={$L[fid]}]{$L[forum]}[/url]\n'
					. '[b]操作时间：[/b]{$L[admindate]}\n'
					. '[b]操作理由：[/b]{$L[reason]}\n\n'
					. '论坛管理操作通知短消息，对本次管理操作有任何异议，请与我取得联系。';
$lang['_othermsg_colony'] = '\n\n[b]帖子：[/b][url=$GLOBALS[db_bbsurl]/job.php?action=topost&tid={$L[tid]}&pid={$L[pid]}]{$L[subject]}[/url]\n'
							. '[b]发表日期：[/b]{$L[postdate]}\n'
							. '[b]所在版块：[/b][url=$GLOBALS[db_bbsurl]/thread.php?fid={$L[fid]}]{$L[forum]}[/url]\n'
							. '[b]操作时间：[/b]{$L[admindate]}\n'
							. '[b]操作理由：[/b]{$L[reason]}\n\n'
							. '论坛管理操作通知短消息，对本次管理操作有任何异议，请与我取得联系。';

$lang['writemsg'] = array (
	'olpay_title'			=> '积分充值支付成功.',
	'olpay_content'			=> '交易币充值支付成功，您需要登录支付宝使用“[color=red]确认收货[/color]”功能完成本次交易。\n'
								. '确认收货后系统会自动对您的交易币帐户进行充值。',
	'olpay_content_2'		=> '积分充值成功，本次充值金额：{$L[number]}RMB，总共获得{$L[cname]}个数：{$L[currency]}。\n谢谢使用！',

	'toolbuy_title'			=> '道具购买支付成功!',
	'toolbuy_content'		=> '道具购买成功，本次支付金额：{$L[fee]}RMB，共购得[b]{$L[toolname]}[/b]道具 [b]{$L[number]}[/b] 个!',

	'forumbuy_title'		=> '版块访问权限购买支付成功!',
	'forumbuy_content'		=> '版块访问权限购买成功，本次支付金额：{$L[fee]}RMB，共购得版块 [b]{$L[fname]}[/b] 访问期限 [b]{$L[number]}[/b] 天!',

	'groupbuy_title'		=> '特殊组购买支付成功!',
	'groupbuy_content'		=> '特殊组身份购买成功，本次支付金额：{$L[fee]}RMB，共购得用户组 [b]{$L[gname]}[/b] 身份 [b]{$L[number]}[/b] 天!',

	'virement_title'		=> '银行汇款通知!!',
	'virement_content'		=> '用户{$L[windid]}通过银行给你转帐{$L[to_money]}元钱，'
								. '系统自动把以前的利息加到你的存款中，你的利息将从现在重新开始计算\n转帐附言：{$L[memo]}',
	'metal_add'				=> '授予勋章通知',
	'metal_post_title'	    => '您的勋章申请已提交',
	'metal_post_content'    => '您的勋章申请已提交，正在审核中\n\n勋章名称：{$L[mname]}\n操作：{$L[windid]}\n理由：{$L[reason]}',
	'metal_add_content'		=> '您被授予勋章\n\n勋章名称：{$L[mname]}\n操作：{$L[windid]}\n理由：{$L[reason]}',
	'metal_cancel'			=> '收回勋章通知',
	'metal_cancel_content'	=> '您的勋章被收回\n\n勋章名称：{$L[mname]}\n操作：{$L[windid]}\n理由：{$L[reason]}',
	'metal_cancel_text'		=> '您的勋章被收回\n\n勋章名称：{$L[medalname]}\n操作：SYSTEM\n理由：过期',
	'metal_refuse'			=> '勋章申请未通过',
	'metal_refuse_content'	=> '您的勋章申请未通过审核\n\n勋章名称：{$L[mname]}\n操作：{$L[windid]}\n理由：{$L[reason]}',
	'medal_apply_title'		=> '勋章申请通知!',
	'medal_apply_content'	=> '用户 {$L[username]} 于 {$L[time]} 申请了 {$L[medal]} 勋章，请求您审核。',
	'vire_title'			=> '交易币转帐通知',
	'vire_content'			=> '用户 [b]{$L[windid]}[/b] 使用积分转帐功能，给您转帐 {$L[paynum]} {$L[cname]}，请注意查收。',
	'cyvire_title'			=> '{$L[cn_name]}{$L[moneyname]}转帐通知',
	'cyvire_content'		=> '{$L[cn_name]}([url=$GLOBALS[db_bbsurl]/hack.php?'
								. 'H_name=colony&cyid={$L[cyid]}&job=view&id={$L[cyid]}]'
								. '{$L[all_cname]}[/url])管理员使用{$L[moneyname]}管理功能，'
								. '给你转帐 {$L[currency]} {$L[moneyname]}，请注意查收。',
	'donate_title'			=> '{$L[cn_name]}捐献通知消息',
	'donate_content'		=> '用户{$L[windid]}通过捐献功能，给{$L[cn_name]}({$L[allcname]})'
								. '捐献{$L[moneyname]}：{$L[sendmoney]}。',

	'top_title'				=> '您的帖子被置顶.',
	'untop_title'			=> '您的帖子被解除置顶.',
	'top_content'			=> '您发表的帖子被 [b]{$L[manager]}[/b] 执行 [b]置顶[/b] 操作'.$lang['_othermsg'],
	'untop_content'			=> '您的帖子被 [b]{$L[manager]}[/b] 执行 [b]解除置顶[/b] 操作'.$lang['_othermsg'],
	'digest_title'			=> '您的帖子被设为精华帖',
	'digest_content'		=> '您发表的帖子被 [b]{$L[manager]}[/b] 执行 [b]精华[/b] 操作\n\n'
								. '对您的影响：{$L[affect]}'.$lang['_othermsg'],
	'undigest_title'		=> '您的帖子被取消精华',
	'undigest_content'		=> '您发表的帖子被 [b]{$L[manager]}[/b] 执行 [b]取消精华[/b] 操作\n\n'
								. '对您的影响：{$L[affect]}'.$lang['_othermsg'],
	'lock_title'			=> '您的帖子被锁定',
	'lock_content'			=> '您发表的帖子被 [b]{$L[manager]}[/b] 执行 [b]锁定[/b] 操作'.$lang['_othermsg'],
	'lock_title_2'			=> '您的帖子被关闭',
	'lock_content_2'		=> '您发表的帖子被 [b]{$L[manager]}[/b] 执行 [b]关闭[/b] 操作'.$lang['_othermsg'],
	'unlock_title'			=> '您的帖子被解除锁定',
	'unlock_content'		=> '您发表的帖子被 [b]{$L[manager]}[/b] 执行 [b]解除锁定[/b] 操作'.$lang['_othermsg'],
	'push_title'			=> '您的帖子被提前',
	'push_content'          => '您发表的帖子被 [b]{$L[manager]}[/b] [b]提前了 {$L[timelimit]} 小时[/b]'.$lang['_othermsg'],
	'recommend_title'		=> '您的帖子被推荐',
	'recommend_content'		=> '您发表的帖子被 [b]{$L[manager]}[/b] 执行 [b]推荐[/b] 操作'.$lang['_othermsg'],
	'pushto_title'			=> '您的帖子被推送',
	'pushto_content'		=> '您发表的帖子被 [b]{$L[manager]}[/b] 执行 [b]推送[/b] 操作'.$lang['_othermsg'],
	'unhighlight_title'		=> '您的帖子标题被取消加亮显示',
	'unhighlight_content'	=> '您发表的帖子被 [b]{$L[manager]}[/b] 执行 [b]标题取消加亮[/b] 操作'.$lang['_othermsg'],
	'highlight_title'		=> '您的帖子标题被加亮显示',
	'highlight_content'		=> '您发表的帖子被 [b]{$L[manager]}[/b] 执行 [b]标题加亮[/b] 操作'.$lang['_othermsg'],
	'del_title'				=> '您的帖子被删除',
	'del_content'			=> '您发表的帖子被 [b]{$L[manager]}[/b] 执行 [b]删除[/b] 操作\n\n'
								. '对您的影响：{$L[affect]}'.$lang['_othermsg'],
	'move_title'			=> '您的帖子被移动',
	'move_content'			=> '您发表的帖子被 [b]{$L[manager]}[/b] 执行 [b]移动[/b] 操作\n\n'
								. '[b]目的版块：[/b][url=$GLOBALS[db_bbsurl]/thread.php?fid={$L[tofid]}]{$L[toforum]}[/url]'.$lang['_othermsg'],
	'copy_title'			=> '您的帖子被复制到新版块',
	'copy_content'			=> '您发表的帖子被 [b]{$L[manager]}[/b] 执行 [b]复制[/b] 操作\n\n'
								. '[b]目的版块：[/b][url=$GLOBALS[db_bbsurl]/thread.php?fid={$L[tofid]}]{$L[toforum]}[/url]'.$lang['_othermsg'],
	'ping_title'			=> '"{$L[sender]}"给"{$L[receiver]}"的帖子评分',
	'ping_content'			=> '"{$L[sender]}"给[b]"{$L[receiver]}"[/b]的帖子  执行 [b]评分[/b] 操作\n\n'
								. '影响：{$L[affect]}'.$lang['_othermsg1'],
	'delping_title'			=> '"{$L[receiver]}"的帖子被"{$L[sender]}"取消评分',
	'delping_content'		=> '"{$L[receiver]}"的帖子被[b]"{$L[sender]}"[/b] 执行 [b]取消评分[/b] 操作\n\n'
								. '影响：{$L[affect]}'.$lang['_othermsg1'],
	'deltpc_title'			=> '您的帖子被删除',
	'deltpc_content'		=> '您发表的帖子被 [b]{$L[manager]}[/b] 执行 [b]删除[/b] 操作\n\n'
								. '影响：{$L[affect]}'.$lang['_othermsg'],
	'delrp_title'			=> '您的回复被删除',
	'delrp_content'			=> '您发表的回复被 [b]{$L[manager]}[/b] 执行 [b]删除[/b] 操作\n\n'
								. '对您的影响：{$L[affect]}'.$lang['_othermsg'],
	'reward_title_1'		=> '您的回复被设为最佳答案!',
	'reward_content_1'		=> '您的回复被设为最佳答案!\n\n对您的影响：{$L[affect]}'.$lang['_othermsg'],
	'reward_title_2'		=> '您的回复获得热心助人奖励!',
	'reward_content_2'		=> '您的回复获得热心助人奖励!\n\n对您的影响：{$L[affect]}'.$lang['_othermsg'],
	'endreward_title_1'		=> '您的悬赏被取消!',
	'endreward_title_2'		=> '您的悬赏被强制结案!',
	'endreward_content_1'	=> '由于没有合适的答案，您的悬赏被管理员 [b]{$L[manager]}[/b] 执行 [b]取消[/b] 操作!\n\n'
								. '系统返回您:{$L[affect]}'.$lang['_othermsg'],
	'endreward_content_2'	=> '由于您长时间未对悬赏帖进行结案,且已经有合适的答案，所以被[b]{$L[manager]}[/b] 执行 [b]强制结案[/b] 操作\n\n'
								. '系统不返回所有积分'.$lang['_othermsg'],
	'rewardmsg_title'		=> '悬赏帖(编号:{$L[tid]})请求处理!',
	'rewardmsg_content'		=> '尊敬的版主:\n\t\t您好!\n\t\t由于该次悬赏帖没有产生合适答案，现请求您结案,'
								. '希望您仔细查证后，作出公平处理!'.$lang['_othermsg'],
	'rewardmsg_notice_title'	=> '悬赏帖到期通知!',
	'rewardmsg_notice_content'	=> '您的悬赏帖已经到期，系统提醒您尽快作出处理,否则版主有权强行结案!\n\n'
								. '[b]帖子：[/b][url=$GLOBALS[db_bbsurl]/read.php?tid={$L[tid]}]{$L[subject]}[/url]\n'
								. '[b]发表日期：[/b]{$L[postdate]}\n'
								. '[b]所在版块：[/b][url=$GLOBALS[db_bbsurl]/thread.php?fid={$L[fid]}]{$L[name]}[/url]\n\n'
								. '论坛管理操作通知短消息，对本次管理操作有任何异议，请与我取得联系。',
	'shield_title_2'		=> '您的帖子主题被删除',
	'shield_content_2'		=> '您发表的帖子主题被 [b]{$L[manager]}[/b] [b]删除[/b]'.$lang['_othermsg'],
	'shield_title_1'		=> '您的帖子被屏蔽',
	'shield_content_1'		=> '您发表的帖子被 [b]{$L[manager]}[/b] [b]屏蔽[/b]'.$lang['_othermsg'],
	'shield_title_0'		=> '您的帖子被取消屏蔽',
	'shield_content_0'		=> '您发表的帖子被 [b]{$L[manager]}[/b] 执行 [b]取消屏蔽[/b] 操作'.$lang['_othermsg'],
	'bansignature_title_0'	=> '您的论坛签名被解除屏蔽',
	'bansignature_title_1'	=> '您的论坛签名被禁止',
	'bansignature_content_1'=> '您的论坛签名被 [b]{$L[manager]}[/b] 于{$L[admindate]}执行 [b]禁止[/b] 操作\n'.
							   '[b]操作理由:[/b]{$L[reason]}',
	'bansignature_content_0'=> '您的论坛签名被 [b]{$L[manager]}[/b] 于{$L[admindate]}执行 [b]解除屏蔽[/b] 操作',
	'remind_title'			=> '您的帖子被提醒管理',
	'remind_content'		=> '您发表的帖子被 [b]{$L[manager]}[/b] 执行 [b]提醒管理[/b] 操作'.$lang['_othermsg'],
	'unite_title'			=> '您的帖子被{$L[manager]}合并',
	'unite_content'			=> '您发表的帖子被 [b]{$L[manager]}[/b] 执行 [b]合并[/b] 操作'.$lang['_othermsg'],
	'unite_owner_content'	=> '您发表的帖子被 [b]{$L[manager]}[/b] 执行 [b]合并[/b] 操作',
	'leaveword_title'		=> '您的帖子被留言',
	'leaveword_content'		=> '[b]{$L[author]}[/b] 在您发表的帖子上留言了。'.$lang['_othermsg'],
	'birth_title'			=> '$L[userName],祝您生日快乐',
	'birth_content'			=> '[img]$GLOBALS[db_bbsurl]/u/images/birthday.gif[/img]\r\n'
								. '祈愿您的生日，为您带来一个最瑰丽最金碧辉煌的一生。\r\n'
								. '只希望你的每一天都快乐、健康、美丽，生命需要奋斗、创造、把握！\r\n'
								. '生日的烛光中摇曳一季繁花，每一支都是我的祝愿：生日快乐！\r\n\r\n'
								. '--------------------------------------- {$L[fromUsername]} 送上最真挚的祝福！\r\n\r\n',
	'down_title'			=> '您的帖子被执行压帖操作',
	'down_content'			=> '您发表的帖子被 [b]{$L[manager]}[/b] [b]压后了 {$L[timelimit]} 小时[/b]'.$lang['_othermsg'],
	'change_type_title'		=> '您的帖子被修改了主题分类',
	'change_type_content'	=> '您发表的帖子主题被 [b]{$L[manager]}[/b] [b]修改了主题分类为：{$L[type]}[/b]'.$lang['_othermsg'],
	'check_title'			=> '您的帖子已通过审核',
	'check_content'			=> '您发表的帖子主题被 [b]{$L[manager]}[/b] [b]通过审核[/b]'.$lang['_othermsg'],

	'post_pass_title'		=> '您发表的回复已经通过审核!',
	'post_pass_content'		=> '您的回复，已经通过审核。[url=$GLOBALS[db_bbsurl]/read.php?tid={$L[tid]}]点击查看[/url]\n\n',
	'subject_reply_title'	=> '“{$L[windid]}”回复了“{$L[author]}”发表的主题[{$L[title]}]',
	'subject_replytouser_title' => '“{$L[windid]}”在主题[{$L[title]}]中回复了你',
	'subject_reply_content' => '{$L[windid]}说：$L[content]\n\n[url=$GLOBALS[db_bbsurl]/job.php?action=topost&tid={$L[tid]}&pid={$L[pid]}]查看回复[/url] [url=$GLOBALS[db_bbsurl]/read.php?tid={$L[tid]}]查看主题[/url]\n\n',
	'advert_buy_title'		=> '您的广告位申请已通过审核',
	'advert_buy_content'	=> '本广告位的价格为：{$L[creditnum]} {$L[creditypename]} 每天\n\n'
								. '你购买的天数为：{$L[days]}',
	'advert_apply_title'	=> '广告出租位申请通知!',
	'advert_apply_content'	=> '用户 {$L[username]} 于 {$L[time]} 申请了 {$L[days]} 天的广告展示，请求您审核。',

	'friend_add_title_1'	=> '好友系统通知：{$L[username]}将您列入他（她）的好友名单',
	'friend_add_content_1'	=> '[url=$GLOBALS[db_bbsurl]/{$GLOBALS[db_userurl]}{$L[uid]}]{$L[username]}[/url] 将您列入他（她）的好友名单。',
	'friend_add_title_2'	=> '好友系统通知：{$L[username]} 请求加您为好友',
	'friend_add_content_2'	=> '[url={$GLOBALS[db_bbsurl]}/{$GLOBALS[db_userurl]}{$L[uid]}]{$L[username]}[/url] 请求加您为好友。是否同意？\n\n$L[msg]\n\n',
	'friend_delete_title'	=> '好友系统通知：{$L[username]} 解除与您的好友关系',
	'friend_delete_content'	=> '[url={$GLOBALS[db_bbsurl]}/{$GLOBALS[db_userurl]}{$L[uid]}]{$L[username]}[/url] 解除与您的好友关系。',
	'friend_accept_title'	=> '好友系统通知：{$L[username]} 通过了您的好友请求',
	'friend_accept_content' => '[url={$GLOBALS[db_bbsurl]}/{$GLOBALS[db_userurl]}{$L[uid]}]{$L[username]}[/url] 通过了您的好友请求。',
	'friend_acceptadd_title'=> '好友系统通知：{$L[username]} 通过了您的好友请求,并加您为好友',
	'friend_acceptadd_content'	=> '[url={$GLOBALS[db_bbsurl]}/{$GLOBALS[db_userurl]}{$L[uid]}]{$L[username]}[/url] 通过了您的好友请求,并加您为好友。',

	'friend_refuse_title'	=> '好友系统通知：{$L[username]} 拒绝了您的好友请求',
	'friend_refuse_content'	=> '[url={$GLOBALS[db_bbsurl]}/{$GLOBALS[db_userurl]}{$L[uid]}]{$L[username]}[/url]'
								. '拒绝了您的好友请求\r\n\r\n[b]拒绝理由：[/b]{$L[msg]}\r\n\r\n',
	'friend_agree_title'	=> '好友系统通知：{$L[username]} 通过了您的好友请求',
	'friend_agree_content'	=> '[url={$GLOBALS[db_bbsurl]}/{$GLOBALS[db_userurl]}{$L[uid]}]{$L[username]}[/url]'
								. '通过了您的好友请求\r\n\r\n',
	'user_update_title'		=> '系统通知',
	'user_update_content'	=> '亲爱的{$L[username]}，我们非常高兴的告诉你，你刚刚升到了{$L[membername]}级别。你离下一级{$L[upmembername]}级别还有{$L[userneed]}分，要继续努力哦，<a href="profile.php?action=permission" target="_blank">查看会员组权限</a>',
	'banuser_title'			=> '系统禁言通知',
	'banuser_content_1'		=> '你已经被管理员{$L[manager]} 禁言,禁言时间为{$L[limit]} 天\n\n{$L[reason]}',
	'banuser_content_2'		=> '你已经被管理员{$L[manager]} 禁言\n\n{$L[reason]}',
	'banuser_content_3'		=> '你已经被管理员{$L[manager]} 禁言\n\n{$L[reason]}',

	'banuser_free_title'	=> '解除禁言通知',
	'banuser_free_content'	=> '你已经被管理员{$L[manager]} 解除禁言\n\n{$L[reason]}',

	'onlinepay_logistics'	=> '物流公司：{$L[logistics]}\r\n物流单号：{$L[orderid]}',
	'goods_pay_title'		=> '买家付款通知!',
	'goods_pay_content'		=> '买家 [b]{$L[buyer]}[/b] 于 {$L[buydate]} 下单的商品 [b][url={$GLOBALS[db_bbsurl]}/read.php?tid={$L[tid]}]{$L[goodsname]}[/url][/b] 已经付款，付款信息如下：\r\n\r\n{$L[descrip]}\r\n\r\n请确认后，尽快发货!',
	'goods_send_title'		=> '卖家发货通知!',
	'goods_send_content'	=> '您于 {$L[buydate]} 购买的商品[b][url={$GLOBALS[db_bbsurl]}/read.php?tid={$L[tid]}]{$L[goodsname]}[/url][/b]，卖家 [b]{$L[seller]}[/b] 已经发货，发货信息为：\r\n\r\n{$L[descrip]}',

	'sharelink_apply_title'		=> '自助友情链接申请通知!',
	'sharelink_apply_content'	=> '用户 {$L[username]} 于 {$L[time]} 申请了友情链接展示，请求您审核。',

	'sharelink_pass_title'		=> '自助友情链接申请通过通知!',
	'sharelink_pass_content'	=> '您提交的自助友情链接申请已通过审核。',

	'o_addadmin_title'		=> '群组通知[{$L[cname]}]：您被升为管理员了!',
	'o_addadmin_content'	=> '您加入的群组[url={$L[curl]}]{$L[cname]}[/url]，已经将您升为管理员了，赶快去看看!',
	'o_deladmin_title'		=> '群组通知[{$L[cname]}]：您被取消管理员身份了!',
	'o_deladmin_content'	=> '您加入的群组[url={$L[curl]}]{$L[cname]}[/url]，已经将您取消管理员身份了，赶快去看看!',
	'o_check_title'			=> '群组通知[{$L[cname]}]：您已正式加入群组了!',
	'o_check_content'		=> '您日前申请加入的群组[url={$L[curl]}]{$L[cname]}[/url]，已经正式批准您加入了，[url={$L[curl]}]赶快去看看[/url]!',

	'o_friend_success_title'	=> '好友系统通知：您和{$L[username]}成为了好友',
	'o_friend_success_cotent'	=> '通过邀请好友：您和[url={$L[myurl]}]{$L[username]}[/url]成为了好友',

	'o_board_success_title'		=> '"{$L[sender]}"给"{$L[receiver]}"留了言',
	'o_board_success_cotent'	=> '{$L[content]} \n[url={$GLOBALS[db_bbsurl]}/u.php?a=board&uid={$L[touid]}]查看更多留言[/url]',

	'o_share_success_title'		=> '"{$L[sender]}"评论了"{$L[receiver]}"的分享',
	'o_share_success_cotent'	=> '{$L[title]}\n\n[url={$GLOBALS[db_bbsurl]}/apps.php?q=share]去我的分享页面[/url]',
	'o_write_success_title'		=> '"{$L[sender]}"评论了"{$L[receiver]}"的记录',
	'o_write_success_cotent'	=> '{$L[title]}\n\n[url={$GLOBALS[db_bbsurl]}/apps.php?q=write]去我的记录页面[/url]',
	'o_photo_success_title'		=> '"{$L[sender]}"评论了"{$L[receiver]}"的照片',
	'o_photo_success_cotent'	=> '{$L[title]}\n\n[url={$GLOBALS[db_bbsurl]}/apps.php?username={$L[receiver]}&q=photos&a=view&pid={$L[id]}]去此照片页面[/url]',
	'o_diary_success_title'		=> '"{$L[sender]}"评论了"{$L[receiver]}"的日志',
	'o_diary_success_cotent'	=> '{$L[title]}\n\n[url={$GLOBALS[db_bbsurl]}/apps.php?username={$L[receiver]}&q=diary&a=detail&did={$L[id]}]查看详细日志[/url]',

	'inspect_title'				=> '你的主题已被版主阅读',
	'inspect_content'			=> '您发表的帖子被 [b]{$L[manager]}[/b] 执行 [b]已阅[/b] 操作\r\n[b]帖子标题：[/b]<a target="_blank" href="read.php?tid={$L[tid]}">{$L[subject]}</a>\r\n[b]操作日期：[/b]{$L[postdate]}\r\n[b]操作理由：[/b]{$L[reason]}',
	
	
	'report_title'				=> '有会员举报不良信息，请及时处理',
	'report_content'		=> '你举报的内容已被管理员 [b]{$L[manager]}[/b]处理\r\n [b]类型：[/b]{$L[type]}\r\n[b]操作日期：[/b]{$L[admindate]}\r\n[b]您的举报理由：[/b]{$L[reason]}\r\n[b]链接地址：[/b][url={$L[url]}]进入[/url]',
	'report_content_1_1'	=> '该帖很优秀,建议加为精华帖!'.$lang['_othermsg'],
	'report_content_1_0'	=> '该帖很优秀,建议加为精华帖!'.$lang['_othermsg1'],
	'report_content_0_0'	=> '有会员举报不良信息，请及时处理!'
							. '\n\n[b]类型：$L[type]\n'
							. '[b]操作时间：[/b]{$L[admindate]}\n'
							. '[b]举报理由：[/b]{$L[reason]}\n\n'
							. '[b]链接地址：[/b][url={$L[url]}]进入[/url]\n\n',
							
	'report_deal_title'			=> '您举报的内容被已被管理员处理',
	'report_deal_content'		=> '你举报的内容已被管理员 [b]{$L[manager]}[/b]处理\r\n [b]类型：[/b]{$L[type]}\r\n[b]操作日期：[/b]{$L[admindate]}\r\n[b]您的举报理由：[/b]{$L[reason]}\r\n[b]链接地址：[/b][url={$L[url]}]进入[/url]',
	
	
	
	
	/*'birth_title'				=> '{$L[userName]},祝你生日快乐！',
	'birth_content'				=> '生日快乐，稍上我的祝福，祝你开心每一天！',*/

	'group_attorn_title'		=> '转让群组通知',
	'group_attorn_content'		=> '[b]{$L[username]}[/b]将群组“[url={$GLOBALS[db_bbsurl]}/apps.php?q=group&cyid={$L[cyid]}]{$L[cname]}[/url]”转让给了您，以下是关于此群组的介绍：{$L[descrip]}',

	'group_invite_title'		=> '转让群组通知',
	'email_groupactive_invite_subject' => '{$GLOBALS[windid]}邀请您加入活动{$GLOBALS[objectName]}，并成为他的好友',
	'email_groupactive_invite_content' => '我是{$GLOBALS[windid]}，我在{$GLOBALS[db_bbsname]}上发现了活动{$GLOBALS[objectName]}，下面是关于它的介绍，赶快加入吧！<br />活动{$GLOBALS[objectName]}简介：<br />{$GLOBALS[objectDescrip]}<br /><div id="customdes">{$GLOBALS[customdes]}</div>请点击以下链接，接受好友邀请<br /><a href="{$GLOBALS[invite_url]}">{$GLOBALS[invite_url]}</a>',
	'email_group_invite_subject' => '{$GLOBALS[windid]}邀请您加入群组{$GLOBALS[objectName]}，并成为他的好友',
	'email_group_invite_content' => '我是{$GLOBALS[windid]}，我在{$GLOBALS[db_bbsname]}上发现了群组{$GLOBALS[objectName]}，下面是关于它的介绍，赶快加入吧！。<br />群组{$GLOBALS[objectName]}简介：<br />{$GLOBALS[objectDescrip]} [<a href="{$GLOBALS[db_bbsurl]}/apps.php?q=group&cyid={$GLOBALS[id]}">查看群组</a>]  [<a href="{$GLOBALS[db_bbsurl]}/apps.php?q=group&cyid={$GLOBALS[id]}&a=join&invite_flag=1&step=1" onclick="return ajaxurl(this)">加入群组</a>]<br /><div id="customdes">{$GLOBALS[customdes]}</div>请点击以下链接，接受好友邀请<br /><a href="{$GLOBALS[invite_url]}">{$GLOBALS[invite_url]}</a>',
	'message_group_invite_content' => '{$GLOBALS[windid]}邀请你群组<a href="{$GLOBALS[db_bbsurl]}/apps.php?q=group&cyid={$GLOBALS[id]}">{$GLOBALS[objectName]}</a><br />群组{$GLOBALS[objectName]}介绍：{$GLOBALS[objectDescrip]} ',
	'message_groupactive_invite_subject' => '{$GLOBALS[windid]}邀请您加入活动{$GLOBALS[objectName]}',
	'message_groupactive_invite_content' => '{$GLOBALS[windid]}邀请你加入群组<a href="{$GLOBALS[db_bbsurl]}/apps.php?q=group&cyid={$GLOBALS[cyid]}">{$GLOBALS[colonyName]}</a>的活动“{$GLOBALS[objectName]}”<br />活动介绍：{$GLOBALS[objectDescrip]}<br /><a href="{$GLOBALS[db_bbsurl]}/apps.php?q=group&a=active&cyid={$GLOBALS[cyid]}&job=view&id={$GLOBALS[id]}">快去看看</a>吧！ ',
	'filtermsg_thread_pass_title'	=> '【敏感词】您发表的帖子已经通过审核!',
	'filtermsg_thread_pass_content'	=> '您发布的帖子：{$L[subject]}，已经通过审核。\n\n',
	'filtermsg_thread_del_title'	=> '【敏感词】您发表的帖子因包含敏感内容被管理员删除!',
	'filtermsg_thread_del_content'	=> '您发布的帖子：{$L[subject]}，因包含敏感内容被管理员删除。\n\n',
	'filtermsg_post_pass_title'		=> '【敏感词】您发表的回复已经通过审核!',
	'filtermsg_post_pass_content'	=> '您发表于：{$L[subject]}的回复，已经通过审核。\n\n',
	'filtermsg_post_del_title'		=> '【敏感词】您发表的回复因包含敏感内容被管理员删除!',
	'filtermsg_post_del_content'	=> '您发表于：{$L[subject]}的回复，因包含敏感内容被管理员删除。\n\n',
	'colony_join_title_check'		=> '群组请求[{$L[cname]}]：{$GLOBALS[windid]}申请加入，请审核',
	'colony_join_content_check'		=> '<a href="{$GLOBALS[db_userurl]}{$GLOBALS[winduid]}" target="_blank">{$GLOBALS[windid]}</a>申请加入群组{$L[cname]}，请审核。<a href="{$L[colonyurl]}">查看详情</a>！',
	'colony_join_title'				=> '群组通知[{$L[cname]}]:{$GLOBALS[windid]}已成功加入',
	'colony_join_content'		=> '用户<a href="{$GLOBALS[db_userurl]}{$GLOBALS[winduid]}" target="_blank">{$GLOBALS[windid]}</a>加入了群组[{$L[cname]}] <a href="{$L[colonyurl]}">快去看看吧</a>',
	'o_del_title'				=> '群组通知[{$L[cname]}]：已将你移出该群!',
	'o_del_content'			=> '<a href="{$GLOBALS[db_userurl]}{$GLOBALS[winduid]}" target="_blank">{$GLOBALS[windid]}</a>已经将您移出 [url=$L[curl]]{$L[cname]}[/url] 群了!',

	//团购
	'activity_pcjoin_new_title'		=> '{$L[username]}报名参加了您的团购活动',
	'activity_pcjoin_new_content'	=> '[url=$GLOBALS[db_bbsurl]/{$GLOBALS[db_userurl]}{$L[uid]}]{$L[username]}[/url]报名参加了您发起的团购活动[url=$GLOBALS[db_bbsurl]/read.php?tid={$L[tid]}]“{$L[subject]}”[/url]\r\n\r\n' . '发表日期：{$L[createtime]}'.'\r\n'.'所在版块：[url=$GLOBALS[db_bbsurl]/thread.php?fid={$L[fid]}]{$L[fname]}[/url]',
	
	//活动

	//报名
	'activity_signup_new_title'		=> '{$L[username]}报名参加了您的活动',
	'activity_signup_new_content'	=> '[url=$GLOBALS[db_bbsurl]/{$GLOBALS[db_userurl]}{$L[uid]}]{$L[username]}[/url]报名参加了您发起的活动[url=$GLOBALS[db_bbsurl]/read.php?tid={$L[tid]}]“{$L[subject]}”[/url]\r\n\r\n' . '发表日期：{$L[createtime]}'.'\r\n'.'所在版块：[url=$GLOBALS[db_bbsurl]/thread.php?fid={$L[fid]}]{$L[fname]}[/url]',
	

	//删除
	'activity_signup_close_title'		=> '您从活动中关闭了{$L[username]}',
	'activity_signup_close_content'		=> '您从活动“{$L[subject]}“中关闭了报名者[url=$GLOBALS[db_bbsurl]/{$GLOBALS[db_userurl]}{$L[uid]}]{$L[username]}[/url]\r\n\r\n' . '[url=$GLOBALS[db_bbsurl]/read.php?tid={$L[tid]}]查看活动详情[/url]',
	'activity_signuper_close_title'		=> '您的报名已被{$L[username]}关闭',
	'activity_signuper_close_content'	=> '[url=$GLOBALS[db_bbsurl]/{$GLOBALS[db_userurl]}{$L[uid]}]{$L[username]}[/url]已关闭您在“{$L[subject]}“活动中的报名信息\r\n\r\n' . '[url=$GLOBALS[db_bbsurl]/read.php?tid={$L[tid]}]查看活动详情[/url]',

	//关闭
	'activity_close_pay_title'				=> '您关闭了{$L[username]}的费用',
	'activity_close_pay_content'			=> '您已关闭了[url=$GLOBALS[db_bbsurl]/{$GLOBALS[db_userurl]}{$L[uid]}]{$L[username]}[/url]在活动“{$L[subject]}“中的追加费用{$L[totalcash]}元\r\n\r\n' . '[url=$GLOBALS[db_bbsurl]/read.php?tid={$L[tid]}]查看活动详情[/url]',
	'activity_close_signuper_pay_title'		=> '{$L[username]}关闭了您的费用',
	'activity_close_signuper_pay_content'	=> '[url=$GLOBALS[db_bbsurl]/{$GLOBALS[db_userurl]}{$L[uid]}]{$L[username]}[/url]已关闭了您在活动“{$L[subject]}“中的追加费用{$L[totalcash]}元\r\n\r\n' . '[url=$GLOBALS[db_bbsurl]/read.php?tid={$L[tid]}]查看活动详情[/url]',

	//支付
	'activity_payed_title'				=> '{$L[username]}支付了活动费用',
	'activity_payed_content'			=> '[url=$GLOBALS[db_bbsurl]/{$GLOBALS[db_userurl]}{$L[uid]}]{$L[username]}[/url]通过支付宝支付了“{$L[subject]}“的活动费用{$L[totalcash]}元\r\n\r\n' . '[url=$GLOBALS[db_bbsurl]/read.php?tid={$L[tid]}]查看活动详情[/url]',
	'activity_payed2_content'			=> '[url=$GLOBALS[db_bbsurl]/{$GLOBALS[db_userurl]}{$L[uid]}]{$L[username]}[/url]通过支付宝支付了“{$L[subject]}“的追加费用{$L[totalcash]}元\r\n\r\n' . '[url=$GLOBALS[db_bbsurl]/read.php?tid={$L[tid]}]查看活动详情[/url]',
	'activity_payed_signuper_title'		=> '您支付了活动费用',
	'activity_payed_signuper_content'	=> '您已成功支付了“{$L[subject]}“的活动费用{$L[totalcash]}元\r\n\r\n' . '[url=$GLOBALS[db_bbsurl]/read.php?tid={$L[tid]}]查看活动详情[/url]',
	'activity_payed2_signuper_content'	=> '您已成功支付了“{$L[subject]}“的追加费用{$L[totalcash]}元\r\n\r\n' . '[url=$GLOBALS[db_bbsurl]/read.php?tid={$L[tid]}]查看活动详情[/url]',

	//代付
	'activity_payed_from_title'			=> '您支付了{$L[username]}的活动费用',
	'activity_payed_from_content'		=> '您已成功帮[url=$GLOBALS[db_bbsurl]/{$GLOBALS[db_userurl]}{$L[uid]}]{$L[username]}[/url]支付了“{$L[subject]}“的活动费用{$L[totalcash]}元\r\n\r\n' . '[url=$GLOBALS[db_bbsurl]/read.php?tid={$L[tid]}]查看活动详情[/url]',
	'activity_payed2_from_content'		=> '您已成功帮[url=$GLOBALS[db_bbsurl]/{$GLOBALS[db_userurl]}{$L[uid]}]{$L[username]}[/url]支付了“{$L[subject]}“的追加费用{$L[totalcash]}元\r\n\r\n' . '[url=$GLOBALS[db_bbsurl]/read.php?tid={$L[tid]}]查看活动详情[/url]',

	//被代付
	'activity_payed_signuper_from_title'	=> '{$L[username]}支付了您的活动费用',
	'activity_payed_signuper_from_content'	=> '[url=$GLOBALS[db_bbsurl]/{$GLOBALS[db_userurl]}{$L[uid]}]{$L[username]}[/url]帮您支付了“{$L[subject]}“的活动费用{$L[totalcash]}元\r\n\r\n' . '[url=$GLOBALS[db_bbsurl]/read.php?tid={$L[tid]}]查看活动详情[/url]',
	'activity_payed2_signuper_from_content'	=> '[url=$GLOBALS[db_bbsurl]/{$GLOBALS[db_userurl]}{$L[uid]}]{$L[username]}[/url]帮您支付了“{$L[subject]}“的追加费用{$L[totalcash]}元\r\n\r\n' . '[url=$GLOBALS[db_bbsurl]/read.php?tid={$L[tid]}]查看活动详情[/url]',

	//确认支付
	'activity_confirmpay_title'				=> '您修改了{$L[username]}的支付状态',
	'activity_confirmpay_content'			=> '您已将[url=$GLOBALS[db_bbsurl]/{$GLOBALS[db_userurl]}{$L[uid]}]{$L[username]}[/url]在活动“{$L[subject]}“中{$L[totalcash]}元费用的支付状态改为“已支付”\r\n\r\n' . '[url=$GLOBALS[db_bbsurl]/read.php?tid={$L[tid]}]查看活动详情[/url]',
	'activity_confirmpay2_content'	=> '您已将[url=$GLOBALS[db_bbsurl]/{$GLOBALS[db_userurl]}{$L[uid]}]{$L[username]}[/url]在活动“{$L[subject]}“中{$L[totalcash]}元追加费用的支付状态改为“已支付”\r\n\r\n' . '[url=$GLOBALS[db_bbsurl]/read.php?tid={$L[tid]}]查看活动详情[/url]',
	'activity_confirmpay_signuper_title'	=> '{$L[username]}修改了您的支付状态',
	'activity_confirmpay_signuper_content'	=> '[url=$GLOBALS[db_bbsurl]/{$GLOBALS[db_userurl]}{$L[uid]}]{$L[username]}[/url]已将您在活动“{$L[subject]}“中{$L[totalcash]}元费用的支付状态改为“已支付”\r\n\r\n' . '[url=$GLOBALS[db_bbsurl]/read.php?tid={$L[tid]}]查看活动详情[/url]',
	'activity_confirmpay2_signuper_content'	=> '[url=$GLOBALS[db_bbsurl]/{$GLOBALS[db_userurl]}{$L[uid]}]{$L[username]}[/url]已将您在活动“{$L[subject]}“中{$L[totalcash]}元追加费用的支付状态改为“已支付”\r\n\r\n' . '[url=$GLOBALS[db_bbsurl]/read.php?tid={$L[tid]}]查看活动详情[/url]',

	//退款
	'activity_refund_title'				=> '您退回了{$L[username]}的费用',
	'activity_refund_content'			=> '您已成功退回[url=$GLOBALS[db_bbsurl]/{$GLOBALS[db_userurl]}{$L[uid]}]{$L[username]}[/url]在活动“{$L[subject]}“中的费用{$L[totalcash]}元\r\n\r\n' . '[url=$GLOBALS[db_bbsurl]/read.php?tid={$L[tid]}]查看活动详情[/url]',
	'activity_refund2_content'			=> '您已成功退回[url=$GLOBALS[db_bbsurl]/{$GLOBALS[db_userurl]}{$L[uid]}]{$L[username]}[/url]在活动“{$L[subject]}“中的追加费用{$L[totalcash]}元\r\n\r\n' . '[url=$GLOBALS[db_bbsurl]/read.php?tid={$L[tid]}]查看活动详情[/url]',
	'activity_refund_signuper_title'	=> '{$L[username]}退回了您的费用',
	'activity_refund_signuper_content'	=> '[url=$GLOBALS[db_bbsurl]/{$GLOBALS[db_userurl]}{$L[uid]}]{$L[username]}[/url]已成功退回您在活动“{$L[subject]}“中的费用{$L[totalcash]}元\r\n\r\n' . '[url=$GLOBALS[db_bbsurl]/read.php?tid={$L[tid]}]查看活动详情[/url]',
	'activity_refund2_signuper_content'	=> '[url=$GLOBALS[db_bbsurl]/{$GLOBALS[db_userurl]}{$L[uid]}]{$L[username]}[/url]已成功退回您在活动“{$L[subject]}“中的追加费用{$L[totalcash]}元\r\n\r\n' . '[url=$GLOBALS[db_bbsurl]/read.php?tid={$L[tid]}]查看活动详情[/url]',

	//追加活动费用
	'activity_additional_title'			=> '{$L[username]}追加了你的活动费用',
	'activity_additional_content'		=> '[url=$GLOBALS[db_bbsurl]/{$GLOBALS[db_userurl]}{$L[uid]}]{$L[username]}[/url]追加了“{$L[subject]}“的活动费用{$L[totalcash]}元\r\n\r\n' . '[url=$GLOBALS[db_bbsurl]/read.php?tid={$L[tid]}]查看活动详情[/url]',

	//取消活动
	'activity_cancel_title'				=> '“{$L[subject]}“活动已取消',
	'activity_cancel_content'			=> '“{$L[subject]}“活动未达到最低人数，活动自动取消，请及时退款已缴纳的报名费用\r\n\r\n' . '[url=$GLOBALS[db_bbsurl]/read.php?tid={$L[tid]}]查看活动详情[/url]',
	'activity_cancel_signuper_title'	=> '“{$L[subject]}“活动已取消',
	'activity_cancel_signuper_content'	=> '“{$L[subject]}“活动未达到最低人数，活动自动取消，请联系活动发起者退回报名费用\r\n\r\n' . '[url=$GLOBALS[db_bbsurl]/read.php?tid={$L[tid]}]查看活动详情[/url]',

	//活动被删除（帖子被删除）
	'activity_delete_title'				=> '“{$L[subject]}“活动被删除',
	'activity_delete_content'			=> '“{$L[subject]}“活动被管理员删除，请及时退款已缴纳的报名费用\n\n'
					. '论坛管理操作通知短消息，对本次管理操作有任何异议，请与我取得联系。',
	'activity_delete_signuper_content'	=> '“{$L[subject]}“活动被管理员删除，请联系活动发起者退回报名费用\n\n'
					. '[url=$GLOBALS[db_bbsurl]/mode.php?m=o&q=activity&see=feeslog]查看我的活动费用流通日志[/url]',
					
	'split_title'			=> '您的帖子被拆分',
	'split_content'		    => '您发表的帖子{$L[spiltInfo]}被 [b]{$L[manager]}[/b] 执行 [b]拆分[/b] 操作\n\n'
								. '操作原因：{$L[msg]}\n\n'
								. '论坛管理操作通知短消息，对本次管理操作有任何异议，请与我取得联系。',
								
	//孔明灯
	'kmd_manage_pass_title'	=> '您的孔明灯申请已通过审核',
	'kmd_manage_pass_content'	=> '您好，您于[color=blue]{$L[creadtime]}[/color]申请的孔明灯帖[color=blue]{$L[subject]}[/color]已通过审核，推广到期时间为：[color=blue]{$L[endtime]}[/color]。如有问题，请联系管理员',	
	'kmd_manage_repulse_title'	=> '您的孔明灯申请未通过审核',
	'kmd_manage_repulse_content'	=> '您好，您于[color=blue]{$L[creadtime]}[/color] 申请的孔明灯帖 [color=blue]{$L[subject]}[/color]未通过审核。如有问题，请联系管理员',					
    'kmd_manage_pay_back_title'  => '您的孔明灯申请已退款成功!',
	'kmd_manage_pay_back_content'  => ' 您好，您于 [color=blue]{$L[creadtime]}[/color]  申请的孔明灯帖  [color=blue]{$L[subject]}[/color]，费用为 [color=blue]{$L[rmb]}[/color]元， 已退款成功，请注意查收。如有问题，请联系管理员。  ',		
	'kmd_manage_pay_title'  =>  '您的孔明灯申请已支付成功!',
	'kmd_manage_pay_content'  =>	'您好，您于 [color=blue]{$L[creadtime]}[/color]  申请的孔明灯帖  [color=blue]{$L[subject]}[/color] ，费用为 [color=blue]{$L[rmb]}[/color]元，已成功支付，请等待审核，审核通过后即可正常显示。如有问题，请联系管理员。',
	'kmd_review_title'	=> '【审核】$L[username]已提交孔明灯申请，请确认是否已支付',
	'kmd_review_content'	=> '[url=$GLOBALS[db_bbsurl]/u.php?username=$L[username]]$L[username][/url]提交了孔明灯申请，推广版块：[url=$GLOBALS[db_bbsurl]/thread.php?fid=$L[fid]]$L[forumname][/url]，推广费用：[color=orange]$L[money]元[/color]，请查看后进入后台确认支付状态。',
	'kmd_review_user_title' => '您的孔明灯申请提交成功，请等待审核',
	'kmd_review_user_content' => '您申请的孔明灯，推广版块：[url=$GLOBALS[db_bbsurl]/thread.php?fid=$L[fid]]$L[forumname][/url]，推广费用：[color=orange]$L[money]元[/color]，已成功提交至后台，支付后请联系管理员审核开通。',
	'kmd_review_thread_change_title' => '【审核】$L[username]已提交孔明灯帖子更换申请',
	'kmd_review_thread_add_title' => '【审核】$L[username]已提交孔明灯帖子添加申请',
	'kmd_review_thread_content' => '[url=$GLOBALS[db_bbsurl]/u.php?username=$L[username]]$L[username][/url]提交了孔明灯推广申请，推广帖子：[url=$GLOBALS[db_bbsurl]/read.php?tid=$L[tid]]$L[threadtitle][/url]，请进入后台进行审核状态。',
	'kmd_review_user_thread_title' => '您的孔明灯推广申请提交成功，请等待审核',
	'kmd_review_user_thread_content' => '您申请的孔明灯推广帖子：[url=$GLOBALS[db_bbsurl]/read.php?tid=$L[tid]]$L[threadtitle][/url]，已成功提交至后台，请等待管理员审核开通。',

	'kmd_admin_paylog_checked_title' => '您的孔明灯购买申请已开通成功 ',
	'kmd_admin_paylog_checked_content' => '您申请的孔明灯，推广版块：[url=$GLOBALS[db_bbsurl]/thread.php?fid=$L[fid]]$L[forumname][/url]，推广费用：$L[price]元，已成功开通，现在可以在[url=$GLOBALS[db_bbsurl]/apps.php?q=kmd]个人中心-孔明灯[/url] 添加推广帖子了。',
	'kmd_admin_paylog_reject_title' => '您的孔明灯购买申请未被批准',
	'kmd_admin_paylog_reject_content' => '您申请的孔明灯，推广版块：[url=$GLOBALS[db_bbsurl]/thread.php?fid=$L[fid]]$L[forumname][/url]，推广费用：$L[price]元，未被批准。',
	'kmd_admin_thread_checked_title' => '您的孔明灯推广帖子已被通过',
	'kmd_admin_thread_checked_content' => '您申请的孔明灯推广帖子：[url=$GLOBALS[db_bbsurl]/read.php?tid=$L[tid]]$L[subject][/url]，已被通过，请至相应版块查看效果。',
	'kmd_admin_thread_reject_title' => '您的孔明灯推广帖子已被拒绝',
	'kmd_admin_thread_reject_content' => '您申请的孔明灯推广帖子：您申请的孔明灯推广帖子：[url=$GLOBALS[db_bbsurl]/read.php?tid=$L[tid]]$L[subject][/url]，已被拒绝，请换个帖子试试。，已被拒绝，请换个帖子试试。',
	'kmd_admin_kmd_canceled_title' => '因特殊原因，您的孔明灯已被管理员撤消',
	'kmd_admin_kmd_canceled_content' => '对不起，您的孔明灯涉及不良操作已被管理员撤消，如有疑问请联系站方沟通。',
);
?>