<?php
!defined('P_W') && exit('Forbidden');

if (!$action) {
	require GetLang('left');
	$db_diy = $db->get_value("SELECT db_value FROM pw_config WHERE db_name='db_diy'");
	if ($db_diy) {
		$db_diy = ','.$db_diy.',';
	} else {
		$db_diy = ',setforum,setuser,level,postcache,article,';
	}
	foreach ($nav_left['mode']['items'] as $key=>$value) {
		$nav_left[$key] = $value;
	}
	unset($nav_left['mode']);
	foreach ($nav_left as $cate=>$left) {
		foreach ($left['items'] as $key=>$value) {
			if (is_array($value)) {
				foreach ($value['items'] as $k=>$v) {
					$nav_left[$cate]['items'][$v] = $purview[$v][0];
					unset($nav_left[$cate]['items'][$k]);
				}
				unset($nav_left[$cate]['items'][$key]);
			} else {
				$nav_left[$cate]['items'][$value] = $purview[$value][0];
					unset($nav_left[$cate]['items'][$key]);
			}
		}
	}
	$editset = $checkvar = '';
	foreach ($nav_left as $title => $left) {
		$checkvar .= ",'chk_$title' : true";
		$editset .= '<tr class="tr1 vt"><td class="td1"><a style="cursor:pointer" onclick="CheckForm(getObj(\''.$title.'\'))">'.$left['name'].'</a></td><td id="'.$title.'" class="td2"><ul class="list_A list_160" style="width:100%;">';
		foreach ($left['items'] as $key => $value) {
			$checked = (strpos($db_diy,','.$key.',')!==false) ? 'CHECKED' : '';
			$editset .= ' <li><input type="checkbox" name="diydb[]" value="'.$key.'" '.$checked.'> '.$value.'</li>';
		}
		$editset .= "</ul></td></tr>";
	}
	$checkvar && $checkvar = substr($checkvar,1);
	include PrintEot('diyoption');exit;
} elseif ($action=='edit') {
	S::gp(array('diydb'),'P');
	if (is_array($diydb)) {
		if (count($diydb)>15) adminmsg('diyoption_maxlength');
		$diydb	= implode(',',$diydb);
	} else {
		$diydb	= '';
	}
	setConfig('db_diy', $diydb);
	updatecache_c();
	adminmsg('operate_success');
} else {
	ObHeader($basename);
}
?>