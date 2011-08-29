<?php
!defined('P_W') && exit('Forbidden');
if(isset($_GET['ajax']) && $_GET['ajax'] == 1){
	define('AJAX','1');
}
empty($subtype) && $subtype = 'shield';
$normalUrl = $baseUrl."?type=shield";
!empty($winduid) && $userId = $winduid;
S::gp(array('action'), 'GP');

$app_array = getUserApplist();
$defaultShield = $messageServer->getDefaultShields($app_array);
$shieldHtml = createHTML($app_array);
$nav = $action ? array($action => 'class="current"'):array('shield'=>'class="current"');
$noticevoiceStatus = getstatus($winddb['userstatus'], PW_USERSTATUS_NOTICEVPICE); //消息提示音状态
$noticevoiceStatus = ($noticevoiceStatus == 1) ? "checked" : "";
if(empty($action)){
	if($_POST['step'] == 2){
		PostCheck();
		S::gp(array('shieldinfo','blacklist','blackgroup','noticevoice'), 'GP');
		//是否开启消息提示音
		$userService = L::loadClass("userservice", 'user');	
		$userService->setUserStatus($winduid, PW_USERSTATUS_NOTICEVPICE, (int)$noticevoice);
		//屏蔽的黑名单
		if($shieldinfo && $messageServer->getMsKey('shieldinfo')){
			$newShield = createShield($shieldinfo);
			$shieldlist = compareShield($defaultShield,$newShield);
			$messageServer->setMsConfig(array('shieldinfo'=>serialize($shieldlist)),$userId);
		}
		//屏蔽的用户组
		if($_G['msggroup'] && $messageServer->getMsKey('blackgroup')){	
			//* include_once pwCache::getPath(D_P.'data/bbscache/level.php');
			pwCache::getData(D_P.'data/bbscache/level.php');
			$blackInsert = array();
			foreach($ltitle as $key => $value){
				if(!$blackgroup[$key]){
					$blackInsert[] = $key;
				}
			}	
			$messageServer->setMsConfig(array('blackgroup'=>serialize($blackInsert)),$userId);			
		}
		//屏蔽设置
		if($messageServer->getMsKey('blacklist')){
  			$blacklist = explode(',',$blacklist);		 	
			$messageServer->setMsConfig(array('blacklist'=>serialize($blacklist)),$userId);	
		}
		refreshto($normalUrl,'operate_success');
	}
    $config = $messageServer->getMsConfigs($userId);
    $config['shieldinfo'] && $shield = unserialize($config['shieldinfo']);
    $allShieldCheck = $shield ? 0 : 1; 
    $config['blacklist'] && $blacklist = implode(',',unserialize($config['blacklist']));
	if ($_G['msggroup']) {
	 	//* include_once pwCache::getPath(D_P.'data/bbscache/level.php');	
	 	pwCache::getData(D_P.'data/bbscache/level.php');	
		$config['blackgroup'] && $blackgroup = unserialize($config['blackgroup']);
		$allColonyCheck = $blackgroup ? 0 :1;
		$usergroup = '';
		foreach ($ltitle as $key => $value) {
				if($allColonyCheck){
					$checked = 'checked';
				}else{
					if ($blackgroup && in_array($key,$blackgroup)) {
						$checked = '';
					} else {
						$checked = 'checked';
					}
				}
				$usergroup .= "<li><input type=\"checkbox\" name=\"blackgroup[$key]\" value=\"$key\" $checked>$value</li>";
		}
	}	
}    
!defined('AJAX') && include_once R_P.'actions/message/ms_header.php';
require messageEot('shield');
if (defined('AJAX')) {
	ajax_footer();
} else {
	pwOutPut();
}

function compareShield($defaultShield,$newShield){
	$insertArray = array();
	foreach($defaultShield as $key => $value){
		if($newShield[$key]){
			$insertArray[$key] = $newShield[$key];
		}else{
			$insertArray[$key] = 0;
		}
	}
	return $insertArray;
}

function createShield($shield){
		$tmp = array();
		if(is_array($shield)){
			foreach($shield as $key => $value){
					foreach($value as $subkey=>$subvalue){
						if(is_array($subvalue)){
							foreach($subvalue as $thirdkey=>$thirdvalue){
								$tmp[$key.'_'.$subkey.'_'.$thirdkey] = 1;
							}
						}else{
							$tmp[$key.'_'.$subkey] = 1;
						}
					}
			}
		}else{
			$tmp = array($key);
		}
		return $tmp;
}
function createHTML($applist){
	$html = array(
	    'sms' => array(
	            'name' => '站内信',
	            'value' => 1,
	            'sub' => array(
						/* modified for phpwind8.5
	                    'message' => array(
	                    			'name'=>'好友给我空间的留言','value'=>1
									 ),
						'comment' => array(
	                    		'name'=>'好友对我的评论',
	                    		'value'=>1,
	                    		'sub'=>array(
									'diary' => array('name'=>'好友评论我的日志','value'=>1),
									'photo' => array('name'=>'好友评论我的相册','value'=>1),
									)
								),
						*/
			    		'ratescore'=>array('name'=>'评分','value'=>1),
						'reply'=>array('name'=>'帖子回复','value'=>1)         	  
	                )
	
	        ),
	    'notice' => array(
	            'name'  => '通知',
	            'value' => 1,
	            'sub'   => array(
	         			'guestbook' => array(
	                    			'name'=>'好友给我空间的留言','value'=>1
									 ),
						'comment' => array(
	                    		'name'=>'好友对我的评论',
	                    		'value'=>1,
	                    		'sub'=>array(
									'diary' => array('name'=>'好友评论我的日志','value'=>1),
									'photo' => array('name'=>'好友评论我的相册','value'=>1),
									)
								),	          
	                	'postcate' => array(
	                		'name' => '团购通知','value' => 1
	                	),
	                	'active'  => array(
	                		'name' => '活动通知','value' => 1
	                	),
	                	'website' => array(
	                		'name' => '站长通知','value' => 1
	                	),
	                	'apps' => array(
	                		'name' => '应用通知','value' => 1
	                	)
			    		     	  
	                )
	
	        ),
	    'request' => array(
	            'name'  => '请求',
	            'value' => 1,
	            'sub'   => array(                  
				    'friend' => array('name' => '好友邀请','value' => 1),
				    'group'  => array('name' => '群组邀请','value' => 1),
				    //'apps'   => array('name' => '应用安装邀请','value' => 1)
	          	    )
	        )
	);
	if(!empty($applist)){
		$html['notice']['sub']['app']['name'] = '应用通知';
		$html['notice']['sub']['app']['value'] = 1;
		foreach($applist as $key=>$value){
			$html['notice']['sub']['app']['sub'][$value['appid']]['name'] = $value['appname'];	
	 		$html['notice']['sub']['app']['sub'][$value['appid']]['value'] = 1;
	 	}
	}
	return $html;
}
?>