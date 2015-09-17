<script>
    
    intake_final_init();
    intake_final_bind_event();
    
    intake_final_after_submit = function(){
        $('#modal_intake_final').modal('hide');
        window.location.href = APP_WINDOW.current_url();
    }
    
    var sales_pos_intake_final_init = function(){
        var parent_pane = $('#modal_intake_final')[0];
        intake_final_components_prepare();
        $('#modal_intake_final').find('#intake_final_reference').select2('disable');
    }
    
    $('#sales_pos_new_intake_final').on('click',function(){
        var parent_pane = $('#modal_intake_final')[0];
        
        var reference_id = $('#sales_pos_id').val();
        var reference_text = $('#sales_pos_code').val();
        var reference_type = 'sales_invoice';
        
        $(parent_pane).find('#intake_final_method').val('add');
        $(parent_pane).find('#intake_final_id').val('');
        sales_pos_intake_final_init();
        $(parent_pane).find('#intake_final_reference')
            .select2('data',{id:reference_id,text:reference_text, reference_type:reference_type}).change(); 
       $(parent_pane).find('#intake_final_reference').select2('disable');
       $('#modal_intake_final').modal('show');
    });
    
    var llinks = $('#intake_final_view_table').find('a');
    $.each(llinks, function(key, val){
        
        $(val).off('click');
        $(val).on('click',function(e){
            e.preventDefault();
            var lid = $(val).attr('href');
            var parent_pane = $('#modal_intake_final')[0];
            $(parent_pane).find('#intake_final_method').val('view');
            $(parent_pane).find('#intake_final_id').val(lid);
            sales_pos_intake_final_init();
            $(parent_pane).modal('show');
        });

    });
</script>