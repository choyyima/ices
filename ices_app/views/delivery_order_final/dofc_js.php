<script>
    var reference_id = '<?php echo $reference_id ?>';
    var reference_text = '<?php echo $reference_text ?>';
    var reference_type = '<?php echo $reference_type ?>';
    
    
    dofc_init();
    dofc_bind_event();
    
    dofc_after_submit = function(){
        $('#modal_dofc').modal('hide');
        window.location.href = APP_WINDOW.current_url();
    }
    
    var dof_dofc_init = function(){
        var parent_pane = $('#modal_dofc')[0];
        dofc_components_prepare();
        $(parent_pane).find('#dofc_reference').select2('disable');
    }
    
    $('#dofc_btn_new').on('click',function(){
        var parent_pane = $('#modal_dofc')[0];
        $(parent_pane).find('#dofc_method').val('add');
        $(parent_pane).find('#dofc_id').val('');
        dof_dofc_init();
        
        $(parent_pane).find('#dofc_reference')
            .select2('data',{id:reference_id,text:reference_text,reference_type:reference_type}).change(); 
        $(parent_pane).find('#dofc_reference').select2('disable');
        
        
    });
    
    var llinks = $('#dofc_view_table').find('a');
    $.each(llinks, function(key, val){
        
        $(val).off('click');
        $(val).on('click',function(e){
            e.preventDefault();
            var lid = $(val).attr('href');
            var parent_pane = $('#modal_dofc')[0];
            $(parent_pane).find('#dofc_method').val('view');
            $(parent_pane).find('#dofc_id').val(lid);
            dof_dofc_init();
            $(parent_pane).modal('show');
        });

    });
    
</script>