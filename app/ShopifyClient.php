<?php


namespace App;


use Illuminate\Support\Facades\Log;
use Osiset\BasicShopifyAPI\BasicShopifyAPI;


class ShopifyClient
{

    /**
     * @var BasicShopifyAPI
     */
    protected $client;


    public static function fromShop($id)
    {
        $ret = new ShopifyClient();
        $shop = User::where('id', $id)->first();
        $ret->client = $shop->api();
        return $ret;
    }

    /**
     * @param Order $order
     * @return mixed
     * @throws \Exception
     */
    public function markOrderPaid(Order $order)
    {
        $id = $order->shopify_order_id;

        $lastTransaction = $this->getLatestTransaction($id);
        Log::info("Last transaction:");
        Log::info(json_encode($lastTransaction));
        if($lastTransaction->status != "success") {
            $response = $this->client->rest('POST', "/admin/orders/{$id}/transactions.json", [
                'transaction' => [
                    "currency"=> $order->currency,
                    "amount"=> $order->total_amount,
                    "kind"=> "capture",
                    'authorization' => $order->ipn_response['yuansferId'],
                    'parent_id' => $lastTransaction->id,
                ]
            ]);
            $statusCode = $response["response"]->getStatusCode();
            if($statusCode != 201) {
                $msg = json_encode($response["body"]);
                $msg.= "Shopify error on marking as paid: $statusCode, data:".$msg;
                throw new \Exception($msg);
            }
        }
        return true;
    }

    /**
     * @param Order $order
     * @throws \Exception
     */
    public function tagOrderWithVendor(Order $order)
    {
        $orderID = $order->shopify_order_id;
        $vendor = $order->vendor;
        Log::info("tagging order $orderID with $vendor");
        $response = $this->client->rest("PUT", "/admin/orders/{$orderID}.json", [
            "order" => [
                "id" => $orderID,
                "tags" => $vendor
            ]
        ]);
        $statusCode = $response["response"]->getStatusCode();
        if($statusCode != 201) {
            $msg = json_encode($response["body"]);
            $msg.= "Shopify error on updating tag: $statusCode, data:".$msg;
            throw new \Exception($msg);
        }
    }

    /**
     * @param $id
     * @return mixed
     */
    private function getLatestTransaction($id)
    {
        $response = $this->client->rest('GET', "/admin/orders/{$id}/transactions.json");
        $transactions = $response["body"]->transactions;
        return $transactions[sizeof($transactions) - 1];
    }
}
