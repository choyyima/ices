<script>
    var security_controller_parent_pane = $('<?php echo $detail_tab; ?>')[0];
    var security_controller_ajax_url = null;
    var security_controller_index_url = null;
    var security_controller_view_url = null;
    var security_controller_window_scroll = null;
    var security_controller_data_support_url = null;
    var security_controller_common_ajax_listener = null;
    var security_controller_component_prefix_id = '';
    
    var security_controller_init = function(){
        var parent_pane = security_controller_parent_pane;

        security_controller_ajax_url = '<?php echo $ajax_url ?>';
        security_controller_index_url = '<?php echo $index_url ?>';
        security_controller_view_url = '<?php echo $view_url ?>';
        security_controller_window_scroll = '<?php echo $window_scroll; ?>';
        security_controller_data_support_url = '<?php echo $data_support_url; ?>';
        security_controller_common_ajax_listener = '<?php echo $common_ajax_listener; ?>';
        security_controller_component_prefix_id = '#<?php echo $component_prefix_id; ?>';
        
    }

    var security_controller_after_submit = function(){

    }
    
    var security_controller_data ={
        
    }
    
    var security_controller_methods = {
        
        hide_all:function(){
            var lparent_pane = security_controller_parent_pane;
            var lprefix_id = security_controller_component_prefix_id;
            $(lparent_pane).find('.hide_all').hide();
        },
        disable_all:function(){
            var lparent_pane = security_controller_parent_pane;
            var lcomponents = $(lparent_pane).find('.disable_all');
            APP_COMPONENT.disable_all(lparent_pane);
        },
        
        show_hide: function(){
            var lparent_pane = security_controller_parent_pane;
            var lprefix_id = security_controller_component_prefix_id;
            var lmethod = $(lparent_pane).find('#security_controller_method').val();            
            
            security_controller_methods.hide_all();
            
            switch(lmethod){
                case 'add':
                case 'view':
                    $(lparent_pane).find(lprefix_id+'_app_name').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find(lprefix_id+'_controller_name').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find(lprefix_id+'_method_name').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find(lprefix_id+'_security_controller_status').closest('div [class*="form-group"]').show();
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
            var lparent_pane = security_controller_parent_pane;
            var lprefix_id = security_controller_component_prefix_id;
            var lmethod = $(lparent_pane).find(lprefix_id+'_method').val();  
            security_controller_methods.disable_all();
            
            switch(lmethod){
                case "add":
                case 'view':
                    $(lparent_pane).find(lprefix_id+'_app_name').select2('enable');
                    $(lparent_pane).find(lprefix_id+"_controller_name").prop("disabled",false);
                    $(lparent_pane).find(lprefix_id+"_method_name").prop("disabled",false);
                    $(lparent_pane).find(lprefix_id+'_security_controller_status').select2('enable');
                    break;
            }
        },
        reset_all:function(){
            var lparent_pane = security_controller_parent_pane;
            var lprefix_id = security_controller_component_prefix_id;
            
            $(lparent_pane).find(lprefix_id+'_app_name').select2('data',$(lparent_pane).find(lprefix_id+'_app_name').select2_data_list()[0]);
            $(lparent_pane).find(lprefix_id+'_controller_name').val('');
            $(lparent_pane).find(lprefix_id+'_method_name').val('');
            
            APP_FORM.status.default_status_set(
                'security_controller',
                $(lparent_pane).find(lprefix_id+'_security_controller_status')
            );
           
        },
        submit:function(){
            var lparent_pane = security_controller_parent_pane;
            var lprefix_id = security_controller_component_prefix_id;
            var ajax_url = security_controller_index_url;
            var lmethod = $(lparent_pane).find("#security_controller_method").val();
            var security_controller_id = $(lparent_pane).find(lprefix_id+"_id").val();        
            var json_data = {
                ajax_post:true,
                security_controller:{},
                message_session:true
            };

            switch(lmethod){
                case 'add':
                case 'view':
                    json_data.security_controller.app_name = $(lparent_pane).find(lprefix_id+"_app_name").select2('val');
                    json_data.security_controller.name = $(lparent_pane).find(lprefix_id+"_controller_name").val();
                    json_data.security_controller.method = $(lparent_pane).find(lprefix_id+"_method_name").val();
                    json_data.security_controller.security_controller_status = $(lparent_pane).find(lprefix_id+"_security_controller_status").select2('val');
                    break;
            }
            
            var lajax_method='';
            switch(lmethod){
                case 'add':
                    lajax_method = 'security_controller_add';
                    break;
                case 'view':
                    lajax_method = $(lparent_pane).find(lprefix_id+'_security_controller_status').select2('data').method;
                    break;
            }
            ajax_url +=lajax_method+'/'+security_controller_id;
            
            result = APP_DATA_TRANSFER.submit(ajax_url,json_data);
            if(result.success ===1){
                $(security_controller_parent_pane).find(lprefix_id+'_id').val(result.trans_id);
                if(security_controller_view_url !==''){
                    var url = security_controller_view_url+result.trans_id;
                    window.location.href=url;
                }
                else{
                    security_controller_after_submit();
                }
            }
        },
    }

    var security_controller_bind_event = function(){
        var lparent_pane = security_controller_parent_pane;
        var lprefix_id = security_controller_component_prefix_id;
        
        $(lparent_pane).find(lprefix_id+'_btn_submit').off('click');
        APP_COMPONENT.button.submit.set($(lparent_pane).find(lprefix_id+'_btn_submit'),{
            parent_pane:lparent_pane,
            module_method:security_controller_methods
        });
        
        
    }
    
    var security_controller_components_prepare= function(){
        var lparent_pane = security_controller_parent_pane;
        var lprefix_id = security_controller_component_prefix_id;
        var method = $(security_controller_parent_pane).find(lprefix_id+"_method").val();
                
        var security_controller_data_set = function(){
            var lparent_pane = security_controller_parent_pane;
            var lprefix_id = security_controller_component_prefix_id;
            switch(method){
                case "add":
                    security_controller_methods.reset_all();
                    break;
                case "view":
                    var security_controller_id = $(security_controller_parent_pane).find(lprefix_id+"_id").val();
                    var json_data={data:security_controller_id};
                    var lresponse = APP_DATA_TRANSFER.ajaxPOST(security_controller_data_support_url+"security_controller_get",json_data).response;
                    if(lresponse != []){
                        var lsecurity_controller = lresponse.security_controller;
                        $(lparent_pane).find(lprefix_id+'_app_name').select2('data',lsecurity_controller.app_name);
                        $(lparent_pane).find(lprefix_id+'_controller_name').val(lsecurity_controller.name);
                        $(lparent_pane).find(lprefix_id+'_method_name').val(lsecurity_controller.method);
                        
                        $(lparent_pane).find(lprefix_id+'_security_controller_status')
                        .select2('data',lsecurity_controller.security_controller_status
                        ).change();
                            
                        $(lparent_pane).find(lprefix_id+'_security_controller_status')
                        .select2({data:lresponse.security_controller_status_list});
                        
                        
                    };
                    break;            
            }
        }
    
        security_controller_methods.enable_disable();
        security_controller_methods.show_hide();
        security_controller_data_set();
    }
    
</script>