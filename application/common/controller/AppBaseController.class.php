<?php
namespace Common\Controller;
use Think\Controller;

/**
 *
 * @author Administrator
 *        
 */
class AppBaseController extends Controller
{

	var $user_info; // 登陆用户信息

	function _initialize ()
	{
		header("Content-type:text/html;charset=utf-8");
		if (ACTION_NAME == 'regist' || ACTION_NAME == 'third_login' || ACTION_NAME == "login" || ACTION_NAME == "forget_user_pass" || ACTION_NAME == "verifySend")
		{
			
		}
		else
		{
			$this->check_token();
		}
	}
	// 输出一个html5页面
	function html5 ($content)
	{
		$str = '<!DOCTYPE HTML><html><head>';
		$str .= '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="Cache-Control" content="no-cache"/>
<meta name="viewport" content="width=device-width; initial-scale=1;  minimum-scale=1.0; maximum-scale=1.4"/>
<meta name="MobileOptimized" content="240"/>
<style>
p img,img{display:block;margin:0 auto; max-width:340px;vertical-align: middle;}
p{display:block;max-width:340px;}</style>
</head><body>';
		$str .= '<div>' . $content . "</div>";
		$str .= '</body></html>';
		echo $str;
	}
	// 检测token
	function check_token ()
	{
		$token = I('token');
		if (! $token)
		{
			$this->json_echo(402,"没有登陆");
		}
		$rs = M('users')->where("token='$token' and user_type=2")->find();
		if (! $rs)
		{
			$this->json_echo(402,"没有登陆");
		}
		if ($rs['user_status'] != 1)
		{
			$this->json_echo(403,'用户被封禁或待验证！请联系客服');
		}
		$this->user_info = $rs;
	}
	// 生产token
	function mk_token ($id)
	{
		// return time () . mt_rand () . mt_rand () . mt_rand ();
		return $id;
	}

	/**
	 * 输出前图片加网址
	 *
	 * @param unknown $array        	
	 */
	function _add_http_imgage (&$array)
	{
		if (is_array($array))
		{
			foreach ($array as $k => $v)
			{
				$this->_add_http_imgage($array[$k]);
			}
		}
		else
		{
			$abc = substr($array,- 4);
			if (strncasecmp($abc,".jpg",4) == 0 || strncasecmp($abc,".png",4) == 0 || strncasecmp($abc,".bmp",4) == 0 || strncasecmp($abc,".gif",4) == 0)
			{
				if (strncasecmp(substr($array,0,4),"http",4) == 0)
				{
				}
				else
				{
					$array = C('SERVER_NAME') .'/'. $array;
				}
			}
		}
	}

	/**
	 * 手机接口输出
	 *
	 * @param number $code
	 *        	注意： code=0是操作失败
	 *        	code=1是操作成功
	 *        	code =402 需要等录,直接跳登录页面
	 *        	code=403,禁用,用户被封禁或待验证！请联系客服
	 *        	msg：是操作因原
	 * @param string $msg
	 *        	提示信息
	 * @param
	 *        	have_img是否有图，如果有图就加网址
	 * @param arrat $result
	 *        	必须是数组
	 *        	
	 */
	public function json_echo ($code = 0, $msg = '', $result = array(), $have_img = 0)
	{
		if ($have_img)
		{
			$this->_add_http_imgage($result);
		}
		$arr = array ("code" => $code ,"msg" => $msg ,"result" => $result);
		echo json_encode($arr);
		exit(0);
	}
	
	/*
	 * 用户导出excel :
	 */
	function exportexcel ($data = array(), $title = array(), $filename = 'report')
	{
		header("Content-type:application/octet-stream");
		header("Accept-Ranges:bytes");
		header("Content-type:application/vnd.ms-excel");
		header("Content-Disposition:attachment;filename=" . $filename . ".xls");
		header("Pragma: no-cache");
		header("Expires: 0");
		// 导出xls 开始
		if (! empty($title))
		{
			foreach ($title as $k => $v)
			{
				$title[$k] = iconv("UTF-8","GB2312",$v);
			}
			$title = implode("\t",$title);
			echo "$title\n";
		}
		if (! empty($data))
		{
			foreach ($data as $key => $val)
			{
				foreach ($val as $ck => $cv)
				{
					$val[$ck] = iconv("UTF-8","GB2312",$cv);
					if (strlen($val[$ck]) < 5)
					{
						$val[$ck] = "121";
					}
				}
				$data[$key] = implode("\t",$val);
			}
			echo implode("\n",$data);
		}
		exit();
	}

	/**
	 * 发送模板短信
	 */
	function sendTemplateSMS ($to, $datas, $tempId)
	{
		import("vendor.Ucpaas.Ucpaas#class","",".php"); // 导入发短信类
		$options['accountsid'] = 'acf19e34404c5abcfe9ae3ffce39a40d';
		$options['token'] = 'eaca10c3602cbaac3a92f632b9b7ac31';
		
		$ucpass = new \Ucpaas($options);
		$r = $ucpass->templateSMS("f99e4b81b48845738f4017ea09564ae2",$to,$tempId,$datas);
		file_put_contents("templateSMS.txt",$r,FILE_APPEND);
		$r = json_decode($r,true);
		file_put_contents("templateSMS.txt",var_export($r,TRUE),FILE_APPEND);
		if (intval($r['resp']['respCode']) == 0)
		{
			return array (0 => true ,1 => "发送成功");
		}
		else
		{
			if (intval($r['resp']['respCode']) == 100005)
			{
				return array (0 => false ,1 => "错误IP");
			}
			if (intval($r['resp']['respCode']) == 100006 || intval($r['resp']['respCode']) == 100015)
			{
				return array (0 => false ,1 => "手机号错有误");
			}
			if (intval($r['resp']['respCode']) == 105122)
			{
				return array (0 => false ,1 => "于由运营商原因，你一天只能接收五条短信！");
			}
			else
			{
				return array (0 => false ,1 => "发送失败！");
			}
		}
	}
	// 生成8位邀请码
	public function mk_rand ($table, $field)
	{
		$rand = rand(10000000,99999999);
		if ($count = M($table)->where($field . "='$rand'")->count())
		{
			return $this->mk_rand($table,$field);
		}
		return $rand;
	}
}