<?php


namespace App\Http\Controllers;


use App\ShopConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{

    public function index()
    {
        $config = ShopConfig::fromShop(Auth::user()->id);

        return view('dashboard', [
            'config' => $config->config,
        ]);
    }

    public function store(Request $request)
    {
        $config = ShopConfig::fromShop(Auth::user()->id);

        $config->update([
            'config' => [
                'merchantNo' => $request->get('merchantNo'),
                'storeNo' => $request->get('storeNo'),
                'token' => $request->get('token'),
                'test' => $request->get('test', null) == "on"
            ]
        ]);

        return back()->with('success', 'Settings updated');
    }

}
