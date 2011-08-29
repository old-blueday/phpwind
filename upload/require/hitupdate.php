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
			//$db->update("UPDATE pw_threads as t,heap_hitupdate as h SET t.hits=t.hits+h.hits WHERE t.tid=h.tid");
			$db->update(pwQuery::buildClause('UPDATE :pw_table1 as t, :pw_table2 as h SET t.hits=t.hits+h.hits WHERE t.tid=h.tid', array('pw_threads','heap_hitupdate')));
			$db->query("DELETE FROM heap_hitupdate");
		}
		$nowtime	= ($timestamp-$tdtime)/3600;
		$hit_control= floor($nowtime/$db_hithour)+1;
		if($hit_control>24/$db_hithour) $hit_control = 1;
		//* $db->update("UPDATE pw_bbsinfo SET hit_control=".S::sqlEscape($hit_control,false).",hit_tdtime=".S::sqlEscape($tdtime,false)."WHERE id=1");
		pwQuery::update('pw_bbsinfo', 'id=:id', array(1), array('hit_control'=>$hit_control, 'hit_tdtime'=>$tdtime));
		unset($hitarray,$hits,$hits_a);
	}
	P_unlink(D_P."data/bbscache/hits.txt");
}
?>