<?php
!function_exists('readover') && exit('Forbidden');

Class DB_ERROR {
	function DB_ERROR($msg) {
		global $db_bbsname,$db_obstart,$REQUEST_URI,$dbhost,$db,$pwServer,$db_charset;
		$tmp = $db->getCurrentDb();
		$sqlerror = mysqli_error($tmp->sql);
		$sqlerrno = mysqli_errno($tmp->sql);
		$sqlerror = str_replace($dbhost,'dbhost',$sqlerror);
		ob_end_clean();
		$db_obstart && function_exists('ob_gzhandler') ? ob_start('ob_gzhandler') : ob_start();
		if (defined('AJAX')) {
			header("Content-Type: text/xml;charset=$db_charset");
			echo "<?xml version=\"1.0\" encoding=\"$db_charset\"?><ajax><![CDATA[$msg]]></ajax>";
		} else {
			echo"<!doctype html><head><meta charset='$db_charset' /><title>$db_bbsname</title><link rel=\"stylesheet\" href=\"images/pw_core.css?101128\" /><style>.tips{border:3px solid #dcc7ab;background:#fffef5;font:12px/1.5 Arial,Microsoft Yahei,Simsun;color:#000;padding:20px;width:500px;margin:50px auto;-moz-box-shadow:0 0 5px #eaeaea;box-shadow:0 0 5px #eaeaea;}a{text-decoration:none;color:#014c90;}a:hover,.alink a,.link{text-decoration:underline;}</style>";
			echo"<div class=\"tips\"><table width=\"100%\"><tr><td><h2 class=\"f14 b\">&#x9519;&#x8BEF;&#x4FE1;&#x606F;:</h2><p>$msg</p><br>";
			echo"<h2 class=\"f14 b\">&#x94FE;&#x63A5;&#x5730;&#x5740;(The URL Is):</h2>http://".$pwServer['HTTP_HOST'].$REQUEST_URI;
			echo"<br><br><h2 class=\"f14 b\">MySQL&#x670D;&#x52A1;&#x5668;&#x9519;&#x8BEF;(MySQL Server Error):</h2>$sqlerror  ( $sqlerrno )  <a target='_blank' href='http://faq.phpwind.net/mysql.php?id=$sqlerrno'>&#26597;&#30475;&#38169;&#35823;&#30456;&#20851;&#20449;&#24687;</a><br><br>";
			echo"<h2 class=\"f14 b\">&#x5BFB;&#x6C42;&#x5E2E;&#x52A9;(You Can Get Help In):</h2><a target='_blank' href='http://www.phpwind.net'>http://www.phpwind.net</a>";
			echo"</td></tr></table></div></body></html>";
		}
		$this->dblog($msg);
		exit;
	}
	function dblog($msg){
		$msg = str_replace(array("\n","\r","<"),array('','','&lt;'),$msg);
		if (file_exists(D_P.'data/bbscache/dblog.php')){
			pwCache::writeover(D_P.'data/bbscache/dblog.php',"$msg\n", 'ab');
		} else{
			pwCache::writeover(D_P.'data/bbscache/dblog.php',"<?php die;?>\n$msg\n");
		}
	}
}
?>