<?php
require_once('global.php');
@header("Content-type: application/xml");
$for_google = S::getGP('g');
if ($for_google) {
	$cachefile = D_P."data/bbscache/sitemap_google.xml";
} else {
	$cachefile = D_P."sitemap.xml";
}
//* @include_once pwCache::getPath(D_P.'data/bbscache/sm_config.php');
pwCache::getData(D_P.'data/bbscache/sm_config.php');
!$sm_updatePeri && $sm_updatePeri = 12;

if ($timestamp-pwFilemtime($cachefile)>=$sm_updatePeri*3600) {
	$sm_num < 1 && $sm_num = 1000;
	if ($db_tlist) {
		$rt = $db->get_one("SELECT MAX(tid) AS mtid FROM pw_threads");
		$pw_tmsgs = GetTtable($rt['mtid']);
	} else {
		$pw_tmsgs = 'pw_tmsgs';
	}
	$fidoff = array('0');
	$query = $db->query("SELECT fid,allowvisit,password,f_type,forumsell FROM pw_forums WHERE type<>'category'");
	while ($rt = $db->fetch_array($query)) {
		if ($rt['f_type'] == 'hidden' || $rt['password'] || $rt['forumsell'] || $rt['allowvisit']) {
			$fidoff[] = $rt['fid'];
		}
	}
	$sql = $fidoff ? ' fid NOT IN(' . S::sqlImplode($fidoff) . ')' : '1';
	$query = $db->query("SELECT t.tid,t.fid,t.subject,t.postdate,t.lastpost,t.hits,t.replies,t.digest,tm.content FROM pw_threads t FORCE INDEX (".getForceIndex('idx_postdate').") LEFT JOIN $pw_tmsgs tm ON t.tid=tm.tid WHERE $sql ORDER BY t.postdate DESC LIMIT $sm_num");
	while ($rt = $db->fetch_array($query)) {
		if ($db_htmifopen) {
			$link = "$db_bbsurl/read{$db_dir}tid-{$rt['tid']}$db_ext";
		} else {
			$link = "$db_bbsurl/read.php?tid={$rt['tid']}";
		}
		if ($for_google) {
			$mapinfo .= "\t<url>\r\n\t\t<loc>$link</loc>\r\n\t\t<lastmod>".get_date($rt['lastpost'])."</lastmod>\r\n\t\t<changefreq>daily</changefreq>\r\n\t\t<priority>0.5</priority>\r\n\t</url>\r\n";
		} else {
			$mapinfo .= "\t<item>\r\n\t\t<link>$link</link>\r\n\t\t<title>".str_replace('&','&amp;',$rt['subject'])."</title>\r\n\t\t<pubDate>".get_date($rt['postdate'])."</pubDate>\r\n\t\t<bbs:lastDate>".get_date($rt['lastpost'])."</bbs:lastDate>\r\n\t\t<bbs:reply>$rt[replies]</bbs:reply>\r\n\t\t<bbs:hit>$rt[hits]</bbs:hit>\r\n\t\t<bbs:mainLen>".strlen($rt['content'])."</bbs:mainLen>\r\n\t\t<bbs:boardid>$rt[fid]</bbs:boardid>\r\n\t\t<bbs:pick>$rt[digest]</bbs:pick>\r\n\t</item>\r\n";
		}
	}

	$db_charset == 'gbk' && $db_charset = 'GB2312';
	if ($for_google) {
		$mapinfo = "<urlset xmlns=\"http://www.google.com/schemas/sitemap/0.84\">\r\n".$mapinfo."</urlset>";
	} else {
		$mapinfo = "<?xml version =\"1.0\" encoding=\"{$db_charset}\"?>\r\n<document xmlns:bbs=\"http://www.baidu.com/search/bbs_sitemap.xsd\">\r\n\t<webSite>$db_bbsurl</webSite>\r\n\t<webMaster>$db_ceoemail</webMaster>\r\n\t<updatePeri>$sm_updatePeri</updatePeri>\r\n\t<updatetime>".get_date($timestamp)."</updatetime>\r\n\t<version>phpwind $wind_version Certificate</version>\r\n".$mapinfo."</document>";
	}
	pwCache::writeover($cachefile,$mapinfo);
	echo $mapinfo;
} else {
	readfile($cachefile);
}
?>