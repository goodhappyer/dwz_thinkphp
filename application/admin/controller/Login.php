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
	public function index() 
	{
		$view=new View();
		return $view->fetch();
	}
	public function login()
	{
		$username=INPUT::request('username');
		$password=INPUT::request('password');
		$this->do_login($username,$password);	
	}
	private function  do_login($username,$password)
	{
		$r=$this->_mod->login($username,$password);
		print_r($r);
	}
	private function do_login_byid($id)
	{
		if(is_numeric($id))
		{
			print_r($this->_mod->find());
		//	$r=$this->_mod->where(['id'=>$id])->find();
			print_r($r);
			die();
			Session::set("visitor",$r);
			$this->redircet("admin/index");
		}
	}
}


