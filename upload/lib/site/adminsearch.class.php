<?php
!function_exists('readover') && exit('Forbidden');

/**
 * 后台搜索工具类
 *
 * @package Tool
 * @copyright phpwind
 * @author xiaolang
 */
class AdminSearch {
	var $keyword;
	var $purview = array();
	var $lang = array();
	var $result = array();

	/**
	 * 构造器
	 *
	 * @global $admin_file
	 * @see GetLang
	 * @see adminRightCheck
	 * @param string $keyword 搜索关键字
	 */
	function AdminSearch($keyword) {
		global $admin_file;
		require GetLang('purview');
		require GetLang('search');
		foreach ($purview as $key => $value) {
			if (!adminRightCheck($key)) {
				unset($purviewp[$key]);
				unset($search[$key]);
			}
		}
		$this->purview = &$purview;
		$this->lang = &$search;
		$this->keyword = $keyword;
	}

	/**
	 * 搜索
	 *
	 * @return bool|array
	 */
	function search() {
		if (!$this->keyword) return false;
		$this->searchPurview();
		$this->searchLang();
		return $this->result;
	}

	function searchPurview() {
		foreach ($this->purview as $key => $value) {
			if ($this->_strpos($value[0], $this->keyword) !== false) {
				$this->initResult($key);
			}
		}
	}

	function searchLang() {
		foreach ($this->lang as $key => $value) {
			foreach ($value as $k => $val) {
				if (is_array($val)) {
					foreach ($val as $v) {
						if ($this->_strpos($v, $this->keyword) !== false && $this->initResult($key)) {
							if (!isset($this->result[$key]['lang'][$k])) {
								$this->result[$key]['lang'][$k] = array($val['name']);
							}
							$this->result[$key]['lang'][$k][] = $this->redColorKeyword($v);
						}
					}
				} else {
					if ($this->_strpos($val, $this->keyword) !== false && $this->initResult($key)) {
						$this->result[$key]['lang'][] = $this->redColorKeyword($val);
					}
				}
			}
		}
	}

	/**
	 * 追加查询结果
	 *
	 * @param string $key
	 */
	function initResult($key) {
		if (array_key_exists($key, $this->purview) && !isset($this->result[$key])) {
			$this->result[$key] = array(
				'name' => strip_tags($this->purview[$key][0]),
				'url' => isset($this->lang[$key]['baseUrl']) ? $this->lang[$key]['baseUrl'] : $this->purview[$key][1],
				'lang' => array()
			);
		}
		return isset($this->result[$key]);
	}

	function redColorKeyword($text) {
		return preg_replace("/".$this->keyword."/i", "<font color='red'>" .$this->keyword. "</font>", $text);
	}

	function _strpos($string, $find) {
		if (function_exists('stripos')) {
			return stripos($string, $find);
		}
		return strpos($string, $find);
	}

}
