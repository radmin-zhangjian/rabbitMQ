# rabbitMQ

## Introduction

RabbitMQ 安装

## Install

```
$ composer require yuqinglan/rabbitmq
或者 config.json 模式
"require": {
    "yuqinglan/rabbitMQ": ">=1.0",
    "php-amqplib/php-amqplib": ">=2.6.1"
}
```

## Demo

```php
命令提示行下

# 消费者
php topics_receive_log.php anonymous.error
php topics_receive_log.php anonymous.waring
php topics_receive_log.php anonymous.*
#
# 生产者
php topics_send_log.php anonymous.info
php topics_send_log.php anonymous.error
php topics_send_log.php anonymous.waring
#
# 封装 消费者 透传
$binding_keys = array_slice($argv, 1);  // array $binding_keys
(new TopicsReceive($binding_keys, ['heartbeat' => 0]))->worker();
# 封装 生产者 透传
$routing_key = isset($argv[1]) && !empty($argv[1]) ? $argv[1] : 'anonymous.info';
$send = new TopicsSend($routing_key);
$data = implode(' ', array_slice($argv, 2));
$url = 'http://test-www.cheoo.com/baseApi/';
$infoData = [
    'url' => $url . "/saas/sendJpush",  // 业务逻辑地址
    'header' => '',  // 非必填
    'isPost' => true,  // 非必填
    'timeout' => 10,  // 非必填
    'key' => $routing_key,
    'data' => [
        'type' => 2,
        'content' => '测试数据队列',
        'jpushSaasId' => [
            '1507bfd3f79568d9064',
            '160a3797c856fc362d0',
            '18171adc03528894c7d',
            '1a0018970a89e81fabc',
            '18071adc035b28a61b2',
            '101d8559096b09b929b',
            '170976fa8a8b48416b9',
            '191e35f7e069b6109ab'
        ]
    ]
];
for ($i=0; $i<10; $i++) {
    sleep(1);
    $infoData['data']['content'] = $infoData['data']['content'] . '_' . $data . '_' . $i;
    $res = $send->send($infoData);
    var_dump($res);
}
```

## config.json

```
{
    // 1. 项目命名空间
    "name": "yuqinglan/rabbitmq",
    // 2. 项目描述
    "description": "RabbitMQ 第一版测试",
    // 3. 项目类型
    "type": "library",
    // 4. 最低稳定版本，stable=稳固, RC, beta, alpha, dev=开发
    "minimum-stability": "stable",
    // 5. 要安装的依赖
    "require": {
        "php": ">=7.0",
        "php-amqplib/php-amqplib": ">=2.9.2"
    },
    "require-dev": {
        "php": ">=7.0",
        "php-amqplib/php-amqplib": ">=2.9.2"
    },
    // 6. 授权类型
    "license": "MIT",
    // 7. 作者
    "authors": [
        {
            "name": "zhangjian",
            "email": "6204000@qq.com"
        }
    ],
    // 8. 自动加载空间
    "autoload": {
        "psr-4": {
            "zhangjian\\rabbitMQ\\": ""
        }
    }
}
```