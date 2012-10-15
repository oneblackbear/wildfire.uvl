<?
class WildfireUvlController extends ApplicationController{

  public $paginate_dealership_list = false;
  public $paginate_vehicles_list = true;

  public $vehicle_class = "WildfireUvlVehicle";
  public $per_page = 5;

  //pushing back to the stack
  public function controller_global(){
    WaxEvent::add("cms.action_set", function(){
      $obj = WaxEvent::data();
      if(Request::param("uvl")) $obj->action = "vehicle_search";
    });
    WaxEvent::add("cms.cms_stack_set", function(){
      $obj = WaxEvent::data();
      array_unshift($obj->cms_stack, $obj->controller);
    });
    parent::controller_global();
    if(!$this->cms_called) $this->cms();

  }

  /**
   * VEHICLES
   */
  public function vehicle_search(){
    $this->__vehicle_listing();
    $this->use_view = "vehicle-search";
    $this->use_layout = false;
    unset($_GET['uvl']);
  }
  //small on used on the listing
  public function __vehicle_summary(){}
  //main one - view of the actual vehicle
  public function __vehicle(){}

  /**
   * handles the display, filtering, search etc of vehicles - main partial
   */
  public function __vehicle_listing(){
    $model = new $this->vehicle_class($this->cms_live_scope);
    if(!$this->vehicle_filters) $this->vehicle_filters = Request::param('vehicle');
    elseif($vehicle_filters = Request::param('vehicle')) $this->vehicle_filters = array_merge($vehicle_filters, $this->vehicle_filters);
    if(!$this->vehicle_sort) $this->vehicle_sort = Request::param('sort');
    $model = $this->__vehicle_sort( $this->__vehicle_filters($model, $this->vehicle_filters), $this->vehicle_sort);

    if($this->paginate_vehicles_list){
      if(!$this->this_page = Request::param('page')) $this->this_page = 1;
      $this->vehicles = $model->page($this->this_page, $this->per_page);
    }else $this->vehicles = $model->all();

  }
  /**
   * find min - max values for search options, custom search fields etc, partial is used in __vehicle_listing
   */
  public function __vehicle_search_options($cache=true, $return=false){
    //this could be a very slow query, lots of db look ups, so add in some caching
    // if($cache && ($cached = $this->__uvl_cache("__vehicle_search_options"))) $search_options = $cached;
    // else
    $search_options = array();

    if(!$search_options){
      $model = new WildfireUvlVehicleSearchField("sorted");
      foreach($model->all() as $search){
        $opt =  array('col'=>$search->column_name, 'title'=>$search->title, 'type'=>$search->search_type, 'inc'=>$search->increment, 'pos'=>$search->position);
        if($search->search_type == "range") $opt['range'] = $this->__vehicle_search_range_values(new $this->vehicle_class, $search->column_name);
        else $opt['options'] = $this->__vehicle_search_select_options(new $this->vehicle_class, $search->column_name);
        $search_options[$search->column_name] = $opt;
      }
    }
    $this->search_options = $search_options;
    if($return) return $this->search_options;
  }

  public function __compound_lookup(){
    $this->use_layout = false;
    $this->results = array();
    if(($col = Request::param('col')) && ($val = Request::param('val')) && ($need = Request::param('need'))){
      $model = new $this->vehicle_class($this->cms_live_scope);
      foreach($model->filter($col, $val)->group($need)->all() as $row) $this->results[] = $row->$need;
    }
  }
  /**
   * this is a range column, so we just look for the min & max values on the db, called by __vehicle_search_options
   */
  protected function __vehicle_search_range_values($model, $column){
    //find min & max of this column
    $wax_model = new WaxModel;
    $sql = "SELECT DISTINCT MIN(`$column`) as minval, MAX(`$column`) as maxval FROM ".$model->table." WHERE `$column` > 0";
    $res = $wax_model->query($sql)->fetchAll();
    return array('min'=>$res[0]['minval'], 'max'=>$res[0]['maxval']);
  }
  /**
   * this goes over a join, so we have multiple options to look at - called by __vehicle_search_options
   */
  protected function __vehicle_search_select_options($model, $join_name){
    $options = array();
    //join
    if($j_class = $model->columns[$join_name][1]['target_model']){
      $join_model = new $j_class($this->cms_content_scope);
      foreach($join_model->all() as $row) $options[] = array('title'=>$row->humanize(), 'primval'=>$row->primval);
    }else{
      //assume its a column, so grab all the versions of that col from this table
      $class = get_class($model);
      $cloned = new $class($this->cms_content_scope);
      if($cloned->columns[$join_name]) foreach($cloned->group($join_name)->all() as $item) $options[] = array('title'=>$item->$join_name, 'primval'=>urlencode($item->$join_name));
    }
    return $options;
  }
  /**
   * take the passed in filter data, work out what data type it is, call its matching function which updates the model with filters
   */
  protected function __vehicle_filters($model, $filters){
    WaxEvent::run("uvl.vehicle.filters", $model);
    //go over the filters, compare to the search and based on what type they are run code to update the model filters
    $search_options = $this->__vehicle_search_options(true, true);
    $process = array();
    foreach($filters as $key=>$val){
      if($search = $search_options[$key]) $model = $this->{"__vehicle_filter_".$search['type']}($model, $key, $filters[$key], $search_options[$key]);
    }
    return $model;
  }
  /**
   * the most basic filter, ranges are between 2 values, so set the >= / <= on the model for the relevant column
   */
  protected function __vehicle_filter_range($model, $col, $values, $search){
    if($values['min']) $model = $model->filter($col, $values['min'], ">=");
    if($values['max']) $model = $model->filter($col, $values['max'], "<=");
    return $model;
  }
  protected function __vehicle_filter_compound($model, $col, $values, $search){
    if($values) $model->filter($col, $values);
    return $model;
  }
  /**
   * more complicated, this could be either a many to many, a regular column or a foreign key
   * - for joins, its complicated, so call another function which will lookup the join table etc
   * - foreign key will need to find the real column name for the db filter by joining table & primary key from the other side
   * - otherwise its just a straight $col = $values
   */
  protected function __vehicle_filter_multiselect($model, $col,$values, $search){
    //many to many
    if($values && $model->columns[$col][1]['target_model'] && $model->columns[$col][0] == "ManyToManyField") return $this->__vehicle_filter_join($model, $col, $values, $search);
    elseif($values && $model->columns[$col][0] == "ForeignKey" && ($mc = $model->columns[$col][1]['target_model']) && ($m = new $mc) ) return $model->filter($mc->table."_".$mc->primary_key, $values);
    //otherise assume its a filter on a foreign key or a group col like body_style etc
    elseif($values) return $model->filter($col, $values);
    else return $model;
  }
  /**
   * finds all the id's from the model class that are joined to $col item that is present in $values array
   * - if $model is the vehicle, $col is "transmission" and $values is array(1,2) this will find all vehicles
   *   which are joined to transmission type 1 or 2
   */
  protected function __vehicle_filter_join($model, $col, $values, $search){
    //find the target table
    $target_class = $model->columns[$col][1]['target_model'];
    $target = new $target_class;
    $target_table = $target->table;
    $original_table = $model->table;
    //the keys for each side on the table
    $target_key = $target->table."_".$target->primary_key;
    $original_key = $model->table."_".$model->primary_key;
    //figure what the join table is called
    if($target_table < $original_table) $table = $target_table."_".$original_table;
    else $table = $original_table."_".$target_table;

    $ids = implode(",", $values);
    $query_model = new WaxModel;
    $filter_ids = array(0);
    foreach($query_model->query("SELECT `$original_key` as filterid FROM `$table` WHERE `$target_key` IN($ids)")->fetchAll() as $row) $filter_ids[] = $row['filterid'];

    return $model->filter($model->primary_key, $filter_ids);
  }
  /**
   * the normal select dropdown should only be a column or a foreign key, so pass along to multiple select version which can handle it
   */
  protected function __vehicle_filter_select($model, $col, $value, $search){
    return $this->__vehicle_filter_multiselect($model, $col, $value, $search);
  }
  /**
   * check the post data for sort value, if none is found than apply the first sort field from the db (called in __vehicle_listing)
   */
  protected function __vehicle_sort($model, $posted){
    if(!$posted && ($sort = new WildfireUvlVehicleSortField($this->cms_content_scope)) && ($first = $sort->first())) $posted = $first->primval;
    if(($info = new WildfireUvlVehicleSortField($posted)) && $info->primval) $model = $model->order($info->column_name." ".$info->direction);
    return $model;
  }

  /**
   * partial to show sort options
   */
  public function __vehicle_sort_and_pagination_options(){
    $sort = new WildfireUvlVehicleSortField($this->cms_content_scope);
    $this->sort_options = $sort->all();
    $this->posted_sort = Request::param('sort');
  }
  /**
   * DEALERSHIPS
   */

  /**
   * handle dealership lookups
   */
  public function __dealership_listing(){
    /**
     * will keep this one simple, just list all dealership branches
     */
    $model = new $this->cms_content_class($this->cms_live_scope);
    $model = $this->__dealership_filters($model, Request::param('dealership'));
    if($this->paginate_dealership_list){
      if(!$this->this_page = Request::param('page')) $this->this_page = 1;
      $this->dealerships = $model->page($this->this_page, $this->per_page);
    }else $this->dealerships = $model->all();
  }
  public function __dealership_summary(){}
  public function __dealership(){}

  protected function __dealership_filters($model, $filters){ return $model; }

  /**
   * a small cache helper for the slow queries that runs on memcached
   */
  protected function __uvl_cache($func, $lifetime=3600){
    if(class_exists("Memcache", false)){
      $store = new WaxCache("wuvl/".$func, "memcache", array("lifetime"=>$lifetime));
      if($cache = $store->get()) return unserialize($cache);
      else{
        $value = $this->$func(false, true);
        $store->set(serialize($value));
        return $value;
      }
    }
    return false;
  }
}
?>