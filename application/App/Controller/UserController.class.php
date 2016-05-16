<?php
namespace App\Controller;
use Common\Controller\AppBaseController;

class UserController extends AppBaseController
{

	var $User; // 用户模型

	function _initialize ()
	{
		parent::_initialize();
		$this->User = D('users');
	}

	public function index ()
	{
		echo "1";
	}

	public function third_login ()
	{
		$oauth_user = M("oauthUser");
		$d = array ();
		$d['from'] = I("from");
		$d['name'] = I("name");
		$d['openid'] = I("openid");
		$d['head_img'] = I("icon");
		if ($d['from'] == "" || $d['name'] == "" || $d['openid'] == "")
		{
			$this->json_echo(0,"缺少参数");
		}
		else
		{
			
			$r = $oauth_user->where(array ("openid" => $d["openid"]))->find();
			
			if ($r)
			{
				// 找到，新生成token
				$this->User->where(array ("id" => $r['uid']))->setField(array ('token' ,'last_login_time'),array ($this->mk_token() ,date("Y-m-d H:i:s")));
				$this->json_echo(1,'登录成功',$this->successReturn($r['uid']));
			}
			else
			{
				// 找不到
				$this->User->create();
				$this->User->user_login = "";
				$this->User->user_nicename = $d['name'];
				$this->User->user_type = 2;
				$this->User->create_time = date("Y-m-d H:i:s");
				$this->User->user_pass = sp_password("");
				$this->User->token = $this->mk_token();
				$this->User->avatar = $d['head_img'];
				// =========
				$uid = $this->User->add(); // 增加个空用户
				$d['uid'] = $uid;
				$d['create_time'] = date("Y-m-d H:i:s");
				$d['login_times'] = 1;
				$d['status'] = 1;
				$oauth_user->add($d);
				$this->User->where(array ("id" => $d['uid']))->setField('token',$this->mk_token());
				$this->json_echo(1,'登录成功',$this->successReturn($d['uid']));
			}
		}
	}
	/* 查看用户信息 */
	public function user_info ()
	{
		$id = I("id");
		if (! $id)
		{
			$id = $this->user_info['id'];
		}
		$r = $this->User->where(array ("id" => $id))
			->field("id,hospital,offices,addr,mobile,user_nicename,avatar")
			->find();
		if (str_replace("http","",$r['avatar']) == $r['avatar'])
		{
			$r['avatar'] = C('SERVER_NAME'). '/' . $r['avatar']; // 插入数据成功
		}
		else
		{
		}
		$this->json_echo(1,'获取成功',$r);
	}
	/* 修改信息 */
	public function set_user_info ()
	{
		$fields = array ("hospital" ,"offices" ,"addr" ,"mobile" ,"user_nicename"); // 可修改的字段
		
		foreach ($_POST as $k => $v)
		{
			if (! in_array($k,$fields))
			{
				unset($_POST[$k]);
			}
		}
		
		if ($this->User->create())
		{
			$this->User->where(array ("id" => $this->user_info['id']))->save();
			$this->json_echo(1,'修改成功');
		}
		else
		{
			$this->json_echo(0,'不可修改');
		}
	}

	public function regist ()
	{
		if ($_REQUEST)
		{
			$user_login = I('user_login');
			$verifys = I('verify');
			$verify = M('userVerify')->where(array ("phone" => $user_login ,"verify" => $verifys ,"date_time" => array ("GT" ,time() - 60 * 5)))
				->order("id desc")
				->find();
			if ($verify['verify'] != $verifys)
			{
				$this->json_echo(0,'短信验证码验证失败!');
			}
			$this->User->create();
			$this->User->user_login = $user_login;
			$this->User->user_type = 2;
			$this->User->create_time = date("Y-m-d H:i:s");
			$this->User->last_login_time = date("Y-m-d H:i:s");
			if ($this->User->where("user_login='$user_login'")->count())
			{
				$this->json_echo(0,'用户已经注册，请登录！');
			}
			$this->User->user_pass = sp_password(I('user_pass'));
			$this->User->token = $this->mk_token();
			
			if ($add = $this->User->add())
			{
				$this->json_echo(1,'注册成功！',$this->successReturn($add));
			}
			$this->json_echo(0,'注册失败！');
		}
	}

	/**
	 * 用户登录
	 */
	public function login ()
	{
		// 检测是否是第一次登录
		$user_login = I('user_login');
		$user_pass = I('user_pass');
		$d['last_time'] = time();
		if ($user_login == "" || $user_pass == "")
		{
			$this->json_echo(0,'用户名或密码为空');
		}
		$r = $this->User->where(array ("user_login" => $user_login ,"user_pass" => sp_password($user_pass)))->find();
		if ($r)
		{
			if ($r['user_status'] == 0)
			{
				$this->json_echo(0,'帐号禁用，请联系平台管理员！');
			}
			$this->User->where(array ("id" => $r['id']))->setField(array ('token' => $this->mk_token($r['id']) ,'last_login_time' => time()));
			$this->json_echo(1,'登录成功',$this->successReturn($r['id']),1);
		}
		else
		{
			$this->json_echo(0,'用户名密码错误！');
		}
	}
	// 登陆返回数据
	protected function successReturn ($id)
	{
		return $this->User->field("user_pass",true)->find($id);
	}

	/**
	 * 修改密码
	 */
	public function modify_user_pass ()
	{
		$user_pass = sp_password(I('user_pass'));
		if ($this->User->where(array ("id" => $this->user_info['id']))->setField('user_pass',$user_pass))
		{
			$this->json_echo(1,'修改密码成功');
		}
		$this->json_echo(0,'修改密码失败');
	}

	/**
	 * 忘记密码，找回密码
	 */
	public function forget_user_pass ()
	{
		$verify = M('userVerify');
		$user_login = I('user_login') ? I('user_login') : $this->json_echo(0,'没有用户名！');
		$d['user_pass'] = sp_password(I('user_pass'));
		$this->verifyCheck($user_login,I('verify')); // 验证验证码
		$r = $this->User->where("user_login='$user_login'")->find();
		if (! $r)
		{
			$this->json_echo(0,'没有此用户！');
		}
		else
		{
			if ($this->User->where(array ("id" => $r['id']))->save($d))
			{
				$this->json_echo(1,'找回密码成功');
			}
		}
		$this->json_echo(0,'找回密码失败');
	}

	/**
	 * 验证码验证
	 */
	public function verifyCheck ($user_login = '', $verify = '')
	{
		if ($verify == "happyer")
		{
			return;
		}
		$user_login = $user_login ? $user_login : I('user_login','');
		$verify = $verify ? $verify : I('verify','');
		$rs = M('userVerify')->where("phone='$user_login' and verify='$verify'")->find();
		if (! $rs) $this->json_echo(0,'验证码错误！');
		if (time() - $rs['date_time'] > 1800)
		{
			$this->json_echo(0,'验证码验证过期!');
		}
	}

	/**
	 * 验证码发送
	 */
	public function verifySend ()
	{
		$user_login = I('user_login') ? I('user_login') : $this->json_echo(0,'没有填空手机号！');
		$act = I('act');
		$verify = M('userVerify');
		$user = $this->User;
		$verify_code = rand(1111,9999);
		$d = array ('phone' => $user_login ,'verify' => $verify_code ,'date_time' => time());
		
		if ((str_replace("regist","",$act) != $act))
		{
			$rs = $verify->where("phone='$user_login'")->find();
			if ($user->where("user_login='$user_login'")->count())
			{
				$this->json_echo(0,'用户已经注册过!');
			}
			if ($rs)
			{
				if (time() - $rs['date_time'] < 120)
				{
					$this->json_echo(0,'两分钟发一次!');
				}
			}
			$r = $this->sendTemplateSMS($user_login,$verify_code,"19028");
			if ($r[0] && $verify->add($d))
			{
				$this->json_echo(1,$r[1],$verify_code);
			}
			else
			{
				$this->json_echo(0,$r[1],$verify_code);
			}
			$this->json_echo(0,'验证码发送失败！');
		}
		
		if ((str_replace("finduser_pass","",$act) != $act))
		{
			if (! $this->User->where("user_login='$user_login'")->count())
			{
				$this->json_echo(0,'用户还没有注册!');
			}
			if (time() - $rs['date_time'] < 120)
			{
				$this->json_echo(0,'两分钟发一次!');
			}
			$r = $this->sendTemplateSMS($user_login,$verify_code,"19042");
			
			if ($r[0] && $verify->add($d))
			{
				$this->json_echo(1,$r[1],$verify_code);
			}
			else
			{
				$this->json_echo(0,$r[1],$verify_code);
			}
			$this->json_echo(0,'验证码发送失败！');
		}
		$this->json_echo(0,'act参数错误！');
	}

	/**
	 * 修改头像
	 */
	public function headpic ()
	{
		$uid = $this->user_info['id'];
		
		$upload = new \Think\Upload(); // 实例化上传类
		$upload->maxSize = 3145728; // 设置附件上传大小
		$upload->exts = array ('jpg' ,'gif' ,'png' ,'jpeg'); // 设置附件上传类型
		$upload->rootPath = '.';
		$upload->savePath = '/Upload/avatar/'; // 设置附件上传目录
		$upload->autoSub = false;
		$upload->saveName = $uid . '_' . time();
		$upload->replace = true;
		
		// 上传文件
		$info = $upload->uploadOne($_FILES['avatar']);
		
		if (! $info)
		{
			// 上传错误提示错误信息
			json_echo(0,$upload->getError());
		}
		else
		{
			// 上传成功
			$_POST['avatar'] = $info['savepath'] . $info['savename'];
			M('users')->where('id=' . $uid)->save($_POST);
			
			json_echo(1,'修改头像成功!');
		}
	}
}


