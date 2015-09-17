<script>

    var rpt_product_parent_pane = $('<?php echo $detail_tab; ?>')[0];
    var rpt_product_ajax_url = null;
    var rpt_product_form_render_url = null;
    var rpt_product_index_url = null;
    var rpt_product_view_url = null;
    var rpt_product_window_scroll = null;
    var rpt_product_data_support_url = null;
    var rpt_product_common_ajax_listener = null;
    var rpt_product_component_prefix_id = '';
    
    
    var rpt_product_insert_dummy = true;

    var rpt_product_init = function(){
        var lparent_pane = rpt_product_parent_pane;
        rpt_product_ajax_url = '<?php echo $ajax_url ?>';
        rpt_product_index_url = '<?php echo $index_url ?>';
        rpt_product_form_render_url = '<?php echo $form_render_url ?>';
        rpt_product_view_url = '<?php echo $view_url ?>';
        rpt_product_window_scroll = '<?php echo $window_scroll; ?>';
        rpt_product_data_support_url = '<?php echo $data_support_url; ?>';
        rpt_product_common_ajax_listener = '<?php echo $common_ajax_listener; ?>';
        rpt_product_component_prefix_id = '#<?php echo $component_prefix_id; ?>';
        rpt_product_rma_extra_param_get = function(){
            //input select detail use this function to get extra parameter for further query
            
        };
        
        
    }
    
    var rpt_product_methods = {
        hide_all:function(){
            var lparent_pane = rpt_product_parent_pane;
            var lc_arr = $(lparent_pane).find('.hide_all').hide();
        },
        show_hide:function(){
            var lparent_pane = rpt_product_parent_pane;
            var lmethod = $(lparent_pane).find('#rpt_product_method').val();
            var lprefix_id = rpt_product_component_prefix_id;
            rpt_product_methods.hide_all();
            
            switch(lmethod){
                case 'add':   
                case 'view':
                    $(lparent_pane).find(lprefix_id+'_module_name').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find(lprefix_id+'_module_condition').closest('div [class*="form-group"]').show();
                    break;
            }
        },
        disable_all:function(){
            var lparent_pane = rpt_product_parent_pane;
            APP_COMPONENT.disable_all(lparent_pane);
        },
        enable_disable:function(){
            var lparent_pane = rpt_product_parent_pane;
            var lmethod = $(lparent_pane).find('#rpt_product_method').val();
            var lprefix_id = rpt_product_component_prefix_id;
            rpt_product_methods.disable_all();
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
            var lparent_pane = rpt_product_parent_pane;
            var lprefix_id = rpt_product_component_prefix_id;
        },
        submit:function(){
            var lparent_pane = rpt_product_parent_pane;
            var lprefix_id = rpt_product_component_prefix_id;
            var lajax_url = rpt_product_index_url;
            var lmethod = $(lparent_pane).find('#rpt_product_method').val();
            var json_data = {
                ajax_post:true,
                message_session:true,
            };

            switch(lmethod){
                case 'add':
                    
                    lajax_url +='rpt_product_add/';
                    break;
                case 'view':
                    var rpt_product_id = $(lparent_pane).find('#rpt_product_id').val();
                    var lajax_method = $(lparent_pane).find('#rpt_product_rpt_product_status').select2('data').method;
                    lajax_url +=lajax_method+'/'+rpt_product_id;
                    break;
            }
            
            result = APP_DATA_TRANSFER.submit(lajax_url,json_data);

            if(result.success ===1){
                $(lparent_pane).find('#rpt_product_id').val(result.trans_id);
                if(rpt_product_view_url !==''){
                    var url = rpt_product_view_url+result.trans_id;
                    window.location.href=url;
                }
                else{
                    rpt_product_after_submit();
                }
            }
        },
        report_table:{
            reset:function(){
                var lparent_pane = rpt_product_parent_pane;
                var lprefix_id = rpt_product_component_prefix_id;
                $(lparent_pane).find(lprefix_id+'_report_div').empty();
            }
        }
        
    };
    
    var rpt_product_bind_event = function(){
        var lparent_pane = rpt_product_parent_pane;
        var lprefix_id = rpt_product_component_prefix_id;
        
        $(lparent_pane).find(lprefix_id+'_module_name').on('change',function(){
            var lparent_pane = rpt_product_parent_pane;
            var lprefix_id = rpt_product_component_prefix_id;
            
            rpt_product_methods.report_table.reset();
            
            if($(this).select2('val')!== ''){
                var json_data = {
                    module_name:$(lparent_pane).find(lprefix_id+'_module_name').select2('val'),
                }
                var lajax_url = rpt_product_form_render_url+'report_get/';
                var lresponse = APP_DATA_TRANSFER.ajaxPOST(lajax_url, json_data).response;
                APP_COMPONENT.attach($(lparent_pane).find(lprefix_id+'_report_div')[0],lresponse);
                
            }
        });
        
       
        $(lparent_pane).find('#save_excel').off();
        $(lparent_pane).find('#save_excel').on('click',function(){
            var lmodule_name = $(lparent_pane).find(lprefix_id+'_module_name').select2('val');
            if(lmodule_name !== ''){
                window.open(rpt_product_index_url+'download_excel/'+lmodule_name+'/');
            }
        });
            
        
    }
    
    var rpt_product_components_prepare = function(){
        

        var rpt_product_data_set = function(){
            var lparent_pane = rpt_product_parent_pane;
            var lprefix_id = rpt_product_component_prefix_id;
            var lmethod = $(lparent_pane).find('#rpt_product_method').val();
            
            switch(lmethod){
                case 'add':
                case 'view':
                    
                    break;
            }
        }
        
        
        rpt_product_methods.enable_disable();
        rpt_product_methods.show_hide();
        rpt_product_data_set();
    }
    
    var rpt_product_after_submit = function(){
        //function that will be executed after submit 
    }
    
    var rpt_product_reference_extra_param_get = function(){
        return {};
    }
    
    
    
    
    
</script>