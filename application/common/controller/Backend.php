<?php
namespace app\common\controller;
use app\common\controller\Base;
use think\Controller;
use think\Db;
use think\Cache;
use think\Input;
use think\View; 
use think\Session;
/*
 * 后台控制器基类
 *
 * @author goodhappyer
 */
class Backend extends Base
{
	protected $_name;
	protected $menu = array();
	protected $_mod;
	protected $search=[]; //显示查找数据
	protected $_search=[]; //查找数据库的字段
	protected $_search_like_fields;
	protected $view;
	public function _initialize() 
	{
		session_start();
        	parent::_initialize();
        	$this->_check_priv();
		if($this->_mod==null)
		{
			if($this->_name!=null)
			{
				$modepath='app\\common\\model\\'.$this->_name;
				$this->_mod=new $modepath; 
			}
			else
			{
				die('$this->name is null');
			}
		}
		$this->view=new View();
		$this->view->assign("menu",$this->menu);
    	}
	/*
	dwz 返回结果
	$statusCode 200 成功 300 失败 301需要登陆
	
	*/
	public function dwz_ajax_return($statusCode,$message,$navTabId,$callbackType,$forwardUrl)
	{
		$T=array();
		$T['statusCode']=$statusCode;
		$T['message']=$message;
		$T['navTabId']=$navTabId;
		$T['callbackType']=$callbackType;
		$T['forwardUrl']=$forwardUrl;
		echo json_encode($T);
		exit ;	
	}

	public function dwz_ajax_return_ok($message="操作成功",$navTabId="",$callbackType="closeCurrent",$forwardUrl="")
	{
		$statusCode=200;
		$this->dwz_ajax_return($statusCode,$message,$navTabId,$callbackType,$forwardUrl);
	}

	public function dwz_ajax_return_err($message="操作失败",$navTabId="",$callbackType="",$forwardUrl="")
	{
		$statusCode=300;
		$this->dwz_ajax_return($statusCode,$message,$navTabId,$callbackType,$forwardUrl);
	}
	protected function _check_priv()
	{
		if(CONTROLLER_NAME=='login')
		{
			return true;
		}
		$user=model("AdminUser");
		$this->visitor=$user->check_token(Session::get("token"));
		if($this->visitor==null)
		{
		//	$this->redirect("login/index");	
		}
	}
	protected function _create_search($time_field=null)
	{
		$tableinfo=$this->_mod->getTableInfo();
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
		$this->view->assign("search",$this->search);
	}
	/**
     	* 列表页面
     	*/
    	public function index() 
	{
		$this->_list($this->_create_search());
		return $this->view->fetch();
	}
	/*
	*
	*/
	protected function _list($search,$table=null,$sort_by="",$order_by="",$field_list="*",$pagesize=20)
	{
		if($table==null)
		{
			$table=$this->_mod;
		}
		$this->_mod->where($search);
		$list=$this->_mod->select();
		$this->view->assign("list",$list);
	}
}
