<?php
/**
 * create by liaohu 2010-06-11
 */
!defined('P_W') && exit('Forbidden');
S::gp(array('fid','tid'), null, 2);
/**
 * 实例化类，输出执行结果
 * @var unknown_type
 */
$commend = new commend($fid);
$commend->delItemFromList($tid);
echo $commend;
ajax_footer();

/**
 * 定义类
 * @author hu.liaoh
 */
class commend{
	/**
	 * 定义变量
	 * @var unknown_typell
	 */
	var $table;
	var $commend;
	var $forumset;
	var $db;
	var $result;
	var $fid;
	var $msg; 
	
	/**
	 * 构造函数，初始化变量
	 * @param $fid
	 * @return unknown_type
	 */
	function commend($fid){
		$this->msg = array('success'=>false,'msg');
		$this->table = 'pw_forumsextra';
		$this->commend = null;
		$this->forumset = null;		
		$this->db = $GLOBALS['db'];
		$this->result = null;
		$this->fid = $fid;
		
		$this->init();
	}
	/**
	 * 类初始化
	 * init
	 */
	function init(){
		$this->getResult();
	}
	/**
	 * 获取Forumset
	 * @return unknown_type
	 */
	function setForumset(){
		$this->forumset = unserialize($this->result['forumset']);
	}
	/**
	 * 获取commend
	 * @return unknown_type
	 */
	function setCommend(){
		$this->commend = unserialize($this->result['commend']);
	}
	/**
	 * 根据版块id获取相关推荐帖信息
	 * @return unknown_type
	 */
	function getResult(){
		$sql = "SELECT forumset,commend FROM " . $this->table . " WHERE fid=" . S::sqlEscape($this->fid);
		$this->result = $this->db->get_one($sql);
		if($this->result){
			$this->setForumset();
			$this->setCommend();
		}
	}
	/**
	 * 更新删除结果
	 * @return unknown_type
	 */
	function setResult(){
		require_once(R_P.'admin/cache.php');
		/**
		 * 更新数据库 
		 * @var unknown_type
		 */
		$sql = "UPDATE " . $this->table . " SET ".
			S::sqlSingle(array(
				"forumset" => serialize($this->forumset),
				"commend" => serialize($this->commend)
			))
		. "WHERE fid=" . S::sqlEscape($this->fid);
		if($this->db->update($sql)){
			$this->setMsg(true);
		}
		/**
		 * 更新缓存
		 */
		updatecache_f();
	}
	/**
	 * 从commendlist中删除推荐帖tid
	 * @param $tid
	 * @return unknown_type
	 */
	function delItemFromList($tid){
		$cmdlist = explode(",", $this->forumset['commendlist']);
		$pos = array_search($tid, $cmdlist);
		if(false !== $pos){
			$cmdlist = $this->delInArray($cmdlist, $pos);
			$this->forumset['commendlist'] = implode(",", $cmdlist);
		}		
		$this->delCommend($tid);
	}
	/**
	 * 删除推荐帖内容
	 * @param $tid
	 * @return unknown_type
	 */
	function delCommend($tid){
		foreach($this->commend as $key=>$value){
			if($tid == $value['tid']){
				$this->commend = $this->delInArray($this->commend, $key);
				break;
			}
		}
		$this->setResult();
	}
	/**
	 * 更新消息
	 * @param $success
	 * @param $msg
	 * @return unknown_type
	 */
	function setMsg($success,$msg = ''){
		$this->msg = array('success'=>$success,'msg'=>$msg);
	}
	/**
	 * 从数组中删除
	 * @param $arr
	 * @param $pos
	 * @param $num
	 * @return unknown_type
	 */
	function Delinarray($arr, $pos, $num = 1){
		array_splice($arr, $pos, $num);
		return $arr;
	}
	/**
	 * 输出消息
	 * @return unknown_type
	 */
	function __toString(){
		return pwJsonEncode($this->msg);
	}
}
