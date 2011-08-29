<?php
!function_exists('adminmsg') && exit('Forbidden');
$basename="$admin_file?adminjob=setbwd";

//引入短消息类

S::gp(array('action','job', 'page'));
$optCates = '';
(!is_numeric($page) || $page<1) && $page = 1;
$db_perpage = 20;
$operate = array(
	0 => getLangInfo('cpmsg','filter_operate_not'),
	1 => getLangInfo('cpmsg','filter_operate_pass'),
	2 => getLangInfo('cpmsg','filter_operate_del'),
	3 => getLangInfo('cpmsg','filter_operate_replace'),
);

$level = array(
	1 => getLangInfo('cpmsg','filter_word_level_forbidden'),
	2 => getLangInfo('cpmsg','filter_word_level_checked'),
	3 => getLangInfo('cpmsg','filter_word_level_replace'),
);

$score = array(1 => 1, 2 => 0.8, 3 => 0.6);
$center_level = array(10 => 1, 8 => 2, 6 => 3);//中心更新下来的词语等级

$updateHost = 'http://app.phpwind.net/pwbbsapi.php?';
//$updateHost = 'http://localhost/www.phpwind.com/pwbbsapi.php?';

if ($admin_gid == 3) {
	!$action && $action = 'scan';
} else {
	!$action && $action = 'check';
}

if ($action == 'setting') {
	if ($admin_gid != 3){
		adminmsg('illegal_request');
	}
	if (empty($job)) {
		S::gp(array('type','keyword','class','show', 'sort', 'importshow', 'success','fail'));
		$type = intval($type);
		$class = (int) $class;

		$style = array(
			1 => 'style=" background:#F00;color: #fff;display:inline;padding:1px;padding:0 5px;"',
			2 => 'style=" background:#ff6600;color: #fff;display:inline;padding:1px;padding:0 5px;"',
			3 => 'style=" background:#44aa00;color: #fff;display:inline;padding:1px;padding:0 5px;"',
		);

		$sqladd = ' WHERE 1 ';
		if($keyword){
			$sqladd .= " AND word LIKE ".S::sqlEscape("%$keyword%");
		}

		if($type){
			$sqladd .= " AND type = " . S::sqlEscape($type);
		}

		switch ($sort) {
			case 'word':
				$ordersql = 'ORDER BY word ASC';
				break;
			case 'type':
				$ordersql = 'ORDER BY type DESC';
				break;
			case 'class':
				$ordersql = 'ORDER BY classid ASC';
				break;
			default:
				$ordersql = 'ORDER BY id DESC';
				break;
		}

		$show_all = $show_center = '';
		if(intval($class) > 0){
			$sqladd .= " AND classid = " . S::sqlEscape($class);
		} elseif ($class == -1) {
			$show_notclass = 'class="current"';
			$sqladd .= " AND classid = 0";
		}

		if (!$show && !$class) {
			$show_all = 'class="current"';
		} elseif ($show == -1) {
			$sqladd .= " AND classid = 0";
			$show_notclass = 'class="current"';
		} elseif (intval($show)) {
			$sqladd .= " AND classid = " . S::sqlEscape($show);
		}

		$sql = "SELECT COUNT(*) AS sum FROM pw_wordfb ".$sqladd;
		$count = $db->get_value($sql);
		$page_count = ceil($count/$db_perpage);
		if ($page > $page_count) $page = $page_count;
		$limit = S::sqlLimit(($page-1)*$db_perpage,$db_perpage);
		$pages = numofpage($count,$page,$page_count, "$basename&action=setting&type=$type&keyword=".rawurlencode($keyword)."&sort=".rawurlencode($sort)."&show=".rawurlencode($show)."&");

		if(!$keyword) $keyword = getLangInfo('cpmsg','filter_keyword');

		$classdb = array();
		$search_class = array();
		$classlist = array();
		$sql = "SELECT * FROM pw_filter_class";
		$query = $db->query($sql);
		while($rt = $db->fetch_array($query)){
			HtmlConvert($rt);
			$search_class[$rt['id']]=$rt;
			$classdb[$rt['id']]=$rt;
			$classlist[$rt['id']]=$rt['title'];
			if ($show == $rt['id']) {
				if($rt['state'] > 0){
					$switch = getLangInfo('cpmsg','filter_class_close');
				} else {
					$switch = getLangInfo('cpmsg','filter_class_open');
				}
			}
		}
		$classlist[0] = getLangInfo('cpmsg','filter_class_nonentity');

		$replacedb = array();
		$sql = "SELECT * FROM pw_wordfb ".$sqladd." $ordersql $limit";

		$query = $db->query($sql);
		while($rt = $db->fetch_array($query)){
			HtmlConvert($rt);
			if (!$rt['type']) $rt['type'] = 3;
			$rt['class'] = $classlist[$rt['classid']];
			$rt['style'] = $style[$rt['type']];
			$rt['level'] = $level[$rt['type']];
			$replacedb[$rt['id']]=$rt;
		}
		include_once PrintEot('filter');exit;
	} elseif ($job == 'add') {
		S::gp(array('step'));
		if ($step == 2) {
			S::gp(array('type','repword','class','newclass'));
			$word = S::getGP('word', 'P');
			$word = trim(str_replace("\r\n",",",$word));
			$word = array_unique(explode(',', $word));
			$strword=S::sqlImplode($word);

			$type = intval($type);
			if(!$word) ajaxmsg('filtermsg_cannot', "$basename&action=setting");

			//查询是否有重复记录
			$sql = "SELECT * FROM pw_wordfb WHERE word IN ($strword)";
			$query = $db->query($sql);
			$wordfb = $db->affected_rows($query);

			//插入新分类
			if ($newclass) {
				$class = newClass($newclass);
			}

			if (!$wordfb) {
				if (!$repword) $repword = '*****';
				//插入敏感词
				insertWord($word, $type, $class, $repword);

				//更新缓存
    			updatecache_w();

				//设置字典文件
				setAllDictionary();

				require_once(R_P.'require/posthost.php');
				$appclient = L::loadClass('AppClient');

				$sitehash = $appclient->getApicode();

				$word = serialize(pwConvert($word,'UTF8',$db_charset));

				//提交到中心词库
				$data = PostHost($updateHost, "m=wordsfb&a=custom&sitehash=$sitehash&type=plus&word=$word", "POST");

				if ($class) {
					//重定向
					adminmsg('operate_success', "$basename&action=setting&show=$class");
				} else {
					//重定向
					adminmsg('operate_success', "$basename&action=setting");
				}
			}
		} else {
		    define('AJAX', 1);

		    $classdb = array();
			$sql = "SELECT * FROM pw_filter_class WHERE state=1";
			$query = $db->query($sql);
			while($rt = $db->fetch_array($query)){
				HtmlConvert($rt);
				$classdb[$rt['id']]=$rt;
			}

		    $ajax_basename = EncodeUrl($basename."&action=setting&job=add");
		    $post_basename = EncodeUrl($basename."&action=setting&job=enforce");
		    include_once PrintEot('filterAjax');
		    ajax_footer();
		}
	} elseif ($job == 'enforce') {
		define('AJAX', 1);
	    S::gp(array('step'));
		if ($step == 2) {
    		$word = S::getGP('word', 'P');
    		$newclass = S::getGP('newclass', 'P');

    		if ($newclass) {
	    		//判断长度
				if (strlen($newclass) > 16) {
					echo getLangInfo('cpmsg','filter_class_len');
					ajax_footer();
					exit;
				}

				//判断是否有重复分类
				$sql = " SELECT id FROM pw_filter_class WHERE title = " . S::sqlEscape($newclass);
				$num = $db->get_one($sql);
				if ($num) {
					echo getLangInfo('cpmsg','filter_class_repeat');
					ajax_footer();
					exit;
				}
    		}

			$word = trim(str_replace("\r\n",",",$word));
			$word = explode(',', $word);
			$strword=S::sqlImplode($word);
			$type = intval($type);
			if(!$word) ajaxmsg('filtermsg_cannot', "$basename&action=setting");

			//查询是否有重复记录
			$sql = "SELECT id,word FROM pw_wordfb WHERE word IN ($strword)";
			$query = $db->query($sql);
			while($rt = $db->fetch_array($query)){
				HtmlConvert($rt);
				$wordfb[$rt['id']] =$rt['word'];
			}

			if ($wordfb) {
				$prompt=S::sqlImplode($wordfb);

				//提示信息
				$L = array(
					'prompt' => $prompt ,
				);

				echo getLangInfo('cpmsg','filter_word_repeat', $L);
			}

			ajax_footer();
		}
	} elseif ($job == 'edit') {
	    S::gp(array('step'));
		if ($step == 2) {
			S::gp(array('id', 'type','repword','class','newclass'));
			$type = intval($type);

			//插入新分类
			if ($newclass) {
				$class = newClass($newclass);
				if(!$class) adminmsg('filter_class_repeat', "$basename&action=setting");
			}

			$wordtime = mktime(0,0,0,date("m"),date("d"),date("Y"));
			$repword || $repword = '*****';
			$value = array(
				'type'	 => $type,
				'wordreplace' => $repword,
				'wordtime' => $wordtime,
				'classid' => $class,
				'custom'   => 1
			);

			$sql = "UPDATE pw_wordfb"
				 . " SET " . S::sqlSingle($value)
				 . "WHERE id = " . S::sqlEscape($id);
			$db->update($sql);

			//更新缓存
			updatecache_w();

			//设置字典文件
			setAllDictionary();

			//重定向
			adminmsg('operate_success', EncodeUrl($basename.'&action=setting&show='.$class));
		} else {
		    define('AJAX', 1);
			S::gp(array('id'));

			$sql = " SELECT * FROM pw_wordfb WHERE id =".S::sqlEscape($id);
			$word = $db->get_one($sql);

			$selected ='';
			if (!$word['type']) $word['type'] = 3;
			foreach ($level as $key => $value)
			{
				if ($word['type'] == $key) {
					$selected .= '<option value="'.$key.'" selected>'.$value.'</option>';
				} else {
					$selected .= '<option value="'.$key.'">'.$value.'</option>';
				}
			}

			$classdb = array();
			$classdb[0] = getLangInfo('cpmsg','filter_class_nonentity');
			$sql = "SELECT * FROM pw_filter_class";
			$query = $db->query($sql);
			while($rt = $db->fetch_array($query)){
				HtmlConvert($rt);
				$classdb[$rt['id']]=$rt['title'];
			}

			$class_selected ='';
			foreach ($classdb as $key => $value)
			{
				if ($word['classid'] == $key) {
					$class_selected .= '<option value="'.$key.'" selected>'.$value.'</option>';
				} else {
					$class_selected .= '<option value="'.$key.'">'.$value.'</option>';
				}
			}

			$ajax_basename = EncodeUrl($basename."&action=setting&job=edit");
			include_once PrintEot('filterAjax');
			ajax_footer();
		}
	} elseif ($job == 'batchedit') {
	    S::gp(array('step'));
		if ($step == 2) {
			S::gp(array('id', 'type','word','repword','class','newclass'));

			$type = intval($type);

			//插入新分类
			if ($newclass) {
				$class = newClass($newclass);
				if(!$class) adminmsg('filter_class_repeat', "$basename&action=setting");
			}

			$wordtime = mktime(0,0,0,date("m"),date("d"),date("Y"));
			$repword || $repword = '*****';
			$value = array(
				'type'	 => $type,
				'wordreplace' => $repword,
				'wordtime' => $wordtime,
				'classid' => $class,
				'custom'   => 1
			);

			$sql = "UPDATE pw_wordfb"
				 . " SET " . S::sqlSingle($value)
				 . "WHERE id IN (".$id.")";
			$db->update($sql);

			//更新缓存
			updatecache_w();

			//设置字典文件
			setAllDictionary();

			//重定向
			adminmsg('operate_success', "$basename" . "&action=setting&show=".$class);
		} else {
		    define('AJAX', 1);
			S::gp(array('id'));
			if (!$id) adminmsg('operate_error');
			$id = explode(',', $id);

			foreach ($id as $value){
				if($value==""){
					continue;
				}
				$str .= $str ? ', '.S::sqlEscape($value).'' : S::sqlEscape($value);
				$wid .= $wid ? ', '.$value : $value;
			}

			$sql = " SELECT * FROM pw_wordfb WHERE id IN (".$str.")";
			$query = $db->query($sql);
			while($rt = $db->fetch_array($query)){
				$list[] = $rt['word'];
			}

			$classdb = array();
			$sql = "SELECT * FROM pw_filter_class WHERE state=1";
			$query = $db->query($sql);
			while($rt = $db->fetch_array($query)){
				HtmlConvert($rt);
				$classdb[$rt['id']]=$rt;
			}

			$ajax_basename = EncodeUrl($basename."&action=setting&job=batchedit");
			include_once PrintEot('filterAjax');
			ajax_footer();
		}
	}  elseif ($job == 'del') {
		S::gp(array('step'));
		if ($step == 2) {
			S::gp(array('id'));
			if (!$id) adminmsg('operate_error');

			$sql = "SELECT word, custom FROM pw_wordfb WHERE id IN (".$id.")";
			$query = $db->query($sql);
			$word = array();
			while ($rt = $db->fetch_array($query)) {
				if ($rt['custom'] == 1)  {
					$word[] = $rt['word'];
				}
			}
			$word = serialize($word);

			$sql = "DELETE FROM pw_wordfb WHERE id IN (".$id.")";
			$db->update($sql);

			//更新缓存
			updatecache_w();

			//设置字典文件
			setAllDictionary();

			require_once(R_P.'require/posthost.php');
			$appclient = L::loadClass('AppClient');

			$sitehash = $appclient->getApicode();

			$word = serialize(pwConvert(array($word),'UTF8',$db_charset));

			//提交到中心词库
			$data = PostHost($updateHost, "m=wordsfb&a=custom&sitehash=$sitehash&type=subtract&word=$word", "POST");

			//重定向
			adminmsg('operate_success', "$basename&action=setting");
		} else {
		    define('AJAX', 1);
			S::gp(array('id'));
			if (!$id) adminmsg('operate_error');
			$id = explode(',', $id);

			$count = 0;
			foreach ($id as $value){
				if($value==""){
					continue;
				}
				$str .= $str ? ', '.S::sqlEscape($value).'' : S::sqlEscape($value);
				$wid .= $wid ? ', '.$value : $value;
				$count++;
			}

			$sql = " SELECT * FROM pw_wordfb WHERE id IN (".$str.")";
			$query = $db->query($sql);
			while($rt = $db->fetch_array($query)){
				$list[] = $rt['word'];
			}

			$ajax_basename = EncodeUrl($basename."&action=setting&job=del");
			include_once PrintEot('filterAjax');
			ajax_footer();
		}
	} elseif ($job == 'setting') {
		S::gp(array('setting'));

		require_once(R_P.'admin/cache.php');
	    setConfig('db_wordsfb_setting',$setting);
	    updatecache_c();

		//重定向
		adminmsg('operate_success', "$basename&action=scan");
	}
} elseif ($action == 'class') {
	S::gp(array('step'));
	if (empty($job)) {
		$limit = S::sqlLimit(($page-1)*$db_perpage,$db_perpage);

		$sqladd = ' WHERE 1 ';

		//获取总分类数
		$sql = "SELECT COUNT(*) AS sum FROM pw_filter_class ".$sqladd;
		$count = $db->get_value($sql);
		$pages = numofpage($count,$page,ceil($count/$db_perpage), "$basename&action=class");

		$word_count = array();
		//获取各分类敏感词总数
		$sql = "SELECT classid,COUNT(*) AS count FROM pw_wordfb GROUP BY classid";
		$query = $db->query($sql);
		while($rt = $db->fetch_array($query)){
			HtmlConvert($rt);
			$word_count[$rt['classid']] = $rt['count'];
		}

		$classdb = array();
		//读取分类列表
		$sql = "SELECT * FROM pw_filter_class $sqladd $limit";
		$query = $db->query($sql);
		while($rt = $db->fetch_array($query)){
			HtmlConvert($rt);
			$rt['count'] = $word_count[$rt['id']];
			$rt['status'] = $rt['state'] ? getLangInfo('cpmsg','filter_class_show_open') : getLangInfo('cpmsg','filter_class_show_close');
			$rt['state_button'] = $rt['state'] ? getLangInfo('cpmsg','filter_class_show_close') : getLangInfo('cpmsg','filter_class_show_open');
			$classdb[] = $rt;
		}
		include_once PrintEot('filter');exit;
	} elseif ($job == 'add') {//添加分类
		if ($step == 2) {
			S::gp(array('title'));

			//判断长度
			if (strlen($title) > 16) adminmsg('filter_class_len', EncodeUrl($basename.'&action=class'));

			if (!newClass($title)) adminmsg('filter_class_repeat', EncodeUrl($basename.'&action=class'));

			//重定向
			adminmsg('operate_success', EncodeUrl($basename.'&action=class'));
		} else {
			define('AJAX', 1);
	    	$ajax_basename = EncodeUrl($basename."&action=class&job=add");
	    	include_once PrintEot('filterAjax');
	    	ajax_footer();
		}
	} elseif ($job == 'edit') {//修改分类
		if ($step == 2) {
			S::gp(array('id','title'));

			//判断长度
			if (strlen($title) > 16) adminmsg('filter_class_len', EncodeUrl($basename.'&action=class'));

			//判断是否有重复分类
			$sql = " SELECT id FROM pw_filter_class WHERE title = " . S::sqlEscape($title);
			$num = $db->get_one($sql);
			if ($num) adminmsg('filter_class_repeat', EncodeUrl($basename.'&action=class'));

			//更新分类信息
			$sql = "UPDATE pw_filter_class SET title=".S::sqlEscape($title)." WHERE id=".S::sqlEscape($id);
			$db->update($sql);

			//重定向
			adminmsg('operate_success', EncodeUrl($basename.'&action=class'));
		} else {
			define('AJAX', 1);
			S::gp(array('id'));

			//获取分类信息
			$sql = "SELECT id,title FROM pw_filter_class WHERE id=".S::sqlEscape($id);
			$class = $db->get_one($sql);

	    	$ajax_basename = EncodeUrl($basename."&action=class&job=edit");
	    	include_once PrintEot('filterAjax');
	    	ajax_footer();
		}
	} elseif ($job == 'del') {//删除分类
		if ($step == 2) {
			S::gp(array('class'));
			$class = (int) $class;

			delClass($class);

			//更新缓存
    		updatecache_w();

			//设置字典文件
			setAllDictionary();

			//重定向
			adminmsg('operate_success', $basename.'&action=class');exit;
		} else {
			define('AJAX', 1);
			S::gp(array('class'));
			$class = (int) $class;

			if ($class > 0) {
				//获取分类名
				$sql = "SELECT title FROM pw_filter_class WHERE id=".S::sqlEscape($class);
				$title = $db->get_value($sql);

				//获取该分类敏感词总数
				$sql = "SELECT COUNT(*) AS count FROM pw_wordfb WHERE classid=".S::sqlEscape($class);
				$count = $db->get_value($sql);
			} elseif ($class == 0) {
				$title = getLangInfo('cpmsg', 'filter_all_word');

				//获取该分类敏感词总数
				$sql = "SELECT COUNT(*) AS count FROM pw_wordfb";
				$count = $db->get_value($sql);
			} else {
				adminmsg('filter_class_cannot_delete');
			}

	    	$ajax_basename = EncodeUrl($basename."&action=class&job=del");
	    	include_once PrintEot('filterAjax');
	    	ajax_footer();
		}
	} elseif ($job == 'import') {//导入词库
		if ($step == 2) {
			S::gp(array('class', 'newclass'),'P');
			$class = (int) $class;
			$upload = $_FILES['upload'];

			if ($upload['error'] == 4) {
				adminmsg('filter_upload_dict_file', "$basename&action=setting");
			}

			//插入新分类
			if ($newclass) {
				$class = newClass($newclass);
				if(!$class) adminmsg('filter_class_repeat', "$basename&action=setting");
			}

			if (is_array($upload)) {
				$upload_name = $upload['name'];
				$upload_size = $upload['size'];
				$upload = $upload['tmp_name'];
			}

			$basename.="&type=$type";

			if($upload && $upload!='none'){
				require_once(R_P.'require/postfunc.php');
				$attach_ext = strtolower(substr(strrchr($upload_name,'.'),1));
				if(!if_uploaded_file($upload)){
					adminmsg('upload_error', "$basename&action=setting");
				} elseif($attach_ext!='txt'){
					adminmsg('upload_type_error', "$basename&action=setting");
				}
				$source = D_P."data/tmp/word.txt";
				if(postupload($upload,$source)){
					$content = explode("\n",readover($source));
					$wordtime = mktime(0,0,0,date("m"),date("d"),date("Y"));

					$success = 0;
					$fail    = 0;
					foreach($content as $key => $value){
						if($value){
							$word = trim(substr($value, 0, strpos($value,'|')));
							$type = trim(substr(strrchr($value,'|'), 1));
							if (!intval($type)) {
								$fail++;
								continue;
							}

							$id = $db->get_value("SELECT id FROM pw_wordfb WHERE word=".S::sqlEscape($word));

							if(empty($id)){
								$sql  ="INSERT INTO pw_wordfb (word,wordreplace,type,wordtime,classid,custom) VALUES (".S::sqlEscape($word).", '*****', ".S::sqlEscape($type).", ".S::sqlEscape($wordtime).", ".S::sqlEscape($class).", 1)";
								$db->update($sql);
								$success++;
							} else {
								$fail++;
							}
						}
					}

					//更新缓存
					updatecache_w();

					//设置字典文件
					setAllDictionary();

					//重定向
					$jumpurl = "$basename" . "&action=setting&importshow=1&success=".$success."&fail=".$fail;
					$show = <<<EOT
<script language="JavaScript">
	location.href = "$jumpurl";
</script>
EOT;
					echo $show;
				} else{
					adminmsg('upload_error');
				}
				pwCache::deleteData($source);
			}

		} else {
			define('AJAX', 1);
			S::gp(array('class'));
			$class = (int) $class;

			$classdb = array();
			$sql = "SELECT * FROM pw_filter_class WHERE state=1";
			$query = $db->query($sql);
			while($rt = $db->fetch_array($query)){
				HtmlConvert($rt);
				$classdb[$rt['id']]=$rt;
			}

	    	$ajax_basename = EncodeUrl($basename."&action=class&job=import");
	    	include_once PrintEot('filterAjax');
	    	ajax_footer();
		}
	} elseif ($job == 'importshow') {//显示导入结果
		define('AJAX', 1);
		S::gp(array('success', 'fail'));
	    include_once PrintEot('filterAjax');
	    ajax_footer();
	} elseif ($job == 'export') {//导出词库
		if ($step == 2) {
			S::gp(array('class', 'dict_name'));
			$class = (int) $class;

			if (intval($class) > 0) {
				$sql = "SELECT word, type FROM pw_wordfb WHERE classid=".S::sqlEscape($class);
				$query = $db->query($sql);
				while($rt = $db->fetch_array($query)){
					$words .= $rt['word']."|".$rt['type']."\r\n";
				}
			} else {
				$classid = getCloseClass();
				$classid = S::sqlImplode($classid);

				$sql = "SELECT word, type FROM pw_wordfb WHERE classid NOT IN ($classid) ";
				$query = $db->query($sql);
				while($rt = $db->fetch_array($query)){
					$words .= $rt['word']."|".$rt['type']."\r\n";
				}
			}

			$dict_name = $dict_name.'.txt';
			if(!strpos(strtolower($_SERVER["HTTP_USER_AGENT"]),"msie")) {
				$dict_name = pwConvert($dict_name,'UTF8',$db_charset);
			}

			header('Last-Modified: '.gmdate('D, d M Y H:i:s',$timestamp+86400).' GMT');
			header('Cache-control: no-cache');
			header('Content-Encoding: none');
			header('Content-Disposition: attachment; filename="'.$dict_name.'"');
			header('Content-type: txt');
			header('Content-Length: '.strlen($words));
			echo $words;exit;
		} else {
			define('AJAX', 1);
			S::gp(array('class'));
			$class = (int) $class;

			if ($class > 0) {
				//获取分类名
				$sql = "SELECT title FROM pw_filter_class WHERE id=".S::sqlEscape($class);
				$title = $db->get_value($sql);

				//获取该分类敏感词总数
				$sql = "SELECT COUNT(*) AS count FROM pw_wordfb WHERE classid=".S::sqlEscape($class);
				$count = $db->get_value($sql);
			} else {
				$title = getLangInfo('cpmsg','filter_all_word');

				$classid = getCloseClass();
				$classid = S::sqlImplode($classid);

				//获取该分类敏感词总数
				$sql = "SELECT COUNT(*) AS count FROM pw_wordfb WHERE classid NOT IN ($classid) ";
				$count = $db->get_value($sql);

				$job = 'exportall';
			}

	    	$ajax_basename = EncodeUrl($basename."&action=class&job=export");
	    	include_once PrintEot('filterAjax');
	    	ajax_footer();
		}
	} elseif ($job == "switch") {//开启/关闭分类
		if ($step == 2) {
			S::gp(array('class', 'state'));

			if ($class > 0) {
				//更改分类状态
				setClassState($class, $state);

				//重定向
				adminmsg('operate_success', "$basename" . "&action=class");
			}
		} else {
			define('AJAX', 1);
			S::gp(array('class'));
			$class = (int) $class;

			if ($class > 0) {
				//获取分类名
				$sql = "SELECT title,state FROM pw_filter_class WHERE id=".S::sqlEscape($class);
				$filter_class = $db->get_one($sql);
				$title  = $filter_class['title'];
				$state  = $filter_class['state'];
				$state  = $state ? 0 : 1;
				$show   = $state ? getLangInfo('cpmsg','filter_class_show_open') : getLangInfo('cpmsg','filter_class_show_close');
				$prompt = $state ? getLangInfo('cpmsg','filter_switch_open') : getLangInfo('cpmsg','filter_switch_close');


				//获取该分类敏感词总数
				$sql = "SELECT COUNT(*) AS count FROM pw_wordfb WHERE classid=".S::sqlEscape($class);
				$count = $db->get_value($sql);
			} else {
				ajaxmsg('filter_class_state');
			}

	    	$ajax_basename = EncodeUrl($basename."&action=class&job=switch");
	    	include_once PrintEot('filterAjax');
	    	ajax_footer();
		}
	}
} elseif ($action == 'scan') {
	if ($admin_gid != 3 && $admin_gid != 4){
		adminmsg('illegal_request');
	}

	if ($job == 'go') {
		define('AJAX', 1);
		S::gp(array('fid', 'restart', 'result'));

		# 如果没有敏感词,则不扫描
		$sql = "SELECT COUNT(*) AS count FROM pw_wordfb";
		$word_count = $db->get_value($sql);
		if(!$word_count) scanmsg('filter_setting_not_word', "$basename&action=scan");

		if (!$fid) scanmsg('operate_success', "$basename&action=scan");

		# 开始扫描
		$scan = L::loadClass('WordScan', 'forum');
		$result = $scan->run($fid, $result, $restart);

		# 判断是否扫描完成
		if ($result['prompt'] && $result['prompt'] == 1) {
			scanmsg('filtermsg_scanfinish', "$basename&action=scan&skip=".$fid);
		} elseif ($result['prompt'] && $result['prompt'] == 2) {
			scanmsg('filter_forum_notcontent', "$basename&action=scan&skip=".$fid);
		} elseif ($result['prompt'] && $result['prompt'] == 3) {
			scanmsg('filter_increase_threads', "$basename&action=scan&skip=".$fid);
		}

		# 当前扫描进度
		$progress['progress'] = $result['progress'];
		$progress['count'] = $result['count'];

		# 处理模板显示的时间进度
		$progress['show_progress'] = (int)($progress['progress'] / $progress['count'] * 100);

		showScan($progress);
		ajax_footer();
	} else {
		S::gp(array('skip'));
		$cachetime = $db_wordsfb_cachetime + 3600;
		if ($timestamp > $cachetime) {
			$cache    = setScanCache();
			$catedb   = unserialize($cache['catedb']);
			$threaddb = unserialize($cache['threaddb']);
		} else {
			# 读取缓存
			require_once pwCache::getPath(D_P.'data/bbscache/wordsfb_progress.php');
			$catedb   = unserialize($catedb);
			$threaddb = unserialize($threaddb);
		}

		foreach ($threaddb as $key => $forums) {
			foreach ($forums  as $key2 => $value) {
				# 处理模板显示的时间进度
				$value['show_progress'] = (int)($value['progress'] / $value['count'] * 100);
				if ($value['show_progress'] >= 100) {
					$value['show_progress'] = 100;
					$threaddb[$key][$key2]['progress_style'] = '#16824e';
				} else {
					$threaddb[$key][$key2]['progress_style'] = '#1475a8';
				}
				$threaddb[$key][$key2]['show_progress'] = $value['show_progress'];
			}
		}

		$show_disable = $show_enabled = '';
		if (empty($db_wordsfb_setting)) {
			$show_disable = 'checked';
		} else {
			$show_enabled = 'checked';
		}

		$space  = '<i class="lower lower_a"></i>';

		$post_basename = EncodeUrl($basename."&action=scan");

		include_once PrintEot('filter');
	}
} elseif ($action == 'check') {
	S::gp(array('sort'));

	if(!$sort) $sort = 'pf.id DESC';
	if (empty($job)) {
		Clear();

		$count = $db->get_value("SELECT COUNT(*) FROM pw_filter WHERE state=0 AND tid>0 AND pid=0");
		$page_count = ceil($count/$db_perpage);
		if ($page > $page_count) $page = $page_count;
		$limit = S::sqlLimit(($page-1)*$db_perpage,$db_perpage);
		$pages = numofpage($count,$page,$page_count, "$basename&action=check&");

		$sql = "SELECT pf.id,pf.created_at,pf.filter,pf.tid,pf.pid,pt.subject,pt.author,pt.postdate "
			 . "FROM pw_filter AS pf LEFT JOIN pw_threads AS pt ON pf.tid = pt.tid "
			 . "WHERE pf.state=0 AND pf.tid>0 AND pid=0 ORDER BY $sort $limit";
		$query = $db->query($sql);
		while ($rt = $db->fetch_array($query)) {
			$rt['subject'] = substrs($rt['subject'], 30);
			$rt['date'] = get_date($rt['postdate']);
			$check_list[] = $rt;
		}
	} elseif ($job == 'post') {

		$count = $db->get_value("SELECT COUNT(*) FROM pw_filter WHERE state=0 AND tid>0 AND pid>0");
		$page_count = ceil($count/$db_perpage);
		if ($page > $page_count) $page = $page_count;
		$limit = S::sqlLimit(($page-1)*$db_perpage,$db_perpage);
		$pages = numofpage($count,$page,$page_count, "$basename&action=check&job=post&");

		$sql = "SELECT pf.*,pt.subject,pt.ptable FROM pw_filter AS pf "
			 . "LEFT JOIN pw_threads AS pt ON pf.tid = pt.tid "
			 . "WHERE pf.state=0 AND pf.pid>0 AND pf.tid>0 ORDER BY $sort $limit";
		$query = $db->query($sql);
		while ($rt = $db->fetch_array($query)) {
			$rt['subject'] = substrs($rt['subject'], 40);
			$rt['date'] = get_date($rt['created_at']);
			$check_list[] = $rt;
		}
	} elseif ($job == 'pass') {
		S::gp(array('pid', 'tid', 'type'));

		if (!$tid && !$pid) adminmsg('operate_error');

		if (!$type || $type == 'thread') {
			if (is_array($tid)) {
				$sTid = S::sqlImplode($tid);
			} else {
				$sTid = (int)$tid;
			}
			$ttable = array();
			//查找帖子信息
			$sql = "SELECT pf.id,pt.fid,pt.tid,pt.subject,pt.author,pt.ifcheck FROM pw_filter AS pf "
				 . "LEFT JOIN pw_threads AS pt ON pf.tid=pt.tid WHERE pt.tid IN (".$sTid.") AND pf.pid=0";
			$query = $db->query($sql);
			$objid = $fids = array();
			while ($rt = $db->fetch_array($query)) {
				$objid[]  = $rt['id'];
				$tt = GetTtable($rt['tid']);
				$ttable[$tt] = 1;
				$fids[$rt['fid']][] = $rt['tid'];

				//发消息通知
				M::sendNotice(
					array($rt['author']),
					array(
						'title' => getLangInfo('writemsg','filtermsg_thread_pass_title'),
						'content' => getLangInfo('writemsg','filtermsg_thread_pass_content',array(
							'subject' => $rt['subject'] ,
						)),
					)
				);
			}

			foreach ($fids as $key => $value) {
				$tids = S::sqlImplode($value);
				$sql = "SELECT COUNT(*) FROM pw_threads WHERE ifcheck=0 AND tid IN (".$tids.")";
				$num = $db->get_value($sql);

				$sql = "UPDATE pw_forumdata SET article=article+".S::sqlEscape($num,false).",topic=topic+".S::sqlEscape($num,false)."WHERE fid=".S::sqlEscape($key);
				$db->update($sql);
			}

			//更改帖子状态
			//$sql = "UPDATE pw_threads SET ifcheck=1 WHERE tid IN (".$sTid.")";
			pwQuery::update('pw_threads', 'tid IN (:tid)', array($tid), array('ifcheck'=>1));
			foreach (array_keys($ttable) as $pw_tmsgs) {
				//* $sql = "UPDATE $pw_tmsgs SET ifwordsfb='$db_wordsfb' WHERE tid IN (".$sTid.")";
				pwQuery::update($pw_tmsgs, 'tid IN (:tid)', array($tid), array('ifwordsfb'=>$db_wordsfb));
			}
		
			$filter_id = implode(',' , $objid);
			if ($filter_id) {
				//更改审核状态,更新审核人员信息
				$sql = "UPDATE pw_filter SET state=1,assessor=". S::sqlEscape($admin_name) .",updated_at=".S::sqlEscape($timestamp) ." WHERE id IN (".$filter_id.")";
				$db->update($sql);
			}

			//重定向
			adminmsg('operate_success', "$basename" . "&action=check");
		} else { # 回复
			if (empty($pid)) adminmsg('operate_error');

			if (is_array($pid)) {
				if (!$selid = checkselid($pid)) {
					$basename = "javascript:history.go(-1);";
					adminmsg('operate_error');
				}
				$objid = array_keys($pid);
			} else {
				$selid = (int)$pid;
				$objid[] = S::getGP('id');
			}

			$ptable = S::getGP('ptable');

			if (is_array($ptable)) {
				if ($db_plist && count($db_plist)>1) {
					foreach ($ptable as $key=>$value) {
						if (isset($db_plist[$value])) {
							$postslist[$value] = GetPtable($value);
						}
					}
				} else {
					$postslist[] = 'pw_posts';
				}
			} else {
				$postslist[] = GetPtable($ptable);
			}

			foreach ($postslist as $pw_posts) {
				$fids  = $tids = $db_threads = array();
				$sql = "SELECT fid,tid,pid,author FROM $pw_posts WHERE pid IN($selid)";
				$query = $db->query($sql);
				while ($rt = $db->fetch_array($query)) {
					$fids[$rt['fid']][$rt['tid']][] = $rt['pid'];
					$db_threads[$rt['tid']][] = $rt;
				}

				foreach ($db_threads as $tid => $thread) {
					$toUser = array();
					foreach ($thread as $post) {
						$toUser[] = $post['author'];
					}

					$sql = "SELECT subject FROM pw_threads WHERE tid =".S::sqlEscape($tid);
					$subject = $db->get_value($sql);

					M::sendNotice(
						$toUser,
						array(
							'title' => getLangInfo('writemsg','filtermsg_post_pass_title'),
							'content' => getLangInfo('writemsg','filtermsg_post_pass_content',array(
								'subject' => $subject
							)),
						)
					);
				}

				foreach ($fids as $fid => $value) {
					$forum_count = 0;
					foreach ($value as $tid => $value) {
						$pids = S::sqlImplode($value);
						# 获取主题下要审核通过的回复数量
						$sql = "SELECT COUNT(*) FROM $pw_posts WHERE ifcheck=0 AND pid IN (".$pids.")";
						$num = $db->get_value($sql);
						$forum_count += $num;

						# 获取主题下最后回复信息
						$sql = "SELECT postdate,author FROM $pw_posts WHERE tid=" . S::sqlEscape($tid) . " ORDER BY postdate DESC LIMIT 1";
						$last = $db->get_one($sql);

						# 更新主题的回复数,最后回复信息
						//$sql = "UPDATE pw_threads SET replies=replies+".S::sqlEscape($num) . ",lastpost=" . S::sqlEscape($last['postdate'],false) . ",lastposter =" . S::sqlEscape($last['author'],false) . "WHERE tid=" . S::sqlEscape($tid);
						$sql= pwQuery::buildClause('UPDATE :pw_table SET replies = replies + :replies, lastpost = :lastpost, lastposter = :lastposter WHERE tid = :tid', array('pw_threads', $num, $last['postdate'], $last['author'], $tid));
						$db->update($sql);

						# memcache refresh
						// $threadList = L::loadClass("threadlist", 'forum');
						// $threadList->updateThreadIdsByForumId($fid,$tid);
						Perf::gatherInfo('changeThreadWithForumIds', array('fid'=>$fid));						
					}

					# 更新版块帖子个数
					$sql = "UPDATE pw_forumdata SET article=article+".S::sqlEscape($forum_count)." WHERE fid=".S::sqlEscape($fid);
					$db->update($sql);
				}
				# 更新回复表的更新系数
				$db->update("UPDATE $pw_posts SET ifcheck='1',ifwordsfb='$db_wordsfb' WHERE pid IN($selid)");

				/*foreach ($tids as $key => $value) {
					$rt = $db->get_one("SELECT postdate,author FROM $pw_posts WHERE tid=" . S::sqlEscape($key) . " ORDER BY postdate DESC LIMIT 1");
					$db->update("UPDATE pw_threads SET replies=replies+".S::sqlEscape($value) . ",lastpost=" . S::sqlEscape($rt['postdate'],false) . ",lastposter =" . S::sqlEscape($rt['author'],false) . "WHERE tid=" . S::sqlEscape($key));
					# memcache refresh
					$threadList = L::loadClass("threadlist", 'forum');
					$threadList->updateThreadIdsByForumId($fid,$tid);
				}
				foreach ($fids as $key => $value) {
					$db->update("UPDATE pw_forumdata SET article=article+".S::sqlEscape($value).",tpost=tpost+".S::sqlEscape($value,false)."WHERE fid=".S::sqlEscape($key));
				}
				$db->update("UPDATE $pw_posts SET ifcheck='1',ifwordsfb='$db_wordsfb' WHERE pid IN($selid)");*/
			}

			$filter_id = implode(',' , $objid);
			if ($filter_id) {
				//更改审核状态,更新审核人员信息
				$sql = "UPDATE pw_filter SET state=1,assessor=". S::sqlEscape($admin_name) .",updated_at=".S::sqlEscape($timestamp) ." WHERE id IN (".$filter_id.")";
				$db->update($sql);
			}

			//重定向
			adminmsg('operate_success', "$basename" . "&action=check&job=post");
		}
	} elseif ($job == 'allpass') {
		S::gp(array('type', 'step'));
		if ($step == 2) {
			if (!$type || $type == 'thread') {
				$ttable = array();
				//查找帖子信息
				$sql = "SELECT pf.id,pt.fid,pt.tid,pt.subject,pt.author FROM pw_filter AS pf "
					 . "LEFT JOIN pw_threads AS pt ON pf.tid=pt.tid WHERE pt.tid >0 AND pf.pid=0 AND pf.state=0";
				$query = $db->query($sql);
				$objid = $fids = array();
				while ($rt = $db->fetch_array($query)) {
					$sTid[] = (int)$rt['tid'];
					$objid[]  = $rt['id'];
					$tt = GetTtable($rt['tid']);
					$ttable[$tt] = 1;
					$fids[$rt['fid']]['tid'][] = $rt['tid'];

					M::sendNotice(
						array($rt['author']),
						array(
							'title' => getLangInfo('writemsg','filtermsg_thread_pass_title'),
							'content' => getLangInfo('writemsg','filtermsg_thread_pass_content',array(
								'subject' => $rt['subject'] ,
							)),
						)
					);
				}

				foreach ($fids as $key => $value) {
					$tids = S::sqlImplode($value['tid']);
					if ($tids) {
						$sql = "SELECT COUNT(*) FROM pw_threads WHERE ifcheck=0 AND tid IN (".$tids.")";
						$num = $db->get_value($sql);

						$db->update("UPDATE pw_forumdata SET article=article+".S::sqlEscape($num).",topic=topic+".S::sqlEscape($num,false)."WHERE fid=".S::sqlEscape($key));
					}
				}

				$tmpStid = $sTid;
				$sTid = S::sqlImplode($sTid);
				//更改帖子状态
				if ($sTid) {
					//$sql = "UPDATE pw_threads SET ifcheck=1 WHERE tid IN (".$sTid.")";
					pwQuery::update('pw_threads', 'tid IN (:tid)', array($tmpStid), array('ifcheck'=>1));
					foreach (array_keys($ttable) as $pw_tmsgs) {
						//* $sql = "UPDATE $pw_tmsgs SET ifwordsfb='$db_wordsfb' WHERE tid IN (".$sTid.")";
						pwQuery::update($pw_tmsgs, 'tid IN (:tid)', array($tmpStid), array('ifwordsfb'=>$db_wordsfb));
					}
				}

				$filter_id = S::sqlImplode($objid);
				if ($filter_id) {
					//更改审核状态,更新审核人员信息
					$sql = "UPDATE pw_filter SET state=1,assessor=". S::sqlEscape($admin_name) .",updated_at=".S::sqlEscape($timestamp) ." WHERE id IN (".$filter_id.")";
					$db->update($sql);
				}


				//重定向
				adminmsg('operate_success', "$basename" . "&action=check");
			} else {
				$sql = "SELECT pid FROM pw_filter WHERE pid>0 AND state=0";
				$query = $db->query($sql);
				while ($rt = $db->fetch_array($query)) {
					$pid[] = (int) $rt['pid'];
				}

				if (is_array($pid)) {
					if (!$selid = checkselid($pid)) {
						$basename = "javascript:history.go(-1);";
						adminmsg('operate_error');
					}
				}

				if ($db_plist && is_array($db_plist)) {
					foreach ($db_plist as $key=>$value) {
						if ($key>0) {
							$postslist[] = 'pw_posts'.(int)$key;
						} else {
							$postslist[] = 'pw_posts';
						}
					}
				} else {
					$postslist[] = 'pw_posts';
				}

				foreach ($postslist as $pw_posts) {
					$fids  = $tids = $db_threads = array();
					$sql = "SELECT fid,tid,pid,author FROM $pw_posts WHERE pid IN($selid)";
					$query = $db->query($sql);
					while ($rt = $db->fetch_array($query)) {
						$fids[$rt['fid']][$rt['tid']][] = $rt['pid'];
						//$tids[$rt['tid']] ++;
						//$fids[$rt['fid']] ++;
						$db_threads[$rt['tid']][] = $rt;
					}

					foreach ($db_threads as $tid => $thread) {
						$toUser = array();
						foreach ($thread as $post) {
							$toUser[] = $post['author'];
						}

						$sql = "SELECT subject FROM pw_threads WHERE tid =".S::sqlEscape($tid);
						$subject = $db->get_value($sql);

						$return = M::sendNotice(
							$toUser,
							array(
								'title' => getLangInfo('writemsg','filtermsg_post_pass_title'),
								'content' => getLangInfo('writemsg','filtermsg_post_pass_content',array(
									'subject' => $subject
								)),
							)
						);
					}

					foreach ($fids as $fid => $value) {
						$forum_count = 0;
						foreach ($value as $tid => $value) {
							$pids = S::sqlImplode($value);
							# 获取主题下要审核通过的回复数量
							$sql = "SELECT COUNT(*) FROM $pw_posts WHERE ifcheck=0 AND pid IN (".$pids.")";
							$num = $db->get_value($sql);
							$forum_count += $num;

							# 获取主题下最后回复信息
							$sql = "SELECT postdate,author FROM $pw_posts WHERE tid=" . S::sqlEscape($tid) . " ORDER BY postdate DESC LIMIT 1";
							$last = $db->get_one($sql);

							# 更新主题的回复数,最后回复信息
							//$sql = "UPDATE pw_threads SET replies=replies+".S::sqlEscape($num) . ",lastpost=" . S::sqlEscape($last['postdate'],false) . ",lastposter =" . S::sqlEscape($last['author'],false) . "WHERE tid=" . S::sqlEscape($tid);
							$sql = pwQuery::buildClause('UPDATE :pw_table SET replies = replies + :replies, lastpost = :lastpost, lastposter = :lastposter WHERE tid = :tid', array('pw_threads', $num, $last['postdate'], $last['author'], $tid));
							$db->update($sql);

							# memcache refresh
							// $threadList = L::loadClass("threadlist", 'forum');
							// $threadList->updateThreadIdsByForumId($fid,$tid);
							Perf::gatherInfo('changeThreadWithForumIds', array('fid'=>$fid));						
						}

						# 更新版块帖子个数
						$sql = "UPDATE pw_forumdata SET article=article+".S::sqlEscape($forum_count)." WHERE fid=".S::sqlEscape($fid);
						$db->update($sql);
					}
					# 更新回复表的更新系数
					$db->update("UPDATE $pw_posts SET ifcheck='1',ifwordsfb='$db_wordsfb' WHERE pid IN($selid)");
				}

				$filter_id = implode(',' , $pid);
				if ($filter_id) {
					//更改审核状态,更新审核人员信息
					$sql = "UPDATE pw_filter SET state=1,assessor=". S::sqlEscape($admin_name) .",updated_at=".S::sqlEscape($timestamp) ." WHERE pid IN (".$filter_id.")";
					$db->update($sql);
				}

				//重定向
				adminmsg('operate_success', "$basename" . "&action=check&job=post");
			}
		} else {
			define('AJAX', 1);
			if (!$type || $type == 'thread') {
				//获取全部待审核主题
				$sql = "SELECT COUNT(*) AS count FROM pw_filter WHERE tid>0 AND pid=0 AND state=0";
				$count = $db->get_value($sql);

				$title = getLangInfo('cpmsg','filter_scan_type_thread');

				$ajax_basename = EncodeUrl($basename."&action=check&job=allpass");
			} else {
				//获取全部待审核回复
				$sql = "SELECT COUNT(*) AS count FROM pw_filter WHERE tid>0 AND pid>0 AND state=0";
				$count = $db->get_value($sql);

				$title = getLangInfo('cpmsg','filter_scan_type_post');

				$ajax_basename = EncodeUrl($basename."&action=check&job=allpass");
			}
			include_once PrintEot('filterAjax');
			ajax_footer();
		}
	} elseif ($job == 'del') {
		S::gp(array('pid', 'tid', 'type'));

		if (!$tid && !$pid) adminmsg('operate_error', "$basename" . "&action=check");

		$delarticle = L::loadClass('DelArticle', 'forum');

		if (!$type || $type == 'thread') {
			if (is_array($tid)) {
				$sTid = S::sqlImplode($tid);
			} else {
				$sTid = (int) $tid;
			}
			$sql = "SELECT pf.id,pf.tid,pt.fid,pt.subject,pt.author,pt.authorid FROM pw_filter AS pf "
				 . "LEFT JOIN pw_threads AS pt ON pf.tid=pt.tid WHERE pf.tid IN (".$sTid.") AND pf.pid=0";
			$query = $db->query($sql);
			$objid = array();
			$threadsid = array();

			require_once(R_P.'require/credit.php');
			$creditOpKey = "Delete";
			$forumInfos = array();

			while ($rt = $db->fetch_array($query)) {
				$fids[$rt['fid']][] = $rt['tid'];
				$objid[]  = $rt['id'];
				$threadsid[] = $rt['tid'];
				if (!isset($forumInfos[$rt['fid']])) $forumInfos[$rt['fid']] = L::forum($rt['fid']);
				$foruminfo = $forumInfos[$rt['fid']];
				$creditset = $credit->creditset($foruminfo['creditset'],$db_creditset);

				$credit->addLog("topic_$creditOpKey", $creditset[$creditOpKey], array(
					'uid' => $rt['authorid'],
					'username' => $rt['author'],
					'ip' => $onlineip,
					'fname' => strip_tags($foruminfo['name']),
					'operator' => $windid,
				));
				$credit->sets($rt['authorid'],$creditset[$creditOpKey],false);

				M::sendNotice(
					array($rt['author']),
					array(
						'title' => getLangInfo('writemsg','filtermsg_thread_del_title'),
						'content' => getLangInfo('writemsg','filtermsg_thread_del_content',array(
							'subject' => $rt['subject']
						)),
					)
				);
			}

			$filter_id = implode(',' , $objid);

			$credit->runsql();
			$delarticle->delTopicByTids($threadsid, $db_recycle);

			//更改审核状态,更新审核人员信息
			$sql = "UPDATE pw_filter SET state=2,assessor=". S::sqlEscape($admin_name) .",updated_at=".S::sqlEscape($timestamp) ." WHERE id IN (".$filter_id.")";
			$db->update($sql);

			//重定向
			adminmsg('operate_success', "$basename" . "&action=check");
		} else {
			if (empty($pid)) adminmsg('operate_error');

			if (is_array($pid)) {
				if (!$selid = checkselid($pid)) {
					$basename = "javascript:history.go(-1);";
					adminmsg('operate_error');
				}
				$objid = array_keys($pid);
			} else {
				$selid = (int)$pid;
				$objid = (int)$pid;
			}

			$ptable = S::getGP('ptable');

			if (is_array($ptable)) {
				if ($db_plist && count($db_plist)>1) {
					foreach ($ptable as $key=>$value) {
						if (isset($db_plist[$value])) {
							$postslist[$value] = GetPtable($value);
						}
					}
				} else {
					$postslist[] = 'pw_posts';
				}
			} else {
				$postslist[] = GetPtable($ptable);
			}

			foreach ($postslist as $pw_posts) {
				$fids  = $tids = $db_threads = array();
				$sql = "SELECT fid,tid,pid,author FROM $pw_posts WHERE pid IN($selid)";
				$query = $db->query($sql);
				while ($rt = $db->fetch_array($query)) {
					$fids[$rt['fid']][$rt['tid']][] = $rt['pid'];
					$db_threads[$rt['tid']][] = $rt;
					$rt['ptable'] = substr($pw_posts, 8);
					$replydb[] = $rt;
				}

				foreach ($fids as $fid => $value) {
					$forum_count = 0;
					foreach ($value as $tid => $value) {
						$pids = S::sqlImplode($value);
						# 获取主题下要审核通过的回复数量
						$sql = "SELECT COUNT(*) FROM $pw_posts WHERE ifcheck=1 AND pid IN (".$pids.")";
						$num = $db->get_value($sql);
						$forum_count += $num;

						# 获取主题下最后回复信息
						$sql = "SELECT postdate,author FROM $pw_posts WHERE tid=" . S::sqlEscape($tid) . " AND pid NOT IN (".$pids.") ORDER BY postdate DESC LIMIT 1";
						$last = $db->get_one($sql);

						# 更新主题的回复数,最后回复信息
						//$sql = "UPDATE pw_threads SET replies=replies-".S::sqlEscape($num) . ",lastpost=" . S::sqlEscape($last['postdate'],false) . ",lastposter =" . S::sqlEscape($last['author'],false) . "WHERE tid=" . S::sqlEscape($tid);
						$sql = pwQuery::buildClause('UPDATE :pw_table SET replies = replies - :replies, lastpost = :lastpost, lastposter = :lastposter WHERE tid = :tid', array('pw_threads', $num, $last['postdate'], $last['author'], $tid));
						$db->update($sql);

						# memcache refresh
						// $threadList = L::loadClass("threadlist", 'forum');
						// $threadList->updateThreadIdsByForumId($fid,$tid);
						Perf::gatherInfo('changeThreadWithForumIds', array('fid'=>$fid));						
					}

					# 更新版块文章数
					$sql = "UPDATE pw_forumdata SET article=article-".S::sqlEscape($forum_count)." WHERE fid=".S::sqlEscape($fid);
					$db->update($sql);
				}

				foreach ($db_threads as $tid => $thread) {
					$toUser = array();
					foreach ($thread as $post) {
						$toUser[] = $post['author'];
					}

					$sql = "SELECT subject FROM pw_threads WHERE tid =".S::sqlEscape($tid);
					$subject = $db->get_value($sql);

					M::sendNotice(
						$toUser,
						array(
							'title' => getLangInfo('writemsg','filtermsg_post_del_title'),
							'content' => getLangInfo('writemsg','filtermsg_post_del_content',array(
								'subject' => $subject
							)),
						)
					);
				}
			}

			$delarticle->delReply($replydb, $db_recycle);

			if (is_array($objid)) {
				$filter_id = implode(',' , $objid);
				if ($filter_id) {
					//更改审核状态,更新审核人员信息
					$sql = "UPDATE pw_filter SET state=2,assessor=". S::sqlEscape($admin_name) .",updated_at=".S::sqlEscape($timestamp) ." WHERE id IN (".$filter_id.")";
					$db->update($sql);
				}
			} else {
				$filter_id = $objid;

				if ($filter_id) {
					//更改审核状态,更新审核人员信息
					$sql = "UPDATE pw_filter SET state=2,assessor=". S::sqlEscape($admin_name) .",updated_at=".S::sqlEscape($timestamp) ." WHERE pid IN (".$filter_id.")";
					$db->update($sql);
				}
			}

			//重定向
			adminmsg('operate_success', "$basename" . "&action=check&job=post");
		}
	}
	include_once PrintEot('filter');exit;
} elseif ($action == 'record') {
	if ($admin_gid == 3){
		S::gp(array('sort'));

		if(!$sort) $sort = 'pf.updated_at';
		if (empty($job)) {

			$count = $db->get_value("SELECT COUNT(*) FROM pw_filter WHERE state>0 AND tid>0 AND pid=0");
			$page_count = ceil($count/$db_perpage);
			if ($page > $page_count) $page = $page_count;
			$limit = S::sqlLimit(($page-1)*$db_perpage,$db_perpage);
			$pages = numofpage($count,$page,$page_count, "$basename&action=record&");

			$sql = "SELECT pf.*,pt.subject, pt.author FROM pw_filter AS pf LEFT JOIN pw_threads AS pt ON pf.tid = pt.tid WHERE pf.state>0 AND pf.tid>0 AND pf.pid=0 ORDER BY $sort DESC $limit";
			$query = $db->query($sql);
			while ($rt = $db->fetch_array($query)) {
				$rt['date']  = get_date($rt['updated_at']);
				$rt['operate'] = $operate[$rt['state']];
				$record_list[] = $rt;
			}
			include_once PrintEot('filter');exit;
		} elseif ($job == 'post') {

			$count = $db->get_value("SELECT COUNT(*) FROM pw_filter WHERE state>0 AND pid>0");
			$page_count = ceil($count/$db_perpage);
			if ($page > $page_count) $page = $page_count;
			$limit = S::sqlLimit(($page-1)*$db_perpage,$db_perpage);
			$pages = numofpage($count,$page,$page_count, "$basename&action=record&job=post&");

			$sql = "SELECT pf.*,pt.subject FROM pw_filter AS pf LEFT JOIN pw_threads AS pt ON pf.tid = pt.tid WHERE pf.state>0 AND pf.pid>0 ORDER BY $sort DESC  $limit";
			$query = $db->query($sql);
			while ($rt = $db->fetch_array($query)) {
				$rt['subject'] = substrs($rt['subject'], 33);
				$rt['date']  = get_date($rt['updated_at']);
				$rt['operate'] = $operate[$rt['state']];
				$record_list[] = $rt;
			}
			include_once PrintEot('filter');exit;
		}
	} else {
		adminmsg('illegal_request');
	}
} elseif ($action == 'show') {
	define('AJAX', 1);
	S::gp(array('pid', 'tid', 'type'));

	if ($pid > 0 && $tid > 0){
		$job = 'post';
		# 回复
		$sql = "SELECT ptable FROM pw_threads WHERE tid = ". S::sqlEscape($tid);
		$ptable = $db->get_value($sql);
		$pw_posts = GetPtable($ptable);
		$objid = $db->get_value("SELECT id FROM pw_filter WHERE pid=" . S::sqlEscape($pid). " AND tid=" . S::sqlEscape($tid));
		//获取回复帖信息
		$sql = "SELECT pt.tid, pt.subject, pp.pid, pp.author, pp.postdate, pp.content FROM $pw_posts AS pp LEFT JOIN pw_threads AS pt ON pp.tid = pt.tid WHERE pp.pid=" . S::sqlEscape($pid). " AND pt.tid=" . S::sqlEscape($tid);
		$content = $db->get_one($sql);
		if(!$content && $objid) {
			$sql = "DELETE FROM pw_filter WHERE pid=" . S::sqlEscape($pid). " AND tid=" . S::sqlEscape($tid);
			$db->update($sql);

			ajaxmsg('filtermsg_post_already_delete', "$basename&action=$type&job=post");exit;
		}

		$content['subject'] = showHightLight($content['subject']);
		$content['content'] = showHightLight($content['content']);
		$content['date']    = get_date($content['postdate']);
	} else {
		$job = 'thread';
		$pw_tmsgs = GetTtable($tid);

		//获取主题帖信息
		$sql = "SELECT pt.tid, pt.author, pt.subject, pt.postdate, pc.content FROM pw_threads AS pt LEFT JOIN $pw_tmsgs AS pc ON pt.tid = pc.tid WHERE pt.tid=" . S::sqlEscape($tid);
		$content = $db->get_one($sql);
		$content['subject'] = showHightLight($content['subject']);
		$content['content'] = showHightLight($content['content']);
		$content['date']    = get_date($content['postdate']);
	}
	include_once PrintEot('filterAjax');
	ajax_footer();
} elseif ($action == 'synchronous') {
	if ($admin_gid == 3){
		$appclient = L::loadClass('AppClient');
		$sitehash = $appclient->getApicode();

		if ($job == 'confirm') {
			define('AJAX', 1);

			$ft_update_num = getWordUpdate();

			if ($ft_update_num) {
				$classdb = array();
				$sql = "SELECT * FROM pw_filter_class";
				$query = $db->query($sql);
				while($rt = $db->fetch_array($query)){
					HtmlConvert($rt);
					$classdb[$rt['id']]=$rt;
				}
			} else {
				$job = 'notupdate';
			}

			include_once PrintEot('filterAjax');
			ajax_footer();
		} else {
			define('AJAX', 1);
			S::gp(array('state','class','newclass'));

			//插入新分类
			if ($newclass) {
				$class = newClass($newclass);
			}

			$class_title = $db->get_value("SELECT title FROM pw_filter_class WHERE id=".S::sqlEscape($class));

			//更改分类状态
			setClassState($class, $state);

			require_once(R_P.'require/posthost.php');

			//获取中心词库词语数量
			$app_num = $db->get_value("SELECT COUNT(*) AS count FROM pw_wordfb WHERE custom = 0");

			if (empty($app_num)) {
				//重新同步中心词库
				$data = PostHost($updateHost, "m=wordsfb&a=restart&sitehash=$sitehash", "POST");
			} else {
				//同步中心词库
				$data = PostHost($updateHost, "m=wordsfb&a=update&sitehash=$sitehash", "POST");
			}

			$content = pwConvert(unserialize($data),$db_charset,'UTF8');

			$list = array();
			if(is_array($content)){
				$i = 0;
				foreach($content as $key => $value){
					if($value['word']){
						$id = $db->get_value("SELECT id FROM pw_wordfb WHERE word=".S::sqlEscape($value['word']));

						if(empty($id)){
							$sql  ="INSERT INTO pw_wordfb (word,wordreplace,type,wordtime,classid) VALUES (".S::sqlEscape($value['word']) .", '*****', ".S::sqlEscape($center_level[$value['level']]) .", ".S::sqlEscape($timestamp) .", ".S::sqlEscape($class) ." )";
							$db->update($sql);

							$list[] = array('word' => $value['word'], 'level' => $center_level[$value['level']]);

							$i++;
						}
					}
				}

				//更新缓存
				updatecache_w();

				//设置字典文件
				setAllDictionary();

				if (!$i) {
					ajaxmsg('没有需要同步的敏感词(本地已有相同的敏感词)', "$basename&action=setting");
				}

				include_once PrintEot('filterAjax');
				ajax_footer();
			} else {
				if ($content) {
					//重定向
					ajaxmsg($content, "$basename&action=setting");
				} else {
					//重定向
					ajaxmsg('filtermsg_not_update', "$basename&action=setting");
				}
			}
		}
	} else {
		adminmsg('illegal_request');
	}
} else {
	//重定向
	adminmsg('operate_success');
}

/**
 * @desc 显示"正在扫描"模板
 *
 * @param array $forums -- 扫描进度信息
 */
function showScan($progress)
{
	$show = <<<EOT
<div id="fid$thread[fid]">
<div style="width:150px;" class="fl mr10">
	<div class="fil_percent">
		<div class="fil_per_value" style="width:$progress[show_progress]%;"></div>
	</div>
</div>
<p class="fl" style="line-height:16px;">$progress[progress]/$progress[count]</p>
<div class="c"></div>
</div>
EOT;
	echo pwJsonEncode(array('msg' => $show));
}

/**
 * @desc 显示经过Json处理过的提示信息并跳转
 *
 * @param string $msg -- 提示
 * @param string $jumpurl -- 跳转到URL
 */
function scanmsg($msg, $jumpurl='')
{
	$msg = getLangInfo('cpmsg',$msg);
	$show = <<<EOT
	$msg
EOT;
	echo pwJsonEncode(array('msg' => $show, 'url' => $jumpurl));
	ajax_footer();
}

/**
 * @desc AJAX跳转
 *
 * @param String $msg -- 提示
 * @param String $jumpurl -- 跳转到URL
 */
function ajaxmsg($msg, $jumpurl='')
{
	define('AJAX', 1);

	$msg = getLangInfo('cpmsg',$msg);
	$show = <<<EOT
<div style="padding:1.5em 3em">
	$msg
</div>
<script language="JavaScript">
function skip(){
	location.href = "$jumpurl";
}
setTimeout("skip()",2000);
</script>
EOT;
	echo $show;
	ajax_footer();
}


/**
 * 清空无效记录
 */
function Clear()
{
	global $db;
	$sql = "SELECT pf.id,pt.tid FROM pw_filter AS pf LEFT JOIN pw_threads AS pt ON pf.tid = pt.tid WHERE pf.state=0 AND pf.tid>0 OR pf.state=2 AND pid=0 ORDER BY pf.id";
	$query = $db->query($sql);
	while ($rt = $db->fetch_array($query)) {
		if (!$rt['tid']) {
			$sql = "DELETE FROM pw_filter WHERE id=".S::sqlEscape($rt['id']);
			$db->update($sql);
		}
	}
}

/**
* @desc 返回一个高亮的字符串
*/
function showHightLight($msg) {
	$wordsfb = L::loadClass('FilterUtil', 'filter');
	$wordsfb->loadWords();

	if ($wordsfb->replace) {
		foreach ($wordsfb->replace as $key => $value) {
		    $keyword = $wordsfb->getTrueBanword($key);
		    $replace = '<span style="background:#0F3;font-weight:bold;display:inline;padding:1px">'.$keyword.'</span>';
		    $msg = preg_replace("/$key/i", $replace, $msg);
		}
	}

	if ($wordsfb->alarm) {
		foreach ($wordsfb->alarm as $key => $value) {
		    $keyword = $wordsfb->getTrueBanword($key);
		    $replace = '<span style="background:#FF0;font-weight:bold;display:inline;padding:1px">'.$keyword.'</span>';
		    $msg = preg_replace("/$key/i", $replace, $msg);
		}
	}

	if ($wordsfb->fbwords) {
		foreach ($wordsfb->fbwords as $key => $value) {
		    $keyword = $wordsfb->getTrueBanword($key);
		    $replace = '<span style="background:#F00;font-weight:bold;display:inline;padding:1px">'.$keyword.'</span>';
		    $msg = preg_replace("/$key/i", $replace, $msg);
		}
    }

	return $msg;
}

/**
* @desc 判断是否需要更新
*/
function getWordUpdate() {
	global $db, $updateHost;

	require_once(R_P.'require/posthost.php');
	$appclient = L::loadClass('AppClient');

	$sitehash = $appclient->getApicode();

	//获取中心词库词语数量
	$app_num = $db->get_value("SELECT COUNT(*) AS count FROM pw_wordfb WHERE custom = 0");

	//获取更新词数
	if ($app_num) {
		$data = PostHost($updateHost, "m=wordsfb&a=request&sitehash=$sitehash", "POST");
	} else {
		$data = PostHost($updateHost, "m=wordsfb&a=requestall&sitehash=$sitehash", "POST");
	}

	$data = intval($data);

	return $data;
}

/**
 * @desc 生成总字典文件
 */
function setAllDictionary()
{
	global $db, $score;

	L::loadClass('filterutil', 'filter', false);

	$bin_file = D_P.'data/bbscache/dict_all.dat';
	$source_file = D_P.'data/bbscache/dict_all.txt';

	if(!file_exists($bin_file) && !file_exists($source_file)) {
		pwCache::setData($source_file, '');//文本形式字典
		pwCache::setData($bin_file,'');//二进制字典
	}

	$classid = getCloseClass();
	$classid = S::sqlImplode($classid);

	$querys = $db->query("SELECT word, type FROM pw_wordfb WHERE classid NOT IN ($classid)");
	$content = "";
	while ($value = $db->fetch_array($querys)) {
		$weighing = $score[$value['type']];
	  	$content.="".$value['word']."|".$weighing."\r\n";
	}

	pwCache::setData($source_file, $content);//文本形式字典
	pwCache::setData($bin_file,'');//二进制字典

	//更新二进制字典
	$trie = new Trie();
	$trie->build($source_file, $bin_file);
}

/**
 * @desc 获取已关闭分类的id
 *
 * @return string
 */
function getCloseClass()
{
	global $db;

	$sql = "SELECT id FROM pw_filter_class WHERE state=0";
	$query = $db->query($sql);
	$id = array();
	while ($value = $db->fetch_array($query)) {
		$id[] = $value['id'];
	}

	if (!$id) $id=array(-1);

	return $id;
}

/**
 * @desc 新建分类
 *
 * @param string $title -- 分类名
 * @return int
 */
function newClass($title)
{
	global $db;

	//判断是否有重复分类
	$sql = " SELECT id FROM pw_filter_class WHERE title = " . S::sqlEscape($title);
	$num = $db->get_one($sql);

	if ($num) {
		return 0;
	} else {
		//插入分类
		$sql = "INSERT pw_filter_class SET state=1,title = " . S::sqlEscape($title);
		$db->update($sql);

		return $db->insert_id();
	}
}

/**
 * @desc 删除分类
 *
 * @param int $class -- 分类id
 */
function delClass($class)
{
	global $db;

	if ($class > 0) {
		//删除分类
		$sql = "DELETE FROM pw_filter_class WHERE id=".S::sqlEscape($class);
		$query = $db->update($sql);

		//删除分类下的敏感词
		$sql = "DELETE FROM pw_wordfb WHERE classid=".S::sqlEscape($class);
		$query = $db->update($sql);
	} elseif ($class == 0) {
		//清空敏感词
		$sql = "DELETE FROM pw_wordfb";
		$query = $db->update($sql);
	}
}

/**
 * 添加敏感词
 *
 * @param string $word -- 敏感词
 * @param int $type -- 等级
 * @param int $class -- 分类id
 * @param string $repword -- 替换词
 */
function insertWord($word, $type, $class, $repword = '*****')
{
	global $db;
	$wordtime = mktime(0,0,0,date("m"),date("d"),date("Y"));
	$insertValue = ','.S::sqlEscape($type).','.S::sqlEscape($class).','.S::sqlEscape($repword).','.S::sqlEscape($wordtime).', 1';

	if (is_array($word)) {
		$sql = "INSERT INTO pw_wordfb (word, type, classid, wordreplace, wordtime, custom) VALUES";

		$sqlStr = '';
		foreach ($word as $value) {
			if ($value) {
				$sqlStr .= $sqlStr ? ", (".S::sqlEscape($value).$insertValue.")" : "(".S::sqlEscape($value).$insertValue.")";
			}
		}
		$sql = $sql.$sqlStr;
		$db->update($sql);
	} else {
		$value = array(
			'word'	      => $word,
			'wordreplace' => $repword,
			'type'	      => $type,
			'wordtime'    => $wordtime,
			'custom'      => 1
		);

		$sql = "INSERT INTO pw_wordfb"
			 . " SET " . S::sqlSingle($value);
		$db->update($sql);
	}
}

/**
 * @desc 设置分类状态
 *
 * @param int $class 分类id
 * @param int $state 状态
 */
function setClassState($class, $state) {
	global $db;
	$sql = "UPDATE pw_filter_class SET state=".S::sqlEscape($state)." WHERE id=".S::sqlEscape($class);;
	$db->update($sql);

	//更新缓存
	updatecache_w();

	//设置字典文件
	setAllDictionary();
}

function setScanCache()
{
	global $db, $timestamp, $db_plist;

	# 获取回复表
	if ($db_plist && is_array($db_plist)) {
		foreach ($db_plist as $key=>$value) {
			if ($key>0) {
				$postslist[] = 'pw_posts'.(int)$key;
			} else {
				$postslist[] = 'pw_posts';
			}
		}
	} else {
		$postslist[] = 'pw_posts';
	}

	if(file_exists(D_P.'data/bbscache/wordsfb_progress.php')) {
		# 读取缓存
		require_once pwCache::getPath(D_P.'data/bbscache/wordsfb_progress.php');
		$temp_threaddb = unserialize($threaddb);
	} else {
		$temp_threaddb = array();
	}

	$forum = $catedb = $forumdb = $subdb1 = $subdb2 = $threaddb = array();

	# 获取版块列表
	$query = $db->query("SELECT fid,name,fup,type FROM pw_forums WHERE cms!='1' ORDER BY vieworder");
	while ($forums = $db->fetch_array($query)) {
		$forums['name'] = Quot_cv(strip_tags($forums['name']));

		if ($forums['type'] == 'category') {
			$catedb[]  = $forums;
		} elseif ($forums['type'] == 'forum') {
			$forumdb[] = $forums;
		} elseif ($forums['type'] == 'sub') {
			$subdb1[]  = $forums;
		} else {
			$subdb2[]  = $forums;
		}
	}

	foreach ($catedb as $cate) {
		$threaddb[$cate['fid']] = array();
		foreach ($forumdb as $key2 => $forumss) {
			if ($forumss['fup'] == $cate['fid']) {
				if (!array_key_exists($forumss['fid'], $temp_threaddb[$cate['fid']])) {
					# 读取版块帖子总数和表进度
					$forumss['count']    = 0;
					$forumss['progress'] = 0;
					$forumss['result']	 = 0;
					$forumss['table_progress']['pw_threads'] = 0;
					foreach ($postslist as $pw_posts) {
						$forumss['table_progress'][$pw_posts] = 0;
					}
					$threaddb[$cate['fid']][$forumss['fid']] = $forumss;
				} else {
					$threaddb[$cate['fid']][$forumss['fid']] = $temp_threaddb[$cate['fid']][$forumss['fid']];
					unset($threaddb[$cate['fid']][$forumss['fid']]['table_progress']);
					$threaddb[$cate['fid']][$forumss['fid']]['table_progress']['pw_threads'] = $temp_threaddb[$cate['fid']][$forumss['fid']]['table_progress']['pw_threads'];
					foreach ($postslist as $pw_posts) {
						$threaddb[$cate['fid']][$forumss['fid']]['table_progress'][$pw_posts] = $temp_threaddb[$cate['fid']][$forumss['fid']]['table_progress'][$pw_posts];
					}
				}
				unset($forumdb[$key2]);
				foreach ($subdb1 as $key3 => $sub1) {
					if ($sub1['fup'] == $forumss['fid']) {
						if (!array_key_exists($sub1['fid'], $temp_threaddb[$cate['fid']])) {
							# 读取版块帖子总数和表进度
							$sub1['count']    = 0;
							$sub1['progress'] = 0;
							$sub1['result']	  = 0;
							$sub1['table_progress']['pw_threads'] = 0;
							foreach ($postslist as $pw_posts) {
								$sub1['table_progress'][$pw_posts] = 0;
							}
							$threaddb[$cate['fid']][$sub1['fid']] = $sub1;
						} else {
							$threaddb[$cate['fid']][$sub1['fid']] = $temp_threaddb[$cate['fid']][$sub1['fid']];
							unset($threaddb[$cate['fid']][$sub1['fid']]['table_progress']);
							$threaddb[$cate['fid']][$sub1['fid']]['table_progress']['pw_threads'] = $temp_threaddb[$cate['fid']][$sub1['fid']]['table_progress']['pw_threads'];
							foreach ($postslist as $pw_posts) {
								$threaddb[$cate['fid']][$sub1['fid']]['table_progress'][$pw_posts] = $temp_threaddb[$cate['fid']][$sub1['fid']]['table_progress'][$pw_posts];
							}
						}
						unset($subdb1[$key3]);
						foreach ($subdb2 as $key4 => $sub2) {
							if ($sub2['fup'] == $sub1['fid']) {
								if (!array_key_exists($sub2['fid'], $temp_threaddb[$cate['fid']])) {
									# 读取版块帖子总数和表进度
									$sub2['count']    = 0;
									$sub2['progress'] = 0;
									$sub2['result']	  = 0;
									$sub2['table_progress']['pw_threads'] = 0;
									foreach ($postslist as $pw_posts) {
										$sub2['table_progress'][$pw_posts] = 0;
									}
									$threaddb[$cate['fid']][$sub2['fid']] = $sub2;
								} else {
									$threaddb[$cate['fid']][$sub2['fid']] = $temp_threaddb[$cate['fid']][$sub2['fid']];
									unset($threaddb[$cate['fid']][$sub2['fid']]['table_progress']);
									$threaddb[$cate['fid']][$sub2['fid']]['table_progress']['pw_threads'] = $temp_threaddb[$cate['fid']][$sub2['fid']]['table_progress']['pw_threads'];
									foreach ($postslist as $pw_posts) {
										$threaddb[$cate['fid']][$sub2['fid']]['table_progress'][$pw_posts] = $temp_threaddb[$cate['fid']][$sub2['fid']]['table_progress'][$pw_posts];
									}
								}
								unset($subdb2[$key4]);
							}
						}
					}
				}
			}
		}
	}

	$catedb   = serialize($catedb);
	$threaddb = serialize($threaddb);

	# 写入文件
	$filecontent = "<?php\r\n";
	$filecontent.="\$catedb=".pw_var_export($catedb).";\r\n";
	$filecontent.="\$threaddb=".pw_var_export($threaddb).";\r\n";
	$filecontent.="?>";
	$cahce_file = D_P.'data/bbscache/wordsfb_progress.php';
	pwCache::setData($cahce_file, $filecontent);

	setConfig('db_wordsfb_cachetime', $timestamp);
	updatecache_c();

	return array('catedb' => $catedb, 'threaddb' => $threaddb);
}
?>