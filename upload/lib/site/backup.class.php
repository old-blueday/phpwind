<?php
!defined('P_W') && exit('Forbidden');

/**
 * 数据库备份
 * 
 */
class PW_Backup {
	
	var $db;
	var $backupTipLength;
	var $backupTip;
	
	function PW_Backup() {
		global $db;
		$this->db =& $db;
		$this->backupTip = $this->getBackupTip();
		$this->backupTipLength = strlen($this->backupTip);
	}
	
	/**
	 * 备份表数据
	 * @param $tabledb
	 * @param $tableid
	 * @param $start
	 * @param $sizelimit
	 * @param $insertmethod
	 * @param $filename
	 * @return array
	 */
	function backupData($tabledb, $tableid, $start, $sizelimit, $insertmethod, $filename = '') {
		if (!S::isArray($tabledb)) return array();
		$tableid = intval($tableid) ? intval($tableid) : 0;
		list($backupData, $totalRows, $tableSaveInfo) = array('', 0, array());
		$method = (strtolower($insertmethod) == 'common') ? '_backupDataCommonMethod' : '_backupDataExtendMethod';
		list($backupData, $tableid, $start, $totalRows, $tableSaveInfo) = $this->$method($tabledb, $tableid, $start, $sizelimit);
		$this->_recordTableSaveInfo($tableSaveInfo, $filename);
		return array($backupData, $tableid, $start, $totalRows);
	}
	
	/**
	 * 普通方式备份表数据
	 * @param $tabledb
	 * @param $tableid
	 * @param $start
	 * @param $sizelimit
	 * @return array
	 */
	function _backupDataCommonMethod($tabledb, $tableid, $start, $sizelimit) {
		list($writedRows, $backupData, $tableSaveInfo, $totalTableNum) = array(0, '', array(), count($tabledb));
		for ($i = $tableid; $i < $totalTableNum; $i++) {
			$totalRows = $this->_getTotalRows($tabledb[$i]);
			$flag = true;
			$tmpWritedRows = $writedRows;
			while ($flag) {
				$selectNum = $totalRows < 5000 ? $totalRows : 5000;
				list($query, $fieldNum) = $this->_selectData($tabledb[$i], $start, $selectNum);
				while ($result = $this->db->fetch_array($query, 2)) {
					$tmpData = "INSERT INTO" . S::sqlMetadata($tabledb[$i]) . " VALUES('" . $this->db->escape_string($result[0]) . "'";
					$tmpData .= $this->_buildFieldsData($fieldNum, $result) . ");\n";
					if ($sizelimit && (($this->backupTipLength + strlen($backupData) + strlen($tmpData) + 2) > $sizelimit * 1000)) {
						$tableSaveInfo[$tabledb[$tableid]] = array('start' => $tmpWritedRows, 'end' => -1);
						$flag = false;
						break 3;
					}
					$backupData .= $tmpData;
					$writedRows++;
					$start++;
				}
				$this->db->free_result($query);
				if ($start >= $totalRows) {
					$start = 0;
					break;
				}
			}
			$backupData .= "\n";
			$tableSaveInfo[$tabledb[$tableid++]] = array('start' => $tmpWritedRows, 'end' => $writedRows++);
		}
		return array($backupData, $tableid, $start, $totalRows, $tableSaveInfo);
	}
	
	/**
	 * 扩展方式备份表数据
	 * @param $tabledb
	 * @param $tableid
	 * @param $start
	 * @param $sizelimit
	 * @return array
	 */
	function _backupDataExtendMethod($tabledb, $tableid, $start, $sizelimit) {
		list($writedRows, $backupData, $tableSaveInfo, $totalTableNum) = array(0, '', array(), count($tabledb));
		for ($i = $tableid; $i < $totalTableNum; $i++) {
			$totalRows = $this->_getTotalRows($tabledb[$i]);
			$flag = true;
			$tmpWritedRows = $writedRows;
			$outFrontData = 'INSERT INTO' . S::sqlMetadata($tabledb[$i]) . ' VALUES';
			while ($flag) {
				$outTmpData = '';
				$selectNum = $totalRows < 1000 ? $totalRows : 1000;
				list($query, $fieldNum) = $this->_selectData($tabledb[$i], $start, $selectNum);
				while ($result = $this->db->fetch_array($query, 2)) {
					$tmpData = "('" . $this->db->escape_string($result[0]) . "'";
					$tmpData .= $this->_buildFieldsData($fieldNum, $result) . "),";
					if ($sizelimit && (($this->backupTipLength + strlen($backupData) + strlen($tmpData) + strlen($outTmpData) + strlen($outFrontData) + 2) > $sizelimit * 1000)) {
						$outTmpData && $backupData .= $outFrontData . rtrim($outTmpData, ',') . ";\n";
						$tableSaveInfo[$tabledb[$tableid]] = array('start' => $tmpWritedRows, 'end' => -1);
						$flag = false;
						break 3;
					}
					if (strlen($outFrontData) + strlen($outTmpData) + strlen($tmpData) > 768 * 1000) {
						break;
					}
					$outTmpData .= $tmpData;
					$start++;
				}
				$this->db->free_result($query);
				if ($outTmpData) {
					$backupData .= $outFrontData . rtrim($outTmpData, ',') . ";\n";
					$writedRows++;
				}
				if ($start >= $totalRows) {
					$start = 0;
					break;
				}
			}
			$backupData .= "\n";
			$tableSaveInfo[$tabledb[$tableid++]] = array('start' => $tmpWritedRows, 'end' => $writedRows++);
		}
		return array($backupData, $tableid, $start, $totalRows, $tableSaveInfo);
	}
	
	/**
	 * 获取一个表的总行数
	 * @param $table
	 */
	function _getTotalRows($table) {
		$tableStatus = $this->db->get_one('SHOW TABLE STATUS LIKE ' . S::sqlEscape($table));
		return intval($tableStatus['Rows']);
	}
	
	/**
	 * 获取数据
	 * @param $table
	 * @param $start
	 * @param $num
	 */
	function _selectData($table, $start, $num) {
		list($start, $num) = array(intval($start), intval($num));
		$sqlLimit = S::sqlLimit($start, $num);
		$query = $this->db->query('SELECT * FROM ' . S::sqlMetadata($table) . $sqlLimit);
		$fieldNum = $this->db->num_fields($query);
		return array($query, $fieldNum);
	}
	
	/**
	 * 组装每个字段的数据
	 * @param $total
	 * @param $result
	 */
	function _buildFieldsData($total, $result) {
		list($total, $data) = array(intval($total), '');
		if ($total < 2) return $data;
		for ($i = 1; $i < $total; $i++) {
			$data .= ",'" . $this->db->escape_string($result[$i]) . "'";
		}
		return $data;
	}
	
	/**
	 * 备份表结构
	 * @param $tabledb
	 * @param $dirname
	 * @param $isCompress
	 * @return bool
	 */
	function backupTable($tabledb, $dirname, $isCompress){
		list($dirname, $isCompress) = array(Pcv($dirname), intval($isCompress));
		if (!S::isArray($tabledb) || !$dirname) return false;
		$createSql = '';
		foreach($tabledb as $table){
			$createSql .= "DROP TABLE IF EXISTS `$table`;\n";
			$CreatTable = $this->db->get_one("SHOW CREATE TABLE $table");
			$CreatTable['Create Table'] = str_replace($CreatTable['Table'], $table, $CreatTable['Create Table']);
			$createSql .= $CreatTable['Create Table'] . ";\n\n";
		}
		$this->saveData($dirname . '/table.sql', $createSql, $isCompress);
		return true;
	}
	
	/**
	 * 备份文件提示
	 * @return string
	 */
	function getBackupTip() {
		global $wind_version, $timestamp, $PW;
		return "#\n# phpwind bakfile\n# version:" . $wind_version . "\n# time: " . get_date($timestamp,'Y-m-d H:i') . "\n# tablepre: $PW\n# phpwind: http://www.phpwind.net\n# --------------------------------------------------------\n\n\n";
	}
	
	/**
	 * 获取备份文件提示的行数
	 * @return int
	 */
	function getLinesOfBackupTip() {
		return substr_count($this->backupTip, "\n");
	}
	
	/**
	 * 保存数据到文件
	 * @param $filePath
	 * @param $data
	 * @param $isCompress
	 * @return bool
	 */
	function saveData($filePath, $data, $isCompress) {
		$filePath = Pcv($filePath);
		if (!trim($data) || !$filePath) return false;
		$filePath = $this->getSavePath() . $filePath;
		$this->createFolder(dirname($filePath));
		$data = $this->backupTip . $data;
		if ($isCompress && $this->_checkZlib()) {
			$zipService = $this->_getZipService();
			$filename = basename($filePath);
			$zipName = substrs($filename, strpos($filename, '.'), 'N') . '.zip';
			$filePath = dirname($filePath) . '/' . $zipName;
			$zipService->init();
			$zipService->addFile($data, $filename);
			$data = $zipService->getCompressedFile();
		}
		pwCache::writeover($filePath, $data);
		return true;
	}
	
	/**
	 * 记录表数据的保存文件跟位置
	 * @param $tableSaveInfo
	 * @param $filename
	 * @return bool
	 */
	function _recordTableSaveInfo($tableSaveInfo, $filename) {
		$filename = Pcv($filename);
		if (!$filename || !S::isArray($tableSaveInfo)) return false;
		$filePath = $this->getSavePath() . dirname($filename);
		$filename = basename($filename);
		$this->createFolder($filePath);
		$linesOfBackupTip = $this->getLinesOfBackupTip();
		foreach ($tableSaveInfo as $key => $value) {
			$value['start'] += $linesOfBackupTip;
			$value['end'] != -1 && $value['end'] += $linesOfBackupTip;
			$record .= $key . ':' . $filename . ',' . $value['start'] . ',' . $value['end'] . "\n";
		}
		pwCache::writeover($filePath . '/table.index', $record, 'ab+');
		return true;
	}
	
	/**
	 * 备份文件保存目录
	 * @return string
	 */
	function getSavePath() {
		return  D_P . 'data/sqlback/';
	}
	
	/**
	 * 生成文件前缀
	 * @return string
	 */
	function getDirectoryName() {
		global $timestamp, $wind_version;
		$version = str_replace('.', '-', $wind_version);
		return 'pw_' . $version . '_' . get_date($timestamp, 'YmdHis') . '_' . randstr(5);
	}
	
	/**
	 * 创建文件夹
	 * @param $path
	 */
	function createFolder($path) {
		$path = Pcv($path);
		if ($path && !is_dir($path)) {
			PW_Backup::createFolder(dirname($path));
			mkdir($path);
			chmod($path, 0777);
		}
	}
	
	/**
	 * 获取压缩服务
	 * @return object
	 */
	function _getZipService() {
		static $zipService;
		if (!$zipService) {
			L::loadClass('zip', 'utility', false);
			$zipService = new Zip();
		}
		return $zipService;
	}
	
	/**
	 * zlib扩展是否开启
	 */
	function _checkZlib() {
		return (extension_loaded('zlib') && function_exists('gzcompress')) ? true : false;
	}
}
?>