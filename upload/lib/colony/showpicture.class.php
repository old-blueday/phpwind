<?php
!defined('P_W') && exit('Forbidden');

/**
 * 图片展示类
 *
 * @package Forum
 */
class PW_ShowPicture {
	var $_perPage = 5;
	var $_db;

	function PW_ShowPicture($register) {
		$this->_db = $GLOBALS['db'];
		$this->_register($register);
	}

	function _register($register) {
		$this->_db_shield = &$register['db_shield'];
		$this->_groupid = &$register['groupid'];
		$this->_pwModeImg = &$register['pwModeImg'];

	}

	function getPictures($pictureId) {
		$photo = $this->_getPicture($pictureId);
		if (!$photo) {
			return $this->_getEmpty();
		}
		$pictureIds = $this->_getPictureIds($photo['aid']);
		if (!$pictureIds) {
			return $this->_getEmpty();
		}
		list($needPictureIds, $prePid, $nextPid) = $this->_calculate($pictureId, $pictureIds);
		if (!$needPictureIds) {
			return $this->_getEmpty();
		}
		$pictures = $this->_buildPictures($needPictureIds);
		return array(
			$photo,
			$pictures,
			$prePid,
			$nextPid
		);
	}

	function getGroupsPictures($pictureId) {
		$photo = $this->_getGroupsPicture($pictureId);
		if (!$photo) {
			return $this->_getEmpty();
		}
		$pictureIds = $this->_getPictureIds($photo['aid']);
		if (!$pictureIds) {
			return $this->_getEmpty();
		}
		list($needPictureIds, $prePid, $nextPid) = $this->_calculate($pictureId, $pictureIds);
		if (!$needPictureIds) {
			return $this->_getEmpty();
		}
		$pictures = $this->_buildPictures($needPictureIds);
		return array(
			$photo,
			$pictures,
			$prePid,
			$nextPid
		);
	}

	function _calculate($pictureId, $pictureIds) {
		$total = count($pictureIds);

		$center = ceil(($this->_perPage - 1) / 2);
		// 二分居中法
		$currentKey = array_search($pictureId, $pictureIds);

		$preKey = isset($pictureIds[$currentKey - 1]) ? $currentKey - 1 : 0;
		$prePid = $pictureIds[$preKey];
		$nextKey = isset($pictureIds[$currentKey + 1]) ? $currentKey + 1 : $total - 1;
		$nextPid = $pictureIds[$nextKey];
		if ($total <= $this->_perPage) {
			return array(
				$pictureIds,
				$prePid,
				$nextPid
			);
		}
		if ($currentKey - $center <= 0) {
			$start = 0;
		} elseif ($currentKey + $center >= $total) {
			$start = $total - $this->_perPage;
		} else {
			$start = $currentKey - $center;
		}
		$end = $this->_perPage;
		$needPictureIds = array_slice($pictureIds, $start, $end);
		return array(
			$needPictureIds,
			$prePid,
			$nextPid
		);
	}

	function _buildPictures($needPictureIds) {
		$pictures = $this->_getPicturesByPicturesIds($needPictureIds);
		if (!$pictures) {
			return null;
		}
		$photos = array();
		foreach ($pictures as $photo) {
			$photo['path'] = getphotourl($photo['path'], $photo['ifthumb']);
			if ($album['groupid'] == 6 && $this->_db_shield && $this->_groupid != 3) { //TODO $album?
				$photo['path'] = $this->_pwModeImg . '/banuser.gif';
			}
			$photos[] = $photo;
		}
		return $photos;
	}

	function _getPicturesByPicturesIds($pictureIds) {
		if (!is_array($pictureIds)) {
			return null;
		}
		$pictureIds = implode(",", $pictureIds);
		$query = $this->_db->query("SELECT pid,path,ifthumb FROM pw_cnphoto WHERE pid in(" . $pictureIds . ") ORDER BY pid DESC ");
		$result = array();
		while ($rs = $this->_db->fetch_array($query)) {
			$result[] = $rs;
		}
		return $result;
	}

	function _getAlbumByPictureId($pictureId) {
		return $this->_db->get_one("SELECT * FROM pw_cnphoto p LEFT JOIN pw_cnalbum a USING(aid) WHERE pid=" . S::sqlEscape($pictureId));
	}

	function _getAlbumByAlbumId($albumId) {
		return $this->_db->get_one("SELECT * FROM pw_cnalbum  WHERE aid=" . S::sqlEscape($albumId) . " LIMIT 1");
	}

	function _getPictureIds($ablumId) {
		$query = $this->_db->query("SELECT pid FROM pw_cnphoto WHERE aid=" . S::sqlEscape($ablumId) . " ORDER BY pid ASC LIMIT 200");
		$result = array();
		while ($rs = $this->_db->fetch_array($query)) {
			$result[] = $rs['pid'];
		}
		return $result;
	}

	function _getEmpty() {
		return array(
			"",
			"",
			"",
			""
		);
	}

	function _getPicture($pictureId) {
		return $this->_db->get_one("SELECT p.pid,p.aid,p.pintro,p.path as basepath,p.uploader,p.uptime,p.hits,p.c_num,p.ifthumb,a.aname,a.private, a.ownerid,a.owner,a.photonum,m.groupid FROM pw_cnphoto p LEFT JOIN pw_cnalbum a ON p.aid=a.aid LEFT JOIN pw_members m ON p.uploader=m.username WHERE p.pid=" . S::sqlEscape($pictureId) . " AND a.atype='0'");
	}

	function _getGroupsPicture($pictureId) {
		return $this->_db->get_one("SELECT p.pid,p.aid,p.pintro,p.path as basepath,p.ifthumb,p.uploader,p.uptime,p.hits,a.aname,a.atype, a.private,a.ownerid,a.photonum,a.memopen,a.aintro,m.groupid FROM pw_cnphoto p LEFT JOIN pw_cnalbum a ON p.aid=a.aid LEFT JOIN pw_members m ON p.uploader=m.username WHERE p.pid=" . S::sqlEscape($pictureId));
	}

	function _getConnection() {
		return $GLOBALS['db'];
	}

}
?>