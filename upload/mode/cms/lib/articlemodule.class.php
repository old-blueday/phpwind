<?php
!defined('M_P') && exit('Forbidden');
require_once (M_P . 'lib/base/module.class.php');

/**
 * 文章相关的会话bean
 * @author xiejin
 *
 */
class PW_ArticleModule extends PW_Module {
	var $PAGECUT = '[###page###]'; //分页符

	var $articleId;
	var $subject;
	var $descrip;
	var $author;
	var $user;
	var $userId;
	var $jumpUrl;
	var $fromInfo;
	var $fromUrl;
	var $columnId;
	var $ifcheck;
	var $postDate;
	var $modifyDate;
	var $ifAttach;
	var $sourceType;
	var $sourceId;
	var $hits;
	var $relate = array();

	var $pageCount;
	var $content;
	var $attach = array();
	var $_filterUitil;

	function __construct() {
		parent::__construct();
		$this->_filterUitil = L::loadClass('FilterUtil', 'filter');
	}

	function PW_ArticleModule() {
		$this->__construct();
	}

	/**
	 * 设置文章 主题
	 * @param string $subject
	 */
	function setSubject($subject) {
		if (empty($subject)) {
			$this->addError('文章<span class=\'s1\'>标题</span>不能为空');
		}
		if (strlen($subject) > 80) {
			$this->addError('文章<span class=\'s1\'>标题</span>长度不能大于<span class=\'s1\'>80</span>字节');
		}
		if( ($bword = $this->_filterUitil->comprise($subject)) &&  $GLOBALS['iscontinue'] != 1 ){ 
			$this->addError("文章<span class='s1'>标题</span>中含有敏感词：<span class='s1'>" . $bword . "</span>，禁止发表，请返回修改");
		}
		$subject = $this->_filterUitil->convert($subject);
		$this->subject = pwHtmlspecialchars_decode($subject);
	}

	/**
	 * 设置文章内容
	 * @param string $content
	 * @param int $page
	 */
	function setContent($content, $page = 0) {
		if (empty($content)) {
			$this->addError('文章<span class=\'s1\'>内容</span>不能为空');
		}
		if (strlen($content) > 50000) {
			$this->addError('文章<span class=\'s1\'>内容</span>过大');
		}
		if( ($bword = $this->_filterUitil->comprise($content)) &&  $GLOBALS['iscontinue'] != 1) {
			$this->addError("文章内容中含有敏感词：<span class='s1'>" . $bword . "</span>，禁止发表，请返回修改");
		}
		$content = $this->_filterUitil->convert($content);
		$content = htmlspecialchars($content);
		//$content = html_check($content);
		foreach (array('wmv', 'rm', 'flash') as $key => $value) {
			if (strpos(",{$GLOBALS['_G']['media']},", ",$value,") === false) {
				$content = preg_replace("/(\[$value=([0-9]{1,3}\,[0-9]{1,3}\,)?)1(\].+?\[\/$value\])/is", "\${1}0\\3", $content);
			}
		}
		$content = preg_replace(array("/<script.*>.*<\/script>/is", "/<(([^\"']|\"[^\"]*\"|'[^']*')*?)>/eis",
			"/javascript/i"), array("", "\$this->_jscv('\\1')", "java script"), str_replace('.', '&#46;', $content));
		$this->content = $this->_cookContentByPage($content, $page);
	}

	/**
	 * 过滤javascript代码
	 * @param string $code
	 */
	function _jscv($code) {
		$code = str_replace('\\"','"',$code);
		$code = preg_replace('/[\s]on[\w]+\s*=\s*(\\\"|\\\\\').+?\\1/is',"",$code);
		$code = preg_replace("/[\s]on[\w]+\s*=[^\s]*/is","",$code);
		return '<'.$code.'>';
	}

	/**
	 * 设置文章摘要
	 * @param string $descrip
	 */
	function setDescrip($descrip) {
		if( ($bword = $this->_filterUitil->comprise( $descrip ))  &&  $GLOBALS['iscontinue'] != 1) {
			$this->addError("文章<span class='s1'>摘要</span>中包含禁用敏感词：<span class='s1'>" . $bword. "</span>，禁止发表，请返回修改");
		 } 
		$descrip = $this->_filterUitil->convert( $descrip );
		if (empty($descrip)) {
			$descrip = substrs($this->_filterWindCode(), 200);
		}
		if (strlen($descrip) > 255) {
			$this->addError('<span class=\'s1\'>描述</span>内容不能大于<span class=\'s1\'>255</span>字节');
		}
		$this->descrip = $descrip;
	}

	function _filterWindCode(){
		return trim(str_replace(array($this->PAGECUT,'\[s:[0-9]*\]'),'',stripWindCode(strip_tags($this->content))),' ');
	}

	/**
	 * 设置文章所属栏目ID
	 * @param int $channelId
	 */
	function setColumnId($columnId) {
		$columnId = (int) $columnId;
		if (!$columnId) {
			$this->addError('没有选择<span class=\'s1\'>栏目<span>');
		}
		$this->columnId = $columnId;
	}

	/**
	 * 设置相关文章
	 * @param array $relate
	 * @return string
	 */
	function setRelate($relate) {
		if (!$relate['subject'] && !$relate['url']) {return false;}
		$temp = array();
		foreach ($relate['subject'] as $key => $value) {
			if ($value && $relate['url'][$key] && $this->_checkUrl($relate['url'][$key])) {
				$temp[] = array('subject' => $value, 'url' => $relate['url'][$key]);
			}
		}
		$this->relate = $temp;
	}

	/**
	 * 设置附件
	 * @param array $oldatt_desc
	 * @param array $keep
	 */
	function setAttach($flashatt, $oldatt_desc = array()) {
		global $db_allowupload, $_G;
		$attachs = $this->attach ? $this->attach : array();
		$attachs = $this->_cookOldAttachs($attachs, $oldatt_desc);
		C::loadClass('articleupload', 'upload', false);
		$uploaddb = array();
		if ($db_allowupload && $_G['allowupload'] && (PwUpload::getUploadNum() || $flashatt)) {
			$articleUpload = new ArticleUpload();
			$articleUpload->setFlashAtt($flashatt, intval($_POST['savetoalbum']), intval($_POST['albumid']));
			PwUpload::upload($articleUpload);
			$uploaddb = $articleUpload->getAttachs();
		}
		$this->attach = (array)$attachs + (array)$uploaddb;
	}

	/**
	 * 获取某分页文章内容
	 * @param $page
	 */
	function getPageContent($page = 1) {
		if ($page === 'add') return '';
		if ($page === 'all') return $this->content;
		$page = (int) $page;
		$contents = $this->_explodeContent();
		$key = $page - 1;
		if (isset($contents[$key])) {
			return count($contents) > 1 ? $this->autoCloseTags($contents[$key]) : $contents[$key];
		}
		$this->addError('分页数有误');
	}

	/**
	 * 自动补全ubb
	 * @param $content
	 */
	function autoCloseTags($content) {
		$openTags = array('paragraph', 'hr', 'attachment', 's');
		//$closeTags = array('code', 'payto', 'list', 'u', 'b', 'i', 'li', 'sub', 'sup', 'strike', 'blockquote', 'backcolor', 'color', 'font', 'size', 'align', 'email', 'glow', 'img', 'music', 'url', 'fly', 'move', 'post', 'hide', 'sell', 'quote', 'flash', 'table', 'wmv', 'mp3', 'rm', 'iframe');
		preg_match_all('/\[([a-z]+)[^\]]*\]/i', $content, $result);
		$startTags = $result[1];
		preg_match_all('/\[\/([^\]]+)\]/i', $content, $result);
		$endTags = $result[1];
		list($startTagsLength, $startCountNum, $endCountNum) = array(count($startTags), array_count_values($startTags), array_count_values($endTags));
		if (count($endTags) == $startTagsLength) return $content;

		$startTags = array_reverse($startTags);
		for ($i = 0; $i < $startTagsLength; $i++) {
			if (in_array($startTags[$i], $openTags) || $startCountNum[$startTags[$i]] == $endCountNum[$startTags[$i]]) continue;
			$nextTag = $startTags[$i+1];
			$content = $nextTag ? preg_replace('/\[\/' . $nextTag . '\]/iU', '[/' . $startTags[$i] . '][/' . $nextTag . ']', $content) : $content . '[/' . $startTags[$i] . ']';
		}
		return $content;
	}
	
	/**
	 * 删除本bean的某个分页
	 * @param int $page
	 */
	function deletePage($page) {
		$page = (int) $page;
		$pageCount = $this->getPageCount();
		if ($pageCount < 2) {
			$this->addError('本文章没有分页，无法删除');
		}
		if ($page > $pageCount || !$page) {
			$this->addError('需要删除的分页数据有误');
		}
		$key = $page - 1;
		$contents = $this->_explodeContent();

		unset($contents[$key]);
		$this->content = implode($this->PAGECUT, $contents);
	}

	/**
	 * 设置文章跳转链接
	 * @param string $jumpUrl
	 */
	function setJumpUrl($jumpUrl) {
		if ($jumpUrl && !$this->_checkUrl($jumpUrl)) {
			$this->addError('跳转链接地址格式有误');
		}
		$this->jumpUrl = $jumpUrl;
	}

	function setSourceType($sourceType) {
		$sourceTypeConfig = $this->getSourceTypeConfig();
		if ($sourceType && !isset($sourceTypeConfig[$sourceType])) {
			$this->addError('<span class=\'s1\'>文章获取来源</span>有误');
		}
		$this->sourceType = $sourceType;
	}

	function setSourceId($soureId) {
		$soureId = (int) $soureId;
		if (!$soureId) $this->sourceType = '';
		$this->sourceId = $soureId;
	}

	/**
	 * 获取文章的分页数
	 * return int
	 */
	function getPageCount() {
		$contents = $this->_explodeContent();
		return count($contents);
	}

	/**
	 * 获取文章的分页模块
	 * @param $page
	 * @param $url
	 * return string
	 */
	function getPages($page, $url) {
		return $this->_markPages($page, $this->getPageCount(), $url);
	}

	/**
	 * 获取数据来源配置
	 */
	function getSourceTypeConfig() {
		return array('thread' => '帖子ID', 'diary' => '日志ID');
	}

	/**
	 * @param string $sourceType
	 * @return Object
	 */
	function sourceFactory($sourceType) {
		$sourceTypeConfig = $this->getSourceTypeConfig();
		if (isset($sourceTypeConfig[$sourceType])) {
			$className = $sourceType . 'sourcetype';
			return C::loadClass($className, 'sourcetype');
		}
		return C::loadClass('nonesourcetype', 'sourcetype');
	}

	function _cookContentByPage($content, $page) {
		if ($page === 'add') return $this->content . "\r\n" . $this->PAGECUT . "\r\n" . $content;
		if ($page === 'all') return $content;
		$page = (int) $page;
		if (!$page || !$this->content) return $content;
		$contents = $this->_explodeContent();

		$key = $page - 1;
		$contents[$key] = $content;
		return implode($this->PAGECUT, $contents);
	}

	function _cookOldAttachs($attachs, $oldatt_desc) {
		foreach ($attachs as $key => $value) {
			$value['descrip'] = $oldatt_desc[$value['attach_id']];
			$value['attname'] = 'update';
			$attachs[$key] = $value;
		}
		return $attachs;
	}

	function _checkUrl($jumpUrl) {
		if (strpos($jumpUrl, 'http') === 0) {return true;}
		return false;
	}

	function _markPages($page, $count, $url) {
		if ($count < 2) return '';
		$page = (int) $page;
		list($url, $mao) = explode('#', $url);
		$mao && $mao = '#' . $mao;
		$pages = "<div class=\"pages\">";
		if ($page > 1) {
			$pages .= "<a href=\"{$url}page=" . ($page - 1) . "$mao\">上一页</a>";
		}

		for ($i = $page - 3; $i <= $page - 1; $i++) {
			if ($i < 1) continue;
			$pages .= "<a href=\"{$url}page=$i$mao\">$i</a>";
		}
		if ($page) {
			$pages .= "<b>$page</b>";
		}
		if ($page < $count) {
			$this->_numPages($pages, $page, $count, $url, $mao);
		}
		$pages .= "</div>";
		return $pages;
	}

	function _numPages(&$pages, $page, $count, $url, $mao) {
		$flag = 0;
		for ($i = $page + 1; $i <= $count; $i++) {
			$pages .= "<a href=\"{$url}page=$i$mao\">$i</a>";
			$flag++;
			if ($flag == 4) break;
		}
		$pages .= "<a href=\"{$url}page=" . ($page + 1) . "$mao\">下一页</a>";
	}

	function _explodeContent() {
		return explode($this->PAGECUT, $this->content);
	}

	function getSourceUrl() {
		$source = $this->sourceFactory($this->sourceType);
		return $source->getSourceUrl($this->sourceId);
	}

	function setAuthor($author) {
		$author = $this->_filterUitil->convert($author);
		$this->author = $author;
	}

	function setFromInfo($fromInfo) {
		$fromInfo = $this->_filterUitil->convert($fromInfo);
		$this->fromInfo = $fromInfo;
	}

	function setFromUrl($fromUrl) {
		if ($fromUrl && !$this->_checkUrl($fromUrl)) {
			$this->addError('来源网址格式有误');
		}
		$this->fromUrl = $fromUrl;
	}

	function setPostDate($postDate) {
		$this->postDate = (int) $postDate;
	}

	function setModifyDate($modifyDate) {
		$this->modifyDate = (int) $modifyDate;
	}

	function setUser($user) {
		$this->user = $user;
	}

	function setUserId($userId) {
		$this->userId = $userId;
	}

	function setIfCheck($ifcheck) {
		$this->ifcheck = (int) $ifcheck;
	}

	function setIfAttach($ifAttach) {
		$this->ifAttach = (int) $ifAttach;
	}

	function setPageCount($count) {
		$this->pageCount = (int) $count;
	}

	function setArticleId($articleId) {
		$this->articleId = (int) $articleId;
	}

}
