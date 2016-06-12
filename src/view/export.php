<?php
if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>
<h2>
	<?php echo _e( 'Export Now', 'woocommerce-order-export' ) ?>
</h2>
<div class="tabs-content">
	<?php
//settings for form
	$show = array( 'date_filter' => true, 'export_button' => true, 'destinations' => false, 'schedule' => false, );
	$WC_Order_Export->render( 'settings-form', array( 'mode' => 'now', 'id' => 0, 'WC_Order_Export' => $WC_Order_Export, 'ajaxurl' => $ajaxurl, 'show' => $show ) );
	?> 
</div>