<script>
    var refill_receipt_id = '<?php echo $refill_receipt_id ?>';
    var refill_receipt_text = '<?php echo $refill_receipt_text ?>';
    var customer_id = '<?php echo $customer_id ?>';
    
    refill_receipt_allocation_init();
    refill_receipt_allocation_bind_event();
    
    refill_receipt_allocation_after_submit = function(){
        $('#modal_refill_receipt_allocation').modal('hide');
        window.location.href = APP_WINDOW.current_url();
    }
    
    var refill_receipt_refill_receipt_allocation_init = function(){
        var parent_pane = $('#modal_refill_receipt_allocation')[0];
        refill_receipt_allocation_components_prepare();
        $('#modal_refill_receipt_allocation').find('#refill_receipt_allocation_refill_receipt').select2('disable');
    }
    
    $('#refill_receipt_allocation_new').on('click',function(){
        var parent_pane = $('#modal_refill_receipt_allocation')[0];
        $(parent_pane).find('#refill_receipt_allocation_method').val('add');
        $(parent_pane).find('#refill_receipt_allocation_id').val('');
        $(parent_pane).find('#refill_receipt_allocation_customer_id').val(customer_id);
        refill_receipt_refill_receipt_allocation_init();
        $(parent_pane).find('#refill_receipt_allocation_refill_receipt')
            .select2('data',{id:refill_receipt_id,text:refill_receipt_text}).change(); 
       $(parent_pane).find('#refill_receipt_allocation_refill_receipt').select2('disable');
    });
    
    var llinks = $('#refill_receipt_allocation_table').find('a');
    $.each(llinks, function(key, val){
        
        $(val).off('click');
        $(val).on('click',function(e){
            e.preventDefault();
            var lid = $(val).attr('href');
            var parent_pane = $('#modal_refill_receipt_allocation')[0];
            $(parent_pane).find('#refill_receipt_allocation_method').val('view');
            $(parent_pane).find('#refill_receipt_allocation_id').val(lid);
            refill_receipt_refill_receipt_allocation_init();
            $(parent_pane).modal('show');
        });

    });
    
</script>