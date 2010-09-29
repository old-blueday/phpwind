<?php
!function_exists('readover') && exit('Forbidden');

function expinfo($agent) 
{
	$expser="";$expserver="";
    //$agent = $GLOBALS["HTTP_USER_AGENT"];
	if (ereg("Mozilla",$agent) && ereg("MSIE",$agent))
	{
		$temp = explode("(", $agent); $anc=$temp[1];
		$temp = explode(";",$anc); $anc=$temp[1];
		$temp = explode(" ",$anc);$expserver=$temp[2];
		$expserver =preg_replace("/([\d\.]+)/","\\1",$expserver);
		$expserver = " $expserver";
		$expser = "Internet Explorer";
	}
	elseif (ereg("Mozilla",$agent) && !ereg("MSIE",$agent)) 
	{
		$temp =explode("(", $agent); $anc=$temp[0];
        $temp =explode("/", $anc); $expserver=$temp[1];
        $temp =explode(" ",$expserver); $expserver=$temp[0];
        $expserver =preg_replace("/([\d\.]+)/","\\1",$expserver);
        $expserver = " $expserver";
        $expser = "Netscape Navigator";
    }
	if ($expser!="") 
	{
		$expseinfo = "$expser$expserver";
	}
	else
	{
		$expseinfo = "Unknown";
	}
	return $expseinfo;
}
//会员操作系统
function sysinfo($agent)
{
	$sys="";
	//$agent = $GLOBALS["HTTP_USER_AGENT"];
	if (eregi('win',$agent) && eregi('nt 5\.1',$agent))
	{
		$sys="Windows XP";
	}
	elseif (eregi('win',$agent) && ereg('98',$agent))
	{
		$sys="Windows 98";
	}
	elseif (eregi('win',$agent) && eregi('nt 5\.0',$agent))
	{
		$sys="Windows 2000";
	}
	elseif (eregi('win 9x',$agent) && strpos($agent, '4.90')) 
	{
		$sys="Windows ME";
	}
	elseif (eregi('win',$agent) && strpos($agent, '95')) 
	{
		$sys="Windows 95"; 
    }
	elseif (eregi('win',$agent) && eregi('nt',$agent)) 
	{
		$sys="Windows NT";
    }
	elseif (eregi('win',$agent) && ereg('32',$agent)) 
	{
		$sys="Windows 32";
	}
	elseif (eregi('linux',$agent)) 
	{
		$sys="Linux";
	}
	elseif (eregi('unix',$agent)) 
	{
		$sys="Unix";
	}
	elseif (eregi('ibm',$agent) && eregi('os',$agent)) 
	{
		$sys="IBM OS/2";
	}
	elseif (eregi('NetBSD',$agent)) 
	{
		$sys="NetBSD";
	}
	elseif (eregi('BSD',$agent)) 
	{
		$sys="BSD";
	}
	elseif (eregi('FreeBSD',$agent)) 
	{
		$sys="FreeBSD";
	}
	else
		$sys = "Unknown";
	return $sys;
}
?>