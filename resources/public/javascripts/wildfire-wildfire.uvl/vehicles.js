$(document).ready(function() {
  var b = $("#wildfire_uvl_vehicle_model option:selected").val();  
  $("#wildfire_uvl_vehicle_model").remoteChained("#wildfire_uvl_vehicle_make", '/uvl/model_list.json?model='+b);
});
