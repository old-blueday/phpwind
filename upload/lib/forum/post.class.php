<?php
!defined('P_W') && exit('Forbidden');

/**
 * 发表帖子操作类
 * fix by sky_hold@163.com
 *
 * @package Thread
 */
class PwPost {

	var $db;
	var $user;
	var $uid;
	var $username;
	var $groupid;
	var $forum; //class PwForum

	var $_G;
	var $isGM;
	var $isBM;
	var $admincheck;
	var $allowsell;
	var $allowencode;
	var $prompt = array();

	var $hours;
	var $errMsg = array();
	var $errMode = false;

	function PwPost(&$forum) {
		global $db,$winddb,$groupid,$_time,$_G,$manager,$windid,$winduid;
		$this->db =& $db;
		$this->user =& $winddb;
		$this->groupid =& $groupid;
		$this->hours =& $_time['hours'];
		$this->forum =& $forum;

		$this->uid =& $winduid;
		$this->username =& $windid;

		$this->_G =& $_G;
		$this->isGM = S::inArray($this->username, $manager);
		$this->isBM = $this->forum->isBM($this->username);
		$this->admincheck = ($this->isGM || $this->isBM);
		$this->allowhide = ($this->forum->foruminfo['allowhide'] && $this->_G['allowhidden']);
		$this->allowsell = ($this->forum->foruminfo['allowsell'] && $this->_G['allowsell']);
		$this->allowencode = ($this->forum->forumset['allowencode'] && $this->_G['allowencode']);
	}

	function forumcheck() {
		if (!$this->forum->isForum()) {
			return $this->showmsg('data_error');
		}
		$this->forum->forumcheck($this->user, $this->groupid);

		if (!$this->admincheck) {
			$this->forum->creditcheck($this->user, $this->groupid);
		}
		if (!$this->isGM && !$this->forum->allowtime($this->hours) && !pwRights($this->isBM, 'allowtime')) {
			return $this->showmsg('forum_allowtime');
		}
	}

	function checkSpecial($special) {
		if (!($this->forum->foruminfo['allowtype'] & pow(2,$special))) {
			if (empty($special) && $this->forum->foruminfo['allowtype'] > 0) {
				$special = (int)log($this->forum->foruminfo['allowtype'],2);
			} else {
				return $this->showmsg('post_allowtype');
			}
		}
		return $special;
	}

	function postcheck() {
		global $db_openpost,$db_postallowtime,$timestamp;
		list($openpost, $poststart, $postend) = explode("\t", $db_openpost);
		$GLOBALS['db_poststart'] = $poststart;
		$GLOBALS['db_postend'] = $postend;

		if ($openpost == 1 && $this->groupid != 3 && $this->groupid != 4) {
			if ($poststart < $postend && ($this->hours < $poststart || $this->hours >= $postend)) {
				return $this->showmsg('post_openpost');
			} elseif ($poststart > $postend && ($this->hours < $poststart && $this->hours >= $postend)) {
				return $this->showmsg('post_openpost');
			}
		}
		if ($this->groupid == '7') {
			return $this->showmsg('reg_check');
		}
		if ($db_postallowtime && $timestamp - $this->user['regdate'] < $db_postallowtime*60) {
			return $this->showmsg('post_newrg_limit');
		}
	}

	function checkUserCredit($add) {
		global $credit;
		isset($credit) || require_once(R_P.'require/credit.php');

		$flag = 0;
		$reduce = array();
		foreach ($add as $key => $value) {
			if ($value < 0) {
				$reduce[$key] = $value;
				$flag |= is_numeric($key) ? 2 : 1;
			}
		}
		if ($reduce) {
			$u_credit = $credit->get($this->uid, ($flag == 1 ? 'COMMON' : ($flag == 2 ? 'CUSTOM' : 'ALL')));
			$err = array();
			foreach ($reduce as $key => $value) {
				if ($u_credit[$key] + $value < 0) {
					$err[] = -$value . $credit->cType[$key];
				}
			}
			if ($err) {
				$GLOBALS['shortCredit'] = implode(',', $err);
				return $this->showmsg('post_credit_enough');
			}
		}
	}

	function updateUserInfo($type, $add, $content = '') {
		global $db_creditset,$credit,$db_upgrade,$timestamp,$db_tcheck;

		if ($this->groupid <> 'guest') {

			require_once(R_P.'require/credit.php');
			$this->user['todaypost'] ++;
			$this->user['monthpost'] ++;
			$this->user['postnum'] ++;
			$this->user['lastpost'] = $timestamp;

			$credit->addLog('topic_' . $type, $add, array(
				'uid'		=> $this->uid,
				'username'	=> $this->username,
				'ip'		=> $GLOBALS['onlineip'],
				'fname'		=> $this->forum->name
			));
			$credit->sets($this->uid, $add, false);

			$this->user['rvrc'] += $add['rvrc'];
			$this->user['money'] += $add['money'];
			$this->user['credit'] += $add['credit'];
			$this->user['currency'] += $add['currency'];

			$usercredit = array(
				'postnum'	=> $this->user['postnum'],
				'digests'	=> $this->user['digests'],
				'rvrc'		=> $this->user['rvrc'],
				'money'		=> $this->user['money'],
				'credit'	=> $this->user['credit'],
				'currency'	=> $this->user['currency'],
				'onlinetime'=> $this->user['onlinetime']
			);
			$upgradeset = unserialize($db_upgrade);

			foreach ($upgradeset as $key => $val) {
				if (is_numeric($key) && $val) {
					foreach ($credit->get($this->user['uid'], 'CUSTOM') as $key => $value) {
						$usercredit[$key] = $value;
					}
					break;
				}
			}
			require_once(R_P.'require/functions.php');
			$memberid = getmemberid(CalculateCredit($usercredit, $upgradeset));
			$userService = L::loadClass('UserService', 'user'); /* @var $userService PW_UserService */
			if ($this->user['memberid'] != $memberid) {
				$userService->update($this->user['uid'], array('memberid' => $memberid));
				$membername = getMembername($memberid);
				$upmemberid	= getNextMemberid($memberid);
				$upmembername = getMembername($upmemberid);
				$upneedcredit = getmemberNeed($upmemberid);
				$userneed = $upneedcredit-CalculateCredit($usercredit, $upgradeset);
				M::sendNotice(
				array($this->user['username']),
				array(
				'title' => getLangInfo('writemsg','user_update_title',array(
					'username'=>$windid
				)),
				'content' => getLangInfo('writemsg','user_update_content',array(
					'membername' => $membername,
					'upmembername' => $upmembername,
					'username' =>$this->user['username'],
					'userneed' =>$userneed
				)),
				)
			);				
			}
			$pwSQL = array(
				'postnum'		=> $this->user['postnum'],
				'todaypost'		=> $this->user['todaypost'],
				'monthpost'		=> $this->user['monthpost'],
				'lastpost'		=> $this->user['lastpost'],
				'uploadtime'	=> $this->user['uploadtime'],
				'uploadnum'		=> $this->user['uploadnum']
			);
			$db_tcheck && $pwSQL['postcheck'] = PwPost::tcheck($content);
			$userService->update($this->uid, array(), $pwSQL);
			$credit->runsql();
			/**
			if (!array_sum($add)) {
				$_cache = getDatastore();
				$_cache->delete('UID_'.$this->uid);
			}
			**/
		} else {
			Cookie('userlastptime',$timestamp);
		}
	}

	function showmsg($msg) {
		if ($this->errMode) {
			$this->errMsg[] = $msg;
		} else {
			Showmsg($msg);
		}
		return true;
	}

	function getprompt(){
		return $this->prompt;
	}

	//static function
	function tcheck($content) {
		$content = trim($content);
		$content = strlen($content)>100 ? substr($content,0,100) : $content;
		return substr(md5($content),5,16);
	}
}

/**
 * 帖子
 *
 * @package Thread
 * @abstract
 */
class postData {
	/** 设置 **/
	var $titlemax;
	var $postmax;
	var $postmin;
	var $threadMinLength;
	var $posturlnum;
	var $db;
	var $post;
	var $forum;
	var $filter;

	var $linkChecker = null;
	var $linkCheckStrategy;
	var $blackListLinkCheckStrategy;

	var $data;
	var $att = null;
	var $tag = null;

	var $hide = 0;
	var $enhide = array();
	var $sell = array();

	var $code_htm;
	var $code_id;
	var $iscontinue = 1;

	function postData(&$post) {
		global $db,$db_titlemax,$db_postmax,$db_postmin,$db_posturlnum,$_G;
		$this->titlemax =& $db_titlemax;
		$this->postmax =& $db_postmax;
		$this->postmin =& $db_postmin;
		$this->threadMinLength = $post->forum->foruminfo['forumset']['contentminlen'];
		$this->db =& $db;
		$this->post =& $post;
		$this->forum =& $post->forum;
		$this->filter = L::loadClass('FilterUtil', 'filter');
		$this->_G =& $_G;
		$this->posturlnum = $_G['posturlnum'];
		$this->db_posturlnum =& $db_posturlnum;
		$this->data = array(
			'fid' => $this->forum->fid,
			'author' => $this->post->username,
			'authorid' => $this->post->uid,
			'title' => '',
			'content' => '',
			'convert' => 1,
			'ifcheck' => 1,
			'ifwordsfb' => $this->filter->code,
			'ifsign' => 0,
			'icon' => 0,
			'hideatt' => 0,
			'aid' => 0,
			'ifupload' => 0,
			'lastposter' => $this->post->username
		);

		$this->initLinkChecker();
	}

	function initLinkChecker() {
		global $db_urlcheck, $db_urlblacklist, $db_urlcheckstrategy, $db_blurlcheckstrategy, $db_urlchecklimit;

		if ($db_urlcheckstrategy || $db_blurlcheckstrategy) {
			$this->linkChecker = L::loadClass('LinkChecker', 'forum');
			$this->linkChecker->setConfig(explode(",", $db_urlcheck), explode(",", $db_urlblacklist), $db_urlchecklimit);
			$this->linkCheckStrategy = $db_urlcheckstrategy;
			$this->blackListLinkCheckStrategy = $db_blurlcheckstrategy;
		}
	}

	function initData($bhv) {
		$this->data = array_merge($this->data, $bhv->resetData());
	}

	//abstract
	function setTitle($title) {}

	function setContent($content) {
		$check_content = $content;
		for ($i = 10; $i < 14; $i++) {
			$check_content = str_replace(Chr($i),'',$check_content);
		}
		$contentLength = strlen(trim($check_content));
		$minLength = empty($this->threadMinLength) ? $this->postmin : $this->threadMinLength;
		if ($contentLength >= $this->postmax || $contentLength < $minLength) {
			if (empty($this->threadMinLength)) return $this->post->showmsg('postfunc_content_limit');
			$GLOBALS['contentMinLength'] = $this->threadMinLength;
			return $this->post->showmsg('postfunc_content_threadlimit');
		}

		if ($this->forum->foruminfo['allowsell'] && strpos($content,"[sell") !== false && strpos($content,"[/sell]") !== false) {
			if (preg_match_all('/\[sell=([\d]+?)(,[\w]+)?\]([^\x00]*?)\[\/sell\]/', $content, $sellList)) {
				$sellMax = max($sellList[1]);
				if ($GLOBALS['db_sellset']['price'] && (int) $sellMax > $GLOBALS['db_sellset']['price']) return false;
				$content = preg_replace('/\[sell=([\d]+)/', "[sell=$sellMax", $content);
			}
		}
		/*
		 if (($GLOBALS['banword'] = $this->wordsfb->comprise($content, false)) !== false) {
			return $this->post->showmsg('content_wordsfb');
			}
			*/
		$this->data['content'] = $content;
		return true;
	}

	function setConvert($convert, $autourl = 1) {
		if ($convert) {
			$autourl && $this->data['content'] = $this->autourl($this->data['content']);
			//增加前台发链接帖限制@modify panjl@2010-11-3
			if($this->posturlnum > 0 && $this->post->user['postnum'] < $this->posturlnum && !$this->post->isGM && $this->urlCheck($this->data['content'])){
				return $this->post->showmsg('postgroup_urlnum_limit');
			}

			if ($this->posturlnum <= 0 && $this->db_posturlnum > 0 && $this->post->user['postnum'] < $this->db_posturlnum && !$this->post->isGM && $this->urlCheck($this->data['content'])) {
				return $this->post->showmsg('postfunc_urlnum_limit');
			}
		}
		$this->data['convert'] = $convert ? 1 : 0;
	}

	function setAnonymous($anonymous) {
		$this->data['anonymous'] = ($anonymous && ($this->post->isGM || $this->forum->forumset['anonymous'] && $this->post->_G['anonymous'])) ? 1 : 0;
		if ($this->data['anonymous']) {
			$this->data['lastposter'] = $GLOBALS['db_anonymousname'];
		}
	}

	function setIfsign($usesign, $usehtml) {
		$ifsign = $usesign ? 1 : 0;
		if ($usehtml && $this->post->_G['htmlcode']) {
			$ifsign += 2;
		}
		$this->data['ifsign'] = $ifsign;
	}

	function setHideatt($hideatt) {
		$this->data['hideatt'] = ($hideatt && ($this->post->isGM || $this->forum->foruminfo['allowhide'] && $this->post->_G['allowhidden'])) ? 1 : 0;
	}

	function setIconid($iconid) {
		$this->data['icon'] = $iconid;
	}

	function setHide($hide) {
		$this->hide = $hide;
	}

	function setEnhide($requireenhide, $enhidervrc, $enhidetype) {
		global $db_enhideset;
		if ($requireenhide) {
			!in_array($enhidetype, $db_enhideset['type']) && $enhidetype = 'rvrc';
			$this->enhide = array($enhidervrc, $enhidetype);
		}
	}

	function setSell($requiresell, $money, $credittype) {
		global $db_sellset;
		if ($requiresell) {
			!in_array($credittype, $db_sellset['type']) && $credittype = 'money';
			$this->sell = array($money, $credittype);
		}
	}

	function setAttachs() {
		if (is_object($this->att)) {
			$this->data['ifupload'] = $this->att->ifupload;
			$this->data['aid'] = $this->att->getAttachNum();
			if ($idrelate = $this->att->getIdRelate()) {
				foreach ($idrelate as $aid => $id) {
					$this->data['content'] = str_replace("[upload=$id]", "[attachment=$aid]", $this->data['content']);
				}
			}
		}
	}
	
	function setData($key, $value) {
		if (isset($this->data[$key])) {
			$this->data[$key] = $value;
		}
	}
	//abstract
	function setIfcheck() {}

	function getIfcheck() {
		return $this->data['ifcheck'];
	}

	function checkdata() {
		$this->data['title'] = S::escapeChar($this->data['title']);
		//$this->data['ifwordsfb'] = $this->wordsfb->ifwordsfb(stripslashes($this->data['content']));

		if ($this->data['convert']) {
			$this->data['content'] = $this->html_check($this->data['content']);
			$this->windcodeCheck();
		} else {
			$this->data['convert'] = 1;
		}
		if ($this->data['ifsign'] < 2) {
			$this->data['content'] = S::escapeChar($this->data['content']);
		} else {
			$this->data['content'] = preg_replace(
			array("/<script.*>.*<\/script>/is","/<(([^\"']|\"[^\"]*\"|'[^']*')*?)>/eis","/javascript/i"),
			array("","\$this->jscv('\\1')","java script"),
			str_replace('.','&#46;',$this->data['content'])
			);
		}
		$this->setIfcheck();
		$this->checkLinks();
		$this->wordFilter();
		$this->setAttachs();
	}

	function checkLinks() {
		if ($this->linkChecker) {
			$this->linkChecker->checkContent($this->data['content']);
			if ($this->linkCheckStrategy && $this->linkChecker->isReachLimit()) {
				if ('verify' == $this->linkCheckStrategy) {
					$this->data['ifcheck'] = 0;
				} elseif ('exception' == $this->linkCheckStrategy) {
					$this->post->showmsg('urlcheck_toomany');
				}
			}
			if ($this->blackListLinkCheckStrategy && $this->linkChecker->haveBlackDomains()) {
				$GLOBALS['blackurl'] = implode(", ", $this->linkChecker->getBlackUrls());
				$this->post->showmsg('urlcheck_inblack');
			}
		}
	}

	function windcodeCheck() {
		foreach (array('wmv','rm','flash') as $key => $value) {
			if (strpos(",{$this->post->_G[media]},",",$value,") === false) {
				$this->data['content'] = preg_replace("/(\[$value=([0-9]{1,3}\,[0-9]{1,3}\,)?)1(\].+?\[\/$value\])/is", "\${1}0\\3", $this->data['content']);
			}
		}
		if (!$this->post->isGM && (!$this->forum->foruminfo['allowhide'] || !$this->post->_G['allowhidden'])) {
			( false !== strpos($this->data['content'],"[post]")) &&  $this->post->prompt['allowhide'] = 1;
			$this->data['content'] = str_replace("[post]","[\tpost]", $this->data['content']);
		} elseif ($this->hide == '1') {
			$this->data['content'] = "[post]".str_replace(array('[post]','[/post]'), "", $this->data['content'])."[/post]";
			$this->data['convert'] = 2;
		} elseif (false !== strpos($this->data['content'], '[post]') && false !== strpos($this->data['content'], '[/post]')) {
			$this->data['convert'] = 2;
		}
		if (!$this->post->isGM && (!$this->forum->forumset['allowencode'] || !$this->post->_G['allowencode'])) {
			( false !== strpos($this->data['content'],"[hide=")) &&  $this->post->prompt['allowencode'] = 1;
			$this->data['content'] = str_replace("[hide=","[\thide=", $this->data['content']);
		} elseif ($this->enhide) {
			$this->data['content'] = preg_replace("/\[hide=(.+?)\]/is","",$this->data['content']);
			$this->data['content'] = "[hide=".$this->enhide[0].",{$this->enhide[1]}]".str_replace("[/hide]","",$this->data['content'])."[/hide]";
			$this->data['convert'] = 2;
		}
		if (!$this->post->isGM && (!$this->forum->foruminfo['allowsell'] || !$this->post->_G['allowsell'])) {
			( false !== strpos($this->data['content'],"[sell=")) && $this->post->prompt['allowsell'] = 1;
			$this->data['content'] = str_replace("[sell=","[\tsell=", $this->data['content']);
		} elseif ($this->sell) {
			$this->data['content'] = str_replace("[/sell]","",preg_replace("/\[sell=(.+?)\]/is","",$this->data['content']));
			$this->data['content'] = "[sell=".$this->sell[0].",{$this->sell[1]}]{$this->data[content]}[/sell]";
			$this->data['convert'] = 2;
		} elseif (false !== strpos($this->data['content'], '[sell') && false !== strpos($this->data['content'], '[/sell]')) {
			$this->data['convert'] = 2;
		}
		if ($this->data['convert'] == 1) {
			$this->data['content'] != convert($this->data['content'],'') && $this->data['convert'] = 2;
		}
	}

	function wordFilter() {
		$this->filter->getFilterResult($this->data['title'] . "\t" . $this->data['content']);
		if ($this->filter->filter_weight) {
			$title_filter_word = '';
			$titlelen = strlen($this->data['title']);
			$arrPos = array_keys($this->filter->filter_word);
			foreach ($arrPos as $key) {
				if ($key < $titlelen) {
					$title_filter_word .= $title_filter_word ? ','.$this->filter->filter_word[$key] : $this->filter->filter_word[$key];
				} else {
					break;
				}
			}
			if ($title_filter_word) {
				$GLOBALS['banword'] = $title_filter_word;
				return $this->post->showmsg('title_wordsfb');
			}
			if ($this->filter->filter_weight == 1) {
				$GLOBALS['banword'] = implode(',',$this->filter->filter_word);
				return $this->post->showmsg('content_wordsfb');
			}
			if( ! $this->iscontinue &&  $this->filter->filter_weight == 2){
				$GLOBALS['banword'] = implode(',',$this->filter->filter_word);
				return $this->showMsg('post_word_check');
			};
			if( ! $this->iscontinue && $this->filter->filter_weight == 3){
				$GLOBALS['banword'] = implode(',',$this->filter->filter_word);
				return $this->showMsg('enter_words');
			}
			$this->data['ifwordsfb'] = 0;
			return true;
		}
		if(! $this->iscontinue && !$this->getIfcheck()){
				return $this->showMsg('post_check');
		}
		return true;
	}

	function showMsg($msg ){
		if(defined('AJAX'))
		return $this->post->showmsg("continue\t" . getLangInfo('refreshto', $msg));
		else
		return $this->post->showmsg($msg);
	}

	function conentCheck() {
		global $db_tcheck;
		if ($db_tcheck && $this->post->user['postcheck'] == PwPost::tcheck($this->data['content'])) {
			return $this->post->showmsg('content_same');
		}
	}

	function getData() {
		$this->checkdata();
		return $this->data;
	}

	function urlCheck($str) {
		return (strpos($str,'[/URL]') !== false || strpos($str,'[/url]') !== false);
	}

	function html_check($souce) {
		global $db_bbsurl,$db_picpath,$db_attachname;
		if (strpos($souce,$db_bbsurl) !== false) {
			$souce = str_replace($db_picpath, 'p_w_picpath', $souce);
			$souce = str_replace($db_attachname, 'p_w_upload', $souce);
		}
		return $souce;
	}

	function jscv($code) {
		$code = str_replace('\\"','"',$code);
		$code = preg_replace('/[\s]on[\w]+\s*=\s*(\\\"|\\\\\').+?\\1/is',"",$code);
		$code = preg_replace("/[\s]on[\w]+\s*=[^\s]*/is","",$code);
		return '<'.$code.'>';
	}

	function autourl($message){
		global $db_autoimg,$db_cvtimes;
		$this->code_htm = array();
		$this->code_id  = 0;
		if (strpos($message,"[code]") !== false && strpos($message,"[/code]") !== false) {
			$message = preg_replace("/\[code\](.+?)\[\/code\]/eis","\$this->code_check('\\1')", $message, $db_cvtimes);
		}
		if ($db_autoimg == 1) {
			$message = preg_replace(
			array("/(?<=[^\]a-z0-9-=\"'\\/])((https?|ftp):\/\/|www\.)([a-z0-9\/\-_+=.~!%@?#%&;:$\\│]+\.(gif|jpg|png))(?![\w\/\-+\.$&?#]{1})/i"),
			array("[img]\\1\\3[/img]"),
				' ' . $message
			);
			$message = substr($message,1);
		}
		$message = preg_replace(
		array(
				"/(?<=[^\]a-z0-9-=\"'\\/])((https?|ftp|gopher|news|telnet|mms|rtsp):\/\/|www\.)([a-z0-9\/\-_+=.~!%@?#%&;:$\\│\|]+)/i",
				"/(?<=[^\]a-z0-9\/\-_.~?=:.])([_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,4}))/i"
				),
				array(
				"[url]\\1\\3[/url]",
				"[email]\\0[/email]"
				),
			' '.$message
				);
				if (is_array($this->code_htm)) {
					foreach($this->code_htm as $key => $value){
						$message = str_replace("<\twind_phpcode_$key\t>", $value, $message);
					}
				}
				$message = substr($message,1);
				return $message;
	}

	function code_check($code){
		$this->code_id++;
		$this->code_htm[$this->code_id] = '[code]' . str_replace('\\"','"',$code) . '[/code]';
		return "<\twind_phpcode_{$this->code_id}\t>";
	}
}

/**
 * 主帖
 *
 * @package Thread
 */
class topicPostData extends postData {

	function topicPostData(&$post) {
		parent::postData($post);
		$this->data = array_merge($this->data,array(
			'w_type' => 0,
			'digest' => 0,
			'topped' => 0,
			'special' => 0,
			'ifmail' => 0,
			'tpcstatus' => 0,
			'ifmagic' => 0,
			'magic' => '',
			'modelid' => 0,
		));
	}

	function setTitle($title) {
		$title = trim($title);
		if (empty($title) || strlen($title) > $this->titlemax) {
			return $this->post->showmsg('postfunc_subject_limit');
		}
		/*
		 if (($GLOBALS['banword'] = $this->wordsfb->comprise($title)) !== false) {
			return $this->post->showmsg('title_wordsfb');
			}
			*/
		$this->data['title'] = $title;
	}

	function setWtype($p_type, $p_sub_type, $t_per, $t_db) {
		if (!$p_type || empty($t_db[$p_type]) || ($t_per == 0 && !$this->post->admincheck)) {
			$w_type = 0;
		} else {
			$w_type = $p_type;
		}
		if (!$p_sub_type || empty($t_db[$p_sub_type]) || ($t_per == 0 && !$this->post->admincheck)) {
			$w_sub_type = 0;
		} else {
			$w_sub_type = $p_sub_type;
		}
		$w_type = $w_sub_type ? $w_sub_type : $w_type;
		if ($GLOBALS['db_forcetype'] && $w_type == '0') {
			return $this->post->showmsg('force_tid_select');
		}
		$this->data['w_type'] = $w_type;
	}

	function setTags($tags) {
		global $db_iftag;
		if ($db_iftag) {
			/*
			 if (($GLOBALS['banword'] = $this->wordsfb->comprise($tags)) !== false) {
				return $this->post->showmsg('tag_wordsfb');
				}
				*/
			$this->tag = new BbsTag($this->post);
			$this->data['tags'] = $this->tag->setTags($tags);
		}
	}

	function setIfmail($mail,$newrp) {
		global $db_replysendmail,$db_replysitemail;
		$ifmail = ($mail && $db_replysendmail) ? 1 : 0;
		$newrp && $db_replysitemail && $ifmail += 2;
		$this->data['ifmail'] = $ifmail;
	}

	function setDigest($digest) {
		if ($digest && !pwRights($this->post->isBM,'digestadmin')) {
			$digest = 0;
		}
		$this->data['digest'] = $digest;
	}

	function setTopped($topped) {
		if ($topped && !$this->post->isGM && (pwRights($this->post->isBM, 'topped') < $topped)) {
			$topped = 0;
		}
		$this->data['topped'] = $topped;
	}

	function setMagic($magicid,$magicname) {
		global $db_windmagic;
		if ($db_windmagic) {
			$this->data['ifmagic'] = $magicid ? 1 : 0;
			$this->data['magic'] = $magicid ? ($magicid . "\t" . $magicname) : '';
		}
	}

	function setStatus($pos, $value = '1') {
		setstatus($this->data['tpcstatus'], $pos, $value);
	}

	function setIfcheck() {
		if (($this->forum->foruminfo['f_check'] == 1 || $this->forum->foruminfo['f_check'] == 3) && $this->post->_G['atccheck'] && !$this->post->admincheck && $this->post->groupid != 3) {
			$ifcheck = 0;
		} else {
			$ifcheck = (!$this->post->admincheck && $this->filter->filter_weight == 2) ? 0 : 1;
		}
		$this->data['ifcheck'] = $ifcheck;

	}
}

/**
 * 回帖
 *
 * @package Thread
 */
class replyPostData extends postData {

	function replyPostData(&$post) {
		parent::postData($post);
	}

	function setTitle($title) {
		$title = trim($title);
		if (strlen($title) > $this->titlemax) {
			return $this->post->showmsg('postfunc_subject_limit');
		}
		/*
		 if (stripslashes($title) == 'Re:' . $this->tpcArr['subject']) {
			$title = '';
			}
			if (($GLOBALS['banword'] = $this->wordsfb->comprise($title)) !== false) {
			return $this->post->showmsg('title_wordsfb');
			}
			*/
		$this->data['title'] = $title;
	}

	function setIfcheck() {
		if ($this->forum->foruminfo['f_check'] > 1 && $this->post->_G['atccheck'] && !$this->post->admincheck && $this->post->groupid != 3) {
			$ifcheck = 0;
		} else {
			$ifcheck = (!$this->post->admincheck && $this->filter->filter_weight == 2) ? 0 : 1;
		}
		$this->data['ifcheck'] = $ifcheck;
	}
}

/**
 * BBS标签
 *
 * @package Thread
 */
class BbsTag {

	var $post;
	var $tags;
	var $db;

	function BbsTag(&$post) {
		global $db;
		$this->tags = array();
		$this->db =& $db;
		$this->post =& $post;
	}

	function callback($matches){
		$this->tags[] = $matches[1];
		return '';
	}
	function setTags($tags) {
		if (!$tags) {
			return '';
		}
		$tags = htmlspecialchars_decode($tags);
		$tags = stripslashes($tags);
		$tags = str_replace(array('“','”'),'"',$tags);
		$tags = preg_replace_callback('/("[^"]+")/', array($this,'callback'), $tags);
		$this->tags = array_merge($this->tags,explode(" ",preg_replace('/\s+/is',' ',trim($tags))));
		$this->tags = array_unique($this->tags);
		if (count($this->tags) > 5) {
			return $this->post->showmsg("tags_num_limit");
		}
		foreach ($this->tags as $key => $value) {
			$this->tags[$key] = trim(str_replace('&nbsp;',' ',$this->tags[$key]));
			if (!$this->tags[$key]){
				unset($this->tags[$key]);continue;
			}
			if (strlen($this->tags[$key])>15 || strlen($this->tags[$key])<3) {
				return $this->post->showmsg('tag_length_limit');
			}
		}
		return implode(" ",$this->tags);
	}

	function insert($tid) {
		$sql = $upids = $tagids = array();
		foreach ($this->tags as $key => $value) {
			if (!$value)
			continue;
			$rt = $this->db->get_one("SELECT tagid FROM pw_tags WHERE tagname=".S::sqlEscape($value));
			if (!$rt) {
				$this->db->update("INSERT INTO pw_tags SET ".S::sqlSingle(array('tagname'=>$value,'num'=>1)));
				$tagid = $this->db->insert_id();
			} else {
				$tagid = $rt['tagid'];
				$upids[] = $tagid;
			}
			$sql[] = array($tagid, $tid);
			$tagids[] = $tagid;
		}
		if ($upids) {
			$this->db->update("UPDATE pw_tags SET num=num+1 WHERE tagid IN(" . S::sqlImplode($upids) . ')');
		}
		if ($sql) {
			$this->db->update("INSERT INTO pw_tagdata (tagid,tid) VALUES " . S::sqlMulti($sql));
		}
		if ($tagids) {
			$statistics = L::loadClass('Statistics', 'datanalyse');
			$statistics->addtag($tagids);
		}
	}

	function update($tid) {
		$tagids	= array();
		$tags = array();
		$query	= $this->db->query("SELECT * FROM pw_tagdata td LEFT JOIN pw_tags t USING(tagid) WHERE td.tid=" . S::sqlEscape($tid));
		while ($rt = $this->db->fetch_array($query)) {
			if (!in_array($rt['tagname'], $this->tags)) {
				$tagids[] = $rt['tagid'];
			} else {
				$tags[] = $rt['tagname'];
			}
		}
		if ($tagids) {
			$statistics = L::loadClass('Statistics', 'datanalyse');
			$statistics->deletetag($tagids);

			$tagids = S::sqlImplode($tagids);
			$this->db->update("DELETE FROM pw_tagdata WHERE tid=" . S::sqlEscape($tid) . " AND tagid IN($tagids)");
			$this->db->update("UPDATE pw_tags SET num=num-1 WHERE tagid IN($tagids)");
		}
		if ($this->tags = array_diff($this->tags, $tags)) {
			$this->insert($tid);
		}
	}

	function relate($subject,$content){
		@include pwCache::getPath(D_P.'data/bbscache/tagdb.php');
		$i    = 0;
		$tags = '';
		if(!$tagdb){
			return '';
		}
		foreach ($tagdb as $tag => $num) {
			if (strpos($subject,$tag) !== false || strpos($content,$tag) !== false) {
				$tags .= $tags ? ' '.$tag : $tag;
				if(++$i > 9) break;
			}
		}
		return $tags;
	}
}
?>