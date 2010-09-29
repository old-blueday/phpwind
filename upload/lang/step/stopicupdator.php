<?php
! defined ( 'P_W' ) && exit ( 'Forbidden' );
if (!defined('A_P')) define('A_P',R_P."apps/stopic/");
class StopicUpdator {
	/**
	 * @var PW_STopicService
	 */
	var $stopicService = null;
	/**
	 * @var IndexContainer
	 */
	var $layoutIndexGenerator = null;
	/**
	 * @var IndexContainer
	 */
	var $blockIndexGenerator = null;
	
	function __construct() {
		$this->StopicUpdator();
	}
	
	function StopicUpdator() {
		$this->stopicService = L::loadClass('stopicservice','stopic');
	}
	
	function run() {
		$stopicList = $this->getAll();
		foreach ($stopicList as $stopicData) {
			$this->updateStopic($stopicData);
		}
	}
	
	function updateStopic($stopicData) {
		$stopicId = $stopicData['stopic_id'];
		$layoutType = $stopicData['layout'] ? $stopicData['layout'] : 'type1v0';
		$blockConfig = $stopicData['block_config'] ? unserialize($stopicData['block_config']) : array();
		$navConfig = $stopicData['nav_config'] ? unserialize($stopicData['nav_config']) : array();
		$layoutConfig = $stopicData['layout_config'] ? unserialize($stopicData['layout_config']) : array();
		$layoutConfig = $this->updateLayoutCss($layoutConfig);
		$bannerUrl = $stopicData['banner_url'] && $stopicData['banner_url'] != 'http://' ? $stopicData['banner_url'] : '';

		$this->setLayoutIndexGenerator(new indexContainer());
		$this->setBlockIndexGenerator(new indexContainer());
		$newBlockConfig = $this->transformLayouts($layoutType, $blockConfig);
		$newBlockConfig = $this->updateBlocks($stopicId, $newBlockConfig);
		$prependBlockConfig = $this->prependBlocks($stopicId, $navConfig, $bannerUrl, $layoutConfig);

		$newBlockConfig = array_merge($prependBlockConfig, $newBlockConfig);

		$updateData = array();
		$updateData['layout_config'] = $layoutConfig;
		if (!empty($newBlockConfig)) $updateData['block_config'] = $newBlockConfig;
		$this->stopicService->updateSTopicById($stopicId, $updateData);
		//$this->stopicService->creatStopicHtml($stopicId);
	}
	
	function prependBlocks($stopicId, $navConfig, $bannerUrl, $layoutConfig) {
		if (empty($navConfig) && empty($bannerUrl)) return array();
		
		$blockIds = array();
		if (!empty($bannerUrl)) {
			$blockIds[] = $this->createBannerBlock($stopicId, $bannerUrl, $layoutConfig);
		}
		if (!empty($navConfig)) {
			$blockIds[] = $this->createNavBlock($stopicId, $navConfig, $layoutConfig);
		}
		return $this->createLayoutType1v0($blockIds);
	}
	
	function createLayoutType1v0($blockIds) {
		$blockConfig = array();
		$layoutType = 'type1v0';
		$layoutId = $this->layoutIndexGenerator->generateIndex();
		$blockConfig[$layoutType.'_'.$layoutId]['main'] = $blockIds;
		return $blockConfig;
	}
	
	function createNavBlock($stopicId, $navConfig, $layoutConfig) {
		$blockType = 'nvgt';
		$blockId = $this->createNewBlockId($blockType);
		$blockData = array();
		$blockData['link_color'] = isset($layoutConfig['navfontcolor']) ? $layoutConfig['navfontcolor'] : '';
		$blockData['link_bgcolor'] = isset($layoutConfig['navbgcolor']) ? $layoutConfig['navbgcolor'] : '';
		foreach ($navConfig as $v) {
			$blockData['nav'][] = array('title'=>$v['text'], 'url'=>$v['url']);
		}
		
		$block = array('stopic_id'=>$stopicId, 'html_id'=>$blockId, 'title'=>'', 'data'=>$blockData);

		$this->stopicService->addUnit($block);
		return $blockId;
	}
	
	function updateLayoutCss($layoutConfig) {
		if (isset($layoutConfig['othercss'])) {
			$layoutConfig['othercss'] = str_replace('.groupItem', '.itemDraggable', $layoutConfig['othercss']);
			$layoutConfig['othercss'] .= '
#main{padding:0px;}
.zt_nav li{float:left;line-height:35px; font-size:14px;margin:0 10px; white-space:nowrap;}
.wrap{width:960px;margin:0 auto 0;overflow:hidden;}';
		}
		return $layoutConfig;
	}
	
	function createBannerBlock($stopicId, $bannerUrl, $layoutConfig) {
		$blockType = 'banner';
		$blockId = $this->createNewBlockId($blockType);
		$blockData = array();
		$blockData['image'] = $bannerUrl;
		$blockData['title'] = '';
		
		$block = array('stopic_id'=>$stopicId, 'html_id'=>$blockId, 'title'=>'', 'data'=>$blockData);

		$this->stopicService->addUnit($block);
		return $blockId;
	}
	
	function updateBlocks($stopicId, $blockConfig) {
		foreach ($blockConfig as $layoutId => $layoutParts) {
			foreach ($layoutParts as $layoutPart => $blocks) {
				foreach ($blocks as $blockArrIndex => $blockId) {
					$newBlockId = $this->transformBlockId($blockId);
					$blockConfig[$layoutId][$layoutPart][$blockArrIndex] = $newBlockId;
					$this->updateBlockUnitData($stopicId, $blockId, $newBlockId);
				}
			}
		}
		return $blockConfig;
	}
	
	function updateBlockUnitData($stopicId, $blockId, $newBlockId) {
		$this->stopicService->updateUnitByFild($stopicId, $blockId, array('html_id'=>$newBlockId));
	}
	
	function transformBlockId($blockId) {
		list($prefix, $blockOldType, ) = explode("_", $blockId);
		$blockNewType = $this->transformBlockType($blockOldType);
		return $this->createNewBlockId($blockNewType, $prefix);
	}
	
	function createNewBlockId($blockType, $prefix='block') {
		return $prefix . '_' . $blockType . '_' .$this->blockIndexGenerator->generateIndex();
	}
	
	function transformBlockType($type) {
		$types = array(
			'1' => 'thrd',
			'2' => 'thrdSmry',
			'3' => 'pic',
			'4' => 'picTtl',
			'5' => 'picArtcl',
			'6' => 'html',
			'7' => 'picPlyr',
		);
		return $types[$type];
	}
	
	function transformLayouts($layoutType, $blockConfig) {
		if (empty($blockConfig) || !in_array($layoutType, array('type1v0', 'type1v1', 'type1v1v1', 'type1v2', 'type2v1'))) return array();
		$transformAction = 'transformLayout' . ucfirst($layoutType);
		return $this->$transformAction($blockConfig);
	}
	
	function transformLayoutType1v0($blockConfig) {
		$newLayoutType = 'type1v0';
		$newBlockConfig = array();
		if (isset($blockConfig['sort1'])) {
			$layoutId = $this->layoutIndexGenerator->generateIndex();
			$newBlockConfig[$newLayoutType.'_'.$layoutId]['main'] = $blockConfig['sort1'];
		}
		return $newBlockConfig;
	}
	
	function transformLayoutType1v1($blockConfig) {
		$newLayoutType = 'type1v1';
		return $this->transformLayoutTypeLike1v1($newLayoutType, $blockConfig);
	}
	
	function transformLayoutType1v1v1($blockConfig) {
		$newLayoutType = 'type1v1v1';
		$newBlockConfig = array();
		if (isset($blockConfig['sort1'])) {
			$layoutId = $this->layoutIndexGenerator->generateIndex();
			$newBlockConfig['type1v0_'.$layoutId]['main'] = $blockConfig['sort1'];
		}
		if (isset($blockConfig['sort2']) || isset($blockConfig['sort3']) || isset($blockConfig['sort4'])) {
			$layoutId = $this->layoutIndexGenerator->generateIndex();
			if (isset($blockConfig['sort2'])) $newBlockConfig[$newLayoutType.'_'.$layoutId]['left'] = $blockConfig['sort2'];
			if (isset($blockConfig['sort3'])) $newBlockConfig[$newLayoutType.'_'.$layoutId]['mid'] = $blockConfig['sort3'];
			if (isset($blockConfig['sort4'])) $newBlockConfig[$newLayoutType.'_'.$layoutId]['right'] = $blockConfig['sort4'];
		}
		if (isset($blockConfig['sort5'])) {
			$layoutId = $this->layoutIndexGenerator->generateIndex();
			$newBlockConfig['type1v0_'.$layoutId]['main'] = $blockConfig['sort5'];
		}
		return $newBlockConfig;
	}
	
	function transformLayoutType1v2($blockConfig) {
		$newLayoutType = 'type1v2';
		return $this->transformLayoutTypeLike1v1($newLayoutType, $blockConfig);
	}
	
	function transformLayoutType2v1($blockConfig) {
		$newLayoutType = 'type2v1';
		return $this->transformLayoutTypeLike1v1($newLayoutType, $blockConfig);
	}

	function transformLayoutTypeLike1v1($newLayoutType, $blockConfig) {
		$newBlockConfig = array();
		if (isset($blockConfig['sort1'])) {
			$layoutId = $this->layoutIndexGenerator->generateIndex();
			$newBlockConfig['type1v0_'.$layoutId]['main'] = $blockConfig['sort1'];
		}
		if (isset($blockConfig['sort2']) || isset($blockConfig['sort3'])) {
			$layoutId = $this->layoutIndexGenerator->generateIndex();
			if (isset($blockConfig['sort2'])) $newBlockConfig[$newLayoutType.'_'.$layoutId]['left'] = $blockConfig['sort2'];
			if (isset($blockConfig['sort3'])) $newBlockConfig[$newLayoutType.'_'.$layoutId]['right'] = $blockConfig['sort3'];
		}
		if (isset($blockConfig['sort4'])) {
			$layoutId = $this->layoutIndexGenerator->generateIndex();
			$newBlockConfig['type1v0_'.$layoutId]['main'] = $blockConfig['sort4'];
		}
		return $newBlockConfig;
	}
	
	function getAll() {
		return $this->stopicService->findSTopicInPage(1, $this->stopicService->countSTopic());
	}
	
	function setLayoutIndexGenerator($indexContainer) {
		$this->layoutIndexGenerator = $indexContainer;
	}
	
	function setBlockIndexGenerator($indexContainer) {
		$this->blockIndexGenerator = $indexContainer;
	}
}

class IndexContainer {
	var $index = 1;
	function generateIndex() {
		$this->increase();
		return $this->index;
	}
	function getCurrentIndex() {
		return $this->index;
	}
	function increase() {
		$this->index++;
	}
}
?>