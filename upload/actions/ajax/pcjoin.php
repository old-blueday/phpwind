<?php
!defined('P_W') && exit('Forbidden');

S::gp(array(
	'tid',
	'thelast',
	'authorid',
	'pcid'
), GP, 2);

if ($thelast != 1) {
	//$sign = $db->get_value("SELECT sign FROM pw_postcate WHERE pcid=" . S::sqlEscape($pcid));
	

	$pcvaluetable = GetPcatetable($pcid);
	$fieldvalue = $db->get_one("SELECT objecter,limitnum,payway,deposit,price FROM $pcvaluetable WHERE tid=" . S::sqlEscape($tid));
	$membernum = $db->get_value("SELECT SUM(nums) FROM pw_pcmember WHERE tid=" . S::sqlEscape($tid));
	$payway = $fieldvalue['payway'];
	
	if (empty($_POST['step'])) {
		$authorid == $winduid && Showmsg('pcjoin_ownnotjoin');
		
		$isU = ($fieldvalue['objecter'] == 2 && isFriend($authorid, $winduid) || $fieldvalue['objecter'] == 1) ? 1 : 0;
		$fieldvalue['limitnum'] && $morenum = $fieldvalue['limitnum'] - $membernum;
		
		require_once PrintEot('ajax');
		ajax_footer();
	} elseif ($_POST['step'] == '1') {
		PostCheck();
		S::gp(array(
			'nums',
			'phone',
			'mobile',
			'address',
			'zip',
			'message',
			'extra',
			'name'
		));
		
		if (!$mobile || !$name) {
			Showmsg('pcjoin_mobile_error');
		}
		if ($fieldvalue['limitnum'] && $fieldvalue['limitnum'] - $membernum < $nums) {
			if ($pcid == 1) {
				Showmsg('pcjoin_pcid_more');
			} elseif ($pcid == 2) {
				Showmsg('pcjoin_more');
			}
		}
		$nums = (int) $nums;
		if ($nums <= 0) {
			if ($pcid == 1) {
				Showmsg('pcjoin_pcid_nums');
			} elseif ($pcid == 2) {
				Showmsg('pcjoin_nums');
			}
		}
		
		$deposit = !ceil($fieldvalue['deposit']) ? $fieldvalue['price'] : $fieldvalue['deposit'];
		$totalcash = $deposit * $nums;
		
		$sqlarray = array(
			'tid' => $tid,
			'uid' => $winduid,
			'pcid' => $pcid,
			'username' => $windid,
			'nums' => $nums,
			'totalcash' => $totalcash,
			'name' => $name,
			'zip' => $zip,
			'message' => $message,
			'phone' => $phone,
			'mobile' => $mobile,
			'address' => $address,
			'extra' => $extra,
			'jointime' => $timestamp
		);
		$db->update("INSERT INTO pw_pcmember SET " . S::sqlSingle($sqlarray));
		
		$pcmid = $db->insert_id();
		$nextto = 'pcjoin';
		
		$threadInfo = $db->get_one("SELECT * FROM pw_threads WHERE tid=".S::sqlEscape($tid));
		$createTime = get_date($threadInfo['postdate']);
		//* require_once pwCache::getPath(D_P.'data/bbscache/forum_cache.php');
		pwCache::getData(D_P.'data/bbscache/forum_cache.php');
		M::sendNotice(
			array($threadInfo['author']),
			array(
				'title' => getLangInfo('writemsg', 'activity_pcjoin_new_title', array(
						'username' => $windid
					)
				),
				'content' => getLangInfo('writemsg', 'activity_pcjoin_new_content', array(
						'username' => $windid,
						'uid' => $winduid,
						'tid' => $tid,
						'fid' => $threadInfo['fid'],
						'createtime' =>$createTime,
						'fname' => $forum[$threadInfo['fid']]['name'],
						'subject' => $threadInfo['subject']
					)
				),
			),'notice_postcate','notice_postcate'
			);
		
		Showmsg('pcjoin_nextstep');
	}
} elseif ($thelast == 1) {
	S::gp(array(
		'deposit',
		'nums',
		'totalcash',
		'pcmid',
		'payway'
	));
	$alipayurl = "trade.php?action=pcalipay&tid=$tid&pcmid=$pcmid&pcid=$pcid";
	
	require_once PrintEot('ajax');
	ajax_footer();
}


function isFriend($uid, $friend) {
	global $db;
	if ($db->get_value("SELECT uid FROM pw_friends WHERE uid=" . S::sqlEscape($uid) . ' AND friendid=' . S::sqlEscape($friend) . " AND status='0'")) {
		return true;
	}
	return false;
}
