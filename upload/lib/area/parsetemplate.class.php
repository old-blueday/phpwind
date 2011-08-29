<?php
!defined('P_W') && exit('Forbidden');
class PW_ParseTemplate{
	var $string;
	var $sign;
	var $type;

	function PW_ParseTemplate(){

	}
	function setType($type) {
		$this->type = $type;
	}
	function setString(&$string){
		$this->string	=& $string;
	}
	function setSign($sign) {
		$this->sign = $sign;
	}
	function execute($type,$sign,$string){
		$this->setType($type);
		$this->setSign($sign);
		$this->setString($string);
		$this->tplParsePW();
		$this->tplParsePrint();
		return $this->string;
	}
	function tplParsePW(){
		$invokeService = $this->_getinvokeService();
		$reg = $this->_pregPW();
		$replace = array();
		foreach ($reg[1] as $id=>$val) {
			$parsePW = $this->_initParesPW($val);
			$pwName	= $this->_getPWName($parsePW);
			if (!$pwName) continue;
			$temp = $invokeService->getInvokeByName($pwName);
			if (!$temp) {
				Showmsg($pwName.'parse pw error');
			}
			$replace[$id]	= $this->_adminDiv($temp['parsecode'],$pwName,$temp['title']);
		}
		$this->string = str_replace($reg[0],$replace,$this->string);
	}
	
	function _getPareseCode($name) {
		$channelService = $this->_getChannelService();
		$parseCode = $channelService->getParseCode($this->channel,$name);
	}
	
	function _getPWName($parsePW) {
		if ($parsePW['id']) return $parsePW['id'];
		return false;
	}
	
	function _initParesPW($string) {
		$temp = array();
		preg_match_all("/[a-zA-Z\-_]+\s?=\s?(['|\"]?).*?(\\1)/",$string,$match);
		foreach ($match[0] as $pwReg) {
			$pos = strpos($pwReg,"=");
			$key = trim(strtolower(substr($pwReg,0,$pos)));
			if (!in_array($key,array('id','tplid'))) continue;
			$value = trim(substr($pwReg,$pos+1));
			if (preg_match("/^('|\")(.*?)(\\1)$/",$value,$newValue)) {
				$value = trim($newValue[2]);
			}
			$temp[$key] = $value;
		}
		return $temp;
	}

	function _pregPW() {
		preg_match_all('/<pw([^>]+?)\/>/is',$this->string,$reg);
		return $reg;
	}

	function tplParsePrint() {
		$this->string = tplParsePrint($this->string);
	}

	function _adminDiv($string,$id,$title) {
		$temp	= '<div class="view-hover" invokename="'.($title ? $title : $id).'" altname="'.$id.'" channelid="'.$this->sign.'">';
		$temp	.= $string;
		$temp	.= '</div>';
		return $temp;
	}

	function _getinvokeService() {
		return L::loadClass('invokeservice', 'area');
	}
	function _getChannelService() {
		return L::loadClass('channelservice', 'area');
	}
}
?>