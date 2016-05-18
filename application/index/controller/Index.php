<?php
namespace app\index\controller;
use think\Db;
use app\common\model\JQGrid;

class Index
{
	public function index()
	{
		$jqgrid=new JQGrid(['table_name'=>'jqgrid_info']);
		$jqgrid->show();
		return "t";
	}
}
