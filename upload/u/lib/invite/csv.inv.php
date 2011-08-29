<?php
!defined('P_W') && exit('Forbidden');
/**
 * foxmail邮件联系人导入
 * @package  PW_Csv 
 * @author suqian
 */
class INV_Csv{
	
	var $_filename;
	var $_uid = 0;
	var $_emailList = array();
	var $_fp = NULL;

	
	function INV_Csv(){
		global $winduid;
		$this->_uid = $winduid;
	}
	/**
	 * 上传csv文件到服务器端
	 *
	 */
	function _uploadCsv(){
		L::loadClass('csvupload', 'upload', false);
		$csvupload = new CsvUpload($this->_uid);
		PwUpload::upload($csvupload);
		$this->_filename = $csvupload->pathname;
	}
	
	 /**
	 * 取得foxmail联系人email列表
	 */ 
	function getEmailAddressList(){		
		$this->_uploadCsv();
		$point = $this->_getPoint();
		if(empty($point) || !isset($point['email'])){
			return array();
		}
		$this->_open();
		$filesize = filesize($this->_filename);
		$i = 0;
		while($data = fgets($this->_fp, $filesize)){
			$data = explode(',',$data);
			if($i != 0){	
				$value = '';
				$key = $data[$point['email']];
				$key = str_replace('"','',$key);
				$point['nick'] && $value = $data[$point['nick']];
				!$value &&  $value = $data[$point['name']];
				!$value &&  $value = $data[$point['email']];						
				$key && $this->_emailList[$key] = $value;
			}
			$i++;
		}
		$this->_close();
		$this->_del();
		return $this->_emailList;
	}
	/**
	 * 得到foxmail中邮件、昵称、姓名列表
	 * @return array 返回所须数据
	 */ 
	function _getPoint(){
		if(!$this->_isCsv()){
			return array();
		}
		$array = array();
		$this->_open();
		$filesize = filesize($this->_filename);
		$i = 0;
		while($data = fgets($this->_fp, $filesize)){
			$array = $this->_result($i,$data,$array);
			if($i > 2){
				break;
			}
			$i++;
		}
		$this->_close();
		return $array;
	}
	
	function _result($i,$data,$array=array()){
		$data = explode(',',$data);
		$count = count($data);
		for($j = 0;$j<$count;$j++){
			if($i == 0){
				switch($data[$j]){
					case "昵称":$array['nick'] = $j;break;
					case "姓名":$array['name'] = $j;break;
					case "电子邮件地址":$array['email'] = $j;break;
					default:break;
				}
				continue;
			}
			if($data[$array['email']] && $this->_isEmail($data[$array['email']])){
				break;
			}
			if($this->_isEmail($data[$j])){
				$array['email'] = $j;
				break;
			}
		}
		return $array;
	}
	/**
	 * 判断是foxmail邮箱联系人列表导出文件是否是csv格式
	 *
	 */ 
	function _isCsv(){		
		if(!$this->_filename || !is_file($this->_filename)){
			return false;
		}
		$ext = strtolower(substr(strrchr($this->_filename, '.'), 1));
		if(!in_array($ext,array('csv'))){
			return false;
		}
		return true;
	}
	/**
	 * 对Email格式进行验证
	 */ 
	function _isEmail($email){
		return preg_match("/\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*/i",$email);	
	}
	
	/**
	 *打开文件
	 * @param string $method 打开文件方式
	 */ 
	function _open($method = 'r'){
		if(!is_resource($this->_fp)){
			$this->_fp = fopen($this->_filename,$method);			
		}
	}
	/**
	 * 关闭文件
	 */ 
	function _close(){
		if(is_resource($this->_fp)){
			fclose($this->_fp);
			$this->_fp = NULL;
		}
	}
	/**
	 * 从服务器删除用户上传文件
	 */ 
	function _del(){
		if($this->_filename && is_file($this->_filename)){
			P_unlink($this->_filename);
		}
	}
}
?>