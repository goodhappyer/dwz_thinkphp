<?php
namespace app\common\controller;
use app\common\controller\Base;
use think\Controller;
use think\Db;
use think\Cache;
use think\Input; 
/*
 * 后台控制器基类
 *
 * @author andery
 */
class Backend extends Base
{
	protected $_name;
	protected $_menu = array();
	protected $_table;
	protected $search=[]; //显示查找数据
	protected $_search=[]; //查找数据库的字段
	protected $_search_like_fields;
	public function _initialize() 
	{
        	parent::_initialize();
        	$this->_check_priv();
		if($this->_table==null)
		{
			if($this->_name!=null)
			{
				$this->_table=Db::name($this->_name);
			}
			else
			{
				die('$this->name is null');
			}
		}
    	}
	protected function _check_priv()
	{
		
	}
	protected function _create_search($time_field=null)
	{
		$tableinfo=$this->_table->getTableInfo();
		foreach($tableinfo['fields'] as $v)
		{
			if(INPUT::request($v)!=null)
			{
				$this->_search[$v]=INPUT::request($v);
			}
		}
		if(INPUT::request("keywords")!="")
		{
			$this->_search['keywords']=INPUT::request($v);
			$this->_search[$this->_search_like_fields]=array('like','%'.INPUT::request("keywords").'%');
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
	}
	/**
     	* 列表页面
     	*/
    	public function index() 
	{
		$this->_list($this->_create_search());
		$this->display();
	}
	/*
	*
	*/
	protected function _list($search,$table=null,$sort_by="",$order_by="",$field_list="*",$pagesize=20)
	{
		if($table==null)
		{
			$table=$this->_table;
		}
		foreach($this->_search as $k=>$v)
		{
			$this->_table->where($k);
		}
	}
}
