<?
class WildfireUvlVehicleField extends WaxModel{
  
  public function setup(){
    $this->define("title", "CharField", array('scaffold'=>true, 'required'=>true));
    $this->define("column_name", "CharField", array('scaffold'=>true, 'required'=>true));
    $this->define("featured", "BooleanField", array('scaffold'=>true, 'required'=>true));
  }
  
  public function before_save(){
    if(!$this->title) $this->title = "TITLE";
  }
}
?>