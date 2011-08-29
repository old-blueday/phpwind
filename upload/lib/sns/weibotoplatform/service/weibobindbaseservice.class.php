<?php
!defined('P_W') && exit('Forbidden');


class PW_WeiboBindBaseService {
	
	/**
	 * @return PlatformApiClient
	 */
	function _getPlatformApiClient() {
		static $client = null;
		if (!$client) {
			global $db_sitehash, $db_siteownerid;
			L::loadClass('client', 'utility/platformapisdk', false);
			$client = new PlatformApiClient($db_sitehash, $db_siteownerid);
		}
		return $client;
	}
}
