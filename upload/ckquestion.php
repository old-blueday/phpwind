<?php
/**
 * 
 * @author pw team, Nov 13, 2010
 * @copyright 2003-2010 phpwind.net. All rights reserved.
 * @version 
 * @package default
 */
 
 
define('CK',1);
require_once('global.php');
S::GP('q');
$qkey = intval($q);
if (isset($db_question[$qkey])) {
	$question = $qkey < 0 ? getMachineQuestion_1() : $db_question[$qkey];
	$array = array();
	strtoupper($db_charset) == 'GBK' && $question = pwConvert($question,'UTF-8','GBK');
	$len = strlen($question);
	for($i=0,$j=0;$i<$len;){
		++$i;
		$ord = ord($question[$j]);
		if($ord > 127){
			if($ord >= 192 && $ord <= 223) ++$i;
			elseif($ord >= 224 && $ord <= 239) $i = $i + 2;
			elseif($ord >= 240 && $ord <= 247) $i = $i + 3;
		}
		$array[] = substr($question,$j,$i-$j);
		$j = $i;
	}
	$strSize = count($array);
	if($strSize){
		L::loadClass('graphic', 'utility/captcha',false);
		$graphic = new PW_Graphic($strSize * 14,24);
		$len > $strSize && $graphic->lang = 'ch';
		$graphic->fontRandomPosition = false;
		$graphic->fontRandomFamily = true;
		$graphic->fontRandomColor = false;
		$graphic->setCodes($array);
		$graphic->display();
	}
}