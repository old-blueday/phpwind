<?php
! defined ( 'P_W' ) && exit ( 'Forbidden' );
/*
 * 结构化查询语句组装器
 * @author L.IuHu.I@2010/10/19 developer.liuhui@gmail.com
 */
! defined ( 'PW_COLUMN' ) && define ( 'PW_COLUMN', 'column' ); //查询字段
! defined ( 'PW_EXPR' ) && define ( 'PW_EXPR', 'expr' ); //查询表达式
! defined ( 'PW_ORDERBY' ) && define ( 'PW_ORDERBY', 'orderby' ); //排序
! defined ( 'PW_GROUPBY' ) && define ( 'PW_GROUPBY', 'groupby' ); //分组
! defined ( 'PW_LIMIT' ) && define ( 'PW_LIMIT', 'limit' ); //分页
! defined ( 'PW_ASC' ) && define ( 'PW_ASC', 'asc' ); //升序
! defined ( 'PW_DESC' ) && define ( 'PW_DESC', 'desc' ); //降序
define ( 'PW_DEBUG', 0 ); //是否开启调试打印
class PW_QueryBuilder {
	/**
	 * 生成新增语句
	 * @param $tableName   数据表名称,如:pw_threads
	 * @param $col_names   字段名称数组,如:array('tid'=>1,'fid'=>2)
	 */
	function insertClause($tableName, $col_names) {
		if (! $tableName || ! is_array ( $col_names ))
			return '';
		$sql = "INSERT INTO " . S::sqlMetadata ( $tableName ) . " ";
		$sql .= $this->_parseSetSQL ( $col_names );
		$this->_smallHook ( 'insert', $sql, array ($tableName ), $col_names );
		return $sql;
	}
	/**
	 * 生成替换语句
	 * @param $tableName   数据表名称,如:pw_threads
	 * @param $col_names   字段名称数组,如:array('tid'=>1,'fid'=>2)
	 */
	function replaceClause($tableName, $col_names) {
		if (! $tableName || ! is_array ( $col_names ))
			return '';
		$sql = "REPLACE INTO " . S::sqlMetadata ( $tableName ) . " ";
		$sql .= $this->_parseSetSQL ( $col_names );
		$this->_smallHook ( 'replace', $sql, array ($tableName ), $col_names );
		return $sql;
	}
	/**
	 * 生成查询语句
	 * @param $tableName         数据表名称,如:pw_threads
	 * @param $where_statement   查询条件语句,如:fid=:fid and ifcheck=:ifcheck,注意后面部分与字段一致
	 * @param $where_conditions  查询条件参数,如array(1,2),与上在的条件语句顺序保持一致
	 * @param $expand            扩展条件,可选,说明如下
	 * $expand = array(
	 * PW_COLUMN  = array('fid','tid'),//需要查询的字段,默认为*,参数为数组
	 * PW_EXPR    = array('count(*) as c','max(tid)'),//特殊的查询,如统计,最大/小值,参数为数组
	 * PW_ORDERBY = array('postdate'=> PW_ASC,'tid'=>PW_DESC),//排序条件,字段=>升/降形式,参数为数组
	 * PW_GROUPBY = array('tid'),//分组条件,数组
	 * PW_LIMIT   = array(offset,limit),查询起点数与查询个数
	 * );
	 */
	function selectClause($tableName, $where_statement = null, $where_conditions = null, $expand = null) {
		if (! $tableName)
			return '';
		list ( $where_statement, $fields ) = $this->_parseStatement ( $where_statement, $where_conditions );
		$sql = "SELECT ";
		$sql .= $this->_parseColumns ( isset ( $expand [PW_COLUMN] ) ? $expand [PW_COLUMN] : '', isset ( $expand [PW_EXPR] ) ? $expand [PW_EXPR] : '' );
		$sql .= " FROM " . S::sqlMetadata ( $tableName ) . " ";
		($where_statement) && $sql .= " WHERE " . $where_statement;
		(isset ( $expand [PW_GROUPBY] )) && $sql .= $this->_parseGroupBy ( $expand [PW_GROUPBY] );
		(isset ( $expand [PW_ORDERBY] )) && $sql .= $this->_parseOrderBy ( $expand [PW_ORDERBY] );
		(isset ( $expand [PW_LIMIT] )) && $sql .= $this->_parseLimit ( $expand [PW_LIMIT] );
		$this->_smallHook ( 'select', $sql, array ($tableName ), $fields );
		return $sql;
	}
	/**
	 * 生成更新语句
	 * @param $tableName        数据表名称,如pw_threads
	 * @param $where_statement  同上 selectClause()参数
	 * @param $where_conditions 同上 selectClause()参数
	 * @param $col_names        字段名称数组,如:array('tid'=>1,'fid'=>2)
	 * @param $expand           同上 selectClause()参数说明,但只有排序部分
	 */
	function updateClause($tableName, $where_statement = null, $where_conditions = null, $col_names, $expand = null) {
		if (! $tableName || (! is_array ( $col_names ) && ! isset ( $expand [PW_EXPR] )))
			return '';
		list ( $where_statement, $fields ) = $this->_parseStatement ( $where_statement, $where_conditions );
		$sql = "UPDATE " . S::sqlMetadata ( $tableName ) . " ";
		$sql .= $this->_parseSetSQL ( $col_names, (isset ( $expand [PW_EXPR] ) ? $expand [PW_EXPR] : '') );
		($where_statement) && $sql .= " WHERE " . $where_statement;
		(isset ( $expand [PW_ORDERBY] )) && $sql .= $this->_parseOrderBy ( $expand [PW_ORDERBY] );
		(isset ( $expand [PW_LIMIT] )) && $sql .= $this->_parseLimit ( $expand [PW_LIMIT] );
		$this->_smallHook ( 'update', $sql, array ($tableName ), $fields, $col_names );
		return $sql;
	}
	/**
	 * 生成删除语句
	 * @param $tableName        数据表名称,如pw_threads
	 * @param $where_statement  同上 selectClause()参数
	 * @param $where_conditions 同上 selectClause()参数
	 * @param $col_names        字段名称数组,如:array('tid'=>1,'fid'=>2)
	 * @param $expand           同上 selectClause()参数说明,但只有排序部分
	 */
	function deleteClause($tableName, $where_statement = null, $where_conditions = null, $expand = null) {
		if (! $tableName)
			return '';
		list ( $where_statement, $fields ) = $this->_parseStatement ( $where_statement, $where_conditions );
		$sql = "DELETE FROM " . S::sqlMetadata ( $tableName ) . " ";
		($where_statement) && $sql .= " WHERE " . $where_statement;
		(isset ( $expand [PW_ORDERBY] )) && $sql .= $this->_parseOrderBy ( $expand [PW_ORDERBY] );
		(isset ( $expand [PW_LIMIT] )) && $sql .= $this->_parseLimit ( $expand [PW_LIMIT] );
		$this->_smallHook ( 'delete', $sql, array ($tableName ), $fields );
		return $sql;
	}
	/**
	 * 通用查询语句组装
	 * @param $format      查询语句格式,注意数据表名采用:pw_table的形式,多个表名pw_table1,pw_table2
	 * @param $parameters  查询语句变量
	 */
	function buildClause($format, $parameters, $clauses = array()) {
		if (! $format || ! is_array ( $parameters ))
			return '';
		list ( $sql, $matchInfo ) = $this->_parseStatement ( $format, $parameters, true );
		list ( $tables, $fields ) = $this->_parseMatchs ( $matchInfo );
		$this->_smallHook ( trim ( substr ( $format, 0, 7 ) ), $sql, $tables, $fields );
		return $sql;
	}
	/**
	 * 私有解析匹配结果，获取数据表名称和条件字段
	 * @param $matchInfo  匹配结果数组 
	 */
	function _parseMatchs($matchInfo) {
		if (! $matchInfo) {
			return array (array (), array () );
		}
		foreach ( $matchInfo as $k => $v ) {
			if (strpos ( $k, 'pw_table' ) !== false) {
				$tables [] = $v;
				unset ( $matchInfo [$k] );
			}
		}
		return array ($tables, $matchInfo );
	}
	
	/**
	 * 私有解析SET部分结构函数
	 * @param $arrays
	 */
	function _parseSetSQL($arrays, $expr = null) {
		if (! is_array ( $arrays ) && ! $expr) {
			return '';
		}
		$sets = " SET ";
		if ($expr) {
			foreach ( $expr as $v ) {
				$sets .= " " . $v . ",";
			}
		}
		if ($arrays) {
			foreach ( $arrays as $k => $v ) {
				$sets .= " " . S::sqlMetadata ( $k ) . " = " . S::sqlEscape ( $v ) . ",";
			}
		}
		$sets = trim ( $sets, "," );
		return ($sets) ? $sets : '';
	}
	/**
	 * 私有解析格式模板，并实现格式与参数匹配
	 * @param $statement
	 * @param $conditions
	 */
	function _parseStatement($statement, $conditions, $isCheck = false) {
		if (! $statement || ! is_array ( $conditions ))
			return array ('', array () );
		preg_match_all ( '/:(\w+)/', $statement, $matchs );
		if (! $matchs [0])
			return array ('', array () );
		$fields = array ();
		//fix WooYun-2011-02720.感谢Ray在 http://www.wooyun.org/bugs/wooyun-2010-02720 上的反馈
		$seg = randstr(4);
		$statement = preg_replace ('/(:\w+)/', $seg . '${1}' . $seg, $statement );
		foreach ( $matchs [0] as $k => $field ) {
			$fields [$matchs [1] [$k]] = $conditions [$k];
			$param = (is_array ( $conditions [$k] )) ? S::sqlImplode ( $conditions [$k] ) : (($isCheck && strpos ( $field, 'pw_table' ) !== false) ? $conditions [$k] : S::sqlEscape ( $conditions [$k] ));
			$statement = str_replace ( $seg . $field . $seg, $param, $statement );
		}
		return array ($statement, $fields );
	}
	
	/**
	 * 私有解析查询字段部分
	 * @param $columns    字段数组
	 * @param $statements 特殊查询语句
	 */
	function _parseColumns($columns, $statements) {
		$sql = '';
		if ($columns) {
			foreach ( $columns as $column ) {
				$sql .= S::sqlMetadata ( $column ) . ",";
			}
		}
		if ($statements) {
			foreach ( $statements as $statement ) {
				$sql .= $statement . ",";
			}
		}
		return ($sql) ? rtrim ( $sql, ',' ) : '*';
	}
	/**
	 * 私有解析分组语句
	 * @param $groupBy
	 */
	function _parseGroupBy($groupBys) {
		if (! $groupBys)
			return '';
		$sql = ' GROUP BY ';
		foreach ( $groupBys as $field ) {
			$sql .= S::sqlMetadata ( $field ) . ',';
		}
		$sql = rtrim ( $sql, ',' );
		return $sql;
	}
	/**
	 * 私用解析排序语句
	 * @param $orderBy
	 */
	function _parseOrderBy($orderBy) {
		if (! $orderBy)
			return '';
		$orderBy = (is_array ( $orderBy )) ? $orderBy : array ($orderBy );
		$sql = " ORDER BY ";
		foreach ( $orderBy as $field => $sort ) {
			if (! in_array ( strtolower ( $sort ), array (PW_DESC, PW_ASC ) ))
				continue;
			$sql .= S::sqlMetadata ( $field ) . " " . $sort . ",";
		}
		$sql = rtrim ( $sql, ',' );
		return $sql;
	}
	/**
	 * 私有解析分页语句
	 * @param $offset
	 * @param $row_count
	 */
	function _parseLimit($limits) {
		$offset = S::int ( $limits [0] );
		$row_count = S::int ( $limits [1] );
		return ($offset >= 0 && $row_count > 0) ? " LIMIT " . $offset . "," . $row_count : '';
	}
	/**
	 * 调试SQL语句
	 * @param $sql
	 */
	function _debug($sql) {
		if (PW_DEBUG) {
			var_dump ( $sql );
		}
	}
	/**
	 * 小钩子接口,用于实现可扩展
	 * @param $operate    操作行为,可选insert/replace/update/select
	 * @param $tableName  数据表名称
	 * @param $fields     数据条件字段
	 */
	function _smallHook($operate, $sql, $tableNames = array(), $fields = array(), $expand = array()) {
		$this->_debug ( $sql );
		Perf::gatherQuery ( $operate, $tableNames, $fields, $expand );
		return true;
	}
}