jQuery(document).ready(function(){
  
  //range sliders
  jQuery("fieldset.range_slider").each(function(){
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
      }
    });
    obj.append("<div class='range_status clearfix'><span class='range_current_val_max'>"+_max+"</span><span class='range_current_val_min'>"+_min+"</span></div>");
  });
 
  
});