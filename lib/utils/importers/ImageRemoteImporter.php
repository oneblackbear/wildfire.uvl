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
  
  public function parse() {
    $model = new WildfireMedia;
    if(!$this->options['title'])          $this->options['title'] = basename($this->url);
    if(!$this->options['file_type'])      $this->options['file_type'] = File::detect_mime($file);
    if(!$this->options['media_class'])    $this->options['media_class'] = 'WildfireDiskFile';
    
    if($this->destination) $this->options['uploaded_location'] = $this->destination;
    else $this->options['uploaded_location'] = PUBLIC_DIR. "files/".date("Y-m-W")."/";
    
    $this->options['hash'] = hash_hmac('sha1', $this->data, md5($this->data));
    if(!$options['ext']) $options['ext'] = (substr(strrchr($this->file,'.'),1));

    if($saved = $model->update_attributes($options)){
      $obj = new WildfireDiskFile;
      $obj->set($saved);
    }
    return $saved;
  }

  
  
  public function data() {
    return $this->data;
  }
  
  
}