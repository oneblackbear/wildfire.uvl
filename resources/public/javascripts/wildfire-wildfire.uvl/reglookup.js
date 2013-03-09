$(document).ready(function() {
  
  if($("#fetch_reg").length) $("fieldset.submit").hide();
  
	$("#fetch_reg").click(function(){
		var reg = $("#reg_lookup").val();
		$.getJSON("/admin/uvlvehicle/reg.json?reg="+reg, function(data){
			var failure = $.isEmptyObject(data);
			if(!failure) {
				var templ = $("#found_vehicle_template").render(data);
				$("#vehicle_search_results").html(templ).show();
				$("#preferences_form .current_vehicle_block").hide();
				$("#preferences_form .current_vehicle_block input,#preferences_form .current_vehicle_block select").attr("disabled",true);
				$("#reg_lookup_form").hide();
        $("fieldset.submit").show();
			} else {
				var templ = $("#no_vehicle_template").render();
				$("#vehicle_search_results").html(templ).show();
			}
		});
	});
	$("#vehicle_try_again").live("click",function(){
		$("#reg_lookup_form").show();
		$("#vehicle_search_results").hide();
    $("fieldset.submit").hide();
	});
	
	$("#vehicle_set").live("click",function(){
		$(this).parents("form").submit();
	});
  
  
});
