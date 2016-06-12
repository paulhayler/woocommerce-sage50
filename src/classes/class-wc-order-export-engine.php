<?php
if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class WC_Order_Export_Engine {
	
	//
	public static function export( $settings, $filepath ) {
		$export_type = strtolower( $settings[ 'destination' ]['type']);
		if( !in_array( strtoupper($export_type), WC_Order_Export_Admin::$export_types) )
			return __("Wrong format",'woocommerce-order-export');
			
		include_once dirname( __FILE__ ) . "/exports/abstract-class-woe-export.php";
		include_once dirname( __FILE__ ) . "/exports/class-woe-export-{$export_type}.php";
		$class = 'WOE_Export_' . $export_type;
		$exporter = new $class($settings[ 'destination' ]);
		
		$filename = self::make_filename($settings['export_filename']);
		echo $exporter->run_export($filename,$filepath);
	}
	
	private static function make_filename( $mask ) {
		$subst = array(
			'%d'=>date('d'),
			'%m'=>date('m'),
			'%y'=>date('Y'),
		);
		return strtr($mask,$subst);
	}

	// labels for output columns
	private static function get_labels( $fields, $format, &$static_vals ) {
		$labels = array();
		foreach ( $fields as $key => $field ) {
			if(preg_match('#^custom_field_#',$key)) { // for static fields
				$static_vals[$key] = $field[ 'value' ];// record value
			}
			if ( $field[ 'checked' ] )
				$labels[ $key ] = apply_filters( "woe_get_{$format}_label_{$key}", $field[ 'colname' ] );
		}
		return $labels;
	}

	// gather columns having filters
	private static function check_filters( $fields, $format, $type ) {
		$filters = array();
		foreach ( $fields as $key => $field ) {
			if ( $field[ 'checked' ] AND has_filter( "woe_get_{$type}_{$format}_value_{$key}" ) )
				$filters[] = $key;
		}
		return $filters;
	}

	private static function init_formater( $mode, $settings, $fname, &$labels,&$static_vals ) {
		$format = strtolower( $settings[ 'format' ] );
		include_once dirname( __FILE__ ) . "/formats/abstract-class-woe-formatter.php";
		include_once dirname( __FILE__ ) . "/formats/class-woe-formatter-$format.php";

		$format_settings = array();
		foreach ( $settings as $key => $val ) {
			if ( preg_match( '#^format_' . $format . '_(.+)$#', $key, $m ) )
				$format_settings[ $m[ 1 ] ] = $val;
		}
//var_dump($settings[ 'order_fields'  ]);
		$static_vals = array('order'=>array(), 'products'=>array(), 'coupons'=>array());
		$labels = array(
			'order'		 => self::get_labels( $settings[ 'order_fields'  ], $format , $static_vals['order']),
			'products'	 => self::get_labels( $settings[ 'order_product_fields'  ], $format, $static_vals['products'] ),
			'coupons'	 => self::get_labels( $settings[ 'order_coupon_fields'  ], $format, $static_vals['coupons'] ),
		);
		
		$class = 'WOE_Formatter_' . $format;
		return new $class( $mode, $fname, $format_settings, $format, $labels );
	}

	private static function make_header_csv( $labels, $csv_max ) {
		$header = array();
		foreach ( $labels[ 'order' ] as $field => $label ) {
			$field_header = array();
			if ( $field == 'products' OR $field == 'coupons' ) {
				for ( $i = 1; $i <= $csv_max[ $field ]; $i++ ) {
					foreach ( $labels[ $field ] as $field2 => $label2 ) {
						$field_header[] = $label2 . ($csv_max[ $field ] > 1 ? ' #' . $i : '');
					}
				}
			}
			do_action( 'woocommerce_order_export_add_csv_headers', $field_header, $field );
			if ( empty( $field_header ) ) {
				$field_header[] = $label;
			}
			$header = array_merge( $header, $field_header );
		}
		return $header;
	}

	public static  function build_file( $settings, $make_mode, $output_mode , $offset=false, $limit=false, $filename='') {
		global $wpdb;
		if ( $output_mode == 'browser' ) {
			$filename = 'php://output';
			while(@ob_end_clean()) { }; // remove ob_xx
		} else {
			$filename = (!empty($filename) ? $filename : tempnam( "/tmp", $settings[ 'format' ] ));
		}


		//add_filter("woe_csv_output_filter",array($this,'testfilter'),10,2);
		$formater = self::init_formater( $make_mode, $settings, $filename, $labels, $static_vals );
		$format = strtolower( $settings[ 'format' ] );
		
		if($make_mode == 'finish') {
			if($format !='xls')
				$formater->finish();
			return $filename;
		}	

		//get IDs
		$sql					 = WC_Order_Export_Data_Extractor::sql_get_order_ids( $settings );
		if ( $make_mode == 'preview' )
			$sql .= " ORDER BY order_id DESC LIMIT 1";
		elseif ( $make_mode != 'estimate' )
			$sql .= " ORDER BY order_id ASC";
		
		//UNUSED ajax get partial orders
		if($make_mode == 'partial') {
			$offset = intval($offset);
			$limit = intval($limit );
			$sql .= " LIMIT $offset,$limit";
		}


		$order_ids				 = $wpdb->get_col( $sql );
		//$order_ids	= array(387);


		// prepare for XLS/CSV
		$csv_max[ 'coupons' ]	 = $csv_max[ 'products' ]	 = 1;
		if ( $format == 'xls' OR $format == 'csv') {
			if ( @$settings[ 'order_fields' ][ 'products' ][ 'repeat' ] == 'columns' )
				$csv_max[ 'products' ]	 = WC_Order_Export_Data_Extractor::get_max_order_items( "line_item", $order_ids );
			if ( @$settings[ 'order_fields' ][ 'coupons' ][ 'repeat' ] == 'columns' )
				$csv_max[ 'coupons' ]	 = WC_Order_Export_Data_Extractor::get_max_order_items( "coupon", $order_ids );
		}

		// try to optimize calls
		$filters_active	 = array(
			'order'		 => self::check_filters( $settings[ 'order_fields'  ], $format, 'order' ),
			'products'	 => self::check_filters( $settings[ 'order_product_fields'  ], $format, 'order_product' ),
			'coupons'	 => self::check_filters( $settings[ 'order_coupon_fields'  ], $format, 'order_coupon' ),
		);

		// check it once
		$export[ 'products' ]	 = $settings[ 'order_fields'  ][ 'products' ][ 'checked' ];
		$export[ 'coupons' ]	 = $settings[ 'order_fields'  ][ 'coupons' ][ 'checked' ];
		$get_coupon_meta		 = ( $export[ 'coupons' ] AND array_diff( array_keys( $labels[ 'coupons' ] ), array( 'code', 'discount_amount', 'discount_amount_tax' ) ) );


		// 0 
		$header = ($format == 'xls' OR $format == 'csv') ? self::make_header_csv( $labels, $csv_max ) : '';
		
		$options = array();
		if( $format == 'xls' AND @$settings['format_xls_populate_other_columns_product_rows']  OR $format == 'csv' AND @$settings['format_csv_populate_other_columns_product_rows'] )
			$options['populate_other_columns_product_rows'] = 1;
		
		if($make_mode != 'partial')
			$formater->start( $header );
		elseif($format =='json' AND $offset>0 )	
			$formater->prev_added = true;
		
		if($make_mode == 'estimate') { //if estimate return total count
			return $wpdb->get_var(str_replace( 'ID as order_id', 'COUNT(ID) as order_count', $sql ) );
		}
		
		WC_Order_Export_Data_Extractor::prepare_for_export();
		foreach ( $order_ids as $order_id ) {
			$rows = WC_Order_Export_Data_Extractor::fetch_order_data( $order_id, $labels, $format, $filters_active, $csv_max, $export, $get_coupon_meta, $static_vals, $options );
			foreach ( $rows as $row ) {
				$formater->output( $row );
			}
		}
		if( $make_mode != 'partial' OR $format=='xls' )
			$formater->finish();
		return $filename;
	}

}
