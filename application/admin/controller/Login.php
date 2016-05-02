<?php
namespace app\admin\controller;
use app\common\controller\Backend;
use think\Db;
use think\View;
use think\Input;
use think\Session;
class Login extends Backend
{
	var $_name="admin";
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
		print_r($this->_table);
		$r=$this->_table->where(['username'=>$username,'password'=>$password])->find();
	//	$this->do_login_byid($r['id']);

		$r=$this->_table->where(['username'=>$username,'password'=>$password])->find();
	}
	private function do_login_byid($id)
	{
		if(is_numeric($id))
		{
			print_r($this->_table->find());
		//	$r=$this->_table->where(['id'=>$id])->find();
			print_r($r);
			die();
			Session::set("visitor",$r);
			$this->redircet("admin/index");
		}
	}
}


