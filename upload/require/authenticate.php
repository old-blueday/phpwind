<?php
!defined('P_W') && exit('Forbidden');
/**
 * 
 * @author pw team, Nov 5, 2010
 * @copyright 2003-2010 phpwind.net. All rights reserved.
 * @version 
 * @package default
 */
 
 
 function sendDataToPlatform($method, $params) {
	if ($method == '') return false;

	L::loadClass('client', 'utility/platformapisdk', false);
	//require_once(R_P.'api/class_json.php');
	L::loadClass('json', 'utility', false);
    $PlatformApiClient = new PlatformApiClient($GLOBALS['db_sitehash'],$GLOBALS['db_siteownerid']);
    $returnData = $PlatformApiClient->get($method,$params);//return $returnData;
    $Json = new Services_JSON();
    return $Json->decode($returnData);
}