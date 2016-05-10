<?php 
namespace app\admin\controller;

use think\Input;
trait indexTrait
{
	protected function _create_search($time_field=null)
	{
		$fields=$this->_mod::db()->getTableInfo("","fields");
		foreach($fields as $v)
		{
			if(Input::request($v)!=null)
			{
				$this->_search[$v]=Input::request($v);
			}
		}
		if(Input::request("keywords")!="")
		{
			$this->_search['keywords']=Input::request($v);
			$this->_search[$this->_search_like_fields]=array('like','%'.Input::request("keywords").'%');
		}
		if($time_field!=null)
		{
			$endtime=I("endtime")?I("endtime"):date("Y-m-d",2147483646);
    			$this->search['endtime']=I("endtime");
    			$starttime=I("starttime")?I("starttime"):date("1970-1-1 8:0:0",time());
    			$this->search['starttime']=I("starttime");
			$this->_search[$time_field]=array("between",array(strtotime($starttime),strtotime($endtime)+60*60*24));
		}
		$this->search=$this->_search;
		$this->view->assign("search",$this->search);
	}
	
	/**
     	* 列表页面
     	*/
    	public function index() 
	{
		$this->trigger("before_index");
		$this->_list($this->_create_search());
		return $this->view->fetch();
	}	
	/**
	* 列表处理
	*
	* @param obj $model  实例化后的模型
	* @param array $map  条件数据
	* @param string $sort_by  排序字段
	* @param string $order_by  排序方法
	* @param string $field_list 显示字段
	* @param intval $pagesize 每页数据行数
	*/
	protected function _list($map = array(), $sort_by=NULL, $order_by=NULL, $field_list='*',$model=NULL, $pagesize=20)
    	{
		//如果不传
		if($model==NULL)
		{
			$model=$this->_mod;
		}

		$mod_pk = $model->getPk();

		if($sort_by==NULL)
		{
			$sort=$mod_pk;
		}
		else if(Input::request("sort")!=NULL)
		{
			$sort=Input::request("sort");
		}
		else
		{
			$sort=$mod_pk;
		}

		if($order_by!=NULL)
		{
			$order=$order_by;
		}
		else if(Input::request("order"))
		{
			$order = Input::request("order");
		}
		else
		{
            		$order = 'DESC';
        	}

        	//如果需要分页
        	if ($pagesize) 
		{
            		$count = $model::db()->where($map)->count($mod_pk);
            		$pager = new Page($count, $pagesize);
        	}
        	$select = $model::Db()->field($field_list)->where($map)->order($sort . ' ' . $order);
		
		if ($pagesize)
		{
            		$select->limit($pager->firstRow.','.$pager->listRows);
            		$page = $pager->show();
            		$this->assign("page", $page);
		}
		$list = $select->select();
		$this->assign('list', $list);
		$this->assign('list_table', true);
	}

}
