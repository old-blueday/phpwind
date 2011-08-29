<?php
!defined('P_W') && exit('Forbidden');
/**
 * 实名认证配置
 */

InitGP(array('action','step'));
empty($action) && $action = 'state';
if ($action == 'state') {
	if (empty($step)) {
		ifcheck($db_authstate,'authstate');
	} elseif ($step == 2) {
		InitGP(array('authstate'));
		$authService = L::loadClass('Authentication', 'user');
		$returnData = $authService->sendData('credit.site.setstatus', array('isopen' => $authstate ? true : false));
		if ($authstate && !$returnData->status) {
			adminmsg('与app平台通信失败，请到应用中心设置app平台选项 并登陆');
		}
		
		//云统计获取实名认证的安装时间
		$stasticsService = L::loadClass('Statistics', 'datanalyse');
		$db_authstate == 0 && $authstate == 1 && $stasticsService->authInstallTime();
		
		setConfig('db_authstate',$authstate);
		updatecache_c();
		adminmsg('operate_success', "$basename&action=state");	
	}
} elseif ($action == 'basic') {
	if (empty($step)) {
		$db_authreg == 1 ? $openAuthreg = 'checked' : $closeAuthreg = 'checked';
		$db_authgetpwd == 1 ? $openAuthpwd = 'checked' : $closeAuthpwd = 'checked';
		$db_authcertificate == 1 ? $openAuthcertificate = 'checked' : $closeAuthcertificate = 'checked';
	} elseif($step == 2) {
		InitGP(array('config'));
		/*
		$multiSql = array();
		foreach ($config as $key => $value) {
			$multiSql['db_' . $key] = array('db_' . $key, 'string', $value);
		}
		$multiSql = pwSqlMulti($multiSql);
		$multiSql && $db->update('REPLACE INTO pw_config (db_name,vtype,db_value) VALUES' . $multiSql);
		*/
		strlen($config['authsitename']) > 20 && adminmsg('站点名称长度不能超过20字节', "$basename&action=basic");
		saveConfig();
		updatecache_c();
		if ($config['authsitename']) {
			//发送至app
			$authService = L::loadClass('Authentication', 'user');
			$returnData = $authService->sendData('credit.site.setsms', array('sitename' => $config['authsitename']));
			if (!$returnData->status) {
				adminmsg('由于网络原因,站点名称设置失败,请稍候再试', "$basename&action=basic");
			}
		}
		adminmsg('operate_success', "$basename&action=basic");
	}
} elseif ($action == 'forumpost') {
	if (empty($step)) {
		list($catedb, $threaddb) = getAllThreads();
		$space  = '<i class="lower lower_a"></i>';
	} elseif ($step == 2) {
		$items = array('changedcellphone','changedalipay','changedcertificate','changedallowread','changedallowpost','changedallowrp','changedallowupload','changedlogicalmethod');
		InitGP(array_merge(array('auth') , $items));
		$tmpArray = $changedArray = $updateArray = array();
		foreach ($items as $v) {
			$$v && $changedArray = array_merge(explode(',',$$v), $changedArray);
		}
		$changedArray = array_unique($changedArray);
		empty($changedArray) && adminmsg('operate_error', "$basename&admintype=authentication&action=forumpost");
		foreach ($changedArray as $value) {
			$updateArray[$value] = array(
				'auth_cellphone' => ($auth[$value]['cellphone'] ? 1 : 0),
				'auth_alipay' => ($auth[$value]['alipay'] ? 1 : 0),
				'auth_certificate' => ($auth[$value]['certificate'] ? 1 : 0),
				'auth_allowread' => ($auth[$value]['allowread'] ? 1 : 0),
				'auth_allowpost' => ($auth[$value]['allowpost'] ? 1 : 0),
				'auth_allowrp' => ($auth[$value]['allowrp'] ? 1 : 0),
				'auth_allowupload' => ($auth[$value]['allowupload'] ? 1 : 0),
				'auth_logicalmethod' => ($auth[$value]['logicalmethod'] ? 1 : 0)
			);
		}
		updateForumset($updateArray);
		adminmsg('operate_success', "$basename&action=forumpost");
	}
} elseif ($action == 'forumcredit') {
	if (empty($step)) {
		list($catedb, $threaddb) = getAllThreads();
		$space  = '<i class="lower lower_a"></i>';
	} elseif ($step == 2) {
		InitGP(array('auth'));
		$updateArray = array();
		foreach ($auth as $key => $value) {
			!empty($value['cellphone']) && $value['cellphone'] = $value['cellphone'] < 0 ? 0 :(int) $value['cellphone'];
			!empty($value['alipay']) && $value['alipay'] = $value['alipay'] < 0 ? 0 :(int) $value['alipay'];
			!empty($value['certificate']) && $value['certificate'] = $value['certificate'] < 0 ? 0 :(int) $value['certificate'];
			$updateArray[$key] = array(
				'auth_cellphone_credit' => $value['cellphone'],
				'auth_alipay_credit' => $value['alipay'],
				'auth_certificate_credit' => $value['certificate']
			);
		}
		updateForumset($updateArray);
		adminmsg('operate_success', "$basename&action=forumcredit");
	}
} elseif ($action == 'static' || $action == 'smsbuy') {

	L::loadClass('client', 'utility/platformapisdk', false);
	$platformApiClient = new PlatformApiClient($db_sitehash, $db_siteownerid);
	switch ($action) {
		case 'smsbuy':
			$method = 'credit.pay.index';
			break;
		default:
			$method = 'credit.statistics.show';
			break;
	}
	$appurl = $platformApiClient->buildPageUrl(0, $method, array('wind_version'=>$wind_version));

} elseif ($action == 'certificateauth') {
	S::gp(array('page','state','step'),'GP',2);
	$authService = L::loadClass('Authentication', 'user');
	$states = $authService->getCertificateStates();
	if (empty($step)) {
		$total = $authService->countCertificateInfo($state);
		$pageSize = $db_perpage;
		$certificateInfo = array();
		if ($total) {
			$pages = ceil($total/$pageSize);
			if ($page > $pages || $page < 1) $page = 1;
			$start = ($page-1) * $pageSize;
			$certificateInfo = $authService->getCertificateInfo($start,$pageSize,$state);
			if(S::isArray($certificateInfo)){
				$types = $authService->certificateTypes;
				$userService = L::loadClass('UserService','user');
				$userNames = $userService->getUserNamesByUserIds(array_keys($certificateInfo));
				foreach ($certificateInfo as $k=>$v){
					$v['createtime'] = date('Y-m-d H:i:s',$v['createtime']);
					$v['state'] = $states[$v['state']];
					$v['type'] = $types[$v['type']];
					if ($v['attach1']) {
						list($v['attach1'],) = geturl($v['attach1'], 'lf');
						$v['attach1'] = $v['attach1'].'?'.$timestamp;
					}
					if ($v['attach2']) {
						list($v['attach2'],) = geturl($v['attach2'], 'lf');
						$v['attach2'] = $v['attach2'].'?'.$timestamp;
					}
					$v['username'] = $userNames[$v['uid']];
					$certificateInfo[$k] = $v;
				}
				$stateHtml = '';
	
			}
			$pagination = pagerforjs($total, $page, $pages,"onclick=\"manageclass.certificateinfo(this,'certificate_form','')\"");
		}
		foreach ($states as $k=>$v){
			$checked = $state == $k ? ' selected="1"' : '';
			$stateHtml .= "<option value=\"$k\"$checked>$v</option>";
		}
	} elseif($step == 2) {
		S::gp(array('selid','dostate'));
		$url = "$basename&action=certificateauth&state=$state&page=$page";
		empty($selid) &&  adminmsg('operate_error', $url);
		$dostate = intval($dostate);
		if ($dostate == -1) {
			$return = $authService->deleteCertificateByIds($selid);
			if($return !== true) adminmsg($return, $url);
		} elseif (isset($states[$dostate])) {
			$authService->updateCertificateStateByIds($selid,$dostate);
		} else {
			adminmsg('operate_fail', $url);
		}
		adminmsg('operate_success', $url);
	}
}
include PrintEot('authentication');exit;

/**
 * 取得所有分类与版块
 * @return array
 */
function getAllThreads() {
	global $db;
	$catedb = $forumdb = $subdb1 = $subdb2 = array();
	$query = $db->query("SELECT f.fid,f.fup,f.type,f.name,fe.forumset FROM pw_forums f LEFT JOIN pw_forumsextra fe ON f.fid=fe.fid WHERE f.cms!='1' ORDER BY f.vieworder");
	while ($forums = $db->fetch_array($query)) {
		$forums['name'] = Quot_cv(strip_tags($forums['name']));
		if ($forums['type'] == 'category') {
			$catedb[$forums['fid']] = $forums;
		} elseif ($forums['type'] == 'forum') {
			$forumdb[$forums['fid']] = $forums;
		} elseif ($forums['type'] == 'sub') {
			$subdb1[$forums['fid']] = $forums;
		} else {
			$subdb2[$forums['fid']] = $forums;
		}
	}
	$threaddb = array();
	foreach ($catedb as $cate) {
		$threaddb[$cate['fid']] = array();
		foreach ($forumdb as $key2 => $forumss) {
			if ($forumss['fup'] == $cate['fid']) {
				$threaddb[$cate['fid']][] = $forumss;
				unset($forumdb[$key2]);
				foreach ($subdb1 as $key3 => $sub1) {
					if ($sub1['fup'] == $forumss['fid']) {
						$threaddb[$cate['fid']][] = $sub1;
						unset($subdb1[$key3]);
						foreach ($subdb2 as $key4 => $sub2) {
							if ($sub2['fup'] == $sub1['fid']) {
								$threaddb[$cate['fid']][] = $sub2;
								unset($subdb2[$key4]);
							}
						}
					}
				}
			}
		}
	}
	return array($catedb, $threaddb);
}

/**
 * 更新数据库与缓存文件
 * @param $forumset
 */
function updateForumset($forumset) {
	global $db;
	foreach ($forumset as $k => $v) {
		$rs = $db->get_value('SELECT forumset FROM pw_forumsextra WHERE fid=' . pwEscape($k));
		if (empty($rs)){
			$db->update('REPLACE INTO pw_forumsextra (fid,forumset) VALUES (' . pwEscape($k) . ',' . pwEscape(serialize($v)) . ')');
		}else{
			$forumsets =array_merge(unserialize($rs), $v);
			$db->update('UPDATE pw_forumsextra SET forumset=' . pwEscape(serialize($forumsets)) . ' WHERE fid=' . pwEscape($k));
		}
	}
	updatecache_forums(array_keys($forumset));
}
?>