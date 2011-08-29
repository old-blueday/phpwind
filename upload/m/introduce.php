<?php
error_reporting(0);
define('W_P', __FILE__ ? dirname(__FILE__) . '/' : './');
require_once (W_P . '../global.php');
$db_bbsurl = $_mainUrl;
require_once (R_P . 'require/header.php');
require Pcv(W_P . 'template/introduce.htm');;
footer();