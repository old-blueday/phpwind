<?php
!defined('P_W') && exit('Forbidden');
class PW_moduleConfigService {
	function PW_moduleConfigService() {
		$this->__construct();
	}
	function __construct() {

	}
	/*
	 * 根据配置文件修改数据库中的配置
	 */
	function updateInvokesByModuleConfig($templateFile,$configFile) {
		$invokeService = $this->_getInvokeService();

		$this->_initPwContainer($templateFile,$configFile);
		$modules = $this->_getModulesFromTemplate($templateFile);
		
		foreach ($modules as $module) {
			$oldModuleInfo = $invokeService->getInvokeByName($module);
			$newModulePiecesCode = $this->_getInvokeStringFromConfigFile($configFile,$module);
			$newModuleTitle = $this->_getModuleTitle($configFile,$module);

			$this->_processModuleTitle($module,$oldModuleInfo,$newModuleTitle);
			$this->_processModuleTagCode($module,$oldModuleInfo,$newModulePiecesCode);
		}
	}
	
	function _processModuleTagCode($module,$oldModuleInfo,$newModulePiecesCode) {		
		if ($oldModuleInfo && $oldModuleInfo['tagcode'] == $newModulePiecesCode) return true;
		
		$invokeService = $this->_getInvokeService();
		if ($oldModuleInfo) {
			$invokeService->updateInvokeTagCode($module,$newModulePiecesCode);
			return true;
		}
		$invokeService->addInvoke($module,$newModulePiecesCode);
		return true;
	}
	
	function _processModuleTitle($module,$oldModuleInfo,$newModuleTitle) {
		if (!$oldModuleInfo || $oldModuleInfo['title'] == $newModuleTitle) return true;
		$invokeService = $this->_getInvokeService();
		$invokeService->updateInvokeTitle($module,$newModuleTitle);
	}
	
	function _getModuleTitle($configFile,$module,$ifCache = 1) {
		$pwLine = $this->_getModulePWLine($configFile,$module,$ifCache);
		if (!$pwLine) return false;
		
		preg_match("/title\s?=\s?(['|\"]?)(.*?)(\\1)/is",$pwLine,$match);
		if ( !$match || !$match[2]) return $module;
		
		return $match[2];
	}
	function _getModulePWLine($configFile,$module,$ifCache = 1) {
		$configFileString = $this->_getFileString($configFile,$ifCache);
		preg_match('/<pw.+?id="'.$module.'".+?\/>/i',$configFileString,$match);
		return $match[0] ? $match[0] : '';
	}
	/**
	 * 获取文件中某个模块的模板
	 * @param $file
	 * @param $invokeName
	 */
	function getPiecesCode($file,$invokeName) {
		return $this->_getInvokeStringFromConfigFile($file,$invokeName);
	}
	/**
	 * 获取文件中某个模块的模板（包含pw头）
	 * @param $file
	 * @param $invokeName
	 */
	function getPieceCodeHavePw($file,$invokeName) {
		return $this->_getInvokeStringFromConfigFile($file,$invokeName,1);
	}

	function _getInvokeStringFromConfigFile($fileName, $invokeName,$ifAll=false) {
		$data = '';
		$start = 0;
		$handle = @fopen($fileName, 'rb');
		
		while(!feof($handle)) {
			$lineString = fgets($handle,4096);
			if (!$start && preg_match('/<pw.+?id="'.$invokeName.'".+?\/>/i',$lineString)) {
				$start = 1;
				if ($ifAll) $data .= $lineString;
				continue;
			}
			if ($start && preg_match('/<pw.+?\/>/i',$lineString)) break;
			if ($start) $data .= $lineString;
		}
		fclose($handle);
		return $data;
	}
	/**
	 * 更新某个文件某个模块的模板
	 * @param string $configFile
	 * @param string $name
	 * @param string $code
	 */
	function updateModuleCode($configFile,$name,$code) {
		$fileString	= $this->_getFileString($configFile);
		$oldModuleString = $this->getPieceCodeHavePw($configFile,$name);
		$newModuleString = $this->_createNewPieceCode($configFile,$name,$code);

		$newString	= str_replace($oldModuleString,$newModuleString,$fileString);
		$temp = pwCache::setData($configFile,$newString,false,'wb+');
		if (!$temp && !is_writable($configFile)) $this->_writeOverMessage($configFile);
		clearstatcache();
	}
	/**
	 * 通过配置更新某个文件某个模块的模板
	 * @param string $configFile
	 * @param string $name
	 * @param string $code
	 */
	function updateModuleByConfig($configFile,$name,$pieceConfig,$title) {
		if (!is_array($pieceConfig)) return false;
		$tagCode = $this->getPiecesCode($configFile,$name);
		$newCode = $this->_createPriceCode($tagCode,$pieceConfig);
		$this->updateModuleCode($configFile,$name,$newCode);
		$this->updateModuleTitle($configFile,$name,$title);
		$this->_updateModuleDBConfig($pieceConfig);
	}
	
	function _updateModuleDBConfig($pieceConfig) {
		$invokeService = $this->_getInvokeService();
		$invokeService->updateInvokePieces($pieceConfig);
	}
	/**
	 * 更新某个模块的模块别称
	 * @param $configFile
	 * @param $name
	 * @param $title
	 */
	function updateModuleTitle($configFile,$name,$title) {
		$oldTitle = $this->_getModuleTitle($configFile,$name,0);
		if ($oldTitle == $title || !$oldTitle) return true;
		$pwLine = $this->_getModulePWLine($configFile,$name,0);
		$newPWLine = $this->_createPWCode($name,$title);
		
		$configString = readover($configFile);
		$newString = str_replace($pwLine,$newPWLine,$configString);
		$temp = pwCache::setData($configFile,$newString,false,'wb+');
		if (!$temp && !is_writable($configFile)) $this->_writeOverMessage($configFile);
		return true;
	}
	
	function _checkModuleTitle($configFile,$name,$title) {
		$configString = $this->_getFileString($configFile);
		preg_match("/<pw.*?title\s?=\s?(['|\"]?)(.*?)(\\1)/i",$configString,$match);
	}
	
	function getTagCodeFromPost($tagcode) {
		$mixed = str_replace(array("\0","%00","\r"),'',$tagcode);
		$mixed = preg_replace(
			array('/[\\x00-\\x08\\x0B\\x0C\\x0E-\\x1F]/','/&(?!(#[0-9]+|[a-z]+);)/is'),
			array('','&amp;'),
			$mixed
		);
		$mixed = stripslashes($mixed);
		$mixed = str_replace(array('&#60;','&#61;'),array('<','='),$mixed);
		if ($this->_checkTplContent($mixed)) {
			return false;
		}
		return $mixed;
	}

	function checkScript($tagcode) {
		if (strpos($tagcode,'<script')!==false) {
			return true;
		}
		return false;
	}
	
	function _checkTplContent ($content) {
		return strpos($content,'$') !== false || strpos($content,'EOT;') !== false || strpos($content,'<!--#') !== false || strpos($content,'#-->') !== false || strpos($content,'<<<') !==false || strpos($content,'?>') !== false || strpos($content,'<?') !== false || strpos($content,'write') !== false || strpos($content,'file_put_contents') !== false || strpos($content,'..') !== false;
	}

	
	/**
	 * 解析<pw>sssss</pw>标签
	 * @param $templateFile
	 * @param $configFile
	 */
	function _initPwContainer($templateFile,$configFile) {
		$fileString	= $this->_getFileString($templateFile);
		preg_match_all('/<pw>([^\x00]*?)<\/pw>/i',$fileString,$reg);
		if (!$reg[1]) return;
		
		$configString = $this->_getFileString($configFile);
		$data = "\r\n";
		$newFileString = $fileString;
		foreach ($reg[1] as $key=>$value) {
			$id = $this->_createUniqueId($configFile);
			if (preg_match('/<pw.+?id="'.$id.'"/i',$configString)) continue;
			$pwCode = $this->_createPWCode($id);
			$data .= $pwCode;
			$data .= ((strpos($value,"\n")<2 && strpos($value,"\n")!==false) ? '':"\r\n").$value."\r\n";
			
			$newFileString = str_replace($reg[0][$key],$pwCode,$newFileString);
		}
		$temp = pwCache::setData($configFile,$data, false, 'ab+');
		if (!$temp && !is_writable($configFile)) $this->_writeOverMessage($configFile);
		$temp = pwCache::setData($templateFile,$newFileString,false,'wb+');
		if (!$temp && !is_writable($templateFile)) $this->_writeOverMessage($templateFile);
	}
	
	function _getSignFormConfigFile($file) {
		static $result = false;
		if ($result) {
			return $result;
		}
		if (!strpos($file,PW_PORTAL_CONFIG)) {
			return '';
		}
		$temp = explode('/',$file);
		array_pop($temp);
		$result = end($temp);
		return $result;
	}
	
	function _createUniqueId($file) {
		static $ids=array();
		$invokeService = $this->_getInvokeService();
		$sign = $this->_getSignFormConfigFile($file);
		 
		$id = $this->_randString().$this->_randInt().$sign;
		if (in_array($id,$ids) || $invokeService->getInvokeByName($id)) return $this->_createUniqueId($file);
		$ids[] = $id;
		return $id;
	}
	
	function _randString() {
		return chr(rand(65,90));
	}
	function _randInt() {
		return rand(1,9999);
	}
	/**
	 * 读取文件内容，当$ifCache为真时，则缓存内容
	 * @param $file
	 * @param $ifCache
	 */
	function _getFileString($file,$ifCache = 0) {
		static $fileString = array();
		if (!$ifCache) {
			return readover($file);
		}
		$fileMD5String = md5($file);
		if (!isset($fileString[$fileMD5String])) {
			$fileString[$fileMD5String] = readover($file);
		}
		return $fileString[$fileMD5String];
	}
	/**
	 * 处理模块的id
	 * @param $string
	 * @param $cateName
	 */
	function cookModuleIds($string,$cateName) {
		preg_match_all('/(<pw.+?id="(.+?)"[^>]+?\/>)/i',$string,$reg);
		$search = $replace = array();
		foreach ($reg[2] as $k=>$v) {
			$search[] = $reg[1][$k];
			$newModuleName = $this->_getNewModuleName($v,$cateName);
			$replace[] = str_replace($v,$newModuleName,$reg[1][$k]);
		}
		return str_replace($search,$replace,$string);
	}
	function _getNewModuleName($name,$cateName) {
		if (!strpos($name,'@')) {
			$temp = $name.'@'.$cateName;
		} else {
			$_position = strpos($name,'@');
			$temp = substr($name,0,$_position+1).$cateName;
		}
		return $temp;
	}
	
	function _getModulesFromTemplate($templteFile) {
		$fileString	= readover($templteFile);
		preg_match_all('/<pw.+?id="(.+?)".+?\/>/i',$fileString,$reg);
		return $reg[1];
	}
	function _createNewPieceCode($configFile,$name,$code) {
		$pwLine = $this->_getModulePWLine($configFile,$name,0);
		$newString = $pwLine."\r\n".$code;
		if (preg_match("/(\n)$/is",$newString)) return $newString;
		return $newString."\r\n";
	}
	function _createPWCode($name,$title='') {
		$temp = '<pw id="'.$name.'"';
		if ($title) {
			$temp .= ' title="'.$title.'"';
		}
		$temp .= ' />';
		return $temp;
	}

	function _createPriceCode($tagCode,$pieces) {
		$temp = $tagCode;
		foreach ($pieces as $piece) {
			$temp = $this->_replaceListCode($temp,$piece);
			$temp = $this->_replaceLoopCode($temp,$piece);
		}
		return $temp;
	}
	function _replaceListCode($tagcode,$piece) {
		$replace = '<list ';
		foreach ($piece as $key=>$value) {
			if (!in_array($key,array('title','action','func','num','rang','cachetime','ifpushonly'))) continue;
			$replace .= $key.'="'.$value.'" ';
		}
		$replace .= '/>';
		$search = $this->_getListString($tagcode,$piece['title']);
		return str_replace($search,$replace,$tagcode);
	}
	function _replaceLoopCode($tagcode,$piece) {
		$loopString = $this->_getLoopString($tagcode,$piece['title']);
		preg_match_all('/\{([\w\,\-:\s]+?)\}/',$loopString,$mat);
		$search = $replace = array();
		foreach ($mat[1] as $k=>$v) {
			if (strpos($v,',') === false) continue;
			$pos = strpos($v,",");
			$key = trim(strtolower(substr($v,0,$pos)));
			$search[]	= '{'.$v.'}';
			$replace[]	= '{'.$key.','.$piece['param'][$key].'}';
		}

		$replaceString	= str_replace($search,$replace,$loopString);
		return str_replace($loopString,$replaceString,$tagcode);
	}
	function _getListString($tagcode,$pieceTitle) {
		preg_match('/(<list.+?title="'.$pieceTitle.'".+?\/>)/i',$tagcode,$reg);
		return $reg[1];
	}
	function _getLoopString($tagcode,$pieceTitle) {
		preg_match('/<list.+?title="'.$pieceTitle.'".+?\/>([^\x00]+?<loop>)([^\x00]+?)(<\/loop>)/i',$tagcode,$reg);
		return $reg[2];
	}
	
	function _getInvokeService() {
		return L::loadClass('InvokeService', 'area');
	}
	
	function _writeOverMessage($path) {
		Showmsg('请设置'.str_replace(R_P,'',$path).'文件为可写');
	}
}
?>