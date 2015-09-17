<script>
    var reference_id = '<?php echo $reference_id ?>';
    var reference_text = '<?php echo $reference_text ?>';
    var reference_type = '<?php echo $reference_type ?>';
    var supplier_id = '<?php echo $supplier_id ?>';
    
    purchase_receipt_allocation_init();
    purchase_receipt_allocation_bind_event();
    
    purchase_receipt_allocation_after_submit = function(){
        $('#modal_purchase_receipt_allocation').modal('hide');
        window.location.href = APP_WINDOW.current_url();
    }
    
    var purchase_invoice_purchase_receipt_allocation_init = function(){
        var parent_pane = $('#modal_purchase_receipt_allocation')[0];
        purchase_receipt_allocation_components_prepare();
        $('#modal_purchase_receipt_allocation').find('#purchase_receipt_allocation_purchase_receipt').select2('disable');
    }
    
    $('#purchase_receipt_allocation_new').on('click',function(){
        var parent_pane = $('#modal_purchase_receipt_allocation')[0];
        $(parent_pane).find('#purchase_receipt_allocation_method').val('add');
        $(parent_pane).find('#purchase_receipt_allocation_id').val('');
        $(parent_pane).find('#purchase_receipt_allocation_supplier_id').val(supplier_id);
        purchase_invoice_purchase_receipt_allocation_init();
        $(parent_pane).find('#purchase_receipt_allocation_purchase_receipt').select2('enable');
        $(parent_pane).find('#purchase_receipt_allocation_reference')
            .select2('data',{id:reference_id,text:reference_text,reference_type:reference_type}).change(); 
       $(parent_pane).find('#purchase_receipt_allocation_reference').select2('disable');
       
    });
    
    var llinks = $('#purchase_receipt_allocation_table').find('a');
    $.each(llinks, function(key, val){
        
        $(val).off('click');
        $(val).on('click',function(e){
            e.preventDefault();
            var lid = $(val).attr('href');
            var parent_pane = $('#modal_purchase_receipt_allocation')[0];
            $(parent_pane).find('#purchase_receipt_allocation_method').val('view');
            $(parent_pane).find('#purchase_receipt_allocation_id').val(lid);
            purchase_invoice_purchase_receipt_allocation_init();
            $(parent_pane).modal('show');
        });

    });
    
</script>