<?php
!function_exists('adminmsg') && exit('Forbidden');

//* include pwCache::getPath(D_P.'data/bbscache/forum_cache.php');
pwCache::getData(D_P.'data/bbscache/forum_cache.php');

$basename = "$admin_file?adminjob=attachstats";

if(empty($action)){
	$rs = $db->get_one("SELECT COUNT(*) AS count FROM pw_attachs");
	$A_count = $rs['count'];
	$rs = $db->get_one("SELECT SUM(size) AS size FROM pw_attachs");
	$A_size  = $rs['size'] ? $rs['size'] : 0;
	$average = $A_count ? number_format($A_size/$A_count,1) : 0;
	$rs = $db->get_one("SELECT SUM(hits) AS hits FROM pw_attachs");
	$A_hits  = $rs['hits'] ? $rs['hits'] : 0;

	$M_hits = $M_size = array();
	$query  = $db->query("SELECT * FROM pw_attachs ORDER BY hits DESC LIMIT 20");
	while($attach=$db->fetch_array($query)){
		$attach['url'] = $attach['attachurl'];
		$attach['imgurl'] = geturl($attach['attachurl'],'show');
		$attach['imgurl'] = is_array($attach['imgurl']) ? $attach['imgurl'][0] : '';
		$attach['forum'] = strip_tags($forum[$attach['fid']]['name']);
		$M_hits[] = $attach;
	}
	$query = $db->query("SELECT * FROM pw_attachs ORDER BY size DESC LIMIT 20");
	while($attach=$db->fetch_array($query)){
		$attach['url'] = $attach['attachurl'];
		$attach['imgurl'] = geturl($attach['attachurl'],'show');
		$attach['imgurl'] = is_array($attach['imgurl']) ? $attach['imgurl'][0] : '';
		$attach['forum'] = strip_tags($forum[$attach['fid']]['name']);
		$M_size[] = $attach;
	}

	include PrintEot('attachstats');exit;
} elseif($_POST['action']=='delete') {
	$adminmsg = '';
	$count = $delnum = 0;
	S::gp(array('urlarray','F_basename'),'P');
	if (!empty($urlarray)) {
		$delname = '';
		$count = count($urlarray);
		$adminmsg .= '共删除'.$count.'条记录';
		foreach ($urlarray as $key => $value) {
			if (P_unlink($attachdir.'/'.$value)) {
				P_unlink($attachdir.'/thumb/'.$value);
				$delnum++;
				$delname .= $value.'<br />';
			}
			$db->update("DELETE FROM pw_attachs WHERE aid=".S::sqlEscape($key,false));
		}
		$delnum>0 && $adminmsg .= '，'.$delnum.'个附件。<br />已删除的文件; <br />'.$delname;
	}
	$F_basename && $basename = $F_basename;
	!$adminmsg && $adminmsg = 'operate_error';
	adminmsg($adminmsg);
}
/*function getallfile($filedir){
	global $attach;
	$dir=opendir($filedir.'/');
	while($file=readdir($dir)){
		if(($file!=".") && ($file!="..") && ($file!="") && (strpos($file,'html')===false)){
			if (is_dir($filedir.'/'.$file)){
				getallfile($filedir.'/'.$file);
			}else{


				if(substr(strrchr($filedir,'/'),1)){
					$file = substr(strrchr($filedir,'/'),1).'/'.$file;
				}
				$attach[]=$file;
				//$count++;
				//if($count>1000)return;
			}
		}
	}closedir($dir);
}*/
?>