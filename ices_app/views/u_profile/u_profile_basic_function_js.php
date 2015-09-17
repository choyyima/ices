<script>

    var u_profile_parent_pane = $('<?php echo $detail_tab; ?>')[0];
    var u_profile_ajax_url = null;
    var u_profile_index_url = null;
    var u_profile_view_url = null;
    var u_profile_window_scroll = null;
    var u_profile_data_support_url = null;
    var u_profile_common_ajax_listener = null;
    var u_profile_component_prefix_id = '';
    
    var u_profile_insert_dummy = true;

    var u_profile_init = function(){
        var parent_pane = u_profile_parent_pane;
        u_profile_ajax_url = '<?php echo $ajax_url ?>';
        u_profile_index_url = '<?php echo $index_url ?>';
        u_profile_view_url = '<?php echo $view_url ?>';
        u_profile_window_scroll = '<?php echo $window_scroll; ?>';
        u_profile_data_support_url = '<?php echo $data_support_url; ?>';
        u_profile_common_ajax_listener = '<?php echo $common_ajax_listener; ?>';
        u_profile_component_prefix_id = '#<?php echo $component_prefix_id; ?>';
        u_profile_rma_extra_param_get = function(){
            //input select detail use this function to get extra parameter for further query
            
        };
        
        
    }
    
    var u_profile_methods = {
        hide_all:function(){
            var lparent_pane = u_profile_parent_pane;
            var lc_arr = $(lparent_pane).find('.hide_all').hide();
        },
        show_hide:function(){
            var lparent_pane = u_profile_parent_pane;
            var lmethod = $(lparent_pane).find('#u_profile_method').val();
            var lprefix_id = u_profile_component_prefix_id;
            u_profile_methods.hide_all();
            
            switch(lmethod){
                case 'add':   
                case 'view':
                    
                    $(lparent_pane).find(lprefix_id+'_name').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find(lprefix_id+'_first_name').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find(lprefix_id+'_last_name').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find(lprefix_id+'_password').closest('div [class*="form-group"]').show();
                    break;
            }
        },
        disable_all:function(){
            var lparent_pane = u_profile_parent_pane;
            APP_COMPONENT.disable_all(lparent_pane);
        },
        enable_disable:function(){
            var lparent_pane = u_profile_parent_pane;
            var lmethod = $(lparent_pane).find('#u_profile_method').val();
            var lprefix_id = u_profile_component_prefix_id;
            u_profile_methods.disable_all();
            switch(lmethod){
                case 'add':
                case 'view':
                    $(lparent_pane).find(lprefix_id+'_first_name').prop('disabled',false);
                    $(lparent_pane).find(lprefix_id+'_last_name').prop('disabled',false);
                    $(lparent_pane).find(lprefix_id+'_password').prop('disabled',false);
                    break;
            }
        },
        reset_all:function(){
            var lparent_pane = u_profile_parent_pane;
            var lprefix_id = u_profile_component_prefix_id;
        },
        submit:function(){
            var lparent_pane = u_profile_parent_pane;
            var lprefix_id = u_profile_component_prefix_id;
            var lajax_url = u_profile_index_url;
            var lmethod = $(lparent_pane).find('#u_profile_method').val();
            var json_data = {
                ajax_post:true,
                message_session:true,
            };

            switch(lmethod){
                case 'add':
                case 'view':
                    json_data.u_profile = {
                        first_name:$(lparent_pane).find(lprefix_id+'_first_name').val(),
                        last_name:$(lparent_pane).find(lprefix_id+'_last_name').val(),
                        password:$(lparent_pane).find(lprefix_id+'_password').val(),
                    };
                    
                    var u_profile_id = $(lparent_pane).find('#u_profile_id').val();
                    var lajax_method = 'u_profile_update/';
                    lajax_url +=lajax_method+u_profile_id;
                    break;
            }
            
            result = APP_DATA_TRANSFER.submit(lajax_url,json_data);

            if(result.success ===1){
                $(lparent_pane).find('#u_profile_id').val(result.trans_id);
                if(u_profile_view_url !==''){
                    var url = u_profile_index_url;
                    window.location.href=url;
                }
                else{
                    u_profile_after_submit();
                }
            }
        },
        report_table:{
            reset:function(){
                $(u_profile_parent_pane).find(u_profile_component_prefix_id+'_report_table').empty();
            }
        }
        
    };
    
    var u_profile_bind_event = function(){
        var lparent_pane = u_profile_parent_pane;
        var lprefix_id = u_profile_component_prefix_id;
        
        $(lparent_pane).find(lprefix_id+'_submit').off('click');
        $(lparent_pane).find(lprefix_id+'_submit').on('click',function(e){
            e.preventDefault();
            btn = $(this);
            btn.addClass('disabled');
            var lparent_pane = u_profile_parent_pane;
            modal_confirmation_submit_parent = $(lparent_pane).attr('class').indexOf('modal-body')!==-1?
                $(lparent_pane).closest('.modal'):null;
            $('#modal_confirmation_submit').modal('show');
            $('#modal_confirmation_submit_btn_submit').on('click',function(){
                u_profile_methods.submit();
            });
            $(u_profile_window_scroll).scrollTop(0);        
            setTimeout(function(){btn.removeClass('disabled')},1000);
        });
        
    }
    
    var u_profile_components_prepare = function(){
        

        var u_profile_data_set = function(){
            var lparent_pane = u_profile_parent_pane;
            var lprefix_id = u_profile_component_prefix_id;
            var lmethod = $(lparent_pane).find('#u_profile_method').val();
            
            switch(lmethod){
                case 'add':
                    u_profile_methods.reset_all();
                case 'view':
                    
                    break;
            }
        }
        
        
        u_profile_methods.enable_disable();
        u_profile_methods.show_hide();
        u_profile_data_set();
    }
    
    var u_profile_after_submit = function(){
        //function that will be executed after submit 
    }
    
    var u_profile_reference_extra_param_get = function(){
        var lresult = {};
        var lparent_pane = u_profile_parent_pane;
        var lprefix_id = u_profile_component_prefix_id;
        var lmodule_name = $(lparent_pane).find(lprefix_id+'_module_name').select2('val');
        var lmodule_action = $(lparent_pane).find(lprefix_id+'_module_action').select2('val');
        lresult = {module_name:lmodule_name, module_action:lmodule_action};
        return lresult;
    }
    
    
    
    
    
</script>