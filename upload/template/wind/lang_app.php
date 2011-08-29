<?php
!function_exists('readover') && exit('Forbidden');

$lang['app'] = array (

'group'			=> '群组',
'active'		=> '活动',
'groupactive'	=> '群组活动',
'defaultalbum'	=> '默认相册',
'photo_belong'	=> '属于：',
'video'			=> '视频',
'music'			=> '音乐',
'web'			=> '网页',
'flash'			=> 'flash',
'user'			=> '用户',
'photo'			=> '照片',
'album'			=> '相册',
'reply'			=> '回复',
'house'			=> '楼盘',
'sale'          => '出售房源',
'hire'          => '出租房源',

'diary'			=> '日志',
'topic'			=> '帖子',

'video_recommend'	=> '推荐了一个视频',
'music_recommend'	=> '推荐了一个音乐',
'web_recommend'		=> '推荐了一个网页',
'flash_recommend'	=> '收藏了一个flash',

'diary_recommend'		=> '推荐了一篇日志',
'photo_recommend'		=> '推荐了一张照片',
'album_recommend'		=> '推荐了一个相册',
'group_recommend'		=> '推荐了一个群组',
'groupactive_recommend'	=> '推荐了一个群组活动',
'topic_recommend'		=> '推荐了一篇帖子',
'reply_recommend'		=> '推荐了一篇回复',
'cms_recommend'			=> '推荐了一篇文章',
'house_recommend'		=> '推荐了一个楼盘',

'weibo'			=> '新鲜事',
'multimedia'	=> '多媒体',
'cms'			=> '文章',
'postfavor'			=> '帖子',
'tucool'			=> '图酷',
'collection_type_name'	=> '[{$L[type]}] 收藏于: {$L[postdate]}',
'collection_postfavor_name'	=> '[{$L[type]}] 更新于: {$L[postdate]}',
'ajax_sendweibo_info' => '我在用户[url={$L[db_bbsurl]}/{$GLOBALS[db_userurl]}{$L[uid]}]{$L[username]}[/url]的个人空间发现这个信息，认为很有价值，特别推荐。\\n\\n主要信息:\\n{$L[title]}\\n\\n描述:\\n{$L[descrip]}\\n\\n希望你能喜欢。',
'ajax_sendweibo_groupinfo' => '我在群组“{$L[cname]}”发现这个信息，认为很有价值，特别推荐。\\n\\n主要信息:\\n{$L[title]}\\n\\n描述:\\n{$L[descrip]}\\n\\n希望你能喜欢。',
'ajax_sendweibo_cmsinfo' => '我发现了一篇文章，认为很有价值，特别推荐。\\n\\n主要信息:\\n{$L[title]}\\n\\n描述:\\n{$L[descrip]}\\n\\n希望你能喜欢。',
'ajax_sendweibo_houseinfo' => '我发现一个楼盘：{$L[title]} &nbsp;认为很有价值，特别推荐给你。\\n楼盘位置: {$L[postion]} \\n希望你能喜欢。',
);
?>