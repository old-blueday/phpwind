<?php
!defined('P_W') && exit('Forbidden');
define('AJAX',1);

$ajaxurl = EncodeUrl($basename.'&ajax=1');

S::gp(array('step','block_id','stopic_id'));

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
	S::gp(array('block_title'));
	$block_title = trim($block_title);
	$block_data = array();
	
	//do it self
	if ('thrd' == $block_type) {
		S::gp(array('url','title'));
		if (is_array($title)) {
			foreach ($title as $k => $v) {
				$v = trim($v);
				if ('' != $v) {
					$block_data[] = array('title'=>$v, 'url'=>trim($url[$k]));
				}
			}
		}
	} elseif ('thrdSmry' == $block_type) {
		S::gp(array('url','title','descrip'));
		if (is_array($title)) {
			foreach ($title as $k => $v) {
				$v = trim($v);
				if ('' != $v) {
					$block_data[] = array('title'=>$v, 'url'=>trim($url[$k]), 'descrip'=>trim($descrip[$k]));
				}
			}
		}
	} elseif ('pic' == $block_type) {
		S::gp(array('url','image','image_upload','image_type'));
		L::loadClass('stopicupload', 'upload', false);
		if (is_array($image)) {
			foreach ($image as $k => $v) {
				if ($image_type[$k] == 0) {
					$v = trim($v);
					if ('' != $v) {
						$block_data[] = array('image'=>$v, 'url'=>trim($url[$k]));
					}
				} else {
					$imgUrl = stopicUploadImg($k);
					if ($imgUrl == false) continue;
					$block_data[] = array('image'=>$imgUrl, 'url'=>trim($url[$k]));
				}
			}
		}
	} elseif ('picTtl' == $block_type) {
		S::gp(array('url','image','title','image_upload','image_type'));
		L::loadClass('stopicupload', 'upload', false);
		if (is_array($image)) {
			foreach ($image as $k => $v) {
				if ($image_type[$k] == 0) {
					$v = trim($v);
					if ('' != $v) {
						$block_data[] = array('image'=>$v, 'url'=>trim($url[$k]),'title'=>trim($title[$k]));
					}
				} else {
					$imgUrl = stopicUploadImg($k);
					if ($imgUrl == false) continue;
					$block_data[] = array('image'=>$imgUrl, 'url'=>trim($url[$k]),'title'=>trim($title[$k]));
				}
			}
		}
	} elseif ('picArtcl' == $block_type) {
		S::gp(array('url','image','title','descrip','image_upload','image_type'));
		L::loadClass('stopicupload', 'upload', false);
		if (is_array($image)) {
			foreach ($image as $k => $v) {
				if ($image_type[$k] == 0) {
					$v = trim($v);
					if ('' != $v) {
						$block_data[] = array('image'=>$v, 'url'=>trim($url[$k]),'title'=>trim($title[$k]),'descrip'=>trim($descrip[$k]));
					}
				} else {
					$imgUrl = stopicUploadImg($k);
					if ($imgUrl == false) continue;
					$block_data[] = array('image'=>$imgUrl, 'url'=>trim($url[$k]),'title'=>trim($title[$k]),'descrip'=>trim($descrip[$k]));
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
		S::gp(array('url','image','title','image_upload','image_type'));
		L::loadClass('stopicupload', 'upload', false);
		if (is_array($image)) {
			foreach ($image as $k => $v) {
				if ($image_type[$k] == 0) {
					$v = trim($v);
					if ('' != $v) {
						$block_data[] = array('image'=>$v, 'url'=>trim($url[$k]),'title'=>trim($title[$k]));
					}
				} else {
					$imgUrl = stopicUploadImg($k);
					if ($imgUrl == false) continue;
					$block_data[] = array('image'=>$imgUrl, 'url'=>trim($url[$k]),'title'=>trim($title[$k]));
				}
			}
		}
	} elseif ('nvgt' == $block_type) {
		S::gp(array('url', 'title', 'link_color', 'link_bgcolor'));
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
		S::gp(array('banner_image', 'banner_title', 'postion_left', 'postion_top', 'font_style', 'font_size', 'font_color','image_type'));
		if ($image_type == 1) {
			L::loadClass('stopicupload', 'upload', false);
			$imgUrl = stopicUploadImg(0);
			if ($imgUrl !== false) $banner_image = $imgUrl;
		}
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
		S::gp(array('tid', 'height'), null, 2);
		$block_data = array(
			'tid' => $tid,
			'height' => $height,
		);
	}
	stopic_stripslashes($block_data);
	$stopic_service->updateUnitByFild($stopic_id, $block_id, array('title'=>$block_title,'data'=>$block_data));
	$result	= array(
		'title'		=> stripslashes($block_title),
		'content'	=> ($block_type == 'comment') ? '' : $stopic_service->getHtmlData($block_data, $block_type, $block_id),
	);
	
	$result	= pwJsonEncode($result);
	$result = stripslashes($result);
	echo "success\t".$result;
	
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
	return S::escapePath(A_P."/template/admin/block/$type.htm");
}

//上传图片
function stopicUploadImg($k) {
	global $db_bbsurl;
	$img = new StopicUpload($k);
	$returnImg = PwUpload::upload($img);
	if (!is_array($returnImg) || count($returnImg) == 0) return false;
	$imageUrl = geturl($returnImg[0]['fileuploadurl']);
	return $imageUrl[0];
}
?>