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

	function addInvoke($name, $tagCode) {
		$invokeDB = $this->_getInvokeDB();
		$invokePieceDB = $this->_getInvokePieceDB();
		$parseTagCode = L::loadClass('ParseTagCode', 'area');
		$parseTagCode->init($name, $tagCode);
		$parsecode = $parseTagCode->getParseCode();
		
		$invokeDB->insertData(array('name' => $name, 'tagcode' => $tagCode, 'parsecode' => $parsecode,'title'=>$name));
		
		$invokepiece = $parseTagCode->getConditoin();
		if ($invokepiece) {
			$invokePieceDB->insertDatas($invokepiece);
		}
	}

	function updateInvokeTagCode($name, $tagCode) {
		$temp = $this->getInvokeByName($name);
		
		$parseTagCode = L::loadClass('ParseTagCode', 'area');
		$parseTagCode->init($name, $tagCode);
		$parsecode = $parseTagCode->getParseCode();
		
		$newInvokePieces = $parseTagCode->getConditoin();
		
		$this->updateInvokeByName($name, array('tagcode' => $tagCode, 'parsecode' => $parsecode));
		$this->_updateInvokePieceTagCode($name, $newInvokePieces);
	}
	
	function updateInvokeTitle($name,$title) {
		$this->updateInvokeByName($name, array('title' => $title));
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