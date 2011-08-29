<?php
!defined('P_W') && exit('Forbidden');

class PW_Datanalyse {
	var $datanalyseDB;
	var $actions = array();
	var $tags = array();
	var $nums = array();
	var $pk = 'id';
	
	var $overtime = 30; //超时时间30天
	var $top = 200;

	function PW_Datanalyse() {
		$this->__construct();
	}

	function __construct() {
		$this->datanalyseDB = L::loadDB('datanalyse', 'datanalyse');
		/* @var $this->datanalyseDB PW_DatanalyseDB */
		$this->_setActions();
	}

	/**
	 * 根据action获得指定条数的数据
	 * @param string/array $action
	 * @param int $num
	 * @param int $time
	 */
	function getDataAndNumsByAction($action, $num, $time = '') {
		if (!$this->_filterAction($action)) return array();
		$this->_getTagsByAction($action, $num, $time);
		$data = $this->_getDataByTags();
		$this->_clearNotExistData($data,$action);
		return $this->_sortResultData($data);
	}
	
	/**
	 * 根据action获取热门文章列表
	 * @param string/array $action
	 * @param int $num
	 * @param int $time
	 */
	function getHotArticleByAction($action, $num, $time = '') {
		if (!$this->_filterAction($action)) return array();
		$this->_formatResultData($this->datanalyseDB->getDataOderByTag($action, $num, $time));
		return $this->_getHotArticlesByTags();
	}

	function _clearNotExistData($data, $action) {
		if (count($data) == count($this->tags)) return;
		if (is_array($action)) return;
		$_notExist = $_data = array();
		foreach ($data as $key => $value) {
			$_data[] = $value[$this->pk];
		}
		foreach ($this->tags as $v) {
			if (!in_array($v, $_data)) {
				$_notExist[] = $v;
			}
		}
		if ($_notExist) {
			$this->datanalyseDB->deleteDataByActionAndTag($action, $_notExist);
		}
		return;
	}

	/**
	 * @param array $data
	 */
	function _sortResultData($data) {
		$_tmp = array();
		foreach ($this->nums as $key => $value) {
			foreach ((array) $data as $k => $var) {
				if ($var[$this->pk] == $key) {
					$var['num'] = $value;
					$_tmp[] = $var;
					unset($data[$k]);
					break;
				}
			}
		}
		return $_tmp;
	}

	/**
	 * 设置一组actions
	 */
	function _setActions() {
		$this->actions = array_merge($this->actions, (array) $this->_getExtendActions());
	}

	/**
	 * 获得评价分类型
	 * @return multitype:
	 */
	function _getExtendActions() {
		return array();
	}

	/**
	 * 根据类型获取热榜数据
	 * @param string $action
	 * @param int $num
	 */
	function _getTagsByAction($action, $num, $time) {
		if (is_array($action)) {
			$this->_formatResultData($this->datanalyseDB->getTagsByActionsAndTime($action, $num, $time));
		} else {
			$this->_formatResultData($this->datanalyseDB->getTagsByActionAndTime($action, $num, $time));
		}
	}

	/**
	 * 格式化結果
	 * @param array $data
	 */
	function _formatResultData($data) {
		foreach ((array) $data as $key => $value) {
			$this->tags[] = $value['tag'];
			$this->nums[$value['tag']] = $value['nums'];
		}
	}

	/**
	 * 过滤非法action类型，如果action不存在返回空
	 * @param string $action
	 */
	function _filterAction($actions) {
		!is_array($actions) && $actions = (array) $actions;
		foreach ($actions as $var) {
			if (!in_array($var, $this->actions)) return false;
		}
		return true;
	}

	/**
	 * 根据$action类型清理热榜数据
	 * @param string $action
	 */
	function clearData($action) {
		$_overTime = $this->_getCurrentTime() - 86400 * $this->overtime;
		$this->_clearOverTimeData($_overTime);
		$this->_clearOtherData($_overTime, $action);
	}

	/**
	 * 获取当前时间戳
	 * @return Ambigous <number, string, unknown>
	 */
	function _getCurrentTime() {
		global $timestamp;
		return PwStrtoTime(get_date($timestamp, 'Y-m-d'));
	}

	/**
	 * @param int $overtime
	 * @param string $action
	 */
	function _clearOtherData($overtime, $action) {
		for ($index = 0; $index <= $this->overtime; $index++) {
			$time = $overtime + $index * 24 * 60 * 60;
			$rt = $this->datanalyseDB->getMaxNumByActionAndTime($action, $time, $this->top);
			if ($rt) {
				$this->datanalyseDB->deleteDataByTimeAndAction($action, $time, $rt);
			}
		}
	}

	/**
	 * 清理超时数据
	 */
	function _clearOverTimeData($time) {
		$this->datanalyseDB->_deleteDataByTime($time);
	}

	/**
	 * 获得最后一次清理的时间，如果返回0则始终清理
	 * 同时写入现在的清理时间
	 * @return number
	 */
	function _getLastClearTime($action) {
		return $this->_readFileByKey($action, $this->_getCurrentTime());
	}

	/**
	 * 根据KEY=>VALUE读写文件
	 * 读取原有的KEY的值并写入新的值
	 * @param string/array $key
	 * @param string $value
	 * @return string
	 */
	function _readFileByKey($key, $value = '') {
		$_filename = D_P . "data/bbscache/datanalyse.php";
		//* if (file_exists($_filename)) include pwCache::getPath($_filename);
		if (file_exists($_filename)) extract(pwCache::getData($_filename, false));
		$_data = "\$overtimes=array(\r\n";
		$_result = '';
		foreach ((array) $overtimes as $k => $var) {
			if ($key == $k) {
				$_result = $var;
				$_data .= $value ? "\t\t'" . $k . "'=>'" . $value . "',\r\n" : "\t\t'" . $k . "'=>'" . $var . "',\r\n";
			} else {
				$_data .= "\t\t'" . $k . "'=>'" . $var . "',\r\n";
			}
		}
		$_data .= "\t)";
		pwCache::setData($_filename, "<?php\r\n" . $_data . "\r\n?>");
		return $_result;
	}

}
?>