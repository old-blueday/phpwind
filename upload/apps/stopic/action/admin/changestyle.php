<?php
!defined('P_W') && exit('Forbidden');
define('AJAX',1);
InitGP(array('style'));
$layout	= $stopic_service->getStyleConfig($style,'layout_set');
if (!$layout) {
	echo "error";
	ajax_footer();
}
$layout['bannerurl'] = $stopic_service->getStyleBanner($style);

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