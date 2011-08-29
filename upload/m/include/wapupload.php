<?php 
!function_exists('readover') && exit('Forbidden');
require_once(R_P . 'lib/upload/attupload.class.php');
class WapUpload extends AttUpload {
    function WapUpload($uid, $flashatt = null, $savetoalbum = 0, $albumid = 0){
    	parent::AttUpload($uid, $flashatt, $savetoalbum, $albumid);
    	$this->ifthumb = 1;
    }
}
?>