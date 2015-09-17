<script>

    var refill_product_medium_parent_pane = $('<?php echo $detail_tab; ?>')[0];
    var refill_product_medium_ajax_url = null;
    var refill_product_medium_index_url = null;
    var refill_product_medium_view_url = null;
    var refill_product_medium_window_scroll = null;
    var refill_product_medium_data_support_url = null;
    var refill_product_medium_common_ajax_listener = null;
    var refill_product_medium_component_prefix_id = '';
    
    var refill_product_medium_insert_dummy = true;

    var refill_product_medium_init = function(){
        var parent_pane = refill_product_medium_parent_pane;
        refill_product_medium_ajax_url = '<?php echo $ajax_url ?>';
        refill_product_medium_index_url = '<?php echo $index_url ?>';
        refill_product_medium_view_url = '<?php echo $view_url ?>';
        refill_product_medium_window_scroll = '<?php echo $window_scroll; ?>';
        refill_product_medium_data_support_url = '<?php echo $data_support_url; ?>';
        refill_product_medium_common_ajax_listener = '<?php echo $common_ajax_listener; ?>';
        refill_product_medium_component_prefix_id = '#<?php echo $component_prefix_id; ?>';
                
        
    }
    
    var refill_product_medium_methods = {
        hide_all:function(){
            var lparent_pane = refill_product_medium_parent_pane;
            var lc_arr = $(lparent_pane).find('.hide_all').hide();
            $(lparent_pane).find('#refill_product_medium_print').hide();
            $(lparent_pane).find('#refill_product_medium_submit').hide();
        },
        show_hide:function(){
            var lparent_pane = refill_product_medium_parent_pane;
            var lmethod = $(lparent_pane).find('#refill_product_medium_method').val();
            refill_product_medium_methods.hide_all();
            
            switch(lmethod){
                case 'add':                    
                    
                case 'view':
                    $(lparent_pane).find('#refill_product_medium_submit').show();
                    $(lparent_pane).find('#refill_product_medium_code').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#refill_product_medium_name').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#refill_product_medium_refill_product_medium_status').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#refill_product_medium_notes').closest('.form-group').show();
                    break;
            }
        },
        disable_all:function(){
            var lparent_pane = refill_product_medium_parent_pane;
            APP_COMPONENT.disable_all(lparent_pane);
        },
        enable_disable:function(){
            var lparent_pane = refill_product_medium_parent_pane;
            var lmethod = $(lparent_pane).find('#refill_product_medium_method').val();    
            refill_product_medium_methods.disable_all();
            switch(lmethod){
                case 'add':
                   
                    break;
                case 'view':
                   
                    break;
            }
        },
        reset_all:function(){
            var lparent_pane = refill_product_medium_parent_pane;
            var lprefix_id = refill_product_medium_component_prefix_id;
            $(lparent_pane).find(lprefix_id+'_code').val('');
            $(lparent_pane).find(lprefix_id+'_name').val('');
            var ldefault_status = null;
            ldefault_status = APP_DATA_TRANSFER.common_ajax_listener('module_status/default_status_get',{module:'refill_product_medium'}).response;

            $(lparent_pane).find(lprefix_id+'_refill_product_medium_status')
                    .select2('data',{id:ldefault_status.val,text:ldefault_status.label}).change();
            var lstatus_list = [
                {id:ldefault_status.val,text:ldefault_status.label}
            ];
           
            
            
        },
        submit:function(){
            var lparent_pane = refill_product_medium_parent_pane;
            var lprefix_id = refill_product_medium_component_prefix_id;
            var lajax_url = refill_product_medium_index_url;
            var lmethod = $(lparent_pane).find(lprefix_id+'_method').val();
            var json_data = {
                ajax_post:true,
                message_session:true,
            };

            switch(lmethod){
                case 'add':
                    json_data.refill_product_medium = {
                        code:$(lparent_pane).find(lprefix_id+'_code').val(),
                        name:$(lparent_pane).find(lprefix_id+'_name').val(),
                        notes:$(lparent_pane).find(lprefix_id+'_notes').val(),
                    };
                    
                    lajax_url +='refill_product_medium_add/';
                    break;
                case 'view':
                    json_data.refill_product_medium = {
                        code:$(lparent_pane).find(lprefix_id+'_code').val(),
                        name:$(lparent_pane).find(lprefix_id+'_name').val(),
                        notes:$(lparent_pane).find(lprefix_id+'_notes').val(),
                    };
                    
                    var refill_product_medium_id = $(lparent_pane).find(lprefix_id+'_id').val();
                    var lajax_method = $(lparent_pane).find(lprefix_id+'_refill_product_medium_status').
                        select2('data').method;
                    lajax_url +=lajax_method+'/'+refill_product_medium_id;
                    break;
            }
            
            result = APP_DATA_TRANSFER.submit(lajax_url,json_data);

            if(result.success ===1){
                $(lparent_pane).find(lprefix_id+'_id').val(result.trans_id);
                if(refill_product_medium_view_url !==''){
                    var url = refill_product_medium_view_url+result.trans_id;
                    window.location.href=url;
                }
                else{
                    refill_product_medium_after_submit();
                }
            }
        }
    };
    
    var refill_product_medium_bind_event = function(){
        var lparent_pane = refill_product_medium_parent_pane;
        var lprefix_id = refill_product_medium_component_prefix_id;
                
        $(lparent_pane).find('#refill_product_medium_submit').off();
        var lparam = {
            window_scroll: refill_product_medium_window_scroll,
            parent_pane: refill_product_medium_parent_pane,
            module_method: refill_product_medium_methods
        };
        
        APP_COMPONENT.button.submit.set(
            $(lparent_pane).find('#refill_product_medium_submit')[0],
            lparam
        );
        
            
        
    }
    
    var refill_product_medium_components_prepare = function(){
        

        var refill_product_medium_data_set = function(){
            var lparent_pane = refill_product_medium_parent_pane;
            var lprefix_id = refill_product_medium_component_prefix_id;
            var lmethod = $(lparent_pane).find('#refill_product_medium_method').val();
            
            switch(lmethod){
                case 'add':
                    refill_product_medium_methods.reset_all();
                    if(refill_product_medium_insert_dummy){
                        
                    }
                    break;
                case 'view':
                    
                    var lrefill_product_medium_id = $(lparent_pane).find('#refill_product_medium_id').val();
                    var lajax_url = refill_product_medium_data_support_url+'refill_product_medium_get/';
                    var json_data = {data:lrefill_product_medium_id};
                    var lresponse = APP_DATA_TRANSFER.ajaxPOST(lajax_url,json_data).response;
                    var lrefill_product_medium = lresponse.refill_product_medium;

                    $(lparent_pane).find('#refill_product_medium_code').val(lrefill_product_medium.code);
                    $(lparent_pane).find('#refill_product_medium_name').val(lrefill_product_medium.name);
                    $(lparent_pane).find('#refill_product_medium_notes').val(lrefill_product_medium.notes);
                    
                    $(lparent_pane).find('#refill_product_medium_refill_product_medium_status')
                        .select2('data',{id:lrefill_product_medium.refill_product_medium_status
                            ,text:lrefill_product_medium.refill_product_medium_status_text}).change();
                    
                    $(lparent_pane).find('#refill_product_medium_refill_product_medium_status')
                            .select2({data:lresponse.refill_product_medium_status_list});
                    
                    
                    
                    
                    break;
            }
        }
        
        
        refill_product_medium_methods.enable_disable();
        refill_product_medium_methods.show_hide();
        refill_product_medium_data_set();
    }
    
    var refill_product_medium_after_submit = function(){
        //function that will be executed after submit 
    }
    
   
    
    
    
    
    
</script>