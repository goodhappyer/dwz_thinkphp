<?php
namespace app\admin\controller;
use app\common\controller\Base;
use think\Cache;

class Index extends Base
{
	protected function _initialize() 
	{
		parent::_initialize();
	}
	public function index()
	{
		dump(Cache::get("a"));	
	}
}

