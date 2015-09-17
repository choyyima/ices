<script>
var comp_mail_manager_mail_list_methods = {
    
}

var comp_mail_manager_mail_list_bind_event = function(){
    var lparent_pane = comp_mail_manager_parent_pane;
    var lprefix_id = comp_mail_manager_component_prefix_id;
    
    comp_mail_manager_mail_list_table_method.setting.func_new_row_validation= function(iopt){
        var lmodule_type = comp_mail_manager_methods.module_type_get();
        var lresult = {success:1,msg:[]};
        var success = 0;
        var lrow = iopt.tr;
        
        lresult.success = success;
        return lresult;
    };
    
    comp_mail_manager_mail_list_table_method.setting.func_get_data_table = function(){
        var lparent_pane = comp_mail_manager_parent_pane;
        var lprefix_id = comp_mail_manager_component_prefix_id;
        var lresult = [];
        var ltable = comp_mail_manager_mail_list_table_method.setting.table_get();
        
        var ltbody = $(ltable).find(' tbody')[0];
        $.each($(ltbody).find('tr'), function(lidx, lrow){
            lresult.push({
                id: $(lrow).find('[col_name="id"] div').text(),
                username: $(lrow).find('[col_name="username"] input').val(),
                password: $(lrow).find('[col_name="password"] input').val(),
            });
        });
        return lresult;
    };
    
    comp_mail_manager_mail_list_table_method.setting.func_row_bind_event = function(iopt){
        var lparent_pane = comp_mail_manager_parent_pane;
        var lprefix_id = comp_mail_manager_component_prefix_id;
        var lrow = iopt.tr;
        var ltbody = iopt.tbody;
        var ldata_row = iopt.data_row;
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

    comp_mail_manager_mail_list_table_method.setting.func_row_transform_comp_on_new_row = function(iopt){
    }

    comp_mail_manager_mail_list_table_method.setting.func_row_data_assign = function(iopt){
        var lparent_pane = comp_mail_manager_parent_pane;
        var lprefix_id = comp_mail_manager_component_prefix_id;
        var ldata_row = iopt.data_row;
        var lrow = iopt.tr;
        var lmethod = $(lparent_pane).find(lprefix_id+'_method').val();
        

        if(Object.keys(ldata_row).length > 0){
            if(lmethod === 'add'){
                
            }
            else if (lmethod === 'view'){
                $(lrow).find('[col_name="id"]')[0].innerHTML = '<div>'+ldata_row.id+'</div>';
                $(lrow).find('[col_name="code"]')[0].innerHTML = '<div>'+ldata_row.code+'</div>';
                $(lrow).find('[col_name="name"]')[0].innerHTML = '<div>'+ldata_row.name+'</div>';
                $(lrow).find('[col_name="username"] input').val(ldata_row.username);
                $(lrow).find('[col_name="password"] input').val(ldata_row.password);
            }
        }

    }
    
}
</script>