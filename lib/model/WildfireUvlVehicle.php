<?
class WildfireUvlVehicle extends WildfireContent{

  public function setup(){
    parent::setup();
    $this->define("code", "CharField", array('required'=>true)); //a unique ref from import
    $this->define("registration", "CharField", array('required'=>true)); //reg plate
    $this->define("make", "CharField", array('required'=>true));
    $this->define("model", "CharField", array('required'=>true));
    $this->define("price", "FloatField", array('required'=>true, 'maxlength'=>'12,2')); //reg plate
    $this->define("engine_size", "CharField");
    $this->define("colour", "CharField");

    $this->define("branches", 'ManyToManyField', array('target_model'=>'WildfireUvlBranch', 'group'=>'relationships'));
    $this->define("fuel_type", 'ManyToManyField', array('target_model'=>'WildfireUvlVehicleFuel', 'group'=>'relationships'));
    $this->define("transmission", 'ManyToManyField', array('target_model'=>'WildfireUvlVehicleTransmission', 'group'=>'relationships'));
    $this->define("features", 'ManyToManyField', array('target_model'=>'WildfireUvlVehicleFeature', 'group'=>'relationships'));

    //matched to carweb for future use
    $this->define("original_registration_mark", "CharField", array('group'=>'extras'));
    $this->define("VIN", "CharField", array('group'=>'extras'));
    $this->define("model_range_description", "CharField", array('group'=>'extras'));
    $this->define("model_series", "CharField", array('group'=>'extras'));
    $this->define("model_variant_description", "CharField", array('group'=>'extras'));
    $this->define("date_of_manufacture", "CharField", array('group'=>'extras'));
    $this->define("date_of_first_manufacture", "CharField", array('group'=>'extras'));
    $this->define("body_style", "CharField", array('group'=>'extras'));

    //remove the date_start / date_end
    $this->define("date_start", "DateTimeField", array('export'=>true, 'editable'=>false));
		$this->define("date_end", "DateTimeField", array('export'=>true, 'editable'=>false));
    unset($this->columns['view'], $this->columns['layout']);
  }
  
  public function scope_live(){
    return $this->filter("status", 1)->order("sort ASC, title ASC");
  }
  
  public function before_save(){
    $this->date_end = $this->date_start = "1970-01-01 00:00:00"; //epoc
    parent::before_save();
  }
  
}
?>