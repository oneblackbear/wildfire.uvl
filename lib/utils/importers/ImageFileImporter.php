<?php

/**
 *
 * Imports an image file into wildfire media
 *
 * @package wildfire.uvl
 * @author Ross Riley
 **/
 
class ImageFileImporter {
  
  public $file = false;
  public $data = false;
  public $options = array();
  public $destination = false;
  
  public $media_class = false;
  public $media_object = false;
  public $media_options = array();
  
  
  public function __construct($file, $options = array()) {
    $this->file = $file;
    $this->data = file_get_contents($this->file);
    $this->options = $options;
  }
  
  public function parse() {
    $this->media_object = new $this->options["media_class"];
    if(!isset($this->options['title']))          $this->media_options['title'] = basename($this->file);
    if(!isset($this->options['media_class']))    $this->media_options['media_class'] = 'WildfireDiskFile';
    if($this->destination) $this->options['uploaded_location'] = $this->destination;
    else $this->options['uploaded_location'] = PUBLIC_DIR. "files/".date("Y-m-W")."/";
  }
  
  public function save() {
    if(file_put_contents($this->options['uploaded_location'], $this->data)) {
      $this->options['hash'] = hash_hmac('sha1', $this->data, md5($this->data));
      $this->options['ext'] = (substr(strrchr($this->file,'.'),1));
      $this->options['file_type'] = $this->detect_mime($this->options['uploaded_location']);
    }
    return $this->media_object->update_attributes($this->options);
  }

  
  
  public function data() {
    return $this->data;
  }
  
	public function detect_mime($file) {
	  $type = exec("file --mime -b ".escapeshellarg($file));
  	return $type;
	}
  
  
}