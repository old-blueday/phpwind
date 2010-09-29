<?php
!defined('P_W') && exit('Forbidden');
define('AJAX',1);

$ajaxurl = EncodeUrl($basename.'&ajax=1');

InitGP(array('step','block_id','stopic_id'));

if (!$block_id || !$stopic_id) showmsg('undefined_error');
$stopic_data = $stopic_service->getSTopicInfoById($stopic_id);
if (!$stopic_data) showmsg('undefined_error');

list(,$block_type,) = explode('_', $block_id);
$block_name = $stopic_service->getBlockById($block_type);

if (!$step) {
	$block	= $stopic_service->getStopicUnitByStopic($stopic_id, $block_id);
	$block_data = $block ? $block['data'] : array();
	if (!$block) {
		$block	= array('stopic_id'=>$stopic_id,'html_id'=>$block_id,'title'=>'');
		$stopic_service->addUnit($block);
	}
	$block_job = '';
	include stopic_use_layout ('ajax');
} else {
	InitGP(array('block_title'));
	$block_title = trim($block_title);
	$block_data = array();
	
	//do it self
	if ('thrd' == $block_type) {
		InitGP(array('url','title'));
		if (is_array($title)) {
			foreach ($title as $k => $v) {
				$v = trim($v);
				if ('' != $v) {
					$block_data[] = array('title'=>$v, 'url'=>trim($url[$k]));
				}
			}
		}
	} elseif ('thrdSmry' == $block_type) {
		InitGP(array('url','title','descrip'));
		if (is_array($title)) {
			foreach ($title as $k => $v) {
				$v = trim($v);
				if ('' != $v) {
					$block_data[] = array('title'=>$v, 'url'=>trim($url[$k]), 'descrip'=>trim($descrip[$k]));
				}
			}
		}
	} elseif ('pic' == $block_type) {
		InitGP(array('url','image'));
		if (is_array($image)) {
			foreach ($image as $k => $v) {
				$v = trim($v);
				if ('' != $v) {
					$block_data[] = array('image'=>$v, 'url'=>trim($url[$k]));
				}
			}
		}
	} elseif ('picTtl' == $block_type) {
		InitGP(array('url','image','title'));
		if (is_array($image)) {
			foreach ($image as $k => $v) {
				$v = trim($v);
				if ('' != $v) {
					$block_data[] = array('image'=>$v, 'url'=>trim($url[$k]), 'title'=>trim($title[$k]));
				}
			}
		}
	} elseif ('picArtcl' == $block_type) {
		InitGP(array('url','image','title','descrip'));
		if (is_array($image)) {
			foreach ($image as $k => $v) {
				$v = trim($v);
				if ('' != $v) {
					$block_data[] = array('image'=>$v, 'url'=>trim($url[$k]), 'title'=>trim($title[$k]), 'descrip'=>trim($descrip[$k]));
				}
			}
		}
	} elseif ('html' == $block_type) {
		$html = ieconvert($_POST['html']);
		if (is_array($html)) {
			foreach ($html as $k => $v) {
				$v = trim($v);
				if ('' != $v) {
					$block_data[] = array('html'=>$v);
				}
			}
		}
	} elseif ('picPlyr' == $block_type) {
		InitGP(array('url','image','title'));
		if (is_array($image)) {
			foreach ($image as $k => $v) {
				$v = trim($v);
				if ('' != $v) {
					$block_data[] = array('image'=>$v, 'url'=>trim($url[$k]), 'title'=>trim($title[$k]));
				}
			}
		}
	} elseif ('nvgt' == $block_type) {
		InitGP(array('url', 'title', 'link_color', 'link_bgcolor'));
		$block_data['link_color'] = $link_color;
		$block_data['link_bgcolor'] = $link_bgcolor;
		if (is_array($title)) {
			foreach ($title as $k => $v) {
				$v = trim($v);
				if ('' != $v) {
					$block_data['nav'][] = array('title'=>$v, 'url'=>trim($url[$k]));
				}
			}
		}
	} elseif ('banner' == $block_type) {
		InitGP(array('banner_image', 'banner_title', 'postion_left', 'postion_top', 'font_style', 'font_size', 'font_color'));
		$block_data = array(
			'image' => $banner_image,
			'title' => $banner_title,
			'title_left' => $postion_left,
			'title_top' => $postion_top,
			'title_style' => $font_style,
			'title_size' => $font_size,
			'title_color' => $font_color,
		);
	} elseif ('spclTpc' == $block_type) {
		InitGP(array('tid', 'height'), null, 2);
		$block_data = array(
			'tid' => $tid,
			'height' => $height,
		);
	}
	stopic_stripslashes($block_data);
	$isUpdate = $stopic_service->updateUnitByFild($stopic_id, $block_id, array('title'=>$block_title,'data'=>$block_data));
	if ($isUpdate) {
		$result	= array(
			'title'		=> stripslashes($block_title),
			'content'	=> $stopic_service->getHtmlData($block_data, $block_type, $block_id),
		);
		$result	= pwJsonEncode($result);
		echo "success\t".$result;
	} else {
		echo 'error';
	}
	
	ajax_footer();
}

function stopic_stripslashes(&$array){
	if (is_array($array)) {
		foreach ($array as $key => $value) {
			if (is_array($value)) {
				stopic_stripslashes($array[$key]);
			} else {
				$array[$key] = stripslashes($value);
			}
		}
	}
}
function stopic_use_block($type) {
	return Pcv(A_P."/template/admin/block/$type.htm");
}
?>