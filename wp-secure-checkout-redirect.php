<?php
/**
 * Plugin Name: Secure Checkout Redirect
 * Version: 1.0.6
 * Description: Provides functionality for WordPress WooCommerce.
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Plugin URI: https://github.com/uleytech/wp-secure-checkout-redirect
 * Author: Oleksandr Krokhin
 * Author URI: https://www.krohin.com
 * License: MIT
 */

function getCartProduct()
{
    foreach (WC()->cart->get_cart() as $key => $item) {
        $product = apply_filters('woocommerce_cart_item_product', $item['data'], $item, $key);
//        $productId = $item['product_id'];
        $uuid = $product->get_sku();
        $qty = $item['quantity'];
        $items[] = [
//            'groupId' => $item['product_id'], // ?
//            'productId' => $productId, // ?
            'qty' => $qty,
            'uuid' => $uuid
        ];
    }
    return [
        'cart' => json_encode(['items' => $items]),
    ];
}

function action_woocommerce_before_checkout_form($cart_item_data)
{
    $affId = $_COOKIE['aid'];
    $products = getCartProduct();
    $url = dirname(set_url_scheme('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF']));
    echo '
    <form id="myForm" action="https://secure-safepay.com/" method="post">
        <input type="hidden" name="cart" value=\'' . $products['cart'] . '\'>
        <input type="hidden" name="ip_address" value="' . $_SERVER["REMOTE_ADDR"] . '">
        <input type="hidden" name="url" value="' . $url . '">
        <input type="hidden" name="aff_id" value="' . $affId . '">
        <input type="hidden" name="lang" value="en">
        <input type="hidden" name="currency" value="EUR">
        <input type="hidden" name="currencyPrice" value="1">
        <input type="hidden" name="theme" value="">
    </form>
    <script type="text/javascript">
        // document.body.style.backgroundColor = "#FFFFFF";
        document.getElementById("myForm").submit();
    </script>
    ';

    WC()->cart->empty_cart($clear_persistent_cart = true);
}

function scr_add_script() {
    echo '<style>main.checkout{opacity: 0;}</style>';
}
add_action('wp_head', 'scr_add_script');

// add the action
add_action('woocommerce_before_checkout_form', 'action_woocommerce_before_checkout_form', 10, 1);
