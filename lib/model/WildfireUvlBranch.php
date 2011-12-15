<?
class WildfireUvlBranch extends WildfireContent{
  public static $vat_options = array('N/A', 'exc VAT', 'inc Vat');

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

    //configurable bits for the vehicle summary - make this overwriteable on the vehicle as well
    $this->define("vehicle_fields", "ManyToManyField", array('target_model'=>'WildfireUvlVechileField', 'group'=>'relationships'));
    //show sale prices
    $this->define("vehicle_sale_prices", "BooleanField", array('label'=>'Show vehicle sale prices'));
    //should prices be shown ex VAT, inc VAT, or as raw price (ie new car & used commericals need vat, used normal cars dont)
    $this->define("vehicle_price_with_vat", "IntegerField", array('label'=>'Show vehicle price inc VAT', 'widget'=>'SelectInput', 'choices'=>self::$vat_options));
    //the vat rate to use (percentage, so 20, 17.5 etc)
    $this->define("vehicle_vat", "FloatField", array('label'=>'VAT rate'));
    //vehicles assigned to this dealer
    $this->define("vehicles", 'ManyToManyField', array('target_model'=>'WildfireUvlVehicle', 'group'=>'relationships'));
    
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