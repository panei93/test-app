<?php
/**
 * Application level View Helper
 *
 * This file is application-wide helper file. You can put all
 * application-wide helper-related methods here.
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.View.Helper
 * @since         CakePHP(tm) v 0.2.9
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

App::uses('AppHelper', 'View/Helper');

/**
 * Application helper
 *
 * Add your application-wide methods in the class below, your helpers
 * will inherit them.
 *
 * @package       app.View.Helper
 */
class CsvHelper extends AppHelper {
	var $delimiter = ',';
	var $enclosure = '"';
	var $filename = 'Export.csv';
	var $line = array();
	var $buffer;
	
	function CsvHelper() {
		$this->clear();
	}
	
	function clear() {
		$this->line = array();
		$this->buffer = fopen('php://temp/maxmemory:'. (5*1024*1024), 'r+');
	}
	
	function addField($value) {
		$this->line[] = $value;
	}
	
	function endRow() {
		$this->addRow($this->line);
		$this->line = array();
	}
	
	function addRow($row) {
		fputcsv($this->buffer, $row, $this->delimiter, $this->enclosure);
	}
	
	function renderHeaders() {
		header("Content-type:application/vnd.ms-excel");
		header("Content-disposition:attachment;filename=".$this->filename);
	}
	
	function setFilename($filename) {
		$this->filename = $filename;
		if (strtolower(substr($this->filename, -4)) != '.csv') {
			$this->filename .= '.csv';
		}
	}
	
	function render($outputHeaders = true, $to_encoding = null, $from_encoding = "auto") {
		if ($outputHeaders) {
			if (is_string($outputHeaders)) {
				$this->setFilename($outputHeaders);
			}
			$this->renderHeaders();
		}
		rewind($this->buffer);
		$output = stream_get_contents($this->buffer);
		if ($to_encoding) {
			$output = mb_convert_encoding($output, $to_encoding, $from_encoding);
		}
		return $this->output($output);
	}
}
