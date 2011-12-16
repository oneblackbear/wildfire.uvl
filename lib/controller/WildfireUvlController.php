<?
class WildfireUvlController extends ApplicationController{

  //pushing back to the stack
  public function controller_global(){
    WaxEvent::add("cms.cms_stack_set", function(){
      $obj = WaxEvent::data();
      array_unshift($obj->cms_stack, $obj->controller);
    });
    parent::controller_global();
  }


  /**
   * handles the display, filtering, search etc of vehicles
   */

  public function __vehicle_listing(){}
  //small on used on the listing
  public function __vehicle_summary(){}
  //main one
  public function __vehicle(){}    

  protected function __vehicle_filters(){}

  /**
   * handle dealership lookups
   */
  public function __dealership_listing(){}
  public function __dealership_summary(){}
  public function __dealership(){}

  public function __dealership_filters(){}

}
?>