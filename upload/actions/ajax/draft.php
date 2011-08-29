<?php
!defined('P_W') && exit('Forbidden');

!$_G['maxgraft'] && Showmsg('draft_right');

if (empty($_POST['step'])) {
	
	$db_showperpage = 5;
	S::gp(array('page'), 'GP', 2);
	$page < 1 && $page = 1;
	$rt = $db->get_one("SELECT COUNT(*) AS sum FROM pw_draft WHERE uid=" . S::sqlEscape($winduid));
	$maxpage = ceil($rt['sum'] / $db_showperpage);
	$maxpage && $page > $maxpage && $page = $maxpage;
	$limit = S::sqlLimit(($page - 1) * $db_showperpage, $db_showperpage);
	
	$query = $db->query("SELECT * FROM pw_draft WHERE uid=" . S::sqlEscape($winduid) . $limit);
	if ($db->num_rows($query) == 0) {
		Showmsg('draft_error');
	}
	$drdb = array();
	while ($rt = $db->fetch_array($query)) {
		$drdb[] = $rt;
	}
	require_once PrintEot('ajax');
	ajax_footer();

} elseif ($_POST['step'] == 2) {
	
	PostCheck();
	S::gp(array('atc_content'), 'P');
	!$atc_content && Showmsg('content_empty');
	$atc_content = str_replace('%26', '&', $atc_content);
	$rt = $db->get_one("SELECT COUNT(*) AS sum FROM pw_draft WHERE uid=" . S::sqlEscape($winduid));
	if ($rt['sum'] >= $_G['maxgraft']) {
		Showmsg('draft_full');
	}
	$db->update("INSERT INTO pw_draft SET " . S::sqlSingle(array(
		'uid' => $winduid,
		'content' => $atc_content
	)));
	Showmsg('save_success');

} elseif ($_POST['step'] == 3) {
	
	PostCheck();
	S::gp(array('atc_content', 'did'), 'P');
	!$atc_content && Showmsg('content_empty');
	$db->update('UPDATE pw_draft SET content=' . S::sqlEscape($atc_content) . ' WHERE uid=' . S::sqlEscape($winduid) . ' AND did=' . S::sqlEscape($did));
	Showmsg('update_success');

} else {
	
	PostCheck();
	S::gp(array('did'));
	$db->update('DELETE FROM pw_draft WHERE uid=' . S::sqlEscape($winduid) . ' AND did=' . S::sqlEscape($did));
	Showmsg('delete_success');
}
