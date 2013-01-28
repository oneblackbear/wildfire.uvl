<?php
class CsvPackager{
  public static $config = array(
    "autotrader"=>array(
      "field_mapping"=>array(
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
        "PictureRefs"=>"csv_images",
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
        "Cap_Id"=>""),
      "extension"=>"txt"),
    "gforces"=>array(
      "field_mapping"=>array(
        "FullRegistration"=>"registration",
        "VIN"=>"VIN",
        "CapID"=>"",
        "CapCode"=>"",
        "StockID"=>"id",
        "FeedID"=>"dealer_id",
        "Make"=>"make",
        "Model"=>"model",
        "Variant"=>"",
        "Transmission"=>"transmission",
        "BodyStyle"=>"body_style",
        "FuelType"=>"fuel_type",
        "EngineSizeLtrs"=>"",
        "EngineSizeCC"=>"",
        "Doors"=>"",
        "Price"=>"price",
        "OldPrice"=>"previous_price",
        "OfferPrice"=>"",
        "Owners"=>"",
        "Mileage"=>"mileage",
        "ManufacturerExteriorColour"=>"",
        "ExteriorColourGeneric"=>"colour",
        "InteriorColour"=>"",
        "InternalTrim"=>"",
        "ExternalTrim"=>"",
        "StandardEquipment"=>"",
        "Description"=>"content",
        "ManagerDescription"=>"",
        "Warranty"=>"",
        "FranchiseApproved"=>"",
        "RegistrationDate"=>"date_of_first_registration",
        "ManufactureDate"=>"date_of_manufacture",
        "MOTDate"=>"",
        "ServiceDate"=>"",
        "CO2 "=>"",
        "MPG "=>"",
        "InsuranceGroup"=>"",
        "BHP "=>"",
        "isExDemo"=>"",
        "isPlusVat"=>"gforces_is_plus_vat",
        "ServiceHistory"=>"",
        "OfferDetail"=>"",
        "Featured"=>"",
        "New"=>"gforces_is_new",
        "TradePrice"=>"",
        "CustomInt"=>"",
        "CustomBool"=>"",
        "FreeText"=>"",
        "VehicleType"=>"",
        "Exports"=>"",
        "Images"=>"csv_images"),
      "extension"=>"csv",
      "filters"=>"registration != '' and registration is not null and dealer_id != '' and dealer_id is not null and mileage != '' and mileage is not null and price != '' and price is not null"));

  public static function package($format){
    if(!($config = self::$config[$format])) throw WaxException("format required");
    WaxEvent::add("uvl.vehicle.filters", function() use($config){
      $model = WaxEvent::data();
      $model->filter($config["filters"]);
    });
    $image_export_path = CACHE_DIR."export/$format/".date("c")."/";
    $export_name = "oneblackbear-".date("dmY")."-DMS14";
    mkdir($image_export_path, 0777, true);
    $data = partial("uvl/__vehicle_listing", array("paginate_vehicles_list"=>false, "image_export_path"=>$image_export_path, "field_mapping"=>$config["field_mapping"]), "csv");
    file_put_contents($image_export_path.$export_name.".".$config['extension'], $data);
    exec("cd ".$image_export_path." && zip -jm $export_name.zip *");
    $conf = Config::get("uvl_export");
    self::ftp($image_export_path, $conf[$format]);
    return 1;
  }

  public static function ftp($folder, $ftp_details){
    $conn_id = ftp_connect($ftp_details['host']);
    $login_result = ftp_login($conn_id, $ftp_details['username'], $ftp_details['password']);
    foreach(glob("$folder*") as $file) ftp_put($conn_id, basename($file), $file, FTP_BINARY);
    ftp_close($conn_id);
  }
}