<?php
!defined('PW_UPLOAD') && exit('Forbidden');

$smileService = L::loadClass('smile', 'smile'); /* @var $smileService PW_Smile */

$view = 1;
$smiles = array(
    '1.gif' => array('path' => '1.gif', 'name' => '微笑', 'order' => $view++),
    '3.gif' => array('path' => '3.gif', 'name' => '乖', 'order' => $view++),
    '4.gif' => array('path' => '4.gif', 'name' => '花痴', 'order' => $view++),
    '5.gif' => array('path' => '5.gif', 'name' => '哈哈', 'order' => $view++),
    '6.gif' => array('path' => '6.gif', 'name' => '呼叫', 'order' => $view++),
    '7.gif' => array('path' => '7.gif', 'name' => '天使', 'order' => $view++),
    '8.gif' => array('path' => '8.gif', 'name' => '摇头', 'order' => $view++),
    '9.gif' => array('path' => '9.gif', 'name' => '不会吧', 'order' => $view++),
    '10.gif' => array('path' => '10.gif', 'name' => '忧伤', 'order' => $view++),
    '11.gif' => array('path' => '11.gif', 'name' => '悲泣', 'order' => $view++),
    '12.gif' => array('path' => '12.gif', 'name' => 'I服了U', 'order' => $view++),
    '13.gif' => array('path' => '13.gif', 'name' => '背', 'order' => $view++),
    '14.gif' => array('path' => '14.gif', 'name' => '鄙视', 'order' => $view++),
    '15.gif' => array('path' => '15.gif', 'name' => '大怒', 'order' => $view++),
    '16.gif' => array('path' => '16.gif', 'name' => '好的', 'order' => $view++),
    '17.gif' => array('path' => '17.gif', 'name' => '鼓掌', 'order' => $view++),
    '18.gif' => array('path' => '18.gif', 'name' => '握手', 'order' => $view++),
    '19.gif' => array('path' => '19.gif', 'name' => '玫瑰', 'order' => $view++),
    '20.gif' => array('path' => '20.gif', 'name' => '心碎', 'order' => $view++),
    '21.gif' => array('path' => '21.gif', 'name' => '委屈', 'order' => $view++),
    '22.gif' => array('path' => '22.gif', 'name' => '老大', 'order' => $view++),
    '23.gif' => array('path' => '23.gif', 'name' => '炸弹', 'order' => $view++),
);
$smileService->adds(0, $smiles);
