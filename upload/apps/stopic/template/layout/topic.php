<!--<?php
!defined('P_W') && exit('Forbidden');

print <<<EOT
--><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" id="html">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=$db_charset" />
<title>$webPageTitle - Powered by phpwind</title>
<meta name="generator" content="phpwind $wind_version" />
<meta name="description" content="$metaDescription" />
<meta name="keywords" content="$metaKeywords" />
<!--meta http-equiv="x-ua-compatible" content="ie=7" /-->
<link rel='archives' title='$db_bbsname' href='$db_bbsurl/simple/' />
<!--
EOT;
if($fid){print <<<EOT
-->
<link rel="alternate" type="application/rss+xml" title="RSS" href="$db_bbsurl/rss.php?fid=$fid" />
<!--
EOT;
}print <<<EOT
-->
<base id="headbase" href="$db_bbsurl/" />
<!--
EOT;
if(SCR == 'read' && $link_ref_canonical){print <<<EOT
-->
<link rel="canonical" href="$link_ref_canonical" />
<!--
EOT;
}print <<<EOT
-->
<link rel="stylesheet" type="text/css" href="$imgpath/wind-reset.css" />
<!--
EOT;
@include S::escapePath($css_path);
if($pwModeCss){print <<<EOT
-->
<style>
#html{background:#fff;}
body{background:#fff;}
</style>
<link rel="stylesheet" type="text/css" href="$pwModeCss" />
<!--
EOT;
}print <<<EOT
-->
<script type="text/javascript" src="js/core/core.js"></script>
<script type="text/javascript" src="js/pw_ajax.js"></script>
<script type="text/javascript">
var imgpath = '$imgpath';
var verifyhash = '$verifyhash';
var modeimg = '$pwModeImg';
var modeBase = '$baseUrl';
var winduid = '$winduid';
var windid	= '$windid';
var groupid	= '$groupid';
var basename = '$basename';
var temp_basename = '$temp_basename';
</script>
</head>
<body>

<div class="main-wrap">
	<div id="main">
EOT;

include stopic_load_topic_view($special);

print <<<EOT
	</div>
</div>
<script type="text/javascript" src="js/global.js"></script>

</body>
</html>
EOT;
$output = ob_get_contents();
$output = str_replace(array('<!--<!--<!---->','<!--<!---->','<!---->-->','<!---->'),'',$output);
echo ObContents($output);
unset($output);
?>