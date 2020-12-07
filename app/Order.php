<?php


namespace App;


use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    const STATUS_CREATED = 'created';
    const STATUS_WAITING_PAYMENT = 'waiting_payment';
    const STATUS_PROCESSING = 'processing';
    const STATUS_COMPLETED = 'completed';
    const STATUS_ERROR = 'error';
    const STATUS_UNKNOWN = 'unknown';



    protected $fillable = [
        'shop_id',
        'shopify_order_id',
        'total_amount',
        'currency',
        'vendor',
        'terminal',
        'reference',
        'description',
        'note',
        'credit_type',
        'goods_info',
        'yuansfer_response',
        'ipn_response',
        'payment_status',
        'shopify_payment_status',
        'payment_error',
        'redirect_uri'
    ];

    protected $casts = [
        'yuansfer_response' => 'array',
        'ipn_response' =>  'array',
        'goods_info' => 'array'
    ];

    public function getPaymentDescription()
    {
        $withTryAgain = '<a href="/apps/yuansfer/checkout/'.$this->id.'">clicking here</a>';
        switch ($this->payment_status) {
            case self::STATUS_CREATED:
                return "Please wait till we redirect you to payment gateway";
                break;
            case self::STATUS_WAITING_PAYMENT:
                return "We are awaiting payment. If you still did not submit the payment, you can do it by $withTryAgain";
                break;
            case self::STATUS_COMPLETED:
                return "Payment successfully paid";
            case self::STATUS_ERROR:
                return "There was an issue processing your payment. Details: {$this->payment_error}. You can try again by $withTryAgain";
            case self::STATUS_PROCESSING:
                return "Payment is processing";
        }
        return "";
    }


    public function shopConfig()
    {
        return ShopConfig::fromShop($this->shop_id);
    }



}
