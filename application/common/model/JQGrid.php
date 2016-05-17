<?php
namespace app\common\model;
use think\Input;
use think\Db;

class JQGrid
{

	var $jqgrid_talbe = "jqgrid_info";

	var $table_name;

	var $debug = true;

	var $table_id;

	var $pager_id;

	var $js_init_function = "pageInit";

	var $js_jqg_datatype = "local";

	var $js_jqg_height = "250";

	var $js_jqg_colNames = array ();

	var $js_jqg_colModel = array ();

	var $js_jqg_multiselect = true;

	var $js_jqg_caption;

	var $js_jqg_data = array ();

	function __construct ($items = [])
	{
		foreach ($items as $k => $v)
		{
			$this->$k = $v;
		}
	}

	function show ()
	{
		if (empty($this->table_name))
		{
			throw new Exception("table_name 没有设置表名!");
		}
		else
		{
			if (empty($this->table_id))
			{
				$this->table_id = "list_" . $this->table_name;
			}
			if (empty($this->pager_id))
			{
				$this->table_id = "pager_" . $this->table_name;
			}
		}
		if ($this->debug)
		{
			// 开发模式要把jqgrid的字段是否放在数据库中
			$fields = Db::name($this->table_name)->getTableInfo();
			foreach ($fields as $k => $v)
			{
				if (Db::name($this->jqgrid_talbe)->where("table_name",$this->table_name)
					->where("field_name",$v['fields'])
					->find())
				{
					$d['table_name']=$this->table_name;
					$d['field_name']=$this->$v['fields'];
					if()
					Db::name($this->jqgrid_talbe)->insert($d);
				}
			}
		}
	}
}