<?php
!defined('P_W') && exit('Forbidden');
class PW_pieceOperate {
	var $datasourceService;
	var $lang;
	function __construct() {
		$this->lang = include(R_P.'mode/area/config/element_lang_config.php');
		$this->datasourceService = L::loadClass('datasourceservice', 'area');
	}
	function PW_pieceOperate() {
		$this->__construct();
	}

	function _getSourceService() {
		return L::loadClass('datasourceservice', 'area');
	}

	/*
	 * 初始化小模块的配置
	 */
	function initPiece($piece) {
		$piece = $this->_initAction($piece);
		$piece = $this->_initConfig($piece);
		$piece = $this->_initParam($piece);
		return $piece;
	}
	/**
	 * 获取
	 * @param string $type
	 * @param array $piece
	 * return array
	 */

	function getConfigHtmlBySourceType($type,$id,$default=array()) {
		$datasourceService = $this->_getSourceService();
		$sourceConfig = $datasourceService->getSourceConfig($type);
		if (!$sourceConfig) return array();
		return $this->_getConfigHtml($sourceConfig,$id,$default);
	}

	function _initAction($piece) {
		$datasourceService = $this->_getSourceService();

		$temp = array();
		$temp['title'] = getLangInfo('other','set_invoke_action');

		$temp_func = '<select onchange="pieceActionChange('.$piece['id'].',this.value);" name="p_action['.$piece['id'].']">';
		$stamp = $datasourceService->getSourceTypes();
		foreach ($stamp as $key=>$value) {
			$selected = $key == $piece['action'] ? 'selected' : '';
			$temp_func .= '<option value="'.$key.'" '.$selected.'>'.$value['title'].'</option>';
		}
		$temp['html'] = $temp_func;
		$piece['p_action'] = $temp;
		return $piece;
	}

	function _initConfig($piece) {

		$piece['config'] = $this->getConfigHtmlBySourceType($piece['action'],$piece['id'],$piece['config']);
		return $piece;
	}

	function _initParam($piece) {
		$temp_param = array();
		foreach ($piece['param'] as $k=>$value) {
			if ($value!='default') {
				$temp_param[] = '<tr class="tr1 vt"><td class="td1">'.$this->_getParamDiscrip($k,$value,$piece['action']).'</td><td class="td2"><input type="text" class="input" value="'.$value.'" name="param['.$piece['id'].']['.$k.']"></td></tr>';
			} else {
				$temp_param[] = '<input type="hidden" name="param['.$piece['id'].']['.$k.']" value="'.$value.'">';
			}
		}
		$piece['param'] = $temp_param;
		return $piece;
	}

	/**
	 * 获取配置的html展示
	 * @param array $config
	 * @param array $setting
	 * return array('title'=>$string,'html'=>$string);
	 */
	function _getConfigHtml($config,$id,$setting = array()) {
		$temp = array();
		foreach ($config as $key=>$value) {
			$temp[$key]['title'] = $value['name'];
			$default = isset($setting[$key]) ? $setting[$key] : '';

			$temp[$key]['html'] = $this->_getConfigHtmlByType($id,$key,$value,$default);
		}
		return $temp;
	}
	/**
	 * 通过配置获取配置的html（工厂）
	 * @param int $id
	 * @param string $name
	 * @param array $config
	 * @param arrray $default
	 * return string
	 */
	function _getConfigHtmlByType($id,$name,$config,$default) {
		switch ($config['type']) {
			case 'select' :
				return $this->_getSelectHtml($id,$name,$config['value'],$default);
			case 'mselect' :
				return $this->_getMSelectHtml($id,$name,$config['value'],$default);
			case 'text' :
				return $this->_getTextHtml($id,$name,$default);
		}
	}
	function _getSelectHtml($id,$name,$config,$default) {
		$htmlName = $this->_getHtmlName($id,$name);
		$string = '<select name="'.$htmlName.'">';
		foreach ($config as $key=>$value) {
			$selected = $key == $default ? 'selected' : '';
			$string .= '<option value="'.$key.'" '.$selected.'>'.$value.'</option>';
		}
		$string .= '</select>';
		return $string;
	}
	function _getTextHtml($id,$name,$default='') {
		$htmlName = $this->_getHtmlName($id,$name);
		return '<input type="text" class="input" name="'.$htmlName.'" value="'.$default.'">';
	}
	function _getMSelectHtml($id,$name,$config,$default) {
		$default = !empty($default) ? (array)$default : array('0');
		$htmlName = $this->_getHtmlName($id,$name);
		$optionCount = count($config) > 10 ? 10 : count($config);
		$selectHeight = 'style="height:'.($optionCount*20).'px"';
		$string = '<select name="'.$htmlName.'[]" multiple="multiple" '.$selectHeight.'>';
		foreach ($config as $key=>$value) {
			$selected = S::inArray($key,$default) ? 'selected' : '';
			$string .= '<option value="'.$key.'" '.$selected.'>'.$value.'</option>';
		}
		$string .= '</select>';

		return $string;
	}
	function _getHtmlName($id,$name) {
		return 'config['.$id.']['.$name.']';
	}

	function getParamName($type,$stamp='subject') {
		if ($type=='title') {
			return $this->_getParamNameByTitle($stamp);
		}
		return $this->_getElementLang($type);
	}

	function _getElementLang($key) {
		if (isset($this->lang[$key])) return $this->lang[$key];
		return '';
	}

	function _getParamNameByTitle($stamp) {
		if ($stamp=='forum') {
			return $this->_getElementLang('title_forum');
		} elseif($stamp=='user') {
			return $this->_getElementLang('title_user');
		} elseif($stamp=='tag') {
			return $this->_getElementLang('title_tag');
		} else {
			return $this->_getElementLang('title');
		}
	}

	function _getParamDiscrip($key,$format,$sourceType) {
		$temp = $this->datasourceService->getSourceLang($key,$sourceType);
		if (!$format) return $temp;
		if (is_numeric($format)) {
			return $temp . '长度[字节]';
		}
		if (preg_match('/^\d{1,3},\d{1,3}$/',$format)) {
			return '图片长宽';
		}
		if (preg_match('/^\w{1,4}(:|-)\w{1,4}((:|-)\w{1,4})?$/',$format)) {
			return $temp . '格式';
		}
		return $temp;
	}
}
?>