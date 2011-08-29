<?php 
!defined('P_W') && exit('Forbidden');

/**
 * 注册,新用户引导校验
 *
 */
class PW_RegisterCheck {
	
/**
 * 校验用户名
 * @param $username 用户名
 * @return int
 */	
	function checkUsername($username) {
		global $rg_config;
		L::loadClass('register', 'user', false);
		if (!PW_Register::checkNameLen(strlen($username))) {
			return 1;
			//ajax_footer();
		}
		$S_key = array("\\",'&',' ',"'",'"','/','*',',','<','>',"\r","\t","\n",'#','%','?','　');
		foreach ($S_key as $value) {
			if (strpos($username,$value) !== false) {
				return 2;
				//ajax_footer();
			}
		}
		if (!$rg_config['rg_rglower'] && !PW_Register::checkRglower($username)) {
			return 3;
			//ajax_footer();
		}

		$banname = explode(',',$rg_config['rg_banname']);
		foreach ($banname as $value) {
			if ($value !== '' && strpos($username,$value) !== false) {
				return 2;
				//ajax_footer();
			}
		}

		require_once(R_P . 'uc_client/uc_client.php');
		if (uc_user_get($username)) {
			return 4;
		} else {
			return 0;
		}
		
	}

/**
 * 校验邮箱
 * @param $email 邮箱
 * @return int
 */	
	function checkEmail($email) {
		global $rg_config;
		if (!$email || !preg_match("/\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*/i", $email)) {
			return 1;
			//ajax_footer();
		}

		if ($rg_config['rg_emailtype'] == 1 && $rg_config['rg_email']) {
			$e_check = 0;
			$e_limit = explode(',', $rg_config['rg_email']);
			foreach ($e_limit as $key => $val) {
				if (strpos($email,"@".$val) !== false) {
					$e_check = 1;
					break;
				}
			}
			if ($e_check == 0){
				return 4;
				//ajax_footer();
			}
		}


		if ($rg_config['rg_emailtype'] == 2 && $rg_config['rg_banemail']){
			$e_check = 0;
			$e_limit = explode(',', $rg_config['rg_banemail']);
			foreach ($e_limit as $key => $val) {
				if (strpos($email,"@".$val) !== false) {
					$e_check = 1;
					break;
				}
			}
			if ($e_check == 1){
				return 5;
				//ajax_footer();
			}
		}

		require_once(R_P . 'uc_client/uc_client.php');
		if (uc_user_get($email, 2)) {
			return 2;
		} else {
			return 0;
		}
		
	}
	
/**
 * 校验验证码
 * @param $gdcode 验证码
 * @return int
 */		
	function checkGdcode($gdcode) {
		if (!$gdcode || !SafeCheck(explode("\t",StrCode(GetCookie('cknum'),'DECODE')),strtoupper($gdcode),'cknum',1800,false,false)) {
			return 1;
		} else {
			return 0;
		}
	}

/**
 * 校验验证问题
 * @param $anser 答案
 * @param $question 问题
 * @return int
 */	
	function checkQanswer($answer, $question) {
		global $db_answer;
		if (!$question || ( $question > 0 && $answer != $db_answer[$question]) || ($question < 0 && !SafeCheck(explode("\t", StrCode(GetCookie('ckquestion'), 'DECODE')), $answer, 'ckquestion', 1800,false,false))) {
			return 1;
		} else {
			return 0;
		}
	}
	
/**
 * 校验邀请码
 * @param $invcode 邀请码
 * @return int
 */	
	function checkInvcode($invcode) {
		global $db;
		if (empty($invcode)) {
			return 1;
		} else {
			$inv_config['inv_days'] *= 86400;
			$inv = $db->get_one("SELECT id FROM pw_invitecode WHERE invcode=" . S::sqlEscape($invcode) . " AND ifused<'1' AND createtime>" . S::sqlEscape($timestamp - $inv_config['inv_days']));
			if (!$inv) {
				return 2;
			} else {
				return 0;
			}
		}
	}

/**
 * 校验自定义字段
 * @param $fieldname 字段名
 * @param $value 值
 * @return int
 */	
	function checkCustomerField($fieldname,$value) {
		if (empty($value)) {
			return 1;
		} else {
			$customfieldService = L::loadClass('CustomerFieldService','user');
			$result = $customfieldService->checkData($fieldname,$value,true);
			$result = $result === true ? 0 : ($result === false ? 1 : $result);
			return $result;
		}
	}
}
?>