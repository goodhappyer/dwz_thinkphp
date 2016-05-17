<?php

namespace Admin\Controller;

use Common\Controller\AdminbaseController;

class ShareController extends AdminbaseController {
	protected $share_model;
	protected $targets = array (
			"_blank" => "新标签页打开",
			"_self" => "本窗口打开" 
	);
	function _initialize() {
		parent::_initialize ();
		$this->share_model = D ( "share_view" );
	}
	function index() {
		$count=$this->share_model->count ();
		$page = $this->page($count, 20);
		$share = $this->share_model->limit($page->firstRow . ',' . $page->listRows)->order("id desc")->select ();
		foreach($share as $k=>$v)
		{
			$share[$k]['input_time']=date("y-m-d H:i:s",$share[$k]['input_time']);
		}
		$this->assign ( "share", $share );
		$this->assign("Page", $page->show('Admin'));
		$this->display ();
	}
}