<?php
!function_exists('readover') && exit('Forbidden');

/**
 * Rss
 * 
 * @package Rss
 */
class Rss {
	
	var $rssHeader;
	var $rssChannel;
	var $rssImage;
	var $rssItem;
	
	function Rss($Rss = array('xml'=>"1.0",'rss'=>"2.0",'encoding'=>"gb2312")) {
		
		$this->rssHeader = "<?xml version=\"$Rss[xml]\" encoding=\"$Rss[encoding]\"?>\n";
		$this->rssHeader .= "<rss version=\"$Rss[rss]\">\n";
	}
	
	function channel($channel) {
		
		$this->rssChannel = "<channel>\n";
		foreach ($channel as $key => $value) {
			$this->rssChannel .= " <$key><![CDATA[" . $value . "]]></$key>\n";
		}
	}
	
	function image($image) {
		
		$this->rssImage = "  <image>\n";
		foreach ($image as $key => $value) {
			$this->rssImage .= " <$key><![CDATA[" . $value . "]]></$key>\n";
		}
		$this->rssImage .= "  </image>\n";
	}
	
	function item($item) {
		
		$this->rssItem .= "<item>\n";
		foreach ($item as $key => $value) {
			$this->rssItem .= " <$key><![CDATA[" . $value . "]]></$key>\n";
		}
		$this->rssItem .= "</item>\n";
	}
/*added for YunLiao 1.2: start*/
	function getItems(){
		return $this->rssItem;
	}
	
	function getRss(){
		$all = $this->rssHeader;
		$all .= $this->rssChannel;
		$all .= $this->rssItem;
		$all .= "</channel></rss>";
		return $all;
	}
/*added for YunLiao 1.2: end*/
	function generate($rss_path) {
	/*modded for YunLiao 1.2: start*/
/* original -- start*/
		/*
		$all = $this->rssHeader;
		$all .= $this->rssChannel;
		$all .= $this->rssImage;
		$all .= $this->rssItem;
		$all .= "</channel></rss>";
		*/
/* original -- end*/
		$all = $this->getRss();
/*modded for YunLiao 1.2: end*/
		pwCache::setData($rss_path, $all);
	}
}
?>