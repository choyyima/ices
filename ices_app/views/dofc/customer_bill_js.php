<script>
    var reference_id = '<?php echo $reference_id ?>';
    var reference_text = '<?php echo $reference_text ?>';
    
    customer_bill_init();
    customer_bill_bind_event();
    
    customer_bill_after_submit = function(){
        $('#modal_customer_bill').modal('hide');
        window.location.href = APP_WINDOW.current_url();
    }
    
    var dofc_customer_bill_init = function(){
        var parent_pane = $('#modal_customer_bill')[0];
        customer_bill_components_prepare();
        $('#modal_customer_bill').find('#customer_bill_reference').select2('disable');
    }
        
    var llinks = $('#customer_bill_table').find('a');
    $.each(llinks, function(key, val){        
        $(val).off('click');
        $(val).on('click',function(e){
            e.preventDefault();
            var lid = $(val).attr('href');
            var parent_pane = $('#modal_customer_bill')[0];
            $(parent_pane).find('#customer_bill_method').val('view');
            $(parent_pane).find('#customer_bill_id').val(lid);
            dofc_customer_bill_init();
            $(parent_pane).modal('show');
        });

    });
    
    
</script>