<script>
    var receive_product_rma_methods={
        hide_show:function(){
            receive_product_methods.hide_all();
            var lparent_pane = receive_product_parent_pane;
            var lmethod = $(lparent_pane).find('#receive_product_method').val();

            switch(lmethod){
                case  'add':                    
                    $(lparent_pane).find('#receive_product_store').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#receive_product_reference').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#receive_product_code').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#receive_product_receive_product_date').closest('div [class*="form-group"]').show();  
                    $(lparent_pane).find('#receive_product_warehouse_from').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#receive_product_warehouse_to').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#receive_product_notes').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#receive_product_receive_product_status').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#receive_product_rma_add_table').closest('div [class*="form-group"]').show();
                    break;
                case 'view':
                    $(lparent_pane).find('#receive_product_store').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#receive_product_reference').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#receive_product_code').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#receive_product_receive_product_date').closest('div [class*="form-group"]').show();  
                    $(lparent_pane).find('#receive_product_warehouse_from').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#receive_product_warehouse_to').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#receive_product_notes').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#receive_product_receive_product_status').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#receive_product_rma_view_table').closest('div [class*="form-group"]').show();
                    
                    break;
            }
        },
        enable_disable: function(){
            receive_product_methods.disable_all();
            var lparent_pane = receive_product_parent_pane;
            var lmethod = $(lparent_pane).find('#receive_product_method').val();
            switch(lmethod){
                case 'add':
                    $(lparent_pane).find('#receive_product_reference').select2('enable');
                    $(lparent_pane).find('#receive_product_receive_product_status').select2('enable');
                    $(lparent_pane).find('#receive_product_warehouse_to').select2('enable');
                    $(lparent_pane).find('#receive_product_receive_product_date').prop('disabled',false);
                    $(lparent_pane).find('#receive_product_rma').select2('enable');
                    $(lparent_pane).find('#receive_product_notes').prop('disabled',false);
                    $(lparent_pane).find('#receive_product_store').select2('enable');
                    $(lparent_pane).find('#receive_product_warehouse_from_detail').find('input').prop('disabled',false);
                    break;
                case 'view':
                    $(lparent_pane).find('#receive_product_receive_product_status').select2('enable');
                    $(lparent_pane).find('#receive_product_notes').prop('disabled',false);
                    break;
                    
            }
        },
        rma_info_set:function(){
            var lparent_pane = receive_product_parent_pane;
            var lresult = APP_DATA_TRANSFER.ajaxPOST(receive_product_data_support_url+'rma/rma_detail_get',{data:$(lparent_pane).find('#receive_product_reference').select2('val')});
            var ldata = lresult.response;
            var lparent_elem = $('#receive_product_reference_detail').find('li')[0];
            
            var lsupplier = document.createElement('div');
            $(lsupplier).addClass('extra_info');
            lsupplier.innerHTML = '<span><strong>Supplier: </strong><span>'+ldata.supplier+'</span></span>';
            lparent_elem.insertBefore(lsupplier,lparent_elem.children[$(lparent_elem).children().length-1]);
            
            switch($(lparent_pane).find('#receive_product_method').val()){
                case 'add':
                    if(typeof ldata.rma_date !=='undefined'){
                        $(lparent_pane).find('#receive_product_receive_product_date')
                            .datetimepicker({
                                minDate:ldata.rma_date,
                                minTime:ldata.rma_time,
                                value:ldata.rma_date+' '+ldata.rma_time
                            });
                    }
                    break;
            }
        },
        warehouse_from_info_set:function(){
            var lparent_pane = receive_product_parent_pane;
            var lrma_id = $(lparent_pane).find('#receive_product_reference').select2('val');
            var lresult = APP_DATA_TRANSFER.ajaxPOST(receive_product_data_support_url+'rma/warehouse_from_detail_get',
            {rma_id:lrma_id});
            var lwarehouse_detail = lresult.response;            
            $(lparent_pane).find('#receive_product_warehouse_from_code').text(lwarehouse_detail.code);
            $(lparent_pane).find('#receive_product_warehouse_from_name').text(lwarehouse_detail.name);
            $(lparent_pane).find('#receive_product_warehouse_from_type').text(lwarehouse_detail.type);
            $(lparent_pane).find('#receive_product_warehouse_from_reference_code').val('');
            $(lparent_pane).find('#receive_product_warehouse_from_contact_name').val(lwarehouse_detail.contact_name);
            $(lparent_pane).find('#receive_product_warehouse_from_address').val(lwarehouse_detail.address);
            $(lparent_pane).find('#receive_product_warehouse_from_phone').val(lwarehouse_detail.phone);
              
        },
        init_data:function(){
            var lparent_pane = receive_product_parent_pane;
            var lmethod = $(lparent_pane).find('#receive_product_method').val();
            switch(lmethod){
                case 'add':                                        
                    $(lparent_pane).find('#receive_product_code').val('[AUTO GENERATE]');
                    var ldefault_status = null;
                    ldefault_status = APP_DATA_TRANSFER.ajaxPOST(receive_product_data_support_url+'rma/default_status_get');
                    
                    $(lparent_pane).find('#receive_product_receive_product_status')
                            .select2('data',{id:ldefault_status.val,text:ldefault_status.label}).change();
                    var lreceive_product_status_list = [
                        {id:ldefault_status.val,text:ldefault_status.label}//,
                    ]
                    $(lparent_pane).find('#receive_product_receive_product_status').select2({data:lreceive_product_status_list});
                    $(lparent_pane).find('#receive_product_warehouse_to').select2('data',null);
                    
                    var lresult = APP_DATA_TRANSFER.ajaxPOST(receive_product_data_support_url+'rma/warehouse_supplier_get/');
                    var lwarehouse_supplier = lresult.response;
                    $(lparent_pane).find('#receive_product_warehouse_from').select2('data',lwarehouse_supplier).change();
                    
                    var lresult = APP_DATA_TRANSFER.ajaxPOST('<?php echo get_instance()->config->base_url() ?>'+'store/data_support/default_store_get/');
                    var ldefault_store = lresult.response;
                    $(lparent_pane).find('#receive_product_store').select2('data',
                            {id:ldefault_store.id,text:ldefault_store.name}
                        );
                    
                   
                    receive_product_rma_methods.rma_info_set();
                    receive_product_rma_methods.warehouse_from_info_set();
                    receive_product_rma_methods.load_product_table();
                    break;
                case 'view':          
                    try{
                        var lreceive_product_id = $(lparent_pane).find('#receive_product_id').val();
                        var lajax_url = receive_product_data_support_url+'rma/receive_product_get';
                        var json_data = {data:lreceive_product_id};
                        var lreceive_product = APP_DATA_TRANSFER.ajaxPOST(lajax_url,json_data);
                        $(lparent_pane).find('#receive_product_code').val(lreceive_product.code);
                        $(lparent_pane).find('#receive_product_warehouse_to').select2('data',{id:lreceive_product.warehouse_to_id,text:lreceive_product.warehouse_to_name});
                        $(lparent_pane).find('#receive_product_warehouse_from').select2('data',{id:lreceive_product.warehouse_from_id,text:lreceive_product.warehouse_from_name}).change();
                        $(lparent_pane).find('#receive_product_receive_product_date').datetimepicker({value:lreceive_product.receive_product_date})
                        $(lparent_pane).find('#receive_product_rma').select2('data',{id:lreceive_product.rma_id,text:lreceive_product.rma_code}).change();
                        $(lparent_pane).find('#receive_product_notes').val(lreceive_product.notes);
                        $(lparent_pane).find('#receive_product_cancellation_reason').val(lreceive_product.cancellation_reason);
                        $(lparent_pane).find('#receive_product_store').select2('data',{id:lreceive_product.store_id,text:lreceive_product.store_name});

                        $(lparent_pane).find('#receive_product_warehouse_from_code')
                            .text(lreceive_product.warehouse_from_code);
                        $(lparent_pane).find('#receive_product_warehouse_from_name')
                                .text(lreceive_product.warehouse_from_name);
                        $(lparent_pane).find('#receive_product_warehouse_from_type')
                                .text(lreceive_product.warehouse_from_type);
                        $(lparent_pane).find('#receive_product_warehouse_from_reference_code')
                                .val(lreceive_product.warehouse_from_reference_code);
                        $(lparent_pane).find('#receive_product_warehouse_from_contact_name')
                                .val(lreceive_product.warehouse_from_contact_name);
                        $(lparent_pane).find('#receive_product_warehouse_from_address')
                                .val(lreceive_product.warehouse_from_address);
                        $(lparent_pane).find('#receive_product_warehouse_from_phone')
                            .val(lreceive_product.warehouse_from_phone);

                        $(lparent_pane).find('#receive_product_receive_product_status')
                                .select2('data',{id:lreceive_product.receive_product_status
                                    ,text:lreceive_product.receive_product_status_name}).change();
                        var lreceive_product_status_list = [
                            {id:lreceive_product.receive_product_status,text:lreceive_product.receive_product_status_name}
                        ];

                        lajax_url = receive_product_data_support_url+'rma/next_allowed_status';
                        json_data = {data:lreceive_product.receive_product_status};
                        var lresult = APP_DATA_TRANSFER.ajaxPOST(lajax_url,json_data);
                        var lresponse = lresult.response;
                        $.each(lresponse,function(key, val){
                            lreceive_product_status_list.push({id:val.val,text:val.label});
                        });

                        $(lparent_pane).find('#receive_product_receive_product_status')
                                .select2({data:lreceive_product_status_list});
                        receive_product_rma_methods.rma_info_set();
                        receive_product_rma_methods.load_product_table();

                    }
                    catch(ex){APP_MESSAGE.set('error',ex);}
                    break;
            }
            
        },
        security_set:function(){
            var lparent_pane = receive_product_parent_pane;
            var lsubmit_show = true;  
            
            var lstatus_label = receive_product_methods.status_label_get();
            
            if($(lparent_pane).find('#receive_product_method').val() === 'add'){
                lstatus_label = 'add';
            }
            
            if(!APP_SECURITY.permission_get('receive_product','rma_'+lstatus_label).result){
                lsubmit_show = false;
            }
            if($(lparent_pane).find('#receive_product_id').val() !== ''){
                if($.inArray(receive_product_methods.current_status_get(),['X']) !== -1){
                    lsubmit_show = false;
                }
            }
            
            if(lsubmit_show){
                $(lparent_pane).find('#receive_product_submit').show();
                $(lparent_pane).find('#receive_product_cancellation_reason').prop('disabled',false);
                $(lparent_pane).find('#receive_product_notes').prop('disabled',false);
            }
            else{
                $(lparent_pane).find('#receive_product_submit').hide();
                $(lparent_pane).find('#receive_product_cancellation_reason').prop('disabled',true);
                $(lparent_pane).find('#receive_product_notes').prop('disabled',true);
            }
        },
        load_product_table : function(){
            var lparent_pane = receive_product_parent_pane;
            var lmethod = $(lparent_pane).find('#receive_product_method').val();
            switch(lmethod){
                case 'add':
                        var lrma_id = $(lparent_pane).find('#receive_product_reference').select2('val');
                        if(lrma_id !== ''){
                            var lajax_url = receive_product_data_support_url+'rma/rma_product_outstanding_get';
                            var json_data = {data: lrma_id};
                            var lresult = APP_DATA_TRANSFER.ajaxPOST(lajax_url, json_data);
                            var lproduct = lresult.response;
                            var ltbody = $(lparent_pane).find('#receive_product_rma_add_table').find('tbody')[0];
                            $(ltbody).empty();
                            var lrow_count = 1;
                            fast_draw = APP_COMPONENT.table_fast_draw;
                            $.each(lproduct,function(key, val){
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

                            })
                        }
                    break;
                case 'edit':
                case 'view':
                    var ltbody = $(lparent_pane).find('#receive_product_rma_view_table').find('tbody')[0];
                    $(ltbody).empty();
                    lajax_url = receive_product_data_support_url+'rma/receive_product_product_get';
                    var lreceive_product_id = $(lparent_pane).find('#receive_product_id').val();
                    json_data = {data:lreceive_product_id};
                    var result = APP_DATA_TRANSFER.ajaxPOST(lajax_url,json_data);
                    lreceive_product_product = result.response;
                    var lrow_count = 1;
                    var fast_draw = APP_COMPONENT.table_fast_draw;
                    $.each(lreceive_product_product,function(key, val){
                        var lrow = document.createElement('tr');

                        fast_draw.col_add(lrow,{tag:'td',col_name:'row_num',style:'',val:lrow_count,type:'text'});                            
                        fast_draw.col_add(lrow,{tag:'td',col_name:'product_img',style:'',val:val.product_img,type:'text'});
                        fast_draw.col_add(lrow,{tag:'td',col_name:'product_name',style:'',val:val.product_name,type:'text'});
                        fast_draw.col_add(lrow,{tag:'td',col_name:'qty',style:'text-align:right',val:val.qty,type:'text'});
                        fast_draw.col_add(lrow,{tag:'td',col_name:'unit_name',style:'',val:val.unit_name,type:'text'});

                        ltbody.appendChild(lrow);
                        lrow_count+=1;
                    });                    

                    break;
            }
        },
        submit:function(){
            var lparent_pane = receive_product_parent_pane;
            var lajax_url = receive_product_index_url;
            var lmethod = $(lparent_pane).find('#receive_product_method').val();
            var json_data = {
                ajax_post:true,
                message_session:true,
            };

            switch(lmethod){
                case 'add':
                    json_data.receive_product = {
                        store_id:$(lparent_pane).find('#receive_product_store').select2('val'),
                        receive_product_status:$(lparent_pane).find('#receive_product_receive_product_status').select2('val'),
                        receive_product_date:$(lparent_pane).find('#receive_product_receive_product_date').val(),
                        notes:$(lparent_pane).find('#receive_product_notes').val()                                            
                    };
                    json_data.warehouse_from = {
                        reference_code: $(lparent_pane).find('#receive_product_warehouse_from_reference_code').val(),
                        contact_name: $(lparent_pane).find('#receive_product_warehouse_from_contact_name').val(),
                        address: $(lparent_pane).find('#receive_product_warehouse_from_address').val(),
                        phone: $(lparent_pane).find('#receive_product_warehouse_from_phone').val(),
                    };
                    json_data.warehouse_to ={
                        warehouse_id: $(lparent_pane).find('#receive_product_warehouse_to').select2('val')
                    }; 
                    json_data.rma_receive_product = {
                        rma_id:$(lparent_pane).find('#receive_product_reference').select2('val')
                    };
                    json_data.receive_product_product=[];
                    var lproduct = $(lparent_pane).find('#receive_product_rma_add_table')[0];
                        $.each($(lproduct).find('tbody').children(),function(key, val){
                            json_data.receive_product_product.push({
                                product_id:$(val).find('[col_name="product_id"]')[0].innerHTML,
                                unit_id:$(val).find('[col_name="unit_id"]')[0].innerHTML,
                                qty:$(val).find('[col_name="qty"]').find('input').val().replace(/[,]/g,'')
                            });
                        });
                    lajax_url +='rma_add';
                    break;
                case 'view':
                    json_data.receive_product = {
                        receive_product_status:$(lparent_pane).find('#receive_product_receive_product_status').select2('val'),
                        notes:$(lparent_pane).find('#receive_product_notes').val(),
                        cancellation_reason:$(lparent_pane).find('#receive_product_cancellation_reason').val()
                    };
                    var receive_product_id = $(lparent_pane).find('#receive_product_id').val();
                    var lajax_method = 'rma_'+receive_product_methods.status_label_get();
                    lajax_url +=lajax_method+'/'+receive_product_id;
                    break;
            }

            result = APP_DATA_TRANSFER.submit(lajax_url,json_data);

            if(result.success ===1){
                $(lparent_pane).find('#receive_product_id').val(result.trans_id);
                if(receive_product_view_url !==''){
                    var url = receive_product_view_url+result.trans_id;
                    window.location.href=url;
                }
                else{
                    receive_product_after_submit();
                }
            }

        }

    }
    
    
</script>