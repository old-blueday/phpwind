<?php
!function_exists('readover') && exit('Forbidden');

function SQLCreate($sqlarray, $ifupdate=false) {
	global $db,$charset;
	$query = '';
	foreach ($sqlarray as $value) {		
		if ( $value[0] == '#' ) continue;
		$query = $value;
		$lowquery = strtolower(substr($query,0,5));
		if ($lowquery == 'drop ') continue;
		if (substr($value,-1)==';' && ($ifupdate || !in_array($lowquery,array('delet','updat')))) {
			$next = CheckDrop($query);
			if ($lowquery == 'creat') {
				if (!$next) continue;
				strpos($query,'IF NOT EXISTS')===false && $query = str_replace('TABLE','TABLE IF NOT EXISTS',$query);
				$extra1 = trim(substr(strrchr($value,')'),1));
				$tabtype = substr(strchr($extra1,'='),1);
				$tabtype = substr($tabtype,0,strpos($tabtype,strpos($tabtype,' ') ? ' ' : ';'));
				if ($db->server_info() >= '4.1') {
					$extra2 = "ENGINE=$tabtype".($charset ? " DEFAULT CHARSET=$charset" : '');
				} else {
					$extra2 = "TYPE=$tabtype";
				}
				$query = str_replace($extra1,$extra2.';',$query);
			} elseif (in_array($lowquery,array('inser','repla'))) {
				if (!$next) continue;
				$lowquery == 'inser' && $query = 'REPLACE '.substr($query,6);
			} elseif ($lowquery == 'alter' && !$next && strpos(strtolower($query),'drop')!==false) {
				continue;
			}
			$db->query($query);
			$query = '';
		}
	}
}

function SQLDrop($sqlarray) {
	global $db;
	foreach ($sqlarray as $query) {
		$lowquery = strtolower(substr($query,0,6));
		$next = CheckDrop($query);
		if ($next && $lowquery == 'create') {
			$t_name = trim(substr($query,0,strpos($query,'(')));
			$t_name = substr($t_name,strrpos($t_name,' ')+1);
			$db->query("DROP TABLE IF EXISTS $t_name");
		}
	}
}
function FileArray($hackdir,$base='hack'){
	if (!in_array($base,array('hack','mode'))) $base = 'hack';
	if (function_exists('file_get_contents')) {
		$filedata = @file_get_contents(S::escapePath(R_P."$base/$hackdir/sql.txt"));
	} else {
		$filedata = readover(R_P."$base/$hackdir/sql.txt");
	}
	
	$filedata = preg_replace("/;(\r\n|\n)/is", ";[pw]", $filedata);	
	$filedata = trim(str_replace(array("\t","\r","\n"),array('','',''),$filedata));	
	$sqlarray = $filedata ? explode("[pw]",$filedata) : array();
	return $sqlarray;
}
function CheckDrop($query){
	global $db;
	require_once(R_P.'admin/table.php');
	list($pwdb) = N_getTabledb();
	$next = true;
	foreach ($pwdb as $value) {
		if (strpos(strtolower($query),strtolower($value))!==false) {
			$next = false;
			break;
		}
	}
	return $next;
}
?>