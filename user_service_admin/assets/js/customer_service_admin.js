jQuery(document).ready(function($){

  // On User notes Screen
  var site_url = window.location.href;
  var country_val = 0;
  var url_search = site_url.search("wdt_column_filter");
  $("#woocommerce_enable_country").click(function(){
    if($('#woocommerce_enable_country').is(":checked")){
      country_val ="on";
    }else{
      country_val ="off";
    }

    var data = {
      'action': 'save_country_option',
      country: country_val
    };

    // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
    $.post(ajaxurl, data, function(response) {
     
      $(".success-message").text("Option saved Successfully");
    });
    
  });

  if(url_search != -1){
    $(".user-notes-wpdatatable-wrap").show();
    $(".add-new-note-wrap").show();

      $.ajax({
              url: ajaxurl,
              type:'POST',
              dataType: 'json',
              data: {
                action: 'get_user_name_and_key',
                site_url: site_url
              },
              cache:false,
              beforeSend: function(){
                
              },
              success: function( result ) {

                $(".notes-find-user").val(result.user_key+'-'+result.user_login);
                $(".notes-for").html("Notes for "+result.user_login);
                $(".btn-add-new-note").css("display","inline-block");
              }
            });


  }

  // End of User notes Screen

  $('.status-msg').hide().html('');

  /*----- Placement Wizard Page -----*/

  if($('.btn-next-placement').hasClass('done'))
    $('.btn-next-placement').removeClass('done');

  /* ----- Multiselect unplaced affiliates ----- */
  $.ajax({
    url: ajaxurl,
    dataType:'json',
    data: {
      action: 'get_multiselect_options',
    },
    cache: false,
    success: function (result){

      var unplaced_affiliate_list = '';
      unplaced_affiliate_list = '<select size="2" style="width:310px;height:200px;">';

        $.each(result, function(arr, i){
          unplaced_affiliate_list += '<option value="'+i.user_key+'-'+i.user_login+'">'+i.user_login+' - '+i.user_key+'</option>';
        });

      unplaced_affiliate_list += '</select>';
      $('.unplaced-list').html(unplaced_affiliate_list);
    }
  });

  /* ----- On double click of Multiselect unplaced affiliates ----- */
  $(document).on('dblclick', '.affiliate-list', function(){

    var affiliate = $(this).find(":selected").val();
    var affiliate_data = affiliate.split('-');
    var affiliate_id = affiliate_data[0];

    $.ajax({
              url: ajaxurl,
              type:'POST',
              data: {
                action: 'get_selected_affiliate_node',
                step_screen: 1,
                affiliate_id: affiliate_id
              },
              cache:false,
              beforeSend: function(){
                $('.loader-wrap').css('display', 'block');
              },
              success: function( result ) {
                
                $('.loader-wrap').css('display', 'none');
                $('.selected-affiliate-wrap').html(result);
                $('.btn-next-placement').attr("disabled", false);
                
                   $(".btn-next-placement").attr('data-affiliate_id',affiliate_id);
                   $(".btn-next-placement").attr('data-affiliate_name',affiliate_data[1]);
                
              },
              complete: function(){
                $('#'+affiliate_id).addClass('position-selected');
              }
            });

  });

  /* ----- On single click of Multiselect unplaced affiliates ----- */
  $(document).on('click', '.affiliate-list', function(){

    var affiliate = $(this).find(":selected").val();
    var affiliate_data = affiliate.split('-');
    var affiliate_id = affiliate_data[0];

    $(".btn-next-placement").attr('data-affiliate_id',affiliate_id);
    $(".btn-next-placement").attr('data-affiliate_name',affiliate_data[1]);
    $('.btn-next-placement').attr("disabled", false);    

  });


  /* ----- Placement Wizard screen 1 ----- */
	//Autocomplete for Affiliate's Search
    $( ".search-affiliate" ).autocomplete({
        source: function( request, response ) {

            $.ajax({
              url: ajaxurl,
              dataType: "json",
              data: {
                action: 'search_affiliate_autocomplete_results',
                q: request.term
              },
              success: function( results ) {

                $('.error-msg').html('');
                var affiliates = [];
                $.each(results, function( arr,i ){
                    affiliates.push(i['user_key']+'-'+i['user_login']);
                });

                response( affiliates );
              }
            });

        },
        minLength: 3,
        select: function (event, ui) {
            $('.error-msg').html('');
            var affiliate = ui.item.value;
            var affiliate_data = affiliate.split('-');
            var affiliate_id = affiliate_data[0];

            $(".btn-search-affiliate").attr('data-affiliate_id',affiliate_id);
            $(".btn-search-affiliate").attr('data-affiliate_name',affiliate_data[1]);
         
        }
    });

    //On click of search affiliate button
    $(document).on('click', ".btn-search-affiliate", function(){

      var affiliate_id = $(this).attr('data-affiliate_id');      
    	var affiliate_name = $(this).attr('data-affiliate_name');      

    	$.ajax({
              url: ajaxurl,
              type:'POST',
              data: {
                action: 'get_selected_affiliate_node',
                step_screen: 1,
                affiliate_id: affiliate_id
              },
              cache:false,
              beforeSend: function(){
                $('.loader-wrap').css('display', 'block');
              },
              success: function( result ) {
              	
                $('.loader-wrap').css('display', 'none');
                $('.selected-affiliate-wrap').html(result);
                $('.btn-next-placement').attr("disabled", false);
                
                   $(".btn-next-placement").attr('data-affiliate_id',affiliate_id);
                   $(".btn-next-placement").attr('data-affiliate_name',affiliate_name);
              },
              complete: function(){
                $('#'+affiliate_id).addClass('position-selected');
              }
            });
    });

    /* ----- Placement Wizard Next button ----- */
    $(document).on('click', '.btn-next-placement', function(){

      $('.btn-next-placement').attr('data-step','step-2');
      $('.btn-next-placement').removeClass('btn-step-1');
      $('.btn-next-placement').addClass('btn-step-2');

      var affiliate_id = $(".btn-next-placement").attr('data-affiliate_id');
      var affiliate_name = $(".btn-next-placement").attr('data-affiliate_name');

      $('ul li').removeClass('active');
      $('ul li:nth-child(2)').addClass('active');
      $('.step-2-li').show().html('For <br/>'+affiliate_name);

      $('.steps').hide();
      $('.step-2').show();    
      
      $('.btn-parent-treeview').show();
      $('.btn-next-placement').attr("disabled", true);
      $('.screen-1-affiliate').html(affiliate_name+'('+affiliate_id+')');

    });

    /* ----- Placement Wizard screen 2 ----- */

    //On Screen 2 Display
    $(document).on("click", ".btn-next-placement", function(){
      

        var affiliate_id = $(".btn-step-2").attr('data-affiliate_id');
        var site_url = window.location.origin;

        if( $(".btn-next-placement").hasClass('btn-step-2') ){
        $.ajax({
              url: ajaxurl,
              type: 'POST',
              dataType: "json",
              data: {
                action: 'get_affiliate_sponsor',
                affiliate_id: affiliate_id
              },
              success: function( result ) {
                
                
                var aff_name = $('.btn-next-placement').data('affiliate_name');
                
                $('.btn-sponsor-treeview').attr('href',site_url+'/genealogy-tree-view/?trigger_tree=1&root_node='+result.sponsor_login);
                $('.btn-sponsor-treeview').html('<i class="fa fa-eye"></i> '+aff_name+' Sponsor\'s Tree View');
              }
            });

        }  

    });

    //Autocomplete for Parent Affiliate's Search
    $( ".search-parent" ).autocomplete({
        source: function( request, response ) {

            var affiliate_id = $(".btn-next-placement").attr('data-affiliate_id');

            $.ajax({
              url: ajaxurl,
              dataType: "json",
              data: {
                action: 'get_affiliate_autocomplete_results',
                q: request.term,
                affiliate_id: affiliate_id
              },
              success: function( results ) {
                
                var affiliates = [];
                $.each(results, function( arr,i ){
                    affiliates.push(i['user_key']+'-'+i['user_login']);
                });

                response( affiliates );
              }
            });

        },
        minLength: 3,
        select: function (event, ui) {

            var parent = ui.item.value;
            var parent_data = parent.split('-');
            var parent_id = parent_data[0];  
            var site_url = window.location.origin;

            $(".btn-search-parent").attr('data-parent_id',parent_id);
            $(".btn-search-parent").attr('data-parent_name',parent_data[1]);
            $('.btn-parent-treeview').attr('href',site_url+'/genealogy-tree-view/?trigger_tree=1&root_node='+parent_data[1]);
            $('.btn-parent-treeview').html('<i class="fa fa-eye"></i> '+parent_data[1]+' Tree View')
            
        }
    });

    //On click of search parent button
    $(document).on('click', ".btn-search-parent", function(){

      var parent = $('.search-parent').val();
      var parent_data = parent.split('-');
      var parent_id = parent_data[0];  
      
      $.ajax({
              url: ajaxurl,
              type:'POST',
              data: {
                action: 'get_selected_affiliate_node',
                step_screen: 2,
                affiliate_id: parent_id
              },
              cache:false,
              beforeSend: function(){
                $('.loader-wrap').css('display', 'block');
              },
              success: function( result ) {

                  $('.loader-wrap').css('display', 'none');
                  $('.steps').hide();
                  $('.step-2').show();
                  $('.selected-parent-wrap').show().html(result);
                  $('.btn-next-placement').attr("disabled", true);                 
                  $('.btn-next-placement').attr('data-parent_id',parent_id);
                  $(".btn-next-placement").attr('data-parent_name',parent_data[1]);
                  $(".btn-parent-treeview").removeClass('disabled');

              }
          });
    });

    //On click of empty node
    $(document).on('click', '.add-new', function(){      

      var parent = $('.search-parent').val();
      var parent_data = parent.split('-');
      var parent_id = parent_data[0];      

      var leg_position = 1;
      var affiliate_id = $('.btn-next-placement').attr('data-affiliate_id');

      if($(this).hasClass('add-new-left')){
        leg_position = 0;
      }
      
      /*
       * remove active class from other elements
       */
       $('.update-child-affiliate').removeClass('position-selected');
       $('.add-new').removeClass('position-selected');
       
       // add active class to currernt
       $(this).addClass('position-selected'); 
       
       /*
        * set button attributes
        */
        $(".btn-next-placement").attr('data-parent_id',parent_id);   
        $(".btn-next-placement").attr('data-leg',leg_position);   
        $(".btn-next-placement").attr('data-placement_type','for_unplaced_affiliates');
        $(".btn-next-placement").addClass('add-affiliate');     
        $(".btn-next-placement").removeClass('update-affiliate');     
        $('.btn-next-placement').attr("disabled", false); 

    });

    //Display tree structure
    function get_tree_structure(){

      var affiliate_id = $('.btn-next-placement').attr('data-affiliate_id');
      var parent_id =  $('.btn-next-placement').attr('data-parent_id');

      $.ajax({
              url: ajaxurl,
              type:'POST',
              data: {
                action: 'get_selected_affiliate_node',
                step_screen: 3,
                affiliate_id: affiliate_id,
                parent_id: parent_id,
              },
              cache:false,
              beforeSend: function(){
                $('.loader-wrap').css('display', 'block');
              },
              success: function( result ) {

                $('.loader-wrap').css('display', 'none');
                console.log('get_tree_structure<br>'+result)
                $('.steps').hide();
                $('.step-3').show();
                $('.tree-structure-wrap').show().html(result);
                $('.btn-next-placement').attr("disabled", false);               

              }
          });

    }

    //On click of any parent's child node
    $(document).on('click', '.update-child-affiliate', function(){

        //Selcted position(Affiliate will be unplaed)
        var to_be_unplaced_affiliate_id = $(this).data('affiliate_id');
        var to_be_unplaced_affiliate_name = $(this).data('affiliate_name');
        var leg_position = $(this).attr('data-leg');

        $(".btn-next-placement").attr('data-to_be_unplaced_affiliate_id',to_be_unplaced_affiliate_id); 
        $(".btn-next-placement").attr('data-to_be_unplaced_affiliate_name',to_be_unplaced_affiliate_name);
        $(".btn-next-placement").attr('data-leg',leg_position);   
   
        //Assign class only if once not selected
        $('.update-child-affiliate').removeClass('position-selected');
        $('.add-new').removeClass('position-selected');
        $(this).addClass('position-selected');   

        //To be placed (step1) Affiliate
        var affiliate_id = $('.btn-next-placement').attr('data-affiliate_id');        
         
        $(".btn-next-placement").attr('data-placement_type','for_placed_affiliates');
        $(".btn-next-placement").addClass('update-affiliate');
        $(".btn-next-placement").removeClass('add-affiliate');

        $('.btn-next-placement').attr("disabled", false); 
        //If session is set
        var session_set = $('.btn-next-placement').attr('session_set');
        if(session_set){
          affiliate_id = to_be_unplaced_affiliate_id;
          $('.btn-next-placement').attr('data-affiliate_id', affiliate_id);
        }
       
    });

    //On click of next button after selecting empty node
    $(document).on('click','.add-affiliate', function(){

      var affiliate_id = $('.btn-next-placement').attr('data-affiliate_id');
      var affiliate_name = $('.btn-next-placement').attr('data-affiliate_name');
      var parent_id =  $('.btn-next-placement').attr('data-parent_id');
      var parent_name = $(".btn-next-placement").attr('data-parent_name')
      var leg_position =  $('.btn-next-placement').attr('data-leg');
      var placement_type =  $('.btn-next-placement').attr('data-placement_type');

      place_affiliate(affiliate_id, affiliate_name, parent_id, parent_name, leg_position, placement_type, false);
    });

    //On click of next button after selecting child node of a parent
    $(document).on('click','.update-affiliate', function(){

      var affiliate_id = $('.btn-next-placement').attr('data-affiliate_id');
      var affiliate_name = $('.btn-next-placement').attr('data-affiliate_name');
      var session_set = $('.btn-next-placement').attr('session_set');

      if(session_set){
        // affiliate_id = $('.btn-next-placement').attr('data-unplaced_id_0');
        affiliate_id = $('.btn-next-placement').attr('data-to_be_unplaced_affiliate_id');

        // affiliate_name = $('.btn-next-placement').attr('data-to_be_unplaced_affiliate_name');
        affiliate_name = $('.btn-next-placement').attr('data-to_be_unplaced_affiliate_name');
      }

      var parent_id =  $('.btn-next-placement').attr('data-parent_id');
      var parent_name = $(".btn-next-placement").attr('data-parent_name')
      var leg_position =  $('.btn-next-placement').attr('data-leg');
      var placement_type =  $('.btn-next-placement').attr('data-placement_type');

      place_affiliate(affiliate_id, affiliate_name, parent_id, parent_name, leg_position, placement_type, session_set);
    });

    //Entire affiliate placement wizard logic
    function place_affiliate(affiliate_id, affiliate_name, parent_id, parent_name, leg_position, placement_type,session_set = false){
        
        console.log(affiliate_name);
        var unplaced_affiliate_id = '';
        var unplaced_affiliate_name = '';

        if(placement_type == 'for_placed_affiliates'){

          unplaced_affiliate_id = $('.btn-next-placement').attr('data-to_be_unplaced_affiliate_id');
          unplaced_affiliate_name = $('.btn-next-placement').attr('data-to_be_unplaced_affiliate_name');

        }

        $.ajax({
          url: ajaxurl,
          type:'POST',
          dataType: 'json',
          data: {
            action: 'place_affiliate',
            step_screen: 2,
            placement_type: placement_type,
            affiliate_id: affiliate_id,
            affiliate_name: affiliate_name,
            parent_id: parent_id,
            unplaced_affiliate_id: unplaced_affiliate_id,
            unplaced_affiliate_name: unplaced_affiliate_name,
            leg_position: leg_position,
          },
          cache:false,
          beforeSend: function(){
            $('.loader-wrap').css('display', 'block');
          },
          success: function( result ) {

            $('.loader-wrap').css('display', 'none');
            console.log(result);
            $('.update-message').css('display','block');
     
            if(result.valid_placement){

              $('.validation-message').html('');
              if(result.affiliate_placed){

                console.log('session:'+$('.btn-next-placement').attr('session_set'));
                
                if($('.btn-next-placement').attr('session_set') && unplaced_affiliate_name != ''  ){
                  affiliate_name = unplaced_affiliate_name;
                }

                console.log('affiliate_name:'+affiliate_name);
                console.log('unplaced_affiliate_name:'+unplaced_affiliate_name);

                $('.update-message').css('color','green').html("<h2><i class='fa fa-check'></i> Member "+affiliate_name+" placed Successfully under the Parent "+parent_name+".</h2>");
                $('.step-2-li').html('').hide();
                $('.btn-next-placement').removeClass('btn-step-2');
                $('.btn-next-placement').addClass('btn-step-3');
                $('ul li').removeClass('active');
                $('ul li:nth-child(3)').addClass('active');
                

                $('.steps').hide();
                $('.step-3').show();    
                $('.selected-parent-wrap').hide();

                  if(result.session_set){
                    $('.btn-next-placement').attr("disabled", true);
                    $('.btn-next-placement').attr('session_set',true);
                    $('.btn-next-placement').attr('affiliate_childs',result.affiliate_childs);
                    
                    // $.each(result.session_value, function(arr, i){
                    //   $('.btn-next-placement').attr('data-unplaced_id_'+arr, i.user_key);
                    // });     

                    // $('.btn-next-placement').attr('data-unplaced_username', result.to_be_placed_username);
                  }else{

                    $('.btn-next-placement').html('Done');
                    $('.btn-next-placement').addClass('done');
                    $('.btn-next-placement').removeClass('update-affiliate');
                    $('.btn-next-placement').removeClass('add-affiliate');
                    $('.btn-next-placement').attr("disabled", 'disabled');
                  }

                $('.screen-1-affiliate').html(''); 
                get_tree_structure();

              }else{
                $('.update-message').css('color','red').html("Member could not be placed.");
              }     
            }else{
              $('.validation-message').css('display','block');
              $('.validation-message').html("<h2>"+result.placement_validation+"</h2>").fadeIn(5000);
            }  

          },
          complete: function(){

              if(placement_type == 'for_placed_affiliates'){

                // var new_affiliate_id = $('.btn-next-placement').attr('data-unplaced_id_0');
                var new_affiliate_id = $('.btn-next-placement').attr('data-to_be_unplaced_affiliate_id');

                // var new_affiliate_name = $('.btn-next-placement').attr('data-unplaced_username');
                var new_affiliate_name = $('.btn-next-placement').attr('data-to_be_unplaced_affiliate_name');

                console.log(new_affiliate_name+'-'+new_affiliate_id);

                var eliminate_timer = setTimeout(function(){

                  $('ul li').removeClass('active');
                  $('ul li:nth-child(2)').addClass('active').find('span').css('display','block');
                  
                  $('.step-2-li').html('for <br>'+new_affiliate_name);

                  $('.steps').hide();
                  $('.step-2').show(); 

                  $('.btn-next-placement').attr('data-affiliate_id', new_affiliate_id);
                  $('.search-parent').val('');
                  
                  $('.screen-1-affiliate').html(new_affiliate_name+'('+new_affiliate_id+')');
                  console.log('for <br>'+new_affiliate_name+'');
                  
                }, 5000); 

                $('.btn-next-placement').attr("disabled", true);                 
                if($('.btn-next-placement').attr("data-placement_type") ==  'for_placed_affiliates' ){
                  $('.btn-next-placement').removeClass('add-affiliate');
                }else{
                  $('.btn-next-placement').removeClass('update-affiliate');
                }             

              }
          }
      });

    }

    //On click on Done button when entire placement is completed
    $(document).on('click', ".done", function(){
      window.location.reload();
    });

    //On click of X on popup
    $(document).on('click','.popupCloseButton',function(){
      $('.geneology_popup').hide();
    });

    //On click of tree view button on step 2/3 of placement wizard
    $(document).on('click','.btn-parent-treeview',function(){   

     $('.geneology_popup').show();
    });

    /*----- End of Placement Wizard Page -----*/


    /*===============================================*/
    /*===============================================*/


    /*----- General Page sections -----*/

    //Autocomplete for Parent Affiliate's Search
    $( ".search-user" ).autocomplete({
        source: function( request, response ) {

            $.ajax({
              url: ajaxurl,
              dataType: "json",
              data: {
                // action: 'search_user_autocomplete_results',
                action: 'search_users',
                q: request.term,
                ajax_type:'change_sponsor_search_user',
              },
              success: function( results ) {
                
                var affiliates = [];
                $.each(results, function( arr,i ){
                    affiliates.push(i['user_key']+'-'+i['user_login']);
                });

                response( affiliates );
              }
            });

        },
        minLength: 3,
        select: function (event, ui) {

            var affiliate = ui.item.value;
            $('.get-sponsor-details').attr("disabled", false);
             display_sponsor_details(affiliate);
            
        }
    });


    //Display current existing sponsor details
    function display_sponsor_details(affiliate){

        $.ajax({
          url: ajaxurl,
          type:'post',
          data: {
            action: 'display_sponsor_details',
            affiliate: affiliate,
            is_ajax: 1,
          },
          beforeSend:function(){
            $('.existing-sponsor').html("");
          },
          success: function( results ) {
            
            $('.existing-sponsor').show().html("Existing sponsor: "+results);
          }
        });
    }

    //Autocomplete for Parent Affiliate's Search
    $( ".search-sponsor" ).autocomplete({
        source: function( request, response ) {

            $.ajax({
              url: ajaxurl,
              dataType: "json",
              data: {
                action: 'search_users',
                // action: 'search_sponsor_autocomplete_results',
                q: request.term,
                ajax_type:'change_sponsor_search_sponsor',
              },
              beforeSend: function(){
                $(".error-message").hide().html("");
              },
              success: function( results ) {
                
                var sponsors = [];

                if(results.length >0){
                  $.each(results, function( arr,i ){
                      sponsors.push(i['user_key']+'-'+i['user_login']);
                  });
                }else{
                  $(".error-message").show().html("No records with above mentioned sponsor");
                }
                

                response( sponsors );
              }
            });

        },
        minLength: 3,
        select: function (event, ui) {

            var sponsor = ui.item.value;
            $('.modify-new-sponsor').attr("disabled", false);
        }
    });

    $(".search-sponsor").keydown(function(){
      $(".error-message").hide().html("");
    });

    //On click of change sponsor button 
    $(document).on('click', '.modify-new-sponsor', function(e){
        e.preventDefault();

        var form_change_sponsor_data = $("#form_change_sponsor").serialize();
        var user = $(".search-user").val();
        var sponsor = $(".search-sponsor").val();

        if($.trim(user) == '' && $.trim(sponsor) == ''){
          $('.error-message').show().html("Invalid Member and sponsor").fadeIn(10000);
        }else if($.trim(user) == '' ){
          $('.error-message').show().html("Invalid member").fadeIn(10000);
        }else if($.trim(sponsor) == ''){
          $('.error-message').show().html("Invalid sponsor").fadeIn(10000);
        }else{

          $.ajax({
              url: ajaxurl,
              type:'POST',
              data: {
                action: 'change_sponsor',
                data: form_change_sponsor_data,
              },
              cache:false,
              beforeSend: function(){
                $('.loader-wrap').css('display', 'block');
              },
              success: function( result ) {

                // if(result == 1){
                  $('.sponsor-change-message').html("Sponsor changed Successfully.").fadeIn(50000);
                // }

              }
          });

        }
                 
    });

    //On key press of input field for search affiliate 
    $( ".search-valid-affiliate" ).keydown(function(){
        
        $('.status-msg').hide().html('');

        $('.btn-activate').hide().attr('disabled',true);
        $('.btn-terminate').hide().attr('disabled',true);

    });

    //On key press of input field for search user 
    $(".search-user").keydown(function(){
        $('.sponsor-change-message').html('');
        $('.existing-sponsor').hide();
        $('.search-sponsor').val('');
        $('.modify-new-sponsor').attr('disabled', true);
    });

    //Search Valid affiliate to terminate/activate
    $( ".search-valid-affiliate" ).autocomplete({
        source: function( request, response ) {

            $('.btn-activate').hide().attr('disabled',true);
            $('.btn-terminate').hide().attr('disabled',true);

            $.ajax({
              url: ajaxurl,
              dataType: "json",
              data: {
                // action: 'affiliate_lookup_list',
                action: 'search_users',
                q: request.term,
                ajax_type: 'termination_affiliate_list',
              },
              success: function( results ) {
                console.log('users');
                var affiliates_list = [];
                $.each(results, function( arr,i ){
                    affiliates_list.push(i['user_key']+'-'+i['user_login']);
                });

                response( affiliates_list );
              }
            });

        },
        minLength: 3,
        select: function (event, ui) {

            var valid_affiliate = ui.item.value;
            get_banned_status(valid_affiliate);
            console.log("select");
           
            
        }
    });

    //Check for active users
    function get_banned_status(valid_affiliate){
      
      $.ajax({
          url: ajaxurl,
          type:'POST',
          data: {
            action: 'get_banned_status',
            affiliate: valid_affiliate,
          },
          cache:false,
          beforeSend: function(){

            $(".btn-activate").attr('disabled',true).hide();
            $(".btn-terminate").attr('disabled',true).hide();
     
          },
          success: function( result ) {
            var obj=jQuery.parseJSON(result);
            
            $(".btn-"+obj.msg).show();
            $(".btn-"+obj.msg).removeAttr('disabled');
            $(".btn-"+obj.msg).attr('data-valid_affiliate',valid_affiliate);
          }
      });


    }

    //Terminate affiliate
    $(document).on('click', '.terminate-yes', function(e){
        e.preventDefault();

        var valid_affiliate = $('.btn-terminate').data('valid_affiliate');

        $.ajax({
          url: ajaxurl,
          type:'POST',
          data: {
            action: 'terminate_affiliate',
            affiliate: valid_affiliate,
          },
          cache:false,
          beforeSend: function(){
            $('.status-msg').hide().html('');    
            // $('.btn-terminate').addClass('buttonload');       
            // $('.btn-activate').addClass('buttonload');   
            // $('.btn-activate').find('i').removeClass('fa-user-plus');         
            // $('.btn-terminate').find('i').removeClass('fa-user-times');        
            // $('.btn-activate').find('i').addClass('fa-spinner');         
            // $('.btn-activate').find('i').addClass('fa-spin');         
                   
          },
          success: function( result ) {

            $('.status-msg').hide().html(''); 
            $('#terminate-modal').dialog("close");
           

            if(result){
              $('.status-msg').show().css('color','green').html("Member Terminated.").fadeIn(10000);

              $('.affiliate-actions').find('.btn').attr('disabled',true);
              $('.search-valid-affiliate').val('');

            }else{
              $('.status-msg').show().css('color','red').html("Member Could not be Terminated.").fadeIn(10000);
            }

            // $('.btn-terminate').removeClass('buttonload');  
            // $('.btn-activate').removeClass('buttonload');   
            // $('.btn-activate').find('i').addClass('fa-user-plus');         
            // $('.btn-terminate').find('i').addClass('fa-user-times');        
            // $('.btn-activate').find('i').removeClass('fa-spinner');         
            // $('.btn-activate').find('i').removeClass('fa-spin'); 
            

          }
      });

    });

    //Activate affiliate
    $(document).on('click', '.activate-yes', function(e){
        e.preventDefault();

        var valid_affiliate = $('.btn-activate').data('valid_affiliate');

        $.ajax({
          url: ajaxurl,
          type:'POST',
          dataType: 'json',
          data: {
            action: 'activate_affiliate',
            affiliate: valid_affiliate,
          },
          cache:false,
          beforeSend: function(){
            $('.status-msg').hide().html('');            
          },
          success: function( result ) {

            $('.status-msg').hide().html(''); 
            // $('#activate-modal').css('display', 'none');
            $("#activate-modal").dialog("close");

            

            if(result.update_flag){
              $('.status-msg').show().css('color','green').html('Member Activated.').fadeIn(10000);
              //To reset password click on <a href="'+result.reset_pass_url+'">Reset password</a>

              $('.affiliate-actions').find('.btn').attr('disabled',true).hide();
              $('.search-valid-affiliate').val('');
            }else{
              $('.status-msg').show().css('color','red').html("Member Could not be Activated.").fadeIn(10000);
            }
            
          }
      });

    });

    //Hide terminate/activate modal
    $("#terminate-modal").dialog({
      autoOpen: false
    });

    $("#activate-modal").dialog({
      autoOpen: false
    });

    //Affiliate terminate modal

    // When the user clicks the button, open the modal 
    $(document).on('click', '.btn-terminate', function(e){
        e.preventDefault();

        $('#terminate-modal').dialog('open');
    });

    // When the user clicks on <span> (x) OR No option to close the terminate modal
    $(document).on('click', '.terminate-close, .terminate-no', function(e){
      e.preventDefault();
      $('#terminate-modal').dialog("close");

    });

    //Affiliate activate modal

    // When the user clicks the button, open the modal 
    $(document).on('click', '.btn-activate', function(e){
        e.preventDefault();
        $("#activate-modal").dialog("open");
    });

    // When the user clicks on <span> (x) OR No option to close the activate modal
    $(document).on('click', '.activate-close, .activate-no', function(e){
        e.preventDefault();
        $("#activate-modal").dialog("close");
    });

  
  
    /*----- End of Affiliate Terminate -----*/

    /*===============================================*/
    /*===============================================*/

    /*----- Change Username Section -----*/

    $('.change-username').autocomplete({
        source: function( request, response ) {

            $.ajax({
              url: ajaxurl,
              dataType: "json",
              data: {
                action: 'search_username_to_change',
                q: request.term
              },
              beforeSend: function(){
                $('.change-username-validation').hide().html('');
              },
              success: function( results ) {
                $(this).removeClass('ui-autocomplete-loading');
                $('.change-username-validation').hide().html('');
                // $('.error-msg').html('');
                var usernames = [];
                $.each(results, function( arr,i ){
                    usernames.push(i['user_login']);
                });

                if(usernames.length >0){
                  response( usernames );
                }else{
                  $('.change-username-validation').show().html('Not found!');
                }
                
              }
            });

        },
        minLength: 3,
        select: function (event, ui) {
    
            var usernames = ui.item.value;
           
            $(".new-username-list").css('display','block');
            // $(".btn-search-affiliate").attr('data-affiliate_name',affiliate_data[1]);
         
        }
    });

    $( ".new-username-list input" ).autocomplete({

        source: function( request, response ) {

                   $.ajax({
                      url: ajaxurl,
                      type:'POST',
                      data: {
                        action: 'uname_availibility_status',
                        username: request.term,
                      },
                      cache:false,
                      beforeSend: function(){
                       
                        $(".uname-availabilty-msg").find('.unavailable').css('display', 'none');
                        $(".uname-availabilty-msg").find('.available').css('display', 'none');
                        

                      },
                      success: function( result ) {

                      var obj=jQuery.parseJSON(result);

                        $(".new-username").removeClass('ui-autocomplete-loading');                        // $(".ui-autocomplete-loading").hide();
                        $(".uname-availabilty-msg").show();

                        if(obj.valid){
                          $(".uname-availabilty-msg").find('.unavailable').css('display', 'none');
                          $(".uname-availabilty-msg").find('.available').css('display', 'block');
                          $(".btn-modify-username").attr('disabled',false).show();
                        }else{
                          $(".uname-availabilty-msg").find('.available').css('display', 'none');
                          if(obj.msg == 'warning'){
                            $(".uname-availabilty-msg").find('.unavailable').text("username shouldn't be contatin space");
                          }
                          $(".uname-availabilty-msg").find('.unavailable').css('display', 'block');
                          $(".btn-modify-username").attr('disabled',true);
                        }

                      }
                  });

        },
        minLength: 0,
        select: function (event, ui) {
         
            var uname = ui.item.value;
         
        }
    });

    $(".btn-modify-username").click(function(){

      var current_uname = $( ".change-username" ).val();
      var new_uname = $( ".new-username-list input" ).val();
      var hasSpace = $( ".new-username-list input" ).val().indexOf(' ')>=0;
      if(hasSpace){
        $(".change-username-msg").css('color', 'red').show().html('Space not allowed in Username!');
        return false;

      }
      $.ajax({
          url: ajaxurl,
          type:'POST',
          data: {
            action: 'update_new_username',
            current_uname: current_uname,
            new_uname: new_uname,
          },
          cache:false,
          beforeSend: function(){
                $(".change-username-msg").html('').hide();
          },
          success: function( result ) {

            if(result){
                $(".change-username-msg").css('color', 'green').show().html('Username updated!');
            }else{
                $(".change-username-msg").css('color', 'red').show().html('Username could not be updated!');
            }
            
          },
          complete: function(){
            
          }
      });
      

    });


    $(".change-username").keydown(function(){

      $(".new-username-list").hide();
      $(".uname-availabilty-msg").hide();
      $(".btn-modify-username").hide();
      $(".change-username-msg").hide();
      $( ".new-username-list input" ).val('');
      $('.change-username-validation').hide().html('');
      $(this).removeClass('ui-autocomplete-loading');

    });



    /*----- End of Change Username -----*/

    /*===============================================*/
    /*===============================================*/

    /*----- Order Assignment Section -----*/

    $('.order-no').autocomplete({
        source: function( request, response ) {

            $.ajax({
              url: ajaxurl,
              dataType: "json",
              data: {
                action: 'get_order_nos',
                q: request.term
              },
              beforeSend: function(){
                $('.order-list-validation').hide().html('');
              },
              success: function( results ) {

                $('.order-list-validation').hide().html('');
                $(this).removeClass('ui-autocomplete-loading');

                var orders = [];
                $.each(results, function( arr,i ){
                    orders.push(i['ID']);
                });

                if(orders.length >0){
                  response( orders );
                }else{
                  $('.order-list-validation').show().html('Order id does not exists');
                }
                
              }
            });

        },
        minLength: 3,
        select: function (event, ui) {
    
            var order_no = ui.item.value;

            if(order_no != ''){

              $(".new-cust-username-list").css('display','block');
              $(".order-options-wrap").css('display','block');
              $(".order-update").css('display','block');
              $('.btn-order-update').show();
              $('.order-no').removeClass('ui-autocomplete-loading');
              after_order_selection(order_no);
            }
            
         
        }
    });

    function after_order_selection(order_no){

        $.ajax({
                url: ajaxurl,
                dataType: "json",
                type: 'POST',
                data: {
                  action: 'after_order_selection',
                  order_no: order_no
                },
                beforeSend: function(){
                  $("#process_volume").attr("disabled",true);
                  $(".order-customer").html("").css('display','none');
                  $(".existing-order-type").html("").css('display','none');
                },
                success: function( results ) {

                  $("#process_volume").attr("disabled",true);

                  if(results.order_type != ""){
                    $(".existing-order-type").html("Current: <b>"+results.order_type+"</b>").css('display','block');
                  }
                  if(results.customer_name != ""){
                    $(".order-customer").html("Current: <b>"+results.customer_name+"</b>").css('display','block');
                  }
                  
                  if(results.process_volume){
                    $("#process_volume").attr("disabled",false);
                  }
                }
              });

    }

    $('.order-no').keydown(function(){

      $('.order-list-validation').hide().html('');
      $('.order-options-wrap').css('display','none');
      $('.new-cust-username-list').css('display','none');
      $('.order-no').removeClass('ui-autocomplete-loading');
      $('.btn-order-update').hide();
      $(".order-customer").html("").css('display','none');
      $(".existing-order-type").html("").css('display','none');

    });

     //Autocomplete for Parent Affiliate's Search
    $( ".new-cust-username" ).autocomplete({
        source: function( request, response ) {

            $.ajax({
              url: ajaxurl,
              dataType: "json",
              data: {
                action: 'search_users',
                // action: 'new_cust_username',
                q: request.term,
                ajax_type: 'order_assignment_new_user'
              },
              success: function( results ) {
                
                var affiliates = [];
                $.each(results, function( arr,i ){
                    affiliates.push(i['user_login']);
                });

                response( affiliates );
              }
            });

        },
        minLength: 3,
        select: function (event, ui) {

            var affiliate = ui.item.value;
            // $('.get-sponsor-details').attr("disabled", false);
            //  display_sponsor_details(affiliate);
            
        }
    });
    $(document).on('submit',"#upload_csv",function(e){
      e.preventDefault();

      formdata = new FormData();
      if($("#csv_file").prop('files').length > 0){
        file =$("#csv_file").prop('files')[0];
        formdata.append("csv", file);
        formdata.append("action","update_order_backdates");
      }
      jQuery.ajax({
                  url: ajaxurl,
                  type: "POST",
                  data: formdata,
                  processData: false,
                  contentType: false,
                  success: function (response) {
                      var obj=jQuery.parseJSON(response);
                      if(obj.update_msg == "Updated Successfully."){

                        $(".run_script_wrapper").show();
                        $("#csv_file_data").text("Order's imported Successfully.!! Click on Run process button to run back order script");
                        $("#csv_file_data").css("color",'green');
                      }else{
                        $("#csv_file_data").text(obj.update_msg);
                        $("#csv_file_data").css("color",'Red');
                      }
                  }
                });
     
    });
    $(document).on('click',".btn-order-update",function(e){

        e.preventDefault();
        var process_volume = $("#process_volume").attr("checked");
        var order_no = $(".order-no").val();
        var new_uname = $(".new-cust-username").val();
        var order_status = $(".order-types").val();

        $.ajax({
                url: ajaxurl,
                type: 'POST',
                dataType: 'json',
                data: {
                  action: 'update_order_assignment',
                  order_no: order_no,
                  new_uname: new_uname,
                  order_status: order_status,
                  process_volume: process_volume,
                },
                beforeSend: function(){
                  $(".order-update-msg").hide().html('');
                },
                success: function( results ) {

                  if(results.update_msg){
                    $(".order-update-msg").show().css('color','green').html('Order Assignment Updated.');
                  }else{
                    $(".order-update-msg").show().css('color','red').html('Order Assignment Could not be Updated.');
                  }

                  $('.new-cust-username').val('');
                  $('.order-types').val('0');
                  $('#process_volume').prop("checked", false);;
                  
                }
              });

      });


    /*----- End of Order Assignment Section -----*/

    /*===============================================*/
    /*===============================================*/

    /* ----- Auto Qualify ----- */

    /* User input field */
    $(".username-input").autocomplete({
        source: function( request, response ) {

            $.ajax({
              url: ajaxurl,
              dataType: "json",
              data: {
                // action: 'search_all_wpmlm_users',
                action: 'search_users',
                q: request.term,
                ajax_type: 'auto_qualify_all_users',
              },
              success: function( results ) {
                
                var affiliates = [];
                $.each(results, function( arr,i ){
                    affiliates.push(i['user_key']+'-'+i['user_login']);
                });

                response( affiliates );
              }
            });

        },
        minLength: 3,
        select: function (event, ui) {

            var user_id = ui.item.value;
            search_auto_qualified_users(user_id)
            
        }
      
    });
    $(".username-rewards").autocomplete({
        source: function( request, response ) {

            $.ajax({
              url: ajaxurl,
              dataType: "json",
              data: {
                // action: 'search_all_wpmlm_users',
                action: 'search_users',
                q: request.term,
                ajax_type: 'rewards_all_users',
              },
              success: function( results ) {
                
                var affiliates = [];
                $.each(results, function( arr,i ){
                    affiliates.push(i['user_key']+'-'+i['user_login']);
                });

                response( affiliates );
              }
            });

        },
        minLength: 3,
        select: function (event, ui) {

            var user_id = ui.item.value;
            $(".username-rewards-hidden").val(user_id);
            //search_reward_qualified_users(user_id);
            get_unredeemed_rewards(user_id);
            
        }
      
    });



    function get_unredeemed_rewards( affiliate_id ) {
       $.ajax({
            url: ajaxurl,
            dataType: "json",
            type: 'POST',
            data: {
                action: 'get_unredeemed_rewards',
                affiliate_id: affiliate_id
            },
            beforeSend: function(){
                $('.reward-qualify-add-personal').attr("disabled", true).hide();
                $('.reward-qualify-add-team').attr("disabled", true).hide();
            },
            success: function( results ) {
              if ( results.html ) {
                $(".unused-rewards-list").html(results.html);
              }
              if ( results.user_found_pref == true ) {
                    $('.reward-qualify-add-personal').attr("disabled", false).show();
                    $('.reward-qualify-add-team').attr("disabled", true).hide();
                    $('.button_or').hide();

              } else {
                  $('.reward-qualify-add-personal').attr("disabled", false).show();
                    $('.reward-qualify-add-team').attr("disabled", false).show();
                    $('.button_or').show();
              }
            }
        });
    }



    $(document).on("click", ".delete_rewards", function(event){
        event.preventDefault();
        var deleteid = $(this).data("deleteid"); 
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'delete_unredeemed_rewards',
                deleteid: deleteid
            },

            success: function( results ) {
                $( "#"+deleteid ).remove();
            }
        });
    });


    function search_reward_qualified_users(affiliate_id)
    {
       $.ajax({
              url: ajaxurl,
              dataType: "json",
              type: 'POST',
              data: {
                action: 'search_reward_qualified_users_byrole',
                affiliate_id: affiliate_id
              },
              beforeSend: function(){
                $('.reward-qualify-add-personal').attr("disabled", true).hide();
                $('.reward-qualify-add-team').attr("disabled", true).hide();
                
              },
              success: function( results ) {
                   console.log(results);
                  if(results.user_found_pref == false){
                    console.log("oneee");
                    $('.reward-qualify-add-personal').attr("disabled", false).show();
                    $('.reward-qualify-add-team').attr("disabled", false).show();
                    $('.button_or').show();

                  }else{
                    console.log("two");
                    $('.reward-qualify-add-personal').attr("disabled", false).show();
                    $('.reward-qualify-add-team').attr("disabled", true).hide();
                    $('.button_or').hide();
                  }
              }
            });
    }
    $(document).on('click','.reward-qualify-add-personal',function(){
      var affiliate_id = $('.username-rewards-hidden').val();
        $.ajax({
              url: ajaxurl,
              dataType: "json",
              type: 'POST',
              data: {
                action: 'add_reward',
                user_action: 'add_rewards_personal',
                affiliate_id: affiliate_id
              },
              beforeSend: function(){
                $('.auto-qualify-msg').hide().html("");
              },
              success: function( results ) {
                   
                  if(results.added){
                   
                    $('.auto-qualify-msg').show().css("color","green").html("Rewards added to user");              

                  }else{
                  
                    $('.auto-qualify-msg').show().css("color","red").html("Rewards Not added to user");
                  }
              }
            });
    });

    $(document).on('click','.reward-qualify-add-team',function(){
      var affiliate_id = $('.username-rewards-hidden').val();
        $.ajax({
              url: ajaxurl,
              dataType: "json",
              type: 'POST',
              data: {
                action: 'add_reward',
                user_action: 'add_rewards_team',
                affiliate_id: affiliate_id
              },
              beforeSend: function(){
                $('.auto-qualify-msg').hide().html("");
              },
              success: function( results ) {
                   
                  if(results.added){
                   
                    $('.auto-qualify-msg').show().css("color","green").html("Rewards added to user");              

                  }else{
                  
                    $('.auto-qualify-msg').show().css("color","red").html("Rewards Not added to user");
                  }
              }
            });
    });


     /* Display user actions add/delete qualified user buttons  */
    function search_auto_qualified_users(affiliate_id){

      $.ajax({
              url: ajaxurl,
              dataType: "json",
              type: 'POST',
              data: {
                action: 'search_auto_qualified_users',
                affiliate_id: affiliate_id
              },
              beforeSend: function(){
                $('.auto-qualify-add').attr("disabled", true).hide();
                $('.auto-qualify-delete').attr("disabled", true).hide();
              },
              success: function( results ) {

                  $('.btn-qualify-update').attr("data-affiliate_id", affiliate_id);
                

                  if(!results.user_found){
                    
                    $('.auto-qualify-add').attr("disabled", false).show();
                    $('.auto-qualify-delete').attr("disabled", true).hide();

                  }else{
                    
                    $('.auto-qualify-delete').attr("disabled", false).show();
                    $('.auto-qualify-add').attr("disabled", true).hide();
                  }
              }
            });

    }

    //On Qualified button click
     $(document).on("click",".auto-qualify-add",function(){

        // var affiliate_id = $('.btn-qualify-update').data("affiliate_id");
        var affiliate_id = $('.username-input').val();
        $.ajax({
              url: ajaxurl,
              dataType: "json",
              type: 'POST',
              data: {
                action: 'modify_auto_qualified_user',
                user_action: 'add',
                affiliate_id: affiliate_id
              },
              beforeSend: function(){
                $('.auto-qualify-msg').hide().html("");
              },
              success: function( results ) {
                   
                  if(results.added){
                   
                    $('.auto-qualify-msg').show().css("color","green").html("Member Added to Qualified List");              

                  }else{
                  
                    $('.auto-qualify-msg').show().css("color","red").html("Member Not Added to Qualified List");
                  }
              }
            });

    });

    //On delete button click
    $(document).on("click",".auto-qualify-delete", function(){

         // var affiliate_id = $('.btn-qualify-update').data("affiliate_id");
         var affiliate_id = $('.username-input').val();

        $.ajax({
              url: ajaxurl,
              dataType: "json",
              type: 'POST',
              data: {
                action: 'modify_auto_qualified_user',
                user_action: 'delete',
                affiliate_id:affiliate_id
              },
              beforeSend: function(){
                $('.auto-qualify-msg').hide().html("");
              },
              success: function( results ) {
                   
                  if(results.deleted){
                   
                    $('.auto-qualify-msg').show().css("color","green").html("Member Removed from Qualified List");              

                  }else{
                  
                    $('.auto-qualify-msg').show().css("color","red").html("Member Deletion failed");
                  }
              }
            });

    });

    function nl2br (str, is_xhtml) {   
        var breakTag = (is_xhtml || typeof is_xhtml === 'undefined') ? '<br />' : '<br>';    
        return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1'+ breakTag +'$2');
    }
    //On key press of input field for search qualified user 
    $( ".username-input" ).keydown(function(){

        $('.auto-qualify-msg').html('').hide();
        $('.btn-qualify-update ').hide().attr('disabled',true);

    });


    /*-------------------- Change User Role -------------------*/

    //Autocomplete for Parent Affiliate's Search
    $( ".change-role" ).autocomplete({
        source: function( request, response ) {

            $.ajax({
              url: ajaxurl,
              dataType: "json",
              data: {
                action: 'search_users',
                q: request.term,
                ajax_type:'change_user_role_search_user',
              },
              success: function( results ) {
                
                var affiliates = [];
                $.each(results, function( arr,i ){
                    affiliates.push(i['user_key']+'-'+i['user_login']);
                });

                response( affiliates );
              }
            });

        },
        minLength: 3,
        select: function (event, ui) {
            console.log(ui);
            console.log(event);
            var affiliate = ui.item.value;
            show_change_to_role_button(affiliate);

        }
    });

    function show_change_to_role_button(affiliate){

      $.ajax({
              url: ajaxurl,
              type: 'POST',
              data: {
                action: 'show_change_to_role_button',
                affiliate: affiliate
              },
              beforeSend: function(){
                $('.update-as-customer').hide().attr("disabled", true);
                $('.update-as-affiliate').hide().attr("disabled", true);
              },
              success: function( result ) {
                   
                  if($.trim(result) == 'representative'){                   
                    $('.update-as-customer').show().attr("disabled", false);
                    $('.update-as-preferredcustomer').show().attr("disabled", false);
                  }

                  if($.trim(result) == 'retail_customer'){                  
                    $('.update-as-affiliate').show().attr("disabled", false);
                    $('.update-as-preferredcustomer').show().attr("disabled", false);
                  }
                  if($.trim(result) == 'preferred_customer'){                  
                    $('.update-as-affiliate').show().attr("disabled", false);
                     $('.update-as-customer').show().attr("disabled", false);
                  }

              }
            });
    }

    //On delete button click
    $(document).on("click",".btn-role-update", function(){

      if( $(this).hasClass("update-as-affiliate") ){
        var change_role = "representative";
      }

      if( $(this).hasClass("update-as-customer") ){
        var change_role = "retail_customer";
      }
      if( $(this).hasClass("update-as-preferredcustomer") ){
        var change_role = "preferred_customer";
      }
      
      var affiliate = $(".change-role").val();

      $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
              action: 'modify_user_role',
              change_role: change_role,
              affiliate: affiliate
            },
            beforeSend: function(){
              // $('.auto-qualify-msg').hide().html("");
              $('.role-update-msg').hide().html(""); 
            },
            success: function( results ) {
                 
                if(results){
                 
                  $('.role-update-msg').show().css("color","green").html("User role changed.");              

                }else{
                
                  $('.role-update-msg').show().css("color","red").html("User role updation failed.");
                }
            }
          });
    });


    $(".change-role").keydown(function(){
        $('.role-update-msg').hide().html("");
        $('.update-as-customer').hide().attr("disabled", true);
        $('.update-as-affiliate').hide().attr("disabled", true);
    });

    /* User notes Screen */

    //Autocomplete for Parent Affiliate's Search
    $( ".notes-find-user" ).autocomplete({
        source: function( request, response ) {

            $.ajax({
              url: ajaxurl,
              dataType: "json",
              data: {
                action: 'search_users',
                q: request.term,
                ajax_type:'notes_search_user',
              },
              success: function( results ) {
                
                var affiliates = [];
                $.each(results, function( arr,i ){
                    affiliates.push(i['user_key']+'-'+i['user_login']);
                });

                response( affiliates );
              }
            });

        },
        minLength: 3,
        select: function (event, ui) {

            var affiliate = ui.item.value;

            var res = affiliate.split("-");
            var affiliate_name = res[1];
            var url = window.location.href;

            window.location.replace(url+"&wdt_column_filter[1]="+affiliate_name);  

        }
    });    

    $(document).on("click", ".save-notes-details", function(e){
        e.preventDefault();
   
        // var form_user_notes = $("form#save-user-notes").serialize();       
        
        var type = $(this).data('type');

        if($.trim(type) == 'update'){
          var user = '';
          var subject = $('input.edit-subject').val();
          var note = $('textarea.edit-note').val();
          var note_id = $(this).data('note_id');
        
        }else{
          var subject = $('input.subject').val();
          var note = $('textarea.note').val();
          var user = $(".notes-find-user").val();
        }

        

        $.ajax({
              url: ajaxurl,
              type: 'POST',
              dataType: "json",
              data: {
                action: 'save_user_notes',
                user: user,
                subject: subject,
                note: note,
                type: type,
                note_id: note_id
              },
              beforeSend: function(){

                if( $.trim(type) == 'update' ){  
                    $(".mofified-user-notes-msg").removeClass('error').html("");                
                    $(".mofified-user-notes-msg").removeClass('success').html("");                
                }else{
                  $(".user-notes-msg").removeClass('error').html("");
                  $(".user-notes-msg").removeClass('success').html("");
                }

              },
              success: function( results ) {
                
                if(results){

                  if( $.trim(type) == 'update' ){  
                    $(".mofified-user-notes-msg").addClass('success').html("Note modified.");                  
                  }else{
                    $(".user-notes-msg").addClass('success').html("New note created.");
                    // $(".subject").val("");
                    // $(".note").val("");
                    // $("form#save-user-notes").reset();
                  }

                }else{

                  if( $.trim(type) == 'update' ){  
                    $(".mofified-user-notes-msg").addClass('error').html("Note could not be modified.");                
                  }else{
                    $(".user-notes-msg").addClass('error').html("New note not generated.");
                  }

                }
              },
              complete: function(){
                // if( $.trim(type) == 'update' ){  
                  location.reload();
                // }
              }
            });    

    });

    $(".view-notes").click(function(){
        // e.preventDefault();   
        var note_id = $(this).data('note_id');        
        
        $.ajax({
              url: ajaxurl,
              type: 'POST',
              dataType: "json",
              data: {
                action: 'view_notes',
                note_id: note_id,
              },
              success: function( results ) {
                var note_details = '';
                
                if(results.note_id >1){                  

                  note_details += "<table border='0'>";      
                  note_details += "<tr><td><b>Note ID:</b> "+results.note_id+"</td></tr>";
                  note_details += "<tr><td><b>User:</b> "+results.user+"</td></tr>";
                  note_details += "<tr><td><b>User admin:</b> "+results.admin_user+"</td></tr>";
                  note_details += "<tr><td><b>Summary:</b> "+results.subject+"</td></tr>";
                  note_details += "<tr><td><b>Note:</b></td></tr>";
                  note_details += "<tr><td>"+results.text+"</td></tr>";
                  note_details += "<tr><td><b>Created at:</b> "+results.created+"</td></tr>";
                  note_details += "<tr><td><b>Updated by:</b> "+results.Updatedby+"</td></tr>";       
                  note_details += "</table>";

                  $(".notes-details").removeClass('error').html(note_details);
                  $("#view-modal").css("display", "block");

                }else{
                  $(".notes-details").addClass('error').html("Error retrieving user note details.");
                }

              }
            });    

    });

    $(document).on("click", ".view-close", function(e){
        $("#view-modal").css("display", "none");
    });

    $(document).on("click", ".delete-close", function(e){
        $("#delete-modal").css("display", "none");
    });

    $(document).on("click", ".edit-close", function(e){
        $("#edit-modal").css("display", "none");
    });

    $(document).on("click", ".add-close", function(e){
        $("#add-modal").css("display", "none");
    });    

    $(document).on("click", ".edit-notes", function(e){
        e.preventDefault();
   
        var note_id = $(this).data('note_id');
        $("#edit-modal").css("display", "block");

        $.ajax({
              url: ajaxurl,
              type: 'POST',
              dataType: "json",
              data: {
                action: 'edit_notes',
                note_id: note_id,
              },
              beforeSend: function(){
                  $(".mofified-user-notes-msg").removeClass('error').html("");                
                  $(".mofified-user-notes-msg").removeClass('success').html("");                 

              },
              success: function( results ) {

                

                $('.edit-subject').val(results.subject);
                $('.edit-note').val(results.text);
                $(".save-notes-details").attr('data-note_id',note_id);

              }
            });    

    });

    $(document).on("click", ".delete-notes", function(e){

      $("#delete-modal").css("display", "block");

      var note_id = $(this).data("note_id");
      $(".delete-yes").removeClass('waves-effect').attr('data-note_id',note_id);
      $(".delete-no").removeClass('waves-effect');       

    });

    $(document).on("click", ".delete-yes", function(e){
      var note_id = $(this).data("note_id");

          $.ajax({
              url: ajaxurl,
              type: 'POST',
              dataType: "json",
              data: {
                action: 'delete_note',
                note_id: note_id,
              },
              beforeSend: function(){
                $(".user-notes-msg").removeClass('error').html("");
                $(".user-notes-msg").removeClass('success').html("");
              },
              success: function( results ) {
                
                if(results.deleted){
                  $(".wpdatatable-notes-msg").addClass('success').html("Note deleted.");
                  $("#delete-modal").css("display", "none");
                }else{
                  $(".wpdatatable-notes-msg").addClass('error').html("Note not deleted.");
                  $("#delete-modal").css("display", "none");
                }
              },
              complete: function(){
                location.reload();
              }
            });  

    });

    $(document).on("click", ".btn-add-new-note", function(e){
      e.preventDefault();
      $("#add-modal").css("display", "block");    

    });


    //Count variable to insert 1 record at a time
   var cnt=0;

   $(document).on('click', '.btn-add-new-setting', function(){

        cnt=parseInt(cnt)+parseInt(1);
        
        var adding_new_setting_html = '';

        $.ajax({

            url: ajaxurl,
                data: {
                    action: 'get_commission_settings_categories',
                    is_cajax:1,
                },
                dataType:'json',
                type: 'POST',
                cache: false,
                beforeSend: function(){
                    loading('show');
                },
                success: function (response) {

                    loading('hide');
                    adding_new_setting_html += '<tr class="commission-settings-list-row-id" id="new-setting-row" >';
                    adding_new_setting_html += '    <td>';
                    adding_new_setting_html += '<button class="button btn-cancel-setting" data-id="new-setting-row" type="button">Cancel</button>';
                    adding_new_setting_html += '    </td>';
                    adding_new_setting_html += '    <td> general <td>';
                    adding_new_setting_html += '    <input type="text" class="commission-settings-new-key"/>';
                    adding_new_setting_html += '    </td>';
                    adding_new_setting_html += '    <td>';
                    adding_new_setting_html += '    <input type="text" class="commission-settings-new-value"/>';
                    adding_new_setting_html += '    </td>';
                    adding_new_setting_html += '    <td>';
                    adding_new_setting_html += '<button class="button btn-add-new-setting-record" type="button">Add</button>';
                    adding_new_setting_html += '    </td>';
                    adding_new_setting_html += '    <td>';
                    adding_new_setting_html += '<button class="button btn-delete-setting" type="button" disabled>Delete</button>'; 
                    adding_new_setting_html += '    </td>';        
                    adding_new_setting_html += '</tr>';

                    //Limit to insert 1 record at a time
                    if(cnt < 2){
                        $('.commission-settings-list-body').prepend(adding_new_setting_html);
                    }

                }
            
        });        

   });

   /* On click of Edit button */
   $(document).on('click', '.btn-edit-setting', function(){
        
        var id = $(this).attr('data-record_id');

        $('.btn-update-setting-'+id).show();
        $('.btn-cancel-edit-'+id).show();
        $('.btn-edit-setting-'+id).hide();

        $('.setting-key-'+id).css('display','block');
        $('.setting-value-'+id).css('display','block');
        // $('.setting-category-'+id).css('display','block');

        $('.setting-key-text-'+id).css('display','none');
        $('.setting-value-text-'+id).css('display','none');
        // $('.setting-category-text-'+id).css('display','none');

        $(this).parents('tr').css('background-color','#9dc8dc');

   });

    $(document).on('click', '.btn-cancel-setting', function(){
        var cancel_id = $(this).attr('data-id');
        $("#"+cancel_id).remove();
        cnt=0;
    });

    $(document).on('click','.btn-cancel-edit', function(){

        var cancel_edit = $(this).attr('data-title_id');

        $('.setting-key-'+cancel_edit).hide();
        $('.setting-value-'+cancel_edit).hide();
        $('.setting-category-'+cancel_edit).hide();
        $('.btn-update-setting-'+cancel_edit).hide();
        $('.btn-cancel-edit-'+cancel_edit).hide();

        $('.setting-key-text-'+cancel_edit).show();
        $('.setting-value-text-'+cancel_edit).show();
        $('.setting-category-text-'+cancel_edit).show();
        $('.btn-edit-setting-'+cancel_edit).show();

        // $(this).parents('tr').css('background-color','#fff');

   });


  /* On click of Update button */

   $(document).on('click', '.btn-update-setting', function(){

        loading('show');
        var id = $(this).attr('data-record_id');
        var key = $('.setting-key-'+id).val();
        var value = $('.setting-value-'+id).val();
        var category = $('.setting-category-'+id).val();

        $.ajax({
                url: ajaxurl,
                data: {
                    action: 'update_csa_admin_adv_settings',
                    settings_id: id,
                    settings_key: key,
                    settings_value: value,
                    settings_category: category,
                    is_ajax:1,
                },
                type: 'POST',
                cache: false,
                beforeSend: function(){
                    loading('show');
                },
                success: function (response) {
                    
                    if(response){
                        loading('hide');
                        $('.record-updation-msg').css('display','block').fadeOut(1000);
                    }  

                    
                },complete: function(){
                  loading('hide');
                  location.reload();
                }
            });

   });

   /* ---- Deleting record from the database ----- */

   //Confirmation Dialogue
   $(document).on('click', '.btn-delete-setting', function(){
        
        var id = $(this).attr('data-record_id');

        $('.cd-popup').addClass('is-visible');

        $(".cd-popup").find('.cd-popup-adv-settings p').html("Are you sure you want to delete record ID:"+id+"?");
            
        $(".cd-popup").find('.cd-popup-adv-settings ul').attr('id',id);

        $(this).parents('tr').css('background-color','#9dc8dc');

   });

   //Deleting record from the database
   $(document).on('click', '.cd-popup-adv-settings ul a', function(){

        var confirm_msg = $(this).html();

        $('.cd-popup').removeClass('is-visible');

        if($.trim(confirm_msg) == 'Yes'){

            loading('show');

            var id = $('.cd-popup-adv-settings ul').attr('id');
            //Deleting record from database
            $.ajax({
                    url: ajaxurl,
                    data: {
                        action: 'delete_csa_admin_adv_settings',
                        settings_id: id,
                        is_ajax:1,
                    },
                    type: 'POST',
                    cache: false,
                    beforeSend: function(){
                        loading('show');
                    },
                    success: function (response) {
                        
                        if(response){

                            $('.record-deletion-msg').css('display','block').fadeOut(1000);

                       }  

                        
                    },
                    complete: function(){
                      loading('hide');
                      location.reload();
                    }
                });

        }
        else{
            $("tr:odd").css('background-color','#f7f7f7');
            $("tr:even").css('background-color','#fff');
        }

   });

   /* Pop Up JS */

    //open popup
    $('.cd-popup-trigger').on('click', function(event){
        event.preventDefault();
        $('.cd-popup').addClass('is-visible');
    });
      
     //close popup
    $('.cd-popup').on('click', function(event){
        if( $(event.target).is('.cd-popup-close') || $(event.target).is('.cd-popup') ) {
          event.preventDefault();
          $(this).removeClass('is-visible');
        }
    });
      
    //close popup when clicking the esc keyboard button
    $(document).keyup(function(event){
      if(event.which=='27'){
        $('.cd-popup').removeClass('is-visible');
      }
    });

    /* ---- Adding new record to the database ----- */

   $(document).on('click', '.btn-add-new-setting-record', function(){
        
        loading('show');
        
        //Reset Count variable to insert next record
        cnt=0;
        
        var new_key = $('.commission-settings-new-key').val();
        var new_value = $('.commission-settings-new-value').val();
        var validate = true;

        if(new_key == ''){
            validate = false;
        }

        if(new_value == ''){
            validate = false;
        }
        
        var no_of_records = $('.commission-settings-list-row-id').length;
        var no_of_pages =$('.commission-settings-pagination a').length;
        no_of_pages = no_of_pages -2;

        var total_records = no_of_pages*no_of_records;

        if(validate){

            //Adding new record to database
            $.ajax({
                    url: ajaxurl,
                    data: {
                        action: 'add_new_csa_admin_adv_settings',
                        new_key: new_key,
                        new_value: new_value,
                        is_ajax:1,
                    },
                    type: 'POST',
                    cache: false,
                    beforeSend: function(){
                        loading('show');
                    },
                    success: function (response) {
                        loading('show');
                        if(response){
                            loading('hide');
                            $('.record-updation-msg').css('display','block').fadeOut(4500);
                        }  

                        $(".loader-wrap").css('display','none');
                    },
                    complete: function(){
                        loading('hide');
                        location.reload();
                        
                    }
                });

        }
        else{
            $('.record-validate-msg').css('color','red').css('display','block').fadeOut(1000);
        }
        

        loading('hide');

   });



  $('#save_gh_settings').on('click', function (e) {

      e.preventDefault();

      var data = {
          'action': 'save_gh_settings',
          // 'category': $("#product_category").val(),
          'subsion_page': $("#tree_view_redirect_page").val(),
          // 'sucess_page': $("#sponsor_checkout_redirect_page").val(),
      }

      $.ajax({

          url: ajaxurl,
          type: 'post',
          dataType: 'json',
          data: data,
          success: function (data) {

              if ($.trim(data)) {
                  $('.url-change-message').html('Settings Updated');
                  // .css('color', 'green');
                  // $('.success-msg').css('font-weight', 'bold');
              } else {
                  $('.error-message').html('Error updating the settings');
                  //.css('color', 'red');
                  //$('.success-msg').css('font-weight', 'bold');
              }
          }

      });

  });

   function loading(status){
        if(status == 'show'){
            $(".loader-wrap").show();
            $('body').css('overflow','hidden');
        }else if(status == 'hide'){
            $(".loader-wrap").hide();
            $('body').css('overflow','scroll');
        }
    }
    $(".run_script").click(function(){
     
      $('.cd-popup').addClass('is-visible');

    });
    $(document).on('click', '.cd-popup-run-process ul a', function(){
      var confirm_msg = $(this).html();

        $('.cd-popup').removeClass('is-visible');

        if($.trim(confirm_msg) == 'Yes'){
          var timeout = 0;
          //Auto run script
          var order_cron_run_url = $('#run_orderback').val();

          $.ajax({
          url: order_cron_run_url,
          data: '',
          type: 'POST',
          cache: false,
          beforeSend: function(){
          // loading('show');
          
          $('.order_run_logs_popup').show(); 
          },
          xhr: function (response) { 
          
          var xhr = $.ajaxSettings.xhr();

          var d = new Date();

          var month = d.getMonth()+1;
          var day = d.getDate();

          var output = ((''+day).length<2 ? '0' : '') + day+ '_' + month + '_' +
          d.getFullYear() ;

          var order_cron_run_url_new = order_cron_run_url.split('/cron/commissions/');
          order_cron_run_log_url = order_cron_run_url_new['0']+'/cron/commissions/cron_logs/log_order_backdates_'+output+'.log';   

          var counter = 0;                        

          timeout = setInterval( function(){

          $.ajax(order_cron_run_log_url).done( function(data){
          data = nl2br(data)
          data = data.split("<br />").slice(-25).join("<br />");
          
          $('.order_run_logs_popup p').html(data);
          });
          //counter++;

          },15000); 
          return xhr;
          },
          success: function (response) {
            
            $('.order_run_logs_popup p').html(response);
            clearInterval(timeout);
            $('.order_run_logs_popup').hide();
          

          },
          error:function(){
            
          alert('something went wrong, please try after some time!');
          
          }
          });         
        
        }
    });
    
});