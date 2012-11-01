<?
class WildfireUvlVehicle extends WildfireContent{
  public static $vat_options = array('N/A', 'exc VAT', 'inc Vat');

  public function setup(){
    $this->define("registration", "CharField", array('required'=>true, 'scaffold'=>true)); //reg plate
    $this->define("make", "CharField", array('required'=>true, 'scaffold'=>true));
    $this->define("model", "CharField", array('required'=>true, 'scaffold'=>true));

    parent::setup();
    $this->define("status", "IntegerField", array('default'=>0, 'maxlength'=>2, "widget"=>"SelectInput", "choices"=>array(0=>"Not Live",1=>"Live"), 'scaffold'=>true, 'editable'=>true, 'label'=>"Live", 'info_preview'=>1, "tree_scaffold"=>1));
    $this->define("price", "FloatField", array('required'=>true, 'maxlength'=>'12,2'));
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

    $this->define("export_to_ebay", "BooleanField", array("group"=>"export"));
    $this->define("ebay_id", "CharField", array("editable"=>false));
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

  public function after_save(){
    if($this->export_to_ebay && !$this->ebay_id && ($data = $this->to_ebay())){
      $res = Ebay::AddItem([
        "system" => "sandbox",
        "appid" => "OneBlack-5c85-4661-83b1-7b1902d54e91",
        "auth_token" => "AgAAAA**AQAAAA**aAAAAA**Q+6PUA**nY+sHZ2PrBmdj6wVnY+sEZ2PrA2dj6wFk4GhCZWHoQmdj6x9nY+seQ**4PoBAA**AAMAAA**BsCEULphN3npZa1USxpwcBaE1BgqjvedyNc23i5MBKKQkb9c+aBhMMkKvLbxplb0bRWf6QjoRRYQPTTVlwrCSyth/EzILoaUerVJg3stwqr/azxHS691XWWUwC2xT0FDdGdzZlhFm1twVT8ogANk3haTNC7cgjD8mPsadKFiaHWYDFgDGZcf1gexYtgRm3u6CMx9j21ky0D7VZvd1G35nbbVOna8bI7zfS47dvdxgV1w5TCYjym1YRd2L4Q8cgpWGUR61WL14BqzUfnRAgt0AAmlK+rQDPPYOGhyak/kuyFs0kD6boSxzKCdtYXMKlL5h8UQLchNjPmdQpui87mdNmykffPi8INWcfaffAUV9gYAulgW5j3iQwiOtb/LJJDsmHCdOa8jlWaio8kiJlgrn1EuSmu8qYVMNlFwcDfKK8Zn6XcOMteYT+g2KSriXI06G/Ysqx/dNpCxlM3a1+YqWP9zymPPVgZbE1P/sxxAK1UxavFkcr976K8wRSj+R8KzMwwet8hvzVJyY+28M2yZVvVoyp9reVFglVfN4dbAqR5i0/065kePdSb+Cbh6BmdRF+qFlPqb1zjTOtdLyaHdUXXopv0pQ3dI7VnjsGKA9ISmIV21eEEqMvGa8LsUweWzFt+wTd+DGfv8qXNEGG4fEtP3ErMby9HHhkGox7RADXJNDil/rFqt9Ze/Ob3ONJYeJY3er8TjQTT8KDuEllolFsmcKdah+A9lTHBElPTVru2qMgHGCaRSkJSgzxB+B4Fn",
        "data" => $data]);
      if($res->Ack == "Success"){
        $this->ebay_id = $res->ItemID;
        $this->save();
      }
    }
  }

  //specs for this structure are on http://developer.ebay.com/DevZone/XML/docs/Reference/eBay/AddItems.html
  public function to_ebay(){
    if(!($dealer = $this->dealer) || !$this->title) return;
    $item_holder = new stdClass;
    $item_holder->MessageID = $this->id;
    $item_holder->Item->Title = $this->title;
    $item_holder->Item->Description = $this->content?$this->content:$this->title;
    $item_holder->Item->Site = "UK";
    $item_holder->Item->Quantity = "1";
    $item_holder->Item->StartPrice = $this->price;
    $item_holder->Item->ListingDuration = "Days_7";
    $item_holder->Item->ListingType = "FixedPriceItem";
    $item_holder->Item->ReturnPolicy->ReturnsAcceptedOption = "ReturnsNotAccepted";
    $item_holder->Item->Country = "GB";
    $item_holder->Item->Currency = "GBP";
    $item_holder->Item->PostalCode = $dealer->postal_code;
    $item_holder->Item->PaymentMethods = "CashOnPickup";
    $item_holder->Item->PrimaryCategory->CategoryID = 52636;
    return $item_holder;
  }
}
?>