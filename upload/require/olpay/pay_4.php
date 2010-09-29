<?php
!function_exists('readover') && exit('Forbidden');

$pwSQL = array();
$invcodes=array();
$fistinvcode = '';
for ($i = 0; $i < $rt['number']; $i++) {
	$invcode = randstr(16);
	$i == 0 && $fistinvcode = $invcode;
	//$invcodes .= ($invcodes ? "\n" : '') . $invcode;
	$invcodes[] = $invcode;
	$pwSQL[] = array(
		'invcode'	=> $invcode,
		'uid'		=> 0,
		'createtime'=> $timestamp
	);
}

if($invcodes){
	$invlink = '';
	foreach ($invcodes as $key => $value) {
		$invlink .= '<a href=\"' . $db_bbsurl . '/' . $db_registerfile . '?invcode=' . $value . '\">' . $db_bbsurl . '/' . $db_registerfile . '?invcode=' . $value . '</a><br>';
	}

}
if ($pwSQL) {
	$db->update("INSERT INTO pw_invitecode (invcode,uid,createtime) VALUES " . pwSqlMulti($pwSQL));
	$inv_id = $db->insert_id();
	$db->update("UPDATE pw_clientorder SET paycredit=" . pwEscape($inv_id) . ' WHERE id=' . pwEscape($rt['id']));
}
require_once(R_P.'require/sendemail.php');
$sendinfo = sendemail($rt['payemail'],'email_invite_subject','email_invite_content','email_additional');

$ret_url = $regurl.'?invcode=' . $fistinvcode;
?>