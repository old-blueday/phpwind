<?php
!function_exists('readover') && exit('Forbidden');
class PW_ExtendSearcherAbstract {
	
	function getSearchResult() {
	}
	
	/**
	 * HTML内容输出
	 * @param $htmlFile HTML模板路径
	 * @param $params   页面参数
	 */
	function _outputHtml($htmlFile, $params = array()) {
		ob_start();
		ob_implicit_flush(false);
		require S::escapePath($htmlFile);
		return ob_get_clean();
	}
	
	/**
	 * 获取扩展搜索的HTML模板路径
	 * @param $direcotry 扩展服务的目录,正常当前为 dirname(__FILE__)或其它自定义目录
	 * @param $htmlname  扩展搜索服务HTML模板名称
	 */
	function _getHtmlFile($direcotry, $htmlname) {
		$filePath = S::escapePath($direcotry . '/template/' . $htmlname);
		if (!is_file($filePath)) return '';
		return $filePath;
	}
}