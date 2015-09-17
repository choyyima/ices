<script>

    var intake_final_parent_pane = $('<?php echo $detail_tab; ?>')[0];
    var intake_final_ajax_url = null;
    var intake_final_index_url = null;
    var intake_final_view_url = null;
    var intake_final_window_scroll = null;
    var intake_final_data_support_url = null;
    var intake_final_common_ajax_listener = null;

    var intake_final_init = function(){
        var parent_pane = intake_final_parent_pane;
        intake_final_ajax_url = '<?php echo $ajax_url ?>';
        intake_final_index_url = '<?php echo $index_url ?>';
        intake_final_view_url = '<?php echo $view_url ?>';
        intake_final_window_scroll = '<?php echo $window_scroll; ?>';
        intake_final_data_support_url = '<?php echo $data_support_url; ?>';
        intake_final_common_ajax_listener = '<?php echo $common_ajax_listener; ?>';
        
        intake_final_rma_extra_param_get = function(){
            //input select detail use this function to get extra parameter for further query
           
           
        };
        
        
    }
    
    var intake_final_methods = {
        hide_all:function(){
            var lparent_pane = intake_final_parent_pane;
            var lc_arr = $(lparent_pane).find('.hide_all').hide();
            $(lparent_pane).find('#intake_final_print').hide();
            //$(lparent_pane).find('#intake_final_submit').hide();
        },
        show_hide:function(){
            var lparent_pane = intake_final_parent_pane;
            var lmethod = $(lparent_pane).find('#intake_final_method').val();
            var lintake_type = $(lparent_pane).find('#intake_final_type').val();
            intake_final_methods.hide_all();
            
            $(lparent_pane).find('#intake_final_reference').closest('div [class*="form-group"]').show();
            $(lparent_pane).find('#intake_final_code').closest('div [class*="form-group"]').show();
            $(lparent_pane).find('#intake_final_store').closest('div [class*="form-group"]').show();
            
            
            switch(lmethod){
                case 'add':                    
                    $(lparent_pane).find('#intake_final_print').hide();
                    if(lintake_type!==''){
                        $(lparent_pane).find('#intake_final_submit').show();
                        $(lparent_pane).find('#intake_final_code').closest('div [class*="form-group"]').show();
                        $(lparent_pane).find('#intake_final_store').closest('div [class*="form-group"]').show();
                        $(lparent_pane).find('#intake_final_intake_final_date').closest('div [class*="form-group"]').show();
                        $(lparent_pane).find('#intake_final_warehouse_to').closest('div [class*="form-group"]').show();
                        $(lparent_pane).find('#intake_final_intake_final_status').closest('div [class*="form-group"]').show();
                        
                        switch(lintake_type){
                            case 'sales_invoice':
                                    
                                    break;
                        }
                    }
                    break;
                case 'view':
                    $(lparent_pane).find('#intake_final_print').show();
                    $(lparent_pane).find('#intake_final_submit').show();
                    $(lparent_pane).find('#intake_final_code').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#intake_final_store').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#intake_final_intake_final_date').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#intake_final_warehouse_to').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#intake_final_intake_final_status').closest('div [class*="form-group"]').show();
                    $(lparent_pane).find('#intake_final_product_table').closest('div [class*="form-group"]').show();
                    break;
            }
        },
        disable_all:function(){
            var lparent_pane = intake_final_parent_pane;
            APP_COMPONENT.disable_all(lparent_pane);
        },
        enable_disable:function(){
            var lparent_pane = intake_final_parent_pane;
            var lmethod = $(lparent_pane).find('#intake_final_method').val();    
            var lintake_type = $(lparent_pane).find('#intake_final_type').val();
            intake_final_methods.disable_all();
            switch(lmethod){
                case 'add':
                    $(lparent_pane).find('#intake_final_reference').select2('enable');
                    $(lparent_pane).find('#intake_final_store').select2('enable');
                    $(lparent_pane).find('#intake_final_intake_final_date').prop('disabled',false);
                    $(lparent_pane).find('#intake_final_notes').prop('disabled',false);
                    switch(lintake_type){
                        case 'sales_invoice':
                            $(lparent_pane).find('#intake_final_warehouse_to').closest('.form-group').find('input').prop('disabled',false);
                            break;
                    }
                    break;
                case 'view':
                    $(lparent_pane).find('#intake_final_reference').select2('disable');
                    $(lparent_pane).find('#intake_final_notes').prop('disabled',false);
                    break;
            }
        },
        reset_all:function(){
            var lparent_pane = intake_final_parent_pane;
            $(lparent_pane).find('#intake_final_code').val('[AUTO GENERATE]');
            var ldefault_status = null;
            ldefault_status = APP_DATA_TRANSFER.common_ajax_listener('module_status/default_status_get',{module:'intake_final'}).response;

            $(lparent_pane).find('#intake_final_intake_final_status')
                    .select2('data',{id:ldefault_status.val,text:ldefault_status.label}).change();
            var lintake_status_list = [
                {id:ldefault_status.val,text:ldefault_status.label}
            ];
            
            $(lparent_pane).find('#intake_final_intake_final_status').
                select2({data:lintake_status_list});
            
            
            var ldefault_store = APP_DATA_TRANSFER.ajaxPOST(APP_PATH.base_url+
                'store/data_support/default_store_get/').response;
            $(lparent_pane).find('#intake_final_store').select2('data',
                {id:ldefault_store.id,text:ldefault_store.name}
            );
    
            $(lparent_pane).find('#intake_final_intake_final_date').datetimepicker({
                value:APP_GENERATOR.CURR_DATETIME('minute', 10,'F d, Y H:i')
            });
            
            $(lparent_pane).find('#intake_final_warehouse_to').select2('data',null);
            
            intake_final_methods.table.product.reset();
            intake_final_methods.table.qty.reset();
            
        },
        reserved_qty_calculate:function(lrow){
            var lparent_pane = intake_final_parent_pane;
            var loutstanding_qty = parseFloat($('#intake_final_product_table tbody tr:eq('+$(lrow).index()+') [col_name="qty_outstanding"]').text().replace(/[^0-9.]/g,''));
            var lqty_input_arr = $(lrow).find('[col_name="qty"] input');
            var lqty_total = parseFloat('0');
            $.each(lqty_input_arr, function(lidx, linput){
                lqty_total+= parseFloat($(linput).val().replace(/[^0-9.]/g,''));
            });

            var lreserved_qty = loutstanding_qty - lqty_total; 

            $(lparent_pane).find('#intake_final_product_table tbody tr:eq('+$(lrow).index()+') [col_name="reserved_qty"] span').text(
                APP_CONVERTER.thousand_separator(lreserved_qty)
            ).change();
        },
        table:{
            product:{
                reset:function(){
                    var lparent_pane = intake_final_parent_pane;
                    $(lparent_pane).find('#intake_final_product_table tbody').empty();
                    $(lparent_pane).find('#intake_final_product_table thead').empty();
                },
                header_set:function(){
                    var lparent_pane = intake_final_parent_pane;
                    var fast_draw = APP_COMPONENT.table_fast_draw;
                    var lthead = $(lparent_pane).find('#intake_final_product_table thead')[0];
                    var lintake_type = $(lparent_pane).find('#intake_final_type').val();
                    var lrow = document.createElement('tr');
                    var lmethod = $(lparent_pane).find('#intake_final_method').val();
                    
                    
                    fast_draw.col_add(lrow,{tag:'th',col_name:'row_num',style:'',val:'<br/><br/>#',type:'text',class:'table-row-num'});
                    fast_draw.col_add(lrow,{tag:'th',col_name:'product-img',style:'',val:'',type:'text',class:'product-img'});
                    fast_draw.col_add(lrow,{tag:'th',col_name:'product',style:'width:125px;max-width:125px',val:'<br/>Product',type:'text',class:''});
                    fast_draw.col_add(lrow,{tag:'th',col_name:'unit',style:'width:50px',val:'<br/>Unit',type:'text',class:''});
                    if(lintake_type === 'sales_invoice'){
                        fast_draw.col_add(lrow,{tag:'th',col_name:'qty',style:'width:75px;text-align:right',val:'Sales<br/>Qty',type:'text',class:''});
                        if(lmethod ==='add'){
                            fast_draw.col_add(lrow,{tag:'th',col_name:'qty_outstanding',style:'width:75px;text-align:right',val:'Outstanding<br/>Qty',type:'text',class:''});
                            
                            fast_draw.col_add(lrow,{tag:'th',col_name:'reserved_qty'
                                ,col_style:'text-align:right;width:75px'
                                ,val:'Reserved<br/>Qty',type:'text',
                                });
                        }
                    }
                    lthead.appendChild(lrow);
                    
                },
                load:function(iproduct_arr){
                    var lparent_pane = intake_final_parent_pane;
                    var ltbody = $(lparent_pane).find('#intake_final_product_table tbody')[0];
                    var fast_draw = APP_COMPONENT.table_fast_draw;
                    var lintake_type = $(lparent_pane).find('#intake_final_type').val();
                    var lmethod = $(lparent_pane).find('#intake_final_method').val();
                    intake_final_methods.table.product.header_set();
                    
                    $.each(iproduct_arr, function (lidx, lproduct){
                        var lrow = document.createElement('tr');
                        var row_num = lidx;
                        fast_draw.col_add(lrow,{tag:'td',col_name:'row_num',style:'vertical-align:middle',val:row_num+1,type:'text'});
                        fast_draw.col_add(lrow,{tag:'td',col_name:'product_img',style:'vertical-align:middle',val:lproduct.product_img,type:'text'});
                        fast_draw.col_add(lrow,{tag:'td',col_name:'product_id',style:'vertical-align:middle',val:lproduct.product_id,type:'text',visible:false});
                        fast_draw.col_add(lrow,{tag:'td',col_name:'product',style:'vertical-align:middle',val:lproduct.product_code,type:'text'});
                        fast_draw.col_add(lrow,{tag:'td',col_name:'unit_id',style:'vertical-align:middle',val:lproduct.unit_id,type:'text',visible:false});
                        fast_draw.col_add(lrow,{tag:'td',col_name:'unit',style:'vertical-align:middle',val:lproduct.unit_code,type:'text'});
                        if(lintake_type === 'sales_invoice'){
                            fast_draw.col_add(lrow,{tag:'td',col_name:'qty',style:'vertical-align:right',val:lproduct.qty,type:'text',style:"text-align:right"});
                            if(lmethod ==='add'){
                                var lqty_outstanding_td = fast_draw.col_add(lrow,{tag:'td',col_name:'qty_outstanding',style:'vertical-align:right;',val:lproduct.qty_outstanding,type:'text',style:"text-align:right"});
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
                    var lparent_pane = intake_final_parent_pane;
                    $(lparent_pane).find('#intake_final_qty_table tbody').empty();
                    $(lparent_pane).find('#intake_final_qty_table thead').empty();
                },
                header_set:function(iwarehouse_arr, iintake_arr){
                    var lparent_pane = intake_final_parent_pane;
                    var lmethod = $(lparent_pane).find('#intake_final_method').val();
                    var fast_draw = APP_COMPONENT.table_fast_draw;
                    var lthead = $(lparent_pane).find('#intake_final_qty_table thead')[0];
                    var lintake_type = $(lparent_pane).find('#intake_final_type').val();
                    var lrow = document.createElement('tr');
                                        
                    fast_draw.col_add(lrow,{tag:'th',col_name:''
                        ,col_style:'vertical-align:middle;width:0px',val:'',type:'text'});
                        
                    $.each(iwarehouse_arr, function(warehouse_idx,warehouse){
                        var lcode = '<br/>';
                        $.each(iintake_arr, function (intake_idx, intake){
                            if(intake.warehouse_from.id === warehouse.id){
                                lcode = '<a href="<?php echo get_instance()->config->base_url().'intake/view/'; ?>'+intake.id+'" target="_blank"><strong>'+intake.code+'</strong></a><br/>'+intake.intake_status_text+'';
                            }
                        });
                        fast_draw.col_add(lrow,{tag:'th',col_name:''
                            ,col_style:'vertical-align:middle;width:35px;margin-left:8px',val:'',type:'text',
                            attr:{warehouse_id:warehouse.id.toString()}
                        });
                        fast_draw.col_add(lrow,{tag:'th',col_name:''
                            ,col_style:'vertical-align:middle;text-align:center;width:150px'
                            ,val:lcode+'<br/>'+warehouse.name,type:'text',
                            attr:{colspan:'2',warehouse_id:warehouse.id.toString(),warehouse_header:''}});

                    });
                    
                    lthead.appendChild(lrow);
                    
                },
                load:function(iintake_arr, iproduct_stock_arr, iref_product_arr){
                    var lparent_pane = intake_final_parent_pane;
                    var ltbody = $(lparent_pane).find('#intake_final_qty_table tbody')[0];
                    var fast_draw = APP_COMPONENT.table_fast_draw;
                    var lintake_type = $(lparent_pane).find('#intake_final_type').val();
                    var lwarehouse_arr = APP_MODULE.warehouse.bos_get();
                    var lmethod = $(lparent_pane).find('#intake_final_method').val();
                    
                    intake_final_methods.table.qty.header_set(lwarehouse_arr,iintake_arr);
                    
                    var lproduct_tr_arr = $(lparent_pane).find('#intake_final_product_table tbody tr');
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
                            
                            fast_draw.col_add(lrow,{tag:'td',col_name:'reference_type',style:'vertical-align:middle'
                                ,val:'<div warehouse_id = "'+warehouse.id+'" product_id="'+lproduct_id+'" unit_id="'+lunit_id+'">'+lproduct_reference_type+'</div>',type:'text',visible:false,});
                            fast_draw.col_add(lrow,{tag:'td',col_name:'reference_id',style:'vertical-align:middle'
                                ,val:'<div warehouse_id = "'+warehouse.id+'" product_id="'+lproduct_id+'" unit_id="'+lunit_id+'">'+lproduct_reference_id+'</div>',type:'text',visible:false,});
                            
                            if(lmethod ==='add'){
                                lstock_qty_td = fast_draw.col_add(lrow,{tag:'td',col_name:'stock_qty'
                                    ,style:'vertical-align:middle;text-align:right'
                                    ,val:lstock_text,type:'text',attr:{warehouse_id:warehouse.id.toString()}}
                                );
                            }
                            
                            var lqty_td = '';
                            if(lmethod ==='add'){
                                lqty_td = fast_draw.col_add(lrow,{tag:'td',col_name:'qty',style:'vertical-align:middle'
                                    ,val:'<div><input warehouse_id = "'+warehouse.id+'" product_id="'+lproduct_id+'" unit_id="'+lunit_id+'" class="form-control" style="text-align:right;font-size:12px;font-weight:bold;min-width:100px" disabled></div>'
                                    ,type:'text',attr:{warehouse_id:warehouse.id.toString()}});
                            }
                            else if (lmethod ==='view'){
                                lqty_td = fast_draw.col_add(lrow,{tag:'td',col_name:'qty',style:'vertical-align:middle'
                                    ,val:'<div style="text-align:center;"><span warehouse_id = "'+warehouse.id+'" product_id="'+lproduct_id+'" unit_id="'+lunit_id+'" style="font-size:12px;font-weight:bold;min-width:100px">0.00</span></div>'
                                    ,type:'text',attr:{colspan:'2',warehouse_id:warehouse.id.toString()}});

                            }
                            
                            if(lmethod ==='add'){
                                var lqty_input = $(lqty_td).find('input')[0];
                                var loutstanding_qty = parseFloat($(lproduct_tr).find('[col_name="qty_outstanding"]').text().replace(/[^0-9.]/g,''));
                                var lqty_stock_float = parseFloat(lqty_stock.replace(/[^0-9.]/g,''));
                                var lmax_qty = loutstanding_qty<lqty_stock_float? loutstanding_qty : lqty_stock_float;
                                
                                APP_EVENT.init().component_set(lqty_input).type_set('input').numeric_set()
                                    .max_val_set(lmax_qty).min_val_set(0).render();                            
                            
                                $(lqty_input).on('blur',function(){
                                    intake_final_methods.reserved_qty_calculate($(this).closest('tr')[0]);
                                });
                            
                                APP_COMPONENT.input.color_non_zero(lqty_input,'blue');
                                $(lqty_input).val(0).blur();
                            }
                            else{
                                var lqty_span = $(lqty_td).find('span')[0];
                                APP_COMPONENT.text.color_non_zero(lqty_span,'blue');
                            }
                            
                        });
                        
                        ltbody.appendChild(lrow);
                        if(lmethod ==='view'){
                            $.each(iintake_arr,function(intake_idx,intake){
                                $.each(intake.product, function(product_idx,product){                                    
                                    var lspan = $(ltbody).find('span[warehouse_id="'+intake.warehouse_from.id+'"][product_id="'+product.product_id+'"][unit_id="'+product.unit_id+'"]')[0];
                                    $(lspan).text(product.qty).change();                             
                                    var lspan = $(ltbody).find('[col_name="reference_type"] div[warehouse_id="'+intake.warehouse_from.id+'"][product_id="'+product.product_id+'"][unit_id="'+product.unit_id+'"]')[0];
                                    $(lspan).text(product.reference_type).change();
                                    var lspan = $(ltbody).find('[col_name="reference_id"] div[warehouse_id="'+intake.warehouse_from.id+'"][product_id="'+product.product_id+'"][unit_id="'+product.unit_id+'"]')[0];
                                    $(lspan).text(product.reference_id).change();
                                });
                            });
                        }
                        
                        
                    });
                    
                    $('#intake_final_qty_table tbody input[type="checkbox"]').iCheck({checkboxClass: 'icheckbox_minimal'});
                    $('#intake_final_qty_table tbody input[type="checkbox"]').on('ifToggled',function(){
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
            var lparent_pane = intake_final_parent_pane;
            var lajax_url = intake_final_index_url;
            var lmethod = $(lparent_pane).find('#intake_final_method').val();
            var json_data = {
                ajax_post:true,
                message_session:true,
            };

            switch(lmethod){
                case 'add':
                    json_data.reference={
                        id:$(lparent_pane).find('#intake_final_reference').select2('val')
                    };
                    json_data.intake_final = {
                        store_id:$(lparent_pane).find('#intake_final_store').select2('val'),
                        intake_final_date:$(lparent_pane).find('#intake_final_intake_final_date').val(),
                        intake_final_type:$(lparent_pane).find('#intake_final_type').val(),
                    };

                    json_data.intake=[];
                    
                    var lwarehouse_arr = APP_MODULE.warehouse.bos_get();
                    lwarehouse_arr.push({id:'reserved_qty'});
                    $.each(lwarehouse_arr, function(lwarehouse_idx,lwarehouse){
                        var ltemp_delivery = {product:[],warehouse_from_id:lwarehouse.id};
                        var linput_arr = $(lparent_pane).find('#intake_final_qty_table tbody tr [col_name="qty"] input[warehouse_id="'+lwarehouse.id+'"]');
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
                            json_data.intake.push(ltemp_delivery);
                        }
                    });
                    lajax_url +='intake_final_add/';
                    break;
                case 'view':
                    json_data.intake_final = {
                        intake_final_status:$(lparent_pane).find('#intake_final_intake_final_status').select2('val'),
                        notes:$(lparent_pane).find('#intake_final_notes').val(),
                        cancellation_reason:$(lparent_pane).find('#intake_final_intake_final_cancellation_reason').val()
                    };
                    var intake_final_id = $(lparent_pane).find('#intake_final_id').val();
                    var lajax_method = $(lparent_pane).find('#intake_final_intake_final_status').select2('data').method;
                    lajax_url +=lajax_method+'/'+intake_final_id;
                    break;
            }

            result = APP_DATA_TRANSFER.submit(lajax_url,json_data);

            if(result.success ===1){
                $(lparent_pane).find('#intake_final_id').val(result.trans_id);
                if(intake_final_view_url !==''){
                    var url = intake_final_view_url+result.trans_id;
                    window.location.href=url;
                }
                else{
                    intake_final_after_submit();
                }
            }
        }
    };
    
    var intake_final_bind_event = function(){
        var parent_pane = intake_final_parent_pane;
        
        <?php  ?>        
        $(parent_pane).find('#intake_final_print').on('click',function(){            
            var ldof_id = $(parent_pane).find('#intake_final_id').val();      
            
            modal_print.init();
            modal_print.menu.add('<?php echo Lang::get("Product Intake").' Final'; ?>',
                intake_final_index_url+'intake_final_print/'+ldof_id);
            modal_print.show();
        })  ;      
        <?php  ?>
        
        $(parent_pane).find("#intake_final_reference")
        .on('change', function(){
            var lparent_pane = intake_final_parent_pane;
            var lmethod = $(lparent_pane).find('#intake_final_method').val();
            var lintake_type = '';
            var lref_data = $(this).select2('data');            
            if(lref_data === null) lref_data = {id:'',text:'',reference_type:'',reference_type_name:''}
            
            $('#intake_final_type').val(lref_data.reference_type);            
            lintake_type = $(lparent_pane).find('#intake_final_type').val();
            
            intake_final_methods.show_hide();//important for reference switching
            intake_final_methods.enable_disable();//important for reference switching
            
            $('#intake_final_reference_detail').find('.extra_info').remove();
            
            if(lmethod === 'add'){
                intake_final_methods.table.product.reset();
                intake_final_methods.table.qty.reset();
                
                var lresponse = APP_DATA_TRANSFER.ajaxPOST(intake_final_data_support_url+'/dependency_data_get',{ref_id:lref_data.id,ref_type:lintake_type}).response;
                var lref = lresponse.ref;
                var lref_product = lresponse.ref_product;
                
                APP_COMPONENT.reference_detail.extra_info_set($(lparent_pane).find('#intake_final_reference_detail')[0],lresponse.reference_detail);
                
                intake_final_methods.table.product.load(lref_product);
                intake_final_methods.table.qty.load({},lresponse.product_stock,lresponse.ref_product);
                
                switch(lintake_type){
                    case 'sales_invoice':
                        
                        break;
                }
            }
            
        });
        
        $(parent_pane).find('#intake_final_submit').off();        
        $(parent_pane).find('#intake_final_submit')
        .on('click',function(e){
            e.preventDefault();
            var btn = $(this);
            btn.addClass('disabled');
            var lparent_pane = intake_final_parent_pane;
            modal_confirmation_submit_parent = $(lparent_pane).attr('class').indexOf('modal-body')!==-1?
                $(lparent_pane).closest('.modal'):null;
            $('#modal_confirmation_submit').modal('show');
            $('#modal_confirmation_submit_btn_submit').on('click',function(){
                intake_final_methods.submit();
                $('#modal_confirmation_submit').modal('hide');

            });
                
            
            $(intake_final_window_scroll).scrollTop(0);        
            setTimeout(function(){btn.removeClass('disabled')},1000);

            
        });
            
        
    }
    
    var intake_final_components_prepare = function(){
        

        var intake_final_data_set = function(){
            var lparent_pane = intake_final_parent_pane;
            var lmethod = $(lparent_pane).find('#intake_final_method').val();
            
            switch(lmethod){
                case 'add':
                    intake_final_methods.reset_all();
                    break;
                case 'view':

                        
                
                    var lintake_id = $(lparent_pane).find('#intake_final_id').val();
                    var lajax_url = intake_final_data_support_url+'intake_final_get';
                    var json_data = {data:lintake_id};
                    var lresponse = APP_DATA_TRANSFER.ajaxPOST(lajax_url,json_data).response;
                    var lintake_final = lresponse.intake_final;
                    
                    $(lparent_pane).find('#intake_final_reference_detail .extra-info').remove();
                    $(lparent_pane).find('#intake_final_reference').select2(
                        'data',lresponse.reference); 
                    APP_COMPONENT.reference_detail.extra_info_set($('#intake_final_reference_detail')[0],lresponse.reference_detail);
                    
                    $(lparent_pane).find('#intake_final_store').select2('data',{id:lintake_final.store_id
                        ,text:lintake_final.store_text});
                    $(lparent_pane).find('#intake_final_code').val(lintake_final.code);
                    
                    $(lparent_pane).find('#intake_final_warehouse_to')
                        .select2('data',{id:lintake_final.warehouse_to_id,
                            text:lintake_final.warehouse_to_text}
                        );
                
                    $(lparent_pane).find('#intake_final_warehouse_to_code')
                            .text(lintake_final.warehouse_to_code);
                    $(lparent_pane).find('#intake_final_warehouse_to_name')
                            .text(lintake_final.warehouse_to_name);
                    $(lparent_pane).find('#intake_final_warehouse_to_type')
                            .text(lintake_final.warehouse_to_type_name);
                    $(lparent_pane).find('#intake_final_warehouse_to_contact_name')
                            .val(lintake_final.warehouse_to_contact_name);
                    $(lparent_pane).find('#intake_final_warehouse_to_address')
                            .val(lintake_final.warehouse_to_address);
                    $(lparent_pane).find('#intake_final_warehouse_to_phone')
                            .val(lintake_final.warehouse_to_phone);

                    $(lparent_pane).find('#intake_final_intake_final_date').datetimepicker({value:lintake_final.intake_final_date});
                    $(lparent_pane).find('#intake_final_intake_final_cancellation_reason').val(lintake_final.cancellation_reason);

                    $(lparent_pane).find('#intake_final_intake_final_status')
                            .select2('data',{id:lintake_final.intake_final_status
                                ,text:lintake_final.intake_final_status_text}).change();
                                  
                    $(lparent_pane).find('#intake_final_intake_final_status')
                            .select2({data:lresponse.intake_final_status_list});
                    intake_final_methods.table.product.reset();
                    intake_final_methods.table.qty.reset();
                    intake_final_methods.table.product.load(lresponse.product_ordered);
                    intake_final_methods.table.qty.load(lresponse.intake,[],[]);
                    
                    break;
            }
        }
        
        
        intake_final_methods.enable_disable();
        intake_final_methods.show_hide();
        intake_final_data_set();
    }
    
    var intake_final_after_submit = function(){
        //function that will be executed after submit 
    }
    
   
    
    
    
    
    
</script>