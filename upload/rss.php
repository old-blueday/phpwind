<?php
define('SCR','rss');
define('RSS',1);
require_once('global.php');

$Rss_newnum=20;
$Rss_listnum=20;
$Rss_updatetime=10;

if ($tid) {
	$rs = $db->get_one("SELECT t.subject,t.fid,t.ptable,f.name,f.allowvisit,f.f_type FROM pw_threads t LEFT JOIN pw_forums f USING(fid) WHERE tid=".S::sqlEscape($tid));
	if (!$rs || $rs['allowvisit'] != '' || $rs['f_type'] == 'hidden') {
		echo "<META HTTP-EQUIV='Refresh' CONTENT='0; URL=rss.php'>";exit;
	}
	L::loadClass('rss', 'utility', false);
	$title = substrs($rs['subject'],40);
	if ($db_htmifopen) {
		$link = "$db_bbsurl/read{$db_dir}tid-{$tid}$db_ext";
	} else {
		$link = "$db_bbsurl/read.php?tid={$tid}";
	}
	$channel = array(
		'title'			=>  $title,
		'link'			=>  $link,
		'description'	=>  "Latest $Rss_newnum replies of $title",
		'copyright'		=>  "Copyright(C) $db_bbsname",
		'generator'		=>  "phpwind forums by phpwind studio",
		'lastBuildDate' =>  date('r'),
	);
	$image = array(
		'url'		  =>  "$imgpath/$stylepath/rss.gif",
		'title'		  =>  'phpwind board',
		'link'		  =>  $db_bbsurl,
		'description' =>  $db_bbsname,
	);
	$Rss = new Rss(array('xml'=>"1.0",'rss'=>"2.0",'encoding'=>$db_charset));
	$Rss->channel($channel);
	$Rss->image($image);

	$pw_posts = GetPtable($rs['ptable']);
	$query = $db->query("SELECT pid,tid,subject,author,postdate,anonymous,content FROM $pw_posts WHERE tid=".S::sqlEscape($tid)." ORDER BY postdate DESC LIMIT $Rss_listnum");
	while ($rt = $db->fetch_array($query)) {
		$rt['content'] = substrs(stripWindCode($rt['content']),300);
		$rt['anonymous'] && $rt['author'] = $db_anonymousname;
		$link = "$db_bbsurl/job.php?action=topost&tid={$rt['tid']}&pid={$rt['pid']}";
		$item = array(
			'title'       =>  $rt['subject'],
			'description' =>  $rt['content'],
			'link'        =>  $link,
			'author'      =>  $rt['author'],
			'category'    =>  strip_tags($rs['name']),
			'pubdate'     =>  date('r',$rt['postdate']),
		);
		$Rss->item($item);
	}
	$all = $Rss->rssHeader;
	$all .= $Rss->rssChannel;
	$all .= $Rss->rssImage;
	$all .= $Rss->rssItem;
	$all .= "</channel></rss>";
	header("Content-type: application/xml");
	echo $all;exit;
} else {
	$cache_path = D_P.'data/bbscache/rss_'.$fid.'_cache.xml';
	if ($timestamp-pwFilemtime($cache_path) > $Rss_updatetime*60) {
		L::loadClass('rss', 'utility', false);
		//* require_once pwCache::getPath(D_P.'data/bbscache/forum_cache.php');
		pwCache::getData(D_P.'data/bbscache/forum_cache.php');
		if ($db_tlist) {
			@extract($db->get_one("SELECT MAX(tid) AS tid FROM pw_threads"));
			$pw_tmsgs = GetTtable($tid);
		} else {
			$pw_tmsgs = 'pw_tmsgs';
		}
		if ($fid) {
			$rt = $db->get_one("SELECT allowvisit,f_type FROM pw_forums WHERE fid=".S::sqlEscape($fid));
			if (!$rt || $rt['allowvisit'] != '' || $rt['f_type'] == 'hidden') {
				echo"<META HTTP-EQUIV='Refresh' CONTENT='0; URL=rss.php'>";exit;
			}
		}
		$sql = $forceindex = '';
		if ($fid) {
			$description = "Latest $Rss_newnum article of ".$forum[$fid]['name'];
			$sql = "WHERE t.fid=".S::sqlEscape($fid)."AND ifcheck=1 AND topped='0' AND lastpost>".S::sqlEscape($timestamp-604800)." ORDER BY lastpost DESC LIMIT $Rss_listnum";
		} else {
			$fids  = $extra = '';
			$query = $db->query("SELECT fid FROM pw_forums WHERE allowvisit='' AND f_type!='hidden'");
			while ($rt = $db->fetch_array($query)) {
				$fids .= $extra."'".$rt['fid']."'";
				$extra = ',';
			}
			$description = "Latest $Rss_newnum article of all forums";
			if ($fids) {
				$sql = "WHERE fid IN($fids) AND ifcheck=1 AND topped='0' AND postdate>".S::sqlEscape($timestamp-604800)." ORDER BY postdate DESC LIMIT $Rss_newnum";
				$forceindex = 'FORCE INDEX ('.getForceIndex('idx_postdate').')';
			}
		}
		$channel = array(
			'title'			=>  $db_bbsname,
			'link'			=>  $db_bbsurl,
			'description'	=>  $description,
			'copyright'		=>  "Copyright(C) $db_bbsname",
			'generator'		=>  "phpwind forums by phpwind studio",
			'lastBuildDate' =>  date('r'),
		);
		$image = array(
			'url'		  =>  "$imgpath/$stylepath/rss.gif",
			'title'		  =>  'phpwind board',
			'link'		  =>  $db_bbsurl,
			'description' =>  $db_bbsname,
		);
		$Rss = new Rss(array('xml'=>"1.0",'rss'=>"2.0",'encoding'=>$db_charset));
		$Rss->channel($channel);
		$Rss->image($image);
		if ($sql) {
			$query = $db->query("SELECT t.tid,t.fid,t.subject,t.author,t.postdate,t.anonymous,tm.content FROM pw_threads t $forceindex RIGHT JOIN $pw_tmsgs tm ON tm.tid=t.tid $sql");
			while ($rt = $db->fetch_array($query)) {
				$rt['content'] = substrs(stripWindCode($rt['content']),300);
				$rt['anonymous'] && $rt['author'] = $db_anonymousname;
				if ($db_htmifopen) {
					$link = "$db_bbsurl/read{$db_dir}tid-{$rt['tid']}$db_ext";
				} else {
					$link = "$db_bbsurl/read.php?tid={$rt['tid']}";
				}
				$item = array(
					'title'       => $rt['subject'],
					'description' => $rt['content'],
					'link'        => $link,
					'author'      => $rt['author'],
					'category'    => $forum[$rt['fid']]['name'],
					'pubdate'     => date('r',$rt['postdate']),
				);
				$Rss->item($item);
			}
		}
		$Rss->generate($cache_path);
	}
	header("Content-type: application/xml");
	@readfile($cache_path);
	exit;
}
?>