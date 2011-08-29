<?php
! defined ( 'P_W' ) && exit ( 'Forbidden' );
/**
 * 频道相关(包括栏目)服务层
*/
class PW_ChannelService {

	function PW_ChannelService() {
		$this->__construct();
	}
	function __construct() {

	}
	/*
	 * 创建频道
	 */
	function createChannel($name,$alias,$theme,$domain) {
		$channelId = $this->addChannel($name,$alias,$theme,$domain);
		$channelPath = $this->getChannelPath($alias);
		
		L::loadClass('fileoperate', 'utility', false);

		PW_FileOperate::createFolder(S::escapePath($channelPath));
		
		$this->_createChannelFiles($theme,$alias,$name);
		
		return $channelId;
	}
	
	function _createChannelFiles($theme,$alias,$name) {
		$this->_copyThemesFiles($theme,$alias);
		
		$this->_initTemplateFile($alias,$name);
		$this->_initConfigFile($alias,$name);
		$this->_initIndexHtmlFile($alias);
	}
	
	function _initTemplateFile($alias,$name) {
		$templateFile = $this->_getTemplateFile($alias);
		$this->_initFileModuleIds($templateFile,$name);
	}
	
	function _initConfigFile($alias,$name) {
		$configFile = $this->_getConfigFile($alias);
		$this->_initFileModuleIds($configFile,$name);
	}
	
	function _initIndexHtmlFile($alias) {
		$indexFile = $this->_getIndexFile($alias);
		@fclose(@fopen($indexFile,'w'));
		@chmod($indexFile,0777);
	}
	
	function _initFileModuleIds($file,$name) {
		@chmod($file,0777);
		
		$fileString = pwCache::readover($file);
		$moduleConfigService = L::loadClass('moduleconfigservice', 'area');
		$newString	= $moduleConfigService->cookModuleIds($fileString,$name);
		pwCache::writeover($file,$newString);
	}
	
	function _copyThemesFiles($theme,$alias) {
		$themePath = R_P.'mode/area/themes/'.$theme;
		$channelPath = $this->getChannelPath($alias);
		
		L::loadClass('fileoperate', 'utility', false);
		PW_FileOperate::copyFiles($themePath,$channelPath);
	}
	/**
	 * 通过频道别名获取频道信息
	 * @param $alias
	 */
	function getChannelInfoByAlias($alias) {
		$channelDAO = $this->_getChannelDAO();
		$temp =  $channelDAO->getChannelByAlias($alias);
		return $this->_cookChannelInfo($temp);
	}
	/**
	 * 通过频道id获取频道唯一标识
	 */
	function getAliasByChannelid($cid) {
		$temp = $this->getChannelByChannelid($cid);
		if (!$temp) return false;
		return $temp['alias'];
	}
	function getChannelByChannelid($cid) {
		$channelDAO = $this->_getChannelDAO();
		$temp = $channelDAO->getChannelByChannelid($cid);
		return $this->_cookChannelInfo($temp);
	}
	
	function _cookChannelInfo($info) {
		if (!$info) return array();
		global $db_bbsurl;
		$info['url'] = $this->getChannelUrl($info);
		$info['activeUrl'] = $db_bbsurl.'/index.php?m=area&alias='.$info['alias'];
		return $info;
	}
	
	function getChannels() {
		$temp = array();
		$channelDAO = $this->_getChannelDAO();
		$channels = $channelDAO->getChannels();
		foreach ($channels as $key=>$value) {
			$temp[$value['alias']] = $value;
		}
		return $temp;
	}
	function getChannelUrl($channel) {
		global $db_bbsurl,$index_url,$db_htmdir;
		if (is_numeric($channel)) {
			$channelDAO = $this->_getChannelDAO();
			$channel = $channelDAO->getChannelByChannelid($channel);
		}
		if (!$channel || !is_array($channel)) {
			return '';
		}
		$db_bbsurl = $index_url ? $index_url : $db_bbsurl;

		if ($channel['domain_band']) {
			return strpos($channel['domain_band'],'http://')===false ? 'http://'.$channel['domain_band'] : $channel['domain_band'];
		}
		return $db_bbsurl . '/' . $db_htmdir . '/channel/'.$channel['alias'];
	}
	function addChannel($channel_name,$channel_alias,$channel_theme,$channel_domain) {
		$channelDAO = $this->_getChannelDAO();
		$temp = $channelDAO->addChannel($channel_name,$channel_alias,$channel_theme,$channel_domain);
		if (!$temp) return false;
		$this->updateAreaChannels();
		return $temp;
	}
	function delChannel($id) {
		$this->_delChannelRelateData($id);
		$channelDAO = $this->_getChannelDAO();
		$temp = $channelDAO->delChannel($id);
		if (!$temp) return false;
		$this->updateAreaChannels();
		return true;
	}
	function _delChannelRelateData($id) {
		$channelInfo = $this->getChannelByChannelid($id);
		if (!$channelInfo) return false;
		$invokeService = L::loadClass('invokeservice', 'area');
		$pageInvokes = $invokeService->getChannelPageInvokes($channelInfo['alias']);
		foreach ($pageInvokes as $invoke) {
			$invokeService->deleteInvoke($invoke['name']);
		}
	}
	/**
	 * 获取某个频道的SEO信息
	 * @param int $channelId 频道id
	 * @return array 
	 */
	function getChannelSEO($channelId) {
		$channelInfo = $this->getChannelByChannelid($channelId);
		if ($channelInfo) {
			return array('metatitle' => $channelInfo['metatitle'], 'metadescrip' => $channelInfo['metadescrip'],'metakeywords' => $channelInfo['metakeywords']);
		}
		return array();
	}
	
	function updateDefaultAlias($alias) {
		global $db;
		$update	= array('area_default_alias','string',$alias,'');
		$db->update("REPLACE INTO pw_hack VALUES (".S::sqlImplode($update).')');
		updatecache_conf('area',true);
		return true;
	}
	function updateStaticTime($time) {
		global $db;
		$time = (int) $time;
		$update	= array('area_statictime','string',$time,'');
		$db->update("REPLACE INTO pw_hack VALUES (".S::sqlImplode($update).')');
		updatecache_conf('area',true);
		return true;
	}
	function updateAreaChannels() {
		global $db;
		$channels = $this->getChannels();
		$db->update("REPLACE INTO pw_hack SET hk_name='area_channels',vtype='array',hk_value=".S::sqlEscape(serialize($channels),false));
		updatecache_conf('area',true);
		return true;
	}
	function updateChannelStaticTime($alias,$time) {
		$this->updateChannelByAlias($alias,array('statictime'=>$time));
	}
	function updateChannel($channelId,$array) {
		$temp = $this->_updateChannel($channelId,$array);
		if ($temp) $this->_updateChannelDomain();
		$this->updateAreaChannels();
		return $temp;
	}
	function updateChannels($array) {
		foreach ($array as $key=>$value) {
			$this->_updateChannel($key,$value);
		}
		$this->_updateChannelDomain();
		$this->updateAreaChannels();
	}
	
	function _updateChannel($channelId,$array) {
		$channelDAO = $this->_getChannelDAO();
		
		if (isset($array['queue'])) $array['queue'] = (int) $array['queue'];
		return $channelDAO->update($channelId,$array);
	}
	function updateChannelByAlias($alias,$array) {
		$channelDAO = $this->_getChannelDAO();
		$temp = $channelDAO->updateByAlias($alias,$array);
		if ($temp) $this->_updateChannelDomain();
		$this->updateAreaChannels();
		return $temp;
	}

	function _updateChannelDomain() {
		include_once R_P.'admin/cache.php';
		$channelDAO = $this->_getChannelDAO();
		$channelDomain = $channelDAO->getSecendDomains();
		setConfig('db_channeldomain', $channelDomain);
		updatecache_c();
	}
	/**
	 * 更新某个频道的SEO信息
	 * @param int $channelId 频道id
	 * @param array $seo seo信息
	 * @return array 
	 */
	function updateChannelSEO($channelId,$seo) {
		$this->updateChannel($channelId,array('metatitle' => $seo['metatitle'], 'metadescrip' => $seo['metadescrip'],'metakeywords' => $seo['metakeywords'],));
	}
	
	function _getIndexFile($alias) {
		return $this->getChannelPath($alias).'/index.html';
	}
	
	function _getTemplateFile($alias) {
		return $this->getChannelPath($alias).'/'.PW_PORTAL_MAIN;
	}
	function _getConfigFile($alias) {
		return $this->getChannelPath($alias).'/'.PW_PORTAL_CONFIG;
	}
	function getChannelPath($alias) {
		return S::escapePath(AREA_PATH.$alias);
	}

	function _getChannelDAO() {
		return L::loadDB('Channel', 'area');
	}
}
?>