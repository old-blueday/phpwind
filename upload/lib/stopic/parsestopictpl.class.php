<?php
/**
 * 专题模板解析器
 * 
 * @package STopic
 */

!defined('P_W') && exit('Forbidden');

/**
 * 专题模板解析器
 * 
 * @package STopic
 */
class PW_ParseStopicTpl {
	/**
	 * html配置
	 * 
	 * 专题模板中一些容器、操作元素的class名
	 * 
	 * @var array
	 */
	var $_htmlConfig = array(
		'packclass'		=> 'itemDroppable',
		'itemclass'		=> 'itemDraggable',
		'headclass'		=> 'itemHeader',
		'layoutheadclass' => 'layoutHeader',
		'editclass'		=> 'editEl',
		'closeclass'	=> 'closeEl',
		'contentclass'	=> 'itemContent',
	);
	
	/**
	 * 专题服务对象
	 * 
	 * @var PW_STopicService
	 */
	var $service;
	
	/**
	 * 专题数据数组
	 * 
	 * @var array
	 */
	var $stopic;
	
	/**
	 * 专题模块列表数组
	 * 
	 * @var array
	 */
	var $units;
	
	/**
	 * 专题模块类型列表数组
	 * 
	 * @var array
	 */
	var $blocks;
	
	/**
	 * 是否是后台管理
	 * 
	 * @var bool
	 */
	var $ifadmin;
	
	/**
	 * 要删除的专题模块列表
	 * 
	 * @var array
	 */
	var $delunits;
	
	/**
	 * 布局html数组
	 * 
	 * @var array
	 */
	var $layoutStrings = array();

	/**
	 * 设置当前是否在处理后台管理的模板
	 * 
	 * @param bool $ifadmin
	 */
	function setIfAdmin($ifadmin) {
		$this->ifadmin = $ifadmin;
	}
	
	/**
	 * 获取布局html
	 * 
	 * @param string $layout 布局名
	 * @return string
	 */
	function getLayoutString($layout) {
		if (!isset($this->layoutStrings[$layout])) {
			if ($layout && file_exists(S::escapePath(A_P.'data/layout/'.$layout.'/layout.htm'))) {
				//* $this->layoutStrings[$layout] = readover(S::escapePath(A_P.'data/layout/'.$layout.'/layout.htm'));
				$this->layoutStrings[$layout] = pwCache::readover(S::escapePath(A_P.'data/layout/'.$layout.'/layout.htm'));
			} else {
				$this->layoutStrings[$layout] = '';
			}
		}
		return $this->layoutStrings[$layout];
	}
	
	/**
	 * 解析专题模板，得到专题html内容
	 * 
	 * @param PW_STopicService $stopic_service 专题服务
	 * @param array $stopic 专题数据
	 * @param array $units 专题模块数组
	 * @param array $blocks 模块类型数组
	 * @param bool $ifadmin 是否后台管理
	 * @return string 专题html内容
	 */
	function exute($stopic_service,$stopic,$units,$blocks,$ifadmin) {
		global $ifDelOldUnit;
		$this->_register($stopic_service,$stopic,$units,$blocks,$ifadmin);

		$content = '';
		foreach ($this->stopic['block_config'] as $layout_id => $blocks) {
			$tmp = $this->_getLayoutContent($layout_id,$stopic['stopic_id']);
			$head = $this->ifadmin ? $this->_getLayoutHeadData($layout_id) : '';
			$tmp = '<div id="'.$layout_id.'" class="layoutDraggable cc" width="100%">'.$head.$tmp.'</div>';
			$content .= $tmp;
		}
		$ifDelOldUnit || $this->_delOverageUnits();
		return $content;
	}
	
	/**
	 * 获取布局的html内容
	 * 
	 * @access protected
	 * @param string $layout_id 布局html的id
	 * @return string
	 */
	function _getLayoutContent($layout_id,$stopic_id) {
		list($layout_type, ) = explode('_', $layout_id);
		$string = $this->getLayoutString($layout_type);

		$string = str_replace('{REPLACE_LAYOUT_ID}', $layout_id, $string);

		preg_match_all('/<div(.+?)>([^\x00]+?)<\/div>/is',$string,$match);
		$search = $replace = array();
		foreach ($match[1] as $key=>$value) {
			if (strpos($value,$this->_htmlConfig['packclass'])===false) {
				continue;
			}
			$id	= $this->_getId($value);
			if (!$id) {
				continue;
			}
			$search[]	= $match[0][$key];

			$replace[]	= $this->_getReplace($id,$value,$stopic_id);
		}

		return str_replace($search,$replace,$string);
	}
	
	/**
	 * 获取布局的头部html内容
	 * 
	 * @access protected
	 * @param string $layout_id 布局id
	 */
	function _getLayoutHeadData($layout_id) {
		list($layout_type, ) = explode('_', $layout_id);
		$layout_info = $this->service->getLayoutInfo($layout_type);
		return '<div class="'.$this->_htmlConfig['layoutheadclass'].'">
<span>'.$layout_info['desc'].'</span>
<a class="closeEl" href="javascript:void(0);">[x]</a>
</div>';
	}

	/**
	 * 注册变量
	 * 
	 * @access protected
	 * @param PW_STopicService $stopic_service 专题服务
	 * @param array $stopic 专题数据
	 * @param array $units 专题模块数组
	 * @param array $blocks 模块类型数组
	 * @param bool $ifadmin 是否后台管理
	 */
	function _register($stopic_service,$stopic,$units,$blocks,$ifadmin) {
		$this->service	=&$stopic_service;
		$this->setIfAdmin($ifadmin);

		$this->stopic	= $stopic;
		$this->units	= $this->delunits = $units;
		$this->blocks	= $blocks;
	}

	/**
	 * 删除失效的模块数据
	 * 
	 * @access protected
	 */
	function _delOverageUnits() {
		if ($this->delunits) {
			$keys = array_keys($this->delunits);
			$this->service->deleteUnits($this->stopic['stopic_id'],$keys);
		}
	}

	/**
	 * 解析、获取模板html中的id属性
	 * 
	 * @access protected
	 * @param string $string 模板html
	 * @return string
	 */
	function _getId($string) {
		preg_match('/id=\s?("|\')([\w]*?)\\1/is',$string,$match);
		return $match[2];
	}
	
	/**
	 * 获取布局中容器的html内容
	 * 
	 * @access protected
	 * @param string $id 布局中容器的id属性
	 * @param string $divconfig 布局容器的div配置属性
	 * @return string
	 */
	function _getReplace($id,$divconfig,$stopic_id) {
		$temp = '';
		$temp .= '<div'.$divconfig.'>';
		$temp .= $this->ifadmin ? '&nbsp;' : '';
		$temp .= $this->_getUnitsByPack($id);
		$temp .= '</div>';
		return (strrpos($temp,'{REPLACE_STOPIC_ID}') !== false) ? str_replace('{REPLACE_STOPIC_ID}', $stopic_id, $temp) : $temp;
	}
	
	/**
	 * 获取布局容器中所有模块的html内容
	 * 
	 * @access protected
	 * @param string $id 布局容器的id属性
	 * @return string
	 */
	function _getUnitsByPack($id) {
		list($layout_type, $layout_id, $layout_part) = explode("_",$id);
		if (!isset($this->stopic['block_config'][$layout_type.'_'.$layout_id][$layout_part])) return '';
		$temp	= '';
		foreach ($this->stopic['block_config'][$layout_type.'_'.$layout_id][$layout_part] as $html_id) {
			$temp	.= $this->_getUnitHTML($html_id);
		}
		return $temp;
	}
	
	/**
	 * 获取单个模块的html内容
	 * 
	 * @access protected
	 * @param string $html_id 模块html的id属性
	 * @return string
	 */
	function _getUnitHTML($html_id) {
		$html_id	= trim($html_id);
		list(,$unitType,) = explode("_", $html_id);
		unset($this->delunits[$html_id]);
		$itemStyle = $this->_isUnitNoBorder($unitType) ? 'style="padding:0;margin:0;border:0;"' : '';
		$contentStyle = $this->_isUnitNoBorder($unitType) ? 'style="padding:0;margin:0;border:0;"' : '';
		$temp	= '<div class="'.$this->_htmlConfig['itemclass'].'" id="'.$html_id.'" '.$itemStyle.'>';
		$temp	.= $this->_getHeadData($html_id);
		$temp	.= '<div class="'.$this->_htmlConfig['contentclass'].'" '.$contentStyle.'>';
		$temp	.= $this->_getHtmlData($html_id);
		$temp	.= '</div>';
		$temp	.= '</div>';
		return $temp;
	}
	
	/**
	 * 模块是否不需要边框
	 * 
	 * @access protected
	 * @param string $unitType 模块类型，横幅和导航栏不需要边框
	 * @return bool
	 */
	function _isUnitNoBorder($unitType) {
		return in_array($unitType, array('banner', 'nvgt'));
	}
	
	/**
	 * 获取模块头部html内容
	 * 
	 * @access protected
	 * @param $html_id
	 */
	function _getHeadData($html_id) {
		$temp	= '';
		if (!$this->units[$html_id]['title'] && !$this->ifadmin) return '';
		$temp	.= '<div class="'.$this->_htmlConfig['headclass'].'"><span>';
		$temp	.= $this->units[$html_id]['title'];
		$temp	.= '</span>';
		if ($this->ifadmin) {
			$temp	.= '<a href="javascript:void(0);" class="'.$this->_htmlConfig['editclass'].'">'.getLangInfo('other','stopic_edit').'</a>';
			$temp	.= '<a href="javascript:void(0);" class="'.$this->_htmlConfig['closeclass'].'">[x]</a>';
		}
		$temp	.= '</div>';
		return $temp;
	}
	
	/**
	 * 生成模块的html内容
	 * 
	 * @access protected
	 * @param string $html_id 模块的html的id属性
	 * @return string
	 */
	function _getHtmlData($html_id){
		$block_data	= $this->units[$html_id]['data'];
		list(,$block_type,) = explode("_", $html_id);
		//$blockid	= $this->units[$html_id]['block_id'];
		//$block	= $this->blocks[$blockid];
		return $this->service->getHtmlData($block_data,$block_type,$html_id);
	}
}
