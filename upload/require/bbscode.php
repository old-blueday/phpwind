<?php
!function_exists('readover') && exit('Forbidden');

function convert($message,$allow,$type="post"){
	global $isGM,$pwPostHide,$pwSellHide,$pwEncodeHide,$code_num,$code_htm,$phpcode_htm,$foruminfo,$db_picpath,$imgpath,$stylepath,$db_attachname, $attachpath,$db_cvtimes,$forumset,$tpc_tag,$db_windcode,$sell_num;

	$code_num = $sell_num = 0;
	$code_htm = array();
	if (strpos($message,"[code]") !== false && strpos($message,"[/code]") !== false) {
		$message = preg_replace("/\[code\](.+?)\[\/code\]/eis","phpcode('\\1')",$message,$db_cvtimes);
	}
	if (strpos($message,"[payto]") !== false && strpos($message,"[/payto]") !== false) {
		require_once(R_P.'require/paytofunc.php');
		$message = preg_replace("/\[payto\](.+?)\[\/payto\]/eis","payto('\\1')",$message);
	}
	if (($pos = strpos($message,"[paragraph]")) !== false && $pos == min($pos, 10)) {
		$message = str_replace('[paragraph]', '', $message);
		$tmplist = explode('<br />', $message);
		$message = '<p style="text-indent: 2em;">' . implode('</p><p style="text-indent: 2em;">', $tmplist) . '</p>';
	}
	$message = preg_replace('/\[list=([aA1]?)\](.+?)\[\/list\]/is', "<ol type=\"\\1\" style=\"margin:0 0 0 25px\">\\2</ol>", $message);

	$searcharray = array('[u]','[/u]','[b]','[/b]','[i]','[/i]','[list]','[li]','[/li]','[/list]','[sub]', '[/sub]','[sup]','[/sup]','[strike]','[/strike]','[blockquote]','[/blockquote]','[hr]','[/backcolor]', '[/color]','[/font]','[/size]','[/align]'
	);
	$replacearray = array('<u>','</u>','<b>','</b>','<i>','</i>','<ul style="margin:0 0 0 25px">','<li>', '</li>','</ul>','<sub>','</sub>','<sup>','</sup>','<strike>','</strike>','<blockquote>','</blockquote>', '<hr />','</span>','</span>','</font>','</font>','</div>'
	);
	$message = str_replace($searcharray,$replacearray,$message);

	$message = str_replace("p_w_upload",$db_attachname,$message);//此处位置不可调换
	$message = str_replace("p_w_picpath",$db_picpath,$message);//此处位置不可调换

	$searcharray = array(
		"/\[font=([^\[\(&\\;]+?)\]/is",
		"/\[color=([#0-9a-z]{1,15})\]/is",
		"/\[backcolor=([#0-9a-z]{1,10})\]/is",
		"/\[email=([^\[]*)\]([^\[]*)\[\/email\]/is",
	    "/\[email\]([^\[]*)\[\/email\]/is",
		"/\[size=(\d+)\]/eis",
		"/\[align=(left|center|right|justify)\]/is",
		"/\[glow=(\d+)\,([0-9a-zA-Z]+?)\,(\d+)\](.+?)\[\/glow\]/is"
	);
	$replacearray = array(
		"<font face=\"\\1 \">",
		"<span style=\"color:\\1 \">",
		"<span style=\"background-color:\\1 \">",
		"<a href=\"mailto:\\1 \">\\2</a>",
		"<a href=\"mailto:\\1 \">\\1</a>",
		"size('\\1','$allow[size]')",
		"<div align=\"\\1\">",
		"<div style=\"width:\\1px;filter:glow(color=\\2,strength=\\3);\">\\4</div>"
	);
	$message = preg_replace($searcharray,$replacearray,$message);

	if ($db_windcode) {
		$message = preg_replace($db_windcode['searcharray'], $db_windcode['replacearray'], $message);
	}
	if ($allow['pic']) {
		$message = preg_replace("/\[img\]([^\<\r\n\"']+?)\[\/img\]/eis", "cvpic('\\1','','$allow[picwidth]','$allow[picheight]')",$message,$db_cvtimes);
    } else{
		$message = preg_replace("/\[img\]([^\<\r\n\"']+?)\[\/img\]/eis","nopic('\\1')",$message,$db_cvtimes);
	}
	if (strpos($message,'[/MUSIC]') !== false || strpos($message,'[/music]') !== false) {
		$message = preg_replace("/\[music=(\d+)\](.+?)\[\/music\]/eis","SetMusic('\\1')",$message,$db_cvtimes);
	}

	if (strpos($message,'[/URL]') !== false || strpos($message,'[/url]') !== false) {
		$searcharray = array(
			"/\[url=(https?|ftp|gopher|news|telnet|mms|rtsp|thunder)([^\[\s]+?)(\,(1)\/?)?\](.+?)\[\/url\]/eis",
			"/\[url\]www\.([^\[]+?)\[\/url\]/eis",
			"/\[url\](https?|ftp|gopher|news|telnet|mms|rtsp|thunder)([^\[]+?)\[\/url\]/eis",
			"/\[url=([^\[\s]+?)(\,(1)\/?)?\](.+?)\[\/url\]/eis"
		);
		$replacearray = array(
			"cvurl('\\1','\\2','\\5','\\4','$allow[checkurl]')",
			"cvurl('\\1','','','','$allow[checkurl]')",
			"cvurl('\\1','\\2','','','$allow[checkurl]')",
			"cvurl('','\\1','\\4','\\3','$allow[checkurl]')"
		);
		$message = preg_replace($searcharray,$replacearray,$message);
	}

	$searcharray = array(
		"/\[fly\]([^\[]*)\[\/fly\]/is",
		"/\[move\]([^\[]*)\[\/move\]/is"
	);
	$replacearray = array(
		"<marquee width=90% behavior=alternate scrollamount=3>\\1</marquee>",
		"<marquee scrollamount=3>\\1</marquee>"
	);
	$message = preg_replace($searcharray,$replacearray,$message);

	if ($type == 'post') {
		$tpc_tag && $message = relatetag($message, $tpc_tag);
		if ($foruminfo['allowhide'] && strpos($message,"[post]")!==false && strpos($message,"[/post]")!==false) {
			$message=preg_replace("/\[post\](.+?)\[\/post\]/eis","post('\\1')",$message);
		}
		if ($forumset['allowencode'] && strpos($message,"[hide=")!==false && strpos($message,"[/hide]")!==false) {
			$message=preg_replace("/\[hide=(.+?)\](.+?)\[\/hide\]/eis","hidden('\\1','\\2')",$message);
		}
		if ($foruminfo['allowsell'] && strpos($message,"[sell")!==false && strpos($message,"[/sell]")!==false) {
			$message = preg_replace("/\[sell=(.+?)\](.+?)\[\/sell\]/eis","sell('\\1','\\2')",$message);
		}
	}
	if (strpos($message,"[quote]") !== false && strpos($message,"[/quote]") !== false) {
		$message = preg_replace("/\[quote\](.*?)\[\/quote\]/eis","qoute('\\1')",$message);
	}
	if (is_array($code_htm)) {
		krsort($code_htm);
		foreach ($code_htm as $codehtm) {
			foreach ($codehtm as $key => $value) {
				$message = str_replace("<\twind_code_$key\t>",$value,$message);
			}
		}
	}
	if ($allow['flash']) {
		$message = preg_replace("/\[flash=(\d+?)\,(\d+?)(\,(0|1))?\]([^\[\<\r\n\"']+?)\[\/flash\]/eis", "wplayer('\\5','\\1','\\2','\\4','flash')",$message,$db_cvtimes);
	} else {
		$message = preg_replace("/\[flash=(\d+?)\,(\d+?)(\,(0|1))?\]([^\[\<\r\n\"']+?)\[\/flash\]/is","<img src='$imgpath/wind/file/music.gif' align='absbottom'> <a target='_blank' href='\\5 '>flash: \\5</a>",$message,$db_cvtimes);
	}
	if ($type == 'post') {
		$t = 0;
		while (strpos($message,'[table') !== false && strpos($message,'[/table]') !== false) {
			$message = preg_replace('/\[table(?:=(\d{1,3}(?:%|px)?)(?:,(#\w{6}))?(?:,(#\w{6}))?(?:,(\d+))?)?\](.*?)\[\/table\]/eis', "tablefun('\\5','\\1','\\2','\\3','\\4')",$message);
			if (++$t>4) break;
		}
		if ($allow['mpeg']) {
			$message = preg_replace(
				array(
					"/\[(wmv|mp3)=(0|1)\]([^\<\r\n\"']+?)\[\/\\1\]/eis",
					"/\[wmv(=([0-9]{1,3})\,([0-9]{1,3})\,(0|1))?\]([^\<\r\n\"']+?)\[\/wmv\]/eis",
					"/\[rm(=([0-9]{1,3})\,([0-9]{1,3})\,(0|1))?\]([^\<\r\n\"']+?)\[\/rm\]/eis"
				),
				array(
					"wplayer('\\3','314','53','\\2','wmv')",
					"wplayer('\\5','\\2','\\3','\\4','wmv')",
					"wplayer('\\5','\\2','\\3','\\4','rm')"
				),$message,$db_cvtimes
			);
		} else{
			$message = preg_replace(
				array(
					"/\[mp3=[01]{1}\]([^\<\r\n\"']+?)\[\/mp3\]/is",
					"/\[wmv=[01]{1}\]([^\<\r\n\"']+?)\[\/wmv\]/is",
					"/\[wmv(?:=[0-9]{1,3}\,[0-9]{1,3}\,[01]{1})?\]([^\<\r\n\"']+?)\[\/wmv\]/is",
					"/\[rm(?:=[0-9]{1,3}\,[0-9]{1,3}\,[01]{1})\]([^\<\r\n\"']+?)\[\/rm\]/is"
				),
				"<img src=\"$imgpath/wind/file/music.gif\" align=\"absbottom\"> <a target=\"_blank\" href=\"\\1 \">\\1</a>",$message,$db_cvtimes
			);
		}
		if ($allow['iframe']) {
			$message = preg_replace("/\[iframe\]([^\[\<\r\n\"']+?)\[\/iframe\]/is","<IFRAME SRC=\\1 FRAMEBORDER=0 ALLOWTRANSPARENCY=true SCROLLING=YES WIDTH=97% HEIGHT=340></IFRAME>",$message,$db_cvtimes);
		} else {
			$message = preg_replace("/\[iframe\]([^\[\<\r\n\"']+?)\[\/iframe\]/is","Iframe Close: <a target=_blank href='\\1 '>\\1</a>",$message,$db_cvtimes);
		}
		strpos($message,'[s:') !== false && $message = showface($message);
	}
	if (is_array($phpcode_htm)) {
		foreach($phpcode_htm as $key => $value){
			$message = str_replace("<\twind_phpcode_$key\t>",$value,$message);
		}
	}
	return $message;
}
function SetMusic($sid) {//Set for xiami.com
	$sid = (int)$sid;
	return '<embed src="http://www.xiami.com/widget/0_'.$sid.'/singlePlayer.swf" type="application/x-shockwave-flash" width="257" height="33" wmode="transparent"></embed>';
}
function copyctrl() {
	$lenth=10;
	mt_srand((double)microtime() * 1000000);
	for ($i = 0; $i < $lenth; $i++) {
		$randval .= chr(mt_rand(0,126));
	}
	$randval = str_replace('<','&lt;',$randval);
	return "<span style=\"display:none\"> $randval </span>&nbsp;<br />";
}

function attachment($message, &$attstr = array()) {
	$matches = $aids = $attstr = array();
	preg_match_all('/\[(attachment|p_w_upload|p_w_picpath)=(\d+)\]/is', $message, $matches);
	foreach ($matches[2] as $key => $aid) {
		$aids[] = $aid;
		$attstr[$aid] = $matches[1][$key];
	}
	return $aids;
}

function tablefun($text, $width = '', $bgColor = '', $borderColor = '', $borderWidth = '') {
	global $tdcolor,$td_htm,$td_num;
	if (!preg_match("/\[tr\]\s*\[td(=(\d{1,2}),(\d{1,2})(,(\d{1,3}(%|px)?))?)?\]/", $text) && !preg_match("/^<tr[^>]*?>\s*<td[^>]*?>/", $text)) {
		return preg_replace("/\[tr\]|\[td(=(\d{1,2}),(\d{1,2})(,(\d{1,3}(%|px)?))?)?\]|\[\/td\]|\[\/tr\]/", '', $text);
	}
	if ($width && preg_match('/^(\d{1,3})(%|px)?$/', $width, $matchs)) {
		$unit = $matchs[2] ? $matchs[2] : 'px';
		$width = $unit == 'px' ? min($matchs[1], 600).'px' : min($matchs[1], 100).'%';
	} else {
		$width = '100%';
	}
	$tdStyle = '';
	$tableStyle = 'width:' . $width;
	$bgColor && $tableStyle .= ';background-color:' . $bgColor;
	$borderWidth && $tableStyle .= ';border-width:' . $borderWidth . 'px;border-style:solid';
	!$borderColor && $borderColor = $tdcolor;
	$tableStyle .= ';border-color:' . $borderColor;
	$tdStyle = ' style="border-color:' . $borderColor . '"';

	$text = preg_replace(
		array(
			'/(\[\/td\]\s*)?\[\/tr\]\s*/is',
			'/\[(tr|\/td)\]\s*\[td(=(\d{1,2}),(\d{1,2})(,(\d{1,3}(%|px)?))?)?\]/eis',
			'/\[tr\]/is',
			"/\\n/is"
		),
		array('</td></tr>',"tdfun('\\1','\\3','\\4','\\6','$tdStyle')","<tr><td{$tdStyle}>",'<br />'),
		trim(str_replace(array('\\"','<br />'),array('"',"\n"),$text))
	);
	return "<table class=\"read_form\" style=\"$tableStyle\" cellspacing=\"0\" cellpadding=\"0\">$text</table>";
}
function tdfun($t,$col,$row,$width,$tdStyle = '') {
	return ($t == 'tr' ? '<tr>' : '</td>').(($col && $row) ? "<td colspan=\"$col\" rowspan=\"$row\" width=\"$width\"{$tdStyle}>" : "<td{$tdStyle}>");
}
function size($size,$allowsize) {
	$allowsize && $size > $allowsize && $size = $allowsize;
	return "<font size=\"$size\">";
}
function cvurl($http,$url='',$name='',$ifdownload='',$checkurl) {
	global $code_num,$code_htm,$db_bbsurl;
	$code_num++;
	if ($checkurl == 1) {
		static $urlnum = 0;
		$stamp = $GLOBALS['type'] == 'ajax_addfloor' ? "_{$GLOBALS['timestamp']}" : '';
		$addjs = 'onclick="return checkUrl(this)" id="url_' . ++$urlnum . $stamp .'"';
	}
	$name = str_replace('\\"','"',$name);
	if (!$url) {
		$url = "<a href=\"http://www.$http\" target=\"_blank\" $addjs>www.$http</a>";
	} elseif (!$name) {
		$url = "<a href=\"$http$url\" target=\"_blank\" $addjs>$http$url</a>";
	} elseif (!$http && $url) {
		$url = "<a href=\"http://$url\" target=\"_blank\" $addjs>$name</a>";
	} elseif (!$ifdownload) {
		$url = "<a href=\"$http$url\" target=\"_blank\" $addjs>$name</a>";
	} else {
		$url = "<a class=\"down\" href=\"$http$url\" target=\"_blank\" $addjs>$name</a>";
	}
	$code_htm[0][$code_num] = $url;
	return "<\twind_code_$code_num\t>";
}

function nopic($url) {
	global $code_num,$code_htm,$imgpath,$stylepath;
	$code_num++;
	$code_htm[-1][$code_num]="<img src=\"$imgpath/$stylepath/file/img.gif\" align=\"absbottom\" border=\"0\"> <a target=\"_blank\" href=\"$url \">img: $url</a>";
	return "<\twind_code_$code_num\t>";
}

function cvpic($url,$type='',$picwidth='',$picheight='',$ifthumb='') {
	global $db_bbsurl,$db_picpath,$attachpath,$db_ftpweb,$code_num,$code_htm;
	$lower_url = strtolower($url);
	strncmp($lower_url,'http',4)!=0 && $url = "$db_bbsurl/$url";
	if (strpos($lower_url,'login')!==false && (strpos($lower_url,'action=quit')!==false || strpos($lower_url,'action-quit')!==false)) {
		$url = preg_replace('/login/i','log in',$url);
	}
	$url = str_replace(array("&#39;","'"),'',$url);
	$turl = $url;
	$wopen = 0;
	$alt = '';
	if ($ifthumb) {
		if ($db_ftpweb && !strpos($url,$attachpath) !== false) {
			$picurlpath = $db_ftpweb;
		} else{
			$picurlpath = $attachpath;
		}
		if (strpos($url,$picurlpath) !== false) {
			$wopen = 1;
			$alt = 'title="点击查看原图"';
			$turl = str_replace($picurlpath, "$picurlpath/thumb", $url);
		}
	}
	if ($picwidth || $picheight) {
		$wopen = !$wopen ? "if(this.parentNode.tagName!='A'&&this.width>=$picwidth)" : '';
		$onload = $styleCss = '';
		if ($picwidth) {
			$onload .= "if(this.offsetWidth>'$picwidth')this.width='$picwidth';";
			$styleCss .= "max-width:{$picwidth}px;";
		}
		if ($picheight) {
			$onload .= "if(this.offsetHeight>'$picheight')this.height='$picheight';";
			$styleCss .= "max-height:{$picheight}px;";
		}
		$code = "<img src=\"$turl\" border=\"0\" onclick=\"$wopen window.open('$url');\" style=\"$styleCss\" onload=\"$onload\" $alt>";
	} else {
		$wopen = !$wopen ? "if(this.parentNode.tagName!='A'&&this.width>screen.width-461)" : '';
		$code = "<img src=\"$turl\" border=\"0\" onclick=\"$wopen window.open('$url');\" $alt>";
	}
	if ($type) {
		return $code;
	} else {
		$code_htm[-1][++$code_num] = $code;
		return "<\twind_code_$code_num\t>";
	}
}

function phpcode($code){
	global $phpcode_htm,$codeid;
	$code = str_replace(array("[attachment=",'\\"'),array("&#91;attachment=",'"'),trim($code));
	$codeid ++;
	$code = preg_replace('/^(<br \/>)?(.+?)(<br \/>)$/','\\2',$code);
	$code = str_replace("<br />", "</li><li>", $code);
	$phpcode_htm[$codeid] = "<div class=\"f12\"><a href=\"javascript:\"  onclick=\"CopyCode(document.getElementById('code$codeid'));\">".getLangInfo('bbscode','copycode')."</a></div><div class=\"blockquote2\" id=\"code$codeid\"><ol><li>".preg_replace("/^(\<br \/\>)?(.*)/is","\\2",$code)."</li></ol></div>";
	return "<\twind_phpcode_$codeid\t>";
}

function qoute($code) {
	global $code_num,$code_htm,$i_table;
	$code_num++;
	$code_htm[6][$code_num]="<blockquote class=\"blockquote3\"><div class=\"quote\">".getLangInfo('bbscode','qoute')." </div><div class=\"text\">".str_replace('\\"','"',$code)."</div></blockquote>";
	return "<\twind_code_$code_num\t>";
}
function ifpost($tid) {
	global $admincheck,$tpc_author,$winduid,$windid,$db,$pwPostHide,$ifColonyAdmin;
	if ($windid && $tpc_author == $windid) return 2;
	static $ifview = null;
	if (!isset($ifview)) {
		if ($pwPostHide) {
			$ifview = 3;
		} elseif ($admincheck || $ifColonyAdmin) {
			$ifview = 4;
		} else {
			$pw_posts = GetPtable($GLOBALS['ptable']);
			$rs = $db->get_one("SELECT count(*) AS count FROM $pw_posts WHERE tid=".S::sqlEscape($tid)." AND authorid=".S::sqlEscape($winduid));
			$ifview = $rs['count'] > 0 ? 1 : 0;
		}
	}
	return $ifview;
}
function post($code) {
	global $code_num,$tid,$code_htm,$tpc_pid;
	$code_num++;

	if (ifpost($tid) > 0) {
		$r_ifpost = ifpost($tid);
		$code_htm[3][$code_num] = "<h6 class=\"f12 quoteTips\" style=\"border-bottom:0;\">".getLangInfo('bbscode','bbcode_hide'.$r_ifpost)."</h6><div style=\"border:1px dotted #eca46a;border-top:0;\" class=\"p10\">".str_replace('\\"','"',$code)."</div>";
	} else {
		$code_htm[3][$code_num] = "<div id=\"hidden_{$code_num}_{$tpc_pid}\" class=\"f12 hidden quoteTips\" style=\"margin:10px 0;\">" . getLangInfo('bbscode','bbcode_hide') . "</div>";
	}
	return "<\twind_code_$code_num\t>";
}
function hidden($cost,$code) {
	global $groupid,$code_num,$code_htm;
	$code_num++;

	if ($groupid != 'guest') {
		global $db,$isGM,$winddb,$userrvrc,$userpath,$windid,$tpc_author,$_CREDITDB, $winduid,$db_enhideset,$pwEncodeHide;

		static $sCredit = null;
		list($creditvalue,$credittype) = explode(',',$cost);
		if (!$credittype || !S::inArray($credittype,$db_enhideset['type'])) {
			$credittype = 'rvrc';
		}
		if (in_array($credittype,array('money','rvrc','credit','currency'))) {
			$creditname = $GLOBALS['db_'.$credittype.'name'];
			$usercredit = $credittype == 'rvrc' ? $userrvrc : $winddb[$credittype];
		} elseif (isset($_CREDITDB[$credittype])) {
			$creditname = $_CREDITDB[$credittype][0];
			if (!isset($sCredit)) {
				$query = $db->query("SELECT uid,cid,value FROM pw_membercredit WHERE uid=".S::sqlEscape($winduid));
				while ($rt = $db->fetch_array($query)) {
					$sCredit[$rt['cid']] = $rt['value'];
				}
				$db->free_result($query);
			}
			$usercredit = $sCredit[$credittype];
		} else {
			$creditname = $GLOBALS['db_moneyname'];
			$usercredit = $winddb['money'];
		}
		$creditvalue = intval(trim(stripslashes($creditvalue)));
		if ($windid != $tpc_author && $usercredit < $creditvalue && !$isGM && !$pwEncodeHide) {
			$code = "<blockquote class=\"blockquote\" style=\"margin:10px 0;\">"
					. getLangInfo('bbscode','bbcode_encode1',array('name'=>$creditname,'value'=>$creditvalue))
					. "</blockquote>";
		} else {
			$code = "<h6 class=\"quote\" style=\"padding:0;margin:0;\"><span class=\"s2 f12 fn\">"
					. getLangInfo('bbscode','bbcode_encode2',array('name'=>$creditname,'value'=>$creditvalue))
					. "</span></h6><blockquote class=\"blockquote\" style=\"margin:10px 0;\">"
					. str_replace('\\"','"',$code)
					. "</blockquote>";
		}
	} else {
		$code = "<blockquote class=\"blockquote\" style=\"margin:10px 0;\">"
				. getLangInfo('bbscode','bbcode_encode3')
				. "</blockquote>";
	}
	$code_htm[4][$code_num] = $code;
	return "<\twind_code_$code_num\t>";
}
function sell($cost,$code) {
	global $isGM,$windid,$winduid,$tpc_author,$tpc_buy,$tpc_pid,$fid,$tid,$i_table,$groupid,$code_num,$code_htm, $db_bbsurl,$db_sellset,$_CREDITDB,$pwSellHide,$sell_num;
	$code_num++;
	list($creditvalue,$credittype) = explode(',',$cost);
	$creditvalue = (int)$creditvalue;
	if ($creditvalue < 0) {
		$creditvalue = 0;
	} elseif ($db_sellset['price'] && $creditvalue > $db_sellset['price']) {
		$creditvalue = $db_sellset['price'];
	}
	$creditname = isset($_CREDITDB[$credittype]) ? $_CREDITDB[$credittype][0] : (in_array($credittype,array('money','rvrc','credit','currency')) ? $GLOBALS['db_'.$credittype.'name'] : $GLOBALS['db_moneyname']);
	
	$userarray = $tpc_buy ? unserialize($tpc_buy) : array();
	
	$count = 0;
	foreach ($userarray as $value) {
		if ($value) {
			$count++;
			$buyers.="<option value=''>".$value."</option>";
		}
	}
	$isShow = ($winduid && ($isGM || $pwSellHide || $tpc_author == $windid || ($userarray && @in_array($winduid,array_keys($userarray)))));
	$printcode = '';
	$buyBaseUrl = "job.php?action=buytopic&tid=$tid&pid=$tpc_pid&verify=$GLOBALS[verifyhash]&page={$GLOBALS['page']}";
	if (++$sell_num == 1) {
		$printcode = "<div class=\"quoteTips f12 mb10\"><span class=\"mr10\">" 
						. getLangInfo('bbscode','bbcode_sell_info',array('value' => $creditvalue, 'name' => $creditname, 'count' => $count))
						. "</span> "
						. getLangInfo('bbscode','bbcode_sell_record_buy',array('record' => $buyBaseUrl."&type=record", 'buy' => $buyBaseUrl."&type=buy"))
						."</div>";
		if (!$isShow) {
			$printcode .= "<div class=\"mb10 f12 b\">"
						. getLangInfo('bbscode','bbcode_sell_notice')
					. "</div> "; 
		}
	}
	if ($isShow) {
		$printcode .= "<div style=\"border:1px dotted #cccccc;padding:5px;\">"
					. str_replace('\\"','"',$code)
					. "</div>";
	} else {
		$printcode .= "<div class=\"quoteTips f12\">"
					. getLangInfo('bbscode','bbcode_sell_infonotice',array('value' => $creditvalue, 'name' => $creditname,'buy' => $buyBaseUrl."&type=buy"))
					. "</div>";
	}
	
	$code_htm[5][$code_num] = $printcode;
	return "<\twind_code_$code_num\t>";
}

function shield($code){
	global $groupid;
	$code = getLangInfo('bbscode',$code);
	return "<span style=\"color:black;background-color:#ffff66\">$code</span>";
}
function wplayer($wmvurl,$width='',$height='',$auto='',$type='wmv'){
	static $player_id = 0;
	!$width && $width = 314;
	!$height && $height = 256;
    return (++$player_id == 1 ? "<script id=\"js_player\" src=\"js/player.js\"></script>" : '')."<div id=\"player_$player_id\"><span class=\"bt2\" style=\"margin-left:0;\"><span><button onclick=\"player('player_$player_id','$wmvurl','$width','$height','$type');\" type=\"button\">".getLangInfo('bbscode','player_'.$type)."</button></span></span></div>".($auto == '1' ? "<script language=\"JavaScript\">player('player_{$player_id}','$wmvurl','$width','$height','$type');</script>" : '');
}
function showface($message) {
	global $face,$db_cvtimes;
	//* include_once pwCache::getPath(D_P.'data/bbscache/postcache.php');
	extract(pwCache::getData(D_P.'data/bbscache/postcache.php', false));
	$message = preg_replace("/\[s:(.+?)\]/eis","postcache('\\1')",$message,$db_cvtimes);
	return $message;
}
function postcache($key) {
	global $face,$imgpath,$tpc_author;
	is_array($face) && !$face[$key] && $face[$key] = current($face);
	if ($face[$key][2]) {
		return "<br /><img src=$imgpath/post/smile/{$face[$key][0]} /><br />[<span class=\"s1 b\">$tpc_author</span>] {$face[$key][2]}<br />";
	} else {
		return "<img src=\"$imgpath/post/smile/{$face[$key][0]}\" />";
	}
}
function wordsConvert($str, $wdstruct = array()) {
	$wordsfb = L::loadClass('FilterUtil', 'filter');
	return $wordsfb->convert($str, $wdstruct);
}
function leaveword($code,$pid) {
	global $admincheck,$imgpath,$tid;
	return "<div id=\"lwd_$pid\" class=\"louMes\">".($admincheck ? "<span onclick=\"read.obj=getObj('lwd_$pid');ajax.send('pw_ajax.php?action=leaveword','step=3&tid=$tid&pid=$pid',worded);\" class=\"adel fr\">x</span>" : '')."<h4 class=\"b\">".getLangInfo('bbscode','post_reply')."</h4><p>".str_replace("\n","<br />",$code)."</p></div>";
}
function relatetag($message, $tags) {
	if (!is_array($tags)) return $message;
	foreach ($tags as $key => $tag) {
		$message = preg_replace("/(?<=[\s\"\]>()]|[\x7f-\xff]|^)(".preg_quote($tag, '/').")([.,:;-?!()\s\"<\[]|[\x7f-\xff]|$)/siUe","tagfont('\\1','\\2')",$message,1);
	}
	return $message;
}
function tagfont($tag,$code) {
	static $rlt_id = 0;
	$rlt_id++;
	return "<span onclick=\"sendmsg('pw_ajax.php','action=relatetag&tagname=$tag',this.id)\" style=\"cursor:pointer;border-bottom: 1px solid #FA891B;\" id=\"rlt_$rlt_id\">$tag</span>$code";
}

function getReadTag($tags) {
	list($tagdb, $relatetag) = explode("\t", $tags);
	$html = '';
	$list = array();
	$tagdb = $tagdb ? parseReadTag($tagdb) : array();
	foreach ($tagdb as $key => $tag) {
		$tag && $html .= "<a href=\"link.php?action=tag&tagname=".rawurlencode($tag)."\"><span class=\"s2\">$tag</span></a> ";
	}
	$GLOBALS['db_readtag'] && $list = array_merge($tagdb, $relatetag ? parseReadTag($relatetag) : array());
	return array($html, $list);
}

/**
 * 将保存的TAGS字符串重新解开
 * @param string $tags
 */
function parseReadTag($tags){
	$pattern = '/"([^"]+)"/';
	$readTags = array();
	if (preg_match_all($pattern, $tags ,$m)) {
		$tmpArray = preg_split($pattern, $tags);
		$tags1 = array();
		foreach ($tmpArray as $v){
			$v = trim($v);
			if(!$v) continue;
			$tags1 = array_merge($tags1,explode(' ',$v));
		}
		$readTags = array_merge($tags1,$m[1]);
	} else {
		$readTags = explode(' ',$tags);
	}
	foreach ($readTags as $k=>$v) {
		if(!$v) unset($readTags[$k]);
	}
	return $readTags;
}


/**
 * 自定义字段格式化
 */
function formatCustomerField($fieldInfo,$value){
	global $areas;
	$html = '';
	if (!is_array($fieldInfo['options'])) {
		$options = explode("\n", $fieldInfo['options']);
		$fieldInfo['options'] = array();
		foreach ($options as $v){
			list($tmpKey,$tmpVal) = explode('=', $v);
			if(!$tmpVal) continue;
			$fieldInfo['options'][$tmpKey] = $tmpVal;
		}
	}
	switch ($fieldInfo['type']){
		case 7:
			$data = explode(',',$areas[$value]);
			break;
		case 4:
			$data = array();
			$value = explode("\t",$value);
			foreach ($value as $v){
				$data[] = $fieldInfo['options'][$v];
			}
			break;
		case 3:
		case 5:
			$data = '';
			foreach ($fieldInfo['options'] as $k=>$v){
				if($k == $value){
					$data = $v;
				}
			}
			break;
		default:
			$data = $value;
			break;
	}
	if(!is_array($data) || count($data) == 1){
		$html = $data;
		is_array($html) && $html = $html[0];
	} else {
		$html = '<ul>';
		foreach ($data as $v){
			$html .= sprintf('<li>%s</li>',$v);
		}
		$html .= '</ul>';
	}
	return $html;
}

/**
 * 帖子内容页附件展示调用库
 * @package read
 * @author sky_hold@163.com
 */
class attachShow {

	var $attachs = array(); //附件列表
	var $sellids = array(); //出售的附件id集合
	var $isAdmin; //是否有管理权限
	var $isConfineView; //限制浏览
	var $isImgShow; //是否显示图片
	var $mode;
	var $downloadUrl = 'job.php?action=download&';

	/**
	 * 初始化附件展示配置
	 * @param bool $isAdmin 是否拥有管理权限
	 * @param string $uploadset 附件展示设置(是否显示图片等)
	 * @param bool $isConfineGuset 是否限制游客浏览
	 * @param string $mode 应用场景(帖子、活动、日志等)
	 */
	function attachShow($isAdmin, $uploadset = '', $isConfineGuset = false, $mode = '') {
		list(,, $downloadmoney, $downloadimg) = explode("\t", $uploadset);
		$this->isImgShow = (!$downloadmoney || !$downloadimg || $GLOBALS['_G']['allowdownload'] == 2);
		$this->isAdmin = $isAdmin;
		$this->isConfineView = ($isConfineGuset && !$GLOBALS['winduid']);
		$this->mode = $mode;
		$mode && $this->downloadUrl .= 'type=' . $mode . '&';
	}
	
	/**
	 * 从数据库获取指定id的附件列表，初始化类库信息
	 * @param int $tid 帖子tid
	 * @param array $pids 回复pid集合
	 */
	function init($tid, $pids) {
		global $db;
		$array = array();
		$query = $db->query('SELECT * FROM pw_attachs WHERE tid=' . S::sqlEscape($tid) . " AND pid IN (" . S::sqlImplode($pids) . ")");
		while ($rt = $db->fetch_array($query)) {
			$array[] = $rt;
		}
		$this->setData($array);
	}

	function setData($array, $vkey = 'pid') {
		foreach ($array as $key => $rt) {
			(!isset($rt[$vkey]) || $rt[$vkey] == '0') && $rt[$vkey] = 'tpc';
			$rt['needrvrc'] && $rt['special'] == 2 && $this->sellids[] = $rt['aid'];
			$this->attachs[$rt[$vkey]][$rt['aid']] = $rt;
		}
		foreach ($this->attachs as $key => $value) {
			ksort($this->attachs[$key]);
		}
	}

	/**
	 * 解析内容中的附件信息
	 * @param int $pid 回复pid
	 * @param string $content 帖子内容
	 * @param bool $isSelf 是否是本帖作者
	 * return array 归类后的附件列表
	 */
	function parseAttachs($pid, &$content, $isSelf) {
		$array = array();
		if ($attachs = $this->getAttachs($pid, $isSelf)) {
			$aids = attachment($content, $attstr);
			foreach ($attachs as $atype => $value) {
				foreach ($value as $k => $v) {
					if (in_array($k, $aids)) {
						$content = $this->parseContent($content, $atype, $v, $attstr[$k]);
					} else {
						$array[$atype][$k] = $v;
					}
				}
			}
		}
		return $array;
	}

	function getAttachs($pid, $isAdmin) {
		if (!isset($this->attachs[$pid])) return array();
		$isAdmin = $isAdmin || $this->isAdmin;
		$attachs = $this->attachs[$pid];
		$array = array();
		foreach ($attachs as $key => $attach) {
			$attach['dfadmin'] = $isAdmin;
			if ($atype = $this->analyse($attach)) {
				$array[$atype][$attach['aid']] = $attach;
			}
		}
		return $array;
	}

	function clearAttachTags($content) {
		return preg_replace('/\[(attachment|p_w_upload|p_w_picpath)=\d+\]/is', '', $content);
	}

	function analyse(&$attach) {
		global $db_windpost,$db_sellset, $db_hash;
		$atype = '';
		if ($attach['type'] == 'img' && $attach['needrvrc'] == 0 && $this->isImgShow) {
			$a_url = geturl($attach['attachurl'], 'show');
			if (is_array($a_url)) {
				$atype = 'pic';
				$attach += array(
					'url' => $a_url[0],
					'img' => cvpic($a_url[0].'?'.$attach['size'], 1, $db_windpost['picwidth'], $db_windpost['picheight'], $attach['ifthumb'] & 1),
					'miniUrl' => attachShow::getMiniUrl($attach['attachurl'], $attach['ifthumb'], $a_url[1])
				);
			} elseif ($a_url == 'imgurl') {
				$atype = 'picurl';
				$attach += array(
					'verify' => md5("showimg{$attach[tid]}{$attach[pid]}{$attach[fid]}{$attach[aid]}{$db_hash}")
				);
			}
		} else {
			$atype = 'downattach';
			if ($attach['needrvrc'] > 0) {
				!$attach['ctype'] && $attach['ctype'] = ($attach['special'] == 2) ? 'money' : 'rvrc';
				if ($attach['type'] == 'img') {
					$a_url = geturl($attach['attachurl'], 'show');
					$attach['img'] = cvpic($a_url[0].'?'.$attach['size'], 1, $db_windpost['picwidth'], $db_windpost['picheight'], $attach['ifthumb'] & 1);
				}
				if ($attach['special'] == 2) {
					$db_sellset['price'] > 0 && $attach['needrvrc'] = min($attach['needrvrc'], $db_sellset['price']);
				}
			}
			$attach += array(
				'cname' => pwCreditNames($attach['ctype']),
				'ext' => strtolower(substr(strrchr($attach['name'],'.'),1))
			);
		}
		return $atype;
	}

	function parseContent($message, $atype, $att, $attstr = '') {
		!$attstr && $attstr = 'attachment';
		$html = '';
		switch ($atype) {
			case 'pic': $html = $this->parsePicHtml($att);break;
			case 'downattach': $html = $this->parseAttachHtml($att);break;
			case 'picurl':
				$html = "&#36828;&#31243;&#22270;&#29255;：<a href=\"job.php?action=showimg&tid={$GLOBALS[tid]}&pid={$GLOBALS[tpc_pid]}&fid={$GLOBALS[fid]}&aid={$att[aid]}&verify={$att[verify]}\" target=\"_blank\">$att[name]</a>";break;
		}
		$message = str_replace("[$attstr={$att[aid]}]", "<span id=\"att_$att[aid]\" class=\"f12\">".$html.'</span>', $message);
		return $message;
	}
	
	function parsePicHtml($att) {
		global $forumcolorone,$forumcolortwo,$read,$isTucool,$isGM,$authorid;
		$html  = "<span id=\"td_att$att[aid]\" onmouseover=\"read.open('menu_att{$att['aid']}','td_att{$att['aid']}');\" style=\"display:inline-block;\">$att[img]</span>";
		$html .= "<div id=\"menu_att{$att[aid]}\" class=\"pw_menu\" style=\"display:none;\"><div style=\"border:1px solid $forumcolorone;background:$forumcolortwo;padding:5px 10px;\">";
		$att['descrip'] && $html .= "<p>描述:$att[descrip]</p>";
		$html .= "<p><span class=\"mr10\">图片:$att[name]</span>";
		if($att['dfadmin']) {
			$setCoverHtml = ($isTucool && ($read['authorid'] == $authorid || $isGM)) ? sprintf('[<a class="cp s4" onclick="setcover(\'%s\',this);">设为封面</a>] ',$att[aid]) : '';
			$html .= "{$setCoverHtml}[<a class=\"cp s4\" onclick=\"delatt('','$att[aid]','{$this->mode}');\">删除</a>]";
		}
		$html .= "</p></div></div>";
		return $html;
	}
	
	function parseAttachHtml($att) {
		$html = '<b>' . $att['descrip'] . '</b>' . "<img src=\"$GLOBALS[imgpath]/$GLOBALS[stylepath]/file/$att[type].gif\" align=\"absbottom\" /><a href=\"{$this->downloadUrl}aid=$att[aid]\" onclick=\"return ajaxurl(this,'&check=1');\"> $att[name]</a> ($att[size] K) &#19979;&#36733;&#27425;&#25968;:$att[hits] ";
		if ($att['needrvrc'] > 0) {
			$tmpLang = array(
				'title' => array(1 => '', 2 => '<span class="b s2">&#38468;&#20214;&#20986;&#21806;：</span>'),
				'type' => array(1 => '&#21152;&#23494;', 2 => '&#20986;&#21806;'),
				'need' => array(1 => '&#38656;&#35201;', 2 => '&#21806;&#20215;'),
				'record' => array(1 => '', 2 => "<a class=\"mr10 s4\" title=\"&#26597;&#30475;&#35760;&#24405;\" onclick=\"sendmsg('job.php?action=attachbuy&amp;type=record&amp;aid={$att['aid']}');\" href=\"javascript:;\">[&#35760;&#24405;]</a>")
			);
			$type = $att['special'] == 2 ? 2 : 1;
			$html = $tmpLang['title'][$type] . "<span class=\"w\"><img src=\"$GLOBALS[imgpath]/$GLOBALS[stylepath]/file/$att[type].gif\" align=\"absmiddle\" /><a id=\"td_att{$att['aid']}\" href=\"{$this->downloadUrl}aid=$att[aid]\" onclick=\"return ajaxurl(this,'&check=1');\" onmouseover=\"read.open('menu_att{$att['aid']}','td_att{$att['aid']}');\" style=\"margin-left:5px;margin-right:10px;\">$att[name]</a></span>";
			$att['type'] == 'img' && $this->viewHiddenAtt($att) && $html.= '<br>'.$att['img'].'<br>';
			$html .= "<div id=\"menu_att{$att['aid']}\" class=\"pw_menu\" style=\"display:none;\"><div class=\"p10\"><ul>".
				"<li>&#31867;&#22411;: <span class=\"b s2\">{$tmpLang['type'][$type]}</span></li>".
				"<li>&#19979;&#36733;: $att[hits]</li>".
				"<li>{$tmpLang['need'][$type]}: {$att[needrvrc]}{$att[cname]}</li>".
				"<li>&#22823;&#23567;: $att[size] K</li>";
			$att['descrip'] && $html .= "<li>&#25551;&#36848;: {$att['descrip']}</li>";
			$html .= "<li>{$tmpLang['record'][$type]}<span onclick=\"return ajaxurl(getObj('td_att{$att['aid']}'),'&check=1');\" class=\"mr10 s4 cp\" id=\"fg_{$att['aid']}\">[&#19979;&#36733;]</span>";
			$att['dfadmin'] && $html .= "<a onclick=\"delatt('tpc','{$att['aid']}','{$this->mode}');\" class=\"cp s4\">[&#21024;&#38500;]</a>";
			$html .= "</li></ul></div></div>";
		} elseif (in_array($att['ext'], array('mp3','wma','wmv','rm','swf'))) {
			$html .= "[<a style=\"cursor:pointer\" onclick=\"playatt('$att[aid]');\">&#35797;&#25773;</a>]";
		}
		return $html;
	}

	function viewHiddenAtt($attach) {
		if ($attach['dfadmin']) return true;
		if ($attach['special'] == 2 && $this->isBuyFromSellAtt($attach['aid'])) return true;
		if ($attach['special'] == 1 && $this->checkCreditFromHiddenAtt($attach['ctype'], $attach['needrvrc'])) return true;
		return false;
	}

	function isBuyFromSellAtt($aid) {
		static $buyAids = null;
		if (!isset($buyAids)) {
			global $db,$winduid;
			$buyAids = array();
			if ($this->sellids) {
				$query = $db->query("SELECT aid FROM pw_attachbuy WHERE uid= " . S::sqlEscape($winduid) . ' AND aid IN(' . S::sqlImplode($this->sellids) . ')');
				while ($rt = $db->fetch_array($query)) {
					$buyAids[] = $rt['aid'];
				}
			}
		}
		return in_array($aid, $buyAids);
	}

	function checkCreditFromHiddenAtt($ctype, $v) {
		$hav = 0;
		if (in_array($ctype, array('money', 'rvrc', 'credit', 'currency'))) {
			$hav = $ctype == 'rvrc' ? $GLOBALS['userrvrc'] : $GLOBALS['winddb'][$ctype]; 
		}
		if (is_numeric($ctype)) {
			static $creditdb = null;
			if (!isset($creditdb)) {
				global $credit;
				require_once( R_P ."require/credit.php");
				$creditdb = $credit->get($GLOBALS['winduid'],'CUSTOM');
			}
			$hav = $creditdb[$ctype];
		}
		return $hav > $v;
	}

	function isShow(&$ifhide, $tid) {
		$ifhide > 0 && ifpost($tid) > 0 && $ifhide = 0;
		return (!$this->isConfineView && !$ifhide);
	}

	/**
	 * static publick function
	 */
	function getMiniUrl($path, $ifthumb, $where) {
		$dir = '';
		($ifthumb & 1) && $dir = 'thumb/';
		($ifthumb & 2) && $dir = 'thumb/mini/';
		if ($where == 'Local') return $GLOBALS['attachpath'] . '/' . $dir . $path;
		if ($where == 'Ftp') return $GLOBALS['db_ftpweb'] . '/' . $dir . $path;
		if (!is_array($GLOBALS['attach_url'])) return $GLOBALS['attach_url'] . '/' . $dir . $path;
		return $GLOBALS['attach_url'][0] . '/' . $dir . $path;
	}
}
?>