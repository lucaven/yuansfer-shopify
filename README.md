# Yuansfer Shopify Integration

## Install

### Requirements
This app is built with <a href="https://laravel.com/">Laravel</a>.
Please follow the System Requirements located <a href="https://laravel.com/docs/6.x#server-requirements">here</a>

After the system requirements are sent, now we can bootstrap the app.

- Run ``composer install`` inside project root to install all dependencies
- Copy ``.env.example`` to ``.env``and configure a database
- Run ``php artisan migrate`` to migrate tables into database
- Insert ``SHOPIFY_APP_NAME``, ``SHOPIFY_API_KEY``and ``SHOPIFY_API_SECRET`` which you obtain when creating an app on Shopify

### Configure the app on Shopify

From the partner dashboard, open the created app
- Enable the ``Online store`` extension under ``Extension``
- Now you should have ``Online store`` under Extension, click on ``Manage app proxy``
- The subpath prefix is ``apps`` and subpath is ``yuansfer``
- The Proxy URL is your domain name where the app root is + "/assets" eg: "https://yuansfer.com/assets"
- Click Save

### Test it

You should be able now to install the app on development store.

## For merchants

### After merchant installs the app
- Add script in "Additional scripts" section in Shopify Checkout settings

Direct URI is ``admin/settings/checkout``

The script
```html
<script src="/apps/yuansfer/js/checkout.js?id={{order.id}}"></script>
```

The script is loaded through a proxy and checks if the gateway on Shopify matches
the gateway for Yuansfer. If there is a match, customer will be redirected to payment gateway.

- Add payment gateway

Direct URI is ``admin/settings/payments``

Under "Manual payment methods", merchant needs to create a manual payment method.

How the values are handled is visible in ``app/Services/PaymentService.php`` under ``isYuansfer`` method.

For example: If the payment method contains word "wechat", we assume it's a WeChatPay gateway.
Merchant can combine any words like WeChat or weChat or WeChat Payments etc. 

Merchant can add/enable/remove/disable many custom payment methods.


## FAQ

Q: Getting a Shop Domain not found error.

A: This is because the app in debug mode.
Disable it in ``.env`` and now merchant will see the install page instead.

Q: Can I modify the proxy URL?

A: Yes, but any references for ``/apps/yuansfer`` will need to change also.

Q: Can we add a new provider ?

A: Yes, just add a key value provider in ``PaymentService``.

Q: Can I enable Yuansfer test mode?

A: Yes, when you are in the app dashboard, inspect the HTMl source and you will see a hidden field called test.
Change the value to "on" and save. Now your app is in test mode. Same goes deactivating the test mode.


## Help
Contact HarttMedia at `support@harttmedia.com`
