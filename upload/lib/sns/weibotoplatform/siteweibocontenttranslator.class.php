<?php

class PW_SiteWeiboContentTranslator {
	function translate($content) {
		$pattern = '|(\[([^\[\]]+)\])|Uis';
		return preg_replace($pattern, "[s:\\2]", $content);
	}
}
