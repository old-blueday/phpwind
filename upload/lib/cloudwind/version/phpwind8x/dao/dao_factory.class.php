<?php
! defined ( 'CLOUDWIND' ) && exit ( 'Forbidden' );
class CloudWind_Dao_Factory {
	var $_dao = array ();
	
	function getSearchThreadDao() {
		if (! $this->_dao ['SearchThreadDao']) {
			require_once CLOUDWIND_VERSION_DIR . '/dao/search_threadsdb.class.php';
			$this->_dao ['SearchThreadDao'] = new CloudWind_Search_ThreadsDb ();
		}
		return $this->_dao ['SearchThreadDao'];
	}
	
	function getSearchPostDao() {
		if (! $this->_dao ['SearchPostDao']) {
			require_once CLOUDWIND_VERSION_DIR . '/dao/search_postsdb.class.php';
			$this->_dao ['SearchPostDao'] = new CloudWind_Search_PostsDb ();
		}
		return $this->_dao ['SearchPostDao'];
	}
	
	function getSearchWeiboDao() {
		if (! $this->_dao ['SearchWeiboDao']) {
			require_once CLOUDWIND_VERSION_DIR . '/dao/search_weibosdb.class.php';
			$this->_dao ['SearchWeiboDao'] = new CloudWind_Search_WeibosDb ();
		}
		return $this->_dao ['SearchWeiboDao'];
	}
	
	function getSearchMemberDao() {
		if (! $this->_dao ['SearchMemberDao']) {
			require_once CLOUDWIND_VERSION_DIR . '/dao/search_membersdb.class.php';
			$this->_dao ['SearchMemberDao'] = new CloudWind_Search_MembersDb ();
		}
		return $this->_dao ['SearchMemberDao'];
	}
	
	function getSearchForumDao() {
		if (! $this->_dao ['SearchForumDao']) {
			require_once CLOUDWIND_VERSION_DIR . '/dao/search_forumsdb.class.php';
			$this->_dao ['SearchForumDao'] = new CloudWind_Search_ForumsDb ();
		}
		return $this->_dao ['SearchForumDao'];
	}
	
	function getSearchDiaryDao() {
		if (! $this->_dao ['SearchDiaryDao']) {
			require_once CLOUDWIND_VERSION_DIR . '/dao/search_diarysdb.class.php';
			$this->_dao ['SearchDiaryDao'] = new CloudWind_Search_DiarysDb ();
		}
		return $this->_dao ['SearchDiaryDao'];
	}
	
	function getSearchColonyDao() {
		if (! $this->_dao ['SearchColonyDao']) {
			require_once CLOUDWIND_VERSION_DIR . '/dao/search_colonysdb.class.php';
			$this->_dao ['SearchColonyDao'] = new CloudWind_Search_ColonysDb ();
		}
		return $this->_dao ['SearchColonyDao'];
	}
	
	function getSearchAttachDao() {
		if (! $this->_dao ['SearchAttachDao']) {
			require_once CLOUDWIND_VERSION_DIR . '/dao/search_attachsdb.class.php';
			$this->_dao ['SearchAttachDao'] = new CloudWind_Search_AttachsDb ();
		}
		return $this->_dao ['SearchAttachDao'];
	}
	
	function getDefendPostVerifyDao() {
		if (! $this->_dao ['DefendPostVerifyDao']) {
			require_once CLOUDWIND_VERSION_DIR . '/dao/defend_postverifydb.class.php';
			$this->_dao ['DefendPostVerifyDao'] = new CloudWind_Defend_PostVerifyDb ();
		}
		return $this->_dao ['DefendPostVerifyDao'];
	}
	
	function getPlatformAggregateDao() {
		if (! $this->_dao ['PlatformAggregateDao']) {
			require_once CLOUDWIND_VERSION_DIR . '/dao/platform_aggregatedb.class.php';
			$this->_dao ['PlatformAggregateDao'] = new CloudWind_Platform_AggregateDb ();
		}
		return $this->_dao ['PlatformAggregateDao'];
	}
	
	function getPlatformLogSettingDao() {
		if (! $this->_dao ['PlatformLogSettingDao']) {
			require_once CLOUDWIND_VERSION_DIR . '/dao/platform_logsettingdb.class.php';
			$this->_dao ['PlatformLogSettingDao'] = new CloudWind_Platform_LogSettingDb ();
		}
		return $this->_dao ['PlatformLogSettingDao'];
	}
	
	function getPlatformSettingDao() {
		if (! $this->_dao ['PlatformSettingDao']) {
			require_once CLOUDWIND_VERSION_DIR . '/dao/platform_settingdb.class.php';
			$this->_dao ['PlatformSettingDao'] = new CloudWind_Platform_SettingDb ();
		}
		return $this->_dao ['PlatformSettingDao'];
	}
	
	function getDefendUserDefendDao() {
		if (! $this->_dao ['DefendUserDefendDao']) {
			require_once CLOUDWIND_VERSION_DIR . '/dao/defend_userdefenddb.class.php';
			$this->_dao ['DefendUserDefendDao'] = new CloudWind_Defend_UserDefendDb ();
		}
		return $this->_dao ['DefendUserDefendDao'];
	}
	
	function getSearchLogsDao() {
		if (! $this->_dao ['SearchLogsDao']) {
			require_once CLOUDWIND_VERSION_DIR . '/dao/search_logsdb.class.php';
			$this->_dao ['SearchLogsDao'] = new CloudWind_Search_LogsDb ();
		}
		return $this->_dao ['SearchLogsDao'];
	}
	
}