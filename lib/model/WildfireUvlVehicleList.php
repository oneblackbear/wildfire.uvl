<?php
class WildfireUvlVehicleList extends WaxModel{
  
  public static $models = false;
  public static $manufacturers = false;

  public function setup(){
    $this->define("manufacturer", "CharField", array());
    $this->define("model", "CharField", array());
    $this->define("ebay_id", "CharField", array());
  }


  public static function find_manufacturers() {
    if(self::$manufacturers) return self::$manufacturers;
    $model = new WildfireUvlVehicleList;
    $results = $model->group("manufacturer")->all();
    $manufacturers = array();
    foreach($results as $res) $manufacturers[]=$res->manufacturer;
    self::$manufacturers = $manufacturers;
    return $manufacturers;
  }
  
  public static function find_models($make = false, $prefix = true) {
    if(!$make && self::$models) return self::$models;
    $model = new WildfireUvlVehicleList;
    if($make) $model->filter("manufacturer",$make);
    $results = $model->all();
    $models = array(""=>"-- SELECT --");
    foreach($results as $res) {
      if($prefix) $models[(string)$res->model]=$res->manufacturer." - ".$res->model;
      else $models[(string)$res->model]=$res->model;
    }
    ksort($models);
    if(!$make) self::$models = $models;
    return $models;
  }
  
}
