<?php
!function_exists('readover') && exit('Forbidden');
/**
$query = $db->query("SELECT id,awardee,level FROM pw_medalslogs WHERE action='1' AND state='0' AND timelimit>0 AND awardtime+timelimit*2592000<".S::sqlEscape($timestamp));
$medaldb = $namedb = array();
while ($rt = $db->fetch_array($query)) {
	$medaldb[$rt['awardee']][] = array($rt['id'],$rt['level']);
	$namedb[] = $rt['awardee'];
}
if ($namedb) {
	pwCache::getData(D_P.'data/bbscache/medaldb.php');
	$pwSQL = $ids = $medaluser  = array();
	$reason = S::escapeChar(getLangInfo('other','medal_reason'));
	
	$userService = L::loadClass('UserService', 'user');
	foreach ($userService->getByUserNames(array_unique($namedb)) as $rt) {
		$medals = ",".$rt['medals'].",";
		$medalname = '';
		foreach ($medaldb[$rt['username']] as $key => $value) {
			$ids[] = $value[0];
			$medal = $value[1];
			$pwSQL[] = array($rt['username'],'SYSTEM',$timestamp,$medal,2,$reason);
			$medals = str_replace(",$medal,",',',$medals);
			$medaluser[] = '(uid='.S::sqlEscape($rt['uid']).' AND mid='.S::sqlEscape($medal).')';
			$medalname .= $medalname ? ','.$_MEDALDB[$medal]['name'] : $_MEDALDB[$medal]['name'];
		}
		$metal_cancel = S::escapeChar(getLangInfo('other','metal_cancel'));
		$metal_cancel_text = S::escapeChar(getLangInfo('other','metal_cancel_text',array('medalname'=>$medalname)));
		M::sendNotice(array($rt['username']),array('title' => $metal_cancel,'content' => $metal_cancel_text));
		$medals = substr($medals,1,-1);
		$userService->update($rt['uid'], array('medals' => $medals));
	}
	$pwSQL		&& $db->update("INSERT INTO pw_medalslogs (awardee,awarder,awardtime,level,action,why) VALUES".S::sqlMulti($pwSQL,false));
	$ids		&& $db->update("UPDATE pw_medalslogs SET state='1' WHERE id IN(".S::sqlImplode($ids,false).")");
	$medaluser	&& $db->update("DELETE FROM pw_medaluser WHERE ".implode(' OR ',$medaluser));
	updatemedal_list();
}

function updatemedal_list(){
	global $db;
	$query = $db->query("SELECT uid FROM pw_medaluser GROUP BY uid");
	$medaldb = '<?php die;?>0';
	while($rt=$db->fetch_array($query)){
		$medaldb .= ','.$rt['uid'];
	}
	pwCache::setData(D_P.'data/bbscache/medals_list.php',$medaldb);
}
*/
$medalService = L::loadClass('medalservice','medal');
$medalService->recoverOverdueMedals();
?>