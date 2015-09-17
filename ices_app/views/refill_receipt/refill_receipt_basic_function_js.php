<script>

    var refill_receipt_parent_pane = $('<?php echo $detail_tab; ?>')[0];
    var refill_receipt_ajax_url = null;
    var refill_receipt_index_url = null;
    var refill_receipt_view_url = null;
    var refill_receipt_window_scroll = null;
    var refill_receipt_data_support_url = null;
    var refill_receipt_common_ajax_listener = null;
    var refill_receipt_component_prefix_id = '';
    
    var refill_receipt_insert_dummy = false;

    var refill_receipt_init = function(){
        var parent_pane = refill_receipt_parent_pane;
        refill_receipt_ajax_url = '<?php echo $ajax_url ?>';
        refill_receipt_index_url = '<?php echo $index_url ?>';
        refill_receipt_view_url = '<?php echo $view_url ?>';
        refill_receipt_window_scroll = '<?php echo $window_scroll; ?>';
        refill_receipt_data_support_url = '<?php echo $data_support_url; ?>';
        refill_receipt_common_ajax_listener = '<?php echo $common_ajax_listener; ?>';
        refill_receipt_component_prefix_id = '#<?php echo $component_prefix_id; ?>';
        
        refill_receipt_rma_extra_param_get = function(){
            //input select detail use this function to get extra parameter for further query
            
        };
        
        
    }
    
    var refill_receipt_methods = {
        hide_all:function(){
            var lparent_pane = refill_receipt_parent_pane;
            var lprefix_id = refill_receipt_component_prefix_id;
            var lc_arr = $(lparent_pane).find('.hide_all').hide();
            $(lparent_pane).find(lprefix_id+'_print').hide();
            $(lparent_pane).find(lprefix_id+'_submit').hide();
        },
        show_hide:function(){
            var lparent_pane = refill_receipt_parent_pane;
            var lprefix_id = refill_receipt_component_prefix_id;
            var lmethod = $(lparent_pane).find(lprefix_id+'_method').val();            
            refill_receipt_methods.hide_all();
            
            switch(lmethod){
                case 'add':                    
                    
                case 'view':
                    $(lparent_pane).find(lprefix_id+'_submit').show();
                    $(lparent_pane).find(lprefix_id+'_payment_type').closest('.form-group').show();
                    $(lparent_pane).find(lprefix_id+'_code').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find(lprefix_id+'_store').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find(lprefix_id+'_customer').closest('.form-group').show();
                    $(lparent_pane).find(lprefix_id+'_refill_receipt_date').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find(lprefix_id+'_refill_receipt_status').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find(lprefix_id+'_customer_bank_acc').closest('.form-group').show();
                    $(lparent_pane).find(lprefix_id+'_bos_bank_account').closest('.form-group').show();
                    $(lparent_pane).find(lprefix_id+'_amount').closest('.form-group').show();
                    $(lparent_pane).find(lprefix_id+'_outstanding_amount').closest('.form-group').show();
                    $(lparent_pane).find(lprefix_id+'_change_amount').closest('.form-group').show();
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
            var lparent_pane = refill_receipt_parent_pane;
            APP_COMPONENT.disable_all(lparent_pane);
        },
        enable_disable:function(){
            var lparent_pane = refill_receipt_parent_pane;
            var lprefix_id = refill_receipt_component_prefix_id;
            var lmethod = $(lparent_pane).find(lprefix_id+'_method').val();    
            refill_receipt_methods.disable_all();
            switch(lmethod){
                case 'add':
                    $(lparent_pane).find(lprefix_id+'_payment_type').select2('enable');
                    $(lparent_pane).find(lprefix_id+'_store').select2('enable');
                    $(lparent_pane).find(lprefix_id+'_customer').select2('enable');
                    $(lparent_pane).find(lprefix_id+'_amount').prop('disabled',false);
                    $(lparent_pane).find(lprefix_id+'_notes').prop('disabled',false);
                    break;
                case 'view':
                    var lpayment_type_data = $(lparent_pane).find(lprefix_id+'_payment_type').select2('data');
                    var ldeposit_date = $(lparent_pane).find(lprefix_id+'_deposit_date').val();
                    var lrefill_receipt_status_val = $(lparent_pane).find(lprefix_id+'_refill_receipt_status').select2('val');
                    $(lparent_pane).find(lprefix_id+'_notes').prop('disabled',false);
                    if(lpayment_type_data !== null && lrefill_receipt_status_val === 'invoiced'){
                        if(lpayment_type_data.payment_type_code === 'CASH' && ldeposit_date === ''){
                            $(lparent_pane).find(lprefix_id+'_deposit_date').prop('disabled',false);
                        }
                    }
                    break;
            }
        },
        reset_all:function(){
            var lparent_pane = refill_receipt_parent_pane;
            var lprefix_id = refill_receipt_component_prefix_id;
            $(lparent_pane).find(lprefix_id+'_code').val('[AUTO GENERATE]');
            var ldefault_status = null;
            ldefault_status = APP_DATA_TRANSFER.common_ajax_listener('module_status/default_status_get',{module:'refill_receipt'}).response;

            $(lparent_pane).find(lprefix_id+'_refill_receipt_status')
                    .select2('data',{id:ldefault_status.val,text:ldefault_status.label}).change();
            var lstatus_list = [
                {id:ldefault_status.val,text:ldefault_status.label}
            ];
                        
            var ldefault_store = APP_DATA_TRANSFER.ajaxPOST(APP_PATH.base_url+
                'store/data_support/default_store_get/').response;
            $(lparent_pane).find(lprefix_id+'_store').select2('data',
                {id:ldefault_store.id,text:ldefault_store.name}
            );
    
            $(lparent_pane).find(lprefix_id+'_refill_receipt_date').datetimepicker({
                value:APP_GENERATOR.CURR_DATETIME(null, null,'F d, Y H:i')
            });
            
            $(lparent_pane).find(lprefix_id+'_bos_bank_account').select2('data',null);
            $(lparent_pane).find(lprefix_id+'_outstanding_amount').blur();
            $(lparent_pane).find(lprefix_id+'_change_amount').blur();
            
        },
        submit:function(){
            var lparent_pane = refill_receipt_parent_pane;
            var lprefix_id = refill_receipt_component_prefix_id;
            var lajax_url = refill_receipt_index_url;
            var lmethod = $(lparent_pane).find(lprefix_id+'_method').val();
            var json_data = {
                ajax_post:true,
                message_session:true,
            };

            switch(lmethod){
                case 'add':
                    json_data.refill_receipt = {
                        store_id:$(lparent_pane).find(lprefix_id+'_store').select2('val'),
                        customer_id:$(lparent_pane).find(lprefix_id+'_customer').select2('val'),
                        payment_type_id: $(lparent_pane).find(lprefix_id+'_payment_type').select2('val'),
                        customer_bank_acc:$(lparent_pane).find(lprefix_id+'_customer_bank_acc').val(),
                        bos_bank_account_id:$(lparent_pane).find(lprefix_id+'_bos_bank_account').select2('val'),
                        amount:$(lparent_pane).find(lprefix_id+'_amount').val().replace(/[^0-9.]/g,''),
                        change_amount:$(lparent_pane).find(lprefix_id+'_change_amount').val().replace(/[^0-9.]/g,''),
                        notes:$(lparent_pane).find(lprefix_id+'_notes').val(),
                    }
                    lajax_url +='refill_receipt_add/';
                    break;
                case 'view':
                    json_data.refill_receipt = {
                        notes: $(lparent_pane).find(lprefix_id+'_notes').val(),
                        refill_receipt_status: $(lparent_pane).find(lprefix_id+'_refill_receipt_status').select2('val'),
                        cancellation_reason: $(lparent_pane).find(lprefix_id+'_refill_receipt_cancellation_reason').val(),
                        deposit_date:$(lparent_pane).find(lprefix_id+'_deposit_date').val()===''?null:$(lparent_pane).find(lprefix_id+'_deposit_date').val(),
                    }
                    
                    var refill_receipt_id = $(lparent_pane).find(lprefix_id+'_id').val();
                    var lajax_method = $(lparent_pane).find(lprefix_id+'_refill_receipt_status').
                        select2('data').method;
                    lajax_url +=lajax_method+'/'+refill_receipt_id;
                    break;
            }
            
            result = APP_DATA_TRANSFER.submit(lajax_url,json_data);

            APP_DATA_TRANSFER.after_submit({
                result:result,
                func_after_submit:refill_receipt_after_submit,
                view_url:refill_receipt_view_url,
            });
        }
    };
    
    var refill_receipt_bind_event = function(){
        var lparent_pane = refill_receipt_parent_pane;
        var lprefix_id = refill_receipt_component_prefix_id;
        
        $(lparent_pane).find(lprefix_id+'_amount').off();
        APP_COMPONENT.input.numeric($(lparent_pane).find(lprefix_id+'_amount')[0],{min_val:0});
        $(lparent_pane).find(lprefix_id+'_amount').blur();
        
        $(lparent_pane).find(lprefix_id+'_amount').on('change',function(){
            var lval = $(this).val().replace(/[^0-9.]/g,'');
            $(lparent_pane).find(lprefix_id+'_change_amount').off();
            APP_COMPONENT.input.numeric($(lparent_pane).find(lprefix_id+'_change_amount')[0],{min_val:0,max_val:lval});
            $(lparent_pane).find(lprefix_id+'_change_amount').val('').blur();
        });
        
        $(lparent_pane).find(lprefix_id+'_outstanding_amount').off();
        APP_COMPONENT.input.numeric($(lparent_pane).find(lprefix_id+'_outstanding_amount')[0],{min_val:0,max_val:0});
        
        $(lparent_pane).find(lprefix_id+'_change_amount').off();
        APP_COMPONENT.input.numeric($(lparent_pane).find(lprefix_id+'_change_amount')[0],{min_val:0,max_val:0});
        
        $(lparent_pane).find(lprefix_id+'_customer').on('change',function(){
            var lparent_pane = refill_receipt_parent_pane;
            var lprefix_id = refill_receipt_component_prefix_id;
            var lmethod = $(lparent_pane).find(lprefix_id+'_method').val();
            if(lmethod === 'add'){
                $(lparent_pane).find(lprefix_id+'_payment_type').select2({data:[]});
                if($(this).select2('val')!==''){
                    var ljson_data = {customer_id:$(this).select2('val')}
                    var lresponse = APP_DATA_TRANSFER.ajaxPOST(refill_receipt_data_support_url+'input_select_payment_type_get/',ljson_data).response;
                    $(lparent_pane).find(lprefix_id+'_payment_type').select2({data:lresponse});
                    $(lparent_pane).find(lprefix_id+'_payment_type').select2('data',lresponse[0]);
                    $(lparent_pane).find(lprefix_id+'_payment_type').change();
                }
            }
        });
        
        $(lparent_pane).find(lprefix_id+'_payment_type').on('change',function(){
            $(lparent_pane).find(lprefix_id+'_change_amount').prop('disabled',true);
            $(lparent_pane).find(lprefix_id+'_customer_bank_acc').prop('disabled',true);
            $(lparent_pane).find(lprefix_id+'_bos_bank_account').select2('disable');
            $(lparent_pane).find(lprefix_id+'_bos_bank_account').select2('data',null);
                    
            $(lparent_pane).find(lprefix_id+'_change_amount').val('').blur();
            if($(this).select2('data').code==='CASH'){
                $(lparent_pane).find(lprefix_id+'_change_amount').prop('disabled',false);
            }
            else{
                $(lparent_pane).find(lprefix_id+'_customer_bank_acc').prop('disabled',false);
                $(lparent_pane).find(lprefix_id+'_bos_bank_account').select2('enable');

            }
        });
        
        $(lparent_pane).find(lprefix_id+'_submit').off('click');
        APP_COMPONENT.button.submit.set($(lparent_pane).find(lprefix_id+'_submit'),{
            parent_pane:lparent_pane,
            module_method:refill_receipt_methods
        });
        
    }
    
    var refill_receipt_components_prepare = function(){
        

        var refill_receipt_data_set = function(){
            var lparent_pane = refill_receipt_parent_pane;
            var lprefix_id = refill_receipt_component_prefix_id;
            var lmethod = $(lparent_pane).find(lprefix_id+'_method').val();
            
            switch(lmethod){
                case 'add':
                    refill_receipt_methods.reset_all();
                    break;
                case 'view':
                    
                    var lrefill_receipt_id = $(lparent_pane).find(lprefix_id+'_id').val();
                    var lajax_url = refill_receipt_data_support_url+'refill_receipt_get/';
                    var json_data = {data:lrefill_receipt_id};
                    var lresponse = APP_DATA_TRANSFER.ajaxPOST(lajax_url,json_data).response;
                    var lrefill_receipt = lresponse.refill_receipt;
                    var lreference = lresponse.reference;
                    var lreference_detail = lresponse.reference_detail;
                    
                    $(lparent_pane).find(lprefix_id+'_payment_type').select2('data',{id:lrefill_receipt.payment_type_id,text:lrefill_receipt.payment_type_text,payment_type_code:lrefill_receipt.payment_type_code});
                    $(lparent_pane).find(lprefix_id+'_store').select2('data',{id:lrefill_receipt.store_id
                        ,text:lrefill_receipt.store_text});
                    $(lparent_pane).find(lprefix_id+'_code').val(lrefill_receipt.code);
                    $(lparent_pane).find(lprefix_id+'_customer_bank_acc').val(lrefill_receipt.customer_bank_acc);
                    $(lparent_pane).find(lprefix_id+'_bos_bank_account').select2('data',{id:lrefill_receipt.bos_bank_account_id,text:lrefill_receipt.bos_bank_account_text});
                    $(lparent_pane).find(lprefix_id+'_amount').val(lrefill_receipt.amount);
                    $(lparent_pane).find(lprefix_id+'_outstanding_amount').val(lrefill_receipt.outstanding_amount);
                    $(lparent_pane).find(lprefix_id+'_change_amount').val(lrefill_receipt.change_amount);
                    $(lparent_pane).find(lprefix_id+'_refill_receipt_date').datetimepicker({value:lrefill_receipt.refill_receipt_date});
                    $(lparent_pane).find(lprefix_id+'_deposit_date').datetimepicker({value:lrefill_receipt.deposit_date});
                    $(lparent_pane).find(lprefix_id+'_refill_receipt_cancellation_reason').val(lrefill_receipt.cancellation_reason);
                    
                    $(lparent_pane).find(lprefix_id+'_refill_receipt_status')
                            .select2('data',{id:lrefill_receipt.refill_receipt_status
                                ,text:lrefill_receipt.refill_receipt_status_text}).change();
                    
                    $(lparent_pane).find(lprefix_id+'_customer')
                            .select2('data',{id:lrefill_receipt.customer_id
                                ,text:lrefill_receipt.customer_text});
                    
                    $(lparent_pane).find(lprefix_id+'_refill_receipt_status')
                            .select2({data:lresponse.refill_receipt_status_list});
                    
                    $(lparent_pane).find(lprefix_id+'_notes').val(lrefill_receipt.notes);
                    
                    refill_receipt_methods.show_hide();
                    refill_receipt_methods.enable_disable();
                    
                    break;
            }
        }
        
        
        refill_receipt_methods.enable_disable();
        refill_receipt_methods.show_hide();
        refill_receipt_data_set();
    }
    
    var refill_receipt_after_submit = function(){
        //function that will be executed after submit 
    }
    
   
    
    
    
    
    
</script>