<script>
    var product_id = '<?php echo $product_id ?>';
    var product_name = '<?php echo $product_name ?>';

    $('#modal_product_unit_conversion').find('.modal-title>strong').text(product_name+' - Unit Conversion');
    product_unit_conversion_init();
    product_unit_conversion_bind_event();
    
    product_unit_conversion_after_submit = function(){
        $('#modal_product_unit_conversion').modal('hide');
        window.location.href = APP_WINDOW.current_url();
    }
 
    
    $('#product_unit_conversion_btn_new').on('click',function(){
       
       var parent_pane = $('#modal_product_unit_conversion').find('.modal-body')[0];
       $(parent_pane).find('#product_unit_conversion_method').val('add');
       $(parent_pane).find('#product_unit_conversion_id').val('');
       $(parent_pane).find('#product_unit_conversion_reference').val(product_id); 
       product_unit_conversion_components_prepare();
      
       
    });
        
    var lbuttons = $('#product_unit_conversion_table').find('button');
    $.each($(lbuttons),function(key, val){
        $(val).on('click',function(){
            var delete_url = $(this).attr("delete_url");
            var form = $("#modal_confirmation_form")[0];
            $(form).attr("action",delete_url);
            $(form).closest('.modal').modal('show');
        });
    });
    
    var llinks = $('#product_unit_conversion_table').find('a');
    $.each(llinks, function(key, val){
        
        $(val).off('click');
        $(val).on('click',function(e){
            e.preventDefault();
            var lid = $(val).attr('href');
            var parent_pane = $('#modal_product_unit_conversion')[0];
            $(parent_pane).find('#product_unit_conversion_method').val('view');
            $(parent_pane).find('#product_unit_conversion_id').val(lid);            
            product_unit_conversion_components_prepare();
            $('#modal_product_unit_conversion').find('#product_unit_conversion_reference').select2('disable');
            $(parent_pane).modal('show');
        });

    });
    
</script>