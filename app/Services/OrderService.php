<?php


namespace App\Services;


use App\Order;
use Illuminate\Support\Str;

class OrderService
{

    /** @var Order */
    protected $order;

    /** @var bool */
    protected $first = false;


    /**
     * @param $shopId
     * @param $gateway
     * @param $shopifyOrder
     * @return OrderService
     */
    public static function fromShopify($shopId, $gateway, $shopifyOrder, $isMobile)
    {
        $ret = new OrderService();

        /** @var Order $order */
        $order = Order::where('shopify_order_id', $shopifyOrder->id)->first();
        if(!$order) {
            $goodsInfo = [];
            foreach ($shopifyOrder->line_items as $item) {
                $goodsInfo[] = [
                    'goods_name' => $item->title,
                    'quantity' => $item->quantity
                ];
            }
            $order = new Order();
            $order->fill([
                'shop_id' => $shopId,
                'shopify_order_id' => $shopifyOrder->id,
                'total_amount' => $shopifyOrder->total_price,
                'currency' => $shopifyOrder->currency,
                'vendor' => $gateway,
                'reference' => $shopifyOrder->name,
                'note' => $shopifyOrder->note,
                'goods_info' => $goodsInfo,
                'redirect_uri' => $shopifyOrder->order_status_url,
                'terminal' => $isMobile ? "WAP" : "ONLINE",
            ]);
            $order->save();
            $order = Order::where('shopify_order_id', $shopifyOrder->id)->first();
            $ret->first = true;
        }
        $ret->order = $order;
        return $ret;
    }

    /**
     * @param $id
     * @return OrderService
     */
    public static function fromId($id)
    {
        $ret = new OrderService();
        $ret->order = Order::where('id', $id)->first();
        return $ret;
    }

    /**
     * @return bool
     */
    public function hasOrder()
    {
        return $this->order != null;
    }

    /**
     * @param $msg
     * @return $this
     */
    public function updateWithError($msg)
    {
        $this->order->update([
            'payment_status' => Order::STATUS_ERROR,
            'payment_error' => $msg
        ]);
        return $this;
    }

    /**
     * @return string
     */
    public function getRedirect()
    {
        return $this->order->redirect_uri;
    }

    /**
     * @return string
     */
    public function getCashierUrl()
    {
        if($this->order->yuansfer_response) {
            if(!array_key_exists("result", $this->order->yuansfer_response)) {
                $this->order->update([
                    'reference' => Str::random(8)
                ]);
            }else {
                return $this->order->yuansfer_response["result"]["cashierUrl"];
            }
        }
        return false;
    }

    /**
     * @return Order
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @return bool
     */
    public function isFirst(): bool
    {
        return $this->first;
    }

    /**
     * @param bool $first
     * @return OrderService
     */
    public function setFirst(bool $first): OrderService
    {
        $this->first = $first;
        return $this;
    }



}
