<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

namespace think;

use think\Exception;

class UploadFile
{

    /**
     * 上传驱动实例
     * @var Object
     */
    private $uploader;

    /**
     * 上传文件信息
     * @var array
     */
    protected $info = [];

    /**
     * 上传错误信息
     * @var string
     */
    private $error = ''; //上传错误信息

    public function __construct($file)
    {
        $this->info = $file;
    }

    public function isValid($rule = [])
    {

    }

    protected function checkPath($path)
    {

        if (is_dir($path)) {
            return true;
        }

        if (mkdir($path, 0777, true)) {
            return true;
        } else {
            $this->error = "目录 {$savepath} 创建失败！";
            return false;
        }
    }

    /**
     * 移动文件
     * @param  string   $path    保存路径
     * @param  string   $savename    保存的文件名
     * @param  boolean $replace 同名文件是否覆盖
     * @return boolean          保存状态，true-成功，false-失败
     */
    public function moveTo($path, $savename = '', $replace = true)
    {
        if (false === $this->checkPath($path)) {
            return false;
        }

        $savename = $savename ?: $this->info['name'];
        /* 不覆盖同名文件 */
        if (!$replace && is_file($path . $savename)) {
            $this->error = '存在同名文件' . $path . $savename;
            return false;
        }

        /* 移动文件 */
        if (!move_uploaded_file($this->info['tmp_name'], $path . $savename)) {
            $this->error = '文件上传保存错误！';
            return false;
        }
        return true;
    }

    /**
     * 设置上传驱动
     * @param string $driver 驱动名称
     * @param array $config 驱动配置
     */
    private function setDriver($driver = null, $config = null)
    {
        $class = strpos($driver, '\\') ? $driver : '\\think\\file\\driver\\' . ucfirst(strtolower($driver));
        if (class_exists($class)) {
            $this->uploader = new $class($config);
        } else {
            throw new Exception('class [ ' . $class . ' ] not exists', 10001);
        }
    }

    /**
     * 调用上传驱动类的方法
     * @access public
     * @param string $method 方法名称
     * @param array $args 调用参数
     * @return mixed
     */
    public function __call($method, $args)
    {
        return call_user_func_array([$this->uploader, $method], $args);
    }

}
