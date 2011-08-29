<?php
!defined('P_W') && exit('Forbidden');
require_once(R_P.'lib/base/relatedata.php');
class PW_SubjectRelateData extends RelateData {
	
	function getRelateDataByKey($key) {
		global $db;
		L::loadClass('tplgetdata', 'area', false);
		$tid	= (int) $key;
		if (!$tid) return array();
		$thread	= $db->get_one("SELECT tid,fid,author,authorid,subject,type,postdate,hits,replies FROM pw_threads WHERE tid=".S::sqlEscape($tid));
		if (!$thread) return array();
		$thread['url'] 	= $this->_getSubjectUrl($thread['tid']);
		$thread['title'] 	= $thread['subject'];
		$thread['titlealt'] = $thread['subject'];
		$thread['authorurl']= 'u.php?uid='.$thread['authorid'];
		$thread['image']	= $this->_getImagesByTid($tid);
		$thread['forumname']= getForumName($thread['fid']);
		$thread['forumurl']	= getForumUrl($thread['fid']);
		$thread['descrip'] = getDescripByTid($tid);
		return $thread;
	}
	
	function getHtmlForView($default = 0) {
		$default = (int) $default ? (int) $default : '';
		$_input = '<input type="text" class="input" name="pushkey" id="pushkey" value="'.$default.'" >';
		$_input .= '<input type="button" class="btn" id="pushkeybutton" value="获取数据" >';
		$_input .= '(可输入帖子tid获取数据)';
		return array(
			'title'=>'帖子tid',
			'html'=>$_input,
		);
	}
	
	function _getSubjectUrl($tid) {
		global $db_bbsurl;
		$temp = 'read.php?tid='.$tid;
		return $db_bbsurl.'/'.urlRewrite($temp);
	}

	function _getImagesByTid($tid) {
		global $db;
		$temp	= array();
		$query	= $db->query("SELECT attachurl FROM pw_attachs WHERE tid=".S::sqlEscape($tid,false)." AND type='img' LIMIT 5");
		while($rt = $db->fetch_array($query)){
			$a_url	= geturl($rt['attachurl'],'show');
			$temp[] = is_array($a_url) ? $a_url[0] : $a_url;
		}
		return $temp;
	}
}
?>