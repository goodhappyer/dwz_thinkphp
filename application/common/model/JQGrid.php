<?php
namespace app\common\model;
use PDO;
use think\Input;
use think\Db;
class JQGrid_item
{
	private $jqgrid_talbe = "jqgrid_info";
	var $debug = true;
	// 存放jqgrid信息的表
	var $data_table;
	// js的初始化函数
	var $url;
	var $datatype='json';
	var $height;
	var $colNames;
	var $colModel;
	var $multiselect;
	var $caption;
	function create_js_item ()
	{
		if (empty($this->data_table))
		{
			throw new Exception("data_table is empty");
		}
		if(empty($this->url))
		{
			$this->url='?s=/'.MODULE_NAME.'/'.CONTROLLER_NAME.'/jqgrid_data';
		}
		if ($this->debug)
		{
			// 開發時開啓debug,检查表中是否增加过字段
			$tableinfo = Db::name($this->data_table)->getTableInfo();
			foreach ($tableinfo['fields'] as $k => $v)
			{
				if (! Db::name($this->jqgrid_talbe)->where("data_table",$this->data_table)
					->where("field_name",$v)
					->find())
				{
					$d['data_table'] = $this->data_table;
					$d['field_name'] = $v;
					$d['jqgrid_name'] = $v;
					$d['jqgrid_index'] = $v;
					if ($tableinfo['bind'][$v] == PDO::PARAM_STR)
					{
						$d['jqgrid_width'] = 300;
						$d['jqgrid_align'] = 'right';
						$d['jqgrid_sorttype'] = false;
					}
					else if ($tableinfo['bind'][$v] == PDO::PARAM_INT)
					{
						$d['jqgrid_width'] = 80;
						$d['jqgrid_align'] = 'left';
						$d['jqgrid_sorttype'] = 'int';
					}
					else
					{
						$d['jqgrid_width'] = 50;
						$d['jqgrid_align'] = 'center';
						$d['jqgrid_sorttype'] = false;
					}
					Db::name($this->jqgrid_talbe)->insert($d);
				}
			}
		}
		

		if (empty($this->table_id))
		{
			$this->table_id = "list_" . $this->data_table;
		}
		if (empty($this->pager_id))
		{
			$this->table_id = "pager_" . $this->data_table;
		}
		$cols = Db::name($this->jqgrid_talbe)->where("data_table",$this->data_table)->select();
		foreach ($cols as $v)
		{
			$this->colNames[] = $v['jqgrid_name'];
			$col = [ ];
			$col['name'] = $v['field_name'];
			$col['index'] = $v['jqgrid_index'];
			$col['width'] = $v['jqgrid_width'];
			$col['align'] = $v['jqgrid_align'];
			$col['sorttype'] = $v['jqgrid_sorttype'];
			$this->colModel[] = $col;
		}
		return $this;
	}
	function get_data()
	{
		$cols = Db::name($this->jqgrid_talbe)->where("data_table",$this->data_table)->select();
		foreach ($cols as $v)
		{
			$this->colNames[] = $v['jqgrid_name'];
		}
		$_search=Input("_search");
		$rows=Input("rows");
		$page=Input("page",1);
		$sidx=input("sidx");
		$sord=input("asc","asc");
		$data['page']=$page;
		$data['total']=Db::name($this->data_table)->field($this->colNames)->count();	
		$data['records']=Db::name($this->data_table)->field($this->colNames)->limit(($page-1)*$rows,$rows)->count();	
		$rows=Db::name($this->data_table)->field($this->colNames)->limit(($page-1)*$rows,$rows)->select();	
		foreach ($rows as $k=>$v)
		{
			$d=array();
			$d['id']=$v['id'];
			foreach($v as $v1)
			{
				$d['cell'][]=$v1;
			}
			$data['rows'][]=$d;
		}
		return 	$data;
	}		
}
class JQGrid
{
	// 用table表生成jqgrid
	var $table_id="list";
	// 把内容放在$table_id中
	var $pager_id;
	// 把分页放在pager_id中
	var $js_init_function = "pageInit";
	var $jqgrid_item; // jqgrid的配置项
	/**
	 * 
	 * @param unknown $args 可以傳表名或是一個數組
	 */
	function __construct ($args)
	{
		if(is_string($args))
		{
			$items['data_table']=$args;
		}
		else 
		{
			$items=$args; 
		}
		$this->jqgrid_item = new JQGrid_item();
		foreach ($items as $k => $v)
		{
			if (property_exists($this->jqgrid_item,$k))
			{
				$this->jqgrid_item->$k = $v;
			}
			$this->$k = $v;
		}
	}
	function show ()
	{
		$str=     '$(function(){ ' . $this->js_init_function . '(); });';
		$str=$str.'  function ' . $this->js_init_function . '(){'."\n";
		$str=$str.'   jQuery("#' . $this->table_id . '").jqGrid(';
		$str=$str.json_encode($this->jqgrid_item->create_js_item())."\n";
		$str=$str.'   )'."\n";
		$str=$str.'}'."\n";
		return $str;
	}
	function data()
	{
		return 	json_encode($this->jqgrid_item->get_data());
	}
}
