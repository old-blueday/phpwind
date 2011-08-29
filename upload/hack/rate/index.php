<?php
!function_exists('readover') && exit('Forbidden');
define ( "H_R", R_P . 'hack/rate/' );
define ( "L_R", R_P . "lib/rate/" );
!$noAjax && S::gp ( array ("job", "objectid", "optionid", "typeid", "action", "elementid", "authorid" ) );
!$noAjax && !$typeid && Showmsg('评价参数错误');/**/
class RateIndex {

    var $_bbsUrl;
    var $_job;
    var $_objectId;
    var $_optionId;
    var $_typeId;
    var $_action;
    var $_pwServer;
    var $_uid;
    var $_authorid;
    var $_cache = TRUE;
    var $_tips = null;
    var $_groupId;
    var $_db_ratepower;
    var $_db_rategroup;
    var $_showResult = FALSE;
    var $_noAjax = FALSE;
    var $_hotSource = TRUE; #是否来源于hot
    var $_db_hackdb;

    function RateIndex($register) {
        $this->_register ( $register );
        $this->_init ();
    }

    function _register($register) {
        $this->_bbsUrl = &$register ['bbsUrl'];
        $this->_job = &$register ['job'];
        $this->_objectId = &$register ['objectid'];
        $this->_optionId = &$register ['optionid'];
        $this->_typeId = &$register ['typeid'];
        $this->_action = &$register ['action'];
        $this->_pwServer = &$register ['pwServer'];
        $this->_uid = &$register ['uid'];
        $this->_elementid = &$register ['elementid'];
        $this->_authorid = &$register ['authorid'];
        $this->_groupId = &$register ['groupId'];
        $this->_db_ratepower = &$register ['db_ratepower'];
        $this->_db_rategroup = &$register ['db_rategroup'];
        $this->_ip = pwGetIp ();
        $this->_noAjax = &$register ['noAjax'];
        $this->_db_hackdb = &$register ['db_hackdb'];
    }

    function _init() {
        if ($this->_job == "vote" && strtolower ( $this->_pwServer ['REQUEST_METHOD'] ) == "post") {
            $this->_vote ();
        }
        if($this->_job == "hot" && strtolower ( $this->_pwServer ['REQUEST_METHOD'] ) == "get") {
            $this->_hotVote ();
        }
        !$this->_noAjax && $this->_render ();
    }

    function _vote() {
        if (intval ( $this->_optionId ) < 1 || ! in_array ( $this->_typeId, array (1, 2, 3 ) ) || intval ( $this->_objectId ) < 1 || !isset($this->_db_hackdb['rate'])) {
            return '';
        }
        //用户权限
		$groupSets = $this->_getRateGroupSet ();
        $message = $this->_checkPower ( $groupSets );
        if (TRUE !== $message ) {
            $this->_showMessage ( $message );
        }
        $anonymity = ($this->_groupId == 2 && $groupSets [$this->_groupId] > 0) ? TRUE : FALSE;
        //检查作者与用户信息的正确性
		$rateService = $this->_getRateService();
        $this->_tips = $rateService->addRate ( $this->_uid, $this->_objectId, $this->_optionId, $this->_typeId, $this->_ip, $anonymity );
        $this->_showResult = TRUE;
    }

    function _hotVote() {
    # like ratethread_1 get optionid
        $this->_optionId = intval(substr($this->_optionId,strpos($this->_optionId,"_")+1));
        if (intval ( $this->_optionId ) < 1 || ! in_array ( $this->_typeId, array (1, 2, 3 ) ) || intval ( $this->_objectId ) < 1 || !isset($this->_db_hackdb['rate'])) {
            $this->_showHotMessage ( $this->_language('rate_error') );
        }
        //用户权限
        $message = $this->_checkPower ( $this->_getRateGroupSet () );
        if (TRUE !== $message ) {
            $this->_showHotMessage ( $message );
        }
		$rateService = $this->_getRateService();
        $rateService->addRate ( $this->_uid, $this->_objectId, $this->_optionId, $this->_typeId, $this->_ip, FALSE );
        $this->_showHotMessage ( $this->_language('rate_success') );
    }

    function _getRateGroupSet() {
        return unserialize ( $this->_db_rategroup );
    }

    # 检查用户是否有权限
    function _checkPower($groupSets) {
        $groupId = ($this->_groupId == "guest") ? 2 : $this->_groupId;
        if (! $this->_isOpenRate ()) {
            return $this->_language('rate_error');
        }
		$rateService = $this->_getRateService();
        $rate = $rateService->getsRateByUserId ( $this->_uid, $this->_objectId, $this->_typeId );
        if($rate) {
            return $this->_language('rate_rated');
        }
        if( $this->_uid == $this->_authorid){
            return $this->_language('rate_self');
        }
        # 管理员不受限制
        if ($groupId == 3) {
            return TRUE;
        }
        # 对应的用户组是否存在
        if (! isset ( $groupSets [$groupId] )) {
            return $this->_language('rate_error');
        }

        //可评价次数小于1，则不能评价
        if ($groupSets [$groupId] < 1 && $groupId != 2) {
            return $this->_language('rate_error');
        }
        //检查当前用户名可评价次数
        if ($groupSets [$groupId] >= 1 && $this->_uid) {
        //检查用户已评价次数
            $haveTimes = $rateService->countByUserId ( $this->_uid );
            if ($haveTimes < 0) {
                return $this->_language('rate_error');
            }
            if ($groupSets [$groupId] <= $haveTimes) {
                return $this->_language("rate_today_times_limit");
            }
        }

        //游客检查
        if ($groupId == 2) {
            if ($groupSets [$groupId] <= 0) {
                return $this->_language('rate_need_login');
            }
            #取不到用户IP则不能评价
            if ($this->_ip == 'Unknown') {
                return $this->_language('rate_unknow_ip');
            }
            //检查用户IP是否存在
            $haveTimes = $rateService->countByIp ( $this->_ip );
            if ($haveTimes < 0) {
                return $this->_language('rate_error');
            }
            if ($groupSets [$groupId] <= $haveTimes) {
                return $this->_language('rate_anonmity_limit');
            }
        }
        return TRUE;
    }

    function _language($key) {
        $message = array();
        $message['rate_error'] = '抱歉，你不能评价，请与管理员联系';
        $message['rate_success'] = '恭喜，评价成功!';
        $message['rate_self'] = "抱歉，属于自己的不能评价";
        $message['rate_today_times_limit'] = "抱歉，你今天的评价次数达到上限";
        $message['rate_need_login'] = '抱歉，你不能评价，请先 <a href="login.php" class="s3">登录</a>';
        $message['rate_unknow_ip'] = '抱歉，无法确认你的身份，你不能评价，请先 <a href="login.php" class="s3">登录</a>';
        $message['rate_anonmity_limit'] = '抱歉，游客评价次数达到上限，请先 <a href="login.php" class="s3">登录</a>';
        $message['rate_rated'] = '抱歉，你已经参与过该评价';
        return (isset($message[$key])) ? $message[$key] : '';
    }

    function _isOpenRate() {
        $powerSet = unserialize ( $this->_db_ratepower );
        # 全局设置检查 是否开启相对应的评价功能
        if (! isset ( $powerSet [$this->_typeId] ) || $powerSet [$this->_typeId] != 1) {
            return FALSE;
        }
        return TRUE;
    }

    # 分发两个页面，一个评价页和评价结果页
    function _render() {
    # 是否关闭评价
        if (! $this->_isOpenRate () || !isset($this->_db_hackdb['rate'])) {
            return '';
        }
        list ( $typeId, $objectId, $optionId, $elementId, $userId, $authorId ) = array ($this->_typeId, $this->_objectId, $this->_optionId, $this->_elementid, $this->_uid, $this->_authorid );
		$typename = getLangInfo('other','rate_type_'.$typeId);
        list ( $rateConfigs, $imagesUrl, $bbsUrl, $ajaxUrl ) = $this->_buildVoteParams ();
		$rateService = $this->_getRateService();
        //游客
        if(!$this->_uid) {
            $groupId = ($this->_groupId == "guest") ? 2 : $this->_groupId;
            $groupSets = $this->_getRateGroupSet ();
            $anonymity = ($groupSets [$groupId] > 0 && $rateService->getsByIp ( $this->_ip, $objectId, $typeId )) ? TRUE : FALSE;
        }
        //是否已评价
        $showVote = ($this->_showResult || $rateService->getsRateByUserId ( $userId, $objectId, $typeId ) || $authorId == $userId || $anonymity) ? FALSE : TRUE;
        //如果已评价
        if (! $showVote) {
            list($typeTitle,$hotHref) = $this->_getTypeParams($typeId);
            $tips = ($this->_tips) ? $this->_tips : ''; //评价成功后提示积分信息
            list ( $rateResultHtml, $total, $weekHTML) = $this->_buildResultHTML ( $rateConfigs );
        } else {
        	$rateResultHtml = $this->_buildVoteResultHTML($rateConfigs, $ajaxUrl);
        }
        include H_R . 'template/index.htm';
        !$this->_noAjax && ajax_footer ();
    }

    function getVoting() {
        echo '-->';
        $this->_render();
        echo '<!--';
    }

    function _buildVoteParams() {
        $typeid = (in_array ( $this->_typeId, array (1, 2, 3 ) )) ? $this->_typeId : 1;
        $bbsUrl = $this->_bbsUrl . "/";
        $imagesUrl = $this->_getDefaultImageUrl ();
        $ajaxUrl = $bbsUrl . "hack.php?H_name=rate&action=ajax";
		$rateService = $this->_getRateService();
        $rateConfigs = $rateService->getsRateConfigByTypeId ( $typeid );
        return array ($rateConfigs, $imagesUrl, $bbsUrl, $ajaxUrl );
    }

    function _getDefaultImageUrl() {
        return $this->_bbsUrl . "/hack/rate/";
    }

    # 分步骤组装
    function _buildResultHTML($rateConfigs) {
    //组装评价结果
		$rateService = $this->_getRateService();
        list ( $rateResults, $total ) = $rateService->getRateResultByTypeId ( $this->_typeId, $this->_objectId );
        $resultHTML = "";
        foreach ( $rateConfigs as $config ) {
            if ($config ['isopen'] == 0) {
                continue;
            }
            $voteNum = (isset ( $rateResults [$config ['id']] ['num'] )) ? $rateResults [$config ['id']] ['num'] : 0;
            $percentage = ($total == 0) ? 100 : (100 - $voteNum / $total * 100);
            $resultHTML .= '<td><a href="javascript:;" hidefocus="true">' . $voteNum . '<br />';
            $resultHTML .= '<div class="mood-one"><div class="mood-two" style="height:' . $percentage . '%;"></div></div>';
            $resultHTML .= '<img src="' . $this->_getDefaultImageUrl () . 'images/' . $config ['icon'] . '" /><br />' . $config ['title'] . '</a></td>';
        }
		$weekHTML = ($this->_cache) ? $this->_get_RateConfigResultCache ( $rateConfigs ) : $this->_buildWeekResultHtml ( $rateConfigs );
        return array ($resultHTML, $total, $weekHTML );
    }
    
    function _buildVoteResultHTML($rateConfigs, $ajaxUrl) {
    	$rateService = $this->_getRateService();
        list ( $rateResults, $total ) = $rateService->getRateResultByTypeId ( $this->_typeId, $this->_objectId );
        $resultHTML = "";
        foreach ( $rateConfigs as $config ) {
            if ($config ['isopen'] == 0) {
                continue;
            }
            $voteNum = (isset ( $rateResults [$config ['id']] ['num'] )) ? $rateResults [$config ['id']] ['num'] : 0;
            $percentage = ($total == 0) ? 100 : (100 - $voteNum / $total * 100);
            $resultHTML .= '<td>' . $voteNum . '<br />';
            $resultHTML .= '<div class="mood-one"><div class="mood-two" style="height:' . $percentage . '%;"></div></div>';
            $resultHTML .= "<a href=\"javascript:;\" onclick=\"rate.voting('$this->_elementid', '$ajaxUrl','{$this->_objectId}', '$config[id]', '{$this->_typeId}','$this->_authorid');\" title=\"$config[tips]\">";
            $resultHTML .= '<img src="' . $this->_getDefaultImageUrl () . 'images/' . $config ['icon'] . '" /><br />' . $config ['title'] . '</a></td>';
        }
        return $resultHTML;
    }

    function _getTypeParams($typeId){
        $links = $titles = array();
        $links['1'] = 'apps.php?q=hot&action=threadHot&sub=threadRate';
        $links['2'] = 'apps.php?q=hot&action=diaryHot&sub=diaryRate';
        $links['3'] = 'apps.php?q=hot&action=picHot&sub=picRate';
        $titles['1'] = '帖子';
        $titles['2'] = '日志';
        $titles['3'] = '照片';
        (!in_array($typeId,array('1','2','3'))) && $typeId = 1;
        return array($titles[$typeId],$links[$typeId]);
    }

    function _buildWeekResultHtml($rateConfigs) {
		$rateService = $this->_getRateService();
        $weekResult = $rateService->getWeekData($this->_typeId,$this->_hotSource);
        if (! $weekResult) {
            return null;
        }
        $weekHTML = '';
        $currentNum = 1;
        foreach ( $rateConfigs as $config ) {
            if (! isset ( $weekResult [$config ['id']] ) || $config ['isopen'] == 0) {
                continue;
            }
            ($currentNum == 3) && $weekHTML .= '<span id="more" style="display:none;">';
            $weekHTML .= '<li>最' . $config ['title'] . '：';
            $weekHTML .= '<a href="' . $this->_bbsUrl . $weekResult [$config ['id']] ['objectInfo'] ['href'] . '" target="_blank">' . $weekResult [$config ['id']] ['objectInfo'] ['title'] . '</a>&nbsp;&nbsp;';
            $weekHTML .= '作者：<a href="' . $this->_bbsUrl . $weekResult [$config ['id']] ['objectInfo'] ['authorUrl'] . '" class="black" target="_blank">' . $weekResult [$config ['id']] ['objectInfo'] ['author'] . '</a>';
            $weekHTML .= '</li>';
            $currentNum ++;
        }
        $weekHTML .= ($currentNum-1 >= 3) ?  '</span>' : '<span id="more" style="display:none;"></span>';
        return $weekHTML;
    }

    function _get_RateConfigResultCache($rateConfigs) {
    //每一个小时更新一次
        $filePath = $this->_getReteConfigFilePath ( $this->_typeId );
        if (! file_exists ( $filePath ) || time () - filemtime ( $filePath ) > 3600) {
            $weekHTML = $this->_buildWeekResultHtml ( $rateConfigs );
            pwCache::setData ( s::escapePath($filePath), $weekHTML );//write ignore null or not
        } else {
            //* $weekHTML = readover ( $filePath );
            $weekHTML = pwCache::getData(S::escapePath($filePath) , false, true);
        }
        return $weekHTML;
    }

    function _showMessage($message) {
        echo '<div class="fl" style="zoom:1;padding:5px 8px 5px;line-height:1.3;border:1px solid #f8d0a5;background:#fffae1;color:#000;margin:10px 0;">' . $message . '</div><div class="c"></div><div id="ratetips"></div>';
        ajax_footer ();
    }

    function _showHotMessage($message) {
        echo $message;
        ajax_footer ();
    }

    function _getReteConfigFilePath($typeId) {
        return S::escapePath(D_P . '/data/bbscache/rate_week_' . $typeId . '.php');
    }

    function _getRateService() {
        return L::loadClass('rate', 'rate');
    }
}
$register = array ("bbsUrl" => $db_bbsurl, "job" => $job, "objectid" => $objectid, "optionid" => $optionid, "typeid" => $typeid, "action" => $action, "pwServer" => $pwServer, "uid" => $winduid, "elementid" => $elementid, "authorid" => $authorid, "groupId" => $groupid, "db_ratepower" => $db_ratepower, "db_rategroup" => $db_rategroup,"noAjax"=>$noAjax,"db_hackdb"=>$db_hackdb );
$rateIndexObject = new RateIndex ( $register );
?>