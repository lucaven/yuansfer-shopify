var template = `
<div class="content-box">
    <div class="content-box__row text-container">
        <h2 class="heading-2">Payment</h2>
        <p class="os-step__description">{!! $order->getPaymentDescription() !!}</p>
    </div>
</div>
    `;
    document.addEventListener("DOMContentLoaded", function(event) {
    var el = document.getElementsByClassName("section__content")[0];
    el.innerHTML = template + el.innerHTML;

    @if($order->payment_status == \App\Order::STATUS_CREATED)
        window.location.href = "/apps/yuansfer/checkout/{{$order->id}}";
    @endif

    });
