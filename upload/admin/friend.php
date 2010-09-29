<?php
!function_exists('adminmsg') && exit('Forbidden');
$basename .= '&admintype='.$admintype;
include_once(D_P.'data/bbscache/inv_config.php');
require_once(R_P.'require/credit.php');
$nav =  $action ? array($action=>'class = current') : 'class=current';
if (empty($_POST['step'])) {
	if($admintype == 'invite'){
		if(empty($action)){
			ifcheck($inv_open,'open');
			ifcheck($inv_onlinesell,'onlinesell');
			$usergroup	= "";
			$num		= 0;
			foreach ($ltitle as $key => $value) {
				if ($key != 1 && $key != 2) {
					$checked = '';
					if (strpos($inv_groups,','.$key.',') !== false) {
						$checked = 'checked';
					}
					$num++;
					$htm_tr = $num%4 == 0 ?  '' : '';
					$usergroup .=" <li><input type='checkbox' name='groups[]' value='$key' $checked>$value</li>$htm_tr";
				}
			}
		}elseif($action == 'manager'){
			InitGP(array('page','type','username'));
			$sql =  '';
			$inv_days *= 86400;
			$timediff = (int)($timestamp-$inv_days);
			$sel = array($type => 'selected');
			empty($type) && $type = '0';
			if ($type == '1') {
				$sql .= "AND i.ifused='0' AND i.createtime>=".$timediff;
			} elseif ($type == '2') {
				$sql .= "AND i.ifused='0' AND i.createtime<".$timediff;
			} elseif ($type == '3') {
				$sql .= "AND i.ifused='1'";
			}
			if($username){
				$sql .= ' AND m.username = '.pwEscape($username);
			}
			$db_showperpage = 20;
			(!is_numeric($page) || $page<1) && $page = 1;
			$limit = pwLimit(($page-1)*$db_showperpage,$db_showperpage);
			$rt    = $db->get_one("SELECT COUNT(*) AS sum FROM pw_invitecode i LEFT JOIN pw_members m USING(uid)  WHERE 1=1 $sql");
			$pages = numofpage($rt['sum'],$page,ceil($rt['sum']/$db_showperpage),"$basename&action=manager&type=$type&");
		
			$query = $db->query("SELECT i.*,m.username FROM pw_invitecode i LEFT JOIN pw_members m USING(uid) WHERE 1=1 $sql $limit");
			$invdb = array();
			$i = 1;
			while ($rt = $db->fetch_array($query)) {
				$rt['used'] = '';
				if ($rt['ifused'] =='0' && $rt['createtime'] < $timediff){
					$rt['used'] = "<span class='gray'>已过期</span>";
				} elseif ($rt['ifused'] == '0' && $rt['createtime'] >= $timediff){
					$rt['used'] = "<span class='s3'  >未使用</span>";
				} elseif ($rt['ifused'] == '1'){
					$rt['used'] = "<span class='s2'  >已注册</span>";
				}
				$rt['num']=($page-1)*$db_showperpage+$i++;
				$rt['createtime']=get_date($rt['createtime'],'Y-m-d H:i:s');
				$invdb[]=$rt;
			}
		}
	}elseif($admintype == 'propagateset'){
		if(empty($action)){
			ifcheck($inv_linkopen,'linkopen');
			ifcheck($inv_linktype,'linktype');
		}elseif($action == 'statics'){
			InitGP(array('page'));
			$db_showperpage = 20;
			(!is_numeric($page) || $page<1) && $page = 1;
			$limit = pwLimit(($page-1)*$db_showperpage,$db_showperpage);
			$rt    = $db->get_one("SELECT COUNT(*) AS sum FROM pw_inviterecord");
			$pages = numofpage($rt['sum'],$page,ceil($rt['sum']/$db_showperpage),"$basename&action=statics&");
			$query = $db->query("SELECT username,(SELECT COUNT(uid) FROM pw_inviterecord WHERE uid=a.uid and typeid=0) visitnum,(SELECT COUNT(uid) FROM pw_inviterecord WHERE uid=a.uid and typeid=1) registernum,reward,unit, create_time,typeid typeid FROM pw_inviterecord a GROUP BY uid ORDER BY create_time DESC $limit");	 
			$invdb = array();
			while ($rt = $db->fetch_array($query)) {	
				$rt['create_time'] = get_date($rt['create_time'],'Y-m-d H:i:s');
				$rt['type'] = $rt['typeid'] ? '注册' : '访问';
				$rt['reward'] = $rt['reward'].$credit->cType[$rt['unit']];
				$invdb[]=$rt;
			}
			
		}
	}
	include PrintEot('friend');exit;
}else{
	if($admintype == 'invite'){
		if($_POST['step'] == '2'){		
			InitGP(array('config','groups'),'P');
			if(!is_numeric($config['open'])) $config['open']=1;
			if(!is_numeric($config['days'])) $config['days']=10;
			if(!is_numeric($config['limitdays'])) $config['limitdays']=0;
			if(!is_numeric($config['costs'])) $config['costs']=1;
			if(is_array($groups)){
				$config['groups'] = ','.implode(',',$groups).',';
			} else {
				$config['groups'] = '';
			}
			foreach($config as $key=>$value){
				$db->pw_update(
					"SELECT hk_name FROM pw_hack WHERE hk_name=".pwEscape("inv_$key"),
					"UPDATE pw_hack SET hk_value=".pwEscape($value)."WHERE hk_name=".pwEscape("inv_$key"),
					"INSERT INTO pw_hack SET hk_name=".pwEscape("inv_$key").",hk_value=".pwEscape($value)
				);
			}
			updatecache_inv();	
		}elseif($_POST['step'] == "3"){
			InitGP(array('selid'),'P');
			if (!$selid = checkselid($selid)) {
				adminmsg('operate_error');
			}
			$selid && $db->update("DELETE FROM pw_invitecode WHERE id IN ($selid)");		
		}
	}elseif($admintype == 'propagateset'){
		
		if($_POST['step'] == '2'){		
			InitGP(array('config'),'P');
			if(!is_numeric($config['linkopen'])) $config['linkopen']=0;
			if(!is_numeric($config['linktype'])) $config['linktype']=0;
			if(!is_numeric($config['linkscore'])) $config['linkscore']=0;			
			
			foreach($config as $key=>$value){
				$db->pw_update(
					"SELECT hk_name FROM pw_hack WHERE hk_name=".pwEscape("inv_$key"),
					"UPDATE pw_hack SET hk_value=".pwEscape($value)."WHERE hk_name=".pwEscape("inv_$key"),
					"INSERT INTO pw_hack SET hk_name=".pwEscape("inv_$key").",hk_value=".pwEscape($value)
				);
			}
			updatecache_inv();	
		}
	}
	adminmsg('operate_success');	
}



?>