<?
class CMSAdminUvlvehicleController extends CMSAdminUvlController{
  public $module_name = "uvlvehicle";
  public $model_class = 'WildfireUvlVehicle';
  public $display_name = "Vehicles";
  public $dashboard = false;
  public $file_tags = array('gallery image');
  public static $restricted_tree = false;
  public $per_page = 25;

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
      
      WaxEvent::add("cms.form.setup", function(){
        $data = WaxEvent::data();
        if($data->model->vrm_data_matched ==1) {
          $data->form->registration->editable=false;
          $data->form->make->editable=false;
        }
        //print_r($data->model->row); exit;
      });
      
      
    }
  }
  
  public function create() {
    $this->model = new WildfireUvlVehicle;
    $this->form = new WaxForm($this->model);
    if($this->form->is_posted()) {
      $vehicle = post("wildfire_uvl_vehicle");
      $fuel_type = new WildfireUvlVehicleFuel( ($vehicle["fuel"]="DIESEL") ? 1 : 2 ); 
      $transmission = new WildfireUvlVehicleTransmission( ($vehicle["transmission"]="MANUAL") ? 1 : 2 );
      unset($vehicle["transmission"]);
      $this->model->set_attributes($vehicle);
      if($res = $this->fuzzy_match_model($vehicle["make"], $vehicle["model"])) {
        $this->model->model = $res;
      }
      if(!$vehicle["price"]) $this->model->price = 0;
      $this->model->vrm_data_matched = 1;
      $this->model->title = $vehicle['make']." ".$vehicle["model"];
      $new_vehicle = $this->model->save();
      $new_vehicle->fuel_type = $fuel_type;
      $new_vehicle->transmission = $transmission;
      if($new_vehicle->id) {
        $this->add_message("Vehicle Created, please now add images and set status to live when complete!");
        $this->redirect_to("/admin/uvlvehicle/edit/".$new_vehicle->id);
      } else {
        $this->add_message("Vehicle Could not be created!", "error");
        $this->redirect_to("/admin/uvlvehicle/create/");
      }
    }
  }
  
  public function old_create() {
    parent::create();
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
  
	public function reg() {
    $this->response->add_header("Content-Type","application/json");
	  $reg = preg_replace("/[\s]*/","",get("reg"));
	  $url = "http://v.obb.im/api/get.json?token=a504de1e549c1546f69c14d4244771f2653840b5&registration=".$reg;
	  $this->use_layout = false;
	  $res = json_decode(file_get_contents($url));
    if(!$res) $this->results = array();
    else $this->results = $res;
	}
  
  protected function fuzzy_match_model($make, $model_text) {
    $models = file_get_contents("http://".$_SERVER["HTTP_HOST"]."/uvl/model_list.json?wildfire_uvl_vehicle_make=".$make);
    $model_list = json_decode($models);
    $sorted = array();

    foreach($model_list as $model) {
      if(strpos($model_text, $model)!==false) $sorted[levenshtein($model, $model_text)]=$model;
    }
    ksort($sorted);
    if(count($sorted)) return array_shift($sorted);
    return false;
  }
  
}
