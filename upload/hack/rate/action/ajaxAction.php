<?php
!function_exists('readover') && exit('Forbidden');
S::gp ( array ("action", "rateconfig", "id", "show","typeid" ) );
class AjaxAction {

	var $_action;
	var $_rateconfig;
	var $_adminName;
	var $_id;
	var $_pwServer;
	var $_show;
	var $_typeId;

	function AjaxAction($register) {
		$this->_register ( $register );
		$this->_init ();
	}

	function _register($register) {
		$this->_action = &$register ['action'];
		$this->_rateconfig = &$register ['rateconfig'];
		$this->_adminName = &$register ['adminName'];
		$this->_pwServer = &$register ['pwServer'];
		$this->_id = &$register ['id'];
		$this->_show = &$register ['show'];
		$this->_typeId = &$register ['typeid'];
	}

	function _init() {
		if ($this->_action == "add" && strtolower ( $this->_pwServer ['REQUEST_METHOD'] ) == "post") {
			$this->_add ();
		}
		if ($this->_action == "update" && strtolower ( $this->_pwServer ['REQUEST_METHOD'] ) == "post") {
			$this->_update ();
		}
		$this->_render ();
	}

	function _add() {
		list ( $title, $icon, $typeid, $isopen, $jumpUrl ) = $this->_checkData ();
		$fieldData = array ("title" => $title, "icon" => $icon, "typeid" => $typeid, "isopen" => $isopen, "creator" => $this->_adminName );
		$rateService = $this->_getRateService();
		$result = $rateService->addRateConfig ( $fieldData );
		(! $result) ? adminmsg ( "对不起，增加评价选项失败", $jumpUrl ) : adminmsg ( "恭喜你，增加评价选项成功!", $jumpUrl );
	}

	function _update() {
		list ( $title, $icon, $typeid, $isopen, $jumpUrl ) = $this->_checkData ();
		$fieldData = array ("title" => $title, "icon" => $icon, "typeid" => $typeid, "isopen" => $isopen, "updater" => $this->_adminName );
		$rateService = $this->_getRateService();
		$result = $rateService->updateRateConfig ( $fieldData, $this->_id );
		(! $result) ? adminmsg ( "对不起，更新评价选项失败", $jumpUrl ) : adminmsg ( "恭喜你，更新评价选项成功!", $jumpUrl );
	}

	function _checkData() {
		$title = trim ( $this->_rateconfig ['title'] );
		$icon = trim ( $this->_rateconfig ['icon'] );
		$typeid = (in_array ( $this->_rateconfig ['typeid'], array (1, 2, 3 ) )) ? $this->_rateconfig ['typeid'] : 1;
		$isopen = (in_array ( $this->_rateconfig ['isopen'], array (1, 0 ) )) ? $this->_rateconfig ['isopen'] : 1;
		$jumpUrl = $this->_getDefaultUrl () . "&typeid=" . $typeid;
		if ($title == "" || $icon == "") {
			adminmsg ( "对不起，标题或图标不能为空不能为空", $jumpUrl );
		}
		if (strlen ( $title ) > 6) {
			adminmsg ( "对不起，标题长度不能大于6个字节", $jumpUrl );
		}
		$iconExt = substr ( $icon, strrpos ( $icon, "." ) + 1 );
		if (! in_array ( $iconExt, array ("gif", "png", "jpg", "jpeg" ) )) {
			adminmsg ( "对不起，图标格式不正确，请确定后缀是gif,png,jpg或jpeg", $jumpUrl );
		}
		return array ($title, $icon, $typeid, $isopen, $jumpUrl );
	}

	function _render() {
		$show = ($this->_show) ? $this->_show : "add";
		if ($show == "update") {
			list ( $id, $rateConfig, $isopen ) = $this->_buildUpdateHtml ();
		}
		(isset ( $isopen )) ? $isopen : $isopen [1] = "checked=checked";
		$typeId = (isset ( $rateConfig ['typeid'] )) ? $rateConfig ['typeid'] : 1;
		$typeSelect = $this->_buildTypeSelectHTML ( $typeId );
		$default_handler_url = EncodeUrl ( $this->_getDefaultUrl () . "&job=ajax" );
		include H_R . '/template/ajax.htm';
		ajax_footer ();
	}

	function _buildUpdateHtml() {
		$id = $this->_id;
		$rateService = $this->_getRateService();
		$rateConfig = $rateService->getRateConfig ( $this->_typeId,$this->_id );
		foreach ( array (1, 0 ) as $v ) {
			$isopen [$v] = ($v == $rateConfig ['isopen']) ? "checked=checked" : "";
		}
		return array ($id, $rateConfig, $isopen );
	}

	function _buildTypeSelectHTML($typeid) {
		foreach ( array (1, 2, 3 ) as $v ) {
			$selected [$v] = ($v == $typeid) ? "selected=selected" : "";
		}
		$html = "";
		$html .= '<select name="rateconfig[typeid]" class="select_wa">';
		$html .= '<option value="1" ' . $selected [1] . '>帖子评价控件</option>';
		$html .= '<option value="2" ' . $selected [2] . '>日志评价控件</option>';
		$html .= '<option value="3" ' . $selected [3] . '>相片评价控件</option>';
		$html .= '</select>';
		return $html;
	}
	function _getDefaultUrl() {
		return $GLOBALS['db_adminfile']."?adminjob=hack&hackset=rate";
	}

	function _getRateService() {
		return L::loadClass('rate','rate');
	}

}
$register = array ("action" => $action, "rateconfig" => $rateconfig, "adminName" => $admin_name, "id" => $id, "pwServer" => $pwServer, "show" => $show,"typeid"=>$typeid );
$object = new AjaxAction ( $register );
?>