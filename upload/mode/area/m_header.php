<?php
!defined('P_W') && exit('Forbidden');
S::gp(array('alias','ifactive'),'G');

extract(L::style('',$skinco));
//* include_once pwCache::getPath(D_P.'data/bbscache/area_config.php');
pwCache::getData(D_P.'data/bbscache/area_config.php');
$chanelService = L::loadClass('channelService', 'area');
$channelInfo = $chanelService->getChannelInfoByAlias($alias);
$areaLevelService = L::loadClass('arealevel', 'area');

$ifChannelEdit=$areaLevelService->getAreaLevel($winduid,$channelInfo['id']);
list($_Navbar,$_LoginInfo) = pwNavBar();
list(,$showq) = explode("\t", $db_qcheck);
require modeEot('m_header');

$output = str_replace(array('<!--<!---->', '<!---->'), '', ob_get_contents());

echo ObContents("document.write('".addslashes(preg_replace("'([\r\n\t])+'", "",trim($output)))."');");
?>