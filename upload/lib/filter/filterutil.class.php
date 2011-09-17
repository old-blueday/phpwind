<?php
!function_exists('readover') && exit('Forbidden');
/**
 * 过滤内容的实体工具，包含过滤的一系列动作及实现
 * 对其他底层类的封装和调用，每个方法都可以单独调用，
 * 用以实现外部的需求，比如需要进行编码转换，那么直接掉
 * 本类中的编码转换即可获得数据，其他方法调用类似
 *
 * @package Filter
 */
class PW_FilterUtil {
	/**
	 * 序列化后二进制字典存放的默认路径
	 * @var string
	 */
	var $dict_bin_path;
	/**
	 * 默认所有字典的存放位置
	 * @var string
	 */
	var $dict_dir;
	/**
	 * 默认文本形式字典存放路径
	 * @var string
	 */
	var $dict_source_path;
	/**
	 * 当次检测的敏感词权重等级
	 * @var int
	 */
	var $filter_weight = 0;
	/**
	 * 当次检测的敏感词数据
	 * @var array
	 */
	var $filter_word;

	var $code;
	var $fbwords = null;
	var $replace = null;
	var $_list = array();

	function PW_FilterUtil($file = array()) {
		if ($file) {
			$this->dict_dir = $file['dir'];
			$this->dict_bin_path = $file['bin'];
			$this->dict_source_path = $file['source'];
		} else {
			$this->dict_dir = D_P.'data/bbscache/';
			$this->dict_path = $this->dict_dir . 'wordsfb.php';
			$this->dict_bin_path = $this->dict_dir . 'dict_all.dat';
			$this->dict_source_path = $this->dict_dir . 'dict_all.txt';
		}
		$this->code = $GLOBALS['db_wordsfb'];
	}

	function setFiles($file) {
		$this->dict_dir = $file['dir'];
		$this->dict_bin_path = $file['bin'];
		$this->dict_source_path = $file['source'];
	}

	/**
	 * 检测字符串是否需要经过词语替换
	 */
	function ifwordsfb($str) {
		return ($this->comprise($str) === false) ? $this->code : 0;
	}

	function equal($currcode) {
		return ($currcode == $this->code);
	}

	function loadWords() {
		if (!is_array($this->fbwords)) {
			//* include pwCache::getPath(D_P."data/bbscache/wordsfb.php");
			extract(pwCache::getData(D_P."data/bbscache/wordsfb.php", false));
			$this->fbwords	= (array)$wordsfb;
			$this->replace	= (array)$replace;
			$this->alarm	= (array)$alarm;
		}
	}
	/**
	 * 返回一个经过词语替换的字符串
	 */
	function convert($str, $wdstruct = array()) {
		$msg = $str;
		
		$file = array(
			'bin'    => $this->dict_bin_path,
			'source' => $this->dict_source_path
		);
		$trie = new Trie($file);
		$trie->nodes = $trie->getBinaryDict($trie->default_out_path);
		$msg = $trie->replaces($str);
		
		if ($wdstruct) {
			if ($msg == $str) {
				$this->addList('yes', $wdstruct['type'], $wdstruct['id']);
			} elseif ($wdstruct['code'] > 0) {
				$this->addList('no', $wdstruct['type'], $wdstruct['id']);
			}
		}
		return $msg;
	}
	function addList($key, $type, $id) {
		if (empty($this->_list)) {
			register_shutdown_function(array($this, 'updateWordsfb'));
		}
		$this->_list[$key][$type][] = $id;
	}
	/**
	 * 更新所有内容的词语系数
	 */
	function updateWordsfb() {
		if ($this->_list['yes']) {
			$this->_update($this->_list['yes'], $this->code);
		}
		if ($this->_list['no']) {
			$this->_update($this->_list['no'], 0);
		}
		$this->_list = array();
	}

	function _update($arr, $val) {//private function
		global $db;
		foreach ($arr as $k => $v) {
			list($table, $field) = $this->tablestruct($k);
			if ($table && $v) {
				/**
				$db->update("UPDATE $table SET ifwordsfb=" . S::sqlEscape($val) . " WHERE $field IN (" . S::sqlImplode($v) . ')');
				**/
				pwQuery::update("{$table}", "$field IN (:$field)", array($v), array('ifwordsfb' => $val));
			}
		}
	}

	function tablestruct($type) {
		$struct = array(
			'topic'		=> array($GLOBALS['pw_tmsgs'], 'tid'),
			'posts'		=> array($GLOBALS['pw_posts'], 'pid'),
			'comments'	=> array('pw_comment', 'id'),
			'oboard'	=> array('pw_oboard','id'),
			'diary'		=> array('pw_diary','did')
		);
		return isset($struct[$type]) ? $struct[$type] : array('','');
	}

	/**
	 * 检测字符串中是否包含禁用词语
	 *
	 * @param $str
	 * @param $replace
	 * @param $alarm
	 * @return bool 是否包含禁用词语，true为包含，false为没有禁用词语
	 */
	function comprise($str, $replace = true,$alarm = true) {
		if (empty($str)) {
			return false;
		}
		$this->getFilterResult($str);
		$titlelen = strlen($str);
		$arrWords = array_keys($this->filter_word);
		$title_filter_word = '';
		foreach ($arrWords as $key) {
			if ($key > $titlelen) break;
			$title_filter_word .= $title_filter_word ? ','.$this->filter_word[$key] : $this->filter_word[$key];
		}
		return $title_filter_word ? $this->getTrueBanword($title_filter_word) : false;
	}

	function getTrueBanword($word) {
		$word = stripslashes($word);
		//$word = substr($s_word,1,strlen($word)-3);
		$word = preg_replace('/\.\{0\,(\d+)\}/i', '', $word);
		return $word;
	}
	/**
	 * 检测内容中是否有报警词语
	 */
	function alarm($title, $content = '') {
		if ($this->alarm) {
			foreach ($this->alarm as $key => $value) {
				if (preg_match($key,$title) || preg_match($key,$content)) {
					return true;
				}
			}
		}
		return false;
	}

    /**
	* @desc 返回唯一实例,本例为单件

	function getInstance() {
		static $instance = null;
		if (!isset($instance)) {
			$instance = new FilterUtil();
		}
		return $instance;
	}*/

    /**
     * 构建字典
     * @param $path 序列化后字典存放路径
     * @return $return int 构造成功后返回的行数
     */
    function buildDict($path = null) {
        if($path == null) {
           $path = array(
                'bin'    => $this->dict_bin_path,
                'source' => $this->dict_source_path
            );
        }
        $trie = new Trie($path);
        $return = $trie->build();
        return $return;
    }

    /**
     * 过滤内容
     * 1.过滤HTML代码 2. 过滤火星文 3.过滤英文标点
     * 4. 繁体转换 5. 跳词合并 6. 分词匹配 7.计算权重
     * 注意:过滤英文标点后过滤全角符号，可能会出现错误
     * @param $content string 干净的待分词的内容
     * @param $skip int  跳词距离
     * @param $convert boll  简繁转换
     * @param $dic_path string 序列化字典存放路径
     * @return $weight int 被分析文档的整体权重
     */
	function paraseContent($content, $skip = 0, $convert = false, $dict_path = null) {

		//过滤用户输入文本中所有UBB标签
		//$content = $this->filterWindCode($content);

		//过滤用户输入文本中所有HTML标签
		$content = $this->filterHtml($content);

		//过滤中文状态标点符号及火星文,会过滤日文片假名
		//$content = $this->filterChineseCode($content);

		//过滤键盘上能敲出来的各种标点符号，包括全角和半角
		// $content = $this->filterSymbol($content);

		if($convert){
			//进行编码转换，这里主要用于繁体转简体
			$content = $this->convertCode($content);
		}

		if ($skip >= 1) {
			$skip = intval($skip);
			//跳词处理
			$content = $this->skipWords($skip,$content);
		}
		$file = array(
			'bin'    => $this->dict_bin_path,
			'source' => $this->dict_source_path
		);
		$trie = new Trie($file);

		//用于提供查找关键字的方法
		$result = $trie->search($content, $dict_path);
		if (empty($result)) {
            return 0;
        }

		$bayes = new Bayes();
		//获取文章权重
		$weight = $bayes->getWeight($result);
		return array($weight,$result);
    }

	function getFilterResult($content, $skip = 0, $convert = false, $dict_path = null ) {
		//判断敏感词
		$result = $this->paraseContent($content);
		$array = array();
		//处理判断结果结果
		if (is_array($result)) {
			foreach ($result[1] as $key=>$value) {
				$array[$key] = $value[0];
			}
			$array = array_unique($array);

			$this->filter_weight = $result[0] >= 1 ? 1 : ($result[0] >= 0.8 ? 2 : 3);
		}
		$this->filter_word = $array;
	}

	/**
	 * @desc 插入filter记录
	 *
	 * @param int $tid -- 主题id
	 * @param int $pid -- 回复id
	 * @param string $filter -- 包含敏感词
	 */
	function insert($tid, $pid, $filter, $state=0) {
    	global $db,$timestamp;

    	//判断是否重复记录
    	$sql = "SELECT id,state FROM pw_filter WHERE tid=".S::sqlEscape($tid)." AND pid=".S::sqlEscape($pid);
    	$record = $db->get_one($sql);

	    if (!$record) {
	    	//处理数据
	    	$value = array(
	    	    'tid'    => $tid,
	    	    'pid'    => $pid,
	            'filter' => $filter,
	            'state'  => ($state!=3 ? 0 : 3),
				'assessor'=> ($state!=3 ? '' : 'SYSTEM'),
	            'created_at' => $timestamp,
				'updated_at' => $timestamp,
	        );
	        //插入新记录
	        $db->update("INSERT INTO pw_filter SET " . S::sqlSingle($value));
    	} else {
    		if ($record['state'] == 2 || $record['state'] == 1) {
    			//处理数据
				$value = array(
					'state'  => 0,
					'filter' => $filter,
					'created_at' => $timestamp,
				);
				$value = S::sqlSingle($value);

    			//更新记录
				$sql = "UPDATE pw_filter SET {$value} WHERE tid=".S::sqlEscape($tid)." AND pid=" . S::sqlEscape($pid);
				$db->update($sql);
    		}
    	}
    }

	/**
	 * @desc 删除filter
	 *
	 * @param int $tid 主题id
	 * @param int $pid 回复id
	 */
	function delete($tid, $pid) {
		global $db;
		$db->update("DELETE FROM pw_filter WHERE tid=" . S::sqlEscape($tid) . " AND pid=" . S::sqlEscape($pid));
	}

	/**
	 * 过滤键盘上能敲出来的各种标点符号，包括全角和半角
	 * @param $content 经过基本HTML标签过滤的内容
	 * @return $ret string 返回过滤后的结果
	 */
	function filterSymbol($content) {
		$length = strlen($content);
		$i = 0;
		$ret = '';
		while ($i < $length) {
			$c = ord($content[$i]);
			if($c<48 || ($c>58 && $c <65) || ($c>90 && $c <97) ||($c>122 && $c<127) ) {
				$i++;
				continue;  //ASCII码中规定的非数字字母符号
			}
			$ret .= chr($c);
			$i++;
		}
		return $ret;
	}

    /**
     * 进行编码转换，这里主要用于繁体转简体
     * @param $fcode 即from code ，原来的编码,比如"BIG5"
     * @param $tcode 即to code，目标编码,比如"GB2312"
     * @param $content 对已经处理过的文本进行转换
     * @param $dict_dir 转换字符对照表存放位置
     * @return $ret string 返回转换后的文本
     */
    function convertCode($content, $fcode = 'CHST', $tcode = 'CHSS', $dict_dir = null) {
        if(is_null($dict_dir)) {
            $dict_dir = $this->dict_dir;
        }
        L::loadClass('Chinese', 'utility/lang', false);
        $ch = new Chinese($fcode, $tcode, true);
        $ret = $ch->Convert($content);
        return $ret;
    }

    /**
     * 跳词处理
     * @param $skip 跳跃长度
     * @param $content 处理过的输入文本
     * @param $dict_dir 字典文本文件位置
     * @return $ret 跳词处理后的文本
     */
    function skipWords($skip, $content, $dict_dir=null) {
        $ret = $content;
        if(is_null($dict_dir)) {
            $dict_dir = $this->dict_source_path;
        }

        $handle = fopen($dict_dir,"r");
        while (!feof($handle)) {
            $lines = fgets($handle);
			//echo $lines;
			//exit;
			//echo $lines;
			//$lines = "古方迷香 1";
            preg_match('/^(.*?)\s+(.*)/i', $lines, $key);
            $len = strlen($key[1]); //计算关键词长度
            for($i=0; $i<$len;$i++) { //开始拼装正则
                if($i == 0) {
					if(ord($key[1][$i]) > 127){
						$rgx = substr($key[1], $i,2);
						$i++;
					}else{
						$rgx = substr($key[1], $i,1);
					}
                } else  {
					if(ord($key[1][$i]) > 127){
						$rgx .= "(.{0,".$skip."}?)". substr($key[1], $i,2);
						$i++;
					}else{
						$rgx .=  substr(str_replace(array('/','.'),array('\/','\.'),$key[1]), $i,1);
					}
                    if($i == $len-1) {
                        $rgx ="/" . $rgx ."/";
                    }
                }
            }
			//echo "$rgx, $key[1], $ret";

			//echo $rgx;exit;
            $ret = preg_replace($rgx, $key[1], $ret);
        }
        fclose($handle);
        return $ret;
    }

    /**
     * 过滤用户输入文本中所有UBB标签
     * @param $content string 用户输入的文本
     * @return string  返回被过滤后文本
     */
    function filterWindCode($content) {
    	$pattern = array();
    	if (strpos($content,"[post]")!==false && strpos($content,"[/post]")!==false) {
    		$pattern[] = "/\[post\].+?\[\/post\]/is";
    	}
    	if (strpos($content,"[hide=")!==false && strpos($content,"[/hide]")!==false) {
    		$pattern[] = "/\[hide=.+?\].+?\[\/hide\]/is";
    	}
    	if (strpos($content,"[sell")!==false && strpos($content,"[/sell]")!==false) {
    		$pattern[] = "/\[sell=.+?\].+?\[\/sell\]/is";
    	}
    	$pattern[] = "/\[[a-zA-Z]+[^]]*?\]/is";
    	$pattern[] = "/\[\/[a-zA-Z]*[^]]\]/is";

    	$content = preg_replace($pattern,'',$content);
    	return trim($content);
    }

    /**
     * 过滤用户输入文本中所有HTML标签
     * @param $content string 用户输入的文本
     * @return $ret string  返回被过滤后文本
     */
    function filterHtml($content) {
        $ret = strip_tags($content);
        return $ret;
    }

    /**
     * 过滤中文状态标点符号及火星文,会过滤日文片假名
     * @param $content  string  需要过滤的字符串
     * @return $ret string 过滤后的字符串
     */
    function filterChineseCode($content) {
        $ret = "";
        $chars = array();
        //为是否为中文标点做标记
        $is_code = false;
        $length = iconv_strlen($content,"GBK");
        for ($i=0; $i<$length; $i++) {
            $chars[] = iconv_substr($content, $i, 1, "GBK");
        }

        foreach($chars as $char){

            for($byte = 0xA0; $byte<= 0xA9; $byte++) {
                if(strlen($char) == 2 && ord($char[0]) == $byte) {
                    $is_code = true;
                    continue;
                }
            }
            if(!$is_code) {
                $ret .= $char;
            }
            //回溯标记位
            $is_code = false;
        }
        return $ret;
    }
}

class Trie {
    //默认序列化后字典存放路径
    var $default_out_path ;
    //默认原始字典存放路径
    var $default_dict_path ;
    //节点数组。每个节点为二元组，依次为是否叶子节点，子节点.
    var $nodes ;

    function Trie($file) {
        $this->default_out_path  = $file['bin'];
        $this->default_dict_path = $file['source'];
    }

    /**
     * 构建树，存储序列化文本等操作封装
     * @param $path  string 字典存放位置
     * @param $out_path  string 序列化后存放位置
     * @return $ret mixed 是否成功，不成功返回false
     */
    function build($path = null, $out_path = null) {
        if(empty($path)) {
            $path = $this->default_dict_path;
        }
        if(empty($out_path)) {
            $out_path = $this->default_out_path;
        }

        $words = $this->getDict($path);
        $tree = $this->getTree($words);
        $ret = $this->putBinaryDict($out_path, $tree);
        $a = true;
        return $ret;
    }

    /**
     * 用于提供查找关键字的方法
     * @param $content string 需要查找的文本
     * @param $dict_path 序列化字典路径
     * @return $matchs array 查找到的关键字和权重
     */
    function search($content, $dict_path) {
        if(empty($dict_path)) {
            $dict_path = $this->default_out_path;
            $ifUpperCase = 1;
        }else{
        	$ifUpperCase = 0;
        }
        $words = $this->getBinaryDict($dict_path);
		if ($words) {
			$this->nodes = $words;
			$matchs = $this->match($ifUpperCase,$content);
			return $matchs;
		} else {
			return false;
		}
    }

    /**
     * 将文件中的字典逐行放到数组中去
     * @param $path string 字典路径
     * @return $words array 字典
     */
    function getDict($path) {
        $i = 0;
        $words = array();

        $handle = fopen($path, "r");

        if($handle == false) {
            return $words;
        }
        while(!feof($handle)) {
            $words[$i] = trim(fgets($handle));
            $i++;
        }
        fclose($handle);
        return $words;
    }

    /**
     * 获取序列化后的字典并反序列化
     * @param $path string 序列化字典存放路径
     * @return $words array 反序列化后的数组
     */
    function getBinaryDict($path = null) {
        if(empty($path)) {
            $path = $this->default_out_path;
        }
		$words = readover($path);
        if(!$words) {
            return array();
        }
        $words = unserialize ($words);
        return $words;
    }

    /**
     * 将字典序列化后保存到文件中
     * @param $path string 保存路径
     * @param $words array 数组形式的字典
     * @return $ret mixed 没有保存成功返回false
     */
    function putBinaryDict($path, $words) {
        if(empty($path)) {
            $path = $this->default_out_path;
        }
        if(!$words) {
            return ;
        }
        $words = serialize($words);
        $handle = fopen($path, 'wb');
        $ret = fwrite($handle, $words);
        if($ret == false) {
            return false;
        }
        fclose($handle);
        return $ret;

    }

    /**
     * 构建树的过程方法
     * @param $words array 字典和权重数组
     */
    function getTree($words) {
        $this->nodes = array( array(false, array()) ); //初始化，添加根节点
        $p = 1; //下一个要插入的节点号
        foreach ($words as $word) {
			$cur = 0; //当前节点号
			//preg_match('/^(.*?)\s+(.*)/i', $word, $weight); //提取关键字和权重
			//$weight = explode("|", $word);
			//$word = trim($weight[0]);
			list($word, $weight, $replace) = $this->split($word);
			for ($len = strlen($word), $i = 0; $i < $len; $i++) {
				$c = ord($word[$i]);
				if (isset($this->nodes[$cur][1][$c])) { //已存在就下移
					$cur = $this->nodes[$cur][1][$c];
					continue;
				}
				$this->nodes[$p]= array(false, array()); //创建新节点
				$this->nodes[$cur][1][$c] = $p; //在父节点记录子节点号
				$cur = $p; //把当前节点设为新插入的
				$p++; //
			}
			$this->nodes[$cur][0] = true; //一个词结束，标记叶子节点
			$this->nodes[$cur][2] = trim($weight); //将权重放在叶子节点
			$this->nodes[$cur][3] = trim($replace);
		}
		return $this->nodes;
	}

	function split($str) {
		if (($pos = strrpos($str, '|')) === false) {
			return array($str, 0);
		}
		return explode('|',$str);
	}

    /**
     * 用于搜索关键字的方法
     * @param $s string 需要查找的文本
     * @return $ret array 查找到的关键词及权重
     */
    function match($ifUppCase,$s) {
    	$ifUppCase == 1 && $s = strtolower($s);
        $isUTF8 = strtoupper(substr($GLOBALS['db_charset'],0,3)) === 'UTF' ? true : false;
        $ret = array();
        $cur = 0; //当前节点，初始为根节点
        $i = 0; //字符串当前偏移
        $p = 0; //字符串回溯位置
        $len = strlen($s);
        while($i < $len) {
            $c = ord($s[$i]);
            if (isset($this->nodes[$cur][1][$c])) { //如果存在
                $cur = $this->nodes[$cur][1][$c]; //下移当前节点
                if ($this->nodes[$cur][0]) { //是叶子节点，单词匹配！
                    $ret[$p] = array(substr($s, $p, $i - $p + 1), $this->nodes[$cur][2]); //取出匹配位置和匹配的词以及词的权重
                    $p = $i + 1; //设置下一个回溯位置
                    $cur = 0; //重置当前节点为根节点
                }
				$i++; //下一个字符
            } else { //不匹配
				$cur = 0; //重置当前节点为根节点
                if (!$isUTF8 && ord($s[$p]) > 127 && ord($s[$p+1]) > 127) {
					$p += 2; //设置下一个回溯位置
				} else {
					$p += 1; //设置下一个回溯位置
				}
				$i = $p; //把当前偏移设为回溯位置
            }
        }
        return $ret;    
    }

 	function replaces($s,$ifUppCase = 0) {
    	$ifUppCase && $s = strtolower($s);
        $isUTF8 = strtoupper(substr($GLOBALS['db_charset'],0,3)) === 'UTF' ? true : false;
        $ret = array();
        $cur = 0; //当前节点，初始为根节点
        $i = 0; //字符串当前偏移
        $p = 0; //字符串回溯位置
        $len = strlen($s);
        while($i < $len) {
            $c = ord($s[$i]);
            if (isset($this->nodes[$cur][1][$c])) { //如果存在
                $cur = $this->nodes[$cur][1][$c]; //下移当前节点
                if ($this->nodes[$cur][0]) { //是叶子节点，单词匹配！
                    $s = ($this->nodes[$cur][2] == 0.6 && isset($this->nodes[$cur][3])) ? substr_replace($s, $this->nodes[$cur][3], $p, $i - $p + 1) : $s; //取出匹配位置和匹配的词以及词的权重
                    $p = $i + 1; //设置下一个回溯位置
                    $cur = 0; //重置当前节点为根节点
                }
				$i++; //下一个字符
            } else { //不匹配
				$cur = 0; //重置当前节点为根节点
                if (!$isUTF8 && ord($s[$p]) > 127 && ord($s[$p+1]) > 127) {
					$p += 2; //设置下一个回溯位置
				} else {
					$p += 1; //设置下一个回溯位置
				}
				$i = $p; //把当前偏移设为回溯位置
            }
        }
        return $s;    
    }
}

/**
 * 根据给定词语权重对文档进行评分，目前使用Bayes算法，不考虑词频影响
 * 算法如下：
 * 假设文档中有分词t1,t2,t3,……tn,其权重分别为w1,w2,w3,……,wn
 * 则根据Bayes算法，文档权重为：
 * 设p1 = w1*w2*w3*……*wn
 * 设p2 = (1-w1)*(1-w2)*(1-w3)*……*(1-wn)
 * 则文档权重 w = p1/(p1+p2)
 * 如果p1+p2=0,文档权重为1
 * 权重低于0.5的关键词会降低整体权重，大于0.5则会提高整体权重
 * 如0.9, 0.8, 0.5, 0.6 经过Bayes计算后权重为0.98，
 * 而0.9, 0.8, 0.5, 0.1 经过计算后权重仅为0.8
 */
class Bayes {

    /**
     * 获取文章权重
     * @param $keys 文档中匹配的关键词数组及权重信息
     * @return  $weight 经过Bayes算法处理过的权重
     */
    function getWeight($keys) {
		//print_r($keys);
        $p1 = 1;
        $p2 = 1;
        foreach($keys as $key) {
            if( empty($key[1]) ) {
                continue;
            }
            $weight = floatval($key[1]);
            $p1 *= $weight;
            $p2 *= (1- $weight);
        }
        if( ($p1 + $p2) == 0 ) {
            $weight = 1;
            return $weight;
        }

        $weight = $p1 / ($p1 + $p2);
        return $weight;
    }
}
?>