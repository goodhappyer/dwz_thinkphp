<?php
namespace app\common\model;

class AdminMenu extends \think\Model
{
	private $tree_str="";
	public function get_mymeun($menu_id,$role_id)
	{
		$this->tree($menu_id,0,0,"",'T');
		eval($this->tree_str);
		$this->data=$T;
		return $this->data;	

	}
	/*
	专为dwz写的
	*/
	public function get_dwxmenu()
	{
		return $this->get_dwzget_child($this->data);
	}
	public function get_dwzget_child($data=array())
	{
		$str="";
		foreach($data as $v)
		{
			if($v['menu_type']==0)
			{

				$str=$str."<li><a href=''>".$v['name'].'</a><ul>';
				$str=$str.$this->get_dwzget_child($v['item']);
				$str=$str."</ul></li>";

			}
			else if($v['menu_type']==1)
			{
					
				$str=$str.'<li><a href="/Admin/'.$v['module_name'].'/'.$v['action_name'].'/menu_id/'.$v['id'].'" target="navTab" rel="main">'.$v['name'].'</a></li>';
			}
		}
		return $str;
	}
	/*
	*这个好难啊		
	*/
	public function tree($pid,$level,$ids,$varstr,$T)
	{
		$level++;	
		$r=self::db()->where(['pid'=>$pid])->select();
		if($r!=null)
		{
			foreach($r as $k=> $v)
			{
				$fields=self::db()->getTableInfo("","fields");
				foreach( $fields as $field)
				{
					$this->tree_str=$this->tree_str.'$'.$T.$varstr.'['.$k.']["'.$field.'"]="'.$v[$field].'";';
				}
				$this->tree($v['id'],$level,$ids,$varstr.'['.$k.']'.'["item"]',$T);
			}
		}
	}
}
