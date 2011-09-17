<?php
! defined ( 'CLOUDWIND' ) && exit ( 'Forbidden' );
require_once CLOUDWIND . '/client/search/general/service/general.abstract.class.php';
class CloudWind_General_Format extends CloudWind_General_Abstract {
	
	function getThreadFormat($list, $command = YUN_COMMAND_ADD) {
		$out = '';
		$out .= 'CMD=' . $command . YUN_ROW_SEPARATOR;
		$out .= 'tid=' . $list ['tid'] . YUN_ROW_SEPARATOR;
		$out .= 'pid=' . $list ['pid'] . YUN_ROW_SEPARATOR;
		$out .= 'subject=' . $list ['subject'] . YUN_ROW_SEPARATOR;
		$out .= 'content=' . $list ['content'] . YUN_ROW_SEPARATOR;
		$out .= 'fid=' . $list ['fid'] . YUN_ROW_SEPARATOR;
		$out .= 'forumname=' . $list ['forumname'] . YUN_ROW_SEPARATOR;
		$out .= 'forumlink=' . $list ['forumlink'] . YUN_ROW_SEPARATOR;
		$out .= 'ifcheck=' . $list ['ifcheck'] . YUN_ROW_SEPARATOR;
		$out .= 'authorid=' . $list ['authorid'] . YUN_ROW_SEPARATOR;
		$out .= 'author=' . $list ['author'] . YUN_ROW_SEPARATOR;
		$out .= 'lastpost=' . $list ['lastpost'] . YUN_ROW_SEPARATOR;
		$out .= 'postdate=' . $list ['postdate'] . YUN_ROW_SEPARATOR;
		$out .= 'digest=' . $list ['digest'] . YUN_ROW_SEPARATOR;
		$out .= 'hits=' . $list ['hits'] . YUN_ROW_SEPARATOR;
		$out .= 'replies=' . $list ['replies'] . YUN_ROW_SEPARATOR;
		$out .= 'link=' . $list ['link'] . YUN_ROW_SEPARATOR;
		$out .= 'ifupload=' . $list ['ifupload'] . YUN_ROW_SEPARATOR;
		$out .= 'topped=' . $list ['topped'] . YUN_ROW_SEPARATOR;
		$out .= 'special=' . $list ['special'] . YUN_ROW_SEPARATOR;
		$out .= 'deleted=0' . YUN_ROW_SEPARATOR;
		$out .= YUN_SEGMENT_SEPARATOR;
		return $out;
	}
	
	function getPostFormat($list, $command = YUN_COMMAND_ADD) {
		$out = '';
		$out .= 'CMD=' . $command . YUN_ROW_SEPARATOR;
		$out .= 'tid=' . $list ['tid'] . YUN_ROW_SEPARATOR;
		$out .= 'pid=' . $list ['pid'] . YUN_ROW_SEPARATOR;
		$out .= 'subject=' . $list ['subject'] . YUN_ROW_SEPARATOR;
		$out .= 'content=' . $list ['content'] . YUN_ROW_SEPARATOR;
		$out .= 'fid=' . $list ['fid'] . YUN_ROW_SEPARATOR;
		$out .= 'forumname=' . $list ['forumname'] . YUN_ROW_SEPARATOR;
		$out .= 'forumlink=' . $list ['forumlink'] . YUN_ROW_SEPARATOR;
		$out .= 'ifcheck=' . $list ['ifcheck'] . YUN_ROW_SEPARATOR;
		$out .= 'authorid=' . $list ['authorid'] . YUN_ROW_SEPARATOR;
		$out .= 'author=' . $list ['author'] . YUN_ROW_SEPARATOR;
		$out .= 'lastpost=0' . YUN_ROW_SEPARATOR;
		$out .= 'postdate=' . $list ['postdate'] . YUN_ROW_SEPARATOR;
		$out .= 'digest=0' . YUN_ROW_SEPARATOR;
		$out .= 'hits=0' . YUN_ROW_SEPARATOR;
		$out .= 'replies=0' . YUN_ROW_SEPARATOR;
		$out .= 'link=' . $list ['link'] . YUN_ROW_SEPARATOR;
		$out .= 'deleted=0' . YUN_ROW_SEPARATOR;
		$out .= YUN_SEGMENT_SEPARATOR;
		return $out;
	}
	
	function getWeiboFormat($list, $command = YUN_COMMAND_ADD) {
		$out = '';
		$out .= 'CMD=' . $command . YUN_ROW_SEPARATOR;
		$out .= 'mid=' . $list ['mid'] . YUN_ROW_SEPARATOR;
		$out .= 'uid=' . $list ['uid'] . YUN_ROW_SEPARATOR;
		$out .= 'replies=' . $list ['replies'] . YUN_ROW_SEPARATOR;
		$out .= 'postdate=' . $list ['postdate'] . YUN_ROW_SEPARATOR;
		$out .= 'contenttype=' . $list ['contenttype'] . YUN_ROW_SEPARATOR;
		$out .= 'content=' . $list ['content'] . YUN_ROW_SEPARATOR;
		$out .= 'deleted=0' . YUN_ROW_SEPARATOR;
		$out .= YUN_SEGMENT_SEPARATOR;
		return $out;
	}
	
	function getMemberFormat($list, $command = YUN_COMMAND_ADD) {
		$out = '';
		$out .= 'CMD=' . $command . YUN_ROW_SEPARATOR;
		$out .= 'uid=' . $list ['uid'] . YUN_ROW_SEPARATOR;
		$out .= 'username=' . $list ['username'] . YUN_ROW_SEPARATOR;
		$out .= 'link=' . $list ['link'] . YUN_ROW_SEPARATOR;
		$out .= 'regdate=' . $list ['regdate'] . YUN_ROW_SEPARATOR;
		$out .= 'deleted=0' . YUN_ROW_SEPARATOR;
		$out .= YUN_SEGMENT_SEPARATOR;
		return $out;
	}
	
	function getForumFormat($list, $command = YUN_COMMAND_ADD) {
		$out = '';
		$out .= 'CMD=' . $command . YUN_ROW_SEPARATOR;
		$out .= 'fid=' . $list ['fid'] . YUN_ROW_SEPARATOR;
		$out .= 'name=' . $list ['name'] . YUN_ROW_SEPARATOR;
		$out .= 'link=' . $list ['link'] . YUN_ROW_SEPARATOR;
		$out .= 'deleted=0' . YUN_ROW_SEPARATOR;
		$out .= YUN_SEGMENT_SEPARATOR;
		return $out;
	}
	
	function getDiaryFormat($list, $command = YUN_COMMAND_ADD) {
		$out = '';
		$out .= 'CMD=' . $command . YUN_ROW_SEPARATOR;
		$out .= 'did=' . $list ['did'] . YUN_ROW_SEPARATOR;
		$out .= 'uid=' . $list ['uid'] . YUN_ROW_SEPARATOR;
		$out .= 'username=' . $list ['username'] . YUN_ROW_SEPARATOR;
		$out .= 'subject=' . $list ['subject'] . YUN_ROW_SEPARATOR;
		$out .= 'content=' . $list ['content'] . YUN_ROW_SEPARATOR;
		$out .= 'postdate=' . $list ['postdate'] . YUN_ROW_SEPARATOR;
		$out .= 'link=' . $list ['link'] . YUN_ROW_SEPARATOR;
		$out .= 'deleted=0' . YUN_ROW_SEPARATOR;
		$out .= YUN_SEGMENT_SEPARATOR;
		return $out;
	}
	
	function getColonyFormat($list, $command = YUN_COMMAND_ADD) {
		$out = '';
		$out .= 'CMD=' . $command . YUN_ROW_SEPARATOR;
		$out .= 'id=' . $list ['id'] . YUN_ROW_SEPARATOR;
		$out .= 'classid=' . $list ['classid'] . YUN_ROW_SEPARATOR;
		$out .= 'cname=' . $list ['cname'] . YUN_ROW_SEPARATOR;
		$out .= 'link=' . $list ['link'] . YUN_ROW_SEPARATOR;
		$out .= 'deleted=0' . YUN_ROW_SEPARATOR;
		$out .= YUN_SEGMENT_SEPARATOR;
		return $out;
	}
	
	function getAttachFormat($list, $command = YUN_COMMAND_ADD) {
		$out = '';
		$out .= 'CMD=' . $command . YUN_ROW_SEPARATOR;
		$out .= 'tid=' . $list ['tid'] . YUN_ROW_SEPARATOR;
		$out .= 'fid=' . $list ['fid'] . YUN_ROW_SEPARATOR;
		$out .= 'pid=' . $list ['pid'] . YUN_ROW_SEPARATOR;
		$out .= 'did=' . $list ['did'] . YUN_ROW_SEPARATOR;
		$out .= 'uid=' . $list ['uid'] . YUN_ROW_SEPARATOR;
		$out .= 'mid=' . $list ['mid'] . YUN_ROW_SEPARATOR;
		$out .= 'size=' . $list ['size'] . YUN_ROW_SEPARATOR;
		$out .= 'hits=' . $list ['hits'] . YUN_ROW_SEPARATOR;
		$out .= 'special=' . $list ['special'] . YUN_ROW_SEPARATOR;
		$out .= 'postdate=' . $list ['postdate'] . YUN_ROW_SEPARATOR;
		$out .= 'name=' . $list ['name'] . YUN_ROW_SEPARATOR;
		$out .= 'descrip=' . $list ['descrip'] . YUN_ROW_SEPARATOR;
		$out .= 'ctype=' . $list ['ctype'] . YUN_ROW_SEPARATOR;
		$out .= 'type=' . $list ['type'] . YUN_ROW_SEPARATOR;
		$out .= 'deleted=0' . YUN_ROW_SEPARATOR;
		$out .= YUN_SEGMENT_SEPARATOR;
		return $out;
	}
	
	function getDeleteFormat($key, $value) {
		$out = '';
		$out .= 'CMD=delete' . YUN_ROW_SEPARATOR;
		$out .= $key . '=' . $value . YUN_ROW_SEPARATOR;
		$out .= YUN_SEGMENT_SEPARATOR;
		return $out;
	}

}