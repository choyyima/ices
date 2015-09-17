<script>
    var reference_id = '<?php echo $reference_id ?>';
    var reference_text = '<?php echo $reference_text ?>';
    var reference_type = '<?php echo $reference_type ?>';
    var customer_id = '<?php echo $customer_id ?>';
    
    customer_deposit_allocation_init();
    customer_deposit_allocation_bind_event();
    
    customer_deposit_allocation_after_submit = function(){
        $('#modal_customer_deposit_allocation').modal('hide');
        window.location.href = APP_WINDOW.current_url();
    }
    
    var refill_invoice_customer_deposit_allocation_init = function(){
        var parent_pane = $('#modal_customer_deposit_allocation')[0];
        customer_deposit_allocation_components_prepare();
        $('#modal_customer_deposit_allocation').find('#customer_deposit_allocation_customer_deposit').select2('disable');
    }
    
    $('#customer_deposit_allocation_new').on('click',function(){
        var parent_pane = $('#modal_customer_deposit_allocation')[0];
        $(parent_pane).find('#customer_deposit_allocation_method').val('add');
        $(parent_pane).find('#customer_deposit_allocation_id').val('');
        $(parent_pane).find('#customer_deposit_allocation_customer_id').val(customer_id);
        refill_invoice_customer_deposit_allocation_init();
        $(parent_pane).find('#customer_deposit_allocation_customer_deposit').select2('enable');
        $(parent_pane).find('#customer_deposit_allocation_reference')
            .select2('data',{id:reference_id,text:reference_text,reference_type:reference_type}).change(); 
       $(parent_pane).find('#customer_deposit_allocation_reference').select2('disable');
       
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
            refill_invoice_customer_deposit_allocation_init();
            $(parent_pane).modal('show');
        });

    });
    
</script>