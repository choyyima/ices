<script>
    $('#store_button_save').on('click',function(e){
        e.preventDefault();
        btn = $(this);
        btn.addClass('disabled');    
        var json_data ={
            ajax_post:true,
            code:$("#code").val(),
            name:$("#name").val(),
            city:$("#city").val(),
            address:$("#address").val(),
            country:$("#country").val(),
            phone:$("#phone").val(),
            email:$("#email").val(),
            notes:$("#notes").val(),
            warehouse_id:[]
        };
        
        var warehouse = [];
        tbody = $("#warehouse_table").find('tbody');
        
        $.each(tbody.children() ,function(tbody_key, tbody_val){
            warehouse_id = $(tbody_val).find('[name=id]')[0].innerHTML;
            json_data.warehouse_id.push(warehouse_id); 
        });
        console.log(json_data);
        var store_ajax_url = APP_WINDOW.current_url();
        var index = "<?php echo $index ?>";
        var response = APP_DATA_TRANSFER.submit(store_ajax_url,json_data,index);
        setTimeout(function(){btn.removeClass('disabled')},1000);
    });


</script>