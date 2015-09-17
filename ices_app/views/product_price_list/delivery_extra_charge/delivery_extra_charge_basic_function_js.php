<script>

    var delivery_extra_charge_parent_pane = $('<?php echo $detail_tab; ?>')[0];
    var delivery_extra_charge_ajax_url = null;
    var delivery_extra_charge_index_url = null;
    var delivery_extra_charge_view_url = null;
    var delivery_extra_charge_window_scroll = null;
    var delivery_extra_charge_data_support_url = null;
    var delivery_extra_charge_common_ajax_listener = null;

    var delivery_extra_charge_init = function(){
        var parent_pane = delivery_extra_charge_parent_pane;
        delivery_extra_charge_ajax_url = '<?php echo $ajax_url ?>';
        delivery_extra_charge_index_url = '<?php echo $index_url ?>';
        delivery_extra_charge_view_url = '<?php echo $view_url ?>';
        delivery_extra_charge_window_scroll = '<?php echo $window_scroll; ?>';
        delivery_extra_charge_data_support_url = '<?php echo $data_support_url; ?>';
        delivery_extra_charge_common_ajax_listener = '<?php echo $common_ajax_listener; ?>';
        
        delivery_extra_charge_purchase_invoice_extra_param_get = function(){
            //input select detail use this function to get extra parameter for further query
            
        };
        
        
    }
    
    var delivery_extra_charge_methods = {
        hide_all:function(){
            var lparent_pane = delivery_extra_charge_parent_pane;
            $(lparent_pane).find('.hide_all').hide();
            $(lparent_pane).find('#delivery_extra_charge_btn_submit').hide();
            
        },
        disable_all:function(){
            var lparent_pane = delivery_extra_charge_parent_pane;
            var lcomponents = $(lparent_pane).find('.disable_all');
            
            $.each(lcomponents,function(key, val){
                $(val).prop('disabled',true);
            });
            $(lparent_pane).find('#delivery_extra_charge_unit').select2('disable');
        },
        data_reset_all:function(){
           var lparent_pane = delivery_extra_charge_parent_pane;
           $(lparent_pane).find('#delivery_extra_charge_description').val('');
           $(lparent_pane).find('#delivery_extra_charge_min_qty').val('0.00').blur();
           $(lparent_pane).find('#delivery_extra_charge_amount').val('0.00').blur();
           $(lparent_pane).find('#delivery_extra_charge_unit').select2('data',null);
        },
        security_set:function(){
            
        },
        submit: function(){
            var lparent_pane = delivery_extra_charge_parent_pane;
            var lajax_url = delivery_extra_charge_index_url;
            var lmethod = $(lparent_pane).find('#delivery_extra_charge_method').val();
            var json_data = {
                ajax_post:true,
                message_session:true,
            };

            json_data.delivery_extra_charge = {
                description:$(lparent_pane).find('#delivery_extra_charge_description').val(),
                product_price_list_id:$(lparent_pane).find('#delivery_extra_charge_reference').val(),
                amount:$(lparent_pane).find('#delivery_extra_charge_amount').val().replace(/[,]/g,''),
                min_qty:$(lparent_pane).find('#delivery_extra_charge_min_qty').val().replace(/[,]/g,''),
                unit_id:$(lparent_pane).find('#delivery_extra_charge_unit').select2('val')
            };
            
            switch(lmethod){
                case 'add':                    
                    lajax_url +='delivery_extra_charge_add';
                    break;
                case 'view':
                    var delivery_extra_charge_id = $(lparent_pane).find('#delivery_extra_charge_id').val();
                    lajax_url +='delivery_extra_charge_update/'+delivery_extra_charge_id;
                    break;
            }
            result = null;
            
            result = APP_DATA_TRANSFER.submit(lajax_url,json_data);
            
            
            if(result !== null){
                if(result.success ===1){
                    $(lparent_pane).find('#delivery_extra_charge_id').val(result.trans_id);
                    if(delivery_extra_charge_view_url !==''){
                        var url = delivery_extra_charge_view_url+result.trans_id;
                        window.location.href=url;
                    }
                    else{
                        delivery_extra_charge_after_submit();
                    }
                }
            }
        },
        security_set:function(){
            var lparent_pane = delivery_extra_charge_parent_pane;
            var lsubmit_show = false;  
            
            var lstatus_label = '';
            switch($(lparent_pane).find('#delivery_extra_charge_method').val()){
                case 'add':
                    lstatus_label = 'delivery_extra_charge_add';
                    break
                case 'view':
                    lstatus_label = 'delivery_extra_charge_update';
                    break;
            }
                        
            if(APP_SECURITY.permission_get('product_price_list',lstatus_label).result){
                lsubmit_show = true;
            }
            
            if(lsubmit_show){
                $(lparent_pane).find('#delivery_extra_charge_btn_submit').show();
                
            }
            else{
                $(lparent_pane).find('#delivery_extra_charge_btn_submit').hide();
                
            }    
        }
    };
    
    var delivery_extra_charge_bind_event = function(){
        var lparent_pane = delivery_extra_charge_parent_pane;
        
        lamount = $(lparent_pane).find('#delivery_extra_charge_mixed_amount')[0];
        APP_EVENT.init().component_set(lamount).type_set('input').numeric_set().min_val_set(0).render();
        $(lamount).blur();
        
        $(lparent_pane).find('#delivery_extra_charge_btn_submit').off();        
        $(lparent_pane).find('#delivery_extra_charge_btn_submit')
        .on('click',function(e){
            e.preventDefault();
            var btn = $(this);
            btn.addClass('disabled');
            var lparent_pane = delivery_extra_charge_parent_pane;
            modal_confirmation_submit_parent = $(lparent_pane).attr('class').indexOf('modal-body')!==-1?
                $(lparent_pane).closest('.modal'):null;
            var lcalculation_type = $(lparent_pane).find('#delivery_extra_charge_calculation_type').val();
            $('#modal_confirmation_submit').modal('show');
            $('#modal_confirmation_submit_btn_submit').on('click',function(){
                delivery_extra_charge_methods.submit();
                $('#modal_confirmation_submit').modal('hide');

            });
            $(delivery_extra_charge_window_scroll).scrollTop(0);        
            setTimeout(function(){btn.removeClass('disabled')},1000);
        });
    }
    
    var delivery_extra_charge_components_prepare = function(){
        

        var delivery_extra_charge_data_set = function(){
            var lparent_pane = delivery_extra_charge_parent_pane;
            var lmethod = $(lparent_pane).find('#delivery_extra_charge_method').val();
            delivery_extra_charge_methods.data_reset_all();
            switch(lmethod){
                case 'add':
                    delivery_extra_charge_methods.security_set();
                    break;
                case 'view':
                    var ldelivery_extra_charge_id = $(lparent_pane).find('#delivery_extra_charge_id').val();
                    var lajax_support = delivery_extra_charge_data_support_url+'delivery_extra_charge/delivery_extra_charge_get';
                    var ljson_data = {delivery_extra_charge_id:ldelivery_extra_charge_id};
                    var lresult = APP_DATA_TRANSFER.ajaxPOST(lajax_support,ljson_data);
                    var lresponse = lresult.response;
                    $(lparent_pane).find('#delivery_extra_charge_description').val(lresponse.description).blur();
                    $(lparent_pane).find('#delivery_extra_charge_min_qty').val(lresponse.min_qty).blur();
                    $(lparent_pane).find('#delivery_extra_charge_amount').val(lresponse.amount).blur();
                    $(lparent_pane).find('#delivery_extra_charge_unit').select2('data',{id:lresponse.unit_id, text:lresponse.unit_name});
                    delivery_extra_charge_methods.security_set();
                    break;
            }
        }
        
        var delivery_extra_charge_enable_disable = function(){
            
            var lparent_pane = delivery_extra_charge_parent_pane;
            var lmethod = $(lparent_pane).find('#delivery_extra_charge_method').val();    
            delivery_extra_charge_methods.disable_all();
            switch(lmethod){
                case 'add':
                case 'view':
                    $(lparent_pane).find('#delivery_extra_charge_description').prop('disabled',false);
                    $(lparent_pane).find('#delivery_extra_charge_unit').select2('enable');
                    $(lparent_pane).find('#delivery_extra_charge_min_qty').prop('disabled',false);
                    $(lparent_pane).find('#delivery_extra_charge_amount').prop('disabled',false);
                    
                    break;
            }
        };
        
        var delivery_extra_charge_show_hide = function(){
            var lparent_pane = delivery_extra_charge_parent_pane;
            var lmethod = $(lparent_pane).find('#delivery_extra_charge_method').val();
            switch(lmethod){
                case 'add':     
                case 'view':
                    $(lparent_pane).find('#delivery_extra_charge_description').closest('.form-group').show();
                    $(lparent_pane).find('#delivery_extra_charge_unit').closest('.form-group').show();
                    $(lparent_pane).find('#delivery_extra_charge_min_qty').closest('.form-group').show();
                    $(lparent_pane).find('#delivery_extra_charge_amount').closest('.form-group').show();
                    break;
            }
        };
        
        delivery_extra_charge_enable_disable();
        delivery_extra_charge_show_hide();        
        delivery_extra_charge_data_set();
    }
    
    var delivery_extra_charge_after_submit = function(){
        //function that will be executed after submit 
    }
    
   
    
    
    
    

</script>