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

namespace zhangjian\rabbitMQ;


//require_once __DIR__ . '/../../vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;


//$routing_key = isset($argv[1]) && !empty($argv[1]) ? $argv[1] : 'anonymous.info';
//$send = new TopicsSend($routing_key);
//$data = implode(' ', array_slice($argv, 2));
//$url = 'http://test-www.cheoo.com/baseApi/';
//$infoData = [
//    'url' => $url . "/saas/sendJpush",  // 业务逻辑地址
//    'header' => '',  // 非必填
//    'isPost' => true,  // 非必填
//    'timeout' => 10,  // 非必填
//    'key' => $routing_key,
//    'data' => [
//        'type' => 2,
//        'content' => '测试数据队列',
//        'jpushSaasId' => [
//            '1507bfd3f79568d9064',
//            '160a3797c856fc362d0',
//            '18171adc03528894c7d',
//            '1a0018970a89e81fabc',
//            '18071adc035b28a61b2',
//            '101d8559096b09b929b',
//            '170976fa8a8b48416b9',
//            '191e35f7e069b6109ab'
//        ]
//    ]
//];
//for ($i=0; $i<10; $i++) {
//    sleep(1);
//    $infoData['data']['content'] = $infoData['data']['content'] . '_' . $data . '_' . $i;
//    $res = $send->send($infoData);
//    var_dump($res);
//}


class TopicsSend
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

    /**
     * @var string $data
     */
    protected $data;

    /**
     * @var string $exchange
     */
    protected $exchange;

    /**
     * @var string $type
     */
    protected $type;

    public function __construct($argv, $param = [], $exchange = 'anonymous', $type = 'topic')
    {
        $host = isset($param['host']) && !empty($param['host']) ? $param['host'] : 'localhost';
        $port = isset($param['port']) && !empty($param['port']) ? $param['port'] : 5672;
        $user = isset($param['user']) && !empty($param['user']) ? $param['user'] : 'zhangjian';
        $password = isset($param['password']) && !empty($param['password']) ? $param['password'] : 'zhangjian';
        $vhost = isset($param['vhost']) && !empty($param['vhost']) ? $param['vhost'] : '/';
        $insist = isset($param['insist']) && !empty($param['insist']) ? $param['insist'] : false;
        $login_method = isset($param['login_method']) && !empty($param['login_method']) ? $param['login_method'] : 'AMQPLAIN';
        $login_response = isset($param['login_response']) && !empty($param['login_response']) ? $param['login_response'] : null;
        $locale = isset($param['locale']) && !empty($param['locale']) ? $param['locale'] : 'en_US';
        $connection_timeout = isset($param['connection_timeout']) && !empty($param['connection_timeout']) ? $param['connection_timeout'] : 3.0;
        $read_write_timeout = isset($param['read_write_timeout']) && !empty($param['read_write_timeout']) ? $param['read_write_timeout'] : 130;
        $context = isset($param['context']) && !empty($param['context']) ? $param['context'] : null;
        $keepalive = isset($param['keepalive']) && !empty($param['keepalive']) ? $param['keepalive'] : false;
        $heartbeat = isset($param['heartbeat']) && !empty($param['heartbeat']) ? $param['heartbeat'] : 60;
        $channel_rpc_timeout = isset($param['channel_rpc_timeout']) && !empty($param['channel_rpc_timeout']) ? $param['channel_rpc_timeout'] : 0.0;
        $ssl_protocol = isset($param['ssl_protocol']) && !empty($param['ssl_protocol']) ? $param['ssl_protocol'] : null;
        $this->connection = new AMQPStreamConnection(
            $host,
            $port,
            $user,
            $password,
            $vhost,
            $insist,
            $login_method,
            $login_response,
            $locale,
            $connection_timeout,
            $read_write_timeout,
            $context,
            $keepalive,
            $heartbeat,
            $channel_rpc_timeout,
            $ssl_protocol
        );
        $this->channel = $this->connection->channel();
        $this->argv = $argv;
        $this->exchange = $exchange;
        $this->type = $type;
    }

    public function send($data)
    {
        /**
         * 声明交换 创建交换机logs
         * fanout = 扇出交换
         * direct = 直接交换
         * topic = 主题交换
         */
        $this->channel->exchange_declare($this->exchange, $this->type, false, false, false);

        /**
         * 接收数据
         * 设置队列名称 默认：anonymous.info [anonymous.waring, anonymous.error] / anonymous.* / anonymous.#
         */
        $routing_key = isset($this->argv) && !empty($this->argv) ? $this->argv : 'anonymous.info';
        if (empty($data)) {
            return ['code' => 2, 'msg' => 'data is empty!'];
        }

        /**
         * 发布到队列
         * 同一个交换机[Exchanges]下 可以创建多个不同队列 $routing_key
         */
        $msg = new AMQPMessage(json_encode($data));
        $this->channel->basic_publish($msg, $this->exchange, $routing_key);

        return ['code' => 0, 'msg' => 'success', 'key' => $routing_key, 'data' => json_encode($data)];

    }

    public function __destruct()
    {
        $this->channel->close();
        $this->connection->close();
    }

}