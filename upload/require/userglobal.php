<?php
!function_exists('readover') && exit('Forbidden');
function checkinline($filename,$offset,$keyword) {
	global $db_olsize;
	if (!$offset || $offset%($db_olsize+1) != 0) return 0;
	$fp = fopen($filename,"rb");
	flock($fp,LOCK_SH);
	fseek($fp,$offset);
	$Checkdata = fread($fp,$db_olsize);
	fclose($fp);
	if (strpos("\n".$Checkdata,"\n".$keyword."\t") !== false) {
		return 1;
	} else {
		return 0;
	}
}
function GetInsertOffset($filename) {
	global $db_olsize,$windid,$onlineip;
	$padfp = 0;
	$N_offset = 0;
	$isModify = 1;
	$fp = fopen($filename,"rb");
	flock($fp,LOCK_SH);
	while (feof($fp) === false) {
		$Checkdata = fread($fp,($db_olsize+1)*2000);
		if ($windid && $offset = strpos("\n".$Checkdata,"\n".$windid."\t")) {
			//$windid通过安全验证
			$isModify = 0;
			$offset += $N_offset;
			break;
		} elseif (!$windid && $offset = strpos("\n".$Checkdata,"\n".$onlineip."\t")) {
			$isModify = 0;
			$offset += $N_offset;
			break;
		} elseif (!$padfp && $padfp = strpos($Checkdata,str_pad(" ",$db_olsize-1)."\n")) {
			$padfp += $N_offset-1;
			//$padfp=$offset;
			//break;
		}
		$N_offset = ftell($fp);
	}
	if (!$offset) {
		$offset = $padfp ? $padfp : $N_offset;
	}
	fclose($fp); unset($Checkdata);
	return array($offset,$isModify);
}
function addonlinefile($offset,$uid) {
	global $windid,$groupid,$lastvisit,$timestamp,$onlineip,$db_onlinetime,$fid,$tid,$wind_in,$tdtime, $db_olsize,$db_today;

	if (strlen($fid)>4) $fidwt = ''; else $fidwt = $fid;
	if (strlen($tid)>7) $tidwt = ''; else $tidwt = $tid;

	$wherebbsyou = getuseraction($fid,$wind_in);
	$acttime = get_date($timestamp,'m-d H:i');
	$D_name = "data/bbscache/online.php";
	if (!file_exists(D_P.$D_name)) {
		pwCache::writeover(D_P.$D_name,str_pad("<?php die;?>",96)."\n");
	}
	if (GetCookie('hideid') != 1) {
		$newonline = "$windid\t$timestamp\t$onlineip\t$fidwt\t$tidwt\t$groupid\t$wherebbsyou\t$acttime\t$uid\t";
		$newonline = str_pad($newonline,$db_olsize)."\n";
		if (checkinline(D_P.$D_name,$offset,$windid)) {
			$isModify = 0;
			writeinline(D_P.$D_name,$newonline,$offset);
		} else {
			list($offset,$isModify)=GetInsertOffset(D_P.$D_name);
			writeinline(D_P.$D_name,$newonline,$offset);
		}
		if ($db_today && $timestamp-$lastvisit>$db_onlinetime) {
			require_once(R_P.'require/today.php');
		}
	} elseif (GetCookie('hideid') == 1) {
		require_once(R_P.'require/hidden.php');
	}
	if ($isModify === 1) {
		//频度可控制性
		ModifySelectFile(D_P."data/bbscache/guest.php");
	}
	return array($offset,$isModify);
}
function addguestfile($offset) {
	global $timestamp,$onlineip,$tid,$fid,$wind_in,$db_olsize;
	if (strlen($fid)>4) $fidwt=''; else $fidwt = $fid;
	if (strlen($tid)>7) $tidwt=''; else $tidwt = $tid;
	$wherebbsyou = getuseraction($fid,$wind_in);
	$acttime = get_date($timestamp,'m-d H:i');
	$newonline = "$onlineip\t$timestamp\t<FiD>$fidwt\t$tidwt\t$wherebbsyou\t$acttime\t"; //<FiD>主要用于thread.php里快速找到指定的版块游客
	$newonline = str_pad($newonline,$db_olsize)."\n";
	$D_name = "data/bbscache/guest.php";
	if (!file_exists(D_P.$D_name)) {
		pwCache::setData(D_P.$D_name,str_pad("<?php die;?>",96)."\n");
	}
	if (checkinline(D_P.$D_name,$offset,$onlineip)) {
		$isModify = 0;
		writeinline(D_P.$D_name,$newonline,$offset);
	} else {
		list($offset,$isModify) = GetInsertOffset(D_P.$D_name);
		writeinline(D_P.$D_name,$newonline,$offset);
	}
	if ($isModify === 1) {
		//频度可控制性
		ModifySelectFile(D_P."data/bbscache/online.php");
		if ($GLOBALS['userinbbs'] === 0) {
			$GLOBALS['userinbbs']--;
			ModifySelectFile(D_P.$D_name,1);
		}
	}
	return array($offset,$isModify);
}

function writeinline($filename,$data,$offset) {
	$fp = fopen($filename,"rb+");
	flock($fp,LOCK_EX);
	fseek($fp,$offset);
	fwrite($fp,$data);
	fclose($fp);
}
function ModifySelectFile($filename,$deny=0) {
	global $db_olsize,$timestamp,$db_onlinetime,$onlineip,$guestinbbs,$userinbbs;
	$array_bit = $filename === D_P."data/bbscache/guest.php" ? 0 : 2;
	$addnbsp = str_pad(" ",$db_olsize)."\n";
	$addfb = str_pad("<?php die;?>",$db_olsize)."\n";
	$cutsize = $db_olsize+1;
	$step = $olnum = $end = 0;
	$onlinetime = $timestamp - $db_onlinetime;
	$A_offset = array();
	$fp = fopen($filename,"rb");
	flock($fp,LOCK_SH);
	fseek($fp,0,SEEK_END);
	while (ftell($fp) > $cutsize && $step < 20000) {
		$step++;
		$offset = -($cutsize*$step);
		fseek($fp,$offset,SEEK_END);
		$line = fread($fp,42);
		if (empty($end)) {
			if (strpos($line,"\t") !== false || ftell($fp) <= $cutsize) {
				$end = $offset;
			}
		}
		if (strpos($line,"\t") !== false) {
			$detail = explode("\t",$line);
			if ($detail[1] < $onlinetime || ($detail[$array_bit] === $onlineip && $deny == 0)) {
				$A_offset[] = $offset;
			} else {
				$olnum++;
			}
		}
	}
	fclose($fp);
	$fp = fopen($filename,"rb+");
	flock($fp,LOCK_EX);
	fwrite($fp,$addfb);
	foreach ($A_offset as $value) {
		fseek($fp,$value,SEEK_END);fwrite($fp,$addnbsp);
	}
	if (isset($end)) ftruncate($fp,filesize($filename)+$end+$cutsize);
	fclose($fp);
	include_once (D_P.'data/bbscache/olcache.php');
	if ($filename === D_P."data/bbscache/guest.php") {
		$guestinbbs = $olnum;
		$userinbbs++;
	} else {
		$userinbbs = $olnum;
		$guestinbbs++;
	}
	$olcache = "<?php\n\$userinbbs=$userinbbs;\n\$guestinbbs=$guestinbbs;\n?>";
	pwCache::writeover(D_P.'data/bbscache/olcache.php',$olcache);
}
function getuseraction($id,$action) {
	global $forum;
	//* include_once pwCache::getPath(D_P.'data/bbscache/forum_cache.php');
	extract(pwCache::getData(D_P.'data/bbscache/forum_cache.php', false));
	$name = $forum[$id]['name'];
	if ($name) {
		$name = preg_replace("/\<(.+?)\>/is","",$name);
		return substrs($name,13);
	} elseif ($action && ($tmpMsg = getLangInfo('action',$action))) {
		if ($tmpMsg != $action) {
			return $tmpMsg;
		}
	}
	return getLangInfo('action','other');
}

?>