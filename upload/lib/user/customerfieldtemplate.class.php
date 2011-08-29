<?php
!defined('P_W') && exit('Forbidden');

class PW_CustomerFieldTemplate {

	var $pwCustomerField; /* @var $pwCustomerField PW_CustomerField */
	var $fieldInfo;
	var $fieldHtml;
	var $requriedHtml;
	var $defaultValue = '';
	var $disabledHtml = '';
	var $readonlyHtml = '';
	var $defaultValueHtml = '';
	var $htmlTemplate = array();
	var $loadedScripts = array();
	
	var $template = 'base';
	
	function PW_CustomerFieldTemplate($template = ''){
		$template && $this->template = $template;
		$this->pwCustomerField = ($pwCustomerField && is_a($pwCustomerField , 'PW_CustomerField')) ? $pwCustomerField : L::loadClass('CustomerField','user');
	}

	function buildHtml($fieldInfo,$defaultValue='',$return = false){
		if (!S::isArray($fieldInfo)) return false;
		$this->fieldHtml = '';
		$this->defaultValueHtml = '';
		$this->fieldInfo = $this->pwCustomerField->formatFieldInfo($fieldInfo);
		$this->defaultValue = '';
		//if ($defaultValue !== '') {
		//if($this->pwCustomerField->typeMap['checkbox'] == $this->fieldInfo['type']){var_dump($defaultValue);exit;}
		if (!$this->isEmptyValue($defaultValue)) {
			$this->defaultValue = $defaultValue;
			if(isset($this->disabled)) {
				$this->disabled = $this->fieldInfo['editable'] ? false : true;
				$this->initDisabledHtml();
			}
		}
		$this->formatRequriedHtml();
		$this->formatDescripHtml();
		if (!isset($this->pwCustomerField->flipTypeMap[$this->fieldInfo['type']])) return false;
		$typeUcfirst = ucfirst($this->pwCustomerField->flipTypeMap[$this->fieldInfo['type']]);
		$methodBuildField = 'build'.$typeUcfirst;
		method_exists($this,$methodBuildField) && $this->$methodBuildField();
		$methodSysfieldTemplate = 'getTemplate'.ucfirst($this->fieldInfo['fieldname']);//默认字段模板
		$methodGetTemplate = 'getTemplate'.$typeUcfirst;//普通字段模板
		if ($this->fieldInfo['ifsys'] && method_exists($this,$methodSysfieldTemplate)) {
			$methodGetTemplate = $methodSysfieldTemplate;
		}
		$template = $this->$methodGetTemplate();
		isset($this->disabled) && $this->disabled = false;
		$this->unsetDisabledHtml();
		return $template; 
	}
	

	
	function buildInput(){
		$this->defaultValue && $this->defaultValueHtml = $this->defaultValue;
	}
	function buildTextarea(){
		$this->defaultValue && $this->defaultValueHtml = $this->defaultValue;
	}
	function buildSelect(){
$this->fieldInfo['html_select'] = <<<EOT
					<select{$this->disabledHtml} name="{$this->fieldInfo['fieldname']}">
EOT;
		$selectedHtml = '';
		foreach ($this->fieldInfo['options'] as $k=>$v){
			$selectedHtml = '';
			if ($this->defaultValue && $k == $this->defaultValue) $selectedHtml = '  selected=1';
$this->fieldInfo['html_select'] .= <<<EOT
<option value="$k"$selectedHtml>$v</option>
EOT;
		}
$this->fieldInfo['html_select'] .= <<<EOT
					</select>
EOT;
	}
	
	function buildRadio(){
		$this->fieldInfo['html_radio'] = '';
		$checked = false;
		foreach ($this->fieldInfo['options'] as $k=>$v){
			$checkedHtml = '';
			if (($k == $this->defaultValue || $this->defaultValue === '') && $checked == false) {
				$checkedHtml = 'checked';
				$checked = true;
			}
$this->fieldInfo['html_radio'] .= <<<EOT
				<label class="mr15"><input {$this->disabledHtml} type="radio" name="{$this->fieldInfo['fieldname']}" value="$k" $checkedHtml>$v</label>
EOT;
	
		}
	}
	
	function buildCheckbox(){
		$this->fieldInfo['html_checkbox'] = '';
		$defaultValue = array();
		$this->defaultValue && $defaultValue = explode("\t",$this->defaultValue);
		foreach ($this->fieldInfo['options'] as $k=>$v){
			$checkedHtml = S::inArray($k,$defaultValue) ? ' checked' : '';
$this->fieldInfo['html_checkbox'] .= <<<EOT
	<label class="mr15"><input {$this->disabledHtml} type="checkbox" name="{$this->fieldInfo['fieldname']}[]" value="$k"$checkedHtml>$v</label>
EOT;
		}
	}
	
	function buildYear(){
		$this->fieldInfo['html_year'] = '';
		$defaultValue = array('year'=>0,'month'=>0,'day'=>0);
		if ($this->defaultValue) {
			$tmpDate = getdate(strtotime($this->defaultValue));
			$defaultValue['year'] = $tmpDate['year'];
			$defaultValue['month'] = $tmpDate['mon'];
			$defaultValue['day'] = $tmpDate['mday'];
		}
$this->fieldInfo['html_year'] .= <<<EOT
<select {$this->disabledHtml} name="{$this->fieldInfo['fieldname']}_year">
EOT;
		for ($i = $this->fieldInfo['options']['enddate']; $i>= $this->fieldInfo['options']['startdate']; $i--) {		
			//year
			$selectedHtml = $i == $defaultValue['year'] ? ' selected=1' : '';
$this->fieldInfo['html_year'] .= <<<EOT
<option value="$i" $selectedHtml>$i</option>
EOT;
		}
$this->fieldInfo['html_year'] .= <<<EOT
</select>

<select {$this->disabledHtml} name="{$this->fieldInfo['fieldname']}_month">
EOT;
		for ($i = 1; $i<= 12; $i++) {
			$selectedHtml = $i == $defaultValue['month'] ? ' selected=1' : '';
$this->fieldInfo['html_year'] .= <<<EOT
<option value="$i" $selectedHtml>$i </option>
EOT;
}
$this->fieldInfo['html_year'] .= <<<EOT
</select>

<select {$this->disabledHtml} name="{$this->fieldInfo['fieldname']}_day">
EOT;
		for ($i = 1; $i<= 31; $i++) {
			$selectedHtml = $i == $defaultValue['day'] ? ' selected=1' : '';
$this->fieldInfo['html_year'] .= <<<EOT
<option value="$i" $selectedHtml>$i </option>
EOT;
}
$this->fieldInfo['html_year'] .= <<<EOT
</select>
EOT;
	}
	
	function buildArea(){
		static $areaService;
		!$areaService && $areaService = L::LoadClass('AreasService','utility');
		//$id = md5(microtime());
		$id = $this->fieldInfo['fieldname'];
		if(!S::isArray($this->defaultValue)){
			$basicValue = !S::isArray($this->fieldInfo['options']) ? array(
				array('parentid'=>0,'selectid'=>'province_'.$id,'defaultid'=>'','hasfirst'=>1),
				array('parentid'=>-1,'selectid'=>'city_'.$id,'defaultid'=>'','hasfirst'=>1),
				array('parentid'=>-1,'selectid'=>'area_'.$id,'defaultid'=>'','hasfirst'=>1)
			) : array(
				array('parentid'=>0,'selectid'=>'province_'.$id,'defaultid'=>intval($this->fieldInfo['options']['province']),'hasfirst'=>1),
				array('parentid'=>intval($this->fieldInfo['options']['province']),'selectid'=>'city_'.$id,'defaultid'=>intval($this->fieldInfo['options']['city']),'hasfirst'=>1),
				array('parentid'=>intval($this->fieldInfo['options']['city']),'selectid'=>'area_'.$id,'defaultid'=>intval($this->fieldInfo['options']['area']),'hasfirst'=>1)
			);
		} else {
			$basicValue = array(
				array('parentid'=>0,'selectid'=>'province_'.$id,'defaultid'=>intval($this->defaultValue['province']),'hasfirst'=>1),
				array('parentid'=>$this->defaultValue['province'],'selectid'=>'city_'.$id,'defaultid'=>intval($this->defaultValue['city']),'hasfirst'=>1),
				array('parentid'=>$this->defaultValue['city'],'selectid'=>'area_'.$id,'defaultid'=>intval($this->defaultValue['area']),'hasfirst'=>1)
			);
		}
		$this->fieldInfo['html_areascripts'] = $areaService->buildAllAreasLists($basicValue);
		if(!$this->loadedScripts['area']){
			$this->fieldInfo['html_areascripts'] .= <<<EOT
	<script src="js/pw_areas.js"></script>
EOT;
			$this->loadedScripts['area'] = true;
		}
	$this->fieldInfo['html_areaprovince'] = <<<EOT
			<select id="province_$id" {$this->disabledHtml} onchange="changeSubArea(this.value, 'city_$id',0,true);" class="w" style="width:70px;"></select>
EOT;
	$this->fieldInfo['html_areacity'] = <<<EOT
			<select id="city_$id" {$this->disabledHtml} onchange="changeSubArea(this.value, 'area_$id',0,true);" class="w" style="width:70px;">
			</select>
EOT;
	$this->fieldInfo['html_areaarea'] = <<<EOT
			<select id="area_$id" {$this->disabledHtml} name="{$this->fieldInfo[fieldname]}" class="w" style="width:70px;">
			</select>
EOT;
	}
	
	function buildEducation(){
		$id = 'ids_' . md5(microtime());
		$selectSchoolHtml = $this->disabled ? '' : ' onclick="getSchoolWindow(\''.$id.'\');"';//学校选择事件
		$educationService = L::loadClass('EducationService','user');
$this->fieldInfo['html_eduscripts'] = <<<EOT
		<script src="data/bbscache/areadata.js"></script>
		<script src="js/pw_areas.js"></script>
		<script>
			var eduHtml = '';
			var schoolids = new Array();
			schoolids[0] = "$id";
			function getSchoolWindow(id,inputObj){
				var type=1;
				inputObj = getObj('schoolname_' + id);
				level = getObj('level_' + id).value;
				if (level == 0) return;
				var schoolNameObj = getObj('schoolname_'+id);
				if(level > 3){
					type = 3;//大学
				}else if(level > 1){
					type = 2;//中学
				}
				url = 'pw_ajax.php?action=pwschools&type=' + type + '&sid=' + id;
				sendmsg(url,'',getObj('schoolname_' + id));return false;
			}
			
			function deleteEducation(id) {
				if (!id) return false; 
				var url = 'pw_ajax.php?action=pwschools&job=deleducation&educationid=' + id;
				ajax.send(url,'',function(){
					var response = ajax.request.responseText.split("\t");
					if (response[0] == 'success') {
						delElement('educationItem_'+id);
						showDialog('success',response[1], 2);
					} else {
						showDialog('error', response[1], 2);
					}
				});
			}
			
			function addEducation(s,t){
				//var l = getObj(t).lastChild;
				var n = getObj(s).cloneNode(true);
				n.style.display = '';
				n.id = '';
				var inputs = n.getElementsByTagName("input");
				for (var i=0; i<inputs.length; i++) {
					inputs[i].value = '';
				}
				var selects = n.getElementsByTagName("select");
				for (var j=0; j<selects.length; j++) {
					if (/^level_ids/.test(selects[j].id)){
						selects[j].options[4].selected = true;
						continue;
					}
					selects[j].options[0].selected = true;
				}
				var randomId = Math.random().toString().substring(2);
				var html = n.innerHTML.replace(/((w+_)?ids_)[a-z0-9]+/ig, '$1'+randomId);
				n.id = 'eduItem_' + Math.random().toString().substring(2);
				n.innerHTML = '<a href="javascript://" class="fr mr20 s4 pr" onclick="delElement(getObj(\''+n.id+'\'));">删除</a>' + html;
				n.innerHTML = n.innerHTML.replace(/<span class="s1">\*<\/span>/ig, '');
				getObj(t).appendChild(n);
				try{
					getObj('schoolname_ids_' + randomId + '_info').className = 'ignore';
					getObj('schoolname_ids_' + randomId + '_info').innerHTML = '';
					getObj('schoolname_ids_' + randomId + '_info').style.display = 'none';
				}catch(e){}
			}

			function clearSchoolnameAndTime(id) {
				getObj('schoolname_' + id).value = getObj('schoolid_' + id).value =  '';
				getObj('schoolyear_' + id).options[0].selected = true;
			}
		</script>
EOT;
$this->fieldInfo['html_edulevel'] = <<<EOT
				<select {$this->disabledHtml} name="new_{$this->fieldInfo['fieldname']}_level[]" id="level_$id" onchange="clearSchoolnameAndTime('$id');">
EOT;
		foreach ($educationService->educationMap as $k=>$v) {
			$selectedHtml = $k == 5 ? ' selected="selected"' : '';
$this->fieldInfo['html_edulevel'] .= <<<EOT
				<option value="$k"$selectedHtml>$v</option>
EOT;
}
$this->fieldInfo['html_edulevel'] .= <<<EOT
				</select>
EOT;
$this->fieldInfo['html_eduname'] = <<<EOT
			<input type="hidden" name="new_{$this->fieldInfo['fieldname']}_schoolid[]" id="schoolid_$id" value=""><input type="text" class="input" {$this->disabledHtml} readonly="true" $selectSchoolHtml name="schoolname_$id" id="schoolname_$id" />
EOT;

$this->fieldInfo['html_eduname_info'] = <<<EOT
<div class="ignore" id="schoolname_{$id}_info" style="display:none"></div>
EOT;

$this->fieldInfo['html_eduyear'] = <<<EOT
				<select {$this->disabledHtml} name="new_{$this->fieldInfo['fieldname']}_year[]" id="schoolyear_$id">
EOT;
$date = getdate($GLOBALS['timestamp']);
$startYear = $date['year'];
$endYear = $startYear - 100;
			for($i = $startYear; $i>= $endYear ; $i--){
$this->fieldInfo['html_eduyear'] .= <<<EOT
				<option value="$i">$i</option>
EOT;
			}
$this->fieldInfo['html_eduyear'] .= <<<EOT
				</select>
EOT;
		/*有默认值格式化默认值*/
		if (S::isArray($this->defaultValue) && preg_match('/<\!--DEFAULT_VALUE_TEMPLATE-->(.*)<\!--END_DEFAULT_VALUE_TEMPLATE-->/is',$this->getTemplateEducation(),$m) ) {
			if(!$m[1]) return ;
			$countNum = 0;
			foreach ($this->defaultValue as $k=>$v) {
				$countNum++;
				$selectSchoolHtml = $this->disabled ? '' : ' onclick="getSchoolWindow('.$k.');"';//学校选择事件
				if (!S::isArray($v)) continue;
				$tmpEducation = array();
				//教育程度
$tmpEducation['html_edulevel'] = <<<EOT
				<select {$this->disabledHtml} name="{$this->fieldInfo['fieldname']}_level[$v[educationid]]" id="level_$k">
EOT;
				foreach ($educationService->educationMap as $key=>$value) {
					$selectedHtml = $key == $v['educationlevel'] ? '  selected=1' : '';
$tmpEducation['html_edulevel'] .= <<<EOT
				<option value="$key"$selectedHtml>$value</option>
EOT;
				}
$tmpEducation['html_edulevel'] .= <<<EOT
				</select>
EOT;
$tmpEducation['html_eduname'] = <<<EOT
			<input type="hidden" name="{$this->fieldInfo['fieldname']}_schoolid[$v[educationid]]" id="schoolid_$k" value="$v[schoolid]"><input type="text" class="input" {$this->disabledHtml} readonly="true" onfocus="" $selectSchoolHtml id="schoolname_$k" value="$v[schoolname]" />
EOT;
				//入学年份
				$tempDate = getdate($v['starttime']);
				$year = $tempDate['year'];
$tmpEducation['html_eduyear'] = <<<EOT
				<select {$this->disabledHtml} name="{$this->fieldInfo['fieldname']}_year[$v[educationid]]" id="schoolyear_$k">
EOT;
$date = getdate($GLOBALS['timestamp']);
$startYear = $date['year'];
$endYear = $startYear - 100;
			for($i = $startYear; $i>= $endYear ; $i--){
				$selectedHtml = $i == $year ? '  selected=1' : '';
$tmpEducation['html_eduyear'] .= <<<EOT
				<option value="$i"$selectedHtml>$i</option>
EOT;
			}
$tmpEducation['html_eduyear'] .= <<<EOT
				</select>
EOT;

$tmpEducation['html_deletebutton'] = !$this->disabled && $countNum != 1 ? "<a href=\"javascript://\" class=\"fr mr20 s4 pr\" onclick=\"deleteEducation('$k');\">删除</a>" : '';
				$this->defaultValueHtml .= "<div id=educationItem_{$k}>" . preg_replace(
					array('/<!--EDU_LEVEL-->.*<!--END_EDU_LEVEL-->/is','/<!--EDU_NAME-->.*<!--END_EDU_NAME-->/is','/<!--EDU_YEAR-->.*<!--END_EDU_YEAR-->/is','/<!--DELETE_BUTTON-->.*<!--END_DELETE_BUTTON-->/is'),
					array($tmpEducation['html_edulevel'],$tmpEducation['html_eduname'],$tmpEducation['html_eduyear'],$tmpEducation['html_deletebutton']),
					 $m[1]
				) . '</div>';
			}
		}
	}

	function buildCareer(){
		$id = 'ids_' . md5(microtime());
$this->fieldInfo['html_careerscripts'] = <<<EOT
		<script>
			var companyids = new Array();
			companyids[0] = "$id";
			function addCareer(s,t){
				//var l = getObj(t).lastChild;
				var n = getObj(s).cloneNode(true);
				n.style.display = '';
				n.id = '';
				var inputs = n.getElementsByTagName("input");
				for (var i=0; i<inputs.length; i++) {
					inputs[i].value = '';
				}
				var selects = n.getElementsByTagName("select");
				for (var j=0; j<selects.length; j++) {
					selects[j].options[0].selected = true;
				}
				var randomId = Math.random().toString().substring(2);
				var html = n.innerHTML.replace(/((w+_)?ids_)[a-z0-9]+/ig, '$1'+ randomId);
				n.id = 'careerItem_' + Math.random().toString().substring(2);
				n.innerHTML = '<a href="javascript://" class="fr mr20 s4 pr" onclick="delElement(getObj(\''+n.id+'\'));">删除</a>' + html;
				n.innerHTML = n.innerHTML.replace(/<span class="s1">\*<\/span>/ig, '');
				getObj(t).appendChild(n);
				try{
					getObj('companyname_ids_' + randomId + '_info').className = 'ignore';
					getObj('companyname_ids_' + randomId + '_info').innerHTML = '';
					getObj('companyname_ids_' + randomId + '_info').style.display = 'none';
				}catch(e){}
			}
			
			function deleteCareer(id) {
				if (!id) return false; 
				var url = 'pw_ajax.php?action=pwschools&job=delcareer&careerid=' + id;
				ajax.send(url,'',function(){
					var response = ajax.request.responseText.split("\t");
					if (response[0] == 'success') {
						delElement('careerItem_'+id);
						showDialog('success',response[1], 2);
					} else {
						showDialog('error', response[1], 2);
					}
				});
			}
		</script>
EOT;
$this->fieldInfo['html_careername'] = <<<EOT
			<input {$this->readonlyHtml} type="text" class="input" id="companyname_$id" name="new_career_companyname[]" />
EOT;
$this->fieldInfo['html_careerdate'] = <<<EOT
				<select {$this->disabledHtml} name="new_career_year[]">
EOT;
$date = getdate($GLOBALS['timestamp']);
$startYear = $date['year'];
$endYear = $startYear - 100;
			for($i = $startYear; $i>= $endYear ; $i--){
$this->fieldInfo['html_careerdate'] .= <<<EOT
				<option value="$i">$i</option>
EOT;
			}
$this->fieldInfo['html_careerdate'] .= <<<EOT
				</select>
<select {$this->disabledHtml} name="new_career_month[]">
EOT;
		for ($i = 1; $i<= 12; $i++) {
$this->fieldInfo['html_careerdate'] .= <<<EOT
<option value="$i">$i </option>
EOT;
}
$this->fieldInfo['html_careerdate'] .= <<<EOT
</select>

<select {$this->disabledHtml} name="new_career_day[]">
EOT;
		for ($i = 1; $i<= 31; $i++) {
$this->fieldInfo['html_careerdate'] .= <<<EOT
<option value="$i">$i </option>
EOT;
}
$this->fieldInfo['html_careerdate'] .= <<<EOT
</select>
EOT;

$this->fieldInfo['html_companyname_info'] = <<<EOT
<div class="ignore" id="companyname_{$id}_info" style="display:none"></div>
EOT;

		/*有默认值格式化默认值*/
		if (S::isArray($this->defaultValue) && preg_match('/<\!--DEFAULT_VALUE_TEMPLATE-->(.*)<\!--END_DEFAULT_VALUE_TEMPLATE-->/is',$this->getTemplateCareer(),$m)) {
			if(!$m[1]) return ;
			$countNum = 0;
			foreach ($this->defaultValue as $k=>$v) {
				if (!S::isArray($v)) continue;
				$countNum++;
				$tmpCareer = array();
				//公司名
$tmpCareer['html_careername'] = <<<EOT
			<input {$this->disabledHtml} type="text" class="input" onfocus="" id="companyname_{$v[careerid]}" name="career_companyname[{$v[careerid]}]" value="$v[companyname]" />
EOT;
				//入职日期
				$careerdate = getdate($v['starttime']);
$tmpCareer['html_careerdate'] = <<<EOT
				<select {$this->disabledHtml} name="career_year[{$v[careerid]}]">
EOT;
$date = getdate($GLOBALS['timestamp']);
$startYear = $date['year'];
$endYear = $startYear - 100;
			for($i = $startYear; $i>= $endYear ; $i--){
				$selectedHtml = $careerdate['year'] == $i ?' selected=1':'';
$tmpCareer['html_careerdate'] .= <<<EOT
				<option value="$i"$selectedHtml>$i</option>
EOT;
			}
$tmpCareer['html_careerdate'] .= <<<EOT
				</select>
<select {$this->disabledHtml} name="career_month[{$v[careerid]}]">
EOT;
		for ($i = 1; $i<= 12; $i++) {
			$selectedHtml = $careerdate['mon'] == $i ?'  selected=1':'';
$tmpCareer['html_careerdate'] .= <<<EOT
<option value="$i"$selectedHtml>$i </option>
EOT;
}
$tmpCareer['html_careerdate'] .= <<<EOT
</select>

<select {$this->disabledHtml} name="career_day[{$v[careerid]}]">
EOT;
		for ($i = 1; $i<= 31; $i++) {
			$selectedHtml = $careerdate['mday'] == $i ?'  selected=1':'';
$tmpCareer['html_careerdate'] .= <<<EOT
<option value="$i"$selectedHtml>$i </option>
EOT;
}
$tmpCareer['html_careerdate'] .= <<<EOT
</select>
EOT;

$tmpEducation['html_deletebutton'] = !$this->disabled && $countNum != 1 ? "<a href=\"javascript://\" class=\"fr mr20 s4 pr\" onclick=\"deleteCareer('$k');\">删除</a>" : '';
				//default value html
				$this->defaultValueHtml .= "<div id=careerItem_{$k}>" .  preg_replace(
					array('/<!--CAREER_NAME-->.*<!--END_CAREER_NAME-->/is','/<!--CAREER_DATE-->.*<!--END_CAREER_DATE-->/is','/<!--DELETE_BUTTON-->.*<!--END_DELETE_BUTTON-->/is'),
					array($tmpCareer['html_careername'],$tmpCareer['html_careerdate'],$tmpEducation['html_deletebutton']),
					$m[1]
				) . '</div>';
			}
		}
	}
	
	function initDisabledHtml(){
		$this->readonlyHtml = $this->disabled ? ' readonly="readonly"' : '';
		$this->disabledHtml = $this->disabled ? ' disabled="disabled" style="color:#999;"' : '';
	}
	function unsetDisabledHtml(){
		$this->readonlyHtml = $this->disabledHtml = '';
	}
	function isEmptyValue($value){
		if ($this->fieldInfo['fieldname'] == 'bday' && $value == '0000-00-00') return true;
		switch ($this->fieldInfo['type']) {
			//case $this->pwCustomerField->typeMap['checkbox']:
			case $this->pwCustomerField->typeMap['education']:
			case $this->pwCustomerField->typeMap['career']:
			case $this->pwCustomerField->typeMap['area']:
					if ($value == '') return true;
					return !S::isArray($value);
				break;
			case $this->pwCustomerField->typeMap['radio']:
					if ($value === '') return true;
					return !is_numeric($value);
				break;
			/*case $this->pwCustomerField->typeMap['area']:
				return $value <= 0;
				break;*/
			default:
				return $value == '';
			break;
		}
	}
}