<?php
!function_exists('readover') && exit('Forbidden');

//* include_once pwCache::getPath(D_P.'data/bbscache/newinfo_config.php');
pwCache::getData(D_P.'data/bbscache/newinfo_config.php');
foreach ($nf_order as $key => $val) {
	$updatetime = $nf_order[$key]['updatetime'] ? $nf_order[$key]['updatetime'] : 30;
	if ($val['order'] && $val['cachetime']+$updatetime<$timestamp && $val['type'] != 'custom' && ($val['type'] != 'newpic' || !$val['mode'])) {
		$element = L::loadClass('element');
		$element->setDefaultNum($nf_config['shownum']);
		if ($val['type'] == 'newpic' && !$val['mode']) {
			$nf_newinfodb[$key] = $element->newPic(0,5);
			foreach ($nf_newinfodb[$key] as $k => $v) {
				$nf_newinfodb[$key][$k] = array('id'=>$v['addition']['tid'],'name'=>substrs($v['title'],$nf_config['titlelen'],'N'),'value'=>$v['image']);
			}
		} elseif ($val['type'] == 'info' && $val['mode']) {
			$info = $element->getInfo();
			$bbsinfo = explode(',',$val['mode']);
			unset($nf_newinfodb[$key]);
			foreach ($bbsinfo as $val) {
				$nf_newinfodb[$key][$val] = $info[$val];
			}
		} elseif (in_array($val['type'],array('newpost','newreply','digest','hits','replies','newfavor','hotfavor'))) {
			switch ($val['type']) {
				case 'newpost' :
					$nf_newinfodb[$key] = $element->newSubject();break;
				case 'newreply' :
					$nf_newinfodb[$key] = $element->newReply();break;
				case 'digest' :
					$nf_newinfodb[$key] = $element->digestSubject();break;
				case 'hits' :
					$nf_newinfodb[$key] = $element->hitSort();break;
				case 'replies' :
					$nf_newinfodb[$key] = $element->replySort();break;
				case 'newfavor' :
					$nf_newinfodb[$key] = $element->newFavorsort();break;
				case 'hotfavor' :
					$nf_newinfodb[$key] = $element->hotFavorsort();break;
			}
			foreach ($nf_newinfodb[$key] as $k => $v) {
				$nf_newinfodb[$key][$k] = array(
					'id'		=> $v['addition']['tid'],
					'name'		=> substrs($v['title'],$nf_config['titlelen'],'N'),
					'value'		=> $v['value'],
					'addition'	=> array(
						'fid'		=> $v['addition']['fid'],
						'author'	=> $v['addition']['author'],
						'authorid'	=> $v['addition']['authorid'],
						'type'		=> $v['addition']['type'],
						'hits'		=> $v['addition']['hits'],
						'replies'	=> $v['addition']['replies'],
						'favors'	=> $v['addition']['favors']
					)
				);
			}
		} elseif ($val['type'] == 'new') {
			$nf_newinfodb[$key] = $element->getMembers('new');
			foreach ($nf_newinfodb[$key] as $k => $v) {
				$nf_newinfodb[$key][$k] = array('id'=>$v['addition']['uid'],'name'=>$v['title'],'value'=>$v['value']);
			}
		} elseif (in_array($val['type'],array('todaypost','rvrc','postnum','onlinetime','monthpost','monoltime','money','digests','currency','credit')) || array_key_exists($val['type'],$_CREDITDB)) {
			$nf_newinfodb[$key] = $element->userSort($val['type']);
			foreach ($nf_newinfodb[$key] as $k => $v) {
				$nf_newinfodb[$key][$k] = array('id'=>$v['addition']['uid'],'name'=>$v['title'],'value'=>$v['value']);
			}
		} elseif (in_array($val['type'],array('topic','article','tpost'))) {
			$nf_newinfodb[$key] = $element->forumSort($val['type']);
			foreach ($nf_newinfodb[$key] as $k => $v) {
				$nf_newinfodb[$key][$k] = array('id'=>$v['addition']['fid'],'name'=>$v['title'],'value'=>$v['value']);
			}
		}
		$nf_order[$key]['cachetime'] = $timestamp;
		uasort($nf_order,'cacheTimeCmp');
		pwCache::setData(D_P.'data/bbscache/newinfo_config.php',"<?php\r\n\$nf_config=".pw_var_export($nf_config).";\r\n\$nf_newinfodb=".pw_var_export($nf_newinfodb).";\r\n\$nf_order=".pw_var_export($nf_order).";\r\n?>");
		break;
	}
}
uasort($nf_order,'orderCmp');

function cacheTimeCmp($row1,$row2){
	$first = strcmp($row1['cachetime'], $row2['cachetime']);
	if ($first) {
		return $first;
	} elseif ($row2['order'] > $row1['order']) {
       return 1;
   } elseif ($row2['order'] < $row1['order']) {
		return -1;
   } else {
		return 0;
   }
}
function orderCmp($row1,$row2){
	if ($row2['order'] > $row1['order']) {
		return 1;
	} elseif ($row2['order'] < $row1['order']) {
		return -1;
	} else {
		return strcmp($row1['cachetime'], $row2['cachetime']);
	}
}
?>
