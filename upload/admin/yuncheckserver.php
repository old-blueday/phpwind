<?php
require_once(R_P.'lib/cloudwind/cloudwind.class.php');
$_service = CloudWind::getPlatformCheckServerService ();
if ($_service->checkCloudWind () < 9) {
	ObHeader ( $admin_file . '?adminjob=yunbasic' );
}
list ( $bbsname, $bbsurl, $bbsversion, $cloudversion ) = $_service->getSiteInfo ();
list ( $fsockopen, $parse_url, $isgethostbyname, $gethostbyname ) = $_service->getFunctionsInfo ();
list ( $searchHost, $searchIP, $searchPort, $searchPing ) = $_service->getSearchHostInfo ();
list ( $defendHost, $defendIp, $defendPort, $defendPing ) = $_service->getDefendHostInfo ();
$description = $_service->getBaseDescription ();
include PrintEot ( 'yuncheckserver' );