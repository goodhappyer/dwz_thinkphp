<?php
namespace app\common\model;

class AdminMenu extends \think\Model
{
	var $T=array();
	public function get_mymeun($role_id)
	{
		print_r($this->tree(0,0,0));
		$this->data=$this->T;
		return $this->data;	
	}
	public function tree($pid,$level,$ids)
	{
		$level++;	
		$T=self::db()->where(['pid'=>$pid])->select();
		if($T!=null)
		{
			foreach($T as $k=> $v)
			{
				return $this->tree($v['id'],$level,$ids);
			}
		}
	}
}
