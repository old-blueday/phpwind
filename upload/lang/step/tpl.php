<?php
!defined('PW_UPLOAD') && exit('Forbidden');
$tpls=array(
	'0' => array(
		'tplid' => '1',
		'type' => 'subject',
		'name' => '帖子列表1',
		'descrip' => '由一张图片和若干帖子列表组成',
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
		'image' => '1.jpg',
	),
	'1' => array(
		'tplid' => '2',
		'type' => 'subject',
		'name' => '帖子列表2',
		'descrip' => '由标题和摘要组成',
		'tagcode' => '<list action="subject" num="3" title="帖子及摘要" />
<loop>
<h4><a href="{url}" target="_blank">{title,25}</a></h4>
<p>{descrip,40}</p>
<ul class="cc area-list-tree">
{tagrelate}
</ul>
</loop>',
		'image' => '2.jpg',
	),
	'2' => array(
		'tplid' => '3',
		'type' => 'subject',
		'name' => '帖子列表3',
		'descrip' => '由若干图片和若干帖子列表组成，图片在帖子列表的左侧',
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
		'image' => '3.jpg',
	),
	'3' => array(
		'tplid' => '4',
		'type' => 'subject',
		'name' => '帖子列表4',
		'descrip' => '只由帖子列表组成',
		'tagcode' => '<list action="subject" num="8" title="帖子列表" />
<ul>
<loop>
<li><a href="{url}" target="_blank">{title,32}</a></li>
</loop>
</ul>',
		'image' => '4.jpg',
	),
	'4' => array(
		'tplid' => '5',
		'type' => 'image',
		'name' => '图文混合',
		'descrip' => '由图片和帖子列表，及摘早组成',
		'tagcode' => '<list action="image" num="3" title="图文模块" />
<loop>
<a href="{url}" target="_blank"><img src="{image,100,100}" class="fl" /></a>
<h4><a href="{url}" target="_blank">{title,32}</a></h4>
<p>{descrip,50}</p>
<div class="c"></div>
</loop>',
		'image' => '5.jpg',
	),
	'5' => array(
		'tplid' => '6',
		'type' => 'subject',
		'name' => '帖子列表6',
		'descrip' => '由若干图片和若干帖子列表组成，图片在帖子列表的左侧,且图片带有标题',
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
		'image' => '6.jpg',
	),
	'6' => array(
		'tplid' => '7',
		'type' => 'image',
		'name' => '最新图片',
		'descrip' => '包括图片，和图片所在的帖子名称',
		'tagcode' => '<list action="image" num="6" title="图片模块" />
<loop>
<a href="{url}" target="_blank"><img src="{image,100,100}" class="fl" /><p>{title,8}</p></a>
</loop>',
		'image' => '7.jpg',
	),
	'7' => array(
		'tplid' => '8',
		'type' => 'forum',
		'name' => '版块排行',
		'descrip' => '版块排行列表',
		'tagcode' => '<list action="forum" num="12" title="版块模块" />
<ul>
<loop>
<li><span class="fr">{value}</span><a href="{url}" target="_blank">{title}</a></li>
</loop>
</ul>',
		'image' => '8.jpg',
	),
	'8' => array(
		'tplid' => '9',
		'type' => 'user',
		'name' => '用户排行',
		'descrip' => '版块排行列表',
		'tagcode' => '<list action="user" num="12" title="用户模块" />
<ul>
<loop>
<li><span>{value}</span><img src="{image,40,40}" align="absmiddle" /> <a href="{url}" target="_blank">{title}</a></li>
</loop>
</ul>',
		'image' => '9.jpg',
	),
	'9' => array(
		'tplid' => '11',
		'type' => 'player',
		'name' => '播放器1',
		'descrip' => '播放器',
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
		'image' => '10.jpg',
	),
	'10' => array(
		'tplid' => '12',
		'type' => 'user',
		'name' => '用户排行2',
		'descrip' => '不包括头像',
		'tagcode' => '<list action="user" num="12" title="用户模块" />
<ul>
<loop>
<li><span class="fr">{value}</span><a href="{url}" target="_blank">{title}</a></li>
</loop>
</ul>',
		'image' => '11.jpg',
	),
	'11' => array(
		'tplid' => '13',
		'type' => 'subject',
		'name' => '帖子列表5',
		'descrip' => '包括帖子所在的版块',
		'tagcode' => '<list action="subject" num="12" title="帖子列表" />
<ul>
<loop>
<li><span><a href="{forumurl}" target="_blank">[{forumname}]</a></span><a href="{url}"  target="_blank">{title,32}</a></li>
</loop>
</ul>',
		'image' => '12.jpg',
	),
	'12' => array(
		'tplid' => '14',
		'type' => 'subject',
		'name' => '帖子列表7',
		'descrip' => '包括帖子标题和作者',
		'tagcode' => '<list action="subject" num="12" title="帖子列表" />
<ul>
<loop>
<li><span class="fr"><a href="u.php?username={author}" target="_blank">{author}</a></span><a href="{url}"  target="_blank">{title,32}</a></li>
</loop>
</ul>',
		'image' => '13.jpg',
	),
	'13' => array(
		'tplid' => '15',
		'type' => 'subject',
		'name' => '帖子图片复合',
		'descrip' => '由一个图片有标题模块和帖子模块组成',
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
		'image' => '14.jpg',
	),
	'14' => array(
		'tplid' => '16',
		'type' => 'image',
		'name' => '图片列表',
		'descrip' => '只包括图片',
		'tagcode' => '<list action="image" num="6" title="图片模块" />
<loop>
<a href="{url}" target="_blank"><img src="{image,100,100}" class="fl" /></a>
</loop>',
		'image' => '15.jpg',
	),
	'15' => array(
		'tplid' => '17',
		'type' => 'subject',
		'name' => '帖子列表及说明',
		'descrip' => '包括版块名称帖子及摘要说明',
		'tagcode' => '<list action="subject" num="10" title="帖子列表" />
<ul>
<loop>
<li><a href="{forumurl}"><span>[{forumname}]</span></a><a href="{url}" target="_blank">{title,28}</a><span>&nbsp;{descrip,22}</span></li>
</loop>
</ul>',
		'image' => '16.jpg',
	),
	'16' => array(
		'tplid' => '18',
		'type' => 'subject',
		'name' => '帖子及图片复合',
		'descrip' => '由图片模块和帖子模块组成',
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
		'image' => '17.jpg',
	),
	'17' => array(
		'tplid' => '19',
		'type' => 'tag',
		'name' => '标签模块',
		'descrip' => '标签列表',
		'tagcode' => '<list action="tag" num="10" title="标签模块" />
<loop>
<a href="{url}" target="_blank">{title}</a>
</loop>',
		'image' => '18.jpg',
	),
	'18' => array(
		'tplid' => '20',
		'type' => 'subject',
		'name' => '帖子及图片复合2',
		'descrip' => '由图片模块和帖子模块组成',
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
		'image' => '19.jpg',
	),
);
$pw_tpl = L::loadDB('Tpl', 'area');
$db->query("TRUNCATE TABLE `pw_tpl`");
foreach ($tpls as $key=>$value) {
	$pw_tpl->insertData($value);
}
?>