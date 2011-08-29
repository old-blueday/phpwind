
DROP TABLE IF EXISTS pw_actattachs;
CREATE TABLE pw_actattachs (
  aid mediumint(8) unsigned NOT NULL auto_increment,
  uid int(10) unsigned NOT NULL default '0',
  actid int(10) unsigned NOT NULL default '0',
  name varchar(255) NOT NULL default '',
  type varchar(30) NOT NULL default '',
  size int(10) unsigned NOT NULL default '0',
  attachurl varchar(255) NOT NULL default '',
  hits mediumint(8) unsigned NOT NULL default '0',
  uploadtime int(10) NOT NULL default '0',
  descrip varchar(100) NOT NULL default '',
  ifthumb tinyint(3) NOT NULL default '0',
  PRIMARY KEY  (aid),
  KEY idx_actid (actid)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS pw_actions;
CREATE TABLE pw_actions (
  id smallint(6) unsigned NOT NULL auto_increment,
  images varchar(15) NOT NULL default '',
  name varchar(15) NOT NULL default '',
  descrip varchar(100) NOT NULL default '',
  PRIMARY KEY  (id)
) TYPE=MyISAM;
INSERT INTO pw_actions(images,name,descrip) VALUES ('1.gif', '{#action_1}', '{#act_1}');
INSERT INTO pw_actions(images,name,descrip) VALUES ('2.gif', '{#action_2}', '{#act_2}');
INSERT INTO pw_actions(images,name,descrip) VALUES ('3.gif', '{#action_3}', '{#act_3}');
INSERT INTO pw_actions(images,name,descrip) VALUES ('4.gif', '{#action_4}', '{#act_4}');
INSERT INTO pw_actions(images,name,descrip) VALUES ('5.gif', '{#action_5}', '{#act_5}');

DROP TABLE IF EXISTS pw_active;
CREATE TABLE pw_active (
  id mediumint(8) unsigned NOT NULL auto_increment,
  cid int(10) unsigned NOT NULL default '0',
  uid int(10) unsigned NOT NULL default '0',
  type tinyint(3) unsigned NOT NULL default '0',
  title varchar(120) NOT NULL default '',
  createtime int(10) unsigned NOT NULL default '0',
  begintime int(10) unsigned NOT NULL default '0',
  endtime int(10) unsigned NOT NULL default '0',
  deadline int(10) unsigned NOT NULL default '0',
  address varchar(255) NOT NULL default '',
  objecter tinyint(3) NOT NULL default '0',
  limitnum tinyint(3) NOT NULL default '0',
  price decimal(8,2) NOT NULL,
  introduction varchar(255) NOT NULL default '',
  poster varchar(60) NOT NULL default '',
  content text NOT NULL,
  members int(10) unsigned NOT NULL default '0',
  hits int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (id),
  KEY idx_cid (cid),
  KEY idx_uid (uid)
) ENGINE=MyISAM;
 

DROP TABLE IF EXISTS pw_activity;
CREATE TABLE pw_activity (
  tid int(10) unsigned NOT NULL default '0',
  subject varchar(100) NOT NULL default '',
  admin int(10) NOT NULL default '0',
  starttime int(10) NOT NULL default '0',
  endtime int(10) NOT NULL default '0',
  location varchar(20) NOT NULL default '',
  num smallint(6) NOT NULL default '0',
  sexneed tinyint(3) NOT NULL default '0',
  costs int(10) NOT NULL default '0',
  deadline int(10) NOT NULL default '0',
  PRIMARY KEY  (tid),
  KEY idx_admin (admin)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_activitycate;
CREATE TABLE pw_activitycate (
  `actid` smallint(6) unsigned NOT NULL auto_increment,
  `name` varchar(30) NOT NULL default '',
  `ifable` tinyint(3) NOT NULL default '1',
  `vieworder` tinyint(3) NOT NULL default '0',
  `ifdel` tinyint(3) NOT NULL default '0',
  PRIMARY KEY  (`actid`),
  KEY idx_vieworder (vieworder)
) TYPE=MyISAM AUTO_INCREMENT=5;
REPLACE INTO pw_activitycate VALUES(1, '户外活动', 1, 0, 0);
REPLACE INTO pw_activitycate VALUES(2, '体育健康', 1, 1, 0);
REPLACE INTO pw_activitycate VALUES(3, '文娱休闲', 1, 2, 0);
REPLACE INTO pw_activitycate VALUES(4, '其他活动', 1, 3, 0);

DROP TABLE IF EXISTS pw_activitydefaultvalue;
CREATE TABLE pw_activitydefaultvalue (
  `tid` int(10) unsigned NOT NULL,
  `actmid` smallint(6) unsigned NOT NULL default '0',
  `fid` smallint(6) unsigned NOT NULL default '0',
  `ifrecycle` tinyint(3) NOT NULL default '0',
  `iscertified` tinyint(3) NOT NULL default '1',
  `iscancel` tinyint(3) NOT NULL default '0',
  `out_biz_no` varchar(255) default NULL default '',
  `batch_no` varchar(255) NOT NULL default '',
  `user_id` varchar(255) NOT NULL default '',
  `recommend` tinyint(1) NOT NULL default '0',
  `starttime` int(10) unsigned NOT NULL default '0',
  `endtime` int(10) unsigned NOT NULL default '0',
  `location` varchar(255) NOT NULL default '',
  `contact` varchar(255) NOT NULL default '',
  `telephone` varchar(255) NOT NULL default '',
  `picture1` varchar(255) NOT NULL default '',
  `picture2` varchar(255) NOT NULL default '',
  `picture3` varchar(255) NOT NULL default '',
  `picture4` varchar(255) NOT NULL default '',
  `picture5` varchar(255) NOT NULL default '',
  `signupstarttime` int(10) unsigned NOT NULL default '0',
  `signupendtime` int(10) unsigned NOT NULL default '0',
  `minparticipant` int(10) unsigned NOT NULL default '0',
  `maxparticipant` int(10) unsigned NOT NULL default '0',
  `userlimit` tinyint(3) NOT NULL default '0',
  `specificuserlimit` varchar(255) NOT NULL default '',
  `genderlimit` tinyint(3) unsigned NOT NULL default '0',
  `fees` varchar(255) NOT NULL default '',
  `feesdetail` varchar(255) NOT NULL default '',
  `paymethod` tinyint(3) unsigned NOT NULL default '0',
  `pushtime` int(10) NOT NULL default '0',
  `updatetime` int(10) NOT NULL default '0',
  PRIMARY KEY  (`tid`),
  KEY `idx_actmid_fid` (`actmid`,`fid`)
)  TYPE=MyISAM ;

DROP TABLE IF EXISTS pw_activityfield;
CREATE TABLE pw_activityfield (
  `fieldid` smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(30) NOT NULL default '',
  `fieldname` varchar(30) NOT NULL default '',
  `actmid` smallint(6) unsigned NOT NULL default '0',
  `vieworder` tinyint(3) NOT NULL default '0',
  `type` varchar(20) NOT NULL default '',
  `rules` mediumtext NOT NULL,
  `ifable` tinyint(3) NOT NULL default '1',
  `ifsearch` tinyint(3) NOT NULL default '0',
  `ifasearch` tinyint(3) NOT NULL default '0',
  `issearchable` tinyint(3) unsigned NOT NULL default '1',
  `threadshow` tinyint(3) NOT NULL default '0',
  `allowthreadshow` tinyint(3) unsigned NOT NULL default '1',
  `ifmust` tinyint(3) NOT NULL default '1',
  `ifdel` tinyint(3) NOT NULL default '1',
  `mustenable` tinyint(3) unsigned NOT NULL default '0',
  `textwidth` tinyint(3) NOT NULL default '0',
  `descrip` varchar(255) NOT NULL default '',
  `sectionname` varchar(255) NOT NULL default '',
  PRIMARY KEY (`fieldid`),
  KEY `idx_actmid` (`actmid`)
)  TYPE=MyISAM AUTO_INCREMENT=537 ;
REPLACE INTO pw_activityfield VALUES(1, '活动时间', 'starttime', 1, 1, 'calendar', 'a:1:{s:9:"precision";s:6:"minute";}', 1, 0, 0, 1, 1, 1, 1, 0, 1, 18, '', '活动概况');
REPLACE INTO pw_activityfield VALUES(2, '-', 'endtime', 1, 1, 'calendar', 'a:1:{s:9:"precision";s:6:"minute";}', 1, 0, 0, 1, 1, 1, 1, 0, 1, 18, '', '活动概况');
REPLACE INTO pw_activityfield VALUES(3, '活动地点', 'location', 1, 2, 'text', '', 1, 1, 0, 1, 1, 1, 0, 0, 0, 40, '(多个活动地点用,隔开)', '活动概况');
REPLACE INTO pw_activityfield VALUES(4, '主题图片', 'picture1', 1, 3, 'upload', '', 1, 0, 0, 0, 0, 0, 0, 0, 1, 0, '(最多5张，支持gif、jpeg、jpg、bmp、png，小于2M)', '活动概况');
REPLACE INTO pw_activityfield VALUES(5, '主题图片二', 'picture2', 1, 3, 'upload', '', 1, 0, 0, 0, 0, 0, 0, 0, 1, 0, '', '活动概况');
REPLACE INTO pw_activityfield VALUES(6, '主题图片三', 'picture3', 1, 3, 'upload', '', 1, 0, 0, 0, 0, 0, 0, 0, 1, 0, '', '活动概况');
REPLACE INTO pw_activityfield VALUES(7, '主题图片四', 'picture4', 1, 3, 'upload', '', 1, 0, 0, 0, 0, 0, 0, 0, 1, 0, '', '活动概况');
REPLACE INTO pw_activityfield VALUES(8, '主题图片五', 'picture5', 1, 3, 'upload', '', 1, 0, 0, 0, 0, 0, 0, 0, 1, 0, '', '活动概况');
REPLACE INTO pw_activityfield VALUES(9, '联系人', 'contact', 1, 4, 'text', '', 1, 0, 1, 1, 0, 1, 1, 0, 1, 20, '', '活动概况');
REPLACE INTO pw_activityfield VALUES(10, '联系电话', 'telephone', 1, 5, 'text', '', 1, 0, 1, 1, 0, 1, 1, 0, 1, 40, '(多个联系电话用,隔开)', '活动概况');
REPLACE INTO pw_activityfield VALUES(11, '报名时间', 'signupstarttime', 1, 6, 'calendar', 'a:1:{s:9:"precision";s:6:"minute";}', 1, 1, 0, 1, 0, 1, 1, 0, 1, 18, '', '报名说明');
REPLACE INTO pw_activityfield VALUES(12, '-', 'signupendtime', 1, 6, 'calendar', 'a:1:{s:9:"precision";s:6:"minute";}', 1, 1, 0, 1, 0, 1, 1, 0, 1, 18, '', '报名说明');
REPLACE INTO pw_activityfield VALUES(13, '人数限制{@}最少', 'minparticipant', 1, 7, 'text', '', 1, 0, 1, 0, 0, 1, 1, 0, 0, 3, '', '报名说明');
REPLACE INTO pw_activityfield VALUES(14, '最多', 'maxparticipant', 1, 7, 'text', '', 1, 0, 1, 0, 0, 1, 1, 0, 0, 3, '(留空或0表示没有限制)', '报名说明');
REPLACE INTO pw_activityfield VALUES(15, '报名限制', 'userlimit', 1, 8, 'radio', 'a:2:{i:0;s:14:"1=所有用户";i:1;s:11:"2=仅好友";}', 1, 1, 0, 1, 0, 1, 0, 0, 0, 0, '', '报名说明');
REPLACE INTO pw_activityfield VALUES(16, '请输入其它限制', 'specificuserlimit', 1, 8, 'text', '', 1, 0, 1, 1, 0, 1, 0, 0, 0, 14, '', '报名说明');
REPLACE INTO pw_activityfield VALUES(17, '性别限制', 'genderlimit', 1, 9, 'radio', 'a:3:{i:0;s:8:"1=全部";i:1;s:11:"2=仅男生";i:2;s:11:"3=仅女生";}', 1, 0, 1, 1, 0, 1, 0, 0, 0, 0, '', '报名说明');
REPLACE INTO pw_activityfield VALUES(18, '活动费用', 'fees', 1, 10, 'text', '', 1, 1, 0, 0, 0, 1, 1, 0, 1, 0, '(如 小孩 每位 10元，有车族 每位10 元等)', '费用说明');
REPLACE INTO pw_activityfield VALUES(19, '费用明细', 'feesdetail', 1, 11, 'text', '', 1, 0, 1, 0, 0, 1, 0, 0, 0, 0, '', '费用说明');
REPLACE INTO pw_activityfield VALUES(20, '支付方式', 'paymethod', 1, 12, 'radio', 'a:2:{i:0;s:11:"1=支付宝";i:1;s:14:"2=现金支付";}', 1, 0, 1, 1, 0, 1, 1, 0, 1, 0, '', '费用说明');
REPLACE INTO pw_activityfield VALUES(21, '活动时间', 'starttime', 2, 1, 'calendar', 'a:1:{s:9:"precision";s:6:"minute";}', 1, 1, 1, 1, 0, 1, 1, 0, 1, 18, '', '活动概况');
REPLACE INTO pw_activityfield VALUES(22, '-', 'endtime', 2, 1, 'calendar', 'a:1:{s:9:"precision";s:6:"minute";}', 1, 1, 1, 1, 0, 1, 1, 0, 1, 18, '', '活动概况');
REPLACE INTO pw_activityfield VALUES(23, '活动地点', 'location', 2, 2, 'text', '', 0, 1, 1, 1, 0, 1, 0, 0, 0, 40, '(多个活动地点用,隔开)', '活动概况');
REPLACE INTO pw_activityfield VALUES(24, '主题图片', 'picture1', 2, 3, 'upload', '', 1, 0, 0, 0, 0, 0, 0, 0, 1, 0, '(最多5张，支持gif、jpeg、jpg、bmp、png，小于2M)', '活动概况');
REPLACE INTO pw_activityfield VALUES(25, '主题图片二', 'picture2', 2, 3, 'upload', '', 1, 0, 0, 0, 0, 0, 0, 0, 1, 0, '', '活动概况');
REPLACE INTO pw_activityfield VALUES(26, '主题图片三', 'picture3', 2, 3, 'upload', '', 1, 0, 0, 0, 0, 0, 0, 0, 1, 0, '', '活动概况');
REPLACE INTO pw_activityfield VALUES(27, '主题图片四', 'picture4', 2, 3, 'upload', '', 1, 0, 0, 0, 0, 0, 0, 0, 1, 0, '', '活动概况');
REPLACE INTO pw_activityfield VALUES(28, '主题图片五', 'picture5', 2, 3, 'upload', '', 1, 0, 0, 0, 0, 0, 0, 0, 1, 0, '', '活动概况');
REPLACE INTO pw_activityfield VALUES(29, '联系人', 'contact', 2, 4, 'text', '', 1, 1, 1, 1, 0, 1, 1, 0, 1, 20, '', '活动概况');
REPLACE INTO pw_activityfield VALUES(30, '联系电话', 'telephone', 2, 5, 'text', '', 1, 1, 1, 1, 0, 1, 1, 0, 1, 40, '(多个联系电话用,隔开)', '活动概况');
REPLACE INTO pw_activityfield VALUES(31, '报名时间', 'signupstarttime', 2, 6, 'calendar', 'a:1:{s:9:"precision";s:6:"minute";}', 1, 0, 1, 1, 0, 1, 1, 0, 1, 18, '', '报名说明');
REPLACE INTO pw_activityfield VALUES(32, '-', 'signupendtime', 2, 6, 'calendar', 'a:1:{s:9:"precision";s:6:"minute";}', 1, 0, 1, 1, 0, 1, 1, 0, 1, 18, '', '报名说明');
REPLACE INTO pw_activityfield VALUES(33, '人数限制{@}最少', 'minparticipant', 2, 7, 'text', '', 0, 0, 0, 0, 0, 1, 0, 0, 0, 3, '', '报名说明');
REPLACE INTO pw_activityfield VALUES(34, '最多', 'maxparticipant', 2, 7, 'text', '', 0, 0, 0, 0, 0, 1, 0, 0, 0, 3, '(留空或0表示没有限制)', '报名说明');
REPLACE INTO pw_activityfield VALUES(35, '报名限制', 'userlimit', 2, 8, 'radio', 'a:2:{i:0;s:14:"1=所有用户";i:1;s:11:"2=仅好友";}', 1, 0, 1, 1, 0, 1, 0, 0, 0, 0, '', '报名说明');
REPLACE INTO pw_activityfield VALUES(36, '请输入其它限制', 'specificuserlimit', 2, 8, 'text', '', 0, 0, 1, 1, 0, 1, 0, 0, 0, 14, '', '报名说明');
REPLACE INTO pw_activityfield VALUES(37, '性别限制', 'genderlimit', 2, 9, 'radio', 'a:3:{i:0;s:8:"1=全部";i:1;s:11:"2=仅男生";i:2;s:11:"3=仅女生";}', 0, 0, 0, 1, 0, 1, 0, 0, 0, 0, '', '报名说明');
REPLACE INTO pw_activityfield VALUES(38, '活动费用', 'fees', 2, 10, 'text', '', 1, 0, 0, 0, 0, 1, 1, 0, 1, 0, '(如 小孩 每位 10元，有车族 每位10 元等)', '费用说明');
REPLACE INTO pw_activityfield VALUES(39, '费用明细', 'feesdetail', 2, 11, 'text', '', 0, 0, 0, 0, 0, 1, 0, 0, 0, 0, '', '费用说明');
REPLACE INTO pw_activityfield VALUES(40, '支付方式', 'paymethod', 2, 12, 'radio', 'a:2:{i:0;s:11:"1=支付宝";i:1;s:14:"2=现金支付";}', 1, 0, 0, 1, 0, 1, 1, 0, 1, 0, '', '费用说明');
REPLACE INTO pw_activityfield VALUES(41, '活动时间', 'starttime', 3, 1, 'calendar', 'a:1:{s:9:"precision";s:6:"minute";}', 1, 0, 0, 1, 0, 1, 1, 0, 1, 18, '', '活动概况');
REPLACE INTO pw_activityfield VALUES(42, '-', 'endtime', 3, 1, 'calendar', 'a:1:{s:9:"precision";s:6:"minute";}', 1, 0, 0, 1, 0, 1, 1, 0, 1, 18, '', '活动概况');
REPLACE INTO pw_activityfield VALUES(43, '活动地点', 'location', 3, 2, 'text', '', 1, 0, 0, 1, 0, 1, 0, 0, 0, 40, '(多个活动地点用,隔开)', '活动概况');
REPLACE INTO pw_activityfield VALUES(44, '主题图片', 'picture1', 3, 3, 'upload', '', 1, 0, 0, 0, 0, 0, 0, 0, 1, 0, '(最多5张，支持gif、jpeg、jpg、bmp、png，小于2M)', '活动概况');
REPLACE INTO pw_activityfield VALUES(45, '主题图片二', 'picture2', 3, 3, 'upload', '', 1, 0, 0, 0, 0, 0, 0, 0, 1, 0, '', '活动概况');
REPLACE INTO pw_activityfield VALUES(46, '主题图片三', 'picture3', 3, 3, 'upload', '', 1, 0, 0, 0, 0, 0, 0, 0, 1, 0, '', '活动概况');
REPLACE INTO pw_activityfield VALUES(47, '主题图片四', 'picture4', 3, 3, 'upload', '', 1, 0, 0, 0, 0, 0, 0, 0, 1, 0, '', '活动概况');
REPLACE INTO pw_activityfield VALUES(48, '主题图片五', 'picture5', 3, 3, 'upload', '', 1, 0, 0, 0, 0, 0, 0, 0, 1, 0, '', '活动概况');
REPLACE INTO pw_activityfield VALUES(49, '联系人', 'contact', 3, 4, 'text', '', 1, 0, 0, 1, 0, 1, 1, 0, 1, 20, '', '活动概况');
REPLACE INTO pw_activityfield VALUES(50, '联系电话', 'telephone', 3, 5, 'text', '', 1, 0, 0, 1, 0, 1, 1, 0, 1, 40, '(多个联系电话用,隔开)', '活动概况');
REPLACE INTO pw_activityfield VALUES(51, '报名时间', 'signupstarttime', 3, 6, 'calendar', 'a:1:{s:9:"precision";s:6:"minute";}', 1, 0, 0, 1, 0, 1, 1, 0, 1, 18, '', '报名说明');
REPLACE INTO pw_activityfield VALUES(52, '-', 'signupendtime', 3, 6, 'calendar', 'a:1:{s:9:"precision";s:6:"minute";}', 1, 0, 0, 1, 0, 1, 1, 0, 1, 18, '', '报名说明');
REPLACE INTO pw_activityfield VALUES(53, '人数限制{@}最少', 'minparticipant', 3, 7, 'text', '', 1, 0, 0, 0, 0, 1, 0, 0, 0, 3, '', '报名说明');
REPLACE INTO pw_activityfield VALUES(54, '最多', 'maxparticipant', 3, 7, 'text', '', 1, 0, 0, 0, 0, 1, 0, 0, 0, 3, '(留空或0表示没有限制)', '报名说明');
REPLACE INTO pw_activityfield VALUES(55, '报名限制', 'userlimit', 3, 8, 'radio', 'a:2:{i:0;s:14:"1=所有用户";i:1;s:11:"2=仅好友";}', 1, 0, 0, 1, 0, 1, 0, 0, 0, 3, '', '报名说明');
REPLACE INTO pw_activityfield VALUES(56, '请输入其它限制', 'specificuserlimit', 3, 8, 'text', '', 1, 0, 0, 1, 0, 1, 0, 0, 0, 14, '', '报名说明');
REPLACE INTO pw_activityfield VALUES(57, '性别限制', 'genderlimit', 3, 9, 'radio', 'a:3:{i:0;s:8:"1=全部";i:1;s:11:"2=仅男生";i:2;s:11:"3=仅女生";}', 1, 0, 0, 1, 0, 1, 0, 0, 0, 0, '', '报名说明');
REPLACE INTO pw_activityfield VALUES(58, '活动费用', 'fees', 3, 10, 'text', '', 1, 0, 0, 0, 0, 1, 1, 0, 1, 0, '(如 小孩 每位 10元，有车族 每位10 元等)', '费用说明');
REPLACE INTO pw_activityfield VALUES(59, '费用明细', 'feesdetail', 3, 11, 'text', '', 1, 0, 0, 0, 0, 1, 0, 0, 0, 0, '', '费用说明');
REPLACE INTO pw_activityfield VALUES(60, '支付方式', 'paymethod', 3, 12, 'radio', 'a:2:{i:0;s:11:"1=支付宝";i:1;s:14:"2=现金支付";}', 1, 0, 0, 1, 0, 1, 1, 0, 1, 0, '', '费用说明');
REPLACE INTO pw_activityfield VALUES(61, '活动时间', 'starttime', 4, 1, 'calendar', 'a:1:{s:9:"precision";s:6:"minute";}', 1, 0, 0, 1, 0, 1, 1, 0, 1, 18, '', '活动概况');
REPLACE INTO pw_activityfield VALUES(62, '-', 'endtime', 4, 1, 'calendar', 'a:1:{s:9:"precision";s:6:"minute";}', 1, 0, 0, 1, 0, 1, 1, 0, 1, 18, '', '活动概况');
REPLACE INTO pw_activityfield VALUES(63, '活动地点', 'location', 4, 2, 'text', '', 1, 0, 0, 1, 0, 1, 0, 0, 0, 40, '(多个活动地点用,隔开)', '活动概况');
REPLACE INTO pw_activityfield VALUES(64, '主题图片', 'picture1', 4, 3, 'upload', '', 1, 0, 0, 0, 0, 0, 0, 0, 1, 0, '(最多5张，支持gif、jpeg、jpg、bmp、png，小于2M)', '活动概况');
REPLACE INTO pw_activityfield VALUES(65, '主题图片二', 'picture2', 4, 3, 'upload', '', 1, 0, 0, 0, 0, 0, 0, 0, 1, 0, '', '活动概况');
REPLACE INTO pw_activityfield VALUES(66, '主题图片三', 'picture3', 4, 3, 'upload', '', 1, 0, 0, 0, 0, 0, 0, 0, 1, 0, '', '活动概况');
REPLACE INTO pw_activityfield VALUES(67, '主题图片四', 'picture4', 4, 3, 'upload', '', 1, 0, 0, 0, 0, 0, 0, 0, 1, 0, '', '活动概况');
REPLACE INTO pw_activityfield VALUES(68, '主题图片五', 'picture5', 4, 3, 'upload', '', 1, 0, 0, 0, 0, 0, 0, 0, 1, 0, '', '活动概况');
REPLACE INTO pw_activityfield VALUES(69, '联系人', 'contact', 4, 4, 'text', '', 1, 0, 0, 1, 0, 1, 1, 0, 1, 20, '', '活动概况');
REPLACE INTO pw_activityfield VALUES(70, '联系电话', 'telephone', 4, 5, 'text', '', 1, 0, 0, 1, 0, 1, 1, 0, 1, 40, '(多个联系电话用,隔开)', '活动概况');
REPLACE INTO pw_activityfield VALUES(71, '报名时间', 'signupstarttime', 4, 6, 'calendar', 'a:1:{s:9:"precision";s:6:"minute";}', 1, 0, 0, 1, 0, 1, 1, 0, 1, 18, '', '报名说明');
REPLACE INTO pw_activityfield VALUES(72, '-', 'signupendtime', 4, 6, 'calendar', 'a:1:{s:9:"precision";s:6:"minute";}', 1, 0, 0, 1, 0, 1, 1, 0, 1, 18, '', '报名说明');
REPLACE INTO pw_activityfield VALUES(73, '人数限制{@}最少', 'minparticipant', 4, 7, 'text', '', 1, 0, 0, 0, 0, 1, 0, 0, 0, 3, '', '报名说明');
REPLACE INTO pw_activityfield VALUES(74, '最多', 'maxparticipant', 4, 7, 'text', '', 1, 0, 0, 0, 0, 1, 0, 0, 0, 3, '(留空或0表示没有限制)', '报名说明');
REPLACE INTO pw_activityfield VALUES(75, '报名限制', 'userlimit', 4, 8, 'radio', 'a:2:{i:0;s:14:"1=所有用户";i:1;s:11:"2=仅好友";}', 1, 0, 0, 1, 0, 1, 0, 0, 0, 3, '', '报名说明');
REPLACE INTO pw_activityfield VALUES(76, '请输入其它限制', 'specificuserlimit', 4, 8, 'text', '', 1, 0, 0, 1, 0, 1, 0, 0, 0, 14, '', '报名说明');
REPLACE INTO pw_activityfield VALUES(77, '性别限制', 'genderlimit', 4, 9, 'radio', 'a:3:{i:0;s:8:"1=全部";i:1;s:11:"2=仅男生";i:2;s:11:"3=仅女生";}', 1, 0, 0, 1, 0, 1, 0, 0, 0, 0, '', '报名说明');
REPLACE INTO pw_activityfield VALUES(78, '活动费用', 'fees', 4, 10, 'text', '', 1, 0, 0, 0, 0, 1, 1, 0, 1, 0, '(如 小孩 每位 10元，有车族 每位10 元等)', '费用说明');
REPLACE INTO pw_activityfield VALUES(79, '费用明细', 'feesdetail', 4, 11, 'text', '', 1, 0, 0, 0, 0, 1, 0, 0, 0, 0, '', '费用说明');
REPLACE INTO pw_activityfield VALUES(80, '支付方式', 'paymethod', 4, 12, 'radio', 'a:2:{i:0;s:11:"1=支付宝";i:1;s:14:"2=现金支付";}', 1, 0, 0, 1, 0, 1, 1, 0, 1, 0, '', '费用说明');
REPLACE INTO pw_activityfield VALUES(81, '活动时间', 'starttime', 5, 1, 'calendar', 'a:1:{s:9:"precision";s:6:"minute";}', 1, 1, 1, 1, 1, 1, 1, 0, 1, 18, '', '活动概况');
REPLACE INTO pw_activityfield VALUES(82, '-', 'endtime', 5, 1, 'calendar', 'a:1:{s:9:"precision";s:6:"minute";}', 1, 1, 1, 1, 1, 1, 1, 0, 1, 18, '', '活动概况');
REPLACE INTO pw_activityfield VALUES(83, '活动地点', 'location', 5, 2, 'text', '', 1, 1, 1, 1, 1, 1, 0, 0, 0, 40, '(多个活动地点用,隔开)', '活动概况');
REPLACE INTO pw_activityfield VALUES(84, '主题图片', 'picture1', 5, 3, 'upload', '', 1, 0, 0, 0, 0, 0, 0, 0, 1, 0, '(最多5张，支持gif、jpeg、jpg、bmp、png，小于2M)', '活动概况');
REPLACE INTO pw_activityfield VALUES(85, '主题图片二', 'picture2', 5, 3, 'upload', '', 1, 0, 0, 0, 0, 0, 0, 0, 1, 0, '', '活动概况');
REPLACE INTO pw_activityfield VALUES(86, '主题图片三', 'picture3', 5, 3, 'upload', '', 1, 0, 0, 0, 0, 0, 0, 0, 1, 0, '', '活动概况');
REPLACE INTO pw_activityfield VALUES(87, '主题图片四', 'picture4', 5, 3, 'upload', '', 1, 0, 0, 0, 0, 0, 0, 0, 1, 0, '', '活动概况');
REPLACE INTO pw_activityfield VALUES(88, '主题图片五', 'picture5', 5, 3, 'upload', '', 1, 0, 0, 0, 0, 0, 0, 0, 1, 0, '', '活动概况');
REPLACE INTO pw_activityfield VALUES(89, '联系人', 'contact', 5, 4, 'text', '', 1, 1, 1, 1, 1, 1, 1, 0, 1, 20, '', '活动概况');
REPLACE INTO pw_activityfield VALUES(90, '联系电话', 'telephone', 5, 5, 'text', '', 1, 1, 1, 1, 1, 1, 1, 0, 1, 40, '(多个联系电话用,隔开)', '活动概况');
REPLACE INTO pw_activityfield VALUES(91, '报名时间', 'signupstarttime', 5, 6, 'calendar', 'a:1:{s:9:"precision";s:6:"minute";}', 1, 1, 1, 1, 1, 1, 1, 0, 1, 18, '', '报名说明');
REPLACE INTO pw_activityfield VALUES(92, '-', 'signupendtime', 5, 6, 'calendar', 'a:1:{s:9:"precision";s:6:"minute";}', 1, 1, 1, 1, 1, 1, 1, 0, 1, 18, '', '报名说明');
REPLACE INTO pw_activityfield VALUES(93, '人数限制{@}最少', 'minparticipant', 5, 7, 'text', '', 1, 0, 0, 0, 1, 1, 0, 0, 0, 3, '', '报名说明');
REPLACE INTO pw_activityfield VALUES(94, '最多', 'maxparticipant', 5, 7, 'text', '', 1, 0, 0, 0, 1, 1, 0, 0, 0, 3, '(留空或0表示没有限制)', '报名说明');
REPLACE INTO pw_activityfield VALUES(95, '报名限制', 'userlimit', 5, 8, 'radio', 'a:2:{i:0;s:14:"1=所有用户";i:1;s:11:"2=仅好友";}', 1, 1, 1, 1, 1, 1, 0, 0, 0, 0, '', '报名说明');
REPLACE INTO pw_activityfield VALUES(96, '请输入其它限制', 'specificuserlimit', 5, 8, 'text', '', 1, 1, 1, 1, 1, 1, 0, 0, 0, 14, '', '报名说明');
REPLACE INTO pw_activityfield VALUES(97, '性别限制', 'genderlimit', 5, 9, 'radio', 'a:3:{i:0;s:8:"1=全部";i:1;s:11:"2=仅男生";i:2;s:11:"3=仅女生";}', 1, 1, 1, 1, 1, 1, 0, 0, 0, 0, '', '报名说明');
REPLACE INTO pw_activityfield VALUES(98, '活动费用', 'fees', 5, 10, 'text', '', 1, 0, 0, 0, 1, 1, 1, 0, 1, 0, '(如 小孩 每位 10元，有车族 每位10 元等)', '费用说明');
REPLACE INTO pw_activityfield VALUES(99, '费用明细', 'feesdetail', 5, 11, 'text', '', 1, 0, 0, 0, 1, 1, 0, 0, 0, 0, '', '费用说明');
REPLACE INTO pw_activityfield VALUES(100, '支付方式', 'paymethod', 5, 12, 'radio', 'a:2:{i:0;s:11:"1=支付宝";i:1;s:14:"2=现金支付";}', 1, 1, 1, 1, 1, 1, 1, 0, 1, 0, '', '费用说明');
REPLACE INTO pw_activityfield VALUES(101, '活动时间', 'starttime', 6, 1, 'calendar', 'a:1:{s:9:"precision";s:6:"minute";}', 1, 0, 0, 1, 0, 1, 1, 0, 1, 18, '', '活动概况');
REPLACE INTO pw_activityfield VALUES(102, '-', 'endtime', 6, 1, 'calendar', 'a:1:{s:9:"precision";s:6:"minute";}', 1, 0, 0, 1, 0, 1, 1, 0, 1, 18, '', '活动概况');
REPLACE INTO pw_activityfield VALUES(103, '活动地点', 'location', 6, 2, 'text', '', 1, 0, 0, 1, 0, 1, 0, 0, 0, 40, '(多个活动地点用,隔开)', '活动概况');
REPLACE INTO pw_activityfield VALUES(104, '主题图片', 'picture1', 6, 3, 'upload', '', 1, 0, 0, 0, 0, 0, 0, 0, 1, 0, '(最多5张，支持gif、jpeg、jpg、bmp、png，小于2M)', '活动概况');
REPLACE INTO pw_activityfield VALUES(105, '主题图片二', 'picture2', 6, 3, 'upload', '', 1, 0, 0, 0, 0, 0, 0, 0, 1, 0, '', '活动概况');
REPLACE INTO pw_activityfield VALUES(106, '主题图片三', 'picture3', 6, 3, 'upload', '', 1, 0, 0, 0, 0, 0, 0, 0, 1, 0, '', '活动概况');
REPLACE INTO pw_activityfield VALUES(107, '主题图片四', 'picture4', 6, 3, 'upload', '', 1, 0, 0, 0, 0, 0, 0, 0, 1, 0, '', '活动概况');
REPLACE INTO pw_activityfield VALUES(108, '主题图片五', 'picture5', 6, 3, 'upload', '', 1, 0, 0, 0, 0, 0, 0, 0, 1, 0, '', '活动概况');
REPLACE INTO pw_activityfield VALUES(109, '联系人', 'contact', 6, 4, 'text', '', 1, 0, 0, 1, 0, 1, 1, 0, 1, 20, '', '活动概况');
REPLACE INTO pw_activityfield VALUES(110, '联系电话', 'telephone', 6, 5, 'text', '', 1, 0, 0, 1, 0, 1, 1, 0, 1, 40, '(多个联系电话用,隔开)', '活动概况');
REPLACE INTO pw_activityfield VALUES(111, '报名时间', 'signupstarttime', 6, 6, 'calendar', 'a:1:{s:9:"precision";s:6:"minute";}', 1, 0, 0, 1, 0, 1, 1, 0, 1, 18, '', '报名说明');
REPLACE INTO pw_activityfield VALUES(112, '-', 'signupendtime', 6, 6, 'calendar', 'a:1:{s:9:"precision";s:6:"minute";}', 1, 0, 0, 1, 0, 1, 1, 0, 1, 18, '', '报名说明');
REPLACE INTO pw_activityfield VALUES(113, '人数限制{@}最少', 'minparticipant', 6, 7, 'text', '', 1, 0, 0, 0, 0, 1, 0, 0, 0, 3, '', '报名说明');
REPLACE INTO pw_activityfield VALUES(114, '最多', 'maxparticipant', 6, 7, 'text', '', 1, 0, 0, 0, 0, 1, 0, 0, 0, 3, '(留空或0表示没有限制)', '报名说明');
REPLACE INTO pw_activityfield VALUES(115, '报名限制', 'userlimit', 6, 8, 'radio', 'a:2:{i:0;s:14:"1=所有用户";i:1;s:11:"2=仅好友";}', 1, 0, 0, 1, 0, 1, 0, 0, 0, 3, '', '报名说明');
REPLACE INTO pw_activityfield VALUES(116, '请输入其它限制', 'specificuserlimit', 6, 8, 'text', '', 1, 0, 0, 1, 0, 1, 0, 0, 0, 14, '', '报名说明');
REPLACE INTO pw_activityfield VALUES(117, '性别限制', 'genderlimit', 6, 9, 'radio', 'a:3:{i:0;s:8:"1=全部";i:1;s:11:"2=仅男生";i:2;s:11:"3=仅女生";}', 1, 0, 0, 1, 0, 1, 0, 0, 0, 0, '', '报名说明');
REPLACE INTO pw_activityfield VALUES(118, '活动费用', 'fees', 6, 10, 'text', '', 1, 0, 0, 0, 0, 1, 1, 0, 1, 0, '(如 小孩 每位 10元，有车族 每位10 元等)', '费用说明');
REPLACE INTO pw_activityfield VALUES(119, '费用明细', 'feesdetail', 6, 11, 'text', '', 1, 0, 0, 0, 0, 1, 0, 0, 0, 0, '', '费用说明');
REPLACE INTO pw_activityfield VALUES(120, '支付方式', 'paymethod', 6, 12, 'radio', 'a:2:{i:0;s:11:"1=支付宝";i:1;s:14:"2=现金支付";}', 1, 0, 0, 1, 0, 1, 1, 0, 1, 0, '', '费用说明');
REPLACE INTO pw_activityfield VALUES(121, '活动时间', 'starttime', 7, 1, 'calendar', 'a:1:{s:9:"precision";s:6:"minute";}', 1, 0, 0, 1, 0, 1, 1, 0, 1, 18, '', '活动概况');
REPLACE INTO pw_activityfield VALUES(122, '-', 'endtime', 7, 1, 'calendar', 'a:1:{s:9:"precision";s:6:"minute";}', 1, 0, 0, 1, 0, 1, 1, 0, 1, 18, '', '活动概况');
REPLACE INTO pw_activityfield VALUES(123, '活动地点', 'location', 7, 2, 'text', '', 1, 0, 0, 1, 0, 1, 0, 0, 0, 40, '(多个活动地点用,隔开)', '活动概况');
REPLACE INTO pw_activityfield VALUES(124, '主题图片', 'picture1', 7, 3, 'upload', '', 1, 0, 0, 0, 0, 0, 0, 0, 1, 0, '(最多5张，支持gif、jpeg、jpg、bmp、png，小于2M)', '活动概况');
REPLACE INTO pw_activityfield VALUES(125, '主题图片二', 'picture2', 7, 3, 'upload', '', 1, 0, 0, 0, 0, 0, 0, 0, 1, 0, '', '活动概况');
REPLACE INTO pw_activityfield VALUES(126, '主题图片三', 'picture3', 7, 3, 'upload', '', 1, 0, 0, 0, 0, 0, 0, 0, 1, 0, '', '活动概况');
REPLACE INTO pw_activityfield VALUES(127, '主题图片四', 'picture4', 7, 3, 'upload', '', 1, 0, 0, 0, 0, 0, 0, 0, 1, 0, '', '活动概况');
REPLACE INTO pw_activityfield VALUES(128, '主题图片五', 'picture5', 7, 3, 'upload', '', 1, 0, 0, 0, 0, 0, 0, 0, 1, 0, '', '活动概况');
REPLACE INTO pw_activityfield VALUES(129, '联系人', 'contact', 7, 4, 'text', '', 1, 0, 0, 1, 0, 1, 1, 0, 1, 20, '', '活动概况');
REPLACE INTO pw_activityfield VALUES(130, '联系电话', 'telephone', 7, 5, 'text', '', 1, 0, 0, 1, 0, 1, 1, 0, 1, 40, '(多个联系电话用,隔开)', '活动概况');
REPLACE INTO pw_activityfield VALUES(131, '报名时间', 'signupstarttime', 7, 6, 'calendar', 'a:1:{s:9:"precision";s:6:"minute";}', 1, 0, 0, 1, 0, 1, 1, 0, 1, 18, '', '报名说明');
REPLACE INTO pw_activityfield VALUES(132, '-', 'signupendtime', 7, 6, 'calendar', 'a:1:{s:9:"precision";s:6:"minute";}', 1, 0, 0, 1, 0, 1, 1, 0, 1, 18, '', '报名说明');
REPLACE INTO pw_activityfield VALUES(133, '人数限制{@}最少', 'minparticipant', 7, 7, 'text', '', 1, 0, 0, 0, 0, 1, 0, 0, 0, 3, '', '报名说明');
REPLACE INTO pw_activityfield VALUES(134, '最多', 'maxparticipant', 7, 7, 'text', '', 1, 0, 0, 0, 0, 1, 0, 0, 0, 3, '(留空或0表示没有限制)', '报名说明');
REPLACE INTO pw_activityfield VALUES(135, '报名限制', 'userlimit', 7, 8, 'radio', 'a:2:{i:0;s:14:"1=所有用户";i:1;s:11:"2=仅好友";}', 1, 0, 0, 1, 0, 1, 0, 0, 0, 3, '', '报名说明');
REPLACE INTO pw_activityfield VALUES(136, '请输入其它限制', 'specificuserlimit', 7, 8, 'text', '', 1, 0, 0, 1, 0, 1, 0, 0, 0, 14, '', '报名说明');
REPLACE INTO pw_activityfield VALUES(137, '性别限制', 'genderlimit', 7, 9, 'radio', 'a:3:{i:0;s:8:"1=全部";i:1;s:11:"2=仅男生";i:2;s:11:"3=仅女生";}', 1, 0, 0, 1, 0, 1, 0, 0, 0, 0, '', '报名说明');
REPLACE INTO pw_activityfield VALUES(138, '活动费用', 'fees', 7, 10, 'text', '', 1, 0, 0, 0, 0, 1, 1, 0, 1, 0, '(如 小孩 每位 10元，有车族 每位10 元等)', '费用说明');
REPLACE INTO pw_activityfield VALUES(139, '费用明细', 'feesdetail', 7, 11, 'text', '', 1, 0, 0, 0, 0, 1, 0, 0, 0, 0, '', '费用说明');
REPLACE INTO pw_activityfield VALUES(140, '支付方式', 'paymethod', 7, 12, 'radio', 'a:2:{i:0;s:11:"1=支付宝";i:1;s:14:"2=现金支付";}', 1, 0, 0, 1, 0, 1, 1, 0, 1, 0, '', '费用说明');
REPLACE INTO pw_activityfield VALUES(141, '活动时间', 'starttime', 8, 1, 'calendar', 'a:1:{s:9:"precision";s:6:"minute";}', 1, 0, 0, 1, 0, 1, 1, 0, 1, 18, '', '活动概况');
REPLACE INTO pw_activityfield VALUES(142, '-', 'endtime', 8, 1, 'calendar', 'a:1:{s:9:"precision";s:6:"minute";}', 1, 0, 0, 1, 0, 1, 1, 0, 1, 18, '', '活动概况');
REPLACE INTO pw_activityfield VALUES(143, '活动地点', 'location', 8, 2, 'text', '', 1, 0, 0, 1, 0, 1, 0, 0, 0, 40, '(多个活动地点用,隔开)', '活动概况');
REPLACE INTO pw_activityfield VALUES(144, '主题图片', 'picture1', 8, 3, 'upload', '', 1, 0, 0, 0, 0, 0, 0, 0, 1, 0, '(最多5张，支持gif、jpeg、jpg、bmp、png，小于2M)', '活动概况');
REPLACE INTO pw_activityfield VALUES(145, '主题图片二', 'picture2', 8, 3, 'upload', '', 1, 0, 0, 0, 0, 0, 0, 0, 1, 0, '', '活动概况');
REPLACE INTO pw_activityfield VALUES(146, '主题图片三', 'picture3', 8, 3, 'upload', '', 1, 0, 0, 0, 0, 0, 0, 0, 1, 0, '', '活动概况');
REPLACE INTO pw_activityfield VALUES(147, '主题图片四', 'picture4', 8, 3, 'upload', '', 1, 0, 0, 0, 0, 0, 0, 0, 1, 0, '', '活动概况');
REPLACE INTO pw_activityfield VALUES(148, '主题图片五', 'picture5', 8, 3, 'upload', '', 1, 0, 0, 0, 0, 0, 0, 0, 1, 0, '', '活动概况');
REPLACE INTO pw_activityfield VALUES(149, '联系人', 'contact', 8, 4, 'text', '', 1, 0, 0, 1, 0, 1, 1, 0, 1, 20, '', '活动概况');
REPLACE INTO pw_activityfield VALUES(150, '联系电话', 'telephone', 8, 5, 'text', '', 1, 0, 0, 1, 0, 1, 1, 0, 1, 40, '(多个联系电话用,隔开)', '活动概况');
REPLACE INTO pw_activityfield VALUES(151, '报名时间', 'signupstarttime', 8, 6, 'calendar', 'a:1:{s:9:"precision";s:6:"minute";}', 1, 0, 0, 1, 0, 1, 1, 0, 1, 18, '', '报名说明');
REPLACE INTO pw_activityfield VALUES(152, '-', 'signupendtime', 8, 6, 'calendar', 'a:1:{s:9:"precision";s:6:"minute";}', 1, 0, 0, 1, 0, 1, 1, 0, 1, 18, '', '报名说明');
REPLACE INTO pw_activityfield VALUES(153, '人数限制{@}最少', 'minparticipant', 8, 7, 'text', '', 1, 0, 0, 0, 0, 1, 0, 0, 0, 3, '', '报名说明');
REPLACE INTO pw_activityfield VALUES(154, '最多', 'maxparticipant', 8, 7, 'text', '', 1, 0, 0, 0, 0, 1, 0, 0, 0, 3, '(留空或0表示没有限制)', '报名说明');
REPLACE INTO pw_activityfield VALUES(155, '报名限制', 'userlimit', 8, 8, 'radio', 'a:2:{i:0;s:14:"1=所有用户";i:1;s:11:"2=仅好友";}', 1, 0, 0, 1, 0, 1, 0, 0, 0, 3, '', '报名说明');
REPLACE INTO pw_activityfield VALUES(156, '请输入其它限制', 'specificuserlimit', 8, 8, 'text', '', 1, 0, 0, 1, 0, 1, 0, 0, 0, 14, '', '报名说明');
REPLACE INTO pw_activityfield VALUES(157, '性别限制', 'genderlimit', 8, 9, 'radio', 'a:3:{i:0;s:8:"1=全部";i:1;s:11:"2=仅男生";i:2;s:11:"3=仅女生";}', 1, 0, 0, 1, 0, 1, 0, 0, 0, 0, '', '报名说明');
REPLACE INTO pw_activityfield VALUES(158, '活动费用', 'fees', 8, 10, 'text', '', 1, 0, 0, 0, 0, 1, 1, 0, 1, 0, '(如 小孩 每位 10元，有车族 每位10 元等)', '费用说明');
REPLACE INTO pw_activityfield VALUES(159, '费用明细', 'feesdetail', 8, 11, 'text', '', 1, 0, 0, 0, 0, 1, 0, 0, 0, 0, '', '费用说明');
REPLACE INTO pw_activityfield VALUES(160, '支付方式', 'paymethod', 8, 12, 'radio', 'a:2:{i:0;s:11:"1=支付宝";i:1;s:14:"2=现金支付";}', 1, 0, 0, 1, 0, 1, 1, 0, 1, 0, '', '费用说明');
REPLACE INTO pw_activityfield VALUES(161, '活动时间', 'starttime', 9, 1, 'calendar', 'a:1:{s:9:"precision";s:6:"minute";}', 1, 0, 0, 1, 0, 1, 1, 0, 1, 18, '', '活动概况');
REPLACE INTO pw_activityfield VALUES(162, '-', 'endtime', 9, 1, 'calendar', 'a:1:{s:9:"precision";s:6:"minute";}', 1, 0, 0, 1, 0, 1, 1, 0, 1, 18, '', '活动概况');
REPLACE INTO pw_activityfield VALUES(163, '活动地点', 'location', 9, 2, 'text', '', 1, 0, 0, 1, 0, 1, 0, 0, 0, 40, '(多个活动地点用,隔开)', '活动概况');
REPLACE INTO pw_activityfield VALUES(164, '主题图片', 'picture1', 9, 3, 'upload', '', 1, 0, 0, 0, 0, 0, 0, 0, 1, 0, '(最多5张，支持gif、jpeg、jpg、bmp、png，小于2M)', '活动概况');
REPLACE INTO pw_activityfield VALUES(165, '主题图片二', 'picture2', 9, 3, 'upload', '', 1, 0, 0, 0, 0, 0, 0, 0, 1, 0, '', '活动概况');
REPLACE INTO pw_activityfield VALUES(166, '主题图片三', 'picture3', 9, 3, 'upload', '', 1, 0, 0, 0, 0, 0, 0, 0, 1, 0, '', '活动概况');
REPLACE INTO pw_activityfield VALUES(167, '主题图片四', 'picture4', 9, 3, 'upload', '', 1, 0, 0, 0, 0, 0, 0, 0, 1, 0, '', '活动概况');
REPLACE INTO pw_activityfield VALUES(168, '主题图片五', 'picture5', 9, 3, 'upload', '', 1, 0, 0, 0, 0, 0, 0, 0, 1, 0, '', '活动概况');
REPLACE INTO pw_activityfield VALUES(169, '联系人', 'contact', 9, 4, 'text', '', 1, 0, 0, 1, 0, 1, 1, 0, 1, 20, '', '活动概况');
REPLACE INTO pw_activityfield VALUES(170, '联系电话', 'telephone', 9, 5, 'text', '', 1, 0, 0, 1, 0, 1, 1, 0, 1, 40, '(多个联系电话用,隔开)', '活动概况');
REPLACE INTO pw_activityfield VALUES(171, '报名时间', 'signupstarttime', 9, 6, 'calendar', 'a:1:{s:9:"precision";s:6:"minute";}', 1, 0, 0, 1, 0, 1, 1, 0, 1, 18, '', '报名说明');
REPLACE INTO pw_activityfield VALUES(172, '-', 'signupendtime', 9, 6, 'calendar', 'a:1:{s:9:"precision";s:6:"minute";}', 1, 0, 0, 1, 0, 1, 1, 0, 1, 18, '', '报名说明');
REPLACE INTO pw_activityfield VALUES(173, '人数限制{@}最少', 'minparticipant', 9, 7, 'text', '', 1, 0, 0, 0, 0, 1, 0, 0, 0, 3, '', '报名说明');
REPLACE INTO pw_activityfield VALUES(174, '最多', 'maxparticipant', 9, 7, 'text', '', 1, 0, 0, 0, 0, 1, 0, 0, 0, 3, '(留空或0表示没有限制)', '报名说明');
REPLACE INTO pw_activityfield VALUES(175, '报名限制', 'userlimit', 9, 8, 'radio', 'a:2:{i:0;s:14:"1=所有用户";i:1;s:11:"2=仅好友";}', 1, 0, 0, 1, 0, 1, 0, 0, 0, 3, '', '报名说明');
REPLACE INTO pw_activityfield VALUES(176, '请输入其它限制', 'specificuserlimit', 9, 8, 'text', '', 1, 0, 0, 1, 0, 1, 0, 0, 0, 14, '', '报名说明');
REPLACE INTO pw_activityfield VALUES(177, '性别限制', 'genderlimit', 9, 9, 'radio', 'a:3:{i:0;s:8:"1=全部";i:1;s:11:"2=仅男生";i:2;s:11:"3=仅女生";}', 1, 0, 0, 1, 0, 1, 0, 0, 0, 0, '', '报名说明');
REPLACE INTO pw_activityfield VALUES(178, '活动费用', 'fees', 9, 10, 'text', '', 1, 0, 0, 0, 0, 1, 1, 0, 1, 0, '(如 小孩 每位 10元，有车族 每位10 元等)', '费用说明');
REPLACE INTO pw_activityfield VALUES(179, '费用明细', 'feesdetail', 9, 11, 'text', '', 1, 0, 0, 0, 0, 1, 0, 0, 0, 0, '', '费用说明');
REPLACE INTO pw_activityfield VALUES(180, '支付方式', 'paymethod', 9, 12, 'radio', 'a:2:{i:0;s:11:"1=支付宝";i:1;s:14:"2=现金支付";}', 1, 0, 0, 1, 0, 1, 1, 0, 1, 0, '', '费用说明');
REPLACE INTO pw_activityfield VALUES(181, '活动时间', 'starttime', 10, 1, 'calendar', 'a:1:{s:9:"precision";s:6:"minute";}', 1, 0, 0, 1, 0, 1, 1, 0, 1, 18, '', '活动概况');
REPLACE INTO pw_activityfield VALUES(182, '-', 'endtime', 10, 1, 'calendar', 'a:1:{s:9:"precision";s:6:"minute";}', 1, 0, 0, 1, 0, 1, 1, 0, 1, 18, '', '活动概况');
REPLACE INTO pw_activityfield VALUES(183, '活动地点', 'location', 10, 2, 'text', '', 1, 0, 0, 1, 0, 1, 0, 0, 0, 40, '(多个活动地点用,隔开)', '活动概况');
REPLACE INTO pw_activityfield VALUES(184, '主题图片', 'picture1', 10, 3, 'upload', '', 1, 0, 0, 0, 0, 0, 0, 0, 1, 0, '(最多5张，支持gif、jpeg、jpg、bmp、png，小于2M)', '活动概况');
REPLACE INTO pw_activityfield VALUES(185, '主题图片二', 'picture2', 10, 3, 'upload', '', 1, 0, 0, 0, 0, 0, 0, 0, 1, 0, '', '活动概况');
REPLACE INTO pw_activityfield VALUES(186, '主题图片三', 'picture3', 10, 3, 'upload', '', 1, 0, 0, 0, 0, 0, 0, 0, 1, 0, '', '活动概况');
REPLACE INTO pw_activityfield VALUES(187, '主题图片四', 'picture4', 10, 3, 'upload', '', 1, 0, 0, 0, 0, 0, 0, 0, 1, 0, '', '活动概况');
REPLACE INTO pw_activityfield VALUES(188, '主题图片五', 'picture5', 10, 3, 'upload', '', 1, 0, 0, 0, 0, 0, 0, 0, 1, 0, '', '活动概况');
REPLACE INTO pw_activityfield VALUES(189, '联系人', 'contact', 10, 4, 'text', '', 1, 0, 0, 1, 0, 1, 1, 0, 1, 20, '', '活动概况');
REPLACE INTO pw_activityfield VALUES(190, '联系电话', 'telephone', 10, 5, 'text', '', 1, 0, 0, 1, 0, 1, 1, 0, 1, 40, '(多个联系电话用,隔开)', '活动概况');
REPLACE INTO pw_activityfield VALUES(191, '报名时间', 'signupstarttime', 10, 6, 'calendar', 'a:1:{s:9:"precision";s:6:"minute";}', 1, 0, 0, 1, 0, 1, 1, 0, 1, 18, '', '报名说明');
REPLACE INTO pw_activityfield VALUES(192, '-', 'signupendtime', 10, 6, 'calendar', 'a:1:{s:9:"precision";s:6:"minute";}', 1, 0, 0, 1, 0, 1, 1, 0, 1, 18, '', '报名说明');
REPLACE INTO pw_activityfield VALUES(193, '人数限制{@}最少', 'minparticipant', 10, 7, 'text', '', 1, 0, 0, 0, 0, 1, 0, 0, 0, 3, '', '报名说明');
REPLACE INTO pw_activityfield VALUES(194, '最多', 'maxparticipant', 10, 7, 'text', '', 1, 0, 0, 0, 0, 1, 0, 0, 0, 3, '(留空或0表示没有限制)', '报名说明');
REPLACE INTO pw_activityfield VALUES(195, '报名限制', 'userlimit', 10, 8, 'radio', 'a:2:{i:0;s:14:"1=所有用户";i:1;s:11:"2=仅好友";}', 1, 0, 0, 1, 0, 1, 0, 0, 0, 3, '', '报名说明');
REPLACE INTO pw_activityfield VALUES(196, '请输入其它限制', 'specificuserlimit', 10, 8, 'text', '', 1, 0, 0, 1, 0, 1, 0, 0, 0, 14, '', '报名说明');
REPLACE INTO pw_activityfield VALUES(197, '性别限制', 'genderlimit', 10, 9, 'radio', 'a:3:{i:0;s:8:"1=全部";i:1;s:11:"2=仅男生";i:2;s:11:"3=仅女生";}', 1, 0, 0, 1, 0, 1, 0, 0, 0, 0, '', '报名说明');
REPLACE INTO pw_activityfield VALUES(198, '活动费用', 'fees', 10, 10, 'text', '', 1, 0, 0, 0, 0, 1, 1, 0, 1, 0, '(如 小孩 每位 10元，有车族 每位10 元等)', '费用说明');
REPLACE INTO pw_activityfield VALUES(199, '费用明细', 'feesdetail', 10, 11, 'text', '', 1, 0, 0, 0, 0, 1, 0, 0, 0, 0, '', '费用说明');
REPLACE INTO pw_activityfield VALUES(200, '支付方式', 'paymethod', 10, 12, 'radio', 'a:2:{i:0;s:11:"1=支付宝";i:1;s:14:"2=现金支付";}', 1, 0, 0, 1, 0, 1, 1, 0, 1, 0, '', '费用说明');
REPLACE INTO pw_activityfield VALUES(201, '活动时间', 'starttime', 11, 1, 'calendar', 'a:1:{s:9:"precision";s:6:"minute";}', 1, 0, 0, 1, 0, 1, 1, 0, 1, 18, '', '活动概况');
REPLACE INTO pw_activityfield VALUES(202, '-', 'endtime', 11, 1, 'calendar', 'a:1:{s:9:"precision";s:6:"minute";}', 1, 0, 0, 1, 0, 1, 1, 0, 1, 18, '', '活动概况');
REPLACE INTO pw_activityfield VALUES(203, '活动地点', 'location', 11, 2, 'text', '', 1, 0, 0, 1, 0, 1, 0, 0, 0, 40, '(多个活动地点用,隔开)', '活动概况');
REPLACE INTO pw_activityfield VALUES(204, '主题图片', 'picture1', 11, 3, 'upload', '', 1, 0, 0, 0, 0, 0, 0, 0, 1, 0, '(最多5张，支持gif、jpeg、jpg、bmp、png，小于2M)', '活动概况');
REPLACE INTO pw_activityfield VALUES(205, '主题图片二', 'picture2', 11, 3, 'upload', '', 1, 0, 0, 0, 0, 0, 0, 0, 1, 0, '', '活动概况');
REPLACE INTO pw_activityfield VALUES(206, '主题图片三', 'picture3', 11, 3, 'upload', '', 1, 0, 0, 0, 0, 0, 0, 0, 1, 0, '', '活动概况');
REPLACE INTO pw_activityfield VALUES(207, '主题图片四', 'picture4', 11, 3, 'upload', '', 1, 0, 0, 0, 0, 0, 0, 0, 1, 0, '', '活动概况');
REPLACE INTO pw_activityfield VALUES(208, '主题图片五', 'picture5', 11, 3, 'upload', '', 1, 0, 0, 0, 0, 0, 0, 0, 1, 0, '', '活动概况');
REPLACE INTO pw_activityfield VALUES(209, '联系人', 'contact', 11, 4, 'text', '', 1, 0, 0, 1, 0, 1, 1, 0, 1, 20, '', '活动概况');
REPLACE INTO pw_activityfield VALUES(210, '联系电话', 'telephone', 11, 5, 'text', '', 1, 0, 0, 1, 0, 1, 1, 0, 1, 40, '(多个联系电话用,隔开)', '活动概况');
REPLACE INTO pw_activityfield VALUES(211, '报名时间', 'signupstarttime', 11, 6, 'calendar', 'a:1:{s:9:"precision";s:6:"minute";}', 1, 0, 0, 1, 0, 1, 1, 0, 1, 18, '', '报名说明');
REPLACE INTO pw_activityfield VALUES(212, '-', 'signupendtime', 11, 6, 'calendar', 'a:1:{s:9:"precision";s:6:"minute";}', 1, 0, 0, 1, 0, 1, 1, 0, 1, 18, '', '报名说明');
REPLACE INTO pw_activityfield VALUES(213, '人数限制{@}最少', 'minparticipant', 11, 7, 'text', '', 1, 0, 0, 0, 0, 1, 0, 0, 0, 3, '', '报名说明');
REPLACE INTO pw_activityfield VALUES(214, '最多', 'maxparticipant', 11, 7, 'text', '', 1, 0, 0, 0, 0, 1, 0, 0, 0, 3, '(留空或0表示没有限制)', '报名说明');
REPLACE INTO pw_activityfield VALUES(215, '报名限制', 'userlimit', 11, 8, 'radio', 'a:2:{i:0;s:14:"1=所有用户";i:1;s:11:"2=仅好友";}', 1, 0, 0, 1, 0, 1, 0, 0, 0, 3, '', '报名说明');
REPLACE INTO pw_activityfield VALUES(216, '请输入其它限制', 'specificuserlimit', 11, 8, 'text', '', 1, 0, 0, 1, 0, 1, 0, 0, 0, 14, '', '报名说明');
REPLACE INTO pw_activityfield VALUES(217, '性别限制', 'genderlimit', 11, 9, 'radio', 'a:3:{i:0;s:8:"1=全部";i:1;s:11:"2=仅男生";i:2;s:11:"3=仅女生";}', 1, 0, 0, 1, 0, 1, 0, 0, 0, 0, '', '报名说明');
REPLACE INTO pw_activityfield VALUES(218, '活动费用', 'fees', 11, 10, 'text', '', 1, 0, 0, 0, 0, 1, 1, 0, 1, 0, '(如 小孩 每位 10元，有车族 每位10 元等)', '费用说明');
REPLACE INTO pw_activityfield VALUES(219, '费用明细', 'feesdetail', 11, 11, 'text', '', 1, 0, 0, 0, 0, 1, 0, 0, 0, 0, '', '费用说明');
REPLACE INTO pw_activityfield VALUES(220, '支付方式', 'paymethod', 11, 12, 'radio', 'a:2:{i:0;s:11:"1=支付宝";i:1;s:14:"2=现金支付";}', 1, 0, 0, 1, 0, 1, 1, 0, 1, 0, '', '费用说明');
REPLACE INTO pw_activityfield VALUES(221, '活动时间', 'starttime', 12, 1, 'calendar', 'a:1:{s:9:"precision";s:6:"minute";}', 1, 0, 0, 1, 0, 1, 1, 0, 1, 18, '', '活动概况');
REPLACE INTO pw_activityfield VALUES(222, '-', 'endtime', 12, 1, 'calendar', 'a:1:{s:9:"precision";s:6:"minute";}', 1, 0, 0, 1, 0, 1, 1, 0, 1, 18, '', '活动概况');
REPLACE INTO pw_activityfield VALUES(223, '活动地点', 'location', 12, 2, 'text', '', 1, 0, 0, 1, 0, 1, 0, 0, 0, 40, '(多个活动地点用,隔开)', '活动概况');
REPLACE INTO pw_activityfield VALUES(224, '主题图片', 'picture1', 12, 3, 'upload', '', 1, 0, 0, 0, 0, 0, 0, 0, 1, 0, '(最多5张，支持gif、jpeg、jpg、bmp、png，小于2M)', '活动概况');
REPLACE INTO pw_activityfield VALUES(225, '主题图片二', 'picture2', 12, 3, 'upload', '', 1, 0, 0, 0, 0, 0, 0, 0, 1, 0, '', '活动概况');
REPLACE INTO pw_activityfield VALUES(226, '主题图片三', 'picture3', 12, 3, 'upload', '', 1, 0, 0, 0, 0, 0, 0, 0, 1, 0, '', '活动概况');
REPLACE INTO pw_activityfield VALUES(227, '主题图片四', 'picture4', 12, 3, 'upload', '', 1, 0, 0, 0, 0, 0, 0, 0, 1, 0, '', '活动概况');
REPLACE INTO pw_activityfield VALUES(228, '主题图片五', 'picture5', 12, 3, 'upload', '', 1, 0, 0, 0, 0, 0, 0, 0, 1, 0, '', '活动概况');
REPLACE INTO pw_activityfield VALUES(229, '联系人', 'contact', 12, 4, 'text', '', 1, 0, 0, 1, 0, 1, 1, 0, 1, 20, '', '活动概况');
REPLACE INTO pw_activityfield VALUES(230, '联系电话', 'telephone', 12, 5, 'text', '', 1, 0, 0, 1, 0, 1, 1, 0, 1, 40, '(多个联系电话用,隔开)', '活动概况');
REPLACE INTO pw_activityfield VALUES(231, '报名时间', 'signupstarttime', 12, 6, 'calendar', 'a:1:{s:9:"precision";s:6:"minute";}', 1, 0, 0, 1, 0, 1, 1, 0, 1, 18, '', '报名说明');
REPLACE INTO pw_activityfield VALUES(232, '-', 'signupendtime', 12, 6, 'calendar', 'a:1:{s:9:"precision";s:6:"minute";}', 1, 0, 0, 1, 0, 1, 1, 0, 1, 18, '', '报名说明');
REPLACE INTO pw_activityfield VALUES(233, '人数限制{@}最少', 'minparticipant', 12, 7, 'text', '', 1, 0, 0, 0, 0, 1, 0, 0, 0, 3, '', '报名说明');
REPLACE INTO pw_activityfield VALUES(234, '最多', 'maxparticipant', 12, 7, 'text', '', 1, 0, 0, 0, 0, 1, 0, 0, 0, 3, '(留空或0表示没有限制)', '报名说明');
REPLACE INTO pw_activityfield VALUES(235, '报名限制', 'userlimit', 12, 8, 'radio', 'a:2:{i:0;s:14:"1=所有用户";i:1;s:11:"2=仅好友";}', 1, 0, 0, 1, 0, 1, 0, 0, 0, 3, '', '报名说明');
REPLACE INTO pw_activityfield VALUES(236, '请输入其它限制', 'specificuserlimit', 12, 8, 'text', '', 1, 0, 0, 1, 0, 1, 0, 0, 0, 14, '', '报名说明');
REPLACE INTO pw_activityfield VALUES(237, '性别限制', 'genderlimit', 12, 9, 'radio', 'a:3:{i:0;s:8:"1=全部";i:1;s:11:"2=仅男生";i:2;s:11:"3=仅女生";}', 1, 0, 0, 1, 0, 1, 0, 0, 0, 0, '', '报名说明');
REPLACE INTO pw_activityfield VALUES(238, '活动费用', 'fees', 12, 10, 'text', '', 1, 0, 0, 0, 0, 1, 1, 0, 1, 0, '(如 小孩 每位 10元，有车族 每位10 元等)', '费用说明');
REPLACE INTO pw_activityfield VALUES(239, '费用明细', 'feesdetail', 12, 11, 'text', '', 1, 0, 0, 0, 0, 1, 0, 0, 0, 0, '', '费用说明');
REPLACE INTO pw_activityfield VALUES(240, '支付方式', 'paymethod', 12, 12, 'radio', 'a:2:{i:0;s:11:"1=支付宝";i:1;s:14:"2=现金支付";}', 1, 0, 0, 1, 0, 1, 1, 0, 1, 0, '', '费用说明');
REPLACE INTO pw_activityfield VALUES(241, '活动时间', 'starttime', 13, 1, 'calendar', 'a:1:{s:9:"precision";s:6:"minute";}', 1, 0, 0, 1, 0, 1, 1, 0, 1, 18, '', '活动概况');
REPLACE INTO pw_activityfield VALUES(242, '-', 'endtime', 13, 1, 'calendar', 'a:1:{s:9:"precision";s:6:"minute";}', 1, 0, 0, 1, 0, 1, 1, 0, 1, 18, '', '活动概况');
REPLACE INTO pw_activityfield VALUES(243, '活动地点', 'location', 13, 2, 'text', '', 1, 0, 0, 1, 0, 1, 0, 0, 0, 40, '(多个活动地点用,隔开)', '活动概况');
REPLACE INTO pw_activityfield VALUES(244, '主题图片', 'picture1', 13, 3, 'upload', '', 1, 0, 0, 0, 0, 0, 0, 0, 1, 0, '(最多5张，支持gif、jpeg、jpg、bmp、png，小于2M)', '活动概况');
REPLACE INTO pw_activityfield VALUES(245, '主题图片二', 'picture2', 13, 3, 'upload', '', 1, 0, 0, 0, 0, 0, 0, 0, 1, 0, '', '活动概况');
REPLACE INTO pw_activityfield VALUES(246, '主题图片三', 'picture3', 13, 3, 'upload', '', 1, 0, 0, 0, 0, 0, 0, 0, 1, 0, '', '活动概况');
REPLACE INTO pw_activityfield VALUES(247, '主题图片四', 'picture4', 13, 3, 'upload', '', 1, 0, 0, 0, 0, 0, 0, 0, 1, 0, '', '活动概况');
REPLACE INTO pw_activityfield VALUES(248, '主题图片五', 'picture5', 13, 3, 'upload', '', 1, 0, 0, 0, 0, 0, 0, 0, 1, 0, '', '活动概况');
REPLACE INTO pw_activityfield VALUES(249, '联系人', 'contact', 13, 4, 'text', '', 1, 0, 0, 1, 0, 1, 1, 0, 1, 20, '', '活动概况');
REPLACE INTO pw_activityfield VALUES(250, '联系电话', 'telephone', 13, 5, 'text', '', 1, 0, 0, 1, 0, 1, 1, 0, 1, 40, '(多个联系电话用,隔开)', '活动概况');
REPLACE INTO pw_activityfield VALUES(251, '报名时间', 'signupstarttime', 13, 6, 'calendar', 'a:1:{s:9:"precision";s:6:"minute";}', 1, 0, 0, 1, 0, 1, 1, 0, 1, 18, '', '报名说明');
REPLACE INTO pw_activityfield VALUES(252, '-', 'signupendtime', 13, 6, 'calendar', 'a:1:{s:9:"precision";s:6:"minute";}', 1, 0, 0, 1, 0, 1, 1, 0, 1, 18, '', '报名说明');
REPLACE INTO pw_activityfield VALUES(253, '人数限制{@}最少', 'minparticipant', 13, 7, 'text', '', 1, 0, 0, 0, 0, 1, 0, 0, 0, 3, '', '报名说明');
REPLACE INTO pw_activityfield VALUES(254, '最多', 'maxparticipant', 13, 7, 'text', 'a:1:{s:9:"alipaymax";s:2:"30";}', 1, 0, 0, 0, 0, 1, 0, 0, 0, 3, '(留空或0表示没有限制)', '报名说明');
REPLACE INTO pw_activityfield VALUES(255, '报名限制', 'userlimit', 13, 8, 'radio', 'a:2:{i:0;s:14:"1=所有用户";i:1;s:11:"2=仅好友";}', 1, 0, 0, 1, 0, 1, 0, 0, 0, 3, '', '报名说明');
REPLACE INTO pw_activityfield VALUES(256, '请输入其它限制', 'specificuserlimit', 13, 8, 'text', '', 1, 0, 0, 1, 0, 1, 0, 0, 0, 14, '', '报名说明');
REPLACE INTO pw_activityfield VALUES(257, '性别限制', 'genderlimit', 13, 9, 'radio', 'a:3:{i:0;s:8:"1=全部";i:1;s:11:"2=仅男生";i:2;s:11:"3=仅女生";}', 1, 0, 0, 1, 0, 1, 0, 0, 0, 0, '', '报名说明');
REPLACE INTO pw_activityfield VALUES(258, '活动费用', 'fees', 13, 10, 'text', '', 1, 0, 0, 0, 0, 1, 1, 0, 1, 0, '(如 小孩 每位 10元，有车族 每位10 元等)', '费用说明');
REPLACE INTO pw_activityfield VALUES(259, '费用明细', 'feesdetail', 13, 11, 'text', '', 1, 0, 0, 0, 0, 1, 0, 0, 0, 0, '', '费用说明');
REPLACE INTO pw_activityfield VALUES(260, '支付方式', 'paymethod', 13, 12, 'radio', 'a:2:{i:0;s:11:"1=支付宝";i:1;s:14:"2=现金支付";}', 1, 0, 0, 1, 0, 1, 1, 0, 1, 0, '', '费用说明');
REPLACE INTO pw_activityfield VALUES(261, '活动时间', 'starttime', 14, 1, 'calendar', 'a:1:{s:9:"precision";s:6:"minute";}', 1, 0, 0, 1, 0, 1, 1, 0, 1, 18, '', '活动概况');
REPLACE INTO pw_activityfield VALUES(262, '-', 'endtime', 14, 1, 'calendar', 'a:1:{s:9:"precision";s:6:"minute";}', 1, 0, 0, 1, 0, 1, 1, 0, 1, 18, '', '活动概况');
REPLACE INTO pw_activityfield VALUES(263, '活动地点', 'location', 14, 2, 'text', '', 1, 0, 0, 1, 0, 1, 0, 0, 0, 40, '(多个活动地点用,隔开)', '活动概况');
REPLACE INTO pw_activityfield VALUES(264, '主题图片', 'picture1', 14, 3, 'upload', '', 1, 0, 0, 0, 0, 0, 0, 0, 1, 0, '(最多5张，支持gif、jpeg、jpg、bmp、png，小于2M)', '活动概况');
REPLACE INTO pw_activityfield VALUES(265, '主题图片二', 'picture2', 14, 3, 'upload', '', 1, 0, 0, 0, 0, 0, 0, 0, 1, 0, '', '活动概况');
REPLACE INTO pw_activityfield VALUES(266, '主题图片三', 'picture3', 14, 3, 'upload', '', 1, 0, 0, 0, 0, 0, 0, 0, 1, 0, '', '活动概况');
REPLACE INTO pw_activityfield VALUES(267, '主题图片四', 'picture4', 14, 3, 'upload', '', 1, 0, 0, 0, 0, 0, 0, 0, 1, 0, '', '活动概况');
REPLACE INTO pw_activityfield VALUES(268, '主题图片五', 'picture5', 14, 3, 'upload', '', 1, 0, 0, 0, 0, 0, 0, 0, 1, 0, '', '活动概况');
REPLACE INTO pw_activityfield VALUES(269, '联系人', 'contact', 14, 4, 'text', '', 1, 0, 0, 1, 0, 1, 1, 0, 1, 20, '', '活动概况');
REPLACE INTO pw_activityfield VALUES(270, '联系电话', 'telephone', 14, 5, 'text', '', 1, 0, 0, 1, 0, 1, 1, 0, 1, 40, '(多个联系电话用,隔开)', '活动概况');
REPLACE INTO pw_activityfield VALUES(271, '报名时间', 'signupstarttime', 14, 6, 'calendar', 'a:1:{s:9:"precision";s:6:"minute";}', 1, 0, 0, 1, 0, 1, 1, 0, 1, 18, '', '报名说明');
REPLACE INTO pw_activityfield VALUES(272, '-', 'signupendtime', 14, 6, 'calendar', 'a:1:{s:9:"precision";s:6:"minute";}', 1, 0, 0, 1, 0, 1, 1, 0, 1, 18, '', '报名说明');
REPLACE INTO pw_activityfield VALUES(273, '人数限制{@}最少', 'minparticipant', 14, 7, 'text', '', 1, 0, 0, 0, 0, 1, 0, 0, 0, 3, '', '报名说明');
REPLACE INTO pw_activityfield VALUES(274, '最多', 'maxparticipant', 14, 7, 'text', 'a:1:{s:9:"alipaymax";s:2:"30";}', 1, 0, 0, 0, 0, 1, 0, 0, 0, 3, '(留空或0表示没有限制)', '报名说明');
REPLACE INTO pw_activityfield VALUES(275, '报名限制', 'userlimit', 14, 8, 'radio', 'a:2:{i:0;s:14:"1=所有用户";i:1;s:11:"2=仅好友";}', 1, 0, 0, 1, 0, 1, 0, 0, 0, 3, '', '报名说明');
REPLACE INTO pw_activityfield VALUES(276, '请输入其它限制', 'specificuserlimit', 14, 8, 'text', '', 1, 0, 0, 1, 0, 1, 0, 0, 0, 14, '', '报名说明');
REPLACE INTO pw_activityfield VALUES(277, '性别限制', 'genderlimit', 14, 9, 'radio', 'a:3:{i:0;s:8:"1=全部";i:1;s:11:"2=仅男生";i:2;s:11:"3=仅女生";}', 1, 0, 0, 1, 0, 1, 0, 0, 0, 0, '', '报名说明');
REPLACE INTO pw_activityfield VALUES(278, '活动费用', 'fees', 14, 10, 'text', '', 1, 0, 0, 0, 0, 1, 1, 0, 1, 0, '(如 小孩 每位 10元，有车族 每位10 元等)', '费用说明');
REPLACE INTO pw_activityfield VALUES(279, '费用明细', 'feesdetail', 14, 11, 'text', '', 1, 0, 0, 0, 0, 1, 0, 0, 0, 0, '', '费用说明');
REPLACE INTO pw_activityfield VALUES(280, '支付方式', 'paymethod', 14, 12, 'radio', 'a:2:{i:0;s:11:"1=支付宝";i:1;s:14:"2=现金支付";}', 1, 0, 0, 1, 0, 1, 1, 0, 1, 0, '', '费用说明');
REPLACE INTO pw_activityfield VALUES(281, '活动时间', 'starttime', 15, 1, 'calendar', 'a:1:{s:9:"precision";s:6:"minute";}', 1, 0, 0, 1, 0, 1, 1, 0, 1, 18, '', '活动概况');
REPLACE INTO pw_activityfield VALUES(282, '-', 'endtime', 15, 1, 'calendar', 'a:1:{s:9:"precision";s:6:"minute";}', 1, 0, 0, 1, 0, 1, 1, 0, 1, 18, '', '活动概况');
REPLACE INTO pw_activityfield VALUES(283, '活动地点', 'location', 15, 2, 'text', '', 1, 0, 0, 1, 0, 1, 0, 0, 0, 40, '(多个活动地点用,隔开)', '活动概况');
REPLACE INTO pw_activityfield VALUES(284, '主题图片', 'picture1', 15, 3, 'upload', '', 1, 0, 0, 0, 0, 0, 0, 0, 1, 0, '(最多5张，支持gif、jpeg、jpg、bmp、png，小于2M)', '活动概况');
REPLACE INTO pw_activityfield VALUES(285, '主题图片二', 'picture2', 15, 3, 'upload', '', 1, 0, 0, 0, 0, 0, 0, 0, 1, 0, '', '活动概况');
REPLACE INTO pw_activityfield VALUES(286, '主题图片三', 'picture3', 15, 3, 'upload', '', 1, 0, 0, 0, 0, 0, 0, 0, 1, 0, '', '活动概况');
REPLACE INTO pw_activityfield VALUES(287, '主题图片四', 'picture4', 15, 3, 'upload', '', 1, 0, 0, 0, 0, 0, 0, 0, 1, 0, '', '活动概况');
REPLACE INTO pw_activityfield VALUES(288, '主题图片五', 'picture5', 15, 3, 'upload', '', 1, 0, 0, 0, 0, 0, 0, 0, 1, 0, '', '活动概况');
REPLACE INTO pw_activityfield VALUES(289, '联系人', 'contact', 15, 4, 'text', '', 1, 0, 0, 1, 0, 1, 1, 0, 1, 20, '', '活动概况');
REPLACE INTO pw_activityfield VALUES(290, '联系电话', 'telephone', 15, 5, 'text', '', 1, 0, 0, 1, 0, 1, 1, 0, 1, 40, '(多个联系电话用,隔开)', '活动概况');
REPLACE INTO pw_activityfield VALUES(291, '报名时间', 'signupstarttime', 15, 6, 'calendar', 'a:1:{s:9:"precision";s:6:"minute";}', 1, 0, 0, 1, 0, 1, 1, 0, 1, 18, '', '报名说明');
REPLACE INTO pw_activityfield VALUES(292, '-', 'signupendtime', 15, 6, 'calendar', 'a:1:{s:9:"precision";s:6:"minute";}', 1, 0, 0, 1, 0, 1, 1, 0, 1, 18, '', '报名说明');
REPLACE INTO pw_activityfield VALUES(293, '人数限制{@}最少', 'minparticipant', 15, 7, 'text', '', 1, 0, 0, 0, 0, 1, 0, 0, 0, 3, '', '报名说明');
REPLACE INTO pw_activityfield VALUES(294, '最多', 'maxparticipant', 15, 7, 'text', '', 1, 0, 0, 0, 0, 1, 0, 0, 0, 3, '(留空或0表示没有限制)', '报名说明');
REPLACE INTO pw_activityfield VALUES(295, '报名限制', 'userlimit', 15, 8, 'radio', 'a:2:{i:0;s:14:"1=所有用户";i:1;s:11:"2=仅好友";}', 1, 0, 0, 1, 0, 1, 0, 0, 0, 3, '', '报名说明');
REPLACE INTO pw_activityfield VALUES(296, '请输入其它限制', 'specificuserlimit', 15, 8, 'text', '', 1, 0, 0, 1, 0, 1, 0, 0, 0, 14, '', '报名说明');
REPLACE INTO pw_activityfield VALUES(297, '性别限制', 'genderlimit', 15, 9, 'radio', 'a:3:{i:0;s:8:"1=全部";i:1;s:11:"2=仅男生";i:2;s:11:"3=仅女生";}', 1, 0, 0, 1, 0, 1, 0, 0, 0, 0, '', '报名说明');
REPLACE INTO pw_activityfield VALUES(298, '活动费用', 'fees', 15, 10, 'text', '', 1, 0, 0, 0, 0, 1, 1, 0, 1, 0, '(如 小孩 每位 10元，有车族 每位10 元等)', '费用说明');
REPLACE INTO pw_activityfield VALUES(299, '费用明细', 'feesdetail', 15, 11, 'text', '', 1, 0, 0, 0, 0, 1, 0, 0, 0, 0, '', '费用说明');
REPLACE INTO pw_activityfield VALUES(300, '支付方式', 'paymethod', 15, 12, 'radio', 'a:2:{i:0;s:11:"1=支付宝";i:1;s:14:"2=现金支付";}', 1, 0, 0, 1, 0, 1, 1, 0, 1, 0, '', '费用说明');
REPLACE INTO pw_activityfield VALUES(301, '活动时间', 'starttime', 16, 1, 'calendar', 'a:1:{s:9:"precision";s:6:"minute";}', 1, 0, 0, 1, 0, 1, 1, 0, 1, 18, '', '活动概况');
REPLACE INTO pw_activityfield VALUES(302, '-', 'endtime', 16, 1, 'calendar', 'a:1:{s:9:"precision";s:6:"minute";}', 1, 0, 0, 1, 0, 1, 1, 0, 1, 18, '', '活动概况');
REPLACE INTO pw_activityfield VALUES(303, '活动地点', 'location', 16, 2, 'text', '', 1, 0, 0, 1, 0, 1, 0, 0, 0, 40, '(多个活动地点用,隔开)', '活动概况');
REPLACE INTO pw_activityfield VALUES(304, '主题图片', 'picture1', 16, 3, 'upload', '', 1, 0, 0, 0, 0, 0, 0, 0, 1, 0, '(最多5张，支持gif、jpeg、jpg、bmp、png，小于2M)', '活动概况');
REPLACE INTO pw_activityfield VALUES(305, '主题图片二', 'picture2', 16, 3, 'upload', '', 1, 0, 0, 0, 0, 0, 0, 0, 1, 0, '', '活动概况');
REPLACE INTO pw_activityfield VALUES(306, '主题图片三', 'picture3', 16, 3, 'upload', '', 1, 0, 0, 0, 0, 0, 0, 0, 1, 0, '', '活动概况');
REPLACE INTO pw_activityfield VALUES(307, '主题图片四', 'picture4', 16, 3, 'upload', '', 1, 0, 0, 0, 0, 0, 0, 0, 1, 0, '', '活动概况');
REPLACE INTO pw_activityfield VALUES(308, '主题图片五', 'picture5', 16, 3, 'upload', '', 1, 0, 0, 0, 0, 0, 0, 0, 1, 0, '', '活动概况');
REPLACE INTO pw_activityfield VALUES(309, '联系人', 'contact', 16, 4, 'text', '', 1, 0, 0, 1, 0, 1, 1, 0, 1, 20, '', '活动概况');
REPLACE INTO pw_activityfield VALUES(310, '联系电话', 'telephone', 16, 5, 'text', '', 1, 0, 0, 1, 0, 1, 1, 0, 1, 40, '(多个联系电话用,隔开)', '活动概况');
REPLACE INTO pw_activityfield VALUES(311, '报名时间', 'signupstarttime', 16, 6, 'calendar', 'a:1:{s:9:"precision";s:6:"minute";}', 1, 0, 0, 1, 0, 1, 1, 0, 1, 18, '', '报名说明');
REPLACE INTO pw_activityfield VALUES(312, '-', 'signupendtime', 16, 6, 'calendar', 'a:1:{s:9:"precision";s:6:"minute";}', 1, 0, 0, 1, 0, 1, 1, 0, 1, 18, '', '报名说明');
REPLACE INTO pw_activityfield VALUES(313, '人数限制{@}最少', 'minparticipant', 16, 7, 'text', '', 1, 0, 0, 0, 0, 1, 0, 0, 0, 3, '', '报名说明');
REPLACE INTO pw_activityfield VALUES(314, '最多', 'maxparticipant', 16, 7, 'text', '', 1, 0, 0, 0, 0, 1, 0, 0, 0, 3, '(留空或0表示没有限制)', '报名说明');
REPLACE INTO pw_activityfield VALUES(315, '报名限制', 'userlimit', 16, 8, 'radio', 'a:2:{i:0;s:14:"1=所有用户";i:1;s:11:"2=仅好友";}', 1, 0, 0, 1, 0, 1, 0, 0, 0, 3, '', '报名说明');
REPLACE INTO pw_activityfield VALUES(316, '请输入其它限制', 'specificuserlimit', 16, 8, 'text', '', 1, 0, 0, 1, 0, 1, 0, 0, 0, 14, '', '报名说明');
REPLACE INTO pw_activityfield VALUES(317, '性别限制', 'genderlimit', 16, 9, 'radio', 'a:3:{i:0;s:8:"1=全部";i:1;s:11:"2=仅男生";i:2;s:11:"3=仅女生";}', 1, 0, 0, 1, 0, 1, 0, 0, 0, 0, '', '报名说明');
REPLACE INTO pw_activityfield VALUES(318, '活动费用', 'fees', 16, 10, 'text', '', 1, 0, 0, 0, 0, 1, 1, 0, 1, 0, '(如 小孩 每位 10元，有车族 每位10 元等)', '费用说明');
REPLACE INTO pw_activityfield VALUES(319, '费用明细', 'feesdetail', 16, 11, 'text', '', 1, 0, 0, 0, 0, 1, 0, 0, 0, 0, '', '费用说明');
REPLACE INTO pw_activityfield VALUES(320, '支付方式', 'paymethod', 16, 12, 'radio', 'a:2:{i:0;s:11:"1=支付宝";i:1;s:14:"2=现金支付";}', 1, 0, 0, 1, 0, 1, 1, 0, 1, 0, '', '费用说明');
REPLACE INTO pw_activityfield VALUES(321, '活动时间', 'starttime', 17, 1, 'calendar', 'a:1:{s:9:"precision";s:6:"minute";}', 1, 0, 0, 1, 0, 1, 1, 0, 1, 18, '', '活动概况');
REPLACE INTO pw_activityfield VALUES(322, '-', 'endtime', 17, 1, 'calendar', 'a:1:{s:9:"precision";s:6:"minute";}', 1, 0, 0, 1, 0, 1, 1, 0, 1, 18, '', '活动概况');
REPLACE INTO pw_activityfield VALUES(323, '活动地点', 'location', 17, 2, 'text', '', 1, 0, 0, 1, 0, 1, 0, 0, 0, 40, '(多个活动地点用,隔开)', '活动概况');
REPLACE INTO pw_activityfield VALUES(324, '主题图片', 'picture1', 17, 3, 'upload', '', 1, 0, 0, 0, 0, 0, 0, 0, 1, 0, '(最多5张，支持gif、jpeg、jpg、bmp、png，小于2M)', '活动概况');
REPLACE INTO pw_activityfield VALUES(325, '主题图片二', 'picture2', 17, 3, 'upload', '', 1, 0, 0, 0, 0, 0, 0, 0, 1, 0, '', '活动概况');
REPLACE INTO pw_activityfield VALUES(326, '主题图片三', 'picture3', 17, 3, 'upload', '', 1, 0, 0, 0, 0, 0, 0, 0, 1, 0, '', '活动概况');
REPLACE INTO pw_activityfield VALUES(327, '主题图片四', 'picture4', 17, 3, 'upload', '', 1, 0, 0, 0, 0, 0, 0, 0, 1, 0, '', '活动概况');
REPLACE INTO pw_activityfield VALUES(328, '主题图片五', 'picture5', 17, 3, 'upload', '', 1, 0, 0, 0, 0, 0, 0, 0, 1, 0, '', '活动概况');
REPLACE INTO pw_activityfield VALUES(329, '联系人', 'contact', 17, 4, 'text', '', 1, 0, 0, 1, 0, 1, 1, 0, 1, 20, '', '活动概况');
REPLACE INTO pw_activityfield VALUES(330, '联系电话', 'telephone', 17, 5, 'text', '', 1, 0, 0, 1, 0, 1, 1, 0, 1, 40, '(多个联系电话用,隔开)', '活动概况');
REPLACE INTO pw_activityfield VALUES(331, '报名时间', 'signupstarttime', 17, 6, 'calendar', 'a:1:{s:9:"precision";s:6:"minute";}', 1, 0, 0, 1, 0, 1, 1, 0, 1, 18, '', '报名说明');
REPLACE INTO pw_activityfield VALUES(332, '-', 'signupendtime', 17, 6, 'calendar', 'a:1:{s:9:"precision";s:6:"minute";}', 1, 0, 0, 1, 0, 1, 1, 0, 1, 18, '', '报名说明');
REPLACE INTO pw_activityfield VALUES(333, '人数限制{@}最少', 'minparticipant', 17, 7, 'text', '', 1, 0, 0, 0, 0, 1, 0, 0, 0, 3, '', '报名说明');
REPLACE INTO pw_activityfield VALUES(334, '最多', 'maxparticipant', 17, 7, 'text', '', 1, 0, 0, 0, 0, 1, 0, 0, 0, 3, '(留空或0表示没有限制)', '报名说明');
REPLACE INTO pw_activityfield VALUES(335, '报名限制', 'userlimit', 17, 8, 'radio', 'a:2:{i:0;s:14:"1=所有用户";i:1;s:11:"2=仅好友";}', 1, 0, 0, 1, 0, 1, 0, 0, 0, 3, '', '报名说明');
REPLACE INTO pw_activityfield VALUES(336, '请输入其它限制', 'specificuserlimit', 17, 8, 'text', '', 1, 0, 0, 1, 0, 1, 0, 0, 0, 14, '', '报名说明');
REPLACE INTO pw_activityfield VALUES(337, '性别限制', 'genderlimit', 17, 9, 'radio', 'a:3:{i:0;s:8:"1=全部";i:1;s:11:"2=仅男生";i:2;s:11:"3=仅女生";}', 1, 0, 0, 1, 0, 1, 0, 0, 0, 0, '', '报名说明');
REPLACE INTO pw_activityfield VALUES(338, '活动费用', 'fees', 17, 10, 'text', '', 1, 0, 0, 0, 0, 1, 1, 0, 1, 0, '(如 小孩 每位 10元，有车族 每位10 元等)', '费用说明');
REPLACE INTO pw_activityfield VALUES(339, '费用明细', 'feesdetail', 17, 11, 'text', '', 1, 0, 0, 0, 0, 1, 0, 0, 0, 0, '', '费用说明');
REPLACE INTO pw_activityfield VALUES(340, '支付方式', 'paymethod', 17, 12, 'radio', 'a:2:{i:0;s:11:"1=支付宝";i:1;s:14:"2=现金支付";}', 1, 0, 0, 1, 0, 1, 1, 0, 1, 0, '', '费用说明');

DROP TABLE IF EXISTS pw_activitymembers;
CREATE TABLE pw_activitymembers (
  `actuid` int(10) unsigned NOT NULL auto_increment,
  `fupid` mediumint(8) NOT NULL default '0',
  `tid` int(10) unsigned NOT NULL default '0',
  `uid` int(10) unsigned NOT NULL default '0',
  `actmid` tinyint(3) unsigned NOT NULL default '0',
  `username` varchar(15) NOT NULL default '',
  `signupdetail` varchar(255) NOT NULL default '',
  `signupnum` smallint(6) unsigned NOT NULL default '0',
  `nickname` varchar(255) NOT NULL default '',
  `totalcash` varchar(255) NOT NULL default '',
  `mobile` varchar(15) NOT NULL default '',
  `telephone` varchar(15) NOT NULL default '',
  `address` varchar(255) NOT NULL default '',
  `message` text NOT NULL,
  `ifanonymous` tinyint(3) NOT NULL default '0',
  `ifpay` tinyint(3) NOT NULL default '0',
  `signuptime` int(10) unsigned default '0',
  `fromuid` int(10) unsigned NOT NULL default '0',
  `fromusername` varchar(15) NOT NULL default '',
  `issubstitute` tinyint(3) NOT NULL default '0',
  `isadditional` tinyint(3) NOT NULL default '0',
  `isrefund` tinyint(3) NOT NULL default '0',
  `refundcost` varchar(255) NOT NULL default '',
  `refundreason` varchar(255) NOT NULL default '',
  `additionalreason` varchar(255) NOT NULL default '',
  `out_trade_no` varchar(255) NOT NULL default '0',
  `batch_detail_no` varchar(255) NOT NULL default '0',
  PRIMARY KEY  (`actuid`),
  KEY `idx_tid_uid` (`tid`,`uid`),
  KEY `idx_uid` (`uid`),
  KEY `idx_fupid` (`fupid`)
)  TYPE=MyISAM ;

DROP TABLE IF EXISTS pw_activitymodel;
CREATE TABLE pw_activitymodel (
  `actmid` smallint(6) unsigned NOT NULL auto_increment,
  `name` varchar(30) NOT NULL default '',
  `actid` tinyint(3) unsigned NOT NULL default '0',
  `ifable` tinyint(3) NOT NULL default '1',
  `vieworder` tinyint(3) NOT NULL default '0',
  `ifdel` tinyint(3) unsigned NOT NULL default '1',
  PRIMARY KEY  (`actmid`),
  KEY `idx_actid` (`actid`)
)  TYPE=MyISAM AUTO_INCREMENT=19 ;
REPLACE INTO pw_activitymodel VALUES(1, '爬山', 1, 1, 0, 0);
REPLACE INTO pw_activitymodel VALUES(2, '烧烤', 1, 1, 1, 0);
REPLACE INTO pw_activitymodel VALUES(3, '暴走/快闪', 1, 1, 2, 0);
REPLACE INTO pw_activitymodel VALUES(4, '自驾游', 1, 1, 3, 0);
REPLACE INTO pw_activitymodel VALUES(5, '农家乐', 1, 1, 4, 0);
REPLACE INTO pw_activitymodel VALUES(6, '室内运动', 2, 1, 0, 0);
REPLACE INTO pw_activitymodel VALUES(7, '室外运动', 2, 1, 1, 0);
REPLACE INTO pw_activitymodel VALUES(8, '真人CS', 2, 1, 2, 0);
REPLACE INTO pw_activitymodel VALUES(9, '看球赛', 2, 1, 3, 0);
REPLACE INTO pw_activitymodel VALUES(10, '聚餐/茶馆', 3, 1, 0, 0);
REPLACE INTO pw_activitymodel VALUES(11, '夜生活/舞会', 3, 1, 1, 0);
REPLACE INTO pw_activitymodel VALUES(12, '电影/K歌', 3, 1, 2, 0);
REPLACE INTO pw_activitymodel VALUES(13, '演出/展览', 3, 1, 3, 0);
REPLACE INTO pw_activitymodel VALUES(14, '桌游/棋牌', 3, 1, 4, 0);
REPLACE INTO pw_activitymodel VALUES(15, '相亲', 4, 1, 0, 0);
REPLACE INTO pw_activitymodel VALUES(16, '旅游', 4, 1, 1, 0);
REPLACE INTO pw_activitymodel VALUES(17, '其它', 4, 1, 2, 0);

DROP TABLE IF EXISTS pw_activitypaylog;
CREATE TABLE pw_activitypaylog (
  `actpid` mediumint(8) NOT NULL auto_increment,
  `tid` int(10) unsigned NOT NULL default '0',
  `actuid` int(10) unsigned NOT NULL default '0',
  `uid` int(10) unsigned NOT NULL default '0',
  `username` varchar(15) NOT NULL default '',
  `authorid` int(10) unsigned NOT NULL default '0',
  `author` varchar(15) NOT NULL default '',
  `fromuid` int(10) unsigned NOT NULL default '0',
  `fromusername` varchar(15) NOT NULL default '',
  `cost` varchar(255) NOT NULL default '',
  `costtype` tinyint(3) NOT NULL default '0',
  `status` tinyint(3) NOT NULL default '0',
  `createtime` int(10) unsigned NOT NULL default '0',
  `subject` varchar(100) NOT NULL default '',
  `wherefrom` tinyint(3) NOT NULL default '0',
  PRIMARY KEY  (`actpid`),
  KEY `idx_uid` (`uid`),
  KEY `idx_authorid` (`authorid`),
  KEY `idx_tid` (`tid`),
  KEY `idx_actuid_costtype` (`actuid`,`costtype`),
  KEY `idx_fromuid` (`fromuid`)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_activityvalue1;
CREATE TABLE pw_activityvalue1 (
  `tid` int(10) unsigned NOT NULL,
  `fid` smallint(6) unsigned NOT NULL default '0',
  `ifrecycle` tinyint(3) NOT NULL default '0',
  PRIMARY KEY  (`tid`)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_activityvalue10;
CREATE TABLE pw_activityvalue10 (
  `tid` int(10) unsigned NOT NULL,
  `fid` smallint(6) unsigned NOT NULL default '0',
  `ifrecycle` tinyint(3) NOT NULL default '0',
  PRIMARY KEY  (`tid`)
) TYPE=MyISAM ;

DROP TABLE IF EXISTS pw_activityvalue11;
CREATE TABLE pw_activityvalue11 (
  `tid` int(10) unsigned NOT NULL,
  `fid` smallint(6) unsigned NOT NULL default '0',
  `ifrecycle` tinyint(3) NOT NULL default '0',
  PRIMARY KEY  (`tid`)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_activityvalue12;
CREATE TABLE pw_activityvalue12 (
  `tid` int(10) unsigned NOT NULL,
  `fid` smallint(6) unsigned NOT NULL default '0',
  `ifrecycle` tinyint(3) NOT NULL default '0',
  PRIMARY KEY  (`tid`)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_activityvalue13;
CREATE TABLE pw_activityvalue13 (
  `tid` int(10) unsigned NOT NULL,
  `fid` smallint(6) unsigned NOT NULL default '0',
  `ifrecycle` tinyint(3) NOT NULL default '0',
  PRIMARY KEY  (`tid`)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_activityvalue14;
CREATE TABLE pw_activityvalue14 (
  `tid` int(10) unsigned NOT NULL,
  `fid` smallint(6) unsigned NOT NULL default '0',
  `ifrecycle` tinyint(3) NOT NULL default '0',
  PRIMARY KEY  (`tid`)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_activityvalue15;
CREATE TABLE pw_activityvalue15 (
  `tid` int(10) unsigned NOT NULL,
  `fid` smallint(6) unsigned NOT NULL default '0',
  `ifrecycle` tinyint(3) NOT NULL default '0',
  PRIMARY KEY  (`tid`)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_activityvalue16;
CREATE TABLE pw_activityvalue16 (
  `tid` int(10) unsigned NOT NULL,
  `fid` smallint(6) unsigned NOT NULL default '0',
  `ifrecycle` tinyint(3) NOT NULL default '0',
  PRIMARY KEY  (`tid`)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_activityvalue17;
CREATE TABLE pw_activityvalue17 (
  `tid` int(10) unsigned NOT NULL,
  `fid` smallint(6) unsigned NOT NULL default '0',
  `ifrecycle` tinyint(3) NOT NULL default '0',
  PRIMARY KEY  (`tid`)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_activityvalue2;
CREATE TABLE pw_activityvalue2 (
  `tid` int(10) unsigned NOT NULL,
  `fid` smallint(6) unsigned NOT NULL default '0',
  `ifrecycle` tinyint(3) NOT NULL default '0',
  PRIMARY KEY  (`tid`)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_activityvalue3;
CREATE TABLE pw_activityvalue3 (
  `tid` int(10) unsigned NOT NULL,
  `fid` smallint(6) unsigned NOT NULL default '0',
  `ifrecycle` tinyint(3) NOT NULL default '0',
  PRIMARY KEY  (`tid`)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_activityvalue4;
CREATE TABLE pw_activityvalue4 (
  `tid` int(10) unsigned NOT NULL,
  `fid` smallint(6) unsigned NOT NULL default '0',
  `ifrecycle` tinyint(3) NOT NULL default '0',
  PRIMARY KEY  (`tid`)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_activityvalue5;
CREATE TABLE pw_activityvalue5 (
  `tid` int(10) unsigned NOT NULL,
  `fid` smallint(6) unsigned NOT NULL default '0',
  `ifrecycle` tinyint(3) NOT NULL default '0',
  PRIMARY KEY  (`tid`)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_activityvalue6;
CREATE TABLE pw_activityvalue6 (
  `tid` int(10) unsigned NOT NULL,
  `fid` smallint(6) unsigned NOT NULL default '0',
  `ifrecycle` tinyint(3) NOT NULL default '0',
  PRIMARY KEY  (`tid`)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_activityvalue7;
CREATE TABLE pw_activityvalue7 (
  `tid` int(10) unsigned NOT NULL,
  `fid` smallint(6) unsigned NOT NULL default '0',
  `ifrecycle` tinyint(3) NOT NULL default '0',
  PRIMARY KEY  (`tid`)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_activityvalue8;
CREATE TABLE pw_activityvalue8 (
  `tid` int(10) unsigned NOT NULL,
  `fid` smallint(6) unsigned NOT NULL default '0',
  `ifrecycle` tinyint(3) NOT NULL default '0',
  PRIMARY KEY  (`tid`)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_activityvalue9;
CREATE TABLE pw_activityvalue9 (
  `tid` int(10) unsigned NOT NULL,
  `fid` smallint(6) unsigned NOT NULL default '0',
  `ifrecycle` tinyint(3) NOT NULL default '0',
  PRIMARY KEY  (`tid`)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_actmember;
CREATE TABLE pw_actmember (
  id mediumint(8) unsigned NOT NULL auto_increment,
  actid int(10) unsigned NOT NULL default '0',
  winduid int(10) unsigned NOT NULL default '0',
  state tinyint(3) NOT NULL default '0',
  applydate int(10) NOT NULL default '0',
  contact varchar(20) NOT NULL default '',
  message varchar(80) NOT NULL default '',
  PRIMARY KEY  (id),
  KEY idx_actid (actid),
  KEY idx_winduid (winduid)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_actmembers;
CREATE TABLE pw_actmembers (
  id mediumint(8) unsigned NOT NULL auto_increment,
  uid int(10) unsigned NOT NULL default '0',
  actid int(10) unsigned NOT NULL default '0',
  realname varchar( 30 ) NOT NULL default '',
  phone varchar(15) NOT NULL default '',
  mobile varchar(15) NOT NULL default '',
  address varchar(255) NOT NULL default '',
  anonymous tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (id),
  KEY idx_actid (actid)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS pw_administrators;
CREATE TABLE pw_administrators (
  uid int(10) unsigned NOT NULL default '0',
  username varchar(15) NOT NULL default '',
  groupid tinyint(3) NOT NULL default '0',
  groups varchar(255) NOT NULL default '',
  slog varchar(255) NOT NULL,
  PRIMARY KEY  (uid)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_adminlog;
CREATE TABLE pw_adminlog (
  id int(11) NOT NULL auto_increment,
  type varchar(10) NOT NULL default '',
  username1 varchar(30) NOT NULL default '',
  username2 varchar(30) NOT NULL default '',
  field1 varchar(30) NOT NULL default '',
  field2 varchar(30) NOT NULL default '',
  field3 varchar(255) NOT NULL default '',
  descrip text NOT NULL,
  timestamp int(10) NOT NULL default '0',
  ip varchar(20) NOT NULL default '',
  PRIMARY KEY  (id),
  KEY idx_type_timestamp (type,timestamp),
  KEY idx_username1 (username1),
  KEY idx_username2 (username2)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_adminset;
CREATE TABLE pw_adminset (
  gid tinyint(3) unsigned NOT NULL default '0',
  value text NOT NULL,
  PRIMARY KEY  (gid)
) TYPE=MyISAM;
INSERT INTO pw_adminset (gid, value) VALUES(3, 'a:69:{s:8:"creathtm";s:1:"1";s:9:"forumsell";s:1:"1";s:11:"singleright";s:1:"1";s:8:"tpccheck";s:1:"1";s:9:"postcheck";s:1:"1";s:6:"tagset";s:1:"1";s:6:"pwcode";s:1:"1";s:6:"setbwd";s:1:"1";s:7:"setform";s:1:"1";s:9:"topiccate";s:1:"1";s:8:"postcate";s:1:"1";s:8:"urlcheck";s:1:"1";s:10:"attachment";s:1:"1";s:11:"attachstats";s:1:"1";s:11:"attachrenew";s:1:"1";s:10:"app_photos";s:1:"1";s:9:"app_diary";s:1:"1";s:10:"app_groups";s:1:"1";s:9:"app_share";s:1:"1";s:9:"app_write";s:1:"1";s:7:"app_hot";s:1:"1";s:8:"checkreg";s:1:"1";s:10:"checkemail";s:1:"1";s:7:"banuser";s:1:"1";s:7:"viewban";s:1:"1";s:12:"customcredit";s:1:"1";s:5:"level";s:1:"1";s:9:"userstats";s:1:"1";s:7:"upgrade";s:1:"1";s:6:"uptime";s:1:"1";s:6:"sethtm";s:1:"1";s:9:"datastate";s:1:"1";s:7:"sitemap";s:1:"1";s:9:"postcache";s:1:"1";s:5:"ipban";s:1:"1";s:8:"ipstates";s:1:"1";s:8:"ipsearch";s:1:"1";s:11:"customfield";s:1:"1";s:11:"updatecache";s:1:"1";s:9:"creditdiy";s:1:"1";s:12:"creditchange";s:1:"1";s:6:"rebang";s:1:"1";s:7:"pwcache";s:1:"1";s:6:"report";s:1:"1";s:8:"forumlog";s:1:"1";s:9:"creditlog";s:1:"1";s:3:"app";s:1:"1";s:10:"hackcenter";s:1:"1";s:9:"setstyles";s:1:"1";s:12:"announcement";s:1:"1";s:8:"draftset";s:1:"1";s:8:"sendmail";s:1:"1";s:7:"sendmsg";s:1:"1";s:7:"present";s:1:"1";s:6:"setads";s:1:"1";s:5:"share";s:1:"1";s:9:"viewtoday";s:1:"1";s:5:"chmod";s:1:"1";s:9:"safecheck";s:1:"1";s:4:"help";s:1:"1";s:7:"message";s:1:"1";s:8:"guestdir";s:1:"1";s:7:"recycle";s:1:"1";s:8:"plantodo";s:1:"1";s:7:"addplan";s:1:"1";s:7:"userpay";s:1:"1";s:9:"orderlist";s:1:"1";s:15:"area_tplcontent";s:1:"1";s:10:"o_comments";s:1:"1";}');
INSERT INTO pw_adminset (gid, value) VALUES(4, 'a:19:{s:8:"tpccheck";s:1:"1";s:9:"postcheck";s:1:"1";s:6:"setbwd";s:1:"1";s:10:"attachment";s:1:"1";s:11:"attachstats";s:1:"1";s:11:"attachrenew";s:1:"1";s:7:"banuser";s:1:"1";s:7:"viewban";s:1:"1";s:9:"userstats";s:1:"1";s:9:"editgroup";s:1:"1";s:9:"postcache";s:1:"1";s:5:"ipban";s:1:"1";s:8:"ipsearch";s:1:"1";s:6:"report";s:1:"1";s:8:"forumlog";s:1:"1";s:9:"creditlog";s:1:"1";s:12:"announcement";s:1:"1";s:6:"setads";s:1:"1";s:5:"share";s:1:"1";}');
INSERT INTO pw_adminset (gid, value) VALUES(5, 'a:6:{s:7:"banuser";s:1:"1";s:7:"viewban";s:1:"1";s:6:"report";s:1:"1";s:8:"forumlog";s:1:"1";s:9:"creditlog";s:1:"1";s:12:"announcement";s:1:"1";}');

DROP TABLE IF EXISTS pw_advert;
CREATE TABLE pw_advert (
  id int(10) unsigned NOT NULL auto_increment,
  type tinyint(3) NOT NULL default '0',
  uid int(10) unsigned NOT NULL default '0',
  ckey varchar(32) NOT NULL default '',
  stime int(10) unsigned NOT NULL default '0',
  etime int(10) unsigned NOT NULL default '0',
  ifshow tinyint(3) NOT NULL default '0',
  orderby tinyint(3) NOT NULL default '0',
  descrip varchar(255) NOT NULL,
  config text NOT NULL,
  PRIMARY KEY (id)
) TYPE=MyISAM AUTO_INCREMENT=101 ;

DROP TABLE IF EXISTS pw_announce;
CREATE TABLE pw_announce (
  aid smallint(6) unsigned NOT NULL auto_increment,
  fid smallint(6) NOT NULL default '-1',
  ifopen tinyint(3) NOT NULL default '0',
  ifconvert tinyint(3) NOT NULL default '0',
  vieworder smallint(6) NOT NULL default '0',
  author varchar(15) NOT NULL default '',
  startdate varchar(15) NOT NULL default '',
  url varchar(80) NOT NULL default '',
  enddate varchar(15) NOT NULL default '',
  subject varchar(100) NOT NULL default '',
  content mediumtext NOT NULL,
  PRIMARY KEY  (aid),
  KEY idx_vieworder_startdate (vieworder,startdate),
  KEY idx_fid (fid)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_area_level;
CREATE TABLE pw_area_level (
    uid int(10) unsigned not null default '0',
    username varchar(15) NOT NULL DEFAULT '',
    hasedit tinyint(3)  not null default '0',
    hasattr tinyint(3) not null default '0',
    super tinyint(3) not null default '0',
    level text,
    key idx_uid (uid)
)ENGINE=MyISAM;

DROP TABLE IF EXISTS pw_argument;
CREATE TABLE pw_argument (
  tid int(10) unsigned NOT NULL,
  cyid smallint(6) unsigned NOT NULL default '0',
  topped tinyint(3) unsigned NOT NULL,
  postdate int(10) unsigned NOT NULL default '0',
  lastpost int(10) unsigned NOT NULL default '0',
  digest TINYINT(1) NOT NULL DEFAULT '0',
  titlefont VARCHAR(15) NOT NULL default '',
  toolfield VARCHAR(21) NOT NULL default '',
  PRIMARY KEY (tid),
  KEY idx_cyid_topped_lastpost (cyid,topped,lastpost),
  KEY idx_lastpost (lastpost),
  KEY idx_postdate (postdate),
  KEY idx_digest (digest)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_attachbuy;
CREATE TABLE pw_attachbuy (
  aid mediumint(8) unsigned NOT NULL,
  uid int(10) unsigned NOT NULL default '0',
  ctype varchar(20) NOT NULL default '',
  cost smallint(6) unsigned NOT NULL,
  createdtime int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY  (aid,uid)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_attachdownload;
CREATE TABLE IF NOT EXISTS `pw_attachdownload` (
  `aid` int(10) unsigned NOT NULL,
  `uid` int(10) unsigned NOT NULL,
  `ctype` varchar(20) NOT NULL DEFAULT '0',
  `cost` smallint(6) unsigned NOT NULL,
  `createdtime` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`aid`,`uid`)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_attachs;
CREATE TABLE pw_attachs (
  aid int(10) unsigned NOT NULL auto_increment,
  fid smallint(6) unsigned NOT NULL default '0',
  uid int(10) unsigned NOT NULL default '0',
  tid int(10) unsigned NOT NULL default '0',
  pid int(10) unsigned NOT NULL default '0',
  did int(10) unsigned NOT NULL default '0',
  name varchar(255) NOT NULL default '',
  type varchar(30) NOT NULL default '',
  size int(10) unsigned NOT NULL default '0',
  attachurl varchar(80) NOT NULL default '0',
  hits mediumint(8) unsigned NOT NULL default '0',
  needrvrc smallint(6) unsigned NOT NULL default '0',
  special tinyint(3) unsigned NOT NULL default '0',
  ctype varchar(20) NOT NULL default '',
  uploadtime int(10) NOT NULL default '0',
  descrip varchar(100) NOT NULL default '',
  ifthumb tinyint(3) NOT NULL default '0',
  mid int(10) unsigned NOT NULL default '0',
  PRIMARY KEY (aid),
  KEY idx_fid (fid),
  KEY idx_uid (uid),
  KEY idx_did (did),
  KEY idx_type (type),
  KEY idx_tid_pid (tid,pid)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_attention;
CREATE TABLE pw_attention (
  `uid` int(10) NOT NULL default '0',
  `friendid` int(10) NOT NULL default '0',
  `joindate` int(10) NOT NULL default '0',
  PRIMARY KEY  (`friendid`,`uid`),
  KEY `idx_uid_joindate` (`uid`,`joindate`),
  KEY `idx_friendid_joindate` (`friendid`,`joindate`),
  KEY `idx_joindate` (`joindate`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS pw_attention_blacklist;
CREATE TABLE pw_attention_blacklist (
  `uid` int(10) unsigned NOT NULL,
  `touid` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`uid`,`touid`)
) TYPE=MyISAM;

DROP TABLE IF EXISTS `pw_ban`;
CREATE TABLE IF NOT EXISTS `pw_ban` (
  `id` mediumint(8) unsigned not null auto_increment,
  `uid` int(10) unsigned not null default '0',
  `username` varchar(15) not null default '',
  `type` tinyint(3) unsigned not null default '0',
  `admin` varchar(15) not null default '',
  `reason` varchar(100) not null default '',
  `time` int(10) unsigned not null default '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_type_uid` (`type`,`uid`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS pw_banuser;
CREATE TABLE pw_banuser (
  id mediumint(8) unsigned NOT NULL auto_increment,
  uid int(10) unsigned NOT NULL default '0',
  fid smallint(6) unsigned NOT NULL default '0',
  type tinyint(3) NOT NULL default '0',
  startdate int(10) NOT NULL default '0',
  days smallint(6) NOT NULL default '0',
  admin varchar(15) NOT NULL default '',
  reason varchar(80) NOT NULL default '',
  PRIMARY KEY  (id),
  UNIQUE KEY idx_uid_fid (uid,fid)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_bbsinfo;
CREATE TABLE pw_bbsinfo (
  id smallint(3) unsigned NOT NULL auto_increment,
  newmember varchar(15) NOT NULL default '',
  totalmember mediumint(8) unsigned NOT NULL default '0',
  higholnum mediumint(8) unsigned NOT NULL default '0',
  higholtime int(10) unsigned NOT NULL default '0',
  tdtcontrol int(10) unsigned NOT NULL default '0',
  yposts mediumint(8) unsigned NOT NULL default '0',
  hposts mediumint(8) unsigned NOT NULL default '0',
  hit_tdtime int(10) unsigned NOT NULL default '0',
  hit_control tinyint(3) unsigned NOT NULL default '0',
  plantime int(10) NOT NULL default '0',
  o_post int(10) unsigned NOT NULL default '0',
  o_tpost int(10) unsigned NOT NULL default '0',
  last_statistictime INT NOT NULL DEFAULT '0',
  KEY id (id)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_buyadvert;
CREATE TABLE pw_buyadvert (
  id int(10) unsigned NOT NULL default '0',
  uid int(10) unsigned NOT NULL default '0',
  ifcheck tinyint(3) NOT NULL default '0',
  lasttime int(10) NOT NULL default '0',
  config text NOT NULL,
  PRIMARY KEY (id,uid)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_cache;
CREATE TABLE pw_cache (
  name varchar(20) NOT NULL default '',
  cache mediumtext NOT NULL,
  time int(10) NOT NULL default '0',
  PRIMARY KEY (name)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_cachedata;
CREATE TABLE pw_cachedata (
  id int(10) unsigned NOT NULL auto_increment,
  invokepieceid smallint(6) unsigned NOT NULL default '0',
  fid smallint(6) unsigned NOT NULL default '0',
  loopid smallint(6) unsigned NOT NULL default '0',
  data text NOT NULL,
  cachetime int(10) NOT NULL default '0',
  PRIMARY KEY (id),
  UNIQUE KEY idx_invokepieceid_fid_loopid (invokepieceid,fid,loopid)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_cache_members;
CREATE TABLE pw_cache_members(
    ckey char(32) not null default '',
    cvalue text not null,
    expire int(10) unsigned not null default '0',
    primary key (ckey)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_channel;
CREATE TABLE pw_channel (
  id smallint(6) NOT NULL AUTO_INCREMENT,
  name varchar(20) NOT NULL default '',
  alias varchar(20) NOT NULL default '',
  queue smallint(6) NOT NULL default '0',
  relate_theme varchar(20) NOT NULL default 'default',
  domain_band varchar(50) NOT NULL default '',
  metatitle varchar(255) NOT NULL default '',
  metadescrip varchar(255) NOT NULL default '',
  metakeywords varchar(255) NOT NULL default '',
  statictime int(10) unsigned NOT NULL default '0',
  PRIMARY KEY (id),
  UNIQUE KEY idx_alias (alias),
  KEY idx_relatetheme (relate_theme),
  KEY idx_queue (queue)
) ENGINE=MyISAM;
REPLACE INTO pw_channel VALUES(1, '首页', 'home','', 'home', '', '', '', '', 0);
REPLACE INTO pw_channel VALUES(2, '亲子', 'baby','', 'baby', '', '', '', '', 0);
REPLACE INTO pw_channel VALUES(3, '美食', 'delicious','', 'delicious', '', '', '', '', 0);
REPLACE INTO pw_channel VALUES(4, '汽车', 'auto','', 'auto', '', '', '', '', 0);
REPLACE INTO pw_channel VALUES(5, '家装', 'decoration','', 'decoration', '', '', '', '', 0);

DROP TABLE IF EXISTS pw_clientorder;
CREATE TABLE pw_clientorder (
  id int(11) NOT NULL auto_increment,
  order_no varchar(30) NOT NULL default '',
  type tinyint(3) UNSIGNED NOT NULL,
  uid int(10) unsigned NOT NULL default '0',
  paycredit varchar(15) NOT NULL default '',
  price decimal(8,2) NOT NULL DEFAULT '0',
  payemail varchar(60) NOT NULL default '',
  number smallint(6) NOT NULL default '0',
  date int(10) NOT NULL default '0',
  state tinyint(3) NOT NULL default '0',
  extra_1 int(10) NOT NULL default '0',
  PRIMARY KEY (id),
  KEY idx_uid (uid),
  KEY idx_orderno (order_no)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_cmembers;
CREATE TABLE pw_cmembers (
  id mediumint(9) NOT NULL auto_increment,
  uid int(10) unsigned NOT NULL default '0',
  username varchar(20) NOT NULL default '',
  realname varchar(20) NOT NULL default '',
  ifadmin tinyint(3) NOT NULL default '0',
  gender tinyint(3) NOT NULL default '0',
  tel varchar(15) NOT NULL default '',
  email varchar(50) NOT NULL default '',
  colonyid smallint(6) NOT NULL default '0',
  address varchar(255) NOT NULL default '',
  introduce varchar(255) NOT NULL default '',
  addtime int(10) unsigned NOT NULL default '0',
  lastvisit int(10) unsigned NOT NULL default '0',
  lastpost int(10) unsigned NOT NULL default '0',
  PRIMARY KEY (id),
  UNIQUE KEY idx_colonyid_uid (colonyid,uid),
  KEY idx_uid (uid)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_cms_article;
CREATE TABLE pw_cms_article (
  `article_id` mediumint(8) NOT NULL AUTO_INCREMENT,
  `subject` varchar(100) NOT NULL default '',
  `descrip` varchar(255) NOT NULL default '',
  `author` varchar(15) NOT NULL default '',
  `username` varchar(15) NOT NULL default '',
  `userid` int(10) unsigned NOT NULL default '0',
  `jumpurl` varchar(255) NOT NULL default '',
  `frominfo` varchar(100) NOT NULL default '',
  `fromurl` varchar(255) NOT NULL default '',
  `column_id` smallint(6) NOT NULL default '0',
  `ifcheck` tinyint(3) NOT NULL default '1',
  `postdate` int(10) NOT NULL default '0',
  `modifydate` int(10) NOT NULL default '0',
  `ifattach` tinyint(3) NOT NULL default '0',
  `sourcetype` varchar(30) NOT NULL default '',
  `sourceid` int(10) NOT NULL default '0',
  PRIMARY KEY (`article_id`),
  KEY `idx_columnid` (`column_id`)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_cms_articlecontent;
CREATE TABLE pw_cms_articlecontent (
  `article_id` mediumint(8) NOT NULL,
  `content` text NOT NULL,
  `relatearticle` text NOT NULL,
  PRIMARY KEY (`article_id`)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_cms_articleextend;
CREATE TABLE pw_cms_articleextend (
  `article_id` mediumint(8) NOT NULL,
  `hits` int(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`article_id`)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_cms_attach;
CREATE TABLE pw_cms_attach (
  `attach_id` mediumint(8) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL default '',
  `descrip` varchar(255) NOT NULL default '',
  `article_id` mediumint(8) NOT NULL default '0',
  `type` varchar(10) NOT NULL default '',
  `size` int(10) NOT NULL default '0',
  `uploadtime` int(10) NOT NULL default '0',
  `attachurl` varchar(255) NOT NULL default '',
  `ifthumb` tinyint(3) NOT NULL default '0',
  PRIMARY KEY (`attach_id`),
  KEY `idx_articleid` (`article_id`)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_cms_column;
CREATE TABLE pw_cms_column (
  `column_id` smallint(6) NOT NULL AUTO_INCREMENT,
  `parent_id` smallint(6) NOT NULL default '0',
  `name` varchar(50) NOT NULL default '',
  `order` smallint(6) NOT NULL default '0',
  `allowoffer` tinyint(1) NOT NULL default '0',
  `seotitle` varchar(255) NOT NULL default '',
  `seodesc` varchar(255) NOT NULL default '',
  `seokeywords` varchar(255) NOT NULL default '',
  PRIMARY KEY (`column_id`)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_cms_purview;
CREATE TABLE pw_cms_purview (
  `purview_id` smallint(6) NOT NULL AUTO_INCREMENT,
  `username` varchar(15) NOT NULL default '',
  `super` tinyint(3) NOT NULL default '0',
  `columns` text NOT NULL,
  PRIMARY KEY (`purview_id`)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_cnalbum;
CREATE TABLE pw_cnalbum (
  aid int(11) unsigned not null AUTO_INCREMENT,
  aname varchar(50) NOT NULL default '',
  aintro varchar(200) NOT NULL default '',
  atype smallint(6) NOT NULL default '0',
  private tinyint(3) unsigned NOT NULL,
  albumpwd varchar(40) NOT NULL default '',
  ownerid int(10) unsigned NOT NULL default '0',
  owner varchar(15) NOT NULL default '',
  photonum smallint(6) NOT NULL default '0',
  lastphoto varchar(100) NOT NULL default '',
  lasttime int(10) unsigned NOT NULL default '0',
  lastpid varchar(100) NOT NULL default '',
  crtime int(10) NOT NULL default '0',
  memopen tinyint(3) NOT NULL default '1',
  isdefault tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (aid),
  KEY idx_atype_ownerid (atype,ownerid)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_cnclass;
CREATE TABLE pw_cnclass (
  fid smallint(6) unsigned NOT NULL,
  cname varchar(20) NOT NULL default '',
  ifopen tinyint(3) unsigned NOT NULL default '0',
  cnsum int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (fid)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_cnlevel;
CREATE TABLE pw_cnlevel (
  id mediumint(8) NOT NULL AUTO_INCREMENT,
  ltype enum('common','special') NOT NULL default 'common',
  ltitle varchar(60) NOT NULL default '',
  lpoint int(10) unsigned NOT NULL default '0',
  albumnum mediumint(8) unsigned NOT NULL default '0',
  maxphotonum mediumint(8) unsigned NOT NULL default '0',
  maxmember mediumint(8) unsigned NOT NULL default '0',
  bbsmode tinyint(3) unsigned NOT NULL default '0',
  allowmerge tinyint(3) unsigned NOT NULL default '0',
  allowattorn tinyint(3) unsigned NOT NULL default '0',
  allowdisband tinyint(3) unsigned NOT NULL default '0',
  pictopic tinyint(3) unsigned NOT NULL default '0',
  allowstyle tinyint(3) unsigned NOT NULL default '0',
  topicadmin TEXT NOT NULL,
  modeset text NOT NULL,
  layout text NOT NULL,
  PRIMARY KEY (id)
) ENGINE=MyISAM;
REPLACE INTO pw_cnlevel VALUES('1','common','初级群组','0','10','60','100','0','1','1','0','0','1','a:7:{s:3:"del";s:1:"1";s:9:"highlight";s:1:"0";s:4:"lock";s:1:"0";s:9:"pushtopic";s:1:"0";s:9:"downtopic";s:1:"0";s:8:"toptopic";s:1:"1";s:6:"digest";s:1:"1";}','a:5:{s:6:"thread";a:2:{s:9:"vieworder";s:1:"0";s:5:"title";s:6:"话题";}s:6:"active";a:2:{s:9:"vieworder";s:1:"0";s:5:"title";s:6:"活动";}s:5:"write";a:3:{s:6:"ifopen";s:1:"1";s:9:"vieworder";s:1:"1";s:5:"title";s:9:"讨论区";}s:6:"galbum";a:3:{s:6:"ifopen";s:1:"1";s:9:"vieworder";s:1:"2";s:5:"title";s:6:"相册";}s:6:"member";a:3:{s:6:"ifopen";s:1:"1";s:9:"vieworder";s:1:"3";s:5:"title";s:6:"成员";}}','a:4:{s:6:"thread";a:2:{s:9:"vieworder";s:1:"0";s:3:"num";s:1:"5";}s:6:"active";a:2:{s:9:"vieworder";s:1:"0";s:3:"num";s:1:"4";}s:5:"write";a:3:{s:6:"ifopen";s:1:"1";s:9:"vieworder";s:1:"1";s:3:"num";s:1:"5";}s:6:"galbum";a:3:{s:6:"ifopen";s:1:"1";s:9:"vieworder";s:1:"2";s:3:"num";s:2:"10";}}');
REPLACE INTO pw_cnlevel VALUES('2','common','中级群组','500','20','60','200','0','1','1','0','0','1','a:7:{s:3:"del";s:1:"1";s:9:"highlight";s:1:"0";s:4:"lock";s:1:"0";s:9:"pushtopic";s:1:"0";s:9:"downtopic";s:1:"0";s:8:"toptopic";s:1:"1";s:6:"digest";s:1:"1";}','a:5:{s:6:"active";a:2:{s:9:"vieworder";s:1:"0";s:5:"title";s:6:"活动";}s:5:"write";a:3:{s:6:"ifopen";s:1:"1";s:9:"vieworder";s:1:"1";s:5:"title";s:9:"讨论区";}s:6:"thread";a:3:{s:6:"ifopen";s:1:"1";s:9:"vieworder";s:1:"2";s:5:"title";s:6:"话题";}s:6:"galbum";a:3:{s:6:"ifopen";s:1:"1";s:9:"vieworder";s:1:"3";s:5:"title";s:6:"相册";}s:6:"member";a:3:{s:6:"ifopen";s:1:"1";s:9:"vieworder";s:1:"4";s:5:"title";s:6:"成员";}}','a:4:{s:6:"active";a:2:{s:9:"vieworder";s:1:"0";s:3:"num";s:1:"4";}s:5:"write";a:3:{s:6:"ifopen";s:1:"1";s:9:"vieworder";s:1:"1";s:3:"num";s:1:"5";}s:6:"thread";a:3:{s:6:"ifopen";s:1:"1";s:9:"vieworder";s:1:"2";s:3:"num";s:1:"5";}s:6:"galbum";a:3:{s:6:"ifopen";s:1:"1";s:9:"vieworder";s:1:"3";s:3:"num";s:2:"10";}}');
REPLACE INTO pw_cnlevel VALUES('3','common','高级群组','1000','50','100','500','0','0','0','0','0','1','a:7:{s:3:"del";s:1:"1";s:9:"highlight";s:1:"1";s:4:"lock";s:1:"0";s:9:"pushtopic";s:1:"0";s:9:"downtopic";s:1:"0";s:8:"toptopic";s:1:"1";s:6:"digest";s:1:"1";}','a:5:{s:5:"write";a:3:{s:6:"ifopen";s:1:"1";s:9:"vieworder";s:1:"1";s:5:"title";s:9:"讨论区";}s:6:"thread";a:3:{s:6:"ifopen";s:1:"1";s:9:"vieworder";s:1:"2";s:5:"title";s:6:"话题";}s:6:"active";a:3:{s:6:"ifopen";s:1:"1";s:9:"vieworder";s:1:"3";s:5:"title";s:6:"活动";}s:6:"galbum";a:3:{s:6:"ifopen";s:1:"1";s:9:"vieworder";s:1:"4";s:5:"title";s:6:"相册";}s:6:"member";a:3:{s:6:"ifopen";s:1:"1";s:9:"vieworder";s:1:"5";s:5:"title";s:6:"成员";}}','a:4:{s:5:"write";a:3:{s:6:"ifopen";s:1:"1";s:9:"vieworder";s:1:"1";s:3:"num";s:1:"5";}s:6:"thread";a:3:{s:6:"ifopen";s:1:"1";s:9:"vieworder";s:1:"2";s:3:"num";s:1:"5";}s:6:"active";a:3:{s:6:"ifopen";s:1:"1";s:9:"vieworder";s:1:"3";s:3:"num";s:1:"4";}s:6:"galbum";a:3:{s:6:"ifopen";s:1:"1";s:9:"vieworder";s:1:"4";s:3:"num";s:2:"10";}}');
REPLACE INTO pw_cnlevel VALUES('4','special','官方群组','0','0','0','0','0','1','1','1','0','1','a:7:{s:3:"del";s:1:"1";s:9:"highlight";s:1:"1";s:4:"lock";s:1:"1";s:9:"pushtopic";s:1:"1";s:9:"downtopic";s:1:"1";s:8:"toptopic";s:1:"1";s:6:"digest";s:1:"1";}','a:5:{s:5:"write";a:3:{s:6:"ifopen";s:1:"1";s:9:"vieworder";s:1:"1";s:5:"title";s:9:"讨论区";}s:6:"thread";a:3:{s:6:"ifopen";s:1:"1";s:9:"vieworder";s:1:"2";s:5:"title";s:6:"话题";}s:6:"active";a:3:{s:6:"ifopen";s:1:"1";s:9:"vieworder";s:1:"3";s:5:"title";s:6:"活动";}s:6:"galbum";a:3:{s:6:"ifopen";s:1:"1";s:9:"vieworder";s:1:"4";s:5:"title";s:6:"相册";}s:6:"member";a:3:{s:6:"ifopen";s:1:"1";s:9:"vieworder";s:1:"5";s:5:"title";s:6:"成员";}}','a:4:{s:5:"write";a:3:{s:6:"ifopen";s:1:"1";s:9:"vieworder";s:1:"1";s:3:"num";s:1:"5";}s:6:"thread";a:3:{s:6:"ifopen";s:1:"1";s:9:"vieworder";s:1:"2";s:3:"num";s:1:"5";}s:6:"active";a:3:{s:6:"ifopen";s:1:"1";s:9:"vieworder";s:1:"3";s:3:"num";s:1:"4";}s:6:"galbum";a:3:{s:6:"ifopen";s:1:"1";s:9:"vieworder";s:1:"4";s:3:"num";s:2:"10";}}');
REPLACE INTO pw_cnlevel VALUES('5','special','商业群组','0','100','100','500','0','0','0','0','0','1','a:7:{s:3:"del";s:1:"1";s:9:"highlight";s:1:"1";s:4:"lock";s:1:"1";s:9:"pushtopic";s:1:"1";s:9:"downtopic";s:1:"1";s:8:"toptopic";s:1:"1";s:6:"digest";s:1:"1";}','a:5:{s:5:"write";a:3:{s:6:"ifopen";s:1:"1";s:9:"vieworder";s:1:"1";s:5:"title";s:9:"讨论区";}s:6:"thread";a:3:{s:6:"ifopen";s:1:"1";s:9:"vieworder";s:1:"2";s:5:"title";s:6:"话题";}s:6:"active";a:3:{s:6:"ifopen";s:1:"1";s:9:"vieworder";s:1:"3";s:5:"title";s:6:"活动";}s:6:"galbum";a:3:{s:6:"ifopen";s:1:"1";s:9:"vieworder";s:1:"4";s:5:"title";s:6:"相册";}s:6:"member";a:3:{s:6:"ifopen";s:1:"1";s:9:"vieworder";s:1:"5";s:5:"title";s:9:"VIP会员";}}','a:4:{s:5:"write";a:3:{s:6:"ifopen";s:1:"1";s:9:"vieworder";s:1:"1";s:3:"num";s:1:"5";}s:6:"thread";a:3:{s:6:"ifopen";s:1:"1";s:9:"vieworder";s:1:"2";s:3:"num";s:1:"5";}s:6:"active";a:3:{s:6:"ifopen";s:1:"1";s:9:"vieworder";s:1:"3";s:3:"num";s:1:"4";}s:6:"galbum";a:3:{s:6:"ifopen";s:1:"1";s:9:"vieworder";s:1:"4";s:3:"num";s:2:"10";}}');

DROP TABLE IF EXISTS pw_cnphoto;
CREATE TABLE pw_cnphoto (
  pid int(10) NOT NULL auto_increment,
  aid int(10) unsigned not null default '0',
  pintro varchar(200) NOT NULL default '',
  path varchar(200) NOT NULL default '',
  uploader varchar(50) NOT NULL default '',
  uptime int(10) NOT NULL default '0',
  hits smallint(6) NOT NULL default '0',
  ifthumb tinyint(1) unsigned NOT NULL default '0',
  c_num mediumint(8) unsigned NOT NULL default '0',
  PRIMARY KEY  (pid),
  KEY idx_aid_uptime (aid,uptime)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_cnskin;
CREATE TABLE pw_cnskin (
  dir varchar(20) NOT NULL,
  name varchar(30) NOT NULL default '',
  PRIMARY KEY  (dir)
) ENGINE=MyISAM;
REPLACE INTO pw_cnskin VALUES('skin_sun','阳光');
REPLACE INTO pw_cnskin VALUES('skin_purple','紫色');
REPLACE INTO pw_cnskin VALUES('skin_pink','粉色');
REPLACE INTO pw_cnskin VALUES('skin_night','星空');
REPLACE INTO pw_cnskin VALUES('skin_green','绿野');
REPLACE INTO pw_cnskin VALUES('skin_default','默认');
REPLACE INTO pw_cnskin VALUES('skin_city','城市');
REPLACE INTO pw_cnskin VALUES('skin_sport','运动');
REPLACE INTO pw_cnskin VALUES('skin_leisure','休闲');
REPLACE INTO pw_cnskin VALUES('skin_car','汽车');
REPLACE INTO pw_cnskin VALUES('skin_area','地区');

DROP TABLE IF EXISTS pw_cnstyles;
CREATE TABLE pw_cnstyles (
  id smallint(6) NOT NULL auto_increment,
  cname varchar(20) NOT NULL default '',
  ifopen tinyint(3) NOT NULL default '1',
  csum int(10) NOT NULL default '0',
  upid smallint(10) NOT NULL default '0',
  PRIMARY KEY (id),
  KEY idx_cname (cname)
) ENGINE=MyISAM;
REPLACE INTO pw_cnstyles VALUES('1','默认分类','1','0','0');

DROP TABLE IF EXISTS pw_collection;
CREATE TABLE pw_collection (
  id mediumint(8) NOT NULL auto_increment,
  type varchar(20) NOT NULL default '',
  typeid int(10) unsigned NOT NULL default '0',
  uid int(10) unsigned NOT NULL default '0',
  username varchar(15) NOT NULL default '',
  postdate int(10) unsigned NOT NULL default '0',
  content text NOT NULL,
  ifhidden tinyint(3) unsigned NOT NULL default '0',
  c_num mediumint(8) unsigned NOT NULL default '0',
  ctid int(10) not null default '-1',
  PRIMARY KEY  (id),
  KEY idx_uid_postdate (uid,postdate),
  KEY idx_ctid ( `ctid` )
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_collectiontype;
CREATE TABLE `pw_collectiontype` (
  `ctid` int(10) unsigned not null auto_increment,
  `uid` int(10) unsigned not null default '0',
  `name` varchar(20) not null default '',
  PRIMARY KEY (`ctid`),
  KEY `idx_uid` (`uid`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS pw_colonys;
CREATE TABLE pw_colonys (
  id smallint(6) unsigned NOT NULL auto_increment,
  classid smallint(6) NOT NULL default '0',
  cname varchar(50) NOT NULL default '',
  admin varchar(20) NOT NULL default '',
  members int(10) NOT NULL default '0',
  ifcheck tinyint(3) NOT NULL default '0',
  ifopen tinyint(3) NOT NULL default '1',
  cnimg varchar(100) NOT NULL default '',
  banner varchar(100) NOT NULL default '',
  createtime int(10) NOT NULL default '0',
  annouce text NOT NULL default '',
  albumnum smallint(6) NOT NULL default '0',
  photonum int(10) unsigned NOT NULL default '0',
  writenum int(10) unsigned NOT NULL default '0',
  activitynum int(10) NOT NULL default '0',
  iftopicshowinforum tinyint(3) unsigned NOT NULL default '1',
  annoucesee smallint(6) NOT NULL default '0',
  descrip varchar(255) NOT NULL default '',
  commonlevel mediumint(8) unsigned NOT NULL default '0',
  speciallevel mediumint(8) unsigned NOT NULL default '0',
  tnum int(10) NOT NULL default '0',
  pnum int(10) unsigned NOT NULL default '0',
  todaypost int(10) NOT NULL default '0',
  styleid smallint(6) UNSIGNED NOT NULL default '0',
  visit int(10) NOT NULL default '0',
  ifshow tinyint(3) NOT NULL default '0',
  ifshowpic tinyint(3) NOT NULL default '0',
  colonystyle varchar(40) NOT NULL default '',
  ifwriteopen tinyint(3) NOT NULL default '1',
  ifmemberopen tinyint(3) NOT NULL default '1',
  ifannouceopen tinyint(3) NOT NULL default '1',
  vieworder tinyint(3) NOT NULL default '0',
  titlefont varchar(255) NOT NULL default '',
  viewtype tinyint(3) NOT NULL default '0',
  visitor text NOT NULL,
  PRIMARY KEY  (id),
  UNIQUE KEY idx_cname (cname),
  KEY idx_admin (admin),
  KEY idx_classid (classid),
  KEY idx_classid_vieworder (classid,vieworder)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_comment;
CREATE TABLE pw_comment (
  id mediumint(8) unsigned NOT NULL auto_increment,
  uid int(10) unsigned NOT NULL default '0',
  username varchar(15) NOT NULL default '',
  title varchar(255) NOT NULL default '',
  type varchar(10) NOT NULL default '',
  typeid mediumint(8) NOT NULL default '0',
  upid mediumint(8) NOT NULL default '0',
  postdate int(10) NOT NULL default '0',
  ifwordsfb tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (id),
  KEY idx_type_typeid (type,typeid),
  KEY idx_upid (upid),
  KEY idx_postdate (postdate)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_config;
CREATE TABLE pw_config (
  db_name varchar(30) NOT NULL default '',
  vtype ENUM( 'string', 'array' ) DEFAULT 'string' NOT NULL,
  db_value text NOT NULL,
  decrip text NOT NULL,
  PRIMARY KEY  (db_name)
) TYPE=MyISAM;
INSERT INTO pw_config (db_name, db_value) VALUES ('rg_regdetail', '1');
INSERT INTO pw_config (db_name, db_value) VALUES ('rg_emailcheck', '0');
INSERT INTO pw_config (db_name, db_value) VALUES ('rg_allowsameip', '0');
INSERT INTO pw_config (db_name, db_value) VALUES ('rg_regsendemail', '0');
INSERT INTO pw_config (db_name, db_value) VALUES ('rg_whyregclose', '{#rg_whyregclose}');
INSERT INTO pw_config (db_name, db_value) VALUES ('rg_welcomemsg', '{#rg_welcomemsg}');
INSERT INTO pw_config (db_name, db_value) VALUES ('rg_rgpermit', '{#rg_rgpermit}');
INSERT INTO pw_config (db_name, db_value) VALUES ('rg_registertype','0');
INSERT INTO pw_config (db_name, db_value) VALUES ('rg_regweek','0');
INSERT INTO pw_config (db_name, db_value) VALUES ('rg_banname', '{#rg_banname}');
INSERT INTO pw_config (db_name, db_value) VALUES ('ml_mailifopen', '1');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_bbsifopen', '1');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_whybbsclose', '{#db_whybbsclose}');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_visitmsg', '{#db_visitmsg}');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_openpost','0	0	0');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_onlinelmt', '0');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_regpopup', '0');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_debug', '0');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_forumdir', '0');
INSERT INTO pw_config (db_name, vtype, db_value) VALUES ('db_bbstitle','array','a:2:{s:5:"index";s:0:"";s:5:"other";s:0:"";}');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_metakeyword', '');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_metadescrip', '');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_bbsname', 'phpwind');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_bfn', 'index.php');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_bbsurl', '{#db_bbsurl}');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_ceoemail', 'webmaster@phpwind.com');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_recycle', '1');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_icp', '');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_autochange', '0');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_hour', '20');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_http', 'N');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_attachurl', 'N');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_lp', '0');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_obstart', '0');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_charset', '{#db_charset}');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_forcecharset', '0');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_defaultstyle', 'wind');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_cvtime', '0');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_timedf', '8');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_datefm', 'Y-m-d H:i');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_pingtime', '0');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_columns', '0');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_msgsound', '0');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_shield', '1');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_tcheck', '0');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_adminset', '0');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_ifonlinetime', '1');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_threadrelated', '1');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_ifjump', '1');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_refreshtime', '0');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_onlinetime', '3600');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_maxresult', '500');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_footertime', '1');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_ckpath', '/');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_ckdomain', '');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_postallowtime', '0');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_cvtimes', '30');
INSERT INTO pw_config (db_name, vtype, db_value) VALUES ('db_windpost', 'array','a:8:{s:3:"pic";s:1:"1";s:8:"picwidth";s:3:"700";s:9:"picheight";s:3:"700";s:4:"size";s:1:"6";s:5:"flash";s:1:"1";s:4:"mpeg";s:1:"1";s:6:"iframe";s:1:"0";s:8:"checkurl";s:1:"1";}');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_signheight', '110');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_signwindcode', '1');
INSERT INTO pw_config (db_name, vtype, db_value) VALUES ('db_windpic', 'array' ,'a:5:{s:3:"pic";s:1:"1";s:8:"picwidth";s:3:"700";s:9:"picheight";s:3:"700";s:4:"size";s:1:"5";s:5:"flash";s:1:"0";}');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_allowupload', '1');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_attachdir', '3');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_attachhide', '0');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_attachnum', '4');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_showreplynum', '5');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_selcount', '1000');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_replysendmail', '0');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_replysitemail', '1');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_pwcode', '0');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_setform', '0');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_titlemax', '100');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_postmax', '50000');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_postmin', '2');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_autoimg', '1');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_ntnum', '2');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_ifselfshare', '1');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_indexlink', '1');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_indexmqshare', '0');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_indexshowbirth', '1');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_indexonline', '1');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_adminshow', '0');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_showguest', '0');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_today', '0');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_indexfmlogo', '2');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_todaypost', '1');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_newtime', '3600');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_perpage', '20');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_readperpage', '10');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_maxpage', '1000');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_maxmember', '1000');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_anonymousname', '{#anonymousname}');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_hithour', '0');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_topped', '1');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_threadonline', '1');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_showonline', '1');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_threadshowpost', '0');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_showcolony', '0');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_threademotion', '0');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_ipfrom', '1');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_watermark', '1');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_ifgif', '0');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_waterwidth', '0');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_waterheight', '0');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_waterpos', '1');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_waterimg', 'mark.gif');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_watertext', 'http://www.phpwind.net');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_waterfont', '5');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_watercolor', '#0000FF');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_waterpct', '85');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_jpgquality', '75');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_iffthumb', '1');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_ifathumb', '0');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_signmoney', '0');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_wapifopen', '0');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_wapcharset', '0');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_waplimit', '2000');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_jsifopen', '0');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_jsper', '900');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_bindurl', '');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_loadavg', '3');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_cc', '0');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_ipcheck', '0');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_ifsafecv', '0');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_iplimit', '');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_ifftp', '0');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_ftpweb', '');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_enterreason', '0');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_adminreason', '{#db_adminreason}');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_opensch', '0	0	0');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_gdcheck', '0');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_postgd', '0');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_gdstyle', '0');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_gdtype', '1');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_gdcontent', 'a:3:{i:1;s:1:"1";i:2;s:1:"0";i:3;s:1:"0";}');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_gdsize', '90	30	4');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_upload', '1	120	120	2048');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_uploadfiletype', 'a:6:{s:3:"gif";s:4:"2000";s:3:"png";s:4:"2000";s:3:"zip";s:4:"2000";s:3:"rar";s:4:"2000";s:3:"jpg";s:4:"2000";s:3:"txt";s:4:"2000";}');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_creditset', 'a:6:{s:6:"Digest";a:5:{s:5:"money";s:1:"0";s:4:"rvrc";s:2:"10";s:6:"credit";s:1:"0";s:8:"currency";s:1:"0";i:1;s:1:"0";}s:4:"Post";a:5:{s:5:"money";s:1:"1";s:4:"rvrc";s:1:"1";s:6:"credit";s:1:"0";s:8:"currency";s:1:"0";i:1;s:1:"0";}s:5:"Reply";a:5:{s:5:"money";s:1:"1";s:4:"rvrc";s:1:"0";s:6:"credit";s:1:"0";s:8:"currency";s:1:"0";i:1;s:1:"0";}s:8:"Undigest";a:5:{s:5:"money";s:1:"0";s:4:"rvrc";s:2:"10";s:6:"credit";s:1:"0";s:8:"currency";s:1:"0";i:1;s:1:"0";}s:6:"Delete";a:5:{s:5:"money";s:1:"1";s:4:"rvrc";s:1:"1";s:6:"credit";s:1:"0";s:8:"currency";s:1:"0";i:1;s:1:"0";}s:8:"Deleterp";a:5:{s:5:"money";s:1:"1";s:4:"rvrc";s:1:"0";s:6:"credit";s:1:"0";s:8:"currency";s:1:"0";i:1;s:1:"0";}}');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_showgroup', ',3,4,5,16,');
INSERT INTO pw_config (db_name, vtype,db_value) VALUES ('db_showcustom', 'array','a:2:{i:0;s:5:"money";i:1;s:4:"rvrc";}');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_menu', '3');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_fthumbsize', '100	100');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_athumbsize', '575	0');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_signgroup', ',5,6,7,16,8,9,10,11,12,13,14,15,');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_autoban', '0');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_wapfids', '');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_safegroup', '');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_attfg', '1');
INSERT INTO pw_config (db_name, db_value) VALUES ('rg_allowregister', '1');
INSERT INTO pw_config (db_name, db_value) VALUES ('rg_reg', '1');
INSERT INTO pw_config (db_name, db_value) VALUES ('rg_regsendmsg', '1');
INSERT INTO pw_config (db_name, db_value) VALUES ('rg_ifcheck', '0');
INSERT INTO pw_config (db_name, db_value) VALUES ('rg_rglower', '0');
INSERT INTO pw_config (db_name, db_value) VALUES ('rg_namelen', '3	12');
INSERT INTO pw_config (db_name, db_value) VALUES ('rg_pwdlen', '6	16');
INSERT INTO pw_config (db_name, db_value) VALUES ('ml_smtpauth', '1');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_banby', '0');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_bantype', '0');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_banlimit', '0');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_banmax', '0');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_rmbrate', '10');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_rmblest', '5');
INSERT INTO pw_config (db_name, db_value) VALUES ('cy_virement', '0');
INSERT INTO pw_config (db_name, db_value) VALUES ('cy_virerate', '0');
INSERT INTO pw_config (db_name, db_value) VALUES ('cy_virelimit', '0');
INSERT INTO pw_config (db_name, vtype, db_value, decrip) VALUES('db_diy', 'string', 'basic,setforum,tpccheck,topiccate,setuser,level,announcement,navmode,bakout,area_tplcontent', '');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_ipban', '');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_ipstates', '1');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_union', '');
INSERT INTO pw_config (db_name, db_value) VALUES ('fc_shownum', '9');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_tlist', '');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_ptable', '');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_plist', '');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_ads', '1');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_wordsfb', '1');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_htmifopen', '0');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_dir', '.php?');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_ext', '.html');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_upgrade', 'a:7:{s:7:"postnum";s:1:"1";s:7:"digests";s:1:"0";s:4:"rvrc";s:1:"0";s:5:"money";s:1:"0";s:6:"credit";s:1:"0";s:10:"onlinetime";s:1:"0";i:1;s:1:"0";}');
INSERT INTO pw_config (db_name, db_value) VALUES ('ol_onlinepay', '1');
INSERT INTO pw_config (db_name, db_value) VALUES ('ol_whycolse', '{#ol_whycolse}');
INSERT INTO pw_config (db_name, db_value) VALUES ('ol_payto', '');
INSERT INTO pw_config (db_name, db_value) VALUES ('ol_md5code', '');
INSERT INTO pw_config (db_name, db_value) VALUES ('ol_paypal', '');
INSERT INTO pw_config (db_name, db_value) VALUES ('ol_paypalcode', '');
INSERT INTO pw_config (db_name, db_value) VALUES ('ol_99bill', '');
INSERT INTO pw_config (db_name, db_value) VALUES ('ol_99billcode', '');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_head', '');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_foot', '');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_pptifopen', '0');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_pptkey', '');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_ppttype', 'client');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_ppturls', '');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_pptserverurl', '');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_pptloginurl', 'login.php');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_pptloginouturl', 'login.php?action=quit');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_pptregurl', 'register.php');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_pptcredit', '');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_toolifopen', '1');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_allowtrade', '1');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_attachname', 'attachment');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_picpath', 'images');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_htmdir', 'html');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_readdir', 'read');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_guestdir', 'data/guestcache');
INSERT INTO pw_config (db_name, db_value) VALUES ('ml_mailmethod', '1');
INSERT INTO pw_config (db_name, db_value) VALUES ('ml_smtphost', '');
INSERT INTO pw_config (db_name, db_value) VALUES ('ml_smtpport', '25');
INSERT INTO pw_config (db_name, db_value) VALUES ('ml_smtpfrom', '');
INSERT INTO pw_config (db_name, db_value) VALUES ('ml_smtpuser', '');
INSERT INTO pw_config (db_name, db_value) VALUES ('ml_smtphelo', '');
INSERT INTO pw_config (db_name, db_value) VALUES ('ml_smtpmxmailname', '');
INSERT INTO pw_config (db_name, db_value) VALUES ('ml_mxdns', '');
INSERT INTO pw_config (db_name, db_value) VALUES ('ml_mxdnsbak', '');
INSERT INTO pw_config (db_name, db_value) VALUES ('ftp_pass', '');
INSERT INTO pw_config (db_name, db_value) VALUES ('ftp_server', '');
INSERT INTO pw_config (db_name, db_value) VALUES ('ftp_port', '21');
INSERT INTO pw_config (db_name, db_value) VALUES ('ftp_dir', '');
INSERT INTO pw_config (db_name, db_value) VALUES ('ftp_user', '');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_schwait', '2');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_registerfile', 'register.php');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_adminfile', 'admin.php');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_sortnum', '20');
INSERT INTO pw_config (db_name, vtype, db_value) VALUES ('db_styledb', 'array', 'a:1:{s:4:"wind";a:2:{i:0;s:4:"wind";i:1;s:1:"1";}}');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_moneyname', '{#db_moneyname}');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_moneyunit', '{#db_moneyunit}');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_rvrcname', '{#db_rvrcname}');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_rvrcunit', '{#db_rvrcunit}');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_creditname', '{#db_creditname}');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_creditunit', '{#db_creditunit}');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_currencyname', '{#db_currencyname}');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_currencyunit', '{#db_currencyunit}');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_maxtypenum', '5');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_selectgroup', '1');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_ifpwcache', '567');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_urlcheck', 'phpwind.net,phpwind.com');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_xforwardip', '1');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_adminrecord', '1');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_floorunit', '{#db_floorunit}');
INSERT INTO pw_config (db_name, vtype, db_value) VALUES ('db_floorname', 'array', '{#db_floorname}');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_toolbar', '0');
INSERT INTO pw_config (db_name, vtype, db_value) VALUES ('db_creditlog', 'array' ,'a:6:{s:3:"reg";a:1:{s:5:"money";s:1:"1";}s:5:"topic";a:1:{s:5:"money";s:1:"1";}s:6:"credit";a:1:{s:5:"money";s:1:"1";}s:6:"reward";a:1:{s:5:"money";s:1:"1";}s:4:"hack";a:1:{s:5:"money";s:1:"1";}s:5:"other";a:1:{s:5:"money";s:1:"1";}}');
INSERT INTO pw_config (db_name, vtype, db_value) VALUES ('db_sitemsg', 'array', '{#db_sitemsg}');
INSERT INTO pw_config (db_name, db_value) VALUES ('rg_timeend', '2010');
INSERT INTO pw_config (db_name, db_value) VALUES ('rg_timestart', '1960');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_dopen', '1');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_phopen', '1');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_groups_open', '1');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_share_open', '1');
INSERT INTO pw_config (db_name, vtype, db_value) VALUES('rg_regcredit','array','a:5:{s:5:"money";s:1:"0";s:4:"rvrc";s:1:"0";s:6:"credit";s:1:"0";s:8:"currency";s:1:"0";i:1;s:1:"0";}');
INSERT INTO pw_config (db_name, db_value) VALUES('db_waterfonts','en/PilsenPlakat');
INSERT INTO pw_config (db_name, db_value) VALUES('ftp_timeout','10');
INSERT INTO pw_config (db_name, db_value) VALUES('db_virerate','1');
INSERT INTO pw_config (db_name, db_value) VALUES('db_virelimit','10');
INSERT INTO pw_config (db_name, db_value) VALUES('db_signcurtype','money');
INSERT INTO pw_config (db_name, db_value) VALUES('db_bdayautohide','1');
INSERT INTO pw_config (db_name, vtype, db_value) VALUES('db_creditpay', 'array', 'a:1:{s:5:"money";a:3:{s:7:"rmbrate";s:2:"10";s:7:"rmblest";s:2:"10";s:8:"virement";s:1:"1";}}');
INSERT INTO pw_config (db_name, vtype, db_value) VALUES('db_sellset', 'array', 'a:3:{s:4:"type";a:1:{i:0;s:5:"money";}s:5:"price";s:0:"";s:6:"income";s:0:"";}');
INSERT INTO pw_config (db_name, db_value) VALUES('db_logintype','5');
INSERT INTO pw_config (db_name, db_value) VALUES('db_func','2');
INSERT INTO pw_config (db_name, db_value) VALUES('jf_A','a:1:{s:7:"money_1";a:3:{i:0;s:1:"2";i:1;s:1:"1";i:2;s:1:"1";}}');
INSERT INTO pw_config (db_name, db_value) VALUES('nf_config','a:3:{s:8:"position";s:1:"1";s:8:"titlelen";s:2:"50";s:7:"shownum";s:1:"9";}');
INSERT INTO pw_config (db_name, db_value) VALUES('nf_order','a:0:{}');
INSERT INTO pw_config (db_name, db_value) VALUES('db_iftag', '1');
INSERT INTO pw_config (db_name, db_value) VALUES('db_readtag', '0');
INSERT INTO pw_config (db_name, db_value) VALUES('db_tagindex', '20');
INSERT INTO pw_config (db_name, db_value) VALUES('db_enhideset', 'a:1:{s:4:"type";a:1:{i:0;s:5:"money";}}');
INSERT INTO pw_config (db_name, vtype, db_value, decrip) VALUES('db_rategroup', 'array', 'a:13:{i:8;s:2:"10";i:9;s:2:"10";i:10;s:2:"10";i:11;s:2:"10";i:12;s:2:"10";i:13;s:2:"10";i:14;s:2:"10";i:15;s:2:"10";i:4;s:2:"10";i:5;s:2:"10";i:17;s:2:"10";i:16;s:2:"10";i:2;s:2:"10";}', '');
INSERT INTO pw_config (db_name, vtype, db_value, decrip) VALUES('db_ratepower', 'array', 'a:3:{i:1;s:1:"0";i:2;s:1:"0";i:3;s:1:"0";}', '');
INSERT INTO pw_config (db_name, vtype, db_value, decrip) values('db_job_isopen','string','1','');
INSERT INTO pw_config (db_name, vtype, db_value, decrip) values('db_job_ispop','string','1','');
INSERT INTO pw_config (db_name, vtype, db_value, decrip) values('db_newinfoifopen','string','0','');
INSERT INTO pw_config (db_name, vtype, db_value, decrip) values('db_bbsradioifopen','string','0','');
INSERT INTO pw_config (db_name, db_value) values('db_hotwords', 'phpwind,PW');
INSERT INTO pw_config (db_name, vtype, db_value, decrip) VALUES ('db_portalstatictime', 'string', '1', '');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_classfile_compress', '0');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_cachefile_compress', '0');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_filecache_to_memcache', '0');
INSERT INTO pw_config (db_name, vtype, db_value, decrip) VALUES ('db_unique_strategy', 'string', 'db', '');
INSERT INTO pw_config (db_name, vtype, db_value, decrip) VALUES ('db_postedittime', 'string', '10', '');
INSERT INTO pw_config (db_name, vtype, db_value, decrip) VALUES ('db_portalstatictime', 'string', '15', '');
INSERT INTO pw_config (db_name, vtype, db_value, decrip) VALUES ('db_search_type', 'array', 'a:5:{s:6:"thread";s:6:"帖子";s:5:"diary";s:6:"日志";s:4:"user";s:6:"用户";s:5:"forum";s:6:"版块";s:5:"group";s:6:"群组";}', '');
INSERT INTO pw_config (db_name, db_value) VALUES ('db_admingradereason', '{#db_admingradereason}');

DROP TABLE IF EXISTS pw_creditlog;
CREATE TABLE pw_creditlog (
  id int(10) unsigned NOT NULL auto_increment,
  uid int(10) unsigned NOT NULL default '0',
  username varchar(15) NOT NULL default '',
  ctype varchar(8) NOT NULL default '',
  affect int(10) NOT NULL default '0',
  adddate int(10) NOT NULL default '0',
  logtype varchar(20) NOT NULL default '',
  ip varchar(15) NOT NULL default '',
  descrip varchar(255) NOT NULL default '',
  PRIMARY KEY  (id),
  KEY idx_uid (uid),
  KEY idx_adddate (adddate)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_credits;
CREATE TABLE pw_credits (
  cid MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
  name varchar(30) NOT NULL default '',
  unit varchar(30) NOT NULL default '',
  description varchar(255) NOT NULL default '',
  type ENUM( 'main', 'group' ) NOT NULL,
  PRIMARY KEY  (cid),
  KEY idx_type (type)
) TYPE=MyISAM;
INSERT INTO pw_credits VALUES ('1', '{#credit_name}','{#credit_unit}', '{#credit_descrip}', 'main');

DROP TABLE IF EXISTS pw_customfield;
CREATE TABLE pw_customfield (
  id smallint(6) NOT NULL auto_increment,
  title varchar(50) NOT NULL default '',
  maxlen smallint(6) NOT NULL default '0',
  vieworder smallint(6) NOT NULL default '0',
  type tinyint(1) NOT NULL default '0',
  state tinyint(1) NOT NULL default '0',
  required tinyint(1) NOT NULL default '0',
  viewinread tinyint(1) NOT NULL default '0',
  editable tinyint(1) NOT NULL default '0',
  descrip varchar(255) NOT NULL default '',
  viewright varchar(255) NOT NULL default '',
  options text NOT NULL,
  PRIMARY KEY  (id)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_cwritedata;
CREATE TABLE pw_cwritedata (
  id int(10) unsigned NOT NULL auto_increment,
  uid int(10) unsigned NOT NULL default '0',
  touid int(10) unsigned NOT NULL default '0',
  cyid int(10) unsigned NOT NULL default '0',
  postdate int(10) unsigned NOT NULL default '0',
  replay_time int(10) unsigned NOT NULL default '0',
  isshare tinyint(1) unsigned NOT NULL default '0',
  c_num mediumint(8) unsigned NOT NULL default '0',
  moodfirst tinyint(1) NOT NULL default '0',
  source varchar(10) NOT NULL default '',
  content varchar(255) NOT NULL default '',
  mood varchar(10) NOT NULL default '',
  PRIMARY KEY (id),
  KEY idx_uid (uid),
  KEY idx_mood_moodfirst (mood,moodfirst)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS pw_datanalyse;
CREATE TABLE pw_datanalyse (
  tag int(10) NOT NULL,
  action varchar(30) NOT NULL,
  timeunit int(10) NOT NULL,
  num int(10) NOT NULL DEFAULT '0',
  UNIQUE KEY idx_action_timeunit_tag (action,tag,timeunit),
  KEY idx_tag_action (tag,action)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_datastate;
CREATE TABLE pw_datastate (
  year smallint(4) NOT NULL default '0',
  month tinyint(2) NOT NULL default '0',
  day tinyint(2) NOT NULL default '0',
  topic mediumint(8) NOT NULL default '0',
  reply mediumint(8) NOT NULL default '0',
  regmen mediumint(8) NOT NULL default '0',
  postmen mediumint(8) NOT NULL default '0',
  PRIMARY KEY  (year,month,day)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_datastore;
CREATE TABLE pw_datastore (
  skey varchar(32) NOT NULL,
  expire int(10) unsigned NOT NULL default '0',
  vhash char(32) NOT NULL,
  value text NOT NULL,
  PRIMARY KEY (skey),
  KEY idx_expire (expire)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_debatedata;
CREATE TABLE pw_debatedata (
  pid int(10) unsigned NOT NULL,
  tid int(10) unsigned NOT NULL,
  authorid int(10) unsigned NOT NULL,
  standpoint tinyint(1) unsigned NOT NULL default '0',
  postdate int(10) unsigned NOT NULL default '0',
  vote int(10) unsigned NOT NULL default '0',
  voteids text NOT NULL,
  PRIMARY KEY  (pid,tid,authorid)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_debates;
CREATE TABLE pw_debates (
  tid int(10) unsigned NOT NULL,
  authorid int(10) unsigned NOT NULL default '0',
  postdate int(10) unsigned NOT NULL default '0',
  obtitle varchar(255) NOT NULL default '',
  retitle varchar(255) NOT NULL default '',
  endtime int(10) unsigned NOT NULL default '0',
  obvote int(10) unsigned NOT NULL default '0',
  revote int(10) unsigned NOT NULL default '0',
  obposts int(10) unsigned NOT NULL default '0',
  reposts int(10) unsigned NOT NULL default '0',
  umpire varchar(16) NOT NULL default '',
  umpirepoint varchar(255) NOT NULL default '',
  debater varchar(16) NOT NULL default '',
  judge tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (tid)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_delta_diarys;
CREATE TABLE pw_delta_diarys(
 id int(10) unsigned not null auto_increment,
 state tinyint(3) unsigned not null default 0,
 primary key (id)
)ENGINE=MyISAM;


DROP TABLE IF EXISTS pw_delta_members;
CREATE TABLE pw_delta_members(
 id int(10) unsigned not null auto_increment,
 state tinyint(3) unsigned not null default 0,
 primary key (id)
)ENGINE=MyISAM;

DROP TABLE IF EXISTS pw_delta_posts;
CREATE TABLE pw_delta_posts(
 id int(10) unsigned not null auto_increment,
 state tinyint(3) unsigned not null default 0,
 primary key (id)
)ENGINE=MyISAM;

DROP TABLE IF EXISTS pw_delta_threads;
CREATE TABLE pw_delta_threads(
 id int(10) unsigned not null auto_increment,
 state tinyint(3) unsigned not null default 0,
 primary key (id)
)ENGINE=MyISAM;

DROP TABLE IF EXISTS pw_diary;
CREATE TABLE pw_diary (
  did int(10) NOT NULL auto_increment,
  uid int(10) unsigned NOT NULL default '0',
  dtid mediumint(8) unsigned NOT NULL default '0',
  aid text NOT NULL,
  username varchar(15) NOT NULL default '',
  privacy tinyint(1) NOT NULL default '0',
  subject varchar(150) NOT NULL default '',
  content text NOT NULL,
  ifcopy tinyint(1) NOT NULL default '0',
  copyurl varchar(100) NOT NULL default '',
  ifconvert tinyint(1) NOT NULL default '0',
  ifwordsfb tinyint(1) NOT NULL default '0',
  ifupload tinyint(1) NOT NULL default '0',
  r_num int(10) NOT NULL default '0',
  c_num int(10) NOT NULL default '0',
  postdate int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (did),
  KEY idx_uid (uid),
  KEY idx_postdate (postdate)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_diarytype;
CREATE TABLE pw_diarytype (
  dtid mediumint(8) unsigned NOT NULL auto_increment,
  uid int(10) unsigned NOT NULL default '0',
  name varchar(20) NOT NULL default '',
  num mediumint(8) NOT NULL default '0',
  PRIMARY KEY  (dtid),
  KEY idx_uid (uid)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_draft;
CREATE TABLE pw_draft (
  did mediumint(8) unsigned NOT NULL auto_increment,
  uid int(10) unsigned NOT NULL default '0',
  content text NOT NULL,
  PRIMARY KEY  (did),
  KEY idx_uid (uid)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_elements;
CREATE TABLE pw_elements (
  eid int(10) unsigned NOT NULL auto_increment,
  type varchar(30) NOT NULL default '',
  mark varchar(30) NOT NULL default '',
  id int(10) unsigned NOT NULL default '0',
  value int(10) NOT NULL default '0',
  addition varchar(255) NOT NULL default '',
  special tinyint(1) NOT NULL default '0',
  time int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (eid),
  KEY idx_type_value (type,value),
  UNIQUE KEY idx_type_mark_id (type,mark,id)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_extragroups;
CREATE TABLE pw_extragroups (
  uid int(10) unsigned NOT NULL default '0',
  gid smallint(6) NOT NULL default '0',
  togid smallint(6) NOT NULL default '0',
  startdate int(10) unsigned NOT NULL default '0',
  days smallint(6) NOT NULL default '0',
  KEY idx_uid (uid)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_favors;
CREATE TABLE pw_favors (
  uid int(10) unsigned NOT NULL default '0',
  tids text NOT NULL,
  type varchar(255) NOT NULL default '',
  PRIMARY KEY  (uid)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_feed;
CREATE TABLE pw_feed (
  id int(10) unsigned NOT NULL auto_increment,
  uid int(10) unsigned NOT NULL default '0',
  type varchar(20) NOT NULL default '',
  descrip TEXT,
  timestamp int(10) unsigned NOT NULL default '0',
  typeid mediumint(8) unsigned NOT NULL default '0',
  PRIMARY KEY  (id),
  KEY idx_uid (uid)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_filter;
CREATE TABLE pw_filter (
  id int(10) unsigned NOT NULL auto_increment,
  tid int(10) unsigned NOT NULL default '0',
  pid int(10) unsigned NOT NULL default '0',
  filter mediumtext,
  state tinyint(3) unsigned NOT NULL default '0',
  assessor varchar(15) NOT NULL default '',
  created_at int(10) unsigned NOT NULL default '0',
  updated_at int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (id),
  KEY idx_tid (tid)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_filter_class;
CREATE TABLE pw_filter_class (
  id tinyint(3) unsigned NOT NULL auto_increment,
  title varchar(16) NOT NULL default '',
  state tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (id)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_filter_dictionary;
CREATE TABLE pw_filter_dictionary (
  id int(10) unsigned NOT NULL auto_increment,
  tid int(10) unsigned NOT NULL default '0',
  pid int(10) unsigned NOT NULL default '0',
  title varchar(100) NOT NULL default '',
  bin varchar(255) NOT NULL default '',
  source varchar(255) NOT NULL default '',
  PRIMARY KEY  (id)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_focus;
CREATE TABLE pw_focus (
  id int(10) unsigned NOT NULL auto_increment,
  pushto varchar(30) NOT NULL default '',
  pushtime int(10) NOT NULL default '0',
  fid smallint(6) NOT NULL default '0',
  tid int(10) NOT NULL default '0',
  subject varchar(100) NOT NULL default '',
  content text NOT NULL,
  postdate int(10) NOT NULL default '0',
  url varchar(100) NOT NULL  default '',
  imgurl varchar(100) NOT NULL  default '',
  PRIMARY KEY  (id),
  KEY idx_pushto (pushto)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_forumdata;
CREATE TABLE pw_forumdata (
  fid smallint(6) unsigned NOT NULL default '0',
  tpost mediumint(8) unsigned NOT NULL default '0',
  topic mediumint(8) unsigned NOT NULL default '0',
  article int(10) unsigned NOT NULL default '0',
  subtopic mediumint(8) unsigned NOT NULL default '0',
  top1 smallint(6) unsigned NOT NULL default '0',
  top2 smallint(6) unsigned NOT NULL default '0',
  topthreads text NOT NULL default '',
  aid smallint(6) unsigned NOT NULL default '0',
  aidcache int(10) unsigned NOT NULL default '0',
  aids varchar(135) NOT NULL default '',
  lastpost varchar(135) NOT NULL default '',
  PRIMARY KEY  (fid),
  KEY idx_aid (aid)
) TYPE=MyISAM;
INSERT INTO pw_forumdata (fid, tpost, topic, article, subtopic, top1, top2, aid, aidcache, aids, lastpost) VALUES(1, 0, 0, 0, 0, 0, 0, 0, 0, '', '');
INSERT INTO pw_forumdata (fid, tpost, topic, article, subtopic, top1, top2, aid, aidcache, aids, lastpost) VALUES(2, 0, 0, 0, 0, 0, 0, 0, 0, '', '');

DROP TABLE IF EXISTS pw_forumlog;
CREATE TABLE pw_forumlog (
  id int(11) NOT NULL auto_increment,
  type varchar(10) NOT NULL default '',
  username1 varchar(30) NOT NULL default '',
  username2 varchar(30) NOT NULL default '',
  field1 varchar(30) NOT NULL default '',
  field2 varchar(30) NOT NULL default '',
  field3 varchar(255) NOT NULL default '',
  descrip text NOT NULL,
  timestamp int(10) NOT NULL default '0',
  ip varchar(20) NOT NULL default '',
  PRIMARY KEY  (id),
  KEY idx_type (type),
  KEY idx_username1 (username1),
  KEY idx_username2 (username2)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_forummsg;
CREATE TABLE pw_forummsg (
  id smallint(6) NOT NULL auto_increment,
  fid smallint(6) NOT NULL default '0',
  uid int(10) unsigned NOT NULL default '0',
  username varchar(15) NOT NULL default '',
  toname varchar(200) NOT NULL default '',
  msgtype tinyint(1) NOT NULL default '0',
  posttime int(10) NOT NULL default '0',
  savetime int(10) NOT NULL default '0',
  message mediumtext NOT NULL,
  PRIMARY KEY  (id),
  KEY idx_fid (fid)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_forums;
CREATE TABLE pw_forums (
  fid smallint(6) unsigned NOT NULL auto_increment,
  fup smallint(6) unsigned NOT NULL default '0',
  ifsub tinyint(1) NOT NULL default '0',
  childid tinyint(1) NOT NULL default '0',
  type enum('category','forum','sub','sub2') NOT NULL default 'forum',
  logo varchar(100) NOT NULL default '',
  name varchar(255) NOT NULL default '',
  descrip text NOT NULL,
  title varchar(255) NOT NULL default '',
  dirname varchar(15) NOT NULL default '',
  metadescrip varchar(255) NOT NULL default '',
  keywords varchar(255) NOT NULL default '',
  vieworder smallint(6) NOT NULL default '0',
  forumadmin varchar(255) NOT NULL default '',
  fupadmin varchar(255) NOT NULL default '',
  style varchar(12) NOT NULL default '',
  across tinyint(1) NOT NULL DEFAULT '0',
  allowhtm tinyint(1) NOT NULL default '0',
  allowhide tinyint(1) NOT NULL default '1',
  allowsell tinyint(1) NOT NULL default '1',
  allowtype tinyint(3) NOT NULL default '1',
  copyctrl tinyint(1) NOT NULL default '0',
  allowencode tinyint(1) NOT NULL default '1',
  password varchar(32) NOT NULL default '',
  viewsub tinyint(1) NOT NULL default '0',
  allowvisit varchar(255) NOT NULL default '',
  allowread varchar(255) NOT NULL default '',
  allowpost varchar(255) NOT NULL default '',
  allowrp varchar(255) NOT NULL default '',
  allowdownload varchar(255) NOT NULL default '',
  allowupload varchar(255) NOT NULL default '',
  modelid varchar( 255 ) NOT NULL default '',
  forumsell varchar(15) NOT NULL default '',
  pcid varchar(50) NOT NULL default '',
  actmids varchar(255) NOT NULL default '',
  f_type enum('forum','former','hidden','vote') NOT NULL default 'forum',
  f_check tinyint(1) unsigned NOT NULL default '0',
  t_type tinyint(1) NOT NULL default '0',
  cms tinyint(1) NOT NULL default '0',
  ifhide tinyint(1) NOT NULL default '1',
  showsub tinyint(1) NOT NULL default '0',
  ifcms tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (fid),
  KEY idx_fup (fup),
  KEY idx_ifsub_vieworder_fup (ifsub,vieworder,fup)
) TYPE=MyISAM;
INSERT INTO pw_forums (fid, fup, ifsub, childid, type, logo, name, descrip, dirname, keywords, vieworder, forumadmin, fupadmin, style, across, allowhtm, allowhide, allowsell, allowtype, copyctrl, allowencode, password, viewsub, allowvisit, allowread, allowpost, allowrp, allowdownload, allowupload, modelid, forumsell, pcid, f_type, f_check, t_type, cms, ifhide, showsub, ifcms) VALUES(1, 0, 0, 1, 'category', '', '默认分类', '', '', '', 0, '', '', '0', 0, 0, 1, 1, 3, 0, 1, '', 0, '', '', '', '', '', '', '', '', '', 'forum', 0, 0, 0, 1, 0, 0);
INSERT INTO pw_forums (fid, fup, ifsub, childid, type, logo, name, descrip, dirname, keywords, vieworder, forumadmin, fupadmin, style, across, allowhtm, allowhide, allowsell, allowtype, copyctrl, allowencode, password, viewsub, allowvisit, allowread, allowpost, allowrp, allowdownload, allowupload, modelid, forumsell, pcid, f_type, f_check, t_type, cms, ifhide, showsub, ifcms) VALUES(2, 1, 0, 0, 'forum', '', '默认版块', '', '', '', 0, '', '', '0', 0, 0, 1, 1, 3, 0, 1, '', 0, '', '', '', '', '', '', '', '', '', 'forum', 0, 0, 0, 1, 0, 0);
INSERT INTO pw_forumsextra (fid, creditset, forumset, commend, appinfo) VALUES(2,'','a:9:{s:8:"orderway";s:8:"lastpost";s:3:"asc";s:4:"DESC";s:11:"replayorder";s:1:"1";s:9:"addnotice";s:1:"1";s:9:"imgthread";s:1:"1";s:10:"relatedcon";s:1:"1";s:12:"allowtpctype";s:9:"money			0";s:9:"uploadset";s:1:"1";s:9:"thumbsize";s:7:"300	300";}','','');

DROP TABLE IF EXISTS pw_forumsell;
CREATE TABLE pw_forumsell (
  id mediumint(8) NOT NULL auto_increment,
  fid smallint(6) unsigned NOT NULL default '0',
  uid int(10) unsigned NOT NULL default '1',
  buydate int(10) unsigned NOT NULL default '0',
  overdate int(10) unsigned NOT NULL default '0',
  credit varchar(8) NOT NULL default '',
  cost decimal(8,2) NOT NULL,
  PRIMARY KEY  (id),
  KEY idx_fid (fid),
  KEY idx_uid (uid)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_forumsextra;
CREATE TABLE pw_forumsextra (
  fid smallint(6) NOT NULL default '0',
  creditset text NOT NULL,
  forumset text NOT NULL,
  commend text NOT NULL,
  appinfo TEXT NOT NULL,
  PRIMARY KEY  (fid)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_friends;
CREATE TABLE pw_friends (
  uid int(10) unsigned NOT NULL default '0',
  friendid int(10) unsigned NOT NULL default '0',
  status tinyint(3) not null default '0',
  attention tinyint(3) NOT NULL DEFAULT '0',
  joindate int(10) NOT NULL default '0',
  descrip varchar(255) NOT NULL default '',
  ftid mediumint(8) unsigned NOT NULL DEFAULT '0',
  iffeed tinyint(3) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY  (uid,friendid),
  KEY idx_joindate (joindate),
  KEY idx_ftid (ftid),
  KEY idx_uid_joindate(uid,joindate)
) TYPE=MyISAM;
  

DROP TABLE IF EXISTS pw_friendtype;
CREATE TABLE pw_friendtype (
  ftid int(10) unsigned NOT NULL auto_increment,
  uid int(10) unsigned NOT NULL default '0',
  name varchar(20) NOT NULL default '',
  PRIMARY KEY  (ftid),
  KEY idx_uid (uid)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_group_replay;
CREATE TABLE pw_group_replay (
  uid int(10) NOT NULL default '0',
  tid int(10) NOT NULL default '0',
  cyid int(10) NOT NULL default '0',
  is_read smallint(2) NOT NULL default '0',
  add_time int(10) unsigned NOT NULL default '0',
  num int(10) NOT NULL,
  KEY uid (uid)
) ENGINE = MYISAM;

DROP TABLE IF EXISTS pw_hack;
CREATE TABLE pw_hack(
  hk_name varchar(30) NOT NULL default '',
  vtype ENUM('string','array') NOT NULL,
  hk_value text NOT NULL,
  decrip text NOT NULL,
  PRIMARY KEY  (hk_name)
) TYPE=MyISAM;
INSERT INTO pw_hack(hk_name,hk_value) VALUES ('bk_A','a:1:{s:10:"rvrc_money";a:6:{i:0;s:4:"{#rvrc}";i:1;s:4:"{#money}";i:2;s:1:"2";i:3;s:1:"3";i:4;s:1:"1";i:5;i:1;}}');
INSERT INTO pw_hack (hk_name, vtype, hk_value, decrip) VALUES('bk_ddate', 'string', '10', '');
INSERT INTO pw_hack (hk_name, vtype, hk_value, decrip) VALUES('bk_drate', 'string', '10', '');
INSERT INTO pw_hack (hk_name, vtype, hk_value, decrip) VALUES('bk_num', 'string', '10', '');
INSERT INTO pw_hack (hk_name, vtype, hk_value, decrip) VALUES('bk_open', 'string', '1', '');
INSERT INTO pw_hack (hk_name, vtype, hk_value, decrip) VALUES('bk_per', 'string', '5', '');
INSERT INTO pw_hack (hk_name, vtype, hk_value, decrip) VALUES('bk_rate', 'string', '5', '');
INSERT INTO pw_hack (hk_name, vtype, hk_value, decrip) VALUES('bk_timelimit', 'string', '2', '');
INSERT INTO pw_hack (hk_name, vtype, hk_value, decrip) VALUES('bk_virelimit', 'string', '10', '');
INSERT INTO pw_hack (hk_name, vtype, hk_value, decrip) VALUES('bk_virement', 'string', '1', '');
INSERT INTO pw_hack (hk_name, vtype, hk_value, decrip) VALUES('bk_virerate', 'string', '10', '');
INSERT INTO pw_hack (hk_name, vtype, hk_value, decrip) VALUES('currrate1', 'string', 'a:4:{s:4:"rvrc";s:3:"100";s:5:"money";s:3:"100";s:6:"credit";s:1:"1";i:1;s:1:"5";}', '');
INSERT INTO pw_hack (hk_name, vtype, hk_value, decrip) VALUES('currrate2', 'string', '', '');
INSERT INTO pw_hack (hk_name, vtype, hk_value, decrip) VALUES('cn_open', 'string', '1', '');
INSERT INTO pw_hack (hk_name, vtype, hk_value, decrip) VALUES('cn_remove', 'string', '1', '');
INSERT INTO pw_hack (hk_name, vtype, hk_value, decrip) VALUES('cn_newcolony', 'string', '1', '');
INSERT INTO pw_hack (hk_name, vtype, hk_value, decrip) VALUES('cn_createmoney', 'string', '100', '');
INSERT INTO pw_hack (hk_name, vtype, hk_value, decrip) VALUES('cn_joinmoney', 'string', '10', '');
INSERT INTO pw_hack (hk_name, vtype, hk_value, decrip) VALUES('cn_allowcreate', 'string', '1', '');
INSERT INTO pw_hack (hk_name, vtype, hk_value, decrip) VALUES('cn_allowjoin', 'string', '1', '');
INSERT INTO pw_hack (hk_name, vtype, hk_value, decrip) VALUES('cn_memberfull', 'string', '50', '');
INSERT INTO pw_hack (hk_name, vtype, hk_value, decrip) VALUES('cn_imgsize', 'string', '1048576', '');
INSERT INTO pw_hack (hk_name, vtype, hk_value, decrip) VALUES('cn_name', 'string', '朋友圈', '');
INSERT INTO pw_hack (hk_name, vtype, hk_value, decrip) VALUES('cn_groups', 'string', ',3,4,5,8,9,10,11,12,13,14,15,16,', '');
INSERT INTO pw_hack (hk_name, vtype, hk_value, decrip) VALUES('cn_imgwidth', 'string', '200', '');
INSERT INTO pw_hack (hk_name, vtype, hk_value, decrip) VALUES('cn_imgheight', 'string', '100', '');
INSERT INTO pw_hack (hk_name, vtype, hk_value, decrip) VALUES('cn_visittime', 'string', '60', '');
INSERT INTO pw_hack (hk_name, vtype, hk_value, decrip) VALUES('cn_transfer', 'string', '10', '');
INSERT INTO pw_hack (hk_name, vtype, hk_value, decrip) VALUES('inv_open', 'string', '0', '');
INSERT INTO pw_hack (hk_name, vtype, hk_value, decrip) VALUES('inv_days', 'string', '1', '');
INSERT INTO pw_hack (hk_name, vtype, hk_value, decrip) VALUES('inv_limitdays', 'string', '0', '');
INSERT INTO pw_hack (hk_name, vtype, hk_value, decrip) VALUES('inv_costs', 'string', '50', '');
INSERT INTO pw_hack (hk_name, vtype, hk_value, decrip) VALUES('inv_credit', 'string', 'currency', '');
INSERT INTO pw_hack (hk_name, vtype, hk_value, decrip) VALUES('inv_groups', 'string', ',3,4,5,', '');
INSERT INTO pw_hack (hk_name, vtype, hk_value, decrip) VALUES('md_groups', 'string', ',3,', '');
INSERT INTO pw_hack (hk_name, vtype, hk_value, decrip) VALUES('md_ifmsg', 'string', '1', '');
INSERT INTO pw_hack (hk_name, vtype, hk_value, decrip) VALUES('md_ifopen', 'string', '1', '');
INSERT INTO pw_hack (hk_name, vtype, hk_value, decrip) VALUES('o_camoney', 'string', '0', '');
INSERT INTO pw_hack (hk_name, vtype, hk_value, decrip) VALUES('o_albumnum', 'string', '5', '');
INSERT INTO pw_hack (hk_name, vtype, hk_value, decrip) VALUES('o_albumnum2', 'string', '2', '');
INSERT INTO pw_hack (hk_name, vtype, hk_value, decrip) VALUES('o_maxphotonum', 'string', '20', '');
INSERT INTO pw_hack (hk_name, vtype, hk_value, decrip) VALUES('o_mkdir', 'string', '1', '');
INSERT INTO pw_hack (hk_name, vtype, hk_value, decrip) VALUES('o_maxfilesize', 'string', '500', '');
INSERT INTO pw_hack (hk_name, vtype, hk_value, decrip) VALUES('o_shownum', 'string', '500', '');
INSERT INTO pw_hack (hk_name, vtype, hk_value, decrip) VALUES('o_attachdir', 'string', '2', '');
INSERT INTO pw_hack (hk_name, vtype, hk_value, decrip) VALUES('o_uploadsize', 'string', 'a:5:{s:3:"jpg";s:3:"300";s:4:"jpeg";s:3:"300";s:3:"png";s:3:"400";s:3:"gif";s:3:"400";s:3:"bmp";s:3:"400";}', '');
INSERT INTO pw_hack (hk_name, vtype, hk_value, decrip) VALUES('o_remove', 'string', '1', '');
INSERT INTO pw_hack (hk_name, vtype, hk_value, decrip) VALUES('o_newcolony', 'string', '1', '');
INSERT INTO pw_hack (hk_name, vtype, hk_value, decrip) VALUES('md_ifapply', 'string', '1', '');
INSERT INTO pw_hack (hk_name, vtype, hk_value, decrip) VALUES('md_appgroups', 'string', '', '');
INSERT INTO pw_hack (hk_name, vtype, hk_value, decrip) VALUES('o_diary_gdcheck', 'string', '0', '');
INSERT INTO pw_hack (hk_name, vtype, hk_value, decrip) VALUES('o_diary_qcheck', 'string', '0', '');
INSERT INTO pw_hack (hk_name, vtype, hk_value, decrip) VALUES('o_diary_groups', 'string', '', '');
INSERT INTO pw_hack (hk_name, vtype, hk_value, decrip) VALUES('o_diarylimit', 'string', '0', '');
INSERT INTO pw_hack (hk_name, vtype, hk_value, decrip) VALUES('o_diarypertime', 'string', '0', '');
INSERT INTO pw_hack (hk_name, vtype, hk_value, decrip) VALUES('o_groups_gdcheck', 'string', '0', '');
INSERT INTO pw_hack (hk_name, vtype, hk_value, decrip) VALUES('o_groups_p_gdcheck', 'string', '0', '');
INSERT INTO pw_hack (hk_name, vtype, hk_value, decrip) VALUES('o_groups_qcheck', 'string', '0', '');
INSERT INTO pw_hack (hk_name, vtype, hk_value, decrip) VALUES('o_groups_p_qcheck', 'string', '0', '');
INSERT INTO pw_hack (hk_name, vtype, hk_value, decrip) VALUES('o_share_groups', 'string', '', '');
INSERT INTO pw_hack (hk_name, vtype, hk_value, decrip) VALUES('o_share_gdcheck', 'string', '0', '');
INSERT INTO pw_hack (hk_name, vtype, hk_value, decrip) VALUES('o_share_qcheck', 'string', '0', '');
INSERT INTO pw_hack (hk_name, vtype, hk_value, decrip) VALUES('o_photos_gdcheck', 'string', '0', '');
INSERT INTO pw_hack (hk_name, vtype, hk_value, decrip) VALUES('o_photos_qcheck', 'string', '0', '');
INSERT INTO pw_hack (hk_name, vtype, hk_value, decrip) VALUES('o_photos_groups', 'string', '', '');
INSERT INTO pw_hack (hk_name, vtype, hk_value, decrip) VALUES('o_browseopen', 'string', '1', '');
INSERT INTO pw_hack (hk_name, vtype, hk_value, decrip) VALUES('o_browse', 'string', '511', '');
INSERT INTO pw_hack (hk_name, vtype, hk_value, decrip) VALUES('o_invite', 'string', '1', '');
INSERT INTO pw_hack (hk_name, vtype, hk_value, decrip) VALUES('o_indexset', 'string', '1023', '');
INSERT INTO pw_hack(hk_name, hk_value) VALUES('area_catetpl', 'default');
INSERT INTO pw_hack(hk_name, hk_value) VALUES('area_indextpl', 'default');
INSERT INTO pw_hack (hk_name, vtype, hk_value, decrip) VALUES('o_hot_open', 'string', '1', '');
INSERT INTO pw_hack (hk_name, vtype, hk_value, decrip) VALUES('o_hot_groups', 'string', ',3,4,5,16,', '');
INSERT INTO pw_hack (hk_name, vtype, hk_value, decrip) VALUES ('ft_member', 'string', '', '');
INSERT INTO pw_hack (hk_name, vtype, hk_value, decrip) VALUES ('ft_update_num', 'string', '0', '');
INSERT INTO pw_hack (hk_name, vtype, hk_value, decrip) VALUES ('ft_msg', 'string', '0', '');
INSERT INTO pw_hack (hk_name, vtype, hk_value, decrip) VALUES('o_uskin','array','a:6:{s:5:"black";s:6:"黑色";s:7:"default";s:6:"翱翔";s:3:"ink";s:6:"山水";s:4:"love";s:6:"友情";s:8:"navgreen";s:6:"礼物";s:8:"navyblue";s:6:"深蓝";}','');
INSERT INTO pw_hack (hk_name, vtype, hk_value, decrip) VALUES('o_weibophoto','string','1','');
INSERT INTO pw_hack (hk_name, vtype, hk_value, decrip) VALUES('o_weibourl','string','1','');
INSERT INTO pw_hack VALUES('o_groups_upgrade','array','a:7:{s:4:"tnum";s:1:"1";s:4:"pnum";s:3:"0.2";s:7:"members";s:1:"1";s:8:"albumnum";s:3:"0.5";s:8:"photonum";s:1:"1";s:8:"writenum";s:1:"1";s:11:"activitynum";s:1:"2";}','');
INSERT INTO pw_hack VALUES('o_groups_level','array','a:5:{i:1;s:12:"初级群组";i:2;s:12:"中级群组";i:3;s:12:"高级群组";i:4;s:12:"官方群组";i:5;s:12:"商业群组";}','');
INSERT INTO pw_hack VALUES('o_groups_levelneed','array','a:3:{i:1;s:1:"0";i:2;s:3:"500";i:3;s:4:"1000";}','');
INSERT INTO pw_hack VALUES ('area_statictime','string','15','');
INSERT INTO pw_hack VALUES ('o_weibotip','string','有什么新鲜事想告诉大家？','');
INSERT INTO pw_hack VALUES ('o_ifcommend','string','0','');
INSERT INTO pw_hack VALUES ('o_senderid','string','1','');
INSERT INTO pw_hack VALUES ('o_punchopen','string','1','');
INSERT INTO pw_hack VALUES ('o_punch_reward','string','a:4:{s:4:"type";s:5:"money";s:3:"num";s:1:"5";s:8:"category";s:6:"credit";s:11:"information";s:17:"可获得 铜币 ";}','');
INSERT INTO pw_hack (hk_name, vtype, hk_value, decrip) VALUES ('o_weibopost','string','1','');


DROP TABLE IF EXISTS pw_help;
CREATE TABLE pw_help (
  hid smallint(6) unsigned NOT NULL auto_increment,
  hup smallint(6) unsigned NOT NULL default '0',
  lv tinyint(3) NOT NULL default '0',
  fathers varchar(100) NOT NULL default '',
  ifchild tinyint(3) NOT NULL default '0',
  title varchar(80) NOT NULL default '',
  url varchar(80) NOT NULL default '',
  content mediumtext NOT NULL,
  vieworder tinyint(3) NOT NULL default '0',
  ispw tinyint(3) default '0',
  PRIMARY KEY  (hid),
  KEY idx_hup (hup)
) TYPE=MyISAM;

INSERT INTO pw_help (hid, hup, lv, fathers, ifchild, title, url, content, vieworder, ispw) VALUES('8','7','2','47,7','1','发表主题','','<b>如何发表主题？</b>\n在帖子列表页面和帖子阅读页面，可以看到“发表新帖”图标，点击即可进入主题帖发布页面，如果没有发帖权限，会有提示“本论坛只有特定用户组才能发表主题,请到其他版块发贴,以提高等级!”出现。\n特别地，当鼠标停在“发表新帖”图标上时，如果你在该板块有发表交易贴、悬赏帖或投票帖的权限时，就会出现一个下拉菜单，菜单项里显示：交易、悬赏、投票，点击需要的帖子类型即可进入相应的主题发表页面发布新的主题。\n你也可以在帖子列表页面底部的快速发帖框发表普通主题帖。\n\n<b>如何发表匿名帖？</b>\n在帖子列表页面和帖子阅读页面，点击“发表新帖”图标进入发帖页面，在发帖时勾选内容编辑器下面的匿名帖复选框，或者在快速发帖处勾选（如果复选框呈灰色，说明该板块不允许发布匿名贴或者您的权限不够）。','2','1');
INSERT INTO pw_help (hid, hup, lv, fathers, ifchild, title, url, content, vieworder, ispw) VALUES('77','58','2','51,58','0','如何创建群组？','','对于已登录的会员，在个人中心中点击“群组”进入添加新群组，根据提示信息提交即可。','0','0');
INSERT INTO pw_help (hid, hup, lv, fathers, ifchild, title, url, content, vieworder, ispw) VALUES('78','58','2','51,58','0','设置群管理员','','如果你是群创始人，那么你就有权利去设定该群的管理员。进入群的成员列表，选择相关的成员设置为管理员即可。','0','0');
INSERT INTO pw_help (hid, hup, lv, fathers, ifchild, title, url, content, vieworder, ispw) VALUES('79','58','2','51,58','0','群组设置私有','','群组可以设置成私有。\n1.将群组加入权限设置成不允许任何人加入。\n2.将群组内容设置为不公开。\n3.将群组相册设置为不公开。','0','0');
INSERT INTO pw_help (hid, hup, lv, fathers, ifchild, title, url, content, vieworder, ispw) VALUES('84','0','0','','1','新手导航','','','0','0');
INSERT INTO pw_help (hid, hup, lv, fathers, ifchild, title, url, content, vieworder, ispw) VALUES('85','0','0','','1','站点应用','','','0','0');
INSERT INTO pw_help (hid, hup, lv, fathers, ifchild, title, url, content, vieworder, ispw) VALUES('86','84','1','84','1','账号相关','','','0','0');
INSERT INTO pw_help (hid, hup, lv, fathers, ifchild, title, url, content, vieworder, ispw) VALUES('87','84','1','84','1','帖子相关','','','0','0');
INSERT INTO pw_help (hid, hup, lv, fathers, ifchild, title, url, content, vieworder, ispw) VALUES('88','84','1','84','1','个人中心相关','','个人中心是站点会员在网站社区中的“家”，在这里，会员可以发表日志，上传照片，关注好友以及使用各种在线应用。','0','0');
INSERT INTO pw_help (hid, hup, lv, fathers, ifchild, title, url, content, vieworder, ispw) VALUES('89','84','1','84','1','常用功能','','','0','0');
INSERT INTO pw_help (hid, hup, lv, fathers, ifchild, title, url, content, vieworder, ispw) VALUES('90','85','1','85','1','日志相关','','注册成为网站会员后，可以在自己的个人中心中记录日志，日志中可以上传附件，例如照片等信息，日志可以分类保存，并且可以设置浏览权限。','0','0');
INSERT INTO pw_help (hid, hup, lv, fathers, ifchild, title, url, content, vieworder, ispw) VALUES('91','85','1','85','0','相册相关','','注册成为网站会员后，可以在自己的个人中心中设置相册，上传照片，可以自定义相册名称，并且可以设置浏览权限。','0','0');
INSERT INTO pw_help (hid, hup, lv, fathers, ifchild, title, url, content, vieworder, ispw) VALUES('92','85','1','85','1','群组相关','','群组是具有相同话题和兴趣的会员交流的场所，会员可以查找感兴趣的群组并申请加入，待相关群主审核通过后就可进入群组。会员在获得站点授权后可以创建自己的群组。','0','0');
INSERT INTO pw_help (hid, hup, lv, fathers, ifchild, title, url, content, vieworder, ispw) VALUES('93','85','1','85','0','新鲜事相关','','会员可以随时发布新鲜事，关注你的会员会在第一时间获取到你的新鲜事信息，你也可以通过关注别人来获取别人的最新消息。','0','0');
INSERT INTO pw_help (hid, hup, lv, fathers, ifchild, title, url, content, vieworder, ispw) VALUES('94','86','2','84,86','0','如何注册成为会员？','','如果您还没有注册，是以游客状态浏览论坛的，在头部导航栏可以看到“您尚未　登录&nbsp; 注册”的字样，点击“注册”，填写相应的信息，就可以完成注册了。因站点设置的不同，游客的浏览及使用论坛的权限会受到很多限制，如果您喜欢本站，建议您马上注册。','0','0');
INSERT INTO pw_help (hid, hup, lv, fathers, ifchild, title, url, content, vieworder, ispw) VALUES('95','86','2','84,86','0','如何登录？','','如果您已经是注册会员，可以在网站首页头部的登录模块进行登录，也可以在页面头部导航栏点击“登录”，进入登录页面进行登录，在限制游客访问的页面，也会有登录提示页面出现。','0','0');
INSERT INTO pw_help (hid, hup, lv, fathers, ifchild, title, url, content, vieworder, ispw) VALUES('96','86','2','84,86','0','忘记密码改怎么办？','','如果您忘记密码，请在登录页面点击“找回密码” 并输入用户名，系统将自动发送密码到您注册时填写的电子邮箱中。','0','0');
INSERT INTO pw_help (hid, hup, lv, fathers, ifchild, title, url, content, vieworder, ispw) VALUES('97','86','2','84,86','0','如何编辑个人资料？','','点击页面右上角“设置-帐号”，进入设置账号的页面，就可以对自己的资料信息进行编辑和修改了。','0','0');
INSERT INTO pw_help (hid, hup, lv, fathers, ifchild, title, url, content, vieworder, ispw) VALUES('98','87','2','84,87','0','发表主题','','在帖子列表页面和帖子阅读页面，可以看到“新帖”图标，点击即可进入主题帖发布页面，如果没有发帖权限，会有提示“本论坛只有特定用户组才能发表主题,请到其他版块发帖,以提高等级!”出现。','0','0');
INSERT INTO pw_help (hid, hup, lv, fathers, ifchild, title, url, content, vieworder, ispw) VALUES('99','87','2','84,87','0','发表出售帖','','在帖子列表页面和帖子阅读页面，点击“新帖”图标进入主题帖发布页面，发帖时在帖子编辑器下方找到“出售此帖”，在前面的复选框理勾选（如果复选框呈灰色，说明该版块不允许发布交易帖或者您的权限不够），填写好会员读帖需要支付的金钱数量（注意不能超过支付最大值）。','0','0');
INSERT INTO pw_help (hid, hup, lv, fathers, ifchild, title, url, content, vieworder, ispw) VALUES('100','87','2','84,87','0','发表特殊主题','','在帖子列表页面和帖子阅读页面，当鼠标停在新帖图标上时，如果你在该版块有发表特殊主题（如投票帖、辩论帖等）的权限时，就会出现一个下拉菜单，菜单项里显示：投票、辩论，点击需要的帖子类型即可进入相应的主题发表页面发布新的主题。','0','0');
INSERT INTO pw_help (hid, hup, lv, fathers, ifchild, title, url, content, vieworder, ispw) VALUES('101','87','2','84,87','0','发表匿名帖','','在帖子列表页面和帖子阅读页面，点击“新帖”图标进入发帖页面，在编辑框上方的设置区内勾选“匿名”就可以发表匿名帖子。（如果复选框呈灰色，说明该版块不允许发布匿名帖或者您的权限不够）。','0','0');
INSERT INTO pw_help (hid, hup, lv, fathers, ifchild, title, url, content, vieworder, ispw) VALUES('102','87','2','84,87','0','发表回复','','在帖子阅读页面点击“回复”按钮进入回复页面回复主题帖，也可以在页面下方的快速发帖处进行回复。','0','0');
INSERT INTO pw_help (hid, hup, lv, fathers, ifchild, title, url, content, vieworder, ispw) VALUES('103','87','2','84,87','0','引用功能','','在需要引用的帖子楼层上点击引用，即可引用当前楼层内容，也可以用Wind Code代码进行引用，把需要引用的内容放入[quote] 您要引用的文字[/quote]中间即可。','0','0');
INSERT INTO pw_help (hid, hup, lv, fathers, ifchild, title, url, content, vieworder, ispw) VALUES('104','87','2','84,87','0','附件上传','','在发帖页面下的附件上传处点击浏览按钮，上传有效后缀类型的附件，同时可以在描述框对附件进行描述，并设置下载附件所需要的威望值。','0','0');
INSERT INTO pw_help (hid, hup, lv, fathers, ifchild, title, url, content, vieworder, ispw) VALUES('105','88','2','84,88','0','如何进入个人中心？','','站点会员在登录的状态下可以进入个人中心，点击页面右上角的“我的快捷通道-个人中心”就可以进入到个人中心了。','0','0');
INSERT INTO pw_help (hid, hup, lv, fathers, ifchild, title, url, content, vieworder, ispw) VALUES('106','88','2','84,88','0','如何添加好友？','','首先进入个人中心的朋友模块，然后可以选择添加好友的方式是查找好友或者邀请好友，查找好友的话，首先搜索到要添加为好友的用户，点击“加为好友”，然后待对方同意后，就可以成为好友了；邀请好友的话，首先向好友发送邀请链接，待好友注册后，操作“加为好友”，待对方确认后就可以添加为好友了。','0','0');
INSERT INTO pw_help (hid, hup, lv, fathers, ifchild, title, url, content, vieworder, ispw) VALUES('107','89','2','84,89','0','找回密码','','如果您忘记密码，请在登录页面点击“找回密码” 并输入用户名，系统将自动发送密码到您的有效电子邮箱中。','0','0');
INSERT INTO pw_help (hid, hup, lv, fathers, ifchild, title, url, content, vieworder, ispw) VALUES('108','89','2','84,89','0','收藏功能','','等于已登录的用户，在帖子阅读页的主题帖中，点击“收藏”按钮，可以将帖子收藏到自己的收藏夹中，会员可以在个人中心的收藏模块中查看历史收藏的帖子。','0','0');
INSERT INTO pw_help (hid, hup, lv, fathers, ifchild, title, url, content, vieworder, ispw) VALUES('109','89','2','84,89','0','短消息功能','','可以通过会员头像信息栏或者帖子楼层短消息按钮实现会员之间互发短消息。','0','0');
INSERT INTO pw_help (hid, hup, lv, fathers, ifchild, title, url, content, vieworder, ispw) VALUES('110','89','2','84,89','0','帖子举报功能','','协助站长进行帖子监控、举报不良帖子推荐优秀帖子的功能。在帖子楼层操作栏点击“举报”填写理由并提交就能实现了对当前楼层帖子举报的操作。','0','0');
INSERT INTO pw_help (hid, hup, lv, fathers, ifchild, title, url, content, vieworder, ispw) VALUES('111','90','2','85,90','0','如何发表日志？','','会员登录站点后，进入个人中心，点击右上角的“写日志”按钮就会进入撰写日志的页面，撰写完毕后，点击“提交”按钮，日志发表成功。','0','0');
INSERT INTO pw_help (hid, hup, lv, fathers, ifchild, title, url, content, vieworder, ispw) VALUES('112','90','2','85,90','0','如何设置浏览权限？','','在写新日志时，修改日志的隐私设置，目前日志的浏览权限分为全站可见、仅好友可见、尽自己可见三种类别。','0','0');
INSERT INTO pw_help (hid, hup, lv, fathers, ifchild, title, url, content, vieworder, ispw) VALUES('16','7','2','47,7','0','发表回复','','<b>如何发表回复？</b>\n1.回复主题：帖子阅读页面点击“回复”按钮进入回复页面，或使用页面下方的快速发帖框即可；\n2.回复某楼层：点击该楼层中的“回复”，转到到快速回复框进行回复','3','1');
INSERT INTO pw_help (hid, hup, lv, fathers, ifchild, title, url, content, vieworder, ispw) VALUES('17','7','2','47,7','0','附件使用','','<b>如何发表附件？</b>\n在帖子编辑页面底部附带了附件上传。\n1.普通上传，表示一次上传一个文件，点击[选择文件]选择本地的文件，插入到编辑内容后，才能上传。\n2.批量上传，一次最多可上次15个文件，点击[选择文件]选择本地的文件进行上传，上传完毕后插入到编辑内容。\n\n<b>如何设置附件购买？</b>\n附件普通上传时，设置查看附件需要消耗的积分类型、积分值即可。','4','1');
INSERT INTO pw_help (hid, hup, lv, fathers, ifchild, title, url, content, vieworder, ispw) VALUES('70','54','4','50,53,64,54','0','发表特殊主题','','在帖子列表页面和帖子阅读页面，当鼠标停在新帖图标上时，如果你在该版块有发表特殊主题（如投票帖、辩论帖等）的权限时，就会出现一个下拉菜单，菜单项里显示：投票、辩论，点击需要的帖子类型即可进入相应的主题发表页面发布新的主题。','0','0');
INSERT INTO pw_help (hid, hup, lv, fathers, ifchild, title, url, content, vieworder, ispw) VALUES('71','54','4','50,53,64,54','0','发表匿名帖','','在帖子列表页面和帖子阅读页面，点击“新帖”图标进入发帖页面，在编辑框上方的设置区内勾选“匿名”就可以发表匿名帖子。（如果复选框呈灰色，说明该版块不允许发布匿名帖或者您的权限不够）。','0','0');
INSERT INTO pw_help (hid, hup, lv, fathers, ifchild, title, url, content, vieworder, ispw) VALUES('72','54','4','50,53,64,54','0','发表回复','','在帖子阅读页面点击“回复”按钮进入回复页面回复主题帖，也可以在页面下方的快速发帖处进行回复。','0','0');
INSERT INTO pw_help (hid, hup, lv, fathers, ifchild, title, url, content, vieworder, ispw) VALUES('73','54','4','50,53,64,54','0','引用功能','','在需要引用的帖子楼层上点击引用，即可引用当前楼层内容，也可以用Wind Code代码进行引用，把需要引用的内容放入[quote] 您要引用的文字[/quote]中间即可。','0','0');
INSERT INTO pw_help (hid, hup, lv, fathers, ifchild, title, url, content, vieworder, ispw) VALUES('74','54','4','50,53,64,54','0','附件上传','','在发帖页面下的附件上传处点击浏览按钮，上传有效后缀类型的附件，同时可以在描述框对附件进行描述，并设置下载附件所需要的威望值。','0','0');
INSERT INTO pw_help (hid, hup, lv, fathers, ifchild, title, url, content, vieworder, ispw) VALUES('75','55','2','50,55','0','如何进入个人中心？','','站点会员在登录的状态下可以进入个人中心，点击页面右上角的“我的快捷通道-个人中心”就可以进入到个人中心了。','0','0');
INSERT INTO pw_help (hid, hup, lv, fathers, ifchild, title, url, content, vieworder, ispw) VALUES('76','55','2','50,55','0','如何添加好友？','','首先进入个人中心的朋友模块，然后可以选择添加好友的方式是查找好友或者邀请好友，查找好友的话，首先搜索到要添加为好友的用户，点击“加为好友”，然后待对方同意后，就可以成为好友了；邀请好友的话，首先向好友发送邀请链接，待好友注册后，操作“加为好友”，待对方确认后就可以添加为好友了。','0','0');
INSERT INTO pw_help (hid, hup, lv, fathers, ifchild, title, url, content, vieworder, ispw) VALUES('67','53','2','50,53','0','编辑个人资料','','点击页面右上角“设置-帐号”，进入设置账号的页面，就可以对自己的资料信息进行编辑和修改了。','0','0');
INSERT INTO pw_help (hid, hup, lv, fathers, ifchild, title, url, content, vieworder, ispw) VALUES('68','54','4','50,53,64,54','0','发表主题','','在帖子列表页面和帖子阅读页面，可以看到“新帖”图标，点击即可进入主题帖发布页面，如果没有发帖权限，会有提示“本论坛只有特定用户组才能发表主题,请到其他版块发帖,以提高等级!”出现。','0','0');
INSERT INTO pw_help (hid, hup, lv, fathers, ifchild, title, url, content, vieworder, ispw) VALUES('69','54','4','50,53,64,54','0','发表出售帖','','在帖子列表页面和帖子阅读页面，点击“新帖”图标进入主题帖发布页面，发帖时在帖子编辑器下方找到“出售此帖”，在前面的复选框理勾选（如果复选框呈灰色，说明该版块不允许发布交易帖或者您的权限不够），填写好会员读帖需要支付的金钱数量（注意不能超过支付最大值）。','0','0');
INSERT INTO pw_help (hid, hup, lv, fathers, ifchild, title, url, content, vieworder, ispw) VALUES('65','53','2','50,53','0','如何登录？','','如果您已经是注册会员，可以在网站首页头部的登录模块进行登录，也可以在页面头部导航栏点击“登录”，进入登录页面进行登录，在限制游客访问的页面，也会有登录提示页面出现。','0','0');
INSERT INTO pw_help (hid, hup, lv, fathers, ifchild, title, url, content, vieworder, ispw) VALUES('66','53','2','50,53','0','忘记密码怎么办','','如果您忘记密码，请在登录页面点击“找回密码” 并输入用户名，系统将自动发送密码到您注册时填写的电子邮箱中。','0','0');
INSERT INTO pw_help (hid, hup, lv, fathers, ifchild, title, url, content, vieworder, ispw) VALUES('54','64','3','50,53,64','1','帖子相关','','撒地方撒地方','0','0');
INSERT INTO pw_help (hid, hup, lv, fathers, ifchild, title, url, content, vieworder, ispw) VALUES('113','92','2','85,92','0','如何创建群组？','','对于已登录的会员，在个人中心中点击“群组”进入添加新群组，根据提示信息提交即可。','0','0');
INSERT INTO pw_help (hid, hup, lv, fathers, ifchild, title, url, content, vieworder, ispw) VALUES('64','53','2','50,53','1','注册成为会员？','','如果您还没有注册，是以游客状态浏览论坛的，在头部导航栏可以看到“您尚未　登录&nbsp; 注册”的字样，点击“注册”，填写相应的信息，就可以完成注册了。因站点设置的不同，游客的浏览及使用论坛的权限会受到很多限制，如果您喜欢这个论坛，建议您马上注册。','0','0');
INSERT INTO pw_help (hid, hup, lv, fathers, ifchild, title, url, content, vieworder, ispw) VALUES('40','39','2','38,39','0','设置模块属性','','','1','0');
INSERT INTO pw_help (hid, hup, lv, fathers, ifchild, title, url, content, vieworder, ispw) VALUES('41','39','2','38,39','0','设置模块书香','http://www.baidu.com','啊打三分啊打三分啊打三分啊打三分啊打三分啊打三分啊打三分啊打三分啊打三分啊打三分啊打三分啊打三分啊打三分','2','0');
INSERT INTO pw_help (hid, hup, lv, fathers, ifchild, title, url, content, vieworder, ispw) VALUES('42','13','2','26,13','0','啊打算分手','','','1','0');
INSERT INTO pw_help (hid, hup, lv, fathers, ifchild, title, url, content, vieworder, ispw) VALUES('43','8','3','47,7,8','0','发表主题三级标','','发表主题三级标题发表主题三级标题发表主题三级标题发表主题三级标题发表主题三级标题发表主题三级标题发表主题三级标题发表主题三级标题发表主题三级标题发表主题三级标题','0','0');
INSERT INTO pw_help (hid, hup, lv, fathers, ifchild, title, url, content, vieworder, ispw) VALUES('114','92','2','85,92','0','如何设置群管理员？','','如果你是群创始人，那么你就有权利去设定该群的管理员。进入群的成员列表，选择相关的成员设置为管理员即可。','0','0');
INSERT INTO pw_help (hid, hup, lv, fathers, ifchild, title, url, content, vieworder, ispw) VALUES('115','92','2','85,92','0','群组是否可以设置为私有？','','群组可以设置成私有。\n1.将群组加入权限设置成不允许任何人加入。\n2.将群组内容设置为不公开。\n3.将群组相册设置为不公开。','0','0');

DROP TABLE IF EXISTS pw_invitecode;
CREATE TABLE pw_invitecode (
  id mediumint(8) unsigned NOT NULL auto_increment,
  invcode varchar(40) NOT NULL default '',
  uid int(10) unsigned NOT NULL default '0',
  receiver varchar(20) NOT NULL default '',
  createtime int(10) unsigned NOT NULL default '0',
  usetime int(10) unsigned NOT NULL default '0',
  ifused tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (id),
  KEY idx_uid (uid),
  KEY idx_invcode (invcode)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_inviterecord;
CREATE TABLE pw_inviterecord (
  id int(10) unsigned NOT NULL auto_increment,
  uid int(10) unsigned NOT NULL default '0',
  username varchar(15) NOT NULL default '',
  unit varchar(15) NOT NULL default '',
  reward int(10) NOT NULL default '0',
  ip varchar(16) NOT NULL default '',
  typeid tinyint(3) NOT NULL default '0',
  create_time int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (id),
  KEY idx_uid_ip (uid,ip),
  KEY idx_typeid (typeid)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_invoke;
CREATE TABLE pw_invoke (
  id smallint(6) unsigned NOT NULL auto_increment,
  name varchar(50) NOT NULL default '',
  tplid smallint(6) NOT NULL default '0',
  tagcode TEXT NOT NULL,
  parsecode text NOT NULL,
  title varchar(80) NOT NULL default '',
  PRIMARY KEY  (id),
  UNIQUE KEY idx_name (name)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_invokepiece;
CREATE TABLE pw_invokepiece (
  id smallint(6) unsigned NOT NULL auto_increment,
  invokename varchar(50) NOT NULL default '',
  title varchar(50) NOT NULL default '',
  action varchar(30) NOT NULL default '',
  config text NOT NULL default '',
  num smallint(6) NOT NULL default '0',
  param text NOT NULL,
  cachetime int(10) unsigned NOT NULL default '0',
  ifpushonly tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (id),
  UNIQUE KEY idx_invokename ( invokename , title )
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_ipstates;
CREATE TABLE pw_ipstates (
  day varchar(10) NOT NULL default '',
  month varchar(7) NOT NULL default '',
  nums int(10) NOT NULL default '0',
  PRIMARY KEY  (day)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_job;
CREATE TABLE pw_job (
  id int(10) unsigned NOT NULL AUTO_INCREMENT,
  title varchar(255) default '',
  description text,
  icon varchar(255) default '',
  starttime int(10) unsigned NOT NULL default '0',
  endtime int(10) unsigned NOT NULL default '0',
  period smallint(6) unsigned NOT NULL default '0',
  reward text,
  sequence smallint(3) NOT NULL default '0',
  usergroup varchar(255) NOT NULL default '',
  prepose int(10) unsigned NOT NULL default '0',
  number int(10) NOT NULL default '0',
  member tinyint(3) NOT NULL default '0',
  auto tinyint(3) NOT NULL default '0',
  finish tinyint(3) NOT NULL default '0',
  display tinyint(3) NOT NULL default '0',
  type tinyint(3) NOT NULL default '0',
  job varchar(255) NOT NULL default '',
  factor text,
  isopen tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY (id)
) ENGINE=MyISAM;
INSERT INTO pw_job VALUES('1','更新个人头像','上传自己的头像，给大家留个好印象吧','','1259896560','0','0','a:4:{s:4:"type";s:5:"money";s:3:"num";s:2:"10";s:8:"category";s:6:"credit";s:11:"information";s:17:"可获得 铜币 ";}','2','8','0','0','0','1','0','1','0','doUpdateAvatar','a:1:{s:5:"limit";s:0:"";}','1');
INSERT INTO pw_job VALUES('2','完善个人资料','要让大家了解你，就要先更新自己的个人资料哦','','1259896260','0','0','a:4:{s:4:"type";s:5:"money";s:3:"num";s:2:"10";s:8:"category";s:6:"credit";s:11:"information";s:17:"可获得 铜币 ";}','1','8','0','0','0','1','1','1','0','doUpdatedata','a:1:{s:5:"limit";s:0:"";}','1');
INSERT INTO pw_job VALUES('3','给admin发送消息','要和大家熟悉起来，一定要学会发消息哦，还可以顺便问问题','','1259694720','0','0','a:4:{s:4:"type";s:5:"money";s:3:"num";s:2:"10";s:8:"category";s:6:"credit";s:11:"information";s:17:"可获得 铜币 ";}','3','8','0','0','0','1','1','1','0','doSendMessage','a:2:{s:4:"user";s:5:"admin";s:5:"limit";s:0:"";}','1');
INSERT INTO pw_job VALUES('4','寻找并添加5个好友','去找找有没有志同道合的朋友？加他们为好友吧','','1259694780','0','0','a:4:{s:4:"type";s:5:"money";s:3:"num";s:2:"10";s:8:"category";s:6:"credit";s:11:"information";s:17:"可获得 铜币 ";}','4','8','0','0','0','1','1','1','0','doAddFriend','a:4:{s:4:"user";s:0:"";s:4:"type";s:1:"2";s:3:"num";s:1:"5";s:5:"limit";s:0:"";}','1');
INSERT INTO pw_job VALUES('5','论坛每日红包','发红包咯！每天报到都有红包','','1259694840','0','24','a:4:{s:4:"type";s:5:"money";s:3:"num";s:2:"10";s:8:"category";s:6:"credit";s:11:"information";s:17:"可获得 铜币 ";}','7','','0','0','0','0','1','0','0','doSendGift','','1');

DROP TABLE IF EXISTS pw_jober;
CREATE TABLE pw_jober (
  id int(10) unsigned NOT NULL AUTO_INCREMENT,
  jobid int(10) unsigned NOT NULL default '0',
  userid int(10) unsigned NOT NULL default '0',
  current tinyint(1) NOT NULL default '0',
  step smallint(6) NOT NULL default '0',
  last int(10) unsigned NOT NULL default '0',
  next int(10) unsigned NOT NULL default '0',
  status tinyint(1) NOT NULL default '0',
  creattime int(10) unsigned NOT NULL default '0',
  total smallint(6) unsigned NOT NULL default '0',
  PRIMARY KEY (id),
  KEY idx_jobid_userid (jobid,userid),
  KEY idx_userid_status (userid,status)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS pw_log_colonys;
CREATE TABLE pw_log_colonys(
    id int(10) unsigned not null auto_increment,
    sid int(10) unsigned not null default '0',
    operate tinyint(3) not null default '1',
    modified_time int(10) unsigned not null default '0',
    primary key(id),
    unique key idx_sid_operate(sid,operate)
)ENGINE=MyISAM;

DROP TABLE IF EXISTS pw_log_diary;
CREATE TABLE pw_log_diary(
    id int(10) unsigned not null auto_increment,
    sid int(10) unsigned not null default '0',
    operate tinyint(3) not null default '1',
    modified_time int(10) unsigned not null default '0',
    primary key(id),
    unique key idx_sid_operate(sid,operate)
)ENGINE=MyISAM;

DROP TABLE IF EXISTS pw_log_members;
CREATE TABLE pw_log_members(
    id int(10) unsigned not null auto_increment,
    sid int(10) unsigned not null default '0',
    operate tinyint(3) not null default '1',
    modified_time int(10) unsigned not null default '0',
    primary key(id),
    unique key idx_sid_operate(sid,operate)
)ENGINE=MyISAM;

DROP TABLE IF EXISTS pw_log_posts;
CREATE TABLE pw_log_posts(
    id int(10) unsigned not null auto_increment,
    sid int(10) unsigned not null default '0',
    operate tinyint(3) not null default '1',
    modified_time int(10) unsigned not null default '0',
    primary key(id),
    unique key idx_sid_operate(sid,operate)
)ENGINE=MyISAM;

DROP TABLE IF EXISTS pw_log_threads;
CREATE TABLE pw_log_threads(
    id int(10) unsigned not null auto_increment,
    sid int(10) unsigned not null default '0',
    operate tinyint(3) not null default '1',
    modified_time int(10) unsigned not null default '0',
    primary key(id),
    unique key idx_sid_operate(sid,operate)
)ENGINE=MyISAM;

DROP TABLE IF EXISTS pw_medalinfo;
CREATE TABLE pw_medalinfo (
  id mediumint(8) NOT NULL AUTO_INCREMENT,
  name varchar(40) NOT NULL default '',
  intro varchar(255) NOT NULL default '',
  picurl varchar(255) NOT NULL default '',
  PRIMARY KEY  (id)
) TYPE=MyISAM;
INSERT INTO pw_medalinfo(id,name,intro,picurl) VALUES (1, '{#medalname_1}', '{#medaldesc_1}!','1.gif');
INSERT INTO pw_medalinfo(id,name,intro,picurl) VALUES (2, '{#medalname_2}', '{#medaldesc_2}', '2.gif');
INSERT INTO pw_medalinfo(id,name,intro,picurl) VALUES (3, '{#medalname_3}', '{#medaldesc_3}', '3.gif');
INSERT INTO pw_medalinfo(id,name,intro,picurl) VALUES (4, '{#medalname_4}', '{#medaldesc_4}', '4.gif');
INSERT INTO pw_medalinfo(id,name,intro,picurl) VALUES (5, '{#medalname_5}', '{#medaldesc_5}', '5.gif');
INSERT INTO pw_medalinfo(id,name,intro,picurl) VALUES (6, '{#medalname_6}', '{#medaldesc_6}', '6.gif');
INSERT INTO pw_medalinfo(id,name,intro,picurl) VALUES (7, '{#medalname_7}', '{#medaldesc_7}', '7.gif');
INSERT INTO pw_medalinfo(id,name,intro,picurl) VALUES (8, '{#medalname_8}', '{#medaldesc_8}', '8.gif');
INSERT INTO pw_medalinfo(id,name,intro,picurl) VALUES (9, '{#medalname_9}', '{#medaldesc_9}', '9.gif');
INSERT INTO pw_medalinfo(id,name,intro,picurl) VALUES (10,'{#medalname_10}','{#medaldesc_10}','10.gif');

DROP TABLE IF EXISTS pw_medalslogs;
CREATE TABLE pw_medalslogs (
  id int(10) NOT NULL auto_increment,
  awardee varchar(40) NOT NULL default '',
  awarder varchar(40) NOT NULL default '',
  awardtime int(10) NOT NULL default '0',
  timelimit tinyint(3) NOT NULL default '0',
  state tinyint(3) NOT NULL default '0',
  level smallint(6) NOT NULL DEFAULT '0',
  action tinyint(3) NOT NULL default '0',
  why varchar(255) NOT NULL default '',
  PRIMARY KEY  (id),
  KEY awardee (awardee)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_medaluser;
CREATE TABLE pw_medaluser (
  id mediumint(8) unsigned NOT NULL auto_increment,
  uid int(10) unsigned NOT NULL default '0',
  mid SMALLINT(6) NOT NULL DEFAULT '0',
  PRIMARY KEY  (id),
  KEY idx_uid (uid)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_membercredit;
CREATE TABLE pw_membercredit (
  uid int(10) unsigned NOT NULL default '0',
  cid tinyint(3) NOT NULL default '0',
  value mediumint(8) NOT NULL default '0',
  KEY idx_uid (uid),
  KEY idx_cid (cid),
  KEY idx_cid_value (cid,value)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_memberdata;
CREATE TABLE pw_memberdata (
  uid int(10) unsigned NOT NULL default '1',
  postnum int(10) unsigned NOT NULL default '0',
  digests smallint(6) NOT NULL default '0',
  rvrc int(10) NOT NULL default '0',
  money int(10) NOT NULL default '0',
  credit int(10) NOT NULL default '0',
  currency int(10) NOT NULL default '0',
  lastvisit int(10) unsigned NOT NULL default '0',
  thisvisit int(10) unsigned NOT NULL default '0',
  lastpost int(10) unsigned NOT NULL default '0',
  onlinetime int(10) unsigned NOT NULL default '0',
  monoltime int(10) unsigned NOT NULL default '0',
  todaypost smallint(6) unsigned NOT NULL default '0',
  monthpost smallint(6) unsigned NOT NULL default '0',
  uploadtime int(10) unsigned NOT NULL default '0',
  uploadnum smallint(6) unsigned NOT NULL default '0',
  follows mediumint(8) unsigned NOT NULL,
  fans mediumint(8) unsigned NOT NULL,
  newfans mediumint(8) unsigned NOT NULL,
  newreferto mediumint(8) unsigned NOT NULL,
  newcomment mediumint(8) unsigned NOT NULL DEFAULT '0',
  onlineip varchar(30) NOT NULL default '',
  starttime int(10) unsigned NOT NULL default '0',
  postcheck varchar(16) NOT NULL default '',
  pwdctime int(10) unsigned NOT NULL default '0',
  f_num int(10) unsigned NOT NULL default '0',
  creditpop varchar(150) NOT NULL default '',
  jobnum smallint(3) unsigned NOT NULL default 0,
  lastmsg int(10) unsigned NOT NULL default 0,
  lastgrab int(10) unsigned NOT NULL default 0,
  punch int(10) unsigned not null default '0',
  PRIMARY KEY uid (uid),
  KEY idx_postnum (postnum)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_memberinfo;
CREATE TABLE pw_memberinfo (
  uid int(10) unsigned NOT NULL default '1',
  adsips mediumtext NOT NULL,
  credit text NOT NULL default '',
  deposit int(10) NOT NULL default '0',
  startdate int(10) NOT NULL default '0',
  ddeposit int(10) NOT NULL default '0',
  dstartdate int(10) NOT NULL default '0',
  regreason varchar(255) NOT NULL default '',
  readmsg mediumtext NOT NULL,
  delmsg mediumtext NOT NULL,
  tooltime varchar(42) NOT NULL default '',
  replyinfo varchar(81) NOT NULL default '',
  lasttime int(10) NOT NULL default '0',
  digtid text NOT NULL,
  customdata text NOT NULL,
  tradeinfo text NOT NULL,
  PRIMARY KEY  (uid)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_members;
CREATE TABLE pw_members (
  uid int(10) unsigned NOT NULL auto_increment,
  username varchar(15) NOT NULL default '',
  password varchar(40) NOT NULL default '',
  safecv varchar(10) NOT NULL default '',
  email varchar(60) NOT NULL default '',
  groupid tinyint(3) NOT NULL default '-1',
  memberid tinyint(3) NOT NULL default '0',
  groups varchar(255) NOT NULL default '',
  icon varchar(255) NOT NULL default '',
  gender tinyint(1) NOT NULL default '0',
  regdate int(10) unsigned NOT NULL default '0',
  signature text NOT NULL,
  introduce text NOT NULL,
  oicq varchar(12) NOT NULL default '',
  aliww varchar( 30 ) NOT NULL,
  icq varchar(12) NOT NULL default '',
  msn varchar(35) NOT NULL default '',
  yahoo varchar(35) NOT NULL default '',
  site varchar(75) NOT NULL default '',
  location varchar(36) NOT NULL default '',
  honor varchar(100) NOT NULL default '',
  bday date NOT NULL default '0000-00-00',
  lastaddrst varchar(255) NOT NULL default '',
  yz int(10) NOT NULL default '1',
  timedf varchar(5) NOT NULL default '',
  style varchar(12) NOT NULL default '',
  datefm varchar(15) NOT NULL default '',
  t_num tinyint(3) unsigned NOT NULL default '0',
  p_num tinyint(3) unsigned NOT NULL default '0',
  attach varchar(50) NOT NULL default '',
  hack varchar(255) NOT NULL default '0',
  newpm smallint(6) unsigned NOT NULL default '0',
  banpm text NOT NULL,
  msggroups varchar(255) NOT NULL default '',
  medals varchar(255) NOT NULL default '',
  userstatus int(10) unsigned NOT NULL default '0',
  shortcut varchar(255) NOT NULL default '',
  PRIMARY KEY  (uid),
  UNIQUE KEY idx_username (username),
  KEY idx_groupid (groupid),
  KEY idx_email (email)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_memo;
CREATE TABLE pw_memo (
  mid int(10) unsigned NOT NULL auto_increment,
  username varchar(15) NOT NULL default '',
  postdate int(10) NOT NULL default '0',
  content text NOT NULL,
  isuser tinyint(3) NOT NULL default '0',
  PRIMARY KEY  (mid),
  KEY idx_isuser_username (isuser,username)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_modehot;
CREATE TABLE pw_modehot (
	id int(10) unsigned NOT NULL AUTO_INCREMENT,
	parent_id int(10) unsigned DEFAULT NULL default '0',
	sort tinyint(3) NOT NULL DEFAULT '1',
	tag varchar(20) DEFAULT NULL default '',
	type_name varchar(100) NOT NULL default '',
	filter_type text NOT NULL,
	filter_time text NOT NULL,
	display tinyint(3) NOT NULL DEFAULT '0',
	active tinyint(3) NOT NULL DEFAULT '0',
	remark varchar(100) DEFAULT NULL default '',
	PRIMARY KEY (id)
) TYPE=MyISAM;
INSERT INTO pw_modehot VALUES(1, 0, 1, 'memberHot', '用户排行', 'N;', 'N;', '1', '1', NULL);
INSERT INTO pw_modehot VALUES(2, 1, 1, 'memberOnLine', '在线排行', 'N;', 'a:3:{s:7:"current";s:7:"history";s:7:"filters";a:4:{i:0;s:5:"today";i:1;s:4:"week";i:2;s:5:"month";i:3;s:7:"history";}s:11:"filterItems";a:4:{i:0;s:2:"18";i:1;s:2:"18";i:2;s:2:"18";i:3;s:2:"18";}}', '1', '1', NULL);
INSERT INTO pw_modehot VALUES(3, 1, 2, 'memberCredit', '积分排行', 'a:3:{s:7:"current";s:5:"money";s:7:"filters";a:5:{i:0;s:5:"money";i:1;s:4:"rvrc";i:2;s:6:"credit";i:3;s:8:"currency";i:4;s:1:"4";}s:11:"filterItems";a:5:{i:0;s:2:"18";i:1;s:2:"18";i:2;s:2:"18";i:3;s:2:"18";i:4;s:2:"18";}}', 'N;', '1', '1', NULL);
INSERT INTO pw_modehot VALUES(4, 1, 3, 'memberFriend', '好友排行', 'N;', 'N;', '0', '1', NULL);
INSERT INTO pw_modehot VALUES(5, 1, 4, 'memberThread', '发帖排行', 'N;', 'a:3:{s:7:"current";s:7:"history";s:7:"filters";a:4:{i:0;s:5:"today";i:1;s:4:"week";i:2;s:5:"month";i:3;s:7:"history";}s:11:"filterItems";a:4:{i:0;s:2:"18";i:1;s:2:"18";i:2;s:2:"18";i:3;s:2:"18";}}', '0', '1', NULL);
INSERT INTO pw_modehot VALUES(8, 0, 2, 'threadHot', '帖子排行', 'N;', 'N;', '1', '1', NULL);
INSERT INTO pw_modehot VALUES(9, 8, 1, 'threadPost', '回复排行', 'N;', 'a:3:{s:7:"current";s:7:"history";s:7:"filters";a:4:{i:0;s:5:"today";i:1;s:4:"week";i:2;s:5:"month";i:3;s:7:"history";}s:11:"filterItems";a:4:{i:0;s:2:"10";i:1;s:2:"10";i:2;s:2:"10";i:3;s:2:"10";}}', '1', '1', NULL);
INSERT INTO pw_modehot VALUES(10, 8, 2, 'threadRate', '评价排行', 'a:3:{s:7:"current";s:12:"rateThread_1";s:7:"filters";a:7:{i:0;s:12:"rateThread_1";i:1;s:12:"rateThread_2";i:2;s:12:"rateThread_3";i:3;s:12:"rateThread_4";i:4;s:12:"rateThread_5";i:5;s:12:"rateThread_6";i:6;s:12:"rateThread_7";}s:11:"filterItems";a:7:{i:0;s:2:"10";i:1;s:2:"10";i:2;s:2:"10";i:3;s:2:"10";i:4;s:2:"10";i:5;s:2:"10";i:6;s:2:"10";}}', 'a:3:{s:7:"current";s:7:"history";s:7:"filters";a:4:{i:0;s:5:"today";i:1;s:4:"week";i:2;s:5:"month";i:3;s:7:"history";}s:11:"filterItems";a:4:{i:0;s:2:"10";i:1;s:2:"10";i:2;s:2:"10";i:3;s:2:"10";}}', '0', '1', NULL);
INSERT INTO pw_modehot VALUES(11, 8, 3, 'threadFav', '收藏排行', 'N;', 'a:3:{s:7:"current";s:7:"history";s:7:"filters";a:4:{i:0;s:5:"today";i:1;s:4:"week";i:2;s:5:"month";i:3;s:7:"history";}s:11:"filterItems";a:4:{i:0;s:2:"10";i:1;s:2:"10";i:2;s:2:"10";i:3;s:2:"10";}}', '1', '1', NULL);
INSERT INTO pw_modehot VALUES(13, 0, 3, 'diaryHot', '日志排行', 'N;', 'N;', '1', '1', NULL);
INSERT INTO pw_modehot VALUES(14, 13, 1, 'diaryComment', '评论排行', 'N;', 'a:3:{s:7:"current";s:7:"history";s:7:"filters";a:4:{i:0;s:5:"today";i:1;s:4:"week";i:2;s:5:"month";i:3;s:7:"history";}s:11:"filterItems";a:4:{i:0;s:2:"10";i:1;s:2:"10";i:2;s:2:"10";i:3;s:2:"10";}}', '1', '1', NULL);
INSERT INTO pw_modehot VALUES(15, 13, 2, 'diaryRate', '评价排行', 'a:3:{s:7:"current";s:11:"rateDiary_8";s:7:"filters";a:7:{i:0;s:11:"rateDiary_8";i:1;s:11:"rateDiary_9";i:2;s:12:"rateDiary_10";i:3;s:12:"rateDiary_11";i:4;s:12:"rateDiary_12";i:5;s:12:"rateDiary_13";i:6;s:12:"rateDiary_14";}s:11:"filterItems";a:7:{i:0;s:2:"10";i:1;s:2:"10";i:2;s:2:"10";i:3;s:2:"10";i:4;s:2:"10";i:5;s:2:"10";i:6;s:2:"10";}}', 'a:3:{s:7:"current";s:7:"history";s:7:"filters";a:4:{i:0;s:5:"today";i:1;s:4:"week";i:2;s:5:"month";i:3;s:7:"history";}s:11:"filterItems";a:4:{i:0;s:2:"10";i:1;s:2:"10";i:2;s:2:"10";i:3;s:2:"10";}}', '0', '1', NULL);
INSERT INTO pw_modehot VALUES(16, 13, 3, 'diaryFav', '收藏排行', 'N;', 'a:3:{s:7:"current";s:7:"history";s:7:"filters";a:4:{i:0;s:5:"today";i:1;s:4:"week";i:2;s:5:"month";i:3;s:7:"history";}s:11:"filterItems";a:4:{i:0;s:2:"10";i:1;s:2:"10";i:2;s:2:"10";i:3;s:2:"10";}}', '0', '1', NULL);
INSERT INTO pw_modehot VALUES(18, 0, 4, 'picHot', '照片排行', 'N;', 'N;', '1', '1', NULL);
INSERT INTO pw_modehot VALUES(19, 18, 1, 'picComment', '评论排行', 'N;', 'a:3:{s:7:"current";s:7:"history";s:7:"filters";a:4:{i:0;s:5:"today";i:1;s:4:"week";i:2;s:5:"month";i:3;s:7:"history";}s:11:"filterItems";a:4:{i:0;s:2:"20";i:1;s:2:"20";i:2;s:2:"20";i:3;s:2:"20";}}', '1', '1', NULL);
INSERT INTO pw_modehot VALUES(20, 18, 2, 'picRate', '评价排行', 'a:3:{s:7:"current";s:14:"ratePicture_15";s:7:"filters";a:8:{i:0;s:14:"ratePicture_15";i:1;s:14:"ratePicture_16";i:2;s:14:"ratePicture_17";i:3;s:14:"ratePicture_18";i:4;s:14:"ratePicture_19";i:5;s:14:"ratePicture_20";i:6;s:14:"ratePicture_21";i:7;s:14:"ratePicture_22";}s:11:"filterItems";a:8:{i:0;s:2:"20";i:1;s:2:"20";i:2;s:2:"20";i:3;s:2:"20";i:4;s:2:"20";i:5;s:2:"20";i:6;s:2:"20";i:7;s:2:"20";}}', 'a:3:{s:7:"current";s:7:"history";s:7:"filters";a:4:{i:0;s:5:"today";i:1;s:4:"week";i:2;s:5:"month";i:3;s:7:"history";}s:11:"filterItems";a:4:{i:0;s:2:"20";i:1;s:2:"20";i:2;s:2:"20";i:3;s:2:"20";}}', '0', '1', NULL);
INSERT INTO pw_modehot VALUES(21, 18, 3, 'picFav', '收藏排行', 'N;', 'a:3:{s:7:"current";s:7:"history";s:7:"filters";a:4:{i:0;s:5:"today";i:1;s:4:"week";i:2;s:5:"month";i:3;s:7:"history";}s:11:"filterItems";a:4:{i:0;s:2:"20";i:1;s:2:"20";i:2;s:2:"20";i:3;s:2:"20";}}', '0', '1', NULL);
INSERT INTO pw_modehot VALUES(23, 0, 5, 'forumHot', '论坛版块排行', 'N;', 'N;', '1', '1', NULL);
INSERT INTO pw_modehot VALUES(24, 23, 1, 'forumPost', '今日发帖排行', 'N;', 'N;', '1', '1', NULL);
INSERT INTO pw_modehot VALUES(25, 23, 2, 'forumTopic', '主题排行', 'N;', 'N;', '0', '1', NULL);
INSERT INTO pw_modehot VALUES(26, 23, 3, 'forumArticle', '文章排行', 'N;', 'N;', '0', '1', NULL);

DROP TABLE IF EXISTS pw_ms_attachs;
CREATE TABLE pw_ms_attachs (
  id int(10) unsigned NOT NULL AUTO_INCREMENT,
  uid int(10) unsigned NOT NULL default '0',
  aid int(10) unsigned NOT NULL default '0',
  mid int(10) unsigned NOT NULL default '0',
  rid int(10) unsigned NOT NULL default '0',
  status tinyint(3) NOT NULL default '0',
  created_time int(10) NOT NULL default '0',
  PRIMARY KEY (id),
  KEY idx_mid (mid)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS pw_ms_configs;
CREATE TABLE pw_ms_configs (
  uid int(10) unsigned NOT NULL,
  blacklist text,
  blackcolony text,
  blackgroup text,
  categories text,
  statistics text,
  shieldinfo text,
  sms_num int(10) unsigned NOT NULL default 0,
  notice_num int(10) unsigned NOT NULL default 0,
  request_num int(10) unsigned NOT NULL default 0,
  groupsms_num int(10) unsigned NOT NULL default 0,
  field1 text,
  field2 text,
  num1 int(10) unsigned NOT NULL default 0,
  num2 int(10) unsigned NOT NULL default 0,
  PRIMARY KEY (uid)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS pw_ms_messages;
CREATE TABLE pw_ms_messages (
  mid int(10) unsigned NOT NULL AUTO_INCREMENT,
  create_uid int(10) unsigned NOT NULL default '0',
  create_username varchar(15) NOT NULL default '',
  title varchar(100) NOT NULL default '',
  content text,
  extra text,
  expand varchar(255) NOT NULL default '',
  attach tinyint(1) NOT NULL default '0',
  created_time int(10) NOT NULL default '0',
  modified_time int(10) NOT NULL default '0',
  PRIMARY KEY (mid)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS pw_ms_relations;
CREATE TABLE pw_ms_relations (
  rid int(10) unsigned NOT NULL AUTO_INCREMENT,
  uid int(10) unsigned NOT NULL default '0',
  mid int(10) unsigned NOT NULL default '0',
  categoryid smallint(3) unsigned NOT NULL default '0',
  typeid smallint(3) unsigned NOT NULL default '0',
  status tinyint(1) NOT NULL default '0',
  isown tinyint(1) NOT NULL default '0',
  created_time int(10) NOT NULL default '0',
  actived_time int(10) NOT NULL default '0',
  modified_time int(10) NOT NULL default '0',
  PRIMARY KEY (rid),
  KEY idx_uid_categoryid_typeid_modifiedtime (uid,categoryid,typeid,modified_time),
  KEY idx_uid_categoryid_modifiedtime (uid,categoryid,modified_time),
  KEY idx_uid_status_modifiedtime (uid,status,modified_time),
  KEY idx_uid_isown_modifiedtime (uid,isown,modified_time),
  KEY idx_mid (mid)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS pw_ms_replies;
CREATE TABLE pw_ms_replies (
  id int(10) unsigned NOT NULL AUTO_INCREMENT,
  parentid int(10) unsigned NOT NULL default '0',
  create_uid int(10) unsigned NOT NULL default '0',
  create_username varchar(15) NOT NULL default '',
  title varchar(100) NOT NULL default '',
  content text,
  status tinyint(1) NOT NULL default '0',
  created_time int(10) NOT NULL default '0',
  modified_time int(10) NOT NULL default '0',
  PRIMARY KEY (id),
  KEY idx_parentid_createdtime (parentid,created_time)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS pw_ms_searchs;
CREATE TABLE pw_ms_searchs (
  rid int(10) unsigned NOT NULL AUTO_INCREMENT,
  uid int(10) unsigned NOT NULL default '0',
  mid int(10) unsigned NOT NULL default '0',
  typeid smallint(3) unsigned NOT NULL default '0',
  create_uid int(10) unsigned NOT NULL default '0',
  created_time int(10) NOT NULL default '0',
  PRIMARY KEY (rid),
  KEY idx_uid_createuid_createdtime (uid,create_uid,created_time)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS pw_ms_tasks;
CREATE TABLE pw_ms_tasks (
  id int(10) unsigned NOT NULL AUTO_INCREMENT,
  oid int(10) unsigned NOT NULL default '0',
  mid int(10) unsigned NOT NULL default '0',
  created_time int(10) NOT NULL default '0',
  PRIMARY KEY (id),
  KEY idx_createdtime (created_time)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS pw_nav;
CREATE TABLE pw_nav (
  nid smallint(6) NOT NULL auto_increment,
  nkey varchar(32) NOT NULL default '',
  type varchar(32) NOT NULL default '',
  title char(50) NOT NULL default '',
  style char(50) NOT NULL default '',
  link char(100) NOT NULL default '',
  alt char(50) NOT NULL default '',
  pos varchar(255) NOT NULL,
  target tinyint(3) NOT NULL default '0',
  view smallint(6) NOT NULL default '0',
  upid smallint(6) NOT NULL default '0',
  isshow tinyint(3) NOT NULL default '0',
  floattype varchar(10) NOT NULL default '',
  listtype varchar(10) NOT NULL default '',
  selflisttype varchar(10) NOT NULL default '',
  PRIMARY KEY  (nid)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_oboard;
CREATE TABLE pw_oboard (
  id mediumint(8) unsigned NOT NULL auto_increment,
  uid int(10) unsigned NOT NULL default '0',
  username varchar(15) NOT NULL default '',
  touid int(10) unsigned NOT NULL default '0',
  title varchar(255) NOT NULL default '',
  postdate int(10) NOT NULL default '0',
  c_num mediumint(8) unsigned NOT NULL default '0',
  ifwordsfb tinyint(3) unsigned NOT NULL,
  PRIMARY KEY  (id),
  KEY idx_touid (touid)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_online;
CREATE TABLE pw_online (
  olid int(10) NOT NULL default '0',
  username varchar(15) NOT NULL default '',
  lastvisit int(10) NOT NULL default '0',
  ip varchar(30) NOT NULL default '',
  fid smallint(6) NOT NULL default '0',
  tid int(10) NOT NULL default '0',
  groupid tinyint(3) NOT NULL default '0',
  action varchar(16) NOT NULL default '',
  ifhide tinyint(1) NOT NULL default '0',
  uid int(10) NOT NULL default '0',
  rand smallint(6) not null default '0',
  PRIMARY KEY  (olid),
  KEY idx_uid (uid),
  KEY idx_ip (ip)
) TYPE=HEAP;

DROP TABLE IF EXISTS pw_ouserdata;
CREATE TABLE pw_ouserdata (
  uid int(10) unsigned NOT NULL,
  index_privacy tinyint(3) unsigned NOT NULL default '0',
  profile_privacy tinyint(3) unsigned NOT NULL default '0',
  info_privacy tinyint(3) unsigned NOT NULL default '0',
  credit_privacy tinyint(3) unsigned NOT NULL default '0',
  owrite_privacy tinyint(3) unsigned NOT NULL default '0',
  msgboard_privacy tinyint(3) unsigned NOT NULL default '0',
  photos_privacy tinyint(3) unsigned NOT NULL default '0',
  diary_privacy tinyint(3) unsigned NOT NULL default '0',
  article_isfeed tinyint(3) unsigned NOT NULL default '1',
  diary_isfeed tinyint(3) unsigned NOT NULL default '1',
  photos_isfeed tinyint(3) unsigned NOT NULL default '1',
  group_isfeed tinyint(3) unsigned default '1',
  visits int(10) unsigned NOT NULL default '0',
  tovisits int(10) unsigned NOT NULL default '0',
  tovisit varchar(255) NOT NULL default '',
  whovisit varchar(255) NOT NULL default '',
  diarynum int(10) unsigned NOT NULL default '0',
  photonum int(10) unsigned NOT NULL default '0',
  owritenum int(10) unsigned NOT NULL default '0',
  groupnum int(10) unsigned NOT NULL default '0',
  sharenum int(10) unsigned NOT NULL default '0',
  diary_lastpost int(10) unsigned NOT NULL default '0',
  photo_lastpost int(10) unsigned NOT NULL default '0',
  owrite_lastpost int(10) unsigned NOT NULL default '0',
  group_lastpost int(10) unsigned NOT NULL default '0',
  share_lastpost int(10) unsigned NOT NULL default '0', 
  self_isfollow tinyint(3) unsigned  NOT NULL default '1',
  friend_isfollow tinyint(3) unsigned NOT NULL default '1',
  cnlesp_isfollow tinyint(3) unsigned NOT NULL default '1',
  article_isfollow tinyint(3) unsigned NOT NULL default '1',
  diary_isfollow tinyint(3) unsigned NOT NULL default '1',
  photos_isfollow tinyint(3) unsigned NOT NULL default '1',
  group_isfollow tinyint(3) unsigned NOT NULL default '1',
  sinaweibo_isfollow TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
  PRIMARY KEY  (uid)
) TYPE=MyISAM;
INSERT INTO pw_ouserdata (uid, index_privacy, profile_privacy, info_privacy, credit_privacy, owrite_privacy, msgboard_privacy, photos_privacy, diary_privacy, article_isfeed, diary_isfeed, photos_isfeed, group_isfeed, visits, tovisits, tovisit, whovisit, diarynum, photonum, owritenum, groupnum, sharenum, diary_lastpost, photo_lastpost, owrite_lastpost, group_lastpost, share_lastpost) VALUES (1, 0, 0, 0, 0, 0, 0, 0, 0, 1, 1, 1, 1, 0, 0, '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);

DROP TABLE IF EXISTS pw_overprint;
CREATE TABLE pw_overprint (
  id int(10) unsigned NOT NULL AUTO_INCREMENT,
  title varchar(30) default '',
  icon varchar(255) default '',
  related tinyint(3) NOT NULL default '0',
  total smallint(6) unsigned NOT NULL default '0',
  createtime int(10) NOT NULL default '0',
  isopen tinyint(3) not null default '0',
  PRIMARY KEY (id)
) ENGINE=MyISAM;
INSERT INTO pw_overprint (id, title, icon, related, total, createtime, isopen) values('1','绿色置顶','d2.png','0','1','0','1');
INSERT INTO pw_overprint (id, title, icon, related, total, createtime, isopen) values('2','蓝色置顶','d1.png','0','1','0','1');
INSERT INTO pw_overprint (id, title, icon, related, total, createtime, isopen) values('3','红色置顶','d3.png','0','1','0','1');
INSERT INTO pw_overprint (id, title, icon, related, total, createtime, isopen) values('4','加亮','jl.png','0','1','0','1');
INSERT INTO pw_overprint (id, title, icon, related, total, createtime, isopen) values('5','推送','ts.png','0','1','0','1');
INSERT INTO pw_overprint (id, title, icon, related, total, createtime, isopen) values('6','提前','tq.png','0','1','0','1');
INSERT INTO pw_overprint (id, title, icon, related, total, createtime, isopen) values('7','推荐','tj.png','0','1','0','1');
INSERT INTO pw_overprint (id, title, icon, related, total, createtime, isopen) values('8','置顶','d3.png','-2','1','0','1');
INSERT INTO pw_overprint (id, title, icon, related, total, createtime, isopen) values('9','精华','jh.png','-1','1','0','1');
INSERT INTO pw_overprint (id, title, icon, related, total, createtime, isopen) values('10','锁帖','sd.png','0','1','0','0');

DROP TABLE IF EXISTS pw_owritedata;
CREATE TABLE pw_owritedata (
  id int(10) unsigned NOT NULL auto_increment,
  uid int(10) unsigned NOT NULL default '0',
  touid int(10) unsigned NOT NULL default '0',
  postdate int(10) unsigned NOT NULL default '0',
  isshare tinyint(3) unsigned NOT NULL default '0',
  source varchar(10) NOT NULL default '',
  content varchar(255) NOT NULL default '',
  c_num mediumint(8) UNSIGNED NOT NULL default '0',
  PRIMARY KEY  (id),
  KEY idx_uid (uid)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_pagecache;
CREATE TABLE pw_pagecache (
  `sign` char(32) NOT NULL,
  `type` varchar(30) NOT NULL default '',
  `data` text NOT NULL,
  `cachetime` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY (`sign`),
  KEY idx_type (`type`)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_pageinvoke;
CREATE TABLE pw_pageinvoke (
  id int(10) unsigned NOT NULL AUTO_INCREMENT,
  scr varchar(20) NOT NULL default '',
  sign varchar(20) NOT NULL default '',
  invokename varchar(50) NOT NULL default '',
  pieces varchar(255) NOT NULL default '',
  state tinyint(3) unsigned NOT NULL DEFAULT '0',
  ifverify tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (id),
  UNIQUE KEY idx_scr_sign_invokename (scr,sign,invokename),
  KEY idx_invokename (invokename)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS pw_pcfield;
CREATE TABLE pw_pcfield (
  fieldid smallint(6) unsigned NOT NULL auto_increment,
  name varchar(30) NOT NULL default '',
  fieldname varchar(30) NOT NULL default '',
  pcid smallint(6) unsigned NOT NULL default '0',
  vieworder tinyint(3) NOT NULL default '0',
  type varchar(20) NOT NULL default '',
  rules mediumtext NOT NULL,
  ifable tinyint(3) NOT NULL default '1',
  ifsearch tinyint(3) NOT NULL default '0',
  ifasearch tinyint(3) NOT NULL default '0',
  threadshow tinyint(3) NOT NULL default '0',
  ifmust tinyint(3) NOT NULL default '1',
  ifdel tinyint(3) NOT NULL default '0',
  textsize tinyint(3) NOT NULL default '0',
  descrip varchar(255) NOT NULL default '',
  PRIMARY KEY  (fieldid),
  KEY idx_pcid (pcid)
) TYPE=MyISAM ;

DROP TABLE IF EXISTS pw_pcmember;
CREATE TABLE pw_pcmember (
  pcmid mediumint(8) unsigned NOT NULL auto_increment,
  tid int(10) unsigned NOT NULL default '0',
  uid int(10) unsigned NOT NULL default '0',
  pcid tinyint(3) unsigned NOT NULL default '0',
  username varchar(15) NOT NULL default '',
  name varchar( 255 ) NOT NULL default '',
  zip varchar( 255 ) NOT NULL default '',
  message TEXT NOT NULL,
  nums tinyint(3) unsigned NOT NULL default '0',
  totalcash varchar(20) NOT NULL default '',
  phone varchar(15) NOT NULL default '',
  mobile varchar(15) NOT NULL default '',
  address varchar(255) NOT NULL default '',
  extra tinyint(3) NOT NULL default '0',
  jointime int(10) NOT NULL default 0,
  ifpay tinyint(3) NOT NULL default '0',
  PRIMARY KEY  (pcmid),
  KEY idx_tid_uid (tid,uid),
  KEY idx_uid (uid)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_pcvalue1;
CREATE TABLE pw_pcvalue1 (
  tid int(10) unsigned NOT NULL default '0',
  fid smallint(6) unsigned NOT NULL default  '0',
  ifrecycle tinyint(3) NOT NULL default 0,
  pctype tinyint(3) unsigned NOT NULL default '0',
  begintime int(10) unsigned NOT NULL default '0',
  endtime int(10) unsigned NOT NULL default '0',
  limitnum int(10) unsigned NOT NULL default '0',
  objecter tinyint(3) unsigned NOT NULL default '0',
  price varchar(255) NOT NULL default '',
  deposit varchar(255) NOT NULL default '',
  payway tinyint(3) unsigned NOT NULL default '0',
  contacter varchar(255) NOT NULL default '',
  tel varchar(255) NOT NULL default '',
  phone varchar(255) NOT NULL default '',
  mobile varchar(255) NOT NULL default '',
  pcattach varchar(255) NOT NULL default '',
  mprice varchar(255) NOT NULL default '',
  wangwang varchar(255) NOT NULL default '',
  qq varchar(255) NOT NULL default '',
  PRIMARY KEY (tid),
  KEY idx_fid (fid)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_permission;
CREATE TABLE pw_permission (
  uid int(10) unsigned NOT NULL,
  fid smallint(6) unsigned NOT NULL,
  gid smallint(6) unsigned NOT NULL,
  rkey varchar(20) NOT NULL default '',
  type enum('basic','special','system','systemforum') NOT NULL,
  rvalue text NOT NULL,
  PRIMARY KEY  (uid,fid,gid,rkey),
  KEY idx_rkey (rkey)
) TYPE=MyISAM;
INSERT INTO pw_permission VALUES('0','0','1','allowvisit','basic','1');
INSERT INTO pw_permission VALUES('0','0','1','maxmsg','basic','30');
INSERT INTO pw_permission VALUES('0','0','1','allowhide','basic','0');
INSERT INTO pw_permission VALUES('0','0','1','allowread','basic','1');
INSERT INTO pw_permission VALUES('0','0','1','allowportait','basic','1');
INSERT INTO pw_permission VALUES('0','0','1','upload','basic','1');
INSERT INTO pw_permission VALUES('0','0','1','allowrp','basic','1');
INSERT INTO pw_permission VALUES('0','0','1','allowhonor','basic','1');
INSERT INTO pw_permission VALUES('0','0','1','allowdelatc','basic','1');
INSERT INTO pw_permission VALUES('0','0','1','allowpost','basic','1');
INSERT INTO pw_permission VALUES('0','0','1','allownewvote','basic','1');
INSERT INTO pw_permission VALUES('0','0','1','allowvote','basic','1');
INSERT INTO pw_permission VALUES('0','0','1','allowactive','basic','1');
INSERT INTO pw_permission VALUES('0','0','1','htmlcode','basic','0');
INSERT INTO pw_permission VALUES('0','0','1','allowhidden','basic','1');
INSERT INTO pw_permission VALUES('0','0','1','allowencode','basic','1');
INSERT INTO pw_permission VALUES('0','0','1','allowsell','basic','1');
INSERT INTO pw_permission VALUES('0','0','1','allowsearch','basic','2');
INSERT INTO pw_permission VALUES('0','0','1','allowmember','basic','1');
INSERT INTO pw_permission VALUES('0','0','1','allowprofile','basic','1');
INSERT INTO pw_permission VALUES('0','0','1','allowreport','basic','1');
INSERT INTO pw_permission VALUES('0','0','1','allowmessege','basic','1');
INSERT INTO pw_permission VALUES('0','0','1','allowsort','basic','1');
INSERT INTO pw_permission VALUES('0','0','1','alloworder','basic','1');
INSERT INTO pw_permission VALUES('0','0','1','allowupload','basic','1');
INSERT INTO pw_permission VALUES('0','0','1','allowdownload','basic','1');
INSERT INTO pw_permission VALUES('0','0','1','allownum','basic','50');
INSERT INTO pw_permission VALUES('0','0','1','edittime','basic','0');
INSERT INTO pw_permission VALUES('0','0','1','postpertime','basic','3');
INSERT INTO pw_permission VALUES('0','0','1','searchtime','basic','10');
INSERT INTO pw_permission VALUES('0','0','1','signnum','basic','100');
INSERT INTO pw_permission VALUES('0','0','1','show','basic','1');
INSERT INTO pw_permission VALUES('0','0','1','viewipfrom','basic','0');
INSERT INTO pw_permission VALUES('0','0','1','imgwidth','basic','');
INSERT INTO pw_permission VALUES('0','0','1','imgheight','basic','');
INSERT INTO pw_permission VALUES('0','0','1','msggroup','basic','0');
INSERT INTO pw_permission VALUES('0','0','1','maxfavor','basic','100');
INSERT INTO pw_permission VALUES('0','0','1','viewvote','basic','0');
INSERT INTO pw_permission VALUES('0','0','1','atccheck','basic','1');
INSERT INTO pw_permission VALUES('0','0','1','markable','basic','1');
INSERT INTO pw_permission VALUES('0','0','1','postlimit','basic','');
INSERT INTO pw_permission VALUES('0','0','1','uploadtype','basic','');
INSERT INTO pw_permission VALUES('0','0','1','markdt','basic','');
INSERT INTO pw_permission VALUES('0','0','1','ifmemo','basic','1');
INSERT INTO pw_permission VALUES('0','0','1','atclog','basic','1');
INSERT INTO pw_permission VALUES('0','0','1','modifyvote','basic','1');
INSERT INTO pw_permission VALUES('0','0','1','allowreward','basic','1');
INSERT INTO pw_permission VALUES('0','0','1','allowgoods','basic','1');
INSERT INTO pw_permission VALUES('0','0','1','allowdebate','basic','1');
INSERT INTO pw_permission VALUES('0','0','1','anonymous','basic','0');
INSERT INTO pw_permission VALUES('0','0','1','dig','basic','1');
INSERT INTO pw_permission VALUES('0','0','1','leaveword','basic','1');
INSERT INTO pw_permission VALUES('0','0','1','schtime','basic','7776000');
INSERT INTO pw_permission VALUES('0','0','1','fontsize','basic','');
INSERT INTO pw_permission VALUES('0','0','1','maxsendmsg','basic','');
INSERT INTO pw_permission VALUES('0','0','1','pergroup','basic','member');
INSERT INTO pw_permission VALUES('0','0','1','maxgraft','basic','5');
INSERT INTO pw_permission VALUES('0','0','1','pwdlimitime','basic','');
INSERT INTO pw_permission VALUES('0','0','1','maxcstyles','basic','');
INSERT INTO pw_permission VALUES('0','0','1','media','basic','');
INSERT INTO pw_permission VALUES('0','0','1','markset','basic','a:5:{s:5:"money";a:4:{s:9:"markctype";s:5:"money";s:9:"maxcredit";s:1:"5";s:9:"marklimit";N;s:6:"markdt";s:1:"0";}s:4:"rvrc";a:3:{s:9:"maxcredit";s:0:"";s:9:"marklimit";N;s:6:"markdt";s:1:"0";}s:6:"credit";a:3:{s:9:"maxcredit";s:0:"";s:9:"marklimit";N;s:6:"markdt";s:1:"0";}s:8:"currency";a:3:{s:9:"maxcredit";s:0:"";s:9:"marklimit";N;s:6:"markdt";s:1:"0";}i:1;a:3:{s:9:"maxcredit";s:0:"";s:9:"marklimit";N;s:6:"markdt";s:1:"0";}}');
INSERT INTO pw_permission VALUES('0','0','2','allowvisit','basic','1');
INSERT INTO pw_permission VALUES('0','0','2','maxmsg','basic','0');
INSERT INTO pw_permission VALUES('0','0','2','allowhide','basic','0');
INSERT INTO pw_permission VALUES('0','0','2','allowread','basic','1');
INSERT INTO pw_permission VALUES('0','0','2','allowportait','basic','0');
INSERT INTO pw_permission VALUES('0','0','2','upload','basic','0');
INSERT INTO pw_permission VALUES('0','0','2','allowrp','basic','0');
INSERT INTO pw_permission VALUES('0','0','2','allowhonor','basic','0');
INSERT INTO pw_permission VALUES('0','0','2','allowdelatc','basic','0');
INSERT INTO pw_permission VALUES('0','0','2','allowpost','basic','0');
INSERT INTO pw_permission VALUES('0','0','2','allownewvote','basic','0');
INSERT INTO pw_permission VALUES('0','0','2','allowvote','basic','0');
INSERT INTO pw_permission VALUES('0','0','2','allowactive','basic','0');
INSERT INTO pw_permission VALUES('0','0','2','htmlcode','basic','0');
INSERT INTO pw_permission VALUES('0','0','2','allowhidden','basic','0');
INSERT INTO pw_permission VALUES('0','0','2','allowencode','basic','0');
INSERT INTO pw_permission VALUES('0','0','2','allowsell','basic','0');
INSERT INTO pw_permission VALUES('0','0','2','allowsearch','basic','0');
INSERT INTO pw_permission VALUES('0','0','2','allowmember','basic','0');
INSERT INTO pw_permission VALUES('0','0','2','allowprofile','basic','1');
INSERT INTO pw_permission VALUES('0','0','2','allowreport','basic','0');
INSERT INTO pw_permission VALUES('0','0','2','allowmessege','basic','0');
INSERT INTO pw_permission VALUES('0','0','2','allowsort','basic','0');
INSERT INTO pw_permission VALUES('0','0','2','alloworder','basic','0');
INSERT INTO pw_permission VALUES('0','0','2','allowupload','basic','0');
INSERT INTO pw_permission VALUES('0','0','2','allowdownload','basic','0');
INSERT INTO pw_permission VALUES('0','0','2','allownum','basic','0');
INSERT INTO pw_permission VALUES('0','0','2','edittime','basic','1');
INSERT INTO pw_permission VALUES('0','0','2','postpertime','basic','10');
INSERT INTO pw_permission VALUES('0','0','2','searchtime','basic','10');
INSERT INTO pw_permission VALUES('0','0','2','signnum','basic','0');
INSERT INTO pw_permission VALUES('0','0','2','show','basic','0');
INSERT INTO pw_permission VALUES('0','0','2','viewipfrom','basic','0');
INSERT INTO pw_permission VALUES('0','0','2','imgwidth','basic','');
INSERT INTO pw_permission VALUES('0','0','2','imgheight','basic','');
INSERT INTO pw_permission VALUES('0','0','2','fontsize','basic','');
INSERT INTO pw_permission VALUES('0','0','2','msggroup','basic','0');
INSERT INTO pw_permission VALUES('0','0','2','maxfavor','basic','100');
INSERT INTO pw_permission VALUES('0','0','2','viewvote','basic','0');
INSERT INTO pw_permission VALUES('0','0','2','atccheck','basic','1');
INSERT INTO pw_permission VALUES('0','0','2','markable','basic','0');
INSERT INTO pw_permission VALUES('0','0','2','postlimit','basic','');
INSERT INTO pw_permission VALUES('0','0','2','uploadtype','basic','');
INSERT INTO pw_permission VALUES('0','0','2','markdt','basic','');
INSERT INTO pw_permission VALUES('0','0','2','ifmemo','basic','1');
INSERT INTO pw_permission VALUES('0','0','2','atclog','basic','0');
INSERT INTO pw_permission VALUES('0','0','2','schtime','basic','7776000');
INSERT INTO pw_permission VALUES('0','0','2','modifyvote','basic','0');
INSERT INTO pw_permission VALUES('0','0','2','allowreward','basic','0');
INSERT INTO pw_permission VALUES('0','0','2','allowgoods','basic','0');
INSERT INTO pw_permission VALUES('0','0','2','allowdebate','basic','0');
INSERT INTO pw_permission VALUES('0','0','2','anonymous','basic','0');
INSERT INTO pw_permission VALUES('0','0','2','dig','basic','0');
INSERT INTO pw_permission VALUES('0','0','2','leaveword','basic','0');
INSERT INTO pw_permission VALUES('0','0','2','markset','basic','a:5:{s:5:"money";a:3:{s:9:"maxcredit";s:0:"";s:9:"marklimit";N;s:6:"markdt";s:1:"0";}s:4:"rvrc";a:3:{s:9:"maxcredit";s:0:"";s:9:"marklimit";N;s:6:"markdt";s:1:"0";}s:6:"credit";a:3:{s:9:"maxcredit";s:0:"";s:9:"marklimit";N;s:6:"markdt";s:1:"0";}s:8:"currency";a:3:{s:9:"maxcredit";s:0:"";s:9:"marklimit";N;s:6:"markdt";s:1:"0";}i:1;a:3:{s:9:"maxcredit";s:0:"";s:9:"marklimit";N;s:6:"markdt";s:1:"0";}}');
INSERT INTO pw_permission VALUES('0','0','3','allowvisit','basic','1');
INSERT INTO pw_permission VALUES('0','0','3','maxmsg','basic','50');
INSERT INTO pw_permission VALUES('0','0','3','allowhide','basic','0');
INSERT INTO pw_permission VALUES('0','0','3','allowread','basic','1');
INSERT INTO pw_permission VALUES('0','0','3','allowportait','basic','1');
INSERT INTO pw_permission VALUES('0','0','3','upload','basic','1');
INSERT INTO pw_permission VALUES('0','0','3','allowrp','basic','1');
INSERT INTO pw_permission VALUES('0','0','3','allowhonor','basic','1');
INSERT INTO pw_permission VALUES('0','0','3','allowdelatc','basic','1');
INSERT INTO pw_permission VALUES('0','0','3','allowpost','basic','1');
INSERT INTO pw_permission VALUES('0','0','3','allownewvote','basic','1');
INSERT INTO pw_permission VALUES('0','0','3','allowvote','basic','1');
INSERT INTO pw_permission VALUES('0','0','3','allowactive','basic','1');
INSERT INTO pw_permission VALUES('0','0','3','htmlcode','basic','1');
INSERT INTO pw_permission VALUES('0','0','3','allowhidden','basic','1');
INSERT INTO pw_permission VALUES('0','0','3','allowencode','basic','1');
INSERT INTO pw_permission VALUES('0','0','3','allowsell','basic','1');
INSERT INTO pw_permission VALUES('0','0','3','allowsearch','basic','2');
INSERT INTO pw_permission VALUES('0','0','3','allowmember','basic','1');
INSERT INTO pw_permission VALUES('0','0','3','allowprofile','basic','1');
INSERT INTO pw_permission VALUES('0','0','3','allowreport','basic','1');
INSERT INTO pw_permission VALUES('0','0','3','allowmessege','basic','1');
INSERT INTO pw_permission VALUES('0','0','3','allowsort','basic','1');
INSERT INTO pw_permission VALUES('0','0','3','alloworder','basic','1');
INSERT INTO pw_permission VALUES('0','0','3','allowupload','basic','1');
INSERT INTO pw_permission VALUES('0','0','3','allowdownload','basic','1');
INSERT INTO pw_permission VALUES('0','0','3','allownum','basic','200');
INSERT INTO pw_permission VALUES('0','0','3','edittime','basic','0');
INSERT INTO pw_permission VALUES('0','0','3','postpertime','basic','3');
INSERT INTO pw_permission VALUES('0','0','3','searchtime','basic','10');
INSERT INTO pw_permission VALUES('0','0','3','signnum','basic','200');
INSERT INTO pw_permission VALUES('0','0','3','show','basic','1');
INSERT INTO pw_permission VALUES('0','0','3','viewipfrom','basic','1');
INSERT INTO pw_permission VALUES('0','0','3','imgwidth','basic','');
INSERT INTO pw_permission VALUES('0','0','3','imgheight','basic','');
INSERT INTO pw_permission VALUES('0','0','3','fontsize','basic','');
INSERT INTO pw_permission VALUES('0','0','3','msggroup','basic','1');
INSERT INTO pw_permission VALUES('0','0','3','maxfavor','basic','100');
INSERT INTO pw_permission VALUES('0','0','3','viewvote','basic','1');
INSERT INTO pw_permission VALUES('0','0','3','atccheck','basic','1');
INSERT INTO pw_permission VALUES('0','0','3','markable','basic','2');
INSERT INTO pw_permission VALUES('0','0','3','postlimit','basic','');
INSERT INTO pw_permission VALUES('0','0','3','uploadtype','basic','');
INSERT INTO pw_permission VALUES('0','0','3','markdt','basic','');
INSERT INTO pw_permission VALUES('0','0','3','anonymous','basic','0');
INSERT INTO pw_permission VALUES('0','0','3','leaveword','basic','1');
INSERT INTO pw_permission VALUES('0','0','3','allowadmincp','system','1');
INSERT INTO pw_permission VALUES('0','0','3','delatc','systemforum','1');
INSERT INTO pw_permission VALUES('0','0','3','moveatc','systemforum','1');
INSERT INTO pw_permission VALUES('0','0','3','copyatc','systemforum','1');
INSERT INTO pw_permission VALUES('0','0','3','digestadmin','systemforum','1');
INSERT INTO pw_permission VALUES('0','0','3','lockadmin','systemforum','1');
INSERT INTO pw_permission VALUES('0','0','3','pushadmin','systemforum','1');
INSERT INTO pw_permission VALUES('0','0','3','coloradmin','systemforum','1');
INSERT INTO pw_permission VALUES('0','0','3','downadmin','systemforum','1');
INSERT INTO pw_permission VALUES('0','0','3','viewcheck','systemforum','1');
INSERT INTO pw_permission VALUES('0','0','3','viewclose','systemforum','1');
INSERT INTO pw_permission VALUES('0','0','3','delattach','systemforum','1');
INSERT INTO pw_permission VALUES('0','0','3','viewip','systemforum','1');
INSERT INTO pw_permission VALUES('0','0','3','banuser','systemforum','2');
INSERT INTO pw_permission VALUES('0','0','3','bantype','systemforum','1');
INSERT INTO pw_permission VALUES('0','0','3','banmax','systemforum','30');
INSERT INTO pw_permission VALUES('0','0','3','posthide','systemforum','1');
INSERT INTO pw_permission VALUES('0','0','3','sellhide','systemforum','1');
INSERT INTO pw_permission VALUES('0','0','3','encodehide','systemforum','1');
INSERT INTO pw_permission VALUES('0','0','3','anonyhide','systemforum','1');
INSERT INTO pw_permission VALUES('0','0','3','postpers','systemforum','1');
INSERT INTO pw_permission VALUES('0','0','3','replylock','systemforum','1');
INSERT INTO pw_permission VALUES('0','0','3','modother','systemforum','1');
INSERT INTO pw_permission VALUES('0','0','3','deltpcs','systemforum','1');
INSERT INTO pw_permission VALUES('0','0','3','topped','systemforum','4');
INSERT INTO pw_permission VALUES('0','0','3','tpctype','systemforum','1');
INSERT INTO pw_permission VALUES('0','0','3','tpccheck','systemforum','1');
INSERT INTO pw_permission VALUES('0','0','3','allowtime','systemforum','1');
INSERT INTO pw_permission VALUES('0','0','3','superright','system','1');
INSERT INTO pw_permission VALUES('0','0','3','ifmemo','basic','1');
INSERT INTO pw_permission VALUES('0','0','3','markset','basic','a:5:{s:5:"money";a:4:{s:9:"markctype";s:5:"money";s:9:"maxcredit";s:3:"100";s:9:"marklimit";N;s:6:"markdt";s:1:"0";}s:4:"rvrc";a:3:{s:9:"maxcredit";s:0:"";s:9:"marklimit";N;s:6:"markdt";s:1:"0";}s:6:"credit";a:3:{s:9:"maxcredit";s:0:"";s:9:"marklimit";N;s:6:"markdt";s:1:"0";}s:8:"currency";a:3:{s:9:"maxcredit";s:0:"";s:9:"marklimit";N;s:6:"markdt";s:1:"0";}i:1;a:3:{s:9:"maxcredit";s:0:"";s:9:"marklimit";N;s:6:"markdt";s:1:"0";}}');
INSERT INTO pw_permission VALUES('0','0','3','atclog','basic','1');
INSERT INTO pw_permission VALUES('0','0','3','schtime','basic','7776000');
INSERT INTO pw_permission VALUES('0','0','3','maxsendmsg','basic','');
INSERT INTO pw_permission VALUES('0','0','3','pergroup','basic','member,system,special');
INSERT INTO pw_permission VALUES('0','0','3','maxgraft','basic','10');
INSERT INTO pw_permission VALUES('0','0','3','pwdlimitime','basic','');
INSERT INTO pw_permission VALUES('0','0','3','maxcstyles','basic','');
INSERT INTO pw_permission VALUES('0','0','3','modifyvote','basic','1');
INSERT INTO pw_permission VALUES('0','0','3','allowreward','basic','1');
INSERT INTO pw_permission VALUES('0','0','3','allowgoods','basic','1');
INSERT INTO pw_permission VALUES('0','0','3','allowdebate','basic','1');
INSERT INTO pw_permission VALUES('0','0','3','media','basic','');
INSERT INTO pw_permission VALUES('0','0','3','dig','basic','1');
INSERT INTO pw_permission VALUES('0','0','3','shield','systemforum','1');
INSERT INTO pw_permission VALUES('0','0','3','unite','systemforum','1');
INSERT INTO pw_permission VALUES('0','0','3','remind','systemforum','1');
INSERT INTO pw_permission VALUES('0','0','3','pingcp','systemforum','1');
INSERT INTO pw_permission VALUES('0','0','3','inspect','systemforum','1');
INSERT INTO pw_permission VALUES('0','0','3','allowpcid','basic','1,2');
INSERT INTO pw_permission VALUES('0','0','3','allowmodelid','basic','1');
INSERT INTO pw_permission VALUES('0','0','3','systemforum','basic','1');
INSERT INTO pw_permission VALUES('0','0','3','areapush','systemforum','1');
INSERT INTO pw_permission VALUES('0','0','4','allowvisit','basic','1');
INSERT INTO pw_permission VALUES('0','0','4','areapush','systemforum','1');
INSERT INTO pw_permission VALUES('0','0','4','maxmsg','basic','50');
INSERT INTO pw_permission VALUES('0','0','4','allowhide','basic','0');
INSERT INTO pw_permission VALUES('0','0','4','allowread','basic','1');
INSERT INTO pw_permission VALUES('0','0','4','allowportait','basic','1');
INSERT INTO pw_permission VALUES('0','0','4','upload','basic','1');
INSERT INTO pw_permission VALUES('0','0','4','allowrp','basic','1');
INSERT INTO pw_permission VALUES('0','0','4','allowhonor','basic','1');
INSERT INTO pw_permission VALUES('0','0','4','allowdelatc','basic','1');
INSERT INTO pw_permission VALUES('0','0','4','allowpost','basic','1');
INSERT INTO pw_permission VALUES('0','0','4','allownewvote','basic','1');
INSERT INTO pw_permission VALUES('0','0','4','allowvote','basic','1');
INSERT INTO pw_permission VALUES('0','0','4','allowactive','basic','1');
INSERT INTO pw_permission VALUES('0','0','4','htmlcode','basic','0');
INSERT INTO pw_permission VALUES('0','0','4','allowhidden','basic','1');
INSERT INTO pw_permission VALUES('0','0','4','allowencode','basic','1');
INSERT INTO pw_permission VALUES('0','0','4','allowsell','basic','1');
INSERT INTO pw_permission VALUES('0','0','4','allowsearch','basic','2');
INSERT INTO pw_permission VALUES('0','0','4','allowmember','basic','1');
INSERT INTO pw_permission VALUES('0','0','4','allowprofile','basic','1');
INSERT INTO pw_permission VALUES('0','0','4','allowreport','basic','1');
INSERT INTO pw_permission VALUES('0','0','4','allowmessege','basic','1');
INSERT INTO pw_permission VALUES('0','0','4','allowsort','basic','1');
INSERT INTO pw_permission VALUES('0','0','4','alloworder','basic','1');
INSERT INTO pw_permission VALUES('0','0','4','allowupload','basic','1');
INSERT INTO pw_permission VALUES('0','0','4','allowdownload','basic','1');
INSERT INTO pw_permission VALUES('0','0','4','allownum','basic','80');
INSERT INTO pw_permission VALUES('0','0','4','edittime','basic','0');
INSERT INTO pw_permission VALUES('0','0','4','postpertime','basic','3');
INSERT INTO pw_permission VALUES('0','0','4','searchtime','basic','10');
INSERT INTO pw_permission VALUES('0','0','4','signnum','basic','200');
INSERT INTO pw_permission VALUES('0','0','4','show','basic','1');
INSERT INTO pw_permission VALUES('0','0','4','viewipfrom','basic','1');
INSERT INTO pw_permission VALUES('0','0','4','imgwidth','basic','');
INSERT INTO pw_permission VALUES('0','0','4','imgheight','basic','');
INSERT INTO pw_permission VALUES('0','0','4','fontsize','basic','');
INSERT INTO pw_permission VALUES('0','0','4','msggroup','basic','1');
INSERT INTO pw_permission VALUES('0','0','4','maxfavor','basic','100');
INSERT INTO pw_permission VALUES('0','0','4','viewvote','basic','1');
INSERT INTO pw_permission VALUES('0','0','4','atccheck','basic','1');
INSERT INTO pw_permission VALUES('0','0','4','markable','basic','2');
INSERT INTO pw_permission VALUES('0','0','4','postlimit','basic','');
INSERT INTO pw_permission VALUES('0','0','4','uploadtype','basic','');
INSERT INTO pw_permission VALUES('0','0','4','markdt','basic','');
INSERT INTO pw_permission VALUES('0','0','4','anonymous','basic','0');
INSERT INTO pw_permission VALUES('0','0','4','leaveword','basic','1');
INSERT INTO pw_permission VALUES('0','0','4','allowadmincp','system','1');
INSERT INTO pw_permission VALUES('0','0','4','delatc','systemforum','1');
INSERT INTO pw_permission VALUES('0','0','4','moveatc','systemforum','1');
INSERT INTO pw_permission VALUES('0','0','4','copyatc','systemforum','1');
INSERT INTO pw_permission VALUES('0','0','4','digestadmin','systemforum','1');
INSERT INTO pw_permission VALUES('0','0','4','lockadmin','systemforum','1');
INSERT INTO pw_permission VALUES('0','0','4','pushadmin','systemforum','1');
INSERT INTO pw_permission VALUES('0','0','4','coloradmin','systemforum','1');
INSERT INTO pw_permission VALUES('0','0','4','downadmin','systemforum','1');
INSERT INTO pw_permission VALUES('0','0','4','viewcheck','systemforum','1');
INSERT INTO pw_permission VALUES('0','0','4','viewclose','systemforum','1');
INSERT INTO pw_permission VALUES('0','0','4','delattach','systemforum','1');
INSERT INTO pw_permission VALUES('0','0','4','viewip','systemforum','1');
INSERT INTO pw_permission VALUES('0','0','4','banuser','systemforum','2');
INSERT INTO pw_permission VALUES('0','0','4','bantype','systemforum','1');
INSERT INTO pw_permission VALUES('0','0','4','banmax','systemforum','20');
INSERT INTO pw_permission VALUES('0','0','4','posthide','systemforum','1');
INSERT INTO pw_permission VALUES('0','0','4','sellhide','systemforum','1');
INSERT INTO pw_permission VALUES('0','0','4','encodehide','systemforum','1');
INSERT INTO pw_permission VALUES('0','0','4','anonyhide','systemforum','1');
INSERT INTO pw_permission VALUES('0','0','4','postpers','systemforum','1');
INSERT INTO pw_permission VALUES('0','0','4','replylock','systemforum','1');
INSERT INTO pw_permission VALUES('0','0','4','modother','systemforum','1');
INSERT INTO pw_permission VALUES('0','0','4','deltpcs','systemforum','1');
INSERT INTO pw_permission VALUES('0','0','4','topped','systemforum','4');
INSERT INTO pw_permission VALUES('0','0','4','tpctype','systemforum','1');
INSERT INTO pw_permission VALUES('0','0','4','tpccheck','systemforum','1');
INSERT INTO pw_permission VALUES('0','0','4','allowtime','systemforum','1');
INSERT INTO pw_permission VALUES('0','0','4','superright','system','1');
INSERT INTO pw_permission VALUES('0','0','4','ifmemo','basic','1');
INSERT INTO pw_permission VALUES('0','0','4','markset','basic','a:5:{s:5:"money";a:4:{s:9:"markctype";s:5:"money";s:9:"maxcredit";s:2:"80";s:9:"marklimit";N;s:6:"markdt";s:1:"0";}s:4:"rvrc";a:3:{s:9:"maxcredit";s:0:"";s:9:"marklimit";N;s:6:"markdt";s:1:"0";}s:6:"credit";a:3:{s:9:"maxcredit";s:0:"";s:9:"marklimit";N;s:6:"markdt";s:1:"0";}s:8:"currency";a:3:{s:9:"maxcredit";s:0:"";s:9:"marklimit";N;s:6:"markdt";s:1:"0";}i:1;a:3:{s:9:"maxcredit";s:0:"";s:9:"marklimit";N;s:6:"markdt";s:1:"0";}}');
INSERT INTO pw_permission VALUES('0','0','4','atclog','basic','1');
INSERT INTO pw_permission VALUES('0','0','4','schtime','basic','7776000');
INSERT INTO pw_permission VALUES('0','0','4','maxsendmsg','basic','');
INSERT INTO pw_permission VALUES('0','0','4','pergroup','basic','member,system');
INSERT INTO pw_permission VALUES('0','0','4','maxgraft','basic','10');
INSERT INTO pw_permission VALUES('0','0','4','pwdlimitime','basic','');
INSERT INTO pw_permission VALUES('0','0','4','maxcstyles','basic','');
INSERT INTO pw_permission VALUES('0','0','4','modifyvote','basic','1');
INSERT INTO pw_permission VALUES('0','0','4','allowreward','basic','1');
INSERT INTO pw_permission VALUES('0','0','4','allowgoods','basic','1');
INSERT INTO pw_permission VALUES('0','0','4','allowdebate','basic','1');
INSERT INTO pw_permission VALUES('0','0','4','media','basic','');
INSERT INTO pw_permission VALUES('0','0','4','dig','basic','1');
INSERT INTO pw_permission VALUES('0','0','4','shield','systemforum','1');
INSERT INTO pw_permission VALUES('0','0','4','unite','systemforum','1');
INSERT INTO pw_permission VALUES('0','0','4','remind','systemforum','1');
INSERT INTO pw_permission VALUES('0','0','4','pingcp','systemforum','1');
INSERT INTO pw_permission VALUES('0','0','4','inspect','systemforum','1');
INSERT INTO pw_permission VALUES('0','0','4','allowpcid','basic','1,2');
INSERT INTO pw_permission VALUES('0','0','4','allowmodelid','basic','1');
INSERT INTO pw_permission VALUES('0','0','4','systemforum','basic','1');
INSERT INTO pw_permission VALUES('0','0','5','allowvisit','basic','1');
INSERT INTO pw_permission VALUES('0','0','5','maxmsg','basic','50');
INSERT INTO pw_permission VALUES('0','0','5','allowhide','basic','0');
INSERT INTO pw_permission VALUES('0','0','5','allowread','basic','1');
INSERT INTO pw_permission VALUES('0','0','5','allowportait','basic','1');
INSERT INTO pw_permission VALUES('0','0','5','upload','basic','1');
INSERT INTO pw_permission VALUES('0','0','5','allowrp','basic','1');
INSERT INTO pw_permission VALUES('0','0','5','allowhonor','basic','1');
INSERT INTO pw_permission VALUES('0','0','5','allowdelatc','basic','1');
INSERT INTO pw_permission VALUES('0','0','5','allowpost','basic','1');
INSERT INTO pw_permission VALUES('0','0','5','allownewvote','basic','1');
INSERT INTO pw_permission VALUES('0','0','5','allowvote','basic','1');
INSERT INTO pw_permission VALUES('0','0','5','allowactive','basic','1');
INSERT INTO pw_permission VALUES('0','0','5','htmlcode','basic','0');
INSERT INTO pw_permission VALUES('0','0','5','allowhidden','basic','1');
INSERT INTO pw_permission VALUES('0','0','5','allowencode','basic','1');
INSERT INTO pw_permission VALUES('0','0','5','allowsell','basic','1');
INSERT INTO pw_permission VALUES('0','0','5','allowsearch','basic','2');
INSERT INTO pw_permission VALUES('0','0','5','allowmember','basic','1');
INSERT INTO pw_permission VALUES('0','0','5','allowprofile','basic','1');
INSERT INTO pw_permission VALUES('0','0','5','allowreport','basic','1');
INSERT INTO pw_permission VALUES('0','0','5','allowmessege','basic','1');
INSERT INTO pw_permission VALUES('0','0','5','allowsort','basic','1');
INSERT INTO pw_permission VALUES('0','0','5','alloworder','basic','1');
INSERT INTO pw_permission VALUES('0','0','5','allowupload','basic','1');
INSERT INTO pw_permission VALUES('0','0','5','allowdownload','basic','1');
INSERT INTO pw_permission VALUES('0','0','5','allownum','basic','80');
INSERT INTO pw_permission VALUES('0','0','5','edittime','basic','0');
INSERT INTO pw_permission VALUES('0','0','5','postpertime','basic','3');
INSERT INTO pw_permission VALUES('0','0','5','searchtime','basic','10');
INSERT INTO pw_permission VALUES('0','0','5','signnum','basic','200');
INSERT INTO pw_permission VALUES('0','0','5','show','basic','1');
INSERT INTO pw_permission VALUES('0','0','5','viewipfrom','basic','1');
INSERT INTO pw_permission VALUES('0','0','5','imgwidth','basic','');
INSERT INTO pw_permission VALUES('0','0','5','imgheight','basic','');
INSERT INTO pw_permission VALUES('0','0','5','fontsize','basic','');
INSERT INTO pw_permission VALUES('0','0','5','msggroup','basic','1');
INSERT INTO pw_permission VALUES('0','0','5','maxfavor','basic','100');
INSERT INTO pw_permission VALUES('0','0','5','viewvote','basic','1');
INSERT INTO pw_permission VALUES('0','0','5','atccheck','basic','1');
INSERT INTO pw_permission VALUES('0','0','5','markable','basic','2');
INSERT INTO pw_permission VALUES('0','0','5','postlimit','basic','');
INSERT INTO pw_permission VALUES('0','0','5','uploadtype','basic','');
INSERT INTO pw_permission VALUES('0','0','5','markdt','basic','');
INSERT INTO pw_permission VALUES('0','0','5','anonymous','basic','0');
INSERT INTO pw_permission VALUES('0','0','5','leaveword','basic','1');
INSERT INTO pw_permission VALUES('0','0','5','allowadmincp','system','1');
INSERT INTO pw_permission VALUES('0','0','5','delatc','systemforum','1');
INSERT INTO pw_permission VALUES('0','0','5','moveatc','systemforum','1');
INSERT INTO pw_permission VALUES('0','0','5','copyatc','systemforum','1');
INSERT INTO pw_permission VALUES('0','0','5','digestadmin','systemforum','1');
INSERT INTO pw_permission VALUES('0','0','5','lockadmin','systemforum','1');
INSERT INTO pw_permission VALUES('0','0','5','pushadmin','systemforum','1');
INSERT INTO pw_permission VALUES('0','0','5','coloradmin','systemforum','1');
INSERT INTO pw_permission VALUES('0','0','5','downadmin','systemforum','1');
INSERT INTO pw_permission VALUES('0','0','5','viewcheck','systemforum','1');
INSERT INTO pw_permission VALUES('0','0','5','viewclose','systemforum','1');
INSERT INTO pw_permission VALUES('0','0','5','delattach','systemforum','1');
INSERT INTO pw_permission VALUES('0','0','5','viewip','systemforum','1');
INSERT INTO pw_permission VALUES('0','0','5','banuser','systemforum','1');
INSERT INTO pw_permission VALUES('0','0','5','bantype','systemforum','0');
INSERT INTO pw_permission VALUES('0','0','5','banmax','systemforum','10');
INSERT INTO pw_permission VALUES('0','0','5','posthide','systemforum','0');
INSERT INTO pw_permission VALUES('0','0','5','sellhide','systemforum','0');
INSERT INTO pw_permission VALUES('0','0','5','encodehide','systemforum','0');
INSERT INTO pw_permission VALUES('0','0','5','anonyhide','systemforum','0');
INSERT INTO pw_permission VALUES('0','0','5','postpers','systemforum','0');
INSERT INTO pw_permission VALUES('0','0','5','replylock','systemforum','1');
INSERT INTO pw_permission VALUES('0','0','5','modother','systemforum','1');
INSERT INTO pw_permission VALUES('0','0','5','remind','systemforum','1');
INSERT INTO pw_permission VALUES('0','0','5','shield','systemforum','1');
INSERT INTO pw_permission VALUES('0','0','5','topped','systemforum','1');
INSERT INTO pw_permission VALUES('0','0','5','tpccheck','systemforum','1');
INSERT INTO pw_permission VALUES('0','0','5','tpctype','systemforum','1');
INSERT INTO pw_permission VALUES('0','0','5','unite','systemforum','1');
INSERT INTO pw_permission VALUES('0','0','5','deltpcs','systemforum','1');
INSERT INTO pw_permission VALUES('0','0','5','allowtime','systemforum','1');
INSERT INTO pw_permission VALUES('0','0','5','superright','system','0');
INSERT INTO pw_permission VALUES('0','0','5','ifmemo','basic','1');
INSERT INTO pw_permission VALUES('0','0','5','markset','basic','a:5:{s:5:"money";a:4:{s:9:"markctype";s:5:"money";s:9:"maxcredit";s:2:"80";s:9:"marklimit";N;s:6:"markdt";s:1:"0";}s:4:"rvrc";a:3:{s:9:"maxcredit";s:0:"";s:9:"marklimit";N;s:6:"markdt";s:1:"0";}s:6:"credit";a:3:{s:9:"maxcredit";s:0:"";s:9:"marklimit";N;s:6:"markdt";s:1:"0";}s:8:"currency";a:3:{s:9:"maxcredit";s:0:"";s:9:"marklimit";N;s:6:"markdt";s:1:"0";}i:1;a:3:{s:9:"maxcredit";s:0:"";s:9:"marklimit";N;s:6:"markdt";s:1:"0";}}');
INSERT INTO pw_permission VALUES('0','0','5','atclog','basic','1');
INSERT INTO pw_permission VALUES('0','0','5','schtime','basic','7776000');
INSERT INTO pw_permission VALUES('0','0','5','modifyvote','basic','1');
INSERT INTO pw_permission VALUES('0','0','5','allowreward','basic','1');
INSERT INTO pw_permission VALUES('0','0','5','allowgoods','basic','1');
INSERT INTO pw_permission VALUES('0','0','5','allowdebate','basic','1');
INSERT INTO pw_permission VALUES('0','0','5','dig','basic','1');
INSERT INTO pw_permission VALUES('0','0','5','pingcp','systemforum','1');
INSERT INTO pw_permission VALUES('0','0','5','inspect','systemforum','1');
INSERT INTO pw_permission VALUES('0','0','5','maxsendmsg','basic','');
INSERT INTO pw_permission VALUES('0','0','5','pergroup','basic','member,system,special');
INSERT INTO pw_permission VALUES('0','0','5','maxgraft','basic','10');
INSERT INTO pw_permission VALUES('0','0','5','pwdlimitime','basic','');
INSERT INTO pw_permission VALUES('0','0','5','maxcstyles','basic','');
INSERT INTO pw_permission VALUES('0','0','5','media','basic','');
INSERT INTO pw_permission VALUES('0','0','5','allowpcid','basic','1,2');
INSERT INTO pw_permission VALUES('0','0','5','allowmodelid','basic','1');
INSERT INTO pw_permission VALUES('0','0','5','systemforum','basic','1');
INSERT INTO pw_permission VALUES('0','0','6','allowvisit','basic','1');
INSERT INTO pw_permission VALUES('0','0','6','maxmsg','basic','10');
INSERT INTO pw_permission VALUES('0','0','6','allowhide','basic','0');
INSERT INTO pw_permission VALUES('0','0','6','allowread','basic','1');
INSERT INTO pw_permission VALUES('0','0','6','allowportait','basic','0');
INSERT INTO pw_permission VALUES('0','0','6','upload','basic','0');
INSERT INTO pw_permission VALUES('0','0','6','allowrp','basic','0');
INSERT INTO pw_permission VALUES('0','0','6','allowhonor','basic','0');
INSERT INTO pw_permission VALUES('0','0','6','allowdelatc','basic','0');
INSERT INTO pw_permission VALUES('0','0','6','allowpost','basic','0');
INSERT INTO pw_permission VALUES('0','0','6','allownewvote','basic','0');
INSERT INTO pw_permission VALUES('0','0','6','allowvote','basic','0');
INSERT INTO pw_permission VALUES('0','0','6','allowactive','basic','0');
INSERT INTO pw_permission VALUES('0','0','6','htmlcode','basic','0');
INSERT INTO pw_permission VALUES('0','0','6','allowhidden','basic','0');
INSERT INTO pw_permission VALUES('0','0','6','allowencode','basic','0');
INSERT INTO pw_permission VALUES('0','0','6','allowsell','basic','0');
INSERT INTO pw_permission VALUES('0','0','6','allowsearch','basic','0');
INSERT INTO pw_permission VALUES('0','0','6','allowmember','basic','0');
INSERT INTO pw_permission VALUES('0','0','6','allowprofile','basic','1');
INSERT INTO pw_permission VALUES('0','0','6','allowreport','basic','0');
INSERT INTO pw_permission VALUES('0','0','6','allowmessege','basic','0');
INSERT INTO pw_permission VALUES('0','0','6','allowsort','basic','0');
INSERT INTO pw_permission VALUES('0','0','6','alloworder','basic','0');
INSERT INTO pw_permission VALUES('0','0','6','allowupload','basic','0');
INSERT INTO pw_permission VALUES('0','0','6','allowdownload','basic','0');
INSERT INTO pw_permission VALUES('0','0','6','allownum','basic','50');
INSERT INTO pw_permission VALUES('0','0','6','edittime','basic','0');
INSERT INTO pw_permission VALUES('0','0','6','postpertime','basic','15');
INSERT INTO pw_permission VALUES('0','0','6','searchtime','basic','10');
INSERT INTO pw_permission VALUES('0','0','6','signnum','basic','100');
INSERT INTO pw_permission VALUES('0','0','6','show','basic','0');
INSERT INTO pw_permission VALUES('0','0','6','viewipfrom','basic','0');
INSERT INTO pw_permission VALUES('0','0','6','imgwidth','basic','');
INSERT INTO pw_permission VALUES('0','0','6','imgheight','basic','');
INSERT INTO pw_permission VALUES('0','0','6','fontsize','basic','');
INSERT INTO pw_permission VALUES('0','0','6','msggroup','basic','0');
INSERT INTO pw_permission VALUES('0','0','6','maxfavor','basic','100');
INSERT INTO pw_permission VALUES('0','0','6','viewvote','basic','0');
INSERT INTO pw_permission VALUES('0','0','6','atccheck','basic','1');
INSERT INTO pw_permission VALUES('0','0','6','markable','basic','0');
INSERT INTO pw_permission VALUES('0','0','6','postlimit','basic','');
INSERT INTO pw_permission VALUES('0','0','6','uploadtype','basic','');
INSERT INTO pw_permission VALUES('0','0','6','markdt','basic','');
INSERT INTO pw_permission VALUES('0','0','6','ifmemo','basic','0');
INSERT INTO pw_permission VALUES('0','0','6','atclog','basic','0');
INSERT INTO pw_permission VALUES('0','0','6','schtime','basic','7776000');
INSERT INTO pw_permission VALUES('0','0','6','modifyvote','basic','0');
INSERT INTO pw_permission VALUES('0','0','6','allowreward','basic','0');
INSERT INTO pw_permission VALUES('0','0','6','allowgoods','basic','0');
INSERT INTO pw_permission VALUES('0','0','6','allowdebate','basic','0');
INSERT INTO pw_permission VALUES('0','0','6','anonymous','basic','0');
INSERT INTO pw_permission VALUES('0','0','6','dig','basic','0');
INSERT INTO pw_permission VALUES('0','0','6','leaveword','basic','0');
INSERT INTO pw_permission VALUES('0','0','6','markset','basic','a:5:{s:5:"money";a:3:{s:9:"maxcredit";s:0:"";s:9:"marklimit";N;s:6:"markdt";s:1:"0";}s:4:"rvrc";a:3:{s:9:"maxcredit";s:0:"";s:9:"marklimit";N;s:6:"markdt";s:1:"0";}s:6:"credit";a:3:{s:9:"maxcredit";s:0:"";s:9:"marklimit";N;s:6:"markdt";s:1:"0";}s:8:"currency";a:3:{s:9:"maxcredit";s:0:"";s:9:"marklimit";N;s:6:"markdt";s:1:"0";}i:1;a:3:{s:9:"maxcredit";s:0:"";s:9:"marklimit";N;s:6:"markdt";s:1:"0";}}');
INSERT INTO pw_permission VALUES('0','0','7','allowvisit','basic','1');
INSERT INTO pw_permission VALUES('0','0','7','maxmsg','basic','10');
INSERT INTO pw_permission VALUES('0','0','7','allowhide','basic','0');
INSERT INTO pw_permission VALUES('0','0','7','allowread','basic','1');
INSERT INTO pw_permission VALUES('0','0','7','allowportait','basic','1');
INSERT INTO pw_permission VALUES('0','0','7','upload','basic','0');
INSERT INTO pw_permission VALUES('0','0','7','allowrp','basic','1');
INSERT INTO pw_permission VALUES('0','0','7','allowhonor','basic','0');
INSERT INTO pw_permission VALUES('0','0','7','allowdelatc','basic','1');
INSERT INTO pw_permission VALUES('0','0','7','allowpost','basic','1');
INSERT INTO pw_permission VALUES('0','0','7','allownewvote','basic','1');
INSERT INTO pw_permission VALUES('0','0','7','allowvote','basic','1');
INSERT INTO pw_permission VALUES('0','0','7','allowactive','basic','0');
INSERT INTO pw_permission VALUES('0','0','7','htmlcode','basic','0');
INSERT INTO pw_permission VALUES('0','0','7','allowhidden','basic','1');
INSERT INTO pw_permission VALUES('0','0','7','allowencode','basic','1');
INSERT INTO pw_permission VALUES('0','0','7','allowsell','basic','1');
INSERT INTO pw_permission VALUES('0','0','7','allowsearch','basic','0');
INSERT INTO pw_permission VALUES('0','0','7','allowmember','basic','1');
INSERT INTO pw_permission VALUES('0','0','7','allowprofile','basic','1');
INSERT INTO pw_permission VALUES('0','0','7','allowreport','basic','0');
INSERT INTO pw_permission VALUES('0','0','7','allowmessege','basic','0');
INSERT INTO pw_permission VALUES('0','0','7','allowsort','basic','0');
INSERT INTO pw_permission VALUES('0','0','7','alloworder','basic','1');
INSERT INTO pw_permission VALUES('0','0','7','allowupload','basic','1');
INSERT INTO pw_permission VALUES('0','0','7','allowdownload','basic','1');
INSERT INTO pw_permission VALUES('0','0','7','allownum','basic','50');
INSERT INTO pw_permission VALUES('0','0','7','edittime','basic','0');
INSERT INTO pw_permission VALUES('0','0','7','postpertime','basic','15');
INSERT INTO pw_permission VALUES('0','0','7','searchtime','basic','10');
INSERT INTO pw_permission VALUES('0','0','7','signnum','basic','100');
INSERT INTO pw_permission VALUES('0','0','7','show','basic','0');
INSERT INTO pw_permission VALUES('0','0','7','viewipfrom','basic','0');
INSERT INTO pw_permission VALUES('0','0','7','imgwidth','basic','');
INSERT INTO pw_permission VALUES('0','0','7','imgheight','basic','');
INSERT INTO pw_permission VALUES('0','0','7','fontsize','basic','');
INSERT INTO pw_permission VALUES('0','0','7','msggroup','basic','0');
INSERT INTO pw_permission VALUES('0','0','7','maxfavor','basic','100');
INSERT INTO pw_permission VALUES('0','0','7','viewvote','basic','0');
INSERT INTO pw_permission VALUES('0','0','7','atccheck','basic','1');
INSERT INTO pw_permission VALUES('0','0','7','markable','basic','0');
INSERT INTO pw_permission VALUES('0','0','7','postlimit','basic','');
INSERT INTO pw_permission VALUES('0','0','7','uploadtype','basic','');
INSERT INTO pw_permission VALUES('0','0','7','markdt','basic','');
INSERT INTO pw_permission VALUES('0','0','7','ifmemo','basic','1');
INSERT INTO pw_permission VALUES('0','0','8','allowvisit','basic','1');
INSERT INTO pw_permission VALUES('0','0','8','maxmsg','basic','10');
INSERT INTO pw_permission VALUES('0','0','8','allowhide','basic','0');
INSERT INTO pw_permission VALUES('0','0','8','allowread','basic','1');
INSERT INTO pw_permission VALUES('0','0','8','allowportait','basic','1');
INSERT INTO pw_permission VALUES('0','0','8','upload','basic','1');
INSERT INTO pw_permission VALUES('0','0','8','allowrp','basic','1');
INSERT INTO pw_permission VALUES('0','0','8','allowhonor','basic','1');
INSERT INTO pw_permission VALUES('0','0','8','allowdelatc','basic','1');
INSERT INTO pw_permission VALUES('0','0','8','allowpost','basic','1');
INSERT INTO pw_permission VALUES('0','0','8','allownewvote','basic','0');
INSERT INTO pw_permission VALUES('0','0','8','allowvote','basic','0');
INSERT INTO pw_permission VALUES('0','0','8','allowactive','basic','0');
INSERT INTO pw_permission VALUES('0','0','8','htmlcode','basic','0');
INSERT INTO pw_permission VALUES('0','0','8','allowhidden','basic','1');
INSERT INTO pw_permission VALUES('0','0','8','allowencode','basic','0');
INSERT INTO pw_permission VALUES('0','0','8','allowsell','basic','0');
INSERT INTO pw_permission VALUES('0','0','8','allowsearch','basic','1');
INSERT INTO pw_permission VALUES('0','0','8','allowmember','basic','0');
INSERT INTO pw_permission VALUES('0','0','8','allowprofile','basic','1');
INSERT INTO pw_permission VALUES('0','0','8','allowreport','basic','1');
INSERT INTO pw_permission VALUES('0','0','8','allowmessege','basic','1');
INSERT INTO pw_permission VALUES('0','0','8','allowsort','basic','0');
INSERT INTO pw_permission VALUES('0','0','8','alloworder','basic','1');
INSERT INTO pw_permission VALUES('0','0','8','allowupload','basic','1');
INSERT INTO pw_permission VALUES('0','0','8','allowdownload','basic','1');
INSERT INTO pw_permission VALUES('0','0','8','allownum','basic','50');
INSERT INTO pw_permission VALUES('0','0','8','edittime','basic','0');
INSERT INTO pw_permission VALUES('0','0','8','postpertime','basic','5');
INSERT INTO pw_permission VALUES('0','0','8','searchtime','basic','10');
INSERT INTO pw_permission VALUES('0','0','8','signnum','basic','30');
INSERT INTO pw_permission VALUES('0','0','8','show','basic','0');
INSERT INTO pw_permission VALUES('0','0','8','viewipfrom','basic','0');
INSERT INTO pw_permission VALUES('0','0','8','imgwidth','basic','');
INSERT INTO pw_permission VALUES('0','0','8','imgheight','basic','');
INSERT INTO pw_permission VALUES('0','0','8','fontsize','basic','3');
INSERT INTO pw_permission VALUES('0','0','8','msggroup','basic','0');
INSERT INTO pw_permission VALUES('0','0','8','maxfavor','basic','50');
INSERT INTO pw_permission VALUES('0','0','8','viewvote','basic','0');
INSERT INTO pw_permission VALUES('0','0','8','atccheck','basic','1');
INSERT INTO pw_permission VALUES('0','0','8','markable','basic','0');
INSERT INTO pw_permission VALUES('0','0','8','postlimit','basic','');
INSERT INTO pw_permission VALUES('0','0','8','uploadtype','basic','');
INSERT INTO pw_permission VALUES('0','0','8','markdt','basic','');
INSERT INTO pw_permission VALUES('0','0','8','anonymous','basic','0');
INSERT INTO pw_permission VALUES('0','0','8','leaveword','basic','1');
INSERT INTO pw_permission VALUES('0','0','8','ifmemo','basic','1');
INSERT INTO pw_permission VALUES('0','0','8','atclog','basic','0');
INSERT INTO pw_permission VALUES('0','0','8','schtime','basic','7776000');
INSERT INTO pw_permission VALUES('0','0','8','modifyvote','basic','0');
INSERT INTO pw_permission VALUES('0','0','8','allowreward','basic','0');
INSERT INTO pw_permission VALUES('0','0','8','allowgoods','basic','0');
INSERT INTO pw_permission VALUES('0','0','8','allowdebate','basic','0');
INSERT INTO pw_permission VALUES('0','0','8','dig','basic','0');
INSERT INTO pw_permission VALUES('0','0','8','pergroup','basic','member');
INSERT INTO pw_permission VALUES('0','0','8','markset','basic','a:5:{s:5:"money";a:3:{s:9:"marklimit";N;s:9:"maxcredit";s:0:"";s:6:"markdt";s:1:"0";}s:4:"rvrc";a:3:{s:9:"marklimit";N;s:9:"maxcredit";s:0:"";s:6:"markdt";s:1:"0";}s:6:"credit";a:3:{s:9:"marklimit";N;s:9:"maxcredit";s:0:"";s:6:"markdt";s:1:"0";}s:8:"currency";a:3:{s:9:"marklimit";N;s:9:"maxcredit";s:0:"";s:6:"markdt";s:1:"0";}i:1;a:3:{s:9:"marklimit";N;s:9:"maxcredit";s:0:"";s:6:"markdt";s:1:"0";}}');
INSERT INTO pw_permission VALUES('0','0','8','maxgraft','basic','1');
INSERT INTO pw_permission VALUES('0','0','9','allowvisit','basic','1');
INSERT INTO pw_permission VALUES('0','0','9','maxmsg','basic','10');
INSERT INTO pw_permission VALUES('0','0','9','allowhide','basic','0');
INSERT INTO pw_permission VALUES('0','0','9','allowread','basic','1');
INSERT INTO pw_permission VALUES('0','0','9','allowportait','basic','1');
INSERT INTO pw_permission VALUES('0','0','9','upload','basic','1');
INSERT INTO pw_permission VALUES('0','0','9','allowrp','basic','1');
INSERT INTO pw_permission VALUES('0','0','9','allowhonor','basic','1');
INSERT INTO pw_permission VALUES('0','0','9','allowdelatc','basic','1');
INSERT INTO pw_permission VALUES('0','0','9','allowpost','basic','1');
INSERT INTO pw_permission VALUES('0','0','9','allownewvote','basic','1');
INSERT INTO pw_permission VALUES('0','0','9','allowvote','basic','1');
INSERT INTO pw_permission VALUES('0','0','9','allowactive','basic','1');
INSERT INTO pw_permission VALUES('0','0','9','htmlcode','basic','0');
INSERT INTO pw_permission VALUES('0','0','9','allowhidden','basic','1');
INSERT INTO pw_permission VALUES('0','0','9','allowencode','basic','1');
INSERT INTO pw_permission VALUES('0','0','9','allowsell','basic','1');
INSERT INTO pw_permission VALUES('0','0','9','allowsearch','basic','1');
INSERT INTO pw_permission VALUES('0','0','9','allowmember','basic','1');
INSERT INTO pw_permission VALUES('0','0','9','allowprofile','basic','1');
INSERT INTO pw_permission VALUES('0','0','9','allowreport','basic','1');
INSERT INTO pw_permission VALUES('0','0','9','allowmessege','basic','1');
INSERT INTO pw_permission VALUES('0','0','9','allowsort','basic','1');
INSERT INTO pw_permission VALUES('0','0','9','alloworder','basic','1');
INSERT INTO pw_permission VALUES('0','0','9','allowupload','basic','1');
INSERT INTO pw_permission VALUES('0','0','9','allowdownload','basic','1');
INSERT INTO pw_permission VALUES('0','0','9','allownum','basic','50');
INSERT INTO pw_permission VALUES('0','0','9','edittime','basic','0');
INSERT INTO pw_permission VALUES('0','0','9','postpertime','basic','5');
INSERT INTO pw_permission VALUES('0','0','9','searchtime','basic','10');
INSERT INTO pw_permission VALUES('0','0','9','signnum','basic','50');
INSERT INTO pw_permission VALUES('0','0','9','markable','basic','1');
INSERT INTO pw_permission VALUES('0','0','9','maxfavor','basic','100');
INSERT INTO pw_permission VALUES('0','0','9','markdt','basic','');
INSERT INTO pw_permission VALUES('0','0','9','atccheck','basic','1');
INSERT INTO pw_permission VALUES('0','0','9','anonymous','basic','0');
INSERT INTO pw_permission VALUES('0','0','9','leaveword','basic','1');
INSERT INTO pw_permission VALUES('0','0','9','ifmemo','basic','1');
INSERT INTO pw_permission VALUES('0','0','9','markset','basic','a:5:{s:5:"money";a:4:{s:9:"markctype";s:5:"money";s:9:"marklimit";N;s:9:"maxcredit";s:1:"5";s:6:"markdt";s:1:"0";}s:4:"rvrc";a:3:{s:9:"marklimit";N;s:9:"maxcredit";s:0:"";s:6:"markdt";s:1:"0";}s:6:"credit";a:3:{s:9:"marklimit";N;s:9:"maxcredit";s:0:"";s:6:"markdt";s:1:"0";}s:8:"currency";a:3:{s:9:"marklimit";N;s:9:"maxcredit";s:0:"";s:6:"markdt";s:1:"0";}i:1;a:3:{s:9:"marklimit";N;s:9:"maxcredit";s:0:"";s:6:"markdt";s:1:"0";}}');
INSERT INTO pw_permission VALUES('0','0','9','atclog','basic','1');
INSERT INTO pw_permission VALUES('0','0','9','show','basic','1');
INSERT INTO pw_permission VALUES('0','0','9','viewipfrom','basic','0');
INSERT INTO pw_permission VALUES('0','0','9','schtime','basic','7776000');
INSERT INTO pw_permission VALUES('0','0','9','msggroup','basic','0');
INSERT INTO pw_permission VALUES('0','0','9','modifyvote','basic','1');
INSERT INTO pw_permission VALUES('0','0','9','viewvote','basic','0');
INSERT INTO pw_permission VALUES('0','0','9','allowreward','basic','1');
INSERT INTO pw_permission VALUES('0','0','9','allowgoods','basic','1');
INSERT INTO pw_permission VALUES('0','0','9','allowdebate','basic','1');
INSERT INTO pw_permission VALUES('0','0','9','dig','basic','1');
INSERT INTO pw_permission VALUES('0','0','9','maxgraft','basic','2');
INSERT INTO pw_permission VALUES('0','0','9','pergroup','basic','member');
INSERT INTO pw_permission VALUES('0','0','10','allowvisit','basic','1');
INSERT INTO pw_permission VALUES('0','0','10','pergroup','basic','member');
INSERT INTO pw_permission VALUES('0','0','11','pergroup','basic','member');
INSERT INTO pw_permission VALUES('0','0','12','pergroup','basic','member');
INSERT INTO pw_permission VALUES('0','0','13','pergroup','basic','member');
INSERT INTO pw_permission VALUES('0','0','14','pergroup','basic','member');
INSERT INTO pw_permission VALUES('0','0','15','pergroup','basic','member');
INSERT INTO pw_permission VALUES('0','0','10','maxmsg','basic','30');
INSERT INTO pw_permission VALUES('0','0','10','allowhide','basic','0');
INSERT INTO pw_permission VALUES('0','0','10','allowread','basic','1');
INSERT INTO pw_permission VALUES('0','0','10','allowportait','basic','1');
INSERT INTO pw_permission VALUES('0','0','10','upload','basic','1');
INSERT INTO pw_permission VALUES('0','0','10','allowrp','basic','1');
INSERT INTO pw_permission VALUES('0','0','10','allowhonor','basic','1');
INSERT INTO pw_permission VALUES('0','0','10','allowdelatc','basic','1');
INSERT INTO pw_permission VALUES('0','0','10','allowpost','basic','1');
INSERT INTO pw_permission VALUES('0','0','10','allownewvote','basic','1');
INSERT INTO pw_permission VALUES('0','0','10','allowvote','basic','1');
INSERT INTO pw_permission VALUES('0','0','10','allowactive','basic','1');
INSERT INTO pw_permission VALUES('0','0','10','htmlcode','basic','0');
INSERT INTO pw_permission VALUES('0','0','10','allowhidden','basic','1');
INSERT INTO pw_permission VALUES('0','0','10','allowencode','basic','1');
INSERT INTO pw_permission VALUES('0','0','10','allowsell','basic','1');
INSERT INTO pw_permission VALUES('0','0','10','allowsearch','basic','2');
INSERT INTO pw_permission VALUES('0','0','10','allowmember','basic','1');
INSERT INTO pw_permission VALUES('0','0','10','allowprofile','basic','1');
INSERT INTO pw_permission VALUES('0','0','10','allowreport','basic','1');
INSERT INTO pw_permission VALUES('0','0','10','allowmessege','basic','1');
INSERT INTO pw_permission VALUES('0','0','10','allowsort','basic','1');
INSERT INTO pw_permission VALUES('0','0','10','alloworder','basic','1');
INSERT INTO pw_permission VALUES('0','0','10','allowupload','basic','1');
INSERT INTO pw_permission VALUES('0','0','10','allowdownload','basic','1');
INSERT INTO pw_permission VALUES('0','0','10','allownum','basic','50');
INSERT INTO pw_permission VALUES('0','0','10','edittime','basic','0');
INSERT INTO pw_permission VALUES('0','0','10','postpertime','basic','3');
INSERT INTO pw_permission VALUES('0','0','10','searchtime','basic','10');
INSERT INTO pw_permission VALUES('0','0','10','signnum','basic','100');
INSERT INTO pw_permission VALUES('0','0','10','markable','basic','1');
INSERT INTO pw_permission VALUES('0','0','10','maxfavor','basic','100');
INSERT INTO pw_permission VALUES('0','0','10','markdt','basic','');
INSERT INTO pw_permission VALUES('0','0','10','atccheck','basic','1');
INSERT INTO pw_permission VALUES('0','0','10','anonymous','basic','0');
INSERT INTO pw_permission VALUES('0','0','10','leaveword','basic','1');
INSERT INTO pw_permission VALUES('0','0','10','ifmemo','basic','1');
INSERT INTO pw_permission VALUES('0','0','10','markset','basic','a:5:{s:5:"money";a:4:{s:9:"markctype";s:5:"money";s:9:"marklimit";N;s:9:"maxcredit";s:1:"5";s:6:"markdt";s:1:"0";}s:4:"rvrc";a:3:{s:9:"marklimit";N;s:9:"maxcredit";s:0:"";s:6:"markdt";s:1:"0";}s:6:"credit";a:3:{s:9:"marklimit";N;s:9:"maxcredit";s:0:"";s:6:"markdt";s:1:"0";}s:8:"currency";a:3:{s:9:"marklimit";N;s:9:"maxcredit";s:0:"";s:6:"markdt";s:1:"0";}i:1;a:3:{s:9:"marklimit";N;s:9:"maxcredit";s:0:"";s:6:"markdt";s:1:"0";}}');
INSERT INTO pw_permission VALUES('0','0','10','atclog','basic','1');
INSERT INTO pw_permission VALUES('0','0','10','show','basic','1');
INSERT INTO pw_permission VALUES('0','0','10','modifyvote','basic','1');
INSERT INTO pw_permission VALUES('0','0','10','allowreward','basic','1');
INSERT INTO pw_permission VALUES('0','0','10','allowgoods','basic','1');
INSERT INTO pw_permission VALUES('0','0','10','allowdebate','basic','1');
INSERT INTO pw_permission VALUES('0','0','10','dig','basic','1');
INSERT INTO pw_permission VALUES('0','0','10','viewipfrom','basic','0');
INSERT INTO pw_permission VALUES('0','0','10','schtime','basic','7776000');
INSERT INTO pw_permission VALUES('0','0','10','msggroup','basic','0');
INSERT INTO pw_permission VALUES('0','0','10','viewvote','basic','0');
INSERT INTO pw_permission VALUES('0','0','10','maxgraft','basic','5');
INSERT INTO pw_permission VALUES('0','0','11','allowvisit','basic','1');
INSERT INTO pw_permission VALUES('0','0','11','maxmsg','basic','30');
INSERT INTO pw_permission VALUES('0','0','11','allowhide','basic','0');
INSERT INTO pw_permission VALUES('0','0','11','allowread','basic','1');
INSERT INTO pw_permission VALUES('0','0','11','allowportait','basic','1');
INSERT INTO pw_permission VALUES('0','0','11','upload','basic','1');
INSERT INTO pw_permission VALUES('0','0','11','allowrp','basic','1');
INSERT INTO pw_permission VALUES('0','0','11','allowhonor','basic','1');
INSERT INTO pw_permission VALUES('0','0','11','allowdelatc','basic','1');
INSERT INTO pw_permission VALUES('0','0','11','allowpost','basic','1');
INSERT INTO pw_permission VALUES('0','0','11','allownewvote','basic','1');
INSERT INTO pw_permission VALUES('0','0','11','allowvote','basic','1');
INSERT INTO pw_permission VALUES('0','0','11','allowactive','basic','1');
INSERT INTO pw_permission VALUES('0','0','11','htmlcode','basic','0');
INSERT INTO pw_permission VALUES('0','0','11','allowhidden','basic','1');
INSERT INTO pw_permission VALUES('0','0','11','allowencode','basic','1');
INSERT INTO pw_permission VALUES('0','0','11','allowsell','basic','1');
INSERT INTO pw_permission VALUES('0','0','11','allowsearch','basic','2');
INSERT INTO pw_permission VALUES('0','0','11','allowmember','basic','1');
INSERT INTO pw_permission VALUES('0','0','11','allowprofile','basic','1');
INSERT INTO pw_permission VALUES('0','0','11','allowreport','basic','1');
INSERT INTO pw_permission VALUES('0','0','11','allowmessege','basic','1');
INSERT INTO pw_permission VALUES('0','0','11','allowsort','basic','1');
INSERT INTO pw_permission VALUES('0','0','11','alloworder','basic','1');
INSERT INTO pw_permission VALUES('0','0','11','allowupload','basic','1');
INSERT INTO pw_permission VALUES('0','0','11','allowdownload','basic','1');
INSERT INTO pw_permission VALUES('0','0','11','allownum','basic','50');
INSERT INTO pw_permission VALUES('0','0','11','edittime','basic','0');
INSERT INTO pw_permission VALUES('0','0','11','postpertime','basic','3');
INSERT INTO pw_permission VALUES('0','0','11','searchtime','basic','10');
INSERT INTO pw_permission VALUES('0','0','11','signnum','basic','150');
INSERT INTO pw_permission VALUES('0','0','11','markable','basic','1');
INSERT INTO pw_permission VALUES('0','0','11','maxfavor','basic','100');
INSERT INTO pw_permission VALUES('0','0','11','markdt','basic','');
INSERT INTO pw_permission VALUES('0','0','11','atccheck','basic','1');
INSERT INTO pw_permission VALUES('0','0','11','anonymous','basic','0');
INSERT INTO pw_permission VALUES('0','0','11','leaveword','basic','1');
INSERT INTO pw_permission VALUES('0','0','11','ifmemo','basic','1');
INSERT INTO pw_permission VALUES('0','0','11','markset','basic','a:5:{s:5:"money";a:4:{s:9:"markctype";s:5:"money";s:9:"marklimit";N;s:9:"maxcredit";s:2:"10";s:6:"markdt";s:1:"0";}s:4:"rvrc";a:3:{s:9:"marklimit";N;s:9:"maxcredit";s:0:"";s:6:"markdt";s:1:"0";}s:6:"credit";a:3:{s:9:"marklimit";N;s:9:"maxcredit";s:0:"";s:6:"markdt";s:1:"0";}s:8:"currency";a:3:{s:9:"marklimit";N;s:9:"maxcredit";s:0:"";s:6:"markdt";s:1:"0";}i:1;a:3:{s:9:"marklimit";N;s:9:"maxcredit";s:0:"";s:6:"markdt";s:1:"0";}}');
INSERT INTO pw_permission VALUES('0','0','11','atclog','basic','1');
INSERT INTO pw_permission VALUES('0','0','11','show','basic','1');
INSERT INTO pw_permission VALUES('0','0','11','viewipfrom','basic','0');
INSERT INTO pw_permission VALUES('0','0','11','schtime','basic','7776000');
INSERT INTO pw_permission VALUES('0','0','11','msggroup','basic','0');
INSERT INTO pw_permission VALUES('0','0','11','modifyvote','basic','1');
INSERT INTO pw_permission VALUES('0','0','11','viewvote','basic','0');
INSERT INTO pw_permission VALUES('0','0','11','allowreward','basic','1');
INSERT INTO pw_permission VALUES('0','0','11','allowgoods','basic','1');
INSERT INTO pw_permission VALUES('0','0','11','allowdebate','basic','1');
INSERT INTO pw_permission VALUES('0','0','11','dig','basic','1');
INSERT INTO pw_permission VALUES('0','0','11','maxgraft','basic','5');
INSERT INTO pw_permission VALUES('0','0','12','allowvisit','basic','1');
INSERT INTO pw_permission VALUES('0','0','12','maxmsg','basic','30');
INSERT INTO pw_permission VALUES('0','0','12','allowhide','basic','0');
INSERT INTO pw_permission VALUES('0','0','12','allowread','basic','1');
INSERT INTO pw_permission VALUES('0','0','12','allowportait','basic','1');
INSERT INTO pw_permission VALUES('0','0','12','upload','basic','1');
INSERT INTO pw_permission VALUES('0','0','12','allowrp','basic','1');
INSERT INTO pw_permission VALUES('0','0','12','allowhonor','basic','1');
INSERT INTO pw_permission VALUES('0','0','12','allowdelatc','basic','1');
INSERT INTO pw_permission VALUES('0','0','12','allowpost','basic','1');
INSERT INTO pw_permission VALUES('0','0','12','allownewvote','basic','1');
INSERT INTO pw_permission VALUES('0','0','12','allowvote','basic','1');
INSERT INTO pw_permission VALUES('0','0','12','allowactive','basic','1');
INSERT INTO pw_permission VALUES('0','0','12','htmlcode','basic','0');
INSERT INTO pw_permission VALUES('0','0','12','allowhidden','basic','1');
INSERT INTO pw_permission VALUES('0','0','12','allowencode','basic','1');
INSERT INTO pw_permission VALUES('0','0','12','allowsell','basic','1');
INSERT INTO pw_permission VALUES('0','0','12','allowsearch','basic','2');
INSERT INTO pw_permission VALUES('0','0','12','allowmember','basic','1');
INSERT INTO pw_permission VALUES('0','0','12','allowprofile','basic','1');
INSERT INTO pw_permission VALUES('0','0','12','allowreport','basic','1');
INSERT INTO pw_permission VALUES('0','0','12','allowmessege','basic','1');
INSERT INTO pw_permission VALUES('0','0','12','allowsort','basic','1');
INSERT INTO pw_permission VALUES('0','0','12','alloworder','basic','1');
INSERT INTO pw_permission VALUES('0','0','12','allowupload','basic','1');
INSERT INTO pw_permission VALUES('0','0','12','allowdownload','basic','1');
INSERT INTO pw_permission VALUES('0','0','12','allownum','basic','50');
INSERT INTO pw_permission VALUES('0','0','12','edittime','basic','0');
INSERT INTO pw_permission VALUES('0','0','12','postpertime','basic','3');
INSERT INTO pw_permission VALUES('0','0','12','searchtime','basic','10');
INSERT INTO pw_permission VALUES('0','0','12','signnum','basic','200');
INSERT INTO pw_permission VALUES('0','0','12','markable','basic','1');
INSERT INTO pw_permission VALUES('0','0','12','maxfavor','basic','100');
INSERT INTO pw_permission VALUES('0','0','12','markdt','basic','');
INSERT INTO pw_permission VALUES('0','0','12','atccheck','basic','1');
INSERT INTO pw_permission VALUES('0','0','12','anonymous','basic','0');
INSERT INTO pw_permission VALUES('0','0','12','leaveword','basic','1');
INSERT INTO pw_permission VALUES('0','0','12','ifmemo','basic','1');
INSERT INTO pw_permission VALUES('0','0','12','markset','basic','a:5:{s:5:"money";a:4:{s:9:"markctype";s:5:"money";s:9:"marklimit";N;s:9:"maxcredit";s:2:"20";s:6:"markdt";s:1:"0";}s:4:"rvrc";a:3:{s:9:"marklimit";N;s:9:"maxcredit";s:0:"";s:6:"markdt";s:1:"0";}s:6:"credit";a:3:{s:9:"marklimit";N;s:9:"maxcredit";s:0:"";s:6:"markdt";s:1:"0";}s:8:"currency";a:3:{s:9:"marklimit";N;s:9:"maxcredit";s:0:"";s:6:"markdt";s:1:"0";}i:1;a:3:{s:9:"marklimit";N;s:9:"maxcredit";s:0:"";s:6:"markdt";s:1:"0";}}');
INSERT INTO pw_permission VALUES('0','0','12','atclog','basic','1');
INSERT INTO pw_permission VALUES('0','0','12','show','basic','1');
INSERT INTO pw_permission VALUES('0','0','12','modifyvote','basic','1');
INSERT INTO pw_permission VALUES('0','0','12','allowreward','basic','1');
INSERT INTO pw_permission VALUES('0','0','12','allowgoods','basic','1');
INSERT INTO pw_permission VALUES('0','0','12','allowdebate','basic','1');
INSERT INTO pw_permission VALUES('0','0','12','dig','basic','1');
INSERT INTO pw_permission VALUES('0','0','12','viewipfrom','basic','0');
INSERT INTO pw_permission VALUES('0','0','12','schtime','basic','7776000');
INSERT INTO pw_permission VALUES('0','0','12','msggroup','basic','0');
INSERT INTO pw_permission VALUES('0','0','12','viewvote','basic','0');
INSERT INTO pw_permission VALUES('0','0','12','maxgraft','basic','5');
INSERT INTO pw_permission VALUES('0','0','13','allowvisit','basic','1');
INSERT INTO pw_permission VALUES('0','0','13','maxmsg','basic','50');
INSERT INTO pw_permission VALUES('0','0','13','allowhide','basic','0');
INSERT INTO pw_permission VALUES('0','0','13','allowread','basic','1');
INSERT INTO pw_permission VALUES('0','0','13','allowportait','basic','1');
INSERT INTO pw_permission VALUES('0','0','13','upload','basic','1');
INSERT INTO pw_permission VALUES('0','0','13','allowrp','basic','1');
INSERT INTO pw_permission VALUES('0','0','13','allowhonor','basic','1');
INSERT INTO pw_permission VALUES('0','0','13','allowdelatc','basic','1');
INSERT INTO pw_permission VALUES('0','0','13','allowpost','basic','1');
INSERT INTO pw_permission VALUES('0','0','13','allownewvote','basic','1');
INSERT INTO pw_permission VALUES('0','0','13','allowvote','basic','1');
INSERT INTO pw_permission VALUES('0','0','13','allowactive','basic','1');
INSERT INTO pw_permission VALUES('0','0','13','htmlcode','basic','0');
INSERT INTO pw_permission VALUES('0','0','13','allowhidden','basic','1');
INSERT INTO pw_permission VALUES('0','0','13','allowencode','basic','1');
INSERT INTO pw_permission VALUES('0','0','13','allowsell','basic','1');
INSERT INTO pw_permission VALUES('0','0','13','allowsearch','basic','2');
INSERT INTO pw_permission VALUES('0','0','13','allowmember','basic','1');
INSERT INTO pw_permission VALUES('0','0','13','allowprofile','basic','1');
INSERT INTO pw_permission VALUES('0','0','13','allowreport','basic','1');
INSERT INTO pw_permission VALUES('0','0','13','allowmessege','basic','1');
INSERT INTO pw_permission VALUES('0','0','13','allowsort','basic','1');
INSERT INTO pw_permission VALUES('0','0','13','alloworder','basic','1');
INSERT INTO pw_permission VALUES('0','0','13','allowupload','basic','1');
INSERT INTO pw_permission VALUES('0','0','13','allowdownload','basic','1');
INSERT INTO pw_permission VALUES('0','0','13','allownum','basic','50');
INSERT INTO pw_permission VALUES('0','0','13','edittime','basic','0');
INSERT INTO pw_permission VALUES('0','0','13','postpertime','basic','3');
INSERT INTO pw_permission VALUES('0','0','13','searchtime','basic','10');
INSERT INTO pw_permission VALUES('0','0','13','signnum','basic','200');
INSERT INTO pw_permission VALUES('0','0','13','markable','basic','1');
INSERT INTO pw_permission VALUES('0','0','13','maxfavor','basic','100');
INSERT INTO pw_permission VALUES('0','0','13','markdt','basic','');
INSERT INTO pw_permission VALUES('0','0','13','atccheck','basic','1');
INSERT INTO pw_permission VALUES('0','0','13','anonymous','basic','0');
INSERT INTO pw_permission VALUES('0','0','13','leaveword','basic','1');
INSERT INTO pw_permission VALUES('0','0','13','ifmemo','basic','1');
INSERT INTO pw_permission VALUES('0','0','13','markset','basic','a:5:{s:5:"money";a:4:{s:9:"markctype";s:5:"money";s:9:"maxcredit";s:2:"50";s:9:"marklimit";N;s:6:"markdt";s:1:"0";}s:4:"rvrc";a:3:{s:9:"maxcredit";s:0:"";s:9:"marklimit";N;s:6:"markdt";s:1:"0";}s:6:"credit";a:3:{s:9:"maxcredit";s:0:"";s:9:"marklimit";N;s:6:"markdt";s:1:"0";}s:8:"currency";a:3:{s:9:"maxcredit";s:0:"";s:9:"marklimit";N;s:6:"markdt";s:1:"0";}i:1;a:3:{s:9:"maxcredit";s:0:"";s:9:"marklimit";N;s:6:"markdt";s:1:"0";}}');
INSERT INTO pw_permission VALUES('0','0','13','atclog','basic','1');
INSERT INTO pw_permission VALUES('0','0','13','show','basic','1');
INSERT INTO pw_permission VALUES('0','0','13','modifyvote','basic','1');
INSERT INTO pw_permission VALUES('0','0','13','allowreward','basic','1');
INSERT INTO pw_permission VALUES('0','0','13','allowgoods','basic','1');
INSERT INTO pw_permission VALUES('0','0','13','allowdebate','basic','1');
INSERT INTO pw_permission VALUES('0','0','13','dig','basic','1');
INSERT INTO pw_permission VALUES('0','0','13','viewipfrom','basic','0');
INSERT INTO pw_permission VALUES('0','0','13','schtime','basic','7776000');
INSERT INTO pw_permission VALUES('0','0','13','msggroup','basic','0');
INSERT INTO pw_permission VALUES('0','0','13','viewvote','basic','0');
INSERT INTO pw_permission VALUES('0','0','13','maxgraft','basic','5');
INSERT INTO pw_permission VALUES('0','0','14','allowvisit','basic','1');
INSERT INTO pw_permission VALUES('0','0','14','maxmsg','basic','50');
INSERT INTO pw_permission VALUES('0','0','14','allowhide','basic','0');
INSERT INTO pw_permission VALUES('0','0','14','allowread','basic','1');
INSERT INTO pw_permission VALUES('0','0','14','allowportait','basic','1');
INSERT INTO pw_permission VALUES('0','0','14','upload','basic','1');
INSERT INTO pw_permission VALUES('0','0','14','allowrp','basic','1');
INSERT INTO pw_permission VALUES('0','0','14','allowhonor','basic','1');
INSERT INTO pw_permission VALUES('0','0','14','allowdelatc','basic','1');
INSERT INTO pw_permission VALUES('0','0','14','allowpost','basic','1');
INSERT INTO pw_permission VALUES('0','0','14','allownewvote','basic','1');
INSERT INTO pw_permission VALUES('0','0','14','allowvote','basic','1');
INSERT INTO pw_permission VALUES('0','0','14','allowactive','basic','1');
INSERT INTO pw_permission VALUES('0','0','14','htmlcode','basic','0');
INSERT INTO pw_permission VALUES('0','0','14','allowhidden','basic','1');
INSERT INTO pw_permission VALUES('0','0','14','allowencode','basic','1');
INSERT INTO pw_permission VALUES('0','0','14','allowsell','basic','1');
INSERT INTO pw_permission VALUES('0','0','14','allowsearch','basic','2');
INSERT INTO pw_permission VALUES('0','0','14','allowmember','basic','1');
INSERT INTO pw_permission VALUES('0','0','14','allowprofile','basic','1');
INSERT INTO pw_permission VALUES('0','0','14','allowreport','basic','1');
INSERT INTO pw_permission VALUES('0','0','14','allowmessege','basic','1');
INSERT INTO pw_permission VALUES('0','0','14','allowsort','basic','1');
INSERT INTO pw_permission VALUES('0','0','14','alloworder','basic','1');
INSERT INTO pw_permission VALUES('0','0','14','allowupload','basic','1');
INSERT INTO pw_permission VALUES('0','0','14','allowdownload','basic','1');
INSERT INTO pw_permission VALUES('0','0','14','allownum','basic','80');
INSERT INTO pw_permission VALUES('0','0','14','edittime','basic','0');
INSERT INTO pw_permission VALUES('0','0','14','postpertime','basic','3');
INSERT INTO pw_permission VALUES('0','0','14','searchtime','basic','10');
INSERT INTO pw_permission VALUES('0','0','14','signnum','basic','200');
INSERT INTO pw_permission VALUES('0','0','14','markable','basic','1');
INSERT INTO pw_permission VALUES('0','0','14','maxfavor','basic','100');
INSERT INTO pw_permission VALUES('0','0','14','markdt','basic','');
INSERT INTO pw_permission VALUES('0','0','14','atccheck','basic','1');
INSERT INTO pw_permission VALUES('0','0','14','anonymous','basic','0');
INSERT INTO pw_permission VALUES('0','0','14','leaveword','basic','1');
INSERT INTO pw_permission VALUES('0','0','14','ifmemo','basic','1');
INSERT INTO pw_permission VALUES('0','0','14','markset','basic','a:5:{s:5:"money";a:4:{s:9:"markctype";s:5:"money";s:9:"marklimit";N;s:9:"maxcredit";s:2:"80";s:6:"markdt";s:1:"0";}s:4:"rvrc";a:3:{s:9:"marklimit";N;s:9:"maxcredit";s:0:"";s:6:"markdt";s:1:"0";}s:6:"credit";a:3:{s:9:"marklimit";N;s:9:"maxcredit";s:0:"";s:6:"markdt";s:1:"0";}s:8:"currency";a:3:{s:9:"marklimit";N;s:9:"maxcredit";s:0:"";s:6:"markdt";s:1:"0";}i:1;a:3:{s:9:"marklimit";N;s:9:"maxcredit";s:0:"";s:6:"markdt";s:1:"0";}}');
INSERT INTO pw_permission VALUES('0','0','14','atclog','basic','1');
INSERT INTO pw_permission VALUES('0','0','14','show','basic','1');
INSERT INTO pw_permission VALUES('0','0','14','modifyvote','basic','1');
INSERT INTO pw_permission VALUES('0','0','14','allowreward','basic','1');
INSERT INTO pw_permission VALUES('0','0','14','allowgoods','basic','1');
INSERT INTO pw_permission VALUES('0','0','14','allowdebate','basic','1');
INSERT INTO pw_permission VALUES('0','0','14','dig','basic','1');
INSERT INTO pw_permission VALUES('0','0','14','viewipfrom','basic','0');
INSERT INTO pw_permission VALUES('0','0','14','schtime','basic','7776000');
INSERT INTO pw_permission VALUES('0','0','14','msggroup','basic','0');
INSERT INTO pw_permission VALUES('0','0','14','viewvote','basic','0');
INSERT INTO pw_permission VALUES('0','0','14','maxgraft','basic','5');
INSERT INTO pw_permission VALUES('0','0','15','allowvisit','basic','1');
INSERT INTO pw_permission VALUES('0','0','15','maxmsg','basic','50');
INSERT INTO pw_permission VALUES('0','0','15','allowhide','basic','0');
INSERT INTO pw_permission VALUES('0','0','15','allowread','basic','1');
INSERT INTO pw_permission VALUES('0','0','15','allowportait','basic','1');
INSERT INTO pw_permission VALUES('0','0','15','upload','basic','1');
INSERT INTO pw_permission VALUES('0','0','15','allowrp','basic','1');
INSERT INTO pw_permission VALUES('0','0','15','allowhonor','basic','1');
INSERT INTO pw_permission VALUES('0','0','15','allowdelatc','basic','1');
INSERT INTO pw_permission VALUES('0','0','15','allowpost','basic','1');
INSERT INTO pw_permission VALUES('0','0','15','allownewvote','basic','1');
INSERT INTO pw_permission VALUES('0','0','15','allowvote','basic','1');
INSERT INTO pw_permission VALUES('0','0','15','allowactive','basic','1');
INSERT INTO pw_permission VALUES('0','0','15','htmlcode','basic','0');
INSERT INTO pw_permission VALUES('0','0','15','allowhidden','basic','1');
INSERT INTO pw_permission VALUES('0','0','15','allowencode','basic','1');
INSERT INTO pw_permission VALUES('0','0','15','allowsell','basic','1');
INSERT INTO pw_permission VALUES('0','0','15','allowsearch','basic','2');
INSERT INTO pw_permission VALUES('0','0','15','allowmember','basic','1');
INSERT INTO pw_permission VALUES('0','0','15','allowprofile','basic','1');
INSERT INTO pw_permission VALUES('0','0','15','allowreport','basic','1');
INSERT INTO pw_permission VALUES('0','0','15','allowmessege','basic','1');
INSERT INTO pw_permission VALUES('0','0','15','allowsort','basic','1');
INSERT INTO pw_permission VALUES('0','0','15','alloworder','basic','1');
INSERT INTO pw_permission VALUES('0','0','15','allowupload','basic','1');
INSERT INTO pw_permission VALUES('0','0','15','allowdownload','basic','1');
INSERT INTO pw_permission VALUES('0','0','15','allownum','basic','100');
INSERT INTO pw_permission VALUES('0','0','15','edittime','basic','0');
INSERT INTO pw_permission VALUES('0','0','15','postpertime','basic','1');
INSERT INTO pw_permission VALUES('0','0','15','searchtime','basic','10');
INSERT INTO pw_permission VALUES('0','0','15','signnum','basic','200');
INSERT INTO pw_permission VALUES('0','0','15','markable','basic','1');
INSERT INTO pw_permission VALUES('0','0','15','maxfavor','basic','100');
INSERT INTO pw_permission VALUES('0','0','15','markdt','basic','');
INSERT INTO pw_permission VALUES('0','0','15','atccheck','basic','1');
INSERT INTO pw_permission VALUES('0','0','15','anonymous','basic','0');
INSERT INTO pw_permission VALUES('0','0','15','leaveword','basic','1');
INSERT INTO pw_permission VALUES('0','0','15','ifmemo','basic','1');
INSERT INTO pw_permission VALUES('0','0','15','markset','basic','a:5:{s:5:"money";a:4:{s:9:"markctype";s:5:"money";s:9:"maxcredit";s:3:"100";s:9:"marklimit";N;s:6:"markdt";s:1:"0";}s:4:"rvrc";a:3:{s:9:"maxcredit";s:0:"";s:9:"marklimit";N;s:6:"markdt";s:1:"0";}s:6:"credit";a:3:{s:9:"maxcredit";s:0:"";s:9:"marklimit";N;s:6:"markdt";s:1:"0";}s:8:"currency";a:3:{s:9:"maxcredit";s:0:"";s:9:"marklimit";N;s:6:"markdt";s:1:"0";}i:1;a:3:{s:9:"maxcredit";s:0:"";s:9:"marklimit";N;s:6:"markdt";s:1:"0";}}');
INSERT INTO pw_permission VALUES('0','0','15','atclog','basic','1');
INSERT INTO pw_permission VALUES('0','0','15','show','basic','1');
INSERT INTO pw_permission VALUES('0','0','15','viewipfrom','basic','1');
INSERT INTO pw_permission VALUES('0','0','15','schtime','basic','7776000');
INSERT INTO pw_permission VALUES('0','0','15','msggroup','basic','1');
INSERT INTO pw_permission VALUES('0','0','15','modifyvote','basic','1');
INSERT INTO pw_permission VALUES('0','0','15','viewvote','basic','0');
INSERT INTO pw_permission VALUES('0','0','15','allowreward','basic','1');
INSERT INTO pw_permission VALUES('0','0','15','allowgoods','basic','1');
INSERT INTO pw_permission VALUES('0','0','15','allowdebate','basic','1');
INSERT INTO pw_permission VALUES('0','0','15','dig','basic','1');
INSERT INTO pw_permission VALUES('0','0','15','maxgraft','basic','10');
INSERT INTO pw_permission VALUES('0','0','16','allowvisit','basic','1');
INSERT INTO pw_permission VALUES('0','0','16','maxmsg','basic','30');
INSERT INTO pw_permission VALUES('0','0','16','allowhide','basic','0');
INSERT INTO pw_permission VALUES('0','0','16','allowread','basic','1');
INSERT INTO pw_permission VALUES('0','0','16','allowportait','basic','1');
INSERT INTO pw_permission VALUES('0','0','16','upload','basic','1');
INSERT INTO pw_permission VALUES('0','0','16','allowrp','basic','1');
INSERT INTO pw_permission VALUES('0','0','16','allowhonor','basic','1');
INSERT INTO pw_permission VALUES('0','0','16','allowdelatc','basic','1');
INSERT INTO pw_permission VALUES('0','0','16','allowpost','basic','1');
INSERT INTO pw_permission VALUES('0','0','16','allownewvote','basic','1');
INSERT INTO pw_permission VALUES('0','0','16','allowvote','basic','1');
INSERT INTO pw_permission VALUES('0','0','16','allowactive','basic','1');
INSERT INTO pw_permission VALUES('0','0','16','htmlcode','basic','0');
INSERT INTO pw_permission VALUES('0','0','16','allowhidden','basic','1');
INSERT INTO pw_permission VALUES('0','0','16','allowencode','basic','1');
INSERT INTO pw_permission VALUES('0','0','16','allowsell','basic','1');
INSERT INTO pw_permission VALUES('0','0','16','allowsearch','basic','2');
INSERT INTO pw_permission VALUES('0','0','16','allowmember','basic','1');
INSERT INTO pw_permission VALUES('0','0','16','allowprofile','basic','1');
INSERT INTO pw_permission VALUES('0','0','16','allowreport','basic','1');
INSERT INTO pw_permission VALUES('0','0','16','allowmessege','basic','1');
INSERT INTO pw_permission VALUES('0','0','16','allowsort','basic','1');
INSERT INTO pw_permission VALUES('0','0','16','alloworder','basic','1');
INSERT INTO pw_permission VALUES('0','0','16','allowupload','basic','1');
INSERT INTO pw_permission VALUES('0','0','16','allowdownload','basic','1');
INSERT INTO pw_permission VALUES('0','0','16','allownum','basic','50');
INSERT INTO pw_permission VALUES('0','0','16','edittime','basic','0');
INSERT INTO pw_permission VALUES('0','0','16','postpertime','basic','3');
INSERT INTO pw_permission VALUES('0','0','16','searchtime','basic','10');
INSERT INTO pw_permission VALUES('0','0','16','signnum','basic','100');
INSERT INTO pw_permission VALUES('0','0','16','show','basic','1');
INSERT INTO pw_permission VALUES('0','0','16','viewipfrom','basic','0');
INSERT INTO pw_permission VALUES('0','0','16','imgwidth','basic','');
INSERT INTO pw_permission VALUES('0','0','16','imgheight','basic','');
INSERT INTO pw_permission VALUES('0','0','16','fontsize','basic','');
INSERT INTO pw_permission VALUES('0','0','16','msggroup','basic','0');
INSERT INTO pw_permission VALUES('0','0','16','maxfavor','basic','100');
INSERT INTO pw_permission VALUES('0','0','16','viewvote','basic','0');
INSERT INTO pw_permission VALUES('0','0','16','atccheck','basic','1');
INSERT INTO pw_permission VALUES('0','0','16','markable','basic','1');
INSERT INTO pw_permission VALUES('0','0','16','postlimit','basic','');
INSERT INTO pw_permission VALUES('0','0','16','uploadtype','basic','');
INSERT INTO pw_permission VALUES('0','0','16','markdt','basic','');
INSERT INTO pw_permission VALUES('0','0','16','anonymous','basic','0');
INSERT INTO pw_permission VALUES('0','0','16','leaveword','basic','1');
INSERT INTO pw_permission VALUES('0','0','16','allowadmincp','system','0');
INSERT INTO pw_permission VALUES('0','0','16','delatc','systemforum','0');
INSERT INTO pw_permission VALUES('0','0','16','moveatc','systemforum','0');
INSERT INTO pw_permission VALUES('0','0','16','copyatc','systemforum','0');
INSERT INTO pw_permission VALUES('0','0','16','digestadmin','systemforum','0');
INSERT INTO pw_permission VALUES('0','0','16','lockadmin','systemforum','0');
INSERT INTO pw_permission VALUES('0','0','16','pushadmin','systemforum','0');
INSERT INTO pw_permission VALUES('0','0','16','coloradmin','systemforum','0');
INSERT INTO pw_permission VALUES('0','0','16','downadmin','systemforum','0');
INSERT INTO pw_permission VALUES('0','0','16','viewcheck','systemforum','0');
INSERT INTO pw_permission VALUES('0','0','16','viewclose','systemforum','0');
INSERT INTO pw_permission VALUES('0','0','16','delattach','systemforum','0');
INSERT INTO pw_permission VALUES('0','0','16','viewip','systemforum','0');
INSERT INTO pw_permission VALUES('0','0','16','banuser','systemforum','0');
INSERT INTO pw_permission VALUES('0','0','16','bantype','systemforum','0');
INSERT INTO pw_permission VALUES('0','0','16','banmax','systemforum','0');
INSERT INTO pw_permission VALUES('0','0','16','posthide','systemforum','0');
INSERT INTO pw_permission VALUES('0','0','16','sellhide','systemforum','0');
INSERT INTO pw_permission VALUES('0','0','16','encodehide','systemforum','0');
INSERT INTO pw_permission VALUES('0','0','16','anonyhide','systemforum','0');
INSERT INTO pw_permission VALUES('0','0','16','postpers','systemforum','0');
INSERT INTO pw_permission VALUES('0','0','16','replylock','systemforum','0');
INSERT INTO pw_permission VALUES('0','0','16','modother','systemforum','0');
INSERT INTO pw_permission VALUES('0','0','16','deltpcs','systemforum','0');
INSERT INTO pw_permission VALUES('0','0','16','topped','systemforum','0');
INSERT INTO pw_permission VALUES('0','0','16','tpctype','systemforum','0');
INSERT INTO pw_permission VALUES('0','0','16','tpccheck','systemforum','0');
INSERT INTO pw_permission VALUES('0','0','16','allowtime','systemforum','0');
INSERT INTO pw_permission VALUES('0','0','16','superright','system','0');
INSERT INTO pw_permission VALUES('0','0','16','ifmemo','basic','1');
INSERT INTO pw_permission VALUES('0','0','16','markset','basic','a:5:{s:5:"money";a:4:{s:9:"markctype";s:5:"money";s:9:"maxcredit";s:1:"5";s:9:"marklimit";N;s:6:"markdt";s:1:"0";}s:4:"rvrc";a:3:{s:9:"maxcredit";s:0:"";s:9:"marklimit";N;s:6:"markdt";s:1:"0";}s:6:"credit";a:3:{s:9:"maxcredit";s:0:"";s:9:"marklimit";N;s:6:"markdt";s:1:"0";}s:8:"currency";a:3:{s:9:"maxcredit";s:0:"";s:9:"marklimit";N;s:6:"markdt";s:1:"0";}i:1;a:3:{s:9:"maxcredit";s:0:"";s:9:"marklimit";N;s:6:"markdt";s:1:"0";}}');
INSERT INTO pw_permission VALUES('0','0','16','atclog','basic','1');
INSERT INTO pw_permission VALUES('0','0','16','schtime','basic','7776000');
INSERT INTO pw_permission VALUES('0','0','16','maxsendmsg','basic','');
INSERT INTO pw_permission VALUES('0','0','16','pergroup','basic','member,special');
INSERT INTO pw_permission VALUES('0','0','16','maxgraft','basic','2');
INSERT INTO pw_permission VALUES('0','0','16','pwdlimitime','basic','');
INSERT INTO pw_permission VALUES('0','0','16','maxcstyles','basic','');
INSERT INTO pw_permission VALUES('0','0','16','modifyvote','basic','1');
INSERT INTO pw_permission VALUES('0','0','16','allowreward','basic','1');
INSERT INTO pw_permission VALUES('0','0','16','allowgoods','basic','1');
INSERT INTO pw_permission VALUES('0','0','16','allowdebate','basic','1');
INSERT INTO pw_permission VALUES('0','0','16','media','basic','');
INSERT INTO pw_permission VALUES('0','0','16','dig','basic','1');
INSERT INTO pw_permission VALUES('0','0','17','allowvisit','basic','1');
INSERT INTO pw_permission VALUES('0','0','17','allowhide','basic','0');
INSERT INTO pw_permission VALUES('0','0','17','allowread','basic','0');
INSERT INTO pw_permission VALUES('0','0','17','allowsearch','basic','0');
INSERT INTO pw_permission VALUES('0','0','17','allowmember','basic','0');
INSERT INTO pw_permission VALUES('0','0','17','allowprofile','basic','0');
INSERT INTO pw_permission VALUES('0','0','17','atclog','basic','0');
INSERT INTO pw_permission VALUES('0','0','17','show','basic','0');
INSERT INTO pw_permission VALUES('0','0','17','allowreport','basic','0');
INSERT INTO pw_permission VALUES('0','0','17','upload','basic','0');
INSERT INTO pw_permission VALUES('0','0','17','allowportait','basic','0');
INSERT INTO pw_permission VALUES('0','0','17','allowhonor','basic','0');
INSERT INTO pw_permission VALUES('0','0','17','allowmessege','basic','0');
INSERT INTO pw_permission VALUES('0','0','17','allowsort','basic','0');
INSERT INTO pw_permission VALUES('0','0','17','alloworder','basic','0');
INSERT INTO pw_permission VALUES('0','0','17','viewipfrom','basic','0');
INSERT INTO pw_permission VALUES('0','0','17','schtime','basic','7776000');
INSERT INTO pw_permission VALUES('0','0','17','msggroup','basic','0');
INSERT INTO pw_permission VALUES('0','0','17','ifmemo','basic','0');
INSERT INTO pw_permission VALUES('0','0','17','allowpost','basic','0');
INSERT INTO pw_permission VALUES('0','0','17','allowrp','basic','0');
INSERT INTO pw_permission VALUES('0','0','17','allownewvote','basic','0');
INSERT INTO pw_permission VALUES('0','0','17','modifyvote','basic','0');
INSERT INTO pw_permission VALUES('0','0','17','allowvote','basic','0');
INSERT INTO pw_permission VALUES('0','0','17','viewvote','basic','0');
INSERT INTO pw_permission VALUES('0','0','17','allowactive','basic','0');
INSERT INTO pw_permission VALUES('0','0','17','allowreward','basic','0');
INSERT INTO pw_permission VALUES('0','0','17','allowgoods','basic','0');
INSERT INTO pw_permission VALUES('0','0','17','allowdebate','basic','0');
INSERT INTO pw_permission VALUES('0','0','17','htmlcode','basic','0');
INSERT INTO pw_permission VALUES('0','0','17','allowhidden','basic','0');
INSERT INTO pw_permission VALUES('0','0','17','allowsell','basic','0');
INSERT INTO pw_permission VALUES('0','0','17','allowencode','basic','0');
INSERT INTO pw_permission VALUES('0','0','17','anonymous','basic','0');
INSERT INTO pw_permission VALUES('0','0','17','dig','basic','0');
INSERT INTO pw_permission VALUES('0','0','17','leaveword','basic','0');
INSERT INTO pw_permission VALUES('0','0','17','allowdelatc','basic','0');
INSERT INTO pw_permission VALUES('0','0','17','atccheck','basic','0');
INSERT INTO pw_permission VALUES('0','0','17','markable','basic','0');
INSERT INTO pw_permission VALUES('0','0','17','markset','basic','a:5:{s:5:"money";a:3:{s:9:"maxcredit";s:0:"";s:9:"marklimit";N;s:6:"markdt";s:1:"0";}s:4:"rvrc";a:3:{s:9:"maxcredit";s:0:"";s:9:"marklimit";N;s:6:"markdt";s:1:"0";}s:6:"credit";a:3:{s:9:"maxcredit";s:0:"";s:9:"marklimit";N;s:6:"markdt";s:1:"0";}s:8:"currency";a:3:{s:9:"maxcredit";s:0:"";s:9:"marklimit";N;s:6:"markdt";s:1:"0";}i:1;a:3:{s:9:"maxcredit";s:0:"";s:9:"marklimit";N;s:6:"markdt";s:1:"0";}}');
INSERT INTO pw_permission VALUES('0','0','17','allowupload','basic','0');
INSERT INTO pw_permission VALUES('0','0','17','allowdownload','basic','0');
INSERT INTO pw_permission VALUES('0','0','17','allowadmincp','system','0');
INSERT INTO pw_permission VALUES('0','0','17','superright','system','1');
INSERT INTO pw_permission VALUES('0','0','17','posthide','systemforum','0');
INSERT INTO pw_permission VALUES('0','0','17','sellhide','systemforum','0');
INSERT INTO pw_permission VALUES('0','0','17','encodehide','systemforum','0');
INSERT INTO pw_permission VALUES('0','0','17','anonyhide','systemforum','0');
INSERT INTO pw_permission VALUES('0','0','17','postpers','systemforum','1');
INSERT INTO pw_permission VALUES('0','0','17','replylock','systemforum','0');
INSERT INTO pw_permission VALUES('0','0','17','viewip','systemforum','0');
INSERT INTO pw_permission VALUES('0','0','17','topped','systemforum','0');
INSERT INTO pw_permission VALUES('0','0','17','digestadmin','systemforum','0');
INSERT INTO pw_permission VALUES('0','0','17','lockadmin','systemforum','0');
INSERT INTO pw_permission VALUES('0','0','17','pushadmin','systemforum','0');
INSERT INTO pw_permission VALUES('0','0','17','coloradmin','systemforum','0');
INSERT INTO pw_permission VALUES('0','0','17','downadmin','systemforum','0');
INSERT INTO pw_permission VALUES('0','0','17','tpctype','systemforum','0');
INSERT INTO pw_permission VALUES('0','0','17','tpccheck','systemforum','0');
INSERT INTO pw_permission VALUES('0','0','17','delatc','systemforum','0');
INSERT INTO pw_permission VALUES('0','0','17','moveatc','systemforum','0');
INSERT INTO pw_permission VALUES('0','0','17','copyatc','systemforum','0');
INSERT INTO pw_permission VALUES('0','0','17','modother','systemforum','0');
INSERT INTO pw_permission VALUES('0','0','17','deltpcs','systemforum','0');
INSERT INTO pw_permission VALUES('0','0','17','viewcheck','systemforum','0');
INSERT INTO pw_permission VALUES('0','0','17','viewclose','systemforum','0');
INSERT INTO pw_permission VALUES('0','0','17','delattach','systemforum','0');
INSERT INTO pw_permission VALUES('0','0','17','shield','systemforum','0');
INSERT INTO pw_permission VALUES('0','0','17','unite','systemforum','0');
INSERT INTO pw_permission VALUES('0','0','17','remind','systemforum','0');
INSERT INTO pw_permission VALUES('0','0','17','pingcp','systemforum','0');
INSERT INTO pw_permission VALUES('0','0','17','inspect','systemforum','0');
INSERT INTO pw_permission VALUES('0','0','17','allowtime','systemforum','0');
INSERT INTO pw_permission VALUES('0','0','17','banuser','systemforum','0');
INSERT INTO pw_permission VALUES('0','0','17','bantype','systemforum','0');
INSERT INTO pw_permission VALUES('0','0','17','areapush','systemforum','1');
INSERT INTO pw_permission VALUES('0','0','3','overprint','systemforum','1');
INSERT INTO pw_permission VALUES('0','0','3','replayorder','systemforum','1');
INSERT INTO pw_permission VALUES('0','0','3','replaytopped','systemforum','1');
INSERT INTO pw_permission VALUES('0','0','3','userbinding','basic','1');
INSERT INTO pw_permission VALUES('0','0','4','overprint','systemforum','1');
INSERT INTO pw_permission VALUES('0','0','4','replayorder','systemforum','1');
INSERT INTO pw_permission VALUES('0','0','4','replaytopped','systemforum','1');
INSERT INTO pw_permission VALUES('0','0','4','userbinding','basic','1');
INSERT INTO pw_permission VALUES('0','0','17','overprint','systemforum','1');
INSERT INTO pw_permission VALUES('0','0','17','replayorder','systemforum','1');
INSERT INTO pw_permission VALUES('0','0','17','replaytopped','systemforum','1');
INSERT INTO pw_permission VALUES('0','0','17','userbinding','basic','1');
INSERT INTO pw_permission VALUES('0','0','5','overprint','systemforum','1');
INSERT INTO pw_permission VALUES('0','0','5','replayorder','systemforum','1');
INSERT INTO pw_permission VALUES('0','0','5','replaytopped','systemforum','1');
INSERT INTO pw_permission VALUES('0','0','5','userbinding','basic','1');
INSERT INTO pw_permission VALUES('0','0','8','maxsendmsg','basic','0');
INSERT INTO pw_permission VALUES('0','0','8','multiopen','basic','0');
INSERT INTO pw_permission VALUES('0','0','8','userbinding','basic','0');
INSERT INTO pw_permission VALUES('0','0','8','allowmodelid','basic','0');
INSERT INTO pw_permission VALUES('0','0','8','allowcreate','basic','1');
INSERT INTO pw_permission VALUES('0','0','9','maxsendmsg','basic','0');
INSERT INTO pw_permission VALUES('0','0','9','multiopen','basic','0');
INSERT INTO pw_permission VALUES('0','0','9','userbinding','basic','0');
INSERT INTO pw_permission VALUES('0','0','9','allowmodelid','basic','0');
INSERT INTO pw_permission VALUES('0','0','9','allowcreate','basic','5');
INSERT INTO pw_permission VALUES('0','0','11','maxsendmsg','basic','0');
INSERT INTO pw_permission VALUES('0','0','11','multiopen','basic','0');
INSERT INTO pw_permission VALUES('0','0','11','userbinding','basic','0');
INSERT INTO pw_permission VALUES('0','0','11','allowmodelid','basic','0');
INSERT INTO pw_permission VALUES('0','0','11','allowcreate','basic','5');
INSERT INTO pw_permission VALUES('0','0','10','maxsendmsg','basic','0');
INSERT INTO pw_permission VALUES('0','0','10','multiopen','basic','0');
INSERT INTO pw_permission VALUES('0','0','10','userbinding','basic','0');
INSERT INTO pw_permission VALUES('0','0','10','allowmodelid','basic','0');
INSERT INTO pw_permission VALUES('0','0','10','allowcreate','basic','5');
INSERT INTO pw_permission VALUES('0','0','12','maxsendmsg','basic','0');
INSERT INTO pw_permission VALUES('0','0','12','multiopen','basic','0');
INSERT INTO pw_permission VALUES('0','0','12','userbinding','basic','0');
INSERT INTO pw_permission VALUES('0','0','12','allowmodelid','basic','0');
INSERT INTO pw_permission VALUES('0','0','12','allowcreate','basic','10');
INSERT INTO pw_permission VALUES('0','0','13','allowcreate','basic','10');
INSERT INTO pw_permission VALUES('0','0','14','maxsendmsg','basic','0');
INSERT INTO pw_permission VALUES('0','0','14','multiopen','basic','0');
INSERT INTO pw_permission VALUES('0','0','14','userbinding','basic','0');
INSERT INTO pw_permission VALUES('0','0','14','allowmodelid','basic','0');
INSERT INTO pw_permission VALUES('0','0','14','allowcreate','basic','20');
INSERT INTO pw_permission VALUES('0','0','15','allowcreate','basic','20');
INSERT INTO pw_permission VALUES('0','0','3','tcanedit','systemforum','3,4,5');
INSERT INTO pw_permission VALUES('0','0','4','tcanedit','systemforum','3,4,5');
INSERT INTO pw_permission VALUES('0','0','5','tcanedit','systemforum','5');
INSERT INTO pw_permission VALUES('0','0','3','bansignature','systemforum','1');
INSERT INTO pw_permission VALUES('0','0','3','delalbum','systemforum','1');
INSERT INTO pw_permission VALUES('0','0','3','deldiary','systemforum','1');
INSERT INTO pw_permission VALUES('0','0','3','delweibo','systemforum','1');
INSERT INTO pw_permission VALUES('0','0','3','delactive','systemforum','1');
INSERT INTO pw_permission VALUES('0','0','3','recommendactive','systemforum','1');
INSERT INTO pw_permission VALUES('0','0','3','banuserip','systemforum','1');

DROP TABLE IF EXISTS pw_pidtmp;
CREATE TABLE pw_pidtmp (
  pid int(11) NOT NULL auto_increment,
  PRIMARY KEY (pid)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_pinglog;
CREATE TABLE pw_pinglog (
  id mediumint(8) NOT NULL auto_increment,
  fid smallint(6) NOT NULL default '0',
  tid int(10) NOT NULL default '0',
  pid int(10) NOT NULL default '0',
  uid int(10) unsigned NOT NULL default '0',
  name varchar(15) NOT NULL default '',
  point varchar(10) NOT NULL default '',
  pinger varchar(15) NOT NULL default '',
  record mediumtext NOT NULL,
  pingdate int(10) NOT NULL default '0',
  ifhide tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  PRIMARY KEY (id),
  KEY idx_tid_pid (tid,pid),
  KEY idx_pid (pid),
  KEY idx_fid_tid_pid (fid,tid,pid)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_plan;
CREATE TABLE pw_plan (
  id smallint(6) unsigned NOT NULL auto_increment,
  subject varchar(80) NOT NULL default '',
  month varchar(2) NOT NULL default '',
  week varchar(1) NOT NULL default '',
  day varchar(2) NOT NULL default '',
  hour varchar(80) NOT NULL default '',
  usetime int(10) NOT NULL default '0',
  nexttime int(10) NOT NULL default '0',
  ifsave tinyint(3) NOT NULL default '0',
  ifopen tinyint(3) NOT NULL default '0',
  filename varchar(80) NOT NULL default '',
  config text NOT NULL,
  PRIMARY KEY  (id)
) TYPE=MyISAM;
INSERT INTO pw_plan VALUES (1, '{#plan_2}', '*', '*', '20', '30', 0, 0, 0, 0, 'freeban', '');
INSERT INTO pw_plan VALUES (2, '{#plan_3}', '*', '*', '0', '*', 0, 0, 1, 0, 'birthday', '');
INSERT INTO pw_plan VALUES (3, '{#plan_4}', '*', '1', '12', '30', 0, 0, 1, 0, 'rewardmsg', '');
INSERT INTO pw_plan VALUES (4, '{#plan_5}', '15', '*', '2', '*', 0, 0, 0, 0, 'team', 'a:3:{s:10:"credittype";s:6:"credit";s:6:"credit";a:3:{i:3;s:3:"100";i:4;s:2:"60";i:5;s:2:"50";}s:6:"groups";s:5:"3,4,5";}');
INSERT INTO pw_plan VALUES (5, '{#plan_6}', '16', '*', '18', '30', 0, 0, 0, 0, 'medal', '');
INSERT INTO pw_plan VALUES (6, '{#plan_7}', '*', '*', '22', '*', 0, 0, 0, 0, 'extragroup', '');
INSERT INTO pw_plan VALUES (7,'广告到期提醒','*','*','9','*','0','0','0','1','alteradvert','');

DROP TABLE IF EXISTS pw_polls;
CREATE TABLE pw_polls (
  pollid int(10) unsigned NOT NULL auto_increment,
  tid int(10) unsigned NOT NULL default '0',
  voteopts mediumtext NOT NULL,
  modifiable tinyint(3) NOT NULL default '0',
  previewable tinyint(3) NOT NULL default '0',
  multiple tinyint(3) unsigned NOT NULL default '0',
  mostvotes smallint(6) unsigned NOT NULL default '0',
  voters mediumint(8) unsigned NOT NULL default '0',
  timelimit int(10) NOT NULL default '0',
  leastvotes int(10) unsigned NOT NULL default '0',
  regdatelimit int(10) unsigned NOT NULL default '0',
  creditlimit varchar(255) NOT NULL default '',
  postnumlimit int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (pollid),
  KEY idx_tid (tid)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_postcate;
CREATE TABLE pw_postcate (
  pcid tinyint(3) unsigned NOT NULL auto_increment,
  sign ENUM(  'basic',  'buy' ) NOT NULL default  'basic',
  name varchar(30) NOT NULL default '',
  ifable tinyint(3) NOT NULL default '1',
  vieworder tinyint(3) NOT NULL default '0',
  viewright varchar(255) NOT NULL default '',
  adminright varchar(255) NOT NULL default '',
  PRIMARY KEY  (pcid)
) TYPE=MyISAM ;
  
INSERT INTO pw_postcate (pcid, sign, name, ifable, vieworder, viewright, adminright) VALUES(1, 'buy' , '团购', 1, 0, '', '');

DROP TABLE IF EXISTS pw_posts;
CREATE TABLE pw_posts (
  pid int(10) unsigned NOT NULL auto_increment,
  fid smallint(6) unsigned NOT NULL default '0',
  tid mediumint(8) unsigned NOT NULL default '0',
  aid smallint(6) unsigned NOT NULL default  '0',
  author varchar(15) NOT NULL default '',
  authorid mediumint(8) unsigned NOT NULL default '0',
  icon tinyint(2) NOT NULL default '0',
  postdate int(10) unsigned NOT NULL default '0',
  subject varchar(100) NOT NULL default '',
  userip varchar(15) NOT NULL default '',
  ifsign tinyint(3) NOT NULL default '0',
  buy text NOT NULL,
  alterinfo varchar(50) NOT NULL default '',
  remindinfo varchar(150) NOT NULL default '',
  leaveword varchar(255) NOT NULL default '',
  ipfrom varchar(255) NOT NULL default '',
  ifconvert tinyint(3) NOT NULL default '1',
  ifwordsfb tinyint(3) NOT NULL default '1',
  ifcheck tinyint(3) NOT NULL default '0',
  content mediumtext NOT NULL,
  ifmark varchar(255) NOT NULL default '',
  ifreward tinyint(3) NOT NULL default '0',
  ifshield tinyint(3) unsigned NOT NULL default '0',
  anonymous tinyint(3) NOT NULL default '0',
  ifhide tinyint(3) NOT NULL default '0',
  PRIMARY KEY  (pid),
  KEY idx_fid (fid),
  KEY idx_postdate (postdate),
  KEY idx_tid (tid,postdate),
  KEY idx_authorid (authorid),
  KEY idx_ifcheck (ifcheck)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_postsfloor;
CREATE TABLE pw_postsfloor (
  tid int(10) NOT NULL,
  floor int(10) NOT NULL AUTO_INCREMENT,
  pid int(10) NOT NULL default '0',
  PRIMARY KEY (tid,floor)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_poststopped;
CREATE TABLE pw_poststopped (
  fid smallint(6) unsigned NOT NULL,
  tid int(10) unsigned NOT NULL,
  pid int(10) unsigned NOT NULL,
  floor int(10) unsigned NOT NULL ,
  uptime int(10) unsigned NOT NULL default '0',
  overtime int(10) NOT NULL default '0',
  PRIMARY KEY (fid,tid,pid)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS pw_privacy;
CREATE TABLE pw_privacy (
  uid mediumint(8) NOT NULL,
  ptype varchar(15) NOT NULL,
  pkey varchar(15) NOT NULL,
  value tinyint(3) NOT NULL,
  PRIMARY KEY (uid,ptype,pkey)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS pw_proclock;
CREATE TABLE pw_proclock (
  uid mediumint(8) unsigned NOT NULL,
  action varchar(50) NOT NULL,
  time int(10) NOT NULL default '0',
  PRIMARY KEY  (uid,action)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_pushdata;
CREATE TABLE pw_pushdata (
  id int(10) unsigned NOT NULL auto_increment,
  invokepieceid smallint(6) unsigned NOT NULL default '0',
  fid smallint(6) unsigned NOT NULL default '0',
  editor varchar(15) NOT NULL default '',
  starttime int(10) unsigned NOT NULL default '0',
  endtime int(10) unsigned NOT NULL default '0',
  vieworder tinyint(1) unsigned NOT NULL default '0',
  titlecss varchar(255) NOT NULL default '',
  pushtime int(10) unsigned NOT NULL default '0',
  ifverify tinyint(3) unsigned NOT NULL default '0',
  data text NOT NULL,
  ifbusiness tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY (id),
  KEY idx_ifverify_invokepieceid_vieworder_starttime (ifverify , invokepieceid , vieworder , starttime)
) ENGINE = MyISAM;

DROP TABLE IF EXISTS pw_pushpic;
CREATE TABLE pw_pushpic (
  id int(10) unsigned NOT NULL auto_increment,
  path varchar(255) NOT NULL default '',
  invokepieceid smallint(6) unsigned NOT NULL default '0',
  creator varchar(20) NOT NULL default '',
  createtime int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (id)
) ENGINE=MyISAM;
 

DROP TABLE IF EXISTS pw_rate;
CREATE  TABLE pw_rate (
  objectid int(10) NOT NULL default '0',
  optionid smallint(6) NOT NULL default '0',
  typeid smallint(6) NOT NULL default '0',
  uid int(10) unsigned NOT NULL default '0',
  created_at int(10) unsigned NOT NULL default '0',
  ip varchar(15) NOT NULL default '',
  KEY idx_typeid_objectid_uid (typeid,objectid,uid),
  KEY idx_typeid_createdat_optionid_objectid (typeid,created_at,optionid,objectid),
  KEY idx_uid_createdat (uid,created_at)
) ENGINE = MyISAM;

DROP TABLE IF EXISTS pw_rateconfig;
CREATE  TABLE pw_rateconfig (
  id int(10) unsigned NOT NULL AUTO_INCREMENT,
  title varchar(12) NOT NULL default '',
  icon varchar(75) NOT NULL default '',
  isopen tinyint(1) NOT NULL DEFAULT '1',
  isdefault tinyint(1) NOT NULL DEFAULT '0',
  typeid tinyint(1) NOT NULL DEFAULT '0',
  creditset tinyint(1) NOT NULL DEFAULT '0',
  voternum tinyint(1) NOT NULL DEFAULT '0',
  authornum tinyint(1) NOT NULL DEFAULT '0',
  creator varchar(20) NULL default '',
  created_at int(10) NULL default '0',
  updater varchar(20) NULL default '',
  update_at int(10) NULL default '0',
  PRIMARY KEY (id),
  KEY idx_typeid (typeid)
) ENGINE = MyISAM;
INSERT INTO pw_rateconfig (id, title, icon, isopen, isdefault, typeid, creditset, voternum, authornum, creator, created_at, updater, update_at) VALUES(1, '精彩', '01.gif', 1, 1, 1, -1, 1, 1, 'system', 1251030975, 'admin', 1252394328);
INSERT INTO pw_rateconfig (id, title, icon, isopen, isdefault, typeid, creditset, voternum, authornum, creator, created_at, updater, update_at) VALUES(2, '感动', '02.gif', 1, 1, 1, -1, 1, 1, 'system', 1251030975, 'admin', 1252394328);
INSERT INTO pw_rateconfig (id, title, icon, isopen, isdefault, typeid, creditset, voternum, authornum, creator, created_at, updater, update_at) VALUES(3, '搞笑', '03.gif', 1, 1, 1, -1, 1, 1, 'system', 1251030975, 'admin', 1252394328);
INSERT INTO pw_rateconfig (id, title, icon, isopen, isdefault, typeid, creditset, voternum, authornum, creator, created_at, updater, update_at) VALUES(4, '开心', '04.gif', 1, 1, 1, -1, 1, 1, 'system', 1251030975, 'admin', 1252394328);
INSERT INTO pw_rateconfig (id, title, icon, isopen, isdefault, typeid, creditset, voternum, authornum, creator, created_at, updater, update_at) VALUES(5, '愤怒', '05.gif', 1, 1, 1, -1, 1, 1, 'system', 1251030975, 'admin', 1252394328);
INSERT INTO pw_rateconfig (id, title, icon, isopen, isdefault, typeid, creditset, voternum, authornum, creator, created_at, updater, update_at) VALUES(6, '无聊', '06.gif', 1, 1, 1, -1, 1, 0, 'system', 1251030975, 'admin', 1252394328);
INSERT INTO pw_rateconfig (id, title, icon, isopen, isdefault, typeid, creditset, voternum, authornum, creator, created_at, updater, update_at) VALUES(7, '灌水', '07.gif', 1, 1, 1, -1, 1, -2, 'system', 1251030975, 'admin', 1252394328);
INSERT INTO pw_rateconfig (id, title, icon, isopen, isdefault, typeid, creditset, voternum, authornum, creator, created_at, updater, update_at) VALUES(8, '精彩', '01.gif', 1, 1, 2, -1, 1, 1, 'system', 1251030975, 'admin', 1252394398);
INSERT INTO pw_rateconfig (id, title, icon, isopen, isdefault, typeid, creditset, voternum, authornum, creator, created_at, updater, update_at) VALUES(9, '感动', '02.gif', 1, 1, 2, -1, 1, 1, 'system', 1251030975, 'admin', 1252394398);
INSERT INTO pw_rateconfig (id, title, icon, isopen, isdefault, typeid, creditset, voternum, authornum, creator, created_at, updater, update_at) VALUES(10, '搞笑', '03.gif', 1, 1, 2, -1, 1, 1, 'system', 1251030975, 'admin', 1252394398);
INSERT INTO pw_rateconfig (id, title, icon, isopen, isdefault, typeid, creditset, voternum, authornum, creator, created_at, updater, update_at) VALUES(11, '开心', '04.gif', 1, 1, 2, -1, 1, 1, 'system', 1251030975, 'admin', 1252394398);
INSERT INTO pw_rateconfig (id, title, icon, isopen, isdefault, typeid, creditset, voternum, authornum, creator, created_at, updater, update_at) VALUES(12, '愤怒', '05.gif', 1, 1, 2, -1, 1, 1, 'system', 1251030975, 'admin', 1252394398);
INSERT INTO pw_rateconfig (id, title, icon, isopen, isdefault, typeid, creditset, voternum, authornum, creator, created_at, updater, update_at) VALUES(13, '无聊', '06.gif', 1, 1, 2, -1, 1, 0, 'system', 1251030975, 'admin', 1252394398);
INSERT INTO pw_rateconfig (id, title, icon, isopen, isdefault, typeid, creditset, voternum, authornum, creator, created_at, updater, update_at) VALUES(14, '灌水', '07.gif', 1, 1, 2, -1, 1, -1, 'system', 1251030975, 'admin', 1252394398);
INSERT INTO pw_rateconfig (id, title, icon, isopen, isdefault, typeid, creditset, voternum, authornum, creator, created_at, updater, update_at) VALUES(15, '精彩', '01.gif', 1, 1, 3, -1, 1, 1, 'system', 1251030975, 'admin', 1252394438);
INSERT INTO pw_rateconfig (id, title, icon, isopen, isdefault, typeid, creditset, voternum, authornum, creator, created_at, updater, update_at) VALUES(16, '唯美', '02.gif', 1, 1, 3, -1, 1, 1, 'system', 1251030975, 'admin', 1252394438);
INSERT INTO pw_rateconfig (id, title, icon, isopen, isdefault, typeid, creditset, voternum, authornum, creator, created_at, updater, update_at) VALUES(17, '有趣', '03.gif', 1, 1, 3, -1, 1, 1, 'system', 1251030975, 'admin', 1252394438);
INSERT INTO pw_rateconfig (id, title, icon, isopen, isdefault, typeid, creditset, voternum, authornum, creator, created_at, updater, update_at) VALUES(18, '震惊', '04.gif', 1, 1, 3, -1, 1, 1, 'system', 1251030975, 'admin', 1252394438);
INSERT INTO pw_rateconfig (id, title, icon, isopen, isdefault, typeid, creditset, voternum, authornum, creator, created_at, updater, update_at) VALUES(19, '原创', '05.gif', 1, 1, 3, -1, 1, 1, 'system', 1251030975, 'admin', 1252394438);
INSERT INTO pw_rateconfig (id, title, icon, isopen, isdefault, typeid, creditset, voternum, authornum, creator, created_at, updater, update_at) VALUES(20, '专业', '06.gif', 1, 1, 3, -1, 1, 1, 'system', 1251030975, 'admin', 1252394438);
INSERT INTO pw_rateconfig (id, title, icon, isopen, isdefault, typeid, creditset, voternum, authornum, creator, created_at, updater, update_at) VALUES(21, '无聊', '07.gif', 1, 1, 3, -1, 1, 0, 'system', 1251030975, 'admin', 1252394438);
INSERT INTO pw_rateconfig (id, title, icon, isopen, isdefault, typeid, creditset, voternum, authornum, creator, created_at, updater, update_at) VALUES(22, '劣质', '08.gif', 1, 1, 3, -1, 1, -2, 'system', 1251030975, 'admin', 1252394438);

DROP TABLE IF EXISTS pw_rateresult;
CREATE  TABLE pw_rateresult (
  id int(10) unsigned NOT NULL AUTO_INCREMENT,
  objectid int(10) unsigned NOT NULL default '0',
  optionid smallint(6) unsigned NOT NULL default '0',
  typeid TINYINT(3) NOT NULL default '0',
  num int(10) unsigned NOT NULL default '0',
  PRIMARY KEY (id),
  KEY idx_optionid_objectid (optionid,objectid),
  KEY idx_typeid_objectid (typeid,objectid)
) ENGINE = MyISAM;

DROP TABLE IF EXISTS pw_recycle;
CREATE TABLE pw_recycle (
  pid int(10) unsigned NOT NULL default '0',
  tid int(10) unsigned NOT NULL default '0',
  fid smallint(6) unsigned NOT NULL default '0',
  deltime int(10) unsigned NOT NULL default '0',
  admin varchar(15) NOT NULL default '',
  PRIMARY KEY  (pid,tid),
  KEY idx_tid (tid),
  KEY idx_fid_pid (fid,pid)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_report;
CREATE TABLE pw_report (
  id int(10) unsigned NOT NULL auto_increment,
  tid int(10) unsigned NOT NULL default '0',
  pid int(10) unsigned NOT NULL default '0',
  uid int(10) unsigned NOT NULL default '0',
  type varchar(50) NOT NULL DEFAULT '',
  state tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  reason varchar(255) NOT NULL default '',
  PRIMARY KEY  (id),
  KEY idx_type (type)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_reward;
CREATE TABLE pw_reward (
  tid int(10) unsigned NOT NULL,
  cbtype varchar(20) NOT NULL default '',
  catype varchar(20) NOT NULL default '',
  cbval int(10) NOT NULL default '0',
  caval int(10) NOT NULL default '0',
  timelimit int(10) NOT NULL default '0',
  author varchar(30) NOT NULL default '',
  pid int(10) NOT NULL default '0',
  PRIMARY KEY  (tid),
  KEY idx_timelimit (timelimit)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_schcache;
CREATE TABLE pw_schcache (
  sid mediumint(8) unsigned NOT NULL auto_increment,
  sorderby varchar(13) NOT NULL default '',
  schline varchar(32) NOT NULL default '',
  schtime int(10) unsigned NOT NULL default '0',
  total mediumint(8) unsigned NOT NULL default '0',
  schedid text NOT NULL,
  PRIMARY KEY  (sid),
  KEY idx_schline (schline)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_searchadvert;
CREATE TABLE pw_searchadvert(
   `id` mediumint(8) unsigned not null auto_increment,
   `keyword` varchar(32) not null default '',
   `code` text not null,
   `starttime` int(10) unsigned not null default '0',
   `endtime` int(10) unsigned not null default '0',
   `ifshow` tinyint(3) not null default '0',
   `orderby` tinyint(3) not null default '0',
   `config` text not null,
    primary key(id),
    KEY `idx_keyword` (`keyword`)
)ENGINE=MyISAM;

DROP TABLE IF EXISTS pw_searchforum;
CREATE TABLE IF NOT EXISTS pw_searchforum (
  `id` smallint(6) unsigned not null auto_increment,
  `fid` smallint(6) unsigned not null default '0',
  `vieworder` smallint(6) not null default '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS pw_searchstatistic;
CREATE TABLE IF NOT EXISTS `pw_searchstatistic` (
  `id` int(10) unsigned not null auto_increment,
  `keyword` varchar(32) not null default '',
  `num` mediumint(8) not null default '0',
  `created_time` int(10) not null default '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS pw_setform;
CREATE TABLE pw_setform (
  id int(10) NOT NULL auto_increment,
  name varchar(30) NOT NULL default '',
  ifopen tinyint(3) NOT NULL default '0',
  value text NOT NULL,
  PRIMARY KEY  (id)
) TYPE=MyISAM;
INSERT INTO pw_setform VALUES('1','{#setform_1}','1','{#setfrom_1_inro}');

DROP TABLE IF EXISTS pw_sharelinks;
CREATE TABLE pw_sharelinks (
  sid smallint(6) unsigned NOT NULL auto_increment,
  threadorder tinyint(3) NOT NULL default '0',
  name varchar(100) NOT NULL default '',
  url varchar(100) NOT NULL default '',
  descrip varchar(200) NOT NULL default '0',
  logo varchar(100) NOT NULL default '',
  ifcheck tinyint(3) NOT NULL default '0',
  username varchar(20) NOT NULL default '',
  PRIMARY KEY  (sid)
) TYPE=MyISAM;
INSERT INTO pw_sharelinks (threadorder ,name ,url ,descrip ,logo ,ifcheck) VALUES ('0', 'phpwind Board', 'http://www.phpwind.net', '{#sharelinks}', 'logo.gif', '1');

DROP TABLE IF EXISTS pw_sharelinksrelation;
CREATE TABLE `pw_sharelinksrelation` (
  `sid` mediumint(8) unsigned not null default '0',
  `stid` mediumint(8) unsigned not null default '0',
  KEY `idx_sid` (`sid`),
  UNIQUE KEY `idx_stid_sid` (`stid`,`sid`)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS `pw_sharelinkstype`;
CREATE TABLE IF NOT EXISTS `pw_sharelinkstype` (
  `stid` smallint(6) unsigned not null auto_increment,
  `name` varchar(30) not null default '',
  `vieworder` smallint(6) unsigned not null default '0',
  `ifable` tinyint(3) unsigned not null default '0',
  PRIMARY KEY (`stid`)
) ENGINE=MyISAM;

INSERT INTO `pw_sharelinkstype` (`stid`, `name`, `vieworder`, `ifable`) VALUES (1, '门户', 1, 1),(2, '论坛', 2, 1);

DROP TABLE IF EXISTS pw_singleright;
CREATE TABLE pw_singleright (
  uid int(10) unsigned NOT NULL default '0',
  visit varchar(80) NOT NULL default '',
  post varchar(80) NOT NULL default '',
  reply varchar(80) NOT NULL default '',
  PRIMARY KEY  (uid)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_smiles;
CREATE TABLE pw_smiles (
  id smallint(6) unsigned NOT NULL auto_increment,
  path varchar(255) NOT NULL default '',
  name varchar(255) NOT NULL default '',
  descipt varchar(100) NOT NULL default '',
  vieworder tinyint(2) NOT NULL default '0',
  type smallint(6) NOT NULL default '0',
  PRIMARY KEY  (id)
) TYPE=MyISAM;
INSERT INTO pw_smiles (path,name,vieworder,type) VALUES ('default','{#smile}','1','0');
INSERT INTO pw_smiles (path,vieworder,type) VALUES ('1.gif','0','1');
INSERT INTO pw_smiles (path,vieworder,type) VALUES ('2.gif','0','1');
INSERT INTO pw_smiles (path,vieworder,type) VALUES ('3.gif','0','1');
INSERT INTO pw_smiles (path,vieworder,type) VALUES ('4.gif','0','1');
INSERT INTO pw_smiles (path,vieworder,type) VALUES ('5.gif','0','1');
INSERT INTO pw_smiles (path,vieworder,type) VALUES ('6.gif','0','1');
INSERT INTO pw_smiles (path,vieworder,type) VALUES ('7.gif','0','1');
INSERT INTO pw_smiles (path,vieworder,type) VALUES ('8.gif','0','1');
INSERT INTO pw_smiles (path,vieworder,type) VALUES ('9.gif','0','1');
INSERT INTO pw_smiles (path,vieworder,type) VALUES ('10.gif','0','1');
INSERT INTO pw_smiles (path,vieworder,type) VALUES ('11.gif','0','1');
INSERT INTO pw_smiles (path,vieworder,type) VALUES ('12.gif','0','1');
INSERT INTO pw_smiles (path,vieworder,type) VALUES ('13.gif','0','1');
INSERT INTO pw_smiles (path,vieworder,type) VALUES ('14.gif','0','1');

DROP TABLE IF EXISTS pw_space;
CREATE TABLE pw_space (
  uid int(10) unsigned NOT NULL,
  name varchar(80) NOT NULL default '',
  domain varchar(20) NOT NULL default '',
  spacetype tinyint(1) NOT NULL default '0',
  banner varchar(50) NOT NULL default '',
  skin varchar(15) NOT NULL default '',
  visits int(10) unsigned NOT NULL default '0',
  tovisits int(10) unsigned NOT NULL default '0',
  visitors varchar(255) NOT NULL default '',
  tovisitors varchar(255) NOT NULL default '',
  layout text NOT NULL,
  modelset text NOT NULL,
  descript VARCHAR(255) NOT NULL default '',
  PRIMARY KEY (uid)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS pw_sqlcv;
CREATE TABLE pw_sqlcv (
  id int(10) NOT NULL auto_increment,
  var varchar(20) NOT NULL default '',
  PRIMARY KEY  (id)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_stopic;
CREATE TABLE pw_stopic (
  stopic_id int(10) unsigned NOT NULL auto_increment,
  title varchar(50) NOT NULL default '',
  category_id int(10) unsigned NOT NULL default '0',
  bg_id int(10) NOT NULL default '0',
  copy_from int(10) unsigned NOT NULL default '0',
  layout varchar(20) NOT NULL default '',
  create_date int(10) unsigned NOT NULL default '0',
  start_date int(10) unsigned NOT NULL default '0',
  end_date int(10) unsigned NOT NULL default '0',
  used_count mediumint(8) unsigned NOT NULL default '0',
  view_count int(10) unsigned NOT NULL default '0',
  banner_url varchar(100) NOT NULL default '',
  seo_keyword varchar(255) NOT NULL default '',
  seo_desc varchar(255) NOT NULL default '',
  block_config text NOT NULL,
  layout_config text NOT NULL,
  nav_config text NOT NULL,
  file_name varchar(30) NOT NULL default '',
  PRIMARY KEY  (stopic_id)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_stopicblock;
CREATE TABLE pw_stopicblock (
  block_id smallint(6) unsigned NOT NULL auto_increment,
  name varchar(50) NOT NULL default '',
  tagcode text NOT NULL,
  begin text NOT NULL,
  loops text NOT NULL,
  end text NOT NULL,
  config varchar(255) NOT NULL default '',
  replacetag varchar(255) NOT NULL default '',
  PRIMARY KEY  (block_id)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_stopiccategory;
CREATE TABLE pw_stopiccategory (
  id smallint(6) NOT NULL AUTO_INCREMENT,
  title varchar(45) NOT NULL default '',
  status tinyint(3) NOT NULL default 0,
  num smallint(6) NOT NULL default 0,
  creator varchar(20) NULL default '',
  createtime int(10) NOT NULL default 0,
  PRIMARY KEY (id)
) TYPE=MyISAM;
INSERT INTO pw_stopiccategory (id, title, status, num, creator, createtime) VALUES(1, '房产', 1, 0, 'phpwind', 1250759842);
INSERT INTO pw_stopiccategory (id, title, status, num, creator, createtime) VALUES(2, '汽车', 1, 0, 'phpwind', 1250759842);
INSERT INTO pw_stopiccategory (id, title, status, num, creator, createtime) VALUES(3, '婚庆', 1, 0, 'phpwind', 1250759842);
INSERT INTO pw_stopiccategory (id, title, status, num, creator, createtime) VALUES(4, '母婴', 1, 0, 'phpwind', 1250759842);
INSERT INTO pw_stopiccategory (id, title, status, num, creator, createtime) VALUES(5, '团购', 1, 0, 'phpwind', 1250759842);

DROP TABLE IF EXISTS pw_stopicpictures;
CREATE  TABLE pw_stopicpictures (
  id int(10) NOT NULL AUTO_INCREMENT,
  categoryid smallint(6) NOT NULL default 0,
  title varchar(45) NOT NULL default '',
  path varchar(255) NOT NULL default '',
  num smallint(6) NOT NULL default '0',
  creator varchar(20) NULL default '',
  createtime int(10) NOT NULL default '0',
  PRIMARY KEY (id)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_stopicunit;
CREATE TABLE pw_stopicunit (
  unit_id int(10) unsigned NOT NULL auto_increment,
  stopic_id int(10) unsigned NOT NULL default '0',
  html_id varchar(50) NOT NULL default '',
  block_id smallint(6) unsigned NOT NULL default '0',
  title varchar(255) NOT NULL default '',
  data text NOT NULL,
  PRIMARY KEY  (unit_id),
  UNIQUE KEY idx_stopicid_htmlid (stopic_id,html_id)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_styles;
CREATE TABLE pw_styles (
  sid smallint(6) unsigned NOT NULL auto_increment,
  uid int(10) unsigned NOT NULL default '0',
  name varchar(50) NOT NULL default '',
  customname varchar(50) NOT NULL default '',
  createtime INT(10) NOT NULL default '0',
  lastmodify INT(10) NOT NULL default '0',
  ifopen tinyint(1) NOT NULL default '0',
  stylepath varchar(50) NOT NULL default '',
  tplpath varchar(50) NOT NULL default '',
  yeyestyle varchar(3) NOT NULL default '',
  bgcolor varchar(100) NOT NULL default '',
  linkcolor varchar(7) NOT NULL default '',
  tablecolor varchar(7) NOT NULL default '',
  tdcolor varchar(7) NOT NULL default '',
  tablewidth varchar(7) NOT NULL default '',
  mtablewidth varchar(7) NOT NULL default '',
  headcolor varchar(100) NOT NULL default '',
  headborder varchar(7) NOT NULL default '',
  headfontone varchar(7) NOT NULL default '',
  headfonttwo varchar(7) NOT NULL default '',
  cbgcolor varchar(100) NOT NULL default '',
  cbgborder varchar(7) NOT NULL default '',
  cbgfont varchar(7) NOT NULL default '',
  forumcolorone varchar(7) NOT NULL default '',
  forumcolortwo varchar(7) NOT NULL default '',
  extcss TEXT NOT NULL,
  PRIMARY KEY  (sid),
  KEY idx_uid (uid)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_tagdata;
CREATE TABLE pw_tagdata (
  tagid mediumint(8) NOT NULL default '0',
  tid int(10) unsigned NOT NULL default '0',
  KEY idx_tagid (tagid),
  KEY idx_tid (tid)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_tags;
CREATE TABLE pw_tags (
  tagid mediumint(8) unsigned NOT NULL auto_increment,
  tagname varchar(15) NOT NULL default '',
  num mediumint(8) NOT NULL default '0',
  ifhot tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (tagid),
  KEY idx_ifhot_num (ifhot,num),
  KEY idx_tagname (tagname)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_task;
create table pw_task(
  id int(10) unsigned not null auto_increment,
  name char(20) default '',
  task  varchar(255) default '',
  count int(10) unsigned not null default '0',
  last int(10) unsigned not null default '0',
  next  int(10) unsigned not null default '0',
  ctime int(10) unsigned not null default '0',
  primary key (id),
  key idx_next (next)
)ENGINE=MyISAM;
INSERT  INTO pw_task SET  id='1', name='广告到期提醒', task='alteradver', count='1', last='1258970602', next='1258970612', ctime='1258970602';

DROP TABLE IF EXISTS pw_threads;
CREATE TABLE pw_threads (
  tid int(10) unsigned NOT NULL auto_increment,
  fid smallint(6) unsigned NOT NULL default '0',
  icon tinyint(3) NOT NULL default '0',
  titlefont varchar(15) NOT NULL default '',
  author varchar(15) NOT NULL default '',
  authorid int(10) unsigned NOT NULL default '0',
  subject varchar(100) NOT NULL default '',
  toolinfo varchar(16) NOT NULL default '',
  toolfield varchar(21) NOT NULL default '',
  ifcheck tinyint(3) NOT NULL default '0',
  type smallint(6) NOT NULL default '0',
  postdate int(10) unsigned NOT NULL default '0',
  lastpost int(10) unsigned NOT NULL default '0',
  lastposter varchar(15) NOT NULL default '',
  hits int(10) unsigned NOT NULL default '0',
  replies int(10) unsigned NOT NULL default '0',
  favors INT( 10 ) NOT NULL DEFAULT '0',
  modelid smallint(6) unsigned NOT NULL default '0',
  shares mediumint(8) unsigned NOT NULL default '0',
  topped smallint(6) NOT NULL default '0',
  topreplays smallint(6) NOT NULL default '0',
  locked tinyint(3) NOT NULL default '0',
  digest tinyint(3) NOT NULL default '0',
  special tinyint(3) NOT NULL default '0',
  state tinyint(3) NOT NULL default '0',
  ifupload tinyint(3) NOT NULL default '0',
  ifmail tinyint(3) NOT NULL default '0',
  ifmark smallint(6) NOT NULL default '0',
  ifshield tinyint(3) NOT NULL default '0',
  anonymous tinyint(3) NOT NULL default '0',
  dig int(10) NOT NULL default '0',
  fight int(10) NOT NULL default '0',
  ptable tinyint(3) NOT NULL default '0',
  ifmagic tinyint(3) NOT NULL default '0',
  ifhide tinyint(3) NOT NULL default '0',
  inspect varchar(30) NOT NULL default '',
  tpcstatus int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (tid),
  KEY idx_authorid (authorid),
  KEY idx_postdate (postdate),
  KEY idx_digest (digest),
  KEY idx_fid_type_ifcheck (fid,type,ifcheck),
  KEY idx_special (special),
  KEY idx_fid_ifcheck_topped_lastpost (fid,ifcheck,topped,lastpost)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_threads_img;
CREATE TABLE `pw_threads_img` (
  `tid` int(10) unsigned NOT NULL,
  `fid` mediumint(8) unsigned NOT NULL,
  PRIMARY KEY  (`tid`),
  KEY `fid` (`fid`,`tid`)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_tmsgs;
CREATE TABLE pw_tmsgs (
  tid int(10) unsigned NOT NULL default '0',
  aid SMALLINT(6) UNSIGNED NOT NULL DEFAULT  '0',
  userip varchar(15) NOT NULL default '',
  ifsign tinyint(3) NOT NULL default '0',
  buy text NOT NULL,
  ipfrom varchar(255) NOT NULL default '',
  alterinfo varchar(50) NOT NULL default '',
  remindinfo varchar(150) NOT NULL default '',
  tags varchar(100) NOT NULL default '',
  ifconvert tinyint(3) NOT NULL default '1',
  ifwordsfb tinyint(3) NOT NULL default '1',
  content mediumtext NOT NULL,
  form varchar(30) NOT NULL default '',
  ifmark varchar(255) NOT NULL default '',
  c_from varchar(30) NOT NULL default '',
  magic varchar(50) NOT NULL default '',
  overprint smallint(6) not null default 0,
  PRIMARY KEY  (tid)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_toollog;
CREATE TABLE pw_toollog (
  id int(10) unsigned NOT NULL auto_increment,
  type varchar(10) NOT NULL default '',
  nums smallint(6) NOT NULL default '0',
  money smallint(6) NOT NULL default '0',
  descrip varchar(255) NOT NULL default '',
  uid int(10) unsigned NOT NULL default '0',
  username varchar(15) NOT NULL default '',
  ip varchar(15) NOT NULL default '',
  time int(10) NOT NULL default '0',
  filename varchar(20) NOT NULL default '',
  touid int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (id),
  KEY idx_uid (uid),
  KEY idx_touid (touid),
  KEY idx_type (type)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_tools;
CREATE TABLE pw_tools (
  id smallint(6) NOT NULL auto_increment,
  name varchar(20) NOT NULL default '',
  filename varchar(20) NOT NULL default '',
  descrip varchar(255) NOT NULL default '',
  vieworder tinyint(3) NOT NULL default '0',
  logo varchar(100) NOT NULL default '',
  state tinyint(3) NOT NULL default '0',
  price varchar(255) NOT NULL default '',
  creditype varchar(10) NOT NULL default '',
  rmb decimal(8,2) NOT NULL,
  type tinyint(3) NOT NULL default '0',
  stock smallint(6) NOT NULL default '0',
  conditions text NOT NULL ,
  PRIMARY KEY  (id)
) TYPE=MyISAM;
INSERT INTO pw_tools VALUES('1','{#tool_1}','reputation','{#tool_1_inro}','1','1.gif','1','100','money','10.00','2','100','a:1:{s:6:"credit";a:6:{s:7:"postnum";s:1:"0";s:7:"digests";s:1:"0";s:4:"rvrc";s:1:"0";s:5:"money";s:1:"0";s:6:"credit";s:1:"0";i:1;s:1:"0";}}');
INSERT INTO pw_tools VALUES('2','{#tool_2}','credit','{#tool_2_inro}','2','2.gif','1','100','money','10.00','2','100','a:1:{s:6:"credit";a:6:{s:7:"postnum";s:1:"0";s:7:"digests";s:1:"0";s:4:"rvrc";s:1:"0";s:5:"money";s:1:"0";s:6:"credit";s:1:"0";i:1;s:1:"0";}}');
INSERT INTO pw_tools VALUES('3','{#tool_3}','colortitle','{#tool_3_inro}','3','3.gif','1','200','money','20.00','1','100','a:1:{s:6:"credit";a:6:{s:7:"postnum";s:1:"0";s:7:"digests";s:1:"0";s:4:"rvrc";s:1:"0";s:5:"money";s:1:"0";s:6:"credit";s:1:"0";i:1;s:1:"0";}}');
INSERT INTO pw_tools VALUES('4','{#tool_4}','top','{#tool_4_inro}','4','4.gif','1','200','money','20.00','1','100','a:1:{s:6:"credit";a:6:{s:7:"postnum";s:1:"0";s:7:"digests";s:1:"0";s:4:"rvrc";s:1:"0";s:5:"money";s:1:"0";s:6:"credit";s:1:"0";i:1;s:1:"0";}}');
INSERT INTO pw_tools VALUES('5','{#tool_5}','top2','{#tool_5_inro}','5','5.gif','1','500','money','50.00','1','100','a:1:{s:6:"credit";a:6:{s:7:"postnum";s:1:"0";s:7:"digests";s:1:"0";s:4:"rvrc";s:1:"0";s:5:"money";s:1:"0";s:6:"credit";s:1:"0";i:1;s:1:"0";}}');
INSERT INTO pw_tools VALUES('6','{#tool_6}','top3','{#tool_6_inro}','6','6.gif','1','1000','money','100.00','1','100','a:1:{s:6:"credit";a:6:{s:7:"postnum";s:1:"0";s:7:"digests";s:1:"0";s:4:"rvrc";s:1:"0";s:5:"money";s:1:"0";s:6:"credit";s:1:"0";i:1;s:1:"0";}}');
INSERT INTO pw_tools VALUES('7','{#tool_7}','upread','{#tool_7_inro}','7','7.gif','1','100','money','10.00','1','100','a:1:{s:6:"credit";a:6:{s:7:"postnum";s:1:"0";s:7:"digests";s:1:"0";s:4:"rvrc";s:1:"0";s:5:"money";s:1:"0";s:6:"credit";s:1:"0";i:1;s:1:"0";}}');
INSERT INTO pw_tools VALUES('8','{#tool_8}','changename','{#tool_8_inro}','8','8.gif','1','1000','money','100.00','2','100','a:1:{s:6:"credit";a:6:{s:7:"postnum";s:1:"0";s:7:"digests";s:1:"0";s:4:"rvrc";s:1:"0";s:5:"money";s:1:"0";s:6:"credit";s:1:"0";i:1;s:1:"0";}}');
INSERT INTO pw_tools VALUES('9','{#tool_9}','digest','{#tool_9_inro}','9','9.gif','0','100','currency','0.00','1','100','a:1:{s:6:"credit";a:7:{s:7:"postnum";s:1:"0";s:7:"digests";s:1:"0";s:4:"rvrc";s:1:"0";s:5:"money";s:1:"0";s:6:"credit";s:1:"0";i:1;s:1:"0";i:2;s:1:"0";}}');
INSERT INTO pw_tools VALUES('10','{#tool_10}','digest2','{#tool_10_inro}','10','10.gif','0','200','currency','0.00','1','100','a:1:{s:6:"credit";a:7:{s:7:"postnum";s:1:"0";s:7:"digests";s:1:"0";s:4:"rvrc";s:1:"0";s:5:"money";s:1:"0";s:6:"credit";s:1:"0";i:1;s:1:"0";i:2;s:1:"0";}}');
INSERT INTO pw_tools VALUES('11','{#tool_11}','lock','{#tool_11_inro}','11','11.gif','0','100','currency','0.00','1','100','a:1:{s:6:"credit";a:7:{s:7:"postnum";s:1:"0";s:7:"digests";s:1:"0";s:4:"rvrc";s:1:"0";s:5:"money";s:1:"0";s:6:"credit";s:1:"0";i:1;s:1:"0";i:2;s:1:"0";}}');
INSERT INTO pw_tools VALUES('12','{#tool_12}','unlock','{#tool_12_inro}','12','12.gif','0','100','currency','0.00','1','100','a:1:{s:6:"credit";a:7:{s:7:"postnum";s:1:"0";s:7:"digests";s:1:"0";s:4:"rvrc";s:1:"0";s:5:"money";s:1:"0";s:6:"credit";s:1:"0";i:1;s:1:"0";i:2;s:1:"0";}}');
INSERT INTO pw_tools VALUES('13','{#tool_13}','flower','{#tool_13_inro}','13','13.gif','1','10','money','1.00','1','1000','a:1:{s:6:"credit";a:6:{s:7:"postnum";s:1:"0";s:7:"digests";s:1:"0";s:4:"rvrc";s:1:"0";s:5:"money";s:1:"0";s:6:"credit";s:1:"0";i:1;s:1:"0";}}');
INSERT INTO pw_tools VALUES('14','{#tool_14}','egg','{#tool_14_inro}','14','14.gif','1','10','money','1.00','1','1000','a:1:{s:6:"credit";a:6:{s:7:"postnum";s:1:"0";s:7:"digests";s:1:"0";s:4:"rvrc";s:1:"0";s:5:"money";s:1:"0";s:6:"credit";s:1:"0";i:1;s:1:"0";}}');
INSERT INTO pw_tools VALUES('15','{#tool_15}','luck','{#tool_15_inro}','15','15.gif','0','10','currency','0.00','2','100','a:2:{s:4:"luck";a:3:{s:6:"range1";s:4:"-100";s:6:"range2";s:3:"100";s:8:"lucktype";s:8:"currency";}s:6:"credit";a:6:{s:7:"postnum";s:1:"0";s:7:"digests";s:1:"0";s:4:"rvrc";s:1:"0";s:5:"money";s:1:"0";s:6:"credit";s:1:"0";i:1;s:1:"0";}}');
INSERT INTO pw_tools VALUES('16','{#tool_16}','birth','{#tool_16_inro}','16','16.gif','1','50','money','5.00','2','100','a:1:{s:6:"credit";a:6:{s:7:"postnum";s:1:"0";s:7:"digests";s:1:"0";s:4:"rvrc";s:1:"0";s:5:"money";s:1:"0";s:6:"credit";s:1:"0";i:1;s:1:"0";}}');
INSERT INTO pw_tools VALUES('17','{#tool_17}','backdown','{#tool_17_inro}','17','17.gif','0','10','currency','0.00','1','100','a:1:{s:6:"credit";a:7:{s:7:"postnum";s:1:"0";s:7:"digests";s:1:"0";s:4:"rvrc";s:1:"0";s:5:"money";s:1:"0";s:6:"credit";s:1:"0";i:1;s:1:"0";i:2;s:1:"0";}}');
INSERT INTO pw_tools VALUES('18','{#tool_18}','pig','{#tool_18_inro}','18','18.gif','1','200','money','20.00','2','100','a:1:{s:6:"credit";a:6:{s:7:"postnum";s:1:"0";s:7:"digests";s:1:"0";s:4:"rvrc";s:1:"0";s:5:"money";s:1:"0";s:6:"credit";s:1:"0";i:1;s:1:"0";}}');
INSERT INTO pw_tools VALUES('19','{#tool_19}','clear','{#tool_19_inro}','19','19.gif','1','200','money','20.00','2','100','a:1:{s:6:"credit";a:6:{s:7:"postnum";s:1:"0";s:7:"digests";s:1:"0";s:4:"rvrc";s:1:"0";s:5:"money";s:1:"0";s:6:"credit";s:1:"0";i:1;s:1:"0";}}');
INSERT INTO pw_tools VALUES('20','{#tool_20}','mirror','{#tool_20_inro}','20','20.gif','0','10','currency','0.00','2','100','a:1:{s:6:"credit";a:7:{s:7:"postnum";s:1:"0";s:7:"digests";s:1:"0";s:4:"rvrc";s:1:"0";s:5:"money";s:1:"0";s:6:"credit";s:1:"0";i:1;s:1:"0";i:2;s:1:"0";}}');
INSERT INTO pw_tools VALUES('21','{#tool_21}','defend','{#tool_21_inro}','21','21.gif','1','100','money','10.00','2','100','a:1:{s:6:"credit";a:6:{s:7:"postnum";s:1:"0";s:7:"digests";s:1:"0";s:4:"rvrc";s:1:"0";s:5:"money";s:1:"0";s:6:"credit";s:1:"0";i:1;s:1:"0";}}');
INSERT INTO pw_tools VALUES('22','{#tool_22}','backup','{#tool_22_inro}','22','22.gif','0','10','currency','0.00','1','100','a:1:{s:6:"credit";a:7:{s:7:"postnum";s:1:"0";s:7:"digests";s:1:"0";s:4:"rvrc";s:1:"0";s:5:"money";s:1:"0";s:6:"credit";s:1:"0";i:1;s:1:"0";i:2;s:1:"0";}}');

DROP TABLE IF EXISTS pw_topiccate;
CREATE TABLE pw_topiccate (
  cateid tinyint(3) unsigned NOT NULL auto_increment,
  name varchar(30) NOT NULL default '',
  ifable tinyint(3) NOT NULL default '1',
  vieworder tinyint(3) NOT NULL default '0',
  ifdel tinyint(3) NOT NULL default '0',
  PRIMARY KEY  (cateid)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_topicfield;
CREATE TABLE pw_topicfield (
  fieldid smallint(6) unsigned NOT NULL auto_increment,
  name varchar(30) NOT NULL default '',
  fieldname varchar(30) NOT NULL default '',
  modelid smallint(6) unsigned NOT NULL default '0',
  vieworder tinyint(3) NOT NULL default '0',
  type varchar(20) NOT NULL default '0',
  rules mediumtext NOT NULL,
  ifable tinyint(3) NOT NULL default '1',
  ifsearch tinyint(3) NOT NULL default '0',
  ifasearch tinyint(3) NOT NULL default '0',
  threadshow tinyint(3) NOT NULL default '0',
  ifmust tinyint(3) NOT NULL default '1',
  textsize tinyint(3) NOT NULL DEFAULT  '0',
  descrip varchar(255) NOT NULL,
  PRIMARY KEY  (fieldid),
  KEY idx_modelid (modelid)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_topicmodel;
CREATE TABLE pw_topicmodel (
  modelid smallint(6) unsigned NOT NULL auto_increment,
  name varchar(30) NOT NULL default '',
  cateid tinyint(3) unsigned NOT NULL default '0',
  ifable tinyint(3) NOT NULL default '1',
  vieworder tinyint(3) NOT NULL default '0',
  PRIMARY KEY  (modelid),
  KEY idx_cateid (cateid)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_topictype;
CREATE TABLE pw_topictype (
 id smallint(6) unsigned NOT NULL AUTO_INCREMENT,
 fid smallint(6) unsigned NOT NULL default '0',
 name varchar(255) NOT NULL default '',
 logo varchar(255) NOT NULL default '',
 vieworder tinyint(3) NOT NULL default '0',
 upid smallint(6) unsigned NOT NULL default '0',
 PRIMARY KEY  (id)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_tpl;
CREATE TABLE pw_tpl (
  tplid smallint(6) unsigned NOT NULL auto_increment,
  type varchar(50) NOT NULL default '',
  name varchar(50) NOT NULL default '',
  descrip varchar(255) NOT NULL default '',
  tagcode text NOT NULL,
  image varchar(255) NOT NULL default '',
  PRIMARY KEY  (tplid),
  KEY idx_type (type)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_trade;
CREATE TABLE pw_trade (
  tid int(10) unsigned NOT NULL,
  uid int(10) unsigned NOT NULL default '0',
  name varchar(80) NOT NULL default '',
  icon varchar(80) NOT NULL default '',
  degree tinyint(3) unsigned NOT NULL default '0',
  type smallint(6) unsigned NOT NULL default '0',
  num smallint(6) unsigned NOT NULL default '0',
  salenum smallint(6) unsigned NOT NULL default '0',
  price decimal(8,2) NOT NULL default '0',
  costprice decimal(8,2) NOT NULL default '0',
  locus varchar(30) NOT NULL default '',
  paymethod tinyint(3) unsigned NOT NULL default '0',
  transport tinyint(3) unsigned NOT NULL default '0',
  mailfee decimal(6,2) NOT NULL default '0',
  expressfee decimal(6,2) NOT NULL default '0',
  emsfee decimal(6,2) NOT NULL default '0',
  deadline int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (tid),
  KEY idx_uid (uid)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_tradeorder;
CREATE TABLE pw_tradeorder (
  oid mediumint(8) unsigned NOT NULL auto_increment,
  order_no varchar(30) NOT NULL default '0',
  tid int(10) unsigned NOT NULL default '0',
  subject varchar(80) NOT NULL default '',
  buyer int(10) unsigned NOT NULL default '0',
  seller int(10) unsigned NOT NULL default '0',
  price decimal(6,2) NOT NULL default '0',
  quantity smallint(6) unsigned NOT NULL default '0',
  transportfee decimal(4,2) NOT NULL default '0',
  transport tinyint(3) unsigned NOT NULL default '0',
  buydate int(10) unsigned NOT NULL default '0',
  tradedate int(10) unsigned NOT NULL default '0',
  ifpay tinyint(3) NOT NULL default '0',
  address varchar(80) NOT NULL default '',
  consignee varchar(15) NOT NULL default '',
  tel varchar(15) NOT NULL default '',
  zip varchar(15) NOT NULL default '',
  descrip varchar(255) NOT NULL default '',
  payment tinyint(3) unsigned NOT NULL default '0',
  tradeinfo varchar(255) NOT NULL default '',
  PRIMARY KEY  (oid),
  UNIQUE KEY idx_orderno (order_no),
  KEY idx_tid (tid),
  KEY idx_buyer (buyer),
  KEY idx_seller (seller)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_ucapp;
CREATE TABLE pw_ucapp (
  id smallint(5) unsigned NOT NULL auto_increment,
  name varchar(30) NOT NULL default '',
  siteurl varchar(50) NOT NULL default '',
  secretkey varchar(40) NOT NULL default '',
  interface varchar(30) NOT NULL default '',
  uc tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (id)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_ucnotify;
CREATE TABLE pw_ucnotify (
  nid mediumint(8) NOT NULL auto_increment,
  action varchar(20) NOT NULL default '',
  param text NOT NULL,
  timestamp int(10) unsigned NOT NULL default '0',
  complete tinyint(3) unsigned NOT NULL default '0',
  priority tinyint(3) NOT NULL default '0',
  PRIMARY KEY  (nid)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_ucsyncredit;
CREATE TABLE pw_ucsyncredit (
  uid int(10) unsigned NOT NULL,
  PRIMARY KEY  (uid)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_userapp;
CREATE TABLE pw_userapp (
  uid int(10) unsigned NOT NULL,
  appid mediumint(8) unsigned NOT NULL default '0',
  appname varchar(20) NOT NULL default '',
  appinfo text NOT NULL,
  appevent text NOT NULL,
  PRIMARY KEY (uid,appid)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_userbinding;
CREATE TABLE pw_userbinding (
  id int(10) unsigned NOT NULL auto_increment,
  uid int(10) unsigned NOT NULL default '0',
  password varchar(40) NOT NULL default '',
  PRIMARY KEY  (id,uid),
  UNIQUE KEY idx_uid (uid)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS pw_usercache;
CREATE TABLE pw_usercache (
  uid int(10) unsigned NOT NULL,
  type varchar(255) NOT NULL default '',
  typeid int(10) unsigned NOT NULL default '0',
  expire int(10) unsigned NOT NULL default '0',
  num smallint(6) unsigned NOT NULL default '0',
  value text NOT NULL,
  PRIMARY KEY (uid,type)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS pw_usergroups;
CREATE TABLE pw_usergroups (
  gid smallint(5) unsigned NOT NULL auto_increment,
  gptype enum('default','member','system','special') NOT NULL default 'member',
  grouptitle varchar(60) NOT NULL default '',
  groupimg varchar(15) NOT NULL default '',
  grouppost int(10) NOT NULL default '0',
  ifdefault tinyint(3) unsigned NOT NULL default '1',
  PRIMARY KEY  (gid),
  KEY idx_gptype (gptype),
  KEY idx_grouppost (grouppost)
) TYPE=MyISAM;
INSERT INTO pw_usergroups SET gid = 1,  gptype = 'default',grouptitle = 'default',    groupimg = '8', grouppost = 0, ifdefault=1;
INSERT INTO pw_usergroups SET gid = 2,  gptype = 'default',grouptitle = '{#level_1}', groupimg = '8', grouppost = 0, ifdefault=0;
INSERT INTO pw_usergroups SET gid = 3,  gptype = 'system', grouptitle = '{#level_3}', groupimg = '3', grouppost = 0, ifdefault=0;
INSERT INTO pw_usergroups SET gid = 4,  gptype = 'system', grouptitle = '{#level_4}', groupimg = '4', grouppost = 0, ifdefault=0;
INSERT INTO pw_usergroups SET gid = 5,  gptype = 'system', grouptitle = '{#level_5}', groupimg = '5', grouppost = 0, ifdefault=0;
INSERT INTO pw_usergroups SET gid = 6,  gptype = 'default', grouptitle = '{#level_6}', groupimg = '8', grouppost = 0, ifdefault=0;
INSERT INTO pw_usergroups SET gid = 7,  gptype = 'default', grouptitle = '{#level_7}', groupimg = '8', grouppost = 0, ifdefault=0;
INSERT INTO pw_usergroups SET gid = 8,  gptype = 'member', grouptitle = '{#level_8}', groupimg = '8', grouppost = 0, ifdefault=0;
INSERT INTO pw_usergroups SET gid = 9,  gptype = 'member', grouptitle = '{#level_9}', groupimg = '9', grouppost = 100, ifdefault=0;
INSERT INTO pw_usergroups SET gid = 10, gptype = 'member', grouptitle = '{#level_10}',groupimg = '10',grouppost = 300, ifdefault=0;
INSERT INTO pw_usergroups SET gid = 11, gptype = 'member', grouptitle = '{#level_11}',groupimg = '11',grouppost = 600, ifdefault=0;
INSERT INTO pw_usergroups SET gid = 12, gptype = 'member', grouptitle = '{#level_12}',groupimg = '12',grouppost = 1000, ifdefault=0;
INSERT INTO pw_usergroups SET gid = 13, gptype = 'member', grouptitle = '{#level_13}',groupimg = '13',grouppost = 5000, ifdefault=0;
INSERT INTO pw_usergroups SET gid = 14, gptype = 'member', grouptitle = '{#level_14}',groupimg = '14',grouppost = 10000, ifdefault=0;
INSERT INTO pw_usergroups SET gid = 15, gptype = 'member', grouptitle = '{#level_15}',groupimg = '14',grouppost = 50000, ifdefault=0;
INSERT INTO pw_usergroups SET gid = 16, gptype = 'special',grouptitle = '{#level_16}',groupimg = '5', grouppost = 0, ifdefault=0;
INSERT INTO pw_usergroups SET gid = 17, gptype = 'system',grouptitle = '门户编辑',groupimg = '17', grouppost = 0, ifdefault=0;

DROP TABLE IF EXISTS pw_usertool;
CREATE TABLE pw_usertool (
  uid int(10) unsigned NOT NULL default '0',
  toolid smallint(6) NOT NULL default '0',
  nums smallint(6) NOT NULL default '0',
  sellnums smallint(6) NOT NULL default '0',
  sellprice varchar(255) NOT NULL default '',
  sellstatus tinyint(3) unsigned NOT NULL default '1',
  KEY idx_uid (uid)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_voter;
CREATE TABLE pw_voter (
  tid int(10) unsigned NOT NULL default '0',
  uid int(10) unsigned NOT NULL default '0',
  username varchar(15) NOT NULL default '',
  vote tinyint(3) unsigned NOT NULL default '0',
  time int(10) unsigned NOT NULL default '0',
  KEY idx_tid (tid),
  KEY idx_uid (uid)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_weibo_cmrelations;
CREATE TABLE pw_weibo_cmrelations (
  `cid` int(10) unsigned NOT NULL ,
  `uid` int(10) unsigned NOT NULL ,
  PRIMARY KEY ( `cid` , `uid` )
) TYPE=MYISAM;

DROP TABLE IF EXISTS pw_weibo_cnrelations;
CREATE TABLE pw_weibo_cnrelations (
  `cyid` int(10) unsigned NOT NULL default '0',
  `mid` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`cyid`,`mid`)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_weibo_comment;
CREATE TABLE pw_weibo_comment (
  `cid` INT(10) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY ,
  `uid` INT(10) unsigned NOT NULL default '0',
  `mid` INT(10) unsigned NOT NULL default '0' ,
  `content` varchar(250) NOT NULL default '' ,
  `extra` text NOT NULL,
  `postdate` int(10) unsigned NOT NULL default '0' ,
  KEY idx_mid_postdate( `mid` , `postdate` ),
  KEY idx_postdate( `postdate` )
) TYPE=MYISAM;

DROP TABLE IF EXISTS pw_weibo_content;
CREATE TABLE pw_weibo_content (
  `mid` int(10) unsigned NOT NULL auto_increment,
  `uid` int(10) unsigned NOT NULL default '0',
  `content` text NOT NULL,
  `extra` text NOT NULL,
  `contenttype` tinyint(3) unsigned  NOT NULL default '0',
  `type` tinyint(3) unsigned NOT NULL default '0',
  `objectid` int(10) unsigned NOT NULL default '0',
  `replies` mediumint(8) unsigned NOT NULL default '0',
  `transmit` mediumint(8) unsigned NOT NULL default '0',
  `postdate` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`mid`),
  KEY `idx_uid_postdate` (`uid`,`postdate`),
  KEY `idx_type_objectid` (`type`,`objectid`),
  KEY `idx_postdate`( `postdate` )
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_weibo_referto;
CREATE TABLE pw_weibo_referto (
  `uid` int(10) unsigned NOT NULL,
  `mid` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`uid`,`mid`)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_weibo_relations;
CREATE TABLE pw_weibo_relations (
  `uid` int(10) unsigned NOT NULL default '0',
  `mid` int(10) unsigned NOT NULL default '0',
  `authorid` int(10) unsigned NOT NULL default '0',
  `type` tinyint(1) unsigned NOT NULL default '0',
  `postdate` int(10) unsigned NOT NULL default '0',
  KEY `idx_mid` (`mid`),
  KEY `idx_uid_postdate` (`uid`,`postdate`)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_windcode;
CREATE TABLE pw_windcode (
  id smallint(6) unsigned NOT NULL auto_increment,
  name varchar(15) NOT NULL default '',
  icon varchar(30) NOT NULL default '',
  pattern varchar(30) NOT NULL default '',
  replacement text NOT NULL,
  param tinyint(3) NOT NULL default '0',
  ifopen tinyint(3) NOT NULL default '0',
  title varchar(30) NOT NULL default '',
  descrip varchar(100) NOT NULL default '',
  PRIMARY KEY  (id)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_wordfb;
CREATE TABLE pw_wordfb (
  id smallint(6) unsigned NOT NULL auto_increment,
  word varchar(100) NOT NULL default '',
  wordreplace varchar(100) NOT NULL default '',
  type tinyint(3) NOT NULL default '0',
  wordtime int(10) unsigned NOT NULL default '0',
  custom tinyint(3) NOT NULL default '0',
  classid tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (id)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_write_smiles;
CREATE TABLE pw_write_smiles (
  smileid smallint(6) unsigned NOT NULL auto_increment,
  typeid smallint(6) unsigned NOT NULL default '0',
  vieworder tinyint(3) unsigned  NOT NULL default '0',
  path varchar(20) NOT NULL default '',
  name varchar(20) NOT NULL default '',
  tag varchar(30) NOT NULL default '',
  desciption varchar(100) NOT NULL default '',
  PRIMARY KEY  (smileid)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_cache_members;
CREATE TABLE pw_cache_members(
    ckey char(32) not null default '',
    cvalue text not null,
    expire int(10) unsigned not null default '0',
    primary key (ckey)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_statistics_daily;
CREATE TABLE IF NOT EXISTS `pw_statistics_daily` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` char(32) NOT NULL DEFAULT '',
  `typeid` int(6) UNSIGNED NOT NULL DEFAULT '0',
  `date` date NOT NULL DEFAULT '0000-00-00',
  `value` int(11) UNSIGNED NOT NULL DEFAULT '0',
  `updatetime` int(11) UNSIGNED NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_name_date_typeid` (`name`,`date`,`typeid`)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_weibo_bind;
CREATE TABLE IF NOT EXISTS `pw_weibo_bind` (
  `uid` int(10) unsigned NOT NULL default '0',
  `weibotype` varchar(20) NOT NULL,
  `info` text NOT NULL,
  UNIQUE KEY `uid_weibotype` (`uid`,`weibotype`)
) TYPE=MyISAM;
