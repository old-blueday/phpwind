<?php
!function_exists('readover') && exit('Forbidden');

if (file_exists(D_P."data/bbscache/hits.txt")) {
	if($hitsize < 10240){
		$hitarray	= explode("\t",readover(D_P."data/bbscache/hits.txt"));
		$hits		= array_count_values($hitarray);
		$count		= 0;
		#Temporary Table
		$hits_a		= '';
		foreach ($hits as $key=>$val) {
			$hits_a .= ",('$key','$val')";
			if(++$count>300) break;
		}
		if ($hits_a) {
			$hits_a = trim($hits_a,',');
			$db->query("CREATE TEMPORARY TABLE heap_hitupdate (tid INT(10) UNSIGNED NOT NULL ,hits SMALLINT(6) UNSIGNED NOT NULL) TYPE = HEAP");
			$db->update("INSERT INTO heap_hitupdate (tid,hits) VALUES $hits_a");
			$db->update("UPDATE pw_threads as t,heap_hitupdate as h SET t.hits=t.hits+h.hits WHERE t.tid=h.tid");
			$db->query("DELETE FROM heap_hitupdate");
		}
		$nowtime	= ($timestamp-$tdtime)/3600;
		$hit_control= floor($nowtime/$db_hithour)+1;
		if($hit_control>24/$db_hithour) $hit_control = 1;
		$db->update("UPDATE pw_bbsinfo SET hit_control=".pwEscape($hit_control,false).",hit_tdtime=".pwEscape($tdtime,false)."WHERE id=1");
		unset($hitarray,$hits,$hits_a);
	}
	P_unlink(D_P."data/bbscache/hits.txt");
}
?>