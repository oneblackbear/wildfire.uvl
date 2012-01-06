<?
/**
 * this is to decide what columns are used for sorting
 */
class WildfireUvlVehicleSortField extends WaxModel{
  
  public function setup(){
    $this->define("title", "CharField", array('scaffold'=>true, 'required'=>true));
    $this->define("column_name", "CharField", array('scaffold'=>true, 'required'=>true));
    $this->define("direction", "CharField", array('scaffold'=>true, 'required'=>true, 'default'=>'DESC', 'widget'=>'SelectInput', 'choices'=>array('DESC'=>'Descending', 'ASC'=>'Ascending')));
  }
  
  public function before_save(){
    if(!$this->title) $this->title = "TITLE";
  }
}
?>