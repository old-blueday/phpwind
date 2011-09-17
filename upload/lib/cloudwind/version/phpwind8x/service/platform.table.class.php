<?php
! defined ( 'CLOUDWIND' ) && exit ( 'Forbidden' );
require_once CLOUDWIND . '/client/core/public/core.service.class.php';
class CloudWind_Platform_Table extends CloudWind_Core_Service {
	
	function createCloudWindTables() {
		$sqls = $this->_getCloudWindTables();
		if (!is_array($sqls)) return false;
		foreach ($sqls as $tableName => $sql) {
			$result = $GLOBALS['db']->get_one("SHOW TABLES LIKE '{$tableName}'");
			if ($result) continue;
			$GLOBALS['db']->query($sql);
		}
		return true;
	}

	function _getCloudWindTables() {
		$version = ($GLOBALS['db']->server_info() >= '4.1') ? 'ENGINE=MyISAM' : 'TYPE=MyISAM';
		$charset = (CloudWind_getConfig('g_charset')) ? 'DEFAULT CHARSET=' . CloudWind_getConfig('g_charset') : '';
		return array (
			'pw_log_setting' => "CREATE TABLE IF NOT EXISTS `pw_log_setting`(
			    `id` int(10) unsigned not null auto_increment,
			    `vector` varchar(255) not null default '',
			    `cipher` varchar(255) not null default '',
			    `field1` varchar(255) not null default '',
			    `field2` varchar(255) not null default '',
			    `field3` int(10) unsigned not null default '0',
			    `field4` int(10) unsigned not null default '0',
			    primary key(`id`)
			) $version $charset",
	
			'pw_log_forums' => "CREATE TABLE IF NOT EXISTS `pw_log_forums`(
				`id` int(10) unsigned not null auto_increment,
				`sid` int(10) unsigned not null default '0',
				`operate` tinyint(3) not null default '1',
				`modified_time` int(10) unsigned not null default '0',
				primary key(`id`),
				unique key `idx_sid_operate` (`sid`,`operate`)
			) $version $charset", 
	
			'pw_log_colonys' => "CREATE TABLE IF NOT EXISTS `pw_log_colonys`(
			    `id` int(10) unsigned not null auto_increment,
			    `sid` int(10) unsigned not null default '0',
			    `operate` tinyint(3) not null default '1',
			    `modified_time` int(10) unsigned not null default '0',
			    primary key(`id`),
			    unique key `idx_sid_operate`(`sid`,`operate`)
			) $version $charset", 
	
			'pw_log_members' => "CREATE TABLE IF NOT EXISTS `pw_log_members`(
			    `id` int(10) unsigned not null auto_increment,
			    `sid` int(10) unsigned not null default '0',
			    `operate` tinyint(3) not null default '1',
			    `modified_time` int(10) unsigned not null default '0',
			    primary key(`id`),
			    unique key `idx_sid_operate` (`sid`,`operate`)
			) $version $charset", 
	
			'pw_log_diary' => "CREATE TABLE IF NOT EXISTS `pw_log_diary`(
			    `id` int(10) unsigned not null auto_increment,
			    `sid` int(10) unsigned not null default '0',
			    `operate` tinyint(3) not null default '1',
			    `modified_time` int(10) unsigned not null default '0',
			    primary key(`id`),
			    unique key `idx_sid_operate` (`sid`, `operate`)
			) $version $charset", 
	
			'pw_log_posts' => "CREATE TABLE IF NOT EXISTS `pw_log_posts`(
			    `id` int(10) unsigned not null auto_increment,
			    `sid` int(10) unsigned not null default '0',
			    `operate` tinyint(3) not null default '1',
			    `modified_time` int(10) unsigned not null default '0',
			    primary key(`id`),
			    unique key `idx_sid_operate` (`sid`, `operate`)
			) $version $charset", 
	
			'pw_log_threads' => "CREATE TABLE IF NOT EXISTS `pw_log_threads`(
			    `id` int(10) unsigned not null auto_increment,
			    `sid` int(10) unsigned not null default '0',
			    `operate` tinyint(3) not null default '1',
			    `modified_time` int(10) unsigned not null default '0',
			    primary key(`id`),
			    unique key `idx_sid_operate` (`sid`, `operate`)
			) $version $charset", 
	
			'pw_log_attachs' => "CREATE TABLE IF NOT EXISTS `pw_log_attachs`(
			    `id` int(10) unsigned not null auto_increment,
			    `sid` int(10) unsigned not null default '0',
			    `operate` tinyint(3) not null default '1',
			    `modified_time` int(10) unsigned not null default '0',
			    primary key(`id`),
			    unique key `idx_sid_operate` (`sid`, `operate`)
			) $version $charset",
	
			'pw_log_weibos' => "CREATE TABLE IF NOT EXISTS `pw_log_weibos`(
			    `id` int(10) unsigned not null auto_increment,
			    `sid` int(10) unsigned not null default '0',
			    `operate` tinyint(3) not null default '1',
			    `modified_time` int(10) unsigned not null default '0',
			    primary key(`id`),
			    unique key `idx_sid_operate` (`sid`, `operate`)
			) $version $charset",
	
			'pw_yun_setting' => "CREATE TABLE IF NOT EXISTS `pw_yun_setting` (                      
				`id` int(10) unsigned NOT NULL AUTO_INCREMENT,      
				`setting` text,                                     
				PRIMARY KEY (`id`)                                  
			 ) $version $charset",
	
			'pw_log_aggregate' => "CREATE TABLE IF NOT EXISTS `pw_log_aggregate` (                           
				`id` int(10) unsigned NOT NULL AUTO_INCREMENT, 
				`type` tinyint(3) NOT NULL DEFAULT '0',          
			 	`sid` int(10) unsigned NOT NULL DEFAULT '0',            
			 	`operate` tinyint(3) NOT NULL DEFAULT '1',              
			 	`modified_time` int(10) unsigned NOT NULL DEFAULT '0',  
				PRIMARY KEY (`id`),                                     
			 	UNIQUE KEY `idx_sid_type_operate` (`sid`,`type`,`operate`)          
			) $version $charset",
	
			'pw_log_userdefend' => "CREATE TABLE IF NOT EXISTS `pw_log_userdefend` (	               
				`id` int(10) unsigned NOT NULL AUTO_INCREMENT,  
				`data` text,
				PRIMARY KEY (`id`)	  
			) $version $charset",
	
			'pw_log_postdefend' => "CREATE TABLE IF NOT EXISTS `pw_log_postdefend` (
				`id` int(10) unsigned NOT NULL AUTO_INCREMENT,  
				`data` text,
				PRIMARY KEY (`id`) 
			) $version $charset",
	
			'pw_log_postverify' => "CREATE TABLE IF NOT EXISTS `pw_log_postverify` (	               
				`id` int(10) unsigned NOT NULL AUTO_INCREMENT,  
				`type` int(10) unsigned NOT NULL DEFAULT '0',    
				`tid` int(10) unsigned NOT NULL DEFAULT '0',   
				`pid` int(10) unsigned NOT NULL DEFAULT '0', 
				`modified_time` int(10) unsigned NOT NULL DEFAULT '0', 
				PRIMARY KEY (`id`),
				unique key `idx_tid_pid` (`tid`, `pid`),
				KEY `idx_modifiedtime` (`modified_time`)
			) $version $charset"
		);
	}
}