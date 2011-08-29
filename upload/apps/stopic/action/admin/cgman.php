<?php
! defined ( 'P_W' ) && exit ( 'Forbidden' );

S::gp ( array ("action", "new_category_title", "category_title", "categoryid" ) );

class CategoryManage {
	
	var $action;
	var $stopic_service;
	var $new_category_title;
	var $admin_name;
	var $category_title;
	var $categoryid;
	var $pwService;
	
	# __construct
	function CategoryManage($register) {
		$this->_register ( $register );
		$this->_init ();
	}
	
	# register variables
	function _register($register) {
		$this->action = &$register ['action'];
		$this->stopic_service = &$register ['stopic_service'];
		$this->new_category_title = &$register ['new_category_title'];
		$this->admin_name = &$register ['admin_name'];
		$this->category_title = &$register ['category_title'];
		$this->categoryid = &$register ['categoryid'];
		$this->pwService = &$register ['pwService'];
		$this->jump = &$register ['jump'];
	}
	
	# initiation
	function _init() {
		if ($this->action == "modify" && strtolower ( $this->pwService ['REQUEST_METHOD'] ) == "post") {
			$this->_modify ();
			ObHeader($this->jump);
		}
		if ($this->action == "delete" && strtolower ( $this->pwService ['REQUEST_METHOD'] ) == "get") {
			$this->_delete ();
			ObHeader($this->jump);
		}
	}
	
	# modify categorys
	function _modify() {
		(trim ( $this->new_category_title ) != "") && ! (in_array ( $this->new_category_title, $this->_getCategoryTitles () )) && $this->stopic_service->addCategory ( array ("title" => $this->new_category_title, "creator" => $this->admin_name ) );
		if (count ( $this->category_title ) > 0) {
			//取出所有的分类标题，如果是分类比较多，则可选择获取需要的分类
			//查询的效率肯定大于更新的效率 需要才进行更新
			foreach ( $this->category_title as $categoryId => $title ) {
				if (trim ( $title ) == "" || intval ( $categoryId ) < 1) {
					continue;
				}
				if (! in_array ( $title, $this->_getCategoryTitles () )) {
					$this->stopic_service->updateCategory ( array ("title" => $title ), $categoryId );
				}
			}
		}
	}
	
	# delele categorys
	function _delete() {
		if (!$this->stopic_service->isAllowDeleteCategory($this->categoryid)) Showmsg("对不起，分类下还有专题或者是系统默认分类，不能删除", $this->jump);
		(intval ( $this->categoryid ) > 0) && $this->stopic_service->deleteCategory ( $this->categoryid );
	}
	
	# get all category titles
	function _getCategoryTitles() {
		foreach ( $this->getCategorysLists () as $category ) {
			$titles [] = $category ['title'];
		}
		return $titles;
	}
	
	# get all categorys
	function getCategorysLists() {
		return $this->stopic_service->getCategorys ();
	}
}
$register = array ("action" => $action, "stopic_service" => $stopic_service, "new_category_title" => $new_category_title, "admin_name" => $admin_name, "category_title" => $category_title, "categoryid" => $categoryid, "pwService" => $pwServer ,'jump'=>$stopic_admin_url."&job=$job");
$categoryObject = new CategoryManage ( $register );
$categoryLists = $categoryObject->getCategorysLists ();
include stopic_use_layout ( 'admin' );
?>