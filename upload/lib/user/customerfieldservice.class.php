<?php
!defined('P_W') && exit('Forbidden');

class PW_CustomerFieldService {

	var $customerField;
	var $customerFieldData;
	var $customerFieldTemplate;
	
	function PW_CustomerFieldService(){
		$this->customerField = L::loadClass('CustomerField','user');
	}
	function getRegisterTemplate($complement=1){
		$registerFields = $this->customerField->getFieldsByComplement($complement);
		if (!S::isArray($registerFields)) return false;
		require PrintEot('customerfield_register');
		$template = new customerFieldRegisterTemplate;
		$template->pwCustomerField = $this->customerField;
		$html = ''; 
		foreach ($registerFields as $v) {
			$html .= $template->buildHtml($v);
		}
		return $html;
	}
	
	function getAdminTemplate($uid){
		pwCache::getData(D_P.'data/bbscache/customfield.php');
		global $customfield;
		if (!S::isArray($customfield))return false;
		require PrintEot('customerfield_admin');
		$template = new customerFieldAdminTemplate;
		$template->pwCustomerField = $this->customerField;
		$html = ''; 
		$this->customerFieldData = L::loadClass('CustomerFieldData','user');
		foreach ($customfield as $v) {
			$customFieldValue = $this->customerFieldData->getCustomerData($v,$uid);
			$html .= $template->buildHtml($v,$customFieldValue);
		}
		return $html;
	}
	
	function getRegisterScripts($complement=1){
		$registerFields = $this->customerField->getFieldsByComplement($complement);
		if (!S::isArray($registerFields)) return false;
		$scripts = '';
		$keep = 20;
		foreach ($registerFields as $v) {
			$required = $v['required'] == 1 ? '1' : '0';
			$tips = '本选项填写有误';
			if (S::inArray($v['type'], array($this->customerField->typeMap['input'],$this->customerField->typeMap['textarea']))) {
				!$v['maxlen'] && $v['maxlen'] = 255;
				$tips = "最大长度为$v[maxlen]字节";
			}
			$scripts .= <<<EOT
			regInfo[$keep] 	= new Array(
						" ",
						"本选项填写有误",
						"格式不正确",
						"$tips",
						"$v[descrip]"
					);
			
EOT;
			if($v['type'] == $this->customerField->typeMap['area']){
				//$scripts .= "var $v[fieldname] = new Element('area_$v[fieldname]',regInfo[$keep],'$v[fieldname]'+'_info',null,$required);";
				$scripts .= "extracheck.push(getObj('area_$v[fieldname]'));";
				$scripts .= "var area_$v[fieldname] = new Element('area_$v[fieldname]',regInfo[$keep],'area_$v[fieldname]'+'_info',null,$required);";
			} else {
				switch ($v['fieldname']) {
					case 'education':
						$scripts .= <<<EOT
						if(typeof(schoolids) != 'undefined') {
							for(i=0; i<schoolids.length;i++){
								window['schoolname_'+schoolids[i]] = new Element("schoolname_"+schoolids[i],regInfo[$keep],"schoolname_"+schoolids[i]+'_info',null,$required);
							}
						}
EOT;
						break;
					case 'career':
						$scripts .= <<<EOT
						if(typeof(companyids) != 'undefined') {
							for(i=0; i<companyids.length;i++){
								window['companyname_'+companyids[i]] = new Element("companyname_"+companyids[i],regInfo[$keep],"companyname_"+companyids[i]+'_info',null,$required);
							}
						}
EOT;
						break;
					default:
						$scripts .= "var $v[fieldname] = new Element('$v[fieldname]',regInfo[$keep],'$v[fieldname]'+'_info',null,$required);";
						$v['type'] == $this->customerField->typeMap['checkbox'] && $scripts .= "checkboxArray.push('$v[fieldname]');";
						$v['type'] == $this->customerField->typeMap['radio'] && $scripts .= "radioArray.push('$v[fieldname]');";
				}
				$keep ++;
			}
		}
		return $scripts;
	}
	
	function checkData($fieldname,$value,$returnint = false){
		if (!$value) return false;
		$fieldinfo = $this->customerField->getFieldByFieldName($fieldname);
		if (!S::isArray($fieldinfo)) return false;
		$this->customerFieldData = L::loadClass('CustomerFieldData','user');
		return $this->customerFieldData->checkData($fieldinfo,$value,$returnint);
	}
	
	function saveRegisterCustomerData($complement=1){
		global $winduid;
		if (!$winduid) return false;
		$this->customerFieldData = L::loadClass('CustomerFieldData','user');
		$registerFields = $this->customerField->getFieldsByComplement($complement);
		$this->customerFieldData->customerEdit = true;
		if (!S::isArray($registerFields)) return false;
		foreach ($registerFields as $v) {
			$this->customerFieldData->setData($v,$winduid);
		}
		$this->customerFieldData->updateData();
	}
	
	/**
	 * 资料设置页模板
	 */
	function getProfileTemplateByInfotype($infotype){
		global $winduid;
		$customFields = $this->customerField->getFieldsByCategoryName($infotype);
		$templateString = '';
		if ($customFields) {
			$this->customerFieldData = L::loadClass('CustomerFieldData','user');
			require_once uTemplate::PrintEot('customerfield_profile');
			$template = new customerFieldProfileTemplate();
			foreach ($customFields as $value) {
				$customFieldValue = $this->customerFieldData->getCustomerData($value,$winduid);
				if ($value['fieldname'] == 'bday' && $customFieldValue == '0000-00-00') $customFieldValue = date('Y-m-d',$GLOBALS['winddb']['regdate']);
				$templateString .= $template->buildHtml($value,$customFieldValue);;
			}
		}
		return "<!--$templateString-->";
	}
	
	function saveProfileCustomerData($infotype){
		global $winduid;
		$customFields = $this->customerField->getFieldsByCategoryName($infotype);
		if (S::isArray($customFields)) {
			$this->customerFieldData = L::loadClass('CustomerFieldData','user');
			$this->customerFieldData->customerEdit = true;
			foreach ($customFields as $v) {
				$this->customerFieldData->setData($v, $winduid);
			}
			$this->customerFieldData->updateData();
		}
	}
	
	/**
	 * 后台编辑保存自定义字段(所有字段)
	 */
	function saveAdminCustomerData($uid){
		$uid = intval($uid);
		pwCache::getData(D_P.'data/bbscache/customfield.php');
		global $customfield;
		if (!S::isArray($customfield) || $uid < 1)return false;
		$this->customerFieldData = L::loadClass('CustomerFieldData','user');
		foreach ($customfield as $v) {
			$this->customerFieldData->setData($v, $uid);
		}
		$this->customerFieldData->updateData();
		return true;
	}
	
	function getCustomerValues($uid){
		$customerValues = array();
		$uid = intval($uid);
		pwCache::getData(D_P.'data/bbscache/customfield.php');
		global $customfield;
		if (!S::isArray($customfield) || $uid < 1)return $customerValues;
		$this->customerFieldData = L::loadClass('CustomerFieldData','user');
		foreach ($customfield as $v){
			if($v['viewright']){
				global $groupid,$winduid;
				if($winduid != $uid && !in_array($groupid,explode(",",$v['viewright']))) continue;
			}
			$value = $this->customerFieldData->getCustomerData($v,$uid);
			$customerValues[$v['category']][$v['title']] = $this->formatCustomerHtml($v,$value);
		}
		return $customerValues;
	}
	
	function formatCustomerHtml($fieldInfo,$value){
		$html = '';
		$fieldInfo = $this->customerField->formatFieldInfo($fieldInfo);
		switch ($fieldInfo['type']){
			case $this->customerField->typeMap['area']:
				$areaService = L::loadClass('AreasService', 'utility');
				//$area = $areaService->getAreaByAreaId($value['area']);
				$area = $areaService->getAreasByAreadIds(array_values((array)$value));
				if(S::isArray($area)){
					$data = sprintf(
						'%s-%s-%s',
						$area[$value['province']]['name'],
						$area[$value['city']]['name'],
						$area[$value['area']]['name']
					);
				} else {
					$data = '';
				}
				break;
			case $this->customerField->typeMap['career']:
				$data = array();
				foreach ($value as $v) {
					$data[] = sprintf('<span><em>公司名称:</em>%s</span><em>入职时间:</em>%s',$v['companyname'],date('Y-m-d',$v['starttime']));
				}
				break;
			case $this->customerField->typeMap['education']:
				$data = array();
				$educationService = L::loadClass('EducationService', 'user');
				foreach ($value as $v) {
					$data[] = sprintf(
						'教育程度:%s 学校名称:%s 入学时间:%s',
						$educationService->educationMap[$v['educationlevel']],
						$v['schoolname'],
						date('Y年',$v['starttime'])
					);
				}
				break;
			case $this->customerField->typeMap['checkbox']:
				$data = array();
				$value = explode("\t",$value);
				foreach ($value as $v){
					$data[] = $fieldInfo['options'][$v];
				}
				break;
			case $this->customerField->typeMap['select']:
			case $this->customerField->typeMap['radio']:
				$data = '';
				foreach ($fieldInfo['options'] as $k=>$v){
					if($k == $value){
						$data = $v;
					}
				}
				break;
			default:
				$data = $value;
				break;
		}
		if(!is_array($data) || count($data) == 1){
			$html = $data;
			is_array($html) && $html = $html[0];
		} elseif(in_array($fieldInfo['type'], array($this->customerField->typeMap['career'],$this->customerField->typeMap['education']))) {
			$html = '<ul>';
			foreach ($data as $v){
				$html .= sprintf('<li>%s</li>',$v);
			}
			$html .= '</ul>';
		} else {
			$html = '';
			foreach ($data as $v){
				$html .= sprintf('<span>%s</span>',$v);
			}
		}
		return $html;
	}
}