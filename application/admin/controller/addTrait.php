<?php 
namespace app\admin\controller;
trait addTrait
{
	public function add()

	{
		if(IS_POST)
		{
			$fields=$this->_mod::db()->getTableInfo("","fields");
			foreach($fields as $v)
			{
				if(INPUT::request($v)!=NULL)
				{
					$this->_mod->$v=INPUT::request($v);
				}	
			}
			if($this->_mod->save())
			{
				$this->dwz_ajax_return_ok();
			}
			else
			{
				$this->dwz_ajax_return_err();
			}
		}
		else
		{
			return $this->view->fetch();
		}
	}
}
