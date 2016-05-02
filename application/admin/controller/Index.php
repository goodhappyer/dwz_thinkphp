<?php
namespace app\admin\controller;
use app\common\controller\Backend;
use think\Cache;

class Index extends Backend
{
	public function _initialize() 
	{
		$this->_name="setting";
		parent::_initialize();
	}
}

