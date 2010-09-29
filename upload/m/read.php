<?php
require_once('wap_global.php');

if($tid){
	$pw_tmsgs = GetTtable($tid);
	$rt = $db->get_one("SELECT t.fid,t.tid,t.subject,t.author,t.replies,t.locked,t.postdate,t.anonymous,t.ptable,tm.content,t.tpcstatus FROM pw_threads t LEFT JOIN $pw_tmsgs tm ON tm.tid=t.tid WHERE t.tid=".pwEscape($tid)." AND ifcheck=1");
	if($rt['locked']==2){
		wap_msg('read_locked');
	}
	if(!$rt){
		wap_msg('illegal_tid');
	}
	$fid = $rt['fid'];
	$openIndex 	= getstatus($rt['tpcstatus'], 2);	#高楼是否开启
	forumcheck($fid,'read');
	InitGP(array('page'));
	$page == 'e' && $page=65535;
	$per = 5;
	(int)$page<1 && $page=1;
	if ($openIndex) {
		$count = $db->get_value("SELECT COUNT(*) FROM pw_postsfloor WHERE tid =". pwEscape($rt['tid'])) . " LIMIT 1";
	}else{
		$count = $rt['replies'];
	}
	$totle=ceil($count/$per);
	$totle==0 ? $page=1 : ($page > $totle ? $page=$totle : '');
	$pages = wap_numofpage($page,$totle,"read.php?tid=$tid&amp;");
	$rt['subject']  = str_replace('&nbsp;','',wap_cv($rt['subject']));
	if($page==1){
		$rt['content']	= strip_tags($rt['content']);
		$rt['content']  = substrs($rt['content'],$db_waplimit);
		$rt['content']  = wap_cv($rt['content']);
		$rt['content']  = wap_code($rt['content']);
		$rt['postdate']	= get_date($rt['postdate']);
		$rt['author']   = $rt['anonymous'] ? $db_anonymousname : $rt['author'];
		$rt['author']   = wap_cv($rt['author']);
	}

	$satrt=($page-1)*$per;
	$id=$satrt;
	$limit=pwLimit($satrt,$per);
	$posts='';
	$pw_posts = GetPtable($rt['ptable']);
	
	#高楼索引优化
	if ($openIndex) {
		$start_limit = (int)($page-1)*$per-1;
		$start_limit < 0 && $start_limit = 0;
		$end = $start_limit + $per;
		$sql_floor = " AND f.floor > " . $start_limit ." AND f.floor <= ".$end." ";
		$query = $db->query("SELECT f.pid FROM pw_postsfloor f WHERE f.tid = ". pwEscape($rt['tid']) ." $sql_floor ORDER BY f.floor");
		while ($r = $db->fetch_array($query)) {
			$postIds[] = $r['pid'];
		}
		if ($postIds) {
			$postIds && $sql_postId = " AND pid IN ( ". pwImplode($postIds,false) ." ) ";
			$query = $db->query("SELECT pid,ifcheck,subject,author,content,postdate,anonymous 
				FROM $pw_posts WHERE tid=".pwEscape($rt[tid])." $sql_postId ORDER BY postdate ");
			while ($read = $db->fetch_array($query)) {
				if ($read['ifcheck']!='1') {
					$read['subject'] = '';
					$read['content'] = getLangInfo('bbscode','post_unchecked');
				}
				$currentPostsId[] = $read['pid'];
				$currentPosts[$read['pid']] = $read;
			}
			foreach ($postIds as $key => $value) {
				if (in_array($value,$currentPostsId)) {
					$id++;
					$currentPosts[$value]['subject']  = str_replace('&nbsp;','',wap_cv($currentPosts[$value]['subject']));
					$currentPosts[$value]['content']	= strip_tags($currentPosts[$value]['content']);
					$currentPosts[$value]['content']	= substrs($currentPosts[$value]['content'],$db_waplimit);
					$currentPosts[$value]['content']  = wap_cv($currentPosts[$value]['content']);
					$currentPosts[$value]['content']  = wap_code($currentPosts[$value]['content']);
					$currentPosts[$value]['postdate']	= get_date($currentPosts[$value]['postdate'],"m-d H:i");
					$currentPosts[$value]['id']		= $id;
					$currentPosts[$value]['author']   = $currentPosts[$value]['anonymous'] ? $db_anonymousname : $currentPosts[$value]['author'];
					$currentPosts[$value]['author']   = wap_cv($currentPosts[$value]['author']);
					$postdb[]	    = $currentPosts[$value];
				}else{
					$postdb[] = array('postdate'=>'N','content'=>getLangInfo('bbscode','post_deleted'));
				}
			}
		}
	}else{
		$query=$db->query("SELECT subject,author,content,postdate,anonymous FROM $pw_posts WHERE tid=".pwEscape($rt[tid])." AND ifcheck=1 ORDER BY postdate $limit");
		while($ct=$db->fetch_array($query)){
			if($ct['content']){
				$id++;
				$ct['subject']  = str_replace('&nbsp;','',wap_cv($ct['subject']));
				$ct['content']	= strip_tags($ct['content']);
				$ct['content']	= substrs($ct['content'],$db_waplimit);
				$ct['content']  = wap_cv($ct['content']);
				$ct['content']  = wap_code($ct['content']);
				$ct['postdate']	= get_date($ct['postdate'],"m-d H:i");
				$ct['id']		= $id;
				$ct['author']   = $ct['anonymous'] ? $db_anonymousname : $ct['author'];
				$ct['author']   = wap_cv($ct['author']);
				$postdb[]		= $ct;
			}
		}
	}
	
} else{
	wap_msg('illegal_tid');
}
wap_header('read',$db_bbsname);
require_once PrintEot('wap_read');
wap_footer();
?>