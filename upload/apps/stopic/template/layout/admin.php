<?php
!defined('P_W') && exit('Forbidden');

include_once PrintEot('left');

$gen_class = in_array($job, array('make')) ? 'class="current"' : '';
$man_class = in_array($job, array('cgman','bgman','stman')) ? 'class="current"' : '';

print <<<EOT
-->
<div class="nav3">
	<ul class="cc">
		<li $man_class><a href="$stopic_admin_url&job=stman">专题管理</a></li>
		<li $gen_class><a href="$stopic_admin_url&job=make">制作专题</a></li>
	</ul>
</div>
EOT;

include stopic_load_view($job);

include_once PrintEot('adminbottom');
?>