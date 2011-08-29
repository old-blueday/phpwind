<?php
!defined('P_W') && exit('Forbidden');

/**
*  群组自有分类处理
* 
*  @package 群组
*  @author zhudong
*/

class GroupStyle {
	
	var $db;

	/** 
	*  Construct
	*/

	function GroupStyle(){
		global $db;
		$this->db =& $db;
	}

	/** 
	*  获取所有的分类
	* 
	*  @return $styledb array 分类的数组 
	*/

	function getAllStyles(){

		$query = $this->db->query("SELECT * FROM pw_cnstyles");
		while ($rt = $this->db->fetch_array($query)) {
			$styledb[$rt['id']] = $rt;
		}
		return $styledb;

	}


	/** 
	*  获取所有开启的分类
	* 
	*  @return $styledb array 分类的数组 
	*/

	function getOpenStyles(){

		$query = $this->db->query("SELECT * FROM pw_cnstyles WHERE ifopen=1");
		while ($rt = $this->db->fetch_array($query)) {
			$styledb[$rt['id']] = $rt;
		}
		return $styledb;

	}


	/** 
	*  获取一级分类id
	* 
	*  @return $styledb array 分类id的数组 
	*/

	function getFirstGradeStyleIds(){

		$query = $this->db->query("SELECT * FROM pw_cnstyles WHERE upid='0'");
		while ($rt = $this->db->fetch_array($query)) {
			$styledb[] = $rt['id'];
		}
		return $styledb;

	}
	
	function getFirstGradeStyles(){
		$temp = array();
		$query = $this->db->query("SELECT * FROM pw_cnstyles WHERE upid='0'");
		while ($rt = $this->db->fetch_array($query)) {
			$temp[$rt['id']] = $rt;
		}
		return $temp;

	}

	

	/** 
	*  通过上级ID获取分类
	* 
	*  @param  $upids   array 上级分类ID数组
	*  @return $styledb array 分类的数组 
	*/

	function getGradeStylesByUpid($upids){
		
		if(empty($upids)) return array();

		$query = $this->db->query("SELECT * FROM pw_cnstyles WHERE upid IN (".S::sqlImplode($upids).") ORDER BY vieworder ASC");
		while ($rt = $this->db->fetch_array($query)) {
			//if($rt['ifopen'] == 0) continue; 
			$styledb[$rt['upid']][$rt['id']] = $rt;
		}
		return $styledb;

	}

	/** 
	*  添加二级分类
	* 
	*  @param  $newSubStyle   array 新添加的二级分类的数组
	*  @return null
	*/

	function addNewSubStyle($newSubStyle) {
		$this->db->update("INSERT INTO pw_cnstyles(cname,ifopen,upid,vieworder) VALUES ". S::sqlMulti($newSubStyle));
	}
}
?>