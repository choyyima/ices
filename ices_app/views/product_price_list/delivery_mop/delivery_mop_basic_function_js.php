<script>

    var delivery_mop_parent_pane = $('<?php echo $detail_tab; ?>')[0];
    var delivery_mop_ajax_url = null;
    var delivery_mop_index_url = null;
    var delivery_mop_view_url = null;
    var delivery_mop_window_scroll = null;
    var delivery_mop_data_support_url = null;
    var delivery_mop_common_ajax_listener = null;

    var delivery_mop_init = function(){
        var parent_pane = delivery_mop_parent_pane;
        delivery_mop_ajax_url = '<?php echo $ajax_url ?>';
        delivery_mop_index_url = '<?php echo $index_url ?>';
        delivery_mop_view_url = '<?php echo $view_url ?>';
        delivery_mop_window_scroll = '<?php echo $window_scroll; ?>';
        delivery_mop_data_support_url = '<?php echo $data_support_url; ?>';
        delivery_mop_common_ajax_listener = '<?php echo $common_ajax_listener; ?>';
        
        delivery_mop_purchase_invoice_extra_param_get = function(){
            //input select detail use this function to get extra parameter for further query
            
        };
        
        
    }
    
    var delivery_mop_methods = {
        hide_all:function(){
            var lparent_pane = delivery_mop_parent_pane;
            $(lparent_pane).find('.hide_all').hide();
            $(lparent_pane).find('#delivery_mop_btn_submit').hide();
            $(lparent_pane).find('#delivery_mop_mixed_product_table').closest('div [class*="form-group"]').hide();
            $(lparent_pane).find('#delivery_mop_separated_product_table').closest('div [class*="form-group"]').hide();
        },
        disable_all:function(){
            var lparent_pane = delivery_mop_parent_pane;
            var lcomponents = $(lparent_pane).find('.disable_all');
            
            $.each(lcomponents,function(key, val){
                $(val).prop('disabled',true);
            });

            $(lparent_pane).find('#delivery_mop_mixed_unit').select2('disable',true);
        },
        data_reset_all:function(){
           var lparent_pane = delivery_mop_parent_pane;
           $(lparent_pane).find('#delivery_mop_mixed_product_table>tbody').empty();
           $(lparent_pane).find('#delivery_mop_separated_product_table>tbody').empty();
           
           
        },
        
    };
    
    var delivery_mop_bind_event = function(){
        var lparent_pane = delivery_mop_parent_pane;
        
        lamount = $(lparent_pane).find('#delivery_mop_mixed_amount')[0];
        APP_EVENT.init().component_set(lamount).type_set('input').numeric_set().min_val_set(0).render();
        $(lamount).blur();
        
        $(lparent_pane).find('#delivery_mop_btn_submit').off();        
        $(lparent_pane).find('#delivery_mop_btn_submit')
        .on('click',function(e){
            e.preventDefault();
            var btn = $(this);
            btn.addClass('disabled');
            var lparent_pane = delivery_mop_parent_pane;
            modal_confirmation_submit_parent = $(lparent_pane).attr('class').indexOf('modal-body')!==-1?
                $(lparent_pane).closest('.modal'):null;
            var lcalculation_type = $(lparent_pane).find('#delivery_mop_calculation_type').val();
            $('#modal_confirmation_submit').modal('show');
            $('#modal_confirmation_submit_btn_submit').on('click',function(){
                switch(lcalculation_type){
                    case 'mixed':
                        delivery_mop_mixed_methods.submit();
                        break;
                    case 'separated':
                        delivery_mop_separated_methods.submit();
                        break;
                }
                $('#modal_confirmation_submit').modal('hide');

            });
            $(delivery_mop_window_scroll).scrollTop(0);        
            setTimeout(function(){btn.removeClass('disabled')},1000);
        });
        
        $(lparent_pane).find('#delivery_mop_calculation_type').on('change',function(){
            var lcalculation_type = $(this).select2('val');
            delivery_mop_methods.hide_all();
            delivery_mop_methods.disable_all();
            delivery_mop_methods.data_reset_all();
            switch(lcalculation_type){
                case 'mixed':
                    delivery_mop_mixed_methods.hide_show();
                    delivery_mop_mixed_methods.enable_disable();
                    delivery_mop_mixed_methods.init_data();
                    break;
                case 'separated':
                    delivery_mop_separated_methods.hide_show();
                    delivery_mop_separated_methods.enable_disable();
                    delivery_mop_separated_methods.init_data();
                    break;
            }
        });
            
        
    }
    
    var delivery_mop_components_prepare = function(){
        

        var delivery_mop_data_set = function(){
            var lparent_pane = delivery_mop_parent_pane;
            var lmethod = $(lparent_pane).find('#delivery_mop_method').val();
            delivery_mop_methods.data_reset_all();
            switch(lmethod){
                case 'add':
                    $(lparent_pane).find('#delivery_mop_calculation_type').select2('data',null);
                    $(lparent_pane).find('#delivery_mop_code').val('');
                    break;
                case 'view':
                    var ldelivery_mop_id = $(lparent_pane).find('#delivery_mop_id').val();
                    var lajax_support = delivery_mop_data_support_url+'delivery_mop/delivery_mop_get';
                    var ljson_data = {delivery_mop_id:ldelivery_mop_id};
                    var lresult = APP_DATA_TRANSFER.ajaxPOST(lajax_support,ljson_data);
                    var lresponse = lresult.response;
                    
                    $(lparent_pane).find('#delivery_mop_code').val(lresponse.code);
                    $(lparent_pane).find('#delivery_mop_calculation_type').select2('data',{id:lresponse.calculation_type,text:lresponse.calculation_type_name}).change();
                    
                    break;
            }
        }
        
        var delivery_mop_enable_disable = function(){
            
            var lparent_pane = delivery_mop_parent_pane;
            var lmethod = $(lparent_pane).find('#delivery_mop_method').val();    
            delivery_mop_methods.disable_all();
            switch(lmethod){
                case 'add':
                    $(lparent_pane).find('#delivery_mop_calculation_type').select2('enable');
                    break;
                case 'view':
                    $(lparent_pane).find('#delivery_mop_calculation_type').select2('disable');
                    break;
            }
        };
        
        var delivery_mop_show_hide = function(){
            var lparent_pane = delivery_mop_parent_pane;
            var lmethod = $(lparent_pane).find('#delivery_mop_method').val();
            delivery_mop_methods.hide_all();
            switch(lmethod){
                case 'add':     
                    break;
                case 'view':
                    break;
            }
        };
        
        delivery_mop_enable_disable();
        delivery_mop_show_hide();        
        delivery_mop_data_set();
    }
    
    var delivery_mop_after_submit = function(){
        //function that will be executed after submit 
    }
    
   
    
    
    
    

</script>