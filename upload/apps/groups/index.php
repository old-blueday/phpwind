<?php
!defined('A_P') && exit('Forbidden');

$USCR = 'user_groups';
include_once(D_P . 'data/bbscache/o_config.php');

$baseUrl = 'apps.php?q=' . $q;
$basename =  $baseUrl . '&';

if ($q == "groups") {
	require_once Pcv($appEntryBasePath . 'action/m_groups.php');
} elseif ($q == "group") {
	require_once Pcv($appEntryBasePath . 'action/m_group.php');
} elseif ($q == "galbum") {
	require_once Pcv($appEntryBasePath . 'action/m_galbum.php');
} elseif ($q == 'topicadmin') {
	require_once Pcv($appEntryBasePath . 'action/m_topicadmin.php');
}
?>