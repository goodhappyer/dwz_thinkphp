<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2009 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
namespace Org\Util;
require_once 'simplewind/Core/Library/Vendor/jpush/autoload.php';

use JPush\Model as M;
use JPush\JPushClient;
use JPush\JPushLog;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use JPush\Exception\APIConnectionException;
use JPush\Exception\APIRequestException;


/**
 * 通用的树型类，可以生成任何树型结构
 */
class Jpusht {

    public function tuo(){
        $app_key='dd1066407b044738b6479275';
        $master_secret = '6b135be0037a5c1e693c3dfa';
        JPushLog::setLogHandlers(array(new StreamHandler('jpush.log', Logger::DEBUG)));
        $client = new JPushClient($app_key, $master_secret);
        $push = $client->push();
        $push->setPlatform('all');
        $push->setAudience('all');
//        $push->setNotificationAlert($alert);
        $push ->setMessage(array($msg_content='Message Content', $title='n', $content_type='1', $extras='2'));
//        $push ->build();
        $push ->printJSON();
    }

}

