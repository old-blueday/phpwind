<?php
!function_exists('readover') && exit('Forbidden');

//SEO setting
bbsSeoSettings('index');

require_once PrintEot('simple_header');

$lastgrad=1;
foreach($forum as $key => $value){
	unset($forums['li_add'],$forums['cate']);
	if($value['f_type']=='hidden' || (!$db_showcms && $value['cms']) || $value['ifcms'] == 2){
		continue;
	}

	$forums['fid']=$value['fid'];
	$forums['name']=$value['name'];

	if($value['type']=='category'){
		$forums['cate']=1;
		$forums['grad']=0;
	}elseif($value['type']=='forum'){
		$forums['grad']=1;
	}elseif($forum[$value['fup']]['type']=='forum'){
		$forums['grad']=2;
	}else{
		$forums['grad']=3;
	}
	$gradnum=$forums['grad']-$lastgrad;
	
	if($gradnum>0){
		for($i=0;$i<$gradnum;$i++){
			$forums['li_add'].="<ul>";
		}
	}elseif($gradnum<0){
		for($i=0;$i<-$gradnum;$i++){
			$forums['li_add'].='</ul>';
		}
	}else{
		$forums['li_add']='';
	}
	$lastgrad=$forums['grad'];
	$forumdb[]=$forums;
}
for($i=$gradnum;$i>0;$i--){
	$end_li.='</ul>';
}

require_once PrintEot('simple_index');
?>