<?php
if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class WC_Order_Export_Data_Extractor {
	static $statuses;
	static $countries;

    // Data for FILTERS
	public static function get_visible_order_custom_meta_fields() {
		global $wpdb;
		
		// WC internal table , skip hidden and attributes
		$wc_fields = $wpdb->get_col("SELECT DISTINCT meta_key FROM {$wpdb->prefix}woocommerce_order_itemmeta WHERE meta_key NOT LIKE '\_%' AND meta_key NOT LIKE 'pa\_%' AND order_item_id IN 
									(SELECT DISTINCT order_item_id FROM {$wpdb->prefix}woocommerce_order_items WHERE order_item_type = 'line_item' )");

		// WP internal table	, skip hidden						
		$wp_fields = $wpdb->get_col("SELECT DISTINCT meta_key FROM {$wpdb->postmeta} WHERE meta_key NOT LIKE '\_%'    AND post_id IN 
									(SELECT DISTINCT ID FROM {$wpdb->posts} WHERE post_type = 'shop_order' )");
									
		$fields = array_merge($wp_fields , $wc_fields);
		sort($fields );
		
		return apply_filters('woe_get_visible_order_custom_meta_fields', $fields); 
	}			
	public static function get_visible_product_custom_meta_fields() {
		global $wpdb;
		
		// WP internal table	, skip hidden and attributes						
		$fields  = $wpdb->get_col("SELECT DISTINCT meta_key FROM {$wpdb->postmeta} WHERE meta_key NOT LIKE '\_%' AND meta_key NOT LIKE'attribute\_%' AND post_id IN 
									(SELECT DISTINCT ID FROM {$wpdb->posts} WHERE post_type = 'product' OR post_type = 'product_variation')");
		sort($fields );
		return apply_filters('woe_get_visible_product_custom_meta_fields', $fields); 
	}			
	public static function get_visible_coupon_custom_meta_fields() {
		global $wpdb;
		
		// WP internal table	, skip hidden and attributes						
		$fields  = $wpdb->get_col("SELECT DISTINCT meta_key FROM {$wpdb->postmeta} WHERE meta_key NOT LIKE '\_%' AND post_id IN 
									(SELECT DISTINCT ID FROM {$wpdb->posts} WHERE post_type = 'shop_coupon')");
		sort($fields );
		return apply_filters('woe_get_visible_coupon_custom_meta_fields', $fields); 
	}			
	
	// ADD custom fields for export
	public static function get_all_order_custom_meta_fields() {
		global $wpdb;
		
		// WC internal table , skip hidden and attributes
		//$wc_fields = $wpdb->get_col("SELECT DISTINCT meta_key FROM {$wpdb->prefix}woocommerce_order_itemmeta WHERE order_item_id IN 
		//							(SELECT DISTINCT order_item_id FROM {$wpdb->prefix}woocommerce_order_items WHERE order_item_type = 'line_item' )");
		$wc_fields = array();
		
		// WP internal table	, skip hidden						
		$wp_fields = $wpdb->get_col("SELECT DISTINCT meta_key FROM {$wpdb->postmeta} WHERE post_id IN 
									(SELECT DISTINCT ID FROM {$wpdb->posts} WHERE post_type = 'shop_order' )");
									
		$fields = array_merge($wp_fields , $wc_fields);
		sort($fields );
		
		return apply_filters('woe_get_all_order_custom_meta_fields', $fields); 
	}			
	public static function get_all_product_custom_meta_fields() {
		global $wpdb;
		
		$wc_fields = $wpdb->get_col("SELECT DISTINCT meta_key FROM {$wpdb->prefix}woocommerce_order_itemmeta WHERE order_item_id IN 
									(SELECT DISTINCT order_item_id FROM {$wpdb->prefix}woocommerce_order_items WHERE order_item_type = 'line_item' )");
									
		// WP internal table	, skip hidden and attributes						
		$wp_fields = $wpdb->get_col("SELECT DISTINCT meta_key FROM {$wpdb->postmeta} WHERE post_id IN 
									(SELECT DISTINCT ID FROM {$wpdb->posts} WHERE post_type = 'product' OR post_type = 'product_variation')");
		$fields = array_merge($wp_fields , $wc_fields);
		sort($fields );
		return apply_filters('woe_get_all_product_custom_meta_fields', $fields); 
	}			
	public static function get_all_coupon_custom_meta_fields() {
		global $wpdb;
		
		// WP internal table	, skip hidden and attributes						
		$fields  = $wpdb->get_col("SELECT DISTINCT meta_key FROM {$wpdb->postmeta} WHERE post_id IN 
									(SELECT DISTINCT ID FROM {$wpdb->posts} WHERE post_type = 'shop_coupon')");
		sort($fields );
		return apply_filters('woe_get_all_coupon_custom_meta_fields', $fields); 
	}			
	
	//for FILTERS
	public static function get_product_attributes() {
		global $wpdb;
		
		$attrs = array();
		
		// WC internal table , skip hidden and attributes
		$wc_fields = $wpdb->get_results("SELECT attribute_name,attribute_label FROM {$wpdb->prefix}woocommerce_attribute_taxonomies");
		foreach($wc_fields as $f)
			$attrs['pa_'.$f->attribute_name] = $f->attribute_label;
		
		
		// WP internal table, take all attributes
		$wp_fields  = $wpdb->get_col("SELECT DISTINCT meta_key FROM {$wpdb->postmeta} WHERE meta_key LIKE 'attribute\_%' AND post_id IN  (SELECT DISTINCT ID FROM {$wpdb->posts} WHERE post_type = 'product_variation')");
		foreach($wp_fields as $attr) {
			$attr = str_replace("attribute_","",$attr);
			if(substr($attr,0,3)=='pa_') // skip attributes from WC table
				continue;
			$name = str_replace("-"," ",$attr);
			$name = ucwords($name);
			$attrs[$attr] = $name;
		}
		asort($attrs);
		return apply_filters('woe_get_product_attributes', $attrs); 
	}	
	
	public static function get_product_taxonomies() {
		global $wpdb;
		
		$attrs = array();
		
		// WP internal table, take all taxonomies for products
		$wpdb->show_errors(true);
		$wp_fields  = $wpdb->get_col("SELECT DISTINCT taxonomy FROM {$wpdb->term_relationships} 
					JOIN {$wpdb->term_taxonomy} ON {$wpdb->term_taxonomy}.term_taxonomy_id = {$wpdb->term_relationships}.term_taxonomy_id
					WHERE {$wpdb->term_relationships}.object_id IN  (SELECT DISTINCT ID FROM {$wpdb->posts} WHERE post_type = 'product' OR post_type='product_variation')");
		foreach($wp_fields as $attr) {
			if($attr == 'product_cat' OR substr($attr,0,3)=='pa_') // skip category and attributes from WC table
				continue;
			$attrs[$attr] = $attr;
		}
		asort($attrs);
		return apply_filters('woe_get_product_taxonomies', $attrs); 
	}			
	
	public static function get_order_product_fields($format) {
		$map = array(
			'sku'=>array('label'=>'SKU', 'checked'=>1),
			'name'=>array('label'=>'Name', 'checked'=>1),
			'qty'=>array('label'=>'Quantity', 'checked'=>1),
			'price'=>array('label'=>'Price', 'checked'=>0),
			'line_no_tax'=>array('label'=>'Order line (w/o tax)', 'checked'=>0),
			'line_tax'=>array('label'=>'Order line tax', 'checked'=>0),
			'line_total'=>array('label'=>'Order line total', 'checked'=>0),
			'type'=>array('label'=>'Type', 'checked'=>0),
			'category'=>array('label'=>'Category', 'checked'=>0),
			'tags'=>array('label'=>'Tags', 'checked'=>0),
			'width'=>array('label'=>'Width', 'checked'=>0),
			'length'=>array('label'=>'Length','checked'=>0),
			'height'=>array('label'=>'Height','checked'=>0),
 			'weight'=>array('label'=>'Weight','checked'=>0),
 		);
 		
		foreach($map  as $key=>$value) {
			$map[$key]['colname'] = $value['label'];
			$map[$key]['checked'] = 1; //debug 
		}	
		
		return apply_filters('woe_get_order_product_fields',$map,$format);
	}
	
	public static function get_order_coupon_fields($format) {
		$map = array(
			'code'=>array('label'=>'Coupon Code', 'checked'=>1),
			'discount_amount'=>array('label'=>'Discount Amount', 'checked'=>1),
			'discount_amount_tax'=>array('label'=>'Discount Amount Tax', 'checked'=>1),
			//'type'=>array('label'=>'Coupon Type', 'checked'=>0),
 		);
 		
		foreach($map  as $key=>$value) {
			$map[$key]['colname'] = $value['label'];
		}	
		
		return apply_filters('woe_get_order_coupon_fields',$map,$format);
	}
	
	
	public static function get_order_fields($format) {
		$map = array();
		foreach( array('common','user','billing','shipping','product','coupon','cart','misc') as $segment) {
			$method =  "get_order_fields_".$segment;
			$map_segment = self::$method();
			
			foreach($map_segment as $key=>$value) {
				$map_segment[$key]['segment'] = $segment;
				$map_segment[$key]['colname'] = $value['label'];
				$map_segment[$key]['checked'] = 1; //debug 
			}	
			// woe_get_order_fields_common	filter
			$map_segment = apply_filters("woe_$method", $map_segment,$format);
			$map = array_merge($map,$map_segment);
		}
		return apply_filters('woe_get_order_fields',$map);
	}
	
	public static function get_order_fields_common() {
		return array(
			'order_id' => array('label'=>'Order Id', 'checked'=>0),
			'order_number' => array('label'=>'Order Number', 'checked'=>1),
			'order_status' => array('label'=>'Order Status', 'checked'=>1),
			'order_date' => array('label'=>'Order Date', 'checked'=>1),
			'transaction_id' => array('label'=>'Transaction Id', 'checked'=>0),
			'completed_date' => array('label'=>'Сompleted Date', 'checked'=>0),
			'customer_note' => array('label'=>'Сustomer Note', 'checked'=>1),
		);
	}
	public static function get_order_fields_user() {
		return array(
			'customer_ip_address' => array('label'=>'Customer IP address', 'checked'=>1),
			'customer_user' => array('label'=>'Customer User Id', 'checked'=>1),
			'user_login' => array('label'=>'Customer Username', 'checked'=>0),
			'user_email' => array('label'=>'Customer User Email', 'checked'=>0),
		);
	}
	public static function get_order_fields_billing() {
		return array(
			'billing_first_name' => array('label'=>'First Name (Billing)', 'checked'=>1),
			'billing_last_name' => array('label'=>'Last Name (Billing)', 'checked'=>1),
			'billing_full_name' => array('label'=>'Full Name (Billing)', 'checked'=>0),
			'billing_company' => array('label'=>'Company (Billing)', 'checked'=>1),
			'billing_address_1' => array('label'=>'Address 1 (Billing)', 'checked'=>1),
			'billing_address_2' => array('label'=>'Address 2 (Billing)', 'checked'=>1),
			'billing_city' => array('label'=>'City (Billing)', 'checked'=>1),
			'billing_state' => array('label'=>'State (Billing)', 'checked'=>1),
			'billing_postcode' => array('label'=>'Zip (Billing)', 'checked'=>1),
			'billing_country' => array('label'=>'Country Code (Billing)', 'checked'=>1),
			'billing_country_full' => array('label'=>'Country Name (Billing)', 'checked'=>0),
			'billing_email' => array('label'=>'Email (Billing)', 'checked'=>1),
			'billing_phone' => array('label'=>'Phone (Billing)', 'checked'=>1),
		);
	}
	public static function get_order_fields_shipping() {
		return array(
			'shipping_first_name' => array('label'=>'First Name (Shipping)', 'checked'=>1),
			'shipping_last_name' => array('label'=>'Last Name (Shipping)', 'checked'=>1),
			'shipping_full_name' => array('label'=>'Full Name (Shipping)', 'checked'=>0),
			'shipping_company' => array('label'=>'Company (Shipping)', 'checked'=>1),
			'shipping_address_1' => array('label'=>'Address 1 (Shipping)', 'checked'=>1),
			'shipping_address_2' => array('label'=>'Address 2 (Shipping)', 'checked'=>1),
			'shipping_city' => array('label'=>'City (Shipping)', 'checked'=>1),
			'shipping_state' => array('label'=>'State (Shipping)', 'checked'=>1),
			'shipping_postcode' => array('label'=>'Zip (Shipping)', 'checked'=>1),
			'shipping_country' => array('label'=>'Country Code (Shipping)', 'checked'=>1),
			'shipping_country_full' => array('label'=>'Country Name(Shipping)', 'checked'=>0),
		);
	}
	// meta 
	public static function get_order_fields_product() {
		return array(
			'products' => array('label'=>'Products', 'checked'=>1,'repeat'=>'columns'),
		);
	}
	// meta 
	public static function get_order_fields_coupon() {
		return array(
			'coupons' => array('label'=>'Coupons', 'checked'=>1,'repeat'=>'columns'),
		);
	}
	public static function get_order_fields_cart() {
		return array(
			'shipping_method_title' => array('label'=>'Shipping Method Title', 'checked'=>1),
			'payment_method_title' => array('label'=>'Payment Method Title', 'checked'=>1),
			'coupons_used' => array('label'=>'Сoupons Used', 'checked'=>1),
			'cart_discount' => array('label'=>'Cart Discount Amount', 'checked'=>1),
			'cart_discount_tax' => array('label'=>'Cart Discount Tax Amount', 'checked'=>1),
			'order_tax' => array('label'=>'Order Tax Amount', 'checked'=>1),
			'order_shipping' => array('label'=>'Order Shipping Amount', 'checked'=>1),
			'order_shipping_tax' => array('label'=>'Order Shipping Tax Amount', 'checked'=>1),
			'order_total' => array('label'=>'Order Total Amount', 'checked'=>1),
			'order_currency' => array('label'=>'Currency', 'checked'=>0),
		);
	}
	public static function get_order_fields_misc() {
		return array(
			'count_total_items' => array('label'=>'Total items', 'checked'=>0),
			'count_unique_products' => array('label'=>'Total products', 'checked'=>0),
		);
	}
	
	// for UI only 
	public static function get_visible_segments($fields) {
		$sections = array();
		foreach($fields as $field) {
			if($field['checked'])
				$sections[$field['segment']] = 1;
		}
		return array_keys($sections);
	}
	public static function get_order_segments() {
		return array('common'=>'Common','user'=>'User','billing'=>"Billing",'shipping'=>"Shipping",'product'=>"Products",'coupon'=>"Coupons",'cart'=>"Cart",'misc'=>"Others");
	}
	
	private static function parse_pairs($pairs,$valid_types,$mode='') {
		$pair_types = array();
		foreach($pairs as $pair) {
			list($filter_type,$filter_value) = explode("=",trim($pair));
			if($mode=='lower_filter_label')
				$filter_type = strtolower($filter_type); // Country=>country for locations
			if(!in_array($filter_type,$valid_types))
				continue;
			if(!isset($pair_types[$filter_type]))
				$pair_types[$filter_type] = array();
			$pair_types[$filter_type][] = $filter_value;
		}
		return $pair_types;
	}
	
	private static function sql_subset($arr_values) {
		$values = array();
		foreach($arr_values as $s)
			$values[] = "'$s'";
		return join(",", $values);
	}
	
			
	public static function sql_get_order_ids($settings) {
		//$settings['product_categories'] = array(119);
		//$settings['products'] = array(4554);
		//$settings['shipping_locations'] = array("city=cityS","city=alex","postcode=12345");
		//$settings['product_attributes'] = array("pa_material=glass");
		return self::sql_get_order_ids_Ver1($settings);
	}

	
	public static function sql_get_order_ids_Ver1($settings) {
		global $wpdb;
		
		// deep level !
		

		//custom taxonomies
		$taxonomy_where = "";
		if($settings['product_taxonomies']) {
			$attrs = self::get_product_taxonomies();
			$names2fields = array_flip($attrs);
			$filters = self::parse_pairs($settings['product_taxonomies'], $attrs );
			//print_r($filters );die();
			foreach($filters as $label=>$values) {
				$field = $names2fields[$label];
				$values = self::sql_subset($values);
				if($values) {
					$taxonomy_where_object_id = $taxonomy_where? "AND object_id IN ($taxonomy_where)" : "";
					$taxonomy_where = "(SELECT  object_id FROM {$wpdb->term_relationships} AS {$field}_rel 
						INNER JOIN {$wpdb->term_taxonomy} AS {$field}_cat ON {$field}_cat.term_taxonomy_id = {$field}_rel.term_taxonomy_id
						WHERE {$field}_cat.term_id IN (SELECT term_id FROM {$wpdb->terms} WHERE name IN($values) ) $taxonomy_where_object_id 
					)";
				}	
			}
		}

		$product_category_where = $taxonomy_where ;
		if($settings['product_categories']) {
			$cat_ids = array(0);
			foreach($settings['product_categories'] as $cat_id) {
				$cat_ids[] = $cat_id;
				foreach(get_term_children( $cat_id, 'product_cat') as $child_id)
					$cat_ids[] = $child_id;
			}
			$cat_ids = join(',',$cat_ids);
			$taxonomy_where_object_id = $taxonomy_where? "AND object_id IN ($taxonomy_where)" : "";
			$product_category_where = "(SELECT  DISTINCT object_id FROM {$wpdb->term_relationships} AS product_in_cat 
						LEFT JOIN {$wpdb->term_taxonomy} AS product_category ON product_category.term_taxonomy_id = product_in_cat.term_taxonomy_id
						WHERE product_category.term_id IN ($cat_ids) $taxonomy_where_object_id 
					)";
		}
		
		
		// deep level still
		$product_where = '';
		if($settings['products']) {
			$values = self::sql_subset($settings['products']);
			if($values) {
				$product_where = "($values)";
				$product_category_where = "";
			}	
		}
		if($product_category_where)
			$product_where = $product_category_where;
		
		
		$wc_order_items_meta = "{$wpdb->prefix}woocommerce_order_itemmeta";
		$left_join_order_items_meta = $order_items_meta_where = array();
		
		// filter by product
		if($product_where) {
			$left_join_order_items_meta[] = "LEFT JOIN $wc_order_items_meta  AS orderitemmeta_product ON orderitemmeta_product.order_item_id = order_items.order_item_id";
			$order_items_meta_where[] = " (orderitemmeta_product.meta_key IN ('_variation_id', '_product_id')  AND orderitemmeta_product.meta_value IN $product_where)";
		}
		
		//by attrbutes in woocommerce_order_itemmeta
		if($settings['product_attributes']) {
			$attrs = self::get_product_attributes();
			$names2fields = array_flip($attrs);
			$filters = self::parse_pairs($settings['product_attributes'], $attrs );
			foreach($filters as $label=>$values) {
				$field = $names2fields[$label];
				$values = self::sql_subset($values);
				if($values) {
					$left_join_order_items_meta[] = "LEFT JOIN $wc_order_items_meta  AS `orderitemmeta_{$field}` ON `orderitemmeta_{$field}`.order_item_id = order_items.order_item_id";
					$order_items_meta_where[] = " (`orderitemmeta_{$field}`.meta_key='$field'  AND `orderitemmeta_{$field}`.meta_value IN  ($values) ) ";
				}	
			}
		}
		
		$order_items_meta_where= join(" AND ",$order_items_meta_where);
		if($order_items_meta_where)
			$order_items_meta_where= " AND ". $order_items_meta_where;
		$left_join_order_items_meta= join("  ",$left_join_order_items_meta);

		
		// final sql from WC tables
		$order_items_where = "";
		if($order_items_meta_where) {
			$order_items_where = " AND ID IN (SELECT DISTINCT order_id FROM {$wpdb->prefix}woocommerce_order_items as order_items 
				$left_join_order_items_meta
				WHERE order_item_type='line_item' $order_items_meta_where )";
		}
		
		
		// pre top 
		$left_join_order_meta = $order_meta_where = "";
		if($settings['shipping_locations']) {
			$left_join_order_meta = $order_meta_where = array();
			$filters = self::parse_pairs($settings['shipping_locations'], array('city','state','postcode','country'), 'lower_filter_label');
			$loc_where = array();
			foreach($filters as $field=>$values) {
				$values = self::sql_subset($values);
				if($values) {
					$left_join_order_meta[] = "LEFT JOIN {$wpdb->postmeta} AS ordermeta_{$field} ON ordermeta_{$field}.post_id = orders.ID";
					$order_meta_where [] = " (ordermeta_{$field}.meta_key='_shipping_$field'  AND ordermeta_{$field}.meta_value in ($values)) ";
				}	
			}
			$order_meta_where = join(" AND ",$order_meta_where );
			if($order_meta_where )
				$order_meta_where = " AND ". $order_meta_where ;
			$left_join_order_meta  = join("  ",$left_join_order_meta );
		}
		
		
		//top_level
		$where = array(1);
		if($settings['from_date']) {
			$from_date = date('Y-m-d',strtotime($settings['from_date']));
			if($from_date)
				$where[] = "orders.post_date>='$from_date 00:00:01'";
		}	
		if($settings['to_date']) {
			$to_date = date('Y-m-d',strtotime($settings['to_date']));
			if($to_date)
				$where[] = "orders.post_date<='$to_date 23:59:59'";
		}	
		if($settings['statuses']) {
			$values = self::sql_subset($settings['statuses']);
			if($values)
				$where[] = "orders.post_status in ($values)";
		}	
		$order_sql = join(" AND ",$where);
		
		$sql="SELECT ID as order_id FROM {$wpdb->posts} AS orders
			{$left_join_order_meta}
			WHERE orders.post_type='shop_order' AND $order_sql $order_meta_where $order_items_where";
		return $sql;
	}
	
	
	public static function sql_get_order_ids_Ver2($settings) {
		global $wpdb;
		
		// top meta 
		$left_join_order_meta = $order_meta_where = "";
		if($settings['shipping_locations']) {
			$left_join_order_meta = $order_meta_where = array();
			$filters = self::parse_pairs($settings['shipping_locations'], array('city','state','postcode','country'), 'lower_filter_label');
			$loc_where = array();
			foreach($filters as $field=>$values) {
				$values = self::sql_subset($values);
				if($values) {
					$left_join_order_meta[] = "LEFT JOIN {$wpdb->postmeta} AS ordermeta_{$field} ON ordermeta_{$field}.post_id = orders.ID";
					$order_meta_where [] = " (ordermeta_{$field}.meta_key='_shipping_$field'  AND ordermeta_{$field}.meta_value in ($values)) ";
				}	
			}
			$order_meta_where = join(" AND ",$order_meta_where );
			if($order_meta_where )
				$order_meta_where = " AND ". $order_meta_where ;
			$left_join_order_meta  = join("  ",$left_join_order_meta );
		}
		
		//top level
		$where = array();
		if($settings['from_date']) {
			$from_date = date('Y-m-d',strtotime($settings['from_date']));
			if($from_date)
				$where[] = "orders.post_date>='$from_date 00:00:01'";
		}	
		if($settings['to_date']) {
			$to_date = date('Y-m-d',strtotime($settings['to_date']));
			if($to_date)
				$where[] = "orders.post_date<='$to_date 23:59:59'";
		}	
		if($settings['statuses']) {
			$values = self::sql_subset($settings['statuses']);
			if($values)
				$where[] = "orders.post_status in ($values)";
		}	
		$order_where = join(" AND ",$where);
		if($order_where)
			$order_where  = " AND " . $order_where;
		$order_sql = "SELECT ID as order_id FROM {$wpdb->posts} AS orders
			{$left_join_order_meta}
			WHERE orders.post_type='shop_order' $order_where $order_meta_where"; 
			
		if(empty($settings['product_categories']) AND empty($settings['products']) AND empty($settings['product_attributes']))	
			return $order_sql;
			
			
		// deep level !
		$product_category_where = '';
		if($settings['product_categories']) {
			$cat_ids = array(0);
			foreach($settings['product_categories'] as $cat_id) {
				$cat_ids[] = $cat_id;
				foreach(get_term_children( $cat_id, 'product_cat') as $child_id)
					$cat_ids[] = $child_id;
			}
			$cat_ids = join(',',$cat_ids);
			$product_category_where = "(SELECT object_id FROM {$wpdb->term_relationships} AS product_in_cat 
						LEFT JOIN {$wpdb->term_taxonomy} AS product_category ON product_category.term_taxonomy_id = product_in_cat.term_taxonomy_id
						WHERE product_category.term_id IN ($cat_ids)
					)";
		}
		
		// deep level still
		$product_where = '';
		if($settings['products']) {
			$values = self::sql_subset($settings['products']);
			if($values) {
				$product_where = "($values)";
				$product_category_where = "";
			}	
		}
		if($product_category_where)
			$product_where = $product_category_where;
		
		
		$wc_order_items_meta = "{$wpdb->prefix}woocommerce_order_itemmeta";
		$left_join_order_items_meta = $order_items_meta_where = array();
		
		// filter by product
		if($product_where) {
			$left_join_order_items_meta[] = "LEFT JOIN $wc_order_items_meta  AS orderitemmeta_product ON orderitemmeta_product.order_item_id = order_items.order_item_id";
			$order_items_meta_where[] = " (orderitemmeta_product.meta_key IN ('_variation_id', '_product_id')  AND orderitemmeta_product.meta_value IN $product_where)";
		}
		
		//by attrbutes in woocommerce_order_itemmeta
		if($settings['product_attributes']) {
			$attrs = self::get_product_attributes();
			$names2fields = array_flip($attrs);
			$filters = self::parse_pairs($settings['product_attributes'], $attrs );
			foreach($filters as $label=>$values) {
				$field = $names2fields[$label];
				$values = self::sql_subset($values);
				if($values) {
					$left_join_order_items_meta[] = "LEFT JOIN $wc_order_items_meta  AS `orderitemmeta_{$field}` ON `orderitemmeta_{$field}`.order_item_id = order_items.order_item_id";
					$order_items_meta_where[] = " (`orderitemmeta_{$field}`.meta_key='$field'  AND `orderitemmeta_{$field}`.meta_value IN  ($values) ) ";
				}	
			}
		}
		
		$order_items_meta_where= join(" AND ",$order_items_meta_where);
		if($order_items_meta_where)
			$order_items_meta_where= " AND ". $order_items_meta_where;
		$left_join_order_items_meta= join("  ",$left_join_order_items_meta);

		// final sql from WC tables
		$order_items_where = "";
		if($order_items_meta_where) {
			$sql = "SELECT DISTINCT order_id FROM {$wpdb->prefix}woocommerce_order_items as order_items 
				$left_join_order_items_meta
				WHERE order_item_type='line_item' AND order_id IN ($order_sql)  $order_items_meta_where";
		}
		else
			$sql = $order_sql;
			
		return $sql;
	}
	
	public static function prepare_for_export() {
		self::$statuses = wc_get_order_statuses();
		self::$countries = WC()->countries->countries;
	}
	
	public static function get_max_order_items($type,$ids) {
		global $wpdb;
		
		$ids[] = 0; // for safe 
		$ids = join(",",$ids);
		
		$sql = "SELECT COUNT( * ) AS t
			FROM  `{$wpdb->prefix}woocommerce_order_items` 
			WHERE order_item_type =  '$type'
			AND order_id
			IN ( $ids) 
			GROUP BY order_id
			ORDER BY t DESC 
			LIMIT 1";
			
		$max = $wpdb->get_var($sql);
		if(!$max)	
			$max = 1;
		return $max;	
	}
	
	public static function fetch_order_coupons($order,$labels,$format,$filters_active,$get_coupon_meta,$static_vals) {
		global $wpdb;
		$coupons = array();
		foreach($order->get_items('coupon') as $item) {
			$coupon_meta = array();
			if($get_coupon_meta) {
				$recs = $wpdb->get_results($wpdb->prepare("SELECT meta_value,meta_key FROM {$wpdb->postmeta} AS meta 
					JOIN {$wpdb->posts} AS posts ON posts.ID = meta.post_id
					WHERE posts.post_title=%s",$item['name']));
				foreach($recs  as $rec)
					$coupon_meta[$rec->meta_key] = $rec->meta_value;
			}
			
			$row = array();
			foreach($labels as $field=>$label) {
				if(isset($item[$field]))
					$row[$field] = $item[$field];
				elseif($field == 'code')
					$row['code'] = $item["name"];
				elseif(isset($coupon_meta[$field]))
					$row[$field] = $coupon_meta[$field];
				elseif(isset($static_vals[$field]) )
					$row[$field] = $static_vals[$field];
				else
					$row[$field] = '';
					
				if(isset($filters_active[$field]))	
					$row[$field] = apply_filters("woe_get_order_coupon_{$format}_value_{$field}", $row[$field]);
			}
			$coupons[] = $row;
		}	
		return $coupons;
	}
	
	
	public static function fetch_order_products($order,$labels,$format,$filters_active,$static_vals) {
		$products = array();
		foreach($order->get_items() as $item) {
			$product = $order->get_product_from_item( $item );
			$item_meta = $item['item_meta'];
			$row = array();
			foreach($labels as $field=>$label) {
				if (strpos($field, '__') !== false && $taxonomies = wc_get_product_terms( $item['product_id'], substr($field, 2), array( 'fields' => 'names' ) )) {
					$row[$field] = implode(', ', $taxonomies);
				}
				elseif(isset($item_meta[$field]))
					$row[$field] = $item_meta[$field][0];
				elseif(isset($item_meta["_".$field]))// or hidden field 
					$row[$field] = $item_meta["_".$field][0];
				elseif($field == 'name')
					$row['name'] = $item["name"];
				elseif($field == 'type') 
					$row['type'] = $product->product_type;
				elseif($field == 'tags') {
					$terms = get_the_terms( $product->id, 'product_tag' );
					$row['tags'] = array();
					if($terms){
						foreach($terms as $term)
							$row['tags'][] = $term->name;
					}		
					$row['tags'] = join(",",$row['tags']);
				}	
				elseif($field == 'category') {
					$terms = get_the_terms( $product->id, 'product_cat' );
					$row['category'] = array();
					if($terms) {
						foreach($terms as $term)
							$row['category'][] = $term->name;
					}		
					$row['category'] = join(",",$row['category']);// hierarhy ???
				}	
				elseif($field == 'line_no_tax')
					$row['line_no_tax'] = $item_meta["_line_total"][0] - $item_meta["_line_tax"][0];
				elseif(isset($static_vals[$field]) )
					$row[$field] = $static_vals[$field];
				else
					$row[$field] = $product->$field;
				if(isset($filters_active[$field]))	
					$row[$field] = apply_filters("woe_get_order_product_{$format}_value_{$field}", $row[$field]);
			}
			$products[] = $row;
		}	
		return $products;
	}
	
	public static function fetch_order_data($order_id,$labels,$format,$filters_active,$csv_max,$export,$get_coupon_meta,$static_vals,$options) {
		global $wpdb;
		
		$extra_rows = array();
		$row = array();
		
		//$order_id = 390;
		
		// get order meta 
		$order_meta = array();
		$recs = $wpdb->get_results("SELECT meta_value,meta_key FROM {$wpdb->postmeta} WHERE post_id=$order_id");
		foreach($recs  as $rec)
			$order_meta[$rec->meta_key] = $rec->meta_value;
			
		// take order 
		$order = new WC_Order($order_id);
		
		if($export['products'] OR isset($labels['order']['count_unique_products']))
			$data['products'] = self::fetch_order_products($order,$labels['products'],$format,$filters_active['products'],$static_vals['products']);
		if($export['coupons'] OR isset($labels['order']['coupons_used']) )
			$data['coupons'] = self::fetch_order_coupons($order,$labels['coupons'],$format,$filters_active['coupons'],$get_coupon_meta,$static_vals['coupons']);
		
		// extra WP_User 
		if(isset($labels['order']['user_login']) OR isset($labels['order']['user_email'])) {
			$user = !empty($order_meta['_customer_user'])? get_userdata($order_meta['_customer_user']) : false;
		}
		
		$must_adjust_extra_rows = array();
		
		// fill as it must 
		foreach($labels['order'] as $field=>$label) {
			if(isset($order_meta[$field])) {
				$field_data = array();
				do_action( 'woocommerce_order_export_add_field_data', $field_data, $order_meta[ $field ], $field );
				if ( empty( $field_data ) ) {
					$field_data[ $field ] = $order_meta[ $field ];
				}
				$row = array_merge( $row, $field_data );
			}
			elseif(isset($order_meta["_".$field]))// or hidden field
				$row[$field] = $order_meta["_".$field];
			elseif($field=='order_id')
				$row['order_id'] = $order_id;
			elseif($field=='order_number')
				$row['order_number'] = $order->get_order_number();
			elseif($field=='order_status') {
				$status   = $order->get_status();
				$status   = 'wc-' === substr( $status, 0, 3 ) ? substr( $status, 3 ) : $status;
				$row['order_status'] = isset( self::$statuses[ 'wc-' . $status ] ) ? self::$statuses[ 'wc-' . $status ] : $status;
			} 
			elseif($field=='user_login' OR $field=='user_email' )
				$row[$field] = $user? $user->$field : "";
			elseif($field=='billing_full_name')
				$row[$field] = trim($order_meta["_billing_first_name"].' '.$order_meta["_billing_last_name"]);
			elseif($field=='shipping_full_name')
				$row[$field] = trim($order_meta["_shipping_first_name"].' '.$order_meta["_shipping_last_name"]);
			elseif($field=='billing_country_full')
				$row[$field] = isset(self::$countries[ $order->billing_country ]) ? self::$countries[ $order->billing_country ] : $order->billing_country;
			elseif($field=='shipping_country_full')
				$row[$field] = isset(self::$countries[ $order->shipping_country ]) ? self::$countries[ $order->shipping_country ] : $order->shipping_country;
			elseif($field=='products' OR $field=='coupons') {
				if($format=='xls' OR $format=='csv') {
					if($csv_max[$field]==1) {
						//print_r(array_values($row));die();
						// don't refill columns from parent row!
						//echo count($row)."-".(count($row)+count($labels[$field])-1)."|";
						if(@$options['populate_other_columns_product_rows'])
							$must_adjust_extra_rows = array_merge($must_adjust_extra_rows, range(count($row), count($row)+count($labels[$field])-1 ) );
						self::csv_process_multi_rows($row,$extra_rows,$data[$field],$labels[$field]);
					}	
					else
						self::csv_process_multi_cols($row,$data[$field],$labels[$field],$csv_max[$field]);
				}		
				else
					$row[$field] = $data[$field];
			}
			elseif($field=='shipping_method_title')
				$row[$field] = $order->get_shipping_method();
			elseif($field=='coupons_used')
				$row[$field] = count($data['coupons']);
			elseif($field=='count_total_items')
				$row[$field] = $order->get_item_count();// speed! replace with own counter ?
			elseif($field=='count_unique_products')
				$row[$field] = count($data['products']);
			elseif(isset($static_vals['order'][$field]) )
				$row[$field] = $static_vals['order'][$field];
			else
			{ // customer_note,order_date
				$row[$field] =$order->$field;
				//print_r($field."=".$label); echo "debug static!\n\n";
			}	
				
			if(isset($filters_active['order'][$field]))	
				$row[$field] = apply_filters("woe_get_order_{$format}_value_{$field}", $row[$field]);
		}
		
		// fill child cells 
		if($must_adjust_extra_rows AND $extra_rows) {
			$must_adjust_extra_rows = array_unique($must_adjust_extra_rows);
			$row_vals = array_values($row);
			//print_r($must_adjust_extra_rows);//die();
			//var_dump($row_vals );
			foreach($extra_rows as $id=>$extra_row) 
				foreach($row_vals as $pos=>$val) {
					//add missed columns if no coupon in 2nd row
					if(!isset($extra_rows[$id][$pos]))
						$extra_rows[$id][$pos] = $val;
					if(!in_array($pos, $must_adjust_extra_rows))
						$extra_rows[$id][$pos] = $val;
				}		
		}
		
		
		if($extra_rows) {
			array_unshift($extra_rows,$row);
		}	
		else
			$extra_rows = array($row);
		return $extra_rows;
	}
	
	
	public static function csv_process_multi_rows(&$row,&$extra_rows,$items,$labels) {
		$row_size = count($row);
		// must add one record at least, if no coupons for example
		if(empty($items)) {
			foreach($labels as $field=>$label)
				$row[] = "";
			return;	
		}
		
		foreach($items as $pos=>$data) {
			if($pos==0) { //current row
				foreach($labels as $field=>$label)
					$row[] = $data[$field];
			}	
			else {
				if(!isset($extra_rows[$pos-1])) {
					$extra_rows[$pos-1] = array_fill(0,$row_size,"");
				}	
				// if we adds 1-2 coupons after we added some products	, so $extra_rows ALREADY exists
				while(count($extra_rows[$pos-1])!=$row_size)	
					$extra_rows[$pos-1][] = "";
				foreach($labels as $field=>$label)
					$extra_rows[$pos-1][] = $data[$field];
			}
		}
	}
	
	public static function csv_process_multi_cols(&$row,$data,$labels,$csv_max) {
		for($i=0;$i<$csv_max;$i++) {
			if(empty($data[$i])) {
				foreach($labels as $field=>$label)
					$row[] = "";
			}
			else
				foreach($labels as $field=>$label)
					$row[] = $data[$i][$field];
		}
	}
}
