<?php
!defined('PW_UPLOAD') && exit('Forbidden');
//732 to 75
//UPDATE pw_block
$db->update("REPLACE INTO `pw_block` (`bid`, `sid`, `func`, `name`, `rang`, `cachetime`, `iflock`) VALUES
(1, 1, 'newsubject', '最新主题', '', 1800, 1),
(2, 1, 'newreply', '最新回复', '', 600, 1),
(3, 1, 'digestsubject', '精华主题', '', 86400, 1),
(4, 1, 'replysort', '回复排行', '', 86400, 1),
(5, 1, 'hitsort', '人气排行', '', 86400, 1),
(6, 2, 'usersort', '金钱排行', 'money', 3000, 1),
(7, 2, 'usersort', '威望排行', 'rvrc', 3000, 1),
(8, 3, 'forumsort', '今日发帖', 'tpost', 600, 1),
(9, 3, 'forumsort', '主题数排行', 'topic', 86400, 1),
(10, 3, 'forumsort', '发帖量排行', 'article', 86400, 1),
(11, 4, 'gettags', '热门标签', 'hot', 86400, 1),
(12, 4, 'gettags', '最新标签', 'new', 86400, 1),
(13, 5, 'newpic', '最新图片', '', 1700, 1),
(15, 6, 'hotactive', '热门活动', '', 86400, 1),
(18, 2, 'usersort', '在线时间排行', 'onlinetime', 86400, 1),
(19, 2, 'usersort', '今日发帖排行', 'todaypost', 1200, 1),
(20, 2, 'usersort', '月发帖排行', 'monthpost', 40000, 1),
(21, 2, 'usersort', '发帖排行', 'postnum', 40000, 1),
(22, 2, 'usersort', '月在线排行', 'monoltime', 40000, 1),
(23, 1, 'replysortday', '今日热帖', '', 1800, 1),
(25, 2, 'usersort', '贡献值排行', 'credit', 3000, 1),
(26, 2, 'usersort', '交易币排行榜', 'currency', 3000, 1),
(29, 1, 'highlightsubject', '加亮主题', '', 50000, 1),
(38, 6, 'todayactive', '今日活动', '', 3600, 1),
(41, 6, 'newactive', '最新活动', '', 1800, 1),
(49, 1, 'replysortweek', '近期热帖', '', 86400, 1),
(47, 1, 'hitsortday', '今日人气', '', 1800, 1)");

//update pw_stamp
$db->update("REPLACE INTO `pw_stamp` (`sid`, `name`, `stamp`, `init`, `iflock`, `iffid`) VALUES
(1, '帖子排行', 'subject', 1, 1, 1),
(2, '用户排行', 'user', 6, 1, 0),
(3, '版块排行', 'forum', 9, 1, 0),
(4, '标签排行', 'tag', 11, 1, 0),
(5, '图片', 'image', 13, 1, 1),
(6, '活动', 'active', 41, 1, 1)");

//update pw_tpltype
$db->update("REPLACE INTO `pw_tpltype` (`id`, `type`, `name`) VALUES
(1, 'subject', '帖子类'),
(2, 'image', '图片类'),
(3, 'forum', '版块类'),
(4, 'user', '用户类'),
(5, 'tag', '标签类'),
(6, 'player', '播放器')");

?>