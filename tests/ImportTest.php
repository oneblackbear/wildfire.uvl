<?php



class ImportTest extends \PHPUnit_Framework_TestCase {
  
  public $csv_file = false;
  public $zip_file = false;
  public $image_file = false;
  public $image_url = false;
  
  public $test_mappings = array(
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
  );

  public function setup() {
    $this->csv_file = __DIR__."/resources/csv_test.csv";
    $this->image_file = __DIR__."/resources/image_test.jpg";
    $this->mannhein_file = __DIR__."/resources/mannhein.zip";
  }
  
  public function teardown() {
    
  }


  public function test_csv_read() {
    $this->assertTrue(is_readable($this->csv_file));
    $csv = new CSVFileImporter($this->csv_file);
    $this->assertEquals($this->csv_file, $csv->file);
  }
  
  public function test_csv_parse() {
    $csv = new CSVFileImporter($this->csv_file);
    $csv->parse();
    $this->assertEquals(count($csv->data),4);
  }
  
  public function test_csv_field_read() {
    $csv = new CSVFileImporter($this->csv_file);
    $csv->parse();
    $this->assertEquals(count($csv->fields),34);
  }
  
  public function test_csv_mappings() {
    $csv = new CSVFileImporter($this->csv_file);
    $csv->set_mappings($this->test_mappings);
    $csv->parse();
    $this->assertEquals(count($csv->parsed_data),4);
    $this->assertEquals(count($csv->parsed_data[1]),count($this->test_mappings));
  }
  
  public function test_field_handler() {
    $csv = new CSVFileImporter($this->csv_file);
    $csv->set_mappings($this->test_mappings);
    $csv->register_handler("MediaRef", function($value){
      return "test";
    });
    $csv->parse();
    $this->assertEquals($csv->parsed_data[0]["images"],"test");
  }
  
  public function test_image_file_import() {
    $image = new ImageFileImporter($this->image_file, array("title"=>"Image Test","media_class"=>"TestMedia"));
    $image->destination = __DIR__."/ran_test.jpg";
    $this->assertGreaterThan(1,strlen($image->data));
    $image->parse();
    $this->assertEquals($image->options["title"], "Image Test");
    $image->save();
    $this->assertFileExists($image->destination);
    unlink($image->destination);
    $this->assertFileNotExists($image->destination);
  }
  
  public function test_mannhein() {
    $zip = new ZipArchive;
    $archive = $zip->open($this->mannhein_file);
    $csv_file = $zip->getFromName("cars.txt");
    $tmp_csv = tempnam(sys_get_temp_dir(),"");
    file_put_contents($tmp_csv, $csv_file);
    $csv = new MannheinImporter($tmp_csv);
    $csv->parse();
    $this->assertGreaterThan(1,count($csv->parsed_data));    
    
  }
  
}





