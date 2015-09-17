<script>
    var purchase_receipt_id = '<?php echo $purchase_receipt_id ?>';
    var purchase_receipt_code = '<?php echo $purchase_receipt_code ?>';
    var purchase_receipt_supplier_id = '<?php echo $purchase_receipt_supplier_id ?>';
    var purchase_receipt_supplier_code = '<?php echo $purchase_receipt_supplier_code ?>';
    
    purchase_receipt_allocation_init();
    purchase_receipt_allocation_bind_event();
    
    purchase_receipt_allocation_after_submit = function(){
        $('#modal_purchase_receipt_allocation').modal('hide');
        window.location.href = APP_WINDOW.current_url();
    }
    
    var purchase_receipt_purchase_receipt_allocation_init = function(){
        var parent_pane = $('#modal_purchase_receipt_allocation')[0];
        purchase_receipt_allocation_components_prepare();
        $('#purchase_receipt_allocation_supplier').select2('data',{id:purchase_receipt_supplier_id,text:purchase_receipt_supplier_code}).change();
        $('#modal_purchase_receipt_allocation').find('#purchase_receipt_allocation_purchase_receipt').select2('disable');
    }
    
    $('#purchase_receipt_allocation_new').on('click',function(){
        var parent_pane = $('#modal_purchase_receipt_allocation')[0];
        $(parent_pane).find('#purchase_receipt_allocation_method').val('Add');
        $(parent_pane).find('#purchase_receipt_allocation_id').val('');
        purchase_receipt_purchase_receipt_allocation_init();
        $(parent_pane).find('#purchase_receipt_allocation_purchase_receipt')
            .select2('data',{id:purchase_receipt_id,text:purchase_receipt_code}).change(); 
       
    });
    
    var llinks = $('#purchase_receipt_allocation_table').find('a');
    $.each(llinks, function(key, val){
        
        $(val).off('click');
        $(val).on('click',function(e){
            e.preventDefault();
            var lid = $(val).attr('href');
            var parent_pane = $('#modal_purchase_receipt_allocation')[0];
            $(parent_pane).find('#purchase_receipt_allocation_method').val('Edit');
            $(parent_pane).find('#purchase_receipt_allocation_id').val(lid);
            purchase_receipt_purchase_receipt_allocation_init();
            
            //purchase_receipt_allocation_components_prepare();
            //$('#purchase_receipt_allocation_supplier_id').val(purchase_receipt_supplier_id);
            //$('#modal_purchase_receipt_allocation').find('#purchase_receipt_allocation_purchase_receipt').select2('disable');
            $(parent_pane).modal('show');
        });

    });
    
</script>