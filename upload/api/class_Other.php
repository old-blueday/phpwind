<?php

!defined('P_W') && exit('Forbidden');
/*
*api mode 12 其他操作/信息
*/
class Other {
    var $base;
	var $db;
	var $appver;

    function Other($base) {        
        $this->base = $base;
        $this->db = $base->db;
		$this->appver = '2009/12/18';
    }
	
	function fetchAppver($appid = null,$appkey = null,$appidstate = 0) {//获取版本号
        require_once(R_P.'admin/cache.php');
        if ($appid) {
            setConfig('db_appid', $appid);
        }
        if ($appkey) {
            setConfig('db_siteappkey', $appkey);
        }
		setConfig('db_appidstate', $appidstate);

		updatecache_c();
		return new ApiResponse($this->appver);
	}

	function insertNav($title,$link,$method = '1') {//添加主导航信息
		if (!$title) {
			return new ApiResponse(false);
		}
		$nid = $this->db->get_value("SELECT nid FROM pw_nav WHERE type='main' AND title=" . S::sqlEscape($title));
		$view = $this->db->get_value("SELECT view FROM pw_nav WHERE type='main' ORDER BY view DESC");
		
		$navConfigService = L::loadClass('navconfig', 'site'); /* @var $navConfigService PW_NavConfig */
		if (!$nid && $method == '1') {
			$navConfigService->add(PW_NAV_TYPE_MAIN, array(
					'pos'	=> '-1',
					'title'	=> $name,
					'style'	=> '|||',
					'link'	=> $link,
					'alt'	=> $name,
					'target'=> 1,
					'view'	=> $view+1,
					'upid'	=> 0,
					'isshow'=> 1
			));
		} elseif ($nid) {
			$navConfigService->update($nid, array('isshow'=>$method));
		}
		
		return new ApiResponse(true);
	}

	function shareLinks($stype = 'getsharelinks',$sid,$name,$url = '',$logo = '',$descrip = '',$threadorder = 0,$ifcheck = 0) {//友情链接
		
		require_once(R_P.'admin/cache.php');

		if ($stype == 'getsharelinks') {
			$sharelinkdb = array();
			$query = $this->db->query("SELECT * FROM pw_sharelinks");
			while ($rt = $this->db->fetch_array($query)) {
				$sharelinkdb[$rt['sid']] = $rt;
			}
			return new ApiResponse($sharelinkdb);

		} elseif ($stype == 'upsharelinks') {
			
			$sid = $this->db->get_value('SELECT sid FROM pw_sharelinks WHERE sid='.S::sqlEscape($sid));
			if ($sid) {
				$pwSQL = S::sqlSingle(array(
					'name'			=> $name,
					'url'			=> $url,
					'logo'			=> $logo,
					'descrip'		=> $descrip,
					'threadorder'	=> $threadorder,
					'ifcheck'		=> $ifcheck
				));
				$this->db->update("UPDATE pw_sharelinks SET $pwSQL WHERE sid=".S::sqlEscape($sid));
				updatecache_i();
				
			} else {
				$pwSQL = S::sqlSingle(array(
					'threadorder'	=> $threadorder,
					'name'			=> $name,
					'url'			=> $url,
					'logo'			=> $logo,
					'descrip'		=> $descrip,
					'ifcheck'		=> $ifcheck
				));
				$this->db->update("INSERT INTO pw_sharelinks SET $pwSQL");
				$sid = $this->db->insert_id();
				updatecache_i();
			}
			return new ApiResponse($sid);

		} elseif ($stype == 'remove') {
			
			$this->db->update('DELETE FROM pw_sharelinks WHERE sid='.S::sqlEscape($sid));
			updatecache_i();
			return new ApiResponse($sid);

		} elseif ($stype == 'state') {

			$sid = $this->db->get_value('SELECT sid FROM pw_sharelinks WHERE sid='.S::sqlEscape($sid));

			return new ApiResponse($sid);
		}
	}

	function alertCnzz() {//CNZZ统计开启
		global $db_ystats_ifopen;
		require_once(R_P.'admin/cache.php');
		if ($db_ystats_ifopen == '0') {
			setConfig('db_ystats_ifopen', 1);
			updatecache_c();
		}
		return new ApiResponse(true);
	}
	
	function showSurvey($itemdb = array()) {//调查问卷
		global $db_charset;
		
		$survey_cache = "<?php\r\n";
		if (!empty($itemdb) && is_array($itemdb)) {
			$survey_cache .= "\$db_survey='1';\r\n";
		} else {
			$survey_cache .= "\$db_survey='0';\r\n";
		}
		
		foreach ($itemdb as $key => $item) {
			$item['url'] = rawurldecode($item['url']);
			$itemd[$key] = $item;
		}
		if (is_array($itemd)) {
			$survey_cache .= "\$survey_cache=".pw_var_export($itemd);
			$survey_cache .= ';';
		}
		$survey_cache .= "\r\n?>";
		$survey_cache = pwConvert($survey_cache,$db_charset,'gbk');
		pwCache::setData(D_P."data/bbscache/survey_cache.php",$survey_cache);
		return new ApiResponse(true);
	}

    function configThreads($params) {//生成帖子交换缓存
        if ($params && is_array($params)) {
            require_once(R_P.'admin/cache.php');
            setConfig('db_threadconfig', $params);
            updatecache_c();
            return new ApiResponse(true);
        }
        return new ApiResponse(false);
    }

	function threadscateGory($classdb) {//生成帖子交换分类
    
        $classcache = "<?php\r\n\$info_class=array(\r\n";

        foreach ($classdb as $key => $class) {

            !$class['ifshow'] && $class['ifshow'] = '0';
			$class['cid'] = (int)$class['cid'];
            $flag && $info_class[$class['cid']]['ifshow'] && $class['ifshow'] = '1';

            $class['name'] = str_replace(array('"',"'"),array("&quot;","&#39;"),$class['name']);
            $classcache .= "'$class[cid]'=>".pw_var_export($class).",\r\n\r\n";
        }
        $classcache .= ");\r\n?>";
        pwCache::setData(D_P."data/bbscache/info_class.php",$classcache);
    }

	/** 添加帮助信息
	 * 
	 * @param int $hup 上级项目
	 * @param array/string $title 标题(数组时，则为批量添加),例如array('忘记密码','选择风格') 或者 '忘记密码'
	 * @param array/string $content 内容,如果是数组的话,必须和标题键值一一对应,例如array('忘记密码','选择风格')或者 '忘记密码'
	 * @param string $url 外链URL
	 * @param int $hid 编辑或者删除时的帮助项ID
	 * @param string $action 选择操作add,edit,delete
	 * @return int/string 例如:25 或者 25,26
	 */
	function insertHelp($hup = 0,$title,$content,$url = '',$hid = 0,$action = 'add') {

		//* @include_once pwCache::getPath(D_P.'data/bbscache/help_cache.php');
		extract(pwCache::getData(D_P.'data/bbscache/help_cache.php', false));
		require_once(R_P.'admin/cache.php');
		$hup = (int)$hup;
		$hid = (int)$hid;

		if ($action == 'add' || $action == 'edit') {
			$url = trim($url);
			
			if (is_array($title) && $action == 'add') {
				$titledb = $title;
				unset($title);
				$hids = '';
				foreach ($titledb as $key => $title) {
					$title = trim($title);
					if (empty($title)) {
						return new ApiResponse('help_title_empty');
					}
					$desc = '';
					if (is_array($content)) {
						$desc = $content[$key];
					} else {
						$desc = $content;
					}
					
					$desc = str_replace(
						array("\t","\r",'  '),
						array('&nbsp; &nbsp; ','','&nbsp; '),
						trim($desc)
					);
					$lv = 0;
					$fathers = '';
					foreach ($_HELP as $key => $value) {
						if (strtolower($title) == strtolower($value['title'])) {
							return new ApiResponse('help_title_exist');
						}
						if ($key == $hup) {
							$lv = $value['lv']+1;
							$fathers = ($value['fathers'] ? "$value[fathers]," : '').$hup;
							!$value['ifchild'] && $this->db->update("UPDATE pw_help SET ifchild='1' WHERE hid=".S::sqlEscape($hup));
						}
					}
					$this->db->update("INSERT INTO pw_help"
						. " SET " . S::sqlSingle(array(
							'hup'		=> $hup,
							'lv'		=> $lv,
							'fathers'	=> $fathers,
							'title'		=> $title,
							'url'		=> $url,
							'content'	=> $desc,
							'vieworder'	=> 0
					)));
					$hid = $this->db->insert_id();
					$hids .= $hids ? ','.$hid : $hid;
				}
			} elseif (!is_array($title)) {

				$title = trim($title);
				if (empty($title)) {
					return new ApiResponse('help_title_empty');
				}
				$content = str_replace(
					array("\t","\r",'  '),
					array('&nbsp; &nbsp; ','','&nbsp; '),
					trim($content)
				);
				
				$lv = 0;
				$fathers = '';
				if ($action == 'add') {
					foreach ($_HELP as $key => $value) {
						if (strtolower($title) == strtolower($value['title'])) {
							return new ApiResponse('help_title_exist');
						}
						if ($key == $hup) {
							$lv = $value['lv']+1;
							$fathers = ($value['fathers'] ? "$value[fathers]," : '').$hup;
							!$value['ifchild'] && $this->db->update("UPDATE pw_help SET ifchild='1' WHERE hid=".S::sqlEscape($hup));
						}
					}
					$this->db->update("INSERT INTO pw_help"
						. " SET " . S::sqlSingle(array(
							'hup'		=> $hup,
							'lv'		=> $lv,
							'fathers'	=> $fathers,
							'title'		=> $title,
							'url'		=> $url,
							'content'	=> $content,
							'vieworder'	=> 0
					)));
					$hids = $this->db->insert_id();
					
				} elseif ($action == 'edit') {
					if ($hid == $hup) {
						return new ApiResponse('hup_error1');
					}
					if ($_HELP[$hid]['hup'] != $hup && strpos(",{$_HELP[$hup][fathers]},",",$hid,") !== false) {
						return new ApiResponse('hup_error2');
					}

					foreach ($_HELP as $key => $value) {
						if ($key != $hid && strtolower($title) == strtolower($value['title'])) {
							return new ApiResponse('help_title_exist');
						}
					}
					$this->db->update("UPDATE pw_help"
						. " SET " . S::sqlSingle(array(
								'hup'		=> $hup,
								'title'		=> $title,
								'url'		=> $url,
								'content'	=> $content,
								'vieworder'	=> 0
							))
						. " WHERE hid=".S::sqlEscape($hid)
					);
					$hids = $hid;
				}
			} else {
				return new ApiResponse('help_title_error');
			}
			
			updatecache_help();
			return new ApiResponse($hids);
		} elseif ($action == 'delete' && $hid > 0) {
			$this->db->update("DELETE FROM pw_help WHERE hid=".S::sqlEscape($hid).'OR hup='.S::sqlEscape($hid));
			updatecache_help();
			return new ApiResponse(true);
		} else {
			return new ApiResponse('API_OPERATE_ERROR');
		}
	}

	function alertMusic($state) {//虾米音乐网编辑器开启/关闭

		require_once(R_P.'admin/cache.php');
		setConfig('db_xiami_music_open', $state);
		updatecache_c();
		
		return new ApiResponse(true);
	}
}
