<script>

    var purchase_receipt_parent_pane = $('<?php echo $detail_tab; ?>')[0];
    var purchase_receipt_ajax_url = null;
    var purchase_receipt_index_url = null;
    var purchase_receipt_view_url = null;
    var purchase_receipt_window_scroll = null;
    var purchase_receipt_data_support_url = null;
    var purchase_receipt_common_ajax_listener = null;
    var purchase_receipt_component_prefix_id = '';
    
    var purchase_receipt_insert_dummy = false;

    var purchase_receipt_init = function(){
        var parent_pane = purchase_receipt_parent_pane;
        purchase_receipt_ajax_url = '<?php echo $ajax_url ?>';
        purchase_receipt_index_url = '<?php echo $index_url ?>';
        purchase_receipt_view_url = '<?php echo $view_url ?>';
        purchase_receipt_window_scroll = '<?php echo $window_scroll; ?>';
        purchase_receipt_data_support_url = '<?php echo $data_support_url; ?>';
        purchase_receipt_common_ajax_listener = '<?php echo $common_ajax_listener; ?>';
        purchase_receipt_component_prefix_id = '#<?php echo $component_prefix_id; ?>';
        
        purchase_receipt_rma_extra_param_get = function(){
            //input select detail use this function to get extra parameter for further query
            
        };
        
        
    }
    
    var purchase_receipt_methods = {
        hide_all:function(){
            var lparent_pane = purchase_receipt_parent_pane;
            var lc_arr = $(lparent_pane).find('.hide_all').hide();
            $(lparent_pane).find('#purchase_receipt_print').hide();
            $(lparent_pane).find('#purchase_receipt_submit').hide();
        },
        show_hide:function(){
            var lparent_pane = purchase_receipt_parent_pane;
            var lmethod = $(lparent_pane).find('#purchase_receipt_method').val();
            purchase_receipt_methods.hide_all();
            
            switch(lmethod){
                case 'add':                    
                    
                case 'view':
                    $(lparent_pane).find('#purchase_receipt_submit').show();
                    $(lparent_pane).find('#purchase_receipt_payment_type').closest('.form-group').show();
                    $(lparent_pane).find('#purchase_receipt_code').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#purchase_receipt_store').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#purchase_receipt_supplier').closest('.form-group').show();
                    $(lparent_pane).find('#purchase_receipt_purchase_receipt_date').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#purchase_receipt_purchase_receipt_status').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#purchase_receipt_supplier_bank_acc').closest('.form-group').show();
                    $(lparent_pane).find('#purchase_receipt_bos_bank_name').closest('.form-group').show();
                    $(lparent_pane).find('#purchase_receipt_bos_bank_acc').closest('.form-group').show();
                    $(lparent_pane).find('#purchase_receipt_amount').closest('.form-group').show();
                    $(lparent_pane).find('#purchase_receipt_amount').closest('.form-group').show();
                    $(lparent_pane).find('#purchase_receipt_outstanding_amount').closest('.form-group').show();
                    $(lparent_pane).find('#purchase_receipt_change_amount').closest('.form-group').show();
                    $(lparent_pane).find('#purchase_receipt_notes').closest('.form-group').show();
                    break;
            }
        },
        disable_all:function(){
            var lparent_pane = purchase_receipt_parent_pane;
            APP_COMPONENT.disable_all(lparent_pane);
        },
        enable_disable:function(){
            var lparent_pane = purchase_receipt_parent_pane;
            var lmethod = $(lparent_pane).find('#purchase_receipt_method').val();    
            purchase_receipt_methods.disable_all();
            switch(lmethod){
                case 'add':
                    $(lparent_pane).find('#purchase_receipt_payment_type').select2('enable');
                    $(lparent_pane).find('#purchase_receipt_store').select2('enable');
                    $(lparent_pane).find('#purchase_receipt_supplier').select2('enable');
                    $(lparent_pane).find('#purchase_receipt_amount').prop('disabled',false);
                    $(lparent_pane).find('#purchase_receipt_notes').prop('disabled',false);
                    break;
                case 'view':
                    $(lparent_pane).find('#purchase_receipt_notes').prop('disabled',false);
                    break;
            }
        },
        reset_all:function(){
            var lparent_pane = purchase_receipt_parent_pane;
            var lprefix_id = purchase_receipt_component_prefix_id;
            $(lparent_pane).find(lprefix_id+'_code').val('[AUTO GENERATE]');
            
            $(lparent_pane).find(lprefix_id+'_payment_type').select2({data:[]});
            
            var ljson_data = {}
            var lresponse = APP_DATA_TRANSFER.ajaxPOST(purchase_receipt_data_support_url+'input_select_payment_type_get/',ljson_data).response;
            $(lparent_pane).find(lprefix_id+'_payment_type').select2({data:lresponse});
            $(lparent_pane).find(lprefix_id+'_payment_type').select2('data',lresponse[0]);
            
            
            var ldefault_status = null;
            ldefault_status = APP_DATA_TRANSFER.common_ajax_listener('module_status/default_status_get',{module:'purchase_receipt'}).response;

            $(lparent_pane).find(lprefix_id+'_purchase_receipt_status')
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
    
            $(lparent_pane).find(lprefix_id+'_purchase_receipt_date').datetimepicker({
                value:APP_GENERATOR.CURR_DATETIME(null, null,'F d, Y H:i')
            });
            
            $(lparent_pane).find(lprefix_id+'_outstanding_amount').blur();
            $(lparent_pane).find(lprefix_id+'_change_amount').blur();
            
        },
        submit:function(){
            var lparent_pane = purchase_receipt_parent_pane;
            var lprefix_id = purchase_receipt_component_prefix_id;
            var lajax_url = purchase_receipt_index_url;
            var lmethod = $(lparent_pane).find(lprefix_id+'_method').val();
            var json_data = {
                ajax_post:true,
                message_session:true,
            };

            switch(lmethod){
                case 'add':
                    json_data.purchase_receipt = {
                        store_id:$(lparent_pane).find(lprefix_id+'_store').select2('val'),
                        supplier_id:$(lparent_pane).find(lprefix_id+'_supplier').select2('val'),
                        payment_type_id: $(lparent_pane).find(lprefix_id+'_payment_type').select2('val'),
                        supplier_bank_acc:$(lparent_pane).find(lprefix_id+'_supplier_bank_acc').val(),
                        bos_bank_name:$(lparent_pane).find(lprefix_id+'_bos_bank_name').val(),
                        bos_bank_acc:$(lparent_pane).find(lprefix_id+'_bos_bank_acc').val(),
                        amount:$(lparent_pane).find(lprefix_id+'_amount').val().replace(/[^0-9.]/g,''),
                        change_amount:$(lparent_pane).find(lprefix_id+'_change_amount').val().replace(/[^0-9.]/g,''),
                        notes:$(lparent_pane).find(lprefix_id+'_notes').val(),
                    }
                    lajax_url +='purchase_receipt_add/';
                    break;
                case 'view':
                    json_data.purchase_receipt = {
                        notes: $(lparent_pane).find(lprefix_id+'_notes').val(),
                        purchase_receipt_status: $(lparent_pane).find(lprefix_id+'_purchase_receipt_status').select2('val'),
                        cancellation_reason: $(lparent_pane).find(lprefix_id+'_purchase_receipt_cancellation_reason').val()
                    }
                    
                    var purchase_receipt_id = $(lparent_pane).find(lprefix_id+'_id').val();
                    var lajax_method = $(lparent_pane).find(lprefix_id+'_purchase_receipt_status').
                        select2('data').method;
                    lajax_url +=lajax_method+'/'+purchase_receipt_id;
                    break;
            }
            
            result = APP_DATA_TRANSFER.submit(lajax_url,json_data);

            if(result.success ===1){
                $(lparent_pane).find(lprefix_id+'_id').val(result.trans_id);
                if(purchase_receipt_view_url !==''){
                    var url = purchase_receipt_view_url+result.trans_id;
                    window.location.href=url;
                }
                else{
                    purchase_receipt_after_submit();
                }
            }
        }
    };
    
    var purchase_receipt_bind_event = function(){
        var lparent_pane = purchase_receipt_parent_pane;
        var lprefix_id = purchase_receipt_component_prefix_id;
        
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
                
        $(lparent_pane).find(lprefix_id+'_payment_type').on('change',function(){
            $(lparent_pane).find(lprefix_id+'_change_amount').prop('disabled',true);
            $(lparent_pane).find('#purchase_receipt_supplier_bank_acc').prop('disabled',true);
            $(lparent_pane).find('#purchase_receipt_bos_bank_name').prop('disabled',true);
            $(lparent_pane).find('#purchase_receipt_bos_bank_acc').prop('disabled',true);
                    
            $(lparent_pane).find(lprefix_id+'_change_amount').val('').blur();
            if($(this).select2('data').code==='CASH'){
                $(lparent_pane).find(lprefix_id+'_change_amount').prop('disabled',false);
            }
            else{
                $(lparent_pane).find('#purchase_receipt_supplier_bank_acc').prop('disabled',false);
                $(lparent_pane).find('#purchase_receipt_bos_bank_name').prop('disabled',false);
                $(lparent_pane).find('#purchase_receipt_bos_bank_acc').prop('disabled',false);

            }
        });
        
        
        $(lparent_pane).find('#purchase_receipt_submit').off();        
        $(lparent_pane).find('#purchase_receipt_submit')
        .on('click',function(e){
            e.preventDefault();
            var btn = $(this);
            btn.addClass('disabled');
            var lparent_pane = purchase_receipt_parent_pane;
            modal_confirmation_submit_parent = $(lparent_pane).attr('class').indexOf('modal-body')!==-1?
                $(lparent_pane).closest('.modal'):null;
            $('#modal_confirmation_submit').modal('show');
            $('#modal_confirmation_submit_btn_submit').on('click',function(){
                purchase_receipt_methods.submit();
                $('#modal_confirmation_submit').modal('hide');

            });
            
            $(purchase_receipt_window_scroll).scrollTop(0);        
            setTimeout(function(){btn.removeClass('disabled')},1000);            
        });
            
        
    }
    
    var purchase_receipt_components_prepare = function(){
        

        var purchase_receipt_data_set = function(){
            var lparent_pane = purchase_receipt_parent_pane;
            var lprefix_id = purchase_receipt_component_prefix_id;
            var lmethod = $(lparent_pane).find('#purchase_receipt_method').val();
            
            switch(lmethod){
                case 'add':
                    purchase_receipt_methods.reset_all();
                    if(purchase_receipt_insert_dummy){
                        <?php /*
                        $(lparent_pane).find(lprefix_id+'_supplier').select2('data',{id: "32", text: "<strong >SUP1</strong> Supplier 1 08111111111 "}).change();
                        $(lparent_pane).find(lprefix_id+'_type').select2('data',{id: "1", text: "<strong >CASH</strong>",code:'CASH'}).change();
                        $(lparent_pane).find(lprefix_id+'_amount').val('1000000').change().blur();
                        $(lparent_pane).find(lprefix_id+'_change_amount').val('20000').blur();
                        
                        */ ?>
                    }
                    break;
                case 'view':
                    
                    var lpurchase_receipt_id = $(lparent_pane).find('#purchase_receipt_id').val();
                    var lajax_url = purchase_receipt_data_support_url+'purchase_receipt_get/';
                    var json_data = {data:lpurchase_receipt_id};
                    var lresponse = APP_DATA_TRANSFER.ajaxPOST(lajax_url,json_data).response;
                    var lpurchase_receipt = lresponse.purchase_receipt;
                    var lreference = lresponse.reference;
                    var lreference_detail = lresponse.reference_detail;
                    
                    
                    $(lparent_pane).find('#purchase_receipt_store').select2('data',{id:lpurchase_receipt.store_id
                        ,text:lpurchase_receipt.store_text});
                    $(lparent_pane).find('#purchase_receipt_code').val(lpurchase_receipt.code);
                    $(lparent_pane).find('#purchase_receipt_supplier_bank_acc').val(lpurchase_receipt.supplier_bank_acc);
                    $(lparent_pane).find('#purchase_receipt_bos_bank_name').val(lpurchase_receipt.bos_bank_name);
                    $(lparent_pane).find('#purchase_receipt_bos_bank_acc').val(lpurchase_receipt.bos_bank_acc);
                    $(lparent_pane).find('#purchase_receipt_amount').val(lpurchase_receipt.amount);
                    $(lparent_pane).find('#purchase_receipt_outstanding_amount').val(lpurchase_receipt.outstanding_amount);
                    $(lparent_pane).find('#purchase_receipt_change_amount').val(lpurchase_receipt.change_amount);
                    $(lparent_pane).find('#purchase_receipt_purchase_receipt_date').datetimepicker({value:lpurchase_receipt.purchase_receipt_date});
                    $(lparent_pane).find('#purchase_receipt_purchase_receipt_cancellation_reason').val(lpurchase_receipt.cancellation_reason);

                    $(lparent_pane).find('#purchase_receipt_payment_type')
                            .select2('data',{id:lpurchase_receipt.payment_type_id
                                ,text:lpurchase_receipt.payment_type_text});
                    
                    $(lparent_pane).find('#purchase_receipt_purchase_receipt_status')
                            .select2('data',{id:lpurchase_receipt.purchase_receipt_status
                                ,text:lpurchase_receipt.purchase_receipt_status_text}).change();
                    
                    $(lparent_pane).find('#purchase_receipt_supplier')
                            .select2('data',{id:lpurchase_receipt.supplier_id
                                ,text:lpurchase_receipt.supplier_text}).change();
                    
                    $(lparent_pane).find('#purchase_receipt_purchase_receipt_status')
                            .select2({data:lresponse.purchase_receipt_status_list});
                    
                    $(lparent_pane).find('#purchase_receipt_notes').val(lpurchase_receipt.notes);
                    
                    break;
            }
        }
        
        
        purchase_receipt_methods.enable_disable();
        purchase_receipt_methods.show_hide();
        purchase_receipt_data_set();
    }
    
    var purchase_receipt_after_submit = function(){
        //function that will be executed after submit 
    }
    
   
    
    
    
    
    
</script>