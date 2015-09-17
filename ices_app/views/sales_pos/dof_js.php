<script>
    
    dof_init();
    dof_bind_event();
    
    dof_after_submit = function(){
        $('#modal_dof').modal('hide');
        window.location.href = APP_WINDOW.current_url();
    }
    
    var sales_pos_dof_init = function(){
        var parent_pane = $('#modal_dof')[0];
        dof_components_prepare();
        $('#modal_dof').find('#dof_reference').select2('disable');
    }
    
    $('#sales_pos_new_dof').on('click',function(){
        var parent_pane = $('#modal_dof')[0];
        
        var reference_id = $('#sales_pos_id').val();
        var reference_text = $('#sales_pos_code').val();
        var reference_type = 'sales_invoice';
        
        $(parent_pane).find('#dof_method').val('add');
        $(parent_pane).find('#dof_id').val('');
        sales_pos_dof_init();
        $(parent_pane).find('#dof_reference')
            .select2('data',{id:reference_id,text:reference_text, reference_type:reference_type}).change(); 
       $(parent_pane).find('#dof_reference').select2('disable');
       $('#modal_dof').modal('show');
    });
    
    var llinks = $('#dof_view_table').find('a');
    $.each(llinks, function(key, val){
        
        $(val).off('click');
        $(val).on('click',function(e){
            e.preventDefault();
            var lid = $(val).attr('href');
            var parent_pane = $('#modal_dof')[0];
            $(parent_pane).find('#dof_method').val('view');
            $(parent_pane).find('#dof_id').val(lid);
            sales_pos_dof_init();
            $(parent_pane).modal('show');
        });

    });
</script>