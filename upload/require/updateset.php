<?php
!function_exists('readover') && exit('Forbidden');

function write_config($newconfig=array()){
	global $tplpath;
	if (!empty($newconfig) && is_array($newconfig)) {
		foreach ($newconfig as $key => $value) {
			${$key} = $value;
		}
	} else {
		include (D_P.'data/sql_config.php');
		//* extract(pwCache::getData(D_P.'data/sql_config.php', false));
	}
	$db_hostweb!=0 && $db_hostweb = 1;
	!$pconnect && $pconnect = 0;
	$att_url = $mg_a = $mg_p = '';
	foreach ($manager as $value) {
		$mg_a .= ",'$value'";
	}
	foreach ($manager_pwd as $value) {
		$mg_p .= ",'$value'";
	}
	foreach ($attach_url as $value) {
		$att_url .= ",'$value'";
	}
	$mg_a = substr($mg_a,1); $mg_p = substr($mg_p,1); $att_url = substr($att_url,1);
	if (file_exists(R_P."template/admin_$tplpath")) {
		include S::escapePath(R_P."template/admin_$tplpath/cp_lang_all.php");
	} else {
		include R_P."template/admin/cp_lang_all.php";
	}
	foreach (array('sqlinfo','dbhost','dbuser','dbname','database','PW','pconnect','charset','managerinfo','managername','hostweb','attach_url','slaveConfig') as $I) {
		eval('$lang[\'all\']['.$I.']="'.addcslashes($lang['all'][$I],'"').'";');
	}
	$writetofile =
"<?php
/**
{$lang[all][sqlinfo]}
*/
	{$lang[all][dbhost]}
\$dbhost = '$dbhost';

	{$lang[all][dbuser]}
\$dbuser = '$dbuser';
\$dbpw = '$dbpw';

	{$lang[all][dbname]}
\$dbname = '$dbname';

	{$lang[all][database]}
\$database = '$database';

	{$lang[all][PW]}
\$PW = '$PW';

	{$lang[all][pconnect]}
\$pconnect = '$pconnect';

/**
{$lang[all][charset]}
*/
\$charset = '$charset';

/**
{$lang[all][managerinfo]}
*/
	{$lang[all][managername]}
\$manager = array($mg_a);

	{$lang[all][managerpwd]}
\$manager_pwd = array($mg_p);

/**
{$lang[all][hostweb]}
*/
\$db_hostweb = '$db_hostweb';

/**
{$lang[all][distribute]}
*/
\$db_distribute = '$db_distribute';

/**
{$lang[all][attach_url]}
*/
\$attach_url = array($att_url);

/**
{$lang[all][slaveConfig]}
*/
\$slaveConfigs = ";
	pwCache::writeover(D_P.'data/sql_config.php',$writetofile . pw_var_export($slaveConfigs). ";\r\n?>");
}
?>