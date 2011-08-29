<?php
!defined('P_W') && exit('Forbidden');

S::gp(array('param'));

require_once(R_P . 'u/lib/space.class.php');
$newSpace = new PwSpace($winduid);
$newSpace->initSet();
$space = $newSpace->getInfo();

$layout = array();
if ($param && is_array($param)) {
	foreach ($param as $key => $value) {
		foreach ($value as $k => $v) {
			if (isset($space['modelset'][$v])) {
				$layout[$key][] = $v;
			}
		}
	}
}
$newSpace->updateInfo(array('layout' => serialize($layout)));

Showmsg('布局已保存');

?>