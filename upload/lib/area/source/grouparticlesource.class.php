<?php
!defined('P_W') && exit('Forbidden');
require_once (R_P . 'lib/base/systemdata.php');
class PW_GroupArticleSource extends SystemData {
	var $_lang = array(

		'hits'		=> '点击数',
		'lastposter'=> '最后回帖人',
		'cyid'	=> '群组id',
		'cname'	=> '群组名称',
	);
	
	function getSourceData($config, $num) {
		$config = $this->_initConfig($config);
		return $this->_getData($config['sorttype'],$config['groupid'],$num);
	}
	
	function _getData($sortType, $groupId = 0, $num) {
		switch ($sortType) {
			case '':
			case 'newarticle' :
				return $this->_getDataByNewArticle($groupId,$num);
			case 'newreplye' :
				return $this->_getDataByNewReply($groupId,$num);
		}
	}
	
	function _getDataByNewArticle($groupId,$num) {
		return $this->_getDataForSort('newarticle',$groupId,$num);
	}
	
	function _getDataByNewReply($groupId,$num) {
		return $this->_getDataForSort('newreplye',$groupId,$num);
	}
	
	function _getDataForSort($type,$groupId,$num) {
		global $db;
		$_sql_order = $type=='newarticle' ? 'a.tid' : 'a.lastpost';
		$groupId = (int) $groupId;
		$_sqlAddArray = array();
		if ($groupId) $_sqlAddArray[] = 'a.cyid='.S::sqlEscape($groupId);
		if ($type=='newreplye') $_sqlAddArray[] = 't.replies>0';
		$_sqlAddArray[] = '!(t.fid=0 AND t.ifcheck=1)';
		$_sqlAddArray[] = 'cy.ifopen=1';
		
		$_sqlAdd = $_sqlAddArray ? ' WHERE '.implode(' AND ',$_sqlAddArray) : ' ';
		$_sql = "SELECT t.tid,t.author,t.authorid,t.subject,t.type,t.postdate,t.hits,t.replies,t.lastposter,a.cyid,a.lastpost,cy.cname FROM pw_argument a LEFT JOIN pw_threads t ON a.tid=t.tid LEFT JOIN pw_colonys cy ON a.cyid=cy.id $_sqlAdd ORDER BY $_sql_order DESC" . S::sqlLimit(0,$num);
		$query = $db->query($_sql);
		$temp = array();
		while ($rt = $db->fetch_array($query)) {
			$temp[] = $this->_cookData($rt);
		}
		return $temp;
	}
	
	function _cookData($result) {
		global $db_bbsurl;
		$result['url'] 	= $db_bbsurl.'/apps.php?q=group&a=read&cyid='.$result['cyid'].'&tid='.$result['tid'];
		$result['authorurl'] = 'u.php?uid='.$result['authorid'];
		$result['title'] 	= $result['subject'];
		$result['image']	= '';
		return $result;
	}
	
	function getSourceConfig() {
		return array(
			'sorttype' => array(
				'name' => '调用类型',
				'type' => 'select',
				'value' => array(
					'newarticle' => '最新主题',
					'newreplye' => '最新回复'
				)
			),
			'groupid' => array(
				'name' => '群组ID',
				'type' => 'text',
				'value' => ''
			)
		);
	}
	
	function _initConfig($config) {
		$config = $this->_initSortType($config);
		$temp = array();
		$temp['sorttype'] = $config['sorttype'];
		$temp['groupid'] = (int) $config['groupid'];
		
		return $temp;
	}
	
	function _initSortType($config) {
		$sourceConfig = $this->getSourceConfig();
		$sortTypes = array_keys($sourceConfig['sorttype']['value']);
		if (!isset($config['sorttype']) || !in_array($config['sorttype'], $sortTypes)) {
			$config['sorttype'] = $sortTypes[0];
		}
		return $config;
	}

}