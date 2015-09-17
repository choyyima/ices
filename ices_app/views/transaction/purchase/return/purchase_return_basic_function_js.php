<script>

    var purchase_return_parent_pane = $('<?php echo $detail_tab; ?>')[0];
    var purchase_return_ajax_url = null;
    var purchase_return_index_url = null;
    var purchase_return_view_url = null;
    var purchase_return_window_scroll = null;
    var purchase_return_data_support_url = null;
    var purchase_return_common_ajax_listener = null;
    
    

    var purchase_return_init = function(){
        var parent_pane = purchase_return_parent_pane;
        purchase_return_ajax_url = '<?php echo $ajax_url ?>';
        purchase_return_index_url = '<?php echo $index_url ?>';
        purchase_return_view_url = '<?php echo $view_url ?>';
        purchase_return_window_scroll = '<?php echo $window_scroll; ?>';
        purchase_return_data_support_url = '<?php echo $data_support_url; ?>';
        purchase_return_common_ajax_listener = '<?php echo $common_ajax_listener; ?>';
        
        purchase_return_purchase_invoice_extra_param_get = function(){
            //input select detail use this function to get extra parameter for further query
            
        };
        
        
    }
    
    var purchase_return_methods = {
        current_status_get: function(){
            var lpurchase_return_id = $('#purchase_return_id').val();
            var lresult = APP_DATA_TRANSFER.ajaxPOST(purchase_return_data_support_url+'purchase_return_transaction/purchase_return_current_status/',{data:lpurchase_return_id});
            return lresult.response;
        },
        purchase_invoice_component_load_set: function(){
            var lparent_pane = purchase_return_parent_pane;
            var lpurchase_invoice_id = $(lparent_pane).find('#purchase_return_purchase_invoice').select2('val');
            var lajax_url = purchase_return_data_support_url+'purchase_return_transaction/purchase_invoice_detail_get/';
            var ljson_data = {data:lpurchase_invoice_id};
            var lresult = APP_DATA_TRANSFER.ajaxPOST(lajax_url, ljson_data);
            var lresponse = lresult.response;
            $(lparent_pane).find('#purchase_return_purchase_return_date')
                .datetimepicker({
                            minDate:lresponse.purchase_invoice.purchase_invoice_date.substring(0,10),
                            minTime:lresponse.purchase_invoice.purchase_invoice_date.substring(11,8),
                            value:lresponse.purchase_invoice.purchase_invoice_date
                        });
            
            
            var ltbody = $(lparent_pane).find('#purchase_return_purchase_return_add_table > tbody')[0];
            $(ltbody).empty();
            var lproduct = lresponse.purchase_invoice_product;
            
            $.each(lproduct, function(key, val){
                var ltr = document.createElement('tr');
                
                var lrow_num_td = document.createElement('td');
                lrow_num_td.innerHTML = $(ltbody).children().length+1;
                
                var lproduct_id_td = document.createElement('td');
                $(lproduct_id_td).attr('col_name','product_id');
                $(lproduct_id_td).attr('style','display:none');
                lproduct_id_td.innerHTML = val.product_id;
                
                var lproduct_img_td = document.createElement('td');
                $(lproduct_img_td).attr('col_name','product_img');
                lproduct_img_td.innerHTML = val.product_img;
                
                var lproduct_name_td = document.createElement('td');
                $(lproduct_name_td).attr('col_name','product_name');                
                lproduct_name_td.innerHTML = val.product_name;
                
                var lreceived_qty_td = document.createElement('td');
                $(lreceived_qty_td).attr('col_name','received_qty');
                $(lreceived_qty_td).attr('style','text-align:center');
                lreceived_qty_td.innerHTML = val.received_qty;
                
                var lqty_td = document.createElement('td');
                $(lqty_td).attr('col_name','qty');
                var lqty_input = document.createElement('input');
                $(lqty_input).attr('class','form-control');
                $(lqty_input).attr('style','text-align:right');
                APP_EVENT.init().component_set(lqty_input)
                        .type_set('input').numeric_set().min_val_set(0).max_val_set(val.received_qty.replace(/[,]/g,'')).render();
                lqty_td.appendChild(lqty_input);
                
                
                var lunit_id_td = document.createElement('td');
                $(lunit_id_td).attr('col_name','unit_id');
                $(lunit_id_td).attr('style','display:none');
                lunit_id_td.innerHTML = val.unit_id;
                
                var lunit_name_td = document.createElement('td');
                $(lunit_name_td).attr('col_name','unit_name');   
                $(lunit_name_td).attr('style','text-align:center');
                lunit_name_td.innerHTML = val.unit_name;
                
                var lprice_td = document.createElement('td');
                $(lprice_td).attr('col_name','price');
                var lprice_input = document.createElement('input');
                $(lprice_input).attr('class','form-control');
                $(lprice_input).attr('style','text-align:right');
                $(lprice_input).val(val.price.replace(/[,]/g,''));
                APP_EVENT.init().component_set(lprice_input)
                        .type_set('input').numeric_set().min_val_set(0).max_val_set(val.price.replace(/[,]/g,'')).render();
                lprice_td.appendChild(lprice_input);
                
                var lsubtotal_td = document.createElement('td');
                $(lsubtotal_td).attr('col_name','subtotal');   
                $(lsubtotal_td).attr('style','text-align:right');
                lsubtotal_td.innerHTML = APP_CONVERTER.thousand_separator(0);
                
                ltr.appendChild(lrow_num_td);
                ltr.appendChild(lproduct_id_td);
                ltr.appendChild(lproduct_img_td);
                ltr.appendChild(lproduct_name_td);
                ltr.appendChild(lreceived_qty_td);
                ltr.appendChild(lqty_td);
                ltr.appendChild(lunit_id_td);
                ltr.appendChild(lunit_name_td);
                ltr.appendChild(lprice_td);
                ltr.appendChild(lsubtotal_td);
                
                ltbody.appendChild(ltr);
                
                var lcalculate_total = function(component){
                    
                    var ltable = $(component).closest('table')[0];
                    var lrow = $(component).closest('tr')[0];
                    var lqty = $(lrow).find('[col_name="qty"]>input').val().replace(/[,]/g,'');
                    var lprice = $(lrow).find('[col_name="price"]>input').val().replace(/[,]/g,'');
                    var subtotal = lqty * lprice;
                    var grandtotal = 0;
                    
                    $(lrow).find('[col_name="subtotal"]')[0].innerHTML=APP_CONVERTER.thousand_separator(subtotal);
        
                    $.each($(ltable).find('[col_name="subtotal"]'), function(key, val){
                        grandtotal += parseFloat(val.innerHTML.replace(/[,]/g,''));
                    });
                    
                    
                    $(ltable).find('[col_name="grand_total"]')[0].innerHTML = APP_CONVERTER.thousand_separator(grandtotal);
                }
                
                $(lqty_input).on('blur',function(){
                    lcalculate_total($(this)[0]);
                });
                
                $(lprice_input).on('blur',function(){
                    lcalculate_total($(this)[0]);
                });
                
                $(lqty_input).blur();
                $(lprice_input).blur();
                
                
               
            });
            
            if($(ltbody).find('tr').length>0){
                $(ltbody).closest('table')
                        .find('[col_name="grand_total"]').closest('tr').show();
            }
        },
        submit:function(){
            var lparent_pane = purchase_return_parent_pane;
            var lajax_url = purchase_return_index_url;
            var lmethod = $(lparent_pane).find('#purchase_return_method').val();
            var json_data = {
                ajax_post:true,
                message_session:true,
                purchase_return_product:[]
            };
            
            switch(lmethod){
                case 'add':
                    json_data.purchase_return ={
                        purchase_return_date:$(lparent_pane).find('#purchase_return_purchase_return_date').val(),
                        notes:$(lparent_pane).find('#purchase_return_notes').val()
                    };
                    json_data.purchase_invoice = {
                        id:$(lparent_pane).find('#purchase_return_purchase_invoice').select2('val')
                    };
                    json_data.purchase_return_product = [];
                    var ltbody = $(lparent_pane).find('#purchase_return_purchase_return_add_table>tbody')[0];
                    $.each($(ltbody).find('tr'),function(key, val){
                        json_data.purchase_return_product.push({
                                product_id: $(val).find('[col_name="product_id"]')[0].innerHTML,
                                unit_id: $(val).find('[col_name="unit_id"]')[0].innerHTML,
                                qty: $(val).find('[col_name="qty"]>input').val(),
                                price: $(val).find('[col_name="price"]>input').val()
                            });
                    });
                    lajax_url +='purchase_return_add';
                    break;
                case 'view':
                    var purchase_return_id = $(lparent_pane).find('#purchase_return_id').val();
                    var lajax_method = purchase_return_methods.status_label_get();
                    lajax_url +='purchase_return_'+lajax_method+'/'+purchase_return_id;
                    break;
            }
            result = null;
            result = APP_DATA_TRANSFER.submit(lajax_url,json_data);
            if(result !== null){
                if(result.success ===1){
                    $(lparent_pane).find('#purchase_return_id').val(result.trans_id);
                    if(purchase_return_view_url !==''){
                        var url = purchase_return_view_url+result.trans_id;
                        window.location.href=url;
                    }
                    else{
                        purchase_return_after_submit();
                    }
                }
            }
        }
    };
    
    var purchase_return_bind_event = function(){
        var parent_pane = purchase_return_parent_pane;
        
        $(parent_pane).find("#purchase_return_purchase_invoice")
        .on('change', function(){
            if($(parent_pane).find('#purchase_return_method').val() =='add'){
                purchase_return_methods.purchase_invoice_component_load_set();
            }
            else{
                
            }
            //purchase_return_load_purchase_return_product_table();
        });
        
        $(parent_pane).find('#purchase_return_purchase_return_status').off();
        $(parent_pane).find('#purchase_return_purchase_return_status')
        .on('change',function(){
            var lparent_pane = purchase_return_parent_pane;
    
            if($(this).select2('val') === 'X'){
                $(lparent_pane).find('#purchase_return_div_cancellation_reason').show();
            }
            else{
                $(lparent_pane).find('#purchase_return_div_cancellation_reason').hide();
            }
            
            var lsubmit_show = true;  
            
            var lstatus_label = APP_TOOLS.status_label_get($(lparent_pane).find('#purchase_return_purchase_return_status')[0]);
            
            if($(lparent_pane).find('#purchase_return_method').val() === 'add'){
                lstatus_label = 'add';
            }
            
            if(!APP_SECURITY.permission_get('purchase_return','purchase_return_'+lstatus_label).result){
                lsubmit_show = false;
            }
            
            if($(lparent_pane).find('#purchase_return_id').val() !== ''){
                if($.inArray(purchase_return_methods.current_status_get(),['X'])!==-1){
                    lsubmit_show = false;
                }
            }
            
            if(lsubmit_show){
                $(lparent_pane).find('#purchase_return_submit').show();
                $(lparent_pane).find('#purchase_return_cancellation_reason').prop('disabled',false);
                $(lparent_pane).find('#purchase_return_notes').prop('disabled',false);
            }
            else{
                $(lparent_pane).find('#purchase_return_submit').hide();
                $(lparent_pane).find('#purchase_return_cancellation_reason').prop('disabled',true);
                $(lparent_pane).find('#purchase_return_notes').prop('disabled',true);
            }
            
            
        });
        
        $(parent_pane).find('#purchase_return_submit').off();        
        $(parent_pane).find('#purchase_return_submit')
        .on('click',function(e){
            e.preventDefault();
            var btn = $(this);
            btn.addClass('disabled');
            purchase_return_methods.submit();
            $(purchase_return_window_scroll).scrollTop(0);        
            setTimeout(function(){btn.removeClass('disabled')},1000);

            
        });
            
        
    }
    
    var purchase_return_components_prepare = function(){
        
        
        var purchase_return_data_set = function(){
            var lparent_pane = purchase_return_parent_pane;
            var lmethod = $(lparent_pane).find('#purchase_return_method').val();
            
            switch(lmethod){
                case 'add':
                    $(lparent_pane).find('#purchase_return_code').val('[AUTO GENERATE]');
                    $(lparent_pane).find('#purchase_return_purchase_invoice').select2('data',{id:'',text:''});
                    var lresult = APP_DATA_TRANSFER.ajaxPOST(purchase_return_data_support_url+'purchase_return_transaction/default_status_get');
                    if(lresult.response !== null){
                        var ldefault_status = lresult.response;
                        $(lparent_pane).find('#purchase_return_purchase_return_status')
                                .select2('data',{id:ldefault_status.val,text:ldefault_status.label}).change();
                        var lpurchase_return_status_list = [
                            {id:ldefault_status.val,text:ldefault_status.label}//,
                        ]
                        $(lparent_pane).find('#purchase_return_purchase_return_status').select2({data:lpurchase_return_status_list});
                    }
                    break;
                case 'view':
                    var lpurchase_return_id = $(lparent_pane).find('#purchase_return_id').val();
                    var lajax_url = purchase_return_ajax_url+'purchase_return_transaction/purchase_return_ajax_get';
                    var json_data = {data:lpurchase_return_id};
                    var lresult = APP_DATA_TRANSFER.ajaxPOST(lajax_url,json_data);
                    var lpurchase_return = lresult.response;
                    $(lparent_pane).find('#purchase_return_code').val(lpurchase_return.purchase_return_code);
                    $(lparent_pane).find('#purchase_return_purchase_return_date').datetimepicker({value:lpurchase_return.purchase_return_date})
                    $(lparent_pane).find('#purchase_return_purchase_invoice').select2('data',{id:lpurchase_return.purchase_invoice_id,text:lpurchase_return.purchase_invoice_code}).change();
                    $(lparent_pane).find('#purchase_return_notes').val(lpurchase_return.notes);
                    $(lparent_pane).find('#purchase_return_cancellation_reason').val(lpurchase_return.cancellation_reason);
                    $(lparent_pane).find('#purchase_return_purchase_return_status')
                            .select2('data',{id:lpurchase_return.purchase_return_status
                                ,text:lpurchase_return.purchase_return_status_name}).change();
                    var lpurchase_return_status_list = [
                        {id:lpurchase_return.purchase_return_status,text:lpurchase_return.purchase_return_status_name}
                    ];
                    
                    lajax_url = purchase_return_data_support_url+'purchase_return_transaction/next_allowed_status';
                    json_data = {data:lpurchase_return.purchase_return_status};
                    var lresult = APP_DATA_TRANSFER.ajaxPOST(lajax_url,json_data);
                    var lresponse = lresult.response;
                    $.each(lresponse,function(key, val){
                        lpurchase_return_status_list.push({id:val.val,text:val.label});
                    });
                    
                    $(lparent_pane).find('#purchase_return_purchase_return_status')
                            .select2({data:lpurchase_return_status_list});
                    
                    break;
            }
        }
        
        var purchase_return_components_enable_disable = function(){
            
            var lparent_pane = purchase_return_parent_pane;
            var lmethod = $(lparent_pane).find('#purchase_return_method').val();    
            
            $(lparent_pane).find('#purchase_return_purchase_return_type').select2('disable');
            $(lparent_pane).find('#purchase_return_purchase_return_status').select2('disable');
            $(lparent_pane).find('#purchase_return_to_warehouse').select2('disable');
            $(lparent_pane).find('#purchase_return_purchase_return_date').attr('disabled','');
            $(lparent_pane).find('#purchase_return_purchase_invoice').select2('disable');
            $(lparent_pane).find('#purchase_return_notes').attr('disable','');
            switch(lmethod){
                case 'add':
                    $(lparent_pane).find('#purchase_return_purchase_return_status').select2('enable');
                    $(lparent_pane).find('#purchase_return_to_warehouse').select2('enable');
                    $(lparent_pane).find('#purchase_return_purchase_return_date').removeAttr('disabled');
                    $(lparent_pane).find('#purchase_return_purchase_invoice').select2('enable');
                    $(lparent_pane).find('#purchase_return_notes').removeAttr('disabled');
                    break;
                case 'view':
                    $(lparent_pane).find('#purchase_return_purchase_return_status').select2('enable');
                    $(lparent_pane).find('#purchase_return_notes').removeAttr('disabled');
                    break;
            }
        }
        
        var purchase_return_components_show_hide = function(){
            var lparent_pane = purchase_return_parent_pane;
            var lmethod = $(lparent_pane).find('#purchase_return_method').val();
            
            $(lparent_pane).find('#purchase_return_code').parent().parent().hide();
            $(lparent_pane).find('#purchase_return_purchase_invoice').parent().parent().hide();
            $(lparent_pane).find('#purchase_return_purchase_return_date').parent().parent().hide();
            $(lparent_pane).find('#purchase_return_purchase_return_status').parent().parent().hide();
            $(lparent_pane).find('#purchase_return_purchase_return_add_table').hide();
            $(lparent_pane).find('#purchase_return_purchase_return_view_table').hide();
            $(lparent_pane).find('#purchase_return_notes').parent().parent().hide();
            $(lparent_pane).find('#purchase_return_purchase_return_add_table').find('[col_name="grand_total"]').closest('tr').hide();
            switch(lmethod){
                case 'add':
                    $(lparent_pane).find('#purchase_return_code').parent().parent().show();
                    $(lparent_pane).find('#purchase_return_purchase_invoice').parent().parent().show();
                    $(lparent_pane).find('#purchase_return_purchase_return_date').parent().parent().show();
                    $(lparent_pane).find('#purchase_return_purchase_return_status').parent().parent().show();
                    $(lparent_pane).find('#purchase_return_purchase_return_add_table').show();
                    $(lparent_pane).find('#purchase_return_notes').parent().parent().show();
                    break;
                case 'view':
                    $(lparent_pane).find('#purchase_return_code').parent().parent().show();
                    $(lparent_pane).find('#purchase_return_purchase_invoice').parent().parent().show();
                    $(lparent_pane).find('#purchase_return_purchase_return_date').parent().parent().show();
                    $(lparent_pane).find('#purchase_return_purchase_return_status').parent().parent().show();
                    $(lparent_pane).find('#purchase_return_purchase_return_view_table').show();
                    $(lparent_pane).find('#purchase_return_notes').parent().parent().show();
                    $(lparent_pane).find('#purchase_return_purchase_invoice_detail_outstanding_qty').closest('div').hide();
                    
                    break;
            }
        }
                
        purchase_return_components_enable_disable();
        purchase_return_components_show_hide();
        purchase_return_data_set();
    }
        
        
            
    
    
    
    <?php /*
    
    var purchase_return_purchase_return_type_components_prepare = function(){
        
        
        var purchase_return_purchase_return_type_data_set = function(){
            var lparent_pane = purchase_return_parent_pane;
            var lmethod = $(lparent_pane).find('#purchase_return_method').val();        
            var lpurchase_return_type = $(lparent_pane).find('#purchase_return_purchase_return_type').select2('val');
            
            switch(lmethod){
                case 'add':
                    switch(lpurchase_return_type){
                        case '1':
                            
                        break;
                    }
                    break;
                case 'edit':
                case 'view':
                    switch(lpurchase_return_type){
                        case '1':
                            
                            
                            break;
                    }
                    break;
            }
        }
         
        var purchase_return_purchase_return_type_components_show_hide = function(){
            var lparent_pane = purchase_return_parent_pane;
            var lmethod = $(lparent_pane).find('#purchase_return_method').val();        

            $(lparent_pane).find('#purchase_return_div_cancellation_reason').hide();
            $(lparent_pane).find('#purchase_return_div_purchase_return_from_warehouse').hide();
            $(lparent_pane).find('#purchase_return_div_purchase_return_to_warehouse').hide();
            $(lparent_pane).find('#purchase_return_div_purchase_invoice').hide();
            $(lparent_pane).find('#purchase_return_purchase_return_product_purchase_return_table').hide();
            $(lparent_pane).find('#purchase_return_div_purchase_return_date').hide();
            $(lparent_pane).find('#purchase_return_div_purchase_return_status').hide();
            $(lparent_pane).find('#purchase_return_div_code').hide();
            $(lparent_pane).find('#purchase_return_div_notes').hide();
            $(lparent_pane).find('#purchase_return_purchase_return_add_table').hide();
            $(lparent_pane).find('#purchase_return_purchase_return_view_table').hide();
            $(lparent_pane).find('#purchase_return_div_cancellation_reason').hide();
            
            var lpurchase_return_type = $(lparent_pane).find('#purchase_return_purchase_return_type').select2('val');
            switch(lmethod){
                case 'add':
                    switch(lpurchase_return_type){
                        case'1':
                            
                        break;
                    }
                    break;
                case 'edit':
                case 'view':
                    switch(lpurchase_return_type){
                        case'1':
                            $(lparent_pane).find('#purchase_return_div_purchase_return_to_warehouse').show();
                            $(lparent_pane).find('#purchase_return_div_purchase_invoice').show();
                            $(lparent_pane).find('#purchase_return_purchase_return_product_purchase_return_table').show();
                            $(lparent_pane).find('#purchase_return_div_purchase_return_date').show();
                            $(lparent_pane).find('#purchase_return_div_purchase_return_type').show();
                            $(lparent_pane).find('#purchase_return_div_purchase_return_status').show();
                            $(lparent_pane).find('#purchase_return_div_code').show();
                            $(lparent_pane).find('#purchase_return_purchase_invoice_detail_outstanding_qty').parent().parent().hide();
                            $(lparent_pane).find('#purchase_return_purchase_return_view_table').show();
                            $(lparent_pane).find('#purchase_return_div_notes').show();
                            if($(lparent_pane).find('#purchase_return_purchase_return_status').select2('val') === 'X'){
                                $(lparent_pane).find('#purchase_return_div_cancellation_reason').show();
                            }
                            else if($(lparent_pane).find('#purchase_return_purchase_return_status').select2('val') === 'D'){
                             $(lparent_pane).find('#purchase_return_print').show();   
                            }
                        break;
                    }
                    break;
            }        
        }
        
        var purchase_return_purchase_return_type_components_enable_disable = function(){
            var lparent_pane = purchase_return_parent_pane;
            var lmethod = $(lparent_pane).find('#purchase_return_method').val();        

            $(lparent_pane).find('#purchase_return_purchase_return_status').select2('disable');
            $(lparent_pane).find('#purchase_return_to_warehouse').select2('disable');
            $(lparent_pane).find('#purchase_return_purchase_return_date').attr('disabled','');
            $(lparent_pane).find('#purchase_return_purchase_invoice').select2('disable');
            $(lparent_pane).find('#purchase_return_notes').attr('disabled','');
            $(lparent_pane).find('#purchase_return_cancellation_reason').attr('disabled','');
            var lpurchase_return_type = $(lparent_pane).find('#purchase_return_purchase_return_type').select2('val');
            switch(lmethod){
                case 'add':

                    switch(lpurchase_return_type){
                        case '1':
                            $(lparent_pane).find('#purchase_return_purchase_return_status').select2('enable');
                            $(lparent_pane).find('#purchase_return_to_warehouse').select2('enable');
                            $(lparent_pane).find('#purchase_return_purchase_return_date').removeAttr('disabled');
                            $(lparent_pane).find('#purchase_return_purchase_invoice').select2('enable');
                            $(lparent_pane).find('#purchase_return_notes').removeAttr('disabled');
                            break;
                    }
                    break;
                case 'edit':
                    switch(lpurchase_return_type){
                        case'1':
                            var lpurchase_return_status = $(lparent_pane).find('#purchase_return_purchase_return_status').select2('val');
                            if(lpurchase_return_status === 'O' || lpurchase_return_status === 'D'){
                                $(lparent_pane).find('#purchase_return_purchase_return_status').select2('enable');
                                $(lparent_pane).find('#purchase_return_notes').removeAttr('disabled');
                                $(lparent_pane).find('#purchase_return_cancellation_reason').removeAttr('disabled');
                                

                            }
                            //if($(lparent_pane).find('#purchase_return_purchase_return_status').select2('val') === 'D')
                            //        $(lparent_pane).find('#purchase_return_print').show();
                            break;
                    }
                    break;
                case 'view':
                    switch(lpurchase_return_type){
                        case'1':
                            var lpurchase_return_status = $(lparent_pane).find('#purchase_return_purchase_return_status').select2('val');
                            if(lpurchase_return_status === 'D'){
                                    
                            }
                            break;
                    }
                    break;
            }                
        }
        
        purchase_return_purchase_return_type_data_set();
        purchase_return_purchase_return_type_components_enable_disable();
        purchase_return_purchase_return_type_components_show_hide();
    }
    
    */ ?>
       
    var purchase_return_after_submit = function(){
        //function that will be executed after submit 
    }
    
    var purchase_return_load_purchase_return_product_table = function(){
        var lparent_pane = purchase_return_parent_pane;
        var lmethod = $(lparent_pane).find('#purchase_return_method').val();
        switch(lmethod){
            case 'add':
                    var lpurchase_invoice_id = $(lparent_pane).find('#purchase_return_purchase_invoice').select2('val');
                    if(lpurchase_invoice_id !== ''){
                        var lajax_url = purchase_return_ajax_url+'purchase_invoice_product_outstanding_get';
                        var json_data = {data: lpurchase_invoice_id};
                        var lproduct = APP_DATA_TRANSFER.ajaxPOST(lajax_url, json_data);
                        var ltbody = $(lparent_pane).find('#purchase_return_purchase_return_add_table').find('tbody')[0];
                        $(ltbody).empty();
                        var lrow_count = 1;
                        $.each(lproduct,function(key, val){
                            var lrow = document.createElement('tr');

                            var lrow_num_td = document.createElement('td');
                            lrow_num_td.innerHTML = lrow_count;
                            lrow_count+=1;
                            
                            var lproduct_img_td = document.createElement('td');
                            $(lproduct_img_td).attr('col_name','product_img');
                            lproduct_img_td.innerHTML = val.product_img;

                            var lproduct_id_td = document.createElement('td');
                            $(lproduct_id_td).hide();
                            $(lproduct_id_td).attr('col_name','product_id');
                            lproduct_id_td.innerHTML = val.product_id;

                            var lproduct_name_td = document.createElement('td');
                            lproduct_name_td.innerHTML = val.product_name;

                            var lordered_qty_td = document.createElement('td');
                            lordered_qty_td.innerHTML = val.ordered_qty;

                            var lmax_qty_td = document.createElement('td');
                            lmax_qty_td.innerHTML = val.max_qty;

                            var lunit_id_td = document.createElement('td');
                            $(lunit_id_td).attr('col_name','unit_id');
                            lunit_id_td.innerHTML = val.unit_id;
                            $(lunit_id_td).hide();

                            var lunit_name_td = document.createElement('td');
                            lunit_name_td.innerHTML = val.unit_name;

                            var lqty_td = document.createElement('td');
                            var lqty_input = document.createElement('input');
                            $(lqty_input).attr('class','form-control');
                            $(lqty_input).attr('col_name','qty');
                            APP_EVENT.init().component_set(lqty_input).type_set('input').numeric_set().min_val_set(0).max_val_set(val.max_qty.replace(/[,]/g,'')).render();
                            lqty_td.appendChild(lqty_input);


                            lrow.appendChild(lrow_num_td);
                            lrow.appendChild(lproduct_img_td);
                            lrow.appendChild(lproduct_id_td);
                            lrow.appendChild(lproduct_name_td);
                            lrow.appendChild(lordered_qty_td);
                            lrow.appendChild(lmax_qty_td);
                            lrow.appendChild(lunit_id_td);
                            lrow.appendChild(lunit_name_td);
                            lrow.appendChild(lqty_td);

                            ltbody.appendChild(lrow);

                            $(lqty_input).blur();
                        })
                    }
                break;
            case 'edit':
            case 'view':
                var ltbody = $(lparent_pane).find('#purchase_return_purchase_return_view_table').find('tbody')[0];
                $(ltbody).empty();
                lajax_url = purchase_return_ajax_url+'purchase_return_purchase_return_product_ajax_get';
                var lpurchase_return_id = $(lparent_pane).find('#purchase_return_id').val();
                json_data = {data:lpurchase_return_id};
                var lpurchase_return_product = APP_DATA_TRANSFER.ajaxPOST(lajax_url,json_data);
                var lrow_count = 1;
                $.each(lpurchase_return_product,function(key, val){
                    var lrow = document.createElement('tr');

                    var lrow_num_td = document.createElement('td');
                    lrow_num_td.innerHTML = lrow_count;
                    lrow_count+=1;

                    var lproduct_img_td = document.createElement('td');
                    $(lproduct_img_td).attr('col_name','product_img');
                    lproduct_img_td.innerHTML = val.product_img;
                        
                    var lproduct_name_td = document.createElement('td');
                    lproduct_name_td.innerHTML = val.product_name;

                    var lqty_td = document.createElement('td');
                    $(lqty_td).attr('style','text-align:right');
                    lqty_td.innerHTML = val.qty;

                    var lunit_name_td = document.createElement('td');
                    lunit_name_td.innerHTML = val.unit_name;

                    lrow.appendChild(lrow_num_td);
                    lrow.appendChild(lproduct_img_td);
                    lrow.appendChild(lproduct_name_td);
                    lrow.appendChild(lqty_td);
                    lrow.appendChild(lunit_name_td);
                    ltbody.appendChild(lrow);


                });                    
                            
                break;
        }
    }

    
    
    
    

</script>