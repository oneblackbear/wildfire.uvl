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
  
  
}
?>