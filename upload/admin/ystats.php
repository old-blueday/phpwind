<?php
!defined('P_W') && exit('Forbidden');

!$action && $action='flash';
${'cls_'.$action} = 'class="current"';
$ystatsUrl = 'http://tongji.linezing.com/export/phpwind';
if ($action=='config') {
	$basename .= '&action=config';
	if ($_POST['step']!=2) {
		!isset($db_ystats_ifopen) && $db_ystats_ifopen!='0' && $db_ystats_ifopen = 1;
		(int)$db_ystats_style<1 && $db_ystats_style = 1;
		ifcheck($db_ystats_ifopen,'ifopen');
		${'style_'.$db_ystats_style} = 'CHECKED';
		include PrintEot('ystats');exit;
	} else {
		L::loadClass('xml', 'utility', false);
		$xml = new XML(); $xml->setEncode('UTF-8');
		S::gp(array('config'),'P',2);
		if (!$db_ystats_unit_id && !$db_ystats_key) { //雅虎统计激活
			$ystats = array();
			$response = PostHost($ystatsUrl.'/reg.html?type=1');
			$response && $response = chunkdecode($response);
			$xml->setXMLData($response);
			if (!$xml->isXMLFile()) {
				adminmsg('ystat_xmldata_error');
			}
			$xml->parse();
			$result = XML::getChild($xml->getXMLRoot());
			foreach ($result as $tag){
				$tagname = XML::getTagName($tag);
				$ystats[$tagname] = XML::getData($tag);
			}
			if ($ystats['status']!='0') {
				adminmsg($ystats['info']);
			} else {
				$db_ystats_unit_id = $config['unit_id'] = $ystats['unit_id'];
				$db_ystats_key = $config['key'] = $ystats['key'];
			}
		}
		//雅虎统计选项
		$ystats = array();
		$response = PostHost($ystatsUrl.'/reg.html?type=2&key='.$db_ystats_key.'&unit_id='.$db_ystats_unit_id.'&open='.$config['ifopen'].'&style='.$config['style']);
		$response && $response = chunkdecode($response);
		$xml->setXMLData($response);
		if (!$xml->isXMLFile()) {
			adminmsg('ystat_xmldata_error');
		}
		$xml->parse();
		$result = XML::getChild($xml->getXMLRoot());
		foreach ($result as $tag){
			$tagname = XML::getTagName($tag);
			$ystats[$tagname] = XML::getData($tag);
		}
		if ($ystats['status']!='0') {
			adminmsg($ystats['info']);
		}
		foreach ($config as $key => $value) {
			$key = 'db_ystats_'.$key;
			setConfig($key, $value);
		}
		updatecache_c();
		adminmsg('operate_success');
	}
} elseif ($action=='report') {
	if (empty($db_ystats_unit_id) && empty($db_ystats_key)) {
		adminmsg('ystat_active_account', $basename.'&action=config');
	}
	$basename .= '&action=report';
	S::gp(array('view'));
	S::gp(array('year','month'),'GP',2);
	$pwDate['now']		= $timestamp+28800;//北京时间
	list($pwDate['hours'],$pwDate['mday'],$pwDate['wday'],$pwDate['year'],$pwDate['month']) = explode('-',gmdate('G-j-w-Y-m',$pwDate['now'])); //当天小时数,当月天数,本周天数,当前年份,当前月份
	$pwDate['wday']    == 0 && $pwDate['wday'] = 7;//星期:1-7
	$pwDate['ttime']	= (floor($pwDate['now']/3600)-$pwDate['hours'])*3600;//当天开始时间,时间戳
	$pwDate['mtime']	= $pwDate['ttime']-($pwDate['mday']-1)*86400;//当月开始时间,时间戳
	$pwDate['wtime']	= $pwDate['ttime']-($pwDate['wday']-1)*86400;//当周开始时间(星期一为第一天),时间戳
	$pwDate['lwtime']	= $pwDate['wtime']-604800;
	$pwDate['lasttime'] = $pwDate['now']-2505600;
	$pwDate['md5time']	= mktime(date('H',$timestamp),0,0,date('m',$timestamp),date('d',$timestamp),date('Y',$timestamp));
	$edate = '';
	switch ($view){
		case 'colligate':
			$sdate = gmdate('Y-m-d',$pwDate['lasttime']);
			break;
		case 'thisweek':
			$sdate = gmdate('Y-m-d',$pwDate['wtime']);
			break;
		case 'lastweek':
			$sdate = gmdate('Y-m-d',$pwDate['lwtime']);
			$edate = gmdate('Y-m-d',$pwDate['wtime']-86400);
			break;
		case 'thismonth':
			$sdate = gmdate('Y-m-d',$pwDate['mtime']);
			break;
		case 'last30':
			$sdate = gmdate('Y-m-d',$pwDate['lasttime']);
			break;
		case 'other':
			if ($year<2007 || $year>$pwDate['year'] || $month<1 || $month>12) {
				adminmsg('ystat_date_error');
			}
			$sdate = gmdate('Y-m-d',strtotime($year.'-'.$month.'-1'));
			$edate = gmdate('Y-m-d',strtotime($year.'-'.$month.'-'.gmdate('t',strtotime($sdate))));
			if (strtotime($sdate)>$pwDate['now']) {
				adminmsg('ystat_date_error');
			}
			strtotime($edate)>$pwDate['now'] && $edate = '';
			break;
		default:
			$view = 'colligate';
			$sdate = gmdate('Y-m-d',$pwDate['lasttime']);
	}
	!$year && $year = $pwDate['year'];
	!$month && $month = $pwDate['month'];
	${'year_'.$year} = ${'month_'.$month} = 'SELECTED';
	${$view} = 'CHECKED';

	L::loadClass('xml', 'utility', false);
	$xml = new XML(); $xml->setEncode('UTF-8');
	$ystats = $flashvars = array();
	$verify = md5($db_ystats_key.$db_ystats_unit_id.$pwDate['md5time']);
	$response = PostHost($ystatsUrl.'/report.html?key='.$db_ystats_key.'&unit_id='.$db_ystats_unit_id.'&s='.$verify.'&date1='.$sdate.'&date2='.$edate);
	$response && $response = chunkdecode($response);
	$xml->setXMLData($response);
	if (!$xml->isXMLFile()) {
		adminmsg('ystat_xmldata_error');
	}
	$xml->parse();
	$result = XML::getChild($xml->getXMLRoot());
	foreach ($result as $tag) {
		$tagname = XML::getTagName($tag);
		if ($tagname=='status' || $tagname=='info') {
			$ystats[$tagname] = XML::getData($tag);
		} elseif ($tagname == 'date_list'){
			$datelist = array();
			$datelist = XML::getChild($tag);
			foreach ($datelist as $list){
				$listkey = XML::getProperty($list,'value');
				$listkey = strtotime($listkey);
				$ystats['date_list'][$listkey] = XML::getAttribute($list);
			}
		} else {
			$ystats[$tagname] = XML::getAttribute($tag);
		}
	}
	if ($ystats['status']!='0') {
		adminmsg($ystats['info']);
	}
	$stime = strtotime($sdate);
	$etime = $edate ? strtotime($edate) : $pwDate['now'];
	$flashvars['pv'] = $flashvars['uv'] = $flashvars['ip'] = $flashvars['date'] = array();
	$flashvars['maxvalue'] = $sum_pv = $sum_uv = $sum_ip = 0;
	$total = intval(($etime-$stime)/86400);
	for ($i=0;$i<=$total;$i++){
		$flashvars['date'][$i] = $total>7 && $i%5 ? '' : gmdate('Y-m-d',$stime);
		if ($ystats['date_list'][$stime]) {
			$flashvars['maxvalue']<$ystats['date_list'][$stime]['pv'] && $flashvars['maxvalue'] = $ystats['date_list'][$stime]['pv'];
			$flashvars['maxvalue']<$ystats['date_list'][$stime]['uv'] && $flashvars['maxvalue'] = $ystats['date_list'][$stime]['uv'];
			$flashvars['pv'][$i] = $ystats['date_list'][$stime]['pv'];
			$flashvars['uv'][$i] = $ystats['date_list'][$stime]['uv'];
			$flashvars['ip'][$i] = $ystats['date_list'][$stime]['ip'];
			$sum_pv += $ystats['date_list'][$stime]['pv'];
			$sum_uv += $ystats['date_list'][$stime]['uv'];
			$sum_ip += $ystats['date_list'][$stime]['ip'];
		} else {
			$flashvars['pv'][$i] = $flashvars['uv'][$i] = $flashvars['ip'][$i] = '0';
		}
		$stime += 86400;
	}
	if ($ystats['date_list']) {
		$flashvars['maxvalue'] = ceil(($flashvars['maxvalue']+1)/10)*10;
		$flashvars['pv']	= implode(',',$flashvars['pv']);
		$flashvars['uv']	= implode(',',$flashvars['uv']);
		$flashvars['ip']	= implode(',',$flashvars['ip']);
		$flashvars['date']  = implode(',',$flashvars['date']);
		$flashvars = "&title=,5,&
&y_ticks=2,10,10&
&y_legend=Open Flash Chart,10,0xD2D2D2&
&y_min=0&
&bg_colour=#FFFFFF&
&x_labels=$flashvars[date]&
&values=$flashvars[pv]&
&values_2=$flashvars[uv]&
&values_3=$flashvars[ip]&
&line_dot=2,0xFF6600,PV,12,4&
&line_dot_2=2,0x04D215,UV,12,4&
&line_dot_3=2,0x0D8ECF,IP,12,4&
&y_max=$flashvars[maxvalue]&
";
	} else {
		$flashvars = '';
	}
	pwCache::setData(D_P.'data/bbscache/ystat.php',"<?php\n\$flashvars = \"$flashvars\";\n?>");
	krsort($ystats['date_list']);
	include PrintEot('ystats');exit;
} elseif ($action=='bind') {
	$basename .= '&action=bind';
	$db_ystats_ymail && adminmsg('ystat_ymail_error');
	if ($_POST['step']!=2) {
		include PrintEot('ystats');exit;
	} else {
		S::gp(array('ymail'),'P');
		if (!$ymail || !preg_match('/^[a-zA-Z][a-zA-Z0-9_]{3,31}\@(yahoo\.com\.cn|yahoo\.cn)$/',$ymail)) {
			adminmsg('ystat_ymail_format');
		}
		L::loadClass('xml', 'utility', false);
		$xml = new XML(); $xml->setEncode('UTF-8');
		$response = PostHost($ystatsUrl.'/reg.html?type=3&key='.$db_ystats_key.'&unit_id='.$db_ystats_unit_id.'&ymail='.$ymail);
		$response && $response = chunkdecode($response);

		$xml->setXMLData($response);
		if (!$xml->isXMLFile()) {
			adminmsg('ystat_xmldata_error');
		}

		$xml->parse();
		$ystats = array();

		$result = XML::getChild($xml->getXMLRoot());
		foreach ($result as $tag){
			$tagname = XML::getTagName($tag);
			$ystats[$tagname] = XML::getData($tag);
		}
		if ($ystats['status'] != '0') {
			adminmsg($ystats['info']);
		}
		setConfig('db_ystats_ymail', $ymail);
		updatecache_c();
		adminmsg('operate_success');
	}
} elseif ($action == 'reactivate') {

	$db->update("UPDATE pw_config SET db_value='',vtype='string' WHERE db_name IN('db_ystats_ymail','db_ystats_ifopen','db_ystats_style','db_ystats_unit_id','db_ystats_key')");
	updatecache_c();
	adminmsg('operate_success');

} elseif ($action == 'flash') {

	header('Cache-Control: no-cache, must-revalidate');
	//* @include_once pwCache::getPath(D_P.'data/bbscache/ystat.php');
	pwCache::getData(D_P.'data/bbscache/ystat.php');
	echo $flashvars;exit;
} else {
	ObHeader($basename.'&action=config');
}
function chunkdecode($data){
	if(strpos($data,"<?xml ") !== false){
		return $data;
	}
	$tmp = '';
	$slen = strpos($data,"\r\n");
	$length = (int)hexdec(substr($data,0,$slen+1));
	while ($length>0) {
		$data = substr($data,$slen+2);
		$tmp .= substr($data,0,$length);
		$data = substr($data,$length);
		$slen = strpos($data,"\r\n");
		$length = (int)hexdec(substr($data,0,$slen+1));
	}
	return trim($tmp);
}
?>