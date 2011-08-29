<?php
!function_exists('adminmsg') && exit('Forbidden');

!$adminitem && $adminitem = 'msphinx';
$basename = $basename.'&adminitem='.$adminitem;

if ($adminitem == 'mmemcache') {

	L::loadClass('memcache', 'utility', false);
	$mcache = new PW_Memcache(false);

	if ($_POST['step'] == 2) {
		S::gp ( array ('host', 'port', 'isopen', 'hash') );

		empty($host) && adminmsg ( "抱歉，服务器主机不能为空" );
		empty($port) && adminmsg ( "抱歉，服务器端口不能为空" );
		$isopen = (isset ( $isopen )) ? $isopen : 0;
		if ($isopen) {
			$errormsg = testSockopen($host,$port);
			if ($errormsg[0] != 1) {
				adminmsg($errormsg[1]);
			}
		}
		$db_memcache = array ('isopen' => $isopen, 'host' => $host, 'port' => $port, 'hash' => $hash );
		$db_datastore = $db_memcache['isopen'] ? 'memcache' : '';
		setConfig ( 'db_memcache', $db_memcache );
		setConfig ( 'db_datastore', $db_datastore );
		updatecache_c ();
		$_cacheService = L::loadClass('cacheservice', 'utility');
		$_cacheService->flush(PW_CACHE_MEMCACHE);		
		adminmsg("operate_success");
	} else {
		$ajax = S::getGP('ajax');
		if ($ajax == 1 && strtolower ( $pwServer ['REQUEST_METHOD'] ) == 'post') {
			S::gp ( array ('host', 'port') );
			$errormsg = testSockopen($host,$port);
			showError($errormsg[1]);
		}
		$baseUrl = EncodeUrl ($basename);
		$configure =  $db_memcache ? $db_memcache : array('isopen'=>0,'host'=>'localhost','port'=>11211, 'hash'=>'');
		($configure ['isopen'] == 0) ? $isopenCheck [0] = 'checked=checked' : $isopenCheck [1] = 'checked=checked';
		include PrintEot ('advanced');
	}
} elseif ($adminitem == 'msphinx') {
	$searcher = L::loadClass('searcher', 'search');
	$sphinxSearch = $searcher->sphinxService();
	$ranks = $sphinxSearch->_getSphinxRanks();
	$groups = $sphinxSearch->_getSphinxGroups();
	if ($_POST ['step'] == 2) {
		S::gp ( array ('host', 'port', 'isopen','rank','group','tindex','tcindex','pindex','dindex','dcindex','cmsindex','weiboindex','wordsegment_mode','sync_data') );
		empty($host) && adminmsg ( "抱歉，服务器主机不能为空" );
		empty($port) && adminmsg ( "抱歉，服务器端口不能为空" );
		$isopen = (isset ( $isopen )) ? $isopen : 0;
		if ($isopen) {
			$errormsg = testSockopen($host,$port);
			if ($errormsg[0] != 1) {
				adminmsg($errormsg[1]);
			}
		}
		$sync_data = (array)$sync_data;
		if ($sync_data && array_diff($sync_data, array('sync_threads','sync_posts','sync_diarys','sync_members'))){
			showMsg("抱歉,实时操作记录类型不存在");
		}
		$sphinxData = array ('isopen' => $isopen, 
							 'host' => $host, 
							 'port' => $port, 
							 'rank'=>trim($rank), 
							 'group'=>trim($group),
							 'tindex'=>trim($tindex),
							 'tcindex'=>trim($tcindex),
							 'pindex'=>trim($pindex),
							 'dindex'=>trim($dindex),
							 'dcindex'=>trim($dcindex),
							 'cmsindex'=>trim($cmsindex),
							 'weiboindex'=>trim($weiboindex),
							 'wordsegment_mode'=> S::int($wordsegment_mode),
							 'sync'=>$sync_data,
						);
		setConfig ( 'db_sphinx', $sphinxData );
		updatecache_c ();
		adminmsg("operate_success");

	} else {
		$ajax = S::getGP('ajax');
		if ($ajax == 1 && strtolower ( $pwServer ['REQUEST_METHOD'] ) == 'post') {
			S::gp ( array ('host', 'port') );
			$errormsg = testSockopen($host,$port);
			showError($errormsg[1]);
		}
		$baseUrl = EncodeUrl ($basename);
		$default = $sphinxSearch->_getSphinxDefaults();
		$configure = ($db_sphinx) ? $db_sphinx : $default;
		/*兼容*/
		foreach($default as $k =>$v){
			$configure[$k] = isset($db_sphinx[$k]) ? $db_sphinx[$k] : $default[$k];
		}
		($configure ['isopen'] == 0) ? $isopenCheck [0] = 'checked=checked' : $isopenCheck [1] = 'checked=checked';
		$rankSelects = assignSelect($ranks,$configure['rank']);
		$groupSelects = assignSelect($groups,$configure['group']);
		$wordsegment_mode[isset($configure['wordsegment_mode']) ? $configure['wordsegment_mode'] : 1] = 'checked';
		$sync_data = array();
		if (S::isArray($configure['sync'])){
			foreach ($configure['sync'] as $v){
				$sync_data[$v] = 'CHECKED';
			}
		}
		include PrintEot ( 'advanced' );
	}
} elseif ($adminitem == 'distribute') {
	if ($_POST ['step'] == 2) {
		if (!pwWritable(D_P.'data/sql_config.php')) {
			adminmsg('manager_error');
		}
		include D_P.'data/sql_config.php';

		S::gp ( array ('db_distribute'));
		if($db_distribute){
			$_service = L::loadClass ( 'cachedistribute', 'utility' );
			if (!$_service->dumpData ()) adminmsg("抱歉，将缓存文件导入数据库时出错");
		}
		
		$newconfig = array(
			'dbhost' => $dbhost,
			'dbuser' => $dbuser,
			'dbpw' => $dbpw,
			'dbname' => $dbname,
			'database' => $database,
			'PW' => $PW,
			'pconnect' => $pconnect,
			'charset' => $charset,
			'manager' => $manager,
			'manager_pwd' => $manager_pwd,
			'db_hostweb' => $db_hostweb,
			'db_distribute' => intval($db_distribute),
			'attach_url' => $attach_url
		);
		require_once(R_P.'require/updateset.php');
		write_config($newconfig);
		
		//* setConfig ( 'db_distribute', intval($db_distribute) );
		//* updatecache_c ();		
		adminmsg('operate_success');
	}
	$baseUrl = EncodeUrl ($basename);
	ifcheck($db_distribute, 'db_distribute');
	include PrintEot ( 'advanced' );
}

function assignSelect($arrays,$select){
	$selects = array();
	foreach($arrays as $k=>$v){
		if($select == $v){
			$selects[$k] = 'selected="selected"';
		}else{
			$selects[$k] = "";
		}			
	}
	return $selects;
} 

function showError($error) {
	echo $error;
	ajax_footer ();
	exit ();
}

function testSockopen($host,$port){
	$errormsg = array();
	if ($host == '' || strlen ( $host ) < 8 || $port == '' || ! is_numeric ( $port )) {
		$errormsg = array(4,'服务器host地址或端口号不正确');
	} else {
		$fp = @fsockopen ( $host, $port, $errno, $errstr , 2 );
		if (! $fp) {
			$errstr = trim ( $errstr );
			$errormsg = array(2,"连接 {$host}:{$port} 服务器失败 (errno=$errno, msg=$errstr)");
		} else {
			$errormsg = array(1,"恭喜，服务器连接成功!");
		}
	}
	return $errormsg;
}
?>