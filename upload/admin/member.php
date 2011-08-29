<?php
!function_exists('adminmsg') && exit('Forbidden');
$basename = "$admin_file?adminjob=member";
S::GP('step');
if ($step != 2){
	require_once (R_P . 'require/credit.php');
	$sellset = $enhideset = $signcurtype = '';
	foreach ($credit->cType as $key => $value) {
		//post
		$sellcked = in_array($key, $db_sellset['type']) ? 'CHECKED' : '';
		$sellset .= '<li><input type="checkbox" name="sellset[type][]" value="' . $key . '" ' . $sellcked . ' /> ' . $value . '</li>';
		$enhidechked = in_array($key, $db_enhideset['type']) ? 'CHECKED' : '';
		$enhideset .= '<li><input type="checkbox" name="enhideset[type][]" value="' . $key . '" ' . $enhidechked . ' /> ' . $value . '</li>';
		//face
		if (!is_numeric($key)) {
			if ($db_signcurtype == $key) {
				$signcurtype .= '<option value="' . $key . '" SELECTED>' . $value . '</option>';
			} else {
				$signcurtype .= '<option value="' . $key . '">' . $value . '</option>';
			}
		}
	}
	//post
	$db_postmax = (int) $db_postmax;
	$db_postmin = (int) $db_postmin;
	$db_selcount = (int) $db_selcount;
	$db_titlemax = (int) $db_titlemax;
	$db_showreplynum = (int) $db_showreplynum;
	$db_windpost['price'] = (int) $db_windpost['price'];
	$db_windpost['income'] = (int) $db_windpost['income'];
	ifcheck($db_postedittime, 'postedittime');
	ifcheck($db_selectgroup, 'selectgroup');
	ifcheck($db_windpost['checkurl'], 'windpost_checkurl');
	//face
	list($db_upload, $db_imglen, $db_imgwidth, $db_imgsize) = explode("\t", $db_upload);
//	list($db_fthumbwidth, $db_fthumbheight) = explode("\t", $db_fthumbsize);
	$maxuploadsize = @ini_get('upload_max_filesize');
	$signgroup = '';
	$num = 0;
	foreach ($ltitle as $key => $value) {
		if ($key != 1 && $key != 2) {
			$num++;
			$htm_tr = $num % 3 == 0 ? '' : '';
			$signcked = strpos($db_signgroup, ",$key,") !== false ? 'CHECKED' : '';
			$signgroup .= '<li><input type="checkbox" name="signgroup[]" value="' . $key . '" ' . $signcked . '> ' . $value . '</li>' . $htm_tr;
		}
	}
	$signgroup && $signgroup = '<ul class="list_A list_120">' . $signgroup . '</ul>';
	for ($i = 0; $i < 3; $i++) {
		${'logintype_' . $i} = ($db_logintype & pow(2, $i)) ? 'CHECKED' : '';
	}

	$db_imglen = $db_imglen > 120 ? 120 : (int) $db_imglen;
	$db_imgsize = (int) $db_imgsize;
	$db_imgwidth = $db_imgwidth > 120 ? 120 : (int) $db_imgwidth;
	$db_signmoney = (int) $db_signmoney;
	$db_signheight = (int) $db_signheight;
	//$db_fthumbwidth = (int) $db_fthumbwidth;
	//$db_fthumbheight = (int) $db_fthumbheight;
	$db_windpic['size'] = (int) $db_windpic['size'];
	$db_windpic['picwidth'] = (int) $db_windpic['picwidth'];
	$db_windpic['picheight'] = (int) $db_windpic['picheight'];

	ifcheck($db_upload, 'upload');
//	ifcheck($db_iffthumb, 'iffthumb');
	ifcheck($db_signwindcode, 'signwindcode');
	ifcheck($db_windpic['pic'], 'windpic_pic');
	ifcheck($db_windpic['flash'], 'windpic_flash');
	
	include PrintEot('member');exit;
} else {
	S::gp(array('sellset', 'enhideset','config'), 'P');
	S::gp(array('windpost', 'upload', 'signgroup', 'logintype', 'windpic'), 'P', 2);
	$config['windpost'] = is_array($db_windpost) ? $db_windpost : array();
	//post
	$config['sellset'] = is_array($sellset) ? $sellset : array();
	$config['windpic'] = is_array($windpic) ? $windpic : array();
	is_array($windpost) && $config['windpost'] = array_merge($config['windpost'],$windpost);
	$config['enhideset'] = is_array($enhideset) ? $enhideset : array();
	$config['titlemax'] = (int) $config['titlemax'];
	$config['postmax'] = (int) $config['postmax'];
	$config['postmax'] < 1 && $config['postmax'] = 50000;
	$config['postmin'] = (int) $config['postmin'];
	//face
	$upload['imglen'] = $upload['imglen'] > 120 ? 120 : $upload['imglen'];
	$upload['imgsize'] < 1 && $upload['imgsize'] = 20;
	$upload['imgwidth'] = $upload['imgwidth'] > 120 ? 120 : $upload['imgwidth'];
	$config['logintype'] = intval(array_sum($logintype));
	$config['signgroup'] = $signgroup ? ',' . implode(',', $signgroup) . ',' : '';
	//$config['fthumbsize'] = $fthumbsize['fthumbwidth'] . "\t" . $fthumbsize['fthumbheight'];
	$config['upload'] = $upload['upload'] . "\t" . $upload['imglen'] . "\t" . $upload['imgwidth'] . "\t" . $upload['imgsize'];
	saveConfig();
	adminmsg('operate_success');
}
