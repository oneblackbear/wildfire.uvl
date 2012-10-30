<?php
class WildfireUvlVehicleList extends WaxModel{

  public function setup(){
    $this->define("manufacturer", "CharField", array());
    $this->define("model", "CharField", array());
    $this->define("ebay_id", "CharField", array());
  }


  public static function find_manufacturers() {
    $model = new WildfireUvlVehicleList;
    $results = $model->group("manufacturer")->all();
    $manufacturers = array();
    foreach($results as $res) $manufacturers[]=$res->manufacturer;
    return $manufacturers;
  }
  
  public static function find_models($make = false, $prefix = true) {
    $model = new WildfireUvlVehicleList;
    if($make) $model->filter("manufacturer",$make);
    $results = $model->all();
    $models = array();
    foreach($results as $res) {
      if($prefix) $models[(string)$res->model]=$res->manufacturer." - ".$res->model;
      else $models[(string)$res->model]=$res->model;
    }
    return $models;
  }
  
}
