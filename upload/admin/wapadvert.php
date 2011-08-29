<?php
!defined('P_W') && exit('Forbidden');
InitGP(array('ckey'));
$cates = array();
$query = $db->query("SELECT id,ckey,uid,ifshow,descrip,config FROM pw_advert WHERE type=2 AND ifshow=1 ORDER BY id");
$advertypes = array('txt','img');
while ($rt = $db->fetch_array($query)) {
	list($rt['name'],$rt['descrip']) = explode("~\t~",$rt['descrip']);
	$rt['config'] = unserialize($rt['config']);
	$cates[$rt['ckey']] = $rt;
	$selected = ($ckey == $rt['ckey']) ? "selected=selected" : "";
	$optCates .= "<option value=\"{$rt['ckey']}\" {$selected}>{$rt['name']}</option>";
	$cateDescrip[strtolower($rt['ckey'])] = addslashes($rt['descrip']);
}
$cateDescrip = pwJsonEncode($cateDescrip);
if(empty($action)){
	InitGP(array('keyword','page','advertype','state'));
	$sql = '';$ids = array();
	$ckey && $sql .= " AND ckey=".pwEscape($ckey);
	$keyword && $sql .= " AND descrip LIKE ".pwEscape("%$keyword%");
	(in_array($advertype,$advertypes)) && $sql .= " AND config LIKE '%". $advertype ."%'";
	((string)$state == '0' || (string)$state == '1') && $sql .= " AND ifshow = " . pwEscape($state);
	$count = $db->get_value("SELECT COUNT(*) FROM pw_advert WHERE type=3 $sql");
	$page<1 && $page = 1;
	$limit = pwLimit(($page-1)*$db_perpage,$db_perpage);
	$pages = numofpage($count,$page,ceil($count/$db_perpage), "$basename&ckey=$ckey&advertype=$advertype&adverstatus=$adverstatus&keyword=".rawurlencode($keyword).'&');
	$query = $db->query("SELECT * FROM pw_advert WHERE type=3 $sql ORDER BY id DESC $limit");
	$adverts = array();
	while ($rt = $db->fetch_array($query)) {
		if ($rt['etime'] < $timestamp) {
			if(get_date($rt['etime'],'Y-m-d') != get_date($timestamp,'Y-m-d')){
				$ids[] = $rt['id'];
				$rt['ifshow'] = 0;
			}
		}
		$rt['config'] = unserialize($rt['config']);
		$rt['etime'] = get_date($rt['etime'],'Y-m-d');
		$adverts[$rt['id']] = $rt;
	}
}elseif($action == 'add'){
	require_once(R_P.'require/credit.php');
	include_once(D_P.'data/bbscache/forumcache.php');
	$advert = array(
		'stime'	=> get_date($timestamp,'Y-m-d'),
		'etime'	=> get_date($timestamp + 31536000,'Y-m-d'),
	);
	$config['type'] = 'txt';
	$advert['orderby'] = 0;
	$type_txt = $ifshow_Y = 'checked';
	$selThread_page = $selFids_all = 'selected';
}elseif ($action == 'edit') {
		require_once(R_P.'require/credit.php');
		include_once(D_P.'data/bbscache/forumcache.php');
		InitGP(array('id'));
		$advert = $db->get_one("SELECT * FROM pw_advert WHERE type=3 AND id=".pwEscape($id));
		!$advert && adminmsg('advert_id_error');
		$config = unserialize($advert['config']);
		HtmlConvert($advert);
		HtmlConvert($config);

		$advert['etime'] = get_date($advert['etime'],'Y-m-d');
		$advert['stime'] = get_date($advert['stime'],'Y-m-d');

		ifcheck($advert['ifshow'],'ifshow');

		${'type_'.$config['type']} = 'checked';
		
		/* bug fixed lh*/
		$optCates = '';
		foreach($cates as $v){
			$selected = ($advert['ckey'] == $v['ckey']) ? "selected=selected" : "";
			$optCates .= "<option value=\"{$v['ckey']}\" ".$selected.">".$v['name']."</option>";
		}

		$CreditList = '';
		foreach ($credit->cType as $key => $value) {
			$CreditList	.= "<option value=\"$key\"".($config['creditype']==$key ? ' selected' : '').">$value</option>";
		}

		if ($config['mode']) {
			$modes = explode(',',$config['mode']);
			foreach ($modes as $v) {
				${'mode_'.$v} = 'selected';
			}
		} else {
			$selThread_mode = 'selected';
		}
		if ($config['fid']){
			$fids = explode(',',$config['fid']);
			foreach ($fids as $v) {
				$forumcache = str_replace("<option value=\"$v\">","<option value=\"$v\" selected>",$forumcache);
			}
		} else {
			$selFids_all = 'selected';
		}
		if ($config['page']) {
			$pages = explode(',',$config['page']);
			foreach ($pages as $v) {
				${'selThread_'.$v} = 'selected';
			}
		} else {
			$selThread_page = 'selected';
		}
		include PrintEot('wapadvert');exit;
}elseif($action == 'save'){
	InitGP(array('id','config','advert','fids','pages'));
	$id = intval($id);
	if ($config['type'] == 'txt') {
		if (!$config['title'] || !$config['link']) {
			$basename = "javascript:history.go(-1);";
			adminmsg('advert_txt_error');
		}
		$config['title'] = str_replace(array('&lt;','&gt;'),array('<','>'),$config['title']);
	} elseif ($config['type'] == 'img' && (!$config['url'] || !$config['link'])) {
		$basename = "javascript:history.go(-1);";
		adminmsg('advert_img_error');
	}
	if (empty($advert['descrip'])) {
		if ($config['type'] == 'txt') {
			$advert['descrip'] = substrs($config['title'],250);
		}
		empty($advert['descrip']) && $basename = "javascript:history.go(-1);" && adminmsg('advert_descrip');
	}
	$advert['stime'] = PwStrtoTime($advert['stime']);
	$advert['etime'] = PwStrtoTime($advert['etime']);

	if ($advert['stime'] > $advert['etime']) {
		$basename = "javascript:history.go(-1);";
		adminmsg('advert_time_error');
	}
	
	$advert['orderby'] = (int)$advert['orderby'];
	$advert['ifshow'] = $advert['ifshow'] ? 1 : 0;
	
	if (is_array($fids) && !in_array('-1',$fids)) {
		$config['fid'] = implode(',',$fids);
	}
	if (is_array($pages) && !in_array('page',$pages)) {
		$config['page'] = implode(',',$pages);
	}
	
	foreach ( $config as $key => $value ) {
		if ($config ['type'] == 'img' && in_array ( $key, array ('url', 'link' ) )) {
			$tmp = array (); /*support multi pictures*/
			$config ['multi'] = count ( $value );
			$index = 0; /* sort array */
			foreach ( $value as $k => $v ) {
				$tmp [$index] = stripslashes ( str_replace ( array ('&#61;', '&amp;' ), array ('=', '&' ), $v ) );
				$index ++;
			}
			if ($config ['multi'] > 1) {
				$value = $tmp;
			} else {
				$value = $tmp [0];
			}
		} else {
			$value = stripslashes ( str_replace ( array ('&#61;', '&amp;' ), array ('=', '&' ), $value ) ); /*other*/
		}
		$config [$key] = is_array ( $value ) ? $value : stripslashes ( $value );
	}
	
	$config = addslashes ( serialize ( $config ) );
	
	if ($id) {
		$db->update("UPDATE pw_advert SET " . pwSqlSingle(array(
			'ckey'		=> $advert['ckey'],
			'stime'		=> $advert['stime'],
			'etime'		=> $advert['etime'],
			'ifshow'	=> $advert['ifshow'],
			'orderby'	=> $advert['orderby'],
			'descrip'	=> $advert['descrip'],
			'config'	=> $config
		)) . " WHERE type='3' AND id=".pwEscape($id));
	} else {
		$otherkey = (array)GetGP('otherkey');
		$winduid = $db->get_value("SELECT uid FROM pw_members WHERE username=".pwEscape($admin_name,false));/*administrator*/
		$db->update("INSERT INTO pw_advert SET " . pwSqlSingle(array(
			'uid'       => $winduid,
			'type'		=> 3,
			'ckey'		=> $advert['ckey'],
			'stime'		=> $advert['stime'],
			'etime'		=> $advert['etime'],
			'ifshow'	=> $advert['ifshow'],
			'orderby'	=> $advert['orderby'],
			'descrip'	=> $advert['descrip'],
			'config'	=> $config
		)));
		$id = $db->insert_id();
	}
	
	if ($advert['ifshow']) {
		$db->update("UPDATE pw_advert SET ifshow=1 WHERE type=2 AND ifshow=0 AND ckey=".pwEscape($advert['ckey']));
	}
	updatecache_c();
	$basename .= $id ? "&action=edit&id=$id" : "&action=add";
	adminmsg('operate_success');
}elseif (in_array($action,array('del','show','hide'))) {
	InitGP(array('selid','id'));
	$selid = ($selid) ? $selid : array($id);//only for array
	$selid = is_array($selid) ? $selid : array($selid);
	if (!$selid = checkselid($selid)) {
		adminmsg('operate_error',"$basename");
	}
	switch ($action) {
		case 'del':
			$db->update("DELETE FROM pw_advert WHERE type=3 AND id IN ($selid) AND id>100");
		break;
		case 'show':
			$db->update("UPDATE pw_advert SET ifshow=1 WHERE type=3 AND id IN ($selid)");
		break;
		case 'hide':
			$db->update("UPDATE pw_advert SET ifshow=0 WHERE type=3 AND id IN ($selid)");
		break;
	}

	updatecache_c();
	adminmsg('operate_success',"$basename");
}

include PrintEot('wapadvert');exit;
?>