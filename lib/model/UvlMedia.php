<?php
use Wax\Model\Model;
/**
 * A hybrid media class that delegates
 *
 * @package default
 * @author Ross Riley
 **/
 
class UvlMedia extends Model implements SplSubject{
  
    
  public function __construct($params=null) {
    parent::__construct($params=null);
  }
  
  public function setup(){
    $this->define("id", "GuidField", array("auto"=>false));
    $this->define("title", "CharField", array());
    $this->define("content", "TextField");
    $this->define("mime_type", "CharField", array()); //thats the mime type
    $this->define("extension", "CharField", array());
    $this->define("file", "CharField", array());
    $this->define("hash", "CharField", array()); //md5 hash of file contents
    
  }
  
  
  public function save() {
    $this->before_save();
    $this->notify("before_save");
 	  foreach($this->columns as $col=>$setup) {
 	    $field = $this->get_col($col);
 	    $this->_col_names[$field->col_name] = 1; //cache column names as keys of an array for the adapter to check which cols are allowed to write
 	    $this->get_col($col)->save();
 	  }
    if($this->_create) {
      $this->notify("before_insert");
      $this->_response = parent::insert();
      $this->notify("after_insert");
    } else {
      $this->notify("before_update");
      $this->_response = parent::update();
      $this->notify("after_update");
    }
    $this->after_save();
    $this->notify("after_save");
    return $this->_response;
  }
  
  public function write() {
    $tracker = Config::get("uvl/tracker");
    if(!$trackers) throw new Exception("Incorrect Media Tracker Configuration","Media usage requires a valid service");    
    
    $mapper = new MogileFS\File\Mapper($tracker);
    $file = new MogileFS\File();
    $file->setKey($this->id);
    $file->setFile($this->file);
    $savedFile = $mapper->save($file);
  }
}
