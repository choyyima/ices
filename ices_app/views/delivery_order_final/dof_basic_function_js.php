<script>

    var dof_parent_pane = $('<?php echo $detail_tab; ?>')[0];
    var dof_ajax_url = null;
    var dof_index_url = null;
    var dof_view_url = null;
    var dof_window_scroll = null;
    var dof_data_support_url = null;
    var dof_common_ajax_listener = null;

    var dof_init = function(){
        var parent_pane = dof_parent_pane;
        dof_ajax_url = '<?php echo $ajax_url ?>';
        dof_index_url = '<?php echo $index_url ?>';
        dof_view_url = '<?php echo $view_url ?>';
        dof_window_scroll = '<?php echo $window_scroll; ?>';
        dof_data_support_url = '<?php echo $data_support_url; ?>';
        dof_common_ajax_listener = '<?php echo $common_ajax_listener; ?>';
        
        dof_rma_extra_param_get = function(){
            //input select detail use this function to get extra parameter for further query
            
        };
        
        
    }
    
    var dof_methods = {
        hide_all:function(){
            var lparent_pane = dof_parent_pane;
            var lc_arr = $(lparent_pane).find('.hide_all').hide();
            $(lparent_pane).find('#dof_print').hide();
            //$(lparent_pane).find('#dof_submit').hide();
        },
        show_hide:function(){
            var lparent_pane = dof_parent_pane;
            var lmethod = $(lparent_pane).find('#dof_method').val();
            var ldof_type = $(lparent_pane).find('#dof_type').val();
            dof_methods.hide_all();
            
            $(lparent_pane).find('#dof_reference').closest('div [class*="form-group"]').show();
            $(lparent_pane).find('#dof_code').closest('div [class*="form-group"]').show();
            $(lparent_pane).find('#dof_store').closest('div [class*="form-group"]').show();
            
            switch(lmethod){
                case 'add':                    
                    $(lparent_pane).find('#dof_print').hide();
                    if(ldof_type!==''){
                        $(lparent_pane).find('#dof_submit').show();
                        $(lparent_pane).find('#dof_code').closest('div [class*="form-group"]').show();
                        $(lparent_pane).find('#dof_store').closest('div [class*="form-group"]').show();
                        $(lparent_pane).find('#dof_delivery_order_final_date').closest('div [class*="form-group"]').show();
                        $(lparent_pane).find('#dof_warehouse_to').closest('div [class*="form-group"]').show();
                        $(lparent_pane).find('#dof_delivery_order_final_status').closest('div [class*="form-group"]').show();
                        
                        switch(ldof_type){
                            case 'sales_invoice':
                                    
                                    break;
                        }
                    }
                    break;
                case 'view':
                    $(lparent_pane).find('#dof_print').show();
                    $(lparent_pane).find('#dof_submit').show();
                    $(lparent_pane).find('#dof_code').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#dof_store').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#dof_delivery_order_final_date').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#dof_warehouse_to').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#dof_delivery_order_final_status').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#dof_product_table').closest('div [class*="form-group"]').show();
                    break;
            }
        },
        disable_all:function(){
            var lparent_pane = dof_parent_pane;
            APP_COMPONENT.disable_all(lparent_pane);
        },
        enable_disable:function(){
            var lparent_pane = dof_parent_pane;
            var lmethod = $(lparent_pane).find('#dof_method').val();    
            var ldof_type = $(lparent_pane).find('#dof_type').val();
            dof_methods.disable_all();
            switch(lmethod){
                case 'add':
                    $(lparent_pane).find('#dof_reference').select2('enable');
                    $(lparent_pane).find('#dof_store').select2('enable');
                    $(lparent_pane).find('#dof_delivery_order_final_date').prop('disabled',false);
                    $(lparent_pane).find('#dof_notes').prop('disabled',false);
                    switch(ldof_type){
                        case 'sales_invoice':
                            $(lparent_pane).find('#dof_warehouse_to').closest('.form-group').find('input').prop('disabled',false);
                            break;
                    }
                    break;
                case 'view':
                    $(lparent_pane).find('#dof_reference').select2('disable');
                    $(lparent_pane).find('#dof_notes').prop('disabled',false);
                    break;
            }
        },
        reset_all:function(){
            var lparent_pane = dof_parent_pane;
            $(lparent_pane).find('#dof_code').val('[AUTO GENERATE]');
            var ldefault_status = null;
            ldefault_status = APP_DATA_TRANSFER.common_ajax_listener('module_status/default_status_get',{module:'delivery_order_final'}).response;

            $(lparent_pane).find('#dof_delivery_order_final_status')
                    .select2('data',{id:ldefault_status.val,text:ldefault_status.label}).change();
            var ldof_status_list = [
                {id:ldefault_status.val,text:ldefault_status.label}
            ];
            
            $(lparent_pane).find('#dof_delivery_order_final_status').
                select2({data:ldof_status_list});
            
            
            var ldefault_store = APP_DATA_TRANSFER.ajaxPOST(APP_PATH.base_url+
                'store/data_support/default_store_get/').response;
            $(lparent_pane).find('#dof_store').select2('data',
                {id:ldefault_store.id,text:ldefault_store.name}
            );
    
            $(lparent_pane).find('#dof_delivery_order_final_date').datetimepicker({
                value:APP_GENERATOR.CURR_DATETIME('minute', 10,'F d, Y H:i')
            });
            
            $(lparent_pane).find('#dof_warehouse_to').select2('data',null);
            
            dof_methods.table.product.reset();
            dof_methods.table.qty.reset();
            
        },
        reserved_qty_calculate:function(lrow){
            var lparent_pane = dof_parent_pane;
            var loutstanding_qty = parseFloat($('#dof_product_table tbody tr:eq('+$(lrow).index()+') [col_name="qty_outstanding"]').text().replace(/[^0-9.]/g,''));
            var lqty_input_arr = $(lrow).find('[col_name="qty"] input');
            var lqty_total = parseFloat('0');
            $.each(lqty_input_arr, function(lidx, linput){
                lqty_total+= parseFloat($(linput).val().replace(/[^0-9.]/g,''));
            });

            var lreserved_qty = loutstanding_qty - lqty_total; 

            $(lparent_pane).find('#dof_product_table tbody tr:eq('+$(lrow).index()+') [col_name="reserved_qty"] span').text(
                APP_CONVERTER.thousand_separator(lreserved_qty)
            ).change();
        },
        table:{
            product:{
                reset:function(){
                    var lparent_pane = dof_parent_pane;
                    $(lparent_pane).find('#dof_product_table tbody').empty();
                    $(lparent_pane).find('#dof_product_table thead').empty();
                },
                header_set:function(){
                    var lparent_pane = dof_parent_pane;
                    var fast_draw = APP_COMPONENT.table_fast_draw;
                    var lthead = $(lparent_pane).find('#dof_product_table thead')[0];
                    var ldof_type = $(lparent_pane).find('#dof_type').val();
                    var lrow = document.createElement('tr');
                    var lmethod = $(lparent_pane).find('#dof_method').val();
                    
                    
                    fast_draw.col_add(lrow,{tag:'th',col_name:'row_num',style:'',val:'<br/><br/>#',type:'text',class:'table-row-num'});
                    fast_draw.col_add(lrow,{tag:'th',col_name:'product-img',style:'',val:'',type:'text',class:'product-img'});
                    fast_draw.col_add(lrow,{tag:'th',col_name:'product',col_style:'width:125px;max-width:125px',val:'<br/>Product',type:'text',class:''});
                    fast_draw.col_add(lrow,{tag:'th',col_name:'unit',col_style:'width:50px',val:'<br/>Unit',type:'text',class:''});
                    if(ldof_type === 'sales_invoice'){
                        fast_draw.col_add(lrow,{tag:'th',col_name:'qty',col_style:'width:75px;text-align:right',val:'Sales<br/>Qty',type:'text',class:''});
                        if(lmethod ==='add'){
                            var lqty_outstanding_td = fast_draw.col_add(lrow,{tag:'th',col_name:'qty_outstanding',col_style:'width:75px;text-align:right',val:'Outstanding<br/>Qty',type:'text',class:''});
                            
                            fast_draw.col_add(lrow,{tag:'th',col_name:'reserved_qty'
                                ,col_style:'text-align:right;width:75px'
                                ,val:'Reserved<br/>Qty',type:'text',
                                });
                        }
                    }
                    lthead.appendChild(lrow);
                    
                },
                load:function(iproduct_arr){
                    var lparent_pane = dof_parent_pane;
                    var ltbody = $(lparent_pane).find('#dof_product_table tbody')[0];
                    var fast_draw = APP_COMPONENT.table_fast_draw;
                    var ldof_type = $(lparent_pane).find('#dof_type').val();
                    var lmethod = $(lparent_pane).find('#dof_method').val();
                    dof_methods.table.product.header_set();
                    
                    $.each(iproduct_arr, function (lidx, lproduct){
                        var lrow = document.createElement('tr');
                        var row_num = lidx;
                        fast_draw.col_add(lrow,{tag:'td',col_name:'row_num',style:'vertical-align:middle',val:row_num+1,type:'text'});
                        fast_draw.col_add(lrow,{tag:'td',col_name:'product_img',style:'vertical-align:middle',val:lproduct.product_img,type:'text'});
                        fast_draw.col_add(lrow,{tag:'td',col_name:'product_id',style:'vertical-align:middle',val:lproduct.product_id,type:'text',visible:false});
                        fast_draw.col_add(lrow,{tag:'td',col_name:'product',style:'vertical-align:middle',val:lproduct.product_code,type:'text'});
                        fast_draw.col_add(lrow,{tag:'td',col_name:'unit_id',style:'vertical-align:middle',val:lproduct.unit_id,type:'text',visible:false});
                        fast_draw.col_add(lrow,{tag:'td',col_name:'unit',style:'vertical-align:middle',val:lproduct.unit_code,type:'text'});
                        if(ldof_type === 'sales_invoice'){
                            fast_draw.col_add(lrow,{tag:'td',col_name:'qty',col_style:'text-align:right',val:lproduct.qty,type:'text'});
                            if(lmethod ==='add'){
                                var lqty_outstanding_td = fast_draw.col_add(lrow,{tag:'td',col_name:'qty_outstanding',col_style:'text-align:right;',val:lproduct.qty_outstanding,type:'text'});
                                if(parseFloat(lproduct.qty_outstanding.toString().replace(/[^0-9.]/g,''))>parseFloat('0')){
                                    $(lqty_outstanding_td).css('font-weight','bold');
                                    $(lqty_outstanding_td).addClass('text-green');
                                }
                                
                                var lreserved_qty_td = fast_draw.col_add(lrow,{tag:'td',col_name:'reserved_qty',style:'vertical-align:middle'
                                    ,val:APP_CONVERTER.thousand_separator(lproduct.qty_outstanding)
                                    ,type:'span'
                                    ,col_style:'text-align:right;min-width:100px;font-weight:bold'});
                                APP_COMPONENT.text.color_non_zero($(lreserved_qty_td).find('span')[0],'red');
                                $(lreserved_qty_td).find('span').change();
                            }
                        }
                        
                        
                        ltbody.appendChild(lrow);
                    });
                    $(ltbody).closest('div').scrollLeft('999');
                }
            },
            qty:{
                reset:function(){
                    var lparent_pane = dof_parent_pane;
                    $(lparent_pane).find('#dof_qty_table tbody').empty();
                    $(lparent_pane).find('#dof_qty_table thead').empty();
                },
                header_set:function(iwarehouse_arr, idelivery_order_arr){
                    var lparent_pane = dof_parent_pane;
                    var lmethod = $(lparent_pane).find('#dof_method').val();
                    var fast_draw = APP_COMPONENT.table_fast_draw;
                    var lthead = $(lparent_pane).find('#dof_qty_table thead')[0];
                    var ldof_type = $(lparent_pane).find('#dof_type').val();
                    var lrow = document.createElement('tr');
                                        
                    fast_draw.col_add(lrow,{tag:'th',col_name:''
                        ,col_style:'vertical-align:center;width:0px',val:'',type:'text'});
                    
                    $.each(iwarehouse_arr, function(warehouse_idx,warehouse){
                        var lcode = '<br/>';
                        $.each(idelivery_order_arr, function (delivery_order_idx, delivery_order){
                            if(delivery_order.warehouse_from.id === warehouse.id){
                                lcode = '<a href="<?php echo get_instance()->config->base_url().'delivery_order/view/'; ?>'+delivery_order.id+'" target="_blank"><strong>'+delivery_order.code+'</strong></a><br/>'+delivery_order.delivery_order_status_text+'';
                            }
                        });
                        fast_draw.col_add(lrow,{tag:'th',col_name:''
                            ,col_style:'text-align:center;width:35px;margin-left:8px',val:'',type:'text',
                            attr:{warehouse_id:warehouse.id.toString()}
                        });
                        fast_draw.col_add(lrow,{tag:'th',col_name:''
                            ,col_style:'text-align:center;'
                            ,val:lcode+'<br/>'+warehouse.name,type:'text',
                            attr:{colspan:'2',warehouse_id:warehouse.id.toString(),warehouse_header:''}});

                    });                    
                    
                    lthead.appendChild(lrow);
                    
                },
                load:function(idelivery_order_arr, iproduct_stock_arr, iref_product_arr){
                    var lparent_pane = dof_parent_pane;
                    var ltbody = $(lparent_pane).find('#dof_qty_table tbody')[0];
                    var fast_draw = APP_COMPONENT.table_fast_draw;
                    var ldof_type = $(lparent_pane).find('#dof_type').val();
                    var lwarehouse_arr = APP_MODULE.warehouse.bos_get();
                    var lmethod = $(lparent_pane).find('#dof_method').val();
                    
                    dof_methods.table.qty.header_set(lwarehouse_arr,idelivery_order_arr);
                    
                    var lproduct_tr_arr = $(lparent_pane).find('#dof_product_table tbody tr');
                    $.each(lproduct_tr_arr,function(lproduct_tr_idx, lproduct_tr){
                        var lrow = document.createElement('tr');
                        var lproduct_id = $(lproduct_tr).find('[col_name="product_id"]').text();
                        var lunit_id = $(lproduct_tr).find('[col_name="unit_id"]').text();
                        fast_draw.col_add(lrow,{tag:'td',col_name:'product_img',style:'vertical-align:middle',val:'<img class="product-img" style="width:0px">',type:'text',attr:{style:'width:0px'}});
                        
                        $.each(lwarehouse_arr, function(warehouse_idx, warehouse){
                            
                            var lmax_qty = 0;
                            var lcheckbox_td = '';
                            
                            if(lmethod ==='add'){
                                lcheckbox_td = fast_draw.col_add(lrow,{tag:'td',col_name:'qty_checkbox',style:'vertical-align:middle'
                                ,val:'<input type="checkbox" >',type:'text',attr:{warehouse_id:warehouse.id.toString()}});
                            }
                            else if (lmethod ==='view'){
                                lcheckbox_td = fast_draw.col_add(lrow,{tag:'td',col_name:'qty_checkbox',style:'vertical-align:middle'
                                ,val:'',type:'text',attr:{warehouse_id:warehouse.id.toString()}});
                            
                            }
                            
                            var lstock_text = '';
                            var lqty_stock = 0;
                            var lproduct_reference_type = '';
                            var lproduct_reference_id = '';
                            $.each(iproduct_stock_arr, function(lproduct_stock_idx, lproduct_stock){
                                if(lproduct_stock.product_id == lproduct_id && 
                                    lproduct_stock.unit_id == lunit_id &&
                                    lproduct_stock.warehouse_id == warehouse.id
                                ){
                                    lqty_stock = lproduct_stock.qty;
                                    lstock_text = '<span>'+lqty_stock+'<br/>(stock)</span>';
                                }
                            });
                            
                            $.each(iref_product_arr, function(lrp_idx, lrp_row){
                            if(lrp_row.product_id == lproduct_id && 
                                    lrp_row.unit_id == lunit_id
                                ){
                                    lproduct_reference_type = lrp_row.reference_type;
                                    lproduct_reference_id = lrp_row.reference_id;
                                }
                            });
                            
                            var lstock_qty_td ='';
                            if(lmethod ==='add'){
                                lstock_qty_td = fast_draw.col_add(lrow,{tag:'td',col_name:'stock_qty'
                                    ,style:'vertical-align:middle;text-align:right'
                                    ,val:lstock_text,type:'text',attr:{warehouse_id:warehouse.id.toString()}}
                                );
                            }

                            var lqty_td = '';
                            
                            fast_draw.col_add(lrow,{tag:'td',col_name:'reference_type',style:'vertical-align:middle'
                                ,val:'<div warehouse_id = "'+warehouse.id+'" product_id="'+lproduct_id+'" unit_id="'+lunit_id+'">'+lproduct_reference_type+'</div>',type:'text',visible:false,});
                            fast_draw.col_add(lrow,{tag:'td',col_name:'reference_id',style:'vertical-align:middle'
                                ,val:'<div warehouse_id = "'+warehouse.id+'" product_id="'+lproduct_id+'" unit_id="'+lunit_id+'">'+lproduct_reference_id+'</div>',type:'text',visible:false,});
                            
                            if(lmethod ==='add'){
                                lqty_td = fast_draw.col_add(lrow,{tag:'td',col_name:'qty',style:'vertical-align:middle'
                                    ,val:'<div><input warehouse_id = "'+warehouse.id+'" product_id="'+lproduct_id+'" unit_id="'+lunit_id+'" class="form-control" style="text-align:right;font-size:12px;font-weight:bold;min-width:100px" disabled></div>'
                                    ,type:'text',attr:{warehouse_id:warehouse.id.toString()}});
                            }
                            else if (lmethod ==='view'){                                
                                lqty_td = fast_draw.col_add(lrow,{tag:'td',col_name:'qty',style:'vertical-align:middle'
                                    ,val:'<div style="text-align:center;"><span warehouse_id = "'+warehouse.id+'" product_id="'+lproduct_id+'" unit_id="'+lunit_id+'" style="font-size:12px;font-weight:bold;min-width:100px">0.00</span></div>'
                                    ,type:'text',attr:{warehouse_id:warehouse.id.toString(),colspan:'2'}});
                            
                            }
                                                        
                            if(lmethod ==='add'){
                                var lqty_input = $(lqty_td).find('input')[0];
                                var loutstanding_qty = parseFloat($(lproduct_tr).find('[col_name="qty_outstanding"]').text().replace(/[^0-9.]/g,''));
                                var lqty_stock_float = parseFloat(lqty_stock.replace(/[^0-9.]/g,''));
                                var lmax_qty = loutstanding_qty<lqty_stock_float? loutstanding_qty : lqty_stock_float;
                                
                                APP_EVENT.init().component_set(lqty_input).type_set('input').numeric_set()
                                    .max_val_set(lmax_qty).min_val_set(0).render();                            
                                
                                $(lqty_input).on('blur',function(){
                                    dof_methods.reserved_qty_calculate($(this).closest('tr')[0]);
                                });
                                
                                APP_COMPONENT.text.color_non_zero(lqty_input,'blue');
                                $(lqty_input).val(0).blur();
                                
                            }
                            else{
                                var lqty_span = $(lqty_td).find('span')[0];
                                APP_COMPONENT.text.color_non_zero(lqty_span,'blue');
                            }      
                        });
                        
                        ltbody.appendChild(lrow);
                        if(lmethod ==='view'){
                            $.each(idelivery_order_arr,function(delivery_order_idx,delivery_order){
                                $.each(delivery_order.product, function(product_idx,product){                                    
                                    var lspan = $(ltbody).find('[col_name="qty"] span[warehouse_id="'+delivery_order.warehouse_from.id+'"][product_id="'+product.product_id+'"][unit_id="'+product.unit_id+'"]')[0];
                                    $(lspan).text(product.qty).change();
                                    var lspan = $(ltbody).find('[col_name="reference_type"] div[warehouse_id="'+delivery_order.warehouse_from.id+'"][product_id="'+product.product_id+'"][unit_id="'+product.unit_id+'"]')[0];
                                    $(lspan).text(product.reference_type).change();
                                    var lspan = $(ltbody).find('[col_name="reference_id"] div[warehouse_id="'+delivery_order.warehouse_from.id+'"][product_id="'+product.product_id+'"][unit_id="'+product.unit_id+'"]')[0];
                                    $(lspan).text(product.reference_id).change();
                                });
                            });
                        }
                        
                        
                    });
                    
                    $('#dof_qty_table tbody [col_name="qty_checkbox"] input[type="checkbox"]').iCheck({checkboxClass: 'icheckbox_minimal'});
                    $('#dof_qty_table tbody [col_name="qty_checkbox"] input[type="checkbox"]').on('ifToggled',function(){
                        var lidx = $(this).closest('td').index();  
                        var lwarehouse_id = $(this).closest('td').attr('warehouse_id');
                        var lqty_input = $(this).closest('tr').find('td[col_name="qty"][warehouse_id="'+lwarehouse_id+'"] input')[0];

                        if($(this).is(':checked')){
                            $(lqty_input).prop('disabled',false);
                        }
                        else{
                            $(lqty_input).prop('disabled',true);
                            $(lqty_input).val('').blur();
                        }
                    
                        setTimeout(function(){$(lqty_input).focus();},200);
                    });                    
                    
                }
            }
        },
        submit:function(){
            var lparent_pane = dof_parent_pane;
            var lajax_url = dof_index_url;
            var lmethod = $(lparent_pane).find('#dof_method').val();
            var json_data = {
                ajax_post:true,
                message_session:true,
            };

            switch(lmethod){
                case 'add':
                    json_data.reference={
                        id:$(lparent_pane).find('#dof_reference').select2('val')
                    };
                    json_data.delivery_order_final = {
                        store_id:$(lparent_pane).find('#dof_store').select2('val'),
                        delivery_order_final_date:$(lparent_pane).find('#dof_delivery_order_final_date').val(),
                        delivery_order_final_type:$(lparent_pane).find('#dof_type').val(),
                    };
                    json_data.warehouse_to ={
                        id: $(lparent_pane).find('#dof_warehouse_to').select2('val'),
                        contact_name: $(lparent_pane).find('#dof_warehouse_to_contact_name').val(),
                        address: $(lparent_pane).find('#dof_warehouse_to_address').val(),
                        phone: $(lparent_pane).find('#dof_warehouse_to_phone').val()
                    }; 
                    json_data.delivery_order=[];
                    
                    var lwarehouse_arr = APP_MODULE.warehouse.bos_get();
                    lwarehouse_arr.push({id:'reserved_qty'});
                    $.each(lwarehouse_arr, function(lwarehouse_idx,lwarehouse){
                        var ltemp_delivery = {product:[],warehouse_from_id:lwarehouse.id};
                        var linput_arr = $(lparent_pane).find('#dof_qty_table tbody tr [col_name="qty"] input[warehouse_id="'+lwarehouse.id+'"]');
                        $.each(linput_arr, function(linput_idx, linput){
                            var lrow = $(linput).closest('tr');
                            var lqty = $(linput).val().replace(/[^0-9.]/g,'');
                            if(parseFloat(lqty)>parseFloat('0')){
                                var lproduct_id = $(linput).attr('product_id');
                                var lunit_id = $(linput).attr('unit_id');
                                var lreference_type = $(lrow).find('[col_name="reference_type"] div[warehouse_id="'+lwarehouse.id+'"][product_id="'+lproduct_id+'"][unit_id="'+lunit_id+'"]').text();
                                var lreference_id = $(lrow).find('[col_name="reference_id"] div[warehouse_id="'+lwarehouse.id+'"][product_id="'+lproduct_id+'"][unit_id="'+lunit_id+'" ]').text();
                                ltemp_delivery.product.push({
                                    reference_type:lreference_type,
                                    reference_id:lreference_id,
                                    product_id:lproduct_id,
                                    unit_id:lunit_id,
                                    qty:lqty,
                                });
                            }
                        });
                        if(ltemp_delivery.product.length>0){
                            json_data.delivery_order.push(ltemp_delivery);
                        }
                    });
                    lajax_url +='delivery_order_final_add/';
                    break;
                case 'view':
                    json_data.delivery_order_final = {
                        delivery_order_final_status:$(lparent_pane).find('#dof_delivery_order_final_status').select2('val'),
                        notes:$(lparent_pane).find('#dof_notes').val(),
                        cancellation_reason:$(lparent_pane).find('#dof_delivery_order_final_cancellation_reason').val()
                    };
                    var dof_id = $(lparent_pane).find('#dof_id').val();
                    var lajax_method = $(lparent_pane).find('#dof_delivery_order_final_status').select2('data').method;
                    lajax_url +=lajax_method+'/'+dof_id;
                    break;
            }
            
            result = APP_DATA_TRANSFER.submit(lajax_url,json_data);

            if(result.success ===1){
                $(lparent_pane).find('#dof_id').val(result.trans_id);
                if(dof_view_url !==''){
                    var url = dof_view_url+result.trans_id;
                    window.location.href=url;
                }
                else{
                    dof_after_submit();
                }
            }
        }
    };
    
    var dof_bind_event = function(){
        var parent_pane = dof_parent_pane;
        
        <?php  ?>        
        $(parent_pane).find('#dof_print').on('click',function(){            
            var ldof_id = $(parent_pane).find('#dof_id').val();
            
            modal_print.init();
            modal_print.menu.add('<?php echo Lang::get("Delivery Order Final"); ?>',
                dof_index_url+'delivery_order_final_print/'+ldof_id);
            modal_print.show();
        })  ;      
        <?php  ?>
        
        $(parent_pane).find('#dof_warehouse_to').on('change',function(){
            var lparent_pane = dof_parent_pane;
            
            if($(this).select2('val')!==''){
                
                var ldata = $(this).select2('data');
                $(lparent_pane).find('#dof_warehouse_to_code')
                        .text(ldata.code);
                $(lparent_pane).find('#dof_warehouse_to_name')
                        .text(ldata.name);
                $(lparent_pane).find('#dof_warehouse_to_type')
                        .text(ldata.type_name);
                $(lparent_pane).find('#dof_warehouse_to_contact_name')
                        .val(ldata.contact_name);
                $(lparent_pane).find('#dof_warehouse_to_address')
                        .val(ldata.address);
                $(lparent_pane).find('#dof_warehouse_to_phone')
                        .val(ldata.phone);
            }
        });
        
        $(parent_pane).find("#dof_reference")
        .on('change', function(){
            var lparent_pane = dof_parent_pane;
            var lmethod = $(lparent_pane).find('#dof_method').val();
            var ldof_type = '';
            var lref_data = $(this).select2('data');            
            if(lref_data === null) lref_data = {id:'',text:'',reference_type:'',reference_type_name:''}
            
            $('#dof_type').val(lref_data.reference_type);            
            ldof_type = $(lparent_pane).find('#dof_type').val();
            
            dof_methods.show_hide();//important for reference switching
            dof_methods.enable_disable();//important for reference switching
            
            $('#dof_reference_detail').find('.extra_info').remove();
            
            if(lmethod === 'add'){
                dof_methods.table.product.reset();
                dof_methods.table.qty.reset();
                
                var lresponse = APP_DATA_TRANSFER.ajaxPOST(dof_data_support_url+'/dependency_data_get',{ref_id:lref_data.id,ref_type:ldof_type}).response;
                var lref = lresponse.ref;
                var lref_product = lresponse.ref_product;

                APP_COMPONENT.reference_detail.extra_info_set($(lparent_pane).find('#dof_reference_detail')[0],lresponse.reference_detail);
                
                $(lparent_pane).find('#dof_warehouse_to')
                .select2({data:lresponse.warehouse_to});
                
                if(lresponse.warehouse_to.length>0){
                    $(lparent_pane).find('#dof_warehouse_to')
                        .select2('data',lresponse.warehouse_to[0]).change();                
                }

                dof_methods.table.product.load(lref_product);
                dof_methods.table.qty.load({},lresponse.product_stock,lresponse.ref_product);
                
                switch(ldof_type){
                    case 'sales_invoice':
                        
                        break;
                }
            }
            
        });
        
        $(parent_pane).find('#dof_submit').off();        
        $(parent_pane).find('#dof_submit')
        .on('click',function(e){
            e.preventDefault();
            var btn = $(this);
            btn.addClass('disabled');
            var lparent_pane = dof_parent_pane;
            modal_confirmation_submit_parent = $(lparent_pane).attr('class').indexOf('modal-body')!==-1?
                $(lparent_pane).closest('.modal'):null;
            $('#modal_confirmation_submit').modal('show');
            $('#modal_confirmation_submit_btn_submit').on('click',function(){
                dof_methods.submit();
                $('#modal_confirmation_submit').modal('hide');

            });
                
            
            $(dof_window_scroll).scrollTop(0);        
            setTimeout(function(){btn.removeClass('disabled')},1000);

            
        });
            
        
    }
    
    var dof_components_prepare = function(){
        

        var dof_data_set = function(){
            var lparent_pane = dof_parent_pane;
            var lmethod = $(lparent_pane).find('#dof_method').val();
            
            switch(lmethod){
                case 'add':
                    dof_methods.reset_all();
                    break;
                case 'view':
                    var ldof_id = $(lparent_pane).find('#dof_id').val();  
                    var ldof_id = $(lparent_pane).find('#dof_id').val();
                    var lajax_url = dof_data_support_url+'delivery_order_final_get';
                    var json_data = {data:ldof_id};
                    var lresponse = APP_DATA_TRANSFER.ajaxPOST(lajax_url,json_data).response;
                    var ldelivery_order_final = lresponse.delivery_order_final;
                    
                    $(lparent_pane).find('#dof_type').val(ldelivery_order_final.delivery_order_final_type);
                    $(lparent_pane).find('#dof_reference').select2(
                        'data',lresponse.reference);                    
                    APP_COMPONENT.reference_detail.extra_info_set($('#dof_reference_detail')[0],lresponse.reference_detail);
                     
                    $(lparent_pane).find('#dof_store').select2('data',{id:ldelivery_order_final.store_id
                        ,text:ldelivery_order_final.store_text});
                    $(lparent_pane).find('#dof_code').val(ldelivery_order_final.code);
                    
                    $(lparent_pane).find('#dof_warehouse_to')
                        .select2('data',{id:ldelivery_order_final.warehouse_to_id,
                            text:ldelivery_order_final.warehouse_to_text}
                        );
                
                    $(lparent_pane).find('#dof_warehouse_to_code')
                            .text(ldelivery_order_final.warehouse_to_code);
                    $(lparent_pane).find('#dof_warehouse_to_name')
                            .text(ldelivery_order_final.warehouse_to_name);
                    $(lparent_pane).find('#dof_warehouse_to_type')
                            .text(ldelivery_order_final.warehouse_to_type_name);
                    $(lparent_pane).find('#dof_warehouse_to_contact_name')
                            .val(ldelivery_order_final.warehouse_to_contact_name);
                    $(lparent_pane).find('#dof_warehouse_to_address')
                            .val(ldelivery_order_final.warehouse_to_address);
                    $(lparent_pane).find('#dof_warehouse_to_phone')
                            .val(ldelivery_order_final.warehouse_to_phone);

                    $(lparent_pane).find('#dof_delivery_order_final_date').datetimepicker({value:ldelivery_order_final.delivery_order_final_date});
                    $(lparent_pane).find('#dof_delivery_order_final_cancellation_reason').val(ldelivery_order_final.cancellation_reason);

                    $(lparent_pane).find('#dof_delivery_order_final_status')
                            .select2('data',{id:ldelivery_order_final.delivery_order_final_status
                                ,text:ldelivery_order_final.delivery_order_final_status_text}).change();
                                  
                    $(lparent_pane).find('#dof_delivery_order_final_status')
                            .select2({data:lresponse.delivery_order_final_status_list});
                    dof_methods.table.product.reset();
                    dof_methods.table.qty.reset();
                    dof_methods.table.product.load(lresponse.product_ordered);
                    dof_methods.table.qty.load(lresponse.delivery_order,{},{});
                    
                    break;
            }
        }
        
        
        dof_methods.enable_disable();
        dof_methods.show_hide();
        dof_data_set();
    }
    
    var dof_after_submit = function(){
        //function that will be executed after submit 
    }
    
   
    
    
    
    
    
</script>