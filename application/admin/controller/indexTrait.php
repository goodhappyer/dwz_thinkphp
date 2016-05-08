<?php 
trait index
{
	protected function _create_search($time_field=null)
	{
		$fields=$this->_mod::db()->getTableInfo("","fields");
		foreach($fields as $v)
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
}
