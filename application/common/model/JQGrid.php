<?php 
namespace app\common\model;
use think\Input;

class JQGrid
{
	var $table_id="list";
	var $pager_id="pager";
	var $js_init_function="pageInit";
	var $js_jqg_datatype="local";
	var $js_jqg_height="250";
	var $js_jqg_colNames=array();
	var $js_jqg_colModel=array();
	var $js_jqg_multiselect=true;
	var $js_jqg_caption="";
	var $js_jqg_data=array();
	function ($table)
}