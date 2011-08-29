<?php
!defined('M_P') && exit('Forbidden');
require_once(R_P.'require/forum.php');
//* include_once pwCache::getPath(D_P.'data/bbscache/forum_cache.php');
pwCache::getData(D_P.'data/bbscache/forum_cache.php');

S::gp(array('cateid'),'GP',2);
if (!$cateid && $secdomain = array_search($pwServer['HTTP_HOST'], $db_modedomain)) {
	if (substr($secdomain,0,5) == 'cate_') {
		$cateid = substr($secdomain, 5);
		$db_bbsurl = $_mainUrl;
	}
}
$cateid = (int)$cateid;
!$cateid && Showmsg('no_cateid');
$fid = $cateid;
$SCR = 'cate';
$db_mode = 'area';

/*$metakeyword = strip_tags($forum[$cateid]['name']);
$subject = $metakeyword.' - ';
$db_metakeyword = $metakeyword;*/

#SEO

require_once(M_P.'require/header.php');

$forum[$fid]['type'] != 'category' && Showmsg('not_is_category');

$forumdb = array();
foreach ($forum as $key=>$value) {
	if ($value['fup'] == $fid && $value['f_type'] !='hidden') {
		$value['name'] = strip_tags($value['name']);
		$forumdb[$key] = $value;
	}
}

require_once PrintEot('cate');footer();
?>