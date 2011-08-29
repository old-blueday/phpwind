<?php
!defined('P_W') && exit('Forbidden');
$purview['cms_article'] = array('内容管理', "$admin_file?adminjob=mode&admintype=cms_article");
$purview['cms_column'] = array('栏目管理', "$admin_file?adminjob=mode&admintype=cms_column");
$purview['cms_purview'] = array('权限管理', "$admin_file?adminjob=mode&admintype=cms_purview");
?>