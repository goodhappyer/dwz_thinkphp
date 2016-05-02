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

\think\Loader::import('controller/Jump', TRAIT_PATH, EXT);

use think\View;

class Controller
{
    use \traits\controller\Jump;

    // 视图类实例
    protected $view = null;

    /**
     * 前置操作方法列表
     * @var array $beforeActionList
     * @access protected
     */
    protected $beforeActionList = [];

    /**
     * 架构函数
     * @access public
     */
    public function __construct()
    {
        $this->view = View::instance(Config::get('template'), Config::get('view_replace_str'));

        // 控制器初始化
        if (method_exists($this, '_initialize')) {
            $this->_initialize();
        }

        // 前置操作方法
        if ($this->beforeActionList) {
            foreach ($this->beforeActionList as $method => $options) {
                is_numeric($method) ?
                $this->beforeAction($options) :
                $this->beforeAction($method, $options);
            }
        }
    }

    /**
     * 前置操作
     * @access protected
     * @param string    $method     前置操作方法名
     * @param array     $options    调用参数 ['only'=>[...]] 或者['except'=>[...]]
     */
    protected function beforeAction($method, $options = [])
    {
        if (isset($options['only'])) {
            if (is_string($options['only'])) {
                $options['only'] = explode(',', $options['only']);
            }
            if (!in_array(ACTION_NAME, $options['only'])) {
                return;
            }
        } elseif (isset($options['except'])) {
            if (is_string($options['except'])) {
                $options['except'] = explode(',', $options['except']);
            }
            if (in_array(ACTION_NAME, $options['except'])) {
                return;
            }
        }

        if (method_exists($this, $method)) {
            call_user_func([$this, $method]);
        }
    }

    /**
     * 加载模板输出
     * @access public
     * @param string $template 模板文件名
     * @param array  $vars     模板输出变量
     * @param array $config     模板参数
     * @return mixed
     */
    public function fetch($template = '', $vars = [], $config = [])
    {
        return $this->view->fetch($template, $vars, $config);
    }

    /**
     * 渲染内容输出
     * @access public
     * @param string $content 模板内容
     * @param array  $vars     模板输出变量
     * @param array $config     模板参数
     * @return mixed
     */
    public function display($content = '', $vars = [], $config = [])
    {
        return $this->view->display($content, $vars, $config);
    }

    /**
     * 模板变量赋值
     * @access protected
     * @param mixed $name  要显示的模板变量
     * @param mixed $value 变量的值
     * @return void
     */
    public function assign($name, $value = '')
    {
        $this->view->assign($name, $value);
    }

    /**
     * 初始化模板引擎
     * @access protected
     * @param array|string $engine 引擎参数
     * @return void
     */
    public function engine($engine)
    {
        $this->view->engine($engine);
    }

    /**
     * 验证数据
     * @access protected
     * @param array $data 数据
     * @param string|array $validate 验证器名或者验证规则数组
     * @param array $message 提示信息
     * @param mixed $callback 回调方法（闭包）
     * @return void
     */
    public function validate($data, $validate, $message = [], $callback = null)
    {
        if (is_array($validate)) {
            $v = Loader::validate(Config::get('default_validate'));
            $v->rule($validate);
        } else {
            if (strpos($validate, '.')) {
                // 支持场景
                list($validate, $scene) = explode('.', $validate);
            }
            $v = Loader::validate($validate);
            if (!empty($scene)) {
                $v->scene($scene);
            }
        }

        if (is_array($message)) {
            $v->message($message);
        }

        if (is_callable($callback)) {
            call_user_func_array($callback, [$v, &$data]);
        }

        if (!$v->check($data)) {
            return $v->getError();
        } else {
            return true;
        }
    }
}
