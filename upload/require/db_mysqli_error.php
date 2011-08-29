<?php
!function_exists('readover') && exit('Forbidden');

Class DB_ERROR {
	function DB_ERROR($msg) {
		global $db_bbsname,$db_obstart,$REQUEST_URI,$dbhost,$db,$pwServer,$db_charset;
		$sqlerror = mysqli_error($db->sql);
		$sqlerrno = mysqli_errno($db->sql);
		$sqlerror = str_replace($dbhost,'dbhost',$sqlerror);
		ob_end_clean();
		$db_obstart && function_exists('ob_gzhandler') ? ob_start('ob_gzhandler') : ob_start();
		echo"<html><head><meta http-equiv='Content-Type' content='text/html; charset=$db_charset' /><title>$db_bbsname</title><style type='text/css'>P,BODY{FONT-FAMILY:tahoma,arial,sans-serif;FONT-SIZE:11px;}A { TEXT-DECORATION: none;}a:hover{ text-decoration: underline;}TD { BORDER-RIGHT: 1px; BORDER-TOP: 0px; FONT-SIZE: 16pt; COLOR: #000000;}</style><body>\n\n";
		echo"<table style='TABLE-LAYOUT:fixed;WORD-WRAP: break-word'><tr><td>$msg";
		echo"<br><br><b>The URL Is</b>:<br>http://".$pwServer['HTTP_HOST'].$REQUEST_URI;
		echo"<br><br><b>MySQL Server Error</b>:<br>$sqlerror  ( $sqlerrno )  <a target='_blank' href='http://faq.phpwind.net/mysql.php?id=$sqlerrno'>&#26597;&#30475;&#38169;&#35823;&#30456;&#20851;&#20449;&#24687;</a>";
		echo"<br><br><b>You Can Get Help In</b>:<br><a target='_blank' href='http://www.phpwind.net'><b>http://www.phpwind.net</b></a>";
		echo"</td></tr></table></body></html>";
		$this->dblog($msg);
		exit;
	}
	function dblog($msg){
		$msg = str_replace(array("\n","\r","<"),array('','','&lt;'),$msg);
		if (file_exists(D_P.'data/bbscache/dblog.php')){
			pwCache::setData(D_P.'data/bbscache/dblog.php',"$msg\n", false, 'ab');
		} else{
			pwCache::setData(D_P.'data/bbscache/dblog.php',"<?php die;?>\n$msg\n");
		}
	}
}
?>