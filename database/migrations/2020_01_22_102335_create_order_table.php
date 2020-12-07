<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('shop_id');
            $table->bigInteger('shopify_order_id');

            $table->string('total_amount');
            $table->string('currency');
            $table->string('vendor');
            $table->string('terminal')->default("ONLINE");
            $table->string('reference');
            $table->string('description')->nullable();
            $table->string('note')->nullable();
            $table->string('credit_type')->nullable();
            $table->json('goods_info');

            $table->json('yuansfer_response')->nullable();
            $table->json('ipn_response')->nullable();

            $table->string('payment_status')->default(\App\Order::STATUS_CREATED);
            $table->string('shopify_payment_status')->default(\App\Order::STATUS_CREATED);
            $table->text('payment_error')->nullable();

            $table->string('redirect_uri');



            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orders');
    }
}
