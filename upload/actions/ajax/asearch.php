<?php
!defined('P_W') && exit('Forbidden');

S::gp(array(
	'fid',
	'modelid',
	'pcid',
	'actmid',
	'allactmid',
));

if ($modelid) {
	L::loadClass('posttopic', 'forum', false);
	$postTopic = new postTopic($pwpost);
	$query = $db->query("SELECT fieldid,name as newname,type,rules,vieworder,textsize FROM pw_topicfield WHERE modelid = " . S::sqlEscape($modelid) . " AND ifable='1' AND ifasearch='1' ORDER BY vieworder ASC,fieldid ASC");
	while ($rt = $db->fetch_array($query)) {
		list($rt['name1'], $rt['name2']) = explode('{#}', $rt['newname']);
		$rt['searchhtml'] = $postTopic->getASearchHtml($rt['type'], $rt['fieldid'], $rt['textsize'], $rt['rules']);
		$asearchdb[$rt['fieldid']] = $rt;
	}
} elseif ($pcid) {
	
	L::loadClass('postcate', 'forum', false);
	$postTopic = new postCate($pwpost);
	$query = $db->query("SELECT fieldid,name as newname,type,rules,vieworder,textsize FROM pw_pcfield WHERE pcid = " . S::sqlEscape($pcid) . " AND ifable='1' AND ifasearch='1' ORDER BY vieworder ASC,fieldid ASC");
	while ($rt = $db->fetch_array($query)) {
		list($rt['name1'], $rt['name2']) = explode('{#}', $rt['newname']);
		$rt['searchhtml'] = $postTopic->getASearchHtml($rt['type'], $rt['fieldid'], $rt['textsize'], $rt['rules']);
		$asearchdb[$rt['fieldid']] = $rt;
	}
} elseif ($actmid || $allactmid) {

	L::loadClass('ActivityForBbs', 'activity', false);
	$postActForBbs = new PW_ActivityForBbs($data);
	$fieldService = L::loadClass('ActivityField', 'activity');
	if ($actmid) {
		$advancedSearchFieldDb = $fieldService->getEnabledAndAdvancedSearchableFieldsByModelId($actmid);
	} else {
		$advancedSearchFieldDb = $fieldService->getDefaultSearchFields();
	}
	foreach ($advancedSearchFieldDb as $rt) {
		if($rt['ifasearch'] == 1) {
			$rt['searchhtml'] = $postActForBbs->getASearchHtml($rt['type'],$rt['fieldname'],$rt['textwidth'],$rt['rules']);
			$asearchdb[$rt['fieldname']] = $rt;
		}
	}
}

if (empty($asearchdb)) {
	showmsg('topic_search_forum');
}

require_once PrintEot('ajax');
ajax_footer();
