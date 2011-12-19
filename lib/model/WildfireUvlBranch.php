<?
class WildfireUvlBranch extends WildfireContent{
  public static $vat_options = array('N/A', 'exc VAT', 'inc Vat');

  public function setup(){
    $this->define("code", "CharField", array('required'=>true, 'scaffold'=>true, 'info_preview'=>true)); //a unique ref - if dealer is getting a multi branch import, this code needs to match
    parent::setup();
    $this->define("status", "IntegerField", array('default'=>0, 'maxlength'=>2, "widget"=>"SelectInput", "choices"=>array(0=>"Not Live",1=>"Live"), 'scaffold'=>true, 'label'=>"Live"));
    //add in address details etc
    $this->define("address_line_1", "CharField", array('group'=>'address', 'subgroup'=>'address'));
    $this->define("address_line_2", "CharField", array('group'=>'address', 'subgroup'=>'address'));
    $this->define("address_line_3", "CharField", array('group'=>'address', 'subgroup'=>'address'));
    $this->define("address_line_4", "CharField", array('group'=>'address', 'subgroup'=>'address'));
    $this->define("address_line_5", "CharField", array('group'=>'address', 'subgroup'=>'address'));
    $this->define("postcode", "CharField", array('group'=>'address', 'subgroup'=>'address'));
    $this->define("telephone", "CharField", array('group'=>'address', 'subgroup'=>'contact'));
    $this->define("fax", "CharField", array('group'=>'address', 'subgroup'=>'contact'));
    $this->define("email", "CharField", array('group'=>'address', 'subgroup'=>'contact'));
    $this->define("opening_hours", "TextField", array('group'=>'address', 'widget'=>"TinymceTextareaInput"));
    //coords
    $this->define("lat", "CharField", array('group'=>'address'));
    $this->define("lng", "CharField", array('group'=>'address'));

    //configurable bits for the vehicle summary - make this overwriteable on the vehicle as well
    $this->define("vehicle_featured_fields", "ManyToManyField", array('target_model'=>'WildfireUvlVehicleField', 'group'=>'relationships'));    
    //should prices be shown ex VAT, inc VAT, or as raw price (ie new car & used commericals need vat, used normal cars dont)
    $this->define("vehicle_price_has_vat", "IntegerField", array('group'=>'config', 'label'=>'Show vehicle price inc VAT', 'widget'=>'SelectInput', 'choices'=>self::$vat_options));
    //the vat rate to use (percentage, so 20, 17.5 etc)
    $this->define("vehicle_vat_rate", "FloatField", array('label'=>'VAT rate','group'=>'config', 'maxlength'=>'6,2'));
    //ability to make offers etc
    $this->define("vehicle_make_an_offer", "BooleanField", array('group'=>'config', 'default'=>1));
    $this->define("vehicle_book_a_test_drive", "BooleanField", array('group'=>'config', 'default'=>1));
    
    //vehicles assigned to this dealer
    $this->define("vehicles", 'ManyToManyField', array('target_model'=>'WildfireUvlVehicle', 'group'=>'relationships'));
    
    //remove the date_start / date_end
    $this->define("date_start", "DateTimeField", array('export'=>true, 'editable'=>false));
		$this->define("date_end", "DateTimeField", array('export'=>true, 'editable'=>false));
    unset($this->columns['view'], $this->columns['layout']);
  }

  public function url(){    
    if($this->title != $this->columns['title'][1]['default']) return "dealerships/".Inflections::to_url($this->title);
    else return false;
  }

  public function scope_live(){
    return $this->filter("status", 1)->order("sort ASC, title ASC");
  }

  public function before_save(){
    $this->date_end = $this->date_start = "1970-01-01 00:00:00"; //epoc
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