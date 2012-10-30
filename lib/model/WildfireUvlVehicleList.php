<?php
class WildfireUvlVehicleList extends WaxModel{

  public function setup(){
    $this->define("manufacturer", "CharField", array());
    $this->define("model", "CharField", array());
    $this->define("ebay_id", "CharField", array());
  }


  public static function find_manufacturers() {
    $model = new WildfireUvlVehicleList;
    return $model->group("manufacturer")->all();
  }
  
}
