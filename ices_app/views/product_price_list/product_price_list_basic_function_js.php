<script>

    var product_price_list_parent_pane = $('<?php echo $detail_tab; ?>')[0];
    var product_price_list_ajax_url = null;
    var product_price_list_index_url = null;
    var product_price_list_view_url = null;
    var product_price_list_window_scroll = null;
    var product_price_list_data_support_url = null;
    var product_price_list_common_ajax_listener = null;

    var product_price_list_init = function(){
        var parent_pane = product_price_list_parent_pane;
        product_price_list_ajax_url = '<?php echo $ajax_url ?>';
        product_price_list_index_url = '<?php echo $index_url ?>';
        product_price_list_view_url = '<?php echo $view_url ?>';
        product_price_list_window_scroll = '<?php echo $window_scroll; ?>';
        product_price_list_data_support_url = '<?php echo $data_support_url; ?>';
        product_price_list_common_ajax_listener = '<?php echo $common_ajax_listener; ?>';
        
        product_price_list_purchase_invoice_extra_param_get = function(){
            //input select detail use this function to get extra parameter for further query
            
        };
        
        
    }
    
    var product_price_list_methods = {
        status_label_get:function(){
            var parent_pane = product_price_list_parent_pane;
            return $($(parent_pane).find('#product_price_list_product_price_list_status')
                    .select2('data').text).find('strong').length>0?
                    $($(parent_pane).find('#product_price_list_product_price_list_status')
                    .select2('data').text).find('strong')[0].innerHTML.toString().toLowerCase()
                    :$(parent_pane).find('#product_price_list_product_price_list_status')[0].innerHTML;
        },
        current_status_get: function(){
            var lproduct_price_list_id = $('#product_price_list_id').val();
            var lresult = APP_DATA_TRANSFER.ajaxPOST(product_price_list_data_support_url+'product_price_list_current_status/',{data:lproduct_price_list_id});
            var lresponse = lresult.response;
            return lresponse;
        },
        hide_all:function(){
            var lparent_pane = product_price_list_parent_pane;
            var ldivs = $(lparent_pane).find('>div');
            $.each(ldivs, function(key, val){
                $(val).attr('style','display:none');
            });
            $(lparent_pane).find('#product_price_list_download_excel').hide();
            
        },
        disable_all:function(){
            var lparent_pane = product_price_list_parent_pane;
            $(lparent_pane).find('#product_price_list_reference').select2('disable');
            $(lparent_pane).find('#product_price_list_code').prop('disabled',true);
            $(lparent_pane).find('#product_price_list_name').prop('disabled',true);
            $(lparent_pane).find('#product_price_list_product').select2('disable');
            $(lparent_pane).find('#product_price_list_product_price_list_date').prop('disabled',true);
            $(lparent_pane).find('#product_price_list_product_price_list_status').select2('disable');
            $(lparent_pane).find('#product_price_list_notes').prop('disabled',true);
            $(lparent_pane).find('#product_price_list_warehouse_from_detail').find('input').prop('disabled',true);
            APP_COMPONENT.disable_all(lparent_pane);
        },
        security_set:function(){
            var lparent_pane = product_price_list_parent_pane;
            var lsubmit_show = true;  
            var ldownload_excel_show = true;
            
            var lstatus_label = product_price_list_methods.status_label_get();
            
            if($(lparent_pane).find('#product_price_list_method').val() === 'add'){
                lstatus_label = 'add';
            }
            
            if(!APP_SECURITY.permission_get('product_price_list',lstatus_label).result){
                lsubmit_show = false;
            }
            
            if(lstatus_label ==='add'){
                ldownload_excel_show = false;
            }
            
            if(!APP_SECURITY.permission_get('product_price_list','download_excel').result){
                ldownlaod_excel_show = false;
            }
            
            if(ldownload_excel_show){
                $(lparent_pane).find('#product_price_list_download_excel').show();
            }
            else{
                $(lparent_pane).find('#product_price_list_download_excel').hide();
            }
            
            if(lsubmit_show){
                $(lparent_pane).find('#product_price_list_submit').show();
                $(lparent_pane).find('#product_price_list_notes').prop('disabled',false);
            }
            else{
                $(lparent_pane).find('#product_price_list_submit').hide();
                $(lparent_pane).find('#product_price_list_notes').prop('disabled',true);
            }    
        },
        product_add:function(product_id){
            var lajax_url = product_price_list_data_support_url+'product_unit_get';
            var lresult = APP_DATA_TRANSFER.ajaxPOST(lajax_url, {product_id:product_id});
            var lparent_pane = product_price_list_parent_pane;
            if(lresult !== null){
                var lproducts = lresult.response;
                var ltbody = $(lparent_pane).find('#product_price_list_table').find('tbody')[0];
                fast_draw = APP_COMPONENT.table_fast_draw;
                var lrow_count = $(ltbody).children().length+1;
                $.each(lproducts,function(key, val){
                    var lcont = true;
                    $.each($(ltbody).children(),function(key2, val2){
                        if($(val2).find('[col_name="product_id"]').text() === val.product_id 
                            && $(val2).find('[col_name="unit_id"]').text() === val.unit_id){
                            lcont = false;
                        }
                    });
                    if(lcont){
                        var lrow = document.createElement('tr');

                        fast_draw.col_add(lrow,{tag:'td',col_name:'row_num',style:'',val:lrow_count,type:'text'});                            
                        fast_draw.col_add(lrow,{tag:'td',col_name:'product_img',style:'',val:val.product_img,type:'text'});
                        fast_draw.col_add(lrow,{tag:'td',col_name:'product_id',style:'',val:val.product_id,type:'text',visible:false});
                        var lproduct_name = fast_draw.col_add(lrow,{tag:'td',col_name:'product_name',style:'cursor:pointer',val:'<a href="#">'+val.product_text+'</a>',type:'text'});
                        fast_draw.col_add(lrow,{tag:'td',col_name:'unit_id',style:'',val:val.unit_id,type:'text',visible:false});
                        fast_draw.col_add(lrow,{tag:'td',col_name:'unit_name',style:'',val:val.unit_name,type:'text'});
                        fast_draw.col_add(lrow,{tag:'td',col_name:'amount',col_style:'display:none;',val:'[{"min_qty":"0.00","amount":"0.00"}]',type:'text'});
                        var laction = fast_draw.col_add(lrow,{tag:'td',col_name:'action',style:'',val:'',type:'text'});
                        var ltrash = APP_COMPONENT.trash();                    
                        laction.appendChild(ltrash);
                        
                        $(lproduct_name).find('a').on('click',function(){
                            $('#product_price_list_modal_price_list').attr('active_child',$(this).closest('tr').index());
                            var ldata = $(this).closest('tr').find('[col_name="amount"]').text();
                            product_price_list_methods.modal_price_list_load(ldata);                            
                            var lproduct_name = $(this)[0].innerHTML;
                            $('#product_price_list_modal_price_list').find('.modal-title')[0].innerHTML = lproduct_name;
                            $('#product_price_list_modal_price_list').modal('show');
                        });

                        ltbody.appendChild(lrow);
                        lrow_count+=1;
                    }
                });
            }
        },
        modal_price_list_new_row:function(){
            var ltbody = $('#product_price_list_modal_price_list').find('tbody')[0];
            var lrow = document.createElement('tr');
            var lrow_count = $(ltbody).children().length+1;
            
            $.each($(ltbody).children(),function(key, val){
                laction = $(val).find('[col_name="action"]')[0];
                $(laction).empty();
                var ltrash = APP_COMPONENT.trash();                    
                laction.appendChild(ltrash);
            });
            
            fast_draw.col_add(lrow,{tag:'td',col_name:'row_num',style:'',val:lrow_count,type:'text'});
            var lmin_qty = fast_draw.col_add(lrow,{tag:'td',col_name:'min_qty',col_style:'text-align:right',val:'0',type:'input'});
            var lamount = fast_draw.col_add(lrow,{tag:'td',col_name:'amount',col_style:'text-align:right',val:'0',type:'input'});

            var lmin_qty_input = $(lmin_qty).find('input')[0];
            APP_EVENT.init().component_set(lmin_qty_input).type_set('input').numeric_set().min_val_set(0).render();

            var lamount_input = $(lamount).find('input')[0];
            APP_EVENT.init().component_set(lamount_input).type_set('input').numeric_set().min_val_set(0).render();

            var laction = fast_draw.col_add(lrow,{tag:'td',col_name:'action',style:'',val:'',type:'text'});
            var ladd = APP_COMPONENT.new_row();
            laction.appendChild(ladd);
            
            $(lmin_qty_input).blur();
            $(lamount_input).blur();
            
            $(ladd).on('click',function(){
                product_price_list_methods.modal_price_list_new_row();
            });
            
            ltbody.appendChild(lrow);
            setTimeout(function(){$(lmin_qty_input).focus()},100);
        },
        modal_price_list_load:function(data){
            
            var ltbody = $('#product_price_list_modal_price_list_table').find('tbody')[0];
            $(ltbody).empty();
            fast_draw = APP_COMPONENT.table_fast_draw;
            var lrow_count = 1;
            var ldata = JSON.parse(data);
            for(var i = 0;i<ldata.length;i++){
            
                var lrow = document.createElement('tr');
                fast_draw.col_add(lrow,{tag:'td',col_name:'row_num',style:'',val:lrow_count,type:'text'});
                var lmin_qty = fast_draw.col_add(lrow,{tag:'td',col_name:'min_qty',col_style:'text-align:right',val:ldata[i].min_qty,type:'input'});
                var lamount = fast_draw.col_add(lrow,{tag:'td',col_name:'amount',col_style:'text-align:right',val:ldata[i].amount,type:'input'});
                
                var lmin_qty_input = $(lmin_qty).find('input')[0];
                APP_EVENT.init().component_set(lmin_qty_input).type_set('input').numeric_set().min_val_set(0).render();
                
                var lamount_input = $(lamount).find('input')[0];
                APP_EVENT.init().component_set(lamount_input).type_set('input').numeric_set().min_val_set(0).render();
                
                var laction = fast_draw.col_add(lrow,{tag:'td',col_name:'action',style:'',val:'',type:'text'});
                if(i != ldata.length-1){
                    var ltrash = APP_COMPONENT.trash();                    
                    laction.appendChild(ltrash);
                }
                else{
                    var ladd = APP_COMPONENT.new_row();
                    laction.appendChild(ladd);
                    $(ladd).on('click',function(){
                        product_price_list_methods.modal_price_list_new_row();
                    });
                }
                ltbody.appendChild(lrow);
                $(lmin_qty_input).blur();
                $(lamount_input).blur();
                lrow_count +=1;
            
            }
            $('#product_price_list_modal_price_list_btn_ok').off();
            $('#product_price_list_modal_price_list_btn_ok').on('click',function(){
                var ldata = [];
                var ltbody = $('#product_price_list_modal_price_list_table').find('tbody')[0];
                $.each($(ltbody).children(),function(key, val){
                    ldata.push({
                        min_qty:$(val).find('[col_name="min_qty"]>input').val().replace(/[,]/g,'')
                        ,amount:$(val).find('[col_name="amount"]>input').val().replace(/[,]/g,'')
                        });
                });
                var lactive_child = $('#product_price_list_modal_price_list').attr('active_child');
                $($(product_price_list_parent_pane).find('#product_price_list_table')
                    .find('tbody').children()[lactive_child]).find('[col_name="amount"]')
                    .text(JSON.stringify(ldata,null,null))    
                ;
                $('#product_price_list_modal_price_list').modal('hide');
            });
        },
        submit: function(){
            var lparent_pane = product_price_list_parent_pane;
            var lajax_url = product_price_list_index_url;
            var lmethod = $(lparent_pane).find('#product_price_list_method').val();
            var json_data = {
                ajax_post:true,
                message_session:true,
            };

            json_data.product_price_list = {
                code:$(lparent_pane).find('#product_price_list_code').val(),
                name:$(lparent_pane).find('#product_price_list_name').val(),
                product_price_list_status:$(lparent_pane)
                        .find('#product_price_list_product_price_list_status').select2('val'),
                notes:$(lparent_pane).find('#product_price_list_notes').val(),   
                is_delivery:$(lparent_pane).find('#product_price_list_is_delivery').select2('val'),
                delivery_extra_charge:$(lparent_pane).find('#product_price_list_delivery_extra_charge').val().replace(/[^0-9.]/g,''),
                is_discount:$(lparent_pane).find('#product_price_list_is_discount').select2('val'),
                is_refill_sparepart_price_list:$(lparent_pane).find('#product_price_list_is_refill_sparepart_price_list').select2('val'),
            };

            json_data.product_price_list_product=[];
            var lproduct = $(lparent_pane).find('#product_price_list_table')[0];
            $.each($(lproduct).find('tbody').children(),function(key, val){
                var lamount = JSON.parse($(val).find('[col_name="amount"]').text(),null,null);
                var lproduct_id = $(val).find('[col_name="product_id"]').text();
                var lunit_id = $(val).find('[col_name="unit_id"]').text();
                
                for(var i = 0;i<lamount.length;i++){
                    json_data.product_price_list_product.push({
                        product_id:lproduct_id,
                        unit_id:lunit_id,
                        min_qty:lamount[i].min_qty,
                        amount:lamount[i].amount,
                    });
                }
                
            });
            
            
            switch(lmethod){
                case 'add':
                    
                    lajax_url +='add';
                    break;
                case 'view':
                    var product_price_list_id = $(lparent_pane).find('#product_price_list_id').val();
                    var lajax_method = product_price_list_methods.status_label_get();
                    lajax_url +=lajax_method+'/'+product_price_list_id;
                    break;
            }
            result = null;
            
            result = APP_DATA_TRANSFER.submit(lajax_url,json_data);
            
            
            if(result !== null){
                if(result.success ===1){
                    $(lparent_pane).find('#product_price_list_id').val(result.trans_id);
                    if(product_price_list_view_url !==''){
                        var url = product_price_list_view_url+result.trans_id;
                        window.location.href=url;
                    }
                    else{
                        product_price_list_after_submit();
                    }
                }
            }
        }
       
    };
    
    var product_price_list_bind_event = function(){
        var lparent_pane = product_price_list_parent_pane;
        
        $(lparent_pane).find('#product_price_list_product').on('change',function(){
            var lproduct_id = $(this).select2('val');
            product_price_list_methods.product_add(lproduct_id);
            $(this).select2('data',null);
        });
        
        
        $(lparent_pane).find('#product_price_list_product_price_list_status').off();
        $(lparent_pane).find('#product_price_list_product_price_list_status')
        .on('change',function(){
            var lparent_pane = product_price_list_parent_pane;
        });
        
        $(lparent_pane).find('#product_price_list_submit').off();        
        $(lparent_pane).find('#product_price_list_submit')
        .on('click',function(e){
            e.preventDefault();
            var btn = $(this);
            btn.addClass('disabled');
            var lparent_pane = product_price_list_parent_pane;
            modal_confirmation_submit_parent = $(lparent_pane).attr('class').indexOf('modal-body')!==-1?
                $(lparent_pane).closest('.modal'):null;
            //var lreference_type = $(lparent_pane).find('#product_price_list_reference_type').val();
            $('#modal_confirmation_submit').modal('show');
            $('#modal_confirmation_submit_btn_submit').on('click',function(){
                product_price_list_methods.submit();
                $('#modal_confirmation_submit').modal('hide');

            });
            $(product_price_list_window_scroll).scrollTop(0);        
            setTimeout(function(){btn.removeClass('disabled')},1000);

            
        });
            
        $(lparent_pane).find('#product_price_list_download_excel').on('click',function(e){
            e.preventDefault();
            var lid = $(lparent_pane).find('#product_price_list_id').val();
            var btn = $(this);
            window.open(product_price_list_index_url+'download_excel/'+lid);
            setTimeout(function(){btn.removeClass('disabled')},1000); 
        });
        
        
        
        
    }
    
    var product_price_list_components_prepare = function(){
        

        var product_price_list_data_set = function(){
            var lparent_pane = product_price_list_parent_pane;
            var lmethod = $(lparent_pane).find('#product_price_list_method').val();
            
            switch(lmethod){
                case 'add':
                    $(lparent_pane).find('#product_price_list_code').val('');
                    var ldefault_status = null;
                    ldefault_status = APP_DATA_TRANSFER.ajaxPOST(product_price_list_data_support_url+'default_status_get');
                    
                    APP_FORM.status.default_status_set('product_price_list',
                        $(lparent_pane).find('#product_price_list_product_price_list_status')
                    );                    
                    
                    $(lparent_pane).find('#product_price_list_is_delivery')
                            .select2('data',{id:'1',text:'True'});
                    
                    $(lparent_pane).find('#product_price_list_is_discount')
                            .select2('data',{id:'0',text:'False'});

                    
                    break;
                case 'edit':
                case 'view':
                    var lproduct_price_list_id = $(lparent_pane).find('#product_price_list_id').val();
                    var lajax_url = product_price_list_data_support_url+'product_price_list_get/';
                    var result = APP_DATA_TRANSFER.ajaxPOST(lajax_url,{data:lproduct_price_list_id});
                    var lresponse = result.response;
                    var lproduct_price_list = lresponse.product_price_list;
                    
                    
                    $(lparent_pane).find('#product_price_list_code').val(lproduct_price_list.code);
                    $(lparent_pane).find('#product_price_list_name').val(lproduct_price_list.name);
                    $(lparent_pane).find('#product_price_list_notes').val(lproduct_price_list.notes);
                    
                    $(lparent_pane).find('#product_price_list_product_price_list_status')
                                .select2('data',{id:lproduct_price_list.product_price_list_status
                                    ,text:lproduct_price_list.product_price_list_status_text}).change();
                                
                   $(lparent_pane).find('#product_price_list_product_price_list_status')
                            .select2({data:lresponse.product_price_list_status_list});
                    
                    
                    if(lproduct_price_list.is_delivery === '1'){
                        $(lparent_pane).find('#product_price_list_is_delivery').select2('data',{id:'1',text:'True'});
                    }
                    else{
                        $(lparent_pane).find('#product_price_list_is_delivery').select2('data',{id:'0',text:'False'});
                    }
                    
                    $(lparent_pane).find('#product_price_list_delivery_extra_charge').val(APP_CONVERTER.thousand_separator(lproduct_price_list.delivery_extra_charge));
                    
                    
                    if(lproduct_price_list.is_discount === '1'){
                        $(lparent_pane).find('#product_price_list_is_discount').select2('data',{id:'1',text:'True'});
                    }
                    else{
                        $(lparent_pane).find('#product_price_list_is_discount').select2('data',{id:'0',text:'False'});
                    }
                    
                    if(lproduct_price_list.is_refill_sparepart_price_list === '1'){
                        $(lparent_pane).find('#product_price_list_is_refill_sparepart_price_list').select2('data',{id:'1',text:'True'});
                    }
                    else{
                        $(lparent_pane).find('#product_price_list_is_refill_sparepart_price_list').select2('data',{id:'0',text:'False'});
                    }
                    
                    var lproduct_price_list_id = $(lparent_pane).find('#product_price_list_id').val();
                    var lajax_url = product_price_list_data_support_url+'product_price_list_product_get/';
                    var result = APP_DATA_TRANSFER.ajaxPOST(lajax_url,{data:lproduct_price_list_id});
                    var products = result.response;
                    var ltbody = $(lparent_pane).find('#product_price_list_table').find('tbody')[0];
                    $(ltbody).empty();
                    fast_draw = APP_COMPONENT.table_fast_draw;
                    var clean_prods = [];
                    for(var i = 0;i<products.length;i++){
                        if(i === 0){
                            clean_prods.push({
                                product_id:products[i].product_id,
                                product_img:products[i].product_img,
                                product_text:products[i].product_text,
                                unit_id:products[i].unit_id,
                                unit_name:products[i].unit_name,
                                amount:[{min_qty:products[i].min_qty, amount:products[i].amount}],
                            });
                        }
                        else{
                            var idx = clean_prods.length - 1;
                            if(clean_prods[idx].product_id === products[i].product_id
                                && clean_prods[idx].unit_id === products[i].unit_id
                            ){
                                clean_prods[idx].amount.push({
                                    min_qty:products[i].min_qty,
                                    amount:products[i].amount,
                                });
                            }
                            else{
                                clean_prods.push({
                                    product_id:products[i].product_id,
                                    product_img:products[i].product_img,
                                    product_text:products[i].product_text,
                                    unit_id:products[i].unit_id,
                                    unit_name:products[i].unit_name,
                                    amount:[{min_qty:products[i].min_qty, amount:products[i].amount}],    
                                });
                            }
                        }
                    }
                    var lrow_count = 1;
                    for(var i = 0;i<clean_prods.length;i++){                    
                        
                        var val = clean_prods[i];
                        var lrow = document.createElement('tr');

                        fast_draw.col_add(lrow,{tag:'td',col_name:'row_num',style:'',val:lrow_count,type:'text'});                            
                        fast_draw.col_add(lrow,{tag:'td',col_name:'product_img',style:'',val:val.product_img,type:'text'});
                        fast_draw.col_add(lrow,{tag:'td',col_name:'product_id',style:'',val:val.product_id,type:'text',visible:false});
                        var lproduct_name = fast_draw.col_add(lrow,{tag:'td',col_name:'product_name',style:'cursor:pointer',val:'<a href="#">'+val.product_text+'</a>',type:'text'});
                        fast_draw.col_add(lrow,{tag:'td',col_name:'unit_id',style:'',val:val.unit_id,type:'text',visible:false});
                        fast_draw.col_add(lrow,{tag:'td',col_name:'unit_name',style:'',val:val.unit_name,type:'text'});
                        fast_draw.col_add(lrow,{tag:'td',col_name:'amount',col_style:'display:none',val:JSON.stringify(val.amount,null,null),type:'text'});
                        var laction = fast_draw.col_add(lrow,{tag:'td',col_name:'action',style:'',val:'',type:'text'});
                        
                        
                        
                        var ltrash = APP_COMPONENT.trash();                    
                        laction.appendChild(ltrash);

                        $(lproduct_name).find('a').on('click',function(e){
                            e.preventDefault();
                            $('#product_price_list_modal_price_list').attr('active_child',$(this).closest('tr').index());
                            var ldata = $(this).closest('tr').find('[col_name="amount"]').text();
                            product_price_list_methods.modal_price_list_load(ldata);
                            var lproduct_name = $(this)[0].innerHTML;
                            $('#product_price_list_modal_price_list').find('.modal-title')[0].innerHTML = lproduct_name;
                            $('#product_price_list_modal_price_list').modal('show');
                            
                        });

                        ltbody.appendChild(lrow);
                        lrow_count+=1;

                    }

                    break;
            }
        }
        
        var product_price_list_components_enable_disable = function(){
            
            var lparent_pane = product_price_list_parent_pane;
            var lmethod = $(lparent_pane).find('#product_price_list_method').val();    
            product_price_list_methods.disable_all();
            
            $(lparent_pane).find('#product_price_list_code').prop('disabled',false);
            $(lparent_pane).find('#product_price_list_name').prop('disabled',false);
            $(lparent_pane).find('#product_price_list_notes').prop('disabled',false);
            $(lparent_pane).find('#product_price_list_product_price_list_status').select2('enable');
            $(lparent_pane).find('#product_price_list_product').select2('enable');
            
            
        }
        
        var product_price_list_components_show_hide = function(){
            var lparent_pane = product_price_list_parent_pane;
            var lmethod = $(lparent_pane).find('#product_price_list_method').val();
            product_price_list_methods.hide_all();
            
            $(lparent_pane).find('#product_price_list_code').closest('div [class*="form-group"]').show();
            $(lparent_pane).find('#product_price_list_name').closest('div [class*="form-group"]').show();
            $(lparent_pane).find('#product_price_list_notes').closest('div [class*="form-group"]').show();
            $(lparent_pane).find('#product_price_list_product_price_list_status').closest('div [class*="form-group"]').show();
            $(lparent_pane).find('#product_price_list_product').closest('div [class*="form-group"]').show();
            $(lparent_pane).find('#product_price_list_table').closest('.form-group').show();
            $(lparent_pane).find('#product_price_list_is_delivery').closest('div [class*="form-group"]').show();
            $(lparent_pane).find('#product_price_list_delivery_extra_charge').closest('div [class*="form-group"]').show();
            $(lparent_pane).find('#product_price_list_is_discount').closest('div [class*="form-group"]').show();
            $(lparent_pane).find('#product_price_list_is_refill_sparepart_price_list').closest('div [class*="form-group"]').show();
            switch(lmethod){
                case 'add':
                    
                    break;
                case 'view':
                    $(lparent_pane).find('#product_price_list_download_excel').show();
                    break;
            }
            
        }
                
        product_price_list_components_enable_disable();
        product_price_list_components_show_hide();
        product_price_list_data_set();
    }
    
    var product_price_list_after_submit = function(){
        //function that will be executed after submit 
    }
    
   
    
    
    
    

</script>