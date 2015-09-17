<script>
    var reference_id = '<?php echo $reference_id ?>';
    var reference_text = '<?php echo $reference_text ?>';
    var reference_type = '<?php echo $reference_type ?>';
    
    
    delivery_order_init();
    delivery_order_bind_event();
    
    delivery_order_after_submit = function(){
        $('#modal_delivery_order').modal('hide');
        window.location.href = APP_WINDOW.current_url();
    }
    
    var rswo_delivery_order_init = function(){
        var parent_pane = $('#modal_delivery_order')[0];
        delivery_order_components_prepare();
        $('#modal_delivery_order').find('#delivery_order_reference').select2('disable');
    }
    
    $('#delivery_order_new').on('click',function(){
        var parent_pane = $('#modal_delivery_order')[0];
        $(parent_pane).find('#delivery_order_method').val('add');
        $(parent_pane).find('#delivery_order_id').val('');
        rswo_delivery_order_init();
        
        $(parent_pane).find('#delivery_order_reference')
            .select2('data',{id:reference_id,text:reference_text,reference_type:reference_type}).change(); 
        $(parent_pane).find('#delivery_order_reference').select2('disable');
        
        
    });
    
    var llinks = $('#delivery_order_view_table').find('a');
    $.each(llinks, function(key, val){
        
        $(val).off('click');
        $(val).on('click',function(e){
            e.preventDefault();
            var lid = $(val).attr('href');
            var parent_pane = $('#modal_delivery_order')[0];
            $(parent_pane).find('#delivery_order_method').val('view');
            $(parent_pane).find('#delivery_order_id').val(lid);
            rswo_delivery_order_init();
            $(parent_pane).modal('show');
        });

    });
    
</script>