<script>
    var product_price_list_id = '<?php echo $product_price_list_id ?>';
    
    
    delivery_mop_init();
    delivery_mop_bind_event();
    
    delivery_mop_after_submit = function(){
        $('#modal_delivery_mop').modal('hide');
        window.location.href = APP_WINDOW.current_url();
    }
 
    
    $('#delivery_mop_btn_new').on('click',function(){
       
       var parent_pane = $('#modal_delivery_mop').find('.modal-body')[0];
       $(parent_pane).find('#delivery_mop_method').val('add');
       $(parent_pane).find('#delivery_mop_id').val('');
       $(parent_pane).find('#delivery_mop_reference').val(product_price_list_id); 
       delivery_mop_components_prepare();
      
       
    });
    
    var llinks = $('#delivery_mop_table').find('a');
    $.each(llinks, function(key, val){
        
        $(val).off('click');
        $(val).on('click',function(e){
            e.preventDefault();
            var lid = $(val).attr('href');
            var parent_pane = $('#modal_delivery_mop')[0];
            $(parent_pane).find('#delivery_mop_method').val('view');
            $(parent_pane).find('#delivery_mop_id').val(lid);   
            $(parent_pane).find('#delivery_mop_reference').val(product_price_list_id); 
            delivery_mop_components_prepare();
            $(parent_pane).modal('show');
        });

    });
    
    var lbuttons = $('#delivery_mop_table').find('button');
    $.each($(lbuttons),function(key, val){
        $(val).on('click',function(){
            var delete_url = $(this).attr("delete_url");
            var form = $("#modal_confirmation_form")[0];
            $(form).attr("action",delete_url);
            $(form).closest('.modal').modal('show');
        });
    });
    
</script>