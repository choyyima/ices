<script>
    var reference_id = '<?php echo $reference_id ?>';
    var reference_text = '<?php echo $reference_text ?>';
    var reference_type = '<?php echo $reference_type ?>';
    var customer_id = '<?php echo $customer_id ?>';
    
    sales_receipt_allocation_init();
    sales_receipt_allocation_bind_event();
    
    sales_receipt_allocation_after_submit = function(){
        $('#modal_sales_receipt_allocation').modal('hide');
        window.location.href = APP_WINDOW.current_url();
    }
    
    var customer_bill_sales_receipt_allocation_init = function(){
        var parent_pane = $('#modal_sales_receipt_allocation')[0];
        sales_receipt_allocation_components_prepare();
        $('#modal_sales_receipt_allocation').find('#sales_receipt_allocation_sales_receipt').select2('disable');
    }
    
    $('#sales_receipt_allocation_new').on('click',function(){
        var parent_pane = $('#modal_sales_receipt_allocation')[0];
        $(parent_pane).find('#sales_receipt_allocation_method').val('add');
        $(parent_pane).find('#sales_receipt_allocation_id').val('');
        $(parent_pane).find('#sales_receipt_allocation_customer_id').val(customer_id);
        customer_bill_sales_receipt_allocation_init();
        $(parent_pane).find('#sales_receipt_allocation_sales_receipt').select2('enable');
        $(parent_pane).find('#sales_receipt_allocation_reference')
            .select2('data',{id:reference_id,text:reference_text,reference_type:reference_type}).change(); 
       $(parent_pane).find('#sales_receipt_allocation_reference').select2('disable');
       
    });
    
    var llinks = $('#sales_receipt_allocation_table').find('a');
    $.each(llinks, function(key, val){
        
        $(val).off('click');
        $(val).on('click',function(e){
            e.preventDefault();
            var lid = $(val).attr('href');
            var parent_pane = $('#modal_sales_receipt_allocation')[0];
            $(parent_pane).find('#sales_receipt_allocation_method').val('view');
            $(parent_pane).find('#sales_receipt_allocation_id').val(lid);
            customer_bill_sales_receipt_allocation_init();
            $(parent_pane).modal('show');
        });

    });
    
</script>