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
...

# 生产者
php topics_send_log.php anonymous.info
php topics_send_log.php anonymous.error
php topics_send_log.php anonymous.waring

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