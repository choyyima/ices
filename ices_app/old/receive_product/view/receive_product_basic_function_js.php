<script>

    var receive_product_parent_pane = $('<?php echo $detail_tab; ?>')[0];
    var receive_product_ajax_url = null;
    var receive_product_index_url = null;
    var receive_product_view_url = null;
    var receive_product_window_scroll = null;
    var receive_product_data_support_url = null;
    var receive_product_common_ajax_listener = null;

    var receive_product_init = function(){
        var parent_pane = receive_product_parent_pane;
        receive_product_ajax_url = '<?php echo $ajax_url ?>';
        receive_product_index_url = '<?php echo $index_url ?>';
        receive_product_view_url = '<?php echo $view_url ?>';
        receive_product_window_scroll = '<?php echo $window_scroll; ?>';
        receive_product_data_support_url = '<?php echo $data_support_url; ?>';
        receive_product_common_ajax_listener = '<?php echo $common_ajax_listener; ?>';
        
        receive_product_purchase_invoice_extra_param_get = function(){
            //input select detail use this function to get extra parameter for further query
            
        };
        
        
    }
    
    var receive_product_methods = {
        status_label_get:function(){
            var parent_pane = receive_product_parent_pane;
            return $($(parent_pane).find('#receive_product_receive_product_status')
                    .select2('data').text).find('strong').length>0?
                    $($(parent_pane).find('#receive_product_receive_product_status')
                    .select2('data').text).find('strong')[0].innerHTML.toString().toLowerCase()
                    :$(parent_pane).find('#receive_product_receive_product_status')[0].innerHTML;
        },
        current_status_get: function(){
            var lreceive_product_id = $('#receive_product_id').val();
            var lresult = APP_DATA_TRANSFER.ajaxPOST(receive_product_data_support_url+'receive_product_current_status/',{data:lreceive_product_id});
            var lresponse = lresult.response;
            return lresponse;
        },
        hide_all:function(){
            var lparent_pane = receive_product_parent_pane;
            var ldivs = $(lparent_pane).find('>div');
            $.each(ldivs, function(key, val){
                $(val).attr('style','display:none');
            });
            $('#receive_product_submit').hide();
            $('#receive_product_print').hide();
            
        },
        show_hide:function(){
            var lparent_pane = receive_product_parent_pane;
            var lprefix_id = receive_product_component_prefix_id;
            var lmethod = $(lparent_pane).find(lprefix_id+'_method').val();
            receive_product_methods.hide_all();
            var lrp_type = $(lparent_pane).find(lprefix_id+'_type').val();
            
            switch(lmethod){
                case 'add':
                    $(lparent_pane).find('#receive_product_reference').closest('div [class*="form-group"]').show();
                    break;
                case 'view':
                    $(lparent_pane).find('#receive_product_reference').closest('div [class*="form-group"]').show();
                    break;
            }
        },
        disable_all:function(){
            var lparent_pane = receive_product_parent_pane;
            $(lparent_pane).find('#receive_product_reference').select2('disable');
            $(lparent_pane).find('#receive_product_code').prop('disabled',true);
            $(lparent_pane).find('#receive_product_receive_product_date').prop('disabled',true);
            $(lparent_pane).find('#receive_product_warehouse_from').select2('disable');
            $(lparent_pane).find('#receive_product_warehouse_to').select2('disable');
            $(lparent_pane).find('#receive_product_receive_product_status').select2('disable');
            $(lparent_pane).find('#receive_product_notes').prop('disabled',true);
            $(lparent_pane).find('#receive_product_store').select2('disable');
            $(lparent_pane).find('#receive_product_warehouse_from_detail').find('input').prop('disabled',true);
        },
        enable_disable:function(){
            var lparent_pane = receive_product_parent_pane;
            var lprefix_id = receive_product_component_prefix_id;
            var lmethod = $(lparent_pane).find(lprefix_id+'_method').val();                
            var lrp_type = $(lparent_pane).find(lprefix_id+'_type').val();
            var lreference_id = $(lparent_pane).find(lprefix_id+'_reference').select2('val');
            
            receive_product_methods.disable_all();
            
            switch(lmethod){
                case 'add':
                    $(lparent_pane).find('#receive_product_reference').select2('enable');
                    break;
                case 'view':
                    $(lparent_pane).find('#receive_product_reference').select2('disable');
                    break;
            }
            
            if(lreference_id !== ''){
                
            }
        }
    };
    
    var receive_product_bind_event = function(){
        var parent_pane = receive_product_parent_pane;
        
        <?php /* ?>        
        $(parent_pane).find('#receive_product_print').on('click',function(){
            
            var lreceive_product_id = $(parent_pane).find('#receive_product_id').val();
            window.open(receive_product_index_url+'printing/'+'receive_product/'+lreceive_product_id);
        })  ;      
        <?php */ ?>
        
        $(parent_pane).find("#receive_product_reference")
        .on('change', function(){
            
            var ldata = $(this).select2('data');
    
            if (ldata === null){
                var lparent_pane = receive_product_parent_pane;
                receive_product_methods.hide_all();
                $(lparent_pane).find('#receive_product_reference').closest('div [class*="form-group"]').show();


            }   
            else{
                $('#receive_product_reference_type').val(ldata.reference_type);            

                $('#receive_product_reference_detail_type')[0].innerHTML = '';            
                if(typeof ldata.reference_type_name !== 'undefined') 
                    $('#receive_product_reference_detail_type')[0].innerHTML = ldata.reference_type_name;

                $('#receive_product_reference_detail_code')[0].innerHTML='';
                if(typeof ldata.reference_code !== 'undefined') 
                    $('#receive_product_reference_detail_code')[0].innerHTML = ldata.reference_code;
                $('#receive_product_reference_detail').find('.extra_info').remove();
                
                switch($('#receive_product_reference_type').val()){
                    case 'purchase_invoice':
                        receive_product_purchase_invoice_methods.hide_show();
                        receive_product_purchase_invoice_methods.enable_disable();
                        receive_product_purchase_invoice_methods.init_data();                    
                        break;
                    case 'rma':
                        receive_product_rma_methods.hide_show();
                        receive_product_rma_methods.enable_disable();
                        receive_product_rma_methods.init_data();                    


                }
            }
        });
        
        $(parent_pane).find('#receive_product_receive_product_status').off();
        $(parent_pane).find('#receive_product_receive_product_status')
        .on('change',function(){
            var lparent_pane = receive_product_parent_pane;
    
            if($(this).select2('val') === 'X'){
                $(lparent_pane).find('#receive_product_div_cancellation_reason').show();
            }
            else{
                $(lparent_pane).find('#receive_product_div_cancellation_reason').hide();
            }
            
            var lreference_type = $(lparent_pane).find('#receive_product_reference_type').val();
            switch(lreference_type){
                case 'purchase_invoice':
                    receive_product_purchase_invoice_methods.security_set();
                    break;
                case 'rma':
                    receive_product_rma_methods.security_set();
                    break;
            }
            
            
            
            
        });
        
        $(parent_pane).find('#receive_product_submit').off();        
        $(parent_pane).find('#receive_product_submit')
        .on('click',function(e){
            e.preventDefault();
            var btn = $(this);
            btn.addClass('disabled');            
            var lparent_pane = receive_product_parent_pane;
            modal_confirmation_submit_parent = $(lparent_pane).attr('class').indexOf('modal-body')!==-1?
                $(lparent_pane).closest('.modal'):null;
            var lreference_type = $(lparent_pane).find('#receive_product_reference_type').val();
            switch(lreference_type){
                case 'purchase_invoice':
                    $('#modal_confirmation_submit').modal('show');
                    $('#modal_confirmation_submit_btn_submit').on('click',function(){
                        receive_product_purchase_invoice_methods.submit();
                        $('#modal_confirmation_submit').modal('hide');
                    
                    });
                    
                    break;
                case 'rma':
                    $('#modal_confirmation_submit').modal('show');
                    $('#modal_confirmation_submit_btn_submit').on('click',function(){
                        receive_product_rma_methods.submit();
                        $('#modal_confirmation_submit').modal('hide');
                    
                    });
                    
                    break;
            }
            
            
            $(receive_product_window_scroll).scrollTop(0);        
            setTimeout(function(){btn.removeClass('disabled')},1000);

            
        });
            
        
    }
    
    var receive_product_components_prepare = function(){
        

        var receive_product_data_set = function(){
            var lparent_pane = receive_product_parent_pane;
            var lmethod = $(lparent_pane).find('#receive_product_method').val();
            
            switch(lmethod){
                case 'add':
                    
                    break;
                case 'edit':
                case 'view':
                    var lreceive_product_id = $(lparent_pane).find('#receive_product_id').val();
                    
                    var lajax_url = receive_product_data_support_url+'receive_product_init_get';
                    var result = APP_DATA_TRANSFER.ajaxPOST(lajax_url,{data:lreceive_product_id});
                    var lreceive_product = result.response;
                    $(lparent_pane).find('#receive_product_reference').select2(
                        'data',lreceive_product).change();
                    break;
            }
        }
        
        receive_product_methods.enable_disable();
        receive_product_methods.show_hide();
        receive_product_data_set();
    }
    
    var receive_product_after_submit = function(){
        //function that will be executed after submit 
    }
    
   
    
    
    
    

</script>