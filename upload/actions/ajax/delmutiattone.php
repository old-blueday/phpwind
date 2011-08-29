<?php
!defined('P_W') && exit('Forbidden');

$aid = (int) S::getGP('aid');
S::gp(array('type'));

if ($aid <= 0) {
	echo "error";ajax_footer();
}

$delfileServer = getDelfileFactory($type);
if (!$delfileServer->delete($aid)) {
	echo "error";ajax_footer(); 
}
echo "ok";ajax_footer();

function getDelfileFactory($type) {
	if ($type == 'active') {
		return new activeMutiDelfile();
	}
	if ($type && file_exists(R_P . "require/extents/attach/{$type}MutiDelfile.class.php")) {
		$class = $type . 'MutiDelfile';
		require_once S::escapePath(R_P . "require/extents/attach/{$type}MutiDelfile.class.php");
		return new $class();
	}
	return new threadMutiDelfile();
}

class activeMutiDelfile {

	function delete($aid) {
		global $winduid,$db_ifftp,$db;
		$rt = $db->get_one("SELECT * FROM pw_actattachs WHERE aid=" . S::sqlEscape($aid));
		if (empty($rt)) return true;
		if ($rt['uid'] != $winduid) {
			return false;
		}
		pwDelThreadAtt($rt['attachurl'], $db_ifftp, $rt['ifthumb']);
		$db->update("DELETE FROM pw_actattachs WHERE aid=" . S::sqlEscape($aid) . " AND uid=" . S::sqlEscape($winduid) . " LIMIT 1");
		return true;
	}
}

class threadMutiDelfile {

	function delete($aid) {
		global $winduid,$windid, $db_ifftp, $db;
		$rt = $db->get_one("SELECT aid,uid,fid,tid,pid,attachurl,ifthumb FROM pw_attachs WHERE aid=" . S::sqlEscape($aid));
		if (empty($rt) || $rt['tid'] || $rt['pid'] || $rt['did'] || $rt['mid']) {
			return true;
		}
		if ($rt['uid'] != $winduid) {
			return false;
		}
		pwDelThreadAtt($rt['attachurl'], $db_ifftp, $rt['ifthumb']);
		$db->update("DELETE FROM pw_attachs WHERE aid=" . S::sqlEscape($aid) . " LIMIT 1");
		return true;
	}
}