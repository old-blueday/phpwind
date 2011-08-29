<?php
!defined('P_W') && exit('Forbidden');
define('AJAX',1);
S::gp(array('style','stopicid', 'blockid'));
$stopicid = (int) $stopicid;
$layout	= $stopic_service->getStyleConfig($style,'layout_set');
if (!$layout || $stopicid === '' || $blockid === '') {
	echo "error";
	ajax_footer();
}
$layout['bannerurl'] = $stopic_service->getStyleBanner($style);
$stopic_service->addUnit(array('stopic_id'=>$stopicid,'html_id'=>$blockid, 'title'=>'', 'data'=>array('image'=>$layout['bannerurl'])));
$layout = styleJsonEncode($layout);
echo "success\t".$layout;
ajax_footer();

function styleJsonEncode($var) {
	 switch (gettype($var)) {
		case 'boolean':
			return $var ? 'true' : 'false';
		case 'NULL':
			return 'null';
		case 'integer':
			return (int) $var;
		case 'double':
		case 'float':
			return (float) $var;
		case 'string':
			return '"'.addslashes(str_replace(array("\r\n","\n","\r","\t"),array('<br />','<br />','<br />',''),$var)).'"';
		case 'array':
			if (count($var) && (array_keys($var) !== range(0, sizeof($var) - 1))) {
				$properties = array();
				foreach ($var as $name=>$value) {
					$properties[] = styleJsonEncode(strval($name)) . ':' . styleJsonEncode($value);
				}
				return '{' . join(',', $properties) . '}';
			}
			$elements = array_map('pwJsonEncode', $var);
			return '[' . join(',', $elements) . ']';
	 }
	 return false;
}
?>