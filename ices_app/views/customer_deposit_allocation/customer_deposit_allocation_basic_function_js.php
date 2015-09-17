<script>

    var customer_deposit_allocation_parent_pane = $('<?php echo $detail_tab; ?>')[0];
    var customer_deposit_allocation_ajax_url = null;
    var customer_deposit_allocation_index_url = null;
    var customer_deposit_allocation_view_url = null;
    var customer_deposit_allocation_window_scroll = null;
    var customer_deposit_allocation_data_support_url = null;
    var customer_deposit_allocation_common_ajax_listener = null;
    
    var customer_deposit_allocation_insert_dummy = true;

    var customer_deposit_allocation_init = function(){
        var parent_pane = customer_deposit_allocation_parent_pane;
        customer_deposit_allocation_ajax_url = '<?php echo $ajax_url ?>';
        customer_deposit_allocation_index_url = '<?php echo $index_url ?>';
        customer_deposit_allocation_view_url = '<?php echo $view_url ?>';
        customer_deposit_allocation_window_scroll = '<?php echo $window_scroll; ?>';
        customer_deposit_allocation_data_support_url = '<?php echo $data_support_url; ?>';
        customer_deposit_allocation_common_ajax_listener = '<?php echo $common_ajax_listener; ?>';
        
        customer_deposit_allocation_customer_deposit_extra_param_get = function(){
            var lparent_pane = customer_deposit_allocation_parent_pane;            
            var lresult = {};
            lresult.customer_id = $('#customer_deposit_allocation_customer_id').val();
            var lref_val = $('#customer_deposit_allocation_reference').select2('val');
            if(lref_val !== ''){
                lresult.reference_id = lref_val;
                lresult.reference_type = $('#customer_deposit_allocation_reference').select2('data').reference_type;
            }
            return lresult;
        };
        
        customer_deposit_allocation_reference_extra_param_get = function(){
            var lparent_pane = customer_deposit_allocation_parent_pane;            
            var lresult = {};
            lresult.customer_id = $('#customer_deposit_allocation_customer_id').val();
            var lref_val = $('#customer_deposit_allocation_customer_deposit').select2('val');
            if(lref_val !== ''){
                lresult.customer_deposit_type = $('#customer_deposit_allocation_customer_deposit').select2('data').reference_type;
            }
            return lresult;            
        };
        
        
    }
    
    var customer_deposit_allocation_component = {
        allocated_amount: {
            reset:function(){
                var lparent_pane = customer_deposit_allocation_parent_pane;
                var lallocated_amount_input = $(lparent_pane).find('#customer_deposit_allocation_allocated_amount')[0];
                $(lallocated_amount_input).off();
                APP_COMPONENT.input.numeric(lallocated_amount_input,{max_val:0,min_val:0});
                $(lallocated_amount_input).val('').blur();
            }
        },
        
    }
    
    var customer_deposit_allocation_methods = {
        hide_all:function(){
            var lparent_pane = customer_deposit_allocation_parent_pane;
            var lc_arr = $(lparent_pane).find('.hide_all').hide();
            $(lparent_pane).find('#customer_deposit_allocation_print').hide();
            $(lparent_pane).find('#customer_deposit_allocation_submit').hide();
        },
        show_hide:function(){
            var lparent_pane = customer_deposit_allocation_parent_pane;
            var lmethod = $(lparent_pane).find('#customer_deposit_allocation_method').val();
            customer_deposit_allocation_methods.hide_all();
            
            switch(lmethod){
                case 'add':                    
                    $(lparent_pane).find('#customer_deposit_allocation_submit').show();
                    $(lparent_pane).find('#customer_deposit_allocation_code').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#customer_deposit_allocation_store').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#customer_deposit_allocation_customer_deposit').closest('.form-group').show();
                    $(lparent_pane).find('#customer_deposit_allocation_reference').closest('.form-group').show();
                    $(lparent_pane).find('#customer_deposit_allocation_customer_deposit_allocation_date').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#customer_deposit_allocation_customer_deposit_allocation_status').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#customer_deposit_allocation_allocated_amount').closest('.form-group').show();
                    break;
                case 'view':
                    $(lparent_pane).find('#customer_deposit_allocation_submit').show();
                    $(lparent_pane).find('#customer_deposit_allocation_code').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#customer_deposit_allocation_store').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#customer_deposit_allocation_customer_deposit').closest('.form-group').show();
                    $(lparent_pane).find('#customer_deposit_allocation_reference').closest('.form-group').show();
                    $(lparent_pane).find('#customer_deposit_allocation_customer_deposit_allocation_date').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#customer_deposit_allocation_customer_deposit_allocation_status').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#customer_deposit_allocation_allocated_amount').closest('.form-group').show();
                    break;
            }
        },
        disable_all:function(){
            var lparent_pane = customer_deposit_allocation_parent_pane;
            APP_COMPONENT.disable_all(lparent_pane);
        },
        enable_disable:function(){
            var lparent_pane = customer_deposit_allocation_parent_pane;
            var lmethod = $(lparent_pane).find('#customer_deposit_allocation_method').val();    
            customer_deposit_allocation_methods.disable_all();
            switch(lmethod){
                case 'add':
                    $(lparent_pane).find('#customer_deposit_allocation_customer_deposit').select2('enable');
                    $(lparent_pane).find('#customer_deposit_allocation_reference').select2('enable');
                    $(lparent_pane).find('#customer_deposit_allocation_store').select2('enable');
                    $(lparent_pane).find('#customer_deposit_allocation_allocated_amount').prop('disabled',false);
                    break;
                case 'view':
                    break;
            }
        },
        reset_all:function(){
            var lparent_pane = customer_deposit_allocation_parent_pane;
            $(lparent_pane).find('#customer_deposit_allocation_reference').select2('data',null).change();
            $(lparent_pane).find('#customer_deposit_allocation_code').val('[AUTO GENERATE]');
            APP_FORM.status.default_status_set('customer_deposit_allocation',
            $(lparent_pane).find('#customer_deposit_allocation_customer_deposit_allocation_status'));
            
            
            var ldefault_store = APP_DATA_TRANSFER.ajaxPOST(APP_PATH.base_url+
                'store/data_support/default_store_get/').response;
            $(lparent_pane).find('#customer_deposit_allocation_store').select2('data',
                {id:ldefault_store.id,text:ldefault_store.name}
            );
    
            $(lparent_pane).find('#customer_deposit_allocation_customer_deposit_allocation_date').datetimepicker({
                value:APP_GENERATOR.CURR_DATETIME(null, null,'F d, Y H:i')
            });
            customer_deposit_allocation_component.allocated_amount.reset();
            APP_COMPONENT.reference_detail.extra_info_set($(lparent_pane).find('#customer_deposit_allocation_reference_detail'),[],{reset:true});
            
        },
        reference_reset_dependency:function(){
            var lparent_pane = customer_deposit_allocation_parent_pane;
            APP_COMPONENT.reference_detail.extra_info_set($(lparent_pane).find('#customer_deposit_allocation_reference_detail'),[],{reset:true});
            customer_deposit_allocation_component.allocated_amount.reset();
        },
        max_allocated_amount_set:function(){
            var lparent_pane = customer_deposit_allocation_parent_pane;
            var lallocated_amount_input = $(lparent_pane).find('#customer_deposit_allocation_allocated_amount')[0];
            var lcust_dep_outstanding_amount = $(lparent_pane).find('#customer_deposit_allocation_customer_deposit_detail_outstanding_amount').text().replace(/[^0-9.]/g,'');
            var lref_outstanding_amount = $(lparent_pane).find('#customer_deposit_allocation_reference_detail_outstanding_amount').text().replace(/[^0-9.]/g,'');
            var lmax_val = parseFloat(lcust_dep_outstanding_amount)>parseFloat(lref_outstanding_amount)?
                lref_outstanding_amount:lcust_dep_outstanding_amount;
            $(lallocated_amount_input).off();
            APP_COMPONENT.input.numeric(lallocated_amount_input,{min_val:0,max_val:lmax_val});
        },
        submit:function(){
            var lparent_pane = customer_deposit_allocation_parent_pane;
            var lajax_url = customer_deposit_allocation_index_url;
            var lmethod = $(lparent_pane).find('#customer_deposit_allocation_method').val();
            var json_data = {
                ajax_post:true,
                message_session:true,
            };

            switch(lmethod){
                case 'add':
                    json_data.customer_deposit_allocation ={
                        customer_deposit_allocation_type:$(lparent_pane).find('#customer_deposit_allocation_type').val(),
                        allocated_amount:$(lparent_pane).find('#customer_deposit_allocation_allocated_amount').val().replace(/[^0-9.]/g,''),
                        store_id:$(lparent_pane).find('#customer_deposit_allocation_store').select2('val'),
                    };
                    json_data.reference={
                        id:$(lparent_pane).find('#customer_deposit_allocation_reference').select2('val')
                    };
                    json_data.customer_deposit={
                        id:$(lparent_pane).find('#customer_deposit_allocation_customer_deposit').select2('val')
                    };
                    
                    lajax_url +='customer_deposit_allocation_add/';
                    break;
                case 'view':
                    json_data.customer_deposit_allocation = {
                        customer_deposit_allocation_status :$(lparent_pane).find('#customer_deposit_allocation_customer_deposit_allocation_status').select2('val'),
                        cancellation_reason :$(lparent_pane).find('#customer_deposit_allocation_customer_deposit_allocation_cancellation_reason').val()
                    }
                    var customer_deposit_allocation_id = $(lparent_pane).find('#customer_deposit_allocation_id').val();
                    var lajax_method = $(lparent_pane).find('#customer_deposit_allocation_customer_deposit_allocation_status').select2('data').method;
                    lajax_url +=lajax_method+'/'+customer_deposit_allocation_id;
                    break;
            }
            
            result = APP_DATA_TRANSFER.submit(lajax_url,json_data);

            if(result.success ===1){
                $(lparent_pane).find('#customer_deposit_allocation_id').val(result.trans_id);
                if(customer_deposit_allocation_view_url !==''){
                    var url = customer_deposit_allocation_view_url+result.trans_id;
                    window.location.href=url;
                }
                else{
                    customer_deposit_allocation_after_submit();
                }
            }
        }
    };
    
    var customer_deposit_allocation_bind_event = function(){
        var lparent_pane = customer_deposit_allocation_parent_pane;
        var lprefix_id = '#customer_deposit_allocation';
        
        APP_COMPONENT.input.numeric($(lparent_pane).find('#customer_deposit_allocation_allocated_amount'),{min_val:0,max_val:0});
        $(lparent_pane).find('#customer_deposit_allocation_allocated_amount').blur();
        
        $(lparent_pane).find('#customer_deposit_allocation_customer_deposit').on('change',function(){
            customer_deposit_allocation_methods.max_allocated_amount_set();
        });
        
        $(lparent_pane).find('#customer_deposit_allocation_reference').on('change',function(){
            var lparent_pane = customer_deposit_allocation_parent_pane;
            var lajax_url = customer_deposit_allocation_data_support_url;
            var lreference_data = $(this).select2('data');
            customer_deposit_allocation_methods.reference_reset_dependency();
            
            if(lreference_data !== null){
                var ljson_data = {reference_id :lreference_data.id, reference_type: lreference_data.reference_type}
                $(lparent_pane).find('#customer_deposit_allocation_type').val(lreference_data.reference_type);

                var lresponse = APP_DATA_TRANSFER.ajaxPOST(lajax_url+'input_select_reference_detail_get/',ljson_data).response;
                
                var lreference_detail = lresponse.reference_detail;
                APP_COMPONENT.reference_detail.extra_info_set($(lparent_pane).find('#customer_deposit_allocation_reference_detail'),lresponse.reference_detail,{reset:true});
                
                customer_deposit_allocation_methods.max_allocated_amount_set();
            }                
        });
        
        $(lparent_pane).find(lprefix_id+'_submit').off('click');
        APP_COMPONENT.button.submit.set($(lparent_pane).find(lprefix_id+'_submit'),{
            parent_pane:lparent_pane,
            module_method:customer_deposit_allocation_methods
        });
            
        
    }
    
    var customer_deposit_allocation_components_prepare = function(){
        

        var customer_deposit_allocation_data_set = function(){
            var lparent_pane = customer_deposit_allocation_parent_pane;
            var lmethod = $(lparent_pane).find('#customer_deposit_allocation_method').val();
            
            switch(lmethod){
                case 'add':
                    customer_deposit_allocation_methods.reset_all();
                    if(customer_deposit_allocation_insert_dummy){
                        
                    }
                    break;
                case 'view':
                    
                    var lcustomer_deposit_allocation_id = $(lparent_pane).find('#customer_deposit_allocation_id').val();
                    var lajax_url = customer_deposit_allocation_data_support_url+'customer_deposit_allocation_get/';
                    var json_data = {data:lcustomer_deposit_allocation_id};
                    var lresponse = APP_DATA_TRANSFER.ajaxPOST(lajax_url,json_data).response;
                    var lcomp_prefix = '#customer_deposit_allocation';
                    var lcda = lresponse.cda;
                    var lreference = lresponse.reference;
                    var lcustomer_deposit = lresponse.customer_deposit;
                    
                    
                    $(lparent_pane).find(lcomp_prefix+'_customer_deposit').select2('data',{
                        id:lcustomer_deposit.id, text:lcustomer_deposit.code
                        });
                    $(lparent_pane).find(lcomp_prefix+'_customer_deposit_detail_amount').text(lcustomer_deposit.amount);
                    $(lparent_pane).find(lcomp_prefix+'_customer_deposit_detail_outstanding_amount').text(lcustomer_deposit.outstanding_amount);
                    $(lparent_pane).find(lcomp_prefix+'_customer_deposit_detail_customer_deposit_date').text(lcustomer_deposit.customer_deposit_date);

                    $(lparent_pane).find(lcomp_prefix+'_reference').select2('data',{
                        id:lreference.id, text:lreference.text
                        });
                    
                    APP_COMPONENT.reference_detail.extra_info_set($(lparent_pane).find('#customer_deposit_allocation_reference_detail'),lresponse.reference_detail,{reset:true});

                    $(lparent_pane).find(lcomp_prefix+'_store').select2('data',{id:lcda.store_id
                        ,text:lcda.store_text});
                    $(lparent_pane).find(lcomp_prefix+'_code').val(lcda.code);
                    $(lparent_pane).find(lcomp_prefix+'_allocated_amount').val(lcda.allocated_amount);
                    $(lparent_pane).find(lcomp_prefix+'_customer_deposit_allocation_cancellation_reason').val(lcda.cancellation_reason);

                    $(lparent_pane).find(lcomp_prefix+'_customer_deposit_allocation_status')
                            .select2('data',{id:lcda.cda_status
                                ,text:lcda.cda_status_text}).change();
                    
                    $(lparent_pane).find(lcomp_prefix+'_customer_deposit_allocation_status')
                            .select2({data:lresponse.cda_status_list});
                    
                    break;
            }
        }
        
        
        customer_deposit_allocation_methods.enable_disable();
        customer_deposit_allocation_methods.show_hide();
        customer_deposit_allocation_data_set();
    }
    
    var customer_deposit_allocation_after_submit = function(){
        //function that will be executed after submit 
    }
    
   
    
    
    
    
    
</script>