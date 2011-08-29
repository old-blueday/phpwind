<?php
! function_exists ( 'readover' ) && exit ( 'Forbidden' );
/**
 * 配置说明
 * 二维数组 array('调用位置1'=> array(
 * '调用项1'=>array('文件路径=>'1','调用类名称'=>'classname1'),
 * '调用项2'=>array('文件路径=>'2','调用类名称'=>'classname2'),
 * '调用位置2'=> array(
 * '调用项3'=>array('文件路径=>'3','调用类名称'=>'classname3'),
 * '调用项4'=>array('文件路径=>'4','调用类名称'=>'classname4'),
 * );
 * 1,调用位置1:表示展示的位置，如right_search 将展示在搜索结果集的右边,一个位置可调用多个扩展类结果
 * 2,调用项1:调用的名称标识符
 * 3,文件路径:表示加载扩展搜索类的文件路径
 * 4,调用类名称:PW_classname1Searcher 注意只需要提供classname1 如PW_dianpuSearcher的classname为dianpu
 */
$configs = array ();

if ($db_modes ['dianpu'] ['ifopen']) {
	$configs ['right_search'] = array ('dianpu_dianpu' => array ('path' => R_P . 'mode/dianpu/lib/searcher/dianpusearcher.class.php', 'classname' => 'dianpu' ) );
}
