<?php
namespace app\admin\controller;
use app\common\controller\Backend;
use think\Cache;
use think\View;
use think\Input;
class AdminMenu extends Backend
{
	public function _initialize() 
	{
		$this->_name="AdminMenu";
		parent::_initialize();
	}
	public function index()
	{
		$menu_id=Input::get("menu_id");
		$menu_list=$this->_mod->get_mymeun($menu_id,0);
		$view=new View();
		$view->assign("menu",$menu_list);
		return $view->fetch();
	}
}

