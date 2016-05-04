<?php
namespace app\admin\controller;
use app\common\controller\Backend;
use think\Cache;
use think\View;

class Index extends Backend
{
	public function _initialize() 
	{
		$this->_name="AdminMenu";
		parent::_initialize();
	}
	public function index()
	{
		print_r($this->_mod->get_mymeun(0));
	//	$view=new View();
	//	return $view->fetch();
	}
}

