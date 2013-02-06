<?php

/**
 *
 * @package wildfire.uvl
 * @author Ross Riley
 **/
 
class CSVFileImporter {
  
  public $file = false;
  public $data = false;
  public $fields_first = true;
  public $fields = array();
  public $mappings = array();
  public $parsed_data = array();
  public $handlers = array();
  
  
  public function __construct($file) {
    ini_set('auto_detect_line_endings', true);
    $this->file = $file;
  }
  
  public function set_mappings($map_array) {
    $this->mappings = $map_array;
  }
  
  public function parse() {
    $csv  = new SplFileObject($this->file, 'r');
    while(!$csv->eof() && ($row = $csv->fgetcsv()) ) {
      if($csv->key()==0 && $this->fields_first) $this->fields = $row;
      elseif(count($row)==count($this->fields)) $this->data[]=array_combine($this->fields, $row);
    }
    if(count($this->mappings)) $this->map();
  }
  
  public function map() {
    foreach($this->data as $row) {
      $this->parsed_data[] = $this->map_row($row);
    }
  }
  
  public function map_row($row) {
    $new_row = array();
    foreach($row as $k=>$v) {
      if(array_key_exists($k,$this->mappings)) $new_row[$this->mappings[$k]]= $this->handle($k,$v);
    }
    return $new_row;
  }
  
  public function handle($field, $value) {
    if(isset($this->handlers[$field])) return call_user_func($this->handlers[$field], $value);
    return $value;
  }
  
  public function register_handler($field, $callback) {
    $this->handlers[$field] = $callback;
  }
  
  
}