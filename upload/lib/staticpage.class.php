<?php
!defined('P_W') && exit('Forbidden');

L::loadClass('forum', 'forum', false);
require_once (R_P . 'require/showimg.php');
require_once (R_P . 'require/bbscode.php');

/**
 * StaticPage
 * 
 * @package Cache
 */
class PW_StaticPage {
	
	var $db;
	var $htmdir;
	var $perpage;
	var $fieldadd;
	var $tablaadd;
	var $tpl;
	
	var $forum = null;
	var $attachShow = null;
	
	var $datedir;
	var $fid;
	var $tid;
	var $menu;
	var $msg_guide;
	var $css_path;
	var $subject;
	var $forumtitle;
	var $guidename;
	
	var $vars = array();

	function PW_StaticPage() {
		global $db, $db_readdir, $db_readperpage;
		$this->db = & $db;
		$this->htmdir = & $db_readdir;
		$this->perpage = & $db_readperpage;
		
		$this->initVars();
		$this->initUserColumn();
		$this->initStyle();
	}

	function initVars() {
		$this->vars = L::config();
		$this->vars['imgpath'] = $GLOBALS['imgpath'];
		$this->vars['db_bbstitle'] = $this->vars['db_bbstitle']['other'];
		$this->vars['db_bbsname_a'] = addslashes($this->vars['db_bbsname']); //模版内用到
		$this->vars['wind_version'] = $GLOBALS['wind_version'];
		$this->vars['db_userurl'] = $GLOBALS['db_userurl'];
		$this->vars['creditnames'] = pwCreditNames();

		if (!is_array($this->vars['db_union'])) {
			$this->vars['db_union'] = explode("\t", stripslashes($this->vars['db_union']));
			$this->vars['db_union'][0] && $this->vars['db_hackdb'] = array_merge((array) $this->vars['db_hackdb'], (array) unserialize($this->vars['db_union'][0]));
		}
		list($this->vars['_Navbar']) = pwNavBar();
	}

	function initForum($fid) {
		static $prefid = null;
		if ($this->forum == null || $prefid != $fid) {
			$this->forum = new PwForum($fid);
			if (!$this->forum->isForum() || !$this->forum->isOpen() || !$this->forum->foruminfo['allowhtm']) {return false;}
			$prefid = $fid;
			list($guidename, $forumtitle) = $this->forum->getTitle();
			$this->forumtitle = "|$forumtitle";
			$this->guidename = $this->forum->headguide($guidename);
			$this->vars['forumname'] = $this->forum->name;
		}
		return true;
	}

	function initStyle() {
		$style = L::style(null, L::config('db_defaultstyle'));
		foreach ($style as $key => $value) {
			$this->vars[$key] = $value;
			$GLOBALS[$key] = $value;
		}
		$this->vars['css_path'] = D_P . 'data/style/wind_css.htm';
		
		if (file_exists(R_P . "template/{$style[tplpath]}/readtpl.htm")) {
			$this->tpl = R_P . "template/{$style[tplpath]}/readtpl.htm";
		} else {
			$this->tpl = R_P . "template/wind/readtpl.htm";
		}
	}

	function initUserColumn() {
		$this->fieldadd = '';
		$this->tablaadd = '';
		if ($customfield = L::config('customfield', 'cache_read')) {
			foreach ($customfield as $key => $val) {
				$this->fieldadd .= ",mb.field_" . intval($val['id']);
			}
			$this->fieldadd && $this->tablaadd = " LEFT JOIN pw_memberinfo mb ON mb.uid=t.authorid";
		}
		$this->vars['customfield'] = $customfield;
	}

	function update($tid) {
		$this->tid = $tid;
		if (!$readdb = $this->getReadContent()) {return;}
		$this->createHtml($readdb);
	}

	function isHideContent($content) {
		if ($this->forum->foruminfo['allowsell'] && strpos($content, "[sell") !== false && strpos($content, "[/sell]") !== false) {return true;}
		if ($this->forum->foruminfo['allowhide'] && strpos($content, "[post]") !== false && strpos($content, "[/post]") !== false) {return true;}
		if ($this->forum->forumset['allowencode'] && strpos($content, '[hide=') !== false && strpos($content, '[/hide]') !== false) {return true;}
		return false;
	}

	function getReadContent() {
		$readdb = array();
		$pw_tmsgs = GetTtable($this->tid);
		$read = $this->db->get_one("SELECT t.*,tm.*,m.uid,m.username,m.oicq,m.groupid,m.memberid,m.icon AS micon ,m.hack,m.honor,m.signature,m.regdate,m.medals,m.userstatus,md.onlinetime,md.postnum,md.digests,md.rvrc,md.money,md.credit,md.currency,md.starttime,md.thisvisit,md.lastvisit,mb.* FROM pw_threads t LEFT JOIN $pw_tmsgs tm ON t.tid=tm.tid LEFT JOIN pw_members m ON m.uid=t.authorid LEFT JOIN pw_memberdata md ON md.uid=t.authorid $this->tablaadd WHERE t.tid=" . S::sqlEscape($this->tid));
		
		if (!$read || $read['special'] || !$read['ifcheck']) {return false;}
		$this->fid = $read['fid'];
		$this->datedir = date('ym', $read['postdate']);
		
		if (file_exists(R_P . "$this->htmdir/$this->fid/$this->datedir/$this->tid.html")) {
			P_unlink(R_P . "$this->htmdir/$this->fid/$this->datedir/$this->tid.html");
		}
		if (!$this->initForum($this->fid)) {return false;}
		if ($this->isHideContent($read['content'])) {return false;}
		
		$this->setSeosetting(&$read);
		
		$this->vars['forumtitle'] = $this->forumtitle;
		$this->vars['msg_guide'] = $this->guidename . "<em>&gt;</em><a href=\"read.php?tid=$this->tid\">$read[subject]</a>";
		$this->vars['db_metakeyword'] = $read['subject'] . str_replace(array('|', ' - '), ',', $this->forumtitle) . 'phpwind';
		$this->vars['subject'] = $read['subject'];
		$this->vars['titletop1'] = substrs('Re:' . str_replace('&nbsp;', ' ', $read['subject']), L::config('db_titlemax') - 2);
		$this->vars['hits'] = $read['hits'];
		$this->vars['replies'] = $read['replies'];
		$this->vars['tid'] = $this->tid;
		$this->vars['fid'] = $this->fid;
		$this->vars['pwforum'] = $this->forum;
		$this->vars['postUrl'] = 'post.php?fid=' . $this->fid;
		$_pids = array();
		$read['aid'] && $_pids[] = 0;
		
		$count = $read['replies'] + 1;
		$this->vars['pages'] = numofpage($count, 1, ceil($count / $this->perpage), "$GLOBALS[db_bbsurl]/read.php?tid=$this->tid&");
		
		$read['pid'] = 'tpc';
		$readdb[] = $read;
		
		if ($read['replies'] > 0) {
			$readnum = $this->perpage - 1;
			$pw_posts = GetPtable($read['ptable']);
			$query = $this->db->query("SELECT t.*,m.uid,m.username,m.oicq,m.groupid,m.memberid,m.icon AS micon,m.hack,m.honor,m.signature,m.regdate,m.medals,m.userstatus,md.onlinetime,md.postnum,md.digests,md.rvrc,md.money,md.credit,md.currency,md.starttime,md.thisvisit,md.lastvisit, mb.* FROM $pw_posts t LEFT JOIN pw_members m ON m.uid=t.authorid LEFT JOIN pw_memberdata md ON md.uid=t.authorid $this->tablaadd WHERE t.tid=" . S::sqlEscape($this->tid) . " AND ifcheck='1' ORDER BY postdate LIMIT 0,$readnum");
			while ($read = $this->db->fetch_array($query)) {
				if ($this->isHideContent($read['content'])) {return false;}
				$read['aid'] && $_pids[] = $read['pid'];
				$readdb[] = $read;
			}
			$this->db->free_result($query);
		}
		if ($_pids) {
			$this->attachShow = new attachShow(false);
			$this->attachShow->init($this->tid, $_pids);
		}
		$this->vars['db_menuinit'] = "'td_post' : 'menu_post','td_post1' : 'menu_post','td_hack' : 'menu_hack'";
		
		$bandb = $this->forum->forumBan($readdb);
		$authorids = array($read['authorid']);
		$start_limit = 0;
		foreach ($readdb as $key => $read) {
			isset($bandb[$read['authorid']]) && $read['groupid'] = 6;
			$authorids[] = $read['authorid'];
			$readdb[$key] = $this->htmread($read, $start_limit++);
			$this->vars['db_menuinit'] .= ",'td_read_" . $read['pid'] . "':'menu_read_" . $read['pid'] . "'";
		}
		if (L::config('db_showcustom')) {
			$this->vars['customdb'] = $this->getCustomdb($authorids);
		}
		
		return $readdb;
	}

	function setSeosetting($read) {
		list($this->vars['webPageTitle'], $this->vars['metaDescription'], $this->vars['metaKeywords']) = bbsSeoSettings('read', '', $this->vars['forumname'], '', &$read['subject']);
	}

	function getCustomdb($authorids) {
		$cids = $customdb = array();
		foreach (L::config('_CREDITDB') as $key => $value) {
			if (strpos(L::config('db_showcustom'), ",$key,") !== false) {
				$cids[] = $key;
			}
		}
		if ($cids) {
			$cids = S::sqlImplode($cids);
			$query = $this->db->query("SELECT uid,cid,value FROM pw_membercredit WHERE uid IN(" . S::sqlImplode($authorids) . ") AND cid IN($cids)");
			while ($rt = $this->db->fetch_array($query)) {
				$customdb[$rt['uid']][$rt['cid']] = $rt['value'];
			}
		}
		return $customdb;
	}

	function createHtml($readdb) {
		extract($this->vars);
		ob_end_clean();
		ObStart();
		
		include S::escapePath($this->tpl);
		$ceversion = defined('CE') ? 1 : 0;
		$content = str_replace(array('<!--<!---->', '<!---->'), array('', ''), ob_get_contents());
		$content .= "<script type=\"text/javascript\">(function(d,t){
var url=\"http://init.phpwind.net/init.php?sitehash={$db_sitehash}&v=$wind_version&c=$ceversion\";
var g=d.createElement(t);g.async=1;g.src=url;d.body.appendChild(g)}(document,\"script\"));</script>";
		ob_end_clean();
		ObStart();
		if (!is_dir(R_P . $this->htmdir . '/' . $this->fid)) {
			@mkdir(R_P . $this->htmdir . '/' . $this->fid);
			@chmod(R_P . $this->htmdir . '/' . $this->fid, 0777);
			pwCache::writeover(R_P . "$this->htmdir/$this->fid/index.html", '');
			@chmod(R_P . "$this->htmdir/$this->fid/index.html", 0777);
		}
		if (!is_dir(R_P . $this->htmdir . '/' . $this->fid . '/' . $this->datedir)) {
			@mkdir(R_P . $this->htmdir . '/' . $this->fid . '/' . $this->datedir);
			@chmod(R_P . $this->htmdir . '/' . $this->fid . '/' . $this->datedir, 0777);
			pwCache::writeover(R_P . "$this->htmdir/$this->fid/$this->datedir/index.html", '');
			@chmod(R_P . "$this->htmdir/$this->fid/$this->datedir/index.html", 0777);
		}
		pwCache::writeover(R_P . "$this->htmdir/$this->fid/$this->datedir/$this->tid.html", $content,  "rb+", 0);
		@chmod(R_P . "$this->htmdir/$this->fid/$this->datedir/$this->tid.html", 0777);
	}

	function htmread($read, $start_limit) {
		global $imgpath, $db_ipfrom, $db_windpost, $db_windpic, $db_signwindcode, $db_shield;
		$lpic = L::config('lpic', 'cache_read');
		$ltitle = L::config('ltitle', 'cache_read');
		$_MEDALDB = L::config('_MEDALDB', 'cache_read');
		$read['lou'] = $start_limit;
		$start_limit == $count - 1 && $read['jupend'] = '<a name=lastatc></a>';
		
		$read['ifsign'] < 2 && $read['content'] = str_replace("\n", "<br>", $read['content']);
		$read['groupid'] == '-1' && $read['groupid'] = $read['memberid'];
		$anonymous = $read['anonymous'] ? 1 : 0;
		if ($read['groupid'] != '' && $anonymous == 0) {
			!$lpic[$read['groupid']] && $read['groupid'] = 8;
			$read['lpic'] = $lpic[$read['groupid']];
			$read['level'] = $ltitle[$read['groupid']];
			$read['regdate'] = get_date($read['regdate'], "Y-m-d");
			$read['lastlogin'] = get_date($read['lastvisit'], "Y-m-d");
			$read['aurvrc'] = floor($read['rvrc'] / 10);
			$read['author'] = $read['username'];
			$read['ontime'] = (int) ($read['onlinetime'] / 3600);
			$tpc_author = $read['author'];
			list($read['face'],,$httpWidth,$httpHeight,,,,$read['facesize']) = showfacedesign($read['micon'], true, 'm');
			if ($httpWidth > 120 || $httpHeight > 120 || $read['facesize'] == '') {
				$read['facesize'] = ' width="120" height="120"';
			}
			list($read['posttime']) = getLastDate($read['postdate']);
			if ($db_ipfrom == 1) $read['ipfrom'] = ' From:' . $read['ipfrom'];
			
			if (L::config('md_ifopen', 'cache_read') && $read['medals']) {
				$medals = '';
				$md_a = explode(',', $read['medals']);
				foreach ($md_a as $key => $value) {
					if ($value) $medals .= "<img src=\"{$_MEDALDB[$value][smallimage]}\" title=\"{$_MEDALDB[$value][name]}\" /> ";
				}
				$read['medals'] = $medals . '<br />';
			} else {
				$read['medals'] = '';
			}
			
			if ($read['ifsign'] == 1 || $read['ifsign'] == 3) {
				global $sign;
				if (!$sign[$read['author']]) {
					global $db_signmoney, $db_signgroup, $tdtime;
					if (strpos($db_signgroup, ",$read[groupid],") !== false && $db_signmoney) {
						$read['signature'] = '';
					} else {
						if ($db_signwindcode && getstatus($read['userstatus'], PW_USERSTATUS_SIGNCHANGE)) {
							$read['signature'] = convert($read['signature'], $db_windpic, 2);
						}
						$read['signature'] = str_replace("\n", "<br>", $read['signature']);
					}
					$sign[$read['author']] = $read['signature'];
				} else {
					$read['signature'] = $sign[$read['author']];
				}
			} else {
				$read['signature'] = '';
			}
		} else {
			$read['face'] = "$imgpath/face/none.gif";
			$read['lpic'] = '8';
			$read['level'] = $read['digests'] = $read['postnum'] = $read['money'] = $read['regdate'] = $read['lastlogin'] = $read['aurvrc'] = $read['credit'] = '*';
			if ($anonymous) {
				$read['signature'] = $read['honor'] = $read['medals'] = $read['ipfrom'] = '';
				$read['author'] = $GLOBALS['db_anonymousname'];
				$read['authorid'] = 0;
				foreach (L::config('customfield', 'cache_read') as $key => $val) {
					$field = "field_" . (int) $val['id'];
					$read[$field] = '*';
				}
			}
		}
		$read['postdate'] = get_date($read['postdate']);
		$read['mark'] = '';
		if ($read['ifmark']) {
			$markdb = explode("\t", $read['ifmark']);
			foreach ($markdb as $key => $value) {
				$read['mark'] .= "<li>$value</li>";
			}
		}
		if ($read['icon']) {
			$read['icon'] = "<img src=\"$imgpath/post/emotion/$read[icon].gif\" align=left border=0>";
		} else {
			$read['icon'] = '';
		}
		/**
		 * 动态判断发帖是否需要转换
		 */
		$tpc_shield = 0;
		if ($read['ifshield'] || $read['groupid'] == 6 && $db_shield) {
			$read['subject'] = $read['icon'] = '';
			$read['content'] = shield($read['ifshield'] ? ($read['ifshield'] == 1 ? 'shield_article' : 'shield_del_article') : 'ban_article');
			$tpc_shield = 1;
		}
		if (!$tpc_shield) {
			$wordsfb = L::loadClass('FilterUtil', 'filter');
			if (!$wordsfb->equal($read['ifwordsfb'])) {
				$read['content'] = $wordsfb->convert($read['content']);
			}
			if ($read['ifconvert'] == 2) {
				$read['content'] = preg_replace("/\[sell=(.+?)\]/is", "", $read['content']);
				$read['content'] = preg_replace("/\[hide=(.+?)\]/is", "", $read['content']);
				$read['content'] = str_replace(array('[/hide]', '[/sell]', '[post]', '[/post]'), '', $read['content']);
				$read['content'] = convert($read['content'], $db_windpost);
			} else {
				strpos($read['content'], '[s:') !== false && $read['content'] = showface($read['content']);
			}
			if ($read['aid'] && $this->attachShow->isShow($read['ifhide'], $tid)) {
				$read += $this->attachShow->parseAttachs($read['pid'], $read['content'], false);
			}
		}
		if ($read['remindinfo']) {
			$remind = explode("\t", $read['remindinfo']);
			$remind[0] = str_replace("\n", "<br />", $remind[0]);
			$remind[2] && $remind[2] = get_date($remind[2]);
			$read['remindinfo'] = $remind;
		}
		$this->forum->foruminfo['copyctrl'] && $read['content'] = preg_replace("/<br>/eis", "copyctrl('$read[colour]')", $read['content']);
		
		$read['alterinfo'] && $read['content'] .= "<br><br><br><font color=gray>[ $read[alterinfo] ]</font>";
		return $read;
	}
}
?>