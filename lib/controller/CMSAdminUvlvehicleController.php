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
}
?>