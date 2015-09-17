<script>
    var coba_parent_pane = $('<?php echo $detail_tab; ?>')[0];
    var coba_ajax_url = null;
    var coba_index_url = null;
    var coba_view_url = null;
    var coba_window_scroll = null;
    var coba_data_support_url = null;
    var coba_common_ajax_listener = null;
    var coba_component_prefix_id = '';
    
    var coba_init = function(){
        var parent_pane = coba_parent_pane;

        coba_ajax_url = '<?php echo $ajax_url ?>';
        coba_index_url = '<?php echo $index_url ?>';
        coba_view_url = '<?php echo $view_url ?>';
        coba_window_scroll = '<?php echo $window_scroll; ?>';
        coba_data_support_url = '<?php echo $data_support_url; ?>';
        coba_common_ajax_listener = '<?php echo $common_ajax_listener; ?>';
        coba_component_prefix_id = '#<?php echo $component_prefix_id; ?>';
        
    }

    var coba_after_submit = function(){

    }
    
    var coba_data ={
        
    }
    
    var coba_methods = {
        
        hide_all:function(){
            var lparent_pane = coba_parent_pane;
            var lprefix_id = coba_component_prefix_id;
            $(lparent_pane).find('.hide_all').hide();
        },
        disable_all:function(){
            var lparent_pane = coba_parent_pane;
            var lcomponents = $(lparent_pane).find('.disable_all');
            APP_COMPONENT.disable_all(lparent_pane);
        },
        
        show_hide: function(){
            var lparent_pane = coba_parent_pane;
            var lprefix_id = coba_component_prefix_id;
            var lmethod = $(lparent_pane).find('#coba_method').val();            
            
            coba_methods.hide_all();
            
            switch(lmethod){
                case 'add':
                case 'view':
                    $(lparent_pane).find(lprefix_id+'_app_name').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find(lprefix_id+'_controller_name').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find(lprefix_id+'_method_name').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find(lprefix_id+'_coba_status').closest('div [class*="form-group"]').show();
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
            var lparent_pane = coba_parent_pane;
            var lprefix_id = coba_component_prefix_id;
            var lmethod = $(lparent_pane).find(lprefix_id+'_method').val();  
            coba_methods.disable_all();
            
            switch(lmethod){
                case "add":
                case 'view':
                    $(lparent_pane).find(lprefix_id+'_app_name').select2('enable');
                    $(lparent_pane).find(lprefix_id+"_controller_name").prop("disabled",false);
                    $(lparent_pane).find(lprefix_id+"_method_name").prop("disabled",false);
                    $(lparent_pane).find(lprefix_id+'_coba_status').select2('enable');
                    break;
            }
        },
        reset_all:function(){
            var lparent_pane = coba_parent_pane;
            var lprefix_id = coba_component_prefix_id;
            
            $(lparent_pane).find(lprefix_id+'_app_name').select2('data',$(lparent_pane).find(lprefix_id+'_app_name').select2_data_list()[0]);
            $(lparent_pane).find(lprefix_id+'_controller_name').val('');
            $(lparent_pane).find(lprefix_id+'_method_name').val('');
            
            APP_FORM.status.default_status_set(
                'coba',
                $(lparent_pane).find(lprefix_id+'_coba_status')
            );
           
        },
        submit:function(){
            var lparent_pane = coba_parent_pane;
            var lprefix_id = coba_component_prefix_id;
            var ajax_url = coba_index_url;
            var lmethod = $(lparent_pane).find("#coba_method").val();
            var coba_id = $(lparent_pane).find(lprefix_id+"_id").val();        
            var json_data = {
                ajax_post:true,
                coba:{},
                message_session:true
            };

            switch(lmethod){
                case 'add':
                case 'view':
                    json_data.coba.app_name = $(lparent_pane).find(lprefix_id+"_app_name").select2('val');
                    json_data.coba.name = $(lparent_pane).find(lprefix_id+"_controller_name").val();
                    json_data.coba.method = $(lparent_pane).find(lprefix_id+"_method_name").val();
                    json_data.coba.coba_status = $(lparent_pane).find(lprefix_id+"_coba_status").select2('val');
                    break;
            }
            
            var lajax_method='';
            switch(lmethod){
                case 'add':
                    lajax_method = 'coba_add';
                    break;
                case 'view':
                    lajax_method = $(lparent_pane).find(lprefix_id+'_coba_status').select2('data').method;
                    break;
            }
            ajax_url +=lajax_method+'/'+coba_id;
            
            result = APP_DATA_TRANSFER.submit(ajax_url,json_data);
            if(result.success ===1){
                $(coba_parent_pane).find(lprefix_id+'_id').val(result.trans_id);
                if(coba_view_url !==''){
                    var url = coba_view_url+result.trans_id;
                    window.location.href=url;
                }
                else{
                    coba_after_submit();
                }
            }
        },
    }

    var coba_bind_event = function(){
        var lparent_pane = coba_parent_pane;
        var lprefix_id = coba_component_prefix_id;
        
        $(lparent_pane).find(lprefix_id+'_btn_submit').off('click');
        APP_COMPONENT.button.submit.set($(lparent_pane).find(lprefix_id+'_btn_submit'),{
            parent_pane:lparent_pane,
            module_method:coba_methods
        });
        
        
    }
    
    var coba_components_prepare= function(){
        var lparent_pane = coba_parent_pane;
        var lprefix_id = coba_component_prefix_id;
        var method = $(coba_parent_pane).find(lprefix_id+"_method").val();
                
        var coba_data_set = function(){
            var lparent_pane = coba_parent_pane;
            var lprefix_id = coba_component_prefix_id;
            switch(method){
                case "add":
                    coba_methods.reset_all();
                    break;
                case "view":
                    var coba_id = $(coba_parent_pane).find(lprefix_id+"_id").val();
                    var json_data={data:coba_id};
                    var lresponse = APP_DATA_TRANSFER.ajaxPOST(coba_data_support_url+"coba_get",json_data).response;
                    if(lresponse != []){
                        var lcoba = lresponse.coba;
                        $(lparent_pane).find(lprefix_id+'_app_name').select2('data',{id:lcoba.app_name, text:lcoba.app_name_text});
                        $(lparent_pane).find(lprefix_id+'_controller_name').val(lcoba.name);
                        $(lparent_pane).find(lprefix_id+'_method_name').val(lcoba.method);
                        
                        $(lparent_pane).find(lprefix_id+'_coba_status')
                            .select2('data',{id:lcoba.coba_status
                                ,text:lcoba.coba_status_text}).change();
                            
                        $(lparent_pane).find(lprefix_id+'_coba_status')
                            .select2({data:lresponse.coba_status_list});
                        
                        
                    };
                    break;            
            }
        }
    
        coba_methods.enable_disable();
        coba_methods.show_hide();
        coba_data_set();
    }
    
</script>