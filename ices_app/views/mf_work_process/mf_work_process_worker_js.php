<script>
var mf_work_process_worker_methods = {
    
}

var mf_work_process_worker_bind_event = function(){
    
    mf_work_process_worker_table_method.setting.func_new_row_validation= function(iopt){
        var lmodule_type = mf_work_process_methods.module_type_get();
        var lresult = {success:1,msg:[]};
        var success = 0;
        var lrow = iopt.tr;
        var lname = $(lrow).find('[col_name="name"] input').val();
                
        
        if(lname.replace(/[ ]/g,'') !== ''){
            success = 1;
        }
        lresult.success = success;
        return lresult;
    };
    
    mf_work_process_worker_table_method.setting.func_get_data_table = function(){
        var lparent_pane = mf_work_process_parent_pane;
        var lprefix_id = mf_work_process_component_prefix_id;
        var lresult = [];
        var lmodule_type = mf_work_process_methods.module_type_get();
        
        var ltbody = $(lparent_pane).find(lprefix_id+'_worker_table tbody')[0];
        $.each($(ltbody).find('tr'), function(lidx, lrow){
            var ltemp = {};            
                      
            var lname = '';
            if(lidx < ($(ltbody).find('tr').length - 1)){
                lname = $(lrow).find('[col_name] span').text();
                
            }
            else{
                lname = $(lrow).find('[col_name="name"] input').val();
            }
            
            
            if(
                lname.replace(/[ ]/g,'') !== '' 
            ){
                ltemp = {
                    name: lname,
                };

                lresult.push(ltemp);
            }

            
        });
        return lresult;
    };
    
    mf_work_process_worker_table_method.setting.func_row_bind_event = function(iopt){
        var lparent_pane = mf_work_process_parent_pane;
        var lprefix_id = mf_work_process_component_prefix_id;
        var lrow = iopt.tr;
        var ltbody = iopt.tbody;
        var ldata_row = iopt.data_row;
        var lmodule_type = mf_work_process_methods.module_type_get();
        var lmethod = $(lparent_pane).find(lprefix_id+'_method').val();
        
        <?php // --- Show and Hide phase --- ?>
        
        if(lmethod === 'add'){
           
        }
        else if(lmethod === 'view'){
            
        }
        
        <?php // --- End Of Show and Hide phase --- ?>
        
        if(Object.keys(ldata_row).length === 0){
            
        }
    }

    mf_work_process_worker_table_method.setting.func_row_transform_comp_on_new_row = function(iopt){
        var lrow = iopt.tr;
        
        var lname = $(lrow).find('[col_name="name"] input').val();
        $(lrow).find('[col_name="name"]')[0].innerHTML = '<span>'+lname+'</span>';
        
    }

    mf_work_process_worker_table_method.setting.func_row_data_assign = function(iopt){
        var lparent_pane = mf_work_process_parent_pane;
        var lprefix_id = mf_work_process_component_prefix_id;
        var ldata_row = iopt.data_row;
        var lrow = iopt.tr;
        var lmodule_type = mf_work_process_methods.module_type_get();
        var lmethod = $(lparent_pane).find(lprefix_id+'_method').val();
        if(Object.keys(ldata_row).length > 0){
            $(lrow).find('[col_name="name"]')[0].innerHTML  = '<span>'+ldata_row.name+'</span>';
            $(lrow).find('[col_name="action"]')[0].innerHTML  = '<span></span>';
        }
    }
}
</script>