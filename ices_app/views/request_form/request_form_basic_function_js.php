<script>

    var request_form_parent_pane = $('<?php echo $detail_tab; ?>')[0];
    var request_form_ajax_url = null;
    var request_form_index_url = null;
    var request_form_view_url = null;
    var request_form_window_scroll = null;
    var request_form_data_support_url = null;
    var request_form_common_ajax_listener = null;

    var request_form_type_mapping = {
        mutation:'<?php echo $mutation_id; ?>'
    }

    var request_form_init = function(){
        var parent_pane = request_form_parent_pane;
        request_form_ajax_url = '<?php echo $ajax_url ?>';
        request_form_index_url = '<?php echo $index_url ?>';
        request_form_view_url = '<?php echo $view_url ?>';
        request_form_window_scroll = '<?php echo $window_scroll; ?>';
        request_form_data_support_url = '<?php echo $data_support_url; ?>';
        request_form_common_ajax_listener = '<?php echo $common_ajax_listener; ?>';
        
        request_form_request_form_mutation_product_extra_param_get = function(){
            //return {warehouse_from:$(request_form_parent_pane).find('#request_form_mutation_warehouse_from').select2('val')};
        };
    }
    
    var request_form_methods = {
        status_label_get:function(){
            var parent_pane = request_form_parent_pane;
            return $($(parent_pane).find('#request_form_request_form_status')
                    .select2('data').text).find('strong').length>0?
                    $($(parent_pane).find('#request_form_request_form_status')
                    .select2('data').text).find('strong')[0].innerHTML.toString().toLowerCase()
                    :$(parent_pane).find('#request_form_request_form_status')[0].innerHTML;
        },
        hide_all:function(){
            var lparent_pane = request_form_parent_pane;
            var ldivs = $(lparent_pane).find('>div');
            $.each(ldivs, function(key, val){
                $(val).hide();
            });
            
            $(lparent_pane).find('#request_form_submit').hide();
            
        },
        disable_all:function(){
            var lparent_pane = request_form_parent_pane;
            $(lparent_pane).find('#request_form_request_form_type').select2('disable');
            $(lparent_pane).find('#request_form_code').prop('disabled',true);
            $(lparent_pane).find('#request_form_requester').prop('disabled',true);
            $(lparent_pane).find('#request_form_mutation_warehouse_from').select2('disable');
            $(lparent_pane).find('#request_form_mutation_warehouse_to').select2('disable');
            $(lparent_pane).find('#request_form_request_form_date').prop('disabled',true);
            $(lparent_pane).find('#request_form_notes').prop('disabled',true);
            $(lparent_pane).find('#request_form_request_form_mutation_product').select2('disable');
            $(lparent_pane).find('#request_form_request_form_status').select2('disable');
        }

    };
    
    
    var request_form_bind_event = function(){
        var lparent_pane = request_form_parent_pane;
        
        $(lparent_pane).find('#request_form_request_form_type').on('change',function(){
            if($(this).select2('val') === request_form_type_mapping.mutation){
                request_form_mutation_methods.hide_show();
                request_form_mutation_methods.enable_disable();
                request_form_mutation_methods.init_data();
            }            
        });
        
        $(lparent_pane).find('#request_form_request_form_status').off();
        $(lparent_pane).find('#request_form_request_form_status').on('change',function(){
            if($(this).select2('val') === 'X'){
                $(lparent_pane).find('#request_form_cancellation_reason').parent().show();
            }
            else{
                $(lparent_pane).find('#request_form_cancellation_reason').parent().hide();
            }
            
            var lrequest_form_type = $(lparent_pane).find('#request_form_request_form_type').select2('val');
            switch(lrequest_form_type){
                case request_form_type_mapping.mutation:
                    request_form_mutation_methods.mutation_status_event();
                    break;
            }
            
        });
        
        $(lparent_pane).find("#request_form_request_form_mutation_product")
        .on('change', function(){
            request_form_mutation_methods.product_table_add();
        });
        
        
        
        
        $(lparent_pane).find('#request_form_submit').off();        
        $(lparent_pane).find('#request_form_submit')
        .on('click',function(e){
            e.preventDefault();
            var btn = $(this);
            btn.addClass('disabled');
            var parent_pane = request_form_parent_pane;
            switch($(lparent_pane).find('#request_form_request_form_type').select2('val')){
                case request_form_type_mapping.mutation:
                    request_form_mutation_methods.submit();
                    break;
            }
            
            $(request_form_window_scroll).scrollTop(0);        
            setTimeout(function(){btn.removeClass('disabled')},1000);            
        });
            
        
    }
    
    var request_form_components_prepare = function(){
        
        
        var request_form_data_set = function(){
            var lparent_pane = request_form_parent_pane;
            var lmethod = $(lparent_pane).find('#request_form_method').val();
            
            switch(lmethod){
                case 'add':
                    $(lparent_pane).find('#request_form_code').val('[AUTO GENERATE]');
                    var ldefault_status = null;
                    ldefault_status = APP_DATA_TRANSFER.ajaxPOST(request_form_data_support_url+'default_status_get');
                    
                    $(lparent_pane).find('#request_form_request_form_status')
                            .select2('data',{id:ldefault_status.val,text:ldefault_status.label});
                    var lrequest_form_status_list = [
                        {id:ldefault_status.val,text:ldefault_status.label}//,
                    ]
                    $(lparent_pane).find('#request_form_request_form_status').select2({data:lrequest_form_status_list});
                    $(lparent_pane).find('#request_form_warehouse_to').select2('data',{id:'',text:''});
                    break;
                case 'edit':
                case 'view':
                    var lrequest_form_id = $(lparent_pane).find('#request_form_id').val();
                    var lajax_url = request_form_ajax_url+'request_form_request_form_type_ajax_get';
                    var lrequest_form_type = APP_DATA_TRANSFER.ajaxPOST(lajax_url,{data:lrequest_form_id});
                    $(lparent_pane).find('#request_form_request_form_type')
                            .select2('data',{id:lrequest_form_type.id,text:lrequest_form_type.name}).change();
                    break;
            }
        }
        
        var request_form_components_enable_disable = function(){
            
            var lparent_pane = request_form_parent_pane;
            var lmethod = $(lparent_pane).find('#request_form_method').val();    
            
            $(lparent_pane).find('#request_form_request_form_type').select2('disable');
            $(lparent_pane).find('#request_form_request_form_status').select2('disable');
            $(lparent_pane).find('#request_form_warehouse_to').select2('disable');
            $(lparent_pane).find('#request_form_request_form_date').attr('disabled','');
            $(lparent_pane).find('#request_form_purchase_invoice').select2('disable');
            $(lparent_pane).find('#request_form_notes').attr('disable','');
            switch(lmethod){
                case 'add':
                    $(lparent_pane).find('#request_form_request_form_type').select2('enable');
                    break;
                case 'view':
                    $(lparent_pane).find('#request_form_request_form_type').select2('disable');
                    break;
            }
        }
        
        var request_form_components_show_hide = function(){
            var lparent_pane = request_form_parent_pane;
            var lmethod = $(lparent_pane).find('#request_form_method').val();

            switch(lmethod){
                case 'add':
                    $(lparent_pane).find('#request_form_request_form_type').parent('div').parent('div').show();
                    break;
                case 'view':
                    $(lparent_pane).find('#request_form_request_form_type').parent('div').parent('div').show();
                    break;
            }
        }
                
        request_form_components_enable_disable();
        request_form_components_show_hide();
        request_form_data_set();
    }
        
        
            
    
    
    
    /*
    
    var request_form_request_form_type_components_prepare = function(){
        
        
        var request_form_request_form_type_data_set = function(){
            var lparent_pane = request_form_parent_pane;
            var lmethod = $(lparent_pane).find('#request_form_method').val();        
            var lrequest_form_type = $(lparent_pane).find('#request_form_request_form_type').select2('val');
            
            switch(lmethod){
                case 'add':
                    switch(lrequest_form_type){
                        case '1':
                            
                        break;
                    }
                    break;
                case 'edit':
                case 'view':
                    switch(lrequest_form_type){
                        case '1':
                            
                            
                            break;
                    }
                    break;
            }
        }
         
        var request_form_request_form_type_components_show_hide = function(){
            var lparent_pane = request_form_parent_pane;
            var lmethod = $(lparent_pane).find('#request_form_method').val();        

            $(lparent_pane).find('#request_form_div_cancellation_reason').hide();
            $(lparent_pane).find('#request_form_div_request_form_warehouse_from').hide();
            $(lparent_pane).find('#request_form_div_request_form_warehouse_to').hide();
            $(lparent_pane).find('#request_form_div_purchase_invoice').hide();
            $(lparent_pane).find('#request_form_request_form_product_request_form_table').hide();
            $(lparent_pane).find('#request_form_div_request_form_date').hide();
            $(lparent_pane).find('#request_form_div_request_form_status').hide();
            $(lparent_pane).find('#request_form_div_code').hide();
            $(lparent_pane).find('#request_form_div_notes').hide();
            $(lparent_pane).find('#request_form_request_form_add_table').hide();
            $(lparent_pane).find('#request_form_request_form_view_table').hide();
            $(lparent_pane).find('#request_form_div_cancellation_reason').hide();
            
            var lrequest_form_type = $(lparent_pane).find('#request_form_request_form_type').select2('val');
            switch(lmethod){
                case 'add':
                    switch(lrequest_form_type){
                        case'1':
                            
                        break;
                    }
                    break;
                case 'edit':
                case 'view':
                    switch(lrequest_form_type){
                        case'1':
                            $(lparent_pane).find('#request_form_div_request_form_warehouse_to').show();
                            $(lparent_pane).find('#request_form_div_purchase_invoice').show();
                            $(lparent_pane).find('#request_form_request_form_product_request_form_table').show();
                            $(lparent_pane).find('#request_form_div_request_form_date').show();
                            $(lparent_pane).find('#request_form_div_request_form_type').show();
                            $(lparent_pane).find('#request_form_div_request_form_status').show();
                            $(lparent_pane).find('#request_form_div_code').show();
                            $(lparent_pane).find('#request_form_purchase_invoice_detail_outstanding_qty').parent().parent().hide();
                            $(lparent_pane).find('#request_form_request_form_view_table').show();
                            $(lparent_pane).find('#request_form_div_notes').show();
                            if($(lparent_pane).find('#request_form_request_form_status').select2('val') === 'X'){
                                $(lparent_pane).find('#request_form_div_cancellation_reason').show();
                            }
                            else if($(lparent_pane).find('#request_form_request_form_status').select2('val') === 'D'){
                             $(lparent_pane).find('#request_form_print').show();   
                            }
                        break;
                    }
                    break;
            }        
        }
        
        var request_form_request_form_type_components_enable_disable = function(){
            var lparent_pane = request_form_parent_pane;
            var lmethod = $(lparent_pane).find('#request_form_method').val();        

            $(lparent_pane).find('#request_form_request_form_status').select2('disable');
            $(lparent_pane).find('#request_form_warehouse_to').select2('disable');
            $(lparent_pane).find('#request_form_request_form_date').attr('disabled','');
            $(lparent_pane).find('#request_form_purchase_invoice').select2('disable');
            $(lparent_pane).find('#request_form_notes').attr('disabled','');
            $(lparent_pane).find('#request_form_cancellation_reason').attr('disabled','');
            var lrequest_form_type = $(lparent_pane).find('#request_form_request_form_type').select2('val');
            switch(lmethod){
                case 'add':

                    switch(lrequest_form_type){
                        case '1':
                            $(lparent_pane).find('#request_form_request_form_status').select2('enable');
                            $(lparent_pane).find('#request_form_warehouse_to').select2('enable');
                            $(lparent_pane).find('#request_form_request_form_date').removeAttr('disabled');
                            $(lparent_pane).find('#request_form_purchase_invoice').select2('enable');
                            $(lparent_pane).find('#request_form_notes').removeAttr('disabled');
                            break;
                    }
                    break;
                case 'edit':
                    switch(lrequest_form_type){
                        case'1':
                            var lrequest_form_status = $(lparent_pane).find('#request_form_request_form_status').select2('val');
                            if(lrequest_form_status === 'O' || lrequest_form_status === 'D'){
                                $(lparent_pane).find('#request_form_request_form_status').select2('enable');
                                $(lparent_pane).find('#request_form_notes').removeAttr('disabled');
                                $(lparent_pane).find('#request_form_cancellation_reason').removeAttr('disabled');
                                

                            }
                            //if($(lparent_pane).find('#request_form_request_form_status').select2('val') === 'D')
                            //        $(lparent_pane).find('#request_form_print').show();
                            break;
                    }
                    break;
                case 'view':
                    switch(lrequest_form_type){
                        case'1':
                            var lrequest_form_status = $(lparent_pane).find('#request_form_request_form_status').select2('val');
                            if(lrequest_form_status === 'D'){
                                    
                            }
                            break;
                    }
                    break;
            }                
        }
        
        request_form_request_form_type_data_set();
        request_form_request_form_type_components_enable_disable();
        request_form_request_form_type_components_show_hide();
    }
    
    */   
       
    var request_form_after_submit = function(){
        //function that will be executed after submit 
    }
    
    
    
    
    
    

</script>