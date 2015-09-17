<script>

    var sales_receipt_allocation_parent_pane = $('<?php echo $detail_tab; ?>')[0];
    var sales_receipt_allocation_ajax_url = null;
    var sales_receipt_allocation_index_url = null;
    var sales_receipt_allocation_view_url = null;
    var sales_receipt_allocation_window_scroll = null;
    var sales_receipt_allocation_data_support_url = null;
    var sales_receipt_allocation_common_ajax_listener = null;
    var sales_receipt_allocation_component_prefix_id = '';
    
    var sales_receipt_allocation_insert_dummy = true;

    var sales_receipt_allocation_init = function(){
        var parent_pane = sales_receipt_allocation_parent_pane;
        sales_receipt_allocation_ajax_url = '<?php echo $ajax_url ?>';
        sales_receipt_allocation_index_url = '<?php echo $index_url ?>';
        sales_receipt_allocation_view_url = '<?php echo $view_url ?>';
        sales_receipt_allocation_window_scroll = '<?php echo $window_scroll; ?>';
        sales_receipt_allocation_data_support_url = '<?php echo $data_support_url; ?>';
        sales_receipt_allocation_common_ajax_listener = '<?php echo $common_ajax_listener; ?>';
        sales_receipt_allocation_component_prefix_id = '#<?php echo $component_prefix_id; ?>';
        
        sales_receipt_allocation_sales_receipt_extra_param_get = function(){
            var lparent_pane = sales_receipt_allocation_parent_pane;            
            var lresult = {};
            lresult.customer_id = $('#sales_receipt_allocation_customer_id').val();
            return lresult;
        };
        
        sales_receipt_allocation_reference_extra_param_get = function(){
            var lparent_pane = sales_receipt_allocation_parent_pane;            
            var lresult = {};
            lresult.customer_id = $('#sales_receipt_allocation_customer_id').val();
            return lresult;
            
        };
        
        
    }
    
    var sales_receipt_allocation_component = {
        allocated_amount: {
            reset:function(){
                var lparent_pane = sales_receipt_allocation_parent_pane;
                var lallocated_amount_input = $(lparent_pane).find('#sales_receipt_allocation_allocated_amount')[0];
                $(lallocated_amount_input).off();
                APP_COMPONENT.input.numeric(lallocated_amount_input,{max_val:0,min_val:0});
                $(lallocated_amount_input).val('').blur();
            }
        },
        
    }
    
    var sales_receipt_allocation_methods = {
        hide_all:function(){
            var lparent_pane = sales_receipt_allocation_parent_pane;
            var lc_arr = $(lparent_pane).find('.hide_all').hide();
            $(lparent_pane).find('#sales_receipt_allocation_print').hide();
            $(lparent_pane).find('#sales_receipt_allocation_submit').hide();
        },
        show_hide:function(){
            var lparent_pane = sales_receipt_allocation_parent_pane;
            var lmethod = $(lparent_pane).find('#sales_receipt_allocation_method').val();
            sales_receipt_allocation_methods.hide_all();
            
            switch(lmethod){
                case 'add':                    
                    $(lparent_pane).find('#sales_receipt_allocation_submit').show();
                    $(lparent_pane).find('#sales_receipt_allocation_code').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#sales_receipt_allocation_store').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#sales_receipt_allocation_sales_receipt').closest('.form-group').show();
                    $(lparent_pane).find('#sales_receipt_allocation_reference').closest('.form-group').show();
                    $(lparent_pane).find('#sales_receipt_allocation_sales_receipt_allocation_date').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#sales_receipt_allocation_sales_receipt_allocation_status').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#sales_receipt_allocation_allocated_amount').closest('.form-group').show();
                    break;
                case 'view':
                    $(lparent_pane).find('#sales_receipt_allocation_submit').show();
                    $(lparent_pane).find('#sales_receipt_allocation_code').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#sales_receipt_allocation_store').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#sales_receipt_allocation_sales_receipt').closest('.form-group').show();
                    $(lparent_pane).find('#sales_receipt_allocation_reference').closest('.form-group').show();
                    $(lparent_pane).find('#sales_receipt_allocation_sales_receipt_allocation_date').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#sales_receipt_allocation_sales_receipt_allocation_status').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#sales_receipt_allocation_allocated_amount').closest('.form-group').show();
                    break;
            }
        },
        disable_all:function(){
            var lparent_pane = sales_receipt_allocation_parent_pane;
            APP_COMPONENT.disable_all(lparent_pane);
        },
        enable_disable:function(){
            var lparent_pane = sales_receipt_allocation_parent_pane;
            var lmethod = $(lparent_pane).find('#sales_receipt_allocation_method').val();    
            sales_receipt_allocation_methods.disable_all();
            switch(lmethod){
                case 'add':
                    $(lparent_pane).find('#sales_receipt_payment_type').select2('enable');
                    $(lparent_pane).find('#sales_receipt_allocation_sales_receipt').select2('enable');
                    $(lparent_pane).find('#sales_receipt_allocation_reference').select2('enable');
                    $(lparent_pane).find('#sales_receipt_allocation_store').select2('enable');
                    $(lparent_pane).find('#sales_receipt_allocation_allocated_amount').prop('disabled',false);
                    break;
                case 'view':
                    break;
            }
        },
        reset_all:function(){
            var lparent_pane = sales_receipt_allocation_parent_pane;
            $(lparent_pane).find('#sales_receipt_allocation_reference').select2('data',null).change();
            $(lparent_pane).find('#sales_receipt_allocation_code').val('[AUTO GENERATE]');
            APP_FORM.status.default_status_set('sales_receipt_allocation',
            $(lparent_pane).find('#sales_receipt_allocation_sales_receipt_allocation_status'));
            
            
            var ldefault_store = APP_DATA_TRANSFER.ajaxPOST(APP_PATH.base_url+
                'store/data_support/default_store_get/').response;
            $(lparent_pane).find('#sales_receipt_allocation_store').select2('data',
                {id:ldefault_store.id,text:ldefault_store.name}
            );
    
            $(lparent_pane).find('#sales_receipt_allocation_sales_receipt_allocation_date').datetimepicker({
                value:APP_GENERATOR.CURR_DATETIME(null, null,'F d, Y H:i')
            });
            sales_receipt_allocation_component.allocated_amount.reset();
            APP_COMPONENT.reference_detail.extra_info_set($(lparent_pane).find('#sales_receipt_allocation_reference_detail'),[],{reset:true});
        },
        reference_reset_dependency:function(){
            var lparent_pane = sales_receipt_allocation_parent_pane;
            APP_COMPONENT.reference_detail.extra_info_set($(lparent_pane).find('#sales_receipt_allocation_reference_detail'),[],{reset:true});
            sales_receipt_allocation_component.allocated_amount.reset();
        },
        max_allocated_amount_set:function(){
            var lparent_pane = sales_receipt_allocation_parent_pane;
            var lallocated_amount_input = $(lparent_pane).find('#sales_receipt_allocation_allocated_amount')[0];
            var lsales_receipt_outstanding_amount = $(lparent_pane).find('#sales_receipt_allocation_sales_receipt_detail_outstanding_amount').text().replace(/[^0-9.]/g,'');
            var lref_outstanding_amount = $(lparent_pane).find('#sales_receipt_allocation_reference_detail_outstanding_amount').text().replace(/[^0-9.]/g,'');
            var lmax_val = parseFloat(lsales_receipt_outstanding_amount)>parseFloat(lref_outstanding_amount)?
                lref_outstanding_amount:lsales_receipt_outstanding_amount;
            $(lallocated_amount_input).off();
            APP_COMPONENT.input.numeric(lallocated_amount_input,{min_val:0,max_val:lmax_val});
        },
        submit:function(){
            var lparent_pane = sales_receipt_allocation_parent_pane;
            var lajax_url = sales_receipt_allocation_index_url;
            var lmethod = $(lparent_pane).find('#sales_receipt_allocation_method').val();
            var json_data = {
                ajax_post:true,
                message_session:true,
            };

            switch(lmethod){
                case 'add':
                    json_data.sales_receipt_allocation ={
                        sales_receipt_allocation_type:$(lparent_pane).find('#sales_receipt_allocation_type').val(),
                        allocated_amount:$(lparent_pane).find('#sales_receipt_allocation_allocated_amount').val().replace(/[^0-9.]/g,''),
                        store_id:$(lparent_pane).find('#sales_receipt_allocation_store').select2('val'),
                    };
                    json_data.reference={
                        id:$(lparent_pane).find('#sales_receipt_allocation_reference').select2('val')
                    };
                    json_data.sales_receipt={
                        id:$(lparent_pane).find('#sales_receipt_allocation_sales_receipt').select2('val')
                    };
                    
                    lajax_url +='sales_receipt_allocation_add/';
                    break;
                case 'view':
                    json_data.sales_receipt_allocation = {
                        sales_receipt_allocation_status :$(lparent_pane).find('#sales_receipt_allocation_sales_receipt_allocation_status').select2('val'),
                        cancellation_reason :$(lparent_pane).find('#sales_receipt_allocation_sales_receipt_allocation_cancellation_reason').val()
                    }
                    var sales_receipt_allocation_id = $(lparent_pane).find('#sales_receipt_allocation_id').val();
                    var lajax_method = $(lparent_pane).find('#sales_receipt_allocation_sales_receipt_allocation_status').select2('data').method;
                    lajax_url +=lajax_method+'/'+sales_receipt_allocation_id;
                    break;
            }
            
            result = APP_DATA_TRANSFER.submit(lajax_url,json_data);

            if(result.success ===1){
                $(lparent_pane).find('#sales_receipt_allocation_id').val(result.trans_id);
                if(sales_receipt_allocation_view_url !==''){
                    var url = sales_receipt_allocation_view_url+result.trans_id;
                    window.location.href=url;
                }
                else{
                    sales_receipt_allocation_after_submit();
                }
            }
        }
    };
    
    var sales_receipt_allocation_bind_event = function(){
        var lparent_pane = sales_receipt_allocation_parent_pane;
        
        APP_COMPONENT.input.numeric($(lparent_pane).find('#sales_receipt_allocation_allocated_amount'),{min_val:0,max_val:0});
        $(lparent_pane).find('#sales_receipt_allocation_allocated_amount').blur();
        
        $(lparent_pane).find('#sales_receipt_allocation_sales_receipt').on('change',function(){
            sales_receipt_allocation_methods.max_allocated_amount_set();
        });
        
        $(lparent_pane).find('#sales_receipt_allocation_reference').on('change',function(){
            var lparent_pane = sales_receipt_allocation_parent_pane;
            var lajax_url = sales_receipt_allocation_data_support_url;
            var lreference_data = $(this).select2('data');
            sales_receipt_allocation_methods.reference_reset_dependency();
            
            if(lreference_data !== null){
                var ljson_data = {reference_id :lreference_data.id, reference_type: lreference_data.reference_type}
                $(lparent_pane).find('#sales_receipt_allocation_type').val(lreference_data.reference_type);

                var lresponse = APP_DATA_TRANSFER.ajaxPOST(lajax_url+'input_select_reference_detail_get/',ljson_data).response;
                
                var lreference_detail = lresponse.reference_detail;
                APP_COMPONENT.reference_detail.extra_info_set($(lparent_pane).find('#sales_receipt_allocation_reference_detail'),lresponse.reference_detail);
                
                sales_receipt_allocation_methods.max_allocated_amount_set();
            }                
        });
        
        $(lparent_pane).find('#sales_receipt_allocation_submit').off();        
        $(lparent_pane).find('#sales_receipt_allocation_submit')
        .on('click',function(e){
            e.preventDefault();
            var btn = $(this);
            btn.addClass('disabled');
            var lparent_pane = sales_receipt_allocation_parent_pane;
            modal_confirmation_submit_parent = $(lparent_pane).attr('class').indexOf('modal-body')!==-1?
                $(lparent_pane).closest('.modal'):null;
            $('#modal_confirmation_submit').modal('show');
            $('#modal_confirmation_submit_btn_submit').on('click',function(){
                sales_receipt_allocation_methods.submit();
                $('#modal_confirmation_submit').modal('hide');

            });
            
            $(sales_receipt_allocation_window_scroll).scrollTop(0);        
            setTimeout(function(){btn.removeClass('disabled')},1000);            
        });
            
        
    }
    
    var sales_receipt_allocation_components_prepare = function(){
        

        var sales_receipt_allocation_data_set = function(){
            var lparent_pane = sales_receipt_allocation_parent_pane;
            var lmethod = $(lparent_pane).find('#sales_receipt_allocation_method').val();
            
            switch(lmethod){
                case 'add':
                    sales_receipt_allocation_methods.reset_all();
                    if(sales_receipt_allocation_insert_dummy){
                        
                    }
                    break;
                case 'view':
                    
                    var lsales_receipt_allocation_id = $(lparent_pane).find('#sales_receipt_allocation_id').val();
                    var lajax_url = sales_receipt_allocation_data_support_url+'sales_receipt_allocation_get/';
                    var json_data = {data:lsales_receipt_allocation_id};
                    var lresponse = APP_DATA_TRANSFER.ajaxPOST(lajax_url,json_data).response;
                    var lcomp_prefix = '#sales_receipt_allocation';
                    var lsra = lresponse.sra;
                    var lreference = lresponse.reference;
                    var lsales_receipt = lresponse.sales_receipt;
                    
                    
                    $(lparent_pane).find(lcomp_prefix+'_sales_receipt').select2('data',{
                        id:lsales_receipt.id, text:lsales_receipt.code
                        });
                    $(lparent_pane).find(lcomp_prefix+'_sales_receipt_detail_amount').text(lsales_receipt.amount);
                    $(lparent_pane).find(lcomp_prefix+'_sales_receipt_detail_outstanding_amount').text(lsales_receipt.outstanding_amount);
                    $(lparent_pane).find(lcomp_prefix+'_sales_receipt_detail_sales_receipt_date').text(lsales_receipt.sales_receipt_date);

                    $(lparent_pane).find(lcomp_prefix+'_reference').select2('data',{
                        id:lreference.id, text:lreference.text
                        });
                        
                    APP_COMPONENT.reference_detail.extra_info_set($(lparent_pane).find(lcomp_prefix+'_reference_detail'),lresponse.reference_detail);

                    $(lparent_pane).find(lcomp_prefix+'_store').select2('data',{id:lsra.store_id
                        ,text:lsra.store_text});
                    $(lparent_pane).find(lcomp_prefix+'_code').val(lsra.code);
                    $(lparent_pane).find(lcomp_prefix+'_allocated_amount').val(lsra.allocated_amount);
                    $(lparent_pane).find(lcomp_prefix+'_sales_receipt_allocation_cancellation_reason').val(lsra.cancellation_reason);

                    $(lparent_pane).find(lcomp_prefix+'_sales_receipt_allocation_status')
                            .select2('data',{id:lsra.sra_status
                                ,text:lsra.sra_status_text}).change();
                    
                    $(lparent_pane).find(lcomp_prefix+'_sales_receipt_allocation_status')
                            .select2({data:lresponse.sra_status_list});
                    
                    break;
            }
        }
        
        
        sales_receipt_allocation_methods.enable_disable();
        sales_receipt_allocation_methods.show_hide();
        sales_receipt_allocation_data_set();
    }
    
    var sales_receipt_allocation_after_submit = function(){
        //function that will be executed after submit 
    }
    
   
    
    
    
    
    
</script>