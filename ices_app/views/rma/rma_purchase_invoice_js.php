<script>
    var rma_purchase_invoice_methods={
        hide_show:function(){
            rma_methods.hide_all();
            var lparent_pane = rma_parent_pane;
            var lmethod = $(lparent_pane).find('#rma_method').val();
            switch(lmethod){
                case  'add':                    
                    $(lparent_pane).find('#rma_store').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#rma_reference').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#rma_code').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#rma_rma_date').closest('div [class*="form-group"]').show();  
                    $(lparent_pane).find('#rma_supplier').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#rma_notes').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#rma_rma_status').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#rma_purchase_invoice_add_table').closest('div [class*="form-group"]').show();
                    break;
                case 'view':
                    $(lparent_pane).find('#rma_store').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#rma_reference').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#rma_code').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#rma_rma_date').closest('div [class*="form-group"]').show();  
                    $(lparent_pane).find('#rma_supplier').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#rma_notes').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#rma_rma_status').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#rma_purchase_invoice_view_table').closest('div [class*="form-group"]').show();
                    
                    break;
            }
        },
        enable_disable: function(){
            rma_methods.disable_all();
            var lparent_pane = rma_parent_pane;
            var lmethod = $(lparent_pane).find('#rma_method').val();
            switch(lmethod){
                case 'add':
                    $(lparent_pane).find('#rma_reference').select2('enable');
                    $(lparent_pane).find('#rma_rma_status').select2('enable');
                    $(lparent_pane).find('#rma_supplier').select2('enable');
                    $(lparent_pane).find('#rma_rma_date').prop('disabled',false);
                    $(lparent_pane).find('#rma_notes').prop('disabled',false);
                    $(lparent_pane).find('#rma_store').select2('enable');
                    break;
                case 'view':
                    $(lparent_pane).find('#rma_rma_status').select2('enable');
                    $(lparent_pane).find('#rma_notes').prop('disabled',false);
                    break;
                    
            }
        },
        purchase_invoice_detail_set:function(){
                var lparent_pane = rma_parent_pane;
                var lresult = APP_DATA_TRANSFER.ajaxPOST(rma_data_support_url+'purchase_invoice/purchase_invoice_detail_get',{data:$(lparent_pane).find('#rma_reference').select2('val')});
                var ldata = lresult.response;
                var lparent_elem = $('#rma_reference_detail').find('li')[0];
                if(typeof ldata.grand_total !=='undefined'){
                    var lgrand_total = document.createElement('div');
                    $(lgrand_total).addClass('extra_info');
                    lgrand_total.innerHTML = '<span><strong>Grand Total: </strong><span>'+ldata.grand_total+'</span></span>';
                    lparent_elem.insertBefore(lgrand_total,lparent_elem.children[$(lparent_elem).children().length-1]);
                }

                switch($(lparent_pane).find('#rma_method').val()){
                    case 'add':

                        $(lparent_pane).find('#rma_rma_date')
                            .datetimepicker({
                                minDate:ldata.purchase_invoice_date,
                                minTime:ldata.purchase_invoice_time,
                                value:ldata.purchase_invoice_date+' '+ldata.purchase_invoice_time
                            });

                        $(lparent_pane).find('#rma_supplier')
                                .select2('data',{id:ldata.supplier_id,text:ldata.supplier_name});


                        break;
                }
        }
        ,init_data:function(){
            var lparent_pane = rma_parent_pane;
            var lmethod = $(lparent_pane).find('#rma_method').val();
            switch(lmethod){
                case 'add':                                        
                    $(lparent_pane).find('#rma_code').val('[AUTO GENERATE]');
                    var ldefault_status = null;
                    ldefault_status = APP_DATA_TRANSFER.ajaxPOST(rma_data_support_url+'purchase_invoice/default_status_get');
                    
                    $(lparent_pane).find('#rma_rma_status')
                            .select2('data',{id:ldefault_status.val,text:ldefault_status.label}).change();
                    var lrma_status_list = [
                        {id:ldefault_status.val,text:ldefault_status.label}//,
                    ]
                    $(lparent_pane).find('#rma_rma_status').select2({data:lrma_status_list});
                    
                    var lresult = APP_DATA_TRANSFER.ajaxPOST("<?php echo get_instance()->config->base_url()?>"+'store/data_support/default_store_get/');
                    var ldefault_store = lresult.response;
                    $(lparent_pane).find('#rma_store').select2('data',{id:ldefault_store.id,text:ldefault_store.name});
                    
                    rma_purchase_invoice_methods.purchase_invoice_detail_set();
                    rma_purchase_invoice_methods.load_product_table();
                    break;
                case 'view':          
                        var lrma_id = $(lparent_pane).find('#rma_id').val();
                        var lajax_url = rma_data_support_url+'purchase_invoice/rma_get';
                        var json_data = {data:lrma_id};
                        var lrma = APP_DATA_TRANSFER.ajaxPOST(lajax_url,json_data);
                        $(lparent_pane).find('#rma_code').val(lrma.code);
                        $(lparent_pane).find('#rma_supplier').select2('data',{id:lrma.supplier_id,text:lrma.supplier_name});
                        $(lparent_pane).find('#rma_rma_date').datetimepicker({value:lrma.rma_date})
                        $(lparent_pane).find('#rma_purchase_invoice').select2('data',{id:lrma.purchase_invoice_id,text:lrma.purchase_invoice_code}).change();
                        $(lparent_pane).find('#rma_notes').val(lrma.notes);
                        $(lparent_pane).find('#rma_cancellation_reason').val(lrma.cancellation_reason);
                        $(lparent_pane).find('#rma_store').select2('data',{id:lrma.store_id,text:lrma.store_name});

                        $(lparent_pane).find('#rma_rma_status')
                                .select2('data',{id:lrma.rma_status
                                    ,text:lrma.rma_status_name}).change();
                        var lrma_status_list = [
                            {id:lrma.rma_status,text:lrma.rma_status_name}
                        ];

                        lajax_url = rma_data_support_url+'purchase_invoice/next_allowed_status';
                        json_data = {data:lrma.rma_status};
                        var lresult = APP_DATA_TRANSFER.ajaxPOST(lajax_url,json_data);
                        var lresponse = lresult.response;
                        $.each(lresponse,function(key, val){
                            lrma_status_list.push({id:val.val,text:val.label});
                        });

                        $(lparent_pane).find('#rma_rma_status')
                                .select2({data:lrma_status_list});
                        rma_purchase_invoice_methods.purchase_invoice_detail_set();
                        rma_purchase_invoice_methods.load_product_table();
                    break;
            }
            
        },
        security_set:function(){
            var lparent_pane = rma_parent_pane;
            var lsubmit_show = true;  
            
            var lstatus_label = rma_methods.status_label_get();
            
            if($(lparent_pane).find('#rma_method').val() === 'add'){
                lstatus_label = 'add';
            }
            
            if(!APP_SECURITY.permission_get('rma','purchase_invoice_'+lstatus_label).result){
                lsubmit_show = false;
            }
            if($(lparent_pane).find('#rma_id').val() !== ''){
                if($.inArray(rma_methods.current_status_get(),['X']) !== -1){
                    lsubmit_show = false;
                }
            }
            
            if(lsubmit_show){
                $(lparent_pane).find('#rma_submit').show();
                $(lparent_pane).find('#rma_cancellation_reason').prop('disabled',false);
                $(lparent_pane).find('#rma_notes').prop('disabled',false);
            }
            else{
                $(lparent_pane).find('#rma_submit').hide();
                rma_methods.disable_all();
                $(lparent_pane).find('#rma_notes').prop('disabled',true);
                $(lparent_pane).find('#rma_cancellation_reason').prop('disabled',true);
            }
        },
        load_product_table : function(){
            var lparent_pane = rma_parent_pane;
            var lmethod = $(lparent_pane).find('#rma_method').val();
            switch(lmethod){
                case 'add':
                        var lpurchase_invoice_id = $(lparent_pane).find('#rma_reference').select2('val');
                        if(lpurchase_invoice_id !== ''){
                            var lajax_url = rma_data_support_url+'purchase_invoice/received_product_available_get';
                            var json_data = {data: lpurchase_invoice_id};
                            var lresult = APP_DATA_TRANSFER.ajaxPOST(lajax_url, json_data);
                            var lproduct = lresult.response;
                            var ltbody = $(lparent_pane).find('#rma_purchase_invoice_add_table').find('tbody')[0];
                            $(ltbody).empty();
                            var lrow_count = 1;
                            fast_draw = APP_COMPONENT.table_fast_draw;
                            $.each(lproduct,function(key, val){
                                var lrow = document.createElement('tr');
                            
                                fast_draw.col_add(lrow,{tag:'td',col_name:'row_num',style:'',val:lrow_count,type:'text'});                            
                                fast_draw.col_add(lrow,{tag:'td',col_name:'product_img',style:'',val:val.product_img,type:'text'});
                                fast_draw.col_add(lrow,{tag:'td',col_name:'product_id',style:'',val:val.product_id,type:'text',visible:false});
                                fast_draw.col_add(lrow,{tag:'td',col_name:'product_name',style:'',val:val.product_name,type:'text'});
                                fast_draw.col_add(lrow,{tag:'td',col_name:'max_qty',style:'text-align:right',val:val.max_qty,type:'text'});
                                var lcol = fast_draw.col_add(lrow,{tag:'td',col_name:'qty',style:'text-align:right;',val:'',type:'input',class:'form-control'});
                                var lqty_input = $(lcol).find('input')[0];
                                APP_EVENT.init().component_set(lqty_input).type_set('input').numeric_set().min_val_set(0).max_val_set(val.max_qty.replace(/[,]/g,'')).render();
                                fast_draw.col_add(lrow,{tag:'td',col_name:'unit_id',style:'',val:val.unit_id,type:'text',visible:false});
                                fast_draw.col_add(lrow,{tag:'td',col_name:'unit_name',style:'',val:val.unit_name,type:'text'});

                                ltbody.appendChild(lrow);
                                lrow_count+=1;
                                $(lqty_input).blur();

                            })
                        }
                    break;
                case 'edit':
                case 'view':
                    var ltbody = $(lparent_pane).find('#rma_purchase_invoice_view_table').find('tbody')[0];
                    $(ltbody).empty();
                    lajax_url = rma_data_support_url+'purchase_invoice/rma_product_get';
                    var lrma_id = $(lparent_pane).find('#rma_id').val();
                    json_data = {data:lrma_id};
                    var result = APP_DATA_TRANSFER.ajaxPOST(lajax_url,json_data);
                    try{
                        lrma_product = result.response;
                        var lrow_count = 1;
                        var fast_draw = APP_COMPONENT.table_fast_draw;
                        $.each(lrma_product,function(key, val){
                            var lrow = document.createElement('tr');
                            
                            fast_draw.col_add(lrow,{tag:'td',col_name:'row_num',style:'',val:lrow_count,type:'text'});                            
                            fast_draw.col_add(lrow,{tag:'td',col_name:'product_img',style:'',val:val.product_img,type:'text'});
                            fast_draw.col_add(lrow,{tag:'td',col_name:'product_name',style:'',val:val.product_name,type:'text'});
                            fast_draw.col_add(lrow,{tag:'td',col_name:'qty',style:'text-align:right',val:val.qty,type:'text'});
                            fast_draw.col_add(lrow,{tag:'td',col_name:'unit_name',style:'',val:val.unit_name,type:'text'});
                            
                            ltbody.appendChild(lrow);
                            lrow_count+=1;
                        });                    
                    }
                    catch(ex){
                        APP_MESSAGE.set('error',ex);
                    }
                    break;
            }
        },
        submit:function(){
            var lparent_pane = rma_parent_pane;
            var lajax_url = rma_index_url;
            var lmethod = $(lparent_pane).find('#rma_method').val();
            var json_data = {
                ajax_post:true,
                message_session:true,
            };

            switch(lmethod){
                case 'add':
                    json_data.supplier = {id:$(lparent_pane).find('#rma_supplier').select2('val')};
                    json_data.rma = {
                        store_id:$(lparent_pane).find('#rma_store').select2('val'),
                        rma_date:$(lparent_pane).find('#rma_rma_date').val(),
                        rma_status:$(lparent_pane).find('#rma_rma_status').select2('val'),
                        cancellation_reason:$(lparent_pane).find('#rma_cancellation_reason').val(),                        
                        notes:$(lparent_pane).find('#rma_notes').val() 
                    };
                    json_data.purchase_invoice = {id:$(lparent_pane).find('#rma_reference').select2('val')};
                    json_data.rma_product = [];
                    var lproduct = $(lparent_pane).find('#rma_purchase_invoice_add_table')[0];
                    $.each($(lproduct).find('tbody').children(),function(key, val){
                        json_data.rma_product.push({
                            product_id:$(val).find('[col_name="product_id"]')[0].innerHTML,
                            unit_id:$(val).find('[col_name="unit_id"]')[0].innerHTML,
                            qty:$(val).find('[col_name="qty"]').find('input').val().replace(/[,]/g,'')
                        });
                    });
                    lajax_url +='purchase_invoice_add';
                    break;
                case 'view':
                    json_data.rma = {
                        rma_status:$(lparent_pane).find('#rma_rma_status').select2('val'),
                        cancellation_reason:$(lparent_pane).find('#rma_cancellation_reason').val(),                        
                        notes:$(lparent_pane).find('#rma_notes').val() 
                    };
                    var rma_id = $(lparent_pane).find('#rma_id').val();
                    var lajax_method = 'purchase_invoice_'+rma_methods.status_label_get();
                    lajax_url +=lajax_method+'/'+rma_id;
                    break;
            }

            result = APP_DATA_TRANSFER.submit(lajax_url,json_data);

            if(result.success ===1){
                $(lparent_pane).find('#rma_id').val(result.trans_id);
                if(rma_view_url !==''){
                    var url = rma_view_url+result.trans_id;
                    window.location.href=url;
                }
                else{
                    rma_after_submit();
                }
            }

        }

    }
    
    
</script>