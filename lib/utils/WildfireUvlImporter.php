<?php
class WildfireUvlImporter{
  
  
  // A handler object, 
  public $handler;

  function __construct($handler){
    $this->handler = $handler;
  }

  public function import_all($type = "zip"){
    $maxtime = 0;
    $file = false;
    $zips = glob($this->import_dir."/*.".$type);
    $sorted = array();
    foreach($zips as $zip) if(!stristr($zip,"done")) $sorted[filemtime($zip)] = $zip;
    $time_counter = 0;
    foreach($sorted as $k=>$zip){
      $data = array();
      if($this->import($zip,$time_counter)){
        if(is_readable($zip)){
          $cmd = "cd ".$this->import_dir."/ && mv ".basename($zip)." done-".basename($zip);
          exec($cmd);
        }
      }
    }
  }

  public function import($file,&$time_counter){
    $file_type = substr($file,-4);
    if($file_type == ".zip") $this->sort_zip($file);

    $filenames = glob($this->import_dir."/*.csv");
    $maxtime = 0;
    $filename = false;
    foreach($filenames as $file_csv){
      if(filemtime($file_csv) >= $maxtime){
        $filename = $file_csv;
        $maxtime = filemtime($file_csv);
      }
    }

    if(is_readable($filename)) {
      $data = $this->parse_csv($filename);
      $count = 0;

      $used_cars = new WildfireUvlVehicle;
      if($this->dealer_id) $used_cars->query("UPDATE `$used_cars->table` SET `status` = 0 where dealer_id = $this->dealer_id");
      else $used_cars->query("UPDATE `$used_cars->table` SET `status` = 0");

      foreach($data as $import_id => $bulk) {
        $car = new WildfireUvlVehicle;
        if($this->dealer_id) $existing = $car->filter(array("code"=>$this->dealer_id."_".$bulk[$this->mappings[$this->mapping]["primary_key"]]))->first();
        else $existing = $car->filter(array("code"=> $bulk[$this->mappings[$this->mapping]["primary_key"]]))->first();

        if($existing->primval) {
          $used_car = $existing;
        }else{
          $used_car = new WildfireUvlVehicle;
        }

        foreach($this->mappings[$this->mapping]['fields'] as $from => $to){   
          if($to) {
            if(!in_array($to, array("images","fuel_type","transmission"))){
              $used_car->$to = $bulk[$from];
            // }elseif(in_array($to, array("fuel_type","transmission"))){
            //   $choices = array_flip($used_car->columns[$to][1]["choices"]);
            //   $used_car->$to = $choices[ucwords(strtolower($bulk[$from]))];
            }
          }
        }

        if($this->dealer_id){
          $used_car->dealer_id = $this->dealer_id;
          $used_car->code = $this->dealer_id . "_" .$used_car->code;
        }
        $used_car->status = 1;
        $used_car->date_start = date("Y-m-d H:i:s");
        $used_car->title = $used_car->make." ".$used_car->model;
        $used_car->price = floatval($used_car->price);
        
        if($used_car->save()){
          $count++; 
          //find files in export location
          $this->import_images($used_car,$time_counter);        
        }
      }//end of the import loop

      if($count){
        if($count == count($car->clear()->filter(array("status"=>array(1)))->all())){
          $unused_cars = $car->clear()->filter(array("status"=>array(0)))->all();
        }else{
          return 0;
        }

        $path = $this->import_dir."/*";
        foreach(glob($path) as $file){
          if(substr($file,-4) != ".zip") unlink($file);
        }
        return 1;
      }else{
        return 0;
      }
    }
  }
  
  public function import_local_image_to_media($image, $options = array()) {
    $model = new WildfireMedia;
    if(!$options['title'])      $options['title'] = basename($image, ".".$ext);
    if(!$options['file_type'])  $options['file_type'] = File::detect_mime($file);
    if(!$options['media_class'])  $options['media_class'] = 'WildfireDiskFile';
    if(!$options['uploaded_location'])  $options['uploaded_location'] = PUBLIC_DIR. "files/".date("Y-m-W")."/";
    if(!$options['hash']) {
      $data = file_get_contents($image);
      $options['hash'] = hash_hmac('sha1', $data, md5($data));
    }
    if(!$options['ext']) $options['ext'] = (substr(strrchr($image,'.'),1));

    if($saved = $model->update_attributes($options)){
      $obj = new WildfireDiskFile;
      $obj->set($saved);
    }
    return $saved;
    
  }
  
  public function import_images($car, &$counter){
    $files = glob($this->import_dir."/".$car->registration."*.jpg");

    if(count($files)){
      $found = array();   
      //loop around all found - dont add to db yet as we want the p prefixed image to be added first
      $folder_name = $this->get_folder($car);
      $car->media->unlink();
      foreach($files as $i => $file){
        $media = $this->import_local_image_to_media($file);
        $media->tag = "gallery image";
        $media->join_order = $i;
        $new_media_item = $media->save();
        $car->media = $new_media_item;
      }
    }
  }
  
  public function sort_zip($file){
    if(is_readable($file)){
      $cmd = "cd ".$this->import_dir."/ && unzip ".basename($file);
      exec($cmd);
      $existing = glob($this->import_dir."/*");
      $zips = glob($this->import_dir."/*.zip");
      $new = array_diff($existing, $zips);
      foreach($new as $possible){
        if(is_dir($possible) && substr_count($possible, "export") ) {
          $move = 'mv '.$possible.'/* '.$this->import_dir."/ && rm -Rf ".$possible;
          exec($move);
        }
      }
      exec("chmod -Rf 0777 ".$this->import_dir." && chown nobody -Rf ".$this->import_dir."/*");
    }
  }
  
  public function parse_csv($filename){
    $handle = fopen($filename, "r");
    $row = 1;
    $csv = array();
    $fields = array();
    while (($data = fgetcsv($handle)) !== FALSE) {
      if($row == 1) $fields = $data;
      else{
        foreach($fields as $index => $name){
          $csv[($row-1)][$name] = $data[$index];
        }
      }
      $row ++;
    }
    fclose($handle);
    return $csv;
  }
  
  public function get_folder($car){
    //make a directory for this property
    $folder_name = PUBLIC_DIR . "files/used-cars/".strtolower($car->full_registration);
    if(!is_dir($folder_name)) mkdir($folder_name); //make dir   
    if(!is_readable($folder_name) && is_dir($folder_name)) chmod($folder_name, 0777); //chmod it
    return $folder_name;
  }
  
  public function send_import_status($data){
    // $email = new Notify;
    // $email->send_import($data);
  }
}