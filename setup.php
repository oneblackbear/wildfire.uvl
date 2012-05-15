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
    $obj->define("branches", "ManyToManyField", array('target_model'=>"WildfireUvlBranch", 'group'=>'relationships'));
  });
  //link back to the dealer
  WaxEvent::add("WildfireUvlBranch.setup", function(){
    $obj = WaxEvent::data();
    $obj->define("dealers", "ManyToManyField", array('target_model'=>"Dealer", 'group'=>'relationships'));
  });
  //create branch when saving and the id is set
  WaxEvent::add("Dealer.branch_creation", function(){
    $dealer = WaxEvent::data();
    if($dealer->primval && $dealer->title){
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
                  'date_end'      => $dealer->date_end
                  );
      if($saved = $branch->update_attributes($details)){
        $branch->dealers = $dealer;
      }
    }
  });

}


?>