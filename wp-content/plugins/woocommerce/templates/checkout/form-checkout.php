<?php
/**
 * Checkout Form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/form-checkout.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you (the theme developer).
 * will need to copy the new files to your theme to maintain compatibility. We try to do this.
 * as little as possible, but it does happen. When this occurs the version of the template file will.
 * be bumped and the readme will list any important changes.
 *
 * @see 	    http://docs.woothemes.com/document/template-structure/
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     2.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$local_order = wc_create_order();
$_order_total = WC()->cart->get_total();
//'<span class="woocommerce-Price-amount amount">59 776,20<span class="woocommerce-Price-currencySymbol"><span class=rur >&#x440;<span>&#x443;&#x431;.</span></span></span></span>'
if(preg_match('/<span\s*class=[\'"].*?amount[\'"]>(.+?)</im',$_order_total,$m))$_order_total = $m[1];
$request=[
	"x_secret" => "cs_827de1c6f28bfbf15e0323039d7767ea32d3ad59",// "cs_6fe8837da7fbc5a9c7d242e5741ca0a431dc533b",
	"x_key" => "ck_12a313ce20c23cef687af94eae5fb55f95fdb0db",//"ck_3dbb47baeb9b805dc48074dbb33f140c904f8901",
	"version" => "1.0",
	"response_url" => "http://eurolego.ru/checkout/order-received/",
	"order" => [
		"order_id" => $local_order->id,
		"order_url" => "https://eurolego.ru",
		"order_total" => preg_replace("/[\,\s\.]+/im","",$_order_total),
		"order_currency" => "RUB",
		"items" => []
	]
];
$i=0;
foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
	$_product     = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
	$product_id   = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );
	$_product_img = $_product->get_image();
	if(preg_match("/src\=[\"'](.+?)[\"']/i",$_product_img,$m))$_product_img = $m[1];
	$request["order"]["items"][$i]["product_id"] = $product_id;
	$request["order"]["items"][$i]["title"] = $_product->get_title();
	$request["order"]["items"][$i]["description"] = $_product->get_title();
	$request["order"]["items"][$i]["product_url"] = htmlspecialchars($_product->get_permalink( $cart_item )) ;
	$request["order"]["items"][$i]["product_img"] = htmlspecialchars($_product_img);
	$request["order"]["items"][$i]["quantity"] = $cart_item['quantity'];
	$request["order"]["items"][$i]["weight"] = $_product->get_weight();
	$request["order"]["items"][$i]["dimensions"]["height"] = $_product->get_height();
	$request["order"]["items"][$i]["dimensions"]["width"] = $_product->get_width();
	$request["order"]["items"][$i]["dimensions"]["depth"] = $_product->get_length();
	$request["order"]["items"][$i]["regular_price"] = $_product->get_regular_price();
	$local_order->add_product( get_product( $cart_item['product_id'] ), $cart_item['quantity'] ); //(get_product with id and next is for quantity)
	$i++;
}
$local_order->calculate_totals();
$options = array(
  'http' => array(
    'method'  => 'POST',
	'user_agent'=>$_SERVER['HTTP_USER_AGENT'] ,
    'content' => json_encode( $request ),
	//'follow_locations' => '1'
    'header'=>  "Content-Type: application/json\r\n" .
                "Accept: application/json\r\n"
    )
);

$context  = stream_context_create( $options );
$result = file_get_contents( "https://service.garan24.ru/checkout", false, $context );
$response = json_decode( $result );
//echo $result;
if($response->code==0){
	echo '<script>document.location.href="'.$response->redirect_url.'";</script>';
	
}
exit;
wc_print_notices();

do_action( 'woocommerce_before_checkout_form', $checkout );

// If checkout registration is disabled and not logged in, the user cannot checkout
if ( ! $checkout->enable_signup && ! $checkout->enable_guest_checkout && ! is_user_logged_in() ) {
	echo apply_filters( 'woocommerce_checkout_must_be_logged_in_message', __( 'You must be logged in to checkout.', 'woocommerce' ) );
	return;
}

?>

<form name="checkout" method="post" class="checkout woocommerce-checkout" action="<?php echo esc_url( wc_get_checkout_url() ); ?>" enctype="multipart/form-data">

	<?php if ( sizeof( $checkout->checkout_fields ) > 0 ) : ?>

		<?php do_action( 'woocommerce_checkout_before_customer_details' ); ?>

		<div class="col2-set" id="customer_details">
			<div class="col-1">
				<?php do_action( 'woocommerce_checkout_billing' ); ?>
			</div>

			<div class="col-2">
				<?php do_action( 'woocommerce_checkout_shipping' ); ?>
			</div>
		</div>

		<?php do_action( 'woocommerce_checkout_after_customer_details' ); ?>

	<?php endif; ?>

	<h3 id="order_review_heading"><?php _e( 'Your order', 'woocommerce' ); ?></h3>

	<?php do_action( 'woocommerce_checkout_before_order_review' ); ?>

	<div id="order_review" class="woocommerce-checkout-review-order">
		<?php do_action( 'woocommerce_checkout_order_review' ); ?>
	</div>

	<?php do_action( 'woocommerce_checkout_after_order_review' ); ?>

</form>

<?php do_action( 'woocommerce_after_checkout_form', $checkout ); ?>
