<script>
    var product_price_list_id = '<?php echo $product_price_list_id ?>';
    
    
    delivery_extra_charge_init();
    delivery_extra_charge_bind_event();
    
    delivery_extra_charge_after_submit = function(){
        $('#modal_delivery_extra_charge').modal('hide');
        window.location.href = APP_WINDOW.current_url();
    }
 
    
    $('#delivery_extra_charge_btn_new').on('click',function(){
       
       var parent_pane = $('#modal_delivery_extra_charge').find('.modal-body')[0];
       $(parent_pane).find('#delivery_extra_charge_method').val('add');
       $(parent_pane).find('#delivery_extra_charge_id').val('');
       $(parent_pane).find('#delivery_extra_charge_reference').val(product_price_list_id); 
       delivery_extra_charge_components_prepare();
      
       
    });
    
    var llinks = $('#delivery_extra_charge_table').find('a');
    $.each(llinks, function(key, val){
        
        $(val).off('click');
        $(val).on('click',function(e){
            e.preventDefault();
            var lid = $(val).attr('href');
            var parent_pane = $('#modal_delivery_extra_charge')[0];
            $(parent_pane).find('#delivery_extra_charge_method').val('view');
            $(parent_pane).find('#delivery_extra_charge_id').val(lid);   
            $(parent_pane).find('#delivery_extra_charge_reference').val(product_price_list_id); 
            delivery_extra_charge_components_prepare();
            $(parent_pane).modal('show');
        });

    });
    
    var lbuttons = $('#delivery_extra_charge_table').find('button');
    $.each($(lbuttons),function(key, val){
        $(val).on('click',function(){
            var delete_url = $(this).attr("delete_url");
            var form = $("#modal_confirmation_form")[0];
            $(form).attr("action",delete_url);
            $(form).closest('.modal').modal('show');
        });
    });
    
</script>