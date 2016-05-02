<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2015 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: yunwuxin <448901948@qq.com>
// +----------------------------------------------------------------------

/**
 * ThinkPHP CLI模式定义
 */
return [

    // 命名空间
    'namespace' => [
        'think'       => LIB_PATH . 'think' . DS,
        'behavior'    => LIB_PATH . 'behavior' . DS,
        'traits'      => LIB_PATH . 'traits' . DS,
        APP_NAMESPACE => APP_PATH,
    ],
    // 别名定义
    'alias'     => [
        'think\App'   => MODE_PATH . 'console/App' . EXT,
        'think\Error' => MODE_PATH . 'console/Error' . EXT
    ],
    // 配置文件
    'config'    => THINK_PATH . 'convention' . EXT

];
