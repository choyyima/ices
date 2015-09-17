<script>
    var reference_id = '<?php echo $reference_id ?>';
    var reference_text = '<?php echo $reference_text ?>';
    var reference_type = '<?php echo $reference_type ?>';
    
    
    customer_refund_init();
    customer_refund_bind_event();
    
    customer_refund_after_submit = function(){
        $('#modal_customer_refund').modal('hide');
        window.location.href = APP_WINDOW.current_url();
    }
    
    var customer_deposit_customer_refund_init = function(){
        var parent_pane = $('#modal_customer_refund')[0];
        customer_refund_components_prepare();
        $('#modal_customer_refund').find('#customer_refund_reference').select2('disable');
    }
    
    $('#customer_refund_new').on('click',function(){
        var parent_pane = $('#modal_customer_refund')[0];
        $(parent_pane).find('#customer_refund_method').val('add');
        $(parent_pane).find('#customer_refund_id').val('');

        customer_deposit_customer_refund_init();
        $(parent_pane).find('#customer_refund_reference')
            .select2('data',{id:reference_id,text:reference_text,reference_type:reference_type}).change(); 
       $(parent_pane).find('#customer_refund_reference').select2('disable');
    });
    
    var llinks = $('#customer_refund_table').find('a');
    $.each(llinks, function(key, val){
        
        $(val).off('click');
        $(val).on('click',function(e){
            e.preventDefault();
            var lid = $(val).attr('href');
            var parent_pane = $('#modal_customer_refund')[0];
            $(parent_pane).find('#customer_refund_method').val('view');
            $(parent_pane).find('#customer_refund_id').val(lid);
            customer_deposit_customer_refund_init();
            $(parent_pane).modal('show');
        });

    });
    
</script>