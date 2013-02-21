<?php

class MannheinImporter extends CSVFileImporter {
  
  public $zipfile;
  public $ziparchive;
  
  public $data_filename = "cars.txt";
  public $dealer_filename = "centre.csv";
  
  
  public $dealer_locations;
    
  public $mappings = array(
    "Feed_Id"           => "dealer_id",
    "Vehicle_ID"         => "code",
    "FullRegistration"   => "registration",
    "Colour"             => "colour",
    "FuelType"           => "fuel_type",
    "Year"               => "date_of_first_registration",
    "Mileage"            => "mileage",
    "Bodytype"           => "body_style",
    "Make"               => "make",
    "Model"              => "model",
    "Variant"            => "model_variant_description",
    "EngineSize"         => "engine_size",
    "Price"              => "price",
    "Transmission"       => "transmission",
    "MediaRef"           => "images",
    "Options"            => "content",
    "Comments"           => "excerpt",
    "PictureRefs"        => "images"
  );
  
  public function __construct($zip_file) {
    ini_set('auto_detect_line_endings', true);
    $this->set_zip($zip_file);
    $this->file = $this->get_data_as_file();
  }
  
  public function set_zip($zipfile) {
    $this->zipfile = $zipfile;
    $zip = new ZipArchive;
    $this->ziparchive = new ZipArchive; 
    $this->ziparchive->open($this->zipfile);
  }
  
  public function get_data_as_file() {
    $csv_file = $this->ziparchive->getFromName("cars.txt");
    $tmp_csv = tempnam(sys_get_temp_dir(),"");
    file_put_contents($tmp_csv, $csv_file);
    return $tmp_csv;
  }

  
  public function parse() {
    $this->parse_dealer_locations();
    parent::parse();
  }
  
  public function map() {
    $this->handle_dealer_mapping();
    $this->handle_image_imports();
    parent::map();
  }


  public function parse_dealer_locations() {    
    $dealer_file = $this->ziparchive->getFromName($this->dealer_filename);
    $dealer_rows = explode("\n", $dealer_file);
    $fields = str_getcsv(array_shift($dealer_rows));
    foreach($dealer_rows as $row) {
      if(count($fields)==count(str_getcsv($row))) $dealer_maps[] = array_combine($fields, str_getcsv($row));
    }
    $this->dealer_locations = $dealer_maps;
  }
  
  public function handle_image_imports() {
    foreach($this->data as &$vehicle) {
      $images = array();
      $real_images = array();
      if(strlen($vehicle["PictureRefs"])<2) continue;
      $images = explode(",",$vehicle['PictureRefs']);
      if(count($images)) unset($vehicle["PictureRefs"]);
      foreach($images as $image) {
        $img_c = $this->ziparchive->getFromName($image);
        if(strlen($img_c)>10) $real_images[$image] = $img_c;
      }
      $vehicle["PictureRefs"] = $real_images;
    }
  }
  
  
  public function handle_dealer_mapping() {
    foreach($this->data as &$vehicle) {
      foreach($this->dealer_locations as $dealer_row) {
        if($dealer_row["PFC Dealer Id"] == $vehicle["Feed_Id"]) {
          $vehicle["Feed_Id"] = $dealer_row["Manufacturer Dealer Number"];
        }
      }
    }
  }
  
  
}