<script>
    var reference_id = '<?php echo $reference_id ?>';
    var reference_text = '<?php echo $reference_text ?>';
    var reference_type = '<?php echo $reference_type ?>';
    
    
    customer_deposit_init();
    customer_deposit_bind_event();
    
    customer_deposit_after_submit = function(){
        $('#modal_customer_deposit').modal('hide');
        window.location.href = APP_WINDOW.current_url();
    }
    
    var rwo_customer_deposit_init = function(){
        var parent_pane = $('#modal_customer_deposit')[0];
        customer_deposit_components_prepare();
        $('#modal_customer_deposit').find('#customer_deposit_reference').select2('disable');
    }
    
    $('#customer_deposit_new').on('click',function(){
        var parent_pane = $('#modal_customer_deposit')[0];
        $(parent_pane).find('#customer_deposit_method').val('add');
        $(parent_pane).find('#customer_deposit_id').val('');
        rwo_customer_deposit_init();
        
        $(parent_pane).find('#customer_deposit_reference')
            .select2('data',{id:reference_id,text:reference_text,reference_type:reference_type}).change(); 
        $(parent_pane).find('#customer_deposit_reference').select2('disable');
        
        
    });
    
    var llinks = $('#customer_deposit_view_table').find('a');
    $.each(llinks, function(key, val){
        
        $(val).off('click');
        $(val).on('click',function(e){
            e.preventDefault();
            var lid = $(val).attr('href');
            var parent_pane = $('#modal_customer_deposit')[0];
            $(parent_pane).find('#customer_deposit_method').val('view');
            $(parent_pane).find('#customer_deposit_id').val(lid);
            rwo_customer_deposit_init();
            $(parent_pane).modal('show');
        });

    });
    
</script>