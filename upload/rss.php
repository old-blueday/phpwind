<?php
define('SCR','rss');
define('RSS',1);
require_once('global.php');
require_once(R_P . 'require/bbscode.php');
require_once(R_P . 'require/functions.php');

$Rss_newnum = 200;
$Rss_listnum = 200;
$Rss_updatetime = 10;

if (isset($db_openim_rssTtl)) {
	$ttl = (int)$db_openim_rssTtl;
} else {
	$ttl = 60;
}
$expiredSeconds = 10;


if ($db_http != 'N') {
	$imgpath = $db_http;
} else {
	$imgpath = $db_bbsurl . '/' . $db_picpath;
}
$stylepath = 'wind';

if ($tid) {
	$rs = $db->get_one("SELECT t.subject,t.fid,t.ptable,f.name,f.allowvisit,f.f_type,t.ifshield as ifshield, 
							   t.ifhide as ifhide,f.password as fpassword,f.forumsell as forumsell,fe.forumset as forumset, t.lastpost as lastpost
						FROM pw_threads t LEFT JOIN pw_forums f USING(fid) 
						LEFT JOIN pw_forumsextra fe ON f.fid=fe.fid WHERE tid=" . pwEscape($tid));
	
	if (!$rs || $rs['allowvisit'] != '' || $rs['f_type'] == 'hidden' || $rs['ifshield'] == 1
		|| trim($rs['fpassword']) != '' || trim($rs['forumsell']) != '') {
		exit('Forbidden');
	}
	if (isset($rs['forumset']) && $rs['forumset']) {
		$forumset = unserialize($rs['forumset']);
	} else {
		$forumset = array();
	}
	
	L::loadClass('rss', 'utility', false);
	$title = decodeRssHtml($rs['subject']);
	$title = xmlEscape($title);
	if ($db_htmifopen) {
		$link = "$db_bbsurl/read{$db_dir}tid-{$tid}$db_ext";
	} else {
		$link = "$db_bbsurl/read.php?tid={$tid}";
	}
	$channel = array(
		'title'			=>  $title,
		'link'			=>  $link,
		'description'	=>  "最新回复",
		'copyright'		=>  "Copyright(C) $db_bbsname",
		'generator'		=>  "http://www.phpwind.com",
		'lastBuildDate' =>  date('r'),
		'ttl'           =>  $ttl,
		'pubDate'       =>  date('r',$rs['lastpost']),
	);
	$Rss = new Rss(array('xml'=>"1.0",'rss'=>"2.0",'encoding'=>$db_charset));
	$Rss->channel($channel);

	$pw_posts = GetPtable($rs['ptable']);
	$query = $db->query("SELECT aid,ifhide,pid,tid,subject,aid,author,postdate,anonymous,content 
						FROM $pw_posts 
						WHERE tid=" . pwEscape($tid) . " AND ifshield=0
						ORDER BY postdate DESC LIMIT $Rss_listnum");
	while ($rt = $db->fetch_array($query)) {
		$rt['anonymous'] && $rt['author'] = $db_anonymousname;
		$link = "$db_bbsurl/job.php?action=topost&tid={$rt['tid']}&pid={$rt['pid']}";
		$postTitle = decodeRssHtml($rt['subject']);
		$postTitle = xmlEscape($postTitle);
		
		$allow = is_array($db_windpost) ? $db_windpost : array();
		$allow['flash'] = 0;
		$allow['mpeg'] = 0;
		$allow['checkurl'] = 0;
		
		$description = $rt['content'];
		
		$pid = $rt['pid'];
		$attachShow = new attachShow(false, isset($forumset['uploadset']) ? $forumset['uploadset'] : '', isset($forumset['viewpic']) ? $forumset['viewpic'] : 0);
		$attachShow->init($rt['tid'], array($pid));
		if ($rt['aid']) { //存在附件
			if (!$rt['ifhide'] && !$attachShow->isConfineView) { //不隐藏附件
				foreach ($attachShow->getAttachs($pid, false) as $type => $attachs) {
					if ($type == 'pic') {
					} else { //对除图片外的其他附件做特殊处理
						foreach ($attachs as $attachmentId => $attachRow) {
							unset($attachShow->attachs[$pid][$attachmentId]);
							$description = formatNotImageAttachs($description, $attachRow);
						}
					}
				}
				$attachShow->parseAttachs($pid, $description, false);
			} else {
				$description = $attachShow->clearAttachTags($description);
			}
		}
		
		$description = stripPostHideAndSell($description);
		$description = convert($description, $allow);
		$description = xmlEscape($description);
		
		$item = array(
			'title'       =>  $postTitle ? $postTitle : 'Re:' . $title,
			'description' =>  $description,
			'link'        =>  $link,
			'author'      =>  $db_ceoemail . ' (' . $rt['author'] . ')',
			'category'    =>  strip_tags($rs['name']),
			'pubDate'     =>  date('r',$rt['postdate']),
		);
		$Rss->item($item);
	}
	
	$rssItems = ob_get_contents();
	ob_end_clean();
	$rssItems = $Rss->getItems();
	$etag = md5($rssItems);
	if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && $etag == $_SERVER['HTTP_IF_NONE_MATCH']){
		header ("HTTP/1.0 304 Not Modified");
		exit;
	}else{
		header('Etag:' . $etag);
	}
    
    header("Content-type: application/xml");
    echo $Rss->getRss();exit;
} else {
	$cache_path = D_P . 'data/bbscache/rss_' . $fid . '_cache.xml';
	if ($timestamp - pwFilemtime($cache_path) > $Rss_updatetime * 60) {
		L::loadClass('rss', 'utility', false);
		require_once(D_P.'data/bbscache/forum_cache.php');
		if ($db_tlist) {
			@extract($db->get_one("SELECT MAX(tid) AS tid FROM pw_threads"));
			$pw_tmsgs = GetTtable($tid);
		} else {
			$pw_tmsgs = 'pw_tmsgs';
		}
		
		if ($fid) {
			$rt = $db->get_one("SELECT allowvisit,f_type,password,forumsell
								FROM pw_forums 
								WHERE fid=" . pwEscape($fid));
			if (!$rt || $rt['allowvisit'] != '' || $rt['f_type'] == 'hidden' 
					 || trim($rt['password']) != '' || trim($rt['forumsell']) != '') {
				exit('Forbidden');
			}
		}
		
		$sql = $forceindex = '';
		if ($fid) {
			$sql = "WHERE t.fid=" . pwEscape($fid) . " AND ifcheck=1 AND t.ifshield=0
						  AND specialsort='0' AND postdate>" . pwEscape($timestamp - 604800) . " 
					ORDER BY postdate DESC LIMIT $Rss_listnum";
		} else {
			$fids  = $extra = '';
			$query = $db->query("SELECT fid 
								FROM pw_forums 
								WHERE allowvisit='' AND f_type!='hidden' AND password='' AND forumsell=''");
			while ($rt = $db->fetch_array($query)) {
				$fids .= $extra."'".$rt['fid']."'";
				$extra = ',';
			}
			if ($fids) {
				$sql = "WHERE t.fid IN($fids) AND ifcheck=1 AND specialsort='0' AND ifshield=0 AND postdate>" . pwEscape($timestamp - 604800) . " 
						ORDER BY postdate DESC 
						LIMIT $Rss_newnum";
				$forceindex = '';
			}
		}
		$title = $fid ? strip_tags($forum[$fid]['name']) : $db_bbsname;
		$title = decodeRssHtml($title);
		$title = xmlEscape($title);
		
		$channel = array(
			'title'			=>  $title,
			'link'			=>  $db_bbsurl,
			'description'	=>  "最新帖子",
			'copyright'		=>  "Copyright(C) $db_bbsname",
			'generator'		=>  "http://www.phpwind.com",
			'lastBuildDate' =>  date('r'),
			'ttl'           =>  $ttl
		);

		$Rss = new Rss(array('xml' => "1.0",'rss' => "2.0",'encoding' => $db_charset));
		
		if ($sql) {
			$query = $db->query("SELECT t.tid,t.fid,t.subject,t.author,t.ifhide,t.postdate,t.anonymous,tm.aid,tm.content,tm.ifsign,fe.forumset as forumset 
								FROM pw_threads t $forceindex 
								LEFT JOIN pw_forumsextra fe ON t.fid=fe.fid RIGHT JOIN $pw_tmsgs tm ON tm.tid=t.tid $sql");
			$lastPostFlag = true;
			while ($rt = $db->fetch_array($query)) {
				if (isset($rt['forumset']) && $rt['forumset']) {
					$forumset = unserialize($rt['forumset']);
				} else {
					$forumset = array();
				}
				
				if ($lastPostFlag){
					$forumLastPostTime = $rt['postdate'];
					$lastPostFlag = false;
				}
				$rt['anonymous'] && $rt['author'] = $db_anonymousname;
				if ($db_htmifopen) {
					$link = "$db_bbsurl/read{$db_dir}tid-{$rt['tid']}$db_ext";
				} else {
					$link = "$db_bbsurl/read.php?tid={$rt['tid']}";
				}
				$postTitle = decodeRssHtml($rt['subject']);
				$postTitle = xmlEscape($postTitle);

				$allow = is_array($db_windpost) ? $db_windpost : array();
				$allow['flash'] = 0;
				$allow['mpeg'] = 0;
				$allow['checkurl'] = 0;
				
				$description = $rt['content'];
				
				$pid = 0;
				
				//对附件做处理
				
				$attachShow = new attachShow(false, isset($forumset['uploadset']) ? $forumset['uploadset'] : '', isset($forumset['viewpic']) ? $forumset['viewpic'] : 0);
				$attachShow->init($rt['tid'], array($pid));

				if ($rt['aid']) { //存在附件
					$pid = 'tpc';
					$hasUnsetAttach = false;
					if (!$rt['ifhide'] && !$attachShow->isConfineView) { //不隐藏附件
						foreach ($attachShow->getAttachs($pid, false) as $type => $attachs) {
							if ($type == 'pic') {
							} else { //对除图片外的其他附件做特殊处理
								$hasUnsetAttach = true;
								foreach ($attachs as $attachmentId => $attachRow) {
									unset($attachShow->attachs[$pid][$attachmentId]);
									$description = formatNotImageAttachs($description, $attachRow);
								}
							}
						}
						$attachmentLeft = $attachShow->parseAttachs($pid, $description, false);
					} else {
						$description = $attachShow->clearAttachTags($description);
					}
					if ($attachShow->isConfineView) {
						$description .= '<div style="margin-top: 10px;">';
						$description .= '<span style="background: none repeat scroll 0 0 #F3F9FB;border: 1px solid #A6CBE7;padding: 3px 10px;">本主题包含附件，请 <a style="color: #014C90;" href="' . $link . '" target="_blank">访问</a> 社区查看</span>';
						$description .= '</div>';
					} elseif ($rt['ifhide'] > 0) {
						$description .= '<div style="margin-top: 10px;">';
						$description .= '<span style="margin:0;background: none repeat scroll 0 0 #FFFAE1;border: 1px dotted #ECA46A;padding: 5px 10px 5px 28px;">附件设置隐藏，需要 <a style="color: #014C90;" href="' . $link . '" target="_blank">访问</a> 社区回复后才能看到</span>';
						$description .= '</div>';
					} elseif ($hasUnsetAttach || $attachmentLeft) {
						$description .= '<div style="margin-top: 10px;">';
						$description .= '<span style="background: none repeat scroll 0 0 #F3F9FB;border: 1px solid #A6CBE7;padding: 3px 10px;">本帖包含的部分附件只能  <a style="color: #014C90;" href="' . $link . '" target="_blank">访问</a> 社区查看</span>';
						$description .= '</div>';
					}
				}

				if ($rt['ifsign'] < 2) {
					$description = str_replace("\n", "<br />", $description);
				}
				
				$description = stripPostHideAndSell($description);
				$description = convert($description, $allow);
				$description = xmlEscape($description);
				$item = array(
					'title'       => $postTitle,
					'description' => $description,
					'link'        => $link,
					'author'      => $db_ceoemail . ' (' . $rt['author'] . ')',
					'category'    => $forum[$rt['fid']]['name'],
					'pubDate'     => date('r',$rt['postdate']),
				);
				$Rss->item($item);
			}
		}
		$channel['pubDate'] = date('r',$forumLastPostTime);
		$Rss->channel($channel);
		$Rss->generate($cache_path);
	}
	header("Content-type: application/xml");
	if (file_exists($cache_path)){
		$etag = '"' . md5_file($cache_path) . '"';
		if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && $etag == $_SERVER['HTTP_IF_NONE_MATCH']){
			$statusCode = 304;
		}else{
			header('Etag:' . $etag);
		}
			
		$fileModifiedTime = pwFilemtime($cache_path);
		if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && $fileModifiedTime == $_SERVER['HTTP_IF_MODIFIED_SINCE']){
			$statusCode = 304;
		}else{
			$lastModifiedTime = date('D, d M Y H:i:s', $fileModifiedTime) . ' GMT';
			header("Last-Modified: $lastModifiedTime");
		}
		if ($statusCode === 304){
			header ("HTTP/1.0 304 Not Modified");
			exit;
		}
	}
	@readfile($cache_path);
	exit;
}

function decodeRssHtml($str){
	return html_entity_decode(str_replace('&#160;', ' ', str_replace('&nbsp;', ' ', $str)), ENT_QUOTES);
}

function stripPostHideAndSell($content) {
	$content = preg_replace("/\[post\](.+?)\[\/post\]/is", "<br />(回复可见内容)<br />", $content);
	$content = preg_replace("/\[hide=(.+?)\](.+?)\[\/hide\]/is", "<br />(加密内容)<br />", $content);
	$content = preg_replace("/\[sell=(.+?)\](.+?)\[\/sell\]/is", "<br />(出售内容)<br />", $content);
	return $content;
}

function xmlEscape($str){
    return str_replace(']]>', ']]&gt;', $str);
}

function formatNotImageAttachs($content, $att){
	$replace = '(附件)';
	if ($att['needrvrc'] > 0) {
		if ($att['special'] == 2) {
			$replace = '(出售附件)';
		} else {
			$replace = '(加密附件)';
		}
	}
	return str_replace("[attachment={$att[aid]}]", "<span id=\"att_$att[aid]\">".$replace.'</span>', $content);
}