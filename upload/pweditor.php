<?php
define('SCR', 'job');
require_once ('global.php');

S::gp(array(
	'action'
));
$whiteActions = array(
	'attach', //附件上传
	'image',//图片
	'modifyattach',//修改附件
);
if (in_array($action, $whiteActions)) {
	require S::escapePath(R_P . 'actions/pweditor/' . $action . '.php');
} else {
	Showmsg('undefined_action');
}
?>