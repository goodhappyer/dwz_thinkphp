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

	protected $menu = array ();

	protected $_mod;

	protected $search = [ ]; // 显示查找数据

	protected $_search = [ ]; // 查找数据库的字段

	protected $_search_like_fields;

	protected $view;

	public function _initialize ()
	{
		session_start();
		parent::_initialize();
		$this->_check_priv();
		if ($this->_mod == null)
		{
			if ($this->_name != null)
			{
				$modepath = 'app\\common\\model\\' . $this->_name;
				$this->_mod = new $modepath();
			}
			else
			{
				die('$this->name is null');
			}
		}
		$this->view = new View();
		$this->view->assign("menu",$this->menu);
	}

	protected function trigger ($fun, &$params = array())
	{
		// 函数存在
		if (method_exists($this,$fun))
		{
			call_user_func(array ($this ,$fun),$params);
		}
		else
		{
			return false;
		}
		return true;
	}

	public function before_index ()
	{
		$menu_id = Input::get("menu_id");
		$admin_menu = new \app\common\model\AdminMenu();
		$menu_list = $admin_menu->get_mymeun($menu_id,0);
		$this->view->assign("menu",$menu_list);
	}
	/*
	 * dwz 返回结果
	 * $statusCode 200 成功 300 失败 301需要登陆
	 */
	public function dwz_ajax_return ($statusCode, $message, $navTabId, $callbackType, $forwardUrl)
	{
		$T = array ();
		$T['statusCode'] = $statusCode;
		$T['message'] = $message;
		$T['navTabId'] = $navTabId;
		$T['callbackType'] = $callbackType;
		$T['forwardUrl'] = $forwardUrl;
		echo json_encode($T);
		exit();
	}

	public function dwz_ajax_return_ok ($message = "操作成功", $navTabId = "", $callbackType = "closeCurrent", $forwardUrl = "")
	{
		$statusCode = 200;
		$this->dwz_ajax_return($statusCode,$message,$navTabId,$callbackType,$forwardUrl);
	}

	public function dwz_ajax_return_err ($message = "操作失败", $navTabId = "", $callbackType = "", $forwardUrl = "")
	{
		$statusCode = 300;
		$this->dwz_ajax_return($statusCode,$message,$navTabId,$callbackType,$forwardUrl);
	}

	protected function _check_priv ()
	{
		if (CONTROLLER_NAME == 'login')
		{
			return true;
		}
		$user = model("AdminUser");
		$this->visitor = $user->check_token(Session::get("token"));
		if ($this->visitor == null)
		{
			// $this->redirect("login/index");
		}
	}
}
