var uvl = {};
jQuery(document).ready(function(){
  uvl.form = jQuery("form.vehicles_search_form");
  uvl.__vehicle_form_timer = false;
  uvl.__vehicle_form_post = function(){
          var data = uvl.form.serialize()+"&uvl=1";
          //add the loading classes
          uvl.form.addClass("vehicle_loading").removeClass("vehicle_success").removeClass("vehicle_failed");
        //call form
        jQuery.ajax({
          data:data,
          type:"get",
          url:"/uvl/vehicle_search",
          success:function(res){
            uvl.form.find(".vehicles_set").replaceWith(res);
            uvl.form.addClass("vehicle_success").removeClass("vehicle_loading").removeClass("vehicle_failed");
            jQuery(document).trigger("uvl.search_complete");
          },
          error:function(xhr,status,err){}
        });
  };
  uvl.__compound_lookup = function(select_obj){
    var first_col = select_obj.data('col'),
        value = select_obj.val(),
        sub = select_obj.closest("fieldset").next(".range_compound_end").find("select"),
        needed = sub.data("col"),
        data = {col:first_col, val:value, need:needed}
        ;
    jQuery.ajax({
      data:data,
      type:"post",
      url:"/uvl/__compound_lookup",
      success:function(res){
        sub.html(res);
      },
      error:function(xhr,status,err){}
    });
  };
  
  
  uvl.bind_selects = function() {
    uvl.form.find("select:not(.range_compound_dropdown_start), input[type=checkbox]").live("change", function(){
      clearTimeout(uvl.__vehicle_form_timer);
      uvl.__vehicle_form_timer = setTimeout(uvl.__vehicle_form_post, 800);
    });
  };
  
  uvl.initial_range_html = function() {
    uvl.form.find(".range_compound_dropdown_end").html("<option value=''>--</option>");
  };
  
  uvl.range_start_init = function() {
    uvl.form.find("select.range_compound_dropdown_start").each(function(){
      if(jQuery(this).val()) uvl.__compound_lookup(jQuery(this));
    });
  };
  
  uvl.range_change_handlers = function() {
    uvl.form.find("select.range_compound_dropdown_start").live("change", function(){
      clearTimeout(uvl.__vehicle_form_timer);
      uvl.__compound_lookup(jQuery(this));
    });
  };
  
  uvl.hide_submit = function() {
    uvl.form.find("input[type=submit]").hide();
  };
  
  uvl.setup_sliders = function(){
    uvl.form.find("fieldset.range_slider").each(function(){
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
          clearTimeout(uvl.__vehicle_form_timer);
          uvl.__vehicle_form_timer = setTimeout(uvl.__vehicle_form_post, 800);
        }
      });
      obj.append("<div class='range_status clearfix'><span class='range_current_val_max'>"+_max+"</span><span class='range_current_val_min'>"+_min+"</span></div>");
    });
  };
  
  uvl.bind_pagination = function() {
    
    jQuery(document).bind("uvl.search_complete", function() {
      uvl.form.find(".pagination_link a").click(function(e){
        uvl.form.addClass("vehicle_loading").removeClass("vehicle_success").removeClass("vehicle_failed");
        s_url = $(this).attr("href");
        $.ajax({
          type:"get",
          url:s_url,
          success:function(res){
            uvl.form.find(".vehicles_set").replaceWith(res);
            uvl.form.addClass("vehicle_success").removeClass("vehicle_loading").removeClass("vehicle_failed");
            jQuery(document).trigger("uvl.search_complete");
          },
          error:function(xhr,status,err){}
        });
        e.preventDefault();
      });
    });
    
    
  };
  
  
  uvl.init = function() {
    uvl.bind_selects();
    uvl.initial_range_html();
    uvl.range_start_init();
    uvl.range_change_handlers();
    uvl.hide_submit();
    uvl.setup_sliders();
    uvl.bind_pagination();
  }; 

  uvl.init();


  

  
});
