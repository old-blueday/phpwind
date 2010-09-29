<?php
!function_exists('readover') && exit('Forbidden');

function P_serialize($array,$ret='',$i=1){
	foreach($array as $k => $v){
		if(is_array($v)){
			$next = $i+1;
			$ret .= "$k\t";
			$ret  = P_serialize($v,$ret,$next);
			$ret .= "\n$i\n";
		} else{
			$ret .= "$k\t$v\n$i\n";
		}
	}
	if(substr($ret,-3) == "\n$i\n"){
		$ret = substr($ret,0,-3);
	}
	return $ret;
}
function P_unserialize($str,$array=array(),$i=1){
	$str = explode("\n$i\n",$str);
	foreach ($str as $key => $value){
		$k = substr($value,0,strpos($value,"\t"));
		$v = substr($value,strpos($value,"\t")+1);
		if (strpos($v,"\n") !== false){
			$next  = $i+1;
			$array[$k] = P_unserialize($v,$array[$k],$next);
		} elseif(strpos($v,"\t") !== false){
			$array[$k] = P_array($array[$k],$v);
		} else {
			$array[$k] = $v;
		}
	}
	return $array;
}
function P_array($array,$string){
	$k = substr($string,0,strpos($string,"\t"));
	$v = substr($string,strpos($string,"\t")+1);
	if (strpos($v,"\t") !== false){
		$array[$k] = P_array($array[$k],$v);
	} else {
		$array[$k] = $v;
	}
	return  $array;
}
?>