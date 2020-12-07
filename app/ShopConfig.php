<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ShopConfig extends Model
{
    protected $fillable = [
        'shop_id',
        'config'
    ];

    protected $casts = [
        'config' => 'array'
    ];


    /**
     * @param $id
     * @return ShopConfig|null
     */
    public static function fromShop($id)
    {
        $shop = User::where('id', $id)->first();
        if(!$shop) {
            return null;
        }
        $cfg = ShopConfig::firstOrCreate(
            ["shop_id" => $id],
            ["config" => ["token" => "", "merchantNo" => "", "storeNo" => "", "test" => false]]
        );
        return $cfg;
    }

    public function isValid()
    {
        $params = ['token', 'merchantNo', 'storeNo'];
        foreach ($params as $param) {
            if($this->config[$param] == "") {
                return false;
            }
        }
        return true;
    }

    public function isTestMode()
    {
        return $this->config['test'] == true;
    }

    public function getToken()
    {
        return $this->config['token'];
    }
}
