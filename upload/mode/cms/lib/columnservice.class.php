<?php
!defined('P_W') && exit('Forbidden');

class PW_ColumnService {
	var $_module_name = 'column';

	/**
	 * 获得所有栏目列表
	 * @return array
	 */
	function findAllColumns() {
		/* @var $_columnDB PW_ColumnDB */
		$_columnDB = $this->loadColumnDB();
		return $_columnDB->getColumns();
	}

	function getColumnByName($name) {
		$_columnDB = $this->loadColumnDB();
		return $_columnDB->getColumnByName($name);
	}

	/**
	 * 根据栏目ID获得所有子栏目
	 * @param int $id (默认$id = 0 , 一级栏目)
	 * @param int $num (默认$num = 0 ， 取全部结果)
	 * @return array
	 */
	function getSubColumnsById($id = 0, $num = 0) {
		$columns = $this->findAllColumns();
		if (!$id) return $this->_getTopColumn($columns, $num);
		return $this->_getSubColumns($columns, $id, $num);
	}

	function _getTopColumn($columns, $num) {
		$_tmp = array();
		foreach ($columns as $column) {
			if (!$column['parent_id']) {
				$_tmp[$column['column_id']] = $column;
				if (!$num && count($_tmp) >= $num) break;
			}
		}
		return $_tmp;
	}

	function _getSubColumns($columns, $id, $num) {
		$_tmp = array();
		foreach ($columns as $column) {
			if ($column['parent_id'] == $id) {
				$_tmp[$column['column_id']] = $column;
				if (!$num && count($_tmp) >= $num) break;
			}
		}
		return $_tmp;
	}

	/**
	 * 同级栏目展开,获得栏目
	 * @param int $cid
	 */
	function getColumnsAndSubColumns($cid = '0') {
		$columns = $this->findAllColumns();
		$_parentid = '0';
		if ($cid) {
			$_parentid = $this->_getParentid($columns, $cid);
		}
		return $this->_getSameLevelColumns($columns, $_parentid);
	}

	/**
	 * 获得父ID
	 * @param array $columns
	 * @param int $cid
	 */
	function _getParentid($columns, $cid) {
		foreach ($columns as $column) {
			if ($column['column_id'] == $cid) {
				$_parentid = $column['parent_id'];
			}
		}
		return $_parentid;
	}

	/**
	 * 获得同一级别的栏目列表
	 * @param array $columns
	 * @param int $_parentid
	 */
	function _getSameLevelColumns($columns, $_parentid) {
		$result = array();
		foreach ($columns as $column) {
			if ($column['column_id'] == $_parentid) {
				$result['parent'] = $column;
			}
			if ($column['parent_id'] == $_parentid) {
				$result['sub'][] = $column;
			}
		}
		return $result;
	}

	/**
	 * 获得用户有管理权限的栏目
	 * @param unknown_type $username
	 * @return Ambigous <multitype:, unknown>
	 */
	function getAllPurviewColumns($username) {
		$cms_editadmin = L::config('cms_editadmin', 'cms_config');
		$cms_editadmin = is_array($cms_editadmin) ? $cms_editadmin : array();
		$_keys = array_keys($cms_editadmin);
		$columns = array();
		foreach ($_keys as $key) {
			if (in_array($username, $cms_editadmin[$key])) $columns[] = $key;
		}
		return $columns;
	}

	/**
	 * 获得所有栏目排序后的列表
	 * @return array
	 */
	function getAllOrderColumns($id = 0,$username = '') {
		if (empty($id)) $id = 0;
		$columns = $this->findAllColumns();
		$result = array();
		if ($id) $result[$id] = $columns[$id];
		foreach ($columns as $column) {
			//if ($username && !$column['allowoffer'] && !checkEditPurview($username,$column['column_id'])) continue;
			if ($column['parent_id'] == $id) {
				$column['level'] = 0;
				if ($column['allowoffer'] || !$username || $username && checkEditPurview($username,$column['column_id'])) {
					$result[$column['column_id']] = $column;
				}
				$this->_getColumns($columns, $column['column_id'], $result, 1,$username);
			}
		}
		return $result;
	}

	function getCurrentAndSubColumns($id) {
		$columns = $this->findAllColumns();
		if (!$id) return array($this->_getTopColumns($columns), array());
		return array($this->_getSubs($columns, $id), $columns[$id]);
	}

	function _getTopColumns($columns) {
		$_tmp = array();
		foreach ($columns as $column) {
			if (!$column['parent_id']) {
				$_tmp[] = $column;
			}
		}
		return $_tmp;
	}

	function _getSubs($columns, $id) {
		$_tmp = array();
		foreach ($columns as $column) {
			if ($column['parent_id'] == $id) {
				$_tmp[] = $column;
			}
		}
		return $_tmp;
	}
		
	
	
	/**
	 * 更新栏目的SEO信息
	 * @param array $data
	 * @return boolean
	 */
	function updateColumnSEO($data) {
		$_columnDB = $this->loadColumnDB();
		return $_columnDB->updateColumnSEO($data['cid'], $data['seotitle'], $data['seodesc'], $data['seokeywords']);
	}

	/**
	 * 根据栏目ID获得栏目详情
	 * @param int $cid
	 * @return array
	 */
	function findColumnById($cid) {
		$_columnDB = $this->loadColumnDB();
		$_columns = $_columnDB->getColumn($cid);
		return $_columns[0];
	}

	/**
	 * 根据栏目的ID获得栏目类表
	 * @param array $cids
	 * @return Array
	 */
	function findColumnByIds($cids) {
		$_columnDB = $this->loadColumnDB();
		return $_columnDB->getColumn($cids);
	}

	/**
	 * 根据栏目ID获得栏目名称
	 * @param array $ids
	 * @return array
	 */
	function getColumnNameByCIds($ids) {
		$channles = $this->findColumnByIds($ids);
		$names = '';
		foreach ($channles as $value) {
			$names .= $value['name'] . ', ';
		}
		return trim($names, ' ,');
	}

	/**
	 * 获得栏目下拉选项信息
	 */
	function getColumnOptions($cid = '0') {
		$columns = $this->findAllColumns();
		$options = "<option value=''>一级栏目</option>";
		foreach ($columns as $c) {
			if (!$c['parent_id'] && $cid != $c['column_id'] ) {
				$options .= "<option value='" . $c['column_id'] . "'>" . $c['name'] . "</option>";
				foreach ($columns as $c1) {
					if ($c['column_id'] == $c1['parent_id'] && $c1['column_id'] != $cid) {
						$options .= "<option value='" . $c1['column_id'] . "'>" . "&nbsp;--" . $c1['name'] . "</option>";
					}
				}
			}
		}
		return $options;
	}

	/**
	 * 添加栏目
	 * @param unknown_type $datas
	 * @return string
	 */
	function insertColumns($datas) {
		$_columnDB = $this->loadColumnDB();
		return $_columnDB->insertColumn($datas);
	}

	/**
	 * 更新栏目
	 * @param unknown_type $cid
	 * @param unknown_type $data
	 */
	function updateColumn($cid, $data) {
		$_columnDB = $this->loadColumnDB();
		$data = array('parent_id' => $data[0], '`name`' => $data[1], '`order`' => $data[2], 'allowoffer' => $data[3], 
			'seotitle' => $data[4], 'seodesc' => $data[5], 'seokeywords' => $data[6]);
		return $_columnDB->updateColumn($cid, $data);
	}

	function deleteColumn($cid) {
		$_columnDB = $this->loadColumnDB();
		return $_columnDB->deleteColumnById($cid);
	}

	function updateColumnOrders($data) {
		$_columnDB = $this->loadColumnDB();
		foreach ($data as $id => $order) {
			if (!$_columnDB->updatecolumnOrder($id, $order)) return false;
		}
		return true;
	}

	/* article service */
	function getArticlesByColumeId($cid) {
		$articleService = $this->loadArticleService();
		/* @var $articleService PW_ArticleService */
		if (!is_array($cid)) $cid = array($cid);
		return $articleService->searchAtricles($cid);
	}

	function loadArticleService() {
		return C::loadClass('articleservice');
	}

	function loadColumnDB() {
		return C::loadDB($this->_module_name);
	}

	function _getColumns($columns, $cid, & $result, $l = 1,$username=0) {
		foreach ($columns as $c) {
			//if ($username && !$c['allowoffer'] && !checkEditPurview($username,$c['column_id'])) continue;
			if ($c['parent_id'] == $cid) {
				$c['level'] = $l;
				if ($c['allowoffer'] || !$username || $username && checkEditPurview($username,$c['column_id'])) {
					$result[$c['column_id']] = $c;
				}
				$this->_getcolumns($columns, $c['column_id'], $result, $l + 1,$username);
			}
		}
	}
}
?>