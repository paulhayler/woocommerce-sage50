<?php
if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


if(!class_exists('PHPExcel'))
	include_once dirname(__FILE__) . '/../PHPExcel.php';

class WOE_Formatter_Xls extends WOE_Formatter {
	var $rows;
	
	public function __construct( $mode, $filename , $settings , $format, $labels) {
		parent::__construct( $mode, $filename , $settings , $format, $labels);
		
		if($mode !='preview') {
			fclose($this->handle);
			$this->filename = $filename;
			if(filesize($this->filename) > 0 )
				$this->objPHPExcel = PHPExcel_IOFactory::load($this->filename);
			else
				$this->objPHPExcel = new PHPExcel();
			$this->objPHPExcel->setActiveSheetIndex(0);
			$this->last_row = $this->objPHPExcel->getActiveSheet()->getHighestRow();		
		}	
	}
	
	public function start($data='') {
		if(!$this->settings['display_column_names'] OR !$data) 
			return;
			
		if($this->mode=='preview') {
			$this->rows[] = $data;
			return;
		}	
		
		foreach($data as $pos=>$text) {
			$this->objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($pos,$this->last_row,$text);
		}
		//make first bold
		$last_column = $this->objPHPExcel->getActiveSheet()->getHighestDataColumn();
		$this->objPHPExcel->getActiveSheet()->getStyle("A1:"+$last_column+"1")->getFont()->setBold(true);
		
		//rename 
		$this->objPHPExcel->getActiveSheet()->setTitle( __('Orders','woocommerce-order-export') );
		
		//adjust width for all columns
		$max_columns = PHPExcel_Cell::columnIndexFromString($last_column);
		foreach (range(0, $max_columns) as $col) {
			$this->objPHPExcel->getActiveSheet()->getColumnDimensionByColumn($col)->setAutoSize(true);                
		}			
		
		//freeze
		$this->objPHPExcel->getActiveSheet()->freezePane('A2');
		
		//save only header on init 
		$objWriter = new PHPExcel_Writer_Excel2007($this->objPHPExcel);
		$objWriter->save($this->filename);    			
	}
	
	public function output($rec) {
		if($this->has_output_filter)
			$rec = apply_filters("woe_xls_output_filter",$rec,$rec);
			
		if($this->mode=='preview') {
			$this->rows[] = $rec;	
		}
		else {
			$this->last_row++;
			foreach(array_values($rec) as $pos=>$text) {
				$this->objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($pos,$this->last_row,$text);
			}
		}	
	}
	
	public function finish() {
		if($this->mode=='preview') {
			fwrite($this->handle,'<table>');
			if(count($this->rows)<2)
				$this->rows[] = array( __('<td colspan=10><b>No results</b></td>','woocommerce-order-export') );
			foreach($this->rows as $num=>$rec) {	
				$rec = array_map(function($a){ return '<td>'.$a.'';},$rec);
				if($num==0 AND $this->settings['display_column_names'] )
					fwrite($this->handle,'<tr style="font-weight:bold"><td>'.join('</td><td>',$rec)."</td><tr>\n");
				else	
					fwrite($this->handle,'<tr><td>'.join('</td><td>',$rec)."</td><tr>\n");
			}	
			fwrite($this->handle,'</table>');
		} else {
			$objWriter = new PHPExcel_Writer_Excel2007($this->objPHPExcel);
			$objWriter->save($this->filename);    			
		}
	}
}
