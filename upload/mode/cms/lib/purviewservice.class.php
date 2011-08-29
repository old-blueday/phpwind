<?php
!defined('P_W') && exit('Forbidden');

/**
 * 用户权限管理相关
 * @author yishuo
 */
class PW_PurviewService {

	/**
	 * 根据权限ID删除权限列表
	 * @param int $id
	 */
	function deletePruviewById($id) {
		$purviewDAO = $this->_loadPurviewDAO();
		/* @var $purviewDAO PW_PurviewDB */
		return $purviewDAO->delete($id);
	}

	/**
	 * 根据用户名更新用户权限
	 * @param string $username
	 * @param array $columns
	 * @param string $super
	 * @return string
	 */
	function updatePruviewByUser($username = '', $columns = array(), $super = 0, $pid) {
		$userservice = $this->_loadUserService();
		if (!$userservice->getByUserName($username)) return false;
		$purviewDAO = $this->_loadPurviewDAO();
		/* @var $purviewDAO PW_PurviewDB */
		$purviewDAO->update(array('username' => $username, 'super' => $super, 'columns' => $columns), $pid);
		return true;
	}

	/**
	 * 添加用户的权限信息
	 * @param string $username
	 * @param array $columns
	 * @param string $super
	 * @return boolean
	 */
	function insertPruviewByUser($username = '', $columns = array(), $super = 0) {
		if (strpos($username,',') === false) return $this->_insertPruviewByUser($username, $columns, $super);
		$username = explode(',', $username);
		foreach ($username as $value) {
			$this->_insertPruviewByUser($value, $columns, $super);
		}
		return true;
	}

	function _insertPruviewByUser($username = '', $columns = array(), $super = 0) {
		$userservice = $this->_loadUserService();
		if (!$userservice->getByUserName($username)) return false;
		$purviewDAO = $this->_loadPurviewDAO();
		/* @var $purviewDAO PW_PurviewDB */
		return $purviewDAO->insert(array('username' => $username, 'super' => $super, 'columns' => $columns));
	}

	/**
	 * 根据用户名获得所有的用户权限信息
	 * @param string $username
	 * @param int $page
	 * @param int $perPage
	 * @return array
	 */
	function findAll($username = '', $page = 1, $perPage = 20) {
		$page = (intval($page) < 1) ? 1 : intval($page);
		$perPage = (int) $perPage;
		$purviewDAO = $this->_loadPurviewDAO();
		$results = $purviewDAO->findAll(array('username' => $username), $page, $perPage);
		return $results;
	}

	/**
	 * 根据权限ID获得用户权限信息
	 * @param int $id
	 */
	function findPurviewById($id) {
		$purviewDAO = $this->_loadPurviewDAO();
		$results = $purviewDAO->get($id);
		return $results;
	}

	/* column service */
	
	/**
	 * 根据栏目ID获得栏目名称
	 * @param unknown_type $cids
	 * @return multitype:
	 */
	function getColumnNameByCIds($cids) {
		$_columnService = $this->_loadColumnService();
		/* @var $_columnService PW_ColumnService */
		return $_columnService->getColumnNameByCIds($cids);
	}

	/**
	 * 获得栏目列表
	 * @return array:
	 */
	function getAllColumns() {
		$_columnService = $this->_loadColumnService();
		return $_columnService->getAllOrderColumns();
	}

	/**
	 * @return int
	 */
	function countPurview() {
		$purviewDAO = $this->_loadPurviewDAO();
		return $purviewDAO->count();
	}

	function updatePurviewCache() {
		$purviews = $this->findAll('', 0, 0);
		$editadmin = array();
		foreach ($purviews as $pruview) {
			foreach ($pruview['columns'] as $column) {
				$editadmin[$column][] = $pruview['username'];
			}
		}
		setConfig('cms_editadmin', $editadmin, null, true);
		updatecache_conf('cms', true);
	}

	function _loadPurviewDAO() {
		return C::loadDB('purview');
	}

	function _loadUserService() {
		return L::loadClass('userservice', 'user');
	}

	function _loadColumnService() {
		return C::loadClass('columnservice');
	}

}