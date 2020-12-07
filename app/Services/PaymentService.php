<?php


namespace App\Services;

use App\Order;
use GuzzleHttp\Client;
use Illuminate\Support\Str;

class PaymentService
{

    const API_URL = "https://mapi.yuansfer.com/online/v2/secure-pay";
    const API_URL_TEST = "https://mapi.yuansfer.yunkeguan.com/online/v2/secure-pay";
    const STATUS_SUCCESS = "000100";

    protected static $providers = [
        'alipay' => 'alipay',
        'wechat' => 'wechatpay',
        'union' => 'unionpay',
        'enterprise' => 'enterprisepay',
        'credit' => 'creditcard'
    ];

    /** @var array */
    protected $config;

    /** @var Client  */
    protected $client;

    public function __construct(array $config)
    {
        $this->client = new Client();
        $this->config = $config;
    }

    /**
     * @param array $config
     * @return PaymentService
     */
    public static function create(array $config)
    {
        return new PaymentService($config);
    }

    /**
     * @param $gateway
     * @return bool|mixed
     */
    public static function isYuansfer($gateway)
    {
        $gateway = Str::lower($gateway);
        foreach (self::$providers as $key => $val) {
            if(Str::contains($gateway, $key)) {
                return $val;
            }
        }
        return false;
    }

    /**
     * @param Order $order
     * @return array|string
     * @throws \Exception
     */
    public function build(Order $order)
    {
        $callback = config('app.url')."/callback/order/{$order->shopify_order_id}";
        $params = [
            'merchantNo' => $this->config['merchantNo'],
            'storeNo' => $this->config['storeNo'],
            'currency' => $order->currency,
            'vendor' => $order->vendor,
            'ipnUrl' => config('app.url')."/callback/ipn",
            'callbackUrl' => $callback,
            'terminal' => $order->terminal,
            'reference' => $order->reference,
            'goodsInfo' => json_encode($order->goods_info)
        ];
        switch ($order->currency) {
            case "CNY":
                $params["rmbAmount"] = $order->total_amount;
                break;
            default:
                $params["amount"] = $order->total_amount;
        }
        $opts = ['note', 'description'];
        foreach ($opts as $op) {
            if($order->$op) {
                $params[$op] = $order->$op;
            }
        }

        ksort($params, SORT_STRING);
        $str = '';
        foreach ($params as $k => $v) {
            $str .= $k . '=' . $v . '&';
        }
        $params['verifySign'] = md5($str . md5($this->config['token']));

        $uri = $this->config['test'] ? self::API_URL_TEST : self::API_URL;
        $resp  = $this->client->request('POST',$uri, [
            'form_params' => $params
        ])->getBody()->getContents();
        $body = json_decode($resp, true);

        if($body["ret_code"] != self::STATUS_SUCCESS) {
            throw new \Exception("{$body['ret_code']} {$body['ret_msg']}");
        }
        return $body;
    }


}
