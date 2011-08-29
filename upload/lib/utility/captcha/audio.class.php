<?php
/**
 * 语音验证码
 */
!defined('P_W') && exit('Forbidden');

class PW_Audio {
	/**
	 * @var string	$_audioPath 	语音文件存放目录'/'结尾
	 * @var string  $_code			 验证码
	 * @var string	$_audioFormat 	音频格式
	 * @var string	$_interference 	是否干扰, 1-是, 0-否
	 */
	
	var $_audioPath;
	var $_code;
	var $_audioFormat = 'mp3';
	var $_interference = 0;
	
	/**
	 * 设置语音文件路径
	 * @param $audioPath	路径
	 * @return bool			true-成功, false-失败
	 */
	function setAudioPath($audioPath) {
		if (empty($audioPath)) return false;
		$this->_audioPath = S::escapePath($audioPath);
		return true;
	}
	
	/**
	 * 设置验证码
	 * @param $code			验证码
	 * @return bool			true-成功, false-失败
	 */
	function setCode($code) {
		if (empty($code)) return false;
		$this->_code = $code;
		return true;
	}
	
	/**
	 * 设置音频格式
	 * @param $audioFormat	音频格式
	 * @return bool			true-成功, false-失败
	 */
	function setAudioFormat($audioFormat) {
		if (!S::inArray($audioFormat, array('wav', 'mp3'))) return false;
		$this->_audioFormat = $audioFormat;
		return true;
	}
	
	/**
	 * 设置是否干扰
	 * @param $state		1-是, 0-否
	 * @return bool			true-成功
	 */
	function setInterference($state) {
		if ($state !==1 && $state !== 0) return false;
		$this->_interference = $state;
		return true;
	}
	
	/**
	 * 输出语音
	 */
	function outputAudio() {
		if ($this->_audioFormat == 'wav') {
			$contentType = 'audio/x-wav';
			$type = 'wav';
		} elseif ($this->_audioFormat == 'mp3') {
			$contentType = 'audio/mpeg';
			$type = 'mp3';
		}
		if (empty($contentType)) return false;
		header("Content-type: $contentType");
		$audioContent = ($type == 'wav') ? $this->_getWavAudio() : $this->_getMp3Audio();
		$this->_interference == 1 && $audioContent = $this->_addInterference($audioContent);
		header('Content-Length: ' . strlen($audioContent));
		echo $audioContent;
		exit;
	}
	
	/**
	 * 生成wav格式的语音验证码
	 * @return $outputData	语音数据
	 */
	function _getWavAudio() {
		if (empty($this->_code)) return false;
		$audioData = array();
		$totalDataLength = '';
		for ($i = 0; $i < strlen($this->_code); $i++) {
			$wavFile = $this->_audioPath . strtoupper($this->_code[$i]) . '.wav';
			$wavData = readover($wavFile);
			$headerInfo = substr($wavData, 0, 36);
			$data = unpack('Nriffid/Vfilesize/Nfiletype/Nfmtid/Vfmtsize/vformattag/vchannels/Vsamplespersec/Vbytespersec/vblockalign/vbitspersample', $headerInfo);
			$data['filesize'] = $data['filesize'] + 8;
			$trunkLength = $data['fmtsize'] == 18 ? 46 : 44;
			$data['datainfo'] = substr($wavData, $trunkLength);
			if (($position = strpos($data['datainfo'], 'LIST')) !== false) {
				$data['filesize'] = $data['filesize'] - (strlen($data['datainfo']) - $position);
				$data['datainfo'] = substr($data['datainfo'], 0, $position);
			}
			$totalDataLength += strlen($data['datainfo']);
			$audioData[] = $data;
		}
		$outputData = '';
		foreach ($audioData as $key => $value) {
			if ($key == 0) {
				$wavHeader = pack('C4VC4', ord('R'), ord('I'), ord('F'), ord('F'), $totalDataLength + 36, ord('W'), ord('A'), ord('V'), ord('E'));
				$wavHeader .= pack('C4VvvVVvv',
					ord('f'),
					ord('m'),
					ord('t'),
					ord(' '),
					16,
					$value['formattag'],
					$value['channels'],
					$value['samplespersec'],
					$value['bytespersec'],
					$value['blockalign'],
					$value['bitspersample']
				);
				$wavHeader .= pack('C4V', ord('d'), ord('a'), ord('t'), ord('a'), $totalDataLength);
				$outputData .= $wavHeader;
			}
			$outputData .= $value['datainfo'];
		}
		return $outputData;
	}
	
	/**
	 * 生成mp3格式的语音验证码
	 * @return $outputData	语音数据
	 */
	function _getMp3Audio() {
		if (empty($this->_code)) return false;
		$outputData = '';
		for ($i = 0; $i < strlen($this->_code); $i++) {
			$wavFile = $this->_audioPath . strtoupper($this->_code[$i]) . '.mp3';
			$wavData = readover($wavFile);
			$outputData .= $wavData;
		}
		return $outputData;
	}
	
	/**
	 * 加干扰
	 * @param 	$audioData	音频数据
	 * @return 	$audioData	处理后的数据
	 */
	function _addInterference($audioData) {
		if ($this->_audioFormat == 'wav') {
			$startpos = strpos($audioData, 'data') + 8;	//wav格式的音频数据
			$startpos += rand(1, 32);
		} elseif ($this->_audioFormat == 'mp3') {
			$startpos = 4;	//没有ID3V2标签
			if (stripos($audioData, 'ID3') !== false) {
				$startpos = 24; //ID3V2头跟帧
				($pos = stripos($audioData, '3DI')) !== false && $startpos = $pos + 14; //更准确的获取音频数据
			}
		}
		$dataLength = strlen($audioData) - $startpos - 128;	//末128个字节是MP3格式的ID3V1标签
		for ($i = $startpos; $i < $dataLength; $i += 256) {
			$ord = ord($audioData[$i]);
			if ($ord < 17 || $ord > 111) continue;
			$audioData[$i] = chr($ord + rand(-16, 16));
		}
		return $audioData;
	}
}
?>