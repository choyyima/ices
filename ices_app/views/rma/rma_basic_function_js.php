<script>

    var rma_parent_pane = $('<?php echo $detail_tab; ?>')[0];
    var rma_ajax_url = null;
    var rma_index_url = null;
    var rma_view_url = null;
    var rma_window_scroll = null;
    var rma_data_support_url = null;
    var rma_common_ajax_listener = null;

    var rma_init = function(){
        var parent_pane = rma_parent_pane;
        rma_ajax_url = '<?php echo $ajax_url ?>';
        rma_index_url = '<?php echo $index_url ?>';
        rma_view_url = '<?php echo $view_url ?>';
        rma_window_scroll = '<?php echo $window_scroll; ?>';
        rma_data_support_url = '<?php echo $data_support_url; ?>';
        rma_common_ajax_listener = '<?php echo $common_ajax_listener; ?>';
        
        rma_purchase_invoice_extra_param_get = function(){
            //input select detail use this function to get extra parameter for further query
            
        };
        
        
    }
    
    var rma_methods = {
        status_label_get:function(){
            var parent_pane = rma_parent_pane;
            return $($(parent_pane).find('#rma_rma_status')
                    .select2('data').text).find('strong').length>0?
                    $($(parent_pane).find('#rma_rma_status')
                    .select2('data').text).find('strong')[0].innerHTML.toString().toLowerCase()
                    :$(parent_pane).find('#rma_rma_status')[0].innerHTML;
        },
        current_status_get: function(){
            var lrma_id = $('#rma_id').val();
            var lresult = APP_DATA_TRANSFER.ajaxPOST(rma_data_support_url+'rma_current_status/',{data:lrma_id});
            var lresponse = lresult.response;
            return lresponse;
        },
        hide_all:function(){
            var lparent_pane = rma_parent_pane;
            var ldivs = $(lparent_pane).find('>div');
            $.each(ldivs, function(key, val){
                $(val).attr('style','display:none');
            });
            $('#rma_submit').hide();
            $('#rma_print').hide();
            
        },
        disable_all:function(){
            var lparent_pane = rma_parent_pane;
            $(lparent_pane).find('#rma_reference').select2('disable');
            $(lparent_pane).find('#rma_code').prop('disabled',true);
            $(lparent_pane).find('#rma_rma_date').prop('disabled',true);
            $(lparent_pane).find('#rma_supplier').select2('disable');
            $(lparent_pane).find('#rma_rma_status').select2('disable');
            $(lparent_pane).find('#rma_notes').prop('disabled',true);
            $(lparent_pane).find('#rma_store').select2('disable');
        }
       
    };
    
    var rma_bind_event = function(){
        var parent_pane = rma_parent_pane;

        $(parent_pane).find("#rma_reference")
        .on('change', function(){
            var ldata = $(this).select2('data');
            $(parent_pane).find('#rma_reference_type').val(ldata.reference_type);            
            
            $('#rma_reference_detail_type')[0].innerHTML = '';            
            if(typeof ldata.reference_type_name !== 'undefined') 
                $('#rma_reference_detail_type')[0].innerHTML = ldata.reference_type_name;
            
            $('#rma_reference_detail_code')[0].innerHTML='';
            if(typeof ldata.reference_code !== 'undefined') 
                $('#rma_reference_detail_code')[0].innerHTML = ldata.reference_code;
            $('#rma_reference_detail').find('.extra_info').remove();
            
            
            
            switch($('#rma_reference_type').val()){
                case 'purchase_invoice':
                    rma_purchase_invoice_methods.hide_show();
                    rma_purchase_invoice_methods.enable_disable();
                    rma_purchase_invoice_methods.init_data();                    
                    break;
            }
        });
        
        $(parent_pane).find('#rma_rma_status').off();
        $(parent_pane).find('#rma_rma_status')
        .on('change',function(){
            var lparent_pane = rma_parent_pane;
    
            if($(this).select2('val') === 'X'){
                $(lparent_pane).find('#rma_div_cancellation_reason').show();
            }
            else{
                $(lparent_pane).find('#rma_div_cancellation_reason').hide();
            }
            
            var lreference_type = $(lparent_pane).find('#rma_reference_type').val();
            switch(lreference_type){
                case 'purchase_invoice':
                    rma_purchase_invoice_methods.security_set();
                    break;

            }
            
            
            
            
        });
        
        $(parent_pane).find('#rma_submit').off();        
        $(parent_pane).find('#rma_submit')
        .on('click',function(e){
            e.preventDefault();
            var btn = $(this);
            btn.addClass('disabled');
            var lparent_pane = rma_parent_pane;
            modal_confirmation_submit_parent = $(lparent_pane).attr('class').indexOf('modal-body')!==-1?
                $(lparent_pane).closest('.modal'):null;
            var lreference_type = $(lparent_pane).find('#rma_reference_type').val();
            switch(lreference_type){
                case 'purchase_invoice':
                    $('#modal_confirmation_submit').modal('show');
                    $('#modal_confirmation_submit_btn_submit').on('click',function(){
                        rma_purchase_invoice_methods.submit();
                        $('#modal_confirmation_submit').modal('hide');
                    
                    });
                    
                    break;
            }
            
            
            $(rma_window_scroll).scrollTop(0);        
            setTimeout(function(){btn.removeClass('disabled')},1000);

            
        });
            
        
    }
    
    var rma_components_prepare = function(){
        

        var rma_data_set = function(){
            var lparent_pane = rma_parent_pane;
            var lmethod = $(lparent_pane).find('#rma_method').val();
            
            switch(lmethod){
                case 'add':
                    
                    break;
                case 'edit':
                case 'view':
                    var lrma_id = $(lparent_pane).find('#rma_id').val();
                    
                    var lajax_url = rma_data_support_url+'rma_init_get';
                    var result = APP_DATA_TRANSFER.ajaxPOST(lajax_url,{data:lrma_id});
                    var lrma = result.response;
                    $(lparent_pane).find('#rma_reference').select2(
                        'data',lrma).change();
                    break;
            }
        }
        
        var rma_components_enable_disable = function(){
            
            var lparent_pane = rma_parent_pane;
            var lmethod = $(lparent_pane).find('#rma_method').val();    
            rma_methods.disable_all();

            switch(lmethod){
                case 'add':
                    $(lparent_pane).find('#rma_reference').select2('enable');
                    break;
                case 'view':
                    $(lparent_pane).find('#rma_reference').select2('disable');
                    break;
            }
            
        }
        
        var rma_components_show_hide = function(){
            var lparent_pane = rma_parent_pane;
            var lmethod = $(lparent_pane).find('#rma_method').val();
            rma_methods.hide_all();
            
            switch(lmethod){
                case 'add':
                    $(lparent_pane).find('#rma_reference').closest('div [class*="form-group"]').show();
                    break;
                case 'view':
                    $(lparent_pane).find('#rma_reference').closest('div [class*="form-group"]').show();
                    break;
            }
        }
                
        rma_components_enable_disable();
        rma_components_show_hide();
        rma_data_set();
    }
    
    var rma_after_submit = function(){
        //function that will be executed after submit 
    }
    
   
    
    
    
    

</script>