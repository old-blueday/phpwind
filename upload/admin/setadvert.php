<?php
!function_exists('adminmsg') && exit('Forbidden');
InitGP(array('action','job'));
if (!$action) {
	InitGP(array('ckey','advertype','adverstatus'));/*hold*/
	$cates = $cateDescrip = array();$optCates = '';
	$query = $db->query("SELECT id,ckey,uid,ifshow,descrip,config FROM pw_advert WHERE type=0 AND ifshow=1 ORDER BY id");
	while ($rt = $db->fetch_array($query)) {
		list($rt['name'],$rt['descrip']) = explode("~\t~",$rt['descrip']);
		$rt['ifhire'] = $rt['uid'];
		$rt['config'] = unserialize($rt['config']);
		$cates[$rt['ckey']] = $rt;
		$selected = ($ckey == $rt['ckey']) ? "selected=selected" : "";
		$optCates .= "<option value=\"{$rt['ckey']}\" {$selected}>{$rt['name']}</option>";
		$cateDescrip[strtolower($rt['ckey'])] = addslashes($rt['descrip']);
		//$cateDescrip .= "'".addslashes(strtolower($rt['ckey']))."' : '".addslashes($rt['descrip'])."',";
	}
	$cateDescrip = pwJsonEncode($cateDescrip);
	
	$adverClass = L::loadclass('adver', 'advertisement');/*search*/
	$adverTypeSelect = $adverClass->buildTypeSelect($advertype);
	$adverStatusSelect = $adverClass->buildStatusSelect($adverstatus);
	$ckeySelect = $adverClass->getAdverBenchSelect($ckey,'ckey','ckey');
	
	if (empty($job)) {
		InitGP(array('ckey','keyword','page'));
		$sql = '';$ids = array();
		$ckey && $sql .= " AND ckey=".pwEscape($ckey);
		$keyword && $sql .= " AND descrip LIKE ".pwEscape("%$keyword%");
		(in_array($advertype,array_keys($adverClass->getType()))) && $sql .= " AND config LIKE ".pwEscape("%\"".$advertype."\";%");
		(in_array($adverstatus,array_keys($adverClass->getStatus())) && $adverstatus != '' ) && $sql .= " AND ifshow=".pwEscape($adverstatus);
		$count = $db->get_value("SELECT COUNT(*) FROM pw_advert WHERE type=1 $sql");
		$page<1 && $page = 1;
		$limit = pwLimit(($page-1)*$db_perpage,$db_perpage);
		$pages = numofpage($count,$page,ceil($count/$db_perpage), "$basename&ckey=$ckey&advertype=$advertype&adverstatus=$adverstatus&keyword=".rawurlencode($keyword).'&');
		
		$query = $db->query("SELECT * FROM pw_advert WHERE type=1 $sql ORDER BY id DESC $limit");
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
		if ($ids) {
			$db->update("UPDATE pw_advert SET ifshow=0 WHERE ifshow=1 AND id IN(".pwImplode($ids,false).")");
			updatecache_c();
		}
	} elseif ($job == 'add') {
		require_once(R_P.'require/credit.php');
		include_once(D_P.'data/bbscache/forumcache.php');
		$advert = array(
			'stime'	=> get_date($timestamp,'Y-m-d'),
			'etime'	=> get_date($timestamp + 31536000,'Y-m-d'),
		);
		$config['type'] = 'txt';
		$config['winHeight'] = 100;
		$config['winWidth'] = 200;
		$config['winClose'] = 5;
		$advert['orderby'] = 0;
		$config['link'] = 'http://';
		$type_txt = $ifshow_Y = 'checked';
		$showddate = '';
		$selThread_mode = $selThread_page = $selFids_all = $selLou_all = 'selected';

		include_once PrintEot('setadvert');exit;
	} elseif ($job == 'edit') {
		require_once(R_P.'require/credit.php');
		include_once(D_P.'data/bbscache/forumcache.php');
		InitGP(array('id'));
		$advert = $db->get_one("SELECT * FROM pw_advert WHERE type=1 AND id=".pwEscape($id));
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
		//$optCates = str_replace("<option value=\"{$advert['ckey']}\">","<option value=\"{$advert['ckey']}\" selected>",$optCates);

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
		//$forum_cate_select = preg_replace("/<option[^>]+>.+\|\-.+<\/option>/i", "", $forumcache);
		if ($config['page']) {
			$pages = explode(',',$config['page']);
			foreach ($pages as $v) {
				${'selThread_'.$v} = 'selected';
			}
		} else {
			$selThread_page = 'selected';
		}
		if ($config['lou']) {
			$lous = explode(',',$config['lou']);
			foreach ($lous as $v) {
				${'lou_'.$v} = 'selected';
			}
		} else {
			$selLou_all = 'selected';
		}
		$config['ddate'] = $config['ddate'] ? str_replace(',',':true,',$config['ddate']) . ':true' : '';
		$config['dweek'] = $config['dweek'] ? str_replace(',',':true,',$config['dweek']) . ':true' : '';
		$config['dtime'] = $config['dtime'] ? str_replace(',',':true,',$config['dtime']) . ':true' : '';
		if ($config['ddate'] || $config['dweek'] || $config['dtime']) {
			$showddate = "{days:{".$config['ddate']."},weeks:{".$config['dweek']."},hours:{".$config['dtime']."}}";
		} else {
			$showddate = '';
		}
		
		/*multi pictures support*/
		$total = '';
		if($config['type'] == 'img'){
			if($config['multi']<=1){
				$config['link'] = array($config['link']);
				$config['url'] = array($config['url']);
			}
			$total = count($config['url']);
		}
		include_once PrintEot('setadvert');exit;
	} elseif ($job == 'save') {
		InitGP(array('id','config','advert','lous','fids','modes','pages','ddate','dweek','dtime'));

		$id = intval($id);
		$basename .= $id ? "&job=edit&id=$id" : "&job=add";

		!isset($cates[$advert['ckey']]) && $basename = "javascript:history.go(-1);" && adminmsg('advert_ckey_noexist');
		if ($config['type'] == 'code') {
			$tmpConfig = GetGP('config');
			$config['htmlcode'] = $tmpConfig['htmlcode'];
			if (!$config['htmlcode'] || strlen($config['htmlcode'])>1024) {
				$basename = "javascript:history.go(-1);";
				adminmsg('advert_code_error');
			} elseif(in_array($advert['ckey'],array('Site.FloatLeft','Site.FloatRight','Site.FloatRand')) && preg_match('/<script[^>]*?>.*?<\/script>/si',$config['htmlcode'])) {
				$basename = "javascript:history.go(-1);";
				adminmsg('advert_float_error');
			}
		} elseif ($config['type'] == 'txt') {
			if (!$config['title'] || !$config['link']) {
				$basename = "javascript:history.go(-1);";
				adminmsg('advert_txt_error');
			}
			$config['title'] = str_replace(array('&lt;','&gt;'),array('<','>'),$config['title']);
		} elseif ($config['type'] == 'img' && (!$config['url'] || !$config['link'])) {
			$basename = "javascript:history.go(-1);";
			adminmsg('advert_img_error');
		} elseif ($config['type'] == 'flash' && !$config['link']) {
			$basename = "javascript:history.go(-1);";
			adminmsg('advert_flash_error');
		}
		if (empty($advert['descrip'])) {
			if ($config['type'] == 'code') {
				$advert['descrip'] = substrs(strip_tags($config['htmlcode']),250);
			} elseif ($config['type'] == 'txt') {
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
		
		$config['ddate'] = $config['dweek'] = $config['dtime'] = '';
		if (is_array($ddate)) {
			$config['ddate'] = implode(',',$ddate);
		}
		if (is_array($dweek)) {
			$config['dweek'] = implode(',',$dweek);
		}
		if (is_array($dtime) && count($dtime)<24) {
			$config['dtime'] = implode(',',$dtime);
		}
		$advert['orderby'] = (int)$advert['orderby'];
		$advert['ifshow'] = $advert['ifshow'] ? 1 : 0;

		$tmpCKey = strtolower($advert['ckey']);
		if (strpos($tmpCKey,'popup.')) {
			$config['winHeight'] = $config['winHeight'] ? intval($config['winHeight']) : 100;
			$config['winWidth'] = $config['winWidth'] ? intval($config['winWidth']) : 200;
			$config['winClose'] = $config['winClose'] ? intval($config['winClose']) : 5;
		}
		if (strpos($tmpCKey,'layer.') && is_array($lous) && !in_array('-1',$lous)) {
			$config['lou'] = implode(',',$lous);
		}

		if (is_array($fids) && !in_array('-1',$fids)) {
			$config['fid'] = implode(',',$fids);
		}
		if (is_array($pages) && !in_array('page',$pages)) {
			$config['page'] = implode(',',$pages);
		}
		if (is_array($modes) && !in_array('mode',$modes)) {
			$config['mode'] = implode(',',$modes);
		}

		if ($config['width']) {
			$config['width'] = intval($config['width']) . ($config['width'][strlen($config['width'])-1] == '%' ? '%' : '');
		}
		if ($config['height']) {
			$config['height'] = intval($config['height']) . ($config['height'][strlen($config['height'])-1] == '%' ? '%' : '');
		}
		foreach ($config as $key => $value) {
			if ($config['type'] == 'img' && in_array($key,array('url','link'))) {
				$tmp = array();/*support multi pictures*/
				$config['multi'] = count($value);
				$index = 0;/* sort array */
				foreach($value as $k=>$v){
					$tmp[$index] = stripslashes(str_replace(array('&#61;','&amp;'),array('=','&'),$v));
					$index++;
				}
				if($config['multi']>1){
					$value = $tmp;
				}else{
					$value = $tmp[0];
				}
			}else{
				$value = stripslashes(str_replace(array('&#61;','&amp;'),array('=','&'),$value));/*other*/
			}
			$config[$key] = is_array($value) ? stripslashes($value):$value;
		}
		$config = addslashes(serialize($config));
		if ($id) {
			$db->update("UPDATE pw_advert SET " . pwSqlSingle(array(
				'ckey'		=> $advert['ckey'],
				'stime'		=> $advert['stime'],
				'etime'		=> $advert['etime'],
				'ifshow'	=> $advert['ifshow'],
				'orderby'	=> $advert['orderby'],
				'descrip'	=> $advert['descrip'],
				'config'	=> $config
			)) . " WHERE type='1' AND id=".pwEscape($id));
		} else {
			$otherkey = (array)GetGP('otherkey');
			$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
			$winduid = $userService->getUserIdByUserName($admin_name);
			foreach ($otherkey as $value) {
				if (!$cates[$value] || $advert['ckey'] == $value) continue;
				$db->update("INSERT INTO pw_advert SET " . pwSqlSingle(array(
					'uid'       => $winduid,
					'type'		=> 1,
					'ckey'		=> $value,
					'stime'		=> $advert['stime'],
					'etime'		=> $advert['etime'],
					'ifshow'	=> $advert['ifshow'],
					'orderby'	=> $advert['orderby'],
					'descrip'	=> $advert['descrip'],
					'config'	=> $config
				)));
			}
			$db->update("INSERT INTO pw_advert SET " . pwSqlSingle(array(
				'uid'       => $winduid,
				'type'		=> 1,
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
			$db->update("UPDATE pw_advert SET ifshow=1 WHERE type=0 AND ifshow=0 AND ckey=".pwEscape($advert['ckey']));
		}
		updatecache_c();
		adminmsg('operate_success');
	} elseif (in_array($job,array('del','show','hide'))) {
		InitGP(array('selid','id'));
		if($id && $job == 'del'){
			$selid = array($id);
		}
		if (!$selid = checkselid($selid)) {
			adminmsg('operate_error');
		}
		switch ($job) {
			case 'del':
				$db->update("DELETE FROM pw_advert WHERE type=1 AND id IN ($selid)");
			break;
			case 'show':
				$db->update("UPDATE pw_advert SET ifshow=1 WHERE type=1 AND etime>$timestamp AND id IN ($selid)");
			break;
			case 'hide':
				$db->update("UPDATE pw_advert SET ifshow=0 WHERE type=1 AND id IN ($selid)");
			break;
		}

		updatecache_c();
		adminmsg('operate_success',"$basename&action=");
	} elseif ($job == 'check') {
		InitGP(array('uid'),'GP',2);
		!$uid && adminmsg('unituser_username_empty');
		$buyer = $db->get_one("SELECT b.*,m.username FROM pw_buyadvert b LEFT JOIN pw_members m USING(uid) WHERE b.id=".pwEscape($id)."AND b.uid=".pwEscape($uid));
		!$buyer && adminmsg('unituser_newname_error');
		$buyer_config = unserialize($buyer['config']);
		HtmlConvert($buyer_config);
		$buyer_config['days'] = (int)$buyer_config['days'];
		!$buyer_config['days'] && adminmsg('advert_days_error');
		$usercredit = array();
		foreach ($credit->get($uid) as $key => $value) {
			$usercredit[$key] = $value;
		}
		!array_key_exists($config['creditype'],$usercredit) && adminmsg('advert_creditype_error');
		$price = 0;
		if ($config['price']) {
			 $config['price'] = (int)$config['price'];
			 $price = $config['price']*$buyer_config['days'];
			 $price>$usercredit[$config['creditype']] && adminmsg('advert_creditype_lack');
		}

		$begintime = $db->get_value("SELECT lasttime FROM pw_buyadvert WHERE id=".pwEscape($id)." ORDER BY lasttime DESC");

		if ($begintime && $begintime > $timestamp) {
			$buyer_config['starttime'] = get_date($begintime,'Y-m-d H:i');
			$buyer_config['endtime'] = get_date($begintime+$buyer_config['days']*86400,'Y-m-d H:i');
			$lasttime = $begintime + $buyer_config['days']*86400;
		} else {
			$buyer_config['starttime'] = get_date($timestamp,'Y-m-d H:i');
			$buyer_config['endtime'] = get_date($timestamp+$buyer_config['days']*86400,'Y-m-d H:i');
			$lasttime = $timestamp + $buyer_config['days']*86400;
		}

		$creditype 			= $config['creditype'];
		$creditypename 		= $credit->cType[$config['creditype']];
		$creditnum 			= $config['price'];
		$buyer_config['link']  = str_replace(array('&#61;','&amp;'),array('=','&'),$buyer_config['link']);
		$newconfig 			= addslashes(serialize($buyer_config));

		$credit->set($uid,$creditype,-$price);

		$db->update("UPDATE pw_buyadvert SET ".pwSqlSingle(array(
			'ifcheck'	=> 1,
			'lasttime'	=> $lasttime,
			'config'	=> $newconfig
		)) . "WHERE id=".pwEscape($id)."AND uid=".pwEscape($uid));

		M::sendNotice(
			array($buyer['username']),
			array(
				'title' => getLangInfo('writemsg','advert_buy_title'),
				'content' => getLangInfo('writemsg','advert_buy_content',array(
					'creditnum'		=> $creditnum,
					'creditypename'	=> $creditypename,
					'days'			=> $buyer_config['days']
				)),
			)
		);
		updatecache_c();
		$basename = "$amind_file?adminjob=hack&hackset=advert&job=check&id=$id";
		adminmsg('operate_success');
	}

	include_once PrintEot('setadvert');exit;
} elseif ($action == 'cate') {

	require_once(R_P.'require/credit.php');
	if (empty($job)) {
		InitGP(array('ifshow','ifhire','keyword','page'));

		$pwSQL = '';
		if (!empty($ifshow)) {
			$pwSQL .=  $ifshow == 2 ? " AND ifshow=0 " : " AND ifshow=1 ";
		}
		if (!empty($ifhire)) {
			$pwSQL .=  $ifhire == 2 ? " AND uid=0 " : "AND uid=1 ";
		}
		$keyword && $pwSQL .= " AND descrip LIKE ".pwEscape("%$keyword%");

		$count = $db->get_value("SELECT COUNT(*) FROM pw_advert WHERE type=0 $pwSQL");
		$page<1 && $page = 1;
		$limit = pwLimit(($page-1)*$db_perpage,$db_perpage);
		$pages = numofpage($count,$page,ceil($count/$db_perpage),"$basename&action=$action&ifshow=$ifshow&ifhire=$ifhire&keyword=".rawurlencode($keyword)."&");

		$query = $db->query("SELECT * FROM pw_advert WHERE type=0 $pwSQL ORDER BY ifshow DESC,id DESC $limit");
		while ($rt = $db->fetch_array($query)) {
			list($rt['name'],$rt['descrip']) = explode("~\t~",$rt['descrip']);
			$rt['ifhire'] = $rt['uid'];
			$rt['config'] = unserialize($rt['config']);
			$catedb[] = $rt;
		}
	} elseif ($job == 'edit') {
		if (empty($_POST['step'])) {
			InitGP(array('id'));
			$CreditList = '';
			foreach ($credit->cType as $key => $value) {
				$CreditList	.= "<option value=\"$key\">$value</option>";
			}

			$cate = array();
			$isDefault = '';
			if ($id) {
				$cate = $db->get_one("SELECT * FROM pw_advert WHERE type=0 AND id=".pwEscape($id));
				empty($cate) && adminmsg('advert_id_error',"$basename&action=$action");
				$cate['id'] < 100 && $isDefault = 'disabled';
				list($cate['name'],$cate['descrip']) = explode("~\t~",$cate['descrip']);
				$cate['config'] = unserialize($cate['config']);
				$cate['ifhire'] = $cate['uid'];
			}

			if ($cate['config']['display']) {
				${'display_'.$cate['config']['display']} = 'checked';
			} else {
				$display_rand = 'checked';
			}
			ifcheck($cate['ifshow'],'ifshow');
			ifcheck($cate['ifhire'],'ifhire');
		} elseif ($_POST['step'] == 2) {
			InitGP(array('config','id'));
			$id = (int)$id;
			if ((!$id || $id >=100) && !preg_match('/^([a-zA-Z0-9_.]{1,32})$/i',$config['ckey'])) {
				adminmsg('advert_ckey_error',"$basename&action=$action&job=edit");
			}
			if ($config['ifhire'] && !$credit->check($config['creditype'])) {
				adminmsg('advert_creditype_error');
			}
			if ($id) {
				$rt = $db->get_one("SELECT id,ckey FROM pw_advert WHERE type=0 AND id=".pwEscape($id));
				empty($rt) && adminmsg('advert_id_error',"$basename&action=$action");
				if ($rt['id'] < 100 && strtolower($rt['ckey']) != strtolower($config['ckey'])) {
					$rt = $db->get_one("SELECT id,ckey FROM pw_advert WHERE type=0 AND ckey=".pwEscape($config['ckey']));
					!empty($rt) && adminmsg('advert_ckey_exists',"$basename&action=$action");
					$db->update("UPDATE pw_advert SET ckey=".pwEscape($config['ckey'])."WHERE type=1 AND ckey=".pwEscape($rt['ckey']));
				}
			} else {
				$rt = $db->get_one("SELECT id,ckey FROM pw_advert WHERE type=0 AND ckey=".pwEscape($config['ckey']));
				!empty($rt) && adminmsg('advert_ckey_exists',"$basename&action=$action&job=edit");
			}

			$cate['config'] = array();
			if ($config['ifhire']) {
				$cate['config']['creditype'] = $config['creditype'];
				$cate['config']['price'] = (int)$config['price'];
				$cate['config']['operator'] = $config['operator'];
				$cate['ifhire'] = 1;
			} else {
				$cate['ifhire'] = 0;
			}
			$cate['config']['display'] = $config['display'];
//			$cate['config']['type'] = '';
//			if ($config['type']) {
//				foreach ($config['type'] as $value) {
//					$cate['config']['type'] .= in_array($value,array('floor','thread','mode')) ? $value.',' : '';
//				}
//			}
//			$cate['config']['type'] = trim($cate['config']['type'],',');
			$config['name'] = str_replace("~\t~",'',$config['name']);
			$config['descrip'] = str_replace("~\t~",'',$config['descrip']);
			$cate['descrip'] = substrs($config['name']."~\t~".$config['descrip'],250,'N');
			$cate['ifshow'] = $config['ifshow'] == 1 ? 1 : 0;
			$pwSQL = array(
				'type'		=> 0,
				'uid'		=> $cate['ifhire'],
				'ifshow'	=> $cate['ifshow'],
				'config'	=> serialize($cate['config'])
			);

			if (!$id || $id >= 100) {
				$pwSQL['ckey'] = $config['ckey'];
				$pwSQL['descrip'] = $cate['descrip'];
			}
			if ($id) {
				$db->update("UPDATE pw_advert SET".pwSqlSingle($pwSQL)."WHERE type=0 AND id=".pwEscape($id));
			} else {
				$db->update("INSERT INTO pw_advert SET".pwSqlSingle($pwSQL));
			}
			//$db->update("UPDATE pw_advert SET ifshow=1 WHERE type=0 AND ckey=".pwEscape($advert['ckey']));
			updatecache_c();
			adminmsg('operate_success',"$basename&action=$action");
		}
	} elseif (in_array($job,array('del','show','hide'))) {
		InitGP(array('selid','id'));
		$selid = ($selid) ? $selid : array($id);//only for array
		if (!$selid = checkselid($selid)) {
			adminmsg('operate_error',"$basename&action=$action");
		}
		switch ($job) {
			case 'del':
				$db->update("DELETE FROM pw_advert WHERE type=0 AND id IN ($selid) AND id>100");
			break;
			case 'show':
				$db->update("UPDATE pw_advert SET ifshow=1 WHERE type=0 AND id IN ($selid)");
			break;
			case 'hide':
				$db->update("UPDATE pw_advert SET ifshow=0 WHERE type=0 AND id IN ($selid)");
			break;
		}

		updatecache_c();
		adminmsg('operate_success',"$basename&action=$action");
	}
	include_once PrintEot('setadvert');exit;
		
} elseif ($action == 'statistics') {	
	$adverClass = L::loadclass('adver', 'advertisement');/*statistics*/
	list($status,$types,$benchs) = $adverClass->statistics();
	include_once PrintEot('setadvert');exit;
} elseif ($action == 'alter') {
	$adverClass = L::loadclass('adver', 'advertisement');/*during*/
	initGP(array("step"));
	if ($step == 2 ) {
		InitGP(array('alterstatus','alterbefore','alterway'));
		$alterstatus = in_array($alterstatus,array(1,0)) ? $alterstatus : 1;/*security*/
		$alterway = in_array($alterway,array(1,2)) ? $alterway : 1;
		$alterbefore = intval($alterbefore);
		$alters = array ('alterstatus' => $alterstatus, 'alterbefore' => $alterbefore, 'alterway' => $alterway );
		setConfig ( 'db_alterads', $alters );
		updatecache_c ();
		adminmsg("operate_success","$basename&action=$action");
	}
	$alters = ($db_alterads) ? $db_alterads : $adverClass->getDefaultAlter();/*alter*/
	$c_alterstatus = $c_alterway = array('','');
	($alters['alterstatus'] == 1) ? $c_alterstatus[1] = 'checked' : $c_alterstatus[0] = 'checked';
	($alters['alterway'] == 1)    ? $c_alterway[1]    = 'checked' : $c_alterway[0]    = 'checked';
	include_once PrintEot('setadvert');exit;
} else {
	adminmsg('operate_success');
}

























?>