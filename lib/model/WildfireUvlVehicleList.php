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
  
}
