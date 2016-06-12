<?php
if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

abstract class WOE_Formatter {
	var $has_output_filter;
	var $mode;
	var $settings;
	var $labels;
	var $handle;
	
	public function __construct( $mode, $filename , $settings , $format, $labels) {
		$this->has_output_filter = has_filter("woe_{$format}_output_filter");
		$this->mode = $mode;
		$this->settings = $settings;
		$this->labels = $labels;
		$this->handle = fopen($filename,'a');
		if(!$this->handle)
			throw new Exception($filename . __('can not open for output', 'woocommerce-order-export') );
	}
	
	public function start($data = '') {
	}
	
	public function output($rec) {
	}
	
	public function finish() {
		fclose($this->handle);
	}
}