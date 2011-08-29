<?php
!function_exists('readover') && exit('Forbidden');

$lang['right_title'] = array(
	'basic'		=> '基本权限',
	'read'		=> '帖子权限',
	'att'		=> '附件权限',
	'group'		=> '群组权限',
	'message'	=> '消息权限',
	'special'	=> '用户组购买',
	'system'	=> '管理权限'
);
$lang['right'] = array (
	'message' => array(
		'allowmessege'	=> array(
			'title'	=> '发送消息',
			'desc'  => '开启后，此用户组的用户可以发送消息',
			'html'	=> '<ul class="list_A list_80 cc"><li><input type="radio" value="1" name="group[allowmessege]" $allowmessege_Y />开启</li><li><input type="radio" value="0" name="group[allowmessege]" $allowmessege_N />关闭</li></ul>'
		),
		'maxmsg'	=> array(
			'title'	=> '可存储的最大消息数目',
			'desc'	=> '只统计短消息和多人对话',
			'html'	=> '<input class="input input_wa" value="$maxmsg" name="group[maxmsg]" />'
		),
		'maxsendmsg'	=> array(
			'title'	=> '每日最大发送消息数目',
			'html'	=> '<input class="input input_wa" value="$maxsendmsg" name="group[maxsendmsg]" />'
		),
		'messagecontentsize' => array(
			'title'   => '每条消息内容最多字节数',
			'html'    => '<input class="input input_wa" name="group[messagecontentsize]" value="$messagecontentsize" />'
		),
		'msggroup'	=> array(
			'title'	=> '只接收特定用户组的消息',
			'html'	=> '<ul class="list_A list_80 cc"><li><input type="radio" value="1" name="group[msggroup]" $msggroup_Y />开启</li><li><input type="radio" value="0" name="group[msggroup]" $msggroup_N />关闭</li></ul>'
		),
		'multiopen' => array(
			'title'	  => '发送多人消息',
			'desc'	  => '开启后，此用户组的用户可以发送多人消息',
			'html'    => '<ul class="list_A list_80 cc"><li><input type="radio" value="1" name="group[multiopen]" $multiopen_Y />开启</li><li><input type="radio" value="0" name="group[multiopen]" $multiopen_N />关闭</li></ul>'
		)
	),
	'basic' => array(
		'allowvisit' => array(
			'title'	=> '站点访问',
			'desc'	=> '关闭后，用户将不能访问站点的任何页面',
			'html'	=> '<ul class="list_A list_80 cc"><li><input type="radio" value="1" name="group[allowvisit]" $allowvisit_Y />开启</li><li><input type="radio" value="0" name="group[allowvisit]" $allowvisit_N />关闭</li></ul>'
		),
		'allowhide' => array(
			'title'	=> '隐身登录',
			'desc'	=> '开启后，用户可以隐身登录站点',
			'html'	=> '<ul class="list_A list_80 cc"><li><input type="radio" value="1" name="group[allowhide]" $allowhide_Y />开启</li><li><input type="radio" value="0" name="group[allowhide]" $allowhide_N />关闭</li></ul>'
		),
		'userbinding' => array(
			'title'	=> '多账号绑定',
			'desc'	=> '俗称马甲绑定',
			'desc'	=> '开启后，用户可以进行多账号绑定',
			'html'	=> '<ul class="list_A list_80 cc"><li><input type="radio" value="1" name="group[userbinding]" $userbinding_Y />开启</li><li><input type="radio" value="0" name="group[userbinding]" $userbinding_N />关闭</li></ul>'
		),
		'allowread'	=> array(
			'title'	=> '浏览帖子',
			'desc'	=> '开启后，用户可以浏览帖子',
			'html'	=> '<ul class="list_A list_80 cc"><li><input type="radio" value="1" name="group[allowread]" $allowread_Y />开启</li><li><input type="radio" value="0" name="group[allowread]" $allowread_N />关闭</li></ul>'
		),
		'allowsearch'	=> array(
			'title'	=> '搜索控制',
			'desc'	=> '修改用户的搜索权限',
			'html'	=> '<ul class="list_A"><li><input type="radio" value="0" name="group[allowsearch]" $allowsearch_0 />不允许</li>
			<li><input type="radio" value="1" name="group[allowsearch]" $allowsearch_1 />允许搜索主题标题</li>
			<li><input type="radio" value="2" name="group[allowsearch]" $allowsearch_2 />允许搜索主题标题、内容</li>
			<li><input type="radio" value="3" name="group[allowsearch]" $allowsearch_3 />允许搜索全部内容(包含回复内容)</li></ul>'
		),
		'allowmember'	=> array(
			'title'	=> '查看会员列表',
			'desc'	=> '开启后，用户可以查看会员列表',
			'html'	=> '<ul class="list_A list_80 cc"><li><input type="radio" value="1" name="group[allowmember]" $allowmember_Y />开启</li><li><input type="radio" value="0" name="group[allowmember]" $allowmember_N />关闭</li></ul>'
		),
		'allowviewonlineread'	=> array(
			'title'	=> '查看在线会员所在页面',
			'desc'	=> '允许此用户组用户查看在线会员当前所在的页面',
			'html'	=> '<ul class="list_A list_80 cc"><li><input type="radio" value="1" name="group[allowviewonlineread]" $allowviewonlineread_Y />开启</li><li><input type="radio" value="0" name="group[allowviewonlineread]" $allowviewonlineread_N />关闭</li></ul>'
		),
		/*
		'allowprofile'	=> array(
			'title'	=> '查看会员资料',
			'html'	=> '<ul class="list_A list_80 cc"><li><input type="radio" value="1" name="group[allowprofile]" $allowprofile_Y />开启</li><li><input type="radio" value="0" name="group[allowprofile]" $allowprofile_N />关闭</li></ul>'
		),
		*/
		'atclog' => array(
			'title'	=> '查看帖子操作记录',
			'desc'	=> '允许用户查看自己帖子的被操作情况',
			'html'	=> '<ul class="list_A list_80 cc"><li><input type="radio" value="1" name="group[atclog]" $atclog_Y />开启</li><li><input type="radio" value="0" name="group[atclog]" $atclog_N />关闭</li></ul>'
		),
		//去掉展区功能@modify panjl@2010-11-2
		/*
		'show' => array(
			'title'	=> '使用展区',
			'html'	=> '<ul class="list_A list_80 cc"><li><input type="radio" value="1" name="group[show]" $show_Y />开启</li><li><input type="radio" value="0" name="group[show]" $show_N />关闭</li></ul>'
		),
		*/
		'allowreport' => array(
			'title'	=> '使用举报',
			'desc'	=> '开启后，此用户组的用户可以使用举报功能',
			'html'	=> '<ul class="list_A list_80 cc"><li><input type="radio" value="1" name="group[allowreport]" $allowreport_Y />开启</li><li><input type="radio" value="0" name="group[allowreport]" $allowreport_N />关闭</li></ul>'
		),
		'upload' => array(
			'title'	=> '头像上传',
			'desc'	=> "此设置需先在 <a href=\"$admin_file?adminjob=member\" onclick=\"parent.PW.Dialog({id:'member',url:'$admin_file?adminjob=member',name:'会员相关'});return false;\">全局->会员相关->头像设置</a> 中开启头像上传功能才有效",
			'html'	=> '<ul class="list_A list_80 cc"><li><input type="radio" value="1" name="group[upload]" $upload_Y />开启</li><li><input type="radio" value="0" name="group[upload]" $upload_N />关闭</li></ul>'
		),
		'allowportait'	=> array(
			'title'	=> '头像链接',
			'desc'	=> '开启后，此用户组的用户可以用外部网站图片链接地址作为自己的头像',
			'html'	=> '<ul class="list_A list_80 cc"><li><input type="radio" value="1" name="group[allowportait]" $allowportait_Y />开启</li><li><input type="radio" value="0" name="group[allowportait]" $allowportait_N />关闭</li></ul>'
		),
		'allowhonor'	=> array(
			'title'	=> '个性签名',
			'desc'	=> '开启后，此用户组的用户可以使用个性签名功能',
			'html'	=> '<ul class="list_A list_80 cc"><li><input type="radio" value="1" name="group[allowhonor]" $allowhonor_Y />开启</li><li><input type="radio" value="0" name="group[allowhonor]" $allowhonor_N />关闭</li></ul>'
		),
		/*'allowmessege'	=> array(
			'title'	=> '发送消息',
			'html'	=> '<ul class="list_A list_80 cc"><li><input type="radio" value="1" name="group[allowmessege]" $allowmessege_Y />开启</li><li><input type="radio" value="0" name="group[allowmessege]" $allowmessege_N />关闭</li></ul>'
		),*/
		'allowsort'	=> array(
			'title'	=> '查看统计排行',
			'desc'	=> '开启后，此用户组的用户可以查看统计排行',
			'html'	=> '<ul class="list_A list_80 cc"><li><input type="radio" value="1" name="group[allowsort]" $allowsort_Y />开启</li><li><input type="radio" value="0" name="group[allowsort]" $allowsort_N />关闭</li></ul>'
		),
		'alloworder'=> array(
			'title'	=> '主题排序',
			'html'	=> '<ul class="list_A list_80 cc"><li><input type="radio" value="1" name="group[alloworder]" $alloworder_Y />开启</li><li><input type="radio" value="0" name="group[alloworder]" $alloworder_N />关闭</li></ul>'
		),
		'viewipfrom'	=> array(
			'title'	=> '查看ip来源',
			'desc'	=> "如果论坛模式下界面设置中<a href=\"$admin_file?adminjob=interfacesettings&adminitem=read\">帖子阅读页设置</a>关闭此功能，则此项设置无效",
			'html'	=> '<ul class="list_A list_80 cc"><li><input type="radio" value="1" name="group[viewipfrom]" $viewipfrom_Y />开启</li><li><input type="radio" value="0" name="group[viewipfrom]" $viewipfrom_N />关闭</li></ul>'
		),
		'searchtime'	=> array(
			'title'	=> '两次搜索时间间隔[秒]',
			'html'	=> '<input class="input input_wa" name="group[searchtime]" value="$searchtime" />'
		),
		/*'schtime' => array(
			'title'	=> '搜索发表时间范围',
			'html'	=> '<select name="group[schtime]" class="select_wa">
				<option value="all" $schtime_all>所有主题</option>
				<option value="86400" $schtime_86400>1天内的主题</option>
				<option value="172800" $schtime_172800>2天内的主题</option>
				<option value="604800" $schtime_604800>1星期内的主题</option>
				<option value="2592000" $schtime_2592000>1个月内的主题</option>
				<option value="5184000" $schtime_5184000>2个月内的主题</option>
				<option value="7776000" $schtime_7776000>3个月内的主题</option>
				<option value="15552000" $schtime_15552000>6个月内的主题</option>
				<option value="31536000" $schtime_31536000>1年内的主题</option>
			</select>'
		),*/
		'signnum' => array(
			'title'	=> '帖子签名最大字节数',
			'desc'	=> '为0则不限制',
			'html'	=> '<input class="input input_wa" name="group[signnum]" value="$signnum" />'
		),
		'imgwidth' => array(
			'title'	=> '签名中的图片最大宽度',
			'desc'	=> "留空使用<a href=\"$admin_file?adminjob=member\"  onclick=\"parent.PW.Dialog({id:'member',url:'$admin_file?adminjob=member',name:'会员相关'});return false;\">全局->会员相关->签名设置</a>里的设置",
			'html'	=> '<input class="input input_wa" name="group[imgwidth]" value="$imgwidth" />'
		),
		'imgheight' => array(
			'title'	=> '签名中的图片最大高度',
			'desc'	=> "留空使用<a href=\"$admin_file?adminjob=member\"  onclick=\"parent.PW.Dialog({id:'member',url:'$admin_file?adminjob=member',name:'会员相关'});return false;\">全局->会员相关->签名设置</a>里的设置",
			'html'	=> '<input class="input input_wa" name="group[imgheight]" value="$imgheight" />'
		),
		'fontsize'	=> array(
			'title'	=> '签名中[size]标签最大值',
			'desc'	=> "留空使用<a href=\"$admin_file?adminjob=member\"  onclick=\"parent.PW.Dialog({id:'member',url:'$admin_file?adminjob=member',name:'会员相关'});return false;\">全局->会员相关->签名设置</a>里的设置",
			'html'	=> '<input class="input input_wa" name="group[fontsize]" value="$fontsize" />'
		),
		/*'maxmsg'	=> array(
			'title'	=> '最大短消息数目',
			'desc'	=> '最大消息数为个人消息，不包括群发消息和系统消息',
			'html'	=> '<input class="input input_wa" value="$maxmsg" name="group[maxmsg]" />'
		),*/
		/*'maxsendmsg'	=> array(
			'title'	=> '每日最大发送短消息数目',
			'html'	=> '<input class="input input_wa" value="$maxsendmsg" name="group[maxsendmsg]" />'
		),*/
		/*'msggroup'	=> array(
			'title'	=> '只接收特定用户组的消息',
			'html'	=> '<ul class="list_A list_80 cc"><li><input type="radio" value="1" name="group[msggroup]" $msggroup_Y />开启</li><li><input type="radio" value="0" name="group[msggroup]" $msggroup_N />关闭</li></ul>'
		),*/
		/*'ifmemo'	=> array(
			'title'	=> '能使用便笺的功能',
			'html'	=> '<ul class="list_A list_80 cc"><li><input type="radio" value="1" name="group[ifmemo]" $ifmemo_Y />开启</li><li><input type="radio" value="0" name="group[ifmemo]" $ifmemo_N />关闭</li></ul>'
		),*/
		'pergroup' =>	array(
			'title'	=> '查看用户组权限',
			'html'	=> '<ul class="list_A list_80 cc"><li><input type="checkbox" name="group[pergroup][]" value="member" $pergroup_sel[member] />会员组</li><li><input type="checkbox" name="group[pergroup][]" value="system" $pergroup_sel[system] />系统组</li><li><input type="checkbox" name="group[pergroup][]" value="special" $pergroup_sel[special] />特殊组</li></ul>'
		),
		'maxfavor'	=> array(
			'title'	=> '收藏夹容量',
			'desc'  => '设置收藏夹可以收藏的信息条数',
			'html'	=> '<input class="input input_wa" value="$maxfavor" name="group[maxfavor]" />'
		),
		'maxgraft'	=> array(
			'title'	=> '草稿箱容量',
			'desc'  => '设置草稿箱可以容纳的信息条数',
			'html'	=> '<input class="input input_wa" value="$maxgraft" name="group[maxgraft]" />'
		),
		'pwdlimitime'	=> array(
			'title'	=> '强制用户组密码变更[天]',
			'desc'	=> '0或留空表示不限制',
			'html'	=> '<input class="input input_wa" value="$pwdlimitime" name="group[pwdlimitime]" />'
		),
		/*'maxcstyles'	=> array(
			'title'	=> '自定义风格数量',
			'desc'	=> '(设置0或留空，则不允许使用自定义风格)',
			'html'	=> '<input class="input input_wa" value="$maxcstyles" name="group[maxcstyles]" />'
		)*/
	),
	'read'	=> array(
		'allowpost'	=> array(
			'title'	=> '发表主题',
			'desc'	=> '开启后，此用户组的用户可以发表主题',
			'html'	=> '<ul class="list_A list_80 cc"><li><input type="radio" value="1" name="group[allowpost]" $allowpost_Y />开启</li><li><input type="radio" value="0" name="group[allowpost]" $allowpost_N />关闭</li></ul>'
		),
		'specialtopics' => array(
			'title'	=> '允许发表特殊主题',
			'desc'	=> '允许修改投票选项：允许用户修改自己已发起的投票的选项<br/>勾选此用户组用户可发表的特殊主题，此权限同时受版块权限中的可发表的帖子类型控制',
			'html'	=> '
				<input type="checkbox" name="group[allownewvote]" value="1" $allownewvote_Y /> 投票帖
				&nbsp; <select class="elect_wa" name="group[modifyvote]">
					<option value="1" $modifyvote_Y>允许修改投票选项</option>
					<option value="0" $modifyvote_N>不允许修改投票选项</option>
				</select>
				<ul class="list_120 cc">
					<li><input type="checkbox" name="group[allowreward]" value="1" $allowreward_Y /> 悬赏帖</li>
					<li><input type="checkbox" name="group[allowgoods]" value="1" $allowgoods_Y /> 商品帖</li>
					<li><input type="checkbox" name="group[allowdebate]" value="1" $allowdebate_Y /> 辩论帖</li>
					<li><input type="checkbox" name="group[allowmodelid]" value="1" $allowmodelid_Y /> 分类信息帖</li>
					<li><input type="checkbox" name="group[allowpcid]" value="1" $allowpcid_Y /> 团购帖</li>
					<li><input type="checkbox" name="group[allowactivity]" value="1" $allowactivity_Y /> 活动帖</li>
				</ul>
				<div style="height:1px;background:#dde9f5;overflow:hidden;margin:5px 0;"></div>
				<ul class="list_120 cc">
					<li><input type="checkbox" name="group[robbuild]" value="1" $robbuild_Y /> 抢楼帖</li>
					<li><input type="checkbox" name="group[htmlcode]" value="1" $htmlcode_Y /> html帖</li>
					<li><input type="checkbox" name="group[anonymous]" value="1" $anonymous_Y /> 匿名帖</li>
					<li><input type="checkbox" name="group[allowhidden]" value="1" $allowhidden_Y /> 隐藏帖</li>
					<li><input type="checkbox" name="group[allowsell]" value="1" $allowsell_Y /> 出售帖</li>
					<li><input type="checkbox" name="group[allowencode]" value="1" $allowencode_Y /> 加密帖</li>
				</ul>'
		),
		'allowrp'	=> array(
			'title'	=> '回复主题',
			'desc'	=> '开启后，此用户组的用户可以回复主题',
			'html'	=> '<ul class="list_A list_80 cc"><li><input type="radio" value="1" name="group[allowrp]" $allowrp_Y />开启</li><li><input type="radio" value="0" name="group[allowrp]" $allowrp_N />关闭</li></ul>'
		),
		/*'allownewvote'	=> array(
			'title'	=> '发起投票',
			'desc'	=> '开启后，此用户组的用户可以发起投票',
			'html'	=> '<ul class="list_A list_80 cc"><li><input type="radio" value="1" name="group[allownewvote]" $allownewvote_Y />开启</li><li><input type="radio" value="0" name="group[allownewvote]" $allownewvote_N />关闭</li></ul>'
		),
		'modifyvote'	=> array(
			'title'	=> '修改发起的投票选项',
			'desc'	=> '开启后，此用户组的用户有权限修改发起的投票选项',
			'html'	=> '<ul class="list_A list_80 cc"><li><input type="radio" value="1" name="group[modifyvote]" $modifyvote_Y />开启</li><li><input type="radio" value="0" name="group[modifyvote]" $modifyvote_N />关闭</li></ul>'
		),*/
		'allowvote'	=> array(
			'title' => '参与投票',
			'desc'	=> '开启后，此用户组的用户可以参与投票',
			'html'	=> '<ul class="list_A list_80 cc"><li><input type="radio" value="1" name="group[allowvote]" $allowvote_Y />开启</li><li><input type="radio" value="0" name="group[allowvote]" $allowvote_N />关闭</li></ul>'
		),
		'viewvote'	=> array(
			'title'	=> '查看投票用户',
			'desc'	=> '开启后，此用户组的用户可以查看投票用户',
			'html'	=> '<ul class="list_A list_80 cc"><li><input type="radio" value="1" name="group[viewvote]" $viewvote_Y />开启</li><li><input type="radio" value="0" name="group[viewvote]" $viewvote_N />关闭</li></ul>'
		),
		'leaveword'	=>	array(
			'title'	=> '楼主留言',
			'desc'	=> '开启后，此用户组的用户可使用楼主留言功能',
			'html'	=> '<ul class="list_A list_80 cc"><li><input type="radio" value="1" name="group[leaveword]" $leaveword_Y />开启</li><li><input type="radio" value="0" name="group[leaveword]" $leaveword_N />关闭</li></ul>'
		),
		'dig'	=> array(
			'title'	=> '推荐帖子',
			'desc'	=> '开启后，此用户组的用户可以推荐帖子',
			'html'	=> '<ul class="list_A list_80 cc"><li><input type="radio" value="1" name="group[dig]" $dig_Y />开启</li><li><input type="radio" value="0" name="group[dig]" $dig_N />关闭</li></ul>'
		),
		/*'allowactive'	=> array(
			'title'	=> '发活动帖',
			'html'	=> '<ul class="list_A list_80 cc"><li><input type="radio" value="1" name="group[allowactive]" $allowactive_Y />开启</li><li><input type="radio" value="0" name="group[allowactive]" $allowactive_N />关闭</li></ul>'
		),*/
		/*'allowreward'	=> array(
			'title'	=> '悬赏帖',
			'desc'	=> '开启后，此用户组的用户可以发表悬赏帖',
			'html'	=> '<ul class="list_A list_80 cc"><li><input type="radio" value="1" name="group[allowreward]" $allowreward_Y />开启</li><li><input type="radio" value="0" name="group[allowreward]" $allowreward_N />关闭</li></ul>'
		),
		'allowgoods'	=> array(
			'title'	=> '商品帖',
			'desc'	=> '开启后，此用户组的用户可以发表商品帖',
			'html'	=> '<ul class="list_A list_80 cc"><li><input type="radio" value="1" name="group[allowgoods]" $allowgoods_Y />开启</li><li><input type="radio" value="0" name="group[allowgoods]" $allowgoods_N />关闭</li></ul>'
		),
		'allowdebate'	=> array(
			'title'	=> '辩论帖',
			'desc'	=> '开启后，此用户组的用户可以发表辩论帖',
			'html'	=> '<ul class="list_A list_80 cc"><li><input type="radio" value="1" name="group[allowdebate]" $allowdebate_Y />开启</li><li><input type="radio" value="0" name="group[allowdebate]" $allowdebate_N />关闭</li></ul>'
		),
		'allowmodelid'	=> array(
			'title'	=> '分类信息帖',
			'desc'	=> '开启后，此用户组的用户可以发表分类信息帖',
			'html'	=> '<ul class="list_A list_80 cc"><li><input type="radio" value="1" name="group[allowmodelid]" $allowmodelid_Y />开启</li><li><input type="radio" value="0" name="group[allowmodelid]" $allowmodelid_N />关闭</li></ul>'
		),
		'allowpcid'	=> array(
			'title'	=> '团购帖',
			'desc'	=> '开启后，此用户组的用户可以发表团购帖',
			'html'	=> '<ul class="list_A list_80 cc"><li><input type="radio" name="group[allowpcid]" value="1" $allowpcid_Y/>开启</li><li><input type="radio" name="group[allowpcid]" value="0" $allowpcid_N />关闭</li>'
			//<li><input type="checkbox" name="group[allowpcid][]" value="2" $allowpcid_sel[2] />活动</li></ul>'
		),
		'allowactivity'	=> array(
			'title'	=> '活动帖',
			'desc'	=> '开启后，此用户组的用户可以发表活动帖',
			'html'	=> '<ul class="list_A list_80 cc"><li><input type="radio" value="1" name="group[allowactivity]" $allowactivity_Y />开启</li><li><input type="radio" value="0" name="group[allowactivity]" $allowactivity_N />关闭</li></ul>'
		),
		'robbuild'	=> array(
			'title'	=> '抢楼帖',
			'desc'	=> '开启后，此用户组的用户可以发表抢楼帖',
			'html'	=> '<ul class="list_A list_80 cc"><li><input type="radio" value="1" name="group[robbuild]" $robbuild_Y />开启</li><li><input type="radio" value="0" name="group[robbuild]" $robbuild_N />关闭</li></ul>'
		),
		'htmlcode'	=> array(
			'title'	=> '发表html帖',
			'desc'	=> '这将使用户拥有直接编辑 html 源代码的权利!',
			'html'	=> '<ul class="list_A list_80 cc"><li><input type="radio" value="1" name="group[htmlcode]" $htmlcode_Y />开启</li><li><input type="radio" value="0" name="group[htmlcode]" $htmlcode_N />关闭</li></ul>'
		),
		'allowhidden'	=> array(
			'title'	=> '隐藏帖',
			'desc'	=> '注：此设置同时受版块权限限制，开启版块权限中的发表隐藏帖功能方有效',
			'html'	=> '<ul class="list_A list_80 cc"><li><input type="radio" value="1" name="group[allowhidden]" $allowhidden_Y />开启</li><li><input type="radio" value="0" name="group[allowhidden]" $allowhidden_N />关闭</li></ul>'
		),
		'allowsell'	=> array(
			'title'	=> '出售帖',
			'desc'	=> '注：此设置同时受版块权限限制，开启版块权限中的发表出售帖功能方有效',
			'html'	=> '<ul class="list_A list_80 cc"><li><input type="radio" value="1" name="group[allowsell]" $allowsell_Y />开启</li><li><input type="radio" value="0" name="group[allowsell]" $allowsell_N />关闭</li></ul>'
		),
		'allowencode'	=> array(
			'title'	=> '加密帖',
			'desc'	=> '注：此设置同时受版块权限限制，开启版块权限中的发表加密帖功能方有效',
			'html'	=> '<ul class="list_A list_80 cc"><li><input type="radio" value="1" name="group[allowencode]" $allowencode_Y />开启</li><li><input type="radio" value="0" name="group[allowencode]" $allowencode_N />关闭</li></ul>'
		),
		'anonymous'	=> array(
			'title'	=> '匿名帖',
			'desc'	=> '注：此设置同时受版块权限限制，开启版块权限中的发表匿名帖功能方有效',
			'html'	=> '<ul class="list_A list_80 cc"><li><input type="radio" value="1" name="group[anonymous]" $anonymous_Y />开启</li><li><input type="radio" value="0" name="group[anonymous]" $anonymous_N />关闭</li></ul>'
		),*/
		
		'allowdelatc'	=> array(
			'title'	=> '删除自己的帖子',
			'desc'	=> '开启后，此用户组的用户可以删除自己的帖子',
			'html'	=> '<ul class="list_A list_80 cc"><li><input type="radio" value="1" name="group[allowdelatc]" $allowdelatc_Y />开启</li><li><input type="radio" value="0" name="group[allowdelatc]" $allowdelatc_N />关闭</li></ul>'
		),
		'atccheck'	=> array(
			'title'	=> '帖子需审核',
			'desc'	=> '开启版块帖子审核时,发表的帖子是否需要管理员审核，此项只有在开启版块帖子审核时有效',
			'html'	=> '<ul class="list_A list_80 cc"><li><input type="radio" value="1" name="group[atccheck]" $atccheck_Y />开启</li><li><input type="radio" value="0" name="group[atccheck]" $atccheck_N />关闭</li></ul>'
		),
		'allowreplyreward' => array(
			'title'	=> '允许设置回帖奖励',
			'desc'	=> '允许此用户组用户在发帖时给回复者一定的积分奖励',
			'html'	=> '<ul class="list_A list_80 cc"><li><input type="radio" value="1" name="group[allowreplyreward]" $allowreplyreward_Y />开启</li><li><input type="radio" value="0" name="group[allowreplyreward]" $allowreplyreward_N />关闭</li></ul>'
		),
		'allowremotepic' => array(
			'title'	=> '允许下载远程图片',
			'desc'	=> '允许此用户组用户在发帖时将远程图片本地化',
			'html'	=> '<ul class="list_A list_80 cc"><li><input type="radio" value="1" name="group[allowremotepic]" $allowremotepic_Y />开启</li><li><input type="radio" value="0" name="group[allowremotepic]" $allowremotepic_N />关闭</li></ul>'
		),
		'allowat'	=> array(
			'title'	=> '允许发帖时@其他人',
			'desc'	=> '开启后，此用户组的用户在发帖时可以@提到他人',
			'html'	=> '<ul class="list_A list_80 cc"><li><input type="radio" value="1" name="group[allowat]" $allowat_Y />开启</li><li><input type="radio" value="0" name="group[allowat]" $allowat_N />关闭</li></ul>'
		),
		'atnum'	=> array(
			'title'	=> '发帖时可@用户数',
			'desc'	=> '发帖时@用户数上限，0或留空表示不限制',
			'html'	=> '<input class="input input_wa" value="$atnum" name="group[atnum]" />'
		),
		'postlimit'	=> array(
			'title'	=> '每日最多发帖数',
			'desc'	=> '0或留空表示不限制',
			'html'	=> '<input class="input input_wa" value="$postlimit" name="group[postlimit]" />'
		),
		'allowbuykmd' => array(
			'title'	=> '允许购买孔明灯',
			'desc'	=> '',
			'html'	=> '<ul class="list_A list_80 cc"><li><input type="radio" value="1" name="group[allowbuykmd]" $allowbuykmd_Y />开启</li><li><input type="radio" value="0" name="group[allowbuykmd]" $allowbuykmd_N />关闭</li></ul>'
		),
		'postpertime'	=> array(
			'title'	=> '连续发帖时间控制（秒）',
			'desc'	=> '设定的时间间隔内用户不可连续发帖，0或留空表示不限制，此功能原名为：灌水预防',
			'html'	=> '<input class="input input_wa" value="$postpertime" name="group[postpertime]" />'
		),
		'edittime'	=> array(
			'title'	=> '可编辑时间控制（分钟）',
			'desc'	=> '用户发帖成功后，可以在设定的时间段内重新编辑帖子，0或留空表示不限制，此功能原名为：编辑时间约束[分钟]',
			'html'	=> '<input class="input input_wa" value="$edittime" name="group[edittime]" />'
		),
		//增加链接帖发帖限制@modify panjl@2010-11-2
		'posturlnum'	=> array(
			'title'	=> '链接帖发帖数控制',
			'desc'	=> '发帖数达到设定之后才可以发表带链接的帖子，0或留空表示不限制，此设置可防止注册机注册后发布带链接的广告帖',
			'html'	=> '<input class="input input_wa" value="$posturlnum" name="group[posturlnum]" />'
		),
		'media'	=> array(
			'title'	=> '多媒体自动展开',
			'html'	=> '<ul class="list_A list_80 cc"><li><input type="checkbox" name="group[media][]" value="flash" $media_sel[flash] />flash</li><li>
			<input type="checkbox" name="group[media][]" value="wmv" $media_sel[wmv] />wmv</li><li>
			<input type="checkbox" name="group[media][]" value="rm" $media_sel[rm] />rm</li><li>
			<input type="checkbox" name="group[media][]" value="mp3" $media_sel[mp3] />mp3</li></ul>'
		),
		'markable'	=> array(
			'title'	=> '帖子评分权限',
			'desc'	=> '版主在所管理版块始终有评分权限',
			'html'	=> '<ul class="list_A cc td2_wp"><li><input type="radio" value="0" name="group[markable]" $markable_0 />无</li><li>
			<input type="radio" value="1" name="group[markable]" $markable_1 />允许评分</li><li>
			<input type="radio" value="2" name="group[markable]" $markable_2 />允许重复评分</li></ul>'
		),
		/*'maxcredit'	=> array(
			'title' => '评分上限<font color=blue> 说明：</font>每天所有积分总和允许的最大评分点数',
			'html'	=> '<input type="text" class="input input_wa" value="$maxcredit" name="group[maxcredit]" />'
		),
		'marklimit' => array(
			'title'	=> '评分限制<font color=blue> 说明：</font>每次评分的最大和最小值',
			'html'	=> '最小 <input type=text size="3" class="input" value="$minper" name="group[marklimit][0]" /> 最大 <input type=text size="3" class="input" value="$maxper" name="group[marklimit][1]" />'
		),*/
		'markset'	=> array(
			'title'	=> '帖子评分设置',
			'desc'	=> '复选框不选中或者评分上限、评分限制任何一项留空/设为0，前台将无法使用该评分类型',
			'html'	=> $credit_type
		)
		/*'markdt'	=> array(
			'title'	=> '评分是否需要扣除自身相应的积分',
			'html'	=> '<ul class="list_A list_80 cc"><li><input type="radio" value="1" name="group[markdt]" $markdt_Y />开启</li><li><input type="radio" value="0" name="group[markdt]" $markdt_N />关闭</li></ul>'
		),
		'postpertime'	=> array(
			'title'	=> '灌水预防',
			'desc'	=> '多少秒间隔内不能发帖，0或留空表示不限制',
			'html'	=> '<input class="input input_wa" value="$postpertime" name="group[postpertime]" />'
		),
		'edittime'	=> array(
			'title'	=> '编辑时间约束[分钟]',
			'desc'	=> '超过设定时间后拒绝用户编辑。0或留空表示不限制',
			'html'	=> '<input class="input input_wa" value="$edittime" name="group[edittime]" />'
		),
		//增加链接帖发帖限制@modify panjl@2010-11-2
		'posturlnum'	=> array(
			'title'	=> '链接帖发帖限制',
			'desc'	=> '当会员的发表的帖子数量达到设定值以后就可以发表带链接的帖子。此设置为防止注册机注册后发带有链接地址的广告。0或留空表示不限制',
			'html'	=> '<input class="input input_wa" value="$posturlnum" name="group[posturlnum]" />'
		)*/
	),
	'group' =>array(
		'allowcreate'=>array(
			'title'=>'允许创建群组个数',
			'desc' =>"0 或 留空 表示没限制，需要在群组基础设置中开启<a href=\"$admin_file?adminjob=apps&admintype=groups_set\">允许创建新群组</a>才有效",
			'html'=>'<input size="35" class="input" value="$allowcreate" name="group[allowcreate]" />'
		),
		'allowjoin'=>array(
			'title'=>'允许加入群组个数',
			'desc' =>'0 或 留空 表示没限制',
			'html'=>'<input size="35" class="input" value="$allowjoin" name="group[allowjoin]" />'
		)
	),
	'att'	=> array(
		'allowupload'	=> array(
			'title'	=> '上传附件权限',
			'desc'	=> '可在版块设置处设置上传附件“奖励或扣除”积分',
			'html'	=> '<ul class="list_A"><li><input type="radio" value="0" name="group[allowupload]" $allowupload_0 />不允许上传附件</li><li><input type="radio" value="1" name="group[allowupload]" $allowupload_1 />允许上传附件，按照版块设置奖励或扣除积分</li><li><input type="radio" value="2" name="group[allowupload]" $allowupload_2 />允许上传附件，不奖励或扣除积分</li></ul>'
		),
		'allowdownload'	=> array(
			'title'	=> '下载附件权限',
			'desc'	=> '可在版块设置处设置下载附件“奖励或扣除”积分',
			'html'	=> '<ul class="list_A"><li><input type="radio" value="0" name="group[allowdownload]" $allowdownload_0 />不允许下载附件</li><li><input type="radio" value="1" name="group[allowdownload]" $allowdownload_1 />允许下载附件，按照版块设置奖励或扣除积分</li><li><input type="radio" value="2" name="group[allowdownload]" $allowdownload_2 />允许下载附件，不奖励或扣除积分</li></ul>'
		),
		'allownum'	=> array(
			'title'	=> '一天最多上传附件个数',
			'html'	=> '<input class="input input_wa" value="$allownum" name="group[allownum]" />'
		),
		'uploadtype'	=> array(
			'title'	=> '附件上传的后缀和尺寸',
			'desc'	=> "<font color=\"red\">系统限制上传附件最大尺寸为{$maxuploadsize},</font>留空则使用站点全局中的设置",
			'html'	=> '<div class="admin_table_b"><table cellpadding="0" cellspacing="0">
				<tbody id="mode" style="display:none"><tr>
					<td><input class="input input_wc" name="filetype[]" value=""></td>
					<td><input class="input input_wc" name="maxsize[]" value=""></td><td><a href="javascript:;" onclick="removecols(this);">[删除]</a></td>
				</tr></tbody>
				<tr>
					<td>后缀名(<b>小写</b>)</td>
					<td>最大尺寸(KB)</td>
					<td><a href="javascript:;" class="s3" onclick="addcols(\'mode\',\'ft\');">[添加]</a></td>
				</tr>
				{$upload_type}
				<tbody id="ft"></tbody>
			</table></div>
			<script type="text/javascript">
			addcols(\'mode\',\'ft\');
			</script>'
		)
	),
	'special' => array(
		'allowbuy'	=> array(
			'title'	=> '允许购买',
			'desc'	=> "开启该功能后，需同时到<a href=\"$admin_file?adminjob=plantodo\"><font color=\"blue\">计划任务</font></a>开启“限期头衔自动回收”功能.",
			'html'	=> '<ul class="list_A list_80 cc"><li><input type="radio" value="1" name="group[allowbuy]" $allowbuy_Y />开启</li><li><input type="radio" value="0" name="group[allowbuy]" $allowbuy_N />关闭</li></ul>'
		),
		'selltype'	=> array(
			'title'	=> '特殊组购买币种',
			'html'	=> '<select name="group[selltype]" class="select_wa">$special_type</select>'
		),
		'sellprice'	=> array(
			'title'	=> '每日价格[积分]',
			'html'	=> '<input type="text" class="input input_wa" name="group[sellprice]" value="$sellprice" />'
		),
		'rmbprice'	=> array(
			'title'	=> '每日价格[现金]',
			'desc'	=> '设置此价格，将允许通过网上支付来购买',
			'html'	=> '<input type="text" class="input input_wa" name="group[rmbprice]" value="$rmbprice" />'
		),
		'selllimit'	=> array(
			'title'	=> '购买特殊组天数限制',
			'html'	=> '<input type="text" class="input input_wa" name="group[selllimit]" value="$selllimit" />'
		),
		'sellinfo'	=> array(
			'title'	=> '特殊组描述',
			'desc'	=> '可以填写购买说明和该用户组拥有的特殊权限',
			'html'	=> '<textarea name="group[sellinfo]" class="textarea">$sellinfo</textarea>'
		)
	),
	'system'	=> array(
		'superright' => array(
			'title'	=> '超级管理权限',
			'desc'	=> "<font color=\"red\">是</font>：表明以下针对版块的权限设置对所有版块生效（例如：管理员）<br /><font color=\"red\">否</font>：表明以下针对版块的权限设置对所有版块无效，此时如果要配置单个版块的管理权限，需要到<a href=\"$admin_file?adminjob=singleright\">版块用户权限</a>里进行配置<br />（注：当编辑版主权限时，开启则版主在所有版块都拥有权限，关闭则版主只在本版块拥有权限）",
			'html'	=> '<ul class="list_A list_80 cc"><li><input type="radio" value="1" name="group[superright]" $superright_Y />开启</li><li><input type="radio" value="0" name="group[superright]" $superright_N />关闭</li></ul>'
		),
		'enterreason' => array(
			'title' => '强制输入操作原因',
			'desc' => '开启后，前台管理的所有操作都必须输入操作原因。可避免由于管理操作上的不透明而引起站点会员纠纷',
			'html'	=> '<ul class="list_A list_80 cc"><li><input type="radio" value="1" name="group[enterreason]" $enterreason_Y />开启</li><li><input type="radio" value="0" name="group[enterreason]" $enterreason_N />关闭</li></ul>'
		),
		'colonyright' => array(
			'title'	=> '群组管理权限',
			'desc'	=> "开启了此权限，此类用户将具备任意群组管理员的管理权限",
			'html'	=> '<ul class="list_A list_80 cc"><li><input type="radio" value="1" name="group[colonyright]" $colonyright_Y />开启</li><li><input type="radio" value="0" name="group[colonyright]" $colonyright_N />关闭</li></ul>'
		),
		'forumcolonyright' => array(
			'title'	=> '关联版块群组管理权限',
			'desc'	=> "开启了此权限，此类用户将具备关联板块中群组的管理权限",
			'html'	=> '<ul class="list_A list_80 cc"><li><input type="radio" value="1" name="group[forumcolonyright]" $forumcolonyright_Y />开启</li><li><input type="radio" value="0" name="group[forumcolonyright]" $forumcolonyright_N />关闭</li></ul>'
		)
	),
	'systemforum' => array(
		'posthide'	=> array(
			'title'	=> '查看隐藏帖',
			'html'	=> '<ul class="list_A list_80 cc"><li><input type="radio" value="1" name="group[posthide]" $posthide_Y />开启</li><li><input type="radio" value="0" name="group[posthide]" $posthide_N />关闭</li></ul>'
		),
		'sellhide'	=> array(
			'title'	=> '查看出售帖',
			'html'	=> '<ul class="list_A list_80 cc"><li><input type="radio" value="1" name="group[sellhide]" $sellhide_Y />开启</li><li><input type="radio" value="0" name="group[sellhide]" $sellhide_N />关闭</li></ul>'
		),
		'encodehide'	=> array(
			'title'	=> '查看加密帖',
			'html'	=> '<ul class="list_A list_80 cc"><li><input type="radio" value="1" name="group[encodehide]" $encodehide_Y />开启</li><li><input type="radio" value="0" name="group[encodehide]" $encodehide_N />关闭</li></ul>'
		),
		'anonyhide'	=> array(
			'title'	=> '查看匿名帖',
			'html'	=> '<ul class="list_A list_80 cc"><li><input type="radio" value="1" name="group[anonyhide]" $anonyhide_Y />开启</li><li><input type="radio" value="0" name="group[anonyhide]" $anonyhide_N />关闭</li></ul>'
		),
		'activitylist'=> array(
			'title'	=> '管理活动报名列表',
			'desc'	=> '开启之后有查看报名列表、导出列表、群发短信等操作权限',
			'html'	=> '<ul class="list_A list_80 cc"><li><input type="radio" value="1" name="group[activitylist]" $activitylist_Y />开启</li><li><input type="radio" value="0" name="group[activitylist]" $activitylist_N />关闭</li></ul>'
		),
		'postpers'	=> array(
			'title'	=> '灌水',
			'desc'	=> '不受灌水时间限制',
			'html'	=> '<ul class="list_A list_80 cc"><li><input type="radio" value="1" name="group[postpers]" $postpers_Y />开启</li><li><input type="radio" value="0" name="group[postpers]" $postpers_N />关闭</li></ul>'
		),
		'replylock'	=> array(
			'title'	=> '回复锁定帖',
			'html'	=> '<ul class="list_A list_80 cc"><li><input type=radio value=1 $replylock_Y name=group[replylock]>开启</li><li><input type=radio value=0 $replylock_N name=group[replylock]>关闭</li></ul>'
		),
		'viewip'	=> array(
			'title'	=> '查看IP',
			'desc'	=> '浏览帖子时显示,管理员将不受该功能限制',
			'html'	=> '<ul class="list_A list_80 cc"><li><input type="radio" value="1" name="group[viewip]" $viewip_Y />开启</li><li><input type="radio" value="0" name="group[viewip]" $viewip_N />关闭</li></ul>'
		),
		'topped'	=> array(
			'title'	=> '置顶权限',
			'html'	=> '<ul class="list_A"><li><input type="radio" value="0" name="group[topped]" $topped_0 />无</li><li>
			<input type="radio" value="1" name="group[topped]" $topped_1 />版块置顶</li><li>
			<input type="radio" value="2" name="group[topped]" $topped_2 />版块置顶,分类置顶</li><li>
			<input type="radio" value="3" name="group[topped]" $topped_3 />版块置顶,分类置顶,总置顶</li><li>
			<input type="radio" value="4" name="group[topped]" $topped_4 />任意版块置顶</li></ul>'
		),
		'replayorder' => array(
			'title'	  => '帖子回复显示顺序',
			'desc'    => '开启后，在编辑帖子时，用户能够设置帖子回复的显示顺序',
			'html'	  => '<ul class="list_A list_80 cc"><li><input type="radio" value="1" name="group[replayorder]" $replayorder_Y />开启</li><li><input type="radio" value="0" name="group[replayorder]" $replayorder_N />关闭</li></ul>',
		),
		'digestadmin'	=> array(
			'title'	=> '前台精华',
			'html'	=> '<ul class="list_A list_80 cc"><li><input type="radio" value="1" name="group[digestadmin]" $digestadmin_Y />开启</li><li><input type="radio" value="0" name="group[digestadmin]" $digestadmin_N />关闭</li></ul>'
		),
		'lockadmin'	=> array(
			'title'	=> '前台锁定',
			'html'	=> '<ul class="list_A list_80 cc"><li><input type="radio" value="1" name="group[lockadmin]" $lockadmin_Y />开启</li><li><input type="radio" value="0" name="group[lockadmin]" $lockadmin_N />关闭</li></ul>'
		),
		'pushadmin'	=> array(
			'title'	=> '前台提前',
			'html'	=> '
			<ul class="list_A list_80 cc fl mr20"><li><input type="radio" value="1" name="group[pushadmin]" $pushadmin_Y />开启</li><li><input type="radio" value="0" name="group[pushadmin]" $pushadmin_N />关闭</li></ul>'
		),
		'pushtime'	=> array(
			'title'	=> '提前时间上限[小时]',
			'desc'	=> '留空或0表示不限制',
			'html'	=> '<input type="text" value="$pushtime" name="group[pushtime]" class="input input_wa" />'
		),
		'coloradmin'	=> array(
			'title'	=> '前台加亮',
			'html'	=> '<ul class="list_A list_80 cc"><li><input type="radio" value="1" name="group[coloradmin]" $coloradmin_Y />开启</li><li><input type="radio" value="0" name="group[coloradmin]" $coloradmin_N />关闭</li></ul>'
		),
		'downadmin'	=> array(
			'title'	=> '前台压帖',
			'html'	=> '<ul class="list_A list_80 cc"><li><input type="radio" value="1" name="group[downadmin]" $downadmin_Y />开启</li><li><input type="radio" value="0" name="group[downadmin]" $downadmin_N />关闭</li></ul>'
		),
		'replaytopped' => array(
			'title'    => '前台帖内置顶',
			'html'	   => '<ul class="list_A list_80 cc"><li><input type="radio" value="1" name="group[replaytopped]" $replaytopped_Y />开启</li><li><input type="radio" value="0" name="group[replaytopped]" $replaytopped_N />关闭</li></ul>',
		),
		'tpctype'	=> array(
			'title'	=> '主题分类管理',
			'desc'	=> '<font color=blue> 说明：</font>主题分类批量管理权限',
			'html'	=> '<ul class="list_A list_80 cc"><li><input type="radio" value="1" name="group[tpctype]" $tpctype_Y />开启</li><li><input type="radio" value="0" name="group[tpctype]" $tpctype_N />关闭</li></ul>'
		),
		'tpccheck'	=> array(
			'title'	=> '主题验证管理',
			'desc'	=> '<font color=blue> 说明：</font>前台主题验证管理权限',
			'html'	=> '<ul class="list_A list_80 cc"><li><input type="radio" value="1" name="group[tpccheck]" $tpccheck_Y />开启</li><li><input type="radio" value="0" name="group[tpccheck]" $tpccheck_N />关闭</li></ul>'
		),
		'delatc'	=> array(
			'title'	=> '批量删除主题',
			'desc'	=> '<font color=blue> 说明：</font>前台帖子管理权限',
			'html'	=> '<ul class="list_A list_80 cc"><li><input type="radio" value="1" name="group[delatc]" $delatc_Y />开启</li><li><input type="radio" value="0" name="group[delatc]" $delatc_N />关闭</li></ul>'
		),
		'moveatc'	=> array(
			'title'	=> '批量移动帖子',
			'desc'	=> '<font color=blue> 说明：</font>前台帖子管理权限',
			'html'	=> '<ul class="list_A list_80 cc"><li><input type="radio" value="1" name="group[moveatc]" $moveatc_Y />开启</li><li><input type="radio" value="0" name="group[moveatc]" $moveatc_N />关闭</li></ul>'
		),
		'copyatc'	=> array(
			'title'	=> '批量复制帖子',
			'desc'	=> '<font color=blue> 说明：</font>前台帖子管理权限',
			'html'	=> '<ul class="list_A list_80 cc"><li><input type="radio" value="1" name="group[copyatc]" $copyatc_Y />开启</li><li><input type="radio" value="0" name="group[copyatc]" $copyatc_N />关闭</li></ul>'
		),
		'modother'	=> array(
			'title'	=> '删除单一帖子[包括回复]',
			'html'	=> '<ul class="list_A list_80 cc"><li><input type="radio" value="1" name="group[modother]" $modother_Y />开启</li><li><input type="radio" value="0" name="group[modother]" $modother_N />关闭</li></ul>'
		),
		'deltpcs'	=> array(
			'title'	=> '编辑用户帖子',
			'html'	=> '<ul class="list_A list_80 cc"><li><input type="radio" value="1" name="group[deltpcs]" $deltpcs_Y />开启</li><li><input type="radio" value="0" name="group[deltpcs]" $deltpcs_N />关闭</li></ul>'
		),
		'viewcheck'	=> array(
			'title'	=> '查看需要验证的帖子',
			'html'	=> '<ul class="list_A list_80 cc"><li><input type="radio" value="1" name="group[viewcheck]" $viewcheck_Y />开启</li><li><input type="radio" value="0" name="group[viewcheck]" $viewcheck_N />关闭</li></ul>'
		),
		'viewclose'	=> array(
			'title'	=> '查看关闭帖子',
			'html'	=> '<ul class="list_A list_80 cc"><li><input type="radio" value="1" name="group[viewclose]" $viewclose_Y />开启</li><li><input type="radio" value="0" name="group[viewclose]" $viewclose_N />关闭</li></ul>'
		),
		'delattach'	=> array(
			'title'	=> '删除附件',
			'html'	=> '<ul class="list_A list_80 cc"><li><input type="radio" value="1" name="group[delattach]" $delattach_Y />开启</li><li><input type="radio" value="0" name="group[delattach]" $delattach_N />关闭</li></ul>'
		),
		'shield'	=> array(
			'title'	=> '屏蔽单一主题',
			'html'	=> '<ul class="list_A list_80 cc"><li><input type="radio" value="1" name="group[shield]" $shield_Y />开启</li><li><input type="radio" value="0" name="group[shield]" $shield_N />关闭</li></ul>'
		),
		'unite'	=> array(
			'title'	=> '合并主题',
			'html'	=> '<ul class="list_A list_80 cc"><li><input type="radio" value="1" name="group[unite]" $unite_Y />开启</li><li><input type="radio" value="0" name="group[unite]" $unite_N />关闭</li></ul>'
		),
		'split'	=> array(
			'title'	=> '拆分帖子',
			'html'	=> '<ul class="list_A list_80 cc"><li><input type="radio" value="1" name="group[split]" $split_Y />开启</li><li><input type="radio" value="0" name="group[split]" $split_N />关闭</li></ul>'
		),
		'remind'	=> array(
			'title'	=> '帖子管理提醒',
			'html'	=> '<ul class="list_A list_80 cc"><li><input type="radio" value="1" name="group[remind]" $remind_Y />开启</li><li><input type="radio" value="0" name="group[remind]" $remind_N />关闭</li></ul>'
		),
		'pingcp'	=> array(
			'title'	=> '管理评分记录',
			'html'	=> '<ul class="list_A list_80 cc"><li><input type="radio" value="1" name="group[pingcp]" $pingcp_Y />开启</li><li><input type="radio" value="0" name="group[pingcp]" $pingcp_N />关闭</li></ul>'
		),
		'inspect'	=> array(
			'title'	=> '版主标记已阅读',
			'desc'	=> '<font color=blue> 说明：</font>需在版块基本权限里开启后方可用',
			'html'	=> '<ul class="list_A list_80 cc"><li><input type="radio" value="1" name="group[inspect]" $inspect_Y />开启</li><li><input type="radio" value="0" name="group[inspect]" $inspect_N />关闭</li></ul>'
		),
		'allowtime'	=> array(
			'title'	=> '不受版块发帖时间域限制',
			'html'	=> '<ul class="list_A list_80 cc"><li><input type="radio" value="1" name="group[allowtime]" $allowtime_Y />开启</li><li><input type="radio" value="0" name="group[allowtime]" $allowtime_N />关闭</li></ul>'
		),
		'banuser'	=> array(
			'title'	=> '禁言用户的权限',
			'desc'	=> '<font color="blue">说明:</font><br />
			<font color="red">无禁言权限:</font>该用户组无权限对会员进行禁言操作<br />
			<font color="red">所有版块:</font>(有禁言权限)并且被禁言会员在所有版块中都没权限发言<br />
			<font color="red">单一版块</font>(有禁言权限)并且被禁言会员在帖子所在版块没权限发言,而在其他版块中可以发言',
			'html'	=> '<ul class="list_A"><li><input type="radio" value="0" name="group[banuser]" $banuser_0 />无禁言权限</li><li><input type="radio" value="1" name="group[banuser]" $banuser_1 />单一版块</li><li><input type="radio" value="2" name="group[banuser]" $banuser_2 />所有版块</li></ul>'
		),
		'bantype'	=> array(
			'title'	=> '永久禁言用户',
			'html'	=> '<ul class="list_A list_80 cc"><li><input type="radio" value="1" name="group[bantype]" $bantype_Y />开启</li><li><input type="radio" value="0" name="group[bantype]" $bantype_N />关闭</li></ul>'
		),
		'banmax'	=> array(
			'title'	=> '禁言时间限制',
			'desc'	=> '<font color=blue> 说明：</font>禁言会员的最大天数',
			'html'	=> '<input type=text class="input input_wa" value="$banmax" name="group[banmax]" />'
		),
		'banuserip' => array(
			'title' => '禁止ip',
			'desc'  => '开启后，拥有禁止ip权限',
			'html'	=> '<ul class="list_A list_80 cc"><li><input type="radio" value="1" name="group[banuserip]" $banuserip_Y />开启</li><li><input type="radio" value="0" name="group[banuserip]" $banuserip_N />关闭</li></ul>'
		),
		'banadmin'	=> array(
			'title'	=> '可禁言管理组',
			'desc'	=> '<font color=blue> 说明：</font>开启后，可以禁言所有用户组，包括系统组和特殊用户组',
			'html'	=> '<ul class="list_A list_80 cc"><li><input type="radio" value="1" name="group[banadmin]" $banadmin_Y />开启</li><li><input type="radio" value="0" name="group[banadmin]" $banadmin_N />关闭</li></ul>'
		),
		'bansignature' => array(
			'title' => '禁止帖子签名',
			'desc'  => '开启后，拥有禁止帖子签名权限',
			'html'	=> '<ul class="list_A list_80 cc"><li><input type="radio" value="1" name="group[bansignature]" $bansignature_Y />开启</li><li><input type="radio" value="0" name="group[bansignature]" $bansignature_N />关闭</li></ul>'
		),
		'areapush'	=> array(
			'title'	=> '门户推送',
			'desc'	=> '<font color=blue> 说明：</font>是否有向门户模式的首页和频道页推送帖子的权限',
			'html'	=> '<ul class="list_A list_80 cc"><li><input type="radio" value="1" name="group[areapush]" $areapush_Y />开启</li><li><input type="radio" value="0" name="group[areapush]" $areapush_N />关闭</li></ul>'
		),
		'overprint'	=> array(
			'title'	=> '帖子印戳',
			'desc'	=> '<font color=blue> 说明：</font>是否有给帖子加印戳效果的权限',
			'html'	=> '<ul class="list_A list_80 cc"><li><input type="radio" value="1" name="group[overprint]" $overprint_Y />开启</li><li><input type="radio" value="0" name="group[overprint]" $overprint_N />关闭</li></ul>'
		),
		'tcanedit'	=> array(
			'title'	=> '可编辑管理组帖子',
			'desc'	=> '<font color=blue> 说明：</font>可编辑管理组的帖子',
			'html'	=> '<ul class="list_A list_80 cc">
			<li><label><input type="checkbox" name="group[tcanedit][]" value="3" $tcanedit_sel[3]/>' . $ltitle[3] . '</label></li>
			<li><label><input type="checkbox" name="group[tcanedit][]" value="4" $tcanedit_sel[4]/>' . $ltitle[4] . '</label></li>
			<li><label><input type="checkbox" name="group[tcanedit][]" value="5" $tcanedit_sel[5]/>' . $ltitle[5] . '</label></li></ul>'
		),
		'deldiary'	=> array(
			'title'	=> '日志删除权限',
			'desc'	=> '<font color=blue> 说明：</font>开启后可删除其他用户日志',
			'html'	=> '<ul class="list_A list_80 cc"><li><input type="radio" value="1" name="group[deldiary]" $deldiary_Y />开启</li><li><input type="radio" value="0" name="group[deldiary]" $deldiary_N />关闭</li></ul>'
		),
		'delalbum'	=> array(
			'title'	=> '相册删除权限',
			'desc'	=> '<font color=blue> 说明：</font>开启后可删除其他用户相册和照片',
			'html'	=> '<ul class="list_A list_80 cc"><li><input type="radio" value="1" name="group[delalbum]" $delalbum_Y />开启</li><li><input type="radio" value="0" name="group[delalbum]" $delalbum_N />关闭</li></ul>'
		),
		'delweibo'	=> array(
			'title'	=> '新鲜事删除权限',
			'desc'	=> '<font color=blue> 说明：</font>开启后可删除其他用户新鲜事',
			'html'	=> '<ul class="list_A list_80 cc"><li><input type="radio" value="1" name="group[delweibo]" $delweibo_Y />开启</li><li><input type="radio" value="0" name="group[delweibo]" $delweibo_N />关闭</li></ul>'
		),
		'delactive'	=> array(
			'title'	=> '活动删除权限',
			'desc'	=> '<font color=blue> 说明：</font>开启后可删除个人中心活动',
			'html'	=> '<ul class="list_A list_80 cc"><li><input type="radio" value="1" name="group[delactive]" $delactive_Y />开启</li><li><input type="radio" value="0" name="group[delactive]" $delactive_N />关闭</li></ul>'
		),
		'recommendactive'	=> array(
			'title'	=> '活动推荐权限',
			'desc'	=> '<font color=blue> 说明：</font>开启后可操作个人中心活动推荐',
			'html'	=> '<ul class="list_A list_80 cc"><li><input type="radio" value="1" name="group[recommendactive]" $recommendactive_Y />开启</li><li><input type="radio" value="0" name="group[recommendactive]" $recommendactive_N />关闭</li></ul>'
		)
	)
);
?>