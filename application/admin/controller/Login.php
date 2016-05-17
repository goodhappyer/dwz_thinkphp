<?php
namespace app\admin\controller;
use app\common\controller\Backend;
use think\Db;
use think\View;
use think\Input;
use think\Session;

class Login extends Backend
{
	var $_name="AdminUser";
	public function _initialize() 
	{
        	parent::_initialize();
	}
	public function index() 
	{
		$view=new View();
		return $view->fetch();
	}
	public function login()
	{
		$username=INPUT::request('username');
		$password=INPUT::request('password');
		$this->_mod->login($username,$password);
		print_r($this->_mod);	
		if($this->_mod->token!=null)
		{
			Session::set("token",$this->_mod->token);
			$this->redirect("index/index");
		}
	}
}
