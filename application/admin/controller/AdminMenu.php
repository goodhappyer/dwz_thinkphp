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
		$this->view->assign("menu",$menu_list);
		return $this->view->fetch();
	}
	public function add()
	{
		if(IS_POST)
		{
			$fields=$this->_mod::db()->getTableInfo("","fields");
			foreach($fields as $v)
			{
				if(INPUT::request($v)!=NULL)
				{
					$this->_mod->$v=INPUT::request($v);
				}	
			}

			if($this->_mod->save())
			{
				$this->dwz_ajax_return_ok();
			}
			else
			{
				$this->dwz_ajax_return_err();
			}
		}
		else
		{
			return $this->view->fetch();
		}
	}
	public function lookup()
	{
		$menu_list=$this->_mod->get_dwzget_child($this->_mod->get_mymeun(0,0),1);
		$this->view->assign("menu",$menu_list);
		return $this->view->fetch();
	}
}

