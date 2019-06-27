<?php
// composer 模式
//https://www.rabbitmq.com/tutorials/tutorial-one-php.html
//composer.json
//{
//    "require": {
//        "php-amqplib/php-amqplib": ">=2.6.1"
//    }
//}

/**
 *  https://www.rabbitmq.com/tutorials/tutorial-five-php.html
 *  主题交流
 *  主题交换功能强大，可以像其他交易所一样运行。
 *  当队列绑定“ ＃ ”（哈希）绑定密钥时 - 它将接收所有消息，而不管路由密钥 - 如扇出交换。
 *
 *  当特殊字符“ * ”（星号）和“ ＃ ”（哈希）未在绑定中使用时，[主题交换]的行为就像[直接交换]一样。  ++++++
 */

/**
 * 生产者 主题
 */
require_once __DIR__ . '/../../vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * 创建链接
 */
$connection = new AMQPStreamConnection('localhost', 5672, 'zhangjian', 'zhangjian');
$channel = $connection->channel();

/**
 * 声明交换 创建交换机logs
 * fanout = 扇出交换
 * direct = 直接交换
 * topic = 主题交换
 */
$channel->exchange_declare('topic_logs', 'topic', false, false, false);

/**
 * 接收数据
 * 设置队列名称 默认：anonymous.info [anonymous.waring, anonymous.error] / anonymous.* / anonymous.#
 */
$routing_key = isset($argv[1]) && !empty($argv[1]) ? $argv[1] : 'anonymous.info';
$data = implode(' ', array_slice($argv, 2));
if (empty($data)) {
    $data = "Hello World!";
}

/**
 * 发布到队列
 * 同一个交换机[topic_logs]下 可以创建多个不同队列 $routing_key
 */
$msg = new AMQPMessage($data);
$channel->basic_publish($msg, 'topic_logs', $routing_key);

// 测试
for ($i=0; $i<10; $i++) {
    sleep(1);
    $msg = new AMQPMessage($data . '_' . $i);
    $channel->basic_publish($msg, 'topic_logs', $routing_key);
}

echo ' [x] Sent ', $routing_key, ':', $data, "\n";

$channel->close();
$connection->close();