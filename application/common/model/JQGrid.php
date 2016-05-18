<?php

namespace app\common\model;

use PDO;
use think\Input;
use think\Db;

class JQGrid {
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
	function __construct($items = []) {
		foreach ( $items as $k => $v ) {
			$this->$k = $v;
		}
		if ($this->debug) {
			// 正式板就不运行
			$tableinfo = Db::name ( $this->table_name )->getTableInfo ();
			foreach ( $tableinfo ['fields'] as $k => $v ) {
				if (! Db::name ( $this->jqgrid_talbe )->where ( "table_name", $this->table_name )->where ( "field_name", $v )->find ()) {
					$d ['table_name'] = $this->table_name;
					$d ['field_name'] = $v;
					$d ['jqgrid_name'] = $v;
					$d ['jqgrid_index'] = $v;
					
					if ($tableinfo ['bind'] [$v] == PDO::PARAM_STR) {
						$d ['jqgrid_width'] = 50;
						$d ['jqgrid_align'] = 'right';
						$d ['jqgrid_sorttype'] = false;
					} else if ($tableinfo ['bind'] [$v] == PDO::PARAM_INT) {
						$d ['jqgrid_width'] = 11;
						$d ['jqgrid_align'] = 'left';
						$d ['jqgrid_sorttype'] = 'int';
					} else {
						$d ['jqgrid_width'] = 5;
						$d ['jqgrid_align'] = 'center';
						$d ['jqgrid_sorttype'] = false;
					}
					Db::name ( $this->jqgrid_talbe )->insert ( $d );
				}
			}
		}
	}
	function show() {
		if (empty ( $this->table_name )) {
			throw new Exception ( "table_name is empty" );
		}
		if (empty ( $this->table_id )) {
			$this->table_id = "list_" . $this->table_name;
		}
		if (empty ( $this->pager_id )) {
			$this->table_id = "pager_" . $this->table_name;
		}
		
		Db::name ( $this->jqgrid_talbe )->where ( "table_name", $this->table_name )->select ();
		
		$d=array();
		$d['datatype']=$this->js_jqg_datatype;
		$d['height']=$this->js_jqg_height;
		$d['colNames']="";
		echo '$(function(){'.$this->js_init_function.'();});';
		echo 'function '.$this->js_init_function.'(){';
		
		$d[''];
		echo '}';
	}
}