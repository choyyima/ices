<script>
    var u_group_parent_pane = $('<?php echo $detail_tab; ?>')[0];
    var u_group_ajax_url = null;
    var u_group_index_url = null;
    var u_group_view_url = null;
    var u_group_window_scroll = null;
    var u_group_data_support_url = null;
    var u_group_common_ajax_listener = null;
    var u_group_component_prefix_id = '';
    
    var u_group_init = function(){
        var parent_pane = u_group_parent_pane;

        u_group_ajax_url = '<?php echo $ajax_url ?>';
        u_group_index_url = '<?php echo $index_url ?>';
        u_group_view_url = '<?php echo $view_url ?>';
        u_group_window_scroll = '<?php echo $window_scroll; ?>';
        u_group_data_support_url = '<?php echo $data_support_url; ?>';
        u_group_common_ajax_listener = '<?php echo $common_ajax_listener; ?>';
        u_group_component_prefix_id = '#<?php echo $component_prefix_id; ?>';
        
    }

    var u_group_after_submit = function(){

    }
    
    var u_group_data ={
        
    }
    
    var u_group_methods = {
        
        hide_all:function(){
            var lparent_pane = u_group_parent_pane;
            var lprefix_id = u_group_component_prefix_id;
            $(lparent_pane).find('.hide_all').hide();
        },
        disable_all:function(){
            var lparent_pane = u_group_parent_pane;
            var lcomponents = $(lparent_pane).find('.disable_all');
            APP_COMPONENT.disable_all(lparent_pane);
        },
        
        show_hide: function(){
            var lparent_pane = u_group_parent_pane;
            var lprefix_id = u_group_component_prefix_id;
            var lmethod = $(lparent_pane).find('#u_group_method').val();            
            
            u_group_methods.hide_all();
            
            switch(lmethod){
                case 'add':
                case 'view':
                    $(lparent_pane).find(lprefix_id+'_app_name').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find(lprefix_id+'_name').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find(lprefix_id+'_u_group_status').closest('div [class*="form-group"]').show();
                    break;
            }
            
            switch(lmethod){
                case 'add':
                    
                    break;
                case 'view':
                    $(lparent_pane).find(lprefix_id+'_btn_delete').show();
                    break;
            }
        },        
        enable_disable: function(){
            var lparent_pane = u_group_parent_pane;
            var lprefix_id = u_group_component_prefix_id;
            var lmethod = $(lparent_pane).find(lprefix_id+'_method').val();  
            u_group_methods.disable_all();
            
            switch(lmethod){
                case "add":
                case 'view':
                    $(lparent_pane).find(lprefix_id+'_app_name').select2('enable');
                    $(lparent_pane).find(lprefix_id+"_name").prop("disabled",false);
                    $(lparent_pane).find(lprefix_id+'_u_group_status').select2('enable');
                    break;
            }
        },
        reset_all:function(){
            var lparent_pane = u_group_parent_pane;
            var lprefix_id = u_group_component_prefix_id;
            
            $(lparent_pane).find(lprefix_id+'_app_name').select2('data',$(lparent_pane).find(lprefix_id+'_app_name').select2_data_list()[0]);
            $(lparent_pane).find(lprefix_id+'_controller_name').val('');
            $(lparent_pane).find(lprefix_id+'_method_name').val('');
            
            APP_FORM.status.default_status_set(
                'u_group',
                $(lparent_pane).find(lprefix_id+'_u_group_status')
            );
           
        },
        submit:function(){
            var lparent_pane = u_group_parent_pane;
            var lprefix_id = u_group_component_prefix_id;
            var ajax_url = u_group_index_url;
            var lmethod = $(lparent_pane).find(lprefix_id+"_method").val();
            var u_group_id = $(lparent_pane).find(lprefix_id+"_id").val();        
            var json_data = {
                ajax_post:true,
                u_group:{},
                message_session:true
            };

            switch(lmethod){
                case 'add':
                case 'view':
                    json_data.u_group.app_name = $(lparent_pane).find(lprefix_id+"_app_name").select2('val');
                    json_data.u_group.name = $(lparent_pane).find(lprefix_id+"_name").val();
                    json_data.u_group.u_group_status = $(lparent_pane).find(lprefix_id+"_u_group_status").select2('val');
                    break;
            }
            
            var lajax_method='';
            switch(lmethod){
                case 'add':
                    lajax_method = 'u_group_add';
                    break;
                case 'view':
                    lajax_method = $(lparent_pane).find(lprefix_id+'_u_group_status').select2('data').method;
                    break;
            }
            ajax_url +=lajax_method+'/'+u_group_id;
            
            result = APP_DATA_TRANSFER.submit(ajax_url,json_data);
            if(result.success ===1){
                $(u_group_parent_pane).find(lprefix_id+'_id').val(result.trans_id);
                if(u_group_view_url !==''){
                    var url = u_group_view_url+result.trans_id;
                    window.location.href=url;
                }
                else{
                    u_group_after_submit();
                }
            }
        },
    }

    var u_group_bind_event = function(){
        var lparent_pane = u_group_parent_pane;
        var lprefix_id = u_group_component_prefix_id;
        
        $(lparent_pane).find(lprefix_id+'_btn_submit').off('click');
        APP_COMPONENT.button.submit.set($(lparent_pane).find(lprefix_id+'_btn_submit'),{
            parent_pane:lparent_pane,
            module_method:u_group_methods
        });
        
        
    }
    
    var u_group_components_prepare= function(){
        var lparent_pane = u_group_parent_pane;
        var lprefix_id = u_group_component_prefix_id;
        var method = $(u_group_parent_pane).find(lprefix_id+"_method").val();
                
        var u_group_data_set = function(){
            var lparent_pane = u_group_parent_pane;
            var lprefix_id = u_group_component_prefix_id;
            switch(method){
                case "add":
                    u_group_methods.reset_all();
                    break;
                case "view":
                    var u_group_id = $(u_group_parent_pane).find(lprefix_id+"_id").val();
                    var json_data={data:u_group_id};
                    var lresponse = APP_DATA_TRANSFER.ajaxPOST(u_group_data_support_url+"u_group_get",json_data).response;
                    if(lresponse != []){
                        var lu_group = lresponse.u_group;
                        $(lparent_pane).find(lprefix_id+'_app_name').select2('data',lu_group.app_name);
                        $(lparent_pane).find(lprefix_id+'_name').val(lu_group.name);
                        
                        $(lparent_pane).find(lprefix_id+'_u_group_status')
                            .select2('data',lu_group.u_group_status
                        ).change();
                            
                        $(lparent_pane).find(lprefix_id+'_u_group_status')
                            .select2({data:lresponse.u_group_status_list});
                        
                        
                    };
                    break;            
            }
        }
    
        u_group_methods.enable_disable();
        u_group_methods.show_hide();
        u_group_data_set();
    }
    
</script>