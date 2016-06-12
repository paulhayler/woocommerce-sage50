<?php
if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class WOE_Formatter_Xml extends WOE_Formatter {

	public function start($data = '') {
		fwrite($this->handle,'<?xml version="1.0" encoding="UTF-8"?>'."\n");
		fwrite($this->handle,"<".$this->settings['root_tag'].">\n");
	}
	
	public function output($rec) {
		$xml = new SimpleXMLElement( "<".$this->settings['order_tag']."></".$this->settings['order_tag'].">" );
		
		$labels = $this->labels['order'];
		foreach($rec as $field=>$value) {
			if(is_array($value)) {
				$childs = $xml->addChild( $labels[$field] ); // add Products
				
				if($field=="products") {
					$child_tag = $this->settings['product_tag'];
					$child_labels = $this->labels['products'];
				}	
				elseif($field=="coupons") {
					$child_tag = $this->settings['coupon_tag'];
					$child_labels = $this->labels['coupons'];
				}else {
					continue;
				}	
				
				foreach($value as $child_elements) {
					$child = $childs->addChild( $child_tag ); // add nested Product
					foreach($child_elements as $field_child=>$value_child)
						$child->$child_labels[$field_child]=$value_child;
				}
			}
			else
				$xml->$labels[$field] = $value;
		}
		
		//format it!
		$dom = dom_import_simplexml( $xml );
		$dom->ownerDocument->formatOutput = ($this->mode=='preview');
		$xml = $dom->ownerDocument->saveXML($dom->ownerDocument->documentElement);
		
		if($this->has_output_filter)
			$xml = apply_filters("woe_xml_output_filter", $xml, $rec);
		
		fwrite($this->handle,$xml."\n");
	}
	
	public function finish($data = '') {
		fwrite($this->handle,"</".$this->settings['root_tag'].">\n");
		parent::finish();
	}
}