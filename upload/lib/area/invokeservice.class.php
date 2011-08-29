<?php
!defined('P_W') && exit('Forbidden');
/*
dbs:
	pw_invoke
	pw_invokepiece
*/
class PW_InvokeService {
	var $_invokeDB;
	var $_invokePieceDB;

	/**	pw_invoke **/
	function getInovkes($page, $prePage) {
		$invokeDB = $this->_getInvokeDB();
		return $invokeDB->getInvokes($page, $prePage);
	}

	function deleteInvoke($name) {
		$invokeDB = $this->_getInvokeDB();
		$invokeDB->deleteByName($name);
		$this->deleteInovkePieceByInvokeName($name);
	}
	
	function getInvokeById($id) {
		$id = (int) $id;
		if (!$id) return array();
		$invokeDB = $this->_getInvokeDB();
		return $invokeDB->getDataById($id);
	}

	function getInvokeByName($invokename) {
		$invokeDB = $this->_getInvokeDB();
		return $invokeDB->getDataByName($invokename);
	}

	function updateInvokeByName($name, $data) {
		$invokeDB = $this->_getInvokeDB();
		$invokeDB->updateByName($name, $data);
	}

	function getInovkesByNames($names) {
		$invokeDB = $this->_getInvokeDB();
		return $invokeDB->getDatesByNames($names);
	}

	function addInvoke($name, $tagCode,$type,$sign) {
		$invokeDB = $this->_getInvokeDB();
		$invokePieceDB = $this->_getInvokePieceDB();
		$parseTagCode = L::loadClass('ParseTagCode', 'area');
		$parseTagCode->init($name, $tagCode);
		$parsecode = $parseTagCode->getParseCode();
		
		$invokepiece = $parseTagCode->getConditoin();
		if ($invokepiece) {
			$invokePieceDB->insertDatas($invokepiece);
		}
		$data = array('name' => $name, 'tagcode' => $tagCode, 'parsecode' => $parsecode,'title'=>$name,'scr'=>$type,'sign'=>$sign);
		$data['pieces'] = $this->getInvokePieces($name);
		$invokeDB->insertData($data);
	}

	function updateInvokeTagCode($name, $tagCode) {
		$temp = $this->getInvokeByName($name);
		
		$parseTagCode = L::loadClass('ParseTagCode', 'area');
		$parseTagCode->init($name, $tagCode);
		$parsecode = $parseTagCode->getParseCode();
		$newInvokePieces = $parseTagCode->getConditoin();
		
		$this->_updateInvokePieceTagCode($name, $newInvokePieces);
		$data = array('tagcode' => $tagCode, 'parsecode' => $parsecode);
		$data['pieces'] = $this->getInvokePieces($name);
		$this->updateInvokeByName($name, $data);
		P_unlink($this->getInvokeApiFile($temp['id']));
	}
	
	function updateInvokeTitle($name,$title) {
		$this->updateInvokeByName($name, array('title' => $title));
	}
	
	function invokeApiFileEot($id) {
		$file = $this->getInvokeApiFile($id);
		if (!file_exists($file)) {
			writeover($file, "<?php\r\nprint <<<EOT\r\n".$invokeInfo['parsecode']."\r\nEOT;\r\n?>");
		}
	}
	
	function getInvokeApiFile($id) {
		$id = (int) $id;
		return D_P.'data/tplcache/invoke_name_id_'.$id;
	}

	function getChannelInvokesForSelect($alias, $ifverify = 0,$ifHTML=0) {
		return $this->getPageInvokesForSelect('channel', $alias, $ifverify,$ifHTML);
	}
	
	function getPortalInvokesForSelect($alias, $ifverify = 0,$ifHTML=0) {
		return $this->getPageInvokesForSelect('other', $alias, $ifverify,$ifHTML);
	}

	function getPageInvokesForSelect($scr, $sign, $ifverify = 0,$ifHTML=0) {
		$temp = array();
		$invokes = $this->getEffectPageInvokes($scr, $sign, $ifverify);
		foreach ($invokes as $invoke) {
			if (!$invoke['pieces'] && !$ifHTML) continue; 
			$temp[$invoke['name']] = array('invokename'=>$invoke['name'],'title'=>$invoke['title'],'pieces'=>$invoke['pieces']);
		}
		return $temp;
	}
	function getEffectPageInvokePieces($scr, $sign) {
		$pieces = array();
		$temp = $this->getEffectPageInvokes($scr, $sign);
		$i = 0;
		foreach ($temp as $value) {
			if ($value['pieces'] && is_array($value['pieces'])) {
				$pieces = array_merge($pieces, array_keys($value['pieces']));
			}
		}
		return $pieces;
	}
	
	function getEffectPageInvokes($scr, $sign, $ifverify = 0) {
		$invokeDB = $this->_getInvokeDB();
		return $invokeDB->getEffectPageInvokes($scr, $sign, $ifverify);
	}
	
	function deleteUnuseInvoke($invokename) {
		$invokeDB = $this->_getInvokeDB();
		$invokeInfo = $invokeDB->getDataByName($invokename);
		if ($invokeInfo && $invokeInfo['state']) {
			$invokeDB->deleteByName($invokename);
		}
	}
	
	function updatePageInvokesState($scr, $sign, $invokeNames, $state) {
		$invokeDB = $this->_getInvokeDB();
		$invokeDB->updatePageInvokesState($scr, $sign, $invokeNames, $state);
	}
	
	function searchPageInvokes($array, $page, $preg = 20) {
		$invokeDB = $this->_getInvokeDB();
		return $invokeDB->searchPageInvokes($array, $page, $preg);
	}
	function sreachPageInvokesPages($array, $page, $url, $preg = 20) {
		$invokeDB = $this->_getInvokeDB();
		$page = (int) $page;
		if ($page < 1)
			$page = 1;
		$total = $invokeDB->searchCount($array);
		$numofpage = ceil($total / $preg);
		$numofpage < 1 && $numofpage = 1;
		$page > $numofpage && $page = $numofpage;
		
		return numofpage($total, $page, $numofpage, $url);
	}
	
	function getChannelPageInvokes($sign) {
		$invokeDB = $this->_getInvokeDB();
		return $invokeDB->getPageInvokes('channel',$sign);
	}
	
	function getPortalPageInvokes($sign) {
		$invokeDB = $this->_getInvokeDB();
		return $invokeDB->getPageInvokes('other',$sign);
	}

	function _updateInvokePieceTagCode($name, $newInvokePieces) {
		$oldInvokePieces = $this->getInvokePieceByInvokeName($name);
		$this->_updateOldPieceCacheData($oldInvokePieces);
		
		foreach ($newInvokePieces as $newpiece) {
			$oldInvokePieces = $this->_cookOldInvokePieces($oldInvokePieces,$newpiece);
		}
		if (!$oldInvokePieces) return '';
		foreach ($oldInvokePieces as $key => $value) {
			$this->deleteInvokePieceById($value['id']);
		}
	}
	function _updateOldPieceCacheData($pieces) {
		foreach ($pieces as $key=>$value) {
			$this->_updatePieceCacheData($key);
		}
	}
	function _cookOldInvokePieces($oldInvokePieces,$newpiece) {
		$mark = 0;
		foreach ($oldInvokePieces as $key => $oldpiece) {
			if ($newpiece['title'] != $oldpiece['title']) continue;
			if ($newpiece['action'] != $oldpiece['action']) {
				$newpiece['cachetime'] = 1800;
			}
			$mark = 1;
			$this->updateInvokePieceById($oldpiece['id'], $newpiece);
			unset($oldInvokePieces[$key]);
			break;
		}
		if (!$mark) {
			$this->insertInvokePiece($newpiece);
		}
		return $oldInvokePieces;
	}

	/**	pw_invokepiece **/
	function getInvokePieceByInvokeId($id) {
		$invokePieceDB = $this->_getInvokePieceDB();
		return $invokePieceDB->getDataById($id);
	}

	function getInvokePieceByInvokeName($invokename) {
		$invokePieceDB = $this->_getInvokePieceDB();
		return $invokePieceDB->getDatasByInvokeName($invokename);
	}

	function getInvokePieces($invokename) {
		$pieces = $this->getInvokePieceByInvokeName($invokename);
		$temp = array();
		foreach ($pieces as $piece) {
			$temp[$piece['id']] = $piece['title'];
		}
		return $temp;
	}


	function getInvokePieceForSetConfig($invokename) {
		$invokepieces = $this->getInvokePieceByInvokeName($invokename);
		$pieceOperate = L::loadClass('pieceoperate', 'area');
		foreach ($invokepieces as $key => $piece) {
			$piece = $pieceOperate->initPiece($piece);
			$invokepieces[$key] = $piece;
		}
		return $invokepieces;
	}

	function getInvokePiecesByInvokeNames($names) {
		$invokePieceDB = $this->_getInvokePieceDB();
		return $invokePieceDB->getDatasByInvokeNames($names);
	}

	function getInvokePieceByNameAndTitle($invokename, $title) {
		$invokePieceDB = $this->_getInvokePieceDB();
		return $invokePieceDB->getDataByInvokeNameAndTitle($invokename, $title);
	}

	function updateInvokePieceById($id, $array) {
		$invokePieceDB = $this->_getInvokePieceDB();
		$invokePieceDB->updateById($id, $array);
	}

	function insertInvokePiece($array) {
		$invokePieceDB = $this->_getInvokePieceDB();
		return $invokePieceDB->insertData($array);
	}

	function insertInvokePieces($array) {
		$invokePieceDB = $this->_getInvokePieceDB();
		$invokePieceDB->insertDatas($array);
	}

	function deleteInovkePieceByInvokeName($name) {
		$pushdataService = $this->_getPushdataService();
		
		$invokePieces = $this->getInvokePieceByInvokeName($name);
		foreach ($invokePieces as $value) {
			$pushdataService->deletePushdataByPiece($value["id"]);
		}
		
		$invokePieceDB = $this->_getInvokePieceDB();
		$invokePieceDB->deleteByInvokeName($name);
	}

	function deleteInvokePieceById($id) {
		$pushdataService = $this->_getPushdataService();
		$pushdataService->deletePushdataByPiece($id);
		$invokePieceDB = $this->_getInvokePieceDB();
		$invokePieceDB->deleteById($id);
	}

	function updateInvokePieces($array) {
		if (!is_array($array) || !$array)
			return false;
		foreach ($array as $key => $value) {
			if (!is_array($value))
				return false;
			$this->_updateInvokePiece($value);
		}
	}

	function _updateInvokePiece($array) {
		if (!isset($array['invokename']) || !isset($array['title']))
			return false;
		$temp = $this->getInvokePieceByNameAndTitle($array['invokename'], $array['title']);
		if (!$temp)
			return false;
		$this->updateInvokePieceById($temp['id'], $array);
		$this->_updatePieceCacheData($temp['id']);
	}
	
	function _updatePieceCacheData($pieceId) {
		$cacheDataService = L::loadClass('cachedataservice', 'area');
		$cacheDataService->updateCacheDataPiece($pieceId);
	}
	
	function _getPushdataService() {
		return L::loadClass('pushdataservice','area');
	}

	/** getDBs **/
	function _getInvokeDB() {
		return L::loadDB('Invoke', 'area');
	}

	function _getInvokePieceDB() {
		return L::loadDB('InvokePiece', 'area');
	}
}
?>