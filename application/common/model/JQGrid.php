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
	var $datatype;
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
					$d['table_name'] = $this->table_name;
					$d['field_name'] = $v;
					$d['jqgrid_name'] = $v;
					$d['jqgrid_index'] = $v;
					if ($tableinfo['bind'][$v] == PDO::PARAM_STR)
					{
						$d['jqgrid_width'] = 50;
						$d['jqgrid_align'] = 'right';
						$d['jqgrid_sorttype'] = false;
					}
					else if ($tableinfo['bind'][$v] == PDO::PARAM_INT)
					{
						$d['jqgrid_width'] = 11;
						$d['jqgrid_align'] = 'left';
						$d['jqgrid_sorttype'] = 'int';
					}
					else
					{
						$d['jqgrid_width'] = 5;
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
		foreach ($clos as $v)
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
	}
	function create_data()
	{
		
	}
}
class JQGrid
{
	// 用table表生成jqgrid
	var $table_id;
	// 把内容放在$table_id中
	var $pager_id;
	// 把分页放在pager_id中
	var $js_init_function = "pageInit";
	var $jqgrid_item; // jqgrid的配置项
	function __construct ($items)
	{
		if(is_string($items))
		{
			$items['data_table']=$items;
		}
		$jqgrid_item = new JQGrid_item();
		foreach ($items as $k => $v)
		{
			if (property_exists($jqgrid_item,$k))
			{
				$jqgrid_item->$k = $v;
			}
			$this->$k = $v;
		}
	}
	function show ()
	{
		echo '$(function(){' . $this->js_init_function . '();});';
		echo 'function ' . $this->js_init_function . '(){';
		echo 'jQuery("#' . $this->table_id . '").jqGrid(';
		echo json_encode($d);
		echo ')';
		echo '}';
	}
}
