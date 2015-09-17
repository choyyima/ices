<script>
    var purchase_invoice_id = '<?php echo $purchase_invoice_id ?>';
    var purchase_invoice_code = '<?php echo $purchase_invoice_code ?>';
    
    rma_init();
    rma_bind_event();
    
    rma_after_submit = function(){
        $('#modal_rma').modal('hide');
        window.location.href = APP_WINDOW.current_url();
    }
 
    
    $('#rma_new').on('click',function(){
       
       var parent_pane = $('#modal_rma').find('.modal-body')[0];
       $(parent_pane).find('#rma_method').val('add');
       $(parent_pane).find('#rma_id').val('');
       rma_components_prepare();
      
       $(parent_pane).find('#rma_reference')
            .select2('data',{
                id:purchase_invoice_id,
                text:purchase_invoice_code,
                reference_type:'purchase_invoice',
                reference_code:purchase_invoice_code,
                reference_type_name:'Purchase Invoice'}
            ).change(); 
       
       $('#modal_rma').find('#rma_reference').select2('disable');
       
       //$(parent_pane).modal('show');
    });
    
    var llinks = $('#rma_table').find('a');
    $.each(llinks, function(key, val){
        
        $(val).off('click');
        $(val).on('click',function(e){
            e.preventDefault();
            var lid = $(val).attr('href');
            var parent_pane = $('#modal_rma')[0];
            $(parent_pane).find('#rma_method').val('view');
            $(parent_pane).find('#rma_id').val(lid);            
            rma_components_prepare();
            $('#modal_rma').find('#rma_reference').select2('disable');
            //if($('#modal_rma').find('#rma_rma_status').select2('val') === 'D'){
            //    $('#modal_rma').find('#rma_print').show();
            //}
            $(parent_pane).modal('show');
        });

    });
    
</script>