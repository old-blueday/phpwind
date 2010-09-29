<?php
!function_exists('readover') && exit('Forbidden');

function wap_header($id,$title,$url="",$t=""){
	header("Content-type: text/vnd.wap.wml;");
	require PrintEot('wap_header');
}
function wap_footer(){
	global $wind_version,$db_obstart,$windid,$db_charset,$db_wapcharset,$prog,$chs;
	require_once PrintEot('wap_footer');
	$output = ob_get_contents();
	ob_end_clean();
	$db_obstart && function_exists('ob_gzhandler') ? ob_start('ob_gzhandler') : ob_start();
	if($db_charset != 'utf8'){
		L::loadClass('Chinese', 'utility/lang', false);
		$chs = new Chinese();
		$output = $chs->Convert($output,$db_charset,($db_wapcharset ? 'UTF8' : 'UNICODE'));
	}
	$output = str_replace(array('<!--<!---->','<!---->'),'',$output);
	echo $output;flush();exit;
}
function wap_output($output){
	echo $output;
}
function wap_msg($msg,$url="",$t="10"){
	@extract($GLOBALS, EXTR_SKIP);
	global $db_bbsname,$db_obstart;
	ob_end_clean();
	$db_obstart && function_exists('ob_gzhandler') ? ob_start('ob_gzhandler') : ob_start();
	wap_header('msg',$db_bbsname,$url,$t);
	$msg = getLangInfo('wap',$msg);
	wap_output("<p>$msg".($url ? " <a href='$url'>".getLangInfo('wap','wap_msg_view')."</a>" : '')."</p>\n");
	wap_footer();
}
function wap_login($username,$password,$safecv,$lgt=0) {
	global $db,$timestamp,$onlineip,$db_ckpath,$db_ckdomain,$db_bbsurl,$db_ifsafecv;
	$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
	$men = $lgt ? $userService->get($username, true, true) : $userService->getByUserName($username, true, true);
	if ($men) {
		$e_login = explode("|",$men['onlineip']);
		if ($e_login[0]!=$onlineip.' *' || ($timestamp-$e_login[1])>600 || $e_login[2]>1 ) {
			$men_uid = $men['uid'];
			$men_pwd = $men['password'];
			$check_pwd = $password;
			if ($men['yz'] > 2) {
				wap_msg('login_jihuo');
			}
			if (strlen($men_pwd) == 16) {
				$check_pwd=substr($password,8,16);/*支持 16 位 md5截取密码*/
			}
			if ($men_pwd==$check_pwd && (!$db_ifsafecv || $men['safecv']==$safecv)) {
				if (strlen($men_pwd)==16) {
					$userService->update($men_uid, array('password' => $password));
				}
				$L_groupid=(int)$men['groupid'];
				Cookie("ck_info",$db_ckpath."\t".$db_ckdomain);
			} else {
				global $L_T;
				$L_T=$e_login[2];
				$L_T ? $L_T--:$L_T=5;
				$F_login="$onlineip *|$timestamp|$L_T";
				$userService->update($men_uid, array(), array('onlineip' => $F_login));
				wap_msg('login_pwd_error');
			}
		} else {
			global $L_T;
			$L_T=600-($timestamp-$e_login[1]);
			wap_msg('login_forbid');
		}
	} else {
		global $errorname;
		$errorname=$username;
		wap_msg('user_not_exists');
	}
	Cookie("winduser",StrCode($men_uid."\t".PwdCode($password)."\t".$safecv));
	Cookie('lastvisit','',0);
	wap_msg('wap_login','index.php');
}
function wap_quest($question,$customquest,$answer) {
	$question = $question=='-1' ? $customquest : $question;
	return $question ? substr(md5(md5($question).md5($answer)),8,10) : '';
}
function wap_numofpage($page,$numofpage,$url,$max=null) {
	$total = $numofpage;
	if (!empty($max)) {
		$max = (int)$max;
		$numofpage > $max && $numofpage = $max;
	}
	if ($numofpage <= 1 || !is_numeric($page)) {
		return '';
	} else {
		list($url,$mao) = explode('#',$url);
		$mao && $mao = '#'.$mao;
		$pages = "<small><a href=\"{$url}page=1$mao\">&#60;&#60;</a>";
		for ($i=$page-2;$i<=$page-1;$i++) {
			if($i<1) continue;
			$pages .= " <a href=\"{$url}page=$i$mao\">$i</a>";
		}
		$pages .= " <b>$page</b>";
		if ($page < $numofpage) {
			$flag = 0;
			for ($i=$page+1;$i<=$numofpage;$i++) {
				$pages .= " <a href=\"{$url}page=$i$mao\">$i</a>";
				$flag++;
				if($flag==2) break;
			}
		}
		$pages .= "</small> <input type=\"text\" name=\"page\" size=\"3\" format=\"*N\" /> <do type=\"accept\" label=\"GO\"><go href=\"$url\" method=\"post\"><postfield name=\"page\" value=\"$(page)\" /></go></do><small><a href=\"{$url}page=$numofpage$mao\">&#62;&#62;</a> ($page/$total)</small>";
		return $pages;
	}
}
function wap_cv($msg) {
	$msg = str_replace(array("\0","%00","\r"),'',$msg);
	$msg = preg_replace(
		array('/[\\x00-\\x08\\x0B\\x0C\\x0E-\\x1F]/','/&(?!(#[0-9]+|[a-z]+);)/is'),
		array('','&amp;'),
		$msg
	);
	$msg = str_replace(array("%3C",'<'),'&lt;',$msg);
	$msg = str_replace(array("%3E",'>'),'&gt;',$msg);
	$msg = str_replace(array('"',"'","\t",'  '),array('&quot;','&#39;','    ','&nbsp;&nbsp;'),$msg);
	return $msg;
}
function wap_code($string) {
	$string	= preg_replace(array("/\[post\](.+?)\[\/post\]/is","/\[hide=(.+?)\](.+?)\[\/hide\]/is","/\[sell=(.+?)\](.+?)\[\/sell\]/is","/\[s:(.+?)\]/is"),"",$string);
	$string	= preg_replace("/\[code\](.+?)\[\/code\]/eis","wap_getcode('\\1')",$string);
	$string	= preg_replace("/\[quote\](.+?)\[\/quote\]/eis","wap_getquote('\\1')",$string);
	$string = str_replace(array('[u]','[/u]','[b]','[/b]','[i]','[/i]','[s]','[/s]','&nbsp;'),'',$string);
	$string = wap_clscode($string);
	return  $string;
}
function wap_getcode($string) {
	$string = str_replace(array('[',']'),array('&#91;','&#93;'),$string);
	return '[s][i]'.$string.'[/i][/s]';
}
function wap_getquote($string) {
	$string = wap_clscode($string);
	return '[s][i]'.$string.'[/i][/s]';
}
function wap_clscode($string) {
	for ($i=0;$i<5;$i++) {
		$string = str_replace("\n\n","\n",$string);
		$string = preg_replace("/\[(.+?)\](.+?)\[\/\\1\]/is", "\\2", $string);
		$string = preg_replace("/\[(.+?)=(.+?)\](.+?)\[\/\\1\]/is", "\\3", $string);
	}
	return nl2br($string);
}
?>