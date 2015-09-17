<script>

    var customer_deposit_parent_pane = $('<?php echo $detail_tab; ?>')[0];
    var customer_deposit_ajax_url = null;
    var customer_deposit_index_url = null;
    var customer_deposit_view_url = null;
    var customer_deposit_window_scroll = null;
    var customer_deposit_data_support_url = null;
    var customer_deposit_common_ajax_listener = null;
    var customer_deposit_component_prefix_id = '';
    
    var customer_deposit_insert_dummy = true;

    var customer_deposit_init = function(){
        var parent_pane = customer_deposit_parent_pane;
        customer_deposit_ajax_url = '<?php echo $ajax_url ?>';
        customer_deposit_index_url = '<?php echo $index_url ?>';
        customer_deposit_view_url = '<?php echo $view_url ?>';
        customer_deposit_window_scroll = '<?php echo $window_scroll; ?>';
        customer_deposit_data_support_url = '<?php echo $data_support_url; ?>';
        customer_deposit_common_ajax_listener = '<?php echo $common_ajax_listener; ?>';
        customer_deposit_component_prefix_id = '#<?php echo $component_prefix_id; ?>';
        
        customer_deposit_rma_extra_param_get = function(){
            //input select detail use this function to get extra parameter for further query
            
        };
        
        
    }
    
    var customer_deposit_methods = {
        hide_all:function(){
            var lparent_pane = customer_deposit_parent_pane;
            var lprefix_id = customer_deposit_component_prefix_id;
            var lc_arr = $(lparent_pane).find('.hide_all').hide();
            $(lparent_pane).find(lprefix_id+'_print').hide();
            $(lparent_pane).find(lprefix_id+'_submit').hide();
        },
        show_hide:function(){
            var lparent_pane = customer_deposit_parent_pane;
            var lmethod = $(lparent_pane).find('#customer_deposit_method').val();
            var lprefix_id = customer_deposit_component_prefix_id;
            customer_deposit_methods.hide_all();
            
            switch(lmethod){
                case 'add':                    

                case 'view':
                    $(lparent_pane).find(lprefix_id+'_submit').show();
                    $(lparent_pane).find(lprefix_id+'_code').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find(lprefix_id+'_reference').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find(lprefix_id+'_store').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find(lprefix_id+'_customer').closest('.form-group').show();
                    $(lparent_pane).find(lprefix_id+'_customer_deposit_date').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find(lprefix_id+'_customer_deposit_status').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find(lprefix_id+'_payment_type').closest('.form-group').show();
                    $(lparent_pane).find(lprefix_id+'_amount').closest('.form-group').show();
                    $(lparent_pane).find(lprefix_id+'_outstanding_amount').closest('.form-group').show();
                    $(lparent_pane).find(lprefix_id+'_customer_bank_acc').closest('.form-group').show();
                    $(lparent_pane).find(lprefix_id+'_bos_bank_account').closest('.form-group').show();
                    $(lparent_pane).find(lprefix_id+'_notes').closest('.form-group').show();
                    break;
            }
            
            if(lmethod === 'view'){
                var lpayment_type_data = $(lparent_pane).find(lprefix_id+'_payment_type').select2('data');
                if(lpayment_type_data !== null){
                    if(lpayment_type_data.payment_type_code === 'CASH'){
                        $(lparent_pane).find(lprefix_id+'_deposit_date').closest('div [class*="form-group"]').show();
                    }
                }
            }
            
        },
        disable_all:function(){
            var lparent_pane = customer_deposit_parent_pane;
            APP_COMPONENT.disable_all(lparent_pane);
        },
        enable_disable:function(){
            var lparent_pane = customer_deposit_parent_pane;
            var lmethod = $(lparent_pane).find('#customer_deposit_method').val();
            var lprefix_id = customer_deposit_component_prefix_id;
            customer_deposit_methods.disable_all();
            switch(lmethod){
                case 'add':
                    $(lparent_pane).find(lprefix_id+'_store').select2('enable');
                    $(lparent_pane).find(lprefix_id+'_reference').select2('enable');
                    $(lparent_pane).find(lprefix_id+'_payment_type').select2('enable');
                    $(lparent_pane).find(lprefix_id+'_customer').select2('disable');
                    $(lparent_pane).find(lprefix_id+'_customer_deposit_date').prop('disabled',false);
                    $(lparent_pane).find(lprefix_id+'_customer_deposit_status').prop('disabled',false);
                    $(lparent_pane).find(lprefix_id+'_amount').prop('disabled',false);
                    $(lparent_pane).find(lprefix_id+'_notes').prop('disabled',false);
                    break;
                case 'view':
                    $(lparent_pane).find(lprefix_id+'_notes').prop('disabled',false);
                    var lpayment_type_data = $(lparent_pane).find(lprefix_id+'_payment_type').select2('data');
                    var ldeposit_date = $(lparent_pane).find(lprefix_id+'_deposit_date').val();
                    var lcustomer_deposit_status_val = $(lparent_pane).find(lprefix_id+'_customer_deposit_status').select2('val');
                    $(lparent_pane).find(lprefix_id+'_notes').prop('disabled',false);
                    if(lpayment_type_data !== null && lcustomer_deposit_status_val === 'invoiced'){
                        if(lpayment_type_data.payment_type_code === 'CASH' && ldeposit_date === ''){
                            $(lparent_pane).find(lprefix_id+'_deposit_date').prop('disabled',false);
                        }
                    }
                    break;
            }
        },
        reset_all:function(){
            var lparent_pane = customer_deposit_parent_pane;
            var lprefix_id = customer_deposit_component_prefix_id;
            
            $(lparent_pane).find(lprefix_id+'_code').val('[AUTO GENERATE]');
            var ldefault_status = null;

            
            APP_FORM.status.default_status_set('customer_deposit',
            $(lparent_pane).find(lprefix_id+'_customer_deposit_status'));
            
            var ldefault_store = APP_DATA_TRANSFER.ajaxPOST(APP_PATH.base_url+
                'store/data_support/default_store_get/').response;
            $(lparent_pane).find(lprefix_id+'_store').select2('data',
                {id:ldefault_store.id,text:ldefault_store.name}
            );
    
            $(lparent_pane).find(lprefix_id+'_customer_deposit_date').datetimepicker({
                value:APP_GENERATOR.CURR_DATETIME(null, null,'F d, Y H:i')
            });
            
            $(lparent_pane).find(lprefix_id+'_bos_bank_account').select2('data',null);
            
            $(lparent_pane).find(lprefix_id+'_outstanding_amount').blur();
            $(lparent_pane).find(lprefix_id+'_change_amount').blur();
            
        },
        submit:function(){
            var lparent_pane = customer_deposit_parent_pane;
            var lajax_url = customer_deposit_index_url;
            var lmethod = $(lparent_pane).find('#customer_deposit_method').val();
            var json_data = {
                ajax_post:true,
                message_session:true,
            };

            var lprefix_id = customer_deposit_component_prefix_id;

            switch(lmethod){
                case 'add':
                    json_data.reference = {
                        id:$(lparent_pane).find(lprefix_id+'_reference').select2('val')
                    };
                    json_data.customer_deposit = {
                        store_id:$(lparent_pane).find(lprefix_id+'_store').select2('val'),
                        customer_id:$(lparent_pane).find(lprefix_id+'_customer').select2('val'),
                        customer_deposit_type: $(lparent_pane).find(lprefix_id+'_type').val(),
                        payment_type_id: $(lparent_pane).find(lprefix_id+'_payment_type').select2('val'),
                        customer_bank_acc:$(lparent_pane).find(lprefix_id+'_customer_bank_acc').val(),
                        bos_bank_account_id:$(lparent_pane).find(lprefix_id+'_bos_bank_account').select2('val'),
                        amount:$(lparent_pane).find(lprefix_id+'_amount').val().replace(/[^0-9.]/g,''),
                        notes:$(lparent_pane).find(lprefix_id+'_notes').val(),
                    }
                    lajax_url +='customer_deposit_add/';
                    break;
                case 'view':
                    json_data.customer_deposit = {
                        notes: $(lparent_pane).find(lprefix_id+'_notes').val(),
                        customer_deposit_status: $(lparent_pane).find(lprefix_id+'_customer_deposit_status').select2('val'),
                        cancellation_reason: $(lparent_pane).find(lprefix_id+'_customer_deposit_cancellation_reason').val(),
                        deposit_date:$(lparent_pane).find(lprefix_id+'_deposit_date').val()===''?null:$(lparent_pane).find(lprefix_id+'_deposit_date').val(),
                    }
                    var customer_deposit_id = $(lparent_pane).find('#customer_deposit_id').val();
                    var lajax_method = $(lparent_pane).find('#customer_deposit_customer_deposit_status').select2('data').method;
                    lajax_url +=lajax_method+'/'+customer_deposit_id;
                    break;
            }
            
            result = APP_DATA_TRANSFER.submit(lajax_url,json_data);

            APP_DATA_TRANSFER.after_submit({
                result:result,
                func_after_submit:customer_deposit_after_submit,
                view_url:customer_deposit_view_url,
            });
            
        },
        reference:{
            reset_dependency:function(){
                var lparent_pane = customer_deposit_parent_pane;
                var lprefix_id = customer_deposit_component_prefix_id;
                $(lparent_pane).find(lprefix_id+'_reference_detail .extra_info').remove();
                
                $(lparent_pane).find(lprefix_id+'_amount').off();
                APP_COMPONENT.input.numeric($(lparent_pane).find(lprefix_id+'_amount')[0],{min_val:0});
                $(lparent_pane).find(lprefix_id+'_amount').val('');
                $(lparent_pane).find(lprefix_id+'_amount').blur();
                
                $(lparent_pane).find(lprefix_id+'_customer').select2('data',null);
                $(lparent_pane).find(lprefix_id+'_customer').change();
            }
        },
    };
    
    var customer_deposit_bind_event = function(){
        var lparent_pane = customer_deposit_parent_pane;
        var lprefix_id = customer_deposit_component_prefix_id;
        
        $(lparent_pane).find(lprefix_id+'_amount').off();
        APP_COMPONENT.input.numeric($(lparent_pane).find(lprefix_id+'_amount')[0],{min_val:0});
        $(lparent_pane).find(lprefix_id+'_amount').val('');
        $(lparent_pane).find(lprefix_id+'_amount').blur();
        
        $(lparent_pane).find(lprefix_id+'_reference').on('change',function(){
            customer_deposit_methods.reference.reset_dependency();
            var lparent_pane = customer_deposit_parent_pane;
            var lprefix_id = customer_deposit_component_prefix_id;
            
            if($(this).select2('val')!==''){                
                var ldata = $(this).select2('data');
                var lreference_type = ldata.reference_type;
                
                $(lparent_pane).find(lprefix_id+'_type').val(lreference_type);
                
                var json_data = {
                    reference_type:$(lparent_pane).find(lprefix_id+'_type').val(),
                    reference_id:$(lparent_pane).find(lprefix_id+'_reference').select2('val'),
                };
                
                var lajax_url = customer_deposit_data_support_url+'input_select_reference_detail_get/';
                var lresponse = APP_DATA_TRANSFER.ajaxPOST(lajax_url, json_data).response;
                APP_COMPONENT.reference_detail.extra_info_set($(lparent_pane).find(lprefix_id+'_reference_detail'),lresponse,{reset:true});
                
                var lajax_url = customer_deposit_data_support_url+'input_select_reference_dependency_get/';
                var lresponse = APP_DATA_TRANSFER.ajaxPOST(lajax_url, json_data).response;

                $(lparent_pane).find(lprefix_id+'_customer').select2('data',{id:lresponse.customer_id,text:lresponse.customer_text});
                $(lparent_pane).find(lprefix_id+'_customer').change();
                
                var lamount_limit = parseFloat(lresponse.total_estimated_amount) - parseFloat(lresponse.total_deposit_amount);
                $(lparent_pane).find(lprefix_id+'_amount').off();
                APP_COMPONENT.input.numeric($(lparent_pane).find(lprefix_id+'_amount')[0],{min_val:0,max_val:lamount_limit});
                $(lparent_pane).find(lprefix_id+'_amount').val(lamount_limit);
                $(lparent_pane).find(lprefix_id+'_amount').blur();
                
            }
        });
        
        $(lparent_pane).find(lprefix_id+'_payment_type').on('change',function(){
            $(lparent_pane).find(lprefix_id+'_change_amount').prop('disabled',true);
            $(lparent_pane).find(lprefix_id+'_customer_bank_acc').prop('disabled',true);
            $(lparent_pane).find(lprefix_id+'_bos_bank_account').select2('disable');
            $(lparent_pane).find(lprefix_id+'_bos_bank_account').select2('data',null);
                    
            $(lparent_pane).find(lprefix_id+'_change_amount').val('').blur();
            if($(this).select2('data').code==='CASH'){
            }
            else{
                $(lparent_pane).find(lprefix_id+'_customer_bank_acc').prop('disabled',false);
                $(lparent_pane).find(lprefix_id+'_bos_bank_name').prop('disabled',false);
                $(lparent_pane).find(lprefix_id+'_bos_bank_acc').prop('disabled',false);
                $(lparent_pane).find(lprefix_id+'_bos_bank_account').select2('enable');

            }
        });
        
        $(lparent_pane).find(lprefix_id+'_customer').on('change',function(){
            $(lparent_pane).find(lprefix_id+'_type').select2({data:[]});
            if($(this).select2('val')!==''){
                var ljson_data = {customer_id:$(this).select2('val')}
                var lresponse = APP_DATA_TRANSFER.ajaxPOST(customer_deposit_data_support_url+'input_select_payment_type_get/',ljson_data).response;
                $(lparent_pane).find(lprefix_id+'_payment_type').select2({data:lresponse});
                $(lparent_pane).find(lprefix_id+'_payment_type').select2('data',lresponse[0]);
                $(lparent_pane).find(lprefix_id+'_payment_type').change();
            }
        });
        
        
        $(lparent_pane).find(lprefix_id+'_submit').off('click');
        APP_COMPONENT.button.submit.set($(lparent_pane).find(lprefix_id+'_submit'),{
            parent_pane:lparent_pane,
            module_method:customer_deposit_methods
        });
            
        
    }
    
    var customer_deposit_components_prepare = function(){
        

        var customer_deposit_data_set = function(){
            var lparent_pane = customer_deposit_parent_pane;
            var lmethod = $(lparent_pane).find('#customer_deposit_method').val();
            var lprefix_id = customer_deposit_component_prefix_id;
            
            switch(lmethod){
                case 'add':
                    customer_deposit_methods.reset_all();
                    break;
                case 'view':
                    
                    var lcustomer_deposit_id = $(lparent_pane).find('#customer_deposit_id').val();
                    var lajax_url = customer_deposit_data_support_url+'customer_deposit_get/';
                    var json_data = {data:lcustomer_deposit_id};
                    var lresponse = APP_DATA_TRANSFER.ajaxPOST(lajax_url,json_data).response;
                    var lcustomer_deposit = lresponse.customer_deposit;
                    var lreference = lresponse.reference;
                    var lreference_detail = lresponse.reference_detail;
                    
                    $(lparent_pane).find(lprefix_id+'_reference').select2('data',{id:lreference.id, text:lreference.text});
                    APP_COMPONENT.reference_detail.extra_info_set($(lparent_pane).find(lprefix_id+'_reference_detail')[0],lreference_detail,{reset:true});
                    $(lparent_pane).find(lprefix_id+'_type').val(lcustomer_deposit.customer_deposit_type);
                    
                    APP_COMPONENT.input_select.empty($(lprefix_id+'_payment_type'));
                    $(lparent_pane).find(lprefix_id+'_payment_type')
                            .select2('data',{id:lcustomer_deposit.payment_type_id,text:lcustomer_deposit.payment_type_text,payment_type_code:lcustomer_deposit.payment_type_code});
                    
                    
                    $(lparent_pane).find(lprefix_id+'_store').select2('data',{id:lcustomer_deposit.store_id
                        ,text:lcustomer_deposit.store_text});
                    $(lparent_pane).find(lprefix_id+'_code').val(lcustomer_deposit.code);
                    $(lparent_pane).find(lprefix_id+'_customer_bank_acc').val(lcustomer_deposit.customer_bank_acc);                    
                    $(lparent_pane).find(lprefix_id+'_bos_bank_account').select2('data',{id:lcustomer_deposit.bos_bank_account_id,text:lcustomer_deposit.bos_bank_account_text});
                    $(lparent_pane).find(lprefix_id+'_amount').val(lcustomer_deposit.amount);
                    $(lparent_pane).find(lprefix_id+'_outstanding_amount').val(lcustomer_deposit.outstanding_amount);
                    $(lparent_pane).find(lprefix_id+'_customer_deposit_date').datetimepicker({value:lcustomer_deposit.customer_deposit_date});
                    $(lparent_pane).find(lprefix_id+'_deposit_date').datetimepicker({value:lcustomer_deposit.deposit_date});
                    $(lparent_pane).find(lprefix_id+'_customer_deposit_cancellation_reason').val(lcustomer_deposit.cancellation_reason);
                    
                    $(lparent_pane).find(lprefix_id+'_customer')
                            .select2('data',{id:lcustomer_deposit.customer_id
                                ,text:lcustomer_deposit.customer_text});
                    
                    customer_deposit_methods.show_hide();
                    customer_deposit_methods.enable_disable();
                    
                    $(lparent_pane).find(lprefix_id+'_customer_deposit_status')
                        .select2('data',{id:lcustomer_deposit.customer_deposit_status
                            ,text:lcustomer_deposit.customer_deposit_status_text}).change();
                        
                    $(lparent_pane).find(lprefix_id+'_customer_deposit_status')
                        .select2({data:lresponse.customer_deposit_status_list});
                    
                    break;
            }
        }
        
        
        customer_deposit_methods.enable_disable();
        customer_deposit_methods.show_hide();
        customer_deposit_data_set();
    }
    
    var customer_deposit_after_submit = function(){
        //function that will be executed after submit 
    }
    
   
    
    
    
    
    
</script>