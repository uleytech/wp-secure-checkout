<?php
/**
 * Plugin Name: Secure Checkout Redirect
 * Version: 1.1.0
 * Description: Provides functionality for WordPress WooCommerce.
 * Requires at least: 5.2
 * Requires PHP: 7.2
 * Plugin URI: https://github.com/uleytech/wp-secure-checkout-redirect
 * Author: Oleksandr Krokhin
 * Author URI: https://www.krohin.com
 * License: MIT
 */

require_once __DIR__ . '/include.php';
require_once __DIR__ . '/update.php';


if (is_admin()) {
    new ScrUpdater(
        __FILE__,
        'uleytech',
        "wp-secure-checkout-redirect"
    );
}

/**
 * @return array
 */
function getCartProducts(): array
{
    $items = [];
    foreach (WC()->cart->get_cart() as $key => $item) {
        $product = apply_filters('woocommerce_cart_item_product', $item['data'], $item, $key);
        $uuid = $product->get_sku();
        $qty = $item['quantity'];
        $items[] = [
            'qty' => $qty,
            'uuid' => $uuid
        ];
    }
    return [
        'cart' => json_encode(['items' => $items])
    ];
}

function scrRedirectForm()
{
    $affId = $_COOKIE['aid'] ?? '';
    $products = getCartProducts();
    $url = dirname(set_url_scheme('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF']));
    echo '
    <form id="myForm" action="' . SCR_REDIRECT_URL . '" method="post">
        <input type="hidden" name="cart" value=\'' . $products['cart'] . '\'>
        <input type="hidden" name="ip_address" value="' . $_SERVER["REMOTE_ADDR"] . '">
        <input type="hidden" name="url" value="' . $url . '">
        <input type="hidden" name="aff_id" value="' . $affId . '">
        <input type="hidden" name="lang" value="en">
        <input type="hidden" name="currency" value="EUR">
        <input type="hidden" name="currencyPrice" value="1">
        <input type="hidden" name="theme" value="wordpress">
    </form>
    <script type="text/javascript">
        document.getElementById("myForm").submit();
    </script>
    ';
    WC()->cart->empty_cart($clear_persistent_cart = true);
}

add_action('woocommerce_before_checkout_form', 'scrRedirectForm', 10);

function scrAddScript()
{
    echo '<style>main.checkout{opacity: 0;}</style>';
}

add_action('wp_head', 'scrAddScript');
