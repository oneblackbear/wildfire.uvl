<?
class CMSAdminUvlvehicleController extends CMSAdminUvlController{
  public $module_name = "uvlvehicle";
  public $model_class = 'WildfireUvlVehicle';
  public $display_name = "Vehicles";
  public $dashboard = false;
  public $file_tags = array('gallery image');
  public static $restricted_tree = false;

  public function events(){
    parent::events();
    if(defined("DEALERS")){
      //only show vehicles for that dealer when logged in as a dealer-based user
      WaxEvent::add("cms.model.filters", function(){
        $controller = WaxEvent::data();
        if($dealer = $controller->current_user->dealer) $controller->model->filter("dealer_id", $dealer->id);
      });

      //set dealer based on user creating the new vehicle
      WaxEvent::add("cms.save.after", function(){
        $controller = WaxEvent::data();
        $model = $controller->model;
        if(($dealer = $controller->current_user->dealer) && !$model->dealer){
          $model->dealer = $dealer;
          $model->save();
        }
      });
    }
  }

  public function import(){
    if($dealer = $this->current_user->dealer){
      $import = new WildfireUvlImport(array(
        "import_dir" => WAX_ROOT."tmp/used_import/$dealer->client_id",
        "dealer_id" => $dealer->id
      ));
      $import->import_all();
    }
    $this->redirect_to("/$this->controller");
  }

  public function ebay_export(){
    $ebay_config = array(
      "system" => "sandbox",
      "appid" => "OneBlack-5c85-4661-83b1-7b1902d54e91",
      "auth_token" => "AgAAAA**AQAAAA**aAAAAA**Q+6PUA**nY+sHZ2PrBmdj6wVnY+sEZ2PrA2dj6wFk4GhCZWHoQmdj6x9nY+seQ**4PoBAA**AAMAAA**BsCEULphN3npZa1USxpwcBaE1BgqjvedyNc23i5MBKKQkb9c+aBhMMkKvLbxplb0bRWf6QjoRRYQPTTVlwrCSyth/EzILoaUerVJg3stwqr/azxHS691XWWUwC2xT0FDdGdzZlhFm1twVT8ogANk3haTNC7cgjD8mPsadKFiaHWYDFgDGZcf1gexYtgRm3u6CMx9j21ky0D7VZvd1G35nbbVOna8bI7zfS47dvdxgV1w5TCYjym1YRd2L4Q8cgpWGUR61WL14BqzUfnRAgt0AAmlK+rQDPPYOGhyak/kuyFs0kD6boSxzKCdtYXMKlL5h8UQLchNjPmdQpui87mdNmykffPi8INWcfaffAUV9gYAulgW5j3iQwiOtb/LJJDsmHCdOa8jlWaio8kiJlgrn1EuSmu8qYVMNlFwcDfKK8Zn6XcOMteYT+g2KSriXI06G/Ysqx/dNpCxlM3a1+YqWP9zymPPVgZbE1P/sxxAK1UxavFkcr976K8wRSj+R8KzMwwet8hvzVJyY+28M2yZVvVoyp9reVFglVfN4dbAqR5i0/065kePdSb+Cbh6BmdRF+qFlPqb1zjTOtdLyaHdUXXopv0pQ3dI7VnjsGKA9ISmIV21eEEqMvGa8LsUweWzFt+wTd+DGfv8qXNEGG4fEtP3ErMby9HHhkGox7RADXJNDil/rFqt9Ze/Ob3ONJYeJY3er8TjQTT8KDuEllolFsmcKdah+A9lTHBElPTVru2qMgHGCaRSkJSgzxB+B4Fn"
    );

    $vehicles = WildfireUvlVehicle::find("all", array(
      "filter" => array(array(
        "status" => 1,
        "ebay is null" => null)),
      "limit"  => array(6)
    ));

    $total_vehicles = count($vehicles) - 1;
    $data = new stdClass;
    foreach($vehicles as $i => $vehicle) if($item_holder = $vehicle->to_ebay()){
      if(count($data->AddItemRequestContainer) == 5 || $i >= $total_vehicles){
        $results = Ebay::AddItems($ebay_config + array("data"=>$data));
        if($results->Ack == "Success"){
          foreach($results->AddItemResponseContainer as $result){
            $vehicle = new WildfireUvlVehicle($result->CorrelationID);
            $vehicle->ebay_id = $result->ItemID;
            $vehicle->save();
          }
        }else{
          WaxLog::log("error", print_r($results, 1), "ebay");
          throw new WaxException("Ebay Export Error");
        }
        $data->AddItemRequestContainer = array();
      }
      $data->AddItemRequestContainer[] = $item_holder;
    }
  }

}
?>