<?php
!defined('P_W') && exit('Forbidden');

if (GetCookie('hideid') == '1') {
	Cookie('hideid', '', 0);
	echo "0";
	ajax_footer();
} else {
	Cookie('hideid', '1');
	echo "1";
	ajax_footer();
}
