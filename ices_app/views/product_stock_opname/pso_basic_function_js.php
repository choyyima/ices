<script>
    var pso_parent_pane = $('<?php echo $detail_tab; ?>')[0];
    var pso_ajax_url = null;
    var pso_index_url = null;
    var pso_view_url = null;
    var pso_window_scroll = null;
    var pso_data_support_url = null;
    var pso_common_ajax_listener = null;
    var pso_component_prefix_id = '';
    
    var pso_init = function(){
        var parent_pane = pso_parent_pane;

        pso_ajax_url = '<?php echo $ajax_url ?>';
        pso_index_url = '<?php echo $index_url ?>';
        pso_view_url = '<?php echo $view_url ?>';
        pso_window_scroll = '<?php echo $window_scroll; ?>';
        pso_data_support_url = '<?php echo $data_support_url; ?>';
        pso_common_ajax_listener = '<?php echo $common_ajax_listener; ?>';
        pso_component_prefix_id = '#<?php echo $component_prefix_id; ?>';
        
    }

    var pso_after_submit = function(){

    }
    
    var pso_data = {
        product_stock_opname_status_curr: ''
    }
    
    var pso_methods = {
        hide_all:function(){
            var lparent_pane = pso_parent_pane;
            var lprefix_id = pso_component_prefix_id;
            $(lparent_pane).find('.hide_all').hide();
            $(lparent_pane).find(lprefix_id+'_print').hide();
        },
        disable_all:function(){
            var lparent_pane = pso_parent_pane;
            APP_COMPONENT.disable_all(lparent_pane);
            
        },
        show_hide: function(){
            var lparent_pane = pso_parent_pane;
            var lprefix_id = pso_component_prefix_id;
            var lmethod = $(lparent_pane).find('#pso_method').val();            
            pso_methods.hide_all();
            
            switch(lmethod){
                case 'add':
                case 'view':
                    $(lparent_pane).find(lprefix_id+'_store').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find(lprefix_id+'_code').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find(lprefix_id+'_checker').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find(lprefix_id+'_product_stock_opname_date').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find(lprefix_id+'_product_stock_opname_status').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find(lprefix_id+'_warehouse').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find(lprefix_id+'_description').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find(lprefix_id+'_notes').closest('div [class*="form-group"]').show();
                    break;
            }
            
            if(lmethod === 'view'){
                $(lparent_pane).find(lprefix_id+'_print').show();
            }
        },        
        enable_disable: function(){
            var lparent_pane = pso_parent_pane;
            var lmethod = $(lparent_pane).find('#pso_method').val();  
            var lprefix_id = pso_component_prefix_id;
            pso_methods.disable_all();
            
            switch(lmethod){
                case "add":
                    $(lparent_pane).find(lprefix_id+"_store").select2('enable');
                    $(lparent_pane).find(lprefix_id+'_warehouse').select2('enable');
                    $(lparent_pane).find(lprefix_id+"_checker").prop("disabled",false);
                    $(lparent_pane).find(lprefix_id+"_notes").prop("disabled",false);
                    $(lparent_pane).find(lprefix_id+"_checker").prop("disabled",false);
                    break;
                case 'view':
                    $(lparent_pane).find(lprefix_id+"_notes").prop("disabled",false);
                    
                    break;
            }
        },
        reset_all:function(){
            var lparent_pane = pso_parent_pane;
            var lprefix_id = pso_component_prefix_id;
            $(lparent_pane).find(lprefix_id+'_code').val('[AUTO GENERATE]');
            
            APP_FORM.store.store_set($(lparent_pane).find(lprefix_id+'_store'));
                
            APP_FORM.status.default_status_set('product_stock_opname',
                $(lparent_pane).find(lprefix_id+'_product_stock_opname_status')
            );
            
            pso_product_methods.load_product([]);
        },
        submit:function(){
            var parent_pane = pso_parent_pane;
            var lprefix_id = pso_component_prefix_id;
            var ajax_url = pso_index_url;
            var lmethod = $(parent_pane).find(lprefix_id+"_method").val();
            var pso_id = $(parent_pane).find(lprefix_id+"_id").val();        
            var json_data = {
                ajax_post:true,
                pso:{},
                message_session:true
            };
            
            switch(lmethod){
                case 'add':
                    json_data.pso.store_id = $(parent_pane).find(lprefix_id+"_store").select2('val');
                    json_data.pso.warehouse_id = $(parent_pane).find(lprefix_id+"_warehouse").select2('val');
                    json_data.pso.notes = $(parent_pane).find(lprefix_id+"_notes").val();
                    json_data.pso.checker = $(parent_pane).find(lprefix_id+'_checker').val();
                    json_data.pso_product = pso_product_table_method.get_data_table();
                    
                    break;
                case 'view':
                    json_data.pso.product_stock_opname_status = $(parent_pane).find(lprefix_id+'_product_stock_opname_status').select2('val');
                    json_data.pso.notes = $(parent_pane).find(lprefix_id+"_notes").val();
                    json_data.pso.description = $(parent_pane).find(lprefix_id+'_description').val();
                    break;
            }
            
            var lajax_method='';
            switch(lmethod){
                case 'add':
                    lajax_method = 'pso_add';
                    break;
                case 'view':
                    lajax_method = $(parent_pane).find(lprefix_id+'_product_stock_opname_status').select2('data').method;
                    break;
            }
            ajax_url +=lajax_method+'/'+pso_id;
            var result = null;
            result = APP_DATA_TRANSFER.submit(ajax_url,json_data);
            if(result.success ===1){
                $(pso_parent_pane).find(lprefix_id+'_id').val(result.trans_id);
                if(pso_view_url !==''){
                    var url = pso_view_url+result.trans_id;
                    window.location.href=url;
                }
                else{
                    pso_after_submit();
                }
            }
        },
        
    }

    var pso_bind_event = function(){
        var parent_pane = pso_parent_pane;
        var lprefix_id = pso_component_prefix_id;
        
        $(parent_pane).find(lprefix_id+'_submit').off('click');
        APP_COMPONENT.button.submit.set($(parent_pane).find(lprefix_id+'_submit'),{
            parent_pane:parent_pane,
            module_method:pso_methods
        });
        
        $(parent_pane).find(lprefix_id+'_print').off('click');
        $(parent_pane).find(lprefix_id+'_print').on('click',function(e){
            var lparent_pane = pso_parent_pane;
            var lprefix_id = pso_component_prefix_id;
            var lid = $(lparent_pane).find(lprefix_id+'_id').val();
            e.preventDefault();
            modal_print.init();
            modal_print.menu.add('Product Stock Opname',pso_index_url+'pso_print/product_stock_opname/'+lid);
            modal_print.show();
        });
        
        pso_product_bind_event();
    }
    
    var pso_components_prepare= function(){
        
        var method = $(pso_parent_pane).find("#pso_method").val();
        
        
        var pso_data_set = function(){
            var lparent_pane = pso_parent_pane;
            var lprefix_id = pso_component_prefix_id;
            switch(method){
                case "add":
                    pso_methods.reset_all();      
                    break;
                case "view":
                    var pso_id = $(pso_parent_pane).find(lprefix_id+"_id").val();
                    var json_data={data:pso_id};
                    var lresponse = APP_DATA_TRANSFER.ajaxPOST(pso_data_support_url+"product_stock_opname_get",json_data).response;
                    
                    if(lresponse != []){
                        var lpso = lresponse.pso;
                        pso_data.product_stock_opname_status_curr = lpso.product_stock_opname_status;
                        $(lparent_pane).find(lprefix_id+'_store')
                            .select2('data',{id:lpso.store_id
                                ,text:lpso.store_text}).change();
                        $(lparent_pane).find(lprefix_id+"_code").val(lpso.code);
                        $(lparent_pane).find(lprefix_id+'_product_stock_opname_date').datetimepicker({value:lpso.product_stock_opname_date});
                        $(lparent_pane).find(lprefix_id+"_description").val(lpso.description);
                        $(lparent_pane).find(lprefix_id+"_notes").val(lpso.notes);
                        $(lparent_pane).find(lprefix_id+"_checker").val(lpso.checker);
                        $(lparent_pane).find(lprefix_id+'_product_stock_opname_status')
                            .select2('data',{id:lpso.product_stock_opname_status
                                ,text:lpso.product_stock_opname_status_text}).change();
                        $(lparent_pane).find(lprefix_id+'_product_stock_opname_status')
                            .select2({data:lresponse.product_stock_opname_status_list});
                        $(lparent_pane).find(lprefix_id+'_warehouse').select2('data',{id:lpso.warehouse_id,text:lpso.warehouse_text});
                        pso_product_methods.load_product(lresponse.pso_product);
                        
                        if(pso_data.product_stock_opname_status_curr === 'process'){
                            $(lparent_pane).find(lprefix_id+"_description").prop("disabled",false);
                        }
                        
                    };
                    
                    
                    
                    break;            
            }
        }
    
        pso_methods.enable_disable();
        pso_methods.show_hide();
        pso_data_set();
    }
    
</script>