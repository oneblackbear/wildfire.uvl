<?
/**
 * this is to decide what columns are used for the search filters
 */
class WildfireUvlVehicleSearchField extends WaxModel{
  
  public function setup(){
    $this->define("title", "CharField", array('scaffold'=>true, 'required'=>true));
    $this->define("column_name", "CharField", array('scaffold'=>true, 'required'=>true));
    $this->define("search_type", "CharField", array('widget'=>'SelectInput', 'choices'=>array('multiselect'=>'multiple choice tick boxes', 'select'=>'single choice drop down', 'range'=>'min -> max range slider') ));
  }
  
  public function before_save(){
    if(!$this->title) $this->title = "TITLE";
  }
}
?>