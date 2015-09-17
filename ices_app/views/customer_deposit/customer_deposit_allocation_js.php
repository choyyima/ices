<script>
    var customer_deposit_id = '<?php echo $customer_deposit_id ?>';
    var customer_deposit_text = '<?php echo $customer_deposit_text ?>';
    var customer_deposit_type = '<?php echo $customer_deposit_type ?>';
    var customer_id = '<?php echo $customer_id ?>';
    
    customer_deposit_allocation_init();
    customer_deposit_allocation_bind_event();
    
    customer_deposit_allocation_after_submit = function(){
        $('#modal_customer_deposit_allocation').modal('hide');
        window.location.href = APP_WINDOW.current_url();
    }
    
    var customer_deposit_customer_deposit_allocation_init = function(){
        var parent_pane = $('#modal_customer_deposit_allocation')[0];
        customer_deposit_allocation_components_prepare();
        $('#modal_custoemer_deposit_allocation').find('#custoemer_deposit_allocation_customer_deposit').select2('disable');
    }
    
    $('#customer_deposit_allocation_new').on('click',function(){
        var parent_pane = $('#modal_customer_deposit_allocation')[0];
        $(parent_pane).find('#customer_deposit_allocation_method').val('add');
        $(parent_pane).find('#customer_deposit_allocation_id').val('');
        $(parent_pane).find('#customer_deposit_allocation_customer_id').val(customer_id);
        customer_deposit_customer_deposit_allocation_init();
        $(parent_pane).find('#customer_deposit_allocation_customer_deposit')
            .select2('data',{id:customer_deposit_id,text:customer_deposit_text,reference_type:customer_deposit_type}).change(); 
       $(parent_pane).find('#customer_deposit_allocation_customer_deposit').select2('disable');
    });
    
    var llinks = $('#customer_deposit_allocation_table').find('a');
    $.each(llinks, function(key, val){
        
        $(val).off('click');
        $(val).on('click',function(e){
            e.preventDefault();
            var lid = $(val).attr('href');
            var parent_pane = $('#modal_customer_deposit_allocation')[0];
            $(parent_pane).find('#customer_deposit_allocation_method').val('view');
            $(parent_pane).find('#customer_deposit_allocation_id').val(lid);
            customer_deposit_customer_deposit_allocation_init();
            $(parent_pane).modal('show');
        });

    });
    
    
</script>