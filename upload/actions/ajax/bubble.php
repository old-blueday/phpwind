<?php
!defined('P_W') && exit('Forbidden');

S::gp(array('uid', 'sign'));

$uid = intval($uid);
if ($uid < 1 || !$sign) Showmsg('error');

$cache = perf::gatherCache('pw_members');
$userData = $cache->getMemberDataByUserId($uid);
if (!$userData) Showmsg('error');
$bubbleInfo =  $userData['bubble'] ? unserialize($userData['bubble']) : array();
$bubbleInfo[$sign] = 1;
$userService = L::loadClass('userservice', 'user');
$userService->update($uid, array(), array('bubble' => serialize($bubbleInfo)));

echo 'success';ajax_footer();