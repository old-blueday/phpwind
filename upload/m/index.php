<?php 
require_once('wap_global.php');
$a = S::getGP('a');
empty($a) && $a = "index";
if(in_array($a,  array('index','quit','forum','read','list','myfav','myhome','login','search',
'bbsinfo','items','msg','recommend','reply_all','reply','mawhole','upload','job','ms_index',
'mybbs','myphone','upface','post','register','action','addtofav'))){
    require_once S::escapePath ( W_P . "control/" . $a .".php" );
}else{
    exit('Forbidden');
}
?>