<?php
!function_exists('adminmsg') && exit('Forbidden');
@set_time_limit(0);
$db_perpage=50;
$basename="$admin_file?adminjob=attachment";

//* include pwCache::getPath(D_P.'data/bbscache/forum_cache.php');
pwCache::getData(D_P.'data/bbscache/forum_cache.php');

if($admin_gid == 5){
	list($allowfid,$forumcache) = GetAllowForum($admin_name);
	$sql = $allowfid ? "fid IN($allowfid)" : '0';
} else{
	//* include pwCache::getPath(D_P.'data/bbscache/forumcache.php');
	pwCache::getData(D_P.'data/bbscache/forumcache.php');
	list($hidefid,$hideforum) = GetHiddenForum();
	if($admin_gid == 3){
		$forumcache .= $hideforum;
		$sql = '1';
	} else{
		$sql = $hidefid ? "fid NOT IN($hidefid)" : '1';
	}
}

if(empty($action)){
	S::gp(array('fid','username','uid','filename','hits','ifmore','filesize','ifless','postdate1','postdate2','orderway','asc','pernum','page'));

	$forumcache = str_replace("<option value=\"$fid\">","<option value=\"$fid\" selected>",$forumcache);
	$hitsMoreThan = '0' == $ifmore ? 'checked' : '';
	$hitsLessThan = !$hitsMoreThan ? 'checked' : '';
	$downloadMoreThan = '0' == $ifless ? 'checked' : '';
	$downloadLessThan = !$downloadMoreThan ? 'checked' : '';
	$ascChecked = 'ASC' == $asc ? 'checked' : '';
	$descChecked = !$ascChecked ? 'checked' : '';

	$orderwaySelection = array(
		'uploadtime'=>'上传时间',
		'size'=>'附件大小',
		'needrvrc'=>'所需威望',
		'name'=>'附件名',
		'hits'=>'下载次数',
	);
	$orderwaySelection = formSelect('orderway', $orderway, $orderwaySelection, 'class="select_wa mr20 fl"');

	'' == $postdate2 && $postdate2 = get_date($timestamp + 86400,'Y-m-d');
	'' == $postdate1 && $postdate1 = get_date($timestamp - 90 * 86400,'Y-m-d');

	if(is_numeric($fid)){
		$sql .= " AND fid=".S::sqlEscape($fid);
	}
	$username = trim($username);
	if($username){
		$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
		$uid = $userService->getUserIdByUserName($username);
	}
	is_numeric($uid) && $sql .= " AND uid=".S::sqlEscape($uid,false);

	$filename = trim($filename);
	if($filename!=''){
		$filename = str_replace('*','%',$filename);
		$sql .= " AND (name LIKE ".S::sqlEscape("%$filename%").")";
	}
	if($hits){
		if($ifmore){
			$sql.=" AND (hits<".S::sqlEscape($hits).')';
		} else{
			$sql.=" AND (hits>".S::sqlEscape($hits).')';
		}
	}
	if($filesize){
		if($ifless){
			$sql.=" AND (size<".S::sqlEscape($filesize).')';
		} else{
			$sql.=" AND (size>".S::sqlEscape($filesize).')';
		}
	}
	if($postdate1){
		$uploadtime = PwStrtoTime($postdate1);
		is_numeric($uploadtime) && $sql.=" AND uploadtime>".S::sqlEscape($uploadtime);
	}
	if($postdate2){
		$uploadtime = PwStrtoTime($postdate2);
		is_numeric($uploadtime) && $sql.=" AND uploadtime<".S::sqlEscape($uploadtime);
	}

	if(S::inArray($orderway,array('uploadtime','size','needrvrc','name','hits'))){
		$order = "ORDER BY $orderway";
		$asc=='DESC' && $order.=' '.$asc;
	} else{
		$order = '';
	}
	$pernum=is_numeric($pernum) ? $pernum : 20;
	$page < 1 && $page=1;
	$limit = S::sqlLimit(($page-1)*$pernum,$pernum);

	$rt=$db->get_one("SELECT COUNT(*) AS count FROM pw_attachs WHERE $sql");
	$sum=$rt['count'];
	$numofpage=ceil($sum/$pernum);
	$pages=numofpage($sum,$page,$numofpage,"$basename&fid=$fid&uid=$uid&filename=".rawurlencode($filename)."&hits=$hits&ifmore=$ifmore&filesize=$filesize&ifless=$ifless&orderway=$orderway&asc=$asc&postdate1=$postdate1&postdate2=$postdate2&pernum=$pernum&");

	$attachdb=$thread=array();
	$query=$db->query("SELECT * FROM pw_attachs WHERE $sql $order $limit");
	$searchHits = $hits;
	$searchFid = $fid;
	$searchUid = $uid;
	while(@extract($db->fetch_array($query))){
		if($_POST['direct']){
			if(file_exists("$attachdir/$attachurl")){
				P_unlink("$attachdir/$attachurl");
				$ifthumb && P_unlink("$attachdir/thumb/$attachurl");
			}
		} else{
			$thread['url']=$attachurl;
			$did && $attachurl = "diary/$attachurl";
			$thread['imgurl'] = geturl($attachurl,'show');
			$thread['imgurl'] = is_array($thread['imgurl']) ? $thread['imgurl'][0] : '';
			$thread['name']=$name;
			$thread['aid']=$aid;
			$thread['tid']=$tid;
			$thread['where']="thread.php?fid=$fid";
			$thread['forum']=$forum[$fid]['name'];
			$thread['filezie']=$size;
			$thread['uploadtime']=get_date($uploadtime);
			$attachdb[]=$thread;
		}
	}
	//if($_POST['direct']){
	//	$db->update("DELETE FROM pw_attachs WHERE $sql".S::sqlLimit($pernum));
	//	adminmsg('operate_success');
	//} else{
	$filename = str_replace('%','*',$filename);
	$hits = $searchHits ? $searchHits : '';
	$fid = $searchFid;
	$uid = $searchUid;

	include PrintEot('attachment');exit;
	//}

} elseif($action=='schdir'){

	S::gp(array('filename','filesize','ifless','postdate1','postdate2','pernum','direct','start'));

	if(!$filename && !$filesize && !$postdate1 && !$postdate2){
		adminmsg('noenough_condition');
	}
	$cache_file = D_P."data/bbscache/att_".substr(md5($admin_name),10,10).".txt";
	if(!$start){
		$start = 0;
		if(file_exists($cache_file)){
			//* P_unlink($cache_file);
			pwCache::deleteData($cache_file);

		}
	}
	$num = 0;
	!$pernum && $pernum = 1000;
	$dir1 = opendir($attachdir);
	while(false !== ($file1 = readdir($dir1))){
		if($file1!='' && $file1!='.' && $file1!='..' && !eregi("\.html$",$file1)){
			if(is_dir("$attachdir/$file1")){
				$dir2 = opendir("$attachdir/$file1");
				while(false !==($file2=readdir($dir2))){
					if(is_file("$attachdir/$file1/$file2") && $file2!='' && $file2!='.' && $file2!='..' && !eregi("\.html$",$file2)){
						$num++;
						if($num > $start){
							attachcheck("$file1/$file2");
							if($num-$start>=$pernum){
								if($direct){
									adminmsg('attach_delfile');
								} else{
									adminmsg('attach_step',"$basename&action=$action&filename=$filename&filesize=$filesize&ifless=$ifless&postdate1=$postdate1&postdate2=$postdate2&start=$num&pernum=$pernum&direct=$direct",0);
								}
							}
						}
					}
				}
			} elseif(is_file("$attachdir/$file1")){
				$num++;
				if($num > $start){
					attachcheck("$file1");
					if($num-$start>=$pernum){
						if($direct){
							adminmsg('attach_delfile');
						} else{
							adminmsg('attach_step',"$basename&action=$action&filename=$filename&filesize=$filesize&ifless=$ifless&postdate1=$postdate1&postdate2=$postdate2&start=$num&pernum=$pernum&direct=$direct",0);
						}
					}
				}
			}
		}
	}

	adminmsg('attach_success',"$basename&action=files&filename=$filename&filesize=$filesize&ifless=$ifless&postdate1=$postdate1&postdate2=$postdate2&pernum=$pernum",0);
} elseif($action=='files'){
	S::gp(array('filename','filesize','ifless','postdate1','postdate2','pernum','direct','start'));
	S::gp(array('page'),'GP',2);

	$sizeMoreThan = '0' == $ifless ? 'checked' : '';
	$sizeLessThan = !$sizeMoreThan ? 'checked' : '';
	'' == $postdate2 && $postdate2 = get_date($timestamp + 86400,'Y-m-d');
	'' == $postdate1 && $postdate1 = get_date($timestamp - 90 * 86400,'Y-m-d');
	$pernum = $pernum ? $pernum : 1000;

	$cache_file = D_P."data/bbscache/att_".substr(md5($admin_name),10,10).".txt";
	$page<1 && $page=1;
	$start=($page-1)*$db_perpage*50;
	$readsize=$db_perpage*50;

	$sum=floor(@filesize($cache_file)/50);
	$numofpage=ceil($sum/$db_perpage);
	$pages=numofpage($sum,$page,$numofpage,"$basename&action=files&");

	if($fp=@fopen($cache_file,"rb")){
		flock($fp,LOCK_SH);
		fseek($fp,$start);
		$readdb=fread($fp,$readsize);
		fclose($fp);
	}
	$readdb=explode("\n",$readdb);
	foreach($readdb as $key => $value){
		$value=trim($value);
		if($value){
			$attach['name']=$value;
			if(file_exists("$attachdir/$value")){
				$attach['size']=round(filesize("$attachdir/$value")/1024,1);
				$attach['time']=get_date(fileatime("$attachdir/$value"));
				$attach['exists']=1;
			} else{
				$attach['size']='-';
				$attach['time']='-';
				$attach['exists']=0;
			}
			$attachdb[]=$attach;
		}
	}
	include PrintEot('attachment');exit;
} elseif($_POST['action']=='delfile'){
	S::gp(array('filename','filesize','ifless','postdate1','postdate2','pernum','direct','start'));
	S::gp(array('delfile'),'P');
	if($delfile){
		foreach($delfile as $key => $value){
			if(file_exists("$attachdir/$value")){
				P_unlink("$attachdir/$value");
				P_unlink("$attachdir/thumb/$value");
			}
		}
	}
	$basename="$admin_file?adminjob=attachment&action=files&filename=$filename&filesize=$filesize&ifless=$ifless&postdate1=$postdate1&postdate2=$postdate2&pernum=$pernum";
	adminmsg('attach_delfile');
} elseif($_POST['action']=='delete'){
	S::gp(array('fid','username','uid','filename','hits','ifmore','filesize','ifless','postdate1','postdate2','orderway','asc','pernum','page'));
	S::gp(array('aidarray'),'P');
	$delnum = $count = 0;
	if($aidarray){
		$count   = count($aidarray);
		$attachs = array();
		foreach($aidarray as $value){
			is_numeric($value) && $attachs[] = $value;
		}
		$attachs = S::sqlImplode($attachs);
		$query   = $db->query("SELECT attachurl FROM pw_attachs WHERE $sql AND aid IN($attachs)");
		while($rs=$db->fetch_array($query)){
			if(P_unlink("$attachdir/$rs[attachurl]")){
				$rs['ifthumb'] && P_unlink("$attachdir/thumb/$rs[attachurl]");
				$delnum ++;
				$delname .= "$rs[attachurl]<br>";
			}
		}
		$db->update("DELETE FROM pw_attachs WHERE $sql AND aid IN($attachs)");
	}
	adminmsg('attachstats_del', "$basename&fid=$fid&uid=$uid&filename=".rawurlencode($filename)."&hits=$hits&ifmore=$ifmore&filesize=$filesize&ifless=$ifless&orderway=$orderway&asc=$asc&postdate1=$postdate1&postdate2=$postdate2&pernum=$pernum&page=$page");
} elseif ($action == 'msgList'){
	S::gp(array('page'),'GP');
	$messageServer = L::loadClass('message', 'message');
	$attachCount = $messageServer->countAllAttachs();
	$pageCount = ceil($attachCount/$db_perpage);
	$page = ($page < 0 || empty($page)) ? 1 : (($page>$pageCount) ? $pageCount : $page);
	$attachList = $messageServer->getAllAttachs($page,$db_perpage);
	$pages = numofpage($attachCount,$page,$pageCount,$basename.'&action=msgList&');
	include PrintEot('attachment');exit;
} elseif ($action == 'msgDel'){
	S::gp(array('mids'),'GP');
	!is_array($mids) && adminmsg('请选择要删除的附件');
	$messageServer = L::loadClass('message', 'message');
	$messageServer->deleteAttachsByMessageIds($mids);
	adminmsg('附件删除成功!',"$basename&action=msgList&");
}

function attachcheck($file){
	global $cache_file,$attachdir,$admin_pwd,$filename,$filesize,$ifless,$postdate1,$postdate2,$direct,$attachdir;

	if($filename && strpos($file,$filename)===false){
		return;
	}
	if($filesize){
		if($ifless && filesize("$attachdir/$file") >= $filesize * 1024){
			return;
		} elseif(!$ifless && filesize("$attachdir/$file") <= $filesize * 1024){
			return;
		}
	}
	if($postdate1){
		$visittime = PwStrtoTime($postdate1);
		if(is_numeric($visittime) && fileatime("$attachdir/$file") < $visittime){
			return;
		}
	}
	if($postdate2){
		$visittime = PwStrtoTime($postdate2);
		if(is_numeric($visittime) && fileatime("$attachdir/$file") > $visittime){
			return;
		}
	}
	if($_POST['direct']){
		P_unlink("$attachdir/$file");
		P_unlink("$attachdir/thumb/$file");
	} else{
		strlen($file)>49 && $file=substr($file,0,49);
		writeover($cache_file,str_pad($file,49)."\n","ab");
		//* pwCache::setData($cache_file,str_pad($file,49)."\n", false, "ab");
	}
}
?>