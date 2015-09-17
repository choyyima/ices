<script>
    var reference_id = '<?php echo $reference_id ?>';
    var reference_text = '<?php echo $reference_text ?>';
    var reference_type = '<?php echo $reference_type ?>';
    
    
    mf_work_process_init();
    mf_work_process_bind_event();
    
    mf_work_process_after_submit = function(){
        $('#modal_mf_work_process').modal('hide');
        window.location.href = APP_WINDOW.current_url();
    }
    
    var mfwo_mf_work_process_init = function(){
        var parent_pane = $('#modal_mf_work_process')[0];
        mf_work_process_components_prepare();
        $('#modal_mf_work_process').find('#mf_work_process_reference').select2('disable');
    }
    
    $('#mf_work_process_new').on('click',function(){
        var parent_pane = $('#modal_mf_work_process')[0];
        $(parent_pane).find('#mf_work_process_method').val('add');
        $(parent_pane).find('#mf_work_process_id').val('');
        mfwo_mf_work_process_init();
        
        $(parent_pane).find('#mf_work_process_reference')
            .select2('data',{id:reference_id,text:reference_text,reference_type:reference_type}).change(); 
        $(parent_pane).find('#mf_work_process_reference').select2('disable');
        
        
    });
    
    var llinks = $('#mf_work_process_view_table').find('a');
    $.each(llinks, function(key, val){
        
        $(val).off('click');
        $(val).on('click',function(e){
            e.preventDefault();
            var lid = $(val).attr('href');
            var parent_pane = $('#modal_mf_work_process')[0];
            $(parent_pane).find('#mf_work_process_method').val('view');
            $(parent_pane).find('#mf_work_process_id').val(lid);
            mfwo_mf_work_process_init();
            $(parent_pane).modal('show');
        });

    });
    
</script>