<script>

    var customer_refund_parent_pane = $('<?php echo $detail_tab; ?>')[0];
    var customer_refund_ajax_url = null;
    var customer_refund_index_url = null;
    var customer_refund_view_url = null;
    var customer_refund_window_scroll = null;
    var customer_refund_data_support_url = null;
    var customer_refund_common_ajax_listener = null;
    var customer_refund_component_prefix_id = '';
    
    var customer_refund_insert_dummy = true;

    var customer_refund_init = function(){
        var parent_pane = customer_refund_parent_pane;
        customer_refund_ajax_url = '<?php echo $ajax_url ?>';
        customer_refund_index_url = '<?php echo $index_url ?>';
        customer_refund_view_url = '<?php echo $view_url ?>';
        customer_refund_window_scroll = '<?php echo $window_scroll; ?>';
        customer_refund_data_support_url = '<?php echo $data_support_url; ?>';
        customer_refund_common_ajax_listener = '<?php echo $common_ajax_listener; ?>';
        customer_refund_component_prefix_id = '#<?php echo $component_prefix_id; ?>';
        
        customer_refund_extra_param_get = function(){
            //input select detail use this function to get extra parameter for further query
            
        };
        
        
    }
    
    var customer_refund_methods = {
        hide_all:function(){
            var lparent_pane = customer_refund_parent_pane;
            var lc_arr = $(lparent_pane).find('.hide_all').hide();
            $(lparent_pane).find('#customer_refund_print').hide();
            $(lparent_pane).find('#customer_refund_submit').hide();
        },
        show_hide:function(){
            var lparent_pane = customer_refund_parent_pane;
            var lmethod = $(lparent_pane).find('#customer_refund_method').val();
            customer_refund_methods.hide_all();
            
            switch(lmethod){
                case 'add':                    
                    $(lparent_pane).find('#customer_refund_submit').show();
                    $(lparent_pane).find('#customer_refund_code').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#customer_refund_reference').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#customer_refund_store').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#customer_refund_customer_refund_date').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#customer_refund_customer_refund_status').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#customer_refund_amount').closest('.form-group').show();
                    $(lparent_pane).find('#customer_refund_notes').closest('.form-group').show();
                    
                    break;
                case 'view':
                    $(lparent_pane).find('#customer_refund_submit').show();
                    $(lparent_pane).find('#customer_refund_code').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#customer_refund_reference').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#customer_refund_store').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#customer_refund_customer_refund_date').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#customer_refund_customer_refund_status').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#customer_refund_amount').closest('.form-group').show();
                    $(lparent_pane).find('#customer_refund_notes').closest('.form-group').show();
                    break;
            }
        },
        disable_all:function(){
            var lparent_pane = customer_refund_parent_pane;
            APP_COMPONENT.disable_all(lparent_pane);
        },
        enable_disable:function(){
            var lparent_pane = customer_refund_parent_pane;
            var lmethod = $(lparent_pane).find('#customer_refund_method').val();    
            customer_refund_methods.disable_all();
            switch(lmethod){
                case 'add':
                    $(lparent_pane).find('#customer_refund_submit').prop('disabled',false);                    
                    $(lparent_pane).find('#customer_refund_reference').select2('enable');
                    $(lparent_pane).find('#customer_refund_store').select2('enable');
                    $(lparent_pane).find('#customer_refund_customer_refund_status').select2('enable');
                    $(lparent_pane).find('#customer_refund_amount').prop('disabled',false);
                    $(lparent_pane).find('#customer_refund_notes').prop('disabled',false);
                    break;
                case 'view':
                    $(lparent_pane).find('#customer_refund_notes').prop('disabled',false);
                    break;
            }
        },
        reset_all:function(){
            var lparent_pane = customer_refund_parent_pane;
            var lprefix_id = customer_refund_component_prefix_id;
            $(lparent_pane).find(lprefix_id+'_code').val('[AUTO GENERATE]');
            var ldefault_status = null;
            ldefault_status = APP_DATA_TRANSFER.common_ajax_listener('module_status/default_status_get',{module:'customer_refund'}).response;

            $(lparent_pane).find(lprefix_id+'_customer_refund_status')
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
    
            $(lparent_pane).find(lprefix_id+'_customer_refund_date').datetimepicker({
                value:APP_GENERATOR.CURR_DATETIME(null, null,'F d, Y H:i')
            });
            
            
            $(lparent_pane).find(lprefix_id+'_amount').blur();
        },
        submit:function(){
            var lparent_pane = customer_refund_parent_pane;
            var lprefix_id = customer_refund_component_prefix_id;
            var lajax_url = customer_refund_index_url;
            var lmethod = $(lparent_pane).find('#customer_refund_method').val();
            var json_data = {
                ajax_post:true,
                message_session:true,
            };

            switch(lmethod){
                case 'add':
                    json_data.reference_id=$(lparent_pane).find(lprefix_id+'_reference').select2('val');
                    
                    json_data.customer_refund = {
                        customer_refund_type:$(lparent_pane).find(lprefix_id+'_type').val(),
                        store_id:$(lparent_pane).find(lprefix_id+'_store').select2('val'),
                        amount:$(lparent_pane).find(lprefix_id+'_amount').val().replace(/[^0-9.]/g,''),
                        notes:$(lparent_pane).find(lprefix_id+'_notes').val(),
                    };
                    
                    lajax_url +='customer_refund_add/';
                    break;
                case 'view':
                    json_data.customer_refund = {
                        notes:$(lparent_pane).find(lprefix_id+'_notes').val(),
                        customer_refund_status:$(lparent_pane).find(lprefix_id+'_customer_refund_status').select2('val'),
                        cancellation_reason:$(lparent_pane).find(lprefix_id+'_customer_refund_cancellation_reason').val(),
                    };
                    var customer_refund_id = $(lparent_pane).find('#customer_refund_id').val();
                    var lajax_method = $(lparent_pane).find('#customer_refund_customer_refund_status').select2('data').method;
                    lajax_url +=lajax_method+'/'+customer_refund_id;
                    break;
            }
            
            result = APP_DATA_TRANSFER.submit(lajax_url,json_data);

            if(result.success ===1){
                $(lparent_pane).find('#customer_refund_id').val(result.trans_id);
                if(customer_refund_view_url !==''){
                    var url = customer_refund_view_url+result.trans_id;
                    window.location.href=url;
                }
                else{
                    customer_refund_after_submit();
                }
            }
        },
        reference:{
            reset_dependency:function(){
                var lparent_pane = customer_refund_parent_pane;
                var lprefix_id = customer_refund_component_prefix_id;
                $(lparent_pane).find(lprefix_id+'_reference_detail .extra_info').remove();
                
                $(lparent_pane).find(lprefix_id+'_amount').off();
                APP_COMPONENT.input.numeric($(lparent_pane).find(lprefix_id+'_amount')[0],{min_val:0,max_val:0});
                $(lparent_pane).find(lprefix_id+'_amount').blur();
                
                $(lparent_pane).find(lprefix_id+'_type').val('');
            }
        }
    };
    
    var customer_refund_bind_event = function(){
        var lparent_pane = customer_refund_parent_pane;
        var lprefix_id = customer_refund_component_prefix_id;
        
        $(lparent_pane).find(lprefix_id+'_reference').on('change',function(){
            customer_refund_methods.reference.reset_dependency();
            var lparent_pane = customer_refund_parent_pane;
            var lprefix_id = customer_refund_component_prefix_id;
            
            
            if($(this).select2('val')!==''){
                
                var ldata = $(this).select2('data');
                $(lparent_pane).find(lprefix_id+'_type').val(ldata.reference_type);
                var json_data = {
                    reference_id:ldata.id,
                    reference_type:$(lparent_pane).find(lprefix_id+'_type').val(),
                };
                var lajax_url = customer_refund_data_support_url+'input_select_reference_detail_get/';
                var lresponse = APP_DATA_TRANSFER.ajaxPOST(lajax_url, json_data).response;
                APP_COMPONENT.reference_detail.extra_info_set($(lparent_pane).find('#customer_refund_reference_detail'),lresponse);
                

                var loutstanding_amount = $(lparent_pane).find(lprefix_id+'_reference_detail_outstanding_amount').text().replace(/[^0-9.]/g,'');
                $(lparent_pane).find(lprefix_id+'_amount').off();
                APP_COMPONENT.input.numeric($(lparent_pane).find(lprefix_id+'_amount')[0],{min_val:0,max_val:loutstanding_amount});
                $(lparent_pane).find(lprefix_id+'_amount').blur();
                
            }
        });
        
        $(lparent_pane).find(lprefix_id+'_amount').off();
        APP_COMPONENT.input.numeric($(lparent_pane).find(lprefix_id+'_amount')[0],{min_val:0,max_val:0});
        $(lparent_pane).find(lprefix_id+'_amount').blur();
        
        $(lparent_pane).find('#customer_refund_submit').off();        
        $(lparent_pane).find('#customer_refund_submit')
        .on('click',function(e){
            e.preventDefault();
            var btn = $(this);
            btn.addClass('disabled');
            var lparent_pane = customer_refund_parent_pane;
            modal_confirmation_submit_parent = $(lparent_pane).attr('class').indexOf('modal-body')!==-1?
                $(lparent_pane).closest('.modal'):null;
            $('#modal_confirmation_submit').modal('show');
            $('#modal_confirmation_submit_btn_submit').on('click',function(){
                customer_refund_methods.submit();
                $('#modal_confirmation_submit').modal('hide');

            });
            
            $(customer_refund_window_scroll).scrollTop(0);        
            setTimeout(function(){btn.removeClass('disabled')},1000);            
        });
            
        
    }
    
    var customer_refund_components_prepare = function(){
        

        var customer_refund_data_set = function(){
            var lparent_pane = customer_refund_parent_pane;
            var lprefix_id = customer_refund_component_prefix_id;
            var lmethod = $(lparent_pane).find('#customer_refund_method').val();
            
            switch(lmethod){
                case 'add':
                    customer_refund_methods.reset_all();
                    if(customer_refund_insert_dummy){
                        
                    }
                    break;
                case 'view':
                    
                    var lcustomer_refund_id = $(lparent_pane).find('#customer_refund_id').val();
                    var lajax_url = customer_refund_data_support_url+'customer_refund_get/';
                    var json_data = {data:lcustomer_refund_id};
                    var lresponse = APP_DATA_TRANSFER.ajaxPOST(lajax_url,json_data).response;
                    var lcustomer_refund = lresponse.customer_refund;
                    var lreference = lresponse.reference;
                    var lreference_detail = lresponse.reference_detail;
                    
                    $(lparent_pane).find('#customer_refund_reference').select2('data',lreference);
                    APP_COMPONENT.reference_detail.extra_info_set($(lparent_pane).find(lprefix_id+'_reference_detail')[0],lreference_detail);
                    
                    $(lparent_pane).find('#customer_refund_type').val(lcustomer_refund.customer_refund_type);
                    $(lparent_pane).find('#customer_refund_store').select2('data',{id:lcustomer_refund.store_id
                        ,text:lcustomer_refund.store_text});
                    $(lparent_pane).find('#customer_refund_code').val(lcustomer_refund.code);
                    $(lparent_pane).find('#customer_refund_amount').val(lcustomer_refund.amount);
                    $(lparent_pane).find('#customer_refund_customer_refund_date').datetimepicker({value:lcustomer_refund.customer_refund_date});
                    $(lparent_pane).find('#customer_refund_customer_refund_cancellation_reason').val(lcustomer_refund.cancellation_reason);
                    $(lparent_pane).find('#customer_refund_notes').val(lcustomer_refund.notes);
                    
                    $(lparent_pane).find('#customer_refund_customer_refund_status')
                            .select2('data',{id:lcustomer_refund.customer_refund_status
                                ,text:lcustomer_refund.customer_refund_status_text}).change();
                    
                    $(lparent_pane).find('#customer_refund_customer_refund_status')
                            .select2({data:lresponse.customer_refund_status_list});
                    
                    break;
            }
        }
        
        
        customer_refund_methods.enable_disable();
        customer_refund_methods.show_hide();
        customer_refund_data_set();
    }
    
    var customer_refund_after_submit = function(){
        //function that will be executed after submit 
    }
    
   
    
    
    
    
    
</script>