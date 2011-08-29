<?php
!defined('W_P') && exit('Forbidden');

function wap_createFolder($path) {
	if (!is_dir($path)) {
		wap_createFolder(dirname($path));
		@mkdir($path);
		@chmod($path, 0777);
		@fclose(@fopen($path . '/index.html', 'w'));
		@chmod($path . '/index.html', 0777);
	}
}

function wap_cvpic($url,$type='') {
	global $db_bbsurl,$attachpath,$db_ftpweb,$code_num,$code_htm,$db_wapifathumb,$db_wapathumbsize,$attachdir,$db_wapifathumbgif,$db_ifftp;
	$code_num++;
	$lower_url = strtolower($url);
	strncmp($lower_url,'http',4)!=0 && $url = "$db_bbsurl/$url";
	if (strpos($lower_url,'login')!==false && (strpos($lower_url,'action=quit')!==false || strpos($lower_url,'action-quit')!==false)) {
		$url = preg_replace('/login/i','log in',$url);
	}
	$url = str_replace(array("&#39;","'"),'',$url);
	$turl = $url;
	list($db_wapathumbwidth, $db_wapathumbheight) = explode("\t", $db_wapathumbsize);

	if ($db_wapathumbwidth || $db_wapathumbheight) {
		$wopen = !$wopen ? "if(this.width>=$db_wapathumbwidth)" : '';
		$onload = 'onload="';
		$db_wapathumbwidth  && $onload .= "if(this.offsetWidth>'$db_wapathumbwidth')this.width='$db_wapathumbwidth';";
		$db_wapathumbheight && $onload .= "if(this.offsetHeight>'$db_wapathumbheight')this.height='$db_wapathumbheight';";
		$onload .= '"';
		$code = "<img src=\"$turl\" border=\"0\"  $onload ><div><a target='_blank' href=\"$url\">浏览大图</a></div>";
	} else{
		$wopen = !$wopen ? "if(this.width>screen.width-461)" : '';
		$code = "<img src=\"$turl\" border=\"0\"  ><div><a target='_blank' href=\"$url\">浏览大图</a></div>";
	}
	$code_htm[-1][$code_num]=$code;
	if ($type) {
		return $code;
	} else {
		return "<\twind_code_$code_num\t>";
	}
}

function getPages($cPage, $cCount, $jumpUrl) {
	global $wap_perpage;
	$cPage = (int) $cPage;
	$cPage < 1 && $cPage = 1;
	$cPage > 500 && $cPage = 500;
	$nextP = (int) $cCount < $wap_perpage ? 0 : $cPage + 1;
	$prevP = $cPage <= 1 ? 0 : $cPage - 1;
	$strpages = '';
	if ($nextP) {
		$strpages .= "<a href=\"{$jumpUrl}page=$nextP\">下一页</a>";
	}
	if ($nextP && $prevP) {
		$strpages .= "&nbsp;|&nbsp;";
	}
	if ($prevP) {
		$strpages .= "<a href=\"{$jumpUrl}page=$prevP\">上一页</a>";
	}
	
	return $strpages ? "<div class=\"block\">$strpages</div>" : "";
}

function PrintWAP($template) {
	#require_once PrintEot('wap_'.$template,'htm');
	return Pcv(W_P . 'template/' . $template . '.htm');
}

function wap_header($refreshurl = '') {
	global $db, $winduid, $wapImages, $headTitle, $winddb,$db_charset;
	$messageServer = L::loadClass('message', 'message');; /* @var $messageServer PW_Message */
	list($messageNumber,$noticeNumber,$requestNumber,$groupsmsNumber) = $messageServer->getUserStatistics($winduid);
	require_once PrintWAP('header');
}

function wap_footer() {
	global $wind_version, $db_obstart, $windid, $db_charset, $db_wapcharset, $chs, $timestamp, $db_online, $db,$db_wapregist,$rg_allowregister;;
	Update_ol();
	$userinbbs = $guestinbbs = 0;
	if (empty($db_online)) {
		include (D_P . 'data/bbscache/olcache.php');
	} else {
		$query = $db->query("SELECT uid!=0 as ifuser,COUNT(*) AS count FROM pw_online GROUP BY uid!='0'");
		while ($rt = $db->fetch_array($query)) {
			$rt['ifuser'] ? ($userinbbs = $rt['count']) : ($guestinbbs = $rt['count']);
		}
	}
	$usertotal = $guestinbbs + $userinbbs;
	$ft_time = get_date($timestamp);
	require_once PrintWAP('footer');
	$output = ob_get_contents();
	ob_end_clean();
	$db_obstart && function_exists('ob_gzhandler') ? ob_start('ob_gzhandler') : ob_start();
	if ($db_charset != 'utf8') {
		L::loadClass('Chinese', 'utility/lang', false);
		$chs = new Chinese();
		$output = $chs->Convert($output, $db_charset, ($db_wapcharset ? 'UTF8' : 'UNICODE'));
	}
	$output = str_replace(array('<!--<!---->', '<!---->-->', '<!---->', "\r\n\r\n"), '', $output);
	$wap_view = S::getGP('wap_view');
	if ($wap_view) $output = preg_replace('/<a[^>]*>([^<]+|.*?)?<\/a>/i',"\\1",$output);
	echo $output;
	ob_flush();
	exit();
}

function wap_output($output) {
	echo $output;
}

function getWapLang($T, $I, $L = false) {
	global $lang;
	require_once D_P . 'lang/lang_wap.php';
	if (isset($lang[$T][$I])) {
		eval('$I="' . addcslashes($lang[$T][$I], '"') . '";');
	}
	return $I;
}

function wap_msg($msg, $url = "") {
	$ysmsg = is_array($msg) ? array_pop($msg) : $msg;
	$msg = getWapLang('wap', $ysmsg);
	if (!empty($msg) && $msg == $ysmsg) {
		$msg = getLangInfo('msg', $ysmsg);
		$msg = strip_tags($msg);
	}
	wap_header($url);
	if ($msg) {
		echo '<br />', '<div class="warning">'.$msg.'</div>', '<div>'.($url ? " <a href='$url'>" . getWapLang('wap', 'wap_msg_view') . "</a>" : '').'</div>';
	} else {
		echo $ysmsg;
	}
	wap_footer();
}

function wap_login($username, $password, $safecv, $lgt = 0) {
	global $db, $timestamp, $onlineip, $db_ckpath, $db_ckdomain, $db_bbsurl, $db_ifsafecv;
	$men = $db->get_one("SELECT m.uid,m.password,m.safecv,m.groupid,m.yz,md.onlineip FROM pw_members m LEFT JOIN pw_memberdata md ON md.uid=m.uid WHERE m." . ($lgt ? 'uid' : 'username') . "=" . pwEscape($username));
	if ($men) {
		$e_login = explode("|", $men['onlineip']);
		if ($e_login[0] != $onlineip . ' *' || ($timestamp - $e_login[1]) > 600 || $e_login[2] > 1) {
			$men_uid = $men['uid'];
			$men_pwd = $men['password'];
			$check_pwd = $password;
			if ($men['yz'] > 2) {
				wap_msg('login_jihuo');
				return;
			}
			if (strlen($men_pwd) == 16) {
				$check_pwd = substr($password, 8, 16);
			}
			if ($men_pwd == $check_pwd && (!$db_ifsafecv || $men['safecv'] == $safecv)) {
				if (strlen($men_pwd) == 16) {
					$db->update("UPDATE pw_members SET password=" . pwEscape($password) . " WHERE uid=" . pwEscape($men_uid));
				}
				$L_groupid = (int) $men['groupid'];
				Cookie("ck_info", $db_ckpath . "\t" . $db_ckdomain);
			} else {
				global $L_T;
				$L_T = $e_login[2];
				$L_T ? $L_T-- : $L_T = 5;
				$F_login = "$onlineip *|$timestamp|$L_T";
				$db->update("UPDATE pw_memberdata SET onlineip=" . pwEscape($F_login) . " WHERE uid=" . pwEscape($men_uid));
				wap_msg('login_pwd_error');
				return;
			}
		} else {
			global $L_T;
			$L_T = 600 - ($timestamp - $e_login[1]);
			wap_msg('login_forbid');
			return;
		}
	} else {
		global $errorname;
		$errorname = $username;
		wap_msg('user_not_exists');
		return;
	}
	Cookie("winduser", StrCode($men_uid . "\t" . PwdCode($password) . "\t" . $safecv));
	Cookie('lastvisit', '', 0);
	//自动获取勋章_start
	require_once(R_P.'require/functions.php');
	doMedalBehavior($men_uid,'continue_login');
	//自动获取勋章_end
	wap_msg('wap_login', 'index.php');
}

function wap_quest($question, $customquest, $answer) {
	$question = $question == '-1' ? $customquest : $question;
	return $question ? substr(md5(md5($question) . md5($answer)), 8, 10) : '';
}

function wap_numofpage($page, $numofpage, $url, $max = null) {
	$total = $numofpage;
	if (!empty($max)) {
		$max = (int) $max;
		$numofpage > $max && $numofpage = $max;
	}
	if ($numofpage <= 1 || !is_numeric($page)) {
		return '';
	} else {
		list($url, $mao) = explode('#', $url);
		$mao && $mao = '#' . $mao;
		$pages = "<small><a href=\"{$url}page=1$mao\">&#60;&#60;</a>";
		for ($i = $page - 2; $i <= $page - 1; $i++) {
			if ($i < 1)
				continue;
			$pages .= " <a href=\"{$url}page=$i$mao\">$i</a>";
		}
		$pages .= " <b>$page</b>";
		if ($page < $numofpage) {
			$flag = 0;
			for ($i = $page + 1; $i <= $numofpage; $i++) {
				$pages .= " <a href=\"{$url}page=$i$mao\">$i</a>";
				$flag++;
				if ($flag == 2)
					break;
			}
		}
		$pages .= "</small> <input type=\"text\" name=\"page\" size=\"3\" format=\"*N\" /> <do type=\"accept\" label=\"GO\"><go href=\"$url\" method=\"post\"><postfield name=\"page\" value=\"$(page)\" /></go></do><small><a href=\"{$url}page=$numofpage$mao\">&#62;&#62;</a> ($page/$total)</small>";
		return $pages;
	}
}

function wap_cv($msg,$ifpost = true) {
	if($ifpost){
		$wordsfb = L::loadClass('FilterUtil', 'filter');
		$msg = $wordsfb->convert($msg);
	}
	$msg = str_replace(array("%3C", '&lt;'), '<', $msg);
	$msg = str_replace(array("%3E", '&gt;'), '>', $msg);
	$msg = str_replace(array("\0", "%00"), '', $msg);
	$msg = preg_replace(array('/[\\x00-\\x08\\x0B\\x0C\\x0E-\\x1F]/', '/&(?!(#[0-9]+|[a-z]+);)/is'), array('', '&amp;'), $msg);
	$msg = str_replace(array('"', "'", "\t", '  '), array('&quot;', '&#39;', '    ', '&nbsp;&nbsp;'), $msg);
	$msg = str_replace(array('&#46;&#46;', '&#41;', '&#60;', '&#61;'), array('..', ')', '<', '='), $msg);
	$msg = strip_tags($msg,"<p><br/>");
	return $msg;
}

function wap_code($string) {
	$string = preg_replace("/\[post\](.+?)\[\/post\]/eis", "wap_getHide('\\1')", $string);
	$string = preg_replace("/\[hide=(.+?)\](.+?)\[\/hide\]/eis", "wap_getHide('\\1')", $string);
	$string = preg_replace("/\[sell=(.+?)\](.+?)\[\/sell\]/eis", "wap_getSell('\\1')", $string);
	
	$string = preg_replace(array("/\[attachment=(.+?)\]/is", "/\[s:(.+?)\]/is"), "", $string);
	$string = preg_replace("/\[code\](.+?)\[\/code\]/eis", "wap_getcode('\\1')", $string);
	$string = preg_replace("/\[quote\](.+?)\[\/quote\]/eis", "wap_getquote('\\1')", $string);
	$string = str_replace(array('[u]', '[/u]', '[b]', '[/b]', '[i]', '[/i]', '[s]', '[/s]', '&nbsp;'), '', $string);
	$string = wap_img($string);
	$string = wap_clscode($string);
	$string = str_replace(array('[/backcolor]', '[/size]'), '', $string);
	$string = wap_img3($string);
	$string = str_replace("\n\n", "\n", $string);
	return nl2br($string);
}


function wap_cv_after($msg){
	$msg = str_replace(array("\n","\r","\n\r"),"<br/>", $msg);
	$msg = str_replace(array("\t"),"<br/>", $msg);
	return $msg;
}
function wap_getPost($string) {
	if (empty($string))
		return "";
	return "[本部分内容设定了隐藏]";
}

function wap_getHide($string) {
	if (empty($string))
		return "";
	return "[本部分内容设定了加密]";
}

function wap_getSell($string) {
	if (empty($string))
		return "";
	return "[本部分内容设定了出售]";
}

function wap_img($string) {
	// $string	= preg_replace("/\[img\](.+?)\[\/img\]/eis","\<img src='\\1' alt='pic' />",$string);
	$string = str_replace("[img]", "tpbq1", $string);
	$string = str_replace("[/img]", "tpbq2", $string);
	return $string;
}

function wap_img3($string) {
	// $string	= preg_replace("/tpbq1(.+?)tpbq2/eis","\<img src='\\1' alt='pic' />",$string);
	$string = preg_replace("/tpbq1(.+?)tpbq2/eis", "wap_getimg('\\1')", $string);
	return $string;
}

function wap_img2($string) {
	$string = str_replace('<img src="', "", $string);
	$string = str_replace('" alt="." width="200"/>', "", $string);
	return $string;
}

function wap_getcode($string) {
	$string = str_replace(array('[', ']'), array('&#91;', '&#93;'), $string);
	return '[s][i]' . $string . '[/i][/s]';
}

function wap_getimg($string) {
	$string = str_replace(array('[', ']'), array('&#91;', '&#93;'), $string);
	return '<img src="' . $string . '" alt="." width="200"/>';
}

function wap_getquote($string) {
	$string = wap_clscode($string);
	return '[s][i]' . $string . '[/i][/s]';
}

function wap_clscode($string) {
	return stripWindCode($string);
}

/**
 * *********register*********
 */
function wap_PostCheck($verify = 1, $gdcheck = 0, $qcheck = 0, $refer = 1) {
	global $pwServer;
	$verify && wap_checkVerify();
	if ($refer && $pwServer['REQUEST_METHOD'] == 'POST') {
		$referer_a = @parse_url($pwServer['HTTP_REFERER']);
		if ($referer_a['host']) {
			list($http_host) = explode(':', $pwServer['HTTP_HOST']);
			if ($referer_a['host'] != $http_host) {
				wap_msg('undefined_action');
			}
		}
	}
	$gdcheck && wap_GdConfirm($_POST['gdcode']);
	$qcheck && wap_Qcheck($_POST['qanswer'], $_POST['qkey']);
}

function wap_checkVerify($hash = 'verifyhash') {
	GetGP('verify') != $GLOBALS[$hash] && wap_msg('illegal_request');
}

function wap_GdConfirm($code) {
	Cookie('cknum', '', 0);
	if (!$code || !SafeCheck(explode("\t", StrCode(GetCookie('cknum'), 'DECODE')), strtoupper($code), 'cknum', 1800)) {
		wap_msg('check_error');
	}
}

function wap_Qcheck($answer, $qkey) {
	global $db_question, $db_answer, $basename;
	if ($db_question && (!isset($db_answer[$qkey]) || $answer != $db_answer[$qkey])) {
		wap_msg('qcheck_error', $basename);
	}
}

function forumCheck($fid, $type) {
	global $windid, $groupid, $tid, $fid, $skin, $winddb, $manager, $db, $db_threadrelated, $wind_action, $wind_pwd;
	$forum = $db->get_one('SELECT * FROM pw_forums f LEFT JOIN pw_forumsextra fe USING(fid) WHERE f.fid=' . pwEscape($fid));
	!$forum && wap_msg('data_error');
	$forumset = unserialize($forum['forumset']);
	
	if ($forum['f_type'] == 'former' && $groupid == 'guest' && $_COOKIE) {
		wap_msg('forum_former');
	}
	if (!empty($forum['style']) && file_exists(D_P . "data/style/$forum[style].php")) {
		$skin = $forum['style'];
	}
	$pwdcheck = GetCookie('pwdcheck');
	if ($forum['password'] != '' && ($groupid == 'guest' || $pwdcheck[$fid] != $forum['password'] && !CkInArray($windid, $manager))) {
		require_once (W_P . 'forumpwd.php');
	}
	if ($forum['allowvisit'] && !allowcheck($forum['allowvisit'], $groupid, $winddb['groups'], $fid, $winddb['visit'])) {
		wap_msg('forum_jiami');
	}
	if (!$forum['cms'] && $forum['f_type'] == 'hidden' && !$forum['allowvisit']) {
		wap_msg('forum_hidden');
	}
	
	if (wap_creditCheck()) {
		wap_msg('forum_right');
	}
}

function wap_check($fid, $action) {
	global $db, $groupid, $_G, $t, $db_titlemax, $db_postmin, $db_postmax, $db_wordsfb, $subject, $content;
	
	$subject = trim($subject);
	$content = trim($content);
	if ($action == 'new' && (!$subject || strlen($subject) > $db_titlemax)) {
		wap_msg('subject_limit');
	}
	if (strlen($content) >= $db_postmax || strlen($content) < $db_postmin) {
		wap_msg('content_limit');
	}
	
	$fm = $db->get_one("SELECT f.forumadmin,f.fupadmin,f.password,f.allowvisit,f.f_type,f.f_check,f.allowpost,f.allowrp,fe.forumset FROM pw_forums f LEFT JOIN pw_forumsextra fe USING(fid) WHERE f.fid=" . pwEscape($fid));
	$forumset = unserialize($fm['forumset']);
	if (!$fm || $fm['password'] != '' || $fm['f_type'] == 'hidden' || $fm['allowvisit'] && @strpos($fm['allowvisit'], ",$groupid,") === false) {
		wap_msg('post_right');
	}
	if ($action == 'new') {
		$isGM = CkInArray($GLOBALS['windid'], $GLOBALS['manager']);
		$isBM = admincheck($fm['forumadmin'], $fm['fupadmin'], $GLOBALS['windid']);
		if ($fm['f_check'] == '1' || $fm['f_check'] == '3') {
			//wap_msg('post_right');
		}
		if ($fm['allowpost'] && strpos($fm['allowpost'], ",$groupid,") === false) {
			wap_msg('post_right');
		}
		if (!$fm['allowpost'] && $_G['allowpost'] == 0) {
			wap_msg('post_group');
		}
		if ($forumset['allowtime'] && !$isGM && !allowcheck($forumset['allowtime'], "$t[hours]", '') && !pwRights($isBM, 'allowtime')) {
			wap_msg('post_right');
		}
	} elseif ($action == 'reply') {
		if ($fm['f_check'] == '2' || $fm['f_check'] == '3') {
			wap_msg('reply_right');
		}
		if ($fm['allowrp'] && strpos($fm['allowrp'], ",$groupid,") === false) {
			wap_msg('reply_right');
		}
		if (!$fm['allowrp'] && $_G['allowrp'] == 0) {
			wap_msg('reply_group');
		}
	}
}

function wap_creditCheck() {
	global $db, $winddb, $userrvrc, $fm, $groupid;
	$fm['rvrcneed'] /= 10;
	$fm['moneyneed'] = (int) $fm['moneyneed'];
	$fm['creditneed'] = (int) $fm['creditneed'];
	$fm['postnumneed'] = (int) $fm['postnumneed'];
	$check = 1;
	if ($fm['rvrcneed'] && $userrvrc < $fm['rvrcneed']) {
		$check = 0;
	} elseif ($fm['moneyneed'] && $winddb['money'] < $fm['moneyneed']) {
		$check = 0;
	} elseif ($fm['creditneed'] && $winddb['credit'] < $fm['creditneed']) {
		$check = 0;
	} elseif ($fm['postnumneed'] && $winddb['postnum'] < $fm['postnumneed']) {
		$check = 0;
	}
	if (!$check) {
		return true;
	}
	return false;
}

function replySubject($s) {
	$max = 90;
	$s = viewContent($s);
	
	$clen = strlen($s);
	if ($max < $clen) {
		$s = substrs($s, $max);
		$s = wap_img2($s);
	}
	return $s;
}

/**
 * 会员唯一有效登录验证加密字串
 * @param string $code 会员的有效登录Cookie
 * @param int $expire 链接有效时间（天）
 * @param int $times 该字串可有效登录的次数
 * @return string 有效登录验证字串
 */
function enWindToken($code, $expire = 30, $times = 60) {
	if (!trim($code))
		return '';
	$expire = time() + $expire * 86400;
	$md5word = substr(md5($expire . $code . $times), 8, 18);
	$token = StrCode($expire . "\t" . $times . "\t" . $code . "\t" . $md5word, 'ENCODE');
	return str_replace('=', '', $token);
}

/**
 * 会员唯一有效登录验证字串解密
 * @param string $token 有效登录验证字串
 * @param int $curtimes 当前该字串已经登录访问次数
 * @return mixed 验证通过则返回有效字串,否则返回FALSE
 */
function deWindToken($token, $curtimes = 60) {
	$token = StrCode($token, 'DECODE');
	list($expire, $times, $code, $md5word) = explode("\t", $token);
	if (substr(md5($expire . $code . $times), 8, 18) === $md5word && $times >= $curtimes && $expire >= time() && $code) {
		return addslashes($code);
	}
	return '';
}

/**
 * 获得板块id
 * */
function getFidsForWap() {
	global $groupid;
	static $fids = null;
	$groupid = (int) $groupid;
	if (!isset($fids)) {
		global $db;
		$query = $db->query("SELECT fid FROM pw_forums WHERE fup <> '0' AND cms<> '1' AND forumsell = '' AND (f_type <> 'hidden' OR allowvisit LIKE '%," . $groupid . ",%')");
		while ($rt = $db->fetch_array($query)) {
			$rt['fid'] = (int) $rt['fid'];
			$fids .= ",'$rt[fid]'";
		}
		$fids && $fids = substr($fids, 1);
	}
	return $fids;
}

function getReturnUrl(){
	global $scrMap;
	$scr = unserialize(stripslashes(GetCookie('wap_scr')));;
	$page = $scr['page'];
	$extra = $scr['extra'];
	$url =  $scrMap[$page] ? $scrMap[$page] : 'index.php';
	if($extra && S::isArray($extra)){
		if($page == 'read'){
			$url .= "?tid={$extra['tid']}";
		}elseif($page == "forum"){
			$url .= "?fid={$extra['fid']}";
		}elseif($page == "reply" || $page == "reply_all"){
			$url .= "?tid={$extra['tid']}";
		}
	}
	return $url;
}



function wap_substr($string,$start,$length,$charset = 'UTF-8',$dot= false) {
	$start = $start ? $start : 0;
	$length = $length ? $length : 0;
	$string = $string ? $string : '';
	if (in_array($charset,array('utf-8','UTF-8','utf8'))) {
		return utf8_substr($string,$start,$length,$dot);
	} else {
		return gbk_substr($string,$start,$length,$dot);
	}
}

function wap_strlen($string,$charset = 'UTF-8'){
	$len = strlen ( $string );
	$i = $count = 0;
	while ( $i<strlen ($string)){
		ord($string[$i])> 129 ? in_array($charset,array('utf-8','UTF-8','utf8')) ? $i+=3 :$i += 2 : $i++;
		$count++;
	}
	return $count;
}


function utf8_substr($string,$start,$length,$dot= false){
	$start = $start ? (int)$start*2 : 0;
	$length = $length ? (int)$length*2 : 0;
	$string = $string ? $string : '';
	$strlen = strlen($string);
	$substr = '';
	for($i = 0; $i<$strlen; $i++) {
		if ($i>=$start && $i<($start+$length)) $substr .= (ord(substr( $string,$i,1)) > 129) ? substr($string,$i,3):substr($string,$i,1);
		if (ord(substr($string,$i,1))>129) $i+=2;
	}
	(strlen($substr)<$strlen) && $dot && $substr .= "...";
	return $substr;
	
}

function utf8_strlen($str) {
	$i = $count = 0;
	$len = strlen ( $str );
	while ( $i < $len ) {
		$chr = ord ( $str [$i] );
		$count ++;$i ++;
		if ($i >= $len)break;
		if ($chr & 0x80) {
			$chr <<= 1;
			while ( $chr & 0x80 ) {
				$i ++;
				$chr <<= 1;
			}
		}
	}
	return $count;
}

function gbk_substr($string,$start,$length,$dot= false){
	$start = $start ? (int)$start*2 : 0;
	$length = $length ? (int)$length*2 : 0;
	$string = $string ? $string : '';
	$strlen = strlen($string);
	$substr = '';
	for($i = 0; $i<$strlen; $i++) {
		if ($i>=$start && $i<($start+$length)) $substr .= (ord(substr( $string,$i,1)) > 129) ? substr($string,$i,2):substr($string,$i,1);
		if (ord(substr($string,$i,1))>129) $i++;
	}
	(strlen($substr)<$strlen) && $dot && $substr .= "...";
	return $substr;
	
}
function gbk_strlen($string, $charset = 'UTF-8') {
	$len = strlen ( $string );
	$i = $count = 0;
	while ( $i<strlen ($string)){
		ord($string[$i])> 129 ? $i += 2 : $i++;
		$count++;
	}
	return $count;

}

function checkRglower($username) {
	global $db_charset;
	$namelen = strlen ( $username );
	for($i = 0; $i < $namelen; $i ++) {
		if (ord ( $username [$i] ) > 127) {
			$i += 'utf-8' != $db_charset ? 1 : 2;
		} else {
			if (ord ( $username [$i] ) >= 65 && ord ( $username [$i] ) <= 90) {
				return false;
			}
		}
	}
	return true;
}

?>