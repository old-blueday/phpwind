<?php
!function_exists('readover') && exit('Forbidden');

$lang['toollog'] = array (

	'sell'				=> '转让',
	'sell_descrip'		=> '[b]{$L[username]}[/b] 转让道具： [b]{$L[toolname]}[/b] ,数量：{$L[nums]}。',

	'buy'				=> '购买',
	'buy_descrip'		=> '[b]{$L[username]}[/b] 向系统购买道具： [b]{$L[toolname]}[/b] ,数量：{$L[nums]},花费：{$L[money]}。',

	'buyuser_descrip'	=> '[b]{$L[username]}[/b] 向[b]{$L[from]}[/b]购买道具： [b]{$L[toolname]}[/b] ,数量：{$L[nums]},花费：{$L[money]}。',
	'buyself_descrip'	=> '[b]{$L[username]}[/b] 购回自己转让的道具： [b]{$L[toolname]}[/b] ,数量：{$L[nums]}',

	'change'			=> '转换',
	'change_descrip_1'	=> '[b]{$L[username]}[/b] 使用[b]交易币转换[/b]功能，获得 {$L[creditinfo]}，总计花费交易币数：{$L[currency]}',
	'change_descrip_2'	=> '[b]{$L[username]}[/b] 使用[b]交易币转换[/b]功能，转换 $L[creditinfo] 为交易币，总共获得交易币：$L[currency]',
	'use'				=> '使用',

	'tool_1_descrip'	=> '[b]{$L[username]}[/b] 使用道具 [b]{$L[toolname]}[/b] ,将负威望转为 0。',

	'tool_2_descrip'	=> '[b]{$L[username]}[/b] 使用道具 [b]{$L[toolname]}[/b] ,将所有负积分转为 0。',

	'tool_3_descrip'	=> '[b]{$L[username]}[/b] 使用道具 [b]{$L[toolname]}[/b] ,将帖子 [url=${$GLOBALS[db_bbsurl]}/read.php?tid=$L[tid]][b]$L[subject][/b][/url] 加亮显示。',

	'tool_4_descrip'	=> '[b]{$L[username]}[/b] 使用道具 [b]{$L[toolname]}[/b] ,将帖子 [url={$GLOBALS[db_bbsurl]}/read.php?tid=$L[tid]][b]$L[subject][/b][/url] 在版块中置顶。',

	'tool_5_descrip'	=> '[b]{$L[username]}[/b] 使用道具 [b]{$L[toolname]}[/b] ,将帖子 [url=${$GLOBALS[db_bbsurl]}/read.php?tid=$L[tid]][b]$L[subject][/b][/url] 在所在分类中置顶。',

	'tool_6_descrip'	=> '[b]{$L[username]}[/b] 使用道具 [b]{$L[toolname]}[/b] ,将帖子 [url=${$GLOBALS[db_bbsurl]}/read.php?tid=$L[tid]][b]$L[subject][/b][/url] 在整个论坛中置顶。',

	'tool_7_descrip'	=> '[b]{$L[username]}[/b] 使用道具 [b]{$L[toolname]}[/b] ,将帖子 [url=${$GLOBALS[db_bbsurl]}/read.php?tid=$L[tid]][b]$L[subject][/b][/url] 提前到该版块首页。',

	'tool_8_descrip'	=> '[b]{$L[username]}[/b] 使用道具 [b]{$L[toolname]}[/b] ,将自己在论坛的用户名改为[b]{$L[newname]}[/b]。',

	'tool_9_descrip'	=> '[b]{$L[username]}[/b] 使用道具 [b]{$L[toolname]}[/b] ,将帖子 [url=${$GLOBALS[db_bbsurl]}/read.php?tid=$L[tid]][b]$L[subject][/b][/url] 加为精华I。',

	'tool_10_descrip'	=> '[b]{$L[username]}[/b] 使用道具 [b]{$L[toolname]}[/b] ,将帖子 [url=${$GLOBALS[db_bbsurl]}/read.php?tid=$L[tid]][b]$L[subject][/b][/url] 加为精华II。',

	'tool_11_descrip'	=> '[b]{$L[username]}[/b] 使用道具 [b]{$L[toolname]}[/b] ,将帖子 [url=${$GLOBALS[db_bbsurl]}/read.php?tid=$L[tid]][b]$L[subject][/b][/url] 锁定。',

	'tool_12_descrip'	=> '[b]{$L[username]}[/b] 使用道具 [b]{$L[toolname]}[/b] ,将帖子 [url=${$GLOBALS[db_bbsurl]}/read.php?tid=$L[tid]][b]$L[subject][/b][/url] 解除锁定。',

	'tool_13_descrip'	=> '[b]{$L[username]}[/b] 使用道具 [b]{$L[toolname]}[/b] ,将帖子 [url=${$GLOBALS[db_bbsurl]}/read.php?tid=$L[tid]][b]$L[subject][/b][/url] 支持度加1。',

	'tool_14_descrip'	=> '[b]{$L[username]}[/b] 使用道具 [b]{$L[toolname]}[/b] ,将帖子 [url=${$GLOBALS[db_bbsurl]}/read.php?tid=$L[tid]][b]$L[subject][/b][/url] 反对度加1。',

	'tool_15_descrip'	=> '[b]{$L[username]}[/b] 使用道具 [b]{$L[toolname]}[/b] ,使自己获得[b]{$L[newcurrency]}[/b]交易币。',

	'tool_16_descrip'	=> '[b]{$L[username]}[/b] 使用道具 [b]{$L[toolname]}[/b] ,为对方送去生日祝福。',

	'tool_17_descrip'	=> '[b]{$L[username]}[/b] 使用道具 [b]{$L[toolname]}[/b] ,将帖子 [url=${$GLOBALS[db_bbsurl]}/read.php?tid=$L[tid]][b]$L[subject][/b][/url] 停止在12小时以前。',

	'tool_18_descrip'	=> '[b]{$L[username]}[/b] 使用道具 [b]{$L[toolname]}[/b] ,将对方头像变成猪头。',
	'tool_19_descrip'	=> '[b]{$L[username]}[/b] 使用道具 [b]{$L[toolname]}[/b] ,还原猪头头像。',
	'tool_20_descrip'	=> '[b]{$L[username]}[/b] 使用道具 [b]{$L[toolname]}[/b] ,查看IP。',

	'tool_21_descrip'	=> '[b]{$L[username]}[/b] 使用道具 [b]{$L[toolname]}[/b] ,对特定用户使用了护身符。',

	'tool_22_descrip'	=> '[b]{$L[username]}[/b] 使用道具 [b]{$L[toolname]}[/b] ,将帖子 [url={$GLOBALS[db_bbsurl]}/read.php?tid=$L[tid]][b]$L[subject][/b][/url] 停止在12小时以后。',

	'olpay'				=> '充值',
	'olpay_descrip'		=> '[b]{$L[username]}[/b] 使用交易币充值功能，充值金额：{$L[number]}，获得交易币：{$L[currency]}',
	'colony'			=> '建群',
	'colony_descrip'	=> '[b]{$L[username]}[/b] 使用创建群功能创建群[b]{$L[cname]}[/b]，总共花费交易币：{$L[currency]}。',
	'join'				=> '加群',
	'join_descrip'		=> '[b]{$L[username]}[/b] 加入群[b]{$L[cname]}[/b]，花费交易币：{$L[currency]}。',
	'sign'				=>	'签名',
	'sign_descrip'		=> '[b]{$L[username]}[/b] 使用签名展示功能，花费{$L[ctype]}：{$L[currency]}{$L[cunit]}。',
	'vire'				=>	'转帐',
	'vire_descrip'		=> '[b]{$L[username]}[/b] 使用交易币转帐功能，给用户[b]{$L[toname]}[/b]转帐 {$L[currency]}交易币，'
							. '系统收取手续费：{$L[tax]}。',
	'donate'			=> '提升',
	'donate_descrip'	=> '[b]{$L[username]}[/b] 使用提升我的荣誉点功能，消耗交易币：{$L[currency]}。',
	'cyvire_descrip'	=> '[b]{$L[username]}[/b] 使用群交易币管理功能，给用户[b]{$L[toname]}[/b]转帐 {$L[currency]}交易币，'
							. '系统收取手续费：{$L[tax]}。',
	'group'				=> '用户组',
	'group_descrip'		=> '[b]{$L[username]}[/b] 使用用户组身份购买功能，购买用户组($L[gptitle])身份{$L[days]}天，'
							. '总共花费{$L[curtype]}数：{$L[currency]}。',
	);
?>