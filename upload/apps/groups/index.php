<?php
!defined('A_P') && exit('Forbidden');
$USCR = 'user_groups';
//* include_once pwCache::getPath(D_P . 'data/bbscache/o_config.php');
pwCache::getData(D_P . 'data/bbscache/o_config.php');

$baseUrl = 'apps.php?q=' . $q;
$basename =  $baseUrl . '&';

if ($q == "groups") {
	require_once S::escapePath($appEntryBasePath . 'action/m_groups.php');
} elseif ($q == "group") {
	require_once S::escapePath($appEntryBasePath . 'action/m_group.php');
} elseif ($q == "galbum") {
	require_once S::escapePath($appEntryBasePath . 'action/m_galbum.php');
} elseif ($q == 'topicadmin') {
	require_once S::escapePath($appEntryBasePath . 'action/m_topicadmin.php');
}
?>