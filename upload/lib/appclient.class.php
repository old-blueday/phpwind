<?php
!defined('P_W') && exit('Forbidden');

/**
 * APP相关
 *
 * @package APP
 */
class PW_AppClient {
	var $_db;
	function PW_Appclient() {
		global $db_siteappkey, $timestamp, $db_sitehash, $db_siteownerid, $db_siteid, $db_charset, $db_appifopen, $pwServer, $db_server_url,$db_bbsname;
		$db_bbsurl = S::escapeChar("http://" . $pwServer['HTTP_HOST'] . substr($pwServer['PHP_SELF'], 0, strrpos($pwServer['PHP_SELF'], '/')));
		if (!file_exists(D_P . "data/bbscache/forum_appinfo.php")) {
			require_once (R_P . "admin/cache.php");
			updatecache_f();
		}
		//* @include_once pwCache::getPath(D_P . "data/bbscache/forum_appinfo.php");
		extract(pwCache::getData(D_P . "data/bbscache/forum_appinfo.php", false));
		$this->_db = $GLOBALS['db'];
		$this->appkey = $db_siteappkey;
		$this->timestamp = $timestamp;
		$this->siteid = $db_siteid;
		$this->siteownerid = $db_siteownerid;
		$this->sitehash = $db_sitehash;
		$this->bbsname = $db_bbsname;
		$this->bbsurl = $db_bbsurl;
		$this->charset = $db_charset;
		$this->appifopen = $db_appifopen;
		$this->server_url = $db_server_url;
		$this->appinfo = $forum_appinfo;
	}

	/**
	 * 获取开启的APP列表
	 */
	function getApplist() {
		global $db_apps_list;
		$this->_appsdb = $appsdb = array();
		$appsdb = $db_apps_list;
		!is_array($appsdb) && $appsdb = array();
		foreach($appsdb as $value) {
			if ($value['appstatus'] == 1 && $value['status'] == 1) {
				$this->_appsdb[$value['appid']]['appid'] = $value['appid'];
				$this->_appsdb[$value['appid']]['name'] = $value['name'];
			}
		}
		if (!$this->_appsdb || !$this->appifopen) {
			$this->_appsdb = array();
		}
		return $this->_appsdb;
	}

	/**
	 * 获取个人APP列表
	 */
	function userApplist($uids, $appids = '', $arrt = 0) {
		if (!$uids) return false;
		$this->_app_array = array();
		$this->_appslist = $this->getApplist();
		$sql_uid = $sql_appid = '';
		if (is_numeric($uids)) {
			$sql_uid .= ' uid=' . S::sqlEscape($uids);
		} elseif (is_array($uids)) {
			$sql_uid .= ' uid IN(' . S::sqlImplode($uids) . ')';
		}
		if (is_numeric($appids)) {
			$sql_appid .= ' AND appid=' . S::sqlEscape($appids);
		} elseif (is_array($appids)) {
			$sql_appid .= ' AND appid IN(' . S::sqlImplode($appids) . ')';
		}
			//$query = $this->_db->query("SELECT uid,appid,appname FROM pw_userapp WHERE $sql_uid $sql_appid");
		if (perf::checkMemcache()){
			$appids = is_array($appids) ? $appids : array(intval($appids));
			$_cacheService = Perf::gatherCache('pw_userapp');
			$array = $_cacheService->getUserappsCacheByUids($uids);
			foreach($array as $v) {
				if (in_array($v['appid'],$appids)) continue;
				if ($this->_appslist[$v['appid']]) {
					if ($arrt == 1) {
						$this->_app_array[$v['appid']] = $v['appname'];
					} elseif ($arrt == 2) {
						$this->_app_array[$v['uid']][$v['appid']] = $v;
					} else {
						$this->_app_array[] = $v;
					}
				}
			}	
		} else {
			$query = $this->_db->query("SELECT uid,appid,appname FROM pw_userapp WHERE $sql_uid $sql_appid");
			while ($rt = $this->_db->fetch_array($query)) {
				if ($this->_appslist[$rt['appid']]) {
					if ($arrt == 1) {
						$this->_app_array[$rt['appid']] = $rt['appname'];
					} elseif ($arrt == 2) {
						$this->_app_array[$rt['uid']][$rt['appid']] = $rt;
					} else {
						$this->_app_array[] = $rt;
					}
				}
			}
		}
		
		if (!$this->_app_array || !$this->appifopen) {
			$this->_app_array = array();
		}
		return $this->_app_array;
	}
	function getUserAppsByUid($uid){
		$uid = intval($uid);
		$apps = array();
		if (perf::checkMemcache()){
			$apps = $_cacheService->getUserappsCacheByUids($uid);
		} else {
			$query = $this->_db->query("SELECT * FROM pw_userapp WHERE uid=".S::sqlEscape($uid));
			while ($rt = $this->db->fetch_array($query)) {
				$apps[] = $rt;
			}
		}
		return $apps;
	}
	
	function getUserAppByUidAndAppid($uid,$appid){
		$uid = intval($uid);
		$appid = intval($appid);
		if (perf::checkMemcache()){
			$apps = $_cacheService->getUserappsCacheByUids($uid);
			foreach ($apps as $v){
				if ($v['appid'] == $appid) return $v;		
			}
		} else {
			return $this->_db->get_one("SELECT * FROM pw_userapp WHERE uid=".S::sqlEscape($uid)." AND appid=".S::sqlEscape($appid));
		}
		return array();
	}
	
	function deleteUserAppByUidAndAppid($uid,$appid){
		pwQuery::delete('pw_userapp', 'uid=:uid AND appid=:appid', array($uid,$appid));
	}
	
	/** 获取版块APP信息
	 *
	 * @param int $fid 版块ID
	 * @param string $position 论坛显示APP应用的位置,例如单个forum_erect 或者 forum_erect,forum_across 或者 subforum_erect,subforum_across
	 * 'forum_erect' => '1', //首页(一级)版块竖排
	 * 'forum_across' => '1', //首页(一级)版块横排
	 * 'subforum_erect' => '1', //二级版块竖排
	 * 'subforum_across' => '1', //二级版块横排
	 * 'thread' => '1', //版块列表页导航处
	 * 'read' => '1', //帖子页面页导航处
	 * @param string $appids 显示的APP应用ID,例如17 或者 13,17 或者留空，则显示所有
	 * @return array 显示具体位置的内容
	 */
	function showForumappinfo($fid, $position = 'forum_erect', $appid = 0) {
		global $db_apps_list;
		if (!is_numeric($fid) && !$fid) return false;
		$positiondb = explode(",", $position);
		$appinfodb = array();
		$foruminfo['appinfo'] = $this->appinfo[$fid];
		!is_array($foruminfo['appinfo']) && $foruminfo['appinfo'] = array();
		foreach($foruminfo['appinfo'] as $key => $value) {
			if ($appid && $appid != $key) {
				continue;
			}
			foreach($positiondb as $val) {
				if ($value['position'][$val]) {
					if ($key == $appid && $db_apps_list[$appid]['status'] == 1) {
						$appinfo = $value['c_text'] . ":" . $value['mms_emailcode'] . "." . $fid . $value['mms_domain'];
					}
					$appinfodb[$val][] = $appinfo;
				}
			}
		}
		$newappinfodb = array();
		foreach($appinfodb as $p => $info) {
			$appinfo = '';
			foreach($info as $val) {
				$appinfo .= $val . ' ';
			}
			$newappinfodb[$p] = $appinfo;
		}
		return $newappinfodb;
	}

	/**
	 * 获取数字签名
	 */
	function getApicode() {
		$code = base64_encode(md5(md5($this->siteownerid . $this->appkey) . $this->timestamp . $this->sitehash) . $this->timestamp . '$sitehash=' . $this->sitehash);
		return $code;
	}

	/**
	 * 获取淘链接url地址
	 */
	function getTaojinUrl($system = 'index', $mode = 'index', $action = 'index'){
		global $winduid, $windid;

		$param = array(
			'pw_appId'		=> '17',
			'pw_uid'		=> $winduid,
			'pw_siteurl'	=> $this->bbsurl,
			'pw_t'			=> $this->timestamp,
			'pw_system'		=> $system,
			'pw_mode'		=> $mode,
			'pw_action'		=> $action,
			'pw_query'		=> $this->getApicode(),
		);

		$url = 'http://app.phpwind.net/pwbbsapi.php?m=taoke&';
        ksort($param);
        foreach ( $param as $key => $value ) {
            $url .= "$key=" . urlencode ( $value ) . '&';
        }
        $hash = $param ['pw_system'] .'&'.$param ['pw_mode'].'&'.$param ['pw_action'] .'&'.$param ['pw_appId'] . '&' . $param ['pw_uid'] . '&' . $param ['pw_siteurl']  . '&' . $param ['pw_t'] . '&' . $param['pw_query'];
        $url .= 'pw_sig=' . md5 ( $hash . $this->siteownerid );
        return $url;
	}

	/**
	 * 获取帖子交换上传列表
	 */
	function getThreadsUrl($system = 'index', $mode = 'index', $action = 'index', $fid = 2) {
		global $winduid, $windid, $groupid;
		if (!is_numeric($fid) && !$fid) $fid = 2;
		$param = array(
			'pw_appId' => '8',
			'pw_charset' => $this->charset,
			'pw_uid' => $winduid,
			'pw_siteurl' => $this->bbsurl,
			'pw_t' => $this->timestamp,
			'pw_system' => $system,
			'pw_username' => $windid,
			'pw_fid' => $fid,
			'pw_mode' => $mode,
			'pw_action' => $action,
			'pw_groupid' => $groupid,
			'pw_query' => $this->getApicode(),
		);
		$url = 'http://app.phpwind.net/pwbbsapi.php?m=blooming&';
		ksort($param);
		foreach($param as $key => $value) {
			$url .= "$key=" . urlencode($value) . '&';
		}
		$hash = $param['pw_system'] . '&' . $param['pw_mode'] . '&' . $param['pw_action'] . '&' . $param['pw_appId'] . '&' . $param['pw_uid'] . '&' . $param['pw_siteurl'] . '&' . $param['pw_t'] . '&' . $param['pw_query'];
		$url .= 'pw_sig=' . md5($hash . $this->siteownerid);
		return $url;
	}

	/**
	 * 获取帖子交换权限
	 */
	function getThreadRight() {
        global $windid,$groupid, $db_threadconfig;
        $put = array();
        $t = $db_threadconfig;

        if (is_array($t)) {
            if ($t['ifopen'] == 1) {
                $isManage = ($groupid == 3) ? 1 : 0;//manage?
                //下载和上传权限
                $put['down']['admin'] = ($isManage == 1 && $t['if_admin_down'] == 1) ? 1 : 0;
                $put['down']['other'] = array();
                $put['post']['admin'] = ($isManage == 1 && $t['if_admin_post'] == 1) ? 1 : 0;
				$put['post']['other'] = array();


                if ($t['if_other_down'] == 1) {
                    foreach ($t['permissions'] as $v) {
                        if ($v['username'] == $windid) {
                            $fid_arr		= explode(',',$v['fid']);
                            $if_down_arr	= explode(',',$v['if_down']);
                            $if_post_arr	= explode(',',$v['if_post']);
                            for ($i = 0;$i < count($fid_arr);$i++) {
                                if ($if_down_arr[$i] == 1) {
                                    $put['down']['other'][] = $fid_arr[$i];
                                }
                                if ($if_post_arr[$i] == 1) {
                                    $put['post']['other'][] = $fid_arr[$i];
                                }
                            }
                            break;
                        }
                    }
                }
            }
        }
        return $put;
    }

	/**
	 * 获取APP iframe
	 */
	function getAppIframe($app_id) {
		global $admin_name;
		$app_serverurl = $this->server_url . '/appsmanager.php';
		$param = array(
			'pw_sitehash' => $this->sitehash,
			'pw_fromurl' => $this->bbsurl . "/admin.php?adminjob=app",
			'pw_time' => $this->timestamp,
			'pw_user' => $admin_name,
			'pw_appid' => $app_id,
		);
		$url = $app_serverurl . '?';
		ksort($param);
		foreach($param as $key => $value) {
			$url .= "$key=" . urlencode($value) . '&';
		}
		$arg = 'pw_appid=' . $param['pw_appid'] . '&pw_user=' . $param['pw_user'] . '&pw_time=' . $param['pw_time'];
		$url .= 'pw_sig=' . md5($arg . $this->siteownerid);
		return $url;
	}

	/**
	 * 获取在线APP列表
	 */
	function getOnlineApp() {
		global $admin_name;
		$app_list = $this->server_url . '/adminlist.php';
		$param = array(
			'pw_sitehash' => $this->sitehash,
			'pw_fromurl' => $this->bbsurl . "/admin.php?adminjob=app",
			'pw_time' => $this->timestamp,
			'pw_user' => $admin_name,
		);
		$arg = implode('|', $param);
		ksort($param);
		$url = $app_list . '?';
		foreach($param as $key => $value) {
			$url .= "$key=" . urlencode($value) . '&';
		}
		$url .= 'pw_sig=' . md5($arg . $this->siteownerid);
		return $url;
	}

	/**
	 * APP论坛状态
	 */
	function alertAppState($admintype) {
		global $admin_name, $db_bbsname, $db_timedf;
		$param = array(
			'pw_sitehash' => $this->sitehash,
			'pw_fromurl' => $this->bbsurl . "/admin.php?adminjob=app",
			'pw_time' => $this->timestamp,
			'pw_user' => $admin_name,
		);
		if ($admintype == 'open') {
			$param = array_merge($param, array(
				'action' => 'open',
				'sitename' => $db_bbsname,
				'siteurl' => $this->bbsurl,
				'charset' => $this->charset,
				'timedf' => $db_timedf
			));
		} elseif ($admintype == 'close') {
			$param['action'] = 'close';
		}
		ksort($param);
		$str = $arg = '';
		foreach($param as $key => $value) {
			if ($value) {
				$str .= "$key=" . urlencode($value) . '&';
				$arg .= "$key=$value&";
			}
		}
		$str .= 'pw_sig=' . md5($arg . $this->siteownerid);
		return $str;
	}

	/**
	 * 判断是否为本地
	 */
	function isLocalhost($host) {
		if ($host && strpos($host, 'localhost') === false && strpos($host, '127.0') === false && strpos($host, '127.1') === false && !preg_match('/^192.168.*/', $host) && !preg_match('/^10.*/', $host)) {
			$islocalhost = false;
		} else {
			$islocalhost = true;
		}
		return $islocalhost;
	}

	/**
	 * 云统计
	 */
	function getYunStatisticsUrl() {
		$yunStatisticsUrl = 'http://tongji.phpwind.com/statistic/?' . $this->_bulidQueryString(array(
			'app_key' => $this->sitehash,
			'timestamp' => $this->timestamp,
			'v' => '1.0',
		), $this->siteownerid);
		return $yunStatisticsUrl;
	}

	function _bulidQueryString($params ,$appKey) {
		ksort($params);
		reset($params);
		$pairs = array();
		foreach ($params as $key => $value) {
			$pairs[] = urlencode($key) . '=' . $value;
		}
		$string = implode('&', $pairs);
		$string.= '&sig=' . md5($string .'&' . $appKey);
		return $string;
	}

	/*************************站长中心相关****************************/

	/**
	 * 确认帐号是否存在
	 */
	function checkUsername($appid) {
		
		if (empty($appid)) return false;

		$siteappkey = $this->_checkUsername($appid);
		
		if (!empty($siteappkey['status'])) {
			setConfig('db_siteappkey', $siteappkey['siteid']);
			updatecache_c();
			return true;
		}

		return false;
	}

	/**
	 * 确认帐号是否存在
	 */
	function _checkUsername($appid) {

		$platformApiClient = $this->_getPlatformApiClient();
		
		$params = array(
			'username' => $appid,
			'charset' => $this->charset
		);

		L::loadClass('json', 'utility', false);
		$Json = new Services_JSON(SERVICES_JSON_LOOSE_TYPE);
		return $Json->decode($platformApiClient->post('webmaster.onlineapp.checkusername' ,$params));
	}

	/**
	 * 注册站长中心帐号
	 */
	function registerWebmaster($fields) {

		$params = $this->_checkRegisterWebmaster($fields);
		if (empty($params)) return array('status' => false ,'code' => '-1');

		$platformApiClient = $this->_getPlatformApiClient();

		L::loadClass('json', 'utility', false);
		$Json = new Services_JSON(SERVICES_JSON_LOOSE_TYPE);
		return $Json->decode($platformApiClient->post('webmaster.onlineapp.register' ,$params));
	}

	/**
	 * 注册前逻辑判断操作
	 */
	function _checkRegisterWebmaster($fields) {

		isset($fields['username']) && $username = $fields['username'];
		isset($fields['email']) && $email = $fields['email'];
		isset($fields['password']) && $password = $fields['password'];
		isset($fields['repassword']) && $repassword = $fields['repassword'];
		
		$params = array(
			'username' => $username,
			'email' => $email,
			'password' => $password,
			'repassword' => $repassword,
			'siteid' => $this->siteid,
			'siteownerid' => $this->siteownerid,
			'sitehash' => $this->sitehash,
			'timestamp' => $this->timestamp,
			'sitename' => $this->bbsname,
			'siteurl' => $this->bbsurl,
			'charset' => $this->charset,
		);

		return $params;
		
	}

	/**
	 * 获取错误码信息
	 */
	function getErrorRegCodeMsg($code) {
		switch ($code) {
			case '2':
				$msg = '请检查必填项是否正确';break;
			case '3':
				$msg = '请输入您的站点密钥！';break;
			case '4':
				$msg = '对不起，您填写的信息不匹配！';break;
			case '5':
				$msg = '对不起，您填写的用户名已被使用！';break;
			case '6':
				$msg = '对不起，您填写的邮箱格式有误！';break;
			case '7':
				$msg = '对不起，您填写的邮箱已被使用！';break;
			case '8':
				$msg = '对不起，您填写的域名格式有误！';break;
			case '9':
				$msg = '对不起，您填写的密码长度不正确，须6-20位';break;
			case '10':
				$msg = '对不起，您的密钥已被使用，请联系官方！';break;
			case '11':
				$msg = '对不起，您的操作未成功，请重试';break;
			case '12':
				$msg = '对不起，两次输入的密码不一致';break;
			case '13':
				$msg = '用户名长度必须在2-16个字之间';break;
			default:
				$msg = '对不起，通信失败，请重试';
		}
		return $msg;
	}

	/**
	 * 关联站长中心帐号（重新登录）
	 */
	function linkWebmaster($fields) {

		$params = $this->_checkLinkWebmaster($fields);

		$platformApiClient = $this->_getPlatformApiClient();

		L::loadClass('json', 'utility', false);
		$Json = new Services_JSON(SERVICES_JSON_LOOSE_TYPE);

		return $Json->decode($platformApiClient->post('webmaster.onlineapp.registerbyoldaccount' ,$params));
		
	}

	/**
	 * 关联前逻辑判断操作
	 */
	function _checkLinkWebmaster($fields) {
		
		isset($fields['username']) && $username = $fields['username'];
		isset($fields['password']) && $password = $fields['password'];
		
		$params = array(
			'username' => $username,
			'password' => $password,
			'siteid' => $this->siteid,
			'siteownerid' => $this->siteownerid,
			'sitehash' => $this->sitehash,
			'charset' => $this->charset,
		);

		return $params;
		
	}

	/**
	 * 获取错误码信息
	 */
	function getErrorLinkCodeMsg($code) {
		switch ($code) {
			case '3':
				$msg = '请输入您的站点密钥！';break;
			case '4':
				$msg = '对不起，用户名不存在！';break;
			case '5':
				$msg = '对不起，密码输入有误！';break;
			case '6':
				$msg = '对不起，您填写的信息不匹配！';break;
			case '7':
				$msg = '对不起，您的操作未成功，请重试';break;
			default:
				$msg = '对不起，通信失败，请重试';
		}
		return $msg;
	}

	/**
	 * 登录站长中心
	 */
	function loginWebmaster() {
		
		$platformApiClient = $this->_getPlatformApiClient();
		
		$params = array('siteappkey' => $this->appkey);
		
		return $platformApiClient->post('webmaster.onlineapp.login' ,$params);
		
	}

	/**
	 * 获取站长中心登录后页面
	 */
	function getLoginWebmasterUrl($appkey) {
		
		$platformApiClient = $this->_getPlatformApiClient();
		
		$params = array(
			'siteurl' => $this->bbsurl,
			'siteappkey' => $appkey
		);

		return $platformApiClient->buildPageUrl(0 ,'webmaster.onlineapp.index' ,$params);
		
	}

	/**
	 * 获取推荐应用信息
	 */
	function getOnlineAppList() {
		
		$platformApiClient = $this->_getPlatformApiClient();

		return $platformApiClient->buildPageUrl(0 ,'webmaster.onlineapp.applist');
		
	}

	/**
	 * 判断url是否改动
	 */
	function isUrlChanged() {

		$platformApiClient = $this->_getPlatformApiClient();
	
		$params = array(
			'siteurl' => $this->bbsurl,
			'siteappkey' => $this->appkey
		);
	
		L::loadClass('json', 'utility', false);
		$Json = new Services_JSON(SERVICES_JSON_LOOSE_TYPE);
		return $Json->decode($platformApiClient->post('webmaster.onlineapp.checkurl' ,$params));
		
	}
	
	/**
	 * 获取url改动后的信息
	 */
	function getUrlChangedMsg() {
		
		$isUrlChanged = $this->isUrlChanged();

		if (empty($isUrlChanged['status'])) {

			return $this->getErrorUrlCodeMsg($isUrlChanged['code']);
		}
		return false;
	}

	/**
	 * 获取错误码信息
	 */
	function getErrorUrlCodeMsg($code) {
		
		switch ($code) {
			case '1':
				$msg = '站点资料的网址为空，请先通过应用首页修改资料！';break;
			case '2':
				$msg = false;break;
			case '3':
				$msg = '站点资料的网址和当前不一致，请先通过应用首页修改资料！';break;
			default:
				$msg = false;
		}
		return $msg;
	}
	
	/**
	 * 客户端生成服务
	 */
	function _getPlatformApiClient() {
		static $client = null;
		if (!$client) {
			L::loadClass('client', 'utility/platformapisdk', false);
			$client = new PlatformApiClient($this->sitehash, $this->siteownerid);
		}
		return $client;
	}
	/*************************站长中心相关****************************/


	/**
	 * 获取虾米音乐列表
	 */
	function getMusic($page = 1, $keyword) {
		global $winduid;
		$param = array();
		$param['pw_appIdname'] = 'xiami';
		$param['pw_uid'] = $winduid;
		$param['pw_siteurl'] = $this->bbsurl;
		$param['pw_sitehash'] = $this->sitehash;
		$param['pw_t'] = $this->timestamp;
		$param['pw_bbsapp'] = 1;
		$param['pw_keyword'] = $keyword;
		$param['pw_page'] = $page;
		$url = $this->server_url . '/apps.php?';
		foreach($param as $key => $value) {
			$url .= "$key=" . urlencode($value) . '&';
		}
		$hash = $param['pw_appIdname'] . '|' . $param['pw_uid'] . '|' . $param['pw_siteurl'] . '|' . $param['pw_sitehash'] . '|' . $param['pw_t'];
		$url .= 'pw_sig=' . md5($hash . $this->siteownerid);
		require_once (R_P . 'require/posthost.php');
		$backdata = PostHost($url, '', 'POST');
		if (empty($backdata)) {
			$backdata = PostHost($url, '', 'POST');
		}
		$data = unserialize($backdata);
		return $data;
	}

	/**
	 * 获取APP-iframe列表
	 */
	function ShowAppsList() {
		global $winduid;
		$param = array();
		$param = array(
			'pw_appId' => 0,
			'pw_uid' => $winduid,
			'pw_siteurl' => $this->bbsurl,
			'pw_sitehash' => $this->sitehash,
			'pw_t' => $this->timestamp
		);
		$arg = implode('|', $param);
		$url = $this->server_url . '/list.php?';
		foreach($param as $key => $value) {
			$url .= "$key=" . urlencode($value) . '&';
		}
		$url .= 'pw_sig=' . md5($arg . $this->siteownerid);
		return $url;
	}

	/**
	 * 移除用户个人APP
	 */
	function MoveAppsList($id) {
		global $winduid;
		$param = array();
		$param = array(
			'pw_appId' => 0,
			'pw_uid' => $winduid,
			'pw_siteurl' => $this->bbsurl,
			'pw_sitehash' => $this->sitehash,
			'pw_t' => $this->timestamp,
			'pw_appId' => $id
		);
		$arg = implode('|', $param);
		$url = $this->server_url . '/list.php?';
		foreach($param as $key => $value) {
			$url .= "$key=" . urlencode($value) . '&';
		}
		$url .= 'pw_sig=' . md5($arg . $this->siteownerid);
		require_once (R_P . 'require/posthost.php');
		PostHost($url, 'op=delapp', 'POST');
	}
}
?>