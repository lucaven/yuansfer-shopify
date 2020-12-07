<?php

namespace App\Http\Controllers;

use App\Order;
use App\ShopConfig;
use App\ShopifyClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Yuansfer\Util\Sign;

class CallbackController extends Controller
{

    public function index($id, Request $request)
    {
        $tries = $request->session()->get('tries', 1);
        //@Note - shopify order name reference #123 causes problem
        // on client side since browser will not process anything after # on URL
        // change it to Shopify ID
        Log::info("callback recived $id");
        $order = Order::where('shopify_order_id', $id)->first();
        if(!$order) {
            Log::error("order not found for reference $id");
            abort(404, 'Order not found');
        }

        if(in_array($order->payment_status, [
            Order::STATUS_COMPLETED,
            Order::STATUS_ERROR,
            Order::STATUS_UNKNOWN
        ])) {
            return redirect($order->redirect_uri);
        }

        if($tries > 3) {
            $request->session()->flash('tries');
            return redirect($order->redirect_uri);
        }
        $tries++;
        $request->session()->put('tries', $tries);

        return view('callback');
    }

    public function ipn(Request $request)
    {
        $reference = $request->get('reference');
        Log::info("ipn received for reference $reference");
        /** @var Order $order */
        $order = Order::where('reference', $reference)->first();
        if(!$order) {
            Log::error("order ipn not found for reference $reference");
            abort(404, 'Order not found');
        }

        /** @var ShopConfig $config */
        $config = $order->shopConfig();
        if(!$config->isValid()) {
            // do nothing, return success
            return response('success');
        }

        if(!Sign::verify($request->all(), $config->getToken())) {
            Log::error("signature verification failed for reference $reference");
            abort(403, 'Sign verification failed');
        }

        $status = $request->get('status');
        $fill = [
            'payment_status' => Order::STATUS_UNKNOWN,
            'payment_error' => null,
        ];
        switch ($status) {
            case "dealing":
                $fill['payment_status'] = Order::STATUS_PROCESSING;
                break;
            case "success":
                try {
                    $fill['payment_status'] = Order::STATUS_COMPLETED;
                    $client = ShopifyClient::fromShop($order->shop_id);
                    if($order->shopify_payment_status == Order::STATUS_COMPLETED) {
                        break;
                    }
                    $client->markOrderPaid($order);
                    $fill['shopify_payment_status'] = Order::STATUS_COMPLETED;
                } catch (\Exception $e) {
                    $order->update([
                        'payment_error' => $e->getMessage()
                    ]);
                    abort(500);
                }
                break;
            case "failed":
                $fill['payment_status'] = Order::STATUS_ERROR;
                $fill['payment_error'] = "Failed to capture payment";
                break;
            case "pending":
                $fill['payment_status'] = Order::STATUS_WAITING_PAYMENT;
                break;

        }

        $fill['ipn_response'] = $request->all();
        $order->update($fill);

        return response('success');
    }
}
