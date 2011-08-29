<?php
!defined('P_W') && exit('Forbidden');
define ( "H_R", R_P . "hack/rate/" );
define ( "L_R", R_P . "lib/rate/" );
S::gp ( array ('ajax' ) );
$action = strtolower ( ($job) ? $job : "admin" );
$filepath = H_R . "action/" . $action . "Action.php";

(! file_exists ( $filepath )) && exit ();

if ($job != "ajax") {
	require H_R . '/template/layout.php';
} else {
	require_once S::escapePath($filepath);
}

?>