<?php
/**
 * 控制器基类
 */
namespace app\common\controller;
use think\Controller;
use think\Db;
use think\Cache;
use app\common\model\Setting;

class Base extends Controller
{
	protected $visitor = null; //访问者
	protected function _initialize() 
	{
		/*初始化配置*/
		$setting=new Setting;
		$r=$setting->all();
		foreach($r as $v)
		{
			Cache::set($v['name'],$v['data']);
		}
	}
	public function index()
	{
		echo "test";
	}
	/**
	 * 加密
	 *
	 * @param unknown $code        	
	 * @return string
	 */
	protected function encrypt($code) {
		return base64_encode ( mcrypt_encrypt ( MCRYPT_RIJNDAEL_256, md5 ( "" ), $code, MCRYPT_MODE_ECB, mcrypt_create_iv ( mcrypt_get_iv_size ( MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB ), MCRYPT_RAND ) ) );
	}
	/**
	 * 解密
	 *
	 * @param unknown $code        	
	 * @return string
	 */
	protected function decrypt($code) {
		return mcrypt_decrypt ( MCRYPT_RIJNDAEL_256, md5 ( "" ), base64_decode ( $code ), MCRYPT_MODE_ECB, mcrypt_create_iv ( mcrypt_get_iv_size ( MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB ), MCRYPT_RAND ) );
	}

}
