<?php
!defined('P_W') && exit('Forbidden');

!$db_ifselfshare && Showmsg("sharelink_colse");

if (empty($_POST['step'])) {
	
	require_once PrintEot('ajax');
	ajax_footer();

} else {
	
	PostCheck();
	S::gp(array(
		'linkname',
		'linkurl',
		'username',
		'linkdescrip',
		'linklogo'
	), 'P');
	(!$linkname || !$linkurl) && Showmsg('sharelink_link_empty');
	!$linkdescrip && $linkdescrip = '';
	$username = !$username?$windid:$username.'('.$windid.')';
	!$linklogo && $linklogo = '';
	$linkurl = strtolower($linkurl);
	strncmp($linkurl, 'http://', 7) != 0 && Showmsg('sharelink_link_error');
	$rs = $db->get_one("SELECT sid FROM pw_sharelinks WHERE username=" . S::sqlEscape($username));
	$rs && Showmsg('sharelink_apply_limit');
	$pwSQL = S::sqlSingle(array(
		'name' => $linkname,
		'url' => $linkurl,
		'username' => $username,
		'descrip' => $linkdescrip,
		'logo' => $linklogo,
		'ifcheck' => 0,
		'username' => $username
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
