<?php
! defined ( 'CLOUDWIND' ) && exit ( 'Forbidden' );
require_once CLOUDWIND . '/client/core/public/core.service.class.php';
class CloudWind_Search_Entry extends CloudWind_Core_Service {
	
	function dispatch() {
		list ( $doing ) = $this->getRequest ( array ('doing' ) );
		if (! in_array ( $doing, array ('proxy' ) )) {
			return false;
		}
		$doing = ($doing) ? $doing . 'Entry' : 'searchEntry';
		if (! method_exists ( $this, $doing )) {
			return false;
		}
		return $this->$doing ();
	}
	
	function searcher() {
		if (! CloudWind_getConfig ( 'yunsearch_isopen' )) {
			return true;
		}
		list ( $keyword ) = $this->getRequest ( array ("keyword" ) );
		if (! $keyword) {
			return true;
		}
		if (CloudWind_getConfig ( 'yunsearch_domain' )) {
			header ( "Location:http://" . CloudWind_getConfig ( 'yunsearch_domain' ) . "/index.php?k=" . urlencode ( $keyword ) . $this->getSearchQuery () );
			exit ();
		}
		if (CloudWind_getConfig ( 'yunsearch_unique' )) {
			print_r ( $this->getMainIframe ( CloudWind_getConfig ( 'g_bbsname' ) . ' - 云搜索', 'http://' . $this->getYunHost () . '/index.php?n=' . CloudWind_getConfig ( 'yunsearch_unique' ) . "&k=" . urlencode ( $keyword ) . $this->getSearchQuery (), CloudWind_getConfig ( 'g_charset' ) ) );
			exit ();
		}
		return true;
	}
	
	function getSearchQuery() {
		list ( $keyword, $type, $fid, $username ) = $this->getRequest ( array ("keyword", "type", "fid", "username" ) );
		$query = '&charset=' . CloudWind_getConfig ( 'g_charset' );
		$fid && $query .= "&fid=" . intval ( $fid );
		$type && $query .= "&type=" . trim ( $type );
		$username && $query .= "&username=" . urlencode ( trim ( $username ) );
		return $query;
	}
	
	function getMainIframe($title, $url, $charset) {
		return <<<EOT
<!doctype html>
<html>
	<head>
		<meta charset="$charset">
		<title>$title</title>
		<style type="text/css">html,body{margin:0;padding:0;}</style>
	</head>
	<body>
		<iframe id="searchiframe" style="border:none;overflow:hidden;" width="100%" src="$url" frameborder="0" scrolling="no"></iframe>
	</body>
</html>
EOT;
	}
	
	function proxyEntry() {
		print_r ( $this->getProxyIframe ( CloudWind_getConfig ( 'g_charset' ) ) );
		exit ();
	}
	
	function getProxyIframe($charset) {
		return <<<EOT
<!doctype html>
<html>
	<head>
		<meta charset="$charset">
	</head>
	<body>
	</body>
	<script type="text/javascript">
		(function(){
			var getObj=function(id,parent){
				return (parent?parent:document).getElementById(id);
		    }
			var currHash="";
			var pParentFrame =top.document;
			setInterval(function(){
				var locationUrlHash =location.hash;
				if(typeof locationUrlHash!="undefined"){
					if(locationUrlHash!=currHash){
						if(locationUrlHash.split("#")[1]){
							var size=locationUrlHash.split("#")[1];
							var w=size.split("|")[0];
							var h=size.split("|")[1];
							pParentFrame.getElementById("searchiframe").style.height=h+"px";
						}
						currHash=locationUrlHash;
					}
				}
			},100)
		})();
	</script>
</html>
EOT;
	}

}
