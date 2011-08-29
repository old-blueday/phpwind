<?php
!function_exists('readover') && exit('Forbidden');

/**
 * 主题印戳
 *
 * @package Thread
 */
class PW_OverPrint {
	var $_db = null;
	/**
	 * 文件缓存
	 * @var string
	 */
	var $_filename = "overprint.php";
	/**
	 * 是否开启文件缓存功能
	 * @var bool
	 */
	var $_cache = true; 
	function PW_OverPrint() {
		global $db;
		$this->_db = & $db;
	}
	/*
	* 获取图标
	*/
	function getOverPrintIcons() {
		$dictory = R_P . $this->getIconPath();
		if (!is_dir($dictory)) {
			return array();
		}
		$files = array();
		$handler = opendir($dictory);
		while (false !== ($file = readdir($handler))) {
			if ($file == "." || $file == ".." || !$this->checkIcon($file)) {
				continue;
			}
			$files[] = $file;
		}
		return $files;
	}
	function checkIcon($file) {
		if (!$file) {
			return false;
		}
		$ext = strtolower(substr($file, strrpos($file, ".") + 1));
		if (!in_array($ext, $this->getIconExt())) {
			return false;
		}
		return true;
	}
	/**
	 * 帖子实现
	 * 
	 * 注意，$operate与$oid其中只有一个有值，分别对应操作管理和一般图标
	 * @param $tid 帖子ID
	 * @param $operate 操作类型
	 * @param $oid 无关联ID
	 */
	function suckThread($tid, $operate = '', $oid = '') {
		if (is_array($tid)) {
			foreach($tid as $v) {
				$this->_suckThread($v, $operate, $oid);
			}
		}
		return $this->_suckThread($tid, $operate, $oid);
	}
	function _suckThread($tid, $operate = '', $oid = '') {
		$tid = intval($tid);
		if ($tid < 1) {
			return false;
		}
		if (!$operate && $oid < 0) {
			return false;
		}
		if ($operate) {
			$related = $this->getOperatesMaps($operate);
			if (!$related) {
				return false;
			}
			return $this->overprintThread($tid, $related);
		}
		$oid = intval($oid);
		if ($oid >= 0) {
			return $this->overprintThread($tid, $oid);
		}
	}
	/**
	 * 检查设置的关联操作是不是当前操作
	 */
	function checkThreadRelated($overprint, $operate, $tid) {
		if ($overprint != 2) {
			return false;
		}
		if ($operate == '') {
			return false;
		}
		$related = $this->getOperatesMaps($operate);
		$t_overprint = $this->getOverPrintByThreadId($tid);
		if ($t_overprint == $related) {
			return false;
		}
		return $t_overprint;
	}
	function getOverPrintByThreadId($tid) {
		$pw_tmsgs = GetTtable($tid);
		return $this->_db->get_value("SELECT overprint FROM $pw_tmsgs WHERE tid=" . S::sqlEscape($tid) . " LIMIT 1");
	}
	/**
	 * 初始化获取帖子印戳
	 */
	function getOverPrintIcon($related) {
		if (empty($related)) {
			return '';
		}
		$overPrints = $this->getOverPrints();
		if (!$overPrints) {
			return '';
		}
		foreach($overPrints as $overprint) {
			if ($related < 0 && $overprint['related'] == $related) {
				return $this->getIconPath() . "/" . $overprint['icon'];
			}
			if ($related > 0 && $overprint['id'] == $related) {
				return $this->getIconPath() . "/" . $overprint['icon'];
			}
		}
		return '';
	}
	function overprintThread($tid, $related) {
		$pw_tmsgs = GetTtable($tid);
		//* return $this->_db->update("UPDATE $pw_tmsgs SET overprint=" . S::sqlEscape($related) . " WHERE tid=" . S::sqlEscape($tid) . " LIMIT 1");
		return pwQuery::update($pw_tmsgs, 'tid=:tid', array($tid), array('overprint'=>$related));
	}
	
	function getMultiUnRelatedsHTML($fid, $tids) {
		$html = '<div style="width:270px;">';
		$html .= '<div class="h" onmousedown="read.move(event);" style="cursor: move;"><a href="javascript:;" onclick="closep();" class="adel">close</a>印戳设置</div>';
		$html .= '<input type="hidden" name="overprinttids" id="overprinttids" value="' . implode(',', $tids) . '" />';
		/*中间印戳选择 start*/
		$html .= '<div class="overprint_opl cc">';
		/*元素*/
		$list = $this->buildunRelatedsHTML($fid, '', '', true);
		$list = $list ? $list : "<div class=\"tac p10\">没有可选择的印戳</div>";
		$html .= $list;
		$html .= '</div>';
		/*中间印戳选择 end*/
		$html .= "</div>";
		$html .= '</div>';
		return $html;
	}
	
	function getMultiUnRelatedsHTMLWithoutDiv($fid, $tids) {
		$html = '<div class="overprint_opl cc" style="height: auto" id="odiv">';
		$list= $this->buildunRelatedsHTML($fid, '', '', true, true);
		$list = $list ? $list : "<div class=\"tac p10\">没有可选择的印戳</div>";
		$html .= $list . '</div>';
		return $html;
	}
	
	function getunRelatedsHTML($fid, $tid) {
		$isOverPrint = $this->getOverPrintByThreadId($tid);
		$html = '<div style="width:270px;">';
		$html .= '<div class="h" onmousedown="read.move(event);" style="cursor: move;"><a href="javascript:;" onclick="closep();" class="adel">close</a>印戳设置</div>';
		/*中间印戳选择 start*/
		$html .= '<div class="overprint_opl cc">';
		/*元素*/
		$list = $this->buildunRelatedsHTML($fid, $tid, $isOverPrint);
		$list = $list ? $list : "<div class=\"tac p10\">没有可选择的印戳</div>";
		$html .= $list;
		$html .= '</div>';
		/*中间印戳选择 end*/
		$isOverPrint && $html .= $this->buildunRelatedTxtLI($tid, $fid, 0, "移除印戳", '');
		$html .= "</div>";
		$html .= '</div>';
		return $html;
	}
	function buildunRelatedsHTML($fid, $tid, $isOverPrint, $isMulti = false, $withoutEvent = false) {
		$overprints = $this->getOverPrintUnRelateds();
		if (!$overprints) {
			return '';
		}
		foreach($overprints as $overprint) {
			if ($overprint['isopen'] == 0) { /*过滤*/
				continue;
			}
			$url = $this->getIconPath() . "/" . $overprint['icon'];
			$html .= $isMulti ?  $this->buildMultiUnRelatedLI($fid, $overprint['id'], $overprint['title'], $url,$withoutEvent) : $this->buildunRelatedLI($tid, $fid, $overprint['id'], $overprint['title'], $url, $isOverPrint);
		}
		return $html;
	}
	
	function buildMultiUnRelatedLI($fid, $oid, $title, $url, $withoutEvent = false) {
		$img = '<img src="' . $url . '" height="40" width="40" title="' . $title . '" />';
		return $withoutEvent ? "<a href=\"javascript:;\" onclick=\"setOid(this,'$oid');\">$img</a>" : '<a href="javascript:;" url="mawhole.php?action=multioverprint&step=2&ajax=1&fid=' . $fid . '&oid=' . $oid . '" onclick="return showOverPrint(this,1);">' . $img . '</a>';
	}
	
	function buildunRelatedLI($tid, $fid, $oid, $title, $url, $isOverPrint) {
		$style = ($isOverPrint == $oid) ? "current" : "";
		$img = '<img src="' . $url . '" height="40" width="40" title="' . $title . '" />';
		return '<a class="' . $style . '" href="javascript:;" url="mawhole.php?action=overprint&step=2&ajax=1&fid=' . $fid . '&seltid=' . $tid . '&oid=' . $oid . '" onclick="return showOverPrint(this);">' . $img . '</a>';
	}
	function buildunRelatedTxtLI($tid, $fid, $oid, $title, $url) {
		return '<div class="tar p10"><a href="javascript:;" url="mawhole.php?action=overprint&step=2&ajax=1&fid=' . $fid . '&seltid=' . $tid . '&oid=' . $oid . '" class="bta" onclick="return showOverPrint(this);">' . $title . '</a></div>';
	}
	/**
	 * 设置文件缓存
	 */
	function setFileCache() {
		if (!$this->_cache) {
			return;
		}
		$overPrintDao = $this->_getOverPrintDao();
		$overPrints = $overPrintDao->getAll();
		$tmp_overPrints = "\$overPrints=" . pw_var_export($overPrints) . ";";
		pwCache::setData($this->getCacheFileName(), "<?php\r\n" . $tmp_overPrints . "\r\n?>");
		return $overPrints;
	}
	/**
	 * 获取文件缓存
	 */
	function getFileCache() {
		if (!$this->_cache) {
			return array();
		}
		//* @include S::escapePath($this->getCacheFileName());
		extract(pwCache::getData(S::escapePath($this->getCacheFileName()), false));
		return $overPrints;
	}
	function getCacheFileName() {
		return D_P . "data/bbscache/" . $this->_filename;
	}
	function getIconPath() {
		return "images/overprint";
	}
	function getIconExt() {
		return array(
			'png',
			'gif',
			'jpeg',
			'bmp',
			'jpg'
		);
	}
	function getRelatedSelect($select, $name = "related", $id = "related") {
		$related = $this->getRelatedMaps();
		return $this->_buildSelect($related, $name, $id, $select);
	}
	function getStatusSelect($select, $name = "isopen", $id = "isopen") {
		$selects = $this->getStatus();
		return $this->_buildSelect($selects, $name, $id, $select);
	}
	function getStatus() {
		return array(
			0 => "关闭",
			1 => "启用"
		);
	}
	/**
	 * 关联操作maps
	 */
	function getRelatedMaps() {
		$related = array(
			0 => "无关联",
			'-1' => "精华",
			'-2' => "置顶",
			//'-3'    => "推荐",
			//'-4'    => "推送",
			'-5' => "加亮",
			'-6' => "提前",
			'-7' => "压帖",
			'-8' => "锁定",
			//'-20'    => "关闭"
			
		);
		return $related;
	}
	function getOperatesMaps($operate) {
		$related = array(
			'-1' => "digest", //精华
			'-2' => "headtopic", //置顶
			//'-3'    => "recommend",//推荐
			//'-4'    => "deliver",//推送
			'-5' => "headlight", //加亮
			'-6' => "pushtopic", //提前
			'-7' => "downtopic", //压帖
			'-8' => "lock", //锁定
			
		);
		$related = array_flip($related);
		if (!in_array($operate, array_keys($related))) {
			return false;
		}
		return $related[$operate];
	}
	function addOverPrint($fieldData) {
		(isset($fieldData['related']) && $fieldData['related'] != "-20") && $this->checkOverPrint($fieldData['related']);
		$overPrintDao = $this->_getOverPrintDao();
		$result = $overPrintDao->add($fieldData);
		if ($result) {
			$this->setFileCache();
		}
		return $result;
	}
	/**
	 * 检查是否已存在相对应的操作
	 */
	function checkOverPrint($related) {
		if ($related == '-20') {
			return false;
		}
		$result = $this->getOverPrintByRelated($related);
		if (!$result) {
			return false;
		}
		//更新旧的
		$overPrintDao = $this->_getOverPrintDao();
		$overPrintDao->update(array(
			'related' => 0
		), $result['id']);
		return true;
	}
	function checkRelated($operate) {
		$related = $this->getOperatesMaps($operate);
		if (!$related) {
			return false;
		}
		$result = $this->getOverPrintByRelated($related);
		if (!$result) {
			return false;
		}
		if ($result['isopen'] == 0) { /*是否开启*/
			return false;
		}
		return true;
	}
	function getOverPrintByRelated($related) {
		$overPrintDao = $this->_getOverPrintDao();
		return $overPrintDao->getByRelated($related);
	}
	function getOverPrintUnRelateds() {
		$overPrintDao = $this->_getOverPrintDao();
		return $overPrintDao->getUnRelateds();
	}
	function updateOverPrint($fieldData, $id) {
		isset($fieldData['related']) && $this->checkOverPrint($fieldData['related']);
		$overPrintDao = $this->_getOverPrintDao();
		$result = $overPrintDao->update($fieldData, $id);
		if ($result) {
			$this->setFileCache();
		}
		return $result;
	}
	function getOverPrints($usercache = true) {
		if($usercache){
			$overPrints = $this->getFileCache();
			if ($overPrints) {
				return $overPrints;
			}
		}
		$overPrints =  $this->setFileCache();
		return ($overPrints) ? $overPrints : array();
	}
	function deleteOverPrint($id) {
		$overPrintDao = $this->_getOverPrintDao();
		$result = $overPrintDao->delete($id);
		if ($result) {
			$this->setFileCache();
		}
		return $result;
	}
	function getOverPrint($id) {
		$overPrintDao = $this->_getOverPrintDao();
		return $overPrintDao->get($id);
	}
	function _getOverPrintDao() {
		$overPrintDao = L::loadDB('overprint', 'forum');
		return $overPrintDao;
	}
	/**
	 * 组装下拉框
	 */
	function _buildSelect($arrays, $name, $id, $select = '', $isEmpty = false) {
		if (!is_array($arrays)) {
			return '';
		}
		$html = '<select name="' . $name . '" id="' . $id . '">';
		($isEmpty == true) && $html .= '<option value=""></option>';
		foreach($arrays as $k => $v) {
			$selected = ($select == $k && $select != null) ? 'selected="selected"' : "";
			$html .= '<option value="' . $k . '" ' . $selected . '>' . $v . '</option>';
		}
		$html .= '</select>';
		return $html;
	}
}
