<script>
    var delivery_order_rma_methods={
        show_hide:function(){
            var lparent_pane = delivery_order_parent_pane;
            var lmethod = $(lparent_pane).find('#delivery_order_method').val();

            switch(lmethod){
                case  'add':                    
                    $(lparent_pane).find('#delivery_order_store').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#delivery_order_reference').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#delivery_order_code').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#delivery_order_delivery_order_date').closest('div [class*="form-group"]').show();  
                    $(lparent_pane).find('#delivery_order_warehouse_from').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#delivery_order_warehouse_to').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#delivery_order_notes').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#delivery_order_delivery_order_status').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#delivery_order_rma_add_table').closest('div [class*="form-group"]').show();
                    break;
                case 'view':
                    $(lparent_pane).find('#delivery_order_store').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#delivery_order_reference').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#delivery_order_code').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#delivery_order_delivery_order_date').closest('div [class*="form-group"]').show();  
                    $(lparent_pane).find('#delivery_order_warehouse_from').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#delivery_order_warehouse_to').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#delivery_order_notes').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#delivery_order_delivery_order_status').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#delivery_order_rma_view_table').closest('div [class*="form-group"]').show();
                    
                    break;
            }
        },
        enable_disable: function(){
            delivery_order_methods.disable_all();
            var lparent_pane = delivery_order_parent_pane;
            var lmethod = $(lparent_pane).find('#delivery_order_method').val();
            switch(lmethod){
                case 'add':
                    $(lparent_pane).find('#delivery_order_reference').select2('enable');
                    $(lparent_pane).find('#delivery_order_delivery_order_status').select2('enable');
                    $(lparent_pane).find('#delivery_order_warehouse_from').select2('enable');
                    $(lparent_pane).find('#delivery_order_delivery_order_date').prop('disabled',false);
                    $(lparent_pane).find('#delivery_order_rma').select2('enable');
                    $(lparent_pane).find('#delivery_order_notes').prop('disabled',false);
                    $(lparent_pane).find('#delivery_order_store').select2('enable');
                        $(lparent_pane).find('#delivery_order_warehouse_to_detail').find('input').prop('disabled',false);
                    break;
                case 'view':
                    $(lparent_pane).find('#delivery_order_delivery_order_status').select2('enable');
                    $(lparent_pane).find('#delivery_order_notes').prop('disabled',false);
                    break;
                    
            }
        },
        init_data:function(){
            var lparent_pane = delivery_order_parent_pane;
            var lmethod = $(lparent_pane).find('#delivery_order_method').val();
            switch(lmethod){
                case 'add':                                        
                                        
                    var lresult = APP_DATA_TRANSFER.ajaxPOST(delivery_order_data_support_url+'rma/warehouse_supplier_get/');
                    var lwarehouse_supplier = lresult.response;

                    $(lparent_pane).find('#delivery_order_warehouse_to').select2('data',lwarehouse_supplier).change();
                    
                    $(lparent_pane).find('#delivery_order_rma_add_table>tbody').empty();
                    delivery_order_rma_methods.rma_info_set();
                    delivery_order_rma_methods.warehouse_to_info_set();                    
                    
                    break;
                case 'view':
                    break;
            }
            
        },
        rma_info_set:function(){
            var lparent_pane = delivery_order_parent_pane;
            var lresult = APP_DATA_TRANSFER.ajaxPOST(delivery_order_data_support_url+'rma/rma_detail_get',{data:$(lparent_pane).find('#delivery_order_reference').select2('val')});
            var ldata = lresult.response;
            var lparent_elem = $('#delivery_order_reference_detail').find('li')[0];
            
            var lsupplier = document.createElement('div');
                $(lsupplier).addClass('extra_info');
                lsupplier.innerHTML = '<span><strong>Supplier: </strong><span>'+ldata.supplier+'</span></span>';
                lparent_elem.insertBefore(lsupplier,lparent_elem.children[$(lparent_elem).children().length-1]);
                        
            switch($(lparent_pane).find('#delivery_order_method').val()){
                case 'add':
                    if(typeof ldata.rma_date !=='undefined'){
                        $(lparent_pane).find('#delivery_order_delivery_order_date')
                            .datetimepicker({
                                minDate:ldata.rma_date,
                                minTime:ldata.rma_time,
                                value:ldata.rma_date+' '+ldata.rma_time
                            });
                    }
                    break;
            }
        },
        warehouse_to_info_set:function(){
            var lparent_pane = delivery_order_parent_pane;
            var lrma_id = $(lparent_pane).find('#delivery_order_reference').select2('val');
            var lresult = APP_DATA_TRANSFER.ajaxPOST(delivery_order_data_support_url+'rma/warehouse_to_detail_get',{rma_id:lrma_id});
            var lwarehouse_detail = lresult.response;
            $(lparent_pane).find('#delivery_order_warehouse_to_code').text(lwarehouse_detail.code);
            $(lparent_pane).find('#delivery_order_warehouse_to_name').text(lwarehouse_detail.name);
            $(lparent_pane).find('#delivery_order_warehouse_to_type').text(lwarehouse_detail.type);
            $(lparent_pane).find('#delivery_order_warehouse_to_contact_name').val(lwarehouse_detail.contact_name);
            $(lparent_pane).find('#delivery_order_warehouse_to_address').val(lwarehouse_detail.address);
            $(lparent_pane).find('#delivery_order_warehouse_to_phone').val(lwarehouse_detail.phone);
            
        },
        security_set:function(){
            var lparent_pane = delivery_order_parent_pane;
            var lsubmit_show = true;  
            
            var lstatus_label = delivery_order_methods.status_label_get();
            
            if($(lparent_pane).find('#delivery_order_method').val() === 'add'){
                lstatus_label = 'add';
            }
            
            if(!APP_SECURITY.permission_get('delivery_order','rma_'+lstatus_label).result){
                lsubmit_show = false;
            }
            
            if($(lparent_pane).find('#delivery_order_id').val() !== ''){
                if($.inArray(delivery_order_methods.current_status_get(),['X']) !== -1){
                    lsubmit_show = false;
                }
            }
            
            if(lsubmit_show){
                $(lparent_pane).find('#delivery_order_submit').show();
                $(lparent_pane).find('#delivery_order_cancellation_reason').prop('disabled',false);
                $(lparent_pane).find('#delivery_order_notes').prop('disabled',false);
            }
            else{
                $(lparent_pane).find('#delivery_order_submit').hide();
                $(lparent_pane).find('#delivery_order_cancellation_reason').prop('disabled',true);
                $(lparent_pane).find('#delivery_order_notes').prop('disabled',true);
            }
        },
        load_product_table : function(){
            var lparent_pane = delivery_order_parent_pane;
            var lmethod = $(lparent_pane).find('#delivery_order_method').val();
            switch(lmethod){
                case 'add':
                        var lrma_id = $(lparent_pane).find('#delivery_order_reference').select2('val');
                        var lwarehouse_id = $(lparent_pane).find('#delivery_order_warehouse_from').select2('val');
                        if(lrma_id !== ''){
                            var lajax_url = delivery_order_data_support_url+'rma/rma_product_available_get';
                            var json_data = {rma_id: lrma_id, warehouse_id:lwarehouse_id};
                            var lresult = APP_DATA_TRANSFER.ajaxPOST(lajax_url, json_data);
                            var lproducts = lresult.response;
                            var ltbody = $(lparent_pane).find('#delivery_order_rma_add_table').find('tbody')[0];
                            $(ltbody).empty();
                            var lrow_count = 1;
                            fast_draw = APP_COMPONENT.table_fast_draw;
                            $.each(lproducts,function(key, val){
                                var lrow = document.createElement('tr');
                            
                                fast_draw.col_add(lrow,{tag:'td',col_name:'row_num',style:'',val:lrow_count,type:'text'});                            
                                fast_draw.col_add(lrow,{tag:'td',col_name:'product_img',style:'',val:val.product_img,type:'text'});
                                fast_draw.col_add(lrow,{tag:'td',col_name:'product_id',style:'',val:val.product_id,type:'text',visible:false});
                                fast_draw.col_add(lrow,{tag:'td',col_name:'product_name',style:'',val:val.product_name,type:'text'});
                                fast_draw.col_add(lrow,{tag:'td',col_name:'rma_qty',style:'text-align:right',val:val.rma_qty,type:'text'});
                                fast_draw.col_add(lrow,{tag:'td',col_name:'max_qty',style:'text-align:right',val:val.max_qty,type:'text'});
                                var lcol = fast_draw.col_add(lrow,{tag:'td',col_name:'qty',style:'text-align:right;',val:'',type:'input',class:'form-control'});
                                var lqty_input = $(lcol).find('input')[0];
                                APP_EVENT.init().component_set(lqty_input).type_set('input').numeric_set().min_val_set(0).max_val_set(val.max_qty.replace(/[,]/g,'')).render();
                                
                                fast_draw.col_add(lrow,{tag:'td',col_name:'unit_id',style:'',val:val.unit_id,type:'text',visible:false});
                                fast_draw.col_add(lrow,{tag:'td',col_name:'unit_name',style:'',val:val.unit_name,type:'text'});
                                
                                ltbody.appendChild(lrow);
                                lrow_count+=1;
                                $(lqty_input).blur();

                            });
                        }
                    break;
                case 'edit':
                case 'view':
                    var ltbody = $(lparent_pane).find('#delivery_order_rma_view_table').find('tbody')[0];
                    $(ltbody).empty();
                    lajax_url = delivery_order_data_support_url+'rma/delivery_order_product_get';
                    var ldelivery_order_id = $(lparent_pane).find('#delivery_order_id').val();
                    json_data = {data:ldelivery_order_id};
                    var result = APP_DATA_TRANSFER.ajaxPOST(lajax_url,json_data);
                    ldelivery_order_product = result.response;
                    var lrow_count = 1;
                    var fast_draw = APP_COMPONENT.table_fast_draw;
                    $.each(ldelivery_order_product,function(key, val){
                        var lrow = document.createElement('tr');
                        fast_draw.col_add(lrow,{tag:'td',col_name:'row_num',style:'',val:lrow_count,type:'text'});                            
                        fast_draw.col_add(lrow,{tag:'td',col_name:'product_img',style:'',val:val.product_img,type:'text'});
                        fast_draw.col_add(lrow,{tag:'td',col_name:'product_name',style:'',val:val.product_name,type:'text'});
                        fast_draw.col_add(lrow,{tag:'td',col_name:'qty',col_style:'text-align:right',val:val.qty,type:'text'});
                        fast_draw.col_add(lrow,{tag:'td',col_name:'unit_name',style:'',val:val.unit_name,type:'text'});

                        ltbody.appendChild(lrow);
                        lrow_count+=1;
                    });                    
                    
                    break;
            }
        },
        submit:function(){
            var lparent_pane = delivery_order_parent_pane;
            var lajax_url = delivery_order_index_url;
            var lmethod = $(lparent_pane).find('#delivery_order_method').val();
            var json_data = {
                ajax_post:true,
                message_session:true,
            };

            switch(lmethod){
                case 'add':
                    json_data.delivery_order = {
                        store_id:$(lparent_pane).find('#delivery_order_store').select2('val'),
                        delivery_order_status:$(lparent_pane).find('#delivery_order_delivery_order_status').select2('val'),
                        delivery_order_date:$(lparent_pane).find('#delivery_order_delivery_order_date').val(),
                        notes:$(lparent_pane).find('#delivery_order_notes').val()                                            
                    };
                    json_data.warehouse_from ={
                        warehouse_id: $(lparent_pane).find('#delivery_order_warehouse_from').select2('val')
                    };
                    json_data.warehouse_to ={
                        warehouse_id: $(lparent_pane).find('#delivery_order_warehouse_to').select2('val'),
                        contact_name: $(lparent_pane).find('#delivery_order_warehouse_to_contact_name').val(),
                        address: $(lparent_pane).find('#delivery_order_warehouse_to_address').val(),
                        phone: $(lparent_pane).find('#delivery_order_warehouse_to_phone').val()
                    }; 
                    json_data.rma_delivery_order = {
                        rma_id:$(lparent_pane).find('#delivery_order_reference').select2('val')
                    };
                    json_data.delivery_order_product=[];
                    var lproduct = $(lparent_pane).find('#delivery_order_rma_add_table')[0];
                        $.each($(lproduct).find('tbody').children(),function(key, val){
                            json_data.delivery_order_product.push({
                                product_id:$(val).find('[col_name="product_id"]')[0].innerHTML,
                                unit_id:$(val).find('[col_name="unit_id"]')[0].innerHTML,
                                qty:$(val).find('[col_name="qty"]').find('input').val().replace(/[,]/g,'')
                            });
                        });
                    lajax_url +='rma_add';
                    break;
                case 'view':
                    json_data.delivery_order = {
                        delivery_order_status:$(lparent_pane).find('#delivery_order_delivery_order_status').select2('val'),
                        notes:$(lparent_pane).find('#delivery_order_notes').val(),
                        cancellation_reason:$(lparent_pane).find('#delivery_order_cancellation_reason').val()
                    };
                    var delivery_order_id = $(lparent_pane).find('#delivery_order_id').val();
                    var lajax_method = 'rma_'+delivery_order_methods.status_label_get();
                    lajax_url +=lajax_method+'/'+delivery_order_id;
                    break;
            }

            result = APP_DATA_TRANSFER.submit(lajax_url,json_data);

            if(result.success ===1){
                $(lparent_pane).find('#delivery_order_id').val(result.trans_id);
                if(delivery_order_view_url !==''){
                    var url = delivery_order_view_url+result.trans_id;
                    window.location.href=url;
                }
                else{
                    delivery_order_after_submit();
                }
            }

        }

    }
    
    
</script>