<?
class CMSAdminUvlvehicleController extends CMSAdminUvlController{
  public $module_name = "uvlvehicle";
  public $model_class = 'WildfireUvlVehicle';
  public $display_name = "Vehicles";
  public $dashboard = false;
  public $file_tags = array('gallery image');
  public static $restricted_tree = false;

  // public function events(){
  //   parent::events();
  //   WaxEvent::add("cms.model.filters", function(){
  //     $obj = WaxEvent::data();
  //     if($branches = $obj->current_user->)
  //   });
  // }

}
?>