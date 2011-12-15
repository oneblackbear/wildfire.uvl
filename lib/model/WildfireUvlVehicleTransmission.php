<?
class WildfireUvlVehicleTransmission extends WaxModel{
  
  public function setup(){
    $this->define("title", "CharField", array('scaffold'=>true, 'required'=>true));
    $this->define("searchable", "Boolean", array('scaffold'=>true, 'required'=>true));
  }
  
  public function before_save(){
    if(!$this->title) $this->title = "TITLE";
  }
}
?>