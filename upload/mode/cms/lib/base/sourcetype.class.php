<?php
!defined('M_P') && exit('Forbidden');

class PW_SourceType /*Abstract class*/ {
	/**
	 * 获取数据，并且处理articleModule
	 * @param $articleModule
	 * @param $sourceId
	 */
	function cookArticleModule(&$articleModule,$sourceId) {
		$data = $this->getSourceData($sourceId);
		if (!$data) return $articleModule;
		foreach ($this->getSourceMap() as $key => $value) {
			$articleModule->{$value} = $data[$key];
		}
		$articleModule->setSourceType($this->getSourceType());
		$articleModule->setSourceId($sourceId);

		return $articleModule;
	}
	/**
	 * 获取数据和ArticleModule的映射关系
	 */
	function getSourceMap() {
		return array(
			'subject' => 'subject',
			'content' => 'content',
			'descrip' => 'descrip',
			'author' => 'author',
			'frominfo' => 'fromInfo',
		);
	}
	/**
	 * 通过数据地址
	 * @param $sourceId
	 */
	function getSourceUrl($sourceId) /*Abstract function*/ {
		
	}
	/**
	 * 通过id获取数据
	 * @param $sourceId
	 */
	function getSourceData($sourceId) /*Abstract function*/ {
		
	}
	/**
	 * 获取数据类型
	 */
	function getSourceType() /*Abstract function*/ {
		
	}
}