<?php
!defined('P_W') && exit('Forbidden');
define('M_P',1);
/**
 * cms发送到新鲜事SERVICE
 * 
 * @package weibo_Cms
 * @author lmq
 */
class weibo_Cms extends baseWeibo {
	var $_cid;
	var $_url;
	function weibo_Cms() {
		$this->_url = $GLOBALS['db_bbsurl']."/index.php?m=cms&q=view";
	}
	function init($id) {
		$this->_cid = $id;
		require_once(R_P. 'mode/cms/require/core.php');
		$articleDB = C::loadDB('article');
		$article 	= $articleDB->get($this->_cid);
		empty($article) && Showmsg('data_error');
		$this->_url = $this->_url . "&id=".$this->_cid;
		$title = $content = '我发现了一篇文章'.sprintf("[url=%s] %s [/url]", urlRewrite($this->_url), $article['subject']).'，特别推荐。';
		$descrip = $article['descrip'];
		$mailSubject =  getLangInfo('app','cms_recommend');
		$mailContent = getLangInfo('app','ajax_sendweibo_cmsinfo',array('title'	=> $title,'descrip'=>$descrip));
		$this->_content = $content;
		$this->_mailSubject = $mailSubject;
		$this->_mailContent = $mailContent;
	}
}
?>