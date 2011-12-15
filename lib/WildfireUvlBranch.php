<?
class WildfireUvlBranch extends WildfireContent{

  public function setup(){
    parent::setup();
    $this->define("code", "CharField", array('required'=>true)); //a unique ref - if dealer is getting a multi branch import, this code needs to match
    $this->define("status", "BooleanField"); //simplified version of status
    //add in address details etc
    $this->define("address_line_1", "CharField", array('group'=>'address'));
    $this->define("address_line_2", "CharField", array('group'=>'address'));
    $this->define("address_line_3", "CharField", array('group'=>'address'));
    $this->define("address_line_4", "CharField", array('group'=>'address'));
    $this->define("address_line_5", "CharField", array('group'=>'address'));
    $this->define("postcode", "CharField", array('group'=>'address'));
    $this->define("telephone", "CharField", array('group'=>'address'));
    $this->define("fax", "CharField", array('group'=>'address'));
    $this->define("email", "CharField", array('group'=>'address'));
    $this->define("opening_hours", "TextField", array('group'=>'address'));
    //coords
    $this->define("lat", "CharField", array('group'=>'advanced'));
    $this->define("lng", "CharField", array('group'=>'advanced'));
    
    //remove the date_start / date_end
    unset($this->columns['date_start'], $this->columns['date_end']);
  }

  public function scope_live(){
    return $this->filter("status", 1)->order("sort ASC, title ASC");
  }
  
  public function before_save(){
    parent::before_save();
    if($this->postcode && (!$this->lat == 0)){
      $coords = geo_locate($this->postcode, Config::get("analytics/key"));
      WaxLog::log("error", print_r($coords,1), "geo_locate");
      $this->lat = $coords['lat'];
      $this->lng = $coords['lng'];
    }
  }

}
?>