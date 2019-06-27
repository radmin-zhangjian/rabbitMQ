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
 * 消费者 主题
 */
namespace zhangjian\rabbitMQ;


require_once __DIR__ . '/../../vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
ini_set('default_socket_timeout', 120);


//$binding_keys = array_slice($argv, 1);
//(new TopicsReceive($binding_keys))->worker();

class TopicsReceive
{
    /**
     * @var AMQPStreamConnection
     */
    public $connection;

    /**
     * @var \PhpAmqpLib\Channel\AMQPChannel
     */
    public $channel;

    /**
     * @var array $argv
     */
    protected $argv;

    public function __construct($argv)
    {
        $this->connection = new AMQPStreamConnection(
            'localhost',
            5672,
            'zhangjian',
            'zhangjian',
            '/',
            false,
            'AMQPLAIN',
            null,
            'en_US',
            3.0,
            130,
            null,
            false,
            0
        );
        $this->channel = $this->connection->channel();
        $this->argv = $argv;
    }

    public function worker()
    {
        /**
         * 声明交换 创建交换机logs
         * fanout = 扇出交换
         * direct = 直接交换
         * topic = 主题交换
         */
        $this->channel->exchange_declare('topic_logs', 'topic', false, false, false);

        /**
         * 持久性
         * 第三个参数为 true 标记为消息持久性
         */
        list($queue_name, ,) = $this->channel->queue_declare("", false, true, true, false);

        /**
         * 输入参数
         * 设置队列名称 默认：anonymous.info [anonymous.waring, anonymous.error]
         */
        $binding_keys = $this->argv;
        if (empty($binding_keys)) {
            file_put_contents('php://stderr', "Usage: $this->argv [binding_key]\n");
            exit(1);
        }

        /**
         * 交换和队列之间进行绑定
         * 同一个交换机[topic_logs]下 可以创建多个不同队列 $routing_key
         */
        foreach ($binding_keys as $binding_key) {
            $this->channel->queue_bind($queue_name, 'topic_logs', $binding_key);
        }

        echo " [*] Waiting for logs. To exit press CTRL+C\n";

        /**
         * 回调函数
         */
        $callback = function($msg) {
            echo ' [x] ', $msg->delivery_info['routing_key'], ':', $msg->body, "\n";
        };

        /**
         * 回调函数
         * 告诉RabbitMQ这个特定的回调函数应该从我们的 $queue_name 队列接收消息
         * 第四个参数为 false = 手动确认发送ACK应答  true = 隐士自动应答成功
         */
        $this->channel->basic_consume($queue_name, '', false, false, false, false, $callback);

        while (count($this->channel->callbacks)) {
            $this->channel->wait();
        }

        $this->channel->close();
        $this->connection->close();
    }

    public function __destruct()
    {
//        $this->channel->close();
//        $this->connection->close();
    }
}
