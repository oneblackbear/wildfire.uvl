<?
class WildfireUvlBranch extends WildfireModel{
  
  public function setup(){
    $this->define("code", "CharField", array('required'=>true)); //a unique ref - if dealer is getting a multi branch import, this code needs to match
    
  }
  
}
?>