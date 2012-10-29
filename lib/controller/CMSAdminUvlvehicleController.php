<?
class CMSAdminUvlvehicleController extends CMSAdminUvlController{
  public $module_name = "uvlvehicle";
  public $model_class = 'WildfireUvlVehicle';
  public $display_name = "Vehicles";
  public $dashboard = false;
  public $file_tags = array('gallery image');
  public static $restricted_tree = false;

  public function events(){
    parent::events();
    if(defined("DEALERS")){
      //only show vehicles for that dealer when logged in as a dealer-based user
      WaxEvent::add("cms.model.filters", function(){
        $controller = WaxEvent::data();
        if($dealer = $controller->current_user->dealer) $controller->model->filter("dealer_id", $dealer->id);
      });

      //set dealer based on user creating the new vehicle
      WaxEvent::add("cms.save.after", function(){
        $controller = WaxEvent::data();
        $model = $controller->model;
        if(($dealer = $controller->current_user->dealer) && !$model->dealer){
          $model->dealer = $dealer;
          $model->save();
        }
      });
    }
  }

  public function import(){
    if($dealer = $this->current_user->dealer){
      $import = new WildfireUvlImport(array(
        "import_dir" => WAX_ROOT."tmp/used_import/$dealer->client_id",
        "dealer_id" => $dealer->id
      ));
      $import->import_all();
    }
    $this->redirect_to("/$this->controller");
  }

  public function ebaytest(){
    $ebay_config = [
      "system" => "sandbox",
      "siteid" => "2c684554-a9ce-4fbd-bd4e-cbd443a25a3c",
      "appid" => "OneBlack-5c85-4661-83b1-7b1902d54e91",
      "auth_token" => "AgAAAA**AQAAAA**aAAAAA**fH6OUA**nY+sHZ2PrBmdj6wVnY+sEZ2PrA2dj6wFk4GhCZWHoQqdj6x9nY+seQ**4PoBAA**AAMAAA**UoS/CxHRSJ05ic6010PFIcDdMOEyh29CMDElCp30jVklXttRcMoGMkD7jBtTXJY/XlAVVwyRO5c+gbHWcdpqRL66hFrSNh+HZP1kK5zRIGZAko1EK1znX3rD3buQHgBmHZkiCPtvxeXpramxVIapEP/WBMhsALaZ46VTZDcm4Lb/SaSeJZnc9OSukvDegCIc9T8dTT6HgwVrI4KhcrDz8rtXhYMW41btIDSiXl5ZyLr0fQqpx1F9PSne+HgiLNzEmhr3a6f7IQixRz48gl09GcdiMwiNFjwdP93eZfylGgPS1am92imH/0daglWl5tduA9OPIEWTAsouaOIHGuLMuMNd9t6781KEkx79hMzTXAD1tU78YIi8DftRum5c5g0xGkkMFya9E60F7l4AKLpmdJR4a5MGN4bpgDkycppPqSDlR5ISwJacevXYyOnJpHVcrKPcxIMbprmsRYbP1L3zRetwSZpbvxX4717xcKum6n4Syo1IvH4JVrF/etR6LfS5naNui4mJQQUO0UPUH5wUvovKHystTe6NVTLJ40g4mUYY9618VRqay68WL1550eT7j6rph7BdW7d+HEAal8gg56c7xe/k7d+dFI4+4oO/gW38oZL7LknX4/WbZgpwcuOKYWNzXPLVa5iSyqk+VO2d2QoWXeokISwydMuV6JMRuyTGxzjZeIXpsKgKGov6icGFckWo+mv6sPZqnmN/zJp/tJtPBCPkzWX8jVD4ylJkWlha95N1nDHIe+uIQzM+CF13"
    ];

    $item_holder = new stdClass;
    $item_holder->MessageID = "1";
    $item_holder->Item->ConditionID = "1000";
    $item_holder->Item->Site = "UK";
    $item_holder->Item->Quantity = "1";
    $item_holder->Item->StartPrice = "1.0";
    $item_holder->Item->ListingDuration = "Days_7";
    $item_holder->Item->ListingType = "FixedPriceItem";
    $item_holder->Item->DispatchTimeMax = "3";
    $item_holder->Item->ProductListingDetails->ISBN = "0439784549";
    $item_holder->Item->ProductListingDetails->IncludePrefilledItemInformation = "true";
    $item_holder->Item->ProductListingDetails->IncludeStockPhotoURL = "true";
    $item_holder->Item->ShippingDetails->ShippingType = "Flat";
    $item_holder->Item->ShippingDetails->ShippingServiceOptions->ShippingServicePriority = "1";
    $item_holder->Item->ShippingDetails->ShippingServiceOptions->ShippingService = "USPSMedia";
    $item_holder->Item->ShippingDetails->ShippingServiceOptions->ShippingServiceCost = "2.50";
    $item_holder->Item->ReturnPolicy->ReturnsAcceptedOption = "ReturnsAccepted";
    $item_holder->Item->ReturnPolicy->RefundOption = "MoneyBack";
    $item_holder->Item->ReturnPolicy->ReturnsWithinOption = "Days_30";
    $item_holder->Item->ReturnPolicy->Description = "Text description of return policy details";
    $item_holder->Item->ReturnPolicy->ShippingCostPaidByOption = "Buyer";
    $item_holder->Item->Country = "US";
    $item_holder->Item->Currency = "USD";
    $item_holder->Item->PostalCode = "95125";
    $item_holder->Item->PaymentMethods = "PayPal";
    $item_holder->Item->PayPalEmailAddress = "magicalbookseller@yahoo.com";
    // $item_holder->Item->UUID = "529c4b0f95a04d808bada0841e42f69a";
    $item_holder->Item->PictureDetails->PictureURL = "http://i1.sandbox.ebayimg.com/03/i/00/3e/60/d7_1.JPG?set_id=8800005007";

    $data = new stdClass;
    $data->AddItemRequestContainer = [$item_holder];
    $res = Ebay::AddItems($ebay_config + ["data"=>$data]);
    print_r($res); exit;
  }
}
?>