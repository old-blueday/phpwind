<?php
! function_exists ( 'readover' ) && exit ( 'Forbidden' );
/**
 * sphinx实时索引服务 独立配置/定制化服务/需要服务端支持
 * @author L.IuHu.I@2010-11-20
 * 索引的名称可自定义修改
 * 服务器环境
 * 1,主索引,如 threadsindex
 * 2,增量索引,如 addthreadsindex
 * 3,合并索引并过滤  indexer --merge threadsindex addthreadsindex --merge-dst-range deleted 0 0
 * 目前帖子有三个索引,因此在更新帖子索引时需要更新三个索引项,如版本的 threadsindex, tmsgsindex, threadsallindex三个索引
 * 默认的情况下更新如上三个选项,具体的索引名称请配置如下索引名称列表中
 * 
 * 增量索引的数据来源可以从实时索引表中获取,数据表结构如下
 * -- TableName pw_delta_threads 实时索引表
 * -- Created By phpwind@2010-11-20
 * -- Fields id       主键ID(如tid,uid,did,pid)
 * -- Fields state    状态 0/1（可自定义）
 * CREATE TABLE pw_delta_tablename(
 * 	id int(10) unsigned not null auto_increment,
 * 	state tinyint(3) unsigned not null default 0,
 * 	primary key (id)
 * )ENGINE=MyISAM;
 * 将表名称换成需要增量索引的表,如pw_delta_threads,pw_delta_posts,pw_delta_users,pw_delta_diary等
 */
class PW_RealTimeSearcher {
	
	var $_sphinxConfig = array (); //基础配置
	var $_threadIndexs = array ('threadsindex', 'tmsgsindex', 'threadsallindex' ); //帖子索引名称列表
	var $_userIndexs = array ('membersindex' ); //用户索引名称列表
	var $_diaryIndexs = array ('diarysindex', 'diarycontentsindex', 'diaryallsindex' ); //日志索引名称列表
	var $_postIndexs = array ('postsindex' ); //回复索引名称列表
	var $_deleteFiled = 'authorid'; //合并索引过滤字段
	var $_tableNames = array ('thread' => 'pw_delta_threads', 'post' => 'pw_delta_posts', 'member' => 'pw_delta_members', 'diary' => 'pw_delta_diarys' );
	
	function PW_RealTimeSearcher() {
		$this->_sphinxConfig = ($GLOBALS ['db_sphinx']) ? $GLOBALS ['db_sphinx'] : array ('host' => 'localhost', 'port' => 3312 );
	}
	
	function syncData($type, $operate, $ids) {
		$ids = (S::isArray ( $ids )) ? $ids : array ($ids );
		$indexes = array ('thread' => $this->_threadIndexs, 'post' => $this->_postIndexs, 'user' => $this->_userIndexs, 'diary' => $this->_diaryIndexs );
		if (! isset ( $this->_tableNames [$type] ) || ! isset ( $indexes [$type] )) {
			return false;
		}
		switch ($operate) {
			case 'insert' :
				return $this->_logDelta ( $this->_tableNames [$type], $ids, 0 );
				break;
			case 'update' :
				return $this->_logDelta ( $this->_tableNames [$type], $ids, 0 );
				break;
			case 'delete' :
				return $this->_doSync ( $indexes [$type], array ($this->_deleteFiled ), $ids, 1 );
				break;
			default :
				break;
		}
		return true;
	}
	
	function _logDelta($tableName, $ids, $state) {
		if (! S::isArray ( $ids ))
			return false;
		$_tmp = array ();
		foreach ( $ids as $id ) {
			$_tmp [] = array ('id' => $id, 'state' => $state );
		}
		$GLOBALS ['db']->update ( "REPLACE INTO " . S::sqlMetadata ( $tableName ) . " (id,state) VALUES " . S::sqlMulti ( $_tmp ) );
	}
	
	function _doSync($indexes, $attrs, $ids, $state) {
		if (! S::isArray ( $ids )) {
			return false;
		}
		$_tmp = array ();
		foreach ( $ids as $id ) {
			$_tmp [$id] = array ($state );
		}
		return $this->_syncData ( $indexes, $attrs, $_tmp );
	}
	
	function _syncData($indexes, $attrs, $values) {
		$sphinxAPI = $this->_getSphinxAPI ();
		list ( $host, $port ) = array ($this->_sphinxConfig ['host'], $this->_sphinxConfig ['port'] );
		$sphinxAPI->SetServer ( $host, ( int ) $port );
		$sphinxAPI->SetConnectTimeout ( 1 );
		return $sphinxAPI->UpdateAttributes ( implode ( ',', $indexes ), $attrs, $values );
	}
	
	function _getSphinxAPI() {
		L::loadClass ( 'sphinx', 'utility', false );
		return new SphinxClient ();
	}

}