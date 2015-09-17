<script>
    $('#user_login_submit').on('click',function(e){
        e.preventDefault();
        btn = $(this);
        btn.addClass('disabled');    
        var json_data ={
            ajax_post:true,
            code:$('[name="code"]').val(),
            name:$('[name="name"]').val(),
            password:$('[name="password"]').val(),
            first_name:$('[name="first_name"]').val(),
            last_name:$('[name="last_name"]').val(),
            u_group_id:$('[name="u_group_id"]').select2('val'),
            default_store_id:$('[name="default_store_id"]').select2('val'),
            store:[]
        };
        
        
        tbody = $("#store_table").find('tbody');        
        $.each(tbody.children() ,function(tbody_key, tbody_val){
            var lstore_id = $(tbody_val).find('[name=id]')[0].innerHTML;
            json_data.store.push({id:lstore_id}); 
        });
        
        var store_ajax_url = APP_WINDOW.current_url();
        var index = "<?php echo $index ?>";
        var result = APP_DATA_TRANSFER.submit(store_ajax_url,json_data,index);
        if(result.success ===1){
            var url = index+'view/'+result.trans_id;
            window.location.href=url;
        }
        setTimeout(function(){btn.removeClass('disabled')},1000);
    });


</script>