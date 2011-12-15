<?
class WildfireUvlVehicle extends WildfireContent{
  
  public function setup(){
    parent::setup();
    $this->define("code", "CharField", array('required'=>true)); //a unique ref from import
    
    $this->define("branches", 'ManyToManyField', array('target_model'=>'WildfireUvlBranch', 'group'=>'relationships'));
    $this->define("fuels", 'ManyToManyField', array('target_model'=>'WildfireUvlVehicleFuel', 'group'=>'relationships'));
    $this->define("transmissions", 'ManyToManyField', array('target_model'=>'WildfireUvlVehicleTransmission', 'group'=>'relationships'));
    $this->define("features", 'ManyToManyField', array('target_model'=>'WildfireUvlVehicleFeature', 'group'=>'relationships'));    
    
    unset($this->columns['date_start'], $this->columns['date_end'], $this->columns['view'], $this->columns['layout']);
  }
}
?>