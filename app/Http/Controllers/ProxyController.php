<?php


namespace App\Http\Controllers;


use App\Order;
use App\Services\OrderService;
use App\Services\PaymentService;
use App\ShopConfig;
use App\ShopifyClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ProxyController extends Controller
{

    public function script(Request $request)
    {
        $isMobile = preg_match('/iPhone|Android|Blackberry/i', $request->userAgent());
        $id = $request->get('id');
        Log::info("isMobile: $isMobile, script id: $id");
        if(!$id) {
            Log::error("No id passed");
            return "";
        }

        $shopifyOrder = Cache::remember("order_$id", 120, function() use($id) {
            $api = Auth::user()->api();
            try {
                $response = $api->rest('GET', "/admin/orders/{$id}.json");
                return is_object($response["body"]) ? $response["body"]->order : null;
            }catch (\Exception $e) {
                Log::error($e->getMessage());
                Log::error($e->getTraceAsString());
            }
            return "";
        });
        if(!$shopifyOrder) {
            return "";
        }

        $gateway = PaymentService::isYuansfer($shopifyOrder->gateway);
        if(!$gateway) {
            Log::info("not a yuansfer order $id");
            return "";
        }

        $service = OrderService::fromShopify(
            Auth::user()->id,
            $gateway,
            $shopifyOrder,
            $isMobile
        );

        if($service->isFirst()) {
            try {
                $order = $service->getOrder();
                ShopifyClient::fromShop($order->shop_id)
                    ->tagOrderWithVendor($order);
            }catch (\Exception $e) {
                Log::error($e->getTraceAsString());
            }
        }

        $cfg = $service->getOrder()->shopConfig();
        if(!$cfg->isValid()) {
            $service->updateWithError("Yuansfer is not configured");
        }

        $view = view('yuansfer', [
            "order" => $service->getOrder(),
        ])->render();
        return response($view)->withHeaders(['Content-Type' => 'text/javascript']);
    }

    public function doPayment($id, Request $request)
    {
        $service = OrderService::fromId($id);
        if(!$service->hasOrder()) {
            return back();
        }

        /** @var ShopConfig $cfg */
        $cfg = $service->getOrder()->shopConfig();
        if(!$cfg->isValid()) {
            $service->updateWithError("Yuansfer is not configured");
            return redirect($service->getRedirect());
        }

        $cashierUrl = $service->getCashierUrl();
        if($cashierUrl) {
            return redirect($cashierUrl);
        }

        try {
            $response = PaymentService::create([
                'merchantNo' => $cfg->config['merchantNo'],
                'storeNo' => $cfg->config['storeNo'],
                'token' => $cfg->getToken(),
                'test'  => $cfg->isTestMode()
            ])->build($service->getOrder());
            Log::info("yuansfer response:");
            Log::info(json_encode($response));
            $service->getOrder()->update([
                'payment_status' => Order::STATUS_WAITING_PAYMENT,
                'yuansfer_response' => $response
            ]);
            return redirect($response['result']['cashierUrl']);
        } catch (\Exception $e) {
            $service->updateWithError($e->getMessage());
        }

        return redirect($service->getRedirect());
    }


}
