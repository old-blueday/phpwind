<?php
require_once('global.php');
//* include_once pwCache::getPath(D_P.'data/bbscache/ol_config.php');
pwCache::getData(D_P.'data/bbscache/ol_config.php');
if(!$ol_onlinepay){
	Showmsg($ol_whycolse);
}
if (!$ol_paypal || !$ol_paypalcode) {
	Showmsg('olpay_seterror');
}
if ($_GET['verifycode'] != $ol_paypalcode) {

	Showmsg('undefined_action');

} elseif (S::getGP('payment_status') == 'Completed') {

	S::gp(array('invoice','mc_gross'));
	$rt = $db->get_one("SELECT c.*,m.username FROM pw_clientorder c LEFT JOIN pw_members m USING(uid) WHERE order_no=".S::sqlEscape($invoice));
	if ($rt['state'] == '0') {
		if ($rt['price'] != $mc_gross) {
			Showmsg('gross_error');
		}
		if (file_exists(R_P."require/olpay/pay_{$rt[type]}.php")) {
			require_once S::escapePath(R_P."require/olpay/pay_{$rt[type]}.php");
		}
		$db->update("UPDATE pw_clientorder SET state=2 WHERE order_no=" . S::sqlEscape($invoice));

		require_once(R_P.'require/posthost.php');
		$getdb = '';
		foreach ($_POST as $key => $value) {
			$getdb .= $key."=".urlencode($value)."&";
		}
		$getdb .= 'date='.get_date($timestamp,'Y-m-d-H:i:s');
		$getdb .= '&site='.$pwServer['HTTP_HOST'];
		PostHost("http://pay.phpwind.net/pay/stats.php",$getdb,'POST');exit;
	} else {
		Showmsg('undefined_action');
	}
}
?>