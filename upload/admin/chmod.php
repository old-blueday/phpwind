<?php
!function_exists('adminmsg') && exit('Forbidden');

//@include_once Pcv(D_P."data/style/$skin.php");
if (!$db_attachname || $db_htmdir) {
	$query = $db->query("SELECT db_name,db_value FROM pw_config WHERE db_name IN ('db_attachname','db_htmdir')");
	while ($rt = $db->fetch_array($query)) {
		${$rt['db_name']} = $rt['db_value'];
	}
}
$filepath = array(
	D_P.'data/',
	D_P.'data/sql_config.php',
	D_P.'data/bbscache/',
	D_P.'data/forums/',
	D_P.'data/guestcache/',
	D_P.'data/groupdb/',
	D_P.'data/style/',
	D_P.'data/tmp/',
	D_P.'data/tplcache/',
	R_P."$db_attachname/",
	R_P."$db_attachname/cn_img/",
	R_P."$db_attachname/mini/",
	R_P."$db_attachname/mutiupload/",
	R_P."$db_attachname/photo/",
	R_P."$db_attachname/pushpic/",
	R_P."$db_attachname/thumb/",
	R_P."$db_attachname/upload/",
	R_P."$db_htmdir/",
	R_P."$db_htmdir/channel/",
);
$filemode = array();
foreach($filepath as $key => $value){
	if (substr($value,-1)=='/') {
		$value = substr($value,0,strlen($value)-1);
		if (!file_exists($value)) {
			@mkdir($value,0777);
			@touch("$value/index.html");
		}
	}
	if(!file_exists($value)){
		$filemode[$key] = 1;
	} elseif(!pwWritable($value)){
		$filemode[$key] = 2;
	} else{
		$filemode[$key] = 0;
	}
}
include PrintEot('chmod');exit;
?>