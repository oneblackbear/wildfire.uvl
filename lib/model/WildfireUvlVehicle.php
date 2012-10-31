<?
class WildfireUvlVehicle extends WildfireContent{
  public static $vat_options = array('N/A', 'exc VAT', 'inc Vat');

  public function setup(){
    $this->define("registration", "CharField", array('required'=>true, 'scaffold'=>true)); //reg plate
    $this->define("make", "CharField", array('required'=>true, 'scaffold'=>true, 'widget'=>"SelectInput", 'choices'=>WildfireUvlVehicleList::find_manufacturers(), 'text_choices'=>true));
    $this->define("model", "CharField", array('required'=>true, 'scaffold'=>true, 'widget'=>"SelectInput", 'choices'=>WildfireUvlVehicleList::find_models($this->make), 'text_choices'=>true));

    parent::setup();
    $this->define("status", "IntegerField", array('default'=>0, 'maxlength'=>2, "widget"=>"SelectInput", "choices"=>array(0=>"Not Live",1=>"Live"), 'scaffold'=>true, 'editable'=>true, 'label'=>"Live", 'info_preview'=>1, "tree_scaffold"=>1));
    $this->define("price", "FloatField", array('required'=>true, 'maxlength'=>'12,2', 'label'=>"Price(&pound;) - numbers only"));
    $this->define("code", "CharField", array('required'=>true, 'group'=>'extras')); //a unique ref from import

    $this->define("date_of_manufacture", "CharField", array('group'=>'extras'));
    $this->define("date_of_first_registration", "CharField", array('group'=>'extras'));

    $this->define("previous_price", "FloatField", array('maxlength'=>'12,2', 'group'=>'prices'));
    //should prices be shown ex VAT, inc VAT, or as raw price (ie new car & used commericals need vat, used normal cars dont)
    $this->define("price_has_vat", "IntegerField", array('group'=>'prices', 'label'=>'Show vehicle price inc VAT**', 'widget'=>'SelectInput', 'choices'=>self::$vat_options));
    //the vat rate to use (percentage, so 20, 17.5 etc)
    $this->define("vat_rate", "FloatField", array('label'=>'VAT rate**','group'=>'prices', 'maxlength'=>'6,2'));

    $this->define("engine_size", "CharField", array('group'=>'engine'));
    $this->define("co2", "CharField", array('group'=>'engine'));
    $this->define("mileage", "CharField", array('group'=>'engine'));

    $this->define("body_make", "CharField", array('scaffold'=>true, 'group'=>'sizes / chasis'));
    $this->define("body_model", "CharField", array('scaffold'=>true, 'group'=>'sizes / chasis'));
    $this->define("body_style", "CharField", array('group'=>'sizes / chasis'));
    $this->define("seating_capacity", "IntegerField", array('group'=>'sizes / chasis'));
    $this->define("standing_capacity", "IntegerField", array('group'=>'sizes / chasis'));
    $this->define("length", "FloatField", array('group'=>'sizes / chasis','maxlength'=>'8,2'));
    $this->define("width", "FloatField", array('group'=>'sizes / chasis','maxlength'=>'8,2'));
    $this->define("height", "FloatField", array('group'=>'sizes / chasis','maxlength'=>'8,2'));

    $this->define("colour", "CharField");


    $this->define("branches", 'ManyToManyField', array('target_model'=>'WildfireUvlBranch', 'group'=>'relationships'));
    $this->define("fuel_type", 'ManyToManyField', array('target_model'=>'WildfireUvlVehicleFuel', 'group'=>'relationships'));
    $this->define("transmission", 'ManyToManyField', array('target_model'=>'WildfireUvlVehicleTransmission', 'group'=>'relationships'));
    $this->define("features", 'ManyToManyField', array('target_model'=>'WildfireUvlVehicleFeature', 'group'=>'relationships'));
    //configurable bits for the vehicle summary - make this overwriteable on the vehicle as well
    $this->define("featured_fields", "ManyToManyField", array('target_model'=>'WildfireUvlVehicleField', 'group'=>'relationships'));

    //ability to make offers etc
    $this->define("make_an_offer", "BooleanField", array('group'=>'extras'));
    $this->define("book_a_test_drive", "BooleanField", array('group'=>'extras'));



    //matched to carweb for future use
    $this->define("original_registration_mark", "CharField", array('group'=>'extras'));
    $this->define("VIN", "CharField", array('group'=>'extras'));
    $this->define("model_range_description", "CharField", array('group'=>'extras'));
    $this->define("model_series", "CharField", array('group'=>'extras'));
    $this->define("model_variant_description", "CharField", array('group'=>'extras'));

    //remove the date_start / date_end
    $this->define("date_start", "DateTimeField", array('export'=>true, 'editable'=>false));
	$this->define("date_end", "DateTimeField", array('export'=>true, 'editable'=>false));
	$this->define("sort", "IntegerField", array('maxlength'=>3, 'default'=>0, 'widget'=>"HiddenInput", 'editable'=>false, 'group'=>false));
    unset($this->columns['view'], $this->columns['layout']);
  }


  public function url(){
    if($this->title != $this->columns['title'][1]['default']) return "used/".Inflections::to_url($this->title);
    else return false;
  }

  public function scope_live(){
    return $this->filter("status", 1)->order("sort ASC, title ASC");
  }

  public function before_save(){
    $this->date_end = $this->date_start = "1970-01-01 00:00:00"; //epoc
    if(!$this->code) $this->code = rand(1000,9999);
    parent::before_save();
  }
  
  public function humanize($column=false){
    if($column == "make" ) return $this->make;
    if($column == "model" ) return $this->model;
    return parent::humanize($column);
  }


}
