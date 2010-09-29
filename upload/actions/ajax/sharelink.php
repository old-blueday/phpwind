<?php
!defined('P_W') && exit('Forbidden');

!$db_ifselfshare && Showmsg("sharelink_colse");

if (empty($_POST['step'])) {
	
	require_once PrintEot('ajax');
	ajax_footer();

} else {
	
	PostCheck();
	InitGP(array(
		'linkname',
		'linkurl',
		'linkdescrip',
		'linklogo'
	), 'P');
	(!$linkname || !$linkurl) && Showmsg('sharelink_link_empty');
	!$linkdescrip && $linkdescrip = '';
	!$linklogo && $linklogo = '';
	$linkurl = strtolower($linkurl);
	strncmp($linkurl, 'http://', 7) != 0 && Showmsg('sharelink_link_error');
	$rs = $db->get_one("SELECT sid FROM pw_sharelinks WHERE username=" . pwEscape($windid));
	$rs && Showmsg('sharelink_apply_limit');
	$pwSQL = pwSqlSingle(array(
		'name' => $linkname,
		'url' => $linkurl,
		'descrip' => $linkdescrip,
		'logo' => $linklogo,
		'ifcheck' => 0,
		'username' => $windid
	));
	$db->update("INSERT INTO pw_sharelinks SET $pwSQL");
	
	M::sendNotice(
		array($manager),
		array(
			'title' => getLangInfo('writemsg','sharelink_apply_title'),
			'content' => getLangInfo('writemsg','sharelink_apply_content',array(
				'username' => $windid,
				'time' => get_date($timestamp)
			)),
		)
	);
	
	Showmsg("sharelink_success");
}
