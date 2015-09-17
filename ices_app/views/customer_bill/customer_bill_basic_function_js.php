<script>

    var customer_bill_parent_pane = $('<?php echo $detail_tab; ?>')[0];
    var customer_bill_ajax_url = null;
    var customer_bill_index_url = null;
    var customer_bill_view_url = null;
    var customer_bill_window_scroll = null;
    var customer_bill_data_support_url = null;
    var customer_bill_common_ajax_listener = null;
    
    var customer_bill_insert_dummy = true;

    var customer_bill_init = function(){
        var parent_pane = customer_bill_parent_pane;
        customer_bill_ajax_url = '<?php echo $ajax_url ?>';
        customer_bill_index_url = '<?php echo $index_url ?>';
        customer_bill_view_url = '<?php echo $view_url ?>';
        customer_bill_window_scroll = '<?php echo $window_scroll; ?>';
        customer_bill_data_support_url = '<?php echo $data_support_url; ?>';
        customer_bill_common_ajax_listener = '<?php echo $common_ajax_listener; ?>';
        
        customer_bill_rma_extra_param_get = function(){
            //input select detail use this function to get extra parameter for further query
            
        };
        
        
    }
    
    var customer_bill_methods = {
        hide_all:function(){
            var lparent_pane = customer_bill_parent_pane;
            var lc_arr = $(lparent_pane).find('.hide_all').hide();
            $(lparent_pane).find('#customer_bill_print').hide();
            $(lparent_pane).find('#customer_bill_submit').hide();
        },
        show_hide:function(){
            var lparent_pane = customer_bill_parent_pane;
            var lmethod = $(lparent_pane).find('#customer_bill_method').val();
            customer_bill_methods.hide_all();
            
            switch(lmethod){
                case 'add':                    
                    
                    break;
                case 'view':
                    $(lparent_pane).find('#customer_bill_submit').show();
                    $(lparent_pane).find('#customer_bill_reference').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#customer_bill_code').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#customer_bill_store').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#customer_bill_customer').closest('.form-group').show();
                    $(lparent_pane).find('#customer_bill_customer_bill_date').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#customer_bill_customer_bill_status').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#customer_bill_amount').closest('.form-group').show();
                    $(lparent_pane).find('#customer_bill_outstanding_amount').closest('.form-group').show();
                    break;
            }
        },
        disable_all:function(){
            var lparent_pane = customer_bill_parent_pane;
            APP_COMPONENT.disable_all(lparent_pane);
        },
        enable_disable:function(){
            var lparent_pane = customer_bill_parent_pane;
            var lmethod = $(lparent_pane).find('#customer_bill_method').val();    
            customer_bill_methods.disable_all();
            switch(lmethod){
                case 'add':
                    break;
                case 'view':
                    break;
            }
        },
        reset_all:function(){
            var lparent_pane = customer_bill_parent_pane;
            
        },
        submit:function(){
            var lparent_pane = customer_bill_parent_pane;
            var lajax_url = customer_bill_index_url;
            var lmethod = $(lparent_pane).find('#customer_bill_method').val();
            var json_data = {
                ajax_post:true,
                message_session:true,
            };

            switch(lmethod){
                case 'add':

                    lajax_url +='customer_bill_add/';
                    break;
                case 'view':
                    var customer_bill_id = $(lparent_pane).find('#customer_bill_id').val();
                    var lajax_method = $(lparent_pane).find('#customer_bill_customer_bill_status').select2('data').method;
                    lajax_url +=lajax_method+'/'+customer_bill_id;
                    break;
            }
            
            result = APP_DATA_TRANSFER.submit(lajax_url,json_data);

            if(result.success ===1){
                $(lparent_pane).find('#customer_bill_id').val(result.trans_id);
                if(customer_bill_view_url !==''){
                    var url = customer_bill_view_url+result.trans_id;
                    window.location.href=url;
                }
                else{
                    customer_bill_after_submit();
                }
            }
        }
    };
    
    var customer_bill_bind_event = function(){
        var lparent_pane = customer_bill_parent_pane;
        
        $(lparent_pane).find('#customer_bill_submit').off();        
        $(lparent_pane).find('#customer_bill_submit')
        .on('click',function(e){
            e.preventDefault();
            var btn = $(this);
            btn.addClass('disabled');
            var lparent_pane = customer_bill_parent_pane;
            modal_confirmation_submit_parent = $(lparent_pane).attr('class').indexOf('modal-body')!==-1?
                $(lparent_pane).closest('.modal'):null;
            $('#modal_confirmation_submit').modal('show');
            $('#modal_confirmation_submit_btn_submit').on('click',function(){
                customer_bill_methods.submit();
                $('#modal_confirmation_submit').modal('hide');

            });
            
            $(customer_bill_window_scroll).scrollTop(0);        
            setTimeout(function(){btn.removeClass('disabled')},1000);            
        });
            
        
    }
    
    var customer_bill_components_prepare = function(){
        

        var customer_bill_data_set = function(){
            var lparent_pane = customer_bill_parent_pane;
            var lmethod = $(lparent_pane).find('#customer_bill_method').val();
            
            switch(lmethod){
                case 'add':
                    customer_bill_methods.reset_all();
                    if(customer_bill_insert_dummy){
                        
                    }
                    break;
                case 'view':
                    
                    var lcustomer_bill_id = $(lparent_pane).find('#customer_bill_id').val();
                    var lajax_url = customer_bill_data_support_url+'customer_bill_get/';
                    var json_data = {data:lcustomer_bill_id};
                    var lresponse = APP_DATA_TRANSFER.ajaxPOST(lajax_url,json_data).response;
                    var lcustomer_bill = lresponse.customer_bill;
                    var lreference = lresponse.reference;
                    var lreference_detail = lresponse.reference_detail;
                    
                    $(lparent_pane).find('#customer_bill_reference').select2('data',{id:lreference.id, text:lreference.text});
                    APP_COMPONENT.reference_detail.extra_info_set($(lparent_pane).find('#customer_bill_reference_detail')[0],lreference_detail,{reset:true});
                    
                    $(lparent_pane).find('#customer_bill_store').select2('data',{id:lcustomer_bill.store_id
                        ,text:lcustomer_bill.store_text});
                    $(lparent_pane).find('#customer_bill_code').val(lcustomer_bill.code);
                    $(lparent_pane).find('#customer_bill_amount').val(lcustomer_bill.amount);
                    $(lparent_pane).find('#customer_bill_outstanding_amount').val(lcustomer_bill.outstanding_amount);
                    $(lparent_pane).find('#customer_bill_customer_bill_date').datetimepicker({value:lcustomer_bill.customer_bill_date});
                    $(lparent_pane).find('#customer_bill_customer_bill_cancellation_reason').val(lcustomer_bill.cancellation_reason);

                    $(lparent_pane).find('#customer_bill_customer_bill_status')
                            .select2('data',{id:lcustomer_bill.customer_bill_status
                                ,text:lcustomer_bill.customer_bill_status_text}).change();
                    
                    $(lparent_pane).find('#customer_bill_customer')
                            .select2('data',{id:lcustomer_bill.customer_id
                                ,text:lcustomer_bill.customer_text}).change();
                    
                    $(lparent_pane).find('#customer_bill_customer_bill_status')
                            .select2({data:lresponse.customer_bill_status_list});
                    
                    break;
            }
        }
        
        
        customer_bill_methods.enable_disable();
        customer_bill_methods.show_hide();
        customer_bill_data_set();
    }
    
    var customer_bill_after_submit = function(){
        //function that will be executed after submit 
    }
    
   
    
    
    
    
    
</script>