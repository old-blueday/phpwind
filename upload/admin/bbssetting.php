<?php
!function_exists('adminmsg') && exit('Forbidden');
$basename = "$admin_file?adminjob=bbssetting";
S::GP('step');
if ($step != 2){
	//global
	$sltstyles = '';
	if ($fp = opendir(D_P . 'data/style/')) {
		while (($skinfile = readdir($fp)) !== false) {
			if (preg_match('/([^\.]+?)\.php$/i', $skinfile, $rt) && is_array($db_styledb) && (!empty($db_styledb[$rt[1]]) || $db_styledb[$rt[1]][1])) {
				$skinname = $db_styledb[$rt[1]][0] ? $db_styledb[$rt[1]][0] : $rt[1];
				if ($rt[1] == $db_defaultstyle) {
					$sltstyles .= '<option value="' . $rt[1] . '" SELECTED>' . $skinname . '</option>';
				} else {
					$sltstyles .= '<option value="' . $rt[1] . '">' . $skinname . '</option>';
				}
			}
		}
		closedir($fp);
	}
	${'columns_' . (int) $db_columns} = 'SELECTED';
	$db_txtadnum = (int) $db_txtadnum;
	ifcheck($db_adminset, 'adminset');
	ifcheck($db_menu, 'menu');
	ifcheck($db_recycle, 'recycle');
	${'shiftstyle_'.$db_shiftstyle} = 'checked';
	include PrintEot('bbssetting');exit;
} else {
	S::gp(array('config'), 'P');
	saveConfig();
	adminmsg('operate_success');
}