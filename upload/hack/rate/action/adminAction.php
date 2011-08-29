<?php
!function_exists('readover') && exit('Forbidden');
S::gp ( array ("typeid", "action", "rateconfig", "id","power" ) );
class AdminAction {

	var $_action;
	var $_typeid;
	var $_pwServer;
	var $_rateconfig;
	var $_adminName;
	var $_id;
	var $_bbsUrl;
	var $_power;
	var $_db_ratepower;
	var $_db_rategroup;

	function AdminAction($register) {
		$this->_register ( $register );
		$this->_init ();
	}

	function _register($register) {
		$this->_action = &$register ['action'];
		$this->_typeid = &$register ['typeid'];
		$this->_pwServer = &$register ['pwServer'];
		$this->_rateconfig = &$register ['rateconfig'];
		$this->_id = &$register ['id'];
		$this->_adminName = &$register ['adminName'];
		$this->_bbsUrl = &$register ['bbsUrl'];
		$this->_power = &$register ['power'];
		$this->_db_ratepower = &$register ['db_ratepower'];
		$this->_db_rategroup = &$register ['db_rategroup'];
	}

	function _init() {
		if ($this->_action == "modify" && strtolower ( $this->_pwServer ['REQUEST_METHOD'] ) == "post") {
			$this->_modify ();
		}
		if ($this->_action == "delete" && strtolower ( $this->_pwServer ['REQUEST_METHOD'] ) == "get") {
			$this->_delete ();
		}
		if ($this->_action == "power" && strtolower ( $this->_pwServer ['REQUEST_METHOD'] ) == "post") {
			$this->_power();
		}

		$this->_render ( $this->_typeid );
	}

	function _modify() {
		if (count ( $this->_rateconfig ) > 0) {
			$rateService = $this->_getRateService();
			foreach ( $this->_rateconfig as $id => $config ) {
				if (intval ( $id ) < 1) {
					continue;
				}
				$fieldData = array ();
				$fieldData ['isopen'] = (isset ( $config ['isopen'] ) && $config ['isopen'] == 1) ? 1 : 0;
				$fieldData ['icon'] = trim ( $config ['icon'] );
				$fieldData ['creditset'] = intval ( $config ['creditset'] );
				$fieldData ['voternum'] = intval ( $config ['voternum'] );
				$fieldData ['authornum'] = intval ( $config ['authornum'] );
				$fieldData ['updater'] = $this->_adminName;
				$fieldData ['update_at'] = time ();
				(isset ( $config ['title'] )) && $fieldData ['title'] = $config ['title'];
				$rateService->updateRateConfig ( $fieldData, $id );
			}
		}
		Showmsg ( "恭喜你，评价配置操作成功!", $this->_getDefaultUrl () . "&typeid=" . $this->_typeid );
	}

	function _delete() {
		$rateService = $this->_getRateService();
		(! $rateService->deleteRateConfig ( $this->_id )) && Showmsg ( "对不起，删除评价选项失败", $this->_getDefaultUrl () . "&typeid=" . $this->_typeid );
	}

	function _power(){
		$powerData = array();
		$powerData[1] = (isset($this->_power['type'][1])) ? 1 : 0;
		$powerData[2] = (isset($this->_power['type'][2])) ? 1 : 0;
		$powerData[3] = (isset($this->_power['type'][3])) ? 1 : 0;
		$tmp = array();
		foreach($this->_power['group'] as $key=>$value){
			$tmp[$key] = intval($value);
		}
		$groupData = $tmp;
		$rateService = $this->_getRateService();
		$rateService->addConfigPower($powerData,$groupData);
		Showmsg ( "恭喜你，评价权限设置操作成功!", $this->_getDefaultUrl () . "&typeid=" . $this->_typeid );
	}
	function _render($typeId) {
		$typeId = (intval ( $typeId ) > 1) ? $typeId : 1;
		$currentClass = $this->_getCurrentClass ( $typeId );
		$default_handler_url = $this->_getDefaultUrl () . "&typeid=";
		# 评价权限设置区域
		if($typeId == 100){
			list($userGroups,$userGroupTitles,$imageUrl,$powerSets,$groupSets,$currentPower) = $this->_buildPowerParams();
		}else{
			list($rateConfigs,$default_ajax_url,$imageUrl) = $this->_buildRateParams($typeId);
		}
		include H_R . '/template/admin.htm';
	}

	function _buildRateParams($typeId){
		$default_ajax_url = EncodeUrl ( $this->_getDefaultUrl () );
		$rateConfigs = $this->_buildRateConfigHTML ( $typeId );
		
		$imageUrl = $this->_bbsUrl.'/hack/rate/images/';
		return array($rateConfigs,$default_ajax_url,$imageUrl);
	}

	function _buildPowerParams(){
		$rateService = $this->_getRateService();
		$userGroups = $rateService->getUserGroupLevel();
		$userGroupTitles = array("member"=>"用户组","system"=>"系统组","special"=>"特殊组","default"=>"默认用户组");
		$imageUrl = $this->_bbsUrl.'/images/wind/level/';
		$powerSets = unserialize($this->_db_ratepower);
		$groupSets = unserialize($this->_db_rategroup);
		foreach( $powerSets as $typeId=>$v){
			$currentPower[$typeId] = ($v==1) ? 'checked="checked"' : '';
		}
		return array($userGroups,$userGroupTitles,$imageUrl,$powerSets,$groupSets,$currentPower);
	}
	
	function _buildRateConfigHTML($typeId) {
		$tmp = array ();
		$rateService = $this->_getRateService();
		$rateConfigs = $rateService->getsRateConfigByTypeId ( $typeId );
		if (! $rateConfigs) {
			return null;
		}
		$creditNames = $rateService->getCreditDefaultMap ();
		foreach ( $rateConfigs as $key => $config ) {
			$config ['typename'] = ($config ['typeid'] == 1) ? "帖子" : (($config ['typeid'] == 2) ? "日志" : "相片");
			$config ['creditset'] = $this->_getCreditSelect ( $config ['id'], $config ['creditset'], $creditNames ); //需要知道当前的值
			$config ['voternum'] = $this->_getCreditNumberSelect ( $config ['id'], "voternum", $config ['voternum'] );
			$config ['authornum'] = $this->_getCreditNumberSelect ( $config ['id'], "authornum", $config ['authornum'] );
			$config ['isopen'] = ($config ['isopen'] == 1) ? "checked=checked" : "";
			$tmp [$key] = $config;
		}
		return $tmp;
	}

	function _getCreditSelect($id, $creditset, $creditNames) {
		$html = $option = '';
		foreach ( $creditNames as $key => $value ) {
			$selected = ($key == $creditset) ? 'selected="selected"' : '';
			$option .= '<option value="' . $key . '" ' . $selected . '>' . $value . '</option>';
		}
		$html .= '<select name="rateconfig[' . $id . '][creditset]">';
		$html .= $option;
		$html .= '</select>';
		return $html;
	}
	

	function _getCreditNumberSelect($id, $owner, $number = 0) {
		$html = $option = '';
		for($i = - 10; $i <= 10; $i ++) {
			$selected = ($i == $number) ? 'selected="selected"' : '';
			$option .= '<option value="' . $i . '" ' . $selected . '>' . $i . '</option>';
		}
		$html .= '<select name="rateconfig[' . $id . '][' . $owner . ']">';
		$html .= $option;
		$html .= '</select>';
		return $html;
	}

	function _getPWCredit() {
		require_once R_P . 'require/credit.php';
	}

	function _getCurrentClass($typeId) {
		$rateTypes = $this->_getRateTypes ();
		foreach ( $rateTypes as $key => $value ) {
			$currentClass [$value] = ($typeId == $key) ? 'class="current"' : '';
		}
		$currentClass['power'] = ($typeId == 100) ? 'class="current"' : '';
		return $currentClass;
	}

	function _getRateTypes() {
		return array (1 => "thread", 2 => "blog", 3 => "picture" );
	}

	function _getDefaultUrl() {
		return $GLOBALS['db_adminfile']."?adminjob=hack&hackset=rate";
	}

	function _getRateService() {
		return L::loadClass('rate','rate');
	}

}
$register = array ("typeid" => $typeid, "action" => $action, "pwServer" => $pwServer, "rateconfig" => $rateconfig, "adminName" => $admin_name, "id" => $id,"bbsUrl"=>$db_bbsurl,"power"=>$power,"db_ratepower"=>$db_ratepower,"db_rategroup"=>$db_rategroup );
$object = new AdminAction ( $register );
?>