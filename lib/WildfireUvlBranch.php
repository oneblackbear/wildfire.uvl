<?
class WildfireUvlBranch extends WildfireContent{

  public function setup(){
    parent::setup();
    $this->define("code", "CharField", array('required'=>true)); //a unique ref - if dealer is getting a multi branch import, this code needs to match
    $this->define("status", "BooleanField"); //simplified version of status
    
    //remove the date_start / date_end
    unset($this->columns['date_start'], $this->columns['date_end']);
  }

  public function scope_live(){
    return $this->filter("status", 1)->order("sort ASC, title ASC");
  }

}
?>