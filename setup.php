<?

CMSApplication::register_module("uvlbranch", array("display_name"=>"Dealerships", "link"=>"/admin/uvlbranch/", 'split'=>true));

CMSApplication::register_module("uvlvehicletransmission", array("display_name"=>"Transmissions", "link"=>"/admin/uvlvehicletransmission/"));
CMSApplication::register_module("uvlvehiclefuel", array("display_name"=>"Fuel Types", "link"=>"/admin/uvlvehiclefuel/"));
CMSApplication::register_module("uvlvehiclefeaturedfield", array("display_name"=>"Advert Fields", "link"=>"/admin/uvlvehiclefeaturedfield/"));
CMSApplication::register_module("uvlvehiclesearchoptions", array("display_name"=>"Search Options", "link"=>"/admin/uvlvehiclesearchoptions/"));
CMSApplication::register_module("uvlvehiclesort", array("display_name"=>"Sort Options", "link"=>"/admin/uvlvehiclesort/"));
CMSApplication::register_module("uvlvehiclefeature", array("display_name"=>"Extra Vehicle Features", "link"=>"/admin/uvlvehiclefeature/", 'split'=>true));

CMSApplication::register_module("uvlvehicle", array("display_name"=>"Vehicles", "link"=>"/admin/uvlvehicle/",'split'=>true));

if(defined("DEALERS")){
  //change the dealer allowed modules
  Dealer::$allowed_modules = array('home'=>array('index'=>array()),'content'=>array('index'=>array(), 'edit'=>array('details', 'media', 'google map'), 'uvlvehicle'=>array('details', 'media', 'extras', 'prices', 'engine', 'sizes / chasis')));
  //hook in to the model setup of dealer model to add a join to branches
  WaxEvent::add("Dealer.setup", function(){
    $obj = WaxEvent::data();
    $obj->define("branches", "HasManyField", array('target_model'=>"WildfireUvlBranch", 'group'=>'relationships'));
  });
  //link back to the dealer
  WaxEvent::add("WildfireUvlBranch.setup", function(){
    $obj = WaxEvent::data();
    $obj->define("dealer", "ForeignKey", array('target_model'=>"Dealer", 'group'=>'relationships'));
  });
  WaxEvent::add("WildfireUvlVehicle.setup", function(){
    $obj = WaxEvent::data();
    $obj->define("dealer", "ForeignKey", array('target_model'=>"Dealer", 'group'=>'relationships'));
  });
  //add hook on saving of the vehicle to lock it to the dealer
  WaxEvent::add("WildfireUvlVehicle.before_save", function(){
    $obj = WaxEvent::data();
    if(($user = $obj->author) && ($dealer = $user->dealer)) $obj->dealer_id = $dealer->primval;
    else $obj->dealer_id = 0; //put in a 0 to filter from the lists etc
  });
  //add in this so it will block all views of the branch & join the created user to the dealership
  WaxEvent::add("Dealer.user_creation", function(){
    $dealer = WaxEvent::data();
    $block2 = $block1 = new WildfirePermissionBlacklist;
    $block1->update_attributes(array($user->table."_id"=>$user->primval, 'class'=>'WildfireUvlBranch', 'operation'=>"tree", "value"=>"0:id"));
    $block2->update_attributes(array($user->table."_id"=>$user->primval, 'class'=>'WildfireUvlVehicle', 'operation'=>"tree", "value"=>"0:id"));
  });
  //create branch when saving and the id is set
  WaxEvent::add("Dealer.branch_creation", function(){
    $dealer = WaxEvent::data();
    if($dealer->primval && $dealer->title && ($user = $dealer->wu)){
      $branch = new WildfireUvlBranch;
      //if the join already exists
      if(($branches = $dealer->branches) && $branches->count()) $branch = $branches->first();
      //copy over the data from dealer to the new branch
      $details = array(
                  'title'         => $dealer->title,
                  'code'          => $dealer->client_id,
                  'status'        => $dealer->status,
                  'address_line_1'=> $dealer->address_line_1,
                  'address_line_2'=> $dealer->address_line_2,
                  'address_line_3'=> $dealer->address_line_3,
                  'address_line_4'=> $dealer->city,
                  'address_line_5'=> $dealer->county,
                  'postcode'      => $dealer->postal_code,
                  'telephone'     => $dealer->telephone,
                  'email'         => $dealer->email,
                  'opening_hours' => $dealer->opening_times,
                  'lat'           => $dealer->lat,
                  'lng'           => $dealer->lng,
                  'date_start'    => $dealer->date_start,
                  'date_end'      => $dealer->date_end,
                  'dealer_id'     => $dealer->primval
                  );
      //if worked, update permissions
      if($saved = $branch->update_attributes($details)){
        $block = new WildfirePermissionBlacklist;
        if(($found = $block->filter($user->table."_id", $user->primval)->filter('class', array('WildfireUvlBranch', 'WildfireUvlVehicle'))->filter('operation', "tree")->filter("value", "0:id")->all()) && $found->count()){
          foreach($found as $f) $f->update_attributes(array('value'=>$dealer->primval.":dealer_id"));

        }
      }
    }
  });

}


?>