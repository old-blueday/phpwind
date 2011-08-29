<?php
!defined('P_W') && exit('Forbidden');

$purview['area_channel_manage'] = array('频道管理', "$admin_file?adminjob=mode&admintype=area_channel_manage");
$purview['area_pushdata'] = array('内容推送', "$admin_file?adminjob=mode&admintype=area_pushdata");
$purview['area_level_manage'] = array('权限管理 ', "$admin_file?adminjob=mode&admintype=area_level_manage");
$purview['area_selecttpl'] = array('模板中心', "$admin_file?adminjob=mode&admintype=area_selecttpl");
$purview['area_static_manage'] = array('静态配置', "$admin_file?adminjob=mode&admintype=area_static_manage");
$purview['area_module'] = array('模块管理', "$admin_file?adminjob=mode&admintype=area_module");
?>