<?php
!defined('PW_UPLOAD') && exit('Forbidden');
$invokes=array(
	'0' => array(
		'id' => '1',
		'name' => '首页焦点摘要',
		'tplid' => '2',
		'tagcode' => '<list action="subject" num="3" title="帖子及摘要" />
<loop>
<h4><a href="{url}" target="_blank">{title,25}</a></h4>
<p>{descrip,40}</p>
<ul class="cc area-list-tree">
{tagrelate}
</ul>
</loop>',
		'parsecode' => '
EOT;
$pwresult = pwTplGetData(\'首页焦点摘要\',\'帖子及摘要\');
print <<<EOT


EOT;
foreach($pwresult as $key=>$val){print <<<EOT

<h4><a href="$val[url]" target="_blank">$val[title]</a></h4>
<p>$val[descrip]</p>
<ul class="cc area-list-tree">

EOT;
foreach($val[tagrelate] as $key_1=>$val_1){print <<<EOT
<li><a href="$val_1[url]" target="_blank">$val_1[title]</a></li>
EOT;
}print <<<EOT

</ul>

EOT;
}print <<<EOT
',
		'ifloop' => '0',
		'loops' => '',
		'descrip' => '',
	),
	'1' => array(
		'id' => '2',
		'name' => '首页焦点',
		'tplid' => '4',
		'tagcode' => '<list action="subject" num="8" title="帖子列表" />
<ul>
<loop>
<li><a href="{url}" target="_blank">{title,32}</a></li>
</loop>
</ul>',
		'parsecode' => '
EOT;
$pwresult = pwTplGetData(\'首页焦点\',\'帖子列表\');
print <<<EOT
<ul>
EOT;
foreach($pwresult as $key=>$val){print <<<EOT
<li><a href="$val[url]" target="_blank">$val[title]</a></li>
EOT;
}print <<<EOT
</ul>',
		'ifloop' => '0',
		'loops' => '',
		'descrip' => '',
	),
	'2' => array(
		'id' => '26',
		'name' => '首页社区热门',
		'tplid' => '7',
		'tagcode' => '<list action="image" num="6" title="图片模块" />
<loop>
<a href="{url}" target="_blank"><img src="{image,100,100}" class="fl" /><p>{title,8}</p></a>
</loop>',
		'parsecode' => '
EOT;
$pwresult = pwTplGetData(\'首页社区热门\',\'图片模块\');
foreach($pwresult as $key=>$val){print <<<EOT
<a href="$val[url]" target="_blank"><img src="$val[image]" class="fl" /><p>$val[title]</p></a>
EOT;
}print <<<EOT
',
		'ifloop' => '0',
		'loops' => '',
		'descrip' => '',
	),
	'3' => array(
		'id' => '10',
		'name' => '首页循环版块',
		'tplid' => '6',
		'tagcode' => '<list action="image" num="4" title="图片模块" />
<table width="100%">
<tr>
<th>
<loop>
<a href="{url}" target="_blank"><img src="{image,100,100}" class="fl" /><p>{title,8}</p></a>
</loop>
</th>
<td>
<list action="subject" num="12" title="帖子排行模块" />
<ul>
<loop>
<li><a href="{url}" title="{title}" target="_blank">{title,30}</a></li>
</loop>
</ul>
</td>
</tr></table>',
		'parsecode' => '
EOT;
$pwresult = pwTplGetData(\'首页循环版块\',\'图片模块\',$loopid);
print <<<EOT
<table width="100%">
<tr>
<th>
EOT;
foreach($pwresult as $key=>$val){print <<<EOT
<a href="$val[url]" target="_blank"><img src="$val[image]" class="fl" /><p>$val[title]</p></a>
EOT;
}print <<<EOT
</th>
<td>
EOT;
$pwresult = pwTplGetData(\'首页循环版块\',\'帖子排行模块\',$loopid);
print <<<EOT
<ul>
EOT;
foreach($pwresult as $key=>$val){print <<<EOT
<li><a href="$val[url]" title="$val[title]" target="_blank">$val[title]</a></li>
EOT;
}print <<<EOT
</ul>
</td>
</tr></table>',
		'ifloop' => '1',
		'loops' => '',
		'descrip' => '',
	),
	'4' => array(
		'id' => '5',
		'name' => '首页最新图片',
		'tplid' => '7',
		'tagcode' => '<list action="image" num="6" title="图片模块" />
<loop>
<a href="{url}" target="_blank"><img src="{image,100,100}" class="fl" /><p>{title,8}</p></a>
</loop>',
		'parsecode' => '
EOT;
$pwresult = pwTplGetData(\'首页最新图片\',\'图片模块\');
foreach($pwresult as $key=>$val){print <<<EOT
<a href="$val[url]" target="_blank"><img src="$val[image]" class="fl" /><p>$val[title]</p></a>
EOT;
}print <<<EOT
',
		'ifloop' => '0',
		'loops' => '',
		'descrip' => '',
	),
	'5' => array(
		'id' => '6',
		'name' => '首页某版块调用1',
		'tplid' => '4',
		'tagcode' => '<list action="subject" num="8" title="帖子列表" />
<ul>
<loop>
<li><a href="{url}" target="_blank">{title,32}</a></li>
</loop>
</ul>',
		'parsecode' => '
EOT;
$pwresult = pwTplGetData(\'首页某版块调用1\',\'帖子列表\');
print <<<EOT
<ul>
EOT;
foreach($pwresult as $key=>$val){print <<<EOT
<li><a href="$val[url]" target="_blank">$val[title]</a></li>
EOT;
}print <<<EOT
</ul>',
		'ifloop' => '0',
		'loops' => '',
		'descrip' => '',
	),
	'6' => array(
		'id' => '7',
		'name' => '首页某版块调用2',
		'tplid' => '4',
		'tagcode' => '<list action="subject" num="8" title="帖子列表" />
<ul>
<loop>
<li><a href="{url}" target="_blank">{title,32}</a></li>
</loop>
</ul>',
		'parsecode' => '
EOT;
$pwresult = pwTplGetData(\'首页某版块调用2\',\'帖子列表\');
print <<<EOT
<ul>
EOT;
foreach($pwresult as $key=>$val){print <<<EOT
<li><a href="$val[url]" target="_blank">$val[title]</a></li>
EOT;
}print <<<EOT
</ul>',
		'ifloop' => '0',
		'loops' => '',
		'descrip' => '',
	),
	'7' => array(
		'id' => '8',
		'name' => '版块排行',
		'tplid' => '8',
		'tagcode' => '<list action="forum" num="12" title="版块模块" />
<ul>
<loop>
<li><span class="fr">{value}</span><a href="{url}" target="_blank">{title}</a></li>
</loop>
</ul>',
		'parsecode' => '
EOT;
$pwresult = pwTplGetData(\'版块排行\',\'版块模块\');
print <<<EOT
<ul>
EOT;
foreach($pwresult as $key=>$val){print <<<EOT
<li><span class="fr">$val[value]</span><a href="$val[url]" target="_blank">$val[title]</a></li>
EOT;
}print <<<EOT
</ul>',
		'ifloop' => '0',
		'loops' => '',
		'descrip' => '',
	),
	'8' => array(
		'id' => '14',
		'name' => '用户排行',
		'tplid' => '12',
		'tagcode' => '<list action="user" num="12" title="用户模块" />
<ul>
<loop>
<li><span class="fr">{value}</span><a href="{url}" target="_blank">{title}</a></li>
</loop>
</ul>',
		'parsecode' => '
EOT;
$pwresult = pwTplGetData(\'用户排行\',\'用户模块\');
print <<<EOT
<ul>
EOT;
foreach($pwresult as $key=>$val){print <<<EOT
<li><span class="fr">$val[value]</span><a href="$val[url]" target="_blank">$val[title]</a></li>
EOT;
}print <<<EOT
</ul>',
		'ifloop' => '0',
		'loops' => '',
		'descrip' => '',
	),
	'9' => array(
		'id' => '11',
		'name' => '首页播放器',
		'tplid' => '11',
		'tagcode' => '<div id="pwSlidePlayer" class="pwSlide cc">
<!--#
	$tmpCount=0;
#-->
					<list action="image" num="6" title="播放器" />
					<loop>
<!--#
	$tmpStyle = $tmpCount ? \'style="display:none;"\' : \'\';
	$tmpCount++;
#-->
                        <div id="Switch_$key" $tmpStyle>
                            <a href="{url}" target="_blank"><img class="pwSlideFilter" src="{image,288,198}" />
							<h1>{title,36}</h1></a>
                        </div>
                        </loop>
					<ul id="SwitchNav"></ul>
					<div class="pwSlide-bg"></div>
				</div>
				<div class="c"></div>
				<script type="text/javascript" src="js/sliderplayer.js"></script>
				<script type="text/javascript">pwSliderPlayers("pwSlidePlayer");</script>',
		'parsecode' => '<div id="pwSlidePlayer" class="pwSlide cc">
<!--#
	$tmpCount=0;
#-->
EOT;
$pwresult = pwTplGetData(\'首页播放器\',\'播放器\');
foreach($pwresult as $key=>$val){print <<<EOT
<!--#
	$tmpStyle = $tmpCount ? \'style="display:none;"\' : \'\';
	$tmpCount++;
#-->
                        <div id="Switch_$key" $tmpStyle>
                            <a href="$val[url]" target="_blank"><img src="$val[image]" />
							<h1>$val[title]</h1></a>
                        </div>
EOT;
}print <<<EOT
					<div class="pwSlide-bg"></div>
					<ul id="SwitchNav"></ul>
				</div>
				<div class="c"></div>
				<script type="text/javascript" src="js/sliderplayer.js"></script>
				<script type="text/javascript">pwSliderPlayers("pwSlidePlayer");</script>',
		'ifloop' => '0',
		'loops' => '',
		'descrip' => '',
	),
	'10' => array(
		'id' => '12',
		'name' => '首页播放器下方',
		'tplid' => '3',
		'tagcode' => '<list action="image" num="2" title="图片模块" />
<table width="100%">
<tr>
<th>
<loop>
<a href="{url}" target="_blank"><img src="{image,100,100}" class="fl" /></a>
</loop>
</th>
<td>
<list action="subject" num="10" title="帖子排行模块" />
<ul>
<loop>
<li><a href="{url}" title="{title}" target="_blank">{title,30}</a></li>
</loop>
</ul>
</td>
</tr></table>',
		'parsecode' => '
EOT;
$pwresult = pwTplGetData(\'首页播放器下方\',\'图片模块\');
print <<<EOT
<table width="100%">
<tr>
<th>
EOT;
foreach($pwresult as $key=>$val){print <<<EOT
<a href="$val[url]" target="_blank"><img src="$val[image]" class="fl" /></a>
EOT;
}print <<<EOT
</th>
<td>
EOT;
$pwresult = pwTplGetData(\'首页播放器下方\',\'帖子排行模块\');
print <<<EOT
<ul>
EOT;
foreach($pwresult as $key=>$val){print <<<EOT
<li><a href="$val[url]" title="$val[title]" target="_blank">$val[title]</a></li>
EOT;
}print <<<EOT
</ul>
</td>
</tr></table>',
		'ifloop' => '0',
		'loops' => '',
		'descrip' => '',
	),
	'11' => array(
		'id' => '81',
		'name' => '频道页中部焦点_有作者',
		'tplid' => '14',
		'tagcode' => '<list action="subject" num="12" title="帖子列表" />
<ul>
<loop>
<li><span class="fr"><a href="u.php?username={author}" target="_blank">{author}</a></span><a href="{url}"  target="_blank">{title,32}</a></li>
</loop>
</ul>',
		'parsecode' => '
EOT;
$pwresult = pwTplGetData(\'频道页中部焦点_有作者\',\'帖子列表\');
print <<<EOT
<ul>
EOT;
foreach($pwresult as $key=>$val){print <<<EOT
<li><span class="fr"><a href="u.php?username=$val[author]" target="_blank">$val[author]</a></span><a href="$val[url]"  target="_blank">$val[title]</a></li>
EOT;
}print <<<EOT
</ul>',
		'ifloop' => '0',
		'loops' => '',
		'descrip' => '',
	),
	'12' => array(
		'id' => '78',
		'name' => '频道页本版热门',
		'tplid' => '13',
		'tagcode' => '<list action="subject" num="12" title="帖子列表" />
<ul>
<loop>
<li><span><a href="{forumurl}" target="_blank">[{forumname}]</a></span><a href="{url}"  target="_blank">{title,32}</a></li>
</loop>
</ul>',
		'parsecode' => '
EOT;
$pwresult = pwTplGetData(\'频道页本版热门\',\'帖子列表\');
print <<<EOT
<ul>
EOT;
foreach($pwresult as $key=>$val){print <<<EOT
<li><span><a href="$val[forumurl]" target="_blank">[$val[forumname]]</a></span><a href="$val[url]"  target="_blank">$val[title]</a></li>
EOT;
}print <<<EOT
</ul>',
		'ifloop' => '0',
		'loops' => '',
		'descrip' => '',
	),
	'13' => array(
		'id' => '79',
		'name' => '频道页页面中部焦点',
		'tplid' => '13',
		'tagcode' => '<list action="subject" num="12" title="帖子列表" />
<ul>
<loop>
<li><span><a href="{forumurl}" target="_blank">[{forumname}]</a></span><a href="{url}"  target="_blank">{title,32}</a></li>
</loop>
</ul>',
		'parsecode' => '
EOT;
$pwresult = pwTplGetData(\'频道页页面中部焦点\',\'帖子列表\');
print <<<EOT
<ul>
EOT;
foreach($pwresult as $key=>$val){print <<<EOT
<li><span><a href="$val[forumurl]" target="_blank">[$val[forumname]]</a></span><a href="$val[url]"  target="_blank">$val[title]</a></li>
EOT;
}print <<<EOT
</ul>',
		'ifloop' => '0',
		'loops' => '',
		'descrip' => '',
	),
	'14' => array(
		'id' => '80',
		'name' => '频道页中部焦点摘要图片',
		'tplid' => '5',
		'tagcode' => '<list action="image" num="3" title="图文模块" />
<loop>
<a href="{url}" target="_blank"><img src="{image,100,100}" class="fl" /></a>
<h4><a href="{url}" target="_blank">{title,32}</a></h4>
<p>{descrip,50}</p>
<div class="c"></div>
</loop>',
		'parsecode' => '
EOT;
$pwresult = pwTplGetData(\'频道页中部焦点摘要图片\',\'图文模块\');
foreach($pwresult as $key=>$val){print <<<EOT
<a href="$val[url]" target="_blank"><img src="$val[image]" class="fl" /></a>
<h4><a href="$val[url]" target="_blank">$val[title]</a></h4>
<p>$val[descrip]</p>
<div class="c"></div>
EOT;
}print <<<EOT
',
		'ifloop' => '0',
		'loops' => '',
		'descrip' => '',
	),
	'15' => array(
		'id' => '76',
		'name' => '频道页焦点摘要',
		'tplid' => '2',
		'tagcode' => '<list action="subject" num="3" title="帖子及摘要" />
<loop>
<h4><a href="{url}" target="_blank">{title,25}</a></h4>
<p>{descrip,40}</p>
<ul class="cc area-list-tree">
{tagrelate}
</ul>
</loop>',
		'parsecode' => '
EOT;
$pwresult = pwTplGetData(\'频道页焦点摘要\',\'帖子及摘要\');
print <<<EOT


EOT;
foreach($pwresult as $key=>$val){print <<<EOT

<h4><a href="$val[url]" target="_blank">$val[title]</a></h4>
<p>$val[descrip]</p>
<ul class="cc area-list-tree">

EOT;
foreach($val[tagrelate] as $key_1=>$val_1){print <<<EOT
<li><a href="$val_1[url]" target="_blank">$val_1[title]</a></li>
EOT;
}print <<<EOT

</ul>

EOT;
}print <<<EOT
',
		'ifloop' => '0',
		'loops' => '',
		'descrip' => '',
	),
	'16' => array(
		'id' => '77',
		'name' => '频道页焦点列表',
		'tplid' => '4',
		'tagcode' => '<list action="subject" num="8" title="帖子列表" />
<ul>
<loop>
<li><a href="{url}" target="_blank">{title,32}</a></li>
</loop>
</ul>',
		'parsecode' => '
EOT;
$pwresult = pwTplGetData(\'频道页焦点列表\',\'帖子列表\');
print <<<EOT
<ul>
EOT;
foreach($pwresult as $key=>$val){print <<<EOT
<li><a href="$val[url]" target="_blank">$val[title]</a></li>
EOT;
}print <<<EOT
</ul>',
		'ifloop' => '0',
		'loops' => '',
		'descrip' => '',
	),
	'17' => array(
		'id' => '74',
		'name' => '频道页播放器',
		'tplid' => '11',
		'tagcode' => '<div id="pwSlidePlayer" class="pwSlide cc">
<!--#
	$tmpCount=0;
#-->
					<list action="image" num="6" title="播放器" />
					<loop>
<!--#
	$tmpStyle = $tmpCount ? \'style="display:none;"\' : \'\';
	$tmpCount++;
#-->
                        <div id="Switch_$key" $tmpStyle>
                            <a href="{url}" target="_blank"><img class="pwSlideFilter" src="{image,288,198}" />
							<h1>{title,36}</h1></a>
                        </div>
                        </loop>
					<ul id="SwitchNav"></ul>
					<div class="pwSlide-bg"></div>
				</div>
				<div class="c"></div>
				<script type="text/javascript" src="js/sliderplayer.js"></script>
				<script type="text/javascript">pwSliderPlayers("pwSlidePlayer");</script>',
		'parsecode' => '<div id="pwSlidePlayer" class="pwSlide cc">
<!--#
	$tmpCount=0;
#-->
EOT;
$pwresult = pwTplGetData(\'频道页播放器\',\'播放器\');
foreach($pwresult as $key=>$val){print <<<EOT
<!--#
	$tmpStyle = $tmpCount ? \'style="display:none;"\' : \'\';
	$tmpCount++;
#-->
                        <div id="Switch_$key" $tmpStyle>
                            <a href="$val[url]" target="_blank"><img class="pwSlideFilter" src="$val[image]" />
							<h1>$val[title]</h1></a>
                        </div>
EOT;
}print <<<EOT
					<ul id="SwitchNav"></ul>
					<div class="pwSlide-bg"></div>
				</div>
				<div class="c"></div>
				<script type="text/javascript" src="js/sliderplayer.js"></script>
				<script type="text/javascript">pwSliderPlayers("pwSlidePlayer");</script>',
		'ifloop' => '0',
		'loops' => '',
		'descrip' => '',
	),
	'18' => array(
		'id' => '27',
		'name' => 'fun_焦点摘要',
		'tplid' => '2',
		'tagcode' => '<list action="subject" num="3" title="帖子及摘要" />
<loop>
<h4><a href="{url}" target="_blank">{title,25}</a></h4>
<p>{descrip,40}</p>
<ul class="cc area-list-tree">
{tagrelate}
</ul>
</loop>',
		'parsecode' => '
EOT;
$pwresult = pwTplGetData(\'fun_焦点摘要\',\'帖子及摘要\');
print <<<EOT


EOT;
foreach($pwresult as $key=>$val){print <<<EOT

<h4><a href="$val[url]" target="_blank">$val[title]</a></h4>
<p>$val[descrip]</p>
<ul class="cc area-list-tree">

EOT;
foreach($val[tagrelate] as $key_1=>$val_1){print <<<EOT
<li><a href="$val_1[url]" target="_blank">$val_1[title]</a></li>
EOT;
}print <<<EOT

</ul>

EOT;
}print <<<EOT
',
		'ifloop' => '0',
		'loops' => '',
		'descrip' => '',
	),
	'19' => array(
		'id' => '28',
		'name' => 'fun_焦点列表',
		'tplid' => '4',
		'tagcode' => '<list action="subject" num="8" title="帖子列表" />
<ul>
<loop>
<li><a href="{url}" target="_blank">{title,32}</a></li>
</loop>
</ul>',
		'parsecode' => '
EOT;
$pwresult = pwTplGetData(\'fun_焦点列表\',\'帖子列表\');
print <<<EOT
<ul>
EOT;
foreach($pwresult as $key=>$val){print <<<EOT
<li><a href="$val[url]" target="_blank">$val[title]</a></li>
EOT;
}print <<<EOT
</ul>',
		'ifloop' => '0',
		'loops' => '',
		'descrip' => '',
	),
	'20' => array(
		'id' => '29',
		'name' => 'fun_本版热门',
		'tplid' => '13',
		'tagcode' => '<list action="subject" num="12" title="帖子列表" />
<ul>
<loop>
<li><span><a href="{forumurl}" target="_blank">[{forumname}]</a></span><a href="{url}"  target="_blank">{title,32}</a></li>
</loop>
</ul>',
		'parsecode' => '
EOT;
$pwresult = pwTplGetData(\'fun_本版热门\',\'帖子列表\');
print <<<EOT
<ul>
EOT;
foreach($pwresult as $key=>$val){print <<<EOT
<li><span><a href="$val[forumurl]" target="_blank">[$val[forumname]]</a></span><a href="$val[url]"  target="_blank">$val[title]</a></li>
EOT;
}print <<<EOT
</ul>',
		'ifloop' => '0',
		'loops' => '',
		'descrip' => '',
	),
	'21' => array(
		'id' => '30',
		'name' => 'fun_播放器',
		'tplid' => '11',
		'tagcode' => '<div id="pwSlidePlayer" class="pwSlide cc">
<!--#
	$tmpCount=0;
#-->
					<list action="image" num="6" title="播放器" />
					<loop>
<!--#
	$tmpStyle = $tmpCount ? \'style="display:none;"\' : \'\';
	$tmpCount++;
#-->
                        <div id="Switch_$key" $tmpStyle>
                            <a href="{url}" target="_blank"><img class="pwSlideFilter" src="{image,288,198}" />
							<h1>{title,36}</h1></a>
                        </div>
                        </loop>
					<ul id="SwitchNav"></ul>
					<div class="pwSlide-bg"></div>
				</div>
				<div class="c"></div>
				<script type="text/javascript" src="js/sliderplayer.js"></script>
				<script type="text/javascript">pwSliderPlayers("pwSlidePlayer");</script>',
		'parsecode' => '<div id="pwSlidePlayer" class="pwSlide cc">
<!--#
	$tmpCount=0;
#-->
EOT;
$pwresult = pwTplGetData(\'fun_播放器\',\'播放器\');
foreach($pwresult as $key=>$val){print <<<EOT
<!--#
	$tmpStyle = $tmpCount ? \'style="display:none;"\' : \'\';
	$tmpCount++;
#-->
                        <div id="Switch_$key" $tmpStyle>
                            <a href="$val[url]" target="_blank"><img class="pwSlideFilter" src="$val[image]" />
							<h1>$val[title]</h1></a>
                        </div>
EOT;
}print <<<EOT
					<ul id="SwitchNav"></ul>
					<div class="pwSlide-bg"></div>
				</div>
				<div class="c"></div>
				<script type="text/javascript" src="js/sliderplayer.js"></script>
				<script type="text/javascript">pwSliderPlayers("pwSlidePlayer");</script>',
		'ifloop' => '0',
		'loops' => '',
		'descrip' => '',
	),
	'22' => array(
		'id' => '32',
		'name' => '频道左侧站点推荐',
		'tplid' => '7',
		'tagcode' => '<list action="image" num="6" title="图片模块" />
<loop>
<a href="{url}" target="_blank"><img src="{image,100,100}" class="fl" /><p>{title,8}</p></a>
</loop>',
		'parsecode' => '
EOT;
$pwresult = pwTplGetData(\'频道左侧站点推荐\',\'图片模块\');
foreach($pwresult as $key=>$val){print <<<EOT
<a href="$val[url]" target="_blank"><img src="$val[image]" class="fl" /><p>$val[title]</p></a>
EOT;
}print <<<EOT
',
		'ifloop' => '0',
		'loops' => '',
		'descrip' => '',
	),
	'23' => array(
		'id' => '33',
		'name' => 'fun_页面中部焦点',
		'tplid' => '13',
		'tagcode' => '<list action="subject" num="12" title="帖子列表" />
<ul>
<loop>
<li><span><a href="{forumurl}" target="_blank">[{forumname}]</a></span><a href="{url}"  target="_blank">{title,32}</a></li>
</loop>
</ul>',
		'parsecode' => '
EOT;
$pwresult = pwTplGetData(\'fun_页面中部焦点\',\'帖子列表\');
print <<<EOT
<ul>
EOT;
foreach($pwresult as $key=>$val){print <<<EOT
<li><span><a href="$val[forumurl]" target="_blank">[$val[forumname]]</a></span><a href="$val[url]"  target="_blank">$val[title]</a></li>
EOT;
}print <<<EOT
</ul>',
		'ifloop' => '0',
		'loops' => '',
		'descrip' => '',
	),
	'24' => array(
		'id' => '36',
		'name' => 'fun_中部焦点摘要图片',
		'tplid' => '5',
		'tagcode' => '<list action="image" num="3" title="图文模块" />
<loop>
<a href="{url}" target="_blank"><img src="{image,100,100}" class="fl" /></a>
<h4><a href="{url}" target="_blank">{title,32}</a></h4>
<p>{descrip,50}</p>
<div class="c"></div>
</loop>',
		'parsecode' => '
EOT;
$pwresult = pwTplGetData(\'fun_中部焦点摘要图片\',\'图文模块\');
foreach($pwresult as $key=>$val){print <<<EOT
<a href="$val[url]" target="_blank"><img src="$val[image]" class="fl" /></a>
<h4><a href="$val[url]" target="_blank">$val[title]</a></h4>
<p>$val[descrip]</p>
<div class="c"></div>
EOT;
}print <<<EOT
',
		'ifloop' => '0',
		'loops' => '',
		'descrip' => '',
	),
	'25' => array(
		'id' => '40',
		'name' => 'fun_中部焦点_有作者',
		'tplid' => '14',
		'tagcode' => '<list action="subject" num="12" title="帖子列表" />
<ul>
<loop>
<li><span class="fr"><a href="u.php?username={author}" target="_blank">{author}</a></span><a href="{url}"  target="_blank">{title,32}</a></li>
</loop>
</ul>',
		'parsecode' => '
EOT;
$pwresult = pwTplGetData(\'fun_中部焦点_有作者\',\'帖子列表\');
print <<<EOT
<ul>
EOT;
foreach($pwresult as $key=>$val){print <<<EOT
<li><span class="fr"><a href="u.php?username=$val[author]" target="_blank">$val[author]</a></span><a href="$val[url]"  target="_blank">$val[title]</a></li>
EOT;
}print <<<EOT
</ul>',
		'ifloop' => '0',
		'loops' => '',
		'descrip' => '',
	),
	'26' => array(
		'id' => '44',
		'name' => 'auto_播放器',
		'tplid' => '11',
		'tagcode' => '<div id="pwSlidePlayer" class="pwSlide cc">
<!--#
	$tmpCount=0;
#-->
					<list action="image" num="6" title="播放器" />
					<loop>
<!--#
	$tmpStyle = $tmpCount ? \'style="display:none;"\' : \'\';
	$tmpCount++;
#-->
                        <div id="Switch_$key" $tmpStyle>
                            <a href="{url}" target="_blank"><img class="pwSlideFilter" src="{image,288,198}" />
							<h1>{title,36}</h1></a>
                        </div>
                        </loop>
					<ul id="SwitchNav"></ul>
					<div class="pwSlide-bg"></div>
				</div>
				<div class="c"></div>
				<script type="text/javascript" src="js/sliderplayer.js"></script>
				<script type="text/javascript">pwSliderPlayers("pwSlidePlayer");</script>',
		'parsecode' => '<div id="pwSlidePlayer" class="pwSlide cc">
<!--#
	$tmpCount=0;
#-->
EOT;
$pwresult = pwTplGetData(\'auto_播放器\',\'播放器\');
foreach($pwresult as $key=>$val){print <<<EOT
<!--#
	$tmpStyle = $tmpCount ? \'style="display:none;"\' : \'\';
	$tmpCount++;
#-->
                        <div id="Switch_$key" $tmpStyle>
                            <a href="$val[url]" target="_blank"><img class="pwSlideFilter" src="$val[image]" />
							<h1>$val[title]</h1></a>
                        </div>
EOT;
}print <<<EOT
					<ul id="SwitchNav"></ul>
					<div class="pwSlide-bg"></div>
				</div>
				<div class="c"></div>
				<script type="text/javascript" src="js/sliderplayer.js"></script>
				<script type="text/javascript">pwSliderPlayers("pwSlidePlayer");</script>',
		'ifloop' => '0',
		'loops' => '',
		'descrip' => '',
	),
	'27' => array(
		'id' => '43',
		'name' => 'fun_频道页循环',
		'tplid' => '15',
		'tagcode' => '<list action="image" num="2" title="图片模块" />
<table width="100%">
<tr>
<th>
<loop>
<a href="{url}" target="_blank"><img src="{image,100,100}" class="fl" /></a>
<h4><a href="{url}" target="_blank">{title,32}</a></h4>
<p>{descrip,50}</p>
<div class="c"></div>
</loop>
</th>
<td>
<list action="subject" num="10" title="帖子排行模块" />
<ul>
<loop>
<li><a href="u.php?username={author}" class="fr">{author}</a><a href="{url}" title="{title}" target="_blank">{title,30}</a></li>
</loop>
</ul>
</td>
</tr></table>',
		'parsecode' => '
EOT;
$pwresult = pwTplGetData(\'fun_频道页循环\',\'图片模块\',$loopid);
print <<<EOT
<table width="100%">
<tr>
<th>
EOT;
foreach($pwresult as $key=>$val){print <<<EOT
<a href="$val[url]" target="_blank"><img src="$val[image]" class="fl" /></a>
<h4><a href="$val[url]" target="_blank">$val[title]</a></h4>
<p>$val[descrip]</p>
<div class="c"></div>
EOT;
}print <<<EOT
</th>
<td>
EOT;
$pwresult = pwTplGetData(\'fun_频道页循环\',\'帖子排行模块\',$loopid);
print <<<EOT
<ul>
EOT;
foreach($pwresult as $key=>$val){print <<<EOT
<li><a href="u.php?username=$val[author]" class="fr">$val[author]</a><span><a href="$val[forumurl]" target="_blank">[$val[forumname]]</a></span><a href="$val[url]" title="$val[title]" target="_blank">$val[title]</a></li>
EOT;
}print <<<EOT
</ul>
</td>
</tr></table>',
		'ifloop' => '1',
		'loops' => '',
		'descrip' => '',
	),
	'28' => array(
		'id' => '45',
		'name' => 'auto_焦点摘要',
		'tplid' => '2',
		'tagcode' => '<list action="subject" num="3" title="帖子及摘要" />
<loop>
<h4><a href="{url}" target="_blank">{title,25}</a></h4>
<p>{descrip,40}</p>
<ul class="cc area-list-tree">
{tagrelate}
</ul>
</loop>',
		'parsecode' => '
EOT;
$pwresult = pwTplGetData(\'auto_焦点摘要\',\'帖子及摘要\');
print <<<EOT


EOT;
foreach($pwresult as $key=>$val){print <<<EOT

<h4><a href="$val[url]" target="_blank">$val[title]</a></h4>
<p>$val[descrip]</p>
<ul class="cc area-list-tree">

EOT;
foreach($val[tagrelate] as $key_1=>$val_1){print <<<EOT
<li><a href="$val_1[url]" target="_blank">$val_1[title]</a></li>
EOT;
}print <<<EOT

</ul>

EOT;
}print <<<EOT
',
		'ifloop' => '0',
		'loops' => '',
		'descrip' => '',
	),
	'29' => array(
		'id' => '46',
		'name' => 'auto_焦点列表',
		'tplid' => '4',
		'tagcode' => '<list action="subject" num="8" title="帖子列表" />
<ul>
<loop>
<li><a href="{url}" target="_blank">{title,32}</a></li>
</loop>
</ul>',
		'parsecode' => '
EOT;
$pwresult = pwTplGetData(\'auto_焦点列表\',\'帖子列表\');
print <<<EOT
<ul>
EOT;
foreach($pwresult as $key=>$val){print <<<EOT
<li><a href="$val[url]" target="_blank">$val[title]</a></li>
EOT;
}print <<<EOT
</ul>',
		'ifloop' => '0',
		'loops' => '',
		'descrip' => '',
	),
	'30' => array(
		'id' => '47',
		'name' => 'auto_本版热门',
		'tplid' => '13',
		'tagcode' => '<list action="subject" num="12" title="帖子列表" />
<ul>
<loop>
<li><span><a href="{forumurl}" target="_blank">[{forumname}]</a></span><a href="{url}"  target="_blank">{title,32}</a></li>
</loop>
</ul>',
		'parsecode' => '
EOT;
$pwresult = pwTplGetData(\'auto_本版热门\',\'帖子列表\');
print <<<EOT
<ul>
EOT;
foreach($pwresult as $key=>$val){print <<<EOT
<li><span><a href="$val[forumurl]" target="_blank">[$val[forumname]]</a></span><a href="$val[url]"  target="_blank">$val[title]</a></li>
EOT;
}print <<<EOT
</ul>',
		'ifloop' => '0',
		'loops' => '',
		'descrip' => '',
	),
	'31' => array(
		'id' => '48',
		'name' => 'atuo_中部图片',
		'tplid' => '16',
		'tagcode' => '<list action="image" num="6" title="图片模块" />
<loop>
<a href="{url}" target="_blank"><img src="{image,100,100}" class="fl" /></a>
</loop>',
		'parsecode' => '
EOT;
$pwresult = pwTplGetData(\'atuo_中部图片\',\'图片模块\');
foreach($pwresult as $key=>$val){print <<<EOT
<a href="$val[url]" target="_blank"><img src="$val[image]" class="fl" /></a>
EOT;
}print <<<EOT
',
		'ifloop' => '0',
		'loops' => '',
		'descrip' => '',
	),
	'32' => array(
		'id' => '49',
		'name' => 'auto_中部焦点1',
		'tplid' => '13',
		'tagcode' => '<list action="subject" num="12" title="帖子列表" />
<ul>
<loop>
<li><span><a href="{forumurl}" target="_blank">[{forumname}]</a></span><a href="{url}"  target="_blank">{title,32}</a></li>
</loop>
</ul>',
		'parsecode' => '
EOT;
$pwresult = pwTplGetData(\'auto_中部焦点1\',\'帖子列表\');
print <<<EOT
<ul>
EOT;
foreach($pwresult as $key=>$val){print <<<EOT
<li><span><a href="$val[forumurl]" target="_blank">[$val[forumname]]</a></span><a href="$val[url]"  target="_blank">$val[title]</a></li>
EOT;
}print <<<EOT
</ul>',
		'ifloop' => '0',
		'loops' => '',
		'descrip' => '',
	),
	'33' => array(
		'id' => '50',
		'name' => 'auto_中部焦点2',
		'tplid' => '13',
		'tagcode' => '<list action="subject" num="12" title="帖子列表" />
<ul>
<loop>
<li><span><a href="{forumurl}" target="_blank">[{forumname}]</a></span><a href="{url}"  target="_blank">{title,32}</a></li>
</loop>
</ul>',
		'parsecode' => '
EOT;
$pwresult = pwTplGetData(\'auto_中部焦点2\',\'帖子列表\');
print <<<EOT
<ul>
EOT;
foreach($pwresult as $key=>$val){print <<<EOT
<li><span><a href="$val[forumurl]" target="_blank">[$val[forumname]]</a></span><a href="$val[url]"  target="_blank">$val[title]</a></li>
EOT;
}print <<<EOT
</ul>',
		'ifloop' => '0',
		'loops' => '',
		'descrip' => '',
	),
	'34' => array(
		'id' => '51',
		'name' => 'atuo_频道页循环',
		'tplid' => '15',
		'tagcode' => '<list action="image" num="2" title="图片模块" />
<table width="100%">
<tr>
<th>
<loop>
<a href="{url}" target="_blank"><img src="{image,100,100}" class="fl" /></a>
<h4><a href="{url}" target="_blank">{title,32}</a></h4>
<p>{descrip,50}</p>
<div class="c"></div>
</loop>
</th>
<td>
<list action="subject" num="10" title="帖子排行模块" />
<ul>
<loop>
<li><a href="u.php?username={author}" class="fr">{author}</a><a href="{url}" title="{title}" target="_blank">{title,30}</a></li>
</loop>
</ul>
</td>
</tr></table>',
		'parsecode' => '
EOT;
$pwresult = pwTplGetData(\'atuo_频道页循环\',\'图片模块\',$loopid);
print <<<EOT
<table width="100%">
<tr>
<th>
EOT;
foreach($pwresult as $key=>$val){print <<<EOT
<a href="$val[url]" target="_blank"><img src="$val[image]" class="fl" /></a>
<h4><a href="$val[url]" target="_blank">$val[title]</a></h4>
<p>$val[descrip]</p>
<div class="c"></div>
EOT;
}print <<<EOT
</th>
<td>
EOT;
$pwresult = pwTplGetData(\'atuo_频道页循环\',\'帖子排行模块\',$loopid);
print <<<EOT
<ul>
EOT;
foreach($pwresult as $key=>$val){print <<<EOT
<li><a href="u.php?username=$val[author]" class="fr">$val[author]</a><a href="$val[url]" title="$val[title]" target="_blank">$val[title]</a></li>
EOT;
}print <<<EOT
</ul>
</td>
</tr></table>',
		'ifloop' => '1',
		'loops' => '',
		'descrip' => '',
	),
	'35' => array(
		'id' => '52',
		'name' => 'children_播放器',
		'tplid' => '11',
		'tagcode' => '<div id="pwSlidePlayer" class="pwSlide cc">
<!--#
	$tmpCount=0;
#-->
					<list action="image" num="6" title="播放器" />
					<loop>
<!--#
	$tmpStyle = $tmpCount ? \'style="display:none;"\' : \'\';
	$tmpCount++;
#-->
                        <div id="Switch_$key" $tmpStyle>
                            <a href="{url}" target="_blank"><img class="pwSlideFilter" src="{image,288,198}" />
							<h1>{title,36}</h1></a>
                        </div>
                        </loop>
					<ul id="SwitchNav"></ul>
					<div class="pwSlide-bg"></div>
				</div>
				<div class="c"></div>
				<script type="text/javascript" src="js/sliderplayer.js"></script>
				<script type="text/javascript">pwSliderPlayers("pwSlidePlayer");</script>',
		'parsecode' => '<div id="pwSlidePlayer" class="pwSlide cc">
<!--#
	$tmpCount=0;
#-->
EOT;
$pwresult = pwTplGetData(\'children_播放器\',\'播放器\');
foreach($pwresult as $key=>$val){print <<<EOT
<!--#
	$tmpStyle = $tmpCount ? \'style="display:none;"\' : \'\';
	$tmpCount++;
#-->
                        <div id="Switch_$key" $tmpStyle>
                            <a href="$val[url]" target="_blank"><img class="pwSlideFilter" src="$val[image]" />
							<h1>$val[title]</h1></a>
                        </div>
EOT;
}print <<<EOT
					<ul id="SwitchNav"></ul>
					<div class="pwSlide-bg"></div>
				</div>
				<div class="c"></div>
				<script type="text/javascript" src="js/sliderplayer.js"></script>
				<script type="text/javascript">pwSliderPlayers("pwSlidePlayer");</script>',
		'ifloop' => '0',
		'loops' => '',
		'descrip' => '',
	),
	'36' => array(
		'id' => '53',
		'name' => 'children_焦点摘要',
		'tplid' => '2',
		'tagcode' => '<list action="subject" num="3" title="帖子及摘要" />
<loop>
<h4><a href="{url}" target="_blank">{title,25}</a></h4>
<p>{descrip,40}</p>
<ul class="cc area-list-tree">
{tagrelate}
</ul>
</loop>',
		'parsecode' => '
EOT;
$pwresult = pwTplGetData(\'children_焦点摘要\',\'帖子及摘要\');
print <<<EOT


EOT;
foreach($pwresult as $key=>$val){print <<<EOT

<h4><a href="$val[url]" target="_blank">$val[title]</a></h4>
<p>$val[descrip]</p>
<ul class="cc area-list-tree">

EOT;
foreach($val[tagrelate] as $key_1=>$val_1){print <<<EOT
<li><a href="$val_1[url]" target="_blank">$val_1[title]</a></li>
EOT;
}print <<<EOT

</ul>

EOT;
}print <<<EOT
',
		'ifloop' => '0',
		'loops' => '',
		'descrip' => '',
	),
	'37' => array(
		'id' => '54',
		'name' => 'children_焦点列表',
		'tplid' => '4',
		'tagcode' => '<list action="subject" num="8" title="帖子列表" />
<ul>
<loop>
<li><a href="{url}" target="_blank">{title,32}</a></li>
</loop>
</ul>',
		'parsecode' => '
EOT;
$pwresult = pwTplGetData(\'children_焦点列表\',\'帖子列表\');
print <<<EOT
<ul>
EOT;
foreach($pwresult as $key=>$val){print <<<EOT
<li><a href="$val[url]" target="_blank">$val[title]</a></li>
EOT;
}print <<<EOT
</ul>',
		'ifloop' => '0',
		'loops' => '',
		'descrip' => '',
	),
	'38' => array(
		'id' => '55',
		'name' => 'children_本版热门',
		'tplid' => '13',
		'tagcode' => '<list action="subject" num="12" title="帖子列表" />
<ul>
<loop>
<li><span><a href="{forumurl}" target="_blank">[{forumname}]</a></span><a href="{url}"  target="_blank">{title,32}</a></li>
</loop>
</ul>',
		'parsecode' => '
EOT;
$pwresult = pwTplGetData(\'children_本版热门\',\'帖子列表\');
print <<<EOT
<ul>
EOT;
foreach($pwresult as $key=>$val){print <<<EOT
<li><span><a href="$val[forumurl]" target="_blank">[$val[forumname]]</a></span><a href="$val[url]"  target="_blank">$val[title]</a></li>
EOT;
}print <<<EOT
</ul>',
		'ifloop' => '0',
		'loops' => '',
		'descrip' => '',
	),
	'39' => array(
		'id' => '56',
		'name' => 'children_页面中部焦点',
		'tplid' => '13',
		'tagcode' => '<list action="subject" num="12" title="帖子列表" />
<ul>
<loop>
<li><span><a href="{forumurl}" target="_blank">[{forumname}]</a></span><a href="{url}"  target="_blank">{title,32}</a></li>
</loop>
</ul>',
		'parsecode' => '
EOT;
$pwresult = pwTplGetData(\'children_页面中部焦点\',\'帖子列表\');
print <<<EOT
<ul>
EOT;
foreach($pwresult as $key=>$val){print <<<EOT
<li><span><a href="$val[forumurl]" target="_blank">[$val[forumname]]</a></span><a href="$val[url]"  target="_blank">$val[title]</a></li>
EOT;
}print <<<EOT
</ul>',
		'ifloop' => '0',
		'loops' => '',
		'descrip' => '',
	),
	'40' => array(
		'id' => '57',
		'name' => 'children_中部焦点摘要图片',
		'tplid' => '5',
		'tagcode' => '<list action="image" num="3" title="图文模块" />
<loop>
<a href="{url}" target="_blank"><img src="{image,100,100}" class="fl" /></a>
<h4><a href="{url}" target="_blank">{title,32}</a></h4>
<p>{descrip,50}</p>
<div class="c"></div>
</loop>',
		'parsecode' => '
EOT;
$pwresult = pwTplGetData(\'children_中部焦点摘要图片\',\'图文模块\');
foreach($pwresult as $key=>$val){print <<<EOT
<a href="$val[url]" target="_blank"><img src="$val[image]" class="fl" /></a>
<h4><a href="$val[url]" target="_blank">$val[title]</a></h4>
<p>$val[descrip]</p>
<div class="c"></div>
EOT;
}print <<<EOT
',
		'ifloop' => '0',
		'loops' => '',
		'descrip' => '',
	),
	'41' => array(
		'id' => '58',
		'name' => 'children_中部焦点_有作者',
		'tplid' => '14',
		'tagcode' => '<list action="subject" num="12" title="帖子列表" />
<ul>
<loop>
<li><span class="fr"><a href="u.php?username={author}" target="_blank">{author}</a></span><a href="{url}"  target="_blank">{title,32}</a></li>
</loop>
</ul>',
		'parsecode' => '
EOT;
$pwresult = pwTplGetData(\'children_中部焦点_有作者\',\'帖子列表\');
print <<<EOT
<ul>
EOT;
foreach($pwresult as $key=>$val){print <<<EOT
<li><span class="fr"><a href="u.php?username=$val[author]" target="_blank">$val[author]</a></span><a href="$val[url]"  target="_blank">$val[title]</a></li>
EOT;
}print <<<EOT
</ul>',
		'ifloop' => '0',
		'loops' => '',
		'descrip' => '',
	),
	'42' => array(
		'id' => '59',
		'name' => 'children_频道页循环',
		'tplid' => '15',
		'tagcode' => '<list action="image" num="2" title="图片模块" />
<table width="100%">
<tr>
<th>
<loop>
<a href="{url}" target="_blank"><img src="{image,100,100}" class="fl" /></a>
<h4><a href="{url}" target="_blank">{title,32}</a></h4>
<p>{descrip,50}</p>
<div class="c"></div>
</loop>
</th>
<td>
<list action="subject" num="10" title="帖子排行模块" />
<ul>
<loop>
<li><a href="u.php?username={author}" class="fr">{author}</a><a href="{url}" title="{title}" target="_blank">{title,30}</a></li>
</loop>
</ul>
</td>
</tr></table>',
		'parsecode' => '
EOT;
$pwresult = pwTplGetData(\'children_频道页循环\',\'图片模块\',$loopid);
print <<<EOT
<table width="100%">
<tr>
<th>
EOT;
foreach($pwresult as $key=>$val){print <<<EOT
<a href="$val[url]" target="_blank"><img src="$val[image]" class="fl" /></a>
<h4><a href="$val[url]" target="_blank">$val[title]</a></h4>
<p>$val[descrip]</p>
<div class="c"></div>
EOT;
}print <<<EOT
</th>
<td>
EOT;
$pwresult = pwTplGetData(\'children_频道页循环\',\'帖子排行模块\',$loopid);
print <<<EOT
<ul>
EOT;
foreach($pwresult as $key=>$val){print <<<EOT
<li><a href="u.php?username=$val[author]" class="fr">$val[author]</a><a href="$val[url]" title="$val[title]" target="_blank">$val[title]</a></li>
EOT;
}print <<<EOT
</ul>
</td>
</tr></table>',
		'ifloop' => '1',
		'loops' => '',
		'descrip' => '',
	),
	'43' => array(
		'id' => '60',
		'name' => 'jia_播放器',
		'tplid' => '11',
		'tagcode' => '<div id="pwSlidePlayer" class="pwSlide cc">
<!--#
	$tmpCount=0;
#-->
					<list action="image" num="6" title="播放器" />
					<loop>
<!--#
	$tmpStyle = $tmpCount ? \'style="display:none;"\' : \'\';
	$tmpCount++;
#-->
                        <div id="Switch_$key" $tmpStyle>
                            <a href="{url}" target="_blank"><img class="pwSlideFilter" src="{image,288,198}" />
							<h1>{title,36}</h1></a>
                        </div>
                        </loop>
					<ul id="SwitchNav"></ul>
					<div class="pwSlide-bg"></div>
				</div>
				<div class="c"></div>
				<script type="text/javascript" src="js/sliderplayer.js"></script>
				<script type="text/javascript">pwSliderPlayers("pwSlidePlayer");</script>',
		'parsecode' => '<div id="pwSlidePlayer" class="pwSlide cc">
<!--#
	$tmpCount=0;
#-->
EOT;
$pwresult = pwTplGetData(\'jia_播放器\',\'播放器\');
foreach($pwresult as $key=>$val){print <<<EOT
<!--#
	$tmpStyle = $tmpCount ? \'style="display:none;"\' : \'\';
	$tmpCount++;
#-->
                        <div id="Switch_$key" $tmpStyle>
                            <a href="$val[url]" target="_blank"><img class="pwSlideFilter" src="$val[image]" />
							<h1>$val[title]</h1></a>
                        </div>
EOT;
}print <<<EOT
					<ul id="SwitchNav"></ul>
					<div class="pwSlide-bg"></div>
				</div>
				<div class="c"></div>
				<script type="text/javascript" src="js/sliderplayer.js"></script>
				<script type="text/javascript">pwSliderPlayers("pwSlidePlayer");</script>',
		'ifloop' => '0',
		'loops' => '',
		'descrip' => '',
	),
	'44' => array(
		'id' => '61',
		'name' => 'jia_焦点摘要',
		'tplid' => '2',
		'tagcode' => '<list action="subject" num="3" title="帖子及摘要" />
<loop>
<h4><a href="{url}" target="_blank">{title,25}</a></h4>
<p>{descrip,40}</p>
<ul class="cc area-list-tree">
{tagrelate}
</ul>
</loop>',
		'parsecode' => '
EOT;
$pwresult = pwTplGetData(\'jia_焦点摘要\',\'帖子及摘要\');
print <<<EOT


EOT;
foreach($pwresult as $key=>$val){print <<<EOT

<h4><a href="$val[url]" target="_blank">$val[title]</a></h4>
<p>$val[descrip]</p>
<ul class="cc area-list-tree">

EOT;
foreach($val[tagrelate] as $key_1=>$val_1){print <<<EOT
<li><a href="$val_1[url]" target="_blank">$val_1[title]</a></li>
EOT;
}print <<<EOT

</ul>

EOT;
}print <<<EOT
',
		'ifloop' => '0',
		'loops' => '',
		'descrip' => '',
	),
	'45' => array(
		'id' => '62',
		'name' => 'jia_页面中部焦点',
		'tplid' => '13',
		'tagcode' => '<list action="subject" num="12" title="帖子列表" />
<ul>
<loop>
<li><span><a href="{forumurl}" target="_blank">[{forumname}]</a></span><a href="{url}"  target="_blank">{title,32}</a></li>
</loop>
</ul>',
		'parsecode' => '
EOT;
$pwresult = pwTplGetData(\'jia_页面中部焦点\',\'帖子列表\');
print <<<EOT
<ul>
EOT;
foreach($pwresult as $key=>$val){print <<<EOT
<li><span><a href="$val[forumurl]" target="_blank">[$val[forumname]]</a></span><a href="$val[url]"  target="_blank">$val[title]</a></li>
EOT;
}print <<<EOT
</ul>',
		'ifloop' => '0',
		'loops' => '',
		'descrip' => '',
	),
	'46' => array(
		'id' => '63',
		'name' => 'jia_中部焦点摘要图片',
		'tplid' => '5',
		'tagcode' => '<list action="image" num="3" title="图文模块" />
<loop>
<a href="{url}" target="_blank"><img src="{image,100,100}" class="fl" /></a>
<h4><a href="{url}" target="_blank">{title,32}</a></h4>
<p>{descrip,50}</p>
<div class="c"></div>
</loop>',
		'parsecode' => '
EOT;
$pwresult = pwTplGetData(\'jia_中部焦点摘要图片\',\'图文模块\');
foreach($pwresult as $key=>$val){print <<<EOT
<a href="$val[url]" target="_blank"><img src="$val[image]" class="fl" /></a>
<h4><a href="$val[url]" target="_blank">$val[title]</a></h4>
<p>$val[descrip]</p>
<div class="c"></div>
EOT;
}print <<<EOT
',
		'ifloop' => '0',
		'loops' => '',
		'descrip' => '',
	),
	'47' => array(
		'id' => '64',
		'name' => 'jia_中部焦点_有作者',
		'tplid' => '14',
		'tagcode' => '<list action="subject" num="12" title="帖子列表" />
<ul>
<loop>
<li><span class="fr"><a href="u.php?username={author}" target="_blank">{author}</a></span><a href="{url}"  target="_blank">{title,32}</a></li>
</loop>
</ul>',
		'parsecode' => '
EOT;
$pwresult = pwTplGetData(\'jia_中部焦点_有作者\',\'帖子列表\');
print <<<EOT
<ul>
EOT;
foreach($pwresult as $key=>$val){print <<<EOT
<li><span class="fr"><a href="u.php?username=$val[author]" target="_blank">$val[author]</a></span><a href="$val[url]"  target="_blank">$val[title]</a></li>
EOT;
}print <<<EOT
</ul>',
		'ifloop' => '0',
		'loops' => '',
		'descrip' => '',
	),
	'48' => array(
		'id' => '65',
		'name' => 'jia_频道页循环',
		'tplid' => '15',
		'tagcode' => '<list action="image" num="2" title="图片模块" />
<table width="100%">
<tr>
<th>
<loop>
<a href="{url}" target="_blank"><img src="{image,100,100}" class="fl" /></a>
<h4><a href="{url}" target="_blank">{title,32}</a></h4>
<p>{descrip,50}</p>
<div class="c"></div>
</loop>
</th>
<td>
<list action="subject" num="10" title="帖子排行模块" />
<ul>
<loop>
<li><a href="u.php?username={author}" class="fr">{author}</a><a href="{url}" title="{title}" target="_blank">{title,30}</a></li>
</loop>
</ul>
</td>
</tr></table>',
		'parsecode' => '
EOT;
$pwresult = pwTplGetData(\'jia_频道页循环\',\'图片模块\',$loopid);
print <<<EOT
<table width="100%">
<tr>
<th>
EOT;
foreach($pwresult as $key=>$val){print <<<EOT
<a href="$val[url]" target="_blank"><img src="$val[image]" class="fl" /></a>
<h4><a href="$val[url]" target="_blank">$val[title]</a></h4>
<p>$val[descrip]</p>
<div class="c"></div>
EOT;
}print <<<EOT
</th>
<td>
EOT;
$pwresult = pwTplGetData(\'jia_频道页循环\',\'帖子排行模块\',$loopid);
print <<<EOT
<ul>
EOT;
foreach($pwresult as $key=>$val){print <<<EOT
<li><a href="u.php?username=$val[author]" class="fr">$val[author]</a><a href="$val[url]" title="$val[title]" target="_blank">$val[title]</a></li>
EOT;
}print <<<EOT
</ul>
</td>
</tr></table>',
		'ifloop' => '1',
		'loops' => '',
		'descrip' => '',
	),
	'49' => array(
		'id' => '66',
		'name' => 'women_播放器',
		'tplid' => '11',
		'tagcode' => '<div id="pwSlidePlayer" class="pwSlide cc">
<!--#
	$tmpCount=0;
#-->
					<list action="image" num="6" title="播放器" />
					<loop>
<!--#
	$tmpStyle = $tmpCount ? \'style="display:none;"\' : \'\';
	$tmpCount++;
#-->
                        <div id="Switch_$key" $tmpStyle>
                            <a href="{url}" target="_blank"><img class="pwSlideFilter" src="{image,288,198}" />
							<h1>{title,36}</h1></a>
                        </div>
                        </loop>
					<ul id="SwitchNav"></ul>
					<div class="pwSlide-bg"></div>
				</div>
				<div class="c"></div>
				<script type="text/javascript" src="js/sliderplayer.js"></script>
				<script type="text/javascript">pwSliderPlayers("pwSlidePlayer");</script>',
		'parsecode' => '<div id="pwSlidePlayer" class="pwSlide cc">
<!--#
	$tmpCount=0;
#-->
EOT;
$pwresult = pwTplGetData(\'women_播放器\',\'播放器\');
foreach($pwresult as $key=>$val){print <<<EOT
<!--#
	$tmpStyle = $tmpCount ? \'style="display:none;"\' : \'\';
	$tmpCount++;
#-->
                        <div id="Switch_$key" $tmpStyle>
                            <a href="$val[url]" target="_blank"><img class="pwSlideFilter" src="$val[image]" />
							<h1>$val[title]</h1></a>
                        </div>
EOT;
}print <<<EOT
					<ul id="SwitchNav"></ul>
					<div class="pwSlide-bg"></div>
				</div>
				<div class="c"></div>
				<script type="text/javascript" src="js/sliderplayer.js"></script>
				<script type="text/javascript">pwSliderPlayers("pwSlidePlayer");</script>',
		'ifloop' => '0',
		'loops' => '',
		'descrip' => '',
	),
	'50' => array(
		'id' => '67',
		'name' => 'women_焦点摘要',
		'tplid' => '2',
		'tagcode' => '<list action="subject" num="3" title="帖子及摘要" />
<loop>
<h4><a href="{url}" target="_blank">{title,25}</a></h4>
<p>{descrip,40}</p>
<ul class="cc area-list-tree">
{tagrelate}
</ul>
</loop>',
		'parsecode' => '
EOT;
$pwresult = pwTplGetData(\'women_焦点摘要\',\'帖子及摘要\');
print <<<EOT


EOT;
foreach($pwresult as $key=>$val){print <<<EOT

<h4><a href="$val[url]" target="_blank">$val[title]</a></h4>
<p>$val[descrip]</p>
<ul class="cc area-list-tree">

EOT;
foreach($val[tagrelate] as $key_1=>$val_1){print <<<EOT
<li><a href="$val_1[url]" target="_blank">$val_1[title]</a></li>
EOT;
}print <<<EOT

</ul>

EOT;
}print <<<EOT
',
		'ifloop' => '0',
		'loops' => '',
		'descrip' => '',
	),
	'51' => array(
		'id' => '68',
		'name' => 'women_焦点图片摘要',
		'tplid' => '5',
		'tagcode' => '<list action="image" num="3" title="图文模块" />
<loop>
<a href="{url}" target="_blank"><img src="{image,100,100}" class="fl" /></a>
<h4><a href="{url}" target="_blank">{title,32}</a></h4>
<p>{descrip,50}</p>
<div class="c"></div>
</loop>',
		'parsecode' => '
EOT;
$pwresult = pwTplGetData(\'women_焦点图片摘要\',\'图文模块\');
foreach($pwresult as $key=>$val){print <<<EOT
<a href="$val[url]" target="_blank"><img src="$val[image]" class="fl" /></a>
<h4><a href="$val[url]" target="_blank">$val[title]</a></h4>
<p>$val[descrip]</p>
<div class="c"></div>
EOT;
}print <<<EOT
',
		'ifloop' => '0',
		'loops' => '',
		'descrip' => '',
	),
	'52' => array(
		'id' => '69',
		'name' => 'women_本版热门',
		'tplid' => '13',
		'tagcode' => '<list action="subject" num="12" title="帖子列表" />
<ul>
<loop>
<li><span><a href="{forumurl}" target="_blank">[{forumname}]</a></span><a href="{url}"  target="_blank">{title,32}</a></li>
</loop>
</ul>',
		'parsecode' => '
EOT;
$pwresult = pwTplGetData(\'women_本版热门\',\'帖子列表\');
print <<<EOT
<ul>
EOT;
foreach($pwresult as $key=>$val){print <<<EOT
<li><span><a href="$val[forumurl]" target="_blank">[$val[forumname]]</a></span><a href="$val[url]"  target="_blank">$val[title]</a></li>
EOT;
}print <<<EOT
</ul>',
		'ifloop' => '0',
		'loops' => '',
		'descrip' => '',
	),
	'53' => array(
		'id' => '70',
		'name' => 'women_页面中部焦点',
		'tplid' => '13',
		'tagcode' => '<list action="subject" num="12" title="帖子列表" />
<ul>
<loop>
<li><span><a href="{forumurl}" target="_blank">[{forumname}]</a></span><a href="{url}"  target="_blank">{title,32}</a></li>
</loop>
</ul>',
		'parsecode' => '
EOT;
$pwresult = pwTplGetData(\'women_页面中部焦点\',\'帖子列表\');
print <<<EOT
<ul>
EOT;
foreach($pwresult as $key=>$val){print <<<EOT
<li><span><a href="$val[forumurl]" target="_blank">[$val[forumname]]</a></span><a href="$val[url]"  target="_blank">$val[title]</a></li>
EOT;
}print <<<EOT
</ul>',
		'ifloop' => '0',
		'loops' => '',
		'descrip' => '',
	),
	'54' => array(
		'id' => '71',
		'name' => 'women_中部焦点摘要图片',
		'tplid' => '5',
		'tagcode' => '<list action="image" num="3" title="图文模块" />
<loop>
<a href="{url}" target="_blank"><img src="{image,100,100}" class="fl" /></a>
<h4><a href="{url}" target="_blank">{title,32}</a></h4>
<p>{descrip,50}</p>
<div class="c"></div>
</loop>',
		'parsecode' => '
EOT;
$pwresult = pwTplGetData(\'women_中部焦点摘要图片\',\'图文模块\');
foreach($pwresult as $key=>$val){print <<<EOT
<a href="$val[url]" target="_blank"><img src="$val[image]" class="fl" /></a>
<h4><a href="$val[url]" target="_blank">$val[title]</a></h4>
<p>$val[descrip]</p>
<div class="c"></div>
EOT;
}print <<<EOT
',
		'ifloop' => '0',
		'loops' => '',
		'descrip' => '',
	),
	'55' => array(
		'id' => '72',
		'name' => 'women_中部焦点_有作者',
		'tplid' => '14',
		'tagcode' => '<list action="subject" num="12" title="帖子列表" />
<ul>
<loop>
<li><span class="fr"><a href="u.php?username={author}" target="_blank">{author}</a></span><a href="{url}"  target="_blank">{title,32}</a></li>
</loop>
</ul>',
		'parsecode' => '
EOT;
$pwresult = pwTplGetData(\'women_中部焦点_有作者\',\'帖子列表\');
print <<<EOT
<ul>
EOT;
foreach($pwresult as $key=>$val){print <<<EOT
<li><span class="fr"><a href="u.php?username=$val[author]" target="_blank">$val[author]</a></span><a href="$val[url]"  target="_blank">$val[title]</a></li>
EOT;
}print <<<EOT
</ul>',
		'ifloop' => '0',
		'loops' => '',
		'descrip' => '',
	),
	'56' => array(
		'id' => '73',
		'name' => 'women_频道页循环',
		'tplid' => '15',
		'tagcode' => '<list action="image" num="2" title="图片模块" />
<table width="100%">
<tr>
<th>
<loop>
<a href="{url}" target="_blank"><img src="{image,100,100}" class="fl" /></a>
<h4><a href="{url}" target="_blank">{title,32}</a></h4>
<p>{descrip,50}</p>
<div class="c"></div>
</loop>
</th>
<td>
<list action="subject" num="10" title="帖子排行模块" />
<ul>
<loop>
<li><a href="u.php?username={author}" class="fr">{author}</a><a href="{url}" title="{title}" target="_blank">{title,30}</a></li>
</loop>
</ul>
</td>
</tr></table>',
		'parsecode' => '
EOT;
$pwresult = pwTplGetData(\'women_频道页循环\',\'图片模块\',$loopid);
print <<<EOT
<table width="100%">
<tr>
<th>
EOT;
foreach($pwresult as $key=>$val){print <<<EOT
<a href="$val[url]" target="_blank"><img src="$val[image]" class="fl" /></a>
<h4><a href="$val[url]" target="_blank">$val[title]</a></h4>
<p>$val[descrip]</p>
<div class="c"></div>
EOT;
}print <<<EOT
</th>
<td>
EOT;
$pwresult = pwTplGetData(\'women_频道页循环\',\'帖子排行模块\',$loopid);
print <<<EOT
<ul>
EOT;
foreach($pwresult as $key=>$val){print <<<EOT
<li><a href="u.php?username=$val[author]" class="fr">$val[author]</a><a href="$val[url]" title="$val[title]" target="_blank">$val[title]</a></li>
EOT;
}print <<<EOT
</ul>
</td>
</tr></table>',
		'ifloop' => '1',
		'loops' => '',
		'descrip' => '',
	),
	'57' => array(
		'id' => '83',
		'name' => '频道页循环',
		'tplid' => '15',
		'tagcode' => '<list action="image" num="2" title="图片模块" />
<table width="100%">
<tr>
<th>
<loop>
<a href="{url}" target="_blank"><img src="{image,100,100}" class="fl" /></a>
<h4><a href="{url}" target="_blank">{title,32}</a></h4>
<p>{descrip,50}</p>
<div class="c"></div>
</loop>
</th>
<td>
<list action="subject" num="10" title="帖子排行模块" />
<ul>
<loop>
<li><a href="u.php?username={author}" class="fr">{author}</a><a href="{url}" title="{title}" target="_blank">{title,30}</a></li>
</loop>
</ul>
</td>
</tr></table>',
		'parsecode' => '
EOT;
$pwresult = pwTplGetData(\'频道页循环\',\'图片模块\',$loopid);
print <<<EOT
<table width="100%">
<tr>
<th>
EOT;
foreach($pwresult as $key=>$val){print <<<EOT
<a href="$val[url]" target="_blank"><img src="$val[image]" class="fl" /></a>
<h4><a href="$val[url]" target="_blank">$val[title]</a></h4>
<p>$val[descrip]</p>
<div class="c"></div>
EOT;
}print <<<EOT
</th>
<td>
EOT;
$pwresult = pwTplGetData(\'频道页循环\',\'帖子排行模块\',$loopid);
print <<<EOT
<ul>
EOT;
foreach($pwresult as $key=>$val){print <<<EOT
<li><a href="u.php?username=$val[author]" class="fr">$val[author]</a><a href="$val[url]" title="$val[title]" target="_blank">$val[title]</a></li>
EOT;
}print <<<EOT
</ul>
</td>
</tr></table>',
		'ifloop' => '1',
		'loops' => '',
		'descrip' => '',
	),
	'58' => array(
		'id' => '84',
		'name' => 'home2_首页焦点摘要',
		'tplid' => '2',
		'tagcode' => '<list action="subject" num="3" title="帖子及摘要" />
<loop>
<h4><a href="{url}" target="_blank">{title,25}</a></h4>
<p>{descrip,40}</p>
<ul class="cc area-list-tree">
{tagrelate}
</ul>
</loop>',
		'parsecode' => '
EOT;
$pwresult = pwTplGetData(\'home2_首页焦点摘要\',\'帖子及摘要\');
print <<<EOT


EOT;
foreach($pwresult as $key=>$val){print <<<EOT

<h4><a href="$val[url]" target="_blank">$val[title]</a></h4>
<p>$val[descrip]</p>
<ul class="cc area-list-tree">

EOT;
foreach($val[tagrelate] as $key_1=>$val_1){print <<<EOT
<li><a href="$val_1[url]" target="_blank">$val_1[title]</a></li>
EOT;
}print <<<EOT

</ul>

EOT;
}print <<<EOT
',
		'ifloop' => '0',
		'loops' => '',
		'descrip' => '',
	),
	'59' => array(
		'id' => '85',
		'name' => 'home2_首页播放器',
		'tplid' => '11',
		'tagcode' => '<div id="pwSlidePlayer" class="pwSlide cc">
<!--#
	$tmpCount=0;
#-->
					<list action="image" num="6" title="播放器" />
					<loop>
<!--#
	$tmpStyle = $tmpCount ? \'style="display:none;"\' : \'\';
	$tmpCount++;
#-->
                        <div id="Switch_$key" $tmpStyle>
                            <a href="{url}" target="_blank"><img class="pwSlideFilter" src="{image,288,198}" />
							<h1>{title,36}</h1></a>
                        </div>
                        </loop>
					<ul id="SwitchNav"></ul>
					<div class="pwSlide-bg"></div>
				</div>
				<div class="c"></div>
				<script type="text/javascript" src="js/sliderplayer.js"></script>
				<script type="text/javascript">pwSliderPlayers("pwSlidePlayer");</script>',
		'parsecode' => '<div id="pwSlidePlayer" class="pwSlide cc">
<!--#
	$tmpCount=0;
#-->
EOT;
$pwresult = pwTplGetData(\'home2_首页播放器\',\'播放器\');
foreach($pwresult as $key=>$val){print <<<EOT
<!--#
	$tmpStyle = $tmpCount ? \'style="display:none;"\' : \'\';
	$tmpCount++;
#-->
                        <div id="Switch_$key" $tmpStyle>
                            <a href="$val[url]" target="_blank"><img class="pwSlideFilter" src="$val[image]" />
							<h1>$val[title]</h1></a>
                        </div>
EOT;
}print <<<EOT
					<ul id="SwitchNav"></ul>
					<div class="pwSlide-bg"></div>
				</div>
				<div class="c"></div>
				<script type="text/javascript" src="js/sliderplayer.js"></script>
				<script type="text/javascript">pwSliderPlayers("pwSlidePlayer");</script>',
		'ifloop' => '0',
		'loops' => '',
		'descrip' => '',
	),
	'60' => array(
		'id' => '88',
		'name' => 'home2_首页焦点附说明',
		'tplid' => '17',
		'tagcode' => '<list action="subject" num="10" title="帖子列表" />
<ul>
<loop>
<li><a href="{forumurl}"><span>[{forumname}]</span></a><a href="{url}" target="_blank">{title,28}</a><span>&nbsp;{descrip,22}</span></li>
</loop>
</ul>',
		'parsecode' => '
EOT;
$pwresult = pwTplGetData(\'home2_首页焦点附说明\',\'帖子列表\');
print <<<EOT
<ul>
EOT;
foreach($pwresult as $key=>$val){print <<<EOT
<li><a href="$val[forumurl]"><span>[$val[forumname]]</span></a><a href="$val[url]">$val[title]</a><span>&nbsp;$val[descrip]</span></li>
EOT;
}print <<<EOT
</ul>',
		'ifloop' => '0',
		'loops' => '',
		'descrip' => '',
	),
	'61' => array(
		'id' => '89',
		'name' => 'home2_热门话题',
		'tplid' => '4',
		'tagcode' => '<list action="subject" num="8" title="帖子列表" />
<ul>
<loop>
<li><a href="{url}" target="_blank">{title,32}</a></li>
</loop>
</ul>',
		'parsecode' => '
EOT;
$pwresult = pwTplGetData(\'home2_热门话题\',\'帖子列表\');
print <<<EOT
<ul>
EOT;
foreach($pwresult as $key=>$val){print <<<EOT
<li><a href="$val[url]" target="_blank">$val[title]</a></li>
EOT;
}print <<<EOT
</ul>',
		'ifloop' => '0',
		'loops' => '',
		'descrip' => '',
	),
	'62' => array(
		'id' => '90',
		'name' => 'home2_中部图片',
		'tplid' => '7',
		'tagcode' => '<list action="image" num="6" title="图片模块" />
<loop>
<a href="{url}" target="_blank"><img src="{image,100,100}" class="fl" /><p>{title,8}</p></a>
</loop>',
		'parsecode' => '
EOT;
$pwresult = pwTplGetData(\'home2_中部图片\',\'图片模块\');
foreach($pwresult as $key=>$val){print <<<EOT
<a href="$val[url]" target="_blank"><img src="$val[image]" class="fl" /><p>$val[title]</p></a>
EOT;
}print <<<EOT
',
		'ifloop' => '0',
		'loops' => '',
		'descrip' => '',
	),
	'63' => array(
		'id' => '91',
		'name' => 'home2_中部焦点摘要',
		'tplid' => '2',
		'tagcode' => '<list action="subject" num="3" title="帖子及摘要" />
<loop>
<h4><a href="{url}" target="_blank">{title,25}</a></h4>
<p>{descrip,40}</p>
<ul class="cc area-list-tree">
{tagrelate}
</ul>
</loop>',
		'parsecode' => '
EOT;
$pwresult = pwTplGetData(\'home2_中部焦点摘要\',\'帖子及摘要\');
print <<<EOT


EOT;
foreach($pwresult as $key=>$val){print <<<EOT

<h4><a href="$val[url]" target="_blank">$val[title]</a></h4>
<p>$val[descrip]</p>
<ul class="cc area-list-tree">

EOT;
foreach($val[tagrelate] as $key_1=>$val_1){print <<<EOT
<li><a href="$val_1[url]" target="_blank">$val_1[title]</a></li>
EOT;
}print <<<EOT

</ul>

EOT;
}print <<<EOT
',
		'ifloop' => '0',
		'loops' => '',
		'descrip' => '',
	),
	'64' => array(
		'id' => '92',
		'name' => 'home2_中部大图',
		'tplid' => '16',
		'tagcode' => '<list action="image" num="6" title="图片模块" />
<loop>
<a href="{url}" target="_blank"><img src="{image,100,100}" class="fl" /></a>
</loop>',
		'parsecode' => '
EOT;
$pwresult = pwTplGetData(\'home2_中部大图\',\'图片模块\');
foreach($pwresult as $key=>$val){print <<<EOT
<a href="$val[url]" target="_blank"><img src="$val[image]" class="fl" /></a>
EOT;
}print <<<EOT
',
		'ifloop' => '0',
		'loops' => '',
		'descrip' => '',
	),
	'65' => array(
		'id' => '93',
		'name' => 'home2_中部帖子列表',
		'tplid' => '4',
		'tagcode' => '<list action="subject" num="8" title="帖子列表" />
<ul>
<loop>
<li><a href="{url}" target="_blank">{title,32}</a></li>
</loop>
</ul>',
		'parsecode' => '
EOT;
$pwresult = pwTplGetData(\'home2_中部帖子列表\',\'帖子列表\');
print <<<EOT
<ul>
EOT;
foreach($pwresult as $key=>$val){print <<<EOT
<li><a href="$val[url]" target="_blank">$val[title]</a></li>
EOT;
}print <<<EOT
</ul>',
		'ifloop' => '0',
		'loops' => '',
		'descrip' => '',
	),
	'66' => array(
		'id' => '94',
		'name' => 'home2_首页中部热门',
		'tplid' => '4',
		'tagcode' => '<list action="subject" num="8" title="帖子列表" />
<ul>
<loop>
<li><a href="{url}" target="_blank">{title,32}</a></li>
</loop>
</ul>',
		'parsecode' => '
EOT;
$pwresult = pwTplGetData(\'home2_首页中部热门\',\'帖子列表\');
print <<<EOT
<ul>
EOT;
foreach($pwresult as $key=>$val){print <<<EOT
<li><a href="$val[url]" target="_blank">$val[title]</a></li>
EOT;
}print <<<EOT
</ul>',
		'ifloop' => '0',
		'loops' => '',
		'descrip' => '',
	),
	'67' => array(
		'id' => '95',
		'name' => 'home2_首页循环版块',
		'tplid' => '4',
		'tagcode' => '<list action="subject" num="8" title="帖子列表" />
<ul>
<loop>
<li><a href="{url}" target="_blank">{title,32}</a></li>
</loop>
</ul>',
		'parsecode' => '
EOT;
$pwresult = pwTplGetData(\'home2_首页循环版块\',\'帖子列表\',$loopid);
print <<<EOT
<ul>
EOT;
foreach($pwresult as $key=>$val){print <<<EOT
<li><a href="$val[url]" target="_blank">$val[title]</a></li>
EOT;
}print <<<EOT
</ul>',
		'ifloop' => '1',
		'loops' => array(
			'0' => '18',
			'1' => '39',
		),
		'descrip' => '',
	),
	'68' => array(
		'id' => '96',
		'name' => 'home1_首页焦点摘要',
		'tplid' => '2',
		'tagcode' => '<list action="subject" num="3" title="帖子及摘要" />
<loop>
<h4><a href="{url}" target="_blank">{title,25}</a></h4>
<p>{descrip,40}</p>
<ul class="cc area-list-tree">
{tagrelate}
</ul>
</loop>',
		'parsecode' => '
EOT;
$pwresult = pwTplGetData(\'home1_首页焦点摘要\',\'帖子及摘要\');
print <<<EOT


EOT;
foreach($pwresult as $key=>$val){print <<<EOT

<h4><a href="$val[url]" target="_blank">$val[title]</a></h4>
<p>$val[descrip]</p>
<ul class="cc area-list-tree">

EOT;
foreach($val[tagrelate] as $key_1=>$val_1){print <<<EOT
<li><a href="$val_1[url]" target="_blank">$val_1[title]</a></li>
EOT;
}print <<<EOT

</ul>

EOT;
}print <<<EOT
',
		'ifloop' => '0',
		'loops' => '',
		'descrip' => '',
	),
	'69' => array(
		'id' => '98',
		'name' => 'home1_首页头部左侧焦点',
		'tplid' => '18',
		'tagcode' => '<list action="image" num="3" title="图片模块" />
<loop>
<a href="{url}" target="_blank"><img src="{image,100,100}" class="fl" /></a>
</loop>
<div class="c"></div>
<list action="subject" num="7" title="帖子模块" />
<ul>
<loop>
<li><a href="{url}" target="_blank">{title,36}</a></li>
</loop>
</ul>',
		'parsecode' => '
EOT;
$pwresult = pwTplGetData(\'home1_首页头部左侧焦点\',\'图片模块\');
foreach($pwresult as $key=>$val){print <<<EOT
<a href="$val[url]" target="_blank"><img src="$val[image]" class="fl" /></a>
EOT;
}print <<<EOT
<div class="c"></div>
EOT;
$pwresult = pwTplGetData(\'home1_首页头部左侧焦点\',\'帖子模块\');
print <<<EOT
<ul>
EOT;
foreach($pwresult as $key=>$val){print <<<EOT
<li><a href="$val[url]">$val[title]</a></li>
EOT;
}print <<<EOT
</ul>',
		'ifloop' => '0',
		'loops' => '',
		'descrip' => '',
	),
	'70' => array(
		'id' => '99',
		'name' => 'home1_首页播放器',
		'tplid' => '11',
		'tagcode' => '<div id="pwSlidePlayer" class="pwSlide cc">
<!--#
	$tmpCount=0;
#-->
					<list action="image" num="6" title="播放器" />
					<loop>
<!--#
	$tmpStyle = $tmpCount ? \'style="display:none;"\' : \'\';
	$tmpCount++;
#-->
                        <div id="Switch_$key" $tmpStyle>
                            <a href="{url}" target="_blank"><img class="pwSlideFilter" src="{image,288,198}" />
							<h1>{title,36}</h1></a>
                        </div>
                        </loop>
					<ul id="SwitchNav"></ul>
					<div class="pwSlide-bg"></div>
				</div>
				<div class="c"></div>
				<script type="text/javascript" src="js/sliderplayer.js"></script>
				<script type="text/javascript">pwSliderPlayers("pwSlidePlayer");</script>',
		'parsecode' => '<div id="pwSlidePlayer" class="pwSlide cc">
<!--#
	$tmpCount=0;
#-->
EOT;
$pwresult = pwTplGetData(\'home1_首页播放器\',\'播放器\');
foreach($pwresult as $key=>$val){print <<<EOT
<!--#
	$tmpStyle = $tmpCount ? \'style="display:none;"\' : \'\';
	$tmpCount++;
#-->
                        <div id="Switch_$key" $tmpStyle>
                            <a href="$val[url]" target="_blank"><img class="pwSlideFilter" src="$val[image]" />
							<h1>$val[title]</h1></a>
                        </div>
EOT;
}print <<<EOT
					<ul id="SwitchNav"></ul>
					<div class="pwSlide-bg"></div>
				</div>
				<div class="c"></div>
				<script type="text/javascript" src="js/sliderplayer.js"></script>
				<script type="text/javascript">pwSliderPlayers("pwSlidePlayer");</script>',
		'ifloop' => '0',
		'loops' => '',
		'descrip' => '',
	),
	'71' => array(
		'id' => '100',
		'name' => 'home1_首页头部右侧焦点',
		'tplid' => '4',
		'tagcode' => '<list action="subject" num="8" title="帖子列表" />
<ul>
<loop>
<li><a href="{url}" target="_blank">{title,32}</a></li>
</loop>
</ul>',
		'parsecode' => '
EOT;
$pwresult = pwTplGetData(\'home1_首页头部右侧焦点\',\'帖子列表\');
print <<<EOT
<ul>
EOT;
foreach($pwresult as $key=>$val){print <<<EOT
<li><a href="$val[url]" target="_blank">$val[title]</a></li>
EOT;
}print <<<EOT
</ul>',
		'ifloop' => '0',
		'loops' => '',
		'descrip' => '',
	),
	'72' => array(
		'id' => '101',
		'name' => 'home1_首页中部帖子列表',
		'tplid' => '4',
		'tagcode' => '<list action="subject" num="8" title="帖子列表" />
<ul>
<loop>
<li><a href="{url}" target="_blank">{title,32}</a></li>
</loop>
</ul>',
		'parsecode' => '
EOT;
$pwresult = pwTplGetData(\'home1_首页中部帖子列表\',\'帖子列表\');
print <<<EOT
<ul>
EOT;
foreach($pwresult as $key=>$val){print <<<EOT
<li><a href="$val[url]" target="_blank">$val[title]</a></li>
EOT;
}print <<<EOT
</ul>',
		'ifloop' => '0',
		'loops' => '',
		'descrip' => '',
	),
	'73' => array(
		'id' => '103',
		'name' => 'home1_首页中部右侧',
		'tplid' => '1',
		'tagcode' => '<list action="image" num="1" title="图片模块" />
<loop>
<a href="{url}" target="_blank"><img src="{image,100,100}" class="fl" /></a>
</loop>
<list action="subject" num="10" title="帖子排行模块" />
<ul>
<loop>
<li><a href="{url}" title="{title}" target="_blank">{title,30}</a></li>
</loop>
</ul>',
		'parsecode' => '
EOT;
$pwresult = pwTplGetData(\'home1_首页中部右侧\',\'图片模块\');
foreach($pwresult as $key=>$val){print <<<EOT
<a href="$val[url]" target="_blank"><img src="$val[image]" class="fl" /></a>
EOT;
}$pwresult = pwTplGetData(\'home1_首页中部右侧\',\'帖子排行模块\');
print <<<EOT
<ul>
EOT;
foreach($pwresult as $key=>$val){print <<<EOT
<li><a href="$val[url]" title="$val[title]" target="_blank">$val[title]</a></li>
EOT;
}print <<<EOT
</ul>',
		'ifloop' => '0',
		'loops' => '',
		'descrip' => '',
	),
	'74' => array(
		'id' => '105',
		'name' => 'home1_首页中部标签',
		'tplid' => '19',
		'tagcode' => '<list action="tag" num="10" title="标签模块" />
<loop>
<a href="{url}" target="_blank">{title}</a>
</loop>',
		'parsecode' => '
EOT;
$pwresult = pwTplGetData(\'home1_首页中部标签\',\'标签模块\');
foreach($pwresult as $key=>$val){print <<<EOT
<a href="$val[url]" target="_blank">$val[title]</a>
EOT;
}print <<<EOT
',
		'ifloop' => '0',
		'loops' => '',
		'descrip' => '',
	),
	'75' => array(
		'id' => '106',
		'name' => 'home1_首页中部图片',
		'tplid' => '7',
		'tagcode' => '<list action="image" num="6" title="图片模块" />
<loop>
<a href="{url}" target="_blank"><img src="{image,100,100}" class="fl" /><p>{title,8}</p></a>
</loop>',
		'parsecode' => '
EOT;
$pwresult = pwTplGetData(\'home1_首页中部图片\',\'图片模块\');
foreach($pwresult as $key=>$val){print <<<EOT
<a href="$val[url]" target="_blank"><img src="$val[image]" class="fl" /><p>$val[title]</p></a>
EOT;
}print <<<EOT
',
		'ifloop' => '0',
		'loops' => '',
		'descrip' => '',
	),
	'76' => array(
		'id' => '107',
		'name' => 'home1_首页版块排行',
		'tplid' => '8',
		'tagcode' => '<list action="forum" num="12" title="版块模块" />
<ul>
<loop>
<li><span class="fr">{value}</span><a href="{url}" target="_blank">{title}</a></li>
</loop>
</ul>',
		'parsecode' => '
EOT;
$pwresult = pwTplGetData(\'home1_首页版块排行\',\'版块模块\');
print <<<EOT
<ul>
EOT;
foreach($pwresult as $key=>$val){print <<<EOT
<li><span class="fr">$val[value]</span><a href="$val[url]" target="_blank">$val[title]</a></li>
EOT;
}print <<<EOT
</ul>',
		'ifloop' => '0',
		'loops' => '',
		'descrip' => '',
	),
	'77' => array(
		'id' => '108',
		'name' => 'home1_首页版块循环',
		'tplid' => '20',
		'tagcode' => '<list action="image" num="1" title="图片模块" />
<loop>
<a href="{url}" target="_blank"><img src="{image,100,100}" class="fl" /></a>
<h4><a href="{url}" target="_blank">{title,40}</a></h4>
<p>{descrip,60}</p>
</loop>
<div class="c"></div>
<list action="subject" num="7" title="帖子模块" />
<ul>
<loop>
<li><a href="{url}" target="_blank">{title,40}</a></li>
</loop>
</ul>',
		'parsecode' => '
EOT;
$pwresult = pwTplGetData(\'home1_首页版块循环\',\'图片模块\',$loopid);
foreach($pwresult as $key=>$val){print <<<EOT
<a href="$val[url]" target="_blank"><img src="$val[image]" class="fl" /></a>
<h4><a href="$val[url]" target="_blank">$val[title]</a></h4>
<p>$val[descrip]</p>
EOT;
}print <<<EOT
<div class="c"></div>
EOT;
$pwresult = pwTplGetData(\'home1_首页版块循环\',\'帖子模块\',$loopid);
print <<<EOT
<ul>
EOT;
foreach($pwresult as $key=>$val){print <<<EOT
<li><a href="$val[url]" target="_blank">$val[title]</a></li>
EOT;
}print <<<EOT
</ul>',
		'ifloop' => '1',
		'loops' => array(
			'0' => '19',
			'1' => '20',
			'2' => '24',
			'3' => '34',
		),
		'descrip' => '',
	),
	'78' => array(
		'id' => '109',
		'name' => 'category_频道页头部左侧焦点',
		'tplid' => '18',
		'tagcode' => '<list action="image" num="3" title="图片模块" />
<loop>
<a href="{url}" target="_blank"><img src="{image,100,100}" class="fl" /></a>
</loop>
<div class="c"></div>
<list action="subject" num="7" title="帖子模块" />
<ul>
<loop>
<li><a href="{url}" target="_blank">{title,36}</a></li>
</loop>
</ul>',
		'parsecode' => '
EOT;
$pwresult = pwTplGetData(\'category_频道页头部左侧焦点\',\'图片模块\');
foreach($pwresult as $key=>$val){print <<<EOT
<a href="$val[url]" target="_blank"><img src="$val[image]" class="fl" /></a>
EOT;
}print <<<EOT
<div class="c"></div>
EOT;
$pwresult = pwTplGetData(\'category_频道页头部左侧焦点\',\'帖子模块\');
print <<<EOT
<ul>
EOT;
foreach($pwresult as $key=>$val){print <<<EOT
<li><a href="$val[url]" target="_blank">$val[title]</a></li>
EOT;
}print <<<EOT
</ul>',
		'ifloop' => '0',
		'loops' => '',
		'descrip' => '',
	),
	'79' => array(
		'id' => '110',
		'name' => 'category_频道页热门标签',
		'tplid' => '19',
		'tagcode' => '<list action="tag" num="10" title="标签模块" />
<loop>
<a href="{url}" target="_blank">{title}</a>
</loop>',
		'parsecode' => '
EOT;
$pwresult = pwTplGetData(\'category_频道页热门标签\',\'标签模块\');
foreach($pwresult as $key=>$val){print <<<EOT
<a href="$val[url]" target="_blank">$val[title]</a>
EOT;
}print <<<EOT
',
		'ifloop' => '0',
		'loops' => '',
		'descrip' => '',
	),
	'80' => array(
		'id' => '111',
		'name' => 'category_频道页版块排行',
		'tplid' => '8',
		'tagcode' => '<list action="forum" num="12" title="版块模块" />
<ul>
<loop>
<li><span class="fr">{value}</span><a href="{url}" target="_blank">{title}</a></li>
</loop>
</ul>',
		'parsecode' => '
EOT;
$pwresult = pwTplGetData(\'category_频道页版块排行\',\'版块模块\');
print <<<EOT
<ul>
EOT;
foreach($pwresult as $key=>$val){print <<<EOT
<li><span class="fr">$val[value]</span><a href="$val[url]" target="_blank">$val[title]</a></li>
EOT;
}print <<<EOT
</ul>',
		'ifloop' => '0',
		'loops' => '',
		'descrip' => '',
	),
	'81' => array(
		'id' => '112',
		'name' => 'category_频道页焦点摘要',
		'tplid' => '2',
		'tagcode' => '<list action="subject" num="3" title="帖子及摘要" />
<loop>
<h4><a href="{url}" target="_blank">{title,25}</a></h4>
<p>{descrip,40}</p>
<ul class="cc area-list-tree">
{tagrelate}
</ul>
</loop>',
		'parsecode' => '
EOT;
$pwresult = pwTplGetData(\'category_频道页焦点摘要\',\'帖子及摘要\');
print <<<EOT


EOT;
foreach($pwresult as $key=>$val){print <<<EOT

<h4><a href="$val[url]" target="_blank">$val[title]</a></h4>
<p>$val[descrip]</p>
<ul class="cc area-list-tree">

EOT;
foreach($val[tagrelate] as $key_1=>$val_1){print <<<EOT
<li><a href="$val_1[url]" target="_blank">$val_1[title]</a></li>
EOT;
}print <<<EOT

</ul>

EOT;
}print <<<EOT
',
		'ifloop' => '0',
		'loops' => '',
		'descrip' => '',
	),
	'82' => array(
		'id' => '113',
		'name' => 'category_频道页焦点列表',
		'tplid' => '4',
		'tagcode' => '<list action="subject" num="8" title="帖子列表" />
<ul>
<loop>
<li><a href="{url}" target="_blank">{title,32}</a></li>
</loop>
</ul>',
		'parsecode' => '
EOT;
$pwresult = pwTplGetData(\'category_频道页焦点列表\',\'帖子列表\');
print <<<EOT
<ul>
EOT;
foreach($pwresult as $key=>$val){print <<<EOT
<li><a href="$val[url]" target="_blank">$val[title]</a></li>
EOT;
}print <<<EOT
</ul>',
		'ifloop' => '0',
		'loops' => '',
		'descrip' => '',
	),
	'83' => array(
		'id' => '114',
		'name' => 'category_频道页播放器',
		'tplid' => '11',
		'tagcode' => '<div id="pwSlidePlayer" class="pwSlide cc">
<!--#
	$tmpCount=0;
#-->
					<list action="image" num="6" title="播放器" />
					<loop>
<!--#
	$tmpStyle = $tmpCount ? \'style="display:none;"\' : \'\';
	$tmpCount++;
#-->
                        <div id="Switch_$key" $tmpStyle>
                            <a href="{url}" target="_blank"><img class="pwSlideFilter" src="{image,288,198}" />
							<h1>{title,36}</h1></a>
                        </div>
                        </loop>
					<ul id="SwitchNav"></ul>
					<div class="pwSlide-bg"></div>
				</div>
				<div class="c"></div>
				<script type="text/javascript" src="js/sliderplayer.js"></script>
				<script type="text/javascript">pwSliderPlayers("pwSlidePlayer");</script>',
		'parsecode' => '<div id="pwSlidePlayer" class="pwSlide cc">
<!--#
	$tmpCount=0;
#-->
EOT;
$pwresult = pwTplGetData(\'category_频道页播放器\',\'播放器\');
foreach($pwresult as $key=>$val){print <<<EOT
<!--#
	$tmpStyle = $tmpCount ? \'style="display:none;"\' : \'\';
	$tmpCount++;
#-->
                        <div id="Switch_$key" $tmpStyle>
                            <a href="$val[url]" target="_blank"><img class="pwSlideFilter" src="$val[image]" />
							<h1>$val[title]</h1></a>
                        </div>
EOT;
}print <<<EOT
					<ul id="SwitchNav"></ul>
					<div class="pwSlide-bg"></div>
				</div>
				<div class="c"></div>
				<script type="text/javascript" src="js/sliderplayer.js"></script>
				<script type="text/javascript">pwSliderPlayers("pwSlidePlayer");</script>',
		'ifloop' => '0',
		'loops' => '',
		'descrip' => '',
	),
	'84' => array(
		'id' => '115',
		'name' => 'category_频道页中部焦点',
		'tplid' => '13',
		'tagcode' => '<list action="subject" num="12" title="帖子列表" />
<ul>
<loop>
<li><span><a href="{forumurl}" target="_blank">[{forumname}]</a></span><a href="{url}"  target="_blank">{title,32}</a></li>
</loop>
</ul>',
		'parsecode' => '
EOT;
$pwresult = pwTplGetData(\'category_频道页中部焦点\',\'帖子列表\');
print <<<EOT
<ul>
EOT;
foreach($pwresult as $key=>$val){print <<<EOT
<li><span><a href="$val[forumurl]" target="_blank">[$val[forumname]]</a></span><a href="$val[url]"  target="_blank">$val[title]</a></li>
EOT;
}print <<<EOT
</ul>',
		'ifloop' => '0',
		'loops' => '',
		'descrip' => '',
	),
	'85' => array(
		'id' => '116',
		'name' => 'category_频道页中部右侧',
		'tplid' => '1',
		'tagcode' => '<list action="image" num="1" title="图片模块" />
<loop>
<a href="{url}" target="_blank"><img src="{image,100,100}" class="fl" /></a>
</loop>
<list action="subject" num="10" title="帖子排行模块" />
<ul>
<loop>
<li><a href="{url}" title="{title}" target="_blank">{title,30}</a></li>
</loop>
</ul>',
		'parsecode' => '
EOT;
$pwresult = pwTplGetData(\'category_频道页中部右侧\',\'图片模块\');
foreach($pwresult as $key=>$val){print <<<EOT
<a href="$val[url]" target="_blank"><img src="$val[image]" class="fl" /></a>
EOT;
}$pwresult = pwTplGetData(\'category_频道页中部右侧\',\'帖子排行模块\');
print <<<EOT
<ul>
EOT;
foreach($pwresult as $key=>$val){print <<<EOT
<li><a href="$val[url]" title="$val[title]" target="_blank">$val[title]</a></li>
EOT;
}print <<<EOT
</ul>',
		'ifloop' => '0',
		'loops' => '',
		'descrip' => '',
	),
	'86' => array(
		'id' => '117',
		'name' => 'category_版块中部图片',
		'tplid' => '7',
		'tagcode' => '<list action="image" num="6" title="图片模块" />
<loop>
<a href="{url}" target="_blank"><img src="{image,100,100}" class="fl" /><p>{title,8}</p></a>
</loop>',
		'parsecode' => '
EOT;
$pwresult = pwTplGetData(\'category_版块中部图片\',\'图片模块\');
foreach($pwresult as $key=>$val){print <<<EOT
<a href="$val[url]" target="_blank"><img src="$val[image]" class="fl" /><p>$val[title]</p></a>
EOT;
}print <<<EOT
',
		'ifloop' => '0',
		'loops' => '',
		'descrip' => '',
	),
	'87' => array(
		'id' => '118',
		'name' => 'category_频道页版块循环',
		'tplid' => '20',
		'tagcode' => '<list action="image" num="1" title="图片模块" />
<loop>
<a href="{url}" target="_blank"><img src="{image,100,100}" class="fl" /></a>
<h4><a href="{url}" target="_blank">{title,40}</a></h4>
<p>{descrip,60}</p>
</loop>
<div class="c"></div>
<list action="subject" num="7" title="帖子模块" />
<ul>
<loop>
<li><a href="{url}" target="_blank">{title,40}</a></li>
</loop>
</ul>',
		'parsecode' => '
EOT;
$pwresult = pwTplGetData(\'category_频道页版块循环\',\'图片模块\',$loopid);
foreach($pwresult as $key=>$val){print <<<EOT
<a href="$val[url]" target="_blank"><img src="$val[image]" class="fl" /></a>
<h4><a href="$val[url]" target="_blank">$val[title]</a></h4>
<p>$val[descrip]</p>
EOT;
}print <<<EOT
<div class="c"></div>
EOT;
$pwresult = pwTplGetData(\'category_频道页版块循环\',\'帖子模块\',$loopid);
print <<<EOT
<ul>
EOT;
foreach($pwresult as $key=>$val){print <<<EOT
<li><a href="$val[url]" target="_blank">$val[title]</a></li>
EOT;
}print <<<EOT
</ul>',
		'ifloop' => '1',
		'loops' => array(
			'0' => '18',
			'1' => '39',
			'2' => '78',
			'3' => '65',
			'4' => '26',
			'5' => '27',
		),
		'descrip' => '',
	),
);

$pw_invoke = L::loadDB('Invoke', 'area');
$db->query("TRUNCATE TABLE `pw_invoke`");
foreach ($invokes as $key=>$value) {
	$pw_invoke->insertData($value);
}
?>