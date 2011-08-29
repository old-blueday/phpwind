<?php
!function_exists('adminmsg') && exit('Forbidden');

$basename="$admin_file?adminjob=attachrenew";
($attach_url || $db_ifftp) && adminmsg('attachrenew_forbidden');
S::gp(array('action'));
if (empty($action)) {
	include PrintEot('attachrenew');exit;
} elseif ('delete' == $action) {
	S::gp(array('step'));
	$step	= $step ? (int) $step : 0;
	$prenum = 10000;
	$attachDB = L::loadDB('attachs', 'forum');
	$attachs = $attachDB->groupByTidAndPid($step,$prenum);
	$tTables = $pTables = array();
	foreach ($attachs as $key=>$value) {
		if ($value['pid']) {
			$pTable = GetPtable('N',$value['tid']);
			$pTables[$pTable][] = $value['pid'];
		} else {
			$tTable = GetTtable($value['tid']);
			$tTables[$tTable][] = $value['tid'];
		}
	}
	foreach ($tTables as $table=>$value) {
		$db->update("UPDATE $table SET aid=1 WHERE tid IN (".S::sqlImplode($value).")");
	}
	foreach ($pTables as $table=>$value) {
		$db->update("UPDATE $table SET aid=1 WHERE pid IN (".S::sqlImplode($value).")");
	}

	$maxAid = $attachDB->getTableStructs('Auto_increment');
	if ($maxAid>($step+1)*$prenum) {
		$step++;
		adminmsg('attach_renew_wait',EncodeUrl("$basename&action=$action&step=$step"),1);
	} else {
		adminmsg('attach_renew');
	}
} elseif ('delattach' == $action) {
	$pwServer['REQUEST_METHOD']!='POST' && PostCheck($verify);
	S::gp(array('pernum','start','deltotal'));
	if(!$start){
		$start=0;
		$deltotal=0;
	}
	$num	= 0;
	$delnum	= 0;
	!$pernum && $pernum = 1000;
	$dir1 = opendir($attachdir);
	$whiteDir = array('upload','photo','cn_img','forumlogo','cms_article','diary','house','pushpic','stopic','products','salepic','salethumbpic','idcard');
	foreach ($db_modes as $key=>$value) {
		if (!in_array($key,$whiteDir)) $whiteDir[] = $key;
	}
	while(false !== ($file1 = readdir($dir1))){
		if($file1!='' && $file1!='.' && $file1!='..' && !eregi("\.html$",$file1)){
			if(is_dir("$attachdir/$file1")){
				if(in_array($file1,$whiteDir)){
					continue;
				}
				$dir2 = opendir("$attachdir/$file1");
				while(false !==($file2=readdir($dir2))){
					if(is_file("$attachdir/$file1/$file2") && $file2!='' && $file2!='.' && $file2!='..' && !eregi("\.html$",$file2)){
						$num++;
						if($num > $start){
							$rt = $db->get_one("SELECT aid,ifthumb FROM pw_attachs WHERE attachurl=".S::sqlEscape("$file1/$file2"));
							if(!$rt){
								$delnum++;
								$deltotal++;
								P_unlink("$attachdir/$file1/$file2");
								P_unlink("$attachdir/thumb/$file1/$file2");
							}
							if($num-$start >= $pernum){
								$start = $num-$delnum;
								$j_url = "$basename&action=$action&start=$start&pernum=$pernum&deltotal=$deltotal";
								adminmsg('delattach_step',EncodeUrl($j_url),0);
							}
						}
					}
				}
			} elseif(is_file("$attachdir/$file1")){
				$num++;
				if($num > $start){
					$rt = $db->get_one("SELECT aid,ifthumb FROM pw_attachs WHERE attachurl=".S::sqlEscape($file1));
					if(!$rt){
						$delnum++;
						$deltotal++;
						P_unlink("$attachdir/$file1");
						P_unlink("$attachdir/thumb/$file1");
					}
					if($num-$start>=$pernum){
						$start = $num-$delnum;
						$j_url = "$basename&action=$action&start=$start&pernum=$pernum&deltotal=$deltotal";
						adminmsg('delattach_step',EncodeUrl($j_url),0);
					}
				}
			}
		}
	}
	adminmsg('operate_success');
}
?>