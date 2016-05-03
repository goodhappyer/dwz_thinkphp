<?php
namespace app\common\model;

class AdminUser extends \think\Model
{
	// 定义需要自动写入时间戳格式的字段
	protected $autoTimeField = ['create_time','update_time'];
	// 以上定义需要配合insert、update或者auto才能生效
	protected $insert = ['create_time'];
	protected $update = ['update_time'];
	use UserTrait;
}
