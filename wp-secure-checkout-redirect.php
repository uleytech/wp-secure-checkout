<?php
/**
 * Plugin Name: Secure Checkout Redirect
 * Version: 1.1.1
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
function scrGetCartProducts(): array
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
    $products = scrGetCartProducts();
    $url = dirname(set_url_scheme('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF']));
    echo '
    <form id="scrForm" action="' . SCR_REDIRECT_URL . '" method="' . SCR_REDIRECT_METHOD . '">
        <input type="hidden" name="cart" value=\'' . $products['cart'] . '\'>
        <input type="hidden" name="ip_address" value="' . $_SERVER["REMOTE_ADDR"] . '">
        <input type="hidden" name="url" value="' . $url . '">
        <input type="hidden" name="aff_id" value="' . $affId . '">
        <input type="hidden" name="lang" value="' . SCR_LANG . '">
        <input type="hidden" name="currency" value="' . SCR_CURRENCY . '">
        <input type="hidden" name="currencyPrice" value="' . SCR_CURRENCY_PRICE . '">
        <input type="hidden" name="theme" value="' . SCR_THEME . '">
    </form>
    <script type="text/javascript">
        document.getElementById("scrForm").submit();
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
