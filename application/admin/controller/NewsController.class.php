<?php
namespace Admin\Controller;
use Common\Controller\AdminbaseController;

class NewsController extends AdminbaseController
{

	var $News;

	function _initialize ()
	{
		parent::_initialize();
		$this->News = D("News");
		$this->assign("_name","News");
	}

	/**
	 * 显示列表
	 */
	public function index ()
	{
		$count = $this->News->where($map)->count();
		$page = $this->page($count,20);
		$lists = $this->News->where($map)
			->limit($page->firstRow . ',' . $page->listRows)
			->order('id DESC')
			->select();
		$this->assign("page",$page->show('Admin'));
		$this->assign("lists",$lists);
		$this->display();
	}

	/**
	 * 添加
	 */
	public function edit ()
	{
	}

	/**
	 * 添加
	 */
	public function add ()
	{
	}

	/**
	 * 删除
	 */
	public function delete ()
	{
	}
}

