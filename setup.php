<?

CMSApplication::register_module("uvlbranch", array("display_name"=>"Dealerships", "link"=>"/admin/uvlbranch/", 'split'=>true));

CMSApplication::register_module("uvlvehicletransmission", array("display_name"=>"Transmissions", "link"=>"/admin/uvlvehicletransmission/"));
CMSApplication::register_module("uvlvehiclefuel", array("display_name"=>"Fuel Types", "link"=>"/admin/uvlvehiclefuel/"));
CMSApplication::register_module("uvlvehiclefeaturedfield", array("display_name"=>"Advert Fields", "link"=>"/admin/uvlvehiclefeaturedfield/"));
CMSApplication::register_module("uvlvehiclesearchoptions", array("display_name"=>"Search Options", "link"=>"/admin/uvlvehiclesearchoptions/"));
CMSApplication::register_module("uvlvehiclesort", array("display_name"=>"Sort Options", "link"=>"/admin/uvlvehiclesort/"));
CMSApplication::register_module("uvlvehiclefeature", array("display_name"=>"Extra Vehicle Features", "link"=>"/admin/uvlvehiclefeature/", 'split'=>true));

CMSApplication::register_module("uvlvehicle", array("display_name"=>"Vehicles", "link"=>"/admin/uvlvehicle/",'split'=>true));


?>