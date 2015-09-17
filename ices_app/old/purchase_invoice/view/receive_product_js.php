<script>
    var purchase_invoice_id = '<?php echo $purchase_invoice_id ?>';
    var purchase_invoice_code = '<?php echo $purchase_invoice_code ?>';
    
    receive_product_init();
    receive_product_bind_event();
    
    receive_product_after_submit = function(){
        $('#modal_receive_product').modal('hide');
        window.location.href = APP_WINDOW.current_url();
    }
 
    
    $('#receive_product_new').on('click',function(){
       
       var parent_pane = $('#modal_receive_product').find('.modal-body')[0];
       $(parent_pane).find('#receive_product_method').val('add');
       $(parent_pane).find('#receive_product_id').val('');
       receive_product_components_prepare();
      
       $(parent_pane).find('#receive_product_reference')
            .select2('data',{id:purchase_invoice_id,text:purchase_invoice_code
               ,reference_type:'purchase_invoice'
               ,reference_code:purchase_invoice_code
               ,reference_type_name:'Purchase Invoice'}).change(); 
       $('#modal_receive_product').find('#receive_product_reference').select2('disable');
       
       //$(parent_pane).modal('show');
    });
    
    var llinks = $('#receive_product_table').find('a');
    $.each(llinks, function(key, val){
        
        $(val).off('click');
        $(val).on('click',function(e){
            e.preventDefault();
            var lid = $(val).attr('href');
            var parent_pane = $('#modal_receive_product')[0];
            $(parent_pane).find('#receive_product_method').val('view');
            $(parent_pane).find('#receive_product_id').val(lid);            
            receive_product_components_prepare();
            $('#modal_receive_product').find('#receive_product_reference').select2('disable');
            //$('#modal_receive_product').find('#receive_product_receive_product_type').select2('disable');
            //if($('#modal_receive_product').find('#receive_product_receive_product_status').select2('val') === 'D'){
            //    $('#modal_receive_product').find('#receive_product_print').show();
            //}
            $(parent_pane).modal('show');
        });

    });
    
</script>