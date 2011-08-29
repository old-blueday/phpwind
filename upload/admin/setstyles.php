<?php
!function_exists('adminmsg') && exit('Forbidden');
$basename="$admin_file?adminjob=setstyles";
require_once S::escapePath(R_P.'require/forum.php');

if (!$action) {
	ifcheck($db_showcss,'showcss');
	$allstyles = getAllStyles();
	include PrintEot('setstyles');exit;

} elseif ($action == 'listedit') {
	S::gp(array('ifopen','defaultstyle'));
	S::gp(array('ifopen'),'GP',2);
	$customname = S::getGP('customname');
	$styledb = array();
	$allstyles = getAllStyles();
	foreach ($allstyles as $key => $value) {
		empty($customname[$key]) && $customname[$key] = '';
		if (empty($ifopen[$key])) {
			$ifopen[$key] = '0';
			//$db->update("UPDATE pw_forums SET style='' WHERE style=".S::sqlEscape($key,false));
			pwQuery::update('pw_forums', 'style=:style', array($key), array('style' => ''));
		}
		$ifopen[$key] == '0' && $key == $defaultstyle && $defaultstyle = 'wind';
		//* include_once S::escapePath(D_P."data/style/$key.php");
		extract(pwCache::getData(S::escapePath(D_P."data/style/$key.php"), false));
		//include S::escapePath(D_P."data/style/$key.php");
		$ckstyle = $db->get_value("SELECT sid FROM pw_styles WHERE name=".S::sqlEscape($key,false)." AND uid='0'");
		if ($ckstyle){
			$db->update("UPDATE pw_styles SET ".S::sqlSingle(array('customname'=>$customname[$key],'ifopen'=>$ifopen[$key]))."WHERE name=".S::sqlEscape($key,false)." AND uid='0'");
		} else {
			$db->update("INSERT INTO pw_styles"
			. " SET " . S::sqlSingle(array(
				'name'			=> $key,
				'customname'    => $customname[$key],
				'ifopen'  		=> $ifopen[$key],
				'stylepath'		=> $stylepath,
				'tplpath'		=> $tplpath,
				'yeyestyle'		=> $yeyestyle,
				'bgcolor'		=> $bgcolor,
				'linkcolor'		=> $linkcolor,
				'tablecolor'	=> $tablecolor,
				'tdcolor'		=> $tdcolor,
				'tablewidth'	=> $tablewidth,
				'mtablewidth'	=> $mtablewidth,
				'headcolor'		=> $headcolor,
				'headborder'	=> $headborder,
				'headfontone'	=> $headfontone,
				'headfonttwo'	=> $headfonttwo,
				'cbgcolor'		=> $cbgcolor,
				'cbgborder'		=> $cbgborder,
				'cbgfont'		=> $cbgfont,
				'forumcolorone'	=> $forumcolorone,
				'forumcolortwo'	=> $forumcolortwo,
				'extcss'		=> $extcss
			)));
		}
		$ifopen[$key] == 1 && $styledb[$key] = array($customname[$key],$ifopen[$key],$tplpath);
	}
	setConfig('db_styledb', $styledb);
	setConfig('db_defaultstyle', $defaultstyle);

	updatecache_c();
	adminmsg('operate_success');

} elseif ($action == 'edit') {

	S::gp(array('sid'));

	if (!$_POST['step']) {

		//* include_once S::escapePath(D_P."data/style/$sid.php");
		extract(pwCache::getData(S::escapePath(D_P."data/style/$sid.php"), false));
		ifcheck($yeyestyle,'yes');
		$css_777   = pwWritable(D_P."data/style/{$tplpath}_css.htm") ? 1 : 0;
		$style_css = pwCache::readover(D_P."data/style/{$tplpath}_css.htm");
		$style_css = explode('<!--css-->',$style_css);
		$style_css = str_replace('$',"\$",$style_css[1]);

		include PrintEot('setstyles');exit;
	} else {
		S::gp(array('setting'),'P');
		$basename .= "&action=edit&sid=$sid";
		strpos($setting[7],'%')===false && strpos(strtolower($setting[7]),'px')===false && $setting[7].='px';
		strpos($setting[8],'%')===false && strpos(strtolower($setting[8]),'px')===false && $setting[8].='px';
		$rs = $db->get_one("SELECT sid FROM pw_styles WHERE name=".S::sqlEscape($sid,false));
		if ($rs) {
			$db->update("UPDATE pw_styles"
				. " SET " . S::sqlSingle(array(
						'stylepath'		=> $setting[0],
						'tplpath'		=> $setting[1],
						'yeyestyle'		=> $setting[2],
						'bgcolor'		=> $setting[3],
						'linkcolor'		=> $setting[4],
						'tablecolor'	=> $setting[5],
						'tdcolor'		=> $setting[6],
						'tablewidth'	=> $setting[7],
						'mtablewidth'	=> $setting[8],
						'headcolor'		=> $setting[9],
						'headborder'	=> $setting[10],
						'headfontone'	=> $setting[11],
						'headfonttwo'	=> $setting[12],
						'cbgcolor'		=> $setting[13],
						'cbgborder'		=> $setting[14],
						'cbgfont'		=> $setting[15],
						'forumcolorone'	=> $setting[16],
						'forumcolortwo'	=> $setting[17],
						'extcss'		=> $setting[18]
					))
				. ' WHERE name='.S::sqlEscape($sid));
		} else {
			$db->update("INSERT INTO pw_styles"
				. " SET " . S::sqlSingle(array(
					'name'			=> $sid,
					'ifopen'		=> '1',
					'stylepath'		=> $setting[0],
					'tplpath'		=> $setting[1],
					'yeyestyle'		=> $setting[2],
					'bgcolor'		=> $setting[3],
					'linkcolor'		=> $setting[4],
					'tablecolor'	=> $setting[5],
					'tdcolor'		=> $setting[6],
					'tablewidth'	=> $setting[7],
					'mtablewidth'	=> $setting[8],
					'headcolor'		=> $setting[9],
					'headborder'	=> $setting[10],
					'headfontone'	=> $setting[11],
					'headfonttwo'	=> $setting[12],
					'cbgcolor'		=> $setting[13],
					'cbgborder'		=> $setting[14],
					'cbgfont'		=> $setting[15],
					'forumcolorone'	=> $setting[16],
					'forumcolortwo'	=> $setting[17],
					'extcss'		=> $setting[18]
			)));
		}
		updatecache_sy($sid);
		adminmsg('operate_success');
	}
/*
} elseif ($_POST['action'] == 'editcss') {

	S::gp(array('sid'),'P');
	S::gp(array('style_css'),'P',0);

	$basename .= "&action=edit&sid=$sid";
	include_once S::escapePath(D_P."data/style/$sid.php");
	if (!pwWritable(D_P."data/style/{$tplpath}_css.htm")) {
		adminmsg('style_777');
	}
	$cssadd    = readover(D_P."data/style/{$tplpath}_css.htm");
	$cssadd    = explode('<!--css-->',$cssadd);
	$style_css = str_replace('EOT','',$style_css);
	$style_css = str_replace("$","\$",$cssadd[0].'<!--css-->'.$style_css.'<!--css-->'.$cssadd[2]);
	$style_css = stripslashes($style_css);
	writeover(D_P."data/style/{$tplpath}_css.htm",$style_css);
	updatecache_sy($sid);
	adminmsg('operate_success');
*/
} elseif ($_POST['action'] == 'setcss') {

	S::gp(array('showcss'));
	
	setConfig('db_showcss', $showcss);
	updatecache_c();

	if ($showcss) {
		updatecache_sy();
	}
	adminmsg('operate_success');

} elseif ($action == 'add') {

	if (!$_POST['step']) {

		$yes_Y = 'checked';
		include PrintEot('setstyles');exit;

	} else {

		S::gp(array('setting'),'P');
		$setting[0] = S::escapeChar($setting[0]);
		if (empty($setting[0])) {
			adminmsg('style_empty');
		} elseif (file_exists(D_P."data/style/$setting[0].php")) {
			adminmsg('style_exists');
		}
		strpos($setting[7],'%')===false && strpos(strtolower($setting[7]),'px')===false && $setting[7].='px';
		strpos($setting[8],'%')===false && strpos(strtolower($setting[8]),'px')===false && $setting[8].='px';
		$db->update("INSERT INTO pw_styles (name,stylepath,tplpath,yeyestyle,bgcolor,linkcolor,tablecolor,tdcolor,tablewidth,mtablewidth,headcolor,headborder,headfontone,headfonttwo,cbgcolor,cbgborder,cbgfont,forumcolorone,forumcolortwo,extcss) VALUES ('$setting[0]','$setting[0]','$setting[1]','$setting[2]','$setting[3]','$setting[4]','$setting[5]','$setting[6]','$setting[7]','$setting[8]','$setting[9]','$setting[10]','$setting[11]','$setting[12]','$setting[13]','$setting[14]','$setting[15]','$setting[16]','$setting[17]','$setting[18]')");
		updatecache_sy($setting[0]);
		adminmsg('style_add_success');
	}
} elseif ($action == 'del') {

	PostCheck($verify);
	S::gp(array('sid'));
	if ($sid == $skin) {
		adminmsg('style_del_error');
	}
	$db->update("DELETE FROM pw_styles WHERE name=".S::sqlEscape($sid,false));

	if (file_exists(D_P."data/style/$sid.php")) {
		if (P_unlink(D_P."data/style/$sid.php")) {
			P_unlink(D_P."data/style/{$sid}_css.htm");
			unset($db_styledb[$sid]);
			setConfig('db_styledb', $db_styledb);
			updatecache_c();
			adminmsg('operate_success');
		} else {
			adminmsg('operate_fail');
		}
	} else {
		adminmsg('style_not_exists');
	}
}

function getAllStyles(){
	global $db;
	$styles = array();
	$query = $db->query("SELECT name,customname,ifopen FROM pw_styles WHERE uid='0'");
	while($rt = $db->fetch_array($query)){
		$styledb[$rt['name']] = array(S::escapeChar($rt['customname']),$rt['ifopen']);
	}
	$fp = opendir(D_P."data/style/");
	while ($skinfile = readdir($fp)) {
		if (eregi("\.php$",$skinfile)) {
			$skinfile = str_replace(".php","",$skinfile);
			if ($styledb[$skinfile]) {
				$styles[$skinfile] = $styledb[$skinfile];
			} else {
				$styles[$skinfile] = array('','0');
			}
		}
	}
	closedir($fp);
	return $styles;
}
?>