jQuery(document).ready(function(){
  var form = jQuery("form.vehicles_search_form"),
      __vehicle_form_timer = false,
      __vehicle_form_post = function(){        
          var data = form.serialize()+"&uvl=1";
            ;
        //add the loading classes
        form.addClass("vehicle_loading").removeClass("vehicle_success").removeClass("vehicle_failed");
        //call form
        jQuery.ajax({
          data:data,
          type:"get",
          url:form.attr("data-action"),
          success:function(res){
            form.find(".vehicles_set").replaceWith(res);
            form.addClass("vehicle_success").removeClass("vehicle_loading").removeClass("vehicle_failed");
          },
          error:function(xhr,status,err){
            
          }
        });
      },
      __compound_lookup = function(select_obj){
        
      }
      ;
  form.find("input[type=submit]").hide();
  //range sliders
  form.find("fieldset.range_slider").each(function(){
    var obj = jQuery(this),
        _min = parseFloat(obj.find(".range_slider_min").hide().attr("data-min")),
        _max = parseFloat(obj.find(".range_slider_max").hide().attr("data-max")),
        step = parseFloat(obj.find(".range_slider_max").attr("data-inc")),
        min = _min,
        max = _max
        ;

    obj.find(".slider").slider({
      range:true,
      min:min,
      max:max,
      values:[_min, _max],
      step:step,
      slide: function(e, ui){
        var p = jQuery(e.target).closest("fieldset.range_slider");
        p.find(".range_current_val_max").html(ui.values[1]);
        p.find(".range_current_val_min").html(ui.values[0]);
        p.find(".range_slider_min").val(ui.values[0]);
        p.find(".range_slider_max").val(ui.values[1]);  
        clearTimeout(__vehicle_form_timer);
        __vehicle_form_timer = setTimeout(__vehicle_form_post, 800);
      }
    });
    obj.append("<div class='range_status clearfix'><span class='range_current_val_max'>"+_max+"</span><span class='range_current_val_min'>"+_min+"</span></div>");
  });

  form.find("select:not(.range_compound_dropdown_start), input[type=checkbox]").live("change", function(){
    clearTimeout(__vehicle_form_timer);
    __vehicle_form_timer = setTimeout(__vehicle_form_post, 800);
  });
  
  form.find(".range_compound_dropdown_end").html("<option value=''>--</option>");

  form.find("select.range_compound_dropdown_start").live("change", function(){
    clearTimeout(__vehicle_form_timer);

  });
});