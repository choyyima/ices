<script>
    $("#new_customer_modal").on('shown.bs.modal',function(){
        $(this).scrollTop(0);        
       $("#new_customer_modal_code").focus();
    });
    
    $("#new_customer_modal_button_save").on('click',function(){
        //e.preventDefault();
        btn = $(this);
        btn.addClass('disabled');    
       var json_data ={
           ajax_post:true,
           code:$("#new_customer_modal_code").val(),
           name:$("#new_customer_modal_name").val(),
           city:$("#new_customer_modal_city").val(),
           address:$("#new_customer_modal_address").val(),
           country:$("#new_customer_modal_country").val(),
           phone:$("#new_customer_modal_phone").val(),
           phone2:$("#new_customer_modal_phone2").val(),
           phone3:$("#new_customer_modal_phone3").val(),
           phone4:$("#new_customer_modal_phone4").val(),
           phone5:$("#new_customer_modal_phone5").val(),
           email:$("#new_customer_modal_email").val(),
           notes:$("#new_customer_modal_notes").val(),
           customer_type:[],
           message_session:false
       };
       
       var customer_type = [];
        tbody = $("#new_customer_modal_customer_type_table").find('tbody');
        
        $.each(tbody.children() ,function(tbody_key, tbody_val){
            customer_type_id = $(tbody_val).find('[name=id]')[0].innerHTML;
            json_data.customer_type.push(customer_type_id); 
        });
       
       var customer_ajax_url = "<?php echo $customer_ajax_url ?>";
       var response = APP_DATA_TRANSFER.submit(customer_ajax_url,json_data);
       if(response.success == 1){
            $("#new_customer_modal").modal('hide');
       }
       $("#new_customer_modal").scrollTop(0);        
        setTimeout(function(){btn.removeClass('disabled')},1000);
    });
</script>