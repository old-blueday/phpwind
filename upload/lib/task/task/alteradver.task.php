<?php
/*
 * 广告到期提醒任务
 */
!function_exists('readover') && exit('Forbidden');
class Task_AlterAdver {
	
	var $_db = null;
	
	function Task_AlterAdver(){
		global $db;
		$this->_db = &$db;
	}
	
	function run(){
		return $this->doTask();
	}
	
	function doTask(){
		$configs = $this->_getConfig();
		if(!$configs['alterstatus']){/*is open adver alter */
			return null;
		}
		/*get advers */
		$alterbefore = $configs['alterbefore'];
		$before = $alterbefore*3600*24;
		$current = time();
		$result = array();
		$query = $this->_db->query("SELECT * FROM pw_advert WHERE type=1 AND ifshow=1 AND etime>".S::sqlEscape($current)." AND etime<=".S::sqlEscape($before+$current));
		while($rs = $this->_db->fetch_array($query)){
			$result[$rs['uid']][] = $rs;
		}
		if(!$result){
			return null;
		}
		/* send short message or email */
		$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
		foreach($result as $uid => $advers){
			$content = "你好：";
			foreach($advers as $adver){
				$content .= $this->_getContent($adver,$alterbefore);
			}
			$subject = $this->_getSubject();
			if($configs['alterway'] == 1){
				$this->sendShortMessage($adver['uid'],$subject,$content);
			}else{
				$user = $userService->get($uid);
				if ($user && $user['email']) $this->sendEmail($user['email'],$subject,$content);
			}
		}
		return true;
	}
	
	
	/*
	 * 获取内容
	 */
	function _getContent($adver,$alterbefore){
		$descrip = $adver['descrip'];
		list($up,$down,$title) = $this->_getAdvers($adver['ckey']);
		$html = "<br />描述为  {$descrip} 的广告将在 {$alterbefore} 天后到期，该广告所在的广告位  {$title} 中：已开启的广告有 {$up} 个，已关闭的广告有 {$down} 个。<br />";
		return $html;
	}
	
	function _getAdvers($ckey){
		$query = $this->_db->query("SELECT * FROM pw_advert WHERE ckey=".S::sqlEscape($ckey));
		$current = time();
		$title = $descrip = '';/* adver title*/
		$up = $down = 0;
		while($rs = $this->_db->fetch_array($query)){
			if($rs['type'] == 0){
				list($title,$descrip) = explode("~\t~",$rs['descrip']);
				continue;
			}
			if($rs['ifshow'] == 1 && $current>=$rs['stime'] && $current<= $rs['etime'] ){
				$up++;
			}else{
				$down++;
			}
		}
		return array($up,$down,$title);
	}
	
	/*
	 * 获取标题
	 */
	function _getSubject(){
		return '[注意] 广告到期提醒';
	}
	
	/*
	 * 发送短消息提醒
	 */
	function sendShortMessage($touid,$subject,$content){
		global $db;
		$subject = S::escapeChar($subject);
		$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
		$toUserName = $userService->getUserNameByUserId($touid);
		M::sendNotice(
			array($toUserName),
			array(
				'title' => $subject,
				'content' => $content,
			)
		);
	}
	
	/*
	 * 发送邮件提醒
	 */
	function sendEmail($toemail,$subject,$message){
		require_once(R_P.'require/sendemail.php');/*register.php*/
		$sendinfo = sendemail($toemail,$subject,$message,$additional=null);
	}
	
	/*
	 * 获取广告到期提醒配置
	 */
	function _getConfig(){
		global $db_alterads;
		if($db_alterads){
			return $db_alterads;
		}
		$adverClass = L::loadClass('adver', 'advertisement');
		return $adverClass->getDefaultAlter();
	}
	
	
		
	
	
}