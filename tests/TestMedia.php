<?php

/**
 * mock media class
 *
 *
 **/

class TestMedia {
  
  public $row = array();
  
  
  public function update_attributes($values) {
    foreach($values as $k=>$v) $this->row[$k]=$v;
  }
  
}