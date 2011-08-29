<?php
!function_exists('adminmsg') && exit('Forbidden');
//* @include_once pwCache::getPath(D_P . 'data/bbscache/o_config.php');
pwCache::getData(D_P . 'data/bbscache/o_config.php');
@include_once (R_P . 'require/credit.php');
@include_once (A_P . 'lib/utility.class.php');
@include_once (A_P . 'lang/lang_o_hot.php');
$utility = new HotModuleUtility();
if(empty($action)){
	S::gp(array("hot_baseSet"),'P');
	if ($hot_baseSet=="baseSet") {
		S::gp(array("config","hot_userGroup"),'P');
		$config["hot_groups"] = ','.implode(',',(array)$hot_userGroup).',';
		$cacheFlag=false;
		foreach ($config as $key => $value) {
			if(${'o_'.$key} != $value){
				$db->pw_update("SELECT * FROM pw_hack WHERE hk_name=".S::sqlEscape('o_'.$key),
					   "UPDATE pw_hack SET vtype=".S::sqlEscape('string').", hk_value=".S::sqlEscape($value)." WHERE hk_name=".S::sqlEscape('o_'.$key),
					   "INSERT INTO pw_hack SET hk_name=".S::sqlEscape('o_'.$key).", vtype=".S::sqlEscape('string').", hk_value=".S::sqlEscape($value));
				$cacheFlag=true;
			}
		}
		$cacheFlag && updatecache_conf('o',true);
		adminmsg('operate_success');
	}else{
		$hot_userGroup='';
		$num='';
		foreach ($ltitle as $key => $value) {
			if(!in_array($key,array(1,2,6,7))){
				$num++;
				$tr = $num % 4 == 0 ? '' : '';
				$checked = strpos($o_hot_groups,",".$key.",") !== false ? 'checked' : '';
				$hot_userGroup .= "<li><input type=\"checkbox\" name=\"hot_userGroup[]\" value=\"$key\" ".$checked." /> $value </li>".$tr;
			}
		}
		$hot_userGroup && $hot_userGroup = "<ul class=\"list_A list_120 cc\">$hot_userGroup</ul>";
		ifcheck($o_hot_open,"hot_open");
	}
}elseif($action=="hotTypeSet"){
	S::gp(array('updateAll'),'GP');
	if ($updateAll=="updateAll") {
		S::gp(array('fTime','fType','active','sort','type_name','display'));
		$query = $db->query("SELECT * FROM pw_modehot ORDER BY id");
		$num= 0;
		while ($rt = $db->fetch_array($query)) {
			$num++;
			$sql = "";
			$flag = false;
			$filterTypeData = (array)unserialize($rt["filter_type"]);
			$filterTimeData = (array)unserialize($rt["filter_time"]);
			if ($sort[$rt["id"]] != "" && !is_numeric($sort[$rt["id"]])) {
				$basename = "javascript:history.go(-1);";
				adminmsg('mode_o_hot_sortIsInt');
			}
			$sort[$rt["id"]] && $sort[$rt["id"]] != $rt["sort"] && $flag = true;
			$type_name[$rt["id"]] && $type_name[$rt["id"]] != $rt["type_name"] && $flag = true;
			if($fTime && $fTime[$rt["id"]]){
				$fTime[$rt["id"]] != $filterTimeData["current"] && $flag = true;
				$filterTimeData["current"] = $fTime[$rt["id"]];
			}
			if($fType && $fType[$rt["id"]]){
				$fType[$rt["id"]] != $filterTypeData["current"] && $flag = true;
				$filterTypeData["current"] = $fType[$rt["id"]];
			}
			$display[$rt["id"]] = $display[$rt["id"]] ? '1' : '0';
			$display[$rt["id"]] != $rt["display"] && $flag = true;
			$active[$rt["id"]] = $active[$rt["id"]] ? '1' : '0';
			$active[$rt["id"]] != $rt["active"] && $flag = true;
			if ($flag) {
				$sql = "UPDATE pw_modehot SET sort=".S::sqlEscape($sort[$rt["id"]]).", type_name=".S::sqlEscape($type_name[$rt["id"]]).", 
						filter_type=".S::sqlEscape(serialize($filterTypeData)).", filter_time=".S::sqlEscape(serialize($filterTimeData)).", 
						display=".S::sqlEscape($display[$rt["id"]]).", active=".S::sqlEscape($active[$rt["id"]])." WHERE id=".S::sqlEscape($rt["id"]);
				$db->update($sql);
			}
		}
		$basename = $basename."&action=hotTypeSet";
		adminmsg('operate_success');
	}elseif($updateAll=="default"){
		$default = $lang['o_hot']['default'];
		$db->update("DELETE FROM pw_modehot");
		foreach ($default as $key => $value) {
			$sql = "INSERT INTO pw_modehot SET id=".S::sqlEscape($value['id']).", parent_id=".S::sqlEscape($value["parent_id"]).", sort=".S::sqlEscape($value["sort"]).", type_name=".S::sqlEscape($value["type_name"]).", 
					filter_type=".S::sqlEscape(serialize($value["filter_type"])).", filter_time=".S::sqlEscape(serialize($value["filter_time"])).", 
					display=".S::sqlEscape($value["display"]).", active=".S::sqlEscape($value["active"]).", tag=".S::sqlEscape($value["tag"]);
			$db->update($sql);
		}
		$basename = $basename."&action=hotTypeSet";
		adminmsg('operate_success');
	}else{
		$sqlQueryHotType = "SELECT * FROM pw_modehot ORDER BY sort";
		$query = $db->query($sqlQueryHotType);
		while($rt = $db->fetch_array($query)){
			$rt_active = $rt["active"] ? 'checked' : '';
			$rt_display = $rt["display"] ? 'checked' : '';
			$fType = null;
			$filter = $utility->activeCurrentFilter($rt,null,$fType,'admin');
			$htmlFilterType = $filter['selectType'];
			$htmlFilterTime = $filter['selectTime'];
			if (!$utility->getRateSet($rt['tag'])) {
				continue;
			}
			if ($rt["parent_id"]) {
				$htmlHotList[] = array('active'		=>	$rt_active,
									   'parent'		=>  $rt["parent_id"],
									   'sort'		=>	$rt["sort"],
									   'typeName' 	=>  $rt["type_name"],
									   'filterType' =>  $htmlFilterType,
									   'filterTime' =>  $htmlFilterTime,
									   'display'	=>  $rt_display,
									   'id'			=>  $rt["id"]);
			}else{
				$htmlHotParentList[] = array('active'		=>	$rt_active,
									   'child'		=>  $rt["parent_id"],
									   'sort'		=>	$rt["sort"],
									   'typeName' 	=>  $rt["type_name"],
									   'filterType' =>  $htmlFilterType,
									   'filterTime' =>  $htmlFilterTime,
									   'display'	=>  $rt_display,
									   'id'			=>  $rt["id"]);
			}
		}
	}
}elseif($action=="hotEdit"){
	S::gp(array("updateHot","tag"),'P');
	if($updateHot=="updateHot"){
		$filterType = $utility->getFilter($tag,'type');
		$filterTime = $utility->getFilter($tag,'time');      
		S::gp(array_merge(array("id","currentFilterType","currentFilterTime","active","display","itemsCount",
				"typeName","fFilterType","fFilterTime"),$utility->createParam($filterType,'filterTypeItem'),
		$utility->createParam($filterTime,'filterTimeItem')),'P');
		$active = $active ? $active : '0';
		$display = $display ? $display : '0';
		
		foreach ($filterType as $key => $value) {
			if (!in_array($key,$fFilterType)) {
				${'filterTypeItem_'.$key} = "";
			}
			$fFilterTypeValues[] = ${'filterTypeItem_'.$key};
		}
		
		foreach ($filterTime as $key => $value) {
			if (!in_array($key,$fFilterTime)) {
				${'filterTimeItem_'.$key} = "";
			}
			$fFilterTimeValues[] = ${'filterTimeItem_'.$key};
		}
		
		foreach($fFilterTypeValues as $key => $value){
			if ($value != "" && !is_numeric($value)) {
				$basename = "javascript:history.go(-1);";
				adminmsg('mode_o_hot_itemIsInt');
			}
		}
		foreach($fFilterTimeValues as $key => $value){
			if ($value != "" && !is_numeric($value)) {
				$basename = "javascript:history.go(-1);";
				adminmsg('mode_o_hot_itemIsInt');
			}
		}
		
		$typeKeys = array_keys($filterType);
		$timeKeys = array_keys($filterTime);
		$currentFilterType && !in_array($currentFilterType,$typeKeys) && $currentFilterType = $typeKeys[0];
		$currentFilterTime && !in_array($currentFilterTime,$timeKeys) && $currentFilterTime = $timeKeys[0];
		empty($fFilterType) && $fFilterType = array($currentFilterType);
		empty($fFilterTime) && $fFilterTime = array($currentFilterTime);
		
		$filterTypeData = array_merge(array('current'=>$currentFilterType),
								      array('filters'=>$fFilterType),
								      array('filterItems'=>$fFilterTypeValues));
		$filterTimeData = array_merge(array('current'=>$currentFilterTime),
								      array('filters'=>$fFilterTime),
								      array('filterItems'=>$fFilterTimeValues));
		if ($itemsCount) {
			$filterTypeData = $filterTimeData = $itemsCount;
		}else{
			$filterTypeData = S::sqlEscape(serialize($filterTypeData));
			$filterTimeData = S::sqlEscape(serialize($filterTimeData));
		}
		$sqlUpdateHot = "UPDATE pw_modehot SET type_name=".S::sqlEscape($typeName).",
						filter_type=".$filterTypeData.", filter_time=".$filterTimeData.", 
						display=".S::sqlEscape($display).", active=".S::sqlEscape($active)." WHERE id=".S::sqlEscape($id);
		$db->update($sqlUpdateHot);
		$basename = $basename."&action=hotEdit&hotId=".$id;
		adminmsg('operate_success');
	}else{
		S::gp(array('hotId'),'G');
		$sqlQueryHotById= "SELECT * FROM pw_modehot WHERE id=".S::sqlEscape($hotId);
		$rt = $db->get_one($sqlQueryHotById);
		$active = $rt["active"] ? 'checked' : '';
		$display = $rt["display"] ? 'checked' : '';
		$filterTypeData = (array)unserialize($rt["filter_type"]);
		$filterTimeData = (array)unserialize($rt["filter_time"]);
		if (!$filterTypeData['filters'] && !$filterTimeData['filters']) {
			$itemsCount = $rt["filter_type"];
		}
		$htmlFilterType = $utility->getFilterHtmlData($utility->getFilter($rt['tag'],'type'),$filterTypeData,'filterTypeItem_');
		$htmlFilterTime = $utility->getFilterHtmlData($utility->getFilter($rt['tag'],'time'),$filterTimeData,'filterTimeItem_',$rt['type_name']);
	}
}
require_once PrintApp('admin');
?>