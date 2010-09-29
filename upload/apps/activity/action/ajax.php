<?php
!defined('P_W') && exit('Forbidden');
define('AJAX', '1');
require_once (R_P . 'require/functions.php');

!$winduid && Showmsg('not_login');

InitGP(array('action'));

if ('delactivity' == $action) {
	InitGP(array('id'),'',2);
	if (!$id) {
		echo 'undefined_action';
		ajax_footer();
	}
	$delarticle = L::loadClass('DelArticle', 'forum');
	$readdb = $delarticle->getTopicDb('tid='.pwEscape($id));
	if (!$readdb){
		echo 'mode_o_no_activity';
		ajax_footer();
	}
	if ($winduid != $readdb['authorid'] && !$isGM && !$SYSTEM['delactive']) {
		echo 'mode_o_delactivity_permit_err';
		ajax_footer();
	}
	$delarticle->delTopic($readdb, $db_recycle);
	echo "success";
	ajax_footer();
}

?>