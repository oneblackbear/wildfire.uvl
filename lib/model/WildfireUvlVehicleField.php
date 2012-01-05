<?
/**
 * this is to decide what is shown on the actual advert, and which should be the main featured values
 * - ie (CO<sub>2</sub> emissions, co2, 1) would make co2 column value one of the main items shown
 */
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