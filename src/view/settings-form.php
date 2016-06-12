<?php
if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
add_thickbox();
$settings = $WC_Order_Export->get_export_settings( $mode, $id );

//var_dump( $WC_Order_Export->get_value( $settings, '[schedule][type]' ) );
?>

<script>
	var mode = '<?php echo $mode ?>';
	var job_id = '<?php echo $id ?>';
	var output_format = '<?php echo $settings[ 'format' ] ?>';
	var order_fields = <?php echo json_encode( $settings[ 'order_fields' ] ) ?>;
	var order_products_fields = <?php echo json_encode( $settings[ 'order_product_fields' ] ) ?>;
	var order_coupons_fields = <?php echo json_encode( $settings[ 'order_coupon_fields' ] ) ?>;
</script>


<?php include 'modal-controls.php'; ?>
<form method="post" id="export_job_settings">
<div id="my-left" style="float: left; width: 49%; max-width: 500px;">
	<?php if ( $mode == 'cron' ): ?>
		<div id="my-shedule-days" class="my-block">
			<div class="wc-oe-header"><?php echo _e( 'Schedule', 'woocommerce-order-export' ) ?></div>
			<div id="d-schedule-1">
				<input type="radio" name="settings[schedule][type]" value="schedule-1" id="schedule-1" class="wc-oe-schedule-type" <?php echo ((isset( $settings[ 'schedule' ] ) and $settings[ 'schedule' ][ 'type' ] == 'schedule-1') or ! isset( $settings[ 'schedule' ] )) ? 'checked' : '' ?>>
				<div class="weekday">

					<label>
						<?php _e('Sun', 'woocommerce-order-export')?>
						<input type="checkbox" name="settings[schedule][weekday][Sun]" <?php echo isset( $settings[ 'schedule' ][ 'weekday' ][ 'Sun' ] ) ? 'checked' : '' ?>>
					</label>
					<label>
						<?php _e('Mon', 'woocommerce-order-export')?>
						<input  type="checkbox" name="settings[schedule][weekday][Mon]" <?php echo isset( $settings[ 'schedule' ][ 'weekday' ][ 'Mon' ] ) ? 'checked' : '' ?>>
					</label>
					<label>
						<?php _e('Tue', 'woocommerce-order-export')?>
						<input  type="checkbox" name="settings[schedule][weekday][Tue]" <?php echo isset( $settings[ 'schedule' ][ 'weekday' ][ 'Tue' ] ) ? 'checked' : '' ?>>
					</label>
					<label>
						<?php _e('Wed', 'woocommerce-order-export')?>
						<input  type="checkbox" name="settings[schedule][weekday][Wed]" <?php echo isset( $settings[ 'schedule' ][ 'weekday' ][ 'Wed' ] ) ? 'checked' : '' ?>>
					</label>
					<label>
						<?php _e('Thu', 'woocommerce-order-export')?>
						<input  type="checkbox" name="settings[schedule][weekday][Thu]" <?php echo isset( $settings[ 'schedule' ][ 'weekday' ][ 'Thu' ] ) ? 'checked' : '' ?>>
					</label>
					<label>
						<?php _e('Fri', 'woocommerce-order-export')?>
						<input  type="checkbox" name="settings[schedule][weekday][Fri]" <?php echo isset( $settings[ 'schedule' ][ 'weekday' ][ 'Fri' ] ) ? 'checked' : '' ?>>
					</label>
					<label>
						<?php _e('Sat', 'woocommerce-order-export')?>
						<input  type="checkbox" name="settings[schedule][weekday][Sat]" <?php echo isset( $settings[ 'schedule' ][ 'weekday' ][ 'Sat' ] ) ? 'checked' : '' ?>>
					</label>
				</div>
				<div class="">
					<label style="margin-left: 10px;"><?php _e('Run at', 'woocommerce-order-export')?>:
						<select name="settings[schedule][run_at]" style="width: 80px">
							<?php
							for ( $i = 0; $i <= 23; $i++ ) :
								$time	 = ( $i < 10 ? '0' : '') . "$i:00";
								$time30	 = ( $i < 10 ? '0' : '') . "$i:30";
								?>

								<option <?php echo (isset( $settings[ 'schedule' ][ 'run_at' ] ) and $time == $settings[ 'schedule' ][ 'run_at' ]) ? 'selected' : '' ?>>
									<?php
									echo $time;
									?>
								</option>
								<option <?php echo (isset( $settings[ 'schedule' ][ 'run_at' ] ) and $time30 == $settings[ 'schedule' ][ 'run_at' ]) ? 'selected' : '' ?>>
									<?php
									echo $time30;
									?>
								</option>
							<?php endfor; ?>
						</select>
					</label>
				</div>
			</div>
			<div class="clearfix"></div>

			<div id="d-schedule-2" class="padding-bottom-10">
				<input type="radio" name="settings[schedule][type]" value="schedule-2" id="schedule-2" class="wc-oe-schedule-type" <?php echo (isset( $settings[ 'schedule' ] ) and $settings[ 'schedule' ][ 'type' ] == 'schedule-2') ? 'checked' : '' ?>>
				<select class="wc_oe-select-interval" name="settings[schedule][interval]">
					<option value="-1"><?php echo _e( 'Choose', 'woocommerce-order-export' ) ?></option>
					<option value="custom"  <?php echo (isset( $settings[ 'schedule' ][ 'interval' ] ) AND $settings[ 'schedule' ][ 'interval' ] == 'custom') ? 'selected' : '' ?>><?php echo _e( 'Custom', 'woocommerce-order-export' ) ?></option>
					<?php
					$schedules = wp_get_schedules();
					foreach ( $schedules as $name => $schedule ) :
						?>
						<option value="<?php echo $name ?>" <?php echo (isset( $settings[ 'schedule' ][ 'interval' ] ) AND $settings[ 'schedule' ][ 'interval' ] == $name ) ? 'selected' : '' ?>>
							<?php echo $schedule[ 'display' ] ?>
						</option>
					<?php endforeach; ?>
				</select>
				<label id="custom_interval">
					<?php echo _e( 'interval (min)', 'woocommerce-order-export' ) ?>:
					<input name="settings[schedule][custom_interval]" value="<?php echo isset( $settings[ 'schedule' ][ 'custom_interval' ] ) ? $settings[ 'schedule' ][ 'custom_interval' ] : '' ?>" >
				</label>
			</div>
		</div>
		<br>
		<div id="my-export-file" class="my-block">
			<div class="wc-oe-header">
				<?php echo _e( 'Export filename', 'woocommerce-order-export' ) ?>:
			</div>
			<label id="export_filename">
				<input type="text" name="settings[export_filename]" class="width-100" value="<?php echo isset( $settings[ 'export_filename' ] ) ? $settings[ 'export_filename' ] : 'orders-%y-%m-%d.csv' ?>" >
			</label>
		</div>
		<br>
	<?php endif; ?>

	<?php if ( $show[ 'date_filter' ] ) : ?>
	<div id="my-main" class="my-block">
			<div style="display: inline;">
				<span class="wc-oe-header"><?php echo _e( 'Date range', 'woocommerce-order-export' ) ?></span>
				<input type=text class='date' name="settings[from_date]" id="from_date" value='<?php echo $settings[ 'from_date' ] ?>'>
				<?php echo _e( 'to', 'woocommerce-order-export' ) ?>
				<input type=text class='date' name="settings[to_date]" id="to_date" value='<?php echo $settings[ 'to_date' ] ?>'>
			</div>

		<button id="my-quick-export-btn" class="button-primary"><?php _e('Express Export', 'woocommerce-order-export' ) ?></button>
	</div>
	<br>
	<?php endif; ?>

	<div id="my-format" class="my-block">
		<span class="wc-oe-header"><?php echo _e( 'Format', 'woocommerce-order-export' ) ?></span><br>
		<p>
			<?php foreach ( WC_Order_Export_Admin::$formats as $format ) { ?>
			<label class="button-secondary">
				<input type=radio name="settings[format]" class="output_format" value="<?php echo $format ?>"
					<?php if ( $format == $settings[ 'format' ] ) echo 'checked'; ?> ><?php echo $format ?>
				<span class="ui-icon ui-icon-triangle-1-s my-icon-triangle"></span>
			</label>
			<?php } ?>
		</p>

		<div id='XLS_options' style='display:none'><strong><?php _e( 'XLS options', 'woocommerce-order-export' ) ?></strong><br>
			<input type=hidden name="settings[format_xls_display_column_names]" value=0>
			<input type=hidden name="settings[format_xls_populate_other_columns_product_rows]" value=0>
			<input type=checkbox name="settings[format_xls_display_column_names]" value=1 <?php if ( @$settings[ 'format_xls_display_column_names' ] ) echo 'checked'; ?>  >  <?php _e( 'Output column titles as first line', 'woocommerce-order-export' ) ?><br>
			<input type=checkbox name="settings[format_xls_populate_other_columns_product_rows]" value=1 <?php if ( @$settings[ 'format_xls_populate_other_columns_product_rows' ] ) echo 'checked'; ?>  >  <?php _e( 'Populate other columns if products exported as rows', 'woocommerce-order-export' ) ?><br>
		</div>
		<div id='CSV_options' style='display:none'><strong><?php _e( 'CSV options', 'woocommerce-order-export' ) ?></strong><br>
			<input type=hidden name="settings[format_csv_add_utf8_bom]" value=0>
			<input type=hidden name="settings[format_csv_display_column_names]" value=0>
			<input type=hidden name="settings[format_csv_populate_other_columns_product_rows]" value=0>
			<input type=checkbox name="settings[format_csv_add_utf8_bom]" value=1 <?php if ( @$settings[ 'format_csv_add_utf8_bom' ] ) echo 'checked'; ?>  > <?php _e( 'Output utf-8 BOM', 'woocommerce-order-export' ) ?><br>
			<input type=checkbox name="settings[format_csv_display_column_names]" value=1 <?php if ( @$settings[ 'format_csv_display_column_names' ] ) echo 'checked'; ?>  >  <?php _e( 'Output column titles as first line', 'woocommerce-order-export' ) ?><br>
			<input type=checkbox name="settings[format_csv_populate_other_columns_product_rows]" value=1 <?php if ( @$settings[ 'format_csv_populate_other_columns_product_rows' ] ) echo 'checked'; ?>  >  <?php _e( 'Populate other columns if products exported as rows', 'woocommerce-order-export' ) ?><br>
			<?php _e( 'Field Delimiter', 'woocommerce-order-export' ) ?> <input type=text name="settings[format_csv_delimiter]" value='<?php echo $settings[ 'format_csv_delimiter' ] ?>' size=1>
			<?php _e( 'Line Break', 'woocommerce-order-export' ) ?><input type=text name="settings[format_csv_linebreak]" value='<?php echo $settings[ 'format_csv_linebreak' ] ?>' size=4><br>
		</div>
		<div id='XML_options' style='display:none'><strong><?php _e( 'XML options', 'woocommerce-order-export' ) ?></strong><br>
			<span class="xml-title"><?php _e( 'Root tag', 'woocommerce-order-export' ) ?></span><input type=text name="settings[format_xml_root_tag]" value='<?php echo $settings[ 'format_xml_root_tag' ] ?>'><br>
			<span class="xml-title"><?php _e( 'Order tag', 'woocommerce-order-export' ) ?></span><input type=text name="settings[format_xml_order_tag]" value='<?php echo $settings[ 'format_xml_order_tag' ] ?>'><br>
			<span class="xml-title"><?php _e( 'Product tag', 'woocommerce-order-export' ) ?></span><input type=text name="settings[format_xml_product_tag]" value='<?php echo $settings[ 'format_xml_product_tag' ] ?>'><br>
			<span class="xml-title"><?php _e( 'Coupon tag', 'woocommerce-order-export' ) ?></span><input type=text name="settings[format_xml_coupon_tag]" value='<?php echo $settings[ 'format_xml_coupon_tag' ] ?>'><br>
		</div>
		<div id='Sage_options' style='display:none'><strong><?php _e( 'Sage options', 'woocommerce-order-export' ) ?></strong><br>
			<span class="xml-title"><?php _e( 'Root tag', 'woocommerce-order-export' ) ?></span><input type=text name="settings[format_sage_root_tag]" value='Payload'><br>
			<span class="xml-title"><?php _e( 'Order tag', 'woocommerce-order-export' ) ?></span><input type=text name="settings[format_sage_order_tag]" value='Entry'><br>
			<span class="xml-title"><?php _e( 'Product tag', 'woocommerce-order-export' ) ?></span><input type=text name="settings[format_sage_product_tag]" value='<?php echo $settings[ 'format_sage_product_tag' ] ?>'><br>
			<span class="xml-title"><?php _e( 'Coupon tag', 'woocommerce-order-export' ) ?></span><input type=text name="settings[format_sage_coupon_tag]" value='<?php echo $settings[ 'format_sage_coupon_tag' ] ?>'><br>
		</div>

		<div id='JSON_options' style='display:none'></div>
	</div>
</div>


<div id="my-right" style="float: left; width: 48%; margin: 0px 10px; max-width: 500px;">
	<?php if ( $mode == 'cron' ): ?>
		<div id="my-shedule-destination" class="my-block">
			<div class="wc-oe-header"><?php echo _e( 'Destination', 'woocommerce-order-export' ) ?></div>

			<label class="button-secondary"><input type=radio name="settings[destination][type]" class="output_destination" value="email"
				<?php if ( isset( $settings[ 'destination' ][ 'type' ] ) AND $settings[ 'destination' ][ 'type' ] == 'email' ) echo 'checked'; ?>
				> <?php echo _e( 'EMAIL', 'woocommerce-order-export' ) ?>
				<span class="ui-icon ui-icon-triangle-1-s my-icon-triangle"></span>
			</label>

			<label class="button-secondary"><input type=radio name="settings[destination][type]" class="output_destination" value="ftp"
				<?php if ( isset( $settings[ 'destination' ][ 'type' ] ) AND $settings[ 'destination' ][ 'type' ] == 'ftp' ) echo 'checked'; ?>
				> <?php echo _e( 'FTP', 'woocommerce-order-export' ) ?>
				<span class="ui-icon ui-icon-triangle-1-s my-icon-triangle"></span>
			</label>

			<label class="button-secondary"><input type=radio name="settings[destination][type]" class="output_destination" value="http"
				<?php if ( isset( $settings[ 'destination' ][ 'type' ] ) AND $settings[ 'destination' ][ 'type' ] == 'http' ) echo 'checked'; ?>
				> <?php echo _e( 'HTTP POST', 'woocommerce-order-export' ) ?>
				<span class="ui-icon ui-icon-triangle-1-s my-icon-triangle"></span>
			</label>

			<div class="padding-bottom-10 set-destination my-block" id="email" style="display: none;" >
				<div class="wc-oe-header"><?php echo _e( 'Email Settings', 'woocommerce-order-export' ) ?></div>
				<div class="wc_oe-row">
					<div class="col-100pr">
						<label><div><?php echo _e( 'Recipient(s)', 'woocommerce-order-export' ) ?></div>
							<textarea name="settings[destination][email_recipients]" class="width-100"><?php echo $WC_Order_Export->get_value( $settings, "[destination][email_recipients]" ); ?></textarea>
						</label>
					</div>
				</div>
				<div class="wc_oe-row">
					<div class="col-100pr">
						<label><div><?php echo _e( 'Email Subject', 'woocommerce-order-export' ) ?></div>
							<input type="text" name="settings[destination][email_subject]" class="width-100" value="<?php echo $WC_Order_Export->get_value( $settings, "[destination][email_subject]" ); ?>">
						</label>
					</div>
				</div>
				<div class="wc_oe-row">
					<div class="col-100pr">
						<label>
							<div class="wrap"><input name="" class="wc_oe_test my-test-button add-new-h2" data-test="email" type="button" value="<?php echo _e( 'Test', 'woocommerce-order-export' ) ?>"></div>
						</label>
					</div>
				</div>
			</div>

			<div class="padding-bottom set-destination my-block" id="ftp" style="display: none;">
				<div class="wc-oe-header"><?php echo _e( 'FTP Settings', 'woocommerce-order-export' ) ?></div>
				<div class="wc_oe-row">
					<div class="col-50pr">
						<label><div><?php echo _e( 'Server Name', 'woocommerce-order-export' ) ?></div>
							<input type="text" name="settings[destination][ftp_server]" value="<?php echo $WC_Order_Export->get_value( $settings, "[destination][ftp_server]" ); ?>">
						</label>
					</div>
					<div class="col-50pr">
						<label><div><?php echo _e( 'Port', 'woocommerce-order-export' ) ?></div>
							<input type="text" name="settings[destination][ftp_port]" value="<?php echo $WC_Order_Export->get_value( $settings, "[destination][ftp_port]" ); ?>">
						</label>
					</div>
				</div>
				<div class="wc_oe-row">

					<div class="col-50pr">
						<label><div><?php echo _e( 'Username', 'woocommerce-order-export' ) ?></div>
							<input type="text" name="settings[destination][ftp_user]" value="<?php echo $WC_Order_Export->get_value( $settings, "[destination][ftp_user]" ); ?>">
						</label>
					</div>
					<div class="col-50pr">
						<label><div><?php echo _e( 'Password', 'woocommerce-order-export' ) ?></div>
							<input type="text" name="settings[destination][ftp_pass]" value="<?php echo $WC_Order_Export->get_value( $settings, "[destination][ftp_pass]" ); ?>">
						</label>
					</div>
				</div>
				<div class="wc_oe-row">
					<div class="col-100pr">
						<label><div><?php echo _e( 'Initial path', 'woocommerce-order-export' ) ?></div>
							<input type="text" class="width-100" name="settings[destination][ftp_path]" value="<?php echo $WC_Order_Export->get_value( $settings, "[destination][ftp_path]" ); ?>">
						</label>
					</div>
				</div>
				<div class="wc_oe-row">
					<div class="col-100pr">
						<label>
							<div class=""><input name="settings[destination][ftp_passive_mode]" type="checkbox" <?php echo $WC_Order_Export->get_value( $settings, "[destination][ftp_passive_mode]" ) ? 'checked' : ''; ?>><?php echo _e( 'Passive mode', 'woocommerce-order-export' ) ?></div>
						</label>
					</div>
				</div>
				<div class="wc_oe-row">
					<div class="col-100pr">
						<label>
							<div class="wrap"><input name="" class="wc_oe_test my-test-button add-new-h2" data-test="ftp" type="button" value="<?php echo _e( 'Test', 'woocommerce-order-export' ) ?>"></div>
						</label>
					</div>
				</div>
			</div>

			<div class="padding-bottom-10 set-destination my-block" id="http" style="display: none;" >
				<div class="wc-oe-header"><?php echo _e( 'HTTP POST Settings', 'woocommerce-order-export' ) ?></div>
				<div class="wc_oe-row">
					<div class="col-100pr">
						<label>
							<div><?php echo _e( 'URL', 'woocommerce-order-export' ) ?></div>
							<input type="text" name="settings[destination][http_post_url]" class="width-100" value="<?php echo $WC_Order_Export->get_value( $settings, "[destination][http_post_url]" ); ?>">
						</label>
					</div>
				</div>
				<div class="wc_oe-row">
					<div class="col-100pr">
						<label>
							<div class="wrap"><input name="" class="wc_oe_test my-test-button add-new-h2" data-test="http" type="button" value="<?php echo _e( 'Test', 'woocommerce-order-export' ) ?>"></div>
						</label>
					</div>
				</div>
			</div>
			
			<div id='test_reply_div'>
				<b><?php echo _e( 'Test Results', 'woocommerce-order-export' ) ?></b><br>
				<textarea rows=5 id='test_reply' style="overflow: auto; width:100%" wrap='off'></textarea>
			</div>

		</div>
		<br>
	<?php endif; ?>

	<div class="my-block">
		<span class="my-hide-next "><?php _e( 'Filter by status', 'woocommerce-order-export' ) ?>
			<span class="ui-icon ui-icon-triangle-1-s my-icon-triangle"></span></span>
		<div id="my-order" hidden="hidden">
			<span class="wc-oe-header"><?php echo _e( 'Order Statuses', 'woocommerce-order-export' ) ?></span>
			<select id="statuses" name="settings[statuses][]" multiple="multiple" style="width: 100%">
				<?php foreach ( wc_get_order_statuses() as $id => $status ) { ?>
					<option value="<?php echo $id ?>" <?php if ( in_array( $id, $settings[ 'statuses' ] ) ) echo 'selected'; ?>><?php echo $status ?></option>
				<?php } ?>
			</select>
		</div>
	</div>

	<br>
	<br>

	<div class="my-block">
		<div id=select2_warning style='display:none;color:red;font-size: 120%;'><?php _e( "The filters won't work correctly.<br>Another plugin uses outdated Select2.js", 'woocommerce-order-export' ) ?></div>
		<span class="my-hide-next "><?php _e( 'Filter by product', 'woocommerce-order-export' ) ?>
			<span class="ui-icon ui-icon-triangle-1-s my-icon-triangle"></span></span>
		<div id="my-products" hidden="hidden">
			<span class="wc-oe-header"><?php echo _e( 'Product categories', 'woocommerce-order-export' ) ?></span>
			<select id="product_categories" name="settings[product_categories][]" multiple="multiple" style="width: 100%">
				<?php
				if ( $settings[ 'product_categories' ] )
					foreach ( $settings[ 'product_categories' ] as $cat ) {
						$cat_term = get_term( $cat, 'product_cat' );
						?>
						<option selected value="<?php echo $cat_term->term_id ?>"> <?php echo $cat_term->name; ?></option>
					<?php } ?>
			</select>

			<span class="wc-oe-header"><?php echo _e( 'Product', 'woocommerce-order-export' ) ?></span>

			<select id="products" name="settings[products][]" multiple="multiple" style="width: 100%;">
				<?php
				if ( $settings[ 'products' ] )
					foreach ( $settings[ 'products' ] as $prod ) {
						$p = get_the_title( $prod );
						?>
						<option selected value="<?php echo $prod ?>"> <?php echo $p; ?></option>
					<?php } ?>
			</select>

			<span class="wc-oe-header"><?php echo _e( 'Product Attributes', 'woocommerce-order-export' ) ?></span>
			<br>
			<select id="attributes" style="width: auto;">
				<?php foreach ( WC_Order_Export_Data_Extractor::get_product_attributes() as $attr_id => $attr_name ) { ?>
					<option><?php echo $attr_name; ?></option>
				<?php } ?>
			</select>
			=
			<button id="add_attributes" class="button-secondary"><span class="dashicons dashicons-plus-alt"></span></button>
			<br>
			<select id="attributes_check" multiple name="settings[product_attributes][]" style="width: 100%;">
				<?php
				if ( $settings[ 'product_attributes' ] )
					foreach ( $settings[ 'product_attributes' ] as $prod ) {
						?>
						<option selected value="<?php echo $prod; ?>"> <?php echo $prod; ?></option>
					<?php } ?>
			</select>
			
			<span class="wc-oe-header"><?php echo _e( 'Product Taxonomies', 'woocommerce-order-export' ) ?></span>
			<br>
			<select id="taxonomies" style="width: auto;">
				<?php foreach ( WC_Order_Export_Data_Extractor::get_product_taxonomies() as $attr_id => $attr_name ) { ?>
					<option><?php echo $attr_name; ?></option>
				<?php } ?>
			</select>
			=
			<input type=text id="text_taxonomies" value=''> <button id="add_taxonomies" class="button-secondary"><span class="dashicons dashicons-plus-alt"></span></button>
			<br>
			<select id="taxonomies_check" multiple name="settings[product_taxonomies][]" style="width: 100%;">
				<?php
				if ( $settings[ 'product_taxonomies' ] )
					foreach ( $settings[ 'product_taxonomies' ] as $prod ) {
						?>
						<option selected value="<?php echo $prod; ?>"> <?php echo $prod; ?></option>
					<?php } ?>
			</select>
			
		</div>
	</div>

	<br>
	<br>

	<div class="my-block">
		<span class="my-hide-next "><?php _e( 'Filter by shipping', 'woocommerce-order-export' ) ?>
			<span class="ui-icon ui-icon-triangle-1-s my-icon-triangle"></span></span>
		<div id="my-shipping" hidden="hidden">
			<span class="wc-oe-header"><?php echo _e( 'Shipping locations', 'woocommerce-order-export' ) ?></span>
			<br>
			<select id="shipping_locations">
				<option>City</option>
				<option>State</option>
				<option>Postcode</option>
				<option>Country</option>
			</select>
			=
			<button id="add_locations" class="button-secondary"><span class="dashicons dashicons-plus-alt"></span></button>
			<br>
			<select id="locations_check" multiple name="settings[shipping_locations][]" style="width: 100%;">
				<?php
				print_r( $settings[ 'shipping_locations' ] );
				if ( $settings[ 'shipping_locations' ] )
					foreach ( $settings[ 'shipping_locations' ] as $location ) {
						?>
						<option selected value="<?php echo $location; ?>"> <?php echo $location; ?></option>
					<?php } ?>
			</select>
		</div>
	</div>
</div>

<div class="clearfix"></div>
<br><br>
<div class="my-block">
	<span id='adjust-fields-btn' class="my-hide-next "><?php _e( 'Set up fields to export ', 'woocommerce-order-export' ) ?>
			<span class="ui-icon ui-icon-triangle-1-s my-icon-triangle"></span></span>
	<div id="manage_fields" style="display: none;">
		<br>
		<div id='fields_control' style='display:none'>
			<div class='div_meta' style='display:none'>
				<label style="width: 40%;"><?php _e( 'Meta key', 'woocommerce-order-export' ) ?>:<select id='select_custom_meta'>
						<?php
						foreach ( WC_Order_Export_Data_Extractor::get_all_order_custom_meta_fields() as $meta_id => $meta_name ) {
							echo "<option value=$meta_name >$meta_name</option>";
						};
						?>
					</select></label>
				<label style="width: 40%;"><?php _e( 'Column Name', 'woocommerce-order-export' ) ?>:<input type='text' id='colname_custom_meta'/></label>

				<div style="text-align: right;">
					<button  id='button_custom_meta' class='button-secondary'>Confirm</button>
					<button  class='button-secondary button_cancel'>Cancel</button>
				</div>
			</div>
			<div class='div_custom' style='display:none;'>
				<label style="width: 40%;"><?php _e( 'Column Name', 'woocommerce-order-export' ) ?>:<input type='text' id='colname_custom_field'/></label>
				<label style="width: 40%;"><?php _e( 'Value', 'woocommerce-order-export' ) ?>:<input type='text' id='value_custom_field'/></label>
				<div style="text-align: right;">
					<button  id='button_custom_field' class='button-secondary'><?php _e( 'Confirm', 'woocommerce-order-export' ) ?></button>
					<button   class='button-secondary button_cancel'><?php _e( 'Cancel', 'woocommerce-order-export' ) ?></button>
				</div>
			</div>
			<div class='div1'><span><strong><?php _e( 'Use sections', 'woocommerce-order-export' ) ?>:</strong></span> <?php
				foreach ( WC_Order_Export_Data_Extractor::get_order_segments() as $section_id => $section_name ) {
					echo "<label ><input type=checkbox value=$section_id checked class='field_section'>$section_name &nbsp;</label>";
				}
				?>
			</div>
			<div class='div2'>
				<span><strong><?php _e( 'Actions', 'woocommerce-order-export' ) ?>:</strong></span>
				<button  id='orders_add_custom_meta' class='button-secondary'><?php _e( 'Add Field', 'woocommerce-order-export' ) ?></button>
				<br><br>
				<button  id='orders_add_custom_field' class='button-secondary'><?php _e( 'Add Static Field', 'woocommerce-order-export' ) ?></button>
			</div>
		</div>
		<div id='fields' style='display:none;'>
			<br>
			<div class="mapping_col_2">
				<label style="margin-left: 3px;">
					<input type="checkbox" name="orders_all" value="1"> <?php _e( 'Select All', 'woocommerce-order-export' ) ?></label>
			</div>
			<label class="mapping_col_3" style="color: red; font-size: medium;">
				<?php _e( 'Drag rows to reorder exported fields', 'woocommerce-order-export' ) ?>
			</label>
			<br>
			<ul id="order_fields"></ul>

		</div>
		<div id="modal_content" style="display: none;"></div>
	</div>

</div>
<p class="submit">
	<input type="submit" id='preview-btn' class="button-secondary" value="<?php _e( 'Preview', 'woocommerce-order-export' ) ?>" />
	<input type="submit" id='save-btn' class="button-primary" value="<?php _e( 'Save Settings', 'woocommerce-order-export' ) ?>" />
	<?php if ( $show[ 'export_button' ] ) { ?>
		<input type="submit" id='export-btn' class="button-secondary" value="<?php _e( 'Export', 'woocommerce-order-export' ) ?>" />
	<div id="progress_div" style="display: none;">
		<div id="progressBar"><div></div></div>
	</div>
	<div id="background"></div>
	<?php } ?>
</p>

</form>
<textarea rows=10 id='output_preview' style="overflow: auto;" wrap='off'></textarea> 
<div id='output_preview_csv' style="overflow: auto;width:100%"></div>

<form id='export_new_window_form' method=POST target=_blank></form>
<iframe id='export_new_window_frame' width=0 height=0 style='display:none'></iframe>



<script>
	jQuery( document ).ready( function( $ ) {

		$( '#schedule-1,#schedule-2' ).change( function() {
			if ( $( '#schedule-1' ).is( ':checked' ) && $( '#schedule-1' ).val() == 'schedule-1' ) {
				$( '#d-schedule-2 input:not(input[type=radio])' ).attr( 'disabled', true )
				$( '#d-schedule-2 select' ).attr( 'disabled', true )
				$( '#d-schedule-1 input:not(input[type=radio])' ).attr( 'disabled', false )
				$( '#d-schedule-1 select' ).attr( 'disabled', false )
			} else {
				$( '#d-schedule-1 input:not(input[type=radio])' ).attr( 'disabled', true )
				$( '#d-schedule-1 select' ).attr( 'disabled', true )
				$( '#d-schedule-2 select' ).attr( 'disabled', false )
				$( '#d-schedule-2 input:not(input[type=radio]) ' ).attr( 'disabled', false )
			}
		} )
		$( '#schedule-1' ).change()
		$( '.wc_oe-select-interval' ).change( function() {
			var interval = $( this ).val()
			if ( interval == 'custom' ) {
				$( '#custom_interval' ).show()
			} else {
				$( '#custom_interval' ).hide()
			}
		} )
		$( '.wc_oe-select-interval' ).change()

		$( '.output_destination' ).click( function() {
			var target = $( this ).val();
			$( '.set-destination:not(#' + target + ')' ).hide();
			$( '.my-icon-triangle' ).removeClass( 'ui-icon-triangle-1-n' );
			$( '.my-icon-triangle' ).addClass( 'ui-icon-triangle-1-s' );
			if (!jQuery( '#' + target ).is( ':hidden' )) {
				jQuery( '#' + target ).hide();
			}
			else {
				if (jQuery( '#' + target ).is( ':hidden' )) {
					jQuery( '#' + target ).show();
					$('#test_reply_div').hide();
					$( this ).next().removeClass( 'ui-icon-triangle-1-s' );
					$( this ).next().addClass( 'ui-icon-triangle-1-n' );
				}
			}
		} )

		function my_hide(item) {
			if($( item ).is(':hidden')) {
				$( item ).show();
				return false;
			}
			else {
				$( item ).hide();
				return true;
			}
		}

		$('.my-hide-parent').click(function(){
			my_hide($( this ).parent());
		});

		$('.my-hide-next').click(function(){
			var f = my_hide($( this ).next());
			if (f) {
				$( this ).find( 'span').removeClass( 'ui-icon-triangle-1-n' );
				$( this ).find( 'span').addClass( 'ui-icon-triangle-1-s' );
			}
			else {
				$( this ).find( 'span').removeClass( 'ui-icon-triangle-1-s' );
				$( this ).find( 'span').addClass( 'ui-icon-triangle-1-n' );
			}
			return false;
		});


		$( '.wc_oe_test' ).click( function() {
			var test = $( this ).attr( 'data-test' );
			var data = $( '#export_job_settings' ).serialize()
			data = data + "&action=order_exporter&method=test_destination&mode=" + mode + "&id=" + job_id + "&format=" + test ;
			$('#test_reply_div').hide();
			$.post( ajaxurl,data,function (data){
				$('#test_reply').val(data);
				$('#test_reply_div').show();
			})
		} )
	} )

	function remove_custom_field( item ) {
		jQuery( item).parent().parent().remove();
		return false;
	}
	
	function create_fields( format ) {
		jQuery( '#export_job_settings' ).prepend( jQuery( "#fields_control_products" ) );
		jQuery( '#export_job_settings' ).prepend( jQuery( "#fields_control_coupons" ) );
		jQuery( "#order_fields" ).html();
		jQuery( "#modal_content" ).html( "" );

		var html = '';
		jQuery.each( window['order_fields'], function( index, value ) {
			var checked = ( value.checked == 1 ) ? 'checked' : '';
			var colname = (format=='XLS' || format=='CSV') ? value.colname: ( format=='XML' ? to_xml_tags(index) :index);
			if ( index == 'products' || index == 'coupons' ) {
				var sel_rows = ( value.repeat == 'rows' ) ? 'checked' : '';
				var sel_cols = ( value.repeat == 'columns' ) ? 'checked' : '';
				var modal = '<div id="modal-manage-' + index + '" style="display:none;"><p>';
				modal += create_modal_fields( format, index );
				modal += '</p></div>';
				jQuery( "#modal_content" ).append( modal );
				var row = '<li class="mapping_row segment_' + value.segment + '">\
                                                        <div class="mapping_col_1">\
                                                                <input type=hidden name=orders[segment][' + index + ']  value="' + value.segment + '">\
                                                                <input type=hidden name=orders[label][' + index + ']  value="' + value.label + '">\
                                                                <input type=hidden name=orders[exported][' + index + ']  value="0">\
                                                                <input type=checkbox name=orders[exported][' + index + ']  ' + checked + ' value="1">\
                                                        </div>\
                                                        <div class="mapping_col_2">' + value.label + '</div>\
                                                        <div class="mapping_col_3">';
				if ( format == 'XLS' || format=='CSV' )
					row += 'Add <input type=radio name=orders[repeat][' + index + '] value="columns" ' + sel_cols + ' >as columns  \
                                                                                                                                                                        <input type=radio name=orders[repeat][' + index + '] value="rows" ' + sel_rows + ' >as rows\
                                                                                                                '
				row += '<input class="mapping_fieldname" type=input name=orders[colname][' + index + '] value="' + colname + '">\
                                                                                                                        <a href="#TB_inline?width=600&height=550&inlineId=modal-manage-' + index + '" class="thickbox button-primary">Set up fields to export </a></div>\
                                                </li>\
                        ';
			}
			else {
				var value_part = ''
				var label_part = ''
				if ( index.indexOf( 'custom_field' ) >= 0 ) {
					value_part = '<div class="mapping_col_3"><input class="mapping_fieldname" type=input name=orders[value][' + index + '] value="' + value.value + '"></div>';
					label_part = '<a href="#" onclick="remove_custom_field(this);" style="float: right;"><span class="ui-icon ui-icon-trash"></span></a>';
				}

				var row = '<li class="mapping_row segment_' + value.segment + '">\
                                                        <div class="mapping_col_1">\
                                                                <input type=hidden name=orders[segment][' + index + ']  value="' + value.segment + '">\
                                                                <input type=hidden name=orders[label][' + index + ']  value="' + value.label + '">\
                                                                <input type=hidden name=orders[exported][' + index + ']  value="0">\
                                                                <input type=checkbox name=orders[exported][' + index + ']  ' + checked + ' value="1">\
                                                        </div>\
                                                        <div class="mapping_col_2">' + value.label + label_part + '</div>\
                                                        <div class="mapping_col_3"><input class="mapping_fieldname" type=input name=orders[colname][' + index + '] value="' + colname + '"></div> ' + value_part + '\
                                                </li>\
                        ';
			}
			html += row;
		} );

		jQuery( "#order_fields" ).html( html );
		jQuery( '#modal-manage-products' ).prepend( jQuery( "#fields_control_products" ) );
		jQuery( '#modal-manage-coupons' ).prepend( jQuery( "#fields_control_coupons" ) );
		jQuery( "#fields_control_products").css( 'display', 'inline-block' );
		jQuery( "#fields_control_coupons").css( 'display', 'inline-block' );
		add_bind_for_custom_fields( 'products', output_format, jQuery( "#sort_products" ) );
		add_bind_for_custom_fields( 'coupons', output_format, jQuery( "#sort_coupons" ) );

	}



	function create_modal_fields( format, index_p ) {
		//console.log( 'order_' + index_p + '_fields', window['order_' + index_p + '_fields'] );

		var modal = "<div id='sort_" + index_p + "'>";
		jQuery.each( window['order_' + index_p + '_fields'], function( index, value ) {
			var colname = (format=='XLS' || format=='CSV') ? value.colname: ( format=='XML' ? to_xml_tags(index) :index);
			var checked = ( value.checked == 1 ) ? 'checked' : '';
			var row = '<li class="mapping_row segment_modal_' + index + '">\
                                                        <div class="mapping_col_1">\
                                                                <input type=hidden name=' + index_p + '[label][' + index + ']  value="' + value.label + '">\
                                                                <input type=hidden name=' + index_p + '[exported][' + index + ']  value="0">\
                                                                <input type=checkbox name=' + index_p + '[exported][' + index + ']  ' + checked + ' value="1">\
                                                        </div>\
                                                        <div class="mapping_col_2">' + value.label + '</div>\
                                                        <div class="mapping_col_3"><input class="mapping_fieldname" type=input name=' + index_p + '[colname][' + index + '] value="' + colname + '"></div>\
                                                </li>\
                        ';
			modal += row;
		} );
		modal += "</div>";
		return modal;
	}
		
	//for XML labels
	function to_xml_tags(str) {
		var arr = str.split(/_/);
		for(var i=0,l=arr.length; i<l; i++) {
			arr[i] = arr[i].substr(0,1).toUpperCase() +  (arr[i].length > 1 ? arr[i].substr(1).toLowerCase() : "");
		}
		return arr.join("_");
	}
	

	function change_filename_ext() {
		if ( jQuery( '#export_filename').size() ) {
			var filename = jQuery( '#export_filename input').val();
			var file = filename.replace(/^(.*)\..+$/, "$1." + output_format.toLowerCase());
			jQuery( '#export_filename input').val( file );
		}
	}

	jQuery( document ).ready( function( $ ) {
	
		try {
			select2_inits();
		}
		catch(err) {
			console.log(err.message);
			jQuery('#select2_warning').show();
		}
		
		bind_events();
		jQuery('#attributes').change();
		jQuery('#shipping_locations').change();
//		jQuery( '#' + output_format + '_options' ).show();

		//jQuery('#fields').toggle(); //debug 
		create_fields( output_format );
		$('#test_reply_div').hide();
//		jQuery( '#' + output_format + '_options' ).hide();

		jQuery( "#sort_products" ).sortable().disableSelection();
		jQuery( "#sort_coupons" ).sortable().disableSelection();
		jQuery( "#order_fields" ).sortable().disableSelection();



		jQuery( '.date' ).datepicker( {
			dateFormat: 'yy-mm-dd'
		} );

		jQuery( '#adjust-fields-btn' ).click( function() {
			jQuery( '#fields' ).toggle();
			jQuery( '#fields_control' ).toggle();
			return false;
		} );

		jQuery( '.field_section' ).click( function() {
			var section = jQuery( this ).val();
			var checked = jQuery( this ).is( ':checked' );

			jQuery( '.segment_' + section ).each( function( index ) {
				if ( checked ) {
					jQuery( this ).show();
					//jQuery(this).find('input:checkbox:first').attr('checked', true);
				}
				else {
					jQuery( this ).hide();
					jQuery( this ).find( 'input:checkbox:first' ).attr( 'checked', false );
				}
			} );
		} );

		jQuery( '.output_format' ).click( function() {
			var new_format = jQuery( this ).val();
			jQuery( '#my-format .my-icon-triangle').removeClass( 'ui-icon-triangle-1-n' );
			jQuery( '#my-format .my-icon-triangle').addClass( 'ui-icon-triangle-1-s' );

			if ( new_format != output_format ) {
				jQuery( this ).next().removeClass( 'ui-icon-triangle-1-s' );
				jQuery( this ).next().addClass( 'ui-icon-triangle-1-n' );
				jQuery( '#' + output_format + '_options' ).hide();
				jQuery( '#' + new_format + '_options' ).show();
				output_format = new_format;
				create_fields( output_format )
				jQuery( '#output_preview, #output_preview_csv' ).hide();
//				jQuery( '#fields' ).hide();
//				jQuery( '#fields_control' ).hide();
				change_filename_ext();
			}
			else {
				if (!jQuery( '#' + new_format + '_options' ).is( ':hidden' )) {
					jQuery( '#' + new_format + '_options' ).hide();
				}
				else {
					if (jQuery( '#' + new_format + '_options' ).is( ':hidden' )) {
						jQuery( '#' + new_format + '_options' ).show();
						jQuery( this ).next().removeClass( 'ui-icon-triangle-1-s' );
						jQuery( this ).next().addClass( 'ui-icon-triangle-1-n' );
					}
				}
			}

		} );

		$( '#order_fields input[type=checkbox]' ).change( function() {
			if ( $( '#order_fields input[type=checkbox]:not(:checked)' ).size() ) {
				$( 'input[name=orders_all]' ).attr( 'checked', false );
			}
			else {
				$( 'input[name=orders_all]' ).attr( 'checked', true );
			}
		} );

		$( 'input[name=orders_all]' ).change( function() {
			if ( $( 'input[name=orders_all]' ).is( ':checked' ) ) {
				$( '#order_fields input[type=checkbox]' ).attr( 'checked', true );
			}
			else {
				$( '#order_fields input[type=checkbox]' ).attr( 'checked', false );
			}
		} );

		if ($( '#order_fields input[type=checkbox]' ).size()) {
			$( '#order_fields input[type=checkbox]:first' ).change();
		}




		$( "#preview-btn" ).click( function() {
			jQuery( '#output_preview, #output_preview_csv' ).hide();
			var data = $( '#export_job_settings' ).serialize()
			data = data + "&action=order_exporter&method=preview&mode=" + mode + "&id=" + job_id;
			$.post( ajaxurl, data, function( response ) {
				var id = 'output_preview';
				if(output_format =='XLS' || output_format=='CSV')
					id = 'output_preview_csv';
				jQuery( '#'+id ).html( response );
				jQuery( '#'+id ).show();
			}
			, "html"
				);
			return false;
		} );
// EXPORT FUNCTIONS
		function get_data() {
			var data = $( '#export_job_settings' ).serializeArray()
			data.push( {name:'action',value:'order_exporter'});
			data.push( {name:'mode',value:mode});
			data.push( {name:'id',value:job_id});
			return data;
		}

		function progress(percent, $element) {

			if (percent == 0) {
				$element.find('div').html(percent + "%&nbsp;").animate({width: 0}, 0);
				waitingDialog();
				jQuery('#progress_div').show();
			}
			else {
				var progressBarWidth = percent * $element.width() / 100;
				$element.find('div').html(percent + "%&nbsp;").animate({width: progressBarWidth}, 200);

				if (percent >= 100) {
					jQuery('#progress_div').hide();
					closeWaitingDialog();
				}
			}
		}

		function get_all(start, percent, method) {

			progress(parseInt(percent, 10), jQuery('#progressBar'));

			if (percent < 100) {
				data = get_data();
				data.push( {name:'method',value:method});
				data.push( {name:'start',value:start});
				data.push( {name:'file_id',value:window.file_id});

				jQuery.post(ajaxurl, data, function(response) {
					get_all(response.start, (response.start / window.count) * 100, method)
				}, 'json');
			}
			else {
				data = get_data();
				data.push( {name:'method',value:'export_finish'});
				data.push( {name:'file_id',value:window.file_id});
				jQuery.post(ajaxurl, data, function(response) {
					$('#export_new_window_frame').attr("src",  ajaxurl+'?action=order_exporter&method=export_download&format='+output_format+'&file_id='+window.file_id);
				}, 'json');
			
//			$('#export_new_window_form').find("input").replaceWith( "" );
//			$('#export_new_window_form').attr("action",  ajaxurl);
//			$.each(data, function( index, obj ) {
//				if(obj.value != '') {
//					$('<input>').attr({
//						type: 'hidden',
//						name: obj.name,
//						value: obj.value
//					}).appendTo('#export_new_window_form');
//				}
//			});
//			$('#export_new_window_form').submit();
			}
		}

		function waitingDialog() {
			jQuery("#background").addClass("loading");
		}
		function closeWaitingDialog() {
			jQuery("#background").removeClass("loading");
		}
// EXPORT FUNCTIONS END
		$( "#export-btn, #my-quick-export-btn" ).click( function() {

			data = get_data();
			data.push( {name:'method',value:'export_start'});
			
			jQuery.post(ajaxurl, data, function(response) {
				window.count = response['total'];
				window.file_id = response['file_id'];
				console.log(window.count);
				if(window.count>0)
					get_all(0, 0, 'export_part');
			}, 'json');

			return false;
		} );
		$( "#save-btn" ).click( function() {
			var data = $( '#export_job_settings' ).serialize()
			data = data + "&action=order_exporter&method=save_settings&mode=" + mode + "&id=" + job_id;
			$.post( ajaxurl, data, function( response ) {
				if ( mode == 'cron' ) {
					document.location = '<?php echo admin_url( 'admin.php?page=wc-order-export&tab=schedules&save=y' ) ?>';
				}
				else {
					document.location = '<?php echo admin_url( 'admin.php?page=wc-order-export&tab=export&save=y' ) ?>';
				}
			}, "json" );
			return false;
		} );

		if ( $( '#my-order ul li:not(:first)').size() ) {
			$( '#my-order').prev().click();
		}

		var f = false;
		$( '#my-products ul').each( function( index ) {
			if ( $( this).find( 'li:not(:first)').size() ) {
				f = true;
			}
		} );
		if ( f ) {
			$( '#my-products').prev().click();
		}

		if ( $( '#my-shipping ul li:not(:first)').size() ) {
			$( '#my-shipping').prev().click();
		}

	} );
</script>
