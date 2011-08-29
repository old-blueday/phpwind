<?php
!function_exists('readover') && exit('Forbidden');

$tool = $db->get_one("SELECT id,name FROM pw_tools WHERE id=" . S::sqlEscape($rt['paycredit']));

if ($tool) {

	$db->update("UPDATE pw_tools SET stock=stock-" . S::sqlEscape($rt['number']) . " WHERE id=" . S::sqlEscape($tool['id']));

	$db->pw_update(
		"SELECT uid FROM pw_usertool WHERE uid=" . S::sqlEscape($rt['uid']) . " AND toolid=" . S::sqlEscape($tool['id']),
		"UPDATE pw_usertool SET nums=nums+" . S::sqlEscape($rt['number']) . " WHERE uid=" . S::sqlEscape($rt['uid']) . " AND toolid=" . S::sqlEscape($tool['id']),
		"INSERT INTO pw_usertool SET " . S::sqlSingle(array('nums' => $rt['number'], 'uid' => $rt['uid'], 'toolid' => $tool['id']))
	);

	require_once(R_P.'require/tool.php');
	$logdata = array(
		'type'		=>	'buy',
		'nums'		=>	$nums,
		'money'		=>	$price,
		'descrip'	=>	'buy_descrip',
		'uid'		=>	$rt['uid'],
		'username'	=>	$rt['username'],
		'ip'		=>	$onlineip,
		'time'		=>	$timestamp,
		'toolname'	=>	$tool['name'],
		'from'		=>	'',
	);
	writetoollog($logdata);

	M::sendNotice(
		array($rt['username']),
		array(
			'title' => getLangInfo('writemsg','toolbuy_title'),
			'content' => getLangInfo('writemsg','toolbuy_content',array(
				'fee'		=> $fee,
				'toolname'	=> $tool['name'],
				'number'	=> $rt['number']
			)),
		)
	);
}

$ret_url = 'profile.php?action=toolcenter&job=mytool';
?>