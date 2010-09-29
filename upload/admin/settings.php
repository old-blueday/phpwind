<?php
!defined('P_W') && exit('Forbidden');
include_once (D_P . 'data/bbscache/level.php');

$basename .= '&admintype=' . $admintype;
$basedb = array('basic', 'safe', 'att', 'credit', 'reg', 'member',
				'pcache', 'index', 'thread', 'read', 'jsinvoke',
				'popinfo', 'wap', 'email', 'searcher'
				);
$settingdb = array();
foreach ($basedb as $value) {
	if (If_manager && $admintype == 'all') {
		$settingdb[$value] = true;
	} else {
		$settingdb[$value] = false;
	}
}

if ($_POST['step'] != 2) {
	//$action = '';
	if ($admintype == 'basic' || $settingdb['basic']) {
		if (!$settingdb['basic']) {
			if ($action) {
				Cookie('admin_basic', $action);
			} else {
				$action = $_COOKIE['admin_basic'];
			}
			!$action && $action = 'state';
		}
		//state
		${'bbsifopen_' . (int) $db_bbsifopen} = 'CHECKED';
		//list($db_opensch,$db_schstart,$db_schend) = explode("\t",$db_opensch);
		list($db_openpost, $db_poststart, $db_postend) = explode("\t", $db_openpost);
		$db_whybbsclose = str_replace(array('<br />', '<br>'), "\n", $db_whybbsclose);
		$db_visituser = trim($db_visituser, ',');
		//$db_schend = (int)$db_schend;
		$db_postend = (int) $db_postend;
		//$db_schstart = (int)$db_schstart;
		$db_poststart = (int) $db_poststart;
		$db_bbsname = htmlspecialchars($db_bbsname);
		$db_areaname = htmlspecialchars($db_areaname);
		$db_modename = htmlspecialchars($db_modename);

		//ifcheck($db_opensch,'opensch');
		ifcheck($db_openpost, 'openpost');
		ifcheck($db_visitopen, 'visitopen');
		$visitgroup = '';
		$num = 0;
		foreach ($ltitle as $key => $value) {
			if ($key != 1 && $key != 2) {
				$num++;
				$htm_tr = $num % 3 == 0 ? '' : '';
				$s_checked = strpos($db_visitgroup, ',' . $key . ',') !== false ? 'CHECKED' : '';
				$visitgroup .= '<li><input type="checkbox" name="visitgroup[]" value="' . $key . '" ' . $s_checked . '>' . $value . '</li>' . $htm_tr;
			}
		}
		$visitgroup && $visitgroup = '<ul class="cc list_A list_120">' . $visitgroup . '</ul>';
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
		${'charset_' . str_replace('-', '', $db_charset)} = ${'columns_' . (int) $db_columns} = 'SELECTED';
		$temptimedf = str_replace('.', '_', abs($db_timedf));
		$db_timedf < 0 ? ${'zone_0' . $temptimedf} = 'SELECTED' : ${'zone_' . $temptimedf} = 'SELECTED';
		${'forumdir_' . (int) $db_forumdir} = $check_24 = 'CHECKED';
		if ($db_datefm) {
			if (strpos($db_datefm, 'h:i A') !== false) {
				$db_datefm = str_replace(' h:i A', '', $db_datefm);
				$check_12 = 'CHECKED';
				$check_24 = '';
			} else {
				$db_datefm = str_replace(' H:i', '', $db_datefm);
			}
			$db_datefm = str_replace(array('m', 'n', 'd', 'j', 'y', 'Y'), array('mm', 'm', 'dd', 'd', 'yy', 'yyyy'), $db_datefm);
		} else {
			$db_datefm = 'yyyy-mm-dd';
		}
		$db_onlinetime /= 60;
		!$db_ckpath && $db_ckpath = '/';
		$db_cvtime = (int) $db_cvtime;
		//$db_schwait = (int)$db_schwait;
		$db_txtadnum = (int) $db_txtadnum;
		$db_maxmember = (int) $db_maxmember;
		//$db_maxresult = (int)$db_maxresult;
		$db_refreshtime = (int) $db_refreshtime;

		//ifcheck($db_forumdir,'forumdir');
		ifcheck($db_today, 'today');
		ifcheck($db_debug, 'debug');
		ifcheck($db_ifjump, 'ifjump');
		ifcheck($db_recycle, 'recycle');
		ifcheck($db_msgsound, 'msgsound');
		ifcheck($db_ifcredit, 'ifcredit');
		ifcheck($db_adminset, 'adminset');
		ifcheck($db_menu, 'menu');
		ifcheck($db_readinfo, 'readinfo');
		ifcheck($db_footertime, 'footertime');
		ifcheck($db_enterreason, 'enterreason');
		ifcheck($db_forcecharset, 'forcecharset');
		ifcheck($db_toolbar, 'toolbar');
		for ($i = 0; $i < 5; $i++) {
			${'ajax_' . $i} = ($db_ajax & pow(2, $i)) ? 'CHECKED' : '';
		}
	}
	if ($admintype == 'safe' || $settingdb['safe']) {
		if (!$settingdb['safe']) {
			if ($action) {
				Cookie('admin_safe', $action);
			} else {
				$action = $_COOKIE['admin_safe'];
			}
			!$action && $action = 'speed';
		}
		//speed
		$db_obstart = (int) $db_obstart;
		$db_onlinelmt = (int) $db_onlinelmt;
		ifcheck($db_lp, 'lp');
		ifcheck($db_online, 'online');
		ifcheck($db_redundancy, 'redundancy');
		//seo
		//		!is_array($db_bbstitle) && $db_bbstitle = array('index' => $db_bbstitle,'other' => '');
		//gdcode
		${'gdtype_' . (int) $db_gdtype} = 'SELECTED';
		for ($i = 0; $i < 7; $i++) {
			${'gdcheck_' . $i} = ($db_gdcheck & pow(2, $i)) ? 'CHECKED' : '';
			${'gdstyle_' . $i} = ($db_gdstyle & pow(2, $i)) ? 'CHECKED' : '';
		}
		$gdsize = explode("\t", $db_gdsize);
		list($regq, $loginq, $postq, $msgq,$showq) = explode("\t", $db_qcheck);
		$postq = (int) $postq;
		$db_postgd = (int) $db_postgd;
		ifcheck($msgq, 'msgq');
		ifcheck($regq, 'regq');
		ifcheck($loginq, 'loginq');
		ifcheck($showq, 'showq');
		//dir
		$imgdisabled = $attdisabled = $htmdisabled = $stopicdisabled = '';
		if (file_exists($imgdir) && !pwWritable($imgdir)) {
			$imgdisabled = 'DISABLED';
		}
		if (file_exists($attachdir) && !pwWritable($attachdir)) {
			$attdisabled = 'DISABLED';
		}
		if (file_exists(R_P . $db_htmdir) && !pwWritable(R_P . $db_htmdir)) {
			$htmdisabled = 'DISABLED';
		}
		if (file_exists(R_P . $db_stopicdir) && !pwWritable(R_P . $db_stopicdir)) {
			$stopicdisabled = 'DISABLED';
		}
		//if ($db_htmdir . '/stopic' == $db_stopicdir) $db_stopicdir = '';
		if (strpos($db_stopicdir, $db_htmdir) === 0) $db_stopicdir = substr($db_stopicdir, strlen($db_htmdir) + 1);
		if (strpos($db_readdir, $db_htmdir) === 0) $db_readdir = substr($db_readdir, strlen($db_htmdir) + 1);

		ifcheck($db_autochange, 'autochange');
		${'hour_' . (int) $db_hour} = 'SELECTED';
		!$db_http && $db_http = 'N';
		!$db_attachurl && $db_attachurl = 'N';
		//safe
		${'cc_' . (int) $db_cc} = 'CHECKED';
		$db_loadavg = (int) $db_loadavg;
		ifcheck($db_ipcheck, 'ipcheck');
		ifcheck($db_ifsafecv, 'ifsafecv');
		$db_xforwardip = L::config("db_xforwardip"); /*have overwrite*/
		ifcheck($db_xforwardip, 'xforwardip');
		$safegroup = '';
		$num = 0;
		foreach ($ltitle as $key => $value) {
			if ($key != 1 && $key != 2) {
				$num++;
				$htm_tr = $num % 3 == 0 ? '' : '';
				$s_checked = strpos($db_safegroup, ',' . $key . ',') !== false ? 'CHECKED' : '';
				$safegroup .= '<li><input type="checkbox" name="safegroup[]" value="' . $key . '" ' . $s_checked . '>' . $value . '</li>' . $htm_tr;
			}
		}
		$safegroup && $safegroup = '<ul class="list_A list_120">' . $safegroup . '</li>';
	}
	if ($admintype == 'att' || $settingdb['att']) {

		if (!$settingdb['att']) {
			if ($action) {
				Cookie('admin_att', $action);
			} else {
				$action = $_COOKIE['admin_att'];
			}
			!$action && $action = 'att';
		}

		//pwatermark
		$pwatermark = GetGP('pwatermark');
		InitGP(array('config'), 'G');
		if ($pwatermark == 1) {
			require_once (R_P . 'require/imgfunc.php');
			$source = $imgdir . '/water/watermark.jpg';
			$dstsrc = D_P . 'data/bbscache/watermark_preview.jpg';
			$db_waterfonts = $config['waterfonts'];
			$db_watermark = $config['watermark'];
			if (ImgWaterMark($source, $config['waterpos'], $config['waterimg'], $config['watertext'], $config['waterfont'], $config['watercolor'], $config['waterpct'], $config['jpgquality'], $dstsrc)) {
				$size1 = filesize($source);
				$size2 = filesize($dstsrc);
				$sizerate = round($size2 / $size1, 3) * 100;
				include PrintEot('setting');
				exit();
			} else {
				adminmsg('watermark_error');
			}
		}

		//pathumb
		$pathumb = GetGP('pathumb');
		InitGP(array('athumbsize'), 'G');
		if ($pathumb == 1) {
			require_once (R_P . 'require/imgfunc.php');
			$source = $imgdir . '/water/watermark.jpg';
			$thumburl = D_P . 'data/bbscache/pathumb_preview.jpg';
			$source_size = GetImgSize($source);
			$size1 = filesize($source);
			if ($thumbsize = MakeThumb($source, $thumburl, $athumbsize['athumbwidth'], $athumbsize['athumbheight'])) {
				$size2 = filesize($thumburl);
				$sizerate = round($size2 / $size1, 3) * 100;
				$imageurl = 'data/bbscache/pathumb_preview.jpg';
			} else {
				$size2 = $size1;
				$sizerate = 100;
				$thumbsize[0] = $source_size['width'];
				$thumbsize[1] = $source_size['height'];
				$imageurl = $imgpath . '/water/watermark.jpg';
			}
			include PrintEot('setting');
			exit();
		}
		if (!$settingdb['att']) {
			if ($action) {
				Cookie('admin_att', $action);
			} else {
				$action = $_COOKIE['admin_admin_attsafe'];
			}
			!$action && $action = 'att';
		}
		//att
		(int) $db_attachnum < 1 && $db_attachnum = 4;
		$db_attachhide = (int) $db_attachhide;
		ifcheck($db_attfg, 'attfg');
		ifcheck($db_allowupload, 'allowupload');
		$attachdir_ck[(int) $db_attachdir] = 'SELECTED';
		$maxuploadsize = ini_get('upload_max_filesize');
		!is_array($db_uploadfiletype = (is_array($db_uploadfiletype) ? $db_uploadfiletype : unserialize($db_uploadfiletype))) && $db_uploadfiletype = array();
		//pic
		list($db_athumbwidth, $db_athumbheight) = explode("\t", $db_athumbsize);
		${'ifgif_' . (int) $db_ifgif} = 'CHECKED';
		${'waterpos_ck_' . (int) $db_waterpos} = 'SELECTED';

		$enoption = $choption = '';
		if ($fp = opendir($imgdir . '/fonts/en/')) {
			while (($file = readdir($fp)) !== false) {
				if (substr($file, -4) == '.ttf') {
					$filename = substr($file, 0, -4);
					$enslted = $db_waterfonts == 'en/' . $filename ? 'SELECTED' : '';
					$enoption .= '<option value="en/' . $filename . '" ' . $enslted . '>' . $filename . '</option>';
				}
			}
			closedir($fp);
		}
		if ($fp = opendir($imgdir . '/fonts/ch/')) {
			while (($file = readdir($fp)) !== false) {
				if (substr($file, -4) == '.ttf') {
					$filename = substr($file, 0, -4);
					$chslted = $db_waterfonts == 'ch/' . $filename ? 'SELECTED' : '';
					$choption .= '<option value="ch/' . $filename . '" ' . $chslted . '>' . $filename . '</option>';
				}
			}
			closedir($fp);
		}
		!$enoption && $enoption = '<option value="en/PilsenPlakat">PilsenPlakat</option>';

		$db_waterpct = (int) $db_waterpct;
		$db_waterfont = (int) $db_waterfont;
		$db_jpgquality = (int) $db_jpgquality;
		$db_waterwidth = (int) $db_waterwidth;
		$db_waterheight = (int) $db_waterheight;
		$db_athumbwidth = (int) $db_athumbwidth;
		$db_athumbheight = (int) $db_athumbheight;

		ifcheck($db_ifathumb, 'ifathumb');
		ifcheck($db_ifathumbgif, 'ifathumbgif');
		$db_watermark == 1 ? $watermark_1 = 'checked' : ($db_watermark == 2 ? $watermark_2 = 'checked' : $watermark_0 = 'checked');
		//ftp
		@include_once (D_P . 'data/bbscache/ftp_config.php');
		(int) $ftp_port < 1 && $ftp_port = '21';
		(int) $ftp_timeout < 1 && $ftp_timeout = '10';
		ifcheck($db_ifftp, 'ifftp');
		$ftp_pass = substr($ftp_pass, 0, 1) . '********' . substr($ftp_pass, -1);
		//$ajax_basename = EncodeUrl($basename.'&pwatermark=1');
		$ajax_basename_athumb = EncodeUrl($basename . '&pathumb=1');
	}
	if ($admintype == 'credit' || $settingdb['credit']) {
		if (!$settingdb['credit']) {
			if ($action) {
				Cookie('admin_credit', $action);
			} else {
				$action = $_COOKIE['admin_credit'];
			}
			//$action = $_COOKIE['admin_credit'];
			!$action && $action = 'name';
		}
		require_once (R_P . 'require/credit.php');
		!is_array($creditset = unserialize($db_creditset)) && $creditset = array();
		$creditlog = array();
		foreach ($db_creditlog as $key => $value) {
			foreach ($value as $k => $v) {
				$creditlog[$key][$k] = 'CHECKED';
			}
		}
		$db_virerate = (int) $db_virerate;
		$db_virelimit = (int) $db_virelimit;

		$rt = $db->get_one("SELECT db_value FROM pw_config WHERE db_name='jf_A'");
		$jf_A = $rt['db_value'] ? unserialize($rt['db_value']) : array();
		$creditlist = '';
		foreach ($credit->cType as $key => $value) {
			$creditlist .= "<option value=\"$key\">$value</option>";
		}
		$jf = array();
		foreach ($jf_A as $key => $value) {
			list($j_1, $j_2) = explode('_', $key);
			$jf[$key] = array($credit->cType[$j_1], $credit->cType[$j_2], $value[0], $value[1], $value[2]);
		}
	}
	if ($admintype == 'reg' || $settingdb['reg']) {
		require_once (R_P . 'require/credit.php');
		require_once (D_P . 'data/bbscache/dbreg.php');
		$rg_rgpermit = str_replace(array('<br />', '<br>'), "", $rg_rgpermit);
		$rg_welcomemsg = str_replace(array('<br />', '<br>'), "\n", $rg_welcomemsg);
		$rg_whyregclose = str_replace(array('<br />', '<br>'), "\n", $rg_whyregclose);
		ifcheck($rg_reg, 'reg');
		ifcheck($rg_ifcheck, 'ifcheck');
		ifcheck($rg_rglower, 'rglower');
		ifcheck($db_regpopup, 'regpopup');
		ifcheck($rg_npdifferf, 'npdifferf');
		ifcheck($rg_regdetail, 'regdetail');
		ifcheck($rg_emailcheck, 'emailcheck');
		ifcheck($rg_regsendmsg, 'regsendmsg');
		ifcheck($rg_regsendemail, 'regsendemail');
		$check[$rg_allowregister] = 'checked = checked';
		$rg_emailtype == 1 ? $emailtype_1 = 'checked' : ($rg_emailtype == 2 ? $emailtype_2 = 'checked' : $emailtype_0 = 'checked');

		$rg_timeend = (int) $rg_timeend;
		$rg_timestart = (int) $rg_timestart;
		$db_postallowtime = (int) $db_postallowtime;
		list($rg_regminpwd, $rg_regmaxpwd) = explode("\t", $rg_pwdlen);
		list($rg_regminname, $rg_regmaxname) = explode("\t", $rg_namelen);
		for ($i = 1; $i < 5; $i++) {
			${'pwdcomplex_' . $i} = strpos($rg_pwdcomplex, ',' . $i . ',') === false ? '' : 'CHECKED';
		}
		//selectmon_option
		$selectmon_option = "<select name=\"reg[regmon]\"><option value=\"0\">*</option>";
		for ($i = 1; $i <= 31; $i++) {
			$selectmon_option .= "<option value=\"" . $i . "\">" . $i . "</option>";
		}
		$selectmon_option .= "</select>";
		$selectmon_option = str_replace("<option value=\"" . $rg_regmon . "\">", "<option value=\"" . $rg_regmon . "\" selected=\"SELECTED\">", $selectmon_option);
		//selectweek_option
		$selectweek_option = "<select name=\"reg[regweek]\"><option value=\"0\">*</option>";
		for ($i = 0; $i <= 6; $i++) {
			$selectweek_option .= "<option value=\"" . $i . "\">" . getLangInfo('all', 'week_' . $i) . "</option>";
		}
		$selectweek_option .= "</select>";
		$selectweek_option = str_replace("<option value=\"" . $rg_regweek . "\">", "<option value=\"" . $rg_selectweek . "\" selected=\"SELECTED\">", $selectweek_option);
		$rg_registertype == 1 ? $registertype_1 = 'checked' : ($rg_registertype == 2 ? $registertype_2 = 'checked' : $registertype_0 = 'checked');
	}
	if ($admintype == 'member' || $settingdb['member']) {
		if (!$settingdb['member']) {
			if ($action) {
				Cookie('admin_member', $action);
			} else {
				$action = $_COOKIE['admin_member'];
			}
			//$action = $_COOKIE['admin_member'];
			!$action && $action = 'post';
		}
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
		$db_cvtimes = (int) $db_cvtimes;
		$db_postmax = (int) $db_postmax;
		$db_postmin = (int) $db_postmin;
		$db_selcount = (int) $db_selcount;
		$db_titlemax = (int) $db_titlemax;
		$db_showreplynum = (int) $db_showreplynum;
		$db_windpost['size'] = (int) $db_windpost['size'];
		$db_windpost['price'] = (int) $db_windpost['price'];
		$db_windpost['income'] = (int) $db_windpost['income'];
		$db_windpost['picwidth'] = (int) $db_windpost['picwidth'];
		$db_windpost['picheight'] = (int) $db_windpost['picheight'];
		ifcheck($db_tcheck, 'tcheck');
		ifcheck($db_pwcode, 'pwcode');
		ifcheck($db_setform, 'setform');
		ifcheck($db_autoimg, 'autoimg');
		ifcheck($db_windmagic, 'windmagic');
		ifcheck($db_selectgroup, 'selectgroup');
		ifcheck($db_replysendmail, 'replysendmail');
		ifcheck($db_replysitemail, 'replysitemail');
		ifcheck($db_windpost['pic'], 'windpost_pic');
		ifcheck($db_windpost['mpeg'], 'windpost_mpeg');
		ifcheck($db_windpost['flash'], 'windpost_flash');
		ifcheck($db_windpost['iframe'], 'windpost_iframe');
		ifcheck($db_windpost['checkurl'], 'windpost_checkurl');
		//face
		list($db_upload, $db_imglen, $db_imgwidth, $db_imgsize) = explode("\t", $db_upload);
		list($db_fthumbwidth, $db_fthumbheight) = explode("\t", $db_fthumbsize);
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

		$db_imglen = (int) $db_imglen;
		$db_imgsize = (int) $db_imgsize;
		$db_imgwidth = (int) $db_imgwidth;
		$db_signmoney = (int) $db_signmoney;
		$db_signheight = (int) $db_signheight;
		$db_fthumbwidth = (int) $db_fthumbwidth;
		$db_fthumbheight = (int) $db_fthumbheight;
		$db_windpic['size'] = (int) $db_windpic['size'];
		$db_windpic['picwidth'] = (int) $db_windpic['picwidth'];
		$db_windpic['picheight'] = (int) $db_windpic['picheight'];

		ifcheck($db_upload, 'upload');
		ifcheck($db_iffthumb, 'iffthumb');
		ifcheck($db_signwindcode, 'signwindcode');
		ifcheck($db_windpic['pic'], 'windpic_pic');
		ifcheck($db_windpic['flash'], 'windpic_flash');
	}
	if ($admintype == 'pcache' || $settingdb['pcache']) {
		if (!$settingdb['pcache']) {
			if ($action) {
				Cookie('admin_pcache', $action);
			} else {
				$action = $_COOKIE['admin_pcache'];
			}
			//$action = $_COOKIE['admin_pcache'];
			!$action && $action = 'thread';
		}
		//thread
		$db_fcachenum = (int) $db_fcachenum;
		$db_fcachetime = (int) $db_fcachetime;
		//guest
		$db_fguestnum = (int) $db_fguestnum;
		$db_tguestnum = (int) $db_tguestnum;
		$db_guestread = (int) $db_guestread;
		$db_guestindex = (int) $db_guestindex;
		$db_guestthread = (int) $db_guestthread;
	}
	if ($admintype == 'index' || $settingdb['index']) {
		${'indexfmlogo_' . (int) $db_indexfmlogo} = 'CHECKED';
		$gporder = explode(',', $db_showgroup);
		$usergroup = '';
		$num = 0;
		include_once(D_P.'data/style/'.$db_defaultstyle.'.php');
		foreach ($ltitle as $key => $value) {
			if ($key!=1 && $key!=2) {
				$num++;
				$htm_tr = $num%2 == 0 ? '' : '';
				if (in_array($key, $gporder)) {
					$g_ck = 'CHECKED';
					$g_order = array_search($key, $gporder);
				} else {
					$g_order = $g_ck = '';
				}
//				$imgdisabled = !is_file($imgdir.'/'.$stylepath.'/group/'.$lpic[$key].'.gif') ? 'DISABLED' : '';
				$usergroup .= '<li><input type="checkbox" name="gpshow[' . $key . ']" value="' . $key . '" ' . $g_ck . '> <input name="gporder[' . $key . ']" value="' . $g_order . '" class="input input_wd"> ' . $value . '</li>' . $htm_tr;
			}
		}
		$usergroup && $usergroup = ' <ul class="cc list_B list_160">' . $usergroup . '</ul>';

		ifcheck($db_setindex, 'setindex');
		ifcheck($db_todaypost, 'todaypost');
		ifcheck($db_showguest, 'showguest');
		ifcheck($db_adminshow, 'adminshow');
		ifcheck($db_indexlink, 'indexlink');
		ifcheck($db_indexonline, 'indexonline');
		ifcheck($db_ifselfshare, 'ifselfshare');
		ifcheck($db_bdayautohide, 'bdayautohide');
		ifcheck($db_indexmqshare, 'indexmqshare');
		ifcheck($db_indexshowbirth, 'indexshowbirth');
	}
	if ($admintype == 'thread' || $settingdb['thread']) {
		$hithour_sel[(int) $db_hithour] = 'SELECTED';

		$db_newtime = (int) $db_newtime;
		$db_perpage = (int) $db_perpage;
		$db_maxpage = (int) $db_maxpage;
		$db_maxtypenum = (int) $db_maxtypenum;
		ifcheck($db_threadonline, 'threadonline');
		ifcheck($db_threademotion, 'threademotion');
		ifcheck($db_threadshowpost, 'threadshowpost');
		ifcheck($db_threadsidebarifopen, 'threadsidebarifopen');

	}
	if ($admintype == 'read' || $settingdb['read']) {
		require_once (R_P . 'require/credit.php');

		$db_pingtime = (int) $db_pingtime;
		$db_readperpage = (int) $db_readperpage;
		foreach ($db_floorname as $key => $value) {
			if (empty($floorname)) {
				$floorname = $key . ':' . $value;
				$sFloor = $key;
			} elseif ($key - $sFloor == 1) {
				$floorname .= " , " . $value;
				$sFloor = $key;
			} else {
				$floorname .= "\r\n" . $key . ':' . $value;
			}
		}
		//$floorname = implode("\n",$db_floorname);
		ifcheck($db_shield, 'shield');
		ifcheck($db_ipfrom, 'ipfrom');
		ifcheck($db_showonline, 'showonline');
		ifcheck($db_showcolony, 'showcolony');
		ifcheck($db_ifonlinetime, 'ifonlinetime');
		ifcheck($db_threadrelated, 'threadrelated');

	}
	if ($admintype == 'email' || $settingdb['email']) {
		if (!$settingdb['email']) {
			if ($action) {
				Cookie('admin_email', $action);
			} else {
				$action = $_COOKIE['admin_email'];
			}
			//$action = $_COOKIE['admin_email'];
			!$action && $action = 'email';
		}
		include_once (D_P . 'data/bbscache/mail_config.php');
		//mail
		${'mailmethod_' . (int) $ml_mailmethod} = 'CHECKED';
		$ml_smtppass = substr($ml_smtppass, 0, 1) . '********' . substr($ml_smtppass, -1);

		$ml_smtpport = (int) $ml_smtpport;

		ifcheck($ml_smtpauth, 'smtpauth');
		ifcheck($ml_mailifopen, 'mailifopen');
		/*
		//wap
		$showforums = ''; $num = 0;
		$query = $db->query("SELECT fid,name FROM pw_forums WHERE type<>'category' AND allowvisit='' AND f_type!='hidden' AND cms='0'");
		while ($rt = $db->fetch_array($query,MYSQL_NUM)) {
			$num++;
			$htm_tr = $num%2==0 ? '</tr><tr>' : '';
			$forumscked = strpos(','.$db_wapfids.',',','.$rt[0].',')!==false ? 'CHECKED' : '';
			$showforums .= '<td><input type="checkbox" name="wapfids[]" value="'.$rt[0].'" '.$forumscked.'>'.$rt[1].'</td>'.$htm_tr;
		}
		$showforums && $showforums = '<table cellspacing="0" cellpadding="0" border="0" width="100%" align="center"><tr>'.$showforums.'</tr></table>';

		$db_waplimit = (int)$db_waplimit;

		ifcheck($db_wapifopen,'wapifopen');
		ifcheck($db_wapcharset,'wapcharset');
		//js
		$db_jsper = (int)$db_jsper;

		ifcheck($db_jsifopen,'jsifopen');

		//popinfo
		$db_sitemsg['reg'] = implode("\n",$db_sitemsg['reg']);
		$db_sitemsg['login'] = implode("\n",$db_sitemsg['login']);
		$db_sitemsg['post'] = implode("\n",$db_sitemsg['post']);
		$db_sitemsg['reply'] = implode("\n",$db_sitemsg['reply']);
		*/
	}

	if ($admintype == 'jsinvoke' || $settingdb['jsinvoke']) {
		$db_jsper = (int) $db_jsper;

		ifcheck($db_jsifopen, 'jsifopen');
	}
	if ($admintype == 'popinfo' || $settingdb['popinfo']) {
		$db_sitemsg['reg'] = implode("\n", $db_sitemsg['reg']);
		$db_sitemsg['login'] = implode("\n", $db_sitemsg['login']);
		$db_sitemsg['post'] = implode("\n", $db_sitemsg['post']);
		$db_sitemsg['reply'] = implode("\n", $db_sitemsg['reply']);
	}
	if ($admintype == 'wap' || $settingdb['wap']) {
		$showforums = '';
		$num = 0;
		$query = $db->query("SELECT fid,name FROM pw_forums WHERE type<>'category' AND allowvisit='' AND f_type!='hidden' AND cms='0'");
		while ($rt = $db->fetch_array($query, MYSQL_NUM)) {
			$num++;
			$htm_tr = $num % 2 == 0 ? '' : '';
			$forumscked = strpos(',' . $db_wapfids . ',', ',' . $rt[0] . ',') !== false ? 'CHECKED' : '';
			$showforums .= '<li><input type="checkbox" name="wapfids[]" value="' . $rt[0] . '" ' . $forumscked . '>' . $rt[1] . '</li>' . $htm_tr;
		}
		$showforums && $showforums = '<ul class="list_A list_120 cc">' . $showforums . '</ul>';

		$db_waplimit = (int) $db_waplimit;

		ifcheck($db_wapifopen, 'wapifopen');
		ifcheck($db_wapcharset, 'wapcharset');
	}

	if ($admintype == 'searcher' || $settingdb['searcher']) {
		list($db_opensch, $db_schstart, $db_schend) = explode("\t", $db_opensch);
		$db_schend = (int) $db_schend;
		$db_schstart = (int) $db_schstart;
		ifcheck($db_opensch, 'opensch');
		$db_maxresult = (int) $db_maxresult;
		$db_schwait = (int) $db_schwait;
		$db_hotwords = ($db_hotwords) ? $db_hotwords : '';
		$db_filterids = ($db_filterids) ? $db_filterids : '';
		;
	}
	$display = '';
	if ($action) {
		${'cls_' . $action} = 'class="two"';
		$display = 'none';
	}

	//checkemail
	if ($ml_mailifopen == 1) {
		$ajax_basename = EncodeUrl($basename);
	}
	include PrintEot('setting');
	exit();

} else {

	InitGP(array('config','siteName'), 'P', 0);
	if (!pwWritable(D_P . 'data/bbscache/config.php') && !chmod(D_P . 'data/bbscache/config.php', 0777)) {
		adminmsg('config_777');
	}
	if ($admintype == 'basic' || $settingdb['basic']) {
		InitGP(array('visitgroup', 'postctl', 'schctl', 'cajax'), 'P', 2);
		InitGP(array('siteName'));
		foreach ((array) $siteName as $key => $value) {
			if ($key == 'bbs') {
				$config['bbsname'] = $value;
			} else {
				setConfig($key . '_sitename', $value, null, true);
				updatecache_conf($key, true);
			}
		}

		//state
		$config['visitmsg'] = ieconvert($config['visitmsg']);
		$config['whybbsclose'] = ieconvert($config['whybbsclose']);
		$postctl['poststart'] > 23 && $postctl['poststart'] = 0;
		$postctl['postend'] > 23 && $postctl['postend'] = 0;
		$schctl['schstart'] > 23 && $schctl['schstart'] = 0;
		$schctl['schend'] > 23 && $schctl['schend'] = 0;
		$config['openpost'] = $postctl['openpost'] . "\t" . $postctl['poststart'] . "\t" . $postctl['postend'];
		$config['opensch'] = $schctl['opensch'] . "\t" . $schctl['schstart'] . "\t" . $schctl['schend'];
		strlen($config['visituser']) > 0 && $config['visituser'] = ',' . trim($config['visituser']) . ',';
		$config['visitgroup'] = $visitgroup ? ',' . implode(',', $visitgroup) . ',' : '';
		//info
		substr($config['bbsurl'], 0, 4) != 'http' && adminmsg('bbsurl_http');
		substr($config['bbsurl'], -1) == '/' && $config['bbsurl'] = substr($config['bbsurl'], 0, -1);
		//global
		if ($config['datefm']) {
			if (strpos($config['datefm'], 'mm') !== false) {
				$config['datefm'] = str_replace('mm', 'm', $config['datefm']);
			} else {
				$config['datefm'] = str_replace('m', 'n', $config['datefm']);
			}
			if (strpos($config['datefm'], 'dd') !== false) {
				$config['datefm'] = str_replace('dd', 'd', $config['datefm']);
			} else {
				$config['datefm'] = str_replace('d', 'j', $config['datefm']);
			}
			$config['datefm'] = str_replace(array('yyyy', 'yy'), array('Y', 'y'), $config['datefm']);
			$config['datefm'] .= GetGP('time_f', 'P') == '12' ? ' h:i A' : ' H:i';
		} else {
			$config['datefm'] = 'Y-n-j H:i';
		}
		$config['ajax'] = intval(array_sum($cajax));
		$config['onlinetime'] *= 60;
		substr($config['ckpath'], -1) != '/' && $config['ckpath'] .= '/';
		unset($postctl, $schctl, $cajax);
	}
	if ($admintype == 'safe' || $settingdb['safe']) {
		InitGP(array('answer'), 'P');
		InitGP(array('question'), 'P', 0);
		InitGP(array('safegroup', 'gdcheck', 'gdstyle', 'gdsize', 'qcheck'), 'P', 2);
		//speed
		$config['onlinelmt'] = (int) $config['onlinelmt'] > 0 ? intval($config['onlinelmt']) : 0;
		$config['obstart'] = (int) $config['obstart'] > 9 ? 9 : intval($config['obstart']);
		//gdcode
		$config['gdsize'] = implode("\t", $gdsize);
		$config['safegroup'] = $safegroup ? ',' . implode(',', $safegroup) . ',' : '';
		$config['qcheck'] = $qcheck['regq'] . "\t" . $qcheck['loginq'] . "\t" . $qcheck['postq'] . "\t" . $qcheck['msgq']."\t".$qcheck['showq'];
		$config['gdcheck'] = !empty($gdcheck) ? intval(array_sum($gdcheck)) : 0;
		$config['gdstyle'] = !empty($gdstyle) ? intval(array_sum($gdstyle)) : 0;
		$q_array = $a_array = array();
		if (is_array($question) && is_array($answer)) {
			foreach ($question as $key => $value) {
				$value = trim($value);
				if ($value) {
					$q_array[] = stripslashes($value);
					$a_array[] = stripslashes($answer[$key]);
				}
			}
		}
		$config['question'] = is_array($q_array) ? $q_array : array();
		$config['answer'] = is_array($a_array) ? $a_array : array();

		if (($return = pwDir::rename($config['picpath'], $db_picpath, 'images', R_P)) !== true) {
			$tmpLang = array(1 => 'settings_picdir_dfnotfind', 2 => 'settings_picdir_error');
			adminmsg($tmpLang[$return]);
		}
		if (($return = pwDir::rename($config['attachname'], $db_attachname, 'attachment', R_P)) !== true) {
			$tmpLang = array(1 => 'settings_attdir_dfnotfind', 2 => 'settings_attdir_error');
			adminmsg($tmpLang[$return]);
		}
		if (($return = pwDir::rename($config['htmdir'], $db_htmdir, 'html', R_P)) !== true) {
			$tmpLang = array(1 => 'settings_htmdir_dfnotfind', 2 => 'settings_htmdir_error');
			adminmsg($tmpLang[$return]);
		}

		if (strpos($db_stopicdir, $db_htmdir) === 0) $db_stopicdir = substr($db_stopicdir, strlen($db_htmdir) + 1);
		if (($return = pwDir::rename($config['stopicdir'], $db_stopicdir, 'stopic', R_P . $config['htmdir'] . '/')) !== true) {
			$tmpLang = array(1 => 'settings_stopicdir_dfnotfind', 2 => 'settings_stopicdir_error');
			adminmsg($tmpLang[$return]);
		}
		$config['stopicdir'] = $config['htmdir'] . '/' . $config['stopicdir'];

		if (strpos($db_readdir, $db_htmdir) === 0) $db_readdir = substr($db_readdir, strlen($db_htmdir) + 1);
		if ($config['readdir'] && $db_readdir) {
			if (($return = pwDir::rename($config['readdir'], $db_readdir, 'read', R_P . $config['htmdir'] . '/')) !== true) {
				$tmpLang = array(1 => 'settings_readdir_dfnotfind', 2 => 'settings_readdir_error');
				adminmsg($tmpLang[$return]);
			}
		} elseif ($config['readdir'] && !$db_readdir) {
			@mkdir(R_P . $config['htmdir'] . '/' . $config['readdir'],  0777);
			if (!pwDir::move(R_P . $config['htmdir'] . '/' . $config['readdir'], R_P . $config['htmdir'])) {
				$config['readdir'] = '';
			}
		} elseif (!$config['readdir'] && $db_readdir) {
			if (pwDir::move(R_P . $config['htmdir'], R_P . $config['htmdir'] . '/' . $db_readdir)) {
				deldir(R_P . $config['htmdir'] . '/' . $db_readdir);
			}
		}
		$config['readdir'] = $config['htmdir'] . ($config['readdir'] ? '/' . $config['readdir'] : '');

		if ($config['http'] != 'N') {
			if (!$config['http']) {
				$config['http'] = 'N';
			} elseif (substr($config['http'], 0, 4) != 'http') {
				$config['http'] = $db_http ? $db_http : 'N';
			}
		}
		if ($config['attachurl'] != 'N') {
			if (!$config['attachurl']) {
				$config['attachurl'] = 'N';
			} elseif (substr($config['attachurl'], 0, 4) != 'http') {
				$config['attachurl'] = $db_attachurl ? $db_attachurl : 'N';
			}
		}
		if ($config['autochange'] && (!pwWritable(R_P . $config['picpath']) || !pwWritable(R_P . $config['attachname']))) {
			$config['autochange'] = 0;
		}
		//safe
		$config['iplimit'] = trim($config['iplimit'], ',');
		if (!$config['registerfile']) {
			if (file_exists('register.php')) {
				$config['registerfile'] = 'register.php';
			} else {
				adminmsg('settings_regfile_dfnotfind');
			}
		} elseif (!file_exists(R_P . $config['registerfile']) && $db_registerfile != $config['registerfile']) {
			!preg_match('/^[a-zA-Z][a-zA-Z0-9\_]+\.php$/is', $config['registerfile']) && adminmsg('settings_regfile_error');
			if (!$db_registerfile || !file_exists(R_P . $db_registerfile)) {
				if (file_exists('register.php')) {
					$db_registerfile = 'register.php';
				} else {
					adminmsg('settings_regfile_dfnotfind');
				}
			}
			if (!rename(R_P . $db_registerfile, R_P . $config['registerfile'])) {
				$config['registerfile'] = $db_registerfile;
			}
		}
		if (!$config['adminfile']) {
			if (file_exists('admin.php')) {
				$config['adminfile'] = 'admin.php';
			} else {
				adminmsg('settings_adminfile_dfnotfind');
			}
		} elseif (!file_exists(R_P . $config['adminfile']) && $db_adminfile != $config['adminfile']) {
			!preg_match('/^[a-zA-Z][a-zA-Z0-9\_]+\.php$/is', $config['adminfile']) && adminmsg('settings_adminfile_error');
			if (!$db_adminfile || !file_exists(R_P . $db_adminfile)) {
				if (file_exists('admin.php')) {
					$db_adminfile = 'admin.php';
				} else {
					adminmsg('settings_adminfile_dfnotfind');
				}
			}
			if (!rename(R_P . $db_adminfile, R_P . $config['adminfile'])) {
				$config['adminfile'] = $db_adminfile;
			}else{
				/*更新adminfile*/
				$adminFileChanged = true;
			}
		}
		unset($answer, $question, $safegroup, $gdcheck, $gdstyle, $gdsize, $qcheck);
	}
	if ($admintype == 'att' || $settingdb['att']) {
		//att
		InitGP(array('filetype', 'ftp'), 'P');
		InitGP(array('maxsize', 'athumbsize'), 'P', 2);

		$uploadfiletype = array();
		foreach ($filetype as $key => $value) {
			$value && $uploadfiletype[$value] = $maxsize[$key];
		}
		$config['uploadfiletype'] = serialize($uploadfiletype);
		unset($filetype, $maxsize, $uploadfiletype);
		//pic
		if ($config['watermark'] && (!function_exists('imagecreatefromgif') || !function_exists('imagettfbbox') || !function_exists('imagealphablending'))) {
			$config['watermark'] = 0;
		}
		$config['athumbsize'] = $athumbsize['athumbwidth'] . "\t" . $athumbsize['athumbheight'];
		@include_once (D_P . 'data/bbscache/ftp_config.php');
		if ($ftp['pass'] == substr($ftp_pass, 0, 1) . '********' . substr($ftp_pass, -1)) {
			$ftp['pass'] = $ftp_pass;
		}
	}
	if ($admintype == 'credit' || $settingdb['credit']) {
		require_once (R_P . 'require/credit.php');
		InitGP(array('creditpay', 'creditset', 'credit_name', 'credit_unit', 'credit_desc', 'cdiy_name', 'cdiy_unit',
			'cdiy_desc', 'cname1', 'cname2'), 'P');
		InitGP(array('creditlog', 'ccifopen', 'ccselid', 'cnum1', 'cnum2', 'cifopen'), 'P', 2);
		$config['creditset'] = $config['creditpay'] = $config['creditlog'] = '';

		$rt = $db->get_one("SELECT db_value FROM pw_config WHERE db_name='jf_A'");
		$jf_A = $rt['db_value'] ? unserialize($rt['db_value']) : array();
		foreach ($jf_A as $key => $value) {
			if (!isset($ccselid[$key])) {
				unset($jf_A[$key]);
			} else {
				$jf_A[$key][2] = isset($ccifopen[$key]) ? 1 : 0;
			}
		}
		if (is_array($cname1)) {
			foreach ($cname1 as $key => $value) {
				if ($value && isset($credit->cType[$value]) && $cname2[$key] && isset($credit->cType[$cname2[$key]])) {
					if ($value == $cname2[$key]) {
						adminmsg('bankset_save');
					}
					if ($cnum1[$key] <= 0 || $cnum2[$key] <= 0) {
						adminmsg('bankset_rate_error');
					}
					$jf_A[$value . '_' . $cname2[$key]] = array($cnum1[$key], $cnum2[$key], $cifopen[$key]);
				}
			}
		}
		$value = serialize($jf_A);
		if ($rt) {
			$db->update("UPDATE pw_config SET db_value=" . pwEscape($value, false) . " WHERE db_name='jf_A'");
		} else {
			$db->update("INSERT INTO pw_config SET db_name='jf_A',db_value=" . pwEscape($value, false));
		}

		$delcid = array();
		foreach ($_CREDITDB as $key => $value) {
			if (!isset($credit_name[$key])) {
				$delcid[] = $key;
			} elseif ($credit_name[$key] && ($value[0] != $credit_name[$key] || $value[1] != $credit_unit[$key] || $value[2] != $credit_desc[$key])) {
				$db->update("UPDATE pw_credits SET " . pwSqlSingle(array('name' => $credit_name[$key],
					'unit' => $credit_unit[$key], 'description' => $credit_desc[$key])) . " WHERE cid=" . pwEscape($key));
			}
		}
		if (!empty($delcid)) {
			$delcid = pwImplode($delcid);
			$config['showcustom'] = array();
			foreach ($db_showcustom as $value) {
				strpos($delcid, "'$value'") === false && $config['showcustom'][] = $value;
			}
			$db->update("DELETE FROM pw_credits WHERE cid IN($delcid)");
			$db->update("DELETE FROM pw_membercredit WHERE cid IN($delcid)");
		}
		if (is_array($cdiy_name)) {
			$pwSQL = array();
			foreach ($cdiy_name as $key => $value) {
				if ($value) {
					$pwSQL[] = array($value, $cdiy_unit[$key], $cdiy_desc[$key]);
				}
			}
			$pwSQL && $db->update("INSERT INTO pw_credits (name,unit,description) VALUES " . pwSqlMulti($pwSQL));
		}
		if (is_array($creditset) && !empty($creditset)) {
			foreach ($creditset as $key => $value) {
				foreach ($value as $k => $v) {
					$creditset[$key][$k] = round($v, ($k == 'rvrc' ? 1 : 0));
				}
			}
			$config['creditset'] = addslashes(serialize($creditset));
		}
		if (is_array($creditpay['name']) && !empty($creditpay['name'])) {
			$cpay = array();
			foreach ($creditpay['name'] as $key => $value) {
				if (isset($credit->cType[$value]) && !isset($cpay[$value])) {
					$cpay[$value] = array('rmbrate' => intval($creditpay['rmbrate'][$key]),
						'rmblest' => round($creditpay['rmblest'][$key], 2),
						'virement' => intval($creditpay['virement'][$key]));
				}
			}
			is_array($cpay) && !empty($cpay) && $config['creditpay'] = $cpay;
		}
		$config['creditlog'] = is_array($creditlog) ? $creditlog : array();
		unset($creditpay, $creditset, $creditlog);
	}
	if ($admintype == 'reg' || $settingdb['reg']) {
		if (!pwWritable(D_P . 'data/bbscache/dbreg.php') && !chmod(D_P . 'data/bbscache/dbreg.php', 0777)) {
			adminmsg('dbreg_777');
		}
		InitGP(array('reg'), 'P', 0);

		InitGP(array('namelen', 'pwdlen', 'regcredit'), 'P', 2);
		$reg['email'] = trim($reg['email'], ',');
		$reg['banemail'] = trim($reg['banemail'], ',');
		$reg['banname'] = trim($reg['banname'], ',');
		$reg['allowsameip'] = trim($reg['allowsameip'], ',');
		$reg['rgpermit'] = nl2br(ieconvert($reg['rgpermit']));
		$reg['welcomemsg'] = ieconvert($reg['welcomemsg']);
		$reg['whyregclose'] = ieconvert($reg['whyregclose']);
		if (abs($reg['timeend'] - $reg['timestart']) > 150) {
			adminmsg('reg_timelimit');
		}
		if ($namelen['max'] < 1 || $namelen['max'] > 15) {
			$namelen['max'] = 15;
		}
		if ($namelen['min'] < 1 || $namelen['min'] > $namelen['max']) {
			adminmsg('reg_username_limit');
		}
		if ($pwdlen['min'] < 1 || ($pwdlen['max'] && $pwdlen['min'] > $pwdlen['max'])) {
			adminmsg('reg_password_limit');
		}
		$reg['pwdlen'] = $pwdlen['min'] . "\t" . $pwdlen['max'];
		$reg['namelen'] = $namelen['min'] . "\t" . $namelen['max'];
		$reg['regcredit'] = is_array($regcredit) ? $regcredit : array();
		foreach ($reg['pwdcomplex'] as $key => $value) {
			if ((int) $value < 1) {
				unset($reg['pwdcomplex'][$key]);
			}
		}
		if (count($reg['pwdcomplex'])) {
			$reg['pwdcomplex'] = ',' . implode(',', $reg['pwdcomplex']) . ',';
		} else {
			$reg['pwdcomplex'] = '';
		}
		if ($reg['regmon'] == 0 && $reg['registertype'] == 1 || $reg['regweek'] == -1 && $reg['registertype'] == 2) {
			$reg['registertype'] = 0;
		}
		unset($namelen, $pwdlen, $regcredit);
	}
	if ($admintype == 'member' || $settingdb['member']) {
		InitGP(array('sellset', 'enhideset'), 'P');
		InitGP(array('windpost', 'upload', 'signgroup', 'logintype', 'fthumbsize', 'windpic'), 'P', 2);
		//post
		$config['sellset'] = is_array($sellset) ? $sellset : array();
		$config['windpic'] = is_array($windpic) ? $windpic : array();
		$config['windpost'] = is_array($windpost) ? $windpost : array();
		$config['windpost']['checkurl'] = $db_windpost['checkurl'];
		$config['enhideset'] = is_array($enhideset) ? $enhideset : array();
		(int) $config['postmax'] < 1 && $config['postmax'] = 50000;
		//face
		$upload['imglen'] < 1 && $upload['imglen'] = 160;
		$upload['imgsize'] < 1 && $upload['imgsize'] = 20;
		$upload['imgwidth'] < 1 && $upload['imgwidth'] = 160;
		$config['logintype'] = intval(array_sum($logintype));
		$config['signgroup'] = $signgroup ? ',' . implode(',', $signgroup) . ',' : '';
		$config['fthumbsize'] = $fthumbsize['fthumbwidth'] . "\t" . $fthumbsize['fthumbheight'];
		$config['upload'] = $upload['upload'] . "\t" . $upload['imglen'] . "\t" . $upload['imgwidth'] . "\t" . $upload['imgsize'];
	}
	if ($admintype == 'pcache' || $settingdb['pcache']) {
		//guest
		substr($config['guestdir'], -1) == '/' && $config['guestdir'] = substr($config['guestdir'], 0, -1);
	}
	if ($admintype == 'index' || $settingdb['index']) {
		InitGP(array('gpshow', 'gporder'), 'P', 2);
		if (is_array($gpshow)) {
			$showgroup = array();
			foreach ($gpshow as $key => $value) {
				$showgroup[$value] = $gporder[$key];
			}
			asort($showgroup);
			$showgroup = array_keys($showgroup);
			$config['showgroup'] = ',' . implode(',', $showgroup) . ',';
		} else {
			$config['showgroup'] = '';
		}
	}
	if ($admintype == 'thread' || $settingdb['thread']) {
		(int) $config['perpage'] < 1 && $config['perpage'] = 25;
	}
	if ($admintype == 'read' || $settingdb['read']) {
		InitGP(array('showcustom'), 'P');
		InitGP(array('floorname'), 'P');
		(int) $config['readperpage'] < 1 && $config['readperpage'] = 10;
		$config['anonymousname'] = str_replace(array('<', '>'), array('&lt;', '&gt;'), $config['anonymousname']);
		$config['showcustom'] = $showcustom ? (array) $showcustom : array();
		//$config['showcustom'] = $showcustom ? ','.implode(',',$showcustom).',' : '';


		$floorname = str_replace('，', ',', $floorname);
		if ($floorname = explode("\n", $floorname)) {
			$sFloor = 0;
			foreach ($floorname as $key => $value) {
				if ($tmpArr = explode(",", trim($value))) {
					foreach ($tmpArr as $v) {
						if (preg_match('/(\d+):(.*)/i', $v, $matches)) {
							$sFloor = $matches[1];
							$v = $matches[2];
						}
						if ($v = trim($v)) {
							$floors[$sFloor] = $v;
						}
						$sFloor++;
					}
				}
			}
		} else {
			$floors = array();
		}
		ksort($floors);
		$config['floorname'] = is_array($floors) ? $floors : array();
	}
	if ($admintype == 'email' || $settingdb['email']) {
		InitGP(array('mail', 'ajaxaction', 'toemail', 'fromemail', 'sitemsg'), 'P');
		InitGP(array('wapfids'), 'P', 2);
		if ($ajaxaction == 'emailcheck') {
			require_once (R_P . 'require/sendemail.php');
			$sendinfo = sendemail($toemail, 'emailcheck_subject', 'emailcheck_content');
			if ($sendinfo === true) {
				adminmsg('email_success');
			} else {
				Showmsg(is_string($sendinfo) ? $sendinfo : 'email_fail', 1);
			}
		} else {
			//mail
			@include_once (D_P . 'data/bbscache/mail_config.php');
			$s_ml_smtppass = substr($ml_smtppass, 0, 1) . '********' . substr($ml_smtppass, -1);
			$mail['smtppass'] = $s_ml_smtppass == $mail['smtppass'] ? $ml_smtppass : $mail['smtppass'];
			(int) $mail['smtpport'] < 1 && $mail['smtpport'] = 25;
			/*
			//wap
			$config['wapfids'] = implode(',',$wapfids);

			//js
			$config['bindurl'] = trim($config['bindurl'],',');
			$sitemsg['reg'] = explode("\n",$sitemsg['reg']);
			$sitemsg['login'] = explode("\n",$sitemsg['login']);
			$sitemsg['post'] = explode("\n",$sitemsg['post']);
			$sitemsg['reply'] = explode("\n",$sitemsg['reply']);
			$config['sitemsg'] = $sitemsg ? addslashes(serialize($sitemsg)) : '';
			*/
		}
	}
	if ($admintype == 'popinfo' || $settingdb['popinfo']) {
		InitGP(array('sitemsg'), 'P');
		$config['bindurl'] = trim($config['bindurl'], ',');
		$sitemsg['reg'] = explode("\n", $sitemsg['reg']);
		$sitemsg['login'] = explode("\n", $sitemsg['login']);
		$sitemsg['post'] = explode("\n", $sitemsg['post']);
		$sitemsg['reply'] = explode("\n", $sitemsg['reply']);
		$config['sitemsg'] = is_array($sitemsg) ? $sitemsg : array();
	}
	if ($admintype == 'wap' || $settingdb['wap']) {
		InitGP(array('wapfids'), 'P');
		$config['wapfids'] = implode(',', $wapfids);
	}
	if ($admintype == 'searcher' || $settingdb['searcher']) {
		InitGP(array('schctl'));
		$schctl['schstart'] > 23 && $schctl['schstart'] = 0;
		$schctl['schend'] > 23 && $schctl['schend'] = 0;
		$config['opensch'] = $schctl['opensch'] . "\t" . $schctl['schstart'] . "\t" . $schctl['schend'];
		$config['maxresult'] = intval($config['maxresult']);
		$config['schwait'] = intval($config['schwait']);
		$config['hotwords'] = trim($config['hotwords']);
		$config['filterids'] = trim($config['filterids']);
		if ($config['filterids']) {
			$filterids = explode(",", $config['filterids']);
			foreach ($filterids as $id) {
				$id = intval($id);
				if ($id < 1) {
					adminmsg('搜索过滤版块ID不能为字符');
				}
			}
			$config['filterids'] = implode(',',$filterids);
		}
	}
	$configdb = array();
	$temppre = array('config' => 'db_', 'ftp' => 'ftp_', 'reg' => 'rg_', 'mail' => 'ml_');
	foreach ($temppre as $key => $pre) {
		$key_a = ${$key};
		if (is_array($key_a)) {
			foreach ($key_a as $k => $value) {
				$var = $pre . $k;
				$vtype = 'string';
				if (is_array($value)) {
					$vtype = 'array';
					$value = serialize($value);
				}
				$configdb[$var] = array($var, $vtype, $value);
			}
		}
	}
	if (!empty($configdb)) {
		$names = array_keys($configdb);
		$query = $db->query('SELECT db_name,vtype,db_value FROM pw_config WHERE db_name IN (' . pwImplode($names, false) . ')');
		while ($rt = $db->fetch_array($query)) {
			if (isset($configdb[$rt['db_name']])) {
				if ($rt['db_value'] != $configdb[$rt['db_name']]) {
					$db->update("UPDATE pw_config SET " . pwSqlSingle(array(
						'db_value' => $configdb[$rt['db_name']][2], 'vtype' => $configdb[$rt['db_name']][1])) . ' WHERE db_name=' . pwEscape($rt['db_name']));
				}
				$configdb[$rt['db_name']] = '';
			}
		}
		$db->free_result($query);
		$pwSqlMulti = pwSqlMulti($configdb);
		$pwSqlMulti && $db->update('INSERT INTO pw_config (db_name,vtype,db_value) VALUES' . $pwSqlMulti);
		updatecache_c();
		if (!empty($ftp)) {
			updatecache_ftp();
		}
		if (!empty($mail)) {
			updatecache_ml();
		}
	}
	if($adminFileChanged){
		/*@fix 更改admin_file后引起的的404错误 */
		echo '<script language="JavaScript">parent.location.href = "'.$config['adminfile'].'";</script>';
	}else{
		adminmsg('operate_success');
	}
}


class pwDir {

	function isDir($path, $isCreate = true) {
		if ($isCreate && !is_dir($path)) {
			@mkdir($path, 0777);
		}
		return is_dir($path);
	}

	function rename(&$srcDir, $dstDir, $defaultDir, $baseDir) {
		if (!$srcDir) {
			if (!pwDir::isDir($baseDir . $defaultDir, $baseDir != R_P)) return 1;
			$srcDir = $defaultDir;
			return true;
		}
		if (!is_dir($baseDir . $srcDir) && $dstDir != $srcDir) {
			if (!preg_match('/^[a-zA-Z0-9\_]+$/i', $srcDir)) return 2;
			if (!$dstDir || !is_dir($baseDir . $dstDir)) {
				if (!pwDir::isDir($baseDir . $defaultDir, $baseDir != R_P)) return 1;
				$dstDir = $defaultDir;
			}
			if (!rename($baseDir . $dstDir, $baseDir . $srcDir)) {
				$srcDir = $dstDir;
			}
		}
		return true;
	}

	function move($srcDir, $dstDir) {
		if (!is_dir($srcDir)) return false;
		$fp = opendir($dstDir);
		while (false !== ($file = readdir($fp))) {
			if (is_dir($dstDir . '/' . $file) && is_numeric($file)) {
				rename($dstDir . '/' . $file, $srcDir . '/' . $file);
			}
		}
		return true;
	}
}
?>