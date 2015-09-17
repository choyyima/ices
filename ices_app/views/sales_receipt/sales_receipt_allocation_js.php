<script>
    var sales_receipt_id = '<?php echo $sales_receipt_id ?>';
    var sales_receipt_text = '<?php echo $sales_receipt_text ?>';
    var customer_id = '<?php echo $customer_id ?>';
    
    sales_receipt_allocation_init();
    sales_receipt_allocation_bind_event();
    
    sales_receipt_allocation_after_submit = function(){
        $('#modal_sales_receipt_allocation').modal('hide');
        window.location.href = APP_WINDOW.current_url();
    }
    
    var sales_receipt_sales_receipt_allocation_init = function(){
        var parent_pane = $('#modal_sales_receipt_allocation')[0];
        sales_receipt_allocation_components_prepare();
        $('#modal_sales_receipt_allocation').find('#sales_receipt_allocation_sales_receipt').select2('disable');
    }
    
    $('#sales_receipt_allocation_new').on('click',function(){
        var parent_pane = $('#modal_sales_receipt_allocation')[0];
        $(parent_pane).find('#sales_receipt_allocation_method').val('add');
        $(parent_pane).find('#sales_receipt_allocation_id').val('');
        $(parent_pane).find('#sales_receipt_allocation_customer_id').val(customer_id);
        sales_receipt_sales_receipt_allocation_init();
        $(parent_pane).find('#sales_receipt_allocation_sales_receipt')
            .select2('data',{id:sales_receipt_id,text:sales_receipt_text}).change(); 
       $(parent_pane).find('#sales_receipt_allocation_sales_receipt').select2('disable');
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
            sales_receipt_sales_receipt_allocation_init();
            $(parent_pane).modal('show');
        });

    });
    
    
</script>