<?php
!function_exists('readover') && exit('Forbidden');

$lang['email'] = array (

'email_check_subject'	=> '激活您在 {$GLOBALS[db_bbsname]} 会员帐号的必要步骤!',
'email_check_content'	=> '<html><head><meta http-equiv="Content-Type" content="text/html; charset=gb18030" /><title>激活帐号</title></head><body><div align="center"><table cellpadding="0" cellspacing="1" style="border:3px solid #d9e9f1;background:#7fbddd; text-align:left;"><tr><td style="padding:0;"><table cellpadding="30" cellspacing="0" style="border:1px solid #ffffff;background:#f7f7f7;width:500px;"><tr><td style="line-height:2;font-size:12px;"><div style="font-size:14px;margin-bottom:10px;font-weight:700;">Hi, {$GLOBALS[regname]}</div>
						<p style="color:#ff6600;margin:0;">请点击下面的链接完成激活：</p>
						<div style="padding:8px 10px;margin:10px 0 5px;background:#ffffff;border:1px solid #cbcbcb;word-break:break-all;word-wrap:break-word;line-height:1.5;font-size:14px;"><a href="{$GLOBALS[db_bbsurl]}/{$GLOBALS[db_registerfile]}?step=finish&vip=activating&r_uid={$GLOBALS[winduid]}&pwd={$GLOBALS[rgyz]}&toemail={$GLOBALS[regemail]}" style="color:#3366cc;">{$GLOBALS[db_bbsurl]}/{$GLOBALS[db_registerfile]}?step=finish&vip=activating&r_uid={$GLOBALS[winduid]}&pwd={$GLOBALS[rgyz]}</a></div>
						<div style="color:#999999;margin-bottom:5px;">如果不能点击链接，请复制地址并粘贴到浏览器的地址输入框</div>
						激活后尽快删除此邮件，以免帐号信息泄漏<div style="border-top:1px solid #e2e2e2;background:#ffffff;overflow:hidden;height:1px;*height:2px;margin:10px 0;"></div>欢迎您加入{$GLOBALS[db_bbsname]}，请妥善保管好您的帐号信息<p style="margin:0;"><span style="padding-right:5em;">用户名：{$GLOBALS[regname]}</span>密码：{$GLOBALS[sRegpwd]}</p>
						<div style="border-top:1px solid #e2e2e2;background:#ffffff;overflow:hidden;height:1px;*height:2px;margin:10px 0;"></div>
						如果忘记密码，可以到社区找回密码，也可以写信请管理员重新设定。<br />
						社区地址：<a href="{$GLOBALS[db_bbsurl]}" style="color:#3366cc;">{$GLOBALS[db_bbsurl]}</a></td></tr></table></td></tr></table></div></body></html>',
'email_check_content_resend'	=> '<html><head><meta http-equiv="Content-Type" content="text/html; charset=gb18030" /><title>激活帐号</title></head><body><div align="center"><table cellpadding="0" cellspacing="1" style="border:3px solid #d9e9f1;background:#7fbddd; text-align:left;"><tr><td style="padding:0;"><table cellpadding="30" cellspacing="0" style="border:1px solid #ffffff;background:#f7f7f7;width:500px;"><tr><td style="line-height:2;font-size:12px;"><div style="font-size:14px;margin-bottom:10px;font-weight:700;">Hi, {$GLOBALS[regname]}</div>
						<p style="color:#ff6600;margin:0;">请点击下面的链接完成激活：</p>
						<div style="padding:8px 10px;margin:10px 0 5px;background:#ffffff;border:1px solid #cbcbcb;word-break:break-all;word-wrap:break-word;line-height:1.5;font-size:14px;"><a href="{$GLOBALS[db_bbsurl]}/{$GLOBALS[db_registerfile]}?step=finish&vip=activating&r_uid={$GLOBALS[winduid]}&pwd={$GLOBALS[rgyz]}&toemail={$GLOBALS[regemail]}" style="color:#3366cc;">{$GLOBALS[db_bbsurl]}/{$GLOBALS[db_registerfile]}?step=finish&vip=activating&r_uid={$GLOBALS[winduid]}&pwd={$GLOBALS[rgyz]}</a></div>
						<div style="color:#999999;margin-bottom:5px;">如果不能点击链接，请复制地址并粘贴到浏览器的地址输入框</div>
						激活后尽快删除此邮件，以免帐号信息泄漏<div style="border-top:1px solid #e2e2e2;background:#ffffff;overflow:hidden;height:1px;*height:2px;margin:10px 0;"></div>
						<div style="border-top:1px solid #e2e2e2;background:#ffffff;overflow:hidden;height:1px;*height:2px;margin:10px 0;"></div>
						如果忘记密码，可以到社区找回密码，也可以写信请管理员重新设定。<br />
						社区地址：<a href="{$GLOBALS[db_bbsurl]}" style="color:#3366cc;">{$GLOBALS[db_bbsurl]}</a></td></tr></table></td></tr></table></div></body></html>',

'email_additional'		=> 'Reply-To:{$GLOBALS[fromemail]}\r\nX-Mailer: phpwind电子邮件快递',

'email_welcome_subject'	=> '{$GLOBALS[regname]},您好,感谢您注册{$GLOBALS[db_bbsname]}',
'email_welcome_content'	=> '<html><head><meta http-equiv="Content-Type" content="text/html; charset=gb18030" /><title>感谢您注册</title></head><body><div align="center"><table cellpadding="0" cellspacing="1" style="border:3px solid #d9e9f1;background:#7fbddd; text-align:left;"><tr><td style="padding:0;"><table cellpadding="30" cellspacing="0" style="border:1px solid #ffffff;background:#f7f7f7;width:500px;"><tr><td style="line-height:2;font-size:12px;"><div style="font-size:14px;margin-bottom:10px;font-weight:700;">Hi, {$GLOBALS[regname]}</div>{$GLOBALS[db_bbsname]}欢迎您的加入！<p style="margin:0;"><span style="padding-right:5em;">您的注册名为： {$GLOBALS[regname]}</span>您的密码为： {$GLOBALS[sRegpwd]}</p><div style="border-top:1px solid #e2e2e2;background:#ffffff;overflow:hidden;height:1px;*height:2px;margin:10px 0;"></div>请尽快删除此邮件，以免帐号信息泄漏<br />如果忘记密码，可以到社区找回密码，也可以写信请管理员重新设定。<br />社区地址：<a href="{$GLOBALS[db_bbsurl]}">{$GLOBALS[db_bbsurl]}</a></td></tr></table></td></tr></table></div></body></html>',

'email_sendpwd_subject'	=> '{$GLOBALS[db_bbsname]} 密码重发',
'email_sendpwd_content'	=> '<html><head><meta http-equiv="Content-Type" content="text/html; charset=gb18030" /><title>密码重发</title></head><body><div align="center"><table cellpadding="0" cellspacing="1" style="border:3px solid #d9e9f1;background:#7fbddd; text-align:left;"><tr><td style="padding:0;"><table cellpadding="30" cellspacing="0" style="border:1px solid #ffffff;background:#f7f7f7;width:500px;"><tr><td style="line-height:2;font-size:12px;">请到下面的网址修改密码：<div style="padding:8px 10px;margin:10px 0 5px;background:#ffffff;border:1px solid #cbcbcb;word-break:break-all;word-wrap:break-word;line-height:1.5;font-size:14px;"><a href="{$GLOBALS[db_bbsurl]}/sendpwd.php?action=getback&pwuser={$GLOBALS[pwuser]}&submit={$GLOBALS[submit]}&st={$GLOBALS[timestamp]}">{$GLOBALS[db_bbsurl]}/sendpwd.php?action=getback&pwuser={$GLOBALS[pwuser]}&submit={$GLOBALS[submit]}&st={$GLOBALS[timestamp]}</a></div>修改后请牢记您的密码<br />欢迎来到 {$GLOBALS[db_bbsname]} 我们的网址是:<a href="{$GLOBALS[db_bbsurl]}">{$GLOBALS[db_bbsurl]}</a></td></tr></table></td></tr></table></div></body></html>',

'email_reply_subject'	=> '您在{$GLOBALS[db_bbsname]}的帖子有回复',
'email_reply_content'	=> '<html><head><meta http-equiv="Content-Type" content="text/html; charset=gb18030" /><title>帖子有回复</title></head><body><div align="center"><table cellpadding="0" cellspacing="1" style="border:3px solid #d9e9f1;background:#7fbddd; text-align:left;"><tr><td style="padding:0;"><table cellpadding="30" cellspacing="0" style="border:1px solid #ffffff;background:#f7f7f7;width:500px;"><tr><td style="line-height:2;font-size:12px;"><div style="font-size:14px;margin-bottom:10px;font-weight:700;">Hi</div>我是{$GLOBALS[db_bbsname]}邮件大使<br />您在{$GLOBALS[db_bbsname]}发表的帖子: {$GLOBALS[old_title]}<br />现在有人回复.快来关注一下吧<br /><a href="{$GLOBALS[db_bbsurl]}/read.php?fid={$GLOBALS[fid]}&tid={$GLOBALS[tid]}">{$GLOBALS[db_bbsurl]}/read.php?fid={$GLOBALS[fid]}&tid={$GLOBALS[tid]}</a><br />下次再有人参与主题时,我将不来打扰了</td></tr></table></td></tr></table></div></body></html>',

'email_invite_subject'	=> '您的朋友{$GLOBALS[windid]}邀请您加入{$GLOBALS[db_bbsname]}',
'email_invite_content'	=> '<html><head><meta http-equiv="Content-Type" content="text/html; charset=gb18030" /><title>购买注册码</title></head><body><div align="center"><table cellpadding="0" cellspacing="1" style="border:3px solid #d9e9f1;background:#7fbddd; text-align:left;"><tr><td style="padding:0;"><table cellpadding="30" cellspacing="0" style="border:1px solid #ffffff;background:#f7f7f7;width:500px;"><tr><td style="line-height:2;font-size:12px;">您在{$GLOBALS[db_bbsname]}论坛购买的邀请码如下：
<div style="padding:8px 10px;margin:10px 0;background:#ffffff;border:1px solid #cbcbcb;word-break:break-all;word-wrap:break-word;line-height:1.5;font-size:14px;">{$GLOBALS[invlink]}</div>注册地址：<a href="{$GLOBALS[db_bbsurl]}/{$GLOBALS[db_registerfile]}">{$GLOBALS[db_bbsurl]}/{$GLOBALS[db_registerfile]}</a></td></tr></table></td></tr></table></div></body></html>',
'email_invite_content_new' => '<html><head><meta http-equiv="Content-Type" content="text/html; charset=gb18030" /><title>购买注册码
</title></head><body><div align="center">
<table cellpadding="0" cellspacing="1" style="border:3px solid #d9e9f1;background:#7fbddd; text-align:left;">
<tr>
<td style="padding:0;">
<table cellpadding="30" cellspacing="0" style="border:1px solid #ffffff;background:#f7f7f7;width:500px;">
<tr><td>{$GLOBALS[extranote]}</td></tr>
<tr><td style="line-height:2;font-size:12px;"><div style="padding:8px 10px;margin:10px 0;background:#ffffff;border:1px solid 

#cbcbcb;word-break:break-all;word-wrap:break-word;line-height:1.5;font-size:14px;">{$GLOBALS[invlink]}</div></td></tr>
</table>
</td></tr></table></div></body></html>',

'emailcheck_subject'	=> 'PHPwind电子邮件发送检测',
'emailcheck_content'	=> 'PHPwind电子邮件发送检测成功!',
'email_mode_o_title'	=> '您的朋友{$GLOBALS[windid]}邀请您加入{$GLOBALS[db_bbsname]}',
'email_mode_o_content'	=> '<html><head><meta http-equiv="Content-Type" content="text/html; charset=gb18030" /><title>购买注册码</title></head><body><div align="center"><table cellpadding="0" cellspacing="1" style="border:3px solid #d9e9f1;background:#7fbddd; text-align:left;"><tr><td style="padding:0;"><table cellpadding="30" cellspacing="0" style="border:1px solid #ffffff;background:#f7f7f7;width:500px;"><tr><td style="line-height:2;font-size:12px;">我是{$GLOBALS[windid]}，我在{$GLOBALS[db_bbsname]}上建立了个人主页，请你也加入并成为我的好友。<br />{$GLOBALS[extranote]}\n\n请点击以下链接，接受好友邀请：<br /><a href="{$GLOBALS[invite_url]}">{$GLOBALS[invite_url]}</a><br />{$GLOBALS[db_bbsname]} (<a href="{$GLOBALS[db_bbsurl]}">{$GLOBALS[db_bbsurl]}</a>)</td></tr></table></td></tr></table></div></body></html>',
'email_groupactive_invite_subject' => '{$GLOBALS[windid]}邀请您加入活动{$GLOBALS[objectName]}',
'email_groupactive_invite_content' => '我是{$GLOBALS[windid]}，我在{$GLOBALS[db_bbsname]}上发现了活动{$GLOBALS[objectName]}，请你也加入并成为我的好友。<br />活动{$GLOBALS[objectName]}介绍：<br />{$GLOBALS[objectDescrip]}<br /><div id="customdes">{$GLOBALS[customdes]}</div>请点击以下链接，接受好友邀请<br /><a href="{$GLOBALS[invite_url]}">{$GLOBALS[invite_url]}</a>',
'email_group_invite_subject' => '{$GLOBALS[windid]}邀请您加入群组{$GLOBALS[objectName]}',
'email_group_invite_content' => '我是{$GLOBALS[windid]}，我在{$GLOBALS[db_bbsname]}上发现了群组{$GLOBALS[objectName]}，请你也加入并成为我的好友。<br />群组{$GLOBALS[objectName]}介绍：<br />{$GLOBALS[objectDescrip]} [<a href="{$GLOBALS[db_bbsurl]}/apps.php?q=group&cyid={$GLOBALS[cyid]}">查看群组</a>]<br /><div id="customdes">{$GLOBALS[customdes]}</div>请点击以下链接，接受好友邀请<br /><a href="{$GLOBALS[invite_url]}">{$GLOBALS[invite_url]}</a>',

);
?>