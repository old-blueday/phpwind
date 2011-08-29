<?php
!function_exists('readover') && exit('Forbidden');
/**
 * 热门关键字搜索
 * @author luomingqu 2011-1-20
 * @version phpwind 8.5
 */
class PW_HotwordsSearcher {
	var $_maxNum = 50;
	
	function update($autoInvoke, $num = 10) {
		$num = intval($num);
		$num = $num > $this->_maxNum ? $this->_maxNum : $num;
		if ($autoInvoke['isOpne']) return $this->updateByAutoInvoke($num, $autoInvoke['period']);
		return $this->updateByCustom($num);
	}
	
	function updateByAutoInvoke($num = 10, $period = 7) {
		$num = intval($num);
		if ( $num < 1 ) return false;
		$tempHotwords = $this->_getSearchHotwordsByNumAndFromType($num);
		$customNum = 0;
		$tempcustom = $tempAuto = array();
		foreach ($tempHotwords as $key=>$value) {
			if ($value['fromtype'] == 'auto') {
				$tempAuto[] = $value;
			} elseif ($value['fromtype'] == 'custom') {
				$tempcustom[] = $value;
				$customNum ++ ;
			}
			$_lastViveorder = $value['vieworder'];
		}
		
		$autoNum = $num - $customNum;
		if ($autoNum) {
			$period = intval($period);
			$keywordsTops = $this->_getUseKeywordsTimesByPeriodAndNum($period, $autoNum);
			$tempAutoInvoke = $this->_updateAutoInvoke($tempAuto, $keywordsTops, $_lastViveorder+1);
			$tempHotwords = array_merge($tempcustom,$tempAutoInvoke);
		}
		
		$hotwords = $this->_buildHotwords($tempHotwords);
		return $this->_updateCache($hotwords);
	}	
	
	function updateByCustom($num = 10) {
		$num = intval($num);
		if ( $num < 1 ) return false;
		$tempHotwords = $this->_getSearchHotwordsByNumAndFromType($num, 'custom');
		$hotwords = $this->_buildHotwords($tempHotwords);
		return $this->_updateCache($hotwords);
	}
	
	function _buildHotwordsSort($data) {
		if (!$data || !S::isArray($data)) return array();
		
		foreach ($data as $value) {
			$sortByViewOrder[] = $value['vieworder'];
			$sortById[] = $value['id'];
		}
		array_multisort($sortByViewOrder, SORT_ASC, $sortById, SORT_DESC, $data);
		return 	$data;
	}
	
	function _updateAutoInvoke($tempAutoInvoke = array(), $keywordsTops = array(), $lastViveorder = 1) {
		global $db,$timestamp;
		if (!$keywordsTops) return array();
		$autoIds = $updateArr = $addArr = array();
	
		if ($tempAutoInvoke) {
			foreach ($tempAutoInvoke as $key=>$value) {
				if (!$keywordsTops[$key]) continue;
				$value['keyword'] = $keywordsTops[$key];
				$autoIds[$key] = $value['keyword'];
				$db->query(" UPDATE pw_searchhotwords SET keyword=".S::sqlEscape($value['keyword'])." WHERE id=".S::sqlEscape($value['id']));
				$updateArr[] = $value; 
			}
			
		}
		$tempAddDb = array_diff($keywordsTops, $autoIds);
		if (!$tempAddDb) return $updateArr;
		$_vieworder = $lastViveorder;
		foreach ($tempAddDb as $value) {
			$addArr[] = array(
							'keyword' 	=> $value,
							'vieworder'	=> $_vieworder,
							'fromtype'	=> 'auto',
							'posttime'	=> $timestamp
						);
			$_vieworder++;
		}
		if (!$addArr) return $updateArr;
		$db->update ("INSERT INTO pw_searchhotwords(keyword,vieworder,fromtype,posttime) VALUES " . S::sqlMulti($addArr));
		return array_merge($updateArr,$addArr);
	}

	function _getSearchHotwordsByNumAndFromType($num, $fromtype = '') {
		global $db;
		$num = intval($num);
		if ( $num < 1 ) return array();
		$result = array();
		$_wheresql = '';
		$_wheresql = $fromtype ? " AND fromtype = ".S::sqlEscape($fromtype) : '';
		$_sql = " SELECT * FROM pw_searchhotwords WHERE 1 $_wheresql ORDER BY vieworder ASC ".S::sqlLimit($num);
		$query = $db->query($_sql);
		while ($rt = $db->fetch_array($query)) {
			$result[] = $rt;
		}
		return $result;
	}
		
	function _updateCache($hotwords) {
		global $timestamp;
		require_once (R_P . 'admin/cache.php');
		if (!$hotwords) {
			setConfig ('db_hotwords', '');
			return updatecache_c();
		}
		setConfig ('db_hotwords', $hotwords);
		setConfig('db_hotwordlasttime', $timestamp);
		updatecache_c();
		return true;
	}
	
	function _buildHotwords($data) {
		if (!$data) return '';
		$result = $this->_buildHotwordsSort($data);
		$hotwords = '';
		$filterService = L::loadClass('FilterUtil', 'filter');
		foreach ($result as $value) {
			if (($GLOBALS['banword'] = $filterService->comprise($value['keyword'])) !== false) continue;
			$hotwords .= $value['keyword'] . ",";
		}
		$hotwords = trim($hotwords, ',');
		return $hotwords;
	}
	
	
	function _getUseKeywordsTimesByPeriodAndNum($period , $num = 10) {
		global $timestamp,$db;
		$result = array();
		$period = intval($period);
		$num = intval($num);
		if ($period < 1 || $num < 1) return array();
		$_time = $timestamp - (24*3600*$period);
		$_sql = " SELECT keyword,num FROM pw_searchstatistic WHERE created_time >=".S::sqlEscape($_time);
		$query = $db->query($_sql);
		while ($rt = $db->fetch_array($query)) {
			$result[$rt[keyword]] += $rt[num];
		}
			
		if (!$result) return array();
		arsort($result);
		$result = array_keys($result);
		return array_slice($result,0,$num);      
	}
}