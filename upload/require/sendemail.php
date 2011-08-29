<?php
!function_exists('readover') && exit('Forbidden');
//set_time_limit(1000);
//* @include pwCache::getPath(D_P.'data/bbscache/mail_config.php');
extract(pwCache::getData(D_P.'data/bbscache/mail_config.php', false));

$GLOBALS['M_db']= new Mailconfig(
	array(
		'ifopen'=> $ml_mailifopen,
		'method'=> $ml_mailmethod,
		'host'	=> $ml_smtphost,
		'port'	=> $ml_smtpport,
		'auth'	=> $ml_smtpauth,
		'from'	=> $ml_smtpfrom,
		'user'	=> $ml_smtpuser,
		'pass'	=> $ml_smtppass,
		'smtphelo'=>$ml_smtphelo,
		'smtpmxmailname' =>$ml_smtpmxmailname,
		'mxdns'=>$ml_mxdns,
		'mxdnsbak'=>$ml_mxdnsbak
	)
);
Class Mailconfig {
	var $S_method = 1;
	var $smtp;
	function Mailconfig($smtp=array()){
		
		$this->S_method = $smtp['method'];
		if(!$this->smtp['ifopen'] = $smtp['ifopen']) {
			Showmsg('mail_close');
		}
		if ($this->S_method == 1){
			//不用设置
		} elseif($this->S_method == 2){
			$this->smtp['host'] = $smtp['host'];
			$this->smtp['port'] = $smtp['port'];
			$this->smtp['auth'] = $smtp['auth'];
			$this->smtp['from'] = $smtp['from'];
			$this->smtp['user'] = $smtp['user'];
			$this->smtp['pass'] = $smtp['pass'];
		} elseif($this->S_method == 3){
			$this->smtp['port'] = $smtp['port'];
			$this->smtp['auth'] = $smtp['auth'];
			$this->smtp['from'] = $smtp['from'];
			$this->smtp['smtphelo']=$smtp['smtphelo'];
			$this->smtp['smtpmxmailname']=$smtp['smtpmxmailname'];
			$this->smtp['mxdns']=$smtp['mxdns'];
			$this->smtp['mxdnsbak']=$smtp['mxdnsbak'];
			//hacker
		} else{
			//hacker
		}
	}
	function mailmx($email,$retrys=3){
		global $timestamp;
		$domain=substr($email,strpos($email,'@')+1);
		@include (D_P.'data/bbscache/mx_config.php');

		if(!$_MX[$domain] || $timestamp - pwFilemtime(D_P.'data/bbscache/mx_config.php') > 3600*24*10){
			for($i=0;$i<$retrys;$i++){
				$result = $this->GetMax($domain);
				if($result !== false){
					$_MX[$domain]=$result;
					pwCache::writeover(D_P.'data/bbscache/mx_config.php',"<?php\r\n\$_MX=".pw_var_export($_MX).";\r\n?>");
					$this->smtp['tomx']=$result;
					return true;
				}
			}
			return false;
		} else{
			$this->smtp['tomx']=$_MX[$domain];
			return true;
		}
	}
	function GetMax($maildomain){
		$header=pack("H*","000101000001000000000000");
		$end=pack("H*","00000f0001");
		$domain=explode(".",$maildomain);
		$ques='';
		foreach($domain as $value){
			$ques .=pack("Ca*",strlen($value),$value);
		}

		$fp = ($fp=fsockopen("udp://".$this->smtp['mxdns'], 53, $errno, $errstr , 30)) !== false ? $fp : fsockopen("udp://".$this->smtp['mxdnsbak'], 53, $errno, $errstr , 30);
		if(!$fp){
		   return false;
		} else{
		   fwrite($fp, $header.$ques.$end);
		   $data=fread($fp,12);
		   $q=unpack("n*",$data);

		   if(array_shift($q) !=0x0001){
			   return false;
		   }
		   if((array_shift($q) & 0x800F) !=0x8000){
				return false;
		   }
		   if(array_shift($q) !=0x0001){
				return false;
		   }
		   $anum=array_shift($q);
		   if($anum < 0x0001){
				return false;
		   }
		   $aunum=array_shift($q);
		   $aanum=array_shift($q);

		   if(fread($fp,strlen($ques)+5) !== $ques.$end){
			   return false;
		   }
		   $data .= $ques.$end;

		   $rs=array();
		   for($i=0;$i<$anum;$i++){
			   $mxanwer=array();
			   $tdata=fread($fp,1);
			   $tmx=array();
			   $tna=array();
			   $tpre=65535;
			   $compresspos=-1;
			   for($j=0;$j<32;$j++){
					$tq=array_shift(unpack("C",$tdata));
					if(($tq & 192)==192){
						if($compresspos<0){
							$tdata .=fread($fp,1);
							$data .=$tdata;
						} else{
							$tdata=substr($data,$compresspos,2);
						}
						$tq=array_shift(unpack("n*",$tdata));
						$compresspos=($tq & 0x3fff);
						$tdata=substr($data,$compresspos,1);
						continue;
					}
					if($tq==0){
						break;
					} elseif($compresspos>0){
						$tna[]=array_shift(unpack("a*",substr($data,$compresspos+1,$tq)));
						$compresspos +=$tq+1;
						$tdata=substr($data,$compresspos,1);
					} else{
						$tdata=fread($fp,$tq);
						$data .=$tdata;
						$tna[]=unpack("a*",$tdata);
						$tdata=fread($fp,1);
						$data .=$tdata;
					}
			   }
			   $tdata=fread($fp,10);
			   $data .=$tdata;
			   $tq=unpack("n*",$tdata);
			   $tdata=fread($fp,$tq[5]);
			   $data .=$tdata;
			   $tttl=array_shift(unpack("n*",$tq[4]));
			   $tna=implode($tna,".");
			   if($tq[1]===15 && $tq[2]===1){
				   $tpref=array_shift(unpack("n*",substr($tdata,0,2)));
				   $tdata=substr($tdata,2);
				   $compresspos=-1;
				   $tmdata=substr($tdata,0,1);
				   $j=1;
				   for($k=0;$k<32;$k++){
						$tq=array_shift(unpack("C",$tmdata));
						if(($tq & 192)==192){
							if($compresspos<0){
								$tmdata .=substr($tdata,$j,1);
								$j++;
							} else{
								$tmdata=substr($data,$compresspos,2);
							}
							$tq=array_shift(unpack("n*",$tmdata));
							$compresspos=($tq & 0x3fff);
							$tmdata=substr($data,$compresspos,1);
							continue;
						}
						if($tq==0){
							break;
						} elseif($compresspos>0){
							$tmx[]=array_shift(unpack("a*",substr($data,$compresspos+1,$tq)));
							$compresspos +=$tq+1;
							$tmdata=substr($data,$compresspos,1);
						} else{
							$tmdata=substr($tdata,$j,$tq);
							$j +=$tq;
							$tmx[]=array_shift(unpack("a*",$tmdata));
							$tmdata=substr($tdata,$j,1);
							$j++;
						}
				   }
				   $rs['mx'][$tttl][]=implode($tmx,".");
			   }
		   }
		   arsort($rs,SORT_ASC);
		   foreach($rs['mx'] as $key=>$values){
			   arsort($values,SORT_ASC);
			   foreach($values as $value){
				  $mxs[]=$value;
			   }
		   }
		   fclose($fp);
		   return $mxs;
		}
	}
}

function sendemail($toemail,$subject,$message,$additional=null){
	global $M_db,$db_bbsname,$regname,$db_bbsurl,$windid,$winduid,$timestamp,$regpwd,$manager,$db_ceoemail,$fromemail,$pwd_user,$submit,$receiver,$old_title,$fid,$tid,$pwuser,$db_charset,$sendtoname,$db_registerfile;
	!$fromemail && $fromemail = $db_ceoemail;
	!$sendtoname && $sendtoname = $toemail;
	!$windid && $windid = $db_bbsname;
	$subject = stripslashes(getLangInfo('email',$subject));
	$message = stripslashes(getLangInfo('email',$message));
	$additional = getLangInfo('email',$additional);
	$send_subject = "=?$db_charset?B?".base64_encode(str_replace(array("\r","\n"), array('',' '),$subject)).'?=';
	$send_message = chunk_split(base64_encode(str_replace("\r\n.", " \r\n..", str_replace("\n", "\r\n", str_replace("\r", "\n", str_replace("\r\n", "\n", str_replace("\n\r", "\r", $message)))))));
	$send_from = "=?$db_charset?B?".base64_encode($db_bbsname)."?= <$fromemail>";
	$send_to = "=?$db_charset?B?".base64_encode($sendtoname)."?= <$toemail>";
	!empty($additional) && $additional && substr(str_replace(array("\r","\n"),array('','<rn>'),$additional),-4) != '<rn>' && $additional .= "\r\n";
	$additional = "To: $send_to\r\nFrom: $send_from\r\nMIME-Version: 1.0\r\nContent-type: text/html; charset=$db_charset\r\n{$additional}Content-Transfer-Encoding: base64\r\n";
	if($M_db->S_method == 1){
		if(@mail($toemail,$send_subject,$send_message,$additional)){
			return true;
		} else{
			return false;
		}
	} elseif($M_db->S_method == 2){
		if(!$fp=fsockopen($M_db->smtp['host'],$M_db->smtp['port'],$errno,$errstr)){
			Showmsg('email_connect_failed');
		}
		if(strncmp(fgets($fp,512),'220',3)!=0){
			Showmsg('email_connect_failed');
		}
		if($M_db->smtp['auth']){
			fwrite($fp,"EHLO phpwind\r\n");
			while($rt=strtolower(fgets($fp,512))){
				if(strpos($rt,"-")!==3 || empty($rt)){
					break;
				} elseif(strpos($rt,"2")!==0){
					return false;
				}
			}
			fwrite($fp, "AUTH LOGIN\r\n");
			if(strncmp(fgets($fp,512),'334',3)!=0){
				return false;
			}
			fwrite($fp, base64_encode($M_db->smtp['user'])."\r\n");
			if(strncmp(fgets($fp,512),'334',3)!=0){
				return 'email_user_failed';
			}
			fwrite($fp, base64_encode($M_db->smtp['pass'])."\r\n");
			if(strncmp(fgets($fp,512),'235',3)!=0){
				return 'email_pass_failed';
			}
		} else{
			fwrite($fp, "HELO phpwind\r\n");
		}
		$from = $M_db->smtp['from'];
		$from = preg_replace("/.*\<(.+?)\>.*/", "\\1", $from);
		fwrite($fp, "MAIL FROM: <$from>\r\n");
		if(strncmp(fgets($fp,512),'250',3)!=0){
			return 'email_from_failed';
		}
		fwrite($fp, "RCPT TO: <$toemail>\r\n");
		if(strncmp(fgets($fp,512),'250',3)!=0){
			return 'email_toemail_failed';
		}
		fwrite($fp, "DATA\r\n");
		if(strncmp(fgets($fp,512),'354',3)!=0){
			return 'email_data_failed';
		}
		$msg  = "Date: ".Date("r")."\r\n";
		$msg .= "Subject: $send_subject\r\n";
		$msg .= "$additional\r\n";
		$msg .= "$send_message\r\n.\r\n";
		fwrite($fp, $msg);
		$lastmessage = fgets($fp, 512);
		if(substr($lastmessage, 0, 3) != 250)
		{
			Showmsg('email_connect_failed');
		}
		fwrite($fp, "QUIT\r\n");
		fclose($fp);
		return true;
	} elseif($M_db->S_method == 3){
		if(!$M_db->mailmx($toemail)){
			return false;
		}
		foreach($M_db->smtp['tomx'] as $server){
			if(($fp=fsockopen($server,25,$errno,$errstr)) && strncmp(fgets($fp,512),'220',3)==0){
				break;
			}
		}
		fwrite($fp, "HELO ".$M_db->smtp['smtphelo']."\r\n");

		if(strncmp(fgets($fp,512),'250',3)!=0){
			fwrite($fp,"EHLO ".$M_db->smtp['smtphelo']."\r\n");
			while($rt=strtolower(fgets($fp,512))){
				if(strpos($rt,"-")!==3 || empty($rt)){
					break;
				} elseif(strpos($rt,"2")!==0){
					return false;
				}
			}
			fwrite($fp, "AUTH LOGIN\r\n");
			if(strncmp(fgets($fp,512),'334',3)!=0){
				return false;
			}
			fwrite($fp, base64_encode($M_db->smtp['user'])."\r\n");
			if(strncmp(fgets($fp,512),'334',3)!=0){
				return false;
			}
			fwrite($fp, base64_encode($M_db->smtp['pass'])."\r\n");
			if(strncmp(fgets($fp,512),'235',3)!=0){
				return false;
			}
		}
		$from  = $M_db->smtp['smtpmxmailname'];
		$reply = $M_db->smtp['from'];
		fwrite($fp, "MAIL FROM: <$from>\r\n");
		if(strncmp(fgets($fp,512),'250',3)!=0){
			return false;
		}
		fwrite($fp, "RCPT TO: <$toemail>\r\n");
		if(strncmp(fgets($fp,512),'250',3)!=0){
			return false;
		}
		fwrite($fp, "DATA\r\n");
		if(strncmp(fgets($fp,512),'354',3)!=0){
			return false;
		}
		$msg  = "Date: ".Date("r")."\r\n";
		$msg .= "Subject: $send_subject\r\n";
		$msg .= "$additional\r\n";
		$msg .= "$send_message\r\n.\r\n";
		fwrite($fp, $msg);
		if(strncmp(fgets($fp,512),'250',3)!=0){
			return false;
		}
		fwrite($fp, "QUIT\r\n");
		fclose($fp);
		return true;
		//hacker
	} else{
		//hacker
	}
}
?>