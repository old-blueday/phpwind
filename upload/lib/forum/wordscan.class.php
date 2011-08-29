<?php !defined('P_W') && exit('Forbidden');
/**
 * @desc 敏感词扫描类
 *
 */
class PW_WordScan {
	/**
	 * @desc DB类
	 *
	 * @var object
	 */
	var $db;
	
	/**
	 * @desc 敏感词过滤类
	 *
	 * @var object
	 */
	var $filter;
	
	/**
	 * @desc 跳词数
	 *
	 * @var int
	 */
	var $skip = 0;
	
	/**
	 * @desc 是否开启简繁转换: false=>否; true=>是
	 *
	 * @var BOOL
	 */
	var $convert = false;
	
	/**
	 * @desc 每次扫描记录条数
	 *
	 * @var int
	 */
	var $pagesize;
	
	/**
	 * @desc 如何处理扫描结果:1=>帖子停止浏览; 0=>帖子正常浏览
	 *
	 * @var int
	 */
	var $dispose = 1;
	
	/**
	 * @desc 要扫描的指定版块id
	 *
	 * @var int
	 */
	var $fid ;
	
	/**
	 * @desc 该版块的记录总数
	 *
	 * @var int
	 */
	var $count;
	
	/**
	 * @desc 该版块已扫描的记录数
	 *
	 * @var int
	 */
	var $progress;
	
	/**
	 * 该次扫描到的包含敏感词的记录数
	 *
	 * @var int
	 */
	var $result;
	
	/**
	 * @desc 该版块下所有表进度
	 *
	 * @var int
	 */
	var $table_progress;
	
	/**
	 * @desc 当前扫描的数据表
	 *
	 * @var string
	 */
	var $table;
	
	/**
	 * @desc 最后一次扫描到的记录id
	 *
	 * @var int
	 */
	var $objid;
	
	function PW_WordScan() {
		global $db,$db_wordsfb_setting;
		$this->db = $db;
		$this->filter = L::loadClass('FilterUtil', 'filter');
		
		if (empty($db_wordsfb_setting)) {
			$this->dispose = 0;
		}
	}
	
	/**
	 * @desc 扫描并返回结果
	 *
	 * @param int $fid -- 版块ID
	 * @param int $result  -- 是否清空待审核记录
	 * @param int $restart -- 是否重新扫描(清空扫描记录)
	 * @param int $pagesize -- 每次扫描记录条数
	 * @param int $skip -- 跳词数
	 * @param int $convert -- 是否开启简繁转换: 0=>否; 1=>是
	 * @return array -- 扫描进度:返回当前版块的记录总数和已扫描记录数和提示id
	 * 
	 * prompt: 提示信息id
	 * 1 扫描完成
	 * 2 版块无帖子
	 * 3 没有新增的帖子
	 */
	function run($fid, $result = 0, $restart = 0, $pagesize = 1000, $skip = 0, $convert = false) {
		$this->fid = $fid;
		$this->skip = $skip;
		$this->convert = $convert;
		$this->pagesize = $pagesize;
		if ($restart == 1) {
			$this->ClearScanProgress();
		} else {
			$this->getProgress($result);
		}

		if (!$this->count) return array('prompt' => 2);
		
		if ($this->progress >= $this->count) return array('prompt' => 3);

		foreach ($this->table_progress as $table => $progress) {
			if ($table == 'pw_threads') {
				$sql = "SELECT COUNT(*) FROM $table WHERE tid>".S::sqlEscape($progress)." AND fid =".S::sqlEscape($this->fid);
				$new = $this->db->get_value($sql);
				if ($new) {//if (10 < $new) {
					$this->table = $table;
					$this->scanThreads();
					
					# 更新扫描进度
					$this->updateProgress();
					return array('progress' => $this->progress, 'count' => $this->count);
				}
			} else {
				$sql = "SELECT COUNT(*) FROM $table WHERE pid>".S::sqlEscape($progress)." AND fid =".S::sqlEscape($this->fid);
				$new = $this->db->get_value($sql);
				if ($new) {//if (10 < $new) {
					$this->table = $table;
					$finish = $this->scanPosts();

					# 更新扫描进度
					$this->updateProgress();
					return array('progress' => $this->progress, 'count' => $this->count);
				}
			}
		}
		return array('progress' => $this->progress, 'count' => $this->count, 'prompt' => 1);
	}
	
	/**
	 * @desc 扫描主题表
	 */
	function scanThreads() {
		# 获取主题信息,判断是否重复记录
		$sql = " SELECT t.tid, t.subject, t.ifcheck, t.postdate, t.author, f.id, f.state "
			 . " FROM $this->table AS t LEFT JOIN pw_filter AS f ON t.tid = f.tid"
			 . " WHERE t.tid>".S::sqlEscape($this->table_progress[$this->table])." AND t.fid =".S::sqlEscape($this->fid)
			 . " GROUP BY t.tid ORDER BY t.tid ASC LIMIT ".$this->pagesize;
		$query = $this->db->query($sql);
		
		$num = 0;
		while ($thread = $this->db->fetch_array($query)) {
			# 获取帖子内容
			$pw_tmsgs = GetTtable($thread['tid']);
			$sql = " SELECT content FROM $pw_tmsgs WHERE tid=".S::sqlEscape($thread['tid']);
			$thread['content'] = $this->db->get_value($sql);
			
			# 扫描进度
			$this->progress++;
			$this->objid = $thread['tid'];
						
			# 帖子内容
			$content = $thread['subject'] . $thread['content'];
	
			# 过滤敏感词
			$result = $this->filter->paraseContent($content, $this->skip, $this->convert);
	
			# 处理扫描结果
			if (is_array($result)) {
				$word  = $this->getWordString($result[1]);	
				$score = round($result[0], 2);
		
				if ($this->dispose && $score > 0 && $thread['ifcheck']) {
					# 更改审核状态
					//$sql = "UPDATE pw_threads SET ifcheck=0 WHERE tid = " .S::sqlEscape($thread['tid']);
					pwQuery::update('pw_threads', 'tid=:tid', array($thread['tid']), array('ifcheck'=>0));
					
					$num++;
							
					# 更新版块信息
					$this->updateCache();
						
					# 发消息通知
					$msg = array(
						'subject' => $thread['subject'],
						'tid' 	  => $thread['tid'],
						'fid' 	  => $this->fid,
					);
					$this->sendMsg($thread['author'], $msg, 't');
				}

				if (!$thread['id']) {
					# 如果不重复,扫描到的结果+1
					$this->result++;
					
					$compart = $insertString ? ',' : '';
					# 处理数据
					$insertString .= $compart . "( " . S::sqlEscape($thread['tid']) . ", " . S::sqlEscape($word) . ", "
							      . S::sqlEscape($thread['postdate']) . ")";					
				} elseif ($thread['state']) {
					# 如果是已经审核通过的再次被扫到,扫描到的结果+1
					$this->result++;
		
					# 处理数据
					$value = array(
						'state'  => 0,
						'filter' => $word,
						'created_at' => $thread['postdate'],
					);
					$value = S::sqlSingle($value);
		
					# 更新记录
					$sql = "UPDATE pw_filter SET {$value} WHERE pid=0 AND tid = " . S::sqlEscape($thread['tid']);
					$this->db->update($sql);
				}
			}
		}
		
		# 插入记录
		if ($insertString) {
			$insertSql =  "INSERT INTO pw_filter (tid, filter, created_at) VALUES " . $insertString;
			$this->db->update($insertSql);
		}
		
		if ($this->dispose && $num) {
			$this->updateCache($num);
		}
	}
	
	/**
	 * @desc 更新因帖子状态改变而影响的版块信息
	 */
	function updateCache($num) {
		/**
		$sql = "UPDATE pw_forumdata SET article=article-".S::sqlEscape($num,false).",topic=topic-".S::sqlEscape($num,false)." WHERE fid=".S::sqlEscape($this->fid);
		$this->db->update($sql);
		**/
		$this->db->update(pwQuery::buildClause("UPDATE :pw_table SET article=article-:article,topic=topic-:topic WHERE fid=:fid", array('pw_forumdata', $num, $num, $this->fid)));
	}
	
	/**
	 * @desc 扫描回复表
	 */
	function scanPosts()
	{
		# 获取帖子信息,判断是否重复记录
		$sql = "SELECT p.pid,p.content,p.subject,t.tid,t.subject AS title,p.author,p.postdate,p.ifcheck,f.id,f.state "
			 . "FROM $this->table AS p LEFT JOIN pw_threads AS t ON p.tid=t.tid LEFT JOIN pw_filter AS f ON p.pid = f.pid "
			 . "WHERE p.tid > 0 AND p.pid>".S::sqlEscape($this->table_progress[$this->table])
			 ." AND t.fid =".S::sqlEscape($this->fid)
			 . " GROUP BY p.pid ORDER BY p.pid ASC LIMIT ".$this->pagesize;
		$query = $this->db->query($sql);		
		while ($post = $this->db->fetch_array($query)) {
			
			# 扫描进度
			$this->progress++;
			$this->objid = $post['pid'];
		
			# 内容
			$content = $post['subject'] . $post['content'];
			# 过滤敏感词
			$result = $this->filter->paraseContent($content, $this->skip, $this->convert);
		
			# 处理扫描结果
			if(is_array($result)) {		
				$word  = $this->getWordString($result[1]);	
				$score = round($result[0], 2);
		
				if ($this->dispose && $score > 0 && $post['ifcheck']) {
					$tids[$post['tid']]++;
					# 待审核
					$sql = "UPDATE $this->table SET ifcheck=0 WHERE pid = " .S::sqlEscape($post['pid']);
					$this->db->update($sql);
						
					# 发消息通知
					$msg = array(
						'subject' => $post['title'],
						'tid' => $post['tid'],
						'pid' => $post['pid'],
						'fid' => $this->fid,
					);
					$this->sendMsg($post['author'], $msg, 'p');
				}
		
				if (!$post['id']) {
					# 如果不是重复记录,扫描到的结果+1
					$this->result++;
		
					$compart = $insertSql ? ',' : '';
					# 处理数据
					$insertSql .= $compart . "( " . S::sqlEscape($post['tid']) . ", " . S::sqlEscape($post['pid']) 
									  . ", " . S::sqlEscape($word) . ", " . S::sqlEscape($post['postdate']) . ")";
				} elseif ($post['state']) {					
					# 如果是已经处理过的审核记录再次被扫到,扫描到的结果+1
					$this->result++;
							
					# 处理数据
					$value = array(
						'state'  => 0,
						'filter' => $word,
						'created_at' => $post['postdate'],
					);
					$value = S::sqlSingle($value);
		
					# 更新记录
					$sql = "UPDATE pw_filter SET {$value} WHERE tid=".S::sqlEscape($post['tid'])." AND pid=" . S::sqlEscape($post['pid']);
					$this->db->update($sql);
				}
			}			
		}

		if ($this->dispose && $tids) {
			# 总文章数
			$article = 0;
				
			foreach ($tids as $key => $value) {				
				# 更新主题帖回复数
				//$sql = "UPDATE pw_threads SET replies=replies-".S::sqlEscape($value,false)." WHERE tid=".S::sqlEscape($key);
				$sql = pwQuery::buildClause('UPDATE :pw_table SET replies=replies-:replies WHERE tid=:tid', array('pw_threads', $value, $key));
				$this->db->update($sql);
				
				$article += $value;
			}
			
			# 更新版块文章数
			/**
			$sql = "UPDATE pw_forumdata SET article=article-".S::sqlEscape($article,false)." WHERE fid=".S::sqlEscape($this->fid);
			$this->db->update($sql);
			**/
			$this->db->update(pwQuery::buildClause("UPDATE :pw_table SET article=article-:article WHERE fid=:fid", array('pw_forumdata', $article, $this->fid)));
		}
		
		# 插入记录
		if ($insertSql) {
			$sql =  "INSERT INTO pw_filter (tid, pid, filter, created_at) VALUES " . $insertSql;
			$this->db->update($sql);
		}
	}
	
	/**
	 * @desc 发消息通知用户帖子被封
	 *
	 * @param string $user -- 收件人用户名
	 * @param array $L     -- 消息内容信息
	 * @param string $type -- 帖子类型:t=>主题;p=>回复
	 */
	function sendMsg($user, $L, $type = 't') {
		if ($type == 't') {		
			$title	 = getLangInfo('cpmsg','filtermsg_thread_title');
			$content = getLangInfo('cpmsg','filtermsg_thread_content', $L);
		} else {
			$title	 = getLangInfo('cpmsg','filtermsg_post_title');
			$content = getLangInfo('cpmsg','filtermsg_post_content', $L);
		}
	
		M::sendNotice(
			array($user),
			array(
				'title' => $title,
				'content' => $content,
			)
		);
	}
	
	/**
	 * @desc 清空扫描进度
	 */
	function ClearScanProgress() {
		global $db_plist;
		
		# 读取缓存
		//* require_once pwCache::getPath(D_P.'data/bbscache/wordsfb_progress.php');
		extract(pwCache::getData(D_P.'data/bbscache/wordsfb_progress.php', false));
		$this->threaddb = unserialize($threaddb);
		$this->catedb   = unserialize($catedb);
		$temp_threaddb = unserialize($threaddb);
		
		# 获取主题表
		$sql = "SELECT COUNT(*) AS count FROM pw_threads WHERE fid =".S::sqlEscape($this->fid);
		$count = $this->db->get_value($sql);
		
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
			
		foreach ($postslist as $pw_posts) {
			$sql = "SELECT COUNT(*) AS count FROM $pw_posts WHERE fid =".S::sqlEscape($this->fid);
			$postcount = $this->db->get_value($sql);
			$count += $postcount;
		}
		
		foreach ($temp_threaddb as $key => $forums) {
			foreach ($forums  as $key2 => $value) {
				if ($this->fid == $key2) {
					
					foreach ($value['table_progress'] as $table => $progress) {
						$value['table_progress'][$table] = 0;
					}
					
					$this->count = $count;
					$this->progress = 0;
					$this->result = 0;
					$this->table_progress = $value['table_progress'];
					
					$temp_threaddb[$key][$key2]['count'] = $this->count;
					$temp_threaddb[$key][$key2]['progress'] = $this->progress;
					$temp_threaddb[$key][$key2]['result'] = $this->result;
					$temp_threaddb[$key][$key2]['table_progress'] = $this->table_progress;
				}
			}
		}
		$this->threaddb = $temp_threaddb;
		$threaddb = serialize($temp_threaddb);
	
		# 写入文件	
		$filecontent = "<?php\r\n";
		$filecontent.="\$catedb=".pw_var_export($catedb).";\r\n";
		$filecontent.="\$threaddb=".pw_var_export($threaddb).";\r\n";
		$filecontent.="?>";
		$cahce_file = D_P.'data/bbscache/wordsfb_progress.php';
		pwCache::setData($cahce_file, $filecontent);
	}
	
	/**
	 * @desc 获取扫描进度
	 */
	function getProgress($result)
	{
		global $db_plist;
		
		# 读取缓存
		//* require_once pwCache::getPath(D_P.'data/bbscache/wordsfb_progress.php');
		extract(pwCache::getData(D_P.'data/bbscache/wordsfb_progress.php', false));
		$this->threaddb = unserialize($threaddb);
		$this->catedb   = unserialize($catedb);
		$temp_threaddb = unserialize($threaddb);
		
		# 获取主题帖总数
		$sql = "SELECT COUNT(*) AS count FROM pw_threads WHERE fid =".S::sqlEscape($this->fid);
		$count = $this->db->get_value($sql);
		
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
		
		# 获取回复帖数量
		foreach ($postslist as $pw_posts) {
			$sql = "SELECT COUNT(*) AS count FROM $pw_posts WHERE fid =".S::sqlEscape($this->fid);
			$postcount = $this->db->get_value($sql);
			$count += $postcount;
		}

		foreach ($temp_threaddb as $key => $forums) {
			foreach ($forums  as $key2 => $value) {
				if ($this->fid == $key2) {
					$this->table_progress = $value['table_progress'];
					$this->count = $count;
					$this->progress = $value['progress'];
					if ($result == 1) {
						$this->result = 0;
					} else {
						$this->result = $value['result'];
					}
					
					$temp_threaddb[$key][$key2]['count'] = $this->count;
					$temp_threaddb[$key][$key2]['progress'] = $this->progress;
					$temp_threaddb[$key][$key2]['result'] = $this->result;
					$temp_threaddb[$key][$key2]['table_progress'] = $this->table_progress;
				}
			}
		}

		$this->threaddb = $temp_threaddb;
		$threaddb = serialize($temp_threaddb);
		$catedb   = serialize($this->catedb);
	
		# 写入文件	
		$filecontent = "<?php\r\n";
		$filecontent.="\$catedb=".pw_var_export($catedb).";\r\n";
		$filecontent.="\$threaddb=".pw_var_export($threaddb).";\r\n";
		$filecontent.="?>";
		$cahce_file = D_P.'data/bbscache/wordsfb_progress.php';
		pwCache::setData($cahce_file, $filecontent);
	}
	
	/*function getProgress()
	{
		# 读取缓存
		require_once(D_P.'data/bbscache/wordsfb_progress.php');
		$this->threaddb = unserialize($threaddb);
		$this->catedb   = unserialize($catedb);
				
		foreach ($this->threaddb as $key => $forums) {
			foreach ($forums  as $key2 => $value) {
				if ($this->fid == $key2) {
					$this->table_progress = $value['table_progress'];
					$this->count = $value['count'];
					$this->progress = $value['progress'];
					$this->result = $value['result'];
				}
			}
		}
	}*/
	
	/**
	 * @desc 更新扫描进度
	 */
	function updateProgress() {
		if ($this->progress > $this->count) $this->progress = $this->count;
		if ($this->objid) {
			foreach ($this->threaddb as $key => $forums) {
				foreach ($forums  as $key2 => $value) {
					if ($this->fid == $key2) {
						$this->threaddb[$key][$key2]['progress'] = $this->progress;
						$this->threaddb[$key][$key2]['result']   = $this->result;
						$this->threaddb[$key][$key2]['table_progress'][$this->table] = $this->objid;
					}
				}
			}
			$threaddb = serialize($this->threaddb);
			$catedb = serialize($this->catedb);
		
			# 写入文件	
			$filecontent = "<?php\r\n";
			$filecontent.="\$catedb=".pw_var_export($catedb).";\r\n";
			$filecontent.="\$threaddb=".pw_var_export($threaddb).";\r\n";
			$filecontent.="?>";
			$cahce_file = D_P.'data/bbscache/wordsfb_progress.php';
			pwCache::setData($cahce_file, $filecontent);
		}
	}
	
	/**
	 * @desc 将扫描出的敏感词数组过滤成字符串
	 *
	 * @param unknown_type $strArray
	 * @return unknown
	 */
	function getWordString($strArray) {
		$array = array();
		
		foreach ($strArray as $value) {
			$array[] = $value[0];
		}
	
		$array = array_unique($array);
	
		$string='';
		foreach($array as $key=>$val) {
			if ($val) {
				$string .= $string ? ','.$val : $val;
			}
		}
		
		return $string;
	}
}