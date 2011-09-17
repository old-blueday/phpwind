<?php
!function_exists('readover') && exit('Forbidden');
/**
 * 搜索统计
 * @author luomingqu 2010-11-16
 * @version phpwind 8.3
 */
class PW_KeywordStatistic {
	var $_keyword;
	var $_fileName = 'keywordstatistic.php';
	var $_filePath = '';
	var $_fileSize;
	var $_maxFileSizeLimit = 1024;
	var $_timestamp;
	var $_lastUpdateTime;
	var $_statisticTimeNode = 86400; //86400 24*3600   当前时间-上次时间>1天，执行统计操作
	
	function PW_KeywordStatistic() {
		global $timestamp;
		$this->_filePath = D_P . 'data/bbscache/'.$this->_fileName;
		$this->_fileSize = $this->getFileSize();
		$this->_timestamp = $timestamp;
	}
	
	function init($keyword) {
		$this->_keyword = $keyword;
		$this->_lastUpdateTime = $this->getLastUpdateTime();
	}
	
	function execute() {
		$this->_writeover();
		$this->update();
	}
	
	function _writeover() {
		$this->_keyword = trim($this->_filterCheckKeyword($this->_keyword));
		if (!$this->_keyword) return false;
		writeover($this->_filePath, $this->_parsetxt($this->_keyword), 'ab+');
		return true;
	}
		
	function update() {
		if ($this->_fileSize && ($this->_fileSize > $this->_maxFileSizeLimit || ($this->_timestamp - $this->_lastUpdateTime > $this->_statisticTimeNode))) {
			$this->_updateDb();
			P_unlink($this->_filePath);
		}
		return true;
	}
	
	function _updateDb() {
		global $db;
		$fileContent = $this->_getFileContent();
		$temparray = explode("\t",$fileContent);
		$data = s::isArray($temparray) ? array_count_values($temparray) : array();
		$nowtime = PwStrtoTime(get_date($this->_timestamp,'Y-m-d'));
		$sql = array();
		foreach ($data as $key => $val) {
			$key = trim($this->_filterCheckKeyword($key));
			if (!$key) continue;
			$sql[] = array($key,$val,$nowtime);
			if(++$count>1000) break;
		}
		if (!$sql) return false;
		$db->query("INSERT INTO pw_searchstatistic(keyword,num,created_time) VALUES " . S::sqlMulti($sql));
		$deleteTime = $this->_timestamp - 86400*90;
		$db->query("DELETE FROM pw_searchstatistic WHERE created_time < $deleteTime");
		return true;
	}
	
	function _getFileContent() {
		$fileContent = readover($this->_filePath);
		return str_replace( array("<?php die;?>"), array(" "), $fileContent);
	}

	function _filterCheckKeyword($keyword) {
		if (!$keyword) return false;
		return s::stripTags(str_replace ( array ("&#160;", "&#61;", "&nbsp;", "&#60;", "<", ">", "&gt;", "(", ")", "&#41;" ), array (" " ), $keyword ));
	}

	function getFileSize() {
		if (!file_exists($this->_filePath)) return false;
		return @filesize($this->_filePath);
	}
	
	function getLastUpdateTime() {
		if (!file_exists($this->_filePath)) return $this->_timestamp;
		$time = pwFilemtime($this->_filePath);
		return PwStrtoTime(get_date($time,'Y-m-d'));
	}

	function _parsetxt($txt) {	
		$txt = (!$this->_filePath || !$this->_fileSize) ? "<?php die;?>\n".$txt : $txt;
		return trim($txt)."\t";
	}
}