<script>
    var refill_invoice_parent_pane = $('<?php echo $detail_tab; ?>')[0];
    var refill_invoice_ajax_url = null;
    var refill_invoice_index_url = null;
    var refill_invoice_view_url = null;
    var refill_invoice_window_scroll = null;
    var refill_invoice_data_support_url = null;
    var refill_invoice_common_ajax_listener = null;
    var refill_invoice_component_prefix_id = '';
    
    var refill_invoice_init = function(){
        var parent_pane = refill_invoice_parent_pane;

        refill_invoice_ajax_url = '<?php echo $ajax_url ?>';
        refill_invoice_index_url = '<?php echo $index_url ?>';
        refill_invoice_view_url = '<?php echo $view_url ?>';
        refill_invoice_window_scroll = '<?php echo $window_scroll; ?>';
        refill_invoice_data_support_url = '<?php echo $data_support_url; ?>';
        refill_invoice_common_ajax_listener = '<?php echo $common_ajax_listener; ?>';
        refill_invoice_component_prefix_id = '#<?php echo $component_prefix_id; ?>';
        
    }

    var refill_invoice_after_submit = function(){

    }
    
    var refill_invoice_data ={
        current_status:'',
        sir_exists:false,
    }
    
    var refill_invoice_methods = {
        
        hide_all:function(){
            var lparent_pane = refill_invoice_parent_pane;
            $(lparent_pane).find('.hide_all').hide();
        },
        disable_all:function(){
            var lparent_pane = refill_invoice_parent_pane;
            var lcomponents = $(lparent_pane).find('.disable_all');
            APP_COMPONENT.disable_all(lparent_pane);
        },
        
        show_hide: function(){
            var lparent_pane = refill_invoice_parent_pane;
            var lprefix_id = refill_invoice_component_prefix_id;
            var lmethod = $(lparent_pane).find(lprefix_id+'_method').val();            
            var lrefill_invoice_type = refill_invoice_methods.module_type_get();
            var lstatus = $(lparent_pane).find(lprefix_id+'_refill_invoice_status').select2('val');
            refill_invoice_methods.hide_all();
            
            switch(lmethod){
                case 'add':
                    $(lparent_pane).find(lprefix_id+'_store').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find(lprefix_id+'_code').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find(lprefix_id+'_customer').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find(lprefix_id+'_refill_invoice_date').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find(lprefix_id+'_refill_invoice_status').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find(lprefix_id+'_notes').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find(lprefix_id+'_product_table').closest('.form-group').show();
                    $(lparent_pane).find(lprefix_id+'_grand_total_amount').closest('div [class*="form-group"]').show();
                    
                    break;
                case 'view':
                    $(lparent_pane).find(lprefix_id+'_code').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find(lprefix_id+'_customer').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find(lprefix_id+'_refill_invoice_date').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find(lprefix_id+'_refill_invoice_status').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find(lprefix_id+'_notes').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find(lprefix_id+'_product_table').closest('.form-group').show();
                    $(lparent_pane).find(lprefix_id+'_grand_total_amount').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find(lprefix_id+'_outstanding_amount').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find(lprefix_id+'_btn_print').show();
                    break;
                    
            }
            
            
            if(lmethod === 'view'){
                
            }
            
        },        
        enable_disable: function(){
            var lparent_pane = refill_invoice_parent_pane;
            var lprefix_id = refill_invoice_component_prefix_id;
            var lmethod = $(lparent_pane).find(lprefix_id+'_method').val();  
            refill_invoice_methods.disable_all();
            
            switch(lmethod){
                case "add":
                    $(lparent_pane).find(lprefix_id+'_store').select2('enable');
                    $(lparent_pane).find(lprefix_id+"_notes").prop("disabled",false);
                    $(lparent_pane).find(lprefix_id+'_reference').select2('enable');
                    
                    break;
                case 'view':
                    $(lparent_pane).find(lprefix_id+"_notes").prop("disabled",false);
                    break;
            }
        },
        reset_all:function(){
            var lparent_pane = refill_invoice_parent_pane;
            var lprefix_id = refill_invoice_component_prefix_id;
            
            var ldefault_store = APP_DATA_TRANSFER.ajaxPOST(APP_PATH.base_url+
                'store/data_support/default_store_get/').response;
            $(lparent_pane).find(lprefix_id+'_store').select2('data',
                {id:ldefault_store.id,text:ldefault_store.name}
            );
    
            $(lparent_pane).find(lprefix_id+'_code').val('[AUTO GENERATE]');
            
            APP_FORM.status.default_status_set(
                'refill_invoice',
                $(lparent_pane).find(lprefix_id+'_refill_invoice_status')
            );
            
             $(lparent_pane).find(lprefix_id+'_refill_invoice_date').datetimepicker({
                value:APP_GENERATOR.CURR_DATETIME('','','F d, Y H:i'),
            });            
            
            $(lparent_pane).find(lprefix_id+'_type').val('');                        
            $(lparent_pane).find(lprefix_id+'_reference_detail .extra_info').remove();
            $(lparent_pane).find(lprefix_id+'_grand_total_amount').val(APP_CONVERTER.thousand_separator('0'))
            $(lparent_pane).find(lprefix_id+'_customer').select2('data',{});
            
            refill_invoice_product_table_method.reset();
            refill_invoice_product_table_method.head_generate();
        },
        submit:function(){
            var lparent_pane = refill_invoice_parent_pane;
            var lprefix_id = refill_invoice_component_prefix_id;
            var ajax_url = refill_invoice_index_url;
            var lmethod = $(lparent_pane).find("#refill_invoice_method").val();
            var refill_invoice_id = $(lparent_pane).find("#refill_invoice_id").val();        
            var lmodule_type = refill_invoice_methods.module_type_get();
            var json_data = {
                ajax_post:true,
                refill_invoice:{},
                message_session:true
            };

            switch(lmethod){
                case 'add':
                    json_data.refill_invoice.store_id = $(lparent_pane).find(lprefix_id+"_store").select2('val');
                    json_data.refill_invoice.refill_invoice_type = lmodule_type;
                    json_data.refill_invoice.reference_id = $(lparent_pane).find(lprefix_id+'_reference').select2('val');
                    json_data.refill_invoice.notes = $(lparent_pane).find(lprefix_id+"_notes").val();
                    break;
                case 'view':
                    json_data.refill_invoice.refill_invoice_status = $(lparent_pane).find(lprefix_id+'_refill_invoice_status').select2('val');
                    json_data.refill_invoice.cancellation_reason = $(lparent_pane).find(lprefix_id+"_refill_invoice_cancellation_reason").val();
                    json_data.refill_invoice.notes = $(lparent_pane).find(lprefix_id+"_notes").val();
                    break;
            }
            
            var lajax_method='';
            switch(lmethod){
                case 'add':
                    lajax_method = 'refill_invoice_add';
                    break;
                case 'view':
                    lajax_method = $(lparent_pane).find(lprefix_id+'_refill_invoice_status').select2('data').method;
                    break;
            }
            ajax_url +=lajax_method+'/'+refill_invoice_id;
            
            result = APP_DATA_TRANSFER.submit(ajax_url,json_data);
            if(result.success ===1){
                $(refill_invoice_parent_pane).find('#refill_invoice_id').val(result.trans_id);
                if(refill_invoice_view_url !==''){
                    var url = refill_invoice_view_url+result.trans_id;
                    window.location.href=url;
                }
                else{
                    refill_invoice_after_submit();
                }
            }
        },
        module_type_get:function(){
            var lparent_pane = refill_invoice_parent_pane;
            var lprefix_id = refill_invoice_component_prefix_id;
            return $(lparent_pane).find(lprefix_id+'_type').val();
        },
        reference_type_get:function(){
            var lparent_pane = refill_invoice_parent_pane;
            var lprefix_id = refill_invoice_component_prefix_id;
            var lresult = '';
            var lval = $(lparent_pane).find(lprefix_id+'_reference').select2('val');
            if(lval !== ''){
                var ldata = $(lparent_pane).find(lprefix_id+'_reference').select2('data');
                lresult = typeof ldata.reference_type !== 'undefined'? ldata.reference_type : '';
            }
            return lresult;
        }
        
    }

    var refill_invoice_bind_event = function(){
        var lparent_pane = refill_invoice_parent_pane;
        var lprefix_id = refill_invoice_component_prefix_id;
        
        $(lparent_pane).find(lprefix_id+'_submit').off('click');
        APP_COMPONENT.button.submit.set($(lparent_pane).find(lprefix_id+'_submit'),{
            parent_pane:lparent_pane,
            module_method:refill_invoice_methods
        });
        
        $(lparent_pane).find(lprefix_id+'_reference').on('change',function(){
            var lparent_pane = refill_invoice_parent_pane;
            var lprefix_id = refill_invoice_component_prefix_id;
            var lmethod = $(lparent_pane).find(lprefix_id+'_method').val();
            
            var lref_id = $(this).select2('val');
            $(lparent_pane).find(lprefix_id+'_type').val('');                        
            $(lparent_pane).find(lprefix_id+'_reference_detail .extra_info').remove();
            $(lparent_pane).find(lprefix_id+'_grand_total_amount').val(APP_CONVERTER.thousand_separator('0'))
            $(lparent_pane).find(lprefix_id+'_customer').select2('data',{});
            refill_invoice_product_table_method.reset();
            refill_invoice_product_table_method.head_generate();
            
            if(lref_id !== '' && lmethod === 'add'){
                var ldata = $(this).select2('data');
                $(lparent_pane).find(lprefix_id+'_type').val(ldata.reference_type);
                var lajax_url = refill_invoice_data_support_url+'reference_dependency_get/';
                var lresponse = APP_DATA_TRANSFER.ajaxPOST(lajax_url, {reference_type:ldata.reference_type,reference_id:ldata.id}).response;
                APP_COMPONENT.reference_detail.extra_info_set($(lparent_pane).find(lprefix_id+'_reference_detail'),lresponse.reference_detail,{reset:true});
                $(lparent_pane).find(lprefix_id+'_customer').select2('data',{id:lresponse.reference.customer_id,text:lresponse.reference.customer_text});                
                refill_invoice_product_methods.load_product(lresponse.reference_product);                
                var lgrand_total_amount = APP_CONVERTER._float('0');
                $.each(lresponse.reference_product,function(lidx, lrow){
                    lgrand_total_amount+=APP_CONVERTER._float(lrow.amount);
                });
                $(lparent_pane).find(lprefix_id+'_grand_total_amount').val(APP_CONVERTER.thousand_separator(lgrand_total_amount))
                
            }
            
            refill_invoice_methods.show_hide();
            
        });
        
        $(lparent_pane).find(lprefix_id+'_btn_print').off();
        $(lparent_pane).find(lprefix_id+'_btn_print').on('click',function(){
            var lpos_id = $(lparent_pane).find(lprefix_id+'_id').val();
            modal_print.init();
            modal_print.menu.add('Invoice',refill_invoice_index_url+'refill_invoice_print/'+lpos_id+'/invoice');
            modal_print.menu.add('Payment',refill_invoice_index_url+'refill_invoice_print/'+lpos_id+'/payment');
            modal_print.show();
            
        });
        
        refill_invoice_product_bind_event();
        
    }
    
    var refill_invoice_components_prepare= function(){
        
        var method = $(refill_invoice_parent_pane).find("#refill_invoice_method").val();
        
        
        var refill_invoice_data_set = function(){
            var lparent_pane = refill_invoice_parent_pane;
            var lprefix_id = refill_invoice_component_prefix_id;
            switch(method){
                case "add":
                    refill_invoice_methods.reset_all();
                    break;
                case "view":
                    
                    var refill_invoice_id = $(refill_invoice_parent_pane).find(lprefix_id+"_id").val();
                    var json_data={data:refill_invoice_id};
                    var lresponse = APP_DATA_TRANSFER.ajaxPOST(refill_invoice_data_support_url+"refill_invoice_get",json_data).response;
                    if(lresponse != []){
                        
                        var lreference = lresponse.reference;
                        var lrefill_invoice = lresponse.refill_invoice;
                        var lri_product = lresponse.ri_product;
                        
                        $(lparent_pane).find(lprefix_id+'_store').select2('data',{id:lrefill_invoice.store_id, text:lrefill_invoice.store_text});
                        $(lparent_pane).find(lprefix_id+'_reference').select2('data',lreference).change();
                        APP_COMPONENT.reference_detail.extra_info_set($(lparent_pane).find(lprefix_id+'_reference_detail'),lresponse.reference_detail,{reset:true});
                        $(lparent_pane).find(lprefix_id+'_customer').select2('data',{id:lrefill_invoice.customer_id,text:lrefill_invoice.customer_text})
                        $(lparent_pane).find(lprefix_id+'_code').val(lrefill_invoice.code);                        
                        $(lparent_pane).find(lprefix_id+'_refill_invoice_date').val(new Date(lrefill_invoice.refill_invoice_date).format('F d, Y H:i'));  
                        $(lparent_pane).find(lprefix_id+'_grand_total_amount').val(APP_CONVERTER.thousand_separator(lrefill_invoice.grand_total_amount));
                        $(lparent_pane).find(lprefix_id+'_outstanding_amount').val(APP_CONVERTER.thousand_separator(lrefill_invoice.outstanding_amount));
                        refill_invoice_data.current_status = lrefill_invoice.refill_invoice_status;
                        
                        refill_invoice_product_table_method.reset();
                        refill_invoice_product_table_method.head_generate();
                        $.each(lri_product, function(li, lrow){
                            refill_invoice_product_table_method.input_row_generate(lrow);
                        });
                        
                        $(lparent_pane).find(lprefix_id+'_refill_invoice_status')
                            .select2('data',{id:lrefill_invoice.refill_invoice_status
                                ,text:lrefill_invoice.refill_invoice_status_text}).change();
                            
                        $(lparent_pane).find(lprefix_id+'_refill_invoice_status')
                            .select2({data:lresponse.refill_invoice_status_list});
                            
                        $(lparent_pane).find(lprefix_id+'_refill_invoice_cancellation_reason').val(lrefill_invoice.cancellation_reason);
                        
                        
                    };
                    break;            
            }
        }
    
        refill_invoice_methods.enable_disable();
        refill_invoice_methods.show_hide();
        refill_invoice_data_set();
    }
    
</script>