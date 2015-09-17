<script>
    var reference_id = '<?php echo $reference_id ?>';
    var reference_text = '<?php echo $reference_text ?>';
    var reference_type = '<?php echo $reference_type ?>';
    
    receive_product_init();
    receive_product_bind_event();
    
    receive_product_after_submit = function(){
        $('#modal_receive_product').modal('hide');
        window.location.href = APP_WINDOW.current_url();
    }
    
    var purchase_invoice_receive_product_init = function(){
        var parent_pane = $('#modal_receive_product')[0];
        receive_product_components_prepare();
        $('#modal_receive_product').find('#receive_product_reference').select2('disable');
    }
    
    $('#receive_product_new').on('click',function(){
        var parent_pane = $('#modal_receive_product')[0];
        $(parent_pane).find('#receive_product_method').val('add');
        $(parent_pane).find('#receive_product_id').val('');
        purchase_invoice_receive_product_init();
        $(parent_pane).find('#receive_product_reference')
            .select2('data',{id:reference_id,text:reference_text, reference_type:reference_type}).change(); 
       $(parent_pane).find('#receive_product_reference').select2('disable');
    });
    
    var llinks = $('#receive_product_view_table').find('a');
    $.each(llinks, function(key, val){
        
        $(val).off('click');
        $(val).on('click',function(e){
            e.preventDefault();
            var lid = $(val).attr('href');
            var parent_pane = $('#modal_receive_product')[0];
            $(parent_pane).find('#receive_product_method').val('view');
            $(parent_pane).find('#receive_product_id').val(lid);
            purchase_invoice_receive_product_init();
            $(parent_pane).modal('show');
        });

    });
    
</script>