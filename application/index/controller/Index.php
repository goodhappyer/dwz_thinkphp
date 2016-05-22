<?php
namespace app\index\controller;
use think\Db;
use app\common\model\JQGrid;
use \think\View;

class Index
{
	public function index()
	{
		$view=new View();
		$jqgrid=new JQGrid('jqgrid_info');
		$view->assign("jqgrid",$jqgrid->show());
		return $view->fetch();
	}
	public function jqgrid_data()
	{
		$jqgrid=new JQGrid('jqgrid_info');
		$jqgrid->show();
		echo $jqgrid->data();
	}
}
