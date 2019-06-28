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


//require_once __DIR__ . '/../../vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
ini_set('default_socket_timeout', 120);


//$binding_keys = array_slice($argv, 1);
//(new TopicsReceive($binding_keys, ['heartbeat' => 0]))->worker();

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

    public function worker()
    {
        /**
         * 声明交换 创建交换机logs
         * fanout = 扇出交换
         * direct = 直接交换
         * topic = 主题交换
         */
        $this->channel->exchange_declare($this->exchange, $this->type, false, false, false);

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
         * 同一个交换机[Exchanges]下 可以创建多个不同队列 $routing_key
         */
        foreach ($binding_keys as $binding_key) {
            $this->channel->queue_bind($queue_name, $this->exchange, $binding_key);
        }

        echo " [*] Waiting for logs. To exit press CTRL+C\n";

        /**
         * 回调函数
         */
        $callback = function($msg) {
//            echo ' [x] ', $msg->delivery_info['routing_key'], ':', $msg->body, "\n";
            $key = $msg->delivery_info['routing_key'];
            $data = json_decode($msg->body, true);
            if (!empty($data)) {
                if ($key == $data['key']) {
                    $dataStr = http_build_query($data['data']);
                    $header = isset($data['header']) ? $data['header'] : '';
                    $isPost = isset($data['isPost']) ? $data['isPost'] : true;
                    $timeout = isset($data['timeout']) ? $data['timeout'] : 5;
                    $response = self::curl($data['url'], $header, $dataStr, $isPost, $timeout);
                    echo date('Y-m-d H:i:s', time()) . "：" . $response . "\n";
                } else {
                    echo "key错误:{$data['key']}-{$key}-".json_encode($data['data'])."\n";
                }
            }
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

    // 签名附加在 HTTP header 中，字段名为 x-izayoi-sign
    public static function curl($url, $header = [], $dataStr = "", $isPost = false, $timeOut = 20, $referer = '', $cookie = '')
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeOut);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeOut);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        if ($header) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        }
        if ($referer) {
            curl_setopt($ch, CURLOPT_REFERER, $referer);
        } else {
            curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        }
        if ($isPost) {
            if ($cookie) {
                curl_setopt($ch, CURLOPT_COOKIE, $cookie);
            }
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $dataStr);
            curl_setopt($ch, CURLOPT_URL, $url);
        } else {
            if (empty($dataStr)) {
                curl_setopt($ch, CURLOPT_URL, $url);
            } else {
                curl_setopt($ch, CURLOPT_URL, $url . '?' . $dataStr);
            }
        }
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }

}
