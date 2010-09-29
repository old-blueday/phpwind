<?php
define('SCR','cate');
require_once('global.php');
$_GET['fid'] = (int)GetGP('cateid');


InitGP(array('cateid'),'GP',2);
empty($cateid) && Showmsg('data_error');
ObHeader('index.php?m=bbs&cateid='.$cateid);

?>