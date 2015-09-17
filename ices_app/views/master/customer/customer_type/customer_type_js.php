<script>
    $('#customer_type_button_save').on('click',function(e){
        e.preventDefault();
        btn = $(this);
        btn.addClass('disabled');    
        var json_data ={
            ajax_post:true,
            code:$("#code").val(),
            name:$("#name").val(),
            notes:$("#notes").val(),
            product_price_list:[],
            refill_product_price_list:[]
        };

        tbody = $("#product_price_list_table").find('tbody');
        
        $.each(tbody.children() ,function(tbody_key, tbody_val){
            product_price_list_id = $(tbody_val).find('[name=id]')[0].innerHTML;
            json_data.product_price_list.push(product_price_list_id); 
        });

        tbody = $("#refill_product_price_list_table").find('tbody');
        
        $.each(tbody.children() ,function(tbody_key, tbody_val){
            refill_product_price_list_id = $(tbody_val).find('[name=id]')[0].innerHTML;
            json_data.refill_product_price_list.push(refill_product_price_list_id); 
        });

        var customer_ajax_url = APP_WINDOW.current_url();
        var index = "<?php echo $index ?>";
        var response = APP_DATA_TRANSFER.submit(customer_ajax_url,json_data,index);
         setTimeout(function(){btn.removeClass('disabled')},1000);
    });


</script>