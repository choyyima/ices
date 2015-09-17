<script>
    var rma_id = '<?php echo $rma_id ?>';
    var rma_code = '<?php echo $rma_code ?>';
    
    delivery_order_init();
    delivery_order_bind_event();
    
    delivery_order_after_submit = function(){
        $('#modal_delivery_order').modal('hide');
        window.location.href = APP_WINDOW.current_url();
    }
 
    
    $('#delivery_order_new').on('click',function(){
       
       var parent_pane = $('#modal_delivery_order').find('.modal-body')[0];
       $(parent_pane).find('#delivery_order_method').val('add');
       $(parent_pane).find('#delivery_order_id').val('');
       delivery_order_components_prepare();
      
       $(parent_pane).find('#delivery_order_reference')
            .select2('data',{
                id:rma_id,
                text:rma_code,
                reference_type:'rma',
                reference_code:rma_code,
                reference_type_name:'Return Merchandise Authorization'}
            ).change(); 
       
       $('#modal_delivery_order').find('#delivery_order_reference').select2('disable');
       
       //$(parent_pane).modal('show');
    });
    
    var llinks = $('#delivery_order_table').find('a');
    $.each(llinks, function(key, val){
        
        $(val).off('click');
        $(val).on('click',function(e){
            e.preventDefault();
            var lid = $(val).attr('href');
            var parent_pane = $('#modal_delivery_order')[0];
            $(parent_pane).find('#delivery_order_method').val('view');
            $(parent_pane).find('#delivery_order_id').val(lid);            
            delivery_order_components_prepare();
            $('#modal_delivery_order').find('#delivery_order_reference').select2('disable');
            //if($('#modal_delivery_order').find('#delivery_order_delivery_order_status').select2('val') === 'D'){
            //    $('#modal_delivery_order').find('#delivery_order_print').show();
            //}
            $(parent_pane).modal('show');
        });

    });
    
</script>