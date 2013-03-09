<?php
class WildfireUvlVehicleList extends WaxModel{
  
  public static $models = false;
  public static $manufacturers = false;
  public $api = "http://v.obb.im/api/makes.json";

  public static function find_manufacturers() {
    $m_model = new WildfireUvlVehicleList;
    return $m_model->manufacturers();
  }
  
  public static function find_models($make = false, $prefix = true) {
    $m_model = new WildfireUvlVehicleList;
    return $m_model->models($make, $prefix);
  }
  
  public function manufacturers() {
    if(self::$manufacturers) return self::$manufacturers;
    
    $api_key = Config::get("uvl/vrm_api_key");
    if(!$api_key) throw new Exception("Vehicle Lookup Required API Key","Please check your configuration");
    $man_results = $this->json_url_cache($this->api."?token=".$api_key);
    $manufacturers = array();
    foreach($man_results as $res) $manufacturers[]=$res->manufacturer;
    self::$manufacturers = $manufacturers;
    return $manufacturers;
  }
  
  public function models($make = false, $prefix = true) {
    if(self::$models) return self::$models;
    
    $api_key = Config::get("uvl/vrm_api_key");
    if(!$api_key) throw new Exception("Vehicle Lookup Required API Key","Please check your configuration");    
    
    if(!$make) $model_results = $this->json_url_cache("http://v.obb.im/api/models.json"."?token=".$api_key);
    else {
      $model_results = $this->json_url_cache("http://v.obb.im/api/models/".Inflections::to_url($make).".json"."?token=".$api_key);
    }

    $all_models = array(""=>"-- SELECT --");

    foreach($model_results as $res) {
      if($prefix) $all_models[(string)$res->model]=$res->manufacturer." - ".$res->model;
      else $all_models[(string)$res->model]=$res->model;
    }
    self::$models = $all_models;
    return $all_models;
  }
  
  protected function json_url_cache($url, $lifetime=3600){
    if(class_exists("Memcache", false)){
      $store = new WaxCache("wuvl/".$url, "memcache", array("lifetime"=>$lifetime));
      if($cache = $store->get()) return json_decode(unserialize($cache));
      else{
        $result = serialize(file_get_contents($url));
        $store->set($result);
        return json_decode(unserialize($result));
      }
    }else{
      $result = serialize(file_get_contents($url));      
      return json_decode(unserialize($result));
    }
    return false;
  }
  
  
  
}
