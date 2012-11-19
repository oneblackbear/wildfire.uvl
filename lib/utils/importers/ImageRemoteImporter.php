<?php

/**
 *
 * Imports an image file into wildfire media
 *
 * @package wildfire.uvl
 * @author Ross Riley
 **/
 
class ImageRemoteImporter extends ImageFileImporter {
  
  public $url = false;
  
  
  public function __construct($url, $options = array()) {
    $this->url = $url;
    $this->data = file_get_contents($this->url);
    $this->options = $options;
  }
  
  
}