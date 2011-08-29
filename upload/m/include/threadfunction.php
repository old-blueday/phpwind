<?php
!defined('W_P') && exit('Forbidden');

function viewContent($s) {
	$s = wap_cv($s);
	$s = wap_code($s);
	$s = strip_tags($s,"<p><br/>");
	$s = wap_cv_after($s);
	return $s;
}

function viewAids($tid, $pid) {
	global $db, $db_windpost;
	$sql = "SELECT * FROM pw_attachs WHERE tid=" . pwEscape($tid) . " AND type='img' AND pid=" . pwEscape($pid) . " AND special=0 AND needrvrc=0";
	$query = $db->query($sql);
	$s = "";
	$imgs = array();
	while ($ct = $db->fetch_array($query)) {
		$a_url = geturl($ct['attachurl'], 'show', true);
		$dfurl = '<br>' . wap_cvpic($a_url[0], 1);
		$ct['descrip'] = str_replace('&nbsp;', '', wap_cv($ct['descrip']));
		$ct['img'] = $dfurl;
		$imgs[] = $ct;
	}
	return $imgs;
}

function viewDownloads($tid, $pid) {
	global $db;
	$sql = "SELECT * FROM pw_attachs WHERE tid=" . pwEscape($tid) . " AND type<>'img' AND pid=" . pwEscape($pid);
	$query = $db->query($sql);
	$s = "";
	$d = array();
	while ($ct = $db->fetch_array($query)) {
		$ct['descrip'] = str_replace('&nbsp;', '', wap_cv($ct['descrip']));
		$d[] = $ct;
	}
	return $d;
}

function viewReply($tid, $page, $replies, $per, $max, $ptable, $order) {
	global $db,$db_shield;
	$page == 'e' && $page = 65535;
	( int ) $page < 1 && $page = 1;
	$total = ceil ( $replies / $per );
	$total == 0 ? $page = 1 : ($page > $total ? $page = $total : '');

	$satrt = ($page - 1) * $per;
	$id = $satrt;
	$limit = pwLimit ( $satrt, $per );
	$posts = '';
	$pw_posts = GetPtable ( $ptable );
	$sql = "SELECT p.aid,p.pid,p.subject,p.author,p.authorid,p.content,p.postdate,p.anonymous,p.ifshield,m.groupid 
			FROM $pw_posts p LEFT JOIN pw_members m ON m.uid = p.authorid
			WHERE tid=" . pwEscape ( $tid ) . " 
			AND ifcheck=1 ORDER BY postdate $limit";
	if ($order == 2) {
		$sql = "SELECT p.aid,p.pid,p.subject,p.author,p.authorid,p.content,p.postdate,p.anonymous,p.ifshield,m.groupid 
				FROM $pw_posts p LEFT JOIN pw_members m ON m.uid = p.authorid
				WHERE tid=" . pwEscape ( $tid ) . " 
				AND ifcheck=1 ORDER BY postdate desc $limit";
	}

	$query = $db->query ( $sql );
	while ( $ct = $db->fetch_array ( $query ) ) {
		if ($ct ['content']) {
			$id ++;
			if ($ct['ifshield'] || $ct['groupid'] == 6 && $db_shield) {
				if ($ct['ifshield'] == 2) {
					$ct['content'] = shield('shield_del_article');
					$ct['subject'] = '';
					$tpc_shield = 1;
				} else {
					$ct['content'] = shield($ct['ifshield'] ? 'shield_article' : 'ban_article');
					$ct['subject'] = '';
					$tpc_shield = 1;
				}
			}
			$ct ['subject'] = str_replace ( '&nbsp;', '', wap_cv ( $ct ['subject'] ) );

			$ct ['content'] = replySubject ( $ct ['content'] );

			list(,$ct ['postdate']) = getLastDate($ct ['postdate']);
			$ct ['id'] = $id;
			if ($order == 2)
				$ct ['id'] = $replies - $id + 1;
			
			if ($ct['anonymous'] && $ct['authorid']!=$winduid) {
				$ct['author']	= $db_anonymousname;
				$ct['authorid'] = 0;
			}
			$ct ['author'] = wap_cv ( $ct ['author'] );
			$postdb [] = $ct;
		}
	}
	return $postdb;
}

function viewAidsForHtml($tid, $pid) {
	global $db;
	$sql = "select name,attachurl,descrip from pw_attachs where tid=" . pwEscape ( $tid ) . " AND type='img' and pid=" . pwEscape ( $pid ) . " and special=0 and needrvrc=0";
	$query = $db->query ( $sql );
	$s = "";
	while ( $ct = $db->fetch_array ( $query ) ) {
		$ct ['descrip'] = str_replace ( '&nbsp;', '', wap_cv ( $ct ['descrip'] ) );
		if ($ct ['descrip'] && $ct ['descrip'] != ''){
			$s = $s . $ct ['descrip'] . "<br/>";
		}
		$url = geturl($ct['attachurl'], 'show');
		$thumburl = '<br>' . wap_cvpic($url[0], 1);
		$s .=  $thumburl ."<br/>";
	}
	return $s;
}


function viewOneReply($tid, $pid, $ptable) {
	global $db, $db_waplimit, $c_page,$db_anonymousname,$pwAnonyHide,$winduid;
	$pw_posts = GetPtable ( $ptable );
	$sql = "SELECT pid,subject,author,authorid,content,postdate,anonymous,aid FROM $pw_posts WHERE pid=" . pwEscape ( $pid );
	$ct = $db->get_one ( $sql );
	if ($ct) {
		$ct ['subject'] = str_replace ( '&nbsp;', '', wap_cv ( $ct ['subject'] ) );
		$content = viewContent ( $ct ['content'] );
		$yxqw = "";

		/*************对内容进行分页**********/
		(int) $c_page < 1 && $c_page = 1;
		$clen = wap_strlen($content,$db_charset); //TODO mbstring
		$maxp = ceil($clen / $db_waplimit);
		$c_nextp = $c_page + 1;
		$c_prep = $c_page - 1;
		if ($c_nextp > $maxp) $c_nextp = $maxp;
		if	($c_prep <= 0 ) $c_prep = 1;
		$yxqw = "";
		if ($maxp > 1) {
			$content = wap_substr($content, $db_waplimit*($c_page-1),$db_waplimit,$db_charset);
			$content = wap_img2($content);
			if(empty($content)){
				wap_msg("已到最后一页","index.php?a=read&tid=$tid");
			}
			if($c_page == 1 ){
				$yxqw = "<a href='index.php?a=reply&pid=" . $pid . "&amp;tid=".$tid."&amp;c_page=$c_nextp'>下一页</a>";
			}elseif($c_page == $maxp){
				$yxqw = "<a href='index.php?a=reply&pid=" . $pid . "&amp;tid=".$tid."&amp;c_page=$c_prep'>上一页</a>&nbsp;";
			}else{
				$yxqw = "<a href='index.php?a=reply&pid=" . $pid . "&amp;tid=".$tid."&amp;c_page=$c_nextp'>下一页</a>";
				$yxqw .= "<a href='index.php?a=reply&pid=" . $pid . "&amp;tid=".$tid."&amp;c_page=$c_prep'>上一页</a>&nbsp;";
			}
			$yxqw .= "&nbsp;({$c_page}/{$maxp})<br/>";
		}else{
			$content = wap_img2($content);
		}
		$ct ['content'] = $content;
		/*************对内容进行分页**********/
		
		if ($ct['anonymous'] && $ct['authorid']!=$winduid && !$pwAnonyHide) {
			$ct['author']	= $db_anonymousname;
			$ct['authorid'] = 0;
		}
		
		list(,$ct ['postdate']) = getLastDate($ct ['postdate']);
		$ct ['id'] = $id;
		//$ct ['author'] = $ct ['anonymous'] ? $db_anonymousname : $ct ['author'];
		$ct ['author'] = wap_cv ( $ct ['author'] );
		$ct ['yxqw'] = $yxqw;
		if ($ct ['aid'] && $ct ['aid'] != '') {
			$ct ['aidimgs'] = viewAidsForHtml ( $tid, $pid );
			$ct ['aidatts'] = viewDownloads($tid,$pid);
		} else {
			$ct ['aidimgs'] = '';
			$ct ['aidatts'] = '';
		}
	}

	return $ct;
}

function nextReply($tid, $pid, $ptable, $order) {
	global $db;
	$pw_posts = GetPtable ( $ptable );

	if ($order == 1) {
		$sql = "SELECT pid,content,author,authorid,content,postdate,anonymous FROM $pw_posts WHERE tid=" . pwEscape ( $tid ) . " AND ifcheck=1 and pid>" . pwEscape ( $pid ) . " ORDER BY postdate limit 1";
	} else {
		$sql = "SELECT pid,subject,author,authorid,content,postdate,anonymous FROM $pw_posts WHERE tid=" . pwEscape ( $tid ) . " AND ifcheck=1 and pid<" . pwEscape ( $pid ) . " ORDER BY postdate desc limit 1";
	}
	$ct = $db->get_one ( $sql );
	if ($ct) {
		$ct ['content'] = replySubject ( $ct ['content'] );
	} else {
		$ct = array ("pid" => 0, "content" => "" );
	}
	return $ct;
}