<?php
!function_exists('readover') && exit('Forbidden');
$filename=D_P.'data/bbscache/today.php';
$dbtdsize=100;
if(file_exists($filename)){
	$todaydata=readover($filename);
	if($offset=strpos($todaydata,"\n".$windid."\t")){/*使用精确匹配 必须是"\n".$windid."\t"*/
		$offset+=1;
		if($fp=@fopen($filename,"rb+")){
			flock($fp,LOCK_EX);
			list($node,$yestime)=nodeinfo($fp,$dbtdsize,$offset);/*修改头结点*/
			$nowfp=$offset/($dbtdsize+1);
			if("$nowfp"!=$node && $node!=''){
				fputin($fp,$node,$dbtdsize,$nowfp);/*修改头结点指向的数据段*/
				list($oldprior,$oldnext)=fputin($fp,$nowfp,$dbtdsize,'node',$node);/*修改需要更新的数据*/
				if($oldprior!='node'){
					fputin($fp,$oldprior,$dbtdsize,'M',$oldnext);/*修改前一结点的后趋*/
				}
				if($oldnext!='NULL' && $oldprior!='node'){
					fputin($fp,$oldnext,$dbtdsize,$oldprior);/*修改后一结点的前趋*/
				}
			}
			fclose($fp);
		}
	}else{
		$offset=filesize($filename);
		if($fp=@fopen($filename,"rb+")){
			flock($fp,LOCK_EX);
			list($node,$yestime)=nodeinfo($fp,$dbtdsize,$offset);
			if($node!=''){/*修改头结点*/
				$nowfp=$offset/($dbtdsize+1);
				if($node!='NULL') {
					fputin($fp,$node,$dbtdsize,$nowfp);
				}
				if($node!=$nowfp) fputin($fp,$nowfp,$dbtdsize,'node',$node,Y);/*添加数据*/
			}
			fclose($fp);
		}
	}
}
if($yestime!=$tdtime) {
	//* P_unlink($filename);
	pwCache::deleteData($filename);
	pwCache::setData($filename,str_pad("<?php die;?>\tNULL\t$tdtime\t",$dbtdsize)."\n");/*24小时初始化一次*/
}
function fputin($fp,$offset,$dbtdsize,$prior='M',$next='M',$ifadd='N')
{
	$offset=$offset*($dbtdsize+1);/*将行数转换成指针偏移量*/
	fseek($fp,$offset,SEEK_SET);
	if($ifadd=='N'){
		$iddata=fread($fp,$dbtdsize);
		$idarray=explode("\t",$iddata);
		fseek($fp,$offset,SEEK_SET);
	}
	if($next!='M' && $prior!='M'){/*说明这一数据是被更改的数据段.需要对其他辅助信息进行更改*/
		global $windid,$timestamp,$onlineip,$winddb;
		$idarray[0]=$windid;$idarray[3]=$winddb['regdate'];
		if($ifadd!='N') $idarray[4]=$timestamp;
		$idarray[5]=$timestamp;$idarray[6]=$onlineip;$idarray[7]=$winddb['postnum'];$idarray[8]=$winddb['rvrc'];
	}
	if($prior=='M') $prior=$idarray[1];
	if($next=='M') $next=$idarray[2];
	$data="$idarray[0]\t$prior\t$next\t$idarray[3]\t$idarray[4]\t$idarray[5]\t$idarray[6]\t$idarray[7]\t$idarray[8]\t";
	$data=str_pad($data,$dbtdsize)."\n";/*定长写入*/
	fwrite($fp,$data);
	return array($idarray[1],$idarray[2]);/*传回数据更新前的上一结点和下一结点*/
}
function nodeinfo($fp,$dbtdsize,$offset)
{
	$offset=$offset/($dbtdsize+1);
	$node=fread($fp,$dbtdsize);
	$nodedb=explode("\t",$node);/*头结点在第二个数据段*/
	if(is_int($offset)){
		$nodedata=str_pad("<?php die;?>\t$offset\t$nodedb[2]\t",$dbtdsize)."\n";
		fseek($fp,0,SEEK_SET);/*将指针放于文件开头*/
		fwrite($fp,$nodedata);
		return array($nodedb[1],$nodedb[2]);
	}else{
		return '';
	}
}
?>