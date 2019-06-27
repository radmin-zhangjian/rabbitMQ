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