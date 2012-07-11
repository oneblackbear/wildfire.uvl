<?php
class Autotrader{
  public static $fields = array(
    "Feed_Id"=>"autotrader_dealer_id",
    "Vehicle_ID"=>"id",
    "FullRegistration"=>"registration",
    "Colour"=>"colour",
    "FuelType"=>"fuel_type",
    "Year"=>"year_of_first_registration",
    "Mileage"=>"mileage",
    "Bodytype"=>"body_style",
    "Doors"=>"number_of_doors",
    "Make"=>"make",
    "Model"=>"model",
    "Variant"=>"model_variant_description",
    "EngineSize"=>"engine_size",
    "Price"=>"price",
    "Transmission"=>"transmission",
    "PictureRefs"=>"autotrader_images",
    "ServiceHistory"=>"",
    "PreviousOwners"=>"number_of_previous_owners",
    "Category"=>"",
    "FourWheelDrive"=>"",
    "Options"=>"",
    "Comments"=>"",
    "New"=>"",
    "Used"=>"",
    "Site"=>"",
    "Origin"=>"",
    "v5"=>"",
    "Condition"=>"",
    "ExDemo"=>"",
    "FranchiseApproved"=>"",
    "TradePrice"=>"",
    "TradePriceExtra"=>"",
    "ServiceHistoryText"=>"",
    "Cap_Id"=>""
  );

  public function package(){
    $image_export_path = CACHE_DIR."autotrader/".date("c")."/";
    $export_name = "oneblackbear-".date("dmY")."-DMS14";
    mkdir($image_export_path, 0777, true);
    $autotrader_data = partial("used/__vehicle_listing", array("paginate_vehicles_list"=>false, "image_export_path"=>$image_export_path), "autotrader");
    file_put_contents($image_export_path.$export_name.".txt", $autotrader_data);
    exec("cd ".$image_export_path." && zip -j ".$export_name.".zip *");
    return 1;
  }
}