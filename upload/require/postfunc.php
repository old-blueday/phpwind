<?php
!function_exists('readover') && exit('Forbidden');

function cvipfrom($ip,$txt=null){
	if ($ip=='Unknown') return 'Unknown';
	$d_ip = explode('.',$ip);
	if ($txt!='0.txt') {
		$onlineip = $ip;
		$ip = substr($ip,strpos($ip,'.')+1);
		$txt = $d_ip[0].'.txt';
		$d_ip[0] = $d_ip[1]; $d_ip[1] = $d_ip[2]; $d_ip[2] = $d_ip[3]; $d_ip[3] = '';
	}
	if ($db = @fopen(R_P.'ipdata/'.$txt,'rb')) {
		flock($db,LOCK_SH);
		$f = $l_d = '';
		$d = "\n".fread($db,filesize(R_P.'ipdata/'.$txt));
		$wholeIP = $d_ip[0].'.'.$d_ip[1].'.'.$d_ip[2];
		$d_ip[3] && $wholeIP .= '.'.$d_ip[3];
		$wholeIP = str_replace('255','*',$wholeIP);
		if (($s = strpos($d,"\n$wholeIP\t"))!==false) {
			fseek($db,$s,SEEK_SET);
			$l_d = substr(fgets($db,100),0,-1); fclose($db);
			$ip_a = explode("\t",$l_d);
			$ip_a[3] && $ip_a[2] .= ' '.$ip_a[3];
			return $ip_a[2];
		}
		$ip = d_ip($d_ip);
		while (!$f && !$l_d && $wholeIP) {
			if (($s = strpos($d,"\n".$wholeIP.'.'))!==false) {
				list($l_d,$f) = s_ip($db,$s,$ip);
				if ($f) return $f;
				while ($l_d && preg_match("/^\n$wholeIP/i","\n".$l_d)!==false) {
					list($l_d,$f) = s_ip($db,$s,$ip,$l_d);
					if ($f) return $f;
				}
			}
			if (strpos($wholeIP,'.')!==false) {
				$wholeIP = substr($wholeIP,0,strrpos(substr($wholeIP,0,-1),'.'));
			} else {
				if ($txt=='0.txt') return 'Unknown';
				$wholeIP--;
			}
		}
		fclose($db);
	}
	if ($txt!='0.txt') {
		$f = cvipfrom($onlineip,'0.txt');
		if (!$f) return 'Unknown';
		return $f;
	}
	return 'Unknown';
}
function s_ip($db,$s,$ip,$l_d=null){
	if (empty($l_d)) {
		fseek($db,$s,SEEK_SET);
		$l_d = fgets($db,100);
	}
	$ip_a = explode("\t",$l_d);
	$ip_a[0] = d_ip(explode('.',$ip_a[0]));
	$ip_a[1] = d_ip(explode('.',$ip_a[1]));
	if ($ip<$ip_a[0]) {
		$f = $l_d = '';
	} elseif ($ip>=$ip_a[0] && $ip<=$ip_a[1]) {
		fclose($db);
		$ip_a[3] && $ip_a[2] .= ' '.$ip_a[3];
		$f = $ip_a[2]; $l_d = '';
	} else {
		$f = '';
		$l_d = fgets($db,100);
	}
	return array($l_d,$f);
}
function d_ip($d_ip){
	$d_ips = '';
	foreach ($d_ip as $value) {
		$d_ips .= '.'.sprintf("%03d",str_replace('*','255',$value));
	}
	return substr($d_ips,1);
}
/**
 * 发表、回复、修改帖子后更新帖子数、回复时间和生成静态页面访问地址的函数
 *
 * @param int $fid 所属版块
 * @param int $allowhtm 是否允许生成静态页
 * @param string $type new:发表新帖、reply:回复
 * @param string $sys_type：现无用
 */
function lastinfo($fid,$allowhtm=0,$type='',$sys_type='') {
	global $db,$R_url,$db_readdir,$foruminfo,$tid,$windid,$timestamp,$atc_title,$t_date,$replytitle;
	if ($type == 'new') {
		$rt['tid']      = $tid;
		$rt['postdate'] = $timestamp;
		$rt['lastpost'] = $timestamp;
		$author   = $windid;
		$subject  = substrs($atc_title,26);
		$topicadd = ",tpost=tpost+1,article=article+1,topic=topic+1 ";
		$fupadd   = "subtopic=subtopic+1,tpost=tpost+1,article=article+1";
	} elseif ($type == 'reply') {
		$rt['tid']      = $tid;
		$rt['postdate'] = $t_date;
		$rt['lastpost'] = $timestamp;
		$author         = $windid;
		$subject  = $atc_title ? substrs($atc_title,26) : 'Re:'.addslashes(substrs($replytitle,26));
		$topicadd = ",tpost=tpost+1,article=article+1 ";
		$fupadd   = "tpost=tpost+1,article=article+1 ";
	} else {
		$rt = $db->get_one("SELECT tid,author,postdate,subject,lastpost,lastposter FROM pw_threads WHERE fid=".pwEscape($fid)." AND topped=0 AND ifcheck=1 AND lastpost>0 ORDER BY lastpost DESC LIMIT 0,1");

		if ($rt['postdate'] == $rt['lastpost']) {
			$subject = addslashes(substrs($rt['subject'],26));
			$author  = $rt['author'];
		} else {
			$subject = 'Re:'.addslashes(substrs($rt['subject'],26));
			$author  = $rt['lastposter'];
		}
		$topicadd = $fupadd = "";
	}
	$GLOBALS['anonymous'] && $author = $GLOBALS['db_anonymousname'];

	$htmurl   = $db_readdir.'/'.$fid.'/'.date('ym',$rt['postdate']).'/'.$rt['tid'].'.html';
	$new_url  = file_exists(R_P.$htmurl) && $allowhtm && $sys_type!='1B' ? "$R_url/$htmurl" : "read.php?tid=$rt[tid]&page=e#a";
	$lastpost = $subject."\t".addslashes($author)."\t".$rt['lastpost']."\t".$new_url;
	$db->update("UPDATE pw_forumdata SET lastpost=".pwEscape($lastpost).$topicadd." WHERE fid=".pwEscape($fid));

	if ($foruminfo['type'] == 'sub' || $foruminfo['type'] == 'sub2') {
		if ($foruminfo['password'] != '' || $foruminfo['allowvisit'] != '' || $foruminfo['f_type'] == 'hidden') {
			$lastpost = '';
		} else {
			$lastpost = "lastpost=".pwEscape($lastpost);
		}
		if ($lastpost && $fupadd) {
			$lastpost .= ', ';
		}
		if ($lastpost || $fupadd) {
			$db->update("UPDATE pw_forumdata SET $lastpost $fupadd WHERE fid=".pwEscape($foruminfo['fup']));
			if ($foruminfo['type'] == 'sub2') {
				$rt1 = $db->get_one("SELECT fup FROM pw_forums WHERE fid=".pwEscape($foruminfo['fup']));
				$db->update("UPDATE pw_forumdata SET $lastpost $fupadd WHERE fid=".pwEscape($rt1['fup']));
			}
		}
	}
}

function bbspostguide($type = 'Post') {
	global $db,$creditset,$db_creditset,$db_upgrade,$db_hour,$groupid,$windid,$winduid,$winddb,$timestamp,$fid, $tid,$tdtime,$db_autochange,$db_tcheck,$atc_content,$_G,$credit;

	if ($db_autochange) {
		if (file_exists(D_P."data/bbscache/set_cache.php")) {
			list(,$set_control) = explode("|",readover(D_P."data/bbscache/set_cache.php"));
		} else {
			$set_control = 0;
		}
		if (($timestamp - $set_control) > $db_hour * 3600) {
			require_once(R_P.'require/postconcle.php');
		}
	}
	if ($groupid <> 'guest') {
		require_once(R_P.'require/credit.php');

		$creditset = $credit->creditset($creditset,$db_creditset);
		$winddb['todaypost'] ++;
		$winddb['monthpost'] ++;
		$winddb['lastpost'] = $timestamp;
		$winddb['postnum'] ++;
		$winddb['rvrc']   += $creditset[$type]['rvrc'];
		$winddb['money']  += $creditset[$type]['money'];
		$winddb['credit'] += $creditset[$type]['credit'];
		$winddb['currency'] += $creditset[$type]['currency'];

		$usercredit = array(
			'postnum'	=> $winddb['postnum'],
			'digests'	=> $winddb['digests'],
			'rvrc'		=> $winddb['rvrc'],
			'money'		=> $winddb['money'],
			'credit'	=> $winddb['credit'],
			'currency'	=> $winddb['currency'],
			'onlinetime'=> $winddb['onlinetime'],
		);
		$upgradeset = unserialize($db_upgrade);

		foreach ($upgradeset as $key => $val) {
			if (is_numeric($key) && $val) {
				foreach ($credit->get($winduid,'CUSTOM') as $key => $value) {
					$usercredit[$key] = $value;
				}
				break;
			}
		}
		require_once(R_P.'require/functions.php');
		$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
		$memberid = getmemberid(CalculateCredit($usercredit,$upgradeset));
		if ($winddb['memberid'] != $memberid) {
			$userService->update($winduid, array('memberid' => $memberid));
		}
		$credit->addLog('topic_'.$type,$creditset[$type],array(
			'uid'		=> $winduid,
			'username'	=> $windid,
			'ip'		=> $GLOBALS['onlineip'],
			'fname'		=> $GLOBALS['forum'][$fid]['name']
		));
		$credit->sets($winduid,$creditset[$type],false);
		$credit->runsql();

		$updateMemberData = array(
			'postnum'		=> $winddb['postnum'],
			'todaypost'		=> $winddb['todaypost'],
			'monthpost'		=> $winddb['monthpost'],
			'lastpost'		=> $winddb['lastpost'],
			'uploadtime'	=> $winddb['uploadtime'],
			'uploadnum'		=> $winddb['uploadnum']
		);
		if ($db_tcheck) $updateMemberData['postcheck'] = tcheck($atc_content);
		$userService->update($winduid, array(), $updateMemberData);
	} else {
		Cookie('userlastptime',$timestamp);
	}
}

function check_data($type="new") {
	global $db_titlemax,$db_postmin,$db_postmax,$foruminfo,$atc_usesign,$article,$db_sellset,$db_enhideset,$isGM,$winddb,$db_posturlnum;

	$atc_title   = trim($_POST['atc_title']);
	$atc_content = $_POST['atc_content'];
	if (empty($article) && !$atc_title || strlen($atc_title)>$db_titlemax) {
		Showmsg('postfunc_subject_limit');
	}
	$check_content = $atc_content;
	for ($i=10;$i<14;$i++) {
		$check_content = str_replace(Chr($i),'',$check_content);
	}
	if (strlen(trim($check_content))>=$db_postmax || strlen(trim($check_content))<$db_postmin) {
		Showmsg('postfunc_content_limit');
	}
	$atc_title = Char_cv($atc_title);
	$wordsfb = L::loadClass('FilterUtil', 'filter');
	$ifwordsfb = $wordsfb->ifwordsfb(stripslashes($atc_content));
	$ifconvert = 1;
	unset($_POST['atc_content']);

	if ($_POST['atc_convert'] == "1") {
		$_POST['atc_autourl'] && $atc_content = autourl($atc_content);
		if ($db_posturlnum > 0 && $winddb['postnum'] < $db_posturlnum && !$isGM && postUrlCheck($atc_content)){
			Showmsg('postfunc_urlnum_limit');
		}
		$atc_content = html_check($atc_content);
		/*
		* 权限控制是否能发布自动播放的多媒体
		*/
		foreach (array('wmv','rm','flash') as $key => $value) {
			if (strpos(",{$GLOBALS[_G][media]},",",$value,") === false) {
				$atc_content = preg_replace("/(\[$value=([0-9]{1,3}\,[0-9]{1,3}\,)?)1(\].+?\[\/$value\])/is", "\${1}0\\3",$atc_content);
			}
		}
		/*
		* [post]、[hide、[sell=位置不能换
		*/
		if (!$isGM && (!$foruminfo['allowhide'] || !$GLOBALS['_G']['allowhidden'])) {
			$atc_content = str_replace("[post]","[\tpost]",$atc_content);
		} elseif ($_POST['atc_hide'] == '1') {
			$atc_content = "[post]".str_replace(array('[post]','[/post]'),"",$atc_content)."[/post]";
			$ifconvert = 2;
		}
		if (!$isGM && (!$GLOBALS['forumset']['allowencode'] || !$GLOBALS['_G']['allowencode'])) {
			$atc_content = str_replace("[hide=","[\thide=",$atc_content);
		} elseif ($_POST['atc_requireenhide'] == '1') {
			$atc_enhidetype = in_array($_POST['atc_enhidetype'],$db_enhideset['type']) ? $_POST['atc_enhidetype'] : 'rvrc';
			$atc_content = preg_replace("/\[hide=(.+?)\]/is","",$atc_content);
			$atc_content = "[hide=".(int)$_POST['atc_rvrc'].",{$atc_enhidetype}]".str_replace("[/hide]","",$atc_content)."[/hide]";
			$ifconvert = 2;
		}
		if (!$isGM && (!$foruminfo['allowsell'] || !$GLOBALS['_G']['allowsell'])) {
			$atc_content = str_replace("[sell=","[\tsell=",$atc_content);
		} elseif ($_POST['atc_requiresell'] == '1') {
			$atc_credittype = in_array($_POST['atc_credittype'],$db_sellset['type']) ? $_POST['atc_credittype'] : 'money';
			$atc_content = str_replace("[/sell]","",preg_replace("/\[sell=(.+?)\]/is","",$atc_content));
			$atc_content = "[sell=".(int)$_POST['atc_money'].",{$atc_credittype}]{$atc_content}[/sell]";
			$ifconvert = 2;
		}
		/*if ($ifconvert == 1) {
			$atc_content != convert($atc_content,'') && $ifconvert = 2;
		}*/
		$ifconvert = 2;
	}
	if ($atc_usesign < 2) {
		$atc_content = Char_cv($atc_content);
	} else {
		$atc_content = preg_replace(
			array("/<script.*>.*<\/script>/is","/<(([^\"']|\"[^\"]*\"|'[^']*')*?)>/eis","/javascript/i"),
			array("","jscv('\\1')","java script"),
			str_replace('.','&#46;',$atc_content)
		);
	}
	return array($atc_title,$atc_content,$ifconvert,$ifwordsfb);
}

//自动url转变函数
function autourl($message){
	global $db_autoimg,$db_cvtimes,$code_htm,$codeid;
	$code_htm = array();$codeid = 0;
	if (strpos($message,"[code]") !== false && strpos($message,"[/code]") !== false) {
		$message = preg_replace("/\[code\](.+?)\[\/code\]/eis","code_check('\\1')",$message,$db_cvtimes);
	}

	if($db_autoimg==1){
		$message=preg_replace(array(
					"/(?<=[^\]a-z0-9-=\"'\\/])((https?|ftp):\/\/|www\.)([a-z0-9\/\-_+=.~!%@?#%&;:$\\│]+\.(gif|jpg|png))(?![\w\/\-+\.$&?#]{1})/i",
				), array(
					"[img]\\1\\3[/img]",
				), ' '.$message);
		$message=substr($message,1);
	}
	$message=preg_replace(array(
					"/(?<=[^\]a-z0-9-=\"'\\/])((https?|ftp|gopher|news|telnet|mms|rtsp):\/\/|www\.)([a-z0-9\/\-_+=.~!%@?#%&;:$\\│\|]+)/i",
					"/(?<=[^\]a-z0-9\/\-_.~?=:.])([_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,4}))/i"
				), array(
					"[url]\\1\\3[/url]",
					"[email]\\0[/email]"
				), ' '.$message);
	if (is_array($code_htm)) {
		foreach($code_htm as $key => $value){
			$message = str_replace("<\twind_phpcode_$key\t>",$value,$message);
		}
	}
	$message=substr($message,1);
	return $message;
}
function code_check($code){
	global $code_htm,$codeid;
	$codeid++;
	$code_htm[$codeid] = '[code]'.str_replace('\\"','"',$code).'[/code]';
	return "<\twind_phpcode_$codeid\t>";
}
function html_check($souce) {
	global $db_bbsurl,$db_picpath,$db_attachname;
	if (strpos($souce,$db_bbsurl) !== false) {
		$souce = str_replace($db_picpath,'p_w_picpath',$souce);
		$souce = str_replace($db_attachname,'p_w_upload',$souce);
	}
	return $souce;
}
function jscv($code) {
	$code = str_replace('\\"','"',$code);
	$code = preg_replace('/[\s]on[\w]+\s*=\s*(\\\"|\\\\\').+?\\1/is',"",$code);
	$code = preg_replace("/[\s]on[\w]+\s*=[^\s]*/is","",$code);
	return '<'.$code.'>';
}
function tcheck($content) {
	$content = trim($content);
	$content = strlen($content)>100 ? substr($content,0,100) : $content;
	return substr(md5($content),5,16);
}
function check_tag($tags) {
	$tags = array_unique(explode(" ",preg_replace('/\s+/is',' ',trim($tags))));
	count($tags)>5 && Showmsg("tags_num_limit");
	foreach ($tags as $key => $value) {
		(strlen($value)>15 || strlen($value)<3) && Showmsg('tag_length_limit');
	}
	$tags = implode(" ",$tags);
	return Char_cv($tags);
}
function insert_tag($tid,$tags) {
	global $db;
	$sql  = array();
	$tags = explode(" ",$tags);
	foreach ($tags as $key => $value) {
		if (!$value)	continue;
		$rt = $db->get_one("SELECT tagid FROM pw_tags WHERE tagname=".pwEscape($value));
		if (!$rt) {
			$db->update("INSERT INTO pw_tags SET ".pwSqlSingle(array('tagname'=>$value,'num'=>1)));
			$tagid = $db->insert_id();
		} else {
			$tagid = $rt['tagid'];
			$db->update("UPDATE pw_tags SET num=num+1 WHERE tagid=".pwEscape($tagid));
		}
		$sql[] = array($tagid,$tid);
	}
	$sql && $db->update("INSERT INTO pw_tagdata (tagid,tid) VALUES ".pwSqlMulti($sql));
}
function update_tag($tid,$tags) {
	global $db;
	$tags	= " $tags ";
	$tagids	= array();
	$query	= $db->query("SELECT * FROM pw_tagdata td LEFT JOIN pw_tags t USING(tagid) WHERE td.tid=".pwEscape($tid));
	while ($rt = $db->fetch_array($query)) {
		if (strpos($tags," $rt[tagname] ") === false) {
			$tagids[] = $rt['tagid'];
		} else {
			$tags = str_replace(" $rt[tagname] "," ",$tags);
		}
	}
	if ($tagids) {
		$tagids = pwImplode($tagids);
		$db->update("DELETE FROM pw_tagdata WHERE tid=".pwEscape($tid)."AND tagid IN($tagids)");
		$db->update("UPDATE pw_tags SET num=num-1 WHERE tagid IN($tagids)");
	}
	if ($tags = trim($tags)) {
		insert_tag($tid,$tags);
	}
}
function relate_tag($subject,$content){
	@include(D_P.'data/bbscache/tagdb.php');
	$i    = 0;
	$tags = '';
	foreach ($tagdb as $tag => $num) {
		if (strpos($subject,$tag) !== false || strpos($content,$tag) !== false) {
			$tags .= $tags ? ' '.$tag : $tag;
			if(++$i > 9) break;
		}
	}
	return $tags;
}
/*
function alarm($title,$content) {
	global $alarm,$admincheck;
	if (empty($alarm) || $admincheck) return 1;
	foreach ($alarm as $key => $value) {
		if (preg_match($key,$title) || preg_match($key,$content)) {
			return 0;
		}
	}
	return 1;
}
*/
function postupload($tmp_name,$filename) {
	if (strpos($filename,'..') !== false || strpos($filename,'.php.') !== false || eregi("\.php$",$filename)) {
		exit('illegal file type!');
	}
	createFolder(dirname($filename));
	if (function_exists("move_uploaded_file") && @move_uploaded_file($tmp_name,$filename)) {
		@chmod($filename,0777);
		return true;
	} elseif (@copy($tmp_name, $filename)) {
		@chmod($filename,0777);
		return true;
	} elseif (is_readable($tmp_name)) {
		writeover($filename,readover($tmp_name));
		if (file_exists($filename)) {
			@chmod($filename,0777);
			return true;
		}
	}
	return false;
}
function pwMovefile($dstfile,$srcfile) {
	createFolder(dirname($dstfile));
	if (rename($srcfile,$dstfile)) {
		@chmod($dstfile,0777);
		return true;
	} elseif (@copy($srcfile,$dstfile)) {
		@chmod($dstfile,0777);
		P_unlink($srcfile);
		return true;
	} elseif (is_readable($srcfile)) {
		writeover($dstfile,readover($srcfile));
		if (file_exists($dstfile)) {
			@chmod($dstfile,0777);
			P_unlink($srcfile);
			return true;
		}
	}
	return false;
}
function if_uploaded_file($tmp_name) {
	if (!$tmp_name || $tmp_name == 'none') {
		return false;
	} elseif (function_exists('is_uploaded_file') && !is_uploaded_file($tmp_name) && !is_uploaded_file(str_replace('\\\\', '\\', $tmp_name))) {
		return false;
	} else {
		return true;
	}
}
function UploadFile($uid,$uptype = 'all',$thumbs = null){//fix by noizy
	global $ifupload,$db_attachnum,$db_uploadfiletype,$action,$replacedb,$winddb,$_G,$tdtime,$timestamp,$fid,$db_attachdir,$attachdir,$db_watermark,$db_waterwidth,$db_waterheight,$db_ifgif,$db_waterimg,$db_waterpos,$db_watertext,$db_waterfont,$db_watercolor,$db_waterpct,$db_jpgquality,$db_ifathumb,$db_iffthumb,$db_athumbsize,$db_fthumbsize,$db_ifftp,$atc_attachment_name,$attach_ext,$savedir,$forumset;
	$uploaddb = array();
	foreach ($_FILES as $key => $value) {
		if (if_uploaded_file($value['tmp_name'])) {
			list($t,$i) = explode('_',$key);
			$i = (int)$i;
			$atc_attachment = $value['tmp_name'];
			$atc_attachment_name = Char_cv($value['name']);
			$atc_attachment_size = $value['size'];
			$attach_ext = strtolower(substr(strrchr($atc_attachment_name,'.'),1));
			if (empty($attach_ext) || !isset($db_uploadfiletype[$attach_ext])) {
				uploadmsg($uptype,'upload_type_error');
			}
			if ((int)$atc_attachment_size < 1) {
				uploadmsg($uptype,'upload_size_0');
			}
			if ($db_uploadfiletype[$attach_ext] && $atc_attachment_size > $db_uploadfiletype[$attach_ext]*1024) {
				uploadmsg($uptype,'upload_size_error');
			}
			if ($uptype == 'face') {
				$ifreplace = 0;
				$db_attachdir = 1;
				$db_ifathumb = $db_iffthumb;
				$db_athumbsize = $db_fthumbsize;
				$savedir = $thumbdir = '';
				$tmpname = $uptype."_$uid.$attach_ext";
				$savedir = 'upload/'.str_pad(substr($uid,-2),2,'0',STR_PAD_LEFT);
				$fileuplodeurl = $thumbdir = "$savedir/$uid.$attach_ext";
			
			} elseif ($uptype == 'cnlogo') {
				$ifreplace = $db_ifathumb = 0;
				$db_attachdir = 1;
				$savedir = 'cn_img';
				$tmpname = $uptype."_$uid.$attach_ext";
				$fileuplodeurl = "$savedir/colony_$uid.$attach_ext";
				$thumbdir = '';
			} elseif ($uptype == 'forumlogo') {
				$ifreplace = 0;
				$db_attachdir = 1;
				$db_ifathumb = 0;
				$tmpname = $uptype."_$uid.$attach_ext";
				$savedir = 'forumlogo';
				$fileuplodeurl = "$savedir/$fid.$attach_ext";
			} elseif ($uptype == 'photo') {
				if ($t == 'replace') {
					$ifreplace = 1;
					$fileuplodeurl = $replacedb[$i];
					$tmpurl = strrchr($fileuplodeurl,'/');
					$fileuplodename = $tmpurl ? substr($tmpurl,1) : $fileuplodeurl;
					$tmpname = $uptype."_$fileuplodename";
				} else {
					$ifreplace = 0;
					$uid .= substr(md5($timestamp.$i.randstr(8)),10,15);
					$tmpname = $uptype."_$uid.$attach_ext";
					$fileuplodeurl = $fileuplodename = "$uid.$attach_ext";
					$db_ifathumb = 1;
					if ($db_attachdir) {
						$savedir = 'photo/';
						if ($db_attachdir == 2) {
							$savedir .= 'Day_'.date('ymd');
						} elseif ($db_attachdir == 3) {
							$savedir .= "Cyid_$GLOBALS[cyid]";
						} else {
							$savedir .= 'Mon_'.date('ym');
						}
						$fileuplodeurl = $savedir.'/'.$fileuplodeurl;
					}
				}
				$thumbdir = str_replace($fileuplodename,'s_'.$fileuplodename,$fileuplodeurl);
			} else {
				if ($action == 'modify' && $t == 'replace' && isset($replacedb[$i])) {
					$ifreplace = 1;
					$fileuplodeurl = $replacedb[$i]['attachurl'];
					$tmpurl = strrchr($fileuplodeurl,'/');
					$tmpname = $uptype.'_'.($tmpurl ? substr($tmpurl,1) : $fileuplodeurl);
				} else {
					$ifreplace = 0;
					$attach_ext = preg_replace('/(php|asp|jsp|cgi|fcgi|exe|pl|phtml|dll|asa|com|scr|inf)/i', "scp_\\1", $attach_ext);
					$winddb['uploadtime'] = $timestamp;
					$winddb['uploadnum']++;
					$prename = substr(md5($timestamp.$i.randstr(8)),10,15);
					$tmpname = $uptype."_$prename.$attach_ext";
					$fileuplodeurl = $fid."_{$uid}_$prename.$attach_ext";
					if ($db_attachdir) {
						if ($db_attachdir == 2) {
							$savedir = "Type_$attach_ext";
						} elseif ($db_attachdir == 3) {
							$savedir = 'Mon_'.date('ym');
						} elseif ($db_attachdir == 4) {
							$savedir = 'Day_'.date('ymd');
						} else {
							$savedir = "Fid_$fid";
						}
						$fileuplodeurl = $savedir.'/'.$fileuplodeurl;
					}
				}
				$thumbdir = "thumb/$fileuplodeurl";
			}
			$havefile = $ifthumb = 0;
			if ($db_ifftp || file_exists("$attachdir/$fileuplodeurl")) {
				$havefile = 1;
				$source = D_P."data/tmp/$tmpname";
			} else {
				$source = "$attachdir/$fileuplodeurl";
			}
			if (!postupload($atc_attachment,$source)) {
				uploadmsg($uptype,'upload_error');
			}
			if ($uptype == 'face') {
				$max_source = $attachdir."/upload/tmp/max_$tmpname";
				if (!copy($source,$max_source)) {
					uploadmsg($uptype,'upload_error');
				}
				/*
				if (!postupload($atc_attachment,$max_source)) {
					uploadmsg($uptype,'upload_error');
				}
				*/
			}
			$ifupload = 3; $type = 'zip';
			$img_size[0] = $img_size[1] = 0;
			$size = ceil(filesize($source)/1024);

			if (in_array($attach_ext,array('gif','jpg','jpeg','png','bmp','swf'))) {
				require_once(R_P.'require/imgfunc.php');
				if (!$img_size = GetImgSize($source,$attach_ext)) {
					P_unlink($source);
					uploadmsg($uptype,'upload_content_error');
				}
				$ifupload = 1;
				$img_size[0] = $img_size['width'];
				$img_size[1] = $img_size['height'];
				unset($img_size['width'],$img_size['height']);
				$type = 'img';
				if ($attach_ext == 'swf') {
					$type = 'zip';
				} elseif ($db_ifathumb) {
					$thumburl = $havefile ? D_P."data/tmp/thumb_$tmpname" : "$attachdir/$thumbdir";
					list($db_thumbw,$db_thumbh) = explode("\t",$db_athumbsize);
					list($cenTer,$sameFile) = explode("\t",$thumbs);
					createFolder(dirname($thumburl));
					if ($thumbsize = MakeThumb($source,$thumburl,$db_thumbw,$db_thumbh,$cenTer,$sameFile)) {
						$img_size[0] = $thumbsize[0];
						$img_size[1] = $thumbsize[1];
						$source != $thumburl && $ifthumb = 1;
					}
				}
		
				if ($uptype == 'all' && $db_watermark && $forumset['watermark'] && $img_size[2]<'4' && $img_size[0]>$db_waterwidth && $img_size[1]>$db_waterheight && function_exists('imagecreatefromgif') && function_exists('imagealphablending') && ($attach_ext!='gif' || function_exists('imagegif') && ($db_ifgif==2 || $db_ifgif==1 && (PHP_VERSION > '4.4.2' && PHP_VERSION < '5' || PHP_VERSION > '5.1.4'))) && ($db_waterimg && function_exists('imagecopymerge') || !$db_waterimg && function_exists('imagettfbbox'))) {
					ImgWaterMark($source,$db_waterpos,$db_waterimg,$db_watertext,$db_waterfont,$db_watercolor, $db_waterpct,$db_jpgquality);
					if ($ifthumb == 1) {
						ImgWaterMark($thumburl,$db_waterpos,$db_waterimg,$db_watertext,$db_waterfont,$db_watercolor, $db_waterpct,$db_jpgquality);
					}
				}
			} elseif ($attach_ext == 'txt') {
				if (preg_match('/(onload|submit|post|form)/i',readover($source))) {
					P_unlink($source);
					uploadmsg($uptype,'upload_content_error');
				}
				$ifupload = 2;
				$type = 'txt';
			}
			require_once(R_P.'require/functions.php');
			if (pwFtpNew($GLOBALS['ftp'],$db_ifftp) && $GLOBALS['ftp']->upload($source,$fileuplodeurl)) {
				P_unlink($source);
				P_unlink("$attachdir/$fileuplodeurl");
				if ($ifthumb == 1) {
					$GLOBALS['ftp']->mkdir("thumb/$savedir");
					$GLOBALS['ftp']->upload($thumburl,$thumbdir) && P_unlink($thumburl);
				}
			
			} elseif ($havefile) {
				P_unlink("$attachdir/$fileuplodeurl");
				@rename($source,"$attachdir/$fileuplodeurl");
				if ($ifthumb == 1) {
					P_unlink("$attachdir/$thumbdir");
					@rename($thumburl,"$attachdir/$thumbdir");
				}
			}
			$uploaddb[] = array('id' => $i,'ifreplace' => $ifreplace,'name' => $atc_attachment_name,'size' => $size,'type' => $type,'attachurl' => $fileuplodeurl,'ifthumb' => $ifthumb,'img_w' => $img_size[0],'img_h' => $img_size[1],'tmpname' => $tmpname);
		}
	}
	return $uploaddb;
}
function uploadmsg($uptype,$msg) {
	if ($uptype == 'face' && defined('AJAX') && AJAX) {
		$msg = Char_cv(getLangInfo('msg',$msg));
		echo "<script language=\"JavaScript1.2\">parent.facepath('','','$msg','','');</script>";exit;
	} else {
		Showmsg($msg);
	}
}
function createFolder($path) {
	if (!is_dir($path)) {
		createFolder(dirname($path));
		@mkdir($path);
		@chmod($path,0777);
		@fclose(@fopen($path.'/index.html','w'));
		@chmod($path.'/index.html',0777);
	}
}
function postUrlCheck($atc_content) {
	if (strpos($atc_content,'[/URL]') !== false || strpos($atc_content,'[/url]') !== false) {
		return true;
	} else {
		return false;
	}
}
/*
function getTrueBanword($word) {
	$s_word = stripslashes($word);
	$banword = substr($s_word,1,strlen($s_word)-3);
	$banword = preg_replace('/\.\{0\,(\d+)\}/i','',$banword);
	return $banword;
}
*/
?>