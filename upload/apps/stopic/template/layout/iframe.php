<?php
!defined('P_W') && exit('Forbidden');

include_once PrintEot('left');

print <<<EOT
-->

<style type="text/css">
.pd5{padding:0 0 5px;}
.p10{padding:10px;}
.zt-list,.zt-list2{padding-bottom:10px;}
.zt-list li{float:left;width:60px;margin-right:20px; text-align:center;}

.zt-list3 li{float:left;width:120px;height:120px;margin:0 10px 10px 0;display:inline;overflow:hidden;border:1px solid #cccccc;padding:4px;}
.zt-list3 li a:hover img{border:3px solid #ff7e00;width:114px;height:114px;}
.zt-list3 .current{border:1px solid #ff7e00;background:#ffd3a8;}
.zt-list3 .current a:hover img{border:0;width:120px;height:120px;}

.choose,.preview{border:1px solid;border-color: #b8b8b8 #dedede #dedede #b8b8b8;background:#ffffff;width:960px;}
.choose .widget-top{float:left;white-space:nowrap;padding:0 5px;line-height:24px;height:24px;border:1px solid #ffcba9;background:#fff0e6;margin:5px 10px 0 0;color:#555555;}
.choose .widget-top:hover{border:1px solid #ff6600;background:#ffdec8;color:#000;}

.preview{margin:10px 0;}

.black{color:#555555;}
.fn,.fn a{font-weight:normal}

.zt-list2 li{float:left;width:100px;margin:0 20px 20px 0;}

.nopic {height:60px;background:#ddd;margin-bottom:5px;}
</style>

EOT;

include stopic_load_view($job);

include_once PrintEot('adminbottom');
?>