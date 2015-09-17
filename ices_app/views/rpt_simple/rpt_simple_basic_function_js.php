<script>

    var rpt_simple_parent_pane = $('<?php echo $detail_tab; ?>')[0];
    var rpt_simple_ajax_url = null;
    var rpt_simple_index_url = null;
    var rpt_simple_view_url = null;
    var rpt_simple_window_scroll = null;
    var rpt_simple_data_support_url = null;
    var rpt_simple_common_ajax_listener = null;
    var rpt_simple_component_prefix_id = '';
    
    var rpt_simple_insert_dummy = true;

    var rpt_simple_init = function(){
        var parent_pane = rpt_simple_parent_pane;
        rpt_simple_ajax_url = '<?php echo $ajax_url ?>';
        rpt_simple_index_url = '<?php echo $index_url ?>';
        rpt_simple_view_url = '<?php echo $view_url ?>';
        rpt_simple_window_scroll = '<?php echo $window_scroll; ?>';
        rpt_simple_data_support_url = '<?php echo $data_support_url; ?>';
        rpt_simple_common_ajax_listener = '<?php echo $common_ajax_listener; ?>';
        rpt_simple_component_prefix_id = '#<?php echo $component_prefix_id; ?>';
        rpt_simple_rma_extra_param_get = function(){
            //input select detail use this function to get extra parameter for further query
            
        };
        
        
    }
    
    var rpt_simple_methods = {
        hide_all:function(){
            var lparent_pane = rpt_simple_parent_pane;
            var lc_arr = $(lparent_pane).find('.hide_all').hide();
        },
        show_hide:function(){
            var lparent_pane = rpt_simple_parent_pane;
            var lmethod = $(lparent_pane).find('#rpt_simple_method').val();
            var lprefix_id = rpt_simple_component_prefix_id;
            rpt_simple_methods.hide_all();
            
            switch(lmethod){
                case 'add':   
                case 'view':
                    $(lparent_pane).find(lprefix_id+'_module_name').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find(lprefix_id+'_module_condition').closest('div [class*="form-group"]').show();
                    break;
            }
        },
        disable_all:function(){
            var lparent_pane = rpt_simple_parent_pane;
            APP_COMPONENT.disable_all(lparent_pane);
        },
        enable_disable:function(){
            var lparent_pane = rpt_simple_parent_pane;
            var lmethod = $(lparent_pane).find('#rpt_simple_method').val();
            var lprefix_id = rpt_simple_component_prefix_id;
            rpt_simple_methods.disable_all();
            switch(lmethod){
                case 'add':
                    break;
                case 'view':
                    $(lparent_pane).find(lprefix_id+'_module_name').select2('enable');
                    $(lparent_pane).find(lprefix_id+'_module_condition').select2('enable');
                   
                    break;
            }
        },
        reset_all:function(){
            var lparent_pane = rpt_simple_parent_pane;
            var lprefix_id = rpt_simple_component_prefix_id;
        },
        submit:function(){
            var lparent_pane = rpt_simple_parent_pane;
            var lprefix_id = rpt_simple_component_prefix_id;
            var lajax_url = rpt_simple_index_url;
            var lmethod = $(lparent_pane).find('#rpt_simple_method').val();
            var json_data = {
                ajax_post:true,
                message_session:true,
            };

            switch(lmethod){
                case 'add':
                    
                    lajax_url +='rpt_simple_add/';
                    break;
                case 'view':
                    var rpt_simple_id = $(lparent_pane).find('#rpt_simple_id').val();
                    var lajax_method = $(lparent_pane).find('#rpt_simple_rpt_simple_status').select2('data').method;
                    lajax_url +=lajax_method+'/'+rpt_simple_id;
                    break;
            }
            
            result = APP_DATA_TRANSFER.submit(lajax_url,json_data);

            if(result.success ===1){
                $(lparent_pane).find('#rpt_simple_id').val(result.trans_id);
                if(rpt_simple_view_url !==''){
                    var url = rpt_simple_view_url+result.trans_id;
                    window.location.href=url;
                }
                else{
                    rpt_simple_after_submit();
                }
            }
        },
        module_name:{
            reset:function(){
                $(rpt_simple_parent_pane).find(rpt_simple_component_prefix_id+'_module_name').select2('data',null);
                rpt_simple_methods.module_name.reset_dependency();
            },
            reset_dependency:function(){
                rpt_simple_methods.module_condition.reset();                
            }
        },
        module_condition:{
            reset:function(){
                $(rpt_simple_parent_pane).find(rpt_simple_component_prefix_id+'_module_condition').select2('data',null);
                $(rpt_simple_parent_pane).find(rpt_simple_component_prefix_id+'_module_condition').select2({data:[]});
                rpt_simple_methods.module_condition.reset_dependency();
            },
            reset_dependency:function(){
                rpt_simple_methods.report_table.reset();
            }
        },
        report_table:{
            reset:function(){
                $(rpt_simple_parent_pane).find(rpt_simple_component_prefix_id+'_report_table').empty();
            }
        }
        
    };
    
    var rpt_simple_bind_event = function(){
        var lparent_pane = rpt_simple_parent_pane;
        var lprefix_id = rpt_simple_component_prefix_id;
        
        $(lparent_pane).find(lprefix_id+'_module_name').on('change',function(){
            rpt_simple_methods.module_name.reset_dependency();
            if($(this).select2('val')!=='') {
                var lcondition = $(this).select2('data')['condition'];
                $(lparent_pane).find(lprefix_id+'_module_condition').select2({data:lcondition,allowClear:true});
            }
        });
        
        $(lparent_pane).find(lprefix_id+'_module_condition').on('change',function(){
            var lparent_pane = rpt_simple_parent_pane;
            var lprefix_id = rpt_simple_component_prefix_id;
            rpt_simple_methods.module_condition.reset_dependency();
            var json_data = {
                module_name:$(lparent_pane).find(lprefix_id+'_module_name').select2('val'),
                module_condition:$(lparent_pane).find(lprefix_id+'_module_condition').select2('val'),
            }
            var lajax_url = rpt_simple_data_support_url+'report_table_get/';
            var lresponse = APP_DATA_TRANSFER.ajaxPOST(lajax_url, json_data).response;
            $(lparent_pane).find(lprefix_id+'_report_table')[0].innerHTML = lresponse;
            
        });
        
        
        $(lparent_pane).find('#save_excel').off();
        $(lparent_pane).find('#save_excel').on('click',function(){
            var lmodule_name = $(lparent_pane).find(lprefix_id+'_module_name').select2('val');
            var lmodule_condition =$(lparent_pane).find(lprefix_id+'_module_condition').select2('val');            
            window.open(rpt_simple_index_url+'download_excel/'+lmodule_name+'/'+lmodule_condition+'/');
        });
            
        
    }
    
    var rpt_simple_components_prepare = function(){
        

        var rpt_simple_data_set = function(){
            var lparent_pane = rpt_simple_parent_pane;
            var lprefix_id = rpt_simple_component_prefix_id;
            var lmethod = $(lparent_pane).find('#rpt_simple_method').val();
            
            switch(lmethod){
                case 'add':
                    rpt_simple_methods.reset_all();
                    
                    break;
                case 'view':
                    
                    break;
            }
        }
        
        
        rpt_simple_methods.enable_disable();
        rpt_simple_methods.show_hide();
        rpt_simple_data_set();
    }
    
    var rpt_simple_after_submit = function(){
        //function that will be executed after submit 
    }
    
    var rpt_simple_reference_extra_param_get = function(){
        var lresult = {};
        var lparent_pane = rpt_simple_parent_pane;
        var lprefix_id = rpt_simple_component_prefix_id;
        var lmodule_name = $(lparent_pane).find(lprefix_id+'_module_name').select2('val');
        var lmodule_action = $(lparent_pane).find(lprefix_id+'_module_action').select2('val');
        lresult = {module_name:lmodule_name, module_action:lmodule_action};
        return lresult;
    }
    
    
    
    
    
</script>