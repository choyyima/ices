<script>

    var sales_receipt_parent_pane = $('<?php echo $detail_tab; ?>')[0];
    var sales_receipt_ajax_url = null;
    var sales_receipt_index_url = null;
    var sales_receipt_view_url = null;
    var sales_receipt_window_scroll = null;
    var sales_receipt_data_support_url = null;
    var sales_receipt_common_ajax_listener = null;
    var sales_receipt_component_prefix_id = '';
    
    var sales_receipt_insert_dummy = false;

    var sales_receipt_init = function(){
        var parent_pane = sales_receipt_parent_pane;
        sales_receipt_ajax_url = '<?php echo $ajax_url ?>';
        sales_receipt_index_url = '<?php echo $index_url ?>';
        sales_receipt_view_url = '<?php echo $view_url ?>';
        sales_receipt_window_scroll = '<?php echo $window_scroll; ?>';
        sales_receipt_data_support_url = '<?php echo $data_support_url; ?>';
        sales_receipt_common_ajax_listener = '<?php echo $common_ajax_listener; ?>';
        sales_receipt_component_prefix_id = '#<?php echo $component_prefix_id; ?>';
        
        sales_receipt_rma_extra_param_get = function(){
            //input select detail use this function to get extra parameter for further query
            
        };
        
        
    }
    
    var sales_receipt_methods = {
        hide_all:function(){
            var lparent_pane = sales_receipt_parent_pane;
            var lc_arr = $(lparent_pane).find('.hide_all').hide();
            $(lparent_pane).find('#sales_receipt_print').hide();
            $(lparent_pane).find('#sales_receipt_submit').hide();
        },
        show_hide:function(){
            var lparent_pane = sales_receipt_parent_pane;
            var lmethod = $(lparent_pane).find('#sales_receipt_method').val();
            var lprefix_id = sales_receipt_component_prefix_id;
            sales_receipt_methods.hide_all();
            
            switch(lmethod){
                case 'add':                    
                    
                case 'view':
                    $(lparent_pane).find(lprefix_id+'_submit').show();
                    $(lparent_pane).find(lprefix_id+'_payment_type').closest('.form-group').show();
                    $(lparent_pane).find(lprefix_id+'_code').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find(lprefix_id+'_store').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find(lprefix_id+'_customer').closest('.form-group').show();
                    $(lparent_pane).find(lprefix_id+'_sales_receipt_date').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find(lprefix_id+'_sales_receipt_status').closest('div [class*="form-group"]').show();
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
            var lparent_pane = sales_receipt_parent_pane;
            APP_COMPONENT.disable_all(lparent_pane);
        },
        enable_disable:function(){
            var lparent_pane = sales_receipt_parent_pane;
            var lmethod = $(lparent_pane).find('#sales_receipt_method').val();    
            var lprefix_id = sales_receipt_component_prefix_id;
            sales_receipt_methods.disable_all();
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
                    var lsales_receipt_status_val = $(lparent_pane).find(lprefix_id+'_sales_receipt_status').select2('val');
                    $(lparent_pane).find(lprefix_id+'_notes').prop('disabled',false);
                    if(lpayment_type_data !== null && lsales_receipt_status_val === 'invoiced'){
                        if(lpayment_type_data.payment_type_code === 'CASH' && ldeposit_date === ''){
                            $(lparent_pane).find(lprefix_id+'_deposit_date').prop('disabled',false);
                        }
                    }
                    break;
            }
        },
        reset_all:function(){
            var lparent_pane = sales_receipt_parent_pane;
            var lprefix_id = sales_receipt_component_prefix_id;
            $(lparent_pane).find(lprefix_id+'_code').val('[AUTO GENERATE]');
            var ldefault_status = null;
            ldefault_status = APP_DATA_TRANSFER.common_ajax_listener('module_status/default_status_get',{module:'sales_receipt'}).response;

            $(lparent_pane).find(lprefix_id+'_sales_receipt_status')
                    .select2('data',{id:ldefault_status.val,text:ldefault_status.label}).change();
            var lstatus_list = [
                {id:ldefault_status.val,text:ldefault_status.label}
            ];
            
            $(lparent_pane).find('#dof_delivery_order_final_status').
                select2({data:lstatus_list});
            
            
            var ldefault_store = APP_DATA_TRANSFER.ajaxPOST(APP_PATH.base_url+
                'store/data_support/default_store_get/').response;
            $(lparent_pane).find(lprefix_id+'_store').select2('data',
                {id:ldefault_store.id,text:ldefault_store.name}
            );
    
            $(lparent_pane).find(lprefix_id+'_sales_receipt_date').datetimepicker({
                value:APP_GENERATOR.CURR_DATETIME(null, null,'F d, Y H:i')
            });
            $(lparent_pane).find(lprefix_id+'_bos_bank_account').select2('data',null);
            $(lparent_pane).find(lprefix_id+'_outstanding_amount').blur();
            $(lparent_pane).find(lprefix_id+'_change_amount').blur();
            
        },
        submit:function(){
            var lparent_pane = sales_receipt_parent_pane;
            var lprefix_id = sales_receipt_component_prefix_id;
            var lajax_url = sales_receipt_index_url;
            var lmethod = $(lparent_pane).find(lprefix_id+'_method').val();
            var json_data = {
                ajax_post:true,
                message_session:true,
            };

            switch(lmethod){
                case 'add':
                    json_data.sales_receipt = {
                        store_id:$(lparent_pane).find(lprefix_id+'_store').select2('val'),
                        customer_id:$(lparent_pane).find(lprefix_id+'_customer').select2('val'),
                        payment_type_id: $(lparent_pane).find(lprefix_id+'_payment_type').select2('val'),
                        customer_bank_acc:$(lparent_pane).find(lprefix_id+'_customer_bank_acc').val(),
                        bos_bank_account_id:$(lparent_pane).find(lprefix_id+'_bos_bank_account').select2('val'),
                        amount:$(lparent_pane).find(lprefix_id+'_amount').val().replace(/[^0-9.]/g,''),
                        change_amount:$(lparent_pane).find(lprefix_id+'_change_amount').val().replace(/[^0-9.]/g,''),
                        notes:$(lparent_pane).find(lprefix_id+'_notes').val(),
                    }
                    lajax_url +='sales_receipt_add/';
                    break;
                case 'view':
                    json_data.sales_receipt = {
                        notes: $(lparent_pane).find(lprefix_id+'_notes').val(),
                        sales_receipt_status: $(lparent_pane).find(lprefix_id+'_sales_receipt_status').select2('val'),
                        cancellation_reason: $(lparent_pane).find(lprefix_id+'_sales_receipt_cancellation_reason').val(),
                        deposit_date:$(lparent_pane).find(lprefix_id+'_deposit_date').val()===''?null:$(lparent_pane).find(lprefix_id+'_deposit_date').val(),
                    }
                    
                    var sales_receipt_id = $(lparent_pane).find(lprefix_id+'_id').val();
                    var lajax_method = $(lparent_pane).find(lprefix_id+'_sales_receipt_status').
                        select2('data').method;
                    lajax_url +=lajax_method+'/'+sales_receipt_id;
                    break;
            }
            
            result = APP_DATA_TRANSFER.submit(lajax_url,json_data);
            
            APP_DATA_TRANSFER.after_submit({
                result:result,
                func_after_submit:sales_receipt_after_submit,
                view_url:sales_receipt_view_url,
            });

        }
    };
    
    var sales_receipt_bind_event = function(){
        var lparent_pane = sales_receipt_parent_pane;
        var lprefix_id = sales_receipt_component_prefix_id;
        
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
            var lparent_pane = sales_receipt_parent_pane;
            var lprefix_id = sales_receipt_component_prefix_id;
            var lmethod = $(lparent_pane).find(lprefix_id+'_method').val();
            if(lmethod === 'add'){
                $(lparent_pane).find(lprefix_id+'_payment_type').select2({data:[]});
                if($(this).select2('val')!==''){
                    var ljson_data = {customer_id:$(this).select2('val')}
                    var lresponse = APP_DATA_TRANSFER.ajaxPOST(sales_receipt_data_support_url+'input_select_payment_type_get/',ljson_data).response;
                    $(lparent_pane).find(lprefix_id+'_payment_type').select2({data:lresponse});
                    $(lparent_pane).find(lprefix_id+'_payment_type').select2('data',lresponse[0]);
                    $(lparent_pane).find(lprefix_id+'_payment_type').change();
                }
            }
        });
        
        $(lparent_pane).find(lprefix_id+'_payment_type').on('change',function(){
            var lprefix_id = sales_receipt_component_prefix_id;
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
        
        
        $(lparent_pane).find('#sales_receipt_submit').off();        
        $(lparent_pane).find('#sales_receipt_submit')
        .on('click',function(e){
            e.preventDefault();
            var btn = $(this);
            btn.addClass('disabled');
            var lparent_pane = sales_receipt_parent_pane;
            modal_confirmation_submit_parent = $(lparent_pane).attr('class').indexOf('modal-body')!==-1?
                $(lparent_pane).closest('.modal'):null;
            $('#modal_confirmation_submit').modal('show');
            $('#modal_confirmation_submit_btn_submit').on('click',function(){
                sales_receipt_methods.submit();
                $('#modal_confirmation_submit').modal('hide');

            });
            
            $(sales_receipt_window_scroll).scrollTop(0);        
            setTimeout(function(){btn.removeClass('disabled')},1000);            
        });
            
        
    }
    
    var sales_receipt_components_prepare = function(){
        

        var sales_receipt_data_set = function(){
            var lparent_pane = sales_receipt_parent_pane;
            var lprefix_id = sales_receipt_component_prefix_id;
            var lmethod = $(lparent_pane).find('#sales_receipt_method').val();
            
            switch(lmethod){
                case 'add':
                    sales_receipt_methods.reset_all();
                    break;
                case 'view':
                    
                    var lsales_receipt_id = $(lparent_pane).find('#sales_receipt_id').val();
                    var lajax_url = sales_receipt_data_support_url+'sales_receipt_get/';
                    var json_data = {data:lsales_receipt_id};
                    var lresponse = APP_DATA_TRANSFER.ajaxPOST(lajax_url,json_data).response;
                    var lsales_receipt = lresponse.sales_receipt;
                    var lreference = lresponse.reference;
                    var lreference_detail = lresponse.reference_detail;
                    
                    $(lparent_pane).find(lprefix_id+'_payment_type').select2('data',{id:lsales_receipt.payment_type_id,text:lsales_receipt.payment_type_text,payment_type_code:lsales_receipt.payment_type_code});
                    $(lparent_pane).find(lprefix_id+'_store').select2('data',{id:lsales_receipt.store_id
                        ,text:lsales_receipt.store_text});
                    $(lparent_pane).find(lprefix_id+'_code').val(lsales_receipt.code);
                    $(lparent_pane).find(lprefix_id+'_customer_bank_acc').val(lsales_receipt.customer_bank_acc);                    
                    $(lparent_pane).find(lprefix_id+'_bos_bank_account').select2('data',{id:lsales_receipt.bos_bank_account_id,text:lsales_receipt.bos_bank_account_text});
                    $(lparent_pane).find(lprefix_id+'_amount').val(lsales_receipt.amount);
                    $(lparent_pane).find(lprefix_id+'_outstanding_amount').val(lsales_receipt.outstanding_amount);
                    $(lparent_pane).find(lprefix_id+'_change_amount').val(lsales_receipt.change_amount);
                    $(lparent_pane).find(lprefix_id+'_sales_receipt_date').datetimepicker({value:lsales_receipt.sales_receipt_date});
                    $(lparent_pane).find(lprefix_id+'_deposit_date').datetimepicker({value:lsales_receipt.deposit_date});
                    $(lparent_pane).find(lprefix_id+'_sales_receipt_cancellation_reason').val(lsales_receipt.cancellation_reason);
                    
                    $(lparent_pane).find(lprefix_id+'_customer')
                            .select2('data',{id:lsales_receipt.customer_id
                                ,text:lsales_receipt.customer_text});
                                        
                    $(lparent_pane).find(lprefix_id+'_notes').val(lsales_receipt.notes);
                    
                    sales_receipt_methods.show_hide();
                    sales_receipt_methods.enable_disable();
                    
                    $(lparent_pane).find(lprefix_id+'_sales_receipt_status')
                            .select2({data:lresponse.sales_receipt_status_list});
                    
                     $(lparent_pane).find(lprefix_id+'_sales_receipt_status')
                        .select2('data',{id:lsales_receipt.sales_receipt_status
                            ,text:lsales_receipt.sales_receipt_status_text}).change();
                    
                    break;
            }
        }
        
        
        sales_receipt_methods.enable_disable();
        sales_receipt_methods.show_hide();
        sales_receipt_data_set();
    }
    
    var sales_receipt_after_submit = function(){
        //function that will be executed after submit 
    }
    
   
    
    
    
    
    
</script>