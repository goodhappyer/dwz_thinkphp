<?php
namespace app\admin\controller;
use app\common\controller\Backend;
use think\Cache;
use think\View;
use think\Input;
class AdminUser extends Backend
{
	public function _initialize() 
	{
		$this->_name="AdminUser";
		parent::_initialize();
	}
	public function _after_index()
	{
	}
	use AddTrait;
	use IndexTrait;
}
