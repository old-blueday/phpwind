<?php
!defined('P_W') && exit('Forbidden');
define('M_P', R_P . "mode/house/");
/**
 * 房产发送到新鲜事SERVICE
 * 
 * @package weibo_Cms
 * @author lmq
 */
class weibo_House extends baseWeibo {
	var $_hid;
	var $_url;
	function weibo_House() {
		$this->_url = $GLOBALS['db_bbsurl']."/mode.php?m=house";
	}
	function init($id) {
		$this->_hid = $id;
		require_once(R_P. 'mode/house/require/core.php');
		$houseService = house::loadClass('house');
		$housefieldsService = House::loadService('HouseFieldsService');
		$house 	= $houseService->getHouseInfoByHid($id);
		empty($house) && Showmsg('data_error');
		$title = $content = sprintf("[url=%s] %s [/url]", $this->_url . "&q=info&hid=".$this->_hid, $house['name']);
		
		$position = '';
		if($house['area']){
			$areaField = $housefieldsService->getCompsiteFieldsByType('area');//所属区域
			$area = $areaField[$house['area']];
			$area = sprintf("[url=%s] %s [/url]", $this->_url . "&q=list&area=".$house['area'], $area);  
			$postion .= $area;
		}
		
		if($house['plate']){
			$plateField = $housefieldsService->getCompsiteFieldsByType('plate');//所在商圈
			$plate = $plateField[$house['plate']];
			$plate = sprintf("[url=%s] %s [/url]", $this->_url . "&q=list&plate=".$house['plate'], $plate);    
			$postion .= $plate;
		}
		$postion .= $house['address'];
		
		$mailSubject =  getLangInfo('app','house_recommend');
		$mailContent = getLangInfo('app','ajax_sendweibo_houseinfo',array('title'	=> $title,'postion'=>$postion));
		$this->_content = $content;
		$this->_mailSubject = $mailSubject;
		$this->_mailContent = $mailContent;
	}
}
?>