<?php
define('SCR', 'link');
require_once ('global.php');
require_once (R_P . 'require/functions.php');

S::gp(array(
	'action'
));

if (in_array($action, array('previous', 'taglist', 'tag'))) {
	require R_P . 'actions/job/' . $action . '.php';
} else {
	Showmsg('undefined_action');
}