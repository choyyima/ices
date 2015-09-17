<script>

    var product_unit_conversion_parent_pane = $('<?php echo $detail_tab; ?>')[0];
    var product_unit_conversion_ajax_url = null;
    var product_unit_conversion_index_url = null;
    var product_unit_conversion_view_url = null;
    var product_unit_conversion_window_scroll = null;
    var product_unit_conversion_data_support_url = null;
    var product_unit_conversion_common_ajax_listener = null;

    var product_unit_conversion_init = function(){
        var parent_pane = product_unit_conversion_parent_pane;
        product_unit_conversion_ajax_url = '<?php echo $ajax_url ?>';
        product_unit_conversion_index_url = '<?php echo $index_url ?>';
        product_unit_conversion_view_url = '<?php echo $view_url ?>';
        product_unit_conversion_window_scroll = '<?php echo $window_scroll; ?>';
        product_unit_conversion_data_support_url = '<?php echo $data_support_url; ?>';
        product_unit_conversion_common_ajax_listener = '<?php echo $common_ajax_listener; ?>';
        
        product_unit_conversion_purchase_invoice_extra_param_get = function(){
            
            
        };
        
        
    }
    
    var product_unit_conversion_methods = {
        hide_all:function(){
            var lparent_pane = product_unit_conversion_parent_pane;
            $(lparent_pane).find('.hide_all').hide();
        },
        disable_all:function(){
            var lparent_pane = product_unit_conversion_parent_pane;
            APP_COMPONENT.disable_all(lparent_pane);
            $(lparent_pane).find('#product_unit_conversion_unit_1').select2('disable');
            $(lparent_pane).find('#product_unit_conversion_unit_2').select2('disable');
            $(lparent_pane).find('#product_unit_conversion_expedition').select2('disable');
        },
        data_reset_all:function(){
           var lparent_pane = product_unit_conversion_parent_pane;
           $(lparent_pane).find('#product_unit_conversion_qty_1').val('1.00');
           $(lparent_pane).find('#product_unit_conversion_unit_1').select2('data',null);
           $(lparent_pane).find('#product_unit_conversion_qty_2').val('0.00');
           $(lparent_pane).find('#product_unit_conversion_unit_2').select2('data',null);
        },
        security_set:function(){
            var lparent_pane = product_unit_conversion_parent_pane;
            var lsubmit_show = false;  
            
            var lstatus_label = '';
            switch($(lparent_pane).find('#product_unit_conversion_method').val()){
                case 'add':
                    lstatus_label = 'add';
                    break
                case 'view':
                    lstatus_label = $(lparent_pane).find('#product_unit_conversion_status').select2('val');
                    break;
            }
                        
            if(APP_SECURITY.permission_get('product','product_unit_conversion_'+lstatus_label).result){
                lsubmit_show = true;
            }
            
            if(lsubmit_show){
                $(lparent_pane).find('#product_unit_conversion_btn_submit').show();
                
            }
            else{
                $(lparent_pane).find('#product_unit_conversion_btn_submit').hide();
                
            }    
        },
        
        Unit_1_set:function(){
            var lparent_pane = product_unit_conversion_parent_pane;
            var lajax_url = product_unit_conversion_data_support_url+'unit_1_list';
            var lproduct_id = $(lparent_pane).find('#product_unit_conversion_reference').val();
            var ljson_data = {product_id:lproduct_id};
            var lresult = APP_DATA_TRANSFER.ajaxPOST(lajax_url,ljson_data);
            var lresponse = lresult.response;
            $(lparent_pane).find('#product_unit_conversion_unit_1').select2({data:lresponse});
            if(lresponse.length>0){
                $(lparent_pane).find('#product_unit_conversion_unit_1').select2('data',lresponse[0]);
            }
        }
        
    };
    
    var product_unit_conversion_bind_event = function(){
        var lparent_pane = product_unit_conversion_parent_pane;
        
        var lqty1 = $(lparent_pane).find('#product_unit_conversion_qty_1')[0];
        APP_COMPONENT.input.numeric($(lqty1),{min_val:1,dec:5});
        
        
        var lqty = $(lparent_pane).find('#product_unit_conversion_qty_2')[0];
        APP_COMPONENT.input.numeric($(lqty),{min_val:0,dec:5});
        
        $(lqty).blur();
        
        $(lparent_pane).find('#product_unit_conversion_btn_submit').off();        
        $(lparent_pane).find('#product_unit_conversion_btn_submit')
        .on('click',function(e){
            e.preventDefault();
            var btn = $(this);
            btn.addClass('disabled');
            var lparent_pane = product_unit_conversion_parent_pane;
            modal_confirmation_submit_parent = $(lparent_pane).attr('class').indexOf('modal-body')!==-1?
                $(lparent_pane).closest('.modal'):null;
            var ltype = $(lparent_pane).find('#product_unit_conversion_type').val();
            $('#modal_confirmation_submit').modal('show');
            $('#modal_confirmation_submit_btn_submit').on('click',function(){
                var ltype = $(lparent_pane).find('#product_unit_conversion_type').select2('val');
                switch(ltype){
                    case 'sales_moq':
                        product_unit_conversion_sales_moq_methods.submit();
                    break;
                    case 'sales_real_weight':
                        product_unit_conversion_sales_real_weight_methods.submit();
                    break;
                    case 'sales_expedition_weight':
                        product_unit_conversion_sales_expedition_weight_methods.submit();
                    break;
                }
                
                $('#modal_confirmation_submit').modal('hide');

            });
            $(product_unit_conversion_window_scroll).scrollTop(0);        
            setTimeout(function(){btn.removeClass('disabled')},1000);
        });
        
        $(lparent_pane).find('#product_unit_conversion_type').on('change',function(){
            var lparent_pane = product_unit_conversion_parent_pane;
            var ltype = $(lparent_pane).find('#product_unit_conversion_type').select2('val');
            product_unit_conversion_methods.hide_all();
            product_unit_conversion_methods.disable_all();
            product_unit_conversion_methods.data_reset_all();
            switch(ltype){
                case 'sales_moq':
                    product_unit_conversion_sales_moq_methods.show();
                    product_unit_conversion_sales_moq_methods.enable();
                    product_unit_conversion_sales_moq_methods.data_set();
                    break;
                case 'sales_real_weight':
                    product_unit_conversion_sales_real_weight_methods.show();
                    product_unit_conversion_sales_real_weight_methods.enable();
                    product_unit_conversion_sales_real_weight_methods.data_set();
                    break;
                case 'sales_expedition_weight':
                    product_unit_conversion_sales_expedition_weight_methods.show();
                    product_unit_conversion_sales_expedition_weight_methods.enable();
                    product_unit_conversion_sales_expedition_weight_methods.data_set();
                    break;
            }
            
        });
        
        $(lparent_pane).find('#product_unit_conversion_status').on('change',function(){
            product_unit_conversion_methods.security_set();
        });
        
    }
    
    var product_unit_conversion_components_prepare = function(){
        


        var product_unit_conversion_data_set = function(){
            var lparent_pane = product_unit_conversion_parent_pane;
            var lmethod = $(lparent_pane).find('#product_unit_conversion_method').val();
            product_unit_conversion_methods.data_reset_all();
            switch(lmethod){
                case 'add':
                    $(lparent_pane).find('#product_unit_conversion_type').select2('data',null);
                    APP_FORM.status.default_status_set('product_unit_conversion', 
                        $(lparent_pane).find('#product_unit_conversion_status')[0]);
                    break;
                case 'view':
                    $(lparent_pane).find('#product_unit_conversion_type').select2('data',null);
                    var lajax_url = product_unit_conversion_data_support_url+'product_unit_conversion_type_get';
                    var ljson_data = {product_unit_conversion_id:$(lparent_pane).find('#product_unit_conversion_id').val()};
                    var lresult = APP_DATA_TRANSFER.ajaxPOST(lajax_url,ljson_data);
                    var lresponse = lresult.response;
                    switch(lresponse.id){
                        case 'sales_moq':
                            $(lparent_pane).find('#product_unit_conversion_type').select2('data',{id:lresponse.id,text:lresponse.text});
                            product_unit_conversion_sales_moq_methods.show();
                            product_unit_conversion_sales_moq_methods.enable();
                            product_unit_conversion_sales_moq_methods.data_set();
                            break;
                        case 'sales_real_weight':
                            $(lparent_pane).find('#product_unit_conversion_type').select2('data',{id:lresponse.id,text:lresponse.text});
                            product_unit_conversion_sales_real_weight_methods.show();
                            product_unit_conversion_sales_real_weight_methods.enable();
                            product_unit_conversion_sales_real_weight_methods.data_set();
                            break;
                        case 'sales_expedition_weight':
                            $(lparent_pane).find('#product_unit_conversion_type').select2('data',{id:lresponse.id,text:lresponse.text});
                            product_unit_conversion_sales_expedition_weight_methods.show();
                            product_unit_conversion_sales_expedition_weight_methods.enable();
                            product_unit_conversion_sales_expedition_weight_methods.data_set();
                            break;
                    }
                    break;
            }
        }
        
        var product_unit_conversion_enable_disable = function(){
            
            var lparent_pane = product_unit_conversion_parent_pane;
            var lmethod = $(lparent_pane).find('#product_unit_conversion_method').val();    
            product_unit_conversion_methods.disable_all();
            switch(lmethod){
                case 'add':
                    $(lparent_pane).find('#product_unit_conversion_type').select2('enable');
                    
                    break
                case 'view':
                    $(lparent_pane).find('#product_unit_conversion_type').select2('disable');
                    
                    break;
            }
            
        };
        
        var product_unit_conversion_show_hide = function(){
            var lparent_pane = product_unit_conversion_parent_pane;
            var lmethod = $(lparent_pane).find('#product_unit_conversion_method').val();
            product_unit_conversion_methods.hide_all();
            product_unit_conversion_methods.security_set();
            
        };
        
        product_unit_conversion_enable_disable();
        product_unit_conversion_show_hide();        
        product_unit_conversion_data_set();
    }
        
    var product_unit_conversion_after_submit = function(){
        //function that will be executed after submit 
    }
    
   
    
    
    
    

</script>