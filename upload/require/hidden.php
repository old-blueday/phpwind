<?php
!function_exists('readover') && exit('Forbidden');
$newonline="$windid\t$timestamp\t$onlineip\t$fidwt\t$tidwt\t$groupid\t$wherebbsyou\t$acttime\t$uid\t<>\t";
$newonline=str_pad($newonline,$db_olsize)."\n";
if(checkinline(R_P.$D_name,$offset,$windid)){
	$isModify=0;
	writeinline(R_P.$D_name,$newonline,$offset);
} else{
	list($offset,$isModify)=GetInsertOffset(R_P.$D_name);
	writeinline(R_P.$D_name,$newonline,$offset);
}
?>