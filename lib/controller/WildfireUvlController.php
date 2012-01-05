<?
class WildfireUvlController extends ApplicationController{
  
  public $paginate_dealership_list = false;
  public $paginate_vehicle_list = true;
  
  //pushing back to the stack
  public function controller_global(){
    WaxEvent::add("cms.cms_stack_set", function(){
      $obj = WaxEvent::data();
      array_unshift($obj->cms_stack, $obj->controller);
    });
    parent::controller_global();
    if(!$this->cms_called) $this->cms();
  }


  /**
   * handles the display, filtering, search etc of vehicles
   */

  public function __vehicle_listing(){
    $model = new $this->cms_content_class($this->cms_live_scope);
    $model = $this->__vehicle_filters($model);
    if($this->paginate_vehicle_list){
      if(!$this->this_page = Request::param('page')) $this->this_page = 1;
      $this->vehicles = $model->page($this->this_page, $this->per_page);
    }else $this->vehicles = $model->all();

  }
  //small on used on the listing
  public function __vehicle_summary(){}
  //main one
  public function __vehicle(){}    

  protected function __vehicle_filters($model){
    return $model;
  }

  /**
   * handle dealership lookups
   */
  public function __dealership_listing(){
    /**
     * will keep this one simple, just list all dealership branches
     */
    $model = new $this->cms_content_class($this->cms_live_scope);
    $model = $this->__dealership_filters($model);
    if($this->paginate_dealership_list){
      if(!$this->this_page = Request::param('page')) $this->this_page = 1;
      $this->dealerships = $model->page($this->this_page, $this->per_page);
    }else $this->dealerships = $model->all();
  }
  public function __dealership_summary(){}
  public function __dealership(){}

  protected function __dealership_filters($model){ return $model; }

}
?>