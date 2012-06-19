<?
/**
 * this is to decide what columns are used for the search filters
 */
class WildfireUvlVehicleSearchField extends WaxModel{
  
  public function setup(){
    $this->define("title", "CharField", array('scaffold'=>true, 'required'=>true));
    $this->define("column_name", "CharField", array('scaffold'=>true, 'required'=>true));
    $this->define("search_type", "CharField", array('widget'=>'SelectInput', 'choices'=>array('multiselect'=>'multiple choice tick boxes', 'select'=>'single choice drop down', 'range'=>'min -> max range slider', 'compound'=>'Compounded drop downs (make -> model)') ));
    $this->define("increment", "FloatField", array('maxlength'=>"6,2", 'label'=>'Increment (range type only)'));
    $this->define("position", "CharField", array('maxlength'=>"10", 'label'=>'Compound position', 'widget'=>'SelectInput', 'choices'=>array(''=>"--", 'start'=>'start', 'end'=>'end')));    
    $this->define("sort", "IntegerField");
  }
  
  public function before_save(){
    if(!$this->title) $this->title = "TITLE";
  }

  public function scope_sorted(){
    $this->order("sort");
  }
}
?>