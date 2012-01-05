<?
/**
 * this is for the extra random data like cd changer, central locking etc that come back in a big lump
 */
class WildfireUvlVehicleFeature extends WaxModel{
  
  public function setup(){
    $this->define("title", "CharField", array('scaffold'=>true, 'required'=>true));
  }
  
  public function before_save(){
    if(!$this->title) $this->title = "TITLE";
  }
}
?>