<?php
! defined ( 'P_W' ) && exit ( 'Forbidden' );
S::gp ( array ("page", "action", "background_check", "background_select", "background_update", "background_delete" ) );

class BackGroundManage {
	
	var $stopic_service;
	var $action;
	var $pwService;
	var $background_check;
	var $background_select;
	var $background_update; # is click update sumbit
	var $background_delete; # is click delete sumbit
	var $jump;
	var $page;
	var $perPage;
	
	function BackGroundManage($register) {
		$this->_register ( $register );
		$this->_init ();
	}
	
	function _register($register) {
		$this->stopic_service = &$register ['stopic_service'];
		$this->action = &$register ['action'];
		$this->pwService = &$register ['pwService'];
		$this->background_update = &$register ['background_update'];
		$this->background_delete = &$register ['background_delete'];
		$this->background_select = &$register ['background_select'];
		$this->background_check = &$register ['background_check'];
		$this->jump = &$register ['stopic_handler_url'];
		$this->page = &$register ['page'];
		$this->perPage = &$register ['db_perpage'];
	}
	
	function _init() {
		if ($this->action == "modify" && strtolower ( $this->pwService ['REQUEST_METHOD'] ) == "post") {
			$this->_modify ();
		}
	}
	
	function _modify() {
		//先删除，这样就能少做更新操作
		//考虑到每条SQL不可共同，每次都执行foreach循环更新，而且删除需要判断是否可以删除
		if ($this->background_check != "" && $this->background_delete != "") {
			foreach ( $this->background_check as $pictureId => $value ) {
				if ($value != 1) {
					continue;
				}
				$result = $this->stopic_service->deletePicture ( $pictureId );
				(! $result) && Showmsg ( "对不起，背景图片已在使用中", $this->jump );
			}
			Showmsg ( "删除成功!", $this->jump );
		}
		if ($this->background_select != "" && $this->background_update != "") {
			foreach ( $this->background_select as $pictureId => $categoryId ) {
				$result = $this->stopic_service->updatePicture ( array ("categoryid" => $categoryId ), $pictureId );
				//(! $result) && Showmsg ( "对不起，背景图片分类更新失败", $this->jump );
			}
			Showmsg ( "保存成功!", $this->jump );
		}

		
	}
	
	function execute() {
		$totalNum = $this->countPictures ();
		$pager = $lists = "";
		if ($totalNum) {
			$this->page = ($this->page > 1) ? $this->page : 1;
			$totalPage = ceil ( $totalNum / $this->perPage );
			$pager = numofpage ( $totalNum, $this->page, $totalPage, $this->jump . "&" );
			$lists = $this->getBackGroundLists ( $this->page, $this->perPage );
		}
		$bool = ($totalNum) ? TRUE : FALSE;
		return array ($bool, $pager, $lists );
	}
	
	function getBackGroundLists($page = 1, $perPage = 10) {
		$pictures = $this->stopic_service->getBackgroundsInPage ( $page, $perPage );
		if (! $pictures) {
			return null;
		}
		$tmp = array ();
		$categoryLists = $this->getCategorysLists ();
		foreach ( $pictures as $key => $picture ) {
			$picture ['select_html'] = $this->_buildSelectHtml ( $categoryLists, $picture ['id'], $picture ['categoryid'] );
			$tmp [$key] = $picture;
		}
		return $tmp;
	}
	
	function _buildSelectHtml($categoryLists, $pictureId, $categoryId) {
		$html = $option = '';
		foreach ( $categoryLists as $list ) {
			$option .= '<option value="' . $list ['id'] . '" ' . (($categoryId > 0 && $list ['id'] == $categoryId) ? " selected=selected " : "") . '>' . $list ['title'] . '</option>';
		}
		$html .= '<select style="height:21px;" name="background_select[' . $pictureId . ']">';
		$html .= '<option value="0" ' . (($categoryId == 0) ? " selected=selected " : "") . '>无分类</option>';
		$html .= $option;
		$html .= '	</select> ';
		return $html;
	}
	
	function getCategorysLists() {
		return $this->stopic_service->getCategorys ();
	}
	
	function countPictures() {
		return $this->stopic_service->countPictures ();
	}

}
$register = array ("stopic_service" => $stopic_service, "action" => $action, "pwService" => $pwServer, "background_select" => $background_select, "background_check" => $background_check, "background_update" => $background_update, "background_delete" => $background_delete, "stopic_handler_url" => $stopic_admin_url."&job=$job", "db_perpage" => $db_perpage, "page" => $page );
$backgroundObject = new BackGroundManage ( $register );
list ( $bool, $backgroundpages, $backgroundLists ) = $backgroundObject->execute ();

include stopic_use_layout ( 'admin' );
?>