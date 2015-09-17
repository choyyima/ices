<script>

    var sir_parent_pane = $('<?php echo $detail_tab; ?>')[0];
    var sir_ajax_url = null;
    var sir_index_url = null;
    var sir_view_url = null;
    var sir_window_scroll = null;
    var sir_data_support_url = null;
    var sir_common_ajax_listener = null;
    var sir_component_prefix_id = '';
    
    var sir_insert_dummy = false;
    var sir_insert_dummy_module='';

    var sir_init = function(){
        var parent_pane = sir_parent_pane;
        sir_ajax_url = '<?php echo $ajax_url ?>';
        sir_index_url = '<?php echo $index_url ?>';
        sir_view_url = '<?php echo $view_url ?>';
        sir_window_scroll = '<?php echo $window_scroll; ?>';
        sir_data_support_url = '<?php echo $data_support_url; ?>';
        sir_common_ajax_listener = '<?php echo $common_ajax_listener; ?>';
        sir_component_prefix_id = '#<?php echo $component_prefix_id; ?>';
        sir_rma_extra_param_get = function(){
            //input select detail use this function to get extra parameter for further query
            
        };
        
        
    }
    
    var sir_methods = {
        hide_all:function(){
            var lparent_pane = sir_parent_pane;
            var lc_arr = $(lparent_pane).find('.hide_all').hide();
            $(lparent_pane).find('#sir_print').hide();
            $(lparent_pane).find('#sir_submit').hide();
        },
        show_hide:function(){
            var lparent_pane = sir_parent_pane;
            var lmethod = $(lparent_pane).find('#sir_method').val();
            var lprefix_id = sir_component_prefix_id;
            sir_methods.hide_all();
            
            var lmodule_name = $(lparent_pane).find(lprefix_id+'_module_name').select2('val');
            var lmodule_action = $(lparent_pane).find(lprefix_id+'_module_action').select2('val');
            
            switch(lmethod){
                case 'add':   
                    $(lparent_pane).find(lprefix_id+'_submit').show();
                    $(lparent_pane).find(lprefix_id+'_reference').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find(lprefix_id+'_module_name').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find(lprefix_id+'_module_action').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find(lprefix_id+'_code').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find(lprefix_id+'_store').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find(lprefix_id+'_creator').closest('.form-group').show();
                    $(lparent_pane).find(lprefix_id+'_sir_date').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find(lprefix_id+'_sir_status').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find(lprefix_id+'_notes').closest('.form-group').show();
                    break;
                case 'view':
                    $(lparent_pane).find(lprefix_id+'_submit').show();
                    $(lparent_pane).find(lprefix_id+'_reference').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find(lprefix_id+'_module_name').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find(lprefix_id+'_module_action').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find(lprefix_id+'_code').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find(lprefix_id+'_store').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find(lprefix_id+'_creator').closest('.form-group').show();
                    $(lparent_pane).find(lprefix_id+'_sir_date').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find(lprefix_id+'_sir_status').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find(lprefix_id+'_notes').closest('.form-group').show();
                    break;
            }
            
            switch(lmodule_name+'_'+lmodule_action){
                case 'sales_invoice_pos_cancel':
                    break;
                case 'product_stock_opname':
                    $(lparent_pane).find(lprefix_id+'_stock_opname').closest('.form-group').show();
                    break;
            }
            
            
        },
        disable_all:function(){
            var lparent_pane = sir_parent_pane;
            var lmethod = $(lparent_pane).find('#sir_method').val();
            var lprefix_id = sir_component_prefix_id;
            
            APP_COMPONENT.disable_all(lparent_pane);
            
        },
        enable_disable:function(){
            var lparent_pane = sir_parent_pane;
            var lmethod = $(lparent_pane).find('#sir_method').val();
            var lprefix_id = sir_component_prefix_id;
            sir_methods.disable_all();            
            
            var lmodule_name = $(lparent_pane).find(lprefix_id+'_module_name').select2('val');
            var lmodule_action = $(lparent_pane).find(lprefix_id+'_module_action').select2('val');           
            
            switch(lmethod){
                case 'add':
                    $(lparent_pane).find(lprefix_id+'_store').select2('enable');
                    $(lparent_pane).find(lprefix_id+'_module_name').select2('enable');
                    $(lparent_pane).find(lprefix_id+'_module_action').select2('enable');
                    $(lparent_pane).find(lprefix_id+'_creator').prop('disabled',false);
                    $(lparent_pane).find(lprefix_id+'_description').prop('disabled',false);
                    
                    switch(lmodule_name+'_'+lmodule_action){
                        case 'sales_invoice_pos_cancel':
                        case 'refill_invoice_cancel':
                            $(lparent_pane).find(lprefix_id+'_reference').select2('enable');
                            break;
                        case 'product_stock_opname':
                            break;
                    }
                    
                    break;
                case 'view':
                    break;
            }
            
            
        },
        reset_all:function(){
            var lparent_pane = sir_parent_pane;
            var lprefix_id = sir_component_prefix_id;

            $(lparent_pane).find(lprefix_id+'_code').val('[AUTO GENERATE]');
            
            var ldefault_store = APP_DATA_TRANSFER.ajaxPOST(APP_PATH.base_url+
                'store/data_support/default_store_get/').response;
            $(lparent_pane).find(lprefix_id+'_store').select2('data',
                {id:ldefault_store.id,text:ldefault_store.name}
            );
    
            $(lparent_pane).find(lprefix_id+'_sir_date').datetimepicker({
                value:APP_GENERATOR.CURR_DATETIME(null, null,'F d, Y H:i')
            });
            
            $(lparent_pane).find(lprefix_id+'_creator').val('<?php echo User_Info::get()['name'] ?>');
            
            sir_methods.module_name.reset();
            
            var ldefault_status = null;
            ldefault_status = APP_DATA_TRANSFER.common_ajax_listener('module_status/default_status_get',{module:'sir'}).response;

            $(lparent_pane).find(lprefix_id+'_sir_status')
                    .select2('data',{id:ldefault_status.val,text:ldefault_status.label}).change();
            var lsir_status_list = [
                {id:ldefault_status.val,text:ldefault_status.label}
            ];
            
            $(lparent_pane).find(lprefix_id+'_sir_status').
                select2({data:lsir_status_list});
            
            
        },
        submit:function(){
            var lparent_pane = sir_parent_pane;
            var lprefix_id = sir_component_prefix_id;
            var lajax_url = sir_index_url;
            var lmethod = $(lparent_pane).find('#sir_method').val();
            var json_data = {
                ajax_post:true,
                message_session:true,
            };
            
            var lmodule_name = $(lparent_pane).find(lprefix_id+'_module_name').select2('val')+'_'+
                $(lparent_pane).find(lprefix_id+'_module_action').select2('val');

            json_data.sir = {
                store_id:$(lparent_pane).find(lprefix_id+'_store').val(),
                reference_id:$(lparent_pane).find(lprefix_id+'_reference').select2('val'),
                sir_date:$(lparent_pane).find(lprefix_id+'_sir_date').val(),
                creator:$(lparent_pane).find(lprefix_id+'_creator').val(),
                description:$(lparent_pane).find(lprefix_id+'_description').val(),
                module_name:$(lparent_pane).find(lprefix_id+'_module_name').select2('val'),
                module_action:$(lparent_pane).find(lprefix_id+'_module_action').select2('val')
            };
                
            switch(lmethod){
                case 'add':
                    break;
                case 'view':
                    break;
            }
            
            var sir_id = $(lparent_pane).find('#sir_id').val();
            var lajax_method = '';
            
            if(lmethod === 'add'){
                lajax_method = APP_DATA_TRANSFER.ajaxPOST(sir_data_support_url+'module_method_get/',
                    {
                        module_name:$(lparent_pane).find(lprefix_id+'_module_name').select2('val'),
                        module_action:$(lparent_pane).find(lprefix_id+'_module_action').select2('val'),
                    }
                ).response;
            }
            else {
                lajax_method = $(lparent_pane).find('#sir_sir_status').select2('data').method+'/';
            }
            lajax_url+=lajax_method+sir_id;
            result = APP_DATA_TRANSFER.submit(lajax_url,json_data);

            if(result.success ===1){
                $(lparent_pane).find('#sir_id').val(result.trans_id);
                if(sir_view_url !==''){
                    var url = sir_view_url+result.trans_id;
                    window.location.href=url;
                }
                else{
                    sir_after_submit();
                }
            }
        },
        module_name:{
            reset:function(){
                $(sir_parent_pane).find(sir_component_prefix_id+'_module_name').select2('data',null);
                sir_methods.module_name.reset_dependency();
            },
            reset_dependency:function(){
                sir_methods.module_action.reset();                
            }
        },
        module_action:{
            reset:function(){
                $(sir_parent_pane).find(sir_component_prefix_id+'_module_action').select2('data',null);
                $(sir_parent_pane).find(sir_component_prefix_id+'_module_action').select2({data:[]});
                sir_methods.module_action.reset_dependency();
            },
            reset_dependency:function(){
                sir_methods.reference.reset();
            }
        },
        reference:{
            reset:function(){
                $(sir_parent_pane).find(sir_component_prefix_id+'_reference').select2('data',null);
                sir_methods.reference.reset_dependency();
                
            },            
            reset_dependency:function(){
                $(sir_parent_pane).find(sir_component_prefix_id+'_reference_detail .extra_info').remove(); 
            }
        }
    };
    
    var sir_bind_event = function(){
        var lparent_pane = sir_parent_pane;
        var lprefix_id = sir_component_prefix_id;
        
        $(lparent_pane).find(lprefix_id+'_module_name').on('change',function(){
            sir_methods.module_name.reset_dependency();
            if($(this).select2('val')!=='') {
                var laction = $(this).select2('data')['action'];
                $(lparent_pane).find(lprefix_id+'_module_action').select2({data:laction});
            }
            sir_methods.show_hide();
            sir_methods.enable_disable();
            
            $(lparent_pane).find(lprefix_id+'_reference').select2('data',null);
        });
        
        $(lparent_pane).find(lprefix_id+'_module_action').on('change',function(){
            sir_methods.reference.reset_dependency();
            sir_methods.show_hide();
            sir_methods.enable_disable();
            
            var lparent_pane = sir_parent_pane;
            var lprefix_id = sir_component_prefix_id;


            var lmodule_name = $(lparent_pane).find(lprefix_id+'_module_name').select2('val');
            var lmodule_action = $(lparent_pane).find(lprefix_id+'_module_action').select2('val');
            $(lparent_pane).find(lprefix_id+'_reference').select2('data',null);
            
        });
        
        $(lparent_pane).find(lprefix_id+'_reference').on('change', function(){
            sir_methods.reference.reset_dependency();
            var lparent_pane = sir_parent_pane;
            var lprefix_id = sir_component_prefix_id;
            if($(this).select2('val')!==''){
                var json_data = {
                    module_name:$(lparent_pane).find(lprefix_id+'_module_name').select2('val'),
                    module_action:$(lparent_pane).find(lprefix_id+'_module_action').select2('val'),
                    reference_id:$(lparent_pane).find(lprefix_id+'_reference').select2('val'),
                };
                var lajax_url = sir_data_support_url+'input_select_reference_detail_get/';
                var lresponse = APP_DATA_TRANSFER.ajaxPOST(lajax_url, json_data).response;
                APP_COMPONENT.reference_detail.extra_info_set($(lparent_pane).find('#sir_reference_detail'),lresponse);
            }
        });
        
        $(lparent_pane).find('#sir_submit').off();        
        $(lparent_pane).find('#sir_submit')
        .on('click',function(e){
            e.preventDefault();
            var btn = $(this);
            btn.addClass('disabled');
            var lparent_pane = sir_parent_pane;
            modal_confirmation_submit_parent = $(lparent_pane).attr('class').indexOf('modal-body')!==-1?
                $(lparent_pane).closest('.modal'):null;
            $('#modal_confirmation_submit').modal('show');
            $('#modal_confirmation_submit_btn_submit').on('click',function(){
                sir_methods.submit();
                $('#modal_confirmation_submit').modal('hide');

            });
            
            $(sir_window_scroll).scrollTop(0);        
            setTimeout(function(){btn.removeClass('disabled')},1000);            
        });
            
        
    }
    
    var sir_components_prepare = function(){
        

        var sir_data_set = function(){
            var lparent_pane = sir_parent_pane;
            var lprefix_id = sir_component_prefix_id;
            var lmethod = $(lparent_pane).find('#sir_method').val();
            
            switch(lmethod){
                case 'add':
                    sir_methods.reset_all();
                    break;
                case 'view':
                    
                    var lsir_id = $(lparent_pane).find('#sir_id').val();
                    var lajax_url = sir_data_support_url+'sir_get/';
                    var json_data = {data:lsir_id};
                    var lresponse = APP_DATA_TRANSFER.ajaxPOST(lajax_url,json_data).response;
                    var lsir = lresponse.sir;
                    var lreference = lresponse.reference;
                    var lreference_detail = lresponse.reference_detail;
                    var lmodule_name = lsir.module_name;
                    var lmodule_action = lsir.module_action;
                    
                    $(lparent_pane).find(lprefix_id+'_module_name').select2('data',{id:lsir.module_name, text:lsir.module_name_text});
                    $(lparent_pane).find(lprefix_id+'_module_action').select2('data',{id:lsir.module_action, text:lsir.module_action_text});
                    
                    $(lparent_pane).find(lprefix_id+'_reference').select2('data',{id:lreference.id, text:lreference.text});
                    sir_methods.reference.reset_dependency();
                    APP_COMPONENT.reference_detail.extra_info_set($(lparent_pane).find('#sir_reference_detail'),lreference_detail);
                    
                    $(lparent_pane).find('#sir_store').select2('data',{id:lsir.store_id
                        ,text:lsir.store_text});
                    $(lparent_pane).find('#sir_code').val(lsir.code);
                    $(lparent_pane).find('#sir_sir_date').datetimepicker({value:lsir.sir_date});
                    $(lparent_pane).find('#sir_creator').val(lsir.creator);
                    $(lparent_pane).find('#sir_description').val(lsir.description);

                    switch(lmodule_name+'_'+lmodule_action){
                        case 'product_stock_opname':
                            $(lparent_pane).find(lprefix_id+'_stock_opname_data')[0].innerHTML = JSON.stringify(lresponse.extra_data.product_stock_opname_product);
                            $(lparent_pane).find(lprefix_id+'_stock_opname').closest('.form-group').show();
                            sir_stock_opname.ajax_url = '<?php echo get_instance()->config->base_url().'sir/ajax_search/stock_opname_table_search_view'; ?>';
                            sir_stock_opname.additional_filter = [{id:'sir_reference',type:'select2',field:'product_stock_opname_id'}];
                            sir_stock_opname.methods.data_show(1);
                            
                            break;
                    }
                    
                    $(lparent_pane).find('#sir_sir_status')
                            .select2('data',{id:lsir.sir_status
                                ,text:lsir.sir_status_text}).change();
                    
                    $(lparent_pane).find('#sir_customer')
                            .select2('data',{id:lsir.customer_id
                                ,text:lsir.customer_text}).change();
                    
                    $(lparent_pane).find('#sir_sir_status')
                            .select2({data:lresponse.sir_status_list});
                    
                    break;
            }
        }
        sir_methods.enable_disable();
        sir_methods.show_hide();
        sir_data_set();
    }
    
    var sir_after_submit = function(){
        //function that will be executed after submit 
    }
    
    var sir_reference_extra_param_get = function(){
        var lresult = {};
        var lparent_pane = sir_parent_pane;
        var lprefix_id = sir_component_prefix_id;
        var lmodule_name = $(lparent_pane).find(lprefix_id+'_module_name').select2('val');
        var lmodule_action = $(lparent_pane).find(lprefix_id+'_module_action').select2('val');
        lresult = {module_name:lmodule_name, module_action:lmodule_action};
        return lresult;
    }
    
    
    
    
    
</script>